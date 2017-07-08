# 在PHP中用协同程序实现合作多任务

2013/06/30 · [IT技术][0] · [1 评论][1] · [php][2]

 分享到： 12

原文出处： [nikic][3] 译文出处：[oschina - 几点人, Khiyuan, 杨少瑜, 蒋锴, TigaPile][4]

PHP 5.5 一个比较好的新功能是实现对生成器和协同程序的支持。对于生成器，PHP的[文档][5]和各种其他的博客文章（就像[这一篇][6]或[这一篇][7]）已经有了非常详细的讲解。协同程序相对受到的关注就少了，所以协同程序虽然有很强大的功能但也很难被知晓，解释起来也比较困难。

这篇文章指导你通过使用协同程序来实施任务调度，通过实例实现对技术的理解。我将在前三节做一个简单的背景介绍。如果你已经有了比较好的基础，可以直接跳到“协同多任务处理”一节。

### 生成器

生成器最基本的思想也是一个函数，这个函数的返回值是依次输出，而不是只返回一个单独的值。或者，换句话说，生成器使你更方便的实现了迭代器接口。下面通过实现一个xrange函数来简单说明：
```php
<?php

function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}

foreach (xrange(1, 1000000) as $num) {
    echo $num, "\n";
}
```
上面这个xrange（）函数提供了和PHP的内建函数range()一样的功能。但是不同的是range()函数返回的是一个包含属组值从1到100万的数组（注：请查看手册）。而xrange（）函数返回的是依次输出这些值的一个迭代器，而且并不会真正以数组形式计算。

这种方法的优点是显而易见的。它可以让你在处理大数据集合的时候不用一次性的加载到内存中。甚至你可以处理无限大的数据流。

当然，也可以不同通过生成器来实现这个功能，而是可以通过继承Iterator接口实现。通过使用生成器实现起来会更方便，而不用再去实现iterator接口中的5个方法了。

### **生成器为可中断的函数**

要从生成器认识协同程序，理解它们内部是如何工作的非常重要：生成器是可中断的函数，在它里面，yield构成了中断点。

紧接着上面的例子，如果你调用xrange(1,1000000)的话，xrange()函数里代码没有真正地运行。相反，PHP只是返回了一个实现了迭代器接口的 生成器类实例：
```php
<?php

$range = xrange(1, 1000000);
var_dump($range); // object(Generator)#1
var_dump($range instanceof Iterator); // bool(true)
```
你对某个对象调用迭代器方法一次，其中的代码运行一次。例如，如果你调用$range->rewind(),那么xrange()里的代码运行到控制流 第一次出现yield的地方。在这种情况下，这就意味着当$i=$start时yield $i才运行。传递给yield语句的值是使用$range->current()获取的。

为了继续执行生成器中的代码，你必须调用$range->next()方法。这将再次启动生成器，直到yield语句出现。因此，连续调用next()和current()方法 你将能从生成器里获得所有的值，直到某个点没有再出现yield语句。对xrange()来说，这种情形出现在$i超过$end时。在这中情况下， 控制流将到达函数的终点，因此将不执行任何代码。一旦这种情况发生，vaild()方法将返回假，这时迭代结束。

**协程**

协程给上面功能添加的主要东西是回送数据给生成器的能力。这将把生成器到调用者的单向通信转变为两者之间的双向通信。

通过调用生成器的send()方法而不是其next()方法传递数据给协程。下面的logger()协程是这种通信如何运行的例子：
```php
<?php

function logger($fileName) {
    $fileHandle = fopen($fileName, 'a');
    while (true) {
        fwrite($fileHandle, yield . "\n");
    }
}

$logger = logger(__DIR__ . '/log');
$logger->send('Foo');
$logger->send('Bar')
```
正如你能看到，这儿yield没有作为一个语句来使用，而是用作一个表达式。即它有一个返回值。yield的返回值是传递给send()方法的值。 在这个例子里，yield将首先返回”Foo”,然后返回”Bar”。

上面的例子里yield仅作为接收者。混合两种用法是可能的，即既可接收也可发送。接收和发送通信如何进行的例子如下：
```php
<?php

function gen() {
    $ret = (yield 'yield1');
    var_dump($ret);
    $ret = (yield 'yield2');
    var_dump($ret);
}

$gen = gen();
var_dump($gen->current());    // string(6) "yield1"
var_dump($gen->send('ret1')); // string(4) "ret1"   (the first var_dump in gen)
                              // string(6) "yield2" (the var_dump of the ->send() return value)
var_dump($gen->send('ret2')); // string(4) "ret2"   (again from within gen)
                              // NULL               (the return value of ->send())
```
马上理解输出的精确顺序有点困难，因此确定你知道为什按照这种方式输出。我愿意特别指出的有两点：第一点，yield表达式两边使用 圆括号不是偶然。由于技术原因（虽然我已经考虑为赋值增加一个异常，就像Python那样），圆括号是必须的。第二点，你可能已经注意到 调用current()之前没有调用rewind()。如果是这么做的，那么已经隐含地执行了rewind操作。

**多任务协作**

如果阅读了上面的logger()例子，那么你认为“为了双向通信我为什么要使用协程呢？ 为什么我不能只用常见的类呢？”，你这么问完全正确。上面的例子演示了基本用法，然而上下文中没有真正的展示出使用协程的优点。这就是列举许多协程例子的理由。正如上面介绍里提到的，协程是非常强大的概念，不过这样的应用很稀少而且常常十分复杂。给出一些简单而真实的例子很难。

在这篇文章里，我决定去做的是使用协程实现多任务协作。我们尽力解决的问题是你想并发地运行多任务(或者“程序”）。不过处理器在一个时刻只能运行一个任务（这篇文章的目标是不考虑多核的）。因此处理器需要在不同的任务之间进行切换，而且总是让每个任务运行 “一小会儿”。

多任务协作这个术语中的“协作”说明了如何进行这种切换的：它要求当前正在运行的任务自动把控制传回给调度器，这样它就可以运行其他任务了。这与“抢占”多任务相反，抢占多任务是这样的：调度器可以中断运行了一段时间的任务，不管它喜欢还是不喜欢。协作多任务在Windows的早期版本（windows95)和Mac OS中有使用，不过它们后来都切换到使用抢先多任务了。理由相当明确：如果你依靠程序自动传回 控制的话，那么坏行为的软件将很容易为自身占用整个CPU，不与其他任务共享。

这个时候你应当明白协程和任务调度之间的联系：yield指令提供了任务中断自身的一种方法，然后把控制传递给调度器。因此协程可以运行多个其他任务。更进一步来说，yield可以用来在任务和调度器之间进行通信。

我们的目的是 对 “任务”用更轻量级的包装的协程函数:
```php
<?php

class Task {
    protected $taskId;
    protected $coroutine;
    protected $sendValue = null;
    protected $beforeFirstYield = true;

    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }

    public function isFinished() {
        return !$this->coroutine->valid();
    }
}
```
一个任务是用 任务ID标记一个协程。使用setSendValue()方法，你可以指定哪些值将被发送到下次的恢复（在之后你会了解到我们需要这个）。 run()函数确实没有做什么，除了调用send()方法的协同程序。要理解为什么添加beforeFirstYieldflag，需要考虑下面的代码片段：
```php
<?php

function gen() {
    yield 'foo';
    yield 'bar';
}

$gen = gen();
var_dump($gen->send('something'));

// As the send() happens before the first yield there is an implicit rewind() call,
// so what really happens is this:
$gen->rewind();
var_dump($gen->send('something'));

// The rewind() will advance to the first yield (and ignore its value), the send() will
// advance to the second yield (and dump its value). Thus we loose the first yielded value!
```
通过添加 beforeFirstYieldcondition 我们可以确定 first yield 的值 被返回。

调度器现在不得不比多任务循环要做稍微多点了，然后才运行多任务：
```php
<?php

class Scheduler {
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;

    public function __construct() {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }

    public function run() {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $task->run();

            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }
}
```
newTask()方法（使用下一个空闲的任务id）创建一个新任务，然后把这个任务放入任务映射数组里。接着它通过把任务放入任务队列里来实现对任务的调度。接着run()方法扫描任务队列，运行任务。如果一个任务结束了，那么它将从队列里删除，否则它将在队列的末尾再次被调度。  
让我们看看下面具有两个简单（并且没有什么意义）任务的调度器：
```php
<?php

function task1() {
    for ($i = 1; $i <= 10; ++$i) {
        echo "This is task 1 iteration $i.\n";
        yield;
    }
}

function task2() {
    for ($i = 1; $i <= 5; ++$i) {
        echo "This is task 2 iteration $i.\n";
        yield;
    }
}

$scheduler = new Scheduler;

$scheduler->newTask(task1());
$scheduler->newTask(task2());

$scheduler->run();
```
两个任务都仅仅回显一条信息，然后使用yield把控制回传给调度器。输出结果如下：
```
This is task 1 iteration 1.
This is task 2 iteration 1.
This is task 1 iteration 2.
This is task 2 iteration 2.
This is task 1 iteration 3.
This is task 2 iteration 3.
This is task 1 iteration 4.
This is task 2 iteration 4.
This is task 1 iteration 5.
This is task 2 iteration 5.
This is task 1 iteration 6.
This is task 1 iteration 7.
This is task 1 iteration 8.
This is task 1 iteration 9.
This is task 1 iteration 10.
```
输出确实如我们所期望的：对前五个迭代来说，两个任务是交替运行的，接着第二个任务结束后，只有第一个任务继续运行。

### **与调度器之间通信**

既然调度器已经运行了，那么我们就转向日程表的下一项：任务和调度器之间的通信。我们将使用进程用来和操作系统会话的同样的方式来通信：系统调用。我们需要系统调用的理由是操作系统与进程相比它处在不同的权限级别上。因此为了执行特权级别的操作（如杀死另一个进程），就不得不以某种方式把控制传回给内核，这样内核就可以执行所说的操作了。再说一遍，这种行为在内部是通过使用中断指令来实现的。过去使用的是通用的int指令，如今使用的是更特殊并且更快速的syscall/sysenter指令。

我们的任务调度系统将反映这种设计：不是简单地把调度器传递给任务（这样久允许它做它想做的任何事），我们将通过给yield表达式传递信息来与系统调用通信。这儿yield即是中断，也是传递信息给调度器（和从调度器传递出信息）的方法。

为了说明系统调用，我将对可调用的系统调用做一个小小的封装：
```php
<?php

class SystemCall {
    protected $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler) {
        $callback = $this->callback; // Can't call it directly in PHP :/
        return $callback($task, $scheduler);
    }
}
```
它将像其他任何可调用那样(使用_invoke)运行，不过它要求调度器把正在调用的任务和自身传递给这个函数。为了解决这个问题 我们不得不微微的修改调度器的run方法：
```php
<?php
public function run() {
    while (!$this->taskQueue->isEmpty()) {
        $task = $this->taskQueue->dequeue();
        $retval = $task->run();

        if ($retval instanceof SystemCall) {
            $retval($task, $this);
            continue;
        }

        if ($task->isFinished()) {
            unset($this->taskMap[$task->getTaskId()]);
        } else {
            $this->schedule($task);
        }
    }
}
```
第一个系统调用除了返回任务ID外什么都没有做：
```php
<?php
function getTaskId() {
    return new SystemCall(function(Task $task, Scheduler $scheduler) {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}
```
这个函数确实设置任务id为下一次发送的值，并再次调度了这个任务。由于使用了系统调用，所以调度器不能自动调用任务，我们需要手工调度任务（稍后你将明白为什么这么做）。要使用这个新的系统调用的话，我们要重新编写以前的例子：
```php
<?php

function task($max) {
    $tid = (yield getTaskId()); // <-- here's the syscall!
    for ($i = 1; $i <= $max; ++$i) {
        echo "This is task $tid iteration $i.\n";
        yield;
    }
}

$scheduler = new Scheduler;

$scheduler->newTask(task(10));
$scheduler->newTask(task(5));

$scheduler->run();
```
这段代码将给出与前一个例子相同的输出。注意系统调用同其他任何调用一样正常地运行，不过预先增加了yield。要创建新的任务，然后再杀死它们的话，需要两个以上的系统调用：
```php
<?php

function newTask(Generator $coroutine) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($coroutine) {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}

function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}
```
killTask函数需要在调度器里增加一个方法：
```php
<?php

public function killTask($tid) {
    if (!isset($this->taskMap[$tid])) {
        return false;
    }

    unset($this->taskMap[$tid]);

    // This is a bit ugly and could be optimized so it does not have to walk the queue,
    // but assuming that killing tasks is rather rare I won't bother with it now
    foreach ($this->taskQueue as $i => $task) {
        if ($task->getTaskId() === $tid) {
            unset($this->taskQueue[$i]);
            break;
        }
    }

    return true;
}
```
用来测试新功能的微脚本：
```php
<?php

function childTask() {
    $tid = (yield getTaskId());
    while (true) {
        echo "Child task $tid still alive!\n";
        yield;
    }
}

function task() {
    $tid = (yield getTaskId());
    $childTid = (yield newTask(childTask()));

    for ($i = 1; $i <= 6; ++$i) {
        echo "Parent task $tid iteration $i.\n";
        yield;

        if ($i == 3) yield killTask($childTid);
    }
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```
这段代码将打印以下信息：
```
Parent task 1 iteration 1.
Child task 2 still alive!
Parent task 1 iteration 2.
Child task 2 still alive!
Parent task 1 iteration 3.
Child task 2 still alive!
Parent task 1 iteration 4.
Parent task 1 iteration 5.
Parent task 1 iteration 6.
```
经过三次迭代以后子任务将被杀死，因此这就是”Child is still alive”消息结束的时候。可能应当指出的是这不是真正的父子关系。 因为甚至在父任务结束后子任务仍然可以运行。或者子任务可以杀死父任务。可以修改调度器使它具有更层级化的任务结构，不过 在这篇文章里我没有这么做。

你可以实现许多进程管理调用。例如 wait（它一直等待到任务结束运行时）,exec（它替代当前任务）和fork（它创建一个 当前任务的克隆）。fork非常酷，而且你可以使用PHP的协程真正地实现它，因为它们都支持克隆。

然而让我们把这些留给有兴趣的读者吧，我们去看下一个议题。

### **非阻塞IO**

很明显，我们的任务管理系统的真正很酷的应用是web服务器。它有一个任务是在套接字上侦听是否有新连接，当有新连接要建立的时候 ，它创建一个新任务来处理新连接。  
web服务器最难的部分通常是像读数据这样的套接字操作是阻塞的。例如PHP将等待到客户端完成发送为止。对一个WEB服务器来说，这 根本不行；这就意味着服务器在一个时间点上只能处理一个连接。

解决方案是确保在真正对套接字读写之前该套接字已经“准备就绪”。为了查找哪个套接字已经准备好读或者写了，可以使用 [流选择][8]函数。

首先，让我们添加两个新的 syscall，它们将等待直到指定 socket 准备好：
```php
<?php

function waitForRead($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket, $task);
        }
    );
}

function waitForWrite($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket, $task);
        }
    );
}
```
这些 syscall 只是在调度器中代理其各自的方法：
```php
<?php

// resourceID => [socket, tasks]
protected $waitingForRead = [];
protected $waitingForWrite = [];

public function waitForRead($socket, Task $task) {
    if (isset($this->waitingForRead[(int) $socket])) {
        $this->waitingForRead[(int) $socket][1][] = $task;
    } else {
        $this->waitingForRead[(int) $socket] = [$socket, [$task]];
    }
}

public function waitForWrite($socket, Task $task) {
    if (isset($this->waitingForWrite[(int) $socket])) {
        $this->waitingForWrite[(int) $socket][1][] = $task;
    } else {
        $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
    }
}
```
waitingForRead 及 waitingForWrite 属性是两个承载等待的socket 及等待它们的任务的数组。有趣的部分在于下面的方法，它将检查 socket 是否可用，并重新安排各自任务：
```php
<?php

protected function ioPoll($timeout) {
    $rSocks = [];
    foreach ($this->waitingForRead as list($socket)) {
        $rSocks[] = $socket;
    }

    $wSocks = [];
    foreach ($this->waitingForWrite as list($socket)) {
        $wSocks[] = $socket;
    }

    $eSocks = []; // dummy

    if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
        return;
    }

    foreach ($rSocks as $socket) {
        list(, $tasks) = $this->waitingForRead[(int) $socket];
        unset($this->waitingForRead[(int) $socket]);

        foreach ($tasks as $task) {
            $this->schedule($task);
        }
    }

    foreach ($wSocks as $socket) {
        list(, $tasks) = $this->waitingForWrite[(int) $socket];
        unset($this->waitingForWrite[(int) $socket]);

        foreach ($tasks as $task) {
            $this->schedule($task);
        }
    }
}
```
stream_select 函数接受承载读取、写入以及待检查的socket的数组（我们无需考虑最后一类）。数组将按引用传递，函数只会保留那些状态改变了的数组元素。我们可以遍历这些数组，并重新安排与之相关的任务。

为了正常地执行上面的轮询动作，我们将在调度器里增加一个特殊的任务：
```php
<?php
protected function ioPollTask() {
    while (true) {
        if ($this->taskQueue->isEmpty()) {
            $this->ioPoll(null);
        } else {
            $this->ioPoll(0);
        }
        yield;
    }
}
```
需要在某个地方注册这个任务，例如，你可以在run()方法的开始增加$this->newTask($this->ioPollTask())。然后就像其他 任务一样每执行完整任务循环一次就执行轮询操作一次（这么做一定不是最好的方法）。ioPollTask将使用0秒的超时来调用ioPoll， 这意味着stream_select将立即返回（而不是等待）。

只有任务队列为空时，我们才使用null超时，这意味着它一直等到某个套接口准备就绪。如果我们没有这么做，那么轮询任务将一而再， 再而三的循环运行，直到有新的连接建立。这将导致100%的CPU利用率。相反，让操作系统做这种等待会更有效。

现在编写服务器相对容易了：
```php
<?php

function server($port) {
    echo "Starting server at port $port...\n";

    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);

    stream_set_blocking($socket, 0);

    while (true) {
        yield waitForRead($socket);
        $clientSocket = stream_socket_accept($socket, 0);
        yield newTask(handleClient($clientSocket));
    }
}

function handleClient($socket) {
    yield waitForRead($socket);
    $data = fread($socket, 8192);

    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);

    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;

    yield waitForWrite($socket);
    fwrite($socket, $response);

    fclose($socket);
}

$scheduler = new Scheduler;
$scheduler->newTask(server(8000));
$scheduler->run();
```
这段代码将接收到localhost:8000上的连接，然后仅仅返回发送来的内容作为HTTP响应。要做“实际”的事情的话就爱哪个非常复杂（处理 HTTP请求可能已经超出了这篇文章的范围）。上面的代码片段只是演示了一般性的概念。

你可以使用类似于ab -n 10000 -c 100 localhost:8000/这样命令来测试服务器。这条命令将向服务器发送10000个请求，并且其中100个请求将同时到达。使用这样的数目，我得到了处于中间的10毫秒的响应时间。不过还有一个问题：有少数几个请求真正处理的很慢（如5秒）， 这就是为什么总吞吐量只有2000请求/秒（如果是10毫秒的响应时间的话，总的吞吐量应该更像是10000请求/秒）。调高并发数（比如 -c 500),服务器大多数运行良好，不过某些连接将抛出“连接被对方重置”的错误。由于我对低级别的socket资料了解的非常少，所以 我不能指出问题出在哪儿。

### 协程堆栈

如果你试图用我们的调度系统建立更大的系统的话，你将很快遇到问题：我们习惯了把代码分解为更小的函数，然后调用它们。然而， 如果使用了协程的话，就不能这么做了。例如，看下面代码：
```php
<?php

function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;
    }
}

function task() {
    echoTimes('foo', 10); // print foo ten times
    echo "---\n";
    echoTimes('bar', 5); // print bar five times
    yield; // force it to be a coroutine
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```
这段代码试图把重复循环“输出n次“的代码嵌入到一个独立的协程里，然后从主任务里调用它。然而它无法运行。正如在这篇文章的开始 所提到的，调用生成器（或者协程）将没有真正地做任何事情，它仅仅返回一个对象。这也出现在上面的例子里。echoTimes调用除了放回一个（无用的）协程对象外不做任何事情。

为了仍然允许这么做，我们需要在这个裸协程上写一个小小的封装。我们将调用它：“协程堆栈”。因为它将管理嵌套的协程调用堆栈。 这将是通过生成协程来调用子协程成为可能：

    $retval = (yield someCoroutine($foo, $bar));

使用yield，子协程也能再次返回值：

    yield retval("I'm a return value!");

retval函数除了返回一个值的封装外没有做任何其他事情。这个封装将表示它是一个返回值。
```php
<?php

class CoroutineReturnValue {
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }
}

function retval($value) {
    return new CoroutineReturnValue($value);
}
```
为了把协程转变为协程堆栈（它支持子调用），我们将不得不编写另外一个函数（很明显，它是另一个协程）：
```php
<?php

function stackedCoroutine(Generator $gen) {
    $stack = new SplStack;

    for (;;) {
        $value = $gen->current();

        if ($value instanceof Generator) {
            $stack->push($gen);
            $gen = $value;
            continue;
        }

        $isReturnValue = $value instanceof CoroutineReturnValue;
        if (!$gen->valid() || $isReturnValue) {
            if ($stack->isEmpty()) {
                return;
            }

            $gen = $stack->pop();
            $gen->send($isReturnValue ? $value->getValue() : NULL);
            continue;
        }

        $gen->send(yield $gen->key() => $value);
    }
}
```
这个函数在调用者和当前正在运行的子协程之间扮演着简单代理的角色。在$gen->send(yield $gen->key()=>$value)；这行完成了代理功能。另外它检查返回值是否是生成器，万一是生成器的话，它将开始运行这个生成器，并把前一个协程压入堆栈里。一旦它获得了CoroutineReturnValue的话，它将再次请求堆栈弹出，然后继续执行前一个协程。

为了使协程堆栈在任务里可用，任务构造器里的$this-coroutine =$coroutine;这行需要替代为$this->coroutine = StackedCoroutine($coroutine);。

现在我们可以稍微改进上面web服务器例子：把wait+read(和wait+write和warit+accept)这样的动作分组为函数。为了分组相关的 功能，我将使用下面类：
```php
<?php

class CoSocket {
    protected $socket;

    public function __construct($socket) {
        $this->socket = $socket;
    }

    public function accept() {
        yield waitForRead($this->socket);
        yield retval(new CoSocket(stream_socket_accept($this->socket, 0)));
    }

    public function read($size) {
        yield waitForRead($this->socket);
        yield retval(fread($this->socket, $size));
    }

    public function write($string) {
        yield waitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    public function close() {
        @fclose($this->socket);
    }
}
```
现在服务器可以编写的稍微简洁点了：
```php
<?php

function server($port) {
    echo "Starting server at port $port...\n";

    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);

    stream_set_blocking($socket, 0);

    $socket = new CoSocket($socket);
    while (true) {
        yield newTask(
            handleClient(yield $socket->accept())
        );
    }
}

function handleClient($socket) {
    $data = (yield $socket->read(8192));

    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);

    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;

    yield $socket->write($response);
    yield $socket->close();
}
```
## 错误处理

作为一个优秀的程序员，相信你已经察觉到上面的例子缺少错误处理。几乎所有的 socket 都是易出错的。我这样做的原因一方面固然是因为错误处理的乏味（特别是 socket！），另一方面也在于它很容易使代码体积膨胀。

不过，我仍然了一讲一下常见的协程错误处理：协程允许使用 throw() 方法在其_内部_抛出一个错误。尽管此方法还未在 PHP 中实现，但我很快就会提交它，就在今天。

throw() 方法接受一个 Exception，并将其抛出到协程的当前悬挂点，看看下面代码：
```php
<?php

function gen() {
    echo "Foo\n";
    try {
        yield;
    } catch (Exception $e) {
        echo "Exception: {$e->getMessage()}\n";
    }
    echo "Bar\n";
}

$gen = gen();
$gen->rewind();                     // echos "Foo"
$gen->throw(new Exception('Test')); // echos "Exception: Test"
                                    // and "Bar"
```
这非常棒，因为我们可以使用系统调用以及子协程调用异常抛出。对与系统调用，Scheduler::run() 方法需要一些小调整：
```php
<?php

if ($retval instanceof SystemCall) {
    try {
        $retval($task, $this);
    } catch (Exception $e) {
        $task->setException($e);
        $this->schedule($task);
    }
    continue;
}

Task 类也许要添加 throw 调用处理：

<?php

class Task {
    // ...
    protected $exception = null;

    public function setException($exception) {
        $this->exception = $exception;
    }

    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } elseif ($this->exception) {
            $retval = $this->coroutine->throw($this->exception);
            $this->exception = null;
            return $retval;
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }

    // ...
}
```
现在，我们已经可以在系统调用中使用异常抛出了！例如，要调用 killTask，让我们在传递 ID 不可用时抛出一个异常：
```php
<?php

function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            if ($scheduler->killTask($tid)) {
                $scheduler->schedule($task);
            } else {
                throw new InvalidArgumentException('Invalid task ID!');
            }
        }
    );
}
```
试试看：
```php
<?php

function task() {
    try {
        yield killTask(500);
    } catch (Exception $e) {
        echo 'Tried to kill task 500 but failed: ', $e->getMessage(), "\n";
    }
}
```
这些代码现在尚不能正常运作，因为 stackedCoroutine 函数无法正确处理异常。要修复需要做些调整：
```php
<?php

function stackedCoroutine(Generator $gen) {
    $stack = new SplStack;
    $exception = null;

    for (;;) {
        try {
            if ($exception) {
                $gen->throw($exception);
                $exception = null;
                continue;
            }

            $value = $gen->current();

            if ($value instanceof Generator) {
                $stack->push($gen);
                $gen = $value;
                continue;
            }

            $isReturnValue = $value instanceof CoroutineReturnValue;
            if (!$gen->valid() || $isReturnValue) {
                if ($stack->isEmpty()) {
                    return;
                }

                $gen = $stack->pop();
                $gen->send($isReturnValue ? $value->getValue() : NULL);
                continue;
            }

            try {
                $sendValue = (yield $gen->key() => $value);
            } catch (Exception $e) {
                $gen->throw($e);
                continue;
            }

            $gen->send($sendValue);
        } catch (Exception $e) {
            if ($stack->isEmpty()) {
                throw $e;
            }

            $gen = $stack->pop();
            $exception = $e;
        }
    }
}
```
### 结束语

在这篇文章里，我使用多任务协作构建了一个任务调度器，其中包括执行“系统调用”，做非阻塞操作和处理错误。所有这些里真正很酷的事情是任务的结果代码看起来完全同步，甚至任务正在执行大量的异步操作的时候也是这样。如果你打算从套接口读取数据的话，你将不需要传递某个回调函数或者注册一个事件侦听器。相反，你只要书写yield $socket->read()。这儿大部分都是你常常也要编写的，只在它的前面增加yield。

当我第一次听到所有这一切的时候，我发现这个概念完全令人折服，而且正是这个激励我在PHP中实现了它。同时我发现协程真正令人心慌。在令人敬畏的代码和很大一堆代码之间只有单薄的一行，我认为协程正好处在这一行上。讲讲使用上面所述的方法书写异步代码是否真的有益对我来说很难。

无论如何，我认为这是一个有趣的话题，而且我希望你也能找到它的乐趣。欢迎评论:)

[0]: http://blog.jobbole.com/category/it-tech/
[1]: #article-comment
[2]: http://blog.jobbole.com/tag/php/
[3]: http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html
[4]: http://www.oschina.net/translate/cooperative-multitasking-using-coroutines-in-php
[5]: http://de2.php.net/manual/en/language.generators.overview.php
[6]: http://blog.ircmaxell.com/2012/07/what-generators-can-do-for-you.html
[7]: https://sheriframadan.com/2012/10/test-drive-php-5-5-a-sneak-peek/#generators
[8]: http://de3.php.net/manual/en/function.stream-select.php
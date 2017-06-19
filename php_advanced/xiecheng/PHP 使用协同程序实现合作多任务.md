# [[转]：PHP 使用协同程序实现合作多任务][0]

* [php][1]

[**在路上**][2] 1月29日发布 



**协程是比较少见的概念，先转过来作为参考，需要时应该可以用到**  
转自：[http://www.oschina.net/transl...][11]

### 生成器

生成器最基本的思想也是一个函数，这个函数的返回值是依次输出，而不是只返回一个单独的值。或者，换句话说，生成器使你更方便的实现了迭代器接口。下面通过实现一个xrange函数来简单说明：

<?php

    <?php
    
    function xrange($start, $end, $step = 1) {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }
    
    foreach (xrange(1, 1000000) as $num) {
        echo $num, "\n";
    }

上面这个xrange（）函数提供了和PHP的内建函数range()一样的功能。但是不同的是range()函数返回的是一个包含属组值从1到100万的数组（注：请查看手册）。而xrange（）函数返回的是依次输出这些值的一个迭代器，而且并不会真正以数组形式计算。

这种方法的优点是显而易见的。它可以让你在处理大数据集合的时候不用一次性的加载到内存中。甚至你可以处理无限大的数据流。

当然，也可以不同通过生成器来实现这个功能，而是可以通过继承Iterator接口实现。通过使用生成器实现起来会更方便，而不用再去实现iterator接口中的5个方法了。

### **生成器为可中断的函数**

要从生成器认识协同程序，理解它们内部是如何工作的非常重要：生成器是可中断的函数，在它里面，yield构成了中断点。 

紧接着上面的例子，如果你调用xrange(1,1000000)的话，xrange()函数里代码没有真正地运行。相反，PHP只是返回了一个实现了迭代器接口的 生成器类实例： 

    <?php
    
    $range = xrange(1, 1000000);
    var_dump($range); // object(Generator)#1
    var_dump($range instanceof Iterator); // bool(true)

**协程**

协程给上面功能添加的主要东西是回送数据给生成器的能力。这将把生成器到调用者的单向通信转变为两者之间的双向通信。

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

正如你能看到，这儿yield没有作为一个语句来使用，而是用作一个表达式。即它有一个返回值。yield的返回值是传递给send()方法的值。 在这个例子里，yield将首先返回"Foo",然后返回"Bar"。

上面的例子里yield仅作为接收者。混合两种用法是可能的，即既可接收也可发送。接收和发送通信如何进行的例子如下：

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

**多任务协作**

如果阅读了上面的logger()例子，那么你认为“为了双向通信我为什么要使用协程呢？ 为什么我不能只用常见的类呢？”，你这么问完全正确。上面的例子演示了基本用法，然而上下文中没有真正的展示出使用协程的优点。这就是列举许多协程例子的理由。正如上面介绍里提到的，协程是非常强大的概念，不过这样的应用很稀少而且常常十分复杂。给出一些简单而真实的例子很难。

多任务协作这个术语中的“协作”说明了如何进行这种切换的：它要求当前正在运行的任务自动把控制传回给调度器，这样它就可以运行其他任务了。这与“抢占”多任务相反，抢占多任务是这样的：调度器可以中断运行了一段时间的任务，不管它喜欢还是不喜欢。协作多任务在Windows的早期版本（windows95)和Mac OS中有使用，不过它们后来都切换到使用抢先多任务了。理由相当明确：如果你依靠程序自动传回 控制的话，那么坏行为的软件将很容易为自身占用整个CPU，不与其他任务共享。 

这个时候你应当明白协程和任务调度之间的联系：yield指令提供了任务中断自身的一种方法，然后把控制传递给调度器。因此协程可以运行多个其他任务。更进一步来说，yield可以用来在任务和调度器之间进行通信。

我们的目的是 对 “任务”用更轻量级的包装的协程函数:

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

任务ID标记

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

调度器现在不得不比多任务循环要做稍微多点了，然后才运行多任务：

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

### **与调度器之间通信**既然调度器已经运行了，那么我们就转向日程表的下一项：任务和调度器之间的通信。我们将使用进程用来和操作系统会话的同样的方式来通信：系统调用。我们需要系统调用的理由是操作系统与进程相比它处在不同的权限级别上。因此为了执行特权级别的操作（如杀死另一个进程），就不得不以某种方式把控制传回给内核，这样内核就可以执行所说的操作了。再说一遍，这种行为在内部是通过使用中断指令来实现的。过去使用的是通用的int指令，如今使用的是更特殊并且更快速的syscall/sysenter指令。

为了说明系统调用，我将对可调用的系统调用做一个小小的封装：

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

    <?php
    function getTaskId() {
        return new SystemCall(function(Task $task, Scheduler $scheduler) {
            $task->setSendValue($task->getTaskId());
            $scheduler->schedule($task);
        });
    }

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

killTask函数需要在调度器里增加一个方法：

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

这段代码将打印以下信息：

    Parent task 1 iteration 1.
    Child task 2 still alive!
    Parent task 1 iteration 2.
    Child task 2 still alive!
    Parent task 1 iteration 3.
    Child task 2 still alive!
    Parent task 1 iteration 4.
    Parent task 1 iteration 5.
    Parent task 1 iteration 6.

[0]: /a/1190000008227240
[1]: /t/php/blogs
[2]: /u/zailushang
[11]: http://www.oschina.net/translate/cooperative-multitasking-using-coroutines-in-php
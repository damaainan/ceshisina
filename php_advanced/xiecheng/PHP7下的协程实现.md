# PHP7下的协程实现

 时间 2017-12-17 11:55:08  

原文[https://segmentfault.com/a/1190000012457145][1]


## 前言

相信大家都听说过『协程』这个概念吧。

但是有些同学对这个概念似懂非懂，不知道怎么实现，怎么用，用在哪，甚至有些人认为yield就是协程！

我始终相信，如果你无法准确地表达出一个知识点的话，我可以认为你就是不懂。

如果你之前了解过利用PHP实现协程的话，你肯定看过鸟哥的那篇文章： [在PHP中使用协程实现多任务调度| 风雪之隅][3]

鸟哥这篇文章是从国外的作者翻译来的，翻译的简洁明了，也给出了具体的例子了。

我写这篇文章的目的，是想对鸟哥文章做更加充足的补充，毕竟有部分同学的基础还是不够好，看得也是云头雾里的。

## 什么是协程

先搞清楚，什么是协程。

你可能已经听过『进程』和『线程』这两个概念。

进程就是二进制可执行文件在计算机内存里的一个运行实例，就好比你的.exe文件是个类，进程就是new出来的那个实例。

进程是计算机系统进行资源分配和调度的基本单位（调度单位这里别纠结线程进程的），每个CPU下同一时刻只能处理一个进程。

所谓的并行，只不过是看起来并行，CPU事实上在用很快的速度切换不同的进程。

进程的切换需要进行系统调用，CPU要保存当前进程的各个信息，同时还会使CPUCache被废掉。

所以进程切换不到费不得已就不做。

那么怎么实现『进程切换不到费不得已就不做』呢？

首先进程被切换的条件是：进程执行完毕、分配给进程的CPU时间片结束，系统发生中断需要处理，或者进程等待必要的资源（进程阻塞）等。你想下，前面几种情况自然没有什么话可说，但是如果是在阻塞等待，是不是就浪费了。

其实阻塞的话我们的程序还有其他可执行的地方可以执行，不一定要傻傻的等！

所以就有了线程。

线程简单理解就是一个『微进程』，专门跑一个函数（逻辑流）。

所以我们就可以在编写程序的过程中将可以同时运行的函数用线程来体现了。

线程有两种类型，一种是由内核来管理和调度。

我们说，只要涉及需要内核参与管理调度的，代价都是很大的。这种线程其实也就解决了当一个进程中，某个正在执行的线程遇到阻塞，我们可以调度另外一个可运行的线程来跑，但是还是在同一个进程里，所以没有了进程切换。

还有另外一种线程，他的调度是由程序员自己写程序来管理的，对内核来说不可见。这种线程叫做『用户空间线程』。

协程可以理解就是一种用户空间线程。

协程，有几个特点：

* 协同，因为是由程序员自己写的调度策略，其通过协作而不是抢占来进行切换
* 在用户态完成创建，切换和销毁
* :warning: 从编程角度上看，协程的思想本质上就是控制流的主动让出（yield）和恢复（resume）机制
* 迭代器经常用来实现协程

说到这里，你应该明白协程的基本概念了吧？

## PHP实现协程

一步一步来，从解释概念说起！

## 可迭代对象

PHP5提供了一种定义对象的方法使其可以通过单元列表来遍历，例如用 foreach 语句。 

你如果要实现一个可迭代对象，你就要实现 Iterator 接口： 

```php
    <?php
    class MyIterator implements Iterator
    {
        private $var = array();
    
        public function __construct($array)
        {
            if (is_array($array)) {
                $this->var = $array;
            }
        }
    
        public function rewind() {
            echo "rewinding\n";
            reset($this->var);
        }
    
        public function current() {
            $var = current($this->var);
            echo "current: $var\n";
            return $var;
        }
    
        public function key() {
            $var = key($this->var);
            echo "key: $var\n";
            return $var;
        }
    
        public function next() {
            $var = next($this->var);
            echo "next: $var\n";
            return $var;
        }
    
        public function valid() {
            $var = $this->current() !== false;
            echo "valid: {$var}\n";
            return $var;
        }
    }
    
    $values = array(1,2,3);
    $it = new MyIterator($values);
    
    foreach ($it as $a => $b) {
        print "$a: $b\n";
    }
```

## 生成器

可以说之前为了拥有一个能够被 foreach 遍历的对象，你不得不去实现一堆的方法， yield 关键字就是为了简化这个过程。 

生成器提供了一种更容易的方法来实现简单的对象迭代，相比较定义类实现 Iterator 接口的方式，性能开销和复杂性大大降低。 

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

记住，一个函数中如果用了 yield ，他就是一个生成器，直接调用他是没有用的，不能等同于一个函数那样去执行！ 

所以， yield 就是 yield ，下次谁再说 yield 是协程，我肯定把你xxxx。 

## PHP协程

前面介绍协程的时候说了，协程需要程序员自己去编写调度机制，下面我们来看这个机制怎么写。

### 0）生成器正确使用

既然生成器不能像函数一样直接调用，那么怎么才能调用呢？

方法如下：

1. foreach他
1. send($value)
1. current / next...

### 1）Task实现

Task就是一个任务的抽象，刚刚我们说了协程就是用户空间协程，线程可以理解就是跑一个函数。

所以Task的构造函数中就是接收一个闭包函数，我们命名为 coroutine 。 

```php
    /**
     * Task任务类
     */
    class Task
    {
        protected $taskId;
        protected $coroutine;
        protected $beforeFirstYield = true;
        protected $sendValue;
    
        /**
         * Task constructor.
         * @param $taskId
         * @param Generator $coroutine
         */
        public function __construct($taskId, Generator $coroutine)
        {
            $this->taskId = $taskId;
            $this->coroutine = $coroutine;
        }
    
        /**
         * 获取当前的Task的ID
         * 
         * @return mixed
         */
        public function getTaskId()
        {
            return $this->taskId;
        }
    
        /**
         * 判断Task执行完毕了没有
         * 
         * @return bool
         */
        public function isFinished()
        {
            return !$this->coroutine->valid();
        }
    
        /**
         * 设置下次要传给协程的值，比如 $id = (yield $xxxx)，这个值就给了$id了
         * 
         * @param $value
         */
        public function setSendValue($value)
        {
            $this->sendValue = $value;
        }
    
        /**
         * 运行任务
         * 
         * @return mixed
         */
        public function run()
        {
            // 这里要注意，生成器的开始会reset，所以第一个值要用current获取
            if ($this->beforeFirstYield) {
                $this->beforeFirstYield = false;
                return $this->coroutine->current();
            } else {
                // 我们说过了，用send去调用一个生成器
                $retval = $this->coroutine->send($this->sendValue);
                $this->sendValue = ;
                return $retval;
            }
        }
    }
```

### 2）Scheduler实现

接下来就是 Scheduler 这个重点核心部分，他扮演着调度员的角色。 

```php
    /**
     * Class Scheduler
     */
    Class Scheduler
    {
        /**
         * @var SplQueue
         */
        protected $taskQueue;
        /**
         * @var int
         */
        protected $tid = 0;
    
        /**
         * Scheduler constructor.
         */
        public function __construct()
        {
            /* 原理就是维护了一个队列，
             * 前面说过，从编程角度上看，协程的思想本质上就是控制流的主动让出（yield）和恢复（resume）机制
             * */
            $this->taskQueue = new SplQueue();
        }
    
        /**
         * 增加一个任务
         *
         * @param Generator $task
         * @return int
         */
        public function addTask(Generator $task)
        {
            $tid = $this->tid;
            $task = new Task($tid, $task);
            $this->taskQueue->enqueue($task);
            $this->tid++;
            return $tid;
        }
    
        /**
         * 把任务进入队列
         *
         * @param Task $task
         */
        public function schedule(Task $task)
        {
            $this->taskQueue->enqueue($task);
        }
    
        /**
         * 运行调度器
         */
        public function run()
        {
            while (!$this->taskQueue->isEmpty()) {
                // 任务出队
                $task = $this->taskQueue->dequeue();
                $res = $task->run(); // 运行任务直到 yield
    
                if (!$task->isFinished()) {
                    $this->schedule($task); // 任务如果还没完全执行完毕，入队等下次执行
                }
            }
        }
    }
```

这样我们基本就实现了一个协程调度器。

你可以使用下面的代码来测试：

```php
    <?php
    function task1() {
        for ($i = 1; $i <= 10; ++$i) {
            echo "This is task 1 iteration $i.\n";
            yield; // 主动让出CPU的执行权
        }
    }
     
    function task2() {
        for ($i = 1; $i <= 5; ++$i) {
            echo "This is task 2 iteration $i.\n";
            yield; // 主动让出CPU的执行权
        }
    }
     
    $scheduler = new Scheduler; // 实例化一个调度器
    $scheduler->newTask(task1()); // 添加不同的闭包函数作为任务
    $scheduler->newTask(task2());
    $scheduler->run();
```

关键说下在哪里能用得到PHP协程。

```php
    function task1() {
            /* 这里有一个远程任务，需要耗时10s，可能是一个远程机器抓取分析远程网址的任务，我们只要提交最后去远程机器拿结果就行了 */
            remote_task_commit();
            // 这时候请求发出后，我们不要在这里等，主动让出CPU的执行权给task2运行，他不依赖这个结果
            yield;
            yield (remote_task_receive());
            ...
    }
     
    function task2() {
        for ($i = 1; $i <= 5; ++$i) {
            echo "This is task 2 iteration $i.\n";
            yield; // 主动让出CPU的执行权
        }
    }
```

这样就提高了程序的执行效率。

关于『系统调用』的实现，鸟哥已经讲得很明白，我这里不再说明。

### 3）协程堆栈

鸟哥文中还有一个协程堆栈的例子。

我们上面说过了，如果在函数中使用了 yield ，就不能当做函数使用。 

所以你在一个协程函数中嵌套另外一个协程函数：

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

这里的echoTimes是执行不了的！所以就需要协程堆栈。

不过没关系，我们改一改我们刚刚的代码。

把Task中的初始化方法改下，因为我们在运行一个Task的时候，我们要分析出他包含了哪些子协程，然后将子协程用一个堆栈保存。（C语言学的好的同学自然能理解这里，不理解的同学我建议去了解下进程的内存模型是怎么处理函数调用）

```php
    /**
         * Task constructor.
         * @param $taskId
         * @param Generator $coroutine
         */
        public function __construct($taskId, Generator $coroutine)
        {
            $this->taskId = $taskId;
            // $this->coroutine = $coroutine;
            // 换成这个，实际Task->run的就是stackedCoroutine这个函数，不是$coroutine保存的闭包函数了
            $this->coroutine = stackedCoroutine($coroutine); 
        }
```

当Task->run()的时候，一个循环来分析：

```php
    /**
     * @param Generator $gen
     */
    function stackedCoroutine(Generator $gen)
    {
        $stack = new SplStack;
    
        // 不断遍历这个传进来的生成器
        for (; ;) {
            // $gen可以理解为指向当前运行的协程闭包函数（生成器）
            $value = $gen->current(); // 获取中断点，也就是yield出来的值
    
            if ($value instanceof Generator) {
                // 如果是也是一个生成器，这就是子协程了，把当前运行的协程入栈保存
                $stack->push($gen);
                $gen = $value; // 把子协程函数给gen，继续执行，注意接下来就是执行子协程的流程了
                continue;
            }
    
            // 我们对子协程返回的结果做了封装，下面讲
            $isReturnValue = $value instanceof CoroutineReturnValue; // 子协程返回`$value`需要主协程帮忙处理
            
            if (!$gen->valid() || $isReturnValue) {
                if ($stack->isEmpty()) {
                    return;
                }
                // 如果是gen已经执行完毕，或者遇到子协程需要返回值给主协程去处理
                $gen = $stack->pop(); //出栈，得到之前入栈保存的主协程
                $gen->send($isReturnValue ? $value->getValue() : NULL); // 调用主协程处理子协程的输出值
                continue;
            }
    
            $gen->send(yield $gen->key() => $value); // 继续执行子协程
        }
    }
```

然后我们增加echoTime的结束标示：

```php
    class CoroutineReturnValue {
        protected $value;
     
        public function __construct($value) {
            $this->value = $value;
        }
         
        // 获取能把子协程的输出值给主协程，作为主协程的send参数
        public function getValue() {
            return $this->value;
        }
    }
    
    function retval($value) {
        return new CoroutineReturnValue($value);
    }
```

然后修改 echoTimes ： 

```php
    function echoTimes($msg, $max) {
        for ($i = 1; $i <= $max; ++$i) {
            echo "$msg iteration $i\n";
            yield;
        }
        yield retval("");  // 增加这个作为结束标示
    }
```

Task 变为： 

```php
    function task1()
    {
        yield echoTimes('bar', 5);
    }
```

这样就实现了一个协程堆栈，现在你可以举一反三了。

### 4）PHP7中yield from关键字

PHP7中增加了 yield from ，所以我们不需要自己实现携程堆栈，真实太好了。 

把Task的构造函数改回去：

```
    public function __construct($taskId, Generator $coroutine)
        {
            $this->taskId = $taskId;
            $this->coroutine = $coroutine;
            // $this->coroutine = stackedCoroutine($coroutine); //不需要自己实现了，改回之前的
        }
```

echoTimes 函数： 

```
    function echoTimes($msg, $max) {
        for ($i = 1; $i <= $max; ++$i) {
            echo "$msg iteration $i\n";
            yield;
        }
    }

```
task1 生成器： 

```
    function task1()
    {
        yield from echoTimes('bar', 5);
    }
```

这样，轻松调用子协程。

## 总结


[1]: https://segmentfault.com/a/1190000012457145

[3]: http://www.laruence.com/2015/05/28/3038.html
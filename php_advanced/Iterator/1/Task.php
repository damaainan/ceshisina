<?php 
class Task
{
    // 任务 ID
    protected $taskId;
    // 协程对象
    protected $coroutine;
    // send() 值
    protected $sendVal = null;
    // 是否首次 yield
    protected $beforeFirstYield = true;
    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }
    
    public function getTaskId() {
        return $this->taskId;
    }
    public function setSendValue($sendVal) {
        $this->sendVal = $sendVal;
    }
    public function run() {
        // 如之前提到的在send之前, 当迭代器被创建后第一次 yield 之前，一个 renwind() 方法会被隐式调用
        // 所以实际上发生的应该类似:
        // $this->coroutine->rewind();
        // $this->coroutine->send();
         
        // 这样 renwind 的执行将会导致第一个 yield 被执行, 并且忽略了他的返回值.
        // 真正当我们调用 yield 的时候, 我们得到的是第二个yield的值，导致第一个yield的值被忽略。
        // 所以这个加上一个是否第一次 yield 的判断来避免这个问题
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendVal);
            $this->sendVal = null;
            return $retval;
        }
    }
    public function isFinished() {
        return !$this->coroutine->valid();
    }
}


class Scheduler
{
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
 
    public function __construct() {
        $this->taskQueue = new SplQueue();
    }
 
    // （使用下一个空闲的任务id）创建一个新任务,然后把这个任务放入任务map数组里. 接着它通过把任务放入任务队列里来实现对任务的调度. 接着run()方法扫描任务队列, 运行任务.如果一个任务结束了, 那么它将从队列里删除, 否则它将在队列的末尾再次被调度。
    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }
 
    public function schedule(Task $task) {
        // 任务入队
        $this->taskQueue->enqueue($task);
    }
 
    public function run() {
        while (!$this->taskQueue->isEmpty()) {
            // 任务出队
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
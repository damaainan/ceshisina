<?php
// 与调度器之间通信
class Task {
    protected $taskId;
    protected $coroutine;
    protected $sendValue= null;
    protected $beforeFirstYield= true;
 
    public function __construct($taskId, Generator $coroutine){
        $this->taskId =$taskId;
        $this->coroutine =$coroutine;
    }
 
    public function getTaskId(){
        return $this->taskId;
    }
 
    public function setSendValue($sendValue){
        $this->sendValue =$sendValue;
    }
 
    public function run(){
        if($this->beforeFirstYield){
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        }else{
            $retval=$this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }
 
    public function isFinished(){
        return !$this->coroutine->valid();
    }
}


// 调度器
class Scheduler {
    protected $maxTaskId=0;
    protected $taskMap=[];// taskId => task
    protected $taskQueue;
 
    public function __construct(){
        $this->taskQueue =new SplQueue();
    }
 
    public function newTask(Generator $coroutine){
        $tid=++$this->maxTaskId;
        $task=new Task($tid,$coroutine);
        $this->taskMap[$tid]=$task;
        $this->schedule($task);
        return $tid;
    }
 
    public function schedule(Task $task){
        $this->taskQueue->enqueue($task);
    }
 
    public function run(){
        while(!$this->taskQueue->isEmpty()){
            $task=$this->taskQueue->dequeue();
            $retval=$task->run();
     
            if($retval instanceof SystemCall){
                $retval($task,$this);
                continue;
            }
     
            if($task->isFinished()){
                unset($this->taskMap[$task->getTaskId()]);
            }else{
                $this->schedule($task);
            }
        }
    }

     public function killTask($tid){
            if(!isset($this->taskMap[$tid])){
                return false;
            }
         
            unset($this->taskMap[$tid]);
         
            // This is a bit ugly and could be optimized so it does not have to walk the queue,
            // but assuming that killing tasks is rather rare I won't bother with it now
            foreach($this->taskQueue as$i=>$task){
                if($task->getTaskId()===$tid){
                    unset($this->taskQueue[$i]);
                    break;
                }
            }
         
            return true;
        }
}


class SystemCall {
    protected $callback;
 
    public function __construct(callable $callback){
        $this->callback =$callback;
    }
 
    public function __invoke(Task $task, Scheduler $scheduler){
        $callback=$this->callback;
        return $callback($task,$scheduler);
    }
}


function newTask(Generator $coroutine){
    return new SystemCall(
        function(Task $task, Scheduler $scheduler)use($coroutine){
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}
 
function killTask($tid){
    return new SystemCall(
        function(Task $task, Scheduler $scheduler)use($tid){
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}

function getTaskId(){
    return new SystemCall(function(Task $task, Scheduler $scheduler){
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}
function childTask(){
    $tid=(yield getTaskId());
    while(true){
        echo"Child task $tid still alive!\n";
        yield;
    }
}
 
function task(){
    $tid=(yield getTaskId());
    $childTid=(yield newTask(childTask()));
 
    for($i=1;$i<=6;++$i){
        echo"Parent task $tid iteration $i.\n";
        yield;
 
        if($i==3) yield killTask($childTid);
    }
}
 
$scheduler=new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
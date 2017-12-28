<?php  
//Coroutine.php  
//依赖swoole实现的定时器，也可以用其它方法实现定时器  
  
class Coroutine  
{  
    //可以根据需要更改定时器间隔，单位ms  
    const TICK_INTERVAL = 1;  
  
    private $routineList;  
  
    private $tickId = -1;  
  
    public function __construct()  
    {  
        $this->routineList = [];  
    }  
  
    public function start(Generator $routine)  
    {  
        $task = new Task($routine);  
        $this->routineList[] = $task;  
        $this->startTick();  
    }  
  
    public function stop(Generator $routine)  
    {  
        foreach ($this->routineList as $k => $task) {  
            if($task->getRoutine() == $routine){  
                unset($this->routineList[$k]);  
            }  
        }  
    }  
  
    private function startTick()  
    {  
        swoole_timer_tick(self::TICK_INTERVAL, function($timerId){  
            $this->tickId = $timerId;  
            $this->run();  
        });  
    }  
  
    private function stopTick()  
    {  
        if($this->tickId >= 0) {  
            swoole_timer_clear($this->tickId);  
        }  
    }  
  
    private function run()  
    {  
        if(empty($this->routineList)){  
            $this->stopTick();  
            return;  
        }  
  
        foreach ($this->routineList as $k => $task) {  
            $task->run();  
  
            if($task->isFinished()){  
                unset($this->routineList[$k]);  
            }  
        }  
    }  
      
}  
  
class Task  
{  
    protected $stack;  
    protected $routine;  
  
    public function __construct(Generator $routine)  
    {  
        $this->routine = $routine;  
        $this->stack = new SplStack();  
    }  
  
    /** 
     * [run 协程调度] 
     * @return [type]         [description] 
     */  
    public function run()  
    {  
        $routine = &$this->routine;  
  
        try {  
  
            if(!$routine){  
                return;  
            }  
  
            $value = $routine->current();   
  
            //嵌套的协程  
            if ($value instanceof Generator) {  
                $this->stack->push($routine);  
                $routine = $value;  
                return;  
            }  
  
            //嵌套的协程返回  
            if(!$routine->valid() && !$this->stack->isEmpty()) {  
                $routine = $this->stack->pop();  
            }  
  
            $routine->next();  
  
        } catch (Exception $e) {  
  
            if ($this->stack->isEmpty()) {  
                /* 
                    throw the exception  
                */  
                return;  
            }  
        }  
    }  
  
    /** 
     * [isFinished 判断该task是否完成] 
     * @return boolean [description] 
     */  
    public function isFinished()  
    {  
        return $this->stack->isEmpty() && !$this->routine->valid();  
    }  
  
    public function getRoutine()  
    {  
        return $this->routine;  
    }  
}  
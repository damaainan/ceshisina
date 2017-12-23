<?php

// 为Gen引入throw方法
class Gen
{
    public $isfirst = true;
    public $generator;

    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function valid()
    {
        return $this->generator->valid();
    }

    public function send($value = null)
    {
        if ($this->isfirst) {
            $this->isfirst = false;
            return $this->generator->current();
        } else {
            return $this->generator->send($value);
        }
    }
    // PHP7 之前 关键词不能用作名字
    public function throw_(\Exception $ex)
    {
        return $this->generator->throw($ex);
    }
}

final class AsyncTask
{
    public $gen;

    public function __construct(\Generator $gen)
    {
        $this->gen = new Gen($gen);
    }
    
    public function begin()
    {
        return $this->next();
    }

    // 这里添加第二个参数， 用来在迭代过程传递异常
    public function next($result = null, \Exception $ex = null)
    {
        if ($ex) {
            $this->gen->throw_($ex);
        }

        $ex = null;
        try {
            // send方法内部是一个resume的过程: 
            // 恢复execute_data上下文， 调用zend_execute_ex()继续执行,
            // 后续中op_array内可能会抛出异常
            $value = $this->gen->send($result);
        } catch (\Exception $ex) {}

        if ($ex) {
            if ($this->gen->valid()) {
                // 传递异常
                return $this->next(null, $ex);
            } else {
                throw $ex;
            }
        } else {
            if ($this->gen->valid()) {
                // 正常yield值
                return $this->next($value);
            } else {
                return $result;
            }
        }
    }
}



function newGen()
{
    $r1 = (yield 1);
    throw new \Exception("e");
    $r2 = (yield 2);
    yield 3;
}
$task = new AsyncTask(newGen());
try {
    $r = $task->begin();
    echo $r;
} catch (\Exception $ex) {
    echo $ex->getMessage(); // output: e
}
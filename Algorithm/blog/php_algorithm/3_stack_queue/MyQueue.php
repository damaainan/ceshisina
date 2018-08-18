<?php

class MyQueue {

    private $arr = [];
    // 队列的大小
    private $count = 0;
    // 有效数据的大小
    private $elements = 0;
    // 队头
    private $front = 0;
    // 队尾
    private $end = -1;

    public function __construct($count) {
        $this->count = $count;
    }

    // 添加数据，从队尾插入
    public function insert($value) {
        $this->arr[++$this->end] = $value;
        $this->elements++;
    }

    // 移除数据，从队头删除
    public function remove() {
        $this->elements--;
        return $this->arr[$this->front++];
    }

    // 查看数据，从队头查看
    public function peek() {
        return $this->arr[$this->front];
    }

    // 判断是否为空
    public function isEmpty() {
        return $this->elements == 0;
    }

    // 判断是否满
    public function isFull() {
        return $this->elements== $this->count;
    }
}


$queue = new MyQueue(5);
$queue->insert(20);
$queue->insert(10);
$queue->insert(5);
$queue->insert(100);
$queue->insert(100);
var_dump($queue->isEmpty());
echo '<br />';
var_dump($queue->isFull());
echo '<br />';
var_dump($queue->peek());
echo '<br />';

$queue->remove();

while(!$queue->isEmpty()) {
   echo $queue->remove() . '<br />'; 
}

// var_dump($stack->isEmpty());
// echo '<br />';
// var_dump($stack->isFull());





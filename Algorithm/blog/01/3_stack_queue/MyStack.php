<?php

class MyStack {

    private $arr = [];
    private $count = 0;
    private $top = -1;

    public function __construct($count) {
        $this->count = $count;
    }

    // 添加数据
    public function push($value) {
        $this->arr[++$this->top] = $value;
    }

    // 移除数据
    public function pop() {
        return $this->arr[$this->top--];
    }

    // 查看数据
    public function peek() {
        return $this->arr[$this->top];
    }

    // 判断是否为空
    public function isEmpty() {
        return $this->top == -1;
    }

    // 判断是否栈满
    public function isFull() {
        return $this->top == $this->count - 1;
    }
}


$stack = new MyStack(5);
$stack->push(20);
$stack->push(10);
$stack->push(5);
$stack->push(100);
$stack->push(100);
var_dump($stack->isEmpty());
echo '<br />';
var_dump($stack->isFull());
echo '<br />';
var_dump($stack->peek());
echo '<br />';

while(!$stack->isEmpty()) {
   echo $stack->pop() . '<br />'; 
}

var_dump($stack->isEmpty());
echo '<br />';
var_dump($stack->isFull());





<?php
 /**
  * 栈
  * 栈被称为一种后入先出（ LIFO， last-in-first-out） 的数据结构
  *
  * 入栈使用 push() 方法
  * pop() 方法虽然可以访问栈顶的元素， 但是调用该方法后， 栈顶元素也从栈中被永久性地删除了。
  * peek() 方法则只返回栈顶元素， 而不删除它
  * clear() 方法清除栈内所有元素
  * length 属性记录栈内元素的个数。
  */
 class Stack{
    protected $dataStore = [];
    protected $top = 0;

	function push($element) {
	    $this->dataStore[$this->top++] = $element;
	}

	function pop() {
	    return $this->dataStore[--$this->top];
	}

	function peek() {
		if($this->top==0){
			return "0 element!";
		}
	    return $this->dataStore[$this->top - 1];
	}

	function length() {
	    return $this->top;
	}

	function clear() {
	    $this->top = 0;
	}
}

$s = new Stack();
$s->push("David");
$s->push("Raymond");
$s->push("Bryan");
var_dump("length: " + $s->length());
var_dump($s->peek());
$popped = $s->pop();
var_dump("The popped element is: " + $popped);
var_dump($s->peek());
$s->push("Cynthia");
var_dump($s->peek());
$s->clear();
var_dump("length: " + $s->length());
var_dump($s->peek());
$s->push("Clayton");
var_dump($s->peek());
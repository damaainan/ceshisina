<?php
 /**
  * 队列是一种先进先出（ First-In-First-Out， FIFO） 的数据结构
  * enqueue() 方法向队尾添加一个元素
  * dequeue() 方法删除队首的元素
  * front back 读取队首和队尾的元素
  * toString() 方法显示队列内的所有元素
  * isEmpty判断队列是否为空
  */
 

 class Queue{
    protected $dataStore = [];

	function enqueue($element) {
	    $this->dataStore.push($element);
	}

	function dequeue() {
	    return $this->dataStore.shift();
	}

	function front() {
	    return $this->dataStore[0];
	}

	function back() {
	    return $this->dataStore[$this->dataStore->length - 1];
	}

	function toString() {
	    $retStr = "";
	    for ($i = 0; $i < $this->dataStore->length; ++$i) {
	        $retStr += $this->dataStore[$i] + "\n";
	    }
	    return $retStr;
	}

	function isEmpty() {
	    if ($this->dataStore->length == 0) {
	        return true;
	    } else {
	        return false;
	    }
	}

	function count() {
	    return $this->dataStore->length;
	}
}

$q = new Queue();
$q->enqueue("Meredith");
$q->enqueue("Cynthia");
$q->enqueue("Jennifer");
var_dump($q->toString());
$q->dequeue();
var_dump($q->toString());
var_dump("Front of queue: " + $q->front());
var_dump("Back of queue: " + $q->back());
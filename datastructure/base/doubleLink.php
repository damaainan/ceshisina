<?php
/**
 * 从链表的头节点遍历到尾节点很简单， 但反过来， 从后向前遍历则没那么简单
 */

class Node{
    public $element;
    public $next = null;
    public $previous = null;//当属性私有的时候不能被访问？
    public function __construct($element)
    {
        $this->element = $element;
    }
}
class DLList{
    protected $head;

    public function __construct()
    {
        $this->head = new Node("head");
    }

	function dispReverse() {
	    $currNode = $this->head;
	    $currNode = $this->findLast();
	    while ($currNode->previous !== null) {
	        var_dump($currNode->element);
	        $currNode = $currNode->previous;
	    }
	}

	function findLast() {
	    $currNode = $this->head;
	    while ($currNode->next === null) {
	        $currNode = $currNode->next;
	    }
	    return $currNode;
	}

	function remove($item) {
	    $currNode = $this->find($item);
	    if ($currNode->next !== null) {
	        $currNode->previous->next = $currNode->next;
	        $currNode->next->previous = $currNode->previous;
	        $currNode->next = null;
	        $currNode->previous = null;
	    }
	}
	//findPrevious 没用了， 注释掉
	/*function findPrevious(item) {
	$currNode = $this->head;
	while (!($currNode->next == null) &&
	($currNode->next.element != item)) {
	currNode = $currNode->next;
	} r
	eturn currNode;
	}*/
	function display() {
	    $currNode = $this->head;
	    while ($currNode->next !== null) {
	        var_dump($currNode->next->element);
	        $currNode = $currNode->next;
	    }
	}

	function find($item) {
	    $currNode = $this->head;
	    while ($currNode->element != $item) {
	        $currNode = $currNode->next;
	    }
	    return $currNode;
	}

	function insert($newElement, $item) {
	    $newNode = new Node($newElement);
	    $current = $this->find($item);
	    $newNode->next = $current->next;
	    $newNode->previous = $current;
	    $current->next = $newNode;
	}
}

$cities = new DLList();
$cities->insert("Conway", "head");
$cities->insert("Russellville", "Conway");
$cities->insert("Carlisle", "Russellville");
$cities->insert("Alma", "Carlisle");
$cities->display();
// print();
$cities->remove("Carlisle");
$cities->display();
// print();
$cities->dispReverse();
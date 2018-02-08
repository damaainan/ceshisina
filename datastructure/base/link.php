<?php
/**
 * 链表是由一组节点组成的集合。 每个节点都使用一个对象的引用指向它的后继。 指向另一个节点的引用叫做链
 *
 * 数组元素靠它们的位置进行引用， 链表元素则是靠相互之间的关系进行引用
 *
 * 遍历链表， 就是跟着链接， 从链表的首元素一直走到尾元素（ 但这不包含链表的头节点， 头节点常常用来作为链表的接入点）
 * 链表的尾元素指向一个 null 节点
 * 许多链表的实现都在链表最前面有一个特殊节点， 叫做头节点
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
class LList{
    public $head;
    public function __construct()
    {
        $this->head = new Node("head");
    }

	function findPrevious($item) {
	    $currNode = $this->head;
	    while ($currNode->next !== null && ($currNode->next->element != $item)) {
	        $currNode = $currNode->next;
	    }
	    return $currNode;
	}

	function remove($item) {
	    $prevNode = $this->findPrevious($item);
	    if ($prevNode->next !== null) {
	        $prevNode->next = $prevNode->next->next;
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
	    $current->next = $newNode;
	}

	function display() {
	    $currNode = $this->head;
	    while ($currNode->next !== null) {
	        var_dump($currNode->next->element);
	        $currNode = $currNode->next;
	    }
	}
}

$cities = new LList();
$cities->insert("Conway", "head");
$cities->insert("Russellville", "Conway");
$cities->insert("Alma", "Russellville");
$cities->insert("Carlisle","Conway");
$cities->display();
$cities->remove("Carlisle");
$cities->display();
<?php

// 单向链表

class Node {

    public $data;

    public $next;
    
    public function __construct($data) {
        $this->data = $data;
    }

    public function display() {
        echo $this->data . '<br />';
    }
}

class FirstLastLinkedList {

    // 头节点
    private $first;
    // 尾节点
    private $last;

    public function __construct() {
        $this->first = new Node(null);
        $this->last = new Node(null);
    }

    // 插入节点，在头节点后插入
    public function insertFirst($data) {
        $node = new Node($data);
        if ($this->first->next == null) {
            $this->last->next = $node;
        }
        $node->next = $this->first->next;
        $this->first->next = $node;
    }

    // 插入节点，在尾节点后插入
    public function insertLast($data) {
        $node = new Node($data);

        if ($this->last->next == null) {
            $this->first->next = $node;
        } else {
            $this->last->next->next = $node;
        }

        $this->last->next = $node;
    }

    // 删除节点，在头结点删除
    public function deleteFirst() {
        $tmp = $this->first->next;
        $this->first->next = $tmp->next;
    }

    // 查找
    public function find($data) {
        $current = $this->first->next;
        while ($current != null) {
            if ($current->data == $data) {
                return $current;
            }

            $current = $current->next;
        }

        return null;
    }

    // 删除节点
    public function delete($data) {
        $current = $this->first;
        while ($current != null) {
            if ($current->next->data == $data) {
                $tmp = $current->next;
                $current->next = $current->next->next;
                return $tmp;
            }

            $current = $current->next;
        }

        return null;
    }

    public function display() {
        $current = $this->first->next;
        while ($current != null) {
            $current->display();
            $current = $current->next;
        }
    }
}


$linkedList = new FirstLastLinkedList();
$linkedList->insertFirst(25);
$linkedList->insertFirst(30);
$linkedList->insertLast(20);
$linkedList->insertLast(10);
$linkedList->insertLast(5);
$linkedList->insertLast(100);

$linkedList->display();

// $linkedList->deleteFirst();
// $linkedList->display();

// print_r($linkedList->find(100));

// $linkedList->delete(150);
// $linkedList->display();






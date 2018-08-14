<?php

// 双端链表

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

class LinkedList {

    // 头节点
    private $first;

    public function __construct() {
        $this->first = new Node(null);
    }

    // 插入节点，在头节点后插入
    public function insertFirst($data) {
        $node = new Node($data);
        $node->next = $this->first->next;
        $this->first->next = $node;
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


$linkedList = new LinkedList();
$linkedList->insertFirst(20);
$linkedList->insertFirst(10);
$linkedList->insertFirst(5);
$linkedList->insertFirst(100);
$linkedList->insertFirst(150);

// $linkedList->display();

// $linkedList->deleteFirst();
// $linkedList->display();

// print_r($linkedList->find(100));

// $linkedList->delete(150);
// $linkedList->display();






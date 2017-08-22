<?php 

// 链表的实现


class ListNode  // 节点对象
{
    public $data = null;  //用来保存节点上的数据
    public $next = null;  //用来保存指向下一个节点的链接
    public function __construct(string $data = null)
    {
        $this->data = $data;
    }
}


class LinkedList
{

    private $_firstNode  = null; // 第一个节点 空对象
    private $_totalNodes = 0; // 记录节点总数

    public function insert(string $data = null)
    {
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) { // 初始情况
            $this->_firstNode = &$newNode; // 第一个节点变为新加的数据
        } else {
            $currentNode = $this->_firstNode;  // 已有数据的情况
            while ($currentNode->next !== null) { //需要插入在最后     1 -> 2  ->  3 ->  4 -> 5 ->  null
                $currentNode = $currentNode->next;
            }
            $currentNode->next = $newNode;
        }
        $this->_totalNode++; // 节点数加 1 
        return true;
    }

    public function getNthNode(int $n = 0)
    {
        $count = 1;
        if ($this->_firstNode !== null) {
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($count === $n) {
                    return $currentNode;
                }
                $count++;
                $currentNode = $currentNode->next;
            }
        }
    }

    public function search(string $data = null)
    {
        if ($this->_totalNode) { // 节点存在
            $currentNode = $this->_firstNode; // 从第一个节点开始寻找
            while ($currentNode !== null) { // 节点不为 null
                if ($currentNode->data === $data) { 
                    return $currentNode;
                }
                $currentNode = $currentNode->next; // 未找到 向下一个节点
            }
        }
        return false;
    }








}//end class
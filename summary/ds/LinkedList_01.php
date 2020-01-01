<?php 

/**
 * 单链表
 */

// 单链表节点类
class ListNode{
    public $data = null; // 节点数据
    public $next = null;
    public function __construct(string $data = null)
    {
        $this->data = $data;
    }
}

// 单链表类
class LinkedList{

	private $_firstNode  = null; // 首节点
    private $_totalNodes = 0; // 节点总数

    // 插入节点 加在最后一个节点后
    public function insert(string $data = null){
        $newNode = new ListNode($data); // 初始化节点数据
        if ($this->_firstNode === null) { // 判断是否首节点
            $this->_firstNode = &$newNode; // 首节点
        } else { 
            $currentNode = $this->_firstNode; // 
            while ($currentNode->next !== null) { // 如果不是最后一个节点
                $currentNode = $currentNode->next; // 后移一个
            }
            $currentNode->next = $newNode;
        }
        $this->_totalNodes++; // 节点数加 1 
        return true;
    }

    // 展示
    public function display(){
        echo "链表总节点数: " . $this->_totalNodes . "\r\n";
        $currentNode = $this->_firstNode;
        while ($currentNode !== null) {
            echo $currentNode->data . "\r\n"; // 输出节点数据
            $currentNode = $currentNode->next; // 后移
        }
    }

    // 插入首节点 
    public function insertAtFirst(string $data = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) { // 不存在首节点
            $this->_firstNode = &$newNode;
        } else {
            $currentFirstNode = $this->_firstNode;
            $this->_firstNode = &$newNode; // 新节点变为首节点
            $newNode->next    = $currentFirstNode; // 新节点指向原来的首节点
        }
        $this->_totalNodes++;
        return true;
    }

    // 查找数据
    public function search(string $data = null){
        if ($this->_totalNode) { // 判断是否存在节点
            $currentNode = $this->_firstNode; // 从首节点开始
            while ($currentNode !== null) {
                if ($currentNode->data === $data) {
                    return $currentNode;
                }
                $currentNode = $currentNode->next; // 后移
            }
        }
        return false;
    }

    // 在数据之前插入
    public function insertBefore(string $data = null, string $query = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode) {
            $previous    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    $newNode->next  = $currentNode; // 新节点指向当前节点
                    $previous->next = $newNode; // 前一节点指向新节点
                    $this->_totalNodes++;
                    break;
                }
                $previous    = $currentNode;
                $currentNode = $currentNode->next;
            }
        }
    }

    // 在数据之后插入
    public function insertAfter(string $data = null, string $query = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode) {
            $nextNode    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    if ($nextNode !== null) {
                        $newNode->next = $nextNode;
                    }
                    $currentNode->next = $newNode;
                    $this->_totalNodes++;
                    break;
                }
                $currentNode = $currentNode->next;
                $nextNode    = $currentNode->next;
            }
        }
    }


    // 删除首节点
    public function deleteFirst(){
        if ($this->_firstNode !== null) {
            if ($this->_firstNode->next !== null) { //存在两个以上节点
                $this->_firstNode = $this->_firstNode->next; // 首节点变为首节点的下一节点
            } else {
                $this->_firstNode = null; // 只存在首节点，首节点清空
            }
            $this->_totalNodes--; // 总数减一
            return true;
        }
        return false;
    }


    // 删除尾节点
    public function deleteLast(){
        if ($this->_firstNode !== null) {
            $currentNode = $this->_firstNode;
            if ($currentNode->next === null) { // 只有一个节点的情况
                $this->_firstNode = null;
            } else {
                $previousNode = null;
                while ($currentNode->next !== null) { // 找到尾节点
                    $previousNode = $currentNode;
                    $currentNode  = $currentNode->next;
                }
                $previousNode->next = null;
                $this->_totalNodes--;
                return true;
            }
        }
        return false;
    }

    // 删除某节点
    public function delete(string $query = null){
        if ($this->_firstNode) {
            $previous    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    if ($currentNode->next === null) {
                        $previous->next = null;
                    } else {
                        $previous->next = $currentNode->next;
                    }
                    $this->_totalNodes--;
                    break;
                }
                $previous    = $currentNode;
                $currentNode = $currentNode->next;
            }
        }
    }


    // 反转链表
    public function reverse(){
        if ($this->_firstNode !== null) {
            if ($this->_firstNode->next !== null) {
                $reversedList = null;
                $next         = null;
                $currentNode  = $this->_firstNode;
                while ($currentNode !== null) {
                    $next              = $currentNode->next;
                    $currentNode->next = $reversedList; // 当前链表指针指向前一个节点
                    $reversedList      = $currentNode; 
                    $currentNode       = $next; // 后移
                }
                $this->_firstNode = $reversedList;
            }
        }
    }


    // 根据索引获取节点数据 从一开始  返回节点对象
    public function getNthNode(int $n = 0){
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
            return false;
        }
        return false;
    }

}

$linkedList = new LinkedList();
$linkedList->insert("第一个节点");
$linkedList->insert("第二个节点");
$linkedList->insert("第三个节点");
$linkedList->display();
$linkedList->insertAtFirst("新第一个节点");
$linkedList->insertBefore("第三个节点之前", "第三个节点");
$linkedList->insertAfter("第三个节点之后", "第三个节点");
$linkedList->display();
$linkedList->deleteFirst();
$linkedList->deleteLast();
$linkedList->delete("第三个节点之前");
$linkedList->display();

$linkedList->reverse();
$linkedList->display();
echo "第一节点是: " . $linkedList->getNthNode(1)->data;
<?php 

/**
 * 双向链表
 */

// 双向链表节点类
class ListNode{
    public $data = null; // 数据
    public $next = null; // 后指针
    public $prev = null; // 前指针
    public function __construct(string $data = null)
    {
        $this->data = $data;
    }
}


/**
 * 双向链表类
 *
 * 首节点 prev 指针为 null
 * 尾节点 next 指针为 null
 */
class DoublyLinkedList{
    private $_firstNode = null; // 首节点
    private $_lastNode  = null; // 尾节点
    private $_totalNode = 0;

    // 在最前插入
    public function insertAtFirst(string $data = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) {
            $this->_firstNode = &$newNode;
            $this->_lastNode  = $newNode;
        } else {
            $currentFirstNode       = $this->_firstNode;
            $this->_firstNode       = &$newNode;
            $newNode->next          = $currentFirstNode; // 当前节点后移
            $currentFirstNode->prev = $newNode; // 当前节点 prev 指向新节点
        }
        $this->_totalNode++;
        return true;
    }


    // 在最后插入
    public function insertAtLast(string $data = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) {
            $this->_firstNode = &$newNode;
            $this->_lastNode  = $newNode;
        } else {
            $currentNode       = $this->_lastNode;
            $currentNode->next = $newNode;
            $newNode->prev     = $currentNode;
            $this->_lastNode   = $newNode;
        }
        $this->_totalNode++;
        return true;
    }

    public function insertBefore(string $data = null, string $query = null){
        $newNode = new ListNode($data);
        if ($this->_firstNode) {
            $previous    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    $newNode->next     = $currentNode;
                    $currentNode->prev = $newNode;
                    $previous->next    = $newNode;
                    $newNode->prev     = $previous;
                    $this->_totalNode++;
                    break;
                }
                $previous    = $currentNode;
                $currentNode = $currentNode->next;
            }
        }
    }

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
                    if ($currentNode === $this->_lastNode) {
                        $this->_lastNode = $newNode;
                    }
                    $currentNode->next = $newNode;
                    var_dump($currentNode);
                    var_dump($nextNode);
                    die;
                    $nextNode->prev    = $newNode;
                    $newNode->prev     = $currentNode;
                    $this->_totalNode++;
                    break;
                }
                $currentNode = $currentNode->next;
                $nextNode    = $currentNode->next;
            }
        }
    }

    public function deleteFirst(){
        if ($this->_firstNode !== null) {
            if ($this->_firstNode->next !== null) {
                $this->_firstNode       = $this->_firstNode->next;
                $this->_firstNode->prev = null;
            } else {
                $this->_firstNode = null;
            }
            $this->_totalNode--;
            return true;
        }
        return false;
    }

    public function deleteLast(){
        if ($this->_lastNode !== null) {
            $currentNode = $this->_lastNode;
            if ($currentNode->prev === null) {
                $this->_firstNode = null;
                $this->_lastNode  = null;
            } else {
                $previousNode       = $currentNode->prev;
                $this->_lastNode    = $previousNode;
                $previousNode->next = null;
                $this->_totalNode--;
                return true;
            }
        }
        return false;
    }

    public function delete(string $query = null){
        if ($this->_firstNode) {
            $previous    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    if ($currentNode->next === null) {
                        $previous->next = null;
                    } else {
                        $previous->next          = $currentNode->next;
                        $currentNode->next->prev = $previous;
                    }
                    $this->_totalNode--;
                    break;
                }
                $previous    = $currentNode;
                $currentNode = $currentNode->next;
            }
        }
    }

    public function displayForward(){
        echo "顺序展示: " . $this->_totalNode . "\n";
        $currentNode = $this->_firstNode;
        while ($currentNode !== null) {
            echo $currentNode->data . "\n";
            $currentNode = $currentNode->next;
        }
    }

    public function displayBackward(){
        echo "逆序展示: " . $this->_totalNode . "\n";
        $currentNode = $this->_lastNode;
        while ($currentNode !== null) {
            echo $currentNode->data . "\n";
            $currentNode = $currentNode->prev;
        }
    }

}

$linkedList = new DoublyLinkedList();
$linkedList->insertAtLast("第一个节点");
$linkedList->insertAtLast("第二个节点");
$linkedList->insertAtLast("第三个节点");
$linkedList->displayForward();
$linkedList->insertAtFirst("新第一个节点");
$linkedList->insertBefore("第三个节点之前", "第三个节点");
$linkedList->displayForward();
$linkedList->insertAfter("第三个节点之后", "第三个节点");
$linkedList->displayForward();
$linkedList->deleteFirst();
$linkedList->deleteLast();
$linkedList->delete("第三个节点之前");
$linkedList->displayForward();
$linkedList->displayBackward();
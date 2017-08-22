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
            while ($currentNode->next !== null) { //需要插入在最后     1 -> 2  ->  3 ->  4 -> 5 ->  null   1 是 firstNode
                $currentNode = $currentNode->next;
            }
            $currentNode->next = $newNode;
        }
        $this->_totalNodes++; // 节点数加 1 
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


    public function insertAtFirst(string $data = null)
    {
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) {
            $this->_firstNode = &$newNode;
        } else {
            $currentFirstNode = $this->_firstNode;
            $this->_firstNode = &$newNode;
            $newNode->next    = $currentFirstNode;
        }
        $this->_totalNodes++;
        return true;
    }
    public function insertBefore(string $data = null, string $query = null)
    {
        $newNode = new ListNode($data);
        if ($this->_firstNode) {
            $previous    = null;
            $currentNode = $this->_firstNode;
            while ($currentNode !== null) {
                if ($currentNode->data === $query) {
                    $newNode->next  = $currentNode;
                    $previous->next = $newNode;
                    $this->_totalNodes++;
                    break;
                }
                $previous    = $currentNode;
                $currentNode = $currentNode->next;
            }
        }
    }

    public function insertAfter(string $data = null, string $query = null)
    {
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

    public function display()
    {
        echo "Total book titles: " . $this->_totalNode . "\r\n";
        $currentNode = $this->_firstNode;
        while ($currentNode !== null) {
            echo $currentNode->data . "\r\n";
            $currentNode = $currentNode->next;
        }
    }
    public function reverse()//翻转链表  掉头即可
    {
        if ($this->_firstNode !== null) {
            if ($this->_firstNode->next !== null) {
                $reversedList = null;
                $next         = null;
                $currentNode  = $this->_firstNode;
                while ($currentNode !== null) {
                    $next              = $currentNode->next;
                    $currentNode->next = $reversedList;
                    $reversedList      = $currentNode;
                    $currentNode       = $next;
                }
                $this->_firstNode = $reversedList;
            }
        }
    }


    public function deleteFirst()
    {
        if ($this->_firstNode !== null) {
            if ($this->_firstNode->next !== null) {
                $this->_firstNode = $this->_firstNode->next;
            } else {
                $this->_firstNode = null;
            }
            $this->_totalNodes--;
            return true;
        }
        return false;
    }

    public function deleteLast()
    {
        if ($this->_firstNode !== null) {
            $currentNode = $this->_firstNode;
            if ($currentNode->next === null) {
                $this->_firstNode = null;
            } else {
                $previousNode = null;
                while ($currentNode->next !== null) {
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

    public function delete(string $query = null)
    {
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


}//end class


$BookTitles = new LinkedList();
$BookTitles->insert("Introduction to Algorithm");
$BookTitles->insert("Introduction to PHP and Data structures");
$BookTitles->insert("Programming Intelligence");

/* 结构如下 多层嵌套
class LinkedList#1 (2) {
  private $_firstNode =>
  class ListNode#2 (2) {
    public $data =>
    string(25) "Introduction to Algorithm"
    public $next =>
    class ListNode#3 (2) {
      public $data =>
      string(39) "Introduction to PHP and Data structures"
      public $next =>
      NULL
    }
  }
  private $_totalNodes =>
  int(2)
}
 */


var_dump($BookTitles);
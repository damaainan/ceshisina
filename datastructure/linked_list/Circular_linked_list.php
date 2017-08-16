<?php

class ListNode
{
    public $data = null;
    public $next = null;
    public function __construct(string $data = null)
    {
        $this->data = $data;
    }
}

class CircularLinkedList
{
    private $_firstNode = null;
    private $_totalNode = 0;
    public function insertAtEnd(string $data = null)
    {
        $newNode = new ListNode($data);
        if ($this->_firstNode === null) {
            $this->_firstNode = &$newNode;
        } else {
            $currentNode = $this->_firstNode;
            while ($currentNode->next !== $this->_firstNode) {
                $currentNode = $currentNode->next;
            }
            $currentNode->next = $newNode;
        }
        $newNode->next = $this->_firstNode;
        $this->_totalNode++;
        return true;
    }

    public function display()
    {
        echo "Total book titles: " . $this->_totalNode . "\r\n";
        $currentNode = $this->_firstNode;
        while ($currentNode->next !== $this->_firstNode) {
            echo $currentNode->data . "\r\n";
            $currentNode = $currentNode->next;
        }
        if ($currentNode) {
            echo $currentNode->data . "\r\n";
        }
    }

}

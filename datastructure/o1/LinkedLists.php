<?php

# Linked list is a group of elements, each of them have a link (poiner, reference) to the next (linked list) 
# and previous (double linked list) element. Elements can not be accessed by index. Elemets are accessed only in consequence.
# Lists are better than arrays in adding elements (faster in memory). But is lists are implemented using arrays there is no difference.
# Linear Search complexity: O(n) (arrays are better( O(log n)) in binary search)
# 
# Time complexity:
# insert: O(1)
# find: O(n)
#
# Animation: https://www.cs.usfca.edu/~galles/visualization/StackLL.html

# 1. Built in implementstion
$a = new SplDoublyLinkedList();

$a->push('hello');
$a->push('kitty');
$a->push(',');
$a->push('how');
$a->push('are');
$a->push('you');
$a->push('?');

foreach($a as $item){
  echo "${item} ";
}

echo "{$a->count()}";

while($a->count()){
  $x = $a->shift();
  echo "${x} ";
}

echo "{$a->count()}";

# 2. Test implementation
class ListItem{
  private $next = null;
  private $previous = null;
  private $value = null;
  
  public function __construct($value){
    $this->value = $value;
  }
  
  public function getValue(){
    return $this->value;
  }
  
  public function setNext(ListItem $item){
    $this->next = $item;
  }
  
  public function setPrevious(ListItem $item){
    $this->previous = $item;
  }
  
  public function getNext(){
    return $this->next;
  }
  
  public function getPrevious(){
    return $this->previous;
  }
}
class DoublyLinkedList{
  
  private $_head = null;
  private $_tail = null;
  
  public function push($value){
    $item = new ListItem($value);
    if($this->_tail){
      $this->_tail->setNext($item);
      $item->setPrevious( $this->_tail);
    }
    else{
      $this->_head = &$item;
    }
    $this->_tail = $item;
  }
  
  public function unshift($value){
    //todo
  }
  
  public function shift(){
    //todo
  }
  
  public function pop(){
    if($this->_tail){
        $value = $this->_tail->getValue();
        $this->_tail = $this->_tail->getPrevious();
    }
    else{
        $value = null;
    }
    return $value;
  }
}


$a = new DoublyLinkedList();
$a->push('hello');
$a->push('kitty');
$a->push(',');
$a->push('how');
$a->push('are');
$a->push('you');
$a->push('?');

while($x = $a->pop()){
  echo "${x} ";
}
$a->push('hello');
$a->push('kitty');
$a->push(',');
$a->push('how');
$a->push('are');
$a->push('you');
$a->push('?');

while($x = $a->pop()){
  echo "${x} ";
}

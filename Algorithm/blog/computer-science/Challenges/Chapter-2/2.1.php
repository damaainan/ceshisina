<?php

# remove duplicates from an unsorted linked list without a temporary buffer

interface LinkedListInterface{
    public function head();
    public function add();
}
interface LinkedListNodeInterface{
    public function next();
    //public $next;
}

function removeDuplicates(LinkedList &$list){
  
  //Questions:
  // should I create a list implementation? - no
  // is the hash table considered as a buffer? - yes
  
  //Solution:
  // for each element in the liked list  O(n)
    // write true to the hash table if the record was found O(1)
    // if there is already true, remove the element O(1)
  
  //without extra space we will use runners and double loop O(pow(n,2))
  
  $current = $list->head();
    
  while($current != NULL){
    
    $runner = $current;
    while($runner != NULL){
      if($runner->next() == $current){
        $runner->next = $runner->next->next;
      }
      else{
        $runner = $runner->next();
    }
  
    $current = $current->next();
  }
  
}


$list = new LinkedList();
$list->add(1);
$list->add(2);
$list->add(13);
$list->add(1);
$list->add(2);
$list->add(1);
$list->add(11);

removeDuplicates($list);
var_dump($list);

//Brute force: O(n) Space O(n)
//Final: 
//BCR: O(n)

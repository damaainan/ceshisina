<?php

# partition

function partition(LinkedList $list, $border){
  
  //Questions:
  // does the order matter? - no
  
  // for each element O(n)
    // if it's less than x, prepend it to the list [prev_element.next = element.next, element.next = first_element.next, first_element.next = element ]
    // var found, unless it true don't move them around
    
    
    //other way - loop through the list and detach element and attach them to other lists and then merg two lists

  $current = $list->head;
  $border_reached = false;
    
  while($current){
    if($current->value < $border){
      if($border_reached){
        $current->next = $current->previous->next;
        //if singly linked, loop through the list until the record with next == current will be found, then change next to current->next
        $current->previous = null;
        $current->next = $list->head;
        $list->head = $current;
      }
    }
    else{
      $border_reached = true;
    }
    $current = $current->next();
  }
  
}

// 1 -> 10 -> 3 -> 4 -> 5 -> 8 -> 7 -> 5  ||  4
// 1 -> 3 -> 4 -> 10 -> 5 -> 8 -> 7 -> 5

$list = new LinkedList();

//Brute force: 
//Final: O(n) Space O(1)
//BCR: O(n)

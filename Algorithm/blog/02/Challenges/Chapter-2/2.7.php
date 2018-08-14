<?php

# intersection

function getKthNode(ListNode $current, $k){
  while($k > 0){
      $current = $current->next;
      $k--;
    }
  return $current;
}

function getTailAndSize(ListNode $current){
  $tail = null;
  $size = 0;
  while($current != null){
    $current = $current->next;
    $tail = $current;
    $size ++;
  }
  return compact('tail', 'size');
}

function findIntersection(SinglyLinkedList $list1, SinglyLinkedList $list2){
  
  //traverse each of them till the end, if they have the same tail, they intersect
  //traverse them in parallel from lenLongest-lenSmallest, comparing nodes on each
  
  $list1_results = getTailAndSize($list1->head);
  $list2_results = getTailAndSize($list2->head);
  
  if($list1_results['tail'] == $list2_results['tail']){ // they do intersect
    $skip = abs($list1_results['size'] - $list2_results['size']);
    
    $longest_list = $list2_results['size'] >= $list1_results['size'] ? $list2 : $list1;
    $shortest_list = $list2_results['size'] >= $list1_results['size'] ? $list1 : $list2;
    
    $first_list_pointer = getKthNode($longest_list->head, $skip);
    $second_list_pointer = $shortest_list->head;
    
    while($first_list_pointer != $second_list_pointer){
      $first_list_pointer = $first_list_pointer->next;
      $second_list_pointer = $second_list_pointer->next;
    }
    
    return $first_list_pointer;
  }
  return false;
  
}

//Brute Force: O(mk)
//Final: O(m+k) Space O(1)
//BCR: O(m + k)

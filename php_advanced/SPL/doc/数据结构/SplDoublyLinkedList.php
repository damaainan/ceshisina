<?php

$splDoubleLinkedList = new SplDoublyLinkedList();
$splDoubleLinkedList->push('a');
$splDoubleLinkedList->push('3');
$splDoubleLinkedList->push('v');
$splDoubleLinkedList->push('1');
$splDoubleLinkedList->push('p');
//First of all, we need rewind list
$splDoubleLinkedList->rewind();
//Use while, check if the list has valid node
while ($splDoubleLinkedList->valid()){
     //Print current node's value
     echo $splDoubleLinkedList->current()."\n";
     //Turn the cursor to next node
     $splDoubleLinkedList->next();
 }
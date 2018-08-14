<?php

# Last In First Out (LIFO) structure
# Abstract data type
# 3 methods: push, pop, peek
# Could be implemented depending of a language natively, with a list or array behind the curtain
# 
# Time complexity:
# insert: O(1)
# find (next): O(1)
#
# Animation:
# LinkedList: https://www.cs.usfca.edu/~galles/visualization/StackLL.html
# Array: https://www.cs.usfca.edu/~galles/visualization/StackArray.html

$a = new SplStack();
$a->push('1');
$a->push('2');
$a->push('3');

while($a->count()){
  $x = $a->pop();
  echo "{$x} ";
}



$q = new SplStack();
$q->push(1);
$q->push(2);
$q->push(3);
$q->setIteratorMode(SplDoublyLinkedList:: IT_MODE_DELETE | SplDoublyLinkedList::IT_MODE_LIFO);

foreach($q as $x){
    echo $x;
}

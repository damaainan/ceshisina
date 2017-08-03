<?php

# First in, first out (FIFO)
# Abstract data type
# methods put, shift
# 
# Time complexity:
# insert: O(1)
# find (next): O(1)
#
# Animation:
# Array: https://www.cs.usfca.edu/~galles/visualization/QueueArray.html
# LinkedList: https://www.cs.usfca.edu/~galles/visualization/QueueLL.html

$q = new SplQueue();
$q->enqueue(1);
$q->enqueue(2);
$q->enqueue(3);
$q->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);

foreach($q as $x){
    echo $x;
}

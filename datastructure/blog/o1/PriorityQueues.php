<?php

# A queue where elements are sorted internally by priority and added later element with higher priority could be removed earlier
#
#

class MyPriorityQueue extends SplPriorityQueue{
    public function compare($priority1, $priority2){
        if ($priority1 === $priority2) return 0; 
        return $priority1 < $priority2 ? -1 : 1; 
    }
}

$q = new MyPriorityQueue();
$q->insert("A", 5);
$q->insert("B", 10);
$q->insert("C", 5);

$q->top(); 
while($q->valid()){
    echo "{$q->current()} ";
    $q->next();
}

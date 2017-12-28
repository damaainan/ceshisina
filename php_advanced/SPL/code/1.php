<?php 
//栈（后进先出）
$stack =new SplStack();
$stack->push("data1");
$stack->push("data2");
echo $stack->pop();
echo $stack->pop();

//队列(先进先出)
$queue = new SplQueue();
$queue->enqueue("aaaaaa");
$queue->enqueue("bbbbbb");
echo $queue->dequeue();
echo $queue->dequeue();

//最小堆(从小到大)
$heap = new SplMinHeap();
$heap->insert("555");
$heap->insert("444");
echo $heap->extract();
echo $heap->extract();

//最大堆(从大到小)
$maxHeap = new SplMaxHeap();
$maxHeap->insert(888);
$maxHeap->insert(999);
echo $maxHeap->extract();
echo $maxHeap->extract();
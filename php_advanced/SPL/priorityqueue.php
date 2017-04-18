<?php 
// 优先队列SplPriorityQueue是基于堆(后文介绍)实现


$pq = new SplPriorityQueue();
  
$pq->insert('a', 10);
$pq->insert('b', 1);
$pq->insert('c', 8);
  
echo $pq->count() .PHP_EOL; //3
echo $pq->current() . PHP_EOL; //a
  
/**
 * 设置元素出队模式
 * SplPriorityQueue::EXTR_DATA 仅提取值
 * SplPriorityQueue::EXTR_PRIORITY 仅提取优先级
 * SplPriorityQueue::EXTR_BOTH 提取数组包含值和优先级
 */
$pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);
  
while($pq->valid()) {
  print_r($pq->current()); //a c b
  $pq->next();
}

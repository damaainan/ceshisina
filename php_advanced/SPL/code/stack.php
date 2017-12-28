<?php 
// 栈(Stack)是一种特殊的线性表，因为它只能在线性表的一端进行插入或删除元素(即进栈和出栈)
// SplStack就是继承双链表(SplDoublyLinkedList)实现栈


//把栈想象成一个颠倒的数组
$stack = new SplStack();
/**
 * 可见栈和双链表的区别就是IteratorMode改变了而已，栈的IteratorMode只能为：
 * （1）SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP （默认值,迭代后数据保存）
 * （2）SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_DELETE （迭代后数据删除）
 */
$stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_DELETE);
$stack->push('a');
$stack->push('b');
$stack->push('c');
  
$stack->pop(); //出栈
  
$stack->offsetSet(0, 'first');//index 为0的是最后一个元素
  
foreach($stack as $item) {
 echo $item . PHP_EOL; // first a
}
  
print_R($stack); //测试IteratorMode
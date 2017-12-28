<?php 
/**
 * 堆(Heap)就是为了实现优先队列而设计的一种数据结构，它是通过构造二叉堆(二叉树的一种)实现。根节点最大的堆叫做最大堆或大根堆，根节点最小的堆叫做最小堆或小根堆。二叉堆还常用于排序(堆排序)。
如下：最小堆（任意节点的优先级不小于它的子节点）

最大堆(SplMaxHeap)和最小堆(SplMinHeap)就是继承它实现的。最大堆和最小堆并没有额外的方法
 */

class MySimpleHeap extends SplHeap
{
  //compare()方法用来比较两个元素的大小，绝对他们在堆中的位置
  public function compare( $value1, $value2 ) {
    return ( $value1 - $value2 );
  }
}
  
$obj = new MySimpleHeap();
$obj->insert( 4 );
$obj->insert( 8 );
$obj->insert( 1 );
$obj->insert( 0 );
  
echo $obj->top(); //8
echo $obj->count(); //4
  
foreach( $obj as $number ) {
 echo $number;
}


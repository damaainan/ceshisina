<?php 

//二叉树的广度优先遍历
//使用一个队列实现
class Node {
 public $data = null;
 public $left = null;
 public $right = null;
}

//@param $btree 二叉树根节点
function breadth_first_traverse($btree) {
 $traverse_data = array();
 $queue = array();
 array_unshift($queue, $btree); //根节点入队
 while (!empty($queue)) { //持续输出节点，直到队列为空
   $cnode = array_pop($queue); //队尾元素出队
   $traverse_data[] = $cnode->data;
   //左节点先入队，然后右节点入队
   if ($cnode->left != null) array_unshift($queue, $cnode->left);
   if ($cnode->right != null) array_unshift($queue, $cnode->right);
 }
 return $traverse_data;
}


//深度优先遍历,使用一个栈实现
function depth_first_traverse($btree) {
$traverse_data = array();
$stack = array();
array_push($stack, $btree);
while (!empty($stack)) {
  $cnode = array_pop($stack);
  $traverse_data[] = $cnode->data;
  if ($cnode->right != null) array_push($stack, $cnode->right);
  if ($cnode->left != null) array_push($stack, $cnode->left);
}
return $traverse_data;
}

$root = new Node();
$node1 = new Node();
$node2 = new Node();
$node3 = new Node();
$node4 = new Node();
$node5 = new Node();
$node6 = new Node();
$root->data = 1;
$node1->data = 2;
$node2->data = 3;
$node3->data = 4;
$node4->data = 5;
$node5->data = 6;
$node6->data = 7;
$root->left = $node1;
$root->right = $node2;
$node1->left = $node3;
$node1->right = $node4;
$node2->left = $node5;
$node2->right = $node6;
$traverse = breadth_first_traverse($root);
print_r($traverse);
echo "";
$traverse = depth_first_traverse($root);
print_r($traverse);
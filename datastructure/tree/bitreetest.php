<?php 
//PHP实现的线索二叉树及二叉树遍历方法,结合实例形式较为详细的分析了线索二叉树的定义,创建,判断与遍历等技巧
//
//
require 'biTree.php';
  $str = 'ko#be8#tr####acy#####';
  $tree = new BiTree($str);
  $tree->createThreadTree();
  echo $tree->threadList() . "\n";//从第一个结点开始遍历线索二叉树
  echo $tree->threadListReserv();//从最后一个结点开始反向遍历
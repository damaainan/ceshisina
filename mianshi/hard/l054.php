<?php
/**
 *Closest Binary Search Tree Value II 最近的二分搜索树的值之二


Given a non-empty binary search tree and a target value, find k values in the BST that are closest to the target.

Note:

Given target value is a floating point.
You may assume k is always valid, that is: k ≤ total nodes.
You are guaranteed to have only one unique set of k values in the BST that are closest to the target.


Follow up:
Assume that the BST is balanced, could you solve it in less than O(n) runtime (where n = total nodes)?

Hint:

1. Consider implement these two helper functions:
　　i. getPredecessor(N), which returns the next smaller node to N.
　　ii. getSuccessor(N), which returns the next larger node to N.
2. Try to assume that each node has a parent pointer, it makes the problem much easier.
3. Without parent pointer we just need to keep track of the path from the root to the current node using a stack.
4. You would need two stacks to track the path in finding predecessor and successor node separately.



这道题是之前那道Closest Binary Search Tree Value的拓展，那道题只让我们找出离目标值最近的一个节点值，而这道题让我们找出离目标值最近的k个节点值，难度瞬间增加了不少，我最先想到的方法是用中序遍历将所有节点值存入到一个一维数组中，由于二分搜索树的性质，这个一维数组是有序的，然后我们再在有序数组中需要和目标值最近的k个值就简单的多
 */
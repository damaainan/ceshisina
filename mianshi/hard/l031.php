<?php
/**
 *Binary Tree Maximum Path Sum 求二叉树的最大路径和


Given a binary tree, find the maximum path sum.

The path may start and end at any node in the tree.

For example:
Given the below binary tree,

1
/ \
2   3


Return 6.



这道求二叉树的最大路径和是一道蛮有难度的题，难就难在起始位置和结束位置可以为任意位置，我当然是又不会了，于是上网看看大神们的解法，看了很多人的都没太看明白，最后发现了网友Yu's Coding Garden的博客，感觉讲解还比较清楚，像这种类似数的遍历的题，一般来说都需要用DFS来求解，我们先来看一个简单的例子：

4
/ \
11 13
/ \
7  2

对于一条路径来说，可以分为两种情况，一是当顶节点是当前点，另一种是顶节点是父节点。例如，对于节点11来说，

1. 当顶节点是当前节点，对于节点11，路径为 7->11->2

2. 当顶节点是父节点4时，对于节点11，路径为 7->11->4->13

对于DFS来说，其递归过程中必定会对某节点的子节点调用，那么其返回值应该适用于上面第二种情况，但是最终结果肯定是第一种情况，那么如果保存并更新最终结果呢，我们可以将其放在参数中传递。那么对于任意一个节点n来说，

DFS(n) = max(DFS(n->left) + n->val, DFS(n->right) + n->val, n->val);

top(n) = max(DFS(n), DFS(n->left) + DFS(n->right) + n->val, n->val);

res = max(res, top(n));

理解了上述三个公式，这道题就基本理解了
 */
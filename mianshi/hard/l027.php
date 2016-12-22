<?php
/**
 *Recover Binary Search Tree 复原二叉搜索树


Two elements of a binary search tree (BST) are swapped by mistake.

Recover the tree without changing its structure.

Note:
A solution using O(n) space is pretty straight forward. Could you devise a constant space solution?



confused what "{1,#,2,3}" means? > read more on how binary tree is serialized on OJ.



这道题要求我们复原一个二叉搜索树，说是其中有两个的顺序被调换了，题目要求上说O(n)的解法很直观，这种解法需要用到递归，用中序遍历树，并将所有节点存到一个一维向量中，把所有节点值存到另一个一维向量中，然后对存节点值的一维向量排序，在将排好的数组按顺序赋给节点。这种最一般的解法可针对任意个数目的节点错乱的情况，
 */
<?php
/**
 *Populating Next Right Pointers in Each Node II 每个节点的右向指针之二


Follow up for problem "Populating Next Right Pointers in Each Node".

What if the given tree could be any binary tree? Would your previous solution still work?

Note:

You may only use constant extra space.


For example,
Given the following binary tree,

1
/  \
2    3
/ \    \
4   5    7


After calling your function, the tree should look like:

1 -> NULL
/  \
2 -> 3 -> NULL
/ \    \
4-> 5 -> 7 -> NULL


这道是之前那道Populating Next Right Pointers in Each Node 每个节点的右向指针的延续，原本的完全二叉树的条件不再满足，但是整体的思路还是很相似，仍然有递归和非递归的解法。我们先来看递归的解法，这里由于子树有可能残缺，故需要平行扫描父节点同层的节点，找到他们的左右子节点。
 */
<?php
/**
 *Copy List with Random Pointer 拷贝带有随机指针的链表


A linked list is given such that each node contains an additional random pointer which could point to any node in the list or null.

Return a deep copy of the list.



这道链表的深度拷贝题的难点就在于如何处理随机指针的问题，由于每一个节点都有一个随机指针，这个指针可以为空，也可以指向链表的任意一个节点，如果我们在每生成一个新节点给其随机指针赋值时，都要去便利原链表的话，OJ上肯定会超时，所以我们可以考虑用Hash map来缩短查找时间，第一遍遍历生成所有新节点时同时建立一个原节点和新节点的哈希表，第二遍给随机指针赋值时，查找时间是常数级。
 */
<?php
/**
 *Word Search II 词语搜索之二


Given a 2D board and a list of words from the dictionary, find all words in the board.

Each word must be constructed from letters of sequentially adjacent cell, where "adjacent" cells are those horizontally or vertically neighboring. The same letter cell may not be used more than once in a word.

For example,
Given words = ["oath","pea","eat","rain"] and board =

[
['o','a','a','n'],
['e','t','a','e'],
['i','h','k','r'],
['i','f','l','v']
]
Return ["eat","oath"].

Note:
You may assume that all inputs are consist of lowercase letters a-z.

click to show hint.

You would need to optimize your backtracking to pass the larger test. Could you stop backtracking earlier?

If the current candidate does not exist in all words' prefix, you could stop backtracking immediately. What kind of data structure could answer such query efficiently? Does a hash table work? Why or why not? How about a Trie? If you would like to learn how to implement a basic trie, please work on this problem: Implement Trie (Prefix Tree) first.



这道题是在之前那道Word Search 词语搜索的基础上做了些拓展，之前是给一个单词让判断是否存在，现在是给了一堆单词，让返回所有存在的单词，在这道题最开始更新的几个小时内，用brute force是可以通过OJ的，就是在之前那题的基础上多加一个for循环而已，但是后来出题者其实是想考察字典树的应用，所以加了一个超大的test case，以至于brute force无法通过，强制我们必须要用字典树来求解。LeetCode中有关字典树的题还有 Implement Trie (Prefix Tree) 实现字典树(前缀树)和Add and Search Word - Data structure design 添加和查找单词-数据结构设计，那么我们在这题中只要实现字典树中的insert功能就行了，查找单词和前缀就没有必要了，然后DFS的思路跟之前那道Word Search 词语搜索基本相同
 */
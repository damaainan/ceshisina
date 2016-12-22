<?php
/**
 *Substring with Concatenation of All Words 串联所有单词的子串


You are given a string, s, and a list of words, words, that are all of the same length. Find all starting indices of substring(s) in s that is a concatenation of each word in words exactly once and without any intervening characters.

For example, given:
s: "barfoothefoobarman"
words: ["foo", "bar"]

You should return the indices: [0,9].
(order does not matter).



这道题让我们求串联所有单词的子串，就是说给定一个长字符串，再给定几个长度相同的单词，让我们找出串联给定所有单词的子串的起始位置，还是蛮有难度的一道题。这道题我们需要用到两个哈希表，第一个哈希表先把所有的单词存进去，然后从开头开始一个个遍历，停止条件为当剩余字符个数小于单词集里所有字符的长度。这时候我们需要定义第二个哈希表，然后每次找出给定单词长度的子串，看其是否在第一个哈希表里，如果没有，则break，如果有，则加入第二个哈希表，但相同的词只能出现一次，如果多了，也break。如果正好匹配完给定单词集里所有的单词，则把i存入结果中，具体参见代码如下：
 */
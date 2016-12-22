<?php
/**
 *Minimum Unique Word Abbreviation 最短的独一无二的单词缩写


A string such as "word" contains the following abbreviations:

["word", "1ord", "w1rd", "wo1d", "wor1", "2rd", "w2d", "wo2", "1o1d", "1or1", "w1r1", "1o2", "2r1", "3d", "w3", "4"]
Given a target string and a set of strings in a dictionary, find an abbreviation of this target string with thesmallest possible length such that it does not conflict with abbreviations of the strings in the dictionary.

Each number or letter in the abbreviation is considered length = 1. For example, the abbreviation "a32bc" has length = 4.

Note:

In the case of multiple answers as shown in the second example below, you may return any one of them.
Assume length of target string = m, and dictionary size = n. You may assume that m ≤ 21, n ≤ 1000, and log2(n) + m ≤ 20.


Examples:

"apple", ["blade"] -> "a4" (because "5" or "4e" conflicts with "blade")

"apple", ["plain", "amber", "blade"] -> "1p3" (other valid answers include "ap3", "a3e", "2p2", "3le", "3l1").


这道题实际上是之前那两道Valid Word Abbreviation和Generalized Abbreviation的合体，我们的思路其实很简单，首先找出target的所有的单词缩写的形式，然后按照长度来排序，小的排前面，我们用优先队列来自动排序，里面存一个pair，保存单词缩写及其长度，然后我们从最短的单词缩写开始，跟dictionary中所有的单词一一进行验证，利用Valid Word Abbreviation中的方法，看其是否是合法的单词的缩写，如果是，说明有冲突，直接break，进行下一个单词缩写的验证
 */
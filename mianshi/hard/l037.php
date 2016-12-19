<?php
/**
 *Word Break II 拆分词句之二


Given a string s and a dictionary of words dict, add spaces in s to construct a sentence where each word is a valid dictionary word.

Return all such possible sentences.

For example, given
s = "catsanddog",
dict = ["cat", "cats", "and", "sand", "dog"].

A solution is ["cats and dog", "cat sand dog"].



这道题是之前那道Word Break 拆分词句的拓展，那道题只让我们判断给定的字符串能否被拆分成字典中的词，而这道题加大了难度，让我们求出所有可以拆分成的情况，就像题目中给的例子所示。可以用DFS的套路来解题，但是不是一般的brute force，我们必须进行剪枝优化，因为按照惯例OJ的最后一个test case都是巨长无比的，很容易就Time Limit Exceeded。那么如何进行剪枝优化呢，可以参见网友水中的鱼的博客，定义一个一位数组possible，其中possible[i] = true表示在[i, n]区间上有解，n为s的长度，如果某个区间之前被判定了无解，下次循环时就会跳过这个区间，从而大大减少了运行时间，
 */
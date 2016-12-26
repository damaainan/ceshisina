<?php
/**
 *Strobogrammatic Number III 对称数之三


A strobogrammatic number is a number that looks the same when rotated 180 degrees (looked at upside down).

Write a function to count the total strobogrammatic numbers that exist in the range of low <= num <= high.

For example,
Given low = "50", high = "100", return 3. Because 69, 88, and 96 are three strobogrammatic numbers.

Note:
Because the range might be a large number, the low and high numbers are represented as string.



Show Tags
Show Similar Problems

这道题是之前那两道Strobogrammatic Number II和Strobogrammatic Number的拓展，又增加了难度，让我们找到给定范围内的对称数的个数，我们当然不能一个一个的判断是不是对称数，我们也不能直接每个长度调用第二道中的方法，保存所有的对称数，然后再统计个数，这样OJ会提示内存超过允许的范围，所以我们的解法是基于第二道的基础上，不保存所有的结果，而是在递归中直接计数，根据之前的分析，需要初始化n=0和n=1的情况，然后在其基础上进行递归，递归的长度len从low到high之间遍历，然后我们看当前单词长度有没有达到len，如果达到了，我们首先要去掉开头是0的多位数，然后去掉长度和low相同但小于low的数，和长度和high相同但大于high的数，然后结果自增1，然后分别给当前单词左右加上那五对对称数，继续递归调用
 */
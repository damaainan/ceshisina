<?php
/**
 * 【POJ 2406】 Power Strings（KMP求循环节）

Time Limit: 3000MS      Memory Limit: 65536K
Total Submissions: 40536        Accepted: 16862
Description
Given two strings a and b we define a*b to be their concatenation. For example, if a = "abc" and b = "def" then a*b = "abcdef". If we think of concatenation as multiplication, exponentiation by a non-negative integer is defined
in the normal way: a^0 = "" (the empty string) and a^(n+1) = a*(a^n).
Input
Each test case is a line of input representing s, a string of printable characters. The length of s will be at least 1 and will not exceed 1 million characters. A line containing a period follows the last test case.
Output
For each s you should print the largest n such that s = a^n for some string a.

Sample Input
abcd
aaaa
ababab
.

Sample Output
1
4
3

Hint
This problem has huge input, use scanf instead of cin to avoid time limit exceed.
Source
Waterloo local 2002.07.01



来源讲解

之前只在比赛中接触到过循环节，还蛮频繁的。当时直接夹杂出来个最小表示。。当时挺懵的。专题做到KMP这里，惊喜的发现是个循环节(虽然起初不是按循环节写的。。。

对KMP理解突然加深了一大截，不过起初用了种类似dp的方法，借用了之前的状态(由于先写的1961 直接抓来同样的代码改改交了 发现时间好久 然后才明白了……

首先对于Next数组，Next[i]其实实在i-1上建立的，也就是i-1与其应匹配的位置上的字符匹配，如果不同就往前找，直到找到第一个匹配的字符str[j]，或者找到串首，然后给Next[i]赋值j+1 表示i失配后应和str[j+1]比较

这样来看，如果通过Next来找第i个字符往前的最早匹配，其实应该观察Next[i+1] 这样其实从Next[i+1]-1到i间就是前缀串i的重复子串 也就是长为i的前缀的模式串

那么题中所求的就是长为len的前缀的模式串 也就是len/Next[len]（Next数组下标从0开始
 */
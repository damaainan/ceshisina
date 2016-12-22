<?php
/**
 *Wildcard Matching 外卡匹配


Implement wildcard pattern matching with support for '?' and '*'.

'?' Matches any single character.
'*' Matches any sequence of characters (including the empty sequence).

The matching should cover the entire input string (not partial).

The function prototype should be:
bool isMatch(const char *s, const char *p)

Some examples:
isMatch("aa","a") → false
isMatch("aa","aa") → true
isMatch("aaa","aa") → false
isMatch("aa", "*") → true
isMatch("aa", "a*") → true
isMatch("ab", "?*") → true
isMatch("aab", "c*a*b") → false
这道题通配符匹配问题还是小有难度的，这道里用了贪婪算法Greedy Alogrithm来解，由于有特殊字符*和？，其中？能代替任何字符，*能代替任何字符串，那么我们需要定义几个额外的指针，其中scur和pcur分别指向当前遍历到的字符，再定义pstar指向p中最后一个*的位置，sstar指向此时对应的s的位置，具体算法如下：

- 定义scur, pcur, sstar, pstar

- 如果*scur存在

- 如果*scur等于*pcur或者*pcur为 '?'，则scur和pcur都自增1

- 如果*pcur为'*'，则pstar指向pcur位置，pcur自增1，且sstar指向scur

- 如果pstar存在，则pcur指向pstar的下一个位置，scur指向sstar自增1后的位置

- 如果pcur为'*'，则pcur自增1

- 若*pcur存在，返回False，若不存在，返回True
 */
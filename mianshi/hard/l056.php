<?php
/**
 *Word Pattern II 词语模式之二


Given a pattern and a string str, find if str follows the same pattern.

Here follow means a full match, such that there is a bijection between a letter in pattern and a non-empty substring in str.

Examples:

pattern = "abab", str = "redblueredblue" should return true.
pattern = "aaaa", str = "asdasdasdasd" should return true.
pattern = "aabb", str = "xyzabcxzyabc" should return false.


Notes:
You may assume both pattern and str contains only lowercase letters.



这道题是之前那道Word Pattern的拓展，之前那道题词语之间都有空格隔开，这样我们可以一个单词一个单词的读入，然后来判断是否符合给定的特征，而这道题没有空格了，那么难度就大大的增加了，因为我们不知道对应的单词是什么，所以得自行分开，那么我们可以用回溯法来生成每一种情况来判断，我们还是需要用哈希表来建立模式字符和单词之间的映射，我们还需要用变量p和r来记录当前递归到的模式字符和单词串的位置，在递归函数中，如果p和r分别等于模式字符串和单词字符串的长度，说明此时匹配成功结束了，返回ture，反之如果一个达到了而另一个没有，说明匹配失败了，返回false。如果都不满足上述条件的话，我们取出当前位置的模式字符，然后从单词串的r位置开始往后遍历，每次取出一个单词，如果模式字符已经存在哈希表中，而且对应的单词和取出的单词也相等，那么我们再次调用递归函数在下一个位置，如果返回true，那么我们就返回true。反之如果该模式字符不在哈希表中，我们要看有没有别的模式字符已经映射了当前取出的单词，如果没有的话，我们建立新的映射，并且调用递归函数，注意如果递归函数返回false了，我们要在哈希表中删去这个映射
 */
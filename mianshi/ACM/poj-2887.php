<?php
/**
 * 【POJ 2887】Big String（块状数组）

Time Limit: 1000MS      Memory Limit: 131072K
Total Submissions: 6380     Accepted: 1537
Description

You are given a string and supposed to do some string manipulations.

Input

The first line of the input contains the initial string. You can assume that it is non-empty and its length does not exceed 1,000,000.

The second line contains the number of manipulation commands N (0 <
N ≤ 2,000). The following N lines describe a command each. The commands are in one of the two formats below:

I ch p: Insert a character ch before the p-th character of the current string. If
p is larger than the length of the string, the character is appended to the end of the string.
Q p: Query the p-th character of the current string. The input ensures that the
p-th character exists.

All characters in the input are digits or lowercase letters of the English alphabet.

Output

For each Q command output one line containing only the single character queried.

Sample Input
ab
7
Q 1
I c 2
I d 4
I e 2
Q 5
I f 1
Q 3

Sample Output
a
d
e




题目大意是给出一个初始字符串s 之后有q次操作

每次操作有两种

Q x(0 < x <= len(s)) 表示查询当前串中第x个字符

I c x(c为字母 0 < x <= len(s)+1)表示在第x个位置插入c字符 x == len+1表示在串尾插入

如果只有查询这一种操作就简单的很，但还有插入操作，如果O（n）的遍历 妥妥的会超时 这里就要用到一个新的知识点——块状链表（数组）

用法就是把字符串分割成一个个块 此题每个块需要大小为1000 否则会超时

划分的方式就是把字符串先处理成链表 每个节点存当前位置的字符 每存1000个字符 就把当前位置记录一下 这样就相当于把字符串分割成了一个个长为1000的小块（最后一块长度可能不足1000）

最后得到的其实是需多个指针 指向每一个“关节” 或者说是分割点

这样对于每次查询 可以先找出该字符所处的块 然后由块的端点（分割点）暴力找这个字符即可 这样就可以用空间换时间 而且物超所值（每次查询O（1000））

插入略微麻烦一点 跟查询同样的方法 找到插入的位置，然后用链表的插入 把新字符生成的节点插入到当前位置 如果这样就结束了 你会发现插入点之后的相对位置就乱了

或者说之后的分割点指向的位置就不对了 当前插入的块中的字符数量会变为1001（此处只考虑插入块在中间 暂时不考虑尾块） 那么就要处理掉这个1

方法就是让1往后移 一直移动到最后一个块 由于最后一个块不足1000 所以加一位不会受到影响

实现方法就是把当前块往后的所有分割点往前指一个字符 可以自行出一组数据演试一下 1000比较大 可以让每个块大小为3 然后自行实现一下 理解后会发现这种做法真的很美妙

刚才链表的后面我写了个（数组） 因为链表略耗时（我用的C++里的new 可能malloc不会出现超时） 所以用了数组和前向星 节省了一半多的时间=.=
 */
<?php
/**
 * 【POJ 3368】 Frequent values（RMQ）

Time Limit: 2000MS      Memory Limit: 65536K
Total Submissions: 15813        Accepted: 5749
Description

You are given a sequence of n integers a1 , a2 , ... , an in non-decreasing order. In addition to that, you are given several queries consisting of indices
i and j (1 ≤ i ≤ j ≤ n). For each query, determine the most frequent value among the integers
ai , ... , aj.

Input

The input consists of several test cases. Each test case starts with a line containing two integers
n and q (1 ≤ n, q ≤ 100000). The next line contains
n integers a1 , ... , an (-100000 ≤ ai ≤ 100000, for each
i ∈ {1, ..., n}) separated by spaces. You can assume that for each i ∈ {1, ..., n-1}: ai ≤ ai+1. The following
q lines contain one query each, consisting of two integers
i and j (1 ≤ i ≤ j ≤ n), which indicate the boundary indices for the

query.

The last test case is followed by a line containing a single 0.

Output

For each query, print one line with one integer: The number of occurrences of the most frequent value within the given range.

Sample Input
10 3
-1 -1 1 1 1 1 3 10 10 10
2 3
1 10
5 10
0

Sample Output
1
4
3


来源讲解

题目意思比较明了，给出一个长n的有序数组，固定是升序。之后q次查询，每次询问区间[l,r]中出现的最长连续相同序列的长度。

刚开始想直接上ST算法，发现不是很直接的ST，需要一些辅助的东西来变换。就直接先写了发线段树。

发现线段树思路很清晰，先用num数组存下n个数的值。

对于区间[L,R]存三个值

mx:当前区间中最大的连续相同序列长度，也就是答案。

lmx:从左端点开始往右能找到的最大的相同序列长度。

rmx:从右端点开始往左能找到的最大的相同序列长度。

这样就可以做递归的初始化了，对于叶子来说 三个值一样 都是1，因为只有当前位置这一个数

对于区间[L,R] 可由[L,MID] [MID+1,R]组合

首先[L,R]的mx是两个子区间mx中大的一个

如果num[MID] == num[MID+1] 说明左右子区间中间可以连接 [L,R]的mx还要跟[L,MID].r+[MID+1,R].l比较 存较大的一个

如果[L,MID].l == MID-L+1，也就是左子区间中的数全是相同的，[L,R].l = [L,MID].l+[MID+1,R].l。否则 [L,R].l = [L,MID].l

同理 如果[MID+1,R].r == R-MID，也就是右子区间中的数全是相同的，[L,R].r = [MID+1,R].r+[L,MID].r。否则 [L,R].r = [MID+1,R].r

这样线段树的初始化就完成了

对于询问来说 询问[l,r]区间的答案

如果当前区间[L,R] MID >= r 或者MID+1 <= l 就正常跑左子树或右子树

否则 就要找左右两边递归出的较大值 另外 还要考虑num[MID] == num[MID+1]的情况 再跟左区间右端点开始的最长序列+右区间左端点开始的最长序列长度比较一下 选一个较大的即可 此时还要对左右区间的端点开始序列长度进行一些切割 越出的就去掉，最后得到的就是所求的答案

线段树思路比较好想 但写起来略繁琐 可能出现各种错误

另解：

今天又想了下ST的写法，大体讲一下，可能讲的不是很明白，大家谅解～

不过提交发现跟线段树相比就优化了几百MS（其实也蛮多了，毕竟2000MS时限，。

大体思路就是在存放数值的数组num之外，再开一个辅助数组f 表示从当前位置往后最多能连续到的位置

比如这个数据:

10 3
-1 -1 1 1 1 1 3 3 10 10

对应的f数组就是

2  2 6 6 6 6 7 7 10 10


对于rmq数组 我的写法是初始化时允许越出 就是只存储当前区间中出现过的数往后延伸出的最大的的长度，超出界限也允许。

这样在查询时需要加一些特殊处理，可能是导致时间不是很理想的原因。

初始化跟普通的rmq一样 就不详讲了

对于查询区间[L,R] 存在三种情况：

1.f[L] >= R 就是整个区间都是连续相同 类似【这种状态 这样答案就是R-L+1

2.f[f[L]+1] >= R 就是刚好两半的情况 类似【】【 这种状态 譬如上面数据中查询[4,7] 刚好是两种数 输出答案就是f[L]-L+1和R-f[L]中较大的一个

3.其余情况，就是类似 【】【】【】【】【 这种状态 其实会发现上面两种都是这种情况的延伸，其实就是两个很细小的剪枝，不过也省去了一些区间为负的特判。

对于这种情况 就需要二分出最后一个残缺的区间的左端点，因为在最开始提到 这个我写的这个ST的数组允许越出，对于右边界需要特殊处理。我想到的是二分。。所以这里可能会多一个nlogn 这样找到右边那个残缺区间的左端点后就好做了 求一下完整区间的RMQ 然后与右部的长度选一个较大的，即为答案
 */
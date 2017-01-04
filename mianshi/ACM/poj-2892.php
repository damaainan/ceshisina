<?php
/**
 * 【POJ 2892】 Tunnel Warfare（树状数组+二分）

Time Limit: 1000MS      Memory Limit: 131072K
Total Submissions: 7517     Accepted: 3104
Description

During the War of Resistance Against Japan, tunnel warfare was carried out extensively in the vast areas of north China Plain. Generally speaking, villages connected by tunnels lay in a line. Except the two at the ends, every village was directly connected
with two neighboring ones.

Frequently the invaders launched attack on some of the villages and destroyed the parts of tunnels in them. The Eighth Route Army commanders requested the latest connection state of the tunnels and villages. If some villages are severely isolated, restoration
of connection must be done immediately!

Input

The first line of the input contains two positive integers n and
m (n, m ≤ 50,000) indicating the number of villages and events. Each of the next
m lines describes an event.

There are three different events described in different format shown below:

D x: The x-th village was destroyed.
Q x: The Army commands requested the number of villages that x-th village was directly or indirectly connected with including itself.
R: The village destroyed last was rebuilt.

Output

Output the answer to each of the Army commanders’ request in order on a separate line.

Sample Input
7 9
D 3
D 6
D 5
Q 4
Q 5
R
Q 4
R
Q 4

Sample Output
1
0
2
4

Hint

An illustration of the sample input:

OOOOOOO

D 3   OOXOOOO

D 6   OOXOOXO

D 5   OOXOXXO

R     OOXOOXO

R     OOXOOOO

来源讲解

题目大意：n个城市连成一条链 除了城市1与城市n 每个城市i左右都分别连接了城市i-1 i+1

有m次操作 操作分为三种

D x 表示摧毁城市x 所有途径该城市的路均被摧毁

R 表示修复上一次摧毁的城市

Q x 表示询问与x直接或间接连接的城市数目（包括城市x）

可以用线段树或树状数组维护区间内未被摧毁的城市数量

线段树的话 对于每次询问 二分找一个最左城市a 和最右城市b 保证[a,x]区间幸存城市数量等于x-a+1 [x,b]区间幸存城市数量等于b-x+1 这样答案就是b-a+1

树状数组只需要修改一点 就是求区间的时候用前缀和相减来做
 */
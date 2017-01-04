<?php
/**
 * 【POJ 1703】 Find them, Catch them（关系并查集）

Time Limit: 1000MS      Memory Limit: 10000K
Total Submissions: 38951        Accepted: 11987
Description
The police office in Tadu City decides to say ends to the chaos, as launch actions to root up the TWO gangs in the city, Gang Dragon and Gang Snake. However, the police first needs to identify which gang a criminal belongs to.
The present question is, given two criminals; do they belong to a same clan? You must give your judgment based on incomplete information. (Since the gangsters are always acting secretly.)

Assume N (N <= 10^5) criminals are currently in Tadu City, numbered from 1 to N. And of course, at least one of them belongs to Gang Dragon, and the same for Gang Snake. You will be given M (M <= 10^5) messages in sequence, which are in the following two kinds:

1. D [a] [b]

where [a] and [b] are the numbers of two criminals, and they belong to different gangs.

2. A [a] [b]

where [a] and [b] are the numbers of two criminals. This requires you to decide whether a and b belong to a same gang.

Input
The first line of the input contains a single integer T (1 <= T <= 20), the number of test cases. Then T cases follow. Each test case begins with a line with two integers N and M, followed by M lines each containing one message
as described above.
Output
For each message "A [a] [b]" in each case, your program should give the judgment based on the information got before. The answers might be one of "In the same gang.", "In different gangs." and "Not sure yet."
Sample Input
1
5 5
A 1 2
D 1 2
A 1 2
D 2 4
A 1 4

Sample Output
Not sure yet.
In different gangs.
In the same gang.

来源讲解

关系并查集入门题目，如果做过食物链应该这个就小case了，。

题目大意是有两个犯罪团伙，给出总人数n，之后m个操作

D a b表示已知a和b不是一个团伙

A a b表示询问a与b的关系

这样可以建立一个并查集，另外开一个数组表示当前目标与它父亲的关系 1表示不在同一团伙 0表示在同一团伙。

可以试试倒过来表示 会发现后面处理起来稍微麻烦点

这样表示的话 对于预处理 所有的pre[x] = x rex[x] = 0（每个点单独是一个集合 与自己关系为0（处在同集合））

对于操作D a b找到a与b所在集合的根k r 如果k == r直接return 否则合并两集合 由于a和b不在同一集合 同时rex[a] rex[b]已知 由此可推出合并后k和r的关系

最后可以得到公式 rex[k] = rex[a]^rex[b]^1

这个。。。怎么得到的话稍微枚举下然后检测下正确性。。。弱只会这种方法推……

对于查询就好说了 如果两集合根不想等 就说明还没建立关系 返回那句话

否则说明a和b在同一集合 然后根据rex[a] rex[b] 很容易推出a和b的关系 做成公式就是rex[a]^rex[b]
 */
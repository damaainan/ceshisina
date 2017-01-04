<?php 
/**
 * 【HDOJ 3584】 Cube(树状数组<区间更新，单点查询>)


Cube

Time Limit: 2000/1000 MS (Java/Others) Memory Limit: 131072/65536 K (Java/Others)

Total Submission(s): 1833 Accepted Submission(s): 951



Problem Description

Given an N*N*N cube A, whose elements are either 0 or 1. A[i, j, k] means the number in the i-th row , j-th column and k-th layer. Initially we have A[i, j, k] = 0 (1 <= i, j, k <= N). 

We define two operations, 1: “Not” operation that we change the A[i, j, k]=!A[i, j, k]. that means we change A[i, j, k] from 0->1,or 1->0. (x1<=i<=x2,y1<=j<=y2,z1<=k<=z2).

0: “Query” operation we want to get the value of A[i, j, k].



Input

Multi-cases.

First line contains N and M, M lines follow indicating the operation below.

Each operation contains an X, the type of operation. 1: “Not” operation and 0: “Query” operation.

If X is 1, following x1, y1, z1, x2, y2, z2.

If X is 0, following x, y, z.



Output

For each query output A[x, y, z] in one line. (1<=n<=100 sum of m <=10000)



Sample Input

2 5
1 1 1 1  1 1 1
0 1 1 1
1 1 1 1  2 2 2
0 1 1 1
0 2 2 2




Sample Output

1
0
1




跟单点更新区间查询类似 想通了就一样了

单点更新区间查询是通过更改该点所在所有不相交区间的值 区间查询时通过Sum(r)-Sum(l-1)查出 Sum(x)就是从右边界x的区间往前累加 把所有不想交区间累加起来就是区间[1,x]的点值和 Lowbite(int x) {return x&(-x)}函数是树状数组的精髓 网上有很多博客讲。。。弱就不献丑了

至于区间更新单点查询 理解了老久 后来发现就是设置增长点和削减点 更新区间[l,r]时 在l处加上 在r+1处减去 这样查询某点x时 从1~x把所有更新的值加起来 就是x处最终的点值 

此题是个三维树状数组 在更新的时候用容斥的方法设置增减点即可
 */
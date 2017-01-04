<?php 
/**
 * 【HDU 1839】 Delay Constrained Maximum Capacity Path（二分+最短路）


Delay Constrained Maximum Capacity Path

Time Limit: 10000/10000 MS (Java/Others) Memory Limit: 65535/65535 K (Java/Others)

Total Submission(s): 1515 Accepted Submission(s): 481



Problem Description

Consider an undirected graph with N vertices, numbered from 1 to N, and M edges. The vertex numbered with 1 corresponds to a mine from where some precious minerals are extracted. The vertex numbered with N corresponds to a minerals processing factory. Each
edge has an associated travel time (in time units) and capacity (in units of minerals). It has been decided that the minerals which are extracted from the mine will be delivered to the factory using a single path. This path should have the highest capacity
possible, in order to be able to transport simultaneously as many units of minerals as possible. The capacity of a path is equal to the smallest capacity of any of its edges. However, the minerals are very sensitive and, once extracted from the mine, they
will start decomposing after T time units, unless they reach the factory within this time interval. Therefore, the total travel time of the chosen path (the sum of the travel times of its edges) should be less or equal to T.



Input

The first line of input contains an integer number X, representing the number of test cases to follow. The first line of each test case contains 3 integer numbers, separated by blanks: N (2 <= N <= 10.000), M (1 <= M <= 50.000) and T (1 <= T <= 500.000). Each
of the next M lines will contain four integer numbers each, separated by blanks: A, B, C and D, meaning that there is an edge between vertices A and B, having capacity C (1 <= C <= 2.000.000.000) and the travel time D (1 <= D <= 50.000). A and B are different
integers between 1 and N. There will exist at most one edge between any two vertices.



Output

For each of the X test cases, in the order given in the input, print one line containing the highest capacity of a path from the mine to the factory, considering the travel time constraint. There will always exist at least one path between the mine and the
factory obbeying the travel time constraint.



Sample Input

2
2 1 10
1 2 13 10
4 4 20
1 2 1000 15
2 4 999 6
1 3 100 15
3 4 99 4




Sample Output

13
99





一个无向图 每条边有流量跟花费（时间） 要求寻找在花费不超过D的情况下1->n的一条流量最大的路线

路线的流量是指路线中能承受的最大流量 即路线中一条流量最小的路径的流量

给的边50000条 当时没想出来怎么做 只知道时间很长 感觉可以搜 但不知道怎么搜 像是最小费的变形（费用不超过时的单条最大流）

之后知道是二分+最短路 因为边少 单独存一下排个序 然后二分 没二分到一个流量 看一下1->n存不存在流量>=该流量且在时间范围内的路径 然后不断二分+搜

二分要满足单调 排序后流量单调了 然后流量越大 可行路线越少 流量越少可行路就越多 因此是单减的 满足二分条件
 */
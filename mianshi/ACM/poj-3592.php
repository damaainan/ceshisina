<?php
/**
 * 【POJ 3592】 Instantaneous Transference（强连通缩点+最长路）

Instantaneous Transference

Time Limit: 5000MS      Memory Limit: 65536K
Total Submissions: 6265     Accepted: 1411
Description

It was long ago when we played the game Red Alert. There is a magic function for the game objects which is called instantaneous transfer. When an object uses this magic function, it will be transferred to the specified point immediately, regardless of how
far it is.

Now there is a mining area, and you are driving an ore-miner truck. Your mission is to take the maximum ores in the field.

The ore area is a rectangle region which is composed by n × m small squares, some of the squares have numbers of ores, while some do not. The ores can't be regenerated after taken.

The starting position of the ore-miner truck is the northwest corner of the field. It must move to the eastern or southern adjacent square, while it can not move to the northern or western adjacent square. And some squares have magic power that can instantaneously
transfer the truck to a certain square specified. However, as the captain of the ore-miner truck, you can decide whether to use this magic power or to stay still. One magic power square will never lose its magic power; you can use the magic power whenever
you get there.

Input

The first line of the input is an integer T which indicates the number of test cases.

For each of the test case, the first will be two integers N, M (2 ≤ N, M ≤ 40).

The next N lines will describe the map of the mine field. Each of the N lines will be a string that contains M characters. Each character will be an integer X (0 ≤ X ≤ 9) or a '*' or a '#'. The integer X indicates
that square has X units of ores, which your truck could get them all. The '*' indicates this square has a magic power which can transfer truck within an instant. The '#' indicates this square is full of rock and the truck can't move on this square.
You can assume that the starting position of the truck will never be a '#' square.

As the map indicates, there are K '*' on the map. Then there follows K lines after the map. The next K lines describe the specified target coordinates for the squares with '*', in the order from north to south then west to east.
(the original point is the northwest corner, the coordinate is formatted as north-south, west-east, all from 0 to N - 1,M - 1).

Output

For each test case output the maximum units of ores you can take.

Sample Input
1
2 2
11
1*
0 0

Sample Output
3



来源讲解

题目大意是给出一个n*m的图，从0,0出发 问怎样得到最高分

图中有三种点 0~9表示达到当前点可得分值 ‘*’为传送门 ‘#’为墙（不可达）

要求只能向右或者向下走

走到*时 可以选择传送 也可以不传送 *格子可以无数次传送

之后k行表示从上到下 从左到右每个*传送到的坐标x y

这是题目大意 如果没有*格子 就是一个裸的最长路 或者dp

由于存在*格子 因此可能有环 这样就没法直接bfs

因此先用邻接表存图 然后对于每个点跑Tarjan并把每个环都缩为一点

然后重新建图 新图中所有的环都变成了一个单点 就可以放心bfs了
 */
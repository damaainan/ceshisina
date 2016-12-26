<?php
/**
 *Dungeon Game 地牢游戏


The demons had captured the princess (P) and imprisoned her in the bottom-right corner of a dungeon. The dungeon consists of M x N rooms laid out in a 2D grid. Our valiant knight (K) was initially positioned in the top-left room and must fight his way through the dungeon to rescue the princess.

The knight has an initial health point represented by a positive integer. If at any point his health point drops to 0 or below, he dies immediately.

Some of the rooms are guarded by demons, so the knight loses health (negative integers) upon entering these rooms; other rooms are either empty (0's) or contain magic orbs that increase the knight's health (positive integers).

In order to reach the princess as quickly as possible, the knight decides to move only rightward or downward in each step.



Write a function to determine the knight's minimum initial health so that he is able to rescue the princess.

For example, given the dungeon below, the initial health of the knight must be at least 7 if he follows the optimal path RIGHT-> RIGHT -> DOWN -> DOWN.

-2 (K)  -3  3
-5  -10 1
10  30  -5 (P)


Notes:

The knight's health has no upper bound.
Any room can contain threats or power-ups, even the first room the knight enters and the bottom-right room where the princess is imprisoned.


Credits:
Special thanks to @stellari for adding this problem and creating all test cases.



这道王子救公主的题还是蛮新颖的，我最开始的想法是比较右边和下边的数字的大小，去大的那个，但是这个算法对某些情况不成立，比如下面的情况：

1 (K)   -3  3
0   -2  0
-3  -3  -3 (P)

如果按我的那种算法走的路径为 1 -> 0 -> -2 -> 0 -> -3, 这样的话骑士的起始血量要为5，而正确的路径应为 1 -> -3 -> 3 -> 0 -> -3, 这样骑士的骑士血量只需为3。无奈只好上网看大神的解法，发现统一都是用动态规划Dynamic Programming来做，建立一个和迷宫大小相同的二维数组用来表示当前位置出发的起始血量，最先初始化的是公主所在的房间的起始生命值，然后慢慢向第一个房间扩散，不断的得到各个位置的最优的起始生命值。递归方程为: 递归方程是dp[i][j] = max(1, min(dp[i+1][j], dp[i][j+1]) - dungeon[i][j]).
 */
<?php
/**
 *Number of Islands II 岛屿的数量之二


A 2d grid map of m rows and n columns is initially filled with water. We may perform an addLand operation which turns the water at position (row, col) into a land. Given a list of positions to operate, count the number of islands after each addLand operation. An island is surrounded by water and is formed by connecting adjacent lands horizontally or vertically. You may assume all four edges of the grid are all surrounded by water.

Example:

Given m = 3, n = 3, positions = [[0,0], [0,1], [1,2], [2,1]].
Initially, the 2d grid grid is filled with water. (Assume 0 represents water and 1 represents land).

0 0 0
0 0 0
0 0 0
Operation #1: addLand(0, 0) turns the water at grid[0][0] into a land.

1 0 0
0 0 0   Number of islands = 1
0 0 0
Operation #2: addLand(0, 1) turns the water at grid[0][1] into a land.

1 1 0
0 0 0   Number of islands = 1
0 0 0
Operation #3: addLand(1, 2) turns the water at grid[1][2] into a land.

1 1 0
0 0 1   Number of islands = 2
0 0 0
Operation #4: addLand(2, 1) turns the water at grid[2][1] into a land.

1 1 0
0 0 1   Number of islands = 3
0 1 0
We return the result as an array: [1, 1, 2, 3]

Challenge:

Can you do it in time complexity O(k log mn), where k is the length of the positions?



这道题是之前那道Number of Islands的拓展，难度增加了不少，因为这次是一个点一个点的增加，每增加一个点，都要统一一下现在总共的岛屿个数，最开始初始化时没有陆地，如下：

0 0 0
0 0 0
0 0 0

假如我们在(0, 0)的位置增加一个陆地，那么此时岛屿数量为1：

1 0 0
0 0 0
0 0 0

假如我们再在(0, 2)的位置增加一个陆地，那么此时岛屿数量为2：

1 0 1
0 0 0
0 0 0

假如我们再在(0, 1)的位置增加一个陆地，那么此时岛屿数量却又变为1：

1 1 1
0 0 0
0 0 0

假如我们再在(1, 1)的位置增加一个陆地，那么此时岛屿数量仍为1：

1 1 1
0 1 0
0 0 0

那么我们为了解决这种陆地之间会合并的情况，最好能够将每个陆地都标记出其属于哪个岛屿，这样就会方便我们统计岛屿个数，于是我们需要一个长度为m*n的一维数组来标记各个位置属于哪个岛屿，我们假设每个位置都是一个单独岛屿，岛屿编号可以用其坐标位置表示，但是我们初始化时将其都赋为-1，这样方便我们知道哪些位置尚未开发。然后我们开始遍历陆地数组，将其岛屿编号设置为其坐标位置，然后岛屿计数加1，我们此时开始遍历其上下左右的位置，遇到越界或者岛屿标号为-1的情况直接跳过，否则我们来查找邻居位置的岛屿编号，如果邻居的岛屿编号和当前点的编号不同，说明我们需要合并岛屿，将此点的编号赋为邻居的编号，在编号数组里也要修改，并将岛屿计数cnt减1。当我们遍历完当前点的所有邻居时，该合并的都合并完了，将此时的岛屿计数cnt存入结果中。

注意在查找岛屿编号的函数中我们可以做路径压缩Path Compression，只需加上一行roots[id] = roots[roots[id]];这样在编号数组中，所有属于同一个岛屿的点的编号都相同，可以自行用上面的那个例子来一步一步的走看roots的值的变化过程:

roots:

-1 -1 -1 -1 -1 -1 -1 -1 -1
0 -1 -1 -1 -1 -1 -1 -1 -1
0 -1  2 -1 -1 -1 -1 -1 -1
2  0  2 -1 -1 -1 -1 -1 -1
2  2  2 -1  2 -1 -1 -1 -1
 */
<?php
/**
 *N-Queens N皇后问题


The n-queens puzzle is the problem of placing n queens on an n×n chessboard such that no two queens attack each other.



Given an integer n, return all distinct solutions to the n-queens puzzle.

Each solution contains a distinct board configuration of the n-queens' placement, where 'Q' and '.' both indicate a queen and an empty space respectively.

For example,
There exist two distinct solutions to the 4-queens puzzle:

[
[".Q..",  // Solution 1
"...Q",
"Q...",
"..Q."],

["..Q.",  // Solution 2
"Q...",
"...Q",
".Q.."]
]


经典的N皇后问题，基本所有的算法书中都会包含的问题，经典解法为回溯递归，一层一层的向下扫描，需要用到一个pos数组，其中pos[i]表示第i行皇后的位置，初始化为-1，然后从第0开始递归，每一行都一次遍历各列，判断如果在该位置放置皇后会不会有冲突，以此类推，当到最后一行的皇后放好后，一种解法就生成了，将其存入结果res中，然后再还会继续完成搜索所有的情况，代码如下
 */
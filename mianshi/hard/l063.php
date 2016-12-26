<?php
/**
 *Range Sum Query 2D - Mutable 二维区域和检索 - 可变


Given a 2D matrix matrix, find the sum of the elements inside the rectangle defined by its upper left corner (row1, col1) and lower right corner (row2, col2).


The above rectangle (with the red border) is defined by (row1, col1) = (2, 1) and (row2, col2) = (4, 3), which contains sum = 8.

Example:
Given matrix = [
[3, 0, 1, 4, 2],
[5, 6, 3, 2, 1],
[1, 2, 0, 1, 5],
[4, 1, 0, 1, 7],
[1, 0, 3, 0, 5]
]

sumRegion(2, 1, 4, 3) -> 8
update(3, 2, 2)
sumRegion(2, 1, 4, 3) -> 10
Note:
The matrix is only modifiable by the update function.
You may assume the number of calls to update and sumRegion function is distributed evenly.
You may assume that row1 ≤ row2 and col1 ≤ col2.


这道题让我们求二维区域和检索，而且告诉我们数组中的值可能变化，这是之前那道Range Sum Query 2D - Immutable的拓展，由于我们之前做过一维数组的可变和不可变的情况Range Sum Query - Mutable和Range Sum Query - Immutable，那么为了能够通过OJ，我们还是需要用到树状数组Binary Indexed Tree(参见Range Sum Query - Mutable)，其查询和修改的复杂度均为O(logn)，那么我们还是要建立树状数组，我们根据数组中的每一个位置，建立一个二维的树状数组，然后还需要一个getSum函数，以便求得从(0, 0)到(i, j)的区间的数字和，然后在求某一个区间和时，就利用其四个顶点的区间和关系可以快速求出
 */
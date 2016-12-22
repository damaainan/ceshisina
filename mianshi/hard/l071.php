<?php
/**
 *Max Sum of Rectangle No Larger Than K 最大矩阵和不超过K


Given a non-empty 2D matrix matrix and an integer k, find the max sum of a rectangle in the matrix such that its sum is no larger than k.

Example:

Given matrix = [
[1,  0, 1],
[0, -2, 3]
]
k = 2


The answer is 2. Because the sum of rectangle [[0, 1], [-2, 3]] is 2 and 2 is the max number no larger than k (k = 2).

Note:

The rectangle inside the matrix must have an area > 0.
What if the number of rows is much larger than the number of columns?


Credits:
Special thanks to @fujiaozhu for adding this problem and creating all test cases.



这道题给了我们一个二维数组，让我们求和不超过的K的最大子矩形，那么我们首先可以考虑使用brute force来解，就是遍历所有的子矩形，然后计算其和跟K比较，找出不超过K的最大值即可。就算是暴力搜索，我们也可以使用优化的算法，比如建立累加和，参见之前那道题Range Sum Query 2D - Immutable，我们可以快速求出任何一个区间和，那么下面的方法就是这样的，当遍历到(i, j)时，我们计算sum(i, j)，表示矩形(0, 0)到(i, j)的和，然后我们遍历这个矩形中所有的子矩形，计算其和跟K相比，这样既可遍历到原矩形的所有子矩形
 */
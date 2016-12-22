<?php
/**
 *Count of Range Sum 区间和计数


Given an integer array nums, return the number of range sums that lie in [lower, upper] inclusive.
Range sum S(i, j) is defined as the sum of the elements in nums between indices i and j (i ≤ j), inclusive.

Note:
A naive algorithm of O(n2) is trivial. You MUST do better than that.

Example:
Given nums = [-2, 5, -1], lower = -2, upper = 2,
Return 3.
The three ranges are : [0, 0], [2, 2], [0, 2] and their respective sums are: -2, -1, 2.

Credits:
Special thanks to @dietpepsi for adding this problem and creating all test cases.



这道题给了我们一个数组，又给了我们一个下限和一个上限，让我们求有多少个不同的区间使得每个区间的和在给定的上下限之间。这道题的难度系数给的是Hard，的确是一道难度不小的题，题目中也说了Brute Force的方法太Naive了，那么我们只能另想方法了。To be honest，这题完全超出了我的能力范围，所以我也没挣扎了，直接上网搜大神们的解法啦。首先根据前面的那几道类似题Range Sum Query - Mutable 区域和检索 - 可变，Range Sum Query 2D - Immutable 二维区域和检索和Range Sum Query - Immutable 区域和检索 - 不可变的解法可知类似的区间和的问题一定是要计算累积和sum的，其中sum[i] = nums[0] + nums[1] + ... + nums[i]，对于某个i来说，只有那些满足 lower <= sum[i] - sum[j] <= upper 的j能形成一个区间[j, i]满足题意，那么我们的目标就是来找到有多少个这样的j (0 =< j < i) 满足 sum[i] - upper =< sum[j] <= sum[i] - lower，我们可以用C++中由红黑树实现的multiset数据结构可以对其中数据排序，然后用upperbound和lowerbound来找临界值。lower_bound是找数组中第一个不小于给定值的数(包括等于情况)，而upper_bound是找数组中第一个大于给定值的数，那么两者相减，就是j的个数
 */
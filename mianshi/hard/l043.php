<?php
/**
 *Maximum Gap 求最大间距
Given an unsorted array, find the maximum difference between the successive elements in its sorted form.

Try to solve it in linear time/space.

Return 0 if the array contains less than 2 elements.

You may assume all elements in the array are non-negative integers and fit in the 32-bit signed integer range.

Credits:
Special thanks to @porker2008 for adding this problem and creating all test cases.



遇到这类问题肯定先想到的是要给数组排序，但是题目要求是要线性的时间和空间，那么只能用桶排序或者基排序。这里我用桶排序Bucket Sort来做，首先找出数组的最大值和最小值，然后要确定每个桶的容量，即为(最大值 - 最小值) / 个数 + 1，在确定桶的个数，即为(最大值 - 最小值) / 桶的容量 + 1，然后需要在每个桶中找出局部最大值和最小值，而最大间距的两个数不会在同一个桶中，而是一个桶的最小值和另一个桶的最大值之间的间距。
 */
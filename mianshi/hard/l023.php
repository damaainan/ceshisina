<?php
/**
 *Largest Rectangle in Histogram 直方图中最大的矩形


Given n non-negative integers representing the histogram's bar height where the width of each bar is 1, find the area of largest rectangle in the histogram.





Above is a histogram where width of each bar is 1, given height = [2,1,5,6,2,3].





The largest rectangle is shown in the shaded area, which has area = 10 unit.



For example,
Given height = [2,1,5,6,2,3],
return 10.



这道题让求直方图中最大的矩形，刚开始看到求极值问题以为要用DP来做，可是想不出递推式，只得作罢。这道题如果用暴力搜索法估计肯定没法通过OJ，但是我也没想出好的优化方法，在网上搜到了网友水中的鱼的博客，发现他想出了一种很好的优化方法，就是遍历数组，每找到一个局部峰值，然后向前遍历所有的值，算出共同的矩形面积，每次对比保留最大值，
 */
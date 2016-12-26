<?php
/**
 *Trapping Rain Water 收集雨水


Given n non-negative integers representing an elevation map where the width of each bar is 1, compute how much water it is able to trap after raining.

For example,
Given [0,1,0,2,1,0,1,3,2,1,2,1], return 6.



The above elevation map is represented by array [0,1,0,2,1,0,1,3,2,1,2,1]. In this case, 6 units of rain water (blue section) are being trapped. Thanks Marcos for contributing this image!



这道收集雨水的题跟之前的那道 Largest Rectangle in Histogram 直方图中最大的矩形 有些类似，但是又不太一样，我最先想到的方法有些复杂，但是也能通过OJ，想法是遍历数组，找到局部最小值，方法是如果当前值大于或等于前一个值，或者当前值大于后一个值则跳过，找到了局部最小值后，然后我们首先向左找到左边的最大值，再找右边的最大值，找右边最大值时要注意当其大于左边最大时时就停止寻找了，然后算出从左边最大值到右边最大值之间能装的水量，之后从右边最大值的位置开始继续找局部最小值，以此类推直到遍历完整个数组，代码如下：
 */
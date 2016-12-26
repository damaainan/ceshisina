<?php
/**
 *Find Median from Data Stream 找出数据流的中位数


Median is the middle value in an ordered integer list. If the size of the list is even, there is no middle value. So the median is the mean of the two middle value.

Examples:

[2,3,4] , the median is 3

[2,3], the median is (2 + 3) / 2 = 2.5

Design a data structure that supports the following two operations:

void addNum(int num) - Add a integer number from the data stream to the data structure.
double findMedian() - Return the median of all elements so far.
For example:

add(1)
add(2)
findMedian() -> 1.5
add(3)
findMedian() -> 2
Credits:
Special thanks to @Louis1992 for adding this problem and creating all test cases.



这道题给我们一个数据流，让我们找出中位数，由于数据流中的数据并不是有序的，所以我们首先应该想个方法让其有序。如果我们用vector来保存数据流的话，每进来一个新数据都要给数组排序，很不高效。所以之后想到用multiset这个数据结构，是有序保存数据的，但是它不能用下标直接访问元素，找中位数也不高效。这里用到的解法十分巧妙，我们使用大小堆来解决问题，其中大堆保存右半段较大的数字，小堆保存左半段较小的数组。这样整个数组就被中间分为两段了，由于堆的保存方式是由大到小，我们希望大堆里面的数据是从小到大，这样取第一个来计算中位数方便。我们用到一个小技巧，就是存到大堆里的数先取反再存，这样由大到小存下来的顺序就是实际上我们想要的从小到大的顺序。当大堆和小堆中的数字一样多时，我们取出大堆小堆的首元素求平均值，当小堆元素多时，取小堆首元素为中位数
 */
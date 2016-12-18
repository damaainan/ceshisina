<?php
/**
 *Insert Interval 插入区间


Given a set of non-overlapping intervals, insert a new interval into the intervals (merge if necessary).

You may assume that the intervals were initially sorted according to their start times.

Example 1:
Given intervals [1,3],[6,9], insert and merge [2,5] in as [1,5],[6,9].

Example 2:
Given [1,2],[3,5],[6,7],[8,10],[12,16], insert and merge [4,9] in as [1,2],[3,10],[12,16].

This is because the new interval [4,9] overlaps with [3,5],[6,7],[8,10].



这道题让我们在一系列非重叠的区间中插入一个新的区间，可能还需要和原有的区间合并，那么我们需要对给区间集一个一个的遍历比较，那么会有两种情况，重叠或是不重叠，不重叠的情况最好，直接将新区间插入到对应的位置即可，重叠的情况比较复杂，有时候会有多个重叠，我们需要更新新区间的范围以便包含所有重叠，而且最后处理的时候还需要删除原区间集中所有和新区间重叠的区间，然后插入新区间即可。具体思路如下：

- 对区间集中每个区间进行遍历

　　- 如果新区间的末尾小于当前区间的开头，则跳出循环

　　- 如果新区间的开头大于当前区间的末尾，不作处理

　　- 如果新区间和当前区间有重叠，则更新新区间的开头为两者最小值，新区间的末尾为两者最大值，重叠数加一

　　- 指针移向下一个区间

- 如果重叠数大于0，则删除掉所有的重叠区间

- 插入新区间到对应的位置
 */
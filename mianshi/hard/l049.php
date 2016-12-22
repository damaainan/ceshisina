<?php
/**
 *Sliding Window Maximum 滑动窗口最大值


Given an array nums, there is a sliding window of size k which is moving from the very left of the array to the very right. You can only see the k numbers in the window. Each time the sliding window moves right by one position.

For example,
Given nums = [1,3,-1,-3,5,3,6,7], and k = 3.

Window position                Max
---------------               -----
[1  3  -1] -3  5  3  6  7       3
1 [3  -1  -3] 5  3  6  7       3
1  3 [-1  -3  5] 3  6  7       5
1  3  -1 [-3  5  3] 6  7       5
1  3  -1  -3 [5  3  6] 7       6
1  3  -1  -3  5 [3  6  7]      7
Therefore, return the max sliding window as [3,3,5,5,6,7].

Note:
You may assume k is always valid, 1 ≤ k ≤ input array's size.

Follow up:
Could you solve it in linear time?

Hint:

How about using a data structure such as deque (double-ended queue)?
The queue size need not be the same as the window’s size.
Remove redundant elements and the queue should store only elements that need to be considered.


这道题给定了一个数组，还给了一个窗口大小k，让我们每次向右滑动一个数字，每次返回窗口内的数字的最大值，而且要求我们代码的时间复杂度为O(n)。提示我们要用双向队列deque来解题，并提示我们窗口中只留下有用的值，没用的全移除掉。果然Hard的题目我就是不会做，网上看到了别人的解法才明白，解法又巧妙有简洁，膜拜啊。大概思路是用双向队列保存数字的下标，遍历整个数组，如果此时队列的首元素是i - k的话，表示此时窗口向右移了一步，则移除队首元素。然后比较队尾元素和将要进来的值，如果小的话就都移除，然后此时我们把队首元素加入结果中即可
 */
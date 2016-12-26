<?php
/**
 *Find the Duplicate Number 寻找重复数


Given an array nums containing n + 1 integers where each integer is between 1 and n (inclusive), prove that at least one duplicate element must exist. Assume that there is only one duplicate number, find the duplicate one.

Note:
You must not modify the array (assume the array is read only).
You must use only constant extra space.
Your runtime complexity should be less than O(n2).
Credits:
Special thanks to @jianchao.li.fighter for adding this problem and creating all test cases.



这道题给了我们n+1个数，所有的数都在[1, n]区域内，首先让我们证明必定会有一个重复数，这不禁让我想起了小学华罗庚奥数中的抽屉原理(又叫鸽巢原理), 即如果有十个苹果放到九个抽屉里，如果苹果全在抽屉里，则至少有一个抽屉里有两个苹果，这里就不证明了，直接来做题吧。题目要求我们不能改变原数组，即不能给原数组排序，又不能用多余空间，那么哈希表神马的也就不用考虑了，又说时间小于O(n2)，也就不能用brute force的方法，那我们也就只能考虑用二分搜索法了，我们在区别[1, n]中搜索，首先求出中点mid，然后遍历整个数组，统计所有小于等于mid的数的个数，如果个数大于mid，则说明重复值在[mid+1, n]之间，反之，重复值应在[1, mid-1]之间，然后依次类推，直到搜索完成，此时的low就是我们要求的重复值
 */
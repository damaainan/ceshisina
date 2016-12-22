<?php
/**
 *Split Array Largest Sum 分割数组的最大值


Given an array which consists of non-negative integers and an integer m, you can split the array into m non-empty continuous subarrays. Write an algorithm to minimize the largest sum among these m subarrays.

Note:
Given m satisfies the following constraint: 1 ≤ m ≤ length(nums) ≤ 14,000.

Examples:

Input:
nums = [7,2,5,10,8]
m = 2

Output:
18

Explanation:
There are four ways to split nums into two subarrays.
The best way is to split it into [7,2,5] and [10,8],
where the largest sum among the two subarrays is only 18.


这道题给了我们一个非负数的数组nums和一个整数m，让我们把数组分割成m个非空的连续子数组，让我们最小化m个子数组中的最大值。开始以为要用博弈论中的最小最大化算法，可是想了半天发现并不会做，于是后面决定采用无脑暴力破解，在nums中取出所有的m个子数组的情况都找一遍最大值，为了加快求子数组和的运算，还建立了累计和数组，可以还是TLE了，所以博主就没有办法了，只能上网参考大神们的解法，发现大家普遍使用了二分搜索法来做，感觉特别巧妙，原来二分搜索法还能这么用，厉害了我的哥。我们首先来分析，如果m和数组nums的个数相等，那么每个数组都是一个子数组，所以返回nums中最大的数字即可，如果m为1，那么整个nums数组就是一个子数组，返回nums所有数字之和，所以对于其他有效的m值，返回的值必定在上面两个值之间，所以我们可以用二分搜索法来做。我们用一个例子来分析，nums = [1, 2, 3, 4, 5], m = 3，我们将left设为数组中的最大值5，right设为数字之和15，然后我们算出中间数为10，我们接下来要做的是找出和最大且小于等于10的子数组的个数，[1, 2, 3, 4], [5]，可以看到我们无法分为3组，说明mid偏大，所以我们让right=mid，然后我们再次进行二分查找哦啊，算出mid=7，再次找出和最大且小于等于7的子数组的个数，[1,2,3], [4], [5]，我们成功的找出了三组，说明mid还可以进一步降低，我们让right=mid，然后我们再次进行二分查找哦啊，算出mid=6，再次找出和最大且小于等于6的子数组的个数，[1,2,3], [4], [5]，我们成功的找出了三组，我们尝试着继续降低mid，我们让right=mid，然后我们再次进行二分查找哦啊，算出mid=5，再次找出和最大且小于等于5的子数组的个数，[1,2], [3], [4], [5]，发现有4组，此时我们的mid太小了，应该增大mid，我们让left=mid+1，此时left=6，right=5，循环退出了，我们返回left即可
 */
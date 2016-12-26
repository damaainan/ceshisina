<?php
/**
 *Jump Game II 跳跃游戏之二


Given an array of non-negative integers, you are initially positioned at the first index of the array.

Each element in the array represents your maximum jump length at that position.

Your goal is to reach the last index in the minimum number of jumps.

For example:
Given array A = [2,3,1,1,4]

The minimum number of jumps to reach the last index is 2. (Jump 1 step from index 0 to 1, then 3 steps to the last index.)



这题是之前那道Jump Game 跳跃游戏 的延伸，那题是问能不能到达最后一个数字，而此题只让我们求到达最后一个位置的最少跳跃数，貌似是默认一定能到达最后位置的? 此题的核心方法还是利用动态规划Dynamic Programming的思想来解，我们需要两个变量cur和pre分别来保存当前的能到达的最远位置和之前能到达的最远位置，只要cur未达到最后一个位置则循环继续，pre记录cur的值，如果当前位置i小于等于pre，则更新cur然后i自增1。更新cur的方法是比较当前的cur和i + A[i]之中的较大值，等i循环到pre的值时，跳跃的步数加一，如果题目中未说明是否能到达末尾，我们还可以判断此时pre和cur是否相等，如果相等说明cur没有更新，即无法到达末尾位置，返回-1，代码如下：
 */
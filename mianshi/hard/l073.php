<?php
/**
 *Frog Jump 青蛙过河


A frog is crossing a river. The river is divided into x units and at each unit there may or may not exist a stone. The frog can jump on a stone, but it must not jump into the water.

Given a list of stones' positions (in units) in sorted ascending order, determine if the frog is able to cross the river by landing on the last stone. Initially, the frog is on the first stone and assume the first jump must be 1 unit.

If the frog's last jump was k units, then its next jump must be either k - 1, k, or k + 1 units. Note that the frog can only jump in the forward direction.

Note:

The number of stones is ≥ 2 and is < 1,100.
Each stone's position will be a non-negative integer < 231.
The first stone's position is always 0.


Example 1:

[0,1,3,5,6,8,12,17]

There are a total of 8 stones.
The first stone at the 0th unit, second stone at the 1st unit,
third stone at the 3rd unit, and so on...
The last stone at the 17th unit.

Return true. The frog can jump to the last stone by jumping
1 unit to the 2nd stone, then 2 units to the 3rd stone, then
2 units to the 4th stone, then 3 units to the 6th stone,
4 units to the 7th stone, and 5 units to the 8th stone.


Example 2:

[0,1,2,3,4,8,9,11]

Return false. There is no way to jump to the last stone as
the gap between the 5th and 6th stone is too large.


终于等到青蛙过河问题了，一颗赛艇。题目中说青蛙如果上一次跳了k距离，那么下一次只能跳k-1, k, 或k+1的距离，那么青蛙跳到某个石头上可能有多种跳法，由于这道题只是让我们判断青蛙是否能跳到最后一个石头上，并没有让我们返回所有的路径，这样就降低了一些难度。我们可以用递归来做，我们维护一个变量res，表示青蛙能跳到的最远的石头，这样最后我们看res是否到达最后一块石头就知道青蛙是否能过河了。然后我们需要两个变量start和jump分别用来表示当前的石头位置和此时青蛙的弹跳力，我们遍历余下的所有石头，对于遍历到的石头，我们计算到当前石头的距离dist，如果遍历到第二个石头，而距离不是1的话，则返回false，因为题目中规定了青蛙第一次跳跃距离必须是1。对于其他石头，我们检测距离和弹跳力的关系，如果满足题意，说明可以跳到该石头上，我们更新res，然后对该石头继续调用递归函数
 */
<?php
/**
 * 【POJ 2482】 Stars in Your Window（线段树+离散化+扫描线）

Time Limit: 1000MS      Memory Limit: 65536K
Total Submissions: 11294        Accepted: 3091
Description
Fleeting time does not blur my memory of you. Can it really be 4 years since I first saw you? I still remember, vividly, on the beautiful Zhuhai Campus, 4 years ago, from the moment I saw you smile, as you were walking out of the
classroom and turned your head back, with the soft sunset glow shining on your rosy cheek, I knew, I knew that I was already drunk on you. Then, after several months’ observation and prying, your grace and your wisdom, your attitude to life and your aspiration
for future were all strongly impressed on my memory. You were the glamorous and sunny girl whom I always dream of to share the rest of my life with. Alas, actually you were far beyond my wildest dreams and I had no idea about how to bridge that gulf between
you and me. So I schemed nothing but to wait, to wait for an appropriate opportunity. Till now — the arrival of graduation, I realize I am such an idiot that one should create the opportunity and seize it instead of just waiting.

These days, having parted with friends, roommates and classmates one after another, I still cannot believe the fact that after waving hands, these familiar faces will soon vanish from our life and become no more than a memory. I will move out from school tomorrow.
And you are planning to fly far far away, to pursue your future and fulfill your dreams. Perhaps we will not meet each other any more if without fate and luck. So tonight, I was wandering around your dormitory building hoping to meet you there by chance. But
contradictorily, your appearance must quicken my heartbeat and my clumsy tongue might be not able to belch out a word. I cannot remember how many times I have passed your dormitory building both in Zhuhai and Guangzhou, and each time aspired to see you appear
in the balcony or your silhouette that cast on the window. I cannot remember how many times this idea comes to my mind: call her out to have dinner or at least a conversation. But each time, thinking of your excellence and my commonness, the predominance of
timidity over courage drove me leave silently.

Graduation, means the end of life in university, the end of these glorious, romantic years. Your lovely smile which is my original incentive to work hard and this unrequited love will be both sealed as a memory in the deep of my heart and my mind. Graduation,
also means a start of new life, a footprint on the way to bright prospect. I truly hope you will be happy everyday abroad and everything goes well. Meanwhile, I will try to get out from puerility and become more sophisticated. To pursue my own love and happiness
here in reality will be my ideal I never desert.

Farewell, my princess!

If someday, somewhere, we have a chance to gather, even as gray-haired man and woman, at that time, I hope we can be good friends to share this memory proudly to relight the youthful and joyful emotions. If this chance never comes, I wish I were the stars in
the sky and twinkling in your window, to bless you far away, as friends, to accompany you every night, sharing the sweet dreams or going through the nightmares together.



Here comes the problem: Assume the sky is a flat plane. All the stars lie on it with a location (x, y). for each star, there is a grade ranging from 1 to 100, representing its brightness, where 100 is the brightest and 1 is the weakest. The window is a rectangle
whose edges are parallel to the x-axis or y-axis. Your task is to tell where I should put the window in order to maximize the sum of the brightness of the stars within the window. Note, the stars which are right on the edge of the window does not count. The
window can be translated but rotation is not allowed.

Input
There are several test cases in the input. The first line of each case contains 3 integers: n, W, H, indicating the number of stars, the horizontal length and the vertical height of the rectangle-shaped window. Then n lines follow,
with 3 integers each: x, y, c, telling the location (x, y) and the brightness of each star. No two stars are on the same point.

There are at least 1 and at most 10000 stars in the sky. 1<=W，H<=1000000, 0<=x，y<2^31.

Output
For each test case, output the maximum brightness in a single line.
Sample Input
3 5 4
1 2 3
2 3 2
6 3 1
3 5 4
1 2 3
2 3 2
5 3 1

Sample Output
5
6


来源讲解

首先题目大意：天上有n颗星星（1 <= n <= 10000） 每个星星有一个坐标 (x,y)（0 <= x , y < 2^31）和亮度 c（1 <= c <= 100）

你有一个矩形框 宽w 高h 问如何框能让框里的星星亮度和最大

另外在边框上的星星的亮度不计入

直观的看 没什么思路……我是没思路…………………………………………暴力的话星星的选与不选会导致出现许多状态 想都甭想+。+

既然是分在线段树专题 那就尽可能往线段树靠呗。。。

线段树是对区间查询 但只支持一维区间 这种二维区间只能想办法把一个维度限制 这样 对于遍历到某个x的时候 出现在所有y的需多个区间的亮度和就容易求了

固定x就需要用到扫描线了 通过排序 让星星按照x有序 这样扫描所有的x 每当扫到一个x就把星星的亮度加入对应的y区间内

但只加亮度满足了左边界 还需要在超出w宽度限制的时候把最前面的星星亮度从区间中取走

也就是用到了扫描线拆分线段的方法 在起点把这个块的价值加入 在终点减去 对于此题 起点是x 终点就是x+w 也就是从x最远能碰触到的边框

这样每个点拆分成两部分 一部分是起点亮度为正值 另一个是终点 亮度相反 对于所有拆出的2*n个点排序 优先按照x排序 x相同的价值为负的在前

因为要求边框上的星星不计入 因此需要先把边框上的星星亮度减去 再加入新星星

这样对于x处理好了 从头遍历 每遍历到一个点 就在y的区间内加入它的亮度（或正或负） y区间其实就是[y,y+h-1] 就是它可以贡献价值的区间

但是y很大 所以又涉及到一个离散化的问题 把所有出现过的y的值进行排序 然后离散化处理下即可

不知道为什么 G++总是RE。。。可能哪里写挫了？。。有G++ A的大神 还是自己水平不够啊～～。。
 */
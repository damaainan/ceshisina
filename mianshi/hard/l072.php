<?php
/**
 *Perfect Rectangle 完美矩形


Given N axis-aligned rectangles where N > 0, determine if they all together form an exact cover of a rectangular region.

Each rectangle is represented as a bottom-left point and a top-right point. For example, a unit square is represented as [1,1,2,2]. (coordinate of bottom-left point is (1, 1) and top-right point is (2, 2)).


Example 1:

rectangles = [
[1,1,3,3],
[3,1,4,2],
[3,2,4,4],
[1,3,2,4],
[2,3,3,4]
]

Return true. All 5 rectangles together form an exact cover of a rectangular region.


Example 2:

rectangles = [
[1,1,2,3],
[1,3,2,4],
[3,1,4,2],
[3,2,4,4]
]

Return false. Because there is a gap between the two rectangular regions.

Example 3:

rectangles = [
[1,1,3,3],
[3,1,4,2],
[1,3,2,4],
[3,2,4,4]
]

Return false. Because there is a gap in the top center.

Example 4:

rectangles = [
[1,1,3,3],
[3,1,4,2],
[1,3,2,4],
[2,2,4,4]
]

Return false. Because two of the rectangles overlap with each other.


这道题是LeetCode第二周编程比赛的压轴题目，然而我并没有做出来，我想了两种方法都无法通过OJ的大数据集合，第一种方法是对于每一个矩形，我将其拆分为多个面积为1的单位矩形，然后以其左下方的点为标记，用一个哈希表建立每一个单位矩形和遍历到的矩形的映射，因为每个单位矩形只能属于一个矩形，否则就会有重叠，我感觉这种思路应该没错，但是由于把每一个遍历到的矩形拆分为单位矩形再建立映射很费时间，尤其是当矩形很大的时候，TLE就很正常了，后来我试的第二种方法是对于遍历到的每个矩形都和其他所有矩形检测一遍是否重叠，这种方法也是毫无悬念的TLE。

博主能力有限，只能去论坛中找各位大神的解法，发现下面两种方法比较fancy，也比较好理解。首先来看第一种方法，这种方法的设计思路很巧妙，利用了mask，也就是位操作Bit Manipulation的一些技巧，下面这张图来自这个帖子：



所有的矩形的四个顶点只会有下面蓝，绿，红三种情况，其中蓝表示该顶点周围没有其他矩形，T型的绿点表示两个矩形并排相邻，红点表示四个矩形相邻，那么在一个完美矩形中，蓝色的点只能有四个，这是个很重要的判断条件。我们再来看矩形的四个顶点，我们按照左下，左上，右上，右下的顺序来给顶点标号为1，2，4，8，为啥不是1，2，3，4呢，我们注意它们的二进制1(0001)，2(0010)，4(0100)，8(1000)，这样便于我们与和或的操作，我们还需要知道的一个判定条件是，当一个点是某一个矩形的左下顶点时，这个点就不能是其他矩形的左下顶点了，这个条件对于这四种顶点都要成立，那么对于每一个点，如果它是某个矩形的四个顶点之一，我们记录下来，如果在别的矩形中它又是相同的顶点，那么直接返回false即可，这样就体现了我们标记为1，2，4，8的好处，我们可以按位检查1。如果每个点的属性没有冲突，那么我们来验证每个点的mask是否合理，通过上面的分析，我们知道每个点只能是蓝，绿，红三种情况的一种，其中蓝的情况是mask的四位中只有一个1，分别就是1(0001)，2(0010)，4(0100)，8(1000)，而且蓝点只能有四个；那么对于T型的绿点，mask的四位中有两个1，那么就有六种情况，分别是12(1100), 10(1010), 9(1001), 6(0110), 5(0101), 3(0011)；而对于红点，mask的四位都是1，只有一种情况15(1111)，那么我们可以通过直接找mask是1，2，4，8的个数，也可以间接通过找不是绿点和红点的个数，看是否是四个。最后一个判定条件是每个矩形面积累加和要等于最后的大矩形的面积，那么大矩形的面积我们通过计算最小左下点和最大右上点来计算出来即可
 */
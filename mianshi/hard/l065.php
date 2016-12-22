<?php
/**
 *Create Maximum Number 创建最大数


Given two arrays of length m and n with digits 0-9 representing two numbers. Create the maximum number of length k <= m + n from digits of the two. The relative order of the digits from the same array must be preserved. Return an array of the k digits. You should try to optimize your time and space complexity.

Example 1:

nums1 = [3, 4, 6, 5]
nums2 = [9, 1, 2, 5, 8, 3]
k = 5
return [9, 8, 6, 5, 3]

Example 2:

nums1 = [6, 7]
nums2 = [6, 0, 4]
k = 5
return [6, 7, 6, 0, 4]

Example 3:

nums1 = [3, 9]
nums2 = [8, 9]
k = 3
return [9, 8, 9]

Credits:
Special thanks to @dietpepsi for adding this problem and creating all test cases.



这道题给了我们两个数组，里面数字是无序的，又给我们一个k值为k <= m + n，然后我们从两个数组中共挑出k个数，数字之间的相对顺序不变，求能组成的最大的数。这道题的难度是Hard，博主木有想出解法，参考网上大神们的解法来做的。由于k的大小不定，所以有三种可能，第一种是当k为0时，两个数组中都不取数；第二种是当k不大于其中一个数组的长度时，有可能只从一个数组中取数；第三种情况是k大于其中一个数组的长度，则需要从两个数组中分别取数，至于每个数组中取几个，每种情况都要考虑到，然后每次更结果即可。对于分别从两个数组中取数字的情况，我们需要将两个取出的小数组混合排序成一个数组，小数组中各自的数字之间的相对顺序不变。我们还需要一个函数来从数组中取若干个数字的函数，而且取出的数要最大。比如当前数组长度为n，需要取出k个数字，我们定义一个变量drop = n - k，表示需要丢弃的数字的个数，我们遍历数组中的数字，进行下列循环，如果此时drop为整数，且结果数组长度不为0，结果数组的尾元素小于当前遍历的元素，则去掉结果数组的尾元素，此时drop自减1，重复循环直至上述任意条件不满足为止，然后把当前元素加入结果数组中，最后我们返回结果数组中的前k个元素。对于两个数组的混合，我们只要从两个数组开头每次取两个，把大的加入结果数组，然后删掉这个大的，然后继续取一对比较，直到两个数组都为空停止
 */
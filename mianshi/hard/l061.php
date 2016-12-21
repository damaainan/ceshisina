<?php
/**
 *Smallest Rectangle Enclosing Black Pixels 包含黑像素的最小矩阵


An image is represented by a binary matrix with 0 as a white pixel and 1 as a black pixel. The black pixels are connected, i.e., there is only one black region. Pixels are connected horizontally and vertically. Given the location (x, y) of one of the black pixels, return the area of the smallest (axis-aligned) rectangle that encloses all black pixels.

For example, given the following image:

[
"0010",
"0110",
"0100"
]
and x = 0, y = 2,



Return 6.



这道题给我们一个二维矩阵，表示一个图片的数据，其中1代表黑像素，0代表白像素，现在让我们找出一个最小的矩阵可以包括所有的黑像素，还给了我们一个黑像素的坐标，我们先来看Brute Force的方法，这种方法的效率不高，遍历了整个数组，如果遇到了1，就更新矩形的返回
 */
<?php
/**
 *Maximal Rectangle 最大矩形


Given a 2D binary matrix filled with 0's and 1's, find the largest rectangle containing all ones and return its area.



此题是之前那道的 Largest Rectangle in Histogram 直方图中最大的矩形 的扩展，这道题的二维矩阵每一层向上都可以看做一个直方图，输入矩阵有多少行，就可以形成多少个直方图，对每个直方图都调用 Largest Rectangle in Histogram 直方图中最大的矩形 中的方法，就可以得到最大的矩形面积。那么这道题唯一要做的就是将每一层构成直方图，由于题目限定了输入矩阵的字符只有 '0' 和 '1' 两种，所以处理起来也相对简单。方法是，对于每一个点，如果是‘0’，则赋0，如果是 ‘1’，就赋 之前的height值加上1。
 */
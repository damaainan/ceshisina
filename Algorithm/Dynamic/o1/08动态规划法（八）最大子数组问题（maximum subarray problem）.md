<script type="text/javascript" src="http://localhost/MathJax/latest.js?config=default"></script>

## 动态规划法（八）最大子数组问题（maximum subarray problem）

来源：[https://www.cnblogs.com/jclian91/p/9151120.html](https://www.cnblogs.com/jclian91/p/9151120.html)

2018-06-07 16:01


## 问题简介

  本文将介绍计算机算法中的经典问题——最大子数组问题（maximum subarray problem）。所谓的最大子数组问题，指的是：给定一个数组A，寻找A的和最大的非空连续子数组。比如，数组 A = [-2, -3, 4, -1, -2, 1, 5, -3]， 最大子数组应为[4, -1, -2, 1, 5],其和为7。

  首先，如果A中的元素全部为正（或非负数），则最大子数组就是它本身；如果A中的元素全部为负，则最大子数组就是第一个元素组成的数组。以上两种情形是平凡的，那么，如果A中的元素既有正数，又有负数，则该如何求解呢？本文将介绍该问题的四种算法，并给出后面三种算法的Python语言实现，解决该问题的算法如下：


* 暴力求解
* 分治法
* Kadane算法
* 动态规划法


  下面就这四种算法做详细介绍。
## 暴力求解

  假设数组的长度为n，暴力求解方法的思路是很简单的，就是将子数组的开始坐标和结束坐标都遍历一下，这样共有\\(C_{n}^{2}\\) 中组合方式，再考虑这所有组合方式中和最大的情形即可。

  该算法的运行时间为\\(O(n^{2})\\) ,效率是很低的。那么，还有其它高效的算法吗？
## 分治法

  分治法的基本思想是将问题划分为一些子问题，子问题的形式与原问题一样，只是规模更小，递归地求解出子问题，如果子问题的规模足够小，则停止递归，直接求解，最后将子问题的解组合成原问题的解。

  对于最大子数组，我们要寻求子数组A[low...high]的最大子数组。令mid为该子数组的中央位置，我们考虑求解两个子数组A[low...mid]和A[mid+1...high]。A[low...high]的任何连续子数组A[i...j]所处的位置必然是以下三种情况之一：


* 完全位于子数组A[low...mid]中,因此\\(low\leq i\leq j \leq mid.\\) 
* 完全位于子数组A[mid+1...high]中,因此\\(mid< i\leq j \leq high.\\) 
* 跨越了中点，因此\\(low \leq i \leq mid < j \leq high.\\) 


因此，最大子数组必定为上述3种情况中的最大者。对于情形1和情形2，可以递归地求解，剩下的就是寻找跨越中点的最大子数组。

任何跨越中点的子数组都是由两个子数组A[i...mid]和A[mid+1...j]组成，其中 \\(low \leq i \leq mid\\) 且\\(mid< j\leq high\\) .因此，我们只需要找出形如A[i...mid]和A[mid+1...j]的最大子数组，然后将其合并即可，这可以在线性时间内完成。过程FIND-MAX-CROSSING-SUBARRAY接收数组A和下标low、mid和high作为输入，返回一个下标元组划定跨越中点的最大子数组的边界，并返回最大子数组中值的和。其伪代码如下：

```python
FIND-MAX-CROSSING-SUBARRAY(A, low, mid, high):
left-sum = -inf
sum = 0
for i = mid downto low
    sum = sum + A[i]
    if sum > left-sum
        left-sum = sum
        max-left = i
        
right-sum = -inf
sum = 0
for j = mid+1 to high
    sum = sum + A[j]
    if sum > right-sum
        right-sum = sum
        max-right = i
        
return (max-left, max-right, left-sum+right+sum)
```

  有了FIND-MAX-CROSSING-SUBARRAY我们可以找到跨越中点的最大子数组，于是，我们也可以设计求解最大子数组问题的分治算法了，其伪代码如下：

```python
FIND-MAXMIMUM-SUBARRAY(A, low, high):
if high = low
    return (low, high, A[low])
else 
    mid = floor((low+high)/2)
    (left-low, left-high, left-sum) = FIND-MAXMIMUM-SUBARRAY(A, low, mid)
    (right-low, right-high, right-sum) = FIND-MAXMIMUM-SUBARRAY(A, mid+1, high)
    (cross-low, cross-high, cross-sum) = FIND-MAXMIMUM-SUBARRAY(A, low, mid, high)
    
    if left-sum >= right-sum >= cross-sum
        return (left-low, left-high, left-sum)
    else right-sum >= left-sum >= cross-sum
        return (right-low, right-high, right-sum)
    else
        return (cross-low, cross-high, cross-sum)
```

  显然这样的分治算法对于初学者来说，有点难度，但是熟能生巧, 多学多练也就不难了。该分治算法的运行时间为\\(O(n*logn).\\)
## Kadane算法

  Kadane算法的伪代码如下：

```
Initialize:
    max_so_far = 0
    max_ending_here = 0

Loop for each element of the array
  (a) max_ending_here = max_ending_here + a[i]
  (b) if(max_ending_here < 0)
            max_ending_here = 0
  (c) if(max_so_far < max_ending_here)
            max_so_far = max_ending_here
return max_so_far
```

  Kadane算法的简单想法就是寻找所有连续的正的子数组（max_ending_here就是用来干这事的），同时，记录所有这些连续的正的子数组中的和最大的连续数组。每一次我们得到一个正数，就将它与max_so_far比较，如果它的值比max_so_far大，则更新max_so_far的值。
## 动态规划法

   用MS[i]表示最大子数组的结束下标为i的情形，则对于i-1，有：
\\[MS[i] = max\{MS[i-1], A[i]\}.\\] 

这样就有了一个子结构，对于初始情形，\\(MS[1]=A[1].\\) 遍历i, 就能得到MS这个数组，其最大者即可最大子数组的和。
## 总结

   可以看到以上四种算法，每种都有各自的优缺点。对于暴力求解方法，想法最简单，但是算法效率不高。Kanade算法简单高效，但是不易想到。分治算法运行效率高，但其分支过程的设计较为麻烦。动态规划法想法巧妙，运行效率也高，但是没有普遍的适用性。
## Python程序

   下面将给出分治算法，Kanade算法和动态规划法来求解最大子数组问题的Python程序， 代码如下：

```python
# -*- coding: utf-8 -*-
__author__ = 'Jclian'

import math

# find max crossing subarray in linear time
def find_max_crossing_subarray(A, low, mid, high):
    max_left, max_right = -1, -1

    # left part of the subarray
    left_sum = float("-Inf")
    sum = 0
    for i in range(mid, low - 1, -1):
        sum += A[i]
        if (sum > left_sum):
            left_sum = sum
            max_left = i

    # right part of the subarray
    right_sum = float("-Inf")
    sum = 0
    for j in range(mid + 1, high + 1):
        sum += A[j]
        if (sum > right_sum):
            right_sum = sum
            max_right = j

    return max_left, max_right, left_sum + right_sum

# using divide and conquer to solve maximum subarray problem
# time complexity: n*logn
def find_maximum_subarray(A, low, high):
    if (high == low):
        return low, high, A[low]
    else:
        mid = math.floor((low + high) / 2)
        left_low, left_high, left_sum = find_maximum_subarray(A, low, mid)
        right_low, right_high, right_sum = find_maximum_subarray(A, mid + 1, high)
        cross_low, cross_high, cross_sum = find_max_crossing_subarray(A, low, mid, high)
        if (left_sum >= right_sum and left_sum >= cross_sum):
            return left_low, left_high, left_sum
        elif (right_sum >= left_sum and right_sum >= cross_sum):
            return right_low, right_high, right_sum
        else:
            return cross_low, cross_high, cross_sum

# Python program to find maximum contiguous subarray
# Kadane’s Algorithm
def maxSubArraySum(a, size):
    max_so_far = float("-inf")
    max_ending_here = 0

    for i in range(size):
        max_ending_here = max_ending_here + a[i]
        if (max_so_far < max_ending_here):
            max_so_far = max_ending_here

        if max_ending_here < 0:
            max_ending_here = 0

    return max_so_far

# using dynamic programming to slove maximum subarray problem
def DP_maximum_subarray(arr):
    t = len(arr)
    MS = [0]*t
    MS[0] = arr[0]

    for i in range(1, t):
        MS[i] = max(MS[i-1]+arr[i], arr[i])

    return MS

def main():
    # example of array A
    A = [13,-3,-25,20,-3,-16,-23,18,20,-7,12,-5,-22,15,-4,7]
    # A = [-2, 2, -3, 4, -1, 2, 1, -5, 3]
    # A = [0,-2, 3, 5, -1, 2]
    # A = [-9, -2, -3, -5, -3]
    # A = [1, 2, 3, 4, 5]
    # A = [-2, -3, 4, -1, -2, 1, 5, -3]

    print('using divide and conquer...')
    print("Maximum contiguous sum is",find_maximum_subarray(A, 0, len(A) - 1), '\n')

    print('using Kanade Algorithm...')
    print("Maximum contiguous sum is", maxSubArraySum(A, len(A)), '\n')

    print('using dynamic programming...')
    MS = DP_maximum_subarray(A)
    print("Maximum contiguous sum is", max(MS), '\n')

main()
```

输出结果如下：

```
using divide and conquer...
Maximum contiguous sum is (7, 10, 43) 

using Kanade Algorithm...
Maximum contiguous sum is 43 

using dynamic programming...
Maximum contiguous sum is 43 
```
## 参考文献


* 算法导论（第三版） 机械工业出版社
* [https://www.geeksforgeeks.org/largest-sum-contiguous-subarray/][100]
* [https://algorithms.tutorialhorizon.com/dynamic-programming-maximum-subarray-problem/][101]

 **`注意：`** 本人现已开通两个微信公众号： 用Python做数学（微信号为：python_math）以及轻松学会Python爬虫（微信号为：easy_web_scrape）， 欢迎大家关注哦~~

[100]: https://www.geeksforgeeks.org/largest-sum-contiguous-subarray/
[101]: https://algorithms.tutorialhorizon.com/dynamic-programming-maximum-subarray-problem/
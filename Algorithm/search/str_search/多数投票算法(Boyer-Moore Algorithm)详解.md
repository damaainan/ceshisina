# 多数投票算法(Boyer-Moore Algorithm)详解

<font face=微软雅黑>

> 写在前面：我在刷LeetCode 169 时碰到了这个问题，并且在评论区找到了这个方法，不过我发现CSDN上对其进行解读的博客大多停留在知其然而不知其所以然的层面，所以准备在此做一个较为详细的解读，重点在于介绍其原理。

## **问题描述**

给定一个无序数组，有n个元素，找出其中的一个多数元素，多数元素出现的次数大于⌊ n/2 ⌋，注意数组中也可能不存在多数元素。

## **一般解法**

1. 先对数组排序，然后取中间位置的元素，再对数据扫描一趟来判断此元素是否为多数元素。时间复杂度O(nlog(n))，空间复杂度O(1)。
1. 使用一个hash表，对数组进行一趟扫描统计每个元素出现的次数，即可得到多数元素。时间复杂度O(n)，空间复杂度O(n)。

## **Boyer-Moore 算法**

该[算法][0]时间复杂度为O(n)，空间复杂度为O(1)，只需要对原数组进行两趟扫描，并且简单易实现。第一趟扫描我们得到一个候选节点candidate，第二趟扫描我们判断candidate出现的次数是否大于⌊ n/2 ⌋。

第一趟扫描中，我们需要记录2个值：

1. candidate，初值可以为任何数
1. count，初值为0

之后，对于数组中每一个元素，首先判断count是否为0，若为0，则把candidate设置为当前元素。之后判断candidate是否与当前元素相等，若相等则count+=1，否则count-=1。

```python
    candidate = 0
    count = 0
    for value in input:
      if count == 0:
        candidate = value
      if candidate == value:
        count += 1
      else:
        count -= 1
```

在第一趟扫描结束后，如果数组中存在多数元素，那么candidate即为其值，如果原数组不存在多数元素，则candidate的值没有意义。所以需要第二趟扫描来统计candidate出现的次数来判断其是否为多数元素。

代码虽简单，但我们不光要知其然，更要知其所以然，探究代码背后的原理往往可以收获更多。

## **原理解析**

为了解析算法的原理，我们只要考虑存在多数元素的情况即可，因为第二趟扫描可以检测出不存在多数元素的情况。

举个例子，我们的输入数组为**[1,1,0,0,0,1,0]**，那么**0**就是多数元素。   
首先，candidate被设置为第一个元素1，count也变成1，由于1不是多数元素，所以当扫描到数组某个位置时，count一定会减为0。在我们的例子中，当扫描到第四个位置时，count变成0.

count 值变化过程：   
**[1,2,1,0……**

当count变成0时，对于每一个出现的1，我们都用一个0与其进行抵消，所以我们消耗掉了与其一样多的0，而0是多数元素，这意味着当扫描到第四个位置时，我们已经最大程度的消耗掉了多数元素。然而，对于数组从第五个位置开始的剩余部分，0依然是其中的多数元素(注意，多数元素出现次数大于⌊ n/2 ⌋，而我们扫描过的部分中多数元素只占一般，那剩余部分中多数元素必然还是那个数字)。如果之前用于抵消的元素中存在非多数元素，那么数组剩余部分包含的多数元素就更多了。

类似的，假设第一个数字就是多数元素，那么当count减为0时，我们消耗掉了与多数元素一样多的非多数元素，那么同样道理，数组剩余部分中的多数元素数值不变。

这两种情况证明了关键的一点：**数组中从candidate被赋值到count减到0的那一段可以被去除，余下部分的多数元素依然是原数组的多数元素**。我们可以不断重复这个过程，直到扫描到数组尾部，那么count必然会大于0，而且这个count对应的candinate就是原数组的多数元素。

## **分布式Boyer-Moore**

Boyer-Moore还有一个优点，那就是可以使用并行算法实现。相关算法可见[Finding the Majority Element in Parallel][2]  
其基本思想为对原数组采用分治的方法，把数组划分成很多段(每段大小可以不相同)，在每段中计算出candidate-count二元组，然后得到最终结果。

举个例子，原数组为**[1,1,0,1,1,0,1,0,0]**  
划分1：   
**[1,1,0,1,1] –> (candidate,count)=(1,3)**  
划分2：   
**[0,1,0,0] –> (candidate,count)=(0,2)**  
根据(1,3)和(0,2)可得，原数组的多数元素为1.

正因为这个特性，考虑若要从一个非常大的数组中寻找多数元素，数据量可能多大数百G，那么我们甚至可以用MapReduce的方式来解决这个问题。

## **参考**

1. [https://gregable.com/2013/10/majority-vote-algorithm-find-majority.html][3]
1. [The Boyer-Moore Majority Vote Algorithm][4]
1. [Finding the Majority Element in Parallel][2]

</font>

[0]: http://lib.csdn.net/base/datastructure
[1]: http://lib.csdn.net/base/python
[2]: http://www.crm.umontreal.ca/pub/Rapports/3300-3399/3302.pdf
[3]: https://gregable.com/2013/10/majority-vote-algorithm-find-majority.html
[4]: http://www.cs.rug.nl/~wim/pub/whh348.pdf
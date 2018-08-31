# [用 JavaScript 实现链表操作 - 15 Merge Sort][0]

[**darkbaby123**][4] 2月18日发布 

对链表进行归并排序，系列目录见 [前言和目录][5] 。

# 需求

实现函数 mergeSort() 进行归并排序。注意这种排序法需要使用递归。在 [frontBackSplit()][6] 和 [sortedMerge()][7] 两个函数的帮助下，你可以很轻松的写一个递归的排序。基本算法是，把一个链表切分成两个更小的链表，递归地对它们进行排序，最终把两个排好序的小链表合成完整的链表。

    var list = 4 -> 2 -> 1 -> 3 -> 8 -> 9 -> null
    mergeSort(list) === 1 -> 2 -> 3 -> 4 -> 8 -> 9 -> null

# 解法

归并排序的运行方式是，递归的把一个大链表切分成两个小链表。切分到最后就全是单节点链表了，而单节点链表可以被认为是已经排好序的。这时候再两两合并，最终会得到一个完整的已排序链表。

因为切分和合并两个最重要的功能都已经实现，需要思考的就只是如何递归整个过程了。我们分析一下可以把整个过程分成：

1. 用 frontBackSplit() 把链表切分成两个，分别叫 first 和 second 。
1. 对 first 和 second 排序。
1. 用 sortedMerge() 把排好序的两个链表合并起来。

其中第 2 步就是递归的点，因为排序这个事情恰好是 mergeSort 本身可以做的。

代码如下：
```js
    const { Node } = require('./00-utils')
    const { frontBackSplit } = require('./12-front-back-split')
    const { sortedMerge } = require('./14-sorted-merge')
    
    function mergeSort(list) {
      if (!list || !list.next) return list
    
      const first = new Node()
      const second = new Node()
      frontBackSplit(list, first, second)
    
      return sortedMerge(mergeSort(first), mergeSort(second))
    }
```
[0]: /a/1190000008398162
[1]: /t/%E7%AE%97%E6%B3%95/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/javascript/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000008243727
[7]: https://segmentfault.com/a/1190000008397427
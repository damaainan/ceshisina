# [用 JavaScript 实现链表操作 - 16 Sorted Intersect][0]

[**darkbaby123**][4] 2月20日发布 

一次遍历取两个排序链表的交集，系列目录见 [前言和目录][5] 。

# 需求

实现函数 sortedIntersect() 取两个已排序的链表的交集，交集指两个链表都有的节点，节点不一定连续。每个链表应该只遍历一次。结果链表中不能包含重复的节点。

    var first = 1 -> 2 -> 2 -> 3 -> 3 -> 6 -> null
    var second = 1 -> 3 -> 4 -> 5 -> 6 -> null
    sortedIntersect(first, second) === 1 -> 3 -> 6 -> null

# 分析

最容易想到的解法可能是从链表 A 中取一个节点，然后遍历链表 B 找到相同的节点加入结果链表，最后取链表 A 的下一个节点重复该步骤。但这题有 **每个链表只能遍历一次** 的限制，那么如何做呢？

我们先假象有两个指针 p1 和 p2，分别指向两个链表的首节点。当我们对比 p1 和 p2 的值时，有这几种情况：

1. p1.data === p2.data ，这时节点肯定交集，加入结果链表中。因为两个节点都用过了，我们可以同时后移 p1 和 p2 比较下一对节点。
1. p1.data < p2.data ，我们应该往后移动 p1 ，不动 p2 ，因为链表是升序排列的，p1 的后续节点有可能会跟 p2 一样大。
1. p1.data > p2.data ，跟上面相反，移动 p2 。
1. p1 或 p2 为空，后面肯定没有交集了，遍历结束。

基本思路就是这样，递归和循环都是如此。

# 递归解法

代码如下：
```js
    function sortedIntersect(first, second) {
      if (!first || !second) return null
    
      if (first.data === second.data) {
        return new Node(first.data, sortedIntersect(nextDifferent(first), nextDifferent(second)))
      } else if (first.data < second.data) {
        return sortedIntersect(first.next, second)
      } else {
        return sortedIntersect(first, second.next)
      }
    }
    
    function nextDifferent(node) {
      let nextNode = node.next
      while (nextNode && nextNode.data === node.data) nextNode = nextNode.next
      return nextNode
    }
```
需要注意的是不能加入重复节点的判断。我是在第 5 行两个链表的节点相等后，往后遍历到下一个值不同的节点，为此单独写了个 nextDifferent 函数。这个做法比较符合我的思路，但其实也可以写进循环体中，各位可以自行思考。

# 循环解法

代码如下，不赘述了：
```js
    function sortedIntersectV2(first, second) {
      const result = new Node()
      let [pr, p1, p2] = [result, first, second]
    
      while (p1 || p2) {
        if (!p1 || !p2) break
    
        if (p1.data === p2.data) {
          pr = pr.next = new Node(p1.data)
          p1 = nextDifferent(p1)
          p2 = nextDifferent(p2)
        } else if (p1.data < p2.data) {
          p1 = p1.next
        } else {
          p2 = p2.next
        }
      }
    
      return result.next
    }
```
[0]: /a/1190000008416965
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
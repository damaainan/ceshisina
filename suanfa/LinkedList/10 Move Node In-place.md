# [用 JavaScript 实现链表操作 - 10 Move Node In-place][0]

[**darkbaby123**][4] 1月11日发布 


用 in-place 的方式把一个链表的首节点移到另一个链表（不改变链表的引用），系列目录见 [前言和目录][5] 。

# 需求

实现一个 moveNode() 函数，把源链表的头结点移到目标链表的开头。要求是不能修改两个链表的引用。

    var source = 1 -> 2 -> 3 -> null
    var dest = 4 -> 5 -> 6 -> null
    moveNode(source, dest)
    source === 2 -> 3 -> null
    dest === 1 -> 4 -> 5 -> 6 -> null

当碰到以下的情况应该抛出异常：

* 源链表为 null
* 目标链表为 null
* 源链表是空节点，data 属性为 null 的节点定义为空节点。

跟 [前一个 kata][6] 不同的是，这个 kata 是在不改变引用的情况下修改两个链表自身。因此 moveNode() 函数不需要返回值。同时这个 kata 也提出了 **空节点** 的概念。空节点会用于目标链表为空的情况（为了保持引用），在函数执行之后，目标链表会由空节点变成一个包含一个节点的链表。

你可以使用 [第一个 kata][7] 的 push 方法。

# 最优的方案

这个算法考的是对链表节点的插入和删除。基本只对 source 和 dest 分别做一次操作，所以不用区分递归和循环。大致思路为：

1. 对 source 做删除一个节点的操作。如果只有一个节点就直接置空。如果有多个节点，就把第二个节点的值赋给头节点，然后让头结点指向第三个节点。
1. 对 dest 做插入一个节点的操作。如果头结点为空就直接赋值，否则把头结点复制一份，作为第二个节点插入到链表中，再把新值赋给头结点。

代码如下：
```js
    function moveNode(source, dest) {
      if (!source || !dest || source.data === null) throw new Error("invalid arguments")
    
      const data = source.data
    
      if (source.next) {
        source.data = source.next.data
        source.next = source.next.next
      } else {
        source.data = null
      }
    
      if (dest.data === null) {
        dest.data = data
      } else {
        dest.next = new Node(dest.data, dest.next)
        dest.data = data
      }
    }
```
# 递归方案

这是我最开始思考的方案，差别在于对 dest 如何插入新节点的处理上用了递归。思路是把所有节点的 data 往后移一位，即把新值赋给第一个节点，第一个节点的值赋给第二个节点，第二个节点的值赋给第三个节点，以此类推。但实际操作中的顺序必须是反的，就是把倒数第二个节点的值赋给最后一个节点，倒数第三个节点的值赋给倒数第二个节点…… 这个思路对 dest 操作了 N 次，不如上一个解法的 1 次操作高效。不过也算是个有意思的递归用例，所以我仍然把它放了上来。

代码如下，主要看 pushInPlaceV2 ：
```js
    function moveNodeV2(source, dest) {
      if (source === null || dest === null || source.isEmpty()) {
        throw new Error('invalid arguments')
      }
    
      pushInPlaceV2(dest, source.data)
    
      if (source.next) {
        source.data = source.next.data
        source.next = source.next.next
      } else {
        source.data = null
      }
    }
    
    function pushInPlaceV2(head, data) {
      if (!head) return new Node(data)
    
      if (!head.isEmpty()) head.next = pushInPlaceV2(head.next, head.data)
      head.data = data
      return head
    }
```
# 总结

总是使用递归会产生惯性，导致忽略了数据结构的基本特性。链表的特性就是插入和删除的便利，改改引用就成了。

算法相关的代码和测试我都放在 [GitHub][8] 上，如果对你有帮助请帮我点个赞！

[0]: /a/1190000008085135
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000008051315
[7]: https://segmentfault.com/a/1190000007625419
[8]: https://github.com/darkbaby123/algorithm-linked-list
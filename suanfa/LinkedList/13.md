# [用 JavaScript 实现链表操作 - 13 Shuffle Merge][0]

[**darkbaby123**][4] 2月18日发布 


把两个链表洗牌合并成一个，系列目录见 [前言和目录][5] 。

# 需求

实现函数 shuffleMerge() 把两个链表合并成一个。新链表的节点是交叉从两个链表中取的。这叫洗牌合并。举个例子，当传入的链表为 1 -> 2 -> 3 -> null 和 7 -> 13 -> 1 -> null 时，合并后的链表为 1 -> 7 -> 2 -> 13 -> 3 -> 1 -> null 。如果合并过程中一个链表的数据先取完了，就从另一个链表中取剩下的数据。这个函数应该返回一个新链表。

    var first = 3 -> 2 -> 8 -> null
    var second = 5 -> 6 -> 1 -> 9 -> 11 -> null
    shuffleMerge(first, second) === 3 -> 5 -> 2 -> 6 -> 8 -> 1 -> 9 -> 11 -> null

如果参数之一为空，应该直接返回另一个链表（即使另一个链表也为空），不需要抛异常。

# 递归解法 1

代码如下：
```js
    function shuffleMerge(first, second) {
      if (!first || !second) return first || second
    
      const list = new Node(first.data, new Node(second.data))
      list.next.next = shuffleMerge(first.next, second.next)
      return list
    }
```
解题思路是，首先判断是否有一个链表为空，有就返回另一个，结束递归。这个判断过了，下面肯定是两个链表都不为空的情况。我们依次从两个链表中取第一个节点组合成新链表，然后递归 shuffleMerge 两个链表的后续节点，并把结果衔接到 list 后面。这段代码基本跟题目描述的意思一致。

# 递归解法 2

在上面的基础上我们还能做个更聪明的版本，代码如下：

    function shuffleMergeV2(first, second) {
      if (!first || !second) return first || second
      return new Node(first.data, shuffleMerge(second, first.next))
    }

通过简单的调换 first 和 second 的顺序，我们能把递归过程从 “先取 first 的首节点再取 second 的首节点” 变成 “总总是取 first 的首节点” 。解法 1 中的三行代码简化成了一行。

# 循环解法

循环其实才是本题的考点，因为这题主要是考指针（引用）操作。尤其是把 “依次移动两个链表的指针” 写进一个循环里。不过上个解法中调换两个链表顺序的方式也可以用到这里。代码如下：
```js
    function shuffleMergeV3(first, second) {
      const result = new Node()
      let pr = result
      let [p1, p2] = [first, second]
    
      while (p1 || p2) {
        if (p1) {
          pr.next = new Node(p1.data)
          pr = pr.next
          p1 = p1.next
        }
        [p1, p2] = [p2, p1]
      }
    
      return result.next
    }
```
首先我们生成一个 dummy node result ，同时建立一个 pr 代表 result 的尾节点（方便插入）。两个链表的指针分别叫 p1 和 p2 。在每次循环中我们都把 p1 的节点数据写到 result 链表的末尾，然后修改指针指向下一个节点。通过 12 行的调换指针，我们可以保证下一次循环就是对另一个链表进行操作了。这样一直遍历到两个链表末尾，返回 result.next 结束。

[0]: /a/1190000008396683
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
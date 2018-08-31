# [用 JavaScript 实现链表操作 - 07 Append][0]

[**darkbaby123**][4] 1月8日发布 


把一个链表连接到另一个链表的末尾。系列目录见 [前言和目录][5] 。

# 需求

实现一个 append() 函数，把两个链表连接起来，并返回连接后的链表头结点。

    var listA = 1 -> 2 -> 3 -> null
    var listB = 4 -> 5 -> 6 -> null
    append(listA, listB) === 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> null

如果两个链表都是 null 就返回 null ，如果其中一个是 null 就返回另一个链表。

# 递归版本

append 本身就可以作为递归的逻辑。append(listA, listB) 实际上等于 listA.next = append(listA.next, listB) ，直到 listA 递归到末尾 null ，这时 append(null, listB) 直接返回 listB 即可。加上边界条件判断，代码如下：
```js
    function append(listA, listB) {
      if (!listA) return listB
      if (!listB) return listA
    
      listA.next = append(listA.next, listB)
      return listA
    }
```
# 循环版本

循环的思路是，在 listA 和 listB 都不为空的情况下，先找到 listA 的尾节点，假设为 node ，然后 node.next = listB 即可。代码如下：
```js
    function appendV2(listA, listB) {
      if (!listA) return listB
      if (!listB) return listA
    
      let node = listA
      while (node.next) node = node.next
    
      node.next = listB
      return listA
    }
```
[0]: /a/1190000008047926
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
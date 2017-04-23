# [用 JavaScript 实现链表操作 - 05 Sorted Insert][0]

[**darkbaby123**][4] 2016年12月25日发布 


把节点插入一个已排序的链表。系列目录见 [前言和目录][5] 。

# 需求

写一个 sortedInsert() 函数，把一个节点插入一个已排序的链表中，链表为升序排列。这个函数接受两个参数：一个链表的头节点和一个数据，并且始终返回新链表的头节点。

    sortedInsert(1 -> 2 -> 3 -> null, 4) === 1 -> 2 -> 3 -> 4 -> null)
    sortedInsert(1 -> 7 -> 8 -> null, 5) === 1 -> 5 -> 7 -> 8 -> null)
    sortedInsert(3 -> 5 -> 9 -> null, 7) === 3 -> 5 -> 7 -> 9 -> null)

# 递归版本

我们可以从简单的情况推演递归的算法。下面假定函数签名为 sortedInsert(head, data) 。

当 head 为空，即空链表，直接返回新节点：

    if (!head) return new Node(data, null)

当 head 的值大于或等于 data 时，新节点也应该插入头部：

    if (head.data >= data) return new Node(data, head)

如果以上两点都不满足，data 就应该插入后续的节点了，这种 “把数据插入某链表” 的逻辑恰好符合 sortedInsert 的定义，因为这个函数始终返回修改后的链表，我们可以新链表赋值给 head.next 完成链接：

    head.next = sortedInsert(head.next, data)
    return head

整合起来代码如下，非常简单并且有表达力：
```js
    function sortedInsert(head, data) {
      if (!head || data <= head.data) return new Node(data, head)
    
      head.next = sortedInsert(head.next, data)
      return head
    }
```
# 循环版本

循环逻辑是这样：从头到尾检查每个节点，对第 n 个节点，如果数据小于或等于节点的值，则新建一个节点插入节点 n 和节点 n-1 之间。如果数据大于节点的值，则对下个节点做同样的判断，直到结束。

先上代码：
```js
    function sortedInsertV2(head, data) {
      let node = head
      let prevNode
    
      while (true) {
        if (!node || data <= node.data) {
          let newNode = new Node(data, node)
          if (prevNode) {
            prevNode.next = newNode
            return head
          } else {
            return newNode
          }
        }
    
        prevNode = node
        node = node.next
      }
    }
```
这段代码比较复杂，主要有几个边界情况处理：

1. 函数需要始终返回新链表的头，但插入的节点可能在链表头部或者其他地方，所以返回值需要判断是返回新节点还是 head 。
1. 因为插入节点的操作需要连接前后两个节点，循环体要维护 prevNode 和 node 两个变量，这也间接导致 for 的写法会比较麻烦，所以才用 while 。

# 循环版本 - dummy node

我们可以用 [上一个 kata][6] 中提到的 dummy node 来解决链表循环中头结点的 if/else 判断，从而简化一下代码：
```js
    function sortedInsertV3(head, data) {
      const dummy = new Node(null, head)
      let prevNode = dummy
      let node = dummy.next
    
      while (true) {
        if (!node || node.data > data) {
          prevNode.next = new Node(data, node)
          return dummy.next
        }
    
        prevNode = node
        node = node.next
      }
    }
```
这段代码简化了第一版循环中返回 head 还是 new Node(...) 的问题。但能不能继续简化一下每次循环中维护两个节点变量的问题呢？

# 循环版本 - dummy node & check next node

为什么要在循环中维护两个变量 prevNode 和 node ？这是因为新节点要插入两个节点之间，而我们每次循环的当前节点是 node ，单链表中的节点没办法引用到上一个节点，所以才需要维护一个 prevNode 。

如果在每次循环中检查的主体是 node.next 呢？这个问题就解决了。换言之，我们检查的是数据是否适合插入到 node 和 node.next 之间。这种做法的唯一问题是第一次循环，我们需要 node.next 指向头结点，那 node 本身又是什么？ dummy node 正好解决了这个问题。这块有点绕，不懂的话可以仔细想想。这是链表的一个常用技巧。

简化后的代码如下，顺带一提，因为可以少维护一个变量，while 可以简化成 for 了：
```js
    function sortedInsertV4(head, data) {
      const dummy = new Node(null, head)
    
      for (let node = dummy; node; node = node.next) {
        const nextNode = node.next
        if (!nextNode || nextNode.data >= data) {
          node.next = new Node(data, nextNode)
          return dummy.next
        }
      }
    }
```
# 总结

这个 kata 是递归简单循环麻烦的一个例子，有比较才会理解递归的优雅之处。另外合理使用 dummy node 可以简化不少循环的代码。算法相关的代码和测试我都放在 [GitHub][7] 上，如果对你有帮助请帮我点个赞！

[0]: /a/1190000007912308
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007800288
[7]: https://github.com/darkbaby123/algorithm-linked-list
# [用 JavaScript 实现链表操作 - 04 Insert Nth Node][0]

[**darkbaby123**][4] 2016年12月14日发布 


插入第 N 个节点。系列目录见 [前言和目录][5] 。

# 需求

实现一个 insertNth() 方法，在链表的第 N 个索引处插入一个新节点。

insertNth() 可以看成是 [01 Push & Build List][6] 中的 push() 函数的更通用版本。给定一个链表，一个范围在 0..length 内的索引号，和一个数据，这个函数会生成一个新的节点并插入到指定的索引位置，并始终返回链表的头。

    insertNth(1 -> 2 -> 3 -> null, 0, 7) === 7 -> 1 -> 2 -> 3 -> null)
    insertNth(1 -> 2 -> 3 -> null, 1, 7) === 1 -> 7 -> 2 -> 3 -> null)
    insertNth(1 -> 2 -> 3 -> null, 3, 7) === 1 -> 2 -> 3 -> 7 -> null)

如果索引号超出了链表的长度，函数应该抛出异常。

实现这个函数允许使用第一个 kata 中的 push 方法。

# 递归版本

让我们先回忆一下 push 函数的用处，指定一个链表的头和一个数据，push 会生成一个新节点并添加到链表的头部，并返回新链表的头。比如：

    push(null, 23) === 23 -> null
    push(1 -> 2 -> null, 23) === 23 -> 1 -> 2 -> null

现在看看 insertNth ，假设函数方法签名是 insertNth(head, index, data) ，那么有两种情况：

如果 index === 0 ，则等同于调用 push 。实现为 push(head, data) 。

如果 index !== 0 ，我们可以把下一个节点当成子链表传入 insertNth ，并让 index 减一。insertNth 的返回值一定是个链表，我们把它赋值给 head.next 就行。这就是一个递归过程。如果这次递归的 insertNth 完不成任务，它会继续递归到下一个节点，直到 index === 0 的最简单情况，或 head 为空抛出异常（索引过大）。

完整代码实现为：
```js
    function insertNth(head, index, data) {
      if (index === 0) return push(head, data)
      if (!head) throw 'invalid argument'
      head.next = insertNth(head.next, index - 1, data)
      return head
    }
```
# 循环版本

如果能理解递归版本的 head.next = insertNth(...) ，那么循环版本也不难实现。不同的是，在循环中我们遍历到 index 的前一个节点，然后用 push 方法生成新节点，并赋值给前一个节点的 next 属性形成一个完整的链表。

完整代码实现如下：
```js
    function insertNthV2(head, index, data) {
      if (index === 0) return push(head, data)
    
      for (let node = head, idx = 0; node; node = node.next, idx++) {
        if (idx + 1 === index) {
          node.next = push(node.next, data)
          return head
        }
      }
    
      throw 'invalid argument'
    }
```
这里有一个边界情况要注意。因为 insertNth 要求返回新链表的头。根据 index 是否为 0 ，这个新链表的头可能是生成的新节点，也可能就是老链表的头 。这点如果写进 for 循环就不可避免有 if/else 的返回值判断。所以我们把 index === 0 的情况单独拿出来放在函数顶部。这个边界情况并非无法纳入循环中，我们下面介绍的一个技巧就与此有关。

# 循环版本 - dummy node

在之前的几个 kata 里，我们提到循环可以更好的容纳边界情况，因为一些条件判断都能写到 for 的头部中去。但这个例子的边界情况是返回值不同：

1. 如果 index === 0 ，返回新节点 。
1. 如果 index !== 0 ，返回 head 。新节点会被插入 head 之后的某个节点链条中。

如何解决这个问题呢，我们可以在 head 前面再加入一个节点（数据任意，一般赋值 null）。这个节点称为 dummy 节点。这样一来，不管新节点插入到哪里，dummy.next 都可以引用到修改后的链表。

代码实现如下，注意 return 的不同。
```js
    function insertNthV3(head, index, data) {
      const dummy = push(head, null)
    
      for (let node = dummy, idx = 0; node; node = node.next, idx++) {
        if (idx === index) {
          node.next = push(node.next, data)
          return dummy.next
        }
      }
    
      throw 'invalid argument'
    }
```
dummy 节点是很多链表操作的常用技巧，虽然在这个 kata 中使用 dummy 节点的代码量并没有变少，但这个技巧在后续的一些复杂 kata 中会非常有用。

[0]: /a/1190000007800288
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007625419
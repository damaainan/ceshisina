# [用 JavaScript 实现链表操作 - 18 Recursive Reverse][0]

[**darkbaby123**][4] 2月25日发布 


用递归的方式反转链表，系列目录见 [前言和目录][5] 。

# 需求

实现函数 reverse() 用递归的方式反转链表。例子如下：

    var list = 2 -> 1 -> 3 -> 6 -> 5 -> null
    reverse(list) === 5 -> 6 -> 3 -> 1 -> 2 -> null

# 解法

让我们先思考一下递归的大概解法：
```js
    function reverse(head) {
      const node = new Node(head.data)
      const rest = reverse(head.next)
      // 把 node 放到 rest 的末尾，并返回 rest
    }
```
麻烦的地方就在最后，把节点加入链表的末尾需要首先遍历整个链表，这无疑非常低效。我们在上一个 kata 的循环里是怎么解决的呢？维护一个 result 变量代表反转链表，然后每次把新节点放到 result 的头部，同时把新节点当做新的 result ，大概这个样子：
```js
    let result
    for (let node = list; node; node = node.next) {
      result = new Node(node.data, result)
    }
```
为了在递归里达到同样的效果，我们也必须维护这么一个变量。为了在每次递归过程中都能用到这个变量，我们得把它当函数的参数传递下去，reverse 的函数签名就变成这样：

    function reverse(head, acc) { ... }

这里 acc 就是反转的链表。整理一番后的代码如下：
```js
    function reverse(head, acc = null) {
      return head ? reverse(head.next, new Node(head.data, acc)) : acc
    }
```
上面这段代码同时也是尾递归。在递归函数中开额外的参数很是常见的做法，也是尾递归优化的必要手段。

[0]: /a/1190000008485170
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
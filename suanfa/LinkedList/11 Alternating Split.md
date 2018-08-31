# [用 JavaScript 实现链表操作 - 11 Alternating Split][0]

[**darkbaby123**][4] 2月2日发布 


把一个链表交替切分成两个，系列目录见 [前言和目录][5] 。

# 需求

实现一个 alternatingSplit() 函数，把一个链表切分成两个。子链表的节点应该是在父链表中交替出现的。如果原链表是 a -> b -> a -> b -> a -> null ，则两个子链表分别为 a -> a -> a -> null 和 b -> b -> null 。

    var list = 1 -> 2 -> 3 -> 4 -> 5 -> null
    alternatingSplit(list).first === 1 -> 3 -> 5 -> null
    alternatingSplit(list).second === 2 -> 4 -> null

为了简化结果，函数会返回一个 Context 对象来保存两个子链表，Context 结构如下所示：

    function Context(first, second) {
      this.first = first
      this.second = second
    }

如果原链表为 null 或者只有一个节点，应该抛出异常。

# 递归版本

代码如下：
```js
    function alternatingSplit(head) {
      if (!head || !head.next) throw new Error('invalid arguments')
      return new Context(split(head), split(head.next))
    }
    
    function split(head) {
      const list = new Node(head.data)
      if (head.next && head.next.next) list.next = split(head.next.next)
      return list
    }
```
这个解法的核心思路在于 split ，这个方法接收一个链表并返回一个以奇数位的节点组成的子链表。所以整个算法的解法就能很容易地用 new Context(split(head), split(head.next)) 表示。

# 另一个递归版本

代码如下：
```js
    function alternatingSplitV2(head) {
      if (!head || !head.next) throw new Error('invalid arguments')
      return new Context(...splitV2(head))
    }
    
    function splitV2(head) {
      if (!head) return [null, null]
    
      const first = new Node(head.data)
      const [second, firstNext] = splitV2(head.next)
      first.next = firstNext
      return [first, second]
    }
```
这里的 splitV2 的作用跟整个算法的含义一样 -- 接收一个链表并返回交叉分割的两个子链表（以数组表示）。第一个子链表的头自然是 new Node(head.data) ，第二个子链表呢？它其实是 splitV2(head.next) 的第一个子链表（见第 4 行）。理解这个逻辑后就能明白递归过程。

# 循环版本

代码如下：
```js
    function alternatingSplitV3(head) {
      if (!head || !head.next) throw new Error('invalid arguments')
    
      const first = new Node()
      const second = new Node()
      const tails = [first, second]
    
      for (let node = head, idx = 0; node; node = node.next, idx = idx ? 0 : 1) {
        tails[idx].next = new Node(node.data)
        tails[idx] = tails[idx].next
      }
    
      return new Context(first.next, second.next)
    }
```
这个思路是，先用两个变量代表子链表，然后对整个链表进行一次遍历，分别把节点交替插入每个子链表中。唯一需要考虑的就是在每个循环体中判断节点该插入哪个链表。我用的是 idx 变量，在每轮循环中把它交替设置成 0 和 1 。也有人使用持续增长的 idx 配合取余来做，比如 idx % 2 。做法有很多种，就不赘述了。

这里也用了 dummy node 的技巧来简化 “判断首节点是否为空” 的情况。关于这个技巧可以看看 [Insert Nth Node][6]

[0]: /a/1190000008239747
[1]: /t/%E7%AE%97%E6%B3%95/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/javascript/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007800288
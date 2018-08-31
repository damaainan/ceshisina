# [用 JavaScript 实现链表操作 - 17 Iterative Reverse][0]

[**darkbaby123**][4] 2月24日发布 


用循环的方式反转链表，系列目录见 [前言和目录][5] 。

# 需求

实现方法 reverse() 用循环的方式反转链表，链表应该只遍历一次。注意这个函数直接修改了链表本身，所以不需要返回值。

    var list = 2 -> 1 -> 3 -> 6 -> 5 -> null
    reverse(list)
    list === 5 -> 6 -> 3 -> 1 -> 2 -> null

# 解法

代码如下：
```js
    function reverse(list) {
      if (!list) return null
    
      let result
      for (let node = list; node; node = node.next) {
        result = new Node(node.data, result)
      }
    
      list.data = result.data
      list.next = result.next
    }
```
思路是，从前到后遍历链表，对每个节点复制一份，并让它的 next 指向前一个节点。最后 result 就是一个反转的新链表了。那么如何修改 list 呢？很简单，把 result 的首节点值赋给 list ，然后让 list 指向 result 的第二个节点就行。

[0]: /a/1190000008476661
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
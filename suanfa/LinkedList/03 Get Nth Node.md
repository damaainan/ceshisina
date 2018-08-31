# [用 JavaScript 实现链表操作 - 03 Get Nth Node][0]

[**darkbaby123**][4] 2016年12月08日发布 


获得链表的第 N 个节点。系列目录见 [前言和目录][5] 。

# 需求

实现一个 getNth() 方法，传入一个链表和一个索引，返回索引代表的节点。索引以 0 为起始，第一个元素索引为 0 ，第二个为 1 ，以此类推。比如：

    getNth(1 -> 2 -> 3 -> null, 0).data === 1
    getNth(1 -> 2 -> 3 -> null, 1).data === 2

传入的索引必须是在效范围内，即 0..length-1 ，如果索引不合法或者链表为空都需要抛出异常。

# 递归版本

假设函数定义为 getNth(head, idx) ，递归过程为：当 idx 为零，直接返回该节点，否则递归调用 getNth(head.next, idx - 1) 。再处理下边界情况就完成了，代码如下：
```js
    function getNth(head, idx) {
      if (!head || idx < 0) throw 'invalid argument'
      if (idx === 0) return head
      return getNth(head.next, idx - 1)
    }
```
# 循环版本

我选择的 for 循环，这样方便把边界情况检查都放到循环里去。如果循环结束还没有查到节点，那肯定是链表或者索引不合法，直接抛异常即可。对比这两个版本和 [02 Length & Count][6] 的例子，不难看出循环可以比递归更容易地处理边界情况，因为一些条件检查可以写进循环的头部，递归就得自己写 if/else 逻辑。
```js
    function getNthV2(head, idx) {
      for (let node = head; node && idx >= 0; node = node.next, idx--) {
        if (idx === 0) return node
      }
      throw 'invalid argument'
    }
```
# 参考资料

[Codewars Kata][7]  
[GitHub 的代码实现][8]  
[GitHub 的测试][9]

[0]: /a/1190000007737715
[1]: /t/%E7%AE%97%E6%B3%95/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/javascript/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007689904?_ea=1435259
[7]: https://www.codewars.com/kata/linked-lists-get-nth-node/javascript
[8]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/lib/03-get-nth-node.js
[9]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/test/03-get-nth-node.test.js
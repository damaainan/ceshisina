# [用 JavaScript 实现链表操作 - 09 Move Node][0]

[**darkbaby123**][4] 1月8日发布 


把一个链表的首节点移到另一个链表。系列目录见 [前言和目录][5] 。

# 需求

实现一个 moveNode() 函数，把源链表的头节点移到目标链表。当源链表为空时函数应抛出异常。为了简化起见，我们会用一个 Context 对象来存储改变后的源链表和目标链表的引用。它也是函数的返回值。

    var source = 1 -> 2 -> 3 -> null
    var dest = 4 -> 5 -> 6 -> null
    moveNode(source, dest).source === 2 -> 3 -> null
    moveNode(source, dest).dest === 1 -> 4 -> 5 -> 6 -> null

这个 kata 是下一个 kata 的简化版，你可以重用 [第一个 kata][6] 的 push 方法。

# 关于 Context

Context 的定义长这个样子，source 代表源链表，dest 代表目标链表。
```js
    function Context(source, dest) {
      this.source = source
      this.dest = dest
    }
```
# 解法

配合 push ，这个 kata 非常简单，注意这个函数没有改变两个链表本身。代码如下：
```js
    function moveNode(source, dest) {
      if (!source) throw new Error('source is empty')
      return new Context(source.next, push(dest, source.data))
    }
```
# 总结

这个 kata 本身很简单，就没有分递归和循环的版本了，其存在意义主要是为了下一个 kata 做铺垫。

算法相关的代码和测试我都放在 [GitHub][7] 上，如果对你有帮助请帮我点个赞！

[0]: /a/1190000008051315
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007625419
[7]: https://github.com/darkbaby123/algorithm-linked-list
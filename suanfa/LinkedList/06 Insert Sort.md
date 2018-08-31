# [用 JavaScript 实现链表操作 - 06 Insert Sort][0]

[**darkbaby123**][4] 2016年12月31日发布 


2016 年末最后一篇，对链表进行插入排序。系列目录见 [前言和目录][5] 。

# 需求

实现一个 insertSort() 函数对链表进行升序排列（插入排序）。实现过程中可以使用 [上一个 kata][6] 中的 sortedInsert() 函数。insertSort() 函数接受链表头为参数并返回排序后的链表头。

    var list = 4 -> 3 -> 1 -> 2 -> null
    insertSort(list) === 1 -> 2 -> 3 -> 4 -> null

如果传入的链表为 null 或者只有一个节点，就原样返回。

# 关于插入排序

插入排序的介绍可以看 [Wikipedia][7] ，大体逻辑为：

1. 建立一个新的空链表。
1. 依次遍历待排序的链表节点，挨个插入新链表的合适位置，始终保持新链表是已排序的。
1. 遍历完成，返回新链表。

观察这段逻辑不难发现，第二个步骤其实就是上个 kata 中 sortedInsert 做的事情 -- 把节点插入一段已排序的链表的合适位置。在此之上稍微包装一下就可以实现 insertSort 。

# 递归版本

首先我们记住两个函数的表达的意思：

1. insertSort 返回链表的排序版本。
1. sortedInsert 把节点插入一个已排序链表的合适位置，并返回修改后的链表（也是已排序的）。

然后我们用递归的思路描述 insertSort 逻辑，应该是先把原链表的第一个节点插入某个已排序的链表的合适位置，这段逻辑可以用 sortedInsert(someList, head.data) 表达。而这个 “某个已排序的链表” ，我们需要它包含除了 head 之外其他的所以节点，这个链表可以用 insertSort(head.next) 来表达。

整理后的代码如下：
```js
    function insertSort(head) {
      if (!head) return null
      return sortedInsert(insertSort(head.next), head.data)
    }
```
# 循环版本

循环版本是最接近算法描述的版本，所以不多赘述。代码如下：
```js
    function insertSort(head) {
      for (var sortedList = null, node = head; node; node = node.next) {
        sortedList = sortedInsert(sortedList, node.data)
      }
      return sortedList
    }
```
# 总结

因为有上个 kata 的函数的帮助，这个插入排序实现起来非常简单。递归版本再次体现了声明式编程的优势。有时候能表达某种数据的不只是变量，也可以是函数。只要我们发现表达合适逻辑的函数，实现过程就会非常简单。

算法相关的代码和测试我都放在 [GitHub][8] 上，如果对你有帮助请帮我点个赞！

[0]: /a/1190000007977789
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000007912308
[7]: https://en.wikipedia.org/wiki/Insertion_sort
[8]: https://github.com/darkbaby123/algorithm-linked-list
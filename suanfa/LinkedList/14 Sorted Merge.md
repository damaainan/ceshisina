# [用 JavaScript 实现链表操作 - 14 Sorted Merge][0]

[**darkbaby123**][4] 2月18日发布 


把两个升序排列的链表合并成一个，系列目录见 [前言和目录][5] 。

# 需求

实现函数 sortedMerge() 把两个升序排列的链表合并成一个新链表，新链表也必须是升序排列的。这个函数应该对每个输入的链表都只遍历一次。

    var first = 2 -> 4 -> 6 -> 7 -> null
    var second = 1 -> 3 -> 5 -> 6 -> 8 -> null
    sortedMerge(first, second) === 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 6 -> 7 -> 8 -> null

有一些边界情况要考虑：first 或 second 可能为 null ，在合并过程中 first 或 second 的数据有可能先取完。如果一个链表为空，就返回另一个链表（即使它也为空），不需要抛出异常。

在做这个 kata 之前，建议先完成 [Shuffle Merge][6] 。

# 递归解法

代码如下：
```js
    function sortedMerge(first, second) {
      if (!first || !second) return first || second
    
      if (first.data <= second.data) {
        return new Node(first.data, sortedMerge(first.next, second))
      } else {
        return new Node(second.data, sortedMerge(first, second.next))
      }
    }
```
跟上个 kata 类似的思路。不过为了保证最后的结果是升序排列的，我们要取两个链表中值更小的首节点，添加到结果链表的末尾。思路就不赘述了 。

# 循环解法

循环是这个 kata 有意思的一点，很多边界情况的判断也发生在这里。很容易写出这样的 if/else ：
```js
    let [p1, p2] = [first, second]
    while (p1 || p2) {
      if (p1 && p2) {
        if (p1.data <= p2.data) {
          // append p1 data to result
        } else {
          // append p2 data to result
        }
      } else if (p1) {
        // append p1 to result
      } else {
        // append p2 to result
      }
    }
```
上面例子里 p1 和 p2 是指向两个链表节点的指针，在循环中它们随时可能变成空，因此要比较数据大小首先就要判断两个都不为空。而且注释中的 append 代码也会有一定重复。

为了解决这个问题，我们可以上个 kata 里调换指针的方法。完整代码如下：
```js
    function sortedMergeV2(first, second) {
      const result = new Node()
      let [pr, p1, p2] = [result, first, second]
    
      while (p1 || p2) {
        // if either list is null, append the other one to the result list
        if (!p1 || !p2) {
          pr.next = (p1 || p2)
          break
        }
    
        if (p1.data <= p2.data) {
          pr = pr.next = new Node(p1.data)
          p1 = p1.next
        } else {
          // switch 2 lists to make sure it's always p1 <= p2
          [p1, p2] = [p2, p1]
        }
      }
    
      return result.next
    }
```
第 7 行判断 p1 或 p2 为空，并且把非空的链表直接添加到 result 末尾，省去了继续循环每个节点。第 17 行的指针调换让 p1 始终小于等于 p2 ，从而避免了重复的 append 代码 。其他技巧如 dummy node 在之前的 kata 都有讲，就不多说了。

[0]: /a/1190000008397427
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://segmentfault.com/a/1190000008396683
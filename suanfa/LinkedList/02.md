# [用 JavaScript 实现链表操作 - 02 Length & Count][0]

[**darkbaby123**][4] 2016年12月04日发布 


计算链表的长度和指定元素的重复次数。系列目录见 [前言和目录][5] 。

# 需求

实现一个 length() 函数来计算链表的长度。
```js
    length(null) === 0
    length(1 -> 2 -> 3 -> null) === 3
```
实现一个 count() 函数来计算指定数字在链表中的重复次数。
```js
    count(null, 1) === 0
    count(1 -> 2 -> 3 -> null, 1) === 1
    count(1 -> 1 -> 1 -> 2 -> 2 -> 2 -> 2 -> 3 -> 3 -> null, 2) === 4
```
# length

## 递归版本

递归是最有表达力的版本。思路非常简单。每个链表的长度 length(head) 都等于 1 + length(head.next) 。空链表长度为 0 。
```js
    function length(head) {
      return head ? 1 + length(head.next) : 0
    }
```
## 循环版本 - while

链表循环第一反应是用 while (node) { node = node.next } 来做，循环外维护一个变量，每次自增 1 即可。
```js
    function lengthV2(head) {
      let len = 0
      let node = head
    
      while (node) {
        len++
        node = node.next
      }
    
      return len
    }
```
## 循环版本 - for

for 和 while 在任何情况下都是可以互换的。我们可以用 for 循环把变量初始化，节点后移的操作都放到一起，简化一下代码量。注意因为 len 要在 for 外部作为返回值使用，我们只能用 var 而不是 let/const 声明变量。
```js
    function lengthV3(head) {
      for (var len = 0, node = head; node; node = node.next) len++
      return len
    }
```
# count

## 递归版本

跟 length 思路类似，区别只是递归时判断一下节点数据。
```js
    function count(head, data) {
      if (!head) return 0
      return (head.data === data ? 1 : 0) + count(head.next, data)
    }
```
## 循环版本

这里我直接演示的 for 版本，思路类似就不多说了。
```js
    function countV2(head, data) {
      for (var n = 0, node = head; node; node = node.next) {
        if (node.data === data) n++
      }
      return n
    }
```
# 参考资料

[Codewars Kata][6]  
[GitHub 的代码实现][7]  
[GitHub 的测试][8]

[0]: /a/1190000007689904
[1]: /t/javascript/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/%E7%AE%97%E6%B3%95/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://www.codewars.com/kata/linked-lists-length-and-count/javascript
[7]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/lib/02-length-and-count.js
[8]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/test/02-length-and-count.test.js
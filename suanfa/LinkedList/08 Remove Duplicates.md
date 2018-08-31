# [用 JavaScript 实现链表操作 - 08 Remove Duplicates][0]

[**darkbaby123**][4] 1月8日发布 

为一个已排序的链表去重，考虑到很长的链表，需要尾调用优化。系列目录见 [前言和目录][5] 。

# 需求

实现一个 removeDuplicates() 函数，给定一个升序排列过的链表，去除链表中重复的元素，并返回修改后的链表。理想情况下链表只应该被遍历一次。

    var list = 1 -> 2 -> 3 -> 3 -> 4 -> 4 -> 5 -> null
    removeDuplicates(list) === 1 -> 2 -> 3 -> 4 -> 5 -> null

如果传入的链表为 null 就返回 null 。

这个解决方案需要考虑链表很长的情况，递归会造成栈溢出，所以递归方案必须用到尾递归。

因为篇幅限制，这里并不解释什么是尾递归，想详细了解的可以先看看 [尾调用][6] 的定义。

# 递归版本 - 非尾递归

对数组或者链表去重本身是个花样很多的算法，但如果链表是已排序的，解法就单一很多了，因为重复的元素都是相邻的。假定链表为 a -> a1 -> a2 ... aN -> b ，其中 a1 到 aN 都是对 a 的重复，那么去重就是把链表变成 a -> b 。

因为递归版本没有循环，所以一次递归操作只能减去一个重复元素，比如第一次去除 a1 ，第二次去除 a2 。

先看一个简单的递归版本，这个版本递归的是 removeDuplicates 自身。先取链表的头结点 head，如果发现它跟之后的节点有重复，就让 head 指向之后的节点（减去一个重复），然后再把 head 放入下一个递归里。如果没有重复，则递归 head 的下一个节点，并把结果指向 head.next 。
```js
    function removeDuplicates(head) {
      if (!head) return null
    
      const nextNode = head.next
      if (nextNode && head.data === nextNode.data) {
        head.next = nextNode.next
        return removeDuplicates(head)
      }
    
      head.next = removeDuplicates(nextNode)
      return head
    }
```
这个版本只有第一个 return removeDuplicates(head) 处是尾递归，最后的 return head 并不是。所以这个解法并不算完全的尾递归，但性能并不算差。经我测试可以处理 30000 个节点的链表，但 40000 个就一定会栈溢出。

# 递归版本 - 尾递归

很多递归没办法自然的写成尾递归，本质原因是无法在多次递归过程中维护共有的变量，这也是循环的优势所在。上面例子中的 head.next = removeDuplicates(nextNode) 就是一个典型，我们需要保留 head 这个变量，好在递归结束把结果赋值给 head.next 。尾递归优化的基本思路，就是把共有的变量继续传给下一个递归过程，这种做法往往需要用到额外的函数参数。下面是一个改变后的尾递归版本：
```js
    function removeDuplicatesV2(head, prev = null, re = null) {
      if (!head) return re
    
      re = re || head
      if (prev && prev.data === head.data) {
        prev.next = head.next
      } else {
        prev = head
      }
    
      return removeDuplicatesV2(head.next, prev, re)
    }
```
我们加了两个变量 prev 和 re 。prev 代表 head 的前一个节点，在递归过程中我们判断的是 prev 和 head 是否有重复。为了最后能返回链表的头我们加了 re 这个参数，它是最后的返回值。re 仅仅指向最开始的 head ，也就是第一次递归的链表的头结点。因为这个算法是修改链表自身，只要链表非空，头结点作为返回值就是确定的，即使链表开头就有重复，被移除的也是头结点之后的节点。

# 如何测试尾递归

首先我们需要一个支持尾递归优化的环境。我测试的环境是 Node v7 。Node 应该是 6.2 之后就支持尾递归优化，但需要指定 harmony_tailcalls 参数开启，默认并不启动。我用的 Mocha 写测试，所以把参数写在 mocha.opts 里，配置如下：

    --use_strict
    --harmony_tailcalls
    --require test/support/expect.js

其次我们需要一个方法来生成很长的，随机重复的，生序排列的链表，我的写法如下：
```js
    // Usage: buildRandomSortedList(40000)
    function buildRandomSortedList(len) {
      let list
      let prevNode
      let num = 1
    
      for (let i = 0; i < len; i++) {
        const node = new Node(randomBool() ? num++ : num)
        if (!list) {
          list = node
        } else {
          prevNode.next = node
        }
        prevNode = node
      }
    
      return list
    }
    
    function randomBool() {
      return Math.random() >= 0.5
    }
```
然后就可以测试了，为了方便同时测试溢出和不溢出的情况，写个 helper ，这个 helper 简单的判断函数是否抛出 RangeError 。因为函数的逻辑已经在之前的测试中保证了，这里就不测试结果是否正确了。
```js
    function createLargeListTests(fn, { isOverflow }) {
      describe(`${fn.name} - max stack size exceed test`, () => {
        it(`${isOverflow ? 'should NOT' : 'should'} be able to handle a big random list.`, () => {
          Error.stackTraceLimit = 10
    
          expect(() => {
            fn(buildRandomSortedList(40000))
          })[isOverflow ? 'toThrow' : 'toNotThrow'](RangeError, 'Maximum call stack size exceeded')
        })
      })
    }
    
    createLargeListTests(removeDuplicates, { isOverflow: true })
    createLargeListTests(removeDuplicatesV2, { isOverflow: false })
```
完整的测试见 [GitHub][7] 。

顺带一提，以上两个递归方案在 Codewars 上都会栈溢出。这是因为 Codewars 虽然用的 Node v6 ，但并没有开启尾递归优化。

# 循环版本

思路一致，就不赘述了，直接看代码：
```js
    function removeDuplicatesV3(head) {
      for (let node = head; node; node = node.next) {
        while (node.next && node.data === node.next.data) node.next = node.next.next
      }
      return head
    }
```
可以看到，因为循环体外的共有变量 node 和 head ，这个例子代码比递归版本要简单直观很多。

# 总结

循环和递归没有孰优孰劣，各有合适的场合。这个 kata 就是一个循环比递归简单的例子。另外，尾递归因为要传递中间变量，所以写起来的感觉会更类似循环而不是正常的递归思路，这也是为什么我对大部分 kata 没有做尾递归的原因 -- 这个教程的目的是展示递归的思路，而尾递归有时候达不到这一点。

算法相关的代码和测试我都放在 [GitHub][8] 上，如果对你有帮助请帮我点个赞！

[0]: /a/1190000008049580
[1]: /t/javascript/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/%E9%93%BE%E8%A1%A8/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://zh.wikipedia.org/wiki/%E5%B0%BE%E8%B0%83%E7%94%A8
[7]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/test/08-remove-duplicates.test.js#L14
[8]: https://github.com/darkbaby123/algorithm-linked-list
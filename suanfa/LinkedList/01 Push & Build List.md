# [用 JavaScript 实现链表操作 - 01 Push & Build List][0]


[**darkbaby123**][4] 2016年11月28日发布 



写两个帮助函数来创建链表。系列目录见 [前言和目录][5] 。

# 需求

写两个方法 push 和 buildList 来初始化链表。尝试在 buildList 中使用 push 。下面的例子中我用 a -> b -> c 来表示链表，这是为了书写方便，并不是 JavaScript 的有效语法。
```js
    let chained = null
    chained = push(chained, 3)
    chained = push(chained, 2)
    chained = push(chained, 1)
    push(chained, 8) === 8 -> 1 -> 2 -> 3 -> null
```
push 用于把一个节点插入到链表的头部。它接受两个参数 head 和 data ，head 可以是一个节点对象或者 null 。这个方法应该始终返回一个新的链表。

buildList 接收一个数组为参数，创建对应的链表。

    buildList([1, 2, 3]) === 1 -> 2 -> 3 -> null

# 定义节点对象

作为链表系列的第一课，我们需要先定义节点对象是什么样子。按照 Codewars 上的设定，一个节点对象有两个属性 data 和 next 。data 是这个节点的值，next 是下一个节点的引用。这是默认的类模板。
```js
    function Node(data) {
      this.data = data
      this.next = null
    }
```
# push

这是 push 的基本实现：
```js
    function push(head, data) {
      const node = new Node(data)
    
      if (head) {
        node.next = head
        return node
      } else {
        return node
      }
    }
```
我更倾向于修改一下 Node 的构造函数，把 next 也当成参数，并且加上默认值，这会让后面的事情简化很多：
```js
    function Node(data = null, next = null) {
      this.data = data
      this.next = next
    }
```
新的 push 实现：
```js
    function push(head, data) {
      return new Node(head, data)
    }
```
# buildList

## 递归版本

这个函数非常适合用递归实现。这是递归的版本：
```js
    function buildList(array) {
      if (!array || !array.length) return null
      const data = array.shift()
      return push(buildList(array), data)
    }
```
递归的思路是，把大的复杂的操作逐步分解成小的操作，直到分解成最基本的情况。拿这个例子解释，给定数组 [1, 2, 3]，递归的实现思路是逐步往链表头部插入数据 3，2，1 ，一共三轮。第一轮相当于 push(someList, 3) 。这个 someList 是什么呢，其实就是 buildList([1, 2]) 的返回值。以此类推：

* 第一轮 push(buildList([1, 2]), 3)
* 第二轮 push(buildList([1]), 2)
* 第三轮 push(buildList([]), 3)

到第三轮就已经是最基本的情况了，数组为空，这时返回 null 代表空节点。

## 循环版本

依照上面的思路，循环也很容易实现，只要反向遍历数组就行。因为循环已经考虑了数组为空的情况，这里就不用进行边界判断了。
```js
    function buildListV2(array) {
      let list = null
      for (let i = array.length - 1; i >= 0; i--) {
        list = push(list, array[i])
      }
      return list
    }
```
## One-liner

结合循环版本的思路和 JavaScript 的数组迭代器，我们可以得出一个 one-liner 版本。
```js
    function buildListV3(array) {
      return (array || []).reduceRight(push, null)
    }
```
这个就不解释了，留给各位自己思考下吧。

# 参考资料

[Codewars Kata][6]  
[GitHub 的代码实现][7]  
[GitHub 的测试][8]

[0]: /a/1190000007625419
[1]: /t/%E9%93%BE%E8%A1%A8/blogs
[2]: /t/%E7%AE%97%E6%B3%95/blogs
[3]: /t/javascript/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
[6]: https://www.codewars.com/kata/linked-lists-push-and-buildonetwothree/javascript
[7]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/lib/01-push-and-build-one-two-three.js
[8]: https://github.com/darkbaby123/algorithm-linked-list/blob/master/test/01-push-and-build-one-two-three.test.js
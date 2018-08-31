# [用 JavaScript 实现链表操作 - 12 Front Back Split][0]

[**darkbaby123**][4] 2月3日发布 


把一个链表居中切分成两个，系列目录见 [前言和目录][5] 。

# 需求

实现函数 frontBackSplit() 把链表居中切分成两个子链表 -- 一个前半部分，另一个后半部分。如果节点数为奇数，则多余的节点应该归类到前半部分中。例子如下，注意 front 和 back 是作为空链表被函数修改的，所以这个函数不需要返回值。

    var source = 1 -> 3 -> 7 -> 8 -> 11 -> 12 -> 14 -> null
    var front = new Node()
    var back = new Node()
    frontBackSplit(source, front, back)
    front === 1 -> 3 -> 7 -> 8 -> null
    back === 11 -> 12 -> 14 -> null

如果函数的任何一个参数为 null 或者原链表长度小于 2 ，应该抛出异常。

提示：一个简单的做法是计算链表的长度，然后除以 2 得出前半部分的长度，最后分割链表。另一个方法是利用双指针。一个 “慢” 指针每次遍历一个节点，同时一个 ”快“ 指针每次遍历两个节点。当快指针遍历到末尾时，慢指针正好遍历到链表的中段。

这个 kata 主要考验的是指针操作，所以解法用不上递归。

# 解法 1 -- 根据长度分割

代码如下：
```js
    function frontBackSplit(source, front, back) {
      if (!front || !back || !source || !source.next) throw new Error('invalid arguments')
    
      const array = []
      for (let node = source; node; node = node.next) array.push(node.data)
    
      const splitIdx = Math.round(array.length / 2)
      const frontData = array.slice(0, splitIdx)
      const backData = array.slice(splitIdx)
    
      appendData(front, frontData)
      appendData(back, backData)
    }
    
    function appendData(list, array) {
      let node = list
      for (const data of array) {
        if (node.data !== null) {
          node.next = new Node(data)
          node = node.next
        } else {
          node.data = data
        }
      }
    }
```
解法思路是把链表变成数组，这样方便计算长度，也方便用 slice 方法分割数组。最后用 appendData 把数组转回链表。因为涉及到多次遍历，这并不是一个高效的方案，而且还需要一个数组处理临时数据。

# 解法 2 -- 根据长度分割改进版

代码如下：
```js
    function frontBackSplitV2(source, front, back) {
      if (!front || !back || !source || !source.next) throw new Error('invalid arguments')
    
      let len = 0
      for (let node = source; node; node = node.next) len++
      const backIdx = Math.round(len / 2)
    
      for (let node = source, idx = 0; node; node = node.next, idx++) {
        append(idx < backIdx ? front : back, node.data)
      }
    }
    
    // Note that it uses the "tail" property to track the tail of the list.
    function append(list, data) {
      if (list.data === null) {
        list.data = data
        list.tail = list
      } else {
        list.tail.next = new Node(data)
        list.tail = list.tail.next
      }
    }
```
这个解法通过遍历链表来获取总长度并算出中间节点的索引，算出长度后再遍历一次链表，然后用 append 方法选择性地把节点数据加入 front 或 back 两个链表中去。这个解法不依赖中间数据（数组）。

append 方法有个值得注意的地方。一般情况下把数据插入链表的末尾的空间复杂度是 O(n) ，为了避免这种情况 append 方法为链表加了一个 tail 属性并让它指向尾节点，让空间复杂度变成 O(1) 。

# 解法 3 -- 双指针

代码如下：
```js
    function frontBackSplitV3(source, front, back) {
      if (!front || !back || !source || !source.next) throw new Error('invalid arguments')
    
      let slow = source
      let fast = source
    
      while (fast) {
        // use append to copy nodes to "front" list because we don't want to mutate the source list.
        append(front, slow.data)
        slow = slow.next
        fast = fast.next && fast.next.next
      }
    
      // "back" list just need to copy one node and point to the rest.
      back.data = slow.data
      back.next = slow.next
    }
```
思路在开篇已经有解释，当快指针遍历到链表末尾，慢指针正好走到链表中部。但如何修改 front 和 back 两个链表还是有点技巧的。

对于 front 链表，慢指针每次遍历的数据就是它需要的，所以每次遍历时把慢指针的数据 append 到 front 链表中就行（第 9 行）。

对于 back 链表，它所需的数据就是慢指针停下的位置到末尾。我们不用复制整个链表数据到 back ，只用复制第一个节点的 data 和 next 即可。这种 **复制头结点，共用剩余节点** 的技巧经常出现在一些 Immutable Data 的操作中，以省去不必要的复制。这个技巧其实也可以用到上一个解法里。

[0]: /a/1190000008243727
[1]: /t/%E7%AE%97%E6%B3%95/blogs
[2]: /t/%E9%93%BE%E8%A1%A8/blogs
[3]: /t/javascript/blogs
[4]: /u/darkbaby123
[5]: https://segmentfault.com/a/1190000007543189
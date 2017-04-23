# FreeCodeCamp 初级算法题 - 数组排序并找出元素索引

 时间 2017-03-24 11:35:26  S1ngS1ng

_原文_[http://singsing.io/blog/2017/03/23/fcc-basic-where-do-i-belong/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 我身在何处？

> 先给数组排序，然后找到指定的值在数组的位置，最后返回位置对应的索引。

> 举例：where([1,2,3,4], 1.5) 应该返回 1。因为1.5插入到数组[1,2,3,4]后变成[1,1.5,2,3,4]，而1.5对应的索引值就是1。

> 同理，where([20,3,5], 19) 应该返回 2。因为数组会先排序为 [3,5,20]，19插入到数组[3,5,20]后变成[3,5,19,20]，而19对应的索引值就是2。

> where([10, 20, 30, 40, 50], 35) 应该返回 3.
> where([10, 20, 30, 40, 50], 30) 应该返回 2.
> where([40, 60], 50) 应该返回 1.
> where([3, 10, 5], 3) 应该返回 0.
> where([5, 3, 20, 3], 5) 应该返回 2.
> where([2, 20, 10], 19) 应该返回 2.
> where([2, 5, 10], 15) 应该返回 3.


## 问题解释 

* 这个 function 接收两个参数，第一个参数为数组 arr ，即为需要查找的原数组。第二个参数为数字 num ，表示需要查询的数字。返回值为 num 在 arr 排序后的索引 (即返回值为数字)
* 比如接收的是 [1, 2, 3, 4] 与 1.5 ，那么输出就是 1 ，如果接收的是 [20, 3, 5] 与 19 ，那么输出应为 2

## 参考链接 

* [Array.sort()][5]
* [Array.indexOf()][6]

## 思路提示 

* 题目的说明已经把思路提示的很明显了。我们只要先把这个元素添加进数组，然后排序。最后通过 indexOf 找出索引就可以了
* 根据题目描述，这个思路应该是最容易想出来的。在其他解法中你还可以看到其他思路

## 参考答案 

### 基本答案 - 排序后通过 indexOf 查找 
```js
    function where(arr, num){
        arr.push(num);
        
        arr.sort(function(a, b){
            return a - b;
        });
    
        return arr.indexOf(num);
    }
```

#### 解释 

* 这个解法主要是 sort 方法的运用。一开始可能不是很好理解。只要记住一点就行， sort 方法根据回调函数的返回值来排序。在上面的写法中，如果返回值小于 0 (即 a - b < 0 )，那么就把 a 排到前面
* 因此，上面写法的意思就是，把数组按照从小到大的顺序来排序。可以这样去记忆，由于 a 与 b 就是数组中的相邻两个元素，既然返回值是 a - b ，那么就意味着 a < b ，所以是从小到大。如果返回值是 b - a ，也就意味着是 b < a ，也就是从大到小排序
* 从小到大排序之后，我们通过 indexOf 来找 num 在其中的位置就可以了
* 如果你不是刚接触编程，可以去了解一下排序的原理 (如果只是从 FCC 开始学编程，那这一步先暂时跳过吧) ，实现排序的算法有很多种，常见的快速排序、冒泡排序、桶排序，以及归并排序、插入排序、基数排序等等，有兴趣的话可以研究一下这些常用的，然后用 JavaScript 来实现每一个，相信你会有很大收获
* **如果你对源码感兴趣** ，比如你想知道 Chrome 浏览器的 sort 是如何实现的，请看 [这个链接][7] 。这部分是 V8 引擎源码，可以看到，当数组长度小于 22 时，用的是插入排序，否则就用快速排序 (确切地说，使用的是快速排序中的 in-place quicksort)

### 优化 - 循环 

#### 思路提示 

* 循环也是没问题的。思路在于，我们遍历数组，统计出其中比 num 小的元素数量
* 举个例子，如果数组中有 3 个数比 num 小，那么 num 得排在第 4 位，因此这时候 num 的 index 就为 3
* 所以，得出结论，如果有 n 个数比 num 小，那我们直接返回 n 就可以了
* 值得注意的是，如果元素相等需要怎么处理？可以想一下，如果传入的 num 在 arr 中已经存在，那我们就不用关心把 num 放在哪，因为排序后，不论放在哪里都是连续的两个数，我们只要找到第一个数的 index 就可以了
* 因此，同样适用于上面的结论：”如果有 n 个数比 num 小，那我们直接返回 n 就可以了”。只要统计的时候，不统计相等的，只统计比 num 小的就行
```js
    function where(arr, num){
        var count = 0;
    
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] < num) {
                count += 1;
            }
        }
    
        return count;
    }
    
```
#### 解释 

* 之所以说这样是优化，如果你听说过时间复杂度，应该就明白为什么了。插入排序的时间复杂度是 n 平方 ，快速排序期望是 n*log(n) ，最坏是 n 平方 。而用 for 循环，时间复杂度是 n
* 如果不明白这一点，也不影响做题。以上的两种方法都是可行而且可以接受的

### 一行搞定 - 使用 filter 

#### 思路提示 

* 首先，请先理解上一个答案中提到的思路。也就是，统计出数组中有多少个元素比 num 小。具体的分析请看上一个解法中提到的
* 那么，如何在这个情景下使用 filter 呢？可以这么考虑一下，既然我们想要知道究竟有多少个元素比 num 小，那我们只要根据这个条件过滤数组，然后用 .length 获取数组长度，是不是就能得出有多少个元素比 num 小了呢？
* 如果你还没想明白，请再读三遍上面这句话。或者，自己想一个实际的例子，带入到上面这句话验证一下

#### 参考链接 

* [Array.filter()][8]

#### ES5 
```js
    function where(arr, num){
        return arr.filter(function(e){
            return e < num;
        }).length;
    }
    
```
#### ES6 
```js
    function where(arr, num){
        return arr.filter(e=> e < num).length;
    }
```
[1]: http://singsing.io/blog/2017/03/23/fcc-basic-where-do-i-belong/?utm_source=tuicool&utm_medium=referral
[3]: https://www.freecodecamp.cn/challenges/where-do-i-belong
[4]: https://www.freecodecamp.com/challenges/where-do-i-belong
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/sort
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
[7]: https://github.com/v8/v8/blob/40aed9791fae1f168649371c87fe86447a81ff35/src/js/array.js#L709-L739
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
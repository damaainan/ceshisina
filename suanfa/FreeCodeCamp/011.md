# FreeCodeCamp 中级算法题 - 给定范围内的数字总和

 时间 2017-04-03 04:20:37  S1ngS1ng

_原文_[http://singsing.io/blog/2017/04/02/fcc-intermediate-sum-all-numbers-in-a-range/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：中级 (Intermediate Algorithm Scripting)

> 我们会传递给你一个包含两个数字的数组。返回这两个数字和它们之间所有数字的和。
> 
> 最小的数字并非总在最前面。
> 
> sumAll([1, 4]) 应该返回一个数字。
> sumAll([1, 4]) 应该返回 10。
> sumAll([4, 1]) 应该返回 10。
> sumAll([5, 10]) 应该返回 45。
> sumAll([10, 5]) 应该返回 45。

## 问题解释 

* 这个 function 接收一个数组参数 arr ，其中包含两个数字。返回值为这两个数字范围内所有数字的总和
* 比如接收的是 [1, 4] ，那么输出就是 10
* 需要注意的是，如果接收的是 [4, 1] ，那么输出也应为 10

## 基本解法 

## 思路提示 

* 这道题目的计算方式很简单，但需要判断传入数组的两个数字大小
* 你可能会想到使用 Array.sort() 。但其实，既然我们确定了只有两个元素，直接通过索引去访问就足够了。排序的话，反正也只有两种可能，所以完全不必要用
* 那么，在基本解法中，我们先不涉及太多的技巧，直接通过循环来解

## 参考链接 

* 没什么可以参考的，只要想清楚逻辑过程，会写 for 循环就足够了。当然，用 while 写也是没问题的

## 代码 
```js
    function sumAll(arr){
        // 用 left 代表较小的数，right 代表较大的数
        var left, right;
        var sum = 0;
    
        if (arr[0] < arr[1]) {
            left = arr[0];
            right = arr[1];
        } else {
            left = arr[1];
            right = arr[0];
        }
    
        for (var i = left; i <= right; i++) {
            sum += i;
        }
    
        return sum;
    }
    
```
## 解释 

* 没有太多要解释的，思路就是先判断大小，定下循环的边界，然后遍历求和

## 优化 - 使用数学方法 

## 思路提示 

* 你应该还记得，JavaScript 中有个 Math.max() 方法，相对应的，还有个 Math.min()
* 所以我们可以简单地通过这两个方法来获取较大数和较小数
* 至于数学方法，其实就是等差数列求和公式。来一起念一遍：”首项加上末项的和乘以项数除以 2”

## 参考链接 

* [Math.max()][5]
* [Math.min()][6]
* 初中数学课本

## 代码 
```js
    function sumAll(arr){
        var left = Math.min.apply(null, arr);
        var right = Math.max.apply(null, arr);
    
        return (left + right) * (right - left + 1) / 2;
    }
```

## 优化 - 使用数组方法 

## 思路提示 

* 首先我们先想一点，如果只给我们两个数字，那除了上面的循环解法，恐怕不会有其他更好的解法了。因为我们只有两个端点，而没有一个连续的序列
* 你可能还记得，数组有个 reduce 方法，可以用于求和。我们在基础算法题中用到过
* 那么现在我们只要解决，如何通过传入的参数生成数组就可以了。可以通过循环，然后把元素 push 进去。也可以用 ES6 的 Array.from 方法，写起来会简洁不少。因为这个方法的回调函数就是一个 “map function”

## 参考链接 

* [Array.reduce()][7]
* [Array.from()][8]
* [Math.abs()][9]

## 代码 

### ES5 
```js
    function sumAll(arr){
        var left = Math.min.apply(null, arr);
        var right = Math.max.apply(null, arr);
        var numArr = [];
    
        for (var i = left; i <= right; i++) {
            numArr.push(i);
        }
    
        return numArr.reduce(function(prev, next){
            return prev + next;
        })
    }
    
```
## 解释 

* 嗯，看起来好像没省多少事儿。在这道题里可能看不出太大区别，但在一些情况下可能会简单一些
* 由于 ES5 生成范围内的数组没有什么简便写法，我们这里就只能用 for 循环了。当然，你也可以尝试一下用 new Array() 来生成。但请注意，这个构造器不会真正的生成元素，所以你不能使用 map 方法
* 我能想到的解决方案只有先 join 然后再 split 。但个人觉得这样写还不如直接用循环完成。如果你有更好的方法，请留言评论

### ES6 
```js
    function sumAll(arr){
        var numArr = Array.from({length: Math.abs(arr[0] - arr[1]) + 1}, (_, i) => i + Math.min.apply(null, arr));
    
        return numArr.reduce((prev, next) => prev + next);
    }
```

[1]: http://singsing.io/blog/2017/04/02/fcc-intermediate-sum-all-numbers-in-a-range/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/sum-all-numbers-in-a-range
[4]: https://www.freecodecamp.com/challenges/sum-all-numbers-in-a-range
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Math/max
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Math/min
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/Reduce
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/from
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Math/abs
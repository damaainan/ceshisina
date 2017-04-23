# FreeCodeCamp 中级算法题 - 多个数组元素排序

 时间 2017-04-22 10:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-sorted-union/][1]


* [中文链接][4]
* [英文链接][5]
* 级别：中级 (Intermediate Algorithm Scripting)

> 写一个 function，传入两个或两个以上的数组，返回一个以给定的原始数组排序的不包含重复值的新数组。

> 换句话说，所有数组中的所有值都应该以原始顺序被包含在内，但是在最终的数组中不包含重复值。

非重复的数字应该以它们原始的顺序排序，但最终的数组不应该以数字顺序排序。

> unite([1, 3, 2], [5, 2, 1, 4], [2, 1]) 应该返回 [1, 3, 2, 5, 4]。
> unite([1, 3, 2], [1, [5]], [2, [4]]) 应该返回 [1, 3, 2, [5], [4]]。
> unite([1, 2, 3], [5, 2, 1]) 应该返回 [1, 2, 3, 5]。
> unite([1, 2, 3], [5, 2, 1, 4], [2, 1], [6, 7, 8]) 应该返回 [1, 2, 3, 5, 4, 6, 7, 8]。


## 问题解释 

* 这个 function 接收两个或两个以上的数组。返回值为过滤及合并之后的一维数组
* 如果传入的是 [1, 2], [2, 3], [4, 5] ，那么返回值就是 [1, 2, 3, 4, 5]
* 需要注意的是，这里不涉及到 “Flatten” (数组扁平化)。比如，传入的是 [1, 2], [[2], 3] ，那么返回值应该是 [1, 2, [2], 3] 。这其实是一个非常深的坑，请看后面详解
* 另外需要注意的是，参数的数量是不确定的

## 基本解法 - 不够严谨的循环解法 

## 思路提示 

* 基本解法中，老规矩，我们先来用循环实现
* 对于不定项参数，最简单的处理方式就是调用 arguments 。注意，这是 JavaScript 中的关键字
* 简单来说， arguments 是一个 “Array-like Object” (也就是很像数组的对象)。简单来比较一下两者的区别： 
  * 数组： ["a", "b", "c"]
  * 类数组对象： {"0": "a", "1": "b", "2": "c", length: 3}
* 由于 JavaScript 的对象要求所有的 key 均为字符串，因此类数组对象必须写成上面这样。它自带了一个 length 属性，你会发现，其实调用的时候也是通过 obj[0] 来获取第一个元素，跟数组好像是一样的。但由于它本身不是数组，因此 **不能使用数组方法**
* 经常遇到的两个类数组对象，一个是 arguments ，另一个是 NodeList
* 不过没关系，我们可以把它转化为数组，方法有很多。以 arguments 为例： 
  * Array.prototype.slice.call(arguments)
  * [].slice.call(arguments)
  * Array.from(arguments)
  * [...arguments]
* 以上四种写法都可以，前两个为 ES5 写法，后两个为 ES6 写法
* 另外，为什么 JavaScript 中会有 arguments ？其实 arguments 代表的是函数接收的实际参数。因此，就算你在函数定义中只定义了一个参数，你依然可以通过 arguments 获取所有传入的参数

## 参考链接 

* [arguments][6]
* [我在 SegmentFault 上关于 Array-like Object 的一个回答][7]

## 代码 
```js
function unite(arr1, arr2, arr3){
    var argArr = [].slice.call(arguments);
    var result = [];

    // 遍历刚生成的 argArr
    for (var i = 0; i < argArr.length; i++) {
        // 遍历 argArr 中每一个数组的每一个元素
        for (var j = 0; j < argArr[i].length; j++) {
            if (result.indexOf(argArr[i][j]) === -1) {
                result.push(argArr[i][j]);
            }
        }
    }

    return result;
}
```

## 优化 - 使用 filter，reduce (同样不够严谨) 

* 想一下就知道，这道题目要求的其实就是过滤及合并。对于过滤，我们可以使用 filter 方法，对于合并，我们可以使用 concat 方法
* 如果想把一次操作的结果应用到下一次操作，那就是 reduce ，你应该还记得
* 由于这里并不是返回等长度的数组，因此我们没法使用 map

## 参考链接 

* [Array.reduce()][8]
* [Array.filter()][9]
* [Array.concat()][10]

## 代码 

### ES6 
```js
function unite(arr1, arr2, arr3){
    return [].slice.call(arguments).reduce((prev, next) => {
        return prev.concat(next.filter(e=> prev.indexOf(e) === -1));
    }, []);
}
```

## 解释 

* 如果你不理解上面这个的执行过程，请像之前文章中那样，列出来一个表格去分析一下执行过程
* 提示一句，第一次调用的时候， prev 是 [] ， next 是 arguments 中的第一个元素。第二次调用的时候， prev 是上一步的返回值， next 是 arguments 中的第二个元素。以此类推

## 优化 - 足够严谨的解法 

## 思路提示 

* 看起来这两个解法问题不大，也能通过测试。开头说了，这道题有一个深坑。原因在于，对于数字，我们可以通过 indexOf 来判断是否存在于数组中。但对于一个 Array ，就不能用这个来判断。比如， [[1], [2]].indexOf([1]) 是会返回 -1 的
* 类似的情况还出现于 NaN 。如果一个数组中存在 NaN ，那我们同样不能用 indexOf 来判断位置，因为 NaN === NaN 或 NaN == NaN 都会返回 false
* 这样一来，事情就变得复杂了很多。但简单归纳一下，办法是肯定有的。对于传入的参数数组，其中的元素有两种可能，数字或数组。因此： 
  * 当元素为数字时，我们就通过 indexOf 来判断。这与上面的思路一样
  * 当元素为数组时，我们需要检查这个数组是否已经存在于结果中
* 思路就是，如果遍历过程中首次遇到一个数组，那我们可以直接保留它。如果它已经存在在结果中，那么我们就不保留
* 为了实现这个，我的做法是先建立一个空数组 nested ，用于存储遍历过程中遇到的数组元素。在遍历的时候，一旦遇到数组，就和这个 nested 比较一下。如果这个数组不在 nested 中，那我们就在结果中保留， **并把这个数组 push 到 nested** 。如果在 nested 中，那我们就不保留，继续遍历
* 于是，现在就只剩下一件事，那就是如何判断一个数组是否在另一个数组中。我试着不封装函数来完成，但我发现那样很困难。所以我觉得这里封装一个判断函数还是有必要的。我们可以偷偷懒，不写遍历，而用 JSON.stringify 方法。首先，从题目看来，所有的元素均为数字，就算是嵌套的数组，数组内元素依然为数字。另外，对于 [1, 2] 和 [2, 1] ，在这道题目中，我们应该认为它是不相等的。这点通过 JSON.stringify 也可以完美处理
* 然后，我们还需要知道一个方法，那就是 Array.some() 。如果你没听说过，请点开底下的链接看一看

## 参考链接 

* [Array.some()][11]
* [JSON.stringify()][12]

## 代码 

### ES6 
```js
    functionuniteUnique(arr){
        // 储存出现的数组元素
        let nested = [];
    
        functionisDuplicate(element, arr){
            // 如果数组重复，那只可能发生在相同长度的情况下，因此我们只要比较相同长度的就够了
            let temp = arr.filter(e=> e.length === element.length);
    
            if (temp.length === 0) {
                // 过滤后长度为 0，那么肯定就是不存在重复的。返回 false
                return false;
            }
    
            // 用 JSON.stringify 比较转换后的字符串。如果相等，那就表示两个数组相等
            return temp.some(tempArr=> JSON.stringify(tempArr) === JSON.stringify(element));
        }
    
        return [].slice.call(arguments).reduce((prev, next) => {
            return prev.concat(next.filter(e=> {
                // 如果当前元素是数组
                if (typeof e === 'object') {
                    // 先判断一次，如果不重复，就 push 然后返回 true
                    // 由于这部分是 filter 方法的返回值，因此返回 true 就是保留，返回 false 就是不保留
                    if (!isDuplicate(e, nested)) {
                        nested.push(e);
                        return true;
                    }
                    return false;
                }
                // 如果当前元素是数字
                return prev.indexOf(e) === -1;
            }))
        }, []);
    }
```

## 解释 

* 要解释的基本都在代码中。如果你觉得这样做是没有必要的，那就忽略这段代码吧
* 我已经给 FCC 官方提交了这个 issue，解决方案可能是在说明中指出可以忽略这种情况，或者加入这个情况的检测。如果 FCC 官方更新，我们同样会更新中文官网


[1]: http://singsing.io/blog/fcc/intermediate-sorted-union/?utm_source=tuicool&utm_medium=referral
[4]: https://www.freecodecamp.cn/challenges/sorted-union
[5]: https://www.freecodecamp.com/challenges/sorted-union
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Functions/arguments
[7]: https://segmentfault.com/q/1010000008573297/a-1020000008579249
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/reduce
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/concat
[11]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/some
[12]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify
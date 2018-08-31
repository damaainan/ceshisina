# FreeCodeCamp 初级算法题 - 找出多个数组中的最大数

 时间 2017-03-20 23:54:50  S1ngS1ng

_原文_[http://singsing.io/blog/2017/03/20/return-largest-numbers-in-arrays/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 右边大数组中包含了4个小数组，分别找到每个小数组中的最大值，然后把它们串联起来，形成一个新数组。

> 提示：你可以用for循环来迭代数组，并通过arr[i]的方式来访问数组的每个元素。

> largestOfFour([[4, 5, 1, 3], [13, 27, 18, 26], [32, 35, 37, 39], [1000, 1001, 857, 1]]) 应该返回一个数组
> largestOfFour([[13, 27, 18, 26], [4, 5, 1, 3], [32, 35, 37, 39], [1000, 1001, 857, 1]]) 应该返回 [27,5,39,1001].
> largestOfFour([[4, 9, 1, 3], [13, 35, 18, 26], [32, 35, 97, 39], [1000000, 1001, 857, 1]]) 应该返回 [9, 35, 97, 1000000].

## 问题解释 

* 这个 function 接收多个数组为参数，返回每个数组的最大值组成的新数组
* 这道题不会涉及到特殊情况的处理，因此只需要遍历，找出最大值就可以了

## 参考链接 

* 对于初级解法，没什么太多要参考的。只需要想想，如何从数组中提取出最大值就可以了
* 题目的整体思路与之前的 [找出最长单词(Find the Longest word in a String)][5] 十分相似。区别在于，这道题是在操作二维数组，那道题是在操作一维数组

## 思路提示 

* 首先设置一个空数组，用于存储结果
* 遍历一遍传入的参数，参数是一个二维数组。因此，在遍历的过程中，我们操作的对象就是其中的每一个数组
* 然后，提取出当前数组的最大值，并把它添加到第一步初始化的数组中。最后返回这个数组即可
* 获取数组的最大值也很容易，首先在外面设置一个变量，初始化为 0。然后，遍历一遍数组，发现更大的就赋值给它，没发现更大的就什么也不做。遍历结束后，这个变量就是数组最大值
* 基本思路中，我们先不涉及太多的技巧，就用最直观、最简单的方式，先把它写出来

## 参考答案 

### 基本答案 
```js
    function largestOfFour(arr){
        // 空数组，用于存储结果
        var result = [];
        
        // 遍历传入的数组
        for (var i = 0; i < arr.length; i++) {
            // 初始化最大值，可以想一下为什么要写在这里
            var largest = 0;
            
            // arr 是一个二维数组，arr[i] 才是我们要找最大值的每一个数组
            for (var j = 0; j < arr[i].length; j++) {
                if (arr[i][j] > largest) {
                    // 找到更大的值，更新 largest
                    largest = arr[i][j];
                }
            }
    
            // 把最大值存储到 `result`
            result.push(largest);
        }
    
        return result;
    }
    
```
#### 解释 

* 首先要想明白一点，第一层的 for 循环我们是在遍历传入的参数。第二层的才是每一个数组。比如 arr[0] 就是传入的二维数组中的第一个数组，那么 arr[0][0] 就是传入的二维数组中，第一个数组的第一个元素
* 明白了上一条，你应该就能理解，为什么我们要在第一个 for 循环里面，第二个 for 循环的外面声明 largest 。简单来说，我们需要找的是 arr[0] , arr[1] , arr[2] … 的最大值。举个例子来说，我们希望 largest 在从 arr[0] 换成 arr[1] 的时候重新赋值
* 在第二层 for 循环结束之后，我们就得到了当前数组的最大值，因此需要把它 push 到 result 中

### 优化 

* 数组方法中，有个叫 .reduce() 的。之前提到过，是在遍历中，把上一次计算结果用于下一次计算
* 因此，对于上面两个 for 循环，我们都可以通过 reduce 来改写。寻找最大值的改写这里不再赘述，请参考 [找出最长单词(Find the Longest word in a String)][5] 那篇”优化”部分，对 reduce 的详细解释
* 对于生成结果数组，一样是可以通过 reduce 来实现的。初始值设置为一个空数组 [] ，然后把每一个最大值 push 进去，记得一定要返回这个数组，用于下次计算。 .push() 的返回值不是数组，而是操作之后的数组长度 

#### 参考链接
* [Array.reduce()][6]
* [找出最长单词(Find the Longest word in a String)][5]
```js 
    function largestOfFour(arr){
        return arr.reduce(function(prevArr, nextArr){
            return prevArr.concat(nextArr.reduce(function(prevNum, nextNum){
               return Math.max(prevNum, nextNum); 
            }, 0));
        }, []); 
    }
```
#### 解释 

* 一定要注意这里的层级关系。我们可以简单理解为，一层 reduce 就相当于一层 for 循环。因此，第一层 reduce 是遍历传入的参数，也就是二维数组，第二层才是遍历数组中的所有元素，也就是要找出最大值的那些数字
* 理解了上面这一点，也就应该能理解为什么在第一层要传入空数组作为默认值，而在第二层要传入 0 作为默认值
* 简单解释一下每一层发生了什么。里面的那一层，对于一个数组，比如 [1, 2, 3, 4] 来说，设定一个初始值为 0，并作为第一次遍历的 prevNum 传入，这时候 nextNum 为第一个元素，就是 1。我们通过 Math.max() ，返回较大的 1。这时候 1 作为下一次执行的 prevNum 传入，同时， nextNum 为第二个元素，就是 2
* 外面那一层，第一次执行，用 [] 作为 prevArr ，这时候 nextArr 为参数二维数组中的第一个数组。我们计算出 nextArr 的最大值，然后把这个结果 concat 到 prevArr 。之所以用 concat ，是因为 concat 返回操作后的数组，而 push 并没有这个效果
* 如果你还没想明白，要么 console.log 一下每一层执行过程中的 prev 和 next ，要么对照着 [找出最长单词(Find the Longest word in a String)][5] 这一篇，自己画出来一个表格，应该就能理解了

### 再优化 - 更简单的思路 

* 我们可以注意到一点，传入的参数是数组，返回的结果也是数组。进一步思考，传入的参数长度应该与结果的长度是一致的，因为参数长度就表示我们需要找出多少个数组中的最大值
* 如果能想明白上面这一点，那么用 .map() 就顺理成章了。之前提到过， .map() 适用于”返回一个相同长度，并经过一些处理的数组”
* 至于找出数组最大值，最简便的写法恐怕是 Math.max.apply(null, arr) 。这一点在 [找出最长单词(Find the Longest word in a String)][5] 那一篇也提到了 

#### 参考链接
* [Array.map()][7]
* [Function.apply()][8]
```js
    function largestOfFour(arr){
        return arr.map(function(e){
            return Math.max.apply(null, e);
        });
    }
    
```
#### 解释 

* 这是我能想到的，看起来最简洁的方式
* 只需要想清楚，调用 arr.map() ，这时候，回调函数里面的 e 其实是数组，也就是对应着 arr[0] ， arr[1] …


[1]: http://singsing.io/blog/2017/03/20/return-largest-numbers-in-arrays/?utm_source=tuicool&utm_medium=referral
[3]: https://www.freecodecamp.cn/challenges/return-largest-numbers-in-arrays
[4]: https://www.freecodecamp.com/challenges/return-largest-numbers-in-arrays
[5]: http://singsing.io/blog/2017/03/18/fcc-basic-find-the-longest-word-in-a-string/
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/Reduce
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Function/apply
# FreeCodeCamp 初级算法题 - 过滤数组假值

 时间 2017-03-23 11:31:37  S1ngS1ng

_原文_[http://singsing.io/blog/2017/03/22/fcc-basic-falsy-bouncer/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 真假美猴王！

> 删除数组中的所有假值。

> 在JavaScript中，假值有false、null、0、""、undefined 和 NaN。

> bouncer([7, "ate", "", false, 9]) 应该返回 [7, "ate", 9].
> bouncer(["a", "b", "c"]) 应该返回 ["a", "b", "c"].
> bouncer([false, null, 0, NaN, undefined, ""]) 应该返回 [].
> bouncer([1, null, NaN, 2, undefined]) 应该返回 [1, 2].

## 问题解释 

* 这个 function 一个数组参数 arr ，即为需要过滤的原数组。返回值为过滤假值后的数组
* 比如接收的是 ["a", 7, false] ，那么输出就是 ["a", 7] 。如果接收的是 ["a", "b", 7] ，那么输出应该还是 ["a", "b", 7]
* 大体上这道题有两个思路。如果你知道数组的 filter 方法，那么可以直接用这个方法去写。如果你不知道，那么用循环写也是没问题的
* 在 JavaScript 中，只有以下这几个会被认为是假值 (falsy)： false , null , 0, "" , undefined , NaN 。我们可以直接通过 Boolean() 构造器来得出元素究竟是不是假值

## 参考链接 

* 对于基本解法，我们先不用数组内建方法，只通过最基本的循环来实现，因此，你只需要知道 for 循环和 push 就够了
* [Array.push()][5]
* [Boolean()][6]

## 参考答案 

### 基本答案 - 循环并生成结果数组 
```js
    function bouncer(arr){
        // 用于存储结果
        var result = [];
    
        for (var i = 0; i < arr.length; i++) {
            if (Boolean(arr[i])) {
                // 如果 Boolean 构造器返回 true，就保存到 result 中
                result.push(arr[i]);
            }
        }
    
        return result;
    }
    
```
#### 解释 

* 这样写，应该思路很明确。遍历传入的 arr ，一个一个判断就好
* 其实 Boolean(arr[i]) 也可以写成 !!arr[i] 。我们都知道， ! 是逻辑非，这里涉及到隐式类型转换，输出一定是一个 Boolean ，但是只用 ! 操作一次，是得到相反的结果
* 因此，我们需要 !! 来操作两次，这样，如果 arr[i] 是 falsy 那就会返回 false 。否则返回 true

### 优化 - 使用数组的 filter 方法 

#### 参考链接 

* [Array.filter()][7]
```js
    function bouncer(arr){
        return arr.filter(function(e){
            return !!e;
        })
    }
    
```
#### 解释 

* 只要你熟悉 filter 方法的参数，就能理解这个写法了。 e 是一个形式参数，代表每一个元素
* 回调函数返回 true 或者 false ，如果是 true 就保留，否则就删去

### 高级解法 - 同样是 filter#### 参考链接 

* [Array.filter()][7]
```js
    function bouncer(arr){
        return arr.filter(Boolean);
    }

```


[1]: http://singsing.io/blog/2017/03/22/fcc-basic-falsy-bouncer/?utm_source=tuicool&utm_medium=referral
[3]: https://www.freecodecamp.cn/challenges/falsy-bouncer
[4]: https://www.freecodecamp.com/challenges/falsy-bouncer
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/push
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Boolean
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
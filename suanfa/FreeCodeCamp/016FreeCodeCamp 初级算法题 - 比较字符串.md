# FreeCodeCamp 初级算法题 - 比较字符串

 时间 2017-03-23 10:31:37  S1ngS1ng

_原文_[http://singsing.io/blog/2017/03/22/fcc-basic-mutations/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)


> 蛤蟆可以吃队友，也可以吃对手。

> 如果数组第一个字符串元素包含了第二个字符串元素的所有字符，函数返回true。

> 举例，["hello", "Hello"]应该返回true，因为在忽略大小写的情况下，第二个字符串的所有字符都可以在第一个字符串找到。

> ["hello", "hey"]应该返回false，因为字符串"hello"并不包含字符"y"。

> ["Alien", "line"]应该返回true，因为"line"中所有字符都可以在"Alien"找到。


> mutation(["hello", "hey"]) 应该返回 false.
> mutation(["hello", "Hello"]) 应该返回 true.
> mutation(["zyxwvutsrqponmlkjihgfedcba", "qrstu"]) 应该返回 true.
> mutation(["Mary", "Army"]) 应该返回 true.
> mutation(["Mary", "Aarmy"]) 应该返回 true.
> mutation(["Alien", "line"]) 应该返回 true.
> mutation(["floor", "for"]) 应该返回 true.
> mutation(["hello", "neo"]) 应该返回 false.

## 问题解释 

* 这个 function 接收一个数组为参数。数组中有两个元素，均为字符串。需要判断第一个字符串是否包含第二个字符串的 **所有字符** 。返回值为 true 或 false
* 比如接收的是 ["hello", "Hello"] ，那么返回值为 true 。如果接收的是 ["hello", "hey"] ，那么返回值为 false
* 从代码角度来说，就是检测第二个字符串的所有字符是否都在第一个字符串中

## 参考链接 

* [String.indexOf()][5]

## 思路提示 

* 篇幅有限，这里就不给出分割成数组的方法了。大家可以试着写一下，其实和使用字符串的 indexOf 思路完全一致，只是操作的东西不同而已
* 整体思路就是，首先我们把两个字符串都转化为小写，然后遍历第二个字符串。只要有字符不在第一个中，我们就返回 false ，如果所有字符都在，我们就返回 true
* 这里就是一个很简单的逻辑短路的写法。记得要在循环结束之后才能得出 true 的结论。可以自己先写一下试试

## 参考答案 

### 基本答案 - 遍历字符串并对比 
```js
    function mutation(arr){
        var source = arr[0].toLowerCase();
        var target = arr[1].toLowerCase();
    
        for (var i = 0; i < target.length; i++) {
            if (source.indexOf(target[i]) === -1) {
                return false;
            }
        }
    
        return true;
    }
    
```
#### 解释 

* 首先把两个字符串提取出来，转成小写，第一个命名为 source ，第二个命名为 target
* 遍历 target ，判断是否 target 中的所有字符是否都在 source 中找到 index 。 .indexOf() 方法会返回字符的位置，如果不存在，会返回 -1
* 因此，只要找到了 index 为 -1 的，就可以得出结论：第一个字符串并不包含第二个字符串中的所有字符。因此，直接 return false 就好了
* 如果循环结束， if 判断没有成立，那就证明第一个字符串包含第二个字符串中的所有字符。因此 return true

### 优化 

* 我们可以使用数组的 .filter() 方法。先 .split() 一下第二个字符串，过滤的原则是，只要这个元素在第一个字符串中存在，我们就不保留它
* 因此，这个数组，过滤之后的结果就是 “在第二个字符串中存在，但在第一个字符串中不存在的字符”
* 我们只需要判断过滤后的数组长度就行。如果长度为 0，表示第二个字符串所有字符都被过滤掉了，因此返回 true 。反之，返回 false#### 参考链接
* [String.split()][5]
* [Array.filter()][6]
```js
    function mutation(arr){
        var sourceStr = arr[0].toLowerCase();
        var targetArr = arr[1].toLowerCase().split("");
    
        var filteredArr = targetArr.filter(function(char){
            return sourceStr.indexOf(char) === -1;
        })
    
        return filteredArr.length === 0;
    }
    
```
#### 解释 

* 应该不需要更多的解释了，只要你熟悉 filter 方法的语法，应该不难理解

### 一行解决 

* 思路相同，少了变量的赋值，直接返回结果 
```js
    function mutation(arr){
        return !arr[1].toLowerCase().split("").filter(function(char){
            return arr[0].toLowerCase().indexOf(char) === -1;
        }).length;
    }

```


[1]: http://singsing.io/blog/2017/03/22/fcc-basic-mutations/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/mutations
[4]: https://www.freecodecamp.com/challenges/mutations
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
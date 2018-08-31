# FreeCodeCamp 初级算法题 - 重复输出字符串

 时间 2017-03-21 01:54:50  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/2017/03/20/repeat-a-string-repeat-a-string/][1]

 主题 [算法][2]

* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 重要的事情说3遍！  
重复一个指定的字符串 num次，如果num是一个负数则返回一个空字符串。

> repeat("*", 3) 应该返回 "***".
> repeat("abc", 3) 应该返回 "abcabcabc".
> repeat("abc", 4) 应该返回 "abcabcabcabc".
> repeat("abc", 1) 应该返回 "abc".
> repeat("*", 8) 应该返回 " * * * * * **".
> repeat("abc", -2) 应该返回 "".

## 问题解释 

* 这个 function 接收两个参数。第一个参数为字符串 str ，即为要重复输出的字符串。第二个参数 num 为一个数字，表示要重复输出的次数。返回值为字符串
* 比如接收参数为 "*" 和 3 ，那么应该返回 *** 。如果接受的第二个参数为负数，则返回空字符 ""

## 参考链接 

* 没什么可以参考的，会写循环就行

## 思路提示 

* 重复输出，最直观的方式就是写一个循环。当然， for 或者 while 都可以。以下会给出这两种写法
* 当然，首先要在外面建立一个空字符，用于存储结果。在遍历过程中，只需要把字符串加到这个变量就行了
* 如果采用其他方法，请注意判断传入的 num 是否为负数。你会在文章后面看到具体说明

## 参考答案 

### 基本答案 - for 循环 
```js
    function repeat(str, num){
        var result = "";
    
        for (var i = 0; i < num; i++) {
            result += str;
        }
    
        return result;
    }
    
```
### 基本答案 - while 循环 
```js
    function repeat(str, num){
        var result = "";
    
        while (num > 0) {
            result += str;
            num--;
        }
    
        return result;
    }
    
```
#### 解释 

* 有些朋友可能会问，为什么这里不处理特殊情况，就是判断 num 是否大于 0 呢？其实，如果 num 小于等于 0，那就不会通过第一个解法中 i < num 或 第二个解法中 num > 0 的判断，也就不会执行循环体的代码，直接 return result 。既然我们已经把初始值设置为了空字符串 “”，因此这时得到的就是正确的结果
* 多说一句关于 while 和 do while 。两者的区别在于， while 是先判断再执行， do while 是先执行再判断
* 对于 while ，我们可以直接用 num 来判断，执行的时候 num-- 就可以了。不需要再定义额外的参数

### 内建方法 - repeat 

在 ES6 中，有一个字符串的内建方法 String.repeat() 。只是，一定要先判断 num 是否大于 0，否则会报错 

#### 参考链接 

* [String.repeat()][5]
* [Ternary Operator][6]
```js
    function repeat(str, num){
        return num > 0 ? str.repeat(num) : "";
    }
    
```
#### 解释 

* 这里用了三元运算符。其实也可以写成 if else 的形式
* ES6 需要浏览器支持，只要你用的是最新版的 Chrome，Safari 或者 Firefox，就肯定没问题

### 巧妙的解法 - join 

还记得数组有一个 .join() 方法吗？它也可以返回字符串 

#### 参考链接 

* [Array.join()][7]
* [Array()][8]
```js
    function repeat(str, num){
        if (num > 0) {
            return Array(num + 1).join(str);
        }
        return "";
    }
    
```
#### 解释 

* 其实最后的 return "" 就相当于是 else 的部分。因为如果通过了判断，会直接返回 if 里面的内容，形成逻辑短路，也就不会执行 return "" 这一句了
* 原理也不复杂。 Array() 是 JavaScript 中的数组构造器，传入一个数字 num + 1 ，就可以生成长度为 num + 1 的空数组，数组中的元素均为 undefined
* 然后我们通过 .join() 方法，把这个数组变成字符串。 undefined 会自动转换成空字符串。对于每个元素，用 join() 的参数把它们连接起来。这就是代码的意思
* 举个例子，执行 ["a", "b", "c"].join("x") ，我们就可以得到字符串 "axbxc"
* 至于 num + 1 ，是因为我们需要输出 num 次，如果生成的数组长度为 num ，那么 join 之后会少一次，因为 join 相当于把参数填入数组元素的”空缺”中

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/2017/03/20/repeat-a-string-repeat-a-string/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11000083
[3]: https://www.freecodecamp.cn/challenges/repeat-a-string-repeat-a-string
[4]: https://www.freecodecamp.com/challenges/repeat-a-string-repeat-a-string
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/repeat
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Conditional_Operator
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/join
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array
# FreeCodeCamp 初级算法题 - 翻转字符串

 时间 2017-03-18 15:12:02  [S1ngS1ng][0]  [相似文章][1] (_1_)

_原文_[http://singsing.io/blog/2017/03/17/fcc-basic-reverse-a-string/][2]

 主题 [算法][3]

* [中文链接][4]
* [英文链接][5]
* 级别：初级 (Basic Algorithm Scripting)


> 翻转字符串

> 先把字符串转化成数组，再借助数组的reverse方法翻转数组顺序，最后把数组转化成字符串。

> 你的结果必须得是一个字符串

> reverseString("hello") 应该返回一个字符串
> reverseString("hello") 应该返回 "olleh".
> reverseString("Howdy") 应该返回 "ydwoH".
> reverseString("Greetings from Earth") 应该返回 "htraE morf sgniteerG".

## 问题解释 

* 这个 function 接收一个字符串参数，返回翻转后的字符串
* 比如接收的是 “hello”，那么输出就是 “olleh”

## 参考链接 

* [String.split()][6]
* [Array.reverse()][7]
* [Array.join()][8]

## 思路提示 

1. 先把字符串分割成为数组
1. 翻转数组
1. 把翻转后的数组合并为字符串

## 参考答案 

### 基本答案 
```js
    function reverseString(str){
        var strArr = str.split('');
        var reversedArr = strArr.reverse();
        return reversedArr.join('');
    }
    
```
#### 解释 

* 第一步就是把传入的 str 分割，并赋值给 strArr
* 第二步是把数组翻转，并赋值给 reversedArr
* 第三步是返回合并之后的字符
* 需要注意的是，以上的 .split 和 .join 都不会改变原来的字符串或数组，但 reverse 会改变原来的数组

### 优化 
```js
    function reverseString(str){
        return str.split('').reverse().join('');
    }
    
```
#### 解释 

* .split 返回分割后的数组，因此可以直接调用 .reverse
* .reverse() 方法返回的是翻转后的数组，因此可以直接调用 .join
* .join 之后就是我们想要的字符串，直接返回即可
* 这里用到了 Method Chaining，也就是方法的链式调用。只要你熟悉方法的返回值，就可以这么做，好处在于可以不用创建这么多变量

### 中级解法 

* 直接利用字符串方法，而不需要转换成数组 
```js
    function reverseString(str){
        var result = "";
        for (var i = str.length - 1; i >= 0; i--) {
            result += str[i];
        }
        return newString;
    }
```
#### 解释 

* 首先我们先创建一个变量，叫 result ，用于保存输出结果
* 然后，从右边开始遍历字符串。值得注意的是，就像数组一样，字符串一样可以通过所以来获取某一个字符。比如， str[0] 就是获取第一个字符。再比如， str[-1] 就是获取最后一个字符
* 因为是从右边开始遍历，那我们把每次遍历到的字符直接加到 result 就可以了
* 需要注意的是边界条件的确定，因为字符串的索引同样是从 0 开始的，因此遍历的初始值要设置为 str.length - 1 ，结束值为 0

### 高级解法 

* 通过字符串方法以及递归来翻转 
```js
    function reverseString(str){
        // 设置递归终点(弹出条件)
        if (!str) {
            return "";
        }
        else {
            // 递归调用
            return reverseString(str.substr(1)) + str.charAt(0);
        }
    }
```
#### 解释 

* 这种方法，一开始不能理解没关系。等做到高级算法题，再回来看看应该就可以理解了
* 递归涉及到两个因素，递归调用以及弹出过程。 reverseString(str.substr(1)) 就是递归调用， + str.charAt(0) 就是弹出过程
* 代码在执行到 reverseString(str.substr(1)) 的时候，会重新调用 reverseString ，并传入 str.substr(1) 作为参数。后面的 + str.charAt(0) 暂时不会执行
* 直到遇见传入的字符串为 "" ，因为有了 "" 返回值，就不会再去调用 reverseString 了。这时候，才会一步一步地执行 + str.charAt(0) ，也就是弹出过程

举个例子： 

    var str = "abc";
    
    reverseString(str)
    

* 执行过程如下： 
  * 首先执行 reverseString(“abc”)，这时候传入的 str 不为空，所以执行 else 部分。读到了 reverseString(str.substr(1)) ，这时候就是递归调用，执行这段代码，其中 str.substr(1) 为 "bc"
    * reverseString("bc") ，这时候传入的 str 不为空，所以执行 reverseString(str.substr(1)) ，其中 str.substr(1) 为 "c"
      * reverseString("c") ，这时候传入的 str 依旧不为空，所以执行 reverseString(str.substr(1)) ，其中 str.substr(1) 为 ""
        * reverseString("") ，终于，传入的 str 为空，这时候返回 ""
      * 回到 reverseString("c") 这一步，刚才的返回值是 “”，此时的 str.charAt(0) 为 "c" ，那么这一步的返回值是 "c"
    * 回到 reverseString("bc") ，刚才的返回值是 "c" ，此时的 str.charAt(0) 为 "b" ，那么这一步的返回值是 "cb"
  * 回到 reverseString("abc") ，刚才的返回值是 "cb" ，此时的 str.charAt(0) 为 "a" ，那么这一步的返回值是 "cba"

至此，我们得到了最终结果，”cba”

[0]: /sites/q22mEzq
[1]: /articles/dup?id=qYbeue3
[2]: http://singsing.io/blog/2017/03/17/fcc-basic-reverse-a-string/?utm_source=tuicool&utm_medium=referral
[3]: /topics/11000083
[4]: https://www.freecodecamp.cn/challenges/reverse-a-string
[5]: https://www.freecodecamp.com/challenges/reverse-a-string
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/reverse
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/join
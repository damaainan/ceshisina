# FreeCodeCamp 中级算法题 - 寻找缺失的字母

 时间 2017-04-14 04:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-missing-letters/][1]



* [中文链接][4]
* [英文链接][5]
* 级别：中级 (Intermediate Algorithm Scripting)

> 从传递进来的字母序列中找到缺失的字母并返回它。

> 如果所有字母都在序列中，返回 undefined。

> fearNotLetter("abce") 应该返回 "d"。
> fearNotLetter("abcdefghjklmno") 应该返回 "i"。
> fearNotLetter("bcd") 应该返回 undefined。
> fearNotLetter("yz") 应该返回 undefined

## 问题解释 

* 这个 function 接收一个字符串参数 str 。返回值也为字符，即缺失的字母。如果没有缺失字母，则返回 undefined
* 举个例子，如果传入参数是 "abce" ，那么返回值应为 "d" 。如果传入参数是 "abc" ，那么返回值为 undefined

## 基本解法 

## 思路提示 

* 这道题目的难度也不大，是一个可以用逻辑短路解决的问题
* 我们肯定要先遍历字符串。只要找到不连续的，直接返回结果就可以了，不用继续判断。如果找到字符串最后，还是连续的，那么我们就返回 undefined
* 至于如何判断是否连续，由于传入的字符串全都是小写，那我们可以通过字符串的 charCodeAt 方法得到 ASCII 码，然后比较一下是否差 1 就可以了
* 但是需要注意，由于返回值是缺失的字符，因此我们还要通过 fromCharCode 来得到返回值

## 参考链接 

* [String.charCodeAt()][6]
* [String.fromCharCode()][7]

## 代码 
```js
function fearNotLetter(str){
    for (var i = 0; i < str.length - 1; i++) {
        var currentCode = str[i].charCodeAt();
        var nextCode = str[i + 1].charCodeAt();

        if (currentCode !== nextCode - 1) {
            return String.fromCharCode(currentCode + 1);
        }
    }
}
```

## 解释 

* 有些朋友可能会说，为什么这个函数没设置最终返回值？如果没有明确地指出函数返回值，那么这种情况被称为 “Implicit Return”，与之相对应的是 “Explicit Return”
* JavaScript 有一个特性，对于 “Implicit Return”，就直接会返回 undefined 。这也正是我们想要得到的结果。循环部分的意思就是，只要找出当前字符的 ASCII 码与下一个字符的 ASCII 码差值不为 1 的，那么就直接返回当前字符码加一得到的字符
* 这里无意讨论 “Implicit Return” 这个 pattern 是好还是坏，个人认为这只是不同语言的特点罢了。比如 Java 和 C++ 不允许 “Implicit Return”，而 JavaScript、Python、Ruby 等很多语言都允许。既然语言允许，这里又可以用，为什么不把它利用起来呢？

## 换个写法 - 递归 

## 思路提示 

* 只是换写法噢，这里不应该算是优化，给热爱递归的朋友一点思路
* 如果你要尝试递归写法，请想好以下三点再动手： 
  * 边界条件/跳出条件
  * 参数的设置
  * 返回值的设置
* 这道题中，其实就是把检查的逻辑进行一次封装

## 代码 
```js
function fearNotLetter(str){
    // 设置初始条件
    var start = 0;

    functioncheckConsecutive(index){
        if (index === str.length - 1) {
            return;
        }

        var a = str[index].charCodeAt();
        var b = str[index + 1].charCodeAt();

        if (a !== b - 1) {
            return String.fromCharCode(a + 1);
        } else {
            return checkConsecutive(index + 1);
        }
    }

    return checkConsecutive(start);
}
```

## 解释 

* 貌似要解释的也不太多了，顺着基本解法的思路，就能写出来。这里的逻辑和之前没有任何区别，只是写法不同了而已

## 再换一种思路 - 使用数组和 ES6 语法 

## 思路提示 

* 如果你熟悉 ES6 语法，你可能会知道 Array.from 这个方法
* 那么思路就是，根据传入字符串的开头和结尾，生成一个字符数组，包含这个范围内的所有数组
* 然后再对这个数组进行 filter 操作，找出与传入字符串不同的元素就可以了
* 需要注意的是， filter 方法返回的是字符串。既然只可能有一个字符缺失，那么我们只需要读取过滤后字符串的第一个元素就行
* 就算过滤后长度为 0，那么就表示传入的字符串没有缺失，如果用 [0] 读取第一个元素就会得到 undefined ，这也符合我们的要求

## 参考链接 

* [Array.from()][8]
* [Array.filter()][9]

## 代码 

### ES6 
```js
function fearNotLetter(str){
    const start = str[0].charCodeAt();
    const end = str.slice(-1).charCodeAt();

    let charArr = Array.from({length: end - start + 1}, (_, i) => String.fromCharCode(start + i));
    
    return charArr.filter(e=> str.indexOf(e) === -1)[0];
}
```

## 解释 

* 数组的长度，我们不能直接通过 str 的长度来确定。因为我们不知道这个字符串是否连续
* 至于生成 charArr 的时候，做差之后该不该加 1，举个实际的例子就明白了
* 同样，这个写法，并不会提升多少运行速度，只是一种思路而已


[1]: http://singsing.io/blog/fcc/intermediate-missing-letters/?utm_source=tuicool&utm_medium=referral
[4]: https://www.freecodecamp.cn/challenges/missing-letters
[5]: https://www.freecodecamp.com/challenges/missing-letters
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/charCodeAt
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/fromCharCode
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/from
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
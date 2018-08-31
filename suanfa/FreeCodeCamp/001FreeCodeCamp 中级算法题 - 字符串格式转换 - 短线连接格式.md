# FreeCodeCamp 中级算法题 - 字符串格式转换 - 短线连接格式

 时间 2017-04-22 12:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-spinal-tap-case/][1]

* [中文链接][3]
* [英文链接][4]
* 级别：中级 (Intermediate Algorithm Scripting)

> 将字符串转换为 spinal case。Spinal case 是 all-lowercase-words-joined-by-dashes 这种形式的，也就是以连字符连接所有小写单词。

> spinalCase("This Is Spinal Tap") 应该返回 "this-is-spinal-tap"。
> spinalCase("thisIsSpinalTap") 应该返回 "this-is-spinal-tap"。
> spinalCase("The_Andy_Griffith_Show") 应该返回 "the-andy-griffith-show"。
> spinalCase("Teletubbies say Eh-oh") 应该返回 "teletubbies-say-eh-oh"。
## 问题解释 

* 这个 function 接收一个字符串参数 str 。返回值为转换后的字符串
* 如果传入的字符串是 "A Bcd" ，那么返回值应为 "a-bcd"
* 这道题乍一看好像很简单，但其实还是有一点点难度的。题目的重点在于判断断线连接的位置，或者说判断单词的边界

## 解题思路 

* 这道题先来说说思路。因为你可能会发现，这道题用循环是很难写的。如果你不这么认为，那就写一下试试吧
* 一开始的思路，很多朋友可能想的是，很简单啊，先转小写然后 split 再 join("-") 就好了。其实并没有这么简单。根据左下角的测试实例，我们先来总结一下，原字符串 str 会有几种单词边界标识： 
  * 第一个测试，字符串是 This Is Spinal Tap 。因此，这里是通过空格来判断边界
  * 第二个测试，字符串是 thisIsSpinalTap 。因此，这里是通过大写字符来判断边界
  * 第三个测试，字符串是 The_Andy_Griffith_Show 。因此，这里是通过下划线 _ 和大写字符来判断边界
  * 第四个测试，字符串是 Teletubbies say Eh-oh 。观察一下结果就会发现， "Eh-oh" 需要被认为是一个词，因此这里也是通过空格来判断边界
* 综上，单词边界的标识可以是空格或大写字符，也可以是下划线。这样，你应该就明白为什么先转小写显然是不行的了
* 对于空格和下划线，解决方式其实很简单，只需要把空格和下划线替换成 - 就可以了
* 第二种情况的没有空格的大写字符(其实就是 camelCase)可能会比较难处理。但其实，你会发现只要是一个小写字符后面紧跟着一个大写字符，那这里就需要转换。因此，通过正则也是不难写的

## 基本解法 - replace 

## 思路提示 

* 既然分割单词的标识可以是空格或大写字符，或两者混搭，或下划线，那么用正则去解就是最方便的方法了
* 至于字符串方法的选择，既然是要找出对应的部分替换成 - ，那当然要用到 replace
* 对于空格，我们可以用 \s 来判断。对于下划线，只需要 _ 就可以了。而且这两种情况是互相独立的，因此，我们可以写成 \s|_
* 对于第二种情况，一个小写字符后面紧跟着一个大写字符，那我们只需要写成 [a-z][A-Z] 就行。注意这里其实省略了后面的匹配次数标识符。你可能还记得，后面加上 ? 表示 0 次或 1 次，加上 + 表示 1 次至 n 次。但这里，我们希望找到的是一个小写字符和一个大写字符挨着的情况，所以不需要匹配次数标识符
* 那么，对于上面提到的这两种写法，分开处理会比较容易。其实，两个 replace 连用就可以解决了。当然，替换之后还要进行一次 toLowerCase

## 参考链接 

* [String.replace()][5]
* [String.toLowerCase()][6]
* [正则表达式][7]

## 代码 
```js
function spinalCase(str){
    return str.replace(/\s|_/g, '-').replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
}
```

## 解释 

* 第一个 replace 就是把空格和下划线替换成 - 。第二个就是找出小写字符之后紧跟着大写字符的形式，然后在他们之间加上一个 - 。其中， $1 就是第一个匹配组 [a-z] ， $2 对应的是 [A-Z] 。这里小括号是必须的，因为加上小括号才表示捕获组 (Capture Group)
* toLowerCase 不会影响非字母部分，直接通过替换之后的字符串去调用就可以了，会把其中存在的大写字符转换为小写

## 另一种写法 - split 

## 思路提示 

* 这道题用 split 不是不行。但正则会比较难写。举个例子， str.split(' ') 分割 str ，分割之后空格是不会保留的。处理之前提到的下划线和空格的情况很容易写，因为我们本身就不需要这个字符。但处理第二种情况 (camelCase) 就会比较麻烦
* 正则中，有一种查找方式叫 positive lookahead (正向先行断言)，用法是 a(?=b) 。它表示，如果 a 之后紧跟着 b ，那么这个 a 就会被匹配到。类似的还有 negative lookahead (负向先行断言)，用法是 a(?!b) 。表示如果 a 之后跟着的不是 b ，那么这个 a 就会被匹配到。显然，这里我们可以通过正向先行断言来处理第二种情况
* 在写的时候需要注意，由于 split 是不保留分割参考的，因此不能写成 ([a-z])(?=[A-Z]) 。这样，在大写字符前面的小写字符就不会被保留下来。只需要写成 (?=[A-Z]) 就可以，这样就会在大写字符前，上一个字符的位置之后进行分割


[1]: http://singsing.io/blog/fcc/intermediate-spinal-tap-case/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/spinal-tap-case
[4]: https://www.freecodecamp.com/challenges/spinal-tap-case
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/replace
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/toLowerCase
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Guide/Regular_Expressions
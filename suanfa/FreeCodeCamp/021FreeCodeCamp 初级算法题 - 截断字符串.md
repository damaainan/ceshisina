# FreeCodeCamp 初级算法题 - 截断字符串

 时间 2017-03-22 11:05:03  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/2017/03/21/truncate-a-string/][1]

 主题 [算法][2]

* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)


> 用瑞兹来截断对面的退路!

> 截断一个字符串！

> 如果字符串的长度比指定的参数num长，则把多余的部分用...来表示。

> 切记，插入到字符串尾部的三个点号也会计入字符串的长度。

> 但是，如果指定的参数num小于或等于3，则添加的三个点号不会计入字符串的长度。


> truncate("A-tisket a-tasket A green and yellow basket", 11) 应该返回 "A-tisket...".
> truncate("Peter Piper picked a peck of pickled peppers", 14) 应该返回 "Peter Piper...".
> truncate("A-tisket a-tasket A green and yellow basket", "A-tisket a-tasket A green and yellow basket".length) 应该返回 "A-tisket a-tasket A green and yellow basket".
> truncate("A-tisket a-tasket A green and yellow basket", "A-tisket a-tasket A green and yellow basket".length + 2) 应该返回 "A-tisket a-tasket A green and yellow basket".
> truncate("A-", 1) 应该返回 "A...".
> truncate("Absolutely Longer", 2) 应该返回 "Ab...".

## 问题解释 

* 这个 function 接收两个参数。第一个是字符串 str ，即为原字符串。第二个是截取长度 num 。返回值为截取后的字符串
* 这道题，关键问题在于对后续 ... 的处理，可以先考虑一下

## 参考链接 

* [String.slice()][5]
* [String.substr()][6]

## 思路提示 

* 首先应该想到，截取的逻辑大致分为两种情况： 
  1. 截取后，带 ...
  1. 截取后不带 ... ，这种情况其实就是输出 str 本身
* 进一步考虑第一种情况，又分为两种： 
  1. num > 3 的时候，需要少截取三位，因为 ... 的长度会计入结果长度
  1. num < 3 的时候，正常截取，并把 ... 加到结尾
* 对于带 ... 和不带 ... 的情况，判断依据是 num 是否大于 str.length
* 先想清楚这些，代码就很好写了

## 参考答案 

### 基本答案 
```js
    function truncate(str, num){
        if (str.length > num) {
            if (num > 3) {
                // num 大于 3，因此要少截三位，留出 ... 的位置
                return str.substr(0, num - 3) + '...';
            } else {
                // num 小于 3，正常截取，并把 ... 补到后面
                return str.substr(0, num) + '...';
            }
        } else {
            // num 比字符串长度大
            return str;
        }
    }
    
```
#### 解释 

* 由于这道题目逻辑层次嵌套比较复杂，因此我决定不省略 else 了
* 用 substr 或者 slice 都是没问题的。而且这道题是从 0 开始，所以两个方法的第二个参数都为 num
* 由于是根据长度来决定，因此我首先考虑到的是用 substr

### 中级解法 - 用三元运算符合并逻辑 
```js
    function truncate(str, num){
        if (str.length > num) {
            return str.substr(0, num > 3 ? num - 3 : num) + '...';
        }
        return str;
    }
```


[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/2017/03/21/truncate-a-string/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11000083
[3]: https://www.freecodecamp.cn/challenges/truncate-a-string
[4]: https://www.freecodecamp.com/challenges/truncate-a-string
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/slice
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/substr
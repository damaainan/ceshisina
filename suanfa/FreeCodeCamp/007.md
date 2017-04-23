# FreeCodeCamp 中级算法题 - 儿童黑话

 时间 2017-04-10 00:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-pig-latin/][1]


* [中文链接][4]
* [英文链接][5]
* 级别：中级 (Intermediate Algorithm Scripting)

> 把指定的字符串翻译成 pig latin。

> Pig Latin 把一个英文单词的第一个辅音或辅音丛（consonant cluster）移到词尾，然后加上后缀 "ay"。

> 如果单词以元音开始，你只需要在词尾添加 "way" 就可以了。

> translate("california") 应该返回 "aliforniacay"。
> translate("paragraphs") 应该返回 "aragraphspay"。
> translate("glove") 应该返回 "oveglay"。
> translate("algorithm") 应该返回 "algorithmway"。
> translate("eight") 应该返回 "eightway"。





## 问题解释 

* 这个 function 接收一个字符串参数 str ，即为原单词。返回值是翻译后的单词，也为字符串
* 比如接收的是 "california" ，那么输出就是 "aliforniacay" 。如果接收的是 "eight" ，那么输出是 "eightway"
* 翻译规则并不复杂，分为两种情况： 
  * 如果单词以元音 (a, e, i, o, u) 开头，那么就直接在结尾加上 "way"
  * 如果单词不以元音开头，那么就把元音前的辅音移至结尾，并加上 "ay"

## 基本解法 - 循环 

## 思路提示 

* 一个很典型，而且不复杂的字符串问题，只需要通过字符串方法解决就好
* 你可能会想到通过元音位置分割成数组，但事实上， split 不会保留参数。比如， "red".split("e") 会得到 ["r", "d"] 。但我们是需要保留分割参数的
* 无论哪种思路，关键逻辑都在于找出第一个元音。只有判断出第一个元音位置，才可以继续执行后续操作
* 对于第一种情况，直接返回字符串加上结尾的 "way" 就好。对于第二种情况，取出元音和之后的部分，添加开头的辅音从(可能不止一个字符)，再加上 "ay"
* 其实，这个需求用正则去解决会比较好。基本解法中我们先不涉及正则，通过最简单的循环来解决

## 参考链接 

* [Array.indexOf()][6]
* [String.slice()][7]

## 代码 
```js
function translate(str){
    // 储存第一个元音的索引
    var vowelIndex;

    for (var i = 0; i < str.length; i++) {
        if (["a", "e", "i", "o", "u"].indexOf(str[i]) > -1) {
            vowelIndex = i;
            break;
        }
    }

    if (vowelIndex) {
        return str.slice(vowelIndex) + str.slice(0, vowelIndex) + "ay";
    }

    return str + "way";
}
```

## 解释 

* 以防有些朋友说这样逻辑不严谨，先解释一点：在英文中，没有任何一个单词 (缩写除外) 是全部由辅音构成的。因此，只要传入的参数是一个合理的英文单词，我们就可以找到元音
* 我们用一个元音数组来判断当前遍历的字符是否为元音。也就是说，遍历过程中，每一个字符都要在那个元音数组中检查一下 indexOf 。如果是元音，那么 index 肯定是大于 -1 的
* 而且要注意，只要找到了第一个元音字符，我们就不该继续遍历了。这就是 break 的作用。简单来说，这里是一个逻辑短路
* 对于结果的返回，这里用到了一个算不上技巧的技巧。在 if 条件中，条件会被隐式转换成 Boolean。举个例子， if (false) 那肯定是不能通过的，但同时， if (undefied) 、 if (null) 、 if(0) 、 if("") 都是不能通过的
* 这涉及到 JavaScript 中一个非常重要的概念：Falsy Value（假值），在一些文档中可能会被拼写成 Falsey。印象中之前一道题目也涉及到了这个知识点。以下均为 Falsy： 
  * false
  * null
  * undefined
  * 0
  * NaN
  * "" 和 ''
* 所以，上面的代码中，如果 vowelIndex 为 0 ，也就表示第一个字符是元音，那么就不会进入 if 里面，而是直接执行最后一行的 return str + "way"
* 如果 vowelIndex 不为 0，那么才会按照 if 里面的方式去处理
* 多说一句。使用这样的隐式转义，一定要小心谨慎。由于这道题目我们 **可以假定** 传入的字符串是一个合理的单词，所以才会这么写。至于不严谨的地方，可以举个例子 
  * 比如传入的字符串全都是辅音，那么循环结束后， vowelIndex 也没有被赋值，因此还是 undefined 。通不过 if 判断，所以会执行最后一行代码，也就是直接在结尾添加 "way"
* 正如一开始所说，这道题目中我们可以假定传入的是一个合理的单词，所以我觉得这段代码是没问题的

## 一点点优化 - 通过 exec 来获取元音位置 

## 思路提示 

* 正则表达式中有一个方法叫 match ，相对应的字符串方法是 exec ，但其实两者有些区别： 
  * 尽管它们都返回数组，但 match 可以通过 /g 这个 flag 来直接返回所有的匹配字符，而 exec 只返回第一个匹配字符，不能通过 /g 返回所有匹配
  * 如果存在捕获组 (Capture Group)，那么 exec 可以直接返回所有捕获组， match 也可以通过不带 /g 的方式来返回不包含匹配字符的所有捕获组
* 当然，我们可以简单地用 exec 方法直接得到第一个元音的位置。上面的循环可以直接改写成一行：
```js
var vowelIndex = /[aeiou]/.exec(str).index;
```

* 这是我能想到的，得到第一个元音索引最简单的办法
* 接下来，就是优化一点字符串的输出逻辑。分情况讨论如下： 
  * 如果元音的索引为 0 ，那么就输出元音和元音后的部分加上 "way"
  * 否则，就输出元音和元音后的部分加上元音前的部分再加上 "ay"
* 进一步归纳，元音本身和元音后的部分无论如何都会输出的，结尾无论如何都得是 "ay" 。那么再这两部分之间，要么是元音前的字符，要么是 "w"
* 这一步的归纳很重要，因为这就是这个解法中的逻辑。如果归纳不出这一点，那就没法合并前面的 ifelse 部分

## 参考链接 

* [RegExp.exec()][8]
* [String.slice()][7]
* [String.substr()][9]
* [运算符优先级][10]

## 代码 
```js
function translate(str){
    var vowelIndex = /[aeiou]/.exec(str).index;

    return str.slice(vowelIndex) + (str.substr(0, vowelIndex) || "w") + "ay";
}
```

## 解释 

* 多说一句，请注意运算符的执行顺序。加减法算是 Operator 中的一种，它的优先级是高于逻辑判断 || 的。因此，我们需要给 || 这个判断外面加上一个括号，括号优先级是最高的
* 不需要去死记硬背优先级，多遇到几次问题、坑踩得多了自然就记住了
* 除此之外，就没有太多要说的了。这个写法的关键在于能否想出逻辑合并的思路

## 换一种思路 - 正则及字符串替换 

## 思路提示 

* 在这个解法中，我们来更进一步，直接用 replace 方法替换字符串
* 这个思路的关键在于设计一个科学合理的正则表达式。简单分析一下，我们需要得到字符串的三个部分：第一个元音之前的字符串、元音字符以及元音之后的字符串
* 所以我们肯定是要用 Capture Group（捕获组）的。大体形式就是 /()([aeiou])()/
* 那么前后两个括号该填什么就显得十分重要了。可以按照这样的思路去思考： 
  * 对于第一个组，应该为 Non Greedy（非贪婪）匹配，也就是只捕获到元音之前
  * 对于第二个组，只需要找到一个元音即可，因此不需要 Quantifiers（指示数量的 * 、 + 或者 ? 。因为就算要移动，我们也只会移动第一个组，而元音这个组是不会动的。所以这个组只起到一个定位的作用，仅此而已
  * 对于第三个组，应该为 Greedy（贪婪）匹配，因为需要一直捕获到单词结尾
  * 另一方面，第一个组和第三个组都可能为空
* 对于非贪婪匹配，最简单的方式就是在 Quantifiers 后面加上一个 ?
* 那么，对于第一个组，我们可以写成 (\w*?) 。其中 * 是 Quantifier，表示出现或不出现( 0 - n 次)。后面的 ? 表示非贪婪匹配，也就是说只匹配到下一个组（第二组）之前
* 对于第三个组，我们可以写成 (\w*) 。 * 的作用和上一条一样，而且 * 本身就是贪婪匹配
* 其实这里用 ES6 的 String Template（模板字符串）会更简洁
* 如果你看不懂上面说的这些，请先去看一看底下列出的参考链接

## 参考链接 

* [String.replace()][11]
* [Regular Expressions][12]
* [String Template][13]

## 代码 

### ES5 
```js
function translate(str){
    return str.replace(/(\w*?)([aeiou])(\w*)/, function(_, before, vowel, after){
        return vowel + after + before || "w" + ay;
    })
}
```

### ES6 
```js
function translate(str){
    return str.replace(/(\w*?)([aeiou])(\w*)/, (_, before, vowel, after) => `${vowel}${after}${before ||"w"}ay`);
}
```

[1]: http://singsing.io/blog/fcc/intermediate-pig-latin/?utm_source=tuicool&utm_medium=referral
[4]: https://www.freecodecamp.cn/challenges/pig-latin
[5]: https://www.freecodecamp.com/challenges/pig-latin
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/slice
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/RegExp/exec
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/substr
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Operator_Precedence
[11]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/replace
[12]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Guide/Regular_Expressions
[13]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/template_strings
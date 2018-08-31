# FreeCodeCamp 中级算法题 - 转换 HTML 实体字符

 时间 2017-04-22 11:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-convert-html-entities/][1]



* [中文链接][4]
* [英文链接][5]
* 级别：中级 (Intermediate Algorithm Scripting)

> 将字符串中的字符 &、<、>、" （双引号）, 以及 ' （单引号）转换为它们对应的 HTML 实体。


> convert("Dolce & Gabbana") 应该返回 Dolce &​amp; Gabbana。
> convert("Hamburgers < Pizza < Tacos") 应该返回 Hamburgers &​lt; Pizza &​lt; Tacos。
> convert("Sixty > twelve") 应该返回 Sixty &​gt; twelve。
> convert('Stuff in "quotation marks"') 应该返回 Stuff in &​quot;quotation marks&​quot;。
> convert("Shindler's List") 应该返回 Shindler&​apos;s List。
> convert("<>") 应该返回 &​lt;&​gt;。
> convert("abc") 应该返回 abc。



## 问题解释 

* 这个 function 接收一个字符串参数 str 。返回值为转换后的字符串
* 如果传入的字符串是 "Dolce & Gabbana" ，那么返回值应为 "Dolce & Gabbana"
* 在这道题目中，只有以下五个字符需要转换： 
  * & 需转换为 '&'
  * < 需转换为 '<'
  * > 需转换为 '>'
  * ' 需转换为 '''
  * " 需转换为 '"'

## 基本解法 - 遍历字符串 

## 思路提示 

* 这道题目难度不大，基本解法中我们先用最简单的遍历去解决。当然，首先需要生成一个空字符串用于存储结果
* 类似的思路是，可以先 split ，然后操作数组，最后再 join 。由于思路很相似，这里就不给出了
* 需要注意的是遍历的开始位置，这很重要。可以先考虑一下，如果从左边开始遍历，由于替换的字符串比原字符长，因此字符串总长度会增加。换句话说，增加的字符串会占用未遍历到的位置
* 因此，从右边开始遍历会是一个更好的选择。另外，从右边开始遍历的话，初始值要设置为 str.length - 1 。跳出条件也应该是 i < 0 。原因很简单，可以自己想一下为什么
* 如果这里用数组去解，就不会有遍历顺序的问题

## 代码 
```js
function convert(str){
    var result = '';

    for (var i = str.length - 1; i >= 0; i--) {
        if (str[i] === '&') {
            result = '&' + result;
        } else if (str[i] === '<') {
            result = '<' + result;
        } else if (str[i] === '>') {
            result = '>' + result;
        } else if (str[i] === "'") {
            result = ''' + result;
        } else if (str[i] === '"') {
            result = '"' + result;
        } else {
            result = str[i] + result;
        }
    }

    return result;
}

```
## 解释 

* 应该不难懂，没什么太多需要解释的。只需要注意一点，既然是从右向左遍历，所以不能写成 result += 这样的形式，这样做是翻转字符串
* 尽量避免转义字符。见到过有些朋友喜欢一直用单引号或双引号，结果就会写成 '\'' 或者 "\"" 这样的形式。个人不是很喜欢这种写法

## 换个思路 - 使用对象和正则 

## 思路提示 

* 对于这道题目的需求，既然存在一一对应关系，我们还可以用 Object 去处理，因为 Object 就是一一对应关系 (也可以称为 Map。注意这个 Map 不是数组方法，而是一种数据结构)
* 而且，既然我们直接操作字符串，那么最方便的替换方式就是用正则表达式了。这个正则表达式很容易写， /[&<>'"]/g ，总共就只有这五种情况
* 但在用 replace 方法的时候，我们不能直接给出字符串表达式，因此需要给 replace 一个回调函数。至于这个函数的参数，我们只需要用第一个，也就是匹配部分
* replace 的回调函数返回值即字符串，也就是我们要替换成的目标字符串。在回调函数中，我们只需要通过上面创建好的 Object 去找就可以了
* 有的朋友可能会有疑问，能用特殊符号作为 Object 的 key 么？当然是可以的。任何 **可以被转换成为字符串的** 东西，都可以作为 Object 的 key

## 参考链接 

* [String.replace()][6]

## 代码 
```js
function convert(str){
    var entityMap = {
        '&': '&',
        '<': '<',
        '>': '>',
        "'": ''',
        '"': '"'
    };

    return str.replace(/[&<>'"]/g, function(matched){
        return entityMap[matched];
    })
}
```

## 换个思路 - 利用数组的 map 或 reduce 

## 思路提示 

* 上面提了一句，可以用数组来解这道题。这里不给出数组的循环解法，给出用 map 或 reduce 的写法
* map 方法会返回等长度的数组。我们会改数组中的元素，但不会影响数组的整体长度，因此这个办法是可行的
* 当然，如果用 map ，由于它还是返回数组，因此最后要 join 起来，返回字符串
* 你还可以观察出，其实这道题的逻辑思路也符合我们以前说过的”把上一次计算结果用于下一次计算”。因此，这里用 reduce 也是没有问题的
* reduce 这个方法的返回值，可以是数组、对象、数字、字符串等等。布尔值也是可以的，印象中我们在之前的题目里遇到过。至于到底返回什么，取决于你怎么写回调函数

## 参考链接 

* [String.split()][7]
* [Array.map()][8]
* [Array.reduce()][9]
* [Array.join()][10]

## 代码 - map 

### ES6 
```js
function convert(str){
    const entityMap = {
        '&': '&',
        '<': '<',
        '>': '>',
        "'": ''',
        '"': '"'
    };

    return str.split('').map(char=> entityMap[char] || char).join('');
}
```

[1]: http://singsing.io/blog/fcc/intermediate-convert-html-entities/?utm_source=tuicool&utm_medium=referral
[4]: https://www.freecodecamp.cn/challenges/convert-html-entities
[5]: https://www.freecodecamp.com/challenges/convert-html-entities
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/replace
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/reduce
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/join
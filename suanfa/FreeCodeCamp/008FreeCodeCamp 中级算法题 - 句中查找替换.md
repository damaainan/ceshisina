# FreeCodeCamp 中级算法题 - 句中查找替换

 时间 2017-04-07 22:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-search-and-replace/][1]


* [中文链接][4]
* [英文链接][5]
* 级别：中级 (Intermediate Algorithm Scripting)


> 使用给定的参数对句子执行一次查找和替换，然后返回新句子。

> 第一个参数是将要对其执行查找和替换的句子。

> 第二个参数是将被替换掉的单词（替换前的单词）。

> 第三个参数用于替换第二个参数（替换后的单词）。

> 注意：替换时保持原单词的大小写。例如，如果你想用单词 "dog" 替换单词 "Book" ，你应该替换成 "Dog"。

> myReplace("Let us go to the store", "store", "mall") 应该返回 "Let us go to the mall"。
> myReplace("He is Sleeping on the couch", "Sleeping", "sitting") 应该返回 "He is Sitting on the couch"。
> myReplace("This has a spellngi error", "spellngi", "spelling") 应该返回 "This has a spelling error"。
> myReplace("His name is Tom", "Tom", "john") 应该返回 "His name is John"。
> myReplace("Let us get back to more Coding", "Coding", "algorithms") 应该返回 "Let us get back to more Algorithms"。




## 问题解释 

* 这个 function 接收三个字符串参数。第一个参数 str 为原字符串，第二个参数 before 为需要替换的部分，第三个参数 after 为替换后的内容。返回值为按规则替换后的字符串
* 举个例子，如果第一个参数是 "His name is Tom" ，第二个参数是 "Tom" ，第三个参数是 "john" ，那么返回值应为 "His name is John"
* 需要注意题目的另一条要求，我们需要保持被替换部分的大小写格式。可能这就是本题出现在中级算法的原因吧

## 基本解法 - 分割成数组再合并 

## 思路提示 

* 题目描述中，建议使用数组的 splice 和 join 方法。尽管不是很必要，但这个写法可能是最容易上手的
* 虽然第一个参数是一个句子，但句子中并不包含标点符号，因此我们可以直接通过空格来 split
* 然后要做的，就是找到需要替换的单词的索引，并把它替换成我们想要的结果。题目中建议用 splice ，其实直接通过赋值去修改这个数组也是可行的
* 至于替换的目标字符串，首先我们需要根据原句中那个单词的大小写格式来转换第三个参数。尽管不确切，但我们可以暂时假定它只存在两种情况：全小写或首字母大写
* 看答案之前，请自己先尝试一下。这道题的写法比较多，看看你能写出来几种

## 参考链接 

* [String.split()][6]
* [Array.splice()][7]
* [Array.join()][8]

## 代码 
```js
function myReplace(str, before, after){
    if (str.length === 0 || before.length === 0) {
        return;
    }

    var strArr = str.split(" ");
    // 找出第二个参数在数组中的索引
    var srcIndex = strArr.indexOf(before);

    // 判断首字母是否为大写
    if (strArr[srcIndex][0] === strArr[srcIndex][0].toUpperCase()) {
        after = after[0].toUpperCase() + after.slice(1);
    }

    strArr.splice(srcIndex, 1, after);

    return strArr.join(" ");
}
```

## 解释 

* 养成一个好习惯，上来先判断边界。这道题目虽然不判断也没关系，但在实际开发的过程中，边界值检查很重要
* 以上代码只是其中一种写法，其中可以替换的部分很多。比如： 
  * 判断首字母大写的方式有很多，可以用正则，也可以用 charCodeAt 方法。上面的判断方式是，对于一个字符，如果转换大写后和原来的值相等，那么这个字符本身就是大写的
  * after 重新赋值那一行，也可以用 substr 或 substring 方法来替代。这三个方法的比较，请参考 [Confirm the Ending 那道题目中的解释][9]
  * splice 那一行，第二个参数为要向后删除的数量，第三个参数为可选参数，表示要添加进来的元素。也可以写成 strArr[srcIndex] = after
* 多说一句，请记住， splice 和 push 是为数不多的，不返回操作后的数组的方法

## 另一种写法 - 使用字符串方法 

## 思路提示 

* 个人认为，分割成数组是不必要的，因为我们完全可以通过字符串方法来实现
* 在字符串中寻找某一个片段，我们可以直接使用字符串的 indexOf 方法。但在这道题目中，既然我们找到之后还要替换，那么完全可以使用 replace 方法
* 既然使用了 replace ，那么就需要用正则去匹配传入的 before 了。如果你不知道该怎么做，请先去 MDN 看看 RegExp() 构造器是如何使用的
* 至于判断首字符是否为大写，你可能已经想到了，用正则也可以

## 参考链接 

* [String.indexOf()][10]
* [String.replace()][11]
* [RegExp()][12]

## 代码 
```js
function myReplace(str, before, after){
    if (str.length === 0 || before.length === 0) {
        return;
    }

    return str.replace(RegExp(before), function(matched){
        if (/^[A-Z]/.test(matched)) {
            return after[0].toUpperCase() + after.slice(1);
        }
        return after;
    });
}
```

## 解释 

* 可能平时见的比较多的用法，是给 replace 的第二个参数传入一个字符串。但其实，传入一个回调函数也是没问题的。这个回调函数的第一个参数就是之前正则匹配到的部分
* 函数的返回值是目标字符，也就是需要替换成的字符。因为，这个函数的返回值其实就是 replace 的第二个参数
* 这里用到了逻辑短路的写法，其实这里执行的方式与在 return after 外面加上 else 是一样的。因为，如果 if 条件满足，那么就会直接执行里面的 return ，直接得出返回值，而而不会执行 if 外面的代码

## 优化 - 更加严谨的解法 

## 思路提示 

* 题目中的原话是”保持原单词的大小写”。因此并没有说大写字符一定会出现在第一位
* 尽管这道题目的测试中也没有出现大写字符在其他位置的情况，但根据题目描述，更严谨的解释是”保持原单词中大小写字符的索引不变”
* 那么这就引出了一个新的问题，如果原单词与替换的目标单词长度不同怎么办？我们可以这样分情况讨论： 
  * 如果原单词比目标单词短，那么对应的索引保持大小写不变，其他部分采用目标字符的大小写格式
  * 如果原单词比目标单词长，那么依然是对应的索引保持大小写不变，忽略超出的部分
* 有两种写法可以实现这个功能： 
  * 利用字符串的 search 方法，找出所有的大写或小写的位置索引。然后把目标字符串先都转换成小写或大写，再根据找出的索引来调整。但正则效率本身不高，因此不是很推荐这个做法
  * 个人更推荐的是先分割字符串，然后用数组的 map 方法生成一个 Boolean 数组，再根据这个数组去调整目标字符串

## 参考链接 

* [String.replace()][11]
* [String.split()][6]
* [Array.map()][13]
* [Array.join()][8]

## 代码 

### ES6 
```js
function myReplace(str, before, after){
    if (str.length === 0 || before.length === 0) {
        return;
    }

    // 大写为 true，小写为 false
    var booleanArr = before.split("").map(e=> e.toUpperCase() === e);
    var afterArr = after.split("");

    var formattedAfter = afterArr.map((str, index) => {
        if (index < booleanArr.length) {
            if (booleanArr[index]) {
                return str.toUpperCase();
            }
            return str.toLowerCase();
        }
        return str;
    }).join("");

    return str.replace(RegExp(before), formattedAfter);
}
```

[1]: http://singsing.io/blog/fcc/intermediate-search-and-replace/?utm_source=tuicool&utm_medium=referral
[4]: https://www.freecodecamp.cn/challenges/search-and-replace
[5]: https://www.freecodecamp.com/challenges/search-and-replace
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/splice
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/join
[9]: http://singsing.io/blog/fcc/basic-confirm-the-ending
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/indexOf
[11]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/replace
[12]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/RegExp
[13]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
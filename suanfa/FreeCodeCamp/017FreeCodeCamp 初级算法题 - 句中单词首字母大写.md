# FreeCodeCamp 初级算法题 - 句中单词首字母大写

 时间 2017-03-20 22:54:50  S1ngS1ng

_原文_[http://singsing.io/blog/2017/03/18/title-case-a-sentence/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 确保字符串的每个单词首字母都大写，其余部分小写。

> 像'the'和'of'这样的连接符同理。

> titleCase("I'm a little tea pot") 应该返回一个字符串
> titleCase("I'm a little tea pot") 应该返回 "I'm A Little Tea Pot".
> titleCase("sHoRt AnD sToUt") 应该返回 "Short And Stout".
> titleCase("HERE IS MY HANDLE HERE IS MY SPOUT") 应该返回 "Here Is My Handle Here Is My Spout".



## 问题解释 

* 这个 function 接收一个字符串参数，返回操作之后的字符串
* 比如接收的是 "short sentence" ，那么输出就是 Short Sentence ，如果接收的是 "shOrT sEnTEnce" ，那么输出也应该是 Short Sentence
* 需要注意的是，题目中暗含了一个情况需要处理，就是每个单词，如果在非首字母位置出现了大写，也要输出小写

## 参考链接 

* [String.split()][5]
* [Array.join()][6]
* [String.toLowerCase()][7]
* [String.toUpperCase()][8]
* [String.substr()][9]
* [String.slice()][10]

## 思路提示 

* 既然我们要操作每一个单词，那可以先用 split(" ") 去分割传入的字符串成为数组，这样操作起来就会方便很多
* 另一方面，为了方便操作，我们可以在分割之前先把所有字符都用 .toLowerCase() 转换成小写。JavaScript 只会去操作大写字符，转换成小写。至于特殊符号和空格，不会影响函数执行
* 分割之后，遍历数据，然后把第一个字符变成大写就行。转换方式就是用 toUpperCase()
* 为保证单词其他部分不变，我们只需要把第一个字符转换成大写，再用 .substr() 或者 .slice() 取出单词剩余部分，拼凑起来就可以了
* 同样，这道题也可以使用正则表达式以及字符串方法完成

## 参考答案 

### 基本答案 - 分割，转换，合并 
```javascript
    function titleCase(str){
        // 转小写及分割成数组
        var stringArr = str.toLowerCase().split(" ");
    
        for (var i = 0; i < stringArr.length; i++) {
            // 修改数组元素。第[0]位就是单词的首字母，转成大写，然后把后面的字符加上去
            stringArr[i] = stringArr[i][0].toUpperCase() + stringArr[i].slice(1);
        }
    
        return stringArr.join(" ");
    }
    
```
#### 解释 

* 可能会有朋友问，为什么不在 function 里生成一个空字符串，再遍历数组，动态地把处理过的字符串添加进去呢？这个思路当然是没问题的，只是需要多做一步，就是处理多余的空格。大家可以写一下试试，顺便，去掉首尾空格的方法叫 .trim()
* .slice(1) 的意思就是，从索引为 1 的字符，一直复制到字符串结尾。这个步骤同样可以写成 .substr(1)
* 最后那里，一定要 .join(" ") ，这表示我们用空格把所有元素合并起来。而且这个空格，不会加到首尾。这也许是用数组的一个好处，毕竟 .join() 是数组方法

### 优化 

* 既然用了数组，这里又是要对每一个元素执行相同的操作，不妨用用 .map()#### 参考链接
* [Array.map()][11]    
```javascript
    function titleCase(str){
        return str.toLowerCase().split(" ").map(function(word){
            return word[0].toUpperCase() + word.slice(1);
        }).join(" ")
    }
```
#### 解释 

* .toLowerCase() 返回字符串，调用 .split() 方法之后返回数组。数组再去调用 .map() 方法
* 只要你理解了上面的基本解法，同时理解了 .map() 方法的作用，就应该不难理解这段代码。 .map() 的好处就在于，它是数组方法，而且返回值为操作之后的数组

### 中级解法 - 使用正则表达式及字符串方法 

* 思路上来说，就是通过正则匹配到首字母，并把它替换成为大写字符，同时，保留其他字符不变
* 当然，也是需要先进行 .toLowerCase() 操作的 
```javascript
    function titleCase(str){
        return str.toLowerCase().replace(/(\s|^)[a-z]/g, function(match){
            return match.toUpperCase();
        })
    }
```

[1]: http://singsing.io/blog/2017/03/18/title-case-a-sentence/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/title-case-a-sentence
[4]: https://www.freecodecamp.com/challenges/title-case-a-sentence
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[6]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_objects/Array/join
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/toLowerCase
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/toUpperCase
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/substr
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/slice
[11]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_objects/Array/map
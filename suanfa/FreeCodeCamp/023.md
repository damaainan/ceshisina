# FreeCodeCamp 初级算法题 - 找出最长的单词

 时间 2017-03-18 18:12:02  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/2017/03/18/fcc-basic-find-the-longest-word-in-a-string/][1]

 主题 [算法][2]

* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 找到提供的句子中最长的单词，并计算它的长度。

> 函数的返回值应该是一个数字。

> findLongestWord("The quick brown fox jumped over the lazy dog") 应该返回一个数字
> findLongestWord("The quick brown fox jumped over the lazy dog") 应该返回 6.
> findLongestWord("May the force be with you") 应该返回 5.
> findLongestWord("Google do a barrel roll") 应该返回 6.
> findLongestWord("What is the average airspeed velocity of an unladen swallow") 应该返回 8.
> findLongestWord("What if we try a super-long word such as otorhinolaryngology") 应该返回 19.

## 问题解释 

* 这个 function 接收一个字符串参数，返回最长单词的长度(一个数值)
* 比如接收的是 “May the force be with you”，那么最长的单词是 “force”，因此返回 5
* 这道题目的参数不含标点和特殊符号，单词之间使用空格分割

## 参考链接 

* [String.split()][5]
* [String.length][6]

## 思路提示 

* 不含标点和特殊符号的意思就是，不需要对字符串进行预先的处理
* 既然字符串是通过空格分割的，那么我们可以通过 .split() 方法，传入空格为参数，形成分割之后的数组，每个元素即为一个单词
* 核心逻辑在于，首先设置一个变量用于储存最长单词的长度，初始化为 0。然后，遍历数组，如果遇到一个单词长度比这个变量储存的长度大的，那就更新这个变量。否则，继续遍历
* 由于我们并不需要返回最长的单词是哪个，也不需要返回最长单词的位置，因此只需要存储长度即可
* 如果不用数组，单纯地遍历字符串 + 双指针，也是可以实现的，只是写起来会很麻烦。因此这道题，建议用数组去解决。在进阶方法中，你会体会到用数组的好处

## 参考答案 

### 基本答案 
```js
    function findLongestWord(str){
        // 按照空格分割字符串，生成数组
        var strArr = str.split(" ");
        // 初始化 length 为 0
        var length = 0;
    
        for (var i = 0; i < strArr.length; i++) {
            // 遍历过程中，若当前字符串长度比 length 大，就更新 length
            if (strArr[i].length > length) {
                length = strArr[i].length;
            }
            // 不需要 else，因为如果比 length 小，继续执行遍历就可以了
        }
        
        // 循环结束，返回 length 作为结果
        return length;
    }
    
```
#### 解释 

* 要解释的不多，都在注释里。理解这个思路就好，在 for 循环外设置一个变量，用于追踪最大值。找到更大的就更新，最后返回这个变量就行
* 至于为什么不在 for 循环里设置变量，因为如果这样，执行循环的每一步都会重新初始化这个变量，我们就不能用这个变量来追踪循环过程了

### 优化 

* 可以优化的方案是，采用数组内置方法 .reduce() 来实现 

#### 参考链接
* [Array.reduce()][7]
* [Math.max()][8]
```js
    function findLongestWord(str){
        var stringArr = str.split(" ");
    
        return stringArr.reduce(function(prev, next){
            // 返回值为参数与当前字符串中较大的数
            // 返回值会作为下次计算的 prev 传入
            return Math.max(prev, next.length);
        }, 0)
    }
    
```
#### 解释 

* 如果不熟悉 .reduce() 语法和参数，请先去上面的链接看一下
* .reduce() 的第一个参数为回调，第二个参数为初始值。第二个参数可以为空
* 举个例子，如果我们传入的字符串是 “a bb ccc d” 执行过程如下：

第 x 步 prev next 返回值 1 0 (初始值) 1 (“a” 的长度) 1 (较大的数) 2 1 (上一步的返回值) 2 (“bb” 的长度) 2 (较大的数) 3 2 (上一步的返回值) 3 (“ccc” 的长度) 3 (较大的数) 4 3 (上一步的返回值) 1 (“d” 的长度) 3 (较大的数) 

* 因此，最终结果为 3
* 关于 .reduce() ，一句话概括，就是：遍历数组，把上一次计算的结果用于下次计算

### 再优化 

* 有没有更简单的写法呢？当然是有的 

#### 参考链接
* [Array.map()][9]
* [Function.apply()][10]
```js
    function findLongestWord(str){
        return Math.max.apply(null, str.split(" ").map(function(e){
            return e.length;
        }))
    }
    
```
#### 解释 

* 一行搞定，看起来可能很酷炫，当然，生产环境中为了可读性，一般不推荐这么写。之所以把我的这个写法放出来，是因为里面涉及到的知识点
* 我们分开来看这段代码。先看 str.split(" ").map() 这部分。 .split() 不用多说了，大家都见过很多次了，重点说说 .map() 。 .map() 是我个人很喜欢的一个方法，对于这个方法，一句话概括，就是：返回等长度的，经过处理后的数组
* e 就是遍历过程中，每一步的元素。比如，数组是 [1, 2, 3] 。那么 .map(function (e)) 里面的 e 就分别是 1, 2, 3。如果我们写成 .map(function (e) { return e + 1 }) 这样，意思就是，对每一个元素都分别执行 + 1 操作，因此，最终我们就会得到 [2, 3, 4]
* 对于上面的代码来说，我们在 .map() 中的返回值为 e.length 。原来的数组元素是句子中的每一个单词， .map() 一下 e.length ，我们就得到了由每个单词长度组成的数组。举例说明，如果原数组是 ['a', 'bb', 'ccc'] ，那么操作之后我们就得到了 [1, 2, 3]
* 再说 Math.max.apply() 这部分，可以先去看一下上面的链接。 apply 的第一个参数是 this 指向，而关键在于第二个参数是数组或者类数组。举个例子，我们可以通过 Math.max(1, 2, 3) 得到 3，也可以通过 Math.max.apply(null, [1, 2, 3]) 来得到 3
* 这样，由于 .map() 那一步返回的是单词长度组成的数组，我们把这个数组扔给 Math.max.apply() 的第二个参数，就得到了那个数组的最大值
* 多说一句，由于 Math.max() 这个方法，并不依赖 context (执行上下文)，因此我们只需要给第一个参数传入 null 。当然，第一个参数传入 undefined 或者 Math 都是可以的

### 高级解法 - 递归 

* 有一句话要说，递归，并不能提高算法效率，只是一些情况下，写起来方便 

#### 参考链接
* [Array.slice()][11]
* [Array.splice()][12]
```js
    function findLongestWord(str){
        // 分割字符串
        var stringArr = str.split(" ");
    
        // 边界值及递归跳出条件
        if (stringArr.length === 1) {
            return stringArr[0].length;
        }
    
        // 比较数组中第一个元素(字符串)与第二个元素(字符串)的长度
        if (stringArr[0].length >= stringArr[1].length) {
            // 如果第一个元素比第二个长
            // 把第二个元素删掉
            stringArr.splice(1, 1);
            // 由于 findLongestWord 接收字符串为参数，因此需要用空格 join
            return findLongestWord(stringArr.join(" "));
        }
    
        if (stringArr[0].length < stringArr[1].length) {
            // 如果第一个元素比第二个元素短
            // 忽略第一个元素，从第二个开始 `.join()`
            return findLongestWord(stringArr.slice(1).join(" "));
        }
    }
```
#### 解释 

* 整体思路就是，每次我们只判断数组中的前两个元素 
  * 如果第一个比较长，那么我们就把第二个删除(留下较长的)，然后再去比较第一个和第三个
  * 如果第二个比较长，那么我们就选取数组中的第二个元素至末尾，然后继续去比较第二个和第三个
* 这样看来，每次递归调用，数组会越来越短。那么最后留下来的，就是最长的那个字符串
* 值得注意的是，由于 splice() 返回的是被删除的元素，而并非删除后的数组。因此我们不能直接链式调用 .join()

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/2017/03/18/fcc-basic-find-the-longest-word-in-a-string/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11000083
[3]: https://www.freecodecamp.cn/challenges/find-the-longest-word-in-a-string
[4]: https://www.freecodecamp.com/challenges/find-the-longest-word-in-a-string
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/split
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/length
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/Reduce
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Math/max
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Function/apply
[11]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
[12]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/splice
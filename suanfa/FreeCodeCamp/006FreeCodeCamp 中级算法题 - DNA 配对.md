# FreeCodeCamp 中级算法题 - DNA 配对

 时间 2017-04-12 02:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-dna-pairing/][1]



* [中文链接][3]
* [英文链接][4]
* 级别：中级 (Intermediate Algorithm Scripting)

> DNA 链缺少配对的碱基。依据每一个碱基，为其找到配对的碱基，然后将结果作为第二个数组返回。

> Base pairs（碱基对） 是一对 AT 和 CG，为给定的字母匹配缺失的碱基。

> 在每一个数组中将给定的字母作为第一个碱基返回。

> 例如，对于输入的 GCG，相应地返回 [["G", "C"], ["C","G"],["G", "C"]]

> 字母和与之配对的字母在一个数组内，然后所有数组再被组织起来封装进一个数组。

> pair("ATCGA") 应该返回 [["A","T"],["T","A"],["C","G"],["G","C"],["A","T"]]。
> pair("TTGAG") 应该返回 [["T","A"],["T","A"],["G","C"],["A","T"],["G","C"]]。
> pair("CTCTA") 应该返回 [["C","G"],["T","A"],["C","G"],["T","A"],["A","T"]]。

## 问题解释 

* 这个 function 接收一个字符串参数 str ，为需要转换的字符串。返回值为转换之后的二维数组
* 例如，第一个参数是 "GCG" ，那么返回值就是 [["G", "C"], ["C", "G"], ["G", "C"]]
* 匹配关系也不复杂， "G" 和 "C" 互相匹配， "A" 和 "T" 互相匹配

## 基本解法 

## 思路提示 

* 这道题看起来并不难，写起来也很简单。在基本解法中，我们可以只用循环来解决
* 当然，先生成一个空数组用于存储结果，然后遍历传入的字符串，对每一个都进行判断，把结果 push 进去就行了
* 循环用 for 或者 while 都行，哪个熟悉就用哪个吧。至于判断每一个元素，可以用 ifelse ，当然也可以用 switch
* 如果你用 while ，请记得先定义好参考值，设置好跳出条件，并且要在结尾改变参考值，否则就是死循环了
* 如果你用 switch ，请记得要在每个条件之后加上 break ，否则会继续判断下去。用 if 也是一样，对于独立判断条件，要用 else if ，而不是全用 if
* 如果你不知道这些内容在说什么，先试着用每种方法都写一遍，就明白了

## 代码 
```js
function pair(str){
    var result = [];

    for (var i = 0; i < str.length; i++) {
        if (str[i] === 'G') {
            result.push(['G', 'C']);
        } else if (str[i] === 'C') {
            result.push(['C', 'G']);
        } else if (str[i] === 'A') {
            result.push(['A', 'T']);
        } else if (str[i] === 'T') {
            result.push(['T', 'A']);
        }
    }

    return result;
}
```

## 解释 

* 唯一美中不足的就是，由于题目中没有说出特殊情况应该如何处理，所以这里也就没法考虑了。特殊情况的意思就是，如果传入的字符含有 "A", "T", "C", "G" 以外的字符

## 优化 - 使用对象 

## 思路提示 

* 对象用于存储一一对应关系。通过题意我们可以得知，碱基对存在如下的对应关系： 
  * A -> T
  * T -> A
  * C -> G
  * G -> C
* 所以，我们可以先创建一个 Object , 存储上面的这四条对应关系。这样在遍历过程中就不需要逐个判断了

## 代码 
```js
function pair(str){
    var result = [];
    var pairMap = {
        A: 'T',
        T: 'A',
        C: 'G',
        G: 'C'
    };

    for (var i = 0; i < str.length; i++) {
        result.push([str[i], pairMap[str[i]]]);
    }

    return result;
}
    
```
## 解释 

* 这样，代码看起来就清爽了不少。当我们需要类似与这样，一一对应关系的时候，请优先考虑使用对象
* 需要说明的是，定义对象的时候， key 可以不加引号，因为不管 key 是什么，JavaScript 都会把它转换为字符串。比如，如果 key 是数字 0 ，那在对象生成的时候就会是 "0" 。如果 key 本身就是一个对象，那就很微妙了。JavaScript 在生成对象的时候会调用它原型链上的 .toString() 方法 
  * 你可以试试，如果把 key 弄成 [0, 1] ，那么最后在 Object 中存储的会是 "0,1" 。这就是数组的 .toString() 方法干的事
  * 你还可以试试，如果把 key 弄成 {a: 1} ，那么最后在 Object 中储存的会是 "[object Object]"
* key 可以不加的，但后面的 value 是一定要加的，因为这里我们需要的是字符串。当然，你也可以更进一步，直接把 A 设置为 ['A', 'T']

## 优化 - 使用数组的 map 方法 

## 思路提示 

* 你会发现，传入的参数是字符串，而输出的是数组。而且，输出数组的长度与传入的字符串长度是相等的
* 之前的文章里提到过，数组的 map 方法返回的是等长度的数组，但前提是，你得用数组去调用 map 方法
* 那么问题就在于如何把传入的 str 转成数组，你肯定能想到，需要用 split
* 我们还是继续用上面写好的 pairMap ，而不是用 if 和 else 之类的来判断

## 参考链接 

* [Array.map()][5]

## 代码 

### ES6 
```js
function pair(str){
    const pairMap = {
        A: 'T',
        T: 'A',
        C: 'G',
        G: 'C'
    };

    return str.split('').map(e=> [e, pairMap[e]]);
}
    
```
## 解释 

* 不需要太多的解释了。只提示一点，如果你看不懂上面的回调，在 ES5 中其实就是这样写的：
```js
return str.split('').map(function(e){
    return [e, pairMap[e]];
});
```

[1]: http://singsing.io/blog/fcc/intermediate-dna-pairing/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/dna-pairing
[4]: https://www.freecodecamp.com/challenges/dna-pairing
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
## 正则表达式JavaScript版本回顾笔记背诵版本

来源：[https://segmentfault.com/a/1190000013741913](https://segmentfault.com/a/1190000013741913)


## 前言

正则表达式之所以强大，是因为其能实现模糊匹配，精确匹配没有任何价值。
## 正则表达式学习笔记
## 第一章 JavaScript原型对象与原型链
### 1.1 正则表达式概念

RegExp：是正则表达式（regular expression）的简写，RegExp描述了一种字符串匹配的模式，可以用来检查一个字符串是含有某种子串，将匹配的子串做替换或者从某个串中取出来符合某个条件的子串等。
### 1.2 正则表达式的用处

数据隐藏，数据采集（爬虫），数据过滤，数据验证（表单验证，手机号码，邮箱地址等），解析、格式检查等。

正则的定义格式也很简单：

```js
var t='ahmatbekjappar';

var reg=/j/;
console.log(reg.test(t));//判断我们查找字符是否存在，存在返回true
```
## 第二章 标准库中的正则对象
### 2.1 创建正则对象

创建正则对象的方法有两种：实际开发中一般都采用字面量创建方法

1.字面量创建法：以斜杠表示开始和结束；2.构造函数生成对象：通过实例化得到对象；


```js
var t='ahmatbekjappar';

var reg1=/j/;//方式1
var reg2=new RegExp('j');//方式2

console.log(reg1.test(t));
console.log(reg2.test(t));
```

区别：

相同点：效果完全一样，也就是等价的不同点：第一种方法是编译时新建正则表达式，第二种方法在运行时新建正则表达式，也就是创建时机不一样。


### 2.2 匹配模式

正则表达式是匹配模式，要么匹配字符，要么匹配位置

匹配模式指的是修饰符，表示正则匹配的附加规则，放在正则模式的最后面（尾部）。修饰符可以单个使用，也可以是多个一起用。

格式：var reg=/正则表达式/匹配模式;

在正则表达式中，匹配模式常用的两种形式：g和i

g:global缩写，代表全局匹配，匹配出所有满足条件的结果，不加g的话第一次匹配成功后正则对象就停止继续匹配。i:ignore缩写，代表忽略大小写，正则匹配时默认区分大小写，加了i就自动忽略字符串大小写


```js
var t='AklmanAhmatbeK';

var reg1=/akl/;
var reg2='akl/i;
console.log(reg1.test(t));//false
console.log(reg1.test(t));//true
```
### 2.3 正则对象的方法

对象.test(str):判断字符串是否具有指定模式的子串，返回结果是一个布尔值；

对象.exec(str):返回字符串指定模式的子串，一次只能获取一个与匹配的结果；

```html
<body>
    <input type="text" id="inp">
    <input type="button" id="btu" value="匹配">
</body>
<script>
    var btu=document.querySelector('#btu');
    btu.onclick=function(){
        var t=document.querySelector('#inp').value;
        var reg=/\d\d\d/;
        console.log(reg.test(t));
        console.log(reg.exec(t));
    }
</script>
```
### 2.4 String对象的方法

search(reg):与indexOf非常相似，返回指定模式的子串在字符串首次穿线的位置。

match(reg):以数组的形式返回指定模式的字符串，可以返回所有匹配的结果。

replace(reg,'将替换的字符'):把指定模式的子串进行替换操作。

split(reg):以指定模式分割字符串，返回结果为数组。

```html
<body>
    <input type="text" id="inp">
    <input type="button" id="btu" value="匹配">
</body>
<script>
    var btu=document.querySelector('#btu');
    btu.onclick=function(){
        var t=document.querySelector('#inp').value;
        var reg=/\d\d\d/;
        console.log(t.search(reg));//返回第一次出现符合的字符串的位置，从0开始计
        console.log(t.match(reg));//数组形式返回符合规则的第一单字符串，使用g则返回全部匹配结果
        console.log(t.replace(reg,'***'));//替换符合规则的第一个字符串，使用g则全全部符合条件的都替换
        console.log(t.split(reg));//以规则为分割标志，分割整个字符串，艺术组形式返回分割结果
    }
</script>
```
## 第三章 要牢记住的概念

子表达式：在正则表达式中，通过一对圆括号起来的内容，被称之为“子表达式”；var reg=/d(d)d/gi;

捕获：在正则表达式中，子表达式匹配到相应的内容时，系统会自动捕获这个行为，然后将子表达式匹配到的内容放入系统的缓存中，这过程就是“捕获”。


![][0]

反向引用：在正则表达式中，可以使用n(n>0,正整数，代表系统中的缓存区编号)来获取缓冲区中的内容，这个过程就是“反向引用”。

![][1]

为什么牢记，有什么用？看代码：查找连续的相同的数字或者内容是用

```js
var t = 'l93ajkdlfja12928jlkfda324';

//子表达式
//只有字表达是中匹配的内容才保存到缓存，这种行为叫捕获。

var reg=/\d(\d)(\d)/;//查找连续的三位纯数字，找到第一个就不找了

console.log(t.match(reg));

var reg1 = /\d(\d)(\d)\1/;//查找连续的四位纯数字，其中第四个数字与第二个数字必须一样，简称反向匹配

console.log(t.match(reg1));

var tel='11223344557799123ew13';
var reg2=/(\d)\1(\d)\2/;//第一个跟第二个一样，第三个跟第四个一样
console.log(tel.match(reg2));

var tel1='13414512211431341';
var reg3=/(\d)(\d)\2\1/;//1221
console.log(tel1.match(reg3));
/**
*例1：查找字符，如：AABB,TTMM
*（提示：在正则表达式中，通过[A-Z]匹配A-Z中的任一字符）
*答：var reg = /([A-Z])\1( [A-Z])\2/g;
**/
/*例2：查找连续相同的四个数字或四个字符
*（提示：在正则表达式中，通过[0-9a-z]）
*答：var reg = /([0-9a-z])\1\1\1/gi;
**/
```
## 第四章 正则表达式应用
### 4.1 正则表达式的组成

正则表达式是由普通字符(例如字符a到z)以及特殊字符(成为元字符)组成的文字模式。正则表达式作为一个模板，将某个字符模式与所搜索的字符串进行匹配。正则表达式三步走：1.查什么？2.查多少？3.从哪查？
### 4.2 匹配符（查什么）

匹配符：字符匹配符用于匹配某个或某些字符；例如d就是匹配0-9的数字。

在正则表达式中，通过一对中括号起来的内容，称之为"字符簇"。字符簇代表的是一个范围，但是匹配时，只能匹配某个范围中的固定的结果。

| 字符簇 | 含义 |
|-|-|
| [a-z] | 匹配字符a到字符z之间的任一字符 |
| [A-Z] | 匹配字符A到字符Z之间的任一字符 |
| [0-9] | 匹配数字0到9之间的任一数字 |
| [0-9a-z] | 匹配数字0到9或者字符a到z之间的任一字符 |
| [0-9a-zA-Z] | 匹配数字0到9或字符a到字符z之或者字符A到字符Z之间的任一字符 |
| [abcd] | 匹配字符a或者字符b或者字符c或者字符d |
| [1234] | 匹配字符1或者字符2或者字符3或者字符4 |


在字符簇中，通过一个^(脱字符)来表示取反的含义：

| 字符簇 | 含义 |
|-|-|
| <sup id="fnref-1">[1][2]</sup> | 匹配除字符a到字符z之外的任一字符 |
| <sup id="fnref-2">[2][3]</sup> | 匹配除字符a，b,c,d外的字符 |
| <sup id="fnref-3">[3][4]</sup> | 匹配除数字0到9之外的任一字符 |


常用的比较特殊的匹配符：

| 字符簇 | 含义 |
|-|-|
| d | 匹配一个数字字符，与使用[0-9]等价 |
| D | 匹配一个非数字字符，还可以使用[脱字符0-9] |
| w | 匹配包括下划线的任何字母数字下划线字符，还可以使用[0-9a-zA-Z] |
| W | 匹配任何非字母数字下划线字符，还可以[脱字符w] |
| s | 匹配任何空白字符 |
| S | 匹配任何非空白字符，还可以使用[脱字符s] |
| .(是个点) | 匹配除"n"之外的任何单个字符 |
| [u4e00-u9fa5] | 匹配中文字符中的任一字符 |


### 4.3 限定符（查多少）

限定符可以指定正则表达式的一个给定字符必须要出现多少次才能满足匹配。

| 字符簇 | 含义 |
|-|-|
| * | 匹配前面的子表达式零次或多次，0到多 |
| + | 匹配前面的子表达式一次或多次，1到多 |
| ？ | 匹配前面的子表达式零次或一次，0或1 |
| {n} | 匹配确定的 n 次 |
| {n,} | 至少匹配n 次 |
| {n,m} | 最少匹配 n 次且最多匹配 m 次 |


对qq号码进行校验要求5-10位，不能以0开头，只能是数字

```js
var str='我的QQ20869921366666666666,nsd你的是6726832618吗?';
// var reg=/\d{3,7}/;

var reg=/[1-9]\d{4,10}/g;//正则表达式默认情况下使用贪婪模式，在满足条件的前提下尽量多的查找匹配
console.log(str.match(reg));

var reg1=/[1-9]\d{4,10}?/;//非贪婪模式，惰性模式：在满足条件的前提下，尽量的少查找匹配
console.log(str.match(reg1));

//？跟在表达式后面是限定符{0,1}
//?跟在限定符后面，使用惰性模式（非贪婪模式）
var reg2=/[1-9]\d??/;
//reg2和reg3是一个意思
var reg3=/[1-9]\d{0,1}?/;
console.log(str.match(reg2));
console.log(str.match(reg3));
```

正则表达式中默认情况下能匹配多就不会匹配少，这种匹配模式就是 **`贪婪模式(贪婪匹配)`** 。

如果想少匹配或者限定的位数匹配则使用 **`非贪婪模式`** ，也就是优先匹配满足条件情况下的优先匹配少的，这种 **`非贪婪模式也被称为惰性匹配`** 。
### 4.4 定位符（从哪查）

正则表达式只会到字符串去寻找是否有与之匹配的内容，如果有就默认是正确的，就不会考虑其字符串本身是否 合法的。因此必须使用 **`定位符`** 来将一个正则表达式固定在一行的开始或者结束；也可以创建只在单词内或者只在单词的开始或结尾处出现的正则表达式。

| 字符簇 | 含义 |
|-|-|
| ^ | 匹配输入字符串的开始位置 |
| $ | 匹配输入字符串的结束位置 |
| b | 匹配一个单词边界 |
| B | 匹配非单词边界 |


注意：脱字符放在字符簇中是取反的意思，放在整个表达式中是开始位置的。
问题1：用正则表达式，匹配字符串"lsd15309873475"中的的手机号

```js
/**分析
*手机号第一位必须是1，第二位必须是3-8之间的数字，第三位到第十一位是只要0-9之间的数字即可
**/
var str='lsd15309873475';
var reg1=/1[345678]\d{9}/;
console.log(str.match(reg1));//给5分

var reg2=/^1[34578]\d{9}$/;
console.log(str.match(reg2));//给满分
```

问题2：

```js
var str = 'i am zhangsan';
//an必须是一个完整的单词
var reg = /\ban\b/;
console.log(str.match(reg));
//an不能是单词的开始，只能是单词的结束
var reg1 = /\Ban\b/;
console.log(str.match(reg1));
```
### 4.5 转义字符

因为在正则表达式中 .(点) +  等是属于表达式的一部分，但是我们在匹配时，字符串中也需要匹配
这些特殊字符，所以，我们必须使用 反斜杠  对某些特殊字符进行转义；

| 需要转义的字符 |
|-|
| 点. |
| 小括号() |
| 中括号[] |
| 左斜杠/ |
| 右斜杠\ |
| 选择匹配符\ | |


问题1：匹配一个合法的网址URL：

```js
var str='http://aklman.com';
//对于. /都必须转义匹配
var reg=/\w+:\/\/\w+\.\w+/;
console.log(str.match(reg));//http://aklman.com
```

问题2：使用正则表达式验证邮箱是否合法

```js
var str='ak@aklman.com';
var reg=/\w+@[0-9a-z]+(\.[0-9a-z]{2,6})+/;
console.log(str.match(reg));
```
### 4.6 或者的用法

查找所有的苹果产品：

```js
var s='ipad ipod iphone imac itouch iamshuai';

var reg=/\bi(pad|pod|phone|mac|touch)\b/g;

console.log(s.match(reg));
```
### 4.7 预查
#### 4.7.1 正向预查，正预测，前瞻，先行断言

问题1：请把ing结尾的单词的词根部分(即不含ing部分)找出来:

```js
var str='sorry,when i am working,do not calling me!';
//只是为了找到ing结尾的单词，但并不是词根部分
var reg1=/\b\w+ing\b/g;//["working", "calling"]

var reg2=/\b\w+(?=ing\b)/g;//["work", "call"]
console.log(str.match(reg1));
console.log(str.match(reg2));
```
#### 4.7.2 负向预查，负预测，前瞻，先行否言

问题2：把不是ing结尾的单词找出来

```js
var str='sorry,when i am working,do not calling me!';
var reg=/\b\w+(?!ing)\w{3}\b/g;//负向预查、负预测、前瞻
console.log(str.match(reg));//["sorry", "when"]
```
## 补充知识点

| 量词 | 含义 |
|-|-|
| n+ | 匹配任何包含至少一个 n 的字符串 |
| n* | 匹配任何包含零个或多个 n 的字符串 |
| n? | 匹配任何包含零个或一个 n 的字符串 |
| n{x} | 匹配包含 X 个 n 的序列的字符串 |
| n{x,y} | 匹配包含 X 至 Y 个 n 的序列的字符串 |
| n{x,} | 匹配包含至少 X 个 n 的序列的字符串 |
| n$ | 匹配任何结尾为 n 的字符串 |
| ^n | 匹配任何开头为 n 的字符串 |
| ?=n | 匹配任何其后紧接指定字符串 n 的字符串 |
| ?!n | 匹配任何其后没有紧接指定字符串 n 的字符串 |


## 参考资料

[W3school][5]
[菜鸟教程][6]
[JavaScript权威指南（第6版）][7]


-----


<li id="fn-1">a-z [↩][8]

<li id="fn-2">abcd [↩][9]

<li id="fn-3">0-9 [↩][10]



[2]: #fn-1
[3]: #fn-2
[4]: #fn-3
[5]: http://www.w3school.com.cn/jsref/jsref_obj_regexp.aspp
[6]: http://www.runoob.com/js/js-obj-regexp.html
[7]: http://item.jd.com/10974436.html
[8]: #fnref-1
[9]: #fnref-2
[10]: #fnref-3
[0]: ./img/bV5HFa.png
[1]: ./img/bV5HFe.png


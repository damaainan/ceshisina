俗话说：有好米还要有好锅。正则表达式要真正发挥作用，要倚仗一些操作正则的方法。

咱们来看看JavaScript中都有哪些操作正则的方法。

## RegExp

`RegExp`是正则表达式的构造函数。

使用构造函数创建正则表达式有多种写法：

```javascript
new RegExp('abc');
// /abc/
new RegExp('abc', 'gi');
// /abc/gi
new RegExp(/abc/gi);
// /abc/gi
new RegExp(/abc/m, 'gi');
// /abc/gi
```

它接受两个参数：第一个参数是匹配模式，可以是字符串也可以是正则表达式；第二个参数是修饰符。

如果第一个参数的正则表达式定义了修饰符，第二个参数又有值，则以第二个参数定义的修饰符为准，这是ES2015的新特性。

使用构造函数一般用于需要动态构造正则表达式的场景，性能不如字面量写法。

我们来看看它的实例属性：

- lastIndex属性。它的作用是全局匹配时标记下一次匹配开始的位置，全局匹配的抓手就是它。
- source属性。它的作用是存储正则模式的主体。比如`/abc/gi`中的`abc`。
- 对应的修饰符属性。目前有`global`、`ignoreCase`、`multiline`、`sticky`、`dotAll`、`unicode`属性，返回布尔值表明是否开启对应修饰符。
- flags属性。返回所有的修饰符。

## match

`match`是String实例方法。

它的作用是根据参数返回匹配结果，取名match也是非常恰当了。

它接受一个正则表达式作为唯一参数。

可是字符串也可以作为参数怎么解释？

```javascript
'abc'.match('b');
// ["b", index: 1, input: "abc", groups: undefined]
```

这是因为match方法内部会隐式的调用`new RegExp()`将其转换成一个正则实例。

match方法的返回值可以分为三种情况。

#### 匹配失败

没什么可说的，返回`null`。

#### 非全局匹配

返回一个数组。

数组的第一项是匹配结果。如果不传参则匹配结果为空字符串。

```javascript
'abc'.match();
// ["", index: 0, input: "abc", groups: undefined]
```

如果正则参数中有捕获组，捕获的结果在数组中从第二项开始依次排列。有捕获组但是没有捕获内容则显示`undefined`。

```javascript
'@abc2018'.match(/@([a-z]+)([A-Z]+)?/);
// ["@abc", "abc", undefined, index: 0, input: "@abc2018", groups: undefined]
```

数组有一个`index`属性，标明匹配结果在文本中的起始位置。

数组有一个`input`属性，显示源文本。

数组有一个`groups`属性，它存储的不是捕获组的信息，而是捕获命名的信息。

```javascript
'@abc2018'.match(/@(?<lowerCase>[a-z]+)(?<upperCase>[A-Z]+)?/);
// ["@abc", "abc", undefined, index: 0, input: "@abc2018", groups: { lowerCase: "abc", upperCase: undefined }]
```

#### 全局匹配

返回一个数组。

捕获的若干结果在数组中依次排列。因为要返回所有匹配的结果，其他的信息，包括捕获组和若干属性就无法列出了。

```javascript
'abc&mno&xyz'.match(/[a-z]+/g);
// ["abc", "mno", "xyz"]
```

## replace

`replace`是String实例方法。

它的作用是将给定字符串替换匹配结果，并返回新的替换后的文本。源文本不会改变。

它接受两个参数。

第一个参数可以是字符串或者正则表达式，它的作用是匹配。

参数是字符串和参数是正则表达式的区别在于：正则表达式的表达能力更强，而且可以全局匹配。因此参数是字符串的话只能进行一次替换。

```javascript
'abc-xyz-abc'.replace('abc', 'biu');
// "biu-xyz-abc"
'abc-xyz-abc'.replace(/abc/, 'biu');
// "biu-xyz-abc"
'abc-xyz-abc'.replace(/abc/g, 'biu');
// "biu-xyz-biu"
```

第二个参数可以是字符串或者函数，它的作用是替换。

#### 第二个参数是字符串

replace方法为第二个参数是字符串的方式提供了一些特殊的变量，能满足一般需求。

`$数字`代表相应顺序的捕获组。注意，虽然它是一个变量，但是不要写成模板字符串\`${$1}biu\`，replace内部逻辑会自动解析字符串，提取出变量。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '$1biu');
// "@biu-xyz-$biu"
```

`$&`代表匹配结果。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '{$&}');
// "{@abc}-xyz-{$abc}"
```

$\`代表匹配结果左边的文本。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '{$`}');
// "{}-xyz-{@abc-xyz-}"
```

`$'`代表匹配结果右边的文本。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, "{$'}");
// "{-xyz-$abc}-xyz-{}"
```

有些时候我要的是变量的符号本身，而不是它的变量值，怎么办？加一个`$`转义一下。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '$$1biu');
// "$1biu-xyz-$1biu"
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '$biu');
// "$biu-xyz-$biu"
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, '$$biu');
// "$biu-xyz-$biu"
```

在不会造成误会的场景，一个`$`和两个`$`的效果都是一个`$`，因为另一个充当转义符号。会造成误会的场景，那就必须加`$`转义了。

#### 第二个参数是函数

字符串的变量毕竟只能引用，无法操作。与之相对，函数的表达能力就强多了。

函数的返回值就是要替换的内容。函数如果没有返回值，默认返回`undefined`，所以替换内容就是`undefined`。

函数的第一个参数，是匹配结果。

```javascript
'abc-xyz-abc'.replace(/abc/g, (match) => `{${match}}`);
// "{abc}-xyz-{abc}"
'abc-xyz-abc'.replace(/abc/g, (match) => {});
// "undefined-xyz-undefined"
```

如果有捕获组，函数的后顺位参数与捕获组一一对应。

```javascript
'@abc3-xyz-$abc5'.replace(/([^-]+)abc(\d+)/g, (match, $1, $2) => `{${$1}${match}${$2}}`);
// "{@@abc33}-xyz-{$$abc55}"
```

倒数第二个参数是匹配结果在文本中的位置。

```javascript
'@abc-xyz-$abc'.replace(/([^-]+)abc/g, (match, $1, index) => `{${match}是位置是${index}}`);
// "{@abc是位置是0}-xyz-{$abc是位置是9}"
```

倒数第一个参数是源文本。

```javascript
'abc-xyz'.replace(/abc/g, (match, index, string) => `{{${match}}属于{${string}}}`);
// "{{abc}属于{abc-xyz}}-xyz"
```

replace方法最常用的地方是转义HTML标签。

```javascript
'<p>hello regex</p>'.replace(/</g, '&lt;').replace(/>/g, '&gt;');
// "&lt;p&gt;hello regex&lt;/p&gt;"
```

## search

`search`是String实例方法。

它的作用是找出首次匹配项的索引。它的功能较单一，性能也更好。

它接受一个正则表达式作为唯一参数。与match一样，如果传入一个非正则表达式，它会调用`new RegExp()`将其转换成一个正则实例。

```javascript
'abc-xyz-abc'.search(/xyz/);
// 4
'abc-xyz-abc'.search(/xyz/g);
// 4
'abc-xyz-abc'.search(/mno/);
// -1
'abc-xyz-abc'.search();
// 0
'abc-xyz-abc'.search(/abc/);
// 0
```

因为只能返回首次匹配的位置，所以全局匹配对它无效。

如果匹配失败，返回`-1`。

## split

`split`是String实例方法。

它的作用是根据传入的分隔符切割源文本。它返回一个由被切割单元组成的数组。

它接受两个参数。第一个参数可以是字符串或者正则表达式，它是分隔符；第二个参数可选，限制返回数组的最大长度。

```javascript
'abc-def_mno+xyz'.split();
// ["abc-def_mno+xyz"]
'abc-def_mno+xyz'.split('-_+');
// ["abc-def_mno+xyz"]
'abc-def_mno+xyz'.split('');
// ["a", "b", "c", "-", "d", "e", "f", "_", "m", "n", "o", "+", "x", "y", "z"]
'abc-def_mno+xyz'.split(/[-_+]/);
// ["abc", "def", "mno", "xyz"]
'abc-def_mno+xyz'.split(/[-_+]/g);
// ["abc", "def", "mno", "xyz"]
'abc-def_mno+xyz'.split(/[-_+]/, 3);
// ["abc", "def", "mno"]
'abc-def_mno+xyz'.split(/[-_+]/, 5);
// ["abc", "def", "mno", "xyz"]
```

如果第一个参数传入的是空字符串，则会切割每一个字符串。

另外，因为split方法中的正则是用来匹配分隔符，所以全局匹配没有意义。

## exec

`exec`是RegExp实例方法。

它的作用是根据参数返回匹配结果，与字符串方法match相似。

```javascript
/xyz/.exec('abc-xyz-abc');
// ["xyz", index: 4, input: "abc-xyz-abc", groups: undefined]
/mno/.exec('abc-xyz-abc');
// null
/xyz/.exec();
// null
```

小小的区别在于参数为空的情况：exec直接返回`null`；match返回一个空字符串数组。原因也很好理解，有鱼没有网，最坏也就是没有收成；有网没有鱼，那可是连奔头都没有了。

它们俩最大的区别在于全局匹配的场景。

全局匹配就意味着多次匹配，RegExp实例有一个`lastIndex`属性，每匹配一次，这个属性就会更新为下一次匹配开始的位置。exec就是根据这个属性来实现全局匹配的。

```javascript
const reg = /abc/g;
reg.lastIndex
// 0
reg.exec('abc-xyz-abc');
// ["abc", index: 0, input: "abc-xyz-abc", groups: undefined]
reg.lastIndex
// 3
reg.exec('abc-xyz-abc');
// ["abc", index: 8, input: "abc-xyz-abc", groups: undefined]
reg.lastIndex
// 11
reg.exec('abc-xyz-abc');
// null
reg.lastIndex
// 0
reg.exec('abc-xyz-abc');
// ["abc", index: 0, input: "abc-xyz-abc", groups: undefined]
```

如果有多个匹配结果，多次执行就能获得所有的匹配结果。所以exec一般用在循环语句中。

有两点需要特别注意：

- 因为`lastIndex`会不断更新，最终又会归于0，所以这个匹配过程是可以无限重复的。
- `lastIndex`属性是属于正则实例的。只有同一个实例的`lastIndex`才会不断更新。

知道第二点意味着什么吗？

```javascript
/abc/g.exec('abc-xyz-abc');
// ["abc", index: 0, input: "abc-xyz-abc", groups: undefined]
/abc/g.exec('abc-xyz-abc');
// ["abc", index: 0, input: "abc-xyz-abc", groups: undefined]
/abc/g.exec('abc-xyz-abc');
// ["abc", index: 0, input: "abc-xyz-abc", groups: undefined]
// ...
```

如果不把正则提取出来，获得它的引用，exec方法就一直在原地打转，因为每次都是一个新的正则实例，每次`lastIndex`都要从0开始。

## test

`test`是RegExp实例方法。

它的作用是找出源文本是否有匹配项，与字符串方法search相似。多用于表单验证中。

```javascript
/abc/.test('abc-xyz-abc');
// true
/mno/.test('abc-xyz-abc');
// false
/abc/.test();
// false
```

test方法与search方法的区别主要体现在两点：

- search方法返回的是索引，test方法只返回布尔值。
- 因为是正则实例方法，全局匹配时也会更新正则实例的`lastIndex`属性，所以也可以多次执行。

```javascript
const reg = /abc/g;
reg.lastIndex
// 0
reg.test('abc-xyz-abc');
// true
reg.lastIndex
// 3
reg.test('abc-xyz-abc');
// true
reg.lastIndex
// 11
reg.test('abc-xyz-abc');
// false
reg.lastIndex
// 0
reg.test('abc-xyz-abc');
// true
```

## 修改字符串方法的底层实现

我们也看到了，一部分处理正则的方法定义在String实例上，一部分处理正则的方法定义在RegExp实例上。为了将处理正则的方法全部统一到RegExp实例上，ES2015修改了部分字符串方法的底层实现。

具体来说，ES2015为RegExp实例新增了四个方法，字符串方法`match`、`replace`、`search`、`split`内部调用已经改成了相应的RegExp实例方法。

```javascript
RegExp.prototype[Symbol.match]
RegExp.prototype[Symbol.replace]
RegExp.prototype[Symbol.search]
RegExp.prototype[Symbol.split]
```

`Symbol.match`是什么？`Symbol`是新增的一种基础数据类型，它有11个内置的值，指向语言内部使用的方法。

`RegExp.prototype[Symbol.match]`在使用上和`match`相比，调用者和参数翻转一下就可以了。

```javascript
'abc-mno-xyz'.match(/mno/);
// ["mno", index: 4, input: "abc-mno-xyz", groups: undefined]
/mno/[Symbol.match]('abc-mno-xyz');
// ["mno", index: 4, input: "abc-mno-xyz", groups: undefined]
```
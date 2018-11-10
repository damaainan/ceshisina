> 名余曰正则兮，字余曰灵均。

`Regular Expressions`翻译成中文叫正则表达式。也不知道是谁翻译过来的，听起来就很严肃。似乎翻译成`通用表达式`更能传达其精髓，如果你不怕梦见屈原的话。

为什么叫通用表达式？因为它有一套和编程语言无关的文本匹配规则。很多语言都实现了正则表达式的文本匹配引擎，只不过在功能集合上略有不同。

我们要记住的是三点：

其一，正则表达式是用来提取文本的。

其二，正则表达式的表达能力强大到令人发指。

其三，正则表达式的语法对初学者不友好。

另外，本专题只涉及JavaScript语言的正则表达式，其他语言的规则可能略有不同。

我还为各位读者准备了一副宣传语，应该能让你心动(点赞)吧？

> 学一门前端工具，几年就过时了。学正则表达式，受用一辈子。

## 普通字符

什么叫普通字符？

当我们写`a`的时候，我们指的就是`a`；当我们写`爱`的时候，我们指的就是`爱`。

```javascript
'hello 😀 regex'.match(/😀/);
// ["😀", index: 6, input: "hello 😀 regex", groups: undefined]
```

这就是普通字符，它在正则中的含义就是检索它本身。除了正则规定的部分字符外，其余的都是普通字符，包括各种人类语言，包括emoji，只要能够表达为字符串。

## 开始与结束

`^`字符的英文是`caret`，翻译成中文是`脱字符`。不要问我，又不是我翻译的。它在正则中属于元字符，通常代表的意义是文本的开始。说通常是因为当它在字符组中`[^abc]`另有含义。

什么叫文本的开始？就是如果它是正则主体的第一个符号，那紧跟着它的字符必须是被匹配文本的第一个字符。

```javascript
'regex'.match(/^r/);
// ["r", index: 0, input: "regex", groups: undefined]
```

问题来了，如果`^`不是正则的第一个符号呢？

```javascript
'regex'.match(/a^r/);
// null
```

所以呀，关于它有三点需要注意：

- 作为匹配文本开始元字符的时候必须是正则主体的第一个符号，否则正则无效。
- 它匹配的是一个位置，而不是具体的文本。
- 它在其他规则中有另外的含义。

`$`字符与`^`正好相反。它代表文本的结束，并且没有其他含义(其实是有的，但不是在正则主体内)。同样，它必须是正则主体的最后一个符号。

```javascript
'regex'.match(/x$/);
// ["x", index: 4, input: "regex", groups: undefined]
```

`^`与`$`特殊的地方在于它匹配的是一个位置。位置不像字符，它看不见，所以更不容易理解。

## 转义

我们现在已经知道`$`匹配文本的结束位置，它是元字符。但是如果我想匹配`$`本身呢？匹配一个美元符号的需求再常见不过了吧。所以我们得将它贬为庶民。

`\`反斜杠就是干这个的。

```javascript
'price: $3.6'.match(/\$[0-9]+\.[0-9]+$/);
// ["$3.6", index: 7, input: "price: $3.6", groups: undefined]
```

上面的例子有点超纲了，超纲的部分先不管。

你可以认为`\`也是一个元字符，它跟在另一个元字符后面，就能还原它本来的含义。

如果有两个`\`呢？那就是转义自身了。如果有三个`\`呢？我们得分成两段去理解。以此类推。

普通字符前面跟了一个`\`是什么效果？首先它们是一个整体，然后普通字符转义后还是普通字符。

## 带反斜杠的元字符

一般来说，普通字符前面带反斜杠还是普通字符，但是有一些普通字符，带反斜杠后反而变成了元字符。

要怪只能怪计算机领域的常用符号太少了。

| 元字符 | 含义 |
| ------ | ------ |
| \b | 匹配一个单词边界(boundary) |
| \B | 匹配一个非单词边界 |
| \d | 匹配一个数字字符(digit) |
| \D | 匹配一个非数字字符 |
| \s | 匹配一个空白字符(space) |
| \S | 匹配一个非空白字符 |
| \w | 匹配一个字母或者一个数字或者一个下划线(word) |
| \W | 匹配一个字母、数字和下划线之外的字符 |

你这么聪明，肯定一眼就看出来，大写代表反义。对，就是这么好记。

#### \b元字符

`\b`匹配的也是一个位置，而不是一个字符。单词和空格之间的位置，就是所谓单词边界。

```javascript
'hello regex'.match(/\bregex$/);
// ["regex", index: 6, input: "hello regex", groups: undefined]
'hello regex'.match(/\Bregex$/);
// null
```

所谓单词边界，对中文等其他语言是无效的。

```javascript
'jiangshuying gaoyuanyuan huosiyan'.match(/\bgaoyuanyuan\b/);
// ["gaoyuanyuan", index: 13, input: "jiangshuying gaoyuanyuan huosiyan", groups: undefined]
'江疏影 高圆圆 霍思燕'.match(/\b高圆圆\b/);
// null
```

所以`\b`翻译一下就是`^\w|\w$|\W\w|\w\W`。

#### \d元字符

`\d`匹配一个数字，注意，这里的数字不是指JavaScript中的数字类型，因为文本全是字符串。它指的是代表数字的字符。

```javascript
'123'.match(/\d/);
// ["1", index: 0, input: "123", groups: undefined]
```

#### \s元字符

`\s`匹配一个空白字符。

这里需要解释一下什么是空白字符。

空白字符不是空格，它是空格的超集。很多人说它是`\f\n\r\t\v`的总和，其中`\f`是换页符，`\n`是换行符，`\r`是回车符，`\t`是水平制表符，`\v`是垂直制表符。是这样么？

```javascript
'a b'.match(/\w\s\w/);
// ["a b", index: 0, input: "a b", groups: undefined]
'a b'.match(/\w\f\w/);
// null
'a b'.match(/\w\n\w/);
// null
'a b'.match(/\w\r\w/);
// null
'a b'.match(/\w\t\w/);
// null
'a b'.match(/\w\v\w/);
// null
'a b'.match(/\w \w/);
// ["a b", index: 0, input: "a b", groups: undefined]
```

这样说的人，明显是没有做过实验。其实正确的写法是`空格\f\n\r\t\v`的总和，集合里面包含一个空格，可千万别忽略了。诶，难道空格在正则中的写法就是`空一格`么，是的，就是这样随意。

这个集合中很多都是不可打印字符，估计只有`\n`是我们的老朋友。所以，如果不需要区分空格和换行的话，那就大胆的用`\s`吧。

#### \w元字符

`\w`匹配一个字母或者一个数字或者一个下划线。为什么要将它们放一起？想一想JavaScript中的变量规则，包括很多应用的用户名都只能是这三样，所以把它们放一起挺方便的。

不过要注意，字母指的是26个英文字母，其他的不行。

```javascript
'正则'.match(/\w/);
// null
```

#### 负阴抱阳

如果我们将大写和小写的带反斜杠的元字符组合在一起，就能匹配任何字符。是的，不针对任何人。

```javascript
'@regex'.match(/[\s\S]/);
// ["@", index: 0, input: "@regex", groups: undefined]
```

方括号的含义我们先按下不表。

## 道生一

`.`在正则中的含义仙风道骨，它匹配换行符之外的任意单个字符。

如果文本不存在换行符，那么`.`和`[\b\B]`和`[\d\D]`和`[\s\S]`和`[\w\W]`是等价的。

如果文本存在换行符，那么`(.|\n)`和`[\b\B]`和`[\d\D]`和`[\s\S]`和`[\w\W]`是等价的。

```javascript
'@regex'.match(/./);
// ["@", index: 0, input: "@regex", groups: undefined]
```

## 量词

前面我们一直在强调，一个元字符只匹配一个字符。即便强大如`.`它也只能匹配一个。

那匹配`gooooogle`的正则是不是得写成`/gooooogle/`呢？

正则冷笑，并向你发射一个蔑视。

如果匹配的模式有重复，我们可以声明它重复的次数。

| 量词 | 含义 |
| ------ | ------ |
| ? | 重复零次或者一次 |
| + | 重复一次或者多次，也就是至少一次 |
| * | 重复零次或者多次，也就是任意次数 |
| {n} | 重复n次 |
| {n,} | 重复n次或者更多次 |
| {n,m} | 重复n次到m次之间的次数，包含n次和m次 |

有三点需要注意：

- `?`在诸如匹配http协议的时候非常有用，就像这样：`/http(s)?/`。它在正则中除了是量词还有别的含义，后面会提到。

- 我们习惯用`/.*/`来匹配若干对我们没有价值的文本，它的含义是`若干除换行符之外的字符`。比如我们需要文本两头的格式化信息，中间是什么无所谓，它就派上用场了。不过它的性能可不好。

- `{n,m}`之间不能有空格，空格在正则中是有含义的。

关于量词最令人困惑的是：它重复什么？

它重复紧贴在它前面的某个集合。第一点，必须是紧贴在它前面；第二点，重复一个集合。最常见的集合就是一个字符，当然正则中有一些元字符能够将若干字符变成一个集合，后面会讲到。

```javascript
'gooooogle'.match(/go{2,5}gle/);
// ["gooooogle", index: 0, input: "gooooogle", groups: undefined]
```

如果一个量词紧贴在另一个量词后面会怎样？

```javascript
'gooooogle'.match(/go{2,5}+gle/);
// Uncaught SyntaxError: Invalid regular expression: /go{2,5}+gle/: Nothing to repeat
```

## 贪婪模式与非贪婪模式

前面提到量词不能紧跟在另一个量词后面，马上要👋👋打脸了。

```javascript
'https'.match(/http(s)?/);
// ["https", "s", index: 0, input: "https", groups: undefined]
'https'.match(/http(s)??/);
// ["http", undefined, index: 0, input: "https", groups: undefined]
```

然而，我的脸是这么好打的？

紧跟在`?`后面的`?`它不是一个量词，而是一个模式切换符，从贪婪模式切换到非贪婪模式。

贪婪模式在正则中是默认的模式，就是在既定规则之下匹配尽可能多的文本。因为正则中有量词，它的重复次数可能是一个区间，这就有了取舍。

紧跟在量词之后加上`?`就可以开启非贪婪模式。怎么省事怎么来。

这里的要点是，`?`必须紧跟着量词，否则的话它自己就变成量词了。

## 字符组

正则中的普通字符只能匹配它自己。如果我要匹配一个普通字符，但是我不确定它是什么，怎么办？

```javascript
'grey or gray'.match(/gr[ae]y/);
// ["grey", index: 0, input: "grey or gray", groups: undefined]
```

方括号在正则中表示一个区间，我们称它为字符组。

首先，字符组中的字符集合只是所有的可选项，最终它只能匹配一个字符。

然后，字符组是一个独立的世界，元字符不需要转义。

```javascript
'$'.match(/[$&@]/);
// ["$", index: 0, input: "$", groups: undefined]
```

最后，有两个字符在字符组中有特殊含义。

`^`在字符组中表示取反，不再是文本开始的位置了。

```javascript
'regex'.match(/[^abc]/);
// ["r", index: 0, input: "regex", groups: undefined]
```

如果我就要`^`呢？前面已经讲过了，转义。

`-`本来是一个普通字符，在字符组中摇身一变成为连字符。

```javascript
'13'.match(/[1-9]3/);
// ["13", index: 0, input: "13", groups: undefined]
```

连字符的意思是匹配范围在它的左边字符和右边字符之间。

如果我这样呢？

```javascript
'abc-3'.match(/[0-z]/);
// ["a", index: 0, input: "abc-3", groups: undefined]
```

```javascript
'xyz-3'.match(/[0-c]/);
// ["3", index: 4, input: "xyz-3", groups: undefined]
```

```javascript
'xyz-3'.match(/[0-$]/);
// Uncaught SyntaxError: Invalid regular expression: /[0-$]/: Range out of order in character class
```

发现什么了没有？只有两种字符是可以用连字符的：英文字母和数字。而且英文字母可以和数字连起来，英文字母的顺序在后面。这和扑克牌`1 2 3 4 5 6 7 8 9 10 J Q K`是一个道理。

## 捕获组与非捕获组

我们已经知道量词是怎么回事了，我们也知道量词只能重复紧贴在它前面的字符。

如果我要重复的是一串字符呢？

```javascript
'i love you very very very much'.match(/i love you very +much/);
// null
'i love you very very very much'.match(/i love you v+e+r+y+ +much/);
// null
```

这样肯定是不行的。是时候请圆括号出山了。

```javascript
'i love you very very very much'.match(/i love you (very )+much/);
// ["i love you very very very much", "very ", index: 0, input: "i love you very very very much", groups: undefined]
```

圆括号的意思是将它其中的字符集合打包成一个整体，然后量词就可以操作这个整体了。这和方括号的效果是完全不一样的。

而且默认的，圆括号的匹配结果是可以捕获的。

#### 正则内捕获

现在我们有一个需求，匹配`<div>`标签。

```javascript
'<div>hello regex</div>'.match(/<div>.*<\/div>/);
// ["<div>hello regex</div>", index: 0, input: "<div>hello regex</div>", groups: undefined]
```

这很简单。但如果我要匹配的是任意标签，包括自定义的标签呢？

```javascript
'<App>hello regex</App>'.match(/<([a-zA-Z]+)>.*<\/\1>/);
// ["<App>hello regex</App>", "App", index: 0, input: "<App>hello regex</App>", groups: undefined]
```

这时候就要用到正则的捕获特性。正则内捕获使用`\数字`的形式，分别对应前面的圆括号捕获的内容。这种捕获的引用也叫**反向引用**。

我们来看一个更复杂的情况：

```javascript
'<App>hello regex</App><p>A</p><p>hello regex</p>'.match(/<((A|a)pp)>(hello regex)+<\/\1><p>\2<\/p><p>\3<\/p>/);
// ["<App>hello regex</App><p>A</p><p>hello regex</p>", "App", "A", "hello regex", index: 0, input: "<App>hello regex</App><p>A</p><p>hello regex</p>", groups: undefined]
```

如果有嵌套的圆括号，那么捕获的引用是先递归的，然后才是下一个顶级捕获。

#### 正则外捕获

```javascript
'@abc'.match(/@(abc)/);
// ["@abc", "abc", index: 0, input: "@abc", groups: undefined]
RegExp.$1;
// "abc"
```

没错，`RegExp`就是构造正则的构造函数。如果有捕获组，它的实例属性`$数字`会显示对应的引用。

如果有多个正则呢？

```javascript
'@abc'.match(/@(abc)/);
// ["@abc", "abc", index: 0, input: "@abc", groups: undefined]
'@xyz'.match(/@(xyz)/);
// ["@xyz", "xyz", index: 0, input: "@xyz", groups: undefined]
RegExp.$1;
// "xyz"
```

`RegExp`构造函数的引用只显示最后一个正则的捕获。

另外还有一个字符串实例方法也支持正则捕获的引用，它就是`replace`方法。

```javascript
'hello **regex**'.replace(/\*{2}(.*)\*{2}/, '<strong>$1</strong>');
// "hello <strong>regex</strong>"
```

实际上它才是最常用的引用捕获的方式。

#### 捕获命名

> 这是ES2018的新特性。

使用`\数字`引用捕获必须保证捕获组的顺序不变。现在开发者可以给捕获组命名了，有了名字以后，引用起来更加确定。

```javascript
'<App>hello regex</App>'.match(/<(?<tag>[a-zA-Z]+)>.*<\/\k<tag>>/);
// ["<App>hello regex</App>", "App", index: 0, input: "<App>hello regex</App>", groups: {tag: "App"}]
```

在捕获组内部最前面加上`?<key>`，它就被命名了。使用`\k<key>`语法就可以引用已经命名的捕获组。

是不是很简单？

通常情况下，开发者只是想在正则中将某些字符当成一个整体看待。捕获组很棒，但是它做了额外的事情，肯定需要额外的内存占用和计算资源。于是正则又有了非捕获组的概念。

```javascript
'@abc'.match(/@(abc)/);
// ["@abc", "abc", index: 0, input: "@abc", groups: undefined]
'@abc'.match(/@(?:abc)/);
// ["@abc", index: 0, input: "@abc", groups: undefined]
```

只要在圆括号内最前面加上`?:`标识，就是告诉正则引擎：我只要这个整体，不需要它的引用，你就别费劲了。从上面的例子也可以看出来，`match`方法返回的结果有些许不一样。

个人观点：我觉得正则的捕获设计应该反过来，默认不捕获，加上`?:`标识后才捕获。因为大多数时候开发者是不需要捕获的，但是它又懒得加`?:`标识，会有些许性能浪费。

## 分支

有时候开发者需要在正则中使用`或者`。

```javascript
'高圆圆'.match(/陈乔恩|高圆圆/);
// ["高圆圆", index: 0, input: "高圆圆", groups: undefined]
```

`|`就代表`或者`。字符组其实也是一个多选结构，但是它们俩有本质区别。字符组最终只能匹配一个字符，而分支匹配的是左边所有的字符或者右边所有的字符。

我们来看一个例子：

```javascript
'我喜欢高圆圆'.match(/我喜欢陈乔恩|高圆圆/);
// ["高圆圆", index: 3, input: "我喜欢高圆圆", groups: undefined]
```

因为`|`是将左右两边一切两半，然后匹配左边或者右边。所以上面的正则显然达不到我们想要的效果。这个时候就需要一个东西来缩小分支的范围。诶，你可能已经想到了：

```javascript
'我喜欢高圆圆'.match(/我喜欢(?:陈乔恩|高圆圆)/);
// ["我喜欢高圆圆", index: 0, input: "我喜欢高圆圆", groups: undefined]
```

没错，就是圆括号。

## 零宽断言

正则中有一些元字符，它不匹配字符，而是匹配一个位置。比如之前提到的`^`和`$`。`^`的意思是说这个位置应该是文本开始的位置。

正则还有一些比较高级的匹配位置的语法，它匹配的是：在这个位置之前或之后应该有什么内容。

零宽(zero-width)是什么意思？指的就是它匹配一个位置，本身没有宽度。

断言(assertion)是什么意思？指的是一种判断，断言之前或之后应该有什么或应该没有什么。

#### 零宽肯定先行断言

所谓的肯定就是判断有什么，而不是判断没有什么。

而先行指的是向前看(lookahead)，断言的这个位置是为前面的规则服务的。

语法很简单：圆括号内最左边加上`?=`标识。

```javascript
'CoffeeScript JavaScript javascript'.match(/\b\w{4}(?=Script\b)/);
// ["Java", index: 13, input: "CoffeeScript JavaScript javascript", groups: undefined]
```

上面匹配的是四个字母，这四个字母要满足以下条件：紧跟着的应该是`Script`字符串，而且`Script`字符串应该是单词的结尾部分。

所以，零宽肯定先行断言的意思是：现在有一段正则语法，用这段语法去匹配给定的文本。但是，满足条件的文本不仅要匹配这段语法，紧跟着它的必须是一个位置，这个位置又必须满足一段正则语法。

说的再直白点，我要匹配一段文本，但是这段文本后面必须紧跟着另一段特定的文本。零宽肯定先行断言就是一个界碑，我要满足前面和后面所有的条件，但是我只要前面的文本。

我们来看另一种情况：

```javascript
'CoffeeScript JavaScript javascript'.match(/\b\w{4}(?=Script\b)\w+/);
// ["JavaScript", index: 13, input: "CoffeeScript JavaScript javascript", groups: undefined]
```

上面的例子更加直观，零宽肯定先行断言已经匹配过`Script`一次了，后面的`\w+`却还是能匹配`Script`成功，足以说明它的`零宽`特性。它为紧贴在它前面的规则服务，并且不影响后面的匹配规则。

#### 零宽肯定后行断言

先行是向前看，那后行就是向后看(lookbehind)咯。

语法是圆括号内最左边加上`?<=`标识。

```javascript
'演员高圆圆 将军霍去病 演员霍思燕'.match(/(?<=演员)霍\S+/);
// ["霍思燕", index: 14, input: "演员高圆圆 将军霍去病 演员霍思燕", groups: undefined]
```

一个正则可以有多个断言：

```javascript
'演员高圆圆 将军霍去病 演员霍思燕'.match(/(?<=演员)霍.+?(?=\s|$)/);
// ["霍思燕", index: 14, input: "演员高圆圆 将军霍去病 演员霍思燕", groups: undefined]
```

#### 零宽否定先行断言

肯定是判断有什么，否定就是判断没有什么咯。

语法是圆括号内最左边加上`?!`标识。

```javascript
'TypeScript Perl JavaScript'.match(/\b\w{4}(?!Script\b)/);
// ["Perl", index: 11, input: "TypeScript Perl JavaScript", groups: undefined]
```

#### 零宽否定后行断言

语法是圆括号最左边加上`?<!`标识。

```javascript
'演员高圆圆 将军霍去病 演员霍思燕'.match(/(?<!演员)霍\S+/);
// ["霍去病", index: 8, input: "演员高圆圆 将军霍去病 演员霍思燕", groups: undefined]
```

## 修饰符

正则表达式除了主体语法，还有若干可选的模式修饰符。

写法就是将修饰符安插在正则主体的尾巴上。比如这样：`/abc/gi`。

#### g修饰符

`g`是`global`的缩写。默认情况下，正则从左向右匹配，只要匹配到了结果就会收工。`g`修饰符会开启全局匹配模式，找到所有匹配的结果。

```javascript
'演员高圆圆 将军霍去病 演员霍思燕'.match(/(?<=演员)\S+/);
// ["高圆圆", index: 2, input: "演员高圆圆 将军霍去病 演员霍思燕", groups: undefined]
'演员高圆圆 将军霍去病 演员霍思燕'.match(/(?<=演员)\S+/g);
// ["高圆圆", "霍思燕"]
```

#### i修饰符

`i`是`ignoreCase`的缩写。默认情况下，`/z/`是无法匹配`Z`的，所以我们有时候不得不这样写：`/[a-zA-Z]/`。`i`修饰符可以全局忽略大小写。

很多时候我们不在乎文本是大写、小写还是大小写混写，这个修饰符还是很有用的。

```javascript
'javascript is great'.match(/JavaScript/);
// null
'javascript is great'.match(/JavaScript/i);
// ["javascript", index: 0, input: "javascript is great", groups: undefined]
```

#### m修饰符

`m`是`multiline`的缩写。这个修饰符有特定起作用的场景：它要和`^`和`$`搭配起来使用。默认情况下，`^`和`$`匹配的是文本的开始和结束，加上`m`修饰符，它们的含义就变成了行的开始和结束。

```javascript
`
abc
xyz
`.match(/xyz/);
// ["xyz", index: 5, input: "↵abc↵xyz↵", groups: undefined]
`
abc
xyz
`.match(/^xyz$/);
// null
`
abc
xyz
`.match(/^xyz$/m);
// ["xyz", index: 5, input: "↵abc↵xyz↵", groups: undefined]
```

#### y修饰符

> 这是ES2015的新特性。

`y`是`sticky`的缩写。`y`修饰符有和`g`修饰符重合的功能，它们都是全局匹配。所以重点在`sticky`上，怎么理解这个`粘连`呢？

`g`修饰符不挑食，匹配完一个接着匹配下一个，对于文本的位置没有要求。但是`y`修饰符要求必须从文本的开始实施匹配，因为它会开启全局匹配，匹配到的文本的下一个字符就是下一次文本的开始。这就是所谓的粘连。

```javascript
'a bag with a tag has a mag'.match(/\wag/g);
// ["bag", "tag", "mag"]
'a bag with a tag has a mag'.match(/\wag/y);
// null
'bagtagmag'.match(/\wag/y);
// ["bag", index: 0, input: "bagtagmag", groups: undefined]
'bagtagmag'.match(/\wag/gy);
// ["bag", "tag", "mag"]
```

有人肯定发现了猫腻：你不是说`y`修饰符是全局匹配么？看上面的例子，单独一个`y`修饰符用match方法怎么并不是全局匹配呢？

诶，这里说来就话长了。

长话短说呢，就涉及到`y`修饰符的本质是什么。它的本质有二：

- 全局匹配(先别着急打我)。
- 从文本的`lastIndex`位置开始新的匹配。lastIndex是什么？它是正则表达式的一个属性，如果是全局匹配，它用来标注下一次匹配的起始点。这才是粘连的本质所在。

不知道你们发现什么了没有：**lastIndex是正则表达式的一个属性**。而上面例子中的match方法是作用在字符串上的，都没有lastIndex属性，休怪人家工作不上心。

```javascript
const reg = /\wag/y;
reg.exec('bagtagmag');
// ["bag", index: 0, input: "bagtagmag", groups: undefined]
reg.exec('bagtagmag');
// ["tag", index: 3, input: "bagtagmag", groups: undefined]
reg.exec('bagtagmag');
// ["mag", index: 6, input: "bagtagmag", groups: undefined]
```

咱们换成正则方法exec，多次执行，正则的lastIndex在变，匹配的结果也在变。全局匹配无疑了吧。

#### s修饰符

> 这是ES2018的新特性。

`s`不是`dotAll`的缩写。`s`修饰符要和`.`搭配使用，默认情况下，`.`匹配除了换行符之外的任意单个字符，然而它还没有强大到无所不能的地步，所以正则索性给它开个挂。

`s`修饰符的作用就是让`.`可以匹配任意单个字符。

`s`是`singleline`的缩写。

```javascript
`
abc
xyz
`.match(/c.x/);
// null
`
abc
xyz
`.match(/c.x/s);
// ["c↵x", index: 3, input: "↵abc↵xyz↵", groups: undefined]
```

#### u修饰符

> 这是ES2015的新特性。

`u`是`unicode`的缩写。有一些Unicode字符超过一个字节，正则就无法正确的识别它们。`u`修饰符就是用来处理这些不常见的情况的。

```javascript
'𠮷'.match(/^.$/);
// null
'𠮷'.match(/^.$/u);
// ["𠮷", index: 0, input: "𠮷", groups: undefined]
```

`𠮷`念`jí`，与`吉`同义。

笔者对Unicode认识尚浅，这里不过多展开。
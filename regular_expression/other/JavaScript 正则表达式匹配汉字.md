# JavaScript 正则表达式匹配汉字

2018年1月26日



![供氧口藏文、汉字、拉丁字母][0]

  
使用了藏文、汉字、拉丁字母三种文字的「供氧口」

## 一个可能有 20 年历史的正则表达式

在谷歌搜索「JavaScript 正则表达式匹配汉字」的时候，前几条结果全都是`/[\u4e00-\u9fa5]/`。没有人怀疑这个正则表达式有什么问题，那么在 2018 年的今天，让我们站在 Chrome 64 的肩膀上，放飞一下自我。

汉文（Han Script）是汉语、日本语、朝鲜语、韩国语的书写系统中的一种文字（Script），越南语在早期也曾在书写系统中使用汉文[1][1]。汉字（CJK Ideograph）是汉文的基本单元。各国都对汉字提出了自己的编码标准，Unicode 将这些标准加总在一起进行统一编码，力求实现原标准与 Unicode 编码之间的无损转换。Unicode 从语义（semantic）、抽象字形（abstract shape），具体字形（typeface）三个维度[2][2]出发，把不同编码标准里「起源相同、本义相同、形状一样或稍异」的汉字赋予相同编码，这些被编码的字符称为中日韩统一表意文字（下文我们提到的「汉字」，如果不加说明，均指代中日韩统一表意文字）。如果把它们全部列举出来写成正则表达式，那么就是技术上完整的匹配汉字的正则表达式了。

正则表达式`/[\u4e00-\u9fa5]/`的意思是匹配所有从 U+4E00, cjk unified ideograph-4e00 到 U+9FA5, cjk unified ideograph-9fa5 的字符。这一段区域对应的是 Unicode 1.0.1 就收录进来的中日韩统一表意文字（CJK Unified Ideographs）区块，在 Unicode 3.0 加入扩展 A 区以前，这个正则表达式确实给出了所有汉字的编码。换言之，从1992年到1999年，这个正则表达式确实是正确的，想必这个表达式已经有20年历史了。

## 匹配所有统一表意文字

然而时光飞逝，Unicode 在2017年6月发布了10.0.0版本。在这20年间，Unicode 添加了许多汉字。比如 Unicode 8.0 添加的 109 号化学元素「鿏（⿰⻐麦）」，其码点是 9FCF，不在这个正则表达式范围中。而如果我们期望程序里的`/[\u4e00-\u9fa5]/`可以与时俱进匹配最新的 Unicode 标准，显然是不现实的事情。因此，我们需要换一个思路，写一个无需维护的正则表达式：

    /\p{Unified_Ideograph}/u

其中`\u`是 ECMAScript 2015 定义的[正则表达式标志][3]，意味着将表达式作为 Unicode 码点序列。`\p`是正在提案阶段的[正则表达式 Unicode 属性转义][4]，它赋予了我们根据 Unicode 字符的属性数据[3][5]构造表达式的能力。`Unified_Ideograph`是 Unicode  
字符的一个二值属性，对于汉字，其取值为 Yes，否则为 No。因此`\p{Unified_Ideograph}`匹配所有满足`Unified_Ideograph=yes`的 Unicode 字符，而它的底层实现由运行时所依赖的 Unicode 版本决定，开发者不需要知道汉字的具体 Unicode 码点范围。

## 容易混淆的其他 Unicode 属性转义表达式

### `/\p{Ideographic}/u`

这个表达式匹配所有满足`Ideographic=yes`的 `Unicode` 字符。我们先看一下 UAX #44 对这个属性的解释[4][6]：

> Characters considered to be CJKV (Chinese, Japanese, Korean, and Vietnamese) or other siniform (Chinese writing-related) ideographs. This property roughly defines the class of “Chinese characters” and does not include characters of other logographic scripts such as Cuneiform or Egyptian Hieroglyphs.

这个属性表明该字符属于 CJKV 表意文字或者与汉语书写相关的其他表意文字（如西夏文、女书），这个属性粗略地定义了「中文字符」的分类。我们查看[Unicode 10.0.0 字符属性列表][7]可以知道，在 Unicode 10.0.0 中，

  
Ideographic 属性为 yes 的字符

> 3006 ; Ideographic # Lo IDEOGRAPHIC CLOSING MARK  
> 3007 ; Ideographic # Nl IDEOGRAPHIC NUMBER ZERO  
> 3021..3029 ; Ideographic # Nl [9] HANGZHOU NUMERAL ONE..HANGZHOU NUMERAL NINE  
> 3038..303A ; Ideographic # Nl [3] HANGZHOU NUMERAL TEN..HANGZHOU NUMERAL THIRTY  
> 3400..4DB5 ; Ideographic # Lo [6582] CJK UNIFIED IDEOGRAPH-3400..CJK UNIFIED IDEOGRAPH-4DB5  
> 4E00..9FEA ; Ideographic # Lo [20971] CJK UNIFIED IDEOGRAPH-4E00..CJK UNIFIED IDEOGRAPH-9FEA  
> F900..FA6D ; Ideographic # Lo [366] CJK COMPATIBILITY IDEOGRAPH-F900..CJK COMPATIBILITY IDEOGRAPH-FA6D  
> FA70..FAD9 ; Ideographic # Lo [106] CJK COMPATIBILITY IDEOGRAPH-FA70..CJK COMPATIBILITY IDEOGRAPH-FAD9  
> 17000..187EC ; Ideographic # Lo [6125] TANGUT IDEOGRAPH-17000..TANGUT IDEOGRAPH-187EC  
> 18800..18AF2 ; Ideographic # Lo [755] TANGUT COMPONENT-001..TANGUT COMPONENT-755  
> 1B170..1B2FB ; Ideographic # Lo [396] NUSHU CHARACTER-1B170..NUSHU CHARACTER-1B2FB  
> 20000..2A6D6 ; Ideographic # Lo [42711] CJK UNIFIED IDEOGRAPH-20000..CJK UNIFIED IDEOGRAPH-2A6D6  
> 2A700..2B734 ; Ideographic # Lo [4149] CJK UNIFIED IDEOGRAPH-2A700..CJK UNIFIED IDEOGRAPH-2B734  
> 2B740..2B81D ; Ideographic # Lo [222] CJK UNIFIED IDEOGRAPH-2B740..CJK UNIFIED IDEOGRAPH-2B81D  
> 2B820..2CEA1 ; Ideographic # Lo [5762] CJK UNIFIED IDEOGRAPH-2B820..CJK UNIFIED IDEOGRAPH-2CEA1  
> 2CEB0..2EBE0 ; Ideographic # Lo [7473] CJK UNIFIED IDEOGRAPH-2CEB0..CJK UNIFIED IDEOGRAPH-2EBE0  
> 2F800..2FA1D ; Ideographic # Lo [542] CJK COMPATIBILITY IDEOGRAPH-2F800..CJK COMPATIBILITY IDEOGRAPH-2FA1D  
> \# Total code points: 

96174囊括了所有统一表意文字、西夏文及其组件、女书、中日韩兼容性字符、苏州码子、「〇」以及日本语中的书信结尾标志「〆」。使用`/\p{Ideographic}/u`来匹配汉字会过于宽泛。一是包含了西夏文、女书，二是只用于编码转换用的兼容字符也纳入其中。

### `/\p{Script=Han}/u`

Script 属性[5][8]用来筛选满足下面条件的一组字符：

1. 字符的书写形式具有共同的图像特征与文字流变
1. 该组字符全部用来表达某个书写系统内的文本信息（textual information）

我们查看[Unicode 10.0.0 Scripts][9]可以知道，

  
满足`Script=Han`的字符

> 2E80..2E99 ; Han # So [26] CJK RADICAL REPEAT..CJK RADICAL RAP  
> 2E9B..2EF3 ; Han # So [89] CJK RADICAL CHOKE..CJK RADICAL C-SIMPLIFIED TURTLE  
> 2F00..2FD5 ; Han # So [214] KANGXI RADICAL ONE..KANGXI RADICAL FLUTE  
> 3005 ; Han # Lm IDEOGRAPHIC ITERATION MARK  
> 3007 ; Han # Nl IDEOGRAPHIC NUMBER ZERO  
> 3021..3029 ; Han # Nl [9] HANGZHOU NUMERAL ONE..HANGZHOU NUMERAL NINE  
> 3038..303A ; Han # Nl [3] HANGZHOU NUMERAL TEN..HANGZHOU NUMERAL THIRTY  
> 303B ; Han # Lm VERTICAL IDEOGRAPHIC ITERATION MARK  
> 3400..4DB5 ; Han # Lo [6582] CJK UNIFIED IDEOGRAPH-3400..CJK UNIFIED IDEOGRAPH-4DB5  
> 4E00..9FEA ; Han # Lo [20971] CJK UNIFIED IDEOGRAPH-4E00..CJK UNIFIED IDEOGRAPH-9FEA  
> F900..FA6D ; Han # Lo [366] CJK COMPATIBILITY IDEOGRAPH-F900..CJK COMPATIBILITY IDEOGRAPH-FA6D  
> FA70..FAD9 ; Han # Lo [106] CJK COMPATIBILITY IDEOGRAPH-FA70..CJK COMPATIBILITY IDEOGRAPH-FAD9  
> 20000..2A6D6 ; Han # Lo [42711] CJK UNIFIED IDEOGRAPH-20000..CJK UNIFIED IDEOGRAPH-2A6D6  
> 2A700..2B734 ; Han # Lo [4149] CJK UNIFIED IDEOGRAPH-2A700..CJK UNIFIED IDEOGRAPH-2B734  
> 2B740..2B81D ; Han # Lo [222] CJK UNIFIED IDEOGRAPH-2B740..CJK UNIFIED IDEOGRAPH-2B81D  
> 2B820..2CEA1 ; Han # Lo [5762] CJK UNIFIED IDEOGRAPH-2B820..CJK UNIFIED IDEOGRAPH-2CEA1  
> 2CEB0..2EBE0 ; Han # Lo [7473] CJK UNIFIED IDEOGRAPH-2CEB0..CJK UNIFIED IDEOGRAPH-2EBE0  
> 2F800..2FA1D ; Han # Lo [542] CJK COMPATIBILITY IDEOGRAPH-2F800..CJK COMPATIBILITY IDEOGRAPH-2FA1D  
> \# Total code points: 

89228囊括了所有统一表意文字、中日韩兼容性字符、苏州码子、「〇」、「〆」、「々」以及字典常用的部首。从前面汉文（Han Script）与汉字（CJK Ideograph）的关系我们可以知道，`/\p{Script=Han}/u`匹配的是汉文作为一个字符集里面的所有字符，因此它包括了部首、「々」等字符，这些字符要么当它们独立存在的时候没有语言意义（部首独立存在是一个符号），要么无法独立存在（「々」依赖于所修饰的汉字）。所以汉字是汉文的一个单元，汉文除了包含汉字以外，还包括这些符号、数字、修饰符。因此使用`/\p{Script=Han}/u`来匹配汉字是混淆了汉文与汉字的概念范围。

## 浏览器兼容性支持

### JavaScript

截至2018年1月，只有 Chrome 64 [支持][10] 正则表达式 Unicode 属性转义。对于其他浏览器，我们需要用babel转译插件[@babel/plugin-proposal-unicode-property-regex][11]将带有属性转义的正则表达式转为 Unicode 码点正则表达式或者 ES 5 的正则表达式。转译结果的在线演示可以在[这里][12]查看，用户可以自己在上面转译其他的 Unicode 属性转义正则表达式。我们在这里列举`/\p{Unified_Ideograph}/u`转译成Unicode 码点正则表达式的结果：


    const regex = /\p{Unified_Ideograph}/u;  
    // transpiled to ES6:  
    const regex = /[\u3400-\u4DB5\u4E00-\u9FEA\uFA0E\uFA0F\uFA11\uFA13\uFA14\uFA1F\uFA21\uFA23\uFA24\uFA27-\uFA29\u{20000}-\u{2A6D6}\u{2A700}-\u{2B734}\u{2B740}-\u{2B81D}\u{2B820}-\u{2CEA1}\u{2CEB0}-\u{2EBE0}]/u;

从上面这个正则表达式可以知道，转译的结果严格跟 

  
Unicode 10.0.0 中 Unified_Ideograph 属性为 yes 的字符

> 3400..4DB5 ; Unified_Ideograph # Lo [6582] CJK UNIFIED IDEOGRAPH-3400..CJK UNIFIED IDEOGRAPH-4DB5  
> 4E00..9FEA ; Unified_Ideograph # Lo [20971] CJK UNIFIED IDEOGRAPH-4E00..CJK UNIFIED IDEOGRAPH-9FEA  
> FA0E..FA0F ; Unified_Ideograph # Lo [2] CJK COMPATIBILITY IDEOGRAPH-FA0E..CJK COMPATIBILITY IDEOGRAPH-FA0F  
> FA11 ; Unified_Ideograph # Lo CJK COMPATIBILITY IDEOGRAPH-FA11  
> FA13..FA14 ; Unified_Ideograph # Lo [2] CJK COMPATIBILITY IDEOGRAPH-FA13..CJK COMPATIBILITY IDEOGRAPH-FA14  
> FA1F ; Unified_Ideograph # Lo CJK COMPATIBILITY IDEOGRAPH-FA1F  
> FA21 ; Unified_Ideograph # Lo CJK COMPATIBILITY IDEOGRAPH-FA21  
> FA23..FA24 ; Unified_Ideograph # Lo [2] CJK COMPATIBILITY IDEOGRAPH-FA23..CJK COMPATIBILITY IDEOGRAPH-FA24  
> FA27..FA29 ; Unified_Ideograph # Lo [3] CJK COMPATIBILITY IDEOGRAPH-FA27..CJK COMPATIBILITY IDEOGRAPH-FA29  
> 20000..2A6D6 ; Unified_Ideograph # Lo [42711] CJK UNIFIED IDEOGRAPH-20000..CJK UNIFIED IDEOGRAPH-2A6D6  
> 2A700..2B734 ; Unified_Ideograph # Lo [4149] CJK UNIFIED IDEOGRAPH-2A700..CJK UNIFIED IDEOGRAPH-2B734  
> 2B740..2B81D ; Unified_Ideograph # Lo [222] CJK UNIFIED IDEOGRAPH-2B740..CJK UNIFIED IDEOGRAPH-2B81D  
> 2B820..2CEA1 ; Unified_Ideograph # Lo [5762] CJK UNIFIED IDEOGRAPH-2B820..CJK UNIFIED IDEOGRAPH-2CEA1  
> 2CEB0..2EBE0 ; Unified_Ideograph # Lo [7473] CJK UNIFIED IDEOGRAPH-2CEB0..CJK UNIFIED IDEOGRAPH-2EBE0  
> \# Total code points: 87882  

严格对应。因此转译是正确的。该插件还可以使用

    {  
      "plugins": [  
        ["@babel/plugin-proposal-unicode-property-regex", { "useUnicodeFlag": false }]  
      ]  
    }

配置将表达式转成 ES5 的传统的以字符的 UTF16 表示为序列的字符串，这里不再赘述。

### `input` 元素的 `pattern` 属性

在前端技术中，除了JavaScript会用到正则表达式，HTML 里`<input>`元素的`pattern`属性也会用到正则表达式。与 JavaScript 相比，`pattern`不支持设置正则表达式的标志位，因此 HTML 标准中强制规定了 `input` 元素的 `pattern` 属性需要施加`unicode`标志[6][13]。目前只有 Chrome 53+, Firefox 遵循了这一标准，其他的浏览器暂未支持。

在 React/Angular/Vue.js 三大前端框架中，Angular 提供了近似于 `pattern` 的指令 `ngPattern`。目前ngPattern尚未施加unicode标志[7][14]。AngularJS 的 ngPattern directive 仍未施加。

在大部分情况，是否施加unicode标志不会对正则表达式产生语义区别。主要的差别在于：

* 在使用`\u{10000}`表示 Unicode 码点字符情形，正则表达式`/\u{10000}/`代表匹配`u`一万次，`/\u{10000}/u`匹配字符`\u{10000}`一次
* `/./`只匹配 BMP 平面的字符，`/./u`匹配所有平面的字符。

由于 `Unicode` 属性转义正则表达式依赖于标识位`\u`，因此下面的用法目前只能在 Chrome 下使用：

    <input type="text" pattern="\p{Unified_Ideograph}">

因此，如果需要兼容其他浏览器，可以使用转译插件的底层库[regexpu-core][15]在 js 层转换正则表达式，再把转换结果输送到 HTML 模版中。

    const rewritePattern = require("regexpu-core");  
    rewritePattern('\\p{Unified_Ideograph}', 'u', {  
      'unicodePropertyEscape': true,  
      'useUnicodeFlag': false  
    });  
    // → '/(?:[\u3400-\u4DB5\u4E00-\u9FEA\uFA0E\uFA0F\uFA11\uFA13\uFA14\uFA1F\uFA21\uFA23\uFA24\uFA27-\uFA29]|[\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879][\uDC00-\uDFFF]|\uD869[\uDC00-\uDED6\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF34\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0])/'

## 总结

1. `/[\u4e00-\u9fa5]/`是错的，不要用二十年前的正则表达式了
1. `/\p{Unified_Ideograph}/u`是正确的，不需要维护，匹配所有汉字。这里`\p`是 `Unicode` 属性转义正则表达式。
1. `/\p{Ideographic}/u` 和 `/\p{Script=Han}/u` 匹配了除了汉字以外的其他一些字符，在「汉字匹配正则表达式」这个需求下，是错的。
1. 目前只有 Chrome 支持 `Unicode` 属性转义正则表达式。对其他环境，使用@babel/plugin-proposal-unicode-property-regex 和 regexpu-core 进行优雅降级。

- - -

  
  
1: [Unicode 10.0.0 第六章第一节，书写系统][16][↩][17]  
2: [Unicode 10.0.0 第十八章第一节，东亚][18][↩][19]  
3: [Unicode 10.0.0 字符属性列表][7][↩][20]  
4: [UAX #44 第 20 版的属性说明][21][↩][22]  
5: [UAX #24 第 27 版][23][↩][24]  
6: [HTML 标准中input元素的pattern属性][25][↩][26]  
7: [给ngPattern施加unicode标志][27][↩][28]

[0]: https://jhuang.me/img/IMG_0376.jpg
[1]: #ref1
[2]: #ref2
[3]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/RegExp
[4]: https://github.com/tc39/proposal-regexp-unicode-property-escapes
[5]: #ref3
[6]: #ref4
[7]: http://www.unicode.org/Public/10.0.0/ucd/PropList.txt
[8]: #ref5
[9]: http://www.unicode.org/Public/10.0.0/ucd/Scripts.txt
[10]: https://www.chromestatus.com/feature/6706900393525248
[11]: https://github.com/babel/babel/tree/master/packages/babel-plugin-proposal-unicode-property-regex
[12]: https://mothereff.in/regexpu#input=const+regex+%3D+/%5Cp%7BUnified_Ideograph%7D/u%3B&unicodePropertyEscape=1
[13]: #ref6
[14]: #ref7
[15]: https://github.com/mathiasbynens/regexpu-core
[16]: http://www.unicode.org/versions/Unicode10.0.0/ch06.pdf
[17]: #fnref1
[18]: http://www.unicode.org/versions/Unicode10.0.0/ch18.pdf
[19]: #fnref2
[20]: #fnref3
[21]: http://www.unicode.org/reports/tr44/tr44-20.html#Property_Definitions
[22]: #fnref4
[23]: http://www.unicode.org/reports/tr24/tr24-27.html#Introduction
[24]: #fnref5
[25]: https://html.spec.whatwg.org/multipage/input.html#the-pattern-attribute
[26]: #fnref6
[27]: https://github.com/angular/angular/pull/20819
[28]: #fnref7
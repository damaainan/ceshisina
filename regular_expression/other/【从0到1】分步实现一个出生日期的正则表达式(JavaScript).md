## 【从0到1】分步实现一个出生日期的正则表达式(JavaScript)

来源：[https://segmentfault.com/a/1190000013898447](https://segmentfault.com/a/1190000013898447)

## 简言

在表单验证中，经常会用正则表达式做出生日期校验。本文把出生日期分割成几个部分，分步地介绍了实现一个出生日期校验的完整过程。相信您在理解了本篇的内容后，对如何编写和如何应用正则表达式会有进一步的理解和体会。

![][0]

声明：本文目的是为了阐述如何编写一个正则表达式的过程。另本文所涉代码皆未经严格测试。


我们将一个形式如 2018-06-15 的出生日期分割个年份，月份和日期三个组成部分，分别来编写相应的正则。
## 1 年份正则


首先给出年份正则表达式的规则定义：

* 年份由4位数字组成
* 只接受19，20开头的年份


根据以上规则，很容易写出年份的正则表达式：

```js
var pattern = /^(19|20)\d{2}$/;
//输出 true
console.log(pattern.test("2008"));
```


其中`/  /`两个斜杠及其中间的字符是正则表达式直接量的定义；`^`表示匹配字符串的开头，`$`表示匹配字符串的结尾；`^(19|20)`表示匹配以19或20开头的字符串，一对小括号作用是将几项组合为一个单元；而`\d{2}`表示匹配任意ASCII数字2次，`\d`等价于`[0-9]`，而`{2}`则表示匹配前一项2次。


上述正则表达式可以匹配1900至2099这些年份，如果想限制年份的范围，增加规则如下：

* 年份起始于1920年
* 年份终止于2018年

根据以上规则，变更正则表达式如下：

```js
var pattern = /^(19[2-9]\d{1})|(20((0[0-9])|(1[0-8])))$/;
//输出 false
console.log(pattern.test("1916"));
//输出 true
console.log(pattern.test("2008"));
//输出 false
console.log(pattern.test("2022"));
```


[演示代码][1]
## 2 月份正则


首先给出月份正则表达式的规则定义：

* 月份可以是1-12
* 月份如果是1-9，则前面可加0


根据以上规则，给出如下正则及简单测试：

```js
var pattern = /^((0?[1-9])|(1[0-2]))$/;
//输出 false
console.log(pattern.test("19"));
//输出 true
console.log(pattern.test("02"));
//输出 true
console.log(pattern.test("2"));
//输出 true
console.log(pattern.test("11"));
```

[演示代码][2]
## 3 日期正则


首先给出日期正则表达式的规则定义：

* 日期可以是1-31
* 如果日期是1-9，则前面可加0



根据以上规则，给出如下正则及简单测试：

```js
var pattern = /^((0?[1-9])|([1-2][0-9])|30|31)$/;
//输出 false
console.log(pattern.test("32"));
//输出 true
console.log(pattern.test("02"));
//输出 true
console.log(pattern.test("2"));
```


[演示代码][3]
## 4 组合校验


根据上述的年份正则，月份正则，日期正则组合形成出生日期的正则表达式：

```js
var pattern = /^((19[2-9]\d{1})|(20((0[0-9])|(1[0-8]))))\-((0?[1-9])|(1[0-2]))\-((0?[1-9])|([1-2][0-9])|30|31)$/;
//输出 true
console.log(pattern.test("1923-3-18"));
//输出 true
console.log(pattern.test("1923-4-31"));
//输出 true
console.log(pattern.test("1923-2-29"));
//输出 true
console.log(pattern.test("2016-2-29"));
```


[演示代码][4]

从以上测试结果可以看出，上述正则验证还不完善，主要是2，4，6，9，11月份的天数问题。
## 5 完善

根据第4步的问题，增加限定规则如下：


* 4，6，9，11月没有31日
* 2月平年是28天
* 2月闰年是29天



平年闰年判定：


能被4整除的年份是闰年，不能被4整除的年份是平年。但是如果是整百年，就只有能被400整除才是闰年，否则就是平年。


根据新增规则及说明，给出下面正则函数及测试：

```js
var checkBirth = function (val) {
    var pattern = /^((?:19[2-9]\d{1})|(?:20[01][0-8]))\-((?:0?[1-9])|(?:1[0-2]))\-((?:0?[1-9])|(?:[1-2][0-9])|30|31)$/;
    var result = val.match(pattern);
    if(result != null) {
        var iYear = parseInt(result[1]);
        var month = result[2];
        var date = result[3];
        if(/^((0?[469])|11)$/.test(month) &&　date == '31') {
            return false;
        } else if(parseInt(month)  == 2){
            if((iYear % 4 ==0 && iYear % 100 != 0) || (iYear % 400 == 0)) {
                if(date == '29') {
                    return true;
                }
            }
            if(parseInt(date) > 28) {
                return false;
            }
        }
        return true;
    }
    return false;
}
//输出 true
console.log(checkBirth("1923-3-18"));
//输出 false 4月份没有31日
console.log(checkBirth("1923-4-31"));
//输出 false  平年
console.log(checkBirth("1923-2-29"));
//输出 true  闰年
console.log(checkBirth("2016-2-29"));
```

[演示代码][5]


上述正则表达式中利用了String的match()方法，该方法唯一参数是一个正则表达式，返回的是一个由匹配结果组成的数组。数组的第一个元素就是匹配的字符串，余下的元素则是正则表达式中用圆括号括起来的子表达式。而`(:?...)`这种形式多次出现，该种方式表示只是把项组合到一个单元，但不记忆与该组相匹配的字符。利用该种方法按照正则匹配的顺序分别取出了年月日项，以便后序比较。


根据上述分析与测试，我们不但实现了年月日的正则的一般判定，还实现了日期范围及2，4，6，9，11月等特殊月份天数的处理，测验结果达到了我们设定的目标。
## 6 总结


上述分析和讲解，只是为了讲述正则表达式而已，因此上述代码并不适用于产品环境。其中比较突出的问题在于对正则表达式的滥用，正则的强大体现在对模式的灵活匹配，但是在日期比较和校验方面不如用`Date()`更直接和简捷。上述`checkBirth()`臃肿而复杂，测试及维护成本都很高。


因此建议将上述函数变更如下：



* 正则只做基本的格式判定
* `Date()`做日期范围的判定
* `Date()`做月份相应天数的校验

变更后的函数和演示代码如下：

```js
var checkBirth = function (val) {
    var pattern = /^(19|20)\d{2}\-((0?[1-9])|(1[0-2]))\-((0?[1-9])|([1-2]\d)|3[01])$/;
    if(pattern.test(val)) {
        var date = new Date(val);
        if(date < new Date("1919-12-31") || date > new Date()) {
            return false;
        }
        var month = val.substring(val.indexOf("-")+1,val.lastIndexOf("-"));
        return date && (date.getMonth()+1 == parseInt(month));
    }
    return false;
}
```


[演示代码][6]


上述代码，分工明确，逻辑简单， 较前一版有了较大地提升。


综上所述，正则表达式是强大的，但并不是万能的，因此不要过份地依赖和滥用正则。

[1]: http://www.42du.cn/run/51
[2]: http://www.42du.cn/run/52
[3]: http://www.42du.cn/run/53
[4]: http://www.42du.cn/run/54
[5]: http://www.42du.cn/run/55
[6]: http://www.42du.cn/run/56
[0]: ../img/1460000013898452.png
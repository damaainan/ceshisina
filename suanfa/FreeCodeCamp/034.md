# intermediate-arguments-optional

 时间 2017-07-12 12:54:47  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-arguments-optional/][1]

 主题 [JavaScript][2]

* 这个 function 在定义的时候没有声明任何参数，但在调用的时候会传入一个或者两个参数。最后返回两数之和
* 如果参数是一个，那么调用方式就是 add(1)(2) 的形式，返回值为 3
* 如果参数是两个，那么调用方式就会是 add(1, 2) 的形式，返回值也为 3

## 基本解法 

## 思路提示 

* 相比之前几道题，这道题稍有难度，但整体逻辑并不复杂
* 通过分析我们得知，只有传入一个参数或两个参数，这两种情况： 
  * 如果传入一个参数，那就需要再通过 (x) 传入第二个参数
  * 如果传入两个参数，直接返回两个参数之和就行
* 既然 add 函数中没有声明参数，因此这道题我们肯定是要用 arguments 来操作了
* 另外还有一点需要考虑，就是如何判断参数是否为数字。你可能第一反应是使用 isNaN 方法来判断，事实上在这道题目中是不够的。因为左边的测试有一条是 add(1, "2") 也应该返回 undefined ，可以先考虑下这个要如何处理
* 另外就是，这道题会用到一点点闭包的知识。闭包可能是刚开始学 JS 接触到的最大难点。对此，我个人的建议是不用过于深究闭包的定义。多写写代码，自然会遇到需要使用闭包的情况。或者很可能你已经会写闭包了，但只是不知道它叫闭包

## 参考链接 

* [arguments][3]
* [typeof][4]
* [isFinite][5]
* [isNaN][6] )

## 代码 

    function add(){
        var arg = [].slice.call(arguments);
        // 边界条件
        if (!isNum(arg[0]) || arg.length === 2 && !isNum(arg[1])) return;
    
        if (arg.length === 2) {
            // 直接返回两数的和
            return arg[0] + arg[1];
        }
    
        // 返回匿名函数，接收下一个参数
        return function(next){
            if (isNum(next)) {
                return arg[0] + next;
            }
            // 可以不写 else，因为如果不声明返回值，那么返回的就是 undefined
        }
    
        function isNum(e){
            return typeof e === 'number' && isFinite(e);
        }
    }
    

## 解释 

* 先从最好解释的说起吧。这里封装了一个用于判断是否为数字的 isNum 方法。由于题目中只允许数字，不允许字符串或者其它，那么最容易想到的就是通过 typeof 了，这样就可以直接过滤掉字符串
* 但这也会带来其他问题。对于 typeof 返回 number 的不光有数字，还有 NaN 以及 Infinity 。如果你不知道 Infinity ，可以去搜搜看，这也是一个关键字。如果用一个数除以 0 ，你也会得到 Infinity
* 因此，我们需要在后面加上 isFinite 来判断，它可以帮我们过滤掉 NaN 以及 Infinity 这两种情况。虽然类似与 isFinite("1") 也会返回 true ，但我们已经有了 typeof 那个判断
* 另外，虽然这里没有涉及，但要多说一句。 isFinite 这个方法，如果传入 [] 或者只有一个数字元素的数组，比如 [3] ，那是会返回 true 的。由于 isFinite([1, 2]) 就会返回 false ，因此我猜测原因是调用了数组的 toString 方法，把 [3] 变成了 "3"
* 第一行代码，再多说一句吧，以前提到过几次。 arguments 和 nodeList 是 JavaScript 中常见的 “Array-like object” (类数组对象)。它本身没有数组方法，对象中的属性是 0 、 1 、 2 、 3 …… 这样的。上面的代码，意思就是把它转成一个数组
* **(补充)：** 写完才想到，这里其实不转也行。因为 “Array-like object” 是有 length 属性的，后续代码中我们也只有访问元素和访问 length 这两个操作。 **但是，强烈建议这里赋一下值** ，因为对于一个参数的情况，我们要返回一个匿名函数，这时候在内部函数也会有一个 arguments
* 返回两数的和应该没什么多说的。题目中只有两种情况，有兴趣的朋友可以尝试一下，能不能写成可以接收不定数目参数的，类似于 add(1, 2, 3)(4)(5, 6)(7)(8) 。这个实现起来会更加有挑战
* 关键在于后面那个 return function ，其实这一部分，我自己刚开始学 JS 的时候也懵了很久。我们可以这么去理解， add(2)(3) 这样的调用方式，其实就是在 add(2) 执行之后， (3) 执行之前的那个瞬间是一个函数，我们暂且称这个函数为 function foo() {} 。那么， foo(3) 就很好理解了，相当于把 3 作为参数传入 foo 。简单画个图：
```
    function foo() {}
    
    add(2)(3)    
       |
       |  如果我们可以让 add(2) 返回函数 foo
       |
       ↓
    foo(3)    -- 那么就变成了这样
```

* 这样画是想说明，对于这里说的那个 foo 函数，根据题目要求，我们只需要在定义的时候让它接收一个参数就好了，也就是图中的 3
* 那么，如果我们把 add 函数的返回值设置成上面说到的 foo 函数 ( **确切一点说，是返回 foo 函数的引用** )，那么它就可以通过 add(2)(3) 这样的形式来调用了，并且 3 会传到 foo 里面去
* 既然 foo 是 add 函数的返回值，那么 foo 本身就是在 add 函数里面的。根据 JS 的作用域特点，在函数 foo 里面我们可以访问到 add 函数作用域中的值，这也就是我们获取第一步传入的 2 的思路
* 再考虑一下，在 add 函数中，反正 foo 也就只在最后执行一次，那我们没必要在 add 里给它命名。因此才有了上面返回一个匿名函数的写法，那个最后返回的匿名函数就是这里我们说的 foo

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-arguments-optional/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11060004
[3]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Functions/arguments
[4]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/typeof
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/isFinite
[6]: http://singsing.io/blog/fcc/intermediate-arguments-optional/- [isFinite](https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/isNaN
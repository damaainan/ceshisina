# intermediate-binary-agents

 时间 2017-07-11 11:54:22  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-binary-agents/][1]

 主题 [JavaScript][2]

* 这个 function 接收一个字符串参数 str ，返回值为转换之后的字符串
* 如果 str 是 "01000001" ，那么返回值应为 "A"

## 基本解法 - for 循环 

## 思路提示 

* 这道题目难度不大。在基本解法中，我们可以只用 for 循环以及 String.fromCharCode 来实现
* 先来观察一下参数 str ，它是由空格分割的长字符串。你可能已经想到了用 split 转成数组，这个方法我们留到后面再说
* 再来明确一下转换的规则。如果你没听说过二进制，那先去搜搜二进制是什么意思吧

插播一则程序员才懂的笑话：

世界上有 10 种人，一种是懂二进制的，另一种不懂

* 我们常见的数字都是十进制的，比如数字 1234 ，其实就是 1 * 10^3 + 2 * 10^2 + 3 * 10^1 + 4 * 10^0 (其中 a^b 符号表示 a 的 b 次幂，下同)
* 同理，对于二进制，数字 10 就表示 1 * 2^1 + 0 * 2^0 。算算得多少，就应该能理解上面的笑话了
* 虽然是废话，但还是要说一句， x 进制的数字，每一位都小于 x 。十进制里面，每一位最大只可能是 9 ；二进制里面，每一位最大只可能是 1 (也就是只有 0 和 1 两种可能)
* 知道了这个规则，我们就可以开始写转换函数了。顺便说一句，JavaScript 会有内置函数处理这个的，不过我们来手写一下也不复杂

## 参考链接 

* [String.fromCharCode][3]
* [Math.pow][4]

## 代码 

    function binaryAgent(str){
        // 跳出条件
        if (str.length === 0) return;
    
        var result = "";
        // 双指针
        var left = 0;
        var right = 1;
        
        while (left < str.length) {
            // 小坑，如果没有后面的条件，则最后一组会执行不到
            if (str[right] === " " || right === str.length) {
                result += binaryToChar(str.slice(left, right));
                // 这里可能有点不清晰。其实就是指针的移动
                // 先把 left 移到当前 right 的右一位，再把 right 移到移动后的 left 右一位
                left = right + 1;
                right = left + 1;
            } else {
                right++;
            }
        }
        
        // 只是一个逻辑的封装。这个函数作用就是传入一段二进制数字，转成 10 进制，然后根据 ASCII 码输出对应的字符
        function binaryToChar(str){
            var num = 0;
            for (var i = 0; i < str.length; i++) {
                num += str[i] * Math.pow(2, str.length - i - 1)
            }
            return String.fromCharCode(num);
        }
        
        return result;
    }
    

## 解释 

* 这种解法是很容易理解的双指针思路。执行过程如下
```
    (以下用 ↑ 表示左边的指针，↓ 表示右边的)
    
    010101 111111 000000
    ↑↓                   初始状态。此时 if 不满足，right++
    
    010101 111111 000000
    ↑ ↓                  此时 if 依旧不满足，继续 right++
    
    ……
    
    010101 111111 000000
    ↑     ↓              此时 if 满足，取到第一组
    
    010101 111111 000000
          ↓↑             这是 left = right + 1 执行后的情况
    
    010101 111111 000000
           ↑↓            这是 right = left + 1 执行后的情况。继续 while 循环。此时 if 不满足，right++
    
    010101 111111 000000
           ↑ ↓           ……以此类推
```

* 底下写了一个 binaryToString 的方法。这其实就是把二进制数字转换成对应的字符。要解释的都在代码里，应该不复杂。其实这个 function 里面的内容，放到上面去也是一个效果。只是这样看起来更舒服些
* 二进制转十进制应该也不需要太多解释了，一开始就说了如何计算的

## 中级解法 - 使用 map 和 parseInt 

## 思路提示 

* parseInt 这个方法相信大家都见过也用过。但我们一般只给它传一个参数，其实 parseInt 是可以接收两个参数的。因此，才会有这道题：

插播一则面试题：

[1, 2 ,3].map(parseInt) 的结果是什么？当然不是 [1, 2, 3]

* 惊不惊喜？意不意外？其实 parseInt 的第二个参数是 radix (基数)。比如，第二个参数传入 8 就代表第一个参数是八进制的， 16 就代表第一个参数是十六进制的。传入 1 是肯定不对的，肯定返回 NaN 。如果 **传入 0 或者不传** ，那要先看第一个参数的开头。如果是 "0x" 或者 "0X" 开头，则会按十六进制解析；如果是 "0" 开头就会按八进制解析，其余均按照十进制解析
* 那么我们只要给第二个参数传入 2 ，就可以直接得到十进制的结果了，代码也变得容易很多
* 很多朋友应该都想到了，既然空格分割，又是字符串，那我们给它 split 一下就行了。当然，之后我们只要再 map 一次就解决了。别忘了最后要 join

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-binary-agents/
[2]: /topics/11060004
[3]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/String/fromCharCode
[4]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Math/pow
# intermediate-everything-be-true

 时间 2017-07-11 12:54:27  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-everything-be-true/][1]

 主题 [JavaScript][2]

* 这个 function 接收一个 JSON 对象参数 collection 和一个字符串参数 pre 。若 pre 表示的属性在 collection 中的每一个对象都存在且对应的值为真则返回 true ，否则返回 false
* 如果 collection 是 [{a: 1, b: 2}, {a: 1, c: 3}] ， pre 是 "a" ，返回值应为 true ；如果这时候 pre 是 "c" ，则返回值为 false
* 另外，如果 collection 是 [{a: 0, b: 2}, {a: false, c: 3}] ， pre 是 "a" ，那么也应该返回 false

## 基本解法 - for 循环 

## 思路提示 

* 很简单的一道送分题，只要会写循环就能做出来
* 当然，你还需要知道对象的 hasOwnProperty 这个方法

## 参考链接 

* [Object.hasOwnProperty][3]

## 代码 

    function every(collection, pre){
        // 遍历 JSON 对象
        for (var i = 0; i < collection.length; i++) {
            // 遍历每一个 Object
            for (var key in collection[i]) {
                if (!collection[i].hasOwnProperty(pre) || !collection[i][pre]) {
                    return false;
                }
            }
        }
        
        return true;
    }
    

## 解释 

* 又是一道很典型的逻辑短路的应用。如果我们找到了一个不符合条件的元素，直接返回 false 就行，不用进行后续判断了
* 注释里解释了一下两层循环分别是在干嘛。除此之外，没有别的要解释了

## 中级解法 - every 和 Object.keys 

## 思路提示 

* 题目中说到了用 every 。如果不熟悉的话，可以先去 MDN 看看文档，链接在底下
* 当然，这道题用 reduce 也是可以写的。有兴趣的话可以自己尝试一下
* Object.keys 也是一个好方法，建议使用
* **划重点：** 需要注意的是，这两个方法在个别浏览器中可能不支持。推荐使用 Chrome/Firefox/Safari

## 参考链接 

* [Object.keys][4]
* [Array.every][5]
* [Array.indexOf][6]

## 代码 

    function every(collection, pre){
        return collection.every(checker);
    
        function checker(obj){
            return Object.keys(obj).indexOf(pre) > -1 && obj[pre];
        }
    }
    

## 解释 

* 首先， every 和 map 什么的很类似，回调函数中第一个参数为元素，第二个为索引，第三个为数组本身。回调函数的返回值需要为 true 或者 false ，作用是对每一个都执行回调函数的检查，如果都通过那就返回 true ，否则返回 false
* 其实，这个回调函数就是我们提取出的公共逻辑。对 collection 中的每一个元素，我们都要判断它是否有 pre 属性，以及 pre 属性的值是否为真
* 判断它是否有属性很简单，我们可以不遍历，而是用 Object.keys 方法。这个方法的返回值为对象的所有属性，而且返回值是一个数组。然后我们就可以用数组的 indexOf 方法来检查 pre 是否在这个数组里，也就知道了这个对象是否有 pre 属性
* 至于后面的 obj[pre] ，有朋友可能会问，这个不是读取值么，没有判断啊。其实是这样，我们把它写到了 && 之后，因此这里是把 obj[pre] 隐式转换成了 Boolean。就好像， true && 1 会返回 true ，而 true && 0 会返回 false 。所以，这里就相当于 Boolean(obj[pre])
* 如果你想不明白上面这一点，请去 MDN 看看 JavaScript 的 Truthy 和 Falsy 分别是怎么回事

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-everything-be-true/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11060004
[3]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/hasOwnProperty
[4]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/keys
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/every
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
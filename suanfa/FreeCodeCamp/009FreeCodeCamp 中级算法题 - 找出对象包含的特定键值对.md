# FreeCodeCamp 中级算法题 - 找出对象包含的特定键值对

 时间 2017-04-05 20:57:35  S1ngS1ng

_原文_[http://singsing.io/blog/fcc/intermediate-where-art-thou/][1]


* [中文链接][3]
* [英文链接][4]
* 级别：中级 (Intermediate Algorithm Scripting)

> 写一个 function，它遍历一个对象数组（第一个参数）并返回一个包含相匹配的属性-值对（第二个参数）的所有对象的数组。如果返回的数组中包含 source 对象的属性-值对，那么此对象的每一个属性-值对都必须存在于 collection 的对象中。

> 例如，如果第一个参数是 [{ first: "Romeo", last: "Montague" }, { first: "Mercutio", last: null }, { first: "Tybalt", last: "Capulet" }]，第二个参数是 { last: "Capulet" }，那么你必须从数组（第一个参数）返回其中的第三个对象，因为它包含了作为第二个参数传递的属性-值对。


> where([{ first: "Romeo", last: "Montague" }, { first: "Mercutio", last: null }, { first: "Tybalt", last: "Capulet" }], { last: "Capulet" }) 应该返回 [{ first: "Tybalt", last: "Capulet" }]。
> where([{ "a": 1 }, { "a": 1 }, { "a": 1, "b": 2 }], { "a": 1 }) 应该返回 [{ "a": 1 }, { "a": 1 }, { "a": 1, "b": 2 }]。
> where([{ "a": 1, "b": 2 }, { "a": 1 }, { "a": 1, "b": 2, "c": 2 }], { "a": 1, "b": 2 }) 应该返回 [{ "a": 1, "b": 2 }, { "a": 1, "b": 2, "c": 2 }]。
> where([{ "a": 1, "b": 2 }, { "a": 1 }, { "a": 1, "b": 2, "c": 2 }], { "a": 1, "c": 2 }) 应该返回 [{ "a": 1, "b": 2, "c": 2 }]。

## 问题解释 

* 这个 function 接收两个参数。第一个参数 collection 为对象数组 (JSON)，第二个参数 source 为对象 (Object)。返回值为过滤后的第一个参数
* 例如，第一个参数是 [{"a": 1}, {"b": 2}, {"a": 1, "b": 2}] ，第二个参数是 {"a": 1} 。那么返回值就是 [{"a": 1}, {"a": 1, "b": 2}]
* 需要注意的是，如果第二个参数包含多个键值对，那么需要在第一个参数中找出包含第二个参数每一对键值对的数据
* 这么说可能有一点绕，简单来说，匹配的规则就是完全包含。可以多，但不能少。另外，光包含属性是不够的，还要满足对应的值相等

## 基本解法 

## 思路提示 

* 老规矩，我们先用循环来写，循环的思路并不难想
* 首先遍历第一个参数，针对每一个遍历中的元素(即为第一个参数的数组元素)，与第二个参数比较，看看是不是第二个参数中的每一个键值对都在当前元素中存在且值相等。如果是则保留，反之不保留
* 题目中建议使用 Object.hasOwnProperty() ，不过个人觉得是没有必要的

## 参考链接 

* [Object.hasOwnProperty()][5]

## 代码 
```js
function where(collection, source){
    var result = [];

    for (var i = 0; i < collection.length; i++) {
        // 用于指示是否最后要 push 当前元素
        var flag = true;

        for (var key in source) {
            if (collection[i][key] !== source[key]) {
                flag = false;
            }
        }

        if (flag) {
            result.push(collection[i]);
        }
    }

    return result;
}
```

## 解释 

* 这道题目乍一看，可能不够中级算法的标准。但写一写就知道了，还是需要一点点技巧的
* 在 JavaScript 中，我们可以通过 break 或者 return 来实现逻辑短路。但对于这道题目，由于我们使用了嵌套循环，这样就肯定不能通过 return 来控制，因为 return 在函数 (function) 中是直接返回结果。这显然不是我们想要的
* 考虑到题目要求，我们需要走完内层遍历才能得出是否要 push 的结论。因此，在这里使用 break 来进行逻辑短路也是不可以的
* 所以，我的做法是，设置一个变量，叫 flag 。它是一个 Boolean，用来指示最后是否 push 当前元素。显然，我们可以因为一个值不相等就决定最后不 push ，但我们不能因为其中一个值相等就 push
* 默认值我们设为 true 。由于我们是用 for...in 遍历的是第二个参数，那么如果对于每个 key ，如果每个 key 在外层遍历的当前元素中都可以找到，而且值与第二个参数的对应值都相等，那才表示可以 push 。因此，只要有 key 不存在或不相等的，我们就把它改成 false 。内层遍历结束后，我们再通过这个 flag 来判断是否要 push
* 有朋友可能会说，这里没有用 hasOwnProperty 方法来判断属性是否存在啊。事实上，访问对象的一个不存在的属性会得到 undefined ，那么这个 undefined 是肯定与第二个参数中任何值都不相等的。因此我觉得这里可以不用 hasOwnproperty 方法

## 优化 - 想通过 return 实现逻辑短路？封装吧 

## 思路提示 

* 如果我们把内层循环的内容写到 function 中，那么我们就可以使用 return 来实现逻辑短路了

## 代码 
```js
function where(collection, source){
    var result = [];

    functioncheckPush(obj){
        for (var key in source) {
            if (obj[key] !== source[key]) {
                return false;
            }
        }
        return true;
    }

    for (var i = 0; i < collection.length; i++) {
        if (checkPush(collection[i])) {
            result.push(collection[i]);
        }
    }

    return result;
}
```

## 解释 

* 封装前，请记得一定要考虑一下如何设置参数和返回值，以及是否有必要进行封装
* 既然我们要封装的是”是否该 push 当前这个对象”的逻辑，因此，函数接受的参数一定是对象本身，返回值就是 Boolean。这应该不难理解
* 个人觉得，既然 source 是不会改变，也不应该被改变的，因此我们可以直接在 checkPush 方法中调用父级作用域上的 source ，而不需要把 source 作为参数传入 checkPush 方法

## 优化 - 使用数组方法 

## 思路提示 

* 说到底，这道题不就是要过滤数组么？那么我们完全可以用数组的 filter 方法来做
* 另外，我们可以通过对象的 Object.keys() 来提取出对象的所有 key ，这个方法返回一个数组。当然，如果不这么做也是可以的，只是这样做会写起来更简便
* 既然这个方法返回数组，那么可玩性就大了很多。如果 source 有多个 key ，那我们需要每个都判断一遍。这样又要写循环了
* 但既然是数组，我们其实可以先用 map 把每个都转成 Boolean，然后用 reduce 方法来完成这件事

## 参考链接 

* [Array.filter()][6]
* [Array.map()][7]
* [Array.reduce()][8]
* [Object.keys()][9]

## 代码 

### ES6 
```js
function where(collection, source){
    var keys = Object.keys(source);

    return collection.filter(e=> {
        return keys.map(key=> {
            return e[key] === source[key];
        })
        .reduce((prev, next) => {
            return prev && next;
        }, true);
    })
}
```

## 解释 

* 首先我们要明确一点， filter 方法接受的回调函数，是需要返回 Boolean 的
* 既然我们生成了 keys 数组，那么不难理解，这个数组中有多少个元素，我们就要进行多少次判断。这就是之前代码的思路
* 所以我们只要把每次的判断都转成 Boolean 就可以了，这也就是上面代码中 map 方法回调函数的作用
* 然后，我们对 reduce 设置一个默认值为 true ，这个思路，可以参考之前的解法，就是设置 flag 的原理。那么在 reduce 中，第一次调用的 prev 就是 true
* 然后我们去比较 map 之后的数组，如果里面都是 true 的话，那么就肯定可以保留，因此 reduce 在这时候就会返回 true 。假如 map 之后的数组存在 false ，那就表示没有找到对应的 key ，或者找到了 key 但对应的值不相等。这是 reduce 就会返回 false
* 那么，我们就可以通过 reduce 的返回值，来判断是否要过滤掉当前元素了，因为这一切都是写在 filter 的回调函数中的

## 一行搞定 - 使用 every 

## 思路提示 

* 如果你不了解数组的 every 方法，请点击底下的链接去看一看，相信你看过之后就知道怎么写了

## 参考链接 

* [Array.every()][10]

## 代码 

### ES6 
```js
function where(collection, source){
    return collection.filter(e=> Object.keys(source).every(key=> e[key] === source[key]));
}
```

[1]: http://singsing.io/blog/fcc/intermediate-where-art-thou/?utm_source=tuicool&utm_medium=referral

[3]: https://www.freecodecamp.cn/challenges/where-art-thou
[4]: https://www.freecodecamp.com/challenges/wherefore-art-thou
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/hasOwnProperty
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/map
[8]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/reduce
[9]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/keys
[10]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/every
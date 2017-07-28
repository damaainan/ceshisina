# intermediate-drop-it

 时间 2017-07-09 09:50:59  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-drop-it/][1]

 主题 [JavaScript][2]

* 这个 function 接受一个数组参数 arr 和一个函数参数 func 。返回值为 arr 第一个满足参数 func 的元素及其之后的所有元素
* 如果 arr 是 [1, 2, 3, 4] ， func 是 function(n) {return n >=3;} ，那么此时返回值应为 [3, 4]
* 更值得关注的是这样的情况，如果 arr 是 [0, 1, 0, 1] ， func 是 function(n) {return n === 1;} ，那么此时返回值应为 [1, 0, 1]

## 基本解法 

## 思路提示 

* 这道题目和上一道 Finders Keepers 的基本思路很相似，难度也很低。区别仅仅在于 Finders Keepers 需要返回一个元素，这道题是返回一个数组
* 题目说明中提到，这道题可以用到 Array.shift() 。尽管我觉得不是很必要，但我们还是先来看看如何用这个方法以及循环写

## 参考链接 

* [Array.shift][3]

## 代码 

    function drop(arr, func){
        var arrCopy = arr.slice();
        for (var i = 0; i < arr.length; i++) {
            if(!func(arr[i])) {
                arrCopy.shift();
            } else {
                return arrCopy;
            }
        }
        return [];
    }
    

## 解释 

* 首先，这里需要存一份原数组的深拷贝。深拷贝的意思是，就算原数组修改，这份拷贝也不会被影响。你可以执行一下这段代码，来比较一下深拷贝 (Hard Copy, Deep Copy) 与浅拷贝 (Shallow Copy) 的区别
```js
    var arr = [1, 2, 3, 4];
    var shallowCopy = arr;
    var hardCopy = arr.slice();
    
    arr.shift();
    
    console.log(shallowCopy, hardCopy);
```

* 不难看出，其中 shallowCopy 会变化，而 hardCopy 不会变
* 至于为什么需要存一份深拷贝，原因很简单。我们在移除数组元素的时候，会使整个数组的长度变短。相当于，如果第一个元素被移除了，而我们又执行了 i++ ，那就会跳过原数组中第二个元素的判断，直接去判断原数组中的第三个了
* 如果我们存了一份拷贝，那么我们循环的参考依然是原来的那个 arr ，而执行删除元素的是那份深拷贝，因此互相之间是不会有任何影响的
* 顺便，深拷贝的方式有很多，我们也可以新建一个空数组，然后遍历 arr ，然后把其中的每个元素 push 到新建数组中。以下再列举六种深拷贝数组的方式：

```js
    var arr = [1, 2, 3, 4];
    // ES5
    var hardCopy1 = arr.slice();
    var hardCopy2 = arr.concat();
    var hardCopy3 = [].concat(arr);
    var hardCopy4 = JSON.parse(JSON.stringify(arr));
    
    // ES6
    var hardCopy5 = Array.from(arr);
    var hardCopy6 = [...arr];
```

* 多说一句，其实有些方法是有适用条件的。我个人比较喜欢用 arr.slice() ，但这种只适用于 arr 中都是 Primitive Type (原始类型) 元素的情况
* 当然，除了存一份拷贝，我们还有其他方式去解决，比如，删除元素的时候执行以下 i-- 可以是一种做法；再比如，从右边遍历也可以是一种做法。有兴趣的朋友可以自己写一下试试
* 最后需要单独返回一下 [] 。因为如果所有元素都不满足 func ，则应该返回 []

## 推荐写法 

## 思路提示 

* 题目还给出了使用 Array.slice 方法的提示。我们来看一看如何用这个方法解题
* 对于这道题目，关键就在于如何找到第一个使得测试函数 func 返回值为 true 的元素。只要找到了，我们只需要用一下 slice ，一直截取到数组终点就可以了

## 参考链接 

* [Array.slice][4]

## 代码 

    function drop(arr, func){
        for (var i = 0; i < arr.length; i++) {
            if (func(arr[i])) {
                return arr.slice(i);
            }
        }
        return [];
    }

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-drop-it/
[2]: /topics/11060004
[3]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/shift
[4]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
## FreeCodeCamp 高级算法题 - 计算轨道周期

来源：[http://singsing.io/blog/fcc/advanced-map-the-debris/](http://singsing.io/blog/fcc/advanced-map-the-debris/)

时间 2018-02-10 15:50:06



* 这个`function`接收一个对象数组`arr`。每个对象包含`name`和`avgAlt`属性。返回值也是一个对象数组，每个对象包含`name`和`orbitalPeriod`属性    

## 解题思路  

* 这可能是高级算法中最简单的题目之一。用遍历，或者`map`实现都可以    
* 如果不知道如何计算轨道周期，请先点开 wikipedia 的链接查看公式。题目要求的返回值，其实就是根据传入数据中每一个`avgAlt`的值计算出轨道周期，然后把属性改为`orbitalPeriod`，并保持`name`属性和值不变    
* 需要注意的是，公式`T = 2π * (a^3 / μ)^(-1/2)`中的`a`在题目中等于`avgAlt + earthRadius`。`μ`的值就是代码中给出的`GM`
* 如果采用 ES6 的写法，会有一个小坑，详情看博文最后的解释

## 解法 - 遍历  

## 代码  

```js

function orbitalPeriod(arr) {
    var GM = 398600.4418;
    var earthRadius = 6367.4447;
    // 用于存储结果
    var result = [];

    for (var i = 0; i < arr.length; i++) {
        var name = arr[i].name;
        var orbitalPeriod = Math.round(Math.sqrt(Math.pow((earthRadius + arr[i].avgAlt), 3) / GM) * 2 * Math.PI);
        result.push({
            name: name,
            orbitalPeriod: orbitalPeriod
        });
    }

    return result;
}

```

## 解法 - 数组 map 方法  

## 代码 - ES5  

```js

function orbitalPeriod(arr) {
    var GM = 398600.4418;
    var earthRadius = 6367.4447;

    return arr.map(function(obj) {
        return {
            name: obj.name,
            orbitalPeriod: Math.round(Math.sqrt(Math.pow((earthRadius + obj.avgAlt), 3) / GM) * 2 * Math.PI)
        };
    });
}

```

## 解法 - ES6  

```js

function orbitalPeriod(arr) {
    var GM = 398600.4418;
    var earthRadius = 6367.4447;

    return arr.map(obj => ({
        name: obj.name,
        orbitalPeriod: Math.round(Math.sqrt(Math.pow((earthRadius + obj.avgAlt), 3) / GM) * 2 * Math.PI)
    }));
}

```

## 解释  

* 使用箭头函数的时候，如果不加大括号，那箭头函数后面的就是返回值。当然，我们也可以用大括号包起来，并在里面加上`return`

```js

var arr = [1, 2, 3];

// 以下两种写法都返回 [2, 3, 4]
arr.map(num => num + 1);
arr.map(num => {
    return num + 1
});

```

* 因此，如果我们希望返回 object，就不能直接用对象字面量 (Object Literal，也就是大括号的形式) 定义返回值

```js

// 我们希望给每个 age 加 10
var json = [{name: 'a', age: 1}, {name: 'b', age: 5}];

// 这种写法是错误的，会得到 [undefined, undefined]
json.map(obj => {age: obj.age + 10});

```

* 上面的写法在编译到 ES5 的时候，会是这样的结果：

```js

json.map(function(obj) {
    age: obj.age + 10;
})

```

* 显然，这里是没有返回值的。因此，需要写成：

```js

// 返回 [{age: 11}, {age: 15}]
json.map(obj => ({age: obj.age + 10}));

```

* 当然，这道题用`forEach`也是可以的。个人更喜欢用`map`，因为`map`有返回值，而`forEach`没有    


## FreeCodeCamp 高级算法题 - 数组的对称差

来源：[http://singsing.io/blog/fcc/advanced-symmetric-difference/](http://singsing.io/blog/fcc/advanced-symmetric-difference/)

时间 2017-07-28 14:03:50



* 这个`function`接收一个参数`arg`，其中包含至少两个数组。返回值也为数组，即为给出数组的对称差    
* 如果`arg`是`[1, 2, 3]`和`[5, 1, 2, 4]`，则返回值为`[3, 4, 5]`

## 解题思路  

* 建议先去了解一下对称差的定义
* 根据定义，对于两个数组来说，对称差的意思就是：    

* 要么在第一个数组中，要么在第二个数组中。换句话说，在第一个数组中排除在第二个数组的元素，然后在第二个数组中排除在第一个数组的元素
* 从另一个角度考虑，其实就是先把两个数组中所有元素都合并成一个数组，然后排除掉既在第一个数组也在第二个数组的元素

* 对于多个数组的操作也不难理解。设`A`、`B`和`C`分别代表三个数组，设`sym()`为求对称差的函数。那么`sym(A, B, C)`就相当于`sym(sym(A, B), C)`
* 需要注意的是，如果数组中有重复元素，那么在结果中是要过滤的。举个例子，如果传入`[1, 1, 2]`和`[2, 3]`，那么结果应该是`[1, 3]`
* 先来看看对于上面提到的两种求对称差的逻辑分别如何实现

## 参考链接  

* [Array.concat][0]

* [Array.filter][1]

* [Array.indexOf][2]

## 代码 - 对称差公共逻辑 - 思路一  

```js

function getSym(arr1, arr2){
    // 得到在第一个数组，但不在第二个数组中的元素
    var result = arr1.filter(function(e){
        return arr2.indexOf(e) === -1;
    });

    // 得到在第二个数组，但不在第以个数组中的元素
    result.concat(arr2.filter(function(e){
        return arr1.indexOf(e) === -1;
    }))

    // 去重
    return result.filter(function(e, i){
        return result.indexOf(e) === i;
    })
}

```

## 代码 - 对称差公共逻辑 - 思路二  

```js

function getSym(arr1, arr2){
    return arr1.concat(arr2)
        .filter(function(e){
            // 排除既在第一个也在第二个数组中的元素
            return !(arr1.indexOf(e) > -1 && arr2.indexOf(e) > -1);
        })
        .filter(function(e, i, arr){
            return arr.indexOf(e) === i;
        })
}

```

## 解释  

* 第一个思路应该不难理解。我们先把在`arr1`中但不在`arr2`中的元素直接作为初始值赋给`result`。然后，把在`arr2`中但不在`arr1`中的元素添加到`result`里。最后，去重    
* 去重的写法可能有点儿不好理解。原理是这样，对于有相同元素的数组，`Array.indexOf`总是返回重复元素中第一个元素的索引。`filter`方法的回调函数，第一个参数是元素本身，第二个参数是当前的索引。通过判断这个当前索引和`Array.indexOf`返回值是否相等 (相等即保留) 就可以实现去重    
* 第二种写法，注意到第二个`filter`方法的回调函数中，我们多设置了一个参数。如果没有很多次链式调用，需要传入第三个参数的情况不是很多，因为我们可以直接通过调用者的变量名去调用    
* 注意，这里我们不可以用`this`。原因很简单，因为任何匿名函数的`this`指向的都是全局对象，比如`window`

## 初级解法 - filter，循环  

## 思路提示  

* 参数是两个数组的情况很好解决，但参数还可能是多个数组。按照一开始的思路，我们要先对前两个求一次`sym`，再对计算结果 (当然，这个计算结果是一个一维数组) 和下一个数组再求一次`sym`
* 其实这就符合递归的模型，因为我们不确定需要做同样的操作多少次。当然，用循环也是肯定可以解的
* 我们在上面已经封装好了求两数组对称差的代码，只要把这部分应用到题目中就可以了

## 代码  

```js

function sym(){
    // 设置初始值
    var result = [];
    for (var i = 0; i < arguments.length - 1; i++) {
        if (result.length > 0) {
            // 表示已经调用过 getSym，因此需要传入 result 进行迭代
            result = getSym(result, arguments[i + 1]) ;
        } else {
            // 表示未调用过 getSym，换句话说，这时候就是 i 为 0 的情况
            result = getSym(arguments[i], arguments[i + 1]);
        }

    }

    // 按照第二个思路封装的 getSym
    function getSym(arr1, arr2){
        return arr1.concat(arr2)
            .filter(function(e){
                return !(arr1.indexOf(e) > -1 && arr2.indexOf(e) > -1);
            })
            .filter(function(e, i, arr){
                return arr.indexOf(e) === i;
            });
    }
    return result;
}

```

## 解释  

* 需要注意的是，既然我们会在`for`循环中调用`i+1`，那么，我们就不能用`arguments.length`作为跳出条件了，因为当`i`为`arguments.length - 1`的时候，`i + 1`为`arguments.length`。对于一个数组`arr`，显然`arr[arr.length]`是`undefined`
* 类似地，如果我们需要在循环中调用`i - 1`，那我们就要把初始值设置为`1`，而不是`0`。`arr[-1]`虽然不会报错，但它会访问数组的最后一个元素，很可能会影响结果    

## 中级解法 - reduce  

## 思路提示  

* 其实应该很多朋友已经反应过来了，上面的就是在`reduce`。对于`for`循环那里，我们可以这样写：    

```js

var result = arguments[0];

for (var i = 1; i < arguments.length; i++) {
    result = getSym(result, arguments[i]);
}

```

* 这就不难看出，其实就是在迭代`result`，且`result`具有初始值`arguments[0]`

[0]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/concat
[1]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
[2]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
# FreeCodeCamp 初级算法题 - 猴子吃香蕉, 分割数组

 时间 2017-03-22 12:05:03  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/2017/03/21/chunky-monkey/][1]

 主题 [算法][2]

* [中文链接][3]
* [英文链接][4]
* 级别：初级 (Basic Algorithm Scripting)

> 猴子吃香蕉可是掰成好几段来吃哦！

> 把一个数组arr按照指定的数组大小size分割成若干个数组块。

> 例如:chunk([1,2,3,4],2)=[[1,2],[3,4]];

> chunk([1,2,3,4,5],2)=[[1,2],[3,4],[5]];

> chunk(["a", "b", "c", "d"], 2) 应该返回 [["a", "b"], ["c", "d"]].
> chunk([0, 1, 2, 3, 4, 5], 3) 应该返回 [[0, 1, 2], [3, 4, 5]].
> chunk([0, 1, 2, 3, 4, 5], 2) 应该返回 [[0, 1], [2, 3], [4, 5]].
> chunk([0, 1, 2, 3, 4, 5], 4) 应该返回 [[0, 1, 2, 3], [4, 5]].
> chunk([0, 1, 2, 3, 4, 5, 6], 3) 应该返回 [[0, 1, 2], [3, 4, 5], [6]].
> chunk([0, 1, 2, 3, 4, 5, 6, 7, 8], 4) 应该返回 [[0, 1, 2, 3], [4, 5, 6, 7], [8]].



## 问题解释 

* 这个 function 接收两个参数，第一个参数为数组 arr ，即为需要分割的原数组。第二个参数为数字 size ，表示分割后每段的长度。返回值为分割后形成的二维数组
* 比如接收的是 [1, 2, 3, 4, 5, 6] 与 2 ，那么输出就是 [[1, 2], [3, 4], [5, 6]]
* 需要注意的是，如果分割的过程出现剩余，那么返回值的最后一个数组会比较短。比如接收的是 [1, 2, 3] 与 2 ，那么输出就是 [[1, 2], [3]]

## 参考链接 

* 对于基本解法，我们先不用数组内建方法，只通过最基本的循环来实现，因此，你只需要知道 for 循环和 push 就够了
* [Array.push()][5]

## 思路提示 

* 只需要模拟一下执行过程，就知道我们只需要写一个循环，实现先取出数组的 0 到 size 位，然后给两个值都加上 size ，也就是取出 size 到 size + size 位，直到数组终点。这样，每次截取的长度都是 size
* 因此，需要嵌套循环。外层的循环用于确定范围，内层的循环就是把范围内的元素每个都 push 到临时数组中，再在内层循环结束后把生成的数组 push 到结果中就可以了
* 写的时候需要注意，一定要判断边界。否则你会得到 undefined
* 这个思路其实就是双指针，请先试着自己写一下，再看答案

## 参考答案 

### 基本答案 
```js
    function chunk(arr, size){
        // 这个数组用于存储结果
        var result = [];
    
        for (var i = 0; i < arr.length; i += size) {
            // 这个数组用于存储临时的数组片段
            var temp = [];
            for (var j = i; j < i + size; j++) {
                // 判断边界。可以思考一下，为什么不能写成 if (arr[j])
                if (j < arr.length) {
                    temp.push(arr[j]);
                }
            }
            result.push(temp);
        }
    
        return result;
    }
    
```
#### 解释 

* 整体思路应该不难理解。外层的 i 用于确定截取的起始点。内层的 j 就是在 i 与 i + size 之间，读出每一个元素，并存储到 temp 中。每存好一个片段 (即内层循环结束)，就把 temp 保存到 result 中
* 这个思路的关键在于选对 i 和 j 的初始值以及循环过程中增值。既然我们需要实现每 size 个元素为一个片段，那么我们肯定需要确定每次截取的起点和终点
* 我们用 i 来表示截取的起点，那么不难得出 i 的初始值为 0。增值应该为 size 。跳出条件也很简单，只要 i < arr.length ，我们就可以一直执行
* 用 j 来选取范围中的元素，因此 j 的初始值应该就为 i (注意，这个 i 是会变化的)。至于 j 的结束值，那么应该为 i + size 。因为我们需要截取的是从 i 到 i + size 的元素。 j 的增值显然应该为 j++ ，因为我们需要获取这个范围之内的所有元素
* 既然我们设置了 j 是从 i 到 i + size ，那么就需要处理 i + size 超出数组长度的情况。简单考虑下这个例子，传入数组 [1, 2, 3, 4, 5] ， size 为 4 ，那么我们应该得到 [[1, 2, 3, 4], [5]] 。当 i 为 0 的时候没有问题，但当第二次循环，即 i 为 4 的时候，这时 j 也为 4。而内层循环跳出条件， i + size 为 8，显然超出了原数组的长度。如果我们读取 arr[6] ，会得到 undefined ，显然我们不想把这个结果放进 temp
* 那么如何判断呢？你可能第一反应是用 if (arr[j]) 来判断。可以先试试，结果是通不过测试的。原因在于 JavaScript 的隐式类型转换， if() 中的内容会被转换成 Boolean ，相当于执行的是 if(Boolean(arr[j])) 或者说 if(!!arr[j]) 。那么如果 arr[j] 是 0，就也会返回 false。但其实， 0 在数组中肯定是允许的情况，我们不应该把它排除掉
* 因此，这里我们只需要简单地判断 j 是否超出了 arr.length 即可。也就有了上面的代码 

#### 多说一句 

我知道，上面解释的内容有点多，看起来好像很复杂。但其实，自己写一写就知道了，这个思路是最容易想出来的

### 优化 - 使用 slice 

* 没错，上面我们就是造了一个 .slice() 的轮子 u.u 

#### 参考链接
* [Array.slice()][6]#### 思路提示
* 之所以推荐用 slice ，是因为就算第二个参数超出了数组的长度也没有关系，不会产生 undefined 之类的问题
* 基本答案中， j 那一层循环的代码，其实就是 .slice() 。我们来用 slice 替换掉那部分吧 
```js
    function chunk(arr, size){
        var result = [];
        // 设置起点
        var i = 0;
    
        while (i < arr.length) {
            result.push(arr.slice(i, i + size));
            i += size;
        }
    
        return result;
    }
```
#### 解释 

* 用 while 写循环要注意什么？一定不要忘了写 i += size 。否则就是无限循环了。另外，记得要先给 i 设置一个初始值 0
* 由于 .slice() 返回截取后的数组，因此我们直接 push 就可以了。给一个数组 push 一个数组，就会形成二维数组。顺便，如果你想生成一维数组，请用 .concat()
* 这个方法看起来简洁很多了。当然，还有更简洁的写法

### 换个写法 - 使用 splice 

* .splice() 是为数不多的，会直接改变原数组的数组方法。我们可以通过 .splice() 来删除数组中的元素，返回值又恰好是被删除的数组部分。具体语法请参考下面的链接 

#### 参考链接
* [Array.splice()][7]
```js
    function chunk(arr, size){
        var result = [];
    
        while (arr.length) {
            result.push(arr.splice(0, size));
        }
    
        return result;
    }
```


[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/2017/03/21/chunky-monkey/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11000083
[3]: https://www.freecodecamp.cn/challenges/chunky-monkey
[4]: https://www.freecodecamp.com/challenges/chunky-monkey
[5]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/push
[6]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
[7]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/splice
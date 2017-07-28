# intermediate-finders-keepers

 时间 2017-07-08 12:01:26  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-finders-keepers/][1]

 主题 [软件开发][2]

* 这个 function 接受一个数组参数 arr 和一个函数参数 func 。返回值为 arr 中满足参数 func 的第一个元素
* 如果 arr 是 [1, 2, 3] ， func 是 function(num) {return num === 2;} ，那么返回值因为 2

## 基本解法 

## 思路提示 

* 这道题目非常简单，我们先来看看如何用循环写

## 代码 

    function find(arr, func){
        for (var i = 0; i < arr.length; i++) {
            if (func(arr[i])) {
                return arr[i];
            }
        }
    }
    

## 解释 

* 这里应该真的不需要太多解释。一个很简单的逻辑短路。由于从左开始遍历，那么最先为 true 的肯定符合”第一个满足 func “ 这个条件
* 因此，这个时候，我们直接把它 return 出来就行，不需要再进行后续的判断

## 基本解法 2 - filter 

## 思路提示 

* 其实，上面我们相当于造了一个数组 filter 方法的轮子。 filter 方法本身不难理解，应用场景也很多，建议采用这个写法
* 只需要注意， filter 方法是返回所有符合条件的，而题目要求返回第一个

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-finders-keepers/
[2]: /topics/11000151
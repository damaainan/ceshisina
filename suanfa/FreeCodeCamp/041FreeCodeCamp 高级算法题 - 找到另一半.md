## FreeCodeCamp 高级算法题 - 找到另一半

来源：[http://singsing.io/blog/fcc/advanced-pairwise/](http://singsing.io/blog/fcc/advanced-pairwise/)

时间 2018-02-14 12:49:14



* 这个`function`接收一个整数数组参数`arr`和一个数字参数`arg`。返回值为和为`arg`的数组元素的参数 (index) 总和    
* 根据测试用例，还有一个隐含信息。用过的数字不可再用于下次计算

## 解题思路  

* 这是高级算法最后一道题，这道题和 leetcode 里的 two sum 比较类似。唯一区别就是 two sum 的 pair 唯一，这里可能不唯一
* 比较容易想出来的写法是嵌套两次循环。外层循环遍历传入的数组，内层循环遍历当前数字之后的数字，看两个数字的和是否等于`arg`。如果等于，就可以求出 index 的总和了。然后继续外层的遍历    
* 注意到测试用例`pairwise([1, 1, 1], 2)`应该返回`1`。我们先把数组元素和 index 列出来。其中第一行为 index，第二行为元素：

| 0 | 1 | 2 |
| - | - | - |
| 1 | 1 | 1 |

* 由于用过的数字不可再用于下次计算，因此，这里的返回值应为`0 + 1`即为`1`
* 同时，还有一个比较重要的推论，就是如果数组中有重复元素，选择未使用过且 index 最小的来完成当前配对     
* 再看下一个测试用例，`pairwise([0, 0, 0, 0, 1, 1], 1)`应该返回`10`。我们还是列个表，第一行为 index，第二行为元素：

| 0 | 1 | 2 | 3 | 4 | 5 |
| - | - | - | - | - | - |
| 0 | 0 | 0 | 0 | 1 | 1 |

* 同理，这里的返回值是`(0 + 4) + (1 + 5)`
* 既然我们要先选择 index 最小的，而且用过的不能用。那最简单的方式就是，我们把用过的数字赋一个`undefined`。这样下次遍历的时候，`undefined`与任何数求和都会是`NaN`
* 需要注意的是，赋值成`NaN`也行，但不要赋值`null`或者`0`之类的。可以自己试一下`null + 1`等于什么。如果我们是在用`===`比较两数之和与`arg`，那把用过的数赋值成一个字符串也没什么毛病。只是我个人觉得这样不够好    
* 个人的习惯是，如果要改传入函数的参数，我会在函数里创建一个备份，然后去修改备份的数据，这样可以保持源数据不变。如果真的需要改变源数据，我也会通过返回值的方式，给源数据重新赋值

## 解法 - for 循环  

## 代码  

```js

function pairwise(arr, arg) {
	// 由于 arr 中元素全部为数字 (原始类型)，因此这样写就可以创建 arr 的 hard copy
	var array = arr.slice();
	var result = 0;

	for (var i = 0; i < array.length; i++) {
		for (var j = i + 1; j < array.length; j++) {
			if (array[i] + array[j] === arg) {
				// 把用过的数字赋值成 undefined
				array[i] = array[j] = undefined;
				result += (i + j);
				// 跳出当前的 for 循环。继续外层的，也就是 i 的那一层
				break;
			}
		}
	}
	return result;
}

```

## 解法 - 数组 reduce 方法  

* 和上面的思路基本一致。只是换了种写法

## 代码  

```js

function pairwise(arr, arg) {
    return arr.slice().reduce(function(accum, current, index, array) {
        var targetIndex = array.indexOf((arg - current), index + 1);
        if (targetIndex === -1) {
            // 如果 arg - current，即需要找的用于配对的数字不存在于数组中
            return accum;
        } else {
            // 把用于配对的数字重新赋值为 undefined
            array[targetIndex] = array[index] = undefined;
            // 返回总和，用于下次计算
            return accum + index + targetIndex;
        }
    }, 0);
}

```

## 解释  

* 可能只有一点需要说明的，就是`indexOf`方法的第二个参数。这种写法我见到的比较少，它决定的是从哪里开始找。详情请去 MDN 看下`indexOf`的文档    


## [CodeWars 系列：Reducing by rules to get the result (6 kyu)](https://blog.stephencode.com/p/codewars_reducing_by_rules_to_get_the_result.html) 

2017/07/10 

## 题目介绍

> Your task is to reduce a list of numbers to one number.  
> For this you get a list of rules, how you have to reduce the numbers.  
> You have to use these rules consecutively. So when you get to the end of the list of rules, you start again at the beginning.

你的任务是通过一组规则将一组数字减少到一个数字。当未缩减到一个数字前，你将连续的使用这些规则，也就意味着当你用到了这组规则的最后一个时，下一次又将从第一个规则开始。

> Both lists/arrays are never null and will always contain valid elements.  
> The list of numbers will always contain more than 1 numbers.  
> In the list of numbers will only be values greater than 0.  
> Every rule takes always two input parameter. 

我向你保证：

    这些数组元素不会为 NULL，且一定是有效元素；
    这组数字至少包含一个数字；
    列表中的数组都是大于0的；
    每一个规则有且仅有两个入参；
> An example is clearer than more words…

好了，一例胜千言 :)

```
    numbers: [ 2.0, 2.0, 3.0, 4.0 ]
    rules: [ (a,b) => a + b, (a,b) => a - b ]
    result: 5.0
    You get a list of four numbers.
    There are two rules. First rule says: Sum the two numbers a and b. Second rule says: Subtract b from a.
    The steps in progressing:
    1. Rule 1: First number + second number -> 2.0 + 2.0 = 4.0
    2. Rule 2: result from step before - third number -> 4.0 - 3.0 = 1.0
    3. Rule 1: result from step before + forth number -> 1.0 + 4.0 = 5.0
```

## 解题思路

刚开始着手解决这一个问题的时候，难的其实不是题目，而是读懂题目。。。（这里需要吐槽一下，混编程行业的，英语说得再怎么烂都能忍，但阅读英文这个技能是必须掌握的。）

读懂题意后，首先解决几个问题，第一，如何知道得出最后的结果了，这个没什么难度，有两种思路，一种是判断数组长度是否为1（每一次循环都会将数组长度减一），另一种是循环数组长度减一的次数（题干中制定规则的入参只有两个，所以循环的次数其实是确定的）。

第二个需要解决的问题是如何让规则数组不停地依次循环，很自然的，我会想到用下面这种方式：

PHP 7 版：

```
    $index = 0;
    $rules_number = count($rules);
    // $rules[$index] will be loop automatically
    if ($index >= $rules_number - 1) {
        $index = 0;
    } else {
        $index++;
    }
```
那么初版本就是这样子的：

PHP 7 版：

```
    function reduce_by_rules($numbers, $rules) {
        $index = 0;
        $rules_number = count($rules);
        while(count($numbers) > 1) {
            $num = array_shift($numbers);
            $numbers[0] = ($rules[$index])($num, $numbers[0]);
            if ($index >= $rules_number - 1) {
                $index = 0;
            } else {
                $index++;
            }
        }
        return $numbers[0];
    }
```
上面这种让数组自循环是通过置零法的方式，但也可以利用取余（MOD）运算法来实现：

PHP 版：

```
    function reduce_by_rules($numbers, $rules) {
        $index = 0;
        $rules_number = count($rules);
        while(count($numbers) > 1) {
            $num = array_shift($numbers);
            $numbers[0] = call_user_func($rules[$index++ % $rules_number], $num, $numbers[0]);
        }
        return $numbers[0];
    }
```
这边使用了 call_user_func() 函数，因为直接使用匿名函数的引用在 PHP 7 以前是会报错的，所以为了兼容性，改了一下。

## 总结

对于一些索引的变换，巧妙使用运算符往往能大大减少代码量和提高性能。


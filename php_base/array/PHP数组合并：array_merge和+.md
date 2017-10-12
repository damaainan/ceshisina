# PHP数组合并：array_merge和+

 时间 2017-03-17 16:23:28  

原文[https://wxb.github.io/2017/03/17/PHP数组合并：array-merge和.html][1]


在PHP日常开发中，数组的处理绝对是频率非常高的一类；我们经常把对象或者字符串转换成数组来处理，是因为php提供了非常丰富的数组处理函数。掌握和熟练地使用这些数组函数不仅让我们的编码变得更加简洁和优美，同时相对于 for 循环来处理数组来说在性能上有很大的提高！毕竟这些函数的实现实在 **`底层opcode`**实现的。 

PHP的数组处理函数大多数比较清晰和简单，手册说明也比较清楚；但是有些函数的使用却存在一些隐性的要求，如果不了解函数的特殊情况会让我们编写的代码不够健壮。今天就来整理一下PHP关于数组合并的函数： `array_merge` ；同时引出一个数组相加： `+` 。 

## array_merge 合并一个或多个数组 

array array_merge ( array $array1 [, array $... ] )  
array_merge() 将一个或多个数组的单元合并起来，一个数组中的值附加在前一个数组的后面。返回作为结果的数组。 

## 合并包含非数组类型 

```php
    $arr1 = [
            'name' => 'wangxb',
            2,
            3
        ];
    // 这里假设$arr2等于一个表达式，但是在某些情况下表达式会得到一个空字符串
    $arr2 = '';
    var_dump(array_merge($arr1, $arr2));  // null
    var_dump(array_merge($arr2, $arr1));  // null
```

运行结果：

![][3]

⚠️ 可以看到报了一个php的Warning级别错误，这个错误并不会导致程序崩溃，但是会导致 `array_merge` 函数计算的结果和你使用该函数的预期不一致，上面的两个数组合并其中一个为空时，合并的结果竟然都成了 `null` ，所以请特别注意这一点！ 

优化一下：

```php
    $arr1 = [
            'name' => 'wangxb',
            2,
            3
        ];
    $arr2 = '';
    var_dump(array_merge((array)$arr1, (array)$arr2));  // array('name' => 'wangxb',2,3)
    var_dump(array_merge((array)$arr2, (array)$arr1));  // array('name' => 'wangxb',2,3)
```

利用 (array) 这种强制类型转换就可以保证array_merge函数的参数都为数组。 

## 合并数组为空或不存在 

```php
    $arr1 = [
            'name' => 'wangxb',
            2,
            3
        ];
    $arr2 = null;
    var_dump((array)$arr2);  // array()
    var_dump((array)$arr3);  // array()
    var_dump(array_merge($arr1, $arr2));  // null
    var_dump(array_merge($arr1, $arr3));  // null
    var_dump(array_merge((array)$arr1, (array)$arr2));  // array('name' => 'wangxb',2,3)
    var_dump(array_merge((array)$arr1, (array)$arr3));  // array('name' => 'wangxb',2,3);
```

结果：

![][4]

这种情况和上一种情况一样，无论变量为null，还是未定义，还是参数是非数组字符串都不会发生致命错误，程序不会崩溃，但是结果和你使用 `array_merge` 这个函数的初衷向背，合并之后竟然成了 `null` ，数组 `$arr1` 中的信息都没有了，这肯定是不对的！ 

所以在使用前进行强制类型转换是很有必要的： **`(array)`** ，这样就可以保证这个合并函数是按照我们预想的情况处理，比如把这些等于空的各种数据类型统一按照空数组去合并。 

## 覆盖和重新索引 

`array_merge` 在处理key=>value这种关联数组时，存在相同key的健值后面的数组健值会替换覆盖前面的健值；但是对于key是数字即索引数组时，相同的key的健值不会覆盖，后面参数数组中的数字健值会重新索引追加到生成数组的后面排列。 

```php
    $arr1 = ['name' => 'wangxb', 'age'=>25, 2, 3];
    
    $arr2 = [1, 2, 3, 'age'=>26, 4];
    print_r(array_merge($arr1, $arr2));
    print_r(array_merge($arr2, $arr1));
```

结果

![][5]

## 重新索引一个数组的索引 

```php
    $arr1 = ['name' => 'wangxb', 'age'=>25, 2=>2, 3=>3];
    $arr2 = [5=>0,4=>1,3=>2,2=>3,1=>4,0=>5];
    print_r(array_merge($arr1));
    print_r(array_merge($arr2));
```

结果

![][6]

这个应该很清楚，`array_merge`函数在只有一个参数时，会对数字健值的索引重排列生成新数组！

## 数组完全合并：+ 

看了上面对 `array_merge` 函数的说明，应该很清楚，这个函数在合并一组数组时会将相同的健值非数字的项后项覆盖前项的健值；如果你想完全保留原有数组并只想新的数组附加到后面，那就得用 `+` 运算符。 

`+` 运算符计算结果时，第一个数组的键名将会被保留。在两个数组中存在相同的键名时，第一个数组中的同键名的元素将会被保留，第二个数组中的元素将会被忽略 

弄清楚这点非常重要，有时候我们想要后面数组值覆盖前面数组相同值，有时候这种覆盖使用的不恰当就会带来安全问题或者数据的泄露，而此时 `+` 可能更符合 

举个例子：假设现在某个页面需要查询 **待分配** 客户，我们写了这个页面的先决条件： $cond ，然后和页面上客户的查询条件合并后查询；如果客户端通过postman这种工具手动添加一个： status=>'施工中' ，就会覆盖我们的条件，也许这部分数据我们并不想给别人看，造成数据泄露； 

![][7]

## 比较总结 

`array_merge`和 `+` 一个根本的区别并不是存在覆盖和不覆盖的问题，而是基准的问题： 

* **array_merge** 存在相同项覆盖时，它是以数组参数列表最后一个健值为最后值；
* `+` 操作符，存在覆盖时，是以相同项最靠前的为基准的，为最后值；

```php
    $arr1 = ['name' => 'wangxb', 'age'=>25, 2, 3];
    $arr2 = [1, 2, 3, 'age'=>26, 4];
    $arr3 = [10, 11,12, 13];
    $res1 = $arr1+$arr2+$arr3;
    $res2 = $arr1+$arr3+$arr2;
    print_r($res1);
    print_r($res2);
```

结果

![][8]

PHP提供处理数组的函数非常多，我们需要了解这些函数能做什么，还需要清楚这些函数有什么特性，以防止我们在处理数据时，某些表达式的极端情况下出现不符合我们程序设计预期的结果，甚至一些安全漏洞！


[1]: https://wxb.github.io/2017/03/17/PHP数组合并：array-merge和.html
[3]: ../img/qmeiUzb.png
[4]: ../img/yeEZNnU.png
[5]: ../img/32a222u.png
[6]: ../img/yyI7VbJ.png
[7]: ../img/BBfUNrf.png
[8]: ../img/bEfEVz3.png
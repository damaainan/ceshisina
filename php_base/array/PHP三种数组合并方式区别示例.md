## PHP三种数组合并方式区别示例

来源：[https://segmentfault.com/a/1190000014838713](https://segmentfault.com/a/1190000014838713)


## 一、写在前面

目前工作中接触到的PHP数组合并方式主要有三种：
1、+操作符
2、array_merge()
3、array_merge_recursive()

它们的区别主要体现在对于相同键名（数字键名、字符串键名）的处理方式，下面本文将以两个实际例子来体现~
## 二、相同字符串键

```php
<?php

$arrFirst = [
    "first_key"  => 1,
    "second_key" => 1,
    "third_key"  => 1,
];

$arrSecond = [
    "first_key"  => 2,
    "second_key" => 2,
    "fourth_key" => 2,
];

//对于重复的字符串键，array_merge后，后面数组的键值会覆盖前面的
echo sprintf("\narray_merge result：\n%s", print_r(array_merge($arrFirst, $arrSecond), true));

//对于重复的字符串键，+操作后，前面数组的键值会覆盖后面的
echo sprintf("\narray + result：\n%s", print_r($arrFirst + $arrSecond, true));

//对于重复的字符串键，array_merge_recursive后，相同键名的键值会被合并到同一数组中（会递归）
echo sprintf("\narray_merge_recursive result：\n%s", print_r(array_merge_recursive($arrFirst, $arrSecond), true));

```

运行结果：

![][0]
## 三、相同数字键

```php
<?php

$arrFirst = [
    111 => "first",
    222 => "first",
    "first" //会指定默认的数字键223
];

$arrSecond = [
    111 => "second",
    333 => "second",
    "second" //会指定默认的数字键334
];

//对于重复的数字键，+操作后，前面数组的键值会覆盖后面的，保留之前数字键
echo sprintf("\narray + result：\n%s", print_r($arrFirst + $arrSecond, true));

//对于重复的数字键，array_merge后，重排数字键，不会覆盖
echo sprintf("\narray_merge result：\n%s", print_r(array_merge($arrFirst, $arrSecond), true));

//对于重复的数字键，array_merge_recursive后，重排数字键，不会覆盖
echo sprintf("\narray_merge_recursive result：\n%s", print_r(array_merge_recursive($arrFirst, $arrSecond), true));
```

运行结果：

![][1]
## 四、附：PHP数组基础概述

一、KEY
1、Key只能为Integer或String，可同时存在
2、包含合法整型值的字符串Key会被转化为整型存储，如：'6'的Key会被转化为6存储
3、布尔值Key会被转化为整型存储，true会被转化为1存储，false会被转化为0存储
4、Null会被转化为空串存储
5、如果多个Key值被转化后为相同的值，则只使用最后一个，前面的被覆盖，如：一个数组中依次定义了'1'、true、1的key，则最后只会存储key为1的value，之前的值都被覆盖
6、如果未指定Key，则会被自动设定为之前用过的最大的整型Key+1，最小为0二、VALUE
 1、可以为PHP任意类型（字符串、整形、浮点型、布尔型、对象、数组、NULL、资源类型）

三、其他类型转化为数组类型
1、Integer、String、Float、Boolean、Resource类型转化为数组类型时，会被自动分配Key为0，Value为其原值
2、Object类型转化为数组类型时，成员变量名会作为数组Key，私有属性的Key会加上类名前缀，保护属性的Key会加上'*'前缀
3、Null会被转化为空数组

四、foreach 
1、两种形式，foreach($arr as $key => $value)与foreach($arr as $value)，其中$value是值传递，使用`&$value`是引用传递 
2、遍历依赖数组内部指针
3、遍历结束后，`$value`会被保留，为防止在多次foreach时出现问题，可以在一次foreach后`unset($value)`或使用不同的变量

[0]: ./img/bVbaqkd.png
[1]: ./img/bVbaqmr.png
## PHP&amp;&amp;&amp;数组

来源：[https://blog.csdn.net/luyaran/article/details/79787080](https://blog.csdn.net/luyaran/article/details/79787080)

时间 2018-04-02 11:44:58


  
## 概述

我们知道，在 PHP 编程语言中，数组的使用频率是很高的，几乎每个脚本都会使用到。 PHP 自带了大量的、优秀的操作数组的函数以供我们使用，本文就对这些数组函数的使用做一些分类和总结，方便大家以后查阅。

  
## 创建

1.`range()`建立一个指定范围的数组：

```php
$arr1 = range(0, 10);     # array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)

$arr2 = range(0, 10, 2);  # array(0, 2, 4, 6, 8, 10)

$arr3 = range('a', 'd');  # array('a', 'b', 'c', 'd')

$arr4 = range('d', 'a');  # array('d', 'c', 'b', 'a')
```

2.`compact()`创建一个包含变量名和它们值的数组：

```php
$number = 10;
$string = "I'm PHPer";
$array  = array("And", "You?");
$result = compact("number", "string", "array"); # array('number'=>10, 'string'=>"I'm PHPer", 'array'=>array("And", "You?"))
```

3.`array_combine()`创建一个用一个数组的值作为其键、另一个数组的值作为其值的数组：

```php
$key    = array("1", "3", "5", "7", "9");
$value  = array("I", "Am", "A", "PHP", "er");
$result = array_combine($number,$array);     # array('1'=>I, '3'=>'Am', '5'=>'A', '7'=>'PHP', '9'=>'er')
```

  
## 遍历

1.`for`循环

```php
$arr = range(0, 10);
for($i = 0; $i < count($arr);  $i++) {
    echo $arr[$i];
}
```

缺点：只能遍历索引数组。

2.`while`循环

```php
$products = array('apple'=>3, 'milk'=>6, 'eggs'=>10);
while(list($product, $quantity) = each($products)) {
    echo $product . '-' . $quantiry;
}
```

缺点：遍历完成之后，不能对数组进行第二次遍历（数组内部指针指向了最后一个元素）。

3.`foreach`循环

```php
$products = array('apple'=>3, 'milk'=>6, 'eggs'=>10);
foreach($products as $product => $quantity) {
    echo $product . '-' . $quantiry;
}
```

  
## 操作 key 或 value

`unset()`— 删除数组成员或数组
`in_array()`— 检查数组中是否存在某个值
`array_key_exists()`— 检查给定的键名或索引是否存在于数组中
`array_search()`— 在数组中搜索给定的值，如果成功则返回相应的键名

```php
$array = array(1, 2, 3);
unset($array); # array()

$fruit = array('apple' => 'goold','orange' => 'fine','banana' => 'OK');
if(in_array('good', $fruit)) {
    echo 'Exit';
}

$search_array = array('first' => 1, 'second' => 4);
if (array_key_exists('first', $search_array)) {
    echo "Exit";
}

$array = array(0 => 'blue', 1 => 'red', 2 => 'green', 3 => 'red');
$key = array_search('green', $array); # $key = 2;
```

`array_keys()`— 返回数组中部分的或所有的键名
`array_values()`— 返回数组中所有的值

```php
$array  = array('apple'=>'good', 'orange'=>'fine', 'banana'=>'ok');
$keys   = array_keys($array);   # array('apple', 'orange', 'banana')
$values = array_values($array); # array('good', 'fine', 'ok')
```
`array_unique()`— 移除数组中重复的值

```php
$input  = array(4, '4', '3', 4, 3, '3');
$result = array_unique($input); # array(4, '3')
```
`array_flip()`— 交换数组中的键和值

```php
$input  = array('oranges', 'apples', 'pears');
$result = array_flip($input); # array('oranges'=>0, 'apples'=>1, 'pears'=>2)
```
`array_count_values()`统计数组中所有的值

```php
$input  = array(1, 'hello', 1, 'world', 'hello');
$result = array_count_values($input); # array('1'=>2, 'hello'=>2, 'world'=>1)
```

  
## 排序

1.`sort()`和`rsort()`对数组进行升序或降序排序：

```php
$fruits = array();
sort($fruits);  # array('apple', 'banana', 'lemon', 'orange')
rsort($fruits); # array('orange', 'lemon', 'banana', 'apple')  
```

2.`asort()`和`arsort()`对关联数组（按元素的值）进行升序或降序排序并保持索引关系：

```php
$fruits = array('d'=>'lemon', 'a'=>'orange', 'b'=>'banana', 'c'=>'apple');
asort($fruits);  # array('c'=>''apple', 'b'=>''banana', 'd'=>'lemon', 'a'=>'orange')
arsort($fruits); # array('a'=>'orange', 'd'=>'lemon', 'b'=>''banana', 'c'=>''apple')
```

3.`ksort()`对数组按照键名排序：

```php
$fruits = array('d'=>'lemon', 'a'=>'orange', 'b'=>'banana', 'c'=>'apple');
ksort($fruits); # array('a'=>'orange', 'b'=>'banana', 'c'=>'apple', 'd'=>'lemon')
```

4.`shuffle()`随机打乱数组排序：

```php
$numbers = range(1, 5);
shuffle($numbers); # array(3, 2, 5, 1, 4)
```

  
## 栈与列队

`array_push()`— 将一个或多个单元压入数组的末尾（入栈）
`array_pop()`— 将数组最后一个单元弹出（出栈）

```php
$stack = array('orange', 'banana');

array_push($stack, 'apple', 'raspberry'); # array('orange', 'banana', 'apple', 'raspberry')

$fruit = array_pop($stack);  #array('orange', 'banana', 'apple')
```

`array_unshift()`— 在数组开头插入一个或多个单元
`array_shift()`— 将数组开头的单元移出数组

```php
$queue = array('orange', 'banana');

array_unshift($queue, 'apple', 'raspberry'); # array('apple', 'raspberry', 'orange', 'banana')

$fruit = array_shift($queue); # array('raspberry', 'orange', 'banana')
```

  
## 分割、填充、合并

`array_slic()`— 从数组中取出一段
`array_splice()`— 把数组中的一部分去掉并用其它值取代

```php
$input  = array('a', 'b', 'c', 'd', 'e');
$result = array_slice($input, 2); # array('c', 'd', 'e')

$input = array('red', 'green', 'blue', 'yellow');
array_splice($input, 2, 1); # array('red', 'green', 'yellow')
```
`array_pad()`— 以指定长度将一个值填充进数组

```php
$input  = array(12, 10, 9);
$result = array_pad($input, 5, 0);   # array(12, 10, 9, 0, 0)
$result = array_pad($input, -7, -1); # array(-1, -1, -1, -1, 12, 10, 9)
```
`array_fill()`— 用给定的值填充数组

```php
$a = array_fill(5, 3, 'a');     # array(5=>'a', 6=>'a', 7=>'a')
$b = array_fill(-2, 3, 'pear'); # array(-2=>'a', 0=>'a', 1=>'a')
```
`array_fill_keys()`— 使用指定的键和值填充数组

```php
$keys   = array('foo', 5, 10, 'bar');
$result = array_fill_keys($keys, 'a'); # array('foo'=>'a', 5=>'a', 10=>'a', 'bar'=>'a')
```
`array_merge()`— 合并一个或多个数组

```php
$array1 = array('data0');
$array2 = array('data1');
$result = array_merge($array1, $array2); # array('data0', 'data1')
```

  
## 其他函数

1.`array_walk()`使用用户自定义函数对数组中的每个元素做回调处理（改变原来数组）：

```php
$a = array(1, 2, 3, 4, 5);
array_walk($a, function(&$value, $key) {
    ++$value;
}); # array(2, 3, 4, 5, 6)
```

2.`array_map()`将回调函数作用到给定数组的单元上（不改变原来数组，同时生成新的数组作为结果）：

```php
$a = array(1, 2, 3, 4, 5);
$b = array_map(function($item) {
    return $item + 1;
}, $a); # array(2, 3, 4, 5, 6)
```

3.`array_rand()`从数组中随机取出一个或多个元素：

```php
$input  = array('apple', 'banana', 'lemon', 'orange');
$result = array_rand($input, 2); # array('banana', 'lemon')
```

4.`array_diff()`计算数组 value 的差集：

```php
$array1 = array('a' => 'green', 'red', 'blue', 'red');
$array2 = array('b' => 'green', 'yellow', 'red');
$result = array_diff($array1, $array2); # array('blue')
```

  

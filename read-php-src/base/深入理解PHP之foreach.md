## 深入理解PHP之foreach

来源：[https://juejin.im/post/5b596b9b6fb9a04fd450a16f](https://juejin.im/post/5b596b9b6fb9a04fd450a16f)

时间 2018-07-26 14:46:12


foreach 语法结构提供了遍历数组的简单方式。

php5之前, foreach仅能用于数组
php5+, 利用foreach可以遍历对象

foreach仅能够应用于数据和对象，如果尝试应用于其他数据类型的变量，或者未初始化的变量将发出错误信息。

有两种语法：

```php
/*
  遍历给定的 array_expression 数据。每次循环中， 当前单元的值被赋给$value并且数组内部的指针向前移一步（因此下次循环中将会得到下一个单元）
*/
foreach (array_expression as $value) {
    // statement
}

foreach (array_expression as $value) :
    // statement
endforeach;
```

```php
/*
  同上，只除了当前单元格的键名也会在每次循环中被赋给变量$key
*/
foreach (array_expression as $key => $value) {
    // statement
}

foreach (array_expression as $key => $value) :
    // statement
endforeach;
```

还能够自定义遍历对象!

当`foreach`开始执行时, 数组内部的指针会自动指向第一个单元. 这意味着不需要在`foreach`循环之前调用`reset()`由于`foreach`依赖内部数组指针, 在循环中修改其值将可能导致意外的行为

可以很容易通过在 $value 之前加上 & 来修改数组元素. 此方法将以`引用`赋值, 而不是拷贝一个值.

```php
<?php

$arr = [1, 2, 3, 4];
foreach($arr as &$value) {
    $value = $value * 2;
}

// $arr is now [2, 4, 6, 8]
unset($value); // 最后取消掉引用

```
`$value`的引用仅在被遍历的数组可以被引用时才可用(例如是个变量)。

以下代码无法运行：

```php
<?php
/*
  此段代码可以运行
  运行结果:
    1-2
    2-4
    3-6
    4-8
*/
foreach (array(1, 2, 3, 4) as &$value) {
    echo $value, '-';
    $value = $value * 2;
    echo $value, PHP_EOL;
}
```

Warning: 数组最后一个元素的`$value`引用在`foreach`循环之后仍会保留。建议使用`unset()`来将其销毁。

Note:`foreach`不支持用`@`来抑制错误信息的能力

foreach 虽然简单, 不过它可能出现一些意外行为， 特别是代码涉及到引用的时候。


## 问题研究


### 问题一: 如下代码运行结果为何不是 2/4/6 ?

```php
<?php
$arr = [1, 2, 3];

foreach ($arr as $k => &$v) {
    $v = $v * 2;
}

foreach ($arr as $k => $v) {
    echo $v, PHP_EOL;
}

/*
输出:
    2
    4
    4
*/
```

我们可以认为`foreach($arr as &$v)`结构隐含了如下操作, 分别将数组当前的`键`和`值`赋值给`$k`和`$v`. 具体展开形如:

```php
<?php
foreach ($arr as $k => $v) {
    $k = currentKey();
    $v = currentVal();
    // 继续运行用户代码
} 
```

根据上述理论, 现在我们重新来分析下第一个`foreach`:

| 循环 | 备注 | $arr值 |
|-|-|-|
| 循环 1-1 | 由于`$v`是一个引用, 因此`$v = &$arr[0]`,`$v = $v * 2`相当于`$arr[0] * 2` | [2, 2, 3] |
| 循环 1-2 | `$v = &$arr[1]` | [2, 4, 3] |
| 循环 1-3 | `$v = &$arr[2]` | [2, 4, 6] |
| 循环 2-1 | 隐含操作`$v = $arr[0]`被触发, 由于此时`$v`仍是`$arr[2]`的引用, 相当于`$arr[2] = $arr[0]` | [2, 4, 2] |
| 循环 2-2 | `$v = $arr[1]`, 即`$arr[2] = $arr[1]` | [2, 4, 4] |
| 循环 2-3 | `$v = $arr[2]`, 即`$arr[2] = $arr[2]` | [2, 4, 4] |
  

如何解决此类问题呢? PHP手册上有一段提醒:

Warning: 数组最后一个元素的 $value 引用在 foreach 循环之后仍会保留。建议使用 unset() 来将其销毁。

```php
<?php
$arr = [1, 2, 3];

foreach ($arr as $k => &$v) {
    $v = $v * 2;
}
unset($v);
foreach ($arr as $k => $v) {
    echo $v, PHP_EOL;
}

/*
输出:
    2
    4
    6
*/
```

从这个问题可以看出, 引用很可能会伴随副作用。如果不希望无意识的修改导致数据内容变更， 最好及时unset掉这些引用。


### 问题二: 如下代码运行结果为何不是 0=>a 1=>b 2=>c

```php
<?php
$arr = ['a', 'b', 'c'];

foreach ($arr as $k => $v) {
    echo key($arr), "=>", current($arr), PHP_EOL;
}

foreach ($arr as $k => &$v) {
    echo key($arr), "=>", current($arr), PHP_EOL;
}
/*
#php5.6
1=>b 1=>b 1=>b
1=>b 2=>c =>

#php7
0=>a 0=>a 0=>a
0=>a 0=>a 0=>a
*/
```

按照手册中的说法, key和current分别是获取数据中当前元素的键值。
那为何`key($arr)`一直是0，`current($arr)`一直是'a'呢？

先用vld查看编译后的`opcode`:

```
➜  demo /usr/local/Cellar/php/7.2.7/bin/php -dvld.active=1 a.php
Finding entry points
Branch analysis from position: 0
Jump found. (Code = 77) Position 1 = 2, Position 2 = 15
Branch analysis from position: 2
Jump found. (Code = 78) Position 1 = 3, Position 2 = 15
Branch analysis from position: 3
Jump found. (Code = 42) Position 1 = 2
Branch analysis from position: 2
Branch analysis from position: 15
Jump found. (Code = 62) Position 1 = -2
Branch analysis from position: 15
filename:       /Users/jianyong/demo/a.php
function name:  (null)
number of ops:  17
compiled vars:  !0 = $arr, !1 = $v, !2 = $k
line     #* E I O op                           fetch          ext  return  operands
-------------------------------------------------------------------------------------
   2     0  E >   ASSIGN                                                   !0, <array>
   4     1      > FE_RESET_R                                       $4      !0, ->15
         2    > > FE_FETCH_R                                       ~5      $4, !1, ->15
         3    >   ASSIGN                                                   !2, ~5
   5     4        INIT_FCALL                                               'key'
         5        SEND_VAR                                                 !0
         6        DO_ICALL                                         $7
         7        ECHO                                                     $7
         8        ECHO                                                     '%3D%3E'
         9        INIT_FCALL                                               'current'
        10        SEND_VAR                                                 !0
        11        DO_ICALL                                         $8
        12        ECHO                                                     $8
        13        ECHO                                                     '%0A'
        14      > JMP                                                      ->2
        15    >   FE_FREE                                                  $4
   7    16      > RETURN                                                   1

branch: #  0; line:     2-    4; sop:     0; eop:     1; out1:   2; out2:  15
branch: #  2; line:     4-    4; sop:     2; eop:     2; out1:   3; out2:  15
branch: #  3; line:     4-    5; sop:     3; eop:    14; out1:   2
branch: # 15; line:     5-    7; sop:    15; eop:    16; out1:  -2
path #1: 0, 2, 3, 2, 15,
path #2: 0, 2, 15,
path #3: 0, 15,
0=>a
0=>a
0=>a
```


## PHP7新特性之foreach



* [x]`foreach`循环对数组内部指针不再起作用, 在PHP7之前, 当数据通过foreach迭代时, 数组指针会移动。    
  

```php
<?php
$array = [0, 1, 2];
foreach ($array as &$val) {
    var_dump(current($array));
}
```

| 版本 | 结果 | 说明 |
|-|-|-|
| PHP5 | int(1) int(2) bool(false) | 数组指针会移动 |
| PHP7 | int(0) int(0) int(0) | 数据指针不再移动 |
  



* [x] 按照值进行循环时, 对数组的修改是不会影响循环。
  
`foreach`按照值进行循环的时候(by-value), foreach是对该数组的一个拷贝进行操作. 所以在循环过程中修改不影响循环结果

```php
<?php
$arr = [0, 1, 2];
$ref = &$arr;

foreach ($arr as $val) {
    var_dump($val);
    unset($arr[1]);
}
```

| 版本 | 结果 | 说明 |
|-|-|-|
| PHP5 | int(0) int(2) | 会将unset的数据跳过 |
| PHP7 | int(0) int(1) int(2) | 对数组的改动不影响循环 |
  



* [x] 按照引用进行循环的时候, 对数组的修改会影响循环
  

```php
<?php
$arr = [0, 1, 2];
$ref = &$arr;

foreach ($arr as &$val) {
    var_dump($val);
    unset($arr[1]);
}
```

| 版本 | 结果 |
|-|-|
| PHP5 | int(0) int(2) |
| PHP7 | int(0) int(2) |
  



* [x] 对简单对象plain(non-Traversable)的循环
  

在简单对象的循环, 不管是按照值循环还是引用循环, 和按照引用对数组循环的行为是一样的, 不过对位置的管理会更加精确



* [x] 对迭代对象(Traversable objects)对象行为和之前一致
  
`stackoverflow`上面的解释, Traversable objects is one that implements Iterator or IteratorAggregate interface

如果一个对象实现了`Iterator`或者`IteratorAggregate`接口, 即可称之为迭代对象


#### 参考



* https://wiki.php.net/rfc/php7_foreach
* [97fe15db4356f8fa1b3b8eb9bb1baa8141376077][0]
    
  



[0]: https://link.juejin.im?target=http%3A%2F%2Fgit.php.net%2F%3Fp%3Dphp-src.git%3Ba%3Dcommitdiff%3Bh%3D97fe15db4356f8fa1b3b8eb9bb1baa8141376077
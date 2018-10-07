## 警惕array_filter/array_unique等垃圾函数

来源：[https://yuerblog.cc/2018/10/05/take-care-of-array_filter-array_unique/](https://yuerblog.cc/2018/10/05/take-care-of-array_filter-array_unique/)

时间 2018-10-05 11:20:08


写这篇博客提醒一下自己和各位，但凡用到array_xxxx系列函数，一定要注意避免踩坑。

array_filter和array_unique在对数组做处理后并不会重建数组下标，导致接下来的json_encode就变成了字典{}而不是数组[]，下面是一个例子：

```php
<?php
 
$arr = [0, 444, 222];
 
$arr = array_filter($arr);
 
var_dump($arr);
 
 
array(2) {
  [1]=>
  int(444)
  [2]=>
  int(222)
}


```

所以，建议大家在项目框架中提供封装过的上述方法，避免研发同学重复的踩坑。

最简单的方法就是套一个array_merge方法，它可以重建数组下标：

```php
<?php
 
$arr = [0, 444, 222];
 
$arr = array_merge(array_filter($arr));
 
var_dump($arr);
 
array(2) {
  [0]=>
  int(444)
  [1]=>
  int(222)
}


```

祝大家少写点低级bug，多点时间休息。


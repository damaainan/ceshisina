## PHP经验总结 - 强大的数组函数

来源：[https://segmentfault.com/a/1190000014746905](https://segmentfault.com/a/1190000014746905)


## 简述

数据处理是任何程序员都避免不了的，PHP有一套强大的数组处理函数，可以很好帮助处理常见的数据处理问题。自己的脑子记性不好，经常忘记函数怎样用，所以记下来给自己以后好好翻查，也希望对你们有所帮助吧。
## Q&A
### PHP怎样定义数组和赋值？

这个简单，给简单列一下，欢迎补充：

（1）数组定义

```php
<?php
    // 数组定义
    $arr1 = array();
    $arr2 = [];
?>
```

（2）数组赋值

```php
<?php
    // 利用 list 函数给数组赋值
    list($arr[], $arr[], $arr[]) = [1, 2, 3];
?>
```
### array_multisort() - 数组排序

函数功能：可以同时对多个数组进行排序，关联键名保持不变，数字键名会被重新索引。

```php
 <?php
    // 自定义数据
    $data[] = array('volume' => 67, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 1);
    $data[] = array('volume' => 85, 'edition' => 6);
    $data[] = array('volume' => 98, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 6);
    $data[] = array('volume' => 67, 'edition' => 7);
    
    // 取得列的列表
    foreach ($data as $key => $row) {
        $volume[$key]  = $row['volume'];
        $edition[$key] = $row['edition'];
    }
    
    // 先将数据根据 volume 降序排列，出现重复时再根据 edition 升序排列
    // 把 $data 作为最后一个参数，以通用键排序
    array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data);
    print_r($data);
 ?>
```
### array_column() - 获取数组指定一列

函数功能：根据指定的 key，获取指定的那一列数据。

```php
<?php
    // 对目标数组获取 key 的一列，并复制到结果数组
    $resultArr = array_column($targetArr, 'key');
?>
```
### array_diff() - 数组相减求差集合

函数功能：对两个数组进行比较，求两个数组的差集。

```php
<?php
    // 把两个数组的差集保存到结果数组
    $diffArr = array_diff($arr1, $arr2);
?>
```
### array_flip() - 数组键和值互换位置

函数功能：将数组中的键和值进行位置调换，

```php
<?php
    // 把目标数组的键和值互换位置
    array_flip($targetArr);
?>
```
### array_intersect() - 两个数组的交集

函数功能：比较两个数据的交集，算出两个数组的相同部分。

```php
<?php
    // 两个数组的交集保存到结果数组
    $resultArr = array_intersect($arr1, $arr2)
?>
```
### array_key_exists() - 判断数组键名是否存在

函数功能：判断数组中指定键名或索引是否存在，仅适用一维数组。

<?php

```php
// 判断数组是否有 key 这个键
if(!array_key_exists('key', $targetArr)) {
    throw new \Exception('目标数组没有key这个键！');
}
```

?>
### array_merge() - 合并数组

函数功能：合并多个数据，不会合并相同键值的元素。

```php
<?php
    // 合并数组
    $resultArr = array_merge($arr1, $arr2)
?>
```
### array_pad() - 按照设定补全数组元素

函数功能：设定函数长度，多除少补地保证数组长度跟设定的一致，可以设置补充元素的值。

```php
<?php
    // 结果计划是：$resultArr = [1,2,3,0,0]
    $resultArr = array_pad([1,2,3], 5, 0);
?>
```
### array_pop() - 数组最后一个元素出栈（删）

函数功能：把数组最后一个函数去掉。

```php
<?php
    // 删掉最后一个元素
    $resultArr = array_pop([1,2,3]);// $resultArr = [3]; [1,2]
?>
```
### array_product() - 数组内元素相乘

函数功能：计算数组内的所有元素相乘的结果，空数组返回1。

```php
<?php
    // 数组内元素相乘
    $result = array_product([1,2,3]) // $result = 6
?>
```
### array_sum() - 数组内元素相加

函数功能：计算数组内所有元素相加的结果，空数组返回0。

```php
<?php
    // 数组内元素相加
    $result = array_product([1,2,3]) // $result = 6
?>
```
### array_push() - 数组叠加元素

函数功能：给数组叠加（入栈）元素，可以是多个。

```php
<?php
    //  数组加元素 
    $resultArr = array_push([1,2],3,4); // $resultArr = [1,2,3,4]
?>
```
### array_search() - 数组搜索键值

函数功能：搜索数组指定值，搜索成功将返回首个元素的键值。

```php
<?php
    // 把数组搜索 needle 的结果保存起来
    $result = array_search('needle', $targetArr);
?>
```
### array_shift() - 数组第一个元素出栈（删）

函数功能：把数组中的第一个元素删掉，弹出第一个元素。

```php
<?php
    // 删掉第一个元素
    $resultArr = array_shift([1,2,3]); // [2,3]
?>
```
### implode() - 数组转字符串

函数功能：把数组以一定格式转为字符串。

```php
<?php
    $arr = array('Hello','World!','I','love','Shanghai!');
    echo implode(" ",$arr);// 数组以空格连在一起，转成字符串
?>
```
### explode() - 字符串转数组

函数功能：把字符串以一定格式切割转为数组。

```php
<?php
    $str = "Hello world. I love Shanghai!";
    print_r (explode(" ",$str));// 字符串以空格的方式切割，转为数组
?>
```
### 未完待续

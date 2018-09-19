## 慎用PHP的unset、array_unique方法

来源：[https://segmentfault.com/a/1190000016392045](https://segmentfault.com/a/1190000016392045)


## 背景

-----

在日常工作中,可能会经常遇到一些PHP的代码场景,需要我们去除数组中的某个项,通常会直接调用unset方法,但是如果用得不妥,会给自己挖坑
## 1.实操

以下使用具体例子进行证明
假设有数组如下值:

```php
$age_arr=[0,12,43,34,24,63,90];

```

1).设定场景是去除年龄为0的数值.简单方法如下:

```php
foreach($age_arr as $k=>$age){
    if($age==0){
        unset($age_arr[$k]);
    }
}

```

2).设定场景取去除0值之后的数组中的第一个人的年龄

```php
$first_people=$age_arr[0];

```

结果会报错,Undefined offset: 0
## 2.剖析

为什么会报错呢?带着疑问,我们尝试输出unset前后的数组,查看其的区别

```php
$age_arr=[0,12,43,34,24,63,90];
echo 'unset前 :'.json_encode($age_arr).'</br>';
unset($age_arr[0]);
echo 'unset后 :'.json_encode($age_arr).'</br>';

```

输出结果:

```php
unset前 :[0,12,43,34,24,63,90]
unset后 :{"1":12,"2":43,"3":34,"4":24,"5":63,"6":90}

```

由上可得知,对数组进行unset操作的时候，PHP会将数组转化为关联数组。当我们使用json_encode的时候，会导致数据结构不一致。而当unset方法执行后，数组会去除相应索引下标指定的值，并且不会重置索引。如上结果可知原先下标为0的已经没了，但为1的不会变成0.
## 3.深入研究

我们接下来调用其他PHP的数组相关方法进行验证,看其他方法是否能正常反馈结果
除了unset会去除数组项外,array_unique方法会去除重复项,以下方法演示:

```php
$age_arr=[0,12,43,34,24,63,43,90];
echo 'array_unique前 :'.json_encode($age_arr).'</br>';
$age_arr=array_unique($age_arr);
echo 'array_unique后 :'.json_encode($age_arr).'</br>';

```

结果:

```php
array_unique前 :[0,12,43,34,24,63,43,90]
array_unique后 :{"0":0,"1":12,"2":43,"3":34,"4":24,"5":63,"7":90}

```

由上可得知,对数组进行array_unique操作的时候，PHP会将数组转化为关联数组。而当array_unique方法执行后，数组会去除相应索引下标指定的值，并且不会重置索引。如上结果可知原先下标为6的已经没了，但为7的不会变成6.

故当使用unset、array_unique时，都会转换成关联数组，后续逻辑如使用中括号索引取值，必然会有问题，需谨慎！
## 4.解决办法

使用array_values方法进行重置索引排序。

官方文档介绍如下：

array_values

(PHP 4, PHP 5, PHP 7)

array_values — 返回数组中所有的值

说明

array array_values( array $array)

array_values() 返回 input 数组中所有的值并给其建立数字索引。

by KingFer

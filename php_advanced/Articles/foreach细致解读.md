### foreach细致解读
- 先决条件

> 本文所使用的PHP版本：7.1

- 巨坑总结

> 巨坑1

```php
<?php

$array1 = ["k1"=>1,"k2"=>2,"k3"=>3];
/*代码片段1*/
foreach($array1 as $k1=> &$v1)
{
    $v1 = $v1+1;
}
xdebug_debug_zval('array1');
//array1: (refcount=1, is_ref=1)=array ('k1' => (refcount=1, is_ref=1)=2, 'k2' => (refcount=1, is_ref=1)=3, 'k3' => (refcount=2, is_ref=1)=4)
//其中refcount表示引用计数，is_ref表示是否为引用，0表示不是引用，1表示是引用
xdebug_debug_zval("v1");
//v1: (refcount=2, is_ref=1)=4

/*代码片段2*/
//其实上面的foreach内部相当于下面的代码片段，但是实际情况会复杂很多，这里只是类比
$array2 = ["k1"=>1,"k2"=>2,"k3"=>3];
$v2 = &$array2['k1'];//声明$v2变量是$array2['k1']的reference（译作：引用）
$v2 = $v2+1;
$v2 = &$array2['k2'];
$v2 = $v2+1;
$v2 = &$array2['k3'];
$v2 = $v2+1;
xdebug_debug_zval('array2');
//array2: (refcount=1, is_ref=0)=array ('k1' => (refcount=1, is_ref=1)=2, 'k2' => (refcount=1, is_ref=1)=3, 'k3' => (refcount=2, is_ref=1)=4)
xdebug_debug_zval("v2");
//v2: (refcount=2, is_ref=1)=4
//接下来我们应该把$v2变量unset掉，避免对$array2['k3']的值产生副作用
//这时发现$v2成了未初始化的变量
unset($v2);
xdebug_debug_zval('v2');
//v2: (refcount=0, is_ref=0)=*uninitialized*

//我们再次给$v2赋值发现它已经非引用变量
$v2 = 111;
xdebug_debug_zval('v2');
//v2: (refcount=0, is_ref=0)=111

```

> 巨坑2

```php
<?php
/*
php5系列版本中，
foreach使用了内部数组指针去遍历数组，
因此如果你在foreach内部使用了能够改变foreach内部数组指针的方法，例如:
reset($array)->重置数组指针到初始位置0
next($array)->数组指针移动到下一个
prev($array)->数组指针移动到上一个
end($array)->数组指针移动到最后一个，
这时我们的遍历数据可能会被打乱，
但在PHP7中由于foreach内部未使用内部数组指针，因此不会产生影响。
PHP5和PHP7中，每次使用foreach遍历数组时都相当于调用了一次reset指针操作，让数组指针回到初始位置。
*/
/*代码片段3*/
$array = [1,2,3,4];

foreach ($array as $v)
{
    var_dump( $v);//php7依次输出：1,2,3,4 php5依次输出：1,2,3,4
    var_dump( next($array));//php7依次输出：2,3,4,false php5依次输出：3,4,false,false
}

```

- 数组遍历为何foreach比for性能好

> 先看stackOverflow上的对比结果：[Performance of FOR vs FOREACH in PHP](https://stackoverflow.com/questions/3430194/performance-of-for-vs-foreach-in-php),
主要有下面几点原因：

1. PHP中的数组C语言底层源码是由HashTable数据结构实现的，HashTable的索引访问开销是O(1)级别，而HashTable这种数据结构适合foreach的iterator（译作：迭代器）访问
2. for循环需要维护一个$i计数器，且每次都要判断一下$i是否越界

- 最佳实践 
1. 能使用foreach的地方尽量用，因为 for 可以完全被 foreach 取代;
2. 尽量不要在foreach内部使用指针操作函数;
3. 在foreach中使用引用的方式修改数组值时一定要记得unset掉对应的中间变量。  

## PHP7中array的两种工作模式

来源：[https://fengyoulin.com/2018/03/12/two_modes_of_the_array_in_php7/](https://fengyoulin.com/2018/03/12/two_modes_of_the_array_in_php7/)

时间 2018-03-12 13:15:53


在PHP中array也就是HashTable，使用起来非常方便。不仅仅是在PHP语言中，在PHP运行时和Zend引擎的实现中，也大量的使用了HashTable。可以说强大的HashTable是PHP得以实现的基石。

其实数组和散列表在严格意义上讲，不是同一个东西。虽然散列表在实现的时候内部是基于数组的，但是其结构和工作原理要比数组复杂得多。因为数组的结构简单，所以实际的存取效率也要比散列表高很多。作为PHP中如此核心的一个组件，在实现的时候肯定要充分考虑到性能问题的。所以PHP7中的HashTable是有两种工作模式的，根据源码中的实现一种是正常的散列表模式，另一种是packed模式，也就是我们刚刚说到的数组模式。

下面我们使用测试代码加上    [zendump][0]
输出的调试信息，来分析HashTable的工作模式：


### 一、数组模式

我们使用安装了    [zendump][0]
扩展的PHP7运行如下代码：

```php
<?php
zendump([]);
$a = ['if', 'else', 'do', 'while', 'for', 'foreach'];
zendump($a);
```

得到如下所示输出：

``` 
zval(0x7f5e8f81e110) -> array(0) addr(0x7f5e8f863840) refcount(2) hash(2,0) bucket(8,0) data(0xffce50)
zval(0x7f5e8f81e110) -> array(6) addr(0x7f5e8f8638a0) refcount(3) hash(2,0) bucket(8,6) data(0x7f5e8f869788)
{
  [0] =>
  zval(0x7f5e8f869788) -> string(2,"if") addr(0x7f5e8f801a00) refcount(1)
  [1] =>
  zval(0x7f5e8f8697a8) -> string(4,"else") addr(0x7f5e8f801a40) refcount(1)
  [2] =>
  zval(0x7f5e8f8697c8) -> string(2,"do") addr(0x7f5e8f801a80) refcount(1)
  [3] =>
  zval(0x7f5e8f8697e8) -> string(5,"while") addr(0x7f5e8f801ac0) refcount(1)
  [4] =>
  zval(0x7f5e8f869808) -> string(3,"for") addr(0x7f5e8f801b00) refcount(1)
  [5] =>
  zval(0x7f5e8f869828) -> string(7,"foreach") addr(0x7f5e8f801b40) refcount(1)
}
```


根据第一行的输出`hash(2,0) bucket(8,0)`可以确定，array对象初始时Hash为2个，Bucket最少分配8个。

根据第二行中的`hash(2,0) bucket(8,6)`可以确定，我们为数组添加6个元素后，Bucket使用了6个，但是Hash没有变化。

我们继续为数组$a添加元素：

```php
$a[] = 'switch';
$a[] = 'case';
$a[] = 'return';
zendump($a);
```

输出结果如下：

```c
zval(0x7ff402c1e150) -> array(9) addr(0x7ff402c63900) refcount(2) hash(2,0) bucket(16,9) data(0x7ff402c73508)
{
  [0] =>
  zval(0x7ff402c73508) -> string(2,"if") addr(0x7ff402c01a00) refcount(2)
  [1] =>
  zval(0x7ff402c73528) -> string(4,"else") addr(0x7ff402c01a40) refcount(2)
  [2] =>
  zval(0x7ff402c73548) -> string(2,"do") addr(0x7ff402c01a80) refcount(2)
  [3] =>
  zval(0x7ff402c73568) -> string(5,"while") addr(0x7ff402c01ac0) refcount(2)
  [4] =>
  zval(0x7ff402c73588) -> string(3,"for") addr(0x7ff402c01b00) refcount(2)
  [5] =>
  zval(0x7ff402c735a8) -> string(7,"foreach") addr(0x7ff402c01b40) refcount(2)
  [6] =>
  zval(0x7ff402c735c8) -> string(6,"switch") addr(0x7ff402c01c40) refcount(1)
  [7] =>
  zval(0x7ff402c735e8) -> string(4,"case") addr(0x7ff402c01cc0) refcount(1)
  [8] =>
  zval(0x7ff402c73608) -> string(6,"return") addr(0x7ff402c01d40) refcount(1)
}
```

我们可以发现数组的地址和内部data的地址都发生了改变，这是因为当我们使用`[]`直接初始化数组$a后，$a会指向编译时创建的字面量，存储在当前op_array的literals里，有兴趣的话自己用`zendump_literals()`打印出来看看。因为编译字面量是只读的，不允许修改，所以在我们要修改$a时，PHP会自动为我们拷贝一份以供修改。这种Copy On Write写时复制的机制是为了提高效率，但是这不是我们研究的重点。

我们的目光回到`hash(2,0) bucket(16,9)`上，Hash区域没有发生什么改变，Bucket区域的容量增大到16，使用了9个。此时的$a是完全工作在数组模式下的，数据直接存储在Bucket数组中按下标访问，Hash功能根本没有被使用到。


### 二、散列表模式

接着上面的代码，我们可以使用两种不同的方式使$a进入到散列表模式，一种是：

```php
$a['hello'] = 'instanceof';
```

另一种是：

```php
$a[100] = 'class';
```

概括起来就是使用一个字符串的key或者使用一个足够大的下标，为$a的一个元素进行赋值。使用字符串key是很好理解的，这也是一般散列表的特点。那么使用一个大的下标呢？当使用一个整数下标访问数组的成员时，如果下标的值小于当前已经分配的Bucket个数，那么直接使用该下标在Bucket数组中寻址，否则需要将下标值进行转换以使其处于Bucket数组的地址空间以内。这样做是有道理的，因为不可能为了一个非常大的下标值而将Bucket数组扩容到相应的大小，且不说内存够不够用，单是出于性能和资源利用率的角度考虑就不能这么做。

将大的下标进行转换，原理类似于散列函数，会将array对象转换到散列表模式。我们来验证一下：

```php
$a[100] = 'class';
zendump($a);
```

下面是输出结果：

``` 
zval(0x7f7c8f41e190) -> array(10) addr(0x7f7c8f463900) refcount(2) hash(16,9) bucket(16,10) data(0x7f7c8f473540)
{
  [0] =>
  zval(0x7f7c8f473540) -> string(2,"if") addr(0x7f7c8f401a00) refcount(2)
  [1] =>
  zval(0x7f7c8f473560) -> string(4,"else") addr(0x7f7c8f401a40) refcount(2)
  [2] =>
  zval(0x7f7c8f473580) -> string(2,"do") addr(0x7f7c8f401a80) refcount(2)
  [3] =>
  zval(0x7f7c8f4735a0) -> string(5,"while") addr(0x7f7c8f401ac0) refcount(2)
  [4] =>
  zval(0x7f7c8f4735c0) -> string(3,"for") addr(0x7f7c8f401b00) refcount(2)
  [5] =>
  zval(0x7f7c8f4735e0) -> string(7,"foreach") addr(0x7f7c8f401b40) refcount(2)
  [6] =>
  zval(0x7f7c8f473600) -> string(6,"switch") addr(0x7f7c8f401c40) refcount(1)
  [7] =>
  zval(0x7f7c8f473620) -> string(4,"case") addr(0x7f7c8f401cc0) refcount(1)
  [8] =>
  zval(0x7f7c8f473640) -> string(6,"return") addr(0x7f7c8f401d40) refcount(1)
  [100] =>
  zval(0x7f7c8f473660) -> string(5,"class") addr(0x2cf2ca0) refcount(1)
}
```

注意发生的变化，从`hash(16,9) bucket(16,10)`可以知道，Hash区域的大小也变成了16，而且也使用了9个，这就说明$a已经转换为散列表模式了。为什么Hash用了9个比Bucket少一个呢？这是因为hash value是有一定的冲突概率的。

总结：我们在这里对这次的研究做一个总结，在PHP7中，当你严格的把一个array对象当成数组来用时（不使用字符串key，不使用超出大小的下标），它就是一个数组，开销也更少。当你开始像一个散列表那样来使用它时，它会转换为一个散列表（分配hash空间，并填充值）。

想要有更深入地了解的话，请自行查看PHP7源码中HashTable的实现。



[0]: https://github.com/php7th/zendump
[1]: https://github.com/php7th/zendump
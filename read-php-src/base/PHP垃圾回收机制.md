## PHP垃圾回收机制

来源：[https://segmentfault.com/a/1190000013893628](https://segmentfault.com/a/1190000013893628)


PHP是一种弱类型的脚本语言，弱类型不表示PHP变量没有类型的区别，PHP变量有8种原始类型：
四种标量类型：

* boolean（布尔值）
* integer（整型）
* float（浮点型）

两种复合类型：

* array（数组）
* object（对象）

两种特殊类型：

* resource（资源）
* NULL


-----


在引擎内部，变量都是用一个结构体来表示的。这个结构体可以在{PHPSRC}/Zend/zend.h中找到：

```c
  struct _zval_struct {  
       /* Variable information */  
       zvalue_value value;     /* value */  
       zend_uint refcount__gc;  //代表一个计数器，表示有多少个变量名指向这个zval容器
       zend_uchar type;    /* active type */  
       zend_uchar is_ref__gc;  //此字段是一个布尔值，用来标识变量是否是一个引用，通过这个字段，PHP引擎可以区分一般变量和引用变量
   };  
```
## copy on write（写时复制技术）

父进程fork子进程之后，子进程的地址空间还是简单的指向父进程的地址空间，只有当子进程需要写地址空间中的内容的时候，才会单独分离一份给子进程，这样就算子进程马上调用exec函数也没有关系，因为根本就不需要从父进程的地址空间中拷贝内容，这样就节省了内存同时又提高了速度。
这个逻辑可以叙述为：对一个一般变量a（isref=0）进行一般的赋值操作，如果a所指向的zval的计数refcount大于1，那么需要为a重新分配一个新的zval，并且把之前的zval的计数refcount减少1。


## PHP5.3版本中对于新的GC算法（Concurrent Cycle Collection in Reference Counted Systems）


几个基本准则：

* 如果一个zval的refcount增加，那么此zval还在使用，不属于垃圾
* 如果一个zval的refcount减少到0，那么zval可以被释放掉，不属于垃圾
* 如果一个zval的refcount减少之后大于0，那么此zval还不能被释放，此zval可能成为一个垃圾。

新的GC算法目的就是防止循环引用的变量引起内存泄露问题。在PHP中GC算法，当节点缓冲区满了之后，垃圾分析算法就会启动，并且会释放掉发现的垃圾，从而回收内存。

现在，如果我们试一下，将数组的引用赋值给数组中的一个元素，有意思的事情发生了：

```php
<?php
$a = array("one");
$a[] = &$a;
?>
```


这样$a数组就有两个元素，一个索引为0，值为字符one，另一个索引为1，为$a自身的引用，内部存储如下：

```
a: (refcount=2, is_ref=1)=array (
   0 => (refcount=1, is_ref=0)='one',
   1 => (refcount=2, is_ref=1)=...
)
```


“...”表示1指向a自身，是一个环形引用（循环引用）：


![][0]


这个时候我们对$a进行unset，那么$a会从符号表中删除，同时$a指向的zval的refcount减少1。

```php
<?php
$a = array('one');
$a[] = &$a;
unset($a);
?>
```


那么问题产生了，$a已经不在符号表中了，用户无法再访问此变量，但是$a之前指向的zval的refcount变为1而不是0，因此不能被回收，这样产生了**`内存泄露`**：


![][1]


这样zval就成为一个垃圾了，新的GC要做的工作就是清理这种垃圾。

在PHP编程中程序员不需要手动处理内存资源分配与释放，意味着PHP本身实现了垃圾回收处理机制。

## PHP5.2中的垃圾回收算法---Reference Counting

这个算法叫做“引用计数”，其思想非常直观和简洁：为每个内存对象分配一个计数器，当一个内存对象建立时计数器初始化为1（因此此时总是有一个变量引用此对象），以后每有一个新变量引用此内存对象，则计数器加1，而每当减少一个引用此内存对象的变量则计数器减1，当垃圾回收机制运作时，将所有计数器为0的内存对象销毁并回收其占用的内存。而php中内存对象就是zval，而计数器就是`refcount__gc`。

[0]: ./img/bV6stZ.png
[1]: ./img/bV6su9.png
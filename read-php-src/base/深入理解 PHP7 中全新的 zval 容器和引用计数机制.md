## 深入理解 PHP7 中全新的 zval 容器和引用计数机制

来源：[https://juejin.im/post/5bbf50e86fb9a05ce02a9c19](https://juejin.im/post/5bbf50e86fb9a05ce02a9c19)

时间 2018-10-15 10:27:14

 
最近在查阅 PHP7 垃圾回收的资料的时候，网上的一些代码示例在本地环境下运行时出现了不同的结果，使我一度非常迷惑。 仔细一想不难发现问题所在：这些文章大多是 PHP5.x 时代的，而 PHP7 发布后，采用了新的 zval 结构，相关的资料也比较贫瘠，所以我结合一些资料做了一个总结， **`主要侧重于解释新 zval 容器中的引用计数机制`**  ，如有谬误，还望不吝指教。
 
## PHP7 中新的 zval 结构
 
明人不说暗话，先看代码！

```c
struct _zval_struct {
	union {
		zend_long         lval;             /* long value */
		double            dval;             /* double value */
		zend_refcounted  *counted;
		zend_string      *str;
		zend_array       *arr;
		zend_object      *obj;
		zend_resource    *res;
		zend_reference   *ref;
		zend_ast_ref     *ast;
		zval             *zv;
		void             *ptr;
		zend_class_entry *ce;
		zend_function    *func;
		struct {
			uint32_t w1;
			uint32_t w2;
		} ww;
	} value;
    union {
        struct {
            ZEND_ENDIAN_LOHI_4(
                zend_uchar    type,         /* active type */
                zend_uchar    type_flags,
                zend_uchar    const_flags,
                zend_uchar    reserved)     /* call info for EX(This) */
        } v;
        uint32_t type_info;
    } u1;
    union {
        uint32_t     var_flags;
        uint32_t     next;                 /* hash collision chain */
        uint32_t     cache_slot;           /* literal cache slot */
        uint32_t     lineno;               /* line number (for ast nodes) */
        uint32_t     num_args;             /* arguments number for EX(This) */
        uint32_t     fe_pos;               /* foreach position */
        uint32_t     fe_iter_idx;          /* foreach iterator index */
    } u2;
};
```
 
对于该结构的详细描述可以参考文末鸟哥的文章，写的非常详细，我就不关公面前耍大刀了，这里我只提出几个比较关键的点：

 
* PHP7 中的变量分为 **`变量名`**  和 **`变量值`**  两部分，分别对应`zval_struct`和在其中声明的`value` 
* `zval_struct.value`中的`zend_long`、`double`都是 **`简单数据类型`**  ，能够直接储存具体的值，而其他复杂数据类型储存一个指向其他数据结构的 **`指针`**   
* PHP7 中，引用计数器储存在`value`中而不是`zval_struct` 
* **`NULL`**  、 **`布尔型`**  都属于 **`没有值`**  的数据类型（其中布尔型通过`IS_FALSE`和`IS_TRUE`两个常量来标记），自然也就没有引用计数  
* **`引用`**  （REFERENCE）变为了一种数据结构而不再只是一个标记位了，它的结构如下：  
 

```c
struct _zend_reference {
    zend_refcounted_h gc;
    zval              val;
}
```

 
* `zend_reference`作为`zval_struct`中包含的一种`value`类型，也拥有自己的`val`值，这个值是指向一个`zval_struct.value`的。他们都拥有自己的 **`引用计数器`**  。  
 
 
引用计数器用来记录当前有多少`zval`指向同一个`zend_value`。
 
针对第六点，请看如下代码：

```php
$a = 'foo';
$b = &$a;
$c = $a;
```
 
此时的数据结构是这样的：
 
$a 与 $b 各拥有一个`zval_struct`容器，并且其中的`value`都指向同一个`zend_reference`结构，`zend_reference`内嵌一个`val`结构， 指向同一个`zend_string`， **`字符串的内容`**  就储存在其中。
 
而 $c 也拥有一个`zval_struct`，而它的 value 在初始化的时候可以直接指向上面提到的`zend_string`，这样在拷贝时就不会产生复制。
 
下面我们就聊一聊在这种全新的`zval`结构中，会出现的种种现象，和这些现象背后的原因。
 
## 问题
 
### 一. 为什么某些变量的引用计数器的初始值为 0
 
#### 现象

```php
$var_int = 233;
$var_float = 233.3;
$var_str = '233';

xdebug_debug_zval('var_int');
xdebug_debug_zval('var_float');
xdebug_debug_zval('var_str');

/** 输出 **
var_int:
(refcount=0, is_ref=0)int 233

var_float:
(refcount=0, is_ref=0)float 233.3

var_str:
(refcount=0, is_ref=0)string '233' (length=3)
**********/
```
 
#### 原因
 
在 PHP7 中，为一个变量赋值的时候，包含了两部分操作：

 
* 为符号量（即变量名）申请一个`zval_struct`结构  
* 将变量的值储存到`zval_struct.value`中 对于`zval`在`value`字段中能保存下的值，就不会在对他们进行引用计数， **`而是在拷贝的时候直接赋值`**  ，这部分类型有：  
 

 
* IS_LONG 
* IS_DOUBLE 
 
 
即我们在 PHP 中的 **`整形`**  与 **`浮点型`**  。
 
那么 var_str 的 refcount 为什么也是 0 呢？
 
这就牵扯到 PHP 中字符串的两种类型：

 
* `interned string`内部字符串（函数名、类名、变量名、静态字符串）：  
 

```php
$str = '233';    // 静态字符串
```

 
* 普通字符串： 
 

```php
$str = '233' . time(); 
```
 
对于 **`内部字符串`**  而言，字符串的内容是唯一不变的，相当于 C 语言中定义在静态变量区的字符串，他们的生存周期存在于整个请求期间，request 完成后会统一销毁释放  ，自然也就无需通过引用计数进行内存管理。
 
### 二. 为什么在对整形、浮点型和静态字符串型变量进行引用赋值时，计数器的值会直接变为2
 
#### 现象

```php
$var_int_1 = 233;
$var_int_2 = &var_int;
xdebug_debug_zval('var_int_1');

/** 输出 **
var_int:
(refcount=2, is_ref=1)int 233
**********/
```
 
#### 原因
 
回忆一下我们开头讲的`zval_struct`中`value`的数据结构，当为一个变量赋 **`整形`**  、 **`浮点型`**  或 **`静态字符串`**  类型的值时，value 的数据类型为`zend_long`、`double`或`zend_string`，这时值是可以直接储存在 value 中的。而按值拷贝时，会开辟一个新的`zval_struct`以同样的方式将值储存到相同数据类型的 value 中，所以 refcount 的值一直都会为 0。
 
但是当使用`&`操作符进行引用拷贝时，情况就不一样了：

 
* PHP 为`&`操作符操作的变量申请一个`zend_reference`结构  
* 将`zend_reference.value`指向原来的`zval_struct.value` 
* `zval_struct.value`的数据类型会被修改为`zend_refrence` 
* 将`zval_struct.value`指向刚刚申请并初始化后的`zend_reference` 
* 为新变量申请`zval_struct`结构，将他的`value`指向刚刚创建的`zend_reference` 
 
 
此时：  var_int_2 都拥有一个`zval_struct`结构体，并且他们的`zval_struct.value`都指向了同一个`zend_reference`结构，所以该结构的引用计数器的值为 2。
 
题外话：zend_reference 又指向了一个整形或浮点型的 value，如果指向的 value 类型是 zend_string，那么该 value 引用计数器的值为 1。而 xdebug 出来的 refcount 显示的是 zend_reference 的计数器值（即 2）
 
### 三. 为什么初始数组的引用计数器的值为 2
 
#### 现象

```php
$var_empty_arr = [1, 2, '3'];
xdebug_debug_zval('var_empty_arr');

/** 输出 **
var_arr:
(refcount=3, is_ref=0)
array (size=3)
  0 => (refcount=0, is_ref=0)int 1
  1 => (refcount=0, is_ref=0)int 2
  2 => (refcount=1, is_ref=0)string '3' (length=1)
**********/
```
 
#### 原因
 
这牵扯到 PHP7 中的另一个概念，叫做`immutable array`（不可变数组）。 关于`immutable array`的详细介绍我放到下篇文章中讲，这里我们只需要知道，这样定义的数组，叫做 **`不可变数组`**  。

```
For arrays the not-refcounted variant is called an "immutable array". If you use opcache, then constant array literals in your code will be converted into immutable arrays. Once again, these live in shared memory and as such must not use refcounting. Immutable arrays have a dummy refcount of 2, as it allows us to optimize certain separation paths.


```
 
不可变数组和我们上面讲到的 **`内部字符串`**  一样，都是 **`不使用引用计数`**  的，但是不同点是，内部字符串的计数值恒为 0，而不可变数组会使用一个 **`伪计数值`**  2。


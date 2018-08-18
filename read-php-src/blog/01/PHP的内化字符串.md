## PHP的内化字符串

来源：[https://fengyoulin.com/2018/06/07/php_interned_string/](https://fengyoulin.com/2018/06/07/php_interned_string/)

时间 2018-06-07 14:10:07


此处所谓的“内化”，源自PHP源码中的interned一词，加上我对此种类型字符串行为特性的理解，译作内化。Zend引擎在使用内化的string与普通的string时，行为上有些不同，下面我们通过具体的实例来演示。


#### 一、普通的string变量

对于普通的string变量，使用引用计数来控制其生命周期，在变量赋值或unset时，引用计数会相应的增减，直到引用计数为0时，Zend引擎会将string变量释放掉。

示例一：

```php
<?php
$a = 'FILE:' . $_SERVER['argv'][0];
zendump_vars();
$b = $a;
zendump_vars();
unset($a);
zendump_vars();
```

以上代码中之所以要运行时拼接一个string再赋值给`$a`，是因为如果直接在代码中写上字符串字面量，会被PHP内化（后面有示例），而如果直接将`$_SERVER['argv'][0]`赋值给`$a`，则会影响引用计数对演示造成干扰。

输出结果如下：

``` 
vars(2): {
  $a ->
  zval(0x7f26e601e080) -> string(31,"FILE:/home/kylin/Desktop/v1.php") addr(0x7f26e60638a0) refcount(1)
  $b ->
  zval(0x7f26e601e090) : undefined
}
vars(2): {
  $a ->
  zval(0x7f26e601e080) -> string(31,"FILE:/home/kylin/Desktop/v1.php") addr(0x7f26e60638a0) refcount(2)
  $b ->
  zval(0x7f26e601e090) -> string(31,"FILE:/home/kylin/Desktop/v1.php") addr(0x7f26e60638a0) refcount(2)
}
vars(2): {
  $a ->
  zval(0x7f26e601e080) : undefined
  $b ->
  zval(0x7f26e601e090) -> string(31,"FILE:/home/kylin/Desktop/v1.php") addr(0x7f26e60638a0) refcount(1)
}
```

以上示例中可以看到：



* 当把`$a`的值赋给`$b`之后，Zend引擎并没有为`$b`重新分配一个新的string，而是直接使`$b`指向与`$a`相同的字符串地址0x7f26e60638a0处，并把字符串的引用计数增1。    
* 当unset掉`$a`之后，`$b`依然指向地址0x7f26e60638a0处的字符串，并且字符串的引用计数已经由2减为1。    
  

示例二：

```php
<?php
$a = 'FILE:' . $_SERVER['argv'][0];
zendump_vars();
$b = [$a => $a];
zendump_vars();
$a = 'CHANGED:' . $_SERVER['argv'][0];
zendump_vars();
```

输出结果如下：

``` 
vars(2): {
  $a ->
  zval(0x7f125d01e080) -> string(31,"FILE:/home/kylin/Desktop/v2.php") addr(0x7f125d0638a0) refcount(1)
  $b ->
  zval(0x7f125d01e090) : undefined
}
vars(2): {
  $a ->
  zval(0x7f125d01e080) -> string(31,"FILE:/home/kylin/Desktop/v2.php") addr(0x7f125d0638a0) refcount(3)
  $b ->
  zval(0x7f125d01e090) -> array(1) addr(0x7f125d063900) refcount(1) hash(8,1) bucket(8,1) data(0x7f125d069660)
  {
    ["FILE:/home/kylin/Desktop/v2.php"] len(31) addr(0x7f125d0638a0) refcount(3) =>
    zval(0x7f125d069660) -> string(31,"FILE:/home/kylin/Desktop/v2.php") addr(0x7f125d0638a0) refcount(3)
  }
}
vars(2): {
  $a ->
  zval(0x7f125d01e080) -> string(34,"CHANGED:/home/kylin/Desktop/v2.php") addr(0x7f125d063960) refcount(1)
  $b ->
  zval(0x7f125d01e090) -> array(1) addr(0x7f125d063900) refcount(1) hash(8,1) bucket(8,1) data(0x7f125d069660)
  {
    ["FILE:/home/kylin/Desktop/v2.php"] len(31) addr(0x7f125d0638a0) refcount(2) =>
    zval(0x7f125d069660) -> string(31,"FILE:/home/kylin/Desktop/v2.php") addr(0x7f125d0638a0) refcount(2)
  }
}
```


在以上示例中可以看到：

1.将`$a`同时用作key和value添加到数组`$b`之后，3个地方同时指向地址0x7f125d0638a0处的string，且引用计数由原来的1变成了3。

2.当我们为`$a`赋上一个新值之后，Zend引擎为`$a`在地址0x7f125d063960处分配了新的string，在`$b`中同时用作key和value的string引用计数由3减为2。

  
#### 二、内化的string

像如下这样直接写在代码中的字符串字面量就会被PHP在编译时内化：

```php
<?php
$a = 'hello, world!';
zendump_vars();
```

运行输出：

``` 
vars(1): {
  $a ->
  zval(0x7f884221e080) -> string(13,"hello, world!") addr(0x7f8842281320) interned
}
```

对于内化的string，Zend引擎不再为其维护引用计数，所以在运行时也不会被释放，最终会在Zend引擎shutdown时随之释放掉。

示例：

```php
<?php
$a = 'hello, world!';
zendump_vars();
$b = [$a => $a];
zendump_vars();
$a = 'great!';
zendump_vars();
```

运行输出：

``` 
vars(2): {
  $a ->
  zval(0x7fa552c1e080) -> string(13,"hello, world!") addr(0x7fa552c81320) interned
  $b ->
  zval(0x7fa552c1e090) : undefined
}
vars(2): {
  $a ->
  zval(0x7fa552c1e080) -> string(13,"hello, world!") addr(0x7fa552c81320) interned
  $b ->
  zval(0x7fa552c1e090) -> array(1) addr(0x7fa552c638a0) refcount(1) hash(8,1) bucket(8,1) data(0x7fa552c69660)
  {
    ["hello, world!"] len(13) addr(0x7fa552c81320) interned =>
    zval(0x7fa552c69660) -> string(13,"hello, world!") addr(0x7fa552c81320) interned
  }
}
vars(2): {
  $a ->
  zval(0x7fa552c1e080) -> string(6,"great!") addr(0x7fa552c01ac0) interned
  $b ->
  zval(0x7fa552c1e090) -> array(1) addr(0x7fa552c638a0) refcount(1) hash(8,1) bucket(8,1) data(0x7fa552c69660)
  {
    ["hello, world!"] len(13) addr(0x7fa552c81320) interned =>
    zval(0x7fa552c69660) -> string(13,"hello, world!") addr(0x7fa552c81320) interned
  }
}
```

从上面的例子可以看到，将一个内化的string赋值给一个变量时，能够像普通string变量一样直接指向同一个地址而不重新分配，就是没有了引用计数的概念。


#### 三、相关源码

zend_types.h中相关的结构定义和宏定义：

```c
typedef struct _zend_refcounted_h {
	uint32_t         refcount;			/* reference counter 32-bit */
	union {
		struct {
			ZEND_ENDIAN_LOHI_3(
				zend_uchar    type,
				zend_uchar    flags,    /* used for strings & objects */
				uint16_t      gc_info)  /* keeps GC root number (or 0) and color */
		} v;
		uint32_t type_info;
	} u;
} zend_refcounted_h;
...

struct _zend_string {
	zend_refcounted_h gc;
	zend_ulong        h;                /* hash value */
	size_t            len;
	char              val[1];
};
...

#define GC_FLAGS(p)					(p)->gc.u.v.flags
```

zend_string.h中相关的宏定义：

```c
#define IS_INTERNED(s)	ZSTR_IS_INTERNED(s)
...

#define ZSTR_IS_INTERNED(s)					(GC_FLAGS(s) & IS_STR_INTERNED)
```

以及在zend_string.c中关于interned string机制实现相关的函数，大家感兴趣的话可以自行阅读。


#### 备注

我刚刚修改了    [zendump][0]
中dump字符串相关的代码，使其能够打印出string的interned属性。之前的实现是参照`debug_zval_dump`的代码，其对开发者隐藏掉了很多Zend Engine底层的细节。



[0]: https://github.com/php7th/zendump
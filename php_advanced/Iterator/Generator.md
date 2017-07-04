# 生成器类

(No version information available, might only be in Git)

## 简介

**Generator** 对象是从 generators 返回的. 

**Caution**  **Generator** 对象不能通过 new 实例化. 

## 类摘要

```
Generator  implements Iterator  {

/* 方法 */

 public  mixed  current ( void )

 public  mixed  key ( void )

 public  void  next ( void )

 public  void  rewind ( void )

 public  mixed  send ( mixed $value )

 public  void  throw ( Exception $exception )

 public  bool  valid ( void )

 public  void  __wakeup ( void )

}
```
## Table of Contents

* Generator::current — 返回当前产生的值
* Generator::key — 返回当前产生的键
* Generator::next — 生成器继续执行
* Generator::rewind — 重置迭代器
* Generator::send — 向生成器中传入一个值
* Generator::throw — 向生成器中抛入一个异常
* Generator::valid — 检查迭代器是否被关闭
* Generator::__wakeup — 序列化回调

```php
<?php
function fib($n)
{
    $cur = 1;
    $prev = 0;
    for ($i = 0; $i < $n; $i++) {
        yield $cur;

        $temp = $cur;
        $cur = $prev + $cur;
        $prev = $temp;
    }
}

$fibs = fib(9);
foreach ($fibs as $fib) {
    echo " " . $fib;
}

// prints: 1 1 2 3 5 8 13 21 34
```





# Generator::current

(PHP 5 >= 5.5.0, PHP 7)

 Generator::current — 返回当前产生的值

### 说明

 public  mixed  **Generator::current** ( void )

### 参数

此函数没有参数。

### 返回值

返回当前产生的值。

----

# Generator::key

(PHP 5 >= 5.5.0, PHP 7)

 Generator::key — 返回当前产生的键

### 说明

 public  mixed  **Generator::key** ( void )

获取产生的值的键 

### 参数

此函数没有参数。

### 返回值

返回当前产生的键。

-----

# Generator::next

(PHP 5 >= 5.5.0, PHP 7)

 Generator::next — 生成器继续执行

### 说明

 public  void  **Generator::next** ( void )

### 参数

此函数没有参数。

### 返回值

没有返回值。

----


# Generator::rewind

(PHP 5 >= 5.5.0, PHP 7)

 Generator::rewind — 重置迭代器

### 说明

 public  void  **Generator::rewind** ( void )

如果迭代已经开始了，这里会抛出一个异常。 

### 参数

此函数没有参数。

### 返回值

没有返回值。

----

# Generator::send

(PHP 5 >= 5.5.0, PHP 7)

 Generator::send — 向生成器中传入一个值

### 说明

 public  mixed  **Generator::send** ( mixed $value )

向生成器中传入一个值，并且当做 yield 表达式的结果，然后继续执行生成器。 

如果当这个方法被调用时，生成器不在 yield 表达式，那么在传入值之前，它会先运行到第一个 yield 表达式。As such it is not necessary to "prime" PHP generators with a Generator::next() call (like it is done in Python). 

### 参数

- value
传入生成器的值。这个值将会被作为生成器当前所在的 yield 的返回值 

### 返回值

返回生成的值。

----


# Generator::throw

(PHP 5 >= 5.5.0, PHP 7)

 Generator::throw — 向生成器中抛入一个异常

### 说明

 public  void  **Generator::throw** ( Exception $exception )

### 参数

exception

### 返回值

返回生成的值。


----


# Generator::valid

(PHP 5 >= 5.5.0, PHP 7)

 Generator::valid — 检查迭代器是否被关闭

### 说明

 public  bool  **Generator::valid** ( void )

### 参数

此函数没有参数。

### 返回值

如果迭代器已被关闭返回 **FALSE**，否则返回 **TRUE**。

----


# Generator::__wakeup

(PHP 5 >= 5.5.0, PHP 7)

 Generator::__wakeup — 序列化回调

### 说明

 public  void  **Generator::__wakeup** ( void )

抛出一个异常以表示生成器不能被序列化。 

### 参数

此函数没有参数。

### 返回值

没有返回值。


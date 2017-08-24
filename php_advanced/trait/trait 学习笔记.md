# PHP trait 学习笔记 

    发表于 2017-01-22   | 

### 介绍

自 PHP 5.4.0 起，PHP 实现了一种代码复用的方法，称为 trait。
众所周知，PHP 中是单继承的，trait 是为类似 PHP 的单继承语言而准备的一种代码复用机制。trait 为了减少单继承语言的限制，使开发人员能够自由地在不同层次结构内独立的类中复用 method。trait 和 Class 组合的语义定义了一种减少复杂性的方式，避免传统多继承和 Mixin 类相关典型问题。
trait 和 Class 相似，但仅仅旨在用细粒度和一致的方式来组合功能。 无法通过 trait 自身来实例化。它为传统继承增加了水平特性的组合；也就是说，应用的几个 Class 之间不需要继承。

### 实例

首先我们举个例子来介绍 trait，和类定义相似，但使用关键字 trait 定义。在类中使用 use 组合。


```php
<?php
trait T
{
    public function m1()
    {
        return 'm1';
    }
    public function m2()
    {
        return 'm2';
    }
}
class Demo
{
    use T;
    public function test()
    {
        return 'test';
    }
}
$demo = new Demo;
echo $demo->m1(), PHP_EOL;
echo $demo->test(), PHP_EOL;
```
### 使用多个 trait

```php
<?php
trait T
{
    public function m1()
    {
        return 'm1';
    }
    public function m2()
    {
        return 'm2';
    }
}
trait T2
{
    public function m3()
    {
        return 'm3';
    }
}
class Demo
{
    use T, T2;
    public function test()
    {
        return 'test';
    }
}
$demo = new Demo;
echo $demo->m1(), PHP_EOL;
echo $demo->m3(), PHP_EOL;
echo $demo->test(), PHP_EOL;
```
### 多个 trait 冲突解决
如果使用多个 trait，但是出现了方法名相同，这是就出现了冲突，就要手动指定使用哪个 trait 的方法，使用 insteadof 关键字实现。

```php
<?php
trait T
{
    public function m1()
    {
        return 'm1';
    }
    public function m2()
    {
        return 'm2';
    }
}
trait T2
{
    public function m1()
    {
        return 'm3';
    }
}
class Demo
{
    use T, T2{
        // 使用 T 的 m1 方法
        T::m1 insteadof T2;
    }
    public function test()
    {
        return 'test';
    }
}
$demo = new Demo;
echo $demo->m1(), PHP_EOL;
echo $demo->test(), PHP_EOL;
```
在冲突的时候，也可以使用 use 定义方法别名解决冲突，例子如下：

```php
<?php
trait T
{
    public function m1()
    {
        return 'm1';
    }
    public function m2()
    {
        return 'm2';
    }
}
trait T2
{
    public function m1()
    {
        return 'm3';
    }
}
class Demo
{
    use T, T2{
        T::m1 insteadof T2;
        T2::m1 as new_m1;
    }
    public function test()
    {
        return 'test';
    }
}
$demo = new Demo;
echo $demo->m1(), PHP_EOL;
echo $demo->new_m1(), PHP_EOL;
echo $demo->test(), PHP_EOL;
```
### 改变访问权限
也可以使用 use 关键字来改变方法的访问权限。

```php
<?php
trait T
{
    public function m1()
    {
        return 'm1';
    }
    public function m2()
    {
        return 'm2';
    }
}
class Demo
{
    use T{
        m2 as protected;
    }
    public function test()
    {
        return 'test';
    }
}
$demo = new Demo;
echo $demo->m1(), PHP_EOL;
echo $demo->m2(), PHP_EOL;
echo $demo->test(), PHP_EOL;
```

上面我只是介绍了一些常用的特性，详细可参考 [官方手册][0]。

[0]: http://php.net/manual/zh/language.oop5.traits.php
## [PHP 代码复用的方式](https://segmentfault.com/a/1190000012019510)


什么是 Trait?

> 自 PHP 5.4.0 起，PHP 实现了一种代码复用的方法，称为 Trait。

* Trait 是为了单继承语言而准备的一种代码复用机制。
* Trait 和 Class 相似，它为传统的继承增加了水平的特性的组合，多个无关的 Class 之间不需要互相继承
* Trait 使得无关的 Class 可以使用相同的属性和方法。


> 简单使用

```php
<?php

trait Test
{
    public function echoHello()
    {
        echo 'Hello Trait';
    }
}

class Base
{
    public function index()
    {
        echo 'index';
    }
}

class One extends Base
{
    use Test;
}

class Two extends Base
{
    use Test;
}

$one = new One();
$two = new Two();

echo $one->echoHello();
echo $one->index();
echo $two->echoHello();
```
结果输出 Hello Trait index Hello Trait。

> 从基类继承的成员会被 Trait 插入的成员所覆盖。优先顺序是来自当前类的成员覆盖了 Trait 的方法，而 Trait 则覆盖了被继承的方法。

```php
<?php

trait Test
{
    public function echoHello()
    {
        echo 'Hello Trait';
    }
}

class Base
{
    use Test;

    public function echoHello()
    {
        echo 'Hello Base';
    }
}

class One extends Base
{
    use Test;

    public function echoHello()
    {
        echo 'Hello One';
    }
}

class Two extends Base
{
    use Test;
}

$one = new One();
$two = new Two();
$base = new Base();

echo $one->echoHello();

echo $two->echoHello();

echo $base->echoHello();
```
结果输出 Hello One Hello Trait Hello Base。

* class one 示例覆盖基类和 Trait Test，说明当前类的方法优先级高于他们。
* class Two 示例覆盖基类，Trait 的有优先级高于继承的基类。
* class Base 示例覆盖 Trait Test，说明当前类的方法优先级高于 Trait。


> 通过逗号分隔，在 use 声明列出多个 trait，可以都插入到一个类中。

```php
<?php

trait Test
{
    public function echoHello()
    {
        echo 'Hello ';
    }
}

trait TestTwo
{
    public function echoWord()
    {
        echo 'word !';
    }
}


class One
{
    use Test,TestTwo;
}

$one  = new One();

echo $one->echoHello();
echo $one->echoWord();
```
结果输出 Hello word !。

> 如果两个 Trait 都插入了一个同名的方法，如果没有明确解决冲突将会产生一个致命错误。

```php
<?php

trait Test
{
    public function echoHello()
    {
        echo 'Hello Test';
    }

    public function echoWord()
    {
        echo 'word Test';
    }
}

trait TestTwo
{
    public function echoHello()
    {
        echo 'Hello TestTwo ';
    }

    public function echoWord()
    {
        echo 'word TestTwo';
    }
}

class One
{
    use Test, TestTwo {
        Test::echoHello as echoTest;
        Test::echoWord insteadof TestTwo;
        TestTwo::echoHello insteadof Test;
    }
}

$one = new One();

echo $one->echoTest();
echo $one->echoWord();
echo $one->echoHello();
```

输出结果：Hello Test word Test Hello TestTwo。

* 使用 `as` 作为别名，即 Test::echoHello as echoTest; 输出 Trait Test 中的 echoHello.
* 使用 `insteadof` 操作符用来排除掉其他 Trait,即 Test::echoWord insteadof TestTwo; 输出的是 word Test,使用 Trait Test 中的 echoWord


> 修改 方法的控制权限

```php
<?php

trait Test
{
    public function echoHello()
    {
        echo 'Hello';
    }

    public function echoWord()
    {
        echo 'word';
    }
}

trait TestTwo
{
    public function echoHello()
    {
        echo 'Hello TestTwo ';
    }

    public function echoWord()
    {
        echo 'word TestTwo';
    }
}

class One
{
    use Test {
        echoHello as private;
    }
}

class Two
{
    use Test {
        echoHello as private echoTwo;
    }
}

$one = new One();
$two = new Two();

echo $two->echoHello();
```

* 输出结果 Hello。
* class one 中使用 as 将 echoHello 设为私有，则通过 class one 不能访问 echoHello。
* class two 中使用 as 先将其重新命名，然后将新命名方法设置为私有，原 Trait 中的方法可以正常访问。

Trait 中还可以像类一样定义属性。就是很好用的啦！


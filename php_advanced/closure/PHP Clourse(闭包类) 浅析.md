## PHP Clourse(闭包类) 浅析

来源：[https://juejin.im/post/5b8129aa51882542f03807a8](https://juejin.im/post/5b8129aa51882542f03807a8)

时间 2018-08-27 10:30:32


闭包是指在创建时封装周围状态的函数。即使闭包所在的环境不存在了，闭包中封装的状态依然存在。

在 PHP 里所有的闭包都是`Clourse`类所实例化的一个对象，也就是说闭包与其他 PHP 对象没有什么不同。而一个对象就必然有其方法和属性，这篇文章将总结 PHP 中闭包的基础用法和`Clourse`类方法的作用。


## 0x01 闭包基本用法

下面看看最基本的闭包使用方法：

```php
<?php
$hello = function ($word) {
    return 'hello ' . $word;
};

echo $hello('world');
// 输出 hello world
```

嘿，这段代码最直观的感受就是将一个函数赋值给了`$hello`变量，然后通过`$hello`直接调用它。但是这个闭包并没有从父作用域中继承变量（就是封装周围状态），我们可以通过`use`关键字从闭包的父作用域继承变量。示例如下：

```php
<?php
$name = 'panda';

$hello = function () use ($name) {
    return 'hello ' . $name;
};

echo $hello();
// 输出 hello panda
```

PHP 7.1 起，`use`不能传入此类变量： superglobals、 $this 或者和参数重名。

此外在使用`use`关键字时，父作用域的变量是通过值传递进闭包的。也就是说一旦闭包创建完成，外部的变量即使修改也不会影响传递进闭包内的值（就是即使闭包所在的环境不存在了，闭包中封装的状态依然存在）。示例如下：

```php
<?php
$name = 'panda';

$hello = function () use ($name) {
    return 'hello ' . $name;
};

$name = 'cat';

echo $hello();
// 输出 hello panda
```

传递变量的引用可以使闭包修改外部变量的值，示例如下：

```php
<?php
$name = 'panda';

$changeName = function () use (&$name) {
    $name = 'cat';
};

$changeName();

echo $name;
// 输出 cat
```

注意：PHP 中传递对象时，默认是以引用传递所以在闭包内操作`use`传递的对象时需要特别注意。示例如下：

```php
<?php
class Dog {
    public $name = 'Wang Cai';
}

$dog = new Dog();

$changeName = function () use ($dog) {
    $dog->name = 'Lai Fu';
};

$changeName();

echo $dog->name;
// 输出 Lai Fu
```


## 0x02 Clourse 类


### 证明闭包只是 Clourse 类对象

```php
<?php
$clourse = function () {
    echo 'hello clourse';
};

if (is_object($clourse)) {
    echo get_class($clourse);
}
// 输出 Closure
```

上面的代码将输出 Closure 证明了闭包只是一个普通的`Closure`类对象。


### Clourse 类摘要

我们可以从PHP 官方手册 看到闭包类的相关信息，下面是我在 PhpStorm 的本地文档查看到`Clourse`类摘要。

```php
/**
 * Class used to represent anonymous functions.
 *Anonymous functions, implemented in PHP 5.3, yield objects of this type.
 * This fact used to be considered an implementation detail, but it can now be relied upon.
 * Starting with PHP 5.4, this class has methods that allow further control of the anonymous function after it has been created.
 *Besides the methods listed here, this class also has an __invoke method.
 * This is for consistency with other classes that implement calling magic, as this method is not used for calling the function.
 * @link http://www.php.net/manual/en/class.closure.php
 */
final class Closure {

    /**
     * This method exists only to disallow instantiation of the Closure class.
     * Objects of this class are created in the fashion described on the anonymous functions page.
     * @link http://www.php.net/manual/en/closure.construct.php
     */
    private function __construct() { }

    /**
     * This is for consistency with other classes that implement calling magic,
     * as this method is not used for calling the function.
     * @param mixed $_ [optional]
     * @return mixed
     * @link http://www.php.net/manual/en/class.closure.php
     */
    public function __invoke(...$_) { }

    /**
     * Duplicates the closure with a new bound object and class scope
     * @link http://www.php.net/manual/en/closure.bindto.php
     * @param object $newthis The object to which the given anonymous function should be bound, or NULL for the closure to be unbound.
     * @param mixed $newscope The class scope to which associate the closure is to be associated, or 'static' to keep the current one.
     * If an object is given, the type of the object will be used instead.
     * This determines the visibility of protected and private methods of the bound object.
     * @return Closure Returns the newly created Closure object or FALSE on failure
     */
    function bindTo($newthis, $newscope = 'static') { }

    /**
     * This method is a static version of Closure::bindTo().
     * See the documentation of that method for more information.
     * @static
     * @link http://www.php.net/manual/en/closure.bind.php
     * @param Closure $closure The anonymous functions to bind.
     * @param object $newthis The object to which the given anonymous function should be bound, or NULL for the closure to be unbound.
     * @param mixed $newscope The class scope to which associate the closure is to be associated, or 'static' to keep the current one.
     * If an object is given, the type of the object will be used instead.
     * This determines the visibility of protected and private methods of the bound object.
     * @return Closure Returns the newly created Closure object or FALSE on failure
     */
    static function bind(Closure $closure, $newthis, $newscope = 'static') { }

    /**
     * Temporarily binds the closure to newthis, and calls it with any given parameters.
     * @link http://php.net/manual/en/closure.call.php
     * @param object $newThis The object to bind the closure to for the duration of the call.
     * @param mixed $parameters [optional] Zero or more parameters, which will be given as parameters to the closure.
     * @return mixed
     * @since 7.0
     */
    function call ($newThis, ...$parameters) {}
    
    /**
     * @param callable $callable
     * @return Closure
     * @since 7.1
     */
    public static function fromCallable (callable $callable) {}
}
```

首先`Clourse`类为`final`类，也就是说它将无法被继承，其次它的构造函数`__construct`被设为`private`即无法通过`new`关键字实例化闭包对象，这两点保证了闭包只能通过`function (...) use(...) {...}`这种语法实例化 。


### 为什么闭包可以当作函数执行？

从上面的类摘要中我们看出`Clourse`类实现了__invoke 方法，在 PHP 官方手册中对该方法解释如下：

当尝试以调用函数的方式调用一个对象时，__invoke()方法会被自动调用。

这就是闭包可以被当作函数执行的原因。


### 绑定指定的$this对象和类作用域

在允许使用闭包路由的框架中（如：Slim），我们可以看见如下写法：

```php
$app->get('/test', function () {
    echo $this->request->getMethod();
});
```

在一个闭包居然能中使用`$this`？这个`$this`指向哪个对象？

通过`bindTo`和`bind`方法都能够实现绑定`$this`和类作用域的功能，示例如下：

```php
<?php

class Pandas {
    public $num = 1;
}

$pandas = new Pandas();

$add = function () {
    echo ++$this->num . PHP_EOL;
};

$newAdd1 = $add->bindTo($pandas);
$newAdd1();
// 输出 2
$newAdd2 = Closure::bind($add, $pandas);
$newAdd2();
// 输出 3
```

上面的这段例子将指定对象绑定为闭包的`$this`，但是我们并没有指定类作用域。所以如果将`Pandas`类的`$num`属性改写为`protected`或`private`则会抛出一个致命错误！

```php
Fatal error: Uncaught Error: Cannot access protected property Pandas::$num


```

在需要访问绑定对象的非公开属性或方法时，我们需要指定类作用域，示例如下：

```php
<?php

class Pandas {
    protected $num = 1;
}

$pandas = new Pandas();

$add = function () {
    echo ++$this->num . PHP_EOL;
};

$newAdd1 = $add->bindTo($pandas, $pandas);
$newAdd1();
// 输出 2
$newAdd2 = Closure::bind($add, $pandas, 'Pandas');
$newAdd2();
// 输出 3
```

这里我们看见`bindTo`和`bind`方法都指定了`$newscope`参数，`$newscope`参数默认为`static`即不改变类作用域。`$newscope`参数接受类名或对象，并将闭包的类作用域改为指定的类作用域，此时`Pandas`类的`$num`属性便能够被闭包访问。


### 一次性绑定 $this 对象和类作用域并执行（PHP7）
`bindTo`和`bind`方法每次指定新的对象和类作用域时都要将原闭包进行复制然后返回新的闭包，在需要多次修改绑定对象的情景下便显得繁琐，所以 PHP7 提供了一个新的方法`call`它能将闭包临时的绑定到一个对象中（类作用域同时被修改为该对象所属的类）并执行。示例如下：

```php
<?php

class Pandas {
    protected $num = 1;
}

$pandas = new Pandas();

$add = function ($num) {
    $this->num += $num;
    echo $this->num . PHP_EOL;
};

$add->call($pandas, 5);
// 输出 6
```


### Callable 转为闭包（PHP7.1）

在 PHP7.1 中`Closure`类存在`fromCallable`方法能够将`callable`类型的值转为闭包，示例如下：

```php
<?php

class Foo
{
    protected $num = 1;

    public static function hello(string $bar)
    {
        echo 'hello ' . $bar;
    }
}

$hello = Closure::fromCallable(['Foo', 'hello']);
$hello('world');
```

这种写法还是挺爽的毕竟通过闭包调用总比用`call_user_func`函数调用爽的多^_^。


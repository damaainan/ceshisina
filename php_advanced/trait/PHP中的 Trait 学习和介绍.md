## PHP中的 Trait 学习和介绍

<font face=微软雅黑>

在阅读Laravel源码的时候接触到了`Trait`，就学习了一下，工作这么多年了，虽然以前听说`Trait`，但是还真的没有用过。于是到PHP官网查看了下，官方的解释： `Trait` 是为类似 PHP 的单继承语言而准备的一种代码复用机制。`Trait` 为了减少单继承语言的限制，使开发人员能够自由地在不同层次结构内独立的类中复用 method。`Trait` 和 `Class` 组合的语义定义了一种减少复杂性的方式，避免传统多继承和 `Mixin` 类相关典型问题。

`Trait` 和 `Class` 相似，但仅仅旨在用细粒度和一致的方式来组合功能。 无法通过 `trait` 自身来实例化。它为传统继承增加了水平特性的组合；也就是说，应用的几个 `Class` 之间不需要继承。


`Trait` 的代码格式为：

    trait ezcReflectionReturnInfo
    {
        public $propertyName;
        public function getMethod()
        {
        }
        ...更多方法
    }

看格式确实和`Class`一样，这里就一步步分析`trait`的属性

#### 继承和接口

我们知道`Class`是可以继承和实现一些接口的（PHP中的类是单继承，但是可以一次实现多个接口，如下代码）

```php
<?php
//声明两个接口
interface IbaseInterfaceA
{
    public function getFaceMethodA();
}

interface IbaseInterfaceB
{
    public function getFaceMethodB();
}

// 声明一个抽象类
abstract class parentClass implements IbaseInterfaceA, IbaseInterfaceB
{
    public function getFaceMethodA()
    {
    }
}

// 子类
class LogClass extends parentClass
{
    public function getFaceMethodB()
    {
    }
}
```

到此`LogClass`可以正常使用没有任何问题，这 **说明** **Class可以一次实现多个接口，但是只能单继承**

我们再看看`Trait`是否也有继承和接口

```php
<?php
interface IbaseInterfaceA
{
    public function getFaceMethodA();
}

trans BaseTrait implements IbaseInterfaceA
{
    public function getFaceMethodA()
    {
    }
}
// Fatal error: Cannot use 'IbaseInterfaceA' as interface on 'BaseTrait' since it is a Trait
```

**出现错误，说明`Trait`不能实现接口，看下面的继承**

```php
<?php
class BaseClass
{
}

trait ParentTrait
{
}

trait BaseTraitA extends ParentTrait
{
}

trait BaseTraitB extends BaseClass
{
}
// Fatal error: A trait (BaseTrait) cannot extend a class. Traits can only be composed from 
// other traits with the 'use' keyword
```

**也是同样出现错误，说明也不能实现继承（无论是继承类还是     `trait` 都不能行）**

**总结：`Trait` 虽然和 `Class` 在代码上写法相似，但是不能有 `Class` 中的继承和实现接口，如果想做“继承”，只能用use关键词** ，如下代码

```php
<?php
trait ParentTrait
{
    public function getMethod()
    {
    }
}

trait BaseTraitA
{
    use ParentTrait;
}

trait BaseTraitB
{
    use BaseTraitA;
}
```

#### 实例化

`class`中如果想实例化一个对象，直接用 `new` 关键词，如 `$abc = new BaseClass()`; 但是 `trait` 是否可以实例化呢，代码如下：

```php
<?php
trait ParentTrait
{
    public function getMethod()
    {
    }
}

$abc = new ParentTrait(); // Fatal error: Cannot instantiate trait ParentTrait
```

**同样错误，说明 `trait` 是不能直接实例化的。如果要想使用 `trait`，只能在类中用 `use` 关键词** ，如下代码

```php
<?php
trait ParentTrait
{
    public function getMethod()
    {
        echo 123;
    }
}

class BaseClass
{
    use ParentTrait;
}

$abc = new BaseClass(); 
$abc->getMethod(); // 输出 123
```

#### 修饰符

`Class`中的方法和属性有一些修饰符，如 `public`、`protected`、`private`、`static`、`abstract`、`final`， `trait`中是否也有这些修饰符呢，如下代码

```php
<?php
trait ParentTrait
{
    public function getMethoda()
    {
        echo 123;
    }

    protected function getMethodb()
    {
    }

    private function getMethodc()
    {
    }

    public static function getMethodd()
    {
    }

    final function getMethode()
    {
    }

    // 抽象方法，使用此 Trait 的类必须实现此方法
    abstract public function getMethodf();
}

class BaseClass
{
    use ParentTrait;

    public function getMethodf()
    {
    }
}

$abc = new BaseClass();
```

**发现并没有什么错误，说明 `Trait` 中也是支持这些修饰符的，** 至于这些修饰符是什么意思，不在详解，都是常用的。

#### 优先级

我们知道在 `Class` 中，子类中的方法优先级大于父类（也就是平常所说的覆盖或重载），`Trait`中的优先级是怎么样的呢，看下面的代码

```php
<?php
trait BaseTrait
{
    public function getMethoda()
    {
        echo 'BaseTrait 123';
    }
}

class ParentClass
{
    use BaseTrait;

    public function getMethoda()
    {
        echo 'ParentClass 123';
    }
}

class BaseClass extends ParentClass
{
    use BaseTrait;

    public function getMethoda()
    {
        echo 'BaseClass 123';
    }
}

$abc = new BaseClass(); 
$abc->getMethoda(); // 输出 BaseClass 123

// 把 BaseClass 中的 getMethoda 方法删除掉，再调用
// $abc->getMethoda();  // 输出  BaseTrait 123

// 把 BaseClass 中的 use BaseTrait 删除旧，再调用
// $abc->getMethoda();  // 输出  ParentClass 123

// 把 ParentClass 中的 getMethoda 方法删除掉，再调用
// $abc->getMethoda();  // 输出  BaseTrait 123
```

以上说明, `Trait` 的优先级为：**子类中的方法 > 子类中的 trait > 父类中的方法 > 父类中的 trait**

分析 `Trait` 中的属性和继承的冲突

```php
class PHP中的 Trait 学习和介绍（二）extends  PHP中的 Trait 学习和介绍（一）
{
    // 本节学习内容
}
```

![未标题-1.jpg][0]

#### 多个 trait

一个类中可以使用多个 `trait` ，中间用逗号分隔。

```php
<?php
trait Hello
{
    public function sayHello()
    {
        echo 'Hello';
    }
}

trait World
{
    public function sayWorld()
    {
        echo ' World';
    }
}

class HelloWord
{
    use Hello, World;

    public function say()
    {
        $this->sayHello();
        $this->sayWorld();
    }
}

$hello = new HelloWord();
$hello->say();
```

以上程序会输出 Hello World

#### 冲突的解决

如果两个 `trait` 都插入了一个同名的方法，如果没有明确解决冲突将会产生一个致命错误。为了解决多个 `trait` 在同一个类中的命名冲突，需要使用 `insteadof` 操作符来明确指定使用冲突方法中的哪一个。

```php
<?php
trait A {
    public function sayHello() {
        echo 'Hello Hello';
    }

    public function haha() {
        echo "Hello haha";
    }
}

trait B {
    public function sayHello() {
        echo 'Hello World';
    }

    public function haha() {
        echo "World haha";
    }
}

class HelloWord {
    use A, B {
        B::sayHello insteadof A;
        A::haha insteadof B;
    }
}

$hello = new HelloWord();
$hello->sayHello(); // Hello World
$hello->haha();     // Hello haha
```

以上方式仅允许排除掉其它方法，**as 操作符可以将其中一个冲突的方法以另一个名称来引入**。如下代码，把A中的 `sayHello` 换成另一个名字`asayHello`

```php
<?php
trait A
{
    public function sayHello()
    {
        echo 'Hello Hello';
    }
}

trait B
{
    public function sayHello()
    {
        echo 'Hello World';
    }
}

class HelloWord
{
    use A, B 
    {
        B::sayHello insteadof A;
        A::sayHello as asayHello;
    }
}

$hello = new HelloWord();
$hello->sayHello();     // Hello World
$hello->asayHello();    // Hello Hello
```

#### 使用 as 语法还可以用来调整方法的访问控制

```php
<?php
trait A
{
    public function sayHelloa()
    {
        echo 'Hello Hello';
    }
}

trait B
{
    public function sayHellob()
    {
        echo 'Hello World';
    }
}

class HelloWord
{
    use A, B 
    {
        B::sayHellob as protected;
    }
}

$hello = new HelloWord();
$hello->sayHelloa();    // Hello Hello
// $hello->sayHellob(); // 出错，不能访问 protected method
```

最后说一点 `trait` 中的静态方法可以直接使用 `trait` 来调用，代码如下：

```php
<?php
trait BaseTrait 
{ 
    public function sayHello() 
    { 
        return 'Hello'; 
    }
} 

echo BaseTrait::sayHello(); // 返回 Hello
```

</font>

[0]: ../img/1482112405387588.jpg


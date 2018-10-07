## PHP 依赖注入容器实现

来源：[https://juejin.im/post/5baeeb5be51d450e99433e57](https://juejin.im/post/5baeeb5be51d450e99433e57)

时间 2018-10-06 09:44:42


在看 Laravel 文档的时候发现入门指南的下一章便是核心架构，对于我这种按部就班往下读的同学这简直是劝退篇。各种之前没有接触过的概念砸得人头晕，容器便是其中之一。不过在拜读过几篇文章后也逐渐理解了容器的作用，所以特此总结一番。


## 0x01 为何要有容器？

这个问题可以也可以替换为「容器解决了什么问题？」。在此之前我们需要理解依赖注入这个概念，可以看一下这篇文章：    [简单解释什么是 依赖注入 和 控制反转][0]
。在实践依赖注入的时候我们会遇到一个问题，这里我将通过示例代码解释，代码如下：

```php
class Bread
{
}

class Bacon
{
}

class Hamburger
{
    protected $materials;

    public function __construct(Bread $bread, Bacon $bacon)
    {
        $this->materials = [$bread, $bacon];
    }
}

class Cola
{
}

class Meal
{
    protected $food;

    protected $drink;

    public function __construct(Hamburger $hamburger, Cola $cola)
    {
        $this->food  = $hamburger;
        $this->drink = $cola;
    }
}
```

上面是按照依赖注入实现的一段代码，我们可以看见套餐类（Meal）依赖汉堡类（Hamburger）和可乐类（Cola），并且汉堡类又依赖于面包类（Bread）和培根类（Bacon）。通过依赖注入能达到松耦合的效果但是这也使得实例化一个有多个依赖的类会变得十分麻烦，下面这段代码是实例化一个套餐类的示例：

```php
$bread = new Bread();
$bacon = new Bacon();

$hamburger = new Hamburger($bread, $bacon);
$cola = new Cola();

$meal = new Meal($hamburger, $cola);
```

可以看见为了获得一个套餐对象，我们需要先实例化该对象的依赖，如果依赖还存在依赖，我们还需要在实例化依赖的依赖……为了解决这个问题容器就应运而生了，容器的定位就是「管理类的依赖和执行依赖注入的工具」。通过容器我们可以将实例化这个过程给自动化，比如我们可以直接用一行代码获取套餐对象：

```php
$container->get(Meal::class);
```


## 0x01 简单容器的实现

下面这段代码是一个简单容器的实现：

```php
class Container
{
    /**
     * @var Closure[]
     */
    protected $binds = [];

    /**
     * Bind class by closure.
     *
     * @param string $class
     * @param Closure $closure
     * @return $this
     */
    public function bind(string $class, Closure $closure)
    {
        $this->binds[$class] = $closure;

        return $this;
    }

    /**
     * Get object by class
     *
     * @param string $class
     * @param array $params
     * @return object
     */
    public function make(string $class, array $params = [])
    {
        if (isset($this->binds[$class])) {
            return ($this->binds[$class])->call($this, $this, ...$params);
        }

        return new $class(...$params);
    }
}
```

这个容器只有两个方法`bind`和`make`，`bind`方法将一个类名和一个闭包进行绑定，然后`make`方法将执行指定类名对应的闭包，并返回该闭包的返回值。我们通过容器的使用示例加深理解：

```php
$container = new Container();

$container->bind(Hamburger::class, function (Container $container) {
    $bread = $container->make(Bread::class);
    $bacon = $container->make(Bacon::class);

    return new Hamburger($bread, $bacon);
});

$container->bind(Meal::class, function (Container $container) {
    $hamburger = $container->make(Hamburger::class);
    $cola      = $container->make(Cola::class);
    return new Meal($hamburger, $cola);
});

// 输出 Meal
echo get_class($container->make(Meal::class));
```

通过上面这个例子我们可以知道`bind`方法传递的是一个「返回类名对应的实例化对象」的闭包，而且该闭包还接收该容器作为参数，所以我们还可以在该闭包内使用容器获取依赖。上面这段代码虽然看起来似乎比使用`new`关键字还复杂，但实际上对每一个类，我们只需要`bind`一次即可。以后每次需要该对象直接用`make`方法即可，在我们的工程中肯定会节省很多代码量。


## 0x02 通过反射强化容器

「反射」官方手册php.net/manual/zh/b…

在上面的的简单容器的例子里，我们还需要通过`bind`方法写好实例化的「脚本」，那我们试想有没有一种方法能够直接生成我们需要的实例呢？其实通过「反射」并在构造函数指定参数的「类型提示类」我们就能实现自动解决依赖的功能。因为通过反射我们可以获取指定类构造函数所需要的参数和参数类型，所以我们的容器可以自动解决这些依赖。示例代码如下：

```php
/**
 * Get object by class
 *
 * @param string $class
 * @param array $params
 * @return object
 */
public function make(string $class, array $params = [])
{
    if (isset($this->binds[$class])) {
        return ($this->binds[$class])->call($this, $this, ...$params);
    }

    return $this->resolve($abstract);
}

/**
 * Get object by reflection
 *
 * @param $abstract
 * @return object
 * @throws ReflectionException
 */
protected function resolve($abstract)
{
    // 获取反射对象
    $constructor = (new ReflectionClass($abstract))->getConstructor();
    // 构造函数未定义，直接实例化对象
    if (is_null($constructor)) {
        return new $abstract;
    }
    // 获取构造函数参数
    $parameters = $constructor->getParameters();
    $arguments  = [];
    foreach ($parameters as $parameter) {
        // 获得参数的类型提示类
        $paramClassName = $parameter->getClass()->name;
        // 参数没有类型提示类，抛出异常
        if (is_null($paramClassName)) {
            throw new Exception('Fail to get instance by reflection');
        }
        // 实例化参数
        $arguments[] = $this->make($paramClassName);
    }

    return new $abstract(...$arguments);
}
```

以上代码基于只是修改了原容器类的`make`方法，`binds`数组中没有找到指定类绑定的闭包后执行`resolve`方法。其中`resolve`方法只是简单的通过反射获取指定类的构造函数并将其依赖实例化，最后实例化指定类。到了这一步以后我们实例化套餐类就真的只需要一行代码了，连配置都不用:-D。

```php
$container->make(Meal::class);
```

当然现在这个容器还是相当简陋的，因为如果指定类依赖标量值（比如：字符串，数组，数值等非对象类型）会直接抛出异常，也无法指定部分依赖并且如果依赖的是接口的话还会出错/(ㄒoㄒ)/ ~ ~，但这些功能都在一些成熟的容器库都有。如果感兴趣可以去看它们的源代码，这里我推荐看    [Pipmle][1]
这个项目。


## 0x03 总结

本文主要介绍了容器的应用场景并实现了一个简单的容器，通过使用容器我们能够很方便的解决依赖注入带来的问题。但是容器也并不是没有缺点，因为大部分容器都应用了反射技术，这会带来较大的性能消耗而且通过容器间接生成的实例 IDE 往往不能识别它的类型，所以就不会有自动提示（可以通过写文档注释解决）。不过个人感觉引入容器其实还是利大于弊滴（纯属个人感觉）！

[PHP 依赖注入容器实现 - 原文地址][2]


[0]: https://link.juejin.im?target=https%3A%2F%2Flaravel-china.org%2Farticles%2F5222%2Fsimply-explain-what-dependency-injection-and-control-inversion-are
[1]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fsilexphp%2FPimple
[2]: https://link.juejin.im?target=https%3A%2F%2Fwww.0php.net%2Fposts%2FPHP-%25E4%25BE%259D%25E8%25B5%2596%25E6%25B3%25A8%25E5%2585%25A5%25E5%25AE%25B9%25E5%2599%25A8%25E5%25AE%259E%25E7%258E%25B0.html
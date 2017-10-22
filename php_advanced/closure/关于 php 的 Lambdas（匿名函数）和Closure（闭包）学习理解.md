# 关于 php 的 Lambdas（匿名函数）和Closure（闭包）学习理解

  发表于 2017-10-07  |    更新于 2017-10-07    |    分类于  [技术tech][0]    |     |  本文总阅读量 11 次    字数统计  2,700  |    阅读时长  11

摘要：  
有几个疑问：

1. 什么是anonymous function（匿名函数）和Closure（闭包）？
1. 为什么要使用 Closure？
1. 怎么使用Closure？

## 什么是anonymous function（匿名函数）和Closure（闭包）

1. Lambdas也就是anonymous function，也就是匿名函数，表示没有名字的函数。
    1. 术语Lambdas来自 Lambda calculus，这是在 20 世纪 30 年代由 Alonzo Church 提出的，关于函数定义、应用程序、递归的规范系统里的一个概念。
1. **如果 lambdas 是没有名字的函数，那么 closures 仅比 lambdas 多一个上下文。就是说，closure 是个匿名函数，在其创建时，将来自创建该函数的代码范围内得变量值附加到它本身。变量通过 use 关键字绑定到 closure。**
    1. 通过 use 把外部变量加载到局部变量中。（匿名函数不支持）
1. 匿名函数（Anonymous functions），也叫闭包函数（closures），允许 临时创建一个没有指定名称的函数。最经常用作回调函数（callback）参数的值。当然，也有其它应用的情况。**在 php 里面匿名函数目前是通过 Closure 类来实现的**。
    1. 在PHP 4最早称为callback，PHP5.3引入了closure与anonymous function。为了避免由于PHP的弱类型本质，让我们误以为传入的是一般类型参数，5.4中特别为传入的callback加上callable的类型限定；所以在PHP中，callback与closure、anonymous function与callable，事实上是同一个事情，底层都是Closure物件。
    1. PHP 5.3 开始，添加了 Lambdas（匿名函数）和 CLosures（闭包）这俩个新的特性。

参考：

* [利用 PHP 5.3 的 lambdas 和 closures][1]
* [PHP: 匿名函数 - Manual][2]

## 为什么要使用 Closure

闭包的出现往往是函数式编程的语言里，在面向对象的编程语言里是可以不需要闭包的。

闭包作用如下：

1. 避免使用全局变量，实现数据隐藏和保持，也就是面向对象的特性：封装。
1. 当成员变量比较少，以及方法只有一个时，比类更简单实现。
1. 数据和函数一起封装，适应异步或并发运行。
1. 有一些函数并不需要复用，或者甚至并不想出现在整体的代码里面，所以需要使用这种一次性的匿名函数（在 php 里面也就是 Closere）。参考：[php - Why use anonymous function? - Stack Overflow][3]
1. 作为回调函数来被使用，可以更好的被回调，类似被array_filter 或者 array_map 回调。参考：[Why and how do you use anonymous functions in PHP? - Stack Overflow][4]

所以，使用闭包能够更好的整洁代码，更快捷的实现逻辑而不影响代码结构。

## 怎么使用Closure

正如前面所说，**closures 仅比 lambdas 多一个上下文（相当于作用域）**，那么使用的时候跟匿名函数也是很相似的，不过Closure的用法会多一些，可以由此来区分两者。

由于匿名函数和Closure非常类似，所以可以将他们放在一起对比，这样能够更好的加深理解。

### 简单例子理解

**匿名函数怎么用?**  
A：由于匿名函数没有名字（同理闭包也没有名字），所以不能像正式的函数那样调用，需要赋值给变量或者作为参数传递。

1. 参数的形式: 通过将匿名函数以参数的方式注入到另一个函数的方式。

```php
<?php
function shout ($message)
{
     echo $message();
}
// 可以看到$message的位置被一个匿名函数填满了
shout(function(){
     return 'Hello world.';
});
```
1. 变量的形式: 将一个匿名函数赋值给了一个变量，当然这里其实也可以说这个函数有了个“假名字”，然后我们通过在变量名后面加括号()的方式来调用即可。

```php
<?php
$greeting = function() {
     return "Hello world.";
}
// call function
echo $greeting();
```
1. 可以通过 call_user_func来调用。参考：[关于call_user_func学习理解 | 神一样的少年][5]

**闭包怎么用?**

```php
<?php
$message = '我是外部变量';
// 闭包可以支持外部变量传入到内部使用，使用 use 关键字
$example = function() use ($message){
     echo $message;
};
$example(); // 输出 我是外部变量
#---------------------------------------------------
$message = '我是外部变量';
// 不使用 use 的话，则没有这个变量
$example = function() {
     echo $message;
};
$example(); // 输出 Undefined variable: message
```
> 两者基本是一样的，有点差异在于 use 的使用，并且从**在 php 里面匿名函数目前是通过 Closure 类来实现的**这里可以了解到，基本一样也是合理的，use 不是唯一的差异性，还有一些差异在后面会说到。

### 官方例子理解

```php
<?php
class Cart
{
    const PRICE_BUTTER  = 1.00;
    const PRICE_MILK    = 3.00;
    const PRICE_EGGS    = 6.95;
    protected   $products = array();
    public function add($product, $quantity)
    {
        $this->products[$product] = $quantity;
    }
    public function getQuantity($product)
    {
        return isset($this->products[$product]) ? $this->products[$product] :
            FALSE;
    }
    public function getTotal($tax)
    {
        $total = 0.00;
          // 闭包，并且使用了 use 传入了参数，并且作为回调函数来使用
          // 因为要修改父作用域的参数total，所以用了&$total（传递变量的引用）
        $callback =
            function ($quantity, $product) use ($tax, &$total)
            {
                $pricePerItem = constant(__CLASS__ . "::PRICE_" .
                    strtoupper($product));
                $total += ($pricePerItem * $quantity) * ($tax + 1.0);
            };
          // array_walk支持回调函数的方式使用
        array_walk($this->products, $callback);
        return round($total, 2);
    }
}
$my_cart = new Cart;
// 往购物车里添加条目
$my_cart->add('butter', 1);
$my_cart->add('milk', 3);
$my_cart->add('eggs', 6);
// 打出出总价格，其中有 5% 的销售税.
print $my_cart->getTotal(0.05) . "\n";
// 最后结果是 54.29
```
* 在 Closure 定义中，通过 use 语句传递了 $XXX 变量, 所以 Closure 内部能够访问到 $XXX 。但是如果在 Closure 作用域内修改变量 $XXX 的值，并不会修改父作用域的 $XXX 值。如果希望修改父作用域的 $user，可以传递变量的引用 &$XXX 给 Closure 。

> 其实就是更好的理解：Closures 仅比 Lambdas（匿名函数） 多一个上下文。就是说，Closure 也是个匿名函数，在其创建时，会将来自创建该函数的代码范围内得变量值附加到它本身。

## php 闭包的方法

闭包多了一些方法，例如：

* `Closure::__construct` — 用于禁止实例化的构造函数。（好像只是用来看看）
* `Closure::bind`— 复制一个闭包，绑定指定的$this对象和类作用域。
* `Closure::bindTo`— 复制当前闭包对象，绑定指定的$this对象和类作用域。

**Closure::bind**

    public static Closure Closure::bind ( Closure $closure , object $newthis [, mixed $newscope = 'static' ] )

参数：

* `closure`：需要绑定的匿名函数。（也是闭包函数）
* `newthis`：需要绑定到匿名函数的对象，或者 NULL 创建未绑定的闭包。
* `newscope`：想要绑定给闭包的类作用域，或者 'static' 表示不改变。如果传入一个对象，则使用这个对象的类型名。 类作用域用来决定在闭包中 $this 对象的 私有、保护方法 的可见性。  
参考：[PHP: Closure::bind - Manual][6]

**Closure::bindTo**

    public Closure Closure::bindTo ( object $newthis [, mixed $newscope = 'static' ] )

参数：

* `newthis`：绑定给匿名函数的一个对象，或者 NULL 来取消绑定。
* `newscope`：**关联到匿名函数的类作用域，或者 ’static'保持当前状态**。如果传入一个对象，则使用这个对象的类型名。 类作用域用来决定在闭包中 $this 对象的 私有、保护方法 的可见性。

> 可以看到bindTo的参数比 bind 要少，并且虽然参数的名字差不多，但是意义不一样，  
> 闭包都是继承 Closure，那么也就说他也是一个类，那么 $this 也是指向实例自己，在闭包中可以使用 bindTo 修改这个指向，让 $this 指向其他的对象，这样 $this 可以访问其他对象的属性和方法。

参考：[http://php.net/manual/zh/closure.bindto.php][7]

### 例子1：

```php
<?php
class Animal {
    private static $cat1 = "cat";
    private $dog1 = "dog";
    public $pig1 = "pig";
}
/*
 * 获取Animal类静态私有成员属性
 */
$cat = static function() {
    return Animal::$cat1;
};
/*
 * 获取Animal实例私有成员属性
 */
$dog = function() {
    return $this->dog1;
};
/*
 * 获取Animal实例公有成员属性
 */
$pig = function() {
    return $this->pig1;
};
// 给闭包绑定了Animal实例的作用域，但未给闭包绑定$this对象
$bindCat = Closure::bind($cat, null, new Animal());  // 第二个参数为 null，第三个参数为Animal实例
// 给闭包绑定了Animal类的作用域，同时将Animal实例对象作为$this对象绑定给闭包
$bindDog = Closure::bind($dog, new Animal(), 'Animal'); // 第二个参数为Animal实例，第三个参数为Animal实例的字符串名字
// 将Animal实例对象作为$this对象绑定给闭包,保留闭包原有作用域
$bindPig = Closure::bind($pig, new Animal()); //第三个参数默认不填是不改变
// 根据绑定规则，允许闭包通过作用域限定操作符获取Animal类静态私有成员属性
echo $bindCat(),"\n"; // 输出 cat
// 根据绑定规则，允许闭包通过绑定的$this对象(Animal实例对象)获取Animal实例私有成员属性
echo $bindDog(),"\n"; // 输出 dog
// 根据绑定规则，允许闭包通过绑定的$this对象获取Animal实例公有成员属性
echo $bindPig(),"\n"; // 输出 pig
```

> 需要注意的是，绑定了对象和绑定了对象作用域在这里是要区分开的，正如官方所说，类作用域用来决定在闭包中 $this 对象的 私有、保护方法 的可见性。

* 关于$bindCat:
    * 因为绑定了闭包的作用域是new Animal()，所以能够访问，即使是私有属性。（第三个参数作用）
    * 要注意$cat闭包的写法，用的是Animal::$cat1来访问这个静态属性的，因为传入的是Animal实例，所以要用Animal来访问
* 关于$bindDog：
    * 因为将new Animal()绑定为$this作用域，所以$dog可以通过$this 来访问
    * 但是因为$dog1是私有属性，如果不绑定作用域的到Animal对象的话，那么就无法访问私有属性的
    * 这里第二个参数绑定为$this其实跟填 null，差别不大，不过填了的话，可以使用$this来调用
* 关于$bindPig：
    * 第三个参数没有填，表示不改变作用域，只绑定了一个new Animal()实例对象，所以能够访问这个实例对象的一些 public 属性

### 例子2：

```php
<?php
class A {
    function __construct($val) {
        $this->val = $val;
    }
    function getClosure() {
        //returns closure bound to this object and scope
        return function() { return $this->val; };
    }
}
$ob1 = new A(1);
$ob2 = new A(2);
$cl = $ob1->getClosure();
echo $cl(), "\n"; // 输出1
// 将闭包的 this 指向改变到另外一个实例
$cl = $cl->bindTo($ob2);
echo $cl(), "\n"; // 输出2
```
### 例子3：

```php
<?php
class App
{
    private $name = 'New App';
    public $version = '1.1';
    public $data = [];
    public function bind($name, $callback)
    {
        // 将 __CLASS__ 绑定到 closure 的 $this 上
        $this->data[$name] = $callback->bindTo($this, __CLASS__);
    }
    public function run($name)
    {
        $this->data[$name]();
    }
}
$app = new App();
$app->bind('dev', function() {
    // 这里的 $this 指向了 App
    echo $this->name;
});
$app->run('dev'); // 输出 New App
```

参考：

* [PHP 中的 Lambdas 和 Closures | 百作坊][8]
* [PHP: 匿名函数 - Manual][2]
* [PHP 闭包（Closure） | Laravel China 社区 - 高品质的 Laravel 开发者社区 - Powered by PHPHub][9]

* **本文作者：** 茅有知
* **本文链接：**[https://www.godblessyuan.com/backend/anonymous-function-closure-lear.html][10]

[0]: /categories/技术tech/
[1]: https://www.ibm.com/developerworks/cn/opensource/os-php-lambda/index.html
[2]: http://php.net/manual/zh/functions.anonymous.php
[3]: https://stackoverflow.com/questions/4147400/why-use-anonymous-function
[4]: https://stackoverflow.com/questions/2412299/why-and-how-do-you-use-anonymous-functions-in-php
[5]: https://www.godblessyuan.com/backend/call_user_func-leran.html
[6]: http://php.net/manual/zh/closure.bind.php
[7]: http://php.net/manual/zh/closure.bindto.php
[8]: http://blog.100dos.com/2016/09/30/Lambdas-and-Closures-in-PHP/
[9]: https://laravel-china.org/articles/4625/php-closure-closure
[10]: https://www.godblessyuan.com/backend/anonymous-function-closure-lear.html
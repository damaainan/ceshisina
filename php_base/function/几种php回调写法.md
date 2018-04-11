## workerman / 学习workerman之前需要知道的几种php回调写法

来源：[https://segmentfault.com/a/1190000014197451](https://segmentfault.com/a/1190000014197451)

在workerman中会经常使用，我们先写一个回调函数，当某个行为被触发后使用该函数处理相关逻辑。
在PHP中最常用的几种回调写法如下
## 匿名函数做为回调

匿名函数（Anonymous functions），也叫闭包函数（closures），允许临时创建一个没有指定名称的函数。最经常用作回调函数（callback）参数的值。当然，也有其它应用的情况。
 **`匿名函数的回调经常将其赋给一个变量（或一个对象的属性）`** 

```php
$add = function($number1,$number2){
    return $number1+$number2;
};

echo $add(1,10);
```

最终结果会输出11。

这中间有一个use的用法，现在很多框架都在使用，包括我自己发布的 [yii2-wx][1] 在处理微信付款结果通知的时候也有用到。

简单点说就是当我们设置了匿名函数的时候，可以从父作用域继承变量，比如如下代码

```php
$number = 10;
$add = function($number1,$number2) use ($number){
    return $number1+$number2 + $number;
};

echo $add(1,10);
```

则结果是21，没错，匿名函数体可以使用继承过来的变量 **`$number`** 。

一点要注意的是，这种继承是在函数被定义的时候就确定了，比如如下代码

```php
$number = 10;
$add = function($number1,$number2) use ($number){
    return $number1+$number2 + $number;
};

$number = 11;
echo $add(1,10);
```

结果还是21，后面重新的赋值并没有起到作用。
 **`那我们如何解决这个问题那？`** 

@nai8@

只需要将继承的变量设置为引用即可，如下

```php
$number = 10;
$add = function($number1,$number2) use (&$number){
    return $number1+$number2 + $number;
};

$number = 11;
echo $add(1,10);
```

搞定了，22出现了。1 + 10 + 11;

在workerman中一般匿名函数作为回调用法如下

```php
use Workerman\Worker;
require_once __DIR__ . '/Workerman/Autoloader.php';
$http_worker = new Worker("http://0.0.0.0:2345");

// 匿名函数回调
$http_worker->onMessage = function($connection, $data)
{
    // 向浏览器发送hello world
    $connection->send('hello world');
};

Worker::runAll();
```
## 普通函数作为回调

这种用法并没有什么可以多说的，不像匿名函数那么多细节，直接看代码。

```php
function add($number1,$number2){
    return $number1+$number2;
};

$add = "add";
echo $add(1,10);
```

在这里没有use用法，并且函数定义和赋值给变量顺序谁上谁下都可以，在语法上这只是对$add的一次变量赋值，之所以能作为回调是我们使用了 $add() 导致的，在workerman中一般用法如下

```php
use Workerman\Worker;
require_once __DIR__ . '/Workerman/Autoloader.php';
$http_worker = new Worker("http://0.0.0.0:2345");

// 匿名函数回调
$http_worker->onMessage = 'on_message';

// 普通函数
function on_mesage($connection, $data)
{
    // 向浏览器发送hello world
    $connection->send('hello world');
}

Worker::runAll();
```
## 类方法作为回调

学习了前面的知识，类的方法作为回调就好学了，一句话： **`将一个类的公共方法作为回调函数`** ;

直接上在workerman中的用法

```php
use Workerman\Worker;
require_once __DIR__ . '/Workerman/Autoloader.php';

// 载入MyClass
require_once __DIR__.'/MyClass.php';

$worker = new Worker("websocket://0.0.0.0:2346");

// 创建一个对象
$myObject = new MyClass();

$worker->onMessage   = [$myObject, 'onMsg'];

Worker::runAll();
```

我们将MyClass的onMsg方法作为回调赋值给$worker->onMessage。
## 类静态方法做为回调

这个最简单了，看代码。

```php
use Workerman\Worker;
require_once __DIR__ . '/Workerman/Autoloader.php';

// 载入MyClass
require_once __DIR__.'/MyClass.php';

$worker = new Worker("websocket://0.0.0.0:2346");

$worker->onMessage   = [$myObject, 'onMsg'];

Worker::runAll();
```

我们将MyClass的静态方法onMsg方法作为回调赋值给$worker->onMessage，因为是静态方法，在onMsg内不能用$this哈。

我的知识分享 [https://nai8.me][2]

![][0]

[1]: https://github.com/abei2017/yii2-wx
[2]: https://nai8.me
[0]: ../img/1460000014200531.png
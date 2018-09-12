# php 中的closure用法

 时间 2017-06-12 15:08:00  

原文[http://www.cnblogs.com/phpper/p/6993093.html][1]


`Closure`，匿名函数，是php5.3的时候引入的,又称为`Anonymous functions`。字面意思也就是没有定义名字的函数。比如以下代码(文件名是do.php)

```php
<?php
function A() {
    return 100;
};

function B(Closure $callback)
{
    return $callback();
}

$a = B(A());
print_r($a);
//输出：Fatal error: Uncaught TypeError: Argument 1 passed to B() must be an instance of Closure, integer given, called in D:\web\test\do.php on line 11 and defined in D:\web\test\do.php:6 Stack trace: #0 D:\web\test\do.php(11): B(100) #1 {main} thrown in D:\web\test\do.php on line 6
```

这里的A()永远没有办法用来作为B的参数，因为A它并不是“匿名”函数。

所以应该改成这样：

```php
<?php
$f = function () {
    return 100;
};

function B(Closure $callback)
{
    return $callback();
}

$a = B($f);
print_r($a);//输出100
```

```php
<?php
$func = function( $param ) {
    echo $param;
};

$func( 'hello word' );

//输出：hello word
```

## 实现闭包

将匿名函数在普通函数中当做参数传入，也可以被返回。这就实现了一个简单的闭包。

下边我举三个例子：

```php
<?php
//例一
//在函数里定义一个匿名函数，并且调用它
function printStr() {
    $func = function( $str ) {
        echo $str;
    };
    $func( ' hello my girlfriend ! ' );
}
printStr();//输出 hello my girlfriend !

//例二
//在函数中把匿名函数返回，并且调用它
function getPrintStrFunc() {
    $func = function( $str ) {
        echo $str;
    };
    return $func;
}
$printStrFunc = getPrintStrFunc();
$printStrFunc( ' do you  love me ? ' );//输出 do you  love me ?

//例三
//把匿名函数当做参数传递，并且调用它
function callFunc( $func ) {
    $func( ' no!i hate you ' );
}

$printStrFunc = function( $str ) {
    echo $str.'<br>';
};
callFunc( $printStrFunc );

//也可以直接将匿名函数进行传递。如果你了解js，这种写法可能会很熟悉
callFunc( function( $str ) {
    echo $str; //输出no!i hate you
} );
```

## 连接闭包和外界变量的关键字：USE

闭包可以保存所在代码块上下文的一些变量和值。PHP在默认情况下，匿名函数不能调用所在代码块的上下文变量，而需要通过使用`use关键字`。

换一个例子看看(好吧，我缺钱，我很俗)：

```php
<?php
function getMoney() {
    $rmb = 1;
    $dollar = 8;
    $func = function() use ( $rmb ) {
        echo $rmb;
        echo $dollar;
    };
    $func();
}
getMoney();
//输出：1
```

可以看到，dollar没有在`use关键字`中声明，在这个匿名函数里也就不能获取到它，所以开发中要注意这个问题。

有人可能会想到，是否可以在匿名函数中改变上下文的变量，但我发现好像是不可以的：

```php
<?php
function getMoney() {
    $rmb = 1;
    $func = function() use ( $rmb ) {
        echo $rmb.'<br>';
        //把$rmb的值加1
        $rmb++;
    };
    $func();
    echo $rmb;
}

getMoney();

//输出：
//1
//1
```

额，原来use所引用的也只不过是变量的一个副本clone而已。但是我想要完全引用变量，而不是复制呢?要达到这种效果，其实在变量前加一个 `&` 符号就可以了：

```php
<?php
function getMoney() {
    $rmb = 1;
    $func = function() use ( &$rmb ) {
        echo $rmb.'<br>';
        //把$rmb的值加1
        $rmb++;
    };
    $func();
    echo $rmb;
}

getMoney();
//输出：
//1
//2
```

好，这样匿名函数就可以引用上下文的变量了。如果将匿名函数返回给外界，匿名函数会保存use所引用的变量，而外界则不能得到这些变量，这样形成‘闭包’这个概念可能会更清晰一些。

根据描述我们再改变一下上面的例子：

```php
<?php
function getMoneyFunc() {
    $rmb = 1;
    $func = function() use ( &$rmb ) {
        echo $rmb.'<br>';
        //把$rmb的值加1
        $rmb++;
    };
    return $func;
}

$getMoney = getMoneyFunc();
$getMoney();
$getMoney();
$getMoney();

//输出：
//1
//2
//3
```

好吧，扯了这么多，那么如果我们要调用一个类里面的匿名函数呢？直接上demo

```php
<?php
class A {
    public static function testA() {
        return function($i) { //返回匿名函数
            return $i+100;
        };
    }
}

function B(Closure $callback)
{
    return $callback(200);
}

$a = B(A::testA());
print_r($a);//输出 300
```

其中的`A::testA()`返回的就是一个`无名funciton`。

## 绑定的概念

上面的例子的`Closure`只是全局的的匿名函数，好了，那我们现在想指定一个类有一个匿名函数。也可以理解说，这个匿名函数的访问范围不再是全局的了，而是一个类的访问范围。

那么我们就需要将“一个匿名函数绑定到一个类中”。

```php
<?php
class A {
    public $base = 100;
}

class B {
    private $base = 1000;
}

$f = function () {
    return $this->base + 3;
};

$a = Closure::bind($f, new A);
print_r($a());//输出 103
echo PHP_EOL;

$b = Closure::bind($f, new B , 'B');
print_r($b());//输出1003
```

上面的例子中，f这个匿名函数中莫名奇妙的有个this,这个this关键词就是说明这个匿名函数是需要绑定在类中的。

绑定之后，就好像A中有这么个函数一样，但是这个函数是`public`还是`private`，`bind`的最后一个参数就说明了这个函数的可调用范围。

上面大家看到了`bindTo`,我们来看官网的介绍

```
    (PHP 5 >= 5.4.0, PHP 7)
    
    Closure::bind — 复制一个闭包，绑定指定的$this对象和类作用域。
    
    说明
    
    public static Closure Closure::bind ( Closure $closure , object $newthis [, mixed $newscope = 'static' ] )
    这个方法是 Closure::bindTo() 的静态版本。查看它的文档获取更多信息。
    
    参数
    
    closure
    需要绑定的匿名函数。
    
    newthis
    需要绑定到匿名函数的对象，或者 NULL 创建未绑定的闭包。
    
    newscope
    想要绑定给闭包的类作用域，或者 'static' 表示不改变。如果传入一个对象，则使用这个对象的类型名。 类作用域用来决定在闭包中 $this 对象的 私有、保护方法 的可见性。（备注：可以传入类名或类的实例，默认值是 'static'， 表示不改变。） 
    返回值：
    返回一个新的 Closure 对象 或者在失败时返回 FALSE
```


```php
<?php
class A {
    private static $sfoo = 1;
    private $ifoo = 2;
}
$cl1 = static function() {
    return A::$sfoo;
};
$cl2 = function() {
    return $this->ifoo;
};

$bcl1 = Closure::bind($cl1, null, 'A');
$bcl2 = Closure::bind($cl2, new A(), 'A');
echo $bcl1(), "\n";//输出 1
echo $bcl2(), "\n";//输出 2
```

我们再来看个例子加深下理解：

```php
<?php

class A {
    public $base = 100;
}

class B {
    private $base = 1000;
}
class C {
    private static $base = 10000;
}

$f = function () {
    return $this->base + 3;
};

$sf = static function() {
    return self::$base + 3;
};

$a = Closure::bind($f, new A);
print_r($a());//这里输出103,绑定到A类
echo PHP_EOL;

$b = Closure::bind($f, new B , 'B');
print_r($b());//这里输出1003，绑定到B类
echo PHP_EOL;

$c = $sf->bindTo(null, 'C'); //注意这里：使用变量#sf绑定到C类，默认第一个参数为null
print_r($c());//这里输出10003
```

我们再看一个demo:

```php
<?php
/**
 * 复制一个闭包，绑定指定的$this对象和类作用域。
 *
 * @author fantasy
 */
class Animal {
    private static $cat = "加菲猫";
    private $dog = "汪汪队";
    public $pig = "猪猪侠";
}

/*
 * 获取Animal类静态私有成员属性
 */
$cat = static function() {
    return Animal::$cat;
};

/*
 * 获取Animal实例私有成员属性
 */
$dog = function() {
    return $this->dog;
};

/*
 * 获取Animal实例公有成员属性
 */
$pig = function() {
    return $this->pig;
};

$bindCat = Closure::bind($cat, null, new Animal());// 给闭包绑定了Animal实例的作用域，但未给闭包绑定$this对象
$bindDog = Closure::bind($dog, new Animal(), 'Animal');// 给闭包绑定了Animal类的作用域，同时将Animal实例对象作为$this对象绑定给闭包
$bindPig = Closure::bind($pig, new Animal());// 将Animal实例对象作为$this对象绑定给闭包,保留闭包原有作用域
echo $bindCat(),'<br>';// 输出：加菲猫，根据绑定规则，允许闭包通过作用域限定操作符获取Animal类静态私有成员属性
echo $bindDog(),'<br>';// 输出：汪汪队, 根据绑定规则，允许闭包通过绑定的$this对象(Animal实例对象)获取Animal实例私有成员属性
echo $bindPig(),'<br>';// 输出：猪猪侠, 根据绑定规则，允许闭包通过绑定的$this对象获取Animal实例公有成员属性
```

通过上面的几个例子，其实匿名绑定的理解就不难了....我们在看一个扩展的demo(引入`trait`特性)

```php
<?php
/**
 * 给类动态添加新方法
 *
 * @author fantasy
 */
trait DynamicTrait {

    /**
     * 自动调用类中存在的方法
     */
    public function __call($name, $args) {
        if(is_callable($this->$name)){
            return call_user_func($this->$name, $args);
        }else{
            throw new \RuntimeException("Method {$name} does not exist");
        }
    }
    /**
     * 添加方法
     */
    public function __set($name, $value) {
        $this->$name = is_callable($value)?
            $value->bindTo($this, $this):
            $value;
    }
}

/**
 * 只带属性不带方法动物类
 *
 * @author fantasy
 */
class Animal {
    use DynamicTrait;
    private $dog = '汪汪队';
}

$animal = new Animal;

// 往动物类实例中添加一个方法获取实例的私有属性$dog
$animal->getdog = function() {
    return $this->dog;
};

echo $animal->getdog();//输出 汪汪队
```

比如现在我们用现在购物环境

```php
<?php
/**
 * 一个基本的购物车，包括一些已经添加的商品和每种商品的数量
 *
 * @author fantasy
 */
class Cart {
    // 定义商品价格
    const PRICE_BUTTER  = 10.00;
    const PRICE_MILK    = 30.33;
    const PRICE_EGGS    = 80.88; 

    protected   $products = array();

    /**
     * 添加商品和数量
     *
     * @access public
     * @param string 商品名称
     * @param string 商品数量
     */
    public function add($item, $quantity) {
        $this->products[$item] = $quantity;
    }

    /**
     * 获取单项商品数量
     *
     * @access public
     * @param string 商品名称
     */
    public function getQuantity($item) {
        return isset($this->products[$item]) ? $this->products[$item] : FALSE;
    }

    /**
     * 获取总价
     *
     * @access public
     * @param string 税率
     */
    public function getTotal($tax) {
        $total = 0.00;

        $callback = function ($quantity, $item) use ($tax, &$total) {
            $pricePerItem = constant(__CLASS__ . "::PRICE_" . strtoupper($item)); //调用以上对应的常量
            $total += ($pricePerItem * $quantity) * ($tax + 1.0);
        };
        array_walk($this->products, $callback);
        return round($total, 2);
    }
}

$my_cart = new Cart;

// 往购物车里添加商品及对应数量
$my_cart->add('butter', 10);
$my_cart->add('milk', 3);
$my_cart->add('eggs', 12);

// 打出出总价格，其中有 3% 的销售税.
echo $my_cart->getTotal(0.03);//输出 1196.4
```

补充说明：闭包可以使用USE关键连接外部变量。   
总结：PHP闭包的特性其实用CLASS就可以实现类似甚至强大得多的功能，更不能和js的闭包相提并论了吧，只能期待PHP以后对闭包支持的改进。不过匿名函数还是挺有用的，比如在使用`preg_replace_callback`等之类的函数可以不用在外部声明回调函数了。合理使用闭包能使代码更加简洁和精炼。


[1]: http://www.cnblogs.com/phpper/p/6993093.html

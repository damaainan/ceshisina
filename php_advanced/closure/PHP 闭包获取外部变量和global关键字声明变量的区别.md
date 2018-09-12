# PHP 闭包获取外部变量和global关键字声明变量的区别

 时间 2017-12-04 07:22:38 

原文[http://www.linuxidc.com/Linux/2017-12/149098.htm][1]


最近在学习workerman的时候比较频繁的接触到回调函数，使用中经常会因为worker的使用方式不同，会用这两种不同的方式去调用外部的worker变量，这里就整理一下PHP闭包获取外部变量和global关键字声明变量的区别。

#### 闭包

闭包是一个常见的概念，我们通常可以将其与回调函数配合使用，可以使代码更加简洁易读。

闭包可以 **通过拷贝的方式** 让函数使用父作用域中的变量。如： 
```php
$global = 'hello';

$bbb = function()use($global){
    echo $global."\n";
};
$bbb();
//输出 'hello'
```
#### global关键字声明变量

通过global声明变量同样可以使函数体调用到函数外部的变量，不过global与use不同，globle关键字会使创建 **一个与外部变量同名的引用** ，并且在函数内对变量作出修改同样会作用域外部变量。 

```php
$global = 'hello';
$fun = function(){
    global $global;
    $global =' world';
    echo $global."\n";
};
$fun();
// 输出 'world'
```
这里只是创建一个同名引用而已，并不会改变原本外部变量$global的作用域，也就是说在另外一个函数中调用该依旧需要声明或者使用闭包

```php
$global = 'hello';
$fun = function(){
    global $global;
    $global =' world';
    echo 'a:'.$global."\n";
};

$ccc = function(){
    echo 'b:'.$global;
};
$fun()
$ccc()
```

    /*
    输出
    a: world
    
    Notice: Undefined variable: global in xxxxxxx on line xx
    */

再稍微改一下代码，这样更容易对比闭包和global关键字声明变量这两种访问外部变量方式的区别。

```php
<?php
$global = 'hello';
$fun = function(){
    global $global;
    $global ='world';
    echo 'a:'.$global."\n";
};

$bbb = function()use($global){
    $global = 'china';
    echo 'c:'.$global."\n";
};

$fun();

echo 'b:'.$global."\n";
$bbb();
echo 'd:'.$global;
```
这里b和d两个输出可以看出来，global改变了外部变量的值，而闭包方式并没有。

输出：

    a: world
    b: world
    c:china
    d: world

最后再贴一个官方文档中比较经典的使用匿名函数，闭包与回调函数配合的例子：

```php
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

        $callback =
            function ($quantity, $product) use ($tax, &$total)
            {
                $pricePerItem = constant(__CLASS__ . "::PRICE_" .
                    strtoupper($product));
                $total += ($pricePerItem * $quantity) * ($tax + 1.0);
            };
        array_walk($this->products, $callback);
        return round($total, 2);
    }
}

$my_cart = new Cart;

$my_cart->add('butter', 1);
$my_cart->add('milk', 3);
$my_cart->add('eggs', 6);


print $my_cart->getTotal(0.05) . "\n";
```



[1]: http://www.linuxidc.com/Linux/2017-12/149098.htm

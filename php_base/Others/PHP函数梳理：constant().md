# PHP函数梳理：constant()

 时间 2017-10-09 15:09:28  

原文[https://wxb.github.io/2017/10/09/PHP函数梳理：constant.html][1]


今天在学习Lambdas和Closure（即：匿名函数和闭包）时，看到这个获取常数的函数，查了查PHP手册关于这个函数的定义：

constant() : 获取一个常量值； 

    mixed constant ( string $name )

通过 name 返回常量的值。

当你不知道常量名，却需要获取常量的值时，constant() 就很有用了。也就是常量名储存在一个变量里，或者由函数返回常量名。

突然就有点懵逼~~~  直接输出变量就行了，为什么还有用函数？这个函数存在的意义是什么？

## 有点懵逼 

当你不知道常量名，却需要获取常量的值时，constant() 就很有用了。也就是常量名储存在一个变量里，或者由函数返回常量名。

懵逼的是：你都不知道常量名，还怎么获取常量值？这不符合我的思维逻辑啊？

仔细读了读中文手册的说明和下面的范例，还是没有弄懂这个函数存在的意义，又对着英文手册学习了一下，才发现这个函数的意义在这句话：

当你不知道常量名，却需要获取常量的值时，constant() 就很有用了。 **也就是常量名储存在一个变量里，或者由函数返回常量名** 。 

## 理解重述 

重点是后面的那句话，中文手册这里的叙述有些模糊，我尝试以我的理解重述一下：

当你要获取的这个常量不是确定的，而是取决于程序运行的情景时，constant()就很有用，也就是常量名不是你写程序时能确定，而是在程序执行过程中得到的一个已知的常量名，constant()函数就可以获取这个常量值，通常这个常量名储存在一个变量里，或者由函数返回常量名，只要是一个存在的常量名字符串就行## 下面给出引出这个问题的代码 

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

        $callback =
            function ($quantity, $product)use($tax, &$total)
            {
                /**
                 * 看这里看这里！！！  
                 * 这里就是这个 constant 的用法: 你要获取的常量值，是取决于程序的运行情景
                 */
                $pricePerItem = constant(__CLASS__ . "::PRICE_" . strtoupper($product));

                $total += ($pricePerItem * $quantity) * ($tax + 1.0);
            };
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

[1]: https://wxb.github.io/2017/10/09/PHP函数梳理：constant.html
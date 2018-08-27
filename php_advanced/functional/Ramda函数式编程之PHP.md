## Ramda函数式编程之PHP

来源：[http://blog.p2hp.com/archives/5114](http://blog.p2hp.com/archives/5114)

时间 2018-03-19 12:52:21



## 0x00 何为函数式编程

网上已经有好多详细的接受了，我认为比较重要的有：


* 函数是“第一等公民”，即函数和其它数据类型一样处于平等地位
* 使用“表达式”（指一个单纯的运算过程，总是有返回值），而不是“语句”（执行操作，没有返回值）
* 没有”副作用“，即不修改外部值
  
## 0x01 开始函数式编程

在此之前，请先了解PHP中的匿名函数和闭包，可以参考我写得[博客][0]

函数式编程有两个最基本的运算：合成和柯里化。

#### 函数合成

函数合成，即把多个函数的运算合成一个函数，如

A=f(x)

B=g(x)

C=f(g(x))

那么C即是A和B的合成。

用代码表示为：

```php
$compose = function ($f,$g){
    return function ($x) use($f,$g){ //这里返回一个函数的函数，即高阶函数
        return $f($g($x));
    };
};

function addTen($a){
    return $a+10;
}

function subOne($a){
    return $a-1;
}

$z = $compose('addTen','subOne');//如果使用 $addOne = function(){}的形式，可以直接传变量
echo $z(5);// 14
```


要求合成的函数也是个 **`纯函数`** ，如果不是纯函数，那么结果不一致，怎么合成呢？

compose返回一个高阶函数，当给合成的这个函数传值时，变回在高阶函数内部调用之前保存的函数。

  
#### 柯里化


可以看到如果这里传入的函数参数有多个，那么上面的合成函数就失效了。

这里就要请出另外一个函数式编程使用到的另外一个大神了，柯里化。“所谓”柯里化”，就是把一个多参数的函数，转化为单参数函数”。

```php
//柯里化之前
function add($a,$b){
    return $a+$b;
}
add(1, 2); // 3

// 柯里化之后
function addX($b) {
    return function ($a) use($b) {
        return $a + $b;
    };
}

$addTwo = addX(2);
$addTwo(1);//3
```
 PHP7以下直接调用addX(2)(1)，会报错 ，所以上面使用了中间变量$addTwo。

```
Parse error: syntax error, unexpected ‘(‘

```

在PHP7以上完善了[一致变量语法][1]，而且PHP7速度更快，强烈建议使用PHP7。

通用柯里化，柯里化很美好，然而我们不可能为每一个函数写一遍，那么有没有包装函数，可以把普通的函数改些为柯里化后的函数呢？

代码如下：（摘自：[pramda][2])

```php
function curry2($callable)
{
    return function () use ($callable) {
        $args = func_get_args();
        switch (func_num_args()) {
            case 0:
                throw new \Exception("Invalid number of arguments");
                break;
            case 1:
                return function ($b) use ($args, $callable) {
                    return call_user_func_array($callable, [$args[0], $b]);
                };
                break;
            case 2:
                return call_user_func_array($callable, $args);
                break;
            default:
                // Why? To support passing curried functions as parameters to functions that pass more that 2 parameters, like reduce
                return call_user_func_array($callable, [$args[0], $args[1]]);
                break;
        }
    };
}
function add($a,$b){
    return $a+$b;
}

$addCurry = curry2('add');
$addTwo = $addCurry(2);
$addTwo(1);//3
```

说明，curry2返回一个闭包（如上面的$addCurry)，当这个闭包被调用时会通过func_get_args动态获取参数，以及func_num_args动态获取参数个数。curry2函如其名，可以给把参数个数为两个函数柯里化。于是在闭包里，我们看到，在对参数个数进行判断，当参数个数为1时，则生成新的闭包（如上面的$addTwo)，新的闭包里保存原函数以及整个参数，当新闭包被调用时，则调用call_user_func_array传入原函数、保存的参数、新参数，获取了想要的结果。

扩展，函数式编程还有另外一个重要的概念， 函子 （即带有map方法的类），更多内容可以看阮老师的这两篇文章，我就不详叙了。

* [函数式编程初探][3]
* [函数式编程入门教程][4]
    
平常我们自己使用的函数，如果符合函数式编程的思想，也可以柯里化。当然对于更多参数的函数得运用更高阶的curryN来柯里化。

这些已经有人造好轮子了，下面开始进入正题了。

## 0x02 Ramda

这个Ramda实际上是函数式编程中的Pointfree风格。

在Ramda里，数据一律放在最后一个参数，理念是”function first，data last”。

比如

```php
//例1
function map(){
    $args = func_get_args();
    $n = func_num_args();
    $callable = $args[$n-1];
    unset($args[$n-1]);
    $res = [];
    foreach ($args as $v){
        if(is_array($v)){
            foreach ($v as $i){
                $res[] = call_user_func($callable,$i);
            }
        }else{
            $res[] = call_user_func($callable,$v);
        }
    }
    return $res;
}
map(1,2,'square');//1,4
map([1,2],'square');// 1,4

//例2
function square($v)
{
    return($v*$v);
}
array_map("square",[1,2]);  //1 ,4
```

上面的代码，例1就不是Ramda风格，而例2则是Ramda风格。

既然有人造好轮子了，那么我们直接用就好啦，下面请出主角，[pramda][2]，Ramda风格的PHP函数式编程库。

安装

```
composer require kapolos/pramda

```

如果出现

```
[InvalidArgumentException]  Could not find package kapolos/pramda.

```

可以在composer.json里加入 "kapolos/pramda":"dev-master"

示例：

```php
$before = [1,2,3,4,5];
$after = P::map(function($num) {
    return $num * 2;
}, $before);
P::toArray($after); //=> [2,4,6,8,10]

$addOne = P::add(1);
$divTen = P::divide(10); //10是被除数
$fn1 = P::compose($addOne,$divTen); //compose从右往左
$fn2 = P::pipe($addOne,$divTen);//pipe从左往右

echo $fn1(1); //11
echo "\n";
echo $fn2(1); //5
```


不足之处，pramda不支持占位符，另外curry函数最多只支持3个参数。

另外有也有两个函数式编程库，[functional-php][6]和[dash][7]可惜不是Ramda风格的。

正如阮老师所提到的学习函数式编程，实际上就是学习函子的各种运算。

如果想了解更多，可以继续阅读阮老师的这两篇文章。


* [Ramda 函数库参考教程][8]
* [Pointfree 编程风格指南][9]
    

[0]: http://www.cnblogs.com/xdao/p/php_anonymous_closure.html
[1]: https://wiki.php.net/rfc/uniform_variable_syntax
[2]: https://github.com/kapolos/pramda
[3]: http://www.ruanyifeng.com/blog/2012/04/functional_programming.html
[4]: http://www.ruanyifeng.com/blog/2017/02/fp-tutorial.html
[5]: https://github.com/kapolos/pramda
[6]: https://github.com/lstrojny/functional-php
[7]: https://github.com/nextbigsoundinc/dash
[8]: http://www.ruanyifeng.com/blog/2017/03/ramda.html
[9]: http://www.ruanyifeng.com/blog/2017/03/pointfree.html
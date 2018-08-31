PHP7 于 2015 年 11 月正式发布，本次更新可谓是 PHP 的重要里程碑 , 它将带来显著的性能改进和新特性，并对之前版本的一些特性进行改进。本文小编将和大家一起来了解探讨 PHP7 中的新特性。

1． **标量类型声明**

 我们知道 PHP 是一种弱类型的编程语言 , 因此没有提供任何方法来指定输入参数和返回值的类型， PHP7 突破了这种现状，增加了对标量类型（ int,float,string,bool ）的声明支持，增加 declare(strict_types=1) 指令声明是否严格类型校验，我们来看一段代码：

    (strict_types=) {
         $x + $y;
    }  add(, );

 有效的类型有： class/interface name, self, array, callable, bool, float, int and string. 

2. `??` **—— NULL****合并运算符**

PHP7 中添加了 NULL 合并运算符，不要小看这个“ `??` ”，有了它我们就能很方便的获取一个参数，并能在其为空的情况下提供一个默认值。如何 `??` 运算符左侧值存在并且不为 NULL ，则返回左侧，否则将返回其右侧值。我们还是通过下面一段代码来体验一下 `??` 运算符的强大吧。

```php
    <?php 
    // 获取user参数的值(如果为空，则用'nobody') 
    // PHP5中我们这样来实现: 
    $username = isset($_GET['user']) ? $_GET['user'] : 'nobody'; 
    // PHP7中，使用??运算符更便捷: 
    $username = $_GET['user'] ?? 'nobody'; 
    ?>
```
3. **匿名类**

匿名类顾名思义没有类名称，其声明和实例化是同时的， PHP7 支持通过 new class 来实例化一个匿名类，可以用来替代一些 " 用后即焚 " 的完整类定义。

    echo ( {
         {
           ;
        }
    })->myMethod();

4. **更多****Error****错误可以进行异常处理**

PHP7 中更多的 Error 变为可捕获的 Exception 返回给开发者，如果不进行捕获则为 Error ，如果捕获就变为一个可在程序内处理的 Exception 。默认情况下， Error 会直接导致程序中断，而 PHP7 则通过 try / catch 程序块捕获并且处理，让程序继续执行下去，为程序员提供更灵活的选择。

代码示例：

    nonExistFunction($arg);

 这时上面的代码会提示错误 “Fatal error: Call to a member function method() on a non-object ” ，并且这个致命错误会停止后面代码的继续执行。

所以如果你想继续执行代码，可以通过异常处理的方式解决：

     {
        nonExistFunction($arg);  }  (EngineException $e ) {
         ;
    }

5. **结合比较运算符（ <=>****）**

这个不多解释，我们直接看示例代码，通过代码你可以很容易了解该运算符的作用。

     {
        ($a < $b) ?  : (($a > $b) ?  : )
    }  {
        $a <=> $b;
    }

6. **定义数组常量**

过去我们在用 define() 定义常量时，数据类型只支持标量，但在 PHP7 中，支持定义数组类型的常量。

    define('MYCONSTANT', array('a','b','c'))

PHP7 新增的特性还有很多，今天我们先介绍到这，后续会持续更新，同时也欢迎广大 PHPer 补充，我们共同交流，共同学习，共同进步。


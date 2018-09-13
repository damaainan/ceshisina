# PHP调试之回溯跟踪

 时间 2017-04-21 17:14:31  SegmentFault

_原文_[https://segmentfault.com/a/1190000009148494][1]


## 前言

在我们调试程序过程中，往往可能会出现需要回溯跟踪一个方法，这里我就介绍两个比较不错的PHP函数，也是我经常使用的。

## 内容

    (PHP 4 >= 4.3.0, PHP 5, PHP 7)
    debug_backtrace — 产生一条回溯跟踪(backtrace)
    
    (PHP 5, PHP 7)
    debug_print_backtrace — 打印一条回溯。

这两个看起来有点相似，其实功能也是差不多的，下面我就以一个简单的例子向大家演示下他们的使用。

## 实例
```php
<?php
/**
 * PHP回溯
 * @author chenyanphp@qq.com
 */
header("Content-Type:text/html;charset=utf-8");

/**
 *
 * 调试函数
 * @param $content
 */
function dump($content)
{
    echo '<pre>';
    var_dump($content);
    echo '</pre>';
}

/**
 * Class A
 */
class A
{
    public function say()
    {
        // 这里打印回溯内容
        dump(debug_backtrace());
        // 调用本身方法打印
        debug_print_backtrace();
        echo '<br>';
        echo 'Hello World!';
    }
}

/**
 * Class B
 */
class B
{
    public function sayB(A $obj)
    {
        $obj->say();
    }
}

/**
 * 测试结果
 */
$a = new A();
$b = new B();
$b->sayB($a);
```
下面是运行结果：

    array(2) {
      [0]=>
      array(7) {
        ["file"]=>
        string(29) "D:\phpStudy\WWW\test\test.php"
        ["line"]=>
        int(43)
        ["function"]=>
        string(3) "say"
        ["class"]=>
        string(1) "A"
        ["object"]=>
        object(A)#1 (0) {
        }
        ["type"]=>
        string(2) "->"
        ["args"]=>
        array(0) {
        }
      }
      [1]=>
      array(7) {
        ["file"]=>
        string(29) "D:\phpStudy\WWW\test\test.php"
        ["line"]=>
        int(52)
        ["function"]=>
        string(4) "sayB"
        ["class"]=>
        string(1) "B"
        ["object"]=>
        object(B)#2 (0) {
        }
        ["type"]=>
        string(2) "->"
        ["args"]=>
        array(1) {
          [0]=>
          object(A)#1 (0) {
          }
        }
      }
    }
    #0 A->say() called at [D:\phpStudy\WWW\test\test.php:43] #1 B->sayB(A Object ()) called at [D:\phpStudy\WWW\test\test.php:52] 
    Hello World!

结合代码不难看出，他们着重返回结果集，拆分结构；另一个着重按调用顺序打印出回溯跟踪。

## 总结

内容就这么多，结果一目了然，其他自己测试下就明了了。

PHP有些方法还是挺不错的，大家平时可以多看看手册。

下面放了他们两的官方链接，有兴趣的可以点击详细看看。

[debug_backtrace][3]  
[debug_print_backtrace][4]




[1]: https://segmentfault.com/a/1190000009148494?utm_source=tuicool&utm_medium=referral

[3]: http://cn2.php.net/manual/zh/function.debug-backtrace.php
[4]: http://cn2.php.net/manual/zh/function.debug-print-backtrace.php
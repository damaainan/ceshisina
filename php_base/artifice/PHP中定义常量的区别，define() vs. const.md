# PHP中定义常量的区别，define() vs. const

 时间 2018-01-10 20:21:38 皮皮赖's Blog

原文[https://www.52bz.la/3571.html][1]


## 前言

今天在Stackoverflow又看到一个很有趣的文章，所以翻译过后摘了过来。文章是由PHP开发成员之一的NikiC写的，权威性自然毋庸置疑 

## 正文

在PHP5.3中，有两种方法可以定义常量： 

    const
    define()
    

    const FOO = 'BAR'; define('FOO','BAR');

这两种方式的根本区别在于 const 会在代码编译时定义一个常量，而 define 则是在代码运行时才定义一个常量。这就使得 const 会有以下几个缺点： 

* const 不能在条件语句中使用。如果要定义一个全局变量， const 必须要处于整个代码的最外层：

```
    if (...) {
         const FOO = 'BAR';    // 无效的 
    } // but 
    if (...) {
         define('FOO', 'BAR'); // 有效的 
    }
```
你可以能会问为什么我要这么做？一个最平常的例子是当你在检测一个常量是否已经被定义时：

    if (!defined('FOO')) {
         define('FOO', 'BAR'); 
     }

* const 只能用来声明变量（如数字、字符串，或者 true, false, null, _FILE_ ），而 define() 还能接受表达式。不过在PHP5.6之后 const 也可以接受常量的表达式了：

```
    const BIT_5 = 1 << 5;    // 在PHP5.6之后有效，之前无效 
    define('BIT_5', 1 << 5); // 一直有效
```
* const 的常量命名只能用直白的文本，而 define() 允许你用任何表达式来对常量命名。这样我们就可以执行以下操作：
```
    for ($i = 0; $i < 32; ++$i) {
         define('BIT_' . $i, 1 << $i); 
     }
```
* const 定义的常量是大小写敏感的，但是 define 允许你将其第三个参数设置为true来关闭其对大小写的敏感：
```
    define('FOO', 'BAR', true); 
    echo FOO; // BAR 
    echo foo; // BAR
```
以上就是你需要注意的几点。那么现在我来说明以下，为什么不涉及以上情况下，我个人总是习惯使用 const ： 

* const 更加易读、美观。
* const 默认在当前的 namespace 下定义常量，而使用 define 则需要你写明整个 namespace 的完整路径：
```
    namespace A/B/C; // 如果要定义常量 A/B/C/FOO: 
    const FOO = 'BAR'; 
    define('A/B/C/FOO', 'BAR');
```
* 自从PHP5.6后，使用 const 数组也能被定义为常量。而 define 目前是不支持这一功能的，但是该功能会在PHP7中被实现：
```
    const FOO = [1, 2, 3];    // 在PHP 5.6中有效 
    define('FOO', [1, 2, 3]); // 在PHP 5.6无效, 在PHP 7.0有效
```
* 因为 const 在编译时就被执行了，所以它在速度上要比 define 快一点。

尤其是在使用 define 定义大量常量时，PHP的运行速度会变得非常慢。人们甚至发明了诸如 apc_load_constantshide 来避免这个问题 

与 define 相比， const 能使定义常量的效率提高一倍（在配置有XDebug的开发机器上，这个差异还会更大）。但是在查询时间上，两者是没有区别的（因为二者用的都是同一个查询表） 

最后需要注意的一点是， const 可以在class和interface当中使用 ，而 define 是做不到这一点的： 

    class Foo {
         const BAR = 2; // 有效 
     } 
     class Baz {
          define('QUX', 2); // 无效 
      }

### 总结

除非你需要使用表达式或者在条件语句中定义常量，不然的话仅仅是为了代码的简单可读性你都最好要使用 const ！

[1]: https://www.52bz.la/3571.html

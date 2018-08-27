# 关于call_user_func学习理解

 发表于 2017-10-05  |    更新于 2017-10-07    |    分类于  [技术tech][0]    |     |  本文总阅读量 7 次    字数统计  3,309  |    阅读时长  13

摘要：  
有几个疑问：

1. 什么是回调？
1. 什么是回调函数？
1. 什么是 php 的回调函数？
1. 如何使用 php 的回调函数？

最近看了很多关于`call_user_func`的资料，对它不是很理解，基础的了解还是有的，但是总会有很多疑问

以下是官方的查到的解释：

* call_user_func(PHP 4, PHP 5, PHP 7)

```
<?php
call_user_func — 把第一个参数作为回调函数调用。

mixed call_user_func ( callable $callback [, mixed $parameter [, mixed $... ]] )

// 第一个参数 callback 是被调用的回调函数，其余参数是回调函数的参数。
```
参考：[PHP: call_user_func - Manual][1]

* Callback / Callable类型
    * 在 php 里面回调类型叫Callback / Callable类型
    * 自 PHP 5.4 起可用 callable 类型指定回调类型 callback。本文档基于同样理由使用 callback 类型信息。
    * 一些函数如` call_user_func()`或 `usort()`可以接受用户自定义的回调函数作为参数。回调函数不止可以是简单函数，还可以是对象的方法，包括静态类方法。  
参考：[PHP: Callback / Callable 类型 - Manual][2]

有几个疑问：

1. 什么是回调？
1. 什么是回调函数？
1. 什么是 php 的回调函数？
1. 如何使用 php 的回调函数？

## 什么是回调？

软件模块之间总是存在着一定的接口，从调用方式上，可以把他们分为三类：**同步调用、回调和异步调用**。

* 同步调用是一种阻塞式调用，调用方要等待对方执行完毕才返回，它是一种单向调用；
* 回调是一种双向调用模式，也就是说，被调用方在接口被调用时也会调用对方的接口；
* 异步调用是一种类似消息或事件的机制，不过它的调用方向刚好相反，接口的服务在收到某种讯息或发生某种事件时，会主动通知客户方（即调用客户方的接口）。
* 回调和异步调用的关系非常紧密，通常我们使用回调来实现异步消息的注册，通过异步调用来实现消息的通知。同步调用是三者当中最简单的，而回调又常常是异步调用的基础。

对于不同类型的语言（如结构化语言和对象语言）、平台（Win32、JDK）或构架（CORBA、DCOM、WebService），客户和服务的交互除了同步方式以外，都需要具备一定的异步通知机制，让服务方（或接口提供方）在某些情况下能够主动通知客户，而回调是实现异步的一个最简捷的途径。对于一般的结构化语言，可以通过回调函数来实现回调。回调函数也是一个函数或过程，不过它是一个由调用方自己实现，供被调用方使用的特殊函数。

在面向对象的语言中，回调则是通过接口或抽象类来实现的，我们把实现这种接口的类成为回调类，回调类的对象成为回调对象。

参考：[http://www.myexception.cn/php/1992369.html][3]

## 什么是回调函数？

* 先看一下C语言里的回调函数：回调函数就是一个通过函数指针调用的函数。如果你把函数的指针（地址）作为参数传递给另一个函数，当这个指针被用来调用其所指向的函数时，我们就说这是回调函数。回调函数不是由该函数的实现方直接调用，而是在特定的事件或条件发生时由另外的一方调用的，用于对该事件或条件进行响应。

> 通俗的来说，回调函数是一个我们定义的函数，但不是我们直接来调用，而是通过另一个函数来调用，这个函数通过接收回调函数的名字和参数来实现对它的调用。

参考：[php回调函数的概念及实例 - CSDN博客][4]

* 回调函数，顾名思义，既然是回，那么就有一个谁是主体的问题，因为回调是往回调用的意思，我调用了函数A，而函数A在执行过程中调用了我提供的函数B，这个函数B就称为函数A的回调函数，显然主体是函数A，
    * 例如：函数是完成某个特定功能的代码集合，在函数执行的过程中，一般是不能去干预他的行为的，当函数被设计成带有回调功能时，我们就有可能在函数的执行过程中，通过回调函数去干预他。


```php
<?php
// 例子：0001
function foo($n, $f='') {
  if($n < 1) return;
  for($i=0; $i<$n; $i++) {
    echo $f ? $f($i) : $i;
  }
}
//无回调时
foo(5); //01234
//有回调时
function f1($v) {
  return $v + $v;
}
// 通过 foo去调用 f1，不是自己调用，是别人调用
foo(5, 'f1'); //02468
```
1. 用户自定义函数也称自定义函数,它们不是PHP提供的,是由程序员创建的.由于自己创建了这样的函数,所以就可以完全控制这些函数.因此可以让一个函数完全按照自己希望的方式运行，例如例子里面的foo(5);，这种就是使用自定义函数，直接自己使用。
1. **回调函数就是那些自己写的，但不是自己来调，而是给别人来调用的函数**，例如例子里面的foo(5, 'f1');。  
参考：[PHP回调函数到底是个啥-CSDN论坛][5]

* 当做一个变量去理解比较容易，比如


```php
<?php
// 函数名作为变量去调用
function a(){
}
// 这里匿名函数被变量调用
$b="a";
$b();
// 这里有函数被别人调用的意思，这就是回调函数常用的方式，或者说，这样调用函数的方式叫做回调函数。
```
## 什么是 php 的回调函数？

* PHP中将一个函数赋值给一个变量的方式有四种：
    1. 函数在外部定义或PHP内置，直接将函数名作为字符串参数传入。注意：如果是类静态函数的话以CLASS::FUNC_NAME的方式传入。（常用方式）
    1. 使用create_function($args, $func_code);创建函数，会返回一个函数名。$func_code为代码体，$args为参数字符串，以,分隔。（这种类似eval()方法的用法，也被PHP官方列为不推荐使用的方式，而且其定义方式太不直观）
    1. 直接赋值:$func_name = function($arg){statement}；。（这种方式创建的函数非常灵活，可以通过变量引用。可以用 is_callable($func_name)来测试此函数是否可以被调用， 也可以通过$func_name($var)来直接调用）
    1. 直接使用匿名函数，在参数处直接定义函数，不赋给具体的变量值。(方式创建的函数比较类似于JS中的回调函数，不需要变量赋值，直接使用)

> 第三和第四种方式创建的函数都是匿名函数，在 php 里面也叫闭包函数，通常他们就是比较多用来进行回调的。  
> 参考：[PHP中的回调函数和匿名函数 - 枕边书 - 博客园][6]

* PHP支持回调函数（callback），但和JavaScript相比，5.3之前的并不是特别灵活，只有“字符串的函数名”和“使用creat_function的返回值”两种选择。在5.3之后又多了匿名函数（在 php 里面也叫**闭包Closure**）的选择。
* php内置函数中很多用到了回调函数，例如：
    * `array_filter` — 用回调函数过滤数组中的单元。
    * `array_diff_ukey` — 用回调函数对键名比较计算数组的差集。

    
```php
<?php
function odd($var)
{
   return($var % 2 == 1);
}
$array1 = array("a"=>1, "b"=>2, "c"=>3, "d"=>4, "e"=>5);
echo "Odd :\n";
// array_filter支持回调函数作为参数传入
print_r(array_filter($array1, "odd"));//这里把array1的值依次传入到odd这个函数里面，这种方式就称为回调
```
## 如何使用 php 的回调函数

### 自己直接使用

类似之前提到的 **例子：0001**

### 使用类似`call_user_func`或者官方提供的 `array_filter` 等的方式使用

官方例子：

* 普通例子

```php
<?php
function increment(&$var)
{
    $var++;
}
$a = 0;
call_user_func('increment', $a);
echo $a."\n";
call_user_func_array('increment', array(&$a)); // You can use this instead before PHP 5.3
echo $a."\n";
```

> 用 & 是因为传入call_user_func()的参数不能为引用传递。

* 命名空间的使用
```php
<?php
namespace Foobar;
class Foo {
    static public function test() {
        print "Hello world!\n";
    }
}
// 通过命名空间来调用类的静态方法
call_user_func(__NAMESPACE__ .'\Foo::test'); // As of PHP 5.3.0
call_user_func(array(__NAMESPACE__ .'\Foo', 'test')); // As of PHP 5.3.0
```
* 调用一个类里面的方法

```php
<?php
class myclass {
    static function say_hello()
    {
        echo "Hello!\n";
    }
}
$classname = "myclass";
// 有3种方式
// 第一：使用 array 数组方式传入，数组第一个元素是 claaname，第二个元素是要调用的函数名
call_user_func(array($classname, 'say_hello'));
// 第二：跟调用类的静态方法类似
call_user_func($classname .'::say_hello'); // As of 5.2.3
// 第三：实例化对象后，array 数组方式传入，数组第一个元素是对象，第二个元素是要调用的函数名
$myobject = new myclass();
call_user_func(array($myobject, 'say_hello'));
```
> 需要注意的是

> 1. 这里的方法都是静态方法！
> 1. 是使用 array 的数组方式传入

* 把完整的函数作为回调传入

```php
<?php
call_user_func

( // 直接传入了一个匿名参数作为回调参数

    function($arg) {

        print "[$arg]\n";

    }

// 第二个参数是回调参数的参数

, 'test'); /* As of PHP 5.3.0 */
```
参考：[PHP: call_user_func - Manual][1]

#### 网上收集的例子

    
```php
<?php
// demo1: 回调php函数 字符串形式
$data = array("name"=>"callback" , "value"=>"test");
$rs1 = http_build_query($data);      //直接调用php函数
$rs2  = call_user_func("http_build_query",$data); //使用回调函数
echo $rs1;  //name=callback&value=test
echo "<br />";
echo $rs2;  //name=callback&value=test
// 这里需要注意的是,参数1必须是可使用的函数可以通过function_exists()返回true的函数，这里提醒isset,empty,is_null 等认为的常用函数实际上是一个操作符.并不能算函数。
```
    
```php
<?php
// demo2 回调php函数 函数名变量形式
function myUrldecode($str){
    return urldecode($str);
}
$data = array("name"=>"callback" , "value"=>"天才");
$str  = http_build_query($data);
$rs1  = urldecode($str);      //urlencode编码
$rs2  = call_user_func(myUrldecode,$str);
echo $rs1;  //name=callback&value=天才
echo "<br />";
echo $rs2;  //name=callback&value=天才
//这里我们可以看到,我们直接使用函数的名称也是可以的,不需要带引号字符串。
```
    
```php
<?php
// demo3 回调 类方法 数组格式
class MyClass{
    private $demo = "test";
    function myUrldecode($str){
        return urldecode($str);
    }
    static  function myUrlencode($str){
        return urlencode($str) ;
    }
}
$str = "?query=/test/demo1";
$encode  = call_user_func(array(MyClass,"myUrlencode"),$str);
//直接使用类的静态方法 将字符串进行url编码 不再是字符串或者函数名,而是一个数组格式,第一个项表示类名,第二个项则表示方法名。 第一项可以为类的引用地址,第二项为静态方法名称
$decode  = call_user_func(array("MyClass","myUrlencode"),$encode);
//同样是使用类的方法,不过调用的是普通方法名称。
echo $encode;  //%3Fquery%3D%2Ftest%2Fdemo1
echo "<br />"; //?query=/test/demo1
echo $decode;
//注意 使用方法名也具有作用域的概念,即private protected 和 public,通常回调类方法都只能调用publi 和默认作用域的 方法。
//同时如果是普通方法,并且内部使用了$this变量,那么进行调用是无法成功的.
```
    
```php
<?php
// demo4 回调类方法 字符串格式
class MyClass{
    private $demo = "test";
    function myUrldecode($str){
        return urldecode($str);
    }
    private function myUrldecode2($str){
    return urldecode($str);
    }
    static  function myUrlencode($str){
        return urlencode($str) ;
    }
}
$str = "?query=/test/demo1";
  $encode  = call_user_func("MyClass::myUrlencode",$str);
  $decode  = call_user_func("MyClass::myUrldecode",$encode);
  echo $encode; //  %3Fquery%3D%2Ftest%2Fdemo1
  echo "<br />";
  echo $decode; //  ?query=/test/demo1
  $encode2  = call_user_func("MyClass::myUrlencode2",$str);
  var_dump($encode2);  // null
//如果直接使用字符串的方法的话,那么必须在类和方法名中添加::作为分割。
//这里我们发现不是静态方法也可以用::进行调用
//这里进行了一个测试,发现调用private 作用域的方法返回的是一个null值,说明确实存在作用域的关系
```
    
```php
<?php
// demo5 回调对象方法 数组格式
class MyClass{
    private $demo = "test";
    function myUrldecode($str){
        return urldecode($str)  ."-" .$this->demo ; //调用内部的this作用域
    }
    static  function myUrlencode($str){
        return urlencode($str);
    }
}
 $str = "?query=/test/demo1";
 $class =  new MyClass();
 $encode  = call_user_func(array($class,"myUrlencode"),$str);
 $decode  = call_user_func(array($class,"myUrldecode"),$str);
 echo $encode; //%3Fquery%3D%2Ftest%2Fdemo1
 echo "<br />";
 echo $decode; //?query=/test/demo1-test
 //很明显,如果使用对象做为回调函数,内部的private 属性和方法也可以使用,但是对外的方法必须为默认或者 public类型
 //对象数组方式第一个选项必须为一个对象
```
参考：[【php】php中的回调函数使用心得 - 2 - lizixiang1993的专栏 - CSDN博客][7]

#### 函数名不确定，参数不确定的情况下用到

```php
<?php
// 第一个例子：（函数名不确定）
$a = "test";
$a();
// 这里如果test函数不存在，则直接会导致错误，而
call_user_func($a)
// 只会返回false
------------------------------------------
// 第二个例子：（参数不确定）
function add($username,$callback){
    return call_user_func($callback,$username);
}
function upper($username){
    return strtoupper($username);
}
class renew{
    function age($username){
       //省略
       return true;
    }
}
add("张三","upper");//直接调用一个函数
add("楼主",array(new renew(),"age"));//调用类里面的方法
```
参考：[php call_user_func有什么意义 - ThinkPHP框架][8]

### 匿名参数无法直接被调用

可以通过 call_user_func 来进行调用。

跟 [关于 php 的 Lambdas（匿名函数）和Closure（闭包）学习理解 | 神一样的少年][9]相关

* **本文作者：** 茅有知
* **本文链接：**[https://www.godblessyuan.com/backend/call_user_func-leran.html][10]

[0]: /categories/技术tech/
[1]: http://php.net/manual/zh/function.call-user-func.php
[2]: http://php.net/manual/zh/language.types.callable.php
[3]: http://www.myexception.cn/php/1992369.html
[4]: http://blog.csdn.net/u010544319/article/details/9186323
[5]: http://bbs.csdn.net/topics/390667186
[6]: http://www.cnblogs.com/zhenbianshu/p/6063340.html
[7]: http://blog.csdn.net/lizixiang1993/article/details/46387297
[8]: http://www.thinkphp.cn/topic/14366.html
[9]: https://www.godblessyuan.com/backend/anonymous-function-closure-lear.html
[10]: https://www.godblessyuan.com/backend/call_user_func-leran.html
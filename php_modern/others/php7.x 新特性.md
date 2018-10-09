## php7.x 新特性 看这一篇就够了

来源：[https://blog.csdn.net/yoloyy/article/details/80757910](https://blog.csdn.net/yoloyy/article/details/80757910)

时间：


## php 新特性



## 前言


上个月同事看见我写

```php
$a = $a ?? '';
```


问我这个写法是什么，还有这样的写法？我说这是php7以上才有的写法，你不知道吗？他说不知道。


心里嘀咕了一下，打算开始写这篇博客。


php7 应该是除了基础之外，是一种现在的php 。因为在php7 出现了，强类型定义，和一些语法上的写法，如 组合比较符， define() 可以定义数组等一些特性。下面开始正式介绍，从php7.0 开始介绍，以后出了新版本，也会在下面陆陆续续加上。 

好了，我们开始



## php7.0



### 标量类型声明


什么是标量类型？


四种标量类型： 

  boolean （布尔型） 

  integer （整型） 

  float （浮点型, 也称作 double) 

  string （字符串） 

  两种复合类型： 

  array （数组） 

  object （对象） 

  资源是一种特殊变量，保存了到外部资源的一个引用。资源是通过专门的函数来建立和使用的。资源类型变量为打开文件、数据库连接、图形画布区域等的特殊句柄。 

  说的通俗一点，标量类型，就是定义变量的一个数据类型。

在php5中，有类名，接口，数组 和回调函数。在php中，增加了 符串(string), 整数 (int), 浮点数 (float), 以及布尔值 (bool)。下面我们来举例子，万事万物看例子

```php
function typeInt(int $a)
{
    echo $a;
}

typeInt('sad');
// 运行，他讲会报错 Fatal error: Uncaught TypeError: Argument 1 passed to type() must be of the type integer, string given
```


在这里，我们定义了$a 必须为int类型，如果 type函数里面传了string 所以报错。让我们修改上述的代码就没错了

```php
function typeString(string $a)
{
    echo $a;
}

typeString('sad'); 
//sad
```



### 返回值类型声明


关于函数的方法返回值，可以定义，比如我某个函数必须要返回int类型，他就定死来返回int ，如果你返回string 则报错。下面看代码

```php

<?php

function returnArray(): array
{

    return [1, 2, 3, 4];
}

print_r(returnArray());
/*Array
(
    [0] => 1
    [1] => 2
    [2] => 3
    [3] => 4
)
*/
```


那当我们的定义了数组，返回了string或者其他类型呢？


那么他将会 **`报错`**  比如

```php
function returnErrorArray(): array
{

    return '1456546';
}

print_r(returnErrorArray());
/*
Array
Fatal error: Uncaught TypeError: Return value of returnArray() must be of the type array, string returned in 
*/

```



### null合并运算符


由于日常使用中存在大量同时使用三元表达式和 isset()的情况， 我们添加了null合并运算符 (??) 这个语法糖。如果变量存在且值不为NULL， 它就会返回自身的值，否则返回它的第二个操作数。

```php
<?php

$username = $_GET['user'] ?? 'nobody';
//这两个是等效的  当不存在user 则返回?? 后面的参数

$username = isset($_GET['user']) ? $_GET['user'] : 'nobody';

?>

```



### 太空船操作符

```php
// 整数
echo 1 <=> 1; // 0 当左边等于右边的时候，返回0
echo 1 <=> 2; // -1  当左边小于右边，返回-1
echo 2 <=> 1; // 1  当左边大于右边，返回1

// 浮点数
echo 1.5 <=> 1.5; // 0
echo 1.5 <=> 2.5; // -1
echo 2.5 <=> 1.5; // 1

// 字符串
echo "a" <=> "a"; // 0
echo "a" <=> "b"; // -1
echo "b" <=> "a"; // 1
```



### define 定义数组


在php7 以前的版本 define 是不能够定义数组的 现在是可以的 比如

```php
define('ANIMALS', [
    'dog',
    'cat',
    'bird'
]);

echo ANIMALS[1]; // 输出 "cat"
```



### use方法 批量导入

```php

/ PHP 7 之前的代码
use some\namespace\ClassA;
use some\namespace\ClassB;
use some\namespace\ClassC as C;

use function some\namespace\fn_a;
use function some\namespace\fn_b;
use function some\namespace\fn_c;

use const some\namespace\ConstA;
use const some\namespace\ConstB;
use const some\namespace\ConstC;

// PHP 7+ 及更高版本的代码
use some\namespace\{ClassA, ClassB, ClassC as C};
use function some\namespace\{fn_a, fn_b, fn_c};
use const some\namespace\{ConstA, ConstB, ConstC};
```



### Unicode codepoint 转译语法

```php
echo "\u{aa}"; //ª
echo "\u{0000aa}";  //ª  
echo "\u{9999}"; //香


```



### 匿名类

```php
<?php
interface Logger {
    public function log(string $msg);
}

class Application {
    private $logger;

    public function getLogger(): Logger {
         return $this->logger;
    }

    public function setLogger(Logger $logger) {
         $this->logger = $logger;
    }
}

$app = new Application;
$app->setLogger(new class implements Logger {  //这里就是匿名类
    public function log(string $msg) {
        echo $msg;
    }
});
```



## php7.1



### 可为空类型


参数以及返回值的类型现在可以通过在类型前加上一个问号使之允许为空。 当启用这个特性时，传入的参数或者函数返回的结果要么是给定的类型，要么是 null 。

```php
<?php

function testReturn(): ?string
{
    return 'elePHPant';
}

var_dump(testReturn()); //string(10) "elePHPant"

function testReturn(): ?string
{
    return null;
}

var_dump(testReturn()); //NULL

function test(?string $name)
{
    var_dump($name);
}

test('elePHPant'); //string(10) "elePHPant"
test(null); //NULL
test(); //Uncaught Error: Too few arguments to function test(), 0 passed in...
```



### void


增加了一个返回void的类型，比如

```php
<?php
function swap(&$left, &$right) : void
{
    if ($left === $right) {
        return;
    }

    $tmp = $left;
    $left = $right;
    $right = $tmp;
}

$a = 1;
$b = 2;
var_dump(swap($a, $b), $a, $b);
```

### 多异常捕获处理


这个功能还是比较常用的，在日常开发之中

```php
<?php
try {
    // some code
} catch (FirstException | SecondException $e) {  //用 | 来捕获FirstException异常，或者SecondException 异常

}
```



## php7.2


php7.2是php7系列 最少的新特性了

### 允许分组命名空间的尾部逗号


比如

```php
<?php

use Foo\Bar\{
    Foo,
    Bar,
    Baz,
};
```



### 允许重写抽象方法

```php
<?php

abstract class A
{
    abstract function test(string $s);
}
abstract class B extends A
{
    // overridden - still maintaining contravariance for parameters and covariance for return
    abstract function test($s) : int;
}
```

### 新的对象类型

```php
?php

function test(object $obj) : object  //这里 可以输入对象
{
    return new SplQueue();
}

test(new StdClass());
```

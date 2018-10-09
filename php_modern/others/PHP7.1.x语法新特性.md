## PHP7.1.x语法新特性

来源：[https://blog.csdn.net/jiaweibin01/article/details/81475823](https://blog.csdn.net/jiaweibin01/article/details/81475823)

时间：

						
## 1.空合并运算符（??）


简化判断

```php
$param = $_GET['param'] ?? 1;
```


相当于：

```php
$param = isset($_GET['param']) ? $_GET['param'] : 1;
```


## 2.变量类型声明


两种模式 : 强制 ( 默认 ) 和 严格模式 

类型：string、int、float和 bool

```php
function add(int $a) { 
    return 1+$a; 
} 
var_dump(add(2); 
```


## 3.返回值类型声明


函数和匿名函数都可以指定返回值的类型

```php
function show(): array { 
    return [1,2,3,4]; 
}

function arraysSum(array ...$arrays): array {
return array_map(function(array $array): int {
return array_sum($array);
}, $arrays);
}
```


## 4.太空船操作符（组合比较符）


太空船操作符用于比较两个表达式。当  a大于、等于或小于  b 时它分别返回 -1 、 0 或 1 。 比较的原则是沿用 PHP 的常规比较规则进行的。

```php
// Integers
echo 1 <=> 1; // 0
echo 1 <=> 2; // -1
echo 2 <=> 1; // 1
// Floats
echo 1.5 <=> 1.5; // 0
echo 1.5 <=> 2.5; // -1
echo 2.5 <=> 1.5; // 1
// Strings
echo "a" <=> "a"; // 0
echo "a" <=> "b"; // -1
echo "b" <=> "a"; // 1
```


## 5.匿名类


现在支持通过 new class 来实例化一个匿名类，这可以用来替代一些“用后即焚”的完整类定义。

```php
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
$app->setLogger(new class implements Logger {
    public function log(string $msg) {
        echo $msg;
    }
});
var_dump($app->getLogger());
```


## 6.Unicode codepoint 转译语法


这接受一个以16进制形式的 Unicode codepoint，并打印出一个双引号或heredoc包围的 UTF-8 编码格式的字符串。 可以接受任何有效的 codepoint，并且开头的 0 是可以省略的。

```php
 echo "\u{9876}"
```


旧版输出：\u{9876} 

新版输入：顶


## 7.Closure::call()


Closure::call() 现在有着更好的性能，简短干练的暂时绑定一个方法到对象上闭包并调用它。

```php
class Test {
    public $name = "lixuan";
}

//PHP7和PHP5.6都可以
$getNameFunc = function () {
    return $this->name;
};
$name = $getNameFunc->bindTo(new Test, 'Test');
echo $name();
//PHP7可以,PHP5.6报错
$getX = function () {
    return $this->name;
};
echo $getX->call(new Test);
```


## 8.为unserialize()提供过滤


这个特性旨在提供更安全的方式解包不可靠的数据。它通过白名单的方式来防止潜在的代码注入。

```php
//将所有对象分为__PHP_Incomplete_Class对象
$data = unserialize($foo, ["allowed_classes" => false]);
//将所有对象分为__PHP_Incomplete_Class 对象 除了ClassName1和ClassName2
$data = unserialize($foo, ["allowed_classes" => ["ClassName1", "ClassName2"]);
//默认行为，和 unserialize($foo)相同
$data = unserialize($foo, ["allowed_classes" => true]);
```


## 9.IntlChar


新增加的 IntlChar 类旨在暴露出更多的 ICU 功能。这个类自身定义了许多静态方法用于操作多字符集的 unicode 字符。Intl是Pecl扩展，使用前需要编译进PHP中，也可apt-get/yum/port install php5-intl

```php
printf('%x', IntlChar::CODEPOINT_MAX);
echo IntlChar::charName('@');
var_dump(IntlChar::ispunct('!'));
```



以上例程会输出： 

10ffff 

COMMERCIAL AT 

bool(true)


## 10.预期


预期是向后兼用并增强之前的 assert() 的方法。 它使得在生产环境中启用断言为零成本，并且提供当断言失败时抛出特定异常的能力。 老版本的API出于兼容目的将继续被维护，assert()现在是一个语言结构，它允许第一个参数是一个表达式，而不仅仅是一个待计算的 string或一个待测试的boolean。

```php
ini_set('assert.exception', 1);
class CustomError extends AssertionError {}
assert(false, new CustomError('Some error message'));
```



以上例程会输出： 

Fatal error: Uncaught CustomError: Some error message


## 11.Group use declarations


从同一 namespace 导入的类、函数和常量现在可以通过单个 use 语句 一次性导入了。

```php
//PHP7之前
use some\namespace\ClassA;
use some\namespace\ClassB;
use some\namespace\ClassC as C;
use function some\namespace\fn_a;
use function some\namespace\fn_b;
use function some\namespace\fn_c;
use const some\namespace\ConstA;
use const some\namespace\ConstB;
use const some\namespace\ConstC;

// PHP7之后
use some\namespace\{ClassA, ClassB, ClassC as C};
use function some\namespace\{fn_a, fn_b, fn_c};
use const some\namespace\{ConstA, ConstB, ConstC};
```



## 12.intdiv()


接收两个参数作为被除数和除数，返回他们相除结果的整数部分。

```php
var_dump(intdiv(7, 2));
```



输出int(3)


## 13.CSPRNG


新增两个函数: random_bytes() and random_int().可以加密的生产被保护的整数和字符串。总之随机数变得安全了。 

random_bytes — 加密生存被保护的伪随机字符串 

random_int —加密生存被保护的伪随机整数


## 14、preg_replace_callback_array()


新增了一个函数preg_replace_callback_array()，使用该函数可以使得在使用preg_replace_callback()函数时代码变得更加优雅。在PHP7之前，回调函数会调用每一个正则表达式，回调函数在部分分支上是被污染了。


## 15、Session options


现在，session_start()函数可以接收一个数组作为参数，可以覆盖php.ini中session的配置项。 

比如，把cache_limiter设置为私有的，同时在阅读完session后立即关闭。

```php
session_start(['cache_limiter' => 'private',
               'read_and_close' => true,
]);
```


## 16、生成器的返回值


在PHP5.5引入生成器的概念。生成器函数每执行一次就得到一个yield标识的值。在PHP7中，当生成器迭代完成后，可以获取该生成器函数的返回值。通过Generator::getReturn()得到。

```php
function generator() {
    yield 1;
    yield 2;
    yield 3;
    return "a";
}

$generatorClass = ("generator")();
foreach ($generatorClass as $val) {
    echo $val ." ";

}
echo $generatorClass->getReturn();
```


输出为：1 2 3 a


## 17、生成器中引入其他生成器


在生成器中可以引入另一个或几个生成器，只需要写yield from functionName1

```php
function generator1() {
    yield 1;
    yield 2;
    yield from generator2();
    yield from generator3();
}

function generator2() {
    yield 3;
    yield 4;
}

function generator3() {
    yield 5;
    yield 6;
}

foreach (generator1() as $val) {
    echo $val, " ";
}
```



输出：1 2 3 4 5 6


## 18.通过define()定义常量数组

```php
define('ANIMALS', ['dog', 'cat', 'bird']);
echo ANIMALS[1]; // outputs "cat"
```

以上为7.0

-----

以下为7.1



## 

```php
function test(?string $name) {
    var_dump($name);
}
```



以上例程会输出：

```


string(5) "tpunt"
NULL
Uncaught Error: Too few arguments to function test(), 0 passed in...
```


## 2.Void 函数


在PHP 7 中引入的其他返回值类型的基础上，一个新的返回值类型void被引入。 返回值声明为 void 类型的方法要么干脆省去 return 语句，要么使用一个空的 return 语句。 对于 void 函数来说，null 不是一个合法的返回值。

```php
function swap(&$left, &$right) : void {
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



以上例程会输出：

```


null
int(2)
int(1)
```



试图去获取一个 void 方法的返回值会得到 null ，并且不会产生任何警告。这么做的原因是不想影响更高层次的方法。


## 3.短数组语法 Symmetric array destructuring


短数组语法（[]）现在可以用于将数组的值赋给一些变量（包括在foreach中）。 这种方式使从数组中提取值变得更为容易。

```php
$data = [
    ['id' => 1, 'name' => 'Tom'],
    ['id' => 2, 'name' => 'Fred'],
];
while (['id' => $id, 'name' => $name] = $data) {
    // logic here with $id and $name
}
```



## 4.类常量可见性


现在起支持设置类常量的可见性。

```php
class ConstDemo
{
    const PUBLIC_CONST_A = 1;
    public const PUBLIC_CONST_B = 2;
    protected const PROTECTED_CONST = 3;
    private const PRIVATE_CONST = 4;
}
```



## 5.iterable 伪类


现在引入了一个新的被称为iterable的伪类 (与callable类似)。 这可以被用在参数或者返回值类型中，它代表接受数组或者实现了Traversable接口的对象。 至于子类，当用作参数时，子类可以收紧父类的iterable类型到array 或一个实现了Traversable的对象。对于返回值，子类可以拓宽父类的 array或对象返回值类型到iterable。

```php
function iterator(iterable $iter) {
    foreach ($iter as $val) {
        //
    }
}
```


## 6.多异常捕获处理


一个catch语句块现在可以通过管道字符(|)来实现多个异常的捕获。 这对于需要同时处理来自不同类的不同异常时很有用。

```php
try {
    // some code
} catch (FirstException | SecondException $e) {
    // handle first and second exceptions
} catch (\Exception $e) {
    // ...
}
```



## 7.list()现在支持键名


现在list()支持在它内部去指定键名。这意味着它可以将任意类型的数组 都赋值给一些变量（与短数组语法类似）

```php
$data = [
    ['id' => 1, 'name' => 'Tom'],
    ['id' => 2, 'name' => 'Fred'],
];
while (list('id' => $id, 'name' => $name) = $data) {
    // logic here with $id and $name
}
```

## 8.支持为负的字符串偏移量


现在所有接偏移量的内置的基于字符串的函数都支持接受负数作为偏移量，包括数组解引用操作符([]).

```php
var_dump("abcdef"[-2]);
var_dump(strpos("aabbcc", "b", -3));
```



以上例程会输出：

```


string (1) "e"
int(3)
```



##  添加额外参数，现在支持了AEAD (模式 GCM and CCM)。 

通过 Closure::fromCallable() 将callables转为闭包 

Closure新增了一个静态方法，用于将callable快速地 转为一个Closure 对象。

```php
class Test {
    public function exposeFunction() {
        return Closure::fromCallable([$this, 'privateFunction']);
    }
    private function privateFunction($param) {
        var_dump($param);
    }
}
$privFunc = (new Test)->exposeFunction();
$privFunc('some value');
```



以上例程会输出：

```


string(10) "some value"
```


## 10.异步信号处理 Asynchronous signal handling


A new function called pcntl_async_signals() has been introduced to enable asynchronous signal handling without using ticks (which introduce a lot of overhead). 

增加了一个新函数 pcntl_async_signals()来处理异步信号，不需要再使用ticks(它会增加占用资源)

```php
pcntl_async_signals(true); // turn on async signals
pcntl_signal(SIGHUP,  function($sig) {
    echo "SIGHUP\n";
});
posix_kill(posix_getpid(), SIGHUP);
```



以上例程会输出：

```
SIGHUP
```


## 11.HTTP/2 服务器推送支持 ext/curl


Support for server push has been added to the CURL extension (requires version 7.46 and above). This can be leveraged through the curl_multi_setopt() function with the new CURLMOPT_PUSHFUNCTION constant. The constants CURL_PUST_OK and CURL_PUSH_DENY have also been added so that the execution of the server push callback can either be approved or denied. 

蹩脚英语： 

对于服务器推送支持添加到curl扩展（需要7.46及以上版本）。 

可以通过用新的CURLMOPT_PUSHFUNCTION常量 让curl_multi_setopt()函数使用。 

也增加了常量CURL_PUST_OK和CURL_PUSH_DENY，可以批准或拒绝 服务器推送回调的执行


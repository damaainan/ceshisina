## php7 新特性整理

来源：[https://blog.csdn.net/h330531987/article/details/74364681](https://blog.csdn.net/h330531987/article/details/74364681)

时间：


PHP7 已经出来1年了，PHP7.1也即将和大家见面，这么多好的特性，好的方法，为什么不使用呢，也希望PHP越来越好。  

在这里整理 PHP 5.1 ，PHP5.2,PHP5.3,PHP5.4,PHP5.5,PHP5.6 ,PHP7,PHP7.1 所有新特性，已备大家学习及使用  

PHP5.1~PHP5.6  [http://blog.csdn.net/fenglailea/article/details/9853645][0]  

PHP7~PHP7.1  

[http://blog.csdn.net/fenglailea/article/details/52717364][1]  

风.fox

## Buid-in web server内置了一个简单的Web服务器

把当前目录作为Root Document只需要这条命令即可:

```
php -S localhost:3300
```

也可以指定其它路径

```
php -S localhost:3300 -t /path/to/root  
```

还可以指定路由

```
php -S localhost:3300 router.php
```

## 命名空间(php5.3)

命名空间的分隔符为反斜杆\

```php
namespace fox\lanmps\Table;    
class Select {}
```

## 获取完整类别名称

PHP5.3 中引入命名空间的别名类和命名空间短版本的功能。虽然这并不适用于字符串类名称

```php
use Some\Deeply\Nested\Namespace\FooBar;    
// does not work, because this will try to use the global `FooBar` class    
$reflection = new ReflectionClass('FooBar');   
echo FooBar::class;  
```

为了解决这个问题采用新的FooBar::class语法，它返回类的完整类别名称

## 命名空间 use 操作符开始支持函数和常量的导入

```php
namespace Name\Space {  
    const FOO = 42;  
    function f() { echo __FUNCTION__."\n"; }  
}  
namespace {  
    use const Name\Space\FOO;  
    use function Name\Space\f;  

    echo FOO."\n";  
    f();  
} 
```

输出  

42  

Name\Space\f

## Group use declarations

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

## 支持延迟静态绑定

static关键字来引用当前类，即实现了延迟静态绑定

```php
class A {    
    public static function who() {    
        echo __CLASS__;    
    }    
    public static function test() {    
        static::who(); // 这里实现了延迟的静态绑定    
    }    
}    
class B extends A {    
    public static function who() {    
         echo __CLASS__;    
    }    
}
B::test();    
```

输出结果：  

B

## 支持goto语句

多数计算机程序设计语言中都支持无条件转向语句goto，当程序执行到goto语句时，即转向由goto语句中的标号指出的程序位置继续执行。尽管goto语句有可能会导致程序流程不清晰，可读性减弱，但在某些情况下具有其独特的方便之处，例如中断深度嵌套的循环和 if 语句。

```php
goto a;    
echo 'Foo';    
a:    
echo 'Bar';    
for($i=0,$j=50; $i<100; $i++) {    
  while($j--) {    
    if($j==17) goto end;    
  }     
}    
echo "i = $i";    
end:    
echo 'j hit 17'; 
```

## 支持闭包、Lambda/Anonymous函数

闭包（Closure）函数和Lambda函数的概念来自于函数编程领域。例如JavaScript 是支持闭包和 lambda 函数的最常见语言之一。  

在PHP中，我们也可以通过create_function()在代码运行时创建函数。但有一个问题：创建的函数仅在运行时才被编译，而不与其它代码同时被编译成执行码，因此我们无法使用类似APC这样的执行码缓存来提高代码执行效率。  

在PHP5.3中，我们可以使用Lambda/匿名函数来定义一些临时使用（即用即弃型）的函数，以作为array_map()/array_walk()等函数的回调函数。

```php
echo preg_replace_callback('~-([a-z])~', function ($match) {    
    return strtoupper($match[1]);    
}, 'hello-world');    
// 输出 helloWorld    
$greet = function($name)    
{    
    printf("Hello %s\r\n", $name);    
};    
$greet('World');    
$greet('PHP');    
//...在某个类中    
$callback =      function ($quantity, $product) use ($tax, &$total)         {    
   $pricePerItem = constant(__CLASS__ . "::PRICE_" .  strtoupper($product));    
   $total += ($pricePerItem * $quantity) * ($tax + 1.0);    
 };    
```

## 魔术方法`__callStatic()`和`__invoke()`

PHP中原本有一个魔术方法`__call()`，当代码调用对象的某个不存在的方法时该魔术方法会被自动调用。新增的`__callStatic()`方法则只用于静态类方法。当尝试调用类中不存在的静态方法时，`__callStatic()`魔术方法将被自动调用。

```php
class MethodTest {    
    public function __call($name, $arguments) {    
        // 参数 $name 大小写敏感    
        echo "调用对象方法 '$name' "    
             . implode(' -- ', $arguments). "\n";    
    }    
    /**  PHP 5.3.0 以上版本中本类方法有效  */    
    public static function __callStatic($name, $arguments) {    
        // 参数 $name 大小写敏感    
        echo "调用静态方法 '$name' "    
             . implode(' -- ', $arguments). "\n";    
    }    
}    

$obj = new MethodTest;    
$obj->runTest('通过对象调用');    
MethodTest::runTest('静态调用');  // As of PHP 5.3.0
```

以上代码执行后输出如下：  

调用对象方法’runTest’ –- 通过对象调用调用静态方法’runTest’ –- 静态调用  

以函数形式来调用对象时，`__invoke()`方法将被自动调用。

```php
class MethodTest {    
    public function __call($name, $arguments) {    
        // 参数 $name 大小写敏感    
        echo "Calling object method '$name' "    
             . implode(', ', $arguments). "\n";    
    }    

    /**  PHP 5.3.0 以上版本中本类方法有效  */    
    public static function __callStatic($name, $arguments) {    
        // 参数 $name 大小写敏感    
        echo "Calling static method '$name' "    
             . implode(', ', $arguments). "\n";    
    }    
}    
$obj = new MethodTest;    
$obj->runTest('in object context');    
MethodTest::runTest('in static context');  // As of PHP 5.3.0  
```

## Nowdoc语法

用法和Heredoc类似，但使用单引号。Heredoc则需要通过使用双引号来声明。  

Nowdoc中不会做任何变量解析，非常适合于传递一段PHP代码。

```php
// Nowdoc 单引号 PHP 5.3之后支持    
$name = 'MyName';    
echo <<<'EOT'    
My name is "$name".    
EOT;    
//上面代码输出 My name is "$name". ((其中变量不被解析)    
// Heredoc不加引号    
echo <<<FOOBAR    
Hello World!    
FOOBAR;    
//或者 双引号 PHP 5.3之后支持    
echo <<<"FOOBAR"    
Hello World!    
FOOBAR;  
```

支持通过Heredoc来初始化静态变量、类成员和类常量。

```php
// 静态变量    
function foo()    
{    
    static $bar = <<<LABEL    
Nothing in here...    
LABEL;    
}    
// 类成员、常量    
class foo    
{    
    const BAR = <<<FOOBAR    
Constant example    
FOOBAR;    

    public $baz = <<<FOOBAR    
Property example    
FOOBAR;    
}  
```

## 在类外也可使用const来定义常量

```php
//PHP中定义常量通常是用这种方式  
define("CONSTANT", "Hello world.");  

//并且新增了一种常量定义方式  
const CONSTANT = 'Hello World'; 
```

## 三元运算符增加了一个快捷书写方式

原本格式为是(expr1) ? (expr2) : (expr3)  

如果expr1结果为True，则返回expr2的结果。  

新增一种书写方式，可以省略中间部分，书写为expr1 ?: expr3  

如果expr1结果为True,则返回expr1的结果

```php
$expr1=1;
$expr2=2;
//原格式  
$expr=$expr1?$expr1:$expr2  
//新格式  
$expr=$expr1?:$expr2
```

输出结果：  

1  

1

## 空合并运算符（??）

简化判断

```php
$param = $_GET['param'] ?? 1;
```

相当于：

```php
$param = isset($_GET['param']) ? $_GET['param'] : 1;
```

## Json更懂中文(JSON_UNESCAPED_UNICODE)

```php
echo json_encode("中文", JSON_UNESCAPED_UNICODE);  
//输出："中文" 
```

## 二进制

```php
$bin  = 0b1101;  
echo $bin;  
//13 
```

## Unicode codepoint 转译语法

这接受一个以16进制形式的 Unicode codepoint，并打印出一个双引号或heredoc包围的 UTF-8 编码格式的字符串。 可以接受任何有效的 codepoint，并且开头的 0 是可以省略的。

```php
 echo "\u{9876}"
```

旧版输出：\u{9876}  

新版输入：顶

## 使用 ** 进行幂运算

加入右连接运算符  * 来进行幂运算。 同时还支持简写的 *  = 运算符，表示进行幂运算并赋值。

```php
printf("2 ** 3 ==      %d\n", 2 ** 3);
printf("2 ** 3 ** 2 == %d\n", 2 ** 3 ** 2);

$a = 2;
$a **= 3;
printf("a ==           %d\n", $a);
```

输出  

2 ** 3 == 8  

2  * 3 *    2 == 512  

a == 8

## 太空船操作符（组合比较符）

太空船操作符用于比较两个表达式。当   a  大    于    、    等    于    或    小    于           b
 时它分别返回 -1 、 0 或 1 。 比较的原则是沿用 PHP 的常规比较规则进行的。

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

## Traits

Traits提供了一种灵活的代码重用机制，即不像interface一样只能定义方法但不能实现，又不能像class一样只能单继承。至于在实践中怎样使用，还需要深入思考。  

魔术常量为TRAIT

```php
官网的一个例子：  
trait SayWorld {  
        public function sayHello() {  
                parent::sayHello();  
                echo "World!\n";  
                echo 'ID:' . $this->id . "\n";  
        }  
}  

class Base {  
        public function sayHello() {  
                echo 'Hello ';  
        }  
}  

class MyHelloWorld extends Base {  
        private $id;  

        public function __construct() {  
                $this->id = 123456;  
        }  

        use SayWorld;  
}  

$o = new MyHelloWorld();  
$o->sayHello();  

/*will output: 
Hello World! 
ID:123456 
 */  
```

## array 数组简写语法

```php
$arr = [1,'james', 'james@fwso.cn'];  
$array = [  
　　"foo" => "bar",  
　　"bar" => "foo"  
　　]; 
```

## array 数组中某个索引值简写

```php
function myfunc() {  
    return array(1,'james', 'james@fwso.cn');  
}
echo myfunc()[1];  

$name = explode(",", "Laruence,male")[0];  
explode(",", "Laruence,male")[3] = "phper";  
```

## 非变量array和string也能支持下标获取了

```php
echo array(1, 2, 3)[0];  
echo [1, 2, 3][0];  
echo "foobar"[2];  
```

## 支持为负的字符串偏移量

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

## 常量引用

"常量引用"意味着数组可以直接操作字符串和数组字面值。举两个例子:

```php
function randomHexString($length) {    
    $str = '';    
    for ($i = 0; $i < $length; ++$i) {    
        $str .= "0123456789abcdef"[mt_rand(0, 15)]; // direct dereference of string    
    }    
}    
function randomBool() {    
    return [false, true][mt_rand(0, 1)]; // direct dereference of array    
}   
```

## 常量增强

允许常量计算,允许使用包含数字、字符串字面值和常量的标量表达式

```php
const A = 2;  
const B = A + 1;  
class C  
{  
    const STR = "hello";  
    const STR2 = self::STR + ", world";  
}
```

允许常量作为函数参数默认

```php
function test($arg = C::STR2)
```

类常量可见性  

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

## 通过define()定义常量数组

```php
define('ANIMALS', ['dog', 'cat', 'bird']);
echo ANIMALS[1]; // outputs "cat"
```

## 函数变量类型声明

两种模式 : 强制 ( 默认 ) 和 严格模式  

类型：array,object(对象),string、int、float和 bool

```php
class bar {  
function foo(bar $foo) {  
}  
//其中函数foo中的参数规定了传入的参数必须为bar类的实例，否则系统会判断出错。同样对于数组来说，也可以进行判断，比如：  
function foo(array $foo) {  
}  
}  
　　foo(array(1, 2, 3)); // 正确，因为传入的是数组  
　　foo(123); // 不正确，传入的不是数组

function add(int $a) 
{ 
    return 1+$a; 
} 
var_dump(add(2));

function foo(int $i) { ... }  
foo(1);      // $i = 1  
foo(1.0);    // $i = 1  
foo("1");    // $i = 1  
foo("1abc"); // not yet clear, maybe $i = 1 with notice  
foo(1.5);    // not yet clear, maybe $i = 1 with notice  
foo([]);     // error  
foo("abc");  // error  
```

## 参数跳跃

如果你有一个函数接受多个可选的参数，目前没有办法只改变最后一个参数，而让其他所有参数为默认值。  

RFC上的例子，如果你有一个函数如下：

```php
function create_query($where, $order_by, $join_type='', $execute = false, $report_errors = true) { ... }  
```

那么有没有办法设置$report_errors=false，而其他两个为默认值。为了解决这个跳跃参数的问题而提出：

```php
create_query("deleted=0", "name", default, default, false);  
```

## 可变函数参数

代替 func_get_args()

```php
function add(...$args)  
{  
    $result = 0;  
    foreach($args as $arg)  
        $result += $arg;  
    return $result;  
} 
```

## 可为空（Nullable）类型

类型现在允许为空，当启用这个特性时，传入的参数或者函数返回的结果要么是给定的类型，要么是 null 。可以通过在类型前面加上一个问号来使之成为可为空的。

```php
function test(?string $name)
{
    var_dump($name);
}
```

以上例程会输出：

```php
string(5) "tpunt"
NULL
Uncaught Error: Too few arguments to function test(), 0 passed in...
```

## Void 函数

在PHP 7 中引入的其他返回值类型的基础上，一个新的返回值类型void被引入。 返回值声明为 void 类型的方法要么干脆省去 return 语句，要么使用一个空的 return 语句。 对于 void 函数来说，null 不是一个合法的返回值。

```php
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

以上例程会输出：

```
null
int(2)
int(1)
```

试图去获取一个 void 方法的返回值会得到 null ，并且不会产生任何警告。这么做的原因是不想影响更高层次的方法。

## 返回值类型声明

函数和匿名函数都可以指定返回值的类型

```php
function show(): array 
{ 
    return [1,2,3,4]; 
}

function arraysSum(array ...$arrays): array
{
return array_map(function(array $array): int {
return array_sum($array);
}, $arrays);
}
```

## 参数解包功能

在调用函数的时候，通过 … 操作符可以把数组或者可遍历对象解包到参数列表，这和Ruby等语言中的扩张(splat)操作符类似

```php
function add($a, $b, $c) {  
    return $a + $b + $c;  
}  
$arr = [2, 3];  
add(1, ...$arr);
```

## 实例化类

```php
class test{  
    function show(){  
return 'test';  
    }  
}  
echo (new test())->show();
```

## 支持 Class::{expr}() 语法

```php
foreach ([new Human("Gonzalo"), new Human("Peter")] as $human) {  
    echo $human->{'hello'}();  
} 
```

## Callable typehint

```php
function foo(callable $callback) {  
}
```

则

```php
foo("false"); //错误，因为false不是callable类型  
　　foo("printf"); //正确  
　　foo(function(){}); //正确  
class A {  
　　static function show() {  
    }  
}  
　　foo(array("A", "show")); //正确
```

## Getter 和 Setter

如果你从不喜欢写这些getXYZ()和setXYZ($value)方法，那么这应该是你最受欢迎的改变。提议添加一个新的语法来定义一个属性的设置/读取:

```php
class TimePeriod {  
    public $seconds;  
    public $hours {  
        get { return $this->seconds / 3600; }  
        set { $this->seconds = $value * 3600; }  
    }  
}  
$timePeriod = new TimePeriod;  
$timePeriod->hours = 10;  
var_dump($timePeriod->seconds); // int(36000)  
var_dump($timePeriod->hours);   // int(10)  
```

## 迭代器 yield

目前，自定义迭代器很少使用，因为它们的实现，需要大量的样板代码。生成器解决这个问题，并提供了一种简单的样板代码来创建迭代器。  

例如，你可以定义一个范围函数作为迭代器:

```php
function *xrange($start, $end, $step = 1) {  
    for ($i = $start; $i < $end; $i += $step) {  
        yield $i;  
    }  
}  
foreach (xrange(10, 20) as $i) {  
    // ...  
}  
```

上述xrange函数具有与内建函数相同的行为，但有一点区别：不是返回一个数组的所有值，而是返回一个迭代器动态生成的值。

## 列表解析和生成器表达式

列表解析提供一个简单的方法对数组进行小规模操作:

```php
$firstNames = [foreach ($users as $user) yield $user->firstName];  
```

上述列表解析相等于下面的代码：

```php
$firstNames = [];  
foreach ($users as $user) {  
    $firstNames[] = $user->firstName;  
}
```

也可以这样过滤数组:

```php
$underageUsers = [foreach ($users as $user) if ($user->age < 18) yield $user];  
```

生成器表达式也很类似，但是返回一个迭代器(用于动态生成值)而不是一个数组。

## 生成器的返回值

在PHP5.5引入生成器的概念。生成器函数每执行一次就得到一个yield标识的值。在PHP7中，当生成器迭代完成后，可以获取该生成器函数的返回值。通过Generator::getReturn()得到。

```php
function generator()
{
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

## 生成器中引入其他生成器

在生成器中可以引入另一个或几个生成器，只需要写yield from functionName1

```php
function generator1()
{
    yield 1;
    yield 2;
    yield from generator2();
    yield from generator3();
}

function generator2()
{
    yield 3;
    yield 4;
}

function generator3()
{
    yield 5;
    yield 6;
}

foreach (generator1() as $val) {
    echo $val, " ";
}
```

输出：1 2 3 4 5 6

## finally关键字

这个和java中的finally一样，经典的try … catch … finally 三段式异常处理。

## 多异常捕获处理

一个catch语句块现在可以通过管道字符(|)来实现多个异常的捕获。 这对于需要同时处理来自不同类的不同异常时很有用。

```php
try {
    // some code
} catch (FirstException | SecondException $e) {
    // handle first and second exceptions
} catch (\Exception $e) {
    // ...
} finally{
//
}
```

## foreach 支持list()

对于"数组的数组"进行迭代，之前需要使用两个foreach，现在只需要使用foreach + list了，但是这个数组的数组中的每个数组的个数需要一样。看文档的例子一看就明白了。

```php
$array = [  
    [1, 2],  
    [3, 4],  
];  
foreach ($array as list($a, $b)) {  
    echo "A: $a; B: $b\n";  
} 
```

## 短数组语法 Symmetric array destructuring

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

## list()现在支持键名

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

## iterable 伪类

现在引入了一个新的被称为iterable的伪类 (与callable类似)。 这可以被用在参数或者返回值类型中，它代表接受数组或者实现了Traversable接口的对象。 至于子类，当用作参数时，子类可以收紧父类的iterable类型到array 或一个实现了Traversable的对象。对于返回值，子类可以拓宽父类的 array或对象返回值类型到iterable。

```php
function iterator(iterable $iter)
{
    foreach ($iter as $val) {
        //
    }
}
```

## ext/openssl 支持 AEAD

通过给openssl_encrypt()和openssl_decrypt() 添加额外参数，现在支持了AEAD (模式 GCM and CCM)。  

通过 Closure::fromCallable() 将callables转为闭包  

Closure新增了一个静态方法，用于将callable快速地 转为一个Closure 对象。

```php
class Test
{
    public function exposeFunction()
    {
        return Closure::fromCallable([$this, 'privateFunction']);
    }
    private function privateFunction($param)
    {
        var_dump($param);
    }
}
$privFunc = (new Test)->exposeFunction();
$privFunc('some value');
```

以上例程会输出：

```php
string(10) "some value"
```

## 匿名类

现在支持通过 new class 来实例化一个匿名类，这可以用来替代一些"用后即焚"的完整类定义。

```php
interface Logger
{
    public function log(string $msg);
}

class Application
{
    private $logger;

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
}

$app = new Application;
$app->setLogger(new class implements Logger
{
    public function log(string $msg)
    {
        echo $msg;
    }
});
var_dump($app->getLogger());
```

## Closure::call()

Closure::call() 现在有着更好的性能，简短干练的暂时绑定一个方法到对象上闭包并调用它。

```php
class Test
{
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

## 为unserialize()提供过滤

这个特性旨在提供更安全的方式解包不可靠的数据。它通过白名单的方式来防止潜在的代码注入。

```php
//将所有对象分为__PHP_Incomplete_Class对象
$data = unserialize($foo, ["allowed_classes" => false]);
//将所有对象分为__PHP_Incomplete_Class 对象 除了ClassName1和ClassName2
$data = unserialize($foo, ["allowed_classes" => ["ClassName1", "ClassName2"]);
//默认行为，和 unserialize($foo)相同
$data = unserialize($foo, ["allowed_classes" => true]);
```

## IntlChar

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

## 预期

预期是向后兼用并增强之前的 assert() 的方法。 它使得在生产环境中启用断言为零成本，并且提供当断言失败时抛出特定异常的能力。 老版本的API出于兼容目的将继续被维护，assert()现在是一个语言结构，它允许第一个参数是一个表达式，而不仅仅是一个待计算的 string或一个待测试的boolean。

```php
ini_set('assert.exception', 1);
class CustomError extends AssertionError {}
assert(false, new CustomError('Some error message'));
```

以上例程会输出：  

Fatal error: Uncaught CustomError: Some error message

## intdiv()

接收两个参数作为被除数和除数，返回他们相除结果的整数部分。

```php
var_dump(intdiv(7, 2));
```

输出int(3)

## CSPRNG

新增两个函数: random_bytes() and random_int().可以加密的生产被保护的整数和字符串。总之随机数变得安全了。  

random_bytes — 加密生存被保护的伪随机字符串  

random_int —加密生存被保护的伪随机整数

## preg_replace_callback_array()

新增了一个函数preg_replace_callback_array()，使用该函数可以使得在使用preg_replace_callback()函数时代码变得更加优雅。在PHP7之前，回调函数会调用每一个正则表达式，回调函数在部分分支上是被污染了。

## Session options

现在，session_start()函数可以接收一个数组作为参数，可以覆盖php.ini中session的配置项。  

比如，把cache_limiter设置为私有的，同时在阅读完session后立即关闭。

```php
session_start(['cache_limiter' => 'private',
               'read_and_close' => true,
]);
```

## $_SERVER["REQUEST_TIME_FLOAT"]

这个是用来统计服务请求时间的，并用ms(毫秒)来表示

```php
echo "脚本执行时间 ", round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2), "s";  
```

## empty() 支持任意表达式

empty() 现在支持传入一个任意表达式，而不仅是一个变量

```php
function always_false() {
    return false;
}

if (empty(always_false())) {
    echo 'This will be printed.';
}

if (empty(true)) {
    echo 'This will not be printed.';
}
```

输出  

This will be printed.

## php://input 可以被复用

php://input 开始支持多次打开和读取，这给处理POST数据的模块的内存占用带来了极大的改善。

## Upload progress 文件上传

Session提供了上传进度支持，通过`$_SESSION["upload_progress_name"]`就可以获得当前文件上传的进度信息，结合Ajax就能很容易实现上传进度条了。  

详细的看[http://www.laruence.com/2011/10/10/2217.html][2]

## 大文件上传支持

可以上传超过2G的大文件。

## GMP支持操作符重载

GMP 对象支持操作符重载和转换为标量，改善了代码的可读性，如：

```php
$a = gmp_init(42);  
$b = gmp_init(17);  

// Pre-5.6 code:  
var_dump(gmp_add($a, $b));  
var_dump(gmp_add($a, 17));  
var_dump(gmp_add(42, $b));  

// New code:  
var_dump($a + $b);  
var_dump($a + 17);  
var_dump(42 + $b);  
```

## JSON 序列化对象

实现了JsonSerializable接口的类的实例在json_encode序列化的之前会调用jsonSerialize方法，而不是直接序列化对象的属性。  

参考[http://www.laruence.com/2011/10/10/2204.html][3]

## HTTP状态码在200-399范围内均被认为访问成功

## 支持动态调用静态方法

```php
class Test{    
    public static function testgo()    
    {    
         echo "gogo!";    
    }    
}    
$class = 'Test';    
$action = 'testgo';    
$class::$action();  //输出 "gogo!"
```

## 弃用e修饰符

e修饰符是指示preg_replace函数用来评估替换字符串作为PHP代码，而不只是仅仅做一个简单的字符串替换。不出所料，这种行为会源源不断的出现安全问题。这就是为什么在PHP5.5 中使用这个修饰符将抛出一个弃用警告。作为替代，你应该使用preg_replace_callback函数。你可以从RFC找到更多关于这个变化相应的信息。

## 新增函数 boolval

PHP已经实现了strval、intval和floatval的函数。为了达到一致性将添加boolval函数。它完全可以作为一个布尔值计算，也可以作为一个回调函数。

## 新增函数hash_pbkdf2

PBKDF2全称"Password-Based Key Derivation Function 2"，正如它的名字一样，是一种从密码派生出加密密钥的算法。这就需要加密算法，也可以用于对密码哈希。更广泛的说明和用法示例

## array_column

```php
//从数据库获取一列，但返回是数组。  
$userNames = [];  
foreach ($users as $user) {  
    $userNames[] = $user['name'];  
}  
//以前获取数组某列值，现在如下  
$userNames = array_column($users, 'name');  
```

## 一个简单的密码散列API

```php
$password = "foo";    
// creating the hash    
$hash = password_hash($password, PASSWORD_BCRYPT);    
// verifying a password    
if (password_verify($password, $hash)) {    
    // password correct!    
} else {    
    // password wrong!    
}   
```

## 异步信号处理 Asynchronous signal handling

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

## HTTP/2 服务器推送支持 ext/curl

Support for server push has been added to the CURL extension (requires version 7.46 and above). This can be leveraged through the curl_multi_setopt() function with the new CURLMOPT_PUSHFUNCTION constant. The constants CURL_PUST_OK and CURL_PUSH_DENY
 have also been added so that the execution of the server push callback can either be approved or denied.  

蹩脚英语：  

对于服务器推送支持添加到curl扩展（需要7.46及以上版本）。  

可以通过用新的CURLMOPT_PUSHFUNCTION常量 让curl_multi_setopt()函数使用。  

也增加了常量CURL_PUST_OK和CURL_PUSH_DENY，可以批准或拒绝 服务器推送回调的执行

## php.ini中可使用变量

## PHP default_charset 默认字符集 为UTF-8

## ext/phar、ext/intl、ext/fileinfo、ext/sqlite3和ext/enchant等扩展默认随PHP绑定发布。其中Phar可用于打包PHP程序，类似于Java中的jar机制

## PHP7.1不兼容性

## 1.当传递参数过少时将抛出错误

在过去如果我们调用一个用户定义的函数时，提供的参数不足，那么将会产生一个警告(warning)。 现在，这个警告被提升为一个错误异常(Error exception)。这个变更仅对用户定义的函数生效， 并不包含内置函数。例如：

```php
function test($param){}
test();
```

输出：

```
Uncaught Error: Too few arguments to function test(), 0 passed in %s on line %d and exactly 1 expected in %s:%d
```

## 2.禁止动态调用函数

禁止动态调用函数如下  

assert() - with a string as the first argument  

compact()  

extract()  

func_get_args()  

func_get_arg()  

func_num_args()  

get_defined_vars()  

mb_parse_str() - with one arg  

parse_str() - with one arg

```php
(function () {
    'func_num_args'();
})();
```

输出

```
Warning: Cannot call func_num_args() dynamically in %s on line %d
```

## 3.无效的类，接口，trait名称命名

以下名称不能用于 类，接口或trait 名称命名：  

void  

iterable

## 4.Numerical string conversions now respect scientific notation

Integer operations and conversions on numerical strings now respect scientific notation. This also includes the (int) cast operation, and the following functions: intval() (where the base is 10), settype(), decbin(), decoct(), and dechex().

## 5.mt_rand 算法修复

mt_rand() will now default to using the fixed version of the Mersenne Twister algorithm. If deterministic output from mt_srand() was relied upon, then the MT_RAND_PHP with the ability to preserve the old (incorrect) implementation via an additional
 optional second parameter to mt_srand().

## 6.rand() 别名 mt_rand() 和 srand() 别名 mt_srand()

rand() and srand() have now been made aliases to mt_rand() and mt_srand(), respectively. This means that the output for the following functions have changes: rand(), shuffle(), str_shuffle(), and array_rand().

## 7.Disallow the ASCII delete control character in identifiers

The ASCII delete control character (0x7F) can no longer be used in identifiers that are not quoted.

## 8.error_log changes with syslog value

If the error_log ini setting is set to syslog, the PHP error levels are mapped to the syslog error levels. This brings finer differentiation in the error logs in contrary to the previous approach where all the errors are logged with the notice level
 only.

## 9.在不完整的对象上不再调用析构方法

析构方法在一个不完整的对象（例如在构造方法中抛出一个异常）上将不再会被调用

## 10.call_user_func()不再支持对传址的函数的调用

call_user_func() 现在在调用一个以引用作为参数的函数时将始终失败。

## 11.字符串不再支持空索引操作符 The empty index operator is not supported for strings anymore

对字符串使用一个空索引操作符（例如 s  t   r  [ ] =        x）将会抛出一个致命错误，
 而不是静默地将其转为一个数组

## 12.ini配置项移除

下列ini配置项已经被移除：  

session.entropy_file  

session.entropy_length  

session.hash_function  

session.hash_bits_per_character

## PHP7.0 不兼容性

## 1、foreach不再改变内部数组指针

在PHP7之前，当数组通过 foreach 迭代时，数组指针会移动。现在开始，不再如此，见下面代码。

```php
$array = [0, 1, 2];
foreach ($array as &$val) {
    var_dump(current($array));
}
```

PHP5输出：  

int(1)  

int(2)  

bool(false)  

PHP7输出 ：  

int(0)  

int(0)  

int(0)

## 2、foreach通过引用遍历时，有更好的迭代特性

当使用引用遍历数组时，现在 foreach 在迭代中能更好的跟踪变化。例如，在迭代中添加一个迭代值到数组中，参考下面的代码：

```php
$array = [0];
foreach ($array as &$val) {
    var_dump($val);
    $array[1] = 1;
}
```

PHP5输出：  

int(0)  

PHP7输出：  

int(0)  

int(1)

## 3、十六进制字符串不再被认为是数字

含十六进制字符串不再被认为是数字

```php
var_dump("0x123" == "291");
var_dump(is_numeric("0x123"));
var_dump("0xe" + "0x1");
var_dump(substr("foo", "0x1"));
```

PHP5输出：  

bool(true)  

bool(true)  

int(15)  

string(2) "oo"  

PHP7输出 ：  

bool(false)  

bool(false)  

int(0)  

Notice: A non well formed numeric value encountered in /tmp/test.php on line 5  

string(3) "foo"

## 4、PHP7中被移除的函数

被移除的函数列表如下：  

call_user_func() 和 call_user_func_array()从PHP 4.1.0开始被废弃。  

已废弃的 mcrypt_generic_end() 函数已被移除，请使用mcrypt_generic_deinit()代替。  

已废弃的 mcrypt_ecb(), mcrypt_cbc(), mcrypt_cfb() 和 mcrypt_ofb() 函数已被移除。  

set_magic_quotes_runtime(), 和它的别名 magic_quotes_runtime()已被移除. 它们在PHP 5.3.0中已经被废弃,并且 在in PHP 5.4.0也由于魔术引号的废弃而失去功能。  

已废弃的 set_socket_blocking() 函数已被移除，请使用stream_set_blocking()代替。  

dl()在 PHP-FPM 不再可用，在 CLI 和 embed SAPIs 中仍可用。  

GD库中下列函数被移除：imagepsbbox()、imagepsencodefont()、imagepsextendfont()、imagepsfreefont()、imagepsloadfont()、imagepsslantfont()、imagepstext()  

在配置文件php.ini中，always_populate_raw_post_data、asp_tags、xsl.security_prefs被移除了。

## 5、new 操作符创建的对象不能以引用方式赋值给变量

new 操作符创建的对象不能以引用方式赋值给变量

```php
class C {}
$c =& new C;
```

PHP5输出：  

Deprecated: Assigning the return value of new by reference is deprecated in /tmp/test.php on line 3  

PHP7输出：  

Parse error: syntax error, unexpected ‘new’ (T_NEW) in /tmp/test.php on line 3

## 6、移除了 ASP 和 script PHP 标签

使用类似 ASP 的标签，以及 script 标签来区分 PHP 代码的方式被移除。 受到影响的标签有：<% %>、<%= %>、

## 7、从不匹配的上下文发起调用

在不匹配的上下文中以静态方式调用非静态方法， 在 PHP 5.6 中已经废弃， 但是在 PHP 7.0 中， 会导致被调用方法中未定义 $this 变量，以及此行为已经废弃的警告。

```php
class A {
    public function test() { var_dump($this); }
}
// 注意：并没有从类 A 继承
class B {
    public function callNonStaticMethodOfA() { A::test(); }
}
(new B)->callNonStaticMethodOfA();
```

PHP5输出：  

Deprecated: Non-static method A::test() should not be called statically, assuming $this from incompatible context in /tmp/test.php on line 8  

object(B)#1 (0) {  

}  

PHP7输出 ：  

Deprecated: Non-static method A::test() should not be called statically in /tmp/test.php on line 8  

Notice: Undefined variable: this in /tmp/test.php on line 3  

NULL

## 8、在数值溢出的时候，内部函数将会失败

将浮点数转换为整数的时候，如果浮点数值太大，导致无法以整数表达的情况下， 在之前的版本中，内部函数会直接将整数截断，并不会引发错误。 在 PHP 7.0 中，如果发生这种情况，会引发 E_WARNING 错误，并且返回 NULL。

## 9、JSON 扩展已经被 JSOND 取代

JSON 扩展已经被 JSOND 扩展取代。  

对于数值的处理，有以下两点需要注意的：  

第一，数值不能以点号（.）结束 （例如，数值 34. 必须写作 34.0 或 34）。  

第二，如果使用科学计数法表示数值，e 前面必须不是点号（.） （例如，3.e3 必须写作 3.0e3 或 3e3）。

## 10、INI 文件中 # 注释格式被移除

在配置文件INI文件中，不再支持以 # 开始的注释行， 请使用 ;（分号）来表示注释。 此变更适用于 php.ini 以及用 parse_ini_file() 和 parse_ini_string() 函数来处理的文件。

## 11、$HTTP_RAW_POST_DATA 被移除

不再提供 $HTTP_RAW_POST_DATA 变量。 请使用 php://input 作为替代。

## 12、yield 变更为右联接运算符

在使用 yield 关键字的时候，不再需要括号， 并且它变更为右联接操作符，其运算符优先级介于 print 和 => 之间。 这可能导致现有代码的行为发生改变。可以通过使用括号来消除歧义。

```php
echo yield -1;
// 在之前版本中会被解释为：
echo (yield) - 1;
// 现在，它将被解释为：
echo yield (-1);
yield $foo or die;
// 在之前版本中会被解释为：
yield ($foo or die);
// 现在，它将被解释为：
(yield $foo) or die;
```

## PHP 7.1.x 中废弃的特性

## 1.ext/mcrypt

mcrypt 扩展已经过时了大约10年，并且用起来很复杂。因此它被废弃并且被 OpenSSL 所取代。 从PHP 7.2起它将被从核心代码中移除并且移到PECL中。

## 2.mb_ereg_replace()和mb_eregi_replace()的Eval选项

对于mb_ereg_replace()和mb_eregi_replace()的 e模式修饰符现在已被废弃

## 弃用或废除

下面是被弃用或废除的 INI 指令列表. 使用下面任何指令都将导致 错误.  

define_syslog_variables  

register_globals  

register_long_arrays  

safe_mode  

magic_quotes_gpc  

magic_quotes_runtime  

magic_quotes_sybase  

弃用 INI 文件中以 ‘#’ 开头的注释.  

弃用函数:  

call_user_method() (使用 call_user_func() 替代)  

call_user_method_array() (使用 call_user_func_array() 替代)  

define_syslog_variables()  

dl()  

ereg() (使用 preg_match() 替代)  

ereg_replace() (使用 preg_replace() 替代)  

eregi() (使用 preg_match() 配合 ‘i’ 修正符替代)  

eregi_replace() (使用 preg_replace() 配合 ‘i’ 修正符替代)  

set_magic_quotes_runtime() 以及它的别名函数 magic_quotes_runtime()  

session_register() (使用      S       E   S  S  I  O N  超    全    部    变    量    替    代    ) s e s s i o n    u      n  r   e g i s t  e r  ( ) ( 使    用           _SESSION
 超全部变量替代)  

session_is_registered() (使用 $_SESSION 超全部变量替代)  

set_socket_blocking() (使用 stream_set_blocking() 替代)  

split() (使用 preg_split() 替代)  

spliti() (使用 preg_split() 配合 ‘i’ 修正符替代)  

sql_regcase()  

mysql_db_query() (使用 mysql_select_db() 和 mysql_query() 替代)  

mysql_escape_string() (使用 mysql_real_escape_string() 替代)  

废弃以字符串传递区域设置名称. 使用 LC_* 系列常量替代.  

mktime() 的 is_dst 参数. 使用新的时区处理函数替代.  

弃用的功能:  

弃用通过引用分配 new 的返回值.  

调用时传递引用被弃用.  

已弃用的多个特性 allow_call_time_pass_reference、define_syslog_variables、highlight.bg、register_globals、register_long_arrays、magic_quotes、safe_mode、zend.ze1_compatibility_mode、session.bug_compat42、session.bug_compat_warn 以及 y2k_compliance。


[0]: http://blog.csdn.net/fenglailea/article/details/9853645
[1]: http://blog.csdn.net/fenglailea/article/details/52717364
[2]: http://www.laruence.com/2011/10/10/2217.html
[3]: http://www.laruence.com/2011/10/10/2204.html
## 搞定PHP面试 - 函数知识点整理

来源：[https://segmentfault.com/a/1190000016350347](https://segmentfault.com/a/1190000016350347)


## 一、函数的定义
### 1. 函数的命名规则
#### **`函数名可以包含字母、数字、下划线，不能以数字开头。`** 

```php
function Func_1(){ } //合法
function func1(){ } //合法
function _func1(){ } //合法
function Func-1(){ } // 非法，不能包含 '-'
function 1_func(){ }// 非法，不能以数字开头
```

在此所说的字母是 a-z，A-Z，以及 ASCII 字符从 127 到 255（0x7f-0xff）。
因此实际上使用中文变量名也是合法的。
甚至使用中文的标点符号作为变量名都是合法的。
只是一般都不推荐这样用。
```php
// 使用中文函数名和变量名
function 面积($长, $宽){ 
    return $长 * $宽;
}

echo 面积(2, 3); // 合法，输出 '6'

// 中文符号函数名
function ？。……(){ 
    return '中文符号';
}

echo ？。……(); // 合法，输出 '中文符号'
```
#### **`函数名不区分大小写`** 

```php
function Func(){ 
    return 'Func';
}

echo func(); // 输出 'Func'
```

函数名不区分大小写，不过在调用函数的时候，使用其在定义时相同的形式是个好习惯。
### 2. 函数的特性
#### **`任何有效的 PHP 代码都有可能出现在函数内部，甚至包括其它函数和类定义。`** 

函数中包含其他函数

```php
function foo()
{
  function bar()
  {
    echo "I don't exist until foo() is called.";
  }
}

/* 现在还不能调用bar()函数，因为它还不存在 */
foo();

/* 现在可以调用bar()函数了，因为foo()函数的执行使得bar()函数变为已定义的函数 */
bar(); // 输出 'I don't exist until foo() is called.'
```

函数中包含类

```php
function foo()
{
  class Bar{
      public $a = 1;
  }
}

/* 现在还不能实例化 Bar 类，因为它还不存在 */
foo();

$bar = new Bar();

echo $bar->a; // 输出 '1'
```
#### **`PHP 中的所有函数和类都具有全局作用域，可以定义在一个函数之内而在之外调用，反之亦然。`** 

示例见上面两个例子#### **`函数无需在调用之前被定义，但是必须保证函数定义的代码能够被执行到`** 

```php
foo(); // 输出 'foo'

function foo()
{
  echo 'foo';
}
```

定义 `foo()` 函数的代码不会被执行

```php
foo(); // PHP Fatal error:  Uncaught Error: Call to undefined function foo()

if (false) {
    function foo()
    {
      echo 'foo';
    }
}
```
#### **`PHP 不支持函数重载，也不可能取消定义或者重定义已声明的函数。`** 

```php
function foo(){
    echo 0;
}

function foo() {
    echo 1;
} 
// PHP Fatal error:  Cannot redeclare foo() (previously declared
```
#### **`PHP 中可以调用递归函数`** 

```php
function recursion($a)
{
    if ($a <= 5) {
        echo "$a\n";
        recursion($a + 1);
    }
}
echo recursion(0);
```

输出

```
0
1
2
3
4
5
```

要避免递归函数／方法调用超过 100-200 层，因为可能会使堆栈崩溃从而使当前脚本终止。 无限递归可视为编程错误。递归次数过多

```php
function recursion($a)
{
    if ($a <= 300) {
        echo "$a\n";
        recursion($a + 1);
    }
}
echo recursion(0);
// PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached, aborting! 
```
#### **`PHP 的函数支持参数默认值`** 

```
function square($num = 0)
{
    echo $num * $num;
}
echo square(3), "\n";
echo square(), "\n";
```

输出

```
9
0
```
#### **`PHP 的函数支持可变数量的参数(PHP 5.6+)`** 

```php
function sum(...$numbers) {
    $acc = 0;
    foreach ($numbers as $n) {
        $acc += $n;
    }
    return $acc;
}

echo sum(1);    // 输出 1
echo sum(1, 2);    // 输出 3
echo sum(1, 2, 3); // 输出6
```
## 二、函数的参数

通过参数列表可以传递信息到函数，多个参数之间以逗号作为分隔符。参数是从左向右求值的。

PHP 支持通过值传递参数（默认），通过引用传递参数以及参数默认值。也支持可变长度的参数列表。
### 1. 通过值传递参数（默认）

默认情况下，函数参数通过值传递，即使在函数内部改变参数的值，它并不会改变函数外部的值。

```php
function addition($num)
{
    $num++;
}

$num = 1;
addition($num); 

echo $num; // 输出 1
```
### 2. 通过引用传递参数

如果希望函数可以修改传入的参数值，必须通过引用传递参数。

如果希望函数的一个参数通过引用传递，可以在定义函数时该参数的前面加上符号`&`。

```php
function addition(& $num)
{
    $num++;
}

$num = 1;
addition($num); 

echo $num; // 输出 2
```

使用引用传递参数，调用函数是只能传变量，不能直接传值。
```php
function addition(& $num)
{
    $num++;
}

addition(1); // PHP Fatal error:  Only variables can be passed by reference 
```
### 3. 参数默认值
#### **`函数可以定义 C++ 风格的标量参数默认值`** 

```php
function makecoffee($type = "cappuccino")
{
    return "Making a cup of $type.\n";
}
echo makecoffee(); // Making a cup of cappuccino.
echo makecoffee(null); // Making a cup of .
echo makecoffee("espresso"); // Making a cup of espresso.
```
#### **`PHP 还允许使用数组 array 和特殊类型 NULL 作为默认参数`** 

```php
function makecoffee($types = ["cappuccino"], $coffeeMaker = NULL)
{
    $device = is_null($coffeeMaker) ? "hands" : $coffeeMaker;
    return "Making a cup of ".join(", ", $types)." with $device.\n";
}
echo makecoffee(); // Making a cup of cappuccino with hands.
echo makecoffee(["cappuccino", "lavazza"], "teapot"); // Making a cup of cappuccino, lavazza with teapot.
```
#### **`默认值必须是常量表达式，不能是诸如变量，类成员，或者函数调用等。`** 

#### **`默认参数必须放在任何非默认参数的右侧；否则，函数将不会按照预期的情况工作。`** 

```php
function makecoffee($type = "cappuccino", $coffeeMaker)
{
    return "Making a cup of {$type} with {$coffeeMaker}.";
}

echo makecoffee(null, 'Jack'); // Making a cup of  with Jack.

echo makecoffee('Jack'); // PHP Fatal error:  Uncaught ArgumentCountError: Too few arguments to function makecoffee(), 1 passed in XX and exactly 2 expected in XX
```
### 4. 类型声明

类型声明允许函数在调用时要求参数为特定类型。 如果给出的值类型不对，那么将会产生一个错误： 在PHP 5中，这将是一个可恢复的致命错误，而在PHP 7中将会抛出一个TypeError异常。

为了指定一个类型声明，类型应该加到参数名前。这个声明可以通过将参数的默认值设为NULL来实现允许传递NULL。
#### 有效的类型声明

| 类型 | 描述 | 最小PHP版本 |
| - | - | - |
| Class/interface name | 该参数必须是给定类或接口的实例（[instanceof][0]）。 | PHP 5.0.0 |
| self | 参数必须是当前方法所在类的实例（[instanceof][0]）。只能在类和实例方法上使用。 | PHP 5.0.0 |
| array | 参数必须是数组（array） | PHP 5.1.0 |
| callable | 参数必须是有效的 [callable][2] | PHP 5.4.0 |
| bool | 参数必须是布尔值 | PHP 7.0.0 |
| float | 参数必须是浮点数 | PHP 7.0.0 |
| int | 参数必须是整数 | PHP 7.0.0 |
| string | 参数必须是字符串 | PHP 7.0.0 |


### 5. 严格类型
 **`默认情况下，如果能做到的话，PHP将会强迫错误类型的值转为函数期望的标量类型。`** 
例如，一个函数的一个参数期望是string，但传入的是integer，最终函数得到的将会是一个string类型的值。

```php
function toString(string $var)
{
    return $var;
}

var_dump(toString(1)); // string(1) "1"
```
 **`可以基于每一个文件开启严格模式。`** 
在严格模式中，只有一个与类型声明完全相符的变量才会被接受，否则将会抛出一个TypeError。 唯一的一个例外是可以将integer传给一个期望float的函数。

可以使用 [declare][3] 语句和strict_types 声明来启用严格模式： **`启用严格模式同时也会影响返回值类型声明。`** 

```php
declare(strict_types=1);

function toString(string $var)
{
    return $var;
}

toString(1); // PHP Fatal error:  Uncaught TypeError: Argument 1 passed to toString() must be of the type string, integer given
```

将integer传给一个期望float的函数

```php
declare(strict_types=1);

function toFloat(float $var)
{
    return $var;
}

$int = 1;
var_dump($int); // int(1)
var_dump(toFloat($int)); // double(1)
```

严格类型适用于在启用严格模式的文件内的函数调用，而不是在那个文件内声明的函数。一个没有启用严格模式的文件内调用了一个在启用严格模式的文件中定义的函数，那么将会遵循调用者的偏好（弱类型），而这个值将会被转换。严格类型仅用于标量类型声明，也正是因为如此，才需要PHP 7.0.0 或更新版本，因为标量类型声明也是在这个版本中添加的。

### 6. 可变数量的参数列表（PHP 5.5+）

PHP 在用户自定义函数中支持可变数量的参数列表。在 PHP 5.6 及以上的版本中，由 ... 语法实现；在 PHP 5.5 及更早版本中，使用函数 [func_num_args()][4]，[func_get_arg()][5]，和 [func_get_args()][6] 实现。
####`...`（in PHP 5.6+）

```php
function sum(...$numbers) {
    $acc = 0;
    foreach ($numbers as $n) {
        $acc += $n;
    }
    return $acc;
}

echo sum(1);    // 输出 1
echo sum(1, 2);    // 输出 3
echo sum(1, 2, 3); // 输出6
```
####`func_num_args()`，`func_get_arg()`和`func_get_args()`（PHP 5.5）

```php
function sum() {
    $acc = 0;
    foreach (func_get_args() as $n) {
        $acc += $n;
    }
    return $acc;
}

echo sum(1);    // 输出 1
echo sum(1, 2);    // 输出 3
echo sum(1, 2, 3); // 输出6
```
## 三、函数返回值
### 1. 函数返回值的特性
 **`值通过使用可选的返回语句（return）返回`** 
 **`可以返回包括数组和对象的任意类型。`** 
 **`返回语句会立即中止函数的运行，并且将控制权交回调用该函数的代码行。`** 
 **`函数不能返回多个值，但可以通过返回一个数组来得到类似的效果。`** 
 **`如果省略了 return，则返回值为 NULL。`** 

```php
function sum($a, $b) {
    $a + $b;
}

var_dump(sum(1, 2)); // NULL
```
### 2. return语句

如果在一个函数中调用 return 语句，将立即结束此函数的执行并将它的参数作为函数的值返回。
 **`return 也会终止 eval() 语句或者脚本文件的执行。`** 

```php
$expression = 'echo "我在return之前"; return; echo "我在return之后";';

eval($expression); // 输出“我在return之前”
```

如果在全局范围中调用 return，则当前脚本文件中止运行。
如果在主脚本文件中调用 return，则脚本中止运行。
 **`如果当前脚本文件是被 include 或者 require 的，则控制交回调用文件。此外，return 的值会被当作 include  或者 require 调用的返回值。`** 

a.php

```php
<?php
$b = require 'b.php';
$c = include 'c.php';

echo $b;
echo $c;

return;
echo '我在a.php return之后';
```

b.php

```php
<?php
return "我是b.php\n";
echo '我在b.php return之后';
```

c.php

```php
<?php
return "我是c.php\n";
echo '我在c.php return之后';
```

运行 a.php，输出结果为：

```
我是b.php
我是c.php
```

如果当前脚本文件是在 php.ini 中的配置选项 [auto_prepend_file][7] 或者 [auto_append_file][8] 所指定的，则此脚本文件中止运行。

Note: 注意既然 return 是语言结构而不是函数，因此其参数没有必要用括号将其括起来。通常都不用括号，实际上也应该不用，这样可以降低 PHP 的负担。Note: 如果没有提供参数，则一定不能用括号，此时返回 NULL。如果调用 return 时加上了括号却又没有参数会导致解析错误。

Note: 当用引用返回值时永远不要使用括号，这样行不通。只能通过引用返回变量，而不是语句的结果。如果使用 return ($a); 时其实不是返回一个变量，而是表达式 ($a) 的值（当然，此时该值也正是 $a 的值）。

### 3. 函数的引用返回

从函数返回一个引用，必须在函数声明和指派返回值给一个变量时，都使用引用运算符`&`

```php
function &myFunc()
{
    static $b = 10;
    return $b;
}

$a = myFunc();
$a = 100;
echo myFunc(); // 10;

$a = &myFunc();
$a = 100;
echo myFunc(); // 100;
```
## 四、可变函数

PHP 支持可变函数的概念。
这意味着如果一个变量名后有圆括号，PHP 将寻找与变量的值同名的函数，并且尝试执行它。
可变函数可以用来实现包括回调函数，函数表在内的一些用途。

可变函数不能用于例如 echo，print，unset()，isset()，empty()，include，require 以及类似的语言结构。
需要使用自己的包装函数来将这些结构用作可变函数。

```php
function foo() {
    return "I'm foo()\n";
}

// 使用 echo 的包装函数
function echoit($string)
{
    echo $string;
}

$func = 'foo';
echo $func();   // This calls foo()，输出“I'm foo()”

$func = 'echoit';
$func($func);  // This calls echoit()，输出“echoit”


$func = 'echo';

echo($func); // 输出“echo”

$func($func); // PHP Fatal error:  Uncaught Error: Call to undefined function echo()
```
 **`可以用可变函数的语法来调用一个对象的方法`** 

```php
class Foo
{
    function variable()
    {
        $name = 'bar';
        $this->$name(); // This calls the Bar() method
    }

    function bar()
    {
        echo "This is Bar";
    }
}

$foo = new Foo();
$funcName = "variable";
$foo->$funcName();   // This calls $foo->variable()，输出“This is Bar”
```
 **`当调用静态方法时，函数调用要比静态属性优先`** 

```php
class Foo
{
    static $variable = 'static property';
    static function variable()
    {
        echo 'Method Variable called';
    }
}

echo Foo::$variable; // 输出 'static property'.
$variable = "variable";
Foo::$variable();  // 调用 $foo->variable()，输出“Method Variable called”
```
 **`可以调用存储在标量内的 [callable][2]（PHP 5.4+）`** 

```php
class Foo
{
    static function bar()
    {
        echo "bar\n";
    }

    function baz()
    {
        echo "baz\n";
    }
}

$func = ["Foo", "bar"];
$func(); // 输出 "bar"

$func = [new Foo, "baz"];
$func(); // 输出 "baz"

$func = "Foo::bar";
$func(); // 自 PHP 7.0.0 版本起会输出 "bar"; 在此之前的版本会引发致命错误
```
## 五、匿名函数
### 1. 匿名函数的特性

匿名函数（Anonymous functions），也叫 **`闭包函数`** （closures），允许 临时创建一个没有指定名称的函数。
最经常用作回调函数（[callback][10]）参数的值。

匿名函数目前是通过 [Closure][11] 类来实现的。
#### **`闭包函数也可以作为变量的值来使用。`** 

PHP 会自动把此种表达式转换成内置类 Closure 的对象实例。把一个 closure 对象赋值给一个变量的方式与普通变量赋值的语法是一样的，最后也要加上分号。

```php
$greet = function($name)
{
    printf("Hello %s \n", $name);
};

$greet('World'); // Hello World
$greet('PHP'); // Hello PHP
```
#### **`闭包可以从父作用域中继承变量。`** 

任何此类变量都应该用 use 语言结构传递进去。 
PHP 7.1 起，不能传入此类变量： superglobals、 $this 或者和参数重名。

```php
$message = 'hello';

// 没有 "use"
$example = function () {
    var_dump($message);
};
echo $example(); // PHP Notice:  Undefined variable: message

// 继承 $message
$example = function () use ($message) {
    var_dump($message);
};
echo $example(); // string(5) "hello"

// 继承的变量的值来自于函数定义时，而不是调用时
$message = 'world';
echo $example(); // string(5) "hello"
```
#### **`通过引用继承父作用域中的变量，可以将父作用域更改的值反映在函数调用中`** 

```php
$message = 'hello';

// 通过引用继承
$example = function () use (&$message) {
    var_dump($message);
};
echo $example(); // string(5) "hello"

// 父作用域更改的值反映在函数调用中
$message = 'world';
echo $example(); // string(5) "world"
```
#### **`闭包函数也可以接受常规参数`** 

```php
$message = 'world';

$example = function ($arg) use ($message) {
    var_dump($arg . ' ' . $message);
};
$example("hello"); // string(11) "hello world"
```
## 六、实例分析
### 例1

```php
//声明函数swap，作为下面匿名函数的回调函数
function swap(&$x, &$y)
{
    $temp = $x;
    $x = $y;
    $y = $temp;

    return;
}

//call_user_func调用的回调函数
function add($a, $b)
{
    return $a + $b;
}

//匿名函数，即不声明函数名称而使用一个变量来代替函数声明
$fuc = function ($fucName) {
    $x = 1;
    $y = 2;
    //调用回调函数
    $fucName($x, $y);
    echo 'x=' . $x . ',y=' . $y, "\n";

    //与$fucName($x, $y)相同效果
    //这里无法调用swap方法，因为swap方法是对参数引用传值
    //call_user_func与call_user_func_array都无法调用引用传参形式的函数
    echo call_user_func('add', $x ,$y);
};

//调用 $fuc
$fuc('swap');
```

输出：

```
x=2,y=1
3
```
### 例2

```php
$var1 = 5;
$var2 = 10;

function foo(&$my_var)
{
    global $var1;
    $var1 += 2;
    $var2 = 4;
    $my_var += 3;
    return $var2;
}

$my_var = 5;
echo foo($my_var). "\n";
echo $my_var. "\n";
echo $var1;
echo $var2;
$bar = 'foo';
$my_var = 10;
echo $bar($my_var). "\n";
```
 **`第14行调用`foo()`方法`** 

```php
function foo(&$my_var)
{
    global $var1; // 5
    $var1 += 2; // 7
    $var2 = 4; // 4
    $my_var += 3; // 8
    return $var2; // 4
}

$my_var = 5;
echo foo($my_var). "\n"; // 4
```
 **`第14行到第17行输出的值分别为：`** 

```php
echo foo($my_var). "\n"; // 4
echo $my_var. "\n"; // 8
echo $var1; // 7
echo $var2; // 10
```
 **`第20行再次调用`foo()`方法`** 

```php
function foo(&$my_var)
{
    global $var1; // 7
    $var1 += 2; // 9
    $var2 = 4; // 4
    $my_var += 3; // 13
    return $var2; // 4
}

$bar = 'foo';
$my_var = 10;
echo $bar($my_var). "\n"; // foo($my_var)  => 4
```

[0]: http://php.net/manual/zh/language.operators.type.php
[1]: http://php.net/manual/zh/language.operators.type.php
[2]: http://php.net/manual/zh/language.types.callable.php
[3]: http://www.php.net/manual/zh/control-structures.declare.php
[4]: http://php.net/manual/zh/function.func-num-args.php
[5]: http://php.net/manual/zh/function.func-get-arg.php
[6]: http://php.net/manual/zh/function.func-get-args.php
[7]: http://www.php.net/manual/zh/ini.core.php#ini.auto-prepend-file
[8]: http://www.php.net/manual/zh/ini.core.php#ini.auto-append-file
[9]: http://php.net/manual/zh/language.types.callable.php
[10]: http://www.php.net/manual/zh/language.pseudo-types.php#language.types.callback
[11]: http://www.php.net/manual/zh/class.closure.php
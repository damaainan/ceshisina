## 搞定PHP面试 - 变量知识点整理

来源：[https://segmentfault.com/a/1190000016291982](https://segmentfault.com/a/1190000016291982)


## 一、变量的定义
### 1. 变量的命名规则
#### **`变量名可以包含字母、数字、下划线，不能以数字开头。`** 

```php
$Var_1 = 'foo'; // 合法
$var1 = 'foo'; // 合法
$_var1 = 'foo'; // 合法
$Var-1 = 'foo'; // 非法，不能包含 -
$1_var = 'foo'; // 非法，不能以数字开头
```

在此所说的字母是 a-z，A-Z，以及 ASCII 字符从 127 到 255（0x7f-0xff）。
因此实际上使用中文变量名也是合法的。
甚至使用中文的标点符号作为变量名都是合法的。
只是一般都不推荐这样用。
```php
$姓名 = 'foo'; // 合法
$？。…… = 'foo'; // 合法。
```
#### **`变量名区分大小写`** 

```php
$var = 'Bob';
$Var = 'Joe';
echo "$var, $Var";      // 输出 "Bob, Joe"
```
#### **`$this 是一个特殊的变量，它不能被赋值`** 

```php
$this = 'foo'; // Fatal error: Cannot re-assign $this
```
### 2. 变量的赋值

变量的引用赋值与传值赋值详情传送门：[变量的引用赋值与传值赋值][0]
#### **`传值赋值`** 

变量默认总是传值赋值。那也就是说，当将一个表达式的值赋予一个变量时，整个原始表达式的值被赋值到目标变量。
这意味着，例如，当一个变量的值赋予另外一个变量时，改变其中一个变量的值，将不会影响到另外一个变量。

```php
$foo = 'Bob';     // 将 'Bob' 赋给 $foo
$bar = $foo;      // 通过 $foo 传值赋值给 $bar
$bar = 'Jack';        // 修改 $bar 变量
echo $foo;          // $foo 的值未改变，依然是 'Bob'
```
#### **`引用赋值`** 

引用赋值，就是新的变量简单的引用（换言之，“成为其别名” 或者 “指向”）了原始变量。
改动新的变量将影响到原始变量，反之亦然。

使用引用赋值，只需要将一个`&`符号加到将要赋值的变量前（源变量）

```php
$foo = 'Bob';        // 将 'Bob' 赋给 $foo
$bar = &$foo;     // 通过 $bar 引用 $foo
$bar = 'Jack';     // 修改 $bar 变量
echo $foo;           // $foo 的值也被修改为 'Jack'
```

只有有名字的变量才可以引用赋值
```php
$foo = 25;
$bar = &$foo;      // 合法的赋值
$bar = &(24 * 7);  // 非法; 引用没有名字的表达式

function test()
{
   return 25;
}

$bar = &test();    // 非法
```
### 3.变量的初始化

虽然在 PHP 中并不需要初始化变量，但对变量进行初始化是个好习惯。
#### **`未初始化的变量的默认值`** 

未初始化的变量具有其类型的默认值。


* 布尔类型的变量默认值是`FALSE`
* 整形和浮点型变量默认值是`0`
* 字符串型变量默认值是空字符串`""`
* 数组变量的默认值是空数组`array()`


### 4. 可变变量

可变变量是指变量的变量名可以动态的设置和使用。

一个可变变量获取了一个普通变量的值作为这个可变变量的变量名。在下面的例子中 hello 使用了两个美元符号（`$`）以后，就可以作为一个可变变量的变量了。

```php
$a = 'hello';

$$a = 'world';
```

这时，两个变量都被定义了：`$a`的内容是“hello”并且 $hello 的内容是“world”。

因此，以下语句：

```php
echo "$a ${$a}";
```

与以下语句输出完全相同的结果：

```php
echo "$a $hello";
```

它们都会输出：hello world。
#### **`可变变量用于数组`** 

要将可变变量用于数组，必须解决一个模棱两可的问题。
这就是当写下`$$a[1]`时，解析器需要知道是想要`$a[1]`作为一个变量呢，还是想要`$ $a`作为一个变量并取出该变量中索引为`[1]`的值。
解决此问题的语法是，对第一种情况用`${$a[1]}`，对第二种情况用`${$a}[1]`。
#### **`可变变量用于类`** 

类的属性也可以通过可变属性名来访问。可变属性名将在该调用所处的范围内被解析。
例如，对于`$foo->$bar`表达式，则会在本地范围来解析 $bar 并且其值将被用于 $foo 的属性名。
对于`$bar`是数组单元时也是一样。

也可使用花括号`{}`来给属性名清晰定界。
最有用是在属性位于数组中，或者属性名包含有多个部分或者属性名包含有非法字符时（例如来自`json_decode()`或`SimpleXML`）。

将一个json格式的字符串转换成php对象：

```php
$string = '{"os-version":"10.3.1","1day":24}';
$obj = json_decode($string);
print_r($obj);
```

输出结果：

```
stdClass Object
(
    [os-version] => 10.3.1
    [1day] => 24
)
```

此时若想访问对象$obj 中的`os-version`属性或`1day`属性，若直接使用`$obj->os-version`，`$obj->1day`访问的话一定会报错。

正确的访问方式：

```php
echo $obj->{"os-version"};
echo '
';
echo $obj->{"1day"};
```

输出结果：

```
10.3.1
24
```
## 二、变量的作用域和静态变量
### 1. 变量的作用域

变量的作用域也称变量的范围，即它定义的上下文背景（也就是它的生效范围）。
 **`php变量的范围跨度同样包含了include和require引入的文件。`** 
#### **`局部变量`** 

在用户自定义函数中，将引入一个局部函数范围。任何用于函数内部的变量的作用域都将被限制在局部函数范围内。例如：

```php
$outer = 'str'; /* 全局范围 */

function myfunc()
{
    echo $outer; /* 对局部范围变量的引用 */
}

myfunc();
```

这个脚本不会有任何输出，因为 echo 语句引用了一个局部版本的变量`$outer`，而且在这个范围内，它并没有被赋值。
#### 全局变量
 **`global关键字`** 

```php
$outer = 'str'; // 全局

function myfunc()
{
    global $outer;
    echo $outer; // 局部
}
myfunc();
```

这个脚本会输出`str`。在函数中使用 **`global关键字`** 声明了全局变量`$a`和`$b`之后，对任一变量的所有引用都会指向其全局版本。对于一个函数能够声明的全局变量的最大个数，PHP 没有限制。
 **`[$GLOBALS][1]超全局数组`** 
`$GLOBALS`— 引用全局作用域中可用的全部变量

```php
$outer = 'str'; // 全局

function myfunc()
{
    echo $GLOBALS['outer'];
}
myfunc();
```

这个脚本会输出`str`。`$GLOBALS`是一个关联数组，每一个变量为一个元素，键名对应变量名，值对应变量的内容。`$GLOBALS`之所以在全局范围内存在，是因为`$GLOBALS`是一个超全局变量。
 **`[超全局变量][2]`** 

PHP 中的许多预定义变量都是“超全局的”，这意味着它们在一个脚本的全部作用域中都可用。在函数或方法中无需执行 global $variable; 就可以访问它们。

这些超全局变量是：

[$GLOBALS][3] — 超全局变量是在全部作用域中始终可用的内置变量
[$_SERVER][4] — 服务器和执行环境信息
[$_GET][5] — HTTP GET 变量
[$_POST][6] — HTTP POST 变量
[$_FILES][7] — HTTP 文件上传变量
[$_COOKIE][8]  — HTTP Cookies
[$_SESSION][9] — Session 变量
[$_REQUEST][10] — HTTP Request 变量。默认情况下包含了`$_GET`，`$_POST`和`$_COOKIE`的数组。
[$_ENV][11] — 环境变量
### 2. 静态变量

变量范围的另一个重要特性是静态变量（static variable）。
静态变量仅在局部函数域中存在，但当程序执行离开此作用域时，其值并不会消失。
#### **`静态变量的特点`** 

1.使用static关键字修饰
2.静态声明是在编译时解析的
3.仅初始化一次
4.初始化时需要赋值
5.每次执行函数该值会保留
6.static修饰的变量是局部的，仅在函数内部有效
7.可以记录函数的调用次数，从而可以在某些条件下终止递归。

```php
function myFunc()
{
    static $a = 1;
    echo $a++;
}
myFunc(); // 1
myFunc(); // 2
myFunc(); // 3
```

变量`$a`仅在第一次调用`myFunc()`函数时被初始化，之后每次调用`myFunc()`函数都会输出 $a 的值并加1。
#### **`声明静态变量时不能用表达式的结果对其赋值`** 

```php
function foo(){
    static $int = 0;        // 正确
    static $int = 1+2;       // 错误  (使用表达式的结果赋值)
    static $int = sqrt(121);      // 错误  (使用表达式的结果赋值)

    echo $int++;
}
```
#### **`静态变量与递归函数`** 

静态变量提供了一种处理递归函数的方法。
递归函数是一种调用自己的函数。写递归函数时要小心，因为可能会无穷递归下去。必须确保有充分的方法来中止递归。
以下这个简单的函数递归计数到 10，使用静态变量 $count 来判断何时停止：

```php
function test()
{
    static $count = 0;

    $count++;
    echo $count;
    if ($count < 10) {
        test();
    }
    $count--;
}
```
### 3. 实例分析

写出如下程序的输出结果

```php
$count = 5;
function get_count()
{
    static $count;
    return $count++;
}

echo $count;
++$count;

echo get_count();
echo get_count();
```
#### **`第8行`echo $count;`输出`5``** 

#### **`第9行`++$count;`，此时`$count`的值为`6``** 

#### **`第11行`echo get_count();`，第一次调用`get_count()`函数`** 

```php
function get_count()
{
    // 声明静态变量 $count，由于为赋值，所以其值为 NULL
    static $count;
    // $count++，先返回 $count 的值，后自增。因此，返回值为 NULL。
    // NULL 自增后的值为 1，因此，自增后的 $count = 1
    return $count++;
}
```

第一次调用`get_count()`的返回值为 NULL，而`echo NULL;`什么都不会输出。
#### **第12行`echo get_count();`，第二次调用`get_count()`函数** 

```php
function get_count()
{
    // 第二次调用时，该行不会执行
    static $count;
    // 此前 $count = 1，$count++，先返回 $count 的值，后自增。因此，返回值为 1。
    return $count++;
}
```

第一次调用`get_count()`的返回值为 1，而`echo get_count();`输出`1`。
#### 整个程序的输出结果为`51`


[0]: https://segmentfault.com/a/1190000016182556
[1]: http://php.net/manual/zh/reserved.variables.globals.php
[2]: http://php.net/manual/zh/language.variables.superglobals.php
[3]: http://php.net/manual/zh/reserved.variables.globals.php
[4]: http://php.net/manual/zh/reserved.variables.server.php
[5]: http://php.net/manual/zh/reserved.variables.get.php
[6]: http://php.net/manual/zh/reserved.variables.post.php
[7]: http://php.net/manual/zh/reserved.variables.files.php
[8]: http://php.net/manual/zh/reserved.variables.cookies.php
[9]: http://php.net/manual/zh/reserved.variables.session.php
[10]: http://php.net/manual/zh/reserved.variables.request.php
[11]: http://php.net/manual/zh/reserved.variables.environment.php
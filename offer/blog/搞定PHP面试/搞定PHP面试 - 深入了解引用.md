## 搞定PHP面试 - 深入了解引用

来源：[https://segmentfault.com/a/1190000016373107](https://segmentfault.com/a/1190000016373107)


### 1. 什么是引用
 **`在 PHP 中引用是指用不同的名字访问同一个变量内容。`** 
PHP 中的变量名和变量内容是不一样的， 因此同样的内容可以有不同的名字。
最接近的比喻是 Unix 的文件名和文件本身——变量名是目录条目，而变量内容则是文件本身。 **`引用可以被看作是 Unix 文件系统中的硬链接。`** 

PHP 中的引用并不像 C 的指针：例如你不能对他们做指针运算。 **`引用并不是实际的内存地址，而是符号表别名。`** 
### 2. 引用的特性
#### **`PHP 的引用允许用两个变量来指向同一个内容。`** 

```php
$a =& $b;
```

这意味着 $a 和 $b 指向了同一个变量。

$a 和 $b 在这里是完全相同的，这并不是 $a 指向了 $b 或者相反，而是 $a 和 $b 指向了同一个地方。#### **`如果具有引用的数组被复制，其值不会解除引用。将数组传值给函数也是如此。`** 

```php
$a = 'a';

$arr1 = [
    'a' => $a,
    'b' => &$a, // $arr1['b'] 与 $a 指向同一个变量
];

// 将 $arr1 传值赋值给 $arr2
$arr2 = $arr1;

print_r($arr2); // $arr2 的值为 ['a' => 'a', 'b' => 'a']

// 修改 $a 的值为 'b'
$a = 'b';

print_r($arr2); // $arr2 的值为 ['a' => 'a', 'b' => 'b']


function foo($arr){
    // 将 $arr['b'] 的值改为 'c';
    $arr['b'] = 'c';
}

echo $a; // $a 的值为 'b'

// 将 $arr1 传入函数
foo($arr1);

echo $a; // $a 的值为 'c'
```
#### **`如果对一个未定义的变量进行引用赋值、引用参数传递或引用返回，则会自动创建该变量。`** 

```php
// 定义函数 foo()，通过引用传递参数
function foo(&$var) { }

foo($a); // 创建变量 $a，值为 NULL
var_dump($a); // NULL

foo($b['b']); // 创建数组 $b = ['b' => NULL]
var_dump(array_key_exists('b', $b)); // bool(true)

$c = new StdClass;
foo($c->d); // 创建对象属性 $c->d = NULL
var_dump(property_exists($c, 'd')); // bool(true)
```
#### **`如果在一个函数内部给一个声明为 global 的变量赋于一个引用，该引用只在函数内部可见。可以通过使用 $GLOBALS 数组避免这一点。`** 

```php
$var1 = 'var1';
$var2 = 'var2';

function global_references($use_globals)
{
    global $var1, $var2;
    if (!$use_globals) {
        $var2 = & $var1; // $var2 只在函数内部可见
    } else {
        $GLOBALS["var2"] = & $var1; // $GLOBALS["var2"]在全球范围内也可见
    }
}

global_references(false);
echo "var2 is set to '$var2'\n"; // var2 is set to 'var2'
global_references(true);
echo "var2 is set to '$var2'\n"; // var2 is set to 'var1'
```

可以把`global $var;`当成是`$var =& $GLOBALS['var'];`的简写。从而将其它引用赋给 $var 只改变了本地变量的引用。
#### **`在 foreach 语句中给一个具有引用的变量赋值，被引用的对象也被改变。`** 

```php
$ref = 0;
$row = & $ref;
foreach ([1, 2, 3] as $row) {
    // do something
}
echo $ref; // 3 - 遍历数组的最后一个元素
```
### 3. 引用传递
#### **`可以将一个变量通过引用传递给函数，这样该函数就可以修改其参数的值。`** 

```php
function foo(&$var)
{
    $var++;
}

$a=5;
foo($a);

echo $a; // 6
```

注意在函数调用时没有引用符号——只有函数定义中有。光是函数定义就足够使参数通过引用来正确传递了。
#### **`可以通过引用传递的内容：`** 


* 变量
* 从函数中返回的引用


#### **`通过引用传递变量`** 

```php
function foo(&$var)
{
    $var++;
}

$a=5;
foo($a);

echo $a; // 6
```
#### **`通过引用传递从函数中返回的引用`** 

```php
function foo(&$var)
{
    $var++;
    echo $var; // 6
}

function &bar()
{
    $a = 5;
    return $a;
}

foo(bar());
```
#### **`不能通过引用传递函数、表达式、值等`** 

```php
function foo(&$var)
{
    $var++;
}

function bar() // 注意，这个函数不返回引用
{
    $a = 5;
    return $a;
}

foo(bar()); // 自 PHP 5.0.5 起导致致命错误，自 PHP 5.1.1 起导致严格模式错误，自 PHP 7.0 起导致 notice 信息

foo($a = 5); // 表达式，不是变量。PHP Notice:  Only variables should be passed by reference

foo(5); // PHP Fatal error:  Only variables can be passed by reference 
```
### 4. 引用返回

当你想要使用一个函数来找到一个引用应该被绑定的变量时，可以使用引用返回。
不要用返回引用来增加性能，引擎足够聪明，可以自己进行优化。仅在有合理的技术原因时才返回引用！

```php
class Foo {
    public $value = 42;

    public function &getValue() {
        return $this->value;
    }
}

$foo = new Foo;
// $myValue 是 $obj->value 的引用.
$myValue = &$foo->getValue();
// 将 $foo->value 修改为 2
$foo->value = 2;
echo $myValue;  // 2
```

与参数引用传递不同，引用返回必须在两个地方都用 & 符号 —— 指出返回的是一个引用，而不是通常的一个拷贝，同样也指出 $myValue 是作为引用的绑定，而不是通常的赋值。 **`引用返回只能返回变量。`** 如果试图这样从函数返回引用：`return intval($this->value);`，将会报错，因为函数在试图返回一个表达式的结果而不是一个引用的变量。只能从函数返回引用变量——没别的方法。

```php
class Foo {
    public $value = 42;

    public function &getValue() {
        return intval($this->value); // PHP Notice:  Only variable references should be returned by reference
    }
}

$foo = new Foo;
// $myValue 是 $obj->value 的引用.
$myValue = &$foo->getValue(); 
```
### 5. 取消引用
 **`当 unset 一个引用，只是断开了变量名和变量内容之间的绑定。这并不意味着变量内容被销毁了。`** 

```php
$a = 1;
$b = & $a;
unset($a);

echo $b; // 1
```
### 6. 发现

许多 PHP 的语法结构是通过引用机制实现的，所以上述有关引用绑定的一切也都适用于这些结构。
#### **`global 引用`** 

当用`global $var`声明一个变量时实际上是在函数内部建立了一个到全局变量的引用。也就是说这样做的效果是相同的：

```php
global $var;

$var =& $GLOBALS["var"];
```

这意味着，`unset $var`不会 unset 掉全局变量`$GLOBALS["var"]`。
#### **`$this`** 

在一个对象的方法中，$this 永远是调用它的对象的引用。

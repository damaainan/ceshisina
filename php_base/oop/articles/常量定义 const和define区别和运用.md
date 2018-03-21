## [PHP]常量定义: const和define区别和运用; 附constant解释

来源：[https://segmentfault.com/a/1190000009559436](https://segmentfault.com/a/1190000009559436)


## 前言

常量是一个简单值的标识符（名字）。如同其名称所暗示的，在脚本执行期间该值不能改变（除了所谓的魔术常量，它们其实不是常量）。常量默认为大小写敏感。通常常量标识符总是大写的。
 **`在 PHP 5.3.0 之前可以用 define() 函数来定义常量`** 。
 **`在 PHP 5.3.0 以后，可以使用 const 关键字在类定义的外部定义常量，先前版本const 关键字只能在类（class）中使用`** 。
 **`一个常量一旦被定义，就不能再改变或者取消定义`** 。
常量只能包含标量数据（boolean，integer，float 和 string），不能是表达式。 
可以定义 resource 常量，但应尽量避免，因为会造成不可预料的结果。

可以简单的通过指定其名字来取得常量的值，与变量不同，不应该在常量前面加上 $ 符号。
如果常量名是动态的，也可以用函数constant() 来获取常量的值。
用get_defined_constants() 可以获得所有已定义的常量列表。
常量和变量有如下不同：
常量前面没有美元符号（$）； 
常量可以不用理会变量的作用域而在任何地方定义和访问； 
常量一旦定义就不能被重新定义或者取消定义； 
常量的值只能是标量。
## const实验

```php
<?php

const CONST_A = 'A';
const CONST_B = 'B';

echo CONST_A;
echo "\n--------\n";
echo CONST_B;
?>
```
### 输出结果

A 
----
B


-----

```php
<?php

const CONST_A = 'A';
if(true){ //这里不管是什么条件 const其实都不可以在条件里出现，无法通过编译解释
    const CONST_B = 'B';
}
const CONST_FOUR = 1+2; //无法通过编译解释，const必须是标量不能是表达式

echo CONST_A;
echo "\n--------\n";
echo CONST_B;
?>
```
### 输出结果

Parse error: syntax error, unexpected 'const' (T_CONST) in /usercode/file.php on line 5

```php
<?php

class fooClass {
    const bar = 'pro';
    public function print(){
        echo self::bar;
    } 
}

echo fooClass::bar;

$classname = "fooClass";
echo $classname::bar. "\n"; // 自 5.3.0 起

$foo = new fooClass();
echo "\n----\n";
echo $foo->print();
echo "\n----\n";
echo $foo::bar;  // 自 5.3.0 起
?>
```
### 输出结果

pro
----
pro
----
pro

## define实验

```php
<?php

define('CONST_A','A');
define('CONST_B','B');
define('CONST_FOUR', 1+3);

echo CONST_A;
echo "\n--------\n";
echo CONST_B;
echo "\n--------\n";
echo CONST_FOUR;
?>
```
### 输出结果

A 
----
B
----
4


-----

```php
<?php

define('CONST_A','A');
if(true){
    define('CONST_B','B');
}
if(false){
    define('CONST_C','C');
}

echo CONST_A;
echo "\n--------\n";
echo CONST_B;
echo "\n--------\n";
echo CONST_C;
?>
```
### 输出结果

A
--------
B
--------
CONST_C
 **`define不可以出现在类定义之中`** 
## 附加constant函数

之前一直不理解constant有什么作用，先看下官方的介绍
 **`通过 name 返回常量的值。当你不知道常量名，却需要获取常量的值时，constant() 就很有用了。也就是常量名储存在一个变量里，或者由函数返回常量名。该函数也适用`** 
下面用一个简单的代码来实验

```php
<?php

define("MAXSIZE", 100);

echo MAXSIZE; //output: 100
echo constant("MAXSIZE"); //output: 100


interface bar {
    const test = 'foobar!';
}

class foo {
    const test = 'foobar!';
}

$const = 'test';

var_dump(constant('bar::'. $const)); //output: "foobar!"
var_dump(constant('foo::'. $const)); //output: "foobar!"
?>
```
## 总结

define可用在条件判断中，不成立的条件中，定义的不生效，成功定义后全局可用，可是表达式赋值
const不可用在条件判断中，不过可定义在类中，不可表达式赋值，必须是标量

通用原则
----

* 所有代码块都需要用`大括号 {}`包围（包括`单行`的if语句）

```actionscript
if(condition) {
    // run 1 line of code
} else {
    // run 1 line of code
}
```

* 缩进使用tabs而不是空格
* 一行代码的长度`不建议`有硬限制；软限制`必须`为120个字符，`建议`每行代码80个字符或者更少。
* 在纯PHP代码源文件末尾`必须`不使用`?>`标签
* 文件编码

> 请调整您的编辑器文件编码为`UTF-8`，并关闭`UTF-8 BOM`的功能。

> 请不要使用windows自带的记事本编辑项目文件。

* 缩进

> 详细的代码缩进会在后面提到，

> 这里需要注意的是，项目中的代码缩进使用的是`制表符(tab)`，而不是`4个空格(space)`，请务必调整。

* UNIX编码规范

> 如果你正在编写一个php文件，那么根据UNIX的C语言编码规范，必须留出最后一个空行。比如

```php
<?php
//this is a test file
echo 'hello';
<---这行留空
```

编码风格
----

左大括号最好和代码在同一行
====

AS3:

```actionscript
class MyClass {
  //class def
}
 
public function myFunc(x:int, y:int):void {
   //do stuff
}
 
if(true) {
   //do stuff
}
```
PHP:

```php
class MyClass {
  //class def
}
 
public function myFunc($x, $y) {
   //do stuff
}
 
if(TRUE) {
   //do stuff
}
```

Protected/Private成员变量应在类头部定义，并且以m_开始
====
AS3:

```actionscript
class MyClass {
    /** State for the avatar */
    protected var m_state:int;
}
```
PHP:

```php
class MyClass {
    /** State for the avatar */
    protected $m_state;
}
```


变量命名应清楚易懂尽量避免使用单个字母做变量名。
===

AS3:

```actionscript
for (var itemIndex:int = 0 ; itemIndex < MAX_ITEMS : itemIndex++) {
    m_items[itemIndex].invoke();
}
```

变量名和函数名应使用小写的camelCase
===
AS3:

```actionscript
public function myFavoriteFunction();
private var m_someLongName;
```
PHP:

```php
public function myFavoriteFunction();
private $m_someLongName;
```

常量应使用大写
===

AS3:

```actionscript
public const MYCONST:int=10;
```
PHP:

```php
define('MYCONST',10);
const MYCONST=10;
```

类名应使用对应的大写CamelCase
===

AS3:

```actionscript
RotateableDecoration.as
```

PHP:

```php
RotateableDecoration.class.php
```

根目录下的php文件应使用小写

```
addneighbor.php
index.php
neighbors.php
seedsforhaiti.php
```

最佳实践
===
* 方法名应描述方法的用途，如果它做了不止一件事，最好分成多个方法。
* 尽量保证函数行数不要太多(~100最多). 如果多于这个数量，那么很可能做了多个事情，最好把它分开。
* 在单独的PHP文件末尾不使用?>标签，避免可能带来whitespace问题。
* 条件语句中避免出现赋值语句, 容易与等值判断混淆, 且不易于阅读。


注释
====
文件的开头应已一小段简介来说明目的和使用方法
AS3/PHP:

```actionscript
/**
 *  Copyright (C) 2014 Game Network Inc.
 *  Avatar.as
 *
 *  Base class for characters in the world, uses a state machine to keep track of character movements.
 */
```

所有的函数在定义前应添加ASDOC/phpDOC
===
AS3:

```actionscript
/**
 * This function adds two numbers
 *
 * @param x X position
 * @param y Y position
 *
 * @return  Sum of the two integers
 */
public function add(x:int, y:int):int {
    return x+y;
}
```
PHP:

```php
/**
 * This function adds two numbers
 *
 * @param integer $x    X position
 * @param integer $y    Y position
 *
 * @return integer  Sum of the two integers
 */
public function add($x, $y) {
    return $x+$y;
}
```

所有的成员变量在定义前应添加ASDOC/phpDOC
===

AS3:

```actionscript

public class MyClass {
    /** This is the only type of comment ASDOC recognizes */
    private var m_myVar:String;
}
```
PHP:

```php
class MyClass {
   /** Some comment about the following variable */
   private $m_myVar;
}
```
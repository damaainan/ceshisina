## 小哥哥，PHP7 新特性了解一下吧

来源：[http://www.phpyc.com/new-features-php-7-example/](http://www.phpyc.com/new-features-php-7-example/)

时间 2018-05-02 18:28:43

PHP 7 版本的有一些令人兴奋的功能，让我们一起来了解一下吧

## 严格模式

将`strict_types=1`放置文件的顶部，会申明我们这个源文件使用严格模式来解析

```php
declare(strict_types=1);
```
#### 在下面的例子中，默认使用严格模式

## 变量类型申明

在 PHP 7 中，我们可以申明一个函数接收什么样的类型的参数

如果某些参数类型不匹配，将会抛出一个致命错误

```php
function sum(int $a, int $b){
    return $a + $b;
}
echo sum(2, 7); //9
echo sum(2, 'sas'); //Error: Uncaught TypeError: Argument 2 passed to sum() must be of the type integer, string given
```
## 返回类型申明

可以和参数类型一样去定义函数的返回类型

```php
function sum(int $a, int $b) : int{
    return $a + $b;
}
```
## 空合并操作符

PHP 7 引入了新的空合并操作符。如果不为空，则返回左数，否则返回右数。

```php
$userId = $_GET['user_id'] ?? 0;
```
## 太空船运算符

PHP 7 新增加的太空船运算符（组合比较符）用于比较两个表达式`$a`和`$b`，如果`$a`小于、等于或大于`$b`时，它分别返回`-1`、`0`或`1`。

```php
$compareResult = $a <=> $b
 
if $a < $b it returns -1
 
if $a = $b it returns 0
 
if $a > $b it returns 1
```
## 随机字节

PHP 7 增加了新的伪随机字节生成器（CSPRNG）。如果你想生成随机字节，可以使用`random_bytes()`方法，它采用单个参数，即随机字符串的长度

```php
$rndByte = random_bytes(6); // 6 是长度
var_dump(bin2hex($rndByte));// "be2a4c1c3da4"
```
## 随机整数

您也可以生成随机整数而不是字节。`random_int`函数有助于生成随机数字。它带有两个参数`min`和`max`，它们定义了随机数的边界

```php
$rndInt = random_int(2,10);
var_dump($rndInt);//int(9)
```
## define 支持数组定义

PHP 7 中可以定义常量数组

```php
define('USERS', [
    'Enda',
    'En',
    'da'
]);
echo USERS[2];//da
```
## Unicode 支持

PHP 7 中支持直接打印 Unicode

```php
echo "\u{aa}";
```

## session_start 支持数组

PHP 7 修改`session_start()`方法将`params`作为数组

```php
session_start([
    'username' => 'enda',
    'email' => 'enda@xxx.com',
]);
```
## intdiv 整除

PHP 7 新增加了`intdiv()`函数，接收两个参数，返回值为第一个参数除于第二个参数的值并取整

```php
echo intdiv(13, 3); //4
```



## ［翻译］PHP中define()和const定义常量的区别

来源：[https://segmentfault.com/a/1190000012021630](https://segmentfault.com/a/1190000012021630)

在PHP中可以通过define()和const两种方式定义常量
可是在开发中我们应该什么时候用define()定义常量，什么时候用const定义常量？ 这两种方式定义常量的主要区别是什么？

从5.3版本开始PHP有两种方法来[定义常量][0]，使用`const`关键字或者是使用`define()`方法：

```php
const FOO = 'BAR';
define('FOO', 'BAR');

```

两者之间最大的区别在于`const`是在编译时定义常量，而`define()`方法是在运行时定义常量。



* `const`不能用在if语句中，`defne()`能用在if语句中。

```php
 if(...) {
     const FOO = 'BAR';//错误
 }
 if(...) {
     define('FOO', 'BAR');//正确
 }
```
`define()`的一个常用场景是先判断常量是否已经定义再定义常量:

```php
 if(defined('FOO')) {
     define('FOO', 'BAR')
 }
 
```



* `const`定义常量时，值只能是静态标量（数字, 字符串,`true`，`false`,`null`), 而`define()`方法可以把任意表达式的值用作常量的值。从PHP5.6开始`const`也允许把表达式用作常量的值了。

```php
const BIT_5 = 1 << 5; //PHP5.6后支持，之前的PHP版本不支持
define('BIT_5', 1 << 5);// 所有PHP版本都支持

```



* `const`只允许简单的常量名，而`define()`可以把任何表达式的值用作常量名

```php
for ($i = 0; $i < 32; $i++) {
    define('BIT_' . $i, 1 << $i);
}

```



* `const`定义的常量常量名是大小写敏感的，而传递`true`给`define()`方法的第三个参数时可以定义大小写不敏感的常量。

```php
define('FOO', 'BAR', true);
echo FOO; //BAR
echo foo; //BAR

```




上面列举的都是`const`相较`define()`而言的一些缺点或者不灵活的地方，下面我们看一下为什么我个人推荐用`const`而不是`define()`来定义常量(除非要在上述列举的场景中定义常量)。



* `const`具有更好的可读性，`const`是语言结构而不是函数，而且与在类中定义类常量的形式保持一致。


* `const`在当前的命名空间中定义常量， 而`define()`要实现类似效果必须在定义时传递完整的命名空间名称：

```php
namespace A\B\C;
//To define the constant A\B\C\FOO:
const FOO = 'BAR';
define('A\B\C\FOO', 'BAR');

```



* `const`从PHP5.6版本开始可以把数组用作常量值，而`define()`在PHP7.0版本开始才支持把数组用作常量值。

```php
const FOO = [1, 2, 3];// valid in PHP 5.6
define('FOO', [1, 2, 3]);// invalid in PHP 5.6, valid in PHP 7.0

```



* 因为`const`是语言结构并且在编译时定义常量所以`const`会比`define()`稍稍快一些。

众所周知PHP在用`define()`定义了大量的常量后会影响效率。 人们设置发明了[apc_load_constants()][1]和[hidef][2]来绕过`define()`导致的效率问题。




最后，`const`还能被用于在类和接口中定义常量，`define()`只能被用于在全局命名空间中定义常量：

```php
class FOO
{
    const BAR = 2;// 正确
}

class Baz
{
    define('QUX', 2)// 错误
}

```

总结：
  除非要在if分支里定义常量或者是通过表达式的值来命名常量， 其他情况( **`即使是只是简单的为了代码的可读性`** )都推荐用`const`替代`define()`。

[0]: http://php.net/manual/zh/phpuage.constants.syntax.php
[1]: http://php.net/apc_load_constants
[2]: https://pecl.php.net/package/hidef
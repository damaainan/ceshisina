# Use PHP7

 时间 2017-12-10 09:41:05  

原文[https://segmentfault.com/a/1190000012357409][1]



### 说明

目前 [RC3][3] 中， [PHP 7.2][4] 计划于11月30日发布。新版本将提供新的特性，功能和改进，使我们能够编写更好的代码。在这篇文章中，我将介绍一些PHP 7.2中最有趣的语言特性。 

### 参数类型声明

从PHP 5开始，我们可以在函数的声明中指定期望传递的参数类型。如果给定的值是不正确的类型，那么PHP会抛出一个错误。 [参数类型声明][5] （也称为类型提示）指定预期要传递给函数或类方法的变量的类型。 

例如下面这个例子：

    class MyClass {
        public $var = 'Hello World';
    }
    
    $myclass = new MyClass;
    
    function test(MyClass $myclass){
        return $myclass->var;
    }
    
    echo test($myclass);

在这个代码中，测试函数需要MyClass的一个实例。不正确的数据类型将导致以下致命错误：

    Fatal error: Uncaught TypeError: Argument 1 passed to test() must be an instance of MyClass, string given, called in /app/index.php on line 12 and defined in /app/index.php:8

由于PHP 7.2 [类型提示][6] 可以与对象数据类型一起使用，并且这种改进允许声明通用对象作为函数或方法的参数。这里是一个例子： 

    class MyClass {
        public $var = '';
    }
    
    class FirstChild extends MyClass {
        public $var = 'My name is Jim';
    }
    class SecondChild extends MyClass {
        public $var = 'My name is John';
    }
    
    $firstchild = new FirstChild;
    $secondchild = new SecondChild;
    
    function test(object $arg) {
        return $arg->var;
    }
    
    echo test($firstchild);
    
    echo test($secondchild);

在这个例子中，我们调用了两次测试函数，每次调用都传递一个不同的对象。在以前的PHP版本中这是不可能的。

Docker命令

在Docker中使用PHP 7.0和PHP 7.2测试类型提示

### 对象返回类型声明

如果参数类型声明指定函数参数的预期类型，则返回类型声明指定返回值的预期类型。

[返回类型声明][7] 指定了一个函数应该返回的变量的类型。 

从PHP 7.2开始，我们被允许为对象数据类型使用返回类型声明。这里是一个例子：

    class MyClass {
        public $var = 'Hello World';
    }
    
    $myclass = new MyClass;
    
    function test(MyClass $arg) : object {
        return $arg;
    }
    
    echo test($myclass)->var;

以前的PHP版本会导致以下致命错误：

    Fatal error: Uncaught TypeError: Return value of test() must be an instance of object, instance of MyClass returned in /app/index.php:10

当然，在PHP 7.2中，这个代码回应了“Hello World”。

### 参数类型加宽

PHP目前不允许子类和它们的父类或接口之间的参数类型有任何差异。这意味着什么？

考虑下面的代码：

    <?php
    class MyClass {
        public function myFunction(array $myarray) { /* ... */ }
    }
    
    class MyChildClass extends MyClass {
        public function myFunction($myarray) { /* ... */ }
    }

这里我们省略了子类中的参数类型。在PHP 7.0中，这段代码会产生以下警告：

    Warning: Declaration of MyChildClass::myFunction($myarray) should be compatible with MyClass::myFunction(array $myarray) in %s on line 8

自 [PHP 7.2][8] 以来，我们被允许在不破坏任何代码的情况下 [省略子类中的类型][8] 。这个建议将允许我们升级类来在库中使用类型提示，而不需要更新所有的子类。 

### 在列表语法中尾随逗号

数组中最后一项之后的尾随逗号是PHP中的 [有效语法][9] ， [有时][10] 为了方便追加新项目并避免由于缺少逗号而导致解析错误，鼓励使用 [该语法][10] 。自PHP 7.2以来，我们被 [允许][11] 在 [分组命名空间中使用尾随逗号][12] 。 

请参阅 [列表语法][11] 中的 [尾随逗号][11] 以便在此RFC处获得更近的视图以及一些代码示例。 

### 安全改进

#### 密码哈希中的Argon2

[Argon2][13] 是一个强大的哈希算法，被选为2015年密码哈希大赛的冠军，PHP 7.2将它作为 [Bcrypt][14] 算法的安全替代品。 

新的PHP版本引入了 [PASSWORD_ARGON2I][15] 常量，现在可以在 [password_*][16] 函数中使用它： 

    password_hash('password', PASSWORD_ARGON2I);

与仅使用一个成本因素的Bcrypt不同，Argon2需要三个成本因素区分如下：

* 甲存储器成本它定义了应散列期间被消耗KIB的数（默认值是1 << 10，或1024 KIB，或1 MIB）
* 甲时间成本定义散列算法的迭代次数（默认为2）
* 一个并行因子，用于设置散列期间将使用的并行线程数（缺省值为2）

三个新的常量定义了默认的成本因素：

* PASSWORD_ARGON2_DEFAULT_MEMORY_COST
* PASSWORD_ARGON2_DEFAULT_TIME_COST
* PASSWORD_ARGON2_DEFAULT_THREADS

这里是一个例子：

    $options = ['memory_cost' => 1<<11, 'time_cost' => 4, 'threads' => 2];
    password_hash('password', PASSWORD_ARGON2I, $options);

有关更多信息，请参阅 [Argon2密码哈希][15] 。 

#### Libsodium作为PHP Core的一部分

从版本7.2开始，PHP将 [钠库][17] 纳入核心。 [Libsodium][18] 是一个跨平台和跨语言的库，用于加密，解密，签名，密码散列等等。以前通过 [PECL][19] 提供。有关Libsodium功能的文档列表，请参阅库快速 [入门指南][20] 。另请参阅PHP 7.2：将现代加密技术添加到其标准库中的第一种编程语言。 

### 弃用

以下是PHP 8.0 不推荐使用的 [函数和功能列表][21] ，不晚于PHP 8.0： 

该__autoload功能已被取代由 [spl_autoload_register][22] 在PHP 5.1。现在，在编译期间遇到弃用通知。 

在$ php_errormsg中时，抛出一个非致命错误变量是在局部范围内创建。由于应该使用PHP 7.2 [error_get_last][23] 和 [error_clear_last][24] 。 

create_function()允许创建一个带有生成函数名称的函数，一系列参数和正文代码作为参数提供。由于安全问题和性能不佳，已将其标记为已弃用，并鼓励使用附件。

已将 mbstring.func_overload ini设置为非零值已被标记为已弃用。

（unset）cast 是一个总是返回null的表达式，被认为是无用的。

如果提供了第二个参数， [parse_str()][25] 会将查询字符串解析为数组，如果不使用，则解析为本地符号表。由于出于安全原因 [不鼓励][26] 在函数范围内设置变量，使用不带第二个参数的 parse_str() 将抛出弃用通知。 

gmp_random() 被认为是平台相关的，将被弃用。使用 [gmp_random_bits()][27] 和 [gmp_random_rage()][28] 来代替。 

each() 被用来像 foreach() 那样迭代一个数组，但是 foreach() 有几个原因是可取的，包括快10倍。现在，在循环的第一个呼叫中将会抛弃。

所述断言函数检查给定的断言，并采取适当的行动，如果结果是FALSE。带有字符串参数的 assert() 的使用现在已被弃用，因为它会打开一个 RCE 漏洞。该 [zend.assertion][29] INI 选项可用于防止断言表达式的评价。 

$ errcontext是包含生成错误时存在的局部变量的数组。它作为使用 [set_error_handler()][30] 函数设置的错误处理程序的最后一个参数传递。 

## 结尾说明

翻译自 [https://kinsta.com/blog/php-7-2/][31]

Script Maker Day Day Up!

[0]: /sites/3uEjY
[1]: https://segmentfault.com/a/1190000012357409

[3]: http://php.net/archive/2017.php#id2017-09-28-2
[4]: https://wiki.php.net/todo/php72
[5]: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
[6]: https://wiki.php.net/rfc/object-typehint
[7]: http://php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration
[8]: https://wiki.php.net/rfc/parameter-no-type-variance
[9]: http://php.net/manual/en/function.array.php
[10]: https://framework.zend.com/manual/2.4/en/ref/coding.standard.html#associative-arrays
[11]: https://wiki.php.net/rfc/list-syntax-trailing-commas
[12]: http://php.net/manual/en/language.namespaces.importing.php#language.namespaces.importing.group
[13]: https://en.wikipedia.org/wiki/Argon2
[14]: https://en.wikipedia.org/wiki/Bcrypt
[15]: https://wiki.php.net/rfc/argon2_password_hash
[16]: http://php.net/manual/en/ref.password.php
[17]: https://wiki.php.net/rfc/libsodium
[18]: https://www.gitbook.com/book/jedisct1/libsodium/details
[19]: https://pecl.php.net/package/libsodium
[20]: https://paragonie.com/book/pecl-libsodium/read/01-quick-start.md
[21]: https://wiki.php.net/rfc/deprecations_php_7_2
[22]: http://php.net/spl_autoload_register
[23]: http://php.net/error_get_last
[24]: http://php.net/manual/en/function.error-clear-last.php
[25]: http://php.net/parse_str
[26]: http://php.net/manual/en/security.globals.php
[27]: http://php.net/manual/en/function.gmp-random-bits.php
[28]: http://php.net/manual/en/function.gmp-random-range.php
[29]: http://php.net/manual/en/ini.core.php#ini.zend.assertions
[30]: http://php.net/manual/it/function.set-error-handler.php
[31]: https://kinsta.com/blog/php-7-2/
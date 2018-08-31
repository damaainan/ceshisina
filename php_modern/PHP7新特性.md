## [PHP7新特性小结](https://segmentfault.com/a/1190000012438552)

![][0]

#### 说明

PHP 7使用新的Zend Engine 3.0将应用程序性能提高近两倍，内存消耗比PHP 5.6高出50％。它允许服务更多的并发用户，而不需要任何额外的硬件。PHP 7是考虑到今天的工作负载而设计和重构的。

#### PHP新功能总结

* 改进的性能 - 将PHPNG代码合并到PHP7中，速度是PHP 5的两倍。
* 降低内存消耗 - 优化的PHP 7使用较少的资源。
* 标量类型声明 - 现在可以强制执行参数和返回类型。
* 一致的64位支持 - 对64位体系结构机器的一致支持。
* 改进了异常层次 - 异常层次得到了改进
* 许多致命的错误转换为例外 - 例外范围增加，涵盖许多致命的错误转换为例外。
* 安全随机数发生器 - 增加新的安全随机数发生器API。
* 已弃用的SAPI和扩展已删除 - 各种旧的和不受支持的SAPI和扩展从最新版本中删除。
* 空合并运算符（？） - 添加了新的空合并运算符。
* 返回和标量类型声明 - 支持所添加的返回类型和参数类型。
* 匿名类 - 支持匿名添加。
* 零成本断言 - 支持零成本断言增加。

#### 标量类型声明

在PHP 7中，引入了一个新的特性，即标量类型声明。标量类型声明有两个选项

* 强制 - 强制是默认模式，不需要指定。
* 严格 - 严格的模式已经明确暗示。

功能参数的以下类型可以使用上述模式强制执行

* float
* int
* bool
* string
* interfaces
* array
* callable

> 强制模式

```php
    <?php
       // Coercive mode
       function sum(int ...$ints) {
          return array_sum($ints);
       }
       print(sum(2, '3', 4.1)); //9
    ?>
```
> 严格模式

```php
    <?php
       // Strict mode
       declare(strict_types=1);
       function sum(int ...$ints) {
          return array_sum($ints);
       }
       print(sum(2, '3', 4.1)); //Fatal error: Uncaught TypeError: Argument 2 passed to sum() must be of the type integer, string given, ...
    ?>
```
#### 返回类型声明

> 有效的返回类型

```php
    <?php
       declare(strict_types = 1);
       function returnIntValue(int $value): int {
          return $value;
       }
       print(returnIntValue(5));
    ?>
```

> 无效返回类型

```php
    <?php
       declare(strict_types = 1);
       function returnIntValue(int $value): int {
          return $value + 1.0;
       }
       print(returnIntValue(5));//Fatal error: Uncaught TypeError: Return value of returnIntValue() must be of the type integer, float returned.
    ?>
```
#### 空合并运算符

在PHP 7中，引入了一个新的特性，即空合并运算符（??）。它用来替代与isset（）函数结合的三元操作。该空如果它存在，而不是空合并运算符返回第一个操作数; 否则返回第二个操作数。

```php
    <?php
       // fetch the value of $_GET['user'] and returns 'not passed'
       // if username is not passed
       $username = $_GET['username'] ?? 'not passed';
       print($username);
       print("<br/>");
    
       // Equivalent code using ternary operator
       $username = isset($_GET['username']) ? $_GET['username'] : 'not passed';
       print($username);
       print("<br/>");
       // Chaining ?? operation
       $username = $_GET['username'] ?? $_POST['username'] ?? 'not passed';
       print($username);
    
       // output
       //not passed
    ?>
```
#### 飞船运算符

它用来比较两个表达式。当第一个表达式分别小于，等于或大于第二个表达式时，它返回-1,0或1。字符串比较ASCII

    //integer comparison
       print( 1 <=> 1);print("<br/>");
       print( 1 <=> 2);print("<br/>");
       print( 2 <=> 1);print("<br/>");
    
    // output
        0
        -1
        1

#### 常量数组

使用define（）函数定义数组常量。在PHP 5.6中，只能使用const关键字来定义它们。

```php
    <?php
    //define a array using define function
       define('animals', [
          'dog',
          'cat',
          'bird'
       ]);
       print(animals[1]);
    // output
      // cat  
    ?>
```
#### 匿名类

现在可以使用新类来定义匿名类。匿名类可以用来代替完整的类定义。

```php
    <?php
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
             print($msg);
          }
       });
    
       $app->getLogger()->log("My first Log Message");
    ?>
    //output
    
    // My first Log Message
```
    

#### Closure类

Closure :: call（）方法被添加为一个简短的方式来临时绑定一个对象作用域到一个闭包并调用它。与PHP5的bindTo相比，它的性能要快得多。

> 在PHP 7之前

```php
    <?php
       class A {
          private $x = 1;
       }
    
       // Define a closure Pre PHP 7 code
       $getValue = function() {
          return $this->x;
       };
    
       // Bind a clousure
       $value = $getValue->bindTo(new A, 'A');
    
       print($value());
       //output
       // 1
    ?>
```
> PHP 7+

```php
    <?php
       class A {
          private $x = 1;
       }
    
       // PHP 7+ code, Define
       $value = function() {
          return $this->x;
       };
    
       print($value->call(new A));
       //output
       // 1
    ?>
```
#### 过滤unserialize

PHP 7引入了过滤的unserialize（）函数，以便在对不可信数据上的对象进行反序列化时提供更好的安全性。它可以防止可能的代码注入，并使开发人员能够对可以反序列化的类进行白名单。

```php
    <?php
       class MyClass1 {
          public $obj1prop;   
       }
       class MyClass2 {
          public $obj2prop;
       }
    
       $obj1 = new MyClass1();
       $obj1->obj1prop = 1;
       $obj2 = new MyClass2();
       $obj2->obj2prop = 2;
    
       $serializedObj1 = serialize($obj1);
       $serializedObj2 = serialize($obj2);
    
       // default behaviour that accepts all classes
       // second argument can be ommited.
       // if allowed_classes is passed as false, unserialize converts all objects into __PHP_Incomplete_Class object
       $data = unserialize($serializedObj1 , ["allowed_classes" => true]);
    
       // converts all objects into __PHP_Incomplete_Class object except those of MyClass1 and MyClass2
       $data2 = unserialize($serializedObj2 , ["allowed_classes" => ["MyClass1", "MyClass2"]]);
    
       print($data->obj1prop);
       print("<br/>");
       print($data2->obj2prop);
    
       //output
       // 1
       // 2
    ?>
```
#### IntlChar

在PHP7中，增加了一个新的IntlChar类，它试图揭示额外的ICU功能。这个类定义了一些静态方法和常量，可以用来处理Unicode字符。在使用这个课程之前，你需要安装Intl扩展。

```php
    <?php
       printf('%x', IntlChar::CODEPOINT_MAX);
       print (IntlChar::charName('@'));
       print(IntlChar::ispunct('!'));
    
       //output
       // 10ffff
       // COMMERCIAL AT
       // true
    ?>
```
#### CSPRNG

在PHP 7中，引入了两个新的函数来以跨平台的方式生成密码安全的整数和字符串。

* random_bytes（） - 生成密码安全的伪随机字节。
* random_int（） - 生成密码安全的伪随机整数。

```php
    <?php
       $bytes = random_bytes(5);
       print(bin2hex($bytes));
    
       //output
       54cc305593
    
      print(random_int(100, 999));
      print("");
      print(random_int(-1000, 0));
    
      //output
      // 614
      // -882
    ?>
```
#### 使用声明

从PHP7开始，可以使用单个use语句从相同的命名空间导入类，函数和常量，而不是使用多个use语句。

```php
    <?php
       // Before PHP 7
       use com\tutorialspoint\ClassA;
       use com\tutorialspoint\ClassB;
       use com\tutorialspoint\ClassC as C;
    
       use function com\tutorialspoint\fn_a;
       use function com\tutorialspoint\fn_b;
       use function com\tutorialspoint\fn_c;
    
       use const com\tutorialspoint\ConstA;
       use const com\tutorialspoint\ConstB;
       use const com\tutorialspoint\ConstC;
    
       // PHP 7+ code
       use com\tutorialspoint\{ClassA, ClassB, ClassC as C};
       use function com\tutorialspoint\{fn_a, fn_b, fn_c};
       use const com\tutorialspoint\{ConstA, ConstB, ConstC};
    
    ?>
```
#### 整数部分

PHP 7引入了一个新的函数intdiv（），它对它的操作数进行整数除法，并将除法运算返回为int。

```php
    <?php
       $value = intdiv(10,3);
       var_dump($value);
       print(" ");
       print($value);
    
       //output
       // int(3)
       // 3
    ?>
```
#### 会话选项

session_start（）函数接受来自PHP7 + 的一系列选项来覆盖php.ini中设置的会话配置指令。这些选项支持session.lazy_write，默认情况下，它会导致PHP在会话数据发生更改时覆盖任何会话文件。

添加的另一个选项是read_and_close，它表示应该读取会话数据，然后应该立即关闭会话。例如，将session.cache_limiter设置为private，并使用以下代码片段将标志设置为在读取完毕后立即关闭会话。

```php
    <?php
       session_start([
          'cache_limiter' => 'private',
          'read_and_close' => true,
       ]);
    ?>
```
#### 弃用

> PHP 4样式构造函数是与它们定义的类具有相同名称的方法，现在已被弃用，并且将来将被删除。如果PHP 4的构造函数是类中定义的唯一构造函数，则PHP 7将发出E_DEPRECATED。实现__construct（）方法的类不受影响。

```php
    <?php
       class A {
          function A() {
             print('Style Constructor');
          }
       }
    ?>
```
> 对非静态方法的静态调用已被弃用，并可能在将来被删除

```php
    <?php
       class A {
          function b() {
             print('Non-static call');
          }
       }
       A::b();
       // Deprecated: Non-static method A::b() should not be called statically in...Non-static call
    ?>
```
> password_hash（）函数的salt选项已被弃用，所以开发人员不会生成自己的（通常是不安全的）盐。当开发人员不提供盐时，函数本身会生成密码安全的盐，因此不再需要定制盐的生成。> 该capture_session_meta SSL上下文选项已被弃用。SSL元数据现在通过stream_get_meta_data（）函数使用。#### 错误处理

从PHP 7开始，错误处理和报告已经改变。而不是通过PHP 5使用的传统错误报告机制来报告错误，现在大多数错误都是通过抛出错误异常来处理的。与异常类似，这些错误异常会一直冒泡，直到它们到达第一个匹配的catch块。如果没有匹配的块，则使用set_exception_handler（）安装的默认异常处理程序将被调用。如果没有默认的异常处理程序，那么异常将被转换为致命错误，并将像传统的错误一样处理。

由于错误层次结构不是从Exception扩展的，所以使用catch（Exception $ e）{...}块来处理PHP 5中未捕获的异常的代码将不会处理这样的错误。catch（Error $ e）{...}块或set_exception_handler（）处理程序是处理致命错误所必需的。

```php
    <?php
       class MathOperations {
          protected $n = 10;
    
          // Try to get the Division by Zero error object and display as Exception
          public function doOperation(): string {
             try {
                $value = $this->n % 0;
                return $value;
             } catch (DivisionByZeroError $e) {
                return $e->getMessage();
             }
          }
       }
    
       $mathOperationsObj = new MathOperations();
       print($mathOperationsObj->doOperation());
    
       // output
       // Modulo by zero
    ?>
```
### 结尾说明

> 2017已经接近尾声，崭新的2018即将来临，在这个知识日新月异的时代，温故而知新。script maker!

[0]: https://segmentfault.com/img/remote/1460000012438557
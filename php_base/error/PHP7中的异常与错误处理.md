# PHP7中的异常与错误处理

 时间 2017-12-05 00:00:00  

原文[https://novnan.github.io/PHP/throwable-exceptions-and-errors-in-php7/][1]


过去的 PHP，处理致命错误几乎是不可能的。致命错误不会调用由 `set_error_handler()` 设置的处理方式，而是简单的停止脚本的执行。 

在 PHP7 中，当致命错误和可捕获的错误( `E_ERROR` 和 `E_RECOVERABLE_ERROR` )发生时会抛出异常，而不是直接停止脚本的运行。对于某些情况，比如内存溢出，致命错误则仍然像之前一样直接停止脚本执行。在 PHP7 中， **一个未捕获的异常也会是一个致命错误** 。这意味着在 PHP5.x 中致命错误抛出的异常未捕获，在 PHP7 中也是致命错误。 

注意：其他级别的错误如 `warning` 和 `notice` ，和之前一样不会抛出异常，只有 `fatal` 和 `recoverable` 级别的错误会抛出异常。 

从 `fatal` 和 `recoverable` 级别错误抛出的异常并非继承自 `Exception` 类。这种分离是为了防止现有 PHP5.x 的用于停止脚本运行的代码也捕获到错误抛出的异常。 `fatal` 和 `recoverable` 级别的错误抛出的异常是一个全新分离出来的类 `Error` 类的实例。跟其他异常一样， `Error` 类异常也能被捕获和处理，同样允许在 `finally` 之类的块结构中运行。 

## Throwable 

为了统一两个异常分支， `Exception` 和 `Error` 都实现了一个全新的接口： `Throwable`  
PHP7 中新的异常结构如下：

```
    interface Throwable
        |- Exception implements Throwable
            |- ...
        |- Error implements Throwable
            |- TypeError extends Error
            |- ParseError extends Error
            |- ArithmeticError extends Error
                |- DivisionByZeroError extends ArithmeticError
            |- AssertionError extends Error
```

如果在 PHP7 的代码中定义了 `Throwable` 类，它将会是如下这样： 

```php
    interface Throwable
    {
        public function getMessage():string;
        public function getCode():int;
        public function getFile():string;
        public function getLine():int;
        public function getTrace():array;
        public function getTraceAsString():string;
        public function getPrevious():Throwable;
        public function __toString():string;
    }
```

这个接口看起来很熟悉。 `Throwable` 规定的方法跟 `Exception` 几乎是一样的。唯一不同的是 `Throwable::getPrevious()` 返回的是 `Throwable` 的实例而不是 `Exception` 的。 `Exception` 和 `Error` 的构造函数跟之前 `Exception` 一样，可以接受任何 `Throwable` 的实例。 

`Throwable` 可以用于 `try/catch` 块中捕获 `Exception` 和 `Error` 对象(或是任何未来可能的异常类型)。记住捕获更多特定类型的异常并且对之做相应的处理是更好的实践。然而在某种情况下我们想捕获任何类型的异常(比如日志或框架中错误处理)。 在 PHP7 中，要捕获所有的应该使用 `Throwable` 而不是 `Exception` 。 

```php
    try {
        // Code that may throw an Exception or Error.
    } catch (Throwable $t) {
        // Handle exception
    }
```

用户定义的类不能实现 `Throwable` 接口。做出这个决定一定程度上是为了预测性和一致性——只有 `Exception` 和 `Error` 的对象可以被抛出。此外，异常需要携带对象在追溯堆栈中创建位置的信息，而用户定义的对象不会自动的有参数来存储这些信息。 

`Throwable` 可以被继承从而创建特定的包接口或者添加额外的方法。一个继承自 `Throwable` 的接口只能被 `Exception` 或 `Error` 的子类来实现。 

```php
    interface MyPackageThrowableextends Throwable{}
    
    class MyPackageExceptionextends Exceptionimplements MyPackageThrowable{}
    
    throw new MyPackageException();
```

## Error 

事实上，PHP5.x 中所有的错误都是 `fatal` 或 `recoverable` 级别的错误，在 PHP7 中都能抛出一个 `Error` 实例。跟其他任何异常一样， `Error` 对象可以使用 try/catch 块来捕获。 

```php
    $var = 1;
    try {
        $var->method(); // Throws an Error object in PHP 7.
    } catch (Error $e) {
        // Handle error
    }
```

通常情况下，之前的致命错误都会抛出一个基本的 `Error` 类实例，但某些错误会抛出一个更具体的 `Error` 子类： `TypeError` 、 `ParseError` 以及 `AssertionError` 。 

### TypeError 

当函数参数或返回值不符合声明的类型时， `TypeError` 的实例会被抛出。 

```php
    function add(int $left, int $right)
    {
        return $left + $right;
    }
    
    try {
        $value = add('left', 'right');
    } catch (TypeError $e) {
        echo $e->getMessage(), "\n";
    }
    
    //Argument 1 passed to add() must be of the type integer, string given
```

### ParseError 

当 `include/require` 文件或 `eval()` 代码存在语法错误时， `ParseError` 会被抛出。 

```php
    try {
        require 'file-with-parse-error.php';
    } catch (ParseError $e) {
        echo $e->getMessage(), "\n";
    }
```

### ArithmeticError 

`ArithmeticError` 在两种情况下会被抛出。一是位移操作负数位。二是调用 `intdiv()` 时分子是 `PHP_INT_MIN` 且分母是 `-1` (这个使用除法运算符的表达式： PHP_INT_MIN / -1 ，结果是浮点型)。 

```php
    try {
    $value = 1 << -1;
    catch (ArithmeticError $e) {
        echo $e->getMessage();//Bit shift by negative number
    }
```

### DevisionByZeroError 

当 `intdiv()` 的分母是 0 或者取模操作 (%) 中分母是 0 时， `DivisionByZeroError` 会被抛出。注意在除法运算符 (/) 中使用 0 作除数（也即xxx/0这样写）时只会触发一个 warning，这时候若分子非零结果是 INF，若分子是 0 结果是 NaN。 

```php
    try {
        $value = 1 % 0;
    } catch (DivisionByZeroError $e) {
        echo $e->getMessage();//Modulo by zero
    }
```

### AssertionError 

当 `assert()` 的条件不满足时， `AssertionError` 会被抛出。 

```php
    ini_set('zend.assertions', 1);
    ini_set('assert.exception', 1);
    
    $test = 1;
    
    assert($test === 0);
    
    //Fatal error: Uncaught AssertionError: assert($test === 0)
```

只有断言启用并且是设置 `ini` 配置的 `zend.assertions = 1` 和 `assert.exception = 1` 时， `assert()` 才会执行并抛 `AssertionError` 。 

## 在你的代码中使用 Error 

用户可以通过继承 `Error` 来创建符合自己层级要求的 `Error` 类。这就形成了一个问题：什么情况下应该抛出 `Exception` ，什么情况下应该抛出 `Error` 。 

`Error` 应该用来表示需要程序员关注的代码问题。从 PHP 引擎抛出的 `Error` 对象属于这些分类，通常都是代码级别的错误，比如传递了错误类型的参数给一个函数或者解析一个文件发生错误。 `Exception` 则应该用于在运行时能安全的处理，并且另一个动作能继续执行的情况。 

由于 `Error` 对象不应该在运行时被处理，因此捕获 `Error` 对象也应该是不频繁的。一般来说， `Error` 对象仅被捕获用于日志记录、执行必要的清理以及展示错误信息给用户。 

## 编写代码支持 PHP5.x 和 PHP7 的异常 

为了在同样的代码中捕获任何 PHP5.x 和 PHP7 的异常，可以使用多个 `catch` ，先捕获 `Throwable` ，然后是 `Exception` 。当 PHP5.x 不再需要支持时，捕获 `Exception` 的 `catch` 块可以移除。 

```php
    try {
        // Code that may throw an Exception or Error.
    } catch (Throwable $t) {
        // Executed only in PHP 7, will not match in PHP 5.x
    } catch (Exception $e) {
        // Executed only in PHP 5.x, will not be reached in PHP 7
    }
```

不幸的是，处理异常的函数中的类型声明不容易确定。当 `Exception` 用于函数参数类型声明时，如果函数调用时候能用 `Error` 的实例，这个类型声明就要去掉。当 PHP5.x 不需要被支持时，类型声明则可以还原为 `Throwable` 。


[1]: https://novnan.github.io/PHP/throwable-exceptions-and-errors-in-php7/

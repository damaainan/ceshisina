## 实例讲解PHP异常

来源：[http://www.jianshu.com/p/d5530126ba1e](http://www.jianshu.com/p/d5530126ba1e)

时间 2018-05-17 22:54:46

 
## PHP异常的概念
 
PHP中的异常与错误是两个不同的概念，异常是指程序运行与预期不一致，需要由开发人员手动抛出。
 
```php
error_reporting(-1);
$num = NULL;
try {
    $num = 3/0;
} catch (Exception $e) {
    echo $e->getMessage();
}
```
 
程序报`Warning: Division by zero`错误，而不是异常
 
要想程序抛出异常，需要由开发人员手动抛出：
 
```php
error_reporting(-1);
$num = NULL;
try {
    $num1 = 3;
    $num2 = 0;
    if ($num2 == 0) {
        throw new Exception("0不能作为除数"); // 手动抛出异常
    }
} catch (Exception $e) { // 捕获异常
    echo $e->getMessage();
}
```
 
  
   
   
![][0]
 
  
 
PHP
 
 
 
## 内置异常类
 
PHP有一些内置的异常类，能够自动捕获异常
 
```php
header('content-type:text/html;charset=utf-8');
try {
    $pdo = new Pdo("mysql:host=localhost;dbname=mysql", 'root', 'nothing'); // 密码随便填，故意写错
    // 并没有手动抛异常
    var_dump($pdo);
} catch (PDOException $e) {
    echo $e->getMessage() . "<br />";
}

echo "测试内置的异常类";
```
 
结果如下：
 
```php
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)
测试内置的异常类
```
 
## 异常可以冒泡传递
 
错误一经出现就要马上处理，而异常是可以冒泡传递的。因此异常可以嵌套。如果所在层的异常抛出后没有被本层捕获，就会寻找上层的捕获程序
 
## 多层异常嵌套
 
```php
header('content-type:text/html;charset=utf-8');
try {
    try {
        throw new Exception('测试异常1');
    } catch (Exception $e) {
        echo $e->getMessage() . "--第二层<br />";
        try {
            throw new Exception('测试异常2');
        } catch (Exception $e) {
            echo $e->getMessage() . "--第三层<br />";
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() . "--第一层<br />";
}
```
 
结果：
 
```php
测试异常1--第二层
测试异常2--第三层
```
 
## 异常冒泡传递
 
```php
header('content-type:text/html;charset=utf-8');
try {
    try {
        throw new Exception('测试异常1');
    } catch (Exception $e) {
        echo $e->getMessage() . "--第二层<br />";
        throw new Exception('测试异常2'); // 当前层并没有catch捕获此异常，因此会到外层去寻找捕获
    }
} catch (Exception $e) {
    echo $e->getMessage() . "--第一层<br />";
}
```
 
```php
测试异常1--第二层
测试异常2--第一层
```
 
## 自定义异常类
 
自定义的异常类需要继承`Exception`，可以重写父类的两个方法：`__construct`和`__toString`

```php
class MyException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        $message = "哈哈，出现异常了，是不是又写了一天的bug啊<br />";
        $message .= $this->message;
        return $message;
    }

    /**
     * 自定义的方法
     */
    public function test()
    {
        echo "异常的测试方法<br />";
    }
}

try {
    throw new MyException('这是自定义的异常');
} catch (MyException $e) {
    echo $e;
    echo $e->getMessage();
    $e->test();
}
```
 
结果：
 
```php
哈哈，出现异常了，是不是又写了一天的bug啊
这是自定义的异常这是自定义的异常异常的测试方法
```
 
还可以分类捕获异常：
 
```php
$type = 1;

try {
    if ($type == 1) {
        throw new Exception('系统异常');
    } else {
        throw new MyException('这是自定义的异常');
    }
} catch (MyException $e) {
    echo $e;
    echo $e->getMessage();
    $e->test();
} catch (Exception $e) {
    echo $e->getMessage();
}
```
 
分类捕获异常时，系统异常基类要放到最后，不然会拦截到自定义的异常
 
## 自定义异常处理器
 
使用`set_exception_handler`函数可指定函数接管异常处理，`restore_exception_handler`函数能恢复到上一次定义过的异常处理函数
 
```php
header('content-type:text/html;charset=utf-8');
function exceptionHandler_1($e)
{
    echo $e->getMessage() . "<br />";
    echo "我来接！自定义的异常处理器1--" . __FUNCTION__ . "<br />";
}

function exceptionHandler_2($e)
{
    echo $e->getMessage() . "<br />";
    echo "放着我来！自定义的异常处理器2--" . __FUNCTION__ . "<br />";
}

set_exception_handler('exceptionHandler_1');
set_exception_handler('exceptionHandler_2');

// 恢复到上一次定义过的异常处理函数
restore_exception_handler();
throw new Exception("异常信息，哪个处理器来接？");

// 抛出异常后，程序随即中止
echo "程序不会继续往下跑...<br />";
```
 
结果：
 
```php
异常信息，哪个处理器来接？
我来接！自定义的异常处理器1--exceptionHandler_1
```
 
## 像处理异常一样处理错误
 
通过`set_error_handler`函数，我们可以捕获错误，像处理异常一样。
 
```php
header('content-type:text/html;charset=utf-8');

function exception_error_handle($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// 不开启错误处理的情况下，默认报 warning 错误。开启后，不会报错，而是输出异常信息
set_error_handler('exception_error_handle');

try {
    echo gettype();
} catch (Exception $e) {
    echo $e->getMessage();
}
```
 
结果：
 
```php
gettype() expects exactly 1 parameter, 0 given
```
 


[0]: ../img/mmA7nqY.png 
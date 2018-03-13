## PHP错误和异常详解（PHP7错误处理）

来源：[http://blog.csdn.net/qq_34858648/article/details/79520588](http://blog.csdn.net/qq_34858648/article/details/79520588)

时间 2018-03-12 14:29:51


世界上没有绝对完美的事，对于程序员来说更是如此，无论我们多么努力、多么细心的开发一个项目，总会有缺陷和错误的存在。

错误和异常的异同

"错误"和"异常"的概念十分相似，很容易混淆，"错误"和"异常"都表明了项目出了问题，都会提供相关的信息，并且都有错误类型。 然而，"异常机制"是在"错误机制"后才出现的，"异常"是避免"错误"的不足。比较重要的一点就是因为     "错误"的信息不丰富 ，我们见过最多的函数说明就是: 成功时候返回***, 错误的时候返回FALSE, 然而一个函数出错的原因可能有多种, 出错的种类更有多种. 一个简单的FALSE, 并不能把具体的错误信息告诉调用者.        

PHP中将代码自身异常（一般是环境或者语法非法所致）成为错误，将运行中出现的逻辑错误称为异常（Exception）错误是没法通过代码处理的，而异常则可以通过try/catch处理.

异常

异常是Exception类的对象，在遇到无法修复的状况时抛出，出现问题时，异常用于主动出击，委托职责，异常还可用于防守，预测潜在的问题，减轻其影响。

Exception对象有两个主要的属性：一个是消息，另一个是数字代码。我们分别可以用getCode()和getMessage()获取这两个属性。如下：

```php
<?php 
$exception = new Exception("figthing!!!",100);
$code = $exception->getCode();//100
$message = $exception->getMessage();//fight.....
```

抛出异常

当一个异常被抛出后代码会立即停止执行，其后的代码将不会继续执行，PHP 会尝试查找匹配的 "catch" 代码块。如果一个异常没有被捕获，而且又没用使用    [set_exception_handler()][0]
作相应的处理的话，那么 PHP 将会产生一个严重的错误，并且输出未能捕获异常( **`Uncaught Exception ...`** )的提示信息。  

```php
throw new Exception("this is a exception");//使用throw抛出异常
```

捕获异常

我们应该捕获抛出的异常并且使用优雅的方式处理。拦截并处理异常的方式是，把可能抛出异常的代码放到try/catch块中。并且如果使用多个catch拦截多个异常的时候，只会运行其中一个，如果PHP没有找到合适的catch块，异常会向上冒泡，直到PHP脚本由于致命错误而终止运行。如下：

```php
try {
	throw new Exception("Error Processing Request");
	$pdo = new PDO("mysql://host=wrong_host;dbname=wrong_name");
} catch (PDOException $e) {
	echo "pdo error!";
} catch(Exception $e){
	echo "exception!";
}finally{
    echo "end!";//finally是在捕获到任何类型的异常后都会运行的一段代码
}
```

```php
运行结果：exception！end！
```


#### 异常处理程序

那么我们应该如何捕获每个可能抛出的异常呢？PHP允许我们注册一个全局异常处理程序，捕获所有未被捕获的异常。异常处理程序使用set_exception_handler()函数注册（这里使用匿名函数）。

```php
set_exception_handler(function (Exception $e)
{
	echo "我自己定义的异常处理".$e->getMessage();
});
throw new Exception("this is a exception");

//运行结果：我自己定义的异常处理this is a exception
```

错误

除了异常之外，PHP还提供了用于报告错误的函数。PHP能触发不同类型的错误，例如致命错误、运行时错误、编译时错误、启动错误以及用户触发的错误。可以在php.ini中设置错误报告方式（这里不做多的解释）

  
下面列举一些错误报告级别：

 
```
值          常量                     说明
1           E_ERROR             报告导致脚本终止运行的致命错误
2           E_WARNING           报告运行时的警告类错误（脚本不会终止运行）
4           E_PARSE             报告编译时的语法解析错误
8           E_NOTICE            报告通知类错误，脚本可能会产生错误
32767       E_ALL               报告所有的可能出现的错误（不同的PHP版本，常量E_ALL的值也可能不同）
```

  

无论如何都必须遵守以下几条规则：



* 一定要让PHP报告错误     
* 在开发环境中要显示错误     
* 在生产环境中不能显示错误     
* 在开发环境和生产环境中都要记录错误     
  

错误处理程序

与异常处理程序一样，我们也可以使用set_error_handler()注册全局错误处理程序，使用自己的逻辑方式拦截并处理PHP错误。我们要在错误处理程序中调用die()或exit()函数。如果不调用，PHP脚本会从出错的地方继续向下执行。如下：

```php
set_error_handler(function ($errno,$errstr,$errfile,$errline)//常用的四个参数
{
	echo "错误等级：".$errno."<br>错误信息：".$errstr."<br>错误的文件名：".$errfile."<br>错误的行号：".$errline;
	exit();
});

trigger_error("this is a error");//自行触发的错误

echo '正常';
```

运行结果：    

错误等级：1024    

错误信息：this is a error    

错误的文件名：/Users/toby/Desktop/www/Exception.php    

错误的行号：33    


相关的还有一个函数register_shutdown_function（）---是一个会在php中止时执行的函数。（有兴趣的可以自行查询一下）


#### 错误转换为异常

我们可以把PHP错误转换为异常（并不是所有的错误都可以转换,只能转换php.ini文件中error_reporting指令设置的错误），使用处理异常的现有流程处理错误。这里我们使用set_error_handler()函数将错误信息托管至ErrorException（它是Exception的子类），进而交给现有的异常处系统处理。如下：

```php
set_exception_handler(function (Exception $e)
{
	echo "我自己定义的异常处理".$e->getMessage();
});

set_error_handler(function ($errno, $errstr, $errfile, $errline )
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);//转换为异常
});

trigger_error("this is a error");//自行触发错误
```

运行结果：我自己定义的异常处理this is a error   


#### PHP7的错误异常处理

PHP 7 改变了大多数错误的报告方式。不同于传统（PHP 5）的错误报告机制，现在大多数错误被作为 Error 异常抛出。  

这种 Error 异常可以像 [Exception][1]异常一样被第一个匹配的 try / catch 块所捕获。如果没有匹配的 [catch][2]块，则调用异常处理函数（事先通过 [set_exception_handler()][3] 注册）进行处理。 如果尚未注册异常处理函数，则按照传统方式处理：被报告为一个致命错误（Fatal Error）。  

Error  类并非继承自 [Exception][1]类，所以不能用 `catch (Exception $e) { ... }` 来捕获  Error 。你可以用 `catch (Error $e) { ... }`，或者通过注册异常处理函数（[set_exception_handler()][3]）来捕获  Error 。   

```php
$a=1;
try {
$a->abc();//未定义此对象
} catch (Exception $e) {
	echo "error";
} catch (Error $e) {
	echo $e->getCode();
}
```

运行结果:0

PHP7 中出现了 `Throwable` 接口，该接口由 `Error` 和 `Exception` 实现，用户不能直接实现 `Throwable` 接口，而只能通过继承 `Exception` 来实现接口   

```php
try {
// Code that may throw an Exception or Error.
} catch (Throwable $t) {
// Executed only in PHP 7, will not match in PHP 5.x
} catch (Exception $e) {
// Executed only in PHP 5.x, will not be reached in PHP 7
}
```

注意实际项目中，在开发环境中我们可以使用Whoops组件，在生产环境中我们可以使用Monolog组件。



[0]: https://link.jianshu.com?t=http://www.php.net/manual/zh/function.set-exception-handler.php
[1]: http://php.net/manual/zh/class.exception.php
[2]: http://php.net/manual/zh/language.exceptions.php#language.exceptions.catch
[3]: http://php.net/manual/zh/function.set-exception-handler.php
[4]: http://php.net/manual/zh/class.exception.php
[5]: http://php.net/manual/zh/function.set-exception-handler.php
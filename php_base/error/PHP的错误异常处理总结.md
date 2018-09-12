## [PHP 的 错误/异常 处理总结](https://segmentfault.com/a/1190000012149712)

## 错误

> 这里说的错误，可能是由 语法解析、运行时等各种原因产生的信息引起的

### 常见的错误类型

#### 运行时错误

* E_ERROR - 致命错误
    * 定义：致命的运行时错误
    * 后果：脚本终止不再继续运行
* E_WARNING - 警告
    * 定义：运行时警告 (非致命错误)
    * 后果：给出提示信息，但是脚本不会终止运行
* E_NOTICE - 通知
    * 定义：运行时通知
    * 结果：给出通知信息，但是脚本不会终止运行

#### 其他类型错误

* 编译时错误  
eg. E_PARSEE_COMPILE_ERRORE_COMPILE_WARNING ...
* 用户产生的信息  
eg. E_USER_WARNINGE_USER_ERRORE_USER_NOTICE
* ... 等

具体如下图：

![][0]

参考：[PHP-错误处理-预定义常量][1]

### 错误处理

> 这里只针对运行时错误进行处理，其他（如： 语法错误 Zend 引擎产生的错误  等）不在讨论范围内。

#### 设置一般错误的处理函数

核心方法：[set_error_handler][2]

测试代码如下：
```php
<?php
/* 让错误信息在标准输出可见 */
ini_set("display_errors","On");

/**
 * 回调函数原型 : bool handler ( int $errno , string $errstr [, string $errfile [, int $errline [, array $errcontext ]]] )
 */
set_error_handler(function ($errno, $errstr) {
    $err_type = '';
    $return = true;
    if (E_WARNING === $errno) {
        $err_type = 'warning';
        $return = false;
    } elseif (E_NOTICE === $errno) {
        $err_type = 'notice';
    } elseif (E_ERROR === $errno) {
        $err_type = 'error';
    }
    echo sprintf("This is error callback, err_type:%s, err_no:%d, err_str:%s \n", $err_type, $errno, $errstr);
    return $return;
});

function sayHere($line)
{
    echo sprintf("I am here.Line:%d \n", $line);
}

/* warning */
function test($a) {}
test();
sayHere(__LINE__);

/* notice */
echo $notice_msg;
sayHere(__LINE__);

/* fatal */
$i = '';
while(1) {
    $i .= 'a';
}

sayHere(__LINE__);
```
结果如下：

![][3]

这里我们看到，set_error_handler**只对**E_WARNINGE_NOTICE 进行了捕获，并且当回调函数遇到  
E_NOTICE 返回 true 的时候，我们看到底层对标准错误的输出，但是遇到 E_WARNING 返回 false，我们并没有看到底层对标准错误的输出。

总结，来自于官方手册：

1. set_error_handler 第二个参数指定的错误类型都会绕过 PHP 标准错误处理程序
1. 以下级别的错误不能由用户定义的函数来处理： E_ERROR、 E_PARSE、 E_CORE_ERROR、 E_CORE_WARNING、 E_COMPILE_ERROR、 E_COMPILE_WARNING

**备注：此方法可有针对性的对服务产生的消息进行收集，处理。比如：在框架初始化时，注册一个定制化的错误回调。**

那致命错误有没有办法处理呢？接着看。

#### 设置致命错误处理函数

我们知道致命错误会引起：脚本终止不再继续运行。  
那么，我们就可以利用 [register_shutdown_function][4] 方法做一些处理。  
作用：注册一个会在php中止时执行的函数

测试代码如下：

```php
<?php
/* 让错误信息在标准输出可见 */
ini_set("display_errors","On");

/**
 * 回调函数原型 : 参数由 register_shutdown_function 的参数决定
 */
register_shutdown_function(function () {
    echo "This will shutdown. \n";
});

function sayHere($line)
{
    echo sprintf("I am here.Line:%d \n", $line);
}

function test($a)
{
    return;
}

/* warning */
test();
sayHere(__LINE__);

/* notice */
echo $notice_msg;
sayHere(__LINE__);

/* fatal */
$i = '';
while(1) {
    $i .= 'a';
}
sayHere(__LINE__);
```
结果如下：

![][5]

如前所述，发生致命错误，进程退出，但是中止之前执行了我们注册的回调函数。

- - -

## 异常

说明：我们这里指用户自定义的异常。

### try-catch 捕获

测试代码如下：

```php
<?php
/* 让错误信息在标准输出可见 */
ini_set("display_errors","On");

class UserException extends \Exception
{
}

try {
    throw new \UserException('This is exception');
} catch (\UserException $e) {
    echo 'UserException:' . $e->getMessage() . PHP_EOL;
} catch (\Exception $e) {
    echo 'Exception:' . $e->getMessage() . PHP_EOL;
} finally {
    echo 'here is finally' . PHP_EOL;
}
```
结果如下：

    ➜  answer git:(master) ✗ php exception.php
    UserException:This is exception
    here is finally

这是常见的捕获，不做过多说明，参见：[异常处理][6]

### 未捕获的异常

那么，如有抛出去的异常未被 catch，怎么办？  
我们先看一下，未被 catch 会怎么样：

```php
<?php
/* 让错误信息在标准输出可见 */
ini_set("display_errors","On");

throw new \Exception('I am an exception');

echo 'I am here' . PHP_EOL;
```
结果如下：

    ➜  answer git:(master) ✗ php throw.php
    
    Fatal error: Uncaught exception 'Exception' with message 'I am an exception' in /Users/javin/github/answer/throw.php:5
    Stack trace:
    #0 {main}
      thrown in /Users/javin/github/answer/throw.php on line 5

会出现 致命错误，脚本中断，那么，我们当然可以用上边所说的 register_shutdown_function 来处理。  
这样的话，就没有合其他致命错误区分了，那么，有没有专门处理未捕获的异常呢？  
答案是有的，它就是：[set_exception_handler][7]

测试代码如下：

```php
<?php
/* 让错误信息在标准输出可见 */
ini_set("display_errors","On");

/**
 * 回调函数签名：void handler ( Exception $ex )
 */
set_exception_handler(function ($e) {
    echo sprintf("This is exception, msg:%s\n", $e->getMessage());
});

throw new \Exception('I am an exception');
echo 'I am here' . PHP_EOL;
```
结果如下：

    ➜  answer git:(master) ✗ php throw.php
    This is exception, msg:I am an exception

结论：set_exception_handler 可以对未捕获的异常进行处理，但是脚本仍然会因为致命错误而中断。

- - -

## 结尾

本文对 异常处理 做了简要的总结，其中涉及到三个核心方法 set_error_handlerregister_shutdown_functionset_exception_handler，其详细说明，请参见 [官方手册][8] 。  
同时 PHP-7 中也有一些新的特性，比如：[Error 类][9]

参考：[PHP 7 错误处理][10]

最后，强烈建议开启编辑器的 语法检查 功能，不管是 IDE，还是 GUI 文本编辑器，还是 vim，这样可以避免很多不必要的错误。如果有使用版本控制，可以给对应的软件加上 语法检查 的钩子。

可以参考：

* [我的 vim-配置][11]
* [自动化检测PHP语法和编程规范(Git pre-commit)][12]

- - -

以上如有错误，请多多指正。如有遗漏，请多多补充。🙏

[0]: ../img/bVY8ot.png
[1]: http://php.net/manual/zh/errorfunc.constants.php
[2]: http://php.net/manual/zh/function.set-error-handler.php
[3]: ../img/bVY8vS.png
[4]: http://php.net/manual/zh/function.register-shutdown-function.php
[5]: ../img/bVY8C1.png
[6]: http://php.net/manual/zh/language.exceptions.php
[7]: http://php.net/manual/zh/function.set-exception-handler.php
[8]: http://php.net/manual/zh/ref.errorfunc.php
[9]: http://php.net/manual/en/class.error.php
[10]: http://php.net/manual/zh/language.errors.php7.php
[11]: https://github.com/fevin/vimrc
[12]: http://blog.blianb.com/archives/2954
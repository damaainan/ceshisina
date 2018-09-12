# 你知道 PHP 中 Exception, Error Handler 的这些细节吗？

 时间 2017-06-27 20:42:55  

原文[http://www.jianshu.com/p/1a443d542219][1]

## 前言

最近项目中有一个功能需要实现:

调试模式下, 将所有错误提前输出, 再输出页面内容.

为实现上述功能, 需使用到 Exception , Error 相关 Handler 方法, 发现有许多坑, 故写此文与大家分享. 

## 主要函数

此篇文章重点关注以下几个函数

* error_reporting()
* set_error_handler()
* set_exception_handler()
* register_shutdown_function()
* error_get_last()

## 这有什么难的?

哈~ 如果您现在有标题中的感慨, 那么也请关注以下本文中将重点讲述的问题列表:

1. `error_reporting()` 与 `error_get_last()` 有什么联系?
1. `set_error_handler()` 与 `set_exception_handler()` 绑定的 handler 什么时候才会启动? 它们有什么联系?
1. `register_shutdown_function()` 通常跟 Exception/Error 有关系么?

上述问题描述模糊, 因此答案也可能千人千面.

因此, 本文只给出自己的答案与大家分享, 如有问题或不同的见解, 期待与您沟通.

如果以上问题, 并不能引起您的兴趣, 或者您已理解透彻了, 就可以自行右上角小红叉啦~

## 解疑:

### 1. error_reporting() 与 error_get_last() 有什么联系? 

[link: php.net - error_reporting()][3]

[link: php.net - error_get_last()][4]

* int error_reporting ([ int $level ] )  
    大家应该再熟悉不过了, 因此不再赘述.

* array error_get_last ( void )获取最后发生的错误.  
    通常用来获取PHP运行过程中的 Fatal Error 错误( PHP 5 ).

这两个函数在字面上关联性并不强, 但请观察以下代码及输出

```php
<?php
error_reporting(E_ALL & ~E_NOTICE);
$a = $b;  //E_NOTICE
print_r(error_get_last());

/* output:
Array
(
    [type] => 8
    [message] => Undefined variable: b
    [file] => /app/t.php
    [line] => 3
)
*/
```

`error_get_last()` 虽然说明了获取最后发生的错误, 实际上也是如此. 但却没有说明, 被 `error_reporting()` 忽略掉的错误是否有可能被获取到, 因此, 当我们使用` error_get_last()` 时需要注意我平时忽略掉的错误, 如: `E_DEPRECATED` 

### 2. set_error_handler() 与 set_exception_handler() 绑定的 handler 什么时候才会启动? 它们有什么联系? 

[link: php.net - set_error_handler()][5]

[link: php.net - set_exception_handler()][6]

* mixed set_error_handler ( callable $error_handler [, int $error_types = E_ALL | E_STRICT ] )设置用户自定义的错误处理函数.

通常在PHP脚本运行过程中, 出现一些非中断性错误时触发.

我们会用这个来记录错误日志或直接输出等操作.

注意:


  1. 参数 `$error_types` 大多设定为 `error_reporting()` , 但建议设定为 `E_ALL` , 具体哪些错误需要被处理, 哪些不需要, 在 handler 内进行判断明显更加灵活.
  1. 以下级别的错误不能由用户定义的函数来处理： `E_ERROR`、 `E_PARSE`、 `E_CORE_ERROR`、 `E_CORE_WARNING`、 `E_COMPILE_ERROR`、 `E_COMPILE_WARNING`，和在 调用 `set_error_handler()` 函数所在文件中产生的大多数 `E_STRICT`
  1. handler 被触发后, 并不会中断PHP运行.
  1. bool error_handler ( int $errno , string $errstr [, string $errfile [, int $errline [, array $errcontext ]]] )  

> 注意 `error_handler` 的返回值:   
   
   > FALSE : 标准的错误处理依然会被执行(标准错误处理根据 display_errors = true/false 决定是否输出到 stderr )

* callable set_exception_handler ( callable $exception_handler )设置用户自定义的异常处理函数

设置默认的异常处理程序，用于没有用 try/catch 块来捕获的异常。 在 exception_handler 调用后异常会中止。

注意:


  1. `exception_handler` 调用后异常会中止(脚本终止).
  1. PHP 5 , PHP 7 的 `exception_handler` 并不相同.   
    PHP 5 : void handler ( Exception $ex )  
    PHP 7 : void handler ( Throwable $ex )
  1. 自 PHP 7 以来，大多数错误抛出 Error 异常，也能被捕获。 Error 和 Exception 都实现了 Throwable 接口。

注意点中2, 3项轻描淡写了一下 PHP 5/PHP 7 之间的不同却透露出重要的消息(坑..) 

PHP 7 中, `exception_handler` 不再只接受 Exception 了, 并且接收了 Error 错误. 

[link: php.net - PHP7 Errors列表][7]

因此, `set_error_handler()` 与 `set_exception_handler()` 之间的关系也迎刃而解: 

* PHP 5 : 
    * `set_error_handler()` : 负责非中断行错误.
    * `set_exception_handler()` : 负责没有被catch的异常(会中断).
    * `Fatal Error` 等: 并不会被两者管理, 正常输出到屏幕上(弊端).

* PHP 7 : 
    * `set_error_handler()` : 负责非中断行错误.
    * `set_exception_handler()` : 负责没有被catch的异常, Error (会中断)
    * Fatal Error 等: 由 `set_exception_handler()` 管理.

### 3. register_shutdown_function() 通常跟Exception/Error有关系么? 

[link: php.net - register_shutdown_function()][8]

注册一个 callback ，它会在脚本执行完成或者 exit() 后被调用。

根据说明可以得出结论, 它与 Exception/Error**完全没关系** . 

提出这个问题, 主要是因为, 在 PHP5 中 Fatal Error 并没有明确的接收地点, 所以我们通常配合 `error_get_last()` 来接收 Fatal Error    


```php
<?php 
register_shutdown_function('shutdown_function');
unknown_function();

function shutdown_function() {
  print_r(error_get_last());
}

/* output:
Array
(
    [type] => 1
    [message] => Uncaught Error: Call to undefined function unknown_function() in /app/t.php:3
Stack trace:
#0 {main}
  thrown
    [file] => /app/t.php
    [line] => 3
)
*/
```

然而随着 PHP 7 的到来, Error 已经可以被 `set_exception_handler()` 捕捉了, 再通过 `error_get_last()` 就多余了. shutdown 中更多的是一些版本冗余的工作. 

## 栗子

前言中的需求: 调试模式下, 将所有错误提前输出, 再输出页面内容.

以下是demo, 省去了环境判断(debug环境), 大家可以根据下面这段代码, 了解本文中所说的各种 handler 的触发和调用情况. 

```php
<?php

/*
要求: 将所有异常打印在屏幕最上方
*/

/* Fatal Error 中断脚本 -> shutdown_handler */

//设置错误级别
define("END_ERRORS", '--END ERRORS--' . PHP_EOL . PHP_EOL);
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

set_error_handler('usr_err_handler', error_reporting()); //注册错误处理函数
set_exception_handler('usr_ex_handler'); //注册异常处理函数
register_shutdown_function('shutdown_handler');    //注册会在php中止时执行的函数


$global_errors = [];    //用于记录所有错误
$errnos = [             //错误级别
    0 => 'ERROR',//PHP7 ERROR的CODE
    1 => 'E_ERROR',//FATAL ERROR(PHP5), E_ERROR
    2 => 'E_WARNING',
    4 => 'E_PARSE',
    8 => 'E_NOTICE',
    16 => 'E_CORE_ERROR',
    32 => 'E_CORE_WARNING',
    64 => 'E_COMPILE_ERROR',
    128 => 'E_COMPILE_WARNING',
    256 => 'E_USER_ERROR',
    512 => 'E_USER_WARNING',
    1024 => 'E_USER_NOTICE',
    2048 => 'E_STRICT',
    4096 => 'E_RECOVERABLE_ERROR',
    8192 => 'E_DEPRECATED',
    16384 => 'E_USER_DEPRECATED',
    30719 => 'E_ALL',
];

function reset_errors()
{
    global $global_errors;
    $global_errors = [];
}

function get_errnostr($errno)
{
    global $errnos;
    return $errnos[$errno];
}

function set_errnos($errno, $errstr)
{
    global $global_errors;
    $global_errors[] = [
        'errno' => $errno,
        'errnostr' => get_errnostr($errno),
        'errstr' => $errstr,
    ];
}

function print_errors($prefix)
{
    global $global_errors;
    foreach ($global_errors as $err) {//由于handler中依然有可能有error 因此放最后
        printf("[%s]: %s, %d, %s\n",
            $prefix, $err['errnostr'], $err['errno'], $err['errstr']);
    }
}

//用户异常处理函数 (进来就中断脚本) PHP5只有Exception进来   PHP7Error和Exception
//PHP7中 void handler (Throwable $ex) 可捕获Error和Exception两种异常, 暂不管
//http://php.net/manual/en/language.errors.php7.php PHP7 Error阅读
//内部如果有Error则触发Error函数, 再回到错误行继续执行
function usr_ex_handler($ex)
{
    $content = ob_get_clean();  //让Exception/Error提前展示

    print_errors('EX ERROR');
    reset_errors();

    $errnostr = get_errnostr($ex->getCode());
    $errno = $ex->getCode();
    $errstr = $ex->getMessage();

    if ($ex instanceof Exception) {
        printf("[EXCEPTION]: %s, %d, %s\n", $errnostr, $errno, $errstr);
    } else {//针对PHP7  $ex instanceof Error
        printf("[EX FATAL ERROR]: %s, %d, %s\n", $errnostr, $errno, $errstr);
    }

    //由于handler中依然有可能有error 因此放最后
    print_errors('EX ERROR');
    reset_errors();

    echo END_ERRORS;
    echo $content;

    return;
}

//用户错误处理函数
//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING不能被用户处理
function usr_err_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
    set_errnos($errno, $errstr);
    return true;    //如果函数返回 FALSE，标准错误处理处理程序将会继续调用。
}

//用户PHP终止函数
function shutdown_handler()
{
    $content = ob_get_clean();  //让Exception/Error提前展示
    $err = error_get_last();//检查一下是否有遗漏掉的错误 php5 fatal error
    if ($err['type'] & error_reporting()) {
        set_errnos($err['type'], $err['message']);
    }
    print_errors('ST ERROR');
    reset_errors();

    echo $content;
}

ob_start();

echo 'Main function...', PHP_EOL;

//搞事情
//throw new Exception('这是一个异常');
trigger_error('这是一个用户error');//E_USER_NOTICE

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    mcrypt_encrypt();//E_WARNING, E_DEPRECATED
} else {
    mysql();
}
unknown_function(); //fatal error


$content = ob_get_clean();

//优先输出错误
print_errors('MA ERROR');
if (!empty($global_errors)) {
    echo END_ERRORS;
}
reset_errors();

//输出正文内容
echo $content;
```

[1]: http://www.jianshu.com/p/1a443d542219

[3]: http://php.net/manual/zh/function.error-reporting.php
[4]: http://php.net/manual/zh/function.error-get-last.php
[5]: http://php.net/manual/zh/function.set-error-handler.php
[6]: http://php.net/manual/zh/function.set-exception-handler.php
[7]: http://php.net/manual/zh/language.errors.php7.php
[8]: http://php.net/manual/zh/function.register-shutdown-function.php
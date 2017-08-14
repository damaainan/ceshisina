## php的错误级别的设置和总结

<font face=微软雅黑>

PHP 的错误机制也是非常复杂的，做了几年php，也没有仔细总结过，现在就补上这一课。特别说明一下，本文章的PHP版本使用的是 5.6.21(xampp 集成环境)


打开`php.ini`文件搜索 `Error handling and logging`，这一块都是和错误相关的配置

**配置选项** （以下是常用的一些配置，还有很多配置并不常用，这里不再介绍，可以去 [PHP运行时配置][1] 查看）

**error_reporting** ：报告错误级别，在PHP5中有[16种错误级别][2]

**error_log = /tmp/php_errors.log** ：php中的错误显示的日志位置

**display_errors** ：是否把错误展示在输出上，这个输出可能是页面，也可能是stdout

**display_startup_errors** ：是否把启动过程的错误信息显示在页面上，记得上面说的有几个Core类型的错误是启动时候发生的，这个就是控制这些错误是否显示页面的。

**log_errors** ：是否要记录错误日志

**log_errors_max_len** ：错误日志的最大长度

**ignore_repeated_errors** ：是否忽略重复的错误

**track_errors** ：是否使用全局变量$php_errormsg来记录最后一个错误

**xmlrpc_errors** ：是否使用XML-RPC的错误信息格式记录错误

**xmlrpc_error_number** ：用作 XML-RPC faultCode 元素的值。

**html_errors** ：是否把输出中的函数等信息变为HTML链接

**docref_root = http://manual/en/** ：如果html_errors开启了，这个链接的根路径是什么

**fastcgi.logging** ：是否把php错误抛出到fastcgi中

我们经常会被问到，error_reporting和display_errors有什么区别呢？这两个函数是完全不一样的。PHP默认是会在日志和标准输出（如果是fpm模式标准输出就是页面）

error_reporting的参数是错误级别。表示什么样子的级别才应该触发错误。如果我们告诉PHP，所有错误级别都不需要触发错误，那么，不管是日志，还是页面，都不会显示这个错误，就相当于什么都没有发生。

display_errors是控制是否要在标准输出展示错误信息

log_errors则是控制是否要在日志中记录错误信息。

error_log是显示错误日志的位置，这个在php-fpm中往往会被重写，于是往往会发现的是cli和fpm的错误日志竟然不是在同一个文件中。

ignore_repeated_errors这个标记控制的是如果有重复的日志，那么就只会记录一条，比如下面的程序：

```php
    <?php
    ini_set('ignore_repeated_errors', 1);
    ini_set('ignore_repeated_source', 1);
     
    $a = $c; $a = $c; //E_NOTICE
    //Notice: Undefined variable: c in /tmp/php/index.php on line 20
```

本来会出现两次NOTICE的，但是现在，只会出现一次

html_errors 和 docref_root 两个是个挺有人性化的配置，配置了这两个参数以后，我们返回的错误信息中如果有一些在文档中有的信息，就会变成链接形式

```php
    <?php
    error_reporting(E_ALL);
    ini_set('html_errors', 1);
    ini_set('docref_root', "https://secure.php.net/manual/zh/");
    include("a2.php"); //E_WARNING
```

能让你快速定位到我们出现错误的地方。是不是很人性

#### php-fpm中的配置

```
    error_log = /var/log/php-fpm/error.log // php-fpm自身的日志
    log_level = notice // php-fpm自身的日志记录级别
    php_flag[display_errors] = off // 覆盖php.ini中的某个配置变量，可被程序中的ini_set覆盖
    php_value[display_errors] = off // 同php_flag
    php_admin_value[error_log] = /tmp/www-error.log // 覆盖php.ini中的某个配置变量，不可被程序中的ini_set覆盖
    php_admin_flag[log_errors] = on // 同php_admin_value
    catch_workers_output = yes // 是否抓取fpmworker的输出
    request_slowlog_timeout = 0 // 慢日志时长
    slowlog = /var/log/php-fpm/www-slow.log // 慢日志记录
```

php-fpm的配置中也有一个error_log配置，这个很经常会和php.ini中的error_log配置弄混。但他们记录的东西是不一样的，php-fpm的error_log只记录php-fpm本身的日志，比如fpm启动，关闭。

而php.ini中的error_log是记录php程序本身的错误日志。

那么在php-fpm中要覆盖php.ini中的error_log配置，就需要使用到下面几个函数：

php_flag

php_value

php_admin_flag

php_admin_value

这四个函数admin的两个函数说明这个变量设置完之后，不能在代码中使用ini_set把这个变量重新赋值了。而php_flag/value就仍然以php代码中的ini_set为准。

slowlog是fpm记录的，可以使用request_slowlog_timeout设置判断慢日志的时长

下面用实例来分析一下上面提到的16种错误级别。首先把配置文件（php.ini）中的错误信息开启

    ; 显示所有的错误信息
    display_errors=On
    
    ; 显示所有的错误
    error_reporting = E_ALL

#### E_ERROR

这种错误是致命错误，会在页面显示Fatal Error，当出现这种错误的时候，程序就无法继续执行下去了，错误示例：

```php
    <?php
    $demo = new Demo(); // Fatal error: Class 'Demo' not found in E:\www.demo.com\index.php on line 2
```

这里出现了致命错误，是因为没有找到 Demo 类，如果有未被捕获的异常，也是会触发这个级别的，如：

```php
    <?php
    // Fatal error: Uncaught exception 'Exception' with message 'test exception' in E:\www.demo.com\index.php:3 Stack trace: #0 {main} thrown in E:\www.demo.com\index.php on line 3
    throw new Exception("test exception");
```

#### E_WARNING

这种错误只是警告，不会终止脚本，程序还会继续进行，显示的错误信息是Warning。比如include一个不存在的文件。

```php
    <?php
    include 'demo.php';
    echo "Hello 易读小屋";
```

以上的“Hello 易读小屋”可以正常显示，不过，也会出现一行 Warning: include(): Failed opening 'demo.php' for inclusion (include_path='C:\xampp\php\PEAR')，虽然这个不影响程序进行，但是一定要把这样的警告给修复了，因为有些警告会导致意想不到的结果。

#### E_PARSE

编译时语法解析错误。解析错误仅仅由分析器产生。这个错误是编译时候发生的，在编译期发现语法错误，不能进行语法分析。

```php
    <?php
    int a = 1;
    echo 123;
```

上面的示例就会报 Parse error: syntax error, unexpected 'a' (T_STRING)，原因是PHP不支持这样的变量定义，这样的错误也是致命的，程序不会再进行下去，如上面的代码并不会输出 123;

#### E_NOTICE

这种错误程度更为轻微一些，提示你这个地方不应该这么写。这个也是运行时错误，这个错误的代码可能在其他地方没有问题，只是在当前上下文情况下出现了问题。比如：打印一个不存在的变量时，如：

```php
    <?php
    echo $a;
    echo "Hello 易读小屋";
```

以上的“Hello 易读小屋”可以正常显示，但是会出现一行 Notice: Undefined variable: a，这样的注意警告，除非你知道是什么意思，否则最好也是修改一下。

#### E_STRICT

这个错误是PHP5之后引入的，你的代码可以运行，但是不是PHP建议的写法。比如在函数形参传递++符号

```php
    <?php
    function change (&$var) 
    {
        $var += 10;
    }
     
    $var = 1;
    change( ++ $var );
    var_dump( $var );
```

以上的代码可以正常输出，不过有一个 Strict Standards: Only variables should be passed by reference的提示

#### E_RECOVERABLE_ERROR

这个级别其实是ERROR级别的，但是它是期望被捕获的，如果没有被错误处理捕获，表现和E_ERROR是一样的。经常出现在形参定义了类型，但调用的时候传入了错误类型。它的错误提醒也比E_ERROR的fatal error前面多了一个Catachable的字样。

```php
    <?php
    function printInt( int $number )
    {
        echo $number;
    }
    printInt('abc');
    
    echo 123;
```

上面会报Catchable fatal error: Argument 1 passed to printInt() must be an instance of int, string given，说明函数需要一个整数，传的参数却是一个字符串，这样的错误是致命的，程序不会再继续执行下去，如上面的代码并没有输出 123;

#### E_DEPRECATED

这个错误表示你用了一个旧版本的函数，而这个函数后期版本可能被禁用或者不维护了。比如用 ereg 做正则匹配

```php
    <?php
    if (ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $date, $regs)) 
    {
        echo "$regs[3].$regs[2].$regs[1]";
    } else {
        echo "Invalid date format: $date";
    }
```

上面的代码会有两个 Notice，表示没有提前定义 $date, $regs 变量。还有一个 Deprecated: Function ereg() is deprecated，这样的警告一般影响不大，但是为了程序移植性更好，还是修改一下吧

#### E_CORE_ERROR, E_CORE_WARNING

这两个错误是由PHP的引擎产生的，在PHP初始化过程中发生。

#### E_COMPILE_ERROR, E_COMPILE_WARNING

这两个错误是由PHP引擎产生的，在编译过程中发生。

#### E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_USER_DEPRECATED

这些错误都是用户制造的，使用trigger_error，这里就相当于一个口子给用户触发出各种错误类型。这个是一个很好逃避try catch异常的方式。

```php
    <?php
    trigger_error("Cannot divide by zero", E_USER_ERROR);
    // E_USER_ERROR
    // E_USER_WARING
    // E_USER_NOTICE
    // E_USER_DEPRECATED
```

#### E_ALL

E_STRICT出外的所有错误和警告信息

#### 总结

我们经常弄混的就是日志问题，以及某些级别的日志为何没有记录到日志中。最主要的是要看error_log，display_errors, log_errors这三个配置，只是在看配置的时候，我们还要注意区分php.ini里面的配置是什么，php-fpm.ini里面的配置是什么。

</font>

[1]: http://php.net/manual/zh/errorfunc.configuration.php#ini.xmlrpc-error-number
[2]: http://php.net/manual/zh/errorfunc.constants.php
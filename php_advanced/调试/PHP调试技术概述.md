# PHP调试技术概述

作者  麻城东 关注 2017.07.29 01:26  字数 1459  

代码调试是任何一个程序员都应该具备的技能，由于PHP是一门动态解释型语言，不需要编译就可以运行，这使得调试变得尤为方便，下面分享一些PHP的调试技术，希望对大家有所帮忙。

###### 1. 单步调试

当程序里包含很多个条件判断，而你不确定程序执行到了哪个分支的时候可以使用，它可以帮你快速的缩小范围，定位问题，排除故障。这些方法包含：`echo pirnt print_r var_dump var_export debug_zval_dump`，其中`var_dump`的价值很多人都不知道，这里不累述，但只想告诉你 `echo "<pre>";var_dump($arr);exit();` 这三件套很实用，你可以记住。

###### 2. 日志调试

很多时候不方便把敏感信息显示出来，但问题又拿捏不准，怎么排查成为一个伤感的问题，这个时候就需要用日志来记录了。日志的重要性大家都知道，不知道的请回家把田耕了。PHP记录日志可以说是所有语言中最简单的，没有之一，你可以直接使用`file_put_contents`或`error_log`,其中较常用的是`error_log` ，使用说明如下：

    bool error_log ( string $message [, int $message_type = 0 [, string $destination [, string $extra_headers ]]] )
    把错误信息发送到 web 服务器的错误日志，或者到一个文件里。
    

###### 3. Xdebug

Xdebug是一个开放源代码的PHP程序调试器(即一个`Debug`工具)，可以用来跟踪，调试和分析PHP程序的运行状况，其之强大这里暂不赘言了。如果安装开启了`xdebug`之后，`var_dump`的结果也会有意外的效果哦。这里直接给出xdebug的一些配置，仅供参考：

    zend_extension="/usr/local/php56/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so"
    ; profiler
    xdebug.profiler_enable=1
    xdebug.profiler_enable_trigger=1
    xdebug.profiler_output_dir=/data/xdebug/php56
    xdebug.profiler_output_name=cachegrind.out.%p
    
    ; trace
    xdebug.auto_trace=1
    xdebug.show_exception_trace=1
    xdebug.trace_output_dir=/data/xdebug/php56
    xdebug.trace_output_name=trace.%c
    

由于`Xdebug`生成的结果，晦涩难懂，用肉眼凡胎实在难窥其意，所以你需要使用`Webgrind`来配合分析。Webgrind是一款 PHP 编写的开源工具，部署简单，使用方便。下载之后只要简单修改一下config.php文件里的`dotExecutable = '/data/xdebug/php56'`即可。  
相关下载地址：  
Xdebug http://xdebug.org/  
Webgrind http://code.google.com/p/webgrind/

###### 4. debug_backtrace/debug_print_backtrace

如果你觉得不想安装使用Xdebug，那么`debug_backtrace`也是一个不错的选择，只需简单的封装，就可以达到调试的目地，是居家必备的良品。  
**debug_backtrace**

    array debug_backtrace ([ int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT [, int $limit = 0 ]] )
    产生一条PHP的回溯跟踪
    

**debug_print_backtrace**

    void debug_print_backtrace ([ int $options = 0 [, int $limit = 0 ]] )
    打印了一条PHP回溯
    

详见：http://php.net/manual/zh/function.debug-backtrace.php


###### 5. FirePHP调试

相信大家对 **Firebug**应该不陌生吧，用它调试JS，简直Very Good. 当然PHP也可以配合 **Firebug**这么好用的工具，即 **FirePHP**。下载安装使用`FirePHP`都较为简单，需先在Firefox上安装好`Firebug`，然后在服务端的PHP代码里使用`FirePHP`即可。

###### 6. PHPDBG

`PHPDBG`是一个PHP的SAPI模块，可以在不用修改代码和不影响性能的情况下控制PHP的运行环境。`PHPDBG`的目标是成为一个轻量级、强大、易用的PHP调试平台。主要功能有：单步调试、灵活的下断点方式、可直接调用php的eval、可以查看当前执行的代码、用户空间API、方便集成、支持指定php配置文件、JIT全局变量、终端操作方便。安装`PHPDBG`只需要在编译PHP的时候加上`--enable-phpdbg`即可，执行文件正常位于`/usr/local/php/bin/`目录之下。

###### 7. APD(AdvancedPHPDebugger)

`APD` 是` Advanced PHP Debugger`，即高级 PHP 调试器。是用来给 PHP 代码 供规划与纠错的能力， 以及 供了显示整个堆栈追踪的能力。`APD` 支持交互式纠错，但默认是将数据写入跟踪文件。它还 供了 基于事件的日志，因此不同级别的信息(包括函数调用，参数传递，计时等)可以对个别的脚本打开或关闭。

###### 8.控制错误

PHP显示错误，主要受`php.ini`里面`error_reporting`和`display_errors`两个参数影响，当我们不方便暴露敏感信息时，可以选择开启php.ini里面的错误日志`log_errors`和`error_log`。如果使用php-fpm还可以在`php-fpm.conf`里面开启`error_log`，如`error_log = /var/log/php-fpm.log`。  
当然有时候可能还需要开启nginx错误日志，如`error_log /var/log/nginx/error.log debug`;


###### 9.使用工具

WEB开发常用的工具你必须得使几个，正所谓工欲善其事，必先利其器嘛！  

**Fiddler**  
Fiddler是一个[http协议][0]调试代理工具，它能够记录并检查所有你的电脑和互联网之间的http通讯，设置断点，查看所有的“进出”Fiddler的数据（指[cookie][1],html,js,css等文件，这些都可以让你胡乱修改的意思）。 Fiddler 要比其他的网络调试器要更加简单，因为它不仅仅暴露http通讯还提供了一个用户友好的格式。

**Tcpdump**  
Tcpdump可以将网络中传送的[数据包][2]完全截获下来提供分析。它支持针对网络层、协议、[主机][3]、网络或端口的过滤，并提供and、or、not等逻辑语句来帮助你去掉无用的信息。  

**Postman/Dohttp**  
模拟创建发送任何Http请求，并保存到历史中可以重复使用。

###### 10.其他杂项

1. 如使用curl库时，可使用`curl_errno`、`curl_error`能处理会员错误。
1. 在cli模式下，使用`time`打印程序的执行时间,如：time php aa.php

**注：**本文部分内容参见了鸟哥的文章http://www.laruence.com/2010/06/21/1608.html


[0]: https://baike.baidu.com/item/http%E5%8D%8F%E8%AE%AE
[1]: https://baike.baidu.com/item/cookie/1119
[2]: https://baike.baidu.com/item/%E6%95%B0%E6%8D%AE%E5%8C%85
[3]: https://baike.baidu.com/item/%E4%B8%BB%E6%9C%BA
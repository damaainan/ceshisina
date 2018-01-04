## [php-fpm7.0 慢查询设置及说明](https://segmentfault.com/a/1190000012690784)


## 环境说明

    root@ubuntu:/home/tb# cat /etc/issue
    Ubuntu 16.04.2 LTS \n \l
    
    root@ubuntu:/home/tb# php -v
    PHP 7.0.15-0ubuntu0.16.04.4 (cli) ( NTS )
    Copyright (c) 1997-2017 The PHP Group
    Zend Engine v3.0.0, Copyright (c) 1998-2017 Zend Technologies
        with Zend OPcache v7.0.15-0ubuntu0.16.04.4, Copyright (c) 1999-2017, by Zend Technologies
    
    
    

## 查看php-fpm配置路径

    root@ubuntu:/home/tb# ps -ef |grep fpm
    root      1642     1  0 10:17 ?        00:00:01 php-fpm: master process (/etc/php/7.0/fpm/php-fpm.conf)
    www-data  3685  1642  0 17:14 ?        00:00:04 php-fpm: pool www
    www-data  3686  1642  0 17:14 ?        00:00:03 php-fpm: pool www
    www-data  3808  1642  0 17:43 ?        00:00:03 php-fpm: pool www
    root      3930  2208  0 18:10 pts/0    00:00:00 grep --color=auto fpm
    root@ubuntu:/home/tb#
    

php-fpm.conf的最后一行为

    include=/etc/php/7.0/fpm/pool.d/*.conf
    

那我们去那里改www.conf，查看关于慢查询的介绍

    314 ; The log file for slow requests
    315 ; Default Value: not set
    316 ; Note: slowlog is mandatory if request_slowlog_timeout is set
    317 ;slowlog = log/$pool.log.slow
    318
    319 ; The timeout for serving a single request after which a PHP backtrace will be
    320 ; dumped to the 'slowlog' file. A value of '0s' means 'off'.
    321 ; Available units: s(econds)(default), m(inutes), h(ours), or d(ays)
    322 ; Default Value: 0
    323 ;request_slowlog_timeout = 0
    

## 添加两行配置

    slowlog=/var/log/php7.0/fpm/slow.log
    request_slowlog_timeout=1s
    

重启fpm，同时注意目录权限等一般问题

    service php7.0-fpm reload
    
    

## 查看log回显结果

    [03-Jan-2018 18:48:53]  [pool www] pid 4934
    script_filename = /usr/share/nginx/ccbranches/index.php
    [0x00007fb626213520] session_start() /usr/share/nginx/ccbranches/app/models/user_model.php:11
    [0x00007fb6262134a0] __construct() /usr/share/nginx/ci_2.2.0/core/Loader.php:303
    [0x00007fb626213390] model() /usr/share/nginx/ccbranches/app/core/XIN_Controller.php:39
    [0x00007fb6262132e0] __construct() /usr/share/nginx/ccbranches/app/controllers/home.php:8
    [0x00007fb626213270] __construct() /usr/share/nginx/ci_2.2.0/core/CodeIgniter.php:308
    [0x00007fb6262130e0] [INCLUDE_OR_EVAL]() /usr/share/nginx/ccbranches/index.php:325
    
    [03-Jan-2018 18:48:53]  [pool www] pid 4931
    script_filename = /usr/share/nginx/ccbranches/index.php
    [0x00007fb626213520] session_start() /usr/share/nginx/ccbranches/app/models/user_model.php:11
    [0x00007fb6262134a0] __construct() /usr/share/nginx/ci_2.2.0/core/Loader.php:303
    [0x00007fb626213390] model() /usr/share/nginx/ccbranches/app/core/XIN_Controller.php:39
    [0x00007fb6262132e0] __construct() /usr/share/nginx/ccbranches/app/controllers/home.php:8
    [0x00007fb626213270] __construct() /usr/share/nginx/ci_2.2.0/core/CodeIgniter.php:308
    [0x00007fb6262130e0] [INCLUDE_OR_EVAL]() /usr/share/nginx/ccbranches/index.php:325
    

## 关于log的说明

    [03-Jan-2018 18:48:53]  [pool www] pid 4931
    

这个没啥解释，时间进程id

    script_filename = /usr/share/nginx/ccbranches/index.php
    

执行脚本名称，php web应用都为单入口

    [0x00007fb626213520] session_start() /usr/share/nginx/ccbranches/app/models/user_model.php:11
    

这个第三行比较重要：是堆栈顶部信息（is the top of the stack trace），他说明指出了超出了阈值的当前执行的方法的函数调用是哪个，以及具体的文件及代码行数  
剩下的其他部分就是 调用的顺序（从下往上，最终导致变慢超时的结果）

**其他说明**

如果发现第三行是以类似curl_exec()，比如：这一般是网络io占用了时间，如果是必须请求第三方，那么。。忍吧。

    [0x00007fb6262136f0] curl_exec() /usr/share/nginx/
    

同样，如果有mysql_query之类的，也是由于sql 慢查询导致的

## 参考链接

[php-fpm配置][0]  
[how-to-read-the-php-slow-request-log][1]

[0]: http://php.net/manual/zh/install.fpm.configuration.php
[1]: https://serverpilot.io/community/articles/how-to-read-the-php-slow-request-log.html
* **PHP运行原理**

**主要了解 CGI、FastCGI、PHP-FPM**

    CGI：是一种通讯协议（已经过时，性能低下）
    FastCGI：也是一种通讯协议，是CGI的升级版（现在推荐使用）
    PHP-FPM：是FastCGI进程管理器
    php-cgi.exe：是PHP脚本解析器，不是FastCGI进程管理器

**PHP 有5种PHP运行模式**：[链接][0]

    1、以CGI模式运行PHP
    
    LoadModule cgi_module modules/mod_cgi.so //要加载apache自带模块
    
    <Files ~ "\.php$">
        Options FollowSymLinks ExecCGI
        AddHandler cgi-script .php
        FcgidWrapper "D:/BtSoft/WebSoft/php/7.1/php-cgi.exe" .php
    </Files>
    
    //如果同时打开多个则会有很多php-cgi.exe,并且在执行完成之后消失：

![][1]

    2、以FastCGI模式运行PHP
    
    FastCGI模式根据进程管理器的不同可以分为：Apache内置进程管理器，PHP-FPM进程管理器
    
    Apache内置进程管理器:
    LoadModule fcgid_module modules/mod_fcgid.so //要加载apache模块,该模块要单独下载
    <IfModule fastcgi_module>
       FastCgiServer /home/weiyanyan/local/apache/cgi-bin/php-cgi -processes 20
       AddType application/x-httpd-php .php
       AddHandler php-fastcgi .php
       Action php-fastcgi /cgi-bin/php-cgi
    </IfModule>
    
    PHP-FPM进程管理器:
    LoadModule fastcgi_module modules/mod_fcgid.so
    <IfModule fastcgi_module>
        FastCgiExternalServer /home/weiyanyan/local/apache/cgi-bin/php-cgi -host 127.0.0.1:9000
        AddType application/x-httpd-php .php
        AddHandler php-fastcgi .php
        Action php-fastcgi /cgi-bin/php-cgi
    </IfModule>

![][2]

> php54是之前是一种关系，php54之后另一种关系。  
> php54之前，php-fpm(第三方编译)是管理器，php-cgi是解释器  
> php54之后，php-fpm(官方自带)，master 与 pool 模式。php-fpm 和 php-cgi 没有关系了。php-fpm又是解释器，又是管理器

    3、以Apache模块模式运行PHP
    
    LoadModule php5_module "C:/php5/php5apache2_2.dll" 
    AddType application/x-httpd-php .php 

[0]: http://www.cnblogs.com/xia520pi/p/3914964.html
[1]: ../../img/bVIJda.png
[2]: ../../img/bVIJdh.png
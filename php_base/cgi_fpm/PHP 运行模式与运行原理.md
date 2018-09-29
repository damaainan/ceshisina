## PHP 运行模式与运行原理

来源：[https://segmentfault.com/a/1190000014913877](https://segmentfault.com/a/1190000014913877)


## 目前常见的4种PHP运行模式

* CGI通用网关接口模式
* FAST-CGI模式
* CLI命令行模式
* 模块模式


## 运行模式
## CGI通用网关接口模式

每有一个用户请求，都会先要创建cgi的子进程，然后处理请求，处理完后结束这个子进程

cgi是一种为了保证web server传递过来的数据是标准格式的通用网关接口协议

比较老，比较原始，大多已经不用了
## FAST-CGI模式

是cgi的升级版本，FastCGI 像是一个常驻 (long-live) 型的 CGI，它可以一直执行着，只要激活后，不会每次都要花费时间去fork 一次，也是一种协议

FastCGI的工作原理是：

　　(1)、Web Server启动时载入FastCGI进程管理器【PHP的FastCGI进程管理器是PHP-FPM(php-FastCGI Process Manager)】（IIS ISAPI或Apache Module);

　　(2)、FastCGI进程管理器自身初始化，启动多个CGI解释器进程 (在任务管理器中可见多个php-cgi.exe)并等待来自Web Server的连接。

　　(3)、当客户端请求到达Web Server时，FastCGI进程管理器选择并连接到一个CGI解释器。Web server将CGI环境变量和标准输入发送到FastCGI子进程php-cgi。

　　(4)、FastCGI子进程完成处理后将标准输出和错误信息从同一连接返回Web Server。当FastCGI子进程关闭连接时，请求便告处理完成。FastCGI子进程接着等待并处理来自FastCGI进程管理器（运行在 WebServer中）的下一个连接。在正常的CGI模式中，php-cgi.exe在此便退出了。

　　在CGI模式中，可以想象 CGI通常有多慢。每一个Web请求PHP都必须重新解析php.ini、重新载入全部dll扩展并重初始化全部数据结构。使用FastCGI，所有这些都只在进程启动时发生一次。一个额外的好处是，持续数据库连接(Persistent database connection)可以工作。
## CLI命令行模式

一般使用调用脚本、查看php信息时会使用到该模式

    php -r"phpinfo();" | less #分页显示

## 模块模式

* Apache  + mod_php
* lighttp + spawn-fcgi
* nginx   + PHP-FPM


## 运行原理

PHP-CGI：fast-cgi是一种协议，而php-cgi是实现了这种协议的进程。不过这种实现比较烂。它是单进程的，一个进程处理一个请求，处理结束后进程就销毁

PHP - FPM：是对php-cgi的改进版，它直接管理多个php-cgi进程/线程。也就是说，php-fpm是php-cgi的进程管理器因此它也算是fastcgi协议的实现
php的运行原理，就是在服务器启动时，自动载入PHP-FPM进程管理器，从而管理多个PHP-CGI进程来准备响应用户的请求，如下图所示：

![][0]

![][1]

由于php-cgi是随服务器启动载入的，所以初始化变量只会发生一次

## 运行模式和运行原理的区别

多个运行模式相当于超市的不同入口，运行原理就是进入超市后的固定的行走路线，通过不同的运行模式进入到底层（进入超市）

[0]: ./img/bVbaJUu.png
[1]: ./img/bVbaJUx.png
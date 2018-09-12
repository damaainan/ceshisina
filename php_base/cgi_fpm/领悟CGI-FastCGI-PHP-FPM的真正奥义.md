# 一步步领悟 CGI FastCGI PHP-FPM 的真正奥义

 时间 2016-02-03 15:07:53  周梦康的博客

原文[http://mengkang.net/664.html][1]


<font face=微软雅黑>

## CGI 模型

CGI 是外部应用程序（ CGI 程序）与 Web 服务器之间的接口标准，是在 CGI 程序和 Web 服务器之间传递信息的规程。

![][4]

###### 图片来源 [http://mengkang.net/491.html][5]


CGI 核心就是其定义的环境变量。 

    SERVER_NAME：运行CGI序为机器名或IP地址。
    SERVER_INTERFACE：WWW服务器的类型，如：CERN型或NCSA型。
    SERVER_PROTOCOL：通信协议，应当是HTTP/1.0。
    SERVER_PORT：TCP端口，一般说来web端口是80。
    HTTP_ACCEPT：HTTP定义的浏览器能够接受的数据类型。
    HTTP_REFERER：发送表单的文件URL。（并非所有的浏览器都传送这一变量）
    HTTP_USER-AGENT：发送表单的浏览的有关信息。
    GETWAY_INTERFACE：CGI程序的版本，在UNIX下为 CGI/1.1。
    PATH_TRANSLATED：PATH_INFO中包含的实际路径名。
    PATH_INFO：浏览器用GET方式发送数据时的附加路径。
    SCRIPT_NAME：CGI程序的路径名。
    QUERY_STRING：表单输入的数据，URL中问号后的内容。
    REMOTE_HOST：发送程序的主机名，不能确定该值。
    REMOTE_ADDR：发送程序的机器的IP地址。
    REMOTE_USER：发送程序的人名。
    CONTENT_TYPE：POST发送，一般为application/xwww-form-urlencoded。
    CONTENT_LENGTH：POST方法输入的数据的字节数。

Web 服务器在接受请求之后对这些环境变量赋值，然后创建一个子进程，在子进程中 CGI 程序通过这些环境变量取值。这个过程就是对 CGI 接口的实现。

举个例子以 C 为 Web 服务器，PHP 作为 CGI 程序。

1. Web 启动 Socket 监听之后，接受到一个客户端的请求

```
    GET /cgi-demo.php?a=b&c=d HTTP/1.1
    Host: localhost:9003
    Connection: keep-alive
    Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
    Upgrade-Insecure-Requests: 1
    User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36
    Accept-Encoding: gzip, deflate, sdch
    Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4,ja;q=0.2
```
2. Web 服务器就可以给环境变量赋值了 

```
    REQUEST_METHOD          GET
    QUERY_STRING            a=b&c=d
    SCRIPT_NAME             /cgi-demo.php
    SERVER_PROTOCOL         HTTP/1.1
    SERVER_NAME             localhost
    SERVER_PORT             9003
```
3. CGI 程序解析获取这些环境变量

```c
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

int main()
{
   putenv("QUERY_STRING=a=b&c=d");

   int pid = fork();

   if (pid == 0)
   {
        system("php cgi-demo.php");
   }

   return 0;
}
```

cgi-demo.php 则在子进程中获取到 web 服务器在父进程设置的环境变量 
```php
<?php
printf("QUERY_STRING:%s\n", getenv("QUERY_STRING"));
```
上面这两段代码仅仅是演示 Web 服务器和 CGI 程序对 CGI 接口的实现，对数据的输入输出都省略没写。完整的Web 服务器 + CGI 程序demo 可以参考 [http://mengkang.net/491.html][5]

## FastCGI 模型

![][6]

FastCGI 的核心则是取缔传统的 fork-and-execute 方式，减少每次启动的巨大开销，以常驻的方式来处理请求。区别于传统的 CGI 是执行脚本从环境变量中换取 CGI 接口定义的值，而 FastCGI 则又多了一层 socket 服务的交互，Web 服务器需要将 CGI 接口数据封装在遵循 FastCGI 协议包中发送给 FastCGI 解析器程序。正式因为 FastCGI 进程管理器是基于 socket 的，所以也是分布式的，所以 Web 服务器和 CGI 程序可以分布部署。

coding...

## PHP-FPM

coding...

<font face=楷体>

#### 大概的介绍可以看

[http://www.php-internals.com/book/?p=chapt02/02-02-03-fastcgi][7]

#### FastCGI协议规范

[http://www.fastcgi.com/devkit/doc/fcgi-spec.html][8]

[http://andylin02.iteye.com/blog/648412][9] （中文版） 

#### FastCGI 进程管理器的 PHP 简单实现

[http://my.oschina.net/goal/blog/196599][10]


</font>
</font>

[1]: http://mengkang.net/664.html

[4]: ./img/mEFnInb.png
[5]: http://mengkang.net/491.html
[6]: ./img/MJnQZby.png
[7]: http://www.php-internals.com/book/?p=chapt02/02-02-03-fastcgi
[8]: http://www.fastcgi.com/devkit/doc/fcgi-spec.html
[9]: http://andylin02.iteye.com/blog/648412
[10]: http://my.oschina.net/goal/blog/196599
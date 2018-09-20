## 通过对nginx中FastCgi进程管理器的学习,了解php动态网站的网页的生成过程、nginx解析php程序的步骤

来源：[https://segmentfault.com/a/1190000016230087](https://segmentfault.com/a/1190000016230087)


## 关于factcgi和cgi的学习

#### 1、FastCGI是什么？  
首先我们看下CGI,CGI全称为通用网关接口 Common Cateway Interface.用于HTTP服务上的程序服务通信交流的一种工具，可以让一个客户端，从网页浏览器向执行在网络服务器上的程序请求数据。CGI描述了服务器和请求处理程序之间传输数据的一种标准。 CGI程序必须运行在网络服务器上。常见的如php-cgi.exe。是php支持CGI标准的执行档。  
CGI的工作原理：每当客户请求CGI的时候，WEB服务器就请求操作系统生成一个新的解释器进程(如php-cgi.exe),当CGI进程完成当前任务结束后，web服务器就会杀死这个进程。  
但是CGI接口方式性能较差，由于每次HTTP服务器遇到动态程序都需要重启解析器来执行解析，拿php举例，每一个web请求,php都必须重新解析php.ini、重新载入全部dll扩展并重新初始化全部数据结构，你可以想象这样多慢。这在处理高并发的问题时，几乎是不可能的。因此诞生了FastCGI.  
所以，CGI解释器的反复加载是CGI性能低下的主要原因，如果CGI解释器保持在内存中并接受FastCGI进程管理器调度，则可以提供良好的性能、伸缩性、Fail-Over特性等等。  

FastCGI:是一个可伸缩、高速的在web server和脚步语言间通讯的接口。其主要行为是将CGI解释器进程保持在内存中并因此获得较高的性能。  

#### 2、FastCGI在web服务器(Nginx)中的工作原理。  
(1)、web Sever 启动时载入FastCGI进程管理器，如php的FastCGI进程管理器是PHP-FPM(php-FastCGI Process Manger).  
(2)、FastCGI进程管理器自身初始化，`启动多个CGI解释器进程`(在任务管理器中可见多个php-cgi.exe)并等待来自web服务器的连接。启动php-cgi FastCGI进程时，可以配置以TCP协议或socker两种方式启动。  
(3)、当客户端请求到达Web Server时，Web Server将请求采用TCP协议或socket方式转发到FastCGI主进程，FastCGI主进程选择并连接到一个CGI解释器(子进程php-cgi.exe)。Web Server将CGI环境变量和标准输入发送到FastCGI子进程php-cgi.exe.  
(4)、FastCGI子进程php-cgi.exe完成处理后将标准输出和错误信息从原来的连接原路返回给web server。当FastCGI子进程关闭连接时，请求便处理完成。但是如果在传统的CGI接口中，此时php-cgi子进程便在此退出了。  
下图所示的是Nginx+FastCGI的运作过程，脚本程序语言是php.  

![][0] 

#### 3、FastCGI的优点  
(1)、php脚本运行速度更快，php解释程序被载入内存而不是每次需要时从存储器读取，极大提升了依靠脚本运行站点的性能。  
(2)、需要使用的系统资源更少，由于服务器不用每次在需要时都载入php解释程序，你可以将站点的传输速度提升很多而不必增加cpu负担。  
(3)、可以把动态语言和HTTP服务器分离开来，同时在脚本解析服务器上启动一个或者多个脚本解析守护进程。多数流行的HTTP服务器都支持FastCGI包括Apache/Nginx/lighttpd等。  
(4)、当HTTP服务器每次遇到动态程序时，可以将其直接交付给FastCGI进程来执行，然后将得到的结果返回给浏览器。这种方式可以让HTTP服务器专一地处理静态请求或者将动态脚本服务器的结果返回给客户端，这在很大程度上提高了整个应用系统的性能。
## 用户对php动态网页的访问过程，以及nginx解析php步骤

用户浏览器发起对网页的访问:[http://192.168.1.103/index.php][1]    
用户和nginx服务器进行三次握手进行TCP连接(还包括nginx访问控制策略、nginx防火墙等访问控制策略)  
第一步：nginx接收到来自服务器的http请求。    
第二步：nginx会根据用户访问的URL和后缀对请求进行判断。  

(1)、例如客户端访问的index.php，nginx则会根据配置文件中的location进行匹配。
例如：

```nginx
server {
    listen 8054;
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }   
    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root  html;
    }
}
```

用户访问的是index.php，则会匹配到location ~.php$，这个的含义是对用户通过url访问的资源进行区分大小的匹配，并且访问的资源是以.php结尾的。  
这里的 fastcgi_pass 127.0.0.1:9000，表示nginx通过fastcgi的接口将http请求发给127.0.0.1:9000进行处理，这个过程就是上面fastcgi运行原理中的第三部。这里的php脚本解析服务和nginx放在同一个服务器上面。    这里我用的是php返回动态的资源，所以这里的FastCGI进程管理器用的是php-fpm。  
(2)、fastcgi_pass将动态资源交给php-fpm后，php-fpm会讲资源转给php脚本解析服务器的wrapper.  
(3)、wrapper收到php-fpm转过来的请求后，wrapper会生成一个新的线程调用php动态程序处理脚本并读取返回数据;比如读取mysql数据库，会触发读库操作。  
(4)、php会将查询处理得到的结果返回给wrapper,一直返回到nginx。最后Nginx将返回的数据发送给客户端。  
参考连接：  
[https://blog.csdn.net/m136663...][2]  
[https://blog.csdn.net/riuhaze...][3]  
[https://www.cnblogs.com/lidab...][4]

[1]: http://192.168.1.103/index.php
[2]: https://blog.csdn.net/m13666368773/article/details/8017673
[3]: https://blog.csdn.net/riuhazen/article/details/78684584
[4]: https://www.cnblogs.com/lidabo/p/7101751.html
[0]: ./img/bVbgfGG.png
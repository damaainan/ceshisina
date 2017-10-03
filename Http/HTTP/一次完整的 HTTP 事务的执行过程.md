##  以 PHP 为例的一次完整的 HTTP 事务的执行过程

## 目录

* [HTTP 事务执行过程][0]
* [HTTP 相关概念术语][1]
* [HTTP 请求与响应][2]
    * [HTTP 请求][3]
        * [HTTP 请求行][4]
        * [HTTP 请求头][5]
        * [HTTP 请求正文][6]
        * [HTTP 请求示例][7]
    * [HTTP 响应][8]
        * [HTTP 响应行][9]
        * [HTTP 响应头][10]
        * [HTTP 响应正文][11]
        * [HTTP 响应示例][12]
* [PHP 和 Web Server 的工作原理][13]
    * [Module][14]
    * [PHP-CGI][15]
    * [PHP-FPM][16]
    * [CGI、FastCGI、PHP-CGI、PHP-FPM 的区别][17]
    * [PHP-FPM 优劣势分析与解决方案][18]
* [Web Server 的配置使用][19]
    * [Apache][20]
        * [Apache 常用命令][21]
        * [Apache 常用的配置与模块][22]
        * [Apache 配置示例][23]
    * [Nginx][24]
        * [Nginx 常用命令][25]
        * [Nginx 常用的模块与配置指令][26]
        * [Nginx 配置示例][27]

## HTTP 事务执行过程

1. 客户端（浏览器）做出请求操作（输入网址、点击链接、提交表单）。
1. 客户端对域名进行解析，向设定的 DNS 服务器请求 IP 地址。
1. 客户端根据 DNS 服务器返回 IP 地址采用三次握手与服务端建立 TCP/IP 连接。
1. TCP/IP 连接成功后，客户端向服务端发送 HTTP 请求。
1. 服务端的 Web Server 会判断 HTTP 请求的资源类型，进行内容分发处理；如果请求的资源为 PHP 文件，服务端软件会启动对应的 CGI 程序进行处理，并返回处理结果。
1. 服务端将 Web Server 的处理结果响应给客户端
1. 客户端接收服务端的响应，并渲染处理结果，如果响应内容需要请求其他静态资源，通过 CDN 加速访问所需资源。
1. 客户端将渲染好的视图呈现出来并断开 TCP/IP 连接

## HTTP 相关概念术语

1.客户端

> 客户端，是指与服务端相对应，为客户提供本地服务的程序。一般安装在普通的用户机上，需要与服务端互相配合运行。Web Application 的客户端一般是浏览器。

2.服务端

> 服务端是为客户端服务的，服务的内容诸如向客户端提供资源，保存客户端数据。

3.三次握手

> 三次握手，又名询问握手协议，是一个用来验证用户或网络提供者的协议。

4.CGI

> CGI 全称是 Common Gateway Interface，CGI 是外部应用程序与 Web Server 之间的接口标准，是在 CGI 程序和 Web 服务器之间传递信息的规程。CGI 规范允许 Web Server 执行外部程序，并将它们的输出发送给Web浏览器。

5.DNS

> DNS 全称是 Domain Name System，译为域名系统。它可以将域名和 IP  
> 地址互相映射，能够让用户使用域名就可以访问互联网服务。

6.HTTP

> HTTP 全称是 HyperText Transfer Protocol，译为超文本传输协议，是一种网络协议。

7.Web Server

> 通常指 Apache、Nginx 等服务器软件

## HTTP 请求与响应

首先从 HTTP 协议开始谈起，HTTP 协议工作流程是客户端发送一个请求给服务端，服务端在接收到这个请求后将产生一个响应返回给客户端。那么 HTTP 请求和 HTTP 响应是什么？

#### HTTP 请求

当客户端向服务端发出请求时，它向服务端传递一个数据块，即请求信息，HTTP 请求信息由三部分组成：

* HTTP Request Line（HTTP 请求行）
* HTTP Request Header（HTTP 请求头）
* HTTP Request Body（HTTP 请求正文）

#### HTTP 请求行

请求行以一个方法符号开头，以空格分开，后面跟着请求的 URI 和协议的版本，格式如下：

    Request Method - URI HTTP-Version CRLF

上述格式中各参数说明如下：

HTTP Request Line | 说明 | 参数 
-|-|-
Method | 请求方法 | GET、POST、PUT、HEAD、DELETE、OPTIONS、TRACE、CONNECT 
Request-URI | 统一资源标识符 | [https://github.com][28] 
HTTP-Version | 请求的 HTTP 协议版本 | HTTP/1.0、HTTP/1.1 
CRLF | 回车和换行 |  

#### HTTP 请求头

HTTP 请求头允许客户端向服务端传递请求的附加信息以及客户端自身的信息。  
每个头域组成形式如下：

    名字 + : + 空格 + 值
    

常见的 HTTP 请求头如下：

* Host

> 头域指定请求资源的 Internet 主机和端口号，必须表示请求 URL 的原始服务器或者网关的位置。

* Accept

> 告诉服务器可以接受的文件格式。

* Cookie

> Cookie 份两种，一种是客户端向服务器发送的，使用 Cookie 头，用来标记一些信息；另一种是服务器发送给浏览器的，头为 Set-Cookie。

* Referer

> 头域允许客户端指定请求 URI 的源资源地址，这可以允许服务器生成回退链表，可用来登录、优化缓存等。

* User-Agent

> UA 包含发出请求的用户信息。通常 User-Agent 包含浏览者的信息，主要是浏览器的名称版本和所用的操作系统。

* Connection

> 表示是否需要持久连接。

* Cache-Control

> 指定请求和响应遵循的缓存机制

* Content-Range

> 响应的资源范围。可以在每次请求中标记请求的资源范围，在连接断开重连时，客户端只请求重连时，客户端只请求该资源未下载的部分，而不是重新请求整个资源。

* Content-Length

> 内容长度

* Accept-Encoding

> 指定所能接受的编码方式。通常服务器会对页面进行 GZIP 压缩后再输出以减少流量。一般浏览器均支持对这种压缩后的数据进行处理。

#### HTTP 请求正文

HTTP 请求头和 HTTP 请求正文之间的一个空行表示请求头已经结束了。请求正文中可以包含提交的查询字符串信息。GET 方式没有请求正文。

#### HTTP 请求示例

HTTP GET 请求

    GET /doc HTTP/1.1
    Host: workerbee.app
    Connection: keep-alive
    Cache-Control: max-age=0
    Upgrade-Insecure-Requests: 1
    User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36
    Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
    Accept-Encoding: gzip, deflate, sdch
    Accept-Language: zh-CN,zh;q=0.8,en;q=0.6
    Cookie: cartalyst_sentry=hello#world
    

HTTP POST 请求

    POST /v1/user/sign-in HTTP/1.1
    Host: workerbee.test.anlewo.com:9351
    Connection: keep-alive
    Content-Length: 25
    Accept: application/json
    Origin: http://workerbee.app
    User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36
    Content-Type: application/x-www-form-urlencoded
    Referer: http://workerbee.app/doc
    Accept-Encoding: gzip, deflate
    Accept-Language: zh-CN,zh;q=0.8,en;q=0.6
    
    username=admin&pwd=123456
    

#### HTTP 响应

服务端在接收和解释请求消息后，服务端会返回一个 HTTP 响应消息。HTTP 响应也由三个部分组成，分别是：

* HTTP Response Line（HTTP 响应行）
* HTTP Response Header（HTTP 响应头）
* HTTP Response Body（HTTP 响应正文）

#### HTTP 响应行

响应行格式如下：

    HTTP - Version Status  - Code Reason - Phrase CRLF
    

上述格式中各参数说明如下：

* **HTTP - Version**：服务端 HTTP 协议的版本。
* **Status-Code**：服务端发回的响应状态代码。 
    * 状态代码由三位数字组成，第一个数字定义了响应的类别，有五种可能取值： 
        * 1XX：提示信息 - 表示请求已被成功接收，继续处理。
        * 2XX：成功 - 表示请求已被成功接收，理解，接受。 
            * 200 OK： 客户端请求成功
        * 3XX：重定向 - 要完成请求必须进行更进一步的处理。 
            * 302 Found：浏览器会使用新的 URL，重新发送 HTTP Request
            * 304 Not Modified：表示上次的资源已经被缓存了，还可以继续使用
        * 4XX：客户端错误 - 请求有语法错误或请求无法实现。 
            * 400 Bad Request：客户端请求有语法错误，不能被服务器所理解
            * 401 Unauthorize：请求未经授权，这个状态代码必须和 WWW-Authenticate 头域一起使用
            * 403 Forbidden：服务器收到请求，但是拒绝提供服务。
            * 404 Not Found：请求资源不存在，例如输入了错误的 URL
        * 5XX：服务器端错误 - 服务器未能实现合法的请求。 
            * 500 Internal Server Error：服务器发生了不可预期的错误。
            * 503 Server Unavailable：服务器当前不能处理客户端的请求，一段时间后可能恢复正常
* **Reason-Phrase**：状态代码的文本描述。

#### HTTP 响应头

HTTP 响应头允许服务器传递不能放在响应行中的附加响应信息，以及关于服务器的信息和对 Request-URI 所标识的资源进行下一步的访问的信息（如 Location）

#### HTTP 响应正文

HTTP 响应正文就是服务端返回的资源内容，HTTP 响应头和 HTTP 响应正文之间也必须使用空行分隔。

#### HTTP 响应示例

    HTTP/1.1 200 OK
    host: localhost:8000
    connection: close
    x-powered-by: PHP/7.0.5
    cache-control: no-cache
    date: Fri, 10 Jun 2016 09:29:07 GMT
    content-type: text/html; charset=UTF-8
    set-cookie: XSRF-TOKEN=eyJpdiI6IlwvKzBIXC9JNzN2YUtpNGNaNndyTG40dz09IiwidmFsdWUiOiJtZlBDOEFQSmNUdzkyMXNQV1NSN2hPVjVFK1FHXC8xVU9nVGMwWFdcL3Q4MWlzc1A0dnhvRlBQckw1YVNpV0hQSTdFODBOQ2FFanF6YVg1TlRiQUhleEtnPT0iLCJtYWMiOiI5ZGRkOWEyYzk3OWY2YzZhOTA5MmFiOTA5ZmFmZmRiNTYxMzA5MjQ4NDdjYjcyNzIzNThjMzAxNmRjNDkzN2UxIn0%3D; expires=Fri, 10-Jun-2016 11:29:07 GMT; Max-Age=7200; path=/
    set-cookie: laravel_session=eyJpdiI6ImdZWUZNOGFKTzV6YWpcL1lPRzRDTHdnPT0iLCJ2YWx1ZSI6IjFxRGRUOG5jRGVSdWJLXC9CMTl6MVJFdVlmaVpER04xb0piMWp3Q3JcL3dMZmR6b3UrU3lpb3FzQTR5QlpoTWxlOE92cmVCbW5iZGZwUUZ1a0d3ZjQrVUE9PSIsIm1hYyI6ImQ5N2M2MjVmYjcxNmE5YzgyY2IyZWJhODhiZTA0NGUxNTdlYmZhYjBkOGEzYzdiNTBiYmJjODE3MWJiOTA5NTIifQ%3D%3D; expires=Fri, 10-Jun-2016 11:29:07 GMT; Max-Age=7200; path=/; HttpOnly
    Transfer-Encoding: chunked
    
    <html>
    <head>
    <title>HTTP 响应示例<title>
    </head>
    <body>
    Hello World!
    </body>
    </html>
    

## PHP 和 Web Server 的工作原理

当服务端接收 HTTP 请求后，服务端的服务器软件，如 Apache、Nginx 则会根据配置处理 HTTP 请求进行分发处理。如果客户端请求的是 index.html 文件，那么 Web Server 会根据配置文件去对应目录下找到这个文件，然后让服务端发送给客户端，这样分发的是静态资源。如果客户端请求的是 index.php 文件，根据配置文件，Web Server 会去找 PHP 解析器来处理。

当 PHP 解析器收到 index.php 这个请求后，会启动对应的 CGI 程序，PHP-CGI 程序就是 PHP 对 CGI 协议的程序实现了。PHP-CGI 程序会解析 php.ini 文件，初始化执行环境，然后处理请求，再以 CGI 规定的格式把结果返回给 Web Server，接着 Web Server 再把结果返回给客户端。

实现 PHP 解析器的方法有三种：

* **Module**：PHP Module 加载方法。
* **PHP-CGI**：PHP 对 Web Server 提供的 CGI 协议的接口程序。
* **PHP-FPM**：PHP 对 Web Server 提供的 FastCGI 协议的接口程序，额外提供了相对智能的任务管理。

以上三种实现方法都是本质都是通过 PHP 的 SAPI 层调用 PHP 执行的。

#### Module

在了解 CGI 之前，我们先了解一下 PHP Module 加载方式。以 Apache 为例，在 Apache 的配置文件 httpd.conf，我们可以查找到以下语句：

    LoadModule php7_module  /usr/local/opt/php70/libexec/apache2/libphp7.so
    
    <FilesMatch .php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    <IfModule dir_module>
        DirectoryIndex index.php index.html
    </IfModule>

这种方法，本质上是使用 Apache 的 LoadModule 来加载 php7_module，也就是把 PHP 作为 Apache 一个子模块来运行。当客户端请求 PHP 文件时，Apache 就会调用 php7_module 来解析 PHP 代码。

这种模式将 PHP 模块安装到 Apache 中，每一次 Apache 接收请求时，都会产生一条 Apache 进程，这条进程就完整的包括 PHP 的运算计算，数据读取等各种操作。

由于每次请求都需要产生一条进程来连接 SAPI 来完成请求，一旦并发数过高，服务端就会承受不住。而且把 php7_module 加载进 Apache 中，出现问题时很难定位是 PHP 的问题 还是 Apache 的问题。

#### PHP-CGI

PHP-CGI 是使用 PHP 实现 CGI 接口的程序。当 Web Server 接收到 PHP 文件请求时，会分发给 CGI 程序处理，CGI 程序处理完毕后，会返回结果给 Web Server，Web Server 再返回给客户端。

PHP-CGI 的好处就是完全独立于 Web Server，只是作为中间层，提供接口给 Apache 和 PHP，它们通过 CGI 来完成数据传递。这样做的好处就减少了两者之间的关联，出现错误时能够较好地定位。

但是 PHP-CGI 采用的是 fork-and-execute 模式，就是每次请求都会有进程启动和退出的过程，在高并发下，Web Server 容易奔溃。

#### PHP-FPM

在了解 PHP-FPM 之前，我们先来了解一下 FastCGI。

从根本上来说，FastCGI 是用来提高 CGI 程序性能的。类似于 CGI，FastCGI 也可以说是一种协议。

FastCGI 像是一个常驻（long-live）型的 CGI，它可以一直执行着，只要激活后，不会每次都要花费时间去 fork 一次。它还支持分布式的运算, 即 FastCGI 程序可以在网站服务器以外的主机上执行，并且接受来自其它网站服务器来的请求。

FastCGI 是语言无关的、可伸缩架构的 CGI 开放扩展，其主要行为是将 CGI 解释器进程保持在内存中，并因此获得较高的性能。众所周知，CGI 解释器的反复加载是 CGI 性能低下的主要原因，如果 CGI 解释器保持在内存中，并接受 FastCGI 进程管理器调度，则可以提供良好的性能、伸缩性、Fail- Over 特性等等。

FastCGI 接口方式采用 C/S 结构，可以将 Web Server 和脚本解析服务器分开，同时在脚本解析服务器上启动一个或者多个脚本解析守护进程。当 Web Server 每次遇到动态程序请求时，可以将其直接交付给 FastCGI 进程来执行，然后将得到的结果返回给浏览器。这种方式可以让 Web Server 专一地处理静态请求，或者将动态脚本服务器的结果返回给客户端，这在很大程度上提高了整个应用系统的性能。

PHP-FPM 是对 FastCGI 协议的具体实现，它负责管理一个进程池，来处理 Web Server 的请求。它自身并不直接处理请求，它会交给 PHP-CGI 去进行处理。因为 PHP-CGI 只是一个 CGI 程序，它只会解析请求，返回结果，不会管理进程。PHP-FPM 是用于调度管理 PHP 解释器 PHPCGI 的管理程序。

#### CGI、FastCGI、PHP-CGI、PHP-FPM 的区别

* **CGI**：一种协议，是外部应用程序与 Web Server 之间的接口标准，是在 CGI 程序和 Web Server 之间传递信息的规程。
* **FastCGI**：一种协议，通过守护进程来管理 CGI 程序，将 CGI 程序常驻于内存中，不必每次处理请求重新初始化 php.ini 和其他数据，提高 CGI 程序的性能。其本身并不处理动态文件，只是负责进程的调度。
* **PHP-CGI**：使用 PHP 实现 CGI 协议的程序，用于解析和处理 PHP 脚本。
* **PHP-FPM**：是一个只用于 PHP 的进程管理器，提供更好的 PHP 进程管理方式，可以有效控制进程，平滑地加载 PHP 配置文件。

#### PHP-FPM 优劣势分析与解决方案

###### 优劣势分析

PHP-FPM 的优势有许多，如实现了 FastCGI 协议来处理 HTTP 请求，有着更高的性能和更小的开销。每当 PHP-FPM 的子进程处理完 HTTP 请求后，相关资源自动销毁，不用担心资源回收的问题。哪怕在运行的过程中报错了，也不影响其他子进程的运行，使得开发者不用过分担心未知的错误而进行面面俱到的防御性编码。在低负载时硬件开销低，只需要 fork 多几个子进程就满足日常访问量。

PHP-FPM 的劣势也很明显。首先是不适合高并发和高负载的场景。因为在高负载的情况，由于 PHP-FPM 多进程模型，会显得子进程的初始化开销过高。加上进程、文件句柄、请求连接等，会使得内存过多地消耗，而 CPU 也得不到充分的利用。又因为子进程的开销过高，导致服务器的内存容易饱满，使得网络连接数过低。由于 PHP-FPM 在完成请求会自动销毁资源，因此也不支持 Websocket 或其他长连接协议。

###### 解决方案

针对 PHP 高初始化开销这个问题，可以使用 C 扩展的框架来重写相关业务，如 Phalcon、Yaf。针对 PHP 高内存开销，设置好短连接、短超时，和使用并行化 RPC 来尽快处理请求。而针对地连接数，只能是加大内存，增加服务器了。不支持 Websocket，只能使用 Nginx 进行反向代理，将 Websocket 等或其他长连接交给服务器其他 Web Server 处理。如 PHP 的 Swoole 或 C/C++/Go/Java 等语言编写的 Web Server。

## Web Server 的配置使用

#### Apache

Apache HTTP Server（简称Apache）是 Apache 软件基金会的一个开放源代码的网页服务器软件，可以在大多数电脑操作系统中运行，由于其跨平台和安全性被广泛使用，是最流行的Web服务器软件之一。它快速、可靠并且可通过简单的API扩充，将 PHP／Perl／Python 等解释器编译到服务器中。

#### Apache 常用命令

    httpd                       # 启动 Apache
    httpd -h                    # 显示帮助
    httpd -v                    # 显示版本信息
    httpd -V                    # 显示版本，编译器版本和配置参数
    httpd -t                    # 检查配置文件语法错误
    httpd -l                    # 显示编译模块
    httpd -L                    # 显示配置指令说明
    httpd -f </path/to/config>  # 指定配置文件
    httpd -S                    # 显示配置文件中的设定

#### Apache 常用的配置与模块

* **Options**（配置选项） 
    * **Indexes**：目录浏览，是否允许在目录下没有 index.html,index.php 的时候显示目录。
    * **Multiviews**：文件匹配，服务器执行一个隐含的文件名模式匹配，并在其结果中选择。
    * **FollowSymLinks**：符号链接，是否允许通过符号链接跨越 DocumentRoot 访问其他目录。
* **AllowOverride**（是否允许根目录下的 .htaccess 文件起到 URL rewrite 的作用） 
    * **All**
    * **None**
* **LoadModule**（加载模块）
* **Listen**（Apache 默认监听端口）
* **ServerRoot**：Apache 安装的基础目录
* **DocumentRoot**：Apache 缺省文件根目录
* **DirectoryIndex**：网站默认首页文件
* **LogLevel**：日志级别设置
* **ErrorLog**：错误日志存放路径
* **CustomLog**：访问日志存放路径
* **VirutalHost**（虚拟主机与配置参数） 
    * **ServerName**：虚拟域名
    * **ServerAlias**：虚拟域名的别名
    * **ServerAdmin**：服务器管理员邮箱
    * **DocumentRoot**：项目代码根目录
    * **LogLevel**：日志级别设置
    * **ErrorLog**：错误日志存放路径
    * **CustomLog**：访问日志存放路径
* **MPM**（工作模式/多处理模块） 
    * **Prefork**
        * **StartServers**：服务器启动时默认开启的进程数
        * **MinSpareServers**：最小的空闲进程数
        * **MaxSpareServers**：最大的空闲进程数
        * **ServerLimit**：在Apache的生命周期内，限制MaxClients的最大值
        * **MaxClients**：最大的并发请求数，最大值不能超过 ServerLimit 设置的值
        * **MaxRequestsPerChild**：一个进程可以处理的最多的请求数（进程复用），如请求超过该设置则杀死进程，0表示永不过期。
    * **Worker**
        * **StartServers**: 服务器启动时默认开启的进程数
        * **MaxClients**: 最大的并发请求数
        * **MinSpareThreads**: 最小的线程空闲数
        * **MaxSpareThreads**: 最大的线程空闲数
        * **ThreadsPerChild**: 每一个进程可以产生的线程数
        * **MaxRequestsPerChild**: 一个线程可以处理的最多的请求数（线程复用），如请求超过该设置则杀死线程，0表示永不过期。
    * **Event**
* **Order, Deny, Allow**（认证，授权与访问控制）
* **HostnameLookups off**（避免 DNS 查询）
* **Timeout**（请求超时）
* **Include**（文件包含）
* **Proxy、mod_proxy**（代理）
* **Cache Guide**（缓存）

#### Apache 配置示例

Laravel .htaccess 文件的 URL rewrite 示例配置

    <IfModule mod_rewrite.c>
        <IfModule mod_negotiation.c>
            Options -MultiViews
        </IfModule>
    
        RewriteEngine On
    
        # Redirect Trailing Slashes If Not A Folder...
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)/$ /$1 [L,R=301]
    
        # Handle Front Controller...
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </IfModule>

配置虚拟主机的 httpd-vhosts.conf 文件的示例配置

    <VirtualHost *:80>
        ServerName cowcat.app
        ServerAdmin cowcat.app
        ServerAlias www.cowcat.app
        DocumentRoot "/Users/LuisEdware/Code/WorkSpace/CowCat/public"
        ErrorLog "/Users/LuisEdware/Code/Errors/cowcat.app-error_log"
        CustomLog "/Users/LuisEdware/Code/Errors/cowcat.app-access_log" common
    </VirtualHost>
    
    <VirtualHost *:80>
       ServerName frontend.dev
       DocumentRoot "/Users/LuisEdware/Code/yii2-advanced-api/frontend/web/"
    
       <Directory "/Users/LuisEdware/Code/yii2-advanced-api/frontend/web/">
           # use mod_rewrite for pretty URL support
           RewriteEngine on
           # If a directory or a file exists, use the request directly
           RewriteCond %{REQUEST_FILENAME} !-f
           RewriteCond %{REQUEST_FILENAME} !-d
           # Otherwise forward the request to index.php
           RewriteRule . index.php
    
           # use index.php as index file
           DirectoryIndex index.php
    
           # ...other settings...
           # Apache 2.4
           Require all granted
    
           ## Apache 2.2
           # Order allow,deny
           # Allow from all
       </Directory>
    </VirtualHost>

#### Nginx

Nginx 是一款面向性能设计的HTTP服务器，相较于 Apache、lighttpd 具有占有内存少，稳定性高等优势。与旧版本（<=2.2）的 Apache 不同，Nginx 不采用每客户机一线程的设计模型，而是充分使用异步逻辑，削减了上下文调度开销，所以并发服务能力更强。整体采用模块化设计，有丰富的模块库和第三方模块库，配置灵活。 在 Linux 操作系统下，Nginx 使用 epoll 事件模型，得益于此，Nginx 在 Linux 操作系统下效率相当高。

#### Nginx 常用命令

    nginx                       # 启动 Nginx
    nginx -h                    # 显示帮助
    nginx -c </path/to/config>  # 指定一个 Nginx 配置文件，以代替缺省的。
    nginx -t                    # 不运行，仅仅测试配置文件语法的正确性。
    nginx -v                    # 显示 Nginx 的版本。
    nginx -V                    # 显示 Nginx 的版本，编译器版本和配置参数。
    nginx -s reload             # 更改了配置后无需重启 Nginx，平滑重启。
    nginx -s stop               # 停止 Nginx

#### Nginx 常用的模块与配置指令

Nginx 的模块从功能角度主要可以分为以下三类：

* **Handler** 模块

> 主要负责处理客户端请求并产生待响应内容，比如 > ngx_http_static_module> 模块，负责客户端的静态页面请求处理并将对应的磁盘文件准备为响应内容输出。

* **Filter** 模块

> 主要负责对输出的内容进行处理，可以对输出进行修改，如 > ngx_http_not_modified_filter_module> ,> ngx_http_header_filter_module>  模块。

* **Upstream** 模块

> 实现反向代理的功能，将真正的请求转发到后端服务器上，如 > ngx_http_proxy_module> 、> ngx_http_fastcgi_module>  模块。

Nginx 常见的配置指令如下：

* **main**：Nginx 在运行时与具体业务功能无关的一些参数，比如工作进程数，运行的身份等 
    * **user**：定义用户和用户组
    * **worker_processes**：Nginx 进程数，建议设置为等于 CPU 总核心数
    * **worker_cpu_affinity**：为每个进程分配 CPU
    * **error_log**：全局错误日志定义类型
    * **events**：定义 Nginx 事件工作模式与连接数上限等参数
    * **http**：提供 HTTP Server 相关的一些配置参数，如是否使用 keepalive、是否使用 gzip 进行压缩等
    * **mall**：提供 Mail Server 相关的一些配置参数，如实现 email 相关的 SMTP/IMAP/POP3 代理
* **events**
    * **use**：参考事件模型
    * **worker_connections**：单个进程最大连接数
* **http**
    * **server**：http 服务上支持若干虚拟主机。每个虚拟主机一个对应的 server 配置项，配置项里面包含该虚拟主机相关的配置。在提供 mail 服务的代理时，也可以建立若干 server.每个 server 通过监听的地址来区分
* **server**
    * **listen**：监听端口
    * **server_name**：域名
    * **access_log**：访问日志
    * **location**：HTTP 服务中，某些特定的 URL 对应的一系列配置项
    * **protocol**：
    * **proxy**：正向代理与反向代理
* **location**
    * **root**
    * **index**
* **mail**
    * **server**
* **FastCGI 相关参数**
    * **fastcgi_connect_timeout**
    * **fastcgi_send_timeout**
    * **fastcgi_read_timeout**
    * **fastcgi_buffer_size**
    * **fastcgi_buffers**
    * **fastcgi_busy_buffers_size**
    * **fastcgi_temp_file_write_size**
* **gzip 模块设置**
    * **gzip**：开启 gzip 压缩输出
    * **gzip_min_length**：最小压缩文件大小
    * **gzip_buffers**：压缩缓冲区
    * **gzip_http_version**：压缩版本（默认1.1，前端如果是squid2.5请使用1.0）
    * **gzip_comp_level**：压缩等级
    * **gzip_types**：压缩类型
    * **gzip_vary**：根据客户端的 HTTP 头来判断是否需要压缩;

#### Nginx 配置示例

Laravel 5.1 项目的配置示例

    server{
        listen 80;
        server_name october.com www.october.com;
    
        # 日志记录
        acomess_log /var/log/nginx/october.com.acomess.log  main;
        error_log /var/log/nginx/october.com.error.log;
    
        location / {
            root /data/www/prod/october/public;
            try_files $uri $uri/ /index.php?$query_string;
            index index.php index.html index.htm;
        }
    
        # 错误页面
        error_page 404              /404.html;
        error_page 500 502 503 504  /50x.html;
        location = /50x.html {
            root   /usr/share/nginx/html;
        }
    
        #PHP 脚本请求全部转发到 FastCGI 处理. 使用 FastCGI 默认配置.
        location ~ \.php$ {
            root /data/www/prod/october/public;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    }

反向代理

    server {
        listen 80;
        server_name gogs.october.com;
    
        location / {
            proxy_pass http://localhost:3000;
        }
    }

[0]: #1
[1]: #2
[2]: #3
[3]: #3.1
[4]: #3.1.1
[5]: #3.1.2
[6]: #3.1.3
[7]: #3.1.4
[8]: #3.2
[9]: #3.2.1
[10]: #3.2.2
[11]: #3.2.3
[12]: #3.2.4
[13]: #4
[14]: #4.1
[15]: #4.2
[16]: #4.3
[17]: #4.4
[18]: #4.5
[19]: #5
[20]: #5.1
[21]: #5.1.3
[22]: #5.1.1
[23]: #5.1.2
[24]: #5.2
[25]: #5.2.3
[26]: #5.2.1
[27]: #5.2.2
[28]: https://github.com
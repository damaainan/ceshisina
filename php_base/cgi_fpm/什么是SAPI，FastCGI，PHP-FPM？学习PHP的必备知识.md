## 什么是SAPI，FastCGI，PHP-FPM？学习PHP的必备知识

来源：[http://www.jianshu.com/p/38cb8ffa4f23](http://www.jianshu.com/p/38cb8ffa4f23)

时间 2018-09-17 10:24:03

 
一个月前，我想在阿里云 ECS 上部署一个 PHP 接口，发现服务器没有配置 PHP-FPM，所以立刻捣鼓了下，没想到是最后花了一小时才搞定，事后分析了下，就是太急躁了，没有使用正确的方法解决问题。
 
一个教训：不管遇到任何事情，切记不能着急，仔细查阅文档才是正道。
 
废话少说，趁着这次机会，我回顾了相关概念，即了解 Nginx、PHP-FPM 是如何协作的，介绍 PHP-FPM 和 PHP 之间的关系，SAPI 和 FastCGI 的区别，理解这些概念对于掌握 PHP 非常重要。
 
后续我也会针对PHP-FPM的配置做一些简单的分享，比如分析本次遇到的问题以及原因，大家可以持续关注。至于为什么不在一篇文章中全部写完，主要考虑到干巴巴罗列知识点，效果可能会比较差，针对特定问题描述的话，读者印象会更深刻。
 
1：什么是 SAPI
 
Server Application Programming Interface (SAPI) 是应用程序接口，对于 PHP 语言来说，它提供了很多 SAPI 接口，有了 SAPI，PHP 才有实际的用武之处。PHP 中最重要的 SAPI 是 PHP-FPM，提供给 Nginx Web 服务器使用，换句话说，有了应用语言的 SAPI，才能扩展 Web 服务器的功能。
 
对于 PHP 来说，它有以下一些 SAPI，如图：

![][0]

 
上图就是 PHP5 相关的 SAPI，比较熟悉的就是 PHP-FPM，还有命令行的 php-cli，在 windows 下 SAPI 就是 php5apache2.dll。
 
2：FastCGI
 
对于 PHP-FPM 来说：

* 实现了 PHP 解析器 
* 基于 FastCGI 协议，负责和 Web 服务器（Nginx、Apache）通信，那什么是 FastCGI？ 

```
FastCGI is a binary protocol for interfacing interactive programs with a web server.
```
 
那么我们来理解下 FastCGI 协议，简单说来它就是 Web 服务器和应用（比如 PHP）之间的一个交互标准，一个二进制的协议，有了该协议，Nginx 和 PHP 之间就能够互相通信了，FastCGI 是 CGI 协议的一个升级。
 
光有 FastCGI 协议没用，基于该协议，必须实现一个 SAPI 接口，PHP-FPM 就是一个 FastCGI 协议的实现，它能够在一组关联的请求中保持一个持久连接（同一个客户请求由同一个 PHP-FPM 子进程处理），这个持久连接是由 PHP-FPM 处理的，而不是由 Web 服务器处理的。
 
相比于 CGI 实现来说，FastCGI 实现能够减少开销，从而提升 Web 服务器的处理能力。
 
一个 Web 请求如下图：

![][1]

 
Nginx 服务器通过 FastCgi 协议，发送环境变量和 HTTP 数据给 PHP-FPM，Nginx 和 PHP-FPM 之间可以通过 Unix domain socket 和 TCP connection 通信。PHP-FPM 处理请求后，通过相同的连接返回数据给 Nginx。
 
通过上图也可以看出，Nginx 和 PHP-FPM 是互相隔离的，也是异步处理的，这也正是 Nginx 高效的原因，关于这方面可以通过一些专业文章去了解。Apache 最初使用 mod_php SAPI 处理请求（高度集成），这也是它缓慢的原因，但是现在 Apache 通过 FastCGI 协议也能和 PHP-FPM 通信了。
 
3：PHP-FPM

```
PHP-FPM (FastCGI Process Manager) is an alternative FastCGI implementation for PHP。
```
 
PHP-FPM 刚才讲了很多了，一方面它基于 FastCGI 协议实现了协议的功能，另外一方面它也集成了 PHP 解析器。
 
PHP-FPM 由一个主进程和多个子进程组成​，主进程复制与 Web 服务器通信，接收 HTTP 请求，然后分配给子进程处理，子进程主要动态执行 PHP 语言，处理完成后，最终返回给 Web 服务器。
 
PHP-FPM 有很多优点，比如：

 
* 能够动态产生子进程（PHP解析器）。 
* 能够平滑启动子进程。 
* 有独立的 php-fpm.conf 配置文件，它基于 php.ini 配置文件。 
* fastcgi_finish_request() 功能支持，非常有用的特性。 
 
 
总之，一句话，对于大型的 PHP 网站来说，PHP-FPM 做了足够多的优化。
 
4：典型的 Nginx 和 PHP-FPM 配置
 
安装和启动 PHP-FPM 很简单，以 Ubuntu 服务器为例，运行如下命令即可：

```
$ apt-get install php5-fpm
$ service php5-fpm start
```
 
通过 Nginx 配置文件了解 Nginx 和 PHP-FPM 的交互细节：

```nginx
server {
    listen 80 ;
    server_name  www.simplehttps.com ;
    location ~ \.php$ {
        root           /usr/share/nginx/html;
        #fastcgi_pass   127.0.0.1:9000;
        fastcgi_pass   unix:/var/run/www.simplehttps.com-fpm.sock;
        fastcgi_index  index.php;
        include        fastcgi_params;

        fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
    }
｝
```
 
通过配置可以看出：

 
* Nginx 可以通过 9000 端口或本地 socket 文件和 PHP-FPM 交互。 
* fastcgi_params 包含了很多 Web 服务器参数，比如 REMOTE_ADDR、QUERY_STRING 等等。 
 
 
最后看下 PHP-FPM 文件结构，如图：

![][2]

 
* `conf.d`：一些 php 通用扩展配置文件。（属于 PHP 的部分） 
* `php.ini`：PHP 核心配置文件。（属于 PHP 的部分） 
* `php-fpm.conf`：fpm 的主配置文件，主要是 PHP-FPM 主进程使用。 
* `pool.d`：该目录下加载的配置文件类似于 Web 服务器中的虚拟主机配置，由 PHP-FPM 子进程处理。 
 
 
关于 php-fpm.conf 和 pool.d 多说几句：
 
（1）php-fpm.conf 是 PHP-FPM 的**主配置文件**，都是**全局性配置**，但配置项较少，比如包含 pid、error_log、events.mechanism 等参数，理解起来很简单。
 
（2）pool.d 目录可以包含多个虚拟主机配置文件，由 php-fpm.conf 负责加载。
 
比如我一台机器上 Nginx 配置了两个虚拟主机，分别是 [www.simplehttps.com][3] 和 [blog.simplehttps.com][4] 。这两个虚拟主机可以加载不同的 PHP-FPM，比如 [www.simplehttps.com-fpm.sock][5] 和 [blog.simplehttps.com-fpm.sock][6] ，那么这两个 PHP-FPM 可以使用不同的配置文件（保存在 pool.d 目录下），配置文件里面的参数可以自由调整，以后我会写文章详细介绍。


[3]: http://www.simplehttps.com
[4]: http://blog.simplehttps.com
[5]: http://www.simplehttps.com-fpm.sock
[6]: http://blog.simplehttps.com-fpm.sock
[0]: ./img/zyaAJrY.png
[1]: ./img/iuAJ7zY.png
[2]: ./img/M3EBJ3.png
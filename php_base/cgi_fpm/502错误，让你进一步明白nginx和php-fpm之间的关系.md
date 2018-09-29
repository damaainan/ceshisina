## 502错误，让你进一步明白nginx和php-fpm之间的关系

来源：[http://www.jianshu.com/p/962413287967](http://www.jianshu.com/p/962413287967)

时间 2018-09-19 10:32:35


[《什么是SAPI，FastCGI，PHP-FPM？学习PHP的必备知识》][0]
这篇文章讲解了一些基础知识，写这篇文章的根本原因是自己在部署Nginx+PHP-FPM的时候遇到了二个问题。今天我就回顾下当时遇到的一个502错误（另外一个问题有机会再讲），以及最后的解决方法，大家也别小看这个502错误，充分理解非常必要，希望通过这篇文章大家能够学到一些知识。

在我这个案例中，Nginx通过FastCGI协议连接PHP-FPM（7.1），Nginx和PHP-FPM部署在同一台机器上，配置完成后，在浏览器中访问，报了一个 502 错误。

首先引用下百科对于 502 的介绍：

```
The server was acting as a gateway or proxy and received an invalid response from the upstream server.


```

它的意思就是Nginx没有获取到PHP-FPM的响应。

我当时处理的比较着急，花了很久的时间，此处忽略各种的排查过程，先贴下最后正确的配置（和本次问题有关的）。

（1）nginx.conf：

```ini
error_log  logs/error.log;
user www-data www-data;
```

（2）php-fpm.conf：

```ini
access.log = /var/log/fpm.log
```

（3）pool.d/[www.conf][1]（PHP-FPM pool 配置文件）:

```ini
user = www-data
group = www-data

listen = /run/php/php7.1-fpm.sock

listen.owner = www-data（错误产生时的配置：listen.owner = www）
listen.group = www-data（错误产生时的配置：listen.owner = www）
listen.mode = 0660
access.log = /var/log/fpm-www.access.log
```

再解释下 pool 配置文件，一般情况下，nginx 一个虚拟主机对应一个 php-fpm pool 文件，这样不同的 php-fpm 工作进程就隔离了，互不影响。

  
#### 接下去介绍分析过程：

1：在出现 502 问题的时候，观察 nginx 的 error.log 文件，会有以下报错：

```
2018/09/18 18:34:32 [crit] 2831#0: *493 connect() to unix:/run/php/php7.1-fpm.sock failed (13: Permission denied) while connecting to upstream, client: 18.179.21.152, server: www.simplehttps.com, request: "GET /1.php HTTP/1.1", upstream: "fastcgi://unix:/run/php/php7.1-fpm.sock:", host: "www.simplehttps.com"
```

其实说的已经很明白了，连接 PHP-FPM 的时候遇到了权限问题。

2：观察下 php 主进程的 error.log

发现 /var/log/fpm.log 文件没有任何的输出，查阅了官方资料，对于 `error_log` 这个指令解释的非常少。

我猜测有两种作用：


* 每个 pool 的错误会重定向到这个文件中。（经过测试，pool错误和这个文件没有关系）
* PHP-FPM 主进程的一些控制错误。（从本案例来说，主进程并不知道Nginx遇到了错误，所以也没有错误输出）
    

最后，php-fpm.conf 下的 `error_log` 指令在我看来没有任何的实际用处，如果读者有知道的，欢迎指导。

（3）定位问题

知道了nginx通过本地socket方式连接php-fpm遇到权限问题，定位到了listen.owner和 listen.group指令。

产生问题的原因就是nginx进程的属主和php-fpm属主权限不一样，在发生502问题的时候，nginx属主是www-data，而listen.owner是www。把它们修改一致后，问题解决。

我们不禁要问，listen.owner和listen.group指令表示什么？看官方的介绍：

```ini
; Set permissions for unix socket, if one is used. In Linux, read/write
; permissions must be set in order to allow connections from a web server. Many
```

它们表示php-fpm工作进程以unix socket和web服务器连接的时候，该socket的权限必须和web服务器的操作（读取）权限一致。

读者大概明白了什么意思，那user和group这两个指令什么意思，为什么和listen.owner指令如此相像，官方是这么介绍的：

```ini
; Unix user/group of processes
```

解释的不是很清楚，实际上表明的是这个php-fpm进程本身权限（通过 ps aux | grep php-fpm就能进一步确认），如果php-fpm要传递错误数据给nginx，那么user和group的指令必须和nginx的user指令配置一样（以后会写文章说明）。

这也间接说明了，如果nginx的user指令和php-fpm工作进程的listen.user指令配置不一样，也不影响两者交互。只是在本机中，nginx和php-fpm如果要读取或操作同一文件，需要配置一致，关于这一点希望大家仔细体会。

实际上最简单的解决方案，就是通过tcp的方式连接nginx和php-fpm（即[www.conf][1]配置`listen = 127.0.0.1:9000`），这样不会有权限操作的问题，但对于本机来说，**`socket连接相比tcp连接，速度上更有保证`**。

【本文2018/09/19 发表于[https://mp.weixin.qq.com/s/keJuNwnZu2ejnZqCvXxy3A][3]，也可以关注我的新书[《深入浅出HTTPS：从原理到实战》][4]】


[0]: https://mp.weixin.qq.com/s/strSY6y8M_CO5xb47o0gbA
[1]: http://www.conf
[2]: http://www.conf
[3]: https://mp.weixin.qq.com/s/keJuNwnZu2ejnZqCvXxy3A
[4]: https://mp.weixin.qq.com/s/80oQhzmP9BTimoReo1oMeQ
## 如何通过 nginx、php-fpm、PHP 的日志调试程序

来源：[https://mp.weixin.qq.com/s/CPsjXITMHfpIWV2ylxknrA](https://mp.weixin.qq.com/s/CPsjXITMHfpIWV2ylxknrA)

时间 2018-10-05 07:55:24

 
最近写了几篇关于504和502的文章，涉及了很多nginx、php-fpm、php方面的细微知识，这些理论虽然简单，但对于理解php和http非常重要。熟悉的同学知道，在工作上我主要使用php开发，而开发过程中，调试是非常关键的一个步骤，出现一个问题，快速定位到问题非常关键，所以今天简单区分下nginx、php-fpm、php三者之间的访问日志（access.log）错误日志（error.log），理解它们，后续开发的时候会更加顺利。
 
通过下图，我们能够了解到这三者之间可能有四种错误日志，如果不理解这张图的结构，可以看下我以前写的文章。
 
![][0]
 
在这四个层面（nginx、php-fpm主进程、php-fpm pool工作进程、php）都有与日志有关的指令，接下去分别描述。
 
### php.ini
 
每一个php解析器都有一个php.ini，该文件定义了很多php的默认行为，从日志的角度看，有三个指令很重要。

```ini
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors=off
error_log=/var/log/phperror.log
```
 
当display_errors=off的时候，php解析的时候如果出现错误（语法错误、异常等），则不会在页面或命令行中不会打印错误，在生产环境中，一般将该指令设置为off，否则出现错误的时候，访问者体验很不好，更严重的时候，泄漏了很多隐私数据，比如打印出数据库的用户名和密码。
 
display_errors关闭的时候，我们怎么知晓php产生了那些错误呢？此时可以借助error_log，该指令指定了一个文件，如果发生错误的时候，就会将错误输出到这个文件中。
 
error_reporting则指定了输出那些类型的错误，E_ALL表示输出所有错误，而E_ALL & ~E_DEPRECATED表示除了E_DEPRECATED级别的错误不输出，其他错误全部输出，关于这个指令，也够我们学一阵子的，但这不是本文的重点。
 
需要指出的是，如果display_errors=on，一旦产生php错误或异常，nginx（包括其他web服务器）会返回200 http状态码，并且在页面中输出错误；但如果display_errors=off的时候，产生php错误或异常的时候，nginx会直接返回500 http错误码。
 
### nginx
 
对于nginx来说，可以通过以下指令控制访问日志和错误日志，非常的简单：

```nginx
access_log  logs/access.log  main;
error_log  logs/error.log;
```
 
对于一个web服务器来说，nginx通过fastcgi连接后端php-fpm的时候，如果产生fastcgi协议级别的错误（比如以前文章中谈到的504、502），会记录在error.log中，比如：

```
2018/09/19 21:01:19 [error] 17034#0: *253 upstream timed out (110: Connection timed out) while reading response header from upstream, client: *.*.*.*, server: www.simplehttps.com, request: "GET /x.php HTTP/1.1", upstream: "http://*.*.*.*:80/x.php", host: "www.simplehttps.com"
```
 
当nginx在内部处理的时候，遇到一些错误（比如ssl异常），也会记录在error.log中，比如：

```
2018/10/02 10:50:00 [crit] 29363#0: *122 SSL_do_handshake() failed (SSL: error:14209102:SSL routines:tls_early_post_process_client_hello:unsupported protocol) while SSL handshaking, client: 182.132.25.13, server: 0.0.0.0:443
```
 
当nginx访问一个后端不存在的程序时，也会记录在error.log中，比如:

```
2018/04/18 21:36:31 [error] 31891#0: *55 open() "/usr/local/index.php" failed (2: No such file or dir ectory), client: 116.62.209.27, server: localhost, request: "GET /index.php HTTP/1.1", host: "",
```
 
读者可能会想，php输出的错误能够记录到nginx日志中吗，答案是可以的，具体等会说。
 
### php-fpm pool 日志
 
在 [《什么是SAPI，FastCGI，PHP-FPM？学习PHP的必备知识》][2] 这篇文章提到php-fpm pool是个非常巧妙的方式，类似于web服务器的虚拟主机，多个php进程池之间相互隔离，但是php.ini只能有一个，如果某个应用（www.test.com）想打开php错误日志（即display_errors指令），而另外个应用（www.test.cn）却想关闭php错误日志，怎么办？或者如何在不同的应用之间定义不同的错误日志路径（即error_log）,怎么办？
 
聪明的php-fpm想到了一个办法，具体的指导策略还是隔离，既然php pool可以分为多个，那么每个pool可以继承和覆盖php.ini的指令，是否可行？
 
可以的，某些php.ini指令可以覆盖，但某些指令不可以（全局的），对于本文要描述的php三个指令，都可以覆盖重载。
 
比如 fpm/pool.d/www.test.com.conf 文件定义如下：

```cfg
error_log=/var/log/test.com-error.log
```
 
而 fpm/pool.d/www.test.cn.conf 文件定义如下：

```cfg
error_log=/var/log/test.cn-error.log
```
 
好处是什么？这样我们能够清晰的区分不同应用的错误，更重要的观点就是pool继承了php.ini，每个pool的php配置（php.ini）是独一无二的。
 
接下去的是重点，php-fpm pool 还多了一些php.ini没有的指令，nginx和php-fpm 工作进程（pool）之间通过fastgcgi协议交互，php-fpm为了增加灵活度，增加了一些指令，本文讲解的就是 catch_workers_output，查看该指令的定义：

```
; Redirect worker stdout and stderr into main error log. If not set, stdout and ; stderr will be redirected to /dev/null according to FastCGI specs. ; Note: on highloaded environement, this can cause some delay in the page


```
 
很简单，该指令默认是关闭的，一旦关闭，php的错误输出会定向到 /dev/null，如果想通过fastcgi协议告知nginx，那么可以打开该指令，这样nginx的access.log就能捕获到php的输出（比如php错误或异常）。重要的是，该指令一旦打开，会影响性能。
 
打开该指令后，如果遇到php错误，nginx的error.log就会记录，比如：

```
2018/10/03 13:01:55 [error] 908#0: *4 FastCGI sent in stderr: "PHP message: PHP Parse error:  syntax error, unexpected end of file, expecting variable (T_VARIABLE) or ${ (T_DOLLAR_OPEN_CURLY_BRACES) or {$ (T_CURLY_OPEN) in /usr/share/nginx/html/index.php on line 7" while reading response header from upstream, client: 118.207.51.2, server: weiboapi.newyingyong.cn, request: "GET /index.php?ssss HTTP/1.1", upstream: "fastcgi://unix:/run/php/php7.1-fpm.sock:", host: ""
```
 
需要注意点是 nginx（user 指令）和php-fpm pool（user指令）的属主文件权限要一致，否则不会产生日志。
 
另外php-fpm pool还可以记录access日志（access.log）和慢日志（slowlog），比较简单就不阐述了。
 
### php-fpm 日志
 
刚才说的php-fpm都说的是worker进程，即pool工作进程，php-fpm主进程（root）也可以配置error.log（在php-fpm.conf文件中配置），就像在 [《什么是SAPI，FastCGI，PHP-FPM？学习PHP的必备知识》][2] 文章说的一样，我从没发现该日志有内容，即使pool配置文件配置错误，也没有发现日志有内容，所以暂时可以忽略了，也就是说本文讨论了nginx、php-fpm工作进程、php解析器的日志情况。
 
相关文章：

 
* [什么是SAPI，FastCGI，PHP-FPM？学习PHP的必备知识][4]
  
* [502错误，让你进一步明白nginx和php-fpm之间的关系][5]
  
* [代理服务器和Web服务器通信中的504问题][6]

 
我最近写了一本新书 [《深入浅出HTTPS：从原理到实战》][7] ，本书github地址是 https://github.com/ywdblog/httpsbook，大家可以一起讨论本书。本书豆瓣地址 https://book.douban.com/subject/30250772/（或点击文末“阅读原文”），如果你读了本书，还请在豆瓣写个评论。或者关注我的公众号 **`（ID：yudadanwx，虞大胆的叽叽喳喳）`**    ，我会分享一些原创文章。
 


[2]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455770169&idx=1&sn=68670208eab3a6f93c528f3d2f14317a&chksm=8cc9eb92bbbe6284fd63220fa48fa455027f2229811900445d94099aeead619548b60e5819e4&scene=21#wechat_redirect
[3]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455770169&idx=1&sn=68670208eab3a6f93c528f3d2f14317a&chksm=8cc9eb92bbbe6284fd63220fa48fa455027f2229811900445d94099aeead619548b60e5819e4&scene=21#wechat_redirect
[4]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455770169&idx=1&sn=68670208eab3a6f93c528f3d2f14317a&chksm=8cc9eb92bbbe6284fd63220fa48fa455027f2229811900445d94099aeead619548b60e5819e4&scene=21#wechat_redirect
[5]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455770175&idx=1&sn=f13fa1bbc34f4d460a98faf688e362b0&chksm=8cc9eb94bbbe6282821cc4eb192a7a20220412497e9429f7dc278762683b58175245af9daa0c&scene=21#wechat_redirect
[6]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455770197&idx=1&sn=3a0da0b57c3c5d761e6655368453e0ad&chksm=8cc9ebfebbbe62e8ca57215f34c16d4b8eba4ea5166a017005bac652a860649686c4496b8076&scene=21#wechat_redirect
[7]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455769944&idx=1&sn=8cc681833f10177a3979f9546867ddc2&chksm=8cc9ecf3bbbe65e55569cf0fd27b7495dc95f40bb98c7624739f5de0a9a749e6c11ee7587c72&scene=21#wechat_redirect
[0]: https://img1.tuicool.com/YzaEFzB.png

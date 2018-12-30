## 对PHP-FPM和CGI，还有并发响应的理解

来源：[https://segmentfault.com/a/1190000012064837](https://segmentfault.com/a/1190000012064837)

关于本篇文章的部分纠正，请参考这篇文章：[][0][http://www.cppblog.com/woaido...][1]
## 首先搞清楚php-fpm与cgi的关系
#### CGI

CGI是一个web server与cgi程序（这里可以理解为是php解释器）之间进行数据传输的协议，保证了传递的是标准数据。
#### PHP-CGI

php-cgi是php解释器，就是上文提到的cgi程序。
#### Fastcgi

Fastcgi是用来提高cgi程序（php-cgi）性能的方案/协议。

cgi程序的性能问题在哪呢？"PHP解析器会解析php.ini文件，初始化执行环境"，就是这里了。标准的CGI对每个请求都会执行这些步骤，所以处理的时间会比较长。

Fastcgi会先启一个master进程，解析配置文件，初始化执行环境，然后再启动多个worker进程。当请求过来时，master会传递给一个worker，然后立即可以接受下一个请求。这样就避免了重复劳动，效率自然提高。而且当worker不够用时，master可以根据配置预先启动几个worker等着；当然空闲worker太多时，也会停掉一些，这样就提高了性能，也节约了资源。这就是Fastcgi的对进程的管理。
#### PHP-FPM

　　上文提到了Fastcgi只是一个方案或者协议，那么php-fpm就是这个实现了Fastcgi的程序，也就是说，上文所描述的进程分配和管理是FPM来做的。官方对FPM的解释是 Fastcgi Process Manager（Fastcgi 进程管理器）。
## PHP对并发访问的处理
#### 进程和线程

PHP从代码级别来讲不支持多线程操作，不能像Java、C#等语言一样可以编写多线程代码。但多线程和并发没有直接关系，多线程只是代码被运行时在同一时间同时执行多个线程任务，来提高服务器CPU的利用率，提高代码效率。但php是可以多进程执行的，上文所述的FPM进程管理机制就是多进程单线程的，有效提高了并发访问的响应效率。
#### 简单的web server + php-fpm 模式

* 当客户端发送一个请求时，web server会通过一个php-fpm进程（这里和下文所说指的fpm进程都是fpm开启的worker进程，关于fpm的工作原理这里不再累述）去执行php代码，php代码的执行是单线程的。

* 那么，当有多个客户端同时发送请求时（并发），web server会通过php-fpm为每个请求开启一个单独进程去执行php代码。

* 请求执行过后，空闲的php-fpm进程被销毁，内存得以释放。

* 但并发的问题在于，在某一时间，客户端请求让php-fpm进程数量达到了最大限制数，这个时候，新来的请求只能等待空闲的php-fpm进程来处理，这就是多进程同步阻塞模式的弊端，当然还有进程过多所带来的内存占用问题等。

-----

参考链接：

* [https://www.zhihu.com/questio...][2]   php fpm 进程数和并发数是什么关系？

* [https://segmentfault.com/q/10...][3] php不支持多线程所以不用考虑并发问题？

* [http://bbs.csdn.net/topics/39...][4]   PHP是单线程的，如何应对大量的http访问？ #9层回答

* [https://www.cnblogs.com/scott...][5]   PHP 线程，进程和并发

* [https://segmentfault.com/q/10...][6]   搞不清FastCgi与PHP-fpm之间是个什么样的关系

* [http://php.net/manual/zh/inst...][7]   FastCGI 进程管理器（FPM）

* [https://www.cnblogs.com/Perki...][8]   多线程(一)高并发和多线程的关系


[0]: http://www.cppblog.com/woaidongmao/archive/2011/06/21/149092.html
[1]: http://www.cppblog.com/woaidongmao/archive/2011/06/21/149092.html
[2]: https://www.zhihu.com/question/64414628
[3]: https://segmentfault.com/q/1010000005942449/a-1020000012063637
[4]: http://bbs.csdn.net/topics/390778072
[5]: https://www.cnblogs.com/scott19820130/p/4915515.html
[6]: https://segmentfault.com/q/1010000000256516
[7]: http://php.net/manual/zh/install.fpm.php
[8]: https://www.cnblogs.com/PerkinsZhu/p/7242247.html
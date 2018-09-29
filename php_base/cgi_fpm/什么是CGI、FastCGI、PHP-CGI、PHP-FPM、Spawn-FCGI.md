## [【整理】什么是CGI、FastCGI、PHP-CGI、PHP-FPM、Spawn-FCGI？](http://mojijs.com/2016/07/217406/index.html)


**首先，CGI是干嘛的？CGI是为了保证web server传递过来的数据是标准格式的，方便CGI程序的编写者。**

    web server（比如说nginx）只是内容的分发者。比如，如果请求/index.html，那么web 
    server会去文件系统中找到这个文件，发送给浏览器，这里分发的是静态数据。好了，如果现在请求的是/index.php，根据配置文
    件，nginx知道这个不是静态文件，需要去找PHP解析器来处理，那么他会把这个请求简单处理后交给PHP解析器。Nginx会传哪些数
    据给PHP解析器呢？url要有吧，查询字符串也得有吧，POST数据也要有，HTTP header不能少吧，好的，CGI就是规定要传哪些数据
    、以什么样的格式传递给后方处理这个请求的协议。仔细想想，你在PHP代码中使用的用户从哪里来的。

    当web server收到/index.php这个请求后，会启动对应的CGI程序，这里就是PHP的解析器。接下来PHP解析器会解析php.ini文件，
    初始化执行环境，然后处理请求，再以规定CGI规定的格式返回处理后的结果，退出进程。web server再把结果返回给浏览器。

**好了，CGI是个协议，跟进程什么的没关系。那fastcgi又是什么呢？Fastcgi是用来提高CGI程序性能的。**

    提高性能，那么CGI程序的性能问题在哪呢？”PHP解析器会解析php.ini文件，初始化执行环境”，就是这里了。标准的CGI对每个请
    求都会执行这些步骤（不闲累啊！启动进程很累的说！），所以处理每个时间的时间会比较长。这明显不合理嘛！那么Fastcgi是怎
    么做的呢？首先，Fastcgi会先启一个master，解析配置文件，初始化执行环境，然后再启动多个worker。当请求过来时，master会
    传递给一个worker，然后立即可以接受下一个请求。这样就避免了重复的劳动，效率自然是高。而且当worker不够用时，master可
    以根据配置预先启动几个worker等着；当然空闲worker太多时，也会停掉一些，这样就提高了性能，也节约了资源。这就是fastcgi
    的对进程的管理。

**那PHP-FPM又是什么呢？是一个实现了Fastcgi的程序，被PHP官方收了。**

    大家都知道，PHP的解释器是php-cgi。php-cgi只是个CGI程序，他自己本身只能解析请求，返回结果，不会进程管理（皇上，臣妾
    真的做不到啊！）所以就出现了一些能够调度php-cgi进程的程序，比如说由lighthttpd分离出来的spawn-fcgi。好了PHP-FPM也是
    这么个东东，在长时间的发展后，逐渐得到了大家的认可（要知道，前几年大家可是抱怨PHP-FPM稳定性太差的），也越来越流行。

**有人说，fastcgi是一个协议，php-fpm实现了这个协议**

    对。

**有人说，php-fpm是fastcgi进程的管理器，用来管理fastcgi进程的**

    不对（原文写的是“对”，应该是笔误，其实想表达的是不对——yockie注）。php-fpm的管理对象是php-cgi。但不能说php-fpm是fast
    cgi进程的管理器，因为前面说了fastcgi是个协议，似乎没有这么个进程存在，就算存在，php-fpm也管理不了他（至少目前是）。
     有的说，php-fpm是php内核的一个补丁

    以前是对的。因为最开始的时候php-fpm没有包含在PHP内核里面，要使用这个功能，需要找到与源码版本相同的php-fpm对内核打补
    丁，然后再编译。后来PHP内核集成了PHP-FPM之后就方便多了，使用–enalbe-fpm这个编译参数即可。

**有人说，修改了php.ini配置文件后，没办法平滑重启，所以就诞生了php-fpm**

    是的，修改php.ini之后，php-cgi进程的确是没办法平滑重启的。php-fpm对此的处理机制是新的worker用新的配置，已经存在的
    worker处理完手上的活就可以歇着了，通过这种机制来平滑过度。

**还有的说PHP-CGI是PHP自带的FastCGI管理器，那这样的话干吗又弄出个php-fpm**

    不对。php-cgi只是解释PHP脚本的程序而已。

【以上参考：https://segmentfault.com/q/1010000000256516】

## -

## -

**什么是CGI**

    CGI全称是“公共网关接口”(Common Gateway Interface)，HTTP服务器与你的或其它机器上的程序进行“交谈”的一种工具，其程序须运行在网络服务器上。

    CGI可以用任何一种语言编写，只要这种语言具有标准输入、输出和环境变量。如php,perl,tcl等

**什么是FastCGI**

    FastCGI像是一个常驻(long-live)型的CGI，它可以一直执行着，只要激活后，不会每次都要花费时间去fork一次(这是CGI最为人诟病的fork-and-execute 模式)。它还支持分布式的运算, 即 FastCGI 程序可以在网站服务器以外的主机上执行并且接受来自其它网站服务器来的请求。

    FastCGI是语言无关的、可伸缩架构的CGI开放扩展，其主要行为是将CGI解释器进程保持在内存中并因此获得较高的性能。众所周知
    ，CGI解释器的反复加载是CGI性能低下的主要原因，如果CGI解释器保持在内存中并接受FastCGI进程管理器调度，则可以提供良好
    的性能、伸缩性、Fail- Over特性等等。

**FastCGI与CGI特点**

    1、如CGI，FastCGI也具有语言无关性.   
    2、如CGI, FastCGI在进程中的应用程序，独立于核心web服务器运行,提供了一个比API更安全的环境。(APIs把应用程序的代码与核
        心的web服务器链接在一起，这意味着在一个错误的API的应用程序可能会损坏其他应用程序或核心服务器; 恶意的API的应用程序代码甚至可以窃取另一个应用程序或核心服务器的密钥。)   
    3、FastCGI技术目前支持语言有：C/C++、Java、Perl、Tcl、Python、SmallTalk、Ruby等。相关模块在Apache, ISS, Lighttpd等流行的服务器上也是可用的。   
    4、如CGI，FastCGI的不依赖于任何Web服务器的内部架构，因此即使服务器技术的变化, FastCGI依然稳定不变。 

**FastCGI的工作原理**

    1、Web Server启动时载入FastCGI进程管理器（IIS ISAPI或Apache Module)   
  
    2、FastCGI进程管理器自身初始化，启动多个CGI解释器进程(可见多个php-cgi)并等待来自Web Server的连接。   
  
    3、当客户端请求到达Web Server时，FastCGI进程管理器选择并连接到一个CGI解释器。Web server将CGI环境变量和标准输入发送到FastCGI子进程php-cgi。   
  
    4、FastCGI子进程完成处理后将标准输出和错误信息从同一连接返回Web Server。当FastCGI子进程关闭连接时，请求便告处理完成。FastCGI子进程接着等待并处理来自FastCGI进程管理器(运行在Web Server中)的下一个连接。 在CGI模式中，php-cgi在此便退出了。 

在上述情况中，你可以想象CGI通常有多慢。每一个Web请求PHP都必须重新解析php.ini、重新载入全部扩展并重初始化全部数据结构。使用FastCGI，所有这些都只在进程启动时发生一次。一个额外的好处是，持续数据库连接(Persistent database connection)可以工作。

**FastCGI的不足**

    因为是多进程，所以比CGI多线程消耗更多的服务器内存，PHP-CGI解释器每进程消耗7至25兆内存，将这个数字乘以50或100就是很大的内存数。   
  
    Nginx 0.8.46+PHP 5.2.14(FastCGI)服务器在3万并发连接下，开启的10个Nginx进程消耗150M内存（15M*10=150M），开启的64个php-cgi进程消耗1280M内存（20M*64=1280M），加上系统自身消耗的内存，总共消耗不到2GB内存。如果服务器内存较小，完全可以只开启25个php-cgi进程，这样php-cgi消耗的总内存数才500M。

上面的数据摘自Nginx 0.8.x + PHP 5.2.13(FastCGI)搭建胜过Apache十倍的Web服务器(第6版)

**什么是PHP-CGI**

    PHP-CGI是PHP自带的CGI程序（原文是“PHP自带的FastCGI管理器”，本人觉得不对，应该是“PHP自带的CGI程序”，下面的PHP-FPM才是FastCGI管理器——yockie注）。   
    启动PHP-CGI，使用如下命令：   
    php-cgi -b 127.0.0.1:9000

**PHP-CGI的不足**  
1、php-cgi变更php.ini配置后需重启php-cgi才能让新的php-ini生效，不可以平滑重启   
2、直接杀死php-cgi进程,php就不能运行了。(PHP-FPM和Spawn-FCGI就没有这个问题,守护进程会平滑从新生成新的子进程。）

**什么是PHP-FPM**

    PHP-FPM是一个PHP FastCGI管理器，是只用于PHP的,可以在 > http://php-fpm.org/download> 下载得到.   
    PHP-FPM其实是PHP源代码的一个补丁，旨在将FastCGI进程管理整合进PHP包中。必须将它patch到你的PHP源代码中，在编译安装PHP后才可以使用。   
    现在我们可以在最新的PHP 5.3.2的源码树里下载得到直接整合了PHP-FPM的分支，据说下个版本会融合进PHP的主分支去。相对Spawn-FCGI，PHP-FPM在CPU和内存方面的控制都更胜一筹，而且前者很容易崩溃，必须用crontab进行监控，而PHP-FPM则没有这种烦恼。   
    PHP5.3.3已经集成php-fpm了，不再是第三方的包了。PHP-FPM提供了更好的PHP进程管理方式，可以有效控制内存和进程、可以平滑重载PHP配置，比spawn-fcgi具有更多有点，所以被PHP官方收录了。在./configure的时候带 –enable-fpm参数即可开启PHP-FPM。 

使用PHP-FPM来控制PHP-CGI的FastCGI进程   
/usr/local/php/sbin/php-fpm{start|stop|quit|restart|reload|logrotate}

    –start 启动php的fastcgi进程   
    –stop 强制终止php的fastcgi进程   
    –quit 平滑终止php的fastcgi进程   
    –restart 重启php的fastcgi进程   
    –reload 重新平滑加载php的php.ini   
    –logrotate 重新启用log文件 

**什么是Spawn-FCGI**

    Spawn-FCGI是一个通用的FastCGI管理服务器，它是lighttpd中的一部份，很多人都用Lighttpd的Spawn-FCGI进行FastCGI模式下的
    管理工作，不过有不少缺点。而PHP-FPM的出现多少缓解了一些问题，但PHP-FPM有个缺点就是要重新编译，这对于一些已经运行的
    环境可能有不小的风险(refer)，在php 5.3.3中可以直接使用PHP-FPM了。   
    Spawn-FCGI目前已经独成为一个项目，更加稳定一些，也给很多Web 站点的配置带来便利。已经有不少站点将它与nginx搭配来解决动态网页。   
    最新的lighttpd也没有包含这一块了(> http://www.lighttpd.net/search?q=Spawn-FCGI> )，但可以在以前版本中找到它。在lighttpd-1.4.15版本中就包含了(> http://www.lighttpd.net/download/lighttpd-1.4.15.tar.gz> )   
    目前Spawn-FCGI的下载地址是> http://redmine.lighttpd.net/projects/spawn-fcgi> ，最新版本是> http://www.lighttpd.net/download/spawn-fcgi-1.6.3.tar.gz

注：最新的Spawn-FCGI可以到lighttpd.net网站搜索“Spawn-FCGI”找到它的最新版本发布地址

下面我们就可以使用Spawn-FCGI来控制php-CGI的FastCGI进程了

    /usr/local/bin/spawn-fcgi -a 127.0.0.1 -p 9000 -C 5 -u www-data -g www-data -f /usr/bin/php-CGI   
    参数含义如下:   
    -f 指定调用FastCGI的进程的执行程序位置，根据系统上所装的PHP的情况具体设置   
    -a 绑定到地址addr   
    -p 绑定到端口port   
    -s 绑定到unix socket的路径path   
    -C 指定产生的FastCGI的进程数，默认为5(仅用于PHP)   
    -P 指定产生的进程的PID文件路径   
    -u和-g FastCGI使用什么身份(-u 用户 -g 用户组)运行，Ubuntu下可以使用www-data，其他的根据情况配置，如nobody、apache等

**PHP-FPM与spawn-CGI对比测试**

    PHP-FPM的使用非常方便,配置都是在PHP-FPM.ini的文件内，而启动、重启都可以从php/sbin/PHP-FPM中进行。更方便的是修改php.ini后可以直接使用PHP-FPM reload进行加载，无需杀掉进程就可以完成php.ini的修改加载   
    结果显示使用PHP-FPM可以使php有不小的性能提升。PHP-FPM控制的进程cpu回收的速度比较慢,内存分配的很均匀。   
    Spawn-FCGI控制的进程CPU下降的很快,而内存分配的比较不均匀。有很多进程似乎未分配到,而另外一些却占用很高。可能是由于进程任务分配的不均匀导致的.而这也导致了总体响应速度的下降。而PHP-FPM合理的分配，导致总体响应的提到以及任务的平均。 

**PHP-FPM与Spawn-FCGI功能比较**  
http://php-fpm.org/about/  
PHP-FPM、Spawn-FCGI都是守护php-cgi的进程管理器。

【以上参考：http://www.mike.org.cn/articles/what-is-cgi-fastcgi-php-fpm-spawn-fcgi/】

## -

## -

我的总结：

    CGI：是一种协议，语言无关，用于处理http服务器的请求   
    FastCGI：也是一种协议，语言无关，用来提高CGI程序性能的   
    PHP-CGI：一个实现了CGI协议的程序，也是PHP的解释器   
    PHP-FPM：一个实现了FastCGI协议的程序，现在已经包含在PHP内核中   
    Spawn-FCGI：同样也是一个实现了FastCGI协议的程序，更通用一些，从lighttpd项目独立出来


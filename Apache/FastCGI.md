# FastCGI入门

_发布时间：_ 2016-01-26 _作者：_ 迹忆 _浏览次数：_

在[《CGI初接触》][0]中我们提到了CGI和Server APIs的运行机制，以及各自的优点和缺点。本章我们来了解一下FastCGI，它结合了CGI和Server APIs各自的优点。

**FastCGI简单介绍**

相对于CGI来说FastCGI有两点是和其不同的。第一点是FastCGI的进程是永久性的，也就是说用于处理请求的进程随着请求的结束并不会退出，而是继续运行等待有新的请求来处理。这样就解决了CGI由于频繁的创建和关闭进程所带来的性能问题；第二点是FastCGI和web服务器之间的通信方式是和CGI不同的。当FastCGI应用和web服务器在同一台机器上的时候，FastCGI和web服务器之间是使用全双工的连接进行通信，环境信息、输入信息、输出和错误信息都是通过这个全双工的连接进行传输的。而CGI是通过环境变量、stdin、stdout和stderr等文件进行通信的。如果FastCGI应用和web服务器在不同的机器上，这时双方是通过socket（也就是TCP连接）来进行通信的。

以上两点是FastCGI不同于CGI的地方，同时也是其优于CGI的地方。除此之外的FastCGi的其他的优点和CGI的相同，实现简单、独立于开发语言、进程独立、不受web服务器内部架构的影响等也同样是FastCGI的优点。

结合以上两点，我们来看一个完整请求的过程

![][1]

  
**FastCGI在PHP中的应用**

在PHP5.3版本以后，PHP中集成了PHP-FPM，实现了FastCGI在PHP中的应用。FPM的具体应用请参考[《PHP中的FPM究竟做了什么》][2]这篇文章。这里仅针对上述第一点FastCGI的进程的永久性做一下说明。

首先我们需要配置一下PHP-FPM（打开配置文件php安装目录/etc/php-fpm.conf），打开文件以后，找到 pm、pm.start_servers这两项分别进行以下配置

    pm = dynamic //在有请求连接的时候才会创建进程  
    pm.start_servers = 2 //开启的FastCGI的子进程数量

当我们启动FPM服务以后，这时系统创建一个FastCGI主进程，然后由主进程创建两个子进程

    # ps –ef | grep php-fpm //查看fpm所有进程信息  
    root 6116 1 0 13:24 ? 00:00:00 php-fpm: master process (/usr/local/php5/etc/php-fpm.conf)  
    nobody 6117 6116 0 13:24 ? 00:00:00 php-fpm: pool www   
    nobody 6118 6116 0 13:24 ? 00:00:00 php-fpm: pool www

我们会看到有三条进程信息，当我们经过一段时间再次使用上述命令查看的时候，发现依旧是这三条信息。所以说FastCGI的进程是永久性的。

当然话又说回来，既然FastCGI进程是永久性的，那如果说Web服务一直没有请求，而进程却在一直占用着资源那对于服务器来说应该是一种资源的浪费。事实确实如此，不过PHP-FPM在设计的时候已经考虑到了这一点，在配置的时候可以将pm选项设置为ondemand，此选项表示随着FPM服务的启动并不会创建子进程，只有当请求连接时才会去创建子进程。

在配置文件中进行如下设置

    pm = ondemand  
    pm.max_children = 10  
    pm.process_idle_timeout = 10s //此选项只有在pm设为ondemand的时候才有效，其含义为当子进程处理完请求以后继续等待新的请求，如果超过10秒没有新的请求，那么该进程将会退出并释放资源。

当配置文件改完以后重启FPM服务，此时我们查看进程信息只有一条信息。

    #ps –ef | grep php-fpm  
    root 6415 1 0 13:38 ? 00:00:00 php-fpm: master process (/usr/local/php5/etc/php-fpm.conf)

此时只有系统创建的主进程，接着我们可以发起一条请求（例如：http://localhost/index.php）。

然后我们再次查看进程信息

    # ps –ef | grep php-fpm  
    root 6415 1 0 13:38 ? 00:00:00 php-fpm: master process (/usr/local/php5/etc/php-fpm.conf)  
    nobody 6485 6415 0 13:42 ? 00:00:00 php-fpm: pool www

此时会有两条进程信息，除了主进程以后，还有处理请求的子进程。接着我们10秒不发请求，10秒以后我们再次查看进程信息

    #ps –ef | grep php-fpm  
    root 6415 1 0 13:38 ? 00:00:00 php-fpm: master process (/usr/local/php5/etc/php-fpm.conf)

此时又变成了一条主进程的信息。当然这条主进程是不会退出的，只要服务开着那它就会一直存在着监听某个端口看是否有新的请求，它和Web服务器进程是一样的，只有当关闭服务以后该进程才会被杀掉。

上述子进程关闭的案例好像和FastCGI的规范不符合，没错FastCGI规范中规定这个进程是永久存在的，但是当pm设为dynamic或者static的时候确实进程是一直生存的，不过如果没有请求那对计算机来说也是一种资源的浪费，因此我们可以将设置其等待时间，超过这个时间由主进程将子进程杀掉。当然我们可以根据实际情况将这个时间设置的长一些，而且如果我们的应用访问频繁的话，这个进程也会一直存在，所以说这一点并不违背FastCGI的规范。

由于FastCGI程序和Web服务器之间是通过TCP连接通信的，所以说FastCGI程序和Web服务器可以部署在不同的机器上面。因此方便了我们以后的扩展以及实现分布式部署从而能很容易的实现负载均衡。

**简单叙述FastCGI****程序是单线程还是多线程**

FastCGI的机制使得开发者可以自由的选择是单线程还是多线程的方式。对于多线程的程序来说其实现方式有两种：

一、程序可以通过多线程同时接受Web服务器的多个连接，来达到接收并发的请求

二、通过多路复用的连接形式，将并发的请求通过一个连接发送给程序的多个线程来进行处理。

当然由于开发过程中还需要考虑到线程安全问题，并且多线程程序测试和调试也是很困难的，所以说大多数开发者还是喜欢使用单线程的模式。对于单线程程序来说，同样可以使用多路复用的web连接形式接收多个请求，但是处理方式是采用event-driven（事件驱动）的方式来进行处理（对于事件驱动可以参考[《我对event-loop的理解》][3]这篇文章大概了解其原理）。  
那到底是用单线程抑或是多线程要根据实际情况还有开发者习惯来做最终的决定（这句话有些多余）。

综上是我对FastCGI的一个大体的介绍，由于水平有限我也只能理解到这个深度，欢迎大家在下面留言给出好的建议。

[0]: http://www.onmpw.com/tm/xwzj/network_62.html
[1]: ./img/1-1601260T401241.png
[2]: http://www.onmpw.com/tm/xwzj/prolan_59.html
[3]: http://www.onmpw.com/tm/xwzj/prolan_54.html
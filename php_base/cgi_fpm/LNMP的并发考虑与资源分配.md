## LNMP的并发考虑与资源分配

### 在招聘中常问的一个问题

<font face=微软雅黑>

PHPer当被问到你的程序性能如何？程序的并发可以达到多少？程序的瓶颈在哪儿？为了满足业务需求应该购买多少台服务器？负载均衡中php应用服务器需要多少台？

可能这些问题在面试中会设置一个应用的场景及一些前提条件，让面试的人去设计，并提出看法建议，能够回答得很好的人还是比较少的。

今天我们来谈谈LNMP的并发考虑和资源分配。

### 概念

#### LNMP中的N是nginx充当Web Server

内容的分发者，会在文件系统找到相应的文件，就返回给浏览器，如：nginx。如果是静态的文件，就可以直接返回，但是如果是index.php需要解析并执行的脚本文件时，Web Server就无力了，需要将请求转发给相应的脚本语言的解析器来解释并执行，最终将程序的执行结果，返回给Web Server，再返回给浏览器。

#### LNMP中的P是php充当后端的逻辑处理程序

那么php与nginx的常规协作方式是如何的呢？需要我们明确几个概念

##### cgi

通用网关接口，是HTTP协议中描述的，Web Server与后端处理程序进程间通信的协议

##### php-cgi

php实现了cgi协议，使得web server与php共同完成一个动态网页的请求响应

##### fastcgi

是为了解决cgi性能问题，而规范的另外一种协议，为什么说解决cgi性能问题，因为在面对各大中型网站的业务需求中，cgi程序表现得越来越无力，因为cgi程序在每次接收到请求时都需要启动新的进程，并初始化环境，然后执行程序，具体的协议内容，在此不引述。

##### php-fpm

实现了fastcgi协议，是php-cgi的进程管理器，解决高并发网站的性能问题。

在最终回答LNMP的并发考虑与资源分配还需要明确的几个概念

#### 并发

一般由单位内完成的请求数来衡量，如，每秒事务数（TPS），每秒HTTP请求数（HPS），每秒查询数（QPS）。通常情况下，我们说PHP的并发，都是指一秒内PHP完成的动态请求的次数。如某网站高峰期的动态请求并发为5000每秒，这个数字不算太高，但也不低。一般日活跃用户数在1000万-5000万的网站应用才能达到这个级别。

#### 性能

一般是指应用程序的处理速度，如果php的应用程序，打开一个页面（执行一个脚本程序）通常需要在50-100ms完成，这对程序的性能要求还是比较高的。但是这还仅仅只是程序处理，php处理完成之后，还要交给web server，web server再将数据返回浏览器，这中间会有一个网络延迟，通常网络正常的情况下，需要大约100ms，最终一个动态网页的请求大约200ms（理想的情况下）可以到达用户浏览器端（仅仅是一个html结构）。

### 资源分配

#### php-fpm进程数

按照上面的描述，并发为5000每秒，每个请求完成大约200ms（具体页面要具体分析，这里只是一个理想值），如果只有5台PHP应用程序服务器，那么每台机器平均为并发1000每秒，如果是使用nginx+php-fpm的架构，php-fpm的php-cgi进程管理器的配置应该如何呢？我计算的结果为（具体的配置项说明在后文）：

    pm=static
    pm.max_children=100
    

上面的100是如何得来的，由于机器平均并发为1000每秒，每个动态请求的处理时间为100ms，也就是说1个php-fpm的worker处理进程在1秒内可以处理10个请求，100个php-fpm的worker处理进程，就可以处理1000个请求。

当然需要结合服务器硬件资源来进行配置，如果配置不当，很容易在请求高峰期或者流量猛增导致服务器宕机。

#### 网络带宽

网络带宽也会是一个重要的因素，如果你的服务处理很强，但是用户的请求和响应不能及时到达也是白忙活，这个参数如何计算呢？

并发5000每秒，每个请求的输出为20K，则5000x20K=100000K=100M

这就要求你的公网负载均衡器外网出口带宽至少要达到100M

#### 内存

上述中100个php-fpm的worker处理进程，理论上如果服务器只运行php-fpm，那么我们可以将服务器内存的一半分配给php-fpm，通常情况下，我们可以认为一个php-fpm的worker处理进程占用内存20M，那么100x20M=2G，也就是说明服务器的内存大约为4G

#### CPU

由于php-fpm是一个多进程的模型应用，CPU进程调度消耗也是很大的，并且PHP应用程序有问题也会导致CPU占用率高，这就没有量化的指标，需要具体情况具体分析了。但是有一个小建议，可以部署一个crontab每隔一分钟检测cpu占用率超过多少就kill掉相应的php-fpm的worker处理进程。

### php-fpm Unix Socket

如果nginx与php在同一台机器，nginx与php-fpm使用unix域套接字代替tcp socke进行通信，这个配置挺关键的，纯echo的ab测试，采用unix域套接字每秒请求数提升10%-20%

即nginx中配置：

    fastcgi_pass unix:/data/server/var/php/php-fpm.sock;
    

php-fpm.conf中配置：

    listen = /data/server/var/php/php-fpm.sock
    

最后遇到很多同学对php-fpm的进程管理器的核心配置不太了解，下面是我翻译的配置说明：

#### php-fpm配置项

#### pm

进程管理器以控制子进程的数量，可能的值有

    static                        一个固定的值，由pm.max_children指定
    dynamic                       动态的（工作方式和Apache的prefork模式一致），但是保持至少一个，由
                                  pm.max_children             在同一时间最大的进程数
                                  pm.start_servers              php-fpm启动时开启的等待请求到来的进程数
                                  pm.min_spare_servers    在空闲状态下，运行的最小进程数，如果小于此值，会创建新的进程
                                  pm.max_spare_servers   在空闲状态下，运行的最大进程数，如果大于此值，会kill部分进程
    ondemand                      启动时不会创建进程，当请求达到时创建子进程处理请求
                                  pm.max_children 在同一时间最大的进程数
                                  pm.process_idle_timeout  空闲多少秒之后进程会被kill
    

    pm = static
    

#### pm.max_children

在同一时间最大的进程数

    pm.max_children = 120
    

#### pm.start_servers

php-fpm启动时开启的等待请求到来的进程数，默认值为：min_spare_servers + (max_spare_servers - min_spare_servers) / 2

    pm.start_servers = 80
    

#### pm.min_spare_servers

在空闲状态下，运行的最小进程数，如果小于此值，会创建新的进程

    pm.min_spare_servers = 60
    

#### pm.max_spare_servers

在空闲状态下，运行的最大进程数，如果大于此值，会kill部分进程

    pm.max_spare_servers = 120
    

### pm.process_idle_timeout

空闲多少秒之后进程会被kill，默认为10s

    pm.process_idle_timeout = 10s
    

#### pm.max_requests

每个进程处理多少个请求之后自动终止，可以有效防止内存溢出，如果为0则不会自动终止，默认为0

    pm.max_requests = 5000
    

### pm.status_path

注册的URI，以展示php-fpm状态的统计信息

    pm.status_path = /status
    

其中统计页面信息有：

    pool                         进程池名称
    process manager              进程管理器名称（static, dynamic or ondemand）
    start time                   php-fpm启动时间
    start since                  php-fpm启动的总秒数
    accepted conn                当前进程池接收的请求数
    listen queue                 等待队列的请求数
    max listen queue             自启动以来等待队列中最大的请求数
    listen queue len             等待连接socket队列大小
    idle processes               当前空闲的进程数
    active processes             活动的进程数
    total processes              总共的进程数（idle+active）
    max active processes         自启动以来活动的进程数最大值
    max children reached         达到最大进程数的次数
    

### ping.path

ping url，可以用来测试php-fpm是否存活并可以响应

    ping.path = /ping
    

### ping.response

ping url的响应正文

    ping.response = pong

</font>
## nginx架构

来源：[https://segmentfault.com/a/1190000017224404](https://segmentfault.com/a/1190000017224404)


## 一.概述

本文将深入剖析nginx的架构。

第一部分介绍nginx现有框架，用典型的4+1视图阐述，包括逻辑架构，开发架构，运行架构，物理架构，功能用例，nginx为单机服务，不考虑物理架构。其中功能用例概述nginx功能；逻辑架构主要介绍nginx高度模块化中各个模块的分层和依赖关系；开发架构主要描述nginx的代码结构和代码内容简介；重点是运行架构，nginx一主多从的进程模型架构和通信，高并发进程和IO并发的选型等。

第二部分对比nginx运行架构和其他开源运行架构，总结nginx为何要这样选型；介绍nginx逻辑架构中的优点。

本文适合阅读对象：1）已经看过nginx代码，本文帮你高度抽象总结了nginx的架构和与我自己设计相比较，nginx哪里设计的优点，试着从架构层来重新看下代码；2）研究各种系统架构的人，本文从统一的架构视图介绍，无需知道nginx的代码细节，列出了与其他架构比，nginx架构的亮点。3）还未看过nginx的代码，关注第二章，可以看四个视图忽略对特性的分析和架构的思考，帮助了解nginx有什么功能、如何组织代码、如何运行。
#### 关键词：Nginx架构，nginx功能，nginx逻辑架构，nginx代码结构，nginx运行架构，nginx高性能实现

## 二.nginx现有架构实现
## 功能介绍

nginx最核心的功能是web服务器和反向代理服务器，web服务器完成对 http请求协议的解析 和 以http协议格式响应请求、缓存、日志处理这些 基本web服务器 功能，反向代理服务器完成对请求的转发、负载均衡、鉴权、限流、缓存、日志处理等代理常用功能。nginx在这方面提供了丰富的功能，包括对http2,ssl等等的支持。除了http外，nginx还支持mail服务和普通的tcp,udp协议的反向代理功能。一下列出了常用功能，详细所有功能见参考1
 ### http服务器/反向代理服务器


* 静态文件，fastcgi,uwsgi,scgi,memcached 服务
* 缓存
* 负载均衡
* SSL/TLS
* HTTP2
* 鉴权/限流
* 虚拟servers


功能用例举例：web服务器和反向代理服务器的功能（第一个locatiion为服务器，后面是反向代理）。在nginx中配置如图配置，启动nginx加载配置后，发起http请求，获取服务器响应。

```nginx
server{
    listen 8091;
    root /home/xiaoju/yyl/;
    index index.php;
    location /
    {
        root html;
        index index.html index.htm;
    }
    location  ~ \.php$
    {
        rewrite /(.*)$ /index.php/$1 break;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi.conf;
    }
}
```

```
curl localhost:8091 -v
* About to connect() to localhost port 8091 (#0)
*   Trying 127.0.0.1... connected
* Connected to localhost (127.0.0.1) port 8091 (#0)
> GET / HTTP/1.1
> User-Agent: curl/7.19.7 (x86_64-redhat-linux-gnu) libcurl/7.19.7 NSS/3.27.1 zlib/1.2.3 libidn/1.18 libssh2/1.4.2
> Host: localhost:8091
> Accept: */*
>
< HTTP/1.1 200 OK
< Server: nginx/1.13.11
< Date: Sun, 02 Dec 2018 06:29:01 GMT
< Content-Type: text/html; charset=utf-8
< Content-Length: 612
< Last-Modified: Tue, 10 Apr 2018 15:32:02 GMT
< Connection: keep-alive
< ETag: "5accd8f2-264"
< Accept-Ranges: bytes
<
<!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
...
</html>
* Connection #0 to host localhost left intact
* Closing connection #0
```
### mail反向代理


* mail反向代理
* SSL; STARTTLS/STLS


### TCP/UDP反向代理  socket,websocket

* SSL/TLS, 负载均衡, 鉴权/限流等

## 2.逻辑架构

nginx在逻辑上分为入口层，模块化的功能处理层，系统调用层。入口调用配置模块和核心模块，各核心模块分别调用各自功能模块，系统调用层封装了各个操作系统的功能被功能处理层使用。逻辑架构最明显主要的特征就是高度模块化，所有功能都是模块，每个模块都统一结构，下面先看下这个统一结构，然后分别介绍各个模块。

![][0]
### 特征——高度模块化

nginx除了main等少量代码，其他全都是模块，所有模块都是Ngx_module_t的抽象，只有初始化，退出，对配置项的处理;每个模块内部也都有自己模块ngx_xx_module_t的抽象；配置也高度抽象统一的结构ngx_command_t。如图：

![][1]
### 核心模块/配置模块

核心流程会只会调用核心模块和配置模块。
核心模块调用各个其他模块的core_module完成各自模块的加载工作。配置模块为其他模块的基础，负责解析配置文件。
### 事件模块

负责请求连接的建立，分发等网络事件及定时器事件，其中所有模块封装到ngx_events_module_t接口中供其他模块直接调用。
### http/stream/mail模块

对应nginx用户功能的三个主体。以Http模块为例，初始化，退出，对配置项的处理等工作也统一封装在ngx_http_module_t中。http请求的处理过程各模块可插拔，为固定的11个阶段，模块想介入，只需在ngx_http_module_t中定义回调函数，http协议内容多，对结果的处理也高度模块化，根据配置项将模块选择性插入到输出过滤链中。
### 系统调用

nginx对各种操作系统的调用做了一层封装，使模块代码无需区分。
### 依赖关系

http,stream,mail依赖事件模块，事件模块依赖核心模块和配置模块，上层模块依赖底层的系统调用。
## 3. 开发架构

开发架构主要关注现有的代码结构和开发代码时如何扩展。先介绍代码结构然后列举一下如何新增模块
### 代码结构

包含core,event,http,mail,misc,os,stream这几个文件夹

* core

为nginx的核心源代码，包括main函数/整体控制，基本数据结构封装，配置管理，内存管理，日志管理，文件读写网络套接字功能，系统参数资源通用管理等

* event

module子目录实现了Nginx支持的事件驱动模型：AIOepollkqueueselectpoll等，其他提供了事件驱动模型相关数据结构的定义、初始化、事件接收传递管理功能以及时间驱动模型调用功能

* http

module文件实现了http模块的功能，perl为对perl的支持，v2为对http2.0的支持，其他提供结构定义、初始化、网络连接建立、管理、关闭、数据报解析、upstream等通用功能


* mail    邮件功能的实现
* misc
* os   根据操作系统对系统调用的封装
* stream   为对TCP/UDP反向代理的支持


### 开发扩展模块

编辑新的模块步骤：


* 在自定义文件夹下，创建conf文件，说明名字和目录。
* 编写代码。
* 编译入nginx：
在configure脚本执行时加入参数--add-module=PATH,执行后会生成objs/Makefile,objs/ngx_modules.c（也可以直接修改）。会执行conf文件，生成的ngx_modules.c包含nginx启动时加载的所有模块，nginx核心代码中全局modules从这里获取。这个模块就被编译到nginx程序中了。


## 4.运行架构

首先给出nginx的整体架构图，然后介绍运行架构中关注的运行模式，通信方式，IO处理选型。再总结下nginx运行架构的特性：事件驱动+碎片化+异步处理
### 架构图

![][2] 
说明：因为精力有限，只看了epoll的运行时架构，以下所有分析均只考虑linux并使用epoll的情况。
启动后，有一个主进程，多个worker进行，两个cache相关进程。多个worker共同监听事件并处理，反向代理会把请求转发给后端服务。
### 一主+多worker+cache manager+cache loader进程


* master: 管理worker等子进程实现重启服务，平滑升级，更换日志文件，配置文件实时生效等
* worker: 简单的负载均衡（高负载等待），抢锁，监听处理事件，接收master命令
* cache: nginx开启缓存功能，会创建cache的两个进程，cache loader在nginx启动后将磁盘上次缓存的对象加载到内存后自动退出，cache管理进程清理超时缓存文件，限制缓存文件总大小，这个过程反反复复，直到Nginx整个进程退出。


### 进程间通信

nginx的进程间通信，在不同应用场景下采取不同的形式：


* linux与master/worker/cache进程通信：信号master启动时先把感兴趣的信号注册；
 在主进程fork子进程之前要把所有信号调用sigprocmask阻塞住，等待fork成功后再将阻塞信号清除；
 主进程之后就挂起在sigsuspend中，等待信号；


* 主进程与子进程通信：socketpair这里每个子进程和父进程之间使用的是socketpair系统调用建立起来的全双工的socket  channel[]在父子进程中各有一套，channel[0]为写端，channel[1]为读端                        
父进程关闭socket[0], 子进程关闭socket[1]，父进程从sockets[1]中读写，子进程从sockets[0]中读写，还是全双工形态

子进程也会监督部分信号，是master通过socketpair发送过去的。linux关闭worker后，worker也会通过socketpair把信号发送给主进程


* 其他进程间共享数据：共享内存nginx中所有共享内存都是以list链表的形式组织在全局变量cf->cycle->shared_memory下,在创建新的共享内存之前会先对该链表进行遍历查找以及冲突检测，对于已经存在且不存在冲突的共享内存可直接返回引用。

函数ngx_shm_alloc()时共享内存的实际分配,针对当前系统可提供的接口,可以是mmap,shmget等
 应用于进程(如子进程之间，在进程重启时新旧主进程需要抢锁等)间需要共享的数据，比如连接数/互斥锁等，另外提下锁有多重互斥方式，在操作系统支持的情况下用优先用原子操作。


### IO处理

这部分为了并发需要考虑多进程，多线程，IO阻塞，IO非阻塞，每个进程处理一个还是多个事件 等典型的IO网络选型中的这几个问题。

nginx在操作系统支持的情况下（不支持根据不同操作系统和配置，事件模型中选择不同IO处理方式）采取多进程，每个进程可以同时接收多个请求，IO多路复用非阻塞的方式。详细的运行架构如图。简单抽象过程：主进程创建监听socket后所有worker子进程继承共同监听，通过抢锁的方式决定同一时刻哪个worker是请求的acceptor方，accept请求后在本子进程中处理。

![][3]

* 多进程

为了并发和利用多核处理，首先启用多进程的模式，在主进程创建所有监听的socket，为了所有worker都可以继承并监听该socket的fd。


* 多acceptor,多handler    采取多acceptor,多handler的模式，每个进程在自己内部acceptor后分配给自己内部的handler处理。为了防止多acceptor同时accept的惊群现象，只有抢到锁的才把事件加入到监听，唤醒只会唤醒当前进程accept事件（新版的nginx采取reuseport可以一个端口被多个进程监听，支持的4.3的accept相关特征也不需要抢锁）
* 进程accept多个     每个进程可以accept多个连接的模式，每次只处理accept，为三次握手完成的请求建立连接后就将其他事件放入延迟队列，释放锁后才处理这部分队列，以便其他进程可以继续抢锁


worker监听处理的过程如图所示，在master启动的时候，为每个端口创建监听套接字listen socked（以下简称lss），然后fork出worker进程，所有worker进程继承同一份lss，为每个ls创建连接和事件结构，每个空闲worker抢锁获取这些lss的处理权。持有锁就将ls的读事件加入到epoll中等待，把接收事件分为两类：优先处理accept,延时处理非建立连接事件。accept后就释放锁。

worker会有三种情况，一种是空闲但没有抢到锁，就等待事件后继续抢锁。另一种是在处理队列中请求，空闲后去抢锁。第三种是空闲并且抢到锁，则持有锁并监听和分配给hander后释放锁，处理队列请求
### 事件驱动+碎片化+异步处理

![][4] 
nginx所有需要等待的全都尽可能的碎片化，并加入到事件中，当事件ready后根据回调调用消费者处理，在Nginx里，Listen后是不需要循环等待accept，把他加入到epoll中，统一在epoll_wait中处理，当有返回直接调用accept。包括后续与客户端建立的主动连接（非Listen的）的所有事件也都统一在epoll_wait中等待，有事件直接调用事件的消费回调函数。在调用epoll_wait时也是一直循环等待事件没有退出，所以就要把事件拆分成特别细小的单元，这些单元都是可以异步执行的，有了epoll这个模型可以把任何涉及到磁盘读写的小粒度事件加入到监控中，比如读过了头第一行就去处理headline把其他的再加入到epoll中。
## 三.nginx架构的思考分析

考虑如果没有nginx，自己实现，是如何实现。


* 先聊运行架构要实现的简化功能概述：服务器要持续启动，监听8000端口，收到请求后解析http协议，若是静态请求，获取文件内容，封装为Http的响应协议格式，发送；若为动态请求，转化为fastcgi协议，转发给fastcgi程序，发送到响应的端口，获取数据后再转化为Http响应协议格式发送。
为了实现高并发，当然要开启多个处理进程，因为要监听同一个端口，需要一个监听进程负责监听端口（在不考虑新的技术支持多个进程同时监听一个端口的情况），accept后分配给处理进程。对于单个监听端口最好设计是单acceptor多进程的形式，然而，这基本上是不可能实现的，因为多个进程处理完的数据如何返回给监听进程，大量的数据再进程间通信是不现实的。因此对于单个监听端口只能是单进程。或者改用线程，然而多线程不稳定。

我可以对多个监听端口开启多个进程，每个监听不同的端口，但端口间流量分配不均匀时，进程负载不均衡。

=》从监听处理进程个数上，nginx比我自己设计聪明的地方体现出来了，特殊的多reactor多进程结构。

再说说每个进程里的高并发，网络连接肯定会有IO等待，此时若可以继续做其他的，会更快。每次接收一个请求的情况下，可以读完请求后，一边请求异步磁盘一边写返回头等；在有子请求和接受多个请求的情况下，可以一边为其他请求建立连接，一边处理本请求的事件，多个请求同时处理。因此设计为单进程可以同时accept多个，每个可以并行的操作都拆为单独事件异步处理。思想和nginx一样。


* 再聊聊开发架构，代码架构可以依照开发架构nginx因为支持反向代理，支持多平台，支持自定义配置，所以有配置模块，统一的事件模块，有抽象的配置结构。自己开发web服务器，可能不会考虑这么多，主要考虑http的处理过程，http固定的读取头，解析头，真实ip，权限，处理，输出协议的转换，写日志做成固定的顺序，直接调用固定函数。nginx再此之上，每个过程有自己的回调，整体阶段清晰，每个模块可以在把回调加入到各个子阶段，更灵活。协议按顺序也先固定输出链，若没有该协议直接跳过，对于这种运行前不知道会有哪些输出过滤的情况，自己写可能就在运行中判断有就调用了，nginx是固定走，没有直接跳过，这两种根据不同应用各有应用吧。


## 高可扩展性


* 模块化无论配置，初始化，代码结构都是模块化的，各个模块要介入到主流程，根本不需要修改主流程代码，通过在hook位置增加回调。


* 高度抽象正常很难想到所有的模块和配置全部都一个抽象结构，各个子模块也都统一抽象结构，新增加功能简单，可读性高


* 输出统一过滤链，功能可插拔扩展容易，代码简洁，可读性高


## 高性能


* 多reactor多进程结构经过上述分析，Nginx在并发选型上要么是单reactor单进程结构，要么是单reactor多线程结构，但多线程只要一个操作共享区域，会影响其他线程，所以在不需要共享数据的情况下，最好用多进程。nginx巧妙的虽然同一时刻只能单reactor，但在accept后立刻释放锁，也达到多reactor的性能，此架构不常见，可以参考。memcache,mysql等因为要共享数据都是多reactor多线程；apache旧版是一个进程处理一个请求，类似phpfpm，本质上是单reactor单进程，后来一个进程中有多个线程，单reactor多线程，但每个线程处理一个请求；后面也加入了IO多路复用，每个线程中处理多个请求。


* 后续preactor+线程


![][5]

```
本质上epoll还是等待的，还是需要进程去询问，利用内核异步IO，可以做到事件自动处理，处理后通知，不需要询问，其架构如下：
```

单linux的AIO还不完善，到目前为止，nginx实现了AIO+线程的模型，但还未应用。

* 内存池，连接池为了省去每次申请，减少内存碎片，统一释放等，提前准备好内存池和连接池   。


## 四.总结

nginx作为一个高性能高可用高可扩展的 http服务器和多协议反向代理服务器，其运行架构采用特殊的监听同一端口却多reactor多进程的模型，值得借鉴；高度抽象和模块化的逻辑架构使得功能庞大代码却清晰易懂，开发和扩展代价低。

* 参考

nginx功能   [http://nginx.org/en/][6]     
nginx代码结构 [https://www.kancloud.cn/diges...][7]

[6]: http://nginx.org/en/
[7]: https://www.kancloud.cn/digest/understandingnginx/202599
[0]: ../img/bVbkq3V.png
[1]: ../img/bVbkq31.png
[2]: ../img/bVbkq34.png
[3]: ../img/bVbkq38.png
[4]: ../img/bVbkq4d.png
[5]: ../img/bVbkq4j.png
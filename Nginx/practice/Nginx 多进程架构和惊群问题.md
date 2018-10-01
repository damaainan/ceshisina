## Nginx 多进程架构和惊群问题

2016.04.19 16:06*

来源：[https://www.jianshu.com/p/7e84a33b46e9](https://www.jianshu.com/p/7e84a33b46e9)


Nginx 多进程架构是：一个master进程和多个worker 进程。

一个worker 通过非阻塞式论询，可维护数千个连接,多个worker共享一个监听套接字.
### Master进程

顾名思义,老板进程，主要负责有轻而巧的工作.

主要通过进程间通信对工人进程发号施令或是处理来自bash的start,stop,reload等用户指令。
### Worker 进程

顾名思义,工人进程，主要负责重而笨的工作，主要负责处理来自浏览器的连接。

网站高并发情况下，巨大的工作负荷都是压到工人进程，老板进程在一旁观看指挥。

在TCP Socket 服务开发中,多进程或多线程共享监听套接字时面临惊群问题.


* 对于主流的linx版本, accept 阻塞调用,已经不存在惊群问题.

也就是说多个进程同时accept 同一个 监听套接字,只有一个进程获的连接.

* 对于`epoll_wait` 非阻塞式的创建连接方式, 存在惊群问题。(即：一个连接请求唤醒多个worker 进程).


Nginx 在linux系统中使用`epoll_wait` 非阻塞式的方式，存在惊群问题。

浏览器的请求连接不经过master进程， **`直接`** 由worker 进程处理,

但是一个请求如何分配到特定的worker进程？


* nginx 默认的配置accept_mutex on;

多个worker 进程通过争锁获得连接，同时只有一个worker获得连接。

工人进程抢着活干（让我来，别和我争）
* accept_mutex off

一个连接请求唤醒多个worker 进程，同时只有一个worker获得连接。

存在惊群问题，由于nginx 的worker 进程数量不大，这个惊群问题影响不大。

少了争锁，这个配置高并发时可提高系统的响应能力。
* 开启SO_REUSEPORT选项: **`reuseport`** 


```nginx
http {
        server {
          listen 80 reuseport;
          server_name  localhost;
          ...
     }
}

```

SO_REUSEPORT选项,是Linux 内核3.9+处理大并发连接的新特性。

开启后，连接请求通过linux内核分配到worker 进程，性能最好。

此选项的系统需求：

Nginx 1.9.1+

DragonFly BSD/Linux 内核3.9+

参考：

[http://blog.csdn.net/Marcky/][0]

[https://www.nginx.com/blog/socket-sharding-nginx-release-1-9-1/][1]


[0]: https://link.jianshu.com?t=http://blog.csdn.net/Marcky/
[1]: https://link.jianshu.com?t=https://www.nginx.com/blog/socket-sharding-nginx-release-1-9-1/
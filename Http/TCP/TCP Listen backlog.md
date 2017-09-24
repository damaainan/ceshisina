# TCP Listen backlog

**水平有限，如有纰漏，敬请指正！**

![][1]

TCP有限状态机

## TCP服务

创建TCP服务的四个基本步骤：

1. socket – 创建socket套接字。
1. bind – 绑定要监听的IP地址。
1. listen – 开始监听客户端连接请求。
1. accept – 获取TCP握手成功的连接。

其中，第3步，开始监听客户端的连接请求时，需要指定一个backlog的参数：

    int listen(int sockfd, int backlog);

这个backlog参数有什么作用呢？不同的操作系统可能有不同的意义，**本文只讨论backlog参数在Linux环境下的作用**。

## TCP连接

建立TCP连接有一个“三次握手”的过程：

1. 客户端向服务端发起连接请求，发送SYN包。
1. 服务端收到客户端的SYN包后向客户端响应ACK+SYN包，同时在内存中建立一个状态为**SYN-RECEIVED**的连接，将连接放进**incomplete connection queue**。
1. 客户端收到服务端的回包后，向服务发送ACK包。服务端收到ACK后，TCP连接进入**ESTABLISHED**状态，将连接放进**complete connection queue**，等待应用程序进行accept。

* 在Linux内核中，步骤2的未完成TCP连接由一个incomplete connection queue维护，其最大长度为/proc/sys/net/ipv4/tcp_max_syn_backlog。
* 步骤3的已完成TCP连接由一个complete connection queue维护，其最大长度为listen函数的参数backlog。
* 画个简图，总结一下上面的内容：

![][2]


TCP握手连接小结

## 当队列满了……

### SYN cookie

[SYN cookie][3]是一种用于对抗[SYN flood][4]攻击的技术，可以避免在incomplete connection queue被填满时无法建立新的TCP连接。对于使用SYN Cookie的服务来说， 当incomplete connection queue被填满时，服务器会表现得像SYN队列扩大了一样。对于队列填满后的新TCP连接，服务器会返回适当的SYN+ACK响应包，但会丢弃对应的SYN队列条目（因为队列已经满了）。如果服务器收到客户端随后的ACK响应，**服务器能够使用编码在 TCP 序号内的信息重构 SYN 队列条目**。

### incomplete connection queue

* 关闭syncookies(net.ipv4.tcp_syncookies = 0)：当队列满时，不在接受新的连接。
* 开启syncookies(net.ipv4.tcp_syncookies = 1)：当队列满时，不受影响。

### complete connection queue

当complete connection queue满了，已经完成三次握手的TCP连接该怎么办？

内核收到客户端的ACK包后，会执行函数[tcp_check_req][5]，进行一些检查后，准备把TCP连接放到complete connection queue中:

    child = inet_csk(sk)->icsk_af_ops->syn_recv_sock(sk, skb, req, NULL, req, &own_req);
    if (!child)
      goto listen_overflow;

对于ipv4来说，执行的是函数[tcp_v4_syn_recv_sock][6]

    if (sk_acceptq_is_full(sk))
        goto exit_overflow;

exit_overflow处的代码会进行一些清理工作，然后返回NULL，最后执行listen_overflow处的代码：

    listen_overflow:
        if (!sysctl_tcp_abort_on_overflow) {
            inet_rsk(req)->acked = 1;
            return NULL;
        }

* 当sysctl_tcp_abort_on_overflow为0时，Linux内核只丢弃客户端的ACK包，然后“什么都不做”。
* 当sysctl_tcp_abort_on_overflow非0时，Linux内核会返回RST包，reset TCP连接。
* sysctl_tcp_abort_on_overflow对应的设置是/proc/sys/net/ipv4/tcp_abort_on_overflow。

（完）

[0]: /u/96141c0d5c5c
[1]: http://upload-images.jianshu.io/upload_images/1814354-203181190336bdc6.png
[2]: http://upload-images.jianshu.io/upload_images/1814354-0cc83f19ef19d595.png
[3]: https://en.wikipedia.org/wiki/SYN_cookies
[4]: https://en.wikipedia.org/wiki/SYN_flood
[5]: https://github.com/torvalds/linux/blob/master/net/ipv4/tcp_minisocks.c#L567
[6]: https://github.com/torvalds/linux/blob/master/net/ipv4/tcp_ipv4.c#L1267
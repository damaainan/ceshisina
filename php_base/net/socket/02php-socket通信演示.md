# 【PHPsocket编程专题(实战篇①)】php-socket通信演示

PHP- - -

建立Socket连接至少需要一对套接字，其中一个运行于客户端，称为ClientSocket ，另一个运行于服务器端，称为ServerSocket 。   
套接字之间的连接过程分为三个步骤：服务器监听，客户端请求，连接确认。   
**服务器监听**：服务器端套接字并不定位具体的客户端套接字，而是处于等待连接的状态，实时监控网络状态，等待客户端的连接请求。   
**客户端请求**：指客户端的套接字提出连接请求，要连接的目标是服务器端的套接字。为此，客户端的套接字必须首先描述它要连接的服务器的套接字，指出服务器端套接字的地址和端口号，然后就向服务器端套接字提出连接请求。   
**连接确认**：当服务器端套接字监听到或者说接收到客户端套接字的连接请求时，就响应客户端套接字的请求，建立一个新的线程，把服务器端套接字的描述发给客户端，一旦客户端确认了此描述，双方就正式建立连接。而服务器端套接字继续处于监听状态，继续接收其他客户端套接字的连接请求。

## 认识socket相关的PHP函数

    

```
socket_accept()  # 接受一个Socket连接
socket_bind()  # 把socket绑定在一个IP地址和端口上
socket_clear_error()  # 清除socket的错误或者最后的错误代码
socket_close()  # 关闭一个socket资源
socket_connect()  # 开始一个socket连接
socket_create_listen()  # 在指定端口打开一个socket监听
socket_create_pair()  # 产生一对没有区别的socket到一个数组里
socket_create()  # 产生一个socket，相当于产生一个socket的数据结构
socket_get_option()  # 获取socket选项
socket_getpeername()  # 获取远程类似主机的ip地址
socket_getsockname()  # 获取本地socket的ip地址
socket_iovec_add()  # 添加一个新的向量到一个分散/聚合的数组
socket_iovec_alloc()  # 这个函数创建一个能够发送接收读写的iovec数据结构
socket_iovec_delete()  # 删除一个已经分配的iovec
socket_iovec_fetch()  # 返回指定的iovec资源的数据
socket_iovec_free()  # 释放一个iovec资源
socket_iovec_set()  # 设置iovec的数据新值
socket_last_error()  # 获取当前socket的最后错误代码
socket_listen()  # 监听由指定socket的所有连接
socket_read()  # 读取指定长度的数据
socket_readv()  # 读取从分散/聚合数组过来的数据
socket_recv()  # 从socket里结束数据到缓存
socket_recvfrom()  # 接受数据从指定的socket，如果没有指定则默认当前socket
socket_recvmsg()  # 从iovec里接受消息
socket_select()  # 多路选择
socket_send()  # 这个函数发送数据到已连接的socket
socket_sendmsg()  # 发送消息到socket
socket_sendto()  # 发送消息到指定地址的socket
socket_set_block()  # 在socket里设置为块模式
socket_set_nonblock()  # socket里设置为非块模式
socket_set_option()  # 设置socket选项
socket_shutdown()  # 这个函数允许你关闭读、写、或者指定的socket
socket_strerror()  # 返回指定错误号的详细错误
socket_write()  # 写数据到socket缓存
socket_writev()  # 写数据到分散/聚合数组
```

## 创建一个socket

产生一个Socket，你需要三个变量：一个协议、一个socket类型和一个公共协议类型。产生一个socket有三种协议供选择，继续看下面的内容来获取详细的协议内容。   
定义一个公共的协议类型是进行连接一个必不可少的元素。下面的表我们看看有那些公共的协议类型。

**表一：协议**

名字/常量 描述 AF_INET 这是大多数用来产生socket的协议，使用TCP或UDP来传输，用在IPv4的地址 AF_INET6 与上面类似，不过是来用在IPv6的地址 AF_UNIX 本地协议，使用在Unix和Linux系统上，它很少使用，一般都是当客户端和服务器在同一台及其上的时候使用 

**表二：Socket类型**

名字/常量 描述 SOCK_STREAM 这个协议是按照顺序的、可靠的、数据完整的基于字节流的连接。这是一个使用最多的socket类型，这个socket是使用TCP来进行传输。 SOCK_DGRAM 这个协议是无连接的、固定长度的传输调用。该协议是不可靠的，使用UDP来进行它的连接。 SOCK_SEQPACKET 这个协议是双线路的、可靠的连接，发送固定长度的数据包进行传输。必须把这个包完整的接受才能进行读取。 SOCK_RAW 这个socket类型提供单一的网络访问，这个socket类型使用ICMP公共协议。（ping、traceroute使用该协议） SOCK_RDM 这个类型是很少使用的，在大部分的操作系统上没有实现，它是提供给数据链路层使用，不保证数据包的顺序 

**表三：公共协议**

名字/常量 描述 ICMP 互联网控制消息协议，主要使用在网关和主机上，用来检查网络状况和报告错误信息 UDP 用户数据报文协议，它是一个无连接，不可靠的传输协议 TCP 传输控制协议，这是一个使用最多的可靠的公共协议，它能保证数据包能够到达接受者那儿，如果在传输过程中发生错误，那么它将重新发送出错数据包。 

现在你知道了产生一个socket的三个元素，那么我们就在php中使用socket_create()函数来产生一个socket。这个socket_create()函数需要三个参数：一个协议、一个socket类型、一个公共协议。socket_create()函数运行成功返回一个包含socket的资源类型，如果没有成功则返回false

## socket通讯演示

![05172951-a955fce4e5d04082828e717fe0e102f9.jpg-26kB][0]

服务器端:server.php

    

```php
<?php
//确保在连接客户端时不会超时
set_time_limit(0);
$ip = '127.0.0.1';
$port = 1935;
/*
 +-------------------------------
 *    @socket通信整个过程
 +-------------------------------
 *    @socket_create
 *    @socket_bind
 *    @socket_listen
 *    @socket_accept
 *    @socket_read
 *    @socket_write
 *    @socket_close
 +--------------------------------
*/
/*----------------    以下操作都是手册上的    -------------------*/
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) { // 创建一个Socket链接
    echo "socket_create() 失败的原因是:" . socket_strerror($sock) . "\n";
}
if (($ret = socket_bind($sock, $ip, $port)) < 0) { //绑定Socket到端口
    echo "socket_bind() 失败的原因是:" . socket_strerror($ret) . "\n";
}
if (($ret = socket_listen($sock, 4)) < 0) { // 开始监听链接链接
    echo "socket_listen() 失败的原因是:" . socket_strerror($ret) . "\n";
}
$count = 0;
do {
    if (($msgsock = socket_accept($sock)) < 0) { //堵塞等待另一个Socket来处理通信
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        break;
    } else {
        //发送消息到客户端
        $msg = "测试成功！\n";
        socket_write($msgsock, $msg, strlen($msg)); 
        //接收客户端消息
        echo "测试成功了啊\n";
        $buf = socket_read($msgsock, 8192); // 获得客户端的输入
        $talkback = "收到的信息:$buf\n";
        echo $talkback;
        if (++$count >= 5) {
            break;
        };
    }
    //echo $buf;
    socket_close($msgsock);
} while (true);
socket_close($sock);
?>
```

然后php server.php,发现1935端口已经处于被监听状态;接下来我们只要运行客户端程序即可连接上。

![QQ截图20151010134926.png-6.1kB][1]

这样就完成第一步**服务器监听**：服务器端套接字并不定位具体的客户端套接字，而是处于等待连接的状态，实时监控网络状态，等待客户端的连接请求。

接下来就第二步**客户端请求**: 

    

```php
<?php
error_reporting(E_ALL);
set_time_limit(0);
echo "<h2>TCP/IP Connection</h2>\n";
$port = 1935;
$ip = "127.0.0.1";
/*
 +-------------------------------
 *    @socket连接整个过程
 +-------------------------------
 *    @socket_create
 *    @socket_connect
 *    @socket_write
 *    @socket_read
 *    @socket_close
 +--------------------------------
*/
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// 第一个参数”AF_INET”用来指定域名;
// 第二个参数”SOCK_STREM”告诉函数将创建一个什么类型的Socket(在这个例子中是TCP类型),UDP是SOCK_DGRAM
if ($socket < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
} else {
    echo "OK.\n";
}
echo "试图连接 '$ip' 端口 '$port'...\n";
$result = socket_connect($socket, $ip, $port);
if ($result < 0) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
} else {
    echo "连接OK\n";
}
$in = "Ho\r\n";
$in.= "first blood\r\n";
$out = '';
if (!socket_write($socket, $in, strlen($in))) {
    echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
} else {
    echo "发送到服务器信息成功！\n";
    echo "发送的内容为:<font color='red'>$in</font> <br>";
}
while ($out = socket_read($socket, 8192)) {
    echo "接收服务器回传信息成功！\n";
    echo "接受的内容为:", $out;
}
echo "关闭SOCKET...\n";
socket_close($socket);
echo "关闭OK\n";
?>
```


这时我们来看看各自的链接(先不管图中的错误,这是我php配置有问题~)

![QQ截图20151010140519.png-29.4kB][2]


![QQ截图20151010140341.png-10.5kB][3]

然后服务器端接着处于监听状态,每次client请求都会接到反馈,注意该列使用的socket通讯方式其实是很落后的同步阻塞 IO 模型,其上还有同步非阻塞 IO 模型(select/poll 的同步模型)以及使用 epoll/kqueue 的异步模型：属于异步阻塞/非阻塞 IO 模型;(大多数都是epoll/kqueue模型)

具体参考: [http://www.cnblogs.com/lchb/articles/3078169.html][4]

[0]: ../img/05172951-a955fce4e5d04082828e717fe0e102f9.jpg
[1]: ../img/QQ%E6%88%AA%E5%9B%BE20151010134926.png
[2]: ../img/QQ%E6%88%AA%E5%9B%BE20151010140519.png
[3]: ../img/QQ%E6%88%AA%E5%9B%BE20151010140341.png
[4]: http://www.cnblogs.com/lchb/articles/3078169.html
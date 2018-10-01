## phper该懂的socket

2017.01.04 20:47*

来源：[https://www.jianshu.com/p/0a9f91257b8e](https://www.jianshu.com/p/0a9f91257b8e)

Swoole 2.0正式版发布了。增加了对协程和PHP7的支持。听起来就是一个让人振奋的消息。感谢有这样一群phper他们在努力为大多数phper做着贡献。但是，毕竟swoole重新定义了php，那我们必须也重新学习起来了。

学习swoole，必须有一些前置知识的学习，比如：多进程/多线程，socket，IO复用，TCP/IP网络协议等。表捉急，慢慢来……
## 什么是socket？

概念：套接字（socket）是通信的基石，是支持TCP/IP协议的网络通信的基本操作单元。它是网络通信过程中端点的抽象表示，包含进行网络通信必须的五种信息：连接使用的协议，本地主机的IP地址，本地进程的协议端口，远地主机的IP地址，远地进程的协议端口。

懵逼了吧？简单点，姐觉得套接字（socket）就是TCP/IP协议对外提供的接口，你要写个应用程序要向网络发出请求，应答请求，你就用他就对了。通过Socket，我们才能使用TCP/IP协议。但是sokect又不仅仅是应用于TCP/IP协议。

这里又引出来一个概念，TCP/IP协议。要想理解socket首先得熟悉一下TCP/IP协议族。顺便复习一下吧。大学没好好学，都还给老师了吧。
###### 什么是TCP协议？

就是三次握手，四次分手，面向连接的，可靠的传输层协议。（此处要记住tcp协议的11中状态，细节自己去搜索吧）
###### OSI参考模型

顺便复习下七层网络吧，当年大学修网络这门课的时候，这可是必考题呢。

应用层，表示层，会话层，传输层，网络层，数据链路层，物理层。

不同于ISO模型的七个分层，TCP/IP协议参考模型把所有的TCP/IP系列协议归类到四个抽象层中

应用层：TFTP，HTTP，SNMP，FTP，SMTP，DNS，Telnet 等等

传输层：TCP，UDP

网络层：IP，ICMP，OSPF，EIGRP，IGMP

数据链路层：SLIP，CSLIP，PPP，MTU

每一抽象层建立在低一层提供的服务上，并且为高一层提供服务，看起来大概是这样子的


![][0]


TCP/IP协议参考模型


![][1]


TCP/IP协议参考模型

###### Socket在哪里呢？


![][2]


原来Socket在这里

###### 啥原理？咋用？

以前听到Socket编程，觉得它是比较高深的编程知识，但是对于phper来说，我们只需要知道他的工作原理就够了。下面这个例子很好得解释了这个原理。

一个生活中的场景。你要打电话给一个朋友，先拨号，朋友听到电话铃声后提起电话，这时你和你的朋友就建立起了连接，就可以讲话了。等交流结束，挂断电话结束此次交谈。拿下图来解释这个过程就是：


![][3]


基于TCP的socket编程客户端和服务端通信过程


基于TCP（面向连接）的socket编程，分为客户端和服务器端。

客户端的流程如下：

（1）创建套接字（socket）

（2）向服务器发出连接请求（connect）

（3）和服务器端进行通信（send/recv）

（4）关闭套接字

服务器端的流程如下：

（1）创建套接字（socket）

（2）将套接字绑定到一个本地地址和端口上（bind）

（3）将套接字设为监听模式，准备接收客户端请求（listen）

（4）等待客户请求到来；当请求到来后，接受连接请求，返回一个新的对应于此次连接的套接字（accept）

（5）用返回的套接字和客户端进行通信（send/recv）

（6）返回，等待另一个客户请求。

（7）关闭套接字。

## 和phper相关的socket知识

谈了很多理论，这些理论很多phper其实都不是很明白，因为这门语言诞生之初，就是为了web而生的，我们大部分的时间都在写的是网页，网页是什么？就是http协议喽~
###### Socket与HTTP

由于通常情况下Socket连接就是TCP连接，因此Socket连接一旦建立，通信双方即可开始相互发送数据内容，直到双方连接断开。

而HTTP连接使用的是“请求—响应”的方式，不仅在请求时需要先建立连接，而且需要客户端向服务器发出请求后，服务器端才能回复数据。

很多情况下，需要服务器端主动向客户端推送数据，保持客户端与服务器数据的实时与同步。此时若双方建立的是Socket连接，服务器就可以直接将数据传送 给客户端；若双方建立的是HTTP连接，则服务器需要等到客户端发送一次请求后才能将数据传回给客户端，因此，客户端定时向服务器端发送连接请求，不仅可以保持在线，同时也是在“询问”服务器是否有新的数据，如果有就将数据传给客户端。

通常，我们的ajax做心跳就是定时向服务器端发出请求，询问是否有新的数据。
###### 用php实现一个socket

我们写个简单的例子哦~


* 安装socket扩展，默认是没有安装的呢
`/alidata/server/php/bin/phpize ./configure --enable-sockets --with-php-config=/alidata/server/php/bin/php-config make make install vim /alidata/server/php-5.4.41/etc/php.ini （加入extension=php_sockets.so） service httpd restart`



![][4]


扩展安装好了


可以开始编程了
 **`服务端代码`** 

```php
#!/alidata/server/php/bin/php -q
<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
//本地IP
$address = '127.0.0.1';
//设置用111端口进行通信
$port = 10000;
//创建SOCKET
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket创建失败原因 " . socket_strerror($sock) . "\n";
}
if (($ret = socket_bind($sock, $address, $port)) < 0) {
    echo "创建套接字失败原因 " . socket_strerror($ret) . "\n";
}
//监听
if (($ret = socket_listen($sock, 5)) < 0) {
    echo "listen failed reason: " . socket_strerror($ret) . "\n";
}
do {
    //接收命令 
    if (($msgsock = @socket_accept($sock)) < 0) {
        echo "failed reason: " . socket_strerror($msgsock) . "\n";
        break;
    }
    $msg = "\nPHP Test Server. \n" ."use quit,shutdown,sun...\n";
    @socket_write($msgsock, $msg, strlen($msg));
    do {
        if (false === ($buf = @socket_read($msgsock, 20, PHP_NORMAL_READ))) {
            echo "socket_read() failed: reason: " . socket_strerror($ret) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'quit') {
            break;
        }
        if ($buf == 'shutdown') {
            socket_close($msgsock);
            break 2;
        }
        if ($buf == 'sun') {
            echo'what are you doing?';
        }
        $talkback = "Backinformation : '$buf'.\n";
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "$buf\n";
    } while (true);

```
 **`客户端代码`** 

```php
<?php
error_reporting(E_ALL);
$service_port = 10000;
$address = '127.0.0.1';
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket < 0) {
    echo "socket create failed. : " . socket_strerror($socket) . "\n";
} else {
    echo "OK.\n";
}
$result = socket_connect($socket, $address, $service_port);
if ($result < 0) {
    echo "SOCKET connect failed. : ($result) " . socket_strerror($result) . "\n";
} else {
    echo "OK.\n";
}

$in = "HEAD / HTTP/1.1\r\n";
$in .= "Connection: Close\r\n\r\n";
$out = '';
echo "Send Command..........";
$in .= "quit\n";
socket_write($socket, $in, strlen($in));
echo "OK.\n";
echo "Reading Backinformatin:\n\n";
while ($out = socket_read($socket, 2048)) {
    echo $out;
}
echo "Close socket........";
socket_close($socket);
echo "OK,haha.\n\n";

```

最近，项目中用的php往cat（大众点评那套）里写日志，打算用swoole来改造一下。

大致思路如下，php的web的项目中，同步往本机的一个swoole服务端写日志。由swoole再去和cat的server进行长连接非阻塞的通信。如果直接在php的web项目进程中直接连接cat服务端，是同步阻塞的方式，如果连接cat的server慢，会造成业务受到影响。缺点就是每个服务器都要部署一套swoole服务端呢。

完


![][5]


以强化Web世界为目的制作出来的女性机器人（图片来自网络）


[0]: ../img/2735552-8e3d32fbf5845715.jpg
[1]: ../img/2735552-db7c6bfa34537520.gif
[2]: ../img/2735552-66ef2cb7bc212811.jpg
[3]: ../img/2735552-00402b69875b06ae.jpg
[4]: ../img/2735552-54385b08642dce3a.png
[5]: ../img/2735552-55a338aedb4c0042.jpg
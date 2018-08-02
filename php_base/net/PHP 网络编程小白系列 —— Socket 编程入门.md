## PHP 网络编程小白系列 —— Socket 编程入门

来源：[https://segmentfault.com/a/1190000015760974](https://segmentfault.com/a/1190000015760974)

这篇文章将会介绍一下 Socket 编程中相关的 PHP 函数，并简单实现一个 C／S 的交互
## Socket 简介

Socket 的官方解释：
在网络编程中最常用的方案便是Client/Server(客户机/服务器)模型。在这种方案中客户应用程序向服务器程序请求服务。一个服务程序通常在一个众所周知的地址监听对服务的请求，也就是说，服务进程一 直处于休眠状态，直到一个客户向这个服务的地址提出了连接请求。在这个时刻，服务程序被"惊醒"并且为客户提供服务－对客户的请求作出适当的反应。为了方便这种Client/Server模型的网络编程，90年代初，由Microsoft联合了其他几家公司共同制定了一套WINDOWS下的网络编程接口，即WindowsSockets规范，它不是一种网络协议,而是一套开放的、支持多种协议的Windows下的网络编程接口。现在的Winsock已经基本上实现了与协议无关，你可以使用Winsock来调用多种协议的功能，但较常使用的是TCP/IP协议。Socket实际在计算机中提供了一个通信端口，可以通过这个端口与任何一个具有Socket接口的计算机通信。应用程序在网络上传输，接收的信息都通过这个Socket接口来实现

我们可以简单的把 Socket 理解为一个可以连通网络上不同计算机应用程序之间的管道，把一堆数据从管道的 A 端扔进去，则会从管道的 B 端（同时还可以从C、D、E、F……端冒出来）。
`注意`：我们会在不同语境下使用不同的词语去修饰 socket，你只需要对它有个概念就好了，因为 socket 本身就没有真正意义上的实体
## Socket 函数介绍

Socket 通信依次会进行 Socket 创建、 Socket 绑定、Socket 监听、Socket 收发、Socket 关闭几个阶段，下面我们列举出 PHP 网络编程中最常用也是必不可少的几个常用的函数进行进一步的说明。
#### socket_create

TODO ： 创建一个新的 socket 资源
函数原型:`resource socket_create ( int $domain , int $type , int $protocol )`
它包含三个参数，分别如下：


* domain：AF_INET、AF_INET6、AF_UNIX，`AF`的释义就`address family`，地址族的意思，我们常用的有 ipv4、ipv6
* type: SOCK_STREAM、SOCK_DGRAM等，最常用的就是`SOCK_STREAM`，基于字节流的SOCKET类型，也是TCP协议使用的类型
* protocol: SOL_TCP、SOL_UDP 这个就是具体使用的传输协议，一般可靠的传输我们选择 TCP，游戏数据传输我们一般选用 UDP 协议


#### socket_bind

TODO ： 将创建的 socket 资源绑定到具体的 ip 地址和端口
函数原型:`bool socket_bind ( resource $socket , string $address [, int $port = 0 ] )`它包含三个参数，分别如下：


* socket: 使用`socket_create`创建的 socket 资源，可以认为是 socket 对应的 id
* address: ip 地址
* port: 监听的端口号，WEB 服务器默认80端口


#### socket_listen

TODO ： 在具体的地址下监听 socket 资源的收发操作
函数原型:`bool socket_listen ( resource $socket [, int $backlog = 0 ] )`它包含两个个参数，分别如下：


* socket: 使用`socket_create`创建的socket资源
* backlog: 等待处理连接队列的最大长度


#### socket_accept

TODO ： 监听之后，接收一个即将来临的新的连接，如果连接建立成功，将返回一个新的 socket 句柄（你可以理解为子进程，通常父进程用来接收新的连接，子进程负责具体的通信）
函数原型:`resource socket_accept ( resource $socket )`
* socket: 使用`socket_create`创建的socket资源

#### socket_write

TODO ： 将指定的数据发送到 对应的 socket 管道
函数原型:`int socket_write ( resource $socket , string $buffer [, int $length ] )`

* socket: 使用`socket_create`创建的socket资源
* buffer: 写入到`socket`资源中的数据
* length: 控制写入到`socket`资源中的`buffer`的长度，如果长度大于`buffer`的容量，则取`buffer`的容量


#### socket_read

TODO ： 获取传送的数据
函数原型:`int socket_read ( resource $socket ,  int $length )`

* socket: 使用`socket_create`创建的socket资源
* length:`socket`资源中的`buffer`的长度


#### socket_close

TODO ： 关闭 socket 资源
函数原型:`void socket_close ( resource $socket )`
* socket:`socket_accept`或者`socket_create`产生的资源，不能用于`stream`资源的关闭

#### stream_socket_server

由于创建一个SOCKET的流程总是 socket、bind、listen，所以PHP提供了一个非常方便的函数一次性创建、绑定端口、监听端口

函数原型:`resource stream_socket_server ( string $local_socket [, int &$errno [, string &$errstr [, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN [, resource $context ]]]] )`

* local_socket: 协议名://地址:端口号
* errno: 错误码
* errstr:  错误信息
* flags: 只使用该函数的部分功能
* context: 使用`stream_context_create`函数创建的资源流上下文


## socket 实现 C／S 交互

基于上面的函数我们可以很方便的去构建 socket 通信程序（在这里我希望读者能单独建立一个目录比如`socket`因为后续我们还会建立很多文件）我们先编辑一个服务端程序`server.php`，如下：

```php
<?php

date_default_timezone_set("Asia/Shanghai");
error_reporting(E_NOTICE );

/*  确保在连接客户端时不会超时   */
set_time_limit(0);

$ip = '127.0.0.1';
$port = 8090;

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
if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
    echo "socket_create() Why failure is:".socket_strerror($sock)."\n";
}

if(($ret = socket_bind($sock,$ip,$port)) < 0) {
    echo "socket_bind() Why failure is:".socket_strerror($ret)."\n";
}

if(($ret = socket_listen($sock,4)) < 0) {
    echo "socket_listen() Why failure is:".socket_strerror($ret)."\n";
}

echo "Start time:".date('Y-m-d H:i:s') . PHP_EOL;
echo "Listening at ".$ip.':'.$port.PHP_EOL;


do {
    /*  创建新的连接  */
    if (($msgsock = socket_accept($sock)) < 0) {
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        break;
    } else {
        
    # 连接成功输出 Socket id
    $i = (int)$msgsock;
    echo "welcome client $i";

        # 向客户端通信(反馈)
        $msg ="连接成功！\n";
        socket_write($msgsock, $msg, strlen($msg));
    }
    socket_close($msgsock);
} while (true);
socket_close($sock);
?>

```

再编辑一个客户端程序`client.php`，如下：

```php
<?php



set_time_limit(0);
$port = 8090;
$ip = "127.0.0.1";

/*
 +-------------------------------
 *    客户端 socket 连接整个过程
 +-------------------------------
 *    @socket_create
 *    @socket_connect
 *    @socket_write
 *    @socket_read
 *    @socket_close
 +--------------------------------
 */


/**
 * @socket_connect:客户端发起套接字连接
 * @param socket  resource $socket       创建的$socket资源
 * @param address string   SOCK_STREAM   IP地址|Unix套接字
 * @param port    int                    端口
 */

/**
 * @socket_create:创建并返回一个套接字
 * @param domain   string AF_INET         IPV4 网络协议
 * @param type     string SOCK_STREAM     全双工字节流（可用的套接字类型）
 * @param protocol string SOL_TCP         具体协议（IPV4下的TCP协议）
 * @param return   套接字
 */

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
}else {
    echo "try to connect '$ip' port: '$port'...\n";
}


$result = socket_connect($socket, $ip, $port);  #socket_connect的返回值应该是boolean值
if ($result < 0) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
}else {
    # 连接成功输出提示信息
    echo "connect successfully\n";

    # 向服务端发送数据
    socket_write($socket, " hello ", 1024);

    # 获取服务端数据
    $result = socket_read($socket, 1024);
    echo "服务器回传数据为：" . $result;


    echo "CLOSE SOCKET...\n";
    socket_close($socket);
    echo "CLOSE OK\n";    
    
}



?>

```

然后我们打开终端(命令行)进入文件目录下依次执行：

```
php server.php
php client.php
```

运行效果如下：

![][0]
`注意`服务器监听时进程是挂起的不能进行其他操作，你可能需要另起一个终端执行客户端程序
## Socket 编程入门结语

本篇文章就是为大家整理了一下 PHP Socket 编程常用的函数并解释了一下各自的意义，然后写了一个简单的 C／S 交互，希望大家对网络编程有个比较直观的认识，下篇文章我会简单讲讲进程在网络编程中的作用，这也是为后面网络模型的讲解打个基础

[0]: ./img/bVbeih9.png
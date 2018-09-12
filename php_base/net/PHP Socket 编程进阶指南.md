## PHP Socket 编程进阶指南

来源：[http://www.cnblogs.com/52fhy/p/9293015.html](http://www.cnblogs.com/52fhy/p/9293015.html)

时间 2018-07-11 10:08:00

## 学习准备

* Linux 或者 Mac 环境； 
* 安装有 Sockets 扩展； 
* 了解 TCP/IP 协议。 

socket函数只是PHP扩展的一部分，编译PHP时必须在配置中添加`--enable-sockets`配置项来启用。
 
如果自带的PHP没有编译scokets扩展，可以下载相同版本的源码，进入`ext/sockets`使用`phpize`编译安装。
 
## socket系列函数

socket服务端/客户端流程：

![][0]

图中所示流程在任何编程语言里都是通用的。
 
### server端
 
接下来我们写一个简单的单进程TCP服务器：
 
socket_tcp_server.php
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */
 
//参数domain: AF_INET,AF_INET6,AF_UNIX
//参数type: SOCK_STREAM,SOCK_DGRAM
//参数protocol: SOL_TCP,SOL_UDP
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
if(!$socket) die("create server fail:".socket_strerror(socket_last_error())."\n");

//绑定
$ret = socket_bind($socket, "0.0.0.0", 9201);
if(!$ret) die("bind server fail:".socket_strerror(socket_last_error())."\n");

//监听
$ret = socket_listen($socket, 2);
if(!$ret) die("listen server fail:".socket_strerror(socket_last_error())."\n");
echo "waiting client...\n";

while(1){
    //阻塞等待客户端连接
    $conn = socket_accept($socket);
    if(!$conn){
        echo "accept server fail:".socket_strerror(socket_last_error())."\n";
        break;
    }

    echo "client connect succ.\n";

    parseRecv($conn);
}

/**
* 解析客户端消息
* 协议：换行符(\n)
*/
function parseRecv($conn)
{
    //循环读取消息
    $recv = ''; //实际接收到的消息
    while(1){
        $buffer = socket_read($conn, 100); //每次读取100byte
        if($buffer === false || $buffer === ''){
            echo "client closed\n";
            socket_close($conn); //关闭本次连接
            break;
        }

        //解析单次消息，协议：换行符
        $pos = strpos($buffer, "\n");
        if($pos === false){ //消息未读取完毕，继续读取
            $recv .= $buffer;
        }else{ //消息读取完毕
            $recv .= trim(substr($buffer, 0, $pos+1)); //去除换行符及空格

            //客户端主动端口连接
            if($recv == 'quit'){
                echo "client closed\n";
                socket_close($conn); //关闭本次连接
                break;
            }

            echo "recv: $recv \n";
            socket_write($conn, "$recv \n"); //发送消息

            $recv = ''; //清空消息，准备下一次接收
        }
    }
}

socket_close($socket);
```
 
说明：例子里我们先创建了一个TCP server，然后循环等待客户端连接。收到客户端连接后，循环解析来自客户端的消息。
 
例子里使用`\n`作为消息结束符，如果一次没有接收到完整消息，就循环读取，直到遇到结束符；读取完一条完整消息后，向客户端发送收到的消息，然后清空消息，准备下一次接收。
 
我们在命令行里运行服务端：
 
```
$ php socket_tcp_server.php 
waiting client...
```
 
新开终端使用telnet连接：
 
```
$ telnet 127.0.0.1 9201
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
hello Server!
```
 
我们发送了一条消息，服务端这边会收到：
 
```
client connect succ.
recv: hello Server!
```
 
接下来，我们使用socket写一个自己的tcp客户端。
 
### client端
 
下面的例子很简单，创建客户端，连接服务端，发送消息，读取完后就结束了。
 
socket_tcp_client.php
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */
 
//创建连接
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
if(!$socket) die("create server fail:".socket_strerror(socket_last_error())."\n");

//连接server
$ret = socket_connect($socket, "127.0.0.1", 9201);
if(!$ret) die("client connect fail:".socket_strerror(socket_last_error())."\n");

//发送消息
socket_write($socket, "hello, I'm client!\n");

//读取消息
$buffer = socket_read($socket, 1024);
echo "from server: $buffer\n";

//关闭连接
socket_close($socket);
```
 
我们先在原来的telnet终端页面输入`quit`退出连接，因为此时我们的服务端还只能接受一个客户端连接。然后运行自己写的客户端：
 
```
$ php socket_tcp_client.php 
from server: hello, I'm client!
```
 
### socket_select
 
上面的例子里，我们的tcp服务端仅能接受一个客户端连接。怎么能做到支持多个客户端连接呢？常用的有：

* 多进程 
* 多线程 
* I/O复用，使用select、poll、epoll等技术 
* 多进程+I/O复用 

本节里我们使用第三种方法，即I/O复用。技术实现层面则是使用PHP提供的socket_select系统调用来实现。
 
I/O复用使得程序能同时监听多个文件描述符。实现I/O复用的系统调用主要的有select、poll、epoll。
 
接下来看实例：
 
socket_select.php
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
if(!$socket) die("create server fail:".socket_strerror(socket_last_error())."\n");

//绑定
$ret = socket_bind($socket, "0.0.0.0", 9201);
if(!$ret) die("bind server fail:".socket_strerror(socket_last_error())."\n");

//监听
$ret = socket_listen($socket, 2);
if(!$ret) die("listen server fail:".socket_strerror(socket_last_error())."\n");
echo "waiting client...\n";

$clients = [$socket];
$recvs = [];

while(1){

    $read = $clients; //拷贝一份，socket_select会修改$read
    $ret = @socket_select($read, $write = NULL, $except = NULL,0);
    if($ret === false){
        break;
    }

    foreach ($read as $k=>$client) {

        //新连接
        if($client === $socket){
            //阻塞等待客户端连接
            $conn = socket_accept($socket);
            if(!$conn){
                echo "accept server fail:".socket_strerror(socket_last_error())."\n";
                break;
            }
            $clients[] = $conn;

            echo "client connect succ. fd: ".$conn."\n";

            //获取客户端IP地址
            socket_getpeername($conn, $addr, $port);
            echo "client addr: $addr:$port\n";

            //获取服务端IP地址
            socket_getsockname($conn, $addr, $port);
            echo "server addr: $addr:$port\n";

            // print_r($clients);
            echo "total: ".(count($clients)-1)." client\n";
        }else{
            //注意：后续使用$client而不是$conn
            if (!isset($recvs[$k]) ) $recvs[$k] = ''; //兼容可能没有值的情况

            $buffer = socket_read($client, 100); //每次读取100byte
            if($buffer === false || $buffer === ''){
                echo "client closed\n";
                unset($clients[array_search($client, $clients)]); //unset
                socket_close($client); //关闭本次连接
                break;
            }

            //解析单次消息，协议：换行符
            $pos = strpos($buffer, "\n");
            if($pos === false){ //消息未读取完毕，继续读取
                $recvs[$k] .= $buffer;
            }else{ //消息读取完毕
                $recvs[$k] .= trim(substr($buffer, 0, $pos+1)); //去除换行符及空格

                //客户端主动端口连接
                if($recvs[$k] == 'quit'){
                    echo "client closed\n";
                    unset($clients[array_search($client, $clients)]); //unset
                    socket_close($client); //关闭本次连接
                    break;
                }

                echo "recv:".$recvs[$k]."\n";
                socket_write($client, $recvs[$k]."\n"); //发送消息

                $recvs[$k] = '';
            }
        }
    }   
}
socket_close($socket);
```
 
我们先使用`Crtl+C`关闭上一次运行的TCP server，然后运行新写的server：
 
```
php socket_select.php
waiting client...
```
 
新开终端telnet客户端：
 
```
telnet 127.0.0.1 9201
Trying 127.0.0.1...
Connected to localhost.
Escape character is '^]'.
hello world
hello world
```
 
再打开终端新开一个telnet客户端，我们来看服务端的输出：
 
```
client connect succ. fd: Resource id #5
client addr: 127.0.0.1:60065
server addr: 127.0.0.1:9201
total: 1 client
recv:hello server!

client connect succ. fd: Resource id #6
client addr: 127.0.0.1:60069
server addr: 127.0.0.1:9201
total: 2 client
recv:hello world
```
 
此时我们的服务端就不受客户端连接数限制了。
 
注意点：
 
1、使用了socket_select后，解析消息的地方不能再是死循环，否则造成阻塞。
 
select 函数监视的文件描述符分为3类，分别是 writefds, readfds, exceptfds，调用之后select函数就会阻塞，直到有文件描述符就绪（有数据可读，可写或者except），或者超时（timeout指定等待时间，如果立即返回设为null即可），函数返回；当select函数返回之后，可以通过遍历 fdset来找到就绪的描述符。
 
2、socket系统调用最大支持1024个客户端连接，如果需要更大的客户端连连，则需要使用poll、epoll等技术。本文不做讲解。
 
### socket_set_option
 
该函数用来设置socket选项，比如设置端口复用。函数原型：
 
```
bool socket_set_option ( resource $socket , int $level , int $optname , mixed $optval )
```
 
示例：
 
```php
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1); //复用端口
```
 
该小节不是本文重点，该函数大家了解即可，需要设置的时候能知道怎么调用。顺便提一下，端口复用技术是用来解决"惊群"问题的，大家感兴趣可以看看博文：Linux网络编程“惊群”问题总结 -
 
https://www.cnblogs.com/Anker/p/7071849.html 。
 
### 函数参考
 
这些 [PHP官方手册][1] 里都有，贴出来供大家快速查阅。
 
```
socket_accept() 接受一个Socket连接
socket_bind() 把socket绑定在一个IP地址和端口上
socket_clear_error() 清除socket的错误或者最后的错误代码
socket_close() 关闭一个socket资源
socket_connect() 开始一个socket连接
socket_create_listen() 在指定端口打开一个socket监听
socket_create_pair() 产生一对没有区别的socket到一个数组里
socket_create() 产生一个socket，相当于产生一个socket的数据结构
socket_get_option() 获取socket选项
socket_getpeername() 获取远程类似主机的ip地址
socket_getsockname() 获取本地socket的ip地址
socket_iovec_add() 添加一个新的向量到一个分散/聚合的数组
socket_iovec_alloc() 这个函数创建一个能够发送接收读写的iovec数据结构
socket_iovec_delete() 删除一个已经分配的iovec
socket_iovec_fetch() 返回指定的iovec资源的数据
socket_iovec_free() 释放一个iovec资源
socket_iovec_set() 设置iovec的数据新值
socket_last_error() 获取当前socket的最后错误代码
socket_listen() 监听由指定socket的所有连接
socket_read() 读取指定长度的数据
socket_readv() 读取从分散/聚合数组过来的数据
socket_recv() 从socket里结束数据到缓存
socket_recvfrom() 接受数据从指定的socket，如果没有指定则默认当前socket
socket_recvmsg() 从iovec里接受消息
socket_select() 多路选择
socket_send() 这个函数发送数据到已连接的socket
socket_sendmsg() 发送消息到socket
socket_sendto() 发送消息到指定地址的socket
socket_set_block() 在socket里设置为块模式
socket_set_nonblock() socket里设置为非块模式
socket_set_option() 设置socket选项
socket_shutdown() 这个函数允许关闭读、写、或者指定的socket
socket_strerror() 返回指定错误号的详细错误
socket_write() 写数据到socket缓存
socket_writev() 写数据到分散/聚合数组
```
 
其中socket里的`write``read`、`writev``readv`、`recv```send`、`recvfrom``sendto`、`recvmsg``sendmsg`五组 I/O 函数可以参考：https://blog.csdn.net/yangbingzhou/article/details/45221649
 
## stream_socket系列函数
 
stream_socket系列函数相当于是socket函数的进一步封装。使用该系类函数能简化我们的编码。
 `stream_socket_server`和`stream_socket_accept`返回的句柄可以由`fgets()`,`fgetss()`,`fwrite()`,`fclose()`以及`feof()`函数调用。
 
### server端
 
我们先看一下函数原型。
 
stream_socket_server：
 
```
resource stream_socket_server ( string $local_socket [, int &$errno [, string &$errstr [, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN [, resource $context ]]]] )
```
 
如果是udp服务，flags指定为`STREAM_SERVER_BIND`。 另外，`$context`由`stream_context_create`创建，例如：
 
```php
$context_option['socket']['so_reuseport'] = 1;//端口复用
$context = stream_context_create($context_option);
```
 
stream_socket_accept：
 
```
resource stream_socket_accept ( resource $server_socket [, float $timeout = ini_get("default_socket_timeout") [, string &$peername ]] )
```
 
接下来我们使用`stream_socket_`系列函数写一个tcp server。
 
#### tcp server
 
示例：
 
stream_socket_server.php
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */

$socket = stream_socket_server ("tcp://0.0.0.0:9201", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
if (false === $socket ) {
    echo "$errstr($errno)\n";
    exit();
}

while(1){
    echo "waiting client...\n";

    $conn = stream_socket_accept($socket, -1);
    if (false === $socket ) {
        exit("accept error\n");
    }

    echo "new Client! fd:".intval($conn)."\n";

    while(1){
        $buffer = fread($conn, 1024);

        //非正常关闭
        if(false === $buffer){
            echo "fread fail\n";
            break;
        }

        $msg = trim($buffer, "\n\r");

        //强制关闭
        if($msg == "quit"){
            echo "client close\n";
            fclose($conn);
            break;
        }

        echo "recv: $msg\n";
        fwrite($conn, "recv: $msg\n");
    }
}

fclose($socket);
```
 
代码相比使用纯`socket`函数少了很多。
 
运行：
 
```
$ php stream_socket_server.php 
waiting client...
new Client! fd:6
recv: hello
```
 
客户端使用telnet：
 
```
$ telnet 127.0.0.1 9201
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
hello
recv: hello
```
 
#### udp server
 
udp服务端不需要listen操作。
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */

$socket = stream_socket_server ("udp://0.0.0.0:9201", $errno, $errstr, STREAM_SERVER_BIND);
if (false === $socket ) {
    echo "$errstr($errno)\n";
    exit();
}

while(1){
    // $buffer = fread($socket, 1024);
    $buffer = stream_socket_recvfrom($socket, 1024, 0, $addr);
    echo $addr;

    //非正常关闭
    if(false === $buffer){
        echo "fread fail\n";
        break;
    }

    $msg = trim($buffer, "\n\r");

    //强制关闭
    if($msg == "quit"){
        echo "client close\n";
        fclose($socket);
        break;
    }

    echo "recv: $msg\n";
    // fwrite($socket, "recv: $msg\n");
    stream_socket_sendto($socket, "recv: $msg\n", 0, $addr);
}
```
 
运行：
 
```
$ php stream_socket_server_udp.php 
127.0.0.1:43172recv: hello
```
 
客户端使用 netcat：
 
```
netcat -u 127.0.0.1 9201
hello
recv: hello
quit
```
 
如果没有netcat需要安装：
 
```
sudo apt-get install netcat
```
 
### 客户端
 
上面我们都是用的`telnet`和`netcat`来连接服务端，接下来我们使用`stream_socket_`系列函数编写tcp/udp客户端。
 
#### 简单示例
 
stream_socket系列函数写client非常简单：
 
```php
<?php

$client = stream_socket_client("tcp://127.0.0.1:9201", $errno, $erstr);
if(!$client) die("err");

fwrite($client, "a");
while(1){
    $rec = fread($client, 1024);
    echo $rec."\n";
}
```
 
udp客户端仅需要修改tcp为udp。
 
### stream_select
 `stream`系列函数使用`stream_select`实现I/O复用，本质都是select系统调用。
 
接下来我们写两个示例，第一个示例和上面使用`socket_select`实现的类似，第二个则是监听了客户端读写事件，从而实现了类似telnet的功能，相信大家会感兴趣的。
 
#### 同时监听socket和连接socket
 
使用stream_select可以实现IO复用，使得单进程程序也能支持同时处理多个客户端连接。示例：
 
```php
<?php 
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */

$socket = stream_socket_server ("tcp://0.0.0.0:9201", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
if (false === $socket ) {
    echo "$errstr($errno)\n";
    exit();
}

$clients = [$socket];
echo "waiting client...\n";

while(1){
    $read = $clients;
    $ret = stream_select($read, $w, $e, 0);
    if(false === $ret){
        break;
    }

    foreach($read as $client){
        if($client == $socket){ //新客户端
            $conn = stream_socket_accept($socket, -1);
            if (false === $socket ) {
                exit("accept error\n");
            }

            echo "new Client! fd:".intval($conn)."\n";

            $clients[] = $conn;
        }else{
            $buffer = fread($client, 1024);//注意，使用$client而不是$conn

            //非正常关闭
            if(false === $buffer){
                echo "fread fail\n";
                $key = array_search($client, $clients);
                unset($clients[$key]);
                break;
            }

            $msg = trim($buffer, "\n\r");

            //强制关闭
            if($msg == "quit"){
                echo "client close\n";
                $key = array_search($client, $clients);
                unset($clients[$key]);
                fclose($client);
                break;
            }

            echo "recv: $msg\n";
            fwrite($conn, "recv: $msg\n");
        }
    }
}

fclose($socket);
```
 
运行服务端并随后运行telnet客户端：
 
```
$ php stream_select.php 
waiting client...
new Client! fd:6
recv: ww
new Client! fd:7
recv: kkk
```
 
可以同时支持多个客户端。从例子可以看出来，`stream_select`和`socket_select`用法相同。
 
#### 同时处理网络连接和用户输入
 
下面的例子使用stream_select实现了客户端程序运行后，支持命令行界面手动实时输入与服务端进程交互：
 
```php
<?php
/**
 * Created by PhpStorm.
 * User: 公众号: 飞鸿影的博客(fhyblog)
 * Date: 2018/6/23
 */

$socket = stream_socket_client("tcp://127.0.0.1:9201", $errno, $erstr);
if(!$socket) die("err");

$clients = [$socket, STDIN];

fwrite(STDOUT, "ENTER MSG:");

while(1){
    $read = $clients;
    $ret = stream_select($read, $w, $e, 0);
    if(false === $ret){
        exit("stream_select err\n");
    }

    foreach($read as $client){
        if($client == $socket){
            $msg = stream_socket_recvfrom($socket, 1024);
            echo "\nRecv: {$msg}\n";
            fwrite(STDOUT, "ENTER MSG:");
        }elseif($client == STDIN){
            $msg = trim(fgets(STDIN));
            if($msg == 'quit'){ //必须trim此处才会相等
                exit("quit\n");
            }

            fwrite($socket, $msg);
            fwrite(STDOUT, "ENTER MSG:");
        }
    }
}
```
 
例子里，我们把`$socket`和`STDIN`使用stream_select监听文件描述符的变化情况，当有文件描述符就绪，函数会返回，从而执行我们逻辑代码。
 
先运行tcp服务端程序stream_select.php，然后运行该客户端程序：
 
```
$ php tcp_client_select.php 
ENTER MSG:hello!
ENTER MSG:
Recv: recv: hello!

ENTER MSG:
```
 
程序一直会等待我们的输入，除非输入quit退出。
 
### 函数参考
 
```
stream_socket_server() - 创建server
stream_socket_accept() - 接受由 stream_socket_server创建的socket连接
stream_socket_get_name() - 获取本地或者远程的套接字名称
stream_set_blocking() - 为资源流设置阻塞或者阻塞模式
stream_set_timeout() - 为资源流设置超时
stream_socket_client() - 创建client

stream_select() - select系统调用，实现IO多路选择
stream_socket_shutdown() - 这个函数允许关闭读、写、或者指定的socket
stream_socket_recvfrom() - 
stream_socket_sendto() -
```
 
## 总结
 
本文主要和大家讲解了 PHP Socket 编程相关知识。通过学习本文，大家学到了如下内容：

* 熟悉 socket 系列函数使用 
* 熟悉 stream_socket 系列函数使用 
* 熟悉 I/O 复用 
* 如何使用 socket 系列函数实现 TCP 服务端和客户端 
* 如何使用 socket_select 实现 I/O 多路复用 
* 如何使用 stream_socket 系列函数实现TCP服务端和客户端 
* 如何使用 stream_select 实现 I/O 多路复用 

也给大家留一个问题：
 
如何基于PHP多进程Master-Worker模型实现支持I/O复用的TCP server？
 
提示：我公众号(fhyblog)里有PHP多进程系列笔记相关文章，多进程不熟悉的同学可以学习一下。
 
(全文完)
 
## 参考
 
1、深入浅出讲解：php的socket通信 - 洒洒 - 博客园
 
http://www.cnblogs.com/thinksasa/archive/2013/02/26/2934206.html
 
2、write read;writev readv;recv send;recvfrom sendto;recvmsg sendmsg五组I/O函数汇总 - CSDN博客
 
https://blog.csdn.net/yangbingzhou/article/details/45221649
 
3、socket编程中的read、write与recv、send的区别 - CSDN博客
 
https://blog.csdn.net/xhu_eternalcc/article/details/18256561
 
4、php select socket - yuanlp_code - 博客园
 
https://www.cnblogs.com/yuanlipu/p/6431834.html
 
5、socket服务的模型以及实现(3)–单进程IO复用select | 你好，欢迎来到老张的博客,张素杰
 
http://www.xtgxiso.com/socket%e6%9c%8d%e5%8a%a1%e7%9a%84%e6%a8%a1%e5%9e%8b%e4%bb%a5%e5%8f%8a%e5%ae%9e%e7%8e%b03-%e5%8d%95%e8%bf%9b%e7%a8%8bio%e5%a4%8d%e7%94%a8select/
 
6、PHP Socket实现websocket（四）Select函数 - 海上小绵羊 - 博客园
 
https://www.cnblogs.com/yangxunwu1992/p/5564454.html .

[1]: http://php.net/manual/zh/ref.sockets.php
[0]: ./img/6NjqyqB.jpg 
## PHP socket初探 --- 先从一个简单的socket服务器开始

来源：[https://segmentfault.com/a/1190000016226578](https://segmentfault.com/a/1190000016226578)

[原文地址：[https://blog.ti-node.com/blog...][4]]

socket的中文名字叫做套接字，这种东西就是对TCP/IP的“封装”。现实中的网络实际上只有四层而已，从上至下分别是应用层、传输层、网络层、数据链路层。最常用的http协议则是属于应用层的协议，而socket，可以简单粗暴的理解为是传输层的一种东西。如果还是很难理解，那再粗暴地点儿tcp://218.221.11.23:9999，看到没？这就是一个tcp socket。

socket赋予了我们操控传输层和网络层的能力，从而得到更强的性能和更高的效率，socket编程是解决高并发网络服务器的最常用解决和成熟的解决方案。任何一名服务器程序员都应当掌握socket编程相关技能。

在php中，可以操控socket的函数一共有两套，一套是socket_ 系列的函数，另一套是stream_ 系列的函数。socket_ 是php直接将C语言中的socket抄了过来得到的实现，而stream_ 系则是php使用流的概念将其进行了一层封装。下面用socket_*系函数简单为这一系列文章开个篇。

先来做个最简单socket服务器：

```php
<?php
$host = '0.0.0.0';
$port = 9999;
// 创建一个tcp socket
$listen_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// 将socket bind到IP：port上
socket_bind($listen_socket, $host, $port);
// 开始监听socket
socket_listen($listen_socket);
// 进入while循环，不用担心死循环死机，因为程序将会阻塞在下面的socket_accept()函数上
while (true) {
    // 此处将会阻塞住，一直到有客户端来连接服务器。阻塞状态的进程是不会占据CPU的
    // 所以你不用担心while循环会将机器拖垮，不会的
    $connection_socket = socket_accept($listen_socket);
    // 向客户端发送一个helloworld
    $msg = "helloworld\r\n";
    socket_write($connection_socket, $msg, strlen($msg));
    socket_close($connection_socket);
}
socket_close($listen_socket);

```

将文件保存为server.php，然后执行`php server.php`运行起来。客户端我们使用`telnet`就可以了，打开另外一个终端执行telnet 127.0.0.1 9999按下回车即可。运行结果如下：

![][0]

简单解析一下上述代码来说明一下tcp socket服务器的流程：

* 1.首先，根据协议族（或地址族）、套接字类型以及具体的的某个协议来创建一个socket。
* 2.第二，将上一步创建好的socket绑定（bind）到一个ip:port上。
* 3.第三，开启监听linten。
* 4.第四，使服务器代码进入无限循环不退出，当没有客户端连接时，程序阻塞在accept上，有连接进来时才会往下执行，然后再次循环下去，为客户端提供持久服务。


上面这个案例中，有两个很大的缺陷：

* 1.一次只可以为一个客户端提供服务，如果正在为第一个客户端发送helloworld期间有第二个客户端来连接，那么第二个客户端就必须要等待片刻才行。
* 2.很容易受到攻击，造成拒绝服务。


分析了上述问题后，又联想到了前面说的多进程，那我们可以在accpet到一个请求后就fork一个子进程来处理这个客户端的请求，这样当accept了第二个客户端后再fork一个子进程来处理第二个客户端的请求，这样问题不就解决了吗？OK！撸一把代码演示一下：

```php
<?php
$host = '0.0.0.0';
$port = 9999;
// 创建一个tcp socket
$listen_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// 将socket bind到IP：port上
socket_bind($listen_socket, $host, $port);
// 开始监听socket
socket_listen($listen_socket);
// 进入while循环，不用担心死循环死机，因为程序将会阻塞在下面的socket_accept()函数上
while (true) {
    // 此处将会阻塞住，一直到有客户端来连接服务器。阻塞状态的进程是不会占据CPU的
    // 所以你不用担心while循环会将机器拖垮，不会的
    $connection_socket = socket_accept($listen_socket);
    // 当accept了新的客户端连接后，就fork出一个子进程专门处理
    $pid = pcntl_fork();
    // 在子进程中处理当前连接的请求业务
    if (0 == $pid) {
        // 向客户端发送一个helloworld
        $msg = "helloworld\r\n";
        socket_write($connection_socket, $msg, strlen($msg));
        // 休眠5秒钟，可以用来观察时候可以同时为多个客户端提供服务
        echo time() . ' : a new client' . PHP_EOL;
        sleep(5);
        socket_close($connection_socket);
        exit;
    }
}
socket_close($listen_socket);

```

将代码保存为server.php，然后执行php server.php，客户端依然使用telnet 127.0.0.1 9999，只不过这次我们开启两个终端来执行telnet。重点观察当第一个客户端连接上去后，第二个客户端时候也可以连接上去。运行结果如下：

![][1]

通过接受到客户端请求的时间戳可以看到现在服务器可以同时为N个客户端服务的。但是，接着想，如果先后有1万个客户端来请求呢？这个时候服务器会fork出1万个子进程来处理每个客户端连接，这是会死人的。fork本身就是一个很浪费系统资源的系统调用，1W次fork足以让系统崩溃，即便当下系统承受住了1W次fork，那么fork出来的这1W个子进程也够系统内存喝一壶了，最后是好不容易费劲fork出来的子进程在处理完毕当前客户端后又被关闭了，下次请求还要重新fork，这本身就是一种浪费，不符合社会主义主流价值观。如果是有人恶意攻击，那么系统fork的数量还会呈直线上涨一直到系统崩溃。

所以，我们就再次提出增进型解决方案。我们可以预估一下业务量，然后在服务启动的时候就fork出固定数量的子进程，每个子进程处于无限循环中并阻塞在accept上，当有客户端连接挤进来就处理客户请求，当处理完成后仅仅关闭连接但本身并不销毁，而是继续等待下一个客户端的请求。这样，不仅避免了进程反复fork销毁巨大资源浪费，而且通过固定数量的子进程来保护系统不会因无限fork而崩溃。

```php
<?php
$host = '0.0.0.0';
$port = 9999;
// 创建一个tcp socket
$listen_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// 将socket bind到IP：port上
socket_bind($listen_socket, $host, $port);
// 开始监听socket
socket_listen($listen_socket);
// 给主进程换个名字
cli_set_process_title('phpserver master process');
// 按照数量fork出固定个数子进程
for ($i = 1; $i <= 10; $i++) {
    $pid = pcntl_fork();
    if (0 == $pid) {
        cli_set_process_title('phpserver worker process');
        while (true) {
            $conn_socket = socket_accept($listen_socket);
            $msg         = "helloworld\r\n";
            socket_write($conn_socket, $msg, strlen($msg));
            socket_close($conn_socket);
        }
    }
}
// 主进程不可以退出，代码演示比较粗暴，为了不保证退出直接走while循环，休眠一秒钟
// 实际上，主进程真正该做的应该是收集子进程pid，监控各个子进程的状态等等
while (true) {
    sleep(1);
}
socket_close($connection_socket);

```

将文件保存为server.php后php server.php执行，然后再用`ps -ef | grep phpserver | grep -v grep`来看下服务器进程状态：

![][2]

可以看到master进程存在，除此之外还有10个子进程处于等待服务状态，再同一个时刻可以同时为10个客户端提供服务。我们通过telnet 127.0.0.1 9999来尝试一下，运行结果如下图：

![][3]

好啦，php新的征程系列就先通过一个简单的入门开始啦！下篇将会讲述一些比较深刻的理论基础知识。

[原文地址：[https://blog.ti-node.com/blog...][4]]

[4]: https://blog.ti-node.com/blog/6382424397004668928
[5]: https://blog.ti-node.com/blog/6382424397004668928
[0]: ./img/1460000016226581.png
[1]: ./img/1460000016226582.png
[2]: ./img/1460000016226583.png
[3]: ./img/1460000016226584.png
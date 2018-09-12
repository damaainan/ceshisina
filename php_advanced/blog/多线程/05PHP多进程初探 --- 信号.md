## PHP多进程初探 --- 信号

来源：[https://segmentfault.com/a/1190000016187090](https://segmentfault.com/a/1190000016187090)

[原文地址：[https://blog.ti-node.com/blog...][3]]

上一篇尬聊了通篇的pcntl_wait()和pcntl_waitpid()，就是为了解决僵尸进程的问题，但最后看起来还是有一些遗留问题，而且因为嘴欠在上篇文章的结尾出也给了解决方案：信号。

信号是一种软件中断，也是一种非常典型的异步事件处理方式。在 NIX系统诞生的混沌之初，信号的定义是比较混乱的，而且最关键是不可靠，这是一个很严重的问题。所以在后来的POSIX标准中，对信号做了标准化同时也各个发行版的 NIX也都提供大量可靠的信号。每种信号都有自己的名字，大概如SIGTERM、SIGHUP、SIGCHLD等等，在*NIX中，这些信号本质上都是整形数字（游有心情的可以参观一下signal.h系列头文件）。

信号的产生是有多种方式的，下面是常见的几种：

* 键盘上按某些组合键，比如Ctrl+C或者Ctrl+D等，会产生SIGINT信号。
* 使用posix kill调用，可以向某个进程发送指定的信号。
* 远程ssh终端情况下，如果你在服务器上执行了一个阻塞的脚本，正在阻塞过程中你关闭了终端，可能就会产生SIGHUP信号。
* 硬件也会产生信号，比如OOM了或者遇到除0这种情况，硬件也会向进程发送特定信号。


而进程在收到信号后，可以有如下三种响应：

* 直接忽略，不做任何反映。就是俗称的完全不鸟。但是有两种信号，永远不会被忽略，一个是SIGSTOP，另一个是SIGKILL，因为这两个进程提供了向内核最后的可靠的结束进程的办法。
* 捕捉信号并作出相应的一些反应，具体响应什么可以由用户自己通过程序自定义。
* 系统默认响应。大多数进程在遇到信号后，如果用户也没有自定义响应，那么就会采取系统默认响应，大多数的系统默认响应就是终止进程。


用人话来表达，就是说假如你是一个进程，你正在干活，突然施工队的喇叭里冲你嚷了一句：“吃饭了！”，于是你就放下手里的活儿去吃饭。你正在干活，突然施工队的喇叭里冲你嚷了一句：“发工资了！”，于是你就放下手里的活儿去领工资。你正在干活，突然施工队的喇叭里冲你嚷了一句：“有人找你！”，于是你就放下手里的活儿去看看是谁找你什么事情。当然了，你很任性，那是完全可以不鸟喇叭里喊什么内容，也就是忽略信号。也可以更任性，当喇叭里冲你嚷“吃饭”的时候，你去就不去吃饭，你去睡觉，这些都可以由你来。而你在干活过程中，从来不会因为要等某个信号就不干活了一直等信号，而是信号随时随地都可能会来，而你只需要在这个时候作出相应的回应即可，所以说，信号是一种软件中断，也是一种异步的处理事件的方式。

回到上文所说的问题，就是子进程在结束前，父进程就已经先调用了pcntl_waitpid()，导致子进程在结束后依然变成了僵尸进程。实际上在父进程不断while循环调用pcntl_waitpid()是个解决办法，大概代码如下：

```php
<?php
$pid = pcntl_fork();
if (0 > $pid) {
    exit('fork error.' . PHP_EOL);
} else if (0 < $pid) {
    // 在父进程中
    cli_set_process_title('php father process');
    // 父进程不断while循环，去反复执行pcntl_waitpid()，从而试图解决已经退出的子进程
    while (true) {
        sleep(1);
        pcntl_waitpid($pid, &$status, WNOHANG);
    }
} else if (0 == $pid) {
    // 在子进程中
    // 子进程休眠3秒钟后直接退出
    cli_set_process_title('php child process');
    sleep(20);
    exit;
}

```

下图是运行结果：

![][0]
##### 解析一下这个结果，我先后三次执行了`ps -aux | grep php`去查看这两个php进程。

* 第一次：子进程正在休眠中，父进程依旧在循环中。
* 第二次：子进程已经退出了，父进程依旧在循环中，但是代码还没有执行到pcntl_waitpid()，所以在子进程退出后到父进程执行回收前这段空隙内子进程变成了僵尸进程。
* 第三次：此时父进程已经执行了pcntl_waitpid()，将已经退出的子进程回收，释放了pid等资源。


但是这样的代码有一个缺陷，实际上就是子进程已经退出的情况下，主进程还在不断while pcntl_waitpid()去回收子进程，这是一件很奇怪的事情，并不符合社会主义主流价值观，不低碳不节能，代码也不优雅，不好看。所以，应该考虑用更好的方式来实现。那么，我们篇头提了许久的信号终于概要出场了。

现在让我们考虑一下，为何信号可以解决“不低碳不节能，代码也不优雅，不好看”的问题。子进程在退出的时候，会向父进程发送一个信号，叫做SIGCHLD，那么父进程一旦收到了这个信号，就可以作出相应的回收动作，也就是执行pcntl_waitpid()，从而解决掉僵尸进程，而且还显得我们代码优雅好看节能环保。

梳理一下流程，子进程向父进程发送SIGCHLD信号是对人们来说是透明的，也就是说我们无须关心。但是，我们需要给父进程安装一个响应SIGCHLD信号的处理器，除此之外，还需要让这些信号处理器运行起来，安装上了不运行是一件尴尬的事情。那么，在php里给进程安装信号处理器使用的函数是pcntl_signal()，让信号处理器跑起来的函数是pcntl_signal_dispatch()。

* pcntl_signal()，安装一个信号处理器，具体说明是pcntl_signal ( int $signo , callback $handler [, bool $restart_syscalls = true ] )，参数signo就是信号，callback则是响应该信号的代码段，返回bool值。
* pcntl_signal_dispatch()，调用每个等待信号通过pcntl_signal() 安装的处理器，参数为void，返回bool值。


下面结合新引入的两个函数来解决一下楼上的丑陋代码：

```php
<?php
$pid = pcntl_fork();
if (0 > $pid) {
    exit('fork error.' . PHP_EOL);
} else if (0 < $pid) {
    // 在父进程中
    // 给父进程安装一个SIGCHLD信号处理器
    pcntl_signal(SIGCHLD, function () use ($pid) {
        echo "收到子进程退出" . PHP_EOL;
        pcntl_waitpid($pid, $status, WNOHANG);
    });
    cli_set_process_title('php father process');
    // 父进程不断while循环，去反复执行pcntl_waitpid()，从而试图解决已经退出的子进程
    while (true) {
        sleep(1);
        // 注释掉原来老掉牙的代码，转而使用pcntl_signal_dispatch()
        //pcntl_waitpid( $pid, &$status, WNOHANG );
        pcntl_signal_dispatch();
    }
} else if (0 == $pid) {
    // 在子进程中
    // 子进程休眠3秒钟后直接退出
    cli_set_process_title('php child process');
    sleep(20);
    exit;
}

```

运行结果如下：

![][1] 

![][2]

-----

[原文地址：[https://blog.ti-node.com/blog...][3]]

[3]: https://blog.ti-node.com/blog/6375675957193211905
[4]: https://blog.ti-node.com/blog/6375675957193211905
[0]: ./img/1460000016187093.png
[1]: ./img/1460000016187094.png
[2]: ./img/1460000016187095.png
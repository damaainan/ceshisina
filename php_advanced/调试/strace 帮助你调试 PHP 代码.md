## strace 帮助你调试 PHP 代码

来源：[https://mp.weixin.qq.com/s/Sf79W5dqUFx7rUYRrtx88Q](https://mp.weixin.qq.com/s/Sf79W5dqUFx7rUYRrtx88Q)

时间 2018-06-26 08:32:05


上个礼拜，一个Crontab后台脚本（PHP代码）运行遇到一些问题，最后借助 strace linux 命令行工具快速解决了，虽然问题产生和解决很简单，但 strace 工具却值得每个程序员去学习。

这个后台脚本在开发环境没有遇到问题，但在线上环境却出现了问题，开发环境和线上环境在代码层没有太多的差别（也是事后才发现有差异），仅仅是开发环境使用的是测试数据库等资源。

线上环境脚本运行的结果就是某个文件没有成功记录数据，按照常规的解决思路，就是在PHP代码中增加调试函数（比如 var_dump 函数）一步步调试，针对这个脚本来说，使用这种方法可能会有以下一些问题：



* 开发环境没有问题，为重现问题，必须在线上环境进行调试，如果增加调试代码，可能会引发一些问题，比如在线上数据库额外插入了一些数据。

    
* 该脚本运行比较缓慢，如果不停的修改代码然后调试，最后花费的时间可能就比较长。

    
  

那有没有快速的调试手段呢？这个脚本预期结果就是在文件中写入日志信息，也就是脚本最终会调用系统函数写入文件，那么是遇到文件权限问题？脚本中断没有运行到写入文件步骤？有没有类似于 tcmdump 这样的工具，能够重现所有的系统处理呢？非常棒的是，strace 就是干这活的，它能够记录所有的系统调用和信号处理。

由于脚本没有成功 fwrite 写入数据，我将该脚本的关键代码抽取出来，代码如下：

```php
<?php
$file  = "/var/log/data.log";
$fp = fopen($file, "a");
if ($fp) {
    echo "start\n";
    foreach ($log as $v) {
       fwrite($fp, $v."\r\n");
    }
}
fclose($fp);
```

然后运行下列的命令，记录所有的系统调用：

```
$ strace -o debug.log php test.php
```

查看关键输出：

```
lstat("/var/log/data.log", {st_mode=S_IFREG|0644, st_size=0, ...}) = 0
lstat("/var/log", {st_mode=S_IFDIR|0775, st_size=4096, ...}) = 0
lstat("/var", {st_mode=S_IFDIR|0755, st_size=4096, ...}) = 0
open("/var/log/data.log", O_WRONLY|O_CREAT|O_APPEND, 0666) = 3
fstat(3, {st_mode=S_IFREG|0644, st_size=0, ...}) = 0
lseek(3, 0, SEEK_CUR)  = 0
lseek(3, 0, SEEK_CUR)  = 0
write(1, "start\n", 6) = 6
close(3) = 0
close(2) = 0
close(1) = 0
munmap(0x7f7aa7dfd000, 4096) = 0
close(0) = 0
```

通过输出可以看出，脚本根本没有运行`fwrite($fp, $v."\r\n");`这段代码，其他一切运行正常，代码也正常结束了。

问题已经很明显了，是`$log`变量在线上环境为空，顺藤摸瓜，仔细检查了`$log`变量的处理，原来是存在潜在的 Bug（具体就不描述了，属于低级错误）。修改代码后，再一次运行 strace，输出结果如下：

```
open("/var/log/data.log", O_WRONLY|O_CREAT|O_APPEND, 0666) = 3
fstat(3, {st_mode=S_IFREG|0644, st_size=0, ...}) = 0
lseek(3, 0, SEEK_CUR)  = 0
lseek(3, 0, SEEK_CUR)  = 0
write(1, "start\n", 6) = 6
write(3, "testdata\r\n", 10) = 10
close(3)  = 0
close(2)  = 0
close(1)  = 0
munmap(0x7f8a034cd000, 4096)  = 0
```

脚本最终成功写入数据到文件中（`write(3, "testdata\r\n", 10) = 10`），问题解决，其实这个问题的产生和原因，最终是代码不严谨造成的，该例也并不能说明 strace 工具的牛逼，因为其他的调试手段同样也能解决，但不可否认的是 strace 工具有非常广泛的使用场景。

接下来我们系统理解下 strace 工具，关于该工具的使用参数和输出可以参考`man strace`，在 man 帮助中，对 strace 的定义如下。

```
strace - trace system calls and signals
strace  is  a useful diagnostic, instructional, and debugging tool


```

以 PHP 代码来说，代码的大部分逻辑都会产生系统调用，比如写入本地文件(write)，连接外部数据库(connect)，即使你完全不知道代码的逻辑，通过 strace 工具也能够知晓 PHP 代码做了哪些事情，这是不是很奇妙？

接下去简单举几个例子，让我们掌握 strace 的使用。

1：例子一

```
$ strace -Tt php test.php
```

-T 参数表示每一个系统调用花费的时间，-t 是输出每个系统调用发生的时间。

test.php 脚本是想连接 google.com，关键输出如下：

```
17:00:21 connect(3, {sa_family=AF_INET, sin_port=htons(443), sin_addr=inet_addr("173.252.73.48")}, 16) = -1 EINPROGRESS (Operation now in progress) <0.000057>
17:00:21 poll([{fd=3, events=POLLIN|POLLOUT|POLLERR|POLLHUP}], 1, 60000) = 0 (Timeout) <60.055735>
```

通过输出可见，最终 poll 尝试了 60 秒，调用超时了，这对于调试网络理解非常有用。每一行的输出分别表示、系统调用开始时间、系统调用命令、系统调用参数、系统调用返回值、系统调用处理时间。

2：例子二

```
# 输出 nginx 工作进程 PID 号
$ ps -uax | grep "nginx: worker process" | grep -v "grep" | awk '{print $2}' 
29785 

$ strace -p  29785 -F
```

上面的例子，strace 观察 nginx 工作进程的运行，从而可以了解其内部处理连接的原理。

其中 -p 参数表示跟踪进程 PID 号，-F 表示过程该进程调用的子进程（比如 PHP 执行 exec 调用），这是非常重要的一个参数。

输出如下：

```
Process 29785 attached
epoll_wait(9, {{EPOLLIN, {u32=3975708920, u64=140539600601336}}}, 512, -1) = 1
accept4(7, {sa_family=AF_INET, sin_port=htons(50787), sin_addr=inet_addr("218.30.113.40")}, [16], SOCK_NONBLOCK) = 3
epoll_ctl(9, EPOLL_CTL_ADD, 3, {EPOLLIN|EPOLLRDHUP|EPOLLET, {u32=3975710081, u64=140539600602497}}) = 0
epoll_wait(9, {{EPOLLIN, {u32=3975710081, u64=140539600602497}}}, 512, 60000) = 1
recvfrom(3, "\26", 1, MSG_PEEK, NULL, NULL) = 1
setsockopt(3, SOL_TCP, TCP_NODELAY, [1], 4) = 0
read(3, "\26\3\1\2\24\1\0\2\20\3\3\361\365r\343(\362p\320RV9\1\316S\31jCQ\211\22\264"..., 16709) = 537
```

可见 nginx 接收到了一个来自于 218.30.113.40:50787 请求，整个过程对于理解网络编程非常有用。

3：例子三

```
$ strace -c -o out.log php test.php
```

-c 参数能够汇总系统调用的报告，比如某个系统调用的次数、失败数等等，-o 参数可以将 strace 输出保存到 out.log 文件中。

类似的输出结果如下：

```
% time     seconds  usecs/call     calls    errors syscall
------ ----------- ----------- --------- --------- ----------------
 21.57    0.000107           1       169           mmap
 18.75    0.000093           1        88           mprotect
 15.12    0.000075           1        81         3 open
 11.29    0.000056           1        80           rt_sigaction
  7.26    0.000036           2        20        19 access
  6.05    0.000030           1        47           read
```

如果一个脚本运行非常缓慢，那么这个参数大概能了解那个系统缓慢，从而找出问题。

最后介绍一个非常有用的调试参数，可以使用 -e 控制正则表达式，比如输出所有与`write`有关的调用。

```
$ strace -e write php test.php
```

该命令输出如下：

```
write(1, "start\n", 6start
)                  = 6
write(3, "testdata\r\n", 10)            = 10
+++ exited with 0 +++
```

使用这工具并不能，难的是如何具体分析问题，让工具帮助你，这篇文章也仅仅是提出一个思路，最重要的是进行实践，获取经验。



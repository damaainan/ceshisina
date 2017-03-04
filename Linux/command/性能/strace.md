## 简介

strace主要用于跟踪系统调用和信号。

ltrace用于跟踪用户级别的函数。

可以解决的问题:

1. 查看哪些系统调用负载较高
1. 查看系统调用耗时
1. 定位一些疑难杂症问题

## 系统调用 strace

如果你直接strace一个进程，你会发现被输出刷屏了。

    strace -p <PID>

### 统计

如果是性能问题，可以使用 -c 参数统计一下系统调用耗时。 

    strace -cp <PID>

如下图，统计一会按 ctrl-c 退出统计，可以看到各个系统调用的耗时。 

    tiankonguse:~ $ sudo strace -cp 6377
    Process 6377 attached
    
    % time     seconds  usecs/call     calls    errors syscall
    ------ ----------- ----------- --------- --------- ----------------
     59.85    0.000787           2       389       377 recvmsg
     19.54    0.000257           2       126           poll
     15.29    0.000201           3        72           write
      3.42    0.000045           1        38           read
      1.14    0.000015           3         6           writev
      0.76    0.000010          10         1           restart_syscall
    ------ ----------- ----------- --------- --------- ----------------
    100.00    0.001315                   632       377 total

### 跟踪系统调用

看到一个系统调用比较耗时时，我们可以使用 -e 参数只看这个系统调用在干什么的。 

    tiankonguse:~ $ sudo strace -p 6377 -e recvmsg
    Process 6377 attached
    recvmsg(16, 0x7fffb7b53160, 0)          = -1 EAGAIN (Resource temporarily unavailable)
    recvmsg(11, 0x7fffb7b53120, 0)          = -1 EAGAIN (Resource temporarily unavailable)
    ...

### 时间参数

上面展示系统调用了，但是没有显示时间。

写一个服务或者程序，系统调用异常了往往就是io操作，所以我们需要看看读与写的时间，来看看耗时是否合理。

* -t 显示时间
* -tt 显示时间和微秒
* -ttt 显示时间戳和微妙
* -T 显示系统调用耗时
* -r 输出每个系统调用的耗时
```
    # -t
    21:32:36 select(14, [3 4 5 6 10 11 13], [], NULL, NULL) = 1 (in [10])
    21:32:39 read(10, "\27\3\3\0\220\0\0\0\0\0\0\0(P\n\2q\214\n7\331\354F\237U\233\355\357\vP\334\247"..., 16384) = 195
    
    # -tt
    21:33:43.202679 select(11, [3 4 5 6 10], [], NULL, NULL) = 1 (in [10])
    21:33:44.755529 read(10, "\27\3\3\0\220\0\0\0\0\0\0\0003jPn@\272\263\256&7\1\350\3171K\375V~P."..., 16384) = 149d
    
    # -ttt
    1472391251.664652 select(11, [3 4 5 6 10], [], NULL, NULL) = 1 (in [10])
    1472391254.052413 read(10, "\27\3\3\0\220\0\0\0\0\0\0\0007\203\334\253I\325\232\216\31\212\207y\35x\263*\317\235\272\242"..., 16384) = 195
    
    # -T
    select(11, [3 4 5 6 10], [], NULL, NULL) = 1 (in [10]) <3.643800>
    read(10, "\27\3\3\0\220\0\0\0\0\0\0\0:\271\330\241H-\34\202\222\261\221|\267\233Z\311\315b\353/"..., 16384) = 195 <0.000098>
```
## 多进程与多线程

如果我们的程序是多进程和多线程时，我们希望strace所有进程， 此时可以使用 -f 参数。 

    tiankonguse:~ $ sudo strace -p 7542 -T -ttt -f
    Process 7542 attached with 9 threads
    [pid  9462] 1472391845.939746 restart_syscall(<... resuming interrupted call ...> <unfinished ...>
    [pid  7561] 1472391845.939766 futex(0x7f99b49feab4, FUTEX_WAIT_PRIVATE, 1, NULL <unfinished ...>
    [pid  7554] 1472391845.939785 futex(0x7f99b97beab4, FUTEX_WAIT_PRIVATE, 1, NULL <unfinished ...>
    [pid  7549] 1472391845.939808 futex(0x7f99ba03f864, FUTEX_WAIT_PRIVATE, 1, NULL <unfinished ...>
    [pid  7548] 1472391845.939822 futex(0x3ed20538a764, FUTEX_WAIT_PRIVATE, 4025, NULL) = -1 EAGAIN (Resource temporarily unavailable) <0.000007>
    [pid  7547] 1472391845.939855 futex(0x3ed20538a764, FUTEX_WAIT_PRIVATE, 4026, NULL <unfinished ...>
    [pid  7548] 1472391845.939869 futex(0x3ed20538a764, FUTEX_WAIT_PRIVATE, 4026, NULL <unfinished ...>
    [pid  7546] 1472391845.939894 futex(0x7f99bb89aab4, FUTEX_WAIT_PRIVATE, 1, NULL <unfinished ...>
    [pid  7545] 1472391845.939906 epoll_wait(12,  <unfinished ...>
    [pid  7542] 1472391845.939935 restart_syscall(<... resuming interrupted call ...>) = -1 ETIMEDOUT (Connection timed out) <0.550624>
    ...

### 输出

一般对于strace输出可以直接重定向到文件，但是这里也可以使用 -o file 参数把内容输出到文件，然后慢慢分析或者使用其他命令进一步分析。 

是不是发现直接使用重定向没有得到内容， 这个说明strace的输出不是标准输出。

所以我们需要把标准错误输出也重定向到标准输出就行了。

命令: 2>&1有时候我们想直接在strace中看系统调用时传输的数据，比如文本形式的日志数据或者http数据。

但是我们又会发现输出的内容比较少，这个使用 -s strsize 参数可以调整输出的buf大小。 

## 用户函数 ltrace

ltrace和strace就是个兄弟命令，用法几乎都一样。

这里只记录一下不同点。

### 系统调用

是的，这个命令也可以查看系统调用，只需要加上 -S 参数。 

## 经验

### 退出

默认会一直追踪进程，需要手动退出。

### 默认输出

默认输出的含义是每个系统调用的函数名，参数和返回值，并且输出是标准错误输出。

    open("/dev/null", O_RDONLY) = 3

### 系统调用失败

系统调用发生错误是通常是-1, 然后errno被设置为对应的错误码。

所以strace的时候会显示错误码和错误信息。

    open("/foo/bar", O_RDONLY) = -1 ENOENT (No such file or directory)

### 信号

对于信号，会输出信号符号和信号信息。

    sigsuspend([] <unfinished ...>
    --- SIGINT (Interrupt) ---
    +++ killed by SIGINT +++

### 未完成系统调用

有时候系统调用需要花费一下时间，所以返回值和耗时是没办法统计到的。所以输出分两个阶段。

第一阶段会标识为 unfinished , 第二阶段会标识为 resumed . 

    [pid 28772] select(4, [3], NULL, NULL, NULL <unfinished ...>
    [pid 28779] clock_gettime(CLOCK_REALTIME, {1130322148, 939977000}) = 0
    [pid 28772] <... select resumed> )      = 1 (in [3])

### 位相关参数

第二个和第三个参数一个是位或的形式， 一个是经典的八进制。

    open("xyzzy", O_WRONLY|O_APPEND|O_CREAT, 0666) = 3

### 复杂结构参数

对于结构体，即使是指针，也把对应的内容以文本的形式输出了。

    lstat("/dev/null", {st_mode=S_IFCHR|0666, st_rdev=makedev(1, 3), ...}) = 0
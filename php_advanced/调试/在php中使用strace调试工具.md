### 在php中使用strace调试工具

strace 是 Linux 环境下的一款程序调试工具，用来监察一个应用程序所使用的系统调用及它所接收的系统信息。

在 Linux 中，进程是不能直接去访问硬件设备(比如读取磁盘文件，接收网络数据等等)，但可以将用户态模式切换至内核态模式，通过系统调用来访问硬件设备。这时 strace 就可以跟踪到一个进程产生的系统调用，包括参数，返回值，执行消耗的时间，调用次数，成功和失败的次数。

比如我们使用 strace 来跟踪 cat 查看一个文件做了什么：

    [root@syyong home]$ strace cat index.php
    execve("/bin/cat", ["cat", "index.php"], [/* 25 vars */]) = 0
    brk(0)                                  = 0x21b0000
    mmap(NULL, 4096, PROT_READ|PROT_WRITE, MAP_PRIVATE|MAP_ANONYMOUS, -1, 0) = 0x7f5fd02fd000
    access("/etc/ld.so.preload", R_OK)      = -1 ENOENT (No such file or directory)
    open("/etc/ld.so.cache", O_RDONLY)      = 3
    fstat(3, {st_mode=S_IFREG|0644, st_size=41783, ...}) = 0
    mmap(NULL, 41783, PROT_READ, MAP_PRIVATE, 3, 0) = 0x7f5fd02f2000
    close(3)                                = 0
    open("/lib64/libc.so.6", O_RDONLY)      = 3
    ...                               
    fstat(1, {st_mode=S_IFCHR|0620, st_rdev=makedev(136, 3), ...}) = 0
    open("index.php", O_RDONLY)             = 3
    fstat(3, {st_mode=S_IFREG|0664, st_size=27, ...}) = 0
    read(3, "<?php\necho 'hello world';\n\n", 32768) = 27
    write(1, "<?php\necho 'hello world';\n\n", 27<?php
    echo 'hello world';
    
    ) = 27
    read(3, "", 32768)                      = 0
    close(3)                                = 0
    close(1)                                = 0
    close(2)                                = 0
    exit_group(0)                           = ?
    

    [root@syyong home]$ strace -e read cat index.php
    read(3, "\177ELF\2\1\1\3\0\0\0\0\0\0\0\0\3\0>\0\1\0\0\0p\356\1\0\0\0\0\0"..., 832) = 832
    read(3, "<?php\necho 'hello world';\n\n", 32768) = 27
    <?php
    echo 'hello world';
    
    read(3, "", 32768)                      = 0
    +++ exited with 0 +++
    

    [root@syyong home]$ strace -c cat index.php
    <?php
    echo "hello world";
    
    % time     seconds  usecs/call     calls    errors syscall
    ------ ----------- ----------- --------- --------- ----------------
      0.00    0.000000           0         3           read
      0.00    0.000000           0         1           write
      0.00    0.000000           0         4           open
      0.00    0.000000           0         6           close
      0.00    0.000000           0         5           fstat
      0.00    0.000000           0         9           mmap
      0.00    0.000000           0         3           mprotect
      0.00    0.000000           0         1           munmap
      0.00    0.000000           0         3           brk
      0.00    0.000000           0         1         1 access
      0.00    0.000000           0         1           execve
      0.00    0.000000           0         1           arch_prctl
    ------ ----------- ----------- --------- --------- ----------------
    100.00    0.000000                    38         1 total
    

    [root@syyong home]$ strace -T cat index.php 2>&1|grep read      
    read(3, "\177ELF\2\1\1\3\0\0\0\0\0\0\0\0\3\0>\0\1\0\0\0p\356\1\0\0\0\0\0"..., 832) = 832 <0.000015>
    read(3, "<?php\necho 'hello world';\n\n", 32768) = 27 <0.000019>
    read(3, "", 32768)                      = 0 <0.000014>
    

默认返回的结果每一行代表一条系统调用，规则为**“系统调用的函数名及其参数=函数返回值”**。也可以外加一些条件比如：-e 指定返回的调用函数，-c 对结果进行统计，-T 查看绝对耗时，-p 通过 pid 附着(attach)到任何运行的进程等等。

strace 的使用方法这里就不做具体介绍了，可以通过 strace --help 去详细了解使用方法。

那么通过 strace 拿到了所有程序去调用系统过程所产生的痕迹后，我们能用来定位哪些问题呢？

1. 调试性能问题，查看系统调用的频率，找出耗时的程序段
1. 查看程序读取的是哪些文件从而定位比如配置文件加载错误问题
1. 查看某个 php 脚本长时间运行“假死”情况
1. 当程序出现 “Out of memory” 时被系统发出的 SIGKILL 信息所 kill

另外因为 strace 拿到的是系统调用相关信息，一般也即是 IO 操作信息，这个对于排查比如 cpu 占用100%问题是无能为力的。这个时候就可以使用 GDB 工具了。

**phptrace**  
因为 strace 只能追踪到系统调用信息，而拿不到php代码层的调用信息。[phptrace扩展][3]就是为了解决这个问题，phptrace 包含两个功能：1. 打印当前 PHP 调用栈，2. 实时追踪 PHP 调用。这样就能更方便我们去查看到我们需要的信息。[phptrace wiki➫][4]


[3]: https://pecl.php.net/package/trace
[4]: https://github.com/Qihoo360/phptrace/wiki
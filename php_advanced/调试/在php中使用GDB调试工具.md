### 在php中使用  GDB 调试工具

gdb 是一个由 GNU 开源组织发布运行在 UNIX/LINUX 操作系统下功能强大的程序调试工具。使用 gdb 可以在程序运行时观察程序的内部结构和内存的使用情况，当程序 dump 时还可以通过 gdb 观察到程序 dump 前发生了什么。主要来说 gdb 具有以下2个功能：

1. 跟踪和变更执行计算机程序
1. 断点功能

因为 php 语言是 c 写的，那么使用 gdb 也就能很方便的去调试 php 代码。举例，我们通过 gdb 来调试一个简单的 php 程序 index.php：

    // 程序代码：
    <?php
    for ($i = 0; $i < 3; $i ++) {
        echo $i . PHP_EOL;
        if ($i == 2) {
            $j = $i + 1;
            var_dump($j);
        }
        sleep(1);
    }
    

gdb 开始调试：

    [root@syyong home]$ sudo gdb php
    

    (gdb)run index.php
    ...
    0
    1
    2
    int(3)
    [Inferior 1 (process 577) exited normally]
    

注：如果 mac 下使用 gdb 时报：“...please check gdb is codesigned - see taskgated(8)...”时可参考[https://leandre.cn/search/gdb/][5]➫。gdb 在调试程序时，如果 ulimit 打开则会把错误信息打印到当前目录下的 core.* 文件中。ulimit -c 如果为0则表示没打开，可以执行 ulimit -c unlimited 或者 ulimit -c 大于0的数字。

#### 常用命令：

* p：print，打印 C 变量的值
* c：continue，继续运行被中止的程序
* b：breakpoint，设置断点，可以按照函数名设置，如 b zif_php_function，也可以按照源代码的行数指定断点，如 b src/networker/Server.c:1000
* t：thread，切换线程，如果进程拥有多个线程，可以使用 t 指令，切换到不同的线程
* ctrl + c：中断当前正在运行的程序，和 c 指令配合使用
* n：next，执行下一行，单步调试
* info threads：查看运行的所有线程
* l：list，查看源码，可以使用 l 函数名 或者 l 行号
* bt：backtrace，查看运行时的函数调用栈。当程序出错后用于查看调用栈信息
* finish：完成当前函数
* f：frame，与 bt 配合使用，可以切换到函数调用栈的某一层
* r：run，运行程序

**使用 .gdbinit 脚本：**  
除了在 gdb shell 里输入命令，也可以预先编写好脚本让 gdb 执行。当 gdb 启动的时候会在当前目录下查找 “.gdbinit” 文件并加载，作为 gdb 命令进行执行。这样就可以不用在命令行中做一些重复的事，比如设定多个断点等操作。另外在 gdb 运行时也可以通过执行“(gdb) source [-s] [-v] filename” 来解释 gdb 命令脚本文件。一个 .gdbinit 文件例子：

    file index.php
    set args hello
    b main
    b foo
    r
    

[php 源码中提供的一个 .gdbinit 示例➫][6]

**其他 gdb 常用命令可以参考：**

* [http://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/gdb.html][7]➫
* [http://coolshell.cn/articles/3643.html][8]➫
* [http://www.cnblogs.com/xuqiang/archive/2011/05/02/2034583.html][9]
* [http://blog.csdn.net/21cnbao/article/details/7385161][10]➫
* [https://sourceware.org/gdb/current/onlinedocs/gdb/][11]➫
* [所有 gdb 命令索引➫][12]

#### gdb 调试 php：

**gdb 有3种使用方式：**

1. 跟踪正在运行的 PHP 程序，使用 “gdb -p 进程ID” 进行附加到进程上
1. 运行并调试 PHP 程序，使用 “gdb php -> run server.php” 进行调试
1. 当 PHP 程序发生 coredump 后使用 gdb 加载 core 内存镜像进行调试 gdb php core

> php 在解释执行过程中，zend 引擎用 executor_globals 变量保存了执行过程中的各种数据，包括执行函数、文件、代码行等。zend 虚拟机是使用 C 编写，gdb 来打印 PHP 的调用栈时，实际是打印的虚拟机的执行信息。

**使用 zbacktrace 更简单的调试：**  
php 源代码中还提供了 zbacktrace 这样的方便的对 gdb 命令的封装的工具。zbacktrace 是 PHP 源码包提供的一个 gdb 自定义指令，功能与 bt 指令类似，与bt不同的是 zbacktrace 看到的调用栈是 PHP 函数调用栈，而不是 c 函数。zbacktrace 可以直接看到当前执行函数、文件名和行数，简化了直接使用 gdb 命令的很多步骤。在 [php-src➫] ([https://github.com/php/php-src][13])的根目录中有一个 .gdbinit 文件，下载后再 gdb shell 中输入：

    (gdb) source .gdbinit
    (gdb) zbacktrace
    

**基于 gdb 的功能特点，我们可以使用gdb来排查比如这些问题：**

1. 某个 php 进程占用 cpu 100% 问题
1. 出现 core dump 问题，比如“Segmentation fault”
1. php 扩展出现错误
1. 死循环问题

**一些使用 gdb 排查问题例子：**

* [更简单的重现 PHP Core 的调用栈➫][14]
* [PHP stream未能及时清理现场导致 Core 的 bug➫][15]
* [一个低概率的 PHP Core dump➫][16]
### GDB

gdb 是一个由 GNU 开源组织发布运行在 UNIX/LINUX 操作系统下功能强大的程序调试工具。使用 gdb 可以在程序运行时观察程序的内部结构和内存的使用情况，当程序 dump 时还可以通过 gdb 观察到程序 dump 前发生了什么。主要来说 gdb 具有以下2个功能：

1. 跟踪和变更执行计算机程序
1. 断点功能

因为 php 语言是 c 写的，那么使用 gdb 也就能很方便的去调试 php 代码。举例，我们通过 gdb 来调试一个简单的 php 程序 index.php：

    // 程序代码：
    <?php
    for ($i = 0; $i < 3; $i ++) {
        echo $i . PHP_EOL;
        if ($i == 2) {
            $j = $i + 1;
            var_dump($j);
        }
        sleep(1);
    }
    

gdb 开始调试：

    [root@syyong home]$ sudo gdb php
    

    (gdb)run index.php
    ...
    0
    1
    2
    int(3)
    [Inferior 1 (process 577) exited normally]
    

注：如果 mac 下使用 gdb 时报：“...please check gdb is codesigned - see taskgated(8)...”时可参考[https://leandre.cn/search/gdb/][5]➫。gdb 在调试程序时，如果 ulimit 打开则会把错误信息打印到当前目录下的 core.* 文件中。ulimit -c 如果为0则表示没打开，可以执行 ulimit -c unlimited 或者 ulimit -c 大于0的数字。

#### 常用命令：

* p：print，打印 C 变量的值
* c：continue，继续运行被中止的程序
* b：breakpoint，设置断点，可以按照函数名设置，如 b zif_php_function，也可以按照源代码的行数指定断点，如 b src/networker/Server.c:1000
* t：thread，切换线程，如果进程拥有多个线程，可以使用 t 指令，切换到不同的线程
* ctrl + c：中断当前正在运行的程序，和 c 指令配合使用
* n：next，执行下一行，单步调试
* info threads：查看运行的所有线程
* l：list，查看源码，可以使用 l 函数名 或者 l 行号
* bt：backtrace，查看运行时的函数调用栈。当程序出错后用于查看调用栈信息
* finish：完成当前函数
* f：frame，与 bt 配合使用，可以切换到函数调用栈的某一层
* r：run，运行程序

**使用 .gdbinit 脚本：**  
除了在 gdb shell 里输入命令，也可以预先编写好脚本让 gdb 执行。当 gdb 启动的时候会在当前目录下查找 “.gdbinit” 文件并加载，作为 gdb 命令进行执行。这样就可以不用在命令行中做一些重复的事，比如设定多个断点等操作。另外在 gdb 运行时也可以通过执行“(gdb) source [-s] [-v] filename” 来解释 gdb 命令脚本文件。一个 .gdbinit 文件例子：

    file index.php
    set args hello
    b main
    b foo
    r
    

[php 源码中提供的一个 .gdbinit 示例➫][6]

**其他 gdb 常用命令可以参考：**

* [http://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/gdb.html][7]➫
* [http://coolshell.cn/articles/3643.html][8]➫
* [http://www.cnblogs.com/xuqiang/archive/2011/05/02/2034583.html][9]
* [http://blog.csdn.net/21cnbao/article/details/7385161][10]➫
* [https://sourceware.org/gdb/current/onlinedocs/gdb/][11]➫
* [所有 gdb 命令索引➫][12]

#### gdb 调试 php：

**gdb 有3种使用方式：**

1. 跟踪正在运行的 PHP 程序，使用 “gdb -p 进程ID” 进行附加到进程上
1. 运行并调试 PHP 程序，使用 “gdb php -> run server.php” 进行调试
1. 当 PHP 程序发生 coredump 后使用 gdb 加载 core 内存镜像进行调试 gdb php core

> php 在解释执行过程中，zend 引擎用 executor_globals 变量保存了执行过程中的各种数据，包括执行函数、文件、代码行等。zend 虚拟机是使用 C 编写，gdb 来打印 PHP 的调用栈时，实际是打印的虚拟机的执行信息。

**使用 zbacktrace 更简单的调试：**  
php 源代码中还提供了 zbacktrace 这样的方便的对 gdb 命令的封装的工具。zbacktrace 是 PHP 源码包提供的一个 gdb 自定义指令，功能与 bt 指令类似，与bt不同的是 zbacktrace 看到的调用栈是 PHP 函数调用栈，而不是 c 函数。zbacktrace 可以直接看到当前执行函数、文件名和行数，简化了直接使用 gdb 命令的很多步骤。在 [php-src➫] ([https://github.com/php/php-src][13])的根目录中有一个 .gdbinit 文件，下载后再 gdb shell 中输入：

    (gdb) source .gdbinit
    (gdb) zbacktrace
    

**基于 gdb 的功能特点，我们可以使用gdb来排查比如这些问题：**

1. 某个 php 进程占用 cpu 100% 问题
1. 出现 core dump 问题，比如“Segmentation fault”
1. php 扩展出现错误
1. 死循环问题

**一些使用 gdb 排查问题例子：**

* [更简单的重现 PHP Core 的调用栈➫][14]
* [PHP stream未能及时清理现场导致 Core 的 bug➫][15]
* [一个低概率的 PHP Core dump➫][16]









[5]: https://leandre.cn/search/gdb/
[6]: https://github.com/php/php-src/blob/master/.gdbinit
[7]: http://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/gdb.html
[8]: http://coolshell.cn/articles/3643.html
[9]: http://www.cnblogs.com/xuqiang/archive/2011/05/02/2034583.html
[10]: http://blog.csdn.net/21cnbao/article/details/7385161
[11]: https://sourceware.org/gdb/current/onlinedocs/gdb/
[12]: https://sourceware.org/gdb/current/onlinedocs/gdb/Command-and-Variable-Index.html#Command-and-Variable-Index
[13]: https://github.com/php/php-src
[14]: http://www.laruence.com/2011/12/06/2381.html
[15]: http://www.laruence.com/2010/09/27/1754.html
[16]: http://www.laruence.com/2008/12/31/647.html
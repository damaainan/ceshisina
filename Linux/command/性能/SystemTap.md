# SystemTap入门手册 

[开发进阶][0]

* [内核][1]
* [后台开发][2]
* [开发基础][3]
* [服务运维][4]
* [读书笔记][5]

虽说SystemTap是DTrace在Linux平台上的一个山寨货，但Linux远比Solaris流行使得SystemTap已然成为一个**功能调试**和**性能分析**的常见利器了，而且一般普通的程序员和普通运维人员基本都用不上他，也只有对Linux内核和整个系统有比较全面的了解和掌握的高阶程序员才能驾驭使用，所以看上去这货也是逼格满满啊！这次把SystemTap的文档过了一遍，但是总算有个大致的了解了，毕竟这是个Begin文档，离深入还有很遥远，即使之前自己也在gdb tracepoint使用中的静态观测点使用中早已涉及到这个东西了。



# 一、SystemTap简介

和DTrace的D语言一样，SystemTap也是通过一种类似的SystemTap script脚本语言来实现线上数据的采集和跟踪。在原理上，SystemTap会根据用户写的script，使用stap工具将脚本代码转换成C代码，并将其编译生成对应的内核模块，接下来将其加载到正在运行的内核中去，就可以直接从内核中提取相关数据，正因为需要最终转换成C并编译，所以即使SystemTap script作为脚本存在，运行时对语法的检查还是比较严格的。SystemTap设计的主要思想就是events-handlers，当运行SystemTap script的时候，SystemTap会监测对应的事件(比如函数的进入和退出、定时器超时、会话结束等)，而当事件一旦发生被捕获到了，那么对应的handler将会被作为一个子例程被内核快速执行(这个子例程通常是在当前上下文中提取感兴趣的数据，并将他们保存到内部变量中，通常还会执行打印显示操作)，接下来执行流程恢复正常。在运行stap的时候需要特殊权限才可以，如果不使用root权限执行，则可以将运行用户添加到stapdev|stapusr用户组中，使其具有对应的执行权限。

    [user@centos ~]$ sudo usermod -a -G stapdev user

虽然SystemTap最初目的是针对Linux内核事件进行跟踪和分析的，但是后面大家发现这种跟踪手段对复杂的用户态程序也具有很大的参考价值，所以后续版本的SystemTap社区一直在致力于提供对用户态程序事件跟踪的支持。各个发行版可能有自己的打包和运行环境，推荐使用CentOS进行学习和测试，毕竟SystemTap是RedHat主导开发的，使用SystemTap需要首先安装systemtap、systemtap-runtime两个工具包，同时还需要安装相应的内核开发包和调试信息包(kernel-debug、kernel-headers、kernel-devel、kernel-debuginfo……)，调试包个头比较大，可以在[网站][6]下载后手动安装，此处需要注意运行的内核版本和其他辅助包版本需要完全一致，因为debuginfo软件仓库的版本号跟主仓库内核版本是严重跑偏的，不一致的话会在运行的时候报出符号无法解析的错误。安装成功后使用下面的命令看是否执行成功，该命令的作用主要是跟踪虚拟文件系统的read事件，而相关的SystemTap script语法细节将会在后面进行介绍。简单命令可以用-e直接通过命令行传入，而复杂命令可以写入.stp脚本中运行。

    stap -v -e 'probe vfs.read {printf("read performed\n"); exit()}'

除了上面默认执行方式，对于长时间的跟踪操作，SystemTap还支持Flight Record Mode，该模式下SystemTap跟踪进程将会被作为守护进程在后台执行，受限于存储空间的限制下，SystemTap将会使用最新的数据覆盖之前最旧数据，以达到rotate的效果：  
**In-memory Flight Recorder**  
该模式下SystemTap将会使用内核的内存作为buffer来保存脚本的输出结果，使用-F参数启动后跟踪进程将自动切换为后台进程，而在任意时候都可以使用staprun -A命令再次attach到该进程上面去，此时会自动打印出内存buffer中最新保留的最新历史数据，并且接下来收集的数据也将会被持续的打印显示出来直到退出。  
默认的buffer尺寸是1MB，启动的时候可以使用-s参数指明其大小。  
**File Flight Recorder**  
该模式下脚本的输出将会保存在文件中，通过-o参数指明输出文件名，通过-S参数可以指明每个输出文件的尺寸大小和保留的最新文件的个数。运行这个命令会将SystemTap进程号打印出来，此后任何时刻可以通过向该进程发送SIGTERM信号结束搜集过程。  
一旦SystemTap会话结束，相应的probe会被禁用，同时对应的内核模块也会被自动卸载，整个过程中没有涉及到代码的修改、重编译和重运行，完全达到了向特定位置插入调试打印语句相同的效果，而且整个过程对线上业务的侵入是很小的。

# 二、SystemTap script语法介绍

SystemTap的核心是SystemTap script的编写，通常使用.stp结尾，该语言有着同C和awk语言极为类似的语法，其基本格式为：

    probe event { statements }
    
    function function_name(arguments) { statements }
    
    probe event { function_name(arguments) }

每一个probe可以有多个event，他们之间使用逗号连接，而当该列表中任意一个event被触发的时候，对应的handler都会被执行。为了提供代码复用，SystemTap script还允许编写函数，该函数可以在statements中随意地被调用。

## 2.1 SystemTap Event事件

SystemTap的事件可以分为_同步_和_异步_两类事件，前者通常是和特定代码位置相关的，所以同步事件具有丰富的上下文信息；异步事件通常不跟特定的代码或者特定指令相关联，比如常见的定时器机制就属于异步事件了。  
**a. 同步事件**  
(1) syscall.system_call，比如syscall.close、syscall.close.return事件就针对于close系统调用的进入和返回。  
(2) vfs.file_operation，对于虚拟文件系统的操作，同样可以增加.return监测返回事件。  
(3) kernel.function(“function”)，跟踪对内核中的函数调用的事件，比如kernel.function(“sys_open”)。注意这里可以使用通配符的机制，来侦听某个源代码中的所有函数调用，比如：

    probe kernel.function("*@net/socket.c") {}
    
    probe kernel.function("*@net/socket.c").return {}

(4) kernel.trace(“tracepoint”)，在较新的内核中已经埋藏了tracepoint，这些tracepoint是静态写入代码中的，比如kernel.trace(“kfree_skb”)。  
(5) module(“module”).function(“function”)，可以跟踪模块中的事件信息，比如

    
    probe module("ext3").function("*") {}
    
    probe module("ext3").statement(0xdeadbeef) {}

**b. 异步事件**  
异步事件有begin、end，分别代表SystemTap会话开始和结束的事件，除此之外最常见的异步事件就是定时器了(timer.ms、timer.us等)，这样就可以相隔特定时间执行某个事件了。比如每4s打印一条信息：  

    probe timer.s(4) { printf("hello world\n") }

## 2.2 SystemTap Handler/Body

Handler是使用花括号包围的语句块，默认情况下SystemTap脚本会一直执行，直到遇到exit()调用，或者手动Ctrl-C退出为止。打印函数printf要数最常用的函数了，其支持和C库中printf类似的格式化输出方式，比如
    
    probe syscall.open { printf ("%s(%d) open\n", execname(), pid()) }

上面语句中的execname()和pid()都是SystemTap支持的常用函数，分别代表进程名字和其进程ID，其他常用的函数还有：  
tid()当前线程ID、uid()用户ID、cpu()当前CPU编号、gettimeofday_s()自那个1970年开始的秒数、ctime()转换成date、pp()当前probe的简短描述、ppfunc()如果可以显示当前probe所在的函数名  
print_backtrace()如果可以，打印内核态backtrace、print_ubacktrace()如果可以，打印用户态backtrace  
sprint(2345)创建字符串”2345”  
thread_indent() 主要用来辅助显示调用层次的，比较的厉害

    
    probe kernel.function("*@net/socket.c").call {
    
      printf ("%s -> %s\n", thread_indent(1), probefunc())
    
    }
    
    probe kernel.function("*@net/socket.c").return {
    
      printf ("%s <- %s\n", thread_indent(-1), probefunc())
    
    }

target() stap可以使用-x pid或-c command来和特定的进程相关联，当在这个时候使用target()就具有特殊的含义了，比如

    
    f (pid() == target()) ...

## 2.3 SystemTap Handler结构元素介绍

**a. 普通变量**  
SystemTap脚本中的变量和通常的脚本语言一样，直接在需要使用的时候使用函数或者表达式对其进行赋值操作，而不需要事先定义/声明这个变量，而变量的类型也会根据其赋值的函数返回或表达式求值类型自动判断为字符串还是整形。  
当在Handler中使用的变量是局部于该probe作用域的，如果需要在多个probe之间共享变量，就需要在所有probe之外采用global的方式进行变量的显式声明，比如

    
    global count_jiffies, count_ms
    
    probe timer.jiffies(100) { count_jiffies ++ }
    
    probe timer.ms(100) { count_ms ++ }

**b. 目标变量target variable**  
通过目标变量可以搜集被跟踪代码中特定位置可见的变量值，通过stap -l参数可以查看在指定位置所包含的probe，而stap -L除了可以列出指定位置的probe外还可列出局部变量。

    ➜ ~ stap -l 'kernel.function("vfs_read")'
    
    kernel.function("vfs_read@fs/read_write.c:448")
    
    ➜ ~ stap -L 'kernel.function("vfs_read")'
    
    kernel.function("vfs_read@fs/read_write.c:448") $file:struct file* $buf:char* $count:size_t $pos:loff_t*

这时候，我们查看一下对应的内核源代码的位置信息(安装kernel-xxx.src.rpm源代码包)，显然是跟上面的结果形成了某种对应关系：

    
    ssize_t vfs_read(struct file *file, char __user *buf, size_t count, loff_t *pos);

对于局部的目标变量都使用美元符号打头，而当目标变量是非局部变量的时候，可以使用@var(“varname@src/file.c”)这样的格式进行指定。SystemTap本身跟踪了目标变量的类型信息，所以对于结构体可以方便的使用->操作符查看结构体的域成员，当然该操作符的使用不管前置是结构体还是指针类型，都统一使用该操作符。比如对于fs/file_table.c中定义的files_stat静态变量，如果需要访问其max_files域可以使用下面的方式来收集：

    
    ➜ ~ sudo stap -e 'probe kernel.function("vfs_read") {

           printf ("current files_stat max_files: %d\n",

                   @var("files_stat@fs/file_table.c")->max_files);

           exit(); }'

    current files_stat max_files: 79636

如果需要访问内核空间、用户空间指定位置特定类型的值，可以使用下面特定的函数来快速收集值  
kernel_char/short/int/long(addr)、kernel_string(addr)、kernel_string_n(addr, n)  
user_char/short/int/long(addr)、user_string(addr)、user_string_n(addr, n)  
绝大多数情况下，SystemTap都是用于收集数据的，所以数据的显示和打印基本能完成大多数SystemTap的功能。为了方便起见，提供了各种变量用于快捷的目标变量代表示：

    
    ➜ ~ sudo stap -e 'probe kernel.function("vfs_read") {
    
    quote> printf ("vars: %s\n", $$vars); printf ("locals: %s\n", $$locals);
    
    quote> printf ("parms: %s\n", $$parms); exit(); }'
    
    vars: file=0xffff88002efc3400 buf=0x7ffca1f6dfa0 count=0x2004 pos=0xffff88002ddaff48 ret=?
    
    locals: ret=?
    
    parms: file=0xffff88002efc3400 buf=0x7ffca1f6dfa0 count=0x2004 pos=0xffff88002ddaff48

其中vars包含局部变量locals和调用参数parms，还说有return变量，不过卸载脚本中好像没用。  
上面显示的file只是个地址，如果想了解其内部成员变量的值，可以在结尾添加dollar符进行取值，而且可以添加跟多的dollar符实现嵌套结构的取值显示。

    ➜ ~ sudo stap -e 'probe kernel.function("vfs_read") {

           printf ("parms: %s\n", $$parms$); exit(); }'

**c. 类型转换和目标变量验证**  
在大多数情况下SystemTap都能够从调试信息中自动判别变量的数据类型，但有时候比如为实现多态的void指针类型的变量就无法得到类型信息，此时可以使用SystemTap的@cast来进行类型类型转换：

    @cast(task, "task_struct", "kernel<linux/sched.h>")->state

通过@defined可以检查目标变量是否可用(是否存在)，其返回值可作为bool条件检测位置。

    
    ➜ ~ sudo stap -e 'probe kernel.function("__handle_mm_fault@mm/memory.c") {

           write_access = (@defined($flags) ? $flags & FAULT_FLAG_WRITE : $write_access)

           print(@defined($flags)); print(write_access); exit(); }'

**d. 条件语句**  
条件和循环是作为一个语言的基本要素，SystemTap也支持。其条件操作符包括：== >= <= !=，同时还可以使用&&、||表达式。  
条件循环语句包括if、if else、while、for语句，其基本语法格式跟C语言是一样的，此外对于关联数组还有新式流行的foreach访问语句：

    
    if (EXPR) STATEMENT [else STATEMENT] 
    
    while (EXPR) STATEMENT
    
    for (A; B; C) STATEMENT

**e. 命令行参数**  
在执行SystemTap脚本的时候，脚本也可以访问命令行参数。对于数值类型(整形)的参数，使用$1、$2、……的方式依次访问，如果是字符串类型的参数，使用@1、 @2、……的方式依次访问。

    
    ➜ ~ sudo stap -e 'probe kernel.function(@1) {
    
           printf ("vars: %s", $$vars); exit(); }' vfs_read

    vars: file=0xffff88002d2bb000 buf=0x7ffd50543d00 count=0x2004 pos=0xffff88002ead7f48 ret=?%

## 2.4 关联数组

关联数组就是通常所谓的字典数据类型，其键值要求必须唯一，其内部采用hash table实现的。使用关联数组的时候必须要求其被声明为global的(而不管其是否只会被一个probe使用)，在global声明的时候可以指定预留大小以提高效能global reads[400}。在使用的时候貌似约定其所有键的类型必须一致、其所有值的类型必须一致，不允许数字和字符串混用，对于已经存在的键再次赋值会用新值替换之前的旧值：

    ➜ ~ sudo stap -e 'global arr
    
    probe begin {
    
    arr["TAO"] = "zhijiang"
    
    arr["time"] = ctime(gettimeofday_s())
    
    printf("show value %s at %s\n", arr["TAO"], arr["time"]) exit();
    
    }'
    
    show value zhijiang at Sun Apr 9 10:21:30 2017

关联数组的使用十分便捷，比如reads[execname()]++自增操作；使用delete reads可以清空重置整个关联数组，或者使用delete reads[execname()]删除某一个键值对，需要注意不要在遍历关联数组的时候删除元素(修改关联数组)。判断一个元素是否在关联数组中使用if([“stapio”] in reads)这类的语句。

    ➜ ~ sudo stap -e 'global reads
    
    probe vfs.read { reads[execname()]++ }
    
    probe timer.s(3) {
    
         // foreach (count in reads)
    
        foreach (count in reads+ limit 5)
    
            printf("sorted: %s : %d \n", count, reads[count]); } '
    
    sorted: systemd-udevd : 4
    
    sorted: irqbalance : 6
    
    sorted: stapio : 21

在上面的foreach语句中，既可以简单的无顺打印，也可以添加+/-进行有序遍历，通过limit还可以限制访问元素迭代的次数。  
关联数组还用处比较多的是数据统计，通过<<<操作符可以进行数据累加，然后通过特定的extractor操作符就可以得到峰值、累计值、均值等信息。

    ➜ ~ sudo stap -e 'global reads
    
    probe vfs.read { reads[execname()] <<< 1 }
    
    probe timer.s(3) {
    
        foreach (item in reads)
    
         printf("%s: count:%d, sum:%d, min:%d, max:%d, avg:%d\n", item,
    
             @count(reads[item]),@sum(reads[item]),@min(reads[item]),@max(reads[ item]),@avg(reads[item]))  
    
    } '

## 2.5 Tapsets

Tapsets是随着系统分发的预定义的SystemTap script library，其一般位于/usr/share/systemtap/tapset/目录下并以.stp结尾。这些脚本通常不是用来直接执行的，而是提供一些预定义的probes和function定义为用户编写脚本服务的。用户编写脚本调用到的probes和handlers，工具会首先在tapsets中寻求其定义，然后再进行后续的脚本转化、编译等步骤。

# 三、用户态跟踪

虽然SystemTap最初是为内核服务的，但是其提供的跟踪特性对于user-space问题跟踪也颇具帮助，所以当前SystemTap开发完善的热点在于对用户态程序跟踪的支持。目前SystemTap支持用户态程序函数的调用和返回、预定义静态marker和用户程序进程事件等信息。

## 3.1 用户态Events

用户态事件跟内核态事件比较相像，所有用户态跟踪事件都以process开头。在指定用户态事件时候，既可以通过PATH制定可执行程序名，这样跟踪的事件就限制在指定的可执行程序范围上。下面带有PATH参数的是在使用的时候必须指定的，而不带PATH参数的事件，其PATH是可选的限定参数。  
**process(“PATH”).function(“function”)**和**process(“PATH”).function(“function”).return**  
**process(“PATH”).statement(“statement”)**  
其跟踪位置为指定代码位置的第一条执行指令，比如下面代表a.out项目源代码中200行位置第一条指令，比如像process(“a.out”).statement(“*@main.c:200”)这么用。

**process(“PATH”).mark(“marker”)**  
这里的mark就是之前讲到的静态跟踪点SDT，很有用吧。所以良好的开发习惯就是要在代码的关键位置多埋一些SDT，因为这东西对程序的行为和性能没有丝毫影响，但是在调试和跟踪程序的时候就知道这东西是多么的有用。

**process.begin**和**process.end**  
进程创建和退出事件。可以使用PATH和process ID进行限定。

**process.thread.begin**和**process.thread.end**  
用户态线程的创建和退出事件。可以使用PATH和process ID进行限定。

**process.syscall**  
跟踪用户态进程的系统调用事件，系统调用号保留在 $ syscall中，可以通过 $ arg1、…… $ arg6访问系统调用参数，使用.return可以捕获系统调用返回事件，通过 $ return访问系统调用的返回值。可以使用PATH和process ID进行限定。

## 3.2 用户态Stack Backtraces

通过-d executable/object可以指定可执行程序或者目标文件的跟踪，而使用-ldd可以指定对用户态引用的共享库的跟踪。手册中说到很多时候编译器为了优化性能会省略stack frame pointers，而使用编译的调试信息，SystemTap可以使用这些调试信息重建调用栈信息。

    
    ➜ tapset sudo stap -d /bin/ls --ldd \
    
    -e 'probe process("ls").function("xmalloc") {print_usyms(ubacktrace())}' \
    
    -c "ls /"
    
    bin boot dev    etc home lib    lib64 media mnt opt    proc root run sbin srv   sys tmp usr <var> </var>
    
     0x4115f0 : xmalloc+0x0/0x20 [/usr/bin/ls]
    
     0x4117c4 : xmemdup+0x14/0x30 [/usr/bin/ls]
    
     0x40ef5a : clone_quoting_options+0x2a/0x40 [/usr/bin/ls]
    
     0x402cff : main+0x34f/0x2198 [/usr/bin/ls]
    
     0x7fa85c8b5b35 : __libc_start_main+0xf5/0x1c0 [/usr/lib64/libc-2.17.so]

流水账算是走完了，要深入的话还需继续努力！

本文完！

# 参考

* [SystemTap Beginners Guide][7]
* [Systemtap tutorial][8]
* [Understanding SDT Markers and Debugging with Subtlety][9]
* [动态追踪技术漫谈][10]

[0]: /categories/开发进阶/
[1]: /tags/内核/
[2]: /tags/后台开发/
[3]: /tags/开发基础/
[4]: /tags/服务运维/
[5]: /tags/读书笔记/
[6]: http://debuginfo.centos.org/
[7]: https://sourceware.org/systemtap/SystemTap_Beginners_Guide/
[8]: https://sourceware.org/systemtap/tutorial/
[9]: http://opensourceforu.com/2015/03/understanding-sdt-markers-and-debugging-with-subtlety/
[10]: https://openresty.org/posts/dynamic-tracing/
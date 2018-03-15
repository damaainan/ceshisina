## linux基础命令介绍九：进程与内存

来源：[https://segmentfault.com/a/1190000007649899](https://segmentfault.com/a/1190000007649899)

计算机存在的目的就是为了运行各种各样的程序，迄今我们介绍的绝大多数命令，都是为了完成某种计算而用编程语言编写的程序，它们以文件的形式保存在操作系统之中(比如/bin下的各种命令)；但静态的程序并不能“自发的”产生结果，只有在操作系统中为其指定输入数据并运行起来，才能得到输出结果。而操作系统中程序运行的最主要表现形式便是`进程`。
静态程序可以长久的存在，动态的进程具有有限的生命周期。每次程序运行的开始(如键入一条命令后按下回车键)，操作系统都要为程序的运行准备各种资源，这些资源绝大多数都处于`内存`之中。为了限制多用户进程的权限，linux还定义了两种进程运行时态：`内核态`和`用户态`；当进程想要请求系统服务时(比如操作一个物理设备)，必须通过`系统调用`(操作系统提供给用户空间的接口函数)来实现，此时系统切换到内核态，代表程序执行该系统调用，执行完毕后系统切换回用户态，继续执行程序代码。
本文介绍linux中关于进程与内存的管理命令(更多的是查看命令)
### 1、`uptime`系统运行时间

```sh
uptime [options]
```

单独执行此命令时，输出信息表示：当前时间，系统运行时长，登录用户个数，系统过去1、5、15分钟内的平均负载。

```sh
[root@centos7 ~]# uptime
 10:46:38 up 58 days, 19:20,  3 users,  load average: 0.00, 0.01, 0.05
```
### 2、`ps`显示系统进程信息

```sh
ps [options]
```

单独运行ps命令时显示信息为：进程ID号(PID)、终端(TTY)、运行累积CPU时长(TIME)、命令名(CMD)

```sh
[root@centos7 ~]# ps
  PID TTY          TIME CMD
 9503 pts/1    00:00:00 bash
 9570 pts/1    00:00:00 ps
```

这里简要叙述一下关于`进程`、`进程组`、`会话`和`终端`的关系。linux操作系统为了方便管理进程，将功能相近或存在父子、兄弟关系的进程归为一组，每个进程必定属于一个进程组，也只能属于一个进程组。一个进程除了有进程ID外，还有一个进程组ID(`PGID`)；每个进程组都有一个进程组组长，它的PID和进程组ID相同。像一系列相关进程可以合并为进程组一样，一系列进程组也可以合并成一个会话`session`。会话是由其中的进程建立的，该进程叫做会话的首进程(session leader)。会话首进程的PID即为此会话的SID(session ID)。每个会话都起始于用户登录，终止于用户退出。会话中的每个进程组称为一个工作(job)。会话可以有一个进程组成为会话的前台工作(foreground)，而其他的进程组是后台工作(background)。每个会话都关联到一个控制终端`control terminal`，当会话终止时(用户退出终端)，系统会发送终止信号(`SIGHUP`)给会话中的所有进程组，进程对此信号的默认处理方式为终止进程。
`ps`接受三种格式的选项，带前缀符号`-`的`UNIX`格式的选项；不带前缀的`BSD`风格的选项；带两个`-`的`GNU`长格式选项。三种类型的选项可以自由组合，但可能会出现冲突。

选项`a`(BSD)表示显示所有和终端关联的进程信息，当配合选项`x`(BSD)一起使用时表示显示所有进程信息(此时终端无关的进程TTY列显示为`?`)。
选项`-a`(UNIX)表示显示与终端关联的除了会话首进程之外的进程信息。选项`-e`表示所有进程。

```sh
[root@centos7 ~]# ps a
  PID TTY      STAT   TIME COMMAND
 2528 tty1     Ss+    0:00 -bash
 9336 pts/0    Ss     0:00 -bash
 9503 pts/1    Ss     0:00 -bash
 9550 pts/2    Ss+    0:00 -bash
 9571 pts/0    S+     0:00 man ps
 9582 pts/0    S+     0:00 less -s
 9643 pts/1    R+     0:00 ps a
[root@centos7 ~]# ps -a
  PID TTY          TIME CMD
 9571 pts/0    00:00:00 man
 9582 pts/0    00:00:00 less
 9644 pts/1    00:00:00 ps
```

如例子中所示，BSD风格的选项还会显示进程的状态信息以及命令的参数。进程在运行的过程当中可能处于的状态包括：

```sh
D 不可中断的睡眠状态(通常在等待IO)
R 正在运行或可以运行(在运行队列中)
S 可中断的睡眠状态(等待一个事件完成)
T 暂停状态
t 跟踪状态
W 换页状态(2.6内核以后版本)
X 死亡状态(不可见)
Z 僵死状态
#BSD风格的选项STAT列还可能包括以下字符
< 高优先级进程
N 低优先级进程
L 锁定状态
s 会话首进程
l 多线程进程
+ 进程处于前台进程组
```

选项`u`显示用户导向的进程信息(如进程的发起用户，用户态占用CPU和MEM百分比等)

```sh
[root@centos7 ~]# ps au
USER       PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND
root      2528  0.0  0.0 115636  2384 tty1     Ss+  9月30   0:00 -bash
root      9336  0.0  0.0 115596  2240 pts/0    Ss   08:44   0:00 -bash
root      9571  0.0  0.0 119196  1972 pts/0    S+   10:59   0:00 man ps
root      9582  0.0  0.0 110276   980 pts/0    S+   10:59   0:00 less -s
root      9835  0.0  0.0 115636  2172 pts/1    Ss   13:48   0:00 -bash
root      9938  0.0  0.0 115512  2096 pts/2    Ss   14:49   0:00 -bash
root      9960  0.0  0.0 154068  5632 pts/2    S+   14:50   0:00 vim others.sh
root      9967  0.0  0.0 139496  1640 pts/1    R+   14:59   0:00 ps au
```
`VSZ`表示占用的总的地址空间大小。它包括了没有映射到内存中的页面。
`RSS`表示实际驻留"在内存中"的内存大小，不包括交换出去的内存。和VSZ的单位均为KB
通常查看所有进程信息会使用命令`ps -ef`或`ps aux`选项`-o`或`o`表示指定输出格式
如显示所有bash进程的pid，命令名，运行于哪颗逻辑cpu：

```sh
[root@centos7 ~]# ps -eo pid,comm,psr|grep bash
 2528 bash              1
 9336 bash              4
 9835 bash              3
 9938 bash              6
```

配合选项`--sort`可指定按某一列排序输出

```sh
#表示按用户名排序
ps -eo pid,user,args --sort user
```

还可以用-o指定许多其他信息，请查询相关手册。
### 3、`kill`终止进程

```sh
kill [options] pid...
```

命令`kill`会发送特定的信号给指定的进程或进程组，如果没有指定信号，则发送TERM信号
选项`-l`表示列出所有支持的信号：

```sh
[root@centos7 ~]# kill -l
 1) SIGHUP       2) SIGINT       3) SIGQUIT      4) SIGILL       5) SIGTRAP
 6) SIGABRT      7) SIGBUS       8) SIGFPE       9) SIGKILL     10) SIGUSR1
11) SIGSEGV     12) SIGUSR2     13) SIGPIPE     14) SIGALRM     15) SIGTERM
16) SIGSTKFLT   17) SIGCHLD     18) SIGCONT     19) SIGSTOP     20) SIGTSTP
21) SIGTTIN     22) SIGTTOU     23) SIGURG      24) SIGXCPU     25) SIGXFSZ
26) SIGVTALRM   27) SIGPROF     28) SIGWINCH    29) SIGIO       30) SIGPWR
31) SIGSYS      34) SIGRTMIN    35) SIGRTMIN+1  36) SIGRTMIN+2  37) SIGRTMIN+3
38) SIGRTMIN+4  39) SIGRTMIN+5  40) SIGRTMIN+6  41) SIGRTMIN+7  42) SIGRTMIN+8
43) SIGRTMIN+9  44) SIGRTMIN+10 45) SIGRTMIN+11 46) SIGRTMIN+12 47) SIGRTMIN+13
48) SIGRTMIN+14 49) SIGRTMIN+15 50) SIGRTMAX-14 51) SIGRTMAX-13 52) SIGRTMAX-12
53) SIGRTMAX-11 54) SIGRTMAX-10 55) SIGRTMAX-9  56) SIGRTMAX-8  57) SIGRTMAX-7
58) SIGRTMAX-6  59) SIGRTMAX-5  60) SIGRTMAX-4  61) SIGRTMAX-3  62) SIGRTMAX-2
63) SIGRTMAX-1  64) SIGRTMAX
[root@centos7 ~]# 
```

可以使用选项`-s`指定要发送的信号
如在一个终端启动进程`sleep 300`，在另一个终端查看并使用信号SIGKILL将其终止：

```sh
[root@centos7 ~]# sleep 300
#此时会一直等待sleep执行完毕
#在另一个终端中
[root@centos7 temp]# ps -ef|grep [s]leep
root     10359  9835  0 12:05 pts/1    00:00:00 sleep 300
#发送信号
[root@centos7 temp]# kill -s SIGKILL 10359
#原终端显示
[root@centos7 ~]# sleep 300
已杀死
[root@centos7 ~]# 
```

或者执行命令`kill -9 10359`是同样的效果。关于其他信号的作用，请自行搜索。
### 4、`pgrep`和`pkill`搜索或者发送信号给进程

```sh
pgrep [options] pattern
pkill [options] pattern
```

这里的`pattern`是正则表达式，用来匹配进程名
如查看名称为`gunicorn`的所有进程

```sh
[root@centos7 ~]# pgrep gunicorn
17268
17286
17289
17290
17293
```

选项`-l`显示进程名和pid

```sh
[root@centos7 ~]# pgrep -l gun
17268 gunicorn
17286 gunicorn
17289 gunicorn
17290 gunicorn
17293 gunicorn
```

如终止所有sleep进程

```sh
pkill sleep
```

如使`syslogd`重读它的配置文件

```sh
pkill -HUP syslogd
```
### 5、`top`显示进程信息
`top`命令实时动态的显示系统汇总信息和进程状态信息，它每隔1s刷新一次，按键盘`q`键退出。
单独执行`top`命令时显示如下输出：

```sh
top - 03:20:02 up 59 days, 17:30,  3 users,  load average: 0.00, 0.01, 0.05
Tasks: 184 total,   1 running, 183 sleeping,   0 stopped,   0 zombie
%Cpu(s):  0.1 us,  0.0 sy,  0.0 ni, 99.9 id,  0.0 wa,  0.0 hi,  0.0 si,  0.0 st
KiB Mem :  8010720 total,  5100308 free,   420652 used,  2489760 buff/cache
KiB Swap:  8257532 total,  8257532 free,        0 used.  6905944 avail Mem 

  PID USER      PR  NI    VIRT    RES    SHR S  %CPU %MEM     TIME+ COMMAND
    1 root      20   0  193664   8708   2396 S   0.0  0.1   1:23.98 systemd
    2 root      20   0       0      0      0 S   0.0  0.0   0:00.44 kthreadd
    3 root      20   0       0      0      0 S   0.0  0.0   0:00.10 ksoftirqd/0
    5 root       0 -20       0      0      0 S   0.0  0.0   0:00.00 kworker/0:0H
    7 root      rt   0       0      0      0 S   0.0  0.0   0:00.34 migration/0
    8 root      20   0       0      0      0 S   0.0  0.0   0:00.00 rcu_bh
    9 root      20   0       0      0      0 S   0.0  0.0   0:00.00 rcuob/0
   10 root      20   0       0      0      0 S   0.0  0.0   0:00.00 rcuob/1
```

下面分别对每行输出内容进行解释(注：top版本为`3.3.10`，其他版本的输出第四行和第五行可能不同)

第一行显示信息和命令`uptime`的输出一致；

第二行显示任务汇总信息，状态即为进程可能状态中的四种；

第三行显示cpu负载信息，其中`us`表示用户态任务占用CPU时间百分比，`sy`表示内核态任务占用CPU时间百分比，`ni`表示改变过进程优先级的进程(通过`nice`或`renice`命令)占用CPU时间百分比，`id`表示CPU空闲时间百分比，`wa`表示等待输入输出的进程占用CPU时间百分比，`hi`表示硬件中断花费时间，`si`表示软件中断花费时间，`st`表示虚拟机等待真实物理机CPU资源的时间

第四行显示内存信息，`total`表示总内存，`free`表示未分配内存，`used`表示使用的内存(值为`total-free-buff/cache`的结果)，`buff/cache`表示缓存内存；

第五行显示交换分区使用量，其中`avail Mem`表示启动一个新程序时可以分配给它的最大内存，和第三行free列不同的地方在于，它会统计可以被回收的缓存分配器(slab)和页高速缓冲存储器(page cache)中的内存。(在一些较早的top实现中，并没有这一列的值)

接下来经过一个空行之后，显示的是进程相关信息，表头各列字段和`ps`命令的输出均有相对应的关系，其中`PR`表示优先级；`NI`表示nice值(后述)；`VIRT`表示虚拟内存大小，对应ps命令中的`VSZ`；`RES`表示进程常驻内存大小，对应ps命令中的`RSS`；`SHR`表示共享内存大小；`S`表示进程状态，对应ps命令的`STAT`；

linux系统的进程状态中有一个优先级(priority)的概念，其值是一个动态变化的整数，范围是0-139，此值越小，则优先级越高，那么它就越优先被CPU执行。如果`top`命令`PR`列显示为`rt`，表示此进程为`实时进程`，它的优先级范围是0-99，比其他的普通进程都要高。linux中还有静态优先级的概念，用户可以通过使用命令`nice`和`renice`对进程设置或改变静态优先级，它可以看成是动态优先级的修正值，能够影响动态优先级的值。
`PR`列显示的值为实际优先级减去实时进程最大优先级之后的值，3.10内核非实时进程的默认值为20，即：`DEFAULT_PRIO = MAX_RT_PRIO + 20 = 120``NI`列不为0时，表示进程被设置过静态优先级值，范围是-20到19，它与当前优先级值的关系是：`DEFAULT_PRIO = MAX_RT_PRIO + (nice) + 20`如使用nice启动一个sleep进程：

```sh
#当不使用选项-n指定时，默认值为10
[root@centos7 ~]# nice -n -10 sleep 300
#对于已存在的进程可以使用renice命令调整其静态优先级
[root@centos7 ~]# 
[root@centos7 ~]# ps -eo pri,ni,comm|grep sleep
29  -10 sleep
[root@centos7 ~]#
[root@centos7 ~]# top -bn1 |egrep 'COMMAND$|sleep$'
  PID USER      PR  NI    VIRT    RES    SHR S  %CPU %MEM     TIME+ COMMAND
11967 root      10 -10  107892    616    528 S   0.0  0.0   0:00.00 sleep
#注意这里ps和top优先级值显示的不同，ps命令pri列的值 29 = MAX_PRIO(139) -  MAX_RT_PRIO(100) + nice(-10)。它们实际的优先级值是相等的。
```

上例中使用了选项`-n`表示top刷新次数，`-b`表示批处理模式运行top，此模式会去掉输出中的控制字符，方便将输出交给其他程序处理。
选项`-o fieldname`按指定列排序输出，选项`-O`可以列出`-o`能够指定的列名

```sh
#自行执行命令查看效果
top -O |tr '\n' ' '
top -bn1 -o PR
```

下面简要介绍一些`top`中可以使用的交互命令：

```sh
q 退出top
h 获得帮助信息
1 显示每个逻辑cpu的信息
k 终止一个进程(会提示用户输入需要终止的pid，以及需要发送什么样的信号)
r 重新设置进程静态优先级(相当于执行renice)
i 忽略闲置和僵死进程
H 显示线程信息
M 根据驻留内存大小排序
P 根据CPU使用百分比排序
W 将当前设置写入~/.toprc文件中
```
### 6、`free`显示系统内存使用情况

```sh
free [options]
```
`free`命令显示系统当前内存、swap(交换分区)的使用情况，默认单位是KB

```sh
#版本3.3.10
[root@centos7 ~]# free
              total        used        free      shared  buff/cache   available
Mem:        8010720      423060     4540476      375580     3047184     6897052
Swap:       8257532           0     8257532
```

显示信息和top命令输出中的对应值一致，其中`shared`表示内存文件系统(tmpfs)中使用内存的大小。
前面讲述了`available`对应值所表示的含义，通常查看系统当前还有多少可用内存，看`available`的对应值就可以了。这里`available = free + 缓存(可被回收部分)`。
但在较老版本的free中并没有这个值，它的输出可能是这样的：

```sh
             total       used       free     shared    buffers     cached
Mem:       8174384    4120488    4053896          0     229320    1041712
-/+ buffers/cache:    2849456    5324928
Swap:     16779884          0   16779884
```

说明：
`buffer(缓冲)`是为了提高内存和硬盘(或其他I/O设备)之间的数据交换的速度而设计的
`cache(缓存)`是为了提高cpu和内存之间的数据交换速度而设计的
所以输出中`buffers`可简单理解为准备写入硬盘的缓冲数据；`cached`可理解为从硬盘中读出的缓存数据(页高速缓冲存储器)，缓存中`可被回收部分`来自cached和slab(缓存分配器)
`Mem`行：`used = total - free`此时的空闲内存`free`列并不能体现系统当前可用内存大小    
`-/+ buffers/cache`行：`used = total - free(Mem) - (buffers + cached)`，这里的`free`列和前面所述的`available`关系为`available = free + 缓存(可被回收部分)`所以当没有`available`列可查看时，并不能通过`free`命令查到或计算出真正可用内存，需要知道缓存部分的具体情况。

选项`-b`、`-k`、`-m`、`-g`分别表示指定各值的单位：bytes, KB, MB, 或者 GB
### 7、`fuser`使用文件或套接字定位进程
`fuser`经常用来查看文件被哪些进程所使用

```sh
[root@centos7 ~]# fuser .
/root:                2528c 11430c 11447c
```

例子表示显示有三个进程在使用当前目录，其中：`2528c`前面数字表示进程PID，后面的字符c表示当前目录(即进程在此目录下工作)，还可能出现的字符有：

```sh
e 表示进程正在运行执行文件
f 打开文件，默认输出时省略
F 写方式打开文件，默认时输出省略
r 根目录
m mmap文件或共享库文件
```

选项`-k`表示发送信号`SIGKILL`给相关进程(谨慎使用)
选项`-i`表示交互，在kill一个进程之前询问用户
选项`-l`列出支持的信号
选项`-SIGNAL`指定信号
### 8、`lsof`列出打开文件

在[这一篇][0]中我们简单描述了bash进程打开的前三个文件，并分别关联到文件描述符`0`,`1`,`2`。对于其他进程打开的文件也是同样，系统为每个进程维护一个文件描述符表，该表的值都是从0开始的数字。单独执行`lsof`命令时会显示系统中所有进程打开的文件

```sh
#命令版本为4.87
[root@centos7 temp]# lsof |head
COMMAND     PID   TID    USER   FD      TYPE             DEVICE  SIZE/OFF       NODE NAME
systemd       1          root  cwd       DIR              253,0      4096        128 /
systemd       1          root  rtd       DIR              253,0      4096        128 /
systemd       1          root  txt       REG              253,0   1489960       6044 /usr/lib/systemd/systemd
systemd       1          root  mem       REG              253,0     20032  201329002 /usr/lib64/libuuid.so.1.3.0
systemd       1          root  mem       REG              253,0    252704  201330338 /usr/lib64/libblkid.so.1.1.0
systemd       1          root  mem       REG              253,0     90632  201328968 /usr/lib64/libz.so.1.2.7
systemd       1          root  mem       REG              253,0     19888  201329137 /usr/lib64/libattr.so.1.1.0
systemd       1          root  mem       REG              253,0     19520  201328509 /usr/lib64/libdl-2.17.so
systemd       1          root  mem       REG              253,0    153192  201328867 /usr/lib64/liblzma.so.5.0.99
```

每行一个打开的文件，表头各列意为：

```sh
COMMAND 进程命令名前9个字符
PID     进程ID
TID     任务ID
FD  1)文件描述符号或者下面字符：
    cwd 当前工作目录
    err FD错误信息
    ltx 共享库代码
    mem 内存映射文件
    mmap 内存映射设备
    pd  父目录
    rtd 根目录
    txt 程序代码
    2)当是FD(数字)时，后面可能跟下面权限字符：
    r 读
    w 写
    u 读写
    空格 权限未知且无锁定字符
    - 权限未知但有锁定字符
    3)权限字符后可能有如下锁定字符：
    r 文件部分读锁
    R 整个文件读锁
    w 文件部分写锁
    W 整个文件写锁
    u 任意长度读写锁
    U 未知类型锁
    空格 无锁
TYPE    类型，可能值为：
    DIR 目录
    REG 普通文件
    CHR 字符设备文件
    BLK 块设备文件
    FIFO 管道文件
    unix UNIX套接字文件
    IPv4 IPv4套接字文件
    ....
DEVICE  设备号
SIZE/OFF 文件大小或偏移量(bytes)
NODE    文件inode号
```

选项`-n`表示不做ip到主机名的转换
选项`-c string`显示COMMAND列中包含指定字符的进程所有打开的文件
选项`-u username`显示所属user进程打开的文件
选项`-d FD`显示打开的文件描述符为FD的文件

```sh
[root@centos7 ~]# lsof -d 4
COMMAND     PID    USER   FD      TYPE             DEVICE SIZE/OFF      NODE NAME
systemd       1    root    4u  a_inode                0,9        0      5755 [eventpoll]
systemd-j   539    root    4u     unix 0xffff880230168f00      0t0     10467 /run/systemd/journal/socket
systemd-u   549    root    4u     unix 0xffff88003693d640      0t0     12826 /run/udev/control
lvmetad     555    root    4wW     REG               0,18        4      8539 /run/lvmetad.pid
auditd      693    root    4w      REG              253,0   701364 208737917 /var/log/audit/audit.log
....
```

选项`+d DIR`显示目录中被进程打开的文件
选项`+D DIR`递归显示目录中被进程打开的文件

```sh
[root@centos7 ~]# lsof +d /root|head -3
COMMAND   PID USER   FD   TYPE DEVICE SIZE/OFF      NODE NAME
bash     2528 root  cwd    DIR  253,0     4096 201326721 /root
bash    12902 root  cwd    DIR  253,0     4096 201326721 /root
```

选项`-i`表示显示符合条件的进程打开的文件，格式为`[46][protocol][@hostname|hostaddr][:service|port]`

```sh
#查看22端口运行情况
[root@centos7 ~]# lsof -ni :22
COMMAND   PID USER   FD   TYPE  DEVICE SIZE/OFF NODE NAME
sshd     1358 root    3u  IPv4    8979      0t0  TCP *:ssh (LISTEN)
sshd     1358 root    4u  IPv6    8981      0t0  TCP *:ssh (LISTEN)
sshd    12900 root    3u  IPv4 3509687      0t0  TCP 10.0.1.254:ssh->192.168.78.143:57325 (ESTABLISHED)
#例子，smtp为/etc/services文件中列出服务中的一种
[root@centos7 ~]# lsof -ni 4TCP@0.0.0.0:22,smtp  
COMMAND   PID USER   FD   TYPE  DEVICE SIZE/OFF NODE NAME
sshd     1358 root    3u  IPv4    8979      0t0  TCP *:ssh (LISTEN)
master   2162 root   13u  IPv4   16970      0t0  TCP 127.0.0.1:smtp (LISTEN)
sshd    12900 root    3u  IPv4 3509687      0t0  TCP 10.0.1.254:ssh->192.168.78.143:57325 (ESTABLISHED)
```

试想，如果删除了一个正在被其他进程打开的文件会怎样？实验来看看现象：

```sh
#使用more命令查看一个文件
[root@centos7 ~]# more /root/.bash_history
#在另一个终端使用lsof查看
[root@centos7 ~]# lsof|grep ^more
more      14470          root  cwd       DIR              253,0      4096  201326721 /root
more      14470          root  rtd       DIR              253,0      4096        128 /
more      14470          root  txt       REG              253,0     41096  134321844 /usr/bin/more
more      14470          root  mem       REG              253,0 106065056  134319094 /usr/lib/locale/locale-archive
more      14470          root  mem       REG              253,0   2107816  201328503 /usr/lib64/libc-2.17.so
more      14470          root  mem       REG              253,0    174520  201328905 /usr/lib64/libtinfo.so.5.9
more      14470          root  mem       REG              253,0    164440  225392061 /usr/lib64/ld-2.17.so
more      14470          root  mem       REG              253,0    272001   67147302 /usr/share/locale/zh_CN/LC_MESSAGES/util-linux.mo
more      14470          root  mem       REG              253,0     26254  201328839 /usr/lib64/gconv/gconv-modules.cache
more      14470          root    0u      CHR              136,1       0t0          4 /dev/pts/1
more      14470          root    1u      CHR              136,1       0t0          4 /dev/pts/1
more      14470          root    2u      CHR              136,1       0t0          4 /dev/pts/1
more      14470          root    3r      REG              253,0     17656  202386313 /root/.bash_history
#删除这个文件
[root@centos7 ~]# rm -f /root/.bash_history
#查看
[root@centos7 ~]# lsof -d 3|grep ^more
more      14470    root    3r      REG              253,0    17656  202386313 /root/.bash_history (deleted)
[root@centos7 ~]#
#会发现文件列多出了delete的字样
```

linux系统中`/proc`目录保存了系统所有进程相关的数据，里面的数字目录名即为PID。我们进一步来看一下刚才的more进程的文件描述符

```sh
[root@centos7 ~]# cat /proc/14470/fd/3 > /root/.bash_history.bak
#此操作会将文件描述符3中的内容保存至/root/.bash_history.bak
#停止more进程并查看
[root@centos7 ~]# ls -l /root/.bash_history*
-rw-r--r-- 1 root root 17656 11月 30 07:47 /root/.bash_history.bak
[root@centos7 ~]# cat /root/.bash_history.bak
#会发现原文件没有了，新文件保存了原文件的所有内容
```

结论就是，如果在删除文件的时候有进程正在打开该文件，那么该文件的内容还是可以通过进程的对应文件描述符恢复的。同时，如果删除了某文件，发现空间并没有释放，说明有进程正在打开该文件(命令`lsof|grep delete`查看)，重新启动该进程之后，空间就会得到释放。
### 9、`iostat`显示CPU、I/O统计信息

```sh
[root@centos7 ~]# iostat
Linux 3.10.0-327.el7.x86_64 (centos7)   2016年11月30日  _x86_64_        (8 CPU)

avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.12    0.00    0.03    0.00    0.00   99.85

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.23         0.79         3.05    4178309   16079082
dm-0              0.22         0.57         2.94    3002207   15480498
dm-1              0.00         0.00         0.00       1088          0
dm-2              0.03         0.22         0.11    1146430     596232
dm-3              0.06         0.01         1.91      28900   10079073
dm-4              0.03         0.01         1.91      28644   10079073
```

显示信息中cpu部分在命令`top`的描述中都有相应的解释，I/O部分是各个设备读写速率及总量信息，其中`tps`表示每秒多少次I/O请求
选项`-c`显示CPU信息
选项`-d`显示设备信息
选项`-x`显示更详细的信息
命令`iostat m n`数字(m,n)，m表示时间间隔，n表示次数；此时iostat会每隔m秒打印一次，打印n次。

```sh
[root@centos7 ~]# iostat -c 1 3
Linux 3.10.0-327.el7.x86_64 (centos7)   2016年11月30日  _x86_64_        (8 CPU)

avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.12    0.00    0.03    0.00    0.00   99.85

avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.12    0.00    0.00    0.00    0.00   99.88

avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.12    0.00    0.12    0.00    0.00   99.75
```

也可以接设备名表示查看指定设备的I/O信息

```sh
[root@centos7 ~]# iostat sda
Linux 3.10.0-327.el7.x86_64 (centos7)   2016年11月30日  _x86_64_        (8 CPU)

avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.12    0.00    0.03    0.00    0.00   99.85

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.23         0.79         3.05    4178309   16084862
```
### 10、`vmstat`显示虚拟内存统计信息

```sh
vmstat [options] [delay [count]]
```

同样也会显示一些CPU和I/O的信息
选项`-w`格式化输出

```sh
[root@centos7 ~]# vmstat -w
procs -----------------------memory---------------------- ---swap-- -----io---- -system-- --------cpu--------
 r  b         swpd         free         buff        cache   si   so    bi    bo   in   cs  us  sy  id  wa  st
 1  0            0      4517628         3184      3067904    0    0     0     1    1    0   0   0 100   0   0
```

其中

```sh
procs
    r 表示可运行状态进程数量
    b 表示不可中断睡眠状态进程数量
memory
    swpd  虚拟内存使用量
    free  空闲内存
    buff  buffer缓冲中内存使用量
    cache cache缓存中内存使用量
swap
    si   硬盘交换至内存量
    so   内存交换至硬盘量
io
    bi   从块设备中收到的块(blocks)数
    bo   发送至块设备的块数
system
    in   每秒中断次数，包括锁。
    cs   每秒进程上下文切换次数。
cpu (同命令top)
    us   用户态任务占用CPU时间百分比
    sy   内核态任务占用CPU时间百分比
    id   CPU空闲时间百分比
    wa   等待输入输出的进程占用CPU时间百分比
    st   虚拟机等待真实物理机CPU资源的时间
```

选项`-m`显示slab信息
选项`-s`显示各种内存计数器及其信息
选项`-d`显示磁盘I/O信息
选项`-p device`显示设备分区详细I/O信息
同`iostat`一样也支持按频率打印次数
### 11、`mpstat`显示CPU相关信息

```sh
mpstat [options] [interval [count]]
```

显示信息和`top`命令相似

```sh
[root@centos7 ~]# mpstat 1 2
Linux 3.10.0-327.el7.x86_64 (centos7)   2016年11月30日  _x86_64_        (8 CPU)

09时18分19秒  CPU    %usr   %nice    %sys %iowait    %irq   %soft  %steal  %guest  %gnice   %idle
09时18分20秒  all    0.12    0.00    0.00    0.00    0.00    0.00    0.00    0.00    0.00   99.88
09时18分21秒  all    0.12    0.00    0.12    0.00    0.00    0.00    0.00    0.00    0.00   99.75
平均时间:  all    0.12    0.00    0.06    0.00    0.00    0.00    0.00    0.00    0.00   99.81
```

选项`-A`显示所有CPU及中断信息相当于执行`mpstat -I ALL -P ALL`选项`-I { SUM | CPU | SCPU | ALL }`显示中断信息
选项`-P { cpu [,...] | ON | ALL }`显示CPU信息

```sh
[root@centos7 ~]# mpstat -P 3,5
Linux 3.10.0-327.el7.x86_64 (centos7)   2016年11月30日  _x86_64_        (8 CPU)

09时29分03秒  CPU    %usr   %nice    %sys %iowait    %irq   %soft  %steal  %guest  %gnice   %idle
09时29分03秒    3    0.15    0.00    0.04    0.00    0.00    0.00    0.00    0.00    0.00   99.81
09时29分03秒    5    0.11    0.00    0.03    0.00    0.00    0.00    0.00    0.00    0.00   99.86
```

本文简单介绍了linux中进程和内存的相关命令，进程和内存在计算机操作系统中非常重要，涉及到的内容也非常多，这里就不做展开了。

[0]: https://segmentfault.com/a/1190000007296066
# CentOS下查看当前系统情况的命令汇总

 时间 2017-09-08 14:21:00  

原文[https://lnmp.ymanz.com/linux/86.html][1]

<font face=微软雅黑>

在Linux下获取当前系统情况，这是在终端维护服务器的时候一个不可或缺的信息的，今天明月就收集了一些 CentOS 下查看当前系统的命令汇总，就当是做个学习笔记了。


### uptime

Linux uptime命令主要用于获取主机运行时间和查询linux系统负载等信息。

    uptime
    
    10:19:04 up 257 days, 18:56, 12 users, load average: 2.10, 2.10,2.09

#### 显示内容说明

    10:19:04 //系统当前时间 

    up 257 days, 18:56 //主机已运行时间,时间越大，说明你的机器越稳定。 

    12 user //用户连接数，是总连接数而不是用户数 

    load average // 系统平均负载，统计最近1，5，15分钟的系统平均负载 

#### 那么什么是系统平均负载呢？

系统平均负载是指在特定时间间隔内运行队列中的平均进程数。

如果每个CPU内核的当前活动进程数不大于3的话，那么系统的性能是良好的。

如果每个CPU内核的任务数大于5，那么这台机器的性能有严重问题。

如果你的linux主机是1个双核CPU的话，当Load Average 为6的时候说明机器已经被充分使用了。

1可以被认为是最优的负载值。负载是会随着系统不同改变得。

单CPU系统1-3和SMP系统6-10都是可能接受的。

    cat /proc/loadavg
    
    # cat /proc/loadavg
    
    0.00 0.01 0.05 2/384 4482
    0.00 0.01 0.05 #表示最近1分钟,5分钟,15分钟 系统的平均负载; 系统负载越高,代表CPU越繁忙;

2/384 #2代表此时运行队列中的进程个数;384 代表系统中进程的总数

4482 #代表到此为止创建的最后一个进程的ID.

### w

    w
    
    02:14:34 up 126 days, 13:08, 2 users, load average: 0.00, 0.01, 0.05
    USER TTY FROM LOGIN@ IDLE JCPU PCPU WHAT
    root tty1 - 29Jul16 114days 0.63s 0.30s -bash
    ceshi pts/0 118.247.5.122 02:03 0.00s 0.00s 0.00s w

#### 显示内容说明

`USER` :用户名 

`TTY` :录后系统分配的终端号 

`FROM` : 远程主机名(即从哪儿登录来的) 

`LOGIN@` :何时登录 

`IDLE` :空闲了多长时间，表示用户闲置的时间。 

`JCPU` :和该终端（tty）连接的所有进程占用的时间，这个时间里并不包括过去的后台作业时间，但却包括当前正在运行的后台作业所占用的时间 

`PCPU` :指当前进程（即在WHAT项中显示的进程）所占用的时间 

`WHAT` :当前正在运行进程的命令行 

### tload

    tload
    
    0.23, 0.32, 0.45

#### 显示内容说明

平均负载:0.23, 0.32, 0.45 表示最近1分钟,5分钟,15分钟的系统平均负载.

### top

    top
    
    top - 21:23:53 up 40 min,  2 users,  load average: 0.19, 0.35, 0.47
            Tasks: 255 total,   1 running, 253 sleeping,   0 stopped,   1 zombie
            %Cpu(s):  1.1 us,  0.3 sy,  0.0 ni, 98.6 id,  0.0 wa,  0.0 hi,  0.0 si,  0.0 st
            KiB Mem:   4022756 total,  2130488 used,  1892268 free,   192608 buffers
            KiB Swap: 19999740 total,        0 used, 19999740 free.   919724 cached Mem

#### 显示内容说明

第一行:时间为:21:23:53; 已经运行了 40min; 当前在线用户:2个; 平均负载:0.19, 0.35, 0.47 表示最近1分钟,5分钟,15分钟的系统平均负载.

第二行:进程总数:255 正在运行进程数:1 睡眠进程数:253 停止的进程数:0 僵尸进程数:1

第三行:用户空间占用CPU百分比: 1.1% 内核空间占用CPU百分比:0.3% 用户进程空间内改变过优先级的进程占用CPU百分比:0.0% 空闲CPU百分比:0.0 等待输入输出的CPU时间百分比:0.0 CPU服务软中断所耗费的时间总额:0.0% StealTime:0.0%

第四行: 物理内存总量:4022756 使用的物理内存总量:2130488 空闲内存总量:1892268 用作内核缓存的内存量:192608

第五行: 交换区总量:19999740 使用的交换区总量:0 空闲交换区总量:19999740 缓冲的交换区总量:919724

第六行: 进程ID、进程所有者、优先级、nice值，负值表示高优先级，正值表示低优先级、进程使用的虚拟内存总量、进程使用的、未被换出的物理内存大小、共享内存大小、进程状态、上次更新到现在的CPU时间占用百分比、进程使用的物理内存百分比、进程使用CPU总时间、命令名、命令行

### 查看磁盘 df

    [root@tbtravel ~]# df  
        文件系统               1K-块        已用     可用 已用% 挂载点  
        /dev/mapper/VolGroup00-LogVol00  
                              26345340  18485276   6500192  74% /  
        /dev/sda1               101086     12354     83513  13% /boot  
        tmpfs                  1037512         0   1037512   0% /dev/shm

### 查看内存 free和vmstat

    [root@tbtravel ~]# free  
                     total       used       free     shared    buffers     cached  
        Mem:       2075024    1500448     574576          0     184772     865532  
        -/+ buffers/cache:     450144    1624880  
        Swap:      4128760          0    4128760  
        [root@tbtravel ~]# vmstat  
        procs -----------memory---------- ---swap-- -----io---- --system-- -----cpu------  
         r  b   swpd   free   buff  cache   si   so    bi    bo   in   cs us sy id wa st  
         0  0      0 574576 184772 865532    0    0     0     3    2    4  0  0 100  0  0

### 查看cpu cat /proc/cpuinfo

    [root@VM_114_93_centos ~]# cat /proc/cpuinfo 
    processor   : 0
    vendor_id   : GenuineIntel
    cpu family  : 6
    model       : 63
    model name  : Intel(R) Xeon(R) CPU E5-26xx v3
    stepping    : 2
    microcode   : 0x1
    cpu MHz     : 2294.686
    cache size  : 4096 KB
    physical id : 0
    siblings    : 1
    core id     : 0
    cpu cores   : 1
    apicid      : 0
    initial apicid  : 0
    fpu     : yes
    fpu_exception   : yes
    cpuid level : 13
    wp      : yes
    flags       : fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ss ht syscall nx lm constant_tsc rep_good nopl eagerfpu pni pclmulqdq ssse3 fma cx16 pcid sse4_1 sse4_2 x2apic movbe popcnt tsc_deadline_timer aes xsave avx f16c rdrand hypervisor lahf_lm abm xsaveopt
    bogomips    : 4589.37
    clflush size    : 64
    cache_alignment : 64
    address sizes   : 40 bits physical, 48 bits virtual
    power management:

### 查看系统内存 cat /proc/meminfo

    [root@VM_114_93_centos ~]# cat /proc/meminfo 
    MemTotal:        1016904 kB
    MemFree:           88596 kB
    MemAvailable:     467012 kB
    Buffers:          123200 kB
    Cached:           345576 kB
    SwapCached:            0 kB
    Active:           609328 kB
    Inactive:         194932 kB
    Active(anon):     338728 kB
    Inactive(anon):     9568 kB
    Active(file):     270600 kB
    Inactive(file):   185364 kB
    Unevictable:           0 kB
    Mlocked:               0 kB
    SwapTotal:             0 kB
    SwapFree:              0 kB
    Dirty:                92 kB
    Writeback:             0 kB
    AnonPages:        335516 kB
    Mapped:            29768 kB
    Shmem:             12812 kB
    Slab:              88420 kB
    SReclaimable:      70152 kB
    SUnreclaim:        18268 kB
    KernelStack:        3216 kB
    PageTables:         7984 kB
    NFS_Unstable:          0 kB
    Bounce:                0 kB
    WritebackTmp:          0 kB
    CommitLimit:      508452 kB
    Committed_AS:    1409228 kB
    VmallocTotal:   34359738367 kB
    VmallocUsed:        9904 kB
    VmallocChunk:   34359711744 kB
    HardwareCorrupted:     0 kB
    AnonHugePages:     16384 kB
    HugePages_Total:       0
    HugePages_Free:        0
    HugePages_Rsvd:        0
    HugePages_Surp:        0
    Hugepagesize:       2048 kB
    DirectMap4k:       61432 kB
    DirectMap2M:      987136 kB

### du

显示每个文件和目录的磁盘使用空间。

`du` 选项 

显示指定文件所占空间

#### 命令示例：

    [root@VM_114_93_centos www]# du -h  index.php 
    4.0K    index.php

#### 命令参数：

`-a` 或 `-all` 显示目录中个别文件的大小。 

`-b` 或 `-bytes` 显示目录或文件大小时，以byte为单位。 

`-c` 或 `–total` 除了显示个别目录或文件的大小外，同时也显示所有目录或文件的总和。 

`-k` 或 `–kilobytes` 以KB(1024bytes)为单位输出。 

`-m` 或 `–megabytes` 以MB为单位输出。 

`-s` 或 `–summarize` 仅显示总计，只列出最后加总的值。 

`-h` 或 `–human-readable` 以K，M，G为单位，提高信息的可读性。 

`-x` 或 `–one-file-xystem` 以一开始处理时的文件系统为准，若遇上其它不同的文件系统目录则略过。 

`-L<符号链接>` 或 `–dereference<符号链接>` 显示选项中所指定符号链接的源文件大小。 

`-S` 或 `–separate-dirs` 显示个别目录的大小时，并不含其子目录的大小。 

`-X<文件>` 或 `–exclude-from=<文件>` 在<文件>指定目录或文件。 

`–exclude=<目录或文件>` 略过指定的目录或文件。 

`-D` 或 `–dereference-args` 显示指定符号链接的源文件大小。 

`-H` 或 `–si` 与-h参数相同，但是K，M，G是以1000为换算单位。 

`-l` 或 `–count-links` 重复计算硬件链接的文件。 

具体关于 du 和 df 命令的使用，请参考 Linux du 命令和 df 命令区别 

总体感觉这算是比较全面的了，不过还有一个 `top` 命令没有列出来，主要是考虑到 `top` 有一定的复杂性，并且实时性更强，这个我们可以留到以后再详述了！

</font>


[1]: https://lnmp.ymanz.com/linux/86.html

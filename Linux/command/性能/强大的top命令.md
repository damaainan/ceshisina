## Linux中强大的top命令

来源：[https://segmentfault.com/a/1190000003075024](https://segmentfault.com/a/1190000003075024)

原文链接：[http://tabalt.net/blog/linux-...][1]

top命令算是最直观、好用的查看服务器负载的命令了。它实时动态刷新显示服务器状态信息，且可以通过交互式命令自定义显示内容，非常强大。

在终端中输入`top`，回车后会显示如下内容：

```
top - 21:48:39 up  8:57,  2 users,  load average: 0.36, 0.24, 0.14
Tasks: 322 total,   2 running, 320 sleeping,   0 stopped,   0 zombie
%Cpu(s):  5.0 us,  1.7 sy,  0.0 ni, 93.0 id,  0.0 wa,  0.3 hi,  0.0 si,  0.0 st
KiB Mem:   1010504 total,   937416 used,    73088 free,    23708 buffers
KiB Swap:  1046524 total,   280708 used,   765816 free.   365556 cached Mem

  PID USER      PR  NI    VIRT    RES    SHR S %CPU %MEM     TIME+ COMMAND      
 8096 root      20   0  320624  38508  21192 S  1.7  3.8   0:41.03 Xorg         
13536 tabalt    20   0  697336 104272  56776 S  1.7 10.3   0:08.29 gnome-langu+ 
 9426 tabalt    20   0 1213228  72976  16860 S  1.0  7.2   2:07.27 compiz       
  197 root      20   0       0      0      0 S  0.3  0.0   0:36.13 kworker/0:2  
 1009 root      20   0  303112   3392   1500 S  0.3  0.3   0:00.93 polkitd      
 9670 tabalt    20   0  325932   4300   2256 S  0.3  0.4   0:40.27 vmtoolsd     
14016 root      25   5   43940   2408   2000 S  0.3  0.2   0:01.12 http         
14149 tabalt    20   0  591180  19504  12820 S  0.3  1.9   0:00.45 gnome-termi+ 
    1 root      20   0   33648   1972    744 S  0.0  0.2   0:01.79 init         
    2 root      20   0       0      0      0 S  0.0  0.0   0:00.00 kthreadd     
    3 root      20   0       0      0      0 S  0.0  0.0   0:02.80 ksoftirqd/0  
    4 root      20   0       0      0      0 S  0.0  0.0   0:00.00 kworker/0:0  
    5 root       0 -20       0      0      0 S  0.0  0.0   0:00.00 kworker/0:0H 
    7 root      20   0       0      0      0 S  0.0  0.0   0:05.55 rcu_sched    
    8 root      20   0       0      0      0 R  0.0  0.0   0:03.43 rcuos/0      
    9 root      20   0       0      0      0 S  0.0  0.0   0:00.00 rcuos/1      
   10 root      20   0       0      0      0 S  0.0  0.0   0:00.00 rcuos/2 

```
### 一、系统信息统计

前五行是系统整体状态的统计信息展示区域。下面分别介绍每一行中的内容：
#### 1、第一行显示服务器概况

如下所示，第一行列出了服务器运行了多长时间，当前有多少个用户登录，服务器的负荷情况等，使用`uptime`命令能获得同样的结果。

```
top - 21:48:39 up  8:57,  2 users,  load average: 0.36, 0.24, 0.14
       /         /        /                \
   当前时间  运行时长   当前登录用户数  平均负载（1分钟、5分钟、15分钟）

```

平均负载的值越小代表系统压力越小，越大则代表系统压力越大。通常，我们会以最后一个数值，也就是15分钟内的平均负载作为参考来评估系统的负载情况。

对于只有单核cpu的系统，`1.0`是该系统所能承受负荷的边界值，大于1.0则有处理需要等待。

一个单核cpu的系统，平均负载的合适值是`0.7`以下。如果负载长期徘徊在1.0，则需要考虑马上处理了。超过1.0的负载，可能会带来非常严重的后果。

当然，多核cpu的系统是在前述值的基础上乘以cpu内核的个数。如对于多核cpu的系统，有N个核则所能承受的边界值为`N.0`。

可以使用如下命令来查看每个处理器的信息：

```
cat /proc/cpuinfo

```

如果只想计算有多少个cpu内核，可以使用如下命令：

```
cat /proc/cpuinfo | grep 'model name' | wc -l

```
#### 2、第二行是进程信息：

```
Tasks: 322 total,   2 running, 320 sleeping,   0 stopped,   0 zombie
        /                /            /             /            /
    进程总数      正运行进程数    睡眠进程数   停止进程数    僵尸进程数

```
#### 3、第三行是CPU信息：

```
%Cpu(s):  
5.0 us      用户空间CPU占比
1.7 sy      内核空间CPU占比
0.0 ni      用户进程空间改过优先级的进程CPU占比
93.0 id     空闲CPU占比
0.0 wa      待输入输出CPU占比
0.3 hi      硬中断（Hardware IRQ）CPU占比
0.0 si      软中断（Software Interrupts）CPU占比
0.0 st      - 

```
#### 4、第四行是内存信息：

```
KiB Mem:   1010504 total,   937416 used,    73088 free,    23708 buffers
                /                /                /                /
            物理内存总量      使用中总量        空闲总量        缓存的内存量

```
#### 5、第五行是swap交换分区信息：

```
KiB Swap:  1046524 total,   280708 used,   765816 free,   365556 cached Mem
                /                /                /                /
            交换区总量      使用中总量        空闲总量        缓存的内存量

```
### 二、进程（任务）状态监控

第七行及以下显示了各进程（任务）的状态监控。各列所代表的含义如下：

```
PID         进程id
USER        进程所有者
PR          进程优先级
NI          nice值。负值表示高优先级，正值表示低优先级
VIRT        进程使用的虚拟内存总量，单位kb。VIRT=SWAP+RES
RES         进程使用的、未被换出的物理内存大小，单位kb。RES=CODE+DATA
SHR         共享内存大小，单位kb
S           进程状态。D=不可中断的睡眠状态 R=运行 S=睡眠 T=跟踪/停止 Z=僵尸进程
%CPU        上次更新到现在的CPU时间占用百分比
%MEM        进程使用的物理内存百分比
TIME+       进程使用的CPU时间总计，单位1/100秒
COMMAND     进程名称（命令名/命令行）

```
### 三、与top交互


* 按键`b`打开或关闭 运行中进程的高亮效果

* 按键`x`打开或关闭 排序列的高亮效果

* `shift + >`或`shift + <`可以向右或左改变排序列

* `f`键，可以进入编辑要显示字段的视图，有  号的字段会显示，无  号不显示，可根据页面提示选择或取消字段。


![][0]

原文链接：[http://tabalt.net/blog/linux-...][1]

[1]: http://tabalt.net/blog/linux-top/
[2]: http://tabalt.net/blog/linux-top/
[0]: https://segmentfault.com/img/remote/1460000009593826
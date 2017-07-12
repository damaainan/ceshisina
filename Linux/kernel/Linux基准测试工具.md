# [几款优秀的Linux基准测试工具][0]

 标签： [github][1][测试工具][2][linux][3][benchmark][4][基准][5]

 2017-01-09 23:26  1072人阅读  

 分类：

版权声明：本文为博主原创文章 && 转载请著名出处 @ http://blog.csdn.net/gatieme

 目录

1. [性能基准测试工具][11]
1. [标准的性能基准测试工具][12]
1. [文件 IO 性能基准测试工具][13]
1. [网络性能测试工具][14]
1. [基准测试工具套件][15]
  1. [1 phoronixcom][16]
  1. [2 内核中的Perf][17]
  1. [3 rt-tests][18]

CSDN GitHub [几款优秀的Linux基准测试工具][19][AderXCoding/system/tools/benchmark][20]

  
[![知识共享许可协议](https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-nc-sa/4.0/)  
本作品采用[知识共享署名-非商业性使用-相同方式共享 4.0 国际许可协议][21]进行许可, 转载请注明出处, 谢谢. 

# 1 性能基准测试工具

- - -

基准[测试][22]是指运行计算机程序去评估硬件和软件性能的行为. 硬件基本测试包括评估处理器, 内存, 显卡, 硬盘, 网络等不同组件的性能.

基准测试有两类 : **复合**和**应用**

* **复合基准**对一个硬件执行压力测试, 如连续写入和读取数据. 应用基准则是衡量真实世界应用程序如[数据库][23]和服务器的性能.
* **基准测试软件**可以让系统测试者和用户客观独立的评估硬件性能.

依据其测试类型的不同可以分为 基准性能测试, 文件 I/O 性能测试, 网络性能测试, 以及多功能的性能测试工具

[Linux][24]下有许多优秀的开源[linux][24]基准测试工具, 如Phoronix Test Suite，IOzone，netperf等,

# 2 标准的性能基准测试工具

- - -

benchmark | 描述 
-|-
sysbench | sysbench 是一款开源的多线程性能测试工具, 可以执行 CPU/内存/线程/IO/数据库等方面的性能测试. 简介数据库目前支持 MySQL/Oracle/PostgreSQL 
hackbench | 源码下载地址 [hackbench.c][25], 改进的用于测试调度器性能的 benchmark 工具, 就一个源文件,编译后运行即可, [手册][26] 
unixbench | 一个用于测试unix系统性能的工具，也是一个比较通用的benchmark, 此测试的目的是对类Unix 系统提供一个基本的性能指示, 参见[Linux性能测试工具-UnixBench–安装以及结果分析][27] 
CineBench | 很有说服力的一套CPU和显卡测试系统 
GreekBench | Geekbench测试你的计算机的CPU处理器和内存性能 
LLCbench | (底层表征基准测试 ow-Level Characterization Benchmarks) 是一个基准测试工具，集成了 MPBench, CacheBench, 和 BLASBench 测试方法 
HardInfo | 一个Linux系统信息查看软件. 它可以显示有关的硬件, 软件, 并进行简单的性能基准测试 
GtkPerf | 是一种应用程序设计，测试基于GTK +的性能 

参见

[六款优秀的 Linux 基准测试工具][28]

# 3 文件 I/O 性能基准测试工具

- - -

benchmark | 描述 
-|-
iozone | iozone 是一款 Linux 文件系统性能测试工具. 它可以测 Reiser4, ext3, ext4 
iometer | Iometer 是一个工作在单系统和集群系统上用来衡量和描述 I/O 子系统的工具 
bonnie++ | Bonnie++ 是一个用来测试 UNIX 文件系统性能的测试工具, 主要目的是为了找出系统的性能瓶颈, 其名字来源于作者喜爱的歌手 Bonnie Raitt 
dbench | Dbench和Tbench是用来模拟工业标准的Netbench负载测试工具来评估文件服务器的测试工具 

参见[bonnie++、dbench、iozone工具][29]

# 4 网络性能测试工具

- - -

Netperf 是一种网络性能的测量工具, 主要针对基于 TCP或UDP 的传输

Netperf 是一种网络性能的测量工具, 主要针对基于 TCP 或 UDP 的传输.

Netperf 根据应用的不同, 可以进行不同模式的网络性能测试, 即**批量数据传输(bulk data transfer)模式**和**请求/应答(request/reponse)模式**

Netperf 测试结果所反映的是两个系统之间发送和接受数据的速度和效 率。

Netperf工具是基于C／S模式的。server端是netserver，用来侦听来自client端的连接，client 端是netperf，用来向server发起网络测试。在client与server之间，首先建立一个控制连接，传递有关测试配置的信息，以及测试的结 果；在控制连接建立并传递了测试配置信息以后，client与server之间会再建立一个测试连接，用来来回传递着特殊的流量模式，以测试网络的性能。

# 5 基准测试工具套件

- - -

benchmark | 描述 
-|-
Phoronix | Test Suite 知名评测机构 Phoronix 提供的 linux 平台测试套件 
perf | Linux内核中的系统性能调优工具, Perf Event 是一款随 Linux 内核代码一同发布和维护的性能诊断工具，由内核社区维护和发展。Perf 不仅可以用于应用程序的性能统计分析，也可以应用于内核代码的性能统计和分析。得益于其优秀的体系结构设计，越来越多的新功能被加入 Perf，使其已经成为一个多功能的性能统计工具集 。在第一部分，将介绍 Perf 在应用程序开发上的应用 
rt-tests | “Cyclictest is a high resolution test program, written by User:Tglx, maintained by User:Clark Williams”, 也就是它是一个高精度的测试程序, Cyclictest 是 rt-tests 下的一个测试工具, 也是 rt-tests 下使用最广泛的测试工具, 一般主要用来测试使用内核的延迟, 从而判断内核的实时性. 

## 5.1 phoronix.com

- - -

phoronix.com 是业内一个知名的网站，其经常发布硬件性能测评以及 Linux 系统相关的性能测评, Phoronix Test Suite 为该网站旗下的 linux 平台测试套件, Phoronix 测试套件遵循 GNU GPLv3 协议. Phoronix Test Suite 默认是通过命令行来的进行测试的, 但也可以调用GUI, Phoronix Test Suite 还提供了上传测试结果的服务，也就说你可以把你的测试结果上传在网上，从而可以和别的 Linux 用户测出来的结果进行对比.

## 5.2 内核中的Perf

- - -

Perf 是用来进行软件性能分析的工具.

通过它, 应用程序可以利用 PMU, tracepoint 和内核中的特殊计数器来进行性能统计. 它不但可以分析指定应用程序的性能问题 (per thread), 也可以用来分析内核的性能问题, 当然也可以同时分析应用代码和内核, 从而全面理解应用程序中的性能瓶颈.

最初的时候, 它叫做 Performance counter, 在 2.6.31 中第一次亮相. 此后他成为内核开发最为活跃的一个领域. 在 2.6.32 中它正式改名为 Performance Event, 因为 perf 已不再仅仅作为 PMU 的抽象, 而是能够处理所有的性能相关的事件.

使用 perf, 您可以分析程序运行期间发生的硬件事件. 比如 instructions retired, processor clock cycles 等; 您也可以分析软件事件, 比如 Page Fault 和进程切换.

这使得 Perf 拥有了众多的性能分析能力. 举例来说, 使用 Perf 可以计算每个时钟周期内的指令数, 称为 IPC, IPC 偏低表明代码没有很好地利用 CPU. Perf 还可以对程序进行函数级别的采样, 从而了解程序的性能瓶颈究竟在哪里等等. Perf 还可以替代 strace, 可以添加动态内核 probe 点, 还可以做 benchmark 衡量调度器的好坏.

人们或许会称它为进行性能分析的”瑞士军刀” 和 “倚天剑”.

## 5.3 rt-tests

- - -

cyclictest测试内核的性能, 包括了 hackbench, cyclictest 等多个 benchmark 工具

[Cyclictest的维基主页][30]这么介绍它“Cyclictest is a high resolution test program, written by User:Tglx, maintained by User:Clark Williams ”，也就是它是一个高精度的测试程序，Cyclictest 是 rt-tests 下的一个测试工具，也是rt-tests 下使用最广泛的测试工具，一般主要用来测试使用内核的延迟，从而判断内核的实时性.

参见

[cyclictest 简介以及安装][31]

[cyclictest 的使用][32]

[0]: http://blog.csdn.net/gatieme/article/details/54296440
[1]: http://www.csdn.net/tag/github
[2]: http://www.csdn.net/tag/%e6%b5%8b%e8%af%95%e5%b7%a5%e5%85%b7
[3]: http://www.csdn.net/tag/linux
[4]: http://www.csdn.net/tag/benchmark
[5]: http://www.csdn.net/tag/%e5%9f%ba%e5%87%86
[11]: #t0
[12]: #t1
[13]: #t2
[14]: #t3
[15]: #t4
[16]: #t5
[17]: #t6
[18]: #t7
[19]: http://blog.csdn.net/gatieme
[20]: https://github.com/gatieme/AderXCoding/tree/master/system/tools/benchmark
[21]: http://creativecommons.org/licenses/by-nc-sa/4.0/
[22]: http://lib.csdn.net/base/softwaretest
[23]: http://lib.csdn.net/base/mysql
[24]: http://lib.csdn.net/base/linux
[25]: http://people.redhat.com/mingo/cfs-scheduler/tools/hackbench.c
[26]: http://man.cx/hackbench
[27]: http://blog.csdn.net/gatieme/article/details/50912910
[28]: http://www.oschina.net/news/28468/6-linux-benchmark-tools
[29]: http://blog.csdn.net/adaptiver/article/details/7013150
[30]: https://rt.wiki.kernel.org/index.php/Cyclictest
[31]: http://blog.csdn.net/longerzone/article/details/16897655
[32]: http://blog.csdn.net/ganggexiongqi/article/details/5841347
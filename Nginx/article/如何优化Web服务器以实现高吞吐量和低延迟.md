## [如何优化Web服务器以实现高吞吐量和低延迟](http://geek.csdn.net/news/detail/237188)


> 原文：[Optimizing web servers for high throughput and low latency][0]  
> 作者：Alexey Ivanov   
> 翻译：不二

_译者注：人们更多的是关注软件一类的优化，当负载上来后，发现硬件发挥不出最大性能。服务器出厂时，BIOS 默认达到了性能和能耗之间的良好平衡，以适应一般环境，然而在不同的环境下可能需要对服务器进行优化，以获得最大的吞吐量或最低的延迟，本文全面讲述如何在硬件层面优化web服务器，请看译文。_

这是对2017年9月6日在NginxConf 2017年演讲内容的延伸。作为Dropbox Traffice团队的网络可靠性工程师（SRE，Site Reliability Engineer ），我负责公司的边缘网络，包括它的可靠性、性能和效率。[Dropbox 边缘网络][1]是一个基于nginx的代理层，用于处理敏感的元数据事务和高吞吐量数据传输。在处理数以万计敏感事务的系统中，通过TCP/IP协议和内核，从驱动程序、中断到库、应用级调优，整个代理堆栈中都存在效率/性能优化。

### 免责声明

这篇文章将讨论如何调优web服务器和代理服务器，并采用科学的方法，将它们逐一应用测量效果，最终确定是否真的对所处的环境有用。

这不是一篇关于Linux性能的文章，尽管它大量引用了bcc工具、eBPF和perf，但不要误以为是使用性能分析工具的全面指南。如果你想了解更多关于他们的信息，可以通过[Brendan Gregg的博客][2]阅读。

这也不是一篇关于浏览器性能的文章。当介绍与延迟相关的优化时，会涉及到客户端的性能，但只是短暂的。如果你想知道更多这方面的，可以阅读Ilya Grigorik的[高性能浏览器网络][3]。

最后，这也不是关于TLS（传输层安全协议）的最佳实践编译，尽管会提到TLS库及其设置，但那是为了评估每种方法的性能和安全性。可以使用[Qualys SSL测试][4]，以验证网络终端与当前的最佳实践，如果想了解更多关于TLS的信息，请考虑阅读[Feisty Duck Bulletproof TLS Newsletter][5]。

### 文章的结构

文章将讨论系统不同层级的效率/性能优化。从硬件和驱动程序的最低级别开始：这些调优可以应用到几乎任何高负载服务器上。然后将转移到linux内核及其TCP/IP协议栈：这些是在任何一个TCP-heavy盒子上尝试的旋钮。最后将讨论库和应用程序级别的调优，它们主要适用于一般的web服务器和nginx服务器。

对于每一个潜在的优化领域，将尝试提供一些关于延迟/吞吐量权衡（如果有的话）的背景知识，以及监控指导方针，最后建议对不同的工作负载作调整。

### 硬件方面

#### CPU

对于良好的非对称RSA/EC性能，具有至少_AVX2(AVX2 in /proc/ cpuinfo)_支持的处理器，最好是具有[大型整数算术能力][6]硬件(bmi和adx)的处理器。而对于对称的情况，应该找AES ciphers和AVX512的_ChaCha+Poly_。英特尔通过OpenSSL 1.0.2对不同的硬件产品进行了[性能比较][7]，说明了这些硬件卸载的影响。

CPU延迟敏感的用例，如路由选择，减少NUMA的节点和禁用HT会有帮助。拥有更多内核意味着高吞吐量的任务会处理得更好，并且将从超线程（除非它们是缓存绑定）中受益，而且通常不会过多地关心NUMA。

具体地说，如果走英特尔的路线，至少需要haswell或broadwell架构的处理器与合适的Skylake CPUs。如果采用AMD，那么EPYC相当不错。

#### NIC（网络适配器）

至少需要10G网卡，最好是25G。如果想要通过TLS单个服务器达到更大的传输，这里所描述的调优是不够的，可能需要将TLS构建到内核级别（例如[FreeBSD][8]，[Linux][9]）。

在软件方面，应该寻找具有活动邮件列表和用户社区的开源驱动程序。如果涉及到调试与驱动相关的问题，这将是非常重要的。

#### 内存

选择的经验法则是，延迟敏感的任务需要更快的内存，而吞吐量敏感的任务需要更多的内存。

#### 硬盘

这取决于缓冲/缓存的需求，如果要缓存大量的内容，应该选择基于flash的存储。有些甚至采用了专门的flash友好文件系统（通常是日志结构的），但是它们并不总是比普通的ext4/xfs性能更好。

无论如何，注意不要因为忘记启用TRIM或更新固件而烧穿了flash。

### 操作系统：低水平

#### 固件

保持固件最新，以避免痛苦和冗长的故障排除会话。尽量保持最新的CPU微码，主板，NICs和ssd固件。这并不意味着要一直花钱，经验法则是把固件更新到上一个版本就行，除非它在最新版本中修复了关键错误，否则只要不要太落后就行。

#### 驱动

更新规则和固件更新差不多，尝试保持最新。这里需要注意的是，如果可能的话，尝试将内核升级与驱动程序更新解耦。例如，可以用DKMS打包驱动程序，或者为使用的所有内核版本预编译驱动程序。这样当更新内核出现故障时不需要考虑驱动程序。

#### CPU

在Ubuntu/Debian中，可以使用一些实用工具安装linux工具包，但是现在只使用_cpupower_、_turbostat_和_x86_energy_perf_policy_即可。为了验证cpu相关的优化，可以通过常用的负载生成工具对软件进行压力测试（例如，Yandex使用[Yandex.Tank][10]）。下面是有一份来自NginxConf开发者的演示，介绍了_nginx加载测试_的最佳实践：“[nginx性能测试][11]”。

#### cpupower

使用这个工具比crawling /proc/更容易。要查看有关处理器及其频率调控器的信息，请运行以下代码。

    $ cpupower frequency-info
    ...
      driver: intel_pstate
      ...
      available cpufreq governors: performance powersave
      ...            
      The governor "performance" may decide which speed to use
      ...
      boost state support:
        Supported: yes
        Active: yes

检查是否启用了Turbo Boost，对于Intel cpu确保运行的是_intel_pstate_，而不是_acpi-cpufreq_或者_pcc-cpufreq_。如果坚持使用_acpic-cpufreq_，那么应该升级内核，或者如果不能升级，确保使用的是performance调控器。在使用_intel_pstate_运行时，powersave调控器也应该运行良好，但这一步需要自己去验证。

想看看空载时CPU到底发生了什么，可以使用turbostat直接查看处理器的MSRs，获取电源、频率和空闲状态信息。

    # turbostat --debug -P
    ... Avg_MHz Busy% ... CPU%c1 CPU%c3 CPU%c6 ... Pkg%pc2 Pkg%pc3 Pkg%pc6 ...

这里可以看到实际的CPU频率（是的，/proc/cpuinfo欺骗了你），[以及核心/包空闲状态][12]。   
如果使用intel_pstate驱动程序，CPU的空闲时间会比预想的要多，可以采取以下措施。

* 设置性能调节。
* 设置_x86_energy_perf_policy_性能。


或者，对于非常延迟的关键任务，还可以这样做。

* 采用_[/dev/cpu_dma_latency][13]_接口。
* 对于UDP流量，使用[busy-polling][14]方法。


更多关于处理器电源管理的信息，可以在2015年的LinuxCon Europe上由“Linux内核”的Intel开源技术中心发表的“[Linux内核中的平衡能力和性能][15]”文章中了解到。

#### CPU亲和力

还可以通过在每个线程/流程上应用CPU关联性来降低延迟，例如nginx，它具有_worker_cpu_affinity_指令，可以自动将每个web服务器进程绑定到它自己的核心。这可以消除CPU迁移，减少缓存遗漏和页面错误，并稍微增加每个周期的指令数。所有这些都可以通过perf stat来验证。

遗憾的是，启用亲和性也会增加等待空闲CPU的时间，从而对性能产生负面影响。可以通过在pid上运行runqlat来监控。

    usecs               : count     distribution
        0 -> 1          : 819      |                                        |
        2 -> 3          : 58888    |******************************          |
        4 -> 7          : 77984    |****************************************|
        8 -> 15         : 10529    |*****                                   |
       16 -> 31         : 4853     |**                                      |
       ...
     4096 -> 8191       : 34       |                                        |
     8192 -> 16383      : 39       |                                        |
    16384 -> 32767      : 17       |                                        |

如果看到多毫秒的延迟，那么除了nginx本身之外，服务器可能在处理其他很多的事，一旦关联将增加延迟，而不是减少。

#### 内存

所有的mm/tunings通常都是非常具体的工作流，可以推荐的方法很少。

* [确定有所帮助的话][16]，设置THP并进行启用，否则会适得其反[得到一个数量级的减速][17]，而不是20%的目标延迟改进。
* 针对单个NUMA节点设置_vm.zone_reclaim_mode_为0.## NUMA。


现代的CPU实际上包含多个独立的CPU，它们通过高速互连和共享资源，从HT核心的L1缓存开始，经过包内的L3高速缓存，再到内存和套接字内PCIe链路。这基本上就组成了NUMA，它具有快速互连的多个执行和存储单元。

对于NUMA的全面概述及其影响，可以参考Frank Denneman的“[NUMA深入分析系列][18]”。

对于NUMA长话短说，可以进行以下选择:

* 选择忽略，在BIOS中禁用它，或者在_numactl –interleave=all_下运行软件，可以得到一般的性能。
* 通过使用单节点服务器来替换它，[就像Facebook和OCP Yosemite平台一样][19]。
* 选择接受，并优化用户和内核空间中的CPU/内存。


现在讨论第三种选择，因为前两种方法并没有太多的优化。

要合理地使用NUMA需要将每个NUMA节点视为一个单独的服务器，对此应该首先检查拓扑结构，可以通过_numactl –hardware_查看。

    $ numactl --hardware
    available: 4 nodes (0-3)
    node 0 cpus: 0 1 2 3 16 17 18 19
    node 0 size: 32149 MB
    node 1 cpus: 4 5 6 7 20 21 22 23
    node 1 size: 32213 MB
    node 2 cpus: 8 9 10 11 24 25 26 27
    node 2 size: 0 MB
    node 3 cpus: 12 13 14 15 28 29 30 31
    node 3 size: 0 MB
    node distances:
    node   0   1   2   3
      0:  10  16  16  16
      1:  16  10  16  16
      2:  16  16  10  16
      3:  16  16  16  10

考虑的因素有：

* 节点的数量。
* 每个节点的内存大小。
* 每个节点的cpu数量。
* 节点之间的距离。


这是一个非常糟糕的用例，因为它有4个节点同时没有附加内存的节点。在不牺牲系统一半内核的情况下，很难把每个节点都当作独立的服务器来处理。

可以通过使用_numastat_来验证:

    $ numastat -n -c
                      Node 0   Node 1 Node 2 Node 3    Total
                    -------- -------- ------ ------ --------
    Numa_Hit        26833500 11885723      0      0 38719223
    Numa_Miss          18672  8561876      0      0  8580548
    Numa_Foreign     8561876    18672      0      0  8580548
    Interleave_Hit    392066   553771      0      0   945836
    Local_Node       8222745 11507968      0      0 19730712
    Other_Node      18629427  8939632      0      0 27569060

也可以以_/proc/meminfo_格式要求numastat输出每个节点的内存使用统计信息。

    $ numastat -m -c
                     Node 0 Node 1 Node 2 Node 3 Total
                     ------ ------ ------ ------ -----
    MemTotal          32150  32214      0      0 64363
    MemFree             462   5793      0      0  6255
    MemUsed           31688  26421      0      0 58109
    Active            16021   8588      0      0 24608
    Inactive          13436  16121      0      0 29557
    Active(anon)       1193    970      0      0  2163
    Inactive(anon)      121    108      0      0   229
    Active(file)      14828   7618      0      0 22446
    Inactive(file)    13315  16013      0      0 29327
    ...
    FilePages         28498  23957      0      0 52454
    Mapped              131    130      0      0   261
    AnonPages           962    757      0      0  1718
    Shmem               355    323      0      0   678
    KernelStack          10      5      0      0    16

现在看一个更简单的拓扑图。

    $ numactl --hardware
    available: 2 nodes (0-1)
    node 0 cpus: 0 1 2 3 4 5 6 7 16 17 18 19 20 21 22 23
    node 0 size: 46967 MB
    node 1 cpus: 8 9 10 11 12 13 14 15 24 25 26 27 28 29 30 31
    node 1 size: 48355 MB

由于节点基本是对称的，可以通过_numactl –cpunodebind=X –membind=X_将应用程序的实例绑定到对应的NUMA节点，然后在另一个端口上公开它，这样就可以通过使用两个节点获得更好的吞吐量，通过保留内存位置获取更好的延迟。

通过对内存操作的延迟来验证NUMA的放置效率，例如使用bcc的funclatency来度量内存重操作的延迟，像memmove。

在内核方面，使用_perf stat_来观察效率，并查询相应的内存和调度器事件。

    # perf stat -e sched:sched_stick_numa,sched:sched_move_numa,sched:sched_swap_numa,migrate:mm_migrate_pages,minor-faults -p PID
    ...
                     1      sched:sched_stick_numa
                     3      sched:sched_move_numa
                    41      sched:sched_swap_numa
                 5,239      migrate:mm_migrate_pages
                50,161      minor-faults

对network-heavy工作负载，最后一点与numa相关的优化来自于一个事实，即网络适配器是一个PCIe设备，每个设备都绑定到自己的numa节点，因此在与网络通信时，一些cpu的延迟时间会较低。讨论NIC和CPU亲和力时将涉及到优化可以应用到哪些地方，现在先说一说PCIe。

#### PCIe

通常情况下，除非出现某种硬件故障，否则不需要深入地进行[PCIe故障排除][20]。因此只需创建“链接宽度”、“链接速度”，并尽可能为PCIe设备创建_RxErr/BadTLP_提醒。当硬件损坏或PCIe协商失败，这将节省故障排除时间。可以使用lspci达到目的。

    # lspci -s 0a:00.0 -vvv
    ...
    LnkCap: Port #0, Speed 8GT/s, Width x8, ASPM L1, Exit Latency L0s <2us, L1 <16us
    LnkSta: Speed 8GT/s, Width x8, TrErr- Train- SlotClk+ DLActive- BWMgmt- ABWMgmt-
    ...
    Capabilities: [100 v2] Advanced Error Reporting
    UESta:  DLP- SDES- TLP- FCP- CmpltTO- CmpltAbrt- ...
    UEMsk:  DLP- SDES- TLP- FCP- CmpltTO- CmpltAbrt- ...
    UESvrt: DLP+ SDES+ TLP- FCP+ CmpltTO- CmpltAbrt- ...
    CESta:  RxErr- BadTLP- BadDLLP- Rollover- Timeout- NonFatalErr-
    CEMsk:  RxErr- BadTLP- BadDLLP- Rollover- Timeout- NonFatalErr+

PCIe可能会成为一个瓶颈，如果存在多个高速设备竞争带宽（例如将快速网络与快速存储结合起来），那么可能需要在cpu之间物理地切分PCI总线设备以获得最大的吞吐量。

  
![image][21]

  
图片来源：

[https://en.wikipedia.org/wiki/PCI_Express History_and_revisions][22]

。

Mellanox网站上有篇文章“[理解PCIe配置的最大性能][23]”，更深入的介绍了PCIe配置，对于高速传输中出现卡顿和操作系统之间的数据包丢失会有所帮助。

英特尔公司表示，有时PCIe的权力管理（ASPM）可能导致更高的延迟，从而导致更高的包损失，但可以通过向内核cmdline添加_pcie_aspm=off_禁用它。

#### NIC

开始之前需要说明的是，Intel和Mellanox都有自己的性能调优指南，无论选择哪种供应商，都有助于了解它们。同时，驱动程序通常也有自己的README和一套实用工具。

下一个需要阅读的是操作系统的手册，例如[红帽企业版Linux系统网络性能调优指南][24]，它提到了下面的大多数优化，甚至更多。

Cloudflare也提供了一篇[关于在博客上调优网络堆栈的一部分的文章][25]，尽管它主要是针对低延迟的用例。

在优化网络适配器时，ethtool将提供很好的帮助。

注意，如果正在使用一个最新的内核，应该在userland中添加一些部分，比如对于网络操作，可能需要最新的：_ethtool,iproute2_，以及_iptables/nftables_包。

可以通过_ethtool -s_了解网卡信息。

    $ ethtool -S eth0 | egrep 'miss|over|drop|lost|fifo'
         rx_dropped: 0
         tx_dropped: 0
         port.rx_dropped: 0
         port.tx_dropped_link_down: 0
         port.rx_oversize: 0
         port.arq_overflows: 0

与NIC制造商进行详细的统计描述，例如Mellanox为他们提供了一个[专门的wiki页面][26]。

内核方面将看到_/proc/interrupts、/proc/softirqs和/proc/net/softnet_stat_。这里有两个有用的bcc工具，hardirqs和softirqs。优化网络的目标是在没有数据包丢失的情况下，对系统进行优化，直至达到最小的CPU使用量。

#### 中断亲和力

调优通常从处理器之间的扩展中断开始，具体怎么做取决于工作量。

* 为了获取最大吞吐量，可以在系统中的所有numa节点上分发中断。
* 为了得到最低延迟，可以将中断限制为单个numa节点。要做到这一点，可能需要减少队列的数量，以适应单个节点（这通常意味着用_ethtool -L_将它们的数量减少一半）。


供应商通常提供脚本以达到目的，例如英特尔有_set_irq_affinity_。

#### 环形缓冲区大小

网卡与内核交换信息一般是通过一种叫做“环”的数据结构来完成的，该数据结构借助ethtool -g可以查看该“环”的当前/最大尺寸。

    $ ethtool -g eth0
    Ring parameters for eth0:
    Pre-set maximums:
    RX:                4096
    TX:                4096
    Current hardware settings:
    RX:                4096
    TX:                4096

可以在预先设置的最大值与-G之间作调整，一般来说越大越好（特别是如果使用中断合并时），因为它将给予更多的保护来防止突发事件和内核内的间断，因此降低了由于没有缓冲区空间/错过中断而减少的数据包数量。但有几点需要说明:

* 在旧的内核，或者没有BQL支持的驱动程序中，设置很高的值意味着tx-side更高的缓存过满。
* 更大的缓冲区也会[增加缓存的压力][27]。


#### 合并

中断合并允许通过在一个中断中聚合多个事件来延迟通知内核的新事件。当前设置可以通过_ethtool -c_来查看。

    $ ethtool -c eth0
    Coalesce parameters for eth0:
    ...
    rx-usecs: 50
    tx-usecs: 50

可以使用静态限制严格限制每秒中断的最大中断数，或者依赖硬件基于吞吐量[自动调整中断率][28]。

启用合并（使用 -c）将增加延迟，并可能引发包丢失，所以需要避免它对延迟敏感。另一方面，完全禁用它可能导致中断节流，从而影响性能。

#### 卸载

现代网卡比较智能，可以将大量的工作转移到硬件或仿真驱动程序上。

所有可能的卸载都可以通过_ethtool -k_查看。

    $ ethtool -k eth0
    Features for eth0:
    ...
    tcp-segmentation-offload: on
    generic-segmentation-offload: on
    generic-receive-offload: on
    large-receive-offload: off [fixed]

在输出中，所有不可调的卸载都采用[fixed]后缀标记。

[关于它们有很多讨论][29]，但这里有一些经验法则。

* 不要启用LRO，使用GRO代替。
* 要小心使用TSO，因为它高度依赖于驱动/固件的质量。
* 不要在旧的内核中启用TSO/GSO，因为它可能会导致严重的缓存过满。所有现代NICs都优化了多核硬件，因此他们将包内部拆分为虚拟队列（通常是一个CPU）。当它在硬件中完成时，它被称为RSS，当操作系统负责交叉cpu的负载平衡包时，它被称为RPS（与TX-counterpart对应的称为XPS)。当操作系统试图变得智能并路由到当前正在处理该套接字的cpu时，它被称为RFS。当硬件实现该功能时，它被称为“加速RFS”或简称“aRFS”。


以下是生产中的一些最佳实践。

* 如果正在使用最新的25 G+硬件，它可能有足够的队列和一个巨大的间接表，以便能够在所有的内核中提供RSS。一些老的NICs只使用前16个cpu。
* 以下情况可以尝试启用RPS:


  * 当有更多的cpu，而不是硬件队列，希望为吞吐量牺牲延迟。
  * 当使用的是内部隧道(如GRE / IPinIP)，NIC不能RSS。
* 如果CPU相当旧且没有x2APIC，则不要启用RPS。
* 通过XPS将每个CPU绑定到它自己的TX队列通常是个好主意。
* RFS的有效性很大程度上取决于工作负载，以及是否将CPU的相关性应用于它。


#### 导流器和ATR

启用导流器（或英特尔术语中的fdir）[在应用程序的目标路由模式中是默认操作][30]，该应用程序通过取样包和转向系统来实现aRFS，并将其控制在可能被处理的核心位置。它的统计数据也可以通过_ethtool - s:$ ethtool - s eth0 |egrep “fdir” port.fdir_flush_cnt:0_实现。

尽管英特尔声称fdir[在某些情况下提高了性能][31]，但外部研究表明它也[引发了最多1%的包重新排序][32]，这对TCP性能有很大的损害。因此可以测试一下，看看FD是否对工作负载有帮助，同时要注意TCPOFOQueue计数器。

### 操作系统：网络栈

对于Linux网络堆栈的调优，有无数的书籍、视频和教程。可悲的是，大量的“sysctl.conf cargo-culting”随之而来，即使最近的内核版本不需要像10年前那样多的调优，而且大多数新的TCP/IP特性在默认情况下都是启用和已调优的，人们仍然在复制旧的已经使用了2.6.18/2.6.32内核的sysctls.conf。

为了验证与网络相关的优化效果，需要：

* 通过/ proc/net/snmp和/ proc/net/netstat收集系统范围的TCP指标。
* 合并从_ss -n –extended –info_，或者从网页服务器内部调用_getsockopt([TCP_INFO][33])/getsockopt([TCP_CC_INFO][34])_获取的每个连接的指标。
* 采样TCP流的tcptrace(1)。
* 从应用程序/浏览器中分析RUM指标。


对于关于网络优化的信息来源，个人喜欢由CDN-folks进行的会议讨论，因为他们通常知道在做什么，比如[LinuxCon在澳大利亚的快速发展][35]。听Linux内核开发人员关于网络的说法也是很有启发性的，例如[netdevconf对话][36]和[NETCONF记录][37]。

通过PackageCloud深入到Linux网络堆栈中时以下需要注意，特别是当侧重监视而不是盲目的调优时。

* [监视和调优Linux网络堆栈：接收数据][38]
* [监视和调优Linux网络堆栈：发送数据][39]


开始之前再次重申：请升级内核！有大量的新网络堆栈改进，比如关于新的热像：TSO自动调整，FQ，步测，TLP，和机架，但以后更多。通过升级到一个新的内核，将得到一大堆拓展性改进，如删除路由缓存、lockless侦听套接字、SO_REUSEPORT等等。

#### 概述

最近的Linux网络文章中非常显目的是“[让Linux TCP快速运行][40]”。它通过将Linux sender-side TCP堆栈分解为功能块，从而巩固了Linux内核的多年改进。

  
![image][41]

  
#### 公平队列和pacing

公平队列负责改善TCP流之间的公平性和减少行阻塞，这对包的下降率有积极的影响。以拥塞控制的速度设定数据包，以同样的间隔时间间隔来设置数据包，从而进一步减少数据包损失，最终提高吞吐量。

附注一点：在linux中，通过fq qdisc可以获得公平队列和pacing。不要误以为这些是BBR的要求，它们都可以与CUBIC一同使用以减少15-20%的数据包损失，从而提高CCs上损失的吞吐量。只是不要在旧的内核（低于3.19版本）中使用它，因为会结束pacing pure ACKs，并破坏uploads/RPCs。

#### TSO autosizing和TSQ

两种方法都负责限制TCP堆栈中的缓冲，从而降低延迟，且不会牺牲吞吐量。

#### 拥塞控制

CC算法本身设计领域很广，近年来围绕它们进行了大量的活动。其中一些活动被编纂为：_tcp_cdg(CAIA)_、_tcp_nv(Facebook)_和_tcp_bbr(谷歌)_。这里不会太深入地讨论他们的内部工作，先假设所有的人都依赖于延迟的增加而不是数据包的减少。

BBR可以说是所有新的拥塞控制中，文档完整、可测试和实用的。基本思想是建立一个基于包传输速率的网络路径模型，然后执行控制循环，最大限度地提高带宽，同时最小化rtt。这正是代理堆栈中需要的。

来自BBR实验的初步数据显示，文件下载速度有提升。

  
![image][42]

  
在东京的BBR实验6小时：x轴表示时间，y轴表示客户端下载速度。

需要强调的是有观察到所有百分位数的速度都在增长，这不是后端修改引起。通常只会给p90+用户带来好处（互联网连接速度最快的用户），因为其他所有人都是带宽限制的。网络级的调优，比如改变拥塞控制，或者启用FQ /pacing显示用户没有带宽限制，但是他们是“tcp - limited”。

如果想了解更多关于BBR的信息，[APNIC有一个很好的入门级的BBR概述][43]（它与基于损失的拥挤控制的对比）。关于BBR的更深入的信息，可以阅读[bbr-dev邮件列表档案][44]（它在顶部有许多有用的链接）。对拥塞控制感兴趣的，可以关注[网络拥塞控制研究小组][45]的有趣活动。

#### ACK处理和丢失检测

关于拥塞控制已经说了很多，下面再次运行最新的内核讨论一下丢失检测。那些新的启发式方法，比如TLP和RACK，经常被添加到TCP，而FACK和ER等旧的东西正在被淘汰。一旦添加，它们是默认启用的，因此在升级之后不需要调整任何系统设置。

#### 用户空间优先级和HOL

用户空间套接字api提供隐式缓冲，一旦被发送，就无法重新排序块，因此在多路复用场景中（例如HTTP/2），可能导致HOL阻塞，以及h2优先级反转。[TCP_NOTSENT_LOWAT][46]套接字选项（和相应的_net.ipv4.tcp_notsent_lowat sysctl_）被设计用来解决这个问题，它设置了一个阈值，在这个阈值中，套接字会认为自己是可写的（即epoll会对欺骗应用程序）。从而可以解决使用HTTP/2优先级的问题，但它也可能对吞吐量产生负面影响。

#### Sysctls

在谈到网络优化的时候，不能不提到关于sysctls的调优。先从不熟悉的开始。   
- _net.ipv4.tcp_tw_recycle=1_——不要使用它——它已经被NAT的用户破坏了，但是如果升级内核将有所改善。   
- _net.ipv4.tcp_timestamps=0_——不要禁用它们，除非已知所有的副作用，而且可以接受它们。例如，一个不明显的副作用是[可以在syncookie上打开窗口缩放和SACK选项][47]。

对于sysctls需要使用：

* _net.ipv4.tcp_slow_start_after_idle=0_——在空闲后缓慢启动的主要问题是“空闲”被定义为一个RTO，它太小了。
* _net.ipv4.tcp_mtu_probing=1_——有用，[如果与客户之间有ICMP的黑名单][48]（很可能有）。
* _net.ipv4.tcp_rmem net.ipv4.tcp_wmem_——应该调整为适合BDP，[越大并不总是越好][49]。
* _echo 2 > /sys/module/tcp_cubic/parameters/hystart_detect_——如果使用的是fq+cubic，[这可能有助于tcp_cubic较早的开始][50]。


同样值得注意的是，有一份由curl的作者Daniel Stenberg编写的RFC草案，命名为[HTTP的TCP调优][51]，它试图整理所有可能对HTTP有利的系统调优。

### 应用程序级别：中层

#### 工具

就像内核一样，拥有最新的用户空间是非常重要的。从升级工具开始，例如打包最新版本的perf、bcc等。

一旦有了新的工具，就可以适当地调整和观察系统的行为。这一部分主要依赖于通过perf top、on-CPU flamegraphs和来自bcc funclatency的临时直方图进行on-cpu分析。

  
![image][52]

  
#### 编译器工具链

如果想要编译硬件优化的组件，那么拥有一个现代的编译器工具链是必不可少的，这在许多web服务器通常使用的库中也是存在的。

除了性能之外，新的编译器具有新的安全特性（如[-fstack-protector-strong][53]或[SafeStack][54]），这些特性是想在边缘网络中应用的。现代工具链的另一个用例是，当运行测试工具时，杀毒软件（例如AddressSanitizer和friends）编译的是二进制文件。

#### 系统库

推荐升级系统库，比如glibc，因为在其他方面，可能会错过-lc、-lm、-lrt等低级功能中最近存在的优化。

#### Zlib

通常，web服务器将负责压缩。根据多少数据将通过代理，偶尔会在perf top看到zlib的符号，例如:

    # perf top
    ...
       8.88%  nginx        [.] longest_match
       8.29%  nginx        [.] deflate_slow
       1.90%  nginx        [.] compress_block

在最低级别上有一些优化的方法：英特尔和Cloudflare，以及一个独立的zlib-ng项目，都有他们的zlib forks，可以通过使用新的指令集提供更好的性能。

#### Malloc

在讨论优化之前，我们主要是面向cpu的，但是现在换一个角度，讨论与内存相关的优化。如果使用大量的Lua和FFI或第三方的重模块来执行它们自己的内存管理，可能会由于碎片化而增加内存使用。可以尝试通过切换到jemalloc或tcmalloc来解决这个问题。

使用自定义malloc有以下好处。

* 将nginx二进制文件与环境分离，glibc版本升级和操作系统迁移会减少它的影响。
* 更好的自查、分析和统计。


#### PCRE

如果在nginx configs中使用许多复杂的正则表达式，或者严重依赖Lua，perf top会显示与pcre相关的标志。可以通过使用JIT编译PCRE进行优化，也可以通过pcre_jit在nginx中启用它。

通过查看火焰图或者使用funclatency检查优化的结果。

    # funclatency /srv/nginx-bazel/sbin/nginx:ngx_http_regex_exec -u
    ...
         usecs               : count     distribution
             0 -> 1          : 1159     |**********                              |
             2 -> 3          : 4468     |****************************************|
             4 -> 7          : 622      |*****                                   |
             8 -> 15         : 610      |*****                                   |
            16 -> 31         : 209      |*                                       |
            32 -> 63         : 91       |                                        |

#### TLS

如果在CDN前端的w/o边缘终止TLS，那么TLS性能优化的价值是非常可观的。在讨论调优时主要关注服务器端效率。

因此需要决定的第一件事是使用哪些TLS库：Vanilla [OpenSSL][55]、OpenBSD [LibreSSL][56]或谷歌的[BoringSSL][57]。在选择TLS库的偏好之后，需要适当地构建它，例如，OpenSSL有一堆构建时的启发式，[可以根据构建环境进行优化][58]；BoringSSL具有确定性的构建，但遗憾的是它更保守，[并且在默认情况下禁用了一些优化][59]。无论如何，在这里优先选择现代的CPU，大多数TLS库可以使用从AES-NI和SSE到ADX和AVX512的所有属性。可以使用带有TLS库的内置性能测试，例如在BoringSSL案例中，内置有bssl speed。

大多数性能不是来自已有的硬件，而是来自将要使用的cipher - suite，因此必须仔细地优化它们。也需要知道这里的变化会影响web服务器的安全性——最快的密码套件不一定是最好的。如果不确定要使用什么加密设置，[Mozilla SSL配置生成器][60]是一个很好的选择。

#### 非对称加密

如果服务处于边缘，那么可能会观察到相当数量的TLS握手，因此CPU占用了大量的非对称密码，这使它成为优化的明显目标。

为了优化服务器端CPU，可以切换到ECDSA certs，其速度通常比RSA快10倍，而且它们的体积要小得多，因此在出现包丢失时能加速握手。但是ECDSA也严重依赖于系统的随机数生成器的质量，所以如果使用的是OpenSSL，那么一定要有足够的熵值（使用BoringSSL，不必担心这一点）。

附注一点，越大并不总是越好，例如使用4096个RSA证书会降低10倍的性能。

    $ bssl speed
    Did 1517 RSA 2048 signing ... (1507.3 ops/sec)
    Did 160 RSA 4096 signing ...  (153.4 ops/sec)

更糟糕的是，小的也不一定是最好的选择：使用non-common p-224字段与更常见的p-256相比，会降低60%的性。

    $ bssl speed
    Did 7056 ECDSA P-224 signing ...  (6831.1 ops/sec)
    Did 17000 ECDSA P-256 signing ... (16885.3 ops/sec)

原则上最常用的加密通常是最优的加密。

在使用RSA certs运行适当优化的基于opentls的库时，可以在perf top：AVX2-capable看到以下的跟踪信息，但不能使用ADX-capable盒子（例如Haswell）。

      6.42%  nginx                [.] rsaz_1024_sqr_avx2
      1.61%  nginx                [.] rsaz_1024_mul_avx2

更新的硬件应该使用通用的montgomery模乘算法和ADX codepath。

      7.08%  nginx                [.] sqrx8x_internal
      2.30%  nginx                [.] mulx4x_internal

#### 对称加密

如果有许多的批量传输，如视频、照片或更通用的文件，需要在分析器的输出中观察对称加密符号。只需要确保CPU具有AES-NI支持，并为AES-GCM ciphers设置服务器端的首选项。根据perf top信息适当的调整硬件。

     8.47%  nginx                [.] aesni_ctr32_ghash_6x

不只是服务器需要解决加密/解密问题，客户端在缺少可用的CPU时也会面临相同的负担。如果没有硬件加速，这可能[非常具有挑战性][61]，因此可以考虑使用一种致力于快速而没有硬件加速的算法，例如[chacha20-poly1305][62]。这将减少一些移动客户的TTLB。

BoringSSL是支持chacha20-poly1305的，而对于OpenSSL 1.0.2，可以考虑使用Cloudflare补丁。BoringSSL还支持“[相等的首选密码组][63]”，因此可以使用以下配置，让客户端根据其硬件功能来决定使用什么密码（从[cloudflare/sslconfig][64]中窃取）。

    ssl_ciphers '[ECDHE-ECDSA-AES128-GCM-SHA256|ECDHE-ECDSA-CHACHA20-POLY1305|ECDHE-RSA-AES128-GCM-SHA256|ECDHE-RSA-CHACHA20-POLY1305]:ECDHE+AES128:RSA+AES128:ECDHE+AES256:RSA+AES256:ECDHE+3DES:RSA+3DES';
    ssl_prefer_server_ciphers on;

### 应用程序级别:高标准的

为了分析高标准的优化效果，需要收集RUM数据。在浏览器中，可以使用[导航定时api][65]和[资源定时api][66]收集。收集的主要指标是TTFB和TTV/TTI。拥有便于查询和图形化格式的数据将极大地简化迭代。

#### 压缩

nginx的压缩从mime.types文件开始，它定义了文件扩展和响应MIME类型之间的默认通信。然后需要定义传递给压缩器的类型。如果想要完整的列表，可以使用mime-db来自动生成mime.types并添加.compressible==true到gzip_types。

在启用gzip时，两个方面要注意。

* 增加了内存的使用，可以通过限制gzip_buffer来解决。
* 因为缓冲，所以增加了TTFB。使用[gzip_no_buffer]([http://hg.nginx.org/nginx/file/c7d4017c8876/src/http/modules/ngx_http_gzip_filter_module.c][67] # l182)可以解决问题。


附注，http压缩不仅限于gzip，nginx有第三方[ngx_brotli][68]模块，与gzip相比改善后的压缩率高达30%。

至于压缩设置本身，讨论两个单独的用例：静态和动态数据。

* 对于静态数据，可以通过预压缩静态资产作为构建过程的一部分来归档最大压缩比。过去有讨论在部署Brotli中为gzip和Brotli提供静态内容的详细信息。
* 对于动态数据，需要谨慎地平衡一次完整的往返：压缩数据的时间和传输时间，以便在客户机上解压。因此从CPU的使用和TTFB的角度来看，设置尽可能高的压缩级别是不明智的。


在代理中缓存会极大地影响web服务器性能，特别是在延迟方面。nginx代理模块有不同的缓冲开关，它们在每个位置上都是可转换的。可以通过_proxy_request_buffering_和_proxy_buffering_对两个方向的缓冲进行单独控制。如果缓冲是启用了内存消耗上限的话，则由_client_body_buffer_size_和_proxy_buffer_设置，达到请求/响应的临界值后，将缓冲到磁盘。对于响应的临界值，可以通过将_proxy_max_temp_file_size_设置为0来禁用。

最常见的缓冲方法有：

* 缓冲请求/响应在内存中的某个阈值，然后溢出到磁盘。如果启用了请求缓冲，那么只需将请求发送到后端，一旦它被完全接收，并通过响应缓冲，就可以在响应完成后立即释放后端线程。这种方法的好处是提高了吞吐量和后端保护，以增加延迟和内存/io的使用（如果使用ssd，这可能不是什么问题）。
* 没有缓冲。缓冲可能不是延迟敏感路由的好选择，特别是那些使用数据流的。对于他们来说可能想要禁用它，但是现在的后端需要处理慢速客户端（包括恶意的慢提交/慢读类型的攻击）。
* Application-controlled响应缓冲通过[X-Accel-Buffering]([https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/][69] # X-Accel-Buffering)数据头。


无论你选择哪种方法，都不要忘记测试它对TTFB和TTLB的影响。另外，正如前面提到的，缓冲可以影响IO的使用，甚至是后端使用率，因此也要密切关注它。

#### TLS

现在我们将讨论TLS的高级方面和延迟改进，可以通过正确配置nginx来实现。文章提到的大多数优化都包含在[高性能浏览器网络][3]的“[优化TLS][70]”部分，并在nginx.conf 2014上让HTTPS更快的讨论。此部分中提到的调优将会影响web服务器的性能和安全性，如果不确定，请参考[Mozilla服务器端TLS指南][71]和/或与安全团队协商。

如何验证优化结果。

* [WebpageTest][72]测试性能影响。
* 采用[来自Qualys的SSL服务器测试][73]，或[Mozilla TLS Observatory][74]测试安全影响。


#### 会话重用

像DBA常说的那样，“最快的查询是从来没有做过的。”TLS同样如此，如果你缓存了握手的结果，可以减少一个RTT的延迟。有两种方法缓存握手结果。

* 要求客户端存储所有会话参数（以签名和加密的方式），并在下一次握手时返回（类似于cookie）。在nginx方面，这是通过_ssl_session_tickets_指令配置的。虽然不会消耗服务器的任何内存，但有一些缺点：   
    * 需要基础设施来创建、旋转和分发LS会话的随机加密/签名键。请记住，不应该使用源控件来存储ticket keys，也不应该从其他非临时的材料中生成这些键，比如日期或证书。
    * PFS不会在每个会话的基础上，而是在每个tls-ticket的基础上，因此如果攻击者获得了ticket key，他们就可以在ticket的持续时间内对任何捕获的流量进行解密。
    * 加密将限制票务钥匙的大小。如果使用的是128位的票证，那么使用AES256没有多大意义，因为Nginx同时支持128位和256位TLS票号。
    * 并不是所有的客户都支持ticket key（尽管所有的现代浏览器都支持它们）。

* 或者，可以在服务器上存储TLS会话参数，只给客户端提供一个引用(id)，通过_ssl_session_cache_指令完成。它的好处是在会话之间保留PFS，并极大地限制表面的攻击。当然ticket keys也有缺点：   
    * 在服务器上每次会话消耗约256字节的内存，这意味着不能将它们存储太长时间。
    * 不能在服务器之间轻松共享。因此要么需要负载平衡器（loadbalancer ，发送相同的客户端到相同的服务器以保存缓存位置），要么写一个分布式TLS会话存储类似[ngx_http_lua_module][75]。


附注一点，如果使用session ticket方法，那么值得使用3个键而不是一个键值，例如：

    ssl_session_tickets on;
    ssl_session_timeout 1h;
    ssl_session_ticket_key /run/nginx-ephemeral/nginx_session_ticket_curr;
    ssl_session_ticket_key /run/nginx-ephemeral/nginx_session_ticket_prev;
    ssl_session_ticket_key /run/nginx-ephemeral/nginx_session_ticket_next;

即便始终使用当前的密钥进行会话加密，也会接受使用之前和之后密钥加密的会话。

#### OCSP Stapling

需要对OCSP响应作staple，否则:

* TLS握手可能需要更长的时间，因为客户端需要与证书颁发机构联系以获取OCSP状态。
* 在OCSP的取回失败可能导致可用性攻击。
* 可能会破坏用户的隐私，因为用户的浏览器会联系第三方服务，表明想要连接到当前网站。


要staple OCSP响应，可以定期从证书颁发机构获取它，将结果分发给web服务器，并使用_ssl_stapling_file_指令来调用。

    ssl_stapling_file /var/cache/nginx/ocsp/www.der;

#### TLS记录大小

TLS将数据分解成记录块，在完全接收到它之前，无法对其进行验证和解密。可以从网络堆栈和应用程序的角度来度量这一延迟。

默认的nginx使用16k块，甚至不适合IW10拥塞窗口，因此需要额外的往返。nginx提供了一种通过_ssl_buffer_size_指令设置记录大小的方法：

* 为了优化低延迟，你应该把它设置成小的，例如4k。从CPU使用的角度来看，进一步减少它将会更加昂贵。
* 为了优化高通量，您应该将其保留在16k。


静态调优的两个问题。

* 需要手动调优它。
* 只能将_ssl_buffer_size_设置为_per-nginx config_或_per-server_块，因此如果有一个具有混合延迟/吞吐量工作负载的服务器，则需要折衷。


还有一种替代方法：动态记录大小调整。来自Cloudflare的nginx补丁[为动态记录大小提供了支持][76]。最初对它进行配置可能是一种痛苦，但一旦配置完成，它就会运行得很好。

#### TLS 1.3

[TLS 1.3的功能确实很不错][77]，但是除非有足够的资源来解决TLS的问题，否则不建议启用它，因为以下原因。

* 它仍然是一个[草案][78]。
* 0-RTT握手有[一些安全隐患][79]，应用程序需要为此做好准备。
* 仍然有一些中间盒子（反病毒，DPIs等）阻止未知的TLS版本。


#### 避免Eventloop Stalls

Nginx是一个基于事件循环的web服务器，意味着它只能一次只做一件事。尽管它似乎同时做了所有这些事情，比如在分时复用中，[所有的nginx都只是在事件之间快速切换][80]，处理一个接一个。这一切都有效，因为处理每个事件只需几微秒。但如果它开始花费太多时间，例如，因为它需要转到旋转盘上，延迟就会飙升。

如果注意到nginx在_ngx_process_events_and_timer_函数中花费了很多时间，并且分布是双向的，那么可能会受到eventloop stalls的影响。

    # funclatency '/srv/nginx-bazel/sbin/nginx:ngx_process_events_and_timers' -m
         msecs               : count     distribution
             0 -> 1          : 3799     |****************************************|
             2 -> 3          : 0        |                                        |
             4 -> 7          : 0        |                                        |
             8 -> 15         : 0        |                                        |
            16 -> 31         : 409      |****                                    |
            32 -> 63         : 313      |***                                     |
            64 -> 127        : 128      |*                                       |

#### AIO和Threadpools

因为AIO和Threadpools是eventloop的主要来源，特别是在旋转磁盘上的IO，所以应该优先查看，还可以通过运行文件记录来观察受它影响的程度。

    # fileslower 10
    Tracing sync read/writes slower than 10 ms
    TIME(s)  COMM           TID    D BYTES   LAT(ms) FILENAME
    2.642    nginx          69097  R 5242880   12.18 0002121812
    4.760    nginx          69754  W 8192      42.08 0002121598
    4.760    nginx          69435  W 2852      42.39 0002121845
    4.760    nginx          69088  W 2852      41.83 0002121854

为了解决这个问题，nginx支持将IO卸载到一个threadpool（它也支持AIO，但是Unixes的本地AIO很不友好，所以最好避免它），基本的设置很简单。

    aio threads;
    aio_write on;

对于更复杂的情况可以设置自定义[线程池]([http://nginx.org/en/docs/ngx_core_module.html][81] # thread_pool)的预留磁盘，如果一个驱动器失效，它不会影响其他的请求。线程池可以极大地减少处于D状态的nginx进程的数量，从而提高延迟和吞吐量。但是它不会完全消除eventloop，因为并不是所有IO操作都被卸载。

#### 日志

记录日志也会花费相当多的时间，因为它在读写磁盘。可以通过运行ext4slower检查是否存在日志，并查找access/error日志引用。

    # ext4slower 10
    TIME     COMM           PID    T BYTES   OFF_KB   LAT(ms) FILENAME
    06:26:03 nginx          69094  W 163070  634126     18.78 access.log
    06:26:08 nginx          69094  W 151     126029     37.35 error.log
    06:26:13 nginx          69082  W 153168  638728    159.96 access.log

通过使用access_log指令的缓冲区参数，在编写它们之前，通过在内存中对访问日志进行欺骗，可以解决这个问题。再使用gzip参数，将日志写入磁盘之前压缩日志，从而减少IO压力。

但是要在日志中完全消除IO档位，只能通过syslog编写日志，这样日志将与nginx事件循环完全集成。

#### 打开文件缓存

因为open(2)调用本质上是阻塞的，而web服务器通常是打开/读取/关闭文件，因此缓存打开的文件可能是有益的。通过查看_ngx_open_cached_file_函数延迟，可以看到便利所在。

    # funclatency /srv/nginx-bazel/sbin/nginx:ngx_open_cached_file -u
         usecs               : count     distribution
             0 -> 1          : 10219    |****************************************|
             2 -> 3          : 21       |                                        |
             4 -> 7          : 3        |                                        |
             8 -> 15         : 1        |                                        |

如果看到有太多的开放调用，或者有一些花费太多时间的调用，可以启用文件缓存。

    open_file_cache max=10000;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;

在启用_open_file_cache_之后，可以通过查看opensnoop来观察所有的缓存遗漏，最终决定[是否需要调整缓存限制][82]。

    # opensnoop -n nginx
    PID    COMM               FD ERR PATH
    69435  nginx             311   0 /srv/site/assets/serviceworker.js
    69086  nginx             158   0 /srv/site/error/404.html
    ...

### 总结

本文描述的所有优化都是基于本地的Web服务器。其中一些提高了拓展性和性能。另外一些与最小延迟或更快地将字节传送给客户机是相关的。但在以往的体验中，大量用户可见的性能来自于更高级的优化，这些优化会影响到整个Dropbox 边缘网络的行为，比如ingress/egress流量工程和智能的内部负载平衡。这些问题处于知识的边缘，而行业才刚刚开始接近它们。

[0]: https://blogs.dropbox.com/tech/2017/09/optimizing-web-servers-for-high-throughput-and-low-latency/
[1]: https://blogs.dropbox.com/tech/2017/06/evolution-of-dropboxs-edge-network/
[2]: http://www.brendangregg.com/linuxperf.html
[3]: https://hpbn.co/
[4]: https://www.ssllabs.com/ssltest/
[5]: https://www.feistyduck.com/bulletproof-tls-newsletter/
[6]: https://www.intel.com/content/dam/www/public/us/en/documents/white-papers/large-integer-squaring-ia-paper.pdf
[7]: https://software.intel.com/en-us/articles/improving-openssl-performance
[8]: https://openconnect.netflix.com/publications/asiabsd_tls_improved.pdf
[9]: https://netdevconf.org/1.2/papers/ktls.pdf
[10]: https://github.com/yandex/yandex-tank
[11]: https://pp.nginx.com/thresh/nginxperftest.odp
[12]: https://software.intel.com/en-us/articles/power-management-states-p-states-c-states-and-package-c-states
[13]: https://access.redhat.com/articles/65410
[14]: https://www.netdevconf.org/2.1/papers/BusyPollingNextGen.pdf
[15]: http://note.youdao.com/
[16]: https://alexandrnikitin.github.io/blog/transparent-hugepages-measuring-the-performance-impact/
[17]: https://blog.nelhage.com/post/transparent-hugepages/
[18]: http://frankdenneman.nl/2016/07/06/introduction-2016-numa-deep-dive-series/
[19]: https://code.facebook.com/posts/1711485769063510/facebook-s-new-front-end-server-design-delivers-on-performance-without-sucking-up-power/
[20]: http://www.cirrascale.com/blog/index.php/pci-debugging-101/
[21]: http://img.blog.csdn.net/20170915105405304
[22]: https://en.wikipedia.org/wiki/PCI_Express#History_and_revisions
[23]: https://community.mellanox.com/docs/DOC-2496
[24]: https://access.redhat.com/sites/default/files/attachments/20150325_network_performance_tuning.pdf
[25]: https://blog.cloudflare.com/how-to-achieve-low-latency/
[26]: https://community.mellanox.com/docs/DOC-2532
[27]: http://patchwork.ozlabs.org/patch/348793/
[28]: https://community.mellanox.com/docs/DOC-2511
[29]: https://lwn.net/Articles/358910/
[30]: https://software.intel.com/en-us/articles/setting-up-intel-ethernet-flow-director
[31]: https://www.intel.com/content/dam/www/public/us/en/documents/white-papers/intel-ethernet-flow-director.pdf
[32]: http://lss.fnal.gov/archive/2010/pub/fermilab-pub-10-309-cd.pdf
[33]: http://linuxgazette.net/136/pfeiffer.html
[34]: https://patchwork.ozlabs.org/patch/465806/
[35]: https://www.youtube.com/watch?v=gfYYggNkM20
[36]: https://www.youtube.com/channel/UCribHdOMgiD5R3OUDgx2qTg
[37]: https://lwn.net/Articles/719388/
[38]: https://blog.packagecloud.io/eng/2016/06/22/monitoring-tuning-linux-networking-stack-receiving-data/
[39]: https://blog.packagecloud.io/eng/2017/02/06/monitoring-tuning-linux-networking-stack-sending-data/
[40]: https://netdevconf.org/1.2/papers/bbr-netdev-1.2.new.new.pdf
[41]: http://img.blog.csdn.net/20170915105425793
[42]: http://img.blog.csdn.net/20170915105509439
[43]: https://blog.apnic.net/2017/05/09/bbr-new-kid-tcp-block/
[44]: https://groups.google.com/forum/#!forum/bbr-dev
[45]: https://datatracker.ietf.org/rg/iccrg/about/
[46]: https://lwn.net/Articles/560082/
[47]: https://lwn.net/Articles/277219/
[48]: https://blog.cloudflare.com/ip-fragmentation-is-broken/
[49]: https://blog.cloudflare.com/the-story-of-one-latency-spike/
[50]: https://groups.google.com/forum/#!topic/bbr-dev/g1tS1HUcymE
[51]: https://github.com/bagder/I-D/blob/gh-pages/httpbis-tcp/draft.md
[52]: http://img.blog.csdn.net/20170915105534442
[53]: https://docs.google.com/document/d/1xXBH6rRZue4f296vGt9YQcuLVQHeE516stHwt8M9xyU/edit
[54]: https://clang.llvm.org/docs/SafeStack.html
[55]: https://www.openssl.org/
[56]: https://www.libressl.org/
[57]: https://boringssl.googlesource.com/boringssl/
[58]: https://github.com/openssl/openssl/blob/1b3011abb36ff743c05afce1c9f2450d83d09d59/crypto/bn/asm/rsaz-avx2.pl#L51-L55
[59]: https://boringssl.googlesource.com/boringssl/+/master/crypto/fipsmodule/bn/asm/rsaz-avx2.pl#82
[60]: https://mozilla.github.io/server-side-tls/ssl-config-generator/
[61]: https://blog.cloudflare.com/do-the-chacha-better-mobile-performance-with-cryptography/
[62]: https://www.imperialviolet.org/2013/10/07/chacha20.html
[63]: https://boringssl.googlesource.com/boringssl/+/858a88daf27975f67d9f63e18f95645be2886bfb%5E!/
[64]: https://github.com/cloudflare/sslconfig/blob/master/conf
[65]: https://developer.mozilla.org/en-US/docs/Web/API/Navigation_timing_API
[66]: https://developer.mozilla.org/en-US/docs/Web/API/Resource_Timing_API
[67]: http://hg.nginx.org/nginx/file/c7d4017c8876/src/http/modules/ngx_http_gzip_filter_module.c
[68]: https://github.com/google/ngx_brotli
[69]: https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/
[70]: https://hpbn.co/transport-layer-security-tls/#optimizing-for-tls
[71]: https://wiki.mozilla.org/Security/Server_Side_TLS
[72]: https://www.webpagetest.org/
[73]: https://www.ssllabs.com/ssltest/index.html
[74]: https://github.com/mozilla/tls-observatory
[75]: https://github.com/openresty/lua-resty-core/blob/master/lib/ngx/ssl/session.md
[76]: https://blog.cloudflare.com/optimizing-tls-over-tcp-to-reduce-latency/
[77]: https://blog.cloudflare.com/tls-1-3-overview-and-q-and-a/
[78]: https://tools.ietf.org/html/draft-ietf-tls-tls13-21
[79]: https://github.com/tlswg/tls13-spec/issues/1001
[80]: https://www.nginx.com/blog/inside-nginx-how-we-designed-for-performance-scale/
[81]: http://nginx.org/en/docs/ngx_core_module.html
[82]: https://nginx.org/en/docs/http/ngx_http_core_module.html#open_file_cache
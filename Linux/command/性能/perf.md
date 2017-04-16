# Linux Perf使用简介 

[开发进阶][0]

* [内核][1]
* [后台开发][2]
* [开发基础][3]
* [服务运维][4]
* [读书笔记][5]

Linux perf也算是一个比较高端的调优工具了，主要被用于对硬件、操作系统或特定服务进行Event和Performance分析，根据得到的结果对软件、系统进行有依据的调优操作。一直觉得驾驭这类工具(还有SystemTap)对使用者的素质要求甚大，该类工具需要对系统原理、结构等知识有较为深入的了解，才能针对性的设计实施收集方案，将集到有价值数据并进一步实施分析和调优。感觉当前对于大规模数据开发的后台组件已经完善，而且各大云计算服务商也提供性能强劲、自动伸缩的开箱即用型基础服务，但是只要有开发就会涉及到性能分析和优化任务，所以这个议题也必将是个经久不衰的话题。  
Linux perf使用相当广泛以至于几乎成为性能分析(performance analysis)的工业事实上的标准，但是其相关资料比较缺乏(scarce)，鲜有覆盖完整、体系性较强的教材可供完整参阅学习，而且官方文档也让人感觉组织的凌乱不堪，应证了牛逼的开源系统常常伴随着文档的短板。这里需要重点推荐一下taobao kernel团队的承刚大侠，他对Linux perf做了较为深入的研究和总结，算是中文中难得的[Linux perf原创资料][6]，无论从原理学习还是操作实战上讲都具有较大的参考价值！

# 一、perf简介

性能分析通常有两种方式：profile和trace，前者讲求的是基于固定事件间隔进行采样来实现，trace则是在特定的跟踪点记录当时执行的时间戳信息。perf是在Linux内核实现的一种性能分析工具，当前正在被活跃的开发和完善，其主要作为profiler角色(hardwar、hardware cache、software events)，同时也实现了tracer的功能(tracepoint、probepoint events)，通过借助于硬件和实现技巧，可以实现对被跟踪系统的最小化干扰、被跟踪系统无须修改和单独配置(无侵入) ，同时可以得到丰富和良好显示的性能相关信息。  
几乎所有的软件运行也有“二八定律”，即少量的代码可能会多次执行，而大多数代码只会被很少执行。那么基于采样原理就可以得到热点代码(hot spot)，针对这部分热点代码进行优化处理(分支判断、指令重排等)，那么理论上将会得到较大的性能提升收益。比如某些代码page-faults指标较高，则可以查看是否这部分代码可以节省耗费内存的大小，高端分析师还可以监测出锁竞(lock contention)争带来的性能损耗，针对性的重新组织代码和结构杜绝这类问题的发生。  
硬件事件的实现是基于硬件(CPU包含PMU Performance Monitoring Units单元)实现的，在事件跟踪运行的时候内核会注册一个类似周期定时器的PMC(Performance Monitoring Counter)，其计数会随着CPU周期进行累加，当PMC溢出的时候会触发一个PMI(Performance Monitoring Interrupt)中断，内核在这个中断处理函数中内核会将相关信息(比如IP、user stack、kernel stack、timer等，他们共同组成一个采样)拷贝到perf工具事先设定的共享内存区域中去，通过mmap机制减少了内核态到用户态的额外数据拷贝。进行perf分析的用户态程序需要保留符号信息，这样perf工具就可以根据采样中的信息推断出当前时刻执行的上下文信息，进而进行统计、计算等操作。

[![perf](https://taozj.org/post_images/images/201703/558cfe86.jpg "perf")](https://taozj.org/post_images/images/201703/558cfe86.jpg "perf")  
需要注意的是，根据文献描述VMware、VirtualBox虚拟化技术的Guest是不允许访问硬件counter的，所以这类虚拟机的perf可能工作的不完整，但是KVM、Xen虚拟化技术是杠杠滴！至少我在VMware虚拟机上perf list和实体机上perf list出来的event类型差异巨大。

# 二、perf常用工具

perf的主要功能是实现在kernel的，而用户态提供了丰富的命令族以简化事件跟踪的操作。下面对常用的命令进行简短的尝试和介绍，其中任何一个命令的详细手册可以通过man perf-subcommand来查看。  
下面的命令默认都是系统全局的，可以通过使用-p pid或-t tid进行限定，来分析观测已经运行的进程、线程的性能信息，而且使用-t指定特定线程后就只针对该线程统计，而不包含该线程创建的其他线程。  
**perf list**  
该命令列出当前系统所支持的预先定义的事件类型(跟环境有关)，这些预定义事件可以分为hw、sw、cache、tracepoint类，用这些作为参数可以显示过滤后该类别支持的事件类型。  
这个命令显示的时间，可以在后面的命令中用于-e过滤使用，比如需要观测Cache丢失事件，可以通过：perf top -e cache-misses，如perf list输出的，内核的tracepoint也可以作为跟踪的过滤条件，比如系统调用的跟踪次数perf stat -e raw_syscalls:sys_enter ls。

[![perf](https://taozj.org/post_images/images/201703/c0236a98.jpg "perf")]https://taozj.org/post_images/images/201703/c0236a98.jpg "perf")

**perf stat**  
通过–分割来运行一个命令，然后记录该进程运行时候的性能数据概况，perf会使用执行时间(ms)、(进程和线程)上下文切换次数、多核心CPU迁移、缺页异常、分支次数、分支预测等指标来评价这个命令的执行，而如果添加-d(–detail)参数，那么现实的信息还会加成。  
大侠描述目前编译器和CPU的努力分支预测都能达到95%以上的成功率，低于这个指标就需要注意代码的优化了。  
-r N可以指定运行该命令几次，这样还可以看出每次测试之间的性能波动情况。

**perf top**  
实时状态显示系统级别或是某个进程/线程的性能profile。从左边第一列可以清楚的看到各个(用户态、内核态)函数调用的热度。  
如果启动时候添加-K参数，就可以略去内核的符号，专注于用户态符号会让显示更加清爽。 类似的，通过-U可以过滤用户态符号的热点显示。  
-F可以指定采样频率，如果感觉测出来的结果不准确，可以适当提高采样频率重新尝试。  
-g graph,caller|callee 可以显示函数调用的图谱，十分的爽。  
perf top可以添加-tui以使用更友好的交互界面，可以使用方向键选中某个符号，然后按a使用annotate功能，界面会显示反汇编的效果，这样就可以显示指令级的热点；在某个函数符号上按d，可以过滤掉不属于该对象的符号，可以让分析更加的有针对性。

**perf record**  
运行一个命令，收集并将其性能数据保存在当前工作目录的perf.data文件中。该命令没有指定收集数据的时段长，根据例子可以这么使用sleep法指定时段长度perf stat -a sleep 5。

**perf report**  
将之前生成的perf.data中保存的数据进行格式化输出，给出分析结果。

**perf diff**  
比较两个性能数据文件的差异，这通常可以用来对比优化前和优化后的效果差异。

**perf lock**  
分析内核中的加锁信息，包括锁的竞争使用情况、等待延时等信息。使用它需要把内核的CONFIG_LOCKDEP和CONFIG_LOCK_STAT符号打开才行。

**perf probe**  
用于增加、删除、显示等对动态跟踪点(dynamic tracepoint)的管理操作。通过–add增加动态tracepoint之后，就可以像内核和其他软件内置的静态tracepoint一样用于perf的操作中。

一大波有用典型的perf使用的例子可以查看[perf Examples][7]。

本文完！

# 参考

* [PERF tutorial: Finding execution hot spots][8]
* [brendangregg.com/perf][7]
* [Kernel_Perf][9]
* [Perf FAQ][6]

[0]: /categories/开发进阶/
[1]: /tags/内核/
[2]: https://taozj.org/后台开发/
[3]: /tags/开发基础/
[4]: /tags/服务运维/
[5]: /tags/读书笔记/
[6]: http://kernel.taobao.org/index.php?title=Documents/Perf_FAQ
[7]: http://www.brendangregg.com/perf.html
[8]: http://sandsoftwaresound.net/perf/perf-tutorial-hot-spots/
[9]: http://kernel.taobao.org/index.php?title=Documents/Kernel_Perf
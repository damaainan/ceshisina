# Linux性能调优工具perf的使用

 时间 2017-06-04 14:51:56  [cpper][0]  [相似文章][1] (_1_)

_原文_[http://cpper.info/2017/06/04/perf.html][2]

 主题 [Linux][3][性能测试][4]

## 1. perf简介

Perf是内置于Linux内核源码树中的性能剖析(profiling)工具，它基于事件采样原理，以性能事件为基础，支持针对处理器相关性能指标与操作系统相关性能指标的性能剖析，常用于性能瓶颈的查找与热点代码的定位。

通过它，应用程序可以利用 PMU，tracepoint 和内核中的特殊计数器来进行性能统计。它不但可以分析指定应用程序的性能问题 (per thread)，也可以用来分析内核的性能问题，当然也可以同时分析应用代码和内核，从而全面理解应用程序中的性能瓶颈。

使用 perf，您可以分析程序运行期间发生的硬件事件，比如 instructions retired ，processor clock cycles 等；您也可以分析软件事件，比如 Page Fault 和进程切换。这使得 Perf 拥有了众多的性能分析能力，举例来说，使用 Perf 可以计算每个时钟周期内的指令数，称为 IPC，IPC 偏低表明代码没有很好地利用 CPU。Perf 还可以对程序进行函数级别的采样，从而了解程序的性能瓶颈究竟在哪里等等。Perf 还可以替代 strace，可以添加动态内核 probe 点，还可以做 benchmark 衡量调度器的好坏。

## 2. 背景知识

有些背景知识是分析性能问题时需要了解的。比如硬件 cache；再比如操作系统内核。应用程序的行为细节往往是和这些东西互相牵扯的，这些底层的东西会以意想不到的方式影响应用程序的性能，比如某些程序无法充分利用 cache，从而导致性能下降。比如不必要地调用过多的系统调用，造成频繁的内核 / 用户切换。等等。方方面面，这里只是为本文的后续内容做一些铺垫，关于调优还有很多东西，我所不知道的比知道的要多的多。

当算法已经优化，代码不断精简，人们调到最后，便需要斤斤计较了。cache 啊，流水线啊一类平时不大注意的东西也必须精打细算了。

### 2.1 硬件特性之 cache

内存读写是很快的，但还是无法和处理器的指令执行速度相比。为了从内存中读取指令和数据，处理器需要等待，用处理器的时间来衡量，这种等待非常漫长。Cache 是一种 SRAM，它的读写速率非常快，能和处理器处理速度相匹配。因此将常用的数据保存在 cache 中，处理器便无须等待，从而提高性能。Cache 的尺寸一般都很小，充分利用 cache 是软件调优非常重要的部分。

### 2.2 硬件特性之流水线，超标量体系结构，乱序执行

提高性能最有效的方式之一就是并行。处理器在硬件设计时也尽可能地并行，比如流水线，超标量体系结构以及乱序执行。 处理器处理一条指令需要分多个步骤完成，比如先取指令，然后完成运算，最后将计算结果输出到总线上。在处理器内部，这可以看作一个三级流水线，如下图所示：

![][5]

指令从左边进入处理器，上图中的流水线有三级，一个时钟周期内可以同时处理三条指令，分别被流水线的不同部分处理。 超标量（superscalar）指一个时钟周期发射多条指令的流水线机器架构，比如 Intel 的 Pentium 处理器，内部有两个执行单元，在一个时钟周期内允许执行两条指令。

此外，在处理器内部，不同指令所需要的处理步骤和时钟周期是不同的，如果严格按照程序的执行顺序执行，那么就无法充分利用处理器的流水线。因此指令有可能被乱序执行。

上述三种并行技术对所执行的指令有一个基本要求，即相邻的指令相互没有依赖关系。假如某条指令需要依赖前面一条指令的执行结果数据，那么 pipeline 便失去作用，因为第二条指令必须等待第一条指令完成。因此好的软件必须尽量避免这种代码的生成。

### 2.3 硬件特性之分支预测

分支指令对软件性能有比较大的影响。尤其是当处理器采用流水线设计之后，假设流水线有三级，当前进入流水的第一条指令为分支指令。假设处理器顺序读取指令，那么如果分支的结果是跳转到其他指令，那么被处理器流水线预取的后续两条指令都将被放弃，从而影响性能。为此，很多处理器都提供了分支预测功能，根据同一条指令的历史执行记录进行预测，读取最可能的下一条指令，而并非顺序读取指令。

分支预测对软件结构有一些要求，对于重复性的分支指令序列，分支预测硬件能得到较好的预测结果，而对于类似 switch case 一类的程序结构，则往往无法得到理想的预测结果。

上面介绍的几种处理器特性对软件的性能有很大的影响，然而依赖时钟进行定期采样的 profiler 模式无法揭示程序对这些处理器硬件特性的使用情况。处理器厂商针对这种情况，在硬件中加入了 PMU 单元，即 performance monitor unit。

PMU 允许软件针对某种硬件事件设置 counter，此后处理器便开始统计该事件的发生次数，当发生的次数超过 counter 内设置的值后，便产生中断。比如 cache miss 达到某个值后，PMU 便能产生相应的中断。

捕获这些中断，便可以考察程序对这些硬件特性的利用效率了。

### 2.4 Tracepoints

Tracepoint 是散落在内核源代码中的一些 hook，一旦使用，它们便可以在特定的代码被运行到时被触发，这一特性可以被各种 trace/debug 工具所使用。Perf 就是该特性的用户之一。

假如您想知道在应用程序运行期间，内核内存管理模块的行为，便可以利用潜伏在 slab 分配器中的 tracepoint。当内核运行到这些 tracepoint 时，便会通知 perf。

Perf 将 tracepoint 产生的事件记录下来，生成报告，通过分析这些报告，调优人员便可以了解程序运行时期内核的种种细节，对性能症状作出更准确的诊断。

## 3. perf 命令

性能调优工具如 perf，Oprofile 等的基本原理都是对被监测对象进行采样，最简单的情形是根据 tick 中断进行采样，即在 tick 中断内触发采样点，在采样点里判断程序当时的上下文。假如一个程序 90% 的时间都花费在函数 foo() 上，那么 90% 的采样点都应该落在函数 foo的上下文中。只要采样频率足够高，采样时间足够长，那么以上推论就比较可靠。因此，通过 tick 触发采样，我们便可以了解程序中哪些地方最耗时间，从而重点分析。

稍微扩展一下思路，就可以发现改变采样的触发条件使得我们可以获得不同的统计数据：

* 以时间点 ( 如 tick) 作为事件触发采样便可以获知程序运行时间的分布。
* 以 cache miss 事件触发采样便可以知道 cache miss 的分布，即 cache 失效经常发生在哪些程序代码中
* 等等

Perf是一个包含多个子工具的工具集：

    # perf
    
     usage: perf [--version] [--help] COMMAND [ARGS]
    
     The most commonly used perf commands are:
       annotate        Read perf.data (created by perf record) and display annotated code
       archive         Create archive with object files with build-ids found in perf.data file
       bench           General framework for benchmark suites
       buildid-cache   Manage build-id cache.
       buildid-list    List the buildids in a perf.data file
       diff            Read two perf.data files and display the differential profile
       evlist          List the event names in a perf.data file
       inject          Filter to augment the events stream with additional information
       kmem            Tool to trace/measure kernel memory(slab) properties
       kvm             Tool to trace/measure kvm guest os
       list            List all symbolic event types
       lock            Analyze lock events
       probe           Define new dynamic tracepoints
       record          Run a command and record its profile into perf.data
       report          Read perf.data (created by perf record) and display the profile
       sched           Tool to trace/measure scheduler properties (latencies)
       script          Read perf.data (created by perf record) and display trace output
       stat            Run a command and gather performance counter statistics
       test            Runs sanity tests.
       timechart       Tool to visualize total system behavior during a workload
       top             System profiling tool.

其中最常用的应该就是perf list、perf record、perf report、perf stat、perf top工具了。

### 3.1 perf list

perf list用来查看perf所支持的性能事件（即能够触发perf能够采样点的事件），其中有软件的也有硬件的。

    NAME
           perf-list - List all symbolic event types
    
    SYNOPSIS
           perf list [hw|sw|cache|tracepoint|event_glob]

* 性能事件的分布


  * hw：Hardware event，9个

    cpu-cycles OR cycles                               [Hardware event]
        instructions                                       [Hardware event]
        cache-references                                   [Hardware event]
        cache-misses                                       [Hardware event]
        branch-instructions OR branches                    [Hardware event]
        branch-misses                                      [Hardware event]
        bus-cycles                                         [Hardware event]
        stalled-cycles-frontend OR idle-cycles-frontend    [Hardware event]
        stalled-cycles-backend OR idle-cycles-backend      [Hardware event]
  * sw：Software event，9个

    cpu-clock                                          [Software event]
        task-clock                                         [Software event]
        page-faults OR faults                              [Software event]
        context-switches OR cs                             [Software event]
        cpu-migrations OR migrations                       [Software event]
        minor-faults                                       [Software event]
        major-faults                                       [Software event]
        alignment-faults                                   [Software event]
        emulation-faults                                   [Software event]
  * cache：Hardware cache event，26个，不再列出
  * tracepoint：Tracepoint event，775个，不再列出

说明：


  * hw和cache是由 PMU 硬件产生的事件，比如 cache 命中，当您需要了解程序对硬件特性的使用情况时，便需要对这些事件进行采样
  * sw 是内核软件产生的事件，比如进程切换，tick 数等，与硬件无关
  * tracepoint是内核中的静态 tracepoint 所触发的事件，这些 tracepoint 用来判断程序运行期间内核的行为细节，比如 slab 分配器的分配次数等。

提示：这里的event是预定义，可以通过perf list命令列出所有的预定义event。
* 指定性能事件

    -e <event> : u // userspace
      -e <event> : k // kernel
      -e <event> : h // hypervisor
      -e <event> : G // guest counting (in KVM guests)
      -e <event> : H // host counting (not in KVM guests)
* 使用示例

    #显示内核和模块中，消耗最多CPU周期的函数：
      perf top -e cycles:k
      #显示分配高速缓存最多的函数：
      perf top -e kmem:kmem_cache_alloc

### 3.2 perf top

对于一个指定的性能事件(默认是CPU周期)，显示消耗最多的函数或指令。

    NAME
           perf-top - System profiling tool.
    
    SYNOPSIS
           perf top [-e <EVENT> | --event=EVENT] [<options>]
    
    DESCRIPTION
           This command generates and displays a performance counter profile in real time.

perf top主要用于实时分析各个函数在某个性能事件上的热度，能够快速的定位热点函数，包括应用程序函数、 模块函数与内核函数，甚至能够定位到热点指令。默认的性能事件为cpu cycles。

* 输出格式

直接运行perf top输出示例如下：

![][6]

其中，


  * 第一列：符号引发的性能事件的比例，默认指占用的cpu周期比例。
  * 第二列：符号所在的DSO(Dynamic Shared Object)，可以是应用程序、内核、动态链接库、模块。
  * 第三列：DSO的类型。[.]表示此符号属于用户态的ELF文件，包括可执行文件与动态链接库)。[k]表述此符号属于内核或模块。
  * 第四列：符号名。有些符号不能解析为函数名，只能用地址表示。

从上图可以看出当前最占CPU的就是t2程序中的bar函数。
* 常用参数

    -a : 搜集所有CPU信息（默认设置）
      -e <event>：指明要分析的性能事件。  
      -p <pid>：Profile events on existing Process ID (comma sperated list). 仅分析目标进程及其创建的线程。  
      -t <tid> 仅分析tid线程。
      -k <path>：Path to vmlinux. Required for annotation functionality. 带符号表的内核映像所在的路径。  
      -K：不显示属于内核或模块的符号。  
      -U：不显示属于用户态程序的符号。  
      -d <n>：界面的刷新周期，默认为2s，因为perf top默认每2s从mmap的内存区域读取一次性能数据。  
      -G：得到函数的调用关系图。  
          perf top -G [fractal]，路径概率为相对值，加起来为100%，调用顺序为从下往上。  
          perf top -G graph，路径概率为绝对值，加起来为该函数的热度。
* 使用示例

    perf top                        # 默认配置
      perf top -G                       # 得到调用关系图
      perf top -e cycles                # 指定性能事件
      perf top -p 23015,32476           # 查看这两个进程的cpu cycles使用情况
      perf top -s comm,pid,symbol       # 显示调用symbol的进程名和进程号
      perf top --comms nginx,top        # 仅显示属于指定进程的符号
      perf top --symbols kfree      # 仅显示指定的符号

### 3.3 perf stat

用于分析指定程序的性能概况。

    NAME
           perf-stat - Run a command and gather performance counter statistics
    
    SYNOPSIS
           perf stat [-e <EVENT> | --event=EVENT] [-a] <command>
           perf stat [-e <EVENT> | --event=EVENT] [-a] — <command> [<options>]
    
    DESCRIPTION
           This command runs a command and gathers performance counter statistics from it. - 输出格式  
    通过对一个简单的ls命令进行统计("perf stat ls")，输出示例如下：  
        
         Performance counter stats for 'ls':
        
                  2.877603      task-clock (msec)         #    0.804 CPUs utilized          
                         0      context-switches          #    0.000 K/sec                  
                         0      cpu-migrations            #    0.000 K/sec                  
                       282      page-faults               #    0.098 M/sec                  
                 3,410,228      cycles                    #    1.185 GHz                      (79.38%)
                 1,940,556      stalled-cycles-frontend   #   56.90% frontend cycles idle     (65.44%)
                 1,546,072      stalled-cycles-backend    #   45.34% backend  cycles idle     (61.49%)
                 2,360,962      instructions              #    0.69  insns per cycle        
                                                          #    0.82  stalled cycles per insn
                   530,167      branches                  #  184.239 M/sec                  
                    19,170      branch-misses             #    3.62% of all branches        
        
               0.003579346 seconds time elapsed
    
    其输出包括ls的执行时间，以及10个性能事件的统计：
    - task-clock：任务真正占用的处理器时间，单位为ms。CPUs utilized = task-clock / time elapsed，CPU的占用率。
    - context-switches：上下文的切换次数。
    - CPU-migrations：处理器迁移次数。Linux为了维持多个处理器的负载均衡，在特定条件下会将某个任务从一个CPU
    迁移到另一个CPU。
    - page-faults：缺页异常的次数。当应用程序请求的页面尚未建立、请求的页面不在内存中，或者请求的页面虽然在内
    存中，但物理地址和虚拟地址的映射关系尚未建立时，都会触发一次缺页异常。另外TLB不命中，页面访问权限不匹配
    等情况也会触发缺页异常。
    - cycles：消耗的处理器周期数。如果把被ls使用的cpu cycles看成是一个处理器的，那么它的主频为2.486GHz。
    可以用cycles / task-clock算出。
    - stalled-cycles-frontend：略过。
    - stalled-cycles-backend：略过。
    - instructions：执行了多少条指令。IPC为平均每个cpu cycle执行了多少条指令。
    - branches：遇到的分支指令数。
    - branch-misses是预测错误的分支指令数。

* 常用参数

    -a：从所有CPU上收集性能数据。
      -p： 仅分析目标进程及其创建的线程。
      -t <tid> 仅分析tid线程。
      -r <num>：重复执行命令求平均，最大值是100，0 表示一直运行
      -C,--cpu=：从指定CPU上收集性能数据。
      -v：be more verbose (show counter open errors, etc), 显示更多性能数据。
      -n：只显示任务的执行时间 。
      -x SEP：指定输出列的分隔符。
      -o file：指定输出文件，--append指定追加模式。
      --pre <cmd>：执行目标程序前先执行的程序。
      --post <cmd>：执行目标程序后再执行的程序。
* 使用示例

以下都以执行”ls”命令进行说明：

    perf stat -r 10 ls > /dev/null         # 执行10次程序，给出标准偏差与期望的比值
      perf stat -v ls > /dev/null            # 显示更详细的信息
      perf stat -n ls > /dev/null            # 只显示任务执行时间，不显示性能计数器
      perf stat -a -A ls > /dev/null         # 单独给出每个CPU上的信息
      perf stat -e syscalls:sys_enter ls     # ls命令执行了多少次系统调用

### 3.4 perf record

收集采样信息，并将其记录在数据文件中。

随后可以通过其它工具(perf-report)对数据文件进行分析，结果类似于perf-top的。

* 常用参数

    -a：从所有CPU上收集性能数据
      -e：<event>：指明要分析的性能事件
      -p：<pid>：仅分析目标进程及其创建的线程
      -t：<tid> 仅分析tid线程
      -o：将采集的数据输出到指定文件
      -g：采集函数的调用关系图
* 使用示例

    perf record -p 2134                     # 记录2134进程的性能数据
      perf record ls -g                     # 记录执行ls时的性能数据
      perf record -e syscalls:sys_enter ls  # 记录执行ls时的系统调用，可以知道哪些系统调用最频繁

说明，如果不指定-o参数，则record的统计信息将输出到当前目录下的perf.data文件。

### 3.5 perf report

读取perf record创建的数据文件，并给出热点分析结果。

* 常用参数

    -i, --input=：输入性能数据文件
      -T, --threads：显示每一个线程的事件统计信息
      --pid=：仅显示给的pid的进程的事件统计信息
      --tid=：仅显示给的tid的线程的事件统计信息
* 使用示例

    perf record -p 19816                    # 记录19816进程的性能数据，默认将统计信息输出perf.data文件
      perf report -i perf.data              # 分析perf.data文件，并打印出分析结果

### 3.6 perf bench

除了调度器之外，很多时候人们都需要衡量自己的工作对系统性能的影响。benchmark 是衡量性能的标准方法，对于同一个目标，如果能够有一个大家都承认的 benchmark，将非常有助于”提高内核性能”这项工作。

* 常用参数

目前perf bench 提供了以下 5 个 benchmark选项:

    sched
             Scheduler and IPC mechanisms.
    
         mem
             Memory access performance.
    
         numa
             NUMA scheduling and MM benchmarks.
    
         futex
             Futex stressing benchmarks.
    
         all
             All benchmark subsystems.
* 使用示例

    perf bench mem memcpy       # 对memcpy的数据拷贝速度进行bench
      # Running 'mem/memcpy' benchmark:
      # Copying 1MB Bytes from 0x7f30d049a010 to 0x7f30d28c6010 ...
        
           969.932105 MB/Sec        # 表示该系统memcpy每秒可以拷贝969MB数据
      perf bench sched pipe     # 对pipe的调度性能进行bench
      # Running 'sched/pipe' benchmark:
      # Executed 1000000 pipe operations between two processes
        
           Total time: 9.647 [sec]
        
             9.647606 usecs/op  # 表示一次pipe调用耗时9.6微秒
               103652 ops/sec       # 表示每秒可以调用103652次pipe，也即QPS

### 3.7 其他（lock、kmem、sched、probe）

还有其他多个perf 工具可以使用，因为不常用，这里不再介绍，如需了解可以查看本文的参考链接。

## 4. 实例

### 4.1 使用stat和record对某一进程进行分析

假设我们有一个程序，CPU耗用比较多，此时可以通过perf record对该程序进行采样并分析瓶颈所在。

这里假设有以下代码：

    // t1.c 
    #include <stdio.h>
    
    void longa() 
    {
        int i,j; 
        for(i = 0; i < 1000000; i++) 
            j=i;
    } 
    
    void foo2() 
    { 
        int i; 
        for(i=0 ; i < 10; i++) 
            longa(); 
    } 
    
    void foo1() 
    { 
        int i; 
        for(i = 0; i< 100; i++) 
            longa(); 
    } 
    
    int main() 
    { 
        foo1(); 
        foo2(); 
    }

编译： gcc -o t1 t1.c 。 

使用perf stat进行分析：

    # perf stat ./t1
    
     Performance counter stats for './t1':
    
            321.443559      task-clock (msec)         #    0.998 CPUs utilized          
                     1      context-switches          #    0.003 K/sec                  
                     5      cpu-migrations            #    0.016 K/sec                  
                   103      page-faults               #    0.320 K/sec                  
           774,890,910      cycles                    #    2.411 GHz                      (83.50%)
           554,286,739      stalled-cycles-frontend   #   71.53% frontend cycles idle     (83.26%)
            34,022,038      stalled-cycles-backend    #    4.39% backend  cycles idle     (66.39%)
           551,316,398      instructions              #    0.71  insns per cycle        
                                                      #    1.01  stalled cycles per insn  (83.19%)
           110,050,211      branches                  #  342.362 M/sec                    (83.48%)
                 6,445      branch-misses             #    0.01% of all branches          (83.52%)
    
           0.322204574 seconds time elapsed 从上面输出可以看出该程序属于CPU密集型程序，因为task-clock行显示0.998 CPU利用率，接近100%。

使用perf record进行采样：

    # perf record  -g ./t1                  # -g 表示生成函数调用图
    # perf report -i perf.data | more       # 对结果进行分析
        99.70%     0.00%  t1       libc-2.12.so       [.] __libc_start_main          
                     |
                     ---__libc_start_main
    
        99.70%     0.00%  t1       t1                 [.] main                       
                     |
                     ---main
                        __libc_start_main
    
        99.70%    99.70%  t1       t1                 [.] longa                      
                     |
                     ---longa
                        |          
                        |--90.89%-- foo1
                        |          main
                        |          __libc_start_main
                        |          
                         --9.11%-- foo2
                                   main
                                   __libc_start_main
    
        90.61%     0.00%  t1       t1                 [.] foo1                       
                     |
                     ---foo1
                        main
                        __libc_start_main
    
         9.09%     0.00%  t1       t1                 [.] foo2                       
                     |
                     ---foo2
                        main
                        __libc_start_main 从上面输出已经基本可以看出来问题所在了：
    
    1. 绝大部分CPU耗时都在longa函数中；
    2. 函数longa的调用流程分别是main->foo1->longa和main->foo2->longa；
    3. 其中main->foo1->longa这一分支上耗时最多；

上面示例比较简单，其实代码逻辑复杂之后，靠肉眼是很难看出来的，而通过perf工具就可以很容易分析出来。

### 4.2 找出系统CPU占用率最高的程序

如果当前系统的CPU使用率很高，这时我们可以直接对整个系统进行分析，找出CPU占用率最高的那个进程并找到其进程中最耗CPU的函数调用。

假设有以下代码:

    // t2.c 
    #include<stdio.h>
    
    void bar()
    {
        printf("bar....\n");
        int i;
        while(1)
            i++;
    }
    
    void foo()
    {
        printf("foo....\n");
        bar();
    }
    
    int main()
    {
        foo();
    }

编译： gcc -o t2 t2.c 。 

先提前运行该程序： ./t2 。 

使用perf top查看系统内实时的CPU消耗：

![][6]

可以看出该系统中的t2程序很耗时。

使用record采样整个系统的CPU使用：

    perf record -a -o cycle.perf -g sleep 10        # 采样所有CPU、输出到cycle.perf文件、生成函数调用图、采样10s后结束
    perf report -i cycle.perf | more                # 分析结果，显示所有比较耗费CPU的程序及函数调用，这里不再列了

### 4.3 利用perf和Flame Graph工具生成火焰图

perf及FlameGraph使用方式如下：

    git clone https://github.com/brendangregg/FlameGraph.git
     cd FlameGraph
     perf script -i perf.data &> perf.unfold            # 以下几个命令都是在FlameGraph目录下执行的
     perl stackcollapse-perf.pl perf.unfold &> perf.folded
     perl flamegraph.pl perf.folded > perf.svg

将perf.svg放在浏览器中查看：

![][7]

其中：

* 每个框代表一个栈里的一个函数
* Y轴代表栈深度（栈桢数）。最顶端的框显示正在运行的函数，这之下的框都是调用者。在下面的函数是上面函数的父函数
* X轴代表采样总量。从左到右并不代表时间变化，从左到右也不具备顺序性
* 框的宽度代表占用CPU总时间。宽的框代表的函数可能比窄的运行慢，或者被调用了更多次数。框的颜色深浅也没有任何意义
* 如果是多线程同时采样，采样总数会超过总时间

写一个脚本简化以上操作：

    cat perf_flame_graph.sh 
    #!/bin/bash
    
    time=0
    pid=0
    
    if [ $# -eq 1 ]; then
        time=$1
    elif [ $# -eq 2 ]; then
        time=$1
        pid=$2
    else
        echo "Usage: $0 seconds [pid]"
        exit 1
    fi
    
    if [ $pid -gt 0 ]; then
        perf record -a -g -p $pid -o perf.data &
    else
        perf record -a -g -o perf.data &
    fi
    
    PID=`ps aux| grep "perf record"| grep -v grep| awk '{print $2}'`
    
    if [ -n "$PID" ]; then
        sleep $time
        kill -s INT $PID
    fi  
    
    sleep 1     # wait until perf exite
    
    perf script -i perf.data &> perf.unfold
    perl stackcollapse-perf.pl perf.unfold &> perf.folded
    perl flamegraph.pl perf.folded >perf.svg
    
    echo "Output : perf.svg"

上面这个脚本仍然在FlameGraph目录下运行，可以带两个参数，第一个参数是采样时间，第二个参数是可选的，如果存在表示是采样的进程pid，否则就是采样整个系统。

## 5. Reference

[Perf – Linux下的系统性能调优工具，第 1 部分][8]

[Perf – Linux下的系统性能调优工具，第 2 部分][9]

[系统级性能分析工具 — Perf][10]

[0]: /sites/fUvyiim
[1]: /articles/dup?id=yQJVJrr
[2]: http://cpper.info/2017/06/04/perf.html?utm_source=tuicool&utm_medium=referral
[3]: /topics/11000069
[4]: /topics/11350023
[5]: http://img1.tuicool.com/VRNvEva.gif
[6]: http://img0.tuicool.com/vYZBNvn.png!web
[7]: http://img2.tuicool.com/fEza2au.png!web
[8]: http://www.ibm.com/developerworks/cn/linux/l-cn-perf1/
[9]: http://www.ibm.com/developerworks/cn/linux/l-cn-perf2/
[10]: http://blog.csdn.net/zhangskd/article/details/37902159
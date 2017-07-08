# 深入分析diskstats

Apr 11 th , 2014

## 背景

内核很多重要子系统均通过proc文件的方式，将自身的一些统计信息输出，方便最终用户查看各子系统的运行状态，这些统计信息被称为metrics。 直接查看metrics并不能获取到有用的信息，一般都是由特定的应用程序(htop/sar/iostat等)每隔一段时间读取相关metrics，并进行相应计算，给出更具用户可读性的输出。 常见的metrics文件有：

* cpu调度统计信息的/proc/stat
* cpu负载统计信息的/proc/loadavg

通用块设备层也有一个重要的统计信息

* /proc/diskstats 内核通过diskstats文件，将通用块设备层的一些重要指标以文件的形式呈现给用户。

因为本文档牵涉到通用块设备层很多细节，建议先了解IO调度器的相关知识。

## 初探diskstats

首先来看下diskstats里面都有些什么，下面截取的是一个diskstats文件内容：


    # cat /proc/diskstats 
       8       0 sda 8567 1560 140762 3460 0 0 0 0 0 2090 3440
       8       1 sda1 8565 1557 140722 3210 0 0 0 0 0 1840 3190
       8      16 sdb 8157 1970 140762 2940 0 0 0 0 0 1710 2890
       8      17 sdb1 8155 1967 140722 2900 0 0 0 0 0 1670 2850
       8      32 sdc 8920 1574 206410 7870 430 0 461 250 0 6820 8120
       8      33 sdc1 8918 1571 206370 7840 430 0 461 250 0 6790 8090
       8      48 sdd 209703 1628 341966 1318450 3109063 331428 943042901 9728000 0 8943570 11015280
       8      49 sdd1 209701 1625 341926 1318200 3109063 331428 943042901 9728000 0 8943320 11015030

虽然如上面提到的，这些数字看上去完全没有规律。不过若想研究内核通用块设备层的统计实现方式，还是得一个一个字段的分析。

简单的说，每一行对应一个块设备，分别有ram0-ram15、loop0-loop7、mtdblock0-mtdblock5，剩下的sdxx就是硬盘和分区了。 这里以sda设备的数据为例，分别列举各字段的意义：

    8       0 sda 8567 1560 140762 3460 0 0 0 0 0 2090 3440

根据内核文档[iostats.txt][0]中描述，各字段意义如下：

域 | Value | Quoted | 解释 
-|-|-|-
F1 | 8 | major number | 此块设备的主设备号 
F2 | 0 | minor mumber | 此块设备的次设备号 
F3 | sda | device name | 此块设备名字 
F4 | 8567 | reads completed successfully | 成功完成的读请求次数 
F5 | 1560 | reads merged | 读请求的次数 
F6 | 140762 | sectors read | 读请求的扇区数总和 
F7 | 3460 | time spent reading (ms) | 读请求花费的时间总和 
F8 | 0 | writes completed | 成功完成的写请求次数 
F9 | 0 | writes merged | 写请求合并的次数 
F10 | 0 | sectors written | 写请求的扇区数总和 
F11 | 0 | time spent writing (ms) | 写请求花费的时间总和 
F12 | 0 | I/Os currently in progress | 次块设备队列中的IO请求数 
F13 | 2090 | time spent doing I/Os (ms) | 块设备队列非空时间总和 
F14 | 3440 | weighted time spent doing I/Os (ms) | 块设备队列非空时间加权总和 

基本上都是数量、时间的累加值，按照读、写分开统计。

## 流程图

下图是Linux内核通用块设备层IO请求处理的完整流程，如图例所示，所有的统计相关处理均有用不同颜色标注。 在进行深入分析前，请大致浏览图片，对整个流程有一个大致印象。 

![[title text [alt text]]][1]

## 实现分析

### proc入口

在内核代码中grep “diskstats”即可找到定义在block/genhd.c中的diskstats_show函数。
    

    while ((hd = disk_part_iter_next(&piter))) {
      cpu = part_stat_lock();
      part_round_stats(cpu, hd);
      part_stat_unlock();
      seq_printf(seqf, "%4d %7d %s %lu %lu %llu "
             "%u %lu %lu %llu %u %u %u %u\n",
             MAJOR(part_devt(hd)), MINOR(part_devt(hd)),
             disk_name(gp, hd->partno, buf),
             part_stat_read(hd, ios[READ]),
             part_stat_read(hd, merges[READ]),
             (unsigned long long)part_stat_read(hd, sectors[READ]),
             jiffies_to_msecs(part_stat_read(hd, ticks[READ])),
             part_stat_read(hd, ios[WRITE]),
             part_stat_read(hd, merges[WRITE]),
             (unsigned long long)part_stat_read(hd, sectors[WRITE]),
             jiffies_to_msecs(part_stat_read(hd, ticks[WRITE])),
             part_in_flight(hd),
             jiffies_to_msecs(part_stat_read(hd, io_ticks)),
             jiffies_to_msecs(part_stat_read(hd, time_in_queue))
          );

此段代码用seq_printf函数将保存在hd_struct结构体内的统计信息组成了diskstats文件。

### 数据结构

用到的数据结构都定义在<linux/genhd.h>中，主要有disk_stats和hd_struct两个结构体，意义见注释：



    struct disk_stats {
        /*
         *sectors[0] <--> F6 
         *sectors[1] <--> F10
         */
        unsigned long sectors[2];    /* READs and WRITEs */
    
        /*
         *ios[0] <--> F4 
         *ios[1] <--> F8
         */
        unsigned long ios[2];
    
        /*
         *merges[0] <--> F5 
         *merges[1] <--> F9
         */
        unsigned long merges[2];
    
        /*
         *ticks[0] <--> F7 
         *ticks[1] <--> F11
         */
        unsigned long ticks[2];
    
        /*F13, time spent doing IOs*/
        unsigned long io_ticks;
    
        /*F14, weighted time spent doing I/Os (ms)  */
        unsigned long time_in_queue;
    };
    
    struct hd_struct {
      unsigned long stamp;
    
        /*F12 I/Os currently in progress*/
      atomic_t in_flight[2];
    
        /*如果支持SMP则需要使用“每CPU”变量，否则需要加锁*/
    #ifdef   CONFIG_SMP
      struct disk_stats __percpu *dkstats;
    #else
      struct disk_stats dkstats;
    #endif
      atomic_t ref;
      struct rcu_head rcu_head;
    };

### F7/F11 ticks

见下一节

### F4/F8 ios

如流程图所示，在每个IO结束后，都会调用blk_account_io_done函数，来对完成的IO进行统计。 blk_account_io_done统计了 ios(F4/F8)和ticks(F7/F11)，还处理了in_flight（后续节有分析）。


    static void blk_account_io_done(struct request *req)
    {
      /*
         * 不统计Flush请求，见 http://en.wikipedia.org/wiki/Disk_buffer#Cache_flushing
      */
      if (blk_do_io_stat(req) && !(req->cmd_flags & REQ_FLUSH_SEQ)) {
            /*
             * duration是当前时间（IO完成时间）减去此IO的入队时间（见流程图）
             */
          unsigned long duration = jiffies - req->start_time;
    
            /*从req获取请求类型：R / W*/
          const int rw = rq_data_dir(req);
          struct hd_struct *part;
          int cpu;
    
          cpu = part_stat_lock();
            /*获取请求对应的partition(part)*/
          part = req->part;
            /*
             * 该partition的ios[rw]加1
             * part_stat_inc定义在<linux/genhd.h>中
             * part_stat_inc这个宏用来处理SMP和非SMP的细节，见上面的结构体定义
             */
          part_stat_inc(cpu, part, ios[rw]);
    
            /*
             * 将此IO的存活时间加进ticks
             */
          part_stat_add(cpu, part, ticks[rw], duration);
    
          part_round_stats(cpu, part);
    
            /*
             *完成了一个IO，也就是in_flight（正在进行）的IO少了一个
             */
          part_dec_in_flight(part, rw);
    
          hd_struct_put(part);
          part_stat_unlock();
      }
    }

### F5/F9 merges

内核每执行一次Back Merge或Front Merge，都会调用drive_stat_acct。 其实in_flight也是在这个函数中统计的，new_io参数用来区分是新的IO，如果不是新IO则是在merge的时候调用的：
    

    static void drive_stat_acct(struct request *rq, int new_io)
    {
      struct hd_struct *part;
        /*从req获取请求类型：R / W*/
      int rw = rq_data_dir(rq);
      int cpu;
    
      if (!blk_do_io_stat(rq))
          return;
    
      cpu = part_stat_lock();
    
      if (!new_io) {
          part = rq->part;
            /*
             * 非新IO，merges[rw]加1
             */
          part_stat_inc(cpu, part, merges[rw]);
      } else {
            .....
          part_round_stats(cpu, part);
            /*
             * 新提交了一个IO，也就是in_flight（正在进行）的IO多了一个
             */
          part_inc_in_flight(part, rw);
      }
    
      part_stat_unlock();
    }

### F6/F10 sectors

读写扇区总数是在blk_account_io_completion函数中统计的，如流程图中所示，这个函数在每次IO结束后调用：


    static void blk_account_io_completion(struct request *req, unsigned int bytes)
    {
        if (blk_do_io_stat(req)) {
            const int rw = rq_data_dir(req);
            struct hd_struct *part;
            int cpu;
    
            cpu = part_stat_lock();
            part = req->part;
            /*
             *bytes是此IO请求的数据长度，右移9位等同于除以512，即转换成扇区数
             *然后加到sectors[rw]中
             */
            part_stat_add(cpu, part, sectors[rw], bytes >> 9);
            part_stat_unlock();
        }
    }

### F12 in_flight

in_flight这个统计比较特别，因为其他统计都是计算累加值，而它是记录当前队列中IO请求的个数。统计方法则是：

* 新IO请求插入队列（被merge的不算）后加1
* 完成一个IO后减1 实现见上面章节中的blk_account_io_done和drive_stat_acct函数内的注释。

### F14 time_in_queue

见下一节。

### F13 io_ticks

io_ticks统计块设备请求队列非空的总时间，统计时间点与in_flight相同，统计代码实现在part_round_stats_single函数中：


    static void part_round_stats_single(int cpu, struct hd_struct *part,
                      unsigned long now)
    {
      if (now == part->stamp)
          return;
        /*
         *块设备队列非空
         */
      if (part_in_flight(part)) {
            /*
             *统计加权时间 队列中IO请求个数 * 耗费时间
             */
          __part_stat_add(cpu, part, time_in_queue,
                  part_in_flight(part) * (now - part->stamp));
    
            /*
             *统计队列非空时间
             */
          __part_stat_add(cpu, part, io_ticks, (now - part->stamp));
      }
      part->stamp = now;
    }

整个代码实现的逻辑比较简单，在新IO请求插入队列（被merge不算），或完成一个IO请求的时候均执行如下操作：

* 队列为空
  1. 记下时间stamp = t1
* 队列不为空
  1. io_ticks[rw] += t2-t1
  1. time_in_queue += in_flight * (t2-t1)
  1. 记下时间stamp = t2

下面是一个实际的例子，示例io_ticks和time_in_queue的计算过程：

ID | Time | Ops | in_flight | stamp | gap | io_ticks | time_in_queue 
-|-|-|-|-|-|-|-
0 | 100.00 | 新IO请求入队 | 0 | 0 | —– | 0 | 0 
1 | 100.10 | 新IO请求入队 | 1 | 100.00 | 0.10 | 0.10 | 0.10 
3 | 101.20 | 完成一个IO请求 | 2 | 100.10 | 0.80 | 1.20 | 1.70 
4 | 103.50 | 完成一个IO请求 | 1 | 100.20 | 1.30 | 2.50 | 3.00 
5 | 153.50 | 新IO请求入队 | 0 | 103.50 | —– | 2.50 | 3.00 
6 | 154.50 | 完成一个IO请求 | 1 | 153.50 | 1.00 | 3.50 | 4.00 

总共时间 54.50s， IO队列非空时间3.50s

 Posted by ykrocku Apr 11 th , 2014 [Linux-kernel][2]

[0]: https://www.kernel.org/doc/Documentation/iostats.txt
[1]: ./img/LinuxBlockIO.png
[2]: http://ykrocku.github.io/blog/categories/linux-kernel/
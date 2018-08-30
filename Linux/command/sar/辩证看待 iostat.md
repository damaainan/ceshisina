## 辩证看待 iostat

来源：[http://io.upyun.com/2018/01/16/using-iostat-dialectically/](http://io.upyun.com/2018/01/16/using-iostat-dialectically/)

时间 2018-01-16 16:15:55


## 前言

 经常做系统分析会接触到很多有用的工具，比如 iostat,它是用来分析磁盘性能、系统 I/O 的利器。

 本文将重点介绍 iostat 命令的使用，并分析容易引起误解的几个指标。  

## iostat

```
iostat - Report Central Processing Unit (CPU) statistics and input/output statistics for devices and partitions.

```

 上面是 man 手册关于 iostat 命令的介绍，非常简单明了。iostat 是我们经常用来分析 cpu 负载和磁盘 I/O 情况的工具。

## iostat 基本使用

 常用命令（个人习惯）：

```
iostat -xk 2 10
```

 参数的解释可以查看 man 手册：

```
OPTIONS
       -c     Display the CPU utilization report.

       -d     Display the device utilization report.

       -g group_name { device [...] | ALL }
              Display statistics for a group of devices.  The iostat command reports statistics for each individual device in the list then a line of global statistics for the group displayed as group_name and made  up  of  all  the
              devices in the list. The ALL keyword means that all the block devices defined by the system shall be included in the group.

       -h     Make the Device Utilization Report easier to read by a human.

       -j { ID | LABEL | PATH | UUID | ... } [ device [...] | ALL ]
              Display  persistent  device  names.  Options  ID,  LABEL,  etc.  specify  the type of the persistent name. These options are not limited, only prerequisite is that directory with required persistent names is present in
              /dev/disk.  Optionally, multiple devices can be specified in the chosen persistent name type.  Because persistent device names are usually long, option -h is enabled implicitly with this option.

       -k     Display statistics in kilobytes per second.

       -m     Display statistics in megabytes per second.

       -N     Display the registered device mapper names for any device mapper devices.  Useful for viewing LVM2 statistics.

       -p [ { device [,...] | ALL } ]
              The -p option displays statistics for block devices and all their partitions that are used by the system.  If a device name is entered on the command line, then statistics for it and all its partitions  are  displayed.
              Last,  the  ALL  keyword  indicates  that  statistics  have to be displayed for all the block devices and partitions defined by the system, including those that have never been used. If option -j is defined before this
              option, devices entered on the command line can be specified with the chosen persistent name type.

       -T     This option must be used with option -g and indicates that only global statistics for the group are to be displayed, and not statistics for individual devices in the group.

       -t     Print the time for each report displayed. The timestamp format may depend on the value of the S_TIME_FORMAT environment variable (see below).

       -V     Print version number then exit.

       -x     Display extended statistics.

       -y     Omit first report with statistics since system boot, if displaying multiple records at given interval.

       -z     Tell iostat to omit output for any devices for which there was no activity during the sample period.
```

 简单讲，-x 参数能比较详细的给出一些指标，2 代表间隔时间为 2s，统计输出 10 次。

 上面的命令可以看到如下的输出：

```
avg-cpu:  %user   %nice %system %iowait  %steal   %idle
           0.40    0.00    0.49    0.42    0.00   98.69

Device:         rrqm/s   wrqm/s     r/s     w/s    rkB/s    wkB/s avgrq-sz avgqu-sz   await r_await w_await  svctm  %util
sda               0.00   253.00    0.02   10.26     0.66  2081.56   405.05     0.65   62.78    6.01   62.92   4.55   4.68
sdb               0.00     0.00    0.00    0.00     0.00     0.00     8.19     0.00    0.23    0.23    0.00   0.23   0.00
sdc               0.00     0.00    0.00    0.00     0.00     0.00     8.19     0.00    0.32    0.32    0.00   0.32   0.00
sdd               0.00     0.00    0.00    0.00     0.00     0.00     8.19     0.00    0.34    0.34    0.00   0.34   0.00
sde               0.00     0.00    0.00    0.00     0.00     0.00     8.19     0.00    0.34    0.34    0.00   0.34   0.00
```

 上面各个字段的解释如下（同样来自 man）

```
Device Utilization Report
              rrqm/s
                     The number of read requests merged per second that were queued to the device.

              wrqm/s
                     The number of write requests merged per second that were queued to the device.

              r/s
                     The number (after merges) of read requests completed per second for the device.

              w/s
                     The number (after merges) of write requests completed per second for the device.

              rsec/s (rkB/s, rMB/s)
                     The number of sectors (kilobytes, megabytes) read from the device per second.

              wsec/s (wkB/s, wMB/s)
                     The number of sectors (kilobytes, megabytes) written to the device per second.

              avgrq-sz
                     The average size (in sectors) of the requests that were issued to the device.

              avgqu-sz
                     The average queue length of the requests that were issued to the device.

              await
                     The average time (in milliseconds) for I/O requests issued to the device to be served. This includes the time spent by the requests in queue and the time spent servicing them.

              r_await
                     The average time (in milliseconds) for read requests issued to the device to be served. This includes the time spent by the requests in queue and the time spent servicing them.
                                   w_await
                     The average time (in milliseconds) for write requests issued to the device to be served. This includes the time spent by the requests in queue and the time spent servicing them.

              svctm
                     The average service time (in milliseconds) for I/O requests that were issued to the device. Warning! Do not trust this field any more.  This field will be removed in a future sysstat version.

              %util
                     Percentage of elapsed time during which I/O requests were issued to the device (bandwidth utilization for the device). Device saturation occurs when this value is close to 100%.
```

 上面的英文应该还是挺容易明白的，其中重点需要关注的是下面几个指标：
  

  * avgrq-sz：每个 IO 的平均扇区数，即所有请求的平均大小，以扇区（512字节）为单位
  * avgqu-sz：平均意义上的请求队列长度
  * await：平均每个 I/O 花费的时间，包括在队列中等待时间以及磁盘控制器中真正处理的时间
  * svctm：每个 I/O 的服务时间。但注意上面的解释  `Warning! Do not trust this field any more`。iostat 中关于每个 I/O 的真实处理时间不可靠    
  * util：磁盘繁忙程度，单位为百分比
  
 分析建议： 当系统性能下降时，我们往往需要着重关注上面列出来的 5 个参数，比如：

  

*  **`I/O 请求队列是否过长？`** 
    
*  **`I/O size 是否过大或过小？`** 
    
*  **`是否造成了 I/O 等待过长？`** 
    
*  **`每个 I/O 处理时间是否过大？`** 
    
*  **`磁盘压力是否过大？`** 
    
  
 综合分析上述指标，可以得到一定的性能分析结论，但需要注意一些陷阱。

## 注意陷进

 我们看到上面 iostat 的输出如下：

```
Device:         rrqm/s   wrqm/s     r/s     w/s    rkB/s    wkB/s avgrq-sz avgqu-sz   await r_await w_await  svctm  %util
sda               0.00   253.00    0.02   10.26     0.66  2081.56   405.05     0.65   62.78    6.01   62.92   4.55   4.68
```

 svctm 为 4.55 ms，即每个 I/O 处理时间为 4.55 ms，这其实是有点偏慢了，但是 await 却高达 62.78 ms，为何？

 上面可以看到总的 I/O 数为『读 I/O』+ 『写 I/O』 = 0.02 + 10.26 ≈ 11 个，假设这 11 个 I/O 是同时发起，且磁盘是顺序处理的情况，那么平均等待时间计算如下：

```
平均等待时间 = 单个 I/O 处理时间 * ( 1 + 2 + 3 + ...+ I/O 请求总数 - 1 ) / 请求总数 = 4.55 * （ 1 + 2 + 3 + ... + 10） / 11 = 22.75 ms
```

 解释如下：

 可以把 iostat 想像成 超市付款处，有 11 个顾客排队等待付款，只有一个收银员在服务，每个顾客处理时间为 4.55 ms，第一个顾客不需要等待，第二个顾客需要等待第一个顾客的处理时间，第三个顾客需要等待前面两位的处理时间…以此类推，所有等待时间为 单个 I/O 处理时间 * ( 1 + 2 + 3 + …+ I/O 请求总数 - 1 ).

 计算得到的平均等待时间为 22.75 ms，再加上单个 I/O 处理时间 4.55 ms 得到 27.3 ms:

```
22.75 + 4.55 = 27.3 ms
```

 27.3 ms 可以表征 iostat 中的 await 指标，因为 await 包括了等待时间和实际处理时间。但 iostat 的 await 为 62.78 ms，为何会比 iostat 得到的 await 值小这么多？ **`why?`** 
  

```
27.3 ms <  62.78 ms
```

 再次查看计算方法，步骤和原理都是正确的，但其中唯一不准确的变量就是单个 I/O 的处理时间 svctm！另外就是前提假定了磁盘是顺序处理 I/O 的。

 那么是不是 svctm 不准确呢？或者磁盘并不是顺序处理 I/O 请求的呢？


### 丢弃 svctm

 我们一直想要得到的指标是能够衡量磁盘性能的指标，也就是单个 I/O 的 service time。但是 service time 和 iostat 无关，iostat 没有任何一个参数能够提供这方面的信息。人们往往对 iostat 抱有过多的期待！

```
Warning! Do not trust this field any more. This field will be removed in a future sysstat version.

```

 man 手册中给出了这么一段模凌两可的警告，却没有说明原因。那么原因是什么呢？svctm 又是怎么得到的呢？

 iostat 命令来自[sysstat][0]工具包，翻阅源码可以在`rd_stats.c`找到 svctm 的计算方法，其实 svctm 的计算依赖于其他指标：  

```
/*
   ***************************************************************************
   * Compute "extended" device statistics (service time, etc.).
   *
   * IN:
   * @sdc     Structure with current device statistics.
   * @sdp     Structure with previous device statistics.
   * @itv     Interval of time in 1/100th of a second.
   *
   * OUT:
   * @xds     Structure with extended statistics.
   ***************************************************************************
  */
  void compute_ext_disk_stats(struct stats_disk *sdc, struct stats_disk *sdp,
               unsigned long long itv, struct ext_disk_stats *xds)
  {
      double tput
          = ((double) (sdc->nr_ios - sdp->nr_ios)) * 100 / itv;

      xds->util  = S_VALUE(sdp->tot_ticks, sdc->tot_ticks, itv);
      xds->svctm = tput ? xds->util / tput : 0.0;
      /*
      * Kernel gives ticks already in milliseconds for all platforms
      * => no need for further scaling.
      */
      xds->await = (sdc->nr_ios - sdp->nr_ios) ?
          ((sdc->rd_ticks - sdp->rd_ticks) + (sdc->wr_ticks - sdp->wr_ticks)) /
          ((double) (sdc->nr_ios - sdp->nr_ios)) : 0.0;
      xds->arqsz = (sdc->nr_ios - sdp->nr_ios) ?
          ((sdc->rd_sect - sdp->rd_sect) + (sdc->wr_sect - sdp->wr_sect)) /
          ((double) (sdc->nr_ios - sdp->nr_ios)) : 0.0;
  }
```

 其中重点关注：

```
xds->svctm = tput ? xds->util / tput : 0.0;
```

 学过 C 语言的都知道这是一个三元运算符：

```
A ? B : C
表示如果 A 为真，那么表达式值为 B，否则为 C
```

 tput可以理解为 IOPS，即当 IOPS 非零时，svctm 等于 util / tput；否则等于 0。  

 tput 相当于 IOPS，下文会作解释。

 上面说的 svctm 的计算依赖的值就是**`util`** ，那么 man 手册给出的警告应该废弃 svctm 的原因是不是因为 util 的计算不准确呢？  


### util 磁盘饱和度

 上面说到应该废弃 svctm 指标，因为它并不能作为衡量磁盘性能的指标，svctm 的计算是不准确的。但从上面的计算公式可以看到，唯一的不确定的变量是 util 的值。

 util 是用来衡量磁盘饱和度的指标，那么 util 是怎么计算的呢？还是上面的`compute_ext_disk_stats`函数：  

```
void compute_ext_disk_stats(struct stats_disk *sdc, struct stats_disk *sdp,
               unsigned long long itv, struct ext_disk_stats *xds)
  {
      double tput
          = ((double) (sdc->nr_ios - sdp->nr_ios)) * 100 / itv;

      xds->util  = S_VALUE(sdp->tot_ticks, sdc->tot_ticks, itv);
      ...
  }
```

 进一步阅读源码找到 S_VALUE 的定义：

```
#define S_VALUE(m,n,p)      (((double) ((n) - (m))) / (p) * 100)
```

 且上面的注释可以看到：

```
* @sdc        Structure with current device statistics.
   * @sdp        Structure with previous device statistics.
   * @itv        Interval of time in 1/100th of a second.
```

 最终得到 util 的计算方法为：

```
util = ( current_tot_ticks - previous_tot_ticks ) /  采样周期 * 100
```

 那么`tot_ticks`是什么呢？这里需要关注`stats_disk`这个结构体，查阅源码在`rd_stats.h`文件中：  

```
/* rd_stats.h */
/* Structure for block devices statistics */
struct stats_disk {
    unsigned long long nr_ios;
    unsigned long      rd_sect  __attribute__ ((aligned (8)));
    unsigned long      wr_sect  __attribute__ ((aligned (8)));
    unsigned int       rd_ticks __attribute__ ((aligned (8)));
    unsigned int       wr_ticks;
    unsigned int       tot_ticks;
    unsigned int       rq_ticks;
    unsigned int       major;
    unsigned int       minor;
};
```

 这里看不出具体每个字段是什么意义，源文件也没有作注释，接着看`rd_stats.c`文件是怎么对结构体赋值的，源文件`rd_stats.c`中：  

```
/*
   ***************************************************************************
   * Read block devices statistics from /proc/diskstats.
   *
*/
  __nr_t read_diskstats_disk(struct stats_disk *st_disk, __nr_t nr_alloc,int read_part)
  {
  ...
     if ((fp = fopen(DISKSTATS, "r")) == NULL)
          return 0;

      while (fgets(line, sizeof(line), fp) != NULL) {

          if (sscanf(line, "%u %u %s %lu %*u %lu %u %lu %*u %lu"
                 " %u %*u %u %u",
                 &major, &minor, dev_name,
                 &rd_ios, &rd_sec, &rd_ticks, &wr_ios, &wr_sec, &wr_ticks,
                 &tot_ticks, &rq_ticks) == 11) { ... }
...
}
```

 核心代码如上，具体来讲，iostat 的使用其实是依赖于`/proc/diskstats`文件，读取`/proc/diskstats`值，然后做进一步的分析处理。这里额外介绍下`/proc/diskstats`文件：  

```
[root@localhost ~]# cat /proc/diskstats
   1       0 ram0 0 0 0 0 0 0 0 0 0 0 0
   1       1 ram1 0 0 0 0 0 0 0 0 0 0 0
   1       2 ram2 0 0 0 0 0 0 0 0 0 0 0
   1       3 ram3 0 0 0 0 0 0 0 0 0 0 0
   1       4 ram4 0 0 0 0 0 0 0 0 0 0 0
   1       5 ram5 0 0 0 0 0 0 0 0 0 0 0
   1       6 ram6 0 0 0 0 0 0 0 0 0 0 0
   1       7 ram7 0 0 0 0 0 0 0 0 0 0 0
   1       8 ram8 0 0 0 0 0 0 0 0 0 0 0
   8       0 sda 82044583 3148 10966722840 222442157 24658460 2499170 2700969385 105371088 0 57897509 328196252
   8       1 sda1 4144 0 339790 2859 93359 82770 4180584 671453 0 534023 674311
   8       2 sda2 487 0 4114 28 0 0 0 0 0 28 28
   8       3 sda3 8450 0 206387 3489 598140 1719768 413807296 6739177 0 1204240 6742537
   8       4 sda4 82031488 3148 10966172437 222435779 23966958 696632 2282981505 97960444 0 57538914 321035535
   8      16 sdb 6696805 672 1028622736 99268437 3479149 1095853 385460280 4357778 0 80933531 103624000
   8      32 sdc 6535697 706 1003357408 101660311 3409287 1048913 370227528 4329287 0 82570947 105987603
   8      48 sdd 6555170 652 1005848496 98046714 3392381 1044610 369149464 4407316 0 80348361 102451899
   8      64 sde 6532011 671 1002703024 134576408 3406505 1054721 372497720 5792380 0 103162428 140366630
```

 每个字段的意义解释如下：

```
The /proc/diskstats file displays the I/O statistics
    of block devices. Each line contains the following 14
    fields:
     1 - major number
     2 - minor mumber
     3 - device name
     4 - reads completed successfully
     5 - reads merged
     6 - sectors read
     7 - time spent reading (ms)
     8 - writes completed
     9 - writes merged
    10 - sectors written
    11 - time spent writing (ms)
    12 - I/Os currently in progress
    13 - time spent doing I/Os (ms)
    14 - weighted time spent doing I/Os (ms)
```

 这里英文的解释可能没有很明白很清楚，尤其是第 7 、11、13 个字段的解释，我们再用中文解释一下：

| 域 | Value | Quoted | 解释 |
|-|-|-|-|
| F1 | 8 | major number | 此块设备的主设备号 |
| F2 | 0 | minor mumber | 此块设备的次设备号 |
| F3 | sda | device name | 此块设备名字 |
| F4 | 8567 | reads completed successfully | 成功完成的读请求次数 |
| F5 | 1560 | reads merged | 读请求的次数 |
| F6 | 140762 | sectors read | 读请求的扇区数总和 |
| F7 | 3460 | time spent reading (ms) | 读请求花费的时间总和 |
| F8 | 0 | writes completed | 成功完成的写请求次数 |
| F9 | 0 | writes merged | 写请求合并的次数 |
| F10 | 0 | sectors written | 写请求的扇区数总和 |
| F11 | 0 | time spent writing (ms) | 写请求花费的时间总和 |
| F12 | 0 | I/Os currently in progress | 次块设备队列中的IO请求数 |
| F13 | 2090 | time spent doing I/Os (ms) | 块设备队列非空时间总和 |
| F14 | 3440 | weighted time spent doing I/Os (ms) | 块设备队列非空时间加权总和 |
  

 这里需要特别对第 7、11、13 个字段做一点解释， 第 7 个字段表示所有读请求的花费时间总和，这里把每个读 I/O 请求都计算在内；同理是第 11 个字段；那么为什么还有第 13 个字段呢？第 13 个字段不关心有多少 I/O 在处理，它只关心设备是否在做 I/O 操作，所以真实情况是第 7 个字段加上第 11 个字段的值会比第 13 个字段的值更大一点。  

 回到`rd_stats.c`源中，`stats_disk`结构体如何赋值的呢？  

```
...
while (fgets(line, sizeof(line), fp) != NULL) 
...
sscanf(line, "%u %u %s %lu %*u %lu %u %lu %*u %lu"
                 " %u %*u %u %u",
                 &major, &minor, dev_name,
                 &rd_ios, &rd_sec, &rd_ticks, &wr_ios, &wr_sec, &wr_ticks,
                 &tot_ticks, &rq_ticks) == 11)
  ...
```

 使用 fgets 函数获得`/proc/diskstats`文件中的一行数据，然后使用 sscanf 函数格式化字符串到结构体`stats_disk`的不同成员变量中。仔细看代码，格式符号有 14 个，但接收字符串的变量只有 11 个，这里要注意的是 sscanf 的使用：  

```
sscanf 中 * 表示读入的数据将被舍弃。带有*的格式指令不对应可变参数列表中的任何数据。
```

 这么一来，我们要寻找的`tot_ticks`就是第 13 个字段，也就是表示：  

```
13 - time spent doing I/Os (ms)，即 花费在 I/O 上的时间
```

 我们再回到 util 的计算：

```
util = ( current_tot_ticks - previous_tot_ticks ) /  采样周期 * 100
```

 util 的计算方法是：统计一个周期内磁盘有多少自然时间(ms) 是用来做 I/O 的，得出百分比，代表磁盘饱和度。  

**上文对于 svctm 的计算提到 tput 这个变量代表 IOPS，这里额外做一点解释** ：  

```
/*rd_stats.c 中 read_diskstats_disk 函数内 */
/* 读 I/O + 写 I/O 数量 */
st_disk_i->nr_ios  = (unsigned long long) rd_ios + (unsigned long long) wr_ios;
...
/* rd_stats.c 中 compute_ext_disk_stats 函数内 */
/* 当前读写 I/O 数量 - 上一次采样时的读写 I/O 数量 */
double tput = ((double) (sdc->nr_ios - sdp->nr_ios)) * 100 / itv;
...
```

 经过对 `/proc/diskstats`各个字段的分析，不难得出，`stats_disk`结构体中的成员变量 `nr_ios`代表读写 I/O 成功完成的数量，也就是 IOPS。  

 再回过来，那么 util 的计算是准确的吗？`tot_ticks`的计算是准确的吗？  

 经过上面的分析，`tot_ticks`其实表示的是 `/proc/diskstats`文件中第 13 个字段，表示磁盘处理 I/O 操作的自然时间，不考虑并行性。那么由此得到的 util 就失去了最原本的意义。  

 举个简单的例子，假设磁盘处理单个 I/O 的能力为 0.01ms，依次有 200 个请求提交，需要 2s 处理完所有的请求，如果采样周期为 1s，在 1s 的采样周期里 util 就达到了 100%；但是如果这 200 个请求分批次的并发提交，比如每次并发提交 2 个请求，即每次同时过来 2 个请求，那么需要 1s 即可完成所有请求，采样周期为 1s，util 也是 100%。

 两种场景下 util 均是 100%，那一种磁盘压力更大？当然是第二种，但仅仅通过 util 并不能得出这个结论。

 再回到 svctm 的计算：

```
double tput  = ((double) (sdc->nr_ios - sdp->nr_ios)) * 100 / itv;
xds->util  = S_VALUE(sdp->tot_ticks, sdc->tot_ticks, itv);
xds->svctm = tput ? xds->util / tput : 0.0;
```

 转换上述两个式子可以得到：

```
svctm = ( current_tot_ticks - previous_tot_ticks ) / (current_ios - previous_ios ) = 采样周期内设备进行 I/O 的自然时间  /  采样周期内读写 I/O 次数
```

 故通过此表达式计算得到的 svctm 其实并不能准确衡量单个 I/O 的处理能力。如果磁盘没有并行处理的能力，那么采样周期内读写 I/O 次数必然减少，相应的，svctm 的计算就会偏大。

 那回到开头提出的疑问，假定顺序请求情况下得到的平均等待时间 27.3ms 小于 iostat 看到的 await 62.78ms:

```
27.3 ms <  62.78 ms
```

 现在可以解释了： 27.3 ms 的计算其实使用了偏小的 svctm 值，故得到的平均等待时间较 62.78ms 小很多。  

## iostat 辩证看待

 分析到这里，原理已经很明白了，util 并不能衡量磁盘的饱和度，svctm 的值失去了意义。期望通过这两个指标获得一个磁盘性能的衡量恐怕不行了！

  

 但平常的分析，我们可以参考 iostat 的输出，再结合其他的一些工具，进行多方面多方位的性能分析，才能得到比较接近真理的结论！

## 延伸

 上文分析了 iostat 容易引起误解的几个指标，在使用 iostat 时我们需要辩证的看待 iostat 的结果。

 但我们往往更希望获得一个能够衡量磁盘性能的指标，iostat 可能帮不上太多忙了，这时可能需要借助其他的工具了，比如 blktrace 这个工具，这才是分析 I/O 的利器！

## 参考


* [深入理解iostat][1]
    
* [容易被误读的IOSTAT][2]
    
* [深入分析diskstats][3]
    
* [[Linux 运维 – 存储] /proc/diskstats详解][4]
    
[0]: https://github.com/sysstat/sysstat
[1]: http://bean-li.github.io/dive-into-iostat/
[2]: http://linuxperf.com/?p=156
[3]: http://ykrocku.github.io/blog/2014/04/11/diskstats/
[4]: https://www.cnblogs.com/zk47/p/4733143.html
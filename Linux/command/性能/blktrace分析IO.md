# blktrace分析IO

 [首页][0]  [分类][1]  [标签][2]  [留言][3]  [关于][4]  [订阅][5]  2017-07-06 | 分类 [linux][6] | 标签 [linux][7] # 前言

上篇博客介绍了iostat的一些输出，这篇介绍blktrace这个神器。上一节介绍iostat的时候，我们心心念念希望得到块设备处理io的service time，而不是service time + wait time，因为对于评估一个磁盘或者云磁盘而言，service time才是衡量磁盘性能的核心指标和直接指标。很不幸iostat无法提供这个指标，但是blktrace可以。

blktrace是一柄神器，很多工具都是基于该神器的：ioprof，seekwatcher，iowatcher，这个工具基本可以满足我们的对块设备请求的所有了解。

# blktrace的原理

一个I/O请求，从应用层到底层块设备，路径如下图所示：

![][8]

从上图可以看出IO路径是很复杂的。这么复杂的IO路径我们是无法用短短一篇小博文介绍清楚的。我们将IO路径简化一下：

![][9]

一个I/O请求进入block layer之后，可能会经历下面的过程：

* Remap: 可能被DM(Device Mapper)或MD(Multiple Device, Software RAID) remap到其它设备
* Split: 可能会因为I/O请求与扇区边界未对齐、或者size太大而被分拆(split)成多个物理I/O
* Merge: 可能会因为与其它I/O请求的物理位置相邻而合并(merge)成一个I/O
* 被IO Scheduler依照调度策略发送给driver
* 被driver提交给硬件，经过HBA、电缆（光纤、网线等）、交换机（SAN或网络）、最后到达存储设备，设备完成IO请求之后再把结果发回。

blktrace 能够记录下IO所经历的各个步骤:

![][10]

我们一起看下blktrace的输出长什么样子：

![][11]

* 第一个字段：8,0 这个字段是设备号 major device ID和minor device ID。
* 第二个字段：3 表示CPU
* 第三个字段：11 序列号
* 第四个字段：0.009507758 Time Stamp是时间偏移
* 第五个字段：PID 本次IO对应的进程ID
* 第六个字段：Event，这个字段非常重要，反映了IO进行到了那一步
* 第七个字段：R表示 Read， W是Write，D表示block，B表示Barrier Operation
* 第八个字段：223490+56，表示的是起始block number 和 number of blocks，即我们常说的Offset 和 Size
* 第九个字段： 进程名

其中第六个字段非常有用：每一个字母都代表了IO请求所经历的某个阶段。

    Q – 即将生成IO请求
    |
    G – IO请求生成
    |
    I – IO请求进入IO Scheduler队列
    |
    D – IO请求进入driver
    |
    C – IO请求执行完毕
    

注意，整个IO路径，分成很多段，每一段开始的时候，都会有一个时间戳，根据上一段开始的时间和下一段开始的时间，就可以得到IO 路径各段花费的时间。

注意，我们心心念念的service time，也就是反应块设备处理能力的指标，就是从D到C所花费的时间，简称D2C。

而iostat输出中的await，即整个IO从生成请求到IO请求执行完毕，即从Q到C所花费的时间，我们简称Q2C。

我们知道Linux 有I/O scheduler，调度器的效率如何，I2D是重要的指标。

注意，这只是blktrace输出的一个部分，很明显，我们还能拿到offset和size，根据offset，我们能拿到某一段时间里，应用程序都访问了整个块设备的那些block，从而绘制出块设备访问轨迹图。

另外还有size和第七个字段（Read or Write），我们可以知道IO size的分布直方图。对于本文来讲，我们就是要根据blktrace来获取这些信息。

# blktrace、blkparse和btt

我们接下来简单介绍这些工具的使用，其中这三个命令都是属于blktrace这个包的，他们是一家人。

首先通过如下命令，可以查看磁盘上的实时信息：

     blktrace -d /dev/sdb -o – | blkparse -i –
    

这个命令会连绵不绝地出现很多输出，当你输入ctrl＋C的时候，会停止。

当然了，你也可以先用如下命令采集信息，待所有信息采集完毕后，统一分析所有采集到的数据。搜集信息的命令如下：

    blktrace -d /dev/sdb
    

注意，这个命令并不是只输出一个文件，他会根据CPU的个数上，每一个CPU都会输出一个文件，如下所示：

    -rw-r--r-- 1 manu manu  1.3M Jul  6 19:58 sdb.blktrace.0
    -rw-r--r-- 1 manu manu  823K Jul  6 19:58 sdb.blktrace.1
    -rw-r--r-- 1 manu manu  2.8M Jul  6 19:58 sdb.blktrace.10
    -rw-r--r-- 1 manu manu  1.9M Jul  6 19:58 sdb.blktrace.11
    -rw-r--r-- 1 manu manu  474K Jul  6 19:58 sdb.blktrace.12
    -rw-r--r-- 1 manu manu  271K Jul  6 19:58 sdb.blktrace.13
    -rw-r--r-- 1 manu manu  578K Jul  6 19:58 sdb.blktrace.14
    -rw-r--r-- 1 manu manu  375K Jul  6 19:58 sdb.blktrace.15
    -rw-r--r-- 1 manu manu  382K Jul  6 19:58 sdb.blktrace.16
    -rw-r--r-- 1 manu manu  478K Jul  6 19:58 sdb.blktrace.17
    -rw-r--r-- 1 manu manu  839K Jul  6 19:58 sdb.blktrace.18
    -rw-r--r-- 1 manu manu  848K Jul  6 19:58 sdb.blktrace.19
    -rw-r--r-- 1 manu manu  1.6M Jul  6 19:58 sdb.blktrace.2
    -rw-r--r-- 1 manu manu  652K Jul  6 19:58 sdb.blktrace.20
    -rw-r--r-- 1 manu manu  738K Jul  6 19:58 sdb.blktrace.21
    -rw-r--r-- 1 manu manu  594K Jul  6 19:58 sdb.blktrace.22
    -rw-r--r-- 1 manu manu  527K Jul  6 19:58 sdb.blktrace.23
    -rw-r--r-- 1 manu manu 1005K Jul  6 19:58 sdb.blktrace.3
    -rw-r--r-- 1 manu manu  1.2M Jul  6 19:58 sdb.blktrace.4
    -rw-r--r-- 1 manu manu  511K Jul  6 19:58 sdb.blktrace.5
    -rw-r--r-- 1 manu manu  2.3M Jul  6 19:58 sdb.blktrace.6
    -rw-r--r-- 1 manu manu  1.3M Jul  6 19:58 sdb.blktrace.7
    -rw-r--r-- 1 manu manu  2.1M Jul  6 19:58 sdb.blktrace.8
    -rw-r--r-- 1 manu manu  1.1M Jul  6 19:58 sdb.blktrace.9
    

有了输出，我们可以通过blkparse -i sdb来分析采集的数据：

      8,16   7     2147     0.999400390 630169  I   W 447379872 + 8 [kworker/u482:0]
      8,16   7     2148     0.999400653 630169  I   W 447380040 + 8 [kworker/u482:0]
      8,16   7     2149     0.999401057 630169  I   W 447380088 + 16 [kworker/u482:0]
      8,16   7     2150     0.999401364 630169  I   W 447380176 + 8 [kworker/u482:0]
      8,16   7     2151     0.999401521 630169  I   W 453543312 + 8 [kworker/u482:0]
      8,16   7     2152     0.999401843 630169  I   W 453543328 + 8 [kworker/u482:0]
      8,16   7     2153     0.999402195 630169  U   N [kworker/u482:0] 14
      8,16   6     5648     0.999403047 16921  C   W 347875880 + 8 [0]
      8,16   6     5649     0.999406293 16921  D   W 301856632 + 8 [ceph-osd]
      8,16   6     5650     0.999421040 16921  C   W 354834456 + 8 [0]
      8,16   6     5651     0.999423900 16921  D   W 301857280 + 8 [ceph-osd]
      8,16   7     2154     0.999442195 630169  A   W 425409840 + 8 <- (8,22) 131806512
      8,16   7     2155     0.999442601 630169  Q   W 425409840 + 8 [kworker/u482:0]
      8,16   7     2156     0.999444277 630169  G   W 425409840 + 8 [kworker/u482:0]
      8,16   7     2157     0.999445177 630169  P   N [kworker/u482:0]
      8,16   7     2158     0.999446341 630169  I   W 425409840 + 8 [kworker/u482:0]
      8,16   7     2159     0.999446773 630169 UT   N [kworker/u482:0] 1
      8,16   6     5652     0.999452685 16921  C   W 354834520 + 8 [0]
      8,16   6     5653     0.999455613 16921  D   W 301857336 + 8 [ceph-osd]
      8,16   6     5654     0.999470425 16921  C   W 393228176 + 8 [0]
      8,16   6     5655     0.999474127 16921  D   W 411554968 + 8 [ceph-osd]
      8,16   6     5656     0.999488551 16921  C   W 393228560 + 8 [0]
      8,16   6     5657     0.999491549 16921  D   W 411556112 + 8 [ceph-osd]
      8,16   6     5658     0.999594849 16923  C   W 393230152 + 16 [0]
      8,16   6     5659     0.999604038 16923  D   W 432877368 + 8 [ceph-osd]
      8,16   6     5660     0.999610322 16923  C   W 487390128 + 8 [0]
      8,16   6     5661     0.999614654 16923  D   W 432879632 + 8 [ceph-osd]
      8,16   6     5662     0.999628284 16923  C   W 487391344 + 8 [0]
      8,16   6     5663     0.999632014 16923  D   W 432879680 + 8 [ceph-osd]
      8,16   6     5664     0.999646122 16923  C   W 293759504 + 8 [0]
    
    

注意，blkparse仅仅是将blktrace输出的信息转化成人可以阅读和理解的输出，但是，信息太多，太杂，人完全没法得到关键信息。 这时候btt就横空出世了，这个工具可以将blktrace采集回来的数据，进行分析，得到对人更有用的信息。事实上，btt也是我们的终点。

接下来，我们要利用blktrace blkparse 以及btt来采集和分析单块磁盘的的性能，最终我会生成一个pdf的文档。步骤如下：

    输入： blktrace采集到的原始数据
    输出： 使用btt，blkparse还有自己写的一些bash脚本和python脚本，输出出pdf格式的report
    

* 通过各种工具，生成原始的分析结果，以及绘制对应的PNG图片：
* 将分析结果以表格和图片的方式，写入markdown文本
* 将markdown 文本通过pandoc转换成pdf文档。

# 获取个阶段的延迟信息

注意，btt已经可以很自如地生成这部分统计信息，我们可以很容易得到如下的表格：

![][12]

方法如下：

首先blkparse可以将对应不同cpu的多个文件聚合成一个文件：

    blkparse -i sdb -d sdb.blktrace.bin
    

然后btt就可以分析这个sdb.blktrace.bin了：

    ==================== All Devices ====================
    
                ALL           MIN           AVG           MAX           N
    --------------- ------------- ------------- ------------- -----------
    
    Q2Q               0.000000001   0.000159747   0.025292639       62150
    Q2G               0.000000233   0.000001380   0.000056343       52423
    G2I               0.000000146   0.000027084   0.005031317       48516
    Q2M               0.000000142   0.000000751   0.000021613        9728
    I2D               0.000000096   0.001534463   0.022469688       52423
    M2D               0.000000647   0.002617691   0.022445412        5821
    D2C               0.000046189   0.000779355   0.007860766       62151
    Q2C               0.000051089   0.002522832   0.026096657       62151
    
    ==================== Device Overhead ====================
    
           DEV |       Q2G       G2I       Q2M       I2D       D2C
    ---------- | --------- --------- --------- --------- ---------
     (  8, 16) |   0.0461%   0.8380%   0.0047%  51.3029%  30.8921%
    ---------- | --------- --------- --------- --------- ---------
       Overall |   0.0461%   0.8380%   0.0047%  51.3029%  30.8921%
    
    ==================== Device Merge Information ====================
    
           DEV |       #Q       #D   Ratio |   BLKmin   BLKavg   BLKmax    Total
    ---------- | -------- -------- ------- | -------- -------- -------- --------
     (  8, 16) |    62151    52246     1.2 |        1       20      664  1051700
    
    ==================== Device Q2Q Seek Information ====================
    
           DEV |          NSEEKS            MEAN          MEDIAN | MODE           
    ---------- | --------------- --------------- --------------- | ---------------
     (  8, 16) |           62151      42079658.0               0 | 0(17159)
    ---------- | --------------- --------------- --------------- | ---------------
       Overall |          NSEEKS            MEAN          MEDIAN | MODE           
       Average |           62151      42079658.0               0 | 0(17159)
    
    ==================== Device D2D Seek Information ====================
    
           DEV |          NSEEKS            MEAN          MEDIAN | MODE           
    ---------- | --------------- --------------- --------------- | ---------------
     (  8, 16) |           52246      39892356.2               0 | 0(9249)
    ---------- | --------------- --------------- --------------- | ---------------
       Overall |          NSEEKS            MEAN          MEDIAN | MODE           
       Average |           52246      39892356.2               0 | 0(9249)
    

注意： D2C和Q2C，一个是表征块设备性能的关键指标，另一个是客户发起请求到收到响应的时间，我们可以看出，

D2C 平均在0.000779355 秒，即0.7毫秒 Q2C 平均在0.002522832 秒，即2.5毫秒，

无论是service time 还是客户感知到的await time，都是非常短的，表现非常不俗。但是D2C花费的时间只占整个Q2C的30%， 51%以上的时间花费在I2D。

下面我们看下D2C和Q2C随着时间的分布情况：

![][13]

![][14]

绘制图片需要的信息可以通过如下指令得到：

    btt -i sdb.blktrace.bin -l sdb.d2c_latency
    btt -i sdb.blktrace.bin -q sdb.q2c_latency
    

# IOPS 和 MBPS

从btt出发，我们分析出来采样时间内，整个块设备的IOPS：

![][15]

![][16]

获取方法如下：

* blkparse -i sdb -d sdb.blktrace.bin
* btt -i sdb.blktrace.bin -q sdb.q2c_latency

注意，这一步之后，我们会得到如下文件：

* sdb.q2c_latency_8,16_q2c,dat
* sys_iops_fp.dat
* sys_mbps_fp.dat
* 8,16_iops_fp.dat
* 8,17_mbps_fp.dat

注意，如果我们blktrace －d sdb，只关注sdb的时候，我们可以通过sys_iops_fp.dat和sys_mbps_fp.dat获取对应的IOPS和MBPS信息：

    cat sys_iops_fp.dat 
    0 3453
    1 4859
    2 7765
    3 6807
    4 4804
    5 4345
    6 2501
    7 10291
    8 2767
    9 4654
    
    

# IO Size Historgram

我们很关心，在采样的时间内，IO size的分布情况，因为这个可以得到，过去的时间里，我们是大IO居多还是小IO居多：

![][17]

步骤如下：

* blkparse -i sdb -d sub.blktrace.bin
* btt -i sdb.blktrace.bin -B sdb.offset

这个步骤之后会生成三个文件：

* sdb.offset_8,16_r.dat
* sdb.offset_8,16_w.dat
* sdb.offset_8,16_c.dat

其中r表示读操作的offset和size信息，w表示写操作的offset和size信息，c表示读＋写。

其输出格式如下：

     cat sdb.offset_8,16_w.dat
        0.000006500 74196632 74196656
        0.000194981 74196656 74196680
        0.000423532 21923304 21923336
        0.000597505 60868864 60868912
        0.001046757 20481496 20481520
        0.001137646 20481520 20481544
        0.002203609 21923336 21923360
        0.002288944 60868912 60868936
        0.002329903 21923360 21923384
        0.002895619 60868936 60868960
        0.004535853 20481544 20481576
        0.004841878 74196680 74196704
        0.004888624 60868960 60869008
        0.004991469 74196704 74196744
        0.005799109 74196744 74196800
        0.005880756 74196800 74196856
        0.007083202 74196856 74196880
        0.007172808 74196880 74196952
        0.007969956 60869008 60869040
        0.008297881 74196952 74196976
        0.008540390 74196976 74197000
        0.009995244 60869040 60869072
        0.010516189 74197000 74197032
        0.011409120 60869072 60869096
        0.011554233 60869096 60869120
        0.011996171 74197032 74197104
        ....
        9.908351667 74389976 74390000
        9.909115545 74390000 74390024
        9.909160991 74390024 74390048
        9.909688260 20665552 20665600
        9.909987699 61083560 61083584
        9.910271958 61083584 61083608
        9.911689305 20665600 20665624
        9.911785890 20665624 20665648
        9.917379146 20665648 20665672
        9.917471753 20665672 20665696
        9.928170104 74390048 74390072
        9.928249913 74390072 74390096
    

注意，第一个字段是时间，第二个字段是开始扇区即offset，第三个字段为结束扇区。不难根据第二个字段和第三个字段算出来size。当然了单位为扇区。

# 访问轨迹图

注意上小节，可以拿到不同时间里，访问磁盘的位置以及访问扇区的个数，如果不考虑访问扇区的个数，我们可以得到一张访问轨迹2D图：

![][18]

如果把访问扇区的个数作为第三个维度，可以得到一张三维图

![][19]



[0]: http://bean-li.github.io/
[1]: http://bean-li.github.io/categories/
[2]: http://bean-li.github.io/tags/
[3]: http://bean-li.github.io/guestbook/
[4]: http://bean-li.github.io/about/
[5]: http://bean-li.github.io/feed/
[6]: http://bean-li.github.io/categories/#linux
[7]: http://bean-li.github.io/tags/#linux
[8]: ./img/Linux-storage-stack-diagram_v4.0.png
[9]: ./img/io_path_simple.png
[10]: ./img/blktrace_architecture.png
[11]: ./img/blktrace_out.png
[12]: ./img/latency_distribution_table.png
[13]: ./img/sdb.d2c_latency.png
[14]: ./img/sdb.q2c_latency.png
[15]: ./img/sdb_iops.png
[16]: ./img/sdb_mbps.png
[17]: ./img/sdb_iosize_hist.png
[18]: ./img/sdb_offset.png
[19]: ./img/sdb_offset_pattern.png
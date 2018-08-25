## sar —— Linux 上最为全面的系统性能分析工具之一

来源：[http://www.jianshu.com/p/09420b980c13](http://www.jianshu.com/p/09420b980c13)

时间 2018-04-26 14:39:32

 `sar`（System Activity Reporter 系统活动情况报告）是目前 Linux 上最为全面的系统性能分析工具之一，可以从多方面对系统的活动进行报告，包括：文件的读写情况、系统调用的使用情况、磁盘 I/O、CPU 效率、内存使用状况、进程活动及 IPC 有关的活动等。
 
我们可以使用`sar`命令来获得整个系统性能的报告。这有助于我们定位系统性能的瓶颈，并且有助于我们找出这些烦人的性能问题的解决方法。
 
Linux 内核维护着一些内部计数器，这些计数器包含了所有的请求及其完成时间和 I/O 块数等信息，`sar`命令从所有的这些信息中计算出请求的利用率和比例，以便找出瓶颈所在。
 `sar`命令主要的用途是生成某段时间内所有活动的报告，因此必需确保`sar`命令在适当的时间进行数据采集（而不是在午餐时间或者周末）
 
原文地址: [https://shockerli.net/post/linux-tool-sar/][1]
 
## 命令参数
 
```
用法: sar [ 选项 ] [ <时间间隔> [ <次数> ] ]

主选项和报告：
    -b  I/O 和传输速率信息状况
    -B  分页状况
    -d  块设备状况
    -I { <中断> | SUM | ALL | XALL }
        中断信息状况
    -m  电源管理信息状况
    -n { <关键词> [,...] | ALL }
        网络统计信息
        关键词可以是：
        DEV  网卡
        EDEV     网卡 (错误)
        NFS  NFS 客户端
        NFSD     NFS 服务器
        SOCK     Sockets (套接字)  (v4)
        IP  IP  流         (v4)
        EIP  IP 流      (v4) (错误)
        ICMP     ICMP 流 (v4)
        EICMP    ICMP 流 (v4) (错误)
        TCP  TCP 流  (v4)
        ETCP     TCP 流  (v4) (错误)
        UDP  UDP 流  (v4)
        SOCK6    Sockets (套接字)  (v6)
        IP6  IP 流      (v6)
        EIP6     IP 流      (v6) (错误)
        ICMP6    ICMP 流 (v6)
        EICMP6 ICMP 流 (v6) (错误)
        UDP6    UDP 流       (v6)
    -q  队列长度和平均负载
    -r  内存利用率
    -R  内存状况
    -S  交换空间利用率
    -u [ ALL ]
        CPU 利用率
    -v  Kernel table 状况
    -w  任务创建与系统转换统计信息
    -W  交换信息
    -y  TTY 设备状况
    -o {<文件路径>}
       将命令结果以二进制格式存放在指定文件中
```
 
## 常用命令
 `sar`命令来自于`sysstat`工具包，如果提示`sar`命令不存在，需先安装`sysstat`。
 
### 网络统计信息
 
```sh
sar -n <关键词> [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -n DEV 1 5
    
平均时间:     IFACE   rxpck/s   txpck/s    rxkB/s    txkB/s   rxcmp/s   txcmp/s  rxmcst/s
平均时间:        lo      2.21      2.21      0.18      0.18      0.00      0.00      0.00
平均时间:      eth0      4.62      3.82      0.37      1.90      0.00      0.00      0.00
```
 
命令中 1 5 表示每一秒钟取 1 次值，一共取 5 次。
 
命令执行后会列出每个网卡这 5 次取值的平均数据，根据实际情况来确定带宽跑满的网卡名称，默认情况下 eth0 为内网网卡，eth1 为外网网卡。
 
### CPU 利用率
 
```sh
sar -u [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -u 1 3
    
Linux 2.6.32-642.13.1.el6.x86_64 (upfor106) 2018年04月25日 _x86_64_ (1 CPU)
    
10时33分08秒     CPU     %user     %nice   %system   %iowait    %steal     %idle
10时33分09秒     all      0.00      0.00      0.00      0.00      0.00    100.00
10时33分10秒     all      0.99      0.00      0.99      0.00      0.00     98.02
10时33分11秒     all      0.00      0.00      0.00      0.00      0.00    100.00
平均时间:        all      0.33      0.00      0.33      0.00      0.00     99.33
```
 
命令中 1 3 表示每一秒钟取 1 次值，一共取 3 次。
 
#### 输出项说明：
 
```sh
CPU：all 表示统计信息为所有 CPU 的平均值。

%user：显示在用户级别(application)运行使用 CPU 总时间的百分比

%nice：显示在用户级别，用于nice操作，所占用 CPU 总时间的百分比

%system：在核心级别(kernel)运行所使用 CPU 总时间的百分比

%iowait：显示用于等待I/O操作占用 CPU 总时间的百分比

%steal：管理程序(hypervisor)为另一个虚拟进程提供服务而等待虚拟 CPU 的百分比

%idle：显示 CPU 空闲时间占用 CPU 总时间的百分比

    1. 若 %iowait 的值过高，表示硬盘存在I/O瓶颈

    2. 若 %idle 的值高但系统响应慢时，有可能是 CPU 等待分配内存，此时应加大内存容量

    3. 若 %idle 的值持续低于1，则系统的 CPU 处理能力相对较低，表明系统中最需要解决的资源是 CPU
```
 
### 索引节点，文件和其他内核表的状态
 
```sh
sar -v [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -v 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

10时50分29秒 dentunusd   file-nr  inode-nr    pty-nr
10时50分30秒    920045       864     13910         2
10时50分31秒    920045       864     13910         2
10时50分32秒    920045       896     13910         2
平均时间:       920045       875     13910         2
```
 
#### 输出项说明：
 
```sh
dentunusd：目录高速缓存中未被使用的条目数量

file-nr：文件句柄（file handle）的使用数量

inode-nr：索引节点句柄（inode handle）的使用数量

pty-nr：使用的 pty 数量
```
 
### 内存利用率
 
```sh
sar -r [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -r 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

10时53分00秒 kbmemfree kbmemused  %memused kbbuffers  kbcached  kbcommit   %commit
10时53分01秒   2027760   2028732     50.01    145492   1243820   1163900     28.69
10时53分02秒   2027620   2028872     50.02    145492   1243820   1163896     28.69
10时53分03秒   2028100   2028392     50.00    145492   1243820   1163900     28.69
平均时间:      2027827   2028665     50.01    145492   1243820   1163899     28.69
```
 
#### 输出项说明：
 
```sh
kbmemfree：这个值和 free 命令中的 free 值基本一致，所以它不包括 buffer 和 cache 的空间

kbmemused：这个值和 free 命令中的 used 值基本一致,所以它包括 buffer 和 cache 的空间

%memused：这个值是 kbmemused 和内存总量(不包括 swap)的一个百分比

kbbuffers 和 kbcached：这两个值就是 free 命令中的 buffer 和 cache

kbcommit：保证当前系统所需要的内存，即为了确保不溢出而需要的内存(RAM + swap)

%commit：这个值是 kbcommit 与内存总量(包括 swap)的一个百分比
```
 
### 内存分页状况
 
```sh
sar -B [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -B 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

10时55分41秒  pgpgin/s pgpgout/s   fault/s  majflt/s  pgfree/s pgscank/s pgscand/s pgsteal/s    %vmeff
10时55分42秒      0.00      0.00   5723.76      0.00   3356.44      0.00      0.00      0.00      0.00
10时55分43秒      0.00      0.00   1185.00      0.00    312.00      0.00      0.00      0.00      0.00
10时55分44秒      0.00      0.00     27.00      0.00     56.00      0.00      0.00      0.00      0.00
平均时间:         0.00      0.00   2323.26      0.00   1248.50      0.00      0.00      0.00      0.00
```
 
#### 输出项说明：
 
```sh
pgpgin/s：表示每秒从磁盘或SWAP置换到内存的字节数(KB)

pgpgout/s：表示每秒从内存置换到磁盘或SWAP的字节数(KB)

fault/s：每秒钟系统产生的缺页数，即主缺页与次缺页之和(major + minor)

majflt/s：每秒钟产生的主缺页数

pgfree/s：每秒被放入空闲队列中的页个数

pgscank/s：每秒被 kswapd 扫描的页个数

pgscand/s：每秒直接被扫描的页个数

pgsteal/s：每秒钟从 cache 中被清除来满足内存需要的页个数

%vmeff：每秒清除的页(pgsteal)占总扫描页(pgscank + pgscand)的百分比
```
 
### I/O 和传输速率信息状况
 
```sh
sar -b [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -b 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

10时58分15秒       tps      rtps      wtps   bread/s   bwrtn/s
10时58分16秒      7.00      0.00      7.00      0.00     64.00
10时58分17秒      4.04      0.00      4.04      0.00     80.81
10时58分18秒      0.00      0.00      0.00      0.00      0.00
平均时间:         3.67      0.00      3.67      0.00     48.00
```
 
#### 输出项说明：
 
```sh
tps：每秒钟物理设备的 I/O 传输总量

rtps：每秒钟从物理设备读入的数据总量

wtps：每秒钟向物理设备写入的数据总量

bread/s：每秒钟从物理设备读入的数据量，单位为：块/s

bwrtn/s：每秒钟向物理设备写入的数据量，单位为：块/s
```
 
### 队列长度和平均负载
 
```sh
sar -q [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -q 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

11时00分35秒   runq-sz  plist-sz   ldavg-1   ldavg-5  ldavg-15
11时00分36秒         0       268      0.00      0.00      0.00
11时00分37秒         0       268      0.00      0.00      0.00
11时00分38秒         0       268      0.00      0.00      0.00
平均时间:            0       268      0.00      0.00      0.00
```
 
#### 输出项说明：
 
```sh
runq-sz：运行队列的长度（等待运行的进程数）

plist-sz：进程列表中进程（processes）和线程（threads）的数量

ldavg-1：最后1分钟的系统平均负载（System load average）

ldavg-5：过去5分钟的系统平均负载

ldavg-15：过去15分钟的系统平均负载
```
 
### 系统交换信息
 
```sh
sar -W [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -W 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

11时01分45秒  pswpin/s pswpout/s
11时01分46秒      0.00      0.00
11时01分47秒      0.00      0.00
11时01分48秒      0.00      0.00
平均时间:         0.00      0.00
```
 
#### 输出项说明：
 
```sh
pswpin/s：每秒系统换入的交换页面（swap page）数量
pswpout/s：每秒系统换出的交换页面（swap page）数量
```
 
### 块设备状况
 
```sh
sar -d [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -d 1 3

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

11时02分46秒       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
11时02分47秒  dev252-0      0.00      0.00      0.00      0.00      0.00      0.00      0.00      0.00

11时02分47秒       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
11时02分48秒  dev252-0      6.06      0.00     64.65     10.67      0.00      0.00      0.00      0.00

11时02分48秒       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
11时02分49秒  dev252-0      0.00      0.00      0.00      0.00      0.00      0.00      0.00      0.00

平均时间:       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
平均时间:  dev252-0      2.00      0.00     21.33     10.67      0.00      0.00      0.00      0.00
```
 
#### 输出项说明：
 
```sh
tps: 每秒从物理磁盘 I/O 的次数。多个逻辑请求会被合并为一个 I/O 磁盘请求，一次传输的大小是不确定的

rd_sec/s: 每秒读扇区的次数

wr_sec/s: 每秒写扇区的次数

avgrq-sz: 平均每次设备 I/O 操作的数据大小(扇区)

avgqu-sz: 磁盘请求队列的平均长度

await: 从请求磁盘操作到系统完成处理，每次请求的平均消耗时间，包括请求队列等待时间，单位是毫秒(1秒=1000毫秒)

svctm: 系统处理每次请求的平均时间,不包括在请求队列中消耗的时间.

%util: I/O请求占CPU的百分比，比率越大，说明越饱和
    1. avgqu-sz 的值较低时，设备的利用率较高
    2. 当%util的值接近 1% 时，表示设备带宽已经占满
```
 
### 输出统计的数据信息
 
```sh
sar -o path_file [选项] [ <时间间隔> [ <次数> ] ]
```
 
#### 示例：
 
```sh
sar -o sarfile.log -u 1 3
```
 
上述示例命令会将`sar -u 1 3`采集到的数据以二进制的格式存放到文件`sarfile.log`中。
 
我们还可以通过命令`sadf -d sarfile.log`将二进制数据文件转换成数据库可读的格式。
 
```sh
sadf -d sarfile.log

# hostname;interval;timestamp;CPU;%user;%nice;%system;%iowait;%steal;%idle
upfor163;1;2018-04-25 03:15:02 UTC;-1;0.00;0.00;0.50;0.50;0.00;99.00
upfor163;1;2018-04-25 03:15:03 UTC;-1;1.01;0.00;0.00;0.00;0.00;98.99
upfor163;1;2018-04-25 03:15:04 UTC;-1;0.00;0.00;0.00;0.00;0.00;100.00
```
 
也可以将这些数据存储在一个 csv 文档中，然后绘制成图表展示方式，如下所示：
 
```sh
sadf -d sarfile.log | sed 's/;/,/g' > sarfile.csv
```
 
  
   
   
![][0]


 
sadf 导出数据绘制的图表
 
 
 
### 从数据文件读取信息
 
```sh
sar -f <文件路径>
```
 
#### 示例：
 
```sh
sar -f sarfile.log

Linux 2.6.32-696.13.2.el6.x86_64 (upfor163) 2018年04月25日 _x86_64_ (2 CPU)

11时15分01秒     CPU     %user     %nice   %system   %iowait    %steal     %idle
11时15分02秒     all      0.00      0.00      0.50      0.50      0.00     99.00
11时15分03秒     all      1.01      0.00      0.00      0.00      0.00     98.99
11时15分04秒     all      0.00      0.00      0.00      0.00      0.00    100.00
平均时间:     all      0.33      0.00      0.17      0.17      0.00     99.33
```
 
又将之前存储在二进制文件中的数据给读取并展示出来。
 
## 性能问题排查技巧
 

* 怀疑 CPU 存在瓶颈，可用`sar -u`和`sar -q`等来查看
  
* 怀疑内存存在瓶颈，可用`sar -B`、`sar -r`和`sar -W`等来查看
  
* 怀疑 I/O 存在瓶颈，可用`sar -b`、`sar -u`和`sar -d`等来查看
  
 

原文地址: [https://shockerli.net/post/linux-tool-sar/][1]
 


[1]: https://link.jianshu.com?t=https%3A%2F%2Fshockerli.net%2Fpost%2Flinux-tool-sar%2F
[2]: https://link.jianshu.com?t=https%3A%2F%2Fshockerli.net%2Fpost%2Flinux-tool-sar%2F
[0]: ../img/MJvYjif.jpg
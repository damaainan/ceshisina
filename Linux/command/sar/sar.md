# Linux 性能工具 sar 示例

 2016年02月24日发布 


# 安装配置 Sysstat

## 安装 Sysstat 包

    ### Ubuntu
    sudo apt-get install sysstat
    ### CentOS
    yum install sysstat
    ### CentOS
    rpm -ivh sysstat-10.0.0-1.i586.rpm

## 源码安装 Sysstat

从 [sysstat 下载页][7]下载最新版本

    wget http://pagesperso-orange.fr/sebastien.godard/sysstat-10.0.0.tar.bz2
    
    tar xvfj sysstat-10.0.0.tar.bz2
    
    cd sysstat-10.0.0
    
    ./configure --enable-install-cron

> 注意：请编译的时候确保使用 > --enable-install-cron>  选项，因为它会自动帮你做以下事情，如果没有使用这个选项，需要你手工处理以下事情

* 创建 /etc/rc.d/init.d/sysstat
* 从 /etc/rc.d/rc*.d/ 目录创建软连接到 /etc/rc.d/init.d/sysstat 以便 linux 启动的时候自动启动 sysstat
* 比如，/etc/rc.d/rc3.d/S01sysstat 被自动链接到 /etc/rc.d/init.d/sysstat

然后在执行 ./configure 后，执行以下步骤安装。

    make
    
    make install

> 注意：这将把 > sar>  以及其他的 > systat>  工具放在 > /usr/local/bin>  目录下。

一旦安装完成后，可以使用 sar -V 命令查看 sar 版本。

    $ sar -V
    sysstat version 10.0.0
    (C) Sebastien Godard (sysstat  orange.fr)

确保 sar 可以正常工作，以下示例给出了系统 CPU 统计数据 3 次（1 秒一次）

    $ sar 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:27:32 PM       CPU     %user     %nice   %system   %iowait    %steal     %idle
    01:27:33 PM       all      0.00      0.00      0.00      0.00      0.00    100.00
    01:27:34 PM       all      0.25      0.00      0.25      0.00      0.00     99.50
    01:27:35 PM       all      0.75      0.00      0.25      0.00      0.00     99.00
    Average:          all      0.33      0.00      0.17      0.00      0.00     99.50

## Sysstat 工具列表

* sar - 收集和展示系统的所有活动数据统计。
* sadc - 代表“系统活动数据收集器”。 这个是 sar 用于数据收集的后台工具。
* sa1 - 存储系统活动数据在二进制文件中。sa1 依赖于 sadc。sa1 运行在定时任务中。
* sa2 - 创建收集的日总结报告。sa2 在定时任务中运行。
* sadf - 可以以 CSV，XML，和其他各种格式生成 sar 报告。
* iostat - 生成 CPU，I/O 统计数据。
* mpstat - 展示 CPU 统计数据。
* pidstat - 基于线程 PID 报告统计数据。
* nfsiostat - 展示 NFS I/O 统计数据。
* cifsiostat - 生成 CIFS 统计数据。

## 使用定时任务收集 sar 统计数据

在 /etc/cron.d 目录创建 sysstat 文件来收集和归档 sar 数据

    # vi /etc/cron.d/sysstat
    */10 * * * * root /usr/local/lib/sa/sa1 1 1
    53 23 * * * root /usr/local/lib/sa/sa2 -A

如果你是通过源码安装的，sa1 和 sa2 的默认位置为 /usr/local/lib/sa。如果你使用包管理器安装（如 yum, up2date, 或者 apt-get），可能位于 /usr/lib/sa/sa1 和 /usr/lib/sa/sa2。

> 为了理解定时任务，请读 [> Linux Crontab: 15 Awesome Cron Job Examples][8]> 。

### /usr/local/lib/sa/sa1

* This runs every 10 minutes and collects sar data for historical reference.
* If you want to collect sar statistics every 5 minutes, change _/10 to_/5 in the above /etc/cron.d/sysstat file.
* This writes the data to /var/log/sa/saXX file. XX is the day of the month. saXX file is a binary file. You cannot view its content by opening it in a text editor.
* For example, If today is 26th day of the month, sa1 writes the sar data to /var/log/sa/sa26
* You can pass two parameters to sa1: interval (in seconds) and count.
* In the above crontab example: sa1 1 1 means that sa1 collects sar data 1 time with 1 second interval (for every 10 mins).

### /usr/local/lib/sa/sa2

* This runs close to midnight (at 23:53) to create the daily summary report of the sar data.
* sa2 creates /var/log/sa/sarXX file (Note that this is different than saXX file that is created by sa1). This sarXX file created by sa2 is an ascii file that you can view it in a text editor.
* This will also remove saXX files that are older than a week. So, write a quick shell script that runs every week to copy the /var/log/sa/* files to some other directory to do historical sar data analysis.

## 10 个 Sar 实践示例

### 所有 CPU 的 CPU 利用率

    $ sar -u 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:27:32 PM       CPU     %user     %nice   %system   %iowait    %steal     %idle
    01:27:33 PM       all      0.00      0.00      0.00      0.00      0.00    100.00
    01:27:34 PM       all      0.25      0.00      0.25      0.00      0.00     99.50
    01:27:35 PM       all      0.75      0.00      0.25      0.00      0.00     99.00
    Average:          all      0.33      0.00      0.17      0.00      0.00     99.50

* sar -u Displays CPU usage for the current day that was collected until that point.
* sar -u 1 3 Displays real time CPU usage every 1 second for 3 times.
* sar -u ALL Same as “sar -u” but displays additional fields.
* sar -u ALL 1 3 Same as “sar -u 1 3″ but displays additional fields.
* sar -u -f /var/log/sa/sa10 Displays CPU usage for the 10day of the month from the sa10 file.

### 独立 CPU 的 CPU 利用率

    $ sar -P ALL 1 1
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:34:12 PM       CPU     %user     %nice   %system   %iowait    %steal     %idle
    01:34:13 PM       all     11.69      0.00      4.71      0.69      0.00     82.90
    01:34:13 PM         0     35.00      0.00      6.00      0.00      0.00     59.00
    01:34:13 PM         1     22.00      0.00      5.00      0.00      0.00     73.00
    01:34:13 PM         2      3.00      0.00      1.00      0.00      0.00     96.00
    01:34:13 PM         3      0.00      0.00      0.00      0.00      0.00    100.00

    $ sar -P 1 1 1
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:36:25 PM       CPU     %user     %nice   %system   %iowait    %steal     %idle
    01:36:26 PM         1      8.08      0.00      2.02      1.01      0.00     88.89

* sar -P ALL Displays CPU usage broken down by all cores for the current day.
* sar -P ALL 1 3 Displays real time CPU usage for ALL cores every 1 second for 3 times (broken down by all cores).
* sar -P 1 Displays CPU usage for core number 1 for the current day.
* sar -P 1 1 3 Displays real time CPU usage for core number 1, every 1 second for 3 times.
* sar -P ALL -f /var/log/sa/sa10 Displays CPU usage broken down by all cores for the 10day day of the month from sa10 file.

### 内存空闲和使用率

    $ sar -r 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    07:28:06 AM kbmemfree kbmemused  %memused kbbuffers  kbcached  kbcommit   %commit  kbactive   kbinact
    07:28:07 AM   6209248   2097432     25.25    189024   1796544    141372      0.85   1921060     88204
    07:28:08 AM   6209248   2097432     25.25    189024   1796544    141372      0.85   1921060     88204
    07:28:09 AM   6209248   2097432     25.25    189024   1796544    141372      0.85   1921060     88204
    Average:      6209248   2097432     25.25    189024   1796544    141372      0.85   1921060     88204

* sar -r
* sar -r 1 3
* sar -r -f /var/log/sa/sa10

### 已使用的 Swap 空间

    $ sar -S 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    07:31:06 AM kbswpfree kbswpused  %swpused  kbswpcad   %swpcad
    07:31:07 AM   8385920         0      0.00         0      0.00
    07:31:08 AM   8385920         0      0.00         0      0.00
    07:31:09 AM   8385920         0      0.00         0      0.00
    Average:      8385920         0      0.00         0      0.00

* sar -S
* sar -S 1 3
* sar -S -f /var/log/sa/sa10

### 综合 I/O 活动数据

* tps – Transactions per second (this includes both read and write)
* rtps – Read transactions per second
* wtps – Write transactions per second
* bread/s – Bytes read per second
* bwrtn/s – Bytes written per second
```
    $ sar -b 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:56:28 PM       tps      rtps      wtps   bread/s   bwrtn/s
    01:56:29 PM    346.00    264.00     82.00   2208.00    768.00
    01:56:30 PM    100.00     36.00     64.00    304.00    816.00
    01:56:31 PM    282.83     32.32    250.51    258.59   2537.37
    Average:       242.81    111.04    131.77    925.75   1369.90
```
* sar -b
* sar -b 1 3
* sar -b -f /var/log/sa/sa10

### 独立的块设备 I/O 活动数据

    $ sar -d 1 1
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:59:45 PM       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
    01:59:46 PM    dev8-0      1.01      0.00      0.00      0.00      0.00      4.00      1.00      0.10
    01:59:46 PM    dev8-1      1.01      0.00      0.00      0.00      0.00      4.00      1.00      0.10
    01:59:46 PM dev120-64      3.03     64.65      0.00     21.33      0.03      9.33      5.33      1.62
    01:59:46 PM dev120-65      3.03     64.65      0.00     21.33      0.03      9.33      5.33      1.62
    01:59:46 PM  dev120-0      8.08      0.00    105.05     13.00      0.00      0.38      0.38      0.30
    01:59:46 PM  dev120-1      8.08      0.00    105.05     13.00      0.00      0.38      0.38      0.30
    01:59:46 PM dev120-96      1.01      8.08      0.00      8.00      0.01      9.00      9.00      0.91
    01:59:46 PM dev120-97      1.01      8.08      0.00      8.00      0.01      9.00      9.00      0.91

加 -p 选项显示实际的设备名字

    $ sar -p -d 1 1
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:59:45 PM       DEV       tps  rd_sec/s  wr_sec/s  avgrq-sz  avgqu-sz     await     svctm     %util
    01:59:46 PM       sda      1.01      0.00      0.00      0.00      0.00      4.00      1.00      0.10
    01:59:46 PM      sda1      1.01      0.00      0.00      0.00      0.00      4.00      1.00      0.10
    01:59:46 PM      sdb1      3.03     64.65      0.00     21.33      0.03      9.33      5.33      1.62
    01:59:46 PM      sdc1      3.03     64.65      0.00     21.33      0.03      9.33      5.33      1.62
    01:59:46 PM      sde1      8.08      0.00    105.05     13.00      0.00      0.38      0.38      0.30
    01:59:46 PM      sdf1      8.08      0.00    105.05     13.00      0.00      0.38      0.38      0.30
    01:59:46 PM      sda2      1.01      8.08      0.00      8.00      0.01      9.00      9.00      0.91
    01:59:46 PM      sdb2      1.01      8.08      0.00      8.00      0.01      9.00      9.00      0.91

* sar -d
* sar -d 1 3
* sar -d -f /var/log/sa/sa10
* sar -p -d

### 展示每秒上下文切换

    $ sar -w 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    08:32:24 AM    proc/s   cswch/s
    08:32:25 AM      3.00     53.00
    08:32:26 AM      4.00     61.39
    08:32:27 AM      2.00     57.00

* sar -w
* sar -w 1 3
* sar -w -f /var/log/sa/sa10

### 运行队列和系统负载报告

    $ sar -q 1 3
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    06:28:53 AM   runq-sz  plist-sz   ldavg-1   ldavg-5  ldavg-15   blocked
    06:28:54 AM         0       230      2.00      3.00      5.00         0
    06:28:55 AM         2       210      2.01      3.15      5.15         0
    06:28:56 AM         2       230      2.12      3.12      5.12         0
    Average:            3       230      3.12      3.12      5.12         0

* sar -q
* sar -q 1 3
* sar -q -f /var/log/sa/sa10

### 网络统计报告

    sar -n KEYWORD

KEYWORD 说明：

* DEV – Displays network devices vital statistics for eth0, eth1, etc.,
* EDEV – Display network device failure statistics
* NFS – Displays NFS client activities
* NFSD – Displays NFS server activities
* SOCK – Displays sockets in use for IPv4
* IP – Displays IPv4 network traffic
* EIP – Displays IPv4 network errors
* ICMP – Displays ICMPv4 network traffic
* EICMP – Displays ICMPv4 network errors
* TCP – Displays TCPv4 network traffic
* ETCP – Displays TCPv4 network errors
* UDP – Displays UDPv4 network traffic  
SOCK6, IP6, EIP6, ICMP6, UDP6 are for IPv6
* ALL – This displays all of the above information. The output will be very long.

```
    $ sar -n DEV 1 1
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    01:11:13 PM     IFACE   rxpck/s   txpck/s   rxbyt/s   txbyt/s   rxcmp/s   txcmp/s  rxmcst/s
    01:11:14 PM        lo      0.00      0.00      0.00      0.00      0.00      0.00      0.00
    01:11:14 PM      eth0    342.57    342.57  93923.76 141773.27      0.00      0.00      0.00
    01:11:14 PM      eth1      0.00      0.00      0.00      0.00      0.00      0.00      0.00
```
### 使用开始时间展示 sar 数据

    $ sar -q -f /var/log/sa/sa23 -s 10:00:01
    Linux 2.6.18-194.el5PAE (dev-db)        03/26/2011      _i686_  (8 CPU)
    
    10:00:01 AM   runq-sz  plist-sz   ldavg-1   ldavg-5  ldavg-15   blocked
    10:10:01 AM         0       127      2.00      3.00      5.00         0
    10:20:01 AM         0       127      2.00      3.00      5.00         0
    ...
    11:20:01 AM         0       127      5.00      3.00      3.00         0
    12:00:01 PM         0       127      4.00      2.00      1.00         0
    

### 参考

* [http://www.thegeekstuff.com/2011/03/sar-examples/][9]



[7]: http://sebastien.godard.pagesperso-orange.fr/download.html
[8]: http://www.thegeekstuff.com/2009/06/15-practical-crontab-examples/
[9]: http://www.thegeekstuff.com/2011/03/sar-examples/
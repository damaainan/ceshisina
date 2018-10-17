## [译] 三种系统监控工具对比：top vs Htop vs Glances

来源：[https://mp.weixin.qq.com/s/_YdwciFT6qu5_kUIyylR2g](https://mp.weixin.qq.com/s/_YdwciFT6qu5_kUIyylR2g)

时间 2018-10-16 08:03:31

 
![][0]
 
作者 | Mark Litwintschik
 
译者 | ma.yao，小大非
 
在开发软件或监控运行的系统时，遥测和环境监测都很重要。以便了解系统的运行状况，本文介绍了 top、Htop、Glances 三个实用工具，以及一种用于监控分布式系统的简单解决方案。
 
在开发软件或监控运行的系统时，遥测和环境监测都很重要。在理解了历史情境下什么是正常行为之后，通常两个最紧迫的问题是：（1）什么发生了变化？（2）什么表现出异常？
 
本文将介绍三个用于临时监控的流行工具，以及一种用于监控分布式系统的简单解决方案。
 
### top    
 
在几乎任何类 UNIX 的现代操作系统中，都可以通过输入 top 来查看一些系统性能指标，这些指标每几秒钟更新一次。

```
$ top -b -n2 -d5
```

```
top - 09:43:05 up  1:08,  0 users,  load average: 0.52, 0.58, 0.59
Tasks:   4 total,   1 running,   3 sleeping,   0 stopped,   0 zombie
%Cpu0  :  4.1 us, 22.2 sy,  0.0 ni, 72.3 id,  0.0 wa,  1.4 hi,  0.0 si,  0.0 st
%Cpu1  :  4.3 us,  7.1 sy,  0.0 ni, 87.7 id,  0.0 wa,  0.9 hi,  0.0 si,  0.0 st
%Cpu2  :  4.4 us,  9.0 sy,  0.0 ni, 85.3 id,  0.0 wa,  1.2 hi,  0.0 si,  0.0 st
%Cpu3  :  3.6 us,  6.7 sy,  0.0 ni, 88.6 id,  0.0 wa,  1.0 hi,  0.0 si,  0.0 st
KiB Mem:  33431016 total,  9521052 used, 23909964 free,    34032 buffers
KiB Swap: 62455548 total,    27064 used, 62428484 free.   188576 cached Mem

  PID USER      PR  NI    VIRT    RES    SHR S  %CPU %MEM     TIME+ COMMAND
    1 root      20   0    8304    132    104 S   0.0  0.0   0:00.14 /init ro
    3 root      20   0    8308     96     56 S   0.0  0.0   0:00.00 /init ro
    4 mark      20   0   17856   5308   5192 S   0.0  0.0   0:00.35 -bash
  228 mark      20   0   14452   1668   1172 R   0.0  0.0   0:00.01 top -b -n2 -d5
```
 
其二进制执行过程与 Comcast 公司的 James Warner 编写的 top 版本最相似。这个版本的 top 是全新的，并且是作为由包括 Lockheed Martin and Heidelberg University 在内的各个组织开发人员的合写版本的替代品开发而成的。
 
top.c 源代码本身相当简单，在撰写本文时，总共有 约 4900 行 C 代码。目前 top 仍然处于开发过程中，其源代码可以在 GitLab 的 procps 仓库(https://gitlab.com/procps-ng/procps)找到。该仓库中还包含其他工具，包括 kill、ps、sysctl、uptime 和 watch。
 
其默认布局一直没有改变过。但是通过过去几十年与 UNIX 系统打交道，每次在一台新机器上使用 top，我都会习惯性地输入 zc1M。
 
top 默认采用单色显示模式，使用 z 将切换至指定颜色模式。数字 1 将显示单个 CPU 的状态，并且能够突出显示单个 CPU 核的负载。我喜欢输入 M，以查看基于内存容量使用压力排序后的各进程信息。top 总共提供了 49 个供查看和排序的指标。
 
默认情况下，命令会截断显示，输入 c 会显示有关其路径和参数的更多扩展信息。 我唯一不满意的是命令和参数被截断了。如果只保留每条命令和参数的开头与结尾，以便区分不同进程，会更加实用。
 
top 配置的更改只会在当前 session 有效。为了解决这个问题，输入大写的 W 会默认将当前配置保存到~/.toprc 中。我对该文件唯一不满的地方是，它包含了大于 0x7F 的字节值，因而不易在 top 之外对其进行更改。

```
$ hexdump -C ~/.toprc | head
```

```
00000000  74 6f 70 27 73 20 43 6f  6e 66 69 67 20 46 69 6c  |top's Config Fil|
00000010  65 20 28 4c 69 6e 75 78  20 70 72 6f 63 65 73 73  |e (Linux process|
00000020  65 73 20 77 69 74 68 20  77 69 6e 64 6f 77 73 29  |es with windows)|
00000030  0a 49 64 3a 69 2c 20 4d  6f 64 65 5f 61 6c 74 73  |.Id:i, Mode_alts|
00000040  63 72 3d 30 2c 20 4d 6f  64 65 5f 69 72 69 78 70  |cr=0, Mode_irixp|
00000050  73 3d 31 2c 20 44 65 6c  61 79 5f 74 69 6d 65 3d  |s=1, Delay_time=|
00000060  33 2e 30 2c 20 43 75 72  77 69 6e 3d 30 0a 44 65  |3.0, Curwin=0.De|
00000070  66 09 66 69 65 6c 64 73  63 75 72 3d a5 a8 b3 b4  |f.fieldscur=....|
00000080  bb bd c0 c4 b7 ba b9 c5  26 27 29 2a 2b 2c 2d 2e  |........&')*+,-.|
00000090  2f 30 31 32 35 36 38 3c  3e 3f 41 42 43 46 47 48  |/012568<>?ABCFGH|
```
 
### Htop   
 
2004 年，Hisham Muhammad 开始致力于创建一个截然不同的系统遥测监控工具。Htop 关注遥测显示的重新布局：使用条形图展示 CPU 和内存的关键指标；使用 F5 快捷键，使进程信息在扁平化列表和层次结构之间切换显示；通过鼠标点击，可以实现属性排序；并且支持 7 种不同的颜色模式。
 
该软件能够很好地使您停留在应用当中。如果您想要查看一个进程使用的文件，您可以选择该进程，并只需输入 l；如果您想要通过 strace 运行该进程，在以授权用户身份运行 htop 的情况下，只需输入 s。
 
在 Ubuntu 16.04.2 LTS 上安装和运行 htop：

```
$ sudo apt install htop
$ htop
```

```
1  [                                         0.0%]   Tasks: 37, 145 thr; 1 running
 2  [                                         0.0%]   Load average: 0.03 0.05 0.07
 3  [                                         0.0%]   Uptime: 01:31:42
 4  [                                         0.0%]
 Mem[||||||||||||||||||||||||||||||||  1.03G/3.84G]
 Swp[                                     0K/4.00G]

  PID USER      PRI  NI  VIRT   RES   SHR S CPU% MEM%   TIME+  Command
    1 root       20   0 37556  5668  4004 S  0.0  0.1  0:03.03 /sbin/init noprompt
27884 clickhous  20   0 3716M  359M 49184 S  0.7  9.1  0:24.93 ├─ /usr/bin/clickhouse-server --config=/etc/cli
29668 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:00.10 │  ├─ /usr/bin/clickhouse-server --config=/etc/
29667 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:01.02 │  ├─ /usr/bin/clickhouse-server --config=/etc/
29666 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:00.08 │  ├─ /usr/bin/clickhouse-server --config=/etc/
29665 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:00.48 │  ├─ /usr/bin/clickhouse-server --config=/etc/
29409 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:03.48 │  ├─ /usr/bin/clickhouse-server --config=/etc/
29408 clickhous  20   0 3716M  359M 49184 S  0.0  9.1  0:02.15 │  ├─ /usr/bin/clickhouse-server --config=/etc/
```
 
至于配置方面，使用该软件的过程中，任何配置修改都会默认自动保存至~/.config/htop/htoprc。该文件是个文本文件，但是附有下面的警告：

```
$ head -n2 ~/.config/htop/htoprc
```

```
# Beware! This file is rewritten by htop when settings are changed in the interface.
# The parser is also very primitive, and not human-friendly.
```
 
鉴于其提供的功能比较简单，它的源代码量还是相当小的。在撰写本文时，它总共有约 12000 行 C 代码，同时还包含约 3000 行代码的其他文件。
 
### Glances
 
Glances (https://nicolargo.github.io/glances/)是一个基于 Python 的系统遥测监控工具。该项目由 Nicolas Hennion 于 2011 年开始创建。Nilcolas 的领英简介显示，他在法国南部的 Thales Alenia Space 卫星控制中心部门担任项目经理。
 
当启动 Glances 时，除了常见的 CPU、内存和进程列表，还将看到云虚拟机类型以及网络、硬盘、和 Docker 容器活动等等。

```
$ glances
```

```
ubuntu (Ubuntu 16.04 64bit / Linux 4.4.0-62-generic)                                            Uptime: 18:55:00

CPU  [  1.7%]   CPU -     1.7%  nice:     0.0%  ctx_sw:   923   MEM -   53.1%   SWAP -    0.1%   LOAD    4-core
MEM  [ 53.1%]   user:     0.8%  irq:      0.0%  inter:    587   total:  3.84G   total:   4.00G   1 min:    0.20
SWAP [  0.1%]   system:   0.7%  iowait:   0.0%  sw_int:   786   used:   2.04G   used:    3.27M   5 min:    0.14
                idle:    98.4%  steal:    0.0%                  free:   1.80G   free:    3.99G   15 min:   0.10

NETWORK       Rx/s   Tx/s   TASKS 203 (349 thr), 1 run, 202 slp, 0 oth sorted automatically by CPU consumption
ens33         152b    3Kb
lo            59Kb   59Kb   CPU%   MEM%  VIRT  RES      PID USER          TIME+ THR  NI S  R/s W/s  Command
                            2.6    4.5   524M  178M   16470 mark          35:48 1     0 S    0 0    /home/mark/.
DISK I/O       R/s    W/s   2.3    0.6   372M  24.5M  14672 mark           0:01 1     0 R    0 0    /home/mark/.
fd0              0      0   1.0    23.7  5.42G 931M   21151 root          13:00 71    0 S    ? ?    java -Xmx1G
loop0            0      0   0.7    9.8   3.71G 385M   27884 clickhous      5:29 46    0 S    ? ?    /usr/bin/cli
loop1            0      0   0.3    2.8   3.53G 109M   12883 zookeeper      1:36 20    0 S    ? ?    /usr/bin/jav
loop2            0      0   0.3    0.2   31.4M 6.80M    333 root           0:53 1     0 S    ? ?    /lib/systemd
loop3            0      0   0.3    0.1   13.8M 2.68M   4353 mark           1:07 1     0 S    0 0    watch ifconf
loop4            0      0   0.0    0.3   186M  9.86M   1447 root           0:35 2     0 S    ? ?    /usr/bin/vmt
loop5            0      0   0.0    0.2   75.2M 8.11M   1470 root           0:00 1     0 S    ? ?    /usr/bin/VGA
loop6            0      0   0.0    0.2   90.6M 6.59M   4381 root           0:00 1     0 S    ? ?    sshd: mark [
loop7            0      0   0.0    0.1   269M  5.75M    595 root           0:13 3     0 S    ? ?    /usr/lib/acc
sda              0    78K   0.0    0.1   36.7M 5.37M      1 root           0:37 1     0 S    ? ?    /sbin/init n
sda1             0    78K   0.0    0.1   64.0M 5.31M   4246 root           0:00 1     0 S    ? ?    /usr/sbin/ss
sda2             0      0   0.0    0.1   44.3M 5.05M   3402 mark           0:00 1     0 S    0 0    /lib/systemd
sda5             0      0   0.0    0.1   21.8M 5.04M   4403 mark          27:23 1     0 S    0 0    -bash
sr0              0      0   0.0    0.1   21.8M 4.93M  21493 mark           0:10 1     0 S    0 0    /bin/bash
sr1              0      0   0.0    0.1   21.7M 4.62M  16114 mark           0:03 1     0 S    0 0    /bin/bash
                            0.0    0.1   21.7M 4.47M  21119 mark           0:00 1     0 S    0 0    /bin/bash
FILE SYS      Used  Total   0.0    0.1   90.6M 4.14M   4402 mark           0:08 1     0 S    ? ?    0
/ (sda1)     2.48G  15.6G   0.0    0.1   250M  3.97M    588 syslog         0:28 4     0 S    ? ?    /usr/sbin/rs
                            0.0    0.1   21.8M 3.87M   3407 mark           0:04 1     0 S    0 0    -bash
SENSORS                     0.0    0.1   51.5M 3.76M  21144 root           0:00 1     0 S    ? ?    sudo nohup /
Physical id          100C   0.0    0.1   41.9M 3.64M    597 messagebu      0:00 1     0 S    ? ?    /usr/bin/dbu
Core 0               100C   0.0    0.1   43.2M 3.45M    396 root           0:01 1     0 S    ? ?    /lib/systemd
Core 1               100C   0.0    0.1   64.3M 3.21M   3377 root           0:00 1     0 S    ? ?    /bin/login -
Core 2               100C   0.0    0.1   28.0M 2.91M    592 root           0:00 1     0 S    ? ?    /lib/systemd
Core 3               100C   0.0    0.1   26.7M 2.86M  16113 mark           0:06 1     0 S    ? ?    SCREEN
                            0.0    0.1   15.7M 2.81M    774 root           0:00 1     0 S    ? ?    /sbin/dhclie
```
 
Glances 由约 1 万行 Python 代码和约 2.5 万行 JavaScript 代码写成，并依赖于 psutil (https://github.com/giampaolo/psutil/)软件包以用于遥测数据收集。它还含有大量 插件，包括支持监控 GPU、Kafka、RAID 设置、文件夹监控以及 WiFi 等等。
 
除了基于 ncurses 的界面，Glances 也能以 Web 应用的形式运行。当在 Windows 10 上通过 cmd.exe 运行 Glances 的时候，将启动一个运行在 TCP 端口为 61209 的 Bottle Web 应用。在浏览器中打开 http://127.0.0.1:61209，会看到一个AngularJS 应用程序的欢迎页面。该页面模仿了 ncurses 界面。
 
也可以通过调用其暴露的 API 接口，配合其他工具使用：

```
$ curl http://127.0.0.1:61209/api/3/all \
    | python -mjson.tool \
    | head -n50
{
    "alert": [],
    "amps": [],
    "batpercent": [],
    "cloud": {},
    "core": {
        "log": 4,
        "phys": 4
    },
    "cpu": {
        "cpucore": 4,
        "ctx_switches": 182358,
        "idle": 82.9,
        "interrupts": 113134,
        "soft_interrupts": 0,
        "syscalls": 215848,
        "system": 12.5,
        "time_since_update": 8.532670974731445,
        "total": 9.8,
        "user": 3.1
    },
    "diskio": [
        {
            "disk_name": "PhysicalDrive6",
            "key": "disk_name",
            "read_bytes": 0,
            "read_count": 0,
            "time_since_update": 8.492774963378906,
            "write_bytes": 0,
            "write_count": 0
        },
        {
            "disk_name": "PhysicalDrive2",
            "key": "disk_name",
            "read_bytes": 0,
            "read_count": 0,
            "time_since_update": 8.492774963378906,
            "write_bytes": 0,
            "write_count": 0
        },
...
```
 
虽然默认的配置文件(https://github.com/nicolargo/glances/blob/develop/conf/glances.conf)有些冗长，但是用户编辑起来还算方便。
 
Glances 还支持将遥测数据导出到 16 个以上不同的目标文件中，包括 StatsD、Kafka、RabbitMQ、JSON、SVG、ElasticSearch、CSV 以及自定义 RESTful API。
 
将 Glances 导入 Kafka
 
以下将介绍将遥测数据导入 CSV 文件，再导入 Kafka。我认为本地硬盘通常要比网络连接更靠谱。当网络连接出现问题的时候，我们还可以利用本地文件再次回填 Kafka。
 
以下命令运行在新安装的 Ubuntu 16.04.2 LTS 上：

```
$ sudo apt update
$ sudo apt install \
    kafkacat \
    python-pip \
    python-virtualenv \
    screen \
    zookeeperd
```
 
使用 Apache 镜像上的二进制包，手动安装 Kafka：

```
$ sudo mkdir -p /opt/kafka
$ wget -c -O kafka.tgz \
    http://www-eu.apache.org/dist/kafka/1.1.1/kafka_2.11-1.1.1.tgz
$ sudo tar xzvf kafka.tgz \
    --directory=/opt/kafka \
    --strip 1
```
 
为 Kafka 创建日志文件，其权限使用我的 UNIX 账号：

```
$ sudo touch /var/log/kafka.log
$ sudo chown mark /var/log/kafka.log
```
 
ZooKeeper 支持了 Kafka 的大多数分布式功能，以下命令将启动 ZooKeeper 服务：

```
$ sudo /etc/init.d/zookeeper start
```
 
启动完 ZooKeeper，启动 Kafka 服务器进程：

```
$ sudo nohup /opt/kafka/bin/kafka-server-start.sh \
             /opt/kafka/config/server.properties \
             > /var/log/kafka.log 2>&1 &
```
 
创建 Python 虚拟环境，并安装 Glances 以及 CSVKit，以便分析 Glances 的 CSV 文件输出：

```
$ virtualenv ~/.monitoring
$ source ~/.monitoring/bin/activate
$ pip install \
    csvkit \
    glances
```
 
接着，启动 screen 会话和 Glances。它将显示 ncurses 界面，并向~/glances.csv 中写入 215 条数据：

```
$ screen
$ glances --export csv \
          --export-csv-file ~/glances.csv
```
 
一旦运行起来，按 CTRL-A，接着按 CTRL-D，返回到常规的 Shell 界面。
 
如下所示，这里有大量收集到的遥测数据：

```
$ csvstat --type ~/glances.csv | tail
```

```
206. mem_available: Number
207. mem_used: Number
208. mem_cached: Number
209. mem_percent: Number
210. mem_free: Number
211. mem_inactive: Number
212. mem_active: Number
213. mem_shared: Number
214. mem_total: Number
215. mem_buffers: Number
```
 
Kafkacat 是采用 C 语言写的一个非 JVM 的 Kafka 生产者和消费者。静态链接的包大小要小于 150KB。使用它，将~/glances.csv 中的内容导入 Kafka Topic “glances_log”中，并对内容进行 Snappy 压缩。

```
$ screen
$ tail -F ~/glances.csv \
    | kafkacat -b localhost:9092 \
               -t glances_log \
               -z snappy
```
 
接下来，一旦运行起来，按 CTRL-A，然后按 CTRL-D，返回到常规 Shell 界面。
 
以上这些运行在 screen 会话中的任何命令，都可以方便地添加到 Supervisord。另外，如果这些进程因为任何原因挂了，都能很好地重启它们。
 
完成上述操作之后，查看前 100 条记录的前三列数据：

```
$ /opt/kafka/bin/kafka-console-consumer.sh \
        --topic glances_log \
        --from-beginning \
        --zookeeper localhost:2181 \
    | head -n100 \
    | csvstat --columns 1-3 \
              --no-header-row
```
 
以下是基于前 100 条记录，收集到的时间戳、CPU 核数以及一分钟负载均值的统计信息：

```
1. "a"

      Type of data:          DateTime
      Contains null values:  False
      Unique values:         100
      Smallest value:        2018-10-07 05:53:49
      Largest value:         2018-10-07 05:58:55
      Most common values:    2018-10-07 05:53:49 (1x)
                             2018-10-07 05:53:52 (1x)
                             2018-10-07 05:53:55 (1x)
                             2018-10-07 05:53:58 (1x)
                             2018-10-07 05:54:01 (1x)

2. "b"

      Type of data:          Number
      Contains null values:  False
      Unique values:         1
      Smallest value:        4
      Largest value:         4
      Sum:                   400
      Mean:                  4
      Median:                4
      StDev:                 0
      Most common values:    4 (100x)

3. "c"

      Type of data:          Number
      Contains null values:  False
      Unique values:         18
      Smallest value:        0.02
      Largest value:         0.22
      Sum:                   6.57
      Mean:                  0.066
      Median:                0.05
      StDev:                 0.045
      Most common values:    0.04 (15x)
                             0.02 (14x)
                             0.03 (13x)
                             0.06 (9x)
                             0.05 (9x)
```
 
英文原文：http://tech.marksblogg.com/top-htop-glances.html
 
活动推荐
 
互联网的快速发展，导致服务数量呈现了指数级增长，自动化运维虽然提升了效率，但也遇到了新的难题。面对繁多的报警信息，运维人员应该如何处理？故障发生时，又如何能够迅速定位问题？
 
由 InfoQ 主办的第四届 CNUTCon 全球运维技术大会，全方位、多角度向参会者阐述智能运维时代的有哪些变革，Twitter、RIOT Games、BAT、华为等国内外一线大厂有哪些新技术和新实践。
 
目前，大会 8 折限时优惠，立减 720 元，团购更优惠！扫描下方二维码或点击阅读原文了解，有任何问题欢迎咨询 Joy 小同学，电话：13269078023（微信同号）。
 


[0]: https://img2.tuicool.com/BRn632Q.jpg

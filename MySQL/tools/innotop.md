# 命令行监控工具Innotop

## 一、工具简介

innotop是一个基于命令行的监控工具，在某种方面模拟了UNIX中的top工具。

类似的命令行监控工具还有mtop和mytop，不过都不如innotop功能强大。

> INNOTOP是一个通过文本模式显示MySQL和InnoDB的监测工具。INNOTOP是用PERL语言写成的，这使它能更加灵活的使用在各种操作平台之上，它能详细的监控出当前MYSQL和INNODB运行的状态，以DBA根据结果，可以合理的优化MYSQL，让MYSQL更稳定更高效的运行。(摘自：参考文档3)

## 二、功能特性

既然说到innotop功能很强大，到底能用来帮助我们做什么事情呢？

可以看一下innotop的功能特性如下：

* 事务列表可以显示INNODB当前的全部事务。
* 查询列表可以显示当前正在运行的查询。
* 可以显示当前锁和锁等待的列表。
* 以相对值 显示服务器状态的变量和汇总信息。
* 有多种模式可用来显示INNODB内部信息，例如：缓冲区、死锁、外键错误、I/O活动情况、行操作、信号量以及其他更多的内容。
* 复制监控，将主服务器和从服务器的状态显示在一起。
* 显示任意服务器变量的模式。
* 服务器组可以更方便地组织多台服务器。
* 在命令行脚本下可以使用非交互式模式。

## 三、工具安装

### 1. 源码安装

最早innotop代码被托管到google code上，后来迁移到了github：[innotop][0]

下载innotop源码，运行标准的make install安装过程即可。

**PS**：八过网上的安装方式大多针对于linux系统而言。本人尝试在本机MAC系统下安装并没有那么简单，卡在安装perl的mysql连接库这块。MAC用户可以尝试使用下面的方法安装。

### 2. brew安装

  innotop --version

## 四、使用方法

### 1. 帮助信息
```
yerba-buena:~ yeshaoting$ innotop --help
Usage: innotop <options> <innodb-status-file>
  --askpass          Prompt for a password when connecting to MySQL
  --[no]color   -C   Use terminal coloring (default)
  --config      -c   Config file to read
  --count            Number of updates before exiting
  --delay       -d   Delay between updates in seconds
  --help             Show this help message
  --host        -h   Connect to host
  --[no]inc     -i   Measure incremental differences
  --mode        -m   Operating mode to start in
  --nonint      -n   Non-interactive, output tab-separated fields
  --password    -p   Password to use for connection
  --port        -P   Port number to use for connection
  --skipcentral -s   Skip reading the central configuration file
  --socket      -S   MySQL socket to use for connection
  --spark            Length of status sparkline (default 10)
  --timestamp   -t   Print timestamp in -n mode (1: per iter; 2: per line)
  --user        -u   User for login if not current user
  --version          Output version information and exit
  --write       -w   Write running configuration into home directory if no config files were loaded
innotop is a MySQL and InnoDB transaction/status monitor, like 'top' for
MySQL.  It displays queries, InnoDB transactions, lock waits, deadlocks,
foreign key errors, open tables, replication status, buffer information,
row operations, logs, I/O operations, load graph, and more.  You can
monitor many servers at once with innotop.
```

查看innotop的帮助信息可以知道innotop使用方法及参数类似于mysql连接方法。

innotop监控本机mysql方式：

    innotop -uroot -proot -h127.0.0.1

### 2. 监控模式

进入innotop命令行默认进入的**Dashboard**模式。

innotop总共有15种监控模式可供切换。如下所示：

```
[RO] Dashboard (? for help)                                                                                                                         localhost, 17d, 0.75 QPS, 3/1/1 con/run/cac thds, 5.6.27
Switch to a different mode:
   A  Dashboard         I  InnoDB I/O Info     Q  Query List
   B  InnoDB Buffers    K  InnoDB Lock Waits   R  InnoDB Row Ops
   C  Command Summary   L  Locks               S  Variables & Status
   D  InnoDB Deadlocks  M  Replication Status  T  InnoDB Txns
   F  InnoDB FK Err     O  Open Tables         U  User Statistics
Actions:
   d  Change refresh interval        q  Quit innotop
   k  Kill a query's connection      r  Reverse sort order
   n  Switch to the next connection  s  Choose sort column
   p  Pause innotop                  x  Kill a query
Other:
 TAB  Switch to the next server group   /  Quickly filter what you see
   !  Show license and warranty         =  Toggle aggregation
   #  Select/create server groups       @  Select/create server connections
   $  Edit configuration settings       \  Clear quick-filters
Press any key to continue
```

**注**：监控默认情况每10秒刷新一下，可以通过**d**命令更新刷新频率。

## 五、工具实践

测试表t数据如下：
```
mysql root@localhost:test> select * from t;
+------+---------+
|   id |   stage |
|------+---------|
|    1 |       1 |
|    4 |       4 |
|    9 |       9 |
|   15 |      15 |
+------+---------+
```

起两个事务T1、T2用于测试。两事务时序表如下：

T1 | T2 
-|-
begin; | begin; 
select * from t where id = 9 lock in share mode; |-
-|select * from t where id = 4 for update; 

### 1. 当前所有事务

innotop界面**SHIFT + T**查看当前事务状态，如下所示：
```
[RO] InnoDB Txns (? for help)                                                                                                        localhost, 17d, InnoDB 4s :-), 0.62 QPS, 3/1/1 con/run/cac thds, 5.6.27
History  Versions  Undo  Dirty Buf  Used Bufs  Txns  MaxTxnTime  LStrcts
    975                      0.00%      5.27%     3       02:28
ID  User       Host  Txn Status  Time   Undo  Query Text
49  localhost        ACTIVE      02:28     0
47  localhost        ACTIVE      02:11     0
```

由上可看出，T1和T2 session ID分别为47和49，事务执行时长分别为1小时和11分钟。

### 2. 查询当前锁状态

安装好innotop工具之后，Lock模式只系那是当前wait的锁状况，而不显示当前持有锁的记录。innotop监控依赖于innodb监控开启状态(参见：[开启InnoDB监控][1])。

具体方法：

    set GLOBAL innodb_status_output=ON;
    set GLOBAL innodb_status_output_locks=ON;

innotop界面**SHIFT + L**查看所有事务锁占用状态，如下所示：
```
[RO] Locks (? for help)                                                                                                              localhost, 17d, InnoDB 7s :-), 0.20 QPS, 3/1/1 con/run/cac thds, 5.6.27
______________________________________ InnoDB Locks _______________________________________
ID  Type    Waiting  Wait   Active  Mode  DB    Table  Index    Ins Intent  Special
47  TABLE         0  00:00   03:55  IS    test  t                        0
47  RECORD        0  00:00   03:55  S     test  t      PRIMARY           0  rec but not gap
49  TABLE         0  00:00   04:12  IX    test  t                        0
49  RECORD        0  00:00   04:12  X     test  t      PRIMARY           0  rec but not gap 
```

由上可看出，T1事务加了两把锁：表t上的表锁IS和共享记录锁S rec but not gap，T2事务也加了两把锁：表t上的表锁IX和记录前的排他间隙锁X rec but not gap。

### 3. 查看锁等待情况

在事务T1中再执行如下命令：

    select * from t where id  = 4 for update;

innotop界面**SHIFT + K**等待锁的事务，如下所示：

```
[RO] InnoDB Lock Waits (? for help)                                                                                                                localhost, 17d, 21.89 QPS, 3/0/0 con/run/cac thds, 5.6.27
WThread  Waiting Query  WWait  BThread  BRowsMod  BAge  BWait  BStatus   Blocking Query
     47  SELECT t          2s       49         0    7m         Sleep 76
```
由上可看出，session ID为47的事务(即：事务T1)正在等待其他事务session ID为49的事务(即：事务T2)释放锁。锁等待时长为2秒。

### 4. 其他模式

通过 **SHIFT + O**可以查看当前打开的表，通过 **SHIFT + I**可以查看INNODB当前I/O状态。

模式的更多用法等待大家后续慢慢挖掘。

## 六、参考文档

1. 高性能MySQL3
1. [Innotop: A real-time, advanced investigation tool for MySQL][2]
1. [MySQL监控利器-Innotop][3]

本文标题: [命令行监控工具Innotop][4]

文章作者: [yeshaoting][5]

发布时间: 2016-08-28, 20:21:00

最后更新: 2016-12-30, 10:43:58

 原始链接: [http://yeshaoting.cn/article/database/命令行监控工具Innotop/][4]

 许可协议: ["署名-非商用-相同方式共享 4.0"][6] 转载请保留原文链接及作者。

[0]: https://github.com/innotop/innotop
[1]: http://yeshaoting.cn/article/database/%E5%BC%80%E5%90%AFInnoDB%E7%9B%91%E6%8E%A7/
[2]: https://www.percona.com/blog/2013/10/14/innotop-real-time-advanced-investigation-tool-mysql/
[3]: http://www.cnblogs.com/ivictor/p/5101506.html
[4]: /article/database/命令行监控工具Innotop/
[5]: /
[6]: http://creativecommons.org/licenses/by-nc-sa/4.0/
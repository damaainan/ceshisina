## redis持久化和常见故障

来源：[https://segmentfault.com/a/1190000004135982](https://segmentfault.com/a/1190000004135982)


## redis 主从复制
## Redis主从复制的原理

当建立主从关系时，slave配置slaveof <master_host> <master_port> 。slave服务器会向主服务器发送一个sync命令。master接受并fork一个进程来执行BGSAVE命令。该命令生成一个RDB文件并且全量发送给slave服务器，slave服务器接收并载入RDB文件，同时，主服务器将缓冲区的命令以增量的方式发送给从服务器，最终使从服务器的数据状态和主服务器保持一致。

![][0]
### RDB的工作原理

当redis生成dump.rdb文件时，工作过程如下


* redis主进程fork一个子进程

* fork出来的子进程将内存的数据集dump到临时的RDB中

* 当子进程对临时的RDB文件写入完毕，redis用新的RDB文件代替旧的RDB文件



### AOF的工作原理

AOF ：append only file。每当Redis执行一个改变数据集的命令时，这个命令都会被追加到AOF文件的末尾。当redis重新启动时，程序可以通过AOF文件恢复数据
## 持久化文件监控

Redis 监控最直接的方法当然就是使用系统提供的 info 命令来做了，只需要执行下面一条命令，就能获得 Redis 系统的状态报告。

``` 
redis-cli info
```
### RDB文件状态监控

其中跟RDB文件状态监控相关的参数


* rdb_changes_since_last_save 表明上次RDB保存以后改变的key次数

* rdb_bgsave_in_progress 表示当前是否在进行bgsave操作。是为1

* rdb_last_save_time 上次保存RDB文件的时间戳

* rdb_last_bgsave_time_sec 上次保存的耗时

* rdb_last_bgsave_status 上次保存的状态

* rdb_current_bgsave_time_sec 目前保存RDB文件已花费的时间



### AOF文件状态监控

其中跟AOF文件状态监控相关的参数


* aof_enabled AOF文件是否启用

* aof_rewrite_in_progress 表示当前是否在进行写入AOF文件操作

* aof_rewrite_scheduled

* aof_last_rewrite_time_sec 上次写入的时间戳

* aof_current_rewrite_time_sec:-1

* aof_last_bgrewrite_status:ok 上次写入状态

* aof_last_write_status:ok 上次写入状态



### 查看rdb文件生成耗时

在我们优化master之前，可以看看目前我们的其中一个生产环境的的redis的持久化状态

``` 
# Persistence
loading:0
rdb_changes_since_last_save:116200
rdb_bgsave_in_progress:1
rdb_last_save_time:1448944451
rdb_last_bgsave_status:ok
rdb_last_bgsave_time_sec:85
rdb_current_bgsave_time_sec:33
aof_enabled:0
aof_rewrite_in_progress:0
aof_rewrite_scheduled:0
aof_last_rewrite_time_sec:-1
aof_current_rewrite_time_sec:-1
aof_last_bgrewrite_status:ok
aof_last_write_status:ok
```

通过redis-cli的info命令，可以看到 「rdb_last_bgsave_time_sec」参数的值，
这个值表示上次bgsave命令执行的时间。在磁盘IO定量的情况下，redis占用的内存越大，
这个值也就越大。通常「rdb_last_bgsave_time_sec」这个时间取决于两个因素：


* REDIS占用的内存大小

* 磁盘的写速度。


`rdb_last_bgsave_time_sec:85`这个标识表示我们上次保存dump RDB文件的时间。这个耗时受限于上面提到的两个因素。

当redis处于 rdb_bgsave_in_progress状态时，通过vmstat命令查看性能，得到wa值偏高，也就是说CPU在等待
IO的请求完成，我们线上的一个应用redis占用的内存是5G左右，也就是redis会生成大约5G左右的dump.rdb文件

vmstat命令

``` 
  r  b   swpd   free   buff  cache   si   so    bi    bo   in   cs us sy id wa st
 0  4      0 223912 2242680 5722008    0    0   200 48648 3640 5443  1  1 63 35  0
 0  3      0 222796 2242680 5722052    0    0    16 48272 2417 5019  1  1 63 35  0
 0  3      0 222300 2242680 5722092    0    0    40 24612 3042 3568  1  1 63 35  0
 0  3      0 220068 2242680 5722124    0    0    64 40328 4304 4737  2  1 63 34  0
 0  3      0 218952 2242680 5722216    0    0   100 48648 4966 5786  1  2 63 35  0
 0  3      0 215356 2242680 5722256    0    0     0 66168 3546 4382  2  1 62 35  0
```

通过上面的输出，看到 **`BGSAVE`**  对于IO的性能影响比较大

那么该如何解决由RDB文件带来的性能上不足的问题，又能保证数据持久化的目的

通常的设计思路就是利用「Replication」机制来解决：即master不开启RDB日志和AOF日志，来保证master的读写性能。而slave则开启rdb和aof来进行持久化，保证数据的持久性，
## 建立主从复制步骤和灾难恢复

我在测试机器上，开启两个实例，端口分别为6379和6380

``` 
master: 172.16.76.232 6379
slave:  172.16.76.232 6380
```
### 修改配置

修改master的redis.conf

关闭RDB

``` 
# save 900 1
# save 300 10
# save 60 10000
```

关闭AOF

``` 
appendonly no
```

分别启动master和slave的redis

``` 
service redis start
```

修改slave配置，指向master服务器

``` 
redis-cli > slaveof 172.16.76.232 6379
```

查看slave的复制状态

``` 
redis-cli > info replication
```
### 脚本模拟填充数据

``` 

#!/bin/bash

ID=1
while(($ID<50001))
do
 redis-cli set "my:$ID" "aaa_okthisis_Omb5EVIwBgPHgbRj64raygpeRLKaNhyB9sLF_$ID"
 redis-cli set "your:$ID" "your_okthisis_Omb5EVIwBgPHgbRj64raygpeRLKaNhyB9sLF_$ID"
 redis-cli set "her:$ID" "her_okthisis_Omb5EVIwBgPHgbRj64raygpeRLKaNhyB9sLF_$ID"
 redis-cli set "his:$ID" "his_okthisis_Omb5EVIwBgPHgbRj64raygpeRLKaNhyB9sLF_$ID"

 ID=$(($ID+1))
done
```
### kill掉master实例模拟灾难

``` 
master redis > killall -9 redis-server
```

``` 
SLAVE redis > SLAVEOF NO ONE
```

取消Slave的同步，避免主库在未完成数据恢复前就重启，进而直接覆盖掉从库上的数据，导致所有的数据丢失。

### 将slave上的RDB和AOF复制到master数据文件夹中

``` 
cp /data/redis_data_slave/dump.rdb /data/redis_data/
cp /data/redis_data_slave/Append.AOF /data/redis_data/
```
### 启动master的实例

``` 
master redis > dbsize
```

查看数据是否恢复

### 重新开启slave复制

``` 
slave redis > slaveof 172.16.76.232 6379
```
## 故障案例报告
## redis丢失数据案例

背景介绍：

``` 
我们的一台redis服务器，硬件配置为4核，4G内存。redis持久话方案是RDB。前面几个月redis使用的
```

内存在1G左右。在一次重启之后，redis只恢复了部分数据，这时查看redis.log文件。看见了如下的错误

``` 
[23635] 25 Jul 08:30:54.059 * 10000 changes in 60 seconds. Saving...
[23635] 25 Jul 08:30:54.059 # Can't save in background: fork: Cannot allocate memory
```

这时，想起了redis启动时的警告

``` 
WARNING overcommit_memory is set to 0!
Background save may fail under low memory condition.
To fix this issue add 'vm.overcommit_memory = 1' to /etc/sysctl.conf and
then reboot or run the command 'sysctl vm.overcommit_memory=1' for this to take effect.
```

翻译

``` 
警告：过量使用内存设置为0！在低内存环境下，后台保存可能失败。为了修正这个问题，
请在/etc/sysctl.conf 添加一项 'vm.overcommit_memory = 1' ，
然后重启（或者运行命令'sysctl vm.overcommit_memory=1' ）使其生效。
```

vm.overcommit_memory不同的值说明


* 0 表示检查是否有足够的内存可用，如果是，允许分配；如果内存不够，拒绝该请求，并返回一个错误给应用程序。

* 1 允许分配超出物理内存加上交换内存的请求

* 2 内核总是返回true



redis的数据回写机制分为两种


* 同步回写即SAVE命令。redis主进程直接写数据到磁盘。当数据量大时，这个命令将阻塞，响应时间长

* 异步回写即BGSAVE命令。redis 主进程fork一个子进程，复制主进程的内存并通过子进程回写数据到磁盘。



由于RDB文件写的时候fork一个子进程。相当于复制了一个内存镜像。当时系统的内存是4G，而redis占用了
近3G的内存，因此肯定会报内存无法分配。如果 「vm.overcommit_memory」设置为0，在可用内存不足的情况
下，就无法分配新的内存。如果 「vm.overcommit_memory」设置为1。 那么redis将使用交换内存。

解决办法:


* 方法一: 修改内核参数 vi /etc/sysctl。设置`vm.overcommit_memory = 1`然后执行

``` 
``` sysctl -p ```
```


* 方法二: 使用交换内存并不是一个完美的方案。最好的办法是扩大物理内存。



## 复制有可能碰到的问题

使用slaveof命令后，长时间看不到数据同步。以为复制功能失效，或配置错了。其实，不用担心，有两种方法可以确定是否正在建立复制。

在创建Redis复制时，一开始可能会发现Slave长时间不开始同步数据，可能数据量太大，导致了Master正在dump数据慢，此时如果你在Master上执行「top -p $(pgrep -d, redis-server)」命令，就能看到dump的过程

方式一: 通过「top」命令

``` 
[root@img1_u ~]# top -p $(pgrep -d, redis-server)
top - 14:06:24 up 54 days,  6:13,  1 user,  load average: 1.18, 1.32, 1.20
Tasks:   2 total,   1 running,   1 sleeping,   0 stopped,   0 zombie
Cpu(s): 15.2%us,  1.7%sy,  0.6%ni, 81.9%id,  0.2%wa,  0.0%hi,  0.4%si,  0.0%st
Mem:  24542176k total, 22771848k used,  1770328k free,  2245720k buffers
Swap:   524280k total,        0k used,   524280k free,  4369452k cached

  PID USER      PR  NI  VIRT  RES  SHR S %CPU %MEM    TIME+  COMMAND
21619 root      20   0 5654m 5.4g  388 R 99.9 23.0   0:23.70 redis-server
 1663 root      20   0 5654m 5.4g 1068 S 15.3 23.0   5042:31 redis-server

```

redis-server是单进程的，现在通过「top」命令查看已经有2个进程，因为之前提到的，redis在建立复制的时，会在

主服务器上执行 **` BGSAVE `**  命令。fork一个子进程，dump出RDB文件。 master dump 完毕，然后再将快照文件传给slave。

方式二:通过「rdb_bgsave_in_progress」标识

进入master的redis-cli

``` 
redis-cli > info persistence
...
loading:0
rdb_changes_since_last_save:0
rdb_bgsave_in_progress:1
rdb_last_save_time:1448992510
rdb_last_bgsave_status:ok
rdb_last_bgsave_time_sec:4
rdb_current_bgsave_time_sec:1
...
```

如果「rdb_bgsave_in_progress」为1，那么master正在进行bgsave命令。同时「rdb_current_bgsave_time_sec」
显示bgsave命令已经执行的时间。由于在master服务器上默认不开启RDB和AOF日志，如果「rdb_bgsave_in_progress」为1，那么就可以肯定由于复制原因发送一个「bgsave」指令 dump 出 RDB 文件。
## redis 内存达到上限

有运营的同事反应，系统在登录的情况下，操作时会无缘无故跳到登录页面。 由于我们的系统做了分布式的
session,默认把session放到redis里，按照以往的故障经验。可能是redis使用了最大内存上限
导致了无法设置key。 登录 redis 服务器查看 redis.conf 文件设置了最大8G内存「maxmemory 8G」
然后通过「redis-cli info memory 」 查询到目前的内存使用情况 「used_memory_human:7.71G」
接着通过redis-cli工具设置值 。报错 「OOM command not allowed when used memory 」。再次
验证了redis服务器已经达到了最大内存

解决方法:


* 关闭redis 服务器`redis-cli shutdown`
* 修改配置文件的最大内存 「maxmemory」

* 启动redis服务器` redis-server redis.conf `


[0]: https://segmentfault.comhttp://7d9op5.com1.z0.glb.clouddn.com/2015/12/01/09def3694fd4880fa668161ee2ac73fd.png
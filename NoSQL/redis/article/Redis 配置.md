# Redis 配置

作者  [三产][0] 已关注 2017.06.16 16:55  字数 1886  阅读 0 评论 0 喜欢 0

Redis 的配置文件位于 Redis 安装目录下，文件名为 redis.conf。

可以通过 **CONFIG** 命令查看或设置配置项，该命令也支持动态设置配置。

## 查询配置

### 语法：CONFIG GET CONFIG_SETTING_NAME

### 示例

    127.0.0.1:6379> CONFIG GET loglevel
    1) "loglevel"
    2) "notice"

使用 * 号获取所有配置项：

### 示例

    127.0.0.1:6379> CONFIG GET *
      1) "dbfilename"
      2) "dump.rdb"
      3) "requirepass"
      4) "admin123"
      5) "masterauth"
      6) "148a6f61787d4ac5:ZnKe123qwe"
      7) "unixsocket"
      8) ""
      9) "logfile"
     10) ""
     11) "pidfile"
     12) ""
     13) "maxmemory"
     14) "0"
     15) "maxmemory-samples"
     16) "5"
     17) "timeout"
     18) "0"
     19) "auto-aof-rewrite-percentage"
     20) "100"
     21) "auto-aof-rewrite-min-size"
     22) "67108864"
     23) "hash-max-ziplist-entries"
     24) "512"
     25) "hash-max-ziplist-value"
     26) "64"
     27) "list-max-ziplist-size"
     28) "-2"
     29) "list-compress-depth"
     30) "0"
     31) "set-max-intset-entries"
     32) "512"
     33) "zset-max-ziplist-entries"
     34) "128"
     35) "zset-max-ziplist-value"
     36) "64"
     37) "hll-sparse-max-bytes"
     38) "3000"
     39) "lua-time-limit"
     40) "5000"
     41) "slowlog-log-slower-than"
     42) "10000"
     43) "latency-monitor-threshold"
     44) "0"
     45) "slowlog-max-len"
     46) "128"
     47) "port"
     48) "6379"
     49) "tcp-backlog"
     50) "511"
     51) "databases"
     52) "16"
     53) "repl-ping-slave-period"
     54) "10"
     55) "repl-timeout"
     56) "60"
     57) "repl-backlog-size"
     58) "1048576"
     59) "repl-backlog-ttl"
     60) "3600"
     61) "maxclients"
     62) "10000"
     63) "watchdog-period"
     64) "0"
     65) "slave-priority"
     66) "100"
     67) "min-slaves-to-write"
     68) "0"
     69) "min-slaves-max-lag"
     70) "10"
     71) "hz"
     72) "10"
     73) "cluster-node-timeout"
     74) "15000"
     75) "cluster-migration-barrier"
     76) "1"
     77) "cluster-slave-validity-factor"
     78) "10"
     79) "repl-diskless-sync-delay"
     80) "5"
     81) "tcp-keepalive"
     82) "0"
     83) "cluster-require-full-coverage"
     84) "yes"
     85) "no-appendfsync-on-rewrite"
     86) "no"
     87) "slave-serve-stale-data"
     88) "yes"
     89) "slave-read-only"
     90) "yes"
     91) "stop-writes-on-bgsave-error"
     92) "yes"
     93) "daemonize"
     94) "no"
     95) "rdbcompression"
     96) "yes"
     97) "rdbchecksum"
     98) "yes"
     99) "activerehashing"
    100) "yes"
    101) "protected-mode"
    102) "yes"
    103) "repl-disable-tcp-nodelay"
    104) "no"
    105) "repl-diskless-sync"
    106) "no"
    107) "aof-rewrite-incremental-fsync"
    108) "yes"
    109) "aof-load-truncated"
    110) "yes"
    111) "maxmemory-policy"
    112) "noeviction"
    113) "loglevel"
    114) "notice"
    115) "supervised"
    116) "no"
    117) "appendfsync"
    118) "everysec"
    119) "appendonly"
    120) "no"
    121) "dir"
    122) "D:\\redis"
    123) "save"
    124) "jd 900 jd 300 jd 60"
    125) "client-output-buffer-limit"
    126) "normal 0 0 0 slave 268435456 67108864 60 pubsub 33554432 8388608 60"
    127) "unixsocketperm"
    128) "0"
    129) "slaveof"
    130) ""
    131) "notify-keyspace-events"
    132) ""
    133) "bind"
    134) "127.0.0.1"

## 编辑配置

你可以通过修改 redis.conf 文件或使用 **CONFIG set** 命令来修改配置。

### 语法：CONFIG SET CONFIG_SETTING_NAME NEW_CONFIG_VALUE

### 示例

    redis 127.0.0.1:6379> CONFIG SET loglevel "notice"OKredis 127.0.0.1:6379> CONFIG GET loglevel 1) "loglevel"2) "notice"

## 参数说明

redis.conf 配置项说明如下：

1. Redis默认不是以守护进程的方式运行，可以通过该配置项修改，使用yes启用守护进程

​ **daemonize no**

1. 当Redis以守护进程方式运行时，Redis默认会把pid写入/var/run/redis.pid文件，可以通过pidfile指定

​ **pidfile /var/run/redis.pid**

1. 指定Redis监听端口，默认端口为6379，作者在自己的一篇博文中解释了为什么选用6379作为默认端口，因为6379在手机按键上MERZ对应的号码，而MERZ取自意大利歌女Alessia Merz的名字

​ **port 6379**

1. 绑定的主机地址

​ **bind 127.0.0.1**

1. 当 客户端闲置多长时间后关闭连接，如果指定为0，表示关闭该功能

​ **timeout 300**

1. 指定日志记录级别，Redis总共支持四个级别：debug、verbose、notice、warning，默认为verbose

​ **loglevel verbose**

1. 日志记录方式，默认为标准输出，如果配置Redis为守护进程方式运行，而这里又配置为日志记录方式为标准输出，则日志将会发送给/dev/null

​ **logfile stdout**

1. 设置数据库的数量，默认数据库为0，可以使用SELECT <dbid>命令在连接上指定数据库id

​ **databases 16**

1. 指定在多长时间内，有多少次更新操作，就将数据同步到数据文件，可以多个条件配合

​ **save <seconds> <changes>**

​ Redis默认配置文件中提供了三个条件：

​ **save 900 1**

​ **save 300 10**

​ **save 60 10000**

​ 分别表示900秒（15分钟）内有1个更改，300秒（5分钟）内有10个更改以及60秒内有10000个更改。

1. 指定存储至本地数据库时是否压缩数据，默认为yes，Redis采用LZF压缩，如果为了节省CPU时间，可以关闭该选项，但会导致数据库文件变的巨大

​ **rdbcompression yes**

1. 指定本地数据库文件名，默认值为dump.rdb

​ **dbfilename dump.rdb**

1. 指定本地数据库存放目录

​ **dir ./**

1. 设置当本机为slav服务时，设置master服务的IP地址及端口，在Redis启动时，它会自动从master进行数据同步

​ **slaveof <masterip> <masterport>**

1. 当master服务设置了密码保护时，slav服务连接master的密码

​ **masterauth <master-password>**

1. 设置Redis连接密码，如果配置了连接密码，客户端在连接Redis时需要通过AUTH <password>命令提供密码，默认关闭

​ **requirepass foobared**

1. 设置同一时间最大客户端连接数，默认无限制，Redis可以同时打开的客户端连接数为Redis进程可以打开的最大文件描述符数，如果设置 maxclients 0，表示不作限制。当客户端连接数到达限制时，Redis会关闭新的连接并向客户端返回max number of clients reached错误信息

​ **maxclients 128**

1. 指定Redis最大内存限制，Redis在启动时会把数据加载到内存中，达到最大内存后，Redis会先尝试清除已到期或即将到期的Key，当此方法处理 后，仍然到达最大内存设置，将无法再进行写入操作，但仍然可以进行读取操作。Redis新的vm机制，会把Key存放内存，Value会存放在swap区

​ **maxmemory <bytes>**

1. 指定是否在每次更新操作后进行日志记录，Redis在默认情况下是异步的把数据写入磁盘，如果不开启，可能会在断电时导致一段时间内的数据丢失。因为 redis本身同步数据文件是按上面save条件来同步的，所以有的数据会在一段时间内只存在于内存中。默认为no

​ **appendonly no**

1. 指定更新日志文件名，默认为appendonly.aof

​ **appendfilename appendonly.aof**

1. 指定更新日志条件，共有3个可选值： 

**no**：表示等操作系统进行数据缓存同步到磁盘（快） 

**always**：表示每次更新操作后手动调用fsync()将数据写到磁盘（慢，安全） 

**everysec**：表示每秒同步一次（折衷，默认值）

​ **appendfsync everysec**

1. 指定是否启用虚拟内存机制，默认值为no，简单的介绍一下，VM机制将数据分页存放，由Redis将访问量较少的页即冷数据swap到磁盘上，访问多的页面由磁盘自动换出到内存中（在后面的文章我会仔细分析Redis的VM机制）

​ **vm-enabled no**

1. 虚拟内存文件路径，默认值为/tmp/redis.swap，不可多个Redis实例共享

​ **vm-swap-file /tmp/redis.swap**

1. 将所有大于vm-max-memory的数据存入虚拟内存,无论vm-max-memory设置多小,所有索引数据都是内存存储的(Redis的索引数据 就是keys),也就是说,当vm-max-memory设置为0的时候,其实是所有value都存在于磁盘。默认值为0

​ **vm-max-memory 0**

1. Redis swap文件分成了很多的page，一个对象可以保存在多个page上面，但一个page上不能被多个对象共享，vm-page-size是要根据存储的 数据大小来设定的，作者建议如果存储很多小对象，page大小最好设置为32或者64bytes；如果存储很大大对象，则可以使用更大的page，如果不 确定，就使用默认值

​ **vm-page-size 32**

1. 设置swap文件中的page数量，由于页表（一种表示页面空闲或使用的bitmap）是在放在内存中的，，在磁盘上每8个pages将消耗1byte的内存。

​ **vm-pages 134217728**

1. 设置访问swap文件的线程数,最好不要超过机器的核数,如果设置为0,那么所有对swap文件的操作都是串行的，可能会造成比较长时间的延迟。默认值为4

​ **vm-max-threads 4**

1. 设置在向客户端应答时，是否把较小的包合并为一个包发送，默认为开启

​ **glueoutputbuf yes**

1. 指定在超过一定的数量或者最大的元素超过某一临界值时，采用一种特殊的哈希算法

​ **hash-max-zipmap-entries 64**

**hash-max-zipmap-value 512**

1. 指定是否激活重置哈希，默认为开启（后面在介绍Redis的哈希算法时具体介绍）

​ **activerehashing yes**

1. 指定包含其它的配置文件，可以在同一主机上多个Redis实例之间使用同一份配置文件，而同时各个实例又拥有自己的特定配置文件

​ **include /path/to/local.conf**

[0]: http://www.jianshu.com/u/2de721a368d3
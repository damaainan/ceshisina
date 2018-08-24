# 深入剖析Redis系列(一) - Redis入门简介与主从搭建

# 前言

Redis 是一种基于 **键值对** 的 NoSQL 数据库。与很多键值对数据库不同，Redis 提供了丰富的 **值数据存储结构**，包括 string(**字符串**)、hash(**哈希**)、list(**列表**)、set(**集合**)、zset(**有序集合**)、bitmap(**位图**)等等。

![][0]

# 正文

Redis 是一个使用 ANSI C 编写的开源、支持 **网络**、基于 **内存**、**单线程模型**、**可选持久性** 的 **键值对存储数据库**。

## 1. Redis的特性

1. 速度快，最快可达到 10w QPS（基于 **内存**，C 语言，**单线程** 架构）；
1. 基于 **键值对** (key/value) 的数据结构服务器。全称 Remote Dictionary Server。包括 string(**字符串**)、hash(**哈希**)、list(**列表**)、set(**集合**)、zset(**有序集合**)、bitmap(**位图**)。同时在 **字符串** 的基础上演变出 **位图**（BitMaps）和 HyperLogLog 两种数据结构。3.2 版本中加入 GEO（**地理信息位置**）。
1. 丰富的功能。例如：**键过期**（缓存），**发布订阅**（消息队列）， Lua 脚本（自己实现 Redis 命令），**事务**，**流水线**（Pipeline，用于减少网络开销）。
1. 简单稳定。无外部库依赖，单线程模型。
1. 客户端语言多。
1. **持久化**（支持两种 **持久化** 方式 RDB 和 AOF）。
1. **主从复制**（分布式的基础）。
1. **高可用**（Redis Sentinel），**分布式**（Redis Cluster）和 **水平扩容**。

## 2. Redis的应用场景

### 2.1. 缓存

合理的使用 **缓存** 能够明显加快访问的速度，同时降低数据源的压力。这也是 Redis 最常用的功能。Redis 提供了 **键值过期时间**（EXPIRE key seconds）设置，并且也提供了灵活控制 **最大内存** 和 **内存溢出** 后的 **淘汰策略**。

### 2.2. 排行榜

每个网站都有自己的排行榜，例如按照 **热度排名** 的排行榜，**发布时间** 的排行榜，**答题排行榜** 等等。Redis 提供了 **列表**（list）和 **有序集合**（zset）数据结构，合理的使用这些数据结构，可以很方便的构建各种排行榜系统。

### 2.3. 计数器

**计数器** 在网站应用中非常重要。例如：**点赞数**加 1，**浏览数** 加 1。还有常用的 **限流操作**，限制每个用户每秒 **访问系统的次数** 等等。Redis 支持 **计数功能**（INCR key），而且计数的 **性能** 也非常好，计数的同时也可以设置 **超时时间**，这样就可以 **实现限流**。

### 2.4. 社交网络

赞/踩，粉丝，共同好友/喜好，推送，下拉刷新等是社交网站必备的功能。由于社交网站 **访问量通常比较大**，而且 **传统的数据库** 不太适合保存这类数据，Redis 提供的 **数据结构** 可以相对比较容易实现这些功能。

### 2.5. 消息队列

Redis 提供的 **发布订阅**（PUB/SUB）和 **阻塞队列** 的功能，虽然和专业的消息队列比，还 **不够强大**，但对于一般的消息队列功能基本满足。

## 3. Redis的安装配置

下面介绍一下 Redis 的安装流程。我会按照如下的顺序，逐步搭建出 **高可用** 的 Redis 缓存服务器集群。

* Redis**单机服务器** 搭建
* Redis**主从复制** 搭建
* Redis-Sentinel**高可用** 搭建

### 3.1. Redis单机服务器安装

#### 3.1.1. 下载并解压

首先从 Redis 官网下载 Redis 源代码并解压，这里使用的是 **最新稳定版本**4.0.11。依次执行如下命令：

    cd /usr/local/
    wget http://download.redis.io/releases/redis-4.0.11.tar.gz
    tar -zxvf redis-4.0.2.tar.gz

#### 3.1.2. 编译并安装

下载并解压完毕后，则对 **源码包** 进行 **编译安装**，这里 Redis 安装路径为 /usr/local/redis。

> **注意**：make install PREFIX=目标安装路径

    cd /usr/local/redis-4.0.11
    make install PREFIX=/usr/local/redis

安装完成时，/usr/local/redis/bin 目录下会生成的几个可执行的文件。

可执行文件 作用 redis-server 启动 redis 服务 redis-cli redis 命令行客户端 redis-benchmark redis 基准测试工具 redis-check-aof redis AOF 持久化文件检测和修复工具 redis-check-dump redis RDB 持久化文件检测和修复工具 redis-sentinel 启动 redis sentinel 

复制 Redis 相关命令到 /usr/local/bin 目录下，这样就可以直接执行这些命令，不用写全路径。

    $ cd /usr/local/redis/bin/
    $ sudo sudo cp redis-cli redis-server redis-sentinel /usr/local/bin

#### 3.1.3. 修改Redis配置文件

安装完成之后将 Redis 配置文件拷贝到 /usr/local 下，redis.conf 是 Redis 的配置文件，redis.conf 在 Redis 源码目录，port 默认是 6379。

    $ sudo cp /usr/local/redis-4.0.11/redis.conf /usr/local/

Redis 配置文件主要参数解析参考：

    # redis进程是否以守护进程的方式运行，yes为是，no为否(不以守护进程的方式运行会占用一个终端)。
    daemonize no
    # 指定redis进程的PID文件存放位置
    pidfile /var/run/redis.pid
    # redis进程的端口号
    port 6379
    # 绑定的主机地址
    bind 127.0.0.1
    # 客户端闲置多长时间后关闭连接，默认此参数为0即关闭此功能
    timeout 300
    # redis日志级别，可用的级别有debug.verbose.notice.warning
    loglevel verbose
    # log文件输出位置，如果进程以守护进程的方式运行，此处又将输出文件设置为stdout的话，就会将日志信息输出到/dev/null里面去了
    logfile stdout
    # 设置数据库的数量，默认为0可以使用select <dbid>命令在连接上指定数据库id
    databases 16
    # 指定在多少时间内刷新次数达到多少的时候会将数据同步到数据文件
    save <seconds> <changes>
    # 指定存储至本地数据库时是否压缩文件，默认为yes即启用存储
    rdbcompression yes
    # 指定本地数据库文件名
    dbfilename dump.db
    # 指定本地数据问就按存放位置
    dir ./
    # 指定当本机为slave服务时，设置master服务的IP地址及端口，在redis启动的时候他会自动跟master进行数据同步
    slaveof <masterip> <masterport>
    # 当master设置了密码保护时，slave服务连接master的密码
    masterauth <master-password>
    # 设置redis连接密码，如果配置了连接密码，客户端在连接redis是需要通过AUTH<password>命令提供密码，默认关闭
    requirepass footbared
    # 设置同一时间最大客户连接数，默认无限制。redis可以同时连接的客户端数为redis程序可以打开的最大文件描述符，如果设置 maxclients 0，表示不作限制。当客户端连接数到达限制时，Redis会关闭新的连接并向客户端返回 max number of clients reached 错误信息
    maxclients 128
    # 指定Redis最大内存限制，Redis在启动时会把数据加载到内存中，达到最大内存后，Redis会先尝试清除已到期或即将到期的Key。当此方法处理后，仍然到达最大内存设置，将无法再进行写入操作，但仍然可以进行读取操作。Redis新的vm机制，会把Key存放内存，Value会存放在swap区
    maxmemory<bytes>
    # 指定是否在每次更新操作后进行日志记录，Redis在默认情况下是异步的把数据写入磁盘，如果不开启，可能会在断电时导致一段时间内的数据丢失。因为redis本身同步数据文件是按上面save条件来同步的，所以有的数据会在一段时间内只存在于内存中。默认为no。
    appendonly no
    # 指定跟新日志文件名默认为appendonly.aof
    appendfilename appendonly.aof
    # 指定更新日志的条件，有三个可选参数 - no：表示等操作系统进行数据缓存同步到磁盘(快)，always：表示每次更新操作后手动调用fsync()将数据写到磁盘(慢，安全)， everysec：表示每秒同步一次(折衷，默认值)；
    appendfsync everysec

* 设置后台启动

由于 Redis 默认是 **前台启动**，不建议使用。修改 Redis 配置文件，把 daemonize no 改为 daemonize yes。

    daemonize yes

* 设置远程访问

Redis 默认只允许 **本机访问**，把 bind 修改为 bind 0.0.0.0 此设置会变成 **允许所有远程访问**。如果想指定限制访问，可设置对应的 IP。

    bind 0.0.0.0

* 配置 Redis 日志记录

找到 logfile 配置，默认是：logfile ""，改为自定义日志文件路径。

    logfile /var/log/redis_6379.log

* 设置 Redis 请求密码

把 requirepass 修改为 123456，修改之后重启下服务

    requirepass "123456"

有了密码之后，进入客户端，就得这样访问：

    $ redis-cli -h 127.0.0.1 -p 6379 -a 123456

#### 3.1.4. Redis的常用命令

* 启动命令
```
    $ redis-server /usr/local/redis.conf
```
* 关闭命令
```
    $ redis-cli -h 127.0.0.1 -p 6379 shutdown
```
* 查看是否启动
```
    $ ps -ef | grep redis
```
* 进入客户端
```
    $ redis-cli
```
* 关闭客户端
```
    $ redis-cli shutdown
```
> **注意**：不建议使用 kill -9，这种方式不但不会做持久化操作，还会造成缓冲区等资源不能优雅关闭。极端情况下造成 AOF 和 **复制丢失数据** 的情况。shutdown 还有一个参数，代表是否在关闭 redis 前，生成 **持久化文件**，命令为 redis-cli shutdown nosave|save。

* 设置为开机自动启动
```
    $ echo "redis-server /usr/local/redis.conf" >> /etc/rc.local
```
* 开放防火墙端口
```
    # 添加规则
    iptables -I INPUT -p tcp -m tcp --dport 6379 -j ACCEPT
    # 保存规则
    service iptables save
    # 重启iptables
    service iptables restart
```
#### 3.1.5. 注册Redis为系统服务

在 /etc/init.d 目录下添加 Redis 服务的 **启动**，**暂停** 和 **重启** 脚本：

    $ sudo /etc/init.d/redis

脚本的内容如下：

    #!/bin/sh  
    #  
    # redis - this script starts and stops the redis-server daemon  
    #  
    # chkconfig:   - 85 15  
    # description:  Redis is a persistent key-value database  
    # processname: redis-server  
    # config:      /usr/local/redis/bin/redis-server
    # config:      /etc/redis.conf  
    # Source function library.  
    . /etc/rc.d/init.d/functions  
    # Source networking configuration.  
    . /etc/sysconfig/network  
    # Check that networking is up.  
    [ "$NETWORKING" = "no" ] && exit 0  
    redis="/usr/local/redis/bin/redis-server" 
    prog=$(basename $redis)  
    REDIS_CONF_FILE="/etc/redis.conf" 
    [ -f /etc/sysconfig/redis ] && . /etc/sysconfig/redis  
    lockfile=/var/lock/subsys/redis  
    start() {  
        [ -x $redis ] || exit 5  
        [ -f $REDIS_CONF_FILE ] || exit 6  
        echo -n $"Starting $prog: "  
        daemon $redis $REDIS_CONF_FILE  
        retval=$?  
        echo  
        [ $retval -eq 0 ] && touch $lockfile  
        return $retval  
    }  
    stop() {  
        echo -n $"Stopping $prog: "  
        killproc $prog -QUIT  
        retval=$?  
        echo  
        [ $retval -eq 0 ] && rm -f $lockfile  
        return $retval  
    }  
    restart() {  
        stop  
        start  
    }  
    reload() {  
        echo -n $"Reloading $prog: "  
        killproc $redis -HUP  
        RETVAL=$?  
        echo  
    }  
    force_reload() {  
        restart  
    }  
    rh_status() {  
        status $prog  
    }  
    rh_status_q() {  
        rh_status >/dev/null 2>&1  
    }  
    case "$1" in  
        start)  
            rh_status_q && exit 0  
            $1  
            ;;  
        stop)  
            rh_status_q || exit 0  
            $1  
            ;;  
        restart|configtest)  
            $1  
            ;;  
        reload)  
            rh_status_q || exit 7  
            $1  
            ;;  
        force-reload)  
            force_reload  
            ;;  
        status)  
            rh_status  
            ;;  
        condrestart|try-restart)  
            rh_status_q || exit 0  
        ;;  
        *)  
            echo $"Usage: $0 {start|stop|status|restart|condrestart|try-restart|reload|orce-reload}"  
            exit 2  
    esac

赋予脚本文件可执行权限：

    $ chmod 755 /etc/init.d/redis

启动、停止和重启 redis 服务：

    service redis start
    service redis stop
    service redis restart

### 3.2. Redis主从复制集群安装

#### 3.2.1. Redis-Server配置说明

角色 IP地址 端口号 Redis Master 10.206.20.231 16379 Redis Slave 10.206.20.231 26379 

#### 3.2.2. Redis主从架构配置

* 编辑 **从机** 的 Redis 配置文件，找到 210 行（大概）- #slaveof <masterip> <masterport> 。去掉该注释，填写 **主服务器** 的 IP 和 **端口**。
```
    slaveof 10.206.20.231 16379
```
* 如果 **主服务器** 设置了密码，还需要找到 masterauth <master-password> 这一行，去掉注释，改为 masterauth 的主机密码。
```
    masterauth 123456
```
* 配置完成后重启 **从服务器** 的 Redis 服务。
```
    $ service redis restart
```
* 重启完成之后，进入 **主服务器** 的 redis-cli 模式下，命令为 redis-cli -h 127.0.0.1 -p 16379 -a 123456。输入 INFO replication 查询到 **当前主机** 的 Redis 的状态，连接上 **主服务器** 的 **从服务器**。

Redis**主服务器** 的配置文件：

* redis.conf
```
    daemonize yes
    pidfile /var/run/redis-16379.pid
    logfile /var/log/redis/redis-16379.log
    port 16379
    bind 0.0.0.0
    timeout 300
    databases 16
    dbfilename dump-16379.db
    dir ./redis-workdir
    masterauth 123456
    requirepass 123456
```
Redis**从服务器** 的配置文件：

* redis.conf
```
    daemonize yes
    pidfile /var/run/redis-26379.pid
    logfile /var/log/redis/redis-26379.log
    port 26379
    bind 0.0.0.0
    timeout 300
    databases 16
    dbfilename dump-26379.db
    dir ./redis-workdir
    masterauth 123456
    requirepass 123456
    slaveof 127.0.0.1 16379
```

Redis**主服务器** 的状态如下：

    # Replication
    role:master
    connected_slaves:1
    slave0:ip=10.206.20.231,port=16379,state=online,offset=28,lag=1
    master_replid:625ae9f362643da5337835beaeabfdca426198c7
    master_replid2:0000000000000000000000000000000000000000
    master_repl_offset:28
    second_repl_offset:-1
    repl_backlog_active:1
    repl_backlog_size:1048576
    repl_backlog_first_byte_offset:1
    repl_backlog_histlen:28

Redis**从服务器** 的状态如下：

    # Replication
    role:slave
    master_host:10.206.20.231
    master_port:26379
    master_link_status:up
    master_last_io_seconds_ago:3
    master_sync_in_progress:0
    slave_repl_offset:210
    slave_priority:100
    slave_read_only:1
    connected_slaves:0
    master_replid:625ae9f362643da5337835beaeabfdca426198c7
    master_replid2:0000000000000000000000000000000000000000
    master_repl_offset:210
    second_repl_offset:-1
    repl_backlog_active:1
    repl_backlog_size:1048576
    repl_backlog_first_byte_offset:1
    repl_backlog_histlen:210

#### 3.2.3. Redis主从配置验证

上面完成了基本的 **主从配置**，可以简单的测试一下效果：

* 进入 **主服务器** 的 redis-cli 模式，然后 set 一个值，比如：
```
    > set master_port "16379"
    OK
```
* 切换进入 **从服务器** 的 redis-cli 的模式，查询刚刚设置的值看是否存在：
```
    > get master_port
    "16379"
```
此时，我们可以发现是可以获取到值的，Redis 的 **主从模式** 正常工作。

# 小结

本文简单的说明了 Redis 的相关 **特性** 和 **应用场景**，详细地给出 Redis 单服务器的 **编译**，**安装**，**配置** 和 **启动**，进一步引入了 Redis**主从复制** 的相关原理和详细配置。关于 Redis 的 **高可用机制** 和 **集群搭建**，下文将给出详细的说明。

# 参考

《Redis 开发与运维》

[0]: https://user-gold-cdn.xitu.io/2018/8/22/16560ce61de7471a?imageView2/0/w/1280/h/960/format/webp/ignore-error/1
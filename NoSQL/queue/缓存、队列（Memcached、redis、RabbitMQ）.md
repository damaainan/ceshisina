# [缓存、队列（Memcached、redis、RabbitMQ）][0]本章内容：

* Memcached 
  * 简介、安装、使用
  * Python 操作 Memcached
  * 天生支持集群
* redis 
  * 简介、安装、使用、实例
  * Python 操作 Redis
  * String、Hash、List、Set、Sort Set 操作
  * 管道
  * 发布订阅

* RabbitMQ 
  * 简介、安装、使用
  * 使用 API 操作 RabbitMQ
  * 消息不丢失
  * 发布订阅
  * 关键字发送
  * 模糊匹配

## 一、Memcached

### 1、简介、安装、使用

Memcached 是一个高性能的分布式内存对象缓存系统，用于动态 Web 应用以减轻数据库负载压力。它通过在内存中缓存数据和对象来减少读取数据库的次数，从而提高动态、数据库驱动网站的速度。Memcached 基于一个存储键/值对的 [hashmap][1]。其[守护进程][2]（daemon ）是用 [C][3] 写的，但是客户端可以用任何语言来编写，并通过 memcached 协议与守护进程通信。

Memcached 内存管理机制：

Menceched 通过预分配指定的内存空间来存取数据，所有的数据都保存在 memcached 内置的内存中。

利用 Slab Allocation 机制来分配和管理内存。按照预先规定的大小，将分配的内存分割成特定长度的内存块，再把尺寸相同的内存块分成组，这些内存块不会释放，可以重复利用。

当存入的数据占满内存空间时，Memcached 使用 LRU 算法自动删除不是用的缓存数据，即重用过期数据的内存空间。Memcached 是为缓存系统设计的，因此没有考虑数据的容灾问题，和机器的内存一样，重启机器将会丢失，如果希望服务重启数据依然能保留，那么就需要 sina 网开发的 Memcachedb 持久性内存缓冲系统，当然还有常见的 NOSQL 服务如 redis。

默认监听端口：11211

Memcached 安装

 


    wget http://memcached.org/latest
    tar -zxvf memcached-1.x.x.tar.gz
    cd memcached-1.x.x
    ./configure && make && make test && sudo make install
     
    PS：依赖libevent
           yum install libevent-devel
           apt-get install libevent-dev


 


    # Memcached 服务安装
    
    # 1、安装libevent
    mkdir /home/oldsuo/tools/
    cd /home/oldsuo/tools/
    wget http://down1.chinaunix.net/distfiles/libevent-2.0.21-stable.tar.gz
    ls libevent-2.0.21-stable.tar.gz
    tar zxf libevent-2.0.21-stable.tar.gz
    cd libevent-2.0.21-stable
    ./configure 
    make && make install
    echo $?
    cd ..
    
    # 2、安装Memcached
    wget  http://memcached.org/files/memcached-1.4.24.tar.gz
    tar zxf memcached-1.4.24.tar.gz
    cd memcached-1.4.24
    ./configure
    make
    make install
    echo $?
    cd ..
    
    # PS :
    memcached-1.4.24.tar    -->客户端
    memcached-1.4.24.tar.gz -->服务端
    
    # 3、启动及关闭服务
    echo "/usr/local/lib" >> /etc/ld.so.conf
    ldconfig
    
    # 查看帮助
    /usr/local/bin/memcached –h
    
    # 启动Memcached服务
    memcached -p 11211 -u root -m 16m -c 10240 –d
    
    # 查看启动状态
    lsof -i :11211
    
    # 关闭服务
    pkill memcached
    # memcached -p 11212 -u root -m 16m -c 10240 -d -P /var/run/11212.pid
    # kill `cat /var/run/11212.pid`
    
    # PS：开机自启动把上述启动命令放入/etc/rc.local


 源码安装启动 Memcached 快速部署文档

 


    # Memcached PHP 客户端安装
    
    cd /home/oldsuo/tools/
    wget http://pecl.php.net/get/memcache-3.0.7.tgz
    tar zxf memcache-3.0.7.tgz
    cd memcache-3.0.7
    /application/php/bin/phpize
    ./configure --enable-memcahce --with-php-config=/application/php/bin/php-config --with-zlib-dir
    make
    make install
    
    # 安装完成后会有类似这样的提示：
    Installing shared extensions:     /application/php5.3.27/lib/php/extensions/no-debug-zts-20131226/
    [root@localhost memcache-3.0.7]# ll /application/php5.3.27/lib/php/extensions/no-debug-zts-20131226/
    total 1132
    -rwxr-xr-x  1 root root 452913 Nov 17 16:52 memcache.so
    -rwxr-xr-x. 1 root root 157862 Oct  9 21:01 mysql.so
    -rwxr-xr-x. 1 root root 542460 Oct  9 19:25 opcache.so
    
    # 编辑php.ini文件，添加extension = memcache.so 一行
    vim /application/php/lib/php.ini
    Extension_dir = "/application/php5.3.27/lib/php/extensions/no-debug-zts-20131226/"
    extension = memcache.so
    
    # 重启 apache 服务是PHP的配置生效
    [root@localhost application]# /usr/local/apache/bin/apachectl -t
    Syntax OK
    [root@localhost application]# /usr/local/apache/bin/apachectl graceful


 源码安装 Memcached PHP 客户端

Memcached 启动

 


    memcached -d -m 10 -u root -l 218.97.240.118 -p 12000 -c 256 -P /tmp/memcached.pid
     
    参数说明:
        -d 是启动一个守护进程
        -m 是分配给Memcache使用的内存数量，单位是MB
        -u 是运行Memcache的用户
        -l 是监听的服务器IP地址
        -p 是设置Memcache监听的端口,最好是1024以上的端口
        -c 选项是最大运行的并发连接数，默认是1024，按照你服务器的负载量来设定
        -P 是设置保存Memcache的pid文件


Memcached 命令

    存储命令: set/add/replace/append/prepend/cas
    获取命令: get/gets
    其他命令: delete/stats..

Memcached 管理

 


    #1、telnet ip port 方式管理
    telnet 127.0.0.1 11211
    
    #2、命令直接操作，nc这样的命令
    [root@localhost application]# printf "stats slabs\r\n"|nc 127.0.0.1 11211    
    STAT active_slabs 0
    STAT total_malloced 0
    END
    
    #3、管理 Memcached 命令
    a、stats           统计Memcached的各种信息。
    b、stats reset     重新统计数据，重新开始统计。
    c、stats slabs     显示slabs信息。通过这命令能获取每个slabs的chunksize长度，从而确定数据保存在哪个slab。
    d、stats items     显示slab中的item数目。
    e、stats setting   查看一些Memcached设置，列如线程数….
    f、stats slabs     查看slabs相关情况。
    g、stats sizes     查看存在Item个数和大小。
    h、stats cachedump 查看key value。
    i、stats reset     清理统计数据。
    j、set|get,gets    用来保存或获取数据。


    # memadmin php 工具管理（memcadmin-1.0.12.tar.gz）
    
    1、安装memadmin php工具。
    cd /home/oldsuo/tools
    wget http://www.junopen.com/memadmin/memadmin-1.0.12.tar.gz
    tar zxf memadmin-1.0.12.tar.gz -C /usr/local/apache/htdocs/
    ll /usr/local/apache/htdocs/memadmin/
    
    2、 登陆memadmin php。
    web方式访问：http://IP地址/memadmin/
    默认用户名密码都为admin。

 Memcached memadmin php工具界面化管理安装部署文档

### 2、Python 操作 Memcached 

#### 1> 安装 API 及 基本操作

 


    python 操作 Memcached 使用 Python-memcached 模块
    下载安装：https://pypi.python.org/pypi/python-memcached
    
    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
    mc.set("foo", "bar")
    ret = mc.get('foo')
    print ret


#### 2> 天生支持集群

python-memcached 模块原生支持集群操作，其原理本质是在内存维护一个主机列表，数字为权重，为3即出现3次，相对应的几率大

 


    mc = memcache.Client([
        ('192.168.1.5:12000', 3),        # 数字为权重
        ('192.168.1.9:12000', 1),
    ], debug=True)
    
    # 那么在内存中主机列表为：
    #    host_list = ["192.168.1.5","192.168.1.5","192.168.1.5","192.168.1.9",]


**那么问题来了，集群情况下如何选择服务器存储呢？**

如果要创建设置一个键值对（如：k1 = "v1"），那么它的执行流程如下：

1. 将 k1 转换成一个数字
1. 将数字和主机列表的长度求余数，得到一个值 N（N 的范围： 0 <= N < 列表长度 ）
1. 在主机列表中根据 第2步得到的值为索引获取主机，例如：host_list[N]
1. 连接 将第3步中获取的主机，将 k1 = "v1" 放置在该服务器的内存中

获取值的话也一样

 


    #!/usr/bin/env python
    #-*- coding:utf-8 -*-
    __author__ = 'Nick Suo'
    
    import binascii
    
    str_input = 'suoning'
    str_bytes = bytes(str_input, encoding='utf-8')
    num = (((binascii.crc32(str_bytes) & 0xffffffff) >> 16) & 0x7fff) or 1
    print(num)


 源码、将字符串转换为数字

#### 3> add

添加一个键值对，如果 key 已经存在，重复添加执行 add 则抛出异常

    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
    mc.add('k1', 'v1')
    # mc.add('k1', 'v2') # 报错，对已经存在的key重复添加，失败！！！

#### 4> replace

replace 修改某个 key 的值，如果 key 不存在，则异常

    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
    # 如果memcache中存在kkkk，则替换成功，否则一场
    mc.replace('kkkk','999')

#### 5> **set 和 set_multi**

set 设置一个键值对，如果 key 不存在，则创建  
set_multi 设置多个键值对，如果 key 不存在，则创建

    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
     
    mc.set('name', 'nick')
    mc.set_multi({'name': 'nick', 'age': '18'})

#### 6> **delete 和 delete_multi**

delete 删除指定的一个键值对  
delete_multi 删除指定的多个键值对

    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
     
    mc..delete('name', 'nick')
    mc.delete_multi({'name': 'nick', 'age': '18'})

#### 7> **get 和 get_multi**

get 获取一个键值对  
get_multi 获取多个键值对

    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
     
    val = mc.get('name')
    item_dict = mc.get_multi(["name", "age",])

#### 8> **append 和 prepend**

append 修改指定key的值，在该值 后面 追加内容  
prepend 修改指定key的值，在该值 前面 插入内容

 


    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
    # 原始值： k1 = "v1"
    
    mc.append('k1', 'after')
    # k1 = "v1after"
     
    mc.prepend('k1', 'before')
    # k1 = "beforev1after"


#### 9> **decr 和 incr**

incr 自增，将 Memcached 中的某个值增加 N （ N 默认为1 ）  
decr 自减，将 Memcached 中的某个值减少 N （ N 默认为1 ）

 


    import memcache
     
    mc = memcache.Client(['192.168.1.5:12000'], debug=True)
    mc.set('k1', '666')
     
    mc.incr('k1')
    # k1 = 667
     
    mc.incr('k1', 10)
    # k1 = 677
     
    mc.decr('k1')
    # k1 = 676
     
    mc.decr('k1', 10)
    # k1 = 666


#### 10> **gets 和 cas**

这两个方法就是传说中的 **锁**

为了避免脏数据的产生而生

    import memcache
    mc = memcache.Client(['192.168.1.5:12000'], debug=True, cache_cas=True)
     
    v = mc.gets('product_count')
    # 如果有人在gets之后和cas之前修改了product_count，那下面的设置将会执行失败，剖出异常
    mc.cas('product_count', "899")

本质：每次执行 gets 时，就从 memcache 中获取一个自增的数字，通过 cas 去修改 gets 到的值时，会携带之前获取的自增值和 memcache 中的自增值进行比较，如果相等，则可以提交，如果不相等，那表示在 gets 和 cas 执行之间，又有其他人执行了 gets（获取了缓冲的指定值），如此一来有可能出现非正常的数据，则不允许修改，并报错。

## 二、redis

### 1、简介、安装、使用、实例

Remote Dictionary Server（Redis）是一个基于 key-value 键值对的持久化数据库存储系统。redis 和 Memcached 缓存服务很像，但它支持存储的 value 类型相对更多，包括 string (字符串)、list ([链表][6])、set (集合)、zset (sorted set --有序集合)和 hash（哈希类型）。这些[数据类型][7]都支持 push/pop、add/remove 及取交集并集和差集及更丰富的操作，而且这些操作都是原子性的。在此基础上，redis 支持各种不同方式的排序。与 memcached 一样，为了保证效率，数据都是缓存在内存中。区别的是 redis 会周期性的把更新的数据写入磁盘或者把修改操作写入追加的记录文件，并且在此基础上实现了 master-slave (主从)同步。

redis 的出现，再一定程度上弥补了 Memcached 这类 key-value 内存换乘服务的不足，在部分场合可以对关系数据库起到很好的补充作用。redis 提供了 Python，Ruby，Erlang，PHP 客户端，使用方便。

官方文档：[http://www.redis.io/documentation][8]

[http://www.redis.cn/][9]

#### Redis 安装和使用实例

 


    # Ubuntu 安装 redis
    $ sudo apt-get install redis-server
    
    # 启动服务端
    $ sudo service redis-server {start|stop|restart|force-reload|status}
    
    # 启动服务端
    $ sudo redis-cli


 


    # 源码安装
    wget http://download.redis.io/releases/redis-3.0.6.tar.gz
    tar xzf redis-3.0.6.tar.gz
    cd redis-3.0.6
    make
    
    # 启动服务端
    src/redis-server
    
    # 启动客户端
    src/redis-cli


 


    # 检测后台进程是否存在
    ps -ef |grep redis
    
    # 检测6379端口是否在监听
    netstat -lntp | grep 6379
    
    # 客户端连接
    $ sudo redis-cli
    127.0.0.1:6379> set foo bar
    OK
    127.0.0.1:6379> get foo
    "bar"


 


    wget http://download.redis.io/releases/redis-3.0.5.tar.gz
    tar zxf redis-3.0.5.tar.gz
    cd redis-3.0.5
    #less README
    make MALLOC=jemalloc
    make PREFIX=/application/redis-3.0.5 install        -->指定安装路径
    echo $?
    ln -s /application/redis-3.0.5/ /application/redis


 redis 源码快速安装文档

 


    [root@localhost redis-3.0.5]# tree /application/redis
    /application/redis
    `-- bin
        |-- redis-benchmark     # Redis性能测试工具，测试Redis在系统及你的配置下的读写性能。
        |-- redis-check-aof     # 更新日志检查。
        |-- redis-check-dump    # 用于本地数据库检查。
        |-- redis-cli           # Redis命令行操作工具。也可以telnet根据其纯文本协议操作
        |-- redis-sentinel -> redis-server
        `-- redis-server        # Redis服务器的daemon启动程序。
    1 directory, 6 files


 redis 安装目录及各文件作用

 


    # 1、 配置环境变量
    # 编辑vim /etc/profile添加一行
    vim /etc/profile
    export PATH=/application/redis/bin/:$PATH
    tail -1 /etc/profile  -->检查    
    source /etc/profile   -->生效
    
    echo export PATH=/application/redis/bin/:$PATH >> /etc/profile
    tail -1 /etc/profile
    source /etc/profile
    
    
    # 2、 拷贝配置文件
    [root@localhost redis-3.0.5]# pwd
    /home/oldSuo/tools/redis-3.0.5    -->解压目录
    [root@localhost redis-3.0.5]# mkdir /application/redis/conf
    [root@localhost redis-3.0.5]# cp redis.conf /application/redis/conf/
    
    cd /home/oldSuo/tools/redis-3.0.5
    mkdir /application/redis/conf
    cp redis.conf /application/redis/conf/
    
    
    # 3、 启动redis
    redis-server /application/redis/conf/redis.conf &
    lsof -i :6379
    
    [root@localhost redis-3.0.5]# lsof -i :6379
    COMMAND    PID USER   FD   TYPE    DEVICE SIZE/OFF NODE NAME
    redis-ser 5876 root    4u  IPv6 793678202      0t0  TCP *:6379 (LISTEN)
    redis-ser 5876 root    5u  IPv4 793678204      0t0  TCP *:6379 (LISTEN)
    
    
    # 4、 关闭redis
    redis-cli shutdown
    lsof -i :6379    -->检查端口
    
    
    #5、 启动常见报错
    报错：WARNING overcommit_memory is set to 0! Background save may fail under low memory condition. To fix this issue add 'vm.overcommit_memory = 1' to /etc/sysctl.conf and then reboot or run the command 'sysctl vm.overcommit_memory=1' for this to take effect.
    解决：    [root@localhost redis-3.0.5]# killall redis-server
             [root@localhost redis-3.0.5]# sysctl vm.overcommit_memory=1
             vm.overcommit_memory = 1
    永久生效：[root@localhost conf]# vim /etc/sysctl.conf
    添加一行vm.overcommit_memory = 1


 配置并启动 redis 服务

 


    [root@localhost conf]# redis-cli --help
    [root@localhost conf]# redis-cli -h 192.168.200.95
    
    [root@localhost conf]# redis-cli       
    127.0.0.1:6379> help
    redis-cli 3.0.5
    Type: "help @<group>" to get a list of commands in <group>
          "help <command>" for help on <command>
          "help <tab>" to get a list of possible help topics
          "quit" to exit
    127.0.0.1:6379> help get
    
      GET key
      summary: Get the value of a key
      since: 1.0.0
      group: string
    
    127.0.0.1:6379> help set
    
      SET key value [EX seconds] [PX milliseconds] [NX|XX]
      summary: Set the string value of a key
      since: 1.0.0
      group: string
    
    127.0.0.1:6379> set 007 oldSuo
    OK
    127.0.0.1:6379> get 007
    "oldSuo"
    127.0.0.1:6379>
    
    或者
    [root@localhost conf]# redis-cli -h 192.168.200.95 -p 6379 set no005 suoning
    OK
    [root@localhost conf]# redis-cli -h 192.168.200.95 -p 6379 get no005         
    "suoning"
    
    删除并检查
    [root@localhost conf]# redis-cli del no005
    (integer) 1
    [root@localhost conf]# redis-cli get no005
    (nil)


 客户端连接命令及命令测试

 


    # 1、下载安装
    wget https://github.com/phpredis/phpredis/archive/master.zip
    
    unzip phpredis-master.zip
    cd phpredis-master
    /application/php/bin/phpize
    ./configure --with-php-config=/application/php/bin/php-config
    make
    make install
    
    [root@localhost phpredis-master]# make install
    Installing shared extensions:     /application/php-5.6.8/lib/php/extensions/no-debug-non-zts-20131226/
    [root@localhost phpredis-master]# cd /application/php-5.6.8/lib/php/extensions/no-debug-non-zts-20131226/
    [root@localhost no-debug-non-zts-20131226]# ls
    memcache.so  opcache.a  opcache.so  redis.so
    [root@localhost no-debug-non-zts-20131226]#
    
    
    # 2、修改php.ini设置，重启php
    在php.ini追加一条记录
    echo "extension = redis.so" >> /application/php/lib/php.ini
    
    #重启 php-fpm
    killall php-fpm
    /application/php/sbin/php-fpm
    
    #网页测试
    ......


 redis 的 php 客户端拓展安装

 


    # 1、修改从库redis.conf配置文件
    #配置从库redis.conf配置文件(先装redis)
    #添加一行，主库IP地址及端口
    vim /application/redis/conf/redis.conf
    # slaveof <masterip> <masterport>
    slaveof 192.168.200.95 6379
    
    
    # 2、重启从库redis服务
    pkill redis
    redis-server /application/redis/conf/redis.conf &
    
    #启动提示
    7815:S 23 Nov 19:48:52.059 # Server started, Redis version 3.0.5
    7815:S 23 Nov 19:48:52.060 * The server is now ready to accept connections on port 6379
    7815:S 23 Nov 19:48:53.060 * Connecting to MASTER 192.168.200.95:6379     -->跟主库建立连接
    7815:S 23 Nov 19:48:53.060 * MASTER <-> SLAVE sync started                -->主从同步已经开始
    7815:S 23 Nov 19:48:53.062 * Non blocking connect for SYNC fired the event.
    7815:S 23 Nov 19:48:53.074 * Master replied to PING, replication can continue...      -->主从ping可以继续
    7815:S 23 Nov 19:48:53.075 * Partial resynchronization not possible (no cached master)
    7815:S 23 Nov 19:48:53.087 * Full resync from master: 24b26f7abc62830a7ff97516c960ba7fc0992da9:1
    7815:S 23 Nov 19:48:53.122 * MASTER <-> SLAVE sync: receiving 32 bytes from master    -->接收到字节数
    7815:S 23 Nov 19:48:53.122 * MASTER <-> SLAVE sync: Flushing old data
    7815:S 23 Nov 19:48:53.122 * MASTER <-> SLAVE sync: Loading DB in memory
    7815:S 23 Nov 19:48:53.122 * MASTER <-> SLAVE sync: Finished with success  -->成功
    
    
    # 3、测试主从同步
    # 主库：写数据
    [root@localhost redis]# redis-cli 
    127.0.0.1:6379> set test1 oldsuo
    OK
    
    # 从库：
    [root@localhost conf]# redis-cli -h localhost -p 6379 monitor      -->开启实时监控
    OK
    1448280033.096372 [0 192.168.200.95:6379] "PING"
    1448280043.125830 [0 192.168.200.95:6379] "PING"
    1448280053.154134 [0 192.168.200.95:6379] "PING"
    1448280070.858808 [0 192.168.200.95:6379] "SELECT" "0"
    1448280070.858828 [0 192.168.200.95:6379] "set" "test1" "oldsuo"   -->主库添加数据，从库同步
    
    [root@localhost redis]# redis-cli -h 192.168.200.92 get test1
    "oldsuo"        -->从库同步成功


 redis 主从同步

    至于 redis 的负载均衡，方案有很多：
    LVS、keepalived、Twemproxy
    小编有时间再补上吧...

 reidis 负载均衡

 


    Redis持久化方式有两种：
    
    （1）RDB
    
    对内存中数据库状态进行快照
    
    （2）AOF
    
    把每条写命令都写入文件，类似mysql的binlog日志
    
    RDB
    
    将Redis在内存中的数据库状态保存到磁盘里面，RDB文件是一个经过压缩的二进制文件，通过该文件可以还原生成RDB文件时的数据库状态
    
    RDB的生成方式：
    
    （1）执行命令手动生成
    
    有两个Redis命令可以用于生成RDB文件，一个是SAVE，另一个是BGSAVE
    
    SAVE命令会阻塞Redis服务器进程，直到RDB文件创建完毕为止，在服务器进程阻塞期间，服务器不能处理任何命令请求
    
    BGSAVE命令会派生出一个子进程，然后由子进程负责创建RDB文件，服务器进程（父进程）继续处理命令请求，创建RDB文件结束之前，客户端发送的BGSAVE和SAVE命令会被服务器拒绝
    
    （2）通过配置自动生成
    
    可以设置服务器配置的save选项，让服务器每隔一段时间自动执行一次BGSAVE命令
    
    可以通过save选项设置多个保存条件，但只要其中任意一个条件被满足，服务器就会执行BGSAVE命令
    
    例如：
    
    save 900 1
    save 300 10
    save 60 10000
    
    那么只要满足以下三个条件中的任意一个，BGSAVE命令就会被执行
    
    服务器在900秒之内，对数据库进行了至少1次修改 
    服务器在300秒之内，对数据库进行了至少10次修改 
    服务器在60秒之内，对数据库进行了至少10000次修改
    
    AOF
    
    AOF持久化是通过保存Redis服务器所执行的写命令来记录数据库状态的
    
    AOF文件刷新的方式，有三种
    
    （1）appendfsync always - 每提交一个修改命令都调用fsync刷新到AOF文件，非常非常慢，但也非常安全
    
    （2）appendfsync everysec - 每秒钟都调用fsync刷新到AOF文件，很快，但可能会丢失一秒以内的数据
    
    （3）appendfsync no - 依靠OS进行刷新，redis不主动刷新AOF，这样最快，但安全性就差
    
    默认并推荐每秒刷新，这样在速度和安全上都做到了兼顾
    
    数据恢复
    
    RDB方式
    
    RDB文件的载入工作是在服务器启动时自动执行的，没有专门用于载入RDB文件的命令，只要Redis服务器在启动时检测到RDB文件存在，它就会自动载入RDB文件，服务器在载入RDB文件期间，会一直处于阻塞状态，直到载入工作完成为止
    
    AOF方式
    
    服务器在启动时，通过载入和执行AOF文件中保存的命令来还原服务器关闭之前的数据库状态，具体过程：
    
    （1）载入AOF文件
    
    （2）创建模拟客户端
    
    （3）从AOF文件中读取一条命令
    
    （4）使用模拟客户端执行命令
    
    （5）循环读取并执行命令，直到全部完成
    
    如果同时启用了RDB和AOF方式，AOF优先，启动时只加载AOF文件恢复数据


 redis 持久化

### 2、Python 操作 Redis

python 安装 redis 模块：


    $ sudo pip install redis
    or
    $ sudo easy_install redis
    or
    $ sudo python setup.py install
    
    详见：https://github.com/WoLpH/redis-py
    https://pypi.python.org/pypi/redis
    https://redislabs.com/python-redis


### API 的使用

#### 1> 操作模式

redis-py 提供两个类 Redis 和 StrictRedis 用于实现 Redis 的操作命令，StrictRedis 用于实现大部分官方的命令，并使用官方的语法和命令，Redis 是 StrictRedis 的子类，用于向后兼容旧版本的 redis-py

    import redis
     
    r = redis.Redis(host='192.168.1.5', port=6379)
    r.set('foo', 'Bar')
    print r.get('foo')

#### 2> 连接池

redis-py 使用 connection pool 来管理对一个 redis server 的所有连接，避免每次建立、释放连接带来的额外开销。默认每个 Redis 实例都会维护着一个自己的连接池。也可以覆盖直接建立一个连接池，然后作为参数 Redis，这样就可以实现多个 Redis 实例共享一个连接池资源。实现客户端分片或有连接如何管理更细的颗粒控制。

    pool = redis.ConnectionPool(host='192.168.1.5', port=6379)
     
    r = redis.Redis(connection_pool=pool)
    r.set('foo', 'Bar')
    print r.get('foo')

#### 3> 操作

分为五种数据类型，见下图：

![][10]

**① String 操作**，String 在内存中格式是一个 name 对应一个 value 来存储

> set(name, value, ex=None, px=None, nx=False, xx=False)

 


    > #>  在Redis中设置值，默认，不存在则创建，存在则修改> 
    #>  参数：> 
    >      ex，过期时间（秒）
         px，过期时间（毫秒）
         nx，如果设置为True，则只有name不存在时，当前set操作才执行
         xx，如果设置为True，则只有name存在时，岗前set操作才执行


> setnx(name, value)

    > #>  设置值，只有name不存在时，执行设置操作（添加）

> setex(name, value, time)

    > #>  设置值> 
    #>  参数：> 
         time，过期时间（数字秒 或 timedelta对象）

> psetex(name, time_ms, value)

    > #>  设置值> 
    #>  参数：> 
         time_ms，过期时间（数字毫秒 或 timedelta对象）

> mset(*args, **kwargs)

    > #>  批量设置值> 
    #>  如：> 
        mset(k1=> '> v1> '> , k2=> '> v2> '> )
        或
        mget({> '> k1> '> : > '> v1> '> , > '> k2> '> : > '> v2> '> })

> get(name)

    > #>  获取值

> mget(keys, *args)

    > #>  批量获取> 
    #>  如：> 
        mget(> '> ylr> '> , > '> nick> '> )
        或
        r.mget([> '> ylr> '> , > '> nick> '> ])

> getset(name, value)

    > #>  设置新值并获取原来的值

> getrange(key, start, end)

 


    >  > #>  获取子序列（根据字节获取，非字符）> 
     > #>  参数：> 
    >      name，Redis 的 name
         start，起始位置（字节）
         end，结束位置（字节）
     > #>  如： "索宁" ，0-3表示 "索"


> setrange(name, offset, value)

    > # > 修改字符串内容，从指定字符串索引开始向后替换（新值太长时，则向后添加）> 
    #>  参数：> 
    >      offset，字符串的索引，字节（一个汉字三个字节）
         value，要设置的值

> setbit(name, offset, value)

 


    > #>  对name对应值的二进制表示的位进行操作> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  offset，位的索引（将值变换成二进制后再进行索引）> 
        > #>  value，值只能是 1 或 0> 
     
    > #>  注：如果在Redis中有一个对应： n1 = "foo"，> 
            那么字符串foo的二进制表示为：01100110 01101111 01101111> 
        所以，如果执行 setbit(> '> n1> '> , 7, 1> )，则就会将第7位设置为1，
            那么最终二进制则变成 > 01100111 01101111 01101111，即：> "> goo> "


> getbit(name, offset)

    > #>  获取name对应的值的二进制表示中的某位的值 （0或1）

> bitcount(key, start=None, end=None)

    >  > #>  获取name对应的值的二进制表示中 1 的个数> 
     > #>  参数：> 
    >      key，Redis的name
         start，位起始位置
         end，位结束位置

> bitop(operation, dest, *keys)

 


    > #>  获取多个值，并将值做位运算，将最后的结果保存至新的name对应的值> 
     
    > #>  参数：> 
    >      operation,AND（并） 、 OR（或） 、 NOT（非） 、 XOR（异或）
         dest, 新的Redis的name
         > *> keys,要查找的Redis的name
     
    > #>  如：> 
        bitop(> "> AND> "> , > '> new_name> '> , > '> n1> '> , > '> n2> '> , > '> n3> '> )
         获取Redis中n1,n2,n3对应的值，然后讲所有的值做位运算（求并集），然后将结果保存 new_name 对应的值中


> strlen(name)

    > #>  返回name对应值的字节长度（一个汉字3个字节）

> incr(self, name, amount=1)

 


    > #>  自增 name对应的值，当name不存在时，则创建name＝amount，否则，则自增。> 
     
    > #>  参数：> 
    >      name,Redis的name
         amount,自增数（必须是整数）
     
    > #>  注：同incrb


> incrbyfloat(self, name, amount=1.0)

    > #>  自增 name对应的值，当name不存在时，则创建name＝amount，否则，则自增。> 
     
    > #>  参数：> 
    >      name,Redis的name
         amount,自增数（浮点型）

> decr(self, name, amount=1)

    > #>  自减 name对应的值，当name不存在时，则创建name＝amount，否则，则自减。> 
     
    > #>  参数：> 
    >      name,Redis的name
         amount,自减数（整数）

> append(key, value)

    > #>  在redis name对应的值后面追加内容> 
     
    > #>  参数：> 
    >     key, redis的name
        value, 要追加的字符串

**② Hash 操作**，redis 中 Hash 在内存中的存储格式类似字典

> hset(name, key, value)

 


    > #>  name对应的hash中设置一个键值对（不存在，则创建；否则，修改）> 
     
    > #>  参数：> 
    >      name，redis的name
         key，name对应的hash中的key
         value，name对应的hash中的value
     
    > #>  注：> 
         hsetnx(name, key, value),当name对应的hash中不存在当前key时则创建（相当于添加）


> hmset(name, mapping)

 


    > #>  在name对应的hash中批量设置键值对> 
     
    > #>  参数：> 
    >      name，redis的name
         mapping，字典，如：{> '> k1> '> :> '> v1> '> , > '> k2> '> : > '> v2> '> }
     
    > #>  如：> 
        > #>  r.hmset('xx', {'k1':'v1', 'k2': 'v2'})


> hget(name,key)

    > #>  在name对应的hash中获取根据key获取value

> hmget(name, keys, *args)

 


    > #>  在name对应的hash中获取多个key的值> 
     
    > #>  参数：> 
    >      name，reids对应的name
         keys，要获取key集合，如：[> '> k1> '> , > '> k2> '> , > '> k3> '> ]
         > *> args，要获取的key，如：k1,k2,k3
     
    > #>  如：> 
         r.mget(> '> xx> '> , [> '> k1> '> , > '> k2> '> ])
         或
         > print>  r.hmget(> '> xx> '> , > '> k1> '> , > '> k2> '> )


> hgetall(name)

    > #>  获取name对应hash的所有键值

> hlen(name)

    > #>  获取name对应的hash中键值对的个数

> hkeys(name)

    > #>  获取name对应的hash中所有的key的值

> hvals(name)

    > #>  获取name对应的hash中所有的value的值

> hexists(name, key)

    > #>  检查name对应的hash是否存在当前传入的key

> hdel(name,*keys)

    > #>  将name对应的hash中指定key的键值对删除

> hincrby(name, key, amount=1)

    >  自增name对应的hash中的指定key的值，不存在则创建key=amount>  参数：> 
        >  name，redis中的name> 
         > key， hash对应的key> 
        >  amount，自增数（整数）

> hincrbyfloat(name, key, amount=1.0)

 


    > #>  自增name对应的hash中的指定key的值，不存在则创建key=amount> 
     
    > #>  参数：> 
        > #>  name，redis中的name> 
        > #>  key， hash对应的key> 
        > #>  amount，自增数（浮点数）> 
     
    > #>  自增name对应的hash中的指定key的值，不存在则创建key=amount


> hscan(name, cursor=0, match=None, count=None)

 


    > #>  增量式迭代获取，对于数据大的数据非常有用，hscan可以实现分片的获取数据，并非一次性将数据全部获取完，从而放置内存被撑爆> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  cursor，游标（基于游标分批取获取数据）> 
        > #>  match，匹配指定key，默认None 表示所有的key> 
        > #>  count，每次分片最少获取个数，默认None表示采用Redis的默认分片个数> 
     
    > #>  如：> 
        > #>  第一次：cursor1, data1 = r.hscan('xx', cursor=0, match=None, count=None)> 
        > #>  第二次：cursor2, data1 = r.hscan('xx', cursor=cursor1, match=None, count=None)> 
        > #>  ...> 
        > #>  直到返回值cursor的值为0时，表示数据已经通过分片获取完毕


> hscan_iter(name, match=None, count=None)

 


    > #>  利用yield封装hscan创建生成器，实现分批去redis中获取数据> 
     
    > #>  参数：> 
        > #>  match，匹配指定key，默认None 表示所有的key> 
        > #>  count，每次分片最少获取个数，默认None表示采用Redis的默认分片个数> 
     
    > #>  如：> 
        > #>  for item in r.hscan_iter('xx'):> 
        > #>      print item


**③ List操作**，redis 中的 List 在在内存中按照一个 name 对应一个 List 来存储，像变量对应一个列表。

> lpush(name,values)

 


    > #>  在name对应的list中添加元素，每个新的元素都添加到列表的最左边> 
     
    > #>  如：> 
        > #>  r.lpush('oo', 11,22,33)> 
        > #>  保存顺序为: 33,22,11> 
     
    > #>  扩展：> 
        > #>  rpush(name, values) 表示从右向左操作


> lpushx(name,value)

    > #>  在name对应的list中添加元素，只有name已经存在时，值添加到列表的最左边> 
     
    > #>  更多：> 
        > #>  rpushx(name, value) 表示从右向左操作

> llen(name)

    > #>  name对应的list元素的个数

> linsert(name, where, refvalue, value))

 


    > #>  在name对应的列表的某一个值前或后插入一个新值> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  where，BEFORE或AFTER> 
        > #>  refvalue，标杆值，即：在它前后插入数据> 
        > #>  value，要插入的数据


> r.lset(name, index, value)

 


    > #>  对name对应的list中的某一个索引位置重新赋值> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  index，list的索引位置> 
        > #>  value，要设置的值


> r.lrem(name, value, num)

 


    > #>  在name对应的list中删除指定的值> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  value，要删除的值> 
        > #>  num，  num=0，删除列表中所有的指定值；> 
               > #>  num=2,从前到后，删除2个；> 
               > #>  num=-2,从后向前，删除2个


> lpop(name)

    > #>  在name对应的列表的左侧获取第一个元素并在列表中移除，返回值则是第一个元素> 
     
    > #>  更多：> 
        > #>  rpop(name) 表示从右向左操作

> lindex(name, index)

    > #>  在name对应的列表中根据索引获取列表元素

> lrange(name, start, end)

    > #>  在name对应的列表分片获取数据> 
    #>  参数：> 
        > #>  name，redis的name> 
        > #>  start，索引的起始位置> 
        > #>  end，索引结束位置

> ltrim(name, start, end)

    > #>  在name对应的列表中移除没有在start-end索引之间的值> 
    #>  参数：> 
        > #>  name，redis的name> 
        > #>  start，索引的起始位置> 
        > #>  end，索引结束位置

> rpoplpush(src, dst)

    > #>  从一个列表取出最右边的元素，同时将其添加至另一个列表的最左边> 
    #>  参数：> 
        > #>  src，要取数据的列表的name> 
        > #>  dst，要添加数据的列表的name

> blpop(keys, timeout)

 


    > #>  将多个列表排列，按照从左到右去pop对应列表的元素> 
     
    > #>  参数：> 
        > #>  keys，redis的name的集合> 
        > #>  timeout，超时时间，当元素所有列表的元素获取完之后，阻塞等待列表内有数据的时间（秒）, 0 表示永远阻塞> 
     
    > #>  更多：> 
        > #>  r.brpop(keys, timeout)，从右向左获取数据


> brpoplpush(src, dst, timeout=0)

 


    > #>  从一个列表的右侧移除一个元素并将其添加到另一个列表的左侧> 
     
    > #>  参数：> 
        > #>  src，取出并要移除元素的列表对应的name> 
        > #>  dst，要插入元素的列表对应的name> 
        > #>  timeout，当src对应的列表中没有数据时，阻塞等待其有数据的超时时间（秒），0 表示永远阻塞


> 自定义增量迭代

 


    > #>  由于redis类库中没有提供对列表元素的增量迭代，如果想要循环name对应的列表的所有元素，那么就需要：> 
        > #>  1、获取name对应的所有列表> 
        > #>  2、循环列表> 
    #>  但是，如果列表非常大，那么就有可能在第一步时就将程序的内容撑爆，所有有必要自定义一个增量迭代的功能：> 
     
    > def>  list_iter(name):
        > """> 
        自定义redis列表增量迭代
        :param name: redis中的name，即：迭代name对应的列表
        :return: yield 返回 列表元素
        > """> 
        list_count > =>  r.llen(name)
        > for>  index > in>  xrange(list_count):
            > yield>  r.lindex(name, index)
     
    > #>  使用> 
    > for>  item > in>  list_iter(> '> pp> '> ):
        > print>  item


****④**Set 操作**，Set 集合就是不允许重复的列表

> sadd(name,values)

    > #>  name对应的集合中添加元素

> scard(name)

    > #>  获取name对应的集合中元素个数

> sdiff(keys, *args)

    > #>  在第一个name对应的集合中且不在其他name对应的集合的元素集合

> sdiffstore(dest, keys, *args)

    > #>  获取第一个name对应的集合中且不在其他name对应的集合，再将其新加入到dest对应的集合中

> sinter(keys, *args)

    > #>  获取多一个name对应集合的并集

> sinterstore(dest, keys, *args)

    > #>  获取多一个name对应集合的并集，再讲其加入到dest对应的集合中

> sismember(name, value)

    > #>  检查value是否是name对应的集合的成员

> smembers(name)

    > #>  获取name对应的集合的所有成员

> smove(src, dst, value)

    > #>  将某个成员从一个集合中移动到另外一个集合

> spop(name)

    > #>  从集合的右侧（尾部）移除一个成员，并将其返回

> srandmember(name, numbers)

    > #>  从name对应的集合中随机获取 numbers 个元素

> srem(name, values)

    > #>  在name对应的集合中删除某些值

> sunion(keys, *args)

    > #>  获取多一个name对应的集合的并集

> sunionstore(dest,keys, *args)

    > #>  获取多一个name对应的集合的并集，并将结果保存到dest对应的集合中

> sscan(name, cursor=0, match=None, count=None)  
> sscan_iter(name, match=None, count=None)

    > #>  同字符串的操作，用于增量迭代分批获取元素，避免内存消耗太大

**⑤ 有序集合**，在集合的基础上，为每个元素排序；元素的排序需要根据另外一个值来进行比较，所以对于有序集合，每一个元素有两个值：值和分数，分数是专门来做排序的。

> zadd(name, *args, **kwargs) 

    > #>  在name对应的有序集合中添加元素> 
    #>  如：> 
         > #>  zadd('zz', 'n1', 1, 'n2', 2)> 
         > #>  或> 
         > #>  zadd('zz', n1=11, n2=22)

> zcard(name)

    > #>  获取name对应的有序集合元素的数量

> zcount(name, min, max)

    > #>  获取name对应的有序集合中分数 在 [min,max] 之间的个数

> zincrby(name, value, amount)

    > #>  自增name对应的有序集合的 name 对应的分数

> r.zrange( name, start, end, desc=False, withscores=False, score_cast_func=float)

 


    > #>  按照索引范围获取name对应的有序集合的元素> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  start，有序集合索引起始位置（非分数）> 
        > #>  end，有序集合索引结束位置（非分数）> 
        > #>  desc，排序规则，默认按照分数从小到大排序> 
        > #>  withscores，是否获取元素的分数，默认只获取元素的值> 
        > #>  score_cast_func，对分数进行数据转换的函数> 
     
    > #>  更多：> 
        > #>  从大到小排序> 
        > #>  zrevrange(name, start, end, withscores=False, score_cast_func=float)> 
     
        > #>  按照分数范围获取name对应的有序集合的元素> 
        > #>  zrangebyscore(name, min, max, start=None, num=None, withscores=False, score_cast_func=float)> 
        > #>  从大到小排序> 
        > #>  zrevrangebyscore(name, max, min, start=None, num=None, withscores=False, score_cast_func=float)


> zrank(name, value)

    > #>  获取某个值在 name对应的有序集合中的排行（从 0 开始）> 
     
    > #>  更多：> 
        > #>  zrevrank(name, value)，从大到小排序

> zrangebylex(name, min, max, start=None, num=None)

 


    > #>  当有序集合的所有成员都具有相同的分值时，有序集合的元素会根据成员的 值 （lexicographical ordering）来进行排序，而这个命令则可以返回给定的有序集合键 key 中， 元素的值介于 min 和 max 之间的成员> 
    #>  对集合中的每个成员进行逐个字节的对比（byte-by-byte compare）， 并按照从低到高的顺序， 返回排序后的集合成员。 如果两个字符串有一部分内容是相同的话， 那么命令会认为较长的字符串比较短的字符串要大> 
     
    > #>  参数：> 
        > #>  name，redis的name> 
        > #>  min，左区间（值）。 + 表示正无限； - 表示负无限； ( 表示开区间； [ 则表示闭区间> 
        > #>  min，右区间（值）> 
        > #>  start，对结果进行分片处理，索引位置> 
        > #>  num，对结果进行分片处理，索引后面的num个元素> 
     
    > #>  如：> 
        > #>  ZADD myzset 0 aa 0 ba 0 ca 0 da 0 ea 0 fa 0 ga> 
        > #>  r.zrangebylex('myzset', "-", "[ca") 结果为：['aa', 'ba', 'ca']> 
     
    > #>  更多：> 
        > #>  从大到小排序> 
        > #>  zrevrangebylex(name, max, min, start=None, num=None)


> zrem(name, values)

    > #>  删除name对应的有序集合中值是values的成员> 
     
    > #>  如：zrem('zz', ['s1', 's2'])

> zremrangebyrank(name, min, max)

    > #>  根据排行范围删除

> zremrangebyscore(name, min, max)

    > #>  根据分数范围删除

> zremrangebylex(name, min, max)

    > #>  根据值返回删除

> zscore(name, value)

    > #>  获取name对应有序集合中 value 对应的分数

> zinterstore(dest, keys, aggregate=None)

    > #>  获取两个有序集合的交集，如果遇到相同值不同分数，则按照aggregate进行操作> 
    #>  aggregate的值为:  SUM  MIN  MAX

> zunionstore(dest, keys, aggregate=None)

    > #>  获取两个有序集合的并集，如果遇到相同值不同分数，则按照aggregate进行操作> 
    #>  aggregate的值为:  SUM  MIN  MAX

> zscan(name, cursor=0, match=None, count=None, score_cast_func=float)  
> zscan_iter(name, match=None, count=None,score_cast_func=float)

    > #>  同字符串相似，相较于字符串新增score_cast_func，用来对分数进行操作

**⑥ 其它**常用操作

> delete(*names) 

    > #>  根据删除redis中的任意数据类型

> exists(name)

    > #>  检测redis的name是否存在

> keys(pattern='*')

 


    > #>  根据模型获取redis的name> 
     
    > #>  更多：> 
        > #>  KEYS * 匹配数据库中所有 key 。> 
        > #>  KEYS h?llo 匹配 hello ， hallo 和 hxllo 等。> 
        > #>  KEYS h*llo 匹配 hllo 和 heeeeello 等。> 
        > #>  KEYS h[ae]llo 匹配 hello 和 hallo ，但不匹配 hillo


> expire(name ,time)

    > #>  为某个redis的某个name设置超时时间

> rename(src, dst)

    > #>  对redis的name重命名为

> move(name, db))

    > #>  将redis的某个值移动到指定的db下

> randomkey()

    > #>  随机获取一个redis的name（不删除）

> type(name)

    > #>  获取name对应值的类型

> scan(cursor=0, match=None, count=None)  
> scan_iter(match=None, count=None)

    > #>  同字符串操作，用于增量迭代获取key

#### 4> 管道

默认情况下，redis-py 每次在执行请求时都会创建和断开一次连接操作（连接池申请连接，归还连接池），如果想要在一次请求中执行多个命令，则可以使用 pipline 实现一次请求执行多个命令，并且默认情况下 pipline 是原子性操作。

见以下实例：

 


    import redis
     
    pool = redis.ConnectionPool(host='10.211.55.4', port=6379)
     
    r = redis.Redis(connection_pool=pool)
     
    # pipe = r.pipeline(transaction=False)
    pipe = r.pipeline(transaction=True)
     
    r.set('name', 'nick')
    r.set('age', '18')
     
    pipe.execute()


#### 5> 发布和订阅

发布者：服务器

订阅者：Dashboad 和数据处理

发布订阅的 Demo 如下：

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
    
    import redis
    
    
    class RedisHelper:
    
        def __init__(self):
            self.__conn = redis.Redis(host='10.211.55.4')
            self.chan_sub = 'fm104.5'
            self.chan_pub = 'fm104.5'
    
        def public(self, msg):
            self.__conn.publish(self.chan_pub, msg)
            return True
    
        def subscribe(self):
            pub = self.__conn.pubsub()
            pub.subscribe(self.chan_sub)
            pub.parse_response()
            return pub


 RedisHelper

订阅者：

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
     
    from monitor.RedisHelper import RedisHelper
     
    obj = RedisHelper()
    redis_sub = obj.subscribe()
     
    while True:
        msg= redis_sub.parse_response()
        print msg


发布者：

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
     
    from monitor.RedisHelper import RedisHelper
     
    obj = RedisHelper()
    obj.public('hello')


更多参见：https://github.com/andymccurdy/redis-py/

http://doc.redisfans.com/

## 三、RabbitMQ

### 1、简介、安装、使用

RabbitMQ 是一个在 AMQP 基础上完成的，可复用的企业消息系统。他遵循 Mozilla Public License 开源协议。

MQ 全称为 Message Queue, [消息队列][11]（MQ）是一种应用程序对应用程序的通信方式。应用程序通过读写出入队列的消息（针对应用程序的数据）来通信，而无需专用连接来链接它们。消息传递指的是程序之间通过在消息中发送数据进行通信，而不是通过直接调用彼此来通信，直接调用通常是用于诸如[远程过程调用][12]的技术。排队指的是应用程序通过 队列来通信。队列的使用除去了接收和发送应用程序同时执行的要求。

流程上生产者把消息放到队列中去, 然后消费者从队列中取出消息。

* Producing , 生产者, 产生消息的角色.
* Exchange , 交换器, 在得到生产者产生的消息后, 把消息放入队列的角色.
* Queue , 队列, 消息暂时保存的地方.
* Consuming , 消费者, 把消息从队列中取出的角色.
* 消息 Message

RabbitMQ安装

 


    # 安装配置epel源
       $ rpm -ivh http://dl.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
     
    # 安装erlang
       $ yum -y install erlang
     
    # 安装RabbitMQ
       $ yum -y install rabbitmq-server


    # 启动
    service rabbitmq-server start/stop
    
    # 默认监听端口5672 (带上 SSL 默认 5671)

python 安装 API

 


    pip install pika
    or
    easy_install pika
    or
    源码
     
    https://pypi.python.org/pypi/pika


### 2、使用API操作RabbitMQ

基于队列 Queue 实现生产者消费者模型：

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
    import Queue
    import threading
    
    
    message = Queue.Queue(10)
    
    
    def producer(i):
        while True:
            message.put(i)
    
    
    def consumer(i):
        while True:
            msg = message.get()
    
    
    for i in range(12):
        t = threading.Thread(target=producer, args=(i,))
        t.start()
    
    for i in range(10):
        t = threading.Thread(target=consumer, args=(i,))
        t.start()


 View Code

RabbitMQ 实现：

 


    #!/usr/bin/env python
    import pika
     
    # ######################### 生产者 #########################
     
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
     
    channel.queue_declare(queue='hello')
     
    channel.basic_publish(exchange='',
                          routing_key='hello',
                          body='Hello World!')
    print(" [x] Sent 'Hello World!'")
    connection.close()
    
    
    
    #!/usr/bin/env python
    import pika
     
    # ########################## 消费者 ##########################
     
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
     
    channel.queue_declare(queue='hello')
     
    def callback(ch, method, properties, body):
        print(" [x] Received %r" % body)
     
    channel.basic_consume(callback,
                          queue='hello',
                          no_ack=True)
     
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()


#### 1、acknowledgment 消息不丢失

no-ack ＝ False，如果消费者由于某些情况宕了(its channel is closed, connection is closed, or TCP connection is lost)，那 RabbitMQ 会重新将该任务放入队列中。

在实际应用中，可能会发生消费者收到Queue中的消息，但没有处理完成就宕机（或出现其他意外）的情况，这种情况下就可能会导致消息丢失。为了避免这种情况发生，我们可以要求消费者在消费完消息后发送一个回执给RabbitMQ，RabbitMQ收到消息回执（Message acknowledgment）后才将该消息从Queue中移除；如果RabbitMQ没有收到回执并检测到消费者的RabbitMQ连接断开，则RabbitMQ会将该消息发送给其他消费者（如果存在多个消费者）进行处理。这里不存在timeout概念，一个消费者处理消息时间再长也不会导致该消息被发送给其他消费者，除非它的RabbitMQ连接断开。  
这里会产生另外一个问题，如果我们的开发人员在处理完业务逻辑后，忘记发送回执给RabbitMQ，这将会导致严重的bug——Queue中堆积的消息会越来越多；消费者重启后会重复消费这些消息并重复执行业务逻辑…

 


    import pika
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='10.211.55.4'))
    channel = connection.channel()
    
    channel.queue_declare(queue='hello')
    
    def callback(ch, method, properties, body):
        print(" [x] Received %r" % body)
        import time
        time.sleep(10)
        print 'ok'
        ch.basic_ack(delivery_tag = method.delivery_tag)
    
    channel.basic_consume(callback,
                          queue='hello',
                          no_ack=False)
    
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()


 消费者

#### 2、 durable 消息不丢失 

如果我们希望即使在RabbitMQ服务重启的情况下，也不会丢失消息，我们可以将Queue与Message都设置为可持久化的（durable），这样可以保证绝大部分情况下我们的RabbitMQ消息不会丢失。但依然解决不了小概率丢失事件的发生（比如RabbitMQ服务器已经接收到生产者的消息，但还没来得及持久化该消息时RabbitMQ服务器就断电了），如果我们需要对这种小概率事件也要管理起来，那么我们要用到事务。由于这里仅为RabbitMQ的简单介绍，所以这里将不讲解RabbitMQ相关的事务。

需要改两处地方

 


    #!/usr/bin/env python
    import pika
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(host='10.211.55.4'))
    channel = connection.channel()
    
    # make message persistent
    channel.queue_declare(queue='hello', durable=True)
    
    channel.basic_publish(exchange='',
                          routing_key='hello',
                          body='Hello World!',
                          properties=pika.BasicProperties(
                              delivery_mode=2, # make message persistent
                          ))
    print(" [x] Sent 'Hello World!'")
    connection.close()


 生产者

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
    import pika
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(host='10.211.55.4'))
    channel = connection.channel()
    
    # make message persistent
    channel.queue_declare(queue='hello', durable=True)
    
    
    def callback(ch, method, properties, body):
        print(" [x] Received %r" % body)
        import time
        time.sleep(10)
        print 'ok'
        ch.basic_ack(delivery_tag = method.delivery_tag)
    
    channel.basic_consume(callback,
                          queue='hello',
                          no_ack=False)
    
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()


 消费者

#### 3、消息获取顺序 

默认情况下，消费者拿消息队列里的数据是按平均分配，例如：消费者1 拿队列中 奇数 序列的任务，消费者2 拿队列中 偶数 序列的任务。

channel.basic_qos(prefetch_count=1) 表示谁来谁取，不再按照奇偶数排列，这个性能较高的机器拿的任务就多

 


    #!/usr/bin/env python
    # -*- coding:utf-8 -*-
    import pika
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(host='10.211.55.4'))
    channel = connection.channel()
    
    # make message persistent
    channel.queue_declare(queue='hello')
    
    
    def callback(ch, method, properties, body):
        print(" [x] Received %r" % body)
        import time
        time.sleep(10)
        print 'ok'
        ch.basic_ack(delivery_tag = method.delivery_tag)
    
    channel.basic_qos(prefetch_count=1)
    
    channel.basic_consume(callback,
                          queue='hello',
                          no_ack=False)
    
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()


 消费者

#### 4、发布订阅

![][13]

发布订阅和简单的消息队列区别在于，发布订阅者会将消息发送给所有的订阅者，而消息队列中的数据被消费一次便消失。所以，RabbitMQ 实现发布订阅时，会为每一个订阅者创建一个队列，而发布者发布消息的时候，会将消息放置在所有相关的队列中。

exchange type = fanout

 


    #!/usr/bin/env python
    import pika
    import sys
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='logs',
                             type='fanout')
    
    message = ' '.join(sys.argv[1:]) or "info: Hello World!"
    channel.basic_publish(exchange='logs',
                          routing_key='',
                          body=message)
    print(" [x] Sent %r" % message)
    connection.close()


 发布者

 


    #!/usr/bin/env python
    import pika
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='logs',
                             type='fanout')
    
    result = channel.queue_declare(exclusive=True)
    queue_name = result.method.queue
    
    channel.queue_bind(exchange='logs',
                       queue=queue_name)
    
    print(' [*] Waiting for logs. To exit press CTRL+C')
    
    def callback(ch, method, properties, body):
        print(" [x] %r" % body)
    
    channel.basic_consume(callback,
                          queue=queue_name,
                          no_ack=True)
    
    channel.start_consuming()


 订阅者

#### 5、关键字发送

![][14]

第4步实例中，发送消息必须明确指定某个队列并向其中发送消息，当然，RabbitMQ 还支持根据关键字发送（队列绑定关键字），发送者将消息发送到 exchange，exchange 根据关键字 判定应该将数据发送至指定队列。

exchange type = direct

 


    #!/usr/bin/env python
    import pika
    import sys
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='direct_logs',
                             type='direct')
    
    result = channel.queue_declare(exclusive=True)
    queue_name = result.method.queue
    
    severities = sys.argv[1:]
    if not severities:
        sys.stderr.write("Usage: %s [info] [warning] [error]\n" % sys.argv[0])
        sys.exit(1)
    
    for severity in severities:
        channel.queue_bind(exchange='direct_logs',
                           queue=queue_name,
                           routing_key=severity)
    
    print(' [*] Waiting for logs. To exit press CTRL+C')
    
    def callback(ch, method, properties, body):
        print(" [x] %r:%r" % (method.routing_key, body))
    
    channel.basic_consume(callback,
                          queue=queue_name,
                          no_ack=True)
    
    channel.start_consuming()


 消费者

 


    #!/usr/bin/env python
    import pika
    import sys
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='direct_logs',
                             type='direct')
    
    severity = sys.argv[1] if len(sys.argv) > 1 else 'info'
    message = ' '.join(sys.argv[2:]) or 'Hello World!'
    channel.basic_publish(exchange='direct_logs',
                          routing_key=severity,
                          body=message)
    print(" [x] Sent %r:%r" % (severity, message))
    connection.close()


 生产者

#### 6、模糊匹配

![][15]

exchange type = topic

在 topic 类型下，可以让队列绑定几个模糊的关键字，之后发送者将数据发送到 exchange，exchange 将传入”路由值“和 ”关键字“进行匹配，匹配成功，则将数据发送到指定队列。

匹配基本规则及示例：

* # 表示可以匹配 0 个 或 多个 单词
* * 表示只能匹配 一个 单词

```
    发送者路由值              队列中
    www.suoning.python      www.*  -- 不匹配
    www.suoning.python      www.# -- 匹配

 


    #!/usr/bin/env python
    import pika
    import sys
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='topic_logs',
                             type='topic')
    
    result = channel.queue_declare(exclusive=True)
    queue_name = result.method.queue
    
    binding_keys = sys.argv[1:]
    if not binding_keys:
        sys.stderr.write("Usage: %s [binding_key]...\n" % sys.argv[0])
        sys.exit(1)
    
    for binding_key in binding_keys:
        channel.queue_bind(exchange='topic_logs',
                           queue=queue_name,
                           routing_key=binding_key)
    
    print(' [*] Waiting for logs. To exit press CTRL+C')
    
    def callback(ch, method, properties, body):
        print(" [x] %r:%r" % (method.routing_key, body))
    
    channel.basic_consume(callback,
                          queue=queue_name,
                          no_ack=True)
    
    channel.start_consuming()
```

 消费者

 

```
    #!/usr/bin/env python
    import pika
    import sys
    
    connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='localhost'))
    channel = connection.channel()
    
    channel.exchange_declare(exchange='topic_logs',
                             type='topic')
    
    routing_key = sys.argv[1] if len(sys.argv) > 1 else 'anonymous.info'
    message = ' '.join(sys.argv[2:]) or 'Hello World!'
    channel.basic_publish(exchange='topic_logs',
                          routing_key=routing_key,
                          body=message)
    print(" [x] Sent %r:%r" % (routing_key, message))
    connection.close()
```
[0]: http://www.cnblogs.com/suoning/p/5807247.html
[1]: http://baike.baidu.com/view/1487140.htm
[2]: http://baike.baidu.com/view/53123.htm
[3]: http://baike.baidu.com/subview/10075/6770152.htm
[4]: http://images.cnblogs.com/OutliningIndicators/ContractedBlock.gif
[5]: http://images.cnblogs.com/OutliningIndicators/ExpandedBlockStart.gif
[6]: http://baike.baidu.com/view/549479.htm
[7]: http://baike.baidu.com/view/675645.htm
[8]: http://www.redis.io/documentation
[9]: http://www.redis.cn/
[10]: ./img/932699/201608/932699-20160829150650793-248211392.jpg
[11]: http://baike.baidu.com/view/262473.htm
[12]: http://baike.baidu.com/view/431455.htm
[13]: ./img/425762/201607/425762-20160717140730998-2143093474.png
[14]: ./img/425762/201607/425762-20160717140748795-1181706200.png
[15]: ./img/425762/201607/425762-20160717140807232-1395723247.png
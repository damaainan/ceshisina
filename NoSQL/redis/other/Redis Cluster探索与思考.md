# Redis Cluster探索与思考

 时间 2017-05-31 09:49:19 

原文[http://geek.csdn.net/news/detail/200023][1]


作者：张冬洪，微博研发中心高级DBA，Redis中国用户组主席，多年Linux和数据库运维经验，专注于MySQL和NoSQL架构设计与运维以及自动化平台的开发；目前在微博主要负责Feed核心系统相关业务的数据库运维和业务保障工作。 

责编：仲培艺，关注数据库领域，寻求报道或者投稿请发邮件zhongpy@csdn.net。 

本文为《程序员》原创文章，未经允许不得转载，更多精彩文章请 [订阅2017年《程序员》][3]

## Redis Cluster的基本原理和架构

Redis Cluster是分布式Redis的实现。随着Redis版本的更替，以及各种已知bug的fixed，在稳定性和高可用性上有了很大的提升和进步，越来越多的企业将Redis Cluster实际应用到线上业务中，通过从社区获取到反馈社区的迭代，为Redis Cluster成为一个可靠的企业级开源产品，在简化业务架构和业务逻辑方面都起着积极重要的作用。下面从Redis Cluster的基本原理为起点开启Redis Cluster在业界的分析与思考之旅。

### 基本原理

Redis Cluster的基本原理可以从数据分片、数据迁移、集群通讯、故障检测以及故障转移等方面进行了解，Cluster相关的代码也不是很多，注释也很详细，可自行查看，地址是： [https://github.com/antirez/redis/blob/unstable/src/cluster.c][4] 。这里由于篇幅的原因，主要从数据分片和数据迁移两方面进行详细介绍： 

#### 数据分片

Redis Cluster在设计中没有使用一致性哈希（Consistency Hashing），而是使用数据分片（Sharding）引入哈希槽（hash slot）来实现；一个 Redis Cluster包含16384（0~16383）个哈希槽，存储在Redis Cluster中的所有键都会被映射到这些slot中，集群中的每个键都属于这16384个哈希槽中的一个，集群使用公式slot=CRC16（key）/16384来计算key属于哪个槽，其中CRC16(key)语句用于计算key的CRC16 校验和。

集群中的每个主节点（Master）都负责处理16384个哈希槽中的一部分，当集群处于稳定状态时，每个哈希槽都只由一个主节点进行处理，每个主节点可以有一个到N个从节点（Slave），当主节点出现宕机或网络断线等不可用时，从节点能自动提升为主节点进行处理。

如图1，ClusterNode数据结构中的slots和numslots属性记录了节点负责处理哪些槽。其中，slot属性是一个二进制位数组（bitarray），其长度为16384/8=2048 Byte，共包含16384个二进制位。集群中的Master节点用bit（0和1）来标识对于某个槽是否拥有。比如，对于编号为1的槽，Master只要判断序列第二位（索引从0开始）的值是不是1即可，时间复杂度为O(1)。

![][5]

图1 ClusterNode数据结构 ![][6]

集群中所有槽的分配信息都保存在ClusterState数据结构的slots数组中，程序要检查槽i是否已经被分配或者找出处理槽i的节点，只需要访问clusterState.slots[i]的值即可，复杂度也为O(1)。ClusterState数据结构如图2所示。

![][7]

图2 ClusterState数据结构 查找关系如图3所示。

![][8]

图3 查找关系图 

#### 数据迁移

数据迁移可以理解为slot和key的迁移，这个功能很重要，极大地方便了集群做线性扩展，以及实现平滑的扩容或缩容。那么它是一个怎样的实现过程？下面举个例子：现在要将Master A节点中编号为1、2、3的slot迁移到Master B节点中，在slot迁移的中间状态下，slot 1、2、3在Master A节点的状态表现为MIGRATING,在Master B节点的状态表现为IMPORTING。

#### MIGRATING状态

这个状态如图4所示是被迁移slot在当前所在Master A节点中出现的一种状态，预备迁移slot从Mater A到Master B的时候，被迁移slot的状态首先变为MIGRATING状态，当客户端请求的某个key所属的slot的状态处于MIGRATING状态时，会出现以下几种情况：

![][9]

图4 slot迁移的中间状态 

* 如果key存在则成功处理。
* 如果key不存在，则返回客户端ASK，客户端根据ASK首先发送ASKING命令到目标节点，然后发送请求的命令到目标节点。
* 当key包含多个命令时：   
    * 如果都存在则成功处理
    * 如果都不存在，则返回客户端ASK
    * 如果一部分存在，则返回客户端TRYAGAIN，通知客户端稍后重试，这样当所有的key都迁移完毕，客户端重试请求时会得到ASK，然后经过一次重定向就可以获取这批键
* 此时并不刷新客户端中node的映射关系

#### IMPORTING状态

这个状态如图2所示是被迁移slot在目标Master B节点中出现的一种状态，预备迁移slot从Mater A到Master B的时候，被迁移slot的状态首先变为IMPORTING状态。在这种状态下的slot对客户端的请求可能会有下面几种影响：

* 如果key不存在则新建。
* 如果key不在该节点上，命令会被MOVED重定向，刷新客户端中node的映射关系。
* 如果是ASKING命令则命令会被执行，从而key没在被迁移的节点，已经被迁移到目标节点的情况命令可以被顺利执行。

#### 键空间迁移

这是完成数据迁移的重要一步，键空间迁移是指当满足了slot迁移前提的情况下，通过相关命令将slot 1、2、3中的键空间从Master A节点转移到Master B节点，这个过程由MIGRATE命令经过3步真正完成数据转移。步骤示意如图5。

![][10]

图5 表空间迁移步骤 经过上面三步可以完成键空间数据迁移，然后再将处于MIGRATING和IMPORTING状态的槽变为常态即可，从而完成整个重新分片的过程。

### 架构

实现细节：

* Redis Cluster中节点负责存储数据，记录集群状态，集群节点能自动发现其他节点，检测出节点的状态，并在需要时剔除故障节点，提升新的主节点。
* Redis Cluster中所有节点通过PING-PONG机制彼此互联，使用一个二级制协议(Cluster Bus) 进行通信，优化传输速度和带宽。发现新的节点、发送PING包、特定情况下发送集群消息，集群连接能够发布与订阅消息。
* 客户端和集群中的节点直连，不需要中间的Proxy层。理论上而言，客户端可以自由地向集群中的所有节点发送请求，但是每次不需要连接集群中的所有节点，只需要连接集群中任何一个可用节点即可。当客户端发起请求后，接收到重定向（MOVED\ASK）错误，会自动重定向到其他节点，所以客户端无需保存集群状态。不过客户端可以缓存键值和节点之间的映射关系，这样能明显提高命令执行的效率。
* Redis Cluster中节点之间使用异步复制，在分区过程中存在窗口，容易导致丢失写入的数据，集群即使努力尝试所有写入，但是以下两种情况可能丢失数据：
    * 命令操作已经到达主节点，但在主节点回复的时候，写入可能还没有通过主节点复制到从节点那里。如果这时主节点宕机了，这条命令将永久丢失。以防主节点长时间不可达而它的一个从节点已经被提升为主节点。
    * 分区导致一个主节点不可达，然而集群发送故障转移(failover)，提升从节点为主节点，原来的主节点再次恢复。一个没有更新路由表（routing table）的客户端或许会在集群把这个主节点变成一个从节点（新主节点的从节点）之前对它进行写入操作，导致数据彻底丢失。
* Redis集群的节点不可用后，在经过集群半数以上Master节点与故障节点通信超过cluster-node-timeout时间后，认为该节点故障，从而集群根据自动故障机制，将从节点提升为主节点。这时集群恢复可用。

## Redis Cluster的优势和不足

### 优势

1. 无中心架构。
1. 数据按照slot存储分布在多个节点，节点间数据共享，可动态调整数据分布。
1. 可扩展性，可线性扩展到1000个节点，节点可动态添加或删除。
1. 高可用性，部分节点不可用时，集群仍可用。通过增加Slave做standby数据副本，能够实现故障自动failover，节点之间通过gossip协议交换状态信息，用投票机制完成Slave到Master的角色提升。
1. 降低运维成本，提高系统的扩展性和可用性。

### 不足

1. Client实现复杂，驱动要求实现Smart Client，缓存slots mapping信息并及时更新，提高了开发难度，客户端的不成熟影响业务的稳定性。目前仅JedisCluster相对成熟，异常处理部分还不完善，比如常见的“max redirect exception”。
1. 节点会因为某些原因发生阻塞（阻塞时间大于clutser-node-timeout），被判断下线，这种failover是没有必要的。
1. 数据通过异步复制,不保证数据的强一致性。
1. 多个业务使用同一套集群时，无法根据统计区分冷热数据，资源隔离性较差，容易出现相互影响的情况。
1. Slave在集群中充当“冷备”，不能缓解读压力，当然可以通过SDK的合理设计来提高Slave资源的利用率。

## Redis Cluster在业界有哪些探索

通过调研了解，目前业界使用Redis Cluster大致可以总结为4类：

### 直连型

直连型，又可以称之为经典型或者传统型，是官方的默认使用方式，架构图见图6。这种使用方式的优缺点在上面的介绍中已经有所说明，这里不再过多重复赘述。但值得一提的是，这种方式使用Redis Cluster需要依赖Smart Client，诸如连接维护、缓存路由表、MultiOp和Pipeline的支持都需要在Client上实现，而且很多语言的Client目前都还是没有的（关于Clients的更多介绍请参考 [https://redis.io/clients][11] ）。虽然Client能够进行定制化，但有一定的开发难度，客户端的不成熟将直接影响到线上业务的稳定性。 

![][12]

图6 Redis Cluster架构 

### 带Proxy型

在Redis Cluster还没有那么稳定的时候，很多公司都已经开始探索分布式Redis的实现了，比如有基于Twemproxy或者Codis的实现，下面举一个唯品会基于Twemproxy架构的例子（不少公司分布式Redis的集群架构都经历过这个阶段），如图7所示。

![][13]

图7 Redis基于Twemproxy的架构实现 这种架构的优点和缺点也比较明显。

#### 优点：

1. 后端Sharding逻辑对业务透明，业务方的读写方式和操作单个Redis一致；
1. 可以作为Cache和Storage的Proxy，Proxy的逻辑和Redis资源层的逻辑是隔离的；
1. Proxy层可以用来兼容那些目前还不支持的Clients。

#### 缺点：

1. 结构复杂，运维成本高；
1. 可扩展性差，进行扩缩容都需要手动干预；
1. failover逻辑需要自己实现，其本身不能支持故障的自动转移；
1. Proxy层多了一次转发，性能有所损耗。

正是因此，我们知道Redis Cluster和基于Twemproxy结构使用中各自的优缺点，于是就出现了下面的这种架构，糅合了二者的优点，尽量规避二者的缺点，架构如图8。

![][14]

图8 Smart Proxy方案架构 目前业界Smart Proxy的方案了解到的有基于Nginx Proxy和自研的，自研的如饿了么开源部分功能的Corvus，优酷土豆是则通过Nginx来实现，滴滴也在展开基于这种方式的探索。选用Nginx Proxy主要是考虑到Nginx的高性能，包括异步非阻塞处理方式、高效的内存管理、和Redis一样都是基于epoll事件驱动模式等优点。优酷土豆的Redis服务化就是采用这种结构。

#### 优点：

1. 提供一套HTTP Restful接口，隔离底层资源，对客户端完全透明，跨语言调用变得简单；
1. 升级维护较为容易，维护Redis Cluster，只需平滑升级Proxy；
1. 层次化存储，底层存储做冷热异构存储；
1. 权限控制，Proxy可以通过密钥管理白名单，把一些不合法的请求都过滤掉，并且也可以对用户请求的超大value进行控制和过滤；
1. 安全性，可以屏蔽掉一些危险命令，比如keys *、save、flushall等，当然这些也可以在Redis上进行设置；
1. 资源逻辑隔离，根据不同用户的key加上前缀，来实现动态路由和资源隔离；
1. 监控埋点，对于不同的接口进行埋点监控。

#### 缺点：

1. Proxy层做了一次转发，性能有所损耗；
1. 增加了运维成本和管理成本，需要对架构和Nginx Proxy的实现细节足够了解，因为Nginx Proxy在批量接口调用高并发下可能会瞬间向Redis Cluster发起几百甚至上千的协程去访问，导致Redis的连接数或系统负载的不稳定，进而影响集群整体的稳定性。

### 云服务型

这种类型典型的案例就是企业级的PaaS产品，如亚马逊和阿里云提供的Redis Cluster服务，用户无需知道内部的实现细节，只管使用即可，降低了运维和开发成本。当然也有开源的产品，国内如搜狐的CacheCloud，它提供一个Redis云管理平台，实现多种类型（Redis Standalone、Redis Sentinel、Redis Cluster）自动部署，解决Redis实例碎片化现象，提供完善统计、监控、运维功能，减少开发人员的运维成本和误操作，提高机器的利用率，提供灵活的伸缩性，提供方便的接入客户端，更多细节请参考： [https://cachecloud.github.io][15] 。尽管这还不错，如果是一个新业务，到可以尝试一下，但若对于一个稳定的业务而言，要迁移到CacheCloud上则需要谨慎。如果对分布式框架感兴趣的可以看下Twitter开源的一个实现Memcached和Redis的分布式缓存框架Pelikan，目前国内并没有看到这样的应用案例，它的官网是 [http://twitter.github.io/pelikan/][16] 。 

![][17]

图9 CacheCloud平台架构 

### 自研型

这种类型在众多类型中更显得孤独，因为这种类型的方案更多是现象级，仅仅存在于为数不多的具有自研能力的公司中，或者说这种方案都是各公司根据自己的业务模型来进行定制化的。这类产品的一个共同特点是没有使用Redis Cluster的全部功能，只是借鉴了Redis Cluster的某些核心功能，比如说failover和slot的迁移。作为国内使用Redis较早的公司之一，新浪微博就基于内部定制化的Redis版本研发出了微博Redis服务化系统Tribe。它支持动态路由、读写分离（从节点能够处理读请求）、负载均衡、配置更新、数据聚集（相同前缀的数据落到同一个slot中）、动态扩缩容，以及数据落地存储。同类型的还有百度的BDRP系统。

![][18]

图10 Tribe系统架构图 

## Redis Cluster运维开发最佳实践经验

* 根据公司的业务模型选择合适的架构，适合自己的才是最好的；
* 做好容错机制，当连接或者请求异常时进行连接retry或reconnect；
* 重试时间可设置大于cluster-node-time (默认15s)，增强容错性，减少不必要的failover；
* 避免产生hot-key，导致节点成为系统的短板；
* 避免产生big-key，导致网卡打爆和慢查询；
* 设置合理的TTL，释放内存。避免大量key在同一时间段过期，虽然Redis已经做了很多优化，仍然会导致请求变慢；
* 避免使用阻塞操作（如save、flushall、flushdb、keys *等），不建议使用事务；
* Redis Cluster不建议使用pipeline和multi-keys操作（如mset/mget. multi-key操作），减少max redirect的产生；
* 当数据量很大时，由于复制积压缓冲区大小的限制，主从节点做一次全量复制导致网络流量暴增，建议单实例容量不要分配过大或者借鉴微博的优化采用增量复制的方式来规避；
* 数据持久化建议在业务低峰期操作，关闭aofrewrite机制，aof的写入操作放到bio线程中完成，解决磁盘压力较大时Redis阻塞的问题。设置系统参数vm.overcommit_memory=1，也可以避免bgsave/aofrewrite的失败；
* client buffer参数调整   
 `client-output-buffer-limit normal 256mb 128mb 60`  
 `client-output-buffer-limit slave 512mb 256mb 180`   
* 对于版本升级的问题，修改源码，将Redis的核心处理逻辑封装到动态库，内存中的数据保存在全局变量里，通过外部程序来调用动态库里的相应函数来读写数据。版本升级时只需要替换成新的动态库文件即可，无须重新载入数据，可毫秒级完成；
* 对于实现异地多活或实现数据中心级灾备的要求（即实现集群间数据的实时同步），可以参考搜狐的实现：Redis Cluster => Redis-Port => Smart proxy => Redis Cluster；
* 从Redis 4.2的Roadmap来看，更值得期待（详情： [https://gist.github.com/antirez/a3787d538eec3db381a41654e214b31d][19] ）： 
    * 加速key->hashslot的分配
    * 更好更多的数据中心存储
    * redis-trib的C代码将移植到redis-cli，瘦身包体积
    * 集群的备份/恢复
    * 非阻塞的Migrate
    * 更快的resharding
    * 隐藏一个只Cache模式，当没有Slave时，Masters当在有一个失败后能够自动重新分配slot
    * Cluster API和Redis Modules的改进，并且Disque分布式消息队列将作为Redis Module加入Redis。


[1]: http://geek.csdn.net/news/detail/200023

[3]: http://dingyue.programmer.com.cn/
[4]: https://github.com/antirez/redis/blob/unstable/src/cluster.c
[5]: ../img/eaYvqqN.jpg
[6]: ../img/VziUjuU.jpg
[7]: ../img/NNzA7j.jpg
[8]: ../img/7R7nMvq.jpg
[9]: ../img/uaIni2Z.jpg
[10]: ../img/Z3IfaqN.jpg
[11]: https://redis.io/clients
[12]: ../img/YVFrUzZ.jpg
[13]: ../img/y6VRfyY.jpg
[14]: ../img/ErM7Bbq.jpg
[15]: https://cachecloud.github.io
[16]: http://twitter.github.io/pelikan/
[17]: ../img/IvEVreb.jpg
[18]: ../img/ZBVV73V.jpg
[19]: https://gist.github.com/antirez/a3787d538eec3db381a41654e214b31d
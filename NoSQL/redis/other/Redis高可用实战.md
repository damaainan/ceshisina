# Redis高可用实战

 时间 2017-10-16 13:25:06  运维生存时间

原文[http://www.ttlsa.com/redis/redis-ha/][1]


### 一，Redis简单介绍 

Redis是一个高性能的key-value非关系型数据库，由于其具有高性能的特性，支持高可用、持久化、多种数据结构、集群等，使其脱颖而出，成为常用的非关系型数据库。

此外，Redis的使用场景也比较多。

1. **会话缓存（Session Cache）**  
 Redis缓存会话有非常好的优势，因为Redis提供持久化，在需要长时间保持会话的应用场景中，如购物车场景这样的场景中能提供很好的长会话支持，能给用户提供很好的购物体验。
1. **全页缓存**  
 在WordPress中，Pantheon提供了一个不错的插件 wp-redis ，这个插件能以最快的速度加载你曾经浏览过的页面。
1. **队列**  
 Reids提供list和set操作，这使得Redis能作为一个很好的消息队列平台来使用。  
 我们常通过Reids的队列功能做购买限制。比如到节假日或者推广期间，进行一些活动，对用户购买行为进行限制，限制今天只能购买几次商品或者一段时间内只能购买一次。也比较适合适用。  
1. **排名**  
 Redis在内存中对数字进行递增或递减的操作实现得非常好。所以我们在很多排名的场景中会应用Redis来进行，比如小说网站对小说进行排名，根据排名，将排名靠前的小说推荐给用户。
1. **发布/订阅**  
 Redis提供发布和订阅功能，发布和订阅的场景很多，比如我们可以基于发布和订阅的脚本触发器，实现用Redis的发布和订阅功能建立起来的聊天系统。

此外还有很多其它场景，Redis都表现的不错。

### 二，Redis使用中单点故障问题

正是由于Redis具备多种优良特新，且应用场景非常丰富，以至于Redis在各个公司都有它存在的身影。那么随之而来的问题和风险也就来了。Redis虽然应用场景丰富，但部分公司在实践Redis应用的时候还是相对保守使用单节点部署，那为日后的维护带来了安全风险。

在2015年的时候，曾处理过一个因为单点故障原因导致的业务中断问题。当时的Redis都未采用分布式部署，采用单实例部署，并未考虑容灾方面的问题。

当时我们通过Redis服务器做用户购买优惠商品的行为控制，但后来由于未知原因Redis节点的服务器宕机了，导致我们无法对用户购买行为进行控制，造成了用户能够在一段时间内多次购买优惠商品的行为。

这种宕机事故可以说已经对公司造成了不可挽回的损失了，安全风险问题非常严重，作为当时运维这个系统的我来说有必要对这个问题进行修复和在架构上的改进。于是我开始了解决非分布式应用下Redis单点故障方面的研究学习。

### 三，非分布式场景下Redis应用的备份与容灾

Redis主从复制现在应该是很普遍了。常用的主从复制架构有如下两种架构方案。

#### 常用Redis主从复制

* **方案一**  
![][3]

 这是最常见的一种架构，一个Master节点，两个Slave节点。客户端写数据的时候是写Master节点，读的时候，是读取两个Slave，这样实现读的扩展，减轻了Master节点读负载。

* **方案二**  
![][4]

这种架构同样是一个Master和两个Slave。不同的是Master和Slave1使用keepalived进行VIP转移。Client连接Master的时候是通过VIP进行连接的。避免了方案一IP更改的情况。

#### Redis主从复制优点与不足

* **优点**
    1. 实现了对master数据的备份，一旦master出现故障，slave节点可以提升为新的master，顶替旧的master继续提供服务
    1. 实现读扩展。使用主从复制架构， 一般都是为了实现读扩展。Master主要实现写功能， Slave实现读的功能

* 不足 

架构方案一 

当Master出现故障时，Client就与Master端断开连接，无法实现写功能，同时Slave也无法从Master进行复制。

![][5]

此时需要经过如下操作(假设提升Slave1为Master):

1. 在Slave1上执 slaveof no one 命令提升Slave1为新的Master节点。
1. 在Slave1上配置为可写，这是因为大多数情况下，都将slave配置只读。
1. 告诉Client端(也就是连接Redis的程序)新的Master节点的连接地址。
1. 配置Slave2从新的Master进行数据复制。

架构方案二 

当master出现故障后，Client可以连接到Slave1上进行数据操作，但是Slave1就成了一个单点，就出现了经常要避免的单点故障(single point of failure)。

![][6]

之后需要经过如下操作： 

1. 在Slave1上执行slaveof no one命令提升Slave1为新的Master节点
1. 在Slave1上配置为可写，这是因为大多数情况下，都将Slave配置只读
1. 配置Slave2从新的Master进行数据复制

可以发现，无论是哪种架构方案都需要人工干预来进行故障转移(failover)。需要人工干预就增加了运维工作量，同时也对业务造成了巨大影响。这时候可以使用Redis的高可用方案-Sentinel

### 四，Redis Sentinel介绍

Redis Sentinel为Redis提供了高可用方案。从实践方面来说，使用Redis Sentinel可以创建一个无需人为干预就可以预防某些故障的Redis环境。

Redis Sentinel设计为分布式的架构，运行多个Sentinel进程来共同合作的。运行多个Sentinel进程合作，当多个Sentinel同一给定的master无法再继续提供服务，就会执行故障检测，这会降低误报的可能性。

### 五，Redis Sentinel功能

Redis Sentinel在Redis高可用方案中主要作用有如下功能：

* 监控   
Sentinel会不断的检查master和slave是否像预期那样正常运行
* 通知   
通过API，Sentinel能够通知系统管理员、程序监控的Redis实例出现了故障
* 自动故障转移   
如果master不像预想中那样正常运行，Sentinel可以启动故障转移过程，其中的一个slave会提成为master，其它slave会重新配置来使用新的master，使用Redis服务的应用程序，当连接时，也会被通知使用新的地址。
* 配置提供者   
Sentinel可以做为客户端服务发现的认证源：客户端连接Sentinel来获取目前负责给定服务的Redis master地址。如果发生故障转移，Sentinel会报告新的地址。

### 六，Redis Sentinel架构

![][7]

### 七，Redis Sentinel实现原理

Sentinel集群对自身和Redis主从复制进行监控。当发现Master节点出现故障时，会经过如下步骤：

* 1）Sentinel之间进行选举，选举出一个leader，由选举出的leader进行failover
* 2）Sentinel leader选取slave节点中的一个slave作为新的Master节点。对slave选举需要对slave进行选举的方法如下：   

a) **与master断开时间**

如果与master断开的时间超过down-after-milliseconds(sentinel配置） * 10秒加上从sentinel判定master不可用到sentinel开始执行故障转移之间的时间，就认为该slave不适合提升为master。

b) **slave优先级**

每个slave都有优先级，保存在redis.conf配置文件里。如果优先级相同，则继续进行。

c) 复制偏移位置 

复制偏移纪录着从master复制数据复制到哪里，复制偏移越大表明从master接受的数据越多，如果复制偏移量也一样，继续进行选举

d) **Run ID**

选举具有最小Run ID的Slave作为新的Master

流程图如下：

![][8]

* 3) Sentinel leader会在上一步选举的新master上执行slaveof no one操作，将其提升为master节点
* 4）Sentinel leader向其它slave发送命令，让剩余的slave成为新的master节点的slave
* 5）Sentinel leader会让原来的master降级为slave，当恢复正常工作，Sentinel leader会发送命令让其从新的master进行复制   
以上failover操作均有sentinel自己独自完成，完全无需人工干预。

### 总结

使用sentinel实现了Redis的高可用，当master出现故障时，完全无需人工干预即可实现故障转移。避免了对业务的影响，提高了运维工作效率。

在部署sentinel的时候，建议使用奇数个sentinel节点，最少三个sentinel节点。


[1]: http://www.ttlsa.com/redis/redis-ha/

[3]: ../img/aQbqqiF.png
[4]: ../img/iYJ3Qr7.png
[5]: ../img/B3EFRz2.jpg
[6]: ../img/m22iiy3.jpg
[7]: ../img/JFnYVrB.jpg
[8]: ../img/im6n2eZ.jpg
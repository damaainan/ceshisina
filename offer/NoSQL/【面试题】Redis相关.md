## 【面试题】Redis相关

来源：[https://www.cnblogs.com/datang6777/p/8630144.html](https://www.cnblogs.com/datang6777/p/8630144.html)

2018-03-23 14:30

     
#### 1.Redis与Memorycache的区别？

* Redis使用单线程，而Memcached是多线程，

* Redis使用现场申请内存的方式来存储数据，并且可以配置虚拟内存；Memcached使用预分配的内存池的方式。

* Redis实现了持久化和主从同步，容灾性会更强。而Memcached只是存放在内存中，服务器故障关机后数据就会消失，

* Redis支持五种数据类型：string,list, Hash,set及zset。而Memcached只是简单的key与value

* Redis的优点：对数据高并发读写、对海量数据的高效率存储和访问、对数据的可扩展性和高可用性

* Redis的应用场景：取最新N个数据的操作、排行榜应用取TOP N操作、需要精准设定过期时间的应用、计数器应用、获取某段时间所有数据排重值的唯一性操作、实时消息系统、构建队列系统、作缓存。

#### 2.Redis的五种数据结构？

* redisObject包含type、encoding、refcount、lru 、*ptr

| 数据结构 | 编码类型 | 常用命令 |
| - | - | - |
| String | int、embstr（39）、raw | set/setnx name value |
| list | ziplist（64、512）、list | lpush、rpop、llen |
| hash | ziplist（64、512）、hashtable | hset、hlen、hget |
| set | intset（512）、hashtable | sadd、scard、smembers、srem |
| zset | ziplist（64、128）、skiplist | zadd、zcard、zrangebyscore |

注：embstr编码创建字符串对象只需内存分配一次，调用一次内存释放函数，而raw都需要两次。可使用INCR和INCRBY生成分布式系统唯一序列号ID

* 双向链表便于在表的两端操作，但是它的内存地址不连续，容易产生内存碎片。ziplist是一整块连续内存，存储效率很高。但它每次数据变动都会引发一次内存的realloc。所以quicklist结合了双向链表和ziplist的优点，是一个双向无环链表，它的每一个节点都是一个ziplist。

#### 3.渐进式rehash过程？

* rehash的步骤 

![][0]

* 为字典的ht[1]哈希表分配空间

* 若是扩展操作，那么ht[1]的大小为>=ht[0].used*2的2^n
* 若是收缩操作，那么ht[1]的大小为>=ht[0].used的2^n

* 将保存在ht[0]中的所有键值对rehash到ht[1]中，rehash指重新计算键的哈希值和索引值，新hash通过hashFunction(key)函数获取， 新的索引值：index=hash&sizemask，然后将键值对放置到ht[1]哈希表的指定位置上。

* 当ht[0]的所有键值对都迁移到了ht[1]之后（ht[0]变为空表），释放ht[0]，将ht[1]设置为ht[0],新建空白的哈希表ht[1]，以备下次rehash使用。

* 扩展与收缩的条件

* 服务器目前没有执行bgsave或bgrewriteaof命令，并且哈希表的负载因子>=1或正在执行负载因子>=5就会进行rehash操作
* 当负载因子的值小于0.1时，程序就会对哈希表进行收缩操作
* 负载因子=ht[0].used/ht[0].size

* 渐进式rehash：若哈希表中保存着数量巨大的键值对，一次进行rehash，很有可能会导致服务器宕机。所以要分多次、渐进式的完成。采取分为而治的方式，将rehash键值对的计算均摊到每个字典增删改查操作，避免了集中式rehash的庞大计算量。

* 主要是维持索引计数器变量rehashidx，每次对字典执行增删改查时将rehashidx值+1，当ht[0]的所有键值对都被rehash到ht[1]中，程序将rehashidx的值设置为-1，表示rehash操作完成

#### 4.rehash源码？

* Redis为了兼顾性能的考虑，分为lazy rehashing：在每次对dict进行操作的时候执行一个slot的rehash。active rehashing：每100ms里面使用1ms时间进行rehash。（serverCron函数），而字典有安全迭代器的情况下不能进行 rehash

* 字典hash（lazy rehashing）函数调用：_dictRehashStep–> dictRehash

* 在_dictRehashStep函数中，会调用dictRehash方法，而_dictRehashStep每次仅会rehash一个值从ht[0]到 ht[1]，但由于_dictRehashStep是被dictGetRandomKey、dictFind、 dictGenericDelete、dictAdd调用的，因此在每次dict增删查改时都会被调用，这无疑就加快了rehash过程。
* 在dictRehash函数中每次增量rehash n个元素，由于在自动调整大小时已设置好了ht[1]的大小，因此rehash的主要过程就是遍历ht[0]，取得key，然后将该key按ht[1]的 桶的大小重新rehash，并在rehash完后将ht[0]指向ht[1],然后将ht[1]清空。在这个过程中rehashidx非常重要，它表示上次rehash时在ht[0]的下标位置。

* 一般情况服务器在对数据库执行读取/写入命令时会对数据库进行渐进式 rehash ，但如果服务器长期没有执行命令的话，数据库字典的 rehash 就可能一直没办法完成，为了防止出现这种情况，我们需要对数据库执行主动 rehash 。

* active rehashing函数调用的过程如下： 

serverCron->databasesCron–>incrementallyRehash->dictRehashMilliseconds->dictRehash，其中incrementallyRehash的时间较长，rehash的个数也比较多。这里每次执行 1 millisecond rehash 操作；如果未完成 rehash，会在下一个 loop 里面继续执行。

#### 5.持久化机制

* RBD是默认方式，将内存中数据以快照的方式写入到二进制文件中，默认文件名为dump.rdb

* 触发rdbSave过程的方式

* save命令：阻塞Redis服务器进程，直到RDB文件创建完毕为止。
* bgsave命令：派生出一个子进程，然后由子进程负责创建RDB文件，父进程继续处理命令请求。
* master接收到slave发来的sync命令
* 定时save(配置文件：900 1或300 10 或 60 10000）

* 命令bgsave与bgrewriteaof不能同时执行，若bgsave正在执行，则bgrewriteaof延迟到bgsave执行完再执行。若bgrewriteaof正在执行，则服务器拒绝执行bgsave命令。dirty计数器：记录距离上一次save/bgsave命令之后，服务器对数据库状态修改了多少次

* RDB文件结构：REDIS占5个字节，检查文件是否是RDB文件；db_version长度为4个字节，一个字符串表示的整数，记录了RDB文件的版本号；database包含零个或任意多个数据库；EOF：1个字节，标志着RDB文件正文内容的结束；check_sum：8字节，保存校验和，由前面四部分计算得出的。

* database结构：SELECTDB：1字节，表示要读一个数据库号码；db_number保存着一个数据库号码；key_value_pairs 保存了数据库中的所有键值对数据，由TYPE、key、value组成

* RDB的优缺点，优点：一个紧凑的文件，适合大规模的数据恢复，对数据完整性和一致性要求不高，恢复速度快。缺点：若Redis出现宕机，就会丢失最后一次快照后的所有修改。fork时，在数据大时较耗时，不能响应毫秒级请求。

* AOF：通过保存redis服务器所执行的写命令来记录数据库状态。

* AOF的优缺点，优点默认策略为每秒钟 fsync 一次，最多丢失一秒的数据，缺点：aof文件要远大于rdb文件，恢复速度慢于rdb，运行效率低。AOF提供更新的数据，而RDB提供更快的恢复速度

* AOF的修复：如果aof文件被破坏， 程序redis-check-aof–fix会进行修复（在flushAppendOnlyFile函数中），若出现断电等情况，就将写出错的情况记录到日志里，之后会处理错误。重启redis然后重新加载，AOF优先，它保存的数据集要比RDB完整.

* AOF写入的步骤：命令追加：将命令追加到AOF缓冲区，文件写入，文件同步

* AOF重写触发机制： 默认配置是当AOF文件大小是上次rewrite后大小的一倍且文件大于64M时并且没有子进程运行时触发。

* AOF重写的实现原理 ：fork出一条新进程来将文件重写(先写入临时文件最后再rename)，遍历新进程数据库的内存数据，将整个内存中的数据库内容用命令的方式重写了一个新的aof文件

* AOF后台重写：AOF重写程序放到子进程中执行，当Redis执行完一个命令后，它会同时将这个写命令发送给AOF缓冲区和AOF重写缓冲区，最后再写入临时文件中。

* 虚拟内存：暂时把不经常访问的数据从内存交换到磁盘中，从而提高数据库容量，但代码复杂，重启慢，复制慢等等，目前已被放弃

* 持久化的优化：抛弃AOF重写机制 ，保存RDB+AOF；Pika：适合数据大于50G且重要，多线程，持久化SSD

#### 6.reaof源码？

![][1]

* 对于缓存块的大小，因为程序需要不断对这个缓存执行 append 操作，而分配一个非常大的空间并不总是可能的，也可能产生大量的复制工作， 所以这里使用多个大小为 AOF_RW_BUF_BLOCK_SIZE 的空间来保存命令。默认每个缓存块的大小是10MB

* 如果客户端有命令执行，然后feedAppendOnlyFile函数判断是否开启了AOF标识，若开启，则将命令放入aof_buf_blocks中，继续判断是否有子进程在运行，若有，则说明正在进行reaof，就将命令放入aof_rewrite_buf_blocks中。

* 服务器开启就是循环文件事件和时间事件的过程，而时间事件是通过ServerCron()函数执行的。该函数会100ms执行一次查看是否有reaof或需要刷新事件。

* 若有刷新事件（默认每秒），调用flushAppendOnlyFile函数将aof_buf_blocks写入磁盘中，若有reaof事件，调用rewriteAppendOnlyFileBackground()函数，它执行 fork() ，调用rewriteAppendOnlyFile函数子进程，在tmpfile中对 AOF 文件进行重写，完成后子进程结束，通知父进程。

* 父进程会捕捉子进程的退出信号，如果子进程的退出状态是 OK ，那么父进程调用backgroundRewriteDoneHandler函数将aof_rewrite_buf_blocks追加到临时文件，然后使用 rename(2) 对临时文件改名，用它代替旧的 AOF 文件，但它调用的 write 操作会阻塞主进程。

* 到现在，后台 AOF 重写已经全部完成了。

#### 7.事务与事件

* 事务的错误处理：语法错误（入队错误）不会执行，而运行错误（执行错误）其它命令仍然执行。multi会开启事务，exec命令会取消对所有键的监控，还可以用unwatch命令来取消监控，而取消事务的命令为 Discard

* watch命令可以监控一个或者多个键，一旦有一个键被修改或删除，之后的事务就不会执行。其中exec / discard / unwatch命令会清除连接中的所有监视。通过watched_keys字典，可以知道哪些数据库键在被监视，若被监视的键被修改，则REDIS_DIRTY_CAS标识被打开，Redis中使用watch实现CAS算法。

* 文件事件处理器由套接字、I/O多路复用程序、文件事件分派器、事件处理器组成。时间事件分为定时事件和周期事件，serverCron函数，定期对自身的资源和状态进行检查。

* 客户端的关闭，硬性限制：若输出缓冲区的大小超过了硬性限制所设置的大小，就立即关闭客户端，软性限制：若输出缓冲区的大小超过了软性缓冲区的大小，但没超过硬性限制，则会记录客户端到达软性限制的起始时间，若持续时间服务器设置的时长，则关闭客户端。

* 伪客户端：创建Lua脚本的伪客户端：在服务器初始化时创建，一直持续到服务器关闭。载入AOF文件时使用的伪客户端：在载入时创建，载入完成后关闭。

* 命令请求从发送到完成的步骤：客户端将命令请求发送给服务器、服务器读取命令请求，并分析命令参数、命令执行器根据参数查找命令的实现函数setCommand，执行实现函数得到回复

* serverCron函数默认每隔100毫秒执行一次，它的功能如下：更新服务器时间缓存、更新LRU时钟、更新服务器每秒执行命令次数、更新服务器内存峰值记录、处理客户端资源、管理数据库资源、执行被延迟的bgrewriteaof、检查持久化操作的运行状态、将AOF缓冲区中的内容写入AOF文件、关闭异步客户端、增加cronloops计数器的值

#### 8.主从复制

* 通过执行slaveof命令或设置slaveof选项，让一个服务器去复制另一个服务器。允许多个slave server拥有和mater server相同的数据库副本

* 旧版复制功能（2.8之前）：同步（SYNC）和命令传播。缺点：断线后复制的效率低。

* 新版复制功能：完整重同步：用于初次复制，和SYNC一样，让主服务器创建并发送RDB文件，向从服务器发送保存在缓冲区中的写命令。部分重同步（PSYNC）：用于断线后重复制，重连后，主服务器将断开期间执行的写命令发送给从服务器。

* 部分重同步功能由主从服务器的复制偏移量、主服务器的积压缓冲区和服务器的运行ID组成

* 复制偏移量：主从服务器每次传播N个字节的数据，就把自己的复制偏移量加N
* 复制积压缓冲区是由主服务器维护的一个固定长度的先进先出队列，默认大小为1MB，当从服务器断开重连时，从服务器通过PSYNC命令将自己的复制偏移量offset发送给主服务器，若offset之后的数据在积压缓冲区中，就进行部分重同步的操作，否则进行完整重同步。
* 服务器运行ID：在启动时由40个随机的十六进制字符生成，断开重连时，从服务器将自己的运行ID发送给主服务器，若主从服务器的运行ID相同，则进行部分重同步操作，否则进行完整重同步操作。

* 复制的实现：设置主服务器的地址和端口、建立套接字连接、发送ping命令、身份验证（可选）、发送端口信息、同步、命令传播。

* 心跳检测：在命令传播阶段，从服务器会默认以每秒一次的频率向主服务器发送replconf ack < replication_offset>命令，作用是检测主从服务器的网络连接状态；辅助实现min-slaves-to-write和min-slaves-max-log选项；检测命令丢失，通过对比主从服务器的复制偏移量知道命令是否丢失。

* 主从复制的特点：一个master 可以拥有多个slave；多个slave可以连接到同一个master，还可以连接到其它slave；主从复制不会阻塞master，在同步数据时，master还可以继续处理client请求；提高系统的伸缩性。

* 哨兵是Redis高可用性的解决方案，由一个或多个sentinel实例组成的哨兵系统可以监视任意多个主服务器。

* 启动哨兵后，会创建连向主服务器的网络连接，命令连接指专门用于向主服务器发送命令，并接受命令回复，而订阅连接指专门用于订阅主服务器的sentinel:hello频道

* 获取服务器信息：和主数据库建立连接后，哨兵会定时执行操作：每10秒哨兵会向主数据库和从数据库发送info命令、每2秒哨兵会向主数据库和从数据库的sentinel:hello 频道发送自己的信息、每秒哨兵会向主数据库和从数据库和其他哨兵节点发送ping命令

* 选举领头Sentinel：如果主数据库断开连接，则会选举领头的哨兵节点对主从系统发起故障恢复。选举过程使用raft 算法。选举规则：删除下线或中断的服务器，尽量选举从服务器优先级高的、复制偏移量大的、运行ＩＤ小的。

#### 9.启动过程

* 初始化服务器状态结构，由initServerConfig函数完成，设置服务器的运行ID、默认运行频率、默认文件配置路径、运行架构、默认端口号、默认RDB持久化条件和AOF持久化条件、初始化服务器的LRU时钟、创建命令表

* 载入配置选项：指定配置参数或文件

* 初始化服务器数据结构： 在第一步时initServerConfig函数只是创建了命令表，而服务器状态还包含其他数据结构，如server.clients链表，server.db数组，server.pubsub_channels字典，server.lua环境，server.log慢查询日志。该函数会为以上数据结构分配内存，之后，initServer函数负责初始化数据结构，为服务器设置进程信号处理器、创建共享对象、打开服务器的监听端口、为serverCron函数创建时间事件、若存在AOF文件，打开AOF文件，若没有，就创建一个新的AOF文件、初始化服务器的后台I/O模块（bio），到这就出现了“面包”图

* 还原数据库状态：若服务器启用了AOF持久化功能，那么就用AOF文件还原数据库状态，否则使用RDB文件来还原数据库状态

* 执行服务器的事件循环

#### 10.集群

* Redis集群是redis提供的分布式数据库方案，集群通过分片来进行数据共享，并提供复制和故障转移功能。由多个节点组成，通过握手（cluster meet命令）添加节点

* Redis集群通过分片的方式保存数据库中的键值对，集群中的整个数据库被分为16384个槽（slot），每个节点可以处理0-16384个槽，当每个槽都有节点在处理时，集群就处于上线状态，命令cluster addslots < slot>可以将一个或多个槽指派给节点负责，slot属性是一个二进制数组，若slots[i]=1，表示节点负责处理槽i，若slots[i]=0,则表示节点不负责处理槽i

* 节点保存键值的步骤 ：先计算键属于哪个槽、判断槽是否由当前节点负责处理、若不是则返回moved错误（该错误被隐藏，但在单机模式下会打印错误），根据错误信息转向正确的节点，而节点数据库的实现只能使用0号库

* 重新分片：将任意数量已经指派给某个节点（源节点）的槽改为指派给另一个节点（目标节点），并且相关槽所属的键值对也会从源节点被移动到目标节点。可在线操作。ASK错误是在节点迁移的过程中，被迁移槽的一部分键值对保存在源节点，而另一部分保存在目的节点。

* 集群的消息有五种，消息由消息头和消息正文组成。

* meet消息：表示接收到服务器发送的cluster meet命令
* ping消息：对五个节点中最长时间没有发送过ping消息的节点发送ping消息，以检测是否在线
* pong消息：接收到meet或ping消息
* fail消息：当A节点判断B节点进入fail状态，A节点就会向集群广播一条关于节点B的fail消息
* publish消息：当节点接收到publish命令时，向集群广播一条publish消息

* 集群的优点，容错性：解决在单服redis的单点问题。扩展性：集群能够很好的实现缓存的性能升级，如多节点的热部署。性能提升 ：在扩展过程中体现。

* 发布与订阅：所有频道的订阅关系保存在服务器状态的pubsub_channel字典里，该字典的键是某个被订阅的频道，值是一个链表，记录了所有订阅这个链表的客户端。

* 将消息发送给频道订阅者 ：若客户端执行publish命令，那将在字典中查找该频道，并通过遍历链表将消息发送给该频道的所有订阅者。命令pubsub channels/numsub/numpat

#### 11.Redis的6种数据淘汰策略

* volatile-lru：利用LRU算法移除设置过过期时间的key。
* volatile-random：随机移除设置过过期时间的key。
* volatile-ttl：移除即将过期的key，根据最近过期时间来删除（辅以TTL）
* allkeys-lru：利用LRU算法移除任何key。
* allkeys-random：随机移除任何key。
* noeviction：不移除任何key，只是返回一个写错误。

* 监视器：执行monitor命令，客户端就可以将自己变成一个监视器，实时的接收并打印出服务器当前处理的命令请求的相关信息。服务器将所有的监视器都记录在monitors链表中。每次处理命令请求时，服务器都会遍历monitors链表，将相关信息发送给监视器。

* 慢查询日志功能用于记录执行时间超过给定时长的命令请求。以先进先出的形式保存在slowlog链表，参数：slowlog-log-slower-than和slowlog-max-len，

#### 12.redis的并发竞争问题？

* 客户端：进行连接池化、读写加锁；服务端：使用setnx命令实现分布式锁。

* **`缓存穿透：访问不存在的对象，解决方法：缓存空对象或布隆过滤器`** 

* 缓存雪崩：缓存层不可用，导致存储层调用量暴增，甚至挂掉。解决方法：保证缓存层的高可用性、依赖隔离组件为后端限流并降级、

* 缓存热点key重建优化，

* 若有一个热点key，并发量巨大，不能再短时间重建缓存，在缓存失效的瞬间，大量线程重建缓存，造成后端负载过大，甚至让应用崩溃
* 解决目标：减少重建缓存的次数、数据尽可能一致、较少的潜在危险、
* 解决方法：互斥锁：只允许一个线程重建缓存，其他线程等待（setnx）；永不过期：对key没有设置过期时间，为每个value设置一个逻辑过期时间，若超时，则使用单独的线程去构建缓存。

* 配置文件：daemonize yes 后台运行；pidfile /var/redis-server.pid 进程文件；

* Jedis的使用

* 连接redis的IP和端口号 Jedis jedis = new Jedis(“127.0.0.1”,6379);
* 执行事务：Transaction transaction=jedis.multi();

[0]: http://img.blog.csdn.net/20170725164319219?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQvYmFpeWVfeGluZw==/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/SouthEast&mprfK=http%3A%2F%2Fblog.csdn.net%2F
[1]: http://img.blog.csdn.net/20170730235122288?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQvYmFpeWVfeGluZw==/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/SouthEast&mprfK=http%3A%2F%2Fblog.csdn.net%2F
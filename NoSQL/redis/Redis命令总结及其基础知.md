# [Redis命令总结及其基础知识讲述][0]

**阅读目录**

* [1、redis的不同之处][1]
* [2、key相关操作][2]
* [3、数据库相关命令][3]
* [4、Connection连接][4]
* [5、STRING字符串][5]
* [6、LIST链表][6]
* [7、SET集合][7]
* [8、HASH散列、哈希][8]
* [9、SORT SET有序集合][9]
* [10、发布与订阅][10]
* [11、Redis的基本事务][11]
* [12、快照持久化][12]
* [13、AOF持久化][13]
* [14、Redis主从复制详细分析][14]
* [15、处理系统故障][15]

#### 1、redis的不同之处

Redis拥有其他数据库不具备的数据结构，又拥有内存存储（这使得redis的速度非常快），远程操作（使得redis可以与多个客户端和服务器进行连接）、持久化（使得服务器可以在重启的时候仍然保持重启之前的数据）和可扩展（通过主从复制和分片）。

Redis结构类型：STRING、LIST、SET、HASH、ZSET(有序集合)

STRING：可以存贮字符串、整数、浮点数

LIST：一个链表，每个节点都包含一个字符串

SET：包含字符串的无序收集容器，每个字符串都是独一无二的

HASH：包含键值的无序散列表，即可以存储多个键值对（key唯一）之间的映射【可以理解为关联数组】

ZSET（有序集合）：字符串成员(member)与浮点数分值(score)一一映射。元素排列顺序由分值大小决定，每个member是唯一的。

#### 2、key相关操作

适用redis全部类型数据

1、DELkey[key…] 删除某个或者多个key-value

2、KEYS pattern 返回匹配的key

3、RANDOMKEY 从当前数据库返回一个key

4、MOVE key num 将当前数据库中的key移到数据库num中

5、RENAME key newkey 改名，newkey存在时则将会覆盖

6、RENAMENX key newkey 当且仅当newkey不存在的时候才执行

7、TYPE key 返回key数据类型

8、EXPIRE key second 设置key的过期时间、秒

9、EXPIREAT key timestamp 设置key的过期时间、时间戳

10、PEXPIRE key milliseconds 设定多少毫秒内过期

11、PEXPIREAT key timestamp-milliseconds 设置为时间戳，毫秒级

12、TTL key 查看给定键距离过期时间还有多少秒

13、PTTL key 查看给定键距离过期时间还有多少毫秒

14、PERSIST key 移除过期时间

15、EXISTS key 检查key是否存在

16、OBJECT refcount|encoding|idletime key 返回key，引用次数|内部存储编码|空转时间

可编为多种方式编码：

    1、字符串可存为raw（一般字符串）、int（小数字）
    
    2、列表可存为ziplist、linkedlist
    
    3、集合可存为inset（数字小集合）、hashtable
    
    4、散列可存为zipmap（小散列）、hashtable
    
    5、有序集合可存为ziplist（小有序集合）、skiplist（任何大小）

17、SORT source-key [BY pattern] [LIMIT offset count] [GET pattern…] [ASC|DESC] [ALPHA] [STORE dest-key]

用于排序，这个排序功能很强大

参数：

    1、source-key：排序的key
    
    2、BY pattern：表示可以通过外部权重进行排序（即外部key，需要与排序key有关联）。例如：链表key为list-userID（1,2,3,4…），则外部key名为，goods_1、goods_2…，则BY pattern为（BY goods_*）
    
    3、LIMIT offset count：表示排序后返回的数据行
    
    4、GET pattern…：获取外部数据（参数与BY pattern一样）
    
    5、ASC|DESC：升序|降序
    
    6、ALPHA：采用字符排序，默认是数字排序
    
    7、STORE dest-key：表示将结果存入dest-key中

#### 3、数据库相关命令

1、SELECT db_index 选择数据库，一共有16个数据库，默认在数据库0

2、DBSIZE 返回当前数据库key数量

3、FLUSHDB 删除当前数据库所有key

4、FLUSHALL 删除所有数据库所有key

#### 4、Connection连接

1、设置密码

可以通过redis配置文件进行设置密码requirepass password配置，配置后需要使用auth pass 进行解锁才能使用其他命令

2、QUITE 关闭与服务器连接退出客户端

3、PING 用于测试与服务器端连接是否生效，返回pong

4、ECHO message 打印消息，测试用

#### 5、STRING字符串

1、SET key value/GET key 设置key-value对/获取值

2、MSET key1 value1 key2 value2…./MGET

3、SETNX key value 当且仅当key不存在时才设置

4、SETEX key second value 设置k-v对时并且设置过期时间

5、GETSET key value 获取旧值设置新值

6、STRLEN key 字符串长度

7、APPEMD key value 追加值

8、GETRANGE key-name start end 返回次字符串的start到end之间的字符

9、SETRANGE key-name offset value 将value代替从offset开始的字符串

10、INCR、DECR、INCRBY、DECRBY、INCRBYFLOAT 增加值

11、GETBIT key-name offset 将字符串看做是二进制位串，并返回位串中的偏移量offset的二进制位的值

12、SETBIT key-name offset value 将字符串看做是二进制位串，并将位串中偏移量offset的二进制值设置为value

13、BITCOUNT key-name [start end] 统计二进制位串里面值为1的数量

14、BITOP AND|OR|XOR|NOT dest-key key1 key2… 对多个key执行并或异或非，并将结果存入到dest-key

#### 6、LIST链表

列表允许用户从序列两端推入或者弹出元素

1、LPUSH/RPUSH、LPOP/RPOP

2、LRANGE key start end 返回偏移量中的值

3、LINDEX key offset 返回偏移量为offset中的值

4、LLEN key-name 返回key-name链的长度

5、LREM key count value count=0 删除全部一样的；count>0从左边检索删除count个；count<0从右边检索，删除count个

6、LTRIM key-name start end 保持start到end所有元素，其他删除

7、lset key index value 将key中下标为index更新值为value。Index超过则报错

8、linsert key befort|after val value 在key中位于val值前或者后，插入value值。Key或者val不存在，则返回错误

9、BLPOP/BRPOP key-name[key-name2…] timeout 多少秒内阻塞并等待可弹出元素出现

10、RPOPLPUSH key-name1 key-name2 从key-name1中弹出最右边的元素，推入key-name2最左边，并返回value元素

11、BRPOPLPUSH key-name value timeout 阻塞式

#### 7、SET集合

1、SADD key member[member2…] 添加一个或者多个member

2、SREM key member[member2…] 移除一个或者多个member

3、SMEMBERS key 返回key中所有的member

4、SISMEMBER key member 判断member是否在key中

5、SCARD key 返回集合里包含的元素数量

6、SPOP key 随机移除集合中的一个元素，并返回

7、SRANDMEMBER key 随机返回一个member，不删除

8、SRANDMEMBER key-name n 随机返回集合里的n个元素。n负数可重复，正数不出现重复

9、SMOVE key-name1 key-name2 value 将value元素从key-name1中移到key-name2中

10、SDIFF key-name1 [key-name2…] 差集

11、SDIFFSTORE dest-key key-name1[key-name2…] 差集存入dest-key中

12、SINTER key-name1[key-name2…] 返回交集

13、SINTERSTORE dest-key key-name1[key-name2…] 交集存入dest-key中

14、SUNION key-name1[key-name2…] 返回并集

15、SUNIONSTORE dest-key key-name1[key-name2…] 并集存入dest-key中

#### 8、HASH散列、哈希

1、HSET key field value 设置散列值

2、hsetnx key field value 当且仅当field不存在时设置

3、HGET key field 获取值

4、HDEL key field field2… 删除一个或者多个值

5、HMSET key field value field2 value2…. 设置多个

6、HMGET key field field2… 获取多个

7、HGETALL key 由于redis是单线程操作，假若hgetall返回的数据量大耗时大，将会导致其他客户端的请求得不到响应

8、HLEN key 返回散列包含键值对的数量

9、HEXISTS key-name filed 检查field是否存key-name中

10、HKEYS key/HVALUES key 返回key中的field、返回key中的value

11、HINCRBY key-name field num 给key-name中field的值（必须是数字）增加num

12、HINCRBYFLOAT key-name key incre

#### 9、SORT SET有序集合

1、ZADD key score member 给有序集合key添加member

2、ZREM key member[member2…] 移出一个或者多个成员

3、ZCRAD key 返回有序集合里的成员数量

4、ZCOUNT key-name min max 返回分值介于min和max之间的成员数量

5、ZSCORE key member 返回成员的分值

6、ZINCRBY key increment member 将member成员分值加上increment

7、ZRANGE key start stop [withscores] 成员按分值从小到大排列，返回有序集合给定排名范围

8、ZREVRANGE key start stop [withscores] 成员按分值从大到小排列，返回有序集合给定排名范围

9、ZRANK key member 分值从小到大排序，返回member有序集合的排名

10、ZREVRANK key member 分值从大到小排序，返回member有序集合的排名

11、ZRANGEBYSCORE key-name min max [withscores][limt offset count] 返回有序集合中介于min和max之间的所有成员、从小到大

12、ZREVRANGEBYSCORE key-name max min [withscores][limt offset count] 返回有序集合中介于max和min之间的所有成员、从大到小

13、ZREMRANGEBYRANK key-name start stop 移出所有有序集合排名介于start和stop之间的元素

14、ZREMRANGEBYSCORE key-name min max 移出所有有序集合score介于min和max之间的元素

15、ZINTERSTORE dest-key key-count key-name1[key-name2…] [weights weight1 weight2…] [aggregate sum|min|max] 先对应分值乘以weights，再取交集，分值对应后面的aggregate，默认sum。结果存入dest-key中

16、ZUNIONSTORE dest-key key-count key-name1[key-name2…] [weights weight1 weight2…] [aggregate sum|min|max]

#### 10、发布与订阅

频道仅仅只是一个key而已

1、SUBSCRIBE channel [channel..] 订阅频道

2、UNSUBSCRIBE [channel1,channel2…] 退定频道

3、PUBLISH channel message 给频道发布消息

4、PSUBSCRIBE pattern [pattern…] 订阅匹配给定模式的所有频道

5、PUNSUBSCRIBE [pattern…] 退订匹配给定模式的所有频道

#### 11、Redis的基本事务

Redis的基本事务需要用到MULTI命令和EXEC命令，这种事务可以让一个客户端在不被其他客户端打断的情况下执行多个命令。这种事务与关系型数据库的能够执行回滚的事务不同，redis中，只要被MULTI和EXEC包围住的命令将一个接一个的执行，直达执行完毕后才会处理其他客户端的命令。

1、WATCH key key2[key3…] 监视key，假若在事务执行之前key数据有更改，则事务将会失败

2、UNWATCH 取消watch监视的所有key。假若已经执行了exec或者discard那么就不用再执行unwatch了，因为exec是执行事务，此时watch效果已经生效；discard命令则是取消事务

3、MULTI 标记一个事务的开始

4、EXEC 执行事务块中的命令，并返回事务块中每条命令的结果。假若有被监视的key有修改则，则事务将被打断

5、DISCARD 取消事务

#### 12、快照持久化

Redis、系统、硬件三个中任意一个崩溃将会造成最近一次已成功创建快照的数据丢失

1、BGSAVE将会调用一个fork创建一个子进程处理持久化数据，父进程继续处理请求命令

2、SAVE接到save命令后，将快照创建完毕后，才处理其他命令

3、配置文件save 60 1000，自动调用BGSAVE

4、接收到SHUTDOWN命令将会自动调用SAVE，成功创建快照后才关闭redis

5、连接别的redis服务将可能会自动执行BGSAVE

6、LASTSAVE返回最后一次执行快照持久化的时间戳bgsave、save

#### 13、AOF持久化

AOF持久化的实质就是将被执行的命令写到AOF文件的末尾，以此来记录数据发生的变化。因此只要执行一次AOF文件中的命令就可以恢复AOF文件所记录的数据集了。

将文件写入硬盘的步骤：

1、调用对文件写入时

2、首先将需要写入的内容存储到缓冲区

3、操作系统在某个时候将缓冲区里的内容写入硬盘。（可通过调用file.flush()方法请求系统尽快将其写入硬盘中，具体何时写入仍然是操作系统决定）

appendfsync配置

    always 将每个redis写命令都要同步写到硬盘中，这将会降低redis的速度
    
    everysec 每秒执行一次同步，显式地将多个写命令同步到硬盘中
    
    no 让系统来决定何时进行同步

缺点:(AOF文件的大小)

    1、随着redis的不断运行，AOF的文件体积将会不断变大，甚至将硬盘所有空间都用完。
    
    2、再者就是，当redis重启的时候需要执行AOF里面的记录命令来恢复之前的数据，假若AOF文件非常大的话，那么还原操作将会耗费很多时间

解决：

    1、手动操作，向redis发送BGREWRITEAOF重写AOF文件，移出冗余的命令
    
    2、配置自动，auto-aof-rewrite-percentage 100、auto-aof-rewrite-min-size 64M即当AOF文件体积大于64M，并且AOF文件体积比上一次重写之后的体积大了至少一倍（100%）的时候，则redis将自动执行BGREWRITEAOF

#### 14、Redis主从复制详细分析

Redis复制即使相当于mysql主从复制一样

1、前提

配置文件设置好dir选项以及dbfilename选项，并且指示的路径和文件必须是redis进程可以写。

2、两种方法设置redis复制

    第一种：在已经运行的redis中，执行SLAVEOF host port
    
    第二种：在配置文件中添加SLAVEOF host port（启动redis将会自动开启）

3、相关命令

SLAVEOF no one 终止复制操作

SLAVEOF host port 开始新的复制操作（默认端口6379）

4、redis复制的启动过程

    1、主：等待命令进入；
    
    从：连接主，发送SYNC
    
    2、主：开始执行BGSAVE，并用缓冲区记录BGSAVE后的写命令；
    
    从：根据配置选项来决定是否使用当前数据来处理当前客户端命令（还有数据的情况，即之前有连接）；否则返回错误
    
    3、主：BGSAVE执行完毕，向从发送快照文件，并且继续用缓冲区记录客户端的写命令
    
    从：丢弃所有旧数据，载入主发过来的快照文件
    
    4、主：快照文件发送完毕，开始发送缓冲区里的写命令（保证数据同步）
    
    从：对主发来的快照文件处理完毕，此时正常接收命令请求进行处理
    
    5、主：缓冲区里的命令发送完毕；此时开始每执行一条命令就向从发送相同的命令
    
    从：执行主发过来的所有缓冲区里的命令；此时开始接收并执行主传过来的命令

> 注意：

    1、从服务器在进行同步时，会清空自己的所有数据
    
    2、redis不支持主主复制

5、多个从服务器进行连接主服务器

出现的两种情况：

    1、新的从服务器在**步骤三尚未执行**，所有从服务器都接收相同的快照文件和缓冲区的命令
    
    2、新的从服务器在**步骤三正在执行或者已经执行**，主与较早的从进行五步骤的复制后，再继续与新的从进行五步骤的复制
    
> 注意：假若有多个从服务器恰巧是第二种情况，那么占用的宽带可能使其他命令请求难以传递给主服务器。

6、INFO返回关于 Redis 服务器的各种信息和统计值。

#### 15、处理系统故障

1、验证快照文件和AOF文件

redis-check-dump <dump.rdb>

redis-check-aof –fix <file.aof>

原理：检查出错位置，aof可以将出错之后的删除；快照只能检查出错位置，不能修复。

2、更换故障主机

A主B从；A宕机

    1、换C做主，B执行SAVE，发给C，开启C，设置B成为C的从。
    
    2、换B做主，C做从。

参考书籍：

《Redis实战》 Josiah.Carlson 著

黄健宏 译

[0]: http://www.cnblogs.com/phpstudy2015-6/p/6567923.html
[1]: #_label0
[2]: #_label1
[3]: #_label2
[4]: #_label3
[5]: #_label4
[6]: #_label5
[7]: #_label6
[8]: #_label7
[9]: #_label8
[10]: #_label9
[11]: #_label10
[12]: #_label11
[13]: #_label12
[14]: #_label13
[15]: #_label14
[16]: #_labelTop
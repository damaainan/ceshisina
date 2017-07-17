# Redis常用命令速查

## 一、Key
##### Key命令速查：

命令 | 说明
-|-
DEL | 删除给定的一个或多个 key，不存在的 key 会被忽略，返回值：被删除 key 的数量
DUMP |    序列化给定 key，返回被序列化的值，使用 RESTORE 命令可以将这个值反序列化为 Redis 键
EXISTS |  检查给定 key 是否存在
EXPIRE |  为给定key设置有效时间，接受时间点
EXPIREAT |    为给定key设置有效时间，接受时间戳timestamp
KEYS  |   查找所有符合给定模式 pattern 的 key；KEYS * 匹配数据库中所有 key；KEYS h?llo 匹配 hello，hallo等。KEYS h[ae]llo匹配hello和hallo
MIGRATE | 将 key 原子性地从当前实例传送到目标实例的指定数据库上，一旦传送成功， key 保证会出现在目标实例上，而当前实例上的 key 会被删除。执行的时候会阻塞进行迁移的两个实例
MOVE |    将当前数据库的 key 移动到给定的数据库 db 当中
OBJECT |  从内部察看给定 key 的 Redis 对象
PERSIST | 移除给定 key 的有效时间
PEXPIRE | 以毫秒为单位设置 key 的有效时间
PEXPIREAT |   以毫秒为单位设置 key 的有效时间(timespan)
PTTL |    以毫秒为单位返回key的剩余有效时间
RANDOMKEY |   从当前数据库中随机返回(已使用的)一个key
RENAME |  将Key改名
RENAMENX |    当且仅当 newkey 不存在时，将 key 改名为 newkey
RESTORE | 反序列化给定的序列化值，并将它和给定的 key 关联
SORT |    返回或保存给定列表、集合、有序集合 key 中经过排序的元素
TTL | 以秒为单位，返回给定 key 的剩余有效时间
TYPE |    返回 key 所储存的值的类型
SCAN |    增量迭代
 

## 二、String
##### String命令速查：

命令 | 说明
-|-
APPEND |  将值追加到指定key的值末尾，如果key不存在，则相当于增加操作。
BITCOUNT |    计算给定字符串中，被设置为 1 的Bit位的数量。
BITOP |   对一个或多个保存二进制位的字符串 key 进行位元操作
DECR |    将 key 中储存的数字值减一。Key不存在，则将值置0，key类型不正确返回一个错误。
DECRBY |  将key所储存的值减去指定数量
GET | 返回key所关联的字符串值，如果Key储存的值不是字符串类型，返回一个错误。
GETBIT |  对key所储存的字符串值，获取指定偏移量上的位
GETRANGE |    返回key中字符串值的子字符串，字符串的截取范围由start和end两个偏移量决定
GETSET |  将给定key的值设为value，并返回key的旧值。非字符串报错。
INCR |    将 key 中储存的数字值增一。不能转换为数字则报错。
INCRBY |  将key所储存的值加上指定增量
INCRBYFLOAT | 为key中所储存的值加上指定的浮点数增量
MGET |    返回所有(一个或多个)给定key的值
MSET |    同时设置一个或多个key-value对
MSETNX |  同时设置一个或多个key-value对，若一个key已被占用，则全部的执行取消。
PSETEX |  以毫秒为单位设置 key 的有效时间
SET | 将字符串值value关联到key 
SETBIT |  对key所储存的字符串值，设置或清除指定偏移量上的位(bit)
SETEX |   将值value关联到 key，并将key的有效时间(秒)
SETNX |   当key未被使用时，设置为指定值
SETRANGE |    用value参数覆写(overwrite)给定key所储存的字符串值，从偏移量 offset 开始
STRLEN |  返回key所储存的字符串值的长度
 

## 三、Hash
##### Hash命令速查：

命令 | 说明
-|-
HDEL |    删除哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
HEXISTS | 查看哈希表 key 中，给定域 field 是否存在
HGET |    返回哈希表 key 中给定域 field 的值
HGETALL | 返回哈希表 key 中，所有的域和值
HINCRBY | 为哈希表 key 中的域 field 的值加上指定增量
HINCRBYFLOAT |    为哈希表 key 中的域 field 加上指定的浮点数增量
HKEYS |   返回哈希表 key 中的所有域
HLEN |    返回哈希表 key 中域的数量
HMGET |   返回哈希表 key 中，一个或多个给定域的值
HMSET |   同时将多个 field-value (域-值)对设置到哈希表 key 中
HSET |    将哈希表 key 中的域 field 的值设为 value
HSETNX |  当且仅当域 field 不存在时，将哈希表 key 中的域 field 的值设置为 value
HVALS |   返回哈希表 key 中所有域的值
HSCAN |   增量迭代
 

## 四、List
##### List命令速查：

命令 | 说明
-|-
BLPOP |  它是 LPOP 命令的阻塞版本，当给定列表内没有任何元素可供弹出的时候，连接将被 BLPOP 命令阻塞，直到等待超时或发现可弹出元素为止 
BRPOP |   与BLPOP同义，弹出位置不同
BRPOPLPUSH |  当列表 source 为空时， BRPOPLPUSH 命令将阻塞连接，直到等待超时
LINDEX |  返回列表 key 中，下标为 index 的元素
LINSERT | 将值 value 插入到列表 key 当中
LLEN |    返回列表 key 的长度
LPOP |    移除并返回列表 key 的头元素
LPUSH |   将一个或多个值 value 插入到列表 key 的表头
LPUSHX |  将值 value 插入到列表 key 的表头，当且仅当 key 存在并且是一个列表
LRANGE |  返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定
LREM |    根据参数 count 的值，移除列表中与参数 value 相等的元素
LSET |    将列表 key 下标为 index 的元素的值设置为 value
LTRIM |   对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
RPOP |    移除并返回列表 key 的尾元素
RPOPLPUSH  |  命令 RPOPLPUSH 在一个原子时间内，执行两个动作：1、将列表 source 中的最后一个元素(尾元素)弹出，并返回给客户端。2、将 source 弹出的元素插入到列表 destination ，作为 destination 列表的的头元素。
RPUSH |   将一个或多个值 value 插入到列表 key 的表尾
RPUSHX |  将值 value 插入到列表 key 的表尾，当且仅当 key 存在并且是一个列表
 

## 五、Set
##### 　Set命令速查

命令 | 说明
-|-
SADD |    将一个或多个 member 元素加入到集合 key 当中，已经存在于集合的 member 元素将被忽略
SCARD |   返回集合 key 的集合中元素的数量
SDIFF |   返回一个集合的全部成员，该集合是所有给定集合之间的差集
SDIFFSTORE |  这个命令的作用和 SDIFF 类似，但它将结果保存到新集合，而不是简单地返回结果集
SINTER |  返回一个集合的全部成员，该集合是所有给定集合的交集
SINTERSTORE | 与SINTER类似，不过可以指定保存到新集合
SISMEMBER |   判断 member 元素是否集合 key 的成员
SMEMBERS |    返回集合 key 中的所有成员
SMOVE |   将 member 元素从一个集合移动到另一个集合
SPOP |    移除并返回集合中的一个随机元素
SRANDMEMBER | 仅仅返回随机元素，而不对集合进行任何改动，与SPOP的区别在于不移除
SREM |    移除集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略
SUNION |  返回一个集合的全部成员，该集合是所有给定集合的并集
SUNIONSTORE | 与SUNION类似，不过可以指定保存到新集合
SSCAN |   增量迭代
 

## 六、SortedSet
##### 　SortedSet命令速查：

命令 | 说明
-|-
ZADD |    将一个或多个 member 元素及其 score 值加入到有序集 key 当中
ZCARD |   返回有序集 key 的基数
ZCOUNT |  返回有序集 key 中， score 值在 min 和 max 之间(包括 score 值等于 min 或 max )的成员的数量
ZINCRBY | 为有序集 key 的成员 member 的 score 值加上指定增量
ZRANGE |  返回有序集 key 中，指定区间内的成员(小到大排列)
ZRANGEBYSCORE |   返回有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员
ZRANK |   返回有序集 key 中成员 member 的排名。其中有序集成员按 score 值递增(从小到大)顺序排列
ZREM |    移除有序集 key 中的一个或多个成员，不存在的成员将被忽略
ZREMRANGEBYRANK | 移除有序集 key 中，指定排名(rank)区间内的所有成员
ZREMRANGEBYSCORE |    移除有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员
ZREVRANGE |   返回有序集 key 中，指定区间内的成员，成员位置按score大到小排列
ZREVRANGEBYSCORE |    返回有序集 key 中， score 值介于 max 和 min 之间(默认包括等于 max 或 min )的所有的成员。成员按 score 值递减(从大到小)排列
ZREVRANK |    返回有序集 key 中成员 member 的排名。其中有序集成员按 score 值递减(从大到小)排序
ZSCORE |  返回有序集 key 中，成员 member 的 score 值
ZUNIONSTORE | 计算给定的一个或多个有序集的并集，其中给定 key 的数量必须以 numkeys 参数指定，并将该并集(结果集)储存到新集合
ZINTERSTORE | 计算给定的一个或多个有序集的交集，其中给定 key 的数量必须以 numkeys 参数指定，并将该交集(结果集)储存到新集合
ZSCAN  | 增量迭代
 

## 七、Pub/Sub
##### Pub/Sub命令速查：

命令 | 说明
-|-
PSUBSCRIBE |  订阅一个或多个符合给定模式的频道
PUBLISH | 将信息 message 发送到指定的频道
PUBSUB |  PUBSUB 是一个查看订阅与发布系统状态的内省命令
PUNSUBSCRIBE |    指示客户端退订所有给定模式
SUBSCRIBE |   订阅给定的一个或多个频道的信息
UNSUBSCRIBE | 指示客户端退订给定的频道
 

## 八、Transaction
##### Transaction命令速查：

命令 | 说明
-|-
DISCARD | 取消事务，放弃执行事务块内的所有命令
EXEC |    执行所有事务块内的命令
MULTI |   标记一个事务块的开始
UNWATCH | 取消 WATCH 命令对所有 key 的监视
WATCH |   监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断
 

## 九、Script
##### script命令速查：

命令 | 说明
-|-
EVAL  |   通过内置的 Lua 解释器，可以使用 EVAL 命令对 Lua 脚本进行求值
EVALSHA | 根据给定的 sha1 校验码，对缓存在服务器中的脚本进行求值
SCRIPT EXISTS  |  给定一个或多个脚本的 SHA1 校验和，返回一个包含 0 和 1 的列表，表示校验和所指定的脚本是否已经被保存在缓存当中
SCRIPT FLUSH  |   清除所有 Lua 脚本缓存
SCRIPT KILL | 停止当前正在运行的 Lua 脚本，当且仅当这个脚本没有执行过任何写操作时，这个命令才生效。这个命令主要用于终止运行时间过长的脚本
SCRIPT LOAD | 将脚本 script 添加到脚本缓存中，但并不立即执行这个脚本
 

## 十、Connection
##### 　connection命令速查:

命令 | 说明
-|-
AUTH  |  通过设置配置文件中 requirepass 项的值，可以使用密码来保护 Redis 服务器
ECHO  |  打印一个特定的信息 message ，测试时使用。
PING   | 使用客户端向 Redis 服务器发送一个 PING ，如果服务器运作正常的话，会返回一个 PONG，通常用于测试与服务器的连接是否仍然生效，或者用于测量延迟值
QUIT  |  请求服务器关闭与当前客户端的连接
SELECT | 切换到指定的数据库，数据库索引号 index 用数字值指定，以 0 作为起始索引值


## 十一、Server
##### server命令速查：

命令 | 说明
-|-
BGREWRITEAOF  |   执行一个 AOF文件 重写操作。重写会创建一个当前 AOF 文件的体积优化版本。
BGSAVE  | 在后台异步(Asynchronously)保存当前数据库的数据到磁盘
CLIENT GETNAME |  返回 CLIENT SETNAME 命令为连接设置的名字
CLIENT KILL | 关闭地址为 ip:port 的客户端
CLIENT LIST | 以人类可读的格式，返回所有连接到服务器的客户端信息和统计数据
CLIENT SETNAME |  为当前连接分配一个名字
CONFIG GET  | CONFIG GET 命令用于取得运行中的 Redis 服务器的配置参数
CONFIG RESETSTAT |    重置 INFO 命令中的某些统计数据
CONFIG REWRITE |  CONFIG REWRITE 命令对启动 Redis 服务器时所指定的 redis.conf 文件进行改写
CONFIG SET |  CONFIG SET 命令可以动态地调整 Redis 服务器的配置而无须重启
DBSIZE |  返回当前数据库的 key 的数量
DEBUG OBJECT   |  DEBUG OBJECT 是一个调试命令，它不应被客户端所使用
DEBUG SEGFAULT |  执行一个不合法的内存访问从而让 Redis 崩溃，仅在开发时用于 BUG 模拟
FLUSHALL   |  清空整个 Redis 服务器的数据(删除所有数据库的所有 key )
FLUSHDB | 清空当前数据库中的所有 key
INFO  |   返回关于 Redis 服务器的各种信息和统计数值
LASTSAVE   |  返回最近一次 Redis 成功将数据保存到磁盘上的时间，以 UNIX 时间戳格式表示
MONITOR | 实时打印出 Redis 服务器接收到的命令，调试用
PSYNC   | 用于复制功能的内部命令
SAVE    | SAVE 命令执行一个同步保存操作，将当前 Redis 实例的所有数据快照(snapshot)以 RDB 文件的形式保存到硬盘。一般来说，在生产环境很少执行 SAVE 操作，因为它会阻塞所有客户端，保存数据库的任务通常由 BGSAVE 命令异步地执行。然而，如果负责保存数据的后台子进程不幸出现问题时， SAVE 可以作为保存数据的最后手段来使用。
SHUTDOWN    | SHUTDOWN 命令执行以下操作：停止所有客户端，如果有至少一个保存点在等待，执行 SAVE 命令，如果 AOF 选项被打开，更新 AOF 文件，关闭 redis 服务器(server)
SLAVEOF |  SLAVEOF 命令用于在 Redis 运行时动态地修改复制(replication)功能的行为
SLOWLOG | Slow log 是 Redis 用来记录查询执行时间的日志系统
SYNC  |   用于复制功能的内部命令
TIME  |   返回当前服务器时间
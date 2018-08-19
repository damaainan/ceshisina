# Redis 键

作者  [三产][0] 已关注 2017.06.16 16:50  字数 4275  阅读 1 评论 0 喜欢 0

# 单个键管理

在 《Redis 概述》 中我们已经介绍过 DEL 、 EXISTS 、 EXPIRE 、SCAN 的用法了，下面我们介绍其他比较重要的命令。

### 查看存储类型

#### TYPE

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**TYPE key**

##### 说明：

返回 key 所储存的值的类型。

##### 返回值：

none (key不存在)

string (字符串)

list (列表)

set (集合)

zset (有序集)

hash (哈希表)

##### 示例：

    coderknock> OBJECT ENCODING zinterstoreTest
    "ziplist"
    coderknock> TYPE embstrKey
    string
    coderknock> TYPE setTest
    zset
    # 元素不存在
    coderknock> TYPE nonKey
    none

### 查看对象内部

#### OBJECT

>** 自2.2.3可用。**

>** 时间复杂度：** O(1)。

##### 语法：**OBJECT subcommand [arguments [arguments ...]]**

##### 说明：

OBJECT 命令允许从内部察看给定 key 的 Redis 对象。

它通常用在除错(debugging)或者了解为了节省空间而对 key 使用特殊编码的情况。

当将Redis用作缓存程序时，你也可以通过 OBJECT 命令中的信息，决定 key 的驱逐策略(eviction policies)。

OBJECT 命令有多个子命令：

* OBJECT REFCOUNT <key> 返回给定 key 引用所储存的值的次数。此命令主要用于除错。
* OBJECT ENCODING <key> 返回给定 key 所储存的值所使用的内部表示(representation)。
* OBJECT IDLETIME <key> 返回给定 key 自储存以来的空闲时间(idle， 没有被读取也没有被写入)，以秒为单位。

对象可以以多种方式编码：

* 字符串可以被编码为 raw (一般字符串)或 int (为了节约内存，Redis 会将字符串表示的 64 位有符号整数编码为整数来进行储存）。
* 列表可以被编码为 ziplist 或 linkedlist 或 quicklist。 ziplist 是为节约大小较小的列表空间而作的特殊表示。
* 集合可以被编码为 intset 或者 hashtable 。 intset 是只储存数字的小集合的特殊表示。
* 哈希表可以编码为 ziplist 或者 hashtable 。 ziplist 是小哈希表的特殊表示。
* 有序集合可以被编码为 ziplist 或者 skiplist 格式。 ziplist 用于表示小的有序集合，而 skiplist 则用于表示任何大小的有序集合。

假如你做了什么让 Redis 没办法再使用节省空间的编码时(比如将一个只有 1 个元素的集合扩展为一个有 100 万个元素的集合)，特殊编码类型(specially encoded types)会自动转换成通用类型(general type)。

##### 返回值：

REFCOUNT 和 IDLETIME 返回数字。

ENCODING 返回相应的编码类型。

如果您尝试检查的对象不存在，则返回 nil。

##### 示例：

    coderknock> OBJECT IDLETIME nonKey
    (nil)
    coderknock> OBJECT IDLETIME embstrKey
    (integer) 2886
    coderknock> OBJECT REFCOUNT embstrKey
    (integer) 1
    coderknock> OBJECT REFCOUNT setTest
    (integer) 1
    coderknock>

### 查看存储类型

#### RENAME

>** 自1.0.0可用。**  
>** 时间复杂度：** O(1)。

##### 语法：**RENAME key newkey**

##### 说明：

将 key 改名为 newkey 。

当 key 和 newkey 相同，或者 key 不存在时，返回一个错误。

当 newkey 已经存在时， RENAME 命令将覆盖旧值。

##### 返回值：

改名成功时提示 OK ，失败时候返回一个错误。

##### 示例：

    # 重命名不存在的 key 
    coderknock> RENAME nonKey newNonKey
    (error) ERR no such key
    coderknock> RENAME setTest newSetTest
    OK
    coderknock>  RENAME newSetTest newSetTest
    OK

为了防止被强行 RENAME，Redis 提供了 RENAMENX 命令，确保只有 newKey 不存在时候才被覆盖。

#### RENAMENX

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**RENAMENX key newkey**

##### 说明：

当且仅当 newkey 不存在时，将 key 改名为 newkey 。

当 key 不存在时，返回一个错误。

##### 返回值：

修改成功时，返回 1 。

如果 newkey 已经存在，返回 0 。

##### 示例：

    # 已经存在的就重命名不了了
    coderknock>  RENAMENX newSetTest newSetTest
    (integer) 0

在使用重命名命令时，有两点需要注意：

* 由于重命名键期间会执行 DEL 命令删除旧的键，如果键对应的值比较大，会存在阻塞Redis的可能性，这点不要忽视。

* 如果 RENAME 和 RENAMENX 中的 key 和 newkey 如果是相同的，在 Redis3.2 和之前版本返回结果略有不同。

Redis3.2中会返回OK：

    coderknock> rename key key
    OK

Redis3.2 之前的版本会提示错误：

    coderknock> rename key key
    (error) ERR source and destination objects are the same

### 随机返回一个键

#### RANDOMKEY

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**RANDOMKEY**

##### 说明：

从当前数据库中随机返回(不删除)一个 key 。值的类型。

##### 返回值：

当数据库不为空时，返回一个 key 。

当数据库为空时，返回 nil 。

##### 示例：

    coderknock> RANDOMKEY
    "ztest"
    coderknock> RANDOMKEY
    "testIntset"
    # 数据库为空
    coderknock> FLUSHDB  # 删除当前数据库所有 key
    OK
    coderknock> RANDOMKEY
    (nil)

### 键过期

Redis 键过期处理之前介绍的 EXPIRE 命令外还有 EXPIREATPEXPIREPEXPIREATPTTLPERSIST**如果过期时间为负值，键会立即被删除，犹如使用 DEL 命令一样。**

**对于字符串类型键，执行 SET 命令会去掉过期时间，这个问题很容易在开发中被忽视。**

**Redis 不支持二级数据结构（例如哈希、列表）内部元素的过期功能**

**SETEX 命令作为 SET + EXPIRE 的组合，不但是原子执行，同时减少了一次网络通讯的时间**#### EXPIREAT 

>** 自1.2.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**EXPIREAT key timestamp**

##### 说明：

EXPIREAT 的作用和 *EXPIRE* 类似，都用于为 key 设置生存时间。

不同在于 EXPIREAT 命令接受的时间参数是 UNIX 时间戳(unix timestamp)。

##### 返回值：

如果生存时间设置成功，返回 1 。

当 key 不存在或没办法设置生存时间，返回 0 。

##### 示例：

    coderknock> EXPIREAT test 1497338910 # 2017/6/13 15:28:30 过期
    (integer) 1
    coderknock> EXPIREAT nonkey 1497338910
    (integer) 0

#### PEXPIRE

>** 自2.6.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**PEXPIRE key milliseconds**

##### 说明：

这个命令和 EXPIRE 命令的作用类似，但是它以毫秒为单位设置 key 的生存时间，而不像 EXPIRE 命令那样，以秒为单位。

##### 返回值：

设置成功，返回 1key 不存在或设置失败，返回 0##### 示例：

    coderknock> SET test "coderknock"
    OK
    # 这里设置的比较小需要一起输入这三个命令才能看出效果，或者时间设置的长些
    coderknock> PEXPIRE test 1500
    (integer) 1
    coderknock> TTL test    # TTL 的返回值以秒为单位
    (integer) 2
    coderknock> PTTL test   # PTTL 可以给出准确的毫秒数
    (integer) 1489

#### PEXPIREAT

>** 自2.6.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**PEXPIREAT key milliseconds-timestamp**

##### 说明：

这个命令和 EXPIREAT 命令类似，但它以毫秒为单位设置 key 的过期 unix 时间戳，而不是像 `EXPIREAT 那样，以秒为单位。

##### 返回值：

如果生存时间设置成功，返回 1 。

当 key 不存在或没办法设置生存时间时，返回 0 。(查看 EXPIRE 命令获取更多信息)失败，返回 0##### 示例：

    coderknock> PEXPIREAT test 1497338910000 # 2017/6/13 15:28:30 过期
    (integer) 1
    coderknock> PEXPIREAT nonkey 1497338910000
    (integer) 0

#### PTTL

>** 自2.6.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**PTTL key**

##### 说明：

这个命令类似于 TTL 命令，但它以毫秒为单位返回 key 的剩余生存时间，而不是像 TTL 命令那样，以秒为单位。

##### 返回值：

当 key 不存在时，返回 -2 。

当 key 存在但没有设置剩余生存时间时，返回 -1 。

否则，以毫秒为单位，返回 key 的剩余生存时间。

**在 Redis 2.8 以前，当 key 不存在，或者 key 没有设置剩余生存时间时，命令都返回 -1** 。

##### 示例：

    # 不存在的 key
    coderknock> FLUSHDB
    OK
    coderknock> PTTL key
                (integer) -2
    
    # key 存在，但没有设置剩余生存时间
    coderknock> SET key value
    OK
    coderknock> PTTL key
                (integer) -1
    
    
    # 有剩余生存时间的 key
    coderknock> PEXPIRE key 10086
    (integer) 1
    coderknock> PTTL key
    (integer) 6179

#### PERSIST

>** 自2.2.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**PERSIST key**

##### 说明：

移除给定 key 的生存时间，将这个 key 从『易失的』(带生存时间 key )转换成『持久的』(一个不带生存时间、永不过期的 key )。

##### 返回值：

当生存时间移除成功时，返回 1 .

如果 key 不存在或 key 没有设置生存时间，返回 0 。

##### 示例：

    coderknock> SET test "coderknock"
    OK
    coderknock> EXPIRE test 1500
    (integer) 1
    coderknock> TTL test
    (integer) 1494
    coderknock> PERSIST test # 移除 key 的生存时间
    (integer) 1
    coderknock> TTL test
                        (integer) -1

迁移键功能非常重要，因为有时候我们只想把部分数据由一个 Redis 迁移到另一个 Redis（例如从生产环境迁移到测试环境），Redis 发展历程中提供了 MOVE、 DUMP + RESTORE、MIGRATE 三组迁移键的方法，它们的实现方式以及使用的场景不太相同，下面分别介绍。

### 迁移键 MOVE 方式

#### MOVE

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：MOVE key db

##### 说明：

将当前数据库的 key 移动到给定的数据库 db 当中。（Redis内部可以有多个数据库，彼此数据是相互隔离的）。

如果当前数据库(源数据库)和给定数据库(目标数据库)有相同名字的给定 key ，或者 key 不存在于当前数据库，那么 MOVE 没有任何效果。

因此，也可以利用这一特性，将 MOVE 当作锁(locking)原语(primitive)。

##### 返回值：

移动成功返回 1 ，失败则返回 0 。

##### 示例：

    coderknock> SELECT 0 # 默认数据库就是 0 ，这里为了让大家更清晰
    OK
    coderknock> GET test
    "a"
    coderknock> MOVE test 1
    (integer) 1
    coderknock> GET test # MOVE 后本库的 test 键就被删除了
    (nil)
    coderknock> SELECT 1
    OK
    coderknock[1]> GET test
    (nil)
    coderknock[1]> GET test
    "a"
    coderknock[1]>  SET newTest db1
    OK
    coderknock[1]> SELECT 0
    coderknock> SET newTest db0
    OK
    coderknock> MOVE newTest 1 # db 1 中有该键所以没有迁移成功
    (integer) 0
    coderknock> GET newTest    # db 0 中该键没有删除
    "db0"
    coderknock> SELECT 1
    OK
    coderknock[1]> GET newTest # db1 中 newTest也没有变化
    "db1"

### 迁移键 DUMP + RESTORE

#### DUMP

>** 自2.6.0可用。**

>** 时间复杂度：** 查找给定键的复杂度为 O(1) ，对键进行序列化的复杂度为 O(N*M) ，其中 N 是构成  key  的 Redis 对象的数量，而 M 则是这些对象的平均大小。

> 如果序列化的对象是比较小的字符串，那么复杂度为 O(1) 。

##### 语法：**DUMP key**

##### 说明：

序列化给定 key ，并返回被序列化的值，使用 *RESTORE* 命令可以将这个值反序列化为 Redis 键。

序列化生成的值有以下几个特点：

* 它带有 64 位的校验和，用于检测错误， *RESTORE* 在进行反序列化之前会先检查校验和。
* 值的编码格式和 RDB 文件保持一致。
* RDB 版本会被编码在序列化值当中，如果因为 Redis 的版本不同造成 RDB 格式不兼容，那么 Redis 会拒绝对这个值进行反序列化操作。

**序列化的值不包括任何生存时间信息。**

##### 返回值：

如果 key 不存在，那么返回 nil 。

否则，返回序列化之后的值。

##### 示例：

    coderknock> DUMP newSetTest
    "\x0c\"\"\x00\x00\x00\x1f\x00\x00\x00\x06\x00\x00\x03one\x05\xf2\x02\x03two\x05\xf3\x02\x05three\a\xf4\xff\a\x00\xde\xde\xc5\xd8|\x84\xd6\xd0"

#### RESTORE

>** 自2.6.0可用。**

>** 时间复杂度：** 查找给定键的复杂度为 O(1) ，对键进行反序列化的复杂度为 O(N*M) ，其中 N 是构成  key  的 Redis 对象的数量，而 M 则是这些对象的平均大小。

> 有序集合(sorted set)的反序列化复杂度为 O(N_M_log(N)) ，因为有序集合每次插入的复杂度为 O(log(N)) 。

> 如果反序列化的对象是比较小的字符串，那么复杂度为 O(1) 。

##### 语法：**RESTORE key ttl serialized-value [REPLACE]**

##### 说明：

反序列化给定的序列化值，并将它和给定的 key 关联。

参数 ttl 以毫秒为单位为 key 设置生存时间；如果 ttl 为 0 ，那么不设置生存时间。

**RESTORE 在执行反序列化之前会先对序列化值的 RDB 版本和数据校验和进行检查，如果 RDB 版本不相同或者数据不完整的话，那么 RESTORE 会拒绝进行反序列化，并返回一个错误。**

如果键 key 已经存在， 并且给定了 REPLACE 选项， 那么使用反序列化得出的值来代替键 key 原有的值； 相反地， 如果键 key 已经存在， 但是没有给定 REPLACE 选项， 那么命令返回一个错误。

更多信息可以参考 *DUMP* 命令。

##### 返回值：

如果反序列化成功那么返回 OK ，否则返回一个错误。

##### 示例

    coderknock[1]> RESTORE restoreSet 0 "\x0c\"\"\x00\x00\x00\x1f\x00\x00\x00\x06\x00\x00\x03one\x05\xf2\x02\x03two\x05\xf3\x02\x05three\a\xf4\xff\a\x00\xde\xde\xc5\xd8|\x84\xd6\xd0"
    OK
    coderknock[1]> ZRANGE restoreSet 0 -1
    1) "one"
    2) "two"
    3) "three"
    coderknock[1]> SELECT 0
    coderknock> RESTORE restoreSet 0 "\x0c\"\"\x00\x00\x00\x1f\x00\x00\x00\x06\x00\x00\x03one\x05\xf2\x02\x03two\x05\xf3\x02\x05three\a\xf4\xff\a\x00\xde\xde\xc5\xd8|\x84\xd6\xd0"
    # 如果 key 已经存在但没有设置 REPLACE 会 报错
    coderknock> RESTORE restoreSet 0 "\x0c\"\"\x00\x00\x00\x1f\x00\x00\x00\x06\x00\x00\x03one\x05\xf2\x02\x03two\x05\xf3\x02\x05three\a\xf4\xff\a\x00\xde\xde\xc5\xd8|\x84\xd6\xd0"
    (error) BUSYKEY Target key name already exists.
    coderknock>  SADD restoreSet 1 java # 这里修改下 restoreSet 中的值
    coderknock>  RESTORE restoreSet 0 "\x0c\"\"\x00\x00\x00\x1f\x00\x00\x00\x06\x00\x00\x03one\x05\xf2\x02\x03two\x05\xf3\x02\x05three\a\xf4\xff\a\x00\xde\xde\xc5\xd8|\x84\xd6\xd0" REPLACE
    OK
    coderknock> ZRANGE restoreSet 0 -1
    1) "one"
    2) "two"
    3) "three"
    # 随便输一条序列化语句
    coderknock> RESTORE test 0  "sui bian shu de"
    (error) ERR DUMP payload version or checksum are wrong

整个迁移过程并非原子性的，而是通过客户端分步完成的。

迁移过程是开启了两个客户端连接，所以 DUMP 的结果不是在源 Redis 和目标 Redis 之间进行传输。

### 迁移键 MIGRATE

#### MIGRATE

>** 自2.6.0可用。**

>** 时间复杂度：** 这个命令在源实例上实际执行  *DUMP* 命令和  *DEL* 命令，在目标实例执行  *RESTORE* 命令，查看以上命令的文档可以看到详细的复杂度说明。

> key 数据在两个实例之间传输的复杂度为 O(N) 。

##### 语法：MIGRATE host port key|"" destination-db timeout [COPY][REPLACE][KEYS key [key ...]]

##### 说明：

host：目标Redis的IP地址 port：目标Redis的端口 timeout：迁移的超时时间（单位为毫秒）。

将 key 原子性地从当前实例传送到目标实例的指定数据库上，一旦传送成功， key 保证会出现在目标实例上，而当前实例上的 key 会被删除。

这个命令是一个原子操作，它在执行的时候会阻塞进行迁移的两个实例，直到以下任意结果发生：迁移成功，迁移失败，等待超时。

命令的内部实现是这样的：它在当前实例对给定 key 执行 DUMP 命令 ，将它序列化，然后传送到目标实例，目标实例再使用 RESTORE 对数据进行反序列化，并将反序列化所得的数据添加到数据库中；当前实例就像目标实例的客户端那样，只要看到 RESTORE 命令返回 OK ，它就会调用 DEL 删除自己数据库上的 key 。

timeout 参数以毫秒为格式，指定当前实例和目标实例进行沟通的**最大间隔时间**。这说明操作并不一定要在 timeout 毫秒内完成，只是说数据传送的时间不能超过这个 timeout 数。

MIGRATE 命令需要在给定的时间规定内完成 IO 操作。如果在传送数据时发生 IO 错误，或者达到了超时时间，那么命令会停止执行，并返回一个特殊的错误： IOERR 。

当 IOERR 出现时，有以下两种可能：

* key 可能存在于两个实例
* key 可能只存在于当前实例

唯一不可能发生的情况就是丢失 key ，因此，如果一个客户端执行 MIGRATE 命令，并且不幸遇上 IOERR 错误，那么这个客户端唯一要做的就是检查自己数据库上的 key 是否已经被正确地删除。

如果有其他错误发生，那么 MIGRATE 保证 key 只会出现在当前实例中。（当然，目标实例的给定数据库上可能有和 key 同名的键，不过这和 MIGRATE 命令没有关系）。

**可选项：**

* COPY ：不移除源实例上的 key 。
* REPLACE ：替换目标实例上已存在的 key 。
* KEYS - 如果key参数是一个空字符串，命令将会转移 KEYS 选项后面的所有键（有关更多信息，请参阅上述部分）。

COPY 、REPLACE 仅在3.0及更高版本中可用。 KEYS 从 Redis 3.0.6 开始可用。

##### 返回值：

迁移成功时返回 OK ，否则返回相应的错误。

##### 示例：

我们需要再启动一个 Redis 或者使用远程 Redis（注意可访问性）

    redis-server --port 6370

我们启动两个客户端，一个连接默认 6379 的 Redis 一个连接 刚启动的 6370 的Redis

    redis-cli -p 6370 # 这里只是列出了连接 6370 的方法

下面示例中注意端口的变化：

    # 在 6379 中添加数据
    coderknock: 6379> SADD 1 java 2 go 3 python
    (integer) 5
    coderknock: 6379> SRANDMEMBER 1
    "python"
    coderknock: 6379> keys *
                    1) "1"
    coderknock: 6379> flushdb
    OK
    coderknock: 6379> SADD set java python go
    (integer) 3
    coderknock: 6379> SMEMBERS set
    1) "python"
    2) "java"
    3) "go"
    coderknock: 6379> ZADD zSet 100coderknock 20 www.coderknock
    (integer) 2
    coderknock: 6379> ZRANGE zSet 0 -1
    1) "www.coderknock"
    2) "coderknock"
    coderknock: 6379> SET hellocoderknock
    OK
    coderknock: 6379> KEYS *
    1) "zSet"
    2) "set"
    3) "hello"
    # 在 6370 中添加数据
    coderknock: 6370> SET db 6370
    OK
    coderknock: 6370> SET set a b c
    (error) ERR syntax error
    coderknock: 6370> SADD set a b c
    (integer) 3
    coderknock: 6370> KEYS *
                    1) "set"
    2) "db"
    # 迁移一个 key
    coderknock: 6379> MIGRATE 127.0.0.1 6370 hello 0 1000
    OK
    coderknock: 6379> GET hello # 当前的 Redis 中 hello 被删除了
    (nil)
    coderknock: 6370> GET hello # 6370 可以查到 hello 这个 key 了
    "CoderKnock"
    # 当使用 keys 参数时要求 key 必须是 空字符串（不能不设也不能设为其他）
    coderknock: 6379>  MIGRATE 127.0.0.1 6370  0 1000 COPY KEYS ""
    (error) ERR When using MIGRATE KEYS option, the key argument must be set to the empty string
    coderknock: 6379> MIGRATE 127.0.0.1 6370 "zSet" 0 1000 COPY KEYS ""
    (error) ERR When using MIGRATE KEYS option, the key argument must be set to the empty string
    # 当输入的 keys 都不存在时返回 NOKEY
    coderknock: 6379> MIGRATE 127.0.0.1 6370 ""  0 1000 COPY KEYS a
    NOKEY
    # 这里不能使用通配符，通配符会当做普通字符处理
    coderknock: 6379>  MIGRATE 127.0.0.1 6370 ""  0 1000 COPY KEYS *
                                                            NOKEY
    # 当目标库有相同 key 会报错
    coderknock: 6379> MIGRATE 127.0.0.1 6370 ""  0 1000 COPY KEYS zSet set
    (error) ERR Target instance replied with error: BUSYKEY Target key name already exists.
    # 加入 REPLACE 参数正常 这里 KEYS 需要在参数列表最后 不然会将 COPY 等当做是一个 key
    coderknock: 6379> MIGRATE 127.0.0.1 6370 ""  0 1000 COPY REPLACE KEYS zSet set
    OK
    # 使用了 COPY 所以当期库中数据没删除
    coderknock: 6379> KEYS *
                    1) "zSet"
    2) "set"
    # 6370 中数据被迁移
    coderknock: 6370> KEYS *
                    1) "set"
    2) "hello"
    3) "zSet"
    4) "db"
    # set 的数据被替换
    coderknock: 6370> SMEMBERS set
    1) "java"
    2) "go"
    3) "python"

MIGRATE 命令也是用于在 Redis 实例间进行数据迁移的，实际上 MIGRATE 命令就是将DUMP 、RESTORE 、DEL 三个命令进行组合，从而简化了操作流程。MIGRATE 命令具有原子性，而且从 Redis3.0.6 版本以后已经支持迁移多个键的功能，有效地提高了迁移效率。

### 遍历键

《Redis 概览》中的 KEYS 以及 SCAN当需要遍历所有键时（例如检测过期或闲置时间、寻找大对象等）， KEYS 是一个很有帮助的命令，例如想删除所有以 s 字符串开头的键，可以执行如下操作：

    [coderknock ~]# redis-cli
    coderknock> set s1 a
    OK
    coderknock> set s2 b
    OK
    coderknock> set s3 c
    OK
    coderknock> set a a
    OK
        # 测试过程中发现 windows 版本无法这样操作
    [coderknock ~]# redis-cli keys s* | xargs redis-cli del
    (integer) 3

但是如果考虑到 Redis 的单线程架构就不那么美妙了，如果 Redis 包含了大量的键，执行 KEYS 命令很可能会造成 Redis 阻塞，所以一般建议不要在生产环境下使用 KEYS 命令。但有时候确实有遍历键的需求该怎么办，可以在以下三种情况使用：

* 在一个不对外提供服务的Redis从节点上执行，这样不会阻塞到客户端的请求，但是会影响到主从复制。
* 如果确认键值总数确实比较少，可以执行该命令。
* 使用 SCAN 命令渐进式的遍历所有键，可以有效防止阻塞。

在 SCAN 的过程中如果有键的变化（增加、删除、修改），那么遍历效果可能会碰到如下问题：新增的键可能没有遍历到，遍历出了重复的键等情况，也就是说 SCAN 并不能保证完整的遍历出来所有的键，这些是我们在开发时需要考虑的。

[0]: http://www.jianshu.com/u/2de721a368d3
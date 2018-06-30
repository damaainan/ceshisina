# Redis 字符串

作者  [三产][0] 已关注 2017.06.16 09:59*  字数 2643  阅读 0 评论 0 喜欢 0

# 字符串操作相关命令

**Redis 的命令及其选项时不区分大小写的（键以及值是区分大小写的），本文中采用 [] 表示可选项，命令中的关键字使用大写，参数值使用小写以便区分**

## 常用命令

### 设置值

#### SET

> ** 自1.0.0起可用。**

> ** 时间复杂度：**  O(1)

##### 语法：SET key value [EX seconds] [PX milliseconds] [NX|XX]

##### 返回值：成功返回 OK 失败返回 nil
##### 示例：

    coderknock> SET testStringSet HelloWorld
    OK
    coderknock> GET testStringSet
    "HelloWorld"

**_如果参数错误会抛出异常_**

##### 说明：

该命令中的可选项解释如下：

* EX seconds ：为该键设置秒级过期时间。EX 为 "expire " 的缩写
* PX milliseconds ：为该键设置毫秒级过期时间
* NX ：键必须不存在，才可以设置成功，用于添加。NX 为 "**N**ot e**X**ists"的缩写
* XX ：与XX相反，键必须存在，才可以设置成功，用于更新

EX 以及 PX 选项比较好理解，下面只提供 NX 和 XX 的示例，请先查看命令然后根据上面的说明进行分析之后再查看下文的分析：

    coderknock> SET testStringSet coderknock NX
    (nil)
    coderknock> GET testStringSet
    "HelloWorld"
    coderknock> SET testStringSetNX coderknock NX
    OK
    coderknock> GET testStringSetNX
    "coderknock"
    coderknock> SET testStringSetNX sanchan XX
    OK
    coderknock> SET testStringSetXX coderknock XX
    (nil)
    coderknock> GET testStringSetXX
    (nil)
    coderknock> GET testStringSetNX
    "sanchan"
    coderknock>

上面的示例中我们对之前设置的 testStringSet 再次进行设置并使用 NX 选项，发现返回值非 之前示例中的 OK 而变成了 nil （代表 SET 操作失败），查看 testStringSet 发现确实未更改，之后 SET 一个没有的 key 并且使用 NX 可以正常 SET。当我们使用 XX 选项添加一个新的 key时发现添加失败，修改已有的 key 成功。

**_Redis 还提供了 SETNX和 SETEX、PSETEX 三个个命令，相当于 SET 命令使用 NX 选项、 EX选项、PX选项时的情形。由于其特性，SETNX可以用作分布式锁的实现 [https://redis.io/topics/distlock][1] 官方给出了相关方法，但是这三个指令之后的版本可能会删除。_**

#### SETNX

SETNX 是 "**SET** if **N**ot e**X**ists" 的简写，

##### 语法: SETNX key value

##### 返回值：当设置成功时 返回 1 反之返回 0。【注意可选项与 SET 有区别，返回值也有区别】

##### 示例：

    coderknock> SETNX testSETNX SETNX
    (integer) 1
    coderknock> SETNX testSETNX a
    (integer) 0
    coderknock> GET testSETNX
    "SETNX"
    coderknock> SETNX testSETNXEX test 1 
    (error) ERR wrong number of arguments for 'setnx' command

**_如果参数错误会抛出异常，例如上面示例的最后一行多了一个 1 的参数_**

#### SETEX

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O(1)

##### 语法：SETEX key seconds value

##### 返回值：成功返回 OK**_如果参数错误会抛出异常_**

##### 示例：

    coderknock> PSETEX mykey 1000 "Hello"
    "OK"
    coderknock> PTTL mykey
    (integer) 999
    coderknock> GET mykey
    "Hello"

#### PSETEX

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O(1)

##### 语法：PSETEX key milliseconds value

##### 返回值：成功返回 OK_PSETEX 与 SETEX很类似所以就不过多讲解了_

##### 示例：

    coderknock> PSETEX mykey 1000 "Hello"
    "OK"
    coderknock> PTTL mykey
    (integer) 999
    coderknock> GET mykey
    "Hello"

官方网站上有更详细的说明：[SET 命令][2][SETEX命令][3][SETNX命令][4][PSETEX命令][5]

### 获取值

#### GET

> ** 自1.0.0起可用。**

> ** 时间复杂度：**  O(1)

##### 语法：GET key

##### 返回值：key 存在则返回 key 对应的值，反之返回 nil##### 示例：

    coderknock> GET nonexisting
    (nil)
    coderknock> SET mykey "Hello"
    "OK"
    coderknock> GET mykey
    "Hello"

### 批量设置值

#### MSET

> ** 自1.0.1起可用。**

> ** 时间复杂度：**  O(N) 其中N是要设置的 key 的数量

##### 语法：MSET key value [key value ...]

##### 返回值：MSET 始终成功，不会有失败的情况，所以返回值始终是 OK##### 示例：

    coderknock> MSET key1 "Hello" key2 "World"
    "OK"
    coderknock> GET key1
    "Hello"
    coderknock> GET key2
    "World"

##### 说明：

[MSET][6]是原子的，所有给定的键都是立即设置的。客户端不可能看到某些 key 被更新，而其他 key 没有更新。

#### MSETNX

> ** 自1.0.1起可用。**

> ** 时间复杂度：**  O(N) 其中N是要设置的 key 的数量

##### 语法：MSETNX key value [key value ...]

##### 返回值：所有 key 都设置成功返回 1，设置失败返回 0

##### 示例：

    coderknock> MSETNX key1 "Hello" key2 "there"
    (integer) 1
    coderknock> MSETNX key2 "there" key3 "world"
    (integer) 0
    coderknock> MGET key1 key2 key3
    1) "Hello"
    2) "there"
    3) (nil)

##### 说明：

为给定的 key 设置各自的值。 即使只有一个 key 已经存在，[MSETNX][7] 也不会执行任何操作。

可以使用 [MSETNX][7] 这种语义来设置表示唯一逻辑对象的不同字段的不同 key ，以确保所有字段设置或都不设置。

[MSETNX][7]是原子的，所有给定的 key 都是一次设置的。客户端不可能看到某些 key 被更新，而其他 key 没有更新。

### 批量获取值

#### MGET

> ** 自1.0.0起可用。**

> ** 时间复杂度：**  O(N) 其中N是要设置的 key 的数量

##### 语法：MGET key [key ...]

##### 返回值：按顺序返回所有 key 的值，如果有些键不存在，那么它的值会是 nil

##### 示例：

    coderknock> SET key1 "Hello"
    "OK"
    coderknock> SET key2 "World"
    "OK"
    coderknock> MGET key1 key2 nonexisting
    1) "Hello"
    2) "World"
    3) (nil)

### 批量操作的优点

批量操作能够提高程序的性能，如果不使用批量操作来执行 n 次操作则花费为：

n 次操作时间 = n 次网络时间 + n 次命令时间。而使用批量操作后则只需要：

n次操作时间 = 1次 网络时间 + n 次命令时间这样会节省很多网络开销，在大型项目中 网络开销往往是系统性能的瓶颈。需要注意的是，如果一次批处理数量太多可能导致 Redis 阻塞或者 网络阻塞。

### 计数

#### INCR

> ** 自1.0.1起可用。**

> ** 时间复杂度：**  O(N) 其中N是要设置的 key 的数量

##### 语法：INCR key

返回值：

INCR 命令用于对值做自增操作，返回结果分为三种情况：

* 值不是整数，返回错误。
* 值是整数，返回自增后的结果。
* 键不存在，按照值为0自增，返回结果为1

##### 示例：

    coderknock> SET mykey "10"
    "OK"
    coderknock> INCR mykey
    (integer) 11
    coderknock> GET mykey
    "11"
    coderknock>

对一个不存在的键执行incr操作后，返回结果是1：

    coderknock> EXISTS key
    (integer) 0
    coderknock> INCR key
    (integer) 1
    coderknock> INCR key
    (integer) 2

如果值不是整数，那么会返回错误：

    coderknock> SET hello world
    OK
    coderknock> INCR  hello
    (error) ERR value is not an integer or out of range

除了 INCR 命令，Redis提供了 DECR（自减）、INCRBY（自增指定数字）、DECRBY（自减指定数字）、INCRBYFLOAT（自增浮点数）：

    DECR key
    INCRBY key increment
    DECRBY key decrement
    INCRBYFLOAT key increment

很多存储系统和编程语言内部使用 CAS（Compare and Swap） 机制实现计数功能，会有一定的 CPU 开销，但在 Redis 中完全不存在这个问题，因为 Redis 是单线程架构，任何命令到了 Redis 服务端都要顺序执行。

#### ##不常用命令

### 追加值

#### APPEND

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法：APPEND key

##### 说明：

如果 key 已经存在并且是字符串，则该命令将追加 value 到字符串的末尾。如果 key不存在，它将被创建并设置为空字符串，因此[APPEND][8]在这种特殊情况下 将类似于[SET][2]。

##### 返回值：

追加操作后的字符串长度。

##### 示例：

    coderknock> EXISTS mykey
    (integer) 0
    coderknock> APPEND mykey "Hello"
    (integer) 5
    coderknock> APPEND mykey " World"
    (integer) 11
    coderknock> GET mykey
    "Hello World"

### 字符串长度

#### STRLEN

> ** 自2.2.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法：STRLEN key

##### 说明：

返回存储在 key 中的字符串值的长度。key 保存非字符串值时返回错误。

##### 返回值：

key 对应字符串值的长度，key 不存在时返回 0。

##### 示例：

    coderknock> SET mykey "Hello world"
    "OK"
    coderknock> STRLEN mykey
    (integer) 11
    coderknock> STRLEN nonexisting
    (integer) 0

操作非字符串值时会报错：

    coderknock> SADD test a
    (integer) 1
    coderknock> TYPE test
    set
    coderknock> STRLEN test
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

一个中文占用3个字节：

    coderknock> SET hello "世界"
    OK
    coderknock> STRLEN hello
    (integer) 6

### 设置并返回原值

#### GETSET

> ** 自1.0.0可用。**

> ** 时间复杂度：**  O（1）

##### 语法：GETSET key value

##### 返回值：

返回 key 存储的旧值，当 key 不存在时返回 nil。

##### 说明：

以原子方式设置 key 到 value ，并返回存储的旧值 key。key 存在但不包含字符串时返回错误。

##### 示例：

    coderknock> SET mykey "Hello"
    "OK"
    coderknock> GETSET mykey "World"
    "Hello"
    coderknock> GET mykey
    "World"
    coderknock> GETSET test a
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

#### SETRANGE

> ** 自2.2.0起可用。**

> ** 时间复杂度：**  O（1），不计算复制新字符串所需的时间。通常，这个字符串非常小，所以摊销的复杂度是O（1）。否则，复杂度为O（M），M为值参数的长度。

##### 语法：SETRANGE key offset value

##### 说明：

重写存储在 key 中的字符串中一部分内容，offset 指定的是替换开始的位置。如果 offset 大于 key 中值的长度，则以0字节填充字符串以达到偏移量 offset 。不存在的 key 被视为空字符串。

##### 请注意，您可以设置的最大偏移量为2 ^ 29 -1（536870911），因为 Redis Strings 限制为512兆字节。如果您需要超出此尺寸，您可以使用多个键。

##### 返回值：

修改后的字符串长度

##### 示例：

    coderknock> SET key1 "Hello World"
    "OK"
    coderknock> SETRANGE key1 6 "Redis"
    (integer) 11
    coderknock> GET key1
    "Hello Redis"
    coderknock> SETRANGE test 5 asf
    (error) WRONGTYPE Operation against a key holding the wrong kind of value
    coderknock> SET str 123456789
    OK
    coderknock> SETRANGE str 20 abv
    (integer) 23
    coderknock> GET str
    "123456789\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00abv"

#### GETRANGE

> ** 自2.4.0起可用。**

> ** 时间复杂度：**  O（N）其中N是返回字符串的长度。复杂性最终由返回的长度确定，但是由于从现有字符串创建子字符串非常高效的，因此可以将短字符串视为 O（1）。

##### 语法：GETRANGE key start end

**警告**：此命令已重命名为 [GETRANGE][9]，在 Redis <= 2.0 的版本中调用 SUBSTR。

##### 说明：

返回 key 由 start ，end（两者均包含）偏移量确定的字符串值的子字符串。可以使用负偏移量来提供从字符串末尾开始的偏移量。-1 表示最后一个字符，-2 表示倒数第二个等等。

该功能通过将结果范围限制为字符串的实际长度来处理超出范围的请求。（end 超出长度则只是返回到最后一个字符）

##### 返回值：

截取后的字符串

##### 示例：

    coderknock> SET mykey "This is a string"
    "OK"
    coderknock> GETRANGE mykey 0 3
    "This"
    coderknock> GETRANGE mykey -3 -1
    "ing"
    coderknock> GETRANGE mykey 0 -1
    "This is a string"
    coderknock> GETRANGE mykey 10 100
    "string"
    coderknock> getrange mykey 100 1000
    ""

# 内部数据结构

Redis 中字符串的内部数据结构有 3 中：

* int：8 个字节的长整数
* embstr：小于等于 39 个字节的字符串 【存疑】
* raw：大于 39 个字节的字符串

Redis 会根据当前值的类型和长度决定使用哪种内部数据接口实现。

整数类型：

    coderknock> SET intKey 8653
    OK
    coderknock> OBJECT ENCODING intKey
    "int"
    coderknock> TYPE intKey
    string

短字符串：

    coderknock> SET embstrKey hello
    OK
    coderknock> OBJECT ENCODING embstrKey
    "embstr"
    coderknock> SET 39Key 123456789012345678901234567890123456789
    OK
    # 这里超过 8 字节，所以虽然是整数但是内部数据结构选择的是 embstr
    coderknock> STRLEN 39Key
    (integer) 39
    coderknock> OBJECT ENCODING 39Key
    "embstr"

长字符串：

    coderknock> SET rawKey asdfghjklzxcvbnmqwertyuiopqwertyuiopasdfaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
    OK
    coderknock> OBJECT ENCODING 40Key
    "raw"

# 典型应用场景

## 缓存功能

![%u67B6%u6784%u7B80%u6790][10]



架构简析

## 计数

## Session共享

在一个集群环境中，Web 服务器有多个，受负载均衡服务器管控，如果每个服务器分别存储各自的 Session 就会导致宕机或者切换服务器后 Session 丢失，用户需要重新登录。  
其中一种解决方案就是将所有 Web 服务器的 Session 放到 Redis 中，从而实现无论具体服务器怎么切换 Session 都不会丢失。

## 限速

类似于计数功能，只是还利用了过期时间这一特性。

[0]: http://www.jianshu.com/u/2de721a368d3
[1]: https://redis.io/topics/distlock
[2]: https://redis.io/commands/set
[3]: https://redis.io/commands/setex
[4]: https://redis.io/commands/setnx
[5]: https://redis.io/commands/psetex
[6]: https://redis.io/commands/mset
[7]: https://redis.io/commands/msetnx
[8]: https://redis.io/commands/append
[9]: https://redis.io/commands/getrange
[10]: https://upload-images.jianshu.io/upload_images/1284956-5662625a1dc0179f.png
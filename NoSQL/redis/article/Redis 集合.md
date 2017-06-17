# Redis 集合

作者  [三产][0] 已关注 2017.06.16 16:46  字数 2400  阅读 1 评论 0 喜欢 0

# 简介

集合（set）类型也是用来保存多个的字符串元素，但和列表类型不一样的是，集合中不允许有重复元素，并且集合中的元素是无序的，不能通过索引下标获取元素。一个集合最多可以存储 $2^{32}-1$ 个元素。Redis除了支持集合内的增删改查，同时还支持多个集合取交集、并集、差集，合理地使用好集合类型，能在实际开发中解决很多实际问题。

# 命令

## 集合内操作

### 添加元素

#### SADD

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是被添加的元素的数量。

##### 语法：**SADD key member [member ...]**

##### 说明：

将一个或多个 member 元素加入到集合 key 当中，已经存在于集合的 member 元素将被忽略。

假如 key 不存在，则创建一个只包含 member 元素作成员的集合。

当 key 不是集合类型时，返回一个错误。

**在 Redis 2.4 版本以前的 SADD 命令，都只接受单个 member 值。**

##### 返回值：

被添加到集合中的新元素的数量，不包括被忽略的元素。

##### 示例：

    coderknock> SADD saddTest add1 add2 add3
    (integer) 3
    # 查看集合中所有元素（该命令之后会介绍）
    coderknock> SMEMBERS saddTest
    1) "add1"
    2) "add3"
    3) "add2"
    # 添加四个元素，其中两个与之前添加过的元素重复
    coderknock> SADD saddTest add1 add4 add3 add5
    (integer) 2 # 只成功添加两个元素（重复的不再添加 ）
    coderknock> SMEMBERS saddTest
    1) "add3"
    2) "add2"
    3) "add4"
    4) "add5"
    5) "add1"

### 删除元素

#### SREM

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是被添加的元素的数量。

##### 语法：**SREM key member [member ...]**

##### 说明：

移除集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。

当 key 不是集合类型，返回一个错误。

**在 Redis 2.4 版本以前的 SREM 命令，都只接受单个 member 值。**

##### 返回值：

被成功移除的元素的数量，不包括被忽略的元素。

##### 示例：

    # 删除三个元素，其中 sadd7 不存在
    coderknock> SREM saddTest add3 add5 add7
    (integer) 2
    coderknock> SMEMBERS saddTest
    1) "add4"
    2) "add1"
    3) "add2"
    # 执行该操作的不是集合元素
    coderknock> SREM embstrKey a
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

### 计算元素个数

#### SCARD

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**SCARD key**

##### 说明：

返回集合 key 的基数(集合中元素的数量)。

##### 返回值：

集合的基数。

当 key 不存在时，返回 0 。

##### 示例：

    coderknock> SCARD saddTest
    (integer) 3
    # key 不存在返回 0
    coderknock> SCARD add
    (integer) 0
    # key 类型不是集合时会报错
    coderknock> SCARD embstrKey
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

SCARD 命令不会遍历集合所有元素，而是直接使用 Redis 内部的变量来获取集合长度。

### 判断元素是否存在集合中

#### SISMEMBER

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**SISMEMBER key member**

##### 说明：

判断 member 元素是否集合 key 的成员。

##### 返回值：

如果 member 元素是集合的成员，返回 1 。

如果 member 元素不是集合的成员，或 key 不存在，返回 0 。

##### 示例：

    coderknock> SISMEMBER saddTest add1
    (integer) 1
    #  add7  元素不存在
    coderknock> SISMEMBER saddTest add7
    (integer) 0
    # key 不存在
    coderknock> SISMEMBER nonSet a
    (integer) 0
    # key 类型不是集合
    coderknock> SISMEMBER embstrKey a
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

### 随机从集合中返回指定个数的元素

#### SRANDMEMBER

>** 自1.0.0可用。**

>** 时间复杂度：** 只提供 > key>  参数时为 O(1) 。

> 如果提供了  count  参数，那么为 O(N) ，N 为返回数组的元素个数。

##### 语法：**SRANDMEMBER key [count]**

##### 说明：

如果命令执行时，只提供了 key 参数，那么返回集合中的一个随机元素。

从 Redis 2.6 版本开始， SRANDMEMBER 命令接受可选的 count 参数：

* 如果 count 为正数，且小于集合基数，那么命令返回一个包含 count 个元素的数组，数组中的元素**各不相同**。如果 count 大于等于集合基数，那么返回整个集合。
* 如果 count 为负数，那么命令返回一个数组，数组中的元素**可能会重复出现多次**，而数组的长度为 count 的绝对值。

该操作和 SPOP 相似，但 SPOP 将随机元素从集合中移除并返回，而 SRANDMEMBER 则仅仅返回随机元素，而不对集合进行任何改动。

##### 返回值：

只提供 key 参数时，返回一个元素；如果集合为空，返回 nil 。

如果提供了 count 参数，那么返回一个数组；如果集合为空，返回空数组。

##### 示例：

    coderknock> SADD languageSet java go python kotlin c lua javascript
    
    coderknock> SRANDMEMBER languageSet
    "kotlin"
    coderknock> SRANDMEMBER languageSet
    "go"
    coderknock> SRANDMEMBER languageSet 3
    1) "go"
    2) "python"
    3) "javascript"
    coderknock> SRANDMEMBER languageSet 3
    1) "c"
    2) "java"
    3) "lua"
    # count 超出 长度时返回所有的元素
    coderknock>  SRANDMEMBER languageSet 8
    1) "kotlin"
    2) "c"
    3) "java"
    4) "go"
    5) "lua"
    6) "python"
    7) "javascript"
    coderknock> SRANDMEMBER languageSet -2
    1) "python"
    2) "kotlin"
    # key 不存在时返回空集合
    coderknock> SRANDMEMBER nonKey 2
    (empty list or set)
    coderknock>  SRANDMEMBER nonKey
    (nil)

### 从集合中随机弹出元素

#### SPOP

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**SPOP key [count]**

##### 说明：

移除并返回集合中的一个随机元素。

如果只想获取一个随机元素，但不想该元素从集合中被移除的话，可以使用 SRANDMEMBER 命令。

从 Redis 3.2 版本开始， SPOP 命令接受可选的 count 参数

##### 返回值：

被移除的随机元素。

当 key 不存在或 key 是空集时，返回 nil 。

如果提供了 count 参数，那么返回一个数组；如果集合为空，返回空数组。

##### 示例：

    coderknock> SMEMBERS languageSet
    1) "kotlin"
    2) "c"
    3) "java"
    4) "go"
    5) "javascript"
    6) "python"
    7) "lua"
    # 该命令不支持付费作为参数
    coderknock> SPOP languageSet -2
    (error) ERR index out of range
    coderknock> SPOP languageSet 2
    1) "lua"
    2) "python"
    coderknock> SMEMBERS languageSet
    1) "kotlin"
    2) "c"
    3) "java"
    4) "go"
    5) "javascript"
    coderknock> SPOP nonSet 2
    (empty list or set)
    coderknock> SPOP nonSet
    (nil)

srandmember 和 spop 都是随机从集合选出元素，两者不同的是 spop 命令执行后，元素会从集合中删除，而 srandmember 不会。

### 获取所有元素

#### SMEMBERS

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  为集合的基数。

##### 语法：**SMEMBERS key**

##### 说明：

返回集合 key 中的所有成员。

不存在的 key 被视为空集合。

这与运行只有 key 参数的 SINTER 命令效果相同。

##### 返回值：

集合中的所有成员，key 不存在返回空集合。

##### 示例：

    coderknock> SISMEMBER saddTest add1
    (integer) 1
    #  add7  元素不存在
    coderknock> SISMEMBER saddTest add7
    (integer) 0
    # key 不存在
    coderknock> SISMEMBER nonSet a
    (integer) 0
    # key 类型不是集合
    coderknock> SISMEMBER embstrKey a
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

smembers 和 lrange、hgetall 都属于比较重的命令，如果元素过多存在阻塞Redis的可能性，这时候可以使用 sscan （在 Redis 概览 中有介绍）来完成。

## 集合间操作

### 交集

#### SINTER

>** 自1.0.0可用。**

>** 时间复杂度：** 最差情况：O(N * M)，  N  为给定集合当中基数最小的集合，  M  为给定集合的个数。

##### 语法：**SINTER key [key ...]**

##### 说明：

返回一个集合的全部成员，该集合是所有给定集合的交集。

不存在的 key 被视为空集。

当给定集合当中有一个空集时，结果也为空集(根据集合运算定律)。

##### 返回值：

交集成员的列表。

##### 示例：

    coderknock> SINTER languageSet
    1) "kotlin"
    2) "c"
    3) "java"
    4) "go"
    5) "javascript"
    coderknock> SADD loveLanguageSet java c# c++ kotlin
    (integer) 4
    coderknock> SINTER languageSet loveLanguageSet
    1) "kotlin"
    2) "java"
    # 不存在的 key 被视为空集
    coderknock> SINTER languageSet nonSet
    (empty list or set)

### 并集

#### SUNION

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是所有给定集合的成员数量之和。

##### 语法：**SUNION key [key ...]**

##### 说明：

返回一个集合的全部成员，该集合是所有给定集合的并集。

不存在的 key 被视为空集。

##### 返回值：

并集成员的列表。

##### 示例：

    coderknock> SUNION languageSet nonSet loveLanguageSet
    1) "kotlin"
    2) "c"
    3) "c++"
    4) "c#"
    5) "java"
    6) "go"
    7) "javascript"

### 差集

#### SDIFF

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是所有给定集合的成员数量之和。

##### 语法：**SDIFF key [key ...]**

##### 说明：

返回一个集合的全部成员，该集合是所有给定集合之间的差集。

不存在的 key 被视为空集。

##### 返回值：

一个包含差集成员的列表。

##### 示例：

    coderknock> SMEMBERS languageSet
    1) "kotlin"
    2) "c"
    3) "java"
    4) "go"
    5) "javascript"
    coderknock> SMEMBERS loveLanguageSet
    1) "kotlin"
    2) "c++"
    3) "c#"
    4) "java"
    # 一个集合与不存在 key 或者空集合的差集还是该集合本身
    coderknock> SDIFF languageSet nonSet
    1) "c"
    2) "kotlin"
    3) "go"
    4) "java"
    5) "javascript"
    coderknock> SDIFF languageSet loveLanguageSet
    1) "c"
    2) "javascript"
    3) "go"

### 存储集合运算结果

SINTER 交集、SUNION 并集、SDIFF 差集在集合较多时运行比较耗时，所以 Redis 提供了原命令 +STORE 的命令可以用来将运算结果进行保存。

#### SINTERSTORE

>** 自1.0.0可用。**

>** 时间复杂度：** O(N * M)，  N  为给定集合当中基数最小的集合，  M  为给定集合的个数。

##### 语法：**SINTERSTORE destination key [key ...]**

##### 说明：

这个命令类似于 SINTER 命令，但它将结果保存到 destination 集合，而不是简单地返回结果集。

如果 destination 集合已经存在，则将其覆盖。

destination 可以是 key 本身。

##### 返回值：

结果集中的成员数量。

##### 示例：

    coderknock> SINTERSTORE sinterStoreTest languageSet loveLanguageSet
    (integer) 2
    coderknock> SMEMBERS sinterStoreTest
    1) "kotlin"
    2) "java"

#### SUNIONSTORE

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是所有给定集合的成员数量之和。

##### 语法：**SUNIONSTORE destination key [key ...]**

##### 说明：

这个命令类似于 SUNION 命令，但它将结果保存到 destination 集合，而不是简单地返回结果集。

如果 destination 已经存在，则将其覆盖。

destination 可以是 key 本身。

##### 返回值：

结果集中的成员数量。

##### 示例：

    coderknock> SUNIONSTORE sunionStoryTest languageSet nonSet loveLanguageSet
    (integer) 7
    coderknock> SMEMBERS sunionStoryTest
    1) "kotlin"
    2) "c"
    3) "c++"
    4) "c#"
    5) "java"
    6) "go"
    7) "javascript"

#### SDIFFSTORE

>** 自1.0.0可用。**

>** 时间复杂度：** O(N)，  N  是所有给定集合的成员数量之和。

##### 语法：**SDIFFSTORE destination key [key ...]**

##### 说明：

这个命令的作用和 SDIFF 类似，但它将结果保存到 destination 集合，而不是简单地返回结果集。

如果 destination 集合已经存在，则将其覆盖。

destination 可以是 key 本身。

##### 返回值：

结果集中的元素数量。

##### 示例：

    coderknock> SDIFFSTORE sdiffTest languageSet loveLanguageSet
    (integer) 3
    coderknock> SMEMBERS sdiffTest
    1) "c"
    2) "javascript"
    3) "go"

# 内部编码

集合类型的内部编码有两种：

* intset（整数集合）：当集合中的元素都是整数且元素个数小于 set-maxintset-entries 配置（默认512个）时，Redis 会选用 intset 来作为集合的内部实现，从而减少内存的使用。

* hashtable（哈希表）：当集合类型无法满足 intset 的条件时，Redis 会使用 hashtable 作为集合的内部实现。
* 当元素个数较少且都为整数时，内部编码为 intset：

    coderknock> SADD testIntset 1 2 3 4 5 6
    (integer) 6
    coderknock> OBJECT ENCODING testIntset
    "intset"

1. 当元素个数超过512个，内部编码变为 hashtable：【这里使用 python 进行测试】

    import redis
    
    r = redis.StrictRedis(host='127.0.0.1', password='admin123', port=6379, db=0)
    num = 512
    key = "intListTest" + str(num)
    r.delete(key)
    for i in range(num):
    r.sadd(key, i)
    
    # 可以使用这个命令查询内部编码
    print(key)
    print(r.scard(key))
    print(r.object("ENCODING", key))

输出结果：

    intListTest512
    512
    b'intset'

我们将 num 改为 513 输出结果：

    intListTest512
    512
    b'intset'

1. 当某个元素不为整数时，内部编码也会变为 hashtable：


    coderknock> SADD testIntset a
    (integer) 1
    coderknock>  OBJECT ENCODING testIntset
    "hashtable"

# 使用场景

* sdd=Tagging（标签）
* spop/srandmember=Random item（生成随机数，比如抽奖）
* sadd+sinter=Social Graph（社交需求）

[0]: http://www.jianshu.com/u/2de721a368d3
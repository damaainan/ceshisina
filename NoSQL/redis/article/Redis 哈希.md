# Redis 哈希

作者  [三产][0] 已关注 2017.06.16 10:10  字数 2156  阅读 0 评论 0 喜欢 0

# 哈希

在 Redis 中，哈希类型是指键值本身又是一个键值对结果，其结构表示为：

Redis 结构：

key -> value在哈希中 上述的 value 结构：

field -> value使用 json 表示：

{key:{field1:value1,field2:value2,...fieldN:valueN}}

Redis 哈希

## 常用命令

### 设置值

#### HSET

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法：HSET key field value

##### 说明：

将哈希表 key 中的域 field 的值设为 value 。

如果 key 不存在，一个新的哈希表被创建并进行 [HSET][1] 操作。

如果域 field 已经存在于哈希表中，旧值将被覆盖。

##### 返回值：

如果 field 是哈希表中的一个新建域，并且值设置成功，返回 1 。

如果哈希表中域 field 已经存在且旧值已被新值覆盖，返回 0 。

##### 示例：

    coderknock> HSET user:1 name sanchan
    (integer) 1
    coderknock> HSET user:1 age 18
    (integer) 1
    coderknock> HSET user:1 age 24
    (integer) 0

此外Redis提供了hsetnx命令，它们的关系就像 SET 和 SETNX 命令一样，只不过作用域由 key 变为 field 。

### 获取值

#### HGET

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法：HSET key field value

##### 说明：

返回哈希表 key 中给定 field 的值。

##### 返回值：

给定 field 的值。

当 给定 field 不存在 或 给定 key 不存在时，返回 nil 。

##### 示例：

    coderknock> HGET user:1 name
    "sanchan"
    coderknock> HGET user:1 sex
    (nil)

#### HGETALL

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（N）， > N>  为哈希表的大小。

##### 语法：HSET key field value

##### 说明：

返回哈希表 key 中，所有的 field 和 value 。

在返回值里，紧跟每个域名 (field name) 之后是域的值 (value) ，所以返回值的长度是哈希表大小的两倍。

##### 返回值：

以列表形式返回哈希表的域和域的值。

若 key 不存在，返回空列表。

##### 示例：

    coderknock> HGETALL user:1
    1) "name"  # field
    2) "sanchan" # value
    3) "age" #field
    4) "24" # value
    #若 key 不存在，返回空列表
    coderknock> HGETALL user:2
    (empty list or set)

##### 注意！：

**在使用 HGETALL 时，如果哈希元素个数比较多，会存在阻塞 Redis 的可能。**  
**如果开发人员只需要获取部分 field，可以使用 HMGET（后面有介绍），如果一定要获取全部 field-value ，可以使用 HSCAN命令，该命令会渐进式遍历哈希类型。**

#### HGETALL

> ** 自2.8.0起可用。**

> ** 时间复杂度：**  每次调用 O（1）。O（N）用于完整的迭代，包括足够的命令调用以使光标返回到0。N是 集合内的元素数量。

##### 语法：HSCAN key cursor [MATCH pattern] [COUNT count]

##### 说明：

获取哈希中所有的 field-value

##### 参加 SCAN（本系列中有单独一篇文章介绍）

### 删除field


#### HDEL

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（N）， N 为要删除的域的数量。

##### 语法： HDEL key field [field ...]

##### 说明：

删除哈希表 key 中的一个或多个指定 field ，不存在的 field 将被忽略。

在 Redis 2.4 以下的版本里， [HDEL][2] 每次只能删除单个域，如果你需要在一个原子时间内删除多个域，请将命令包含在 [_MULTI_][3] / [_EXEC_][4]块内。

在 Redis 2.4 （包含）及以上版本中可以一次传入多个 fiele。

##### 返回值：

被成功移除的 field 的数量，不包括被忽略的 field。如果 key 不存在，则将其视为空哈希，返回 0。

##### 示例：

    coderknock> HDEL user:1 name
    (integer) 1
    coderknock> HGETALL user:1
    1) "age"
    2) "24"
    # 删除的 key 不存在
    coderknock> HDEL user:2 name
    (integer) 0
    coderknock> HGETALL user:3
     1) "name"
     2) "coderknock"
     3) "user"
     4) "sanchan"
     5) "age"
     6) "24"
     7) "website"
     8) "http://www.coderknock.com"
     9) "name2"
    10) "test"
    11) "age2"
    12) "1"
    # 删除多个 field 其中 sex 不存在
    coderknock> HDEL user:3 name2  sex age2
    (integer) 2
    coderknock> HGETALL user:3
    1) "name"
    2) "coderknock"
    3) "user"
    4) "sanchan"
    5) "age"
    6) "24"
    7) "website"
    8) "http://www.coderknock.com"
    # 当一个 key 中所有的 field 被删除则改 key 也会被删除
    coderknock> HDEL user:1 age
    (integer) 1
    coderknock> EXISTS user:1
    (integer) 0

### 计算 field 个数

#### HLEN

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法： HLEN key

##### 说明：

返回哈希表 key 中域的数量。

##### 返回值：

哈希表中域的数量。

当 key 不存在时，返回 0 。

##### 示例：

    coderknock> HLEN user:3
    (integer) 4
    coderknock> HLEN user:1
    (integer) 0

### 批量处理

#### HGET

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（N），  N  为要获取的域的数量。

##### 语法： **HMGET key field [field ...]**
##### 说明：

返回哈希表 key 中，一个或多个给定域的值。

如果给定的域不存在于哈希表，那么返回一个 nil 值。

因为不存在的 key 被当作一个空哈希表来处理，所以对一个不存在的 key 进行 [HMGET][5] 操作将返回一个只带有 nil 值的表。

##### 返回值：

一个包含多个给定域的关联值的表，表值的排列顺序和给定域参数的请求顺序一样。

key 不存在则返回 nil 。

##### 示例：

    coderknock> HMGET user:3 website name age name2 user
    1) "http://www.coderknock.com"
    2) "coderknock"
    3) "24"
    4) (nil)   # 不存在的域返回nil值
    5) "sanchan"
    # key 不存在 返回 nil
    coderknock> HMGET user:4 name
    1) (nil)

#### HMSET

> ** 自2.0.0起可用。**

> ** 时间复杂度：** O（N），  N  为  field-value  对的数量。

##### 语法：**HMSET key field value [field value ...]**
##### 说明：

同时将多个 field-value (域-值)对设置到哈希表 key 中。

此命令会覆盖哈希表中已存在的域。

如果 key 不存在，一个空哈希表被创建并执行 [HMSET][6] 操作。

##### 返回值：

如果命令执行成功，返回 OK 。

当 key 不是哈希表 (hash) 类型时，返回一个错误。

##### 示例：

    # embstrKey 不是哈希，所以会报错
    coderknock> HMSET embstrKey name 1
    (error) WRONGTYPE Operation against a key holding the wrong kind of value
    coderknock> HMSET user:5 name sanchan website https://www.coderknock.com
    OK
    coderknock> HGETALL user:5
    1) "name"
    2) "sanchan"
    3) "website"
    4) "https://www.coderknock.com"
    coderknock> HMSET user:5 user sanchan name coverSanchan
    OK
    coderknock> HGETALL user:5
    1) "name"
    2) "coverSanchan" # name 被覆盖
    3) "website"
    4) "https://www.coderknock.com"
    5) "user"
    6) "sanchan"

### 判断 field 是否存在

#### HEXISTS

> ** 自2.0.0起可用。**

> ** 时间复杂度：**  O（1）

##### 语法： **HEXISTS key field**
##### 说明：

查看哈希表 key 中，给定域 field 是否存在。

##### 返回值：

如果哈希表含有给定域，返回 1 。

如果哈希表不含有给定域，或 key 不存在，返回 0 。

##### 示例：

    coderknock> HEXISTS user:5 name2
    (integer) 0
    coderknock> HEXISTS user:5 name
    (integer) 1
    # embstrKey 不是哈希所以会报错
    coderknock> HEXISTS embstrKey name
    (error) WRONGTYPE Operation against a key holding the wrong kind of value
    # 不存在 a 这个 key
    coderknock> HEXISTS a name
    (integer) 0

### 获取所有 field
#### HKEYS

> ** 自2.0.0起可用。**

> ** 时间复杂度：** O（N），  N  为哈希表的大小。

##### 语法： **HKEYS key**
##### 说明：

返回哈希表 key 中的所有域。

##### 返回值：

一个包含哈希表中所有域的表。

当 key 不存在时，返回一个空表。

##### 示例：

    coderknock> HKEYS user:5
    1) "name"
    2) "website"
    3) "user"
    # embstrKey 不是哈希
    coderknock> HKEYS embstrKey
    (error) WRONGTYPE Operation against a key holding the wrong kind of value
    # a 这个 key 不存在
    coderknock> HKEYS a
    (empty list or set)

### 获取所有 value

#### HVALS

> ** 自2.0.0起可用。**

> ** 时间复杂度：** O（N），  N  为哈希表的大小。

##### 语法：**HVALS key**
##### 说明：

返回哈希表 key 中所有域的值。

##### 返回值：

一个包含哈希表中所有值的表。

当 key 不存在时，返回一个空表。

##### 示例：

    coderknock> HVALS user:5
    1) "coverSanchan"
    2) "https://www.coderknock.com"
    # embstrKey 不是哈希
    coderknock> HVALS embstrKey
    (error) WRONGTYPE Operation against a key holding the wrong kind of value
    # a 这个 key 不存在
    coderknock> HVALS a
    (empty list or set)

### 计数

#### HINCRBY HINCRBYFLOAT

> ** 自2.0.0起可用。**

> ** 时间复杂度：** O（1）

##### 语法：**HINCRBY key field increment**

##### 说明：

对哈希表 key 中的域 field 的值进行增量 increment 操作，类似 字符串操作中的 INCR。

增量也可以为负数，相当于对给定域进行减法操作。

如果 key 不存在，一个新的哈希表被创建并执行 [HINCRBY][7] 命令。

如果域 field 不存在，那么在执行命令前，域的值被初始化为 0 。

对一个储存字符串值的域 field 执行 [HINCRBY][7] 命令将造成一个错误。

本操作的值被限制在 64 位(bit)有符号数字表示之内。

##### 返回值：

执行 [HINCRBY][7] 命令之后，哈希表 key 中域 field 的值。

##### 示例：

    # increment 为正数
    coderknock> HEXISTS coderknockCounter view_count # 对空域进行设置
    (integer) 0
    coderknock> HINCRBY coderknockCounter view_count 1000
    (integer) 1000
    coderknock> HGETALL coderknockCounter
    1) "view_count"
    2) "1000"
    
    # increment 为负数
    coderknock> HINCRBY coderknockCounter view_count -1100
    (integer) -100
    coderknock> HGETALL coderknockCounter
    1) "view_count"
    2) "-100"
    coderknock>
    
    # 尝试对字符串值的域执行HINCRBY命令
    coderknock> HSET strHash strField sanchan       # 设定一个字符串值
    (integer) 1
    coderknock> HGETALL strHash
    1) "strField"
    2) "sanchan"
    coderknock> HINCRBY strHash strField 1          # 命令执行失败，错误。
    (error) ERR hash value is not an integer
    coderknock> HGETALL strHash                     # 原值不变
    1) "strField"
    2) "sanchan"

HINCRBY 和 HINCRBYFLOAT，就像 INCRBY 和 INCRBYFLOAT 命令一样，但是它们的作  
用域是 filed。

### 计算 value 的字符串长度

#### HSTRLEN

> ** 自3.2.0起可用。**

> ** 时间复杂度：** O（1）

##### 语法：**HVALS key**

##### 说明：

返回key 中 field 的值的字符串长度。如果 key 或 field 不存在，则返回0。

##### 返回值：

返回key 中 field 的值的字符串长度。如果 key 或 field 不存在，则返回0。

##### 示例：

    coderknock> HGETALL user:5
    1) "name"
    2) "coverSanchan"
    3) "website"
    4) "https://www.coderknock.com"
    5) "user"
    6) "sanchan"
    coderknock> HSTRLEN user:5 name
    (integer) 12
    # 查询哈希中不存在的 field
    coderknock> HSTRLEN user:5 non
    (integer) 0
    # 查询不存在的 key
    coderknock> HSTRLEN a a
    (integer) 0
    # 查询非哈希 key
    coderknock> HSTRLEN embstrKey name
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

## 内部编码

哈希类型的内部编码有两种：

* ziplist（压缩列表）：当哈希类型元素个数小于 hash-max-ziplist-entries 配置（默认512个）、同时所有值都小于 hash-max-ziplist-value 配置（默认64字节）时，Redis 会使用 ziplist 作为哈希的内部实现，ziplist 使用更加紧凑的结构实现多个元素的连续存储，所以比 hashtable 更加节省内存。
* hashtable（哈希表）：当哈希类型无法满足 ziplist 的条件时，Redis 会使用 hashtable 作为哈希的内部实现，因为此时 ziplist 的读写效率会下降，而 hashtable 的读写时间复杂度为O（1）。

### 示例

#### field 数量较少且没有较大的 value 时，内部编码为 ziplist

    127.0.0.1:6379> HMSET ziplistHash k1 v1  k2 v2
    OK
    127.0.0.1:6379> OBJECT ENCODING ziplistHash
    "ziplist"

#### 当有 value 大于 64 字节，内部编码会由 ziplist 变为 hashtable

    127.0.0.1:6379> HSET ziplistHash k1 qwertyuiopasdfaaaaaaaaaaaaaaaaaaaddddddddddddddddddddsssssssssssffffffffffffffffgggggggggggg
    (integer) 0
    127.0.0.1:6379> OBJECT ENCODING ziplistHash
    "hashtable"

#### 当 field 个数超过 512 时，内部编码也会由 ziplist 变为 hashtable

这里我们使用 python 进行批量操作（与手动插入效果相同，只是数据量较大手动插入比较麻烦，使用其他编程语言也可） pip install redis 需要先安装对应库。

    import redis
    
    r = redis.StrictRedis(host='127.0.0.1', password='admin123', port=6379, db=0)
    
    dict = {}
    for i in range(513):
        dict["field" + str(i)] = "value " + str(i)
    r.hmset("hashtableHash", dict)
    # 也可以使用这个命令查询内部编码 r.object("ENCODING","hashtableHash")

查询内部编码

    127.0.0.1:6379> OBJECT ENCODING hashtableHash
    "hashtable"

使用上面的 python 修改 range 长度发现：

    127.0.0.1:6379> HLEN hashtableHash2
    (integer) 511
    127.0.0.1:6379> OBJECT ENCODING hashtableHash2
    "ziplist"
    127.0.0.1:6379> HLEN hashtableHash1
    (integer) 512
    127.0.0.1:6379> OBJECT ENCODING hashtableHash1
    "ziplist"

[0]: http://www.jianshu.com/u/2de721a368d3
[1]: http://doc.redisfans.com/hash/hset.html#hset
[2]: http://doc.redisfans.com/hash/hdel.html#hdel
[3]: http://doc.redisfans.com/transaction/multi.html#multi
[4]: http://doc.redisfans.com/transaction/exec.html#exec
[5]: http://doc.redisfans.com/hash/hmget.html#hmget
[6]: http://doc.redisfans.com/hash/hmset.html#hmset
[7]: http://doc.redisfans.com/hash/hincrby.html#hincrby
# Redis 列表

作者  [三产][0] 已关注 2017.06.16 16:44  字数 4180  阅读 2 评论 0 喜欢 0

# 简介

列表可以存储 **多个** **有序** **可重复** 的字符串。列表中的每个字符串称为元素(element)，一个列表最多可以存储2 ^ 32 - 1个元素。在Redis中，可以对列表两端插入(push)和弹出(pop)，还可以获取指定范围的元素列表、获取指定索引下标的元素等。列表是一种比较灵活的数据结构，它可以充当栈和队列的角色，在实际开发上有很多应用场景。 

# 命令

## 添加操作

### 从右侧插入元素

#### RPUSH

>** 自1.0.0可用。**

>** 时间复杂度：**  O(1)

##### 语法：**RPUSH key value [value ...]**


##### 说明：

将一个或多个值 value 插入到列表 key 的表尾(最右侧)。

如果有多个 value 值，那么各个 value 值按从左到右的顺序依次插入到表尾：比如对一个空列表 mylist 执行 RPUSH mylist a b c ，得出的结果列表为 a b c ，等同于执行命令 RPUSH mylist a 、 RPUSH mylist b 、 RPUSH mylist c 。

如果 key 不存在，一个空列表会被创建并执行 RPUSH 操作。

当 key 存在但不是列表类型时，返回一个错误。

**在 Redis 2.4 版本以前的 RPUSH 命令，都只接受单个 value 值。**

##### 返回值：

执行 RPUSH 操作后，表的长度。

##### 示例：

    # 一次添加多个元素
    coderknock> RPUSH webSiteList https://coderknock.com https://my.oschina.net/coderknock/blog
    (integer) 2
    # 添加一个重复的元素
    coderknock>  RPUSH webSiteList   https://coderknock.com
    (integer) 3
    # 查看所有元素
    coderknock> LRANGE webSiteList 0 -1
    1) "https://coderknock.com"
    2) "https://my.oschina.net/coderknock/blog"
    3) " https://coderknock.com"

### 从左侧插入元素

#### LPUSH

>** 自1.0.0可用。**

>** 时间复杂度：**  O(1)

##### 语法：LPUSH key value [value ...]

##### 说明：

将一个或多个值 value 插入到列表 key 的表头

如果有多个 value 值，那么各个 value 值按从左到右的顺序依次插入到表头： 比如说，对空列表 mylist 执行命令 LPUSH mylist a b c ，列表的值将是 c b a ，这等同于原子性地执行 LPUSH mylist a 、 LPUSH mylist b 和 LPUSH mylist c 三个命令。

如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作。

当 key 存在但不是列表类型时，返回一个错误。

**在Redis 2.4版本以前的 LPUSH 命令，都只接受单个 value 值。**

##### 返回值：

执行 LPUSH 命令后，列表的长度。

##### 示例：

    coderknock> LPUSH programmingLanguage java python go js
    coderknock> LRANGE programmingLanguage 0 -1
    1) "js"
    2) "go"
    3) "python"
    4) "java"
    # LPUSH 与 RPUSH 用法相似，只是从左侧(头)插入而已
    coderknock> LPUSH webSiteList http://www.coderknock.com
    (integer) 4
    coderknock> LRANGE webSiteList 0 -1
    1) "http://www.coderknock.com"
    2) "https://coderknock.com"
    3) "https://my.oschina.net/coderknock/blog"
    4) "https://my.oschina.net/coderknock/blog"

### 向某个元素前或者后插入元素

#### LINSERT

>** 自2.2.0起可用。**

>** 时间复杂度：** O(N)，  N  为寻找  pivot  过程中经过的元素数量。

##### 语法： LINSERT key BEFORE|AFTER pivot value 【这里的可选参数一次只能用一个】

##### 说明：

将值 value 插入到列表 key 当中，位于值 pivot 之前或之后。

当 pivot 不存在于列表 key 时，不执行任何操作。

当 key 不存在时， key 被视为空列表，不执行任何操作。

如果 key 不是列表类型，返回一个错误。

##### 返回值：

如果命令执行成功，返回插入操作完成之后，列表的长度。

如果没有找到 pivot ，返回 -1 。

如果 key 不存在或为空列表，返回 0 。

##### 示例：

    coderknock> LPUSH programmingLanguage java ruby
    (integer) 6
    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "java"
    3) "js"
    4) "go"
    5) "python"
    6) "java"
    # 可以看到当有重复的元素时，只会插入最近的元素
    coderknock> LINSERT programmingLanguage BEFORE java javaBefter
    (integer) 7
    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "js"
    5) "go"
    6) "python"
    7) "java"
    coderknock>  LINSERT programmingLanguage AFTER java javaAfter
    (integer) 8
    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"
    6) "go"
    7) "python"
    8) "java"
    # 插入到一个不存在的元素
    coderknock>  LINSERT programmingLanguage AFTER c cAfter
    (integer) -1
    # 对一个不存在的 key 进行操作相当于没有进行任何操作
    coderknock> LINSERT noList BEFORE a a
    (integer) 0
    coderknock> EXISTS noList
    (integer) 0

## 查找

### 获取指定范围内的元素列表

#### LRANGE

>** 自1.0.0起可用。**

>** 时间复杂度：** O()S+N)，  S  为偏移量  start  ，  N  为指定区间内元素的数量。

##### 语法： LRANGE key start stop

##### 说明：

返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定。

下标(index)参数 start 和 stop 都以 0 为底，也就是说，以 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。

你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。

**注意 LRANGE 命令和编程语言中区间函数的区别**

假如你有一个包含一百个元素的列表，对该列表执行 LRANGE list 0 10 ，结果是一个包含11个元素的列表，这表明 stop 下标也在 LRANGE 命令的取值范围之内(闭区间)，这和某些语言的区间函数可能不一致，比如 Ruby 的 Range.new 、 Array#slice 和 Python 的 range() 函数。

**超出范围的下标**

超出范围的下标值不会引起错误。

如果 start 下标比列表的最大下标 end ( LLEN list 减去 1 )还要大，那么 LRANGE 返回一个空列表。

如果 stop 下标比 end 下标还要大，Redis将 stop 的值设置为 end 。

##### 返回值：

##### 示例：

    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"
    6) "go"
    7) "python"
    8) "java"
    coderknock> LRANGE programmingLanguage 0 -2
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"
    6) "go"
    7) "python"
    coderknock> LRANGE programmingLanguage  0 4
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"

### 获取列表指定索引下标的元素

#### LINDEX

>** 自1.0.0起可用。**

>** 时间复杂度：**  O(N)，  N  为到达下标  index  过程中经过的元素数量。

> 因此，对列表的头元素和尾元素执行  LINDEX  命令，复杂度为O(1)。

##### 语法：LINDEX key index

##### 说明：

返回列表 key 中，下标为 index 的元素。

下标(index)参数 start 和 stop 都以 0 为底，也就是说，以 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。

你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。

如果 key 不是列表类型，返回一个错误。

##### 返回值：

列表中下标为 index 的元素。

如果 index 参数的值不在列表的区间范围内(out of range)，返回 nil 。

##### 示例：

    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"
    6) "go"
    7) "python"
    8) "java"
    # 从 0 开始 所以 返回的是 javaAfter
    coderknock> LINDEX programmingLanguage 3
    "javaAfter"
    coderknock>  LINDEX programmingLanguage -3
    "go"
    # 如果 index 参数超出列表范围
    coderknock> LINDEX programmingLanguage 8
    (nil)
    # 如果 key 不存在
    coderknock> LINDEX a 0
    (nil)
    # 如果 index 参数不是一个整数会报错
    coderknock> LINDEX programmingLanguage java
    (error) ERR value is not an integer or out of range
    coderknock> LINDEX programmingLanguage 0.1
    (error) ERR value is not an integer or out of range
    # embstrKey 不是列表类型
    coderknock>  LINDEX embstrKey 0
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

### 获取列表长度

#### LLEN

>** 自1.0.0起可用。**

>** 时间复杂度：**  O(N)，  N  为数据库中  key  的数量。

##### 语法：**LLEN key**

##### 说明：

返回列表 key 的长度。

如果 key 不存在，则 key 被解释为一个空列表，返回 0 .

如果 key 不是列表类型，返回一个错误。

##### 返回值：

##### 示例：

    coderknock> LLEN programmingLanguage
    (integer) 8
    # 如果 key 不存在
    coderknock> LLEN a
    (integer) 0
    # 如果 key 对应的类型不是列表
    coderknock> LLEN embstrKey
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

## 删除

### 从左侧删除元素

#### **LPOP**

>** 自1.0.0起可用。**

>** 时间复杂度：**  O(1)。

##### 语法：**LPOP key**

##### 说明：

移除并返回列表 key 的左侧（头）的元素。

##### 返回值：

列表的左侧（头）的元素。

当 key 不存在时，返回 nil 。

##### 示例：

    coderknock> LRANGE programmingLanguage 0 -1
    1) "ruby"
    2) "javaBefter"
    3) "java"
    4) "javaAfter"
    5) "js"
    6) "go"
    7) "python"
    8) "java"
    coderknock> LPOP programmingLanguage
    "ruby"
    coderknock> LRANGE programmingLanguage 0 -1
    1) "javaBefter"
    2) "java"
    3) "javaAfter"
    4) "js"
    5) "go"
    6) "python"
    7) "java"
    coderknock> LPOP embstrKey
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

### 从列表右侧弹出

#### RPOP

>** 自1.0.0起可用。**

>** 时间复杂度：**  O(1) 。

##### 语法：

##### 说明：

移除并返回列表 key 的尾（最右侧）元素。

##### 返回值：

列表的尾元素。

当 key 不存在时，返回 nil 。

##### 示例：

    coderknock> RPOP programmingLanguage
    "java"
    coderknock> LRANGE programmingLanguage 0 -1
    1) "javaBefter"
    2) "java"
    3) "javaAfter"
    4) "js"
    5) "go"
    6) "python"

### 删除指定元素

#### LREM

>** 自1.0.0起可用。**

>** 时间复杂度：** O(N)，  N  为列表的长度。

##### 语法：LREM key count value

##### 说明：

根据参数 count 的值，移除列表中与参数 value 相等的元素。

count 的值可以是以下几种：

* count > 0 : 从表头开始向表尾搜索，移除与 value 相等的元素，数量为 count 。
* count < 0 : 从表尾开始向表头搜索，移除与 value 相等的元素，数量为 count 的绝对值。
* count = 0 : 移除表中所有与 value 相等的值。

##### 返回值：

被移除元素的数量。

因为不存在的 key 被视作空表(empty list)，所以当 key 不存在时， LREM 命令总是返回 0 。

##### 示例：

    coderknock> LRANGE lremTest 0 -1
     1) "java"
     2) "php"
     3) "python"
     4) "java"
     5) "go"
     6) "ruby"
     7) "python"
     8) "java"
     9) "javascript"
    10) "nodejs"
    11) "python"
    12) "go"
    13) "java"
    # 删除前两个 java
    coderknock> LREM lremTest 2 java
    (integer) 2
    coderknock> LRANGE lremTest 0 -1
     1) "php"
     2) "python"
     3) "go"
     4) "ruby"
     5) "python"
     6) "java"
     7) "javascript"
     8) "nodejs"
     9) "python"
    10) "go"
    11) "java"
    # 删除最后两个 python
    coderknock> LREM lremTest -2 python
    (integer) 2
    coderknock> LRANGE lremTest 0 -1
    1) "php"
    2) "python"
    3) "go"
    4) "ruby"
    5) "java"
    6) "javascript"
    7) "nodejs"
    8) "go"
    9) "java"
    # 当数量不够的时候只会返回已经删除的个数
    coderknock> LREM lremTest 4 python
    (integer) 1
    coderknock> LREM lremTest 4 python
    (integer) 0
    # 删除全部的 go
    coderknock> LREM lremTest 0 go
    (integer) 2
    coderknock> LRANGE lremTest 0 -1
    1) "php"
    2) "ruby"
    3) "java"
    4) "javascript"
    5) "nodejs"
    6) "java"

### 按照所有范围修剪列表

#### LREM

>** 自1.0.0起可用。**

>** 时间复杂度：** O(N)，  N  为被移除的元素的数量。

##### 语法：LTRIM key start stop

##### 说明：

对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。

举个例子，执行命令 LTRIM list 0 2 ，表示只保留列表 list 的前三个元素，其余元素全部删除。

参数 start 和 stop 下标(index)都以 0 为底，也就是说，以 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。

你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。

当 key 不是列表类型时，返回一个错误。

LTRIM 命令通常和 LPUSH 命令或 RPUSH 命令配合使用，举个例子：

    LPUSH log newest_log
    LTRIM log 0 99

这个例子模拟了一个日志程序，每次将最新日志 newest_log 放到 log 列表中，并且只保留最新的 100 项。注意当这样使用 LTRIM 命令时，时间复杂度是O(1)，因为平均情况下，每次只有一个元素被移除。

**注意LTRIM命令和编程语言中区间函数的区别**

假如你有一个包含一百个元素的列表 list ，对该列表执行 LTRIM list 0 10 ，结果是一个包含11个元素的列表，这表明 stop 下标也在 LTRIM 命令的取值范围之内(闭区间)，这和某些语言的区间函数可能不一致，比如Ruby的 Range.new 、 Array#slice 和Python的 range() 函数。

**超出范围的下标**

超出范围的下标值不会引起错误。

如果 start 下标比列表的最大下标 end ( LLEN list 减去 1 )还要大，或者 start > stop ， LTRIM 返回一个空列表(因为 LTRIM 已经将整个列表清空)。

如果 stop 下标比 end 下标还要大，Redis将 stop 的值设置为 end 。

##### 返回值：

被移除元素的数量。

因为不存在的 key 被视作空表(empty list)，所以当 key 不存在时， LREM 命令总是返回 0 。

##### 示例：

    # 情况 1： 常见情况， start 和 stop 都在列表的索引范围之内
    coderknock> RPUSH ltrimTest 0 1 2 3 4 5 6 7 8 9
    (integer) 10
    coderknock> LTRIM ltrimTest 1 -1
    OK
    coderknock> LRANGE ltrimTest 0 -1
    1) "1"
    2) "2"
    3) "3"
    4) "4"
    5) "5"
    6) "6"
    7) "7"
    8) "8"
    9) "9"
    # 情况 2： stop 比列表的最大下标还要大
    coderknock> LTRIM ltrimTest 3 1000
    OK
    coderknock> LRANGE ltrimTest 0 -1 # 这里只删除了 之前索引为 0,1,2,3 的元素
    1) "4"
    2) "5"
    3) "6"
    4) "7"
    5) "8"
    6) "9"
    # 情况 3： start 和 stop 都比列表的最大下标要大，并且 start < stop
    coderknock> LTRIM ltrimTest 1000 1001
    OK
    coderknock> LRANGE ltrimTest 0 -1 # 此时列表被清空【因为那些索引下，没有元素】
    (empty list or set)
    # 情况 4：  start > stop
    coderknock> RPUSH ltrimTest 0 1 2 3 4 5 6 7 8 9
    (integer) 10
    coderknock> LTRIM ltrimTest 4 2
    OK
    coderknock> LRANGE ltrimTest 0 -1 # 列表被清空
    (empty list or set)

### 修改指定元素

#### LSET

>** 自1.0.0起可用。**

>** 时间复杂度：** 对头元素或尾元素进行  LSET  操作，复杂度为 O(1)。

> 其他情况下，为 O(N)，  N  为列表的长度。

##### 语法：**LSET key index value**

##### 说明：

将列表 key 下标为 index 的元素的值设置为 value 。

当 index 参数超出范围，或对一个空列表( key 不存在)进行 LSET 时，返回一个错误。

##### 返回值：

操作成功返回 ok ，否则返回错误信息。

##### 示例：

    coderknock> RPUSH lsetTest 0 1 2 3 4 5
    (integer) 6
    coderknock> LRANGE lsetTest 0 -1
    1) "0"
    2) "1"
    3) "2"
    4) "3"
    5) "4"
    6) "5"
    coderknock> LSET lsetTest 2  new2
    OK
    coderknock> LRANGE lsetTest 0 -1
    1) "0"
    2) "1"
    3) "new2"
    4) "3"
    5) "4"
    6) "5"
    # 索引超出时会报错
    coderknock> LSET lsetTest  9 new9
    (error) ERR index out of range
    # key 不存在会报错
    coderknock> LSET nonKey  0 1
    (error) ERR no such key

### 阻塞操作

#### BLPOP

>** 自2.0.0起可用。**

>** 时间复杂度：**  O(1)。

##### 语法：BLPOP key [key ...] timeout

##### 说明：

[BLPOP][1] 是列表的阻塞式(blocking)弹出原语。

它是 [LPOP][2] 命令的阻塞版本，当给定列表内没有任何元素可供弹出的时候，连接将被 [BLPOP][1] 命令阻塞，直到等待超时或发现可弹出元素为止。

当给定多个 key 参数时，按参数 key 的先后顺序依次检查各个列表，弹出第一个非空列表的头元素。

**非阻塞行为**

当 [BLPOP][1] 被调用时，如果给定 key 内至少有一个非空列表，那么弹出遇到的第一个非空列表的头元素，并和被弹出元素所属的列表的名字一起，组成结果返回给调用者。

当存在多个给定 key 时， [BLPOP][1] 按给定 key 参数排列的先后顺序，依次检查各个列表。

假设现在有 job 、 command 和 request 三个列表，其中 job 不存在， command 和 request 都持有非空列表。考虑以下命令：

BLPOP job command request 0[BLPOP][1] 保证返回的元素来自 command ，因为它是按”查找 job -> 查找 command -> 查找 request “这样的顺序，找到第一个非空列表。

    coderknock> DEL job command request           # 确保key都被删除
    (integer) 0
    
    coderknock> LPUSH command "update system..."  # 为command列表增加一个值
    (integer) 1
    
    coderknock> LPUSH request "visit page"        # 为request列表增加一个值
    (integer) 1
    
    coderknock> BLPOP job command request 0       # job 列表为空，被跳过，紧接着 command 列表的第一个元素被弹出。
    1) "command"                             # 弹出元素所属的列表
    2) "update system..."                    # 弹出元素所属的值

**阻塞行为**

如果所有给定 key 都不存在或包含空列表，那么 [BLPOP][1] 命令将阻塞连接，直到等待超时，或有另一个客户端对给定 key 的任意一个执行 [LPUSH][3] 或 [RPUSH][4] 命令为止。

超时参数 timeout 接受一个以秒为单位的数字作为值。超时参数设为 0 表示阻塞时间可以无限期延长(block indefinitely) ，客户端会一直阻塞等待。

    coderknock> EXISTS job                # 确保两个 key 都不存在
    (integer) 0
    coderknock> EXISTS command
    (integer) 0
    
    coderknock> BLPOP job command 300     # 因为key一开始不存在，所以操作会被阻塞，直到另一客户端对 job 或者 command 列表进行 PUSH 操作。
    1) "job"                         # 这里被 push 的是 job
    2) "do my home work"             # 被弹出的值
    (26.26s)                         # 等待的秒数
    
    coderknock> BLPOP job command 5       # 等待超时的情况，这里设置 5 秒超时就，不再阻塞
    (nil)
    (5.66s)                          # 等待的秒数

**相同的key被多个客户端同时阻塞**

相同的 key 可以被多个客户端同时阻塞。

不同的客户端被放进一个队列中，按『先阻塞先服务』(first-BLPOP，first-served)的顺序为 key 执行 [BLPOP][1] 命令。

**在MULTI/EXEC事务中的BLPOP**

[BLPOP][1] 可以用于流水线(pipline,批量地发送多个命令并读入多个回复)，但把它用在 [MULTI][5] / [EXEC][6] 块当中没有意义。因为这要求整个服务器被阻塞以保证块执行时的原子性，该行为阻止了其他客户端执行 [LPUSH][3] 或 [RPUSH][4] 命令。

因此，一个被包裹在 [MULTI][5] / [EXEC][6] 块内的 [BLPOP][1] 命令，行为表现得就像 [LPOP][2] 一样，对空列表返回 nil ，对非空列表弹出列表元素，不进行任何阻塞操作。

    # 对非空列表进行操作
    coderknock> RPUSH job programming
    (integer) 1
    coderknock> MULTI
    OK
    coderknock> BLPOP job 30
    QUEUED
    coderknock> EXEC           # 不阻塞，立即返回
    1) 1) "job"
       2) "programming"
    # 对空列表进行操作
    coderknock> LLEN job      # 空列表
    (integer) 0
    coderknock> MULTI
    OK
    coderknock> BLPOP job 30
    QUEUED
    coderknock> EXEC         # 不阻塞，立即返回
    1) (nil)

##### 返回值：

如果列表为空，返回一个 nil 。

否则，返回一个含有两个元素的列表，第一个元素是被弹出元素所属的 key ，第二个元素是被弹出元素的值。

##### 示例：

已在说明演示

#### BRPOP

>** 自2.0.0起可用。**

>** 时间复杂度：**  O(1)。

##### 语法：**BRPOP**key [key ...] timeout

##### 说明：

[BRPOP][7] 是列表的阻塞式(blocking)弹出原语。

它是 [RPOP][8] 命令的阻塞版本，当给定列表内没有任何元素可供弹出的时候，连接将被 [BRPOP][7] 命令阻塞，直到等待超时或发现可弹出元素为止。

当给定多个 key 参数时，按参数 key 的先后顺序依次检查各个列表，弹出第一个非空列表的尾部元素。

关于阻塞操作的更多信息，请查看 [BLPOP][1] 命令， [BRPOP][7] 除了弹出元素的位置和 [BLPOP][1] 不同之外，其他表现一致。

##### 返回值：

假如在指定时间内没有任何元素被弹出，则返回一个 nil 和等待时长。

反之，返回一个含有两个元素的列表，第一个元素是被弹出元素所属的 key ，第二个元素是被弹出元素的值。

##### 示例：

可以参考 BLPOP 示例

### 内部编码

列表类型的内部编码有三种。

* ziplist（压缩列表）：当列表的元素个数小于list-max-ziplist-entries配置（默认512个），同时列表中每个元素的值都小于list-max-ziplist-value配置时（默认64字节），Redis会选用ziplist来作为列表的内部实现来减少内存的使用。

* linkedlist（链表）：当列表类型无法满足ziplist的条件时，Redis会使用 linkedlist 作为列表的内部实现。
* quicklist (快速列表) Redis 3.2 版本及以上才有该内部编码：quicklist 是一个 ziplist 的双向链表。双向链表是由多个节点（Node）组成的。这个描述的意思是：quicklist 的每个节点都是一个 ziplist。双向链表便于在表的两端进行push和pop操作，但是它的内存开销比较大。首先，它在每个节点上除了要保存数据之外，还要额外保存两个指针；其次，双向链表的各个节点是单独的内存块，地址不连续，节点多了容易产生内存碎片。

ziplist 由于是一整块连续内存，所以存储效率很高。但是，它不利于修改操作，每次数据变动都会引发一次内存的 realloc 。特别是当 ziplist 长度很长的时候，一次 realloc 可能会导致大批量的数据拷贝，进一步降低性能。

于是，结合了双向链表和 ziplist 的优点，quicklist就应运而生了，在时间和空间上做了一个均衡，能较大程度上提高Redis的效率。压入和弹出操作的时间复杂度都很理想。

### 使用场景

* lpush+lpop=Stack（栈）
* lpush+rpop=Queue（队列）
* lpsh+ltrim=Capped Collection（有限集合）
* lpush+brpop=Message Queue（消息队列）

[0]: http://www.jianshu.com/u/2de721a368d3
[1]: http://redisdoc.com/list/blpop.html#blpop
[2]: http://redisdoc.com/list/lpop.html#lpop
[3]: http://redisdoc.com/list/lpush.html#lpush
[4]: http://redisdoc.com/list/rpush.html#rpush
[5]: http://redisdoc.com/transaction/multi.html#multi
[6]: http://redisdoc.com/transaction/exec.html#exec
[7]: http://redisdoc.com/list/brpop.html#brpop
[8]: http://redisdoc.com/list/rpop.html#rpop
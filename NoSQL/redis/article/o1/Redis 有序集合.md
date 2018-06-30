# Redis 有序集合

作者  [三产][0] 已关注 2017.06.16 16:49  字数 3824  阅读 1 评论 0 喜欢 0

# 简介

有序集合是给每个元素设置一个分数（score）作为排序的依据这一概念的集合，其也是不能有重复元素的。有序集合提供了获取指定分数和元素范围查询、计算成员排名等功能。

数据结构 | 是否允许重复元素 | 是否有序 | 有序实现方式 | 应用场景
-|-|-|-|- 
列表 | 是 | 是 | 索引 | 时间轴、消息队列等 
集合 | 否 | 否 | 无 | 标签、社交关系等 
有序结合 | 否 | 是 | 分数 | 排行榜等 

# 命令

## 集合内

### 添加成员

#### ZADD

>** 自1.2.0可用。**

>** 时间复杂度：** O(M*log(N))，  N  是有序集的基数，  M  为成功添加的新成员的数量。

##### 语法：**ZADD key [NX|XX][CH][INCR] score member [score member ...]**

##### 说明：

将一个或多个 member 元素及其 score 值加入到有序集 key 当中。

如果某个 member 已经是有序集的成员，那么更新这个 member 的 score 值，并通过重新插入这个 member 元素，来保证该 member 在正确的位置上。

score 值可以是整数值或双精度浮点数。

如果 key 不存在，则创建一个空的有序集并执行 ZADD 操作。

当 key 存在但不是有序集类型时，返回一个错误。

**在 Redis 2.4 版本以前， ZADD 每次只能添加一个元素。**

Redis 3.0.2 为 ZADD 命令添加了 NXXXCHINCR 四个选项：

* NX：member 必须不存在，才可以设置成功，用于添加。
* XX ：member 必须存在，才可以设置成功，用于更新。
* CH ：返回此次操作后，有序集合元素和分数发生变化的个数
* INCR ：对 score 做增加，相当于后面介绍的ZINCRBY。

##### 返回值：

被成功添加的新成员的数量，不包括那些被更新的、已经存在的成员。

##### 示例：

     coderknock> ZADD ztest 100 java 99 python 80 go 120 kotlin
    (integer) 4
    # 查看有序集合内所有元素并且按分数排序
     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "100"
    7) "kotlin"
    8) "120"
    # 选项填写在 key 后面，位置不能错误
     coderknock> ZADD ztest 100 java 99 python 80 go 120 kotlin CH
    (error) ERR syntax error
     coderknock> ZADD CH ztest 100 java 99 python 80 go 120 kotlin
    (error) ERR syntax error
    # 下面两个语句进行了对比，如果不加 CH 显示的数量不包括更新和已经存在的。
     coderknock>  ZADD ztest CH 100 java 99 python 80 go 121 kotlin
    (integer) 1
     coderknock>  ZADD ztest 100 java 99 python 80 go 120 kotlin
    (integer) 0

有序集合相比集合提供了排序字段，但是也产生了代价，ZADD 的时间复杂度为O(log(n))，SADD 的时间复杂度为 O(1)。

### 计算成员个数

#### ZCARD

>** 自1.2.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**ZCARD key**

##### 说明：

返回有序集 key 的基数。

##### 返回值：

当 key 存在且是有序集类型时，返回有序集的基数。

当 key 不存在时，返回 0 。

##### 示例：

     coderknock> ZCARD ztest
    (integer) 4
     coderknock> ZCARD nonKey
    (integer) 0

### 计算某个成员分数

#### ZSCORE

>** 自1.2.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**ZSCORE key member**

##### 说明：

返回有序集 key 中，成员 member 的 score 值。

如果 member 元素不是有序集 key 的成员，或 key 不存在，返回 nil 。

##### 返回值：

member 成员的 score 值，以字符串形式表示。

如果 member 元素不是有序集 key 的成员，或 key 不存在，返回 nil 。

##### 示例：

     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "100"
    7) "kotlin"
    8) "120"
     coderknock> ZSCORE ztest java
    "100"
    # 不存在时返回 nil
     coderknock> ZSCORE ztest ruby
    (nil)

### 计算成员的排名

#### ZRANK

>** 自2.0.0可用。**

>** 时间复杂度：** O(log(N))。

##### 语法：**ZRANK key member**

##### 说明：

返回有序集 key 中成员 member 的排名。其中有序集成员按 score 值递增(从小到大)顺序排列。

排名以 0 为底，也就是说， score 值最小的成员排名为 0 。

使用 ZREVRANK 命令可以获得成员按 score 值递减(从大到小)排列的排名。

##### 返回值：

如果 member 是有序集 key 的成员，返回 member 的排名。

如果 member 不是有序集 key 的成员，返回 nil 。

##### 示例：

     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "100"
    7) "kotlin"
    8) "120"
    # 没有 WITHSCORES 选项的话则不显示分数
     coderknock> ZRANGE ztest 0 -1
    1) "go"
    2) "python"
    3) "java"
    4) "kotlin"
     coderknock> ZRANK ztest java
    (integer) 2 # 排名从 0 开始
     coderknock> ZRANK ztest ruby
    (nil)

#### ZREVRANK

>** 自2.0.0可用。**

>** 时间复杂度：** O(log(N))。

##### 语法：**ZREVRANK key member**

##### 说明：

返回member存储在排序集中的排名key，其中从高到低排列。排名（或索引）为0，这意味着具有最高分数的成员具有排名0。

使用 ZRANK 获得从低到高排列的分数的元素的排名。

##### 返回值：

如果 member存在于排序集中，则 整数回复 ：排名member。

如果 member 排序集中key不存在或不存在，则 批量字符串回复 ：nil。

##### 示例：

     coderknock> ZREVRANGE ztest 0 -1 WITHSCORES
    1) "kotlin"
    2) "120"
    3) "java"
    4) "100"
    5) "python"
    6) "99"
    7) "go"
    8) "80"
    # 没有 WITHSCORES 选项的话则不显示分数
     coderknock>  ZREVRANGE ztest 0 -1
    1) "kotlin"
    2) "java"
    3) "python"
    4) "go"
     coderknock> ZREVRANK ztest java
    (integer) 1  # 排名从 0 开始

### 删除成员

#### ZREM

>** 自1.2.0可用。**

>** 时间复杂度：** O(M*log(N))，  N  为有序集的基数，  M  为被成功移除的成员的数量。

##### 语法：**ZREM key member [member ...]**

##### 说明：

移除有序集 key 中的一个或多个成员，不存在的成员将被忽略。

当 key 存在但不是有序集类型时，返回一个错误。

**在 Redis 2.4 版本以前， ZREM 每次只能删除一个元素。**

##### 返回值：

被成功移除的成员的数量，不包括被忽略的成员。

##### 示例：

     coderknock> ZREVRANK ztest java
    (integer) 1
     coderknock> ZRANGE ztest 0 -1
    1) "go"
    2) "python"
    3) "java"
    4) "kotlin"
     coderknock> ZREM ztest python
    (integer) 1
     coderknock> ZRANGE ztest 0 -1
    1) "go"
    2) "java"
    3) "kotlin"
     coderknock> ZREM ztest java ruby python go
    (integer) 2

### 增加成员的分数

#### ZINCRBY

>** 自1.2.0可用。**

>** 时间复杂度：** O(log(N))。

##### 语法：**ZINCRBY key increment member**

##### 说明：

为有序集 key 的成员 member 的 score 值加上增量 increment 。

可以通过传递一个负数值 increment ，让 score 减去相应的值，比如 ZINCRBY key -5 member ，就是让 member 的 score 值减去 5 。

当 key 不存在，或 member 不是 key 的成员时， ZINCRBY key increment member 等同于 ZADD key increment member 。

当 key 不是有序集类型时，返回一个错误。

score 值可以是整数值或双精度浮点数。

##### 返回值：

member 成员的新 score 值，以字符串形式表示。

##### 示例：

     coderknock> ZSCORE ztest java
    "100"
     coderknock> ZINCRBY ztest 2 java
    "102"
     coderknock> ZSCORE ztest java
    "102"

### 返回指定排名范围的成员

#### ZRANGE

>** 自1.2.0可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，而  M  为结果集的基数。

##### 语法：**ZRANGE key start stop [WITHSCORES]**

##### 说明：

返回有序集 key 中，指定区间内的成员。

其中成员的位置按 score 值递增(从小到大)来排序。

具有相同 score 值的成员按字典序( lexicographical order )来排列。

如果你需要成员按 score 值递减(从大到小)来排列，请使用 ZREVRANGE 命令。

下标参数 start 和 stop 都以 0 为底，也就是说，以 0 表示有序集第一个成员，以 1 表示有序集第二个成员，以此类推。

你也可以使用负数下标，以 -1 表示最后一个成员， -2 表示倒数第二个成员，以此类推。

超出范围的下标并不会引起错误。

比如说，当 start 的值比有序集的最大下标还要大，或是 start > stop 时， ZRANGE 命令只是简单地返回一个空列表。

另一方面，假如 stop 参数的值比有序集的最大下标还要大，那么 Redis 将 stop 当作最大下标来处理。

可以通过使用 WITHSCORES 选项，来让成员和它的 score 值一并返回，返回列表以 value1,score1, ..., valueN,scoreN 的格式表示。

客户端库可能会返回一些更复杂的数据类型，比如数组、元组等。

##### 返回值：

指定区间内，带有 score 值(可选)的有序集成员的列表。

##### 示例：

     coderknock> ZRANGE ztest 0 -1
    1) "go"
    2) "python"
    3) "java"
    4) "kotlin"
     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "102"
    7) "kotlin"
    8) "120"
    # start > stop 则返回空集合
     coderknock> ZRANGE ztest 3 2 WITHSCORES
    (empty list or set)
     coderknock> ZRANGE ztest 2 5 WITHSCORES
    1) "java"
    2) "102"
    3) "kotlin"
    4) "120"
    # stop > 总长度
     coderknock> ZRANGE ztest 2 1000 WITHSCORES
    1) "java"
    2) "102"
    3) "kotlin"
    4) "120"

#### ZRANGE

>** 自1.2.0可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，而  M  为结果集的基数。

##### 语法：**ZRANGE key start stop [WITHSCORES]**

##### 说明：

返回有序集 key 中，指定区间内的成员。

其中成员的位置按 score 值递减(从大到小)来排列。

具有相同 score 值的成员按字典序的逆序( reverse lexicographical order )排列。

除了成员按 score 值递减的次序排列这一点外， ZREVRANGE 命令的其他方面和 ZRANGE 命令一样。

##### 返回值：

指定区间内，带有 score 值(可选)的有序集成员的列表。

##### 示例：

     coderknock> ZREVRANGE ztest 0 -1
    1) "kotlin"
    2) "java"
    3) "python"
    4) "go"
     coderknock> ZREVRANGE ztest 0 -1 WITHSCORES
    1) "kotlin"
    2) "120"
    3) "java"
    4) "102"
    5) "python"
    6) "99"
    7) "go"
    8) "80"

### 返回指定分数范围的成员

#### ZRANGEBYSCORE

>** 自1.0.5可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，  M  为被结果集的基数。

##### 语法：**ZRANGEBYSCORE key min max [WITHSCORES][LIMIT offset count]**

##### 说明：

返回有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员。有序集成员按 score 值递增(从小到大)次序排列。

具有相同 score 值的成员按字典序( lexicographical order )来排列(该属性是有序集提供的，不需要额外的计算)。

可选的 LIMIT 参数指定返回结果的数量及区间(就像SQL中的 SELECT LIMIT offset, count )，注意当 offset 很大时，定位 offset 的操作可能需要遍历整个有序集，此过程最坏复杂度为 O(N) 时间。

可选的 WITHSCORES 参数决定结果集是单单返回有序集的成员，还是将有序集成员及其 score 值一起返回。

**该选项自 Redis 2.0 版本起可用。**

**区间及无限**

min 和 max 可以是 -inf 和 +inf ，这样一来，你就可以在不知道有序集的最低和最高 score 值的情况下，使用 ZRANGEBYSCORE 这类命令。

默认情况下，区间的取值使用 闭区间 (小于等于或大于等于)，你也可以通过给参数前增加 ( 符号来使用可选的 开区间 (小于或大于)。

举个例子：

    ZRANGEBYSCORE zset (1 5

返回所有符合条件 1 < score <= 5 的成员，而

    ZRANGEBYSCORE zset (5 (10

则返回所有符合条件 5 < score < 10 的成员。

##### 返回值：

指定区间内，带有 score 值(可选)的有序集成员的列表。

##### 示例：

     coderknock> ZRANGEBYSCORE ztest 80 100 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    # 开区间用法
     coderknock> ZRANGEBYSCORE ztest (80 100 WITHSCORES
    1) "python"
    2) "99"
    # 查询所有
     coderknock> ZRANGEBYSCORE ztest -inf +inf WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "102"
    7) "kotlin"
    8) "120"
     coderknock> ZRANGEBYSCORE ztest -inf +inf WITHSCORES LIMIT 1 2
    1) "python"
    2) "99"
    3) "java"
    4) "102"

#### ZREVRANGEBYSCORE

>** 自1.0.5可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，  M  为被结果集的基数。

##### 语法：ZREVRANGEBYSCORE key max min [WITHSCORES][LIMIT offset count]

##### 说明：

返回有序集 key 中， score 值介于 max 和 min 之间(默认包括等于 max 或 min )的所有的成员。有序集成员按 score 值递减(从大到小)的次序排列。

具有相同 score 值的成员按字典序的逆序( reverse lexicographical order )排列。

除了成员按 score 值递减的次序排列这一点外， ZREVRANGEBYSCORE 命令的其他方面和 ZRANGEBYSCORE 命令一样。

##### 返回值：

指定区间内，带有 score 值(可选)的有序集成员的列表。

##### 示例：

    # max min 参数位置也有区别
     coderknock> ZREVRANGEBYSCORE ztest -inf +inf WITHSCORES LIMIT 1 2
    (empty list or set)
     coderknock>  ZREVRANGEBYSCORE ztest +inf -inf WITHSCORES LIMIT 1 2
    1) "java"
    2) "102"
    3) "python"
    4) "99"

### 返回指定分数范围成员个数

#### ZCOUNT

>** 自2.0.0可用。**

>** 时间复杂度：** O(log(N))，  N  为有序集的基数。

##### 语法：**ZCOUNT key min max**

##### 说明：

返回有序集 key 中， score 值在 min 和 max 之间(默认包括 score 值等于 min 或 max )的成员的数量。

关于参数 min 和 max 的详细使用方法，请参考 ZRANGEBYSCORE 命令。

##### 返回值：

score 值在 min 和 max 之间的成员的数量。

##### 示例：

     coderknock> ZCOUNT ztest -inf +inf
    (integer) 4
     coderknock> ZCOUNT ztest 80 100
    (integer) 2

### 删除指定排名内的升序元素

#### ZREMRANGEBYRANK

>** 自2.0.0可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，而  M  为被移除成员的数量。

##### 语法：**ZREMRANGEBYRANK key start stop**

##### 说明：

移除有序集 key 中，指定排名(rank)区间内的所有成员。

区间分别以下标参数 start 和 stop 指出，包含 start 和 stop 在内。

下标参数 start 和 stop 都以 0 为底，也就是说，以 0 表示有序集第一个成员，以 1 表示有序集第二个成员，以此类推。

你也可以使用负数下标，以 -1 表示最后一个成员， -2 表示倒数第二个成员，以此类推。

##### 返回值：

被移除成员的数量。

##### 示例：

    # 查询所有
     coderknock> ZRANGEBYSCORE ztest -inf +inf WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "102"
    7) "kotlin"
    8) "120"
     coderknock> ZREMRANGEBYRANK ztest 1 3
    (integer) 3
     coderknock> ZRANGE ztest 0 -1
    1) "go"

### 删除指定分数内的成员

#### ZREMRANGEBYSCORE

>** 自1.2.0可用。**

>** 时间复杂度：** O(log(N)+M)，  N  为有序集的基数，而  M  为被移除成员的数量。

##### 语法：**ZREMRANGEBYSCORE key min max**

##### 说明：

移除有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员。

**自版本2.1.6开始， score 值等于 min 或 max 的成员也可以不包括在内，详情请参见 ZRANGEBYSCORE 命令。**

##### 返回值：

被移除成员的数量。

##### 示例：

     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "go"
    2) "80"
    3) "python"
    4) "99"
    5) "java"
    6) "100"
    7) "kotlin"
    8) "120"
     coderknock> ZREMRANGEBYSCORE ztest 80 100
    (integer) 3
     coderknock> ZRANGE ztest 0 -1 WITHSCORES
    1) "kotlin"
    2) "120"

## 集合间操作

### 并集

#### ZUNIONSTORE

>** 自2.0.0可用。**

>** 时间复杂度：** O(N)+O(M log(M))，  N  为给定有序集基数的总和，  M  为结果集的基数。

##### 语法：**ZUNIONSTORE destination numkeys key [key ...][WEIGHTS weight [weight ...]][AGGREGATE SUM|MIN|MAX]**

##### 说明：

计算给定的一个或多个有序集的并集，其中给定 key 的数量必须以 numkeys 参数指定，并将该并集(结果集)储存到 destination 。

默认情况下，结果集中某个成员的 score 值是所有给定集下该成员 score 值之 _和_ 。

**WEIGHTS**

使用 WEIGHTS 选项，你可以为 _每个_ 给定有序集 _分别_ 指定一个乘法因子(multiplication factor)，每个给定有序集的所有成员的 score 值在传递给聚合函数(aggregation function)之前都要先乘以该有序集的因子。

如果没有指定 WEIGHTS 选项，乘法因子默认设置为 1 。

**AGGREGATE**

使用 AGGREGATE 选项，你可以指定并集的结果集的聚合方式。

默认使用的参数 SUM ，可以将所有集合中某个成员的 score 值之 _和_ 作为结果集中该成员的 score 值；使用参数 MIN ，可以将所有集合中某个成员的 _最小_score 值作为结果集中该成员的 score 值；而参数 MAX 则是将所有集合中某个成员的 _最大_score 值作为结果集中该成员的 score 值。

##### 返回值：

保存到 destination 的结果集的基数。

##### 示例：

     coderknock> ZRANGE setTest 0 -1 WITHSCORES
    1) "one"
    2) "1"
    3) "two"
    4) "2"
    5) "three"
    6) "3"
     coderknock> ZRANGE setTest2 0 -1 WITHSCORES
    1) "one"
    2) "1"
    3) "two"
    4) "2"
    5) "three"
    6) "3"
    7) "four"
    8) "4"
     coderknock> ZUNIONSTORE outTest 2 setTest setTest2 WEIGHTS 2 3
    (integer) 4
     coderknock> ZRANGE outTest 0 -1 WITHSCORES
    1) "one"
    2) "5"
    3) "two"
    4) "10"
    5) "four"
    6) "12"
    7) "three"
    8) "15"

### 交集

#### ZINTERSTORE

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**ZINTERSTORE**destination numkeys key [key ...][WEIGHTS weight [weight ...]][AGGREGATE SUM|MIN|MAX]**

##### 说明：

计算给定的一个或多个有序集的交集，其中给定 key 的数量必须以 numkeys 参数指定，并将该交集(结果集)储存到 destination 。

默认情况下，结果集中某个成员的 score 值是所有给定集下该成员 score 值之和.

    关于 `WEIGHTS` 和 `AGGREGATE` 选项的描述，参见  `ZUNIONSTORE`  命令。

##### 返回值：

保存到 destination 的结果集的基数。

##### 示例：

     coderknock> ZRANGE setTest 0 -1 WITHSCORES
    1) "one"
    2) "1"
    3) "two"
    4) "2"
    5) "three"
    6) "3"
     coderknock> ZRANGE setTest2 0 -1 WITHSCORES
    1) "one"
    2) "1"
    3) "two"
    4) "2"
    5) "three"
    6) "3"
    7) "four"
    8) "4"
     coderknock> ZINTERSTORE zinterstoreTest 2 setTest setTest2
    (integer) 3
     coderknock> ZRANGE zinterstoreTest 0 -1 WITHSCORES
    1) "one"
    2) "2"
    3) "two"
    4) "4"
    5) "three"
    6) "6"

# 内部编码

有序集合类型的内部编码有两种：

* ziplist（压缩列表）：当有序集合的元素个数小于 zset-max-ziplistentries 配置（默认128个），同时每个元素的值都小于 zset-max-ziplist-value 配置（默认64字节）时，Redis会用 ziplist 来作为有序集合的内部实现，ziplist 可以有效减少内存的使用。
* skiplist（跳跃表）：当 ziplist 条件不满足时，有序集合会使用 skiplist 作为内部实现，因为此时ziplist的读写效率会下降。
* 当元素个数较少且每个元素较小时，内部编码为skiplist：

     coderknock> ZRANGE zinterstoreTest 0 -1 WITHSCORES
    1) "one"
    2) "2"
    3) "two"
    4) "4"
    5) "three"
    6) "6"
     coderknock> OBJECT ENCODING zinterstoreTest
    "ziplist"

1. 当元素个数超过128个，内部编码变为ziplist：

    import redis
    
    r = redis.StrictRedis(host='127.0.0.1', password='admin123', port=6379, db=0)
    num = 128
    key = "ZSETTest" + str(num)
    r.delete(key)
    for i in range(num):
        r.zadd(key, i,i)
    
    # 可以使用这个命令查询内部编码
    print(key)
    print(r.zcard(key))
    print(r.object("ENCODING", key))

当 num = 128 时：

    ZSETTest128
    128
    b'ziplist'

当 num = 129 时：

    ZSETTest129
    129
    b'skiplist'

1. 当某个元素大于64字节时，内部编码也会变为 skiplist ：

     coderknock> ZADD lg64 20 aaaassssddddffffgggghhhhkj=jjjkkkklllllsdfasdlkfcsdkcaneyuirhworitsuhdiouoooooofovutivhwoeirrthsoiuyqrbwiveyrvisuyrsui
    (integer) 1
     coderknock> OBJECT ENCODING lg64
    "skiplist"

# 使用场景

1. 点赞
1. 积分系统
1. 分页
1. 排序

[0]: http://www.jianshu.com/u/2de721a368d3
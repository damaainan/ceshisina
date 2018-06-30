# Redis数据库相关操作

作者  [三产][0] 已关注 2017.06.16 16:51  字数 883  阅读 1 评论 0 喜欢 0

Redis 提供了几个面向 Redis 数据库的操作，例如之前已经介绍过或者使用过的 DBSIZE 、SELECT 、FLUSHDB/FLUSHALL 本节将通过具体的使用场景介绍这些命令。

### 切换数据库

#### SELECT

>** 自1.0.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**SELECT index**

##### 说明：

切换到指定的数据库，数据库索引号 index 用数字值指定，以 0 作为起始索引值。

默认使用 0 号数据库。

##### 返回值：

OK

##### 示例：

    coderknock> SET db_number 0         # 默认使用 0 号数据库
    OK
    coderknock> SELECT 1                # 使用 1 号数据库
    OK
    coderknock[1]> GET db_number        # 已经切换到 1 号数据库，注意 Redis 现在的命令提示符多了个 [1]
    (nil)
    coderknock[1]> SET db_number 1
    OK
    coderknock[1]> GET db_number
    "1"
    coderknock[1]> SELECT 3             # 再切换到 3 号数据库
    OK
    coderknock[3]>                      # 提示符从 [1] 改变成了 [3]

许多关系型数据库，例如 MySQL 支持在一个实例下有多个数据库存在的，但是与关系型数据库用字符来区分不同数据库名不同，Redis 只是用数字作为多个数据库的区分。Redis 默认配置中是有16个数据库：

    # 这里是 Redis 配置文件中的配置项
    databases 16
    
    #以下是在客户端中进行测试
    # 此处可以修改，如果没有修改使用 超过 15 索引的数据库会报错（索引从 0 开始 15 代表有 16 个库）
    127.0.0.1:6379> SELECT 16
    (error) ERR invalid DB index
    127.0.0.1:6379> SELECT 15
    OK

Redis3.0 中已经逐渐弱化这个功能，例如 Redis 的分布式实现 Redis Cluster 只允许使用0号数据库，只不过为了向下兼容老版本的数据库功能，该功能没有完全废弃掉，下面分析一下为什么要废弃掉这个“优秀”的功能呢？总结起来有三点：

* Redis是单线程的。如果使用多个数据库，那么这些数据库仍然是使用一个 CPU ，彼此之间还是会受到影响的。
* 多数据库的使用方式，会让调试和运维不同业务的数据库变的困难，假如有一个慢查询存在，依然会影响其他数据库，这样会使得别的业务方定位问题非常的困难。
* 部分Redis的客户端根本就不支持这种方式。即使支持，在开发的时候来回切换数字形式的数据库，很容易弄乱。

建议如果要使用多个数据库功能，完全可以在一台机器上部署多个 Redis 实例，彼此用端口来做区分，因为现代计算机或者服务器通常是有多个 CPU 的。这样既保证了业务之间不会受到影响，又合理地使用了 CPU 资源。

### 清除数据库

#### FLUSHALL

>** 自1.0.0可用。**

>** 时间复杂度：** 尚未明确。

##### 语法：FLUSHALL [ASYNC]

##### 说明：

清空整个 Redis 服务器的数据(删除所有数据库的所有 key )。

此命令不会失败。

**Redis 4.0 版本提供了ASYNC 可选项，用于将该操作另启一个线程，可以起到异步释放的效果。**

##### 返回值：

总是返回 OK 。

##### 示例：

    coderknock> DBSIZE            # 0 号数据库的 key 数量
    (integer) 9
    coderknock> SELECT 1          # 切换到 1 号数据库
    OK
    coderknock[1]> DBSIZE         # 1 号数据库的 key 数量
    (integer) 6
    coderknock[1]> flushall       # 清空所有数据库的所有 key
    OK
    coderknock[1]> DBSIZE         # 不但 1 号数据库被清空了
    (integer) 0
    coderknock[1]> SELECT 0       # 0 号数据库(以及其他所有数据库)也一样
    OK
    coderknock> DBSIZE
    (integer) 0

#### FLUSHDB

>** 自1.0.0可用。**

> **时间复杂度：O(1)。

##### 语法：FLUSHDB [ASYNC]

##### 说明：

清空当前数据库中的所有 key。

此命令不会失败。

**Redis 4.0 版本提供了ASYNC 可选项，用于将该操作另启一个线程，可以起到异步释放的效果。**

##### 返回值：

总是返回 OK 。

##### 示例：

    coderknock> DBSIZE    # 清空前的 key 数量
    (integer) 4
    coderknock> FLUSHDB
    OK
    coderknock> DBSIZE    # 清空后的 key 数量
    (integer) 0

FLUSHDB/FLUSHALL 命令可以非常方便的清理数据，但是也带来两个问题：

* FLUSHDB/FLUSHALL 命令会将所有数据清除，一旦误操作后果不堪设想。
* 如果当前数据库键值数量比较多，FLUSHDB/FLUSHALL 存在阻塞 Redis 的可能性。

所以在使用FLUSHDB/FLUSHALL 一定要小心谨慎。

[0]: http://www.jianshu.com/u/2de721a368d3
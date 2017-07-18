# 【redis专题(8)】命令语法介绍之通用KEY


    select num 
    

数据库选择 默认有16[0到15]个数据库,默认自动选择0号数据库

    move key num
    

移动key到num服务器

    del key [key ...]
    

删除给定的一个或多个 key 。

    exists key
    

检查给定 key 是否存在。

    expire key 
    

整型值 设置key的生命周期 单位秒数   
如果为p(pexpire)单位就变为毫秒

    expireat key timestamp
    

指定key在UNIX 时间戳(unix timestamp)变失效

    KEYS pattern
    

查找所有符合给定模式 pattern 的 key 。

* `KEYS *` 匹配数据库中所有 key 。
* `KEYS h?llo` 通配单个字符 如 hello ， hallo 和 hxllo 等。
* `KEYS h*llo` 通配任意多个字符(包括没有) 如hllo 和 heeeeello 等。
* `KEYS h[ae]llo` 通配括号内的某1个字符 如hello 和 hallo ，但不匹配 hillo 。


**特殊符号用 \ 隔开。**

    ttl key 
    

查询key的生命周期 默认-1,永久有效; 单位秒数 如果为(pttl)单位就变为毫秒 

    persist key
    

不让key失效;

    randomkey
    

从当前数据库中随机返回(不删除)一个 key 。

    rename key newkey
    

将 key 改名为 newkey 。

    renamenx key newkey
    

当且仅当 newkey 不存在时，将 key 改名为 newkey 。

    type key
    

返回 key 所储存的值的类型。

    flushdb
    

清空当前数据库;

## SORT与SCAN

这两个命令稍微复杂一点，所以单独拎出来讲

### sort

    SORT key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC | DESC] [ALPHA] [STORE destination]
    

如果要把redis作为noSql来用的话，该命令就是一个非常重要的命令了，作用有 **分页，排序，数据关联**

#### 排序

**按数值排序**

    

    # 开销金额列表
    redis> LPUSH today_cost 30 1.5 10 8
    (integer) 4
    # 排序
    redis> SORT today_cost
    1) "1.5"
    2) "8"
    3) "10"
    4) "30"
    # 逆序排序
    redis 127.0.0.1:6379> SORT today_cost DESC
    1) "30"
    2) "10"
    3) "8"
    4) "1.5"

**按字符串排序**

必须显示的指定 alpha 选项才能进行排序，否则不能排序； 

    

    127.0.0.1:6379> SORT website
    (error) ERR One or more scores can't be converted into double
    127.0.0.1:6379> SORT website alpha
    1) "www.alipay.com"
    2) "www.baidu.com"
    3) "www.china.com"
    4) "www.sina.com"
    5) "www.tence.com"
    127.0.0.1:6379> SORT website alpha desc
    1) "www.tence.com"
    2) "www.sina.com"
    3) "www.china.com"
    4) "www.baidu.com"
    5) "www.alipay.com"

**limit**

排序从0开始算

    

    127.0.0.1:6379> SORT testnum  limit 5
    (error) ERR syntax error
    127.0.0.1:6379> SORT testnum  limit 5 10
    1) "5"
    2) "6"
    3) "8"
    4) "9"
    5) "10"
    6) "11"
    7) "24"

**关联外部数据**

    

    # admin
    redis 127.0.0.1:6379> LPUSH uid 1
    (integer) 1
    redis 127.0.0.1:6379> SET user_name_1 admin
    OK
    redis 127.0.0.1:6379> SET user_level_1 9999
    OK
    # jack
    redis 127.0.0.1:6379> LPUSH uid 2
    (integer) 2
    redis 127.0.0.1:6379> SET user_name_2 jack
    OK
    redis 127.0.0.1:6379> SET user_level_2 10
    OK
    # peter
    redis 127.0.0.1:6379> LPUSH uid 3
    (integer) 3
    redis 127.0.0.1:6379> SET user_name_3 peter
    OK
    redis 127.0.0.1:6379> SET user_level_3 25
    OK
    # mary
    redis 127.0.0.1:6379> LPUSH uid 4
    (integer) 4
    redis 127.0.0.1:6379> SET user_name_4 mary
    OK
    redis 127.0.0.1:6379> SET user_level_4 70
    OK

    

    redis 127.0.0.1:6379> SORT uid
    1) "1"      # admin
    2) "2"      # jack
    3) "3"      # peter
    4) "4"      # mary
    # ↓↓ 根据外部的level来排序
    redis 127.0.0.1:6379> SORT uid BY user_level_*
    1) "2"      # jack , level = 10
    2) "3"      # peter, level = 25
    3) "4"      # mary, level = 70
    4) "1"      # admin, level = 9999

user_level_* 是一个占位符， 它先取出 uid 中的值， 然后再用这个值来查找相应的键。

    

    # 获取关联数据
    127.0.0.1:6379> sort uid get user_name_*
    1) "admin"
    2) "jack"
    3) "peter"
    4) "mary"
    127.0.0.1:6379> sort uid get user_name_* by user_level_*
    1) "jack"
    2) "peter"
    3) "mary"
    4) "admin"
    # 获取多个外部键
    redis 127.0.0.1:6379> SORT uid GET user_level_* GET user_name_*
    1) "9999"       # level
    2) "admin"      # name
    3) "10"
    4) "jack"
    5) "25"
    6) "peter"
    7) "70"
    8) "mary"
    # 占位符 # 可以获得当前uid的值，这里由于markdown语法的问题，所以临时转义一下；
    127.0.0.1:6379> sort uid desc get \# get user_level_* get user_name_*
    1) "4"
    2) "70"
    3) "mary"
    4) "3"
    5) "25"
    6) "peter"
    7) "2"
    8) "10"
    9) "jack"
    10) "1"
    11) "999"
    12) "admin"

根据 not-exists-key 可以忽略排序，感觉没什么用

    redis 127.0.0.1:6379> SORT uid BY not-exists-key GET # GET user_level_* GET user_name_*
    1) "4"      # id
    2) "70"     # level
    3) "mary"   # name
    4) "3"
    5) "25"
    6) "peter"
    7) "2"
    8) "10"
    9) "jack"
    10) "1"
    11) "9999"
    12) "admin"
    

**关于哈希表的关联**

BY 和 GET 选项都可以用 key->field 的格式来获取哈希表中的域的值， 其中 key 表示哈希表键， 而 field 则表示哈希表的域；

    

    redis 127.0.0.1:6379> HMSET user_info_1 name admin level 9999
    OK
    redis 127.0.0.1:6379> HMSET user_info_2 name jack level 10
    OK
    redis 127.0.0.1:6379> HMSET user_info_3 name peter level 25
    OK
    redis 127.0.0.1:6379> HMSET user_info_4 name mary level 70
    OK
    127.0.0.1:6379> SORT uid  get # get user_info_*->level by user_info_*->level desc
    1) "1"
    2) "9999"
    3) "4"
    4) "70"
    5) "3"
    6) "25"
    7) "2"
    8) "10"

**保存sort结果**

    

    127.0.0.1:6379> SORT uid get # get user_info_*->name  by user_info_*->level desc store user_level_desc_name
    (integer) 8
    127.0.0.1:6379> EXPIRE user_level_desc_name 60
    (integer) 1

可以通过将 SORT 命令的执行结果保存，并用 EXPIRE 为结果设置生存时间，以此来产生一个 SORT 操作的结果缓存。   
这样就可以避免对 SORT 操作的频繁调用：只有当结果集过期时，才需要再调用一次 SORT 操作。   
另外，为了正确实现这一用法，你可能需要加锁以避免多个客户端同时进行缓存重建(也就是多个客户端，同一时间进行 SORT 操作，并保存为结果集)，这一般是用在数据量较大的时候，在程序里面做如下操作：

2. 先判断 `user_level_desc_name` 有没有；如果有就直接取出；
2. 如果没有，然后用`setnx`加个锁（时间具体看数据大小，或者建立完缓存再让其失效），来建立缓存（这时如果有其他进程访问，就会判断到有这个锁就不建立缓存），然后返回数据；


以上参考： [http://doc.redisfans.com/key/sort.html][0]

## scan

    SCAN cursor [MATCH pattern] [COUNT count]
    

SCAN 命令及其相关的 SSCAN 命令、 HSCAN 命令和 ZSCAN 命令都用于增量地迭代（incrementally iterate）一集元素（a collection of elements）：

* SCAN 命令用于迭代当前数据库中的数据库键。
* SSCAN 命令用于迭代集合键中的元素。
* HSCAN 命令用于迭代哈希键中的键值对。
* ZSCAN 命令用于迭代有序集合中的元素（包括元素成员和元素分值）。


当 KEYS 命令被用于处理一个大的数据库时， 又或者 SMEMBERS 命令被用于处理一个大的集合键时， 它们可能会阻塞服务器达数秒之久。这时候就要选择用 SCAN 命令了。   
使用 SMEMBERS 命令可以返回集合键当前包含的所有元素， 但是对于 SCAN 这类增量式迭代命令来说， 因为在对键进行增量式迭代的过程中， 键可能会被修改， 所以增量式迭代命令只能对被返回的元素提供有限的保证 （offer limited guarantees about the returned elements）。

> SSCAN 命令、 HSCAN 命令和 ZSCAN 命令的第一个参数总是一个数据库键。而 SCAN 命令则不需要在第一个参数提供任何数据库键 —— 因为它迭代的是当前数据库中的所有数据库键。

SCAN 命令是一个基于游标的迭代器，每次被调用之后， 都会向用户返回一个新的游标， 用户在下次迭代时需要使用这个新游标作为 SCAN 命令的游标参数， 以此来延续之前的迭代过程。当 SCAN 命令的游标参数被设置为 0 时， 服务器将开始一次新的迭代， 而当服务器向用户返回值为 0 的游标时， 表示迭代已结束。

    

    redis 127.0.0.1:6379> scan 0
    1) "17"  # 当前迭代的游标到17
    2)  1) "key:12"
    2) "key:8"
    3) "key:4"
    4) "key:14"
    5) "key:16"
    6) "key:17"
    7) "key:15"
    8) "key:10"
    9) "key:3"
    10) "key:7"
    11) "key:1"
    redis 127.0.0.1:6379> scan 17
    1) "0" # 然后从17开始继续迭代，迭代到0就代表迭代结束，它会自动rewind；
    2) 1) "key:5"
    2) "key:18"
    3) "key:0"
    4) "key:2"
    5) "key:19"
    6) "key:13"
    7) "key:6"
    8) "key:9"
    9) "key:11"
    # 也可以通过count来限制每次迭代的数量，注意，在每次迭代时这个count的值可以不一样；
    127.0.0.1:6379> scan 0 count 5
    1) "28"
    2) 1) "member1"
    2) "user_info_1"
    3) "user_name_3"
    4) "user_level_4"
    5) "coll"

### 使用MATCH 选项

通过给定 MATCH 参数可以实现 keys 那样的按模式迭代；但是，这里的按模式迭代和keys有一些本质上的区别：

对元素的模式匹配工作是在**命令从数据集中取出元素之后**， 向客户端返回元素之前的这段时间内进行的， 所以如果被迭代的数据集中只有少量元素和模式相匹配， 那么迭代命令或许会在多次执行中都不返回任何元素。

    

    redis 127.0.0.1:6379> scan 224 MATCH *11*
    1) "80"
    2) (empty list or set)
    redis 127.0.0.1:6379> scan 80 MATCH *11*
    1) "176"
    2) (empty list or set)
    redis 127.0.0.1:6379> scan 176 MATCH *11* COUNT 1000
    1) "0"
    2)  1) "key:611"
    2) "key:711"
    3) "key:118"
    4) "key:117"
    5) "key:311"
    6) "key:112"
    7) "key:111"
    8) "key:110"
    9) "key:113"
    10) "key:211"
    11) "key:411"
    12) "key:115"
    13) "key:116"
    14) "key:114"
    15) "key:119"
    16) "key:811"
    17) "key:511"
    18) "key:11"

更多请参考： [http://doc.redisfans.com/key/index.html][1]

[0]: http://doc.redisfans.com/key/sort.html
[1]: http://doc.redisfans.com/key/index.html
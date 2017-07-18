# 【redis专题(4)】命令语法介绍之sorted_set


有序集合可以模拟优先级队列的实现

## 增

    zadd key score1 value1 score2 value2 ..


    redis 127.0.0.1:6379> zadd stu 18 lily 19 hmm 20 lilei 21 lilei
    (integer) 3

添加元素

在redis的3.02版本还可以为zadd增加一些附加参数 ZADD key [NX|XX] [CH] [INCR] score member 

NX： 不存在的情况下   
XX： 存在的情况下（更新）   
CH： ??   
INCR: 使用该参数使得ZADD的功能类似ZINCRBY的功能

## 删

    zremrangebyscore key min max

    

    redis 127.0.0.1:6379> zremrangebyscore stu 4 10
    (integer) 2
    redis 127.0.0.1:6379> zrange stu 0 -1
    1) "f"

作用: 按照socre来删除元素,删除score在[min,max] (包括)之间的

    zrem key value1 value2 ..


作用: 删除集合中的元素

    zremrangebyrank key start end


    redis 127.0.0.1:6379> zremrangebyrank stu 0 1
    (integer) 2
    redis 127.0.0.1:6379> zrange stu 0 -1
    1) "c"
    2) "e"
    3) "f"
    4) "g"

作用: 按排名删除元素,删除名次在[start,end] (包括)之间的

## 改

    ZINCRBY key increment member


    

    redis> ZADD myzset 1 "one"
    (integer) 1
    redis> ZADD myzset 2 "two"
    (integer) 1
    redis> ZINCRBY myzset 2 "one"
    "3"
    redis> ZRANGE myzset 0 -1 WITHSCORES
    1) "two"
    2) "2"
    3) "one"
    4) "3"
    redis>

为有序集key的成员member的score值加上增量increment。如果key中不存在member，就在key中添加一个member，score是increment（就好像它之前的score是0.0）。如果key不存在，就创建一个只含有指定member成员的有序集合。

## 查

    zrange key start stop [withscores]


    127.0.0.1:6379> zrange yx1 0 3
    127.0.0.1:6379> zrange yx1 0 -1 withscores #取出所有以及它的分数

把集合排序后,返回名次[start,stop]的元素   
默认是升续排列,降序可以用zrevrange   
withscores 是把score也打印出来

    zrangebyscore key min max [withscores] limit offset N
    zrevrangebyscore key max min [withscores] limit offset N
    

    

    redis 127.0.0.1:6379> zadd stu 1 a 3 b 4 c 9 e 12 f 15 g
    (integer) 6
    redis 127.0.0.1:6379> zrangebyscore stu 3 12 limit 1 2 withscores #取3到12,并从1位开始取2位,连同分数一起取出来
    1) "c"
    2) "4"
    3) "e"
    4) "9"
    127.0.0.1:6379> ZREVRANGEBYSCORE stu 12 3 withscores
    1) "f"
    2) "12"
    3) "e"
    4) "9"
    5) "c"
    6) "4"
    7) "b"
    8) "3"

作用: 集合(升续|降序)排序后,取score在[min,max]内的元素,并跳过offset个, 取出N个

注意:zrange是按名次来取,zrangebyscore是按score的值来取;

    zcard key
    

返回集合元素个数

    zrank key member
    

查询member的排名(升序0名开始)

    zrevrank key memeber
    

查询 member的排名(降序0名开始)

    zcount key min max
    

返回[min,max] 区间内元素的数量

    zrevrange key start stop [withscores]
    

作用:把集合降序排列,取名次[start,stop]之间的元素

    zinterstore destination numkeys key1 [key2 ...] [weights weight [weight ...]]   [aggregate sum|min|max]
    

destination: 运算结果存放的集合名称   
numkeys: 参与运算的集合个数   
key1,key2...: 参与运算的集合名称   
weights: 权重   
aggregate: 聚合的方式sum|min|max 默认是sum;

    
    redis 127.0.0.1:6379> zadd z1 2 a 3 b 4 c
    (integer) 3

    redis 127.0.0.1:6379> zadd z2 2.5 a 1 b 8 d
    (integer) 3
    # 取z1和z2的交集

    redis 127.0.0.1:6379> zinterstore tmp 2 z1 z2
    (integer) 2

    redis 127.0.0.1:6379> zrange tmp 0 -1
    1) "b"
    2) "a"

    redis 127.0.0.1:6379> zrange tmp 0 -1 withscores
    1) "b"
    2) "4"
    3) "a"
    4) "4.5"
    # Aggregate sum->score相加,min->最小score的集合, max->最大score集合;
    # 可以通过weigth设置不同key的权重, 交集时,socre * weights

    redis 127.0.0.1:6379> zinterstore tmp 2 z1 z2 aggregate sum #默认是这个
    (integer) 2

    redis 127.0.0.1:6379> zrange tmp 0 -1 withscores
    1) "b"
    2) "4"
    3) "a"
    4) "4.5"

    redis 127.0.0.1:6379> zinterstore tmp 2 z1 z2 aggregate min #两个集合中的交集最小从新生成集合到tmp里面
    (integer) 2

    redis 127.0.0.1:6379> zrange tmp 0 -1 withscores
    1) "b"
    2) "1"
    3) "a"
    4) "2"

    redis 127.0.0.1:6379> zinterstore tmp 2 z1 z2 weights 1 2 #权重默认为1,当前的score的真正值就是score*weight 权重,比如z2里面的b未声明权重2前就是3,声明权重2就是6;声明权重后的运算都是按照权重后的值来运算
    (integer) 2

    redis 127.0.0.1:6379> zrange tmp 0 -1 withscores
    1) "b"
    2) "5"
    3) "a"
    4) "7"

更多请参考： [http://www.redis.cn/commands.html#sorted_set][0]

[0]: http://www.redis.cn/commands.html#sorted_set
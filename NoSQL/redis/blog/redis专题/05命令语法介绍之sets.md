# 【redis专题(5)】命令语法介绍之sets


关于 redis的无序集合有三个特点： 无序性, 确定性(描述准确) , 唯一性；

有点类似于数据容器；

## 增

    SADD key member1 [member2] 
    

作用: 往集合key中增加元素   
注意: 集合具有唯一性,已经存在就放不进;

## 删

    SREM key member1 [member2] 
    

作用: 删除集合中值为 value1 value2的元素   
返回值: 忽略不存在的元素后,真正删除掉的元素的个数

    SPOP key
    

作用: 返回并删除集合中key中1个随机元素,随机--体现了无序性

## 改

    SMOVE source dest value
    

作用:把集合source中的value移动到集合dest中   
注意:只能移动一个

## 查

    SMEMBERS key
    

作用: 返回集合中所有的元素

    SCARD key
    

作用: 返回集合中元素的个数

    SINTER key1 key2 key3
    

作用: 求出key1 key2 key3 三个集合中的交集,并返回

    sinterstore dest key1 key2 key3
    

作用: 求出key1 key2 key3 三个集合中的交集,并赋给dest

    SUNIONSTORE destination key1 [key2] 
    

作用: 所有给定集合的并集存储在 destination 集合中

    SDIFFSTORE destination key1 [key2] 
    

作用: 返回给定所有集合的差集并存储在 destination 中

    sunion key1 key2.. Keyn
    

作用: 求出key1 key2 keyn的并集,并返回

    sdiff key1 key2 key3 
    

作用: 求出key1与key2 key3的差集   
即key1-key2-key3   
差集:当前的集合中在另外一个集合没有的;

    SISMEMBER key member 
    

作用: 判断 member 元素是否是集合 key 的成员

    SRANDMEMBER key [count] 
    

作用: 返回集合中一个或多个随机数


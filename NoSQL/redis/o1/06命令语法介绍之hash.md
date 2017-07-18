# 【redis专题(6)】命令语法介绍之hash


可以把hash看做一个数组hset array key1 value2;，该数据类型特别适用于存储 

## 增

    hset key field value
    

作用: 把key中filed域的值设为value   
注:如果没有field域,直接添加,如果有,则覆盖原field域的值

    hsetnx key field value
    

作用: 将哈希表 key 中的域 field 的值设置为 value ，当且仅当域 field 不存在。   
若域 field 已经存在，该操作无效。   
如果 key 不存在，一个新哈希表被创建并执行 HSETNX 命令。

    hmset key field1 value1 [field2 value2 field3 value3 ......fieldn valuen]
    

作用: 设置field1->N 个域, 对应的值是value1->N   
(对应PHP理解为 $key = array(file1=>value1, field2=>value2 ....fieldN=>valueN))

## 删

    hdel key field
    

作用: 删除key中 field域

## 改

    hincrby key field value
    

作用: 是把key中的field域的值增长整型值value

    hincrbyfloat key field value
    

作用: 是把key中的field域的值增长浮点值value

## 查

    hget key field
    

作用: 返回key中field域的值

    hmget key field1 field2 fieldN
    

作用: 返回key中field1 field2 fieldN域的值

    hgetall key
    

作用:返回key中,所有域与其值

    hlen key
    

作用: 返回key中元素的数量

    hexists key field
    

作用: 判断key中有没有field域

    hkeys key
    

作用: 返回key中所有的field

    kvals key
    

作用: 返回key中所有的value


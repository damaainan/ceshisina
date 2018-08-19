## 使用PHP-redis操作Redis

来源：[https://www.helloweba.net/php/578.html](https://www.helloweba.net/php/578.html)

时间 2018-08-18 21:11:57


PHP-redis扩展提供了丰富的操作Redis的API接口，使用PHP-redis可以轻松操作Redis。本文整理常用的基础的Redis操作方法，作为笔记方便大家查阅。

  
Phpredis扩展的安装方法请参照本站文章：      [CentOS7安装Redis和PHP-redis扩展][0]

  
#### PHP连接Redis

我们一般使用`connect`连接Redis，`connect`方法为短连接，提供了主机、端口、连接超时等参数设置，如：

```php
$redis->connect('127.0.0.1', 6379);
$redis->connect('127.0.0.1'); // 默认端口6379，你可以设置为其他端口
$redis->connect('127.0.0.1', 6379, 2.5); // 2.5秒也连接不上则为超时断开
```

也可以使用`pconnect`或`popen`方法支持长连接，使用同上。

示例代码：

```php
<?php
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo "Connection to server sucessfully";
//查看服务是否运行
echo "Server is running: " . $redis->ping();
```

此外，phpredis还提供了验证密码，选择库和关闭连接释放资源、过期时间（用于缓存）、移动库等方法。

```php
$redis->auth('password');  //登录验证密码，返回【true | false】
$redis->select(0);  //选择redis库, 0~15 共16个库
$redis->close();  //释放资源
$redis->expire('key', 10);  //设置失效时间[true | false]
$redis->move('key', 15);  //把当前库中的key移动到15库中[0|1]
```

  
#### String字符串

Redis 字符串数据类型的相关命令用于管理 redis 字符串值。可以设置、获取、删除键key的值，可以对键值自增和自减等。

```php
$redis->set('key', 1);  //设置key=aa value=1 [true]
$redis->mset($arr);  //设置一个或多个键值[true]
$redis->setnx('key', 'value');  //key=value, key存在返回false[|true]
$redis->get('key');  //获取key [value]
$redis->mget($arr);  //(string|arr), 返回所查询键的值
$redis->del($key_arr);  //(string|arr)删除key，支持数组批量删除【返回删除个数】
$redis->delete($key_str, $key2, $key3);  //删除keys, [del_num]
$redis->getset('old_key', 'new_value');  //先获得key的值，然后重新赋值, [old_value | false]

$redis->strlen('key');  //获取当前key的长度
$redis->append('key', 'string');  //把string追加到key现有的value中[追加后的个数]
$redis->incr('key');  //自增1，如不存在key, 赋值为1(只对整数有效, 存储以10进制64位，redis中为str)[new_num | false]
$redis->incrby('key', $num);  //自增$num, 不存在为赋值, 值需为整数[new_num | false]
$redis->decr('key');  //自减1，[new_num | false]
$redis->decrby('key', $num);  //自减$num，[ new_num | false]
$redis->setex('key', 10, 'value');  //key=value，有效期为10秒[true]
```

  
#### List列表

Redis列表是简单的字符串列表，按照插入顺序排序。你可以添加一个元素到列表的头部（左边）或者尾部（右边）
一个列表最多可以包含 2      <sub>32</sub>
-1 个元素 (4294967295, 每个列表超过40亿个元素)。

```php
$redis->llen('key');  //返回列表key的长度, 不存在key返回0， [ len | 0]
$redis->lpush('key', 'value');  //增，只能将一个值value插入到列表key的表头(左侧)，不存在就创建 [列表的长度 |false]
$redis->rpush('key', 'value');  //增，只能将一个值value插入到列表key的表尾(右侧) [列表的长度 |false]
$redis->lInsert('key',  Redis::AFTER,  'value',  'new_value');  //增，将值value插入到列表key当中，位于值value之前或之后。[new_len | false]
$redis->lpushx('key', 'value');  //增，只能将一个值value插入到列表key的表头，不存在不创建 [列表的长度 |false]
$redis->rpushx('key', 'value');  //增，只能将一个值value插入到列表key的表尾，不存在不创建 [列表的长度 |false]
$redis->lpop('key');  //删，移除并返回列表key的头元素, [被删元素 | false]
$redis->rpop('key');  //删，移除并返回列表key的尾元素, [被删元素 | false]
$redis->lrem('key', 'value', 0);  //删，根据参数count的值，移除列表中与参数value相等的元素count=(0|-n表头向尾|+n表尾向头移除n个value)  [被移除的数量 | 0]
$redis->ltrim('key', start, end);  //删，列表修剪，保留(start, end)之间的值 [true|false]
$redis->lset('key', index, 'new_v');  //改，从表头数，将列表key下标为第index的元素的值为new_v,  [true | false]
$redis->lindex('key', index);  //查，返回列表key中，下标为index的元素[value|false]
$redis->lrange('key', 0, -1);  //查，(start, stop|0, -1)返回列表key中指定区间内的元素，区间以偏移量start和stop指定。[array|false]
```

  
#### Hash字典

Redis hash 是一个string类型的field和value的映射表，hash特别适合用于存储对象。Redis 中每个 hash 可以存储40多亿键值对。

```php
$redis->hset('key', 'field', 'value');  //增，改，将哈希表key中的域field的值设为value, 不存在创建, 存在就覆盖【1 | 0】
$redis->hget('key', 'field');  //查，取值【value|false】
$arr = array('one'=>1, 2, 3);$arr2 = array('one', 0, 1);
$redis->hmset('key', $arr);  //增，改，设置多值$arr为(索引|关联)数组, $arr[key]=field,  [ true ]
$redis->hmget('key', $arr2);  //查，获取指定下标的field，[$arr | false]
$redis->hgetall('key');  //查，返回哈希表key中的所有域和值。[当key不存在时，返回一个空表]
$redis->hkeys('key');  //查，返回哈希表key中的所有域。[当key不存在时，返回一个空表]
$redis->hvals('key');  //查，返回哈希表key中的所有值。[当key不存在时，返回一个空表]
$redis->hdel('key', $arr2);  //删，删除指定下标的field, 不存在的域将被忽略, [num | false]
$redis->hexists('key', 'field');  //查看hash中是否存在field, [1 | 0]
$redis->hincrby('key', 'field', $int_num);  //为哈希表key中的域field的值加上量(+|-)num, [new_num | false]
$redis->hlen('key');  //返回哈希表key中域的数量。[ num | 0]
```

  
#### Set集合

Redis 的 Set 是 String 类型的无序集合。集合成员是唯一的，这就意味着集合中不能出现重复的数据。
Redis 中集合是通过哈希表实现的，所以添加，删除，查找的复杂度都是 O(1)。

```php
$redis->sadd('key', 'value1', 'value2', 'valuen');  //增，改，将一个或多个member元素加入到集合key当中，已经存在于集合的member元素将被忽略。[insert_num]
$redis->srem('key', 'value1', 'value2', 'valuen');  //删，移除集合key中的一个或多个member元素，不存在的member元素会被忽略 [del_num | false]
$redis->smembers('key');  //查，返回集合key中的所有成员 [array | '']
$redis->sismember('key', 'member');  //判断member元素是否是集合key的成员 [1 | 0]
$redis->spop('key');  //删，移除并返回集合中的一个随机元素 [member | false]
$redis->srandmember('key');  //查，返回集合中的一个随机元素 [member | false]
$redis->sinter('key1', 'key2', 'keyn');  //查，返回所有给定集合的交集 [array | false]
$redis->sunion('key1', 'key2', 'keyn');  //查，返回所有给定集合的并集 [array | false]
$redis->sdiff('key1', 'key2', 'keyn');  //查，返回所有给定集合的差集 [array | false]
```

  
#### Sorted Set有序集合

Redis 有序集合和集合一样也是string类型元素的集合,且不允许重复的成员。
不同的是每个元素都会关联一个double类型的分数。redis正是通过分数来为集合中的成员进行从小到大的排序。
有序集合的成员是唯一的,但分数(score)却可以重复。

```php
$redis->zAdd('key', $score1, $member1, $scoreN, $memberN);  //增，改，将一个或多个member元素及其score值加入到有序集key当中。[num | 0]
$redis->zrem('key', 'member1', 'membern');  //删，移除有序集key中的一个或多个成员，不存在的成员将被忽略。[del_num | 0]
$redis->zscore('key', 'member');  //查, 通过值反拿权 [num | null]
$redis->zrange('key', $start, $stop);  //查，通过(score从小到大)【排序名次范围】拿member值，返回有序集key中，【指定区间内】的成员 [array | null]
$redis->zrevrange('key', $start, $stop);  //查，通过(score从大到小)【排序名次范围】拿member值，返回有序集key中，【指定区间内】的成员 [array | null]
$redis->zrangebyscore('key', $min, $max[, $config]);  //查，通过scroe权范围拿member值，返回有序集key中，指定区间内的(从小到大排)成员[array | null]
$redis->zrevrangebyscore('key', $max, $min[, $config]);  //查，通过scroe权范围拿member值，返回有序集key中，指定区间内的(从大到小排)成员[array | null]
$redis->zrank('key', 'member');  //查，通过member值查(score从小到大)排名结果中的【member排序名次】[order | null]
$redis->zrevrank('key', 'member');  //查，通过member值查(score从大到小)排名结果中的【member排序名次】[order | null]
$redis->ZINTERSTORE();  //交集
$redis->ZUNIONSTORE();  //差集
```

  
#### Server服务

Phpredis提供服务级别的操作方法，轻松管理redis。

```php
$redis->dbSize();  //返回当前库中的key的个数
$redis->flushAll();  //清空整个redis[总true]
$redis->flushDB();  //清空当前redis库[总true]
$redis->save();  //同步??把数据存储到磁盘-dump.rdb[true]
$redis->bgsave();  //异步？？把数据存储到磁盘-dump.rdb[true]
$redis->info();  //查询当前redis的状态 [verson:2.4.5....]
$redis->lastSave();  //上次存储时间key的时间[timestamp]

$redis->watch('key', 'keyn');  //监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断 [true]
$redis->unwatch('key', 'keyn');  //取消监视一个(或多个) key [true]
$redis->multi(Redis::MULTI);  //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
$redis->multi(Redis::PIPELINE);  //开启管道，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
$redis->exec();  //执行所有事务块内的命令，；【事务块内所有命令的返回值，按命令执行的先后顺序排列，当操作被打断时，返回空值 false】
```


[0]: https://www.helloweba.net/server/559.html
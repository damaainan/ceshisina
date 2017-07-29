# [30个php操作redis常用方法代码例子][0]

 2016-07-06 17:08  4652人阅读  

注意事项：

1、[Redis][5] 分服务端和客户端， set 和 get 是针对单个字符串

2、list 类型、 string 类型操作类似 [PHP][6] 的数组操作

这篇文章主要介绍了30个[php][6]操作[redis][5]常用方法代码例子,本文其实不止30个方法,可以操作string类型、list类型和set类型的数据,需要的朋友可以参考下

redis的操作很多的，以前看到一个比较全的博客，但是现在找不到了。查个东西搜半天，下面整理一下php处理redis的例子，个人觉得常用一些例子。下面的例子都是基于php-redis这个扩展的。

**1，connect**

描述：实例连接到一个Redis.  
参数：host: string，port: int  
返回值：BOOL 成功返回：TRUE;失败返回：FALSE

示例：

```php
<?php   
$redis = new redis();   
$result = $redis->connect('127.0.0.1', 6379);   
var_dump($result); //结果：bool(true)   
?> 
```
**2，set**描述：设置key和value的值  
参数：Key Value  
返回值：BOOL 成功返回：TRUE;失败返回：FALSE  
示例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$result = $redis->set('test',"11111111111");   
var_dump($result); //结果：bool(true)   
?> 
```
**3，get**

描述：获取有关指定键的值  
参数：key  
返回值：string或BOOL 如果键不存在，则返回 FALSE。否则，返回指定键对应的value值。  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$result = $redis->get('test');   
var_dump($result); //结果：string(11) "11111111111"   
?> 
```
**4，delete**
```
****描述：删除指定的键  
参数：一个键，或不确定数目的参数，每一个关键的数组：key1 key2 key3 … keyN  
返回值：删除的项数  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test',"1111111111111");   
echo $redis->get('test'); //结果：1111111111111   
$redis->delete('test');   
var_dump($redis->get('test')); //结果：bool(false)   
?> 
```
**5，setnx**

描述：如果在[数据库][7]中不存在该键，设置关键值参数  
参数：key value  
返回值：BOOL 成功返回：TRUE;失败返回：FALSE

范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test',"1111111111111");   
$redis->setnx('test',"22222222");   
echo $redis->get('test'); //结果：1111111111111   
$redis->delete('test');   
$redis->setnx('test',"22222222");   
echo $redis->get('test'); //结果：22222222   
?> 
```
**6，exists**

描述：验证指定的键是否存在  
参数key  
返回值：Bool 成功返回：TRUE;失败返回：FALSE  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test',"1111111111111");   
var_dump($redis->exists('test')); //结果：bool(true)   
?> 
```
**7，incr**

描述：数字递增存储键值键.  
参数：key value：将被添加到键的值  
返回值：INT the new value  
实例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test',"123");   
var_dump($redis->incr("test")); //结果：int(124)   
var_dump($redis->incr("test")); //结果：int(125)   
?>
```
**8，decr**

描述：数字递减存储键值。  
参数：key value：将被添加到键的值  
返回值：INT the new value  
实例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test',"123");   
var_dump($redis->decr("test")); //结果：int(122)   
var_dump($redis->decr("test")); //结果：int(121)   
?>
```
**9，getMultiple**

描述：取得所有指定键的值。如果一个或多个键不存在，该数组中该键的值为假  
参数：其中包含键值的列表数组  
返回值：返回包含所有键的值的数组  
实例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->set('test1',"1");   
$redis->set('test2',"2");   
$result = $redis->getMultiple(array('test1','test2'));   
print_r($result); //结果：Array ( [0] => 1 [1] => 2 )   
?>
```
**10，lpush**

描述：由列表头部添加字符串值。如果不存在该键则创建该列表。如果该键存在，而且不是一个列表，返回FALSE。  
参数：key,value  
返回值：成功返回数组长度，失败false  
实例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
var_dump($redis->lpush("test","111")); //结果：int(1)   
var_dump($redis->lpush("test","222")); //结果：int(2)   
?>
```
**11，rpush**

描述：由列表尾部添加字符串值。如果不存在该键则创建该列表。如果该键存在，而且不是一个列表，返回FALSE。  
参数：key,value  
返回值：成功返回数组长度，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
var_dump($redis->lpush("test","111")); //结果：int(1)   
var_dump($redis->lpush("test","222")); //结果：int(2)   
var_dump($redis->rpush("test","333")); //结果：int(3)   
var_dump($redis->rpush("test","444")); //结果：int(4)   
?>
```
**12，lpop**

描述：返回和移除列表的第一个元素  
参数：key  
返回值：成功返回第一个元素的值 ，失败返回false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush("test","111");   
$redis->lpush("test","222"); //多个lpush会把之前的覆盖  
$redis->rpush("test","333");   
$redis->rpush("test","444");   
var_dump($redis->lpop("test")); //结果：string(3) "222"   
?>
```
**13，lsize,llen**

描述：返回的列表的长度。如果列表不存在或为空，该命令返回0。如果该键不是列表，该命令返回FALSE。  
参数：Key  
返回值：成功返回数组长度，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush("test","111");   
$redis->lpush("test","222");   
$redis->rpush("test","333");   
$redis->rpush("test","444");   
var_dump($redis->lsize("test")); //结果：int(4)   
?>
```
**14，lget**

描述：返回指定键存储在列表中指定的元素。 0第一个元素，1第二个… -1最后一个元素，-2的倒数第二…错误的索引或键不指向列表则返回FALSE。  
参数：key index  
返回值：成功返回指定元素的值，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush("test","111");   
$redis->lpush("test","222");   
$redis->rpush("test","333");   
$redis->rpush("test","444");   
var_dump($redis->lget("test",3)); //结果：string(3) "444"   
?>
```
**15，lset**

描述：为列表指定的索引赋新的值,若不存在该索引返回false.  
参数：key index value  
返回值：成功返回true,失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush("test","111");   
$redis->lpush("test","222");   
var_dump($redis->lget("test",1)); //结果：string(3) "111"   
var_dump($redis->lset("test",1,"333")); //结果：bool(true)   
var_dump($redis->lget("test",1)); //结果：string(3) "333"   
?>
```
**16，lgetrange**

描述：  
返回在该区域中的指定键列表中开始到结束存储的指定元素，lGetRange(key, start, end)。0第一个元素，1第二个元素… -1最后一个元素，-2的倒数第二…  
参数：key start end  
返回值：成功返回查找的值，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush("test","111");   
$redis->lpush("test","222");   
print_r($redis->lgetrange("test",0,-1)); //结果：Array ( [0] => 222 [1] => 111 )   
?>
```
**17,lremove**

描述：从列表中从头部开始移除count个匹配的值。如果count为零，所有匹配的元素都被删除。如果count是负数，内容从尾部开始删除。  
参数：key count value  
返回值：成功返回删除的个数，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->lpush('test','a');   
$redis->lpush('test','b');   
$redis->lpush('test','c');   
$redis->rpush('test','a');   
print_r($redis->lgetrange('test', 0, -1)); //结果：Array ( [0] => c [1] => b [2] => a [3] => a )   
var_dump($redis->lremove('test','a',2)); //结果：int(2)   
print_r($redis->lgetrange('test', 0, -1)); //结果：Array ( [0] => c [1] => b )   
?>
```
**18，sadd**

描述：为一个Key添加一个值。如果这个值已经在这个Key中，则返回FALSE。  
参数：key value  
返回值：成功返回true,失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
var_dump($redis->sadd('test','111')); //结果：bool(true)   
var_dump($redis->sadd('test','333')); //结果：bool(true)   
print_r($redis->sort('test')); //排好序再输出结果：Array ( [0] => 111 [1] => 333 )   
?>
```
**19，sremove**

描述：删除Key中指定的value值  
参数：key member  
返回值：true or false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd('test','111');   
$redis->sadd('test','333');   
$redis->sremove('test','111');   
print_r($redis->sort('test')); //结果：Array ( [0] => 333 )   
?>
```
**20,smove**

描述：将Key1中的value移动到Key2中  
参数：srcKey dstKey member  
返回值：true or false  
范例

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->delete('test1');   
$redis->sadd('test','111');   
$redis->sadd('test','333');   
$redis->sadd('test1','222');   
$redis->sadd('test1','444');   
$redis->smove('test',"test1",'111');   
print_r($redis->sort('test1')); //将数组test中的键值111移动到test1中

结果：Array ( [0] => 111 [1] => 222 [2] => 444 )   
?>
```
**21，scontains**

描述：检查集合中是否存在指定的值。  
参数：key value  
返回值：true or false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd('test','111');   
$redis->sadd('test','112');   
$redis->sadd('test','113');   
var_dump($redis->scontains('test', '111')); //结果：bool(true)   
?>
```
**22,ssize**

描述：返回集合中存储值的数量  
参数：key  
返回值：成功返回数组个数，失败0  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd('test','111');   
$redis->sadd('test','112');   
echo $redis->ssize('test'); //结果：2   
?>
```
**23，spop**

描述：随机移除并返回key中的一个值  
参数：key  
返回值：成功返回删除的值，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
var_dump($redis->spop("test")); //结果：string(3) "333"   
?>
```
**24,sinter**

描述：返回一个所有指定键的交集。如果只指定一个键，那么这个命令生成这个集合的成员。如果不存在某个键，则返回FALSE。  
参数：key1, key2, keyN  
返回值：成功返回数组交集，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
var_dump($redis->sinter("test","test1")); 

//结果：array(2) { [0]=> string(3) "111" [1]=> string(3) "222" }  
?>
```
**25,sinterstore**

描述：执行sInter命令并把结果储存到新建的变量中。  
参数：  
Key: dstkey, the key to store the diff into.  
Keys: key1, key2… keyN. key1..keyN are intersected as in sInter.  
返回值：成功返回，交集的个数，失败false  
范例:

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
var_dump($redis->sinterstore('new',"test","test1")); //结果：int(1)   
var_dump($redis->smembers('new')); //结果:array(1) { [0]=> string(3) "111" }   
?>
```
**26,sunion**

描述：  
返回一个所有指定键的并集  
参数：  
Keys: key1, key2, … , keyN  
返回值：成功返回合并后的集，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
print_r($redis->sunion("test","test1")); //结果：Array ( [0] => 111 [1] => 222 [2] => 333 [3] => 444 )   
?>
```
**27,sunionstore**

描述：执行sunion命令并把结果储存到新建的变量中。  
参数：  
Key: dstkey, the key to store the diff into.  
Keys: key1, key2… keyN. key1..keyN are intersected as in sInter.  
返回值：成功返回，交集的个数，失败false  
范例:

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
var_dump($redis->sinterstore('new',"test","test1")); //结果：int(4)   
print_r($redis->smembers('new')); //结果:Array ( [0] => 111 [1] => 222 [2] => 333 [3] => 444 )   
?>
```
**28,sdiff**

描述：返回第一个集合中存在并在其他所有集合中不存在的结果  
参数：Keys: key1, key2, … , keyN: Any number of keys corresponding to sets in redis.  
返回值：成功返回数组，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
print_r($redis->sdiff("test","test1")); //结果：Array ( [0] => 222 [1] => 333 )   
?>
```
**29,sdiffstore**

描述：执行sdiff命令并把结果储存到新建的变量中。  
参数：  
Key: dstkey, the key to store the diff into.  
Keys: key1, key2, … , keyN: Any number of keys corresponding to sets in redis  
返回值：成功返回数字，失败false  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
$redis->sadd("test","333");   
$redis->sadd("test1","111");   
$redis->sadd("test1","444");   
var_dump($redis->sdiffstore('new',"test","test1")); //结果：int(2)   
print_r($redis->smembers('new')); //结果:Array ( [0] => 222 [1] => 333 )   
?>
```
**30,smembers, sgetmembers**

描述：  
返回集合的内容  
参数：Key: key  
返回值：An array of elements, the contents of the set.  
范例：

```php
<?php   
$redis = new redis();   
$redis->connect('127.0.0.1', 6379);   
$redis->delete('test');   
$redis->sadd("test","111");   
$redis->sadd("test","222");   
print_r($redis->smembers('test')); //结果:Array ( [0] => 111 [1] => 222 )   
?>
```
  
php-redis当中，有很多不同名字，但是功能一样的函数，例如：lrem和lremove，这里就不例举了。

[0]: http://blog.csdn.net/nuli888/article/details/51840744
[5]: http://lib.csdn.net/base/redis
[6]: http://lib.csdn.net/base/php
[7]: http://lib.csdn.net/base/mysql
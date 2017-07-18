# 【redis专题(12)】在PHP中使用redis（1）



> phpRedis的扩展并非官方扩展，具体的安装和使用可以参考以下链接：

[https://github.com/phpredis/phpredis][0]**本文主要为上面链接的中文笔记**

## session保存进redis

在php.ini里面做如下设置

    session.save_handler = redis
    session.save_path = "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2&read_timeout=2.5"
    

## 链接

connect, open 链接redis服务 

pconnect, popen 不会主动关闭的链接

host: string，服务地址   
port: int,端口号   
timeout: float,链接时长 (可选, 默认为 0 ，不限链接时间)   
注: 在redis.conf中也有时间，默认为300

auth 密码认证

$redis->auth('foobared');

select 数据库选择,从0开始,默认16个

close 断开与redis的链接,pconnect除外;

## 系统

setOption 设置redis客户端的模式

getOption 获取

ping() 查看链接状态

echo(string) 发送一个字符串给redis服务器 ??不知道有什么用;

bgrewriteaof() 使用aof来进行数据库持久化

bgsave() 将数据异步保存(后台保存,线程不阻塞)到硬盘

save() 将数据同步保存

config() config_get 和 config_set的使用

    

    $redis->config("GET", "*max-*-entries*"); //可以使用通配符*
    /*
    Array
    (
    [hash-max-ziplist-entries] => 512
    [list-max-ziplist-entries] => 512
    [set-max-intset-entries] => 512
    [zset-max-ziplist-entries] => 128
    )
    */
    $redis->config("SET", "dir", "/var/run/redis/dumps/");

dbSize() 查找当前数据库key的数量

flushAll() flushDB() 清理数据库

info() 

    

    $redis -> info('Replication');//主从配置信息;
    $redis -> info();//全部信息

lastSave() 上一次dump rdb的时间;

resetStat() 重置info中的统计数据

slaveof([ip,port]) 选择从服务器,没有参数就没有从服务器

time() 返回当前的服务器时间

slowlog('get') 慢查询

    

    $redis->slowlog('get', 10); //获取10条慢日志
    $redis->slowlog('get');//获取默认条数的慢日志
    $redis->slowlog('reset');//重置慢日志

## key与string

### 增

**set(key,value[,Timeout|Options Array ])**

参考:set key value [ex 秒数] / [px 毫秒数] [nx] /[xx]

    

    $redis->set('key','value', 10);//新建一个key,不过有效期只能保存10秒
    $redis->set('key', 'value', Array('nx', 'ex'=>10));//在key不存在时,添加key并10秒过期
    $redis->set('key', 'value', Array('xx', 'px'=>1000));//在key存在时,更新key并1000毫秒过期

**setex, psetex** 存在时写入

    

    $redis->setex('key', 3600, 'value');//3600秒过期
    $redis->psetex('key', 3600, 'value');//3600毫秒过期

**setnx(key,value)** 不存在时才写入;

**getSet** 返回原来key中的值，并将value写入key

    

    $redis->set('x', '42');
    $exValue = $redis->getSet('x', 'lol'); // return '42', replaces x by 'lol'
    $newValue = $redis->get('x'); // return 'lol'

**mset, msetnx** 批量设置

    

    $redis->mset(array('key0' => 'value0', 'key1' => 'value1'));

### 删

**del, delete** 删除,参数可以为数组;

    

    $redis->delete(array('key3', 'key4')); /* return 2 */

### 改

**incr, incrBy** 整数递增

    

    $redis->incr('key1');//如果key1不存在就设为0再加1;
    $redis->incrBy('key1', 10);

**incrByFloat** 浮点数递增

**decr, decrBy, decrByFloat** 递减，同上

**append(key,value)** 在key的后面追加value的string;

**setRange(key,offset,value)**

    

    $redis->set('key', 'Hello world');
    $redis->setRange('key', 6, "redis"); /* returns 11 */
    $redis->get('key'); /* "Hello redis" */

**setBit(key,offset,value)** 设置二进制key的offset位置上的值;

    

    $redis->set('key', "*");    // ord("*") = 42 = 0x2f = "0010 1010"
    $redis->setBit('key', 5, 1); /* returns 0 */
    $redis->setBit('key', 7, 1); /* returns 0 */
    $redis->get('key'); /* chr(0x2f) = "/" = b("0010 1111") */

**move()** 转移一个key到另外一个数据库

    

    $redis->select(0); // switch to DB 0
    $redis->set('x', '42'); // write 42 to x
    $redis->move('x', 1); // move to DB 1
    $redis->select(1); // switch to DB 1
    $redis->get('x'); // will return 42

**rename, renameKey** 给key重命名

**renameNx** 重命名的名称不存在时才命名;

**expire, setTimeout, pexpire** 设置key的生存时间,其中pexpire为耗秒,其它为秒;

**expireAt, pexpireAt** key存活到一个unix时间戳时间

    

    $redis->expireAt('x', time() + 3);

**persist** 移除生存时间到期的key,如果key到期true 如果不到期false

### 查(包括运算)

**get(key)**

**exists(key)** 判断是否存在

**mGet, getMultiple**

    

    $redis->mGet(array('key1', 'key2', 'key3'));//查找多个key;

**randomKey()** 随机来一个key

**keys, getKeys** 模式获取keys,参考前面的

    

    $allKeys = $redis->keys('*');   // all keys will match this.
    $keyWithUserPrefix = $redis->keys('user*');

**scan** 扫描出所有的key

    

    $it = NULL; /* Initialize our iterator to NULL */
    $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY); /* retry when we get no keys back */
    while($arr_keys = $redis->scan($it)) {
    foreach($arr_keys as $str_key) {
    echo "Here is a key: $str_key<br />"; //可以在这个地方对key做一些业务逻辑;
    }
    echo "No more keys to scan!\n";
    }

**type(key)** 返回key的类型;

1:string: Redis::REDIS_STRING   
2:set: Redis::REDIS_SET   
3:list: Redis::REDIS_LIST   
4:zset: Redis::REDIS_ZSET   
5:hash: Redis::REDIS_HASH   
6:other: Redis::REDIS_NOT_FOUND

**getRange(key,start,end)** 返回名称为key的string中start至end之间的字符

    

    $redis -> getRange('k1',0,-1);//从左到右从0开始,从右到左从-1开始;

**strlen(key)** 返回key的长度

**getBit(key,offset)** 返回二进制offset位置上的值;

    

    $redis->set('key', "\x7f"); // this is 0111 1111
    $redis->getBit('key', 0); /* 0 */
    $redis->getBit('key', 1); /* 1 */

**bitop** 二进制的逻辑运算,具体参数以上bitop

    operation: either "AND", "OR", "NOT", "XOR"
    ret_key: return key
    key1
    key2...
    

**bitcount(key)** 统计二进制key里面1的数量;

    

    $redis -> set('key1', "\x7f"); // this is 0111 1111
    $redis -> bitcount('key1'); //7
    $redis -> set('key2',"*"); //this is 0010 1010
    $redis -> bitcount('key2'); //3

**sort** 用于排序和分页

    

    #参数
    'by' => 'some_pattern_*',
    'limit' => array(0, 1),
    'get' => 'some_other_pattern_*' or an array of patterns, //左链接;
    'sort' => 'asc' or 'desc',
    'alpha' => TRUE,
    'store' => 'external-key'
    #例子
    $redis->delete('s');
    $redis->sadd('s', 5);
    $redis->sadd('s', 4);
    $redis->sadd('s', 2);
    $redis->sadd('s', 1);
    $redis->sadd('s', 3);
    var_dump($redis->sort('s')); // 1,2,3,4,5
    var_dump($redis->sort('s', array('sort' => 'desc'))); // 5,4,3,2,1
    var_dump($redis->sort('s', array('sort' => 'desc', 'store' => 'out'))); // (int)5
    $redis -> sort('newusers',array('by' => 'not-exists-key','get' =>array('#','user:uid:*->username','user:uid:*->password')));

**ttl, pttl** 获取key的生存时间,pttl为微秒;返回-1就代表不过期;

### 其他

**object**

    

    //OBJECT
    $redis->select(8);
    echo '<br><br>OBJECT<br>';
    $redis->SET('game',"WOW");  # 设置一个字符串
    $redis->OBJECT('REFCOUNT','game');  # 只有一个引用
    //sleep(5);
    echo $redis->OBJECT('IDLETIME','game');  # 等待一阵。。。然后查看空转时间 //(integer) 10
    //echo $redis->GET('game');  # 提取game， 让它处于活跃(active)状态  //return WOW
    //echo $redis->OBJECT('IDLETIME','game');  # 不再处于空转 //(integer) 0
    var_dump($redis->OBJECT('ENCODING','game'));  # 字符串的编码方式 //string(3) "raw"
    $redis->SET('phone',15820123123);  # 大的数字也被编码为字符串
    var_dump($redis->OBJECT('ENCODING','phone')); //string(3) "raw"
    $redis->SET('age',20);  # 短数字被编码为int
    var_dump($redis->OBJECT('ENCODING','age')); //string(3) "int"

**dump,restore**

    

    $redis->set('foo', 'bar');
    $val = $redis->dump('foo');
    $redis->restore('bar', 0, $val); // The key 'bar', will now be equal to the key 'foo'

## Hashes

### 增

**hSet**

    

    $redis->hSet('h', 'key1', 'hello'); /* 1, 'key1' => 'hello' in the hash at "h" */

**hSetNx**

    

    #只有没有的时候才添加
    $redis->hSetNx('h', 'key1', 'hello'); /* TRUE, 'key1' => 'hello' in the hash at "h" */
    $redis->hSetNx('h', 'key1', 'world'); /* FALSE, 'key1' => 'hello' in the hash at "h". No change since the field wasn't replaced. */

**hMSet**

    

    $redis->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
    $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.

### 删

**hDel**

    

    $redis -> hDel('hash1','field3');//删除整个hash用通用的delete方法;删除hash中的某个field用hDel;

### 改

**hIncrBy**

    

    $redis -> hIncrBy('hash1','field1',1);//第三参数不能省略,并且只能操作整数

**hIncrByFloat** 略

### 查

**hGet**

    

    $redis->hGet('h','key1')

**hLen**

    

    $redis->hLen('h');//h中field的数量;

**hKeys**

    

    $redis -> hKeys('hash1');//以数组的形式返回hash1中所有的fields名称;

**hVals**

    

    $redis -> hVals('hash1');//以数组的形式返回hash1中所有的fields的值;

**hGetAll**

    

    $redis -> hGetAll('hash1');//以数组的形式返回hash1中所有的fields的名称与值;

**hmGet**

    

    $redis->hmGet('h', array('field1', 'field2')); /* returns array('field1' => 'value1', 'field2' => 'value2') */

**hScan**

    

    #对hash的一个迭代方法,但是还不如hGetAll出来以后foreach;
    $it = NULL;
    $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
    while($arr_keys = $redis->hscan('hash', $it)) {
    foreach($arr_keys as $str_field => $str_value) {
    echo "$str_field => $str_value\n"; /* Print the hash member and value */
    }
    }

**hExists**

    

    $redis -> hExists('hash1','field1'); //判断hash1中是否有值field1

## Lists

### 增

**lPush/rPush**

    

    $redis -> lPush('list','A');
    $redis -> lPush('list','a','b','c');//从尾部压入,返回list的数量

**lPushx/rPushx** #如果存在则不添加

**lInsert**

    

    $redis->lInsert('list', Redis::AFTER, 'c', 'C');
    $redis->lInsert('list', Redis::BEFORE, 'c', 'B');
    //在c的前后加上B,C;return list的count;

### 删

**lPop/rPop** 从头/尾弹出

**lRem, lRemove**

    

    $redis->lRem('key1', 'A', 2);//删除链表key1中的两个A;

### 改

**lSet**

    

    $r = $redis->lSet('list', 0,'ha');//更改索引0处的值为'ha
    '```
    **lTrim, listTrim**
    ```php
    $r =  $redis->lTrim('list', 2,5);//截取list,2到5之间的元素,包括2和5
    <div class="md-section-divider"></div>

### 查

**lIndex, lGet**

    

    $r = $redis -> lindex('list2',2);//获得索引2上的数
    <div class="md-section-divider"></div>

**lRange, lGetRange**

    

    $redis->lRange('key1', 0, -1); //获得全部
    <div class="md-section-divider"></div>

**lGet**

    

    $redis->lGet('list', 0);//获得索引0处的值
    <div class="md-section-divider"></div>

**lLen, lSize**

    

    $redis->lLen('list'); //list的count
    <div class="md-section-divider"></div>

### 其他

**blPop, brPop**

lpop,和rpop的阻塞版本,用于监听list在timeout时间内是否有新值插入,有新值插入就弹出头部或尾部的值,在长轮询Ajax中用到;

    

    $redis->blPop('key1', 'key2', 10); /* array('key1', 'A') */
    /* OR */
    $redis->blPop(array('key1', 'key2'), 10); /* array('key1', 'A') */
    //阻塞效果的前提是key1和key2里面没有值
    <div class="md-section-divider"></div>

**brpoplpush** rpoplpush的阻塞版本

    

    $r = $redis -> brpoplpush('list1','list2',60);//60秒内监听list1,如果有新值插入就弹出并压入list2的头部
    <div class="md-section-divider"></div>

**rpoplpush**

    

    $redis -> rpoplpush('list1','list2');
    <div class="md-section-divider"></div>

## Sets

### 增

**sAdd**

    

    $redis->sAdd('key1' , 'member2', 'member3');
    <div class="md-section-divider"></div>

### 删

**sRem, sRemove**

    

    $redis->sRem('key1', 'member2', 'member3');//删除一个或多个键;
    <div class="md-section-divider"></div>

### 改

**sMove**

    

    $redis->sMove('key1', 'key2', 'member13');#把key1中的member13转移到key2
    <div class="md-section-divider"></div>

**sPop**

    

    $redis->sPop('key1');#随机删除集合key1中的某个元素
    <div class="md-section-divider"></div>

### 查

**sCard, sSize**

    

    $redis -> sCard('key1'); #key1集合的count
    <div class="md-section-divider"></div>

**sDiff**

    

    $redis -> sDiff('set1','set2','set3');#求差集,返回数组
    <div class="md-section-divider"></div>

**sDiffStore**

    

    $redis->sDiffStore('dst', 's0', 's1', 's2');#求差集,结果存入dst集合里面;
    <div class="md-section-divider"></div>

**sInter**

    

    $redis -> sInter('set1','set2','set3');#求交集,返回数组
    <div class="md-section-divider"></div>

**sInterStore**

    

    $redis->sInterStore('output', 'key1', 'key2', 'key3');#求交集,结果存入output
    <div class="md-section-divider"></div>

**sIsMember, sContains**

    

    $redis->sIsMember('key1', 'member1');#检测member1是否存在key1中,如果存在就返回true
    <div class="md-section-divider"></div>

**sMembers, sGetMembers**

    

    $redis->sMembers('s');#全部取出s集合
    <div class="md-section-divider"></div>

**sMove**

    

    $redis->sMove('key1', 'key2', 'member13');#把key1中的member13转移到key2
    <div class="md-section-divider"></div>

**sRandMember**

    

    $redis->sRandMember('key1');#随机取出一个元素

[0]: https://github.com/phpredis/phpredis
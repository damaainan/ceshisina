<font face=微软雅黑>

> 排行榜top100

相关函数 zAdd + lRange

使用方式 使用zAdd记录每个value的分数值，字段即可实现排序，再进行lRange进行取前100，就实现了排行榜的效果

> 计数器

相关函数 incr + decr

使用方式 设置一个初始值为1的key，对其进行incr/decr操作，进行计数的功能。

> 队列

相关函数 rPush + lPop + lSize

使用方式 对一个key进行rPush关键字，再lPop取出关键字进行相关的业务处理，直至lSize为0

> 接口频率限制

相关函数 incr + expire

使用方式 根据ip与时间(粒度可自定义，比如每小时)为key值进行incr计数，并设置expire有效时间，在有效时间内次数大于阀值则给相关的限制

> 数据池

相关函数 hSet + hLen + hKeys

使用方式 根据key值进行hSet设置数据池中的数据，hLen查询数据池的数量，若有则hKeys取之

> 在线时长

相关函数 setEx + get + set + hIncrBy + incrBy + ttl + expireAt + hGetAll

使用方式 先setEx记录上一次操作时间，并用get获取与当前时间对比，若低于某阀值，则视为在线，set更新上一次在线时间，同时hIncrBy当前小时的在线时长(注意59分的情况)，incrBy当天总的在线时长，ttl检测总时长是否设置了过期时间(以免垃圾数据)，并expireAt设置到某时过期，另走队列hGetAll取出当天每小时的在线时间进行记录。


---

**简单字符串缓存实战**

    $redis->connect('127.0.0.1', 6379);
    $strCacheKey  = 'Test_bihu';
    
    //SET 应用
    $arrCacheData = [
        'name' => 'job',
        'sex'  => '男',
        'age'  => '30'
    ];
    $redis->set($strCacheKey, json_encode($arrCacheData));
    $redis->expire($strCacheKey, 30);  # 设置30秒后过期
    $json_data = $redis->get($strCacheKey);
    $data = json_decode($json_data);
    print_r($data->age); //输出数据
    
    //HSET 应用
    $arrWebSite = [
        'google' => [
            'google.com',
            'google.com.hk'
        ],
    ];
    $redis->hSet($strCacheKey, 'google', json_encode($arrWebSite['google']));
    $json_data = $redis->hGet($strCacheKey, 'google');
    $data = json_decode($json_data);
    print_r($data); //输出数据

**简单队列实战**

    $redis->connect('127.0.0.1', 6379);
    $strQueueName  = 'Test_bihu_queue';
    
    //进队列
    $redis->rpush($strQueueName, json_encode(['uid' => 1,'name' => 'Job']));
    $redis->rpush($strQueueName, json_encode(['uid' => 2,'name' => 'Tom']));
    $redis->rpush($strQueueName, json_encode(['uid' => 3,'name' => 'John']));
    echo "---- 进队列成功 ---- <br /><br />";
    
    //查看队列
    $strCount = $redis->lrange($strQueueName, 0, -1);
    echo "当前队列数据为： <br />";
    print_r($strCount);
    
    //出队列
    $redis->lpop($strQueueName);
    echo "<br /><br /> ---- 出队列成功 ---- <br /><br />";
    
    //查看队列
    $strCount = $redis->lrange($strQueueName, 0, -1);
    echo "当前队列数据为： <br />";
    print_r($strCount);

**简单发布订阅实战**

    //以下是 pub.php 文件的内容 cli下运行
    ini_set('default_socket_timeout', -1);
    $redis->connect('127.0.0.1', 6379);
    $strChannel = 'Test_bihu_channel';
    
    //发布
    $redis->publish($strChannel, "来自{$strChannel}频道的推送");
    echo "---- {$strChannel} ---- 频道消息推送成功～ <br/>";
    $redis->close();

    //以下是 sub.php 文件内容 cli下运行
    ini_set('default_socket_timeout', -1);
    $redis->connect('127.0.0.1', 6379);
    $strChannel = 'Test_bihu_channel';
    
    //订阅
    echo "---- 订阅{$strChannel}这个频道，等待消息推送...----  <br/><br/>";
    $redis->subscribe([$strChannel], 'callBackFun');
    function callBackFun($redis, $channel, $msg)
    {
        print_r([
            'redis'   => $redis,
            'channel' => $channel,
            'msg'     => $msg
        ]);
    }

**简单计数器实战**

    $redis->connect('127.0.0.1', 6379);
    $strKey = 'Test_bihu_comments';
    
    //设置初始值
    $redis->set($strKey, 0);
    
    $redis->INCR($strKey);  //+1
    $redis->INCR($strKey);  //+1
    $redis->INCR($strKey);  //+1
    
    $strNowCount = $redis->get($strKey);
    
    echo "---- 当前数量为{$strNowCount}。 ---- ";

**排行榜实战**

    $redis->connect('127.0.0.1', 6379);
    $strKey = 'Test_bihu_score';
    
    //存储数据
    $redis->zadd($strKey, '50', json_encode(['name' => 'Tom']));
    $redis->zadd($strKey, '70', json_encode(['name' => 'John']));
    $redis->zadd($strKey, '90', json_encode(['name' => 'Jerry']));
    $redis->zadd($strKey, '30', json_encode(['name' => 'Job']));
    $redis->zadd($strKey, '100', json_encode(['name' => 'LiMing']));
    
    $dataOne = $redis->ZREVRANGE($strKey, 0, -1, true);
    echo "---- {$strKey}由大到小的排序 ---- <br /><br />";
    print_r($dataOne);
    
    $dataTwo = $redis->ZRANGE($strKey, 0, -1, true);
    echo "<br /><br />---- {$strKey}由小到大的排序 ---- <br /><br />";
    print_r($dataTwo);

**简单字符串悲观锁实战**

解释：悲观锁(Pessimistic Lock), 顾名思义，就是很悲观。

每次去拿数据的时候都认为别人会修改，所以每次在拿数据的时候都会上锁。

场景：如果项目中使用了缓存且对缓存设置了超时时间。

当并发量比较大的时候，如果没有锁机制，那么缓存过期的瞬间，

大量并发请求会穿透缓存直接查询数据库，造成雪崩效应。

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean
     */
    public function lock($key = '', $expire = 5) {
        $is_lock = $this->_redis->setnx($key, time()+$expire);
        //不能获取锁
        if(!$is_lock){
            //判断锁是否过期
            $lock_time = $this->_redis->get($key);
            //锁已过期，删除锁，重新获取
            if (time() > $lock_time) {
                unlock($key);
                $is_lock = $this->_redis->setnx($key, time() + $expire);
            }
        }
    
        return $is_lock? true : false;
    }
    
    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key = ''){
        return $this->_redis->del($key);
    }
    
    // 定义锁标识
    $key = 'Test_bihu_lock';
    
    // 获取锁
    $is_lock = lock($key, 10);
    if ($is_lock) {
        echo 'get lock success<br>';
        echo 'do sth..<br>';
        sleep(5);
        echo 'success<br>';
        unlock($key);
    } else { //获取锁失败
        echo 'request too frequently<br>';
    }

**简单事务的乐观锁实战**

解释：乐观锁(Optimistic Lock), 顾名思义，就是很乐观。

每次去拿数据的时候都认为别人不会修改，所以不会上锁。

watch命令会监视给定的key，当exec时候如果监视的key从调用watch后发生过变化，则整个事务会失败。

也可以调用watch多次监视多个key。这样就可以对指定的key加乐观锁了。

注意watch的key是对整个连接有效的，事务也一样。

如果连接断开，监视和事务都会被自动清除。

当然了exec，discard，unwatch命令都会清除连接中的所有监视。

    $strKey = 'Test_bihu_age';
    
    $redis->set($strKey,10);
    
    $age = $redis->get($strKey);
    
    echo "---- Current Age:{$age} ---- <br/><br/>";
    
    $redis->watch($strKey);
    
    // 开启事务
    $redis->multi();
    
    //在这个时候新开了一个新会话执行
    $redis->set($strKey,30);  //新会话
    
    echo "---- Current Age:{$age} ---- <br/><br/>"; //30
    
    $redis->set($strKey,20);
    
    $redis->exec();
    
    $age = $redis->get($strKey);
    
    echo "---- Current Age:{$age} ---- <br/><br/>"; //30
    
    //当exec时候如果监视的key从调用watch后发生过变化，则整个事务会失败


--- 

下面列出11种Web应用场景，在这些场景下可以充分的利用Redis的特性，大大提高效率。

1.在主页中显示最新的项目列表

Redis使用的是常驻内存的缓存，速度非常快。LPUSH用来插入一个内容ID，作为关键字存储在列表头部。LTRIM用来限制列表中的项目数最多为5000。如果用户需要的检索的数据量超越这个缓存容量，这时才需要把请求发送到数据库。

2.删除和过滤

如果一篇文章被删除，可以使用LREM从缓存中彻底清除掉。

3.排行榜及相关问题

排行榜(leader board)按照得分进行排序。ZADD命令可以直接实现这个功能，而ZREVRANGE命令可以用来按照得分来获取前100名的用户，ZRANK可以用来获取用户排名，非常直接而且操作容易。

4.按照用户投票和时间排序

这就像Reddit的排行榜，得分会随着时间变化。LPUSH和LTRIM命令结合运用，把文章添加到一个列表中。一项后台任务用来获取列表，并重新计算列表的排序，ZADD命令用来按照新的顺序填充生成列表。列表可以实现非常快速的检索，即使是负载很重的站点。

5.过期项目处理

使用unix时间作为关键字，用来保持列表能够按时间排序。对current_time和time_to_live进行检索，完成查找过期项目的艰巨任务。另一项后台任务使用ZRANGE...WITHSCORES进行查询，删除过期的条目。

6.计数

进行各种数据统计的用途是非常广泛的，比如想知道什么时候封锁一个IP地址。INCRBY命令让这些变得很容易，通过原子递增保持计数;GETSET用来重置计数器;过期属性用来确认一个关键字什么时候应该删除。

7.特定时间内的特定项目

这是特定访问者的问题，可以通过给每次页面浏览使用SADD命令来解决。SADD不会将已经存在的成员添加到一个集合。

8.实时分析正在发生的情况，用于数据统计与防止垃圾邮件等

使用Redis原语命令，更容易实施垃圾邮件过滤系统或其他实时跟踪系统。

9.Pub/Sub

在更新中保持用户对数据的映射是系统中的一个普遍任务。Redis的pub/sub功能使用了SUBSCRIBE、UNSUBSCRIBE和PUBLISH命令，让这个变得更加容易。

10.队列

在当前的编程中队列随处可见。除了push和pop类型的命令之外，Redis还有阻塞队列的命令，能够让一个程序在执行时被另一个程序添加到队列。你也可以做些更有趣的事情，比如一个旋转更新的RSS feed队列。

11.缓存

Redis缓存使用的方式与memcache相同。

</font>
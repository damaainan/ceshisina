
> Redis::__construct

说明：创建一个Redis客户端   
范例：

    $redis = new Redis();


> connect, open

说明：实例连接到一个Redis.   
参数：   
Host：string，可以是一个host地址，也可以是一个unix socket   
port: int   
timeout: float 秒数，（可选参数，默认值为0代表不限制）   
返回值：BOOL 成功返回：TRUE;失败返回：FALSE   
范例：

    $redis->connect('127.0.0.1', 6379);
    $redis->connect('127.0.0.1'); // port 6379 by default
    $redis->connect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout.
    $redis->connect('/tmp/redis.sock'); // unix domain socket.


> pconnect, popen

说明：实例连接到一个Redis.，或者连接到一个已经通过pconnect/popen创建的连接上。连接直到遇到close或者[PHP][8]进程结束才会被关闭。   
参数：   
host: string   
port: int   
timeout: float   
persistent_id: string 持久链接的身份验证   
返回值：BOOL 成功返回：TRUE;失败返回：FALSE   
范例：

    $redis->pconnect('127.0.0.1', 6379);
    $redis->pconnect('127.0.0.1'); // port 6379 by default - same connection like before.
    $redis->pconnect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout and would be another connection than the two before.
    $redis->pconnect('127.0.0.1', 6379, 2.5, 'x'); // x is sent as persistent_id and would be another connection the the three before.
    $redis->pconnect('/tmp/redis.sock'); // unix domain socket - would be another connection than the four before.
    close


说明：断开一个Redis实例连接，除非他是通过pconnect 链接的。

> setOption

说明：创建客户端选项。   
参数：Name Value   
返回值：BOOL 成功返回：TRUE;失败返回：FALSE   
范例：

    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);   // don't serialize data
    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);    // use built-in serialize/unserialize
    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);   // use igBinary serialize/unserialize
    $redis->setOption(Redis::OPT_PREFIX, 'myAppName:'); // use custom prefix on all keys


> getOption

说明：获得客户端选项   
参数：Name   
返回值：Value   
范例：

    $redis->getOption(Redis::OPT_SERIALIZER);   // return Redis::SERIALIZER_NONE, Redis::SERIALIZER_PHP, or Redis::SERIALIZER_IGBINARY.


> ping

说明：检查当前的连接状态。   
参数：无   
返回值：STRING：PONG 失败则会返回一个Redis抛出的连接异常。

> get

说明：获得一个指定的key的值。   
参数：Key   
返回值：String or Bool: 如果值存在则返回值，否则返回false。   
范例：

    $redis->get('key');


> set

说明：创建一个值   
参数：Key Value Timeout （可选）可以在一定的timeout时间内让SETEX 优先调用。   
返回值：成功返回true   
范例：

    $redis->set('key', 'value');


> setex   
> 说明：创建一个有一定存活时间的值   
> 参数：Key TTL Value   
> 返回值：成功返回true   
> 范例：

    $redis->setex('key', 3600, 'value'); // sets key → value, with 1h TTL.


> setnx

如果key的值不存在，则创建key的值为value   
参数：Key Value   
返回值：成功返回true 失败返回false   
范例：

    $redis->setnx('key', 'value'); /* return TRUE */
    $redis->setnx('key', 'value'); /* return FALSE */

> del, delete

说明：删除一个指定的key的值   
参数：可以是一个数组，也可以是一个多个字符串。   
返回值：成功删除的个数   
范例：

    $redis->set('key1', 'val1');
    $redis->set('key2', 'val2');
    $redis->set('key3', 'val3');
    $redis->set('key4', 'val4');
    
    $redis->delete('key1', 'key2'); /* return 2 */
    $redis->delete(array('key3', 'key4')); /* return 2 */


> multi, exec, discard.

说明：进入或者退出事务模式   
参数：(可选)   
Redis::MULTI或Redis::PIPELINE. 默认是 Redis::MULTI   
Redis::MULTI：将多个操作当成一个事务执行   
Redis::PIPELINE:让（多条）执行命令简单的，更加快速的发送给服务器，但是没有任何原子性的保证   
discard:删除一个事务   
返回值：   
multi()，返回一个redis对象，并进入multi-mode模式，一旦进入multi-mode模式，以后调用的所有方法都会返回相同的对象，只到exec(）方法被调用。   
范例：

    $ret = $redis->multi()
        ->set('key1', 'val1')
        ->get('key1')
        ->set('key2', 'val2')
        ->get('key2')
        ->exec();
    /*
    $ret == array(
        0 => TRUE,
        1 => 'val1',
        2 => TRUE,
        3 => 'val2');
    */

> watch, unwatch

说明：   
监测一个key的值是否被其它的程序更改。如果这个key在watch 和 exec （方法）间被修改，这个 MULTI/EXEC 事务的执行将失败（return false）   
unwatch 取消被这个程序监测的所有key   
参数：Keys:一对key的列表   
范例：

    $redis->watch('x');
    /* long code here during the execution of which other clients could well modify `x` */
    $ret = $redis->multi()
        ->incr('x')
        ->exec();
    /*
    $ret = FALSE if x has been modified between the call to WATCH and the call to EXEC.
    */

> subscribe

说明：方法回调。注意，该方法可能在未来里发生改变   
参数：channels: array callback: 回调函数名   
范例：

    function f($redis, $chan, $msg) {
        switch($chan) {
            case 'chan-1':
                ...
                break;
    
            case 'chan-2':
                ...
                break;
    
            case 'chan-2':
                ...
                break;
        }
    }
    
    $redis->subscribe(array('chan-1', 'chan-2', 'chan-3'), 'f'); // subscribe to 3 chans

> Publish

说明：发表内容到某一个通道。注意，该方法可能在未来里发生改变   
参数：Channel： Messsage：string   
范例：

    $redis->publish('chan-1', 'hello, world!'); // send message.

> exists

说明：验证指定的值是否存在   
参数：Key   
返回值：成功返回true 失败返回false   
范例：

    $redis->set('key', 'value');
    $redis->exists('key'); /*  TRUE */
    $redis->exists('NonExistingKey'); /* FALSE */


> incr, incrBy   
> 说明：key中的值进行自增.如果第二个参数存在，它将被用来作为整数值递增   
> 参数：Key Value   
> 返回值：返回新value   
> 范例：

    $redis->incr('key1'); /* key1 didn't exists, set to 0 before the increment */
    /* and now has the value 1  */
    
    $redis->incr('key1'); /* 2 */
    $redis->incr('key1'); /* 3 */
    $redis->incr('key1'); /* 4 */
    $redis->incrBy('key1', 10); /* 14 */


> decr, decrBy

说明：删掉key中的值，用法同incr   
范例：

    $redis->decr('key1'); /* key1 didn't exists, set to 0 before the increment */
    /* and now has the value -1  */
    $redis->decr('key1'); /* -2 */
    $redis->decr('key1'); /* -3 */
    $redis->decrBy('key1', 10); /* -13 */


> getMultiple

说明：返回一组数据的值，如果这个数组中的key值不存在，则返回false   
参数：Array   
返回值：Array   
范例：

    $redis->set('key1', 'value1');
    $redis->set('key2', 'value2');
    $redis->set('key3', 'value3');
    $redis->getMultiple(array('key1', 'key2', 'key3')); /* array('value1', 'value2', 'value3');
    $redis->getMultiple(array('key0', 'key1', 'key5')); /* array(`FALSE`, 'value2', `FALSE`);

> lPush

说明：在名称为key的list左边（头）添加一个值为value的 元素，如果这个key值不存在则创建一个。如果key值存在并且不是一个list，则返回false   
参数：Key Value   
返回值：返回key值得长度。   
范例：

    $redis->delete('key1');
    $redis->lPush('key1', 'C'); // returns 1
    $redis->lPush('key1', 'B'); // returns 2
    $redis->lPush('key1', 'A'); // returns 3
    /* key1 now points to the following list: [ 'A', 'B', 'C' ] */


> rPush   
> 说明：   
> 在名称为key的list右边（尾）添加一个值为value的 元素，如果这个key值不存在则创建一个。如果key值存在并且不是一个list，则返回false   
> 参数：Key Value   
> 返回值：返回key值得长度。   
> 范例：

    $redis->delete('key1');
    $redis->rPush('key1', 'A'); // returns 1
    $redis->rPush('key1', 'B'); // returns 2
    $redis->rPush('key1', 'C'); // returns 3
    /* key1 now points to the following list: [ 'A', 'B', 'C' ] */


> lPushx

说明：在名称为key的list左边（头）添加一个值为value的 元素，如果这个value存在则不添加。   
参数：Key Value   
返回值：返回key值得长度。   
范例：

    $redis->delete('key1');
    $redis->lPushx('key1', 'A'); // returns 0
    $redis->lPush('key1', 'A'); // returns 1
    $redis->lPushx('key1', 'B'); // returns 2
    $redis->lPushx('key1', 'C'); // returns 3
    /* key1 now points to the following list: [ 'A', 'B', 'C' ] */

> rPushx

说明：在名称为key的list右边（尾）添加一个值为value的 元素，如果这个value存在则不添加。   
参数：Key Value   
返回值：返回key值得长度。   
范例：

    $redis->delete('key1');
    $redis->rPushx('key1', 'A'); // returns 0
    $redis->rPush('key1', 'A'); // returns 1
    $redis->rPushx('key1', 'B'); // returns 2
    $redis->rPushx('key1', 'C'); // returns 3
    /* key1 now points to the following list: [ 'A', 'B', 'C' ] */


> lPop

说明：输出名称为key的list左(头)起起的第一个元素，删除该元素   
参数：Key   
返回值：失败返回false   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
    $redis->lPop('key1'); /* key1 => [ 'B', 'C' ] */

> rPop

说明：输出名称为key的list右(尾)起起的第一个元素，删除该元素   
参数：Key   
返回值：失败返回false   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
    $redis->rPop('key1'); /* key1 => [ 'A', 'B' ] */


> blPop, brPop

说明：lpop命令的block版本。即当timeout为0时，若遇到名称为key 的list不存在或该list为空，则命令结束。如果timeout>0，则遇到上述情况时，等待timeout秒，如果问题没有解决，则对key+1开始的list执行pop操作   
参数：Key Timeout   
返回值：Array array(‘listName’, ‘element’)   
范例：

    /* Non blocking feature */
    $redis->lPush('key1', 'A');
    $redis->delete('key2');
    
    $redis->blPop('key1', 'key2', 10); /* array('key1', 'A') */
    /* OR */
    $redis->blPop(array('key1', 'key2'), 10); /* array('key1', 'A') */
    
    $redis->brPop('key1', 'key2', 10); /* array('key1', 'A') */
    /* OR */
    $redis->brPop(array('key1', 'key2'), 10); /* array('key1', 'A') */
    
    /* Blocking feature */
    
    /* process 1 */
    $redis->delete('key1');
    $redis->blPop('key1', 10);
    /* blocking for 10 seconds */
    
    /* process 2 */
    $redis->lPush('key1', 'A');
    
    /* process 1 */
    /* array('key1', 'A') is returned*/


> lSize

说明：返回这个key值list的个数，如果这个list不存在或为空，则返回0，如果这个值得类型并不是一个list则返回false。   
参数：Key   
返回值：Long or bool   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
    $redis->lSize('key1');/* 3 */
    $redis->rPop('key1');
    $redis->lSize('key1');/* 2 */


> lIndex, lGet   
> 说明：返回名称为key的list中index位置的元素，0代表第一个，1代表第二个，-1代表最后一个，-2代表倒数第二个，当这个key值不存在于list中时，返回false。   
> 参数：key index   
> 返回值：String or false   
> 范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
    $redis->lGet('key1', 0); /* 'A' */
    $redis->lGet('key1', -1); /* 'C' */
    $redis->lGet('key1', 10); /* `FALSE` */


> lSet

说明：设置名称为key的list中index位置的元素赋值为value   
参数：Key Index Value   
返回值：Bool 成功返回true 失败返回false   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
    $redis->lGet('key1', 0); /* 'A' */
    $redis->lSet('key1', 0, 'X');
    $redis->lGet('key1', 0); /* 'X' */

> lRange, lGetRange

说明：返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）   
参数：Key Start End   
返回值：Array   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C');
    $redis->lRange('key1', 0, -1); /* array('A', 'B', 'C') */


> lTrim, listTrim

说明：截取名称为key的list，保留start至end之间的元素   
参数：Key Start Stop   
返回值：Array   
范例：

    $redis->rPush('key1', 'A');
    $redis->rPush('key1', 'B');
    $redis->rPush('key1', 'C');
    $redis->lRange('key1', 0, -1); /* array('A', 'B', 'C') */
    $redis->lTrim('key1', 0, 1);
    $redis->lRange('key1', 0, -1); /* array('A', 'B') */


> lRem, lRemove

说明：从列表中从头部开始移除count个匹配的值。如果count为零，所有匹配的元素都被删除。如果count是负数，内容从尾部开始删除。   
参数：key value count   
返回值：LONG or bool   
范例：

    $redis->lPush('key1', 'A');
    $redis->lPush('key1', 'B');
    $redis->lPush('key1', 'C');
    $redis->lPush('key1', 'A');
    $redis->lPush('key1', 'A');
    
    $redis->lRange('key1', 0, -1); /* array('A', 'A', 'C', 'B', 'A') */
    $redis->lRem('key1', 'A', 2); /* 2 */
    $redis->lRange('key1', 0, -1); /* array('C', 'B', 'A') */


> lInsert

说明：在名称为key的list中，找到值为pivot 的value，并根据参数Redis::BEFORE | Redis::AFTER，来确定，newvalue 是放在 pivot 的前面，或者后面。如果key不存在，不会插入，如果 pivot不存在，return -1   
参数：key position Redis::BEFORE | Redis::AFTER pivot value   
返回值：返回这个list的长度,如果pivot 不存在 返回-1   
范例：

    $redis->delete('key1');
    $redis->lInsert('key1', Redis::AFTER, 'A', 'X'); /* 0 */
    
    $redis->lPush('key1', 'A');
    $redis->lPush('key1', 'B');
    $redis->lPush('key1', 'C');
    
    $redis->lInsert('key1', Redis::BEFORE, 'C', 'X'); /* 4 */
    $redis->lRange('key1', 0, -1); /* array('A', 'B', 'X', 'C') */
    
    $redis->lInsert('key1', Redis::AFTER, 'C', 'Y'); /* 5 */
    $redis->lRange('key1', 0, -1); /* array('A', 'B', 'X', 'C', 'Y') */
    
    $redis->lInsert('key1', Redis::AFTER, 'W', 'value'); /* -1 */


> sAdd   
> 说明：向名称为key的set中添加元素value,如果value存在，不写入，return false   
> 参数：key value   
> 返回值：Bool 成功返回true 失败或已存在value值则返回false   
> 范例：

    $redis->sAdd('key1' , 'set1'); /* TRUE, 'key1' => {'set1'} */
    $redis->sAdd('key1' , 'set2'); /* TRUE, 'key1' => {'set1', 'set2'}*/
    $redis->sAdd('key1' , 'set2'); /* FALSE, 'key1' => {'set1', 'set2'}*/


> sRem, sRemove

说明：删除名称为key的set中的元素value   
参数：key member   
返回值：Bool   
范例：

    $redis->sAdd('key1' , 'set1');
    $redis->sAdd('key1' , 'set2');
    $redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/
    $redis->sRem('key1', 'set2'); /* 'key1' => {'set1', 'set3'} */

> sMove

说明：将value元素从名称为srckey的集合移到名称为dstkey的集合   
参数：srcKey dstKey member   
返回值：Bool 成功返回true 失败返回false   
范例：

    $redis->sAdd('key1' , 'set11');
    $redis->sAdd('key1' , 'set12');
    $redis->sAdd('key1' , 'set13'); /* 'key1' => {'set11', 'set12', 'set13'}*/
    $redis->sAdd('key2' , 'set21');
    $redis->sAdd('key2' , 'set22'); /* 'key2' => {'set21', 'set22'}*/
    $redis->sMove('key1', 'key2', 'set13'); /* 'key1' =>  {'set11', 'set12'} */
    /* 'key2' =>  {'set21', 'set22', 'set13'} */

> sIsMember, sContains

说明：名称为key的集合中查找是否有value元素   
参数：key value   
返回值：Bool 存在返回true 不存在返回false   
范例：

    $redis->sAdd('key1' , 'set1');
    $redis->sAdd('key1' , 'set2');
    $redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/
    
    $redis->sIsMember('key1', 'set1'); /* TRUE */
    $redis->sIsMember('key1', 'setX'); /* FALSE */


> sCard, sSize

说明：返回名称为key的set的元素个数   
参数：Key   
返回值：Long 元素个数，不存在则返回0   
范例：

    $redis->sAdd('key1' , 'set1');
    $redis->sAdd('key1' , 'set2');
    $redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/
    $redis->sCard('key1'); /* 3 */
    $redis->sCard('keyX'); /* 0 */


> sPop

说明：随机返回并删除名称为key的set中一个元素   
参数：key   
返回值：返回被随机取得的值，如果失败返回false   
范例：

    $redis->sAdd('key1' , 'set1');
    $redis->sAdd('key1' , 'set2');
    $redis->sAdd('key1' , 'set3'); /* 'key1' => {'set3', 'set1', 'set2'}*/
    $redis->sPop('key1'); /* 'set1', 'key1' => {'set3', 'set2'} */
    $redis->sPop('key1'); /* 'set3', 'key1' => {'set2'} */


> sRandMember   
> 说明：随机返回名称为key的set中一个元素   
> 参数：Key   
> 返回值：返回的value的值，失败返回false   
> 范例：

    $redis->sAdd('key1' , 'set1');
    $redis->sAdd('key1' , 'set2');
    $redis->sAdd('key1' , 'set3'); /* 'key1' => {'set3', 'set1', 'set2'}*/
    $redis->sRandMember('key1'); /* 'set1', 'key1' => {'set3', 'set1', 'set2'} */
    $redis->sRandMember('key1'); /* 'set3', 'key1' => {'set3', 'set1', 'set2'} */


> sInter

说明：求交集   
参数：Key1，key2….keyN   
返回值：Array 返回交集的数组，如果交集为空，则返回一个空数组   
范例：

    $redis->sAdd('key1', 'val1');
    $redis->sAdd('key1', 'val2');
    $redis->sAdd('key1', 'val3');
    $redis->sAdd('key1', 'val4');
    
    $redis->sAdd('key2', 'val3');
    $redis->sAdd('key2', 'val4');
    
    $redis->sAdd('key3', 'val3');
    $redis->sAdd('key3', 'val4');
    
    var_dump($redis->sInter('key1', 'key2', 'key3'));
    Output:
    array(2) {
      [0]=>
      string(4) "val4"
      [1]=>
      string(4) "val3"
    }


> sInterStore

> 说明：执行sInter命令并把结果储存到新建的变量中。   
> 参数：Key Key2:key1,key2…keyN   
> 返回值：   
> 范例：

    $redis->sAdd('key1', 'val1');
    $redis->sAdd('key1', 'val2');
    $redis->sAdd('key1', 'val3');
    $redis->sAdd('key1', 'val4');
    
    $redis->sAdd('key2', 'val3');
    $redis->sAdd('key2', 'val4');
    
    $redis->sAdd('key3', 'val3');
    $redis->sAdd('key3', 'val4');
    
    var_dump($redis->sInterStore('output', 'key1', 'key2', 'key3'));
    var_dump($redis->sMembers('output'));
    Output:
    int(2)
    
    array(2) {
      [0]=>
      string(4) "val4"
      [1]=>
      string(4) "val3"
    }


> sUnion

说明：合并多个key值   
参数：Keys：key1，key2…..keyN   
返回值：这些key生成的合集   
范例：

    $redis->delete('s0', 's1', 's2');
    
    $redis->sAdd('s0', '1');
    $redis->sAdd('s0', '2');
    $redis->sAdd('s1', '3');
    $redis->sAdd('s1', '1');
    $redis->sAdd('s2', '3');
    $redis->sAdd('s2', '4');
    
    var_dump($redis->sUnion('s0', 's1', 's2'));
    Return value: all elements that are either in s0 or in s1 or in s2.
    array(4) {
      [0]=>
      string(1) "3"
      [1]=>
      string(1) "4"
      [2]=>
      string(1) "1"
      [3]=>
      string(1) "2"
    }


> sUnionStore

说明：执行sUnion命令并把结果储存到新建的变量中。   
参数：Key: Keys:   
返回值：   
范例：

    $redis->delete('s0', 's1', 's2');
    
    $redis->sAdd('s0', '1');
    $redis->sAdd('s0', '2');
    $redis->sAdd('s1', '3');
    $redis->sAdd('s1', '1');
    $redis->sAdd('s2', '3');
    $redis->sAdd('s2', '4');
    
    var_dump($redis->sUnionStore('dst', 's0', 's1', 's2'));
    var_dump($redis->sMembers('dst'));
    Return value: the number of elements that are either in s0 or in s1 or in s2.
    int(4)
    array(4) {
      [0]=>
      string(1) "3"
      [1]=>
      string(1) "4"
      [2]=>
      string(1) "1"
      [3]=>
      string(1) "2"
    }


> sDiff

说明：求差集   
参数：Keys   
返回值：Array   
范例：

    $redis->delete('s0', 's1', 's2');
    
    $redis->sAdd('s0', '1');
    $redis->sAdd('s0', '2');
    $redis->sAdd('s0', '3');
    $redis->sAdd('s0', '4');
    
    $redis->sAdd('s1', '1');
    $redis->sAdd('s2', '3');
    
    var_dump($redis->sDiff('s0', 's1', 's2'));
    Return value: all elements of s0 that are neither in s1 nor in s2.
    array(2) {
      [0]=>
      string(1) "4"
      [1]=>
      string(1) "2"
    }

> sDiffStore

说明：求差集并把结果储存到新建的变量中。   
参数：Key Keys   
返回值：   
范例：

    $redis->delete('s0', 's1', 's2');
    
    $redis->sAdd('s0', '1');
    $redis->sAdd('s0', '2');
    $redis->sAdd('s0', '3');
    $redis->sAdd('s0', '4');
    
    $redis->sAdd('s1', '1');
    $redis->sAdd('s2', '3');
    
    var_dump($redis->sDiffStore('dst', 's0', 's1', 's2'));
    var_dump($redis->sMembers('dst'));
    Return value: the number of elements of s0 that are neither in s1 nor in s2.
    int(2)
    array(2) {
      [0]=>
      string(1) "4"
      [1]=>
      string(1) "2"
    }

> sMembers, sGetMembers

说明：返回名称为key的set的所有元素   
参数：Key   
返回值：array   
范例：

    $redis->delete('s');
    $redis->sAdd('s', 'a');
    $redis->sAdd('s', 'b');
    $redis->sAdd('s', 'a');
    $redis->sAdd('s', 'c');
    var_dump($redis->sMembers('s'));
    Output:
    array(3) {
      [0]=>
      string(1) "c"
      [1]=>
      string(1) "a"
      [2]=>
      string(1) "b"
    }


> getSet

说明：返回原来key中的值，并将value写入key   
参数：Key Value   
返回值：这个key的前一个值   
范例：

    $redis->set('x', '42');
    $exValue = $redis->getSet('x', 'lol');  // return '42', replaces x by 'lol'
    $newValue = $redis->get('x')'       // return 'lol'


> randomKey

说明：随机返回key空间的一个key   
参数：无   
返回值：在redis中随机存在的一个key   
范例：

    $key = $redis->randomKey();
    $surprise = $redis->get($key);  // who knows what's in there.


> select   
> 说明：选择一个[> 数据库][9]  
> 参数：Dbindex   
> 返回值：Bool   
> 范例：

    $redis->select(0);  // switch to DB 0
    $redis->set('x', '42'); // write 42 to x
    $redis->move('x', 1);   // move to DB 1
    $redis->select(1);  // switch to DB 1
    $redis->get('x');   // will return 42


> move   
> 说明：转移一个key到另外一个数据库   
> 参数：Key   
> 返回值：Bool   
> 范例：

    $redis->select(0);  // switch to DB 0
    $redis->set('x', '42'); // write 42 to x
    $redis->move('x', 1);   // move to DB 1
    $redis->select(1);  // switch to DB 1
    $redis->get('x');   // will return 42

> rename, renameKey

说明：重命名key   
参数：Srckey dstkey   
返回值：Bool   
范例：

    $redis->set('x', '42');
    $redis->rename('x', 'y');
    $redis->get('y');   // → 42
    $redis->get('x');   // → `FALSE`

> renameNx

说明：与remane类似，但是，如果重新命名的名字已经存在，不会替换成功

> setTimeout, expire

说明：设定一个key的活动时间（s）   
参数：Key   
返回值：Bool   
范例：

    $redis->set('x', '42');
    $redis->setTimeout('x', 3); // x will disappear in 3 seconds.
    sleep(5);               // wait 5 seconds
    $redis->get('x');       // will return `FALSE`, as 'x' has expired.

> expireAt

说明：   
key存活到一个unix时间戳时间   
参数：Key Unix timestamp   
返回值：Bool   
范例：

    $redis->set('x', '42');
    $now = time(NULL); // current timestamp
    $redis->expireAt('x', $now + 3);    // x will disappear in 3 seconds.
    sleep(5);               // wait 5 seconds
    $redis->get('x');       // will return `FALSE`, as 'x' has expired.

> keys, getKeys

说明：返回满足给定pattern的所有key   
参数：Pattern (可带*)   
返回值：Array   
范例：

    $allKeys = $redis->keys('*');   // all keys will match this.
    $keyWithUserPrefix = $redis->keys('user*');


> dbSize

说明：查看现在数据库有多少key   
参数：无   
返回值：DB size,   
范例：

    $count = $redis->dbSize();
    echo "Redis has $count keys\n";

> auth

说明：密码验证   
参数：password   
返回值：BOOL   
范例：

    $redis->auth('foobared');

> bgrewriteaof

说明：使用aof来进行数据库持久化   
参数：无   
返回值：Bool   
范例：

    $redis->bgrewriteaof();

> slaveof

说明：选择从服务器   
参数：host (string) and port   
返回值：BOOL   
范例：

    $redis->slaveof('10.0.1.7', 6379);
    /* ... */
    $redis->slaveof();

> object   
> 说明：获得key对象的详细内容   
> 参数：   
> • “encoding”   
> • “refcount”   
> • “idletime”   
> 返回值：   
> STRING for “encoding”,   
> LONG for “refcount” and “idletime”,   
> FALSE if the key doesn’t exist.   
> 范例：

    $redis->object("encoding", "l"); // → ziplist
    $redis->object("refcount", "l"); // → 1
    $redis->object("idletime", "l"); // → 400 (in seconds, with a precision of 10 seconds).

> save   
> 说明：将数据同步保存到磁盘   
> 参数：无   
> 返回值：Bool   
> 范例：

    $redis->save();

> bgsave

说明：将数据异步保存到磁盘   
参数：无   
返回值：Bool   
范例：

    $redis->bgSave();

> lastSave   
> 说明：返回上次成功将数据保存到磁盘的Unix时间戳   
> 参数：无   
> 返回值：timestamp   
> 范例：

    $redis->lastSave();

> type

说明：返回key的类型值   
参数：Key   
返回值：   
根据指定的类型返回   
string: Redis::REDIS_STRING   
set: Redis::REDIS_SET   
list: Redis::REDIS_LIST   
zset: Redis::REDIS_ZSET   
hash: Redis::REDIS_HASH   
other: Redis::REDIS_NOT_FOUND   
范例：

    $redis->type('key');

> append

说明：在指定的一个key值后面追加一个值   
参数：Key Value   
返回值：追加完之后这个key值得长度。   
范例：

    $redis->set('key', 'value1');
    $redis->append('key', 'value2'); /* 12 */
    $redis->get('key'); /* 'value1value2' */

> getRange (方法不存在)   
> 说明：返回名称为key的string中start至end之间的字符   
> 参数：key start end   
> 返回值：截取之后的值   
> 范例：

    $redis->set('key', 'string value');
    $redis->getRange('key', 0, 5); /* 'string' */
    $redis->getRange('key', -5, -1); /* 'value' */

> setRange

说明：改变key的string中start至end之间的字符为value   
参数：key offset value   
返回值：修改后字符的长度   
范例：

    $redis->set('key', 'Hello world');
    $redis->setRange('key', 6, "redis"); /* returns 11 */
    $redis->get('key'); /* "Hello redis" */

> strlen

说明：获得一个指定key的长度   
参数：key   
返回值：长度   
范例：

    $redis->set('key', 'value');
    $redis->strlen('key'); /* 5 */

> getBit   
> 说明：返回一个指定key的二进制信息   
> 参数：key offset   
> 返回值：LONG   
> 范例：

    $redis->set('key', "\x7f"); // this is 0111 1111
    $redis->getBit('key', 0); /* 0 */
    $redis->getBit('key', 1); /* 1 */

> setBit

说明：给一个指定key的值得第offset位 赋值为value。   
参数：key offset value: bool or int (1 or 0)   
返回值：LONG: 0 or 1   
范例：

    $redis->set('key', "*");    // ord("*") = 42 = 0x2f = "0010 1010"
    $redis->setBit('key', 5, 1); /* returns 0 */
    $redis->setBit('key', 7, 1); /* returns 0 */
    $redis->get('key'); /* chr(0x2f) = "/" = b("0010 1111") */

> flushDB

说明：清空当前数据库   
参数：无   
返回值：Bool:永远都返回true   
范例：

    $redis->flushDB();

> flushAll

说明：清空所有数据库   
参数：无   
返回值：Bool:永远都返回true   
范例：

    $redis->flushAll();

> sort

说明：排序，分页等   
参数：   
‘by’ => ‘some_pattern_*’,   
‘limit’ => array(0, 1),   
‘get’ => ‘some_other_pattern_*’ or an array of patterns,   
‘sort’ => ‘asc’ or ‘desc’,   
‘alpha’ => TRUE,   
‘store’ => ‘external-key’   
返回值：Array   
范例：

    $redis->delete('s');
    $redis->sadd('s', 5);
    $redis->sadd('s', 4);
    $redis->sadd('s', 2);
    $redis->sadd('s', 1);
    $redis->sadd('s', 3);
    
    var_dump($redis->sort('s')); // 1,2,3,4,5
    var_dump($redis->sort('s', array('sort' => 'desc'))); // 5,4,3,2,1
    var_dump($redis->sort('s', array('sort' => 'desc', 'store' => 'out'))); // (int)5

> info

说明：返回redis的版本信息等详情   
参数：无   
返回值：   
范例：

    $redis->info();

> resetStat

说明：重新统计输出INFO 命令的结果   
参数：无   
返回值：BOOL   
范例：

    $redis->resetStat();

> ttl

说明：得到一个key的生存时间，如果这个key值不存在则返回false   
参数：Key   
返回值：Long or bool   
范例：

    $redis->ttl('key');

> persist

说明：移除生存时间到期的key   
参数：Key   
返回值：   
Bool 如果移除成功了返回true ，如果值不存在或是还在生存时间内则返回false   
范例：

    $redis->persist('key');

> mset, msetnx

说明：同时给多个key赋值 ，MSETNX 只有当给所有的值都创建成功的时候才会返回true   
参数：array(key => value, …)   
返回值：Bool   
范例：

    $redis->mset(array('key0' => 'value0', 'key1' => 'value1'));
    var_dump($redis->get('key0'));
    var_dump($redis->get('key1'));
    
    Output:
    string(6) "value0"
    string(6) "value1"

> rpoplpush （redis版本1.1以上才可以）

说明：返回并删除名称为srckey的list的尾元素，并将该元素添加到名称为dstkey的list的头部   
参数：Key: srckey Key: dstkey   
返回值：Bool   
范例：

    $redis->delete('x', 'y');
    
    $redis->lPush('x', 'abc');
    $redis->lPush('x', 'def');
    $redis->lPush('y', '123');
    $redis->lPush('y', '456');
    
    // move the last of x to the front of y.
    var_dump($redis->rpoplpush('x', 'y'));
    var_dump($redis->lRange('x', 0, -1));
    var_dump($redis->lRange('y', 0, -1));
    Output:
    string(3) "abc"
    array(1) {
      [0]=>
      string(3) "def"
    }
    array(3) {
      [0]=>
      string(3) "abc"
      [1]=>
      string(3) "456"
      [2]=>
      string(3) "123"
    }

> brpoplpush

说明：Rpoplpush命令的block版本。   
参数：   
Key: srckey   
Key: dstkey   
Long: timeout   
返回值：Bool   
范例：

> zAdd

说明：向名称为key的zset中添加元素member，score用于排序。如果该元素已经存在，则根据score更新该元素的顺序。   
参数：key score : double value: string   
返回值：Long 元素被成功添加了返回1 否则返回0   
范例：

    $redis->zAdd('key', 1, 'val1');
    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 5, 'val5');
    $redis->zRange('key', 0, -1); // array(val0, val1, val5)

> zRange

说明：返回名称为key的zset（元素已按score从小到大排序）中的index从start到end的所有元素   
参数：   
key   
start: long   
end: long   
withscores: bool = false   
返回值：Array   
范例：

    $redis->zAdd('key1', 0, 'val0');
    $redis->zAdd('key1', 2, 'val2');
    $redis->zAdd('key1', 10, 'val10');
    $redis->zRange('key1', 0, -1); /* array('val0', 'val2', 'val10') */
    
    // with scores
    $redis->zRange('key1', 0, -1, true); /* array('val0' => 0, 'val2' => 2, 'val10' => 10) */

> zDelete, zRem

说明：删除名称为key的zset中的元素member   
参数：key member   
返回值：LONG 成功返回1 失败返回0   
范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zDelete('key', 'val2');
    $redis->zRange('key', 0, -1); /* array('val0', 'val10') */

> zRevRange

说明：返回名称为key的zset（元素已按score从大到小排序）中的index从start到end的所有元素.withscores: 是否输出socre的值，默认false，不输出   
参数：   
key   
start: long   
end: long   
withscores: bool = false   
返回值：Array   
范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zRevRange('key', 0, -1); /* array('val10', 'val2', 'val0') */
    
    // with scores
    $redis->zRevRange('key', 0, -1, true); /* array('val10' => 10, 'val2' => 2, 'val0' => 0) */

> zRangeByScore, zRevRangeByScore

说明：返回名称为key值中score >= star且score <= end的所有元素   
参数：   
key   
start: string   
end: string   
options: array   
返回值：Array   
范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zRangeByScore('key', 0, 3); /* array('val0', 'val2') */
    $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE); /* array('val0' => 0, 'val2' => 2) */
    $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1)); /* array('val2' => 2) */
    $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1)); /* array('val2') */
    $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1)); /* array('val2' => 2) */

> zCount   
> 说明：返回名称为key值中score >= star且score <= end的所有元素的个数   
> 参数：key start: string end: string   
> 返回值：LONG 返回相应结果的长度   
> 范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zCount('key', 0, 3); /* 2, corresponding to array('val0', 'val2') */

> zRemRangeByScore, zDeleteRangeByScore

说明：删除名称为key的值中score >= star且score <= end的所有元素，返回删除个数   
参数：   
key   
start: double or “+inf” or “-inf” string   
end: double or “+inf” or “-inf” string   
返回值：LONG 删除的个数   
范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zRemRangeByScore('key', 0, 3); /* 2 */

> zRemRangeByRank, zDeleteRangeByRank

说明：移除有序集key中，指定排名(rank)区间内的所有成员。   
区间分别以下标参数start和stop指出，包含start和stop在内。   
参数：key start: LONG end: LONG   
返回值：LONG   
范例：

    $redis->zAdd('key', 1, 'one');
    $redis->zAdd('key', 2, 'two');
    $redis->zAdd('key', 3, 'three');
    $redis->zRemRangeByRank('key', 0, 1); /* 2 */
    $redis->zRange('key', 0, -1, array('withscores' => TRUE)); /* array('three' => 3) */

> zSize, zCard   
> 说明：返回名称为key的值的所有元素的个数   
> 参数：key   
> 返回值：Long   
> 范例：

    $redis->zAdd('key', 0, 'val0');
    $redis->zAdd('key', 2, 'val2');
    $redis->zAdd('key', 10, 'val10');
    $redis->zSize('key'); /* 3 */

> zScore   
> 说明：返回名称为key的值中元素member的score   
> 参数：key member   
> 返回值：Double   
> 范例：

    $redis->zAdd('key', 2.5, 'val2');
    $redis->zScore('key', 'val2'); /* 2.5 */

> zRank, zRevRank

说明：返回名称为key的值（元素已按score从小到大排序）中member 元素的rank（即index，从0开始），若没有member 元素，返回“null”。zRevRank 是从大到小排序   
参数：key member   
返回值：   
范例：

    $redis->delete('z');
    $redis->zAdd('key', 1, 'one');
    $redis->zAdd('key', 2, 'two');
    $redis->zRank('key', 'one'); /* 0 */
    $redis->zRank('key', 'two'); /* 1 */
    $redis->zRevRank('key', 'one'); /* 1 */
    $redis->zRevRank('key', 'two'); /* 0 */

> zIncrBy

说明：如果在名称为key的值中已经存在元素member，则该元素的score增加increment；否则向集合中添加该元素，其score的值为increment   
参数：   
key   
value: (double) value that will be added to the member’s score   
member   
返回值：DOUBLE   
范例：

    $redis->delete('key');
    $redis->zIncrBy('key', 2.5, 'member1'); /* key or member1 didn't exist, so member1's score is to 0 before the increment */
                          /* and now has the value 2.5  */
    $redis->zIncrBy('key', 1, 'member1'); /* 3.5 */

> zUnion

说明：   
对N个ZSetKeys求并集，并将最后的集合保存在dstkeyN中。对于集合中每一个元素的score，在进行AGGREGATE运算前，都 要乘以对于的WEIGHT参数。如果没有提供WEIGHT，默认为1。默认的AGGREGATE是SUM，即结果集合中元素的score是所有集合对应元 素进行SUM运算的值，而MIN和MAX是指，结果集合中元素的score是所有集合对应元素中最小值和最大值。   
参数：keyOutput arrayZSetKeys arrayWeights   
aggregateFunction Either “SUM”, “MIN”, or “MAX”: defines the behaviour to use on duplicate entries during the zUnion.   
返回值：LONG   
范例：

    $redis->delete('k1');
    $redis->delete('k2');
    $redis->delete('k3');
    $redis->delete('ko1');
    $redis->delete('ko2');
    $redis->delete('ko3');
    
    $redis->zAdd('k1', 0, 'val0');
    $redis->zAdd('k1', 1, 'val1');
    
    $redis->zAdd('k2', 2, 'val2');
    $redis->zAdd('k2', 3, 'val3');
    
    $redis->zUnion('ko1', array('k1', 'k2')); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */
    
    /* Weighted zUnion */
    $redis->zUnion('ko2', array('k1', 'k2'), array(1, 1)); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */
    $redis->zUnion('ko3', array('k1', 'k2'), array(5, 1)); /* 4, 'ko1' => array('val0', 'val2', 'val3', 'val1') */

> zInter

说明：对N个ZSetKeys求交集，并将最后的集合保存在dstkeyN中。对于集合中每一个元素的score，在进行AGGREGATE运算前，都 要乘以对于的WEIGHT参数。如果没有提供WEIGHT，默认为1。默认的AGGREGATE是SUM，即结果集合中元素的score是所有集合对应元 素进行SUM运算的值，而MIN和MAX是指，结果集合中元素的score是所有集合对应元素中最小值和最大值。   
参数：keyOutput arrayZSetKeys arrayWeights   
aggregateFunction Either “SUM”, “MIN”, or “MAX”: defines the behaviour to use on duplicate entries during the zUnion.   
返回值：LONG   
范例：

    $redis->delete('k1');
    $redis->delete('k2');
    $redis->delete('k3');
    $redis->delete('ko1');
    $redis->delete('ko2');
    $redis->delete('ko3');
    
    $redis->zAdd('k1', 0, 'val0');
    $redis->zAdd('k1', 1, 'val1');
    
    $redis->zAdd('k2', 2, 'val2');
    $redis->zAdd('k2', 3, 'val3');
    
    $redis->zUnion('ko1', array('k1', 'k2')); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */
    
    /* Weighted zUnion */
    $redis->zUnion('ko2', array('k1', 'k2'), array(1, 1)); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */
    $redis->zUnion('ko3', array('k1', 'k2'), array(5, 1)); /* 4, 'ko1' => array('val0', 'val2', 'val3', 'val1') */

> hSet

说明：向名称为key的hash中添加元素hashKey—> value   
参数：key hashKey value   
返回值：   
Long:如果这个值不存在并且被添加成功返回1，如果这个值存在并且被替代返回0，错误返回false   
范例：

    $redis->delete('h')
    $redis->hSet('h', 'key1', 'hello'); /* 1, 'key1' => 'hello' in the hash at "h" */
    $redis->hGet('h', 'key1'); /* returns "hello" */
    
    $redis->hSet('h', 'key1', 'plop'); /* 0, value was replaced. */
    $redis->hGet('h', 'key1'); /* returns "plop" */

> hSetNx

说明：   
向名称为key的hash中添加元素hashKey—> value ，只当这个值不存在的时候生效。   
参数：key hashKey value   
返回值：Bool   
范例：

    $redis->delete('h')
    $redis->hSetNx('h', 'key1', 'hello'); /* TRUE, 'key1' => 'hello' in the hash at "h" */
    $redis->hSetNx('h', 'key1', 'world'); /* FALSE, 'key1' => 'hello' in the hash at "h". No change since the field wasn't replaced.

> hGet

说明：返回指定名称为key 的hash中hashKey对应的值   
参数：key hashKey   
返回值：成功取到这个值则返回这个值，否则返回false   
范例：

> hLen

说明：返回名称为key 的hash中元素个数   
参数：key   
返回值：LONG 成功则返回hase中元素的个数，失败则返回false   
范例：

    $redis->delete('h')
    $redis->hSet('h', 'key1', 'hello');
    $redis->hSet('h', 'key2', 'plop');
    $redis->hLen('h'); /* returns 2 */

> hDel

说明：删除名称为key的hash中键为hashKey的域   
参数：key hashKey   
返回值：bool   
范例：

> hKeys

说明：返回名称为key的hash中所有键值   
参数：key   
返回值：Array 类似于php中的array_keys函数   
范例：

    $redis->delete('h');
    $redis->hSet('h', 'a', 'x');
    $redis->hSet('h', 'b', 'y');
    $redis->hSet('h', 'c', 'z');
    $redis->hSet('h', 'd', 't');
    var_dump($redis->hKeys('h'));
    Output:
    array(4) {
      [0]=>
      string(1) "a"
      [1]=>
      string(1) "b"
      [2]=>
      string(1) "c"
      [3]=>
      string(1) "d"
    }

> hVals

说明：返回名称为key的hash中所有值   
参数：Key   
返回值：Array   
范例：

    $redis->delete('h');
    $redis->hSet('h', 'a', 'x');
    $redis->hSet('h', 'b', 'y');
    $redis->hSet('h', 'c', 'z');
    $redis->hSet('h', 'd', 't');
    var_dump($redis->hVals('h'));
    Output:
    array(4) {
      [0]=>
      string(1) "x"
      [1]=>
      string(1) "y"
      [2]=>
      string(1) "z"
      [3]=>
      string(1) "t"
    }

> hGetAll

说明：返回一个完整的hash   
参数：Key   
返回值：array   
范例：

    $redis->delete('h');
    $redis->hSet('h', 'a', 'x');
    $redis->hSet('h', 'b', 'y');
    $redis->hSet('h', 'c', 'z');
    $redis->hSet('h', 'd', 't');
    var_dump($redis->hGetAll('h'));
    Output:
    array(4) {
      ["a"]=>
      string(1) "x"
      ["b"]=>
      string(1) "y"
      ["c"]=>
      string(1) "z"
      ["d"]=>
      string(1) "t"
    }

> hExists

说明：名称为key的hash中是否存在键名字为memberKey 的域   
参数：key memberKey   
返回值：BOOL   
范例：

    $redis->hSet('h', 'a', 'x');
    $redis->hExists('h', 'a'); /*  TRUE */
    $redis->hExists('h', 'NonExistingKey'); /* FALSE */

> hIncrBy

说明：将名称为key的hash中member的值增加value   
参数：key member   
value: (integer) value that will be added to the member’s value   
返回值：LONG 返回这个新值   
范例：

    $redis->delete('h');
    $redis->hIncrBy('h', 'x', 2); /* returns 2: h[x] = 2 now. */
    $redis->hIncrBy('h', 'x', 1); /* h[x] ← 2 + 1. Returns 3 */


> hMset

说明：   
向名称为key的hash中批量添加元素   
参数：Key members: key → value array   
返回值：BOOL   
范例：

    $redis->delete('user:1');
    $redis->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
    $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.


> hMGet

说明：返回名称为key的hash中member 数组中的值所对应的在hash中的value   
参数：key member:Keys Array   
返回值：array   
范例：

    $redis->delete('h');
    $redis->hSet('h', 'field1', 'value1');
    $redis->hSet('h', 'field2', 'value2');
    $redis->hmGet('h', array('field1', 'field2')); /* returns array('field1' => 'value1', 'field2' => 'value2') */



PHP连接Memcache代码

```
<?php
$mem = new Memcache;
$mem->connect('127.0.0.1', 11211) or die ("Could not connect");
$mem->set('key', 'This is a test!', 0, 60);
$val = $mem->get('key');
echo $val;
?>
```


# [php使用memcached缓存总结](http://www.cnblogs.com/chenqionghe/p/4321849.html)
**1. 查询多行记录,以sql的md5值为key,缓存数组(个人觉得最好用的方法)**


    $mem = new Memcache();
    $mem->connect('127.0.0.1',11211);
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM test WHERE id='$id'";
    $key = md5($sql);
    //数据库查询是否已经缓存到memcahced服务器中
    if(!($datas = $mem->get($key)))
    {
        echo 'mysql<br />';
        //如果在memcached中没获取过数据,连mysql获取
        $conn = mysql_connect('localhost','root','123456');
        mysql_select_db('test');
        $result = mysql_query($sql);
        while($row = mysql_fetch_assoc($result))
        {
            $datas[] = $row;
        }
        //再把mysql获取的数据保存到memcached中,供下次使用
        $mem->add($key,$datas);
    }
    else
    {
        echo 'memcache<br />';
    }
    print_r($datas);


**2.查询单行记录,缓存该行记录,以id值为key(也可用md5后的sql语句为键)**


    $rangeid = rand(600,1276);
    $rangeid = '1237';
    $mem = new Memcache;
    $mem->connect('127.0.0.1',11211);
    if( ($com = $mem->get($rangeid)) === false) 
    {
        echo '来自mysql<br />';
        $conn = mysql_connect('localhost','root','123456');
        $sql = 'use dedecms';
        mysql_query($sql,$conn);
        $sql = 'set names utf8';
        mysql_query($sql,$conn);
        $sql = 'select aid,actors from dede_addonmovie where aid=' . $rangeid;
        $rs = mysql_query($sql,$conn);
        $com = mysql_fetch_assoc($rs);
        $mem->add($rangeid , $com , false, 60);
    }
    else 
    {
        echo '来自memcache<br />';
    }
    header('content-type:text/html;charset=utf8;');
    print_r($com);


也可以用另一种方式连接memcache


    $rangeid = rand(600,1276);
    $mconn = memcache_connect('localhost',11211);
    if( ($com = memcache_get($mconn,$rangeid)) === false) 
    {
        $conn = mysql_connect('localhost','root','123456');
        $sql = 'use dedecms';
        mysql_query($sql,$conn);
        $sql = 'set names utf8';
        mysql_query($sql,$conn);
        $sql = 'select aid,actors from dede_addonmovie where aid=' . $rangeid;
        $rs = mysql_query($sql,$conn);
        $com = mysql_fetch_assoc($rs);
        memcache_add($mconn , $rangeid , $com , false, mt_rand(40,120));
    }
    else
    {
        echo 'from cache';
    }
    print_r($com);

### PHP Memcache类 

    Memcache::add — 增加一个条目到缓存服务器
    Memcache::addServer — 向连接池中添加一个memcache服务器
    Memcache::close — 关闭memcache连接
    Memcache::connect — 打开一个memcached服务端连接
    Memcache::decrement — 减小元素的值
    Memcache::delete — 从服务端删除一个元素
    Memcache::flush — 清洗（删除）已经存储的所有的元素
    Memcache::get — 从服务端检回一个元素
    Memcache::getExtendedStats — 缓存服务器池中所有服务器统计信息
    Memcache::getServerStatus — 用于获取一个服务器的在线/离线状态
    Memcache::getStats — 获取服务器统计信息
    Memcache::getVersion — 返回服务器版本信息
    Memcache::increment — 增加一个元素的值
    Memcache::pconnect — 打开一个到服务器的持久化连接
    Memcache::replace — 替换已经存在的元素的值
    Memcache::set — Store data at the server
    Memcache::setCompressThreshold — 开启大值自动压缩
    Memcache::setServerParams — 运行时修改服务器参数和状态


### PHP Memcached类 


    Memcached::add — 向一个新的key下面增加一个元素
    Memcached::addByKey — 在指定服务器上的一个新的key下增加一个元素
    Memcached::addServer — 向服务器池中增加一个服务器
    Memcached::addServers — 向服务器池中增加多台服务器
    Memcached::append — 向已存在元素后追加数据
    Memcached::appendByKey — 向指定服务器上已存在元素后追加数据
    Memcached::cas — 比较并交换值
    Memcached::casByKey — 在指定服务器上比较并交换值
    Memcached::__construct — 创建一个Memcached实例
    Memcached::decrement — 减小数值元素的值
    Memcached::decrementByKey — Decrement numeric item's value, stored on a specific server
    Memcached::delete — 删除一个元素
    Memcached::deleteByKey — 从指定的服务器删除一个元素
    Memcached::deleteMulti — Delete multiple items
    Memcached::deleteMultiByKey — Delete multiple items from a specific server
    Memcached::fetch — 抓取下一个结果
    Memcached::fetchAll — 抓取所有剩余的结果
    Memcached::flush — 作废缓存中的所有元素
    Memcached::get — 检索一个元素
    Memcached::getAllKeys — Gets the keys stored on all the servers
    Memcached::getByKey — 从特定的服务器检索元素
    Memcached::getDelayed — 请求多个元素
    Memcached::getDelayedByKey — 从指定的服务器上请求多个元素
    Memcached::getMulti — 检索多个元素
    Memcached::getMultiByKey — 从特定服务器检索多个元素
    Memcached::getOption — 获取Memcached的选项值
    Memcached::getResultCode — 返回最后一次操作的结果代码
    Memcached::getResultMessage — 返回最后一次操作的结果描述消息
    Memcached::getServerByKey — 获取一个key所映射的服务器信息
    Memcached::getServerList — 获取服务器池中的服务器列表
    Memcached::getStats — 获取服务器池的统计信息
    Memcached::getVersion — 获取服务器池中所有服务器的版本信息
    Memcached::increment — 增加数值元素的值
    Memcached::incrementByKey — Increment numeric item's value, stored on a specific server
    Memcached::isPersistent — Check if a persitent connection to memcache is being used
    Memcached::isPristine — Check if the instance was recently created
    Memcached::prepend — 向一个已存在的元素前面追加数据
    Memcached::prependByKey — Prepend data to an existing item on a specific server
    Memcached::quit — 关闭所有打开的链接。
    Memcached::replace — 替换已存在key下的元素
    Memcached::replaceByKey — Replace the item under an existing key on a specific server
    Memcached::resetServerList — Clears all servers from the server list
    Memcached::set — 存储一个元素
    Memcached::setByKey — Store an item on a specific server
    Memcached::setMulti — 存储多个元素
    Memcached::setMultiByKey — Store multiple items on a specific server
    Memcached::setOption — 设置一个memcached选项
    Memcached::setOptions — Set Memcached options
    Memcached::setSaslAuthData — Set the credentials to use for authentication
    Memcached::touch — Set a new expiration on an item
    Memcached::touchByKey — Set a new expiration on an item on a specific server

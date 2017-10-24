## Memcache/Memcached的PHP操作手册（纯手稿版）


`Memcache`和`Memcached` 其实是一个东西，只是php中要是用的扩展不一样, 2009年左右有人丰富memcache的用法和性能，编写了一个libmemcached是独立第三方client library，才有了memcached ,用法也有了很大的改进比如添加了 getMulti() 批量获取键值

windows下只能安装`php_memcache.dll` 扩展并不存在 php_memcached.dll, 所以windows 中只能使用$mcd = new Memcache() 不能使用new Memcached()

- - -

## Memcache 类

```php
    <?php
    $memcache = new Memcache;

    $memcache->connect('127.0.0.1', 11211);
    

    $memcache->pconnec('127.0.0.1', 11211); 
    // 打开一个到服务器的持久化连接 , 连接不会在脚本执行结束后或者close()被调用后关闭

    $memcache->addServer('123.57.210.55', 11211,$persistent,$weight); 
    // 向连接池中添加一个memcache服务器 $persistent 是否持久化连接 $weight 
       //控制桶的数量提升被选中的权重 $timeout 表示连续持续时间
    

    $memcache->set('name', 'TK'); 
    // 默认存储不压缩 不过期 , 其中字符串和数值直接存储，其他类型序列化后存储 
       //set其实是add方法和replace方法集合
    

    $memcache->set('email', 'julylovin@163.com',MEMCACHE_COMPRESSED,5);
    // MEMCACHE_COMPRESSED设置存储是否压缩 , 5表示5秒后过期但是最大只能设置2592000秒(30天)
      // 如果设置为0 表示永不过期, 可以设置将来的时间戳
    

    $memcache->set('info',array('age'=>'26','salary'=>'1000'));  
    // 可以直接存储数组,redis中存储需要手动serialize()序列化

    $memcache->add('counter', '10', MEMCACHE_COMPRESSED, 0); 
    //如果键值存在会返回false , 如果不存在, 和set方法一样，生成一个counter的key并赋值10

    $memcache->replace ('counter', '10');
     //如果键值不存在会返回false , 如果存在, 替换counter的值为10

    $memcache->increment('counter', 3); 
    // 首先将元素当前值转换成数值然后减去value 操作counter键值+3 
     //  若键不存在 则返回false 不能用于压缩的键值操作，否则get键会失败
    

    $memcache->decrement('counter', 3); // 操作counter键值-3 , 若键不存在 则返回false

    $memcache->delete('counter', 3); // 操作删除键counter ， 3表示3秒内删除，默认是0即立即删除
    

    $memcache->flush(); //flush()立即使所有已经存在的元素失效
    

    $memcache->getExtendedStats (); 
    // 返回一个二维关联数据的服务器统计信息。数组的key由host:port方式组成
    

    $memcache->getServerStatus ('127.0.0.1'); // 获取返回一个服务器的在线/离线状态  0表示离线 非0在线
    

    $memcache->getStats(); // 获取服务器统计信息
    

    $memcache->getVersion(); // 返回服务器版本信息
    

    $memcache->setCompressThreshold ($threshold, $min_saving); 
    //  开启大值自动压缩   $threshold设置压缩阀值 2000字节 ，即字节数大于2K 就压缩
        $min_saving  0--1之间  0.2表示压缩20%
    

    $memcache->setServerParams('memcache_host', 11211, 1, 15, true, '_callback_memcache_failure'); 
    // $memcache->addServer('memcache_host', 11211, false, 1, 1, -1, false);
     //  已经通过addServer 配置过服务器 使用setServerParams 重新设置配置信息
    
```
- - -

## Memcached 类

**此次练习我是在linux上安装了phpstorm，linux平台开发配置看我的其他文章**

```php 
    <?php
    $memcached = new Memcached(); //   必须安装memcached扩展 不会安装看我的相关文章

    #server_key名词解释#
    #当台服务IP和端口完全连通好之后，构成一个hash环，$server_key才会生效 
    #$server_key 只是为了存储的key打个标记 比如有三台 memcache服务器 (A B C ) , 指定 server_key 
    'master-a' 'master-b' 'master-c' 分别存储 会员 订单 日志 等等
    

    $memcached->addServer('192.168.206.128',11211); // 连接服务器

    $memcached->setOption(Memcached::OPT_COMPRESSION, false); 
    //配置存储不压缩，压缩value不利于递增递减
    

    $memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
    //

    $memcached->addServers(array( //添加多台服务器分布式
        array('192.168.206.128', 11311, 20),
        array('192.168.206.128', 11411, 30)
    ));
    

    $memcached->flush(1); //1秒内清除所有元素
    

    $memcached->set('name','TK');
    
    $memcached->setByKey('server_master_db','mage','28');
    # 指定 server_key server_master_db 存储键mage
    
    
    $memcached->setMulti(array('salary'=>'3000','email'=>'julylovin@163.com'));
    // 存储多个元素
    
    $memcached->setMultiByKey('server_master_db',
                        array('salary'=>'3000','email'=>'julylovin@163.com')); 
    //  'server_master_db'服务器 存储多个元素

    $memcached->add('name','TK'); // 键name不存在添加value 否则添加失败
    
    $memcached->addByKey('server_master_db','mname','MTK');
    

    $memcached->append('key','-816'); // 键key的value后追加字符串 -816
    
    $memcached->appendByKey('server_master_db','mname','-923');
    

    $memcached->prepend('name','pre-') ; #向一个已存在的元素前面追加数据
    
    $memcached->prependByKey('server_master_db','name','pre-') ; 
    # 使用server_key自由的将key映射到指定服务器 向一个已存在的元素前面追加数据

    $memcached->get('name');
    
    $memcached->get('name',null,$cas); 
    # 第2参数指定缓存回掉函数 ，不指定传null 
    # 如果元素被找到，并且返回变量 $cas 内部是通过引用变量回传的
    
    $memcached->getByKey('server_master_db','mname');  # 从特定的服务器检索元素

    $memcached->getAllKeys(); // bug 我一致返回是false

    $memcached->cas($cas, 'name', 'TangKang');
    #要与$memcached->get('name',null,$cas) 方法搭配用 才可以拿到 $cas变量
    #它仅在当前客户端最后一次取值后，该key 对应的值没有被其他客户端修改的情况下， 才能够将值写入
    #这是Memcached扩展比Memcache扩展一个非常重要的优势
     在这样一个系统级（Memcache自身提供）的冲突检测机制（乐观锁）下， 我们才能保证高并发下的数据安全
    
    $memcached->casByKey($cas,'server_master_db', 'name', 'TangKang');
    

    $memcached->increment('age','1'); 
    #增加数值元素的值  如果元素的值不是数值类型，将其作为0处理
    
    $memcached->incrementByKey('server_master_db','age','1'); 
    # 用于识别储存和读取值的服务器

    $memcached->decrement('age','1');
    #减少数值元素的值  如果元素的值不是数值类型，将其作为0处理
    
    $memcached->decrementByKey('server_master_db','age','1');
    # 用于识别储存和读取值的服务器

    $memcached->getDelayed(array('name', 'age'), true, null); 
    # 请求多个元素， 如果with_cas设置为true，会同时请求每个元素的CAS标记 
      指定一个result callback来替代明确的抓取结果
    
    $memcached->getDelayedByKey('server_master_db',array('name', 'age'), true, null);  
    

    $memcached->fetch(); 
    # 搭配 $memcached->getDelayed()使用, 从最后一次请求中抓取下一个结果
    
    $memcached->fetchAll();
    #抓取最后一次请求的结果集中剩余的所有结果

    $memcached->getMulti(array('name', 'age')); #检索多个元素
    
    $memcached->getMultiByKey('server_master_db',array('mname', 'mage')); 
    # 从特定服务器检索多个元素
    # 与 $this->memcached->fetchAll() 搭配使用

    $memcached->getOption(Memcached::OPT_COMPRESSION); 
    # 获取Memcached的选项值

    $memcached->getResultCode() ; 
    # 返回最后一次操作的结果代码   Memcached::RES_NOTSTORED
    
    $memcached->getResultMessage() ; 
    # 返回最后一次操作的结果描述消息

    $memcached->getServerByKey('server_master_db') ; # 获取一个key所映射的服务器信息
    
    $memcached->getServerList() ; #  获取服务器池中的服务器列表
    
    $memcached->getStats() ; #  获取服务器池的统计信息
    
    $memcached->getVersion() ;  #  获取服务器池中所有服务器的版本信息

    $memcached->isPersistent() ; #判断当前连接是否是长连接

    $memcached->replace('name','pre-julylovin') ; 
    #set()类似，但是如果 服务端不存在key， 操作将失败
    
    $memcached->replaceByKey('server_master_db','name','pre-julylovin') ; 
    #setBykey()类似，但是如果 服务端不存在key， 操作将失败

    $memcached->resetServerList() ; //清楚服务器池信息
    

    $memcached->setOption(Memcached::OPT_PREFIX_KEY, "widgets") ; 
    #设置一个memcached选项
    
    $memcached->setOptions(array()) ; 
    #设置一个memcached选项

    $memcached->setSaslAuthData($username , $password ) ; 
    #setSaslAuthData 方法不存在

    $memcached->touch('name', 10) ; 
    #设置键name 10秒后过期(只适用30天之内的秒数) ，30天以后请设置时间戳
    
    $memcached->touchByKey('server_master_db','name',10) ;
    

    $memcached->delete('age',10); 
    #10秒(秒数/时间戳)内删除一个元素  这个键已经存在删除队列 
     该键对应的get、add、replace命令都不可用，直到删除
    
    $memcached->deleteByKey('server_master_db','age');
    
    $memcached->deleteMulti(array('age','name')); #传入array删除多个key 
    
    $memcached->deleteMultiByKey('server_master_db',array('age','name'));
    

    $memcached->quit(); # 关闭所有打开的链接

```
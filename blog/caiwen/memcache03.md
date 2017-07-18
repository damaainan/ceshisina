# [【memcache缓存专题(3)】PHP-memcache扩展的安装以及使用][0]

* [memcached][1]

[**菜问**][2] 2015年12月22日发布 



> 安装PHP-memcache扩展和安装其他PHP扩展的步骤是一样的。

## 安装

**step 1**:搜索下载扩展 [http://pecl.php.net/package/memcache][11]

**step 2**:

    gzip -d memcache-2.2.6.tgz
    tar xvf memcache-2.2.6.tar
    cd memcache-2.2.6
    /usr/local/php/bin/phpize #可以先locate查找一下php所在的路径
    ./configure --with-php-config=/usr/local/php/bin/php-config --enable-memcache
    make && make install

**step end**:

    # 通过step2的安装获得以下扩展路径
    Installing shared extensions:     /usr/lib/php/modules/
    
    # 写进PHP.INI中
    extension_dir = "/usr/lib/php/modules/"
    extension=memcache.so
    #或者直接

## PHP操作

> 该扩展是官方扩展,所以在手册里面有以下方法的,但我们一般都不会直接使用,都要进行封装后使用,方面后续的扩展,比如一些函数回调处理

    <?php 
    #初始化
    $mem = new Memcache();
    $mem -> connect("127.0.0.1",11211);
    
    ##### 添加 #####
    $mem -> add('name','zxg',0,1000);
    /*
    bool Memcache::add ( string $key , mixed $var [, int $flag [, int $expire ]] )
    $flag:为0时不压缩,为MEMCACHE_COMPRESSED标记对数据进行压缩(使用zlib)。 
    */
    
    $mem -> add('int',888,0,1000);
    
    $mem -> add('bool',true,0,1000);
    /*
    如果放入的是布尔值,当为false的时候,get出来是空字符串,当为true的时候get出来为1
    */
    
    $mem -> add('arr',array('zz','xx','gg'),MEMCACHE_COMPRESSED,1000);
    /*
    数组比较大的时候可以考虑一下用压缩
    */
    
    /*
    1.另外,在放入对象的时候,要注意:放入或取出时,这个定义对象的类必须要被加载,才能完全的取出;
    
    2.资源类型放不进去;
    
    3.在实际开发使用中,一般存入的key的名称都是唯一的id号;
    */
    
    ##### 更新 #####
    $mem -> set('arr','这是一个数组',0,600);
    /*
    同add的参数一样,在有这个name时为更新,没有这个name时为增加
    */
    
    $mem -> replace('arr','new arr',0,80);
    /*
    同set的参数一样,不过必须要有name值时才有效
    */
    
    $mem -> increment('int',2); //增加2;没有第二参数的话默认为1;
    
    $mem -> decrement('int'); //减少,同上
    
    ##### 删除 #####
    $mem -> delete('int');
    /*
    bool Memcache::delete ( string $key [, int $timeout = 0 ] )
    如果参数timeout指定，该元素会在timeout秒后失效
    */
    
    $mem -> flush();//清空
    
    
    ##### 读取 #####
    $result = $mem -> get('int');
    /*
    string Memcache::get ( string $key [, int &$flags ] )  获取不到就返回false;
    
    array Memcache::get ( array $keys [, array &$flags ] )
    */
    
    $result = $mem -> get(array('name','int','bool','arr')); //分别取多个的key的值
    
    echo '<pre>';
    print_r($result);
    echo '</pre>'; 
    exit;
    ?>  

[0]: /a/1190000004181726
[1]: /t/memcached/blogs
[2]: /u/nixi8

[11]: http://pecl.php.net/package/memcache
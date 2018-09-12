# [对 PHP SESSION 的深刻认识（四）---- 缓存（memcache和redis）存储session][0]

 2016-12-13 21:14  268人阅读 

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [一使用 memcache][10]
1. [二使用 redis][11]
1. [总结][12]

本篇博客将带着大家实现使用缓存系统来存储 session 数据，其中会介绍两个缓存系统 ：memcache 和 [Redis][13]。

## **一、使用 memcache：**

如果大家有看过我之前的这篇博客 [《memcache 和 memcached 的区别分析》][14]，就会发现，[PHP][15]两个扩展中的 memcached 工作的更好，因此这篇博客在使用 memcache 服务时我选择的是 memcached 扩展。

**1、使用 memcached 提供的 session 支持实现(最简单的方法)**

memcached 提供了一个自定义的 session 处理器可以被用于存储用户session 数据到 memcached 服务端。 一个完全独立的 memcached 实例将会在内部使用，因此如果需要您可以设置一个不同的服务器池。

session 的 key 被存储在前缀 memc.sess.key. 之下，因此, 如果你对session 和通常的缓存使用了同样的服务器池，请注意这一点。 译注：另外一个 session 和通常缓存分离的原因是当通常的缓存占满了memcached 服务端后，可能会导致你的 session 被从缓存中踢除，导致用户莫名的掉线。

上面的话都是引用自 [php][15] 官方手册：[http://php.net/manual/zh/memcached.sessions.php][16]

下面我们通过实例来实现将 session 数据存储到 memcache。

第一步，设置session用memcache来存储：

打开配置文件 php.ini ,修改以下两项：

    session.save_handler = memcached
    session.save_path = "127.0.0.1:11211"


重启服务器。

关于修改配置的话，如果你没有修改 php.ini 的权限，或者你只想在当前应用中使用 memcache 来存储 session 的话，可以使用局部的设置：

    #在某个php文件中
    ini_set('session.save_handler','memcached');
    ini_set('session.save_path','127.0.0.1:11211');


配置好之后我们就可以使用了，非常简单：

第二步，在代码中使用会话：
```php
// test1.php 文件

<?php

//如果你没有修改配置文件 php.ini，则用下面两行代码
ini_set('session.save_handler','memcached');
ini_set('session.save_path','127.0.0.1:11211');

//开启会话
session_start();

if(!isset($_SESSION['name'])){
    $_SESSION['name'] = 'default';
}else{
    $_SESSION['name'] = 'lsgogroup';
}
$_SESSION['age'] = 20;

echo session_id();  //获取客户端的sessionId,即 PHPSESSID，后面会用到
```

在浏览器中打开 test1.php，然后我们在 test2.php 中验证是否操作成功。
```php
#test2.php 文件

<?php

//如果你没有修改配置文件 php.ini，则用下面两行代码
ini_set('session.save_handler','memcached');
ini_set('session.save_path','127.0.0.1:11211');

//开启会话
session_start();

var_dump($_SESSION);
```
在浏览器中打开 test2.php ，返回：

    array(2) { ["name"]=> string(7) "default" ["age"]=> int(20) }


我们在 test3.php 中验证 session 是否是存储到了 memcached 中，由于前面说了session 的 key 被存储在前缀 memc.sess.key. 之下，因此当我们要从 memcached 中读取 session数据，我们指定的 key 是 memc.sess.key.sessionId，其中 sessionId 我们在 test1.php 中输出了，直接复制，或者从浏览器中的 cookie 中复制（我这里输出的是 g5ef37mnb7dstkf1kesegbajb7）。
```php
#test3.php 文件
#这里假设大家知道 memcached 的一些操作

<?php
$mem = new memcached();
$mem->addServer("127.0.0.1",11211);
echo $mem->get('memc.sess.key.g5ef37mnb7dstkf1kesegbajb7');
```

返回：

    name|s:7:"default";age|i:20;


如果刷新 test1.php 页面，test2.php 和 test3.php 的输出如下：

    #test2.php
    array(2) { ["name"]=> string(9) "lsgogroup" ["age"]=> int(20) }
    #test3.php
    name|s:9:"lsgogroup";age|i:20;

由上面的 test2.php 和 test3.php 的输出中我们成功的将 session 数据存储到 memcached 中。

memcached 对于 session 的支持还有好几个设置，可能大家都会用到，你如说前面的 key 的前缀设置默认是 memc.sess.key.，我们可以通过修改配置文件 php.ini，将配置项改成

    memcached.sess_prefix = "zhongjin."

或这样：

    ini_set("memcached.sess_prefix","zhongjin.");


那么你在 test3.php 中就可以这样读取 session 数据了：

    echo $mem->get('zhongjin.g5ef37mnb7dstkf1kesegbajb7');


更多有用的配置项大家可以参考官方手册 ：[http://php.net/manual/zh/memcached.configuration.php][17]

第三步，必要了解过期 session 数据的清理

首先，使用 memcached，将 session 数据都存储在内存中，一旦宕机，数据都会丢失，但对于 session 数据来说这其实并不是什么严重的问题。

其次，前面官方手册说道， session 和通常缓存分离的原因是当通常的缓存占满了memcached 服务端后，可能会导致你的 session 被从缓存中踢除，导致用户莫名的掉线。怎么理解？我们要从 memcache 淘汰数据的机制说起。

Memcached主要的cache机制是LRU（最近最少用）[算法][18]+超时失效（学过[操作系统][19]的同学应该会记忆幽深）。当您存数据到memcached中，可以指定该数据在缓存中可以呆多久。如果memcached的内存不够用了，过期的slabs会优先被替换，接着就轮到最老的未被使用的slabs。

怎样判断session失效了呢？在php.ini中有个Session.cookie_lifetime的选项，这个代表SessionID在客户端Cookie储存的时间，默认值是“0”，代表浏览器一关闭，SessionID就作废，这样不管保存在Memcached中的Session是否还有效(保存在Memcached中的session会利用Memcached的内部机制进行处理，即使session数据没有失效，而由于客户端的SessionID已经失效，所以这个key基本上不会有机会使用了，利用Memcached的LRU原则，如果Memcached的内存不够用了，新的数据就会取代过期以及最老的未被使用的数据)，因为SessionID已经失效了，所以在客户端会重新生成一个新的SessionID。

我们再来解释官方手册的问题，通常的缓存是指我们在php中使用memcache来存储[数据库][20]查询结果等数据，导致占用大量的分配给 memcache 的内存，这样可能有某个用户的session数据会因此被淘汰掉，而此时用户还在线，就会导致用户意外掉线。所以官方手册上建议将 session 和通常的缓存分离。

**2、通过php提供的接口，自己改写 session 的处理函数**

通过自定义 session 的处理函数，我们能够更加自如的控制 session 的存取。更加多的细节和思想大家参考我的上一篇博客 [《对 PHP SESSION 的深刻认识（三）—- 数据库存储session》][21]，在这里我直接给出实现：

第一步，修改配置，告诉 php 引擎使用我们自己的session处理函数

打开配置文件 php.ini，修改配置项：

    session.save_handler = user


同时注释 session.save_path 项（在前面添加 ;）.

或者:

    #在某个php文件中 
    ini_set('sesion.save_handler','user');


第二步，编写会话函数

新建 session.inc.php（结构跟session存储在数据库中的代码结构一样），代码如下：
```php
#session.inc.php 文件

<?php

/**
 * Created by PhpStorm.
 * User: lsgozj
 * File: session.inc.php
 * Desc: 处理 session 的自定义类
 * Date: 16-12-13
 * Time: 下午2:45
 */
class memcachedSession implements SessionHandlerInterface
{

    private $_mem = null;   //memcached链接句柄
    //这些信息应该放在配置文件中。。。。
    private $_configs = array(
        'host' => '127.0.0.1',     //主机域
        'port' => 11211,           //端口
        'prefix' => '',              //key前缀
        'expire' => 0                //有效时间
    );

    public function __construct()
    {
        //默认获取配置文件中的配置
        $this->_configs['prefix'] = ini_get('memcached.sess_prefix');
        $this->_configs['expire'] = ini_get('session.gc_maxlifetime');
    }

    //自定义session_start()函数
    public static function my_session_start()
    {
        $sess = new self;
        session_set_save_handler($sess);     //注册自定义函数，在php5.4之后，session_set_save_handler()参数直接传SessionHandlerInterface类型的对象即可。
        session_start();
    }

    /**
     * session_start() 开始会话后第一个调用的函数，类似于构造函数的作用
     * @param string $save_path 默认的保存路径
     * @param string $session_name 默认的参数名（PHPSESSID）
     * @return bool
     */
    public function open($save_path, $session_name)
    {
        $mem = new memcached();
        $mem->addServer($this->_configs['host'], $this->_configs['port']);
        $this->_mem = $mem;
        return true;
    }

    /**
     * 类似于析构函数，在write()之后调用或者session_write_close()函数之调用
     * @return bool
     */
    public function close()
    {
        $this->_mem = null;
        return true;
    }

    /**
     * 读取session信息
     * @param string $sessionId 通过该ID（客户端的PHPSESSID）唯一确定对应的session数据
     * @return session信息或者空串（没有存储session信息）
     */
    public function read($sessionId)
    {
        //根据配置文件获取前缀（当然也可以自定义）
        return $this->_mem->get($this->_configs['prefix'] . $sessionId);
    }

    /**
     * 写入或修改session数据
     * @param string $sessionId 要写入数据的session对应的id（PHPSESSID）
     * @param string $sessionData 要写入的是数据，已经序列化过的
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        return $this->_mem->set($this->_configs['prefix'] . $sessionId, $sessionData, $this->_configs['expire']);
    }

    /**
     * 主动销毁session会话
     * @param string $sessionId 要销毁的会话的唯一ID
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->_mem->delete($this->_configs['prefix'] . $sessionId);
    }

    /**
     * 清理会话中的过期数据
     * @param int $maxlifetime 有效期（自动读取配置文件 php.ini 中的 session.gc_maxlifetime 配置项）
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
```

对以上代码的必要解释：

1、存储时使用的 key 前缀和存储的生命周期读取的是配置文件 php.ini 的设置，如果需要自定义，在构造函数中修改或者直接在 _configs 数组中定义，然后删除构造函数。   
2、由于我们在write()函数中设置缓存的时候已经指定生命周期，过期的数据会由 memcache 自动清理，所以后面的 gc() 函数已经没什么意义了，直接 return true 即可。   
3、在使用 memcached 存储session数据的时候，有效时间不能超过30天。

我们在 test.php 文件中[测试][22]一下是否可用：
```php
#test.php 文件

<?php
require_once('./session.inc.php');
memcachedSession::my_session_start();     //开启会话

$_SESSION['name'] = 'LSGOZJ';
$_SESSION['age'] = 22;

var_dump($_SESSION);
echo '<br>'.session_id();   //获取SESSIONID,我们后面测试会用到，我现在测试得到的是 g5ef37mnb7dstkf1kesegbajb7
```

在浏览器中访问 test.php，然后我们在 test1.php 中看看memcached 中是否已经存储了 session 数据：
```php
#test1.php 文件

<?php
$mem = new memcached();
$mem->addServer('127.0.0.1',11211);
$prefix = ini_get("memcached.sess_prefix");
echo $mem->get($prefix."g5ef37mnb7dstkf1kesegbajb7");
```
在浏览器打开 test1.php 返回：

    name|s:6:"LSGOZJ";age|i:22;


这个结果也能证明，我们已经成功的将 session 数据存储到 memcached 中了。

第三步、简单谈谈session的清理

在上面我们为我们的每一条 session 设置了生命周期，如果到达这个时间该条session还没有被访问的话，那么 memcached 视之为垃圾数据，但是，并不是到期了就把该条 session 数据删除，而是在下一次访问该条 session 数据的时候，检测是否到了有效期，过了有效期才把它从内存中删除。（如果用轮询的办法每时每刻的检测是否到期，那得多耗内存。。。。）

## **二、使用 redis：**

由于使用 [redis][13] 和使用 memcache 是差不多的，因此这部分就快速的带过了。

**1、使用 redis 提供的 session 支持实现(最简单的方法)**

修改配置文件 php.ini：
```ini
session.save_handler = redis
session.save_path = 'tcp://127.0.0.1:6379'
```
或是：
```php
#某个php文件
ini_set('session.save_handler','redis');
ini_set('session.save_path','tcp://127.0.0.1:6379');
```

**2、通过php提供的接口，自己改写 session 的处理函数**

关于这一部分，跟上面 memcached 的内容非常像，就只有个别函数比如设置有效时间，redis使用setex()函数，等等跟memcached 不一样，我就不再举例子了。

[0]: http://www.csdn.net/baidu_30000217/article/details/53609790
[9]: #
[10]: #t0
[11]: #t1
[12]: #t2
[13]: http://lib.csdn.net/base/redis
[14]: http://blog.csdn.net/baidu_30000217/article/details/53586536
[15]: http://lib.csdn.net/base/php
[16]: http://php.net/manual/zh/memcached.sessions.php
[17]: http://php.net/manual/zh/memcached.configuration.php
[18]: http://lib.csdn.net/base/datastructure
[19]: http://lib.csdn.net/base/operatingsystem
[20]: http://lib.csdn.net/base/mysql
[21]: http://blog.csdn.net/baidu_30000217/article/details/53572892
[22]: http://lib.csdn.net/base/softwaretest
## [负载均衡中使用Redis实现共享Session](https://segmentfault.com/a/1190000011558000)

> 最近在研究Web架构方面的知识，包括数据库读写分离，Redis缓存和队列，集群，以及负载均衡（LVS），今天就来先学习下我在负载均衡中遇到的问题，那就是session共享的问题。

### 一、负载均衡

**负载均衡**：把众多的访问量分担到其他的服务器上，让每个服务器的压力减少。

通俗的解释就是：把一项任务交由一个开发人员处理总会有上限处理能力，这时可以考虑增加开发人员来共同处理这项任务，多人处理同一项任务时就会涉及到调度问题，即任务分配，这和多线程理念是一致的。nginx在这里的角色相当于任务分配者。

如我们第一次访问 www.baidu.com 这个域名，可能会对应这个IP 111.13.101.208的服务器，然后第二次访问，IP可能会变为111.13.101.209的服务器，这就是百度采用了负载均衡，一个域名对应多个服务器，将访问量分担到其他的服务器，这样很大程度的减轻了每个服务器上访问量。

![][0]

但是，这里有一个问题，如果我们登录了百度的一个账号，如网页的百度网盘，但是每次有可能请求的是不同的服务器，我们知道每个服务器都会有自己的会话session，所以会导致用户每次刷新网页又要重新登录，这是非常糟糕的体验，因此，根据以上问题，希望session可以共享，这样就可以解决负载均衡中同一个域名不同服务器对应不同session的问题。

### 二、Redis介绍

目前多服务器的共享session，用的最多的是redis。

关于Redis的基础知识，可以看我之前的博文[Redis开发学习][1]。

再简单的梳理下：

1. redis是key-value的存储系统，属于非关系型数据库
1. 特点：支持数据持久化，可以让数据在内存中保存到磁盘里（memcached：数据存在内存里，如果服务重启，数据会丢失）
1. 支持5种数据类型：string，hash，list，set，zset
1. 两种文件格式（即数据持久化）  
（1）RDB（全量数据）：多长时间/频率，把内存中的数据刷到磁盘中，便于下次读取文件时进行加载。（2）AOF（增量请求）：类似mysql的二进制日志，不停地把对数据库的更改语句记录到日志中，下次重启服务，会根据二进制日志把数据重写一次，加载到内存里，实现数据持久化
1. 存储  
（1）内存存储 （2）磁盘存储（RDB） （3）log文件（AOF）

### 三、实现的核心思想

首先要明确session和cookie的区别。浏览器端存的是cookie每次浏览器发请求到服务端是http 报文头是会自动加上你的cookie信息的。服务端拿着用户的cookie作为key去存储里找对应的value(session).

同一域名下的网站的cookie都是一样的。所以无论几台服务器,无论请求分配到哪一台服务器上同一用户的cookie是不变的。也就是说cookie对应的session也是唯一的。

所以，这里只要保证多台业务服务器访问同一个redis服务器(或集群)就行了。

### 四、PHP会话session配置改为Redis

我们可以看到PHP默认的的session配置使用文件形式保存在服务器临时目录中，我们需要Redis作为保存session的驱动，所以，这里需要对配置文件进行修改，PHP的自定义会话机制改为Redis。

![][2]

这里有三种修改方式：

#### 1.修改配置文件php.ini

找到配置文件 php.ini，修改为下面内容，保存并重启服务


    session.save_handler = redis
    session.save_path = "tcp://127.0.0.1:6379"

#### 2.代码中动态配置修改

直接在代码中加入以下内容：


    ini_set("session.save_handler", "redis");
    ini_set("session.save_path", "tcp://127.0.0.1:6379");

注：如果配置文件redis.conf里设置了连接密码requirepass，save_path需要这样写tcp://127.0.0.1:6379?auth=authpwd ，否则保存session的时候会报错。

测试：

```php
    <?php
    //ini_set("session.save_handler", "redis");
    //ini_set("session.save_path", "tcp://127.0.0.1:6379");
    
    session_start();
    
    //存入session
    $_SESSION['class'] = array('name' => 'toefl', 'num' => 8);
    
    //连接redis
    $redis = new redis();
    $redis->connect('127.0.0.1', 6379);
    
    //检查session_id
    echo 'session_id:' . session_id() . '<br/>';
    
    //redis存入的session（redis用session_id作为key,以string的形式存储）
    echo 'redis_session:' . $redis->get('PHPREDIS_SESSION:' . session_id()) . '<br/>';
    
    //php获取session值
    echo 'php_session:' . json_encode($_SESSION['class']);
```

#### 3.自定义会话机制

使用 session_set_save_handle 方法自定义会话机制，网上发现了一个封装非常好的类，我们可以直接使用这个类来实现我们的共享session操作。

```php
    <?php
    class redisSession{
        /**
         * 保存session的数据库表的信息
         */
        private $_options = array(
            'handler' => , //数据库连接句柄
            'host' => ,
            'port' => ,
            'lifeTime' => ,
            'prefix'   => 'PHPREDIS_SESSION:'
        );
    
        /**
         * 构造函数
         * @param $options 设置信息数组
         */
        public function __construct($options=array()){
            if(!class_exists("redis", false)){
                die("必须安装redis扩展");
            }
            if(!isset($options['lifeTime']) || $options['lifeTime'] <= 0){
                $options['lifeTime'] = ini_get('session.gc_maxlifetime');
            }
            $this->_options = array_merge($this->_options, $options);
        }
    
        /**
         * 开始使用该驱动的session
         */
        public function begin(){
            if($this->_options['host'] ===  ||
               $this->_options['port'] ===  ||
               $this->_options['lifeTime'] === 
            ){
                return false;
            }
            //设置session处理函数
            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destory'),
                array($this, 'gc')
            );
        }
        /**
         * 自动开始回话或者session_start()开始回话后第一个调用的函数
         * 类似于构造函数的作用
         * @param $savePath 默认的保存路径
         * @param $sessionName 默认的参数名，PHPSESSID
         */
        public function open($savePath, $sessionName){
            if(is_resource($this->_options['handler'])) return true;
            //连接redis
            $redisHandle = new Redis();
            $redisHandle->connect($this->_options['host'], $this->_options['port']);
            if(!$redisHandle){
                return false;
            }
    
            $this->_options['handler'] = $redisHandle;
    //        $this->gc(null);
            return true;
    
        }
    
        /**
         * 类似于析构函数，在write之后调用或者session_write_close()函数之后调用
         */
        public function close(){
            return $this->_options['handler']->close();
        }
    
        /**
         * 读取session信息
         * @param $sessionId 通过该Id唯一确定对应的session数据
         * @return session信息/空串
         */
        public function read($sessionId){
            $sessionId = $this->_options['prefix'].$sessionId; 
            return $this->_options['handler']->get($sessionId);
        }
    
        /**
         * 写入或者修改session数据
         * @param $sessionId 要写入数据的session对应的id
         * @param $sessionData 要写入的数据，已经序列化过了
         */
        public function write($sessionId, $sessionData){
            $sessionId = $this->_options['prefix'].$sessionId; 
            return $this->_options['handler']->setex($sessionId, $this->_options['lifeTime'], $sessionData);
        }
    
        /**
         * 主动销毁session会话
         * @param $sessionId 要销毁的会话的唯一id
         */
        public function destory($sessionId){
            $sessionId = $this->_options['prefix'].$sessionId; 
    //        $array = $this->print_stack_trace();
    //        log::write($array);
            return $this->_options['handler']->delete($sessionId) >= 1 ? true : false;
        }
    
        /**
         * 清理绘画中的过期数据
         * @param 有效期
         */
        public function gc($lifeTime){
            //获取所有sessionid，让过期的释放掉
            //$this->_options['handler']->keys("*");
            return true;
        }
        //打印堆栈信息
        public function print_stack_trace()
        {
            $array = debug_backtrace ();
            //截取用户信息
            $var = $this->read(session_id());
            $s = strpos($var, "index_dk_user|");
            $e = strpos($var, "}authId|");
            $user = substr($var,$s+14,$e-13);
            $user = unserialize($user);
            //print_r($array);//信息很齐全
            unset ( $array [0] );
            if(!empty($user)){
              $traceInfo = $user['id'].'|'.$user['user_name'].'|'.$user['user_phone'].'|'.$user['presona_name'].'++++++++++++++++\n';
            }else{
              $traceInfo = '++++++++++++++++\n';
            }
            $time = date ( "y-m-d H:i:m" );
            foreach ( $array as $t ) {
                $traceInfo .= '[' . $time . '] ' . $t ['file'] . ' (' . $t ['line'] . ') ';
                $traceInfo .= $t ['class'] . $t ['type'] . $t ['function'] . '(';
                $traceInfo .= implode ( ', ', $t ['args'] );
                $traceInfo .= ")\n";
            }
            $traceInfo .= '++++++++++++++++';
            return $traceInfo;
        }
    
    }
```

在你的项目入口处调用上边的类：  
上边的方法等于是重写了session写入文件的方法，将数据写入到了Redis中。

初始化文件 init.php

```php
    <?php
    require_once("redisSession.php");
    $handler = new redisSession(array(
                    'host' => "127.0.0.1",
                    'port' => "6379"
            ));
    $handler->begin();
    
    // 这也是必须的，打开session，必须在session_set_save_handler后面执行
    session_start();
    
```

测试 test.php

```php
    <?php
    // 引入初始化文件
    include("init.php");
    $_SESSION['isex'] = "Hello";  
    $_SESSION['sex']  = "Corwien";
    
    // 打印文件
    print_r($_SESSION);
    // ( [sex] => Corwien [isex] => Hello )
```

在Redis客户端使用命令查看我们的这条数据是否存在：


    27.0.0.1:6379> keys *
     1) "first_key"
     2) "mylist"
     3) "language"
     4) "mytest"
     5) "pragmmer"
     6) "good"
     7) "PHPREDIS_SESSION:29a111bcs120sv48ibmmjqdag4"
     8) "user:1"
     9) "counter:__rand_int__"
    10) "key:__rand_int__"
    11) "tutorial-list"
    12) "id:1"
    13) "name"
    127.0.0.1:6379> get PHPREDIS_SESSION:29a111bcs120sv48ibmmjqdag4
    "sex|s:7:\"Corwien\";isex|s:5:\"Hello\";"
    127.0.0.1:6379>

我们可以看到，我们的数据被保存在了Redis端了，键为：PHPREDIS_SESSION:29a111bcs120sv48ibmmjqdag4.

- - -

相关文章  
[通过redis实现session共享-php][3]  
[Redis 分布式缓存，是如何实现多台服务器SESSION 实时共享的][4]  
[redis实现session共享，哨兵][5]  
[nginx+iis实现负载均衡][6]  
[我所理解的session_set_save_handler的执行顺序机制][7]

[0]: ../img/bVVhGW.png
[1]: https://segmentfault.com/a/1190000005859888
[2]: ../img/bVWEWn.png
[3]: http://www.cnblogs.com/wangxusummer/p/6382151.html
[4]: https://segmentfault.com/q/1010000003988125
[5]: http://www.cnblogs.com/windysai/p/6226995.html
[6]: http://www.cnblogs.com/yanweidie/p/4658136.html
[7]: https://segmentfault.com/a/1190000003032371
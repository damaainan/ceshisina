# [对 PHP SESSION 的深刻认识（三）---- 数据库存储session][0]

 标签： [session][1][php][2]

 2016-12-11 15:54  257人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][8]
1. [第一步建数据库][9]
1. [第二步编写会话函数][10]
1. [第三步谈谈session清理][11]
1. [总结][12]

## **前言：**

本篇博客是继承自我的前面的两篇博客 [《对 PHP SESSION 的深刻认识（一）》][13]、[《对 PHP SESSION 的深刻认识（二）》][14] 而来的，主要是解决前面的问题。

为什么要使用[数据库][15]保存 session 数据？

就 [PHP][16] 来说，语言本身支持的 session 是以文件的形式保存在磁盘文件中，保存在指定的文件夹中，保存的路径可以在配置文件 [php][16].ini 中设置。但是按照默认的存储方法是有很大的弊端：

1. 保存到文件系统中，只要用到 session 就会从好多个文件中查找是定的 sessionId 对应文件，效率很低，而且导致的 I/O 操作很多；
1. 当用到多台服务器做负载均衡的时候，出现 session 丢失问题（其实是保存在了其他服务器上）。

使用数据库来存储 session 数据，我们就能解决上面的问题。

在之前我曾经写过一篇关于这个主题的博客 [《PHP数据库保存session会话》][17]，为了和现在我这个小系列“对 PHP SESSION 的深刻理解”保持统一，我就再写一次，当然，通过最近的学习，懂得东西肯定比以前多了。

## **第一步：建数据库**

**1、创建会话表**

由于 session 数据是保存在服务器上面的，而在客户端中保存的是一个索引（sessionID）,这个索引对应于服务器上的某一条 session 数据。因此该表必须包含的两个字段是 id、data，还有就是会话会有过期时间，所以在这里还有个字段就是 last_accessed，这里我把该表建在test数据库下：

    CREATE TABLE sessions(
        id CHAR(32) NOT NULL,
        data TEXT,
        last_accessed TIMESTAMP NOT NULL,
        PRIMARY KEY(id)
    );


![这里写图片描述][18]

PS：如果程序需要在会话保存大量的数据，则 data 字段可能就需要定义为 MEDIUMTEXT 或 LONGTEXT 类型了。

**2、创建针对session的数据库用户**

    #创建用户
    CREATE USER sess_user IDENTIFIED BY "sess_pwd";
    #授权访问
    GRANT SELECT,UPDATE,INSERT,DELETE ON test.sessions TO sess_user;


现在数据库已经有了，接下来呢就是代码实现 session 数据的存储了。

## **第二步：编写会话函数**

**1、修改配置文件，告诉 php 引擎使用我们自己的session处理函数**

打开 php.ini 配置文件，将 

    session.save_handler = files

改成：

    session.save_handler = user


重启服务器

**2、通过php提供的接口，自己改写session的处理函数**

要想实现自定义地处理session，关键是通过调用函数 session_set_save_handler()来完成的。

php5.4及之后可以直接实现 SessionHandlerInterface 接口，代码会更加简洁。该接口的结构如下：

    SessionHandlerInterface {
        /* 方法 */
        abstract public bool close ( void )
        abstract public bool destroy ( string $session_id )
        abstract public bool gc ( int $maxlifetime )
        abstract public bool open ( string $save_path , string $session_name )
        abstract public string read ( string $session_id )
        abstract public bool write ( string $session_id , string $session_data )
    }


我们新建 session.inc.php，代码如下：

    <?php
    /**
     * Created by PhpStorm.
     * User: lsgozj
     * File: session.inc.php
     * Desc: 处理 session 的自定义类
     * Date: 16-12-10
     * Time: 下午4:39
     */
    
    class mysqlSession implements SessionHandlerInterface
    {
    
        private $_pdo = null;   //数据库链接句柄
        //这些信息应该放在配置文件中。。。。
        private $_configs = array(
            'dbms' => 'mysql',          //数据库类型
            'dbhost' => 'localhost',    //主机
            'dbname' => 'test',         //数据库名
            'dbtable' => 'sessions',    //数据库表
            'dbuser' => 'sess_user',    //用户
            'dbpwd' => 'sess_pwd',      //密码
        );
    
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
            $dsn = $this->_configs['dbms'] . ":host=" . $this->_configs['dbhost'] . ";dbname=" . $this->_configs['dbname'];
            try {
                $this->_pdo = new PDO($dsn, $this->_configs['dbuser'], $this->_configs['dbpwd']);
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    
        /**
         * 类似于析构函数，在write()之后调用或者session_write_close()函数之调用
         * @return bool
         */
        public function close()
        {
            $this->_pdo = null;
            return true;
        }
    
        /**
         * 读取session信息
         * @param string $sessionId 通过该ID（客户端的PHPSESSID）唯一确定对应的session数据
         * @return session信息或者空串（没有存储session信息）
         */
        public function read($sessionId)
        {
            try {
                $sql = 'SELECT * FROM ' . $this->_configs['dbtable'] . ' WHERE id = ? LIMIT 1';
                $res = $this->_pdo->prepare($sql);
                $res->execute(array($sessionId));
    
                if ($ret = $res->fetch(PDO::FETCH_ASSOC)) {
                    return $ret['data'];
                } else {
                    return '';
                }
            } catch (PDOException $e) {
                return '';
            }
        }
    
        /**
         * 写入或修改session数据
         * @param string $sessionId 要写入数据的session对应的id（PHPSESSID）
         * @param string $sessionData 要写入的是数据，已经序列化过的
         * @return bool
         */
        public function write($sessionId, $sessionData)
        {
            try {
                $sql = 'REPLACE INTO ' . $this->_configs['dbtable'] . '(id,data) VALUES(?,?)';
                $res = $this->_pdo->prepare($sql);
                $res->execute(array($sessionId, $sessionData));
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    
        /**
         * 主动销毁session会话
         * @param string $sessionId 要销毁的会话的唯一ID
         * @return bool
         */
        public function destroy($sessionId)
        {
            try {
                $sql = 'DELETE FROM ' . $this->_configs['dbtable'] . ' WHERE id = ?';
                $res = $this->_pdo->prepare($sql);
                $res->execute(array($sessionId));
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    
        /**
         * 清理会话中的过期数据
         * @param int $maxlifetime 有效期（自动读取配置文件 php.ini 中的 session.gc_maxlifetime 配置项）
         * @return bool
         */
        public function gc($maxlifetime)
        {
            try {
                $sql = 'DELETE FROM ' . $this->_configs['dbtable'] . ' WHERE DATE_ADD(last_accessed,INTERVAL ? SECOND) < NOW()';
                $res = $this->_pdo->prepare($sql);
                $res->execute(array($maxlifetime));
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    }


到了这一步我们的任务基本上是完成了，现在我们来[测试][19]一下是否可用：

    # test.php 文件
    
    <?php
    
    require_once('./session.inc.php');
    mysqlSession::my_session_start();     //开启会话
    
    $_SESSION['name'] = 'LSGOZJ';
    $_SESSION['age'] = 22;
    
    var_dump($_SESSION);

在浏览器访问 test.php，然后去数据库里看看，是否已经成功插入数据库：

![这里写图片描述][20]

你可以在另一个 php 文件里面看看是否能够读取：

    # test1.php
    
    <?php
    
    require_once('./session.inc.php');
    mysqlSession::my_session_start();     //开启会话
    
    echo $_SESSION['name'];


如果发现不能读取的话，就得检查上面的步骤了。

大家可能会发现，在整个过程中我都没有对表中的 last_accessed 字段进行操作，因为这个字段是 timestamp 类型的，它会在表更新和插入时默认插入当前时间，因此我们其实不用管该字段。

## **第三步：谈谈session清理**

本人在完成上面的所有步骤之后，一度怀疑过过期的 session 数据系统会帮我清除吗？

我的环境：

    Ubuntu:16.04
    Php:7.0

我在我的第一篇博客 [《对 PHP SESSION 的深刻认识（一）》][13] 中对 session 的清理有过分析，在这里在给大家复习复习：

配置文件 php.ini 中有如下三个配置项：

session.gc_maxlifetime

session.gc_probability 

session.gc_divisor 

这三个配置项的组合构建服务端 session 的垃圾回收机制。

session.gc_probability 和 session.gc_divisor 构成在每个会话初始化时启动 gc（garbage collection 垃圾回收）进程的概率，此概率用 gc_probability/gc_divisor 计算得来。例如 1/100 意味着在每个请求中有 1% 的概率启动 gc 进程。而清理的标准为 session.gc_maxlifetime 定义的时间。

例如：

session.gc_maxlifetime = 1440 表示当 session 数据在 1440s 后还没有被访问的话，则该 session 数据将会被视为“垃圾数据”，并且等待gc（垃圾回收）进程的调用的时候被清理掉。

注意：一般对于一些大型的门户网站，建议将 session.gc_divisor 调大一点，减少开销。

那么我的问题是什么呢？因为在我的环境下，php.ini 中指定的 session.gc_probability = 0，也就是说启动 gc 进程的概率为零。前面我也说了，概率为零是因为系统默认不使用 gc 进程，而是使用 cron 脚本来执行垃圾清理的。

既然系统不使用 gc 进程，那是不是说明上述代码中的 gc 函数就永远得不到执行了？带着这个疑问，我做了个实验：

分别使用上面定义的方法和 php 原来的方法生成一些 session 数据，然后在一段时间后（超过 session.gc_maxlifetime）,去检查数据库中的 session 数据，发现数据还在，而 /var/lib/php/sessions 下的 session 文件已经被清理掉了！当然有可能是概率的问题，后来我又试了几次，发现结果还是一样！

而当我将 php.ini 中指定的 session.gc_probability 改为大于 0 的数之后，发现数据库中的过期的数据被清除掉了。 

因此，大家在使用数据库存储 session 数据的时候一定要注意修改 session.gc_probability 配置项。

## **总结：**

1、通过这个例子，对 session 机制的理解更加深   
2、复习了一遍 PDO 操作（离上一次使用有点久）   
3、后续博客会谈谈缓存存储session数据

[0]: http://www.csdn.net/baidu_30000217/article/details/53572892
[1]: http://www.csdn.net/tag/session
[2]: http://www.csdn.net/tag/php
[7]: #
[8]: #t0
[9]: #t1
[10]: #t2
[11]: #t3
[12]: #t4
[13]: http://blog.csdn.net/baidu_30000217/article/details/53453202
[14]: http://blog.csdn.net/baidu_30000217/article/details/53466852
[15]: http://lib.csdn.net/base/mysql
[16]: http://lib.csdn.net/base/php
[17]: http://blog.csdn.net/baidu_30000217/article/details/51644539
[18]: ../img/20161211142801556.png
[19]: http://lib.csdn.net/base/softwaretest
[20]: ../img/20161211150545638.png
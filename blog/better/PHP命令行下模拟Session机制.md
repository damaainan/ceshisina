# [PHP命令行下模拟Session机制][0]

## April 26, 2014

_自动化测试过程中常规策略_

### 一．背景

Session称为会话，是指一个终端用户与交互系统进行通信的时间间隔，通常指从注册进入系统到注销退出系统之间所经过的时间，如果需要的话，可能还有一定的操作空间。通常情况下Session用于存储需要在整个用户会话过程中保持其状态的信息，例如登录信息或用户浏览 Web应用程序时需要的其它信息。

PHP的 `$_SESSION` 的功能之所以如此强大是因为有WebServer的支持，试想一下如果在命令行下读取一个 `$_SESSION` 变量，会是什么结果？

必然是`null`，因为PHP的`session_start()`函数在命令行下是无法使用的，假若一段逻辑结果中含有这个Session会话变量，该如何去测试它的有效性？

### 二．Session的原理

为了探究WebServer下的Session原理，我们做一个简单的测试：  
session.php的文件，内容很简单：

    session_start();

通过浏览器访问该文件，同时观察Request Header中的cookie信息以及服务器下的/tmp/目录：

![请求Cookie Request][1]

Cookie中存在一个PHPSESSID的值，而 /tmp/ 下存在一个对应的值，同时还可以知道这个 /tmp/sess_87bufd4ogid71e1gr6dtcbphi0是刚刚建立的，并且文件大小是0。 接着我们给SESSION赋点值：

    session_start();
    $_SESSION['login']  = 1;
    $_SESSION['name']   = 'Lancer He';
    $_SESSION['uid']    = 72;
    $_SESSION['groups'] = array(
        "dev" => 2,
        "loc" => 4,
    );

再观察浏览器的Request Header中的cookie信息依旧不变，但是却可以发现服务器下 /tmp/sess_87bufd4ogid71e1gr6dtcbphi0文件的大小更变，打开发现类似序列化(非序列化)的字符串，信息内容是之前`$_SESSION`的值：

![请求Cookie Request][2]

我们开启一个新的浏览器，比如IE，再查看/tmp/下的文件：

![请求Cookie Request][3]

观察新出现一个以sess为前缀的文件，同时IE的Cookie下出现了这个`PHPSESSID`的值。

因此我们可以基本理解Session的工作原理：

* 当session被启用的时候，一个唯一的标识被储存于本地的cookie中。
* 首先使用 `session_start()` 函数，PHP从`session`仓库中加载已经存储的session变量，如果这个仓库不存在，会被创建。
* 当操作 `$_SESSION`变量时，通过使用PHP内置`Session`函数处理`session`变量。
* 当PHP脚本执行结束时，未被销毁的`session`变量会被自动保存在本地一定路径下的`session`仓库中，这个路径可以通过php.ini文件中的session.save_path指定，默认在/tmp/目录。

### 三．Session策略设计

既然Session的默认机制是存放在文件中，因此我们是不是可以为了命令行模式做一个假的Session机制，因此不妨设计一个策略模式：

* 当通过浏览器请求，使用一个真的Session操作类来操作 `$_SESSION`全局变量。
* 当通过CLI模式请求php文件时，默认使用一个假的Session操作类。

让我们做这样简单的操作，无论在CLI模式或是Http模式都能正常运行：

    \Cores\Session::getInstance()->set('name', "Lancer");
    \Cores\Session::getInstance()->set('age',  "28");
    \Cores\Session::getInstance()->del("age");
    \Cores\Session::getInstance()->has("name")
    \Cores\Session::getInstance()->has("groups", array(
        "dev" => 2,
        "loc" => 4,
    ));

我们可以猜想到：

\Cores\Session对象的 `getInstance()` 方法必然是一个自动选择策略的过程，返回的是一个对象：

* 在CLI模式下返回的是 \Cores\Session_CLI 对象；
* 在普通模式下返回的是 \Cores\Session_Http 对象。

既然是一种策略模式， \Cores\Session_CLI 与 \Cores\Session_Http 必须拥有同样的方法来操作Session，所以需要提供一个接口 \Cores\Session_Interface

根据我们的想法，设计出简单的UML图，Session具有基本的五个方法：  
`start`(开始), `set`(赋值), `has`(存在), `get`(获取), `del`(删除)

![请求Cookie Request][4]

由于Session启动后在整个应用中必然是唯一实例，因此上图 \Cores\Session_CLI 与 \Cores\Session_Http都使用了单例模式，但 \Cores\Session_CLI 必须具有一些特殊的操作，比如写入`session`记录，创建`session_id`等伪操作，因此添加部分方法：

![请求Cookie Request][5]

### 四．程序实现

根据`Session`策略设计，开始编写对应的类：

接口类 **Session_Interface** (不可否认写接口是最没难度的)：

```php
    /**
     * Session接口
     * @author Lancer He <lancer.he@gmail.com>
     * @since  2014-04-23
     * @copyright http://www.crackedzone.com
     */
    interface Session_Interface {
        // 开启
        public function start();
        // 是否存在某个Session
        public function has($name);
        // 获取某个Session
        public function get($name='');
        // 给某个Session赋值
        public function set($name, $value);
        // 删除某个Session值
        public function del($name);
    }
```

**Session_Http**类，用于管理Http请求过来的Session策略：

```php
    /**
     * Http模式下管理$_SESSION类
     * @author Lancer He <lancer.he@gmail.com>
     * @since  2014-04-23
     * @copyright http://www.crackedzone.com
     */
    class Session_Http {
    
        protected static $_instance = null;
    
        /**
         * session是否已经开启
         * @var boolean
         */
        protected $_started = false;
    
        /**
         * 单例模式禁止Clone
         */
        private function __clone() {}
    
        /**
         * 单例模式禁止外部初始化
         */
        private function __construct() {}
    
        /**
         * 返回单例模式
         */
        public static function getInstance() {
            if ( ! is_null( self::$_instance ) ) {
                return self::$_instance;
            }
    
            $instance = new self();
            $instance->start();
            self::$_instance = $instance;
            return $instance;
        }
    
    
        /**
         * 开启Session
         * @return void
         */
        public function start() {
            session_start();
            $this->_started      = true;
        }
    
    
        /**
         * 通过name查看Session是否存在
         * @param  string $name
         * @return boolean
         */
        public function has($name) {
            return isset($_SESSION[$name]);
        }
    
    
        /**
         * 通过name从Session中获取一个值
         * @param  string $name 为空时返回整个sessino
         * @return mixed
         */
        public function get($name='') {
            if ( ! $name )
                return $_SESSION;
    
            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }
    
    
        /**
         * 给指定的name设置一个session值，返回连缀对象
         * @param  string $name
         * @param  mixed  $value
         * @return object
         */
        public function set($name, $value) {
            $_SESSION[$name] = $value;
            return $this;
        }
    
    
        /**
         * 从session中删除一个值，失败返回false，成功返回连缀对象
         * @param  string $name
         * @return false|object
         */
        public function del($name) {
            if ( ! $this->has($name) ) return false;
    
            unset($_SESSION[$name]);
            return $this;
        }
    }
```

**Session_Cli**类，用于命令行下模拟Session效果：

```php
    /**
     * CLI模式下会模拟一个session_id，同时在/tmp/下产生一个sesscli文件用来保存session信息
     * @author Lancer He <lancer.he@gmail.com>
     * @since  2014-04-23
     * @copyright http://www.crackedzone.com
     */
    class Session_Cli {
    
        protected static $_instance = null;
    
        /**
         * session_id
         * @var string
         */
        protected $_session_id = null;
    
        /**
         * session file
         * @var string
         */
        protected $_session_file = null;
    
        /**
         * session数组
         * @var array
         */
        protected $_session = array();
    
        /**
         * session是否已经开启
         * @var boolean
         */
        protected $_started = false;
    
        /**
         * 单例模式禁止Clone
         */
        private function __clone() {}
    
        /**
         * 单例模式禁止外部初始化
         */
        private function __construct() {}
    
        /**
         * 返回单例模式
         */
        public static function getInstance() {
            if ( ! is_null( self::$_instance ) ) {
                return self::$_instance;
            }
    
            $instance = new self();
            $instance->start();
            self::$_instance = $instance;
            return $instance;
        }
    
    
        /**
         * 开启Session
         * @return void
         */
        public function start() {
            $this->_init();
            $this->_started      = true;
        }
    
    
        /**
         * 初始session
         * @return void
         */
        protected function _init() {
            $this->_session_id   = md5(uniqid());
            $this->_session_file = '/tmp/' . APPLICATION_CLI_SESSION_FILE_PREFIX . $this->_session_id;
            if ( file_exists($this->_session_file) ) {
                $this->_session = unserialize( file_get_contents($this->_session_file) );
                return;
            }
    
            file_put_contents($this->_session_file, null);
        }
    
    
        /**
         * 通过name查看Session是否存在
         * @param  string $name
         * @return boolean
         */
        public function has($name) {
            return isset($this->_session[$name]);
        }
    
    
        /**
         * 通过name从Session中获取一个值
         * @param  string $name 为空时返回整个sessino
         * @return mixed
         */
        public function get($name='') {
            if ( ! $name )
                return $this->_session;
    
            return isset($this->_session[$name]) ? $this->_session[$name] : null;
        }
    
    
        /**
         * 给指定的name设置一个session值，返回连缀对象
         * @param  string $name
         * @param  mixed  $value
         * @return object
         */
        public function set($name, $value) {
            $this->_session[$name] = $value;
            return $this;
        }
    
    
        /**
         * 从session中删除一个值，失败返回false，成功返回连缀对象
         * @param  string $name
         * @return false|object
         */
        public function del($name) {
            if ( ! $this->has($name) ) return false;
    
            unset($this->_session[$name]);
            return $this;
        }
    
    
        /**
         * 将session存放到tmp文件中
         * @return void
         */
        public function __destruct() {
            file_put_contents($this->_session_file, serialize($this->_session) );
        }
    }
```

环境使用角色类 **Session**：  
由于具体策略类已经完成，所以我们只需要定义一个常量用于区分是否是CLI请求，同样使用单例模式自动装载对应的具体策略。

```php
    class Session {
        public static function getInstance() {
            return APPLICATION_IS_CLI ? Session_Cli::getInstance() : Session_Http::getInstance();
        }
    }
```

**测试过程**：将设计的程序，通过Http和Cli方式分别测试：  
Cli测试结果：  
![请求Cookie Request][6]

  
![请求Cookie Request][7]

Http测试结果：  
![请求Cookie Request][8]

  
![请求Cookie Request][9]

虽然保存在 /tmp/ 目录下的内容格式不一致，但已经模拟出一个Session仓库的功能，实现了对这个仓库的增删改查功能。

### 五．小结

通过策略模式模拟一个虚拟的Session功能，保证Session在命令行下能够正常工作，为项目的自动化测试提供了基本支持。

策略模式其用意在于封装了一组新的算法，基于不同的策略下能够互相替换，为此我们能够在自动化测试中模拟出更多的功能，如请求的Request功能，渲染的View功能等。

[0]: http://www.crackedzone.com/php-cli-using-session-strategy.html
[1]: ./img/session_1.jpg
[2]: ./img/session_2.jpg
[3]: ./img/session_3.jpg
[4]: ./img/session_4.jpg
[5]: ./img/session_5.jpg
[6]: ./img/session_6.jpg
[7]: ./img/session_7.jpg
[8]: ./img/session_8.jpg
[9]: ./img/session_9.jpg
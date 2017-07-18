# 【redis专题(13)】在PHP中使用redis（2）

Redis- - -

我们在使用PHPredis扩展来操作PHP时，不会直接new redis();，一般要重新封装一下，才进行使用。

封装什么？怎么封装？ 为什么要封装？

1. 使用适配器模式，便于后期缓存中间件的替换，比如某日从redis替换为memcache。
1. 使用单列模式，使内存中只有一个缓存实例。
1. 缓存KEY要统一配置，便于后期批量更改和管理。


## 缓存适配器 （Thinkphp）

```php
namespace Think;
/**
 * 缓存管理类
 */
class Cache{
    /**
     * 操作句柄
     * @var string
     * @access protected
     */
    protected $handler;
    /**
     * 缓存连接参数
     * @var integer
     * @access protected
     */
    protected $options = [];
    /**
     * 连接缓存
     * @access public
     * @param string $type 缓存类型
     * @param array $options 配置数组
     * @return object
     */
    public function connect($type = '' , $options = []){
        if (empty($type))
            $type = C('DATA_CACHE_TYPE');
        $class = strpos($type , '\\') ? $type : 'Think\\Cache\\Driver\\' . ucwords(strtolower($type));
        if (class_exists($class))
            $cache = new $class($options); else
            E(L('_CACHE_TYPE_INVALID_') . ':' . $type);  //错误输出
        return $cache;
    }
    /**
     * 取得缓存类实例
     * @static
     * @access public
     * @return mixed
     */
    static function getInstance($type = '' , $options = []){
        static $_instance = [];
        $guid = $type . to_guid_string($options);  //全局唯一key生成，实现单列；
        if (!isset($_instance[$guid])) {
            $obj = new Cache();
            $_instance[$guid] = $obj->connect($type , $options);
        }
        return $_instance[$guid];
    }
    // 魔术方法，调用缓存中间件中类的get方法获取缓存值，即$cache->name;
    public function __get($name){
        return $this->get($name);
    }
    // 魔术方法，调用缓存中间件中类的set方法设置缓存值，即$cache->name = $value;
    public function __set($name , $value){
        return $this->set($name , $value);
    }
    // unset($cache->name); 就删掉了该缓存；
    public function __unset($name){
        $this->rm($name);
    }
    //调用缓存类型自己的方法
    public function __call($method , $args){
        if (method_exists($this->handler , $method)) {
            return call_user_func_array([$this->handler , $method] , $args);
        } else {
            E(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
    public function setOptions($name , $value){
        $this->options[$name] = $value;
    }
    public function getOptions($name){
        return $this->options[$name];
    }
    /**
     * 队列缓存，设置和获取队列key的值
     * @access protected
     * @param string $key 队列名
     * @return mixed
     */
    protected function queue($key){
        static $_handler = [
            // 获取与设置key到队列，三种驱动方法；
            'file' => ['F' , 'F'] ,
            'xcache' => ['xcache_get' , 'xcache_set'] ,
            'apc' => ['apc_fetch' , 'apc_store'] ,
        ];
        $queue = isset($this->options['queue']) ? $this->options['queue'] : 'file';
        $fun = isset($_handler[$queue]) ? $_handler[$queue] : $_handler['file'];
        $queue_name = isset($this->options['queue_name']) ? $this->options['queue_name'] : 'think_queue';
        $value = $fun[0]($queue_name);
        if (!$value) {
            $value = [];
        }
        // 进列
        if (false === array_search($key , $value))
            array_push($value , $key);
        if (count($value) > $this->options['length']) {
            // 出列
            $key = array_shift($value);
            // 删除缓存
            $this->rm($key);
            if (APP_DEUBG) {
                //调试模式下，记录出列次数
                N($queue_name . '_out_times' , 1 , true);
            }
        }
        return $fun[1]($queue_name , $value);
    }
}
```

## Redis缓存驱动类（Thinkphp）

```php
namespace Think\Cache\Driver;
use Think\Cache;
class Redis extends Cache {
     /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options)) {
            $options = array (
                'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
                'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
                'timeout'       => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent'    => false,
            );
        }
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;   //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }
    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }
    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }
}
```

## KEY的配置类

    

```php 
/**
 *  Redis内相关的key映射类
 */
class RedisKeyMap {
    /**
     * 获取积分key值
     */
    public static function getProfileKey($bizType, $bizNo) {
        return self::getKey( $bizType, $bizNo );
    }
    /**
     * 获取key值
     */
    protected static function getKey( $type, $bizType, $bizNo ) {
        return implode( ':', [$type, $bizType, $bizNo] );
    }
}
```

## 示列

    
```php 

// 配置，可以写进配置文件里面；
$option = [
    'host' => '192.168.3.55' ,
    'port' => 6379,
    'timeout' => false,
    'length' => 10 ,
    'expire' => 360,
    'queue_name' => 'testQueue',
    'prefix' => 'zxg_',  //出于安全性考虑给缓存key加上前缀；
];
$cache = \Think\Cache::getInstance('redis',$option);
$cache->name = 'zxg';
echo $cache->name; // zxg
unset($cache->name);
$cache->name; // false; 不存在；
// 队列，只保存前十个
for($i=0;$i<=100;$i++){
    $str = microtime();
    $key = 'key_'.$str;
    $cache->set($key,$i.':'.$str);
}
```

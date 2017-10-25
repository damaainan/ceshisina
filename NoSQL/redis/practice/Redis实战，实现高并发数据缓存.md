# Redis实战，实现高并发数据缓存【原创】

 时间 2017-09-18 11:13:27  

原文[http://blog.it985.com/22295.html][1]


近期遇到项目需要对终端的高并发请求进行记录，尝试使用直接写入数据库操作使用Jmeter进行100线程无限循环测试，10秒后虚拟机进入宕机状态，实测此方法效率之低已经无法满足当前需求，在不进行多服务器配置情况下决定使用Redis将请求数据进行暂存，并定时将暂存数据存储至SQL中，避开高峰期。

Redis的安装方法请参考 [【项目实战】-Linux Redis 配置和安装【原创】][3]

由于实际情况在sh文件中加入

    sed -i "s/# requirepass foobared/requirepass root/g" /etc/redis/redis.conf;##修改认证密码为root 可自行修改

## Bundle准备篇

经过查找之后在packagist找到了 [SncRedisBundle][4] 用于实现在Symfony中集成 [predis][5]

在composer.json中写入

    //composer.json
       "require": {
            //...
            "snc/redis-bundle": "^2.0",
            "predis/predis": "^1.0"
        },

执行composer update

在config.yml写入配置文件

    snc_redis:
        clients:
            default:
                type: predis
                alias: default
                dsn: redis://localhost
                logging: %kernel.debug%

至此直接使用

    $container->get('snc_redis.default');

获取redis服务对象就可对redis数据进行操作处理。

## 实际应用

目前项目需求需要记录的数据为同一数据对于不同终端每次的请求记录独立记录 现假设数据为A B…. 终端为E 设计如下结构进行存储

    具体使用何种类型储存缓存播放信息在代码中有具体说明 此处说明此缓存使用到的各个字段组成以及相关意义
    
    prefix_ + Unique_identification  存储E id 减少高并发读取 且此参数常驻内存 Unique_identification 唯一标识位
    
    prefix_redis + redis_sid    存储当前节点 用于防止备份过程中出现新数据被销毁导致数据丢失 redis_sid为备份自增量 
    
    [field_name]:Unique_identification_list:[redis_sid] 存储当前播放终端id ex:field_name区分 A B ....
    
    [field_name]:play_list:[redis_sid]:[EQUIPMENT id]  列表存储播放记录 单条具体数据: {"record_id":1,"play_at":"1505291366"}

下面放上Model层代码

```php
    <?php
    
    /**
     * 记录处理
     *
     * Author: Pota
     * Datetime: 9/11/17 8:38 PM
     */
    
    namespace Model;
    
    use ApiBundle\Controller\ApiController;
    use BaseBundle\BaseBundle;
    
    class a_record extends ApiController
    {
        private $field_name;
    
        private $table_type;
    
        private $redis_sid;
        private $save_redis_sid;
        private $is_second = 0;
    
        private $lpush_key;
        private $sadd_key;
    
        private $redis;
    
        /**
         * 获取sid
         *
         * @return int|string
         */
        private function getSid()
        {
            $redis_sid = $this->redis->get('redis_sid',$this->getParameter('prefix_redis'));
            if (!$redis_sid){
                $redis_sid = 1;
                $this->redis->set('redis_sid', $redis_sid,$this->getParameter('prefix_redis'));
            }
    
            return $redis_sid;
        }
    
        /**
         * 增长sid
         *
         * @return int
         */
        private function addSid()
        {
            $redis_sid = $this->redis->getClient()->incr($this->getParameter('prefix_redis').'redis_sid');
            if ($redis_sid > $this->getParameter('max_redis_sid')){
                $redis_sid = 1;
                $this->redis->set('redis_sid',$redis_sid ,$this->getParameter('prefix_redis'));
            }
    
            return $redis_sid;
        }
    
        /*
         * a_record constructor.
         */
        public function __construct()
        {
            parent::__construct();
            $this->container = BaseBundle::getContainer();
            //初始化参数
            $this->redis = $this->get('services.redis');
    
            $this->field_name = $this->table_type ? "advertisement" : "illustrated";
            //读取配置文件
            $this->redis_sid = $this->is_second ?$this->save_redis_sid:$this->getSid();
    
            $this->lpush_key = $this->field_name.':play_list:'.$this->redis_sid.':';
    
            $this->sadd_key = $this->field_name.':mac_list:'.$this->redis_sid;
        }
    
        /**
         * 记录播放记录
         *
         * @param $equipment_id 终端id
         * @param $other_id 
         * @param $play_at 播放时间
         * @return bool
         */
        public function add($equipment_id, $other_id,$play_at)
        {
            $play_at =  date('Y-m-d H:i:s', $play_at);
            //使用 redis集合 存储当前发送的设备id $field_name:mac_list:[redis_sid]
            $this->redis->getClient()->sadd($this->sadd_key, $equipment_id);
            //使用列表存储近段时间 播放记录 $field_name:pay_list:[redis_sid]:[EQUIPMENT id]
            $this->redis->getClient()->lpush($this->lpush_key.$equipment_id, json_encode(array($this->field_name.'_id'=>$other_id,'play_at'=>$play_at)));
           
            return true;
        }
    
        /**
         * 数据备份
         *
         * @return bool
         */
        public function backups()
        {
            $this->save_redis_sid = $this->getSid();
            //开启新的记录-以防读取处理过程中新数据被误删
            $this->addSid();
            //记录处理
            $this->addDetails();
    
            return true;
        }
    
        /**
         * 备份数据处理
         *
         * @return bool
         * @throws \Doctrine\DBAL\DBALException
         */
        private function addDetails()
        {
            $conn = $this->get('database_connection');
            $id = $this->field_name . '_id';
            //播放记录备份
            $equipment = $this->redis->getClient()->smembers($this->sadd_key);
            //构造SQL-INSERT 并记录播放次数-修改相关记录
            $illustrated_sql = "INSERT INTO {$this->field_name}_play_details ({$this->field_name}_id, equipment_id, play_at) VALUES ";
            //循环设备 记录所有列表信息 并修改播放次数
    
            foreach ($equipment AS $v){
                //获取记录总数
                $length = $this->redis->getClient()->llen($this->lpush_key.$v);
                $pay_list = $this->redis->getClient()->lrange($this->lpush_key.$v, 0, $length);
                if (!empty($pay_list))
               
                //实际逻辑代码请自行实现 自行构建SQL实现批量插入
    
                //清除相关缓存信息
                $this->redis->getClient()->del($this->lpush_key.$v);
            }
            $this->redis->getClient()->del($this->sadd_key);
    
            return true;
        }
    }
```

执行完add炒作时候应该生成以下数据

![][6]

由上可看出当前存储节点为4 设备记录列表只有id为1的终端进行了请求

下面为以上使用的redis service代码

```php
    <?php
    
    /**
     * redis-除基础get-set均不可直接复用 其他项目使用请删除
     *
     * Author: Pota
     * Datetime: 9/10/17 11:52 PM
     */
    
    namespace Services;
    
    use Symfony\Component\DependencyInjection\ContainerInterface;
    
    class Redis
    {
        private $container;
    
        private $redis_default;
    
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
            $this->redis_default = $container->get('snc_redis.default');
            //进行redis认证
            $this->redis_default->auth($container->getParameter('redis_auth'));
        }
    
        /**
         * 返回redis对象
         *
         * @return \Predis\Client
         */
        public function getClient()
        {
            return $this->redis_default;
        }
    
        /**
         * 设置key-value
         *
         * @param $key
         * @param $value
         * @param null $prefix
         * @return bool|int|mixed
         */
        public function set($key, $value, $prefix = null)
        {
            $result = $this->redis_default->exists($prefix . $key);
            if (!$result){
                $result = $this->redis_default->set($prefix . $key, $value);
                $result = $result?true:false;
            }
    
            return $result;
        }
    
        /**
         * 获取值
         *
         * @param $key
         * @param null $prefix
         * @return string
         */
        public function get($key, $prefix = null)
        {
            $value = $this->redis_default->get($prefix . $key);
    
            return $value;
        }
    
        public function has($key)
        {
            return $this->redis_default->exists($key)?true:false;
        }
    
    
    }
```

[1]: http://blog.it985.com/22295.html
[3]: http://blog.it985.com/20205.html
[4]: https://github.com/snc/SncRedisBundle
[5]: https://github.com/nrk/predis
[6]: https://img1.tuicool.com/aMN7nir.png
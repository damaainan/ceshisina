## PHP 高级编程之多线程

>  http://netkiller.github.io/journal/thread.php.html

Mr. Neo Chen (netkiller), 陈景峰(BG7NYT)

目录

1. 多线程环境安装  
    1.1. PHP 5.5.9  
    1.2. 安装 pthreads 扩展    
2. Thread  
3. Worker 与 Stackable  
4. 互斥锁  
    4.1. 多线程与共享内存    
5. 线程同步  
6. 线程池  
    6.1. 线程池  
    6.2. 动态队列线程池  
    6.3. pthreads Pool类    
7. 多线程文件安全读写（文件锁）  
8. 多线程与数据连接  
    8.1. Worker 与 PDO  
    8.2. Pool 与 PDO  
    8.3. 多线程中操作数据库总结  
9. Thread And ZeroMQ  
    9.1. 数据库端  
    9.2. 数据处理端  

## 1. 多线程环境安装

### 1.1. PHP 5.5.9

安装PHP 5.5.9

https://github.com/oscm/shell/blob/master/php/5.5.9.sh

    ./configure --prefix=/srv/php-5.5.9 \
    --with-config-file-path=/srv/php-5.5.9/etc \
    --with-config-file-scan-dir=/srv/php-5.5.9/etc/conf.d \
    --enable-fpm \
    --with-fpm-user=www \
    --with-fpm-group=www \
    --with-pear \
    --with-curl \
    --with-gd \
    --with-jpeg-dir \
    --with-png-dir \
    --with-freetype-dir \
    --with-zlib-dir \
    --with-iconv \
    --with-mcrypt \
    --with-mhash \
    --with-pdo-mysql \
    --with-mysql-sock=/var/lib/mysql/mysql.sock \
    --with-openssl \
    --with-xsl \
    --with-recode \
    --enable-sockets \
    --enable-soap \
    --enable-mbstring \
    --enable-gd-native-ttf \
    --enable-zip \
    --enable-xml \
    --enable-bcmath \
    --enable-calendar \
    --enable-shmop \
    --enable-dba \
    --enable-wddx \
    --enable-sysvsem \
    --enable-sysvshm \
    --enable-sysvmsg \
    --enable-opcache \
    --enable-pcntl \
    --enable-maintainer-zts \
    --disable-debug
                

编译必须启用zts支持否则无法安装 pthreads(--enable-maintainer-zts)

### 1.2. 安装 pthreads 扩展

安装https://github.com/oscm/shell/blob/master/php/pecl/pthreads.sh

    # curl -s https://raw.github.com/oscm/shell/master/php/pecl/pthreads.sh | bash
                

查看pthreads是否已经安装

    # php -m | grep pthreads
                

## 2. Thread

```php
    <?php
    class HelloWorld extends Thread {
        public function __construct($world) {
           $this->world = $world;
        }
    
        public function run() {
            print_r(sprintf("Hello %s\n", $this->world));
        }
    }
    
    $thread = new HelloWorld("World");
    
    if ($thread->start()) {
        printf("Thread #%lu says: %s\n", $thread->getThreadId(), $thread->join());
    }
    ?>
```

## 3. Worker 与 Stackable

```php
    <?php
    class SQLQuery extends Stackable {
    
            public function __construct($sql) {
                    $this->sql = $sql;
            }
    
            public function run() {
                    $dbh  = $this->worker->getConnection();
                    $row = $dbh->query($this->sql);
                    while($member = $row->fetch(PDO::FETCH_ASSOC)){
                            print_r($member);
                    }
            }
    
    }
    
    class ExampleWorker extends Worker {
            public static $dbh;
            public function __construct($name) {
            }
    
            /*
            * The run method should just prepare the environment for the work that is coming ...
            */
            public function run(){
                    self::$dbh = new PDO('mysql:host=192.168.2.1;dbname=example','www','123456');
            }
            public function getConnection(){
                    return self::$dbh;
            }
    }
    
    $worker = new ExampleWorker("My Worker Thread");
    
    $work=new SQLQuery('select * from members order by id desc limit 5');
    $worker->stack($work);
    
    $table1 = new SQLQuery('select * from demousers limit 2');
    $worker->stack($table1);
    
    $worker->start();
    $worker->shutdown();
    ?>
```

## 4. 互斥锁

什么情况下会用到互斥锁？在你需要控制多个线程同一时刻只能有一个线程工作的情况下可以使用。

下面我们举一个例子，一个简单的计数器程序，说明有无互斥锁情况下的不同。

```php
    <?php
    $counter = 0;
    //$handle=fopen("php://memory", "rw");
    //$handle=fopen("php://temp", "rw");
    $handle=fopen("/tmp/counter.txt", "w");
    fwrite($handle, $counter );
    fclose($handle);
    
    class CounterThread extends Thread {
        public function __construct($mutex = null){
            $this->mutex = $mutex;
            $this->handle = fopen("/tmp/counter.txt", "w+");
        }
        public function __destruct(){
            fclose($this->handle);
        }
        public function run() {
            if($this->mutex)
                $locked=Mutex::lock($this->mutex);
    
            $counter = intval(fgets($this->handle));
            $counter++;
            rewind($this->handle);
            fputs($this->handle, $counter );
            printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);
    
            if($this->mutex)
                Mutex::unlock($this->mutex);
        }
    }
    
    //没有互斥锁
    for ($i=0;$i<50;$i++){
        $threads[$i] = new CounterThread();
        $threads[$i]->start();
    
    }
    
    //加入互斥锁
    $mutex = Mutex::create(true);
    for ($i=0;$i<50;$i++){
        $threads[$i] = new CounterThread($mutex);
        $threads[$i]->start();
    
    }
    
    Mutex::unlock($mutex);
    for ($i=0;$i<50;$i++){
        $threads[$i]->join();
    }
    Mutex::destroy($mutex);
    
    ?>
```

我们使用文件/tmp/counter.txt保存计数器值，每次打开该文件将数值加一，然后写回文件。当多个线程同时操作一个文件的时候，就会线程运行先后取到的数值不同，写回的数值也不同，最终计数器的数值会混乱。

没有加入锁的结果是计数始终被覆盖，最终结果是2

而加入互斥锁后，只有其中的一个进程完成加一工作并释放锁，其他线程才能得到解锁信号，最终顺利完成计数器累加操作

上面例子也可以通过对文件加锁实现，这里主要讲的是多线程锁，后面会涉及文件锁。

### 4.1. 多线程与共享内存

在共享内存的例子中，没有使用任何锁，仍然可能正常工作，可能工作内存操作本身具备锁的功能。

```php
    <?php
    $tmp = tempnam(__FILE__, 'PHP');
    $key = ftok($tmp, 'a');
    
    $shmid = shm_attach($key);
    $counter = 0;
    shm_put_var( $shmid, 1, $counter );
    
    class CounterThread extends Thread {
        public function __construct($shmid){
            $this->shmid = $shmid;
        }
        public function run() {
    
            $counter = shm_get_var( $this->shmid, 1 );
            $counter++;
            shm_put_var( $this->shmid, 1, $counter );
    
            printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);
        }
    }
    
    for ($i=0;$i<100;$i++){
        $threads[] = new CounterThread($shmid);
    }
    for ($i=0;$i<100;$i++){
        $threads[$i]->start();
    
    }
    
    for ($i=0;$i<100;$i++){
        $threads[$i]->join();
    }
    shm_remove( $shmid );
    shm_detach( $shmid );
    ?>
```

## 5. 线程同步

有些场景我们不希望 thread->start() 就开始运行程序，而是希望线程等待我们的命令。

$thread->wait();作用是 thread->start()后线程并不会立即运行，只有收到 $thread->notify(); 发出的信号后才运行 

```php
    <?php
    $tmp = tempnam(__FILE__, 'PHP');
    $key = ftok($tmp, 'a');
    
    $shmid = shm_attach($key);
    $counter = 0;
    shm_put_var( $shmid, 1, $counter );
    
    class CounterThread extends Thread {
        public function __construct($shmid){
            $this->shmid = $shmid;
        }
        public function run() {
    
            $this->synchronized(function($thread){
                $thread->wait();
            }, $this);
    
            $counter = shm_get_var( $this->shmid, 1 );
            $counter++;
            shm_put_var( $this->shmid, 1, $counter );
    
            printf("Thread #%lu says: %s\n", $this->getThreadId(),$counter);
        }
    }
    
    for ($i=0;$i<100;$i++){
        $threads[] = new CounterThread($shmid);
    }
    for ($i=0;$i<100;$i++){
        $threads[$i]->start();
    
    }
    
    for ($i=0;$i<100;$i++){
        $threads[$i]->synchronized(function($thread){
            $thread->notify();
        }, $threads[$i]);
    }
    
    for ($i=0;$i<100;$i++){
        $threads[$i]->join();
    }
    shm_remove( $shmid );
    shm_detach( $shmid );
    ?>
```

## 6. 线程池

### 6.1. 线程池

自行实现一个Pool类

```php
    <?php
    class Update extends Thread {
    
        public $running = false;
        public $row = array();
        public function __construct($row) {
    
        $this->row = $row;
            $this->sql = null;
        }
    
        public function run() {
    
        if(strlen($this->row['bankno']) > 100 ){
            $bankno = safenet_decrypt($this->row['bankno']);
        }else{
            $error = sprintf("%s, %s\r\n",$this->row['id'], $this->row['bankno']);
            file_put_contents("bankno_error.log", $error, FILE_APPEND);
        }
    
        if( strlen($bankno) > 7 ){
            $sql = sprintf("update members set bankno = '%s' where id = '%s';", $bankno, $this->row['id']);
    
            $this->sql = $sql;
        }
    
        printf("%s\n",$this->sql);
        }
    
    }
    
    class Pool {
        public $pool = array();
        public function __construct($count) {
            $this->count = $count;
        }
        public function push($row){
            if(count($this->pool) < $this->count){
                $this->pool[] = new Update($row);
                return true;
            }else{
                return false;
            }
        }
        public function start(){
            foreach ( $this->pool as $id => $worker){
                $this->pool[$id]->start();
            }
        }
        public function join(){
            foreach ( $this->pool as $id => $worker){
                   $this->pool[$id]->join();
            }
        }
        public function clean(){
            foreach ( $this->pool as $id => $worker){
                if(! $worker->isRunning()){
                    unset($this->pool[$id]);
                }
            }
        }
    }
    
    try {
        $dbh    = new PDO("mysql:host=" . str_replace(':', ';port=', $dbhost) . ";dbname=$dbname", $dbuser, $dbpw, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            PDO::MYSQL_ATTR_COMPRESS => true
            )
        );
    
        $sql  = "select id,bankno from members order by id desc";
        $row = $dbh->query($sql);
        $pool = new Pool(5);
        while($member = $row->fetch(PDO::FETCH_ASSOC))
        {
    
            while(true){
                if($pool->push($member)){ //压入任务到池中
                    break;
                }else{ //如果池已经满，就开始启动线程
                    $pool->start();
                    $pool->join();
                    $pool->clean();
                }
            }
        }
        $pool->start();
        $pool->join();
    
        $dbh = null;
    
    } catch (Exception $e) {
        echo '[' , date('H:i:s') , ']', '系统错误', $e->getMessage(), "\n";
    }
    ?>
```

### 6.2. 动态队列线程池

上面的例子是当线程池满后执行start统一启动，下面的例子是只要线程池中有空闲便立即创建新线程。

```php
    <?php
    class Update extends Thread {
    
        public $running = false;
        public $row = array();
        public function __construct($row) {
    
        $this->row = $row;
            $this->sql = null;
        //print_r($this->row);
        }
    
        public function run() {
    
        if(strlen($this->row['bankno']) > 100 ){
            $bankno = safenet_decrypt($this->row['bankno']);
        }else{
            $error = sprintf("%s, %s\r\n",$this->row['id'], $this->row['bankno']);
            file_put_contents("bankno_error.log", $error, FILE_APPEND);
        }
    
        if( strlen($bankno) > 7 ){
            $sql = sprintf("update members set bankno = '%s' where id = '%s';", $bankno, $this->row['id']);
    
            $this->sql = $sql;
        }
    
        printf("%s\n",$this->sql);
        }
    
    }
    
    
    
    try {
        $dbh    = new PDO("mysql:host=" . str_replace(':', ';port=', $dbhost) . ";dbname=$dbname", $dbuser, $dbpw, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            PDO::MYSQL_ATTR_COMPRESS => true
            )
        );
    
        $sql     = "select id,bankno from members order by id desc limit 50";
    
        $row = $dbh->query($sql);
        $pool = array();
        while($member = $row->fetch(PDO::FETCH_ASSOC))
        {
            $id     = $member['id'];
            while (true){
                if(count($pool) < 5){
                    $pool[$id] = new Update($member);
                    $pool[$id]->start();
                    break;
                }else{
                    foreach ( $pool as $name => $worker){
                        if(! $worker->isRunning()){
                            unset($pool[$name]);
                        }
                    }
                }
            }
    
        }
    
        $dbh = null;
    
    } catch (Exception $e) {
        echo '【' , date('H:i:s') , '】', '【系统错误】', $e->getMessage(), "\n";
    }
    ?>
```

### 6.3. pthreads Pool类

pthreads 提供的 Pool class 例子

```php
    <?php
    
    class WebWorker extends Worker {
    
        public function __construct(SafeLog $logger) {
            $this->logger = $logger;
        }
    
        protected $loger;
    }
    
    class WebWork extends Stackable {
    
        public function isComplete() {
            return $this->complete;
        }
    
        public function run() {
            $this->worker
                ->logger
                ->log("%s executing in Thread #%lu",
                      __CLASS__, $this->worker->getThreadId());
            $this->complete = true;
        }
    
        protected $complete;
    }
    
    class SafeLog extends Stackable {
    
        protected function log($message, $args = []) {
            $args = func_get_args();
    
            if (($message = array_shift($args))) {
                echo vsprintf(
                    "{$message}\n", $args);
            }
        }
    }
    
    
    $pool = new Pool(8, \WebWorker::class, [new SafeLog()]);
    
    $pool->submit($w=new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->submit(new WebWork());
    $pool->shutdown();
    
    $pool->collect(function($work){
        return $work->isComplete();
    });
    
    var_dump($pool);
```

## 7. 多线程文件安全读写（文件锁）

文件所种类。

    LOCK_SH 取得共享锁定（读取的程序）。
    LOCK_EX 取得独占锁定（写入的程序。
    LOCK_UN 释放锁定（无论共享或独占）。
    LOCK_NB 如果不希望 flock() 在锁定时堵塞
            

共享锁例子

```php
    <?php
    
    $fp = fopen("/tmp/lock.txt", "r+");
    
    if (flock($fp, LOCK_EX)) {  // 进行排它型锁定
        ftruncate($fp, 0);      // truncate file
        fwrite($fp, "Write something here\n");
        fflush($fp);            // flush output before releasing the lock
        flock($fp, LOCK_UN);    // 释放锁定
    } else {
        echo "Couldn't get the lock!";
    }
    
    fclose($fp);
    
    ?>
```

共享锁例子2

```php
    <?php
    $fp = fopen('/tmp/lock.txt', 'r+');
    
    /* Activate the LOCK_NB option on an LOCK_EX operation */
    if(!flock($fp, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }
    
    /* ... */
    
    fclose($fp);
    ?>
```

## 8. 多线程与数据连接

pthreads 与 pdo 同时使用是，需要注意一点，需要静态声明public static $dbh;并且通过单例模式访问数据库连接。

### 8.1. Worker 与 PDO

```php
    <?php
    class Work extends Stackable {
    
            public function __construct() {
            }
    
            public function run() {
                    $dbh  = $this->worker->getConnection();
                    $sql     = "select id,name from members order by id desc limit 50";
                    $row = $dbh->query($sql);
                    while($member = $row->fetch(PDO::FETCH_ASSOC)){
                            print_r($member);
                    }
            }
    
    }
    
    class ExampleWorker extends Worker {
            public static $dbh;
            public function __construct($name) {
            }
    
            /*
            * The run method should just prepare the environment for the work that is coming ...
            */
            public function run(){
                    self::$dbh = new PDO('mysql:host=192.168.2.1;dbname=example','www','123456');
            }
            public function getConnection(){
                    return self::$dbh;
            }
    }
    
    $worker = new ExampleWorker("My Worker Thread");
    
    $work=new Work();
    $worker->stack($work);
    
    $worker->start();
    $worker->shutdown();
    ?>
```

### 8.2. Pool 与 PDO

在线程池中链接数据库

```php
    # cat pool.php
    <?php
    class ExampleWorker extends Worker {
    
        public function __construct(Logging $logger) {
            $this->logger = $logger;
        }
    
        protected $logger;
    }
    
    /* the collectable class implements machinery for Pool::collect */
    class Work extends Stackable {
        public function __construct($number) {
            $this->number = $number;
        }
        public function run() {
                    $dbhost = 'db.example.com';               // 数据库服务器
                    $dbuser = 'example.com';                 // 数据库用户名
                    $dbpw = 'password';                               // 数据库密码
                    $dbname = 'example_real';
            $dbh  = new PDO("mysql:host=$dbhost;port=3306;dbname=$dbname", $dbuser, $dbpw, array(
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                            PDO::MYSQL_ATTR_COMPRESS => true,
                PDO::ATTR_PERSISTENT => true
                            )
                    );
            $sql = "select OPEN_TIME, `COMMENT` from MT4_TRADES where LOGIN='".$this->number['name']."' and CMD='6' and `COMMENT` = '".$this->number['order'].":DEPOSIT'";
            #echo $sql;
            $row = $dbh->query($sql);
            $mt4_trades  = $row->fetch(PDO::FETCH_ASSOC);
            if($mt4_trades){
    
                $row = null;
    
                $sql = "UPDATE db_example.accounts SET paystatus='成功', deposit_time='".$mt4_trades['OPEN_TIME']."' where `order` = '".$this->number['order']."';";
                $dbh->query($sql);
                #printf("%s\n",$sql);
            }
            $dbh = null;
            printf("runtime: %s, %s, %s\n", date('Y-m-d H:i:s'), $this->worker->getThreadId() ,$this->number['order']);
    
        }
    }
    
    class Logging extends Stackable {
        protected  static $dbh;
        public function __construct() {
            $dbhost = 'db.example.com';         // 数据库服务器
                $dbuser = 'example.com';                 // 数据库用户名
                $dbpw = 'password';                               // 数据库密码
            $dbname = 'example_real';           // 数据库名
    
            self::$dbh  = new PDO("mysql:host=$dbhost;port=3306;dbname=$dbname", $dbuser, $dbpw, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::MYSQL_ATTR_COMPRESS => true
                )
            );
    
        }
        protected function log($message, $args = []) {
            $args = func_get_args();
    
            if (($message = array_shift($args))) {
                echo vsprintf("{$message}\n", $args);
            }
        }
    
        protected function getConnection(){
                    return self::$dbh;
            }
    }
    
    $pool = new Pool(200, \ExampleWorker::class, [new Logging()]);
    
    $dbhost = 'db.example.com';                      // 数据库服务器
    $dbuser = 'example.com';                 // 数据库用户名
    $dbpw = 'password';                               // 数据库密码
    $dbname = 'db_example';
    $dbh    = new PDO("mysql:host=$dbhost;port=3306;dbname=$dbname", $dbuser, $dbpw, array(
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                            PDO::MYSQL_ATTR_COMPRESS => true
                            )
                    );
    $sql = "select `order`,name from accounts where deposit_time is null order by id desc";
    
    $row = $dbh->query($sql);
    while($account = $row->fetch(PDO::FETCH_ASSOC))
    {
            $pool->submit(new Work($account));
    }
    
    $pool->shutdown();
    
    ?>
```

进一步改进上面程序，我们使用单例模式 $this->worker->getInstance(); 全局仅仅做一次数据库连接，线程使用共享的数据库连接

```php
    <?php
    class ExampleWorker extends Worker {
    
        #public function __construct(Logging $logger) {
        #   $this->logger = $logger;
        #}
    
        #protected $logger;
        protected  static $dbh;
        public function __construct() {
    
        }
        public function run(){
            $dbhost = 'db.example.com';         // 数据库服务器
            $dbuser = 'example.com';            // 数据库用户名
            $dbpw = 'password';                 // 数据库密码
            $dbname = 'example';                // 数据库名
    
            self::$dbh  = new PDO("mysql:host=$dbhost;port=3306;dbname=$dbname", $dbuser, $dbpw, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::MYSQL_ATTR_COMPRESS => true,
                PDO::ATTR_PERSISTENT => true
                )
            );
    
        }
        protected function getInstance(){
            return self::$dbh;
        }
    
    }
    
    /* the collectable class implements machinery for Pool::collect */
    class Work extends Stackable {
        public function __construct($data) {
            $this->data = $data;
            #print_r($data);
        }
    
        public function run() {
            #$this->worker->logger->log("%s executing in Thread #%lu", __CLASS__, $this->worker->getThreadId() );
    
            try {
                $dbh  = $this->worker->getInstance();
                #print_r($dbh);
                        $id = $this->data['id'];
                $mobile = safenet_decrypt($this->data['mobile']);
                #printf("%d, %s \n", $id, $mobile);
                if(strlen($mobile) > 11){
                    $mobile = substr($mobile, -11);
                }
                if($mobile == 'null'){
                #   $sql = "UPDATE members_digest SET mobile = '".$mobile."' where id = '".$id."'";
                #   printf("%s\n",$sql);
                #   $dbh->query($sql);
                    $mobile = '';
                    $sql = "UPDATE members_digest SET mobile = :mobile where id = :id";
                }else{
                    $sql = "UPDATE members_digest SET mobile = md5(:mobile) where id = :id";
                }
                $sth = $dbh->prepare($sql);
                $sth->bindValue(':mobile', $mobile);
                $sth->bindValue(':id', $id);
                $sth->execute();
                #echo $sth->debugDumpParams();
            }
            catch(PDOException $e) {
                $error = sprintf("%s,%s\n", $mobile, $id );
                file_put_contents("mobile_error.log", $error, FILE_APPEND);
            }
    
            #$dbh = null;
            printf("runtime: %s, %s, %s, %s\n", date('Y-m-d H:i:s'), $this->worker->getThreadId() ,$mobile, $id);
            #printf("runtime: %s, %s\n", date('Y-m-d H:i:s'), $this->number);
        }
    }
    
    $pool = new Pool(100, \ExampleWorker::class, []);
    
    #foreach (range(0, 100) as $number) {
    #   $pool->submit(new Work($number));
    #}
    
    $dbhost = 'db.example.com';                     // 数据库服务器
    $dbuser = 'example.com';                        // 数据库用户名
    $dbpw = 'password';                             // 数据库密码
    $dbname = 'example';
    $dbh    = new PDO("mysql:host=$dbhost;port=3307;dbname=$dbname", $dbuser, $dbpw, array(
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                            PDO::MYSQL_ATTR_COMPRESS => true
                            )
                    );
    #print_r($dbh);
    
    #$sql = "select id, mobile from members where id < :id";
    #$sth = $dbh->prepare($sql);
    #$sth->bindValue(':id',300);
    #$sth->execute();
    #$result = $sth->fetchAll();
    #print_r($result);
    #
    #$sql = "UPDATE members_digest SET mobile = :mobile where id = :id";
    #$sth = $dbh->prepare($sql);
    #$sth->bindValue(':mobile', 'aa');
    #$sth->bindValue(':id','272');
    #echo $sth->execute();
    #echo $sth->queryString;
    #echo $sth->debugDumpParams();
    
    
    $sql = "select id, mobile from members order by id asc"; // limit 1000";
    $row = $dbh->query($sql);
    while($members = $row->fetch(PDO::FETCH_ASSOC))
    {
            #$order =  $account['order'];
            #printf("%s\n",$order);
            //print_r($members);
            $pool->submit(new Work($members));
            #unset($account['order']);
    }
    
    $pool->shutdown();
    
    ?>
```

### 8.3. 多线程中操作数据库总结

总的来说 pthreads 仍然处在发展中，仍有一些不足的地方，我们也可以看到pthreads的git在不断改进这个项目

数据库持久链接很重要，否则每个线程都会开启一次数据库连接，然后关闭，会导致很多链接超时。

```php
    <?php
    $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass, array(
        PDO::ATTR_PERSISTENT => true
    ));
    ?>
```

## 9. Thread And ZeroMQ

应用场景，我使用触发器监控数据库某个表，一旦发现有改变就通知程序处理数据

### 9.1. 数据库端

首先安装ZeroMQ 与 MySQL UDF https://github.com/netkiller/mysql-zmq-plugin, 然后创建触发器。

```sql
    CREATE DEFINER=`dba`@`192.168.%` PROCEDURE `Table_Example`(IN `TICKET` INT, IN `LOGIN` INT, IN `CMD` INT, IN `VOLUME` INT)
        LANGUAGE SQL
        NOT DETERMINISTIC
        READS SQL DATA
        SQL SECURITY DEFINER
        COMMENT '交易监控'
    BEGIN
        DECLARE Example CHAR(1) DEFAULT 'N';
    
        IF CMD IN ('0','1') THEN
            IF VOLUME >=10 AND VOLUME <=90 THEN
                select coding into Example from example.members where username = LOGIN and coding = 'Y';
                IF Example = 'Y' THEN
                    select zmq_client('tcp://192.168.2.15:5555', CONCAT(TICKET, ',', LOGIN, ',', VOLUME));
                END IF;
            END IF;
        END IF;
    END
    
    CREATE DEFINER=`dba`@`192.168.6.20` TRIGGER `Table_AFTER_INSERT` AFTER INSERT ON `MT4_TRADES` FOR EACH ROW BEGIN
        call Table_Example(NEW.TICKET,NEW.LOGIN,NEW.CMD,NEW.VOLUME);
    END
```

### 9.2. 数据处理端

```php
    <?php
    class ExampleWorker extends Worker {
    
        #public function __construct(Logging $logger) {
        #   $this->logger = $logger;
        #}
    
        #protected $logger;
        protected  static $dbh;
        public function __construct() {
    
        }
        public function run(){
            $dbhost = '192.168.2.1';            // 数据库服务器
            $dbport = 3306;
            $dbuser = 'www';                    // 数据库用户名
            $dbpass = 'password';               // 数据库密码
            $dbname = 'example';                // 数据库名
    
            self::$dbh  = new PDO("mysql:host=$dbhost;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(
                /* PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'', */
                PDO::MYSQL_ATTR_COMPRESS => true,
                PDO::ATTR_PERSISTENT => true
                )
            );
    
        }
        protected function getInstance(){
            return self::$dbh;
        }
    
    }
    
    /* the collectable class implements machinery for Pool::collect */
    class Fee extends Stackable {
        public function __construct($msg) {
            $trades = explode(",", $msg);
            $this->data = $trades;
            print_r($trades);
        }
    
        public function run() {
            #$this->worker->logger->log("%s executing in Thread #%lu", __CLASS__, $this->worker->getThreadId() );
    
            try {
                $dbh  = $this->worker->getInstance();
    
                $insert = "INSERT INTO coding_fee(ticket, login, volume, `status`) VALUES(:ticket, :login, :volume,'N')";
                $sth = $dbh->prepare($insert);
                $sth->bindValue(':ticket', $this->data[0]);
                $sth->bindValue(':login', $this->data[1]);
                $sth->bindValue(':volume', $this->data[2]);
                $sth->execute();
                //$sth = null;
                //$dbh = null;
    
                /* 业务实现在此处 */
    
                $update = "UPDATE coding_fee SET `status` = 'Y' WHERE ticket = :ticket and `status` = 'N'";
                $sth = $dbh->prepare($update);
                $sth->bindValue(':ticket', $this->data[0]);
                $sth->execute();
                //echo $sth->queryString;
            }
            catch(PDOException $e) {
                $error = sprintf("%s,%s\n", $mobile, $id );
                file_put_contents("mobile_error.log", $error, FILE_APPEND);
            }
    
            #$dbh = null;
            //printf("runtime: %s, %s, %s, %s\n", date('Y-m-d H:i:s'), $this->worker->getThreadId() ,$mobile, $id);
            #printf("runtime: %s, %s\n", date('Y-m-d H:i:s'), $this->number);
        }
    }
    
    class Example {
        /* config */
        const LISTEN = "tcp://192.168.2.15:5555";
        const MAXCONN = 100;
        const pidfile = __CLASS__;
        const uid   = 80;
        const gid   = 80;
    
        protected $pool = NULL;
        protected $zmq = NULL;
        public function __construct() {
            $this->pidfile = '/var/run/'.self::pidfile.'.pid';
        }
        private function daemon(){
            if (file_exists($this->pidfile)) {
                echo "The file $this->pidfile exists.\n";
                exit();
            }
    
            $pid = pcntl_fork();
            if ($pid == -1) {
                 die('could not fork');
            } else if ($pid) {
                 // we are the parent
                 //pcntl_wait($status); //Protect against Zombie children
                exit($pid);
            } else {
                // we are the child
                file_put_contents($this->pidfile, getmypid());
                posix_setuid(self::uid);
                posix_setgid(self::gid);
                return(getmypid());
            }
        }
        private function start(){
            $pid = $this->daemon();
            $this->pool = new Pool(self::MAXCONN, \ExampleWorker::class, []);
            $this->zmq = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REP);
            $this->zmq->bind(self::LISTEN);
    
            /* Loop receiving and echoing back */
            while ($message = $this->zmq->recv()) {
                if($message){
                        $this->pool->submit(new Fee($message));
                        $this->zmq->send('TRUE');
                }else{
                        $this->zmq->send('FALSE');
                }
            }
            $pool->shutdown();
        }
        private function stop(){
    
            if (file_exists($this->pidfile)) {
                $pid = file_get_contents($this->pidfile);
                posix_kill($pid, 9);
                unlink($this->pidfile);
            }
        }
        private function help($proc){
            printf("%s start | stop | help \n", $proc);
        }
        public function main($argv){
            if(count($argv) < 2){
                printf("please input help parameter\n");
                exit();
            }
            if($argv[1] === 'stop'){
                $this->stop();
            }else if($argv[1] === 'start'){
                $this->start();
            }else{
                $this->help($argv[0]);
            }
        }
    }
    
    $example = new Example();
    $example->main($argv);
```

使用方法

    # php example.php start
    # php example.php stop
    # php example.php help
                

此程序涉及守候进程实现$this->daemon()运行后转到后台运行，进程ID保存，进程的互斥（不允许同时启动两个进程），线程池连接数以及线程任务等等


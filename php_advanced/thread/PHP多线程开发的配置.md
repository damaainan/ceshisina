##  PHP多线程开发的配置

<font face=微软雅黑>

PHP 5.3 以版本，使用 `pthreads` PHP扩展，可以使PHP真正地支持多线程。多线程在处理重复性的循环任务，能够大大缩短程序执行时间。


多数网站的性能瓶颈不在PHP服务器上，因为它可以简单地通过横向增加服务器或CPU核数来轻松应对（对于各种云主机，增加VPS或CPU核数就更方便了，直接以备份镜像增加VPS，连操作系统、环境都不用安装配置），而是在于MySQL数据库。如果用 MySQL 数据库，一条联合查询的SQL，也许就可以处理完业务逻辑，但是，遇到大量并发请求，就歇菜了。如果用 NoSQL 数据库，也许需要十次查询，才能处理完同样地业务逻辑，但每次查询都比 MySQL 要快，十次循环NoSQL查询也许比一次MySQL联合查询更快，应对几万次/秒的查询完全没问题。如果加上PHP多线程，通过十个线程同时查询NoSQL，返回结果汇总输出，速度就要更快了。我们实际的APP产品中，调用一个通过用户喜好实时推荐商品的PHP接口，PHP需要对BigSea NoSQL数据库发起500~1000次查询，来实时算出用户的个性喜好商品数据，PHP多线程的作用非常明显。

PHP扩展下载：[http://pecl.php.net/package/pthreads][1]

PHP手册文档：[http://php.net/manual/zh/book.pthreads.php][2]

**特别说明一点，在安装PHP的时候，一定要指定  <font color=red>  --enable-maintainer-zts  </font>参数。这是必选项，安装代码如下：(以下安装过程在 `centos 6.5` 环境中，此安装过程仅供参考，你在实例应用中，PHP的安装目录不一定要    `/www` 中)**

```shell
    # 安装PHP
    tar zxvf php-7.0.0.tar.gz
    cd ../php-7.0.0
    ./configure --prefix=/www/php --enable-fpm --with-fpm-user=www --with-fpm-group=www --with-openssl --with-libxml-dir --with-zlib --enable-mbstring --with-mysql=/www/mysql --with-mysqli=mysqlnd --enable-mysqlnd --with-pdo-mysql=/www/mysql --with-gd --with-jpeg-dir --with-png-dir --with-zlib-dir --with-freetype-dir --enable-sockets --with-curl --enable-maintainer-zts
    make && make install
    
    # 安装扩展
    wget http://pecl.php.net/get/pthreads-3.1.6.tgz
    tar zxvf pthreads-3.1.6.tgz
    cd pthreads-3.1.6
    phpize
    ./configure --with-php-config=/www/php/bin/php-config
    make && make install
    
    # 修改php.ini
    vim /www/php/lib/php.ini
    
    # 在php.ini中添加
    extension = "pthreads.so"
    
    # 重启
    kill -USR2 `cat /www/php/var/run/php-fpm.pid`
    
    # 运行 /www/php/bin/php -m | grep pthreads 发现 pthreads 已经安装成功
```

这里用一个最简单的代码来说明一下多线程的实用方法

```php
<?php
//这里用一个函数，表示操作日志，每操作一次花1秒的时间
function doThings($i) {
    //  Write log file
    sleep(1);
}

$s = microtime(true);
for ($i = 1; $i <= 10; $i++) {
    doThings($i);
}
$e = microtime(true);
echo "For循环：" . ($e - $s) . "\n";

#############################################
class MyThread extends Thread {
    private $i = null;

    public function __construct($i) {
        $this->i = $i;
    }

    public function run() {
        doThings($this->i);
    }
}

$s = microtime(true);
$work = array();
for ($i = 1; $i <= 10; $i++) {
    $work[$i] = new MyThread($i);
    $work[$i]->start();
}
$e = microtime(true);
echo "多线程：" . ($e - $s) . "\n";
```

运行此文件之后，发现多线程的效率会远远高于 for 循环, 效果如图

![2.png][3]

</font>

[1]: http://pecl.php.net/package/pthreads
[2]: http://php.net/manual/zh/book.pthreads.php
[3]: ../img/1482414682599620.png
## php开启opcache加速

来源：[http://blog.51cto.com/laodou/2144704](http://blog.51cto.com/laodou/2144704)

时间 2018-07-16 14:52:50
  
先看一下LNMP架构
 
  
![][0]
 
我们知道很多php项目都会用到上述架构，静态页面走nginx，动态请求传给后端php，php解析后传给客户端
 
那php是如何解析代码的呢？
 
下面是php的原理图
 
  
![][1]
 
当Nginx将CGI请求发送给这个socket（这个socket可以是文件也可以是ip socket）的时候，通过FastCGI接口，wrapper接收到请求，然后派生出一个新的线程，这个线程调用解释器或者外部程序处理脚本并读取返回数据；接着，wrapper再将返回的数据通过FastCGI接口，沿着固定的socket传递给Nginx；最后，Nginx将返回的数据发送给客户端。这就是Nginx+FastCGI的整个运作过程。
 
spawn-fcgi与PHP-FPM都是FastCGI进程管理器。
 
我们知道了php代码解析过程，但是如何提高动态请求的效率
 
我们从以下入手：
 
1）代码层面，可以考虑增加页面的缓存功能（生成静态页），这样就不用再去解析
 
2）php软件级别，增加缓存操作码，让代码解析后，php进行缓存到内存中，下次请求时不再解析。
 
3）对于查询数据库的，可以考虑使用redis、memcache等内存型数据库来存储数据。
 
这里只讲解操作码缓存技术
 
以下是目前流行的软件解决方案（增加缓存操作码）
 
XCache、 Accelerator、以及php自带Zend Opcache
 
目前使用XCache及Zend Opcache比较多
 
这里只讲Zend Opcache，同时只负责php5.6版本
 
 
 
```
cd /usr/local/src/
wget http://cn2.php.net/get/php-5.6.34.tar.gz
wget http://ftp.gnu.org/pub/gnu/libiconv/libiconv-1.14.tar.gz
#先安装依赖
yum install -y pcre pcre-devel zlib zlib-devel gcc gcc-c++ pcre pcre-devel zlib zlib-devel openssl openssl-devel freetype-devel libpng-devel gd-devel libcurl-devel libxslt-devel libxml2-devel libjpeg-devel libjpeg-turbo-devel libiconv-devel libmcrypt-devel mhash mcrypt -y
tar xf libiconv-1.14.tar.gz
./configure --prefix=/usr/local/libiconv && make && make install
useradd apache -u 1012 -g 1012 -s /sbin/nologin -M
#安装php
tar xf php-5.6.34.tar.gz 
cd php-5.6.34
./configure --prefix=/app/php5.6.34 --with-mysqli=mysqlnd --with-mysql=mysqlnd --with-iconv-dir=/usr/local/libiconv --with-freetype-dir --with-jpeg-dir --with-png-dir --with-zlib --with-libxml-dir=/usr --enable-xml --disable-rpath --enable-bcmath --enable-shmop --enable-sysvsem --enable-inline-optimization --with-curl --enable-mbregex --enable-fpm --enable-mbstring --with-mcrypt --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-xmlrpc --enable-zip --enable-soap --enable-short-tags --enable-static --with-xsl --with-fpm-user=www --with-fpm-group=www --enable-opcache  --enable-ftp
touch ext/phar/phar.phar
make && make install
cp php.ini-production /app/php/lib/php.ini
cd /app/php/etc/
cp php-fpm.conf.default php-fpm.conf
sed -i "s#;date.timezone =#date.timezone = Asia/Shanghai#g" /app/php/lib/php.ini
```
 
其中php.ini的opcache配置如下
 
```
[opcache]

opcache.enable=1

opcache.memory_consumption=512

opcache.interned_strings_buffer=8

opcache.max_accelerated_files=4000

opcache.validate_timestamps=1

opcache.revalidate_freq=300

opcache.fast_shutdown=1

zend_extension="opcache.so"
```
 
  
检查opcache是否成功，info.php文件内容如下

```
<?php
 
phpinfo();
 
?>
```
访问后，如下图表示成功
 
  
![][2]
 
如何查看缓存是否生效，做如下实验
 
 
 
[root@tst web]# cat b.php
```
<?php
 
$a = 'hello test';
 
echo $a;
 
?>
```
我们访问一下，发现第一次没有cache hits同时cache misses为1，后面的短时间内访问，我们发现cache hits一直增加，而cache misses一直为1表示缓存成功
 
我们通过设置opcache.revalidate_freq参数，可以实现多长时间缓存失效。
 
这里我们讲解一下opcache配置参数
 
opcache.enable=1 #是否开启opcache，（1表示开启，0表示关闭）
 
opcache.enable_cli=0 #仅针对 CLI 版本的 PHP 启用操作码缓存，一般在生产环境中不开启，测试环境中用的比较多
 
opcache.memory_consumption=512 #共享内存分配多少M，它主要放在precompiled php code 中，默认是64m
 
opcache.interned_strings_buffer=8 #interned_strings内存的数量，一般设置小于8m就可以了，没有必要太大。PHP 5.3.0 之前的版本会忽略此配置指令。
 
opcache.max_accelerated_files=4000 #Opcache 的hash表中存储的key的最大数量是多少，如果命中率不高的话，需要提高该值。最大值在 PHP 5.5.6 之前是 100000，PHP 5.5.6 及之后是 1000000
 
opcache.max_wasted_percentage=5 #浪费内存的上限，以百分比计。 如果达到此上限，那么 Opcache 将产生重新启动续发事件
 
opcache.use_cwd=1 #表示启用，Opcache 将在哈希表的脚本键之后附加改脚本的工作目录， 以避免同名脚本冲突的问题。 禁用此选项可以提高性能，但是可能会导致应用崩溃。
 
opcache.validate_timestamps=1 #启用，那么 OPcache 会每隔 opcache.revalidate_freq 设定的秒数 检查脚本是否更新，必须开启，否则需要手动更新opcache的缓存
 
opcache.revalidate_freq 300 #opcache中的操作码缓存更新频率，单位为s
 
opcache.fast_shutdown=1 #启用，使用快速停止续发事件。 所谓快速停止续发事件是指依赖 Zend 引擎的内存管理模块 一次释放全部请求变量的内存，而不是依次释放每一个已分配的内存块。从 PHP 7.2.0 开始，此配置指令被移除。 快速停止的续发事件的处理已经集成到 PHP 中， 只要有可能，PHP 会自动处理这些续发事件
 
  
参考文档：
 
  [http://us1.php.net/manual/zh/opcache.configuration.php][3] 
 
 


[3]: http://us1.php.net/manual/zh/opcache.configuration.php
[0]: ../img/RbyY7vU.png 
[1]: ../img/3MzAjai.png 
[2]: ../img/ZzaQriU.png 
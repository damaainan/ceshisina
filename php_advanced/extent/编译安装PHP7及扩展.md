## 编译安装PHP7及扩展

来源：[https://segmentfault.com/a/1190000016080151](https://segmentfault.com/a/1190000016080151)


## 一、编译安装PHP
### 1. 下载源码包并解压
#### **`源码包地址：[http://php.net/downloads.php][0]`** 

#### **`下载源码包`** 

当前PHP最新本门是7.2.9，下载 php-7.2.9 源码包

```
wget http://be2.php.net/get/php-7.2.9.tar.gz/from/this/mirror -O php-7.2.9.tar.gz
```
#### **`解压源码包`** 

解压缩

```
tar zxf php-7.2.9.tar.gz
```

进入解压缩后的目录，查看解压的文件

```
[root/usr/local/src/php-7.2.9]# ll
总用量 4.1M
-rw-rw-r--  1 root root  84K 8月  14 14:26 acinclude.m4
-rw-r--r--  1 root root 309K 8月  14 14:26 aclocal.m4
drwxrwxr-x  2 root root   78 8月  14 14:26 appveyor/
-rw-rw-r--  1 root root 1.4K 8月  14 14:26 .appveyor.yml
drwxrwxr-x  2 root root 4.0K 8月  14 14:26 build/
-rwxrwxr-x  1 root root  772 8月  14 14:26 buildconf*
-rw-rw-r--  1 root root  334 8月  14 14:26 buildconf.bat
-rw-rw-r--  1 root root  12K 8月  14 14:26 CODING_STANDARDS
-rw-rw-r--  1 root root  42K 8月  14 14:26 config.guess
-rw-rw-r--  1 root root  36K 8月  14 14:26 config.sub
-rwxr-xr-x  1 root root 2.6M 8月  14 14:26 configure*
-rw-rw-r--  1 root root  46K 8月  14 14:26 configure.ac
-rw-rw-r--  1 root root 3.4K 8月  14 14:26 CONTRIBUTING.md
-rw-rw-r--  1 root root   91 8月  14 14:26 CREDITS
-rw-rw-r--  1 root root  867 8月  14 14:26 .editorconfig
drwxrwxr-x 76 root root 4.0K 8月  14 14:26 ext/
-rw-rw-r--  1 root root  21K 8月  14 14:26 EXTENSIONS
-rw-rw-r--  1 root root  137 8月  14 14:26 footer
-rw-rw-r--  1 root root  13K 8月  14 14:26 .gdbinit
-rw-r--r--  1 root root 1.8K 8月  14 14:26 generated_lists
-rwxrwxr-x  1 root root  581 8月  14 14:26 genfiles*
-rw-rw-r--  1 root root 8.7K 8月  14 14:26 .gitattributes
-rw-rw-r--  1 root root 4.3K 8月  14 14:26 .gitignore
-rw-rw-r--  1 root root 1.2K 8月  14 14:26 header
-rw-rw-r--  1 root root  86K 8月  14 14:26 INSTALL
-rw-r--r--  1 root root    0 8月  14 14:26 install-sh
-rw-rw-r--  1 root root 3.2K 8月  14 14:26 LICENSE
-rw-rw-r--  1 root root 196K 8月  14 14:26 ltmain.sh
drwxrwxr-x  3 root root 4.0K 8月  14 14:26 main/
-rwxrwxr-x  1 root root 4.1K 8月  14 14:26 makedist*
-rw-rw-r--  1 root root 1.1K 8月  14 14:26 Makefile.frag
-rw-rw-r--  1 root root 2.5K 8月  14 14:26 Makefile.gcov
-rw-rw-r--  1 root root 7.0K 8月  14 14:26 Makefile.global
-rw-r--r--  1 root root    0 8月  14 14:26 missing
-rw-r--r--  1 root root    0 8月  14 14:26 mkinstalldirs
-rw-rw-r--  1 root root  90K 8月  14 14:26 NEWS
drwxrwxr-x  2 root root   96 8月  14 14:26 pear/
-rw-rw-r--  1 root root 1.5K 8月  14 14:26 php7.spec.in
-rw-rw-r--  1 root root 2.5K 8月  14 14:26 php.gif
-rw-rw-r--  1 root root  69K 8月  14 14:26 php.ini-development
-rw-rw-r--  1 root root  69K 8月  14 14:26 php.ini-production
-rw-rw-r--  1 root root 6.9K 8月  14 14:26 README.EXT_SKEL
-rw-rw-r--  1 root root 5.0K 8月  14 14:26 README.GIT-RULES
-rw-rw-r--  1 root root 5.3K 8月  14 14:26 README.input_filter
-rw-rw-r--  1 root root 3.4K 8月  14 14:26 README.MAILINGLIST_RULES
-rw-rw-r--  1 root root 1.6K 8月  14 14:26 README.md
-rw-rw-r--  1 root root 5.2K 8月  14 14:26 README.NEW-OUTPUT-API
-rw-rw-r--  1 root root 7.4K 8月  14 14:26 README.PARAMETER_PARSING_API
-rw-rw-r--  1 root root  20K 8月  14 14:26 README.REDIST.BINS
-rw-rw-r--  1 root root  15K 8月  14 14:26 README.RELEASE_PROCESS
-rw-rw-r--  1 root root 5.0K 8月  14 14:26 README.SELF-CONTAINED-EXTENSIONS
-rw-rw-r--  1 root root  15K 8月  14 14:26 README.STREAMS
-rw-rw-r--  1 root root 8.0K 8月  14 14:26 README.SUBMITTING_PATCH
-rw-rw-r--  1 root root 6.6K 8月  14 14:26 README.TESTING
-rw-rw-r--  1 root root 4.9K 8月  14 14:26 README.TESTING2
-rw-rw-r--  1 root root 4.2K 8月  14 14:26 README.UNIX-BUILD-SYSTEM
-rw-rw-r--  1 root root  115 8月  14 14:26 README.WIN32-BUILD-SYSTEM
-rwxrwxr-x  1 root root  84K 8月  14 14:26 run-tests.php*
drwxrwxr-x 10 root root  110 8月  14 14:26 sapi/
drwxrwxr-x  4 root root   99 8月  14 14:26 scripts/
-rwxrwxr-x  1 root root 2.1K 8月  14 14:26 server-tests-config.php*
-rwxrwxr-x  1 root root  52K 8月  14 14:26 server-tests.php*
-rwxrwxr-x  1 root root  108 8月  14 14:26 snapshot*
-rw-rw-r--  1 root root   10 8月  14 14:26 stamp-h.in
drwxrwxr-x 10 root root  133 8月  14 14:26 tests/
drwxrwxr-x  3 root root   33 8月  14 14:26 travis/
-rw-rw-r--  1 root root 1.9K 8月  14 14:26 .travis.yml
drwxrwxr-x  3 root root 4.0K 8月  14 14:26 TSRM/
-rw-rw-r--  1 root root  15K 8月  14 14:26 UPGRADING
-rw-rw-r--  1 root root 3.3K 8月  14 14:26 UPGRADING.INTERNALS
-rwxrwxr-x  1 root root  159 8月  14 14:26 vcsclean*
drwxrwxr-x  3 root root 4.0K 8月  14 14:26 win32/
drwxrwxr-x  3 root root 8.0K 8月  14 14:26 Zend/
```
### 2. 编译安装
#### **`首先安装PHP编译安装所必须的软件`** 

```
[root/usr/local/src/php-7.2.9]# yum -y install gcc gcc++ libxml2-devel libicu-devel
```
#### **``configure`指定安装目录，启用fpm`** 

```
[root/usr/local/src/php-7.2.9]# ./configure --prefix=/usr/local/php7 --enable-fpm
```
#### **`编译安装`** 

```
[root/usr/local/src/php-7.2.9]# make && make install
...省略...
Installing shared extensions:     /usr/local/php7/lib/php/extensions/no-debug-non-zts-20170718/
Installing PHP CLI binary:        /usr/local/php7/bin/
Installing PHP CLI man page:      /usr/local/php7/php/man/man1/
Installing PHP FPM binary:        /usr/local/php7/sbin/
Installing PHP FPM defconfig:     /usr/local/php7/etc/
Installing PHP FPM man page:      /usr/local/php7/php/man/man8/
Installing PHP FPM status page:   /usr/local/php7/php/php/fpm/
Installing phpdbg binary:         /usr/local/php7/bin/
Installing phpdbg man page:       /usr/local/php7/php/man/man1/
Installing PHP CGI binary:        /usr/local/php7/bin/
Installing PHP CGI man page:      /usr/local/php7/php/man/man1/
Installing build environment:     /usr/local/php7/lib/php/build/
Installing header files:          /usr/local/php7/include/php/
Installing helper programs:       /usr/local/php7/bin/
  program: phpize
  program: php-config
Installing man pages:             /usr/local/php7/php/man/man1/
  page: phpize.1
  page: php-config.1
Installing PEAR environment:      /usr/local/php7/lib/php/
[PEAR] Archive_Tar    - installed: 1.4.3
[PEAR] Console_Getopt - installed: 1.4.1
[PEAR] Structures_Graph- installed: 1.1.1
[PEAR] XML_Util       - installed: 1.4.2
[PEAR] PEAR           - installed: 1.10.5
Warning! a PEAR user config file already exists from a previous PEAR installation at '/root/.pearrc'. You may probably want to remove it.
Wrote PEAR system config file at: /usr/local/php7/etc/pear.conf
You may want to add: /usr/local/php7/lib/php to your php.ini include_path
/usr/local/src/php-7.2.9/build/shtool install -c ext/phar/phar.phar /usr/local/php7/bin
ln -s -f phar.phar /usr/local/php7/bin/phar
Installing PDO headers:           /usr/local/php7/include/php/ext/pdo/
```
### 3. 为php命令建立软链接，加入到环境变量中

```
[root/usr/local/src/php-7.2.9]# ln -s /usr/local/php7/bin/php /usr/local/bin/php
```
### 4. 创建配置文件，并将其复制到正确的位置
#### **`查看PHP基本信息`** 

```
[root/usr/local/src/php-7.2.9]# php -ini
phpinfo()
PHP Version => 7.2.9

System => Linux 10.0.2.15 3.10.0-229.el7.x86_64 #1 SMP Fri Mar 6 11:36:42 UTC 2015 x86_64
Build Date => Aug 17 2018 09:09:29
Configure Command =>  './configure'  '--prefix=/usr/local/php7' '--enable-fpm'
Server API => Command Line Interface
Virtual Directory Support => disabled
Configuration File (php.ini) Path => /usr/local/php7/lib
Loaded Configuration File => (none)
Scan this dir for additional .ini files => (none)
Additional .ini files parsed => (none)
PHP API => 20170718
PHP Extension => 20170718
Zend Extension => 320170718
Zend Extension Build => API320170718,NTS
PHP Extension Build => API20170718,NTS
......
```
#### **`将php.ini复制到`Configuration File (php.ini) Path``** 

```
[root/usr/local/src/php-7.2.9]# cp php.ini-development /usr/local/php7/lib/php.ini
```
### 5. 配置php-fpm
#### **`为php-fpm命令建立软链接，加入到环境变量中`** 

```
[root/usr/local/src/php-7.2.9]$ ln -s /usr/local/php7/sbin/php-fpm /usr/local/sbin/php-fpm
```
#### **`复制php配置文件目录下的`php-fpm.conf.default`，并重命名为`php-fpm.conf``** 

```
[root/usr/local/src/php-7.2.9]# cp /usr/local/php7/etc/php-fpm.conf.default /usr/local/php7/etc/php-fpm.conf
```
#### **`复制php配置文件目录下的`php-fpm.d/www.conf.default`，并重命名为`php-fpm.d/www.conf``** 

```
[root/usr/local/src/php-7.2.9]# cp /usr/local/php7/etc/php-fpm.d/www.conf.default /usr/local/php7/etc/php-fpm.d/www.conf
```
#### **`编辑`php-fpm.d/www.conf`，设置 php-fpm 模块使用 www-data 用户和 www-data 用户组的身份运行。`** 

```
vim /usr/local/php7/etc/php-fpm.d/www.conf

user = www-data
group = www-data
```
#### **`需要着重提醒的是，如果文件不存在，则阻止 Nginx 将请求发送到后端的 PHP-FPM 模块， 以避免遭受恶意脚本注入的攻击`** 

编辑 php.ini，文件中的配置项 cgi.fix_pathinfo 设置为 0 。

```
[root/usr/local/src/php-7.2.9]# vim /usr/local/php7/lib/php.ini

cgi.fix_pathinfo=0
```
#### **`启动php-fpm`** 

```
[root/usr/local/src/php-7.2.9]# php-fpm

[root/usr/local/src/php-7.2.9]# ss -tlnp | grep php-fpm
LISTEN  0  128  127.0.0.1:9000  *:*  users:(("php-fpm",pid=4689,fd=5),("php-fpm",pid=4688,fd=5),("php-fpm",pid=4687,fd=7))
```
### 6. 配置 Nginx 使其支持 PHP 应用

```
[root/etc/nginx]# vim conf.d/default.conf

server {
    listen 80;

    root /vagrant;

    location / {
        index  index.php;
    }

    location ~* \.php$ {
        fastcgi_index   index.php;
        fastcgi_pass    127.0.0.1:9000;
        include       fastcgi_params;
        fastcgi_param   SCRIPT_FILENAME    $document_root$fastcgi_script_name;
        fastcgi_param   SCRIPT_NAME        $fastcgi_script_name;
    }
}
```
#### **`创建`/vagrant/index.php`，并填入`<?="Hello world!";``** 

```
[root/etc/nginx]# echo '<?="Hello world!";' > /vagrant/index.php

[root/etc/nginx]# cat /vagrant/index.php
<?="Hello world!";
```
#### **`启动nginx`** 

```
[root/etc/nginx]# nginx

[root/etc/nginx]# ss -tlnp | grep nginx
LISTEN  0  128  *:80  *:*  users:(("nginx",pid=4725,fd=6),("nginx",pid=4724,fd=6))
```
### 7. 访问`curl localhost`，输出`Hello world!`，说明PHP + Nginx安装成功

```
[root/etc/nginx]# curl localhost
Hello world!
```
## 二、编译安装PHP扩展
### 1. php扩展安装流程
#### **`从 pecl.php.net 查找需要的扩展`** 

#### **`选择扩展的版本（注意查看扩展版本与PHP版本的兼容性）`** 

#### **`解压缩下载的文件`** 

#### **`判断文件的安装类型`** 

```
> 直装：（解压出来就是 .so 文件），直接复制文件到扩展目录，在php.ini中开启相应的扩展即可
> 编译安装：需要先进行编译，再复制文件到扩展目录，在php.ini中开启相应的扩展

```
### 2. 编译安装步骤
#### **`在解压缩的扩展目录下执行phpize`** 

若 phpize 没有加入到环境变量，则需要使用绝对路径。例如`/usr/local/php7/bin/phpize`。
phpize是用来扩展php扩展模块的。
通过phpize可以建立php的外挂模块。
 **`configure 配置编译参数`** 

主要是配置php配置文件参数，例如：

```
./configure --with-php-config=/usr/local/php7/bin/php-config

```

若配置编译参数执行过程中出现错误，则需要安装 autoconf。
autoconf可以自动地配置软件源代码。

contos/redhat：`yum install autoconf`
ubuntu：`apt-get install autoconf`#### **`编译安装`** 

```
make && make install

```
#### **`复制`.so`扩展文件到PHP扩展目录（若已经自动复制，则忽略）`** 

#### **`在php.ini中开启扩展，配置相应的扩展参数`** 

### 3. 安装redis扩展实例
#### **``php -m`查看已安装的php扩展`** 

```
[root/usr/local/src/php-7.2.9]# php -m
[PHP Modules]
Core
ctype
date
dom
fileinfo
filter
hash
iconv
json
libxml
pcre
PDO
pdo_sqlite
Phar
posix
Reflection
session
SimpleXML
SPL
sqlite3
standard
tokenizer
xml
xmlreader
xmlwriter

[Zend Modules]
```
#### **`从 pecl.php.net 下载 redis-4.1.1 扩展，并解压缩`** 

```
[root/usr/local/src]# wget https://pecl.php.net/get/redis-4.1.1.tgz
--2018-08-17 10:00:41--  https://pecl.php.net/get/redis-4.1.1.tgz
正在解析主机 pecl.php.net (pecl.php.net)... 104.236.228.160
正在连接 pecl.php.net (pecl.php.net)|104.236.228.160|:443... 已连接。
已发出 HTTP 请求，正在等待回应... 200 OK
长度：220894 (216K) [application/octet-stream]
正在保存至: “redis-4.1.1.tgz”

100%[=========================================================================================================================================================================>] 220,894      192KB/s 用时 1.1s

2018-08-17 10:00:44 (192 KB/s) - 已保存 “redis-4.1.1.tgz” [220894/220894])

[root/usr/local/src]# tar zxf redis-4.1.1.tgz

[root/usr/local/src]# cd redis-4.1.1

```
#### **`在解压缩的扩展目录下执行phpize`** 

```
[root/usr/local/src/redis-4.1.1]# /usr/local/php7/bin/phpize
Configuring for:
PHP Api Version:         20170718
Zend Module Api No:      20170718
Zend Extension Api No:   320170718
```
#### **`configure 配置编译参数`** 

```
[root/usr/local/src/redis-4.1.1]# ./configure --with-php-config=/usr/local/php7/bin/php-config
```
#### **`编译安装`** 

```
[root/usr/local/src/redis-4.1.1]# make && make install
```
#### **`查看编译好的扩展文件`redis.so`，已经自动复制到php扩展目录下`** 

```
[root/usr/local/src/redis-4.1.1]# ll /usr/local/php7/lib/php/extensions/no-debug-non-zts-20170718/
总用量 7.1M
-rwxr-xr-x 1 root root 3.5M 8月  17 09:15 opcache.a*
-rwxr-xr-x 1 root root 1.9M 8月  17 09:15 opcache.so*
-rwxr-xr-x 1 root root 1.8M 8月  17 10:06 redis.so*
```
#### **`编辑`php.ini`，添加`extension=redis``** 

```
[root/usr/local/src/redis-4.1.1]# vim /usr/local/php7/lib/php.ini

extension=redis
```
#### **`此时再使用`php -m`查看已安装的php扩展，redis已经在扩展列表中`** 

```
[root/usr/local/src/redis-4.1.1]# php -m
[PHP Modules]
Core
ctype
date
dom
fileinfo
filter
hash
iconv
json
libxml
pcre
PDO
pdo_sqlite
Phar
posix
redis
Reflection
session
SimpleXML
SPL
sqlite3
standard
tokenizer
xml
xmlreader
xmlwriter

[Zend Modules]
```
### 3. 安装xdebug扩展实例
#### **`从 pecl.php.net 下载 xdebug-2.6.1 扩展，并解压缩`** 

```
[root/usr/local/src]# wget https://pecl.php.net/get/xdebug-2.6.1.tgz

[root/usr/local/src]# tar zxf xdebug-2.6.1.tgz

[root/usr/local/src]# cd xdebug-2.6.1
```
#### **`在解压缩的扩展目录下执行phpize`** 

```
[root/usr/local/src/xdebug-2.6.1]# /usr/local/php7/bin/phpize
Configuring for:
PHP Api Version:         20170718
Zend Module Api No:      20170718
Zend Extension Api No:   320170718
```
#### **`configure 配置编译参数`** 

```
[root/usr/local/src/xdebug-2.6.1]# ./configure --with-php-config=/usr/local/php7/bin/php-config
```
#### **`编译安装`** 

```
[root/usr/local/src/xdebug-2.6.1]# make && make install
```
#### **`查看编译好的扩展文件`redis.so`，已经自动复制到php扩展目录下`** 

```
[root/usr/local/src/xdebug-2.6.1]# ll /usr/local/php7/lib/php/extensions/no-debug-non-zts-20170718/
总用量 8.4M
-rwxr-xr-x 1 root root 3.5M 8月  17 09:15 opcache.a*
-rwxr-xr-x 1 root root 1.9M 8月  17 09:15 opcache.so*
-rwxr-xr-x 1 root root 1.8M 8月  17 10:06 redis.so*
-rwxr-xr-x 1 root root 1.3M 8月  17 11:16 xdebug.so*
```
#### **`编辑`php.ini`，添加`zend_extension=xdebug``** 

```
[root/usr/local/src/xdebug-2.6.1]# vim /usr/local/php7/lib/php.ini

zend_extension=xdebug
```
#### **`此时再使用`php -m`查看已安装的php扩展，xdebug已经在扩展列表中`** 

```
[root/usr/local/src/xdebug-2.6.1]# php -m
[PHP Modules]
Core
ctype
date
dom
fileinfo
filter
hash
iconv
json
libxml
pcre
PDO
pdo_sqlite
Phar
posix
redis
Reflection
session
SimpleXML
SPL
sqlite3
standard
tokenizer
xdebug
xml
xmlreader
xmlwriter

[Zend Modules]
Xdebug
```

[0]: http://php.net/downloads.php
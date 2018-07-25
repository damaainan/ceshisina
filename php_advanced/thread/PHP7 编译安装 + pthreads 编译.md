## PHP7 编译安装 + pthreads 编译

来源：[http://www.hfxblog.com/2018/06/12/PHP7-编译安装-pthreads-编译/](http://www.hfxblog.com/2018/06/12/PHP7-编译安装-pthreads-编译/)

时间 2018-06-13 14:34:58
```
sudo apt install build-essential
```


## 获取源码  

去 PHP 官网    [http://php.net/downloads.php][0]
下载源码（可以选择离自己近的镜像），这里选择的是`*.xz`版本，体积更小

```
wget http://hk2.php.net/get/php-7.2.6.tar.xz/from/this/mirror -O "php-7.2.6.tar.xz"
```

解压：

```
xz -d php-7.2.6.tar.xz
tar -xvf php-7.2.6.tar
```

这个时候可以得到源码的目录`php-7.2.6/`## 额外的依赖包  

```
sudo apt install libxml2-dev \
libcurl4-openssl-dev \
pkg-config \
libssl-dev \
libtidy-dev \
libxslt1-dev \
libreadline-dev
```

可能不同的机器、启用不同的 PHP 模块需要的依赖是不一样的，可以按照抛出的错误进行安装（也可以 Google 错误再安装），缺啥装啥！


## 构建  

准备编译 PHP，这里需要注意的是，CLI 需要多线程功能`pthreads`，而`pthreads`是不支持 CGI 的（见项目说明  [https://github.com/krakjoe/pthreads#sapi-support][1]）；所以要分开编译，分别指定不同的`php.ini`路径从而实现 CLI 单独配置`pthreads`CGI 不需要

开始踩了坑一起编译，使用统一的配置，导致 Apache 无法启动，查看 Apache 的配置却没有问题，最后看`/var/log/apache2/error.log`才知道是加载 PHP 模块的时候出错了（看文档要仔细！！！）


### 编译 PHP CGI  


#### configure  

编译参数和 CLI 的会有一点点区别，后面会给出相关的解释

```
# CGI 不需要线程安全

./configure --prefix=/opt/php \
--enable-calendar \
--with-curl \
--enable-exif \
--enable-ftp \
--with-gettext \
--enable-mbstring \
--enable-mysqlnd \
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-oci8=instantclient,/opt/oracle/instantclient_12_1 \
--with-pdo-oci=instantclient,/opt/oracle/instantclient_12_1,12.1 \
--with-openssl \
--enable-pcntl \
--with-readline \
--enable-shmop \
--enable-sockets \
--with-tidy \
--enable-wddx \
--with-xsl \
--with-xmlrpc \
--enable-zip \
--with-zlib \
--with-config-file-scan-dir=/opt/php/etc \
--with-apxs2=/usr/bin/apxs2 \
--with-config-file-path=/opt/php/cgi \
--disable-cli
```


如果自己输入模块请检查`./configure`运行完成之后有没有 Warnings 很有可能会拼写错误或者配置名字已经改变

使用将帮助信息`./configure --help > youfile.txt`来进行对照查看比较方便

需要注意的是，这里配置了的模块是不需要再在`php.ini`配置的，相当于把这些模块打包进了 PHP 而不是从外部加载，如果强行配置可能会导致警告说在共享模块中找不到该模块
`apxs2`这个是`apxs - APache eXtenSion tool`（使用`man apxs2`得到）可以通过`whereis apxs2`找到位置

指定`--prefix`在 nix 是一个好习惯，方便卸载（ nix 和 Windows 在应用方面架构的差距！）

  
#### make
  

直接执行

```
make
```

一般来说参数填写对了执行就不会有错误，看到最后出现了`Build Complete`就运行完了


执行`make
`的时候需要很长的时间等待使用`htop`查看资源使用情况；发现是单核跑的，经过查询`make`是可以实现多进程运行的，这样时间可以大大减少。

使用`make -j8`（编译的机器 CPU 是 Core-i7 有 8 个逻辑核心所以使用 8 个进程，根据各自情况应该设置合适的数字）

如果是第二次或者多次运行`make`的时候，应该在运行`make`之前运行`make clean`；否则可能会出现一些奇奇怪怪的错误

  
#### make install  
`make`执行完成之后，执行

```
sudo make install
```

一般从输出的信息中我们可以知道安装到了`/opt/php`下

如果没有指定`--prefix`，可以在这里把输出重定向到一个文件，将来要彻底删除的时候有用


#### 配置 php.ini  

我们在配置里写了`--with-config-file-path=/opt/php/cgi`所以把源码目录下的`php.ini-development`（也可以拷贝生产环境的） 拷贝到`/opt/php/cgi/php.ini`#### 配置 PHP 到 Apache  

由于我们配置了`--with-apxs2=/usr/bin/apxs2`Apache 需要的模块已经自动放好了，通过命令可以启用 PHP7 模块

```
sudo a2enmod php7
```

重启 Apache 这个时候发现，HTML 解析正常 PHP 却输出了源码，这表示 PHP 并没有被正确的解析。这个时候去`/etc/apache2/mods-available/`看发现只有`php7.load`没有发现`php7.conf`（这里是通过对比我的 Ubuntu Desktop 发现的，上面是通过`apt`安装的）；
当然经过搜索也有人在 Gist 中说了这个问题    [https://gist.github.com/m1st0/1c41b8d0eb42169ce71a][2]
，
所以在目录下创建文件`php7.conf`

```
sudo vim /etc/apache2/mods-available/php7.conf

# 从 Ubuntu Desktop 的文件复制了如下内容
<FilesMatch ".+\.ph(p[3457]?|t|tml)$">
    SetHandler application/x-httpd-php
</FilesMatch>
<FilesMatch ".+\.phps$">
    SetHandler application/x-httpd-php-source
    # Deny access to raw php sources by default
    # To re-enable it's recommended to enable access to the files
    # only in specific virtual host or directory
    Require all denied
</FilesMatch>
# Deny access to files without filename (e.g. '.php')
<FilesMatch "^\.ph(p[3457]?|t|tml|ps)$">
    Require all denied
</FilesMatch>

# Running PHP scripts in user directories is disabled by default
#
# To re-enable PHP in user directories comment the following lines
# (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
# prevents .htaccess files from disabling it.
<IfModule mod_userdir.c>
    <Directory /home/*/public_html>
        php_admin_flag engine Off
    </Directory>
</IfModule>
```

重新开启一下 PHP 扩展`sudo a2enmod php7
`重启 Apache 发现 PHP 已经可以正常解析；到此 PHP CGI 编译完成！


### 编译 PHP CLI  

这里我们需要`pthreads`多线程支持（其实配置和 CGI 几乎可以通用，只要改动一下配置文件的位置参数）

```
# CLI 需要线程安全，要使用 pthreads，并且不需要 Apache 配置

./configure --prefix=/opt/php \
--enable-calendar \
--with-curl \
--enable-exif \
--enable-ftp \
--with-gettext \
--enable-mbstring \
--enable-mysqlnd \
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-oci8=instantclient,/opt/oracle/instantclient_12_1 \
--with-pdo-oci=instantclient,/opt/oracle/instantclient_12_1,12.1 \
--with-openssl \
--enable-pcntl \
--with-readline \
--enable-shmop \
--enable-sockets \
--with-tidy \
--enable-wddx \
--with-xsl \
--with-xmlrpc \
--enable-zip \
--with-zlib \
--with-config-file-scan-dir=/opt/php/etc \
--with-config-file-path=/opt/php/cli \
--enable-maintainer-zts \
--disable-cgi
```



* 去掉了 Apache 相关的配置`apxs2`
* 禁用`cli`改成了`cgi`
* 配置文件的路径改为`/opt/php/cli`
  

其他的配置均保持一致，后面的步骤和上面一致，直到`sudo make
 install`完成，PHP CLI 部分也已经编译完成，不要忘了将`php.ini`拷贝一份到`cli`目录。


#### 建立符号链接  

使用符号连接我们可以直接在命令行中使用`php`

```
sudo ln --symbolic /opt/php/bin/php /usr/bin/php
```

当然这里也可以把`bin`目录下的其他可执行文件进行设置，看个人需求

到这里基本上 PHP 已经编译好了并可以在命令行中使用了。


## 编译 pthreads  

PHP 的多线程特性不是官方实现的，这个项目在    [Github][3]

从页面上找到 master 分支的 zip 下载链接（clone 也可以）

```
# 下载
wget https://github.com/krakjoe/pthreads/archive/master.zip -O pthreads-master.zip
# 解压
unzip pthreads-master.zip
# 复制到 ext 目录下
mv pthreads-master ext/pthreads
# 进入 pthreads 的目录
cd ext/pthreads/
```

接下来运行`phpize`生成`configure`

```
/opt/php/bin/phpize
```

运行`configure`

```
./configure --with-php-config=/opt/php/bin/php-config --prefix=/opt/php
```

运行完成之后和上面一样的运行`make
 && make
 install`，这个时候应该会生成文件`/opt/php/lib/php/extensions/no-debug-zts-20170718/pthreads.so`加载`pthreads`模块到 PHP，编辑`php.ini`

```
sudo vim /opt/php/conf.d/php.ini

# 加入如下的行
extension=pthread
```

检查 PHP 已经加载的模块，使用`php -m`查看已经加载的模块列表，应该`pthreads`已经出现在列表中了

到此基本上所有的安装都完成了



[0]: http://php.net/downloads.php
[1]: https://github.com/krakjoe/pthreads#sapi-support
[2]: https://gist.github.com/m1st0/1c41b8d0eb42169ce71a
[3]: https://github.com/krakjoe/pthreads
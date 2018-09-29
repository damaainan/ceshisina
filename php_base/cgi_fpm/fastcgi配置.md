# fastcgi配置

我们这里说的fastcgi配置专指nginx对fastcgi的配置，fastcgi本身的配置介绍在[fastcgi 安装][0]文中有说明。

#### nginx的fastcgi模块提供的命令

##### [fastcgi_pass][1]

这个命令是指定将http代理到哪个fastcgi服务端接口。`fastcgi_pass`后面是填写fastcgi服务端地址的，这个地址可以是域地址，也可以是Uninx-域套接字。
```nginx
    fastcgi_pass localhost:9000;
    
    
    fastcgi_pass unix:/tmp/fastcgi.socket;
```
这里的设置需要和fastcgi自身配置的listen_address做相应地对应。

比如上面那个例子，listen_addree就应该这么配置：

    <value name="listen_address">/tmp/fastcgi.socket</value>

##### [fastcgi_param][2]

这个命令是设置fastcgi请求中的参数，具体设置的东西可以在$_SERVER中获取到。

比如你想要设置当前的机器环境，可以使用fastcgi_param ENV test;来设置。

对于php来说，最少需要设置的变量有：
```nginx
    fastcgi_param SCRIPT_FILENAME /home/www/scripts/php$fastcgi_script_name;
    fastcgi_param QUERY_STRING    $query_string;
```
对于POST请求，还需要设置：  
`fastcgi_param REQUEST_METHOD` 
```nginx
    fastcgi_param REQUEST_METHOD requestmethod;fastcgiparamCONTENTTYPEcontent_type;
    fastcgi_param CONTENT_LENGTH $content_length;
```
`fastcgi_param`还可以使用`if_not_empty`进行设置。意思是如果value非空才进行设置。
```nginx
    fastcgi_param HTTPS   $https if_not_empty;
```
##### fastcgi_index

这个命令设置了fastcgi默认使用的脚本。就是当`SCRIPT_FILENAME`没有命中脚本的时候，使用的就是`fastcgi_index`设置的脚本。
```nginx
    # 以上三个命令能组成最基本的fastcgi设置了
    
    location / {
      fastcgi_pass   localhost:9000;
      fastcgi_index  index.php;
     
      fastcgi_param  SCRIPT_FILENAME  /home/www/scripts/php$fastcgi_script_name;
      fastcgi_param  QUERY_STRING     $query_string;
      fastcgi_param  REQUEST_METHOD   $request_method;
      fastcgi_param  CONTENT_TYPE     $content_type;
      fastcgi_param  CONTENT_LENGTH   $content_length;
    }
```
##### fastcgi_hide_header，fastcgi_ignore_headers，fastcgi_pass_header

##### fastcgi_cache

这个命令是开启fastcgi的文件缓存。这个缓存可以将动态的页面存为静态的。以提供为加速或者容灾使用。





### fastcgi安装

这里及以下的web服务器都是以nginx为例子和说明，php以5.3为例子。

#### php-fpm

fastcgi在服务器上会启动多个进程进行解析，这个时候就需要一个fastcgi的管理器，管理哪个子进程可以结束，哪个进行应该开启了。

fastcgi的进程管理器有两种，spawn-fcgi和php-fpm。其中的spawn-fcgi一般是和lighttp配合使用的。而php-fpm一般会配合nginx来使用。

#### 安装

##### 下载php和php-fpm源码包

php下载地址：http://php.net/downloads.php

php-fpm下载地址：http://php-fpm.org/downloads/

在下载php-fpm的时候，需要尽量使php版本和php-fpm版本一致或者版本差别最小，否则有可能会出现兼容性的问题。

##### 配置安装环境

php需要下面软件的支持，如果没有安装，请自行安装：

    gcc gcc-c++ libxml2 libxml2-devel autoconf libjpeg libjpeg-devel libpng libpng-devel freetype freetype-devel  zlib zlib-devel glibc glibc-devel glib2 glib2-devel

##### 编译安装php和php-fpm

    [root@localhost local]#tar zxvf php-5.2.13.tar.gz  
    [root@localhost local]#gzip -cd php-5.2.13-fpm-0.5.13.diff.gz | patch -d php-5.2.13 -p1
    [root@localhost local]#cd php-5.2.13  
    [root@localhost php-5.2.13]#./configure  --prefix=/usr/local/php --enable-fastcgi --enable-fpm  
    [root@localhost php-5.2.13]#make  
    [root@localhost php-5.2.13]#make install  
    [root@localhost php-5.2.13]cp php.ini-dist /usr/local/php/lib/php.ini

##### 配置与优化php-fpm

php的配置文件存放在 /usr/local/php/lib/php.ini中。

而php-fpm的配置文件存放在 /usr/local/php/etc/php-fpm.conf中

其中的几项配置需要注意：

标签`listen_address` 是配置fastcgi进程监听的IP地址以及端口，默认是127.0.0.1:9000。

    <value name="listen_address">127.0.0.1:9000</value>

标签`display_errors`  用来设置是否显示PHP错误信息，默认是0，不显示错误信息，设置为1可以显示PHP错误信息。

    <value name="display_errors">0</value>

标签 `user` 和 `group` 用于设置运行FastCGI进程的用户和用户组。需要注意的是，这里指定的用户和用户组要和Nginx配置文件中指定的用户和用户组一致。

    <value name="user">nobody</value>
    <value name="group">nobody</value>

标签`max_children`  用于设置FastCGI的进程数。根据官方建议，小于2GB内存的服务器，可以只开启64个进程，4GB以上内存的服务器可以开启200个进程。也可以根据服务的内存数来估计需要开启多少fastcgi进程数。大概一个fastcgi进程占20M的内存。

    <value name="max_children">5</value>

标签request_terminate_timeout_用于设置FastCGI执行脚本的时间。默认是0s，也就是无限执行下去，这个参数设置好了可以用于保证不会有执行时间过长的php阻塞住fastcgi进程。

    <value name="request_terminate_timeout">s</value>

标签`rlimit_files`  用于设置PHP-FPM对打开文件描述符的限制，默认值为1024。这个标签的值必须和Linux内核打开文件数关联起来，例如要将此值设置为65535，就必须在Linux命令行执行'ulimit -HSn 65536'。

    <value name="rlimit_files">1024</value>

标签`max_requests` 指明了每个children最多处理多少个请求后便会被关闭，默认的设置是500。

为什么会需要这个参数设置呢？php和fastcgi都是C写的，一些php模块什么的有可能实际上存在着内存泄露等问题，所以一般php作为守护进程一直执行是不大可取的行为。既然php有可能有内存泄露的问题，那么如果fastcgi进程一直执行着，那么就有可能导致机器的内存出现吃爆的现象。所以在执行一段时间之后，我们是希望fastcgi能自动重启动。这个参数就是做这个用的。

    <value name="max_requests">500</value>

标签`allowed_clients` 用于设置允许访问FastCGI进程解析器的IP地址。如果不在这里指定IP地址，Nginx转发过来的PHP解析请求将无法被接受。

    <value name="allowed_clients">127.0.0.1</value>

##### 启动php-fpm

    /usr/local/php/sbin/php-fpm  start


[0]: install.md
[1]: http://tengine.taobao.org/nginx_docs/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_pass
[2]: http://tengine.taobao.org/nginx_docs/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_param
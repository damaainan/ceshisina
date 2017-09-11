# Nginx配置-安装PHP7

 时间 2017-09-03 22:45:22  

原文[http://www.jialeens.com/archives/309][1]

<font face=微软雅黑>

总所周知，Nginx只是一个Web服务器，并不能解析php。nginx一般是把请求发fastcgi管理进程处理，fastcgi管理进程选择cgi子进程处理结果并返回被nginx。

搭建php环境时，经常可以看到cgi、fastcgi、php-fpm，那么他们之间的关系是什么呢，请看下面描述

* CGI: `Common Gateway Interface` 通用网管协议
* FastCGI: 一种CGI的实现方式，更快更搞笑
* php-fpm: `php FastCGI Process Manager`(FastCGI进程管理器)

可以看出`php-fpm`是一个`fastcgi`的实现，专门处理php的，`PHP-FPM`其实是PHP源代码的一个补丁，旨在将FastCGI进程管理整合进PHP包中。必须将它patch到你的PHP源代码中，在编译安装PHP后才可以使用，同时新版PHP已经集成`php-fpm`了，不再是第三方的包了，推荐使用。

今天主要分享一下`php-fpm`的安装与Nginx的对应配置。Nginx的安装可以看以前的系列。

## 一、安装

首先安装php-fpm相关依赖

    yum -y install php70w-fpm php70w-cli php70w-gd php70w-mcrypt php70w-mysql php70w-pear php70w-xml php70w-mbstring php70w-pdo php70w-json php70w-pecl-apcu php70w-pecl-apcu-devel

安装完成后，可以通过php -v查看php是否已经安装完毕。

![][4]

## 二、配置PHP-FPM

使用 vim 编辑默认的 php7-fpm 配置文件。

    vim /etc/php-fpm.d/www.conf

修改php-fpm运行时的用户为nginx

    user = nginx
    group = nginx

修改`php-fpm`运行的端口号

    listen = 127.0.0.1:9000

启动php-fpm，并将其设置为开机启动

    sudo systemctl start php-fpm
    sudo systemctl start nginx
    sudo systemctl enable php-fpm
    sudo systemctl enable nginx

## 三、设置Nginx

下来开始配置Nginx。

在server节点下配置PHP-FPM相关参数：

```nginx
    fastcgi_buffer_size 64k;
    fastcgi_buffers 4 64k;
    fastcgi_busy_buffers_size 128k;
    fastcgi_temp_file_write_size 128k;
```

创建专门处理php的location配置：

```nginx
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PHP_VALUE "upload_max_filesize=128M \n post_max_size=128M";
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
```

完整配置如下：

```nginx
    server {
        listen 80;
        server_name jialeens.com www.jialeens.com;
    
        fastcgi_buffer_size 64k;
        fastcgi_buffers 4 64k;
        fastcgi_busy_buffers_size 128k;
        fastcgi_temp_file_write_size 128k;
        client_max_body_size 100m;
        root /var/www/html;
        index index.php;
    
        access_log /var/log/nginx/jialeens-access-http.log;
        error_log /var/log/nginx/jialeens-error-http.log;
    
        location / {
            try_files $uri $uri/ /index.php?$args;
        }
    
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PHP_VALUE "upload_max_filesize=128M \n post_max_size=128M";
            fastcgi_param PATH_INFO $fastcgi_path_info;
        }
    }
```

## 四、测试

在server根目录下，增加phpinfo.php测试文件，内容如下：vi phpinfo.php

    <?php
        echo phpinfo();
    ?>

保存后退出。访问`http://你的服务器ip/phpinfo.php`，就可以见到php信息了。

![][5]

如果看到上述页面，即表示你的PHP7环境已经安装完成。
</font>

[1]: http://www.jialeens.com/archives/309

[4]: http://img2.tuicool.com/yeAJVnq.png
[5]: http://img2.tuicool.com/6VBBB33.png
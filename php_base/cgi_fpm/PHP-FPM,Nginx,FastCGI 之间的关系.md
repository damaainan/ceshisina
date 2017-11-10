# PHP-FPM,Nginx,FastCGI 之间的关系

 时间 2017-11-09 16:44:27  

原文[https://blog.tanteng.me/2017/11/nginx-fastcgi-php-fpm/][1]


本文介绍 PHP-FPM,Nginx,FastCGI 三者之间的关系，以及 Nginx 反向代理和负载均衡的配置。

### PHP-FPM,Nginx,FastCGI 之间的关系

FastCGI 是一个协议，它是应用程序和 WEB 服务器连接的桥梁。Nginx 并不能直接与 PHP-FPM 通信，而是将请求通过 FastCGI 交给 PHP-FPM 处理。

    location ~ \.php$ {
        try_files $uri /index.php =404;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    

这里 fastcgi_pass 就是把所有 php 请求转发给 php-fpm 进行处理。通过 netstat 命令可以看到，127.0.0.1:9000 这个端口上运行的进程就是 php-fpm.

![][4]

### Nginx 反向代理

Nginx 反向代理最重要的指令是 proxy_pass，如：

    location ^~ /seckill_query/ {
        proxy_pass http://ris.filemail.gdrive:8090/;
        proxy_set_header Host ris.filemail.gdrive;
    }
     
    location ^~ /push_message/ {
        proxy_pass http://channel.filemail.gdrive:8090/;
        proxy_set_header Host channel.filemail.gdrive;
    }
     
    location ^~ /data/ {
        proxy_pass http://ds.filemail.gdrive:8087/;
        proxy_set_header Host ds.filemail.gdrive;
    }
    

通过 location 匹配 url 路径，将其转发到另外一个服务器处理。

通过负载均衡 upstream 也可以实现反向代理。

### Nginx 负载均衡

介绍一下 upstream 模块：

负载均衡模块用于从”upstream”指令定义的后端主机列表中选取一台主机。nginx先使用负载均衡模块找到一台主机，再使用upstream模块实现与这台主机的交互。

负载均衡配置：

    upstream php-upstream {
        ip_hash;
     
        server 192.168.0.1;
        server 192.168.0.2;
    }
     
    location / {
        root   html;
        index  index.html index.htm;
        proxy_pass http://php-upstream;
    }
    

该例定义了一个 php-upstream 的负载均衡配置，通过 proxy_pass 反向代理指令应用这个配置。这里用的 ip_hash 算法，负载均衡的算法有多种，就不一一列举了。

#### 负载均衡也可以用在 fastcgi_pass 上。

如：

    fastcgi_pass http://php-upstream
    

#### 反向代理和负载均衡是什么关系

反向代理和负载均衡这两个词经常出现在一起，但他们实际上是不同的概念，负载均衡它更多的是强调的是一种算法或策略，将请求分布到不同的机器上，因此实际上也起到了反向代理的作用。

#### proxy_pass 和 fastcgi_pass 的区别

一个是反向代理模块，一个是转发给 factcgi 后端处理。

![][5]


[1]: https://blog.tanteng.me/2017/11/nginx-fastcgi-php-fpm/?utm_source=tuicool&utm_medium=referral
[4]: ./img/rquu6fR.png
[5]: ./img/Y3I7JzM.jpg
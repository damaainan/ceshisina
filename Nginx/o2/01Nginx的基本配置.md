## Nginx的基本配置

<font face=微软雅黑>
### 一、配置文件

Nginx安装完成之后，可以再安装目录下看到有以下几个文件。

![][0]

其中需要关注的有nginx.conf和conf.d目录。

* nginx.conf配置着nginx的公用配置，例如nginx的工作线程数，keepalive_time。同时也会引用conf.d中的所有配置文件，可以认为nginx.conf是nginx的默认配置文件。如果我们要修改nginx的工作线程数，则可以修改此配置文件。
* conf.d中的配置文件为自定义的配置，一般情况下，只需要在conf.d中配置自己应用配置信息即可，如果和nginx.conf配置文件有重复，则默认会覆盖nginx.conf中的配置。
* 因为一个nginx可能会监听多个端口，每个端口会去代理不同的机器，所以我建议大家将每一个服务的配置文件单独配置，不要放在一个配置文件中。例如有两个域名A和B，在配置的时候，就在conf.d中创建两个配置文件，一个管理A域名的服务，一个管理B域名的服务，这样可以使配置文件分离，也更清晰的列出每个服务对应的配置文件所在的地方。

### 二、一个简单示例

#### 1、配置

进入conf.d目录，删除原有的conf结尾的配置文件。

    cd conf.d<br>rm -rf *.conf<br>

然后新建一个配置文件，mysite.conf，内容如下：

```nginx
    server {
            listen       80;
            server_name  jialeens.com www.jialeens.com;
            root   /var/www/html;
    
            charset utf-8;
            access_log  logs/host.access.log  main;
    
            #error_page  404              /404.html;
    
            # redirect server error pages to the static page /50x.html
            #
            error_page   500 502 503 504  /50x.html;
            location = /50x.html {
                root   html;
            }
        }
```

保存后退出，先使用测试命令测试一下配置是否有错误,然后执行重启命令

![][1]

这样，访问机器的80端口，就可以看到Nginx的欢迎页面了。

#### 2、配置说明

* server：当前是一个服务
* listen：当前配置服务所监听的端口
* servier_name：当前服务绑定的域名
* root：当前服务的根路径
* charset：字符集编码
* access_log：访问日志路径
* error_page：错误代码页面配置
* location：url路径配置

### 三、配置大全

上面是一个简单的例子，下面是一个比较复杂的配置和参数说明，为了列举多的配置，将配置文件放到一起了。

```nginx
    #定义Nginx运行的用户和用户组
    user www www;
    
    #nginx进程数，建议设置为等于CPU总核心数。
    worker_processes 8;
    
    #全局错误日志定义类型，[ debug | info | notice | warn | error | crit ]
    error_log /var/log/nginx/error.log info;
    
    #进程文件
    pid /var/run/nginx.pid;
    
    #一个nginx进程打开的最多文件描述符数目，理论值应该是最多打开文件数（系统的值ulimit -n）与nginx进程数相除，但是nginx分配请求并不均匀，所以建议与ulimit -n的值保持一致。
    worker_rlimit_nofile 65535;
    
    #工作模式与连接数上限
    events
    {
        #参考事件模型，use [ kqueue | rtsig | epoll | /dev/poll | select | poll ]; epoll模型是Linux 2.6以上版本内核中的高性能网络I/O模型，如果跑在FreeBSD上面，就用kqueue模型。
        use epoll;
        #单个进程最大连接数（最大连接数=连接数*进程数）
        worker_connections 65535;
    }
    
    #设定http服务器
    http
    {
        include mime.types; #文件扩展名与文件类型映射表
        default_type application/octet-stream; #默认文件类型
        #charset utf-8; #默认编码
        server_names_hash_bucket_size 128; #服务器名字的hash表大小
        client_header_buffer_size 32k; #上传文件大小限制
        large_client_header_buffers 4 64k; #设定请求缓
        client_max_body_size 8m; #设定请求缓
        sendfile on; #开启高效文件传输模式，sendfile指令指定nginx是否调用sendfile函数来输出文件，对于普通应用设为 on，如果用来进行下载等应用磁盘IO重负载应用，可设置为off，以平衡磁盘与网络I/O处理速度，降低系统的负载。注意：如果图片显示不正常把这个改成off。
        autoindex on; #开启目录列表访问，合适下载服务器，默认关闭。
        tcp_nopush on; #防止网络阻塞
        tcp_nodelay on; #防止网络阻塞
        keepalive_timeout 120; #长连接超时时间，单位是秒
        
        #FastCGI相关参数是为了改善网站的性能：减少资源占用，提高访问速度。下面参数看字面意思都能理解。
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 64k;
        fastcgi_buffers 4 64k;
        fastcgi_busy_buffers_size 128k;
        fastcgi_temp_file_write_size 128k;
        
        #gzip模块设置
        gzip on; #开启gzip压缩输出
        gzip_min_length 1k; #最小压缩文件大小
        gzip_buffers 4 16k; #压缩缓冲区
        gzip_http_version 1.0; #压缩版本（默认1.1，前端如果是squid2.5请使用1.0）
        gzip_comp_level 2; #压缩等级
        gzip_types text/plain application/x-javascript text/css application/xml;
        #压缩类型，默认就已经包含text/html，所以下面就不用再写了，写上去也不会有问题，但是会有一个warn。
        gzip_vary on;
        #limit_zone crawler $binary_remote_addr 10m; #开启限制IP连接数的时候需要使用
        
        upstream jialeens.com {
            #upstream的负载均衡，weight是权重，可以根据机器配置定义权重。weigth参数表示权值，权值越高被分配到的几率越大。
            server 192.168.0.1:80 weight=3;
            server 192.168.0.2:80 weight=2;
            server 192.168.0.3:80 weight=3;
        }
        
        #虚拟主机的配置
        server
        {
            #监听端口
            listen 80;
            #域名可以有多个，用空格隔开
            server_name jialeens.com www.jialeens.com;
            index index.html index.htm index.php;
            root /data/www/html;
            location ~ .*\.(php|php5)?$
            {
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                include fastcgi.conf;
            }
            #图片缓存时间设置
            location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
            {
                expires 10d;
            }
            #JS和CSS缓存时间设置
            location ~ .*\.(js|css)?$
            {
                expires 1h;
            }
            #日志格式设定
            log_format access '$remote_addr - $remote_user [$time_local] "$request" '
            '$status $body_bytes_sent "$http_referer" '
            '"$http_user_agent" $http_x_forwarded_for';
            #定义本虚拟主机的访问日志
            access_log /var/log/nginx/ha97access.log access;
            
            #对 "/" 启用反向代理
            location / {
                proxy_pass http://127.0.0.1:88;
                proxy_redirect off;
                proxy_set_header X-Real-IP $remote_addr;
                #后端的Web服务器可以通过X-Forwarded-For获取用户真实IP
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                #以下是一些反向代理的配置，可选。
                proxy_set_header Host $host;
                client_max_body_size 10m; #允许客户端请求的最大单文件字节数
                client_body_buffer_size 128k; #缓冲区代理缓冲用户端请求的最大字节数，
                proxy_connect_timeout 90; #nginx跟后端服务器连接超时时间(代理连接超时)
                proxy_send_timeout 90; #后端服务器数据回传时间(代理发送超时)
                proxy_read_timeout 90; #连接成功后，后端服务器响应时间(代理接收超时)
                proxy_buffer_size 4k; #设置代理服务器（nginx）保存用户头信息的缓冲区大小
                proxy_buffers 4 32k; #proxy_buffers缓冲区，网页平均在32k以下的设置
                proxy_busy_buffers_size 64k; #高负荷下缓冲大小（proxy_buffers*2）
                proxy_temp_file_write_size 64k;
                #设定缓存文件夹大小，大于这个值，将从upstream服务器传
            }
            
            #设定查看Nginx状态的地址
            location /NginxStatus {
                stub_status on;
                access_log on;
                auth_basic "NginxStatus";
                auth_basic_user_file conf/htpasswd;
                #htpasswd文件的内容可以用apache提供的htpasswd工具来产生。
            }
            
           
            #本地动静分离反向代理配置
            #所有jsp的页面均交由tomcat或resin处理
            location ~ .(jsp|jspx|do)?$ {
                proxy_set_header Host $host;
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_pass http://127.0.0.1:8080;
            }
            
            #所有静态文件由nginx直接读取不经过tomcat或resin
            location ~ .*.(htm|html|gif|jpg|jpeg|png|bmp|swf|ioc|rar|zip|txt|flv|mid|doc|ppt|pdf|xls|mp3|wma)$
            { expires 15d; }
            location ~ .*.(js|css)?$
            { expires 1h; }
        }
    }
```

其中负载均衡、反向代理等都会在后面的章节讲述。

### 四、其他

* 配置一个Nginx代理多个域名：（二）Nginx基本配置-配置多个域名
* Nginx设置上传文件大小限制：（二）Nginx基本配置-上传文件大小限制




</font>





[0]: ../img/VvUnEfe.png
[1]: ../img/aIfm2eN.png
# Nginx基础笔记 

* [Nginx基础笔记][0]
  * [资源][1]
  * [安装][2]
    * [ubuntu下][3]
    * [编译安装][4]
  * [基本操作][5]
  * [HTTP基本配置][6]
    * [配置说明][7]
    * [配置文件目录结构][8]
    * [配置文件结构][9]
  * [模块][10]
    * [模块化][11]
    * [index模块][12]
    * [Log模块][13]
    * [Real IP模块][14]
    * [Access模块][15]
    * [Rewrite模块][16]
    * [Proxy模块][17]
    * [upstream模块][18]
  * [其他][19]
    * [配置静态化目录][20]
    * [负载均衡][21]
    * [控制页面缓存][22]
    * [nginx的内置变量][23]
    * [配置shtml访问][24]
    * [auth basic配置][25]

nginx小结

### 资源

> 资源

Nginx [官网][26]

Nginx [官方下载地址][27]

Nginx最佳实践配置项目 [地址][28]

Nginx Configuration [wiki][29]

> 教程

agentzh的Nginx教程 [链接][30]

Nginx开发从入门到精通 [入口][31]

> 文章

Nginx战斗准备-优化指南 [入口][32]

Nginx模块开发入门 [入口][33]

解析Nginx负载均衡 [入口][34]

Nginx Rewrite研究笔记 [入口][35]

## 安装

### ubuntu下

    sudo apt-get install nginx
    
    启动
    sudo /etc/init.d/nginx start       #通过init.d下的启动文件启动。
    sudo service nginx start#通过ubuntu的服务管理器启动
    
    配置文件位置
    /etc/nginx/nginx.conf
    

### 编译安装

1.先决条件

    1.gcc
    apt-get install gcc
    2.pcre(Perl Compatible Regular Expression)
    apt-get install libpcre3 libpcre3-dev
    3.zlib
    apt-get install zliblg zliblg-dev
    4.openssl
    apt-get install openssl opensll-dev
    
    #如果非apt，可以使用下载包手动编译安装的方式处理
    

2.下载包

    www.nginx.net 下载稳定版
    wget http://nginx.org/download/nginx-1.4.4.tar.gz
    

3.解压安装

    tar -xzvf nginx-1.4.4.tar.gz
    #默认，安装目录/usr/local/nginx
    ./configure
    make
    make install
    
    #配置
    ./configure --conf-path=/etc/nginx/nginx.conf
    
    可以配置一些其他选项
    
    安装后查看下目录下的Configuration summary
    

4.init脚本

    需要给nginx建立一个init脚本
    从网上捞一个，放入/etc/init.d/nginx
    

推荐编译配置

    1.使用不同prefix，方便指定不同版本,也便于升级
    ./configure --prefix=/usr/local/nginx-1.4.4
    

## 基本操作

    查看帮助
    /usr/local/nginx/sbin/nginx -h
    
    立即停止进程(TERM信号)
    /usr/local/nginx/sbin/nginx -s stop
    
    温和停止进程(QUIT信号)
    /usr/local/nginx/sbin/nginx -s quit
    
    重加载
    /etc/init.d/nginx reload #有init脚本情况下
    /usr/local/nginx/sbin/nginx -s reload #原生
    
    检测配置文件是否正确
    /usr/local/nginx/sbin/nginx -t #生产路径下的
    /usr/local/nginx/sbin/nginx -t -c /home/ken/tmp/test.conf #可以测试某个临时文件
    

## HTTP基本配置

### 配置说明

    注释，#
    每条指令总是以分好结束(;)
    配置继承：在一个区块中嵌套其他区段，那么被嵌套的区段会继承其父区段的设置
    字符串，可以没有引号，但是如果存在特殊字符（空格，分号，花括号）需要用引号引起
    单位：大小(k/K m/M) 时间值(ms/s/m/h/d/w/M/y 默认s)
    模块提供各种变量值，可以进行读取和赋值（每个模块提供变量列表需要自己去查）
    

### 配置文件目录结构

    /usr/local/nginx/conf/
    
    - mime.types 一个文件扩展列表，它们与MIME类型关联
    - fastcgi.conf 与FastCGI相关的配置文件
    - proxy.conf 与Proxy相关的配置文件
    - nginx.conf 应用程序的基本配置文件
    - sites/
        |- a.conf #允许给每个单独网站建立一个配置文件
        |- b.conf
        |- dir/
            |- c.conf
    
    需要在nginx.conf中使用包含命令
    include sites/*.conf;
    include sites/*/*.conf;
    

### 配置文件结构

```nginx
    http { #嵌入配置文件的根部， 一个http里可以配置多个server
    
        server { #声明一个站点
            server_name www.website.com; #监听的主机名
            listen 80; #监听套接字所使用的ip地址和端口号
    
            error_page 404 /not_found.html;
            error_page 500 501 502 503 504 /server_error.html;
    
            index index.html;
    
            root /var/www/website/com/html; #定义文档的根目录
    
            #location, 通过制定的模式与客户端请求的URI相匹配
            location / { #网站的特定位置
            }
            location /admin/ { #网站的特定位置 #
                alias /var/www/locked/; #只能放在 location区段中，为指定路径提供别名
            }
    
            #操作符,匹配时跟定义顺序无关
            location = /abcd { #精确匹配，不能用正则
            }
            location /abc/ { #url必须以指定模式开始，不能用正则
            }
            location ^~ /abcd$ { #吴标致行为，URI定位必须以指定模式开始，如果匹配，停止搜索其他模式
            }
            location ~ ^/abcd$ { #正则匹配，区分大小写
            }
            location ~* ^/abcd$ { #正则匹配，不区分大小写
            }
            location @test  { #定义location区段名，客户端不能访问，内部产生的请求可以,例如try_files或error_page
            }
        }
    }
```

## 模块

### 模块化

nginx真正的魅力在于它的模块，整个应用程序建立在一个模块化系统之上，在编译时，可以对每一个模块进行启用或者禁用

### index模块

定义往回走哪index页

    index index.php index.html /data/website/index.html;
    #可以指定多个，但是ngxin提供第一个找到的文件
    

### Log模块

    access_log /file/path;
    error_log /file/path error;  #level: debug/info/notice/warn/error/crit
    

日志格式

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
    '$status $body_bytes_sent "$http_referer" '
    '"$http_user_agent" $http_x_forwarded_for';
    
    access_log /var/log/test.log main;

### Real IP模块

默认编译nginx不包含这个模块

当通过nginx将用户请求进行转发时，接收请求的应用要拿到用户的真实IP(经转发拿到的是服务器的IP)

    real_ip_header X-Forwarded-For;
    

### Access模块

可以禁用ip段

语法

    #如果规则之间有冲突，会以最前面匹配的规则为准
    deny IP;
    deny subnet;
    allow IP;
    allow subnet;
    # block all ips
    deny    all;
    # allow all ips
    allow    all;
    

配置一个blockips.conf,然后在nginx.conf中include

e.g

```nginx
    location {
        allow 127.0.0.1; #允许本地ip 注意顺序，allow要放在前面
        deny all; #禁止其他ip
    }
```

### Rewrite模块

作用：执行URL重定向,允许你去掉带有恶意的URL，包含多个参数（修改）

利用正则的匹配，分组和引用，达到目的

break/return/set等

```nginx
    if (-f $uri) {
        break
    }
    if ($uri ~ ^/admin/){
        return 403;
    }
    if ($uri ~ ^/search/(.*)$) {
        set $query $1;
        rewrite ^ /search.php?q=$query?;
    }
```

例子

    A:http://website.com/search/some-search-keywords
    B:http://website.com/search.php?q=some-search-keywords
    rewrite ^/search/(.*)$ /search.php?q=$1?;
    
    A:http://website.com/user/31/James
    B:http://website.com/user.php?id=31&name=James
    rewrite ^/user/([0-9]+)/(.+)$ /user.php?id=$1&name=$2?;
    
    A:http://website.com/index.php/param1/param2/param3
    B:http://website.com/index.php/?p1=param1&p2=param2&p3=param3
    rewrite ^/index.php/(.*)/(.*)/(.*)$ /index.php?p1=$1&p2=$2&p3=$3?;

rewrite语法

    rewrite A B option;
    options:
            last :表示完成rewrite
            break:本规则匹配完成后，终止匹配，不再匹配后面的规则
            redirect:返回302临时重定向，地址栏会显示跳转后的地址
            permanent:返回301永久重定向，地址栏会显示跳转后的地址
    

### Proxy模块

默认模块，允许你讲客户端的HTTP请求转到后端服务器

```nginx
    location / {
        proxy_pass_header Server;  #该指令强制一些被忽略的头传递到客户端
        proxy_redirect off; #允许改写出现在HTTP头却被后端服务器触发重定向的URL,对相应本身不做任何处理
        proxy_set_header Host $http_host; #允许你重新定义代理header值再转到后端服务器.目标服务器可以看到客户端的原始主机名
        proxy_set_header X-Real-IP $remote_addr; #目标服务器可以看到客户端的真实ip，而不是转发服务器的ip
        proxy_set_header X-Scheme $scheme;
        proxy_pass http://localhost:8080;
    }
```

### upstream模块

```nginx
    upstream up_name {
        server 192.168.0.1:9000 weight=5; #权重
        server 192.168.0.2:9000 weight=5 max_fails=5 fail_timeout=60s; #在60s内，其错误通信超过5次,认为该服务失效
        server 192.168.0.3:9000 down; #服务标记为离线，不再使用
        server 192.168.0.4:9000 backup; #备份服务器，其他全部宕机了才启用
    }
```
## 其他

### 配置静态化目录

```nginx
        location /static/
        {
            root /var/www/app/;
            autoindex off;
        }
```

### 负载均衡

```nginx
    http {
        include mime.types;
        default_type application/octet-stream;
    
        keepalive_timeout 120;
    
        tcp_nodelay on;
    
        upstream up_localhost {
            server 127.0.0.1:8000 weight=5;
            server 127.0.0.1:8001 weight=10;
        }
    
        server {
            listen 80;
    
            server_name localhost;
    
            location /{
                proxy_pass http://up_localhost;
                proxy_set_header Host $host;
                proxy_set_header X-Real_IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            }
        }
    
    }
```

### 控制页面缓存

```nginx
    location ~ \.(htm|html|gif|jpg|jpeg|png|bmp|ico|css|js|txt)$ {
        root /opt/webapp;
        expires 24h;
    }
    
    expires 1 January, 1970, 00:00:01 GMT;
    expires 60s;
    expires 30m;
    expires 24h;
    expires 1d;
    expires max;
    expires off;
```

### nginx的内置变量

    $arg_PARAMETER 这个变量包含在查询字符串时GET请求PARAMETER的值。
    $args 这个变量等于请求行中的参数。
    $binary_remote_addr 二进制码形式的客户端地址。
    $body_bytes_sent
    $content_length 请求头中的Content-length字段。
    $content_type 请求头中的Content-Type字段。
    $cookie_COOKIE cookie COOKIE的值。
    $document_root 当前请求在root指令中指定的值。
    $document_uri 与$uri相同。
    $host 请求中的主机头字段，如果请求中的主机头不可用，则为服务器处理请求的服务器名称。
    $is_args 如果$args设置，值为"?"，否则为""。
    $limit_rate 这个变量可以限制连接速率。
    $nginx_version 当前运行的nginx版本号。
    $query_string 与$args相同。
    $remote_addr 客户端的IP地址。
    $remote_port 客户端的端口。
    $remote_user 已经经过Auth Basic Module验证的用户名。
    $request_filename 当前连接请求的文件路径，由root或alias指令与URI请求生成。
    $request_body 这个变量（0.7.58+）包含请求的主要信息。在使用proxy_pass或fastcgi_pass指令的location中比较有意义。
    $request_body_file 客户端请求主体信息的临时文件名。
    $request_completion 请求完成
    $request_method 这个变量是客户端请求的动作，通常为GET或POST。包括0.8.20及之前的版本中，这个变量总为main request中的动作，如果当前请求是一个子请求，并不使用这个当前请求的动作。
    $request_uri 这个变量等于包含一些客户端请求参数的原始URI，它无法修改，请查看$uri更改或重写URI。
    $schemeHTTP 方法（如http，https）。按需使用，例：
    rewrite ^(.+)$ $scheme://example.com$1 redirect;
    $server_addr 服务器地址，在完成一次系统调用后可以确定这个值，如果要绕开系统调用，则必须在listen中指定地址并且使用bind参数。
    $server_name 服务器名称。
    $server_port 请求到达服务器的端口号。
    $server_protocol 请求使用的协议，通常是HTTP/1.0或HTTP/1.1。
    $uri 请求中的当前URI(不带请求参数，参数位于$args)，可以不同于浏览器传递的$request_uri的值，它可以通过内部重定向，或者使用index指令进行修改。

### 配置shtml访问

[文档][36]

```nginx
    location  ~ ^/static {
        ....
        ssi on;
        ssi_client_errors on;
        ssi_types text/shtml;
    }
```

### auth basic配置

首先, 安装htpasswd, 生成密码

    htpasswd -c paas_admin username
    password: password
    

生成文件后, 拷贝到nginx目录下

配置

```nginx
    location / {
        auth_basic "Restricted";
        auth_basic_user_file /usr/local/nginx/conf/kibana.htpasswd;
        root  /data/BKLogTool/kibana;
        index  index.html  index.htm;
    }
```

reload nginx

[0]: #
[1]: #_1
[2]: #_3
[3]: #ubuntu
[4]: #_4
[5]: #_6
[6]: #http
[7]: #_7
[8]: #_8
[9]: #_9
[10]: #_11
[11]: #_12
[12]: #index
[13]: #log
[14]: #real-ip
[15]: #access
[16]: #rewrite
[17]: #proxy
[18]: #upstream
[19]: #_14
[20]: #_15
[21]: #_16
[22]: #_17
[23]: #nginx
[24]: #shtml
[25]: #auth-basic
[26]: http://nginx.org/
[27]: http://nginx.org/en/download.html
[28]: https://github.com/Umkus/nginx-boilerplate
[29]: http://wiki.nginx.org/Configuration
[30]: http://openresty.org/download/agentzh-nginx-tutorials-zhcn.html
[31]: http://tengine.taobao.org/book/index.html
[32]: http://www.oschina.net/translate/nginx-setup
[33]: http://blog.codinglabs.org/articles/intro-of-nginx-module-development.html
[34]: http://stblog.baidu-tech.com/?p=2027
[35]: http://blog.cafeneko.info/2010/10/nginx_rewrite_note/
[36]: http://nginx.org/en/docs/http/ngx_http_ssi_module.html
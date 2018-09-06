## Nginx配置文件解析及实例

2018.08.19 21:44*

来源：[https://www.jianshu.com/p/e37a2ed4f68d](https://www.jianshu.com/p/e37a2ed4f68d)

-----

### 配置文件解析
 **`配置文件主要由四部分组成：`** 


* main(全区设置)
* server(主机配置)
* upstream(负载均衡服务器设置)
* location(URL匹配特定位置设置)。


下面以默认的配置文件来说明下具体的配置文件属性含义：

```nginx
#Nginx的worker进程运行用户以及用户组
#user  nobody;

#Nginx开启的进程数
worker_processes  1;

#定义全局错误日志定义类型，[debug|info|notice|warn|crit]
#error_log  logs/error.log;
#error_log  logs/error.log  notice;
#error_log  logs/error.log  info;

#指定进程ID存储文件位置
#pid        logs/nginx.pid;


#事件配置
events {
    
    
    #use [ kqueue | rtsig | epoll | /dev/poll | select | poll ];
    #epoll模型是Linux内核中的高性能网络I/O模型，如果在mac上面，就用kqueue模型。
    use kqueue;
    
    #每个进程可以处理的最大连接数，理论上每台nginx服务器的最大连接数为worker_processes*worker_connections。理论值：worker_rlimit_nofile/worker_processes
    worker_connections  1024;
}

#http参数
http {
    #文件扩展名与文件类型映射表
    include       mime.types;
    #默认文件类型
    default_type  application/octet-stream;
    
    #日志相关定义
    #log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
    #                  '$status $body_bytes_sent "$http_referer" '
    #                  '"$http_user_agent" "$http_x_forwarded_for"';
    
    #连接日志的路径，指定的日志格式放在最后。
    #access_log  logs/access.log  main;

    #开启高效传输模式
    sendfile        on;
    
    #防止网络阻塞
    #tcp_nopush     on;

    #客户端连接超时时间，单位是秒
    #keepalive_timeout  0;
    keepalive_timeout  65;

    #开启gzip压缩输出
    #gzip  on;

    #虚拟主机基本设置
    server {
        #监听的端口号
        listen       80;
        #访问域名
        server_name  localhost;
        
        #编码格式，如果网页格式与当前配置的不同的话将会被自动转码
        #charset koi8-r;

        #虚拟主机访问日志定义
        #access_log  logs/host.access.log  main;
        
        #对URL进行匹配
        location / {
            #访问路径，可相对也可绝对路径
            root   html;
            #首页文件，匹配顺序按照配置顺序匹配
            index  index.html index.htm;
        }
        
        #错误信息返回页面
        #error_page  404              /404.html;
        
        # redirect server error pages to the static page /50x.html
        #
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }
        
        #访问URL以.php结尾则自动转交给127.0.0.1
        # proxy the PHP scripts to Apache listening on 127.0.0.1:80
        #
        #location ~ \.php$ {
        #    proxy_pass   http://127.0.0.1;
        #}
        
        #php脚本请求全部转发给FastCGI处理
        # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
        #
        #location ~ \.php$ {
        #    root           html;
        #    fastcgi_pass   127.0.0.1:9000;
        #    fastcgi_index  index.php;
        #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
        #    include        fastcgi_params;
        #}

        #禁止访问.ht页面
        # deny access to .htaccess files, if Apache's document root
        # concurs with nginx's one
        #
        #location ~ /\.ht {
        #    deny  all;
        #}
    }

    #第二个虚拟主机配置
    # another virtual host using mix of IP-, name-, and port-based configuration
    #
    #server {
    #    listen       8000;
    #    listen       somename:8080;
    #    server_name  somename  alias  another.alias;

    #    location / {
    #        root   html;
    #        index  index.html index.htm;
    #    }
    #}


    #HTTPS虚拟主机定义
    # HTTPS server
    #
    #server {
    #    listen       443 ssl;
    #    server_name  localhost;

    #    ssl_certificate      cert.pem;
    #    ssl_certificate_key  cert.key;

    #    ssl_session_cache    shared:SSL:1m;
    #    ssl_session_timeout  5m;

    #    ssl_ciphers  HIGH:!aNULL:!MD5;
    #    ssl_prefer_server_ciphers  on;

    #    location / {
    #        root   html;
    #        index  index.html index.htm;
    #    }
    #}
    include servers/*;
}

```

-----

### 反向代理实例

假设我现在需要本地访问[www.baidu.com][1];配置如下：

```nginx
server {
    #监听80端口
    listen 80;
    server_name localhost;
     # individual nginx logs for this web vhost
    access_log /tmp/access.log;
    error_log  /tmp/error.log ;

    location / {
        proxy_pass http://www.baidu.com;
    }

```

验证结果：

![][0]

可以看到，我在浏览器中使用localhost打开了百度的首页...

-----

### 负载均衡实例

下面主要验证最常用的三种负载策略。虚拟主机配置：

```nginx
server {
    #监听80端口
    listen 80;
    server_name localhost;
    
    # individual nginx logs for this web vhost
    access_log /tmp/access.log;
    error_log  /tmp/error.log ;

    location / {
        #负载均衡
        #轮询 
        #proxy_pass http://polling_strategy;
        #weight权重
        #proxy_pass http://weight_strategy;
        #ip_hash
        # proxy_pass http://ip_hash_strategy;
        #fair
        # proxy_pass http://fair_strategy;
        #url_hash
        # proxy_pass http://url_hash_strategy;
        #重定向
        #rewrite ^ http://localhost:8080;
    }

```

-----
 **`轮询策略`** 

1、轮询（默认）

```nginx
# 每个请求按时间顺序逐一分配到不同的后端服务器，如果后端服务器down掉，能自动剔除。 
upstream polling_strategy { 
    server test.com:8080; # 应用服务器1
    server test.com:8081; # 应用服务器2
} 

```

测试结果（通过端口号来区分当前访问）：

```nginx
8081：This is 8081
8080：This is 8080
8081：This is 8081
8080：This is 8080

```

-----
 **`权重策略`** 

2、指定权重

指定轮询几率，weight和访问比率成正比，用于后端服务器性能不均的情况。

```nginx
upstream  weight_strategy { 
    server test.com:8080 weight=1; # 应用服务器1
    server test.com:8081 weight=9; # 应用服务器2
}

```

测试结果：总访问次数15次，根据上面的权重配置，两台机器的访问比重：2：13；满足预期。

-----
 **`ip hash策略`** 

3、IP绑定 ip_hash

```nginx
#每个请求按访问ip的hash结果分配，这样每个访客固定访问一个后端服务器，
#可以解决session的问题;在不考虑引入分布式session的情况下，
#原生HttpSession只对当前servlet容器的上下文环境有效
upstream ip_hash_strategy { 
    ip_hash; 
    server glmapper.net:8080; # 应用服务器1
    server glmapper.net:8081; # 应用服务器2
} 

```

* iphash 算法:ip是基本的点分十进制，将ip的前三个端作为参数加入hash函数。这样做的目的是保证ip地址前三位相同的用户经过hash计算将分配到相同的后端server。作者的这个考虑是极为可取的，因此ip地址前三位相同通常意味着来着同一个局域网或者相邻区域，使用相同的后端服务让nginx在一定程度上更具有一致性。

### 其他负载均衡策略

这里需要安装三方插件

-----

4、fair（第三方）

```nginx
#按后端服务器的响应时间来分配请求，响应时间短的优先分配。 
upstream fair_strategy { 
    server glmapper.net:8080; # 应用服务器1
    server glmapper.net:8081; # 应用服务器2
    fair; 
} 
```

5、url_hash（第三方）

```nginx
#按访问url的hash结果来分配请求，使每个url定向到同一个后端服务器，
#后端服务器为缓存时比较有效。 
upstream url_hash_strategy { 
    server glmapper.net:8080; # 应用服务器1
    server glmapper.net:8081; # 应用服务器2 
    hash $request_uri; 
    hash_method crc32; 
} 

```

第三方模块安装:编译安装

```
先构建好目录，这不是必须的，只是个人喜欢有条有序的管理
cd ~/myapp   # 存放我的应用程序
mkdir bin    # 存放编译出来的可执行命令
mkdir src    # 存放源文件
mkdir etc    # 存放配置文件
mkdir var    # 存放 log 文件和 pid 文件
cd src
#下载 nginx 安装包
wget http://nginx.org/download/nginx-1.13.12.tar.gz
# 下载 fair 模块
git clone https://github.com/itoffshore/nginx-upstream-fair
# 解压
tar zxvf nginx-1.13.12.tar.gz
cd nginx-1.13.12
# 安装配置，在这里通过--add-module添加第三方模块，比较重要，否则安装会失败
# 下面的配置三选一即可
# 比较完整的配置
./configure --prefix=/Users/ginkgo/myapp/etc/nginx --sbin-path=/Users/ginkgo/myapp/bin/nginx --conf-path=/Users/ginkgo/myapp/etc/nginx/nginx.conf --error-log-path=/Users/ginkgo/myapp/var/log/nginx/error.log --http-client-body-temp-path=/Users/ginkgo/myapp/var/lib/nginx/body --http-fastcgi-temp-path=/Users/ginkgo/myapp/var/lib/nginx/fastcgi --http-log-path=/Users/ginkgo/myapp/var/log/nginx/access.log --http-proxy-temp-path=/Users/ginkgo/myapp/var/lib/nginx/proxy --lock-path=/Users/ginkgo/myapp/var/lock/nginx.lock --pid-path=/Users/ginkgo/myapp/var/run/nginx.pid --with-debug --with-http_dav_module --with-http_flv_module --with-http_gzip_static_module --with-http_realip_module --with-http_stub_status_module --with-http_geoip_module --with-http_ssl_module --with-http_sub_module --with-ipv6 --with-mail --with-mail_ssl_module --with-openssl=/Users/ginkgo/myapp/src/openssl  --add-module=/Users/ginkgo/myapp/src/nginx-upstream-fair
# --with-openssl=/Users/ginkgo/myapp/src/openssl 指定 OpenSSL 的库路径

# 学习用不着那么多模块，用下面的就行，或者你也可以移除更多的模块
./configure --prefix=/Users/ginkgo/myapp/etc/nginx --sbin-path=/Users/ginkgo/myapp/bin/nginx --conf-path=/Users/ginkgo/myapp/etc/nginx/nginx.conf --error-log-path=/Users/ginkgo/myapp/var/log/nginx/error.log --http-client-body-temp-path=/Users/ginkgo/myapp/var/lib/nginx/body --http-fastcgi-temp-path=/Users/ginkgo/myapp/var/lib/nginx/fastcgi --http-log-path=/Users/ginkgo/myapp/var/log/nginx/access.log --http-proxy-temp-path=/Users/ginkgo/myapp/var/lib/nginx/proxy --lock-path=/Users/ginkgo/myapp/var/lock/nginx.lock --pid-path=/Users/ginkgo/myapp/var/run/nginx.pid --with-debug --with-http_gzip_static_module --with-http_realip_module --with-http_stub_status_module --with-http_ssl_module --with-http_sub_module --with-mail --with-mail_ssl_module --with-openssl=/Users/ginkgo/myapp/src/openssl  --add-module=/Users/ginkgo/myapp/src/nginx-upstream-fair 

# 只保留 fair 模块的配置
./configure --prefix=/Users/ginkgo/myapp/etc/nginx --sbin-path=/Users/ginkgo/myapp/bin/nginx --conf-path=/Users/ginkgo/myapp/etc/nginx/nginx.conf --error-log-path=/Users/ginkgo/myapp/var/log/nginx/error.log  --http-log-path=/Users/ginkgo/myapp/var/log/nginx/access.log  --pid-path=/Users/ginkgo/myapp/var/run/nginx.pid --with-debug  --add-module=/Users/ginkgo/myapp/src/nginx-upstream-fair 
# make 编译 安装
make && make install
cd /Users/ginkgo/myapp/bin
# -c 指定路径的配置文件启动，默认/Users/ginkgo/myapp/etc/nginx/nginx.conf
sudo ./nginx -c /Users/ginkgo/myapp/etc/nginx/nginx.conf
# 这里会报错/lib/nginx/body文件夹找不到，那就创建一下，再启动
# 修改了配置文件,热加载生效
sudo ./nginx -s reload
# 关闭
sudo ./nginx -s quit

```
### 重定向rewrite

```nginx
location / {
    #重定向
    #rewrite ^ http://localhost:8080;
}

```

验证思路：本地使用localhost:80端口进行访问，根据nginx的配置，如果重定向没有生效，则最后会停留在当前localhost:80这个路径，浏览器中的地址栏地址不会发生改变；如果生效了则地址栏地址变为localhost:8080。


[1]: http://www.baidu.com
[0]: ../img/3256507-0c9d025fee8c86d9.png
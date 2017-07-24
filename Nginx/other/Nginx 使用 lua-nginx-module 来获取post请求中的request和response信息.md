# Nginx 使用 lua-nginx-module 来获取post请求中的request和response信息
作者  [lework][0] 已关注 2016.11.10 21:35*  字数 268  阅读 1190 评论 0 喜欢 1

## 1. 软件版本

* 系统 centos6.7X86_64
* nginx 1.11.5
* lua-nginx-module 0.10.7
* PHP 5.6.27

## 2. 环境准备

配置yum仓库

    wget -O /etc/yum.repos.d/CentOS-Base.repo[https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/centos?codeblock=2](https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/centos?codeblock=2)
    wget -O /etc/yum.repos.d/epel.repo[https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/epel?codeblock=0](https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/epel?codeblock=0)
    wget -O /etc/yum.repos.d/epel-testing.repo[https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/epel?codeblock=1](https://lug.ustc.edu.cn/wiki/_export/code/mirrors/help/epel?codeblock=1)
    /usr/sbin/ntpdate  asia.pool.ntp.org

安装编译所需的依赖

    yum -y install gcc gcc-c++ make libtool zlib zlib-devel openssl openssl-devel pcre  pcre-devel

## 3. 软件安装

安装php5.6

    rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
    yum -y install php56w-cli php56w-mysql php56w-xml php56w-mbstring php56w-pdo php56w-bcmath php56w-mcrypt php56w-fpm

编译安装nginx

    cd /opt/software/
    wget https://codeload.github.com/openresty/lua-nginx-module/tar.gz/v0.10.7
    wget http://101.44.1.122/files/71920000037C1419/luajit.org/download/LuaJIT-2.0.4.tar.gz
    wget http://101.44.1.118/files/914700000603AA5C/nginx.org/download/nginx-1.11.5.tar.gz
    
    groupadd nginx
    useradd -g nginx -s /sbin/nologin nginx
    mkdir -p /var/tmp/nginx/client/
    mkdir -p /usr/local/nginx
    
    tar zxf lua-nginx-module-0.10.7.tar.gz 
    tar zxf LuaJIT-2.0.4.tar.gz 
    tar zxf nginx-1.11.5.tar.gz 
    
    cd LuaJIT-2.0.4/
    make && make install
    
    cat >> /etc/profile <<EOF
    export LUAJIT_LIB=/usr/local/lib
    export LUAJIT_INC=/usr/local/include/luajit-2.0
    export LD_LIBRARY_PATH=/usr/local/lib:$LD_LIBRARY_PATH
    EOF
    source /etc/profile
    
    cd ../nginx-1.11.5/
     ./configure   --prefix=/usr/local/nginx   --user=nginx   --group=nginx   --with-http_ssl_module   --with-http_flv_module   --with-http_stub_status_module   --with-http_gzip_static_module   --with-http_realip_module   --http-client-body-temp-path=/var/tmp/nginx/client/   --http-proxy-temp-path=/var/tmp/nginx/proxy/   --http-fastcgi-temp-path=/var/tmp/nginx/fcgi/   --http-uwsgi-temp-path=/var/tmp/nginx/uwsgi   --http-scgi-temp-path=/var/tmp/nginx/scgi   --with-pcre --add-module=../lua-nginx-module-0.10.7
    
    make -j2
    make install

## 4. 配置lua脚本

在nginx配置文件中添加下列location

```nginx
    # /usr/local/nginx/conf/nginx.conf
            location ~* ^/lua(/.*) {
                    default_type 'text/plain';
                   content_by_lua 'ngx.say("hello, lua")';
            }
```


启动nginx

    /usr/local/nginx/sbin/nginx

测试lua脚本执行

    curl http://127.0.0.1/lua/

返回 hello, lua 为正常

## 5. 配置php

启用如下选项

```nginx
    #/usr/local/nginx/conf/nginx.conf
    location / {
                root   /web;
                index  index.php index.html index.htm;
            }
    
    location ~ \.php$ {
                root           /web/;
                fastcgi_pass   127.0.0.1:9000;
                fastcgi_index  index.php;
                fastcgi_param  SCRIPT_FILENAME  /$document_root$fastcgi_script_name;
                include        fastcgi_params;
            }
```

在/web/目录下 新建index.php的测试页面，测试php是否能正常工作

    mkdir /web/
    cat > /web/info.php << EOF
    <?php
    phpinfo();
    ?>
    EOF
    chown nginx.nginx -R /web/

启动php-fpm和nginx

    /etc/init.d/php-fpm start
    /usr/local/nginx/sbin/nginx  -s reload

测试

    curl http://127.0.0.1/info.php

显示phpinfo页面，极为正常

## 6. 配置日志，记录post请求的request_body 和response_body

使用下列配置

```nginx
    # /usr/local/nginx/conf/nginx.conf
    user  nginx;
    worker_processes  1;
    
    events {
        worker_connections  1024;
    }
    
    http {
        include       mime.types;
        default_type  application/octet-stream;
    
        log_format postdata '$remote_addr | $request_body | $resp_body';
    
        sendfile        on;
        keepalive_timeout  65;
    
        server {
            listen       80;
            server_name  localhost;
            charset utf-8;
            set $resp_body "";
            location / {
                root   /web/;
                index  index.php index.html index.htm;
            }
    
    http {
        include       mime.types;
        default_type  application/octet-stream;
    
        log_format postdata '$remote_addr | $request_body | $resp_body';
    
        sendfile        on;
        keepalive_timeout  65;
    
        server {
            listen       80;
            server_name  localhost;
            charset utf-8;
            set $resp_body "";
            location / {
                root   /web/;
                index  index.php index.html index.htm;
            }
    
            location ~* ^/lua(/.*) {
                    default_type 'text/plain';
                    content_by_lua 'ngx.say("hello, lua")';
            }
    
            location ~ \.php$ {
                        root           /web/;
                        fastcgi_pass   127.0.0.1:9000;
                        fastcgi_index  index.php;
                        fastcgi_param  SCRIPT_FILENAME  /$document_root$fastcgi_script_name;
                        include        fastcgi_params;
                        access_log /tmp/nginx_access.log postdata;
                    lua_need_request_body on;
                    body_filter_by_lua '
                            local resp_body = string.sub(ngx.arg[1], 1, 1000)
                            ngx.ctx.buffered = (ngx.ctx.buffered or"") .. resp_body
                            if ngx.arg[2] then
                                    ngx.var.resp_body = ngx.ctx.buffered
                            end
                    ';
            }
        }
    }
```

创建php文件，用来接收post请求，并返回数据

    cat >> /web/index.php  <<EOF
    <?php
    header("Content-type:text/html;charset=utf-8"); 
    print_r(file_get_contents('php://input'));
    ?>
    EOF
    chown nginx.nginx /web/index.php

重启nginx

    /usr/local/nginx/sbin/nginx  -s reload

使用postman工具来发送post请求

![][1]



Paste_Image.png

查看日志

    cat  /tmp/nginx_access.log

![][2]



Paste_Image.png

已经记录post请求的request 和response数据了。

第二行是因为post数据有中文字符，所以变成了16进制。解决方法见 [解决nginx在记录post数据时 中文字符转成16进制的问题][3]

[0]: /u/ace85431b4bb
[1]: ../img/3629406-42b51ef7b121d677.png
[2]: ../img/3629406-3484725c836a316c.png
[3]: http://www.jianshu.com/p/8f8c2b5ca2d1
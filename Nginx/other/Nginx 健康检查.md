## Nginx 健康检查

来源：[http://blog.51cto.com/tchuairen/2287789](http://blog.51cto.com/tchuairen/2287789)

时间 2018-09-30 11:03:52


Nginx 的健康检查这块笔者在网上看了很多文章，基本都是零零散散的，讲各种实现方式，没有一篇能完整的讲当下的 Nginx 实现健康检查的几种方式，应该选哪一种来使用，于是笔者想总结一篇。


#### 一、目前 Nginx 支持两种主流的健康检查模式


#### 主动检查模式

Nginx 服务端会按照设定的间隔时间主动向后端的 upstream_server 发出检查请求来验证后端的各个 upstream_server 的状态。 如果得到某个服务器失败的返回超过一定次数，比如 3 次就会标记该服务器为异常，就不会将请求转发至该服务器。

一般情况下后端服务器需要为这种健康检查专门提供一个低消耗的接口。


#### 被动检查模式

Nginx 在代理请求过程中会自动的监测每个后端服务器对请求的响应状态，如果某个后端服务器对请求的响应状态在短时间内累计一定失败次数时，Nginx 将会标记该服务器异常。就不会转发流量给该服务器。 不过每间隔一段时间 Nginx 还是会转发少量的一些请求给该后端服务器来探测它的返回状态。 以便识别该服务器是否恢复。

后端服务器不需要专门提供健康检查接口，不过这种方式会造成一些用户请求的响应失败，因为 Nginx 需要用一些少量的请求去试探后端的服务是否恢复正常。


* 注：如果是采用 Nginx 被动检查模式，官方原生的 Nginx 就支持，不需要依赖第三方模块或技术，所以下面的探讨都是针对 Nginx 实现主动健康检查的方法
  


#### 二、目前使用 Nginx 实现健康检查的几种方式


#### 1.使用开源模块 nginx_upstream_check_module

源码地址：    [https://github.com/yaoweibin/nginx_upstream_check_module][0]

这是我目前找到的让原生 Nginx 通过添加开源模块，免费实现主动健康检查的唯一方法。 下面我会详细介绍这种方式的安装和配置过程


#### 2.使用商业版 Nginx Plus

[https://www.nginx.com/products/nginx/][1]

这种方法需要收费，可获得技术支持


#### 3.使用淘宝开源的 Tengine 代替 Nginx

[http://tengine.taobao.org][2]

这种方式也免费，可行。


#### 三、这里我们演示第一种方法的实现，使用开源模块 nginx_upstream_check_module

首先去下载该模块的源码包，放到要编译 Nginx 的服务器上；

操作系统环境：Centos6.8 ，这里默认已经安装好了编译所需的开发环境


#### 1.安装编译 Nginx 所需的软件包

```
yum install pcre pcre-devel openssl openssl-devel -y
```


#### 2.选择 Nginx 版本，编译安装（编译前记得给 Nginx 打对应补丁）


* 这里要认真看下，很关键：
    

这里 Nginx 选择：nginx-1.14.0.tar.gz ，nginx_upstream_check_module 源码就下载最新的主线代码包：nginx_upstream_check_module-master.zip 但是编译前补丁要选对应 Nginx 版本的。 比如这里 nginx-1.14.0 补丁要选择 check_1.14.0+.patch ； 补丁文件就在 nginx_upstream_check_module 源码包里面。

```
#!/bin/bash

tar xf nginx-1.14.0.tar.gz 
unzip nginx_upstream_check_module-master.zip

cd nginx-1.14.0

# 打补丁，注意编译前一定要有打补丁这步，不然添加的模块编译不生效
patch -p1 < /root/nginx_upstream_check_module-master/check_1.14.0+.patch

./configure --user=www --group=www --prefix=/alidata/server/nginx --with-http_stub_status_module --with-http_ssl_module --with-http_gzip_static_module --add-module=/root/nginx_upstream_check_module-master

make && make install
```


#### 3.配置和应用

```nginx
# nginx.conf

user  www www;
worker_processes  4;

worker_rlimit_nofile 65535;

events
{ 
  use epoll;
  worker_connections 65535;
}

http {

# 指定一个 upstream 负载均衡组，名称为 evalue
    upstream evalue {
    # 定义组内的节点服务，如果不加 weight 参数，默认就是 Round Robin ，加上了 weight 参数就是加权轮询
            server 192.168.90.100:9999 weight=100;
            server 192.168.90.101:9999 weight=100;
    # interval=3000 检查间隔 3 秒 ， rise=3 连续成功3次认为服务健康 ， fall=5 连续失败5次认为服务不健康 ， timeout=3000 健康检查的超时时间为 3 秒 ， type=http  检查类型 http
            check interval=3000 rise=3 fall=5 timeout=3000 type=http;
    # check_http_send 设定检查的行为：请求类型 url 请求协议 -> HEAD /api/v1/chivox/health HTTP/1.0         
            check_http_send "HEAD /api/v1/chivox/health HTTP/1.0\r\n\r\n";
    # 设定认为返回正常的响应状态       
            check_http_expect_alive http_2xx http_3xx;
            #check_http_send "GET /test3.html HTTP/2.0\r\n\r\n";
    }

}

server {
        listen     80;

        location / {
                proxy_pass http://evalue;
                keepalive_timeout 0;
        }

# 配置健康检查的状态监控页
# check_status [html|csv|json]
# 也可以在请求监控页的时候带上参数以输出不同的格式，/status?format=html | /status?format=csv | /status?format=json

        location /status {
                check_status html;
                access_log off;
        }

        location ~ /.svn/ {
        deny all;
        }
        access_log /alidata/log/nginx/access/evalue.log json;
}
```


[0]: https://github.com/yaoweibin/nginx_upstream_check_module
[1]: https://www.nginx.com/products/nginx/
[2]: http://tengine.taobao.org
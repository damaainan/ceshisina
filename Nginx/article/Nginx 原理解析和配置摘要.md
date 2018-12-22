## Nginx 原理解析和配置摘要

2018.12.11 20:55*

来源：[https://www.jianshu.com/p/bee833927783](https://www.jianshu.com/p/bee833927783)


## 前言

Nginx 作为高性能的 http 服务器，知名度不必多言，相似产品中无出其右。本篇随笔记录我认为较为重要的原理和配置。


![][0]

## 1. 原理解析
### 1.1 结构


![][1]


以上是 Nginx 的结构图，其包含一个 master 和 n 个 worker，master_processes 用于外部通信和统一管理其下 worker_processes ，因此可以做到重启时不中断服务。另外，Nginx 采用了异步非阻塞的方式来处理请求，避免了 cpu 闲置，这是其高性能的主要原由。

### 1.2 模块

Nginx 从功能上可分为以下三大类：


* Handlers（处理器模块）：用于直接处理请求，并进行输出内容和修改 headers 信息等操作，一般只能有一个。

* Filters（过滤器模块）：主要对处理器模块输出的内容进行修改操作，然后输出。

* Proxies（代理模块）：主要是 upstream 模块，与后端一些服务比如 FastCGI 等进行交互，实现服务代理和负载均衡等功能。


### 1.3 工作流程


![][2]


上图是 Nginx 常规的 HTTP 请求和响应过程，当接到请求时，通过查找配置文件将其映射到一个 location block，并按照其中所配置的各个指令，启动不同的模块去完成工作。通常一个 location 中的指令会涉及一个

handler 模块和多个 filter 模块。另外，Nginx 的模块属于静态编译方式，在启动后自动加载。

## 2. 配置摘要

Nginx 对于我来说最常规的运用就是静态资源处理和反向代理，因此我只记录这些相关的配置。Nginx 配置一般分为三部分：global、events 和 http，通用基本配置一般保存在`/etc/nginx/nginx.conf`文件中，具体的服务配置一般保存在`/etc/nginx/conf.d/`文件夹下。
### 2.1 Global 和 Events 配置

一般在`nginx.conf`配置文件的开头位置设置一些与具体业务无关的参数，如下：

```nginx
user  nginx; # 用户或者用户组
worker_processes  2; # worker 进程数,一般与服务器的虚拟内核数相等

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;

```

Events 中需要自行调整的就一个必要参数：worker_connections，这个数值涉及到最大连接数的计算，即：

```nginx
# nginx 作为 http 服务器的时候：
max_clients = worker_processes * worker_connections

# nginx作为反向代理服务器的时候：
max_clients = worker_processes * worker_connections/4

```

当然这个最大连接数还与系统可打开的最大文件数有关，max_clients 必须要小于 file-max（`cat /proc/sys/fs/file-max`），我的配置：

```nginx
events {
    worker_connections  2048;
}

```
### 2.2 http 服务器配置
#### 2.2.1 全局配置

一般情况下虚拟主机以外的配置保持默认就行了，如：gzip 压缩，ip 获取等一般都交给云服务器的负载均衡处理了，写一下默认值吧：

```nginx
http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile        on;
    #tcp_nopush     on;

    keepalive_timeout  65;

    #gzip  on;

    include /etc/nginx/conf.d/*.conf;
}

```
#### 2.2.2 server 虚拟主机配置

真正的与业务相关的配置都在这个小节，顺着流程来讲吧。现在不论是公司站还是个人站都应该普及了 https 了吧（运营商的 http 劫持实在太流氓了，特别是手机端的 web，再强调一遍，流氓，流氓...），这里面涉及到一个 https 强制跳转问题，可以让负载均衡的 80 端口来监听服务器的 81 端口进行重定向：

```nginx
server {
    listen   81;
    return   301 https://$host$request_uri;
}

```

如果是静态网页相关的配置，可以参考`default.conf`：

```nginx
server {
    listen       80;
    server_name  localhost;

    #charset koi8-r;
    #access_log  /var/log/nginx/host.access.log  main;

    location / {
        root   /usr/share/nginx/html;
        index  index.html index.htm;
    }

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /usr/share/nginx/html;
    }
}

```

Nginx 更多的情况是用作反向代理：

```nginx
server
{
    listen 80;
    server_name x.youclk.com;
    location / {
        proxy_pass http://x;
        # Proxy Settings
        # proxy_redirect     off;
        # proxy_set_header   Host             $host;
        # proxy_set_header   X-Real-IP        $remote_addr;
        # proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;
    }
}

server
{
    listen 80;
    server_name y.youclk.com;
    location / {
        proxy_pass http://y;
    }
}

```
## 3. 命令摘要

没啥好说的，不做特殊用途的话以下命令能够满足操作了：

```nginx
service nginx {start|stop|status|restart|reload|configtest}

```
## 结语

本篇随笔可以说是 Nginx 的一些基本摘要，使用和原理方面都没有深入探究，后续如果使用到更高级功能或者有新的应用场景，再来继续补充内容。

-----

我的公众号《有刻》，我们共同成长！


[0]: ../img/6215229-3324581439de11ff.png 
[1]: ../img/6215229-4271938e09ef6c07.png 
[2]: ../img/6215229-c999187458e950c6.png 

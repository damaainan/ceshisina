## nginx 详解 - 详细配置说明

来源：[https://juejin.im/post/5bff57246fb9a049be5d3297](https://juejin.im/post/5bff57246fb9a049be5d3297)

时间 2018-11-29 11:25:34

 
```
yum -y install gcc gcc-c++ autoconf pcre pcre-devel make automake
yum -y install wget httpd-tools vim
```
 
### 关闭 iptables
 
查看iptables规则

```
iptables -L
或
iptables -t nat -L
```
 
关闭 iptables 规则

```
iptables -F
或
iptables -t nat -F
```
 
### 关闭 SELinux
 
查看是否打开

```
getenforce
```
 
关闭

```
setenforce 0
```
 
## 二、Nginx 简介及安装
 
Nginx 是一个开源且高性能、高可靠的 HTTP 中间件、代理服务。
 
### 安装Nginx
 
打开官网[nginx.org/en/linux_pa…][2]
 
To set up the yum repository for RHEL/CentOS, create the file named`/etc/yum.repos.d/nginx.repo`with the following contents:

```
[nginx]
name=nginx repo
baseurl=http://nginx.org/packages/OS/OSRELEASE/$basearch/
gpgcheck=0
enabled=1
```
 
Replace “`OS`” with “`rhel`” or “`centos`”, depending on the distribution used, and “`OSRELEASE`” with “`6`” or “`7`”, for 6.x or 7.x versions, respectively.
 
## 三、安装目录及配置讲解
 
### 3.1 安装目录讲解
 
查看nginx的所有安装目录

```
rpm -ql nginx
```
 
然后得到如下配置

```
[root@ ~]# rpm -ql nginx

nginx日志轮转，用于logrotate服务的日志切割
/etc/logrotate.d/nginx

nginx主配置文件
/etc/nginx/nginx.conf
/etc/nginx
/etc/nginx/conf.d
/etc/nginx/conf.d/default.conf

cgi配置相关，fastcgi配置
/etc/nginx/fastcgi_params
/etc/nginx/scgi_params
/etc/nginx/uwsgi_params

编码转换映射转化文件
/etc/nginx/koi-utf
/etc/nginx/koi-win
/etc/nginx/win-utf

设置http协议的 Content-Type 与扩展名对应关系
/etc/nginx/mime.types


用于配置出系统守护进程管理器管理方式
/etc/sysconfig/nginx
/etc/sysconfig/nginx-debug
/usr/lib/systemd/system/nginx-debug.service
/usr/lib/systemd/system/nginx.service

nginx模块目录
/etc/nginx/modules
/usr/lib64/nginx/modules


/usr/lib64/nginx

/usr/libexec/initscripts/legacy-actions/nginx
/usr/libexec/initscripts/legacy-actions/nginx/check-reload
/usr/libexec/initscripts/legacy-actions/nginx/upgrade

nginx服务的启动管理的终端命令
/usr/sbin/nginx
/usr/sbin/nginx-debug

nginx的手册和帮助文件
/usr/share/doc/nginx-1.14.0
/usr/share/doc/nginx-1.14.0/COPYRIGHT
/usr/share/man/man8/nginx.8.gz


/usr/share/nginx
/usr/share/nginx/html
/usr/share/nginx/html/50x.html
/usr/share/nginx/html/index.html

nginx 的缓存目录
/var/cache/nginx

nginx日志目录
/var/log/nginx
```
 
### 3.2 安装编译参数
 
命令`nginx -V`查看所有编译参数
 
### 3.3 Nginx 默认配置语法
 
| 参数 | 说明 |
| - | - | 
| user | 设置nginx服务的系统使用用户 | 
| worker_processes | 工作进程数（一般与服务器核数保持一致） | 
| rror_log | nginx的错误日志 | 
| pid | nginx服务启动时候pid | 
| events -> worker_connections | 每个进程允许最大连接数 | 
| events -> use | 工作进程数 | 
 
 
#### nginx 的默认配置文件
 
文件路径`/etc/nginx/conf.d/default.conf`

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

    # proxy the PHP scripts to Apache listening on 127.0.0.1:80
    #

    #location ~ \.php$ {
    #    proxy_pass   http://127.0.0.1;
    #}

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    #location ~ \.php$ {
    #    root           html;
    #    fastcgi_pass   127.0.0.1:9000;
    #    fastcgi_index  index.php;
    #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
    #    include        fastcgi_params;
    #}

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    #location ~ /\.ht {
    #    deny  all;
    #}
}
```
 
可以去`/usr/share/nginx/html/index.html`修改默认的展示页面，也可以去`/usr/share/nginx/html/50x.html`修改错误页面。
 
修改后重启 nginx

```
systemctl reload nginx.service
或
systemctl restart nginx.service
```
 
检查 nginx 配置，结果出现 successful 表示成功

```
nginx -t -c /etc/nginx/nginx.conf
```
 
重新加载配置

```
nginx -s reload -c /etc/nginx/nginx.conf
```
 
## 四、常见 Nginx 中间架构

 
* 静态资源WEB服务 
* 代理服务 
* 负载均衡调度器 SLB 
* 动态缓存 
 
 
### 4.1 静态资源WEB服务
 
 ![][0]
 
 ![][1]
 
#### 配置语法-文件读取

```
Syntax: sendfile on|off;
Default: sendfile off;
Context: http,server,location,if in location
```
 
引读：--with-file-aio 异步文件读取
 
#### 配置语法- tcp_nopush

```
Syntax: tcp_nopush on|off;
Default: tcp_nopush off;
Context: http,server,location
```
 
#### 配置语法- tcp_nodelay

```
Syntax: tcp_nodelay on|off;
Default: tcp_nodelay on;
Context: http,server,location
```
 
#### 配置语法- 压缩

```
Syntax: gzip_comp_level level;
Default: gzip_comp_level 1;
Context: http,server,location
```

```
Syntax: gzip_http_version 1.0|1.1;
Default: gzip_http_version 1.1;
Context: http,server,location
```
 
#### 扩展 Nginx 压缩模块

```nginx
预读 gzip 功能
http_gzip_static_module

应用支持 gunzip 的压缩方式
http_gunzip_module
```
 
#### 浏览器缓存设置
 
配置语法 - expires
 
添加 Cache-Control、Expires 头

```
Syntax：expires [modified] time;
		expires epoch | max | off
Default: expires off;
Context: http, server, location, if in location
```
 
#### 跨域
 `*`表示允许所有的网站跨域，为了安全起见可以设置仅需要的网址

```nginx
location ~ .*\.(htm|html)$ {
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods GET,POST,PUT,DELETE,OPTIONS;
    root /opt/app/code
}
```
 
#### 基于 http_refer 防盗链配置模块

```
Syntax: valid_referers none | blocked | server_names | string...;
Default: -
Context: server, location,
```
 
### 4.2 代理服务
 
正向代理与反向代理的区别在于代理的对象不一样

 
* 正向代理代理的对象是客户端
  
* 反向代理代理的对象是服务端

 
配置语法

```
Syntax: proxy_pass URL
Default: -
Context: location,if in location,limit_except
```
 
URL 一般是以下三种

```nginx
http://localhost:8080/uri/
https://192.168.1.1:8000/uri/
http://unix:/tmp/backend.socket:/uri/;
```
 
### 4.3 负载均衡
 
#### HttpIndex模块
 
这个模块提供一个简单方法来实现在轮询和客户端IP之间的后端服务器负荷平衡。
 
配置范例：

```nginx
resolver 10.0.0.1;

upstream dynamic {
    zone upstream_dynamic 64k;
    
    hash $request_uri;  #按照url的hash值来分配，同一个url分配到同一个服务器

    server backend1.example.com      weight=5;
    server backend2.example.com:8080 fail_timeout=5s slow_start=30s;
    server 192.0.2.1                 max_fails=3;
    server backend3.example.com      resolve;
    server backend4.example.com      service=http resolve;

    server backup1.example.com:8080  backup;
    server backup2.example.com:8080  backup;
}

server {
    location / {
        proxy_pass http://dynamic;
        health_check;
    }
}
```
 
状态解释
 
| 配置 | 说明 |
| - | - | 
| down | 当前的server暂时不参与负载均衡 | 
| backup | 预留的备份服务器 | 
| max_fails | 允许请求失败的次数 | 
| fail_timeout | 经过max_fails 失败后，服务暂停的时间 | 
| max_conns | 限制最大的接收的连接数 | 
 
 
#### 调度算法
 
| 配置 | 说明 |
| - | - | 
| 轮询 | 按时间顺序逐一分配到不停的后端服务器 | 
| 加权轮询 | weight值越大，分配到的访问几率越高 | 
| ip_hash | 每个请求按照访问IP的hash结果分配，这样来自同一个ip固定访问一个后端服务器 | 
| url_hash | 按照访问的URL的hash结果来分配请求，使每个URL定向到同一个后端服务器 | 
| least_conn | 最少连接数，哪个机器连接数少就分发 | 
| hash关键数值 | hash自定义的key | 
 
 
### 4.4 缓存
 
缓存类型分类：客户端缓存，代理缓存，服务端缓存
 
proxy_cache

```
Syntax:	proxy_cache zone | off;
Default:	
proxy_cache off;
Context:	http, server, location
```
 
proxy_cache_path

```
Syntax:	proxy_cache_path path [levels=levels] [use_temp_path=on|off] keys_zone=name:size [inactive=time] [max_size=size] [manager_files=number] [manager_sleep=time] [manager_threshold=time] [loader_files=number] [loader_sleep=time] [loader_threshold=time] [purger=on|off] [purger_files=number] [purger_sleep=time] [purger_threshold=time];
Default:	—
Context:	http
```
 
实例

```nginx
proxy_cache_path /data/nginx/cache levels=1:2 keys_zone=cache_zone:10m max_size=10g inactive=60m use_temp_path=off;

map $request_method $purge_method {
    PURGE   1;
    default 0;
}

server {
    ...
    location / {
        proxy_pass http://backend;
        proxy_cache cache_zone;
        proxy_cache_key $uri;
        proxy_cache_purge $purge_method;
        # 当分配的服务器出现50X 错误时分配另一台服务器
        proxy_next_upstream error timeout invalid_header http_500 http_502 http_503 http_504
    }
}
```
 
## 五、Nginx深度学习
 
### 5.1 动静分离

```nginx
upstream java_api{
    server 127.0.0.1:8080;
}
server {
    ...
    #匹配到jsp结尾的请求去请求服务器
    location ~ \.jsp$ {
        proxy_pass http://java_api;
        index index.html index.htm;
    }
    
    #匹配到图片资源返回本地的内容
    location ~ \.(jpg|png|gif)$ {
        expires 1h;
        gzip on;
    }
}
```
 
### 5.2 Nginx 的 rewrite规则
 
作用：实现 url 重写以及重定向
 
使用场景：

 
* URL 访问跳转，支持开发设计
​ 页面跳转、兼容性支持、展示效果等
  
* SEO优化
  
* 维护。后台维护、流量转发等
  
* 安全

 
语法

```
Syntax:	rewrite regex replacement [flag];
Default:	—
Context:	server, location, if
```
 
If the specified regular expression matches a request URI, URI is changed as specified in the`*replacement*`string. The`rewrite`directives are executed sequentially in order of their appearance in the configuration file. It is possible to terminate further processing of the directives using flags. If a replacement string starts with “`http://`”, “`https://`”, or “`$scheme`”, the processing stops and the redirect is returned to a client.
 
An optional`*flag*`parameter can be one of:

 
* `last`停止rewrite检测
stops processing the current set of`ngx_http_rewrite_module`directives and starts a search for a new location matching the changed URI;
  
* `break`停止rewrite检测
stops processing the current set of`ngx_http_rewrite_module`directives as with thebreak directive;
  
* `redirect`返回302临时重定向，地址栏会显示跳转后的地址
returns a temporary redirect with the 302 code; used if a replacement string does not start with “`http://`”, “`https://`”, or “`$scheme`”;
  
* `permanent`返回302永久重定向，地址栏会显示跳转后的地址
returns a permanent redirect with the 301 code.

 
The full redirect URL is formed according to the request scheme (`$scheme`) and the[server_name_in_redirect][3] andport_in_redirect directives.
 
Example:

```nginx
server {
    ...
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 last;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  last;
    return  403;
    ...
}
```
 
But if these directives are put inside the “`/download/`” location, the`last`flag should be replaced by`break`, or otherwise nginx will make 10 cycles and return the 500 error:

```nginx
location /download/ {
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 break;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  break;
    return  403;
}
```
 
If a`*replacement*`string includes the new request arguments, the previous request arguments are appended after them. If this is undesired, putting a question mark at the end of a replacement string avoids having them appended, for example:

```nginx
rewrite ^/users/(.*)$ /show?user=$1? last;
```
 
If a regular expression includes the “`}`” or “`;`” characters, the whole expressions should be enclosed in single or double quotes.
 
### 5.3 安全校验 secure_link
 
指定并允许检查请求的链接的真实性以及保护资源免遭未授权的访问
 
限制链接生效周期

```
Syntax:	secure_link expression;
Default:	—
Context:	http, server, location

Syntax:	secure_link_md5 expression;
Default:	—
Context:	http, server, location
```
 
Example:

```nginx
location /s/ {
    secure_link $arg_md5,$arg_expires;
    secure_link_md5 "$secure_link_expires$uri$remote_addr secret";

    if ($secure_link = "") {
        return 403;
    }

    if ($secure_link = "0") {
        return 410;
    }

    ...
}
```
 
### 5.3 geoip_module 模块
 
基于 IP 地址匹配 MaxMind GeoIP 二进制文件，读取 IP 所在地域信息
 
安装：`yum install nginx-module-geoip`使用场景

 
* 区别国内外做HTTP 访问规则 
* 区别国内城市地域做 HTTP 访问规则 
 
 
### 5.4 配置 HTTPS

```nginx
server {
    listen              443 ssl;
    server_name         www.example.com;
    ssl_certificate     www.example.com.crt;
    ssl_certificate_key www.example.com.key;
    ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers         HIGH:!aNULL:!MD5;
    ...
}
```
 
#### HTTPS 服务优化

 
* 激活 keepalive 长连接 
* 设置 ssl session 缓存 
 

```nginx
server{
    listen 		 443;
    server_name  116.62.103.228 jeson.t.imooc.io;
    keepalive_timeout  100;
    
    ssl on;
    ssl_session_cache  shared:SSL:10m;
    ssl_session_timeout  10m;
    
    ssl_certificate     www.example.com.crt;
    ssl_certificate_key www.example.com.key;
    
    index index.html index.htm;
    location / {
        root /opt/app/code;
    }
}
```
 
### 5.5 Nginx 与 Lua 开发
 
Lua 是一个简洁、轻量、可扩展的脚本语言
 
Nginx + Lua 优势：充分的结合 Nginx 的并发处理 epoll 优势和 Lua 的轻量实现简单的功能且高并发的场景。
 
#### 安装

```
yum install lua
```
 
#### 运行 lua 有两种方式：命令行和脚本

 
* 命令行模式

```
在命令行输入 lua 开启命令行交互模式
```
  
* 脚本模式
编写`test.lua`文件，执行`lua test.lua`运行

 
#### 注释

```
-- 行注释
--[[
    块注释
--]]
```
 
## 六、Nginx 常见问题
 
#### 多个 server_name 的优先级
 
如果多个文件配置有相同的 server_name ，根据文件名先读取到哪个文件就加载哪个文件的配置
 
#### location 匹配优先级

```
=  进行普通字符精确匹配，也就是完全匹配
^~ 表示普通字符匹配，使用前缀匹配
~ \~*  表示执行一个正则匹配()
```
 
前两种匹配到之后就不往下继续匹配了，最后一种会继续往下匹配，如果没有匹配到，就用它的匹配。也就是前两种优先级比第三种高。
 
#### try_files 的使用
 
按顺序检查文件是否存在

```nginx
location / {
    try_files $uri $uri/ /index.php;
}
```
 
## 七、Nginx 性能优化
 
### 7.1 文件句柄
 
文件句柄：linux\Unix 一切皆文件，文件句柄就是一个索引
 
设置方式：系统全局性修改，用户局部性修改，进程局部性修改
 
修改方法：
 
#### 系统全局修改和针对用户修改

```
vim /etc/security/limits.conf
```
 
加入以下代码

```
# 给root用户设置
root soft nofile 10000
root hard nofile 10000
# 给所有用户全局设置
*    soft nofile 10000
*    hard nofile 10000
```
 
soft 不是强制性的，超过设置的值会提醒但不强制执行；hard 会强制执行
 
#### 针对进程修改

```
vim /etc/nginx/nginx.conf
```
 
添加以下代码

```nginx
worker_rlimit_nofile 20000
```
 
### 7.2 CPU 亲和
 
查看当前服务器的 CPU 个数

```
cat /proc/cpuinfo | grep "physical id"|sort|uniq|wc -l
```
 
查看 CPU 核数

```
cat /proc/cpuinfo | grep "cpu cores"|uniq
```
 
worker_processes = CPU 个数 * CPU 核数
 
假如有 2 个 CPU，每个 CPU 有 8 核，那`worker_processes`应该是16
 
打开 nginx 配置文件`vim /etc/nginx/nginx.conf`

```nginx
worker_processes  16;
worker_cpu_affinity  auto;
```
 
然后刷新nginx配置`nginx -s reload -c /etc/nginx/nginx.conf`### 7.3 Nginx 的通用配置

```nginx
user  nginx;
worker_processes  1;
worker_cpu_affinity auto;

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;

worker_rlimit_nofile 10000;

events {
    use epoll;
    worker_connections  1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    charset utf-8;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile        on;
    #tcp_nopush     on;

    keepalive_timeout  65;

    gzip  on;
    gzip_disable  "MSIE [1-6]\.";
    gzip_http_version 1.1;

    include /etc/nginx/conf.d/*.conf;
}
```
 
## 八、基于 Nginx 架构的安全
 
### 8.1 常见的恶意行为及防范手段
 
常见恶意行为：爬虫行为和恶意抓取、资源盗用
 
#### 常用防范手段：
 
基础防盗链功能：目的不让恶意用户轻易爬取网站数据
 
secure_link_module: 提高数据安全性，对数据增加加密验证和失效性，适合核心重要数据
 
access_module: 对后台、部分用户服务的数据提供 IP 防控
 
### 8.2 常见的攻击手段
 
#### 后台密码撞库
 
通过猜测密码字段不断对后台系统登录性尝试，获取后台登录密码
 
防范手段：

 
* 后台登录密码复杂度 
* access_module 对后台提供 IP 防控 
* 预警机制 
 
 
#### 文件上传漏洞

```nginx
location ^~ /upload{
    root /opt/app/images;
    if($requst_filename ~*(.*)\.php){
        return 403;
    }
}
```
 
#### SQL 注入
 
利用未过滤/未审核用户输入的攻击方法，让应用运行本不应该运行的 SQL 代码
 
Nginx + Lua 防火墙实现：[github.com/loveshell/n…][4]
 
以上就是 Nginx 学习笔记的全部内容。
 
作者正在组织写一个有趣的开源项目`coderiver`，致力于打造全平台型全栈精品开源项目。
 `coderiver`中文名 河码，是一个为程序员和设计师提供项目协作的平台。无论你是前端、后端、移动端开发人员，或是设计师、产品经理，都可以在平台上发布项目，与志同道合的小伙伴一起协作完成项目。
 `coderiver`河码 类似程序员客栈，但主要目的是方便各细分领域人才之间技术交流，共同成长，多人协作完成项目。暂不涉及金钱交易。
 
计划做成包含 pc端（Vue、React）、移动H5（Vue、React）、ReactNative混合开发、Android原生、微信小程序、Angular、Node、Flutter、java后端的全平台型全栈项目，欢迎关注。
 
目前已经组建了几十人的研发团队，将致力于为大家提供最优质的开源项目。
 
项目地址：[github.com/cachecats/c…][5]
 
您的鼓励是我前行最大的动力，欢迎点赞，欢迎送小星星:sparkles: ~


[2]: https://link.juejin.im?target=https%3A%2F%2Fnginx.org%2Fen%2Flinux_packages.html%23stable
[3]: https://link.juejin.im?target=http%3A%2F%2Fnginx.org%2Fen%2Fdocs%2Fhttp%2Fngx_http_core_module.html%23server_name_in_redirect
[4]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Floveshell%2Fngx_lua_waf
[5]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fcachecats%2Fcoderiver
[0]: https://img2.tuicool.com/Q36Vjuy.png 
[1]: https://img0.tuicool.com/jARVz2R.png 
## 前端nginx使用札记

来源：[https://segmentfault.com/a/1190000013781162](https://segmentfault.com/a/1190000013781162)


## nginx是什么？


nginx是俄罗斯人 Igor Sysoev为俄罗斯访问量第二的Rambler.ru站点开发的一个十分轻量级的HTTP服务器。它是一个高性能的HTTP和反向代理服务器，同时也可以作为IMAP/POP3/SMTP的代理服务器。nginx使用的是BSD许可。


Nginx 以事件驱动的方式编写，所以有非常好的性能，同时也是一个非常高效的反向代理、负载平衡。


Nginx 因为它的稳定性、丰富的模块库、灵活的配置和低系统资源的消耗而闻名。


nginx适合用来做mongrel clusters 的前端 HTTP 响应。
## 为什么要用nginx，nginx有什么特点？


[nginx的特点][3]：



* 核心特点：高并发请求的同时保持高效的服务

* 热部署

* 低内存消耗

* 处理响应请求很快

* 具有很高的可靠性



同时，nginx也可以实现高效的反向代理、负载均衡。


前端可以用nginx做些什么？



* 搭建静态资源服务器

* 反向代理分发后端服务（可以和nodejs搭配实现前后端分离）和跨域问题

* 根据User Agent来重定向站点

* 开发环境或测试环境切换（切换host）

* url重写，使用rewrie规则本地映射

* 资源内容篡改

* 获取cookie做分流

* 资源合并

* gzip压缩

* 压缩图片

* sourceMap调试


## 如何安装nginx？
### mac安装：


安装[brew][4]之后，执行命令：

```
$ sudo brew install nginx
```
### windows安装


1.下载： [nginx官网][5]



* 解压运行：解压至`c:\nginx`，运行`nginx.exe`(即`nginx -c conf\nginx.conf`)，默认使用80端口，日志见文件夹`C:\nginx\logs`

* 关闭：`nginx -s stop`或`taskkill /F /IM nginx.exe > nul`


 【注意】以下皆以mac为例。 
## nginx如何启动、重启、关闭？


查看nginx版本：`nginx -v`启动nginx服务：
方法一：运行命令：`sudo brew services start nginx`
方法二：运行命令：`nginx`
访问[http://localhost][6]:8080
出现如下界面则表示安装成功：


![][0]


关闭nginx服务：
方法一：运行命令：` sudo brew services stop nginx`
方法二：运行命令：`nginx -s stop`
方法三：
运行命令：`ps -ef | grep nginx`，找到master对应的进程号。
快速停止：`kill -TERM nginx进程号`或`kill -INT nginx进程号`
从容停止：`kill -QUIT nginx进程号`
强制停止所有nginx进程：`pkill -9 nginx`重启nginx服务：
方法一：`nginx -s reload`
方法二： 平滑重启命令：`kill -HUP nginx进程号`nginx信号控制：



* `TERM,INT`快速关闭

* `QUIT`从容关闭

* `HUP`平滑重启，重新加载配置文件

* `USR1`重新打开日志文件，在切割日志时用途较大

* `USR2`平滑升级可执行程序

* `WINCH`从容关闭工作进程


## 如何查看nginx的配置文件nginx.conf的路径和安装路径？


查看配置文件位置和测试配置文件语法：运行命令`nginx -t`:


![][1] 
查看nginx安装路径：
因为是使用brew安装的，所以使用brew命令：`brew info nginx`:


![][2]
## nginx.conf基本配置有哪些？


nginx配置文件主要分成四个部分：



* main，全局设置，影响其它部分所有设置

* server，主机服务相关设置，主要用于指定虚拟主机域名、IP和端口

* location，URL匹配特定位置后的设置，反向代理、内容篡改相关设置

* upstream，上游服务器设置，负载均衡相关配置



他们之间的关系式：server继承main，location继承server；upstream既不会继承指令也不会被继承。


如下是一份通用的配置和详解：

```nginx
#定义 Nginx 运行的用户和用户组,默认由 nobody 账号运行, windows 下面可以注释掉。 
user  nobody; 

#nginx进程数，建议设置为等于CPU总核心数。可以和worker_cpu_affinity配合
worker_processes  1; 

#全局错误日志定义类型，[ debug | info | notice | warn | error | crit ]
#error_log  logs/error.log;
#error_log  logs/error.log  notice;
#error_log  logs/error.log  info;

#进程文件，window下可以注释掉
#pid        logs/nginx.pid;

# 一个nginx进程打开的最多文件描述符(句柄)数目，理论值应该是最多打开文件数（系统的值ulimit -n）与nginx进程数相除，
# 但是nginx分配请求并不均匀，所以建议与ulimit -n的值保持一致。
worker_rlimit_nofile 65535;

#工作模式与连接数上限
events {
    # 参考事件模型，use [ kqueue | rtsig | epoll | /dev/poll | select | poll ]; 
    # epoll模型是Linux 2.6以上版本内核中的高性能网络I/O模型，如果跑在FreeBSD上面，就用kqueue模型。
   #use epoll;
   #connections 20000;  # 每个进程允许的最多连接数
   # 单个进程最大连接数（最大连接数=连接数*进程数）该值受系统进程最大打开文件数限制，需要使用命令ulimit -n 查看当前设置
   worker_connections 65535;
}

#设定http服务器
http {
    #文件扩展名与文件类型映射表
    #include 是个主模块指令，可以将配置文件拆分并引用，可以减少主配置文件的复杂度
    include       mime.types;
    #默认文件类型
    default_type  application/octet-stream;
    #charset utf-8; #默认编码

    #定义虚拟主机日志的格式
    #log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
    #                  '$status $body_bytes_sent "$http_referer" '
    #                  '"$http_user_agent" "$http_x_forwarded_for"';
    
    #定义虚拟主机访问日志
    #access_log  logs/access.log  main;

    #开启高效文件传输模式，sendfile指令指定nginx是否调用sendfile函数来输出文件，对于普通应用设为 on，如果用来进行下载等应用磁盘IO重负载应用，可设置为off，以平衡磁盘与网络I/O处理速度，降低系统的负载。注意：如果图片显示不正常把这个改成off。
    sendfile        on;
    #autoindex on; #开启目录列表访问，合适下载服务器，默认关闭。

    #防止网络阻塞
    #tcp_nopush     on;

    #长连接超时时间，单位是秒，默认为0
    keepalive_timeout  65;

    # gzip压缩功能设置
    gzip on; #开启gzip压缩输出
    gzip_min_length 1k; #最小压缩文件大小
    gzip_buffers    4 16k; #压缩缓冲区
    gzip_http_version 1.0; #压缩版本（默认1.1，前端如果是squid2.5请使用1.0）
    gzip_comp_level 6; #压缩等级
    #压缩类型，默认就已经包含text/html，所以下面就不用再写了，写上去也不会有问题，但是会有一个warn。
    gzip_types text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml;
    gzip_vary on; //和http头有关系，加个vary头，给代理服务器用的，有的浏览器支持压缩，有的不支持，所以避免浪费不支持的也压缩，所以根据客户端的HTTP头来判断，是否需要压缩
    #limit_zone crawler $binary_remote_addr 10m; #开启限制IP连接数的时候需要使用

    # http_proxy服务全局设置
    client_max_body_size   10m;
    client_body_buffer_size   128k;
    proxy_connect_timeout   75;
    proxy_send_timeout   75;
    proxy_read_timeout   75;
    proxy_buffer_size   4k;
    proxy_buffers   4 32k;
    proxy_busy_buffers_size   64k;
    proxy_temp_file_write_size  64k;
    proxy_temp_path   /usr/local/nginx/proxy_temp 1 2;

   # 设定负载均衡后台服务器列表 
    upstream  backend.com  { 
        #ip_hash; # 指定支持的调度算法
        # upstream 的负载均衡，weight 是权重，可以根据机器配置定义权重。weigth 参数表示权值，权值越高被分配到的几率越大。
        server   192.168.10.100:8080 max_fails=2 fail_timeout=30s ;  
        server   192.168.10.101:8080 max_fails=2 fail_timeout=30s ;  
    }

    #虚拟主机的配置
    server {
        #监听端口
        listen       80;
        #域名可以有多个，用空格隔开
        server_name  localhost fontend.com;
        # Server Side Include，通常称为服务器端嵌入
        #ssi on;
        #默认编码
        #charset utf-8;
        #定义本虚拟主机的访问日志
        #access_log  logs/host.access.log  main;
        
        # 因为所有的地址都以 / 开头，所以这条规则将匹配到所有请求
        location / {
            root   html;
            index  index.html index.htm;
        }
        
        #error_page  404              /404.html;

        # redirect server error pages to the static page /50x.html
        #
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }

       # 图片缓存时间设置
       location ~ .*.(gif|jpg|jpeg|png|bmp|swf)$ {
          expires 10d;
       }

       # JS和CSS缓存时间设置
       location ~ .*.(js|css)?$ {
          expires 1h;
       }

        #代理配置
        # proxy the PHP scripts to Apache listening on 127.0.0.1:80
        #location /proxy/ {
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
}

```
### location如何匹配？


示例：

```nginx
location  = / {
  # 精确匹配 / ，主机名后面不能带任何字符串
  [ configuration A ] ;
}

location  / {
  # 因为所有的地址都以 / 开头，所以这条规则将匹配到所有请求
  # 但是正则和最长字符串会优先匹配
  [ configuration B ] ;
}

location /documents/ {
  # 匹配任何以 /documents/ 开头的地址，匹配符合以后，还要继续往下搜索
  # 只有后面的正则表达式没有匹配到时，这一条才会采用这一条
  [ configuration C ] ;
}

location ~ /documents/Abc {
  # 匹配任何以 /documents/ 开头的地址，匹配符合以后，还要继续往下搜索
  # 只有后面的正则表达式没有匹配到时，这一条才会采用这一条
  [ configuration CC ] ;
}

location ^~ /images/ {
  # 匹配任何以 /images/ 开头的地址，匹配符合以后，停止往下搜索正则，采用这一条。
  [ configuration D ] ;
}

location ~* \.(gif|jpg|jpeg)$ {
  # 匹配所有以 gif,jpg或jpeg 结尾的请求
  # 然而，所有请求 /images/ 下的图片会被 config D 处理，因为 ^~ 到达不了这一条正则
  [ configuration E ] ;
}

location /images/ {
  # 字符匹配到 /images/，继续往下，会发现 ^~ 存在
  [ configuration F ] ;
}

location /images/abc {
  # 最长字符匹配到 /images/abc，继续往下，会发现 ^~ 存在
  # F与G的放置顺序是没有关系的
  [ configuration G ] ;
}

location ~ /images/abc/ {
  # 只有去掉 config D 才有效：先最长匹配 config G 开头的地址，继续往下搜索，匹配到这一条正则，采用
    [ configuration H ] ;
}

location ~* /js/.*/\.js
```



* 以`=`开头表示精确匹配

* `^~`开头表示uri以某个常规字符串开头，不是正则匹配

* `~ `开头表示区分大小写的正则匹配;

* `~*`开头表示不区分大小写的正则匹配

* `/`通用匹配, 如果没有其它匹配,任何请求都会匹配到



优先级：
(location `=`) > (location `完整路径`) > (location `^~ 路径`) > (location `~`,`~*` `正则顺序`) > (location `部分起始路径`) > (`/`)
### 如何配置反向代理？


详解：

```nginx
# 对 “/” 启用反向代理
location / {
  proxy_pass http://127.0.0.1:3000;  # 设置要代理的 uri，注意最后的 /。可以是 Unix 域套接字路径，也可以是正则表达式。
  proxy_redirect off; # 设置后端服务器“Location”响应头和“Refresh”响应头的替换文本
  proxy_set_header X-Real-IP $remote_addr; # 获取用户的真实 IP 地址
  #后端的Web服务器可以通过 X-Forwarded-For 获取用户真实IP，多个 nginx 反代的情况下，例如 CDN。参见：http://gong1208.iteye.com/blog/1559835 和 http://bbs.linuxtone.org/thread-9050-1-1.html
  proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  #以下是一些反向代理的配置，可选。
  proxy_set_header Host $host; # 允许重新定义或者添加发往后端服务器的请求头。

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
```


举例：

```nginx
location ^~ /service/ {
  proxy_pass http://192.168.60.245:8080/;
  proxy_redirect      default;
  proxy_set_header    Host $host
  proxy_set_header    X-Real-IP $remote_addr;
  proxy_set_header    X-Forwarded-For  $proxy_add_x_forwarded_for;
}
```


简化：

```nginx
location /proxy/ {
  proxy_pass http://backend.com/;
  proxy_redirect      default;
}
```
### 如何配置rewrite？


rewrite功能就是集合正则表达式和标志位实现url重写和重定向。rewrite只能放在server{}、location{}、if(){}块中，并且只能对域名后边的出去传递参数外的字符串起作用。如URL：
`http://microloan-sms-platform.yxapp.xyz/proxy/sms/task/querydeleted?page=1&pagesize=10`
只对/proxy/sms/task/querydeleted进行重写。


如果相对域名或参数字符串起作用，可以使用全局变量匹配，也可以使用proxy_pass反向代理。


表明看rewrite和location功能有点像，都能实现跳转，主要区别在于rewrite是在同一域名内更改获取资源的路径，而location是对一类路径做控制访问或反向代理，可以proxy_pass到其他机器。很多情况下rewrite也会写在location里，它们的执行顺序是：



* 执行server块的rewrite指令

* 执行location匹配

* 执行选定的location中的rewrite指令



如果其中某步URI被重写，则重新循环执行1-3，直到找到真实存在的文件；循环超过10次，则返回500 Internal Server Error错误。


rewrite规则后边，通常会带有flag标志位：



* `last`: 相当于Apache的[L]标记，表示完成rewrite

* `break`: 停止执行当前虚拟主机的后续rewrite指令集

* `redirect`: 返回`302`临时重定向，地址栏会显示跳转后的地址

* `permanent`: 返回`301`永久重定向，地址栏会显示跳转后的地址


`last`和`break`区别：



* `last`一般写在`server`和`if`中，而`break`一般使用在`location`中

* `last`不终止重写后的url匹配，即新的url会再从`server`走一遍匹配流程，而`break`终止重写后的匹配

* `break`和`last`都能组织继续执行后面的rewrite指令



rewrite常用正则：



* `.`： 匹配除换行符以外的任意字符

* `?`： 重复0次或1次

* `+`： 重复1次或更多次

* `*`： 重复0次或更多次

* `\d`：匹配数字

* `^`： 匹配字符串的开始

* `$`： 匹配字符串的介绍

* `{n}`： 重复n次

* `{n,}`： 重复n次或更多次

* `[c]`： 匹配单个字符c

* `[a-z]`： 匹配a-z小写字母的任意一个



可以使用`()`来进行分组，可以通过`$1`的形式来引用。


示例：

```nginx
location /proxy/ {
        proxy_pass http://microloan-notification-web.yxapp.in;
        rewrite /proxy/(.*)$ /$1 break;
}
```
### 如何配置负载均衡？


示例：

```nginx
upstream test.net{
   ip_hash;
   server 192.168.11.1:80;
   server 192.168.11.11:80  down;
   server 192.168.11.123:8009  max_fails=3  fail_timeout=20s;
   server 192.168.11.1234:8080;
}
```


upstream是Nginx的HTTP Upstream模块，这个模块通过一个简单的调度算法来实现客户端IP到后端服务器的负载均衡。
Nginx的负载均衡模块目前支持4种调度算法：



* `轮询（默认）`。每个请求按时间顺序逐一分配到不同的后端服务器，如果后端某台服务器宕机，故障系统被自动剔除，使用户访问不受影响。Weight 指定轮询权值，Weight值越大，分配到的访问机率越高，主要用于后端每个服务器性能不均的情况下。

* `ip_hash`。每个请求按访问IP的hash结果分配，这样来自同一个IP的访客固定访问一个后端服务器，有效解决了动态网页存在的session共享问题。

* `fair`。这是比上面两个更加智能的负载均衡算法。此种算法可以依据页面大小和加载时间长短智能地进行负载均衡，也就是根据后端服务器的响应时间来分配请求，响应时间短的优先分配。Nginx本身是不支持fair的，如果需要使用这种调度算法，必须下载Nginx的upstream_fair模块。

* `url_hash`。此方法按访问url的hash结果来分配请求，使每个url定向到同一个后端服务器，可以进一步提高后端缓存服务器的效率。Nginx本身是不支持url_hash的，如果需要使用这种调度算法，必须安装Nginx 的hash软件包。



upstream可以设定每个后端服务器在负载均衡调度中的状态，支持的状态参数:



* `down`，表示当前的server暂时不参与负载均衡

* `backup`，预留的备份机器。当其他所有的非backup机器出现故障或者忙的时候，才会请求`backup`机器，因此这台机器的压力最轻。

* `max_fails`，允许请求失败的次数，默认为`1`。当超过最大次数时，返回`proxy_next_upstream`模块定义的错误。

* `fail_timeout`，在经历了`max_fails`次失败后，暂停服务的时间。`max_fails`可以和`fail_timeout`一起使用。


 **`注，当负载调度算法为ip_hash时，后端服务器在负载均衡调度中的状态不能是weight和backup。`** 
### 如何设置页面缓存？


页面缓存设置指令：



* `proxy_cache_path `: 指定缓存的路径和一些其他参数，缓存的数据存储在文件中，并且使用代理url的哈希值作为关键字与文件名。

```nginx
  proxy_cache_path /data/nginx/cache/webserver levels=1:2 keys_zone=webserver:20m max_size=1g;
```

`levels`参数指定缓存的子目录数。`keys_zone`指定活动的key和元数据存储在共享池（webserver为共享池名称，20m位共享池大小），`inactive`参数指定的时间内缓存的数据没有被请求则被删除，默认inactive为10分钟`·max_size`指定缓存空间的大小。



* `proxy_cache `: 设置一个缓存区域的名称，一个相同的区域可以在不同的地方使用。

* `proxy_cache_valid `: 为不同的应答设置不同的缓存时间。


### 如何设置读写分离？

```nginx
server {
    listen       80;
    server_name  localhost;
    #charset koi8-r;
    #access_log  logs/host.access.log  main;
    location / {
        proxy_pass http://192.128.133.202;
        if ($request_method = "PUT"){
                proxy_pass http://192.128.18.201;
        }
    }
}
```
## 参考


[Nginx能为前端开发带来什么？][7]  
[前端工程师应该知道的Nginx][8]  
[前端 Nginx https SSL proxy + 后端 Nginx http 应用的布署教程][9]  
[nginx配置location总结及rewrite规则写法][10]  
[nginx服务器安装及配置文件详解][11]  
[http://freeloda.blog.51cto.com/2033581/1288553][12]  

[3]: https://www.ctolib.com/topics-101000.html
[4]: https://brew.sh/
[5]: http://nginx.org
[6]: http://localhost
[7]: http://imweb.io/topic/56386972d12b230c26e1a17d
[8]: https://www.arayzou.com/2016/09/20/%E5%89%8D%E7%AB%AF%E5%B7%A5%E7%A8%8B%E5%B8%88%E5%BA%94%E8%AF%A5%E7%9F%A5%E9%81%93%E7%9A%84nginx/
[9]: https://www.itsns.org/article/2
[10]: https://segmentfault.com/a/1190000002797606#articleHeader0
[11]: https://segmentfault.com/a/1190000002797601#articleHeader3
[12]: http://freeloda.blog.51cto.com/2033581/1288553
[0]: ../img/1460000013781167.png
[1]: ../img/1460000013781168.png
[2]: ../img/1460000013781169.png

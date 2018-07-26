## 每日一博 | 从一份配置清单详解 Nginx 服务器配置

来源：[https://my.oschina.net/hansonwang99/blog/1835408](https://my.oschina.net/hansonwang99/blog/1835408)

时间 2018-06-28 07:58:57

## 概述
 
在前面 [《Nginx服务器开箱体验》][24] 一文中我们从开箱到体验，感受了一下Nginx服务器的魅力。Nginx是轻量级的高性能Web服务器，提供了诸如HTTP代理和反向代理、负载均衡、缓存等一系列重要特性，因而在实践之中使用广泛，笔者也在学习和实践之中。
 
在本文中，我们继续延续前文，从前文给出的一份示例配置清单开始，详解一下Nginx服务器的各种配置指令的作用和用法。
 
看到了下文中的包含了**“小猪佩琪色”** 的配图了吗，嘿嘿，我们开始吧！
 
  
## Nginx配置文件的整体结构
 
![][3]
 
从图中可以看出主要包含以下几大部分内容：
 
  
#### 1. 全局块 
 
该部分配置主要影响Nginx全局，通常包括下面几个部分：
 
  
 
* 配置运行Nginx服务器用户（组） 
* worker process数 
* Nginx进程PID存放路径 
* 错误日志的存放路径 
* 配置文件的引入 
    
 
  
#### 2. events块 
 
该部分配置主要影响Nginx服务器与用户的网络连接，主要包括：
 
  
 
* 设置网络连接的序列化 
* 是否允许同时接收多个网络连接 
* 事件驱动模型的选择 
* 最大连接数的配置 
    
 
  
#### 3. http块 
 
  
 
* 定义MIMI-Type 
* 自定义服务日志 
* 允许sendfile方式传输文件 
* 连接超时时间 
* 单连接请求数上限 
    
 
  
#### 4. server块 
 
  
 
* 配置网络监听 
* 基于名称的虚拟主机配置 
* 基于IP的虚拟主机配置 
    
 
  
#### 5. location块 
 
  
 
* location配置 
* 请求根目录配置 
* 更改location的URI 
* 网站默认首页配置 
    
 
  
## 一份配置清单例析
 
笔者按照文章： [《Nginx服务器开箱体验》][24] 中的实验，给出了一份简要的清单配置举例：
 
![][4]
 
配置代码如下：
 
```nginx
user  nobody  nobody;
worker_processes  3;
error_log  logs/error.log;
pid  logs/nginx.pid;

events {
	use epoll;
    worker_connections  1024;
}


http {
    include       mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';
    access_log  logs/access.log  main;
    sendfile  on;
    keepalive_timeout  65;

    server {
        listen       8088;
        server_name  codesheep;
        access_log  /codesheep/webserver/server1/log/access.log;
        error_page  404  /404.html;

        location /server1/location1 {
            root   /codesheep/webserver;
            index  index.server2-location1.htm;
        }

        location /server1/location2 {
	    root   /codesheep/webserver;
            index  index.server2-location2.htm;
        }

    }

    server {
        listen       8089;
        server_name  192.168.31.177;
        access_log  /codesheep/webserver/server2/log/access.log;
        error_page  404  /404.html;
		
        location /server2/location1 {
            root   /codesheep/webserver;
            index  index.server2-location1.htm;
        }

        location /srv2/loc2 {
            alias   /codesheep/webserver/server2/location2/;
            index  index.server2-location2.htm;
        }
		
        location = /404.html {
	        root /codesheep/webserver/;
	        index 404.html;
        }
		
    }

}
```
 
接下来就来详细剖析以下配置文件中各个指令的含义:arrow_down:
 
  
## 配置运行Nginx服务器用户（组）
 
指令格式：`user user [group];`
 
* user：指定可以运行Nginx服务器的用户 
* group：可选项，可以运行Nginx服务器的用户组 
    
 
如果user指令不配置或者配置为`user nobody nobody`，则默认所有用户都可以启动Nginx进程
 
  
## worker process数配置
 
Nginx服务器实现并发处理服务的关键，指令格式：`worker_processes number | auto;`
 
* number：Nginx进程最多可以产生的worker process数 
* auto：Nginx进程将自动检测 
    
 
按照上文中的配置清单的实验，我们给worker_processes配置的数目是：3，启动Nginx服务器后，我们可以后台看一下主机上的Nginx进程情况：
 
```
ps -aux | grep nginx
```
 
很明显，理解`worker_processes`这个指令的含义就很容易了
 
![][5]
 
  
## Nginx进程PID存放路径
 
Nginx进程是作为系统守护进程在运行，需要在某文件中保存当前运行程序的主进程号，Nginx支持该保存文件路径的自定义
 
指令格式：`pid file;`
 
* file：指定存放路径和文件名称
  
* 如果不指定默认置于路径`logs/nginx.pid` 
    
 
  
## 错误日志的存放路径
 
指定格式：`error_log file | stderr;`
 
* file：日志输出到某个文件file 
* stderr：日志输出到标准错误输出 
    
 
  
## 配置文件的引入
 
指令格式：`include file;`
 
* 该指令主要用于将其他的Nginx配置或者第三方模块的配置引用到当前的主配置文件中 
    
 
  
## 设置网络连接的序列化
 
指令格式：`accept_mutex on | off;`
 
* 该指令默认为on状态，表示会对多个Nginx进程接收连接进行序列化，防止多个进程对连接的争抢。 
    
 
说到该指令，首先得阐述一下什么是所谓的 **`“惊群问题”`**  ，可以参考 [WIKI百科的解释][26] 。就Nginx的场景来解释的话大致的意思就是：当一个新网络连接来到时，多个worker进程会被同时唤醒，但仅仅只有一个进程可以真正获得连接并处理之。如果每次唤醒的进程数目过多的话，其实是会影响一部分性能的。
 
所以在这里，如果accept_mutex on，那么多个worker将是以串行方式来处理，其中有一个worker会被唤醒；反之若accept_mutex off，那么所有的worker都会被唤醒，不过只有一个worker能获取新连接，其它的worker会重新进入休眠状态
 
这个值的开关与否其实是要和具体场景挂钩的。
 
  
## 是否允许同时接收多个网络连接
 
指令格式：`multi_accept on | off;`
 
* 该指令默认为off状态，意指每个worker process 一次只能接收一个新到达的网络连接。若想让每个Nginx的worker process都有能力同时接收多个网络连接，则需要开启此配置 
    
 
  
## 事件驱动模型的选择
 
指令格式：`use model;`
 
* model模型可选择项包括：select、poll、kqueue、epoll、rtsig等...... 
    
 
  
## 最大连接数的配置
 
指令格式：`worker_connections number;`
 
* number默认值为512，表示允许每一个worker process可以同时开启的最大连接数 
    
 
  
## 定义MIME-Type
 
指令格式：
 
```nginx
include mime.types;
default_type mime-type;
```
 
  
 
* MIME-Type指的是网络资源的媒体类型，也即前端请求的资源类型
  
* include指令将mime.types文件包含进来
  
    
 `cat mime.types`来查看mime.types文件内容，我们发现其就是一个types结构，里面包含了各种浏览器能够识别的MIME类型以及对应类型的文件后缀名字，如下所示：
 
![][6]
 
  
## 自定义服务日志
 
指令格式：
 
```nginx
access_log path [format];
```
 
  
 
* path：自定义服务日志的路径 + 名称
  
* format：可选项，自定义服务日志的字符串格式。其也可以使用`log_format`定义的格式
  
    
 
  
## 允许sendfile方式传输文件
 
指令格式：
 
```nginx
sendfile on | off;
sendfile_max_chunk size;
```
 
  
 
* 前者用于开启或关闭使用sendfile()传输文件，默认off 
* 后者指令若size>0，则Nginx进程的每个worker process每次调用sendfile()传输的数据了最大不能超出此值；若size=0则表示不限制。默认值为0 
    
 
  
## 连接超时时间配置
 
指令格式：`keepalive_timeout timeout [header_timeout];`
 
* timeout 表示server端对连接的保持时间，默认75秒
  
* header_timeout 为可选项，表示在应答报文头部的 Keep-Alive 域设置超时时间：“Keep-Alive : timeout = header_timeout”
  
    
 
  
## 单连接请求数上限
 
指令格式：`keepalive_requests number;`
 
* 该指令用于限制用户通过某一个连接向Nginx服务器发起请求的次数 
    
 
  
## 配置网络监听
 
指令格式：
 
  
 
* 第一种：配置监听的IP地址：`listen IP[:PORT];` 
* 第二种：配置监听的端口：`listen PORT;` 
    
 
实际举例：
 
```nginx
listen 192.168.31.177:8080; # 监听具体IP和具体端口上的连接
listen 192.168.31.177;      # 监听IP上所有端口上的连接
listen 8080;                # 监听具体端口上的所有IP的连接
```
 
  
## 基于名称和IP的虚拟主机配置
 
指令格式：`server_name name1 name2 ...`
 
* name可以有多个并列名称，而且此处的name支持正则表达式书写 
    
 
实际举例：
 
```nginx
server_name ~^www\d+\.myserver\.com$
```
 
此时表示该虚拟主机可以接收类似域名 www1.myserver.com 等的请求而拒绝 www.myserver.com 的域名请求，所以说用正则表达式可以实现更精准的控制
 
至于基于IP的虚拟主机配置比较简单，不再太赘述：
 
指令格式：`server_name IP地址`## location配置
 
指令格式为：`location [ = | ~ | ~* | ^~ ] uri {...}`
 
* 这里的uri分为标准uri和正则uri，两者的唯一区别是uri中是否包含正则表达式 
    
 
uri前面的方括号中的内容是可选项，解释如下：
 
  
 
* `=`：用于标准uri前，要求请求字符串与uri严格匹配，一旦匹配成功则停止
  
* `~`：用于正则uri前，并且区分大小写
  
* `~*`：用于正则uri前，但不区分大小写
  
* `^~`：用于标准uri前，要求Nginx找到标识uri和请求字符串匹配度最高的location后，立即使用此location处理请求，而不再使用location块中的正则uri和请求字符串做匹配
  
    
 
  
## 请求根目录配置
 
指令格式：`root path;`
 
* path：Nginx接收到请求以后查找资源的根目录路径 
    
 
当然，还可以通过alias指令来更改location接收到的URI请求路径，指令为：
 
```nginx
alias path;  # path为修改后的根路径
```
 
  
## 设置网站的默认首页
 
指令格式：`index file ......`
 
* file可以包含多个用空格隔开的文件名，首先找到哪个页面，就使用哪个页面响应请求 
    
 
  
## 后记
 
  



[24]: https://www.jianshu.com/p/dc61f1789f47
[25]: https://www.jianshu.com/p/dc61f1789f47
[26]: https://en.wikipedia.org/wiki/Thundering_herd_problem
[27]: https://my.oschina.net/hansonwang99
[28]: https://www.jianshu.com/p/8eb1668666d4
[29]: https://www.jianshu.com/p/8f226206ca30
[30]: https://www.jianshu.com/p/761b7538592e
[31]: https://www.jianshu.com/p/780a1bf46a1f
[32]: https://www.jianshu.com/p/c88b0f17f62a
[33]: https://www.jianshu.com/p/9bc87b5380e8
[34]: https://www.jianshu.com/p/9e47ffaf5e31
[35]: https://www.jianshu.com/p/a40c36beee63
[36]: https://www.jianshu.com/p/52fa63b222ac
[37]: https://www.jianshu.com/p/c61fcf2a009f
[38]: https://www.jianshu.com/p/da80ea881424
[39]: https://www.jianshu.com/p/477a62165376
[40]: https://www.jianshu.com/p/3f3c9e0e3db5
[41]: https://my.oschina.net/hansonwang99
[42]: http://blog.51cto.com/xxrenzhe/1413203
[43]: http://blog.51cto.com/jungege/1413327
[44]: https://www.oschina.net/question/234345_50631
[45]: https://www.oschina.net/question/234345_50638
[46]: https://gitee.com/Tinywan/lua-nginx-redis
[47]: http://blog.51cto.com/songoo/1416748
[48]: http://blog.51cto.com/airfish2000/1727191
[49]: https://my.oschina.net/u/1038053/blog/619993
[50]: https://my.oschina.net/u/2391658/blog/728919
[51]: http://blog.51cto.com/butterflykiss/1950572
[52]: https://my.oschina.net/hansonwang99/blog/widgets/_blog_detail_list_rel?obj=1835408&p=2&type=ajax
[53]: https://my.oschina.net/xxiaobian/blog/1836762
[54]: https://my.oschina.net/u/3803446/blog/1836761
[55]: https://my.oschina.net/u/2344080/blog/1836757
[56]: https://my.oschina.net/u/1245414/blog/1836751
[57]: https://my.oschina.net/yolks/blog/1836750
[58]: https://my.oschina.net/ahaoboy/blog/1836748
[59]: https://my.oschina.net/u/3246345/blog/1836738
[60]: https://my.oschina.net/windows20/blog/1836737
[61]: https://my.oschina.net/u/2519523/blog/1836730
[62]: https://my.oschina.net/u/3100849/blog/1836729
[63]: https://my.oschina.net/hansonwang99/blog/widgets/_blog_detail_list_news?p=2&type=ajax

[3]: ../img/r2iuE3v.jpg 
[4]: ../img/A3UZry2.jpg 
[5]: ../img/IBVN7ri.jpg 
[6]: ../img/FvYb2uQ.png 
[7]: ../img/rqMFn2J.jpg 
[8]: ../img/YR3aUve.png 
[9]: ../img/rmUNnqA.png 
[10]: ../img/Uv2uArN.jpg 
[11]: ../img/buAzArR.jpg 
[12]: ../img/b6FfEbA.jpg 
[13]: ../img/FjErYn7.jpg 
[14]: ../img/yyMfEfq.jpg 
[15]: ../img/aINNZzF.jpg 
[16]: ../img/UrUz2qq.jpg 
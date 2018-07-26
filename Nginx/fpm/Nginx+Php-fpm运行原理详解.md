## Nginx+Php-fpm运行原理详解

来源：[https://segmentfault.com/a/1190000007322358](https://segmentfault.com/a/1190000007322358)


## 一、代理与反向代理
### 现实生活中的例子
#### 1、正向代理：访问google.com

![][0]

如上图，因为google被墙，我们需要vpn翻墙才能访问google.com。

vpn对于“我们”来说，是可以感知到的（我们连接vpn）
vpn对于"google服务器"来说，是不可感知的(google只知道有http请求过来)。
 **`对于人来说可以感知到，但服务器感知不到的服务器，我们叫他正向代理服务器。`** 
#### 2、反向代理：通过反向代理实现负载均衡

![][1]

如上图，我们访问baidu.com的时候，baidu有一个代理服务器，通过这个代理服务器，可以做负载均衡，路由到不同的server。

此代理服务器,对于“我们”来说是不可感知的(我们只能感知到访问的是百度的服务器，不知道中间还有代理服务器来做负载均衡)。

此代理服务器，对于"server1 server2 server3"是可感知的(代理服务器负载均衡路由到不同的server)
 **`对于人来说不可感知，但对于服务器来说是可以感知的，我们叫他反向代理服务器`** 
### 总结

说白了：“正向”、“反向”是相对于人的感知来说的。
人能感受到的代理就是正向代理，人感受不到的代理就是反向代理。
## 二、初识Nginx与Php-fpm
#### Nginx是什么

Nginx ("engine x") 是一个高性能的HTTP和反向代理服务器，也是一个IMAP/POP3/SMTP服务器。

#### Php-fpm是什么
##### 1、cgi、fast-cgi协议
###### cgi的历史

早期的webserver只处理html等静态文件，但是随着技术的发展，出现了像php等动态语言。
webserver处理不了了，怎么办呢？那就交给php解释器来处理吧！
交给php解释器处理很好，但是，php解释器如何与webserver进行通信呢？

为了解决不同的语言解释器(如php、python解释器)与webserver的通信，于是出现了cgi协议。只要你按照cgi协议去编写程序，就能实现语言解释器与webwerver的通信。如php-cgi程序。

###### fast-cgi的改进

有了cgi协议，解决了php解释器与webserver通信的问题，webserver终于可以处理动态语言了。
但是，webserver每收到一个请求，都会去fork一个cgi进程，请求结束再kill掉这个进程。这样有10000个请求，就需要fork、kill php-cgi进程10000次。

有没有发现很浪费资源？

于是，出现了cgi的改良版本，fast-cgi。fast-cgi每次处理完请求后，不会kill掉这个进程，而是保留这个进程，使这个进程可以一次处理多个请求。这样每次就不用重新fork一个进程了，大大提高了效率。

##### 2、php-fpm是什么

php-fpm即php-Fastcgi Process Manager.
php-fpm是 FastCGI 的实现，并提供了进程管理的功能。
进程包含 master 进程和 worker 进程两种进程。
master 进程只有一个，负责监听端口，接收来自 Web Server 的请求，而 worker 进程则一般有多个(具体数量根据实际需要配置)，每个进程内部都嵌入了一个 PHP 解释器，是 PHP 代码真正执行的地方。
## 三、Nginx如何与Php-fpm结合

上面我们说了，Nginx不只有处理http请求的功能，还能做反向代理。
Nginx通过反向代理功能将动态请求转向后端Php-fpm。
 **`下面我们来配置一个全新的Nginx+Php-fpm`** 
### 1、配置nginx.conf文件

进入nginx目录下，编辑 nginx.conf文件。
如图，在nginx.conf最后一行，添加include文件


![][2]
### 2、添加对应的server

进入上面include的路径，添加一个server.


![][3]
 **`下面我们解释一下配置项的含义：`** 

```nginx
server {
    listen       80; #监听80端口，接收http请求
    server_name  www.example.com; #就是网站地址
    root /usr/local/etc/nginx/www/huxintong_admin; # 准备存放代码工程的路径
    #路由到网站根目录www.example.com时候的处理
    location / {
        index index.php; #跳转到www.example.com/index.php
        autoindex on;
    }   

    #当请求网站下php文件的时候，反向代理到php-fpm
    location ~ \.php$ {
        include /usr/local/etc/nginx/fastcgi.conf; #加载nginx的fastcgi模块
        fastcgi_intercept_errors on;
        fastcgi_pass   127.0.0.1:9000; #nginx fastcgi进程监听的IP地址和端口
    }

}
```

总而言之：当我们访问www.example.com的时候，处理流程是这样的：

```
  www.example.com
        |
        |
      Nginx
        |
        |
路由到www.example.com/index.php
        |
        |
加载nginx的fast-cgi模块
        |
        |
fast-cgi监听127.0.0.1:9000地址
        |
        |
www.example.com/index.php请求到达127.0.0.1:9000
        |
        |
     等待处理...
```
 **`下面我们启用php的php-fpm来处理这个请求`** 

打开php-fpm.conf文件，我们看到如下配置：


![][4]

即:php-fpm模块监听127.0.0.1:9000端口，等待请求到来去处理。

## 四、总结

nginx与php-fpm的结合，完整的流程是这样的。

``` 
 www.example.com
        |
        |
      Nginx
        |
        |
路由到www.example.com/index.php
        |
        |
加载nginx的fast-cgi模块
        |
        |
fast-cgi监听127.0.0.1:9000地址
        |
        |
www.example.com/index.php请求到达127.0.0.1:9000
        |
        |
php-fpm 监听127.0.0.1:9000
        |
        |
php-fpm 接收到请求，启用worker进程处理请求
        |
        |
php-fpm 处理完请求，返回给nginx
        |
        |
nginx将结果通过http返回给浏览器
        
```
## 五、效果展示
### 1、启动nginx与php-fpm模块

![][5]
 **`启动成功，我们查看php-fpm进程`** 


![][6]

如上图，有一个master进程，3个worker进程。

### 2、在网站目录下建立文件

我们编辑文件如下图：


![][7]
### 3、访问网站

![][8]

 **`更多精彩，请关注公众号“聊聊代码”，让我们一起聊聊“左手代码右手诗”的事儿。`** 



[0]: ../img/1460000007322361.png
[1]: ../img/1460000007322362.png
[2]: ../img/1460000007322363.png
[3]: ../img/1460000007322364.png
[4]: ../img/1460000007322365.png
[5]: ../img/1460000007322366.png
[6]: ../img/1460000007322367.png
[7]: ../img/1460000007322368.png
[8]: ../img/1460000007322369.png

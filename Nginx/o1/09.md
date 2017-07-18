# 【nginx网站性能优化篇(2)】反向代理实现Apache与Nginx的动静分离(LNMPA)


## 为什么要使用反向代理

具体请参考这篇博文:[【Linux常识篇(1)】所谓的正向代理与反向代理][0]

## 在虚拟机上配置反向代理的步骤

首先假设你已经假设好了LNMP架构了,这时我们还要安装Apache和php,为什么还要再装一次PHP?因为Apache默认是把PHP作为本身的一个模块(mod_php)来运行的,与Nginx的运行方式不同.

### step1: 安装与配置Apache与php

我们的目的是在localhost:88上配置web1和web2的站点

#### 安装

    

    yum -y install httpd httpd-devel # Ubuntu里面叫做Apache2
    yum -y install php php-mysql php-common php-gd php-mbstring php-mcrypt php-devel php-xml

#### 配置

Apache配置文件/etc/httpd/conf/httpd.conf修改如下信息(不同的就修改,没有的就添加)

```apache
    Listen 88
    ServerName localhost:88
    NameVirtualHost *:88
    
    <VirtualHost *:88>
    DocumentRoot /home/wwwroot/web2/  
    ServerName web2.com          
    ErrorLog logs/web2-error_log              
    CustomLog logs/web2-access_log common     
    </VirtualHost>
    
    <VirtualHost *:88>
    DocumentRoot /home/wwwroot/web1/  
    ServerName web1.com          
    ErrorLog logs/web1-error_log              
    CustomLog logs/web1-access_log common     
    </VirtualHost>
```

然后配置hosts后通过以下能正常访问到默认的index.php就代表完成第一步

    web1.com:88
    web2.com:88
    

### step2: 在Nginx.conf中配置反向代理

#### 通过proxy_pass指向代理服务器Apache

    语法：proxy_pass URL
    默认值：no       
    作用域：location, location中的if字段       
    这个指令设置被代理服务器的地址和被映射的URI，地址可以使用主机名或IP加端口号的形式，例如：proxy_pass http://localhost:8000/uri/;
    

    
```nginx
# 在Nginx的web1的server段加上
location / {
    proxy_pass http://127.0.0.1:88;
}
```

重启Nginx服务,然后再访问web1.com(**客户端192.168.42.196 -> 服务端192.168.42.188**),这个时候访问的是Nginx监听的80端口,进的是Nginx服务,然后Nginx的location命中,再然后由Nginx访问88的httpd(Apache)服务,然后通过Apache来解析执行该文件,这时产生两条日志分别是Nginx服务和Apache服务的两条访问日志

    

    # Nginx
    192.168.42.196 - - [27/Sep/2015:16:06:10 +0800] "GET / HTTP/1.1" 200 20 "-" "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0" -
    #Apache
    192.168.42.188 - - [27/Sep/2015:16:06:10 +0800] "GET / HTTP/1.0" 200 20

我们注意到访问Apache的ip就是服务器的ip,这跟我们想要的客户端ip不符,这时我们就要用到另外一个指令proxy_set_header

#### 通过proxy_set_header向代理服务器Apache发送真实客户端IP

这个指令允许将发送到被代理服务器的请求头重新定义或者增加一些字段。这个值可以是一个文本，变量或者它们的组合。proxy_set_header在指定的字段中没有定义时会从它的上级字段继承。

    语法：proxy_set_header header value 
    默认值： Host and Connection 
    使用字段：http, server, location 
    

    

```nginx
location / {
    proxy_pass http://127.0.0.1:88;
    proxy_set_header  X-Real-IP  $remote_addr; #加上这一行,把$remote_addr赋给变量X-Real-IP,然后该变量可以被后端服务器(被反向代理的服务器)的日志格式中接收到(不管是Nginx和Apache都可以)
}
```

**注意:** 此时Nginx已经通过proxy_pass向代理服务器Apache发送真实客户端IP了,**但是Apache还没接受Nginx发送过来的IP**

所以我们还要去更改Apache的配置文件

    
```apache
LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
# 将%h修改为%{X-Real-IP}i，这里要注意在虚拟主机中的日志格式是不是combined,反正就是修改对应的日志格式既可;
LogFormat "%{X-Real-IP}i %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
```
此时我们就可以把PHP动态文件返回给Apache处理,然后Nginx本身就只处理静态文件,这时我们就可以通过Nginx使用到Apache强大的伪静态功能了

也可以通过配置缓存功能加速Web请求,缓存真实Web服务器上的某些静态资源，减轻真实Web服务器的负载压力

    
```nginx
location ~ \.(jpg|jpeg|png|gif)$ {
    proxy_pass http://192.168.1.204:8080;
    expires 1d;
}
```
### step3: 推荐配置

事实上Nginx需要反向代理过去的,不仅仅有ip,还包含着客户端http请求头必要信息,比如cookie,host,referer等信息,所以推荐如下配置:

    

```nginx
location /
{
    try_files $uri @apache; #try_files 将尝试你列出的文件并设置内部文件指向
}
location @apache
{
    internal; # internal指令指定某个location只能被“内部的”请求调用，外部的调用请求会返回”Not found”
    proxy_pass http://127.0.0.1:88;
    proxy_connect_timeout 300s;
    proxy_send_timeout   900;
    proxy_read_timeout   900;
    proxy_buffer_size    32k;
    proxy_buffers     4 32k;
    proxy_busy_buffers_size 64k;
    proxy_redirect     off;
    proxy_hide_header  Vary;
    proxy_set_header   Accept-Encoding '';
    proxy_set_header   Host   $host;
    proxy_set_header   Referer $http_referer;
    proxy_set_header   Cookie $http_cookie;
    proxy_set_header   X-Real-IP  $remote_addr;
    proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

## LNMPA

通过Nginx强大的反向代理功能,把动态文件给Apache处理,这就形成了LNMPA架构

### LNMP或LAMP的劣势

Nginx是一个小巧而高效的Linux下的Web服务器软件，与Apache相比，消耗资源更少，支持的并发连接，更高的效率，反向代理功能效率高、静态文件处理快等，但动态页面处理能力不如Apache等老牌软件成熟。单独使用Nginx处理大量动态页面时容易产生频繁的502错误。

Apache是一款老牌的Web服务器软件，在高并发时对队列的处理比FastCGI更成熟，Apache的mod_php效率比php-cgi更高且更稳定、对伪静态支持好，不需要转换、多用户多站点权限等方面有着更好的效果，而单独使用Apache处理静态页面时，对内存的占用远远超过Nginx。

### LNMPA的优势

LNMPA使用Nginx作为前端服务器，能够更快、更及时地使用更少的系统资源处理静态页面、js、图片等文件，当客户端请求访问动态页面时，由Nginx反向代理给作为后端服务器的Apache处理，Apache处理完再交予Nginx返回给客户端。   
采用LNMPA能够更好的解决LNMP架构中由于PHP-FPM方面产生的502错误，同时能够以很简单的方式提供更安全的多用户多站点环境。

[0]: http://www.baidu.com
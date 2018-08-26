
## Nginx基本配置-配置多个域名

<font face=微软雅黑>
基于上一篇《（二）Nginx基本配置》讲述的Nginx配置，扩展一下一个Nginx如何配置多个域名的情况。 

### 一、场景

例如我申请了两个域名jialeens.com和jialeens1.com，但是只有一台服务器，此时就需要在Nginx中配置两个域名，都开启80端口才可以被别人访问到。

### 二、配置方案

为了做演示，没有配置真实的域名，只是在本机`C:\Windows\System32\drivers\etc\host`下添加了两条记录来模拟域名访问。

    jialeens.com 127.0.0.1
    jialeens1.com 127.0.0.1

在Nginx配置文件夹conf.d中创建两个配置文件`jialeens.com.conf`和`jialeens1.com.conf`

分别配置两个配置文件内容如下：

#### 1、jialeens.com.conf

```nginx
    server {
            listen       80;
            server_name  jialeens.com www.jialeens.com;
            root   /var/www/jialeens;
    
            charset utf-8;
            access_log  logs/host-jialeens.access.log  main;
    
            #error_page  404              /404.html;
    
            # redirect server error pages to the static page /50x.html
            #
            error_page   500 502 503 504  /50x.html;
            location = /50x.html {
                root   html;
            }
        }
```

#### 2、jialeens1.com.conf

```nginx
    server {
            listen       80;
            server_name  jialeens1.com www.jialeens1.com;
            root   /var/www/jialeens1;
    
            charset utf-8;
            access_log  logs/host.access-jialeens1.log  main;
    
            #error_page  404              /404.html;
    
            # redirect server error pages to the static page /50x.html
            #
            error_page   500 502 503 504  /50x.html;
            location = /50x.html {
                root   html;
            }
        }
```

可以看到两个域名的listen都是80，server_name各自配置了自己的域名，同时root和日志信息都配置了自己的路径。

### 三、原理

Nginx在接收到请求之后，会先获取到请求的url，根据url中的主机域名去查找对于的server，然后就可以确定到底需要去访问哪个server了。

</font>
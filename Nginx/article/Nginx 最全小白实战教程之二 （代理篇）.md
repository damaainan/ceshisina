## Nginx 最全小白实战教程之二 （代理篇）

来源：[https://segmentfault.com/a/1190000014035450](https://segmentfault.com/a/1190000014035450)


## 一、相关概念

代理一般分为正向代理和反向代理，以下是他们的定义(以下内容引自网上)

**`正向代理`** ，也就是传说中的代理,他的工作原理就像一个跳板，简单的说，我是一个用户，我访问不了某网站，但是我能访问一个代理服务器，这个代理服务器呢，他能访问那个我不能访问的网站，于是我先连上代理服务器，告诉他我需要那个无法访问网站的内容，代理服务器去取回来，然后返回给我。从网站的角度，只在代理服务器来取内容的时候有一次记录，有时候并不知道是用户的请求，也隐藏了用户的资料，这取决于代理告不告诉网站。
结论就是，正向代理 是一个位于客户端和原始服务器(origin server)之间的服务器，为了从原始服务器取得内容，客户端向代理发送一个请求并指定目标(原始服务器)，然后代理向原始服务器转交请求并将获得的内容返回给客户端。客户端必须要进行一些特别的设置才能使用正向代理。 **`反向代理`**  例如用户访问 [http://www.test.com/readme][2] ，但www.test.com上并不存在readme页面，他是偷偷从另外一台服务器上取回来，然后作为自己的内容返回用户，但用户并不知情。这里所提到的 www.test.com 这个域名对应的服务器就设置了反向代理功能。
结论就是，反向代理正好相反，对于客户端而言它就像是原始服务器，并且客户端不需要进行任何特别的设置。客户端向反向代理的命名空间(name-space)中的内容发送普通请求，接着反向代理将判断向何处(原始服务器)转交请求，并将获得的内容返回给客户端，就像这些内容原本就是它自己的一样。

两者区别就是：


* 从 **`用途`** 上讲，正向代理的典型用途是为在防火墙内的局域网客户端提供访问Internet的途径。正向代理还可以使用缓冲特性减少网络使用率。反向代理的典型用途是将防火墙后面的服务器提供给Internet用户访问。反向代理还可以为后端的多台服务器提供负载平衡，或为后端较慢的服务器提供缓冲服务。另外，反向代理还可以启用高级URL策略和管理技术，从而使处于不同web服务器系统的web页面同时存在于同一个URL空间下。
* 从 **`安全性`** 讲，正向代理允许客户端通过它访问任意网站并且隐藏客户端自身，因此你必须采取安全措施以确保仅为经过授权的客户端提供服务。反向代理对外都是透明的，访问者并不知道自己访问的是一个代理。


## 二、Nginx代理
#### 1.简单的代理

这样安装的配置文件位置在`/etc/nginx/conf.d`。其中有一个默认的配置文件`default.conf`，内容如下：

```nginx
server {
    listen       80;
    server_name  localhost;

    #charset koi8-r;
    #access_log  /var/log/nginx/log/host.access.log  main;

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

这是一个比较全面的配置文件。且该目录下所有的`.conf`文件都会加载的，只要配置不冲突，所有的配置都会生效。

接下来在此目录新建`guo.conf`，将此域名访问代理我的个人博客 www.guoxiaozhong.cn。
具体内容如下:

```nginx
server {
    listen       80;
    server_name  你的域名或者IP;  
    location / {
        proxy_pass http://www.guoxiaozhong.cn;
    }
}
```

重新加载配置文件;

```
[root@localhost conf.d]# service nginx reload
Reloading nginx:                                           [  OK  ]
[root@localhost conf.d]# service nginx restart
Stopping nginx:                                            [  OK  ]
Starting nginx:                                            [  OK  ]
```

访问效果如下：

![][0]

目前的代理拓扑图为：

![][1]
#### 2.指令说明
 **`listen`** :要监听的端口，我们一般都是监听80端口，其他端口都封死，防火墙只剩80
 **`server_name`** ：服务器的IP或者绑定的域名
 **`location /`** ：这个是路径的意思，`/`这个是代表访问的路径，`/`代表根目录
 **`proxy_pass`** 指令：

语法：proxyz_pass URL
默认值：no
使用字段：location，location中的if字段

这个指令设置被代理服务器的地址和被映射的URI，地址可以使用主机名或IP加端口号的形式，例如：

```nginx
proxy_pass http://www.guoxiaozhong.cn;
```
 **`注意`** ：一定要写`http://`#### 3.分析日志
##### 博客服务器的日志

www.guoxiaozhong 博客服务器的日志

```
Nginx服务器的IP - - [03/Jan/2017:10:49:19 +0800] "GET /2016/11/29/office-2016-for-mac-de-wordmo-ban-wen-jian-jia-wei-zhi/ HTTP/1.0" 200 4112 "设置的代理域名" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:10:49:20 +0800] "GET /content/images/2016/10/logo.jpeg HTTP/1.0" 206 1 "设置的代理域名" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:10:49:20 +0800] "GET /content/images/2016/10/logo.jpeg HTTP/1.0" 206 1778 "设置的代理域名" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:10:49:21 +0800] "GET /assets/images/favicon.png HTTP/1.0" 200 2562 "设置的代理域名" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:53 +0800] "GET / HTTP/1.0" 200 3951 "-" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:53 +0800] "GET /assets/css/vno.css?v=6d7f73f25e HTTP/1.0" 200 4187 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:54 +0800] "GET /assets/css/tomorrow.css?v=6d7f73f25e HTTP/1.0" 200 610 "http://xtrader.spartajet.com/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:54 +0800] "GET /assets/css/animate.css HTTP/1.0" 200 5066 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:55 +0800] "GET /assets/js/main.js?v=6d7f73f25e HTTP/1.0" 200 662 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
Nginx服务器的IP - - [03/Jan/2017:11:01:55 +0800] "GET /assets/js/highlight.pack.js?v=6d7f73f25e HTTP/1.0" 200 15760 "http://xtrader.spartajet.com/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
```

可以看到，我的博客服务器上的日志只显示Nginx的IP和Nginx设置的域名，没有客户端的信息。
##### Nginx代理服务器的日志

```
客户端IP - - [03/Jan/2017:11:01:55 +0800] "GET /assets/js/highlight.pack.js?v=6d7f73f25e HTTP/1.1" 200 15788 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
客户端IP - - [03/Jan/2017:11:01:58 +0800] "GET /content/images/2016/10/logo.jpeg HTTP/1.1" 200 42960 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
客户端IP - - [03/Jan/2017:11:02:13 +0800] "GET /content/images/2016/10/e69ef0aa357bd34022b2824daf3b5a33_r-1.jpg HTTP/1.1" 200 176528 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
客户端IP - - [03/Jan/2017:11:02:16 +0800] "GET /assets/images/favicon.png HTTP/1.1" 200 2562 "设置的代理域名" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36" "-"
```

代理服务器上的日志显示客户端的IP。
#### 4.配置文件显示客户端IP

配置`guo.conf`文件：

```nginx
server {
    listen       80;
    server_name  博客代理域名;

    location / {
        proxy_pass http://www.guoxiaozhong.cn;
        proxy_set_header            X-real-ip $remote_addr;
    }
}
```

主要是加上;

```nginx
proxy_set_header            X-real-ip $remote_addr;
```

[2]: http://www.test.com/readme
[0]: ../img/1460000014035453.png
[1]: ../img/1460000014035454.png
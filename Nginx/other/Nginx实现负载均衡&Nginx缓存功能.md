# Nginx实现负载均衡&Nginx缓存功能

 时间 2017-11-11 19:59:00 

原文[http://www.cnblogs.com/keerya/p/7819842.html][2]

## 一、Nginx是什么

Nginx (engine x) 是一个 高性能的HTTP和反向代理服务器，也是一个IMAP/POP3/SMTP服务器 。Nginx是由伊戈尔·赛索耶夫为俄罗斯访问量第二的Rambler.ru站点（俄文Рамблер）开发的，第一个公开版本0.1.0发布于2004年10月4日。 

其将源代码以类BSD许可证的形式发布，因它的 **稳定性、丰富的功能集、示例配置文件和低系统资源的消耗** 而闻名。2011年6月1日，nginx 1.0.4发布。 

Nginx是一款轻量级的Web 服务器/反向代理服务器及电子邮件（IMAP/POP3）代理服务器。其特点是 **占有内存少，并发能力强** ，事实上nginx的并发能力确实在同类型的网页服务器中表现较好，中国大陆使用nginx网站用户有：百度、京东、新浪、网易、腾讯、淘宝等。 

目前淘宝在nginx做了二次开发：tengine（）。

## 二、Nginx实现反向代理

### 2.1 正向代理和反向代理

正向代理：是一个位于客户端和目标服务器之间的服务器，为了从目标服务器取得内容，客户端向代理发送一个请求并指定目标(目标服务器)，然后代理向目标服务器转交请求并将获得的内容返回给客户端。 

简单来说：

我是一个用户，我访问不了某网站，但是我能访问一个代理服务器；

这个代理服务器呢，他能访问那个我不能访问的网站；

于是我先连上代理服务器，告诉他我需要那个无法访问网站的内容；

代理服务器去取回来，然后返回给我；

从网站的角度，只在代理服务器来取内容的时候有一次记录；

有时候并不知道是用户的请求，也隐藏了用户的资料，这取决于代理告不告诉网站。

反向代理：对于客户端而言它就像是目标服务器，并且客户端不需要进行任何特别的设置。客户端向反向代理的命名空间(name-space)中的内容发送普通请求，接着反向代理将判断向何处(目标服务器)转交请求，并将获得的内容返回给客户端，就像这些内容原本就是它自己的一样。 

简单来说，

用户访问 [http://ooxx.me/readme][5] ； 

但ooxx.me上并不存在readme页面；

他是偷偷从另外一台服务器上取回来,然后作为自己的内容吐给用户；

但用户并不知情┐(ﾟ～ﾟ)┌

这里所提到的 ooxx.me 这个域名对应的服务器就设置了 **反向代理** 功能； 

正向代理和反向代理的区别： 

（1）从 **用途** 上来讲： 

正向代理的典型用途是 **为在防火墙内的局域网客户端提供访问Internet的途径** 。正向代理还可以使用缓冲特性减少网络使用率。反向代理的典型用途是 **将防火墙后面的服务器提供给Internet用户访问** 。反向代理还可以为后端的多台服务器提供 **负载平衡** ，或为后端较慢的服务器提供 **缓冲** 服务。 

另外，反向代理还可以启用高级URL策略和管理技术，从而使处于不同web服务器系统的web页面同时存在于同一个URL空间下。

（2）从 **安全性** 来讲： 

正向代理允许客户端通过它访问任意网站并且隐藏客户端自身，因此你必须采取安全措施以确保仅为经过授权的客户端提供服务。

反向代理对外都是 **透明** 的，访问者并不知道自己访问的是一个代理。 

### 2.2 nginx实现反向代理

nigix代理是基于 **`ngx_http_proxy`** 模块实现的。该模块有很多配置选项，如： 

`proxy_pass`  指定将请求代理至server的URL路径。   
`proxy_set_header`  将发送至server的报文的某首部进行重写。   
`proxy_send_timeout`  在连接断开之前两次发送到server的最大间隔时长；过了这么长时间后端还是没有收到数据，连接会被关闭。   
`proxy_read_timeout`  是从后端读取数据的超时时间，两次读取操作的时间间隔如果大于这个值，和后端的连接会被关闭。   
`proxy_connect_timeout`  是和后端建立连接的超时时间。   
接下来，我们就来仔细说说重点的配置选项：

#### 2.2.1 proxy_pass 配置 

1）替换uri 

常用于页面很固定的时候。比如双十一的大促主页面。

语法如下：

```nginx
    location /uri {
        proxy_pass http://ip:port/newuri/;        # location的/uri将被替换为/newuri
    }
```

举例如下：

```nginx
    location /mobi {
        proxy_pass http://172.17.251.66/mobile/;        # 将/mobi 的请求跳转到新服务器上/mobile目录下
    }
```

在这里，我们需要注意的是， http://ip:port/newuri; ，这个地方最后面加不加 / 意义是不同的。 

如上文，我们就加上了 / ，则意味着全部替换。 

如果我们不加 / ，则是将新路径当做其上级目录，访问的是新路径下的原路径。举例如下： 

```nginx
    location /mobi {
        proxy_pass http://172.17.251.66/mobile;        # 将/mobi 的请求跳转到新服务器上/mobile/mobi目录下
    }
```

2）转换url 

相当于分流，基于url来分流，把一类的请求发送到一个机器（一个集群）中，具体操作看机器的设置。

如果location的URI是通过模式匹配定义的，其URI将直接被传递，而不能为其指定转换的另一个URI。

举例如下：

```nginx
    location ~ ^/mobile {
        proxy_pass http://172.17.251.66;
    }
```

这段代码的意思是，只要有 /mobile 的网址，会直接转到 http://172.17.251.66/mobile 下。 

3）URL重定向 

也就是整个url的重定向。比如两个网站合并或者更换域名时，原先的域名已经不用了，但是有些页面还在访问，就可以通过这种方法来整个重定向，重定向到新的域名中。

如果在location中使用的URL重定向，那么nginx将使用重定向后的URI处理请求，而不再考虑之前定义的URI。

```nginx
    location /youxi{
        rewrite ^(.*)$ /mobile/$1 break;
        proxy_pass http://172.17.251.66;
    }
```

这段代码的意思就是，只要你访问的是带 /youxi 的页面，就会自动重定向到 `http://172.16.100.1/mobile/$1` 上。 `$1` 指的是 `^(.*)$` 中**`括号内`**的部分。这样就实现了整个url的重定向。 

在这里，我们也来详细说说 `ngx_http_rewrite_module` 模块，这是一个非常好用的模块。 

#### 2.2.1.1 ngx_http_rewrite_module 模块 

1）rewrite 用法 

将用户请求的URI基于regex所描述的模式进行检查，匹配到时将其替换为replacement指定的新的URI。

其语法是：

    　　rewrite regex replacement [flag]

注意：如果在同一级配置块中存在多个rewrite规则，那么会自下而下逐个检查；被某条件规则替换完成后，会重新一轮的替换检查。

隐含有循环机制,但不超过10次；如果超过，提示 **500** 响应码， [flag] 所表示的标志位用于控制此循环机制。 

如果replacement是以http://或https://开头，则替换结果会直接以重向返回给客户端。

下面我们来说一说 flag 的具体选项： 

##### [flag] ： 

`last` ：重写完成后停止对当前URI在当前location中后续的其它重写操作，而后对新的URI启动 **新一轮（从第一个开始）** 重写检查；提前重启新一轮循环。 

`break` ：重写完成后 **停止** 对当前URI在当前location中后续的其它重写操作，而后 **直接跳转** 至重写规则配置块之后的其它配置； **结束循环** ，建议在location中使用。 

`redirect` ： **临时重定向** ，重写完成后以临时重定向方式直接返回重写后生成的新URI给客户端，由客户端重新发起请求； **不能以http://或https://开头** ，使用相对路径，状态码： 302。 

`permanent` ：重写完成后以 **永久重定向** 方式直接返回重写后生成的新URI给客户端，由客户端重新发起请求，状态码：301。 

由下图我们可以更清楚的看出跳转到的位置：

![][6]

2）return用法 

return的用法语法如下：

    　　return code [text];
    　　return code URL;
    　　return URL;

停止处理，并返回给客户端指定的响应码。 

#### 2.2.2 proxy_set_header 配置 

proxy_set_header 用于将发送至server的报文的某首部进行重写。常用于 nginx做负载均衡时， 获取客户端IP时， 添加forward头部。

语法如下：

    proxy_set_header Host $host;            # 目的主机地址
    proxy_set_header X-REMOTE-IP $remote_addr;      # 上一跳地址
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;    # 客户端主机地址

原有请求报文中如果存在 X-Forwared-For 首部， 则将 client_addr 以逗号分隔补原有值后， 否则则直接添加此首部； 

### 2.3 nginx实现负载均衡

nginx负载均衡是 **`ngx_http_upstream_module`** 模块的功能， 需要在配置文件 http块 上下文中定义 upstream块 ， 指定一组负载均衡的后端服务器， 然后在上面讲到的 `proxy_pass` 中引用， 就可以反向代理时实现负载均衡了。 

需要注意的是： `ngx_http_upstream` 段要在 server 段前面，要定义在 http 段中。 

语法如下：

    server address [parameters];

接着，我们来看一看选项：

`paramerters` ： 

`weight` ： 负载均衡策略权重， 默认为1； 

`max_fails` ： 在一定时间内（这个时间在fail_timeout参数中设置） 检查这个服务器是否可用时产生的最多失败请求数 

`fail_timeout` ： 在经历了 max_fails 次失败后， 暂停服务的时间。 max_fails 可以和 fail_timeout 一起使用， 进行对后端服务器的健康状态检查； 

`backup` ： 当所有后端服务器都宕机时， 可以指定代理服务器自身作为备份， 对外提供维护提示页面； 

`down` ： 永久不可用。 

需要注意一下的是： `max_fails` 和 `fail_timeout` 是配对使用的，前者是定义在一定时间内检查这个服务器是否连接可用时产生的最多失败请求的次数，后者是规定这个时间，并且这个时间也是在经过前者的失败次数后，暂停服务的时间。 

示例：

    max_fails=3
    fail_timeout=10s

意思就是 10秒内失败3次，则暂停服务10秒。

举例：

```nginx
    upstream dynamic {
        server backend1.example.com weight=5;
        server backend2.example.com:8080 max_fails=3; fail_timeout=5s ;
        server 192.0.2.1 max_fails=3;
        server backup1.example.com:8080 backup;
        server backup2.example.com:8080 backup;
    }
```

当然，我们还有一个专业的健康检测模块 nginx_upstream_check_module-master ，可以根据需要使用。 

upstream 块里可以用多个 server 选项配置多个后端服务器，同时还可配置对后端服务器的健康状态检查，可以在 server 后面加上 max_fails （ 

proxy_next_upstream指定检查策略，默认为返回超时为失败）和 fail_timeout 参数实现；也可以用 health_check 选项来实现， health_check 可以指定的参数较多， 不过需要定义在 location 上下文中。 

另外， 可以指定代理服务器自身作为备份 server ， 当所有后端服务器都宕机时， 对外提供维护提示页面。 

还可以指定负载均衡策略： 主要有 round_robin （加权轮询， 默认） 、 hash 、 ip_hash 、 least_conn （最少连接）和 least_time （最少响应时间，商业版本），策略定义在 upstream 上下文即可。 

具体实例参照 tengine 实现动静分离（）。 

##  三、tengine Tengine是由淘宝网发起的Web服务器项目。它在Nginx的基础上，针对大访问量网站的需求，添加了很多高级功能和特性。Tengine的性能和稳定性已经在大型的网站如淘宝网，天猫商城等得到了很好的检验。它的最终目标是打造一个高效、稳定、安全、易用的Web平台。

从2011年12月开始，Tengine成为一个开源项目，Tengine团队在积极地开发和维护着它。Tengine团队的核心成员来自于淘宝、搜狗等互联网企业。Tengine是社区合作的成果，我们欢迎大家参与其中，贡献自己的力量。

###  tengine实现动静分离 1、下载并解压安装包 

进入官网下载安装包，

![][7]

这里附上官网网址：tengine.taobao.org

小编下载的是 2.2.1 版本。大家可以根据自己的需要来下载。接着，我们使用 rz 命令上传至虚拟机。 

上传完成后，我们来解压：

    tar xvf tengine-2.1.1.tar.gz

2、编译安装 tengine首先，我们要安装依赖的包和包组：

    yum install pcre-devel  openssl-devel -y
    yum groupinstall "development tools" -y

安装完成后，我们进入这个目录：

    cd tengine-2.1.1

然后，我们就可以进行编译安装了：

    ./configure --prefix=/usr/local/tengine
    make && make install

3、修改配置文件 

我们的需求是让这台机器充当调度器，坐到动静分离，所以我们需要在配置文件中添加下面这些：

配置文件为 /usr/local/tengine/conf/nginx.conf 。 

http 段，添加如下内容： 

```nginx
    upstream server-cluster{
            server 172.17.77.77:80;
            server 172.17.252.111:80;
    
            check interval=3000 rise=2 fall=5 timeout=1000 type=http;
            check_http_send "HEAD / HTTP/1.0\r\n\r\n";
            check_http_expect_alive http_2xx http_3xx;
    }
    
    upstream staticsrvs{
            server 172.17.22.22:80;
            server 172.17.1.7:80;
    
            check interval=3000 rise=2 fall=5 timeout=1000 type=http;
            check_http_send "HEAD / HTTP/1.0\r\n\r\n";
            check_http_expect_alive http_2xx http_3xx;
    }
```

server 段，添加如下内容： 

```nginx
    location /stats {
            check_status;      # 定义一个web监听页面
    }
    //以下部分用来实现动静分离
    location ~* .jpg|.png|.gif|.jpeg$ {
            proxy_pass http://staticsrvs;
    }
    location ~* .css|.js|.html|.xml$ {
            proxy_pass http://staticsrvs;
    }
    location / {
            proxy_pass http://server-cluster;
    }
```

如果有下面这一段，我们需要把它注释掉：

```nginx
    location ~ \.php$ {
        root           html;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
```

这样，我们的配置文件就修改完成了。

在我们启动服务前，如果我们的机器开启了 nginx 服务或者 http 服务，要记得把服务关闭，因为 tengine 服务使用的也是 **80端口** 。 

我们来启动服务：

    cd /usr/local/tengine/sbin
    ./nginx -t              //检查配置文件语法错误
    ./nginx                 //启动服务
    ./nginx -s reload       //重新加载服务

当然，我们也可以直接把这个服务写到我们的启动脚本里，这样，以后我们通过 service 或者 systemctl 就可以控制了。 

centos7里的启动脚本在 /usr/lib/systemd/system/nginx.service  
在centos6中，我们如果之前使用 yum 安装过 nginx ，我们就可以复制一个 nginx 的服务脚本，改名为 tengine ，并设置开机自启，具体操作如下： 

    cp /etc/init.d/nginx /etc/init.d/tengine
    vim /etc/init.d/tengine

4、测试 

由于我们在配置文件中定义了一个web的监听页面，所以我们可以去访问一下：

![][8]

上图中就是我们的监听页面，如果某一服务器出现故障，则会标红提示。

我们的网站也是可以正常访问的：

![][9]

接着，我们来测试一下我们的动静分离实现情况，我们把两台静态的服务器的 nginx 服务down掉： 

    systemctl stop nginx

然后我们来看看我们的监听页面，需要刷新几次：

![][10]

我们可以看到，挂掉的两台服务器已经标红了。接着我们来访问一下我们的网站：

![][11]

可以看到，所有的静态文件，包括 图片 和 css 、 js 等文件都没有显示了，我们的动静分离实验圆满完成。 

## 四、nginx实现缓存

### 4.1 为什么需要缓存

缓存的最根本的目的是 为了提高网站性能，减轻频繁访问数据，而给数据库带来的压力 。合理的缓存，还会减轻程序运算时，对CPU带来的压力。在计算机现代结构中，操作内存中的数据比操作存放在硬盘上的数据是要快N个数量级的，操作简单的文本结构的数据，比操作数据库中的数据快N个数量级 。 

例如：每次用户访问网站，都必须从数据库读取网站的标题，每读一次需要15毫秒的时间，如果有100个用户（先不考虑同一时间访问），每小时访问10次，那么就需要读取数据库1000次，需要时间15000毫秒。如果把页面直接变成页面缓存，则每次访问就不需要去数据库读取，大大提升了网站性能。

### 4.2 缓存服务的工作原理

![][12]

缓存的工作原理可以很清楚的从上图中看出来。通过缓存，我们就可以减少大量的重复读取过程，从而节省我们的资源，提升网站的性能。

缓存数据分为两部分（索引，数据）：

1、存储数据的索引，存放在内存中;

2、存储缓存数据，存放在磁盘空间中；

### 4.3 nginx缓存模块

Nginx实现缓存是通过代理缓存 pxory_cache ， 这也是 ngx_http_proxy_module 模块提供的功能， 这里配置选项较多， 常用的选项有： proxy_cache_path 、 proxy_cache 和 proxy_cache_valid 。 

#### 4.3.1 proxy_cache_path

`proxy_cache_path`定义一个完整的缓存空间，指定缓存数据的磁盘路径、索引存放的内存空间以及一些其他参数，如缓存删除策略。

注意：该选项只能定义在http块上下文中。 

如：

    proxy_cache_path /data/cache levels=1:2 keys_zone=web:10m max_size=1G inactive=10;　　　//缓存数据存储在/data/cache目录中

下面我们来看看具体的选项：

levels ：配置在该目录下再分两层目录，一层 **1个随机字符** 作为名称，二层 **2个随机字符** 作为名称， **levels最多三层，每层最多两个字符** ，这是为了加快访问文件的速度；最后使用代理url的 **哈希值** 作为关键字与文件名，一个缓存数据如下： /data/nginx/cache/c/29/b7f54b2df7773722d382f4809d65029c ; 

keys_zone ：用来为这个缓存区 **起名，并设置大小** 。上面的例子就是指定名称为web，这个名称后面proxy_cache需要引用；而10m就是内存空间的大小； 

max_size ：指定最大缓存数据磁盘空间的大小； 

inactive ：在inactive指定的时间内，未被访问的缓存数据将从缓存中 **删除** 。 

#### 4.3.2 proxy_cache

proxy_cache 用来引用上面 proxy_cache_path 定义的缓存空间， 现时打开缓存功能， 如下： 

    proxy_cache web；             //引用上面定义上的缓存空间， 同一缓存空间可以在几个地方使用

#### 4.3.3 proxy_cache_valid

proxy_cache_valid 设置不同响应代码的缓存时间， 如： 

    proxy_cache_valid 200 302 10m;
    proxy_cache_valid 404 1m;

### 4.4 配置nginx缓存实例

先配置 proxy_cache_path ，再配置 proxy_cache 引用、打开缓存空间，接着配置两个 proxy_cache_valid ；为方便调试测试，我们可以通过 add_header 给请求响应增加一个头部信息，表示从服务器上返回的 cache 状态怎么样（有没有命中），主要配置如下： 

定义一个完整的缓存空间；缓存数据存储在/data/cache目录中；配置在该目录下再分两层目录；名称为web(proxy_cache引用)；10m内存空间大小；最大缓存数据磁盘空间的大小；10分钟未被访问的缓存数据将从缓存中删除

```nginx
    http {
    
        proxy_cache_path /data/cache levels=1:2 keys_zone=web:10m max_size=1G inactive=10m;
    
        server {
            listen 80;
            server_name localhost;
            #charset koi8-r;
            #access_log logs/host.access.log main;
            add_header Magedu-Cache "$upstream_cache_status form $server_addr";　　　　#给请求响应增加一个头部信息，表示从服务器上返回的cache状态怎么样（有没有命中）
            location / {
                proxy_pass http://webserver;　　　　#引用上面定义的upstream负载均衡组
                proxy_cache web;　　　　#引用上面定义上的缓存空间，同一缓存空间可以在几个地方使用
                proxy_cache_valid 200 302 10m;
                proxy_cache_valid 404 1m;　　　　#对代码200和302的响应设置10分钟的缓存，对代码404的响应设置为1分钟;
            }
        }
    }
```

## 五、memcached

### 5.1 memcached是什么

Memcached 是一个 **自由开源的，高性能，分布式内存对象** 缓存系统。它是一种 **基于内存** 的 key-value 存储，用来存储小块的任意数据（字符串、对象）。这些数据可以是数据库调用、API调用或者是页面渲染的结果。 

Memcached 简洁而强大。它的简洁设计便于快速开发，减轻开发难度，解决了大数据量缓存的很多问题。它的API兼容大部分流行的开发语言。本质上，它是一个简洁的key-value存储系统。 

一般的使用目的是， 通过缓存数据库查询结果，减少数据库访问次数，以提高动态Web应用的速度、提高可扩展性。

### 5.2 安装配置memcached

#### 5.2.1 安装

直接使用 yum 安装即可。 

    yum install memcached -y

#### 5.2.2 配置文件

memcached 的配置文件与我们常见服务的配置文件不同，他的配置文件非常简单，配置文件为 /etc/sysconfig/memcached 。我们来看一下里面的东西： 

    PORT="11211" #端口
    USER="memcached" #启动用户
    MAXCONN="1024" #最大连接
    CACHESIZE="64" #缓存空间大小

配置文件里只有常用的一些设置，我们可以直接通过修改文件来更改配置，也可以等到我们启动服务的时候添加下面的选项来更改配置：

`-d` 指定memcached进程作为一个守护进程启动 

`-m <num>` 指定分配给memcached使用的内存，单位是MB，默认为64； 

`-u <username>` 运行memcached的用户 

`-l <ip_addr>` 监听的服务器IP地址，如果有多个地址的话，使用逗号分隔，格式可以为“IP地址:端口号”，例如： -l 指定192.168.0.184:19830,192.168.0.195:13542；端口号也可以通过 -p 选项指定 

`-p <num>` Listen on TCP port , the default is port 11211.

`-c <num>` 设置最大运行的并发连接数，默认是1024 

`-R <num>` 为避免客户端饿死（starvation），对连续达到的客户端请求数设置一个限额，如果超过该设置，会选择另一个连接来处理请求，默认为20 

`-k` 设置锁定所有分页的内存，对于大缓存应用场景，谨慎使用该选项 

`-P` 保存memcached进程的pid文件 

`-s <file>` 指定Memcached用于监听的UNIX socket文件 

`-a <perms>` 设置-s选项指定的UNIX socket文件的权限 

`-U <num>` Listen on UDP port , the default is port 11211, 0 is off. 

我们来开启服务：

    systemctl start memcached.service

如果我们想要连接 memcached ，需要用到 telnet 工具，如果没有安装的话，直接 yum 安装即可。我们来连接一下： 

    [root@rs01 ~]# telnet 172.17.77.77 11211
    Trying 172.17.77.77...
    Connected to 172.17.77.77.
    Escape character is '^]'.
    stats　　　　　　　　//查看状态
    STAT pid 15480
    STAT uptime 304
    STAT time 1510475514
    STAT version 1.4.15
    STAT libevent 2.0.21-stable　　
    STAT pointer_size 64　　　　　　
    STAT rusage_user 0.027883
    STAT rusage_system 0.074357
    STAT curr_connections 10
    STAT total_connections 11
    STAT connection_structures 11
    STAT reserved_fds 20
    STAT cmd_get 0
    STAT cmd_set 0
    STAT cmd_flush 0
    STAT cmd_touch 0
    STAT get_hits 0                     //总命中次数
    STAT get_misses 0                   //总未命中次数
    STAT delete_misses 0
    STAT delete_hits 0
    STAT incr_misses 0
    STAT incr_hits 0
    STAT decr_misses 0
    STAT decr_hits 0
    STAT cas_misses 0
    STAT cas_hits 0
    STAT cas_badval 0
    STAT touch_hits 0
    STAT touch_misses 0
    STAT auth_cmds 0
    STAT auth_errors 0
    STAT bytes_read 7
    STAT bytes_written 0
    STAT limit_maxbytes 67108864
    STAT accepting_conns 1
    STAT listen_disabled_num 0
    STAT threads 4
    STAT conn_yields 0
    STAT hash_power_level 16
    STAT hash_bytes 524288
    STAT hash_is_expanding 0
    STAT bytes 0
    STAT curr_items 0
    STAT total_items 0
    STAT expired_unfetched 0
    STAT evicted_unfetched 0
    STAT evictions 0
    STAT reclaimed 0
    END

一般我们衡量一个缓存的性能好坏，一方面是看速度，还有就是看它的命中率。如果一个缓存的命中率很低，就没有太多存在的必要。所以，我们的缓存策略也是很重要的。

接着，我们就来看看在 memcached 中去插入数据的命令： 

命令为 set ，语法如下： 

    set key flags exptime bytes [noreply]
    value

我们来看看各个选项的意思：

key 是通过被存储在Memcached的数据并从memcached获取键(key)的名称。 

flags 是32位无符号整数，该项目被检索时用的数据(由用户提供)，并沿数据返回服务器存储。 

exptime 以秒为单位的过期时间，0表示没有延迟，如果exptime大于30天，Memcached将使用它作为UNIX时间戳过期。 

bytes 是在数据块中，需要被存储的字节数。基本上，这是一个需要存储在memcached的数据的长度。 

noreply (可选) 参数告知服务器不发送回复 

value 是一个需要存储的数据。数据需要与上述选项执行命令后，将通过新的一行。 

我们来依照上述语法添加一条进去：

    set name 1 1800 4 
    keer
    STORED          //表明存上了

现在我们可以来查看一下：

    get name
    VALUE name 1 4
    keer
    END

可以看出，我们刚刚添加的内容已经添加上了，我们再来查看一下状态：

    stats　　
    ……
    STAT get_hits 1                     //总命中次数
    STAT get_misses 0                   //总未命中次数
    ……

发现我们的总命中次数多了一次，就是因为我们刚刚执行了 get 命令，并且是成功的，我们现在来尝试一下 get 一个不存在的内容： 

    get age
    END

然后再来看一下状态：

    stats　　
    ……
    STAT get_hits 1                     //总命中次数
    STAT get_misses 1                   //总未命中次数
    ……

因为我们去获取了一个不存在的内容，所以miss数+1。

当然，这只是我们做的演示，真正的生产环境是不允许我们这么玩的0.0会被玩坏=。=

我们还需要记住的一点是，只要我们的机器断电了，或或者系统重启了， memcached 里面的数据就全部没有了。因为我们的 memcached 是存放在内存中的非关系型数据库，是完全工作在内存中的，所以只要一断电就彻底玩完了╮(╯﹏╰）╭ 

但是我们的nginx数据是存在磁盘中的，只有索引放在内存中，所以即使掉电了，因为磁盘中的数据还在，索引也可以根据数据重新生成。

所以，这样就要涉及到一个选择问题了，看你是追求性能，还是追求安全。我们可以根据不同的需求来选择不同的方式存储数据。

#### 5.2.3 memcached 测试脚本 

在我们的生产环境，我们是可以使用程序来调用的。比如我们可以使用 php 客户端连过来来调用 memcached 。 

为了使我们的 php 连接上 memcache 的客户端，我们需要安装一个包—— php-memcache 。我们直接使用 yum 安装即可： 

    yum install php-memcache

安装完成后，我们需要重启一下 php-fpm 服务： 

    　　systemctl restart php-fpm

我们之前在 /data/web/ 下创建过一个 phpinfo.php 文件，我们可以来通过浏览器查看一下： 

![][13]

可以看出我们的 memcache 已经和 php 建立了连接。 

接下来，给大家提供一个简单的 php 测试 memcache 的小脚本： 

    vim /data/web/memcached.php

```php
    <?php
    $mem = new Memcache;
    $mem->connect("172.17.77.77", 11211); #连接Memcached，ip是你做实验机器的ip
    
    $version = $mem->getVersion();
    echo "Server's version: ".$version."<br/>\n"; #输出Memcached版本信息
    
    $mem->set('magedu', 'Hello World', 0, 600); #向Memcached存储数据'Hello World',时间为600s
    echo "Store data in the cache (data will expire in 600 seconds)<br/>\n";
    
    $get_result = $mem->get('magedu'); #获取testkey的值
    echo "$get_result is from memcached server.";
    ?>
```

然后我们就可以去访问了：

![][14]

bing~我们的测试已经成功啦~


[2]: http://www.cnblogs.com/keerya/p/7819842.html

[5]: http://ooxx.me/readme
[6]: ../img/jYF3Mvz.png
[7]: ../img/6jYjuiM.png
[8]: ../img/zu6n633.png
[9]: ../img/Ez63Qzq.png
[10]: ../img/2m6NfeE.png
[11]: ../img/e6BZ3qm.png
[12]: ../img/I32aEfE.png
[13]: ../img/AzURrqU.png
[14]: ../img/bqy2um6.png
## Nginx 的软件负载均衡详解

<font face=微软雅黑>

负载均衡在服务端开发中算是一个比较重要的特性。因为Nginx除了作为常规的Web服务器外，还会被大规模的用于反向代理前端，因为Nginx的异步框架可以处理很大的并发请求，把这些并发请求hold住之后就可以分发给后台服务端(backend servers，也叫做服务池，后面简称backend)来做复杂的计算、处理和响应，这种模式的好处是相当多的：隐藏业务主机更安全，节约了公网IP地址，并且在业务量增加的时候可以方便地扩容后台服务器。

负载均衡可以分为硬件负载均衡和软件负载均衡，前者一般是专用的软件和硬件相结合的设备，设备商会提供完整成熟的解决方案，通常也会更加昂贵。软件的负载均衡以Nginx占据绝大多数，本文也是基于其手册做相应的学习研究的。

**一、基本简介**

负载均衡涉及到以下的基础知识。

**(1) 负载均衡算法**

> a. Round Robin: 对所有的backend轮训发送请求，算是最简单的方式了，也是默认的分配方式；

> b. Least Connections(least_conn): 跟踪和backend当前的活跃连接数目，最少的连接数目说明这个backend负载最轻，将请求分配给他，这种方式会考虑到配置中给每个upstream分配的weight权重信息；

> c. Least Time(least_time): 请求会分配给响应最快和活跃连接数最少的backend；

> d. IP Hash(ip_hash): 对请求来源IP地址计算hash值，IPv4会考虑前3个octet，IPv6会考虑所有的地址位，然后根据得到的hash值通过某种映射分配到backend；

> e. Generic Hash(hash): 以用户自定义资源(比如URL)的方式计算hash值完成分配，其可选consistent关键字支持一致性hash特性；

**(2) 会话一致性**

用户(浏览器)在和服务端交互的时候，通常会在本地保存一些信息，而整个过程叫做一个会话(Session)并用唯一的Session ID进行标识。会话的概念不仅用于购物车这种常见情况，因为HTTP协议是无状态的，所以任何需要逻辑上下文的情形都必须使用会话机制，此外HTTP客户端也会额外缓存一些数据在本地，这样就可以减少请求提高性能了。如果负载均衡可能将这个会话的请求分配到不同的后台服务端上，这肯定是不合适的，必须通过多个backend共享这些数据，效率肯定会很低下，最简单的情况是保证会话一致性——相同的会话每次请求都会被分配到同一个backend上去。

**(3) 后台服务端的动态配置**

出问题的backend要能被及时探测并剔除出分配群，而当业务增长的时候可以灵活的添加backend数目。此外当前风靡的Elastic Compute云计算服务，服务商也应当根据当前负载自动添加和减少backend主机。

**(4) 基于DNS的负载均衡**

通常现代的网络服务者一个域名会关连到多个主机，在进行DNS查询的时候，默认情况下DNS服务器会以round-robin形式以不同的顺序返回IP地址列表，因此天然将客户请求分配到不同的主机上去。不过这种方式含有固有的缺陷：DNS不会检查主机和IP地址的可访问性，所以分配给客户端的IP不确保是可用的(Google 404)；DNS的解析结果会在客户端、多个中间DNS服务器不断的缓存，所以backend的分配不会那么的理想。

**二、Nginx中的负载均衡**

Nginx中的负载均衡配置在手册中描述的极为细致，此处就不流水帐了。对于常用的HTTP负载均衡，主要先定义一个upstream作为backend group，然后通过proxy_pass/fastcgi_pass等方式进行转发操作，其中fastcgi_pass几乎算是Nginx+PHP站点的标配了。

**2.1 会话一致性**

Nginx中的会话一致性是通过sticky开启的，会话一致性和之前的负载均衡算法之间并不冲突，只是需要在第一次分配之后，该会话的所有请求都分配到那个相同的backend上面。目前支持三种模式的会话一致性：

(1). Cookie Insertion  
在backend第一次response之后，会在其头部添加一个session cookie，即由负载均衡器向客户端植入 cookie，之后客户端接下来的请求都会带有这个cookie值，Nginx可以根据这个cookie判断需要转发给哪个backend了。

    sticky cookie srv_id expires=1h domain=.example.com path=/;

上面的srv_id代表了cookie的名字，而后面的参数expires、domain、path都是可选的。

(2). Sticky Routes  
也是在backend第一次response之后，会产生一个route信息，route信息通常会从cookie/URI信息中提取。

    route $route_cookie $route_uri;

这样Nginx会按照顺序搜索routecookie、route_uri参数并选择第一个非空的参数用作route，而如果所有的参数都是空的，就使用上面默认的负载均衡算法决定请求分发给哪个backend。

(3). Learn  
较为的复杂也较为的智能，Nginx会自动监测request和response中的session信息，而且通常需要回话一致性的请求、应答中都会带有session信息，这和第一种方式相比是不用增加cookie，而是动态学习已有的session。

这种方式需要使用到zone结构，在Nginx中zone都是共享内存，可以在多个worker process中共享数据用的。(不过其他的会话一致性怎么没用到共享内存区域呢？)

    learn 
       create=$upstream_cookie_examplecookie
       lookup=$cookie_examplecookie
       zone=client_sessions:1m
       timeout=1h;
    

**2.2 Session Draining**

主要是有需要关闭某些backend以便维护或者升级，这些关键性的服务都讲求gracefully处理的：就是新的请求不会发送到这个backend上面，而之前分配到这个backend的会话的后续请求还会继续发送给他，直到这个会话最终完成。

让某个backend进入draining的状态，既可以直接修改配置文件，然后按照之前的方式通过向master process发送信号重新加载配置，也可以采用Nginx的on-the-fly配置方式。

    $ curl http://localhost/upstream_conf?upstream=backend
    $ curl http://localhost/upstream_conf?upstream=backend\&id=1\&drain=1
    

通过上面的方式，先列出各个bacnkend的ID号，然后drain指定ID的backend。通过在线观测backend的所有session都完成后，该backend就可以下线了。

**2.3 backend健康监测**

backend出错会涉及到两个参数，max_fails=1 fail_timeout=10s;意味着只要Nginx向backend发送一个请求失败或者没有收到一个响应，就认为该backend在接下来的10s是不可用的状态。

通过周期性地向backend发送特殊的请求，并期盼收到特殊的响应，可以用以确认backend是健康可用的状态。通过health_check可以做出这个配置。

    match server_ok {
        status 200-399;
        header Content-Type = text/html;
        body !~ "maintenance mode";
    }
    server {
        location / {
            proxy_pass http://backend;
            health_check interval=10 fails=3 passes=2 match=server_ok;
        }
    }
    

上面的health_check是必须的，后面的参数都是可选的。尤其是后面的match参数，可以自定义服务器健康的条件，包括返回状态码、头部信息、返回body等，这些条件是&&与关系。默认情况下Nginx会相隔interval的间隔向backend group发送一个”/“的请求，如果超时或者返回非2xx/3xx的响应码，则认为对应的backend是unhealthy的，那么Nginx会停止向其发送request直到下次改backend再次通过检查。

在使用了health_check功能的时候，一般都需要在backend group开辟一个zone，在共享backend group配置的同时，所有backend的状态就可以在所有的worker process所共享了，否则每个worker process独立保存自己的状态检查计数和结果，两种情况会有很大的差异哦。

**2.4 通过DNS设置HTTP负载均衡**

Nginx的backend group中的主机可以配置成域名的形式，如果在域名的后面添加resolve参数，那么Nginx会周期性的解析这个域名，当域名解析的结果发生变化的时候会自动生效而不用重启。

    http {
        resolver 10.0.0.1 valid=300s ipv6=off;
        resolver_timeout 10s;
        server {
            location / {
                proxy_pass http://backend;
            }
        }
        upstream backend {
            zone backend 32k;
            least_conn;
            ...
            server backend1.example.com resolve;
            server backend2.example.com resolve;
        }
    }
    

如果域名解析的结果含有多个IP地址，这些IP地址都会保存到配置文件中去，并且这些IP都参与到自动负载均衡。

**2.5 TCP/UDP流量的负载均衡**

通常，HTTP和HTTPS的负载均衡叫做七层负载均衡，而TCP和UDP协议的负载均衡叫做四层负载均衡。因为七层负载均衡通常都是HTTP和HTTPS协议，所以这种负载均衡相当于是四层负载均衡的特例化，均衡器可以根据HTTP/HTTPS协议的头部(User-Agent、Language等)、响应码甚至是响应内容做额外的规则，达到特定条件特定目的的backend转发的需求。

除了Nginx所专长的HTTP负载均衡，Nginx还支持TCP和UDP流量的负载均衡，适用于LDAP/MySQL/RTMP和DNS/syslog/RADIUS各种应用场景。这类情况的负载均衡使用stream来配置，Nginx编译的时候需要支持–with-stream选项。查看手册，其配置原理和参数和HTTP负载均衡差不多。

因为TCP、UDP的负载均衡都是针对通用程序的，所以之前HTTP协议支持的match条件(status、header、body)是没法使用的。TCP和UDP的程序可以根据特定的程序，采用send、expect的方式来进行动态健康检测。

    match http {
        send      "GET / HTTP/1.0\r\nHost: localhost\r\n\r\n";
        expect ~* "200 OK";
    }
    

**2.6 其他特性**

slow_start=30s：防止新添加/恢复的主机被突然增加的请求所压垮，通过这个参数可以让该主机的weight从0开始慢慢增加到设定值，让其负载有一个缓慢增加的过程。

max_conns=30：可以设置backend的最大连接数目，当超过这个数目的时候会被放到queue队列中，同时队列的大小和超时参数也可以设置，当队列中的请求数大于设定值，或者超过了timeout但是backend还不能处理请求，则客户端将会收到一个错误返回。通常来说这还是一个比较重要的参数，因为Nginx作为反向代理的时候，通常就是用于抗住并发量的，如果给backend过多的并发请求，很可能会占用后端过多的资源(比如线程、进程非事件驱动)，最终反而会影响backend的处理能力。

</font>
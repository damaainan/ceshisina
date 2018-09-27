## Nginx负载均衡——扩展功能(NGINX Plus)

来源：[http://www.cnblogs.com/minirice/p/8570157.html](http://www.cnblogs.com/minirice/p/8570157.html)

时间 2018-03-14 21:10:00

 
本文主要是介绍了NGINX Plus的相关功能，横跨了NGINX Plus R5/R6/R7/R9等各个不同版本的更新。
 
## 什么是NGINX Plus？
 
顾名思义，就是Nginx的加强版或者扩展版。我们知道Nginx是开源的、免费的，但是NGINX Plus的很多功能就需要收费了。Nginx Plus 可以作为一个负载均衡器，一个web服务器，还可以作为一个内容缓存。既然是Nginx的加强版，那无疑功能会比Nginx更加强大。NGINX Plus在开源Nginx已有的功能基础上，提供了许多适合生产环境的专有功能，包括session一致性、实时更新API配置、有效的健康检查等。
 
## NGINX Plus的版本更新
 
NGINX Plus R5 和更新的版本可以支持基于TCP应用的负载均衡(比如MySQL)。这就不仅仅限制于Http的负载均衡，而是大大扩充了Nginx作为负载均衡器的作用范围。R6中TCP负载均衡功能得到很大的扩充，加入了健康检查、动态更新配置、SSL终端等。等到了R7，TCP负载均衡功能就基本和Http负载均衡差不多了。z再到了R9，就可以支持UDP了。通过这些更新，NGINX Plus 远远超过了web应用的层面，成为了一款 **`意义更为广泛的负载均衡器`**  。毕竟协议是基础层面的东西，支持的协议越多，应用面也越广。从最初的Http/SMTP到TCP再到UDP，NGINX Plus一步步的变得越来越强大。
 
开源Nginx和NGINX Plus 都支持HTTP, TCP, 和UDP应用的负载均衡。但NGINX Plus 提供了一些企业级别的功能，这些功能是收费的，包括session一致性，健康检查，动态更新配置等。
 
## HTTP负载均衡
 
NGINX Plus对Http负载均衡做了很多功能优化，诸如HTTP 升级、长连接优化、内容压缩和响应缓存等。在NGINX Plus中Http负载均衡的实现也非常简单：
 
```nginx
http {
    upstream my_upstream {
        server server1.example.com;
        server server2.example.com;
    }

    server {
        listen 80;
        location / {
            proxy_set_header Host $host;
            proxy_pass http://my_upstream;
        }
    }
}
```
 
可以通过`proxy_set_header`指令来设置Host，而`proxy_pass`将请求转发到上游的`my_upstream`中。
 
## Http长连接(HTTP Keepalives)
 
HTTP协议是用的底层TCP协议来传输请求，接收响应的。HTTP1.1支持TCP的长连接或者重利用，以免反复的创建和销毁TCP连接所带来的开销。
 
我们看看Http的长连接：
 
![][0]
 
NGINX是一个完全意义的反向代理，在长连接上也毫不含糊。它管理所以来从客户端到Nginx的长连接，同样也会管理从Nginx到上游服务器的长连接，二者是完全独立的。
 
Nginx管理的长连接：
 
![][1]
 
NGINX 将连接上游服务器的空闲连接做了“缓存”，并不直接关掉它们。如果有请求过来，NGINX先从缓存的活跃连接中去拿一个使用，而不是立马创建一个新的，如果缓存为空那么NGINX 再去新建一个连接。这种操作这降低了Nginx和上游服务器之间的延迟并减少的临时端口的利用率，所以NGINX能处理大的并发。这种技术加上别的负载均衡技术，有时候可以被称为 **`连接池`**  ，或者连接复用。
 
为了配置闲置长连接缓存，你需要指定几个指令：`proxy_http_version,proxy_set_header,keepalive`

```nginx
server {
    listen 80;
    location / {
        proxy_pass http://backend;
        proxy_http_version 1.1; # 只有Http1.1/2.0才能支持长连接
        proxy_set_header Connection "";
    }
}

upstream backend {
    server webserver1;
    server webserver2;

    # maintain a maximum of 20 idle connections to each upstream server
    keepalive 20; # 闲置长连接缓存时间为20
}
```
 
## TCP 和 UDP的负载均衡
 
作为对Http协议的扩展，NGINX Plus可以直接支持基于TCP和UDP协议的应用。基于TCP的如MySQL，支持UDP的如DNS 和RADIUS应用。对于TCP请求来说，NGINX Plus接收了客户端的TCP请求，然后再创建一个TCP请求对上游服务器发起访问。
 
```nginx
stream {
    upstream my_upstream {
        server server1.example.com:1234;
        server server2.example.com:2345;
    }

    server {
        listen 1123 [udp];
        proxy_pass my_upstream; #注意这里没有http://了
    }
}
```
 
对TCP请求的支持出现在NGINX Plus R5，R6和R7版本主要是在优化这个功能，到R7时TCP请求的负载均衡已经强大到足够媲美Http负载均衡了，到了R9，则可以支持UDP了。这里先有个印象，后面会更加详细介绍TCP负载均衡功能。
 
## 连接数限制(Connection Limiting)
 
你还可以为负载均衡做连接数量限制。这里说的连接是指NGINX Plus发给上游服务器的Http/TCP/UDP请求连接(对于UDP则是会话)。有了连接数限制的功能，当上游服务器的Http/TCP连接数量，或者UDP的会话数量超过一定的值时，NGINX Plus就不再创建新的连接或者会话。客户端多出的请求连接可以被放进队列等候，也可以不被处理。可以通过 **``max_conns,queue``**  指令来实现这一点：
 
```nginx
upstream backend {
    zone backends 64k;
    queue 750 timeout=30s;

    server webserver1 max_conns=250;
    server webserver2 max_conns=150;
}
```

 `server`指令表示webserver1 最多承载250个连接而webserver2 最多150个，多出来的可以放在队列queue当中等候。在队列queue中等候的连接数量和等候时间也是有限制的。当webserver1 和webserver2 连接数降低到各自最大连接数以下时，等候在队列queue中的连接随时就补上去。
 `queue 750 timeout=30s`表示总共可以有750个连接排队等候，每个连接等候30s。
 
Limiting connections 是十分有用的，可以为客户端提供可持续可预见的服务——不必因为某台server负载过大导致挂掉。一般来说一台server大概能承载多少负荷是可以通过某些手段测试出来的，因此把这个可承受的上线作为max_conns指令的值便可以保证server的相对安全。
 
## Least Time 负载均衡算法
 
在NGINX Plus R6中增加了一种新的均衡算法——Least Time，将相应时间也考虑进去，算得上对Least Connections的扩展。
 
这种算法同时考虑当前连接数和连接池里各个节点的平均响应时间。目的是使得当前请求选择当下 **`响应更快、连接更少`**  的服务器，而不是选择响应更慢、连接更多的。
 
当连接池的各个服务器节点有着明显不同的响应延时时，这种算法就要优于其他的几种(round-robin/ip-hash/lease connections)。一个典型的应用场景是，如果有两个分布在不同的地域的数据中心，那么本地的数据中心就要比异地的数据中心延时要少得多，这个时候就不能仅仅考虑当下连接数了，这个响应的延时也要被计入考量。Least Time算法就更倾向于选择本地的，当然这只是“更倾向于”的问题，并不能代替Nginx最基本的错误转移功能，哪怕本地的数据中心响应再快，如果它挂掉了Nginx Plus也能马上切换到远端数据中心。
 
![][2]
 
“最少时间”可以有两种计算方式，一种是从请求发出到上流服务器接返回响应头部算的时间，另一种是从请求发出到接收到全部请求体的时间，分别以`header_time`和`response_time`来表示。
 
## Session一致性(Session Persistence)
 
Session一致性问题除了可以通过指定ip-hash的均衡算法来实现，还有更为通用的实现方式，这是在NGINX Plus 中实现的。
 
NGINX Plus可以识别用户Session，从而能够鉴别不同的客户端，并且可以将来自同一个客户端的请求发往同一个上游服务器。这在当应用保存了用户状态的情况下非常有用，可以避免负载均衡器按照某个算法将请求发到别的服务器上去。另外，在共享用户信息的集群服务器这种方式也非常有用。
 
session一致性的要求同一个客户端每次的请求都选择同一个服务器，而负载均衡要求我们利用一种算法去服务器连接池里面去选择下一个，那么这两种矛盾的方式可以共存么？可以的，NGINX Plus按照如下的步骤决策到底选用哪一种：
 

* 如果request匹配某个Session一致性的规则，那么根据这个规则选取上游服务器； 
* 如果没有匹配上或者匹配的服务器无法使用，那么使用负载均衡算法选择上游服务器； 
 

为了能保证session一致性，Nginx Plus提供了sticky cookie，sticky learn和sticky route几种规则。
 
#### sticky cookie 规则
 
对于 sticky cookie 规则，当客户端的 **`第一个`**  请求选择了某个上游服务器，并从这个上游服务器返回响应时，NGINX Plus 为这个响应添加一个session cookie，用来鉴别这个上游服务器。当后面的请求再过来时，NGINX Plus取出这个cookie，分析是哪一台服务器，再把请求发往这台相同的服务器。
 
使用指令`sticky cookie`，配置如下：
 
```nginx
upstream backend {
    server webserver1;
    server webserver2;

    sticky cookie srv_id expires=1h domain=.example.com path=/; 
}
```
 
cookie的名字就叫srv_id，用来“记住”是哪一个server；过期时间1h，domain为`.example.com`；path为`/`
 NGINX Plus在第一次响应中，插入一个名称为`srv_id`的`cookie`，用来“记住”这第一次请求是发个哪个上游的，后面的请求带上这个`cookie`，同样再被NGINX Plus甄别一下，再发往同一个的服务器。这样就能保证session的一致了。
 
#### sticky route 规则
 
和`sticky cookie`规则类似，只不过“记住”上游服务器的方式不同而已。
 
在客户端发起第一次请求时，接收它的服务器为其分配一个route，此后这个客户端发起的所有请求都要带上这个route信息，或者在cookie中或者在uri中。然后和server指令中的route参数做对比，决定选取哪个server。如果指定的服务器无法处理，那交给负载均衡算法去选择下一个服务器。
 
```nginx
map $cookie_jsessionid $route_cookie {
    ~.+\.(?P<route>\w+)$ $route;
}

map $request_uri $route_uri {
    ~jsessionid=.+\.(?P<route>\w+)$ $route;
}

upstream backend {
    server backend1.example.com route=a;
    server backend2.example.com route=b;
    # select first non-empty variable; it should contain either 'a' or 'b'
    sticky route $route_cookie $route_uri;
}
```
 
在这里，route在`JSESSIONID`的`cookie`中选择，如其包含a那么选择服务器`backend1`；如其包含b则选择`backend2`，如果都不包含那么在`$request_uri`中再做类似的选择，以此类推。
 
不管是选哪种方式保持session一致，如果选择出的server无法使用，那么将会按照负载均衡算法(如round-robin)在服务器列表中的选择下一台server继续处理。
 
## 实时健康检查(Active Health Checks)
 
前面提到过，Nginx有两大功能：一个是扩展，增加更多的server以满足更大的并发；二是检测失效server，以便及时排除。那么，如何定义一个“失效server”(failed server)就变得非常重要。这一节就是来讨论这个问题。这是NGINX Plus 才有的功能，并且是收费的。
 
开源版本NGINX 可以提供简单的健康检查，并且可以自动做故障转移。但是如何定义一个上游server“失效”开源NGINX 却做的很简单。NGINX Plus为此提供了一个 **`可以自定义的、综合式的评判标准`**  ，除此之外NGINX Plus还可以平缓的添加新的服务器节点到集群当中。这个功能使得NGINX Plus可能甄别更为多元化的服务器错误，十分有效的增加了`HTTP/TCP/UDP`应用的可靠性。
 
这里要用到的指令有：`health_check,match`等指令:
 
```nginx
upstream my_upstream {
    zone my_upstream 64k;
    server server1.example.com slow_start=30s;
}

server {
    # ...
    location /health {
        internal;
        health_check interval=5s uri=/test.php match=statusok;
        proxy_set_header HOST www.example.com;
        proxy_pass http://my_upstream;
    }
}

match statusok {
    # 在/test.php 做健康检查
    status 200;
    header Content-Type = text/html;
    body ~ "Server[0-9]+ is alive";
}
```
 `health_check`中`interval=5s`表示每隔5s检测一次；`uri=/test.php`表示在`/test.php`里进行健康检查，NGINX Plus自动发起uri的请求，uri可以自定义，你在里面具体执行检查的逻辑，比如mysql/redis这些是否正常，然后作出一定的响应；然后在match指令中，就通过一些规则来匹配`/test.php`的响应。`/test.php`的响应可以包括`status,header,body`这些，供后面这些指令做匹配。全部检查通过，就算健康，server被标记为活跃；如果一项匹配未通过，比如`Content-Type = text/json`或者`status = 201`那都算检测失败，server不健康，被标记为不活跃。
 
## 使用DNS发现新的服务
 
Nginx Plus一启动就会进行DNS解析并且自动永久缓存解析出的域名和IP，但是某些情形下需要重新解析下，这时候可以使用下面的指令来实现：
 
```nginx
resolver 127.0.0.11 valid=10s;

upstream service1 {
    zone service1 64k;
    server www.example.com  service=http resolve;
}
```
 
127.0.0.11是默认的DNS服务器的地址，此例中NGINX Plus每10s中DNS服务器发起一次重新解析的请求。
 
## 访问控制(Access Controls)
 
NGINX Plus Release 7主要给增加了TCP负载均衡的安全性。比如Access Controls和DDoS保护。
 
你现在可以允许或者拒绝对做反向代理的或者做负载均衡的TCP服务器的访问，仅仅通过配置简单的IP或者一个IP范文就能实现：
 
```nginx
server {
    # ...
    proxy_set_header Host www.example.cn;
    proxy_pass http://test;
    deny 72.46.166.10;
    deny 73.46.156.0/24;
    allow all;
}
```
 
第一个deny指令拒绝一个IP的访问，第二个拒绝一个IP范围，除去这两个剩下的都是被允许访问的。被拒绝访问的IP，会被返回403错误。
 
## 连接数限制(Connection Limiting)
 
使用NGINX Plus R7你可以限制客户端发往由NGINX Plus代理的TCP应用的请求数量，防止对TCP的请求数量过多。在你的应用中，可能一部分的比另一部分要慢一些。比如说，请求你的应用的某块，将会产生大量的MySQL请求，或者fork出一大堆的work进程。那么攻击者将会利用这点产生成千上万个请求，致使你的服务器负载过重而瘫痪。
 
但是有了连接数限制功能，你可以通过配置`limit_conn my_limit_conn`指令限制同一个客户端(IP)所能发起的最大请求数，以此将上述的攻击风险降到最低。
 
```nginx
stream {
    limit_conn_zone $binary_remote_addr zone=my_limit_conn:10m;
    # ...
    server {
        limit_conn my_limit_conn 1;
        # ...
    }
}
```
 
这条指令限定了每个IP同时只能有一个连接。
 
## 带宽限制(Bandwidth Limiting)
 
R7 还新增了一项功能——限制每个连接的上传和下载的最大带宽。
 
```nginx
server {
    # ...
    proxy_download_rate 100k;
    proxy_upload_rate  50k;
}
```
 
有了这个配置，客户端最多只能以100kbytes/s的速度下载，以50kbytes/s的速度上传。因为客户端可以开多个连接，因此如果要限制总的上传/下载速度，同时还得限制下单个客户端的连接数。
 
## 支持无缓冲的上传
 
这是在R6中增加的功能。你可以在R6和以后的版本中使用无缓冲的上传，意味Nginx Plus可以通过更大的Http请求比如上传。无缓冲的上传可以在这些请求一过来便进行上传，而不是像之前那样先是缓冲所有的上传内容，再将其转发给你上游服务器。
 
默认情况下，Nginx 在上传时，接收到数据时会先放进缓冲区进行缓冲，以避免将资源和基于worker进程的后端脚本绑定，但是针对事件驱动的后端语言如Node.js，缓冲是几乎没有必要的。这个修改改进了服务器对上传大文件的响应性，因为应用可以一接收到数据就马上对做出响应，使得上传进度条变成实时的和准确的。同样，这个改进也减少了磁盘I/O。
 
## SSL/TLS优化
 
在R6中，可以在和上游的HTTPS 或者 uwSGI 服务器打交道时为客户端提供一个证书。这大大提高了安全性，尤其是在和不受保护网络上的安全服务进行通信的时候。R6 支持IMAP, POP3, 和SMTP的SSL/TLS 客户端认证。
 
## 缓存优化
 
proxy_cache 指令可以支持变量了，这个简单的改进以为着你可以定义几个基于磁盘的缓存，并且根据请求数据做自由的选择。当你打算创建巨大的内容缓存，并且将其保存到不同的磁盘时是非常有用的。
 
## API功能
 
upstreem模块的一些指令，不光可以通过手动去修改，还可以通过restful api的方式去修改，并且马上自动更新。有了这个功能，NGINX Plus的一些功能，你都可以通过API的方式去改变。应用性得到很大提升。当然这也是收费的：
 
```nginx
upstream backend {
    zone backends 64k;
    server 10.10.10.2:220 max_conns=250;
    server 10.10.10.4:220 max_conns=150;
}

server {
    listen 80;
    server_name www.example.org;

    location /api {
        api write=on;
    }
}
```
 
有了API，你就可以使用curl工具来动态修改配置了，比如用POST命令来增加一个集群的节点：
 
```nginx
$ curl -iX POST -d '{"server":"192.168.78.66:80","weight":"200","max_conns":"150"}' http://localhost:80/api/1/http/upstreams/backend/servers/
```
 
相当于添加了一个这样的配置：
 
```nginx
upstream backend {
    zone backends 64k;
    server 10.10.10.2:220 max_conns=250;
    server 10.10.10.4:220 max_conns=150;
    #此处是通过api添加的
    server 192.168.78.66:80 weight=200 max_conns=150;
}
```
 
如果需要修改一个节点配置，你可以用服务器节点在连接池中的自然顺序(从0开始)作为它们各自唯一的ID,然后使用PATCH/DELETE方法去操作它们：
 
```
$ curl -iX PATCH -d '{"server":"192.168.78.55:80","weight":"500","max_conns":"350"}' http://localhost:80/api/1/http/upstreams/backend/servers/2
```
 
这条命令是修改以上连接池中的第三个`server 192.168.78.66:80 max_conns=200;`为：
 
```
server 192.168.78.55:80 weight=500  max_conns=350;
```
 
如果要返回所有的节点信息，可以使用：
 
```
$ curl -s http://localhost:80/api/1/http/upstreams/backend/servers/
```
 
返回的是一个JSON字符串。
 
```json
{
{
    "backup": false,
    "down": false,
    "fail_timeout": "10s",
    "id": 0,
    "max_conns": 250,
    "max_fails": 1,
    "route": "",
    "server": "10.10.10.2:220",
    "slow_start": "0s",
    "weight": 1
},
{
    "backup": false,
    "down": false,
    "fail_timeout": "10s",
    "id": 1,
    "max_conns": 150,
    "max_fails": 1,
    "route": "",
    "server": "10.10.10.4:220",
    "slow_start": "0s",
    "weight": 1
},
{
    "backup": false,
    "down": false,
    "fail_timeout": "10s",
    "id": 2,
    "max_conns": 200,
    "max_fails": 1,
    "route": "",
    "server": "192.168.78.66:80",
    "slow_start": "0s",
    "weight": 200
}
}
```
 
## 配置的最佳实践
 
为不同个应用配置创建各自的目录和文件，并用`include`指令再合并到一起是个非常好的习惯。标准的 NGINX Plus配置是将各个应用的配置文件放到各自的conf.d directory目录下：
 
```nginx
http {
    include /etc/nginx/conf.d/*.conf;
}
stream {
    include /etc/nginx/stream.d/*.conf;
}
```
 
http 和 stream 模块的各自分属不同的目录，而在http 下的都是http请求的配置，stream 都是TCP/UDP请求的配置。没有统一的标准，主要是看开发者自己能便于识别和修改。
 


[0]: https://img1.tuicool.com/n2auqiu.png 
[1]: https://img2.tuicool.com/zYvMBvE.png 
[2]: https://img2.tuicool.com/F7RZbmA.png 






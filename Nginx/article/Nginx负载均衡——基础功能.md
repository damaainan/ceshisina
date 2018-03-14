## Nginx负载均衡——基础功能

来源：[http://www.cnblogs.com/minirice/p/8553778.html](http://www.cnblogs.com/minirice/p/8553778.html)

时间 2018-03-13 09:45:00


熟悉Nginx的小伙伴都知道，Nginx是一个非常好的负载均衡器。除了用的非常普遍的Http负载均衡，Nginx还可以实现Email,FastCGI的负载均衡，甚至可以支持基于Tcp/UDP协议的各种应用的负载均衡(比如MySQL,DNS等)。这些功能分别在Nginx的不同模块实现了。负载均衡可以看成Nginx对外提供的一种服务。

我们先来简单介绍下Nginx负载均衡的基本的功能。并且，我们在下面的介绍中会同时罗列Nginx Plus(Nginx的扩展板，部分功能收费)


## 介绍

在多个应用程序实例之间做负载平衡是一种常用的优化资源利用、最大化吞吐量、减少延迟和确保容错配置的技术。而使用Nginx可以作为一个高效的Http负载均衡器将流量分摊到各个服务器上，从而改善性能，增加扩展性和可靠性。


## 简单配置


负载均衡的基本配置十分的简单，在基本配置上，你可以添加更多的指令来满足自己个性化的需求。

如下：

 
```nginx
http {
    upstream myapp1 {
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
    }

    server {
        listen 80;

        location / {
            proxy_pass http://myapp1;
        }
    }
}
```


上面，所有的请求都将被代理到服务器组myapp1上，myapp1上有三台服务器srv1-srv3，这三台服务器将要分摊请求。如果没有指定负载均衡方法，那么默认的方法是round-robin。

在nginx中，HTTP，HTTPS，FastCGI，uwsgi，SCGI都可以做反向代理，并且可以做负载均衡，上面的例子是http的。要使用https的负载均衡，简单的将http改为https即可。

  
## 负载均衡的常用算法


负载均衡的方法，

就是Http请求如何被分配到各个服务器上的算法。常用的负载均衡的常用算法有以下种：

  

* Round‑Robin 默认的方法，也是最简单的一种。即Http请求按照服务器列表罗列的顺序一次进行分配；
* Least Connections 在这种方式下，每个请求被发送到当下具有最小有效连接数的服务器上，当然权重也会被考虑进去。比如当下有三台服务器A/B/C，当下各自的连接数是100/200/300，那么下一个请求过来就会被分配到A服务器进行处理
* Hash 用户定义一个Hash的Key值，比如IP或者URL，将这个Hash的Key和服务器做一次映射，每次请求过来都会按照这个映射被分配到同一台服务器
* IP Hash (仅适用于Http负载均衡的情况)，根据客户端IP的前三个字节(比如IP是10.25.2.10那么就拿10.25.2做映射)来分配请求，这个和上一种类似
* Least Time 即最少时间。新的请求将被发往拥有最快响应时间和最少连接数的上游服务器。这是Nginx Plus才具有的方式
  

## 最少连接算法

即Least Connections这种算法。最少连接，顾名思义，就是当下谁的连接数最少请求就然该谁来处理。这是一种相对公平的方式，防止某些服务器负载过重，将请求分配到相对“清闲”的服务器上去。基本的配置如下：

```nginx
upstream myapp1 {
        least_conn;
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
}
```

需要指定least_conn指令。


## Session一致性问题


如果负载均衡采用round-robin或者least-connected算法，同一个客户端发送过来的不同请求就有可能被不同的server处理，这种情形下就不能保证两次请求session的一致性。

为了解决这个问题，可以采用第三种负载均衡的算法，那就是ip-hash。有了IP哈希，将客户端的IP和服务器组列表的几个服务器之间建立一种对应关系，那么每个客户端的每次请求就只能被分配到一台server上面，从而保证session的一致性。ip-hash的方式配置如下：

 
```nginx
upstream myapp1 {
    ip_hash;
    server srv1.example.com;
    server srv2.example.com;
    server srv3.example.com;
}
```


## 负载均衡权重

说是负载"均衡"，但是不是说每个server的分配的请求是完全一样。前面讨论的各个服务器组，里面的各个server其实是地位平等、利益均沾。事实上，由于每个server的特性可能不一样，有些server硬件条件好，稳定性高，理应多处理些请求，相反另一些不太稳定的server就应该适当的少分配请求。我们可以为这些server分配不同的权重，来定义它们在处理请求时所扮演角色的重要性。权重用指令weight来表示，权重高表示选择的几率更大，权重低表示选中的几率更小，权重为0表示始终不选用。以round-robin算法为例：

```nginx
upstream myapp1 {
        server srv1.example.com weight=3;
        server srv2.example.com;
        server srv3.example.com;
}
```

没有weight指令的默认其为1。如果有5个请求过来，理想情况下srv1就能接到3个，srv2和srv3各一个。


## 服务器健康检查

反向代理的各种实现(如http/https/FastCGI)还可以对各个server做健康检查。如果请求一个server错误(如返回500，究竟如何才为“失效”，在Nginx Plus中做了扩展)，nginx就将这个server标记为失效的，在接下来一段时间的请求中就会避免选择这台server。究竟这端时间要多长才合适？有max_fails和fail_timeout参数来定义。



* `max_fails`默认是1，表示在fail_timeout时间内，有多少次对某个server的访问失败，就算作这台server的正式失效(你总要给人家多表现几次的机会撒)，默认情况下就是1次；      

    
* `fail_timeout`默认是10s，有两层含义，一就是为max_fails指令限定一个时间范围，二就是如果server已经被标记为失效，那么过了这个时间后，你就应该分配个请求去试探下这个server，是否已经可用了（你总的给人家重新做人的机会）。如果还是不可用，那么此server继续被标记为失效的server，如果已经可用了那么就重新标记为活跃，在接下来的请求中，继续按照round-robin/ip-hash等算法和权重给它分配请求，和平常无异。      

    
  

除了这些指令之外proxy_next_upstream, backup, down, 和 keepalive 也针对负载均衡功能做了不同的限定。

以上这些功能是基本是Nginx的免费版本提供的，其实负载均衡里可以说的话题还多着呢。我们下篇文章中谈谈Nginx Plus提供的更为丰富的负载均衡的功能。



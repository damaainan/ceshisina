# Nginx配置-集群策略

 时间 2017-09-10 11:00:10  

原文[http://www.jialeens.com/archives/314][1]

<font face=微软雅黑>
Nginx的集群提供了多种策略，这篇就以集群策略展开讲解如何根据自身项目来选择和配置集群策略。

当一个应用的请求数大于自身可处理的请求数时，后面的请求将不会被处理或者阻塞，影响应用的使用，所以在不修改程序的前提下，首先会想到横向扩展，搭建集群。

Nginx提供的集群策略有一下五钟：

1. `轮询`
1. `ip_hash`
1. `weight`
1. `fair`( 第三方 )
1. `url_hash`( 第三方 )

##  轮询 

轮询在Nginx钟配置 是默认的，每一个请求按顺序逐一分配到不同的后端服务器，如果后端服务器 down 掉了，则能自动剔除。

配置示例如下：

```nginx
    upstream test {
        server 127.0.0.1:8080;
        servsr 127.0.0.1:8081;
    }
```

可以发现，两个服务在请求数量上基本是持平的。

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8080

127.0.0.1:8081

##   weight   

weight 是设置权重，用于后端服务器性能不均的情况，访问比率约等于权重之比。 

配置示例如下：

```nginx
    upstream test {
        server 127.0.0.1:8080 weight=1;
        servsr 127.0.0.1:8081 weight=5;
    }
```
可以发现，两个服务在请求数量上是按照权重比来分配的。

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8081

127.0.0.1:8081

127.0.0.1:8081

127.0.0.1:8081

127.0.0.1:8080

127.0.0.1:8081

127.0.0.1:8081

127.0.0.1:8081

##  ip_hash 

ip_hash是根据ip的hash值取模，根据余数进行请求的分配，配置如下：

```nginx
    upstream test {
        ip_hash;
        server 127.0.0.1:8080;
        servsr 127.0.0.1:8081;
    }
```
如果用一台电脑访问Nginx，会发现所有的请求都会落到一个后端应用上。

##   fair  

按后端服务器的响应时间来分配请求，响应时间短的优先分配。

```nginx
    upstream test {
        fair;
        server 127.0.0.1:8080;
        servsr 127.0.0.1:8081;
    }
```
##   url_hash  

按访问 URL 的 hash 结果来分配请求，使每个 URL 定向到同一个后端服务器，后端服务器为缓存时比较适用。另外，在 `upstream` 中加入 `hash` 语句后， server 语句不能写入 weight 等其他参数。 

```nginx
    upstream test {
        hash  $request_uri;
        hash_method  crc32;
        server 127.0.0.1:8080;
        servsr 127.0.0.1:8081;  
    }
```
##  Session同步问题 

使用了**集群**之后，不得不考虑**Session同步**的问题了，如果不做Session同步，客户端登录了一个后台应用A之后，下一次请求落到了别的应用B，B应用会发现当前客户端没有登录，这就会出现重复让客户端登录的情况。

对于Nginx的集群，有的策略是不需要做Session同步策略的，例如ip_hash，一个ip始终会访问同一个节点。其他的策略都需要做Session同步。那么Session同步应该怎么做呢？

方式一

对于Tomcat，可以使用Tomcat的Session复制策略，见：http://tomcat.apache.org/tomcat-7.0-doc/cluster-howto.html

方式二

也可以使用Redis/memcache来做Session存储缓存，建议使用redis来缓存，可以参考：

[https://github.com/jcoleman/tomcat-redis-session-manager][3]

[https://github.com/izerui/tomcat-redis-session-manager][4]


</font>

[1]: http://www.jialeens.com/archives/314

[3]: https://github.com/jcoleman/tomcat-redis-session-manager
[4]: https://github.com/izerui/tomcat-redis-session-manager
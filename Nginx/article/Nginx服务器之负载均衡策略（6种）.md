## Nginx服务器之负载均衡策略（6种）

来源：[http://www.cnblogs.com/1214804270hacker/p/9325150.html](http://www.cnblogs.com/1214804270hacker/p/9325150.html)

时间 2018-07-17 18:33:00

## 一、关于Nginx的负载均衡   

在服务器集群中，Nginx起到一个代理服务器的角色（即反向代理），为了避免单独一个服务器压力过大，将来自用户的请求转发给不同的服务器。详情请查看我的另一篇博客。


## 二、Nginx负载均衡策略   

负载均衡用于从“upstream”模块定义的后端服务器列表中选取一台服务器接受用户的请求。一个最基本的upstream模块是这样的，模块内的server是服务器列表：

```nginx
    #动态服务器组
    upstream dynamic_zuoyu {
        server localhost:8080;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082;  #tomcat 8.5
        server localhost:8083;  #tomcat 9.0
    }
```

在upstream模块配置完成后，要让指定的访问反向代理到服务器列表：

```nginx    
        #其他页面反向代理到tomcat容器
        location ~ .*$ {
            index index.jsp index.html;
            proxy_pass http://dynamic_zuoyu;
        }
```

这就是最基本的负载均衡实例，但这不足以满足实际需求；目前Nginx服务器的upstream模块支持6种方式的分配：

  

#### 负载均衡策略

|-|-|
|-|-|
| 轮询 | 默认方式 |
| weight | 权重方式 |
| ip_hash | 依据ip分配方式 |
| least_conn | 最少连接方式 |
| fair（第三方） | 响应时间方式 |
| url_hash（第三方） | 依据URL分配方式 |
  

在这里，只详细说明Nginx自带的负载均衡策略，第三方不多描述。


## 1、轮询   

最基本的配置方法，上面的例子就是轮询的方式，它是upstream模块默认的负载均衡默认策略。每个请求会按时间顺序逐一分配到不同的后端服务器。

有如下参数：

|-|-|
|-|-|
| fail_timeout | 与max_fails结合使用。 |
| max_fails | 设置在fail_timeout参数设置的时间内最大失败次数，如果在这个时间内，所有针对该服务器的请求都失败了，那么认为该服务器会被认为是停机了， |
| fail_time | 服务器会被认为停机的时间长度,默认为10s。 |
| backup | 标记该服务器为备用服务器。当主服务器停止时，请求会被发送到它这里。 |
| down | 标记服务器永久停机了。 |
  

注意：

* 在轮询中，如果服务器down掉了，会自动剔除该服务器。     
* 缺省配置就是轮询策略。     
* 此策略适合服务器配置相当，无状态且短平快的服务使用。     

## 2、weight   

权重方式，在轮询策略的基础上指定轮询的几率。例子如下：

```nginx
    #动态服务器组
    upstream dynamic_zuoyu {
        server localhost:8080   weight=2;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082   backup;  #tomcat 8.5
        server localhost:8083   max_fails=3 fail_timeout=20s;  #tomcat 9.0
    }
```

在该例子中，weight参数用于指定轮询几率，weight的默认值为1,；weight的数值与访问比率成正比，比如Tomcat 7.0被访问的几率为其他服务器的两倍。

注意：

* 权重越高分配到需要处理的请求越多。     
* 此策略可以与least_conn和ip_hash结合使用。     
* 此策略比较适合服务器的硬件配置差别比较大的情况。     
  


## 3、ip_hash   

指定负载均衡器按照基于客户端IP的分配方式，这个方法确保了相同的客户端的请求一直发送到相同的服务器，以保证session会话。这样每个访客都固定访问一个后端服务器，可以解决session不能跨服务器的问题。

```LANG


#动态服务器组
    upstream dynamic_zuoyu {
        ip_hash;    #保证每个访客固定访问一个后端服务器
        server localhost:8080   weight=2;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082;  #tomcat 8.5
        server localhost:8083   max_fails=3 fail_timeout=20s;  #tomcat 9.0
    }
```

注意：

* 在nginx版本1.3.1之前，不能在ip_hash中使用权重（weight）。     
* ip_hash不能与backup同时使用。            
* 此策略适合有状态服务，比如session。            
* 当有服务器需要剔除，必须手动down掉。            
  


## 4、least_conn   

把请求转发给连接数较少的后端服务器。轮询算法是把请求平均的转发给各个后端，使它们的负载大致相同；但是，有些请求占用的时间很长，会导致其所在的后端负载较高。这种情况下，least_conn这种方式就可以达到更好的负载均衡效果。

```nginx
    #动态服务器组
    upstream dynamic_zuoyu {
        least_conn;    #把请求转发给连接数较少的后端服务器
        server localhost:8080   weight=2;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082 backup;  #tomcat 8.5
        server localhost:8083   max_fails=3 fail_timeout=20s;  #tomcat 9.0
    }
```

注意：

* 此负载均衡策略适合请求处理时间长短不一造成服务器过载的情况。     
  


## 5、第三方策略   

第三方的负载均衡策略的实现需要安装第三方插件。


### ①fair               

按照服务器端的响应时间来分配请求，响应时间短的优先分配。

```nginx
    #动态服务器组
    upstream dynamic_zuoyu {
        server localhost:8080;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082;  #tomcat 8.5
        server localhost:8083;  #tomcat 9.0
        fair;    #实现响应时间短的优先分配
    }
```


### ②url_hash

按访问url的hash结果来分配请求，使每个url定向到同一个后端服务器，要配合缓存命中来使用。同一个资源多次请求，可能会到达不同的服务器上，导致不必要的多次下载，缓存命中率不高，以及一些资源时间的浪费。而使用url_hash，可以使得同一个url（也就是同一个资源请求）会到达同一台服务器，一旦缓存住了资源，再此收到请求，就可以从缓存中读取。

```nginx
    #动态服务器组
    upstream dynamic_zuoyu {
        hash $request_uri;    #实现每个url定向到同一个后端服务器
        server localhost:8080;  #tomcat 7.0
        server localhost:8081;  #tomcat 8.0
        server localhost:8082;  #tomcat 8.5
        server localhost:8083;  #tomcat 9.0
    }
```


## 三、总结   

以上便是6种负载均衡策略的实现方式，其中除了轮询和轮询权重外，都是Nginx根据不同的算法实现的。在实际运用中，需要根据不同的场景选择性运用，大都是多种策略结合使用以达到实际需求。

## nginx官方文档 之 http负载均衡 学习笔记

来源：[https://www.cnblogs.com/xjnotxj/p/9322647.html](https://www.cnblogs.com/xjnotxj/p/9322647.html)

2018-07-17 11:55


## 一、负载均衡 算法

-----

大致可以分两类：

（1）不能保证用户的每一次请求都通过负载均衡到达同一服务器。

（2）可保证用户的每一次请求都通过负载均衡到达同一服务器。

第二类的应用场景：

1、如果服务器有 **`缓存`** 机制，让用户访问之前已缓存过的服务器可以加快响应速度。

2、若用户参与需要多个步骤，如：a.填写表单，b.下单并付款，c.提示购买成功。这些步骤需要 **`存储会话`** 状态才能使事务顺利进行。
 第（1）类： 

-----

### 1、默认算法：`Round Robin`（轮询）

-----

```nginx

http {
    upstream ub {
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```
`Round Robin（轮询）`还可以加上 **`服务器权重`** 

```nginx

http {
    upstream ub {
        server 10.117.0.1:3010 weight = 5;
        server 10.117.0.2:3010 weight = 3;
        server 10.117.0.3:3010 weight = 1;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}
```

解释：服务器收到请求数的比重是 5：3：1 
### 2、`Least Connections`（最少连接数）

-----

```nginx

http {
    upstream ub {
        least_conn;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}
```
### 3、`Least Time`（最短时间）【仅适用于 NGINX Plus】

-----

```nginx

http {
    upstream ub {
        hash $request_uri consistent;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

注：肯定比上面的都好使，毕竟是 NGINX Plus 才有的 **`收费`** 功能。

 第（2）类： 

-----

### 4、`IP Hash`（IP哈希）

-----

```nginx

http {
    upstream ub {
        ip_hash;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```
### 5、`Generic Hash`（通用哈希）

-----

```nginx

http {
    upstream ub {
        hash $request_uri consistent;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

解释：等于把上面`ip hash`变成了`$request_uri hash`。
### 6、启动 session 持久化【仅适用于NGINX Plus】

-----

下面三种方法都是基于cookie 机制，只是划分粒度越来越细。
##### （1）Sticky **`cookie`** 

```nginx

http {
    upstream ub { 
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
        sticky cookie srv_id expires=1h domain=.example.com path=/;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

| srv_id | expires | domain | path |
| - | - | - | - |
| cookie_name | 浏览器保留 cookie 的时间 | cookie 的域 | cookie 的路径 |


##### （2）Sticky **`route`** 

```nginx

http {
    upstream ub { 
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
        sticky route $route_cookie $route_uri;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

Nginx 会按照顺序搜索`$route_cookie`、`$route_uri`，并选择第一个非空的参数用作 route，下同。
##### （3）Sticky **`learn`** 

```nginx

http {
    upstream ub { 
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
        sticky learn
            create=$upstream_cookie_examplecookie
            lookup=$cookie_examplecookie
            zone=client_sessions:1m
            timeout=1h;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

这是一种比前两种方法更复杂的会话持久性方法。

参数`create`和`lookup`分别指定变量来指示如何创建新会话和搜索已经存在的会话。

会话存储在 shared memory zone，名字和大小在`zone`参数中配置。在64位平台上一个 megabyte zone可以存储大概 8000 个会话。在`timeout`参数指定的期间内没有被访问的会话将被从 zone 上移除，默认为 10 分钟。

-----

注：对于上述的`第（2）类`：

如果某个 server 不用了，若直接删掉这个 server，会打乱 hash 初始化分配的规则。

推荐的做法是，给server加上`down`，即不打破原有规则，同时让请求交给临近的下一台服务器处理。（如下面代码，10.117.0.2 的请求交给 10.117.0.3 来处理）。这样等宕机的服务器恢复，`“保证用户的每一次请求都通过负载均衡到达同一服务器”`的功能也会恢复正常。

```nginx
    upstream ub {
        ip_hash;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010 down;
        server 10.117.0.3:3010;
    } 
```
## 二、备份服务器

-----

```nginx

http {
    upstream ub {
        hash $request_uri consistent;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010;
        server 10.117.0.4:3010 backup;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

解释：

平时只是 10.117.0.1，10.117.0.2，10.117.0.3 服务器运行中，10.117.0.4 只是待命状态。 **`但当 3 台都宕机了后，第 4 台才会收到请求`** 。
 **`一旦有可用的节点恢复服务，该节点则不再使用，又进入后备状态`** 。
## 三、服务器慢启动

-----

```nginx

http {
    upstream ub {
        hash $request_uri consistent;
        server 10.117.0.1:3010 slow_start=30s;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}
```

好处：服务器慢启动功能可防止最近恢复的服务器被连接淹没，这可能会导致服务器再次被标记为失败。

缺点：如果 upstream 中只有一台服务器，那么`slow_start`参数会被忽略。
## 四、限制连接数量【仅适用于 NGINX Plus】

-----

```nginx
http {
    upstream ub { 
        server 10.117.0.1:3010 max_conns=3;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
        queue 100 timeout=70;
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}
```

1、如果`max_conns`的限制达到了，请求被放入 queue

2、如果 **``queue`排满或者在`timeout`时间内无法选择上游服务器`** ，客户端将接到一个错误。

-----

写在前面：

1、当 NGINX 认为某个服务器不可用时，它会暂时停止向服务器发送请求，等待`fail_timeout`后重试，直到它再次处于活动状态。

2、如果所有节点均失效，备机也为失效时，nginx 会对所有节点恢复为有效，重新尝试探测节点。

## 五、被动健康检查

-----

只有当请求发往服务器节点才能检查

```nginx

http {
    upstream ub { 
        server 10.117.0.1:3010;
        server 10.117.0.2:3010 max_fails=3 fail_timeout=30s;
        server 10.117.0.3:3010; 
    }
    
    server {
        location / {
            proxy_pass http://ub;
        }
    }
}

```

| 参数 | 解释 | 默认值 |
| - | - | - |
| max_fails | 失败的次数 | 1 |
| fail_timeout | 服务器被nginx标记为失效的时长 | 10 |


判断服务器`失效`的条件：

connect refuse / time out
## 六、主动健康检查

-----

在请求发往服务器前 nginx 就会定期自行检查

```nginx

http {
    upstream ub { 
        zone backend 64k;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
    }
    
    server {
        location / {
            proxy_pass http://ub;
            health_check interval=5 fails=3 passes=2 uri=/some/path;
        }
    }
}

```
`zone`参数定义了被 worker 进程共享的并用来存储服务器组配置的内存区域。
 **`健康监测的时间间隔是 10s，在失败 3 次后会认为是不可用的，以后需要两次通过监测才能认为是可用的。`** 

判断服务器`失效`的条件：

connect refuse / time out

```nginx

http {
    upstream ub { 
        zone backend 64k;
        server 10.117.0.1:3010;
        server 10.117.0.2:3010;
        server 10.117.0.3:3010; 
    }

    match server_ok {
        status 200-399;
        header Content-Type = text/html;
        body !~ "maintenance mode";
    }
    
    server {
        location / {
            proxy_pass http://ub;
            health_check match=server_ok;
        }
    }
}

```

判断服务器`失效`的条件：

除了 connect refuse / time out

还有 status != 200-399 / Content-Type != text/html / body ~ "maintenance mode"
## 七、与多个工作进程共享数据

待写
## 八、使用 DNS 配置 HTTP 负载平衡

待写

-----

参考资料

1.【 NGINX Docs | NGINX Load Balancing - HTTP Load Balancer 】[https://docs.nginx.com/nginx/admin-guide/load-balancer/http-load-balancer/][100]

2【 Nginx 的负载均衡原理 】[https://juejin.im/entry/585144e861ff4b00683eb92e][101]

[100]: https://docs.nginx.com/nginx/admin-guide/load-balancer/http-load-balancer/
[101]: https://juejin.im/entry/585144e861ff4b00683eb92e
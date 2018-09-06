## 反向代理 and 负载均衡

来源：[https://segmentfault.com/a/1190000009912062](https://segmentfault.com/a/1190000009912062)


## nginx 负载均衡的平衡机制

* `轮询`，向应用服务器的请求以循环方式分发。

* `最少连接`，下一个请求被分配给具有最少数量活动连接的服务器（最清闲的服务器）。

* `ip-hash`，哈希函数用于确定下一个请求（基于客户端的IP地址）应选择哪个服务器（相同IP 的发送到同一个服务器，解决 session 问题）。


## 轮训方式负载均衡

```nginx
// 代理服务器的配置文件
http {
    // 针对 http://test.com 域名的访问，将会按照默认 轮训的方式分配给列表中的服务器
    upstream http://test.com { 
        server srv1.example.com; // 服务器 A 
        server srv2.example.com; // 服务器 B
        server srv3.example.com; // 服务器 C
    }
}

// 具体负载均衡的服务器 A 配置文件
http {
    server { 
        listen 80;

        location / { 
            proxy_pass http://test.com; 
        } 
    } 
}

// 具体负载均衡的服务器 B 配置文件
http {
    server { 
        listen 80;

        location / { 
            proxy_pass http://test.com; 
        } 
    } 
}

....同上
```
## 最少连接数方式负载均衡

```nginx
// 代理服务器的配置文件
http {
    // 针对 http://test.com 域名的访问，将根据服务器的负载情况进行分配
    upstream http://test.com {
        least_conn; # 表示采取 最少连接数 的负载均衡机制
        server srv1.example.com; // 服务器 A 
        server srv2.example.com; // 服务器 B
        server srv3.example.com; // 服务器 C
    }
}

// 具体负载均衡的服务器 A 配置文件
http {
    server { 
        listen 80;

        location / { 
            proxy_pass http://test.com; 
        } 
    } 
}

....同上
```
## ip-hash 方式负载均衡
#### 解释

请注意，通过循环或最少连接的负载平衡，每个后续客户端的请求可能潜在地分配到不同的服务器。不能保证同一客户端始终被定向到同一个服务器。

使用`ip-hash`，客户端的IP地址用作哈希键，以确定应为客户端请求选择服务器组中的哪个服务器。该方法确保来自同一客户端的请求将始终被定向到同一台服务器，除非该服务器不可用。

解决 `session` 问题。

```nginx
// 代理服务器的配置文件
http {
    // 针对 http://test.com 域名的访问，将确保同一IP始终访问到同一服务器
    upstream http://test.com {
        ip_hash; # ip_hash 的负载均衡机制
        server srv1.example.com; // 服务器 A 
        server srv2.example.com; // 服务器 B
        server srv3.example.com; // 服务器 C
    }
}

// 具体负载均衡的服务器 A 配置文件
http {
    server { 
        listen 80;

        location / { 
            proxy_pass http://test.com; 
        } 
    } 
}

....同上
```
## 加权负载均衡

```nginx
  upstream myapp1 {
        server srv1.example.com weight=3;
        server srv2.example.com;
        server srv3.example.com;
    }
```

当 为服务器指定权重参数时，权重将作为负载均衡决策的一部分进行计算。

通过这种配置，每5个新请求将分布在应用程序实例中，如下所示：3个请求将被定向到srv1，一个请求将转到srv2，另一个请求将转到srv3。
## 服务器健康检查
`nginx`中的反向代理实现包括带内（或被动）服务器运行状况检查。如果特定服务器的响应失败并出现错误，nginx会将此服务器标记为失败，并尝试避免为此后续入站请求选择此服务器一段时间。
`max_fails`设置失败重试次数。`fail_timeout`设置重试间隔时间。默认情况下，`max_fails`设置为`1`。 当设置为`0`时，对该服务器禁用运行状况检查。该`fail_timeout`参数还定义如何，只要服务器失败将被标记。在 服务器故障后的 `fail_timeout`间隔之后，nginx将开始以实时客户端的请求来优雅地探测服务器。如果探针成功，则将服务器标记为实时的。

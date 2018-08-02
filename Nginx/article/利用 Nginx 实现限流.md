## 利用 Nginx 实现限流

来源：[http://blog.battcn.com/2018/07/27/service/nginx-limiting/](http://blog.battcn.com/2018/07/27/service/nginx-limiting/)

时间 2018-07-27 18:50:45


在当下互联网高并发时代中，项目往往会遇到需要限制客户端连接的需求。我们熟知的 Nginx 就提供了有这样的功能，可以简单的实现对客户端请求频率，并发连接和传输速度的限制….


## Nginx 限流  
`Nginx`为我们提供了请求限制模块（`ngx_http_limit_req_module`）、基于令牌桶算法的流量限制模块（`ngx_stream_limit_conn_module`），可以方便的控制令牌速率，自定义调节限流，实现基本的限流控制…


### 请求限制  

请求限制的功能来自于`ngx_http_limit_req_module`模块。使用它需要首先在 http 配置段中定义限制的参照标准和状态缓存区大小。
`limit_req_zone`只能配置在`http`范围内；
`$binary_remote_addr`表示客户端请求的IP地址；
`mylimit`自己定义的变量名；
`rate`请求频率，每秒允许多少请求；
`limit_req`与`limit_req_zone`对应，`burst`表示缓存住的请求数，也就是任务队列。

下面的配置就是定义了使用客户端的 IP 作为参照依据，并使用一个 10M 大小的状态缓存区。结尾的 rate=1r/s 表示针对每个 IP 的请求每秒只接受一次。

10M 的状态缓存空间够不够用呢？官方给出的答案是 1M 的缓存空间可以在 32 位的系统中服务 3.2 万 IP 地址，在 64 位的系统中可以服务 1.6 万 IP 地址，所以需要自己看情况调整。如果状态缓存耗光，后面所有的请求都会收到 503(Service Temporarily Unavailable) 错误。

脚本代码

  
    
```nginx
# 定义了一个 mylimit 缓冲区（容器），请求频率为每秒 1 个请求（nr/s）
limit_req_zone $binary_remote_addr zone=mylimit:10m rate=1r/s;
server {
	listen	70;
	location / {
		# nodelay 不延迟处理
        # burst 是配置超额处理,可简单理解为队列机制
        # 上面配置同一个 IP 没秒只能发送一次请求（1r/s），这里配置了缓存3个请求，就意味着同一秒内只能允许 4 个任务响应成功，其它任务请求则失败（503错误）
		limit_req zone=mylimit burst=3 nodelay;
		proxy_pass http://localhost:7070;
	}
}


```

测试代码

为了方便此处提供`JAVA、AB`两种测试代码..

  
    
```
# -n 即指定压力测试总共的执行次数
# -c 即指定的并发数
ab -n 5 -c 5 http://192.168.0.133:70/index


```

  
    
```java
package com.battcn.limiting;

import org.springframework.http.ResponseEntity;
import org.springframework.web.client.RestTemplate;

import java.util.concurrent.CompletableFuture;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * @author Levin
 * @since 2018/7/27 0027
 */
public class NginxLimiterTest {

    public static void main(String[] args) throws ExecutionException, InterruptedException {
        ExecutorService service = Executors.newFixedThreadPool(5);
        for (int i = 0; i < 6; i++) {
            CompletableFuture.supplyAsync(() -> {
                final ResponseEntity<String> entity = new RestTemplate().getForEntity("http://192.168.0.133:70/index", String.class);
                return entity.getBody();
            }, service).thenAccept(System.out::println);
        }
        service.shutdown();
    }
}


```

测试日志

此处提供 AB 测试结果 JAVA 的日志就不贴了，5个请求其中一个请求是有问题的，出问题的那个就是被拒绝请求的…

  
    
```
[root@localhost myconf]# ab -n 5 -c 5 http://192.168.0.133:70/index
Document Path:          /index
Document Length:        34 bytes

Concurrency Level:      5
Time taken for tests:   0.002 seconds
Complete requests:      5
Failed requests:        1
   (Connect: 0, Receive: 0, Length: 1, Exceptions: 0)


```

### 并发限制  

Nginx 并发限制的功能来自于`ngx_http_limit_conn_module`模块，跟请求配置一样，使用它之前，需要先定义参照标准和状态缓存区。
`limit_conn_zone`只能配置在`http`范围内；
`$binary_remote_addr`表示客户端请求的IP地址；
`myconn`自己定义的变量名（缓冲区）；
`limit_rate`限制传输速度
`limit_conn`与`limit_conn_zone`对应，限制网络连接数

下面的配置就是定义了使用客户端的 IP 作为参照依据，并使用一个 10M 大小的状态缓存区。限定了每个IP只允许建立一个请求连接，同时传输的速度最大为 1024KB

脚本代码

  
    
```nginx
# 定义了一个 myconn 缓冲区（容器）
limit_conn_zone $binary_remote_addr zone=myconn:10m;
server {
	listen	70;
	location / {
		# 每个 IP 只允许一个连接
		limit_conn myconn 1;
		# 限制传输速度（如果有N个并发连接，则是 N * limit_rate）
		limit_rate 1024k;
		proxy_pass http://localhost:7070;
	}
}


```

## 说点什么  

请求限流方面自己写一个简单的`Spring Boot`程序部署到服务器配置好 Nginx 映射即可，并发限流弄一个大文件下载，或者让自己服务接口在内部休眠一定时间就能测试出效果….

参考文献



* **`ngx_http_limit_req_module`**       [http://nginx.org/en/docs/http/ngx_http_limit_req_module.html][0]
    
* **`ngx_http_limit_conn_module`**       [http://nginx.org/en/docs/http/ngx_http_limit_conn_module.html][1]
    
* **`ngx_http_core_module`**       [http://nginx.org/en/docs/http/ngx_http_core_module.html][2]
    
  


## 总结  

限流不一定会提升性能，但使用好限流手段却可保障服务的稳定性、可靠性，使服务更为的健壮….



[0]: http://nginx.org/en/docs/http/ngx_http_limit_req_module.html
[1]: http://nginx.org/en/docs/http/ngx_http_limit_conn_module.html
[2]: http://nginx.org/en/docs/http/ngx_http_core_module.html
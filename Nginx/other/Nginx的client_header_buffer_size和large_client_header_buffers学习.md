## Nginx的client_header_buffer_size和large_client_header_buffers学习

来源：[http://blog.7rule.com/2018/04/01/nginx-header-buffer.html](http://blog.7rule.com/2018/04/01/nginx-header-buffer.html)

时间 2018-04-01 10:32:11


之前看到有人写的一篇关于nginx配置中large_client_header_buffers的问题排查的文章，其中提到：

large_client_header_buffers 虽然也可以在server{}内生效，但是只有 低于 nginx主配置中的值才有意义。

对这个结论，我心存疑虑，总觉得这种设计很奇怪，于是自己做了个测试，希望能了解的更深入一些。


## 测试方法

nginx主配置中加入配置项：（在主配置中将header大小控制在1k）

```nginx
http {
    include  mime.types;
    default_type  application/octet-stream;
    large_client_header_buffers  4 1k;
    ......
}
```

删除所有干扰vhost，仅留下一个：

```nginx
server {
    listen 80;
    server_name  www.job360.com;
    large_client_header_buffers  4 1m;
    ......
}
```

构造请求的shell：（构造header超过1k的请求）

```sh
#!/bin/bash

url="http://www.job360.com/test.html?debug=1"

for i in {0..1000}
do
    var="v$i"
    url="${url}&$var=$i"
done

curl $url -x 127.0.0.1:80 -v
```


## 第一次测试结果

测试得到的结果和之前看到的文章的结果不同，该长url请求成功被nginx处理。

什么情况啊？于是查看和文章中环境上的不同，发现很重要的一点：我只有这一个vhost。

于是添加了另外一个vhost，添加vhost配置如下：（没有设置 large_client_header_buffers）

```nginx
server {
    listen 80;
    server_name db.job360.com;
    ......}
```


## 第二次测试结果

测试发现，nginx依旧可以处理该长url请求。

再次思考不同点，想到：这些vhost是被主配置中include进来的，是否会和读取顺序有关呢？

于是再次调整配置，将两个vhost放到了一个conf文件中，配置如下：

```nginx
server {
    listen 80;
    server_name db.job360.com;
    ......
}

server {
    listen 80;
    server_name  www.job360.com;
    large_client_header_buffers  4 1m;
    ......
}
```


## 第三次测试结果

得到和文章中相同的结果，nginx返回414`Request-URI Too Large`。

带着好奇心，我颠倒了下两个vhost的顺序，如下：

```nginx
server {
    listen 80;
    server_name  www.job360.com;
    large_client_header_buffers  4 1m;
    ......
}

server {
    listen 80;
    server_name db.job360.com;
    ......
}
```


## 第四次测试结果

nginx成功处理该长url请求。


## 初步结论

通过上面的现象，我得到一个初步结论：在第一个vhost中配置的large_client_header_buffers参数会起作用。

好奇怪的现象啊，我对自己得出的结论也是心存疑惑，于是决定从手册中好好读下控制header_buffer相关的指令。


## 从手册上理解nginx有关header_buffer配置指令

从手册上找到有两个指令和header_buffer有关：



* [client_header_buffer_size][0]
    
* [large_client_header_buffers][1]
    
  

对nginx处理header时的方法，学习后理解如下：



* 先处理请求的request_line，之后才是request_header。
* 这两者的buffer分配策略相同。
* 先根据client_header_buffer_size配置的值分配一个buffer，如果分配的buffer无法容纳 request_line/request_header，那么就会再次根据large_client_header_buffers配置的参数分配large_buffer，如果large_buffer还是无法容纳，那么就会返回414（处理request_line）/400（处理request_header）错误。
  

根据对手册的理解，我理解这两个指令在配置header_buffer时的使用场景是不同的，个人理解如下：



* 如果你的请求中的header都很大，那么应该使用client_header_buffer_size，这样能减少一次内存分配。
* 如果你的请求中只有少量请求header很大，那么应该使用large_client_header_buffers，因为这样就仅需在处理大header时才会分配更多的空间，从而减少无谓的内存空间浪费。
  

为了印证自己对两个配置指令的理解，我把large_client_header_buffer换成client_header_buffer_size，重新跑上面的多种测试，得到了和之前各种场景相同的结论。

手册上也只是说明了这两个指令的使用场景，没有说更多的东西了，之前的疑惑还是没有得到解答，那么只有最后一招了，也是绝招：从源码中寻找答案！


## 源码学习

这里从client_header_buffer_size指令入手，先查看这个指令的定义部分：

```c
{ ngx_string("client_header_buffer_size"),
  NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_CONF_TAKE1,              //可以定义在http{}或server{}中，需要携带一个参数
  ngx_conf_set_size_slot,                                           //参数意义为size，使用nginx预定义的解析size参数方法解析
  NGX_HTTP_SRV_CONF_OFFSET,                                         //将参数值放到srv级别的conf中
  offsetof(ngx_http_core_srv_conf_t, client_header_buffer_size),    //解析后放到ngx_http_core_srv_conf_t结构体的client_header_buffer_size中
  NULL },

src/http/ngx_http_core_module.c
```

由定义看到，我们在server{}中解析到的值会和http{}中的值做一次merge，作为该server{}下的最终值。查看merge相关的逻辑：

```c
ngx_conf_merge_size_value(conf->client_header_buffer_size,        //conf代表server{}，prev代表http{}
                          prev->client_header_buffer_size, 1024); 

src/http/ngx_http_core_module.c
```

```c
#define ngx_conf_merge_size_value(conf, prev, default)                       \
    if (conf == NGX_CONF_UNSET_SIZE) {                                       \
        conf = (prev == NGX_CONF_UNSET_SIZE) ? default : prev;               \
    }

src/core/ngx_conf_file.h
```

从这段逻辑中得到结论：如果我们在server{}中配置了client_header_buffer_size，那么针对这个server{}块的最终值应该就是我们配置的值。

为了印证我的结论，我重新写了vhost配置，并在代码中加入调试信息，把最终结果打印出来：

```nginx
http {
    include  mime.types;
    default_type  application/octet-stream;
    large_client_header_buffers  4 1k;
    ......

    server {
        listen 80;
        server_name db.job360.com;
        ......
    }

    server {
        listen 80;
        server_name  www.job360.com;
        large_client_header_buffers  4 1m;
        ......
    }
}
```

调试代码：

```
printf("buffer before merge:\nchild: %lu\nparent: %lu\n\n", conf->client_header_buffer_size, prev->client_header_buffer_size);
......
    ngx_conf_merge_size_value(conf->client_header_buffer_size,
                              prev->client_header_buffer_size, 1024);
......
    printf("buffer after merge:\nchild: %lu\nparent: %lu\n\n", conf->client_header_buffer_size, prev->client_header_buffer_size);

src/http/ngx_http_core_module.c
```

重新编译nginx，测试每个server{}中client_header_buffer_size的最终值为：

```
buffer before merge:
child: 18446744073709551615    //由于第一个server{}中没有配置，所以这个是-1（NGX_CONF_UNSET_SIZE）的unsigned long int表示
parent: 1024    //http{}中配置为1k

buffer after merge:
child: 1024
parent: 1024

buffer before merge:
child: 1048576    //第二个server{}中配置为1m
parent: 1024

buffer after merge:
child: 1048576
parent: 1024
```

从值的最终结果看，的确是之前设置的1m，但是请求时却返回了414。

由于将两个server{}的位置颠倒后可以正常处理请求，所以在颠倒的情况下又测试了下最终值，输出如下：

```
buffer before merge:
child: 1048576
parent: 1024

buffer after merge:
child: 1048576
parent: 1024

buffer before merge:
child: 18446744073709551615
parent: 1024

buffer after merge:
child: 1024
parent: 1024
```

最终值的输出还是1m，但是这次就可以正常处理请求了。

看来nginx在实际处理请求的过程中，一定还有之前不知道的一套逻辑，用来判断client_header_buffer_size的最终值。

nginx处理请求时的相关代码如下：

```
ngx_http_core_srv_conf_t   *cscf;
......
    /* the default server configuration for the address:port */
    cscf = addr_conf->default_server;
......
    if (c->buffer == NULL) {
        c->buffer = ngx_create_temp_buf(c->pool,
                                        cscf->client_header_buffer_size);

src/http/ngx_http_request.c
```

这里真相大白：

原来client_header_buffer_size的最终值，是nginx在解析conf后，default_server中经过merge的最终值。

而default_server在nginx中的定义为：在    [listen][2]
指令中定义：

```
The default_server parameter, if present, will cause the server to become the default server for the specified address:port pair. If none of the directives have the default_server parameter then the first server with the address:port pair will be the default server for this pair.

```

为了验证这一点，我修改vhost配置为：

```nginx
server {
    listen 80;
    server_name db.job360.com;
    ......
}

server {
    listen 80 default;
    server_name  www.job360.com;

    large_client_header_buffers  1m;
    ......
}
```

重启nginx观察merge结果：

```
buffer before merge:
child: 18446744073709551615
parent: 1024

buffer after merge:
child: 1024
parent: 1024

buffer before merge:
child: 1048576
parent: 1024

buffer after merge:
child: 1048576
parent: 1024
```

merge结果没有不同。测试请求，这次nginx成功处理该请求，和预期的效果一致。


## 结束语

笔者又测试了large_client_header_buffers，得到和client_header_buffer_size同样的结果。可以得出结论：nginx在处理header时实际分配的buffer大小，是解析conf后，default_server中的最终值。

个人水平有限，上面的测试方法和理解如有不当的地方，还望大家指正，谢谢！



[0]: http://nginx.org/en/docs/http/ngx_http_core_module.html#client_header_buffer_size
[1]: http://nginx.org/en/docs/http/ngx_http_core_module.html#large_client_header_buffers
[2]: http://nginx.org/en/docs/http/ngx_http_core_module.html#listen
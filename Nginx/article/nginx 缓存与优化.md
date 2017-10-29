# nginx 缓存与优化

 时间 2017-10-13 13:45:50 

原文[http://kekefund.com/2017/10/13/nginx-cache/][1]


在浏览器和应用服务器之间，存在多种“潜在”缓存，如：

* 客户端浏览器缓存
* 中间缓存
* 内容分发网络（CDN）
* 服务器上的负载均衡和反向代理缓存，仅在反向代理和负载均衡的层面，就对性能提高有很大的帮助。

## 为什么要用缓存？ 

* 网站的访问速度更快
* 减轻源服务器的负担
* 提高负载平衡器、反向代理和应用服务器前端web服务的性能

## nginx代理模块缓存指令 

指令 | 说明 
-|-
proxy_cache | 定义缓存的共享内存区域 
proxy_cache_bypass | 一个或者多个字符串变量，变量的值为非空或者非零将会导致响应从上游服务器获取而不是缓存 
proxy_cache_key | 用来区分缓存文件的key，作为缓存key的一个字符串，用于存储或者获取缓存值。默认值为 scheme proxy_host uri is_args $args
proxy_cache_lock | 启用这个指令，当多个客户端请求一个缓存中不存在的文件（或称之为一个MISS），只有这些请求中的第一个被允许发送至服务器。其他请求在第一个请求得到满意结果之后在缓存中得到文件。 
proxy_cache_lock_timeout | 等待一个请求将出现在缓存或者proxy_cache_lock锁释放的时间长度 
proxy_cache_min_uses | 在一个响应被缓存为一个key之前需要请求的最小次数 
proxy_cache_path | 一个放置缓存响应和共享zone（keys_zone=name:size）的目录，用于存放key和响应的元数据。 
proxy_cache_path:keys_zone | 设置一个共享内存区，用于存储缓存键和元数据，有些类似计时器的用途。 
proxy_cache_path:levels | 冒号用于分隔在每个级别（1或2）的子目录名长度，最多三级深； 
proxy_cache_path:inactive | 在一个不活动的响应被驱除出缓存之前待在缓存中的最大时间长度；例如设置如60m，则文件如果在60分钟之内没有被请求，则缓存管理会自动将其在内存中删除，不管文件是否过期。 
proxy_cache_path:max_size | 缓存的最大值，当大小超过这个值，缓存管理器溢出最近最少使用的缓存条目； 
proxy_cache_path:loader_files | 缓存文件的最大数量，它们的元数据被每个缓存载入进程迭代载入； 
proxy_cache_path:loader_sleep | 在每个迭代缓存载入进程之间停顿的毫秒数； 
proxy_cache_path:loader_threshold | 缓存载入进程迭代花去时间的最大值 
proxy_cache_use_stale | 在访问上游服务器的时候发生错误，在这种情况下接受提供过期的缓存数据。参数updating告知NGINX在客户端请求的项目的更新正在原服务器中下载时发送旧内容，而不是向服务器转发重复的请求 
proxy_cache_valid | 缓存的有效期；指定对200、301或者302有效代码缓存的时间长度。特定参数any表示对任何响应都缓存一定时间长度。 
proxy_cache_methods | 缓存支持的方法，默认为GET，可以改为POST，OPTIONS等 

## 缓存策略 

* 首页缓存1分钟，因为它所包含的链接及文件列表经常更新
* 每篇文章都被缓存1天，因为一旦写完它们将不会改变，但我们又不希望缓存被填满，因此需要移除一些旧的缓存内容以便满足空间的需要。
* 尽量地缓存所有图像，因为从磁盘检索图像文件是一件比较“昂贵”的操作。

```nginx
    http {
        proxy_cache_path    /var/spool/nginx/articles    keys_zone=ARTICLES:16m    levels=1:2    inactive=1d;
        proxy_cache_path    /var/spool/nginx/images    keys_zone=IMAGES:128m    levels=1:2    inactive=30d;
        proxy_temp_path    /var/spool/nginx;
    
        server {
    
            location / {
                # 首页
                proxy_cache_valid 1m;
            }
            
            location /articles {
                proxy_cache_valid 1d;
            }
        
            location /img {
                proxy_cache_valid 10y;
            }
        }
    }
```

## 示例 

下面的配置设计缓存所有的响应6个小时，缓存大小为1GB。任何条目保存刷新，就是说，在6个小时内被调用为超时，有效期为1天。在此时间后，上游服务器将再次调用提供响应。如果上游服务器由于错误、超时、无效头或者是由于缓存条目被升级，那么过期的条目就会被使用。共享内存区、CACHE被定义为10MB，并且在location中使用，在这里设置缓存key，并且也可以从这里查询。 
```nginx
    http {
        proxy_temp_path  /var/spool/nginx;
    
        proxy_cache_path  /var/spool/nginx  keys_zone=CACHE:10m  levels=1:2  inactive=6h max_size=1g;
     
        server {
     
            location / {
                # using include to bring in a file with commonly-used settings
                include proxy.conf;
     
                proxy_cache CACHE;
     
                proxy_cache_valid any 1d;
     
                proxy_cache_use_stale error timeout invalid_header updating http_500 http_502 http_503 http_504;
     
                proxy_pass http://upstream;
            }
        }
    }
```

## 实践 

## 1，检测缓存状态 

通过添加如下代码实现 

```nginx
    server {
        listen 80;
        server_name xx.fofpower.com;
        location / {
            proxy_cache api_cache;
            proxy_cache_valid  200 206 304 301 302 1d;        
            proxy_ignore_headers Cache-Control;
            add_header X-Cache-Status $upstream_cache_status;  #添加此行
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_pass http://myapi;
        }
    }
```

浏览器上看到的状态可能有：

![][3]

![][4]

`$upstream_cache_status`可能值：

1，`MISS`——响应在缓存中找不到，所以需要在服务器中取得。

2，`HIT`——响应包含来自缓存的最新有效的内容

3，`EXPIRED`——缓存中的某一项过期了，来自原始服务器的响应包含最新的内容

4，`STALE`——内容陈旧是因为原始服务器不能正确响应。需要配置proxy_cache_use_stale

5，`UPDATING`——内容过期了，因为相对于之前的请求，响应的入口（entry）已经更新，并且proxy_cache_use_stale的updating已被设置

6，`REVALIDATED`——proxy_cache_revalidate命令被启用，NGINX检测得知当前的缓存内容依然有效(If-Modified-Since或者If-None-Match)

## 2，缓存POST请求 

NGINX默认支持GET请求的缓存，要增加POST，需要设置`proxy_cache_methods`。

对于POST请求，url相同，body内容不同，如果使用默认的`proxy_cache_key`，会造成不同的post请求，用了一个缓存键，返回给前端的数据会错乱。

解决方案是将post的请求参数也作为key的一部分。

```nginx
    server {
        listen 80;
        server_name www.fofeasy.com;
        add_header X-Cache-Status $upstream_cache_status;
        location / {
            proxy_cache web_cache;
            proxy_cache_valid  200 206 304 301 302 10d;
            proxy_cache_key $uri$request_body; #增加此行
            proxy_cache_methods GET POST;  #增加此行
            proxy_ignore_headers Cache-Control;
            proxy_redirect off;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_pass http://myweb;
        }
    }
```

## 参考 

* [NGINX缓存使用官方指南][5]
* [使用 Nginx 反代并缓存动态内容][6]


[1]: http://kekefund.com/2017/10/13/nginx-cache/

[3]: ../img/aeQb6bj.png
[4]: ../img/BB36Bvy.png
[5]: https://linux.cn/article-5945-1.html
[6]: https://blessing.studio/use-nginx-proxy-to-cache-dynamic-content/
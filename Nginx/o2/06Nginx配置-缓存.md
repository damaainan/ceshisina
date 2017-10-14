# Nginx配置-缓存

 时间 2017-08-30 12:04:40  

原文[http://www.jialeens.com/archives/307][1]

<font face=微软雅黑>
##  0x01、说明 

Nginx在做代理的过程中，会重复的从后台服务获取图片、css等静态资源，这些资源不是经常发送变化的，但是每次客户端请求时，都需要经过两个步骤，第一个步骤，Nginx先从后端服务器获取静态资源，然后再返回给客户端，可以明显的看出，这里经过了两次网络IO，同时后端服务的性能也是有限的，频繁处理静态资源对服务器性能也会有所影响，为了解决这个问题，咱们引入Nginx缓存。

##  0x02、原理 

Nginx之所以被大量使用，是因为Nginx可以将目标端服务器的指定资源缓存到了本地。例如，客户端在请求静态资源时，Nginx判断当前资源是否需要缓存，如果需要，则从目标服务器获取文件，并保存到Nginx服务器本地；下一次请求时，Nginx会直接从本地缓存目录中找到文件，直接返回给客户端。可以发现，此时，客户端的请求并不会传到后端服务器，而是在Nginx层直接返回了。这就是Nginx缓存的意义。

##  0x03、配置说明 

我们只需要两个命令就可以启用基础缓存： `proxy_cache_path`和`proxy_cache`。`proxy_cache_path`用来设置缓存的路径和配置，`proxy_cache`用来启用缓存。

`proxy_cache_path`和`proxy_cache`。`proxy_cache_path`用来设置缓存的路径和配置，`proxy_cache`用来启用缓存。

    proxy_cache_path /opt/nginx/cache levels=1:2 keys_zone=my_cache:10m max_size=10g inactive=60m 
                     use_temp_path=off;
    server {
    ...
        location / {
            proxy_cache my_cache;
            proxy_pass http://my_upstream;
        }
    }

相关的命令解释如下

* `/opt/nginx/cache` 用来指定缓存文件的路径
* `levels=1:2` 标识Nginx在`/opt/nginx/cache`目录下创建了两层目录结构，主要原因是如果将大量文件放在一个目录下，操作系统寻找文件会是否缓慢，所以这里建议创建两层目录，目录结构会在后面讲述。
* `//`  第一级目录为key的MD5加密的最后一位，第二级目录为key的MD5加密的倒数第2、3位，
* `keys_zone` 设置一个共享内存区，并且进行命名，该内存区用于存储缓存键和元数据，有些类似计时器的用途。将键的拷贝放入内存可以使NGINX在不检索磁盘的情况下快速决定一个请求是`HIT`还是`MISS`，这样大大提高了检索速度。一个1MB的内存空间可以存储大约8000个key，那么上面配置的10MB内存空间可以存储差不多80000个key。
* `max_size` 设置了缓存的上限（在上面的例子中是10G）。这是一个可选项；如果不指定具体值，那就是允许缓存不断增长，占用所有可用的磁盘空间。当缓存达到这个上线，处理器便调用 `cache manager` 来移除最近最少被使用的文件，这样把缓存的空间降低至这个限制之下。
* `inactive` 指定了项目在不被访问的情况下能够在内存中保持的时间。在上面的例子中，如果一个文件在60分钟之内没有被请求，则缓存管理将会自动将其在内存中删除，不管该文件是否过期。该参数默认值为10分钟（10m）。注意，非活动内容有别于过期内容。NGINX不会自动删除由缓存控制头部指定的过期内容（本例中`Cache-Control:max-age=120`）。过期内容只有在inactive指定时间内没有被访问的情况下才会被删除。如果过期内容被访问了，那么NGINX就会将其从原服务器上刷新，并更新对应的inactive计时器。

最后，将`proxy_cache`命令放入配置文件中，与location命令进行共同工作。

##  0x04、一个完整的例子 

```nginx
    http {
        #nginx允许客户端上传最大文件大小
        client_max_body_size 100m;
        include  mime.types;
        default_type  application/octet-stream;
    
        proxy_connect_timeout  90;
        proxy_read_timeout  90;
        proxy_send_timeout  90;
    
        proxy_buffer_size  16k;
        proxy_buffers  4 32k;
        proxy_busy_buffers_size  64k;
        proxy_temp_file_write_size  128k;
    
        #访问日志路径
        access_log  /usr/local/nginx/logs/access.log;
    
        #代理临时文件缓存
        proxy_temp_path  /usr/local/nginx/proxy_temp;
    
        #各个应用对应的css,js,图片缓存路径
        proxy_cache_path  /usr/local/nginx/proxy_cache_test  levels=1:2 keys_zone=cache_test:16m inactive=1h max_size=1g use_temp_path=off;
        
    
        sendfile  on;
        #不显示nginx版本号
        server_tokens off;
    
        keepalive_timeout  65;
    
        #开启压缩
        gzip  on;
    
        server {
            #nginx端口号
            listen  8080;
            server_name  localhost;
    
            location = /favicon.ico {
              log_not_found  off;
              access_log  off;
            }
            #联动配置
            location /test/ {
                proxy_set_header  REMOTE-HOST $remote_addr;
                proxy_set_header  Host $host:8080;
                proxy_set_header  X-Real-IP $remote_addr;
                proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_pass  http://172.25.3.1:8081; 
            }
            location  ~ ^/test/.*\.(gif|jpg|png|css|js|flv|ico|swf)(.*){
                proxy_cache_use_stale  updating error timeout invalid_header http_500 http_502;
                proxy_redirect  off;
                proxy_cache_key  $host$uri$is_args$args;
                proxy_set_header Host $host;
                proxy_cache_valid  200 304 1d;
                proxy_cache_valid  any 1h;
                proxy_cache_lock  on;
                proxy_cache_lock_timeout  5s;
                expires  1d;
                add_header  Nginx-Cache "$upstream_cache_status";
                etag  on;
                proxy_cache  cache_test;
                proxy_pass  http://172.25.3.1:8081; 
            }
        }
    }
```

可以看到上面的`location ~ /test/. .(gif|jpg|png|css|js|flv|ico|swf)(. )`配置，主要负责将图片、css、js等静态资源进行缓存。 

当访问此类资源时，会在对应的缓存目录下生成缓存文件。

进入`/usr/local/nginx/proxy_cache_test/1/50`目录，可以找到对应的缓存文件，打开后内容如下

![][3]

可以注意缓存文件内容有一个字段为KEY,这个key则是在Nginx里配置的规则 `proxy_cache_key $host$uri$is_args$args`;有兴趣的可以将这个KEY进行MD5加密，得出来的字符串和缓存的目录有一定的关系。

例如上面的例子加密结果就是`11257F7F7A319EE7685B56EC15C30A99`，可以发现，此文件的路径为`/9/A9`,。这就是上面配置 `proxy_cache_key` 和`levels=1:2`的作用。

</font>

[1]: http://www.jialeens.com/archives/307

[3]: ../img/7JB7faq.jpg
## 高并发中nginx较优的配置

来源：[https://segmentfault.com/a/1190000016385662](https://segmentfault.com/a/1190000016385662)


## 一、这里的优化主要是指对nginx的配置优化，一般来说nginx配置文件中对优化比较有作用的主要有以下几项：


* nginx进程数，建议按照cpu数目来指定，一般跟cpu核数相同或为它的倍数。

```nginx
worker_processes 8;
```


* 为每个进程分配cpu，上例中将8个进程分配到8个cpu，当然可以写多个，或者将一个进程分配到多个cpu。

```nginx
worker_cpu_affinity 00000001 00000010 00000100 00001000 00010000 00100000 01000000 10000000;
```


* 下面这个指令是指当一个nginx进程打开的最多文件描述符数目，理论值应该是系统的最多打开文件数（ulimit-n）与nginx进程数相除，但是nginx分配请求并不是那么均匀，所以最好与ulimit -n的值保持一致。

```nginx
worker_rlimit_nofile 65535;
```


* 使用epoll的I/O模型，用这个模型来高效处理异步事件

```nginx
use epoll;
```


* 每个进程允许的最多连接数，理论上每台nginx服务器的最大连接数为worker_processes*worker_connections。

```nginx
worker_connections 65535;
```


* http连接超时时间，默认是60s，功能是使客户端到服务器端的连接在设定的时间内持续有效，当出现对服务器的后继请求时，该功能避免了建立或者重新建立连接。切记这个参数也不能设置过大！否则会导致许多无效的http连接占据着nginx的连接数，终nginx崩溃！

```nginx
keepalive_timeout 60;
```


* 客户端请求头部的缓冲区大小，这个可以根据你的系统分页大小来设置，一般一个请求的头部大小不会超过1k，不过由于一般系统分页都要大于1k，所以这里设置为分页大小。分页大小可以用命令getconf PAGESIZE取得。

```nginx
client_header_buffer_size 4k;
```


* 下面这个参数将为打开文件指定缓存，默认是没有启用的，max指定缓存数量，建议和打开文件数一致，inactive是指经过多长时间文件没被请求后删除缓存。

```nginx
open_file_cache max=102400 inactive=20s;
```


* 下面这个是指多长时间检查一次缓存的有效信息。

```nginx
open_file_cache_valid 30s;
```


* open_file_cache指令中的inactive参数时间内文件的最少使用次数，如果超过这个数字，文件描述符一直是在缓存中打开的，如上例，如果有一个文件在inactive时间内一次没被使用，它将被移除。

```nginx
open_file_cache_min_uses 1;
```


* 隐藏响应头中的有关操作系统和web server（Nginx）版本号的信息，这样对于安全性是有好处的。

```nginx
server_tokens off;
```


* 可以让sendfile()发挥作用。sendfile()可以在磁盘和TCP socket之间互相拷贝数据(或任意两个文件描述符)。Pre-sendfile是传送数据之前在用户空间申请数据缓冲区。之后用read()将数据从文件拷贝到这个缓冲区，write()将缓冲区数据写入网络。sendfile()是立即将数据从磁盘读到OS缓存。因为这种拷贝是在内核完成的，sendfile()要比组合read()和write()以及打开关闭丢弃缓冲更加有效(更多有关于sendfile)。

```nginx
sendfile on;
```


* 告诉nginx在一个数据包里发送所有头文件，而不一个接一个的发送。就是说数据包不会马上传送出去，等到数据包最大时，一次性的传输出去，这样有助于解决网络堵塞。

```nginx
tcp_nopush on; 
```


* 告诉nginx不要缓存数据，而是一段一段的发送--当需要及时发送数据时，就应该给应用设置这个属性，这样发送一小块数据信息时就不能立即得到返回值。

```nginx
tcp_nodelay on;
```

比如：

```nginx
http { 
server_tokens off; 
sendfile on; 
tcp_nopush on; 
tcp_nodelay on; 
......
}
```


* 客户端请求头部的缓冲区大小，这个可以根据系统分页大小来设置，一般一个请求头的大小不会超过1k，不过由于一般系统分页都要大于1k，所以这里设置为分页大小。

```nginx
client_header_buffer_size 4k;
```


* 客户端请求头部的缓冲区大小，这个可以根据系统分页大小来设置，一般一个请求头的大小不会超过1k，不过由于一般系统分页都要大于1k，所以这里设置为分页大小。分页大小可以用命令getconf PAGESIZE取得。

```nginx
[root@test-huanqiu ~]# getconf PAGESIZE 
4096
```

但也有client_header_buffer_size超过4k的情况，但是client_header_buffer_size该值必须设置为“系统分页大小”的整倍数。


* 为打开文件指定缓存，默认是没有启用的，max 指定缓存数量，建议和打开文件数一致，inactive 是指经过多长时间文件没被请求后删除缓存。

```nginx
open_file_cache max=65535 inactive=60s;
```


* open_file_cache 指令中的inactive 参数时间内文件的最少使用次数，如果超过这个数字，文件描述符一直是在缓存中打开的，如上例，如果有一个文件在inactive 时间内一次没被使用，它将被移除。

```nginx
open_file_cache_min_uses 1;
```


* 指定多长时间检查一次缓存的有效信息。

```nginx
open_file_cache_valid 80s;
```

-----

下面是一个使用的简单的nginx配置文件：

    [root@dev-huanqiu ~]# cat /usr/local/nginx/conf/nginx.conf


```nginx
user   www www;
worker_processes 8;
worker_cpu_affinity 00000001 00000010 00000100 00001000 00010000 00100000 01000000;
error_log   /www/log/nginx_error.log   crit;
pid         /usr/local/nginx/nginx.pid;
worker_rlimit_nofile 65535;
 
events
{
   use epoll;
   worker_connections 65535;
}
 
http
{
   include       mime.types;
   default_type   application/octet-stream;
 
   charset   utf-8;
 
   server_names_hash_bucket_size 128;
   client_header_buffer_size 2k;
   large_client_header_buffers 4 4k;
   client_max_body_size 8m;
 
   sendfile on;
   tcp_nopush     on;
 
   keepalive_timeout 60;
 
   fastcgi_cache_path /usr/local/nginx/fastcgi_cache levels=1:2
                 keys_zone=TEST:10m
                 inactive=5m;
   fastcgi_connect_timeout 300;
   fastcgi_send_timeout 300;
   fastcgi_read_timeout 300;
   fastcgi_buffer_size 16k;
   fastcgi_buffers 16 16k;
   fastcgi_busy_buffers_size 16k;
   fastcgi_temp_file_write_size 16k;
   fastcgi_cache TEST;
   fastcgi_cache_valid 200 302 1h;
   fastcgi_cache_valid 301 1d;
   fastcgi_cache_valid any 1m;
   fastcgi_cache_min_uses 1;
   fastcgi_cache_use_stale error timeout invalid_header http_500; 
   open_file_cache max=204800 inactive=20s;
   open_file_cache_min_uses 1;
   open_file_cache_valid 30s; 
 
   tcp_nodelay on;
   
   gzip on;
   gzip_min_length   1k;
   gzip_buffers     4 16k;
   gzip_http_version 1.0;
   gzip_comp_level 2;
   gzip_types       text/plain application/x-javascript text/css application/xml;
   gzip_vary on;
 
   server
   {
     listen       8080;
     server_name   huan.wangshibo.com;
     index index.php index.htm;
     root   /www/html/;
 
     location /status
     {
         stub_status on;
     }
 
     location ~ .*\.(php|php5)?$
     {
         fastcgi_pass 127.0.0.1:9000;
         fastcgi_index index.php;
         include fcgi.conf;
     }
 
     location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|js|css)$
     {
       expires       30d;
     }
 
     log_format   access   '$remote_addr - $remote_user [$time_local] "$request" '
               '$status $body_bytes_sent "$http_referer" '
               '"$http_user_agent" $http_x_forwarded_for';
     access_log   /www/log/access.log   access;
       }
}

```
## 二、关于FastCGI的几个指令


* 这个指令为FastCGI缓存指定一个路径，目录结构等级，关键字区域存储时间和非活动删除时间。

```nginx
fastcgi_cache_path /usr/local/nginx/fastcgi_cache levels=1:2 keys_zone=TEST:10m inactive=5m;
```


* 指定连接到后端FastCGI的超时时间。

```nginx
fastcgi_connect_timeout 300;
```


* 向FastCGI传送请求的超时时间，这个值是指已经完成两次握手后向FastCGI传送请求的超时时间。

```nginx
fastcgi_send_timeout 300;
```


* 接收FastCGI应答的超时时间，这个值是指已经完成两次握手后接收FastCGI应答的超时时间。

```nginx
fastcgi_read_timeout 300;
```


* 指定读取FastCGI应答第一部分 需要用多大的缓冲区，这里可以设置为fastcgi_buffers指令指定的缓冲区大小，上面的指令指定它将使用1个 16k的缓冲区去读取应答的第一部分，即应答头，其实这个应答头一般情况下都很小（不会超过1k），但是你如果在fastcgi_buffers指令中指 定了缓冲区的大小，那么它也会分配一个fastcgi_buffers指定的缓冲区大小去缓存。

```nginx
fastcgi_buffer_size 16k;
```


* 指定本地需要用多少和多大的缓冲区来 缓冲FastCGI的应答，如上所示，如果一个php脚本所产生的页面大小为256k，则会为其分配16个16k的缓冲区来缓存，如果大于256k，增大 于256k的部分会缓存到fastcgi_temp指定的路径中， 当然这对服务器负载来说是不明智的方案，因为内存中处理数据速度要快于硬盘，通常这个值 的设置应该选择一个你的站点中的php脚本所产生的页面大小的中间值，比如你的站点大部分脚本所产生的页面大小为 256k就可以把这个值设置为16 16k，或者4 64k 或者64 4k，但很显然，后两种并不是好的设置方法，因为如果产生的页面只有32k，如果用4 64k它会分配1个64k的缓冲区去缓存，而如果使用64 4k它会分配8个4k的缓冲区去缓存，而如果使用16 16k则它会分配2个16k去缓存页面，这样看起来似乎更加合理。

```nginx
fastcgi_buffers 16 16k;
```


* 这个指令我也不知道是做什么用，只知道默认值是fastcgi_buffers的两倍。

```nginx
fastcgi_busy_buffers_size 32k;
```


* 在写入fastcgi_temp_path时将用多大的数据块，默认值是fastcgi_buffers的两倍。

```nginx
fastcgi_temp_file_write_size 32k;
```


* 开启FastCGI缓存并且为其制定一个名称。个人感觉开启缓存非常有用，可以有效降低CPU负载，并且防止502错误。但是这个缓存会引起很多问题，因为它缓存的是动态页面。具体使用还需根据自己的需求。

```nginx
fastcgi_cache TEST
```


* 为指定的应答代码指定缓存时间，如上例中将200，302应答缓存一小时，301应答缓存1天，其他为1分钟。

```nginx
fastcgi_cache_valid 200 302 1h;
fastcgi_cache_valid 301 1d;
fastcgi_cache_valid any 1m;
```


* 缓存在fastcgi_cache_path指令inactive参数值时间内的最少使用次数，如上例，如果在5分钟内某文件1次也没有被使用，那么这个文件将被移除。

```nginx
fastcgi_cache_min_uses 1;
```


* 不知道这个参数的作用，猜想应该是让nginx知道哪些类型的缓存是没用的。

```nginx
fastcgi_cache_use_stale error timeout invalid_header http_500;
```

----By 微风


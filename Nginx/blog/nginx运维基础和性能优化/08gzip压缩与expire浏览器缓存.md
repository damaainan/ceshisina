# 【nginx网站性能优化篇(1)】gzip压缩与expire浏览器缓存


## gzip压缩

### 概述

网页在服务器端经过了gzip或者其他格式的压缩后的输出明显减少了content-length字节,当访问过百万时,这些减少的字节就会变为客观的流量给节约下来;从而减轻服务器的压力以及网页的访问速度;

### 原理

客户端在向服务端发送http请求时,在请求头中有一个Accept-Encoding的头信息,该头信息告知服务器端本客服端能接收什么样的压缩文件,如果服务器端配置了压缩的需求,就会返回相应的压缩文件,然后浏览器再解码呈现出来;我们在做采集时,需要采集的是未压缩的文件,所以在http请求头上不要包含Accept-Encoding的键;

> 通过这个原理在php给app写接口时,可做一些安全方面的处理,具体如何实现,期待和有经验的app开发人员一起研究.

### Nginx的压缩

在http段添加如下配置

    
```nginx
gzip on ;  #是否开启gzip   on|off
gzip_buffers 32 4K  ; #缓冲(压缩在内存中缓冲几块? 每块多大?)  32 4K | 16 8K
gzip_comp_level [1-9] ; #推荐6 压缩级别(级别越高,压的越小,越浪费CPU计算资源)
gzip_disable ; #正则匹配UA 什么样的Uri不进行gzip
gzip_min_length 200 ; # 开始压缩的最小长度(再小就不要压缩了,意义不在)
gzip_http_version 1.1 ; # 开始压缩的http协议版本(可以不设置,目前几乎全是1.1协议)   1.0|1.1 
gzip_proxied          ; # 设置请求者代理服务器,该如何缓存内容
gzip_types text/plain  application/xml ; # 对哪些类型的文件用压缩 如txt,xml,html ,css
gzip_vary on  ; # 是否传输gzip压缩标志    on|off
```

**Example**

    

```nginx
gzip on;
gzip_min_length  1k;
gzip_buffers     4 16k;
gzip_http_version 1.1;
gzip_comp_level 2;
gzip_types     text/plain application/javascript application/x-javascript text/javascript text/css application/xml application/xml+rss;
gzip_vary on;
gzip_proxied   expired no-cache no-store private auth;
gzip_disable   "MSIE [1-6]\.";
```

**注意:**  
1. 图片/mp3这样的二进制文件,不必压缩,因为压缩率比较小, 比如100->80字节,而且压缩也是耗费CPU资源的.   
2. 比较小的文件不必压缩,意义不存在.

## expire浏览器缓存设置

### 概述

这里的缓存控制主要是针对图片,css,js等**变化周期较短的静态文件**;以图片为例,当我们第一次访问这张图片时,服务器返回的是200,同时在响应头返回了两个键,Etag:即该文件的'指纹'(唯一标识)以及Last-Modified:'文件的修改时间';此时浏览器,以及其他的缓存服务器就会把这张图片给缓存起来;再次请求这张图片时,请求头增加了两个键值,If-Modified-Since:上次发生改变的时间;If-None-Match:上次文件本身的Etag值,服务器根据这两个键值判断其Etag和Last-Modified,如果都没发生改变就不返回这张图片,只返回一个304的状态码,服务器接收到这个304的状态码就会自己去从缓存里面找这个被缓存的图片;   
这样就减少了服务器的带宽压力以及提升了网站访问速度;

### 配置

在location段以及if段可以设置

```nginx
location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
{
    expires      30d;
}
location ~ .*\.(js|css)?$
{
    expires      12h;
}
```

格式

    expires 30s;
    expires 30m;
    expires 2h;
    expires 30d;
    

**注意:服务器的日期要准确,如果服务器的日期落后于实际日期,可能导致缓存失效**


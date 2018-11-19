## 【Nginx实战】构建NGINX 4xx 5xx 状态码实例

来源：[https://segmentfault.com/a/1190000016901812](https://segmentfault.com/a/1190000016901812)

运营研发团队  张仕华
## nginx配置

```LANG
worker_processes  1;
events {
    worker_connections  1024;
}
http {
    include       mime.types;
    default_type  application/octet-stream;
    limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;
    sendfile        on;
    keepalive_timeout  65;
    server {
        listen       8070;
        server_name  10.96.79.14;
        limit_req zone=one;
        location / {
            root   html;
            index  index.html index.htm;
        }
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }
        location = /abc.html {
            root html;
            auth_basic           "opened site";
            auth_basic_user_file conf/htpasswd;
        }
         location ~ \.php$ {
            root /home/xiaoju/nginx-1.14.0/html;
            fastcgi_index index.php;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param       SCRIPT_FILENAME  /home/xiaoju/nginx-1.14.0/html$fastcgi_script_name;
            include fastcgi.conf;
            fastcgi_connect_timeout 300;
            fastcgi_send_timeout 300;
            fastcgi_read_timeout 300;
        }
    }
}
```

index.php

```LANG
<?php
echo "124";
```
## 4xx系列
## 400

NGX_HTTP_BAD_REQUEST

```LANG
Host头不合法
 
curl localhost:8070  -H 'Host:123/com'


<html>
<head><title>400 Bad Request</title></head>
<body bgcolor="white">
<center>
## 400 Bad Request
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
 
Content-Length头重复
curl localhost:8070  -H 'Content-Length:1'  -H 'Content-Length:2'


<html>
<head><title>400 Bad Request</title></head>
<body bgcolor="white">
<center>
## 400 Bad Request
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```
## 401

NGX_HTTP_UNAUTHORIZED

```LANG
参考如上nginx配置,访问abc.html需要认证
 
curl localhost:8070/abc.html


<html>
<head><title>401 Authorization Required</title></head>
<body bgcolor="white">
<center>
## 401 Authorization Required
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```
## 403

NGX_HTTP_FORBIDDEN

```LANG
chmod 222 index.html
将index.html设置为不可读
 
curl localhost:8070


<html>
<head><title>403 Forbidden</title></head>
<body bgcolor="white">
<center>
## 403 Forbidden
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```
## 404

NGX_HTTP_NOT_FOUND

```LANG
curl localhost:8070/cde.html


<html>
<head><title>404 Not Found</title></head>
<body bgcolor="white">
<center>
## 404 Not Found
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```
## 405

NGX_HTTP_NOT_ALLOWED

```LANG
使用非GET/POST/HEAD方法访问一个静态文件
curl -X DELETE localhost:8070/index.html -I


HTTP/1.1 405 Not Allowed
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 10:02:22 GMT
Content-Type: text/html
Content-Length: 173
Connection: keep-alive
```
## 5xx系列
## 500

NGX_HTTP_INTERNAL_SERVER_ERROR

修改index.php为

```LANG
<?php
echo "124"
```

缺少引号,语法错误

```LANG
curl localhost:8070/index.php -I


HTTP/1.1 500 Internal Server Error
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 11:29:19 GMT
Content-Type: text/html; charset=UTF-8
Connection: keep-alive
Set-Cookie: PHPSESSID=aoesvcuvbh1nh95kdkp152r9e1; path=/
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
```
## 501

NGX_HTTP_NOT_IMPLEMENTED

```LANG
nginx的transfer-encoding现在只支持chunked,如果客户端随意设置这个值,会报501
 
curl localhost:8070  -H 'Transfer-Encoding:1'


<html>
<head><title>501 Not Implemented</title></head>
<body bgcolor="white">
<center>
## 501 Not Implemented
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```
## 502

NGX_HTTP_BAD_GATEWAY

```LANG
修改nginx配置为
fastcgi_pass 127.0.0.1:8000;
```

指向一个未监听的端口

```LANG
curl localhost:8070/index.php -I


HTTP/1.1 502 Bad Gateway
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 11:28:17 GMT
Content-Type: text/html
Content-Length: 537
Connection: keep-alive
ETag: "5ad6113c-219"
```
## 503

NGX_HTTP_SERVICE_UNAVAILABLE

```LANG
修改nginx配置,限速为每分钟10个请求
 
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/m;
limit_req zone=one;
```

```LANG
连续发送两个请求，第二请求会报503
curl localhost:8070/index.php -I

HTTP/1.1 503 Service Temporarily Unavailable
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 11:31:43 GMT
Content-Type: text/html
Content-Length: 537
Connection: keep-alive
ETag: "5ad6113c-219"
```
## 504

NGX_HTTP_GATEWAY_TIME_OUT

修改index.php为

```LANG
<?php
echo "124";
sleep(5);
休息5秒钟
 
修改nginx配置为
三秒钟读超时
fastcgi_read_timeout 3;
```

```LANG
curl localhost:8070/index.php -I

HTTP/1.1 504 Gateway Time-out
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 12:17:57 GMT
Content-Type: text/html
Content-Length: 537
Connection: keep-alive
ETag: "5ad6113c-219"
```
## 505

NGX_HTTP_VERSION_NOT_SUPPORTED

```LANG
telnet8070端口,输入GET /index.html HTTP/2.1
不支持http/2.1,会报505
 
$telnet localhost 8070
Trying 127.0.0.1...
Connected to localhost.
Escape character is '^]'.
GET /index.html HTTP/2.1
HTTP/1.1 505 HTTP Version Not Supported
Server: nginx/1.14.0
Date: Tue, 18 Sep 2018 12:26:35 GMT
Content-Type: text/html
Content-Length: 203
Connection: close
<html>
<head><title>505 HTTP Version Not Supported</title></head>
<body bgcolor="white">
<center>
## 505 HTTP Version Not Supported
</center>

-----
<center>nginx/1.14.0</center>
</body>
</html>
```

后续基于这几种情况，看Nginx源码内部是怎么实现的。

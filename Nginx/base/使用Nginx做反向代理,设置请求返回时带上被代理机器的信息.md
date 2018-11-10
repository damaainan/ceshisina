## 使用Nginx做反向代理,设置请求返回时带上被代理机器的信息

来源：[https://www.ydstudio.net/archives/94.html](https://www.ydstudio.net/archives/94.html)

时间 2018-11-09 11:09:00


现在的大家经常使用Nginx做代理，例如用Nginx去代理Node。如果代理的Node过多，Node一旦出现问题我们怎么知道到底是哪个出了问题呢？于是就有了今天的文章，我们可以设置请求返回时带上被代理机器的一些信息。


* Nginx的配置
  

```nginx
upstream usa {
    server 127.0.0.1:3001; 
}

server {
    listen       80 ;
    server_name   xxx.com ; 
    error_log    /var/log/nginx/tianxingusa_error.log    error;
    access_log    /var/log/nginx/tianxingusa_accss.log    main;
    
    location / {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host  $http_host;
        proxy_set_header X-Nginx-Proxy true;

        add_header Proxy-Node $upstream_addr;
        add_header Proxy-Status $upstream_status;    
    
        proxy_http_version 1.1;
        proxy_pass    http://usa;
    }
   #省略部分信息
}
```

上面配置中

```nginx
add_header Proxy-Node $upstream_addr;
add_header Proxy-Status $upstream_status;
```

设置了Proxy-Node和Proxy-Status两个header，Proxy-Node显示的是被代理的节点，Proxy-Status显示的是被代理节点的状态，配置好之后重载Nginx的配置文件。我们就可以在 Response Headers 中看到相关的信息：

```
HTTP/1.1 200 OK
Server: nginx
Date: Fri, 09 Nov 2018 03:07:55 GMT
Content-Type: text/html; charset=UTF-8
Content-Length: 1227
Connection: keep-alive
X-Powered-By: Express
Accept-Ranges: bytes
Cache-Control: public, max-age=0
Last-Modified: Fri, 26 Oct 2018 10:11:49 GMT
ETag: W/"4cb-166afdbcd67"
Proxy-Node: 127.0.0.1:3001
Proxy-Status: 200
```

最后更新于2018-11-09  11:09:53 并被添加「nginx」标签，已有 2 位童鞋阅读过。


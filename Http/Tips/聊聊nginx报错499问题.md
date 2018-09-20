## 聊聊nginx报错499问题

来源：[https://segmentfault.com/a/1190000011853336](https://segmentfault.com/a/1190000011853336)


## 序

本文主要来聊一下nginx的access log当中出现的499问题。
## 问题描述
### 499 CLIENT CLOSED REQUEST

A non-standard status code introduced by nginx for the case when a client closes the connection while nginx is processing the request.
### 原因

服务器返回http头之前，客户端就提前关闭了http连接，常见于后台接口处理时间比较长，而前端请求又自带有超时时间。
## 复现
### 请求实例

```html
<!DOCTYPE html>
<html>
<head>
<script src="http://www.w3school.com.cn/jquery/jquery-1.11.1.min.js"></script>
<script>
$(document).ready(function(){
  $("button").click(function(){
      $.ajax({
    url : '/demo/test',
    timeout : 10000,
    type : 'get',
    dataType : 'json',
    success : function(data){
        alert('success');
    }
   });
  });
});
</script>
</head>
<body>

<button>ajax带超时时间请求</button>

</body>
</html>
```
### 后台接口

```java
@GetMapping("/test")
public String test(HttpServletResponse response) throws InterruptedException {
    Thread.sleep(100*1000);
    return "hello";
}
```
### nginx

```nginx
location /demo/ {
    access_log  /usr/local/var/log/nginx/host.access.log  main;
    proxy_pass http://localhost:8080/demo/ ;
}
```

关于log format如下

```nginx
log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';
```
### log实例

```
127.0.0.1 - - [04/Nov/2017:01:11:29 +0800] "GET /demo/test HTTP/1.1" 499 0 "http://localhost:8888/demo.html" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36" "-"
127.0.0.1 - - [04/Nov/2017:01:11:42 +0800] "GET /demo/test HTTP/1.1" 499 0 "http://localhost:8888/demo.html" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36" "-"
127.0.0.1 - - [04/Nov/2017:01:11:58 +0800] "GET /demo/test HTTP/1.1" 499 0 "http://localhost:8888/demo.html" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36" "-"
```
## doc


* [499 CLIENT CLOSED REQUEST][0]
* [服务器排障 之 nginx 499 错误的解决][1]


[0]: https://httpstatuses.com/499
[1]: http://yucanghai.blog.51cto.com/5260262/1713803
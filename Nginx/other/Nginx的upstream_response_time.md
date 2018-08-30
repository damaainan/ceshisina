## Nginx的upstream_response_time

来源：[https://www.tlanyan.me/upstream_response_time-of-nginx/](https://www.tlanyan.me/upstream_response_time-of-nginx/)

时间 2018-08-27 14:29:17

 
转载请注明文章出处： [https://tlanyan.me/upstream_response_time-of-nginx][2]
 
前几日为了查看FPM的性能，在Nginx的配置里增加FPM响应时间的`header`:

```nginx
http {
  ...
  server {
    ...
    location ~ \.php$ {
      ...
      add_header X-Upstream-Time $upstream_response_time;
    }
  }
}
```
 
今天闲来查看网页的响应头，发现值与预期的不一致：
 
![][0]
 
要说153毫秒我是相信的，那么数值的单位是纳秒。但这不符合常理：1. 印象中`upstream_response_time`的单位是毫秒；2. 如果单位是纳秒，就不应该有小数点，精度没这么高（从L1缓存取个值就要0.5~1纳秒，从寄存器取值差不多也要个0.2纳秒）。
 
难道是我对`upstream_response_time`理解错了？翻看Nginx官方文档，对该变量的解释是：

```
$upstream_response_time
 
   keeps time spent on receiving the response from the upstream server; the time is kept in seconds with millisecond resolution. Times of several responses are separated by commas and colons like addresses in the $upstream_addr variable.
```
 
翻译过来：`upstream_response_time`是与上游（FPM）建立连接开始到接收完内容花费的时间，单位为毫秒。所以理解没有错，那么错在什么地方呢？
 
所以Nginx版本的bug？试了另外几个版本，情况一致。
 
搜索”nginx upstream_response_time”，出现的内容基本上是`request_time`和`upstream_response_time`的区别。这些博文中提到的定义，与上面理解的也是一样的。`<https://www.nginx.com>`是官方提供付费商业支持的站点，根据其站点上 [“Using NGINX Logging for Application Performance Monitoring”][3] 这篇博文，这个值是靠谱的（坑社区也就算了，不能坑给钱的上帝吧）。
 
再仔细琢磨这个值，发现怎么有点像时间戳啊？！马上用PHP验证一下：

```
php -a
echo date('Y-m-d H:i:s', 1535347303.280);
```
 
PHP shell输出”2018-08-27 13:21:43″，证明其就是时间戳。
 
没给预期的上游处理时间，给一个时间戳算什么事？接续Google “nginx upstream_response_time timestamp”，结果列表第一个标题似乎就是我的疑问：”Re: nginx report a timestamp on upstream_response_time”。点进去一看，是官方邮件组中某个讨论的回复自动贴在了官方论坛上。除了知道`upstream_response_time`初始化为当前值（`ngx_timeofday()`），暂无对问题解惑的有用信息。
 
继续往下翻，马上就看到了有人在`OpenResty`提出的issue： [[bug] the upstream-response-time value is wrong #206][4] 。根据Nginx与OpenResty的关系，这个issue肯定值得看看。章亦春大佬对该issue的回复（也是对upstream_response_time是时间戳的解答）是：
 
![][1]
 
所以`upstream_response_time`在header中不准确的原因是：其值在log阶段(NGX_HTTP_LOG_PHASE)才会正确生成，发送响应头处于内容生产阶段(NGX_HTTP_CONTENT_PHASE)，期间获取到的值是初始化的时间戳，符合预期。
 
要正确打印其值，可在日志格式中声明：

```
http {
  ...
  log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
        '$status $body_bytes_sent "$http_referer" '
        '"$http_user_agent" "$http_x_forwarded_for" "$request_time" "$upstream_response_time"';
}
```
 
重新加载Nginx配置，刷新网页然后查看日志，每一行最后一列就是我们想要的`upstream_response_time`：

```
xxxx - - [27/Aug/2018:14:20:13 +0800] "GET xxx HTTP/1.1" 200 7659 "xxx" "Mozilla/5.0 (iPhone; CPU iPhone OS 10_2_1 like Mac OS X) AppleWebKit/602.4.6 (KHTML, like Gecko) Mobile/14D27 MicroMessenger/6.5.5 NetType/WIFI Language/zh_CN" "-" "0.000" "-"
xxx - - [27/Aug/2018:14:20:16 +0800] "GET xxx HTTP/1.1" 200 423 "xxx" "Mozilla/5.0 (iPhone; CPU iPhone OS 10_2_1 like Mac OS X) AppleWebKit/602.4.6 (KHTML, like Gecko) Mobile/14D27 MicroMessenger/6.5.5 NetType/WIFI Language/zh_CN" "-" "0.000" "-"
xxx - - [27/Aug/2018:14:20:29 +0800] "GET / HTTP/1.0" 200 6775 "-" "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36" "-" "0.185" "0.010"
```
 
## 参考

 
* [http://nginx.org/en/docs/http/ngx_http_upstream_module.html#var_upstream_response_time][5]  
* [https://www.nginx.com/blog/using-nginx-logging-for-application-performance-monitoring/][6]  
* [https://forum.nginx.org/read.php?29,256539,256556][7]  
* [https://github.com/openresty/openresty/issues/206][8]  
* [https://blog.csdn.net/qinyushuang/article/details/44567885][9]  
 


[2]: https://tlanyan.me/upstream_response_time-of-nginx
[3]: https://www.nginx.com/blog/using-nginx-logging-for-application-performance-monitoring/
[4]: https://github.com/openresty/openresty/issues/206
[5]: http://nginx.org/en/docs/http/ngx_http_upstream_module.html#var_upstream_response_time
[6]: https://www.nginx.com/blog/using-nginx-logging-for-application-performance-monitoring/
[7]: https://forum.nginx.org/read.php?29,256539,256556
[8]: https://github.com/openresty/openresty/issues/206
[9]: https://blog.csdn.net/qinyushuang/article/details/44567885
[0]: ../img/zQZNbyJ.png
[1]: ../img/ZRJrmqY.png
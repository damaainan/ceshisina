## nginx 的 rewrite 理解起来也挺费劲

来源：[https://mp.weixin.qq.com/s/AdN6taSQpgeHbw3xHSKbkg](https://mp.weixin.qq.com/s/AdN6taSQpgeHbw3xHSKbkg)

时间 2018-11-24 14:42:09

 
这周三，有个老同事和我讨论了一个 HTTPS 问题，实质上却是 Nginx 的 rewrite 规则问题，我仔细阅读了官方文档，发现解释的不是特别明了，逐写了这篇博文。
 
rewrite 的语法规则如下：

```nginx
Syntax:  rewrite regex replacement [flag]; Context:  server, location, if


```
 
我们暂时不考虑 flag 参数，先从简单的说起，首先务必要理解的就是 rewrite 规则在 server 段和 location 段中语义是不一样的。
 
在一个 URL 请求中，rewrite 如果匹配到 regex，那么 URL 就会替换成 replacement，如果不考虑 flag，匹配规则是顺序执行的，即使匹配到了，仍然会继续匹配下去。
 
记住，如果 replacement 包含 “http://”, “https://”, or “$scheme”，那么匹配会理解终止，并直接重定向地址给客户端。
 
接下去说说 flag 可选参数，如果有 flag 参数，rewrite 会进一步处理指令。flag 参数有四个：

 
* last：stops processing the current set of ngx_http_rewrite_module directives and starts a search for a new location matching the changed URI;
  
* break：stops processing the current set of ngx_http_rewrite_module directives as with the break directive;
  
* redirect：returns a temporary redirect with the 302 code;
  
* permanent：returns a permanent redirect with the 301 code.

 
flag 参数如果是 redirect 或 permanent，那么处理就相对简单，立刻中止规则匹配，进行 302 或 301 跳转。
 
从文档看，last 和 break 看不出太大区别，我总结就是如果在 location 中配置 flag 是 last，立刻跳出本 location 的匹配，同时会顺序继续搜寻其他 location 的匹配，如果还没匹配到，还会继续搜寻本 location；而 break 跳出本 location 后就不会再匹配其它 location 了。通过个例子说明：

```nginx
location / {
    rewrite ^/a /b last;
    rewrite ^/b /c last;  
}
location = /b {
    return 401;
}
location = /c {
    return 402;
}
```
 
如果访问 https://www.simplehttps.com/a/，则返回 401；如果访问 https://www.simplehttps.com/b/，则返回 402。
 
接下去修改配置：

```nginx
location / {
   rewrite ^/a /b break;
   rewrite ^/b /c break;  
}
location = /b {
    return 401;
}
location = /c {
    return 402;
}
```
 
结果就是，不管访问 https://www.simplehttps.com/a/ 还是 https://www.simplehttps.com/b/，都返回 404。
 
那么如果 flag 配置在 server 段内，会发生什么呢？不管是 break 还是 last，其行为规则是一样的，不会有跳出的行为，会顺序执行。

```nginx
rewrite ^/a /b break; # 或者 rewrite ^/a /b last;
rewrite ^/b /c break; # 或者 rewrite ^/b /c last;  
location = /b {
    return 401;
}
location = /c {
    return 402;
}
```
 
那么文档中描述的死循环什么情况呢？以下的例子在 location 中就会死循环：

```nginx
location /download/ {
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 last;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  last;
    return  403;
}
```
 
原因就在于 rewrite 匹配后还是 /download 开头，如果是 last，会继续走到 location /download/ { 段内，从而会死循环，最终产生 500 错误，所以这种情况下，建议将 last 修改为 break。
 
其实事情也没有那么复杂，取决于你清楚自己想达到什么目标，然后在 last 和 break 之间取舍。
 
我的书《深入浅出HTTPS：从原理到实战》代码实例已经更新到 github 上了，地址是 https://github.com/ywdblog/httpsbook，欢迎一起讨论。



[0]: https://img1.tuicool.com/IBJNNfe.png
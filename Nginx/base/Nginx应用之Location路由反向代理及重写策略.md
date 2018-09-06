## Nginx应用之Location路由反向代理及重写策略

来源：[http://www.etongwl.com/blogphp/archives/1392.html](http://www.etongwl.com/blogphp/archives/1392.html)

时间 2018-02-18 00:28:48

#### 一、常用设置

1､日志格式

```nginx
log_format main '$time_iso8601|$remote_addr|$remote_user|$request_method|$uri|'
          '$status|$request_time|$request_length|$body_bytes_sent|$bytes_sent|'
          '$connection|$http_x_forwarded_for|$upstream_addr|$upstream_status|'
          '$upstream_response_time|$args|$http_referer|$http_user_agent';
access_log  logs/access.log  main;

```

2､反向代理透传客户端IP设置

```nginx
proxy_set_header Host $http_host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

```

3､全局变量

```
$args #这个变量等于请求行中的参数。
$content_length #请求头中的Content-length字段。
$content_type #请求头中的Content-Type字段。
$document_root #当前请求在root指令中指定的值。
$host #请求主机头字段，否则为服务器名称。
$http_user_agent #客户端agent信息
$http_cookie #客户端cookie信息
$limit_rate #这个变量可以限制连接速率。
$request_body_file #客户端请求主体信息的临时文件名。
$request_method #客户端请求的动作，通常为GET或POST。
$remote_addr #客户端的IP地址。
$remote_port #客户端的端口。
$remote_user #已经经过Auth Basic Module验证的用户名。
$request_filename #当前请求的文件路径，由root或alias指令与URI请求生成。
$query_string #与$args相同。
$scheme #HTTP方法（如http，https）。
$server_protocol #请求使用的协议，通常是HTTP/1.0或HTTP/1.1。
$server_addr #服务器地址，在完成一次系统调用后可以确定这个值。
$server_name #服务器名称。
$server_port #请求到达服务器的端口号。
$request_uri #包含请求参数的原始URI，不包含主机名，如：”/foo/bar.php?arg=baz”。
$uri #不带请求参数的当前URI，$uri不包含主机名，如”/foo/bar.html”。
$document_uri #与$uri相同。
```

#### 二、Rewrite规则

语法：rewrite 正则 替换 标志位  

flag标记（rewrite指令的最后一项参数）：

1.`last`  last是终止当前location的rewrite检测,但会继续重试location匹配并处理区块中的rewrite规则。  

2.`break`  break是终止当前location的rewrite检测,而且不再进行location匹配。  

3.`redirect`  返回302临时重定向，浏览器地址会显示跳转后的URL地址。  

4.`permanent`  返回301永久重定向，浏览器地址会显示跳转后的URL地址。  

例：

```nginx
# 正则匹配
location ~ ^/(a|bb|ccc)/ {
    rewrite ^/([a-z]+)/(.*)$ http://106.185.48.229/$2?$1;
}
# 注：用括号括起来的参数为后面的 $1 $2 变量

```

#### 三、反向代理的路由策略

Location的配置：

语法：
 
```nginx

location [=|~|~*|^~] /uri/ {…}

```

语法说明：

 `=   开头表示精确匹配，不支持正则。`    

 `^~  开头表示uri以某个常规字符串开头，不支持正则，理解为匹配url路径即可。`    

 `~和~* 开头表示区分大小写的和不区分大小写的正则匹配。`    

 `!~和!~* 开头表示区分大小写不匹配及不区分大小写不匹配的正则匹配。`    

 `/ 通用匹配，任何请求都会匹配，通常放着配置的最后。`    

#### 匹配优先级：

`=` > `^~` > `~, ~*` > `空`              
`全匹配` > `路径匹配` > `正则匹配` > `字符串匹配`              

示例：
   
```nginx
# 字符串匹配
location /static {
    alias  /home/www/static;
    access_log off;
}
# 路径匹配，此时proxy_pass的结束 / 决定是否带上匹配的路径
location ^~ /333/ {
    proxy_pass http://106.185.48.229/;
}
# 正则匹配，此时proxy_pass不能带结束 /
location ~ ^/(xxx|yyy)/ {
    proxy_pass http://106.185.48.229;
}
# 字符串匹配，此时proxy_pass的结束 / 决定是否带上匹配得路径
location /zzz/ {
    proxy_pass http://106.185.48.229/;
}
# 默认匹配
location / {
    proxy_pass http://127.0.0.1:8080;
}

```


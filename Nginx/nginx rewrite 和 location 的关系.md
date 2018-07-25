## nginx rewrite 和 location 的关系

来源：[http://blog.phpdr.net/nginx-rewrite-和-location-的关系.html](http://blog.phpdr.net/nginx-rewrite-和-location-的关系.html)

时间 2018-05-20 13:42:31


之前在配置nginx时，总是遇到rewrite指令的last和break标识的问题，看到的资料大都是last 基本上都用这个 Flag，break 中止 Rewirte，不在继续匹配。看完之后还是有点懵，后来看了下rewrite模块的文档，终于搞懂了，这个模块内容也不是太多，索性整个把这个模块都好好整理下吧

ngx_http_rewrite_module 模块用来使用正则表达式（PCRE）改变请求的URI，返回重定向，并有条件地选择配置。


## 指令执行顺序

* 首先顺序执行server块中的rewrite模块指令，得到rewrite后的请求URI
* 然后循环执行如下指令
```
> 如果没有遇到中断循环标志，此循环最多执行10次，但是我们可以使用break指令来中断rewrite后的新一轮的循环
```

(1). 依据rewrite后的请求URI，匹配定义的 location 块

(2). 顺序执行匹配到的 location 中的rewrite模块指令


## 指令


### break

> Context: server, location, if


停止执行 ngx_http_rewrite_module 的指令集，但是其他模块指令是不受影响的

例子说明

```nginx
server {
    listen 8080;
    # 此处 break 会停止执行 server 块的 return 指令(return 指令属于rewrite模块)
    # 如果把它注释掉 则所有请求进来都返回 ok
    break;
    return 200 "ok";
    location = /testbreak {
        break;
        return 200 $request_uri;
        proxy_pass http://127.0.0.1:8080/other;
    }
    location / {
        return 200 $request_uri;
    }
}

# 发送请求如下
# curl 127.0.0.1:8080/testbreak
# /other

# 可以看到 返回 `/other` 而不是 `/testbreak`，说明 `proxy_pass` 指令还是被执行了
# 也就是说 其他模块的指令是不会被 break 中断执行的
# (proxy_pass是ngx_http_proxy_module的指令)
```


### if

> Context: server, location

依据指定的条件决定是否执行 if 块语句中的内容


#### if 中的几种 判断条件

* 一个`变量名`，如果变量 $variable 的值为空字符串或者字符串"0"，则为false    
* `变量`与一个字符串的比较 相等为(=) 不相等为(!=)`注意此处不要把相等当做赋值语句啊`
* `变量`与一个正则表达式的模式匹配 操作符可以是(`~`区分大小写的正则匹配，`~*`不区分大小写的正则匹配，`!~``!~*`，前面两者的非)    
* 检测文件是否存在 使用`-f`(存在) 和`!-f`(不存在)    
* 检测路径是否存在 使用`-d`(存在) 和`!-d`(不存在) 后面判断可以是字符串也可是变量    
* 检测文件、路径、或者链接文件是否存在 使用`-e`(存在) 和`!-e`(不存在) 后面判断可以是字符串也可是变量    
* 检测文件是否为可执行文件 使用`-x`(可执行) 和`!-x`(不可执行) 后面判断可以是字符串也可是变量    

注意 上面 第1，2，3条被判断的必须是 变量， 4, 5, 6, 7则可以是变量也可是字符串

```nginx
set $variable "0"; 
if ($variable) {
    # 不会执行，因为 "0" 为 false
    break;            
}

# 使用变量与正则表达式匹配 没有问题
if ( $http_host ~ "^star\.igrow\.cn$" ) {
    break;            
}

# 字符串与正则表达式匹配 报错
if ( "star" ~ "^star\.igrow\.cn$" ) {
    break;            
}
# 检查文件类的 字符串与变量均可
if ( !-f "/data.log" ) {
    break;            
}

if ( !-f $filename ) {
    break;            
}
```


### return

> Context: server, location, if

```nginx
return code [text];
return code URL;
return URL;
```

停止处理并将指定的`code`码返回给客户端。 非标准`code`码 444 关闭连接而不发送响应报头。


从`0.8.42`版本开始，`return`语句可以指定重定向`url`(状态码可以为如下几种 301,302,303,307),

也可以为其他状态码指定响应的文本内容，并且重定向的`url`和响应的文本可以包含`变量`。

有一种特殊情况，就是重定向的`url`可以指定为此服务器本地的`urI`，这样的话，`nginx`会依据请求的协议`$scheme`，`server_name_in_redirect`和`port_in_redirect`自动生成完整的`url`（此处要说明的是`server_name_in_redirect`和`port_in_redirect`指令是表示是否将`server`块中的`server_name`和`listen`的端口 作为`redirect`用 ）

```nginx
# return code [text]; 返回 ok 给客户端
location = /ok {
    return 200 "ok";
}

# return code URL; 临时重定向到 百度
location = /redirect {
    return 302 http://www.baidu.com;
}

# return URL; 和上面一样 默认也是临时重定向
location = /redirect {
    return http://www.baidu.com;
}
```


### rewrite

> Context: server, location, if

```nginx
rewrite regex replacement [flag];
```
`rewrite`指令是使用指定的正则表达式`regex`来匹配请求的`urI`，如果匹配成功，则使用`replacement`更改`URI`。`rewrite`指令按照它们在配置文件中出现的顺序执行。可以使用`flag`标志来终止指令的进一步处理。如果替换字符串`replacement`以`http：//`，`https：//`或`$ scheme`开头，则停止处理后续内容，并直接重定向返回给客户端。

第一种情况 重写的字符串 带`http://`

```nginx
location / {
    # 当匹配 正则表达式 /test1/(.*)时 请求将被临时重定向到 http://www.$1.com
    # 相当于 flag 写为 redirect
    rewrite /test1/(.*) http://www.$1.com;
    return 200 "ok";
}
# 在浏览器中输入 127.0.0.1:8080/test1/baidu 
# 则临时重定向到 www.baidu.com
# 后面的 return 指令将没有机会执行了
```

第二种情况 重写的字符串 不带`http://`

```nginx
location / {
    rewrite /test1/(.*) www.$1.com;
    return 200 "ok";
}
# 发送请求如下
# curl 127.0.0.1:8080/test1/baidu
# ok

# 此处没有带http:// 所以只是简单的重写。请求的 uri 由 /test1/baidu 重写为 www.baidu.com
# 因为会顺序执行 rewrite 指令 所以 下一步执行 return 指令 响应了 ok  
```


#### rewrite 的四个 flag

* `last`

停止处理当前的`ngx_http_rewrite_module`的指令集，并开始搜索与更改后的`URI`相匹配的`location`;    
* `break`

停止处理当前的`ngx_http_rewrite_module`指令集，就像上面说的`break`指令一样;    
* `redirect`

返回302临时重定向。    
* `permanent`

返回301永久重定向。    

```nginx
# 没有rewrite 后面没有任何 flag 时就顺序执行 
# 当 location 中没有 rewrite 模块指令可被执行时 就重写发起新一轮location匹配
location / {
    # 顺序执行如下两条rewrite指令 
    rewrite ^/test1 /test2;
    rewrite ^/test2 /test3;  # 此处发起新一轮location匹配 uri为/test3
}

location = /test2 {
    return 200 "/test2";
}  

location = /test3 {
    return 200 "/test3";
}
# 发送如下请求
# curl 127.0.0.1:8080/test1
# /test3
```


#### last 与 break 的区别


last 和 break一样 它们都会终止此 location 中其他它rewrite模块指令的执行，

但是 last 立即发起新一轮的 location 匹配 而 break 则不会

```nginx
location / {
    rewrite ^/test1 /test2;
    rewrite ^/test2 /test3 last;  # 此处发起新一轮location匹配 uri为/test3
    rewrite ^/test3 /test4;
    proxy_pass http://www.baidu.com;
}

location = /test2 {
    return 200 "/test2";
}  

location = /test3 {
    return 200 "/test3";
}
location = /test4 {
    return 200 "/test4";
}
# 发送如下请求
# curl 127.0.0.1:8080/test1
# /test3 

当如果将上面的 location / 改成如下代码
location / {
    rewrite ^/test1 /test2;
    # 此处 不会 发起新一轮location匹配；当是会终止执行后续rewrite模块指令 重写后的uri为 /more/index.html
    rewrite ^/test2 /more/index.html break;  
    rewrite /more/index\.html /test4; # 这条指令会被忽略

    # 因为 proxy_pass 不是rewrite模块的指令 所以它不会被 break终止
    proxy_pass https://www.baidu.com;
}
# 发送如下请求
# 浏览器输入 127.0.0.1:8080/test1 
# 代理到 百度产品大全页面 https://www.baidu.com/more/index.html;
```


#### 友情提醒下

此处提一下 在上面的代码中即使将`proxy_pass`放在 带有`break`的`rewrite`上面它也是会执行的，这就要扯到`nginx`的执行流程了。大家有兴趣可以了解下。


#### rewrite 后的请求参数

如果替换字符串`replacement`包含新的请求参数，则在它们之后附加先前的请求参数。如果你不想要之前的参数，则在替换字符串`replacement`的末尾放置一个问号，避免附加它们。

```nginx
# 由于最后加了个 ?，原来的请求参数将不会被追加到rewrite之后的url后面 
rewrite ^/users/(.*)$ /show?user=$1? last;
```


### rewrite_log

> Context: http, server, location, if

开启或者关闭`rewrite`模块指令执行的日志，如果开启，则重写将记录下`notice`等级的日志到`nginx`的`error_log`中，默认为关闭`off`

```nginx
Syntax:    rewrite_log on | off;
```


### set

> Context: server, location, if

设置指定变量的值。变量的值可以包含文本，变量或者是它们的组合形式。

```nginx
location / {
    set $var1 "host is ";
    set $var2 $host;
    set $var3 " uri is $request_uri";
    return 200 "response ok $var1$var2$var3";
}
# 发送如下请求
# curl 127.0.0.1:8080/test
# response ok host is 127.0.0.1 uri is /test
```


### uninitialized_variable_warn

> Context: http, server, location, if


控制是否记录 有关未初始化变量的警告。默认开启

参考：https://segmentfault.com/a/1190000008102599

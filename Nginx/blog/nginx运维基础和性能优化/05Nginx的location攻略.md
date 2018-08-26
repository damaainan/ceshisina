# 【nginx运维基础(5)】Nginx的location攻略


## 概述

location 有"定位"的意思, 根据Uri来进行不同的定位.   
在虚拟主机的配置中,是必不可少的,location可以把网站的不同部分,定位到不同的处理方式上.伪静态,反向代理,负载均衡等等都离不开location.

### 语法

    location [=|~|~*|^~] patt {}
    

中括号可以不写任何参数,此时称为一般匹配,也可以写参数.

因此,大类型可以分为**3种**：

`location = patt {}` **[精准匹配]**  
`location patt{}`  **[一般匹配]**  
`location ~ patt{}` **[正则匹配]**  
    

### 匹配说明

### **精准匹配 `=`**

完全匹配指定的 pattern ，且这里的 pattern 被限制成简单的字符串，也就是说这里不能使用正则表达式.

    server {
        server_name website.com;
        location = /abcd {
        […]
        }
    }
    
    http://website.com/abcd        # 正好完全匹配
    http://website.com/ABCD        # 如果运行 Nginx server 的系统本身对大小写不敏感，比如 Windows ，那么也匹配
    http://website.com/abcd?param1    # 忽略查询串参数（query string arguments）,也同样匹配
    http://website.com/abcd/    # 不匹配，因为末尾存在反斜杠（trailing slash），Nginx 不认为这种情况是完全匹配
    http://website.com/abcde    # 不匹配，因为不是完全匹配
    

### **一般匹配 (None)**

可以理解为**左前缀匹配(like pattern%)**,这种情况下，匹配那些以指定的 patern 开头的 URI，注意这里的 URI 只能是普通字符串，不能使用正则表达式.

    server {
        server_name website.com;
        location /abcd {
        […]
        }
    }
    
    http://website.com/abcd        # 正好完全匹配
    http://website.com/ABCD        # 如果运行 Nginx server 的系统本身对大小写不敏感，比如 Windows ，那么也匹配
    http://website.com/abcd?param1     # 忽略查询串参数（query string arguments），这里就是 /abcd 后面的 ?param1
    http://website.com/abcd/    # 末尾存在反斜杠（trailing slash）也属于匹配范围内
    http://website.com/abcde    # 仍然匹配，因为 URI 是以 pattern 开头的
    

### **正则匹配 `~`**

`对大小写敏感`(**在window上无效**)，且 pattern 须是正则表达式

    server {
        server_name website.com;
        location ~ ^/abcd$ {
        […]
        }
    }
    
    
    http://website.com/abcd        # 完全匹配
    
    http://website.com/ABCD        # 不匹配，~ 对大小写是敏感的
    
    http://website.com/abcd?param1     # 忽略查询串参数（query string arguments），这里就是 /abcd 后面的 ?param1 
    
    http://website.com/abcd/    # 不匹配，因为末尾存在反斜杠（trailing slash），并不匹配正则表达式 ^/abcd$
    
    http://website.com/abcde    # 不匹配正则表达式 ^/abcd$
    

### **正则匹配 `~*`**

`不区分大小写`，pattern 须是正则表达式

    server {
        server_name website.com;
        location ~* ^/abcd$ {
        […]
        }
    }
    
    http://website.com/abcd        # 完全匹配
    
    http://website.com/ABCD        # 匹配，这就是它不区分大小写的特性
    
    http://website.com/abcd?param1     # 忽略查询串参数（query string arguments），这里就是 /abcd 后面的 ?param1 
    
    http://website.com/abcd/    # 不匹配，因为末尾存在反斜杠（trailing slash），并不匹配正则表达式 ^/abcd$
    
    http://website.com/abcde    # 不匹配正则表达式 ^/abcd$
    

### **正则匹配 `^~`**

匹配情况类似`一般匹配`，以指定匹配模式开头的 URI 被匹配

### **`!~`和`!~`***

分别为`区分大小写不匹配`及`不区分大小写不匹配`的正则

### **通用匹配 `/`**

任何请求都会匹配到.

### **特殊匹配 `@`**

用于定义一个 Location 块，且该块不能被外部 Client 所访问，只能被 Nginx 内部配置指令所访问，比如 **try_files** or **error_page**

## 匹配优先级

`http://www.test.com/` 从域名后面(uri:http请求行的第二列)开始匹配,也就是`/`,匹配原则一般都是**左前缀匹配**,  
location / {} 能够匹配所有HTTP 请求，因为任何HTTP 请求都必然是以'/'开始的,但是，**正则location 和其他任何比'/'更长的普通location** (location / {} 是普通location 里面最短的，因此其他任何普通location 都会比它更长，当然location = / {} 和 location ^~ / {} 是一样长的）**会优先匹**,由此可见匹配的优先级可以总结为:

<font color=green size=3>
**越详细就越优先**</font>

> 是不是有点像css的选择器?

**Example1**

```nginx
# 首先看有没有精准匹配,如果有,则停止匹配过程.
location = patt {
  config A;
}

location / {
  root   /usr/local/nginx/html;
  index  index.html index.htm;
}
```

如果访问[http://test.com/][0]  
定位流程是   
1: 精准匹配中"/" ,得到index页为index.htm   
2: 再次访问 /index.htm , 此次内部转跳uri已经是"/index.htm",根目录为/usr/local/nginx/html   
3: 最终结果,访问了 /usr/local/nginx/html/index.htm

**Example2**

```nginx
location / {
   root   /usr/local/nginx/html;
   index  index.html index.htm;
}
location /foo {
    root /var/www/html;
    index index.html;
}
```

我们访问 [http://test.com/foo][1]  
对于uri "/foo", 两个location的patt,都能匹配他们   
即 '/'能从左前缀匹配 '/foo', '/foo'也能左前缀匹配'/foo',   
此时, 真正访问 /var/www/html/index.html   
原因:**'/foo'匹配的更长,因此使用之**;

**Example3**
```nginx
location ~ image {
    root /var/www/image;
    index index.html;
}
```

如果我们访问 [http://test.com/image/logo.png][2]  
此时, "/" 与"/image/logo.png" 匹配   
同时,"image"正则 与"image/logo.png"也能匹配,谁发挥作用?   
**正则表达式的成果将会使用.因为此时的正则表达式更详细**  
图片真正会访问 /var/www/image/logo.png 

### **再次总结优先级序如下：**

    1. =
    2. (None)    前提是 pattern 完全匹配 URI 的情况（不是只匹配 URI 的头部,这点很重要）
    3. ^~
    4. ~ 或 ~*
    5. (None) pattern 匹配 URI 的头部
    

貌似与location的书写顺序无关? 但实际上还是有关系的

```nginx
    # 配置一
    server {
    
     listen       9090;
     server_name  localhost;
    
     location ~ \.html$ {
         allow all; 
     }  
    
     location ~ ^/prefix/.*\.html$ {
         deny all;  
     }  
    
    }
    
    
    # 配置二
    server {
    
       listen       9090;
       server_name  localhost;
    
       location ~ ^/prefix/.*\.html$ {
           deny all;  
       }  
    
      location ~ \.html$ {
           allow all; 
       } 
    }
```

URI 请求 | 配置一 | 配置二 
-|-|-
curl [http://localhost:9090/regextest.html][3] | 404 Not Found | 404 Not Found 
curl [http://localhost:9090/prefix/regextest.html][4] | 404 Not Found | 403 Forbidden 

`Location ~ ^/prefix/.*.html$ {deny all;}` 表示正则 location 对于以 `/prefix/` 开头， `.html` 结尾的所有 URI 请求，都拒绝访问；   
`location ~.html$ {allow all;}` 表示正则 location 对于以 `.html` 结尾的 URI 请求，都允许访问. 实际上，`prefix` 的是 `~.html$` 的子集.

在"配置一 "下，两个请求都匹配上 `location ~.html$ {allow all;}` ，并且停止后面的搜索，于是都允许访问， 404 Not Found ；在"配置二 "下， /regextest.html 无法匹配 prefix ，于是继续搜索 `~.html$` ，允许访问，于是 404 Not Found ；然而 /prefix/regextest.html 匹配到 prefix ，于是 deny all ， 403 Forbidden .

### 优先级最终总结

    1. =
    2. (None)    前提是 pattern 完全匹配 URI 的情况（不是只匹配 URI 的头部,这点很重要）
    3. ^~
    4. ~ 或 ~*
    5. (None) pattern 匹配 URI 的头部
    

<font color=green size=3>
**越详细就越优先,但是`同优先级`的情况下,按书写顺序谁先出现就以谁为准(就近原则)**</font>

> 依然和css选择器的优先级很像...

## root&alias文件路径配置

[http://www.ttlsa.com/nginx/nginx-root_alias-file-path-configuration/][5]

## 推荐必须的location

## `\.` 中 `\` 是转义符

```nginx

#直接匹配网站根，通过域名访问网站首页比较频繁，使用这个会加速处理，官网如是说.
#这里是直接转发给后端应用服务器了，也可以是一个静态首页
# 第一个必选规则
location = / {
    proxy_pass http://127.0.0.1:88; 
}
# 第二个必选规则是处理静态文件请求，这是nginx作为http服务器的强项
# 有两种配置模式，目录匹配或后缀匹配,任选其一或搭配使用
location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
{
    expires      30d;
}
location ~ .*\.(js|css)?$
{
    expires      12h;
}
location ~ /\.  
{
    deny all; # 其他的任意后缀都不让其访问;
}
#第三个规则就是通用规则，用来转发动态请求到后端应用服务器
location /
{
    try_files $uri @apache; #try_files 将尝试你列出的文件并设置内部文件指向
}
location @apache
{
    internal; # internal指令指定某个location只能被“内部的”请求调用，外部的调用请求会返回”Not found”
    proxy_pass http://127.0.0.1:88;
    proxy_connect_timeout 300s;
    proxy_send_timeout   900;
    proxy_read_timeout   900;
    proxy_buffer_size    32k;
    proxy_buffers     4 32k;
    proxy_busy_buffers_size 64k;
    proxy_redirect     off;
    proxy_hide_header  Vary;
    proxy_set_header   Accept-Encoding '';
    proxy_set_header   Host   $host;
    proxy_set_header   Referer $http_referer;
    proxy_set_header   Cookie $http_cookie;
    proxy_set_header   X-Real-IP  $remote_addr;
    proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

[0]: http://test.com/
[1]: http://test.com/foo
[2]: http://test.com/image/logo.png
[3]: http://localhost:9090/regextest.html
[4]: http://localhost:9090/prefix/regextest.html
[5]: http://www.ttlsa.com/nginx/nginx-root_alias-file-path-configuration/
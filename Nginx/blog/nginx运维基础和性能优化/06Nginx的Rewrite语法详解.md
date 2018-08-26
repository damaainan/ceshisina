# 【nginx运维基础(6)】Nginx的Rewrite语法详解

## 概述

重写URL是非常有用的一个功能，因为它可以让你提高搜索引擎阅读和索引你的网站的能力；而且在你改变了自己的网站结构后，无需要求用户修改他们的书签，无需其他网站修改它们的友情链接；它还可以提高你的网站的安全性；而且通常会让你的网站更加便于使用和更专业。

### Nginx Rewrite规则相关指令

Nginx Rewrite规则相关指令有if、rewrite、set、return、break等，其中rewrite是最关键的指令。

## Rewrite

重写,写在server段或者location段都可,后出现的先应用


```nginx


#判断访问地址
if  ($remote_addr = 192.168.1.100) { 
  return 403;
}

#判断访问的是否ie;
if ($http_user_agent ~ MSIE) {
  rewrite ^.*$ /ie.htm;
  break; #不break会循环重定向(是ie重写到ie.htm,然后又发现是ie,又重写到ie.htm...)
}

#跳转到404
if (!-e $document_root$fastcgi_script_name) {
  rewrite ^.*$ /404.html;
  break;
} 
```


**注意: Nginx对配置的格式非常的严格,`if后面一定要有空格`,运算符前后也必须要用空格隔开**

    If 空格 (条件) {
        重写模式
    }
    

> rewrite的核心还是正则表达式,其他的只要知道其语法规则既可

### **规则参考**

    ~ 为区分大小写匹配
    
    ~* 为不区分大小写匹配
    
    !~和!~*分别为区分大小写不匹配及不区分大小写不匹配
    
    -f和!-f用来判断是否存在文件
    
    -d和!-d用来判断是否存在目录
    
    -e和!-e用来判断是否存在文件或目录
    
    -x和!-x用来判断文件是否可执行
    
    last 相当于Apache里的[L]标记，表示完成rewrite，呵呵这应该是最常用的
    
    set 设置变量
    
    return  返回状态码 
    
    break 终止匹配, 不再匹配后面的规则
    
    redirect 返回302临时重定向 地址栏会显示跳转后的地址
    
    permanent 返回301永久重定向 地址栏会显示跳转后的地址
    

### **内置变量参考**

    $args, 请求中的参数;
    
    $content_length, HTTP请求信息里的"Content-Length";
    
    $content_type, 请求信息里的"Content-Type";
    
    $document_root, 针对当前请求的根路径设置值;
    
    $document_uri, 与$uri相同;
    
    $host, 请求信息中的"Host"，如果请求中没有Host行，则等于设置的服务器名;
    
    $limit_rate, 对连接速率的限制;
    
    $request_method, 请求的方法，比如"GET"、"POST"等;
    
    $remote_addr, 客户端地址;
    
    $remote_port, 客户端端口号;
    
    $remote_user, 客户端用户名，认证用;
    
    $request_filename, 当前请求的文件路径名
    
    $request_body_file
    
    $request_uri, 请求的URI，带查询字符串;
    
    $query_string, 与$args相同;
    
    $scheme, 所用的协议，比如http或者是https，比如rewrite  ^(.+)$  $scheme://example.com$1  redirect;
    
    $server_protocol, 请求的协议版本，"HTTP/1.0"或"HTTP/1.1";
    
    $server_addr, 服务器地址，如果没有用listen指明服务器地址，使用这个变量将发起一次系统调用以取得地址(造成资源浪费);
    
    $server_name, 请求到达的服务器名;
    
    $server_port, 请求到达的服务器端口号;
    
    $uri, 请求的URI，可能和最初的值有不同，比如经过重定向之类的。
    

> 以上变量也可以用打印日志哦

### 范例分析

**Example1**  
不存在的文件跳到404.html

```nginx

if (!-e $document_root$fastcgi_script_name) {
  rewrite ^.*$ /404.html;
  break;
} 
/*
要加break,以 xx.com/dsafsd.html这个不存在页面为例,我们观察访问日志, 日志中显示的访问路径,依然是GET /dsafsd.html HTTP/1.1
提示: 服务器内部的rewrite和302跳转不一样.302跳转url会改变,变成重新http请求404.html, 而内部rewrite, 上下文没变,
就是说 **fastcgi_script_name** 仍然是 dsafsd.html,因此会循环重定向.
*/
```



**Example2**  
在不使用break的情况下,对ie访问进行重写
```nginx
if ($http_user_agent ~* msie) { //如果是ie访问的话设变量为1;
  set $isie 1;
}
if ($fastcgi_script_name = ie.html) { //如果访问的脚本为ie.html,变量为0;
  set $isie 0;
}
if ($isie 1) { //综合起来,如果ie访问的是ie.html这个脚本就不重写;
  rewrite ^.*$ ie.html;
}  

```



**Example3**

目录自动加`/`    

```nginx
if (-d $request_filename){
    rewrite ^/(.*)([^/])$ http://$host/$1$2/ permanent;
}
```

用`([^/])`匹配最后一个非'`/`'的字符,然后自己强行再添加一个'`/`'($2变量后的那个） 

**Example4**

Nginx防盗链

```nginx

location ~* ^.+\.(jpg|jpeg|gif|png|swf|rar|zip|css|js)$ {
    valid_referers none blocked *.nixi8.com nixi8.com localhost 192.168.42.188; #定义none(空,直接访问),blocked(被防火墙标记过的来路),nixi8.com的二级域名和一级域名,localhost,192.168.42.188
    
    if ($invalid_referer) { # 如果不是上面定义的其中一个
        rewrite ^/ http://www.nixi8.com/none.gif;  # 就重写到一张gif图片上;
        return 412;
        break;
    }

    access_log   off;  # 关闭日志,降低服务器的损耗
    root /opt/lampp/htdocs/web; 
    expires 3d; 
    break;
}
```



**Example5**

隐藏index.php

apache下只要在全局配置文件中设置了缺省首页index.php就能实现直接到达index.php，但是**nginx目前默认情况下只能到达index.html而不能访问到index.php**,所以**`只好rewrite重写使其支持`**

    

```nginx
if (-f $request_filename) { //使其不隐藏index.php的时候也能访问到
    expires max;
    break;
}
if (!-e $request_filename) {
    rewrite ^/(.*)$ /index.php/$1 last;
}
```

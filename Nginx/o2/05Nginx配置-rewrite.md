# Nginx配置-rewrite

 时间 2017-08-20 08:51:47  

原文[http://www.jialeens.com/archives/544][1]

<font face=微软雅黑>

##  序、rewrite概述 

继《 [（四）Nginx配置-Location][3] 》之后，还需要说明一下rewrite的用法。 

rewrite在nginx中对应着`ngx_http_rewrite_module`模块，nginx通过`ngx_http_rewrite_module`模块支持url重写、支持if条件判断，但不支持else。该模块需要PCRE支持。

`ngx_http_rewrite_module`模块允许正则替换URI，返回页面重定向，和按条件选择配置。

##  一、指令执行顺序 ngx_http_rewrite_module模块指令按以下顺序处理：

* 处理在`server`级别中定义的模块指令；
* 为请求查找`location`；
* 处理在选中的`location`中定义的模块指令。如果指令改变了URI，按新的URI查找location。这个循环至多重复 **10次**，之后nginx返回错误500 (Internal Server Error)。

##  二、break指令 

语法：break;

默认值：无

作用域：server,location,if

停止执行当前虚拟主机的后续rewrite指令集

break指令实例：

    if ($slow) {
        limit_rate 10k;
        break;
    }

##  **三、if指令** 

语法：if(condition){…}

默认值：无

作用域：server,location

对给定的条件condition进行判断。如果为真，大括号内的rewrite指令将被执行。

if条件(conditon)可以是如下任何内容:

* 一个变量名；false如果这个变量是空字符串或者以0开始的字符串；
* 使用`=` ,`!=` 比较的一个变量和字符串
* 是用`~`， `~*`与正则表达式匹配的变量，如果这个正则表达式中包含}，;则整个表达式需要用” 或’ 包围
* 使用`-f` ，`!-f` 检查一个文件是否存在
* 使用`-d`, `!-d` 检查一个目录是否存在
* 使用`-e` ，`!-e` 检查一个文件、目录、符号链接是否存在
* 使用`-x` ， `!-x` 检查一个文件是否可执行

if指令实例

```nginx
    if ($http_user_agent ~ MSIE) {
        rewrite ^(.*)$ /msie/$1 break;
    }
    
    if ($http_cookie ~* "id=([^;]+)(?:;|$)") {
        set $id $1;
    }
    
    if ($request_method = POST) {
        return 405;
    }
    
    if ($slow) {
        limit_rate 10k;
    }
    
    if ($invalid_referer) {
        return 403;
    }
```

##  **四、return指令** 

语法：return code;

return code URL;

return URL;   
默认值：无   
作用域：server,location,if 

停止处理并返回指定状态码(code)给客户端。

非标准状态码444表示关闭连接且不给客户端发响应头。

从0.8.42版本起，return 支持响应URL重定向(对于301，302，303，307），或者文本响应(对于其他状态码).

对于文本或者URL重定向可以包含变量

##  **五、rewrite指令** 

语法：`rewrite regex replacement [flag]`;

默认值：无

作用域：server,location,if

如果一个URI匹配指定的正则表达式regex，URI就按照replacement重写。

rewrite按配置文件中出现的顺序执行。flags标志可以停止继续处理。

如果replacement以 `http://` 或 `https://` 开始，将不再继续处理，这个重定向将返回给客户端。

##### flag可以是如下参数

`last` 停止处理后续rewrite指令集，然后对当前重写的新URI在rewrite指令集上重新查找。

`break` 停止处理后续rewrite指令集，并不在重新查找,但是当前location内剩余非rewrite语句和location外的的非rewrite语句可以执行。

`redirect` 如果replacement不是以`http://` 或`https://`开始，返回302临时重定向

`permant` 返回301永久重定向

最终完整的重定向URL包括请求`scheme`(`http://`,`https://`等),请求的`server_name_in_redirect`和 `port_in_redirec`三部分 ，说白了也就是http协议 域名 端口三部分组成。

rewrite实例

```nginx
    server {
        ...
        rewrite ^(/download/.*)/media/(.*)..*$ $1/mp3/$2.mp3 last;
        rewrite ^(/download/.*)/audio/(.*)..*$ $1/mp3/$2.ra last;
        return 403;
        ...
    }
```

如果这些rewrite放到  `/download/`  location如下所示, 那么应使用break而不是last , 使用last将循环10次匹配，然后返回 500错误: 

```nginx
    location /download/ {
        rewrite ^(/download/.*)/media/(.*)..*$ $1/mp3/$2.mp3 break;
        rewrite ^(/download/.*)/audio/(.*)..*$ $1/mp3/$2.ra break;
        return 403;
    }
```

对于重写后的URL（replacement）包含原请求的请求参数，原URL的?后的内容。如果不想带原请求的参数 ，可以在replacement后加一个问号。如下，我们加了一个自定义的参数user=$1,然后在结尾处放了一个问号?,把原请的参数去掉。

    rewrite ^/users/(.*)$ /show?user=$1? last;

如果正则表达regex式中包含 “ } ” 或 “ ; ”, 那么整个表达式需要用双引号或单引号包围. 

##  **六、rewrite_log指令** 

语法：rewrite_log on|off;

默认值：rewrite_log off;

作用域：http,server,location,if

开启或关闭以notice级别打印rewrite处理日志到error log文件。

nginx打开rewrite log例子

rewrite_log on;

error_log logs/xxx.error.log notice;

1.打开rewrite on

2.把error log的级别调整到 notice

##  **七、set指令** 
语法：set variable value;

默认值：none

作用域：server,location,if

定义一个变量并赋值，值可以是文本，变量或者文本变量混合体。

##  **八、uninitialized_variable_warn指令** 

语法：uninitialized_variable_warn on | off;

默认值：uninitialized_variable_warn on

作用域：http,server,location,if

控制是否输出为初始化的变量到日志。

参考资料：

[http://nginx.org/en/docs/http/ngx_http_rewrite_module.html][4]

</font>

[1]: http://www.jialeens.com/archives/544

[3]: http://www.jialeens.com/archives/491
[4]: http://nginx.org/en/docs/http/ngx_http_rewrite_module.html
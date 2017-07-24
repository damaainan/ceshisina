# NGINX的奇淫技巧 —— 1. 字符串截断


在给大家讲述这个问题之前，先给大家看一段nginx配置. 我们用到了 [set-misc-nginx-module][0]


```nginx
    location /test/ {
        default_type text/html;
        set_md5 $hash "secret"$remote_addr;
        echo $hash;
    }
```

这样输出来的内容，可能是下面这样的

    202cb962ac59075b964b07152d234b70
    

但如果我们要截取某几位字符怎么办呢?  
首先大家想到的肯定是使用模块来实现, 但只能这样吗? 有没有更方便的方式呢?

有的.  
我们可以巧妙地使用if + 正则表达式来实现这个小需求:

```nginx
    location /test/ {
        default_type text/html;
        set_md5 $hash "secret"$remote_addr;
        if ( $hash ~ ^[\w][\w][\w][\w][\w][\w][\w][\w]([\w][\w][\w][\w][\w][\w][\w][\w]) ) {
            set $hash $1;
        }
        echo $hash;
    }
```

访问/test/输出的就是:

    ac59075b

[0]: https://github.com/openresty/set-misc-nginx-module


----

# NGINX的奇淫技巧 —— 2. IF AND 和 OR 2015年01月12日发布 


在上一篇文章:《[NGINX里的奇淫技巧 —— 1. 字符串截断][1]》中, 我们介绍过了使用if来进行截断字符串的用法, 这次我们来了解下if的逻辑用法:

什么是逻辑用法呢, 就程序中的and、or关系, 就叫做逻辑了.

NGINX支持if的 and 与 or 或者 && 与 || 吗？

答案是No.  
当你尝试这样配置, 重载nginx时, nginx会报出错误

```nginx
        location = /test/ {
            default_type text/html;
            set $b 0;
            if ( $remote_addr != '' && $http_x_forwarded_for != '' ){
                set $b '1';
            }
            echo $b;
        }
```

    [root@test-vm ~]# /usr/local/nginx/sbin/nginx -t
    
    nginx: [emerg] invalid condition "$remote_addr" in /usr/local/nginx/conf/nginx.conf:60
    configuration file /usr/local/nginx/conf/nginx.conf test failed
    

那么我们应该怎样来实现and 和or的逻辑关系呢？

```nginx
        location = /test_and/ {
            default_type text/html;
            set $a 0;
            set $b 0;
            if ( $remote_addr != '' ){
                set $a 1;
            }
            if ( $http_x_forwarded_for != '' ){
                set $a 1$a;
            }
            if ( $a = 11 ){
                set $b 1;
            }
            echo $b;
        }
```

```nginx
        location = /test_or/ {
            default_type text/html;
            set $a 0;
            set $b 0;
            if ( $remote_addr != '' ){
                set $a 1;
            }
            if ( $http_x_forwarded_for != '' ){
                set $a 1;
            }
            if ( $a = 1 ){
                set $b 1;
            }
            echo $b;
        }
```

[1]: http://segmentfault.com/blog/security/1190000002480053

----

# NGINX的奇淫技巧 —— 3. 不同域名输出不同伺服器标识



大家或许会有这种奇葩的需求...  
要是同一台主机上, 需要针对不同的域名输出不同的Server头, 怎么实现呢?

我们需要用到[ngx_headers_more][2]模块

```nginx
    location / {
        if ( $host = 'segmentfault.com' ){
            more_set_headers 'Server: Nginx';
        }
        if ( $host = '0x01.segmentfault.com' ){
            more_set_headers 'Server: Nginx_improved';
        }
        ....
    }
```

像上面这样, 我们就可以来实现这功能了.  
但这样靠谱吗？ 靠谱, 但是不满足A.R.G.U.S. 的编码风格, 我们绝不允许丑陋的代码让别人看着笑话.

我们追求极客的代码:

```nginx
    map $host $server_x_tag{
        'segmentfault.com' 'Nginx';
        '0x01.segmentfault.com' 'Nginx_improved';
        default 'Nginx';
    }
    
    server{
        server_name 123;
        location / {
            more_set_headers 'Server: $server_x_tag';
        }
    }
```

像这样子, 是不是好看多了?


[2]: https://github.com/openresty/headers-more-nginx-module
----

# NGINX的奇淫技巧 —— 4. 纯CONF实现一个简单的CSRF防火墙

本文章编写中...  
尚需时日...  
下面给的只是一个原型，尚未验证...

```nginx
    server{
        location / {
            default_type text/html;
    
            set $is_post 0;
            set $is_verify_passed 1;
            set $is_csrf_alarm 0;
            set $secret 'asgasdgdfg';
            set_md5 $_csrf_token $remote_addr$secret;
            set     $_csrf_token_post '';
    
            if ( $request_method = "POST" ) {
                set $is_post 1;
                set_form_input $_csrf_token_post '_csrf_token';
            }
            if ( $_csrf_token_post != $_csrf_token ) {
                set $is_verify_passed 0;
            }
            set $is_csrf_alarm $is_post$is_verify_passed;
            if ( $is_csrf_alarm = 10 ){
                echo '{"code":999,"error":true,"data":{"info":"_csrf_token error"}}';
                break;
            }
        }
    }
```

----

# NGINX的奇淫技巧 —— 6. IF实现数学比较功能



nginx的if支持=、!= 逻辑比较, 但不支持if中 <、<、>=、<= 比较.  
本示例使用了[set-misc-nginx-module][3]


```nginx
    location = /test/ {
        default_type html;
        set_random $a 0 9;      #$a 随机 从0-9取
        if ( $a <= 4 ){         #$a 如果 < 4 这是错误的写法
            echo 'a: $a is lte 4';
        }
        if ( $a >= 5 ){         #$a 如果 > 5 这是错误的写法
            echo 'a: $a is gte 5';
        }
    }
```

上面的配置, 在启动nginx时会报错误的.

即然不支持，那有没有办法小小地弥补下呢?
```nginx
    location = /test/ {
        default_type html;
        set_random $a 0 9;     #$a 随机 从0-9取
        if ( $a ~ [0-4] ){     #$a 如果 正则匹配 0-4
            echo 'a: $a is lte 4';
        }
        if ( $a ~ [5-9] ){     #$a 如果 正则匹配 5-9
            echo 'a: $a is gte 5';
        }
    }
```

测试10次：

```
    a: 8 is gte 5
    a: 9 is gte 5
    a: 2 is lte 4
    a: 1 is lte 4
    a: 8 is gte 5
    a: 0 is lte 4
    a: 9 is gte 5
    a: 1 is lte 4
    a: 4 is lte 4
    a: 5 is gte 5
    ...
```

骚年, 速度加入A.R.G.U.S.网络安全小组, 跟老夫们一起学nginx吧~




NGINX竟然不支持这样的写法....
```nginx
    location = /test/ {
        default_type html;
        set_random $a 0 9;     #$a 随机 从0-9取
        set_random $b 0 9;     #$b 随机 从0-9取
        set $ereg "[0-$b]";
        if ( $a ~ $ereg ){     #$a 如果 正则匹配 0-$b
            echo 'a: $a is lte b: $b  ereg: $ereg';
        }
        if ( $a !~ $ereg ){     #$a 如果 正则不匹配 0-$b
            echo 'a: $a is gt b: $b  ereg: $ereg';
        }
    }
```

求大牛来实现...


[3]: https://github.com/openresty/set-misc-nginx-module

----




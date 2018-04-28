## 一篇文章说透Nginx的rewrite模块

来源：[http://www.cnblogs.com/minirice/p/8872093.html](http://www.cnblogs.com/minirice/p/8872093.html)

时间 2018-04-18 09:34:00


rewrite模块即`ngx_http_rewrite_module`模块，主要功能是改写请求URI，是Nginx默认安装的模块。rewrite模块会根据PCRE正则匹配重写URI，然后发起内部跳转再匹配location，或者直接做30x重定向返回客户端。


## rewrite指令的工作原理

rewrite模块的指令有break, if, return, rewrite, set等。rewrite指令所执行的顺序如下：



* 首先在server上下文中依照顺序执行rewrite模块指令；
* 如果server中行了rewrite重写，那么以新URI发起内部跳转，直接匹配location，不会再执行server里的rewrite指令，然后    

* 新URI直接匹配location
* 如果匹配上某个location，那么其中的rewrite模块指令同样依照顺序执行
* 如果再次导致URI的rewrite，那么再一次进行内部跳转去匹配location，但跳转的总次数不能超过10次
      


  

## rewrite


基本语法： rewrite regex replacement [flag];

上下文：server, location, if

regex是PCRE 风格的，如果regex匹配URI，那么URI就会被替换成replacement， **`replacement 就是新的URI`** 。如果rewrite同一个上下文中有多个这样的正则，匹配会依照rewrite指令出现的顺序先后依次进行下去， **`匹配到一个之后并不会终止，而是继续往下匹配`** ，直到返 **`回最后一个匹配上的为止`** 。如果想要中止继续往下匹配，可以使用第三个参数flag。

如果新URI字符中有关于协议的任何东西，比如http://或者https://等，进一步的处理就终止了，直接返回客户端302。

如果返回的是30x，那么浏览器根据这个状态码和Location响应头再发起一次请求，然后才能得到想要的响应结果。但是，如果不是返回30x状态码，那么跳转就是内部的，浏览器不做跳转就能得到相应。

注意：regex直接就是正则表达式，不要再前面添加~符号

flag 参数可以有以下的一些值：


#### last

如果有last参数，那么停止处理任何rewrite相关的指令，立即用替换后的新URI开始下一轮的location匹配


#### break

停止处理任何rewrite的相关指令，就如同break 指令本身一样。

last的break的相同点在于，立即停止执行所有当前上下文的rewrite模块指令；不同点在于last参数接着用新的URI马上搜寻新的location，而break不会搜寻新的location，直接用这个新的URI来处理请求，这样能避免重复rewite。因此，在server上下文中使用last，而在location上下文中使用break 。


#### redirect

replacement 如果不包含协议，仍然是一个新的的URI，那么就用新的URI匹配的location去处理请求，不会返回30x跳转。但是redirect参数可以让这种情况也返回30x(默认302)状态码，就像新的URI包含http://和https://等一样。这样的话，浏览器看到302，就会再发起一次请求，真正返回响应结果的就是这第二个请求。


#### permanent

和redirect参数一样，只不过直接返回301永久重定向

虽说URI有了新的，但是要拼接成完整的URL还需要当前请求的scheme，以及由`server_name_in_redirect`和`port_in_redirect`指令决定的HOST和PORT.

还有一个比较有意思的应用，就是如果replacement中包含请求参数，那么默认情况下旧URI中的请求参数也会拼接在replacement后面作为新的URI，如果不想这么做，可以在replacement的最后面加上?。

举例说明：

```nginx
rewrite ^/users/(.*)$ /show?user=$1? last;
```

这样的新URI还是`/show?user=xxx`但如果不加问号：

```nginx
rewrite ^/users/(.*)$ /show?user=$1 last;
```

得到的新URI就是`/show?user=$1&xxx=xxx`。其中xxx=xxx是旧URI所带的请求参数。


#### rewrite的例子

在server中使用的情况：

```nginx
server {
    ...
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 last;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  last;
    return  403; #没有匹配上，那就返回403咯
    ...
}
```

注意，在server中使用rewrite ，我们使用的flag是last，但是在location中，我们却只能用break：

```nginx
location /download/ {
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 break;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  break;
    return  403;
}
```

如果在location的rewrite也使用last，便会再次以新的URI重新发起内部重定向，再次进行location匹配，而新的URI中极有可能和旧的URI一样再次匹配到相同location中，这样死循环发生了。当循环到第10次时，Nginx会终止这样无意义的循环，并返回500错误。这点需要特别的注意。


## break


基本语法：break;

上下文：server, location, if

停止处理任何rewrite的相关指令。如果出现在location里面，那么所有后面的rewrite模块指令都不会再执行，也不发起内部重定向，而是直接用新的URI进一步处理请求。


## if


基本语法：if (condition) { ... }

上下文：server, location

根据条件condition的真假决定是否加载{...}中的配置，{...}中的配置可以继承外面的配置，也可以对外面已有配置指令进行覆写。

条件condition是针对变量而言的，变量既可以是系统变量，也可以是自定义的，可以是下面几种情况：



* 当condition为变量`$var`本身时，当且仅当变量值为空字符或者0时，条件为false，其余情况皆为 true    
* 变量`$var`通过"="或者"!="与字符串相比较，即`$var = xxx`或者`$var != xxx`
* 匹配一个正则表达式
* `-f``-d``-e``-x`等检验文件或者目录属性或者存在与否的运算符    
  

#### 正则匹配

其中，对于 3. 匹配一个正则表达式的情况，可以细分为：



* `$var ~ Reg`表示大小写敏感匹配    
* `$var ~* Reg`表示大小写不敏感匹配    
* `$var !~ Reg`表示大小写敏感不匹配    
* `$var !~* Reg`表示大小写不敏感不匹配    
  

Reg 中可以捕获变量，当其中包含有 } 或者 ; 时需要用双引号或者单引号括起来。


#### 文件检验

另外，对于 4. 检验文件存在或者属性的情况，具体说来也分为以下几种：



* -f /path/to 检验文件是否存在；!-f /path/to 检验文件是否不存在
* -d /path/to/ 检验目录是否存在；!-d /path/to/ 检验目录是否不存在
* -e /path/to/ 检验文件或者目录或者链接是否存在；!-e /path/to/ 检验文件或者目录或者链接是否不存在
* -x /path/to 检验文件是否为可执行文件；!-x /path/to 检验文件是否为不可执行文件
  

#### if 指令举例

```nginx
if ($http_user_agent ~ MSIE) {
    rewrite ^(.*)$ /msie/$1 break;
}

location /xiao/ {
    if ($http_user_agent ~ Mozilla/5.0) { #如果是chrom浏览器
        rewrite ^(.*)$ http://www.example.com; #返回客户端302
    } 
    if ($http_user_agent ~ curl) { # 如果是curl 发起请求
        rewrite ^/xiao/(.*)$ /xiao/$1.txt break; #得到新的URI
    }  
} 

if ($http_cookie ~* "id=([^;]+)(?:;|$)") {
    set $id $1;  #提取了变量$1
}

if ($request_method = POST) {
    return 405; #可以限制Request Method
}

if ($slow) {
    limit_rate 10k; #这个配置会加在location里面
}

if ($invalid_referer) {
    return 403;
}
```


## return


基本语法：return code [text];或者return code URL;或者return URL;

上下文：server, location, if

停止任何的进一步处理，并且将指定状态码返回给客户端。如果状态码为444(此状态码是非标准的)，那么直接关闭此TCP连接。

return的参数有四种形式：



* `return code`此时，响应内容就是nginx所默认的，比如503 Service Temporarily Unavailable； 如果是444那就直接关闭TCP连接，也可以是其他值(644等)，但是没啥意义    
* `return code text`因为要带响应内容，因此code不能是具有跳转功能的30x    
* `return code URL`此时URI可以为URI做内部跳转，也可以是具有“      [http://”或者“https://”等协议的绝对URL，直接返回客户端，而code是30x(301][0]
, 302, 303, 307,308)    
* `return URL`此时code默认为302，而URL必须是带“      [http://”等协议的绝对URL][1]
    
  

## set


基本语法：set $variable value;

上下文：server, location, if

这是一个有用的指令，用来定义变量，变量的值可以包含字符串，另外的变量或者是二者结合。

```nginx
set $var = $http_x_forwarded_for;
```

注意：在Nginx中，除非特殊说明，大部分地方字符串的不需要引号括住，字符串和变量的拼接也不需要引号


## rewrite_log


基本语法：rewrite_log on | off;

上下文：http, server, location, if

如果开启 on，那么当发生rewrite时，会产生一个notice级别的日志；否则不会产生任何日志。默认情况下是不产生的，但在调试的时候可以将其置为on。

以上这些指令，基本涵盖了rewrite模块的所有应用，在需要改写请求URI，或者做跳转时非常有用。



[0]: http://%E2%80%9D%E6%88%96%E8%80%85%E2%80%9Chttps//%E2%80%9D%E7%AD%89%E5%8D%8F%E8%AE%AE%E7%9A%84%E7%BB%9D%E5%AF%B9URL%EF%BC%8C%E7%9B%B4%E6%8E%A5%E8%BF%94%E5%9B%9E%E5%AE%A2%E6%88%B7%E7%AB%AF%EF%BC%8C%E8%80%8Ccode%E6%98%AF30x(301
[1]: http://%E2%80%9D%E7%AD%89%E5%8D%8F%E8%AE%AE%E7%9A%84%E7%BB%9D%E5%AF%B9url/
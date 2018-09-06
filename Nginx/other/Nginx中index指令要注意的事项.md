## Nginx中index指令要注意的事项

来源：[http://blog.7rule.com/2018/02/28/nginx-index.html](http://blog.7rule.com/2018/02/28/nginx-index.html)

时间 2018-02-28 08:00:00



## 缘起

最近在做一个nginx配置的时候，遇到个问题，请大家先看第一个配置：

```nginx
server {
    listen 80; 

    server_name www.vmc7.com;

    access_log logs/vmc7.log;
    error_log logs/vmc7_error.log debug;

    root /home/web/www;
    index index.php;

    location = /index.php {
    ......
    }   
}
```

当访问www.vmc7.com时，和预想的一样，会实际执行/home/web/www下面的index.php。

下面再来看第二个配置：

```nginx
server {
    listen 80; 
    server_name www.abc.com;

    index index.html index.php;

    location / { 
        root /home/web/abc/php/;
    }   

    location = /index.html {
        root /home/web/abc/html/;
    }   
}
```

其中：



* /home/web/abc/php下存在index.php。
* /home/web/abc/html下存在index.html。
  

这次，当访问www.abc.com时，我以前本以为会执行index.html，但实际执行到的却是index.php。


## 自己的误解

请求进入哪一个location，是根据request_uri决定的。由于第一个配置和自己的预期相符，所以我主观的认为index指令配置的值，会附加到初始的request_uri后面，再去寻找location。

以第一个例子来说，请求www.vmc7.com，实际在寻找location时，会用/index.php去找。但是如果是这样的话，第二个例子在请求www.abc.com时，应该会用index.html去找，这样就应该执行index.html，但结果却不是这样的。


## index指令学习

基于上面的现象，我认识到自己对index指令的理解存在问题，所以决定打开手册好好学习一下。

恍然大悟，总结下index指令会做的事情如下：



* 这是一个content阶段的指令。
* 仅处理request_uri结尾为”/”的请求。
  

请求处理逻辑如下：



* 首先对index指令配置的多个文件做顺序查找，看文件是否存在。
* 如果存在，就结束查找过程，把这个文件附加到请求的request_uri后面，并且发起一个内部的redirect。
* 如果全部尝试后都不存在，那么该index指令执行结束，nginx会继续执行content阶段下一个指令的事情。
  

伪代码如下：

```nginx
find = false;

for (file in file_list) {
    if (file_exists(file)) {   
        find        = true;
        request_uri = request_uri + file;
        break;
    }   
}

if (find) {
    redirect(request_uri);
}
```


## 重新分析请求

为了印证对index指令的理解，我重新编译nginx，并将error_log级别设置为debug，分析www.vmc7.com请求，输出如下：

```
2014/07/30 17:41:11 [debug] 31021#0: *1096 rewrite phase: 0
2014/07/30 17:41:11 [debug] 31021#0: *1096 test location: "/index.php"
2014/07/30 17:41:11 [debug] 31021#0: *1096 using configuration ""
......
2014/07/30 17:41:11 [debug] 31021#0: *1096 content phase: 9
2014/07/30 17:41:11 [debug] 31021#0: *1096 open index "/home/web/www/index.php"
2014/07/30 17:41:11 [debug] 31021#0: *1096 internal redirect: "/index.php?"
2014/07/30 17:41:11 [debug] 31021#0: *1096 rewrite phase: 0
2014/07/30 17:41:11 [debug] 31021#0: *1096 test location: "/index.php"
2014/07/30 17:41:11 [debug] 31021#0: *1096 using configuration "=/index.php"
```

在请求`www.vmc7.com`时，第一次并没有进入到现有的location中，之后在content阶段执行index指令，查找到配置的index.php文件存在后，把request_uri改为`/index.php`再发起redirect，最终进入到`location = /index.php{}`中。

有兴趣的话，大家可以自行分析我第二个配置中`www.abc.com`请求会如何做。



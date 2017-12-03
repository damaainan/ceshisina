# Nginx stub_status 监控模块

 时间 2017-11-30 23:37:00  

原文[https://sometimesnaive.org/article/webfield/nginx/module-stub_status.html][1]


文章目录 

Nginx 的 stub_status 模块用于查看 Nginx 的基本状态信息。 

## 模块实现的功能 

通过这个模块，可以看到如下的 Nginx 状态信息： 

    Active connections: 2
    server accepts handled requests
     62 62 102
    Reading: 0 Writing: 1 Waiting: 0
    

这里是我的 stub_status 页面： [https://sometimesnaive.org/Nanqinlang_status/][3]

其中：

* Active connections ：活跃的连接数
* accepts ：已允许的请求数
* handled ：已处理完毕的请求数
* requests ：总请求数
* Reading ：读取到客户端的 Header 信息数
* Writing ：返回给客户端的 Header 信息数
* Waiting ：开启 keep-alive 的情况下，这个值等于 (Active 减 Reading 减 Writing)，表示 Nginx 已经响应结束的那些请求中，正在等候下一次请求指令的驻留连接

在访问频率高、请求较快处理完毕时，Waiting 较大是正常的；若 Reading + Writing 较大，则说明并发访问数较大。

## 安装模块 

这个模块 Nginx 源码已包含在内，但默认不会编译进来，需要启用这条编译参数： 

    --with-http_stub_status_module
    

## 启用模块 

要启用这个模块，只需要在站点配置写入一行： 

    stub_status on;
    

以我的配置为例： 
```nginx
    location = /Nanqinlang_status/ {
        stub_status on;
    }
```

[1]: https://sometimesnaive.org/article/webfield/nginx/module-stub_status.html

[3]: https://sometimesnaive.org/Nanqinlang_status/
# 使用Webbench测试当前Web服务器的性能

 时间 2017-08-12 18:55:00  明月学习笔记

原文[https://lnmp.ymanz.com/linux/56.html][1]


Webbench是一个在linux下使用的非常简单的网站压测工具。它使用fork()模拟多个客户端同时访问我们设定的URL，测试网站在压力下工作的性能，最多可以模拟3万个并发连接去测试网站的负载能力。

### 安装

    wget http://blog.s135.com/soft/linux/webbench/webbench-1.5.tar.gz
    tar zxvf webbench-1.5.tar.gz
    cd webbench-1.5
    make && make install

### 使用示例

    #WebBench使用
    webbench -c 1000 -t 60 http://192.168.80.157/phpinfo.php
    #webbench -c 并发数 -t 运行测试时间 URL
    
    #Apache测试实例结果：
    
    #当并发300时，
    webbench -c 300 -t 60 http://192.168.80.157/phpinfo.php
    Webbench - Simple Web Benchmark 1.5
    Copyright (c) Radim Kolar 1997-2004, GPL Open Source Software.
    
    Benchmarking: GET http://192.168.80.157/phpinfo.php
    300 clients, running 60 sec.
    
    Speed=24525 pages/min, 20794612 bytes/sec.
    Requests: 24525 susceed, 0 failed.
    
    #每秒钟响应请求数：24525 pages/min，每秒钟传输数据量20794612 bytes/sec.
    
    #当并发1000时，已经显示有87个连接failed了，说明超负荷了。
    
    webbench -c 1000 -t 60 http://192.168.80.157/phpinfo.php
    Webbench - Simple Web Benchmark 1.5
    Copyright (c) Radim Kolar 1997-2004, GPL Open Source Software.
    
    Benchmarking: GET http://192.168.80.157/phpinfo.php
    1000 clients, running 60 sec.
    
    Speed=24920 pages/min, 21037312 bytes/sec.
    Requests: 24833 susceed, 87 failed.
    
    #并发1000运行60秒后产生的TCP连接数12000多个：

![][4]

### 具体参数详解：

短参数 | 长参数 | 作用 
-|-|-
-f | --force | 不需要等待服务器响应 
-r | --reload | 发送重新加载请求 
-t | --time | 运行多长时间，单位：秒" 
-p | --proxy | server:port 使用代理服务器来发送请求 
-c | --clients | 创建多少个客户端，默认1个" 
-9 | --http09 | 使用 HTTP/0.9 
-1 | --http10 | 使用 HTTP/1.0 协议 
-2 | --http11 | 使用 HTTP/1.1 协议 
- | --get | 使用 GET请求方法 
- | --head | 使用 HEAD请求方法 
- | --options | 使用 OPTIONS请求方法 
- | --trace | 使用 TRACE请求方法 
-?/-h | --help | 打印帮助信息 
-V | --version | 显示版本号 

### 总结：

* 1、压力测试工作应该放到产品上线之前，而不是上线以后；
* 2、测试时并发应当由小逐渐加大，比如并发100时观察一下网站负载是多少、打开页面是否流畅，并发200时又是多少、网站打开缓慢时并发是多少、网站打不开时并发又是多少；
* 3、更详细的进行某个页面测试，如电商网站可以着重测试购物车、推广页面等，因为这些页面占整个网站访问量比重较大。


[1]: https://lnmp.ymanz.com/linux/56.html

[4]: http://img1.tuicool.com/BNVfee.png
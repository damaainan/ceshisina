## ab

ab，全称是apache benchmark，是apache官方推出的工具。  
该工具是用来测试Apache服务器的性能的。查看安装的apache的服务器能提供的服务能力，每秒可以处理多少次请求。

### 获取和安装

在编译apache服务器的时候，会一起编译出来。这里就不赘述了。

### 使用方法

由于OSS的bucket有权限，而ab不支持OSS的签名，需要将bucket变成public-read-write（公开读写）后进行测试。

假如模拟的是10个并发，请求100KB的Object  
ab 执行时常用的配置项  
-c 并发数  
一次发送的总请求数，默认是一次发一个请求。

-k 保持keep-alive  
打开keep-alive，在一个HTTP Session中请求多次。默认是关闭的。

-n 请求数  
整个benchmark测试过程中需要发送的请求次数。  
默认是一次，默认情况下得到的性能参数没有代表性。

-t 最大时间  
benchmark测试最长时间. 默认没有限制。

-u 上传文件  
File containing data to PUT. Remember to also set -T.-T content-type

-T 设置上传文件的Content-Type  
例如：application/x-www-form-urlencoded. Default is text/plain.

### 使用示例

* 测试OSS高并发的读写小文件场景性能
* 前置条件

> 创建了一个public-read-write的bucket，假如叫public。下载了ab测试工具（开源）,linux运行环境。oss提供可服务的endpoint，假如叫oss-cn-hangzhou-test.aliyuncs.com，准备5KB的文件，假如叫5KB.txt
* 测试过程


  1. > 模拟小文件（5KB），高并发（50个线程）的写，运行5分钟 ./ab -c 50 -t 300 -T 'text/plain' -u 5KB.txt 
  1. > 模拟小文件（5KB），高并发（50个线程）的读，运行5分钟 ./ab -c 50 -t 300 
* 预期结果

> 测试正常结束 ，Failed requests 为0，Requests per second即表示每秒中客户端能获取的处理能力。这不代表OSS服务端的处理能力。

### 注意事项

* 观察测试工具ab所在机器，以及被测试的前端机的CPU，内存，网络等都不超过最高限度的75%。
* 测试中可能出现端口不足导致的测试失败

> 需要调整内核参数以支持端口重用  
> 例如：在Linux平台下  
> 1 sudo vim /etc/sysctl.conf  
> 2 添加如下内容  
> net.ipv4.tcp_syncookies = 1  
> net.ipv4.tcp_tw_reuse = 1  
> net.ipv4.tcp_tw_recycle = 1  
> net.ipv4.tcp_fin_timeout = 30  
> kernel.printk = 7 4 1 7  
> 3 运行sudo sysctl –p生效

### 结果分析

    $./ab -c 50 -t 60 -n 300000 -k http://oss-cn-hangzhou-test.aliyuncs.com/public/5KB.txt
    This is ApacheBench, Version 2.3 <$Revision: 655654 $>
    Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
    Licensed to The Apache Software Foundation, http://www.apache.org/
    
    Benchmarking oss-cn-hangzhou-test.aliyuncs.com (be patient)
    Completed 30000 requests
    Completed 60000 requests
    Completed 90000 requests
    Completed 120000 requests
    Completed 150000 requests
    Completed 180000 requests
    Completed 210000 requests
    Completed 240000 requests
    Finished 250137 requests
    
    
    Server Software:        AliyunOSS
    Server Hostname:        oss-cn-hangzhou-test.aliyuncs.com
    Server Port:            80
    
    Document Path:          /public/5KB.txt
    Document Length:        5120 bytes
    
    Concurrency Level:      50             并发数
    Time taken for tests:   60.000 seconds 测试运行的时间
    Complete requests:      250137         在运行期间完成的总请求次数
    Failed requests:        0
    Write errors:           0
    Keep-Alive requests:    248492         keep-alive的请求次数
    Total transferred:      1382504896 bytes
    HTML transferred:       1280703929 bytes
    Requests per second:    4168.94 [#/sec] (mean)   每秒的请求次数
    Time per request:       11.993 [ms] (mean)       平均每次请求的时延
    Time per request:       0.240 [ms] (mean, across all concurrent requests)
    Transfer rate:          22501.67 [Kbytes/sec] received
    
    Connection Times (ms)    请求连接的时间
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       1
    Processing:     1   12   7.6     12      87
    Waiting:        1   12   7.6     12      87
    Total:          1   12   7.6     12      87
    
    Percentage of the requests served within a certain time (ms) 请求的半分比及时延
      50%     12
      66%     15
      75%     16
      80%     17
      90%     20
      95%     23
      98%     28
      99%     37
     100%     87 (longest request)
    
    

从测试结果，我们可以看到

* 在50个并发请求的情况下，请求60秒，平均每秒可以处理4168次（也就是说，客户端在这种压力下，看到的QPS为4168）
* 平均每次请求处理的Latency为12ms左右
* 由于开启了keep-alive，连接几乎不耗时间
* 99%的请求都在37ms内完成，最长的请求是87ms
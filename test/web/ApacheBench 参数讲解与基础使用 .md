## ApacheBench 参数讲解与基础使用 


## 目录

* [认识 Apache Bench][0]
* [Apache Bench 的命令参数][1]
* [Apache Bench 的结果参数][2]

## 认识 Apache Bench

Apache Bench 是 Apache 自带的一个命令式程序。它可以创建并发访问线程，同时模拟多个并发请求对某一个 URL 地址进行访问。它的测试目标是基于 URL 的。因此，Apache Bench 即可测试 Apache 的负载压力，也能测试 Nginx、LigntHttp、Tomcat、IIS 等其他 Web Server 的压力。

Apache Bench 命令对发出负载的计算机要求很低，既不会占用很高 CPU，也不会占用很多内存，但却会给目标服务器造成巨大的负载，其原理类似 CC 攻击。在测试使用也须注意，否则一次上太多的负载，可能造成目标服务器因资源耗完，严重时甚至导致死机。

## Apache Bench 的命令参数

参数说明：

* -n requests [Number of requests to perform]

> 执行请求的数量

* -c concurrency [Number of multiple requests to make at a time]

> 一次并发请求的数量

* -t timelimit [Seconds to max. to spend on benchmarking]

> 测试进行的最大秒数

* -s timeout [Seconds to max. wait for each response,Default is 30 seconds]

> 响应等待的最大秒数

* -b windowsize [Size of TCP send/receive buffer, in bytes]

> TCP 发送/接受缓冲区的大小，以字节为单位

* -p postfile [File containing data to POST. Remember also to set -T]

> 包含了需要 POST 的数据的文件，需要设置 -T 参数

* -u putfile [File containing data to PUT. Remember also to set -T]

> 包含了需要 PUT 的数据的问题，需要设置 -T 参数

* -T content-type [Content-type header to use for POST/PUT data, eg.'application/x-www-form-urlencoded'，Default is 'text/plain']

> POST/PUT 数据时需要使用的 Content-type 请求头部

* -v verbosity [How much troubleshooting info to print]

> 打印什么级别的故障排除信息

* -w [Print out results in HTML tables]

> 以 HTML 表格的格式打印出执行结果

* -i [Use HEAD instead of GET]

> 使用 HEAD 请求方法代替 GET 请求方法

* -x attributes [String to insert as table attributes]

> 字符串作为 table 元素的属性插入

* -y attributes [String to insert as tr attributes]

> 字符串作为 tr 元素的属性插入

* -z attributes [String to insert as td or th attributes]

> 字符串作为 td 元素的属性插入

* -C attribute [Add cookie, eg. 'Apache=1234'. (repeatable)]

> 添加一个 Cookie

* -H attribute [Add Arbitrary header line, eg. 'Accept-Encoding: gzip',Inserted after all normal header lines. (repeatable)]

> 添加任意的 HTTP 头部属性

* -A attribute [Add Basic WWW Authentication, the attributes,are a colon separated username and password.]

> 添加基础的 HTTP WWW 认证

* -P attribute [Add Basic Proxy Authentication, the attributes,are a colon separated username and password.]

> 添加基础代理身份验证

* -X proxy:port [Proxyserver and port number to use]

> 代理服务器的端口号

* -V [Print version number and exit]

> 打印 ApacheBench 的版本号并退出 ApacheBench

* -k [Use HTTP KeepAlive feature]

> 使用 HTTP KeepAlive 长连接

* -d [Do not show percentiles served table.]

> 不要显示服务表的百分比

* -S [Do not show confidence estimators and warnings.]

> 不要显示信息评估和警告

* -q [Do not show progress when doing more than 150 requests]

> 当超过 150 个请求时，不显示进度

* -l [Accept variable document length (use this for dynamic pages)]

> 接受可变文档长度（用于动态页面）

* -g filename [Output collected data to gnuplot format file.]

> 将收集到的数据输出到格式为 gnuplot 的文件

* -e filename [Output CSV file with percentages served]

> 将执行过程百分比的数据输出到格式为 csv 的文件

* -r [Don't exit on socket receive errors.]

> 当接收到套接字错误时不要退出执行

* -m method [Method name]

> 执行请求时所使用的 HTTP 请求方法

* -h [Display usage information (this message)]

> 显示 ApacheBench 使用参数信息

* -I [Disable TLS Server Name Indication (SNI) extension]

> 禁用 TLS 服务器名称指示扩展名

* -Z ciphersuite [Specify SSL/TLS cipher suite (See openssl ciphers)]

> 指定 SSL/TLS 密码套件

* -f protocol [Specify SSL/TLS protocol(SSL3, TLS1, TLS1.1, TLS1.2 or ALL)]

> 指定 SSL/TLS 协议

## Apache Bench 的结果参数

* Server Software

> 服务器软件与版本

* Server Hostname

> 服务器域名或地址

* Server Port

> 服务器端口号

* Document Path

> 请求文件的路径

* Document Length

> 请求文件的大小

* Concurrency Level

> 每次并发数

* Time taken for tests

> 测试总时间

* Complete requests

> 处理请求成功的次数

* Failed requests

> 处理请求失败的次数

* Total transferred

> 测试过程传输字节数

* HTML transferred

> HTML 内容传输字节数

* Requests per second

> 每秒处理的请求数 - 吞吐率 - 平均返回数据时间，相当于 Complete requests / Time taken for tests

* Time per request

> 用户等待响应的平均时间，相当于 Time taken for tests /（Complete requests / Concurrency Level）

* Time per request (mean, across all concurrent requests)

> 服务器处理并发请求的平均时间，相当于 Time taken for tests / Complete requests

* Transfer rate

> 请求在单位时间内从服务器获取的数据长度，相当于 Total transferred / Time taken for tests

* Connection Times (ms)
    * Connect：连接服务器的时间
    * Processing：进程处理完成请求的时间
    * Waiting：客户端等待结果返回的时间
    * Total：页面完成渲染的时间

* Percentage of the requests served within a certain time (ms)

> 所有请求的平均速度，如在测试过程中进度到50%时平均响应时间为10148ms，到66%时  
> 平均响应时间为11054ms。

[0]: #1
[1]: #2
[2]: #3
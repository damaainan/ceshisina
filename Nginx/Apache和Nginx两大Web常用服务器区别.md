## Apache和Nginx两大Web常用服务器有什么区别？你知道吗？

来源：[http://server.51cto.com/os-585743.htm](http://server.51cto.com/os-585743.htm)

时间 2018-10-26 10:41:32

 
本篇文章没有把IIS服务器列入其中，因为IIS只能在Windows上跑，而Apache和Nginx可以在各种平台上跑。
 
#### 一、分析两大服务器：
 
1.Apache
 
Apache 是世界排名第一的 web 服务器，根据 netcraft 所作的调查，世界上百分之五十以上的 web 服务器在使用 Apache。
 
1995 年 4 月，最早的 Apache(0.6.2 版 ) 由 Apache group 公布发行。Apache group 是一个完全通过 internet 进行运作的非盈利机构，由它来决定 Apache web 服务器的标准发行版中应该包含哪些内容。 准许任何人修改隐错，提供新的特征和将它移植到新的平台上，以及其它的工作。当新的代码被提交给 Apache group 时，该团体审核它的具体内容，进行测试，如果认为满意，该代码就会被集成到 Apache 的主要发行版中。
 
Apache 的特性 :

 
* 几乎可以运行在所有的计算机平台上 
* 支持最新的 http/1.1 协议 
* 简单而且强有力的基于文件的配置 (httpd.conf) 
* 支持通用网关接口 (cgi) 
* 支持虚拟主机 
* 支持 http 认证 
* 集成 perl 
* 集成的代理服务器 
* 可以通过 web 浏览器监视服务器的状态，可以自定义日志 
* 支持服务器端包含命令 (ssi) 
* 支持安全 socket 层 (ssl) 
* 具有用户会话过程的跟踪能力 
* 支持 fastcgi 
* 支持 java servlets 
 
 
![][0]
 
2.Nginx
 
Nginx 是俄罗斯人编写的十分轻量级的 http 服务器，Nginx的发音为 “engine X”，是一个高性能的 http 和反向代理服务器，同时也是一个 IMAP/POP3/SMTP 代理服务器。Nginx  是由俄罗斯人 Igor Sysoev 为俄罗斯访问量第二的 Rambler.ru 站点开发。
 
Nginx是以事件驱动的方式编写，所以有非常好的，性能，同时也是一个非常高效的反向代理、负载平衡。其拥有匹配 lighttpd 的性能。Nginx 做为 http 服务器，有以下几项基本特性：
 
处理静态文件，索引文件以及自动索引；打开文件描述符缓冲，无缓存的反向代理加速，简单的负载均衡和容错。fastcgi，简单的负载均衡和容错。模块化的结构包括：gzipping, byte ranges, chunked responses, 以及 SSI-filter 等 filter。如果由 fastcgi 或其它代理服务器处理单页中存在的多个 SSI ，则这项处理可以并行运行，而不需要相互等待。
 
Nginx 专为性能优化而开发，性能是其最重要的考量，实现上非常注重效率。它支持内核 Poll 模型，能经受高负载的考验，有报告表明能支持高达 50,000 个并发连接数。
 
Nginx 具有很高的稳定性。其它 http 服务器，当遇到访问的峰值，或者有人恶意发起慢速连接时，也很可能会导致服务器物理内存耗尽频繁交换，失去响应，只能重启服务器。例如当前 Apache 一旦上到 200 个以上进程， web 响应速度就明显非常缓慢了。而 Nginx 采取了分阶段资源分配技术，使得它的 CPU 与内存占用率非常低。 Nginx 官方表示保持 10,000 个没有活动的连接，它只占 2.5M 内存，所以类似 DDOS 这样的攻击对 Nginx 来说基本上是毫无用处的。就稳定性而言。
 
Nginx 支持热部署。它的启动特别容易，并且几乎可以做到 7 * 24 不间断运行，即使运行数个月也不需要重新启动。你还能够在不间断服务的情况下，对软件版本进行进行升级。
 
#### 二、两种 web 服务器的比较：
 
![][1]
 
注：在相对比较大的网站，节约下来的服务器成本无疑是客观的。而有些小型网站往往服务器不多，如果采用Apache这类传统Web服务器，似乎也还能撑过去。但有其很明显的弊端：Apache在处理流量爆发的时候(比如爬虫或者是Digg效应)很容易过载，这样的情况下采用Nginx最为合适。
 
建议方案：
 
Apache后台服务器（主要处理php及一些功能请求如：中文url）Nginx前端服务器（利用它占用系统资源少得优势来处理静态页面大量请求）Lighttpd图片服务器
 
总体来说，随着Nginx功能得完善将使他成为今后web server得主流。
 
三 、性能测试 ：
 
将分别测试 2种软件在对动态页面和静态页面请求及并发时的响应时间
 
l静态页面 搜狐首页
 
Nginx
 
![][2]
 
Apache
 
![][3]
 
l动态页面内部社区首页
 
Nginx
 
![][4]
 
Apache
 
![][5]
 
lPHPINFO 函数页
 
Nginx
 
![][6]
 
Apache 出现丢包
 
![][7]


[0]: ./img/AzYfieQ.jpg
[1]: ./img/6jmyQ3V.jpg
[2]: ./img/jMNnaqr.jpg
[3]: ./img/r2qE3aA.jpg
[4]: ./img/zqMjUbz.jpg
[5]: ./img/yQFzU33.jpg
[6]: ./img/MnUBN3U.jpg
[7]: ./img/ZRNvInz.jpg
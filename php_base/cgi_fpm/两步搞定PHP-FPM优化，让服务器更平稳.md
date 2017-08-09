# 两步搞定PHP-FPM优化，让服务器更平稳

 时间 2017-08-09 13:31:07  

原文[http://www.yunweipai.com/archives/22163.html][1]



导言：Web服务器的CPU指标和MEM指标异常，不稳定？可能是PHP-FPM进程重启机制的问题导致的，一同和百度外卖探索下如何优化吧。

#### 作者简介：

猛哥 

百度外卖基础架构部在线开发方向负责人 

负责在线开发框架的持续优化和长期演进，主导研发的WFE/WODP/WOSP组成一套完整高效的WEB架构，支撑百度外卖所有在线服务高速高质量地发展。

百度外卖基础架构部在线开发团队 

擅长自底向上地创造和优化基础框架、基础库和基础服务，对NGINX和PHP有深层次的理解和创新性的改造。天下大事必作于细，百度外卖基础架构部在线开发团队，将持续精耕细作，适时发布成熟有效的在线服务优化方案，期待与同行保持交流，共同进步。

##  摘要 

通过优化PHP-FPM进程重启机制，改善线上服务器 `CPU_IDLE` 和 `MEM_USED` 波动的问题，使服务器资源利用率更加平滑可靠。 

![][3]

##  背景 
外卖交易服务集群报出在监控图上 `CPU_IDLE` 波动剧烈，如图所示。 

事实上一直以来，不仅 PU_IDLE 存在一定的波动， `MEM_USED` 的周期性断崖式下降再回升也早已司空见惯。那么 `CPU_IDLE` 与 `MEM_UESD` 的波动是否存在关联，追溯这种现象产生的原因，我们就必须理解PHP-FPM进程管理器的机制。 

##  原理 
在PHP5.3.3版本中，PHP-FPM正式被官方收编，作为FastCGI管理器，支持平滑停止启动进程、slow-log、动态进程、运行状态等特性。

PHP-FPM进程管理支持三种方式： `static` 、 `dynamic` 、 `ondemand` 。我们选用的是 `static` 方式，即PHP-FPM生成固定数量的FastCGI进程，这种方式比较简单，避免了频繁开启关闭进程的开销。（在线下虚拟机环境中，进程管理可以配置成 `ondemand` ，既降低了内存需求又避免了进程数量不够用） 

回到面临的问题上， `CPU_IDLE` 和 `MEM_USED` 的周期性波动是如何产生的。首先这是一种所有的集群都存在的现象，然后交易服务集群表现尤为突出。在排查了应用程序（比如日志采集程序、定时脚本）的影响后，思路落在了PHP-FPM的一个关键参数上： `max_requests` 。 

`max_requests` 这个参数使FastCGI进程在处理一定数量的请求后自动重启，以此避免第三方扩展内存泄漏产生破坏性影响。打开线上配置，发现外卖交易服务集群中配置该参数过小，为1000，这便造成了在请求高峰期，FastCGI频繁重启，对CPU产生了负担。于是将 `max_requests` 参数调整为10000后， `CPU_IDLE` 表现得到了改善，如图。 

![][4]

但是经过观察发现， `CPU_IDLE` 和 `MEM_USED` 周期性波动的问题并没有根除，效果如图。 

![][5]

这很好理解，我们调大 `max_requests` 参数，但是FastCGI重启机制依然生效，每个请求都会计数，当计数到达 `max_request` 之后，cgi进程会执行 `fcgi_finish_request` 退出进程，子进程退出， fpm-`master` 进程会收到SIGCHLD信号，运行 `fpm_children_bury` 重启进程，重启的方式是fork一个子进程。 

FastCGI进程通过unix socket承接Nginx请求，负载较为均衡，生产环境流量大，PHP进程数配置较大，数以百计的FastCGI会在同一时间到达 `max_requests` 上限而进行重启，这便造成了 `CPU_IDLE` 和 `MEM_USED` 周期性波动。 

##  优化 

`max_requests` 的初衷是为了避免第三方扩展引起的内存泄漏问题，虽然线上环境使用的扩展经过分析和测试，并没有严重的内存泄漏问题，但是由于扩展内部使用的第三方库太多，并无法完全避免内存泄漏问题，同时 `max_requests` 机制很适合FastCGI多进程环境，以较小的代价，换取内存泄漏的长治久安。 

为了避免 `CPU_IDLE` 和 `MEM_USED` 周期波动，同时保持 `max_requests` 机制，需要在PHP-FPM源码上稍作修改。FastCGI进程在启动时，设置 `max_requests` ，此时只要将 `max_requests` 配置参数散列开，使FastCGI进程分别配置不同的值，即可达到效果。 

具体代码在 `sapi/fpm/fpm/fpm.c` ，修改如下： 

php_mt_srand(GENERATE_SEED()); *max_requests=fpm_globals.max_requests+php_mt_rand()&8191;

##  总结 经过修改上线，对比效果见下图

![][6]

至此 `CPU_IDLE` 和 `MEM_USED` 已经告别了周期性波动，避免了CPU计算资源产生浪涌效果，内存占用数据也更加真实可靠。 

以此文抛砖引玉，PHP-FPM在生产环境的精细优化，任重而道远。

文章来自微信公众号：高效运维


[1]: http://www.yunweipai.com/archives/22163.html
[3]: ./img/V3aIz2j.jpg
[4]: ./img/NryURzu.jpg
[5]: ./img/RV7FRjv.jpg
[6]: ./img/VRBfIrn.jpg
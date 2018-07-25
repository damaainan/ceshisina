## PHP-FPM 调优：为了高性能使用 pm static

来源：[http://www.phpyc.com/php-fpm-tuning-using-pm-static-max-performance/](http://www.phpyc.com/php-fpm-tuning-using-pm-static-max-performance/)

时间 2018-05-05 11:52:00

 
让我们快速了解如何更好的设置 PHP-FPM，以实现高吞吐量和低延迟
 
默认情况下，大多数设置都将 PHP-FPM 的 PM（进程管理器）设置为`dynamic`，并且如果遇到内存不足的问题，还需要使用`ondemand`让我们看一下 php.net 文档中的选项，并介绍我最喜欢的设置 - static：
 
 
* `pm = dynamic`: 子进程的数量根据以下配置动态设置`pm.max_children, pm.start_servers, pm.min_spare_servers, pm.max_spare_servers` 
* `pm = ondemand`: 进程在请求时按需创建，而不是动态的，其中`pm.start_servers`进程数量在服务启动时创建  
* `pm = static`: 子进程的数量由`pm.max_children`决定  
 
 
## PHP-FPM（PM）与 CPUFreq 的相似之处
 
这看起来有点偏离主题，但我希望将其结合到我们的 PHP-FPM 调优主题中
 
我们都遇到过 CPU 缓慢的问题，无论是笔记本，虚拟机还是服务器。
 
你还记得 CPU 调频吗？（CPUFreq），它可以在 linux 和 Windows 上使用，可以将 CPU 频率设置为`ondemand`来提高性能和系统响应能力。
 
现在，我们来比较一下这些描述并寻找相似之处：
 
 
* `Governor = ondemand`: 按需快速动态调整 CPU 频率， 一有 cpu 计算量的任务，就会立即达到最大频率运行，空闲时间增加就降低频率  
* `Governor = conservative`: 按需快速动态调整 CPU 频率， 比 ondemand 的调整更保守  
* `Governor = performance`: 总是运行于最大频率  
 
 
有关更多详细信息，请参阅 CPUFreq 调控器选项的完整列表
 
有没有注意相似之处呢 ？
 
## 使用`pm static`来实现最高性能 
 `pm static`设置在很大程度上取决于您的服务器有多少空闲内存。
 
基本上，如果你的服务器内存很低，那么`pm ondemand`或`dynamic`可能是更好的选择。
 
如果您拥有足够的内存，则可以设置`pm static`来避免大部分 PM 开销。
 
换句话说，当您进行数学运算时，应将`pm.static`设置为服务器可运行的最大数量的进程数，它就不会有内存不足或缓存压力的问题
 
![][0]
 
 
   在上面的截图中，PHP-FPM 的配置为 `pm = static`和 `pm.max_children = 100`
 
它有 32GB的内存，在截图期间，Google Analytics 中约有 200 个 “活跃用户”（过去 60 秒）。
 
在这个级别上，约有`70％`的 PHP-FPM 进程仍然闲置。
 
这意味着 PHP-FPM 设置为服务器资源的最大容量后，它不会去在意当前流量，空闲进程会保持联机状态，等待流量高峰立即响应，而不必等到请求来了之后再创建进程
 
 
 
我将`pm.max_requests`设置的非常高，因为这是一个没有 PHP 内存泄漏的生产服务器。
 
如果您对当前和将来的 PHP 代码有 110％ 的信心，可以将`pm.max_requests = 0`与`pm static`一起使用
 
## 何时使用 ondemand 和 dynamic
 
使用`pm dynamic`，您可能会出现类似于下面的错误：
 
``` 
WARNING: [pool xxxx] seems busy (you may need to increase pm.start_servers, or pm.min/max_spare_servers), spawning 32 children, there are 4 idle, and 59 total children
```
 
您可能会尝试调整 pm 配置，但仍然会看到同样的错误
 
在这种情况下，`pm.min`太低，并且因为流量和峰值波动很大，使用`pm dynamic`可能难以调整
 
一般的建议是使用`pm ondemand`。 然而，情况会变的更糟，因为`ondemand`会在没有流量时关闭空闲进程，然后最终会产生与流量波动很大一样的开销问题 (除非您设置空闲超时的时间非常非常的长）
 
但是，当您拥有多个 pm 进程池时，`pm dynamic`， 特别是`ondemand`是可以为您节省时间的
 
## 结论
 
当流量波动比较大的时候，，PHP-FPM 的`ondemand`和`dynamic`会因为固有开销而限制吞吐量。 您需要了解您的系统并设置 PHP-FPM 进程数，以匹配服务器的最大容量。
 
从`pm.max_children`开始，根据`pm dynamic`或`ondemand`的最大使用情况去设置
 
您会注意到，在`pm static`模式下，因为您将所有内容都保存在内存中，所以随着时间的推移，流量峰值会对 CPU 造成比较小的峰值，并且您的服务器负载和 CPU 平均值将变得更加平滑。 每个需要手动调整的 PHP-FPM 进程数的平均大小会有所不同
 
  
附上一张 A/B 测试图
 
  
![][1]
 
 
 
最后希望这是一篇有用的文章 :grin:
 
 
   本文由 Enda 翻译至 
  [ PHP-FPM tuning: Using ‘pm static’ for Max Performance ][2] 
 
 
其中有一些自己的看法在里面，如果有错误欢迎纠正~ 谢谢
 


[2]: https://www.sitepoint.com/php-fpm-tuning-using-pm-static-max-performance/
[0]: ./img/jmQvQjN.png 
[1]: ./img/JJ3ai2u.png 
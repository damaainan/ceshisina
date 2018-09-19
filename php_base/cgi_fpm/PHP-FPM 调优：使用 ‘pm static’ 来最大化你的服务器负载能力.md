## PHP-FPM 调优：使用 ‘pm static’ 来最大化你的服务器负载能力

来源：[https://segmentfault.com/a/1190000016435378](https://segmentfault.com/a/1190000016435378)

![][0] 
 **`让我们来迅速了解一下怎样设置 PHP-FPM，以便达到高吞吐，低延迟以及稳定的使用 CPU 和内存的完美状态。在默认的情况下，大多数设置都将 PHP-FPM PM（进程管理器）设置为`dynamic `，或者当你有可用内存的问题时常建议你使用`ondemand`。接下来，让我们根据 [php.net][3] 的官方文档来比较一下这两个管理选项和我最常用的设置 ——`static`之间的区别：`** 


* **`pm = dynamic`** ：子进程的数量是根据以下指令来动态生成的：`pm.max_children`, `pm.start_servers`, `pm.min_spare_servers`, `pm.max_spare_servers`.
* **`pm = ondemand`** ：在服务启动的时候根据`pm.start_servers`指令生成进程，而非动态生成。
* **`pm = static`** ：子进程的数量是由`pm.max_children`指令来确定的。

 查看[完整列表][4]，深入了解`php-fpm.conf`的所有指令。 
## PHP-FPM 进程管理器（PM）和 CPUFreq Governor 的相似之处

现在，我们要说些偏离主题，但我觉得和 PHP-FPM 调优有关的事情。好了，我们都有过在某些时候的 CPU 缓慢问题，无论是笔记本电脑、VM 或者是专用的服务器。还记得 CPU 频率缩放问题吗？（[CPUFreq governor][5]）这些设置在类 Unix 系统和 Windows 上是有效的，可以通过修改 CPU governor，将其从`ondemand`修改为`performance`来提高性能并加快系统的响应。现在，让我们来比较下列 CPUFreq governor 描述和 PHP-FPM PM 有哪些相似之处：


* **`Governor = ondemand`** ：根据当前负荷动态调整 CPU 频率。先将 CPU 频率调整至最大，然后随着空闲时间的增加而缩小频率。
* **`Governor = conservative`** ：根据当前负荷动态调整频率。比设置成`ondemand`更加缓慢。
* **`Governor = performance`** ：始终以最大频率运行 CPU。

 查看 [CPUFreq governor 选项详细列表][6] ，获取更多相关信息。 

注意到相似之处了吗？这就是我这个比较的首要目的，为了找到一个最好的方式来写这篇文章，推荐你将 PHP-FPM 的`pm static`当作你的第一选择。

使用 CPU Governor 的`performance`设置是一个非常安全的性能提升方式，因为它能完美的使用你服务器 CPU 的全部性能。唯一需要考虑的因素就是一些诸如散热、电池寿命（笔记本电脑）和一些由 CPU 始终保持 100% 所带来的一些副作用。一旦设置为`performance`，那么它确实是你 CPU 最快的设置。相关实例请阅读 ['force_turbo'][7] 在 Raspberry Pi 上的设置，它教你在 RPi 板上使用`performance`Governor，由于 CPU 时钟速度较低，性能改善将更加明显。
## 使用`pm static`优化你的服务器性能

PHP-FPM 的`static`设置取决于你服务器有多少闲置内存。大多数情况下，如果你服务器的内存不足，那么 PM 设置成`ondemand`或`dynamic`将是更好的选择。但是，一旦你有可用的闲置内存，那么把 PM 设置成`static`的最大值将减少许多 PHP 进程管理器（PM）所带来的开销。换句话说，你应该在没有内存不足和缓存压力的情况下使用`pm.static`来设置 PHP-FPM 进程的最大数量。此外，也不能影响到 CUP 的使用和其他待处理的 PHP-FPM 操作。

![][1]

在上面的截图中，这台服务器的设置（`pm = static`，`pm.max_children =100`）最多使用了 10GB 的内存。请注意高亮的列。Google 分析图中大概有 200 个活跃用户（60秒内）。在这种用户量下，有 70% 的 PHP-FPM 子进程被闲置。这意味着，无论当前流量如何，PHP-FPM 始终保持着足够多的进程。闲置的进程始终保持在线，就算达到了流量的峰值也能快速响应，而不是等待 PM 生成子进程，然后在`x pm.process_idle_timeout`秒后将此进程结束。我将  `pm.max_requests`设置的非常高，因为这是一个不可能发生内存泄漏的 PHP 生产服务器。如果你对你的 PHP 脚本有着 110% 的信心，那么你可用选择使用 `pm.max_requests = 0`。但建议适当的重启服务。将请求数量设置的很高，是为了避免过高的 PM 开销。例如，设置`pm.max_requests = 1000` ，但这需要根据`pm.max_children`的设置和实际每秒的请求数量来决定。

截图使用  [Linux top][8] 通过 'u'（user）选项和 PHP-FPM 用户名进行过滤。并只显示了前 50 个左右（未统计）的进程，但基本上`top`命令也只会显示适合你终端窗口大小的内容 —— 在本例中，使用`%CPU`排序。要查看全部的 100 条 PHP-FPM 进程的话，你需要使用以下命令：

```
top -bn1 | grep php-fpm

```
## 何时使用 ondemand 和 dynamic

使用 `pm dynamic`，您可能会出现类似于下面的错误：

```
WARNING: [pool xxxx] seems busy (you may need to increase pm.start_servers, or pm.min/max_spare_servers), spawning 32 children, there are 4 idle, and 59 total children
```

您可能会尝试调整 pm 配置，但仍然会看到同样的错误\
在这种情况下，`pm.min` 太低，并且因为流量和峰值波动很大，使用 `pm dynamic` 可能难以调整

一般的建议是使用 `pm ondemand`。 然而，情况会变的更糟，因为 `ondemand` 会在没有流量时关闭空闲进程，然后最终会产生与流量波动很大一样的开销问题 (除非您设置空闲超时的时间非常非常的长）

但是，当您拥有多个 pm 进程池时，`pm dynamic`， 特别是 `ondemand` 是可以为您节省时间的。例如在共享的 VPS 上，有 100+ 的 cPanel 账号和 200+ 的域名，使用`pm.static`或者是`pm.dynamic`都是不可能的，即使在没有任何流量的情况下，内存会被瞬间用完，而`pm.ondemand`意味着所有空闲的子进程都会被完全关闭，节省了大量内存。cPanel 的开发者已经意识到了这个问题，现在的 cPanel 默认就是设置为`pm.ondemand`。
## 结论

当流量波动比较大的时候，，PHP-FPM 的 `ondemand` 和 `dynamic` 会因为固有开销而限制吞吐量。 您需要了解您的系统并设置 PHP-FPM 进程数，以匹配服务器的最大容量。\
从 `pm.max_children` 开始，根据 `pm dynamic` 或 `ondemand` 的最大使用情况去设置

您会注意到，在 `pm static` 模式下，因为您将所有内容都保存在内存中，所以随着时间的推移，流量峰值会对 CPU 造成比较小的峰值，并且您的服务器负载和 CPU 平均值将变得更加平滑。 每个需要手动调整的 PHP-FPM 进程数的平均大小会有所不同

更新：附上一张 A/B 测试图。

![][2]

转自 PHP / Laravel 开发者社区 [https://laravel-china.org/top...][9]


[3]: http://php.net
[4]: http://php.net/manual/en/install.fpm.configuration.php
[5]: https://www.kernel.org/doc/Documentation/cpu-freq/governors.txt
[6]: https://www.kernel.org/doc/Documentation/cpu-freq/governors.txt
[7]: https://haydenjames.io/raspberry-pi-3-overclock/
[8]: https://haydenjames.io/linux-top-customize-it/
[9]: https://laravel-china.org/topics/14952
[0]: https://segmentfault.com/img/remote/1460000016435381
[1]: https://segmentfault.com/img/remote/1460000016435382
[2]: https://segmentfault.com/img/remote/1460000016435383
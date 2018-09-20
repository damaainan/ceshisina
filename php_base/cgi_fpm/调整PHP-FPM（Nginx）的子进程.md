## 调整PHP-FPM（Nginx）的子进程

来源：[https://segmentfault.com/a/1190000015920910](https://segmentfault.com/a/1190000015920910)

问题：

日志中出现以下警告消息：

```
[26-Jul-2012 09:49:59] WARNING: [pool www] seems busy (you may need to increase pm.start_servers, or pm.min/max_spare_servers), spawning 32 children, there are 8 idle, and 58 total children

[26-Jul-2012 09:50:00] WARNING: [pool www] server reached pm.max_children setting (50), consider raising it

```

这意味着没有足够的PHP-FPM进程。
解：

我们需要根据系统内存量来计算和更改这些值：

```
pm.max_children = (total RAM - RAM used by other process) / (average amount of RAM used by a PHP process)

/etc/php-fpm.d/www.conf
```
```ini
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

```

* 以下命令将帮助我们确定每个（PHP-FPM）子进程使用的内存：`ps -ylC php-fpm --sort:rss`


`RSS`列显示PHP-FPM进程的未交换的物理内存使用量（千字节）。

平均每个PHP-FPM进程在我的机器上占用大约75MB的RAM。

pm.max_children的适当值可以计算为：

`pm.max_children =专用于Web服务器的总RAM /最大子进程大小` - 在我的情况下是85MB

服务器有8GB的RAM，所以：

`pm.max_children = 6144MB / 85MB = 72`

我留下了一些记忆，让系统呼吸。在计算内存使用情况时，您需要考虑在机器上运行的任何其他服务。

我已经改变了如下设置：

```ini
pm.max_children = 70
pm.start_servers = 20
pm.min_spare_servers = 20
pm.max_spare_servers = 35
pm.max_requests = 500

```

请注意，非常高的价值并不意味着任何好处。

您可以使用此方便的命令检查单个PHP-FPM进程的平均内存使用情况：

```
ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1 } END { printf ("%d%s\n", sum/NR/1024,"M") }'

```

您可以使用上述相同的步骤来计算Apche Web服务器的MaxClients的值- 只需用httpd替换php-fpm。

原文：[https://myshell.co.uk/blog/20...][0]

[0]: https://myshell.co.uk/blog/2012/07/adjusting-child-processes-for-php-fpm-nginx/
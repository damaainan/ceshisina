# Linux下php-fpm进程过多导致内存耗尽问题解决

[熊建刚][0]

1 天前

当个人博客数据库服务经常突然挂断，造成无法访问时我们能做什么？本篇主题就是记录博主针对这一现象时发现问题，分析问题，最后解决问题的过程。

[欢迎访问我的个人博客][1]

## 发现问题

最近，发现个人博客的Linux服务器，数据库服务经常挂掉，导致需要重启，才能正常访问，极其恶心，于是决心开始解决问题，解放我的时间和精力（我可不想经常出问题，然后人工重启，费力费时）。

## 分析问题

发现问题以后，首先使用`free -m`指令查看当前服务器执行状况：

![][2]

可以看到我的服务器内存是2G的，但是目前可用内存只剩下70M，内存使用率高达92%，很有可能是内存使用率过高导致数据库服务挂断。

继续看详细情况，使用`top`指令：

![][3]

然后再看指令输出结果中详细列出的进程情况，重点关注第10列内存使用占比：

![][4]

发现CPU使用率不算高，也排除了CPU的问题，另外可以看到数据库服务占用15.2%的内存，内存使用过高时将会挤掉数据库进程（占用内存最高的进程），导致服务挂断，所以我们需要查看详细内存使用情况，是哪些进程耗费了这么多的内存呢？

使用指令：

    ps auxw | head -1;ps auxw | sort -rn -k4 | head -40
    

查看消耗内存最多的前40个进程：

![][5]

查看第四列内存使用占比，发现除了mysql数据库服务之外，php-fpm服务池开启了太多子进程，占用超过大半内存，问题找到了，我们开始解决问题：设置控制php-fpm进程池进程数量。

## 解决问题

通过各种搜索手段，发现可以通过配置**`pm.max_children`**属性，控制php-fpm子进程数量，首先，打开`php-fpm`配置文件，执行指令：

    vi /etc/php-fpm.d/www.conf
    

找到`pm.max_children`字段，发现其值过大：

![][6]

如图，`pm.max_children`值为50，每一个进程占用1%-2.5%的内存，加起来就耗费大半内存了，所以我们需要将其值调小，博主这里将其设置为25，同时，检查以下两个属性：

1. `pm.max_spare_servers`: 该值表示保证空闲进程数最大值，如果空闲进程大于此值，此进行清理
1. `pm.min_spare_servers`: 保证空闲进程数最小值，如果空闲进程小于此值，则创建新的子进程;

这两个值均不能不能大于`pm.max_children`值，通常设置`pm.max_spare_servers`值为`pm.max_children`值的60%-80%。

最后，重启php-fpm

    systemctl restart php-fpm
    

再次查看内存使用情况， 使用内存降低很多：

![][7]

之后经过多次观察内存使用情况，发现此次改进后，服务器内存资源消耗得到很大缓解。

[0]: https://www.zhihu.com/people/codingplayboy
[1]: http://link.zhihu.com/?target=https%3A//link.juejin.im/%3Ftarget%3Dhttp%253A%252F%252Fblog.codingplayboy.com%252F2017%252F11%252F30%252Flinux_php-fpm_memory_problem%252F
[2]: ./img/v2-c4fb9fbc24a0c630062e7fc6f996cd29_hd.jpg
[3]: ./img/v2-ccbff3df803bd3a378c2987240fc4f6e_hd.jpg
[4]: ./img/v2-72ea9fc3fa27e76e09470aeee6def1d5_hd.jpg
[5]: ./img/v2-0a545842de748f2200ebebfa2b40edbc_hd.jpg
[6]: ./img/v2-7ec3accd3a837cabc4fcc71f25ae3d22_hd.jpg
[7]: ./img/v2-9f2ad98e0a2a39db928fbafa9e2847bc_hd.jpg
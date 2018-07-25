## php-fpm进程数管理

来源：[https://segmentfault.com/a/1190000015612563](https://segmentfault.com/a/1190000015612563)


##`PHP-FPM`先来了解一些名词概念：
`CGI`是`Common Gateway Interface（通用网管协议）`，用于让交互程序和Web服务器通信的协议。它负责处理URL的请求，启动一个进程，将客户端发送的数据作为输入，由Web服务器收集程序的输出并加上合适的头部，再发送回客户端。
`FastCGI`是基于`CGI`的增强版本的协议，不同于创建新的进程来服务请求，使用持续的进程和创建的子进程来处理一连串的进程，这些进程由FastCGI服务器管理，开销更小，效率更高。
`PHP-FPM`是`PHP`实现的`FastCGI Process Manager(FastCGI进程管理器)`, 用于替换`PHP FastCGI`的大部分附加功能，适用于高负载网站。支持的功能如：


* 平滑停止/启动的高级进程管理功能
* 慢日志记录脚本
* 动态/静态子进程产生
* 基于php.ini的配置文件

`PHP-FPM`在5.4之后已经整合进入PHP源代码中，提供更好的PHP进程管理方式，可以有效控制内存和进程，平滑重载PHP配置。如果需要使用，在`./configure`的时候带上`-enable-fpm`参数即可，使用`PHP-FPM`来控制`FastCGI`进程:

```
// 支持start/stop/quit/restart/reload/logrotate参数
// quit/reload是平滑终止和平滑重新加载，即等现有的服务完成
./php-fpm --start
```
##`PHP-FPM`配置
`PHP-FPM`配置文件为`php-fpm.conf`，在这个配置文件中我们需要了解一些参数。下面所有的子进程均指`php-fpm`进程，可以在终端通过`ps aux | grep php`查看到。


* 显示`php-fpm: pool www`的代表work子进程（实际处理请求）
* 显示`php-fpm: process master`的代表master主进程（负责管理work子进程）


### 全局配置

先看`PHP-FPM`最重要的全局配置部分：
####`emergency_restart_threshold`
如果在`emergency_restart_interval`设定的时间内收到该参数设定次数的`SIGSEGV`或`SIGBUS`退出的信号，则`FPM`会重新启动。默认值为0，表示关闭该功能。
####`emergency_restart_interval`

设定平滑重启的间隔时间，有助于解决加速器中共享内存的使用问题。可用单位`s(默认)/m/h/d`，默认值为0， 表示关闭。
####`process.max`

`FPM`能够创建的最大子进程数量，它在使用多个`pm = dynamic`配置的`php-fpm pool`进程池的时候，控制全局的子进程数量。默认值为0，代表着无限制。

### 进程池配置
`PHP-FPM`的配置其余部分是一个名为`Pool Definitions`的区域，这个区域的配置设置每个`PHP-FPM`进程池，进程池中是一系列相关的子进程。这部分开头都是`[进程池名称]`，如`[www]`。

此时可以解释看到`ps aux | grep php`中显示的是`php-fpm: pool www`。

####`pm`

`pm`指的是`process manager`，指定进程管理器如何控制子进程的数量，它为必填项，支持3个值：


* `static`: 使用固定的子进程数量，由`pm.max_children`指定
* `dynamic`：基于下面的参数动态的调整子进程的数量，至少有一个子进程


* `pm.max_chidren`: 可以同时存活的子进程的最大数量
* `pm.start_servers`: 启动时创建的子进程数量，默认值为`min_spare_servers + max_spare_servers - min_spare_servers) / 2`
* `pm.min_spare_servers`: 空闲状态的子进程的最小数量，如果不足，新的子进程会被自动创建
* `pm.max_spare_servers`: 空闲状态的子进程的最大数量，如果超过，一些子进程会被杀死



* `ondemand`: 启动时不会创建子进程，当新的请求到达时才创建。会使用下面两个参数：


* `pm.max_children`
* `pm.process_idle_timeou`t 子进程的空闲超时时间，如果超时时间到没有新的请求可以服务，则会被杀死





####`pm.max_requests`每一个子进程的最大请求服务数量，如果超过了这个值，该子进程会被自动重启。在解决第三方库的内存泄漏问题时，这个参数会很有用。默认值为0，指子进程可以持续不断的服务请求。
##`PHP-FPM`配置优化
`PHP-FPM`管理的方式是一个`master`主进程，多个`pool`进程池，多个`worker`子进程。其中每个进程池监听一个`socket`套接字。具体的图示：

![][0]

其中的`worker`子进程实际处理连接请求，`master`主进程负责管理子进程：

```
1. `master`进程，设置1s定时器，通过`socket`文件监听
2. 在`pm=dynamic`时，如果`idle worker`数量<`pm.min_spare_servers`，创建新的子进程
3. 在`pm=dynamic`时，如果`idle worker`数量>`pm.max_spare_servers`，杀死多余的空闲子进程
4. 在`pm=ondemand`时，如果`idle worker`空闲时间>`pm.process_idle_timeout`，杀死该空闲进程
5. 当连接到达时，检测如果`worker`数量>`pm.max_children`，打印`warning`日志，退出；如果无异常，使用`idle worker`服务，或者新建`worker`服务

```
### 保障基本安全

我们为了避免`PHP-FPM`主进程由于某些糟糕的PHP代码挂掉，需要设置重启的全局配置：

```
; 如果在1min内有10个子进程被中断失效，重启主进程
emergency_restart_threshold = 10
emergency_restart_interval = 1m
```
### 进程数调优

每一个子进程同时只能服务一次连接，所以控制同时存在多少个进程数就很重要，如果过少会导致很多不必要的重建和销毁的开销，如果过多又会占用过多的内存，影响其他服务使用。

我们应该测试自己的PHP进程使用多少内存，一般来说刚启动时是8M左右，运行一段时间由于内存泄漏和缓存会上涨到30M左右，所以你需要根据自己的预期内存大小设定进程的数量。同时根据进程池的数量来看一个进程管理器的子进程数量限制。
#### 测试平均PHP子进程占用的内存：

```
$ps auxf | grep php | grep -v grep
work     26829  0.0  0.0 715976  4712 ?        Ss   Jul11   0:00 php-fpm: master process (./etc/php-fpm.conf)
work     21889  0.0  0.0 729076 29668 ?        S    03:12   0:20  \_ php-fpm: pool www         
work     21273  0.0  0.0 728928 31380 ?        S    03:25   0:21  \_ php-fpm: pool www         
work     15114  0.0  0.0 728052 29084 ?        S    03:40   0:19  \_ php-fpm: pool www         
work     17072  0.0  0.0 728800 34240 ?        S    03:54   0:22  \_ php-fpm: pool www         
work     22763  0.0  0.0 727904 20352 ?        S    11:29   0:04  \_ php-fpm: pool www         
work     38545  0.0  0.0 727796 19484 ?        S    12:34   0:01  \_ php-fpm: pool www

// 共占用的内存数量
$ps auxf | grep php | grep -v grep | grep -v master | awk '{sum+=$6} END {print sum}'
162712

// 所有的子进程数量
$ ps auxf | grep php | grep -v grep | grep -v master | wc -l 
6
```

可以看到第6列，每一个子进程的内存占用大概在19-34M之间（单位为KB）。平均的内存占用为`162712KB/6 = 27.1M`。
#### 查看服务器总的内存大小

```
$ free -g
             total       used       free     shared    buffers     cached
Mem:           157        141         15          0          4        123
-/+ buffers/cache:         13        143
Swap:            0          0          0
```

可以看出我的服务器总得内存大小是157G（-g采用了G的单位）。
#### 进程数限制

此时如果我们分配全部的内存给`PHP-FPM`使用，那么进程数可以限制在`157000/27 = 5814`,但是由于我的服务器同时服务了很多内容，所以我们可以向下调整到512个进程数：

```
process.max = 512
pm = dynamic
pm.max_children = 512
pm.start_servers = 16
pm.min_spare_servers = 8
pm.max_spare_serveres = 30

```
### 防止内存泄漏

由于糟糕的插件和库，内存泄漏时有发生，所以我们需要对每一个子进程服务的请求数量做限制，防止无限制的内存泄漏：

```
pm.max_requests = 1000
```
### 重启

如果上面的配置都按照你的实际需求和环境配置好了，不要忘记重启`PHP-FPM`服务。
## 参考资料


* PHP手册-FastCGI: [http://php.net/manual/zh/inst...][1]  
* 维基百科-CGI：[https://zh.wikipedia.org/wiki...][2]  
* 维基百科-FastCGI：[https://zh.wikipedia.org/wiki...][3]  
* 博客园 php-fpm进程数优化：[https://www.cnblogs.com/52fhy...][4]  
* 简书 php-fpm进程管理：[https://www.jianshu.com/p/c9a...][5]  
* PHP手册 php-fpm.conf：[http://php.net/manual/zh/inst...][6]  
* 《Modern PHP》 第七章 PHP-FPM  


[1]: http://php.net/manual/zh/install.fpm.php
[2]: https://zh.wikipedia.org/wiki/%E9%80%9A%E7%94%A8%E7%BD%91%E5%85%B3%E6%8E%A5%E5%8F%A3
[3]: https://zh.wikipedia.org/wiki/FastCGI
[4]: https://www.cnblogs.com/52fhy/p/5051722.html
[5]: https://www.jianshu.com/p/c9a028c834ff
[6]: http://php.net/manual/zh/install.fpm.configuration.php
[0]: ./img/bVbdFHy.png
## 性能测试工具 wrk 安装与使用

来源：[https://www.cnblogs.com/savorboard/p/wrk.html](https://www.cnblogs.com/savorboard/p/wrk.html)

2016-10-13 17:12


### 介绍

今天给大家介绍一款开源的性能测试工具 wrk，简单易用，没有Load Runner那么复杂，他和 apache benchmark（ab）同属于性能测试工具，但是比 ab 功能更加强大，并且可以支持lua脚本来创建复杂的测试场景。

wrk 的一个很好的特性就是能用很少的线程压出很大的并发量， 原因是它使用了一些操作系统特定的高性能 I/O 机制, 比如 select, epoll, kqueue 等。 其实它是复用了 redis 的 ae 异步事件驱动框架. 确切的说 ae 事件驱动框架并不是 redis 发明的, 它来至于 Tcl的解释器 jim, 这个小巧高效的框架, 因为被 redis 采用而更多的被大家所熟知.

wrk GitHub 源码：[https://github.com/wg/wrk][100]
### 安装

wrk只能运行于 Unix 类的系统上，也只能在这些系统上便宜，所以我们需要一个Linux或者macOs。

不得不说，使用了 Win10之后方便很多。

必备条件：


* Win10 RS及以上版本
* 启用Ubuntu子系统


1、Win10 系统通过`bash`命令，切换到Ubuntu子系统。

然后需要安装一下编译工具，通过运行下面命令来安装工具：

```
# 安装 make 工具
sudo apt-get install make

# 安装 gcc编译环境
sudo apt-get install build-essential

```

安装 gcc 编译环境的时候最好挂一下VPN，速度会快些。

![][0]

2、安装完成之后使用 git 下载 wrk 的源码到本地：

```
https://github.com/wg/wrk.git
```

3、切换到git的wrk目录，然后使用`make`命令：

```
cd /mnt/盘符/wrk目录

make

```

![][1]

编译完成之后，目录下面会多一个 wrk 的文件。

![][2]
### 测试

使用以下命令来测试一下：

```
./wrk -c 1 -t 1 -d 1 http://www.baidu.com
```

![][3]

简单说一下wrk里面各个参数什么意思？


* -t 需要模拟的线程数
* -c 需要模拟的连接数
* --timeout 超时的时间
* -d 测试的持续时间


结果：


* Latency：响应时间
* Req/Sec：每个线程每秒钟的完成的请求数

* Avg：平均
* Max：最大
* Stdev：标准差
* +/- Stdev： 正负一个标准差占比


标准差如果太大说明样本本身离散程度比较高. 有可能系统性能波动很大.

如果想看响应时间的分布情况可以加上--latency参数

![][4]

我们的模拟测试的时候需要注意，一般线程数不宜过多，核数的2到4倍足够了。 多了反而因为线程切换过多造成效率降低， 因为 wrk 不是使用每个连接一个线程的模型， 而是通过异步网络 I/O 提升并发量。 所以网络通信不会阻塞线程执行，这也是 wrk 可以用很少的线程模拟大量网路连接的原因。

在 wrk 的测试结果中，有一项为`Requests/sec`，我们一般称之为QPS（每秒请求数），这是一项压力测试的性能指标，通过这个参数我们可以看出应用程序的吞吐量。
### 总结

关于 wrk 已经介绍完毕了，之所以写这篇文章的目的是为了接下来对 ASP.NET Core做一个性能对比测试（Java，NodeJS，Python等）时需要用到该工具，敬请大家期待。

-----

本文地址：[http://www.cnblogs.com/savorboard/p/wrk.html][101]

作者博客：[Savorboard][102]

欢迎转载，请在明显位置给出出处及链接

[0]: ../img/o_cmd_make.png
[1]: ../img/o_cmd_make_build.png
[2]: ../img/o_wrk-build-finish.png
[3]: ../img/o_cmd_test.png
[4]: ../img/o_wrk_baidu.png
[100]: https://github.com/wg/wrk
[101]: http://www.cnblogs.com/savorboard/p/wrk.html
[102]: http://www.cnblogs.com/savorboard
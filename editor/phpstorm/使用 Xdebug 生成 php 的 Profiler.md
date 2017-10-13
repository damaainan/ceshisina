## [使用 Xdebug 生成 php 的 Profiler](https://segmentfault.com/a/1190000011401007)

## 说明

以下内容摘抄自 [profiling PHP 脚本][0]

> xdebug 的 profiler 是一个强大的工具，它能分析 PHP 代码，探测瓶颈，或者通常意义上来说查看哪部分代码运行缓慢以及可以使用速度提升。Xdebug 2 分析器输出一种兼容 cachegrind 文件格式的分析信息。这允许你能使用出色的 [KCacheGrind][1] 工具（Linux，KDE）来分析你的 profiling 数据。在 Linux 可以使用你最喜欢的包管理器安装 KCacheGrind。

> 在 windows 系统上，有预编译的 [QCacheGrind][2] 二进制程序（QCacheGrind 是没有 KDE 绑定的 KCacheGrind）。

> 在 Mac OSX 系统上，这里也有怎样安装 QCacheGrind 的[说明][3]。

> Windows 用户可以选择性的使用 [WinCacheGrind][4]。它的功能不同于 KCacheGrind，所以 这个页面的 KCacheGrind 使用文档章节不适用于这个程序。WinCacheGrind 目前不支持 Xdebug 2.3 引入的 cachegrind 文件格式的的文件和函数压缩。

> 这也有一种可替代 profile 信息演示的工具叫做 [xdebugtoolkit][5]。一款基于 web 前端叫做 [Webgrind][6]，和一款基于 java 的工具叫做 [XCallGraph][7]。

> 如果你不能使用 KDE（或者不想使用 KDE）的 kcachegrind 包，可以用 perl 脚本 "ct_annotate"，它能从分析器跟踪文件生成 ASCII 输出。

## 配置

### 1) Xdebug 配置

这里依旧使用最小化配置

    ; profiler
    xdebug.profiler_enable = 0;            ; 关闭永久生成profiler
    xdebug.profiler_enable_trigger = 1;    ; 启用 session 触发 profiler
    xdebug.profiler_output_dir = "/data/profiler_dir"   ; 输出的目录
    zend_extension = "/usr/local/opt/php70-xdebug/xdebug.so"

配置完成之后重启 php-fpm 或者 apache### 2) 安装 xdebug 工具

安装 chrome 扩展 [Xdebug helper][8]

![][9]

### 3) 启用 Xdebug helper 的 profiler 工具

![][10]

### 4) 刷新页面, 查看设定的文件夹

在上边设定的文件夹中会生成 profiler 文件

![][11]

### 5) 使用工具来分析 profiler 文件

这里我使用 phpstorm 的分析工具来查看

Tools > Analyze Xdebug Profiler Snapshot选择生成的 输出文件, 可以看到文件的解析信息, 这个对于分析自己写的php代码会有很大益处

![][12]

## 参考文档

* [profiling PHP 脚本][0]
* [Enabling Profiling with Xdebug][13]

[0]: http://xdebug.org.cn/docs/profiler
[1]: https://kcachegrind.github.io/
[2]: http://sourceforge.net/projects/qcachegrindwin/
[3]: http://www.tekkie.ro/computer-setup/how-to-install-kcachegrind-qcachegrind-on-mac-osx/
[4]: http://ceefour.github.io/wincachegrind/
[5]: https://github.com/alexeykupershtokh/xdebugtoolkit
[6]: https://github.com/jokkedk/webgrind
[7]: http://sourceforge.net/projects/xcallgraph/
[8]: https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc
[9]: ./img/1460000011387674.png
[10]: ./img/1460000011401010.png
[11]: ./img/1460000011401011.png
[12]: ./img/1460000011401012.png
[13]: https://www.jetbrains.com/help/phpstorm/enabling-profiling-with-xdebug.html#d128831e22
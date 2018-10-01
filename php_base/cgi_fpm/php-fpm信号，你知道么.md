## php-fpm信号，你造么？

2017.03.07 19:38*

来源：[https://www.jianshu.com/p/2ea78b789263](https://www.jianshu.com/p/2ea78b789263)


## 故障回顾

故障时间：2017-02-25 周六 08：56-10：20左右

故障现象：app找房不可用，后台502

暴露问题：没有报警，没有健康检查，没有兜底，没有降级
## 故障恢复

运维同学说：“标准流程，重启！” 就 T_T了 (一会再说这的问题)
## 故障原因


* 周六之前在监控系统观察，发现每天凌晨都有502的问题；
* 所以查了下发现每天三点都有重启的动作（截图如下）；



![][0]


* 修改restart为reload；
* reload脚本有bug，信号发错，导致master进程被干掉，子进程2048个请求后，自动退出了（so，3点的动作8点多才出现问题，截图如下）；



![][1]

## 涨姿势


* php-fpm的进程相关，见上篇[《php-fpm解读-进程管理的三种模式》][2]

* php-fpm的信号

```
- SIGUSR1信号

```


功能：重新打开日志

作用：提供了一种手段，防止单个日志文件过长

执行步骤：

1、  master进程重新打开error_log，error_log是全局log，所有pool共享

2、  master进程重新打开每个pool的access_log，同一个pool中的子进程会写入同一个access_log

3、  master通过给子进程发送SIGQUIT信号的方式，平滑关闭所有的子进程

4、  每个worker退出后，master会重新fork子进程

为什么要关闭所有的子进程呢？因为master进程重新打开access_log日志文件，只是对master有效，只有重启子进程，子进程才能真正的感知到access_log的变化，才能写到新的日志里面

```
  - SIGUSR2信号

```

功能：重启fpm，包括master和worker

作用：就是为了重启

执行步骤：

1、  master通过给子进程发送SIGQUIT信号的方式，平滑关闭所有的子进程

2、  如果过一段时间，有些子进程还没退出，给子进程发送SIGTERM信号，强制关闭子进程

3、  如果还没关闭，给子进程发送SIGKILL信号，强制关闭

4、  等所有的子进程退出后，master重新启动

```
  - SIGQUIT信号

```

功能：平滑关闭fpm

作用：关闭fpm，不影响正在处理的请求，等处理完正在处理的请求后，子进程才退出

执行步骤：

1、  Master通过给子进程发送SIGQUIT信号的方式，关闭所有的子进程

2、  如果过一段时间，有些子进程还没退出，给子进程发送SIGTERM信号，强制关闭子进程

3、  如果还没关闭，给子进程发送SIGKILL信号，强制关闭

4、  等所有的子进程退出后，master退出

为什么叫平滑关闭？因为master和worker都对SIGQUIT进行了处理，master收到SIGQUIT信号时，它也会给worker发SIGQUIT信号，worker对SIGQUIT信号的处理方式就是，处理完手头的活儿才关闭，所以不影响当前请求

```
  - SIGTERM/SIGINT信号

```

功能：强制关闭fpm

作用：关闭fpm，方法暴力，会影响当前请求

执行步骤：

1、  master通过给子进程发送SIGTERM信号的方式关闭子进程

2、  如果过一段时间，有些子进程没关闭，master给子进程发送SIGKILL信号，强制关闭

3、  等所有子进程退出后，master退出

为什么叫暴力退出？因为worker进程没有处理SIGTERM和SIGKILL信号，当收到master发来的这些信号后，子进程会被os直接干掉。

注意：fpm对外只处理了上面5个信号，所有其他信号都采用默认行为，默认行为有可能会导致master直接退出。其他参见源码 [https://github.com/php/php-src/blob/e3feeba3aedd8e4347fefa315effd7d4f9fa0ca1/sapi/fpm/fpm/fpm_signals.c][3]
## 老司机经验


* 启动脚本一定要从官方下载，运维小哥别自己搞了；
* 发生故障，千万别重启，而是摘机器，保留现场；
* 核心业务一定要多机器，以免发生问题，只有两台，摘掉一台又怕扛不住；
* 所有研发一定要对自己的系统特别了解，跑了什么？怎么跑的？配置是什么?日志在哪里？
* 每次故障都要复盘清楚，不要不了了之（接下来：运维去测试下，日志切割不用动php看行不行；运维加对php-fpm的监控）；
* 报警（以后业务报警研发加，系统级报警运维加）；
* 健康检查（这次nginx的故障转移只加了500,503,404，所以502的出了问题）；



[2]: https://www.jianshu.com/p/c9a028c834ff
[3]: https://link.jianshu.com?t=https://github.com/php/php-src/blob/e3feeba3aedd8e4347fefa315effd7d4f9fa0ca1/sapi/fpm/fpm/fpm_signals.c
[0]: ./img/2735552-80d78eddd12e643c.png
[1]: ./img/2735552-af50dc04d2aae96a.png
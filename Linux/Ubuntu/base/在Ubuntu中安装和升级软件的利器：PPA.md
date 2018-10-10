## 在Ubuntu中安装和升级软件的利器：PPA

2018.09.06 12:39*

来源：[https://www.jianshu.com/p/5cdc52d7376a](https://www.jianshu.com/p/5cdc52d7376a)


在[《Ubuntu14能使用Ubuntu16的源升级软件吗？》][2]这篇文章中，使用 Ubuntu 官方的源没有成功升级 redis-server。在 Ubuntu 系统下，升级软件最好采用 PPA，不用像 CentOS 系统一样寻找、安装、配置源，简单的几个命令就能升级软件版本，非常方便，而且不会和系统源冲突，减少了很多麻烦。
### 什么是 PPA

说到 PPA，首先了解下 launchpad.net，它是一个综合性的软件平台，提供 Bug tracking、Code reviews、Ubuntu package building and hosting。

在该平台中，和软件安装、维护相关的就是 [Ubuntu package building and hosting][3]，也就是所有的 Ubuntu 软件可以通过三种方式获取，主要包含：


* CD mirrors
* Archive mirrors
* Personal Package Archives(PPA)


CD 等仓库源已经不流行，目前最流行的就是 PPA，任何人都可以构建一个 PPA，便利性的同时也带来安全性的问题。综合来说 PPA 是放在 Ubuntu 上的一个 Apt repository ，PPAs 允许第三方的开发者在非官方渠道分发软件包。
### 添加 PPA

那么如何找到特定软件的 PPA 呢？打开页面 [https://launchpad.net/ubuntu/+ppas][4]，然后搜索 redis-server，最终来到 [https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server][5] 页面。

为了添加这个源，可以执行下列的命令：

```
$ add-apt-repository ppa:chris-lea/redis-server
$ apt-get update

```

`add-apt-repository` 是非常重要的一个命令行工具，如果没有安装，运行下列命令：

```
$ apt-get install python-software-properties

```

`add-apt-repository` 介绍如下：

add-apt-repository - Adds a repository into the /etc/apt/sources.list or /etc/apt/sources.list.d or removes an existing one

安装源后，新增加的 PPA（chris-lea-redis-server-trusty.list）保存在 /etc/apt/sources.list.d 目录下。
### 升级 redis-server

配置 redis-server 源后，输入下列命令查看 redis 有多少个版本：

```
$ apt-cache madison redis-server

```

输出如下图：


![][0]

可以看出有三个版本可以安装。

输入下列命令，查看目前系统安装了哪些 redis-server 版本：

```
$ apt-cache policy redis-server 

```

输出如下图：

![][1]

可以看出目前还没有安装 redis-server，为了安装新版本 redis-server，运行如下命令：

```
$ apt-get install redis-server=5:4.0.11-1chl1~trusty1

```

最后成功升级完成，和《Ubuntu14能使用Ubuntu16的源升级软件吗？》中不一样的是，ppa:chris-lea/redis-server 并没有依赖高版本的 init-system-helpers 库。

也就是说 PPA 会根据不同的 Ubuntu 版本升级软件，安装者不用考虑版本冲突、兼容等问题，非常的方便。


[2]: https://mp.weixin.qq.com/s/X_n7n8WTbs_COC3OPJL3xw
[3]: https://launchpad.net/ubuntu
[4]: https://launchpad.net/ubuntu/+ppas
[5]: https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server
[0]: ../img/234392-d7cb80531def78da.png
[1]: ../img/234392-169b04172f06558d.png
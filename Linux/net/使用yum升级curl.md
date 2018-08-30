## 手把手教你使用yum升级curl

来源：[http://www.jianshu.com/p/a337acb40453](http://www.jianshu.com/p/a337acb40453)

时间 2018-08-28 21:43:13

 
前段时间我写了不少关于 curl 方面的文章，但重点主要描述 curl 和 openssl、nss 之间的关系。其实对于很多开发者来说，不太关心 curl 使用何种密码学库，这不，上周五刚好有个公众号留言，询问升级 curl 高版本的问题，这篇文章分享下我升级 curl 的一些经验。
 
留言内容：
 
在 RedHat Enterprise Linux Server release 6.5 上默认 curl 版本是 curl 7.19.7，想升级到 curl 7.61 版本。
 
在 RedHat/CentOS 系统中，curl 默认使用的密码学库是 NSS，升级 curl 有两种方法，分别是编译安装和包安装。
 
编译安装的方法参考 [《源代码编译curl，让其支持nss》][1] 这篇文章。在这篇文章中，NSS 也是编译安装的，实话实话编译过程还是很艰难的，如果不了解 NSS，很容易失败。
 
而使用包安装可能是最轻松的方法，这也是本篇文章讲解的重点，我使用 CentOS 6.5 进行讲解，基本上和 RedHat 6.5 版本无差异。
 
首先思考一个问题，为什么要升级高版本 curl，查看 releases：

 
* [https://curl.haxx.se/changes.html#7_61_0][2]  
* [https://curl.haxx.se/docs/releases.html][3]  
 
 
根本原因估计是为了支持 TLS 1.3 协议。
 
然后查看系统目前安装的 curl 版本：

```sh
$ curl -V

curl 7.19.7 (x86_64-redhat-linux-gnu) libcurl/7.19.7 NSS/3.21 Basic ECC zlib/1.2.3 libidn/1.18 libssh2/1.4.2
Protocols: tftp ftp telnet dict ldap ldaps http file https ftps scp sftp
```
 
7.19 版本虽然2008年就发布了，目前 CentOS 6 还使用该版本，可以看出该版本比较稳定。
 
其次思考如何升级 curl 包，参考 [《推荐RHEL&CentOS系统下的几个包仓库》][4] 这篇文章，也就是寻找合适的 repo，找了几个 repo，支持的版本都是 7.19。
 
查看 curl 官方页面 [http://curl.haxx.se/download.html#LinuxRedhat][5] ，找到对应页面 [https://mirror.city-fan.org/ftp/contrib/sysutils/Mirroring][6] ，这个页面的介绍非常详细。
 
成功寻找到一个 [repo][7] ，接下去介绍安装步骤。
 
（1）安装 repo

```sh
$ rpm -Uvh  http://www.city-fan.org/ftp/contrib/yum-repo/rhel6/x86_64/city-fan.org-release-2-1.rhel6.noarch.rpm
```
 
（2）查看该 repo 包含的 curl 版本

```sh
$ yum --showduplicates list curl --disablerepo="*"  --enablerepo="fan*"
```
 
输出如下：

```sh
Installed Packages
curl.x86_64        7.61.0-6.0.cf.rhel6      @city-fan.org
```
 
可见该 repo 包含 7.61 的 curl 安装包。
 
（3）安装

```sh
$ yum install  "curl-7.61.0-6.0.cf.rhel6.x86_64" --disablerepo="*"  --enablerepo="city*"
```
 
安装会包 libnghttp2 版本的报错，仔细查看 [https://mirror.city-fan.org/ftp/contrib/sysutils/Mirroring][6] 说明，其中说的很清楚：

```sh
Additionally, builds for recent Fedora releases and RHEL are linked against libnghttp2. This library is included in Fedora and can be obtained from the EPEL Repository for RHEL.


```
 
那么升级 libnghttp2 :

```sh
$ yum list libnghttp2  --disablerepo="*"  --enablerepo="epel"  
$ yum install libnghttp2  --disablerepo="*"  --enablerepo="epel"
```
 
最后再安装 curl 成功。
 
（4）查看版本：

```sh
$ curl -V
```
 
输出如下图：

![][0]

 
图1

 
特别说明的是：
 
在 curl 7.61 版本中，使用的密码学库从 NSS 替换为 OpenSSL 了，输入以下命令可以看出：

```sh
$ curl-config --ssl-backends
    OpenSSL
```


[1]: https://mp.weixin.qq.com/s/c-wVO6dwPpAAWRQhuCdDyA
[2]: https://curl.haxx.se/changes.html#7_61_0
[3]: https://curl.haxx.se/docs/releases.html
[4]: https://mp.weixin.qq.com/s/CJsA1LqSqYZBOlZHd5QEbw
[5]: http://curl.haxx.se/download.html#LinuxRedhat
[6]: https://mirror.city-fan.org/ftp/contrib/sysutils/Mirroring
[7]: https://mirror.city-fan.org/ftp/contrib/
[8]: https://mirror.city-fan.org/ftp/contrib/sysutils/Mirroring
[0]: https://img2.tuicool.com/jMNr2i3.png
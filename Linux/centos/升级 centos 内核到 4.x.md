## 升级 centos 内核到 4.x

来源：[https://xiezhenye.com/2018/03/升级-centos-内核到-4-x.html](https://xiezhenye.com/2018/03/升级-centos-内核到-4-x.html)

时间 2018-03-08 12:30:24



centos 发行版是跟随 redhat 发行版的。在内核版本上比较保守。比如 centos 6.x 分支最高是 2.6.32 内核。虽然也会 backport 一些高版本的功能，但是总会需要的功能没有的情况。


不过 centos 的仓库里其实藏着一个 4.x 的内核。


在 centos 仓库里，有一个`centos-release-xen`的包，是 由 Centos、CitrixXen、Godaddy、Rackspace 共同维护的 Xen4CentOS 项目的一部分。

```
Xen4CentOS
The project, while hosted at centos.org, is a collaboration between the Xen Project, the Citrix Xen open source teams, the CentOS developers, GoDaddy Cloud Operations team, Rackspace Hosting and members of the CentOS QA Team.
```


这个包中其实并没有 xen 的工具，只是一个内核而已。安装这个包就能将 linux 内核升级到高版本。

```
yum install centos-release-xen
yum update
```


重启，然后`uname -r`检查一下就会发现内核版本已经到了比如`4.9.58-29.el6.x86_64`
## yum 安装 2.x 版本的git

来源：[https://segmentfault.com/a/1190000016838149](https://segmentfault.com/a/1190000016838149)

[官方教程][0]，在 Linux/Unix 系统中，通过工具在中安装`git`，这种方式比较简单，便于升级卸载工具，网上搜到的全是源码编译安装。

下面介绍在 CentOS 系统中，通过 yum 来安装 git

 **`Red Hat Enterprise Linux, Oracle Linux, CentOS, Scientific Linux, et al.`** 
RHEL and derivatives typically ship older versions of git. You can [download a tarball][1] and build from source, or use a 3rd-party repository such as [the IUS Community Project][2] to obtain a more recent version of git.官方文档说 git 在`RHEL`和衍生产品通常都会发布旧版本的`git`，我们需要源码编译安装，或者使用第三方存储库（如[IUS社区项目][3]）。

现在我们通过，[IUS社区][4]下载 [ius-release.rpm][5] 文件进行安装

```
# 注意下载不同的版本，本机 CentOS 7
wget https://centos7.iuscommunity.org/ius-release.rpm
# 安装rpm文件
rpm -ivh ius-release.rpm
```

查看可安装的git安装包

```
repoquery --whatprovides git
# git-0:1.8.3.1-13.el7.x86_64
# git2u-0:2.16.5-1.ius.centos7.x86_64
# git2u-0:2.16.2-1.ius.centos7.x86_64
# git2u-0:2.16.4-1.ius.centos7.x86_64
# git-0:1.8.3.1-14.el7_5.x86_64
```

卸载我本机的`1.8.3`的`git`，安装`2.16.5`的`git`

```
# 卸载老的版本
yum remove git
# 安装新的版本
yum install git2u
```

[原文收录在这里][6]

[0]: https://git-scm.com/download/linux
[1]: https://www.kernel.org/pub/software/scm/git/
[2]: https://ius.io/
[3]: https://ius.io/
[4]: https://ius.io/GettingStarted/
[5]: https://centos7.iuscommunity.org/ius-release.rpm
[6]: https://github.com/jaywcjlove/handbook
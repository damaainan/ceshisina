## linux基础命令介绍十一：软件包管理

来源：[https://segmentfault.com/a/1190000007813224](https://segmentfault.com/a/1190000007813224)

linux中软件包的管理随着系统发行版本的不同而不同，RPM和DPKG为最常见的两类软件包管理工具，分别应用于基于rpm软件包的linux发行版和基于deb软件包的linux发行版。
本文只描述RPM的使用方法，另一种命令不同，但用法类似，就不做介绍了。
### 1、`rpm`RPM包管理器

选项`-q`表示查询系统安装的软件包

```sh
[root@centos7 ~]# rpm -q sudo
sudo-1.8.6p7-16.el7.x86_64
[root@centos7 ~]# rpm -q nginx
未安装软件包 nginx 
[root@centos7 ~]#
```

选项`-a`表示查询所有安装的rpm包

```sh
[root@centos7 ~]# rpm -qa|grep vim
vim-filesystem-7.4.160-1.el7.x86_64
vim-common-7.4.160-1.el7.x86_64
vim-enhanced-7.4.160-1.el7.x86_64
vim-minimal-7.4.160-1.el7.x86_64
[root@centos7 ~]#
```

选项`-f file`表示查询文件所属软件包

```sh
[root@centos7 ~]# rpm -qf /usr/bin/ls
coreutils-8.22-15.el7.x86_64
[root@centos7 ~]#
```

选项`-c`表示查询软件包的配置文件

```sh
[root@centos7 ~]# rpm -qc sudo
/etc/pam.d/sudo
/etc/pam.d/sudo-i
/etc/sudo-ldap.conf
/etc/sudo.conf
/etc/sudoers
[root@centos7 ~]#
```

选项`-e`表示卸载软件包

```sh
[root@centos7 ~]# rpm -e sudo
警告：/etc/sudoers 已另存为 /etc/sudoers.rpmsave
[root@centos7 ~]# rpm -q sudo
未安装软件包 sudo 
[root@centos7 ~]# 
```

选项`-i`表示安装`-v`表示显示详细信息`-h`表示显示安装进度

```sh
#下载rpm包
[root@centos7 tmp]# wget ftp.scientificlinux.org/linux/scientific/7rolling/x86_64/os/Packages/sudo-1.8.6p7-16.el7.x86_64.rpm
#安装
[root@centos7 tmp]# rpm -ivh sudo-1.8.6p7-16.el7.x86_64.rpm 
警告：sudo-1.8.6p7-16.el7.x86_64.rpm: 头V4 DSA/SHA1 Signature, 密钥 ID 192a7d7d: NOKEY
准备中...                          ################################# [100%]
正在升级/安装...
   1:sudo-1.8.6p7-16.el7              ################################# [100%]
```

有很多软件并不是只有一个rpm包，它们之间有各种各样的依赖关系，当安装(或卸载)时，需要将所有依赖的包都安装(或卸载)之后才能安装(或卸载)成功

```sh
[root@centos7 tmp]# rpm -e vim-common
错误：依赖检测失败：
    vim-common = 2:7.4.160-1.el7 被 (已安裝) vim-enhanced-2:7.4.160-1.el7.x86_64 需要
```

选项`--nodeps`表示忽略依赖关系

```sh
[root@centos7 tmp]# rpm -q vim-common
vim-common-7.4.160-1.el7.x86_64
[root@centos7 tmp]# rpm -e --nodeps vim-common
警告：/etc/vimrc 已另存为 /etc/vimrc.rpmsave
[root@centos7 tmp]# rpm -q vim-common
未安装软件包 vim-common
```

选项`-U`表示对软件包升级

```sh
[root@centos7 tmp]# rpm -q wget
wget-1.14-10.el7_0.1.x86_64
[root@centos7 tmp]# rpm -Uvh wget-1.14-13.el7.x86_64.rpm 
准备中...                          ################################# [100%]
正在升级/安装...
   1:wget-1.14-13.el7                 ################################# [ 50%]
正在清理/删除...
   2:wget-1.14-10.el7_0.1             ################################# [100%]
[root@centos7 tmp]# rpm -q wget
wget-1.14-13.el7.x86_64
```
### 2、`yum`下载更新器

```sh
yum [options] [command] [package ...]
```
`yum`是一个基于rpm的交互式软件包管理器。yum在安装软件时并不需要像rpm那样手动查找安装，它在工作时会搜索源中的rpm包，并自动解决依赖关系，自动下载并安装。yum默认源配置文件位于目录`/etc/yum.repos.d`内。
命令`install`表示安装

```sh
[root@centos7 ~]# yum install vim-common
已加载插件：fastestmirror
Loading mirror speeds from cached hostfile
 * base: mirrors.yun-idc.com
 * extras: mirrors.yun-idc.com
 * updates: mirrors.yun-idc.com
正在解决依赖关系
--> 正在检查事务
---> 软件包 vim-common.x86_64.2.7.4.160-1.el7 将被 安装
--> 解决依赖关系完成

依赖关系解决
.... #省略部分输出
安装  1 软件包

总下载量：5.9 M
安装大小：21 M
Is this ok [y/d/N]:y  #需要在这里输入确认是否安装
Downloading packages:
vim-common-7.4.160-1.el7.x86_64.rpm                         | 5.9 MB  00:00:00     
Running transaction check
Running transaction test
Transaction test succeeded
Running transaction
警告：RPM 数据库已被非 yum 程序修改。
** 发现 1 个已存在的 RPM 数据库问题， 'yum check' 输出如下：
2:vim-enhanced-7.4.160-1.el7.x86_64 有缺少的需求 vim-common = ('2', '7.4.160', '1.el7')
  正在安装    : 2:vim-common-7.4.160-1.el7.x86_64                         1/1 
  验证中      : 2:vim-common-7.4.160-1.el7.x86_64                         1/1 

已安装:
  vim-common.x86_64 2:7.4.160-1.el7                                                                                                   

完毕！
```

命令`check-update`表示检查更新
命令`update`表示升级
命令`search`表示搜索软件包
命令`list`表示列出可用软件包
命令`remove`表示卸载
命令`clean`表示清除yum缓存目录内容
选项`-y`表示在所有需要交互式确认的地方默认输入yes
当`yum`源中没有所需要安装的包时，会报`没有可用软件包`的错误。此时可以通过添加新的yum源来解决
如centos7中安装nginx：

```sh
#安装repo
[root@centos7 tmp]# rpm -ivh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
获取http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
警告：/var/tmp/rpm-tmp.cUANoe: 头V4 RSA/SHA1 Signature, 密钥 ID 7bd9bf62: NOKEY
准备中...                          ################################# [100%]
正在升级/安装...
   1:nginx-release-centos-7-0.el7.ngx ################################# [100%]
```

此时/etc/yum.repos.d中增加了一个新文件nginx.repo

```sh
[root@centos7 tmp]# cat /etc/yum.repos.d/nginx.repo 
# nginx.repo

[nginx]
name=nginx repos                                      #名称
baseurl=http://nginx.org/packages/centos/7/$basearch/ #源地址
gpgcheck=0  #是否检查key，0表示不检查
enabled=1   #这里等于0表示不启用baseurl，为1表示启用baseurl
```

现在就可以通过命令`yum install -y nginx`安装nginx了
当服务器没有网络可用时，还能够设置本地yum源。此时需要手动配置repo文件
首先将安装光盘或ios文件挂载至系统(关于挂载的更多内容请看[这一篇][0])

```sh
[root@centos7 tmp]# mount CentOS-7-x86_64-DVD-1611.iso /media
mount: /dev/loop2 写保护，将以只读方式挂载
[root@centos7 tmp]# 
#如果是光盘则如此挂载：
[root@centos7 tmp]# mount /dev/cdrom /media
mount: /dev/sr0 写保护，将以只读方式挂载
[root@centos7 tmp]#
#卸载用umount或eject
[root@centos7 tmp]# umount /media
[root@centos7 tmp]# eject
```

编辑yum源配置文件

```sh
vim /etc/yum.repos.d/local.repo
    [local]
    name=test
    baseurl=file:///media #这里baseurl写 前缀(file://)+挂载点
    enabled=1
    gpgcheck=0
```

然后将原有网络源配置文件备份到另一个目录，/etc/yum.repo.d中只保留local.repo文件。安装软件：

```sh
yum install bc -y

```
### 3、源码包

前面所说的rpm和deb都是二进制软件包，由于这些软件包都是已经经过编译的，用户不能设置编译选项，也不能对软件做任何更改。相对来说，使用源码包编译安装软件提供了更多的灵活性，在编译时可指定各种选项，对于有能力的用户，还可以修改源代码。下面介绍一下linux中是如何安装源码包的

**`1、获取源码包`** 

```sh
wget http://mirrors.sohu.com/nginx/nginx-1.9.6.tar.gz
```

**`2、解压`** 

```sh
tar zxf nginx-1.9.6.tar.gz
```

**`3、配置`** 

```sh
[root@idc-v-71252 src]# cd nginx-1.9.6
[root@idc-v-71252 nginx-1.9.6]# ./configure --prefix=/usr/local/nginx
```

这里配置选项`--prefix=/usr/local/nginx`表示指定nginx的安装路径为/usr/local/nginx。
可以执行`./configure --help`查看有哪些配置参数，此步骤的执行会检查系统是否符合编译要求。如果报错，很多情况下是因为少了一些编译工具，可以使用yum安装这些工具(当然也可以装源码)。
在本例中报错：`./configure: error: the HTTP rewrite module requires the PCRE library.`。
说明少了pcre库，查看一下系统：

```sh
[root@idc-v-71252 nginx-1.9.6]# rpm -qa pcre
pcre-8.32-15.el7.x86_64
[root@idc-v-71252 nginx-1.9.6]#
```

系统有pcre安装，但没有devel包，使用yum安装

```sh
[root@idc-v-71252 nginx-1.9.6]# yum install pcre-devel -y
```

再次执行configure发现报错变了：`./configure: error: the HTTP gzip module requires the zlib library.`重复上述操作直到所需软件都安装完毕，之后再次执行`./configure --prefix=/usr/local/nginx`**`4、编译`** 

```sh
[root@idc-v-71252 nginx-1.9.6]# make -j8
```

使用`make`进行编译，选项`-j`表示指定并发执行的数量，这里指定了和系统逻辑CPU数(可以使用命令`grep -c "^processor" /proc/cpuinfo`查看逻辑CPU数)相同的并发数。
此步骤也可能会出现报错，一般也是因为缺少包，仔细阅读报错信息，一般都不难解决。

**`5、安装`** 

```sh
[root@idc-v-71252 nginx-1.9.6]# make install
```

如果没有错误，这个软件包就安装完毕了，可以在/usr/local/nginx中找到安装后的文件。

这里说了源码包的一般安装过程，有些源码包的安装可能会有所不同，一般源码包中都有相应的安装说明文件(`README`或`INSTALL`)，仔细阅读这些文件或者通过查询软件官网，就能找到它们的安装方法。

[0]: https://segmentfault.com/a/1190000007813965#articleHeader3
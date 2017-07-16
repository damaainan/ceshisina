# CentOS 7. × 系统及内核升级指南

 时间 2017-07-09 20:26:55  简书

_原文_[http://www.jianshu.com/p/fdf6bb6c5b9c][1]

![][3]

centos

最近在学习 Docker,想在服务器上实践一下.Docker 需要安装在 CentOS 7 64 位的平台，并且内核版本不低于 3.10。 CentOS 7 满足最低内核的要求，但由于 CentOS 7 内核版本比较低，部分功能（如 overlay2 存储层驱动）无法使用，并且部分功能可能不太稳定.需要升级到最新的 CentOS 版本,并且内核也更新到最新的稳定版本.这是我对升级 CentOS 系统版本及内核的记录,方便以后查阅,也分享给大家. 

注意:这篇文章只在 CentOS 7.× 系列版本上验证过,其他 CentOS 版本请谨慎使用.

我的服务器是腾讯云云主机,系统为 CentOS 7.1,系统内核版本为 3.10,我在写这篇博客的时候最新的 CentOS 版本为 CentOS 7.3,而 [The Linux Kernel Archives][4] 上提供的最新稳定的系统内核为 4.12 版本.所以就把我的服务器系统升级为最新的 CentOS 7.3 版本,系统内核升级为 4.12 版本. 

## 升级 CentOS 7.× 到最新的版本

### 备份重要数据

备份重要数据(例如:/etc ,/var ,/opt文件夹)如果 CentOS 是安装在虚拟机上,那么可以使用快照进行备份.像 VMware 虚拟机可以快照备份.也可以针对重要程序数据进行备份，例如 MySQL、Appache、Nginx、DNS 等等.云主机的话,阿里云和腾讯云都可以创建快照备份数据.

### 检查当前 CentOS 系统及内核版本

#### 检查当前 CentOS 系统版本

    # cat /etc/redhat-release
    
    > CentOS Linux release 7.1.1503 (Core)

#### 检查当前 CentOS 系统内核版本

    # uname -sr
    
    > Linux 3.10.0-327.10.1.e17.x86_64

可以看出当前系统为 CentOS 7.1,系统内核版本为 3.10.

#### 运行 yum 命令升级 

CentOS 中 update 命令可以一次性更新所有软件到最新版本。 

注意：不推荐使用 update 的 -y 选项， -y 选项会让你在安装每项更新前都进行确认,这样会非常费时间.对于 CentOS 5.× 和 6.× 的系统我们在更新后需要重新安装应用程序恢复数据，庆幸的是 CentOS 7.× 不需要这么麻烦，可以直接升级. 

    # yum clean all
    
    # yum update

期间会有确认提示,直接回车确认即可.

### 重启系统

    # reboot

### 查看当前 CentOS 系统及内核版本

#### 检查当前 CentOS 系统版本

    # cat /etc/redhat-release
    
    > CentOS Linux release 7.3.1611 (Core)

#### 检查当前 CentOS 系统内核版本

    # uname -sr
    
    > Linux 3.10.0-327.10.1.e17.x86_64

可以看到当前系统为 CentOS 7.3 已经升级成功了,但系统内核版本依旧为 3.10 并没有升级,这是因为 CentOS 为了安全和稳定并不会把系统内核升级到最新的版本,所以 yum update 命令只会升级系统版本和更新软件.接下来我们就来升级系统内核. 

## 升级 CentOS 7.× 内核

### 在 CentOS 7.× 中启用 ELRepo

大多数现代发行版提供了一种使用 yum 等包管理系统和官方支持的仓库升级内核的方法。 

但是，这只会升级内核到仓库中可用的最新版本,而不是在 [The Linux Kernel Archives][4] 中可用的最新主线稳定内核.不幸的是, Red Hat 只允许使用 yum 升级内核.与 Red Hat 不同，CentOS 允许使用 ELRepo,这是一个第三方仓库,可以将内核升级到最新主线稳定内核. 

要在 CentOS 7.× 上启用 ELRepo 仓库,请运行:

    # rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
    
    # rpm -Uvh http://www.elrepo.org/elrepo-release-7.0-2.el7.elrepo.noarch.rpm

仓库启用后，你可以使用下面的命令列出可用的系统内核相关包:

    # yum --disablerepo="*" --enablerepo="elrepo-kernel" list available

接下来，安装最新的主线稳定内核:

    # yum --enablerepo=elrepo-kernel install kernel-ml

由于网络原因,以上操作可能需要不少时间.

#### 重启机器，检查当前 CentOS 系统内核版本

    # uname -sr
    
    > Linux 4.12.0-1.el7.elrepo.x86_64

可以看到系统内核已经升级到最新的主线稳定内核.

### 设置 GRUB 默认的内核版本

为了让新安装的内核成为默认启动选项，你需要如下修改 GRUB 配置,打开并编辑 /etc/default/grub 并设置 GRUB_DEFAULT=0 .意思是 GRUB 初始化页面的第一个内核将作为默认内核. 

    # vi /etc/default/grub
    
    > GRUB_TIMEOUT=5
    > GRUB_DISTRIBUTOR="$(sed 's, release .*$,,g' /etc/system-release)"
    > GRUB_DEFAULT=0
    > GRUB_DISABLE_SUBMENU=true
    > GRUB_TERMINAL_OUTPUT="console"
    > GRUB_CMDLINE_LINUX="crashkernel=auto console=ttyS0 console=tty0 panic=5"
    > GRUB_DISABLE_RECOVERY="true"
    > GRUB_TERMINAL="serial console"
    > GRUB_TERMINAL_OUTPUT="serial console"
    > GRUB_SERIAL_COMMAND="serial --speed=9600 --unit=0 --word=8 --parity=no --stop=1"

接下来运行下面的命令来重新创建内核配置.

    # grub2-mkconfig -o /boot/grub2/grub.cfg

#### 重启机器，查看系统当前内核版本,验证最新的内核已作为默认内核

    # uname -a
    
    > Linux VM_112_0_centos 4.12.0-1.el7.elrepo.x86_64 #1 SMP Sun Jul 2 20:38:48 EDT 2017 x86_64 x86_64 x86_64 GNU/Linux

### 删除 CentOS 更新后的旧内核

#### 查看系统中全部的内核 RPM 包:

    # rpm -qa | grep kernel
    
    > kernel-tools-3.10.0-514.26.2.el7.x86_64
    > kernel-devel-3.10.0-514.10.2.el7.x86_64
    > kernel-3.10.0-514.26.2.el7.x86_64
    > kernel-3.10.0-327.el7.x86_64
    > kernel-ml-4.12.0-1.el7.elrepo.x86_64
    > kernel-headers-3.10.0-514.26.2.el7.x86_64
    > kernel-devel-3.10.0-514.26.2.el7.x86_64
    > kernel-tools-libs-3.10.0-514.26.2.el7.x86_64

#### 删除旧内核的 RPM 包

    yum remove kernel-tools-3.10.0-514.26.2.el7.x86_64 kernel-devel-3.10.0-514.10.2.el7.x86_64 kernel-3.10.0-514.26.2.el7.x86_64 kernel-3.10.0-327.el7.x86_64 kernel-headers-3.10.0-514.26.2.el7.x86_64 kernel-devel-3.10.0-514.26.2.el7.x86_64 kernel-tools-libs-3.10.0-514.26.2.el7.x86_64

#### 重启系统

    # reboot

这样就可以升级完成了.Ubuntu 系统的话可以看看这篇博文 [<<如何在 Ubuntu 中升级内核>>][5]

[1]: http://www.jianshu.com/p/fdf6bb6c5b9c
[3]: http://img2.tuicool.com/ammEjqi.jpg!web
[4]: https://www.kernel.org/
[5]: https://linux.cn/article-8284-1.html
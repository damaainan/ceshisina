# CentOS 7开启BBR

 时间 2017-07-11 15:05:27  tlanyan

_原文_[https://tlanyan.me/use-bbr-in-centos-7/][1]



BBR是谷歌开发的TCP拥堵控制技术，目的是尽量跑满带宽，尽少出现排队的现象。响马老师今天发博文说其境外的某个站点已经支持BBR，于是顺道也在自己的服务器上折腾一下，使其也支持BBR。以下是配置过程：

### 升级内核

BBR算法已经集成在4.9+的Linux内核中（4.9内核发布于2016-12-13），目前最新版的内核是4.12。为了使用BBR，第一件事是升级系统内核：

    rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
    rpm -Uvh http://www.elrepo.org/elrepo-release-7.0-2.el7.elrepo.noarch.rpm
    yum --enablerepo=elrepo-kernel install kernel-ml -y
    

### 更新grub系统引导文件并重启

为了设置新内核为默认运行内核，首先查看系统中存在的内核：

    egrep ^menuentry /etc/grub2.cfg | cut -f 2 -d \'
    

找到4.12的内核编号（从0开始），设置为默认内核并重启：

    grub2-set-default 0 // 0 为新版内核的编号
    reboot
    

### 查看BBR模块是否已经加载

运行 lsmod | grep bbr ，如果结果为空，则需先加载BBR，并: 

    modprobe tcp_bbr
    echo "tcp_bbr" >> /etc/modules-load.d/modules.conf
    

### 在sysctl.conf中配置BBR

在 /etc/sysctl.conf 文件末尾添加两行： 

    net.core.default_qdisc=fq
    net.ipv4.tcp_congestion_control=bbr
    

然后执行 sysctl -p 让配置生效。 

经过以上步骤，可让服务器支持BBR控制算法，理论上可以有效缓解拥塞，充分利用带宽。

### 参考

1. https://github.com/iMeiji/shadowsocks_install/wiki/%E5%BC%80%E5%90%AFTCP-BBR%E6%8B%A5%E5%A1%9E%E6%8E%A7%E5%88%B6%E7%AE%97%E6%B3%95


[1]: https://tlanyan.me/use-bbr-in-centos-7/
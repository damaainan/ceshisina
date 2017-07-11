## centos 安装完成之后需要进行的初始工作

### 1 安装EPEL源

EPEL即Extra Packages for Enterprise Linux，是基于Fedora的一个项目，为红帽系的操作系统提供额外的软件包，适用于RHEL、CentOS和Scientific Linux。EPEL为CentOS提供了额外的10000多个软件包，而且都不会更新或者替换系统本身组件。执行下面这条安装命令后，会在/etc/yum.repos.d目录下生成一个epel.repo文件。

    [root@typecodes ~]# yum -y install epel-release
    

### 2 安装yum-axelget插件

yum-axelget是EPEL提供的一个yum插件。默认的yum是单线程下载的，使用该插件后用yum安装软件时可以并行下载。yum-axelget插件原理是调用系统中的axel下载软件，然后根据软件包的大小自动设定线程数。在多线程操作时，还能避免因为线程数过多而导致服务器拒绝下载的问题，大大提高了软件的下载速度，减少了下载的等待时间。注意：通过下面这条安装命令，会同时安装axel下载软件。

    [root@typecodes ~]# yum -y install yum-axelget
    

### 3 更新CentOS源

在安装完EPEL源和yum-axelget插件后，我们就可以利用它们升级当前的CentOS7到CentOS7.1了（耗时大概10分钟）。

    [root@typecodes ~]# yum clean all && yum makecache && yum -y update
    

然后可以使用下面两条命令查看当前CentOS的内核版本和发行版本信息。

    ##########内核版本
    root@typecodes ~]# cat /proc/version
    Linux version 3.10.0-123.9.3.el7.x86_64 (builder@kbuilder.dev.centos.org) (gcc version 4.8.2 20140120 (Red Hat 4.8.2-16) (GCC) ) #1 SMP Thu Nov 6 15:06:03 UTC 2014
    ##########发行版本
    [root@typecodes ~]# lsb_release -a
    LSB Version:    :core-4.1-amd64:core-4.1-noarch
    Distributor ID: CentOS
    Description:    CentOS Linux release 7.1.1503 (Core) 
    Release:        7.1.1503
    Codename:       Core

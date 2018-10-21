## Ubuntu 设置静态IP

查看主机当前网卡名称 

网卡名称为ens33

修改/etc/network/interfaces文件 
执行命令：vim /etc/network/interfaces命令,添加以下脚本

    auto ens33
    iface ens33 inet static
    address 192.168.1.102
    netmask 255.255.255.0
    gateway 192.168.1.1
    dns-nameserver 114.114.114.114


执行命令/etc/init.d/networking restart,执行成功以后**`重启`**就ok了。



设置为静态IP后缺少DNS服务器，因此接下来我们要设置一个永久的dns服务器。网上有最多的使用 `vim /etc/resolvconf/resolv.conf.d/base` 来配置dns的方法在Ubuntu18.04中已经行不通了，另外使用netplan的那个是针对Ubuntu Server18.04的。在Desktop上，我们要按如下步骤配置：

在命令行输入`sudo vi /etc/systemd/resolved.conf` 修改改文件，

可以清楚地看到就是将DNS前的#号去掉，然后加上通用的DNS服务器地址即可。大家可以自行上网找，也可以就和我一样配置。


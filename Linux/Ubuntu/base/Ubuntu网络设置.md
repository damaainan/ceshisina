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
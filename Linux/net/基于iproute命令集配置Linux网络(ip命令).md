# 基于iproute命令集配置Linux网络(ip命令)

 时间 2017-09-18 17:47:58  

原文[http://blog.csdn.net/leshami/article/details/78021859][1]


iproute是Linux下一个网络管理工具包合集，用于取代先前的如ifconfig，route，ifup，ifdown，netstat等历史网络管理工具。该工具包功能强大，它通过网络链路套接字接口与内核进行联系。iproute的用户界面比net-tools的用户界面要更直观。对网络资源比如链路、IP地址、路由和隧道等用“对象”抽象进行了恰当的定义，因此可以使用一致的语法来管理不同的对象。本文主要描述使用该工具包的ip命令来配置Linux网络。

## 一、iproute工具包集

查看iproute工具包集

    # more /etc/redhat-release 
    CentOS Linux release 7.2.1511 (Core) 
    

#### 查看当前环境下已经安装的iproute包

    # rpm -qa|grep iproute
    iproute-3.10.0-54.el7.x86_64
    

#### 查看iproute包生成的文件

    # rpm -ql iproute
    

#### 查看iproute包配置文件

    # rpm -qc iproute
    

#### 查看iproute包生成的二进制文件

    # rpm -ql iproute|grep "bin" 
    /usr/sbin/arpd
    /usr/sbin/bridge
    /usr/sbin/cbq
    /usr/sbin/ctstat
    /usr/sbin/genl
    /usr/sbin/ifcfg
    /usr/sbin/ifstat
    /usr/sbin/ip    
    /usr/sbin/lnstat
    /usr/sbin/nstat
    /usr/sbin/routef
    /usr/sbin/routel
    /usr/sbin/rtacct
    /usr/sbin/rtmon
    /usr/sbin/rtpr
    /usr/sbin/rtstat
    /usr/sbin/ss
    /usr/sbin/tc

iproute与net-tools命令比对图

![][4]

## 二、ip命令帮助及模块功能

获取ip命令帮助

    # ip help
    Usage: ip [ OPTIONS ] OBJECT { COMMAND | help }
          ip [ -force ] -batch filename
    where  OBJECT := { link | addr | addrlabel | route | rule | neigh | ntable |
                      tunnel | tuntap | maddr | mroute | mrule | monitor | xfrm |
                      netns | l2tp | tcp_metrics | token }
          OPTIONS := { -V[ersion] | -s[tatistics] | -d[etails] | -r[esolve] |
                        -h[uman-readable] | -iec |
                        -f[amily] { inet | inet6 | ipx | dnet | bridge | link } |
                        -4 | -6 | -I | -D | -B | -0 |
                        -l[oops] { maximum-addr-flush-attempts } |
                        -o[neline] | -t[imestamp] | -b[atch] [filename] |
                        -rc[vbuf] [size] | -n[etns] name | -a[ll] }
    

#### 如果要获取某个子模块的帮助，如获取ip addr的具体用法，则

    # ip addr help
    Usage: ip addr {add|change|replace} IFADDR dev STRING [ LIFETIME ]
                                                          [ CONFFLAG-LIST ]
          ip addr del IFADDR dev STRING [mngtmpaddr]
          ip addr {show|save|flush} [ dev STRING ] [ scope SCOPE-ID ]
                                [ to PREFIX ] [ FLAG-LIST ] [ label PATTERN ] [up]
          ip addr {showdump|restore}
    IFADDR := PREFIX | ADDR peer PREFIX
              [ broadcast ADDR ] [ anycast ADDR ]
              [ label STRING ] [ scope SCOPE-ID ]
    SCOPE-ID := [ host | link | global | NUMBER ]
    FLAG-LIST := [ FLAG-LIST ] FLAG
    FLAG  := [ permanent | dynamic | secondary | primary |
              tentative | deprecated | dadfailed | temporary |
              CONFFLAG-LIST ]
    CONFFLAG-LIST := [ CONFFLAG-LIST ] CONFFLAG
    CONFFLAG  := [ home | nodad | mngtmpaddr | noprefixroute ]
    LIFETIME := [ valid_lft LFT ] [ preferred_lft LFT ]
    LFT := forever | SECONDS
    
    # man ip  //获取详细帮助

iproute各子模块功能

    ip link
            网络设备配置命令，如可以启用/禁用某个网络设备，改变mtu及mac地址等
    
    ip addr
            用于管理某个网络设备与协议(ip或ipv6)有关的地址。
            与ip link类似，不过增加了协议有关的管理(ip地址管理)
    
    ip addrlabel 
            ipv6的地址标签，主要用于RFC3484中描述的ipv6地址的选择。
            RFC3484主要介绍了2个算法，用于ipv6地址(源地址和目标地址)的选择策略
    
    ip route    
            管理路由，如添加，删除
    
    ip rule    
            管理路由策略数据库。这里边有一个算法，用来控制路由的选择策略
    
    ip neigh    
            用于neighbor/ARP表的管理，如显示，插入，删除等
    
    ip tunel
            隧道配置
            隧道的作用是将数据(可以是不同协议)封装成ip包然后再互联网传输
    
    ip maddr
            多播地址管理
    
    ip mroute
            多播路由管理
    
    ip monitor
            状态监控。如可以持续监控ip地址和路由的状态
    
    ip xfrm
            设置xfrm。xfrm是一个ip框架，可以转换数据包的格式，如用某个算法对数据包加密

## 三、频繁使用的几个子模块常用方法

子模块用法

    ip link 
            ip link show 查看默认网络连接信息，不包括ip地址
            ip link set 接口 [up|down] [multicast on|off]：
    
    ip addr
        可以在一个接口配置多个地址而不使用接口别名：显示这些地址
        ip addr show    
            ip addr add dev 接口 ip地址/掩码 [ ladel 别名 ]
          ip addr add dev 接口 ip地址 [ ladel 别名 ]
          ip addr flush 接口 [to 网络地址]
    
    ip route            
            ip route add 目标 via 下一跳 src 源地址 [dev 设备]
            ip route del 目标
            ip route list                                                                        
    
    启用/禁用接口：
            ip link set 接口 up|down
            ifconfig 接口 up|down
            ifdown 接口，ifup 接口
        重置网络连接
    
    TUI或GUI
            CentOS 6
                    system-config-network-tui
                配置结束后将保存配置文件中
                    setup --> Network Configuration
    
            CentOS 7
                    nmtui

## 四、使用示例

查看当前主机网络连接信息

    # ip link show
    1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN mode DEFAULT 
        link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
    2: eno16777728: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
        link/ether 00:0c:29:57:26:9d brd ff:ff:ff:ff:ff:ff
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff

查看当前主机指定网络连接信息    

    # ip link show eno16777728
    2: eno16777728: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
        link/ether 00:0c:29:57:26:9d brd ff:ff:ff:ff:ff:ff        

多播的启用与关闭

    # ip link set eno16777728 multicast off
    # ip link show eno16777728  //如下，没有出项MULTICAST
    2: eno16777728: <BROADCAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
        link/ether 00:0c:29:57:26:9d brd ff:ff:ff:ff:ff:ff
    # ip link set eno16777728 multicast on    

网卡的启用与关闭

    # ip link set eno33554960 down
    # ip link set eno33554960 up

显示主机ip地址信息

    # ip addr show
    1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN 
        link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
        inet 127.0.0.1/8 scope host lo
          valid_lft forever preferred_lft forever
        inet6 ::1/128 scope host 
          valid_lft forever preferred_lft forever
    2: eno16777728: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:9d brd ff:ff:ff:ff:ff:ff
        inet 172.24.8.131/24 brd 172.24.8.255 scope global dynamic eno16777728
          valid_lft 1196sec preferred_lft 1196sec           ### Author : Leshami
        inet6 fe80::20c:29ff:fe57:269d/64 scope link     ### Blog : http://blog.csdn.net/leshami 
          valid_lft forever preferred_lft forever                ### QQ/Weixin : 645746311
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff
        inet 192.168.81.144/24 brd 192.168.81.255 scope global dynamic eno33554960
          valid_lft 1380sec preferred_lft 1380sec

为指定网卡删除ip地址

    # ip addr del dev eno33554960 192.168.81.144/24
    # ip addr show eno33554960                    
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff

为指定网卡添加ip地址

    # ip addr add dev eno33554960 192.168.81.189/24  
    # ip addr show eno33554960                    
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff
        inet 192.168.81.189/24 scope global eno33554960
          valid_lft forever preferred_lft forever      

为指定网卡添加多ip地址      

    # ip addr add dev eno33554960 192.168.81.150/24
    # ip addr show eno33554960
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff
        inet 192.168.81.189/24 scope global eno33554960
          valid_lft forever preferred_lft forever
        inet 192.168.81.150/24 scope global secondary eno33554960
          valid_lft forever preferred_lft forever

为指定网卡添加多ip及使用别名

    # ip addr add dev eno33554960 192.168.81.199/24 label eno33554960:0

    # ip addr show label eno33554960:0
        inet 192.168.81.199/24 scope global secondary eno33554960:0
          valid_lft forever preferred_lft forever

    # ifconfig|grep eno33554960:0 -A2  
    eno33554960:0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
            inet 192.168.81.199  netmask 255.255.255.0  broadcast 0.0.0.0
            ether 00:0c:29:57:26:a7  txqueuelen 1000  (Ethernet)              

释放特定网卡ip地址

    # ip addr show eno33554960 
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff
        inet 192.168.81.144/24 scope global dynamic eno33554960
          valid_lft 1364sec preferred_lft 1364sec
        inet 192.168.81.199/24 scope global secondary eno33554960:0
          valid_lft forever preferred_lft forever
    # ip addr flush eno33554960 //如果不指定特定网卡，则表示当前主机所有网卡ip地址被释放
    # ip addr show eno33554960
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff

使用ip route添加网络路由

    ###当前本机IP地址为172.24.8.131
    ###假定要到达目标地址为:192.168.10.0/24，下一跳为 172.24.8.2 则添加路由命令如下

    # route -n  //首先查看当前的路由
    Kernel IP routing table
    Destination    Gateway        Genmask        Flags Metric Ref    Use Iface
    0.0.0.0        192.168.81.2    0.0.0.0        UG    100    0        0 eno33554960
    172.24.8.0      0.0.0.0        255.255.255.0  U    100    0        0 eno16777728
    192.168.81.0    0.0.0.0        255.255.255.0  U    100    0        0 eno33554960
    # ip route list //首先查看当前的路由
    default via 192.168.81.2 dev eno33554960  proto static  metric 100 
    172.24.8.0/24 dev eno16777728  proto kernel  scope link  src 172.24.8.131  metric 100 
    192.168.81.0/24 dev eno33554960  proto kernel  scope link  src 192.168.81.144  metric 100

    # ip route add 192.168.10.0/24 via 172.24.8.2 dev eno16777728
    # ip route list |grep 192.168.10
    192.168.10.0/24 via 172.24.8.2 dev eno16777728 

使用ip route添加主机路由

    ###假定要到达目标地址为：192.168.20.1，下一跳为: 172.24.8.254 
    # ip route add 192.168.20.1 via 172.24.8.254
    # ip route list |grep 192.168.20.1
    192.168.20.1 via 172.24.8.254 dev eno16777728

    删除之前添加的网络路由和主机路由
    # ip route del 192.168.10.0/24  ###对于网络路由应指定掩码
    # ip route del 192.168.20.1

    # ip route list
    default via 192.168.81.2 dev eno33554960  proto static  metric 100 
    172.24.8.0/24 dev eno16777728  proto kernel  scope link  src 172.24.8.131  metric 100 
    192.168.81.0/24 dev eno33554960  proto kernel  scope link  src 192.168.81.144  metric 100 

删除缺省网关

    # ip route del default
    # ip route list|grep default

添加缺省网关

    # ip route add default via 172.24.8.2 dev eno16777728
    # ip route list|grep default
    default via 172.24.8.2 dev eno16777728 

配置特定网卡指定IP路由

    # ip addr add dev eno33554960 172.27.8.150/24  ###为eno33554960添加一个新ip
    # ip addr show eno33554960
    3: eno33554960: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 00:0c:29:57:26:a7 brd ff:ff:ff:ff:ff:ff
        inet 192.168.81.144/24 scope global dynamic eno33554960
          valid_lft 1246sec preferred_lft 1246sec
        inet 172.27.8.150/24 scope global eno33554960
          valid_lft forever preferred_lft forever

    ###配置到达网络192.168.10.0经由172.27.8.2路由并且从这个ip 172.27.8.150收发数据包
    # ip route add 192.168.10.0 via 172.27.8.2 src 172.27.8.150 
    # ip route list |grep 172.27.8.150
    172.27.8.0/24 dev eno33554960  proto kernel  scope link  src 172.27.8.150 
    192.168.10.0 via 172.27.8.2 dev eno33554960  src 172.27.8.150


[1]: http://blog.csdn.net/leshami/article/details/78021859

[4]: ../IMG/26F3Ybb.png
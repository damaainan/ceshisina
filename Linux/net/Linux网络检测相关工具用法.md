# Linux网络检测相关工具用法(ping/netstat/ss/ethtool)

 时间 2017-09-21 17:04:26  

原文[http://blog.csdn.net/leshami/article/details/78054305][1]


当新的Linux主机完成了网络配置，即可以正常接入网络后，我们可以通过Linux自带的相关工具进行网络相关检测。如最常用的ping，netstat，ss，traceroute，ethtool等相关命令。本文主要是描述这几个命令的使用方法，供大家参考。

有关为网络配置基础可以参考以下链接：

[网络及TCP/IP简明快速基础][4][Linux 主机网络接入配置][5][基于iproute命令集配置Linux网络(ip命令)][6]

## 一、ping命令的使用

ping这个命令会发送一些数据包到目标主机，用于检查目标网络是否可达，其协议为基于icmp协议。

获取ping的帮助信息

    # ping -help
    Usage: ping [-aAbBdDfhLnOqrRUvV] [-c count] [-i interval] [-I interface]
                [-m mark] [-M pmtudisc_option] [-l preload] [-p pattern] [-Q tos]
                [-s packetsize] [-S sndbuf] [-t ttl] [-T timestamp_option]
                [-w deadline] [-W timeout] [hop1 ...] destination
    
    常用的用法如下
      ping [options] ip
            -c 次数
        -w 测试执行时长    
    
    使用示例
    

#### 当前环境

    # more /etc/redhat-release 
    CentOS Linux release 7.2.1511 (Core) 
    

#### ping本地回环

    # ping 127.0.0.1 -c 2 
    PING 127.0.0.1 (127.0.0.1) 56(84) bytes of data.
    64 bytes from 127.0.0.1: icmp_seq=1 ttl=64 time=0.108 ms
    64 bytes from 127.0.0.1: icmp_seq=2 ttl=64 time=0.037 ms
    
    --- 127.0.0.1 ping statistics ---
    2 packets transmitted, 2 received, 0% packet loss, time 1000ms
    rtt min/avg/max/mdev = 0.037/0.072/0.108/0.036 ms
    

#### ping本机IP

    # ping 192.168.81.144 -c 2        
    PING 192.168.81.144 (192.168.81.144) 56(84) bytes of data.
    64 bytes from 192.168.81.144: icmp_seq=1 ttl=64 time=0.209 ms
    64 bytes from 192.168.81.144: icmp_seq=2 ttl=64 time=0.058 ms
    
    --- 192.168.81.144 ping statistics ---
    2 packets transmitted, 2 received, 0% packet loss, time 1000ms
    rtt min/avg/max/mdev = 0.058/0.133/0.209/0.076 ms
    

#### ping外部网络(URL，验证DNS解析)

    # ping www.baidu.com -w 2 
    PING www.baidu.com (14.215.177.38) 56(84) bytes of data.
    64 bytes from 14.215.177.38: icmp_seq=1 ttl=128 time=5.92 ms
    64 bytes from 14.215.177.38: icmp_seq=2 ttl=128 time=6.19 ms
    
    --- www.baidu.com ping statistics ---
    2 packets transmitted, 2 received, 0% packet loss, time 1002ms
    rtt min/avg/max/mdev = 5.925/6.057/6.190/0.153 ms
    

#### 指定ping包的大小

    # ping www.baidu.com -s 1024 -c 2
    PING www.baidu.com (14.215.177.38) 1024(1052) bytes of data.
    1032 bytes from 14.215.177.38: icmp_seq=1 ttl=128 time=6.21 ms
    1032 bytes from 14.215.177.38: icmp_seq=2 ttl=128 time=6.47 ms
    
    --- www.baidu.com ping statistics ---
    2 packets transmitted, 2 received, 0% packet loss, time 1002ms
    rtt min/avg/max/mdev = 6.218/6.348/6.478/0.130 ms

## 二、traceroute | mtr 命令的使用

#### traceroute命令

该命令获取当前主机到目标主机所经过的路由(网关)

该命令通过发送小的数据包到目的设备直到其返回，来测量其需要多长时间

最常用的用法

traceroute HOST

获取traceroute帮助

# man traceroute

使用示例


#### traceroute本地主机

    # traceroute 192.168.1.131
    traceroute to 192.168.1.131 (192.168.1.131), 30 hops max, 60 byte packets
    1  192.168.81.2 (192.168.81.2)  0.388 ms  0.341 ms  0.134 ms
    2  * * *
    3  * * * //后面部分省略
    

#### traceroute URL

    # traceroute www.baidu.com
    traceroute to www.baidu.com (14.215.177.39), 30 hops max, 60 byte packets
    1  192.168.81.2 (192.168.81.2)  0.178 ms  0.166 ms  0.179 ms 
    

#### 绕过路由表探测目标URL，如下，提示网络不可达

    # traceroute -r www.baidu.com
    traceroute to www.baidu.com (14.215.177.39), 30 hops max, 60 byte packets
    connect: Network is unreachable

#### mtr命令

网络连通性判断工具，它结合了ping, traceroute,nslookup 的相关特性

mtr HOST

示例


####  mtr本地主机

    # mtr 192.168.1.131
    centos7-a.example.com (0.0.0.0)                                              Wed Sep  6 15:55:39 2017
    Keys:  Help Display mode Restart statistics Order of fields quit
                                                                    Packets              Pings
    Host                                                          Loss%  Snt  Last  Avg  Best  Wrst StDev
    1. 192.168.81.2                                                0.0%    77    0.2  0.3  0.1  1.5  0.1
    2. ???
    

#### mtr URL

    # mtr www.baidu.com
    centos7-a.example.com (0.0.0.0)                                                Wed Sep  6 15:59:29 2017 
    Keys:  Help Display mode Restart statistics Order of fields quit                                  
                                                                    Packets              Pings            
    Host                                                          Loss%  Snt  Last  Avg  Best  Wrst StDev
    1. 192.168.81.2                                                0.0%  131    0.3  0.3  0.1  6.7  0.6
    2. 192.168.1.1                                                  0.0%  131    3.5  1.6  0.8  7.1  0.8  
    3. 58.61.29.9                                                  0.0%  130    9.3  2.7  1.1  89.3  7.7
    4. 14.215.177.39                                                2.3%  130  11.1  6.2  5.2  14.6  1.5

## 三、tracepath命令的使用

用来追踪并显示报文到达目的主机所经过的路由信息

tracepath [option] hostname

    常用选项:
    -n    对沿途各主机节点, 仅仅获取并输出IP地址
            不在每个IP 地址的节点设备上通过DNS查找其主机名,以此来加快测试速度。
    -b    对沿途各主机节点同时显示IP地址和主机名。
    -l    包长度——设置初始的数据包的大小。
    -p  端口号——设置UDP传输协议的端口(缺省为33434)。
    
    示例
    
    # tracepath www.baidu.com
    1: [LOCALHOST]                                        pmtu 1500
    1:  192.168.81.2                                          0.204ms 
    1:  192.168.81.2                                          0.387ms 
    2:  no reply

## 四、netstat命令的使用

netstat

该命令用于显示各种网络相关信息，如网络连接，路由表，接口状态 (Interface Statistics)

masquerade 连接，多播成员 (Multicast Memberships) 等等。

    常用选项    
      -t：tcp协议的连接
      -u：udp协议的链接
      -l：监听状态的连接
      -a：所有状态的连接
      -p：连接相关的进程
      -n：数字格式显示
      -e: 显示额外的信息
      -r: 显示路由表，类似于route或ip route show
    
    常用组合：
        netstat -tan
        netstat -tunlp
        netstat -rn
    
    示例
    

#### 列出所有的端口，包括监听的和未监听的

    # netstat -a
    

#### 列出所有的tcp协议的端口

    # netstat -t              ###Author : Leshami
    Active Internet connections (w/o servers)  ###Blog  : http://blog.csdn.net/leshami
    Proto Recv-Q Send-Q Local Address          Foreign Address        State      
    tcp        0    96 172.24.8.131:ssh        172.24.8.1:59658        ESTABLISHED
    tcp        0      0 172.24.8.131:ssh        172.24.8.1:62097        ESTABLISHED
    

#### 寻找特定程序运行的端口

    # netstat -nltp|grep sshd
    tcp        0      0 0.0.0.0:22              0.0.0.0:*              LISTEN      1564/sshd          
    tcp6      0      0 :::22                  :::*                    LISTEN      1564/sshd 
    

#### 寻找特定端口对应的程序

    # netstat -nltp|grep 1521
    tcp6      0      0 :::1521                  :::*                LISTEN      3708/tnslsnr 
    

#### 查看本机路由信息

    # netstat -r              
    Kernel IP routing table
    Destination    Gateway        Genmask        Flags  MSS Window  irtt Iface
    default        192.168.81.2    0.0.0.0        UG        0 0          0 eno33554960
    172.24.8.0      0.0.0.0        255.255.255.0  U        0 0          0 eno16777728
    192.168.81.0    0.0.0.0        255.255.255.0  U        0 0          0 eno33554960

## 五、ss命令的使用

是socket state缩写，可以查看系统中socket的状态的

如显示PACKET sockets, TCP sockets, UDP sockets, DCCP sockets, RAW sockets, Unix domain sockets等统计

ss一个非常实用、快速、有效的跟踪IP连接和sockets的新工具，用于取代netstat

    用法：
            ss [ OPTIONS ] [ FILTER ]
            常用选项
          -t：tcp协议的连接
          -u：udp协议的链接
          -l：监听状态的连接
          -a：所有状态的连接
          -e：显示扩展信息
          -m：显示套接连接使用的内存信息
          -p：进程及UDP
          -n：数字格式显示
          -o state (established) 
    
            ss -o state established '( dport = :smtp or sport = :smtp )' 显示所有已建立的SMTP连接
            ss -o state established '( dport = :http or sport = :http )' 显示所有已建立的HTTP连接
            ss -x src /tmp/.X11-unix/* 找出所有连接X服务器的进程  ###*/
            ss -s 列出当前socket详细信息:    
    
    示例
    

#### 查看所有TCP协议的连接

    # ss -ta
    State      Recv-Q Send-Q          Local Address:Port                              Peer Address:Port                
    LISTEN      0      128                    *:ssh                                          *:*                    
    LISTEN      0      128            127.0.0.1:ipp                                          *:*                    
    LISTEN      0      100            127.0.0.1:smtp                                          *:*                    
    ESTAB      0      96          172.24.8.131:ssh                                  172.24.8.1:59658                
    ESTAB      0      0            172.24.8.131:ssh                                  172.24.8.1:62097
    

#### 查看所有协议监听以及列出进程号

    # ss -nltup  
    Netid State      Recv-Q Send-Q Local Address:Port  Peer Address:Port
    udp  UNCONN    0      0                  *:44819        *:*  users:(("avahi-daemon",pid=888,fd=13))
    udp  UNCONN    0      0                  *:58348        *:*  users:(("dhclient",pid=63962,fd=20))
    udp  UNCONN    0      0                  *:68            *:*  users:(("dhclient",pid=63962,fd=6))
    udp  UNCONN    0      0                  *:68            *:*  users:(("dhclient",pid=37433,fd=6))
    udp  UNCONN    0      0                  *:5353          *:*  users:(("avahi-daemon",pid=888,fd=12))
    udp  UNCONN    0      0                  *:5384          *:*  users:(("dhclient",pid=37433,fd=20))
    udp  UNCONN    0      0                :::19332        :::*  users:(("dhclient",pid=37433,fd=21))
    tcp  LISTEN    0      128              *:22             *:*  users:("sshd",pid=1564,fd=3))
    tcp  LISTEN    0      128          127.0.0.1:631        *:*  users:(("cupsd",pid=1566,fd=13))
    tcp  LISTEN    0      100          127.0.0.1:25          *:*  users:(("master",pid=2184,fd=13))
    tcp  LISTEN    0      128                :::22        :::*  users:(("sshd",pid=1564,fd=4))
    

#### 查看所有基于ssh建立连接信息

    # ss -o state established '( dport = :ssh or sport = :ssh )'    
    Netid Recv-Q Send-Q Local Address:Port  Peer Address:Port      
    tcp  0      96      172.24.8.131:ssh    172.24.8.1:59658        timer:(on,402ms,0)
    tcp  0      0      172.24.8.131:ssh    172.24.8.1:62097        timer:(keepalive,60min,0)

## 六、ethtool命令

用于获取以太网卡的配置信息，或者修改这些配置

    常用用法
    ethtool eth0        //查询ethx网口基本设置，其中 x 是对应网卡的编号，如eth0、eth1等等
    ethtool –h        //显示ethtool的命令帮助(help)
    ethtool –i eth0    //查询eth0网口的相关信息
    ethtool –d eth0    //查询eth0网口注册性信息
    ethtool –r eth0    //重置eth0网口到自适应模式
    ethtool –S eth0    //查询eth0网口收发包统计
    ethtool –s eth0 [speed 10|100|1000] [duplex half|full]  [autoneg on|off]  
                                          //设置网口速率10/100/1000M、设置网口半/全双工、设置网口是否自协商
    
    示例
    

#### 查看指定网卡的信息

    # ethtool eno16777728
    Settings for eno16777728:
            Supported ports: [ TP ]
            Supported link modes:  10baseT/Half 10baseT/Full 
                                    100baseT/Half 100baseT/Full 
                                    1000baseT/Full 
            Supported pause frame use: No
            Supports auto-negotiation: Yes
            Advertised link modes:  10baseT/Half 10baseT/Full 
                                    100baseT/Half 100baseT/Full 
                                    1000baseT/Full 
            Advertised pause frame use: No
            Advertised auto-negotiation: Yes
            Speed: 1000Mb/s
            Duplex: Full
            Port: Twisted Pair
            PHYAD: 0
            Transceiver: internal
            Auto-negotiation: on
            MDI-X: off (auto)
            Supports Wake-on: d
            Wake-on: d
            Current message level: 0x00000007 (7)
                                  drv probe link
            Link detected: yes
    

#### 查看网卡中接收模块RX、发送模块TX和Autonegotiate模块的状态

    # ethtool -a eno16777728
    Pause parameters for eno16777728:
    Autonegotiate:  on
    RX:            off
    TX:            off
    

#### 显示网卡驱动的信息，如驱动的名称、版本等

    # ethtool -i eno16777728
    driver: e1000
    version: 7.3.21-k8-NAPI
    firmware-version: 
    bus-info: 0000:02:00.0
    supports-statistics: yes
    supports-test: yes
    supports-eeprom-access: yes
    supports-register-dump: yes
    supports-priv-flags: no
    

#### 查询指定网卡的统计信息

    # ethtool -S eno16777728
    NIC statistics:
        rx_packets: 12374
        tx_packets: 9145
        rx_bytes: 1572275
        tx_bytes: 1939008
        rx_broadcast: 0
        tx_broadcast: 0
        rx_multicast: 0
        tx_multicast: 0
        rx_errors: 0
        tx_errors: 0
        tx_dropped: 0
        multicast: 0
        collisions: 0
        rx_length_errors: 0


[1]: http://blog.csdn.net/leshami/article/details/78054305

[4]: http://blog.csdn.net/leshami/article/details/77848302
[5]: http://blog.csdn.net/leshami/article/details/77933663
[6]: http://blog.csdn.net/leshami/article/details/78021859
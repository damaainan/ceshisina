# ip命令用法归纳

<font face=微软雅黑>

1 个月前

ip是iproute2工具包里面的一个命令行工具，用于配置网络接口以及路由表。iproute2正在逐步取代旧的net-tools (ifconfig)，所以是时候学习下iproute2的使用方法啦～

## **接口信息查看**

**查看接口状态和详细统计**

（不指定接口则显示所有接口的详细统计）

    ip -d -s -s link show [dev <接口名>] 
    

例：查看ens34接口信息。

    [root: ~]# ip -d -s -s link show ens34
    
    3: ens34: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
        link/ether 88:32:9b:ca:3f:4a brd ff:ff:ff:ff:ff:ff promiscuity 0 addrgenmode eui64 
        RX: bytes  packets  errors  dropped overrun mcast   
        581645     6100     0       0       0       0       
        RX errors: length   crc     frame   fifo    missed
                   0        0       0       0       0       
        TX: bytes  packets  errors  dropped carrier collsns 
        3743584    3939     0       0       0       0       
        TX errors: aborted  fifo   window heartbeat transns
                   0        0       0       0       2       
    

## **IP地址设置**

**查看接口IP地址**

（不指定接口则显示所有接口的IP地址）

    ip addr show [dev <接口名>]
    

**查看接口IPv6地址**

（不指定接口则显示所有接口的IPv6地址）

    ip -6 addr show [dev <接口名>]
    

**为接口添加IP地址**

    ip addr add <IP地址/前缀长度> [broadcast <广播地址>] dev <接口名>
    

**为接口添加IPv6地址**

    ip -6 addr add <IPv6地址/前缀长度> dev <接口名>
    

**为接口删除IP地址**

    ip addr del <IP地址/前缀长度> dev <接口名>
    

**为接口删除IPv6地址**

    ip -6 addr del <IP地址/前缀长度> dev <接口名>
    

例：为ens34添加IP地址192.168.1.111/24并检查。

    [root: ~]# ip addr add 192.168.1.111/24 dev ens34
    
    3: ens34: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
        link/ether 88:32:9b:ca:3f:4a brd ff:ff:ff:ff:ff:ff
        inet 10.16.1.2/24 brd 10.16.1.255 scope global ens34
           valid_lft forever preferred_lft forever
        inet 192.168.1.111/24 scope global ens34
           valid_lft forever preferred_lft forever
        inet6 fe80::f65c:89ff:fecd:3ab5/64 scope link 
           valid_lft forever preferred_lft forever
    

## **接口设置**

**启用接口**

    ip link set <接口名> up
    

**禁用接口**

    ip link set <接口名> down
    

**设置接口MAC地址**

（设置前请先禁用接口）

    ip link set <接口名> address <值>
    

**设置接口MTU**

（设置前请先禁用接口）

    ip link set <接口名> mtu <值>
    

例：把ens33的MTU改成9000并检查。

    [root: ~]# ip link show dev ens33 #修改前
    
    2: ens33: <BROADCAST,MULTICAST> mtu 1500 qdisc pfifo_fast state DOWN mode DEFAULT qlen 1000
        link/ether 88:32:9b:ca:3f:49 brd ff:ff:ff:ff:ff:ff
    [root: ~]# ip link set ens33 mtu 9000
    
    [root: ~]# ip link show dev ens33  #修改后
    
    2: ens33: <BROADCAST,MULTICAST> mtu 9000 qdisc pfifo_fast state DOWN mode DEFAULT qlen 1000
        link/ether 88:32:9b:ca:3f:49 brd ff:ff:ff:ff:ff:ff
    

## **VLAN设置**

**添加802.1Q VLAN子接口**

    ip link add link <接口名> name <子接口名> type vlan id <VLAN_ID>
    

**删除802.1Q VLAN子接口**

    ip link del <接口名>
    

例：为ens33添加VLAN100子接口并检查。

    [root: ~]# ip link add link ens33 name ens33.100 type vlan id 100
    
    [root: ~]# ip -d -s -s link show ens33.100
    
    7: ens33.100@ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 9000 qdisc noqueue state UP mode DEFAULT qlen 1000
        link/ether 88:32:9b:ca:3f:aa brd ff:ff:ff:ff:ff:ff promiscuity 0 
        vlan protocol 802.1Q id 100 <REORDER_HDR> addrgenmode eui64 
        RX: bytes  packets  errors  dropped overrun mcast   
        0          0        0       0       0       0       
        RX errors: length   crc     frame   fifo    missed
                   0        0       0       0       0       
        TX: bytes  packets  errors  dropped carrier collsns 
        738        9        0       0       0       0       
        TX errors: aborted  fifo   window heartbeat transns
                   0        0       0       0       3       
    

## **路由表设置**

**查看路由表**

（不指定接口则显示所有接口的路由表）

    ip route show [dev <接口名>]
    

**查看指定目标地址用的是哪条路由表**

    ip route get <目标IP>
    

**添加路由表**

    ip route add <目标IP地址/前缀长度> via <下一跳> [dev <出接口>]
    

**添加默认网关**

    ip route add default via <默认网关> [dev <出接口>]
    

**删除路由表**

    ip route del <目标IP地址/前缀长度> via <下一跳> [dev <出接口>]
    

例：查看目标地址为8.8.8.8用的是哪条路由表。

    [root: ~]# ip route get 8.8.8.8
    
    8.8.8.8 via 192.168.1.1 dev ens33  src 192.168.1.143 
        cache 
    #下一跳是192.168.1.1，出接口是ens33，接口的IP是192.168.1.143。
    

## **ARP设置**

**查看ARP表**

（不指定接口则显示所有接口的ARP表）

    ip neigh show [dev <接口名>] 
    

**添加永久ARP条目**

    ip neigh add <IP地址> lladdr <以冒号分割的MAC地址> dev <接口名> nud permanent
    

**把动态ARP条目转换为永久ARP条目（仅限已存在条目）**

    ip neigh change <IP地址> dev <接口名> nud permanent
    

**删除ARP条目**

    ip neigh del <IP地址> dev <接口名>
    

**清空ARP表(不影响永久条目)**

    ip neigh flush all

</font>

[0]: https://www.zhihu.com/people/yuzenan888
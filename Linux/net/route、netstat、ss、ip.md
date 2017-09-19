# route、netstat、ss、ip（网络管理3）

关注 2017.06.28 23:41  字数 19  

## route命令

    查看：route -n
    添加：route add（临时生效、重启丢失）
    删除：route del
    跟踪路由：traceroute/tracepath

    目标（主机路由）：192.168.1.3（机器IP）； 网关：172.16.0.1
    route add -host 192.168.1.3（target） gw172.16.0.1 dev eth0
    
    目标（网络路由）：192.168.0.0（网段）； 网关：172.16.0.1
    route add -net 192.168.0.0/24（target） gw172.16.0.1 dev eth0
    
    dev eth0——可省略（根据网关，可以自动判断接口）；

    添加默认路由，网关：172.16.0.1
    route add -net 0.0.0.0 netmask 0.0.0.0  gw 172.16.0.1
    route add default gw 172.16.0.1

    目标：192.168.1.3 网关：172.16.0.1
    route del -host 192.168.1.3
    
    目标：192.168.0.0 网关：172.16.0.1
    route del -net 192.168.0.0 netmask 255.255.255.0
    
    删除默认路由
    route del default gw 172.16.0.1
    route del -net 0.0.0.0/0 gw 172.16.0.1

## netstat命令

    netstat=ss（通用选项）：显示网络连接；

    -t：tcp协议相关；显示正在发送的数据连接（有6个状态）；
    -u：udp协议相关（无6个状态）；

    -w：raw socket相关；socket套接字；TCP/IP；
    不同网络间主机访问；IP+tcp/udp+port；
    C/S：C——IP/tcp：xxx随机；S——IP/tcp：80；

    -l：处于监听（LISTEN）状态的端口；
    -a：所有状态；
    -n：以数字显示IP和端口；
    -e：扩展格式（详细信息：user...）；
    -p：显示相关进程及PID（程序~端口）；

    DHCP~67（服务）~68（客户）；
    
    netstat -nt   效率低
    ss -nt        效率高

    netstat -ntulp  查询服务
    利用此命令，可以查询系统运行的程序；
    无用程序都卸载——优化系统状态、安全；
    工作上——最小化安装——需要程序——yum安装；

    netstat -ntuape  某个端口哪个应用程序在用；
    lsof -i :80/:20  某个端口哪个应用程序在用；

    -nr  纯数字形式；显示内核路由表；
    -i  MTU  RX（收）/RX-DRP（丢弃）/RX-OVR（负载）/TX（发）
    -I etho  错误命令（奇葩命令）
    -Ietho   流量监控（异常~正常值比较）
    -I=etho  流量监控
    watch -n1 netstat -Ieth0  流量监控
    watch -n1 ifconfig -s eth0

## ss命令

    netstat通过遍历proc来获取socket信息；
    ss使用netlink与内核tcp_diag模块通信获取socket信息；
    netstat和ss二者显示效果不同；
    ss——过滤状态（state）；

    端口：dport= :ssh  sport= :ssh

    -x：unixsock相关
    -m：内存用量
    -o：计时器信息（统计信息）

    常用组合：-tan，-tanl，-tanlp，-uan

    ss -l     显示本地打开的所有端口
    ss -pl    显示每个进程具体打开的socket
    ss -t -a  显示所有tcp socket
    ss -u -a  显示所有的UDP Socekt

    显示所有已建立的ssh连接（注意空格）
    ss -o state established '( dport = :ssh or sport = :ssh )'    
    
    显示所有已建立的HTTP连接
    ss -o state established '( dport = :http or sport = :http )'

    ss -s  列出当前socket详细信息
    
    在centos6.9上，watch -n1 ss -s
    在centos7.3上，ping centos6.9的ip；ping不通，原因是ping 192.168.8.128 不走tcp协议，tcp协议——传输层；
    service httpd start
    ab -c 10 -n 10000 http://192.168.8.129/

## ip命令（配置Linux网络属性）

    ip [OPTIONS] OBJECT {COMMAND|help} 
    OBJECT：{link|addr|route}
    ip addr = ip a   显示信息（数据链路层+网络层）
    ip route
    ip补选项——centos7.3可以——centos6.9不行；

    效果不同（以下两者）
    （1）ifup/ifdown ens1  网络层
         ifconfig查看到无IP地址；
    （2）ifconfig ens1 down/up = ip link set ens1 up/down  数据链路层（更彻底）；
         ip link show ens1  查看信息（处于激活状态的接口）

    地址：作用域（scope）
    global：全局可用
    link：仅链接可用
    host：本机可用

![][1]



Paste_Image.png

    ip addr add/del 1.1.1.1/24 dev eth1 (label eth1:home)
    [scope {global|link|host}]：指明作用域；
    [broadcast ADDRESS]：指明广播地址；
    [label LABEL]：添加地址时指明网卡别名；ifconfig可以看到label；
    label eth1：home 或 label eth1：office
    
    ip a（addr） 可以看到新增加的内容
    ifconfig    看不到新增加的内容

    一个网卡多个IP地址，家里和公司；不用手动更改；
    
    centos6.9上：
    ip a add 3.3.3.3/24 dev eth1  增加一个ip给eth1
    ip a add 3.3.3.3/24 dev eth1 label eth1:home  增加一个别名和ip 
    ip a del 3.3.3.3/24 dev eth1 label eth1:home  删除一个别名和ip
    
    centos 7.3上：
    ping 3.3.3.100可以ping通；
    ping -Ieth0 3.3.3.100  指定ping网卡或接口的哪个IP（测试用）
    ip addr flush dev eth1 (eth1:home)  删除（清空）eth1的IP地址

    ip route add/del 
    
    主机路由TARGET：IP
    ip route add 192.168.1.13 via 172.16.0.1
    
    网络路由TARGET：NETWORK/MASK
    ip route add 192.168.0.0/24 via 172.16.0.1
    
    默认路由
    iproute add default via GW dev IFACE
    iproute add default via 172.16.0.1
    
    删除路由：ip route del TARGET
    
    显示路由：ip route show|list
    
    清空路由表：ip route flush [dev IFACE] 
              ip route flush dev eth0


[1]: http://upload-images.jianshu.io/upload_images/6044565-7249acdab2328250.png
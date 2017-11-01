## 浅析负载均衡及LVS实现 

 原创 _2017-09-19__fireflyc_[写程序的康德][0] 写程序的康德 **写程序的康德** kant_prog

 关注Java和Python和云计算技术分享，主要分享一些进阶类的内容希望更多“成长中的程序员”可以从这些内容中学会“思考”。

## 负载均衡

 负载均衡（Load Balance，缩写LB）是一种**网络技术**，它在多个备选**资源**中做资源分配，以达到选择**最优**。这里有三个关键字：

1. 网络技术，LB要解决的问题本质上是网络的问题，所以它实际上就是 通过修改数据包中MAC地址、IP地址字段来实现数据包的“中转” ；
1. 资源，这里的资源不仅仅是计算机也可以是交换机、存储设备等；
1. 最优，它则是针对业务而言最优，所以一般负载均衡有很多算法；轮询、加权轮询、最小负载等；

LB是网络技术所以业内就参考OSI模型用**四层负载均衡**、**七层负载均衡**进行分类。四层负载均衡工作在OSI的四层，这一层主要是TCP、UDP、SCTP协议，这种类型的负载均衡器不管数据包是什么，只是通过修改IP头部或者以太网头部的地址实现负载均衡。七层负载均衡工作在OSI的七层，这一层主要是HTTP、Mysql等协议，这种负载均衡一般会把数据包内容解析出来后通过一定算法找到合适的服务器转发请求。它是针对某个特定协议所以不**通用**。比如Nginx只能用于HTTP而不适用于Mysql。 四层负载均衡真正传统意义上的负载均衡，它通过修改网络数据包“中转”请求；一般工作在操作系统的内核空间（kernel space），比如通过Linux的netfilter定义的hook改变数据包。七层负载均衡并不是严格意义上的负载均衡，它必须解析出数据包的内容，根据内容来做相关的转发（比如做Mysql的读写分离）；一般工作在用户空间（user space），比如通过Nginx、Mysql Proxy、Apache它们都是实现某个具体协议，很多资料都称这种软件叫代理（Proxy）。

## 实现LB的问题

 无论哪种负载均衡都可以抽象为下面的图形：

![][1]

 任何负载均衡都要解决三个问题：

1. 修改数据包，使得数据包可以发送到backend servers；
1. frontend server要维护一个算法，可以选出 最优 的backend server
1. frontend server要维护一张表记录Client和backend servers的关系（比如TCP请求是 一系列数据包 ，所以在TCP关闭所有的数据包都应该发送到同一个backend server）

以Nginx为例，forntend server收到HTTP数据包后会通过**负载均衡算法**选择出一台backend server；然后**从本地重新构造一个HTTP请求发送给backend server，收到backend server请求后再次重新封装，以自己的身份返回给客户端**。在这个过程中forntend server的Nginx是工作在用户空间的它代替Client访问backend server。

## LVS的实现

 LVS( Linux Virtual Server)是国产开源中非常非常非常优秀的项目，作者是章文嵩博士（关于章博的简历各位自行搜索）。它是一款四层负载均衡软件，在它的实现中forntend server称为director；backend server称为real server，它支持UDP、TCP、SCTP、IPSec（ AH 、ESP两种数据包 ）四种传输层协议的负载。

![][2]

LVS以内核模块的形式加载到内核空间，通过netfilter定义的hook来实现数据包的控制。 它用到了三个Hook（以Linux 4.8.15为例）主要“挂在”：local_in、inet_forward、local_out；所有发送给本机的数据包都会经过local_int，所有非本机的数据包都会经过forward，所有从本机发出的数据包都会经过local_out。

 LVS由两部分组成（很像iptables），用户空间提供了一个ipvsadm的命令行工具，通过它定义负载均衡的“规则”；内核模块是系统的主要模块它包括：

* IP包处理模块，用于截取/改写IP报文；
* 连接表管理，用于记录当前连接的Hash表；
* 调度算法模块，提供了八种负载均衡算法——轮询、加权轮询、最少链接、加权最少链接、局部性最少链接、带复制的局部性最少链接、目标地址哈希、源地址哈希；
* 连接状态收集，回收已经过时的连接；
* 统计，IPVS的统计信息；

## LVS实战

 LVS术语定义：

* DS：Director Server，前端负载均衡器节点（后文用Director称呼）；
* RS：Real Server，后端真实服务器；
* VIP：用户请求的目标的IP地址，一般是公网IP地址；
* DIP：Director Server IP，Director和Real Server通讯的内网IP地址；
* RIP：Real Server IP，Director和Real Server通讯的内网IP地址；

很多文章都罗列了一大堆LVS三种模式之间的区别，我最讨厌的就是简单的罗列——没有什么逻辑性很难记忆。其实LVS中三种模式只有一个区别——谁来返回数据到客户端。在LB架构中客户端请求一定是先到达forntend server（LVS中称为Director），那么返回数据包则不一定经过Director。

* NAT模式中，RS返回数据包是返回给Director，Director再返回给客户端；
* DR（Direct Routing）模式中，RS返回数据是直接返回给客户端（通过额外的路由）；Director通过修改请求中目标地址MAC为选定的RS实现数据转发，这就要求 Diretor和Real Server必须在同一个广播域内 （关于广播域请看《程序员学网络系列》）。
* TUN（IP Tunneling）模式中，RS返回的数据也是直接返回给客户端，这种模式通过Overlay协议（把一个IP数据包封装到另一个数据包内部叫Overlay）避免了DR的限制。

以上就是LVS三种模式真正的区别，是不是清晰多了？^_^

### NAT模式

![][3]

 NAT模式最简单，real_server只配置一个内网IP地址（RIP），网关指向director；director配置连个IP地址分别是提供外部服务的VIP和用于内部通讯的DIP。

* 配置IP地址

VIP：10.10.10.10， DIP：192.168.122.100 RS1-DIP：192.168.122.101 RS2-DIP：192.168.122.102

![][4]

 注意：**Director配置了双网卡，默认路由指向10.10.10.1。即——VIP所在的网卡设置网关，DIP所在的网卡不要设置网关。**

* 配置director

Linux默认不会“转发”数据包，通过echo 1 > /proc/sys/net/ipv4/ip_forward开启forward功能。**开启forward后Linux表现的就像一个路由器**，它会根据本机的路由表转发数据包。

    ipvsadm -A -t 10.10.10.10:80 -s rr       # 添加LVS集群，负载均衡算法为轮询（rr）  
    ipvsadm -a -t 10.10.10.10:80 -r 192.168.122.101 -m -w 1   # 添加LVS集群主机（10.10.10.10:80），VS调度模式为NAT（-m）及RS权重为1  
    ipvsadm -a -t 10.10.10.10:80 -r 192.168.122.102 -m -w 1   # 同上

* 验证

通过client访问10.10.10.10的HTTP服务

![][5]

### NAT模式原理解析

* client发送数据包，被路由到director服务器上；
* director的netfilter local_in hook被触发，lvs模块收到该请求
* lvs查询规则库（ipvsadm生成的规则），发现10.10.10.10:80端口被定义为NAT模式，执行轮询算法
* IP包处理模块修改数据包，把目标IP地址修改为192.168.122.101，从DIP的所在网卡发送出去（所以源MAC是DIP网卡的MAC）。此时的数据包是： 源MAC地址变成了00:01:3a:4d:5d:00（DIP网卡的MAC地址）源IP地址是172.10.10.10（client的IP地址），目标MAC和目标IP地址是本机地址 。通过在RS1上抓包验证这一点


![][6]

 注意，Linux不会“校验”源IP地址是否是本机IP地址所以即便172.10.10.10不在DIP上数据包也是可以被发送的，此时的行为相当于“路由”（想一下“网关”如何给你发送某个公网返回的数据包）。

* RS1收到请求目标MAC和IP都是本机，所以直接交给Nginx处理
* Nginx的返回数据包交给Linux协议栈，系统发现目标地址是172.10.10.10和自己不在同一个网段，则把数据包交给网关——192.168.122.100（director）。 这就是Real Server必须把网关指向Director Server的原因
* director上的lvs再次触发，发现是返回数据包是：源MAC地址和IP地址是VIP网卡的MAC地址

这种模式虽然叫NAT模式，其实和NAT关系并不大，只是借用NAT的概念而已（发送到RS的源IP地址还是客户端的IP地址而不是DIP）。

### DR模式

![][7]

DR（Direct Route，直接路由）和NAT模式最大的区别是RS返回数据包不经过Director而是直接返回给用户。用户到达Director后，LVS会修改用户数据包中目标MAC地址为Real Server然后从DIP所在网卡转发出去，RS的返回数据包直接从单独的链路（拓扑图中是SW2<->R1）返回给用户。 所以DR模式中要求

1. Director和RS必须在同一个广播域中，也就是二层可达（实验的拓扑中是通过SW2实现的） ，因为Director要修改目标MAC地址所以数据包只能在广播域内转发；
1. RS必须可以路由到用户，也就是三层可达（实验的拓扑中Director和Real server共享同一个路由），因为Real Server的返回数据包是直接返回给用户的不经过Director；
1. 配置IP

VIP：10.10.10.10， DIP：192.168.122.100 RS1-DIP：192.168.122.101 RS2-DIP：192.168.122.102

* 配置Real Server

    #绑定VIP到本机的环回口  
    ifconfig lo:0 10.10.10.10 netmask 255.255.255.255  broadcast 10.10.10.10 up  
    #禁用ARP响应  
    echo 1 > /proc/sys/net/ipv4/conf/all/arp_ignore  
    echo 1 > /proc/sys/net/ipv4/conf/lo/arp_ignore  
    echo 2 > /proc/sys/net/ipv4/conf/all/arp_announce  
    echo 2 > /proc/sys/net/ipv4/conf/lo/arp_announce

 RS是直接返回数据给用户，所以必须绑定VIP地址；**因为Director和Real Server都绑定了VIP所以RS必须禁用ARP信息否则可能导致用户请求不是发送给Director而是直接到RS，这和LVS的期望是不相符的。**。不同于NAT，在DR模式下RS的网关是指向默认网关的也就是能“返回”数据到客户端的网关（试验中R1充当默认网关）。

* 配置Director

    ipvsadm -A -t 10.10.10.10:80 -s rr       # 添加LVS集群，负载均衡算法为轮询（rr）  
ipvsadm -a -t 10.10.10.10:80 -r 192.168.122.101 -g -w 1   # 添加LVS集群主机（10.10.10.10:80），VS调度模式为DR（-g）及RS权重为1  
ipvsadm -a -t 10.10.10.10:80 -r 192.168.122.102 -g -w 1   # 同上

* 验证

通过client访问10.10.10.10的HTTP服务

![][5]

### DR模式原理解析

* client发送数据包，被路由到director服务器上；
* director的netfilter local_in hook被触发，lvs模块收到该请求
* lvs查询规则库（ipvsadm生成的规则），发现10.10.10.10:80端口被定义为DR模式，执行轮询算法
* IP包处理模块修改数据包，把目标MAC修改为选中的RS的MAC地址，从DIP的所在网卡发送出去（所以源MAC是DIP网卡的MAC）。此时的数据包是： 源MAC地址变成了00:01:3a:4d:5d:00（DIP网卡的MAC地址）源IP地址是172.10.10.10（client的IP地址），目标MAC是00:01:3a:f4:c5:00（RS的MAC）目标IP地址10.10.10.10（VIP） 。通过在RS1上抓包验证这一点


![][8]

 

* RS1收到请求目标MAC和IP都是本机（VIP配置在本机的环回口），所以直接交给Nginx处理
* Nginx的返回数据包交给Linux协议栈，系统发现目标地址是172.10.10.10和自己不在同一个网段，则把数据包交给网关——192.168.122.1。在我们的试验中R1是网关，它是可以直接返回数据给客户端的，所以数据被成功返回到客户端。

注意：在操作系统中（无论是Linux或者Windows）**返回数据的时候是根据IP地址返回的而不是MAC地址**，RS收到数据包**MAC地址是Director的而IP地址则是客户端的**RS如果按MAC地址返回那么数据包就发送到Director了，很显然是不正确的。而LVS的DR模式正是利用了这一点。

### TUN模式

![][9]

 TUN（IP Tunneling，IP通道）是对DR模式的优化。和DR一样，请求数据包经过Director到Real Server返回数据包则是Real Server直接返回客户端。区别是：DR模式要求Director和Real Server在同一个广播域（通过修改目标MAC地址实现数据包转发）而TUN模式则是通过Overlay协议。 Overlay协议就是指把一个IP数据包封装在另一个数据包里面，LVS里面的Overlay协议属于比较原始的实现叫IP-in-IP，目前常见的Overlay协议包括：VxLAN、GRE、STT之类的。 TUN模式要求

1. Director和Real Server必须三层可达，拓扑图中故意加上一个R2用于分割两个广播域；
1. Real Server必须可以路由到用户，也就是三层可达（实验的拓扑中Director和Real server共享同一个路由），因为RS的返回数据包是直接返回给用户的不经过Director；
1. 因为采用Overlay协议，Real Server的MTU值必须设置为1480（1500-20）——即实际上能发送的数据要 加上外层IP头部

TUN模式和DR模式没有本质区别（配置是一摸一样的，只是网络要求不一样），两者都不是特别实用所以本文就不展开介绍了。

## 总结

 LVS的基本原理是利用Linux的netfilter改变数据包的流向以此实现负载均衡。基于性能考虑LVS提供了二种模式，请求和返回数据包都经过Director的NAT模式；请求经过Director返回数据包由Real Server独立返回的是DR和TUN模式（DR和TUN的区别是网络二层可达还是三层可达）。 **DR和TUN理论上可能性能更高一些，但是这种假设的前提是——性能是出现在数据包转发，而以目前软硬件的架构而言数据转发已经不成问题了。**原因有两点：首先Linux引入的NAPI可以平衡网卡中断模式和Polling的性能问题，所以内核本身的转发能力已经不是1998（LVS设计的时间）的情况，一般而言千兆的网络转发是不成问题的；其次大量的“数据平面加速”方案喷涌而出如果真是数据转发的问题我们有智能网卡、DPDK等方案能很好解决。 那么比较实用的只剩下NAT模式了，但是LVS的NAT模式有一个很大的缺陷——Real Server的网关是指向Director。

## LVS NAT的改进

 一般面向外部提供服务的集群环境中网络工程师会给我们一个**外部IP**，它可能是一个公网IP也可能是躲在防火墙后面的“私网IP”。总之只要我们把这个IP地址配置在某个机器上就能正常对外提供服务了。 这种环境中LVS的DR模式、TUN模式显的都比较繁琐（需要满足一定网络条件），所以NAT模式是最合适的，**但是LVS中的NAT要求Real Server必须把网关指向Director**，这就意味着Real Server之前可以三层可达的网络现在全部不行了（比如之前可以通过网关上网，现在则不行了） 回忆一下问题：**当Director发送数据包的时候源地址是客户端IP地址，所以Real Server会把返回数据发送给网关，如果不把Director设置为Real Server的网关那么返回数据就“丢”了。**改进方法也呼之欲出了，让Director发送数据包的时候使用DIP而不是客户端的IP地址就可以了。 按道理说通过LVS+SNAT可以实现，遗憾的是LVS和Iptables是不兼容的，LVS内部处理完数据包后Iptables会忽略这个数据包，所以解决办法只剩下两个：

1. 在用户空间实现一个反向代理，比如Nginx；
1. 修改LVS代码重新编译内核

第一种方法操作非常简单，在Director上安装一个反向代理，LVS配置的VIP和DIP保持一致就可以了，比如：

    ipvsadm -A -t 192.168.122.100:80 -s rr  
    ipvsadm -a -t 192.168.122.100:80 -r 192.168.122.101 -m -w 1   
    ipvsadm -a -t 192.168.122.100:80 -r 192.168.122.102 -m -w 1 

 第二种方法就是阿里后来贡献的FullNat模式。

## 两个疑问

* **为啥LVS不直接做彻底的NAT而直接使用客户端IP地址呢？改进后的NAT怎么规避这个问题？**

这是由于LVS追求的是透明，试想Real Server如何拿到客户端的IP地址？改进后的NAT Real Server只能看到Director的IP地址，客户端的IP地址通过“额外途径”发送。反向代理方案中直接通过应用层的头部（比如HTTP的 x-forwarded-for）;FullNAT方案中则通过TCP的Option带到Real Server。

* **Linux内核为什么不吸纳FullNAT模式？**

是的，FullNAT配置简单速度也不慢所以是非常好的选择。Linux Kernel没有把它合并到内核代码的原因是认为：FullNAT本质上是LVS+SNAT，当我们提到SNAT的时候其实就是在说“用户空间”它不应该属于内核。这是Linux的一大基本原则。https://www.mail-archive.com/lvs-users@linuxvirtualserver.org/msg06046.html 这里你可以看到撕逼过程。

[0]: ##
[1]: ./img/B5n1OeAZlN3nnXJs8tVg.jpeg
[2]: ./img/q3BbIOSDL5Eec9wXnuCow.jpeg
[3]: ./img/mCiabGXsPkOXE4uArlUwg.jpeg
[4]: ./img/PZFwFMhtMzM1d9bykYZWIxg.jpeg
[5]: ./img/UWodbk7TW9lwTmyDmeQGw.jpeg
[6]: ./img/dPEzknlDfCW5WPE6uibrUw.jpeg
[7]: ./img/0VtRuWvic3gjXAAzA73EBA.jpeg
[8]: ./img/ayT0RdY1t3cKGM14lB7cg.jpeg
[9]: ./img/th5exMs046IA8CWibsVEXA.jpeg
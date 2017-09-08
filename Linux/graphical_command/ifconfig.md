 **ifconfig命令** **-->interfaces config的缩写;用来查看和配置网络设备**

 ****

 **【显示网络设备信息】**

 **![][0]**

 ifconfig  
Linux下网卡命名规律：  
eth0，eth1。第一块以太网卡  
lo为环回接口，它的IP地址固定为127.0.0.1，掩码8位。它代表你的机器本身  
  
第一行：连接类型：Ethernet（以太网）HWaddr（硬件mac地址）  
第二行：网卡的IP地址、子网、掩码  
第三行：UP（代表网卡开启状态）  
RUNNING（代表网卡的网线被接上）  
MULTICAST（支持组播）  
MTU:1500（最大传输单元）：1500字节  
第四、五行：接收、发送数据包情况统计  
第七行：接收、发送数据字节数统计信息

 **![][1]**

【**启动关闭指定网卡**】

    * ifconfig eth0 up # 启动网卡eth0
    * ifconfig eth0 down # 关闭网卡eth0

【**配置IPV4地址**】

    * ifconfig eth0 192.168.120.56 # 为网卡eth0配置IP地址:192.168.120.56
    * ifconfig eth0 192.168.120.56 netmask 255.255.255.0 # 配IP，并加上子掩码
    * ifconfig eth0 192.168.120.56 netmask 255.255.255.0 broadcast 192.168.120.255 # 再加上广播包

【**配置IPV6地址**】

    * ifconfig eth0 add 33ffe:3240:800:1005::2/64 # 为网卡eth0配置IPv6地址
    * ifconfig eth0 add 33ffe:3240:800:1005::2/64 # 为网卡eth0删除IPv6地址
    * ifconfig eth0 hw ether 00:AA:BB:CC:DD:EE # 用ifconfig修改MAC地址


【**启用和关闭ARP协议**】

    * ifconfig eth0 arp # 开启网卡eth0的arp协议
    * ifconfig eth0 -arp # 关闭网卡eth0的arp协议

【**设置最大传输单元**】

    * ifconfig eth0 mtu 1500 # 设置能通过的最大数据包大小

【 **备注** 】

**1.** 用ifconfig命令配置的网卡信息，在网卡重启后机器重启后，配置就不存在

**2.** ssh登陆linux服务器操作要小心，关闭了就不能开启了，除非你有多网卡

**3.**service network [start|stop|restart|status] 临时配置，重启network会恢复到原IP

**4.**通过修改下列配置文件/etc/sysconfig/network-scripts/ifcfg-eth[0-9]达到永久修改IP地址

[0]: ./img/20170429173436938.png
[1]: ./img/20170429172750279.png
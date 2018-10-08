# tcpdump命令常用方法 

> 对于经常与网络通信类应用打交道的朋友来说， tcpdump 、 wireshark 、 tshark 等命令应该不会太陌生，我们经常需要这些工具帮我们抓消息包数据进行分析，这样我们可以快速定位网络交互异常原因所在。

## 基础用法

> 可以根据条件设置抓取指定网卡、指定数量的消息，也可以设置过滤条件过滤掉不需要的地址或者端口消息。

    # -s 0 保证抓包不截断，最大消息包长度为65535 ，  
    # -i team0 设置抓取的网卡名 ，  
    # -w 设置输出的文件名 ,  
    # -C 设置100MB一个文件，  
    # -c 设置抓取10000个消息包后就退出  
    # -n 不转换地址为主机名  
    # "port 。。。"设置过滤条件表达式  
  
    # 抓取team0网卡上端口为1081或者1080的消息输出到tcpdump_`date +%Y%m%d%H%M%S`.cap文件中，抓取单个文件为100MB大小，共抓取10000个消息包后结束抓包工作。  
    tcpdump -s 0 -i team0 -w tcpdump_`date +%Y%m%d%H%M%S`.cap -C 100M -c 10000 -n "port 1081 or port 1080"  
      
    ## 抓取所有经过eth1，目的地址是192.168.1.254或192.168.1.200端口是80的TCP数据  
        tcpdump -i eth1 '((tcp) and (port 80) and ((dst host 192.168.1.254) or (dst host 192.168.1.200)))'

## 高级过滤用法

> 掌握精准的过滤方法可以帮助我们快速获取我们要的信息而减少干扰消息数据，减少我们分析的数据量。

协议头信息过滤规则语法:


    # 比较操作有：>, <,>=, <=, =,="" !="</span">  
    proto[x:y]          : 过滤从x字节开始的y字节数。比如ip[2:2]过滤出3、4字节（第一字节从0开始排）  
    proto[x:y] & z = 0  : proto[x:y]和z的与操作为0  
    proto[x:y] & z !=0  : proto[x:y]和z的与操作不为0  
    proto[x:y] & z = z  : proto[x:y]和z的与操作为z  
    proto[x:y] = z      : proto[x:y]等于z

如果使用此过滤规则，首先是我们需要了解协议的报头信息的结构，如UDP、TCP和IP等协议信息头部结构。具体用法看下面的几个示例：


    # 抓取源端口大于1024的TCP数据包  
    tcpdump -i eth1 'tcp[0:2] > 1024'  
      
    # 获取SYN或者SYN/ACK消息包：TCP头14字节中的flag位判断  
    tcpdump -i eth1 'tcp[13] & 2 = 2'  
      
    # 抓取分片的消息包  
    tcpdump -i eth1 'ip[6] = 64'  
    tcpdump -i eth1 'ip[6] & 0x40 != 0'  
      
    # 抓取大于600字节的消息包  
    tcpdump -i eth1 'ip[2:2] > 600'

> 另外，tcpdump命令为了方便我们使用过滤条件，将一些常用的flag设置成了比较友好可读的英文单词组合：


    ## ICMP类型值有：  
    icmp-echoreply, icmp-unreach, icmp-sourcequench, icmp-redirect, icmp-echo, icmp-routeradvert, icmp-routersolicit, icmp-timxceed, icmp-paramprob, icmp-tstamp, icmp-tstampreply, icmp-ireq, icmp-ireqreply, icmp-maskreq, icmp-maskreply  
    ## TCP标记值：  
    tcp-fin, tcp-syn, tcp-rst, tcp-push, tcp-push, tcp-ack, tcp-urg  
      
    ## 抓取SYN/ACK消息包的方法  
    tcpdump -i eth1 'tcp[tcpflags] & tcp-syn != 0 and tcp[tcpflags] & tcp-ack != 0'

## tcpdump命令常用选项

> tcpdump命令有很多参数选项，这里介绍常用的一些，更多的就需要看man文档啦。


      
    ## -t, -tt, -ttt, -tttt and -ttttt参数的使用显示时间信息如下：  
    root@zioer-book:/tmp# tcpdump -s0 -t  
    IP CM-POP11-659.catv.wtnet.de.56138 > zioer-book.14324: UDP, length 20  
    IP zioer-book.52376 > gateway.domain: 2595+ PTR? 150.43.168.192.in-addr.arpa. (45)  
    IP gateway.domain > zioer-book.52376: 2595* 1/0/0 PTR zioer-book. (69)  
      
    root@zioer-book:/tmp# tcpdump -s0 -tt  
    1500368293.036951 IP zioer-book.14324 > ipbcc24e24.dynamic.kabel-deutschland.de.14341: UDP, length 37  
    1500368293.045147 IP 121.32.46.4.9229 > zioer-book.14324: UDP, length 37  
    1500368293.052791 IP zioer-book.43088 > gateway.domain: 33388+ PTR? 150.43.168.192.in-addr.arpa. (45)  
      
    root@zioer-book:/tmp# tcpdump -s0 -ttt  
     00:00:00.000000 IP pool-108-50-214-128.hrbgpa.fios.verizon.net.43003 > zioer-book.14324: UDP, length 725  
     00:00:00.004135 IP host127-15-dynamic.171-212-r.retail.telecomitalia.it.20457 > zioer-book.14324: UDP, length 53  
     00:00:00.000507 IP zioer-book.14324 > broadband-109-173-12-230.moscow.rt.ru.51497: UDP, length 37  
      
      
    root@zioer-book:/tmp# tcpdump -s0 -tttt  
    2017-07-18 16:58:20.627832 IP zioer-book.14324 > 121.32.46.4.9229: UDP, length 32  
      
    root@zioer-book:/tmp# tcpdump -s0 -ttttt  
     00:00:00.000000 IP cm-84.213.19.161.getinternet.no.37522 > zioer-book.14324: UDP, length 132  
     00:00:00.003350 IP zioer-book.44258 > gateway.domain: 54256+ PTR? 150.43.168.192.in-addr.arpa. (45)

## 常用协议头部信息记录

IP协议头部结构：

[![IP协议头部图片](/images/tech/IP_head.png)](http://www.zioer.org/images/tech/IP_head.png "IP协议头部图片") IP协议头部图片

TCP头部信息：

[![TCP协议头部图片](/images/tech/TCP_head.png)](http://www.zioer.org/images/tech/TCP_head.png "TCP协议头部图片") TCP协议头部图片

UDP头部信息：


    0      7 8     15 16    23 24    31  
    +--------+--------+--------+--------+  
    |     Source      |   Destination   |  
    |      Port       |      Port       |  
    +--------+--------+--------+--------+  
    |                 |                 |  
    |     Length      |    Checksum     |  
    +--------+--------+--------+--------+  
    |                                   |  
    |              DATA ...             |  
    +-----------------------------------+

## 阅读更多

* [tcpdump官方文档][0]
* 想了解更多tcpdump的过滤规则可以查看pcap-filter的man文档，可以获取到更多的示例。

[0]: http://www.tcpdump.org/tcpdump_man.html
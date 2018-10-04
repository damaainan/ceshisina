## 聊聊tcpdump与Wireshark抓包分析

2016.05.25 11:44*

来源：[https://www.jianshu.com/p/a62ed1bb5b20](https://www.jianshu.com/p/a62ed1bb5b20)


          
## 1 起因#

前段时间，一直在调线上的一个问题：线上应用接受POST请求，请求body中的参数获取不全，存在丢失的状况。这个问题是偶发性的，大概发生的几率为5%-10%左右，这个概率已经相当高了。在排查问题的过程中使用到了tcpdump和Wireshark进行抓包分析。感觉这两个工具搭配起来干活，非常完美。所有的网络传输在这两个工具搭配下，都无处遁形。

为了更好、更顺手地能够用好这两个工具，特整理本篇文章，希望也能给大家带来收获。为大家之后排查问题，添一利器。
## 2 tcpdump与Wireshark介绍#

在网络问题的调试中，tcpdump应该说是一个必不可少的工具，和大部分linux下优秀工具一样，它的特点就是简单而强大。`它是基于Unix系统的命令行式的数据包嗅探工具，可以抓取流动在网卡上的数据包`。
`默认情况下，tcpdump不会抓取本机内部通讯的报文`。根据网络协议栈的规定，对于报文，即使是目的地是本机，也需要经过本机的网络协议层，所以本机通讯肯定是通过API进入了内核，并且完成了路由选择。【比如本机的TCP通信，也必须要socket通信的基本要素：src ip port  dst ip port】

如果要使用tcpdump抓取其他主机MAC地址的数据包，必须开启网卡混杂模式，`所谓混杂模式，用最简单的语言就是让网卡抓取任何经过它的数据包，不管这个数据包是不是发给它或者是它发出的`。一般而言，Unix不会让普通用户设置混杂模式，因为这样可以看到别人的信息，比如telnet的用户名和密码，这样会引起一些安全上的问题，所以只有root用户可以开启混杂模式，`开启混杂模式的命令是：ifconfig en0 promisc, en0是你要打开混杂模式的网卡`。
 **`Linux抓包原理：`** 

Linux抓包是通过注册一种虚拟的底层网络协议来完成对网络报文(准确的说是网络设备)消息的处理权。当网卡接收到一个网络报文之后，它会遍历系统中所有已经注册的网络协议，例如以太网协议、x25协议处理模块来尝试进行报文的解析处理，这一点和一些文件系统的挂载相似，就是让系统中所有的已经注册的文件系统来进行尝试挂载，如果哪一个认为自己可以处理，那么就完成挂载。

当抓包模块把自己伪装成一个网络协议的时候，系统在收到报文的时候就会给这个伪协议一次机会，让它来对网卡收到的报文进行一次处理，此时该模块就会趁机对报文进行窥探，也就是把这个报文完完整整的复制一份，假装是自己接收到的报文，汇报给抓包模块。

Wireshark是一个网络协议检测工具，支持Windows平台、Unix平台、Mac平台，一般只在图形界面平台下使用Wireshark，如果是Linux的话，直接使用tcpdump了，因为一般而言Linux都自带的tcpdump，或者用tcpdump抓包以后用Wireshark打开分析。

在Mac平台下，Wireshark通过WinPcap进行抓包，封装的很好，使用起来很方便，可以很容易的制定抓包过滤器或者显示过滤器，具体简单使用下面会介绍。[Wireshark][6]是一个免费的工具，只要google一下就能很容易找到下载的地方。

所以，tcpdump是用来抓取数据非常方便，Wireshark则是用于分析抓取到的数据比较方便。
## 3 tcpdump使用#
## 3.1 语法##

```
tcpdump [ -AdDefIKlLnNOpqRStuUvxX ] [ -B buffer_size ] [ -c count ]
        [ -C file_size ] [ -G rotate_seconds ] [ -F file ]
        [ -i interface ] [ -m module ] [ -M secret ]
        [ -r file ] [ -s snaplen ] [ -T type ] [ -w file ]
        [ -W filecount ]
        [ -E spi@ipaddr algo:secret,...  ]
        [ -y datalinktype ] [ -z postrotate-command ] [ -Z user ]
        [ expression ]

```


* **`类型的关键字`** 


host(缺省类型): 指明一台主机，如：host 210.27.48.2

net: 指明一个网络地址，如：net 202.0.0.0

port: 指明端口号，如：port 23


* **`确定方向的关键字`** 


src: src 210.27.48.2, IP包源地址是210.27.48.2

dst: dst net 202.0.0.0, 目标网络地址是202.0.0.0

dst or src(缺省值)

dst and src


* **`协议的关键字：缺省值是监听所有协议的信息包`** 


fddi

ip

arp

rarp

tcp

udp


* **`其他关键字`** 


gateway

broadcast

less

greater


* **`常用表达式：多条件时可以用括号，但是要用\转义`** 


非 : ! or "not" (去掉双引号)

且 : && or "and"

或 : || or "or"

## 3.2 选项##

```sh
-A：以ASCII编码打印每个报文（不包括链路层的头），这对分析网页来说很方便；
-a：将网络地址和广播地址转变成名字； 
-c<数据包数目>：在收到指定的包的数目后，tcpdump就会停止；
-C：用于判断用 -w 选项将报文写入的文件的大小是否超过这个值，如果超过了就新建文件（文件名后缀是1、2、3依次增加）；
-d：将匹配信息包的代码以人们能够理解的汇编格式给出； 
-dd：将匹配信息包的代码以c语言程序段的格式给出； 
-ddd：将匹配信息包的代码以十进制的形式给出；
-D：列出当前主机的所有网卡编号和名称，可以用于选项 -i；
-e：在输出行打印出数据链路层的头部信息； 
-f：将外部的Internet地址以数字的形式打印出来； 
-F<表达文件>：从指定的文件中读取表达式,忽略其它的表达式； 
-i<网络界面>：监听主机的该网卡上的数据流，如果没有指定，就会使用最小网卡编号的网卡（在选项-D可知道，但是不包括环路接口），linux 2.2 内核及之后的版本支持 any 网卡，用于指代任意网卡； 
-l：如果没有使用 -w 选项，就可以将报文打印到 标准输出终端（此时这是默认）； 
-n：显示ip，而不是主机名； 
-N：不列出域名； 
-O：不将数据包编码最佳化； 
-p：不让网络界面进入混杂模式； 
-q：快速输出，仅列出少数的传输协议信息； 
-r<数据包文件>：从指定的文件中读取包(这些包一般通过-w选项产生)； 
-s<数据包大小>：指定抓包显示一行的宽度，-s0表示可按包长显示完整的包，经常和-A一起用，默认截取长度为60个字节，但一般ethernet MTU都是1500字节。所以，要抓取大于60字节的包时，使用默认参数就会导致包数据丢失； 
-S：用绝对而非相对数值列出TCP关联数； 
-t：在输出的每一行不打印时间戳； 
-tt：在输出的每一行显示未经格式化的时间戳记； 
-T<数据包类型>：将监听到的包直接解释为指定的类型的报文，常见的类型有rpc （远程过程调用）和snmp（简单网络管理协议）； 
-v：输出一个稍微详细的信息，例如在ip包中可以包括ttl和服务类型的信息； 
-vv：输出详细的报文信息； 
-x/-xx/-X/-XX：以十六进制显示包内容，几个选项只有细微的差别，详见man手册； 
-w<数据包文件>：直接将包写入文件中，并不分析和打印出来；
expression：用于筛选的逻辑表达式；

```
## 3.3 命令实践##


* 直接启动tcpdump，将抓取所有经过第一个网络接口上的数据包


```sh
tcpdump

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump
Password:
tcpdump: data link type PKTAP
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on pktap, link-type PKTAP (Packet Tap), capture size 262144 bytes
11:00:19.788139 IP 10.37.63.3.50809 > 10.37.253.32.socks: Flags [.], ack 151417909, win 4096, length 0
11:00:19.790267 IP 10.37.253.32.socks > 10.37.63.3.50809: Flags [.], ack 1, win 560, options [nop,nop,TS val 1323324836 ecr 501713973], length 0
11:00:19.851362 IP 10.37.63.53.57443 > 239.255.255.250.ssdp: UDP, length 133
11:00:19.851367 IP 10.37.63.107.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:00:19.851369 IP 10.37.63.138.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:00:20.060087 IP 10.37.63.71.54616 > 239.255.255.250.ssdp: UDP, length 133

```


* 抓取所有经过指定网络接口上的数据包


```sh
tcpdump -i en0

```

如果不指定网卡，默认tcpdump只会监视第一个网络接口，一般是eth0，下面的例子都没有指定网络接口。
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
11:04:31.780759 IP 10.37.63.100.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST

```


* 抓取所有经过 en0，目的或源地址是 10.37.63.255 的网络数据：


```sh
tcpdump -i en0 host 10.37.63.255

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump host 10.37.63.255
tcpdump: data link type PKTAP
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on pktap, link-type PKTAP (Packet Tap), capture size 262144 bytes
11:07:23.807683 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:23.913143 IP 10.37.63.95.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:24.538785 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:24.643311 IP 10.37.63.95.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:24.747672 IP 10.37.63.87.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): REGISTRATION; REQUEST; BROADCAST
11:07:25.374527 IP 10.37.63.95.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:26.209995 IP 10.37.63.86.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:07:26.210530 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST

```


* 抓取主机10.37.63.255和主机10.37.63.61或10.37.63.95的通信：


```sh
tcpdump host 10.37.63.255 and \(10.37.63.61 or 10.37.63.95 \)

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump host 10.37.63.255 and \(10.37.63.61 or 10.37.63.95 \)
tcpdump: data link type PKTAP
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on pktap, link-type PKTAP (Packet Tap), capture size 262144 bytes
11:10:38.395320 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:39.234047 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:39.962286 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:48.422443 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:49.153630 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:49.894146 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
11:10:52.600297 IP 10.37.63.61.netbios-ns > 10.37.63.255.netbios-ns: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST

```


* 抓取主机192.168.13.210除了和主机10.37.63.61之外所有主机通信的数据包：


```sh
tcpdump -n host 10.37.63.255 and ! 10.37.63.61

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -n host 10.37.63.255 and ! 10.37.63.61
tcpdump: data link type PKTAP
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on pktap, link-type PKTAP (Packet Tap), capture size 262144 bytes
15:54:33.921068 IP 10.37.63.86.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:34.025490 IP 10.37.63.86.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:34.025492 IP 10.37.63.86.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:34.338753 IP 10.37.63.56.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:35.174516 IP 10.37.63.88.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:35.204268 IP 10.37.63.56.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
15:54:35.592199 IP 10.37.63.135.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST

```


* 抓取主机10.37.63.255除了和主机10.37.63.61之外所有主机通信的ip包


```sh
tcpdump ip -n host 10.37.63.255 and ! 10.37.63.61

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump ip -n host 10.37.63.255 and ! 10.37.63.61
Password:
tcpdump: data link type PKTAP
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on pktap, link-type PKTAP (Packet Tap), capture size 262144 bytes
16:02:48.168264 IP 10.37.63.107.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.272626 IP 10.37.63.28.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.586137 IP 10.37.63.75.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.586140 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.586201 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.586202 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:48.690751 IP 10.37.63.103.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:49.004792 IP 10.37.63.28.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:49.212622 IP 10.37.63.88.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:49.317969 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:49.317972 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST
16:02:49.318301 IP 10.37.63.48.137 > 10.37.63.255.137: NBT UDP PACKET(137): QUERY; REQUEST; BROADCAST

```


* 抓取主机10.37.63.3发送的所有数据：


```sh
tcpdump -i en0 src host 10.37.63.3 （注意数据流向）

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 src host 10.37.63.3
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:08:05.698674 IP 10.37.63.3.51503 > 101.201.169.146.https: Flags [.], ack 3067697680, win 4096, length 0
16:08:06.225543 IP 10.37.63.3.56531 > 10.37.253.51.domain: 49330+ PTR? 3.63.37.10.in-addr.arpa. (41)
16:08:06.228851 IP 10.37.63.3.56781 > 10.37.253.51.domain: 9247+ PTR? 146.169.201.101.in-addr.arpa. (46)
16:08:07.247441 IP 10.37.63.3.53716 > 10.37.253.51.domain: 60009+ PTR? 51.253.37.10.in-addr.arpa. (43)
16:08:08.198285 IP 10.37.63.3.newoak > 123.151.13.85.irdmi: UDP, length 47
16:08:08.254488 IP 10.37.63.3.51134 > 10.37.253.51.domain: 52763+ PTR? 85.13.151.123.in-addr.arpa. (44)
16:08:08.917142 IP 10.37.63.3.51815 > 106.11.4.88.https: Flags [P.], seq 341932595:341932930, ack 4196579612, win 65535, length 335
16:08:08.918050 IP 10.37.63.3.51815 > 106.11.4.88.https: Flags [P.], seq 335:804, ack 1, win 65535, length 469
16:08:08.984637 IP 10.37.63.3.51815 > 106.11.4.88.https: Flags [.], ack 292, win 65535, length 0

```


* 抓取主机10.37.63.3接收的所有数据：


```sh
tcpdump -i en0 dst host 10.37.63.3 （注意数据流向） 

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 dst host 10.37.63.3
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:10:00.120346 IP 123.151.13.85.irdmi > 10.37.63.3.newoak: UDP, length 47
16:10:00.447742 IP 106.11.4.88.https > 10.37.63.3.51840: Flags [.], ack 3563461726, win 62712, length 0
16:10:00.449252 IP 106.11.4.88.https > 10.37.63.3.51840: Flags [P.], seq 0:291, ack 1, win 62712, length 291
16:10:00.590941 IP 10.37.253.51.domain > 10.37.63.3.62089: 38134 NXDomain 0/1/0 (101)
16:10:00.593145 IP 10.37.253.51.domain > 10.37.63.3.56987: 19136 NXDomain* 0/0/0 (41)
16:10:01.598164 IP 10.37.253.51.domain > 10.37.63.3.63380: 43688 NXDomain* 0/0/0 (43)
16:10:03.194440 IP 123.151.13.85.irdmi > 10.37.63.3.newoak: UDP, length 79
16:10:03.880803 IP 106.11.4.88.https > 10.37.63.3.51840: Flags [.], ack 806, win 63784, length 0
16:10:03.883452 IP 106.11.4.88.https > 10.37.63.3.51840: Flags [P.], seq 291:582, ack 806, win 63784, length 291
16:10:04.051402 IP dns15.online.tj.cn.irdmi > 10.37.63.3.terabase: UDP, length 87

```


* 抓取主机10.37.63.3所有在TCP 80端口的数据包：


```sh
tcpdump -i en0 host 10.37.63.3 and tcp port 80

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 host 10.37.63.3 and tcp port 80
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:13:34.869399 IP 10.37.63.3.51843 > cncln.online.ln.cn.http: Flags [.], ack 3148173637, win 8192, length 0
16:13:34.890175 IP cncln.online.ln.cn.http > 10.37.63.3.51843: Flags [.], ack 1, win 31, length 0
16:13:49.497784 IP 10.37.63.3.51845 > 27.221.81.19.http: Flags [.], ack 3932049450, win 4096, length 0
16:13:49.497786 IP 10.37.63.3.51844 > 27.221.81.19.http: Flags [.], ack 3635221024, win 4096, length 0
16:13:49.513952 IP 27.221.81.19.http > 10.37.63.3.51845: Flags [.], ack 1, win 122, options [nop,nop,TS val 4035158002 ecr 876369829], length 0
16:13:49.518587 IP 27.221.81.19.http > 10.37.63.3.51844: Flags [.], ack 1, win 122, options [nop,nop,TS val 4035158002 ecr 876369829], length 0

```


* 抓取HTTP主机10.37.63.3在80端口接收到的数据包：


```sh
tcpdump -i en0 host 10.37.63.3 and dst port 80

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 host 10.37.63.3 and dst port 80
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:19:36.187617 IP 10.37.63.3.51901 > 180.149.132.47.http: Flags [P.], seq 219000907:219001688, ack 4212585623, win 8192, length 781: HTTP: GET / HTTP/1.1
16:19:36.194163 IP 10.37.63.3.51901 > 180.149.132.47.http: Flags [.], ack 292, win 8182, length 0
16:19:36.194292 IP 10.37.63.3.51901 > 180.149.132.47.http: Flags [.], ack 453, win 8186, length 0

```


* 抓取所有经过 en0，目的或源端口是 25 的网络数据


```sh
tcpdump -i en0 port 25

# 源端口
tcpdump -i en0 src port 25
# 目的端口
tcpdump -i en0 dst port 25网络过滤

```


* 抓取所有经过 en0，网络是 192.168上的数据包


```sh
tcpdump -i en0 net 192.168
tcpdump -i en0 src net 192.168
tcpdump -i en0 dst net 192.168
tcpdump -i en0 net 192.168.1
tcpdump -i en0 net 192.168.1.0/24

```


* 协议过滤


```sh
tcpdump -i en0 arp
tcpdump -i en0 ip
tcpdump -i en0 tcp
tcpdump -i en0 udp
tcpdump -i en0 icmp

```


* 抓取所有经过 en0，目的地址是 192.168.1.254 或 192.168.1.200 端口是 80 的 TCP 数据


```sh
tcpdump -i en0 '((tcp) and (port 80) and ((dst host 192.168.1.254) or (dst host 192.168.1.200)))'

```


* 抓取所有经过 en0，目标 MAC 地址是 00:01:02:03:04:05 的 ICMP 数据


```sh
tcpdump -i eth1 '((icmp) and ((ether dst host 00:01:02:03:04:05)))'

```


* 抓取所有经过 en0，目的网络是 192.168，但目的主机不是 192.168.1.200 的 TCP 数据


```sh
tcpdump -i en0 '((tcp) and ((dst net 192.168) and (not dst host 192.168.1.200)))'

```


* 只抓 SYN 包


```sh
tcpdump -i en0 'tcp[tcpflags] = tcp-syn'

```


* 抓 SYN, ACK


```sh
tcpdump -i en0 'tcp[tcpflags] & tcp-syn != 0 and tcp[tcpflags] & tcp-ack != 0'

```


* 抓 SMTP 数据，抓取数据区开始为"MAIL"的包，"MAIL"的十六进制为 0x4d41494c


```sh
tcpdump -i en0 '((port 25) and (tcp[(tcp[12]>>2):4] = 0x4d41494c))'

```


* 抓 HTTP GET 数据，"GET "的十六进制是 0x47455420


```sh
tcpdump -i en0 'tcp[(tcp[12]>>2):4] = 0x47455420'

# 0x4745 为"GET"前两个字母"GE",0x4854 为"HTTP"前两个字母"HT"
tcpdump  -XvvennSs 0 -i en0 tcp[20:2]=0x4745 or tcp[20:2]=0x4854

```


* 抓 SSH 返回，"SSH-"的十六进制是 0x5353482D


```sh
tcpdump -i en0 'tcp[(tcp[12]>>2):4] = 0x5353482D'

# 抓老版本的 SSH 返回信息，如"SSH-1.99.."
tcpdump -i en0 '(tcp[(tcp[12]>>2):4] = 0x5353482D) and (tcp[((tcp[12]>>2)+4):2] = 0x312E)' 

```


* 高级包头过滤

 **`如前两个的包头过滤，首先了解如何从包头过滤信息：`** 

```sh
proto[x:y]          : 过滤从x字节开始的y字节数。比如ip[2:2]过滤出3、4字节（第一字节从0开始排）
proto[x:y] & z = 0  : proto[x:y]和z的与操作为0
proto[x:y] & z !=0  : proto[x:y]和z的与操作不为0
proto[x:y] & z = z  : proto[x:y]和z的与操作为z
proto[x:y] = z      : proto[x:y]等于z

```

操作符 : >, <, >=, <=, =, !=

 **`抓取端口大于1024的TCP数据包：`** 

```sh
tcpdump -i en0 'tcp[0:2] > 1024'

```


* 抓 DNS 请求数据


```sh
tcpdump -i en0 udp dst port 53

```


* 其他


-c 参数对于运维人员来说也比较常用，因为流量比较大的服务器，靠人工 CTRL+C 还是抓的太多，于是可以用-c 参数指定抓多少个包。

```sh
time tcpdump -nn -i en0 'tcp[tcpflags] = tcp-syn' -c 10000 > /dev/null

```

上面的命令计算抓 10000 个 SYN 包花费多少时间，可以判断访问量大概是多少。

实时抓取端口号8000的GET包，然后写入GET.log

```sh
tcpdump -i en0 '((port 8000) and (tcp[(tcp[12]>>2):4]=0x47455420))' -nnAl -w /tmp/GET.log

```
## 3.4 抓个网站练练##

想抓取访问某个网站时的网络数据。比如网站 [http://www.baidu.com/][7] 怎么做？


* 通过tcpdump截获主机[www.baidu.com][8]发送与接收所有的数据包


```sh
tcpdump -i en0 host www.baidu.com

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 host www.baidu.com
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes

```


* 访问这个网站


```sh
wget www.baidu.cn

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -i en0 host www.baidu.com
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:43:08.444405 IP 10.37.63.3.52302 > 61.135.169.121.http: Flags [S], seq 3066364056, win 65535, options [mss 1460,nop,wscale 5,nop,nop,TS val 878169772 ecr 0,sackOK,eol], length 0
16:43:08.446470 IP 61.135.169.121.http > 10.37.63.3.52302: Flags [S.], seq 3537377541, ack 3066364057, win 65535, options [mss 1440,nop,wscale 7,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,sackOK,eol], length 0
16:43:08.446517 IP 10.37.63.3.52302 > 61.135.169.121.http: Flags [.], ack 1, win 8192, length 0
16:43:08.446553 IP 10.37.63.3.52302 > 61.135.169.121.http: Flags [P.], seq 1:142, ack 1, win 8192, length 141: HTTP: GET / HTTP/1.1
16:43:08.450529 IP 61.135.169.121.http > 10.37.63.3.52302: Flags [.], ack 142, win 202, length 0
16:43:08.451264 IP 61.135.169.121.http > 10.37.63.3.52302: Flags [P.], seq 1:962, ack 142, win 202, length 961: HTTP: HTTP/1.1 200 OK
16:43:08.451270 IP 61.135.169.121.http > 10.37.63.3.52302: Flags [.], seq 962:2402, ack 142, win 202, length 1440: HTTP
16:43:08.451318 IP 61.135.169.121.http > 10.37.63.3.52302: Flags [.], seq 2402:3842, ack 142, win 202, length 1440: HTTP

```

确认序列号ack为何是1。这是相对值，如何显示绝对值

```sh
tcpdump -S -i en0 host www.baidu.com

```

再次访问这个网站

```sh
wget www.baidu.com

```
 **`控制台输出：`** 

```sh
taomingkais-MacBook-Pro:~ TaoBangren$ sudo tcpdump -S -i en0 host www.baidu.com
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on en0, link-type EN10MB (Ethernet), capture size 262144 bytes
16:50:11.911342 IP 10.37.63.3.52346 > 61.135.169.121.http: Flags [S], seq 1888894292, win 65535, options [mss 1460,nop,wscale 5,nop,nop,TS val 878592161 ecr 0,sackOK,eol], length 0
16:50:11.916158 IP 61.135.169.121.http > 10.37.63.3.52346: Flags [S.], seq 2526934941, ack 1888894293, win 65535, options [mss 1440,nop,wscale 7,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,nop,sackOK,eol], length 0
16:50:11.916208 IP 10.37.63.3.52346 > 61.135.169.121.http: Flags [.], ack 2526934942, win 8192, length 0
16:50:11.916308 IP 10.37.63.3.52346 > 61.135.169.121.http: Flags [P.], seq 1888894293:1888894434, ack 2526934942, win 8192, length 141: HTTP: GET / HTTP/1.1
16:50:11.919124 IP 61.135.169.121.http > 10.37.63.3.52346: Flags [.], ack 1888894434, win 202, length 0
16:50:11.922055 IP 61.135.169.121.http > 10.37.63.3.52346: Flags [P.], seq 2526934942:2526935943, ack 1888894434, win 202, length 1001: HTTP: HTTP/1.1 200 OK
16:50:11.922060 IP 61.135.169.121.http > 10.37.63.3.52346: Flags [.], seq 2526935943:2526937383, ack 1888894434, win 202, length 1440: HTTP
16:50:11.922115 IP 61.135.169.121.http > 10.37.63.3.52346: Flags [.], seq 2526937383:2526938823, ack 1888894434, win 202, length 1440: HTTP

```


* 想要看到详细的http报文。怎么做？


```sh
tcpdump -A -i en0 host www.baidu.com

```

将抓取的结果存到文件，比如文件 file1

```sh
tcpdump -A -i en0 -w file1 host www.baidu.com

```

如何读取这个文件的基本信息

```sh
tcpdump -r file1

```

想要了解更多，比如上面的http报文

```sh
tcpdump -A -r file1

```

也同时想要将确认序列号ack打印成绝对值

```sh
tcpdump -AS -r file1

```

注：

无参数的选项比如 -A, -S, -e, 等。均可以共用一个减号

'src host [www.baidu.cn'][9] 属于 expression ，如果太长，可以用单引号括起来：

```sh
tcpdump -i en0 'src host www.baidu.com'

```


* 分析抓取到的报文


```sh
16:50:11.916308 IP 10.37.63.3.52346 > 61.135.169.121.http: Flags [P.], seq 1888894293:1888894434, ack 2526934942, win 8192, length 141: HTTP: GET / HTTP/1.1

```

第一列是时间戳：时、分、秒、微秒

第二列是网际网路协议的名称

第三列是报文发送方的十进制的网际网路协议地址，以及紧跟其后的端口号（偶尔会是某个协议名如 http ，如果在此处仍然显示端口号加上 -n 选项）

第四列是大于号

第五列是报文接收方的十进制的网际网路协议地址，以及紧跟其后的端口号（偶尔会是某个协议名如 http ，如果在此处仍然显示端口号加上 -n 选项）

第六列是冒号

第七列是 Flags 标识，可能的取值是 [S.] [.] [P.] [F.]

第八、九、十……列 是tcp协议报文头的一些变量值：

```sh
seq 是 请求同步的 序列号

ack 是 已经同步的 序列号

win 是 当前可用窗口大小

length 是 tcp协议报文体的长度

如果加入了-S选项，会看到的 seq, ack 是 两个冒号分割的值，分别表示变更前、后的值。

```

## 4 tcpdump抓取TCP包分析#

TCP传输控制协议是面向连接的可靠的传输层协议，在进行数据传输之前，需要在传输数据的两端（客户端和服务器端）创建一个连接，这个连接由一对插口地址唯一标识，`即是在IP报文首部的源IP地址、目的IP地址，以及TCP数据报首部的源端口地址和目的端口地址`。TCP首部结构如下：


![][0]


注意：通常情况下，一个正常的TCP连接，都会有三个阶段:1、TCP三次握手;2、数据传送;3、TCP四次挥手

 **`其中在TCP连接和断开连接过程中的关键部分如下：`** 


* 源端口号：即发送方的端口号，在TCP连接过程中，对于客户端，端口号往往由内核分配，无需进程指定；

* 目的端口号：即发送目的的端口号；

* 序号：即为发送的数据段首个字节的序号；

* 确认序号：在收到对方发来的数据报，发送确认时期待对方下一次发送的数据序号；

* SYN：同步序列编号，Synchronize Sequence Numbers；

* ACK：确认编号，Acknowledgement Number；

* FIN：结束标志，FINish；


## 4.1 TCP三次握手##
 **`三次握手的过程如下：`** 


![][1]


step1. 由客户端向服务器端发起TCP连接请求。Client发送：`同步序列编号SYN置为1，发送序号Seq为一个随机数，这里假设为X，确认序号ACK置为0`；

step2. 服务器端接收到连接请求。Server响应：`同步序列编号SYN置为1，并将确认序号ACK置为X+1，然后生成一个随机数Y作为发送序号Seq（因为所确认的数据报的确认序号未初始化）`；

step3. 客户端对接收到的确认进行确认。Client发送：`将确认序号ACK置为Y+1，然后将发送序号Seq置为X+1（即为接收到的数据报的确认序号）`；


* **`为什么是三次握手而不是两次`** 


对于step3的作用，假设一种情况，`客户端A向服务器B发送一个连接请求数据报，然后这个数据报在网络中滞留导致其迟到了`，虽然迟到了，但是服务器仍然会接收并发回一个确认数据报。`但是A却因为久久收不到B的确认而将发送的请求连接置为失效，等到一段时间后，接到B发送过来的确认`，A认为自己现在没有发送连接，而B却一直以为连接成功了，于是一直在等待A的动作，而A将不会有任何的动作了。`这会导致服务器资源白白浪费掉了`，因此，两次握手是不行的，`因此需要再加上一次，对B发过来的确认再进行一次确认，即确认这次连接是有效的，从而建立连接`。


* **`对于双方，发送序号的初始化为何值`** 


有的系统中是显式的初始化序号是0，但是这种已知的初始化值是非常危险的，`因为这会使得一些黑客钻漏洞，发送一些数据报来破坏连接`。因此，初始化序号因为取随机数会更好一些，并且是越随机越安全。
 **`tcpdump抓TCP三次握手抓包分析：`** 

```sh
sudo tcpdump -n -S -i lo0 host 10.37.63.3 and tcp port 8080

# 接着再运行：
curl http://10.37.63.3:8080/atbg/doc

```
 **`控制台输出：`** 

```sh
# TCP三次握手 start
16:00:13.486776 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [S], seq 1944916150, win 65535, options [mss 16344,nop,wscale 5,nop,nop,TS val 906474698 ecr 0,sackOK,eol], length 0
16:00:13.486850 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [S.], seq 1119565918, ack 1944916151, win 65535, options [mss 16344,nop,wscale 5,nop,nop,TS val 906474698 ecr 906474698,sackOK,eol], length 0
16:00:13.486860 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [.], ack 1119565919, win 12759, options [nop,nop,TS val 906474698 ecr 906474698], length 0
16:00:13.486868 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [.], ack 1944916151, win 12759, options [nop,nop,TS val 906474698 ecr 906474698], length 0
# TCP三次握手 end

# 传输数据 start
16:00:13.486923 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [P.], seq 1944916151:1944916238, ack 1119565919, win 12759, options [nop,nop,TS val 906474698 ecr 906474698], length 87: HTTP: GET /atbg/doc HTTP/1.1
16:00:13.486944 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [.], ack 1944916238, win 12756, options [nop,nop,TS val 906474698 ecr 906474698], length 0
16:00:13.489750 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [P.], seq 1119565919:1119571913, ack 1944916238, win 12756, options [nop,nop,TS val 906474701 ecr 906474698], length 5994: HTTP: HTTP/1.1 200 OK
16:00:13.489784 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [.], ack 1119571913, win 12572, options [nop,nop,TS val 906474701 ecr 906474701], length 0
# 传输数据 end

# TCP四次挥手 start
16:00:13.490836 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [F.], seq 1944916238, ack 1119571913, win 12572, options [nop,nop,TS val 906474702 ecr 906474701], length 0
16:00:13.490869 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [.], ack 1944916239, win 12756, options [nop,nop,TS val 906474702 ecr 906474702], length 0
16:00:13.490875 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [.], ack 1119571913, win 12572, options [nop,nop,TS val 906474702 ecr 906474702], length 0
16:00:13.491004 IP 10.37.63.3.8080 > 10.37.63.3.61725: Flags [F.], seq 1119571913, ack 1944916239, win 12756, options [nop,nop,TS val 906474702 ecr 906474702], length 0
16:00:13.491081 IP 10.37.63.3.61725 > 10.37.63.3.8080: Flags [.], ack 1119571914, win 12572, options [nop,nop,TS val 906474702 ecr 906474702], length 0
# TCP四次挥手 end

```

每一行中间都有这个包所携带的标志：

S=SYN，发起连接标志。

P=PUSH，传送数据标志。

F=FIN，关闭连接标志。

ack，表示确认包。

RST=RESET，异常关闭连接。

.，表示没有任何标志。

 **`第1行：16:00:13.486776`** ，从10.37.63.3（client）的临时端口61725向10.37.63.3（server）的8080监听端口发起连接，client初始包序号seq为1944916150，滑动窗口大小为65535字节（滑动窗口即tcp接收缓冲区的大小，用于tcp拥塞控制），mss大小为16344（即可接收的最大包长度，通常为MTU减40字节，IP头和TCP头各20字节）。【seq=1944916150，ack=0，syn=1】
 **`第2行：16:00:13.486850`** ，server响应连接，同时带上第一个包的ack信息，为client端的初始包序号seq加1，即1944916151，即server端下次等待接受这个包序号的包，用于tcp字节流的顺序控制。Server端的初始包序号seq为1119565918，mss也是16344。【seq=1119565918，ack=1944916151，syn=1】
 **`第3行：15:46:13.084161`** ，client再次发送确认连接，tcp连接三次握手完成，等待传输数据包。【ack=1119565919，seq=1944916151】
## 4.2 TCP四次挥手##

连接双方在完成数据传输之后就需要断开连接。`由于TCP连接是属于全双工的，即连接双方可以在一条TCP连接上互相传输数据，因此在断开时存在一个半关闭状态，即有有一方失去发送数据的能力，却还能接收数据`。因此，断开连接需要分为四次。主要过程如下：


![][2]


step1. 主机A向主机B发起断开连接请求，之后主机A进入FIN-WAIT-1状态；

step2. 主机B收到主机A的请求后，向主机A发回确认，然后进入CLOSE-WAIT状态；

step3. 主机A收到B的确认之后，进入FIN-WAIT-2状态，此时便是半关闭状态，即主机A失去发送能力，但是主机B却还能向A发送数据，并且A可以接收数据。此时主机B占主导位置了，如果需要继续关闭则需要主机B来操作了；

step4. 主机B向A发出断开连接请求，然后进入LAST-ACK状态；

step5. 主机A接收到请求后发送确认，进入TIME-WAIT状态，等待2MSL之后进入CLOSED状态，而主机B则在接受到确认后进入CLOSED状态；


* **`为何主机A在发送了最后的确认后没有进入CLOSED状态，反而进入了一个等待2MSL的TIME-WAIT`** 


主要作用有两个：

第一，确保主机A最后发送的确认能够到达主机B。如果处于LAST-ACK状态的主机B一直收不到来自主机A的确认，它会重传断开连接请求，然后主机A就可以有足够的时间去再次发送确认。但是这也只能尽最大力量来确保能够正常断开，如果主机A的确认总是在网络中滞留失效，从而超过了2MSL，最后也无法正常断开；

第二，如果主机A在发送了确认之后立即进入CLOSED状态。假设之后主机A再次向主机B发送一条连接请求，而这条连接请求比之前的确认报文更早地到达主机B，则会使得主机B以为这条连接请求是在旧的连接中A发出的报文，并不看成是一条新的连接请求了，即使得这个连接请求失效了，增加2MSL的时间可以使得这个失效的连接请求报文作废，这样才不影响下次新的连接请求中出现失效的连接请求。


* **`为什么断开连接请求报文只有三个，而不是四个`** 


因为在TCP连接过程中，确认的发送有一个延时（即经受延时的确认），一端在发送确认的时候将等待一段时间，如果自己在这段事件内也有数据要发送，就跟确认一起发送，如果没有，则确认单独发送。而我们的抓包实验中，由服务器端先断开连接，`之后客户端在确认的延迟时间内，也有请求断开连接需要发送`，于是就与上次确认一起发送，因此就只有三个数据报了。
## 5 Wireshark分析tcpdump抓包结果#


* **`启动8080端口，tcpdump抓包命令如下：`** 


```sh
tcpdump -i lo0 -s 0 -n -S host 10.37.63.3 and port 8080 -w ./Desktop/tcpdump_10.37.63.3_8080_20160525.cap

# 然后再执行curl
curl http://10.37.63.3:8080/atbg/doc

```


* **`使用Wireshark打开tcpdump_10.37.63.3_8080_20160525.cap文件`** 



![][3]


No. 1-4 行：TCP三次握手环节；

No. 5-8 行：TCP传输数据环节；

No. 9-13 行：TCP四次挥手环节；


* **`顺便说一个查看 http 请求和响应的方法：`** 



![][4]

 **`弹窗如下图所示，上面红色部分为请求信息，下面蓝色部分为响应信息：`** 


![][5]


以上是Wireshark分析tcpdump的简单使用，Wireshark更强大的是过滤器工具，大家可以自行去多研究学习[Wireshark][6]，用起来还是比较爽的。
 **`推荐几个关于Wireshark的文章：`** 


* [Wireshark基本介绍和学习TCP三次握手][11]

* [一站式学习Wireshark][12]


[6]: https://link.jianshu.com?t=https://www.wireshark.org/download.html
[7]: https://link.jianshu.com?t=http://www.baidu.com/
[8]: https://link.jianshu.com?t=http://www.baidu.com
[9]: https://link.jianshu.com?t=http://www.baidu.cn'
[10]: https://link.jianshu.com?t=https://www.wireshark.org/download.html
[11]: https://link.jianshu.com?t=http://www.cnblogs.com/TankXiao/archive/2012/10/10/2711777.html
[12]: https://link.jianshu.com?t=https://community.emc.com/message/818739
[0]: ../IMG/25092458_tkVn.jpg
[1]: ../IMG/25092611_QKse.jpg
[2]: ../IMG/25092817_p0tr.jpg
[3]: ../IMG/25100721_RbeV.png
[4]: ../IMG/25104942_6xtL.png
[5]: ../IMG/25105257_GbHZ.png
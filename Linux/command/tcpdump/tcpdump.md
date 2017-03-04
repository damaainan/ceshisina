tcpdump 是 Linux 下的抓包工具，使用参数比较多，输出条目比较细。

### tcpdump的命令行格式

      tcpdump [ -adeflnNOpqStvx ] [ -c 数量 ] [ -F 文件名 ]
              [ -i 网络接口 ] [ -r 文件名] [ -s snaplen ]
              [ -T 类型 ] [ -w 文件名 ] [表达式 ]
    

### tcpdump的参数选项

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
    -s<数据包大小>：指定抓包显示一行的宽度，-s0表示可按包长显示完整的包，经常和-A一起用，默认截取长度为60个字节，但一般ethernetMTU都是1500字节。所以，要抓取大于60字节的包时，使用默认参数就会导致包数据丢失； 
    -S：用绝对而非相对数值列出TCP关联数； 
    -t：在输出的每一行不打印时间戳； 
    -tt：在输出的每一行显示未经格式化的时间戳记； 
    -T<数据包类型>：将监听到的包直接解释为指定的类型的报文，常见的类型有rpc （远程过程调用）和snmp（简单网络管理协议）； 
    -v：输出一个稍微详细的信息，例如在ip包中可以包括ttl和服务类型的信息； 
    -vv：输出详细的报文信息； 
    -x/-xx/-X/-XX：以十六进制显示包内容，几个选项只有细微的差别，详见man手册； 
    -w<数据包文件>：直接将包写入文件中，并不分析和打印出来；
    expression：用于筛选的逻辑表达式；
    

### tcpdump的表达式

表达式是一个逻辑表达式，tcpdump利用它作为过滤报文的条件，如果一个报文满足表达式的条件，则这个报文将会被捕获。如果没有给出任何条件，则网络上所有的信息包将会被截获。

在表达式中一般如下几种类型的关键字:

#### 关于类型的关键字，主要包括host，net，port

例如，

host 210.27.48.2，指明 210.27.48.2是一台主机，net 202.0.0.0 指明202.0.0.0是一个网络地址，port 23 指明端口号是23。

如果没有指定类型，缺省的类型是host.

#### 关于传输方向的关键字:   src,dst,dst or src,dst and src

例如，src 210.27.48.2 ,指明ip包中源地址是210.27.48.2 , dst net 202.0.0.0 指明目的网络地址是202.0.0.0 。如果没有指明方向关键字，则缺省是src or dst关键字。

#### 关于协议的关键字：fddi,ip,arp,rarp,tcp,udp

Fddi指明是在FDDI(分布式光纤数据接口网络)上的特定的网络协议，实际上它是"ether"的别名，fddi和e ther具有类似的源地址和目的地址，所以可以将fddi协议包当作ether的包进行处理和分析。

其他的几个关键字就是指明了监听的包的协议内容。如果没有指定任何协议，则tcpdump将会监听所有协议的信息包。

#### 逻辑运算符关键字

非运算 'not ' '! '

与运算 'and','&&'

或运算 'or' ,'||'

这些关键字可以组合起来构成强大的组合条件来满足人们的需要，下面举几个例子来说明。

#### 其他重要关键字

除了这三种类型的关键字之外，其他重要的关键字如下：gateway, broadcast,less,greater。


### 案例

想要截获所有210.27.48.1 的主机收到的和发出的所有的数据包：

    tcpdump host 210.27.48.1

想要截获主机210.27.48.1 和主机210.27.48.2 或210.27.48.3的通信，使用命令

    tcpdump host 210.27.48.1 and \(210.27.48.2 or 210.27.48.3\)

如果想要获取主机210.27.48.1除了和主机210.27.48.2之外所有主机通信的ip包，使用命令：

    tcpdump ip host 210.27.48.1 and ! 210.27.48.2

如果想要获取主机210.27.48.1接收或发出的telnet包，使用如下命令：

    tcpdump tcp port 23 host 210.27.48.1

输出结果介绍

下面我们介绍几种典型的tcpdump命令的输出信息

数据链路层头信息

使用命令tcpdump --e host ice

ice 是一台装有linux的主机，她的MAC地址是0:90:27:58:af:1a

H219是一台装有SOLARIC的SUN工作站，它的MAC地址是8:0:20:79:5b:46

命令的输出结果如下所示：

    21:50:12.847509 eth0 < 8:0:20:79:5b:46 0:90:27:58:af:1a ip 60: h219.33357 > ice.telnet 0:0(0) ack 22535 win 8760 (DF)

分析：
```
21:50:12 是显示的时间

847509 是ID号

eth0 < 表示从网络接口eth0 接受该数据包

eth0 > 表示从网络接口设备发送数据包

8:0:20:79:5b:46 是主机H219的MAC地址,它表明是从源地址H219发来的数据包

0:90:27:58:af:1a 是主机ICE的MAC地址,表示该数据包的目的地址是ICE

ip 是表明该数据包是IP数据包,

60 是数据包的长度,

h219.33357 > ice.telnet 表明该数据包是从主机H219的33357端口发往主机ICE的TELNET(23)端口

ack 22535 表明对序列号是222535的包进行响应

win 8760 表明发送窗口的大小是8760
```
#### ARP包的TCPDUMP输出信息

使用命令

    #tcpdump arp

得到的输出结果是：

    22:32:42.802509 eth0 > arp who-has route tell ice (0:90:27:58:af:1a)

    22:32:42.802902 eth0 < arp reply route is-at 0:90:27:12:10:66 (0:90:27:58:af:1a)

分析:
```
22:32:42 时间戳

802509 ID号

eth0 > 表明从主机发出该数据包

arp 表明是ARP请求包

who-has route tell ice 表明是主机ICE请求主机ROUTE的MAC地址

0:90:27:58:af:1a 是主机ICE的MAC地址。
```
TCP包的输出信息

用TCPDUMP捕获的TCP包的一般输出信息是：
```
src > dst: flagsdata-seqnoackwindowurgentoptions
    

src > dst 表明从源地址到目的地址

flags 是TCP包中的标志信息,S 是SYN标志, F(FIN), P(PUSH) , R(RST) "."(没有标记)

data-seqno 是数据包中的数据的顺序号

ack 是下次期望的顺序号

window 是接收缓存的窗口大小

urgent 表明数据包中是否有紧急指针

options 是选项
```
### 用TCPDUMP捕获的UDP包的一般输出信息是：

    route.port1 > ice.port2: udplenth
    

UDP十分简单，上面的输出行表明从主机ROUTE的port1端口发出的一个UDP数据包到主机ICE的port2端口，类型是UDP， 包的长度是lenth

wireshark查看

要让wireshark能分析tcpdump的包，关键的地方是 -s 参数， 还有要保存为-w文件，例如下面的例子：

    ./tcpdump -i eth0 -s 0 -w SuccessC2Server.pcaphost 192.168.1.20 # 抓该主机的所有包,在wireshark中过滤
    ./tcpdump -i eth0 'dst host 239.33.24.212' -w raw.pcap # 抓包的时候就进行过滤
    

wireshark的过滤，很简单的，比如:

tcp.port eq 5541

ip.addr eq 192.168.2.1

过滤出来后， 用fllow tcp 查看包的内容。

### 其他

device eth0/eth1 entered promiscuous mode

message日志中提示：

kernel: device eth0 entered promiscuous mode

kernel: device eth0 left promiscuous mode

网卡进入了混杂模式。一般对通信进行抓包分析时进入混杂模式（tcpdump）。（默认网卡启用了混杂模式的）

关闭混杂模式：ifconfig eth0 -promisc

启用混杂模式：ifconfig eth0 promisc

TCP协议的KeepAlive机制与HeartBeat心跳包:http://www.nowamagic.net/academy/detail/23350382

TCP Keepalive HOWTO:http://www.tldp.org/HOWTO/html_single/TCP-Keepalive-HOWTO/

参考

http://xstarcd.github.io/wiki/shell/tcpdump.html

https://my.oschina.net/xianggao/blog/678644
# Linux防火墙设置之FirewallD

 时间 2016-12-05 20:41:05 

原文[https://www.biaodianfu.com/firewalld.html][1]


centos从7.0 开始将原先的防火墙iptables换成了FirewallD。FirewallD支持 IPv4, IPv6 防火墙设置以及以太网桥接，并且拥有运行时配置和永久配置选项，被称作动态管理防火墙，也就是说不需要重启整个防火墙便可应用更改。centos7默认安装了firewalld，若没有安装，执行   yum  install  firewalld  firewalld  -  config   安装，其中firewalld-config是GUI工具。FirewallD与iptables关系： 

![][5]

firewalld底层仍旧是基于iptables的，但还是有很多不同的地方：

* iptables在 /etc/sysconfig/iptables 中储存配置，而 firewalld 将配置储存在 /usr/lib/firewalld/ 和 /etc/firewalld/ 中的各种 XML 文件里，其中前者是默认的配置，请不要修改。可以在/etc/firewalld/中编辑自己的配置，firewalld优先使用/etc/firewalld/中的配置。
* 使用 iptables，每一个单独更改意味着清除所有旧有的规则和从 /etc/sysconfig/iptables里读取所有新的规则，然而使用 firewalld 却不会再创建任何新的规则；仅仅运行规则中的不同之处。因此，firewalld 可以在运行时间内，改变设置而不丢失现行连接。

firewalld **中zone概念（区域）**

RHEL7中的不过貌似其实现方式还是和iptables一样的，但是不像mariaDB那样兼容MySQL命令，FirewallD无法解析由 ip*tables 和 ebtables 命令行工具添加的防火墙规则

FirewallD使用区域（zone）的概念来管理，每个网卡对应一个zone，这些zone的配置文件可在/usr/lib/firewalld/zones/下看到，默认的是public.由firewalld 提供的区域按照从不信任到信任的顺序排序：

* drop（丢弃）任何流入网络的包都被丢弃，不作出任何响应。只允许流出的网络连接。
* block（阻塞）任何进入的网络连接都被拒绝，并返回 IPv4 的 icmp-host-prohibited 报文或者 IPv6 的 icmp6-adm-prohibited 报文。只允许由该系统初始化的网络连接。
* public（公开） 在用以可以公开的部分。你认为网络中其他的计算机不可信并且可能伤害你的计算机。只允许选中的连接接入。
* external（外部）用在路由器等启用伪装的外部网络。你认为网络中其他的计算机不可信并且可能伤害你的计算机。只允许选中的连接接入。
* dmz（隔离区）用以允许隔离区（dmz）中的电脑有限地被外界网络访问。只接受被选中的连接。
* work（工作）用在工作网络。你信任网络中的大多数计算机不会影响你的计算机。只接受被选中的连接。
* home（家庭）用在家庭网络。你信任网络中的大多数计算机不会影响你的计算机。只接受被选中的连接。
* internal（内部）用在内部网络。你信任网络中的大多数计算机不会影响你的计算机。只接受被选中的连接。
* trusted（信任）允许所有网络连接。

firewalld **中的过滤规则**

* source: 根据源地址过滤
* interface: 根据网卡过滤
* service: 根据服务名过滤
* port: 根据端口过滤
* icmp-block: icmp 报文过滤，按照 icmp 类型配置
* masquerade: ip 地址伪装
* forward-port: 端口转发
* rule: 自定义规则

其中，过滤规则的优先级遵循如下顺序

* source
* interface
* conf

firewalld **常用命令**

fierwalld可以直接修改配置文件进行配置，也可以通过配置工具的命令，这里因为是远程操作为了确保开启后ssh端口是开放的，所以直接修改配置文件。

先查看/etc/firewalld/firewalld.conf中DefaultZone的值，默认是DefaultZone=public，这时/etc/firewalld/zones/目录下应该有个public.xml文件，vi打开它修改成：

```xml
    <?xmlversion="1.0" encoding="utf-8"?>
    <zone>
        <short>Public</short>
        <description>For use in public areas. Youdo not trusttheothercomputersonnetworksto not harmyourcomputer. Onlyselectedincomingconnectionsareaccepted.</description>
        <servicename="dhcpv6-client"></servicename>
        <servicename="ssh"></servicename>
        <servicename="http"></servicename>
        <servicename="https"></servicename>
    </zone>
```

这就代表在public zone中开放ssh（22）、http（80）、https（443）端口，其中对应每一个在/usr/lib/firewalld/services/下*.xml文件定义好的服务类型，比如http.xml文件如下：

```xml
    <?xmlversion="1.0" encoding="utf-8"?>
    <service>
        <short>WWW (HTTP)</short>
        <description>HTTPis theprotocolusedto serveWebpages. If youplanto makeyourWebserverpubliclyavailable, enablethis option. This optionis not requiredfor viewingpageslocallyor developingWebpages.</description>
        <portprotocol="tcp" port="80"></portprotocol>
    </service>
```

所以也可以直接在public.xml中这样：

```xml
    <?xmlversion="1.0" encoding="utf-8"?>
    <zone>
        <short>Public</short>
        <description>For use in public areas. Youdo not trusttheothercomputersonnetworksto not harmyourcomputer. Onlyselectedincomingconnectionsareaccepted.</description>
        <servicename="dhcpv6-client"></servicename>
        <servicename="ssh"></servicename>
        <portprotocol="tcp" port="80"></portprotocol> #等效的
        <servicename="https"></servicename>
    </zone>
```

每次改配置文件还是比较麻烦的，firewalld可以使用firewall-config和firewall-cmd进行配置，前者是由于GUI模式下，后者为命令行下工具,一些常用命令如下：

    systemctlstartfirewalld #启动
    systemctlstatusfirewalld #或者firewall-cmd –state 查看状态
    sytemctldisablefirewalld #停止并禁用开机启动
    systemctlenablefirewalld #设置开机启动
    systemctlstopfirewalld #禁用
    firewall-cmd –version #查看版本
    firewall-cmd –help#帮助信息
    firewall-cmd –get-active-zones#查看区域信息
    firewall-cmd –get-zone-of-interface=eth0#查看指定接口所属区域
    firewall-cmd –panic-on #拒绝所有包
    firewall-cmd –panic-off#取消拒绝状态
    firewall-cmd –query-panic#查看是否拒绝
    firewall-cmd –reload #更新防火墙规则
    firewall-cmd –complete-reload #断开再连接
    firewall-cmd –zone=public –add-interface=eth0 #将接口添加到public区域 ， 默认接口都在public。若加上–permanet则永久生效
    firewall-cmd –set-default-zone=public #设置public为默认接口区域
    firewall-cmd –zone=pulic –list-ports #查看所有打开的端口
    firewall-cmd –zone=pulic –add-port=80/tcp #把tcp 80端口加入到区域
    firewall-cmd –zone=public –add-service=http #把http服务加入到区域
    firewall-cmd –zone=public –remove-service=http #移除http服务
    

部分命令共同的参数说明：

* –zone=ZONE 指定命令作用的zone，省缺的话命令作用于默认zone
* –permanent 有此参数表示命令只是修改配置文件，需要reload才能生效；无此参数则立即在当前运行的实例中生效，不过不会改动配置文件，重启firewalld服务就没效果了。
* –timeout=seconds 表示命令效果持续时间，到期后自动移除，不能和–permanent同时使用。例如因调试的需要加了某项配置，到时间自动移除了，不需要再回来手动删除。也可在出现异常情况时加入特定规则，过一段时间自动解除。

参考连接：

* [https://fedoraproject.org/wiki/FirewallD/zh-cn][6]
* [https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-using-firewalld-on-centos-7][7]

## 拓展知识：Linux中的防火墙

### netfilter

iptables、firewalld这些软件本身其实并不具备防火墙功能，他们的作用都是在用户空间中管理和维护规则，只不过规则结构和使用方法不一样罢了，真正利用规则进行过滤是由内核的netfilter完成的。netfilter是Linux 2.4内核引入的包过滤引擎。由一些数据包过滤表组成，这些表包含内核用来控制信息包过滤的规则集。iptables、firewalld等等都是在用户空间修改过滤表规则的便捷工具。

linux内部结构可以分为三部分，从最底层到最上层依次是：硬件–>内核空间–>用户空间

![][8]

netfilter在数据包必须经过且可以读取规则的位置，共设有5个控制关卡。这5个关卡处的检查规则分别放在5个规则链中：

* PREROUTING 数据包刚进入网络接口之后，路由之前
* INPUT 数据包从内核流入用户空间
* FORWARD 在内核空间中，从一个网络接口进入，到另一个网络接口去。转发过滤。
* OUTPUT 数据包从用户空间流出到内核空间。
* POSTROUTING 路由后，数据包离开网络接口前。

链其实就是包含众多规则的检查清单，每一条链中包含很多规则。当一个数据包到达一个链时，系统就会从链中第一条规则开始检查，看该数据包是否满足规则所定义的条件。如果满足，系统就会根据该条规则所定义的方法处理该数据包；否则就继续检查下一条规则，如果该数据包不符合链中任一条规则，系统就会根据该链预先定义的默认策略来处理数据包。

当一个数据包进入网卡时，它首先进入PREROUTING链，内核根据数据包目的IP判断是否需要转送出去。如果数据包就是进入本机的，它就会沿着图向下移动，到达INPUT链。数据包到了INPUT链后，任何进程都会收到它。本机上运行的程序可以发送数据包，这些数据包会经过OUTPUT链，然后到达POSTROUTING链输出。如果数据包是要转发出去的，且内核允许转发，数据包就会如图所示向右移动，经过FORWARD链，然后到达POSTROUTING链输出

![][9]

可以看出，刚从网络接口进入的数据包尚未进行路由决策，还不知道数据要走向哪里，所以进出口处没办法实现数据过滤，需要在内核空间设置转发关卡、进入用户空间关卡和离开用户空间关卡。

### iptables

iptablses按照用途和使用场合，将5条链各自切分到五张不同的表中。也就是说每张表中可以按需要单独为某些链配置规则。例如，mangle表和filter表中都能为INPUT链配置规则，当数据包流经INPUT位置（进入用户空间），这两个表中INPUT链的规则都会用来做过滤检查。

![][10]

五张表，每张表侧重于不同的功能

* filter 数据包过滤功能。只涉及INPUT, FORWARD, OUTPUT三条链。是iptables命令默认操纵的表。
* nat 地址转换功能。NAT转换只涉及PREROUTING, OUTPUT, POSTOUTING三条链。可通过转发让局域网机器连接互联网
* mangle 数据包修改功能。每条链上都可以做修改操作。修改报文元数据，做防火墙标记等。
* raw 快速通道功能。为了提高效率，优先级最高，符合raw表规则的数据包会跳过一些检查。
* security 需要和selinux结合使用，内置规则比较复杂，通常都会被关闭。

iptables还支持自定义规则链。自定义的链必须和某个特定的链关联起来。可在某个链中设定规则，满足一定条件的数据包跳转到某个目标链处理，目标链处理完成后返回当前链中继续处理后续规则。因为链中规则是从头到尾依次检查的，所以规则的次序是非常重要的。越严格的规则应该越靠前。

#### iptablse服务管理 

    serviceiptablesstart|stop|restart|status
    serviceiptablessave  //定义的所有内容，在重启时都会失效。调用save命令可以把规则保存到文件/etc/sysconfig/iptables中。
    iptables-save          //保存规则
    iptables-restore        //加载规则。开机的时候，会自动加载/etc/sysconfig/iptables
    iptables-restore < /etc/sysconfig/iptables2    //加载自定义的规则文件
     
    //iptables服务配置文件：   /etc/sysconfig/iptables-config
    //iptables规则文件：       /etc/sysconfig/iptables
     
    echo "1">/proc/sys/net/ipv4/ip_forward  //打开iptables转发：
    

#### iptables命令参考 

    iptables [-t TABLE] COMMAND [CHAIN] [CRETIRIA]...  [-j  ACTION]
    

省缺表名为filter。命令中用到的序号(RULENUM)都基于1。

#### COMMAND 命令选项

    -A|--append  CHAIN                                //链尾添加新规则
    -D|--delete  CHAIN [RULENUM]                      //删除链中规则，按需序号或内容确定要删除的规则
    -I|--insert  CHAIN [RULENUM]                      //在链中插入一条新的规则，默认插在开头
    -R|--replaceCHAIN  RULENUM                        //替换、修改一条规则，按序号或内容确定
    -L|--list  [CHAIN [RULENUM]]                      //列出指定链或所有链中指定规则或所有规则
    -S|--list-urles [CHAIN [RULENUM]]                  //显示链中规则
    -F|--flush [CHAIN]                                //清空指定链或所有链中规则
    -Z|--zero [CHAIN [RULENUM]]                        //重置指定链或所有链的计数器(匹配的数据包数和流量字节数)
    -N|--new-chainCHAIN                              //新建自定义规则链
    -X|--delete-cahin [CHAIN]                          //删除指定表中用户自定义的规则链
    -E|--rename-chainOLDCHAINNEWCHAIN                //重命名链，移动任何引用
    -P|-policyCHAINTARGET                            //设置链的默认策略，数据包未匹配任意一条规则就按此策略处理
    

#### CRETIRIA 条件匹配 

分为基本匹配和扩展匹配，扩展匹配又分为隐式匹配和显示匹配。

基本匹配：（可使用 ! 可以否定一个子句，如-p !tcp）

    -p|--proto  PROTO                      //按协议匹配，如tcp、udp、icmp，all表示所有协议。 （/etc/protocols中的协议名）
    -s|--sourceADDRESS[/mask]...          //按数据包的源地址匹配，可使用IP地址、网络地址、主机名、域名
    -d|--destinationADDRESS[/mask]...    //按目标地址匹配，可使用IP地址、网络地址、主机名、域名
    -i|--in-interface INPUTNAME[ +]        //按入站接口(网卡)名匹配，+用于通配。如 eth0, eth+ 。一般用在INPUT和PREROUTING链
    -o|--out-interface OUTPUTNAME[+]      //按出站接口(网卡)名匹配，+用于通配。如 eth0, eth+ 。一般用在OUTPUT和POSTROUTING链
    

扩展匹配：（如: -p tcp -m tcp –dport 80）

    -m|--matchMATCHTYPE  EXTENSIONMATCH...    //扩展匹配，可能加载extension
    

隐式扩展匹配

对-p PROTO的扩展，或者说是-p PROTO的附加匹配条件，-m PROTO 可以省略，所以叫隐式

    -m tcp  //-p tcp的扩展
    　　　　--sport  [!]N[:M]                      //源端口, 服务名、端口、端口范围。
    　　　　--dport  [!]N[:M]                      //目标端口，服务名、端口、端口范围
    　　　　--tcp-flagsCHECKFLAGSFLAGSOFTRUE  //TCP标志位:SYN(同步),ACK(应答),RST(重置),FIN(结束),URG(紧急),PSH(强迫推送)。多个标志位逗号分隔。
    　　　　　　　　　　　　　　　　　　　　　　　　　//CHECKFLAGS为要检查的标志位，FLAGSOFTRUE为必须为1的标志位（其余的应该为0）
    　　　　--syn                              //第一次握手。 等效于 --tcpflags syn,ack,fin,rst syn   四个标志中只有syn为1
    -m udp  //-p udp的扩展
    　　　　--sport N[-M] 
    　　　　--dport N[-M]
    -m icmp  //隐含条件为-p icmp
    　　　　--icmp-type  N            //8:echo-request  0:echo-reply
    

显示扩展匹配

    -m state
    　　　　--state    //连接状态检测，NEW,ESTABLISHED,RELATED,INVALID
    -m multiport 
    　　　　--source-ports  PORT[,PORT]...|N:M            //多个源端口，多个端口用逗号分隔，
    　　　　--destination-portsPORT[,PORT]...|N:M        //多个目的端口
    　　　　--ports    　　　　　　　　　　　　　　　　　　　　 //多个端口，每个包的源端口和目的端口相同才会匹配
    -m limit
    　　　　--limit  N/UNIT    //速率，如3/minute, 1/s, n/second , n/day
    　　　　--limit-burst N    //峰值速率，如100，表示最大不能超过100个数据包
    -m connlimit
    　　　　--connlimit-above N  //多于n个，前面加!取反
    -m iprange
    　　　　--src-rangeIP-IP
    　　　　--dst-rangeIP-IP
    -m mac                    
    　　　　--mac-source        //mac地址限制，不能用在OUTPUT和POSTROUTING规则链上，因为封包要送到网卡后，才能由网卡驱动程序透过ARP 通讯协议查出目的地的MAC 地址
    -m string
    　　　　--algo [bm|kmp]      //匹配算法
    　　　　--string "PATTERN"  //匹配字符模式
    -m recent
    　　　　--name              //设定列表名称，默认为DEFAULT
    　　　　--rsource            //源地址
    　　　　--rdest              //目的地址
    　　　　--set                //添加源地址的包到列表中
    　　　　--update            //每次建立连接都更新列表
    　　　　--rcheck            //检查地址是否在列表
    　　　　--seconds            //指定时间。必须与--rcheck或--update配合使用
    　　　　--hitcount          //命中次数。必须和--rcheck或--update配合使用
    　　　　--remove            //在列表中删除地址
    -m time
    　　　　--timestart h:mm
    　　　　--timestop  hh:mm
    　　　　--daysDAYS          //Mon,Tue,Wed,Thu,Fri,Sat,Sun; 逗号分隔
    -m mark
    　　　　--mark N            //是否包含标记号N
    -m owner 
    　　　　--uid-owner 500  //用来匹配来自本机的封包，是否为某特定使用者所产生的,可以避免服务器使用root或其它身分将敏感数据传送出
    　　　　--gid-owner O    //用来匹配来自本机的封包，是否为某特定使用者群组所产生的
    　　　　--pid-owner 78    //用来匹配来自本机的封包，是否为某特定进程所产生的
    　　　　--sid-owner 100  //用来匹配来自本机的封包，是否为某特定连接（Session ID）的响应封包
    

#### ACTION 目标策略(TARGET)

    -j|--jumpTARGET                //跳转到目标规则，可能加载target extension
    -g|--goto  CHAIN                //跳转到指定链，不再返回
    ACCEPT            规则验证通过，不再检查当前链的后续规则，直接跳到下一个规则链。
    DROP                直接丢弃数据包，不给任何回应。中断过滤。
    REJECT            拒绝数据包通过，会返回响应信息。中断过滤。
    --reject-with  tcp-reset|port-unreachable|echo-reply
    LOG                  在/var/log/messages文件中记录日志，然后将数据包传递给下一条规则。详细位置可查看/etc/syslog.conf配置文件
    --log-prefix "INPUT packets"
    ULOG                更广范围的日志记录信息
    QUEUE              防火墙将数据包移交到用户空间，通过一个内核模块把包交给本地用户程序。中断过滤。
    RETURN            防火墙停止执行当前链中的后续规则，并返回到调用链。主要用在自定义链中。
    custom_chain    转向自定义规则链
    DNAT                目标地址转换，改变数据包的目标地址。外网访问内网资源，主要用在PREROUTING。完成后跳到下一个规则链
    --to-destinationADDRESS[-ADDRESS][:PORT[-PORT]]
    SNAT                源地址转换，改变数据包的源地址。内网访问外网资源。主机的IP地址必须是静态的，主要用在POSTROUTING。完成后跳到下一个规则链。
    --to-sourceADDRESS[-ADDRESS][:PORT[-PORT]]
    MASQUERADE  源地址伪装，用于主机IP是ISP动态分配的情况，会从网卡读取主机IP。直接跳到下一个规则链。
    --to-ports 1024-31000
    REDIRECT        数据包重定向，主要是端口重定向，把包分流。处理完成后继续匹配其他规则。能会用这个功能来迫使站点上的所有Web流量都通过一个Web高速缓存，比如Squid。
    --to-ports 8080
    MARK                打防火墙标记。继续匹配规则。
    --set-mark 2
    MIRROR          发送包之前交换IP源和目的地址，将数据包返回。中断过滤。
    

辅助选项：

    -t|--tableTABLE    //指定操作的表，默认的表为filter
    -n|--numeric        //用数字形式显示地址和端口，显示主机IP地址而不是主机名
    -x|--exact          //计数器显示精确值，不做单位换算
    -v|--verbose  (x3)  //查看规则列表时，显示更详细的信息
    -line-numbers        //查看规则表时，显示在链中的序号
    -V|--version 
    -h|--help  
    [option]  --help    //查看特定选项的帮助，如iptables -p icmp --help
     
    --fragment -f              //match second or further fragments only
    --modprobe=<command>        //try to insert modules using this command
    --set-countersPKTSBYTES  //set the counter during insert/append
    

#### state TCP链接状态

    NEW                第一次握手，要起始一个连接（重设连接或将连接重导向） 
    ESTABLISHED  数据包属于某个已经建立的连接。第二次和第三次握手  (ack=1)
    INVALID          数据包的连接编号（SessionID）无法辨识或编号不正确。如SYN=1 ACK=1 RST=1  
    RELATED          表示该封包是属于某个已经建立的连接，所建立的新连接。如有些服务使用两个相关的端口，如FTP，21和20端口一去一回，FTP数据传输(上传/下载)还会使用特殊的端口
    只允许NEW和ESTABLISHED进，只允许ESTABLISHED出可以阻止反弹式木马。
    

#### 使用示例：

    iptables -F          //删除iptables现有规则
    iptables -L [-v[vv] -n]  //查看iptables规则
    iptables -A INPUT -i eth0 -p tcp --dport 80 -m state --stateNEW,ESTABLISHED -j ACCEPT      //在INPUT链尾添加一条规则
    iptables -I INPUT 2 -i eth0 -p tcp --dport 80 -m state --stateNEW,ESTABLISHED -j ACCEPT    //在INPUT链中插入为第2条规则
    iptables -D  INPUT 2      //删除INPUT链中第2条规则
    iptables -R INPUT 3 -i eth0 -p tcp --dport 80 -m state --stateNEW,ESTABLISHED -j ACCEPT    //替换修改第三条规则
    iptables -P INPUTDROP    //设置INPUT链的默认策略为DROP
     
    //允许远程主机进行SSH连接
    iptables -A INPUT -i eth0 -p tcp --dport 22 -m state --stateNEW,ESTABLISHED -j ACCEPT
    iptables -A OUTPUT -o eth0 -p tcp --sport 22 -m state --stateESTABLISHED -j ACCEPT 
     
    //允许本地主机进行SSH连接
    iptables -A OUTPUT -o eth0 -p tcp --dport 22 -m state --stateNEW,ESTABLISHED -j ACCEPT
    iptables -A INTPUT -i eth0 -p tcp --sport 22 -m state --stateESTABLISHED -j ACCEPT 
     
    //允许HTTP请求
    iptables -A INPUT -i eth0 -p tcp --dport 80 -m state --stateNEW,ESTABLISHED -j ACCEPT
    iptables -A OUTPUT -o eth0 -p tcp --sport 80 -m state --stateESTABLISHED -j ACCEPT 
     
    //限制ping 192.168.146.3主机的数据包数，平均2/s个，最多不能超过3个
    iptables -A INPUT -i eth0 -d 192.168.146.3 -p icmp --icmp-type 8 -m limit --limit 2/second --limit-burst 3 -j ACCEPT 
     
    //限制SSH连接速率（默认策略是DROP）
    iptables -I INPUT 1 -p tcp --dport 22 -d 192.168.146.3 -m state --stateESTABLISHED -j ACCEPT  
    iptables -I INPUT 2 -p tcp --dport 22 -d 192.168.146.3 -m limit --limit 2/minute --limit-burst 2 -m state --stateNEW -j ACCEPT 
     
    //防止syn攻击（限制syn的请求速度）
    iptables -N syn-flood
    iptables -A INPUT -p tcp --syn -j syn-flood
    iptables -A syn-flood -m limit --limit 1/s --limit-burst 4 -j RETURN 
    iptables -A syn-flood -j DROP 
     
    //防止syn攻击（限制单个ip的最大syn连接数）
    iptables –A INPUT –i eth0 –p tcp --syn -m connlimit --connlimit-above 15 -j DROP
     
    iptables -I INPUT -p tcp -dport 22 -m connlimit --connlimit-above 3 -j DROP  //利用recent模块抵御DOS攻击
    iptables -I INPUT -p tcp --dport 22 -m state --stateNEW -m recent --set --nameSSH  //单个IP最多连接3个会话
    Iptables -I INPUT -p tcp --dport 22 -m stateNEW -m recent --update --seconds 300 --hitcount 3 --nameSSH -j DROP  //只要是新的连接请求，就把它加入到SSH列表中。5分钟内你的尝试次数达到3次，就拒绝提供SSH列表中的这个IP服务。被限制5分钟后即可恢复访问。
     
    iptables -I INPUT -p tcp --dport 80 -m connlimit --connlimit-above 30 -j DROP    //防止单个IP访问量过大
    iptables –A OUTPUT –m state --stateNEW –j DROP  //阻止反弹木马
    iptables -A INPUT -p icmp --icmp-typeecho-request -m limit --limit 1/m -j ACCEPT  //防止ping攻击
     
    //只允许自己ping别人，不允许别人ping自己
    iptables -A OUTPUT -p icmp --icmp-type 8 -j ACCEPT
    iptables -A INPUT -p icmp --icmp-type 0 -j ACCEPT
     
    //对于127.0.0.1比较特殊，我们需要明确定义它
    iptables -A INPUT -s 127.0.0.1 -d 127.0.0.1 -j ACCEPT
    iptables -A OUTPUT -s 127.0.0.1 -d 127.0.0.1 -j ACCEPT
     
    //SNAT 基于原地址转换。许多内网用户通过一个外网 口上网的情况。将我们内网的地址转换为一个外网的IP，共用外网IP访问外网资源。
    iptables -t nat -A POSTROUTING -s 192.168.10.0/24 -j SNAT --to-source 172.16.100.1
     
    //当外网地址不是固定的时候。将外网地址换成 MASQUERADE(动态伪装):它可以实现自动读取外网网卡获取的IP地址。
    iptables -t nat -A POSTROUTING -s 192.168.10.0/24 -j MASQUERADE
     
    //DNAT 目标地址转换。目标地址转换要做在到达网卡之前进行转换,所以要做在PREROUTING这个位置上
    iptables -t nat -A PREROUTING -d 192.168.10.18 -p tcp --dport 80 -j DNAT --to-destination 172.16.100.2
    

参考资料：

* [https://wiki.archlinux.org/index.php/Iptables_(%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87)][11]


[1]: https://www.biaodianfu.com/firewalld.html

[4]: /topics/11000069
[5]: ../IMG/NzIJf27.png
[6]: https://fedoraproject.org/wiki/FirewallD/zh-cn
[7]: https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-using-firewalld-on-centos-7
[8]: ../IMG/vmeiMnA.png
[9]: ../IMG/myQJVrE.png
[10]: ../IMG/rE7ZjuN.jpg
[11]: https://wiki.archlinux.org/index.php/Iptables_(%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87)
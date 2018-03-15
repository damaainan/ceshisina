## linux基础命令介绍六：网络

来源：[https://segmentfault.com/a/1190000007483202](https://segmentfault.com/a/1190000007483202)

本文将讲述网络相关命令，作者假定读者具备TCP/IP协议栈的基础知识。对于相关命令及其输出只介绍它的基本的使用方法和大概的描述，具体协议将不作详细解释。

如今网络无疑是很重要的，linux系统中提供了丰富的网络测试与管理命令。我们来一起看看它们。
### 1、`ping`发送TCMP回显请求报文，并等待返回TCMP回显应答。

```sh
ping [OPTIONS]... destination
```

这里的目标`destination`可以是目的IP地址或者域名/主机名

选项`-c`指定发送请求报文的次数，当ping没有任何选项时，在linux中默认将一直发送请求报文直到手动终止。

```sh
[root@centos7 ~]# ping -c 3 www.baidu.com
PING www.a.shifen.com (61.135.169.121) 56(84) bytes of data.
64 bytes from 61.135.169.121: icmp_seq=1 ttl=52 time=1.35 ms
64 bytes from 61.135.169.121: icmp_seq=2 ttl=52 time=1.32 ms
64 bytes from 61.135.169.121: icmp_seq=3 ttl=52 time=1.22 ms

--- www.a.shifen.com ping statistics ---
3 packets transmitted, 3 received, 0% packet loss, time 2003ms
rtt min/avg/max/mdev = 1.225/1.303/1.359/0.064 ms
```

首先，ping程序会向域名服务器(DNS)发送请求，解析域名`www.baidu.com`的IP地址。`DNS`返回域名的一个别名`www.a.shifen.com`以及对应的IP地址`61.135.169.121`。之后ping程序开始向这个地址发送请求报文，每1s发送一个，ping收到TCMP回显应答并将结果显示在终端上，包括ICMP序列号(icmp_seq)，生存时间(ttl)和数据包往返时间(time)。最后，给出汇总信息，包括报文总收发情况，总时间，往返时间最小值、平均值、最大值、平均偏差(越大说明网络越不稳定)。

```sh
[root@centos7 ~]# ping www.a.com
ping: unknown host www.a.com
```

当目的域名无法解析出IP地址时，会报未知主机的错

```sh
[root@centos7 ~]# ping 192.168.0.1
PING 192.168.0.1 (192.168.0.1) 56(84) bytes of data.
^C                           #这里按CTRL+C键手动终止了进程
--- 192.168.0.1 ping statistics ---
6 packets transmitted, 0 received, 100% packet loss, time 4999ms
```

当目的IP地址没有路由时不会收到任何ICMP回显报文

```sh
[root@centos7 ~]# ping -c2 10.0.1.2
PING 10.0.1.2 (10.0.1.2) 56(84) bytes of data.
From 10.0.1.254 icmp_seq=1 Destination Host Unreachable
From 10.0.1.254 icmp_seq=2 Destination Host Unreachable

--- 10.0.1.2 ping statistics ---
2 packets transmitted, 0 received, +2 errors, 100% packet loss, time 999ms
pipe 2
```

当有目的IP的路由但无法达到时显示目标不可达错误(Destination Host Unreachable)。
`ICMP`回显应答还包括超时(request time out)等其他类型。
### 2、`hostname`显示或设置系统主机名

```sh
hostname [OPTIONS]... [NAME]
```

直接执行命令`hostname`时将显示主机名：

```sh
[root@centos7 temp]# hostname
centos7
[root@centos7 temp]#
```

这个主机名是系统的gethostname(2)函数返回的。
可以通过执行命令`hostname NAME`来临时改变主机名：

```sh
[root@centos7 temp]# hostname NAME
[root@centos7 temp]# hostname
NAME
```

这个临时修改实际上是修改了linux kernel中一个同为`hostname`的内核参数，它保存在`/proc/sys/kernel/hostname`中。如果需要永久修改则需要修改配置文件`/etc/sysconfig/network`，centos7中需要修改`/etc/hostname`。需要注意的是，如果配置文件中的主机名是`localhost`或`localhost.localdomain`时，系统会取得网络接口的IP地址，并用这个地址找出`/etc/hosts`文件中对应的主机名，然后将其设置成最终的`hostname`。
### 3、`host`DNS查询

```sh
host name
```
`host`命令通过配置文件`/etc/resolv.conf`中指定的DNS服务器查询`name`的IP地址：

```
[root@centos7 temp]# host www.baidu.com
www.baidu.com is an alias for www.a.shifen.com.
www.a.shifen.com has address 61.135.169.121
www.a.shifen.com has address 61.135.169.125
```
### 4、`dig`DNS查询
`dig`和`host`命令的语法一致，但提供了更详细的信息和更多的选项：

```sh
[root@centos7 ~]# dig www.baidu.com

; <<>> DiG 9.9.4-RedHat-9.9.4-29.el7_2.2 <<>> www.baidu.com
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 22125
;; flags: qr rd ra; QUERY: 1, ANSWER: 3, AUTHORITY: 0, ADDITIONAL: 0

;; QUESTION SECTION:
;www.baidu.com.                 IN      A

;; ANSWER SECTION:
www.baidu.com.          113     IN      CNAME   www.a.shifen.com.
www.a.shifen.com.       113     IN      A       61.135.169.125
www.a.shifen.com.       113     IN      A       61.135.169.121

;; Query time: 2 msec
;; SERVER: 223.5.5.5#53(223.5.5.5)
;; WHEN: 四 11月 10 12:31:20 CST 2016
;; MSG SIZE  rcvd: 90

[root@centos7 ~]# 
```

如只查询域名的A记录并以短格式显示：

```sh
[root@centos7 ~]# dig www.baidu.com A +short
www.a.shifen.com.
61.135.169.125
61.135.169.121
[root@centos7 ~]# 
```

或者：

```sh
[root@centos7 ~]# dig +nocmd www.baidu.com A +noall +answer     
www.baidu.com.          252     IN      CNAME   www.a.shifen.com.
www.a.shifen.com.       252     IN      A       61.135.169.125
www.a.shifen.com.       252     IN      A       61.135.169.121
```

还可以用`@server`的方式指定DNS服务器：

```sh
[root@centos7 ~]# dig +noall +answer www.baidu.com A @8.8.8.8
www.baidu.com.          21      IN      CNAME   www.a.shifen.com.
www.a.shifen.com.       263     IN      A       61.135.169.125
www.a.shifen.com.       263     IN      A       61.135.169.121
```

更多的命令及选项请自行man
### 5、`traceroute`或`tracepath`路由跟踪

```sh
[root@centos7 ~]# tracepath www.baidu.com
 1?: [LOCALHOST]                                         pmtu 1500
 1:  10.0.1.103                                            0.396ms 
 1:  10.0.1.103                                            0.350ms 
 2:  210.51.161.1                                          1.187ms asymm  3 
 3:  210.51.161.1                                          8.186ms 
 4:  210.51.175.81                                         1.117ms 
 5:  61.148.142.61                                         8.554ms asymm 12 
 6:  61.148.147.13                                         1.694ms asymm 12 
 7:  123.126.8.117                                         3.934ms asymm 10 
 8:  61.148.155.46                                         2.703ms asymm 10
 ....
```

这里只列出部分输出，表示跟踪到目的地址的路由，每一跳都返回。
### 6、`ifconfig`配置网络接口

当命令没有任何参数时显示所有网络接口的信息：

```sh
[root@centos7 ~]# ifconfig
ens32: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 172.20.71.254  netmask 255.255.255.0  broadcast 172.20.71.255
        inet6 fe80::250:56ff:fea4:fe34  prefixlen 64  scopeid 0x20* ether 00:50:56:a4:fe:34  txqueuelen 1000  (Ethernet)
        RX packets 11996157  bytes 775368588 (739.4 MiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 12  bytes 888 (888.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

ens33: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.1.254  netmask 255.255.255.0  broadcast 10.0.1.255
        inet6 fe80::250:56ff:fea4:a09  prefixlen 64  scopeid 0x20* ether 00:50:56:a4:0a:09  txqueuelen 1000  (Ethernet)
        RX packets 20941185  bytes 1307830447 (1.2 GiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 147552  bytes 11833605 (11.2 MiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1  (Local Loopback)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

[root@centos7 ~]#
```

本例中显示了两个网卡`ens32`和`ens33`以及环回口`lo`的信息，包括mtu，ip地址，掩码，mac地址，传输和接收数据量等等。
选项`-s`显示精简的信息：

```sh
[root@idc-v-71253 ~]# ifconfig -s ens32
Iface      MTU    RX-OK RX-ERR RX-DRP RX-OVR    TX-OK TX-ERR TX-DRP TX-OVR Flg
ens32     1500 11996951      0      0 0            12      0      0      0 BMRU
```

如给ens33增加一个新地址10.0.1.4：

```sh
[root@centos7 ~]# ifconfig ens33:0 10.0.1.4/24 up
[root@centos7 ~]# ifconfig ens33:0   
ens33:0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.1.4  netmask 255.255.255.0  broadcast 10.0.1.255
        ether 00:50:56:a4:0a:09  txqueuelen 1000  (Ethernet)
```

命令中`/24`表明接口地址的掩码，`up`表示启用此接口。注意如果ip地址已经被使用，这里依然会被设置成功，但此地址被访问时，可能会有冲突。
停用某接口：

```sh
[root@centos7 ~]# ifconfig ens33:0 down
```

如果需要永久增加或修改当前接口的地址，最好直接编辑网卡配置文件`/etc/sysconfig/network-scripts/ifcfg-ens33`(其他系统换成相应文件)中`IPADDR`字段，然后重启网络`systemctl restart network`或`service network restart`生效。
### 7、`arp`和`arping`命令`arp`显示系统的arp缓存，命令`arping`给邻居主机发送ARP请求。

```sh
[root@idc-v-71253 ~]# arp -a
? (10.0.1.1) at 68:8f:84:01:f1:ff [ether] on ens33
? (10.0.1.102) at 00:50:56:a4:18:9a [ether] on ens33
? (10.0.1.254) at 00:50:56:a4:a9:16 [ether] on ens33
? (10.0.1.10) at 00:50:56:a4:d2:e4 [ether] on ens33
? (10.0.1.104) at 00:50:56:a4:37:a7 [ether] on ens33
```
`?`表示未知域名，最后的网卡名表示arp表项对应的网络接口
如发现某地址不稳定，可以使用arping测试该地址是否为MAC地址冲突：

```sh
[root@centos7 ~]# arping 10.0.1.252 -I ens33
ARPING 10.0.1.252 from 10.0.1.254 ens33
Unicast reply from 10.0.1.252 [00:50:56:A4:65:71]  0.843ms
Unicast reply from 10.0.1.252 [00:50:56:A4:0A:09]  1.034ms
```

这里两条返回信息中的MAC地址不同，说明有两块网卡配置了相同的IP地址。选项`-I`指定发送arp请求的网络接口。
如果刚刚更改了网卡的IP地址，但上游设备(如交换机)的arp表项还是老的，可以使用`arping`来强制刷新：

```sh
[root@centos7 ~]# arping -c3 -I ens33 -s 10.0.1.254 10.0.1.1
ARPING 10.0.1.1 from 10.0.1.254 ens33
Unicast reply from 10.0.1.1 [68:8F:84:01:F1:FF]  19.466ms
Unicast reply from 10.0.1.1 [68:8F:84:01:F1:FF]  2.358ms
Unicast reply from 10.0.1.1 [68:8F:84:01:F1:FF]  24.305ms
Sent 3 probes (1 broadcast(s))
Received 3 response(s)
```
`-c`指定发送arp请求次数，`-s`指定源地址，最后的IP表示发送目标(这里是网关地址)。
### 8、`route`显示或更改路由表

```sh
[root@centos7 ~]# route
Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
10.0.1.0        0.0.0.0         255.255.255.0   U     0      0        0 ens33
link-local      0.0.0.0         255.255.0.0     U     1002   0        0 ens32
link-local      0.0.0.0         255.255.0.0     U     1003   0        0 ens33
172.20.71.0     0.0.0.0         255.255.255.0   U     0      0        0 ens32
192.168.78.0    10.0.1.104      255.255.255.0   UG    0      0        0 ens33
```

其中`Destination`表示目的网段或目标主机；`Gateway`表示网关地址；`Genmask`表示目的网段的掩码；`Flags`表示路由标志：U表示路由是启用(up)的、G表示网关；`Metric`表示目标距离，通常用跳数表示；`Ref`表示路由的引用数；`Use`表示路由查找计数；`Iface`表示此条路由的出口。
选项`-n`表示用数字形式显示目的网段
选项`add`和`del`表示添加或删除一条路由。
选项`-net`和`netmask`表示指定目的网段及掩码。
选项`gw`表示指定网关。
选项`dev IF`表示指定出口网卡

如增加一条到192.56.76.x的路由，使它的出口为ens32：

```sh
route add -net 192.56.76.0 netmask 255.255.255.0 dev ens32
```

如增加一条默认路由，指明它的网关为10.0.1.1

```sh
route add default gw 10.0.1.1
```

如增加一条到172.20.70.0的路由，网关为10.0.1.2

```sh
route add -net 172.20.70.0/24 gw 10.0.1.2
```

如删除默认路由

```sh
route del default
```
### 9、`telnet`提供远程登录功能

由于telnet协议使用明文传输，在要求安全登录的环境中并不适用。现在通常用它来进行网络服务的端口测试：

```sh
[root@centos7 ~]# telnet 10.0.1.251 80
Trying 10.0.1.251...
Connected to 10.0.1.251.
Escape character is '^]'.
^]            #这里按了CTRL+]，也可以按CTRL+C强行退出。
telnet> quit
Connection closed.
```

这里对方的80端口是开启并允许通信的。
当对端端口没有开启时：

```sh
[root@centos7 ~]# telnet 10.0.1.251 81
Trying 10.0.1.251...
telnet: connect to address 10.0.1.251: No route to host
```

当对端拒绝连接时：

```sh
[root@centos7 ~]# telnet 10.0.1.251 8085
Trying 10.0.1.251...
telnet: connect to address 10.0.1.251: Connection refused
```
### 10、`ssh`远程登录程序

```sh
ssh [OPTIONS]... [user@]hostname [command]
```
`ssh`的全称是Secure Shell，在不安全的网络主机间提供安全加密的通信，旨在代替其他远程登录协议。

```sh
[root@centos7 ~]# ssh 10.0.1.253
The authenticity of host '10.0.1.253 (10.0.1.253)' can't be established.
ECDSA key fingerprint is 96:bd:a3:a7:87:09:1b:53:44:4c:9b:b9:5f:b2:97:89.
Are you sure you want to continue connecting (yes/no)? yes   #这里输入yes
Warning: Permanently added '10.0.1.253' (ECDSA) to the list of known hosts.
root@10.0.1.253's password:           #这里输入密码
Last login: Fri Nov 11 09:04:01 2016 from 192.168.78.137
[root@idc-v-71253 ~]#                 #已登录
```

当命令`ssh`后直接跟主机IP时表示使用默认用户`root`登录，如果是首次登录，需要确认添加该主机的认证key，当输入yes后，即会在本机`/root/.ssh/known_hosts`中增加一条该主机的记录，下一次登录时就不用再次确认了。然后需要输入用户密码，通过验证之后，我们就获得了目的主机的一个shell，我们就可以在这个shell中执行命令了。
在新shell中输入`exit`即可退回到原shell。
如果需要频繁登录某主机，但不想每次都输入密码，可以设置免密码登录：

```
[root@centos7 ~]# ssh-keygen -t rsa       
Generating public/private rsa key pair.
Enter file in which to save the key (/root/.ssh/id_rsa): #回车
Enter passphrase (empty for no passphrase): #回车
Enter same passphrase again: #回车
Your identification has been saved in /root/.ssh/id_rsa. #私钥
Your public key has been saved in /root/.ssh/id_rsa.pub. #公钥
The key fingerprint is:
be:c3:d0:02:50:35:35:fe:60:d6:2f:26:96:f0:e1:e6 root@centos7
The key's randomart image is:
+--[ RSA 2048]----+
|   ...o.o        |
|  .    o o       |
|   .  . * .      |
|    .  * = .     |
|     . .S + .    |
|      o=.o .     |
|       +E        |
|        o.       |
|        ..       |
+-----------------+
[root@centos7 ~]# 
[root@centos7 ~]# ssh-copy-id 10.0.1.253
/usr/bin/ssh-copy-id: INFO: attempting to log in with the new key(s), to filter out any that are already installed
/usr/bin/ssh-copy-id: INFO: 1 key(s) remain to be installed -- if you are prompted now it is to install the new keys
root@10.0.1.253's password: 

Number of key(s) added: 1

Now try logging into the machine, with:   "ssh '10.0.1.253'"
and check to make sure that only the key(s) you wanted were added.

[root@centos7 ~]# 
```

其中命令`ssh-keygen`用来生成公钥私钥，选项`-t`指明密钥类型。之后使用命令`ssh-copy-id`将公钥发送至目标主机，这里需要输入目标主机用户密码。然后就可以免密码登录了：

```sh
[root@centos7 ~]# ssh 10.0.1.253
Last login: Fri Nov 11 11:08:37 2016 from 10.0.1.254
[root@idc-v-71253 ~]# 
```

还可以通过ssh远程执行命令：

```
[root@centos7 ~]# ssh 10.0.1.252 "hostname"
root@10.0.1.252's password:  #输入密码
idc-v-71252                  #显示命令结果
[root@centos7 ~]#            #并不登录
```

或者手动将公钥拷贝至目标主机：

```
[root@centos7 ~]# cat /root/.ssh/id_rsa.pub | ssh 10.0.1.252 "cat - >> /root/.ssh/authorized_keys"
root@10.0.1.252's password:          #输入密码
[root@centos7 ~]# ssh 10.0.1.252     #免密登录
Last login: Thu Nov 10 14:42:11 2016 from 192.168.78.135
[root@idc-v-71252 ~]# 
```

选项`-p`为登录指定端口：

```sh
[root@centos7 temp]# ssh -p22 10.0.1.252
Last login: Fri Nov 11 11:44:31 2016 from 10.0.1.254
[root@idc-v-71252 ~]# 
```

端口设置在服务端配置文件`/etc/ssh/sshd_config`中，默认端口号为22，如更改需将`#Port 22`去掉注释并将22更改为需要的端口，然后重启sshd服务`service sshd restart`或`systemctl restart sshd`。
如果需要使用另外的用户登录系统则执行`ssh user@host`我们可以用`tar`命令结合`ssh`和管道，将本地(远程)文件备份到远程(本地)：

```sh
tar zc /home/temp | ssh user@host "tar xz"  #本地temp目录备份到远程
ssh user@host "tar cz /home/temp" | tar xz  #远程temp目录备份到本地
```

选项`-L [bind_address:]port:host:hostport`设置本地端口转发

```sh
[root@centos7 ~]# ssh -L 2222:10.0.1.252:22 10.0.1.253
Last login: Mon Nov 14 10:34:43 2016 from 10.0.1.254
[root@idc-v-71253 ~]#    #注意如果这里exit断开连接，则此转发也将终止。
```

此命令的意思是绑定本地端口`2222`，并将所有发送至此端口的数据通过中间主机`10.0.1.253`转发至目标主机`10.0.1.252`的`22`端口，此时如果用`ssh`登录本机的2222端口，则实际登录的是主机`10.0.1.252````sh
[root@centos7 ~]# ssh -p 2222 127.0.0.1
Last login: Mon Nov 14 10:34:56 2016 from 10.0.1.253
[root@idc-v-71252 ~]# 
```

这里默认绑定的是本机的环回口`127.0.0.1`，如绑定到其他地址，则根据语法设置`bind_address`。
选项`-N`表示不执行命令，只设置端口转发时有用
由于上述端口转发命令`ssh -L 2222:10.0.1.252:22 10.0.1.253`会登录到中间主机，并且退出后端口转发也会终止，使用`-N`选项将不会登录，再配合shell后台执行，将会是一个不错的设置端口转发的选择(但要注意对中间主机需要免密码登录)：

```sh
[root@centos7 ~]# ssh -N -L 2222:10.0.1.252:22 10.0.1.253 &
[1] 12432
[root@centos7 ~]#
```

命令最后的符号`&`表示此命令将在后台执行，返回的信息中`[1]`表示后台命令编号，`12432`表示命令的PID。(关于shell后台命令，以后的文章中会有叙述)
选项`-R [bind_address:]port:host:hostport`设置远程端口转发
如我们在`10.0.1.253`上执行：

```sh
ssh -R 2222:10.0.1.252:22 10.0.1.254
```

然后在`10.0.1.254`上登录：

```sh
[root@centos7 ~]# ssh -p 2222 localhost
Last login: Mon Nov 14 10:40:44 2016 from 10.0.1.253
[root@idc-v-71252 ~]#
```

这里的意思是使远程主机`10.0.1.254`(相对10.0.1.253来说)监听端口`2222`，然后将所有发送至此端口的数据转发至目标主机`10.0.1.252`的端口`22`。之后再在`10.0.1.254`登录本地(localhost)的`2222`端口时，实际通过中间主机`10.0.1.253`登录目标主机`10.0.1.252`。
选项`-o OPTION`指定配置文件(如`/etc/ssh/sshd_config`)内选项
如避免第一次登录时输入`yes`确认，可增加`-o StrictHostKeyChecking=no`。
### 11、`scp`远程复制文件

```
scp [OPTIONS]... [[user@]host1:]file1 ... [[user@]host2:]file2
```
`scp`命令通过`ssh`协议将数据加密传输，和`ssh`登录类似，需要输入远程主机用户密码。
如将远程主机`10.0.1.251`中文件/root/a.txt复制到本地当前目录下：

```
[root@centos7 ~]# scp root@10.0.1.251:/root/a.txt ./
root@10.0.1.251's password: 
a.txt                                       100%  125     0.1KB/s   00:00    
[root@centos7 ~]# 
```

命令会显示传输状态(传输百分比，大小，速度，用时)。
将本地文件复制到远程无非是将源和目的调换位置。
选项`-P`指定远端连接端口(ssh服务端口)，`-o ssh_option`使用ssh选项。
选项`-l limit`传输限速，`limit`单位为Kbit/s。
和命令`cp`类似，选项`-r`表示复制目录，`-p`表示保留文件权限时间等
### 12、`netstat`打印网络信息

选项`-a`显示所有端口信息：

```sh
[root@centos7 ~]# netstat -a
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State      
tcp        0      0 0.0.0.0:ssh             0.0.0.0:*               LISTEN     
tcp        0      0 localhost:smtp          0.0.0.0:*               LISTEN     
tcp        0     52 10.0.1.254:ssh   192.168.78.143:49583    ESTABLISHED
tcp6       0      0 [::]:commplex-main      [::]:*                  LISTEN     
tcp6       0      0 [::]:4243               [::]:*                  LISTEN     
tcp6       0      0 [::]:ssh                [::]:*                  LISTEN     
tcp6       0      0 localhost:smtp          [::]:*                  LISTEN     
raw6       0      0 [::]:ipv6-icmp          [::]:*                  7          
raw6       0      0 [::]:ipv6-icmp          [::]:*                  7          
Active UNIX domain sockets (servers and established)
Proto RefCnt Flags       Type       State         I-Node   Path
unix  2      [ ACC ]     STREAM     LISTENING     12807    /run/systemd/private
unix  2      [ ACC ]     STREAM     LISTENING     12815    /run/lvm/lvmpolld.socket
unix  2      [ ]         DGRAM                    12818    /run/systemd/shutdownd
unix  2      [ ACC ]     STREAM     LISTENING     16403    /var/run/dbus/system_bus_socket
....
```

这里只显示部分信息
选项`-t`显示TCP连接信息
选项`-n`显示IP地址而不进行域名转换
选项`-p`显示PID和程序名

```sh
[root@centos7 ~]# netstat -antp
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      1358/sshd           
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      2162/master         
tcp        0     52 10.0.1.254:22           192.168.78.143:49583    ESTABLISHED 12044/sshd: root@pt 
tcp6       0      0 :::5000                 :::*                    LISTEN      17222/docker-proxy  
tcp6       0      0 :::4243                 :::*                    LISTEN      16983/docker        
tcp6       0      0 :::22                   :::*                    LISTEN      1358/sshd           
tcp6       0      0 ::1:25                  :::*                    LISTEN      2162/master         
[root@centos7 ~]# 
```

其中`Proto`表示协议(包括TCP、UDP等)；`Recv-Q`和`Send-Q`表示接收和发送队列，一般都为0，如果非0则表示本地的接收或发送缓存区有数据等待处理；`Local Address`和`Foreign Address`分别表示本地地址和远端地址；`State`表示连接状态，对应于TCP各种连接状态；`PID/Program name`表示进程号和程序名。
选项`-l`表示只显示状态为`LISTEN`的连接

```sh
[root@centos7 ~]# netstat -ntl
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State      
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN     
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN     
tcp6       0      0 :::5000                 :::*                    LISTEN     
tcp6       0      0 :::4243                 :::*                    LISTEN     
tcp6       0      0 :::22                   :::*                    LISTEN     
tcp6       0      0 ::1:25                  :::*                    LISTEN     
[root@centos7 ~]#
```

选项`-u`表示显示UDP连接信息
选项`-r`表示显示路由信息

```sh
[root@centos7 ~]# netstat -r
Kernel IP routing table
Destination     Gateway         Genmask         Flags   MSS Window  irtt Iface
default         10.0.1.103      0.0.0.0         UG        0 0          0 ens33
10.0.1.0        0.0.0.0         255.255.255.0   U         0 0          0 ens33
172.20.71.0     0.0.0.0         255.255.255.0   U         0 0          0 ens32
192.168.78.0    10.0.1.104      255.255.255.0   UG        0 0          0 ens33
```

选项`-i`显示接口信息

```sh
[root@centos7 ~]# netstat -i
Kernel Interface table
Iface      MTU    RX-OK RX-ERR RX-DRP RX-OVR    TX-OK TX-ERR TX-DRP TX-OVR Flg
ens32     1500 13196107      0     77 0          3246      0      0      0 BMRU
ens33     1500 25312388      0     88 0       2516050      0      0      0 BMRU
lo       65536  2503589      0      0 0       2503589      0      0      0 LRU
```
### 13、`tcpdump`网络抓包工具

命令`tcpdump`捕获某网络接口符合表达式`expression`的数据包，并打印出数据包内容的描述信息。
选项`-i`指定网卡：

```sh
[root@idc-v-71253 ~]# tcpdump -i ens33
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on ens33, link-type EN10MB (Ethernet), capture size 65535 bytes
15:41:59.121948 IP 10.0.1.108.3693 > 239.100.1.1.websm: UDP, length 58
15:41:59.122191 IP 10.0.1.109.35673 > 239.100.1.1.websm: UDP, length 57
15:41:59.128282 IP 10.0.1.253.ssh > 192.168.78.143.51694: Flags [P.], seq 749565300:749565496, ack 3522345564, win 255, length 196
15:41:59.134127 IP 192.168.78.143.51694 > 10.0.1.253.ssh: Flags [.], ack 196, win 3977, length 0
15:41:59.140319 ARP, Request who-has 10.0.1.31 tell 10.0.1.102, length 46
15:41:59.168328 ARP, Request who-has 10.0.1.37 tell 10.0.1.102, length 46
15:41:59.262235 ARP, Request who-has 192.168.10.150 tell 192.168.10.151, length 46
15:41:59.622090 IP 10.0.1.108.3693 > 239.100.1.1.websm: UDP, length 58
15:41:59.622178 IP 10.0.1.109.35673 > 239.100.1.1.websm: UDP, length 57
....
```

启动命令之后显示出可以使用`-v`或`-vv`显示更详细的信息，开始从ens33捕获数据包。输出显示出各个发送或接收数据包包头信息(包括ARP、IP、TCP、UDP等等协议)。此命令并未指定`expression`，所以默认将捕获所有数据包。
如果需要将数据包捕获然后通过其他程序(如wireshark)分析，可以使用选项`-w file`将数据写入文件，同时还需要使用选项`-s 0`指定能够捕获的数据包大小为65535字节，以避免数据包被截断而无法被分析。
真实环境中，流经网卡的数据包量是巨大的。可以使用表达式来对数据包进行过滤，对于每个数据包，都要经过表达式的过滤，只有表达式的值为true时，才会输出。
`expression`中可以包含一到多个关键字指定的条件，可以使用`and`(或`&&`)、`or`(或`||`)、`not`(或`!`)和括号`()`表示各个关键字间的逻辑关系，可以用`>`、`<`表示比较，还可以进行计算。其中关键字包括：
`type`类型关键字，如`host`、`net`、`port`和`portrange`，分别表示主机、网段、端口号、端口段。
`direction`方向关键字，如`src`、`dst`分别表示源和目的。
`proto`协议关键字，如`fddi`、`arp`、`ip`、`tcp`、`udp`等分别表示各种网络协议。
由于篇幅所限，下面的例子中将只描述选项和表达式所起到的作用，不再解释输出内容：

```sh
tcpdump -i ens33 dst host 10.0.1.251 
#监视所有从端口ens33发送到主机10.0.1.251的数据包，主机也可以是主机名
tcpdump -i eth0 host ! 211.161.223.70 and ! 211.161.223.71 and dst port 80 
#监听端口eth0，抓取不是来自或去到主机211.161.223.70和211.161.223.71并且目标端口为80的包
tcpdump tcp port 23 host 210.27.48.1 
#获取主机210.27.48.1接收或发出的telnet包
tcpdump 'tcp port 80 and (((ip[2:2] - ((ip[0]&0xf)<<2)) - ((tcp[12]&0xf0)>>2)) != 0) and src net (183.60.190 or 122.13.220)' -s0 -i eth0 -w ipdump
#抓取源或目的端口是80,且源网络是（183.60.190.0/24 或者 122.13.220.0/24），并且含有数据,而不是SYN,FIN以及ACK-only等不含数据的TCP数据包写入文件ipdump
#注意这里表达式使用单引号引起来以避免其中的特殊字符被shell解析而造成语法错误
tcpdump 'tcp[tcpflags] & (tcp-syn|tcp-fin) != 0 and ! src and dst net 10.0.0'
#只打印TCP的开始和结束包(SYN和FIN标记)，并且源和目标网段均不是10.0.0.0/24
tcpdump 'gateway 10.0.1.1 and ip[2:2] > 576' 
#表示抓取发送至网关10.0.1.1并且大于576字节的IP数据包
```

网络相关命令内容较多，下一篇将继续介绍。

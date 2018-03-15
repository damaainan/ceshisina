## linux基础命令介绍十五：推陈出新

来源：[https://segmentfault.com/a/1190000007946958](https://segmentfault.com/a/1190000007946958)

本文介绍`ip`、`ss`、`journalctl`和`firewall-cmd`，它们旨在代替linux中原有的一些命令或服务。
## 1、`ip` 

```sh
ip [OPTIONS] OBJECT COMMAND
```
`ip`是iproute2软件包里面的一个强大的网络配置工具，它能够替代一些传统的网络管理工具，例如`ifconfig`、`route`等，使用权限为超级用户。
`OPTIONS`是修改ip行为或改变其输出的选项。
`OBJECT`是要获取信息的对象。包括：

```sh
address   表示设备的协议(IPv4或IPv6)地址
link      表示网络设备
monitor   表示监控网络连接信息
neighbour 表示管理ARP缓存表
netns     表示管理网络命名空间
route     表示路由表接口
tunnel    表示IP隧道
....
```

对象名可以是全称或简写格式，比如`address`可以简写为`addr`或`a`。
`COMMAND`设置针对指定对象执行的操作，它和对象的类型有关。
### address

如显示网卡`ens33`的信息：

```sh
[root@centos7 ~]# ip addr show ens33
3: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 00:50:56:a4:a9:16 brd ff:ff:ff:ff:ff:ff
    inet 10.0.1.254/24 brd 10.0.1.255 scope global ens33
       valid_lft forever preferred_lft forever
    inet6 fe80::250:56ff:fea4:a916/64 scope link 
       valid_lft forever preferred_lft forever
```

选项`-s`表示输出更多的信息

```sh
[root@centos7 ~]# ip -s addr show ens33
3: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 00:50:56:a4:a9:16 brd ff:ff:ff:ff:ff:ff
    inet 10.0.1.254/24 brd 10.0.1.255 scope global ens33
       valid_lft forever preferred_lft forever
    inet6 fe80::250:56ff:fea4:a916/64 scope link 
       valid_lft forever preferred_lft forever
    RX: bytes  packets  errors  dropped overrun mcast   
    133518854  1415841  0       0       0       0       
    TX: bytes  packets  errors  dropped carrier collsns 
    14033474   59479    0       0       0       0 
```

为`ens33`增加一个新地址

```sh
[root@centos7 ~]# ip addr add 192.168.0.193/24 dev ens33
[root@centos7 ~]# ip a sh ens33
3: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 00:50:56:a4:a9:16 brd ff:ff:ff:ff:ff:ff
    inet 10.0.1.254/24 brd 10.0.1.255 scope global ens33
       valid_lft forever preferred_lft forever
    inet 192.168.0.193/24 scope global ens33
       valid_lft forever preferred_lft forever
    inet6 fe80::250:56ff:fea4:a916/64 scope link 
       valid_lft forever preferred_lft forever
#删除
[root@centos7 ~]# ip addr del 192.168.0.193/24 dev ens33
```
### neighbour

如查看arp表项(neighbour可以简写为neigh或n)

```sh
[root@centos7 ~]# ip neigh
172.20.71.253 dev ens32 lladdr 68:8f:84:03:71:e6 STALE
10.0.1.102 dev ens33 lladdr 00:50:56:a4:18:9a STALE
10.0.1.1 dev ens33 lladdr 68:8f:84:01:f1:ff STALE
10.0.1.103 dev ens33 lladdr 00:1c:7f:3b:da:b0 STALE
10.0.1.104 dev ens33 lladdr 00:50:56:a4:37:a7 DELAY
10.0.1.252 dev ens33 lladdr 00:50:56:a4:65:71 STALE
```
`neighbour`可以使用的COMMAND包括`add`添加、`change`修改、`replace`替换、`delete`删除、`flush`清除等。
如在设备ens33上为地址10.0.1.253添加一个永久的ARP条目：

```sh
[root@centos7 ~]# ip nei add 10.0.1.253 lladdr 78:A3:51:14:F7:98 dev ens33 nud permanent
[root@centos7 ~]# ip nei show dev ens33
10.0.1.103 lladdr 00:1c:7f:3b:da:b0 STALE
10.0.1.1 lladdr 68:8f:84:01:f1:ff STALE
10.0.1.104 lladdr 00:50:56:a4:37:a7 REACHABLE
10.0.1.102 lladdr 00:50:56:a4:18:9a STALE
10.0.1.253 lladdr 78:a3:51:14:f7:98 PERMANENT
10.0.1.252 lladdr 00:50:56:a4:65:71 STALE
```
### link

如更改ens33的MTU(最大传输单元)的值为1600

```sh
[root@centos7 ~]# ip link set dev ens33 mtu 1600
[root@centos7 ~]# ip link show dev ens33        
3: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1600 qdisc pfifo_fast state UP mode DEFAULT qlen 1000
    link/ether 00:50:56:a4:a9:16 brd ff:ff:ff:ff:ff:ff
```

关闭设备ens32

```sh
[root@centos7 ~]# ip link set dev ens32 down 
[root@centos7 ~]# ip li ls dev ens32
2: ens32: <BROADCAST,MULTICAST> mtu 1500 qdisc pfifo_fast state DOWN mode DEFAULT qlen 1000
    link/ether 00:50:56:a4:f6:f8 brd ff:ff:ff:ff:ff:ff
```

创建一个关联到ens32的网桥

```sh
[root@centos7 ~]# ip link add link ens32 name br1 type bridge
[root@centos7 ~]# ip link show dev br1
8: br1: <BROADCAST,MULTICAST> mtu 1500 qdisc noop state DOWN mode DEFAULT 
    link/ether 0e:00:3a:f2:fa:ee brd ff:ff:ff:ff:ff:ff
#启用
[root@centos7 ~]# ip link set dev br1 up
#停用
[root@centos7 ~]# ip link set dev br1 down
#删除
[root@centos7 ~]# ip link del dev br1
```
### route

如显示路由表(这里使用了命令`column -t`对输出进行了格式化)

```sh
[root@centos7 ~]# ip route show|column -t
default          via  10.0.1.103       dev    ens33   proto  static  metric  100
10.0.1.0/24      dev  ens33            proto  kernel  scope  link    src     10.0.1.254     metric  100
172.20.71.0/24   dev  ens32            proto  kernel  scope  link    src     172.20.71.254  metric  100
192.168.78.0/24  via  10.0.1.104       dev    ens33
```

如添加一条到192.168.0.0/16下一跳是10.0.1.101的路由

```sh
[root@centos7 ~]# ip route add 192.168.0.0/16 via 10.0.1.101 dev ens33
[root@centos7 ~]# ip route show|column -t
default          via  10.0.1.103       dev    ens33   proto  static  metric  100
10.0.1.0/24      dev  ens33            proto  kernel  scope  link    src     10.0.1.254     metric  100
172.20.71.0/24   dev  ens32            proto  kernel  scope  link    src     172.20.71.254  metric  100
192.168.0.0/16   via  10.0.1.101       dev    ens33
192.168.78.0/24  via  10.0.1.104       dev    ens33
#删除
[root@centos7 ~]# ip route del 192.168.0.0/16
```

还可以使用`change`、`replace`等表示改变/替换原有路由条目。
如获取单条路由信息

```sh
[root@centos7 ~]# ip rou get 10.0.1.0/24
broadcast 10.0.1.0 dev ens33  src 10.0.1.254 
    cache <local,brd>
```
## 2、`ss````sh
ss [options] [FILTER]
```
`ss`命令可以用来获取socket统计信息，它可以显示和`netstat`类似的内容。但`ss`的优势在于它能够显示更多详细的有关TCP和连接状态的信息，而且比netstat更高效。当服务器的socket连接数量变得非常大时，无论是使用netstat命令还是直接`cat /proc/net/tcp`，执行速度都会很慢。`ss`命令利用了TCP协议栈中`tcp_diag`，`tcp_diag`是一个用于分析统计的模块，可以获得linux内核的第一手信息，这确保了ss的快捷高效。
选项`-a`表示显示所有连接状态信息
选项`-t`表示显示TCP sockets
选项`-u`表示显示UDP sockets
选项`-n`表示不转换数字为服务名
选项`-p`表示显示进程

```sh
[root@centos7 ~]# ss -antp|column -t
State   Recv-Q  Send-Q  Local Address:Port      Peer Address:Port
LISTEN  0       128     *:22                    *:*                   users:(("sshd",pid=1355,fd=3))
LISTEN  0       100     127.0.0.1:25            *:*                   users:(("master",pid=2214,fd=13))
ESTAB   0       0       10.0.1.254:22           192.168.78.141:50332  users:(("sshd",pid=18294,fd=3))
ESTAB   0       52      10.0.1.254:22           192.168.78.178:51667  users:(("sshd",pid=18433,fd=3))
LISTEN  0       128     :::5000                 :::*                  users:(("exe",pid=5908,fd=7))
LISTEN  0       128     :::22                   :::*                  users:(("sshd",pid=1355,fd=4))
LISTEN  0       100     ::1:25                  :::*                  users:(("master",pid=2214,fd=14))
```

选项`-l`表示只显示监听状态的sockets

```sh
[root@centos7 ~]# ss -lt|column -t
State   Recv-Q  Send-Q  Local Address:Port  Peer Address:Port
LISTEN  0       128     *:ssh               *:*
LISTEN  0       100     127.0.0.1:smtp      *:*
LISTEN  0       128     :::commplex-main    :::*
LISTEN  0       128     :::ssh              :::*
LISTEN  0       100     ::1:smtp            :::*
```

选项`-s`表示显示汇总信息

```sh
[root@centos7 ~]# ss -s
Total: 270 (kernel 575)
TCP:   8 (estab 1, closed 1, orphaned 0, synrecv 0, timewait 0/0), ports 0

Transport Total     IP        IPv6
*         575       -         -        
RAW       2         0         2        
UDP       0         0         0        
TCP       7         3         4        
INET      9         3         6        
FRAG      0         0         0 
```

还可以使用`state STATE-FILTER [EXPRESSION]`指定过滤格式
如显示源或目的端口为8080，状态为`established`的连接：

```sh
ss state established '( dport = :8080 or sport = :8080 )'
```

如来自193.233.7/24，状态为fin-wait-1的http或https连接

```sh
ss state fin-wait-1 '( sport = :http or sport = :https )' dst 193.233.7/24
```
## 3、`journalctl````sh
journalctl [OPTIONS...] [MATCHES...]
```

在基于`systemd`的系统中，可以使用一个新工具`Journal`代替原来的系统服务`Syslog`来记录日志。关于`Journal`优越性就不在这里叙述了，我们来直接看它怎么使用。
`Journal`服务的配置文件是`/etc/systemd/journald.conf`，在默认配置中，`Journal`日志保存在目录`/run/log/journal`内(tmpfs内存文件系统)，系统重启将不会保留，可以手动将日志刷到(通过命令`journalctl --flush`)磁盘文件系统上(`/var/log/journal`内)。
`Journal`服务随系统启动而启动，默认会记录从开机到关机全过程的内核和应用程序日志

```sh
#查看服务状态
[root@centos7 ~]# systemctl status -l systemd-journald
● systemd-journald.service - Journal Service
   Loaded: loaded (/usr/lib/systemd/system/systemd-journald.service; static; vendor preset: disabled)
   Active: active (running) since 二 2016-12-20 11:15:22 CST; 1 weeks 0 days ago
     Docs: man:systemd-journald.service(8)
           man:journald.conf(5)
 Main PID: 539 (systemd-journal)
   Status: "Processing requests..."
   CGroup: /system.slice/systemd-journald.service
           └─539 /usr/lib/systemd/systemd-journald

12月 20 11:15:22 centos7 systemd-journal[539]: Runtime journal is using 8.0M (max allowed 391.1M, trying to leave 586.7M free of 3.8G available → current limit 391.1M).
12月 20 11:15:22 centos7 systemd-journal[539]: Runtime journal is using 8.0M (max allowed 391.1M, trying to leave 586.7M free of 3.8G available → current limit 391.1M).
12月 20 11:15:22 centos7 systemd-journal[539]: Journal started
12月 20 11:15:22 centos7 systemd-journal[539]: Runtime journal is using 8.0M (max allowed 391.1M, trying to leave 586.7M free of 3.8G available → current limit 391.1M).
```

当命令`journalctl`不带任何选项时会分页显示系统的所有日志(从本次开机到现在时间)
选项`-k`表示显示内核kernel日志
选项`-u UNIT`表示显示指定服务单元UNIT的日志

```sh
#如上一篇中配置的计时器(ping252.timer)和服务(ping252.service)日志
[root@centos7 ~]# journalctl -u ping252.timer
-- Logs begin at 二 2016-12-20 11:15:19 CST, end at 二 2016-12-27 20:39:54 CST. --
12月 23 14:27:26 centos7 systemd[1]: Started ping 252 every 30s.
12月 23 14:27:26 centos7 systemd[1]: Starting ping 252 every 30s.
12月 23 14:36:57 centos7 systemd[1]: Stopped ping 252 every 30s.
....
[root@centos7 ~]# journalctl -u ping252
-- Logs begin at 二 2016-12-20 11:15:19 CST, end at 二 2016-12-27 20:41:34 CST. --
12月 23 14:28:28 centos7 systemd[1]: Started ping 252.
12月 23 14:28:28 centos7 systemd[1]: Starting ping 252...
12月 23 14:28:28 centos7 systemd[11428]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
12月 23 14:28:28 centos7 systemd[1]: ping252.service: main process exited, code=exited, status=203/EXEC
12月 23 14:28:28 centos7 systemd[1]: Unit ping252.service entered failed state.
12月 23 14:28:28 centos7 systemd[1]: ping252.service failed.
12月 23 14:29:03 centos7 systemd[1]: Started ping 252.
....
```

选项`-r`表示反向输出日志(从当前时间到本次开机)
选项`-n N`表示输出最新的N行日志

```sh
[root@centos7 ~]# journalctl -n 5 -u ping252
-- Logs begin at 二 2016-12-20 11:15:19 CST, end at 二 2016-12-27 20:48:54 CST. --
12月 23 17:27:12 centos7 systemd[1]: Starting 252...
12月 23 17:29:12 centos7 systemd[1]: Started 252.
12月 23 17:29:12 centos7 systemd[1]: Starting 252...
12月 23 17:31:12 centos7 systemd[1]: Started 252.
12月 23 17:31:12 centos7 systemd[1]: Starting 252...
```

选项`-f`表示显示最新的10行日志并继续等待输出新日志(类似于命令`tail -f`)
选项`-p n`表示过滤输出指定级别的日志，其中n的值可以是：

```sh
0 表示 emerg
1 表示 alert
2 表示 crit
3 表示 err
4 表示 warning
5 表示 notice
6 表示 info
7 表示 debug
```

如

```sh
[root@centos7 ~]# journalctl -u ping252 -p 3
-- Logs begin at 二 2016-12-20 11:15:19 CST, end at 二 2016-12-27 21:13:34 CST. --
12月 23 14:28:28 centos7 systemd[11428]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
12月 23 14:29:03 centos7 systemd[11442]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
12月 23 14:30:32 centos7 systemd[11452]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
```

选项`--since=`和`--until=`表示显示晚于指定时间(--since=)的日志、显示早于指定时间(--until=)的日志。时间格式如上一篇`systemd.timer`所示：

```
[root@centos7 ~]# journalctl -u ping252 --since "2016-12-20 11:15:19" --until "now" -p 3              
-- Logs begin at 二 2016-12-20 11:15:19 CST, end at 二 2016-12-27 21:37:14 CST. --
12月 23 14:28:28 centos7 systemd[11428]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
12月 23 14:29:03 centos7 systemd[11442]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
12月 23 14:30:32 centos7 systemd[11452]: Failed at step EXEC spawning /root/temp/ping252.sh: Exec format error
```

选项`--disk-usage`表示显示日志磁盘占用量

```sh
[root@centos7 ~]# journalctl --disk-usage
Archived and active journals take up 104.8M on disk.
```

选项`--vacuum-size=`用于设置日志最大磁盘使用量（值可以使用K、M、G、T等后缀）。
选项`--vacuum-time=`用于清除指定时间之前的日志（可以使用"s", "m", "h", "days", "weeks", "months", "years" 等后缀）

```sh
[root@centos7 ~]# journalctl --vacuum-time="1 days"
Deleted archived journal /run/log/journal/9......2e.journal (48.0M).
Deleted archived journal /run/log/journal/9......a1.journal (48.8M).
Vacuuming done, freed 96.8M of archived journals on disk.
```

选项`-o`表示控制输出格式，可以带一个如下参数：

```sh
short 默认格式，和传统的syslog格式相似，每条日志一行
short-iso 和short类似，但显示ISO 8601时间戳
short-precise 和short类似，只是将时间戳字段的秒数精确到微秒级别
short-monotonic 和short类似，只是将时间戳字段的零值从内核启动时开始计算。
short-unix 和short类似，只是将时间戳字段显示为从"UNIX时间原点"(1970-1-1 00:00:00 UTC)以来的秒数。 精确到微秒级别。
verbose 以结构化的格式显示每条日志的所有字段。
export 将日志序列化为二进制字节流(大部分依然是文本)以适用于备份与网络传输。
json 将日志项按照JSON数据结构格式化， 每条日志一行。
json-pretty 将日志项按照JSON数据结构格式化， 但是每个字段一行， 以便于人类阅读。
json-sse 将日志项按照JSON数据结构格式化，每条日志一行，但是用大括号包围。
cat 仅显示日志的实际内容， 而不显示与此日志相关的任何元数据(包括时间戳)。
```
## 4、`firewall-cmd`同`iptables`一样，`firewalld`也通过内核的netfilter来实现防火墙功能([netfilter的简介][0])，比`iptables`先进的地方在于，`firewalld`可以动态修改单条规则，而不需要像iptables那样，在修改了规则之后必须全部刷新才可以生效。而且`firewalld`在使用上更人性化，不需要理解netfilter的原理也能实现大部分功能。
`firewalld`需要开启守护进程，查看防火墙服务状态：

```sh
[root@idc-v-71252 ~]# systemctl status firewalld
● firewalld.service - firewalld - dynamic firewall daemon
   Loaded: loaded (/usr/lib/systemd/system/firewalld.service; enabled; vendor preset: enabled)
   Active: active (running) since 三 2016-12-14 14:07:04 CST; 1 weeks 4 days ago
 Main PID: 898 (firewalld)
   CGroup: /system.slice/firewalld.service
           └─898 /usr/bin/python -Es /usr/sbin/firewalld --nofork --nopid

12月 14 14:07:03 centos7 systemd[1]: Starting firewalld - dynamic firewall daemon...
12月 14 14:07:04 centos7 systemd[1]: Started firewalld - dynamic firewall daemon.
```

或者通过自身的`firewall-cmd`查看

```sh
[root@centos7 ~]# firewall-cmd --stat
running
[root@centos7 ~]# 
```
`firewalld`的配置文件以xml格式为主(主配置文件firewalld.conf除外)，它们有两个存储位置：

```sh
1、/etc/firewalld
2、/usr/lib/firewalld
```

使用时的规则是这样的：当需要一个文件时，`firewalld`会首先到第一个目录中查找，如果可以找到，那么就直接使用，否则会继续到第二个目录中查找。不推荐在目录`/usr/lib/firewalld`中直接修改配置文件，最好是在`/usr/lib/firewalld`中复制一份配置文件到`/etc/firewalld`的相应目录中，然后进行修改。这样，在恢复默认配置时，直接删除`/etc/firewalld`中的文件即可。
`firewalld`中引入了两个概念：`service`(服务)和`zone`(区域)。
`service`通用配置文件(位于目录`/usr/lib/firewalld/services`内)中定义了服务与端口的映射，`firewalld`在使用时可以直接引用服务名而不是像`iptables`那样引用端口号(就像DNS服务将域名和IP地址做了映射)；

默认时`firewalld`提供了九个`zone`配置文件，位于`/usr/lib/firewalld/zones`中：

```sh
[root@centos7 ~]# ls /usr/lib/firewalld/zones
block.xml  dmz.xml  drop.xml  external.xml  home.xml  internal.xml  public.xml  trusted.xml  work.xml
```

每个文件中定义了一套规则集，或者说判断方案。`firewalld`通过判断配置文件中如下三个地方来决定具体使用哪套方案来过滤包：

```sh
1、source 原地址
2、interface 接收包的网卡
3、默认zone(可在/etc/firewalld/firewalld.conf中配置)
```

这三个优先级按顺序依次降低，也就是说如果按照`source`可以找到就不会再按`interface`去查找，如果前两个都找不到才会使用第三个。
### zone
`public.xml`内容：

```sh
[root@centos7 ~]# cat /usr/lib/firewalld/zones/public.xml 
<?xml version="1.0" encoding="utf-8"?>
<zone>
  <short>Public</short>
  <description>For use in public areas. You do not trust the other computers on networks to not harm your computer. Only selected incoming connections are accepted.</description>
  <service name="ssh"/>
  <service name="dhcpv6-client"/>
</zone>
[root@centos7 ~]# 
```
`zone`配置文件中可以配置的项包括：

```sh
zone 定义zone起始和结束的标签，只能用于zone配置文件，可以设置两个属性：
    version 版本
    target 本zone的默认规则，包括四个可选值：default、ACCEPT、%%REJECT%%、DROP，如果不设置则表示默认值default，如果默认规则不是default，除source和interface两个配置项以外的其他规则项都将被忽略，而直接跳转到默认规则。
short 区域简短描述
description 区域描述
interface 绑定一个本地接口到本zone
source 绑定一个或一组源地址到本zone
service 表示一个服务
port 端口，使用port可以不通过service而直接对端口进行设置
icmp-block icmp报文阻塞，可以按照icmp类型进行设置
masquerade ip地址伪装，也就是按照源网卡地址进行NAT转发
forward-port 端口转发
rule 自定义规则
```
`firewalld`默认区域中，`ACCEPT`用在`trusted`区域，`%%REJECT%%`用在`block`区域，`DROP`用在`drop`区域。使用时可以复制一份需要的文件至/etc/firewalld/zones中，然后将需要的源地址或接口配置在相应的文件中。

配置source.

source在zone的xml文件中的格式为

```sh
<zone>
    <source address="address[/mask]"/>
</zone>
```

需要注意的是相同的`source`项只能在一个`zone`中进行配置，也就是说同一个源地址只能对应一个`zone`，另外，直接编辑xml文件之后需要执行命令`firewall-cmd --reload`才能生效。

当然也可以使用`firewall-cmd`命令进行配置(可选项`--permanent`表示是否保存到配置文件中，使用后需要`--reload`才能生效；`--zone`表示指定zone，不指定表示使用默认zone)：

```sh
#列出指定zone的所有绑定的source地址
firewall-cmd [--permanent] [--zone=zone] --list-sources
#查询指定zone是否跟指定source地址进行了绑定
firewall-cmd [--permanent] [--zone=zone] --query-source=source[/mask]
#将一个source地址绑定到指定的zone(只可绑定一次，第二次绑定到不同的zone会报错)
firewall-cmd [--permanent] [--zone=zone] --add-source=source[/mask]
#改变source地址所绑定的zone
firewall-cmd [--zone=zone] --change-source=source[/mask]
#删除source地址跟zone的绑定
firewall-cmd [--permanent] [--zone=zone] --remove-source=source[/mask]
```

如将源地址192.168.0.0/16添加至默认zone

```sh
[root@centos7 zones]# firewall-cmd --add-source=192.168.0.0/16
success
[root@centos7 zones]# firewall-cmd --list-sources
192.168.0.0/16
[root@centos7 zones]# firewall-cmd --remove-source=192.168.0.0/16
success
[root@centos7 zones]#
```

配置interface.

同source配置项相同，同一个interface只能对应一个zone。interface在zone的xml文件中的格式为：

```sh
<zone>
    <interface name="string"/>
</zone>
```

还可以将`zone`配置在网卡配置文件(ifcfg-*文件)中，使接口绑定到指定zone：

```sh
ZONE=public
```

相应命令：

```sh
#列出指定zone的绑定接口
firewall-cmd [--permanent] [--zone=zone] --list-interfaces
#绑定接口到指定zone
firewall-cmd [--permanent] [--zone=zone] --add-interface=interface
#改变接口绑定zone
firewall-cmd [--zone=zone] --change-interface=interface
#查询接口是否和指定zone绑定
firewall-cmd [--permanent] [--zone=zone] --query-interface=interface
#删除绑定
firewall-cmd [--permanent] [--zone=zone] --remove-interface=interface
```

如将ens32移除出默认zone

```sh
[root@centos7 zones]# firewall-cmd --list-interfaces
ens32 ens33
[root@centos7 zones]# 
[root@centos7 zones]# firewall-cmd --remove-interface=ens32
success
[root@centos7 zones]# firewall-cmd --list-interfaces
ens33
[root@centos7 zones]#
```

配置service

同一个service可以配置到多个不同的zone中

```sh
<zone>
    <service name="string"/>
</zone>
```

相应命令：

```sh
firewall-cmd [--permanent] [--zone=zone] --list-services
#--timeout=seconds表示生效时间，过期后自动删除。不能和--permanent一起使用
firewall-cmd [--permanent] [--zone=zone] --add-service=service [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-service=service
firewall-cmd [--permanent] [--zone=zone] --query-service=service    
#列出所有可用服务
firewall-cmd --get-service
```

如增加http服务到默认zone

```sh
[root@centos7 zones]# firewall-cmd --add-service=http
success
[root@centos7 zones]# firewall-cmd --remove-service=http
success
[root@centos7 zones]# 
```

配置port.

需要同时指定协议和端口号，端口号可以用-连接表示一个范围。

```sh
<zone>
    <port port="portid[-portid]" protocol="tcp|udp"/>
</zone>
```

命令

```sh
firewall-cmd [--permanent] [--zone=zone] --list-ports
firewall-cmd [--permanent] [--zone=zone] --add-port=portid[-portid]/protocol [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-port=portid[-portid]/protocol
firewall-cmd [--permanent] [--zone=zone] --query-port=portid[-portid]/protocol
```

如限时10秒允许80端口的访问

```sh
[root@centos7 zones]# firewall-cmd --add-port=80/tcp --timeout=10
success
[root@centos7 zones]# 
```

配置icmp-block.

```sh
<zone>
    <icmp-block name="string"/>
</zone>
```

string处配置需要阻塞的ICMP类型
命令

```sh
#列出所有ICMP类型
firewall-cmd --get-icmptypes
firewall-cmd [--permanent] [--zone=zone] --list-icmp-blocks
firewall-cmd [--permanent] [--zone=zone] --add-icmp-block=icmptype [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-icmp-block=icmptype
firewall-cmd [--permanent] [--zone=zone] --query-icmp-block=icmptype
```

如禁止ping

```sh
[root@centos7 zones]# firewall-cmd --add-icmp-block=echo-request
success
#在另一台机器ping本机：
[root@idc-v-71252 ~]# ping 10.0.1.254
PING 10.0.1.254 (10.0.1.254) 56(84) bytes of data.
From 10.0.1.254 icmp_seq=1 Destination Host Prohibited
From 10.0.1.254 icmp_seq=2 Destination Host Prohibited
From 10.0.1.254 icmp_seq=3 Destination Host Prohibited
^C
#取消
[root@centos7 zones]# firewall-cmd --remove-icmp-block=echo-request
success
[root@centos7 zones]#
```

配置masquerade.

```sh
<zone>
    <masquerade/>
</zone>
```

NAT转发，将接收到的请求的源地址设置为转发请求网卡的地址。
命令

```sh
firewall-cmd [--permanent] [--zone=zone] --add-masquerade [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-masquerade
firewall-cmd [--permanent] [--zone=zone] --query-masquerade
```

配置forward-port.

```sh
<zone>
    <forward-port port="portid[-portid]" protocol="tcp|udp" [to-port="portid[-portid]"] [to-addr="ipv4address"]/>
</zone>
```

命令(其中转发规则`FORWARD`为`port=portid[-portid]:proto=protocol[:toport=portid[-portid]][:toaddr=address[/mask]]`)

```sh
firewall-cmd [--permanent] [--zone=zone] --list-forward-ports
firewall-cmd [--permanent] [--zone=zone] --add-forward-port=FORWARD [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-forward-port=FORWARD
firewall-cmd [--permanent] [--zone=zone] --query-forward-port=FORWARD
```

如将80端口接收到的请求转发到本机的8080端口(如需转发至其他地址则添加`:to-addr=address[/mask]`)：

```sh
[root@centos7 zones]# firewall-cmd --add-forward-port=port=80:proto=tcp:toport=8080
success
[root@centos7 zones]# firewall-cmd --list-forward-ports
port=80:proto=tcp:toport=8080:toaddr=
[root@centos7 zones]# firewall-cmd --remove-forward-port=port=80:proto=tcp:toport=8080
success
[root@centos7 zones]# 
```

配置rule.
`rule`可以用来定义一条复杂的规则，其在文件中定义如下：

```sh
<zone>
    <rule [family="ipv4|ipv6"]>
               [ <source address="address[/mask]" [invert="bool"]/> ]
               [ <destination address="address[/mask]" [invert="bool"]/> ]
               [
                 <service name="string"/> |
                 <port port="portid[-portid]" protocol="tcp|udp"/> |
                 <protocol value="protocol"/> |
                 <icmp-block name="icmptype"/> |
                 <masquerade/> |
                 <forward-port port="portid[-portid]" protocol="tcp|udp" [to-port="portid[-portid]"] [to-addr="address"]/>
               ]
               [ <log [prefix="prefixtext"] [level="emerg|alert|crit|err|warn|notice|info|debug"]/> [<limit value="rate/duration"/>] </log> ]
               [ <audit> [<limit value="rate/duration"/>] </audit> ]
               [ <accept/> | <reject [type="rejecttype"]/> | <drop/> ]
     </rule>
</zone>
```

这里的`rule`就相当于使用`iptables`时的一条规则。
命令

```sh
firewall-cmd [--permanent] [--zone=zone] --list-rich-rules
firewall-cmd [--permanent] [--zone=zone] --add-rich-rule='rule' [--timeout=seconds]
firewall-cmd [--permanent] [--zone=zone] --remove-rich-rule='rule'
firewall-cmd [--permanent] [--zone=zone] --query-rich-rule='rule'
```

如源地址为192.168.10.0/24的http连接都drop掉：

```sh
[root@centos7 zones]# firewall-cmd --add-rich-rule='rule family="ipv4" source address="192.168.10.0/24" service name="http" drop'
success
[root@centos7 zones]# firewall-cmd --query-rich-rule='rule family="ipv4" source address="192.168.10.0/24" service name="http" drop'
yes
[root@centos7 zones]# firewall-cmd --remove-rich-rule='rule family="ipv4" source address="192.168.10.0/24" service name="http" drop'
success
[root@centos7 zones]# 
```
### service
`service`配置文件格式为：

```sh
<service [version="string"]>
    [<short>short description</short>]
    [<description>description</description>]
    [<port [port="portid[-portid]"] protocol="protocol"/>]
    [<module name="helper"/>]
    [<destination ipv4="address[/mask]" ipv6="address[/mask]"/>]
</service>
```

其中最重要的配置项是`port`，表示将端口绑定到指定服务，当该端口收到包时即表示对该服务的请求，防火墙从而到对应的zone中去查找规则，判断是否放行。

一个service中可以配置多个port项，单个port项中可以配置单个端口，也可以是一个端口段，比如port=80-85表示80到85之间的端口号。
`destination`表示根据目的地址绑定服务，可以是ipv4地址也可以是ipv6地址，可以使用掩码。
`module`用来设置netfilter连接跟踪模块
`firewall-cmd`提供了两个选项用于创建和删除service，`--new-service`和`--delete-service`。不过直接编辑xml文件是更好的选择。
### direct

直接使用防火墙的过滤规则，配置文件为`/etc/firewalld/direct.xml`(可以手动创建或通过命令生成)，文件结构如下：

```sh
<?xml version="1.0" encoding="utf-8"?>
<direct>
   [ <chain ipv="ipv4|ipv6" table="table" chain="chain"/> ]
   [ <rule ipv="ipv4|ipv6" table="table" chain="chain" priority="priority"> args </rule> ]
   [ <passthrough ipv="ipv4|ipv6"> args </passthrough> ]
</direct>
```

可以在配置文件中直接配置`iptables`规则，其中：

```sh
ipv 表示ip版本
table 表示iptables中的table
chain 表示iptables中的chain，可以是自定义的
priority 优先级，类似于iptables中规则的前后顺序，数字越小优先级越高
args  表示具体规则，也可以是自定义的chain
```

如自定义一个叫blacklist的链，然后将所有来自192.168.1.0/24和192.168.5.0/24的数据包都指向了这个链，指定这个链的规则：首先使用'blacklisted: '前缀进行日志记录(每分钟记录一次)，然后drop。

```xml
<?xml version="1.0" encoding="utf-8"?>
<direct>
    <chain ipv="ipv4" table="raw" chain="blacklist"/>
    <rule ipv="ipv4" table="raw" chain="PREROUTING" priority="0">-s 192.168.1.0/24 -j blacklist</rule>
    <rule ipv="ipv4" table="raw" chain="PREROUTING" priority="1">-s 192.168.5.0/24 -j blacklist</rule>
    <rule ipv="ipv4" table="raw" chain="blacklist" priority="0">-m limit --limit 1/min -j LOG --log-prefix "blacklisted: "</rule>
    <rule ipv="ipv4" table="raw" chain="blacklist" priority="1">-j DROP</rule>
</direct>
```

相关命令：

```
firewall-cmd [--permanent] --direct --get-all-chains
firewall-cmd [--permanent] --direct --get-chains { ipv4 | ipv6 | eb } table
firewall-cmd [--permanent] --direct --add-chain { ipv4 | ipv6 | eb } table chain
firewall-cmd [--permanent] --direct --remove-chain { ipv4 | ipv6 | eb } table chain
firewall-cmd [--permanent] --direct --query-chain { ipv4 | ipv6 | eb } table chain

firewall-cmd [--permanent] --direct --get-all-rules
firewall-cmd [--permanent] --direct --get-rules { ipv4 | ipv6 | eb } table chain
firewall-cmd [--permanent] --direct --add-rule { ipv4 | ipv6 | eb } table chain priority args
firewall-cmd [--permanent] --direct --remove-rule { ipv4 | ipv6 | eb } table chain priority args
firewall-cmd [--permanent] --direct --remove-rules { ipv4 | ipv6 | eb } table chain
firewall-cmd [--permanent] --direct --query-rule { ipv4 | ipv6 | eb } table chain priority args

firewall-cmd --direct --passthrough { ipv4 | ipv6 | eb } args
firewall-cmd --permanent --direct --get-all-passthroughs
firewall-cmd --permanent --direct --get-passthroughs { ipv4 | ipv6 | eb }
firewall-cmd --permanent --direct --add-passthrough { ipv4 | ipv6 | eb } args
firewall-cmd --permanent --direct --remove-passthrough { ipv4 | ipv6 | eb } args
firewall-cmd --permanent --direct --query-passthrough { ipv4 | ipv6 | eb } args
```

上述例子转化成命令即为：

```sh
firewall-cmd --permanent --direct --add-chain ipv4 raw blacklist
firewall-cmd --permanent --direct --add-rule ipv4 raw PREROUTING 0 -s 192.168.1.0/24 -j blacklist
firewall-cmd --permanent --direct --add-rule ipv4 raw PREROUTING 1 -s 192.168.5.0/24 -j blacklist
firewall-cmd --permanent --direct --add-rule ipv4 raw blacklist 0 -m limit --limit 1/min -j LOG --log-prefix "blacklisted: "
firewall-cmd --permanent --direct --add-rule ipv4 raw blacklist 1 -j DROP
#重载生效
firewall-cmd --reload
```

在实际生产环境中如果防火墙规则只是由root设定的话，最好将`firewall-cmd`(此文件为python脚本)的权限限制为只有root能执行：

```sh
[root@centos7 ~]# ls -l /usr/bin/firewall-cmd
-rwxr-xr-x. 1 root root 62012 11月 20 2015 /usr/bin/firewall-cmd
[root@centos7 ~]# file /usr/bin/firewall-cmd
/usr/bin/firewall-cmd: Python script, ASCII text executable
[root@centos7 ~]# chmod 750 /usr/bin/firewall-cmd
```

关于`firewalld`的更多内容请查看相关文档

至此，linux基础命令介绍系列就结束了。前后十五篇文章，记录了百余个常用命令。之后将开启新的系列： **`shell编程`** 。

[0]: https://segmentfault.com/a/1190000007541306#articleHeader3
## 如何在 Ubuntu 18.04 下正确配置网络

来源：[http://www.hi-linux.com/posts/49513.html](http://www.hi-linux.com/posts/49513.html)

时间 2018-06-14 11:34:19

 `Netplan`是 Ubuntu 17.10 中引入的一种新的命令行网络配置实用程序，用于在 Ubuntu 系统中轻松管理和配置网络设置。 它允许您使用`YAML`格式的描述文件来抽像化定义网络接口的相关信息。
 `Netplan`可以使用`NetworkManager`或`Systemd-networkd`的网络守护程序来做为内核的接口。`Netplan`的默认描述文件在`/etc/netplan/*.yaml`里，`Netplan`描述文件采用了`YAML`语法。
 
在 Ubuntu 18.04 中如果再通过原来的`ifupdown`工具包继续在`/etc/network/interfaces`文件里配置管理网络接口是无效的。
 
本文我们就来讲讲如何使用`Netplan`在 Ubuntu 18.04 中为网络接口配置静态 IP 地址、动态 IP 地址以及 DNS。
 
#### Netplan 工作原理
 
Netplan 官方网址： [https://netplan.io/][1]
 
Netplan 根据描述文件中定义的内容自动生成其对应的后端网络守护程序所需要的配置信息，后端网络守护程序再根据其配置信息通过 Linux 内核管理对应的网络设备。
 
![][0]
 
### 使用 Networkd 配置网络 
 `Systemd-networkd`是一个管理网络设备的系统守护程序, 它能检测并配置网络设备的状态和创建虚拟网络设备。
 
在进行配置前，我们先来看几个常见配置项的含义。

```
1. enp0s5 指定需配置网络接口的名称。
2. dhcp4  是否打开 IPv4 的 dhcp。
3. dhcp6  是否打开 IPv6 的 dhcp。
4. addresses 定义网络接口的静态 IP 地址。
5. gateway4  指定默认网关的 IPv4 地址。
6. nameservers  指定域名服务器的 IP 地址。


```

 
#### 使用 Networkd 配置动态 IP 地址 
 
Ubuntu 18.04 Server 安装好后，`Netplan`的默认描述文件是：`/etc/netplan/50-cloud-init.yaml`。

 
* 修改 Netplan 的描述文件 
 

```sh
$ sudo vim /etc/netplan/50-cloud-init.yaml

network:
 version: 2
 renderer: networkd
 ethernets:
   enp0s5:
     dhcp4: yes
     dhcp6: yes


```

 
* 运行下面的命令使其生效 
 

```sh
$ sudo netplan apply

```

 
#### 使用 Networkd 配置静态 IP 地址 

 
* 修改 Netplan 的描述文件 
 

```sh
$ sudo vim /etc/netplan/50-cloud-init.yaml

network:
    renderer: networkd
    ethernets:
        enp0s5:
            addresses:
            - 192.168.100.211/23
            gateway4: 192.168.100.1
            nameservers:
                addresses: [8.8.8.8, 8.8.4.4]
                search: []
            optional: true
    version: 2


```

 
* 运行下面的命令使配置生效 
 

```sh
$ sudo netplan apply

```

 
如果你要增加一个 IPv6 地址，可以在 addresses 行增加。多个地址间用逗号分隔：

```sh
$ sudo vim /etc/netplan/50-cloud-init.yaml

network:
    renderer: networkd
    ethernets:
        enp0s5:
            addresses: [192.168.100.211/23, 'fe80:0:0:0:0:0:c0a8:64d3']
            gateway4: 192.168.100.1
            nameservers:
                addresses: [8.8.8.8, 8.8.4.4]
                search: []
            optional: true
    version: 2


```

 
#### 使用 Networkd 同时配置多张网卡 
 
如果要同时配置多张网卡，只需在`Netplan`描述文件中定义多个网络设备就可以了，其它类似。

```sh
$ sudo vim /etc/netplan/50-cloud-init.yaml

# 第一张网卡 enp0s3 配置为动态 IP，第二张网卡 enp0s5 配置为静态 IP。 
network:
  version: 2
  renderer: networkd
  ethernets:
    enp0s3:
      dhcp4: yes
      dhcp6: no
    enp0s5:
      dhcp4: no
      dhcp6: no
      addresses: [192.168.100.211/23]
      gateway4: 192.168.100.1
      nameservers:
        addresses: [8.8.8.8, 8.8.4.4]


```

 
### 使用 NetworkManager 配置网络 
 `NetworkManager`主要用于在桌面系统上管理网络设备。如果您使用`NetworkManager`作为网络设备管理的系统守护程序，将会使用`NetworkManager`的图形程序来管理网络接口。要使用`NetworkManager`，首先需要修改`Netplan`的描述文件：

```sh
$ sudo vim /etc/netplan/50-cloud-init.yaml

network:
  version: 2
  renderer: NetworkManager


```

 
其次是生成`NetworkManager`对应的配置信息：

```sh
$ sudo netplan apply

```

 
最后就可以打开 Ubuntu 桌面系统上的网络接口图形来管理网络。
 
### 其它相关 

 
* YAML 语言基本语法规则 
 
 `YAML`语言的设计目标，就是方便人类读写。它实质上是一种通用的数据串行化格式。`YAML`基本语法规则如下:

```sh
1. 大小写敏感
2. 使用缩进表示层级关系
3. 缩进时不允许使用Tab键，只允许使用空格。
4. 缩进的空格数目不重要，只要相同层级的元素左侧对齐即可
5. # 表示注释，从这个字符一直到行尾，都会被解析器忽略。


```

 
更多的关于`YAML`的使用方法可参考 「 [YAML 语言教程][2] 」一文。

 
* 根据`Netplan`的描述文件手动创建网络守护程序的配置信息  
 

```sh
$ sudo netplan generate


```

 
执行后会使用`/etc/netplan/*.yaml`生成对应网络守护程序的配置信息。例如：

```
$ cat /run/systemd/network/10-netplan-enp0s5.network
[Match]
Name=enp0s5

[Link]
RequiredForOnline=no

[Network]
Address=192.168.100.211/23
Gateway=192.168.100.1
DNS=8.8.8.8
DNS=8.8.4.4


```

 
* 查看当前系统的 DNS Servers 
 

```sh
$ systemd-resolve --status
Global
          DNSSEC NTA: 10.in-addr.arpa
                      16.172.in-addr.arpa
                      168.192.in-addr.arpa
                      17.172.in-addr.arpa
                      18.172.in-addr.arpa
                      19.172.in-addr.arpa
                      20.172.in-addr.arpa
                      21.172.in-addr.arpa
                      22.172.in-addr.arpa
                      23.172.in-addr.arpa
                      24.172.in-addr.arpa
                      25.172.in-addr.arpa
                      26.172.in-addr.arpa
                      27.172.in-addr.arpa
                      28.172.in-addr.arpa
                      29.172.in-addr.arpa
                      30.172.in-addr.arpa
                      31.172.in-addr.arpa
                      corp
                      d.f.ip6.arpa
                      home
                      internal
                      intranet
                      lan
                      local
                      private
                      test

Link 2 (enp0s5)
      Current Scopes: DNS
       LLMNR setting: yes
MulticastDNS setting: no
      DNSSEC setting: no
    DNSSEC supported: no
         DNS Servers: 8.8.8.8
                      8.8.4.4


```

 
### 参考文档 
 
  
[http://www.google.com][3]
 
[http://t.cn/RBxJCC7][4]
 
[http://t.cn/RBxXXlx][5]
 
[http://t.cn/R5mqugZ][6]
 
  [http://t.cn/RBxDyMH][7] 
 


[1]: https://netplan.io/
[2]: https://mp.weixin.qq.com/s?__biz=MzI3MTI2NzkxMA==&mid=2247484080&idx=1&sn=3c5ca66a2dc63c285ca6d2db39f7e553&mpshare=1&scene=23&srcid=0613Bf6KoICw4XVpI9CZzkGE%23rd
[3]: http://www.google.com
[4]: http://t.cn/RBxJCC7
[5]: http://t.cn/RBxXXlx
[6]: http://t.cn/R5mqugZ
[7]: http://t.cn/RBxDyMH
[0]: ../img/mEBZbyf.png
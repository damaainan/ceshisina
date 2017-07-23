## [Tsung笔记之IP地址和端口限制突破篇][0]

### 前言

在[Tsung笔记之压测端资源限制篇][1]中说到单一IP地址的服务器最多能够向外发送64K个连接，这个已算是极限了。

但现在我还想继续深入一下，如何突破这个限制呢 ？

### 如何突破限制

这部分就是要从多个方面去讨论如何如何突破限制单个IP的限制。

##### 0. Tsung支持TCP情况

在Tsung 1.6.0 中支持的TCP属性有限，全部特性如下：

    protocol_options(#proto_opts{tcp_rcv_size = Rcv, tcp_snd_size = Snd,
                                 tcp_reuseaddr = Reuseaddr}) ->
        [binary,
         {active, once},
         {reuseaddr, Reuseaddr},
         {recbuf, Rcv},
         {sndbuf, Snd},
         {keepalive, true} %% FIXME: should be an option
        ].
    

比如可以配置地址重用：

    <option name="tcp_reuseaddr" value="true" />
    

#### 1. 增加IP地址

这是最为现实、最为方便的办法，向运维的同事多申请若干个IP地址就好。在不考虑其它因素前提下，一个IP地址可以对外建立64K个连接，多个IP就是N * 64K了。这个在Tsung中支持的很好。
```
<client host="client_99" maxusers="120000" weight="2" cpu="8">
    <ip value="10.10.10.99"></ip>
    <ip value="10.10.10.11"></ip>
</client>
```
增加IP可以有多种方式：

* 增加物理网卡方式，一个网卡绑定一个IP地址 
  * 代价高
* 一个网卡上绑定多个可用的虚拟IP地址 
  * 比如 ifconfig eth0:2 10.10.10.102 netmask 255.255.255.0
  * 虚拟IP必须是真实可用，否则收不到回包数据

> 要是没有足够的可用虚拟IP地址供你使用，或许你需要关注一下后面的> IP_TRANSPARENT> 特性描述 :))

#### 2. 考虑Linux内核新增SO_REUSEPORT端口重用特性

以被压测的一个TCP服务器为例，继续拿网络四元组说事。

    {SrcIp, SrcPort, TargetIp, TargetPort}
    

* 线上大部分服务器所使用的系统为CentOS 6系列，所使用系统内核低于3.9 
    
    * {SrcIp, SrcPort} 确定了本地建立一个连接的唯一性，本地地址的唯一性
    * {TargetIp, TargetPort}的无法确定唯一，仅仅标识了目的地址

* Linux Kernel 3.9 支持 SO_REUSEPORT 端口重用特性 - 网络四元组中，任何一个元素值的变化都会成为一个全新的连接 
  
    * 真正让网络四元组一起组成了一个网络连接的唯一性
    * 理论上可以对外建立的连接数依赖于四个元素可变数值
    * Totalconnections = NSrcIp * NSrcPort * NTargetIp * NTargetPort

线上有部分服务器安装有CentOS 7，其内核为3.10.0，很自然支持端口重用特性。

针对只有一个IP地址的压测端服务器而言，端口范围也就确定了，只能从目标服务器连接地址上去考虑。有两种方式：

1. 目标服务器增加多个可用IP地址，服务程序绑定指定端口即可 
  
    * N个IP地址，可用存在 64K * N

1. 服务程序绑定多个Port，这个针对程序而言难度不大 
  
    * 针对单个IP，监听了M个端口
    * 可用建立 64K * M 个连接

1. 可用这样梳理 , Total1 ip connections = 64K * N * M

啰嗦了半天，但目前Tsung还没有打算要提供支持呢，怎么办，自己动手丰衣足食吧：

[https://github.com/weibomobile/tsung/commit/f81288539f8e6b6546cb9e239c36f05fc3e1b874][2]

#### 3. 透明代理模式支持

Linux Kernel 2.6.28提供IP_TRANSPARENT特性，支持可以绑定不是本机的IP地址。这种IP地址的绑定不需要显示的配置在物理网卡、虚拟网卡上面，避免了很多手动操作的麻烦。但是需要主动指定这种配置，比如下面的C语言版本代码

    int opt =1;
    setsockopt(server_socket, SOL_IP, IP_TRANSPARENT, &opt, sizeof(opt));
    

目前在最新即将打包的1.6.1版本中提供了对TCP的支持，也需要翻译成对应的选项，以便在建立网络连接时使用：  
![][3]

￼

说明一下：  
- IP_TRANSPARENT没有对应专门的宏变量，其具体值为19  
- SOL_IP定义宏对应值：**0**  
- 添加Socket选项通用格式为：{raw, Protocol, OptionNum, ValueSpec}

那么如何让透明代理模式工作呢？

##### 3.1 启用IP_TRANSPARENT特性
```
<options>
    ...
    <option name="ip_transparent" value="true" />
    ...
<options>
```
##### 3.2 配置可用的额外IP地址

那么这些额外的IP地址如何设置呢？

* 可以为client元素手动添加多个可用的IP地址
```
<client host="tsung_client1" maxusers="500000" weight="1">
   <ip value="10.10.10.117"/>
   <ip value="10.10.10.118"/>
   ......
   <ip value="10.10.10.127"/>
</client>
```
    
* 可以使用新增的iprange特性
```
<client host="tsung_client1" maxusers="500000" weight="1">
    <ip value="10.10.10.117"/>
  <iprange version="v4" value="10.10.10-30.1-254"/>
</client>
```

但是需要确保：

1. > 这些IP地址目前都没有被已有服务器在使用
1. > 并且可以被正常绑定到物理/虚拟网卡上面
1. > 完全可用

##### 3.3 配置路由规则支持

假设我们的tsung_client1这台压测端服务器，绑定所有额外IP地址到物理网卡eth1上，那么需要手动添加路由规则：

    ip rule add iif eth1 tab 100
    ip route add local 0.0.0.0/0 dev lo tab 100
    

这个支持压测端绑定同一网段的可用IP地址，比如压测端IP为172.16.247.130，172.16.247.201暂时空闲的话，那我们就可以使用172.16.89.201这个IP地址用于压测。此时不要求被压测的服务器配置什么。

##### 3.4 进阶，我们使用一个新的网段专用于测试

比如 10.10.10.0 这个段的IP机房暂时没有使用，那我们专用于压测使用，这样一台服务器就有了250多个可用的IP地址了。

压测端前面已经配置好了，现在需要为被压测的服务器添加路由规则，这样在响应数据包的时候能够路由到压测端：

    route add -net 10.10.10.0 netmask 255.255.255.0 gw 172.16.247.130
    

设置完成，可以通过route -n命令查看当前所有路由规则：

![][4]

￼

在不需要时，可以删除掉：

    route del -net 10.10.10.0 netmask 255.255.255.0
    

### 小结

梳理了以上所能够想到的方式，以尽可能突破单机的限制，核心还是尽可能找到足够多可用的IP地址，利用Linux内核特性支持，程序层面绑定尽可能多的IP地址，建立更多的对外连接。当然以上没有考虑类似于CPU、内存等资源限制，实际操作时，还是需要考虑这些资源的限制的。

[0]: http://www.blogjava.net/yongboy/archive/2016/08/16/431601.html
[1]: http://www.blogjava.net/yongboy/archive/2016/07/26/431322.html
[2]: https://github.com/weibomobile/tsung/commit/f81288539f8e6b6546cb9e239c36f05fc3e1b874
[3]: ./img/14711731361283.jpg
[4]: ./img/14713529826391.jpg
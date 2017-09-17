## Linux网络 - 数据包的发送过程


继上一篇介绍了[数据包的接收过程][0]后，本文将介绍在Linux系统中，数据包是如何一步一步从应用程序到网卡并最终发送出去的。

如果英文没有问题，强烈建议阅读后面参考里的文章，里面介绍的更详细。

> 本文只讨论以太网的物理网卡，并且以一个UDP包的发送过程作为示例，由于本人对协议栈的代码不熟，有些地方可能理解有误，欢迎指正

## socket层

                   +-------------+
                   | Application |
                   +-------------+
                         |
                         |
                         ↓
    +------------------------------------------+
    | socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP) |
    +------------------------------------------+
                         |
                         |
                         ↓
               +-------------------+
               | sendto(sock, ...) |
               +-------------------+
                         |
                         |
                         ↓
                  +--------------+
                  | inet_sendmsg |
                  +--------------+
                         |
                         |
                         ↓
                 +---------------+
                 | inet_autobind |
                 +---------------+
                         |
                         |
                         ↓
                   +-----------+
                   | UDP layer |
                   +-----------+
    
    

* **socket(...)：** 创建一个socket结构体，并初始化相应的操作函数，由于我们定义的是UDP的socket，所以里面存放的都是跟UDP相关的函数
* **sendto(sock, ...)：** 应用层的程序（Application）调用该函数开始发送数据包，该函数数会调用后面的inet_sendmsg
* **inet_sendmsg：** 该函数主要是检查当前socket有没有绑定源端口，如果没有的话，调用inet_autobind分配一个，然后调用UDP层的函数
* **inet_autobind：** 该函数会调用socket上绑定的get_port函数获取一个可用的端口，由于该socket是UDP的socket，所以get_port函数会调到UDP代码里面的相应函数。

## UDP层

                         |
                         |
                         ↓
                  +-------------+
                  | udp_sendmsg |
                  +-------------+
                         |
                         |
                         ↓
              +----------------------+
              | ip_route_output_flow |
              +----------------------+
                         |
                         |
                         ↓
                  +-------------+
                  | ip_make_skb |
                  +-------------+
                         |
                         |
                         ↓
             +------------------------+
             | udp_send_skb(skb, fl4) |
             +------------------------+
                         |
                         |
                         ↓
                    +----------+
                    | IP layer |
                    +----------+
    

* **udp_sendmsg：** udp模块发送数据包的入口，该函数较长，在该函数中会先调用ip_route_output_flow获取路由信息（主要包括源IP和网卡），然后调用ip_make_skb构造skb结构体，最后将网卡的信息和该skb关联。
* **ip_route_output_flow：** 该函数会根据路由表和目的IP，找到这个数据包应该从哪个设备发送出去，如果该socket没有绑定源IP，该函数还会根据路由表找到一个最合适的源IP给它。 如果该socket已经绑定了源IP，但根据路由表，从这个源IP对应的网卡没法到达目的地址，则该包会被丢弃，于是数据发送失败，sendto函数将返回错误。该函数最后会将找到的设备和源IP塞进flowi4结构体并返回给udp_sendmsg
* **ip_make_skb：** 该函数的功能是构造skb包，构造好的skb包里面已经分配了IP包头，并且初始化了部分信息（IP包头的源IP就在这里被设置进去），同时该函数会调用__ip_append_dat，如果需要分片的话，会在__ip_append_data函数中进行分片，同时还会在该函数中检查socket的send buffer是否已经用光，如果被用光的话，返回ENOBUFS
* **udp_send_skb(skb, fl4)** 主要是往skb里面填充UDP的包头，同时处理checksum，然后调用IP层的相应函数。

## IP层

              |
              |
              ↓
       +-------------+
       | ip_send_skb |
       +-------------+
              |
              |
              ↓
      +-------------------+       +-------------------+       +---------------+
      | __ip_local_out_sk |------>| NF_INET_LOCAL_OUT |------>| dst_output_sk |
      +-------------------+       +-------------------+       +---------------+
                                                                      |
                                                                      |
                                                                      ↓
     +------------------+        +----------------------+       +-----------+
     | ip_finish_output |<-------| NF_INET_POST_ROUTING |<------| ip_output |
     +------------------+        +----------------------+       +-----------+
              |
              |
              ↓
      +-------------------+      +------------------+       +----------------------+
      | ip_finish_output2 |----->| dst_neigh_output |------>| neigh_resolve_output |
      +-------------------+      +------------------+       +----------------------+
                                                                       |
                                                                       |
                                                                       ↓
                                                               +----------------+
                                                               | dev_queue_xmit |
                                                               +----------------+

* **ip_send_skb：** IP模块发送数据包的入口，该函数只是简单的调用一下后面的函数
* **__ip_local_out_sk：** 设置IP报文头的长度和checksum，然后调用下面netfilter的钩子
* **NF_INET_LOCAL_OUT：** netfilter的钩子，可以通过iptables来配置怎么处理该数据包，如果该数据包没被丢弃，则继续往下走
* **dst_output_sk：** 该函数根据skb里面的信息，调用相应的output函数，在我们UDP IPv4这种情况下，会调用ip_output
* **ip_output：** 将上面udp_sendmsg得到的网卡信息写入skb，然后调用NF_INET_POST_ROUTING的钩子
* **NF_INET_POST_ROUTING：** 在这里，用户有可能配置了SNAT，从而导致该skb的路由信息发生变化
* **ip_finish_output：** 这里会判断经过了上一步后，路由信息是否发生变化，如果发生变化的话，需要重新调用dst_output_sk（重新调用这个函数时，可能就不会再走到ip_output，而是走到被netfilter指定的output函数里，这里有可能是xfrm4_transport_output），否则往下走
* **ip_finish_output2：** 根据目的IP到路由表里面找到下一跳(nexthop)的地址，然后调用__ipv4_neigh_lookup_noref去arp表里面找下一跳的neigh信息，没找到的话会调用__neigh_create构造一个空的neigh结构体
* **dst_neigh_output：** 在该函数中，如果上一步ip_finish_output2没得到neigh信息，那么将会走到函数neigh_resolve_output中，否则直接调用neigh_hh_output，在该函数中，会将neigh信息里面的mac地址填到skb中，然后调用dev_queue_xmit发送数据包
* **neigh_resolve_output：** 该函数里面会发送arp请求，得到下一跳的mac地址，然后将mac地址填到skb中并调用dev_queue_xmit

## netdevice子系统

                              |
                              |
                              ↓
                       +----------------+
      +----------------| dev_queue_xmit |
      |                +----------------+
      |                       |
      |                       |
      |                       ↓
      |              +-----------------+
      |              | Traffic Control |
      |              +-----------------+
      | loopback              |
      |   or                  +--------------------------------------------------------------+
      | IP tunnels            ↓                                                              |
      |                       ↓                                                              |
      |            +---------------------+  Failed   +----------------------+         +---------------+
      +----------->| dev_hard_start_xmit |---------->| raise NET_TX_SOFTIRQ |- - - - >| net_tx_action |
                   +---------------------+           +----------------------+         +---------------+
                              |
                              +----------------------------------+
                              |                                  |
                              ↓                                  ↓
                      +----------------+              +------------------------+
                      | ndo_start_xmit |              | packet taps(AF_PACKET) |
                      +----------------+              +------------------------+

* **dev_queue_xmit：** netdevice子系统的入口函数，在该函数中，会先获取设备对应的qdisc，如果没有的话（如loopback或者IP tunnels），就直接调用dev_hard_start_xmit，否则数据包将经过[Traffic Control][1]模块进行处理
* **Traffic Control：** 这里主要是进行一些过滤和优先级处理，在这里，如果队列满了的话，数据包会被丢掉，详情请参考[文档][1]，这步完成后也会走到dev_hard_start_xmit
* **dev_hard_start_xmit：** 该函数中，首先是拷贝一份skb给“packet taps”，tcpdump就是从这里得到数据的，然后调用ndo_start_xmit。如果dev_hard_start_xmit返回错误的话（大部分情况可能是NETDEV_TX_BUSY），调用它的函数会把skb放到一个地方，然后抛出软中断NET_TX_SOFTIRQ，交给软中断处理程序net_tx_action稍后重试（如果是loopback或者IP tunnels的话，失败后不会有重试的逻辑）
* **ndo_start_xmit：** 这是一个函数指针，会指向具体驱动发送数据的函数

## Device Driver

ndo_start_xmit会绑定到具体网卡驱动的相应函数，到这步之后，就归网卡驱动管了，不同的网卡驱动有不同的处理方式，这里不做详细介绍，其大概流程如下：

1. 将skb放入网卡自己的发送队列
1. 通知网卡发送数据包
1. 网卡发送完成后发送中断给CPU
1. 收到中断后进行skb的清理工作

在网卡驱动发送数据包过程中，会有一些地方需要和netdevice子系统打交道，比如网卡的队列满了，需要告诉上层不要再发了，等队列有空闲的时候，再通知上层接着发数据。

## 其它

* **SO_SNDBUF:** 从上面的流程中可以看出来，对于UDP来说，没有一个对应send buffer存在，SO_SNDBUF只是一个限制，当这个socket分配的skb占用的内存超过这个值的时候，会返回ENOBUFS，所以说只要不出现ENOBUFS错误，把这个值调大没有意义。从sendto函数的帮助文件里面看到这样一句话：(Normally, this does not occur in Linux. Packets are just silently dropped when a device queue overflows.)。这里的device queue应该指的是Traffic Control里面的queue，说明在linux里面，默认的SO_SNDBUF值已经够queue用了，疑问的地方是，queue的长度和个数是可以配置的，如果配置太大的话，按道理应该有可能会出现ENOBUFS的情况。
* **txqueuelen:** 很多地方都说这个是控制qdisc里queue的长度的，但貌似只是部分类型的qdisc用了该配置，如linux默认的pfifo_fast。
* **hardware RX:** 一般网卡都有一个自己的ring queue，这个queue的大小可以通过ethtool来配置，当驱动收到发送请求时，一般是放到这个queue里面，然后通知网卡发送数据，当这个queue满的时候，会给上层调用返回NETDEV_TX_BUSY
* **packet taps(AF_PACKET):** 当第一次发送数据包和重试发送数据包时，都会经过这里，如果发生重试的情况的话，不确定tcpdump是否会抓到两次包，按道理应该不会，可能是我哪里没看懂

## 参考

* [Monitoring and Tuning the Linux Networking Stack: Sending Data][2]
* [queueing in the linux network stack][3]

[0]: https://segmentfault.com/a/1190000008836467
[1]: http://tldp.org/HOWTO/Traffic-Control-HOWTO/intro.html
[2]: https://blog.packagecloud.io/eng/2017/02/06/monitoring-tuning-linux-networking-stack-sending-data/
[3]: https://www.coverfire.com/articles/queueing-in-the-linux-network-stack/
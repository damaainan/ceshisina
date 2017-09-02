# 理解 TCP/IP 网络栈

 时间 2017-07-27 09:46:26  Cizixs Writes Here

原文[http://cizixs.com/2017/07/27/understand-tcp-ip-network-stack][1]




[TOC]

译者注：很久没有翻译文章了，最近在网络看到这篇介绍网络栈的文章非常详细，正好最近在看这方面的内容，索性翻译过来。因为很多文章比较长，而且很多内容比较专业，翻译过程中难免会有错误，如果读者发现错误，还望不吝指出。文章中 Linux 内核源码摘自哪个版本原文并没有表明，我也没有找到对应的版本，代码的缩进可能会有问题。 

原文地址： cubrid.org/blog/understanding-tcp-ip-network-stack，有删减。

没有 TCP/IP 的网络服务是无法想象的，理解数据是怎么在网络中传递的能够让你通过调优、排错来提高网络性能。 这篇文章会介绍 Linux OS 和硬件层的数据流和控制流的网络操作。

## 一. TCP/IP 特性

怎么设计一种网络协议，才能保证数据传输速度很快、能够保证数据的顺序而且没有数据丢失呢？TCP/IP 的设计目标就是如此，下面这些 TCP/IP 的主要特性是理解网络栈的关键。 

TCP and IP 技术上说，TCP 和 IP 在不同的网络层，应该分开来表述。方便起见，我们这里把它们作为一个概念。

### 1. 面向连接

通信双方先建立连接，才能发送数据。TCP 连接是由四元组唯一确定的：

    <本地 IP，本地端口，远端 IP，远端端口>

### 2. 双向字节流

使用字节流来进行双向数据传输

### 3. 有序传输

接收方接收到的数据顺序和发送方的发送顺序一致，要做到这点，数据要有顺序的概念，每个数据段用一个 32 位的整数来标志它的顺序。

### 4. 通过确认（ACK）来实现可靠性

如果发送方发送报文之后，没有收到接收方返回的 ACK 确认，发送方会重新发送数据。因此，发送方的 TCP 缓存中保存着接收方还没有确认的数据。

### 5. 流量控制

发送方最多能发送的数据由接收方能接受的数据量决定。接收方会发送它能接收的最大数据量（可用的 buffer 大小，接收窗口大小）给发送方，发送方也只能发送接收方 **接收窗口** 能够允许的字节大小。 

### 6. 拥塞控制

拥塞窗口独立于接收窗口，通过限制网络中数据流来阻止网络拥塞。和接收窗口类似，发送方根据一定的算法（比如 TCP Vegas、Westwood、BIC、和 CUBIC）发送接收方拥塞窗口允许的最大数据。和流控不同，拥塞控制只在发送方实现。

## 二. 数据发送流程

网络栈有多个层，下图展示了网络不同的层：

![][3]

这些层大致可以分为三类：

1. 用户域
1. 内核域
1. 设备域

用户域和内核域的任务是 CPU 执行的，它们两个也和成为主机，用以和设备域进行区别。设备指的是发送和接受报文的网卡（Network Interface Card/NIC）。

我们来看用户域。首先应用构造出需要发送的数据（上图中的 **User Data** 部分），然后调用 `write()` 系统调用发送数据。假设 socket（图中的 **fd** ） 已经创建，当系统调用执行的时候，就进入到内核域。 

Linux 和 Unix 这种 POSIX 系列的操作系统暴露文件操作符给应用程序，供它们操作 socket。对于 POSIX 系列操作系统来说，socket 就是一种文件。文件层进行简单的检查，然后通过和文件结构体关联的 socket 结构体调用 socket 对应的函数。

内核 socket 有两个缓存：

1. 用来发送数据的 **socket 发送缓存**
1. 用来接收数据的 **socket 接收缓存**

当调用 `write` 系统调用时，用户域的数据被拷贝到内核内存中，然后添加到 socket 发送缓存的尾部，这是为了按照顺序发送数据。在上图中，浅灰色矩形框代表着 socket 缓存中的数据。接着，TCP 层被调用了。 

socket 和 TCB（TCP Control Block） 相关联，TCB 保存着处理 TCP 连接需要的信息，比如连接状态（ **LISTEN** 、 **ESTABLISHED** 、 **TIME_WAIT** ）、接收窗口、拥塞窗口、序列号、重发计时器等。 

如果当前的 TCP 状态允许数据传输，就会生成一个新的 TCP segment（或者说报文）。如若因为流控等原因无法进行数据传输，系统调用到此结束，重新回到用户模式（或者说，控制权又重新交给应用）。

如下图所示。TCP 段有两部分：

1. TCP 头部
1. payload

![][4]

payload 包括了 socket 发送缓存的数据，payload 的最大值是接收窗口、拥塞窗口和最大段（Maximum Segment Size/MSS） 的三者的最大值。

接着，计算出 TCP checksum。计算 checksum 的时候，会考虑到 IP 地址、segment 长度、和协议号等信息。根据 TCP 状态不同，可以传输的报文从 1 个到多个不等。

NOTE：事实上，TCP checksum 是网卡计算的，不是内核。但是为了简单起见，我们假定是内核做了这件事。 

创建的 TCP 段往下走到 IP 层。IP 层给 TCP 段加上 IP 头部，并执行路由的逻辑。路由是查找下一跳的 IP 地址的过程，目的是更接近目的 IP。

IP 层计算并添加上 checksum 之后，报文被发送到以太网层。以太网层通过 ARP（Address Resolution Protocol） 协议查找下一跳的 MAC 地址，然后把以太网层的头部添加到报文上，这样主机上的报文就是最终的完整状态。

IP 层经过路由，就知道了要传输报文的网络接口（NIC），报文就从这个网口发送到下一跳 IP 所在机器。因此，下一步就是调用网口的驱动。

NOTE：如果有网络抓包工具（比如 wireshark 或者 tcpdump）在运行，内核会把报文数据拷贝到应用使用的内存区。 

网卡驱动根据网卡制造商编写的通信协议向网卡发送传输数据的请求，收到请求之后，网卡（NIC）把数据从主内存拷贝到自己的内存区，然后发送到网络线路上。这个过程中，为了遵循以太网协议，网卡还会为报文添加IFG（Inter-Frame Gap）、preamble、CRC。其中 IFG 和 preamble 是为了区分报文/帧的开始，CRC 是为了保护报文的内容（和 TCP IP 中的 checksum 功能相同）。数据传输的速度决定于以太网的物理速度以及流量控制的现状。

网卡发送数据的时候，会在主机 CPU 产生中断，每个中断都有编号，操作系统根据编号找到对应的驱动处理这个中断。驱动程序会在启动的时候注册它的处理函数到系统，操作系统调用驱动注册的处理函数，然后处理函数把传输的报文返回给操作系统。

至此，我们讨论了应用执行写操作时数据在内核和设备中的发送流程。需要注意的是，即使没有应用层显式的写操作，内核也会调用 TCP 层来发送数据。比如，当收到 ACK 报文，接收窗口扩大，内核会把 socket 发送缓存中的数据组装成 TCP 数据段，发送给接收方。

## 三. 数据接收流程

这部分，我们来看看数据的接收流程，数据接收流程是网络栈是怎么处理接收到的数据的。下图主要展示了这个过程：

![][5]

首先，NIC 把报文拷贝到自己的内存中，检查 CRC 判断报文是否有效。如果有效，则发送到报文到主机的内存缓存区中。这部分内存缓存区是驱动提前申请的，专门用来保存接收到的数据。当缓存被分配的时候，驱动会告诉网卡这块内存的地址和大小。如果网卡接收到某个报文的时候，这部分缓存空间不足了，那么网卡可能直接丢掉报文。

把报文发送到主机的内存后，网卡会向主机发送一个中断。驱动这时候会检查它是否能处理这个报文，如果可以，它会把报文发送给上层的网络协议。往上层发送数据时，报文必须是操作系统能够理解的形式。比如 linux 的 `sk_buff` ，BSD 系统的 `mbuf` ，windows 系统的 `NET_BUFFER_LIST` ，都是操作系统能理解的报文结构体。驱动需要把结构化的报文发送给上层。 

以太网层检查报文是否合法，然后把上层协议的类型从报文中抽取出来。以太网层在 `ethertype` 字段中保存着上层使用的协议类型。IPv4 协议对应的值是 `0x8000` ，报文中以太网层的头部会被去掉，剩下的内容发送到上层的 IP 去处理。 

IP 层也会检查报文是否合法，不过它检查的是 IP 协议头部的 checksum。根据 IP 协议头部的地址，这一层会判断报文应该交给上层处理，还是执行路由抉择，或者直接发送给其他系统。如果报文应该有当前主机处理，那么上层协议（传输层）类型会从 IP 头部的 `proto` 字段读取，比如 TCP 协议对应的值是 6。然后报文的 IP 层头部被去掉，剩下的内容继续发送到上层的 TCP 层处理。 

和其他层一样，TCP 层也会先检查报文是否合法，它的判断依据是 TCP checksum。然后它找到和报文关联的 TCB（TCP Control Block），报文是由 `<source IP, source Port, target IP, target Port>` 四元组作为连接标识的。系统找到对应的连接就能继续协议层的处理。如果这是收到的新数据，它会把数据拷贝到 socket 接收缓存中。根据 TCP 连接的状态，可能还会发送一个新的 TCP 报文（比如 ACK 报文通知对方报文已经收到）。至此，TCP/IP 报文接收流程就完成了。 

socket 接收缓存的大小就是 TCP 接受窗口，在一定条件下，TCP 的吞吐量会随着接收窗口增加而增加。过去接收窗口是应用或者操作系统进行配置的，现在的网络栈能够自动调整接收窗口。

当应用调用 `read` 系统调用时，控制权就到了内核，内核会把数据从 socket 缓存拷贝到用户域的内存中，拷贝之后缓存中的数据就被删除。接着 TCP 相关的函数被触发，因为有了新的缓存空间可用，TCP 会增加接收窗口的大小。如果需要，TCP 还会发送一个报文给对方，如果没有数据要发送，系统调用就到此结束。 

## 四. 网络栈发展

上面只描述了网络栈各层最基本的功能。1990 年代网络栈的功能就已经比上面描述的要多，而最近的网络栈功能更多，复杂度也更高。

最新的网络栈按照功能可以分成下面几类：

### 报文预处理操作

Netfilter（firewall，NAT）和流量控制允许用户在基本流程中插入控制代码，改变报文的处理逻辑。

### 协议性能

性能是为了提高 TCP 协议在网络环境中的吞吐量，延迟和稳定性，例子包括各样的拥塞控制算法和 SACK 这样的 TCP 功能。这类的协议改进不会在本文讨论，因为它超出了文章的范围。

### 报文处理效率

报文处理效率是为了提高每秒能处理的报文数量，一般是通过减少 CPU 周期，内存使用，和内存读取时间。减少系统延迟要很多方法，比如并行处理、头部预测、zero-copy、single-copy、checksum offload、TSO、LRO、RSS 等。

## 五. 网络栈流量控制

现在，让我们详细分析网络栈内部的数据流。网络栈基本工作模式是事件驱动的，也就是说事件发生会触发一系列的处理逻辑，因此不需要额外的线程。下图展示了更精细的数据控制流程：

![][6]

图中 (1) 表示应用程序调用了某个系统调用来执行（或者说使用）TCP，比如调用 `read` 或者 `write` 系统调用函数。不过，这一步并没有任何报文传输。 

（2）和 (1) 类似，只是执行 TCP 逻辑之后，还会把报文发送出去。TCP 会生成一个报文，然后往下一直发送到网卡驱动。报文会先到达队列，然后队列的实现决定什么时候把报文发给网卡驱动。这个过程是 linux 中的 queue discipline（qdisc），linux 流量控制就是控制 qdisc 实现的。默认的 qdisc 算法是先进先出（FIFO），通过使用其他的 qdisc 算法，用户可以实现各种效果，比如人为的报文丢失、报文延迟、传输速率控制等。

流程 (3) 表示 TCP 使用的计时器过期的过程。比如 **TIME_WAIT** 计时器过期，TCP 会被调用删除这个连接。 

流程（4）和 流程（3）类似，TCP 计时器过期，但是需要重新发送报文。比如，重传计时器过期，没有接收到 ACK 的报文会重新发送。这两个流程展示了计时器软中断的处理过程。

当网卡驱动接收到一个中断，它会释放传输的报文。大多数情况下，驱动的任务到此就结束了。流程（5）是报文在传输队列（transmit queue）集聚，网卡驱动请求一个软中断（softirq），中断处理函数把传输队列中的报文发送到网卡驱动中。

当网卡驱动接受到一个中断，并且收到了一个新的报文，它也会请求一个软中断。这个软中断会处理接收到的报文，调用驱动程序，并把报文传输到上层。在 Linux 系统中，处理接收到报文的过程被称为 New API（NAPI），它和 polling 类似，因为驱动不会直接把报文发送给上层，而是上层直接获取报文。对应的代码成为 NAPI poll 或者简称 poll。

流程（6）展示了 TCP 执行完成的过程，流程（7）是 TCP 流程需要传输额外的报文。（5）、（6）和（7）都是软中断执行的，而软中断之前也处理了网卡中断。

## 六. 怎么处理中断和接收到的报文？

中断处理的过程很复杂，但是我们需要了解和报文接收有关的性能问题。下图展示了处理中断的过程：

![][7]

假设 CPU 0 在执行应用程序，这时网卡收到一个报文，并为 CPU 0 产生一个中断。CPU 会执行内核中断（irq）处理（ `do_IRQ()` ），它会找到中断号，调用对应的驱动中断处理函数。驱动释放传送的报文，然后调用 `napi_schedule()` 函数处理接收的报文。这个函数发起软件中断（softirq）。 

驱动中断处理完成结束后，控制权就回到了内核处理函数,内核处理函数执行软件中断的中断处理（ `do_softirq()` ）。 

软件处理函数处理接收报文的是 `net_tx_action()` 函数。这个函数调用驱动的 `poll()` 函数， `poll()` 函数继续调用 `netif_receive_skb()` 函数，然后把接收到的报文逐个发送给上层。软件中断处理结束后，应用就从系统调用之后的地方继续执行。 

CPU 收到中断之后，会从头执行到结束，Linux、 BSD 和 Windows 系统的执行流程大致如此。当检查服务器 CPU 使用率时，有时候会看到多个 CPU 中只有一个 CPU 在执行软中断，就是因为这样。为了解决这个问题，提出了很多方案，比如多队列网卡、RSS、和 RPS。

## 七. 数据结构

下面介绍网络栈主要的数据结构。

### sk_buff 结构体

sk_buff或者说 **skb** 代表一个报文，下图就展示了 **sk_buff** 的结构。随着网络功能的增加，这个结构体也会越来越复杂，但是基本的功能却保持不变。 

![][8]

#### 包含报文数据和 metadata

这个结构体包含了报文数据，或者保存了指向报文数据的指针。上图中，data 指针指向了报文， `frags` 指向真正的页。 

诸如头部和 payload 长度这些信息保存在 meta data 区域，比如上图中 `mac_header` 、 `network_header` 和 `transport_header` 分别指向了以太网头部、IP 头部和 TCP 头部，这种方式让 TCP 报文处理更容易。 

#### 如何添加和删除头部

在网络栈各层向上或者向下移动时，各层会添加或者删除头部。指针是为了操作更有效，比如要删除以太网头部，只需要增加 `head` 指针偏移量就行。 

#### 如何合并和分解报文

链表用来高效地执行添加或者删除报文 payload 的任务， `next` 和 `prev` 指针就是这个功能。 

#### 快速分配和释放

因为每次创建报文都要分配一个结构体，因此这里使用了快速分配。比如，如果数据在 10G 的以太网传输，那么每分钟至少要创建和删除一百万报文。

### TCP Control Block

其次，还有一个代表 TCP 连接的结构体，被称为 TCP control block，Linux 中对应的是 `tcp_sock` 。在下图中，你可以看到 file、socket、和 `tcp_sock` 的关系： 

![][9]

系统调用发生时，系统会检查应用使用的文件描述符。对于 Unix 系列的操作系统来说，`socket`、`file`、和文件系统的设备都被抽象为文件，因此 `file` 的结构体中保存的信息最少。对于 `socket`，有一个额外的 `socket` 结构体保存着和这个 `socket` 有关的信息。 `file` 有一个指针指向 `socket` ，而 socket 又指向 `tcp_sock` 。 `tcp_sock` 可以分成 `sock`、`inet_sock` 不同的类型来支持出 TCP 之外的各种协议。可以把这理解为多态！ 

TCP 协议的所有状态信息都保存在 `tcp_sock` 中，比如序列号、接受窗口、拥塞控制、和重传计时器都保存在 `tcp_sock` 。 

`socket` 发送缓存和接收缓存就是 `sk_buff` 列表，它们也保存了 `tcp_sock` 信息。 `dst_entry` 和 IP 路由结果是为了避免频繁地进行路由。 `dst_entry` 允许快速搜索 ARP 结果，也就是目的 MAC 地址。 `dst_entry` 是路由表的一部分，路由表的结构非常复杂，这篇文章不会讨论。报文传输要使用的网络设备也能通过 `dst_entry` 搜索到，网络设备对应的结构体是 `net_device` 。 

因此，通过 `file` 结构体和各级指针就能找到处理 TCP 报文需要的结构体（从文件一直到网络驱动），各种结构体的大小之和也就是 TCP 连接要占用的内存大小，这个值在几 KB（当然不包括报文的数据）。对着更多的功能加进来，这个内存使用也会逐渐增加。 

最后，我们来看看 TCP 连接查找表（lookup table），这是一个哈希表，用来搜索接收到的报文属于哪个 TCP 连接。哈希值是通过报文的` <source IP, target IP, source port, target port>` 四元组和 Jenkins 哈希算法计算的，据说使用这个算法是为了应对对哈希表的攻击。 

## 八. 源码解读：发送数据

我们通过阅读 Linux 内核源码来看看网络栈具体执行的关键任务，我们将会观察经常用到的两条线路。

首先，第一条是应用程序调用 `write` 系统调用发送报文的线路。 

```c
    SYSCALL_DEFINE3(write, unsigned int, fd, const char __user *, buf, ...)
    {
        struct file *file;
        ...
        file = fget_light(fd, &fput_needed);
        ... 
        ret = filp->f_op->aio_write(&kiocb, &iov, 1, kiocb.ki_pos);
    }
    
    struct file_operations {
        ...
        ssize_t (*aio_read) (struct kiocb *, const struct iovec *, ...)
        ssize_t (*aio_write) (struct kiocb *, const struct iovec *, ...)
        ...
    };
     
    static const struct file_operations socket_file_ops = {
        ...
        .aio_read = sock_aio_read,
        .aio_write = sock_aio_write,
        ...
    };
```

当应用程序调用 `write` 系统调用，内核执行 `write()` 函数。首先要根据 fd 找到真正的而繁忙操作符，然后调用 `aio_write` ，这是一个函数指针。在 `file` 结构体中，你可以看到 `file_operations` 结构体指针，这个结构体被称作函数表，里面包含了 `aio_read` 和 `aio_write` 等函数指针。`socket` 真正的函数表是 `socket_file_ops` `，socket` 使用的 `aio_write` 函数是 `sock_aio_write` 。这个函数表的功能类似于 Jave 的 interface，可以方便内核进行代码抽象和重构。 

```c
    static ssize_t sock_aio_write(struct kiocb *iocb, const struct iovec *iov, ..)
    {
        ...
        struct socket *sock = file->private_data;
        ...
        return sock->ops->sendmsg(iocb, sock, msg, size);
    }
     
    struct socket {
        ... 
        struct file *file;
        struct sock *sk;
        const struct proto_ops *ops;
    };
     
    const struct proto_ops inet_stream_ops = {
        .family = PF_INET,
        ...
        .connect = inet_stream_connect,
        .accept = inet_accept,
        .listen = inet_listen, 
        .sendmsg = tcp_sendmsg,
        .recvmsg = inet_recvmsg,
        ...
    };
     
    struct proto_ops {
        ...
        int (*connect) (struct socket *sock, ...)
        int (*accept) (struct socket *sock, ...)
        int (*listen) (struct socket *sock, int len);
        int (*sendmsg) (struct kiocb *iocb, struct socket *sock, ...)
        int (*recvmsg) (struct kiocb *iocb, struct socket *sock, ...)
        ...
    };
```

`sock_aio_write` 函数从 `file` 结构体中获取 `socket` 结构，然后调用 `sendmsg` ，这也是一个函数指针。 `socket` 结构体包括了 `proto_ops` 的函数表，IPv4 对应的实现是 `inet_stream_ops` ， 其中 `sendmsg` 对应的实现是 `tcp_sendmsg` 。 

```c
    int tcp_sendmsg(struct kiocb *iocb, struct socket *sock,
    struct msghdr *msg, size_t size)
    {
        struct sock *sk = sock->sk;
        struct iovec *iov;
        struct tcp_sock *tp = tcp_sk(sk);
        struct sk_buff *skb;
        ...
        
        mss_now = tcp_send_mss(sk, &size_goal, flags);
        /* Ok commence sending. */
        iovlen = msg->msg_iovlen;
        iov = msg->msg_iov;
        copied = 0;
        ...
    
        while (--iovlen >= 0) {
            int seglen = iov->iov_len;
            unsigned char __user *from = iov->iov_base;
            iov++;
            
            while (seglen > 0) {
                int copy = 0;
                int max = size_goal;
                ...
                skb = sk_stream_alloc_skb(sk,
                    select_size(sk, sg),
                    sk->sk_allocation);
                if (!skb)
                    goto wait_for_memory;
                /*
                * Check whether we can use HW checksum.
                */
                if (sk->sk_route_caps & NETIF_F_ALL_CSUM)
                    skb->ip_summed = CHECKSUM_PARTIAL;
                ...
                skb_entail(sk, skb);
                ...
                
                /* Where to copy to? */
                if (skb_tailroom(skb) > 0) {
                    /* We have some space in skb head. Superb! */
                    if (copy > skb_tailroom(skb))
                        copy = skb_tailroom(skb);
                    if ((err = skb_add_data(skb, from, copy)) != 0)
                        goto do_fault;
                    ...
                    if (copied)
                        tcp_push(sk, flags, mss_now, tp->nonagle);
                }
                ...
            }
        }
    }
```

`tcp_sendmsg` 首先从 `socket` 中获取 `tcp_sock` ，然后把应用要发送的数据拷贝到 `socket` 发送缓存。当拷贝数据到 `sk_buff` 的时候，每个 `sk_buff` 要保存多少数据呢？每个 `sk_buff` 只能拷贝并保存 MSS( `tcp_send_mss` ) 字节的内容。Maximum Segment Size（MSS）表示一个 TCP 报文能包含的最大 `payload` 大小。使用 TSO 和 GSO 能够让每个 `sk_buff` 保存超过 MSS 的数据。相关的内容不会在这篇文章讨论。 

`sk_stream_allc_skb` 函数创建一个新的 `sk_buff` ， `skb_entail` 把 `sk_buff` 加到 `send_socket_buffer` 的尾部。 `skb_add_data` 函数把应用的真正数据拷贝到 `sk_buff` 中，拷贝的过程是循环多次这个逻辑（创建一个 `sk_buff` ，然后把它加入到 `socket` 发送缓存中）。因此位于 MSS 的数据所在的 `sk_buff` 在列表的第二个。最终 `tcp_push` 把能发送的数据转换成一个报文，并发送出去。 

```c
    static inline void tcp_push(struct sock *sk, int flags, int mss_now, ...)
        ...
     
    static int tcp_write_xmit(struct sock *sk, unsigned int mss_now, ...)
    int nonagle,
    {
        struct tcp_sock *tp = tcp_sk(sk);
        struct sk_buff *skb;
        ...
        
        while ((skb = tcp_send_head(sk))) {
            ...
            cwnd_quota = tcp_cwnd_test(tp, skb);
            if (!cwnd_quota)
                break;
        
            if (unlikely(!tcp_snd_wnd_test(tp, skb, mss_now)))
                break;
            ...
            if (unlikely(tcp_transmit_skb(sk, skb, 1, gfp)))
                break;
            /* Advance the send_head. This one is sent out.
            * This call will increment packets_out.
            */
            tcp_event_new_data_sent(sk, skb);
        }
    }
```

只要 TCP 允许， `tcp_push` 函数会尽可能把 `socket` 发送缓存中的 `sk_buff` 都发送出去。首先， `tcp_send_head` 获取到 `socket` 缓存中第一个 `sk_buff` ，然后 `tcp_cwnd_test` 和 `tcp_snd_wnd_test` 检查用色窗口和接受窗口是否允许报文传输。接着， `tcp_transmit_skb` 函数创建一个报文： 

```c
    static int tcp_transmit_skb(struct sock *sk, struct sk_buff *skb,
    int clone_it, gfp_t gfp_mask)
    {
        const struct inet_connection_sock *icsk = inet_csk(sk);
        struct inet_sock *inet;
        struct tcp_sock *tp;
        ...
        
        if (likely(clone_it)) {
            if (unlikely(skb_cloned(skb)))
                skb = pskb_copy(skb, gfp_mask);
            else
                skb = skb_clone(skb, gfp_mask);
    
            if (unlikely(!skb))
                return -ENOBUFS;
        }
        ...
        
        skb_push(skb, tcp_header_size);
        skb_reset_transport_header(skb);
        skb_set_owner_w(skb, sk);
    
        /* Build TCP header and checksum it. */
        th = tcp_hdr(skb);
        th->source = inet->inet_sport;
        th->dest = inet->inet_dport;
        th->seq = htonl(tcb->seq);
        th->ack_seq = htonl(tp->rcv_nxt);
        ...
        icsk->icsk_af_ops->send_check(sk, skb);
        ...
        err = icsk->icsk_af_ops->queue_xmit(skb);
        if (likely(err <= 0))
            return err;
        tcp_enter_cwr(sk, 1);
    
        return net_xmit_eval(err);
    }
```

`tcp_transmit_skb` 创建一份 `sk_buff` 的拷贝（ `pskb_copy` ），不过它只拷贝了 meta 数据，并不没有拷贝整个应用的数据。接着 `skb_push` 对头部做安全配置，并记录头部字段的值。 `send_check` 计算 TCP 的 `checksum`，如果使用 `checksum` offload，那么 `checksum` 就不用在此计算。最后 `queue_xmit` 把报文发送到 IP 层，IPv4 实现的 `queu_xmit` 函数是 `ip_queue_xmit` ： 

```
    int ip_queue_xmit(struct sk_buff *skb){
        ...
        
        rt = (struct rtable *)__sk_dst_check(sk, 0);
        
        ...
        
        /* OK, we know where to send it, allocate and build IP header. */
        skb_push(skb, sizeof(struct iphdr) + (opt ? opt->optlen : 0));
        skb_reset_network_header(skb);
        iph = ip_hdr(skb);
        *((__be16 *)iph) = htons((4 << 12) | (5 << 8) | (inet->tos & 0xff));
    
        if (ip_dont_fragment(sk, &rt->dst) && !skb->local_df)
            iph->frag_off = htons(IP_DF);
        else
            iph->frag_off = 0;
        
        iph->ttl = ip_select_ttl(inet, &rt->dst);
        iph->protocol = sk->sk_protocol;
        iph->saddr = rt->rt_src;
        iph->daddr = rt->rt_dst;
        ...
        res = ip_local_out(skb);
        ... ===>
        int __ip_local_out(struct sk_buff *skb)
        ...
        ip_send_check(iph);
        return nf_hook(NFPROTO_IPV4, NF_INET_LOCAL_OUT, skb, NULL,
                       skb_dst(skb)->dev, dst_output);
        
        ... ===>
     
    }
    
    int ip_output(struct sk_buff *skb)
    {
        struct net_device *dev = skb_dst(skb)->dev;
        ...
        skb->dev = dev;
        skb->protocol = htons(ETH_P_IP);
        
        return NF_HOOK_COND(NFPROTO_IPV4, NF_INET_POST_ROUTING, skb, NULL, dev,
        ip_finish_output,
        ... ===>
        static int ip_finish_output(struct sk_buff *skb)
        ...
        
        if (skb->len > ip_skb_dst_mtu(skb) && !skb_is_gso(skb))
            return ip_fragment(skb, ip_finish_output2);
        else
            return ip_finish_output2(skb);
    }
```

`ip_queue_xmit` 执行 IP 层需要的逻辑， `__sk_dst_check` 检查缓存的路由是否有效。如果没有缓存的路由，或者缓存的路由已经失效，就要执行 IP 路由逻辑。接着 `skb_push` 用来对 IP 头部进行安全设置，并记录 IP 头部字段值。接着， `ip_send_check` 计算 IP 头部的 checksum，并调用 `netfilter` 函数。如果需要 IP 分片， `ip_finish_ouput` 也会对 IP 进行分片，如果上层是 TCP 的话，就不需要进行分片。因此 `ip_finish_output2` 就被调用添加以太网头部。最后，一个完整的报文就产生了。 

```
    int dev_queue_xmit(struct sk_buff *skb)
        ... ===>
        static inline int __dev_xmit_skb(struct sk_buff *skb, struct Qdisc *q, ...)
        ...
        if (...) {
            ....
        
        } else {
            if ((q->flags & TCQ_F_CAN_BYPASS) && !qdisc_qlen(q) &&
        qdisc_run_begin(q)) {
            ...
            if (sch_direct_xmit(skb, q, dev, txq, root_lock)) {
                ... ===>
                int sch_direct_xmit(struct sk_buff *skb, struct Qdisc *q, ...)
        
                ...
        
                HARD_TX_LOCK(dev, txq, smp_processor_id());
        
        if (!netif_tx_queue_frozen_or_stopped(txq))
        
            ret = dev_hard_start_xmit(skb, dev, txq);
        
        
        
        HARD_TX_UNLOCK(dev, txq);
        
        ...
        
    }
     
    int dev_hard_start_xmit(struct sk_buff *skb, struct net_device *dev, ...)
        ...
        if (!list_empty(&ptype_all))
            dev_queue_xmit_nit(skb, dev);
        ...
        rc = ops->ndo_start_xmit(skb, dev);
        ...
    }
```

最终完整的报文通过 dev_queue_xmit 传输。首先，报文通过 qdisc ，如果使用的是默认 qdisc 而且队列是空的， sch_direct_xmit 函数会跳过队列过程，直接把报文发送给驱动。 dev_hard_start_xmit 函数调用真正的驱动，在调用驱动之前，会先锁上设备的 TX ，这是为了防止多个线程同时操作驱动。内核锁住设备 TX ，内核的传输代码就不需要再次加锁了。 

ndo_start_xmit 函数调用驱动代码，在此之前还有 ptype_all 和 dev_queue_xmit_nit 。 ptype_all 是一个包含了诸如 packet capture 模块的列表，如果有 capture 应用在运行，那么 ptype_all 会把报文拷贝到应用程序使用的地方，所以 tcpdump 一类的工具看到的报文都是要发送给驱动的。如果使用了 checksum offload 或者 TSO，网卡会对报文进行操作，这将导致最终发到网络上的报文和 tcpdump 捕获的不同。报文传输完成之后，驱动中断处理函数返回 sk_buff 。 

## 九. 源码解读：接收数据

接收数据的流程大致就是从网络上接收到报文，然后一路网上送到 socket 接收缓存中。在执行完驱动中断程序，我们先来看看 napi poll handler： 

    static void net_rx_action(struct softirq_action *h)
    {
        struct softnet_data *sd = &__get_cpu_var(softnet_data);
        unsigned long time_limit = jiffies + 2;
        int budget = netdev_budget;
        void *have;
        local_irq_disable();
        
        while (!list_empty(&sd->poll_list)) {
            struct napi_struct *n;
            ...
        
            n = list_first_entry(&sd->poll_list, struct napi_struct, poll_list);
        
            if (test_bit(NAPI_STATE_SCHED, &n->state)) {
                work = n->poll(n, weight);
                trace_napi_poll(n);
            }
        }
        ...
    }
     
     
    int netif_receive_skb(struct sk_buff *skb)
     
    ... ===>
     
    static int __netif_receive_skb(struct sk_buff *skb)
    {
        struct packet_type *ptype, *pt_prev;
        ...
        __be16 type;
        ...
        list_for_each_entry_rcu(ptype, &ptype_all, list) {
            if (!ptype->dev || ptype->dev == skb->dev) {
                if (pt_prev)
                    ret = deliver_skb(skb, pt_prev, orig_dev);
            pt_prev = ptype;
            }
        }
        ...
        type = skb->protocol;
        list_for_each_entry_rcu(ptype,
        &ptype_base[ntohs(type) & PTYPE_HASH_MASK], list) {
        if (ptype->type == type &&
            (ptype->dev == null_or_dev || ptype->dev == skb->dev ||
            ptype->dev == orig_dev)) {
        
            if (pt_prev)
                ret = deliver_skb(skb, pt_prev, orig_dev);
            pt_prev = ptype;
        }
        
        if (pt_prev) {
            ret = pt_prev->func(skb, skb->dev, pt_prev, orig_dev);
            static struct packet_type ip_packet_type __read_mostly = {
                .type = cpu_to_be16(ETH_P_IP),
                .func = ip_rcv,
                ...
            }
        }
    };

`net_rx_action` 是收到报文的软件中断处理函数，首先，请求 napi `poll` 的驱动被从 `poll_list` 中拿出来，然后调用驱动的 poll handler。驱动把收到的报文转换成 sk_buff ，然后调用 `netif_receive_skb` . 

如果有模块在请求所有的报文，那么 `netif_receive_skb` 就把报文发送给这个模块。和报文传输一样，这些报文也要发送到 ptype_all 中注册的所有模块，以便可以被捕获程序读取。 

接着，报文根据类型被传输到上层，类型保存在以太网帧头部的 2 比特 `ethertype` 字段中，里面的值就代表着报文的类型，驱动会把对应的值保存到 sk_buff 结构体中（ skb->protocol ）。每个报文都有自己的 `packet_type` 结构体，并且会把该结构体的指针注册到 ptype_base 的哈希表中。IPv4 使用 `ip_packet_type` ，对应的 Type 字段的值是 IPv4 的 `ethertype` ( ETH_P_IP )。因此，IPv4 报文会调用 `ip_rcv` 函数： 

    int ip_rcv(struct sk_buff *skb, struct net_device *dev, ...)
    {
        struct iphdr *iph;
        u32 len;
        ...
        iph = ip_hdr(skb);
        ...
        if (iph->ihl < 5 || iph->version != 4)
            goto inhdr_error;
        if (!pskb_may_pull(skb, iph->ihl*4))
            goto inhdr_error;
    
        iph = ip_hdr(skb);
        if (unlikely(ip_fast_csum((u8 *)iph, iph->ihl)))
            goto inhdr_error;
        
        len = ntohs(iph->tot_len);
        if (skb->len < len) {
            IP_INC_STATS_BH(dev_net(dev), IPSTATS_MIB_INTRUNCATEDPKTS);
            goto drop;
        } else if (len < (iph->ihl*4))
            goto inhdr_error;
        
        ...
        return NF_HOOK(NFPROTO_IPV4, NF_INET_PRE_ROUTING, skb, dev, NULL,
        ip_rcv_finish);
        ... ===>
        int ip_local_deliver(struct sk_buff *skb)
        ...
        if (ip_hdr(skb)->frag_off & htons(IP_MF | IP_OFFSET)) {
            if (ip_defrag(skb, IP_DEFRAG_LOCAL_DELIVER))
                return 0;
        
        }
     
     
    return NF_HOOK(NFPROTO_IPV4, NF_INET_LOCAL_IN, skb, skb->dev, NULL,
    ip_local_deliver_finish);
    ... ===>
     
    static int ip_local_deliver_finish(struct sk_buff *skb)
    ...
    __skb_pull(skb, ip_hdrlen(skb));
    ...
    int protocol = ip_hdr(skb)->protocol;
    int hash, raw;
    const struct net_protocol *ipprot;
    
    ...
    hash = protocol & (MAX_INET_PROTOS - 1);
    ipprot = rcu_dereference(inet_protos[hash]);
    if (ipprot != NULL) {
        ...
        ret = ipprot->handler(skb);
        ... ===>
     
    static const struct net_protocol tcp_protocol = {
        .handler = tcp_v4_rcv,
        ...
    };

`ip_rcv` 函数执行 IP 层的任务，它会先检查报文的长度和头部的 checksum。在通过 `netfilter` 代码之后，它还会执行 `ip_local_deliver` ，如果需要还会把 IP 报文进行组装，最后调用 `ip_local_deliver_finish` 。 

`ip_local_deliver_finish` 使用 `__skb_pull` 移除 IP 头部，然后搜索报文中的上层协议，和 `ptype_base` 类似，每个传输层都会住在 `net_protocol` 结构体到 `inet_protos` 。IPv4 TCP 使用的是 `tcp_protocol` ，因此会调用注册的 `tcp_v4_rcv` 处理函数。 

当报文来到 TCP 层，根据 TCP 的状态和报文类型其处理逻辑也不同。这里，我们假定当前 TCP 是 ESTABLISHED 状态，然后收到了期望的数据报文。当没有报文丢失和乱序时，下面的流程会被频繁执行： 

```c
    int tcp_v4_rcv(struct sk_buff *skb)
    {
        const struct iphdr *iph;
        struct tcphdr *th;
        struct sock *sk;
        ...
        th = tcp_hdr(skb);
        if (th->doff < sizeof(struct tcphdr) / 4)
            goto bad_packet;
        if (!pskb_may_pull(skb, th->doff * 4))
            goto discard_it;
        ...
        th = tcp_hdr(skb);
        iph = ip_hdr(skb);
        TCP_SKB_CB(skb)->seq = ntohl(th->seq);
        TCP_SKB_CB(skb)->end_seq = (TCP_SKB_CB(skb)->seq + th->syn + th->fin +
        skb->len - th->doff * 4);
        TCP_SKB_CB(skb)->ack_seq = ntohl(th->ack_seq);
        TCP_SKB_CB(skb)->when = 0;
        TCP_SKB_CB(skb)->flags = iph->tos;
        TCP_SKB_CB(skb)->sacked = 0;
        
        sk = __inet_lookup_skb(&tcp_hashinfo, skb, th->source, th->dest);
        ...
        ret = tcp_v4_do_rcv(sk, skb);
    }
```

首先， `tcp_v4_rcv` 函数检查接收到的报文，如果报文头部大于数据偏移量( th->doff < sizeof(struct tcphdr ) /4 )，说明头部错误。然后 `__inet_lookup_skb` 在 TCP 连接哈希表中查找当前报文所属的连接。从找到的 sock 结构体中，就能找到所有其他相关的结构体，比如 `tcp_sock` 和 `socket` 。 

```c
    int tcp_v4_do_rcv(struct sock *sk, struct sk_buff *skb){
        ...
        if (sk->sk_state == TCP_ESTABLISHED) { /* Fast path */
            sock_rps_save_rxhash(sk, skb->rxhash);
            if (tcp_rcv_established(sk, skb, tcp_hdr(skb), skb->len)) {
            ... ===>
            }
        }
    }
    
    int tcp_rcv_established(struct sock *sk, struct sk_buff *skb,
    ...
    /*
    * Header prediction.
    */
     
    if ((tcp_flag_word(th) & TCP_HP_BITS) == tp->pred_flags &&
    TCP_SKB_CB(skb)->seq == tp->rcv_nxt &&
    !after(TCP_SKB_CB(skb)->ack_seq, tp->snd_nxt))) {
    ...
    if ((int)skb->truesize > sk->sk_forward_alloc)
        goto step5;
    
    NET_INC_STATS_BH(sock_net(sk), LINUX_MIB_TCPHPHITS);
     
    /* Bulk data transfer: receiver */
    __skb_pull(skb, tcp_header_len);
    __skb_queue_tail(&sk->sk_receive_queue, skb);
    skb_set_owner_r(skb, sk);
    tp->rcv_nxt = TCP_SKB_CB(skb)->end_seq;
    
    ...
    if (!copied_early || tp->rcv_nxt != tp->rcv_wup)
        __tcp_ack_snd_check(sk, 0);
    ...
    
    step5:
        if (th->ack && tcp_ack(sk, skb, FLAG_SLOWPATH) < 0)
        goto discard;
        tcp_rcv_rtt_measure_ts(sk, skb);
        /* Process urgent data. */
        tcp_urg(sk, skb, th);
        
        /* step 7: process the segment text */
        tcp_data_queue(sk, skb);
        tcp_data_snd_check(sk);
        tcp_ack_snd_check(sk);
        return 0;
        ...
    }
```

`tcp_v4_do_rcv` 执行和协议相关的内容。如果 TCP 处于 ESTABLISHED 状态，就会调用 `tcp_rcv_established` ， ESTABLISHED 状态的处理逻辑是独立的并且是单独进行优化的，因为它是最常用的状态。 `tcp_rcv_established` 首先执行头部预测代码，常见的状态是接收到的报文是期望收到的，并且没有要发送的数据了，比如序列号就是接收 TCP 期望的。这种情况下，只要把数据放到 socket 缓存，然后发送一个 ACK 报文就行。 

接下来，你会看到比较 `truesize` 和 `sk_forward_alloc` 的代码，这是为了检查 `socket` 接收缓存中是否有足够的空闲空间能添加新的报文数据。如果有，那么头部预测就是 hit （测试成功），那么 `__skb_pull` 会删除 TCP 头部，然后 `__skb_queue_tail` 会把报文加到 `socket` 接收缓存中。最后 `__tcp_ack_snd_check` 用来发送 ACK。到此，报文处理过程就结束了。 

如果 sockt 接收缓存中没有足够的空间，那么接下来的执行逻辑会话费比较长的事件。 `tcp_data_queue` 函数先分配额一个新的缓存空间，把报文数据加到 `socket` 缓存中，同时，socket 缓存大小也要自动进行增加。和前面的执行逻辑不同， `tcp_data_snd_check` 会执行，如果有可以发送的报文，就先发送。然后从调用 `tcp_ack_snd_check` 创建和发送 ACK 报文。 

这两种情况执行的代码并不多，这是常用 case 优化的结果。换句话说，不常用的 case 处理会更慢，比如报文乱序就属于不常用的 case。

## 十. 驱动和网卡之间怎么通信

驱动和网卡之间的通信过程位于网络栈的底层，大多数人对此并不怎么关系。但是，网卡在执行越来越多的任务以解决性能问题。理解两者通信的基础知识能让你理解这些技术。

驱动和网卡之间的通信是异步的。首先，驱动请求报文传输，CPU 不会等待结果就能执行其他任务。网卡把报文发送出去，然后通知 CPU，驱动把接收到的报文返回。

和报文传输类似，报文接收也是异步的。首先，驱动请求报文接收，CPU 也在执行其他任务。然后网卡接收到报文，并通过 CPU，驱动处理接收到的报文，并返回结果。

因为是异步的，所以需要一块空间来存放请求和应答的结果。多数情况下，网卡使用 `ring` 结构体， `ring` 类似于常见的 `queue` 数据结构，它有固定的大小。每个元素保存一个请求或者应答数据。元素是按顺序轮流使用的，这也是名字 `ring` 的来源。 

下图报文发送流程，你可以看到 `ring` 的用处： 

![][10]

驱动从上层接收到数据，然后创建一个网卡能够理解的发送描述符（send descriptor），描述符中保存了报文的大小和内存的地址。因为网卡需要内存的物理地址，因此驱动还要把虚拟地址转换为物理地址。然后，驱动把描述符加入到 `TX ring` （1）（发送描述符的 `ring` ）。 

然后，驱动通知网卡有新的请求（2）。驱动会直接把数据写到网卡的内存地址，CPU 会直接通过 PIO（Programmed I/O） 把数据发送给设备。

网卡收到请求之后从主机内存中获取描述符（3），因为网卡设备没有经过 CPU 而是直接访问内存，所以这个过程被成为 DMA（Direct Memory Access）。

拿到发送描述符之后，网卡确定报文的地址和大小，然后从主机内存中拿到真正的报文数据（4）。如果使用 checksum offload，当网卡从内存中获取报文数据的时候就会计算 checksum，因此不会产生额外的开销。

网卡把报文发送出去（5），然后把发送的报文数写到主机内从中（6）。接着，网卡发起一个中断（7），驱动读取发送的报文数，并返回已经发送的报文。

在下面的图片中，我们看到接收报文的流程：

![][11]

首先，驱动为接收到的报文分配主机内存，并创建接收描述符。接收描述符默认包含了缓存大小和内存地址，和发送描述符一样，接收描述符也保存了 DMA 使用的物理内存地址。然后，把发送描述符加到 RX ring（1）。这是接收请求，而 RX ring 代表这接受请求的 ring。

通过 PIO，驱动通知网卡有新的描述符（2），网卡从 RX ring 中拿到新的描述符。然后它把描述符中的大小和缓存地址保存在网卡内存中（3）。

报文接收之后（4），网卡把报文发送到主机内存的缓存中。如果 checksum offload 函数存在，那么网卡也会在此时计算 checksum。接收报文的真正大小、checksum 值、以及其他信息都保存在一个单独的 ring（接收返回 ring）（6）。receive return ring 保存了接收请求处理的结果。接着，让卡发送中断（7），驱动从接收返回 ring 中获取报文信息，对接收到的报文进行处理。如果需要，还会分配新的内存缓存空间重复（1）和 （2）步骤。

要优化网络栈，很多人觉得 ring 和中断的配置需要调整。如果 TX ring 很大，那么一次可以进行多次发送请求，也可以一次接收到多个报文。比较大的 ring 对大量接收和发送的情况有好处，但是大多数情况下，网卡使用计时器来减少中断的次数，因为 CPU 处理中断开销很大。为了防止产生大多的中断，对主机造成泛洪，接收和发送报文的中断会定期收集和发送。

## 十一. 缓存和流量控制

流量控制在网络栈的多个阶段都有，下图展示了发送数据要用到的缓存。

![][12]

首先，应用产生数据并把它加到 socket 发送缓存中，如果缓存中没有空间，系统调用就会失败或者阻塞。因此，应用层发往内核的数据必须要通过 socket 缓存大小进行限制。

TCP 创建报文，并把报文发送到传输队列中（qdisc），这是一个典型的 FIFO 队列，队列的最大值可以通过 `ifocnfig` 命令输出的 `txqueuelen` 来查看。通常情况下，这个值在几千报文大小。 

TX ring 在驱动和网卡之间，之前也说过，这是一个传输请求的队列。如果队列中没有空间，就无法执行传输请求，报文会在传输队列（qdisc）中堆积。如果堆积的报文太多，就会有报文被丢弃。

网卡把要发送的报文保存到内部的缓存中，这个缓存中的报文传输速率直接收到物理速率的影响（比如，1 Gb/s 的网卡不可能提供 10Gb/s 的性能）。如果网卡缓存中没有空闲的空间，传输报文就必须暂停。

如果内核中报文发送速率比网卡的报文处理速率高，那么报文就会集聚在网卡的缓存中。如果缓存中没有空闲空间，从 TX ring 中处理传输报文就必须停止，那么会有更多的报文堆在 TX ring 中，直到最后队列中没有空间。驱动也没有没法执行传输请求，报文会堆在传输队列中。就像这样，底层的问题会通过缓存一层层网上传播。

下图展示了报文接收的流程：

![][13]

报文先保存在网卡的接收缓存中，从流量控制的角度看，驱动和网卡之间的 RX ring 可以看到报文的缓存。驱动从 RX ring 获取报文 ，然后把报文发送到上层。驱动和上层之间没有缓存，因为网卡驱动使用 NAPI 进行数据传输。因此，可以认为上层直接从 RX ring 中读取报文。报文的数据保存在 socket 接收缓存中，应用从 socket 接收缓存中读取数据。

不支持 NAPI 的驱动会先把报文保存到 backlog 对咯中，然后 NAPI 处理函数去队列中获取报文，因此 backlog 队列可以看到驱动和上层之间的缓存。

如果内核处理报文的速度小于报文流向网卡的速度，那么 RX ring 就会满，网卡的缓存也会满。如果有使用以太网流量控制，那么网卡会发送请求停止继续向网卡发送数据，或者直接丢包。

TCP socket 不会因为 socket 接收缓存没有空间就丢包，因为 TCP 提供的是端对端的流控。但是如果应用程序处理报文数据的速度很慢，UDP 会把报文丢弃，因为 UDP 不提供流量控制。

上面两个图中，驱动使用的 TX ring 和 RX ring 大小就是 ethtool 命令显示的 rings 大小。大多数对吞吐量有要求的情况下，提供 ring 大小和 socket 缓存大小很有用。提高这些大小，能够减少因为空间不够导致的失败率，而且能够提高发送和传输报文的速率。 

## 十二. 总结

最初，我只是计划介绍一些网络知识，帮助读者去开发网络应用、执行性能测试以及调试性能问题。这篇文章的介绍内容很多，希望它能够在开发网络应用和监控网络性能方面对你提供帮助。 TCP/IP 协议本身很复杂，而且有很多特殊情况。幸运的是，你不用理解 TCP/IP 的所有代码才能理解和分析网络性能问题，这篇文章的知识应该就够了。

随着系统网络栈的不断发展，现在的服务器能毫无压力地提供 10-20 Gb/s 的吞吐率。而且还有很多的技术来提高性能，比如 TSO、LRO、RSS、GSO、GRO、UFO、XPS、IOAT、DDIO 和 TOE 等，这次词汇很让人迷惑。

在接下来的文章中，我会继续从性能角度解释网络栈，并讲解这些技术的问题和影响。


[1]: http://cizixs.com/2017/07/27/understand-tcp-ip-network-stack
[3]: ./img/7biau2A.png
[4]: ./img/2QBVZnM.png
[5]: ./img/ZZji2qM.png
[6]: ./img/B3ia6jJ.png
[7]: ./img/r6Bb6zy.png
[8]: ./img/yMNFV3N.png
[9]: ./img/qq2AVrZ.png
[10]: ./img/yUrIfef.png
[11]: ./img/YVnQrqm.png
[12]: ./img/NrEFzmM.png
[13]: ./img/BzyMRra.png
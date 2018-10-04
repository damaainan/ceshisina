## linux IO

2017.07.23 15:48*

来源：[https://www.jianshu.com/p/4dfe41299e07](https://www.jianshu.com/p/4dfe41299e07)



1. 基础介绍
    1. 存储介质
    2. 文件描述符
    3. 标准io
    4. 直接io
2. IO模式
    1. 阻塞I/O (blocking IO)
    2. 非阻塞 I/O（nonblocking IO）
    3. I/O 多路复用（ IO multiplexing）
    4. 异步 I/O（asynchronous IO）
    5. 信号驱动I/O (signal driven I/O)
    6. I/O模式总结
        1. blocking和non-blocking的区别
        2. synchronous IO和asynchronous IO的区别
3. 其他
    1. mmap和read/write区别
    2. iostat
4. 参考


## 1 基础介绍
### 1.1 存储介质

现代计算机物理存储机制金字塔如下图所示：


![][0]


物理存储金字塔


各级存储方式速度和容量：


![][1]


image.png

### 1.2 文件描述符

文件描述符（File descriptor）是计算机科学中的一个术语，是一个用于表述指向文件的引用的抽象化概念。文件描述符在形式上是一个非负整数。实际上，它是一个索引值，指向内核为每一个进程所维护的该进程打开文件的记录表。当程序打开一个现有文件或者创建一个新文件时，内核向进程返回一个文件描述符。在程序设计中，一些涉及底层的程序编写往往会围绕着文件描述符展开。但是文件描述符这一概念往往只适用于UNIX、Linux这样的操作系统。
### 1.3 标准io

在 Linux 中，这种访问文件的方式是通过两个系统调用实现的：read() 和 write()。当应用程序调用read() 系统调用读取一块数据的时候，如果该块数据已经在内存中了，那么就直接从内存中读出该数据并返回给应用程序；如果该块数据不在内存中，那么数据会被从磁盘上读到页高缓存中去，然后再从页缓存中拷贝到用户地址空间中去。如果一个进程读取某个文件，那么其他进程就都不可以读取或者更改该文件；对于写数据操作来说，当一个进程调用了 write() 系统调用往某个文件中写数据的时候，数据会先从用户地址空间拷贝到操作系统内核地址空间的页缓存中去，然后才被写到磁盘上。但是对于这种标准的访问文件的方式来说，在数据被写到页缓存中的时候，write() 系统调用就算执行完成，并不会等数据完全写入到磁盘上。

　　Linux 在这里采用的是我们前边提到的延迟写机制（ deferred writes ）。如果用户采用的是延迟写机制（ deferred writes ），那么应用程序就完全不需要等到数据全部被写回到磁盘，数据只要被写到页缓存中去就可以了。在延迟写机制的情况下，操作系统会定期地将放在页缓存中的数据刷到磁盘上。
### 1.4  直接io

凡是通过直接 I/O 方式进行数据传输，数据均直接在用户地址空间的缓冲区和磁盘之间直接进行传输，完全不需要页缓存的支持。操作系统层提供的缓存往往会使应用程序在读写数据的时候获得更好的性能，但是对于某些特殊的应用程序，比如说数据库管理系统这类应用，他们更倾向于选择他们自己的缓存机制，因为数据库管理系统往往比操作系统更了解数据库中存放的数据，数据库管理系统可以提供一种更加有效的缓存机制来提高数据库中数据的存取性能。
## 2 IO模式

对于一次标准IO访问（以read举例），数据会先被拷贝到操作系统内核的缓冲区中，然后才会从操作系统内核的缓冲区拷贝到应用程序的地址空间。所以说，当一个read操作发生时，它会经历两个阶段：


* 等待数据准备 (Waiting for the data to be ready)
* 将数据从内核拷贝到进程中 (Copying the data from the kernel to the process)


正式因为这两个阶段，linux系统产生了下面五种网络模式的方案。


* 阻塞 I/O（blocking IO）
* 非阻塞 I/O（nonblocking IO）
* I/O 多路复用（ IO multiplexing）
* 信号驱动 I/O（ signal driven IO）
* 异步 I/O（asynchronous IO）


### 2.1 阻塞I/O (blocking IO)

在linux中，默认情况下所有的socket都是blocking， 一个典型的读操作流程大概是这样：


![][2]


阻塞I/O


当用户进程调用了recvfrom这个系统调用，kernel就开始了IO的第一个阶段：准备数据（对于网络IO来说，很多时候数据在一开始还没有到达。比如，还没有收到一个完整的UDP包。这个时候kernel就要等待足够的数据到来）。这个过程需要等待，也就是说数据被拷贝到操作系统内核的缓冲区中是需要一个过程的。而在用户进程这边，整个进程会被阻塞（当然，是进程自己选择的阻塞）。当kernel一直等到数据准备好了，它就会将数据从kernel中拷贝到用户内存，然后kernel返回结果，用户进程才解除block的状态，重新运行起来。

### 2.2 非阻塞 I/O（nonblocking IO）

linux下，可以通过设置socket使其变为non-blocking。当对一个non-blocking socket执行读操作时，流程是这个样子：


![][3]


非阻塞I/O


当用户进程发出read操作时，如果kernel中的数据还没有准备好，那么它并不会block用户进程，而是立刻返回一个error。从用户进程角度讲 ，它发起一个read操作后，并不需要等待，而是马上就得到了一个结果。用户进程判断结果是一个error时，它就知道数据还没有准备好，于是它可以再次发送read操作。一旦kernel中的数据准备好了，并且又再次收到了用户进程的system call，那么它马上就将数据拷贝到了用户内存，然后返回。

### 2.3 I/O 多路复用（ IO multiplexing）

IO multiplexing就是我们说的select，poll，epoll，有些地方也称这种IO方式为event driven IO。select/epoll的好处就在于单个process就可以同时处理多个网络连接的IO。它的基本原理就是select，poll，epoll这个function会不断的轮询所负责的所有socket，当某个socket有数据到达了，就通知用户进程。


![][4]


I/O多路复用


当用户进程调用了select，那么整个进程会被block，而同时，kernel会“监视”所有select负责的socket，当任何一个socket中的数据准备好了，select就会返回。这个时候用户进程再调用read操作，将数据从kernel拷贝到用户进程。

I/O 多路复用的特点是通过一种机制一个进程能同时等待多个文件描述符，而这些文件描述符（套接字描述符）其中的任意一个进入读就绪状态，select()函数就可以返回。

这个图和blocking IO的图其实并没有太大的不同，事实上，还更差一些。因为这里需要使用两个system call (select 和 recvfrom)，而blocking IO只调用了一个system call (recvfrom)。但是，用select的优势在于它可以同时处理多个connection。


所以，如果处理的连接数不是很高的话，使用select/epoll的web server不一定比使用multi-threading + blocking IO的web server性能更好，可能延迟还更大。select/epoll的优势并不是对于单个连接能处理得更快，而是在于能处理更多的连接。）

在IO multiplexing Model中，实际中，对于每一个socket，一般都设置成为non-blocking，但是，如上图所示，整个用户的process其实是一直被block的。只不过process是被select这个函数block，而不是被socket IO给block。
### 2.4 异步 I/O（asynchronous IO）

linux下的asynchronous IO其实用得很少。先看一下它的流程：


![][5]


异步I/O


当应用程序调用aio_read的时候，内核一方面去取数据报内容返回，另外一方面将程序控制权还给应用进程，应用进程继续处理其他事务。这样应用进程就是一种非阻塞的状态。

当内核的数据报就绪的时候，是由内核将数据报拷贝到应用进程中，返回给aio_read中定义好的函数处理程序。
### 2.5 信号驱动I/O (signal driven I/O)


![][6]


信号驱动I/O模型


信号驱动IO模型是应用进程告诉内核：当你的数据报准备好的时候，给我发送一个信号哈，并且调用我的信号处理函数来获取数据报。这个模型是由信号进行驱动


与I/O multiplexing (select and poll)相比，它的优势是，免去了select的阻塞与轮询，当有活跃套接字时，由注册的handler处理，但是copy date过程依然是阻塞的，所以属于非阻塞同步IO。
### 2.6 I/O模式总结
#### 2.6.1 blocking和non-blocking的区别

调用blocking IO会一直block住对应的进程直到操作完成，而non-blocking IO在kernel还准备数据的情况下会立刻返回。
#### 2.6.2 synchronous IO和asynchronous IO的区别

POSIX对于异步IO和非阻塞IO的定义如下：


* A synchronous I/O operation causes the requesting process to be blocked until that I/O operation completes;
* An asynchronous I/O operation does not cause the requesting process to be blocked;


定义中所指的”IO operation”是指真实的IO操作，就是例子中的recvfrom这个system call。non-blocking IO在执行recvfrom这个system call的时候，如果kernel的数据没有准备好，这时候不会block进程。但是，当kernel中数据准备好的时候，recvfrom会将数据从kernel拷贝到用户内存中，这个时候进程是被block了，在这段时间内，进程是被block的。

而asynchronous IO则不一样，当进程发起IO 操作之后，就直接返回再也不理睬了，直到kernel发送一个信号，告诉进程说IO完成。在这整个过程中，进程完全没有被block。


![][7]


I/O模式比较

## 3 其他
### 3.1 mmap和read/write区别

常规文件操作需要从磁盘到页缓存再到用户主存的两次数据拷贝。而mmap操控文件，只需要从磁盘到用户主存的一次数据拷贝过程。说白了，mmap的关键点是实现了用户空间和内核空间的数据直接交互而省去了空间不同数据不通的繁琐过程，因此mmap效率更高。

　但是linux内核对于read操作有readahead机制，在读取文件的时候会预读一部分文件内容，在顺序访问文件的时候read效率更高。mmap更适合随机访问文件的场景。
### 3.2 iostat

通过iostat方便查看CPU、网卡、tty设备、磁盘、CD-ROM 等等设备的活动情况, 负载信息。

命令格式
`iostat[参数][时间][次数]`

命令参数

-C 显示CPU使用情况

-d 显示磁盘使用情况

-k 以 KB 为单位显示

-m 以 M 为单位显示

-N 显示磁盘阵列(LVM) 信息

-n 显示NFS 使用情况

-p[磁盘] 显示磁盘和分区的情况

-t 显示终端和CPU的信息

-x 显示详细信息

-V 显示版本信息

命令执行范例

```
iostat -x 1 1
avg-cpu:  %user   %nice %system %iowait  %steal   %idle
          15.57    1.25    3.11    0.04    0.00   80.04

Device:         rrqm/s   wrqm/s     r/s     w/s    rkB/s    wkB/s avgrq-sz avgqu-sz   await r_await w_await  svctm  %util
sda               0.35   258.72    3.00   32.47   147.38  1851.91   112.73     0.03    0.80    2.35    0.66   0.74   2.63

```

cpu属性值说明：

%user：CPU处在用户模式下的时间百分比。

%nice：CPU处在带NICE值的用户模式下的时间百分比。

%system：CPU处在系统模式下的时间百分比。

%iowait：CPU等待输入输出完成时间的百分比，表示在一个采样周期内有百分之几的时间属于以下情况：CPU空闲、并且有仍未完成的I/O请求。iowait高并不代表等待I/O的进程数量增多了，也不能证明等待I/O的总时间增加了，还需要结合其他指标来看。

%steal：管理程序维护另一个虚拟处理器时，虚拟CPU的无意识等待时间百分比。

%idle：CPU空闲时间百分比。

disk属性值说明：


* rrqm/s: 每秒进行 merge 的读操作数目。即 rmerge/s



* wrqm/s: 每秒进行 merge 的写操作数目。即 wmerge/s
* r/s: 每秒完成的读 I/O 设备次数。即 rio/s
* w/s: 每秒完成的写 I/O 设备次数。即 wio/s
* rsec/s: 每秒读扇区数。即 rsect/s
* wsec/s: 每秒写扇区数。即 wsect/s
* rkB/s: 每秒读K字节数。是 rsect/s 的一半，因为每扇区大小为512字节。
* wkB/s: 每秒写K字节数。是 wsect/s 的一半。
* avgrq-sz: 平均每次设备I/O操作的数据大小 (扇区)。
* avgqu-sz: 平均I/O队列长度。
* await: 平均每次设备I/O操作的等待时间 (毫秒)。
* svctm: 平均每次设备I/O操作的服务时间 (毫秒)。
* %util: 一秒中有百分之多少的时间用于 I/O 操作，即被io消耗的cpu百分比


形象的比喻：


* r/s+w/s 类似于交款人的总数
* 平均队列长度(avgqu-sz)类似于单位时间里平均排队人的个数
* 平均服务时间(svctm)类似于收银员的收款速度
* 平均等待时间(await)类似于平均每人的等待时间
* 平均I/O数据(avgrq-sz)类似于平均每人所买的东西多少
* I/O 操作率 (%util)类似于收款台前有人排队的时间比例


## 4 参考


* [http://weibo.com/p/1001603797133609325472?comment=1][8]
* [http://0xffffff.org/2017/05/01/41-linux-io/][9]
* [https://segmentfault.com/a/1190000003063859][10]
* [http://www.cnblogs.com/huxiao-tee/p/4660352.html][11]
* [http://linuxperf.com/?p=33][12]
* [http://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/iostat.html][13]
* [http://www.cnblogs.com/LittleHann/p/3897910.html][14]



[8]: https://link.jianshu.com?t=http://weibo.com/p/1001603797133609325472?comment=1
[9]: https://link.jianshu.com?t=http://0xffffff.org/2017/05/01/41-linux-io/
[10]: https://link.jianshu.com?t=https://segmentfault.com/a/1190000003063859
[11]: https://link.jianshu.com?t=http://www.cnblogs.com/huxiao-tee/p/4660352.html
[12]: https://link.jianshu.com?t=http://linuxperf.com/?p=33
[13]: https://link.jianshu.com?t=http://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/iostat.html
[14]: https://link.jianshu.com?t=http://www.cnblogs.com/LittleHann/p/3897910.html
[0]: ./img/6497917-773610d8fe3b68d9.png
[1]: ./img/6497917-0ab4e6ee52c447ce.png
[2]: ./img/6497917-33d28f218da030b7.png
[3]: ./img/6497917-053a1c6642b9ca0e.png
[4]: ./img/6497917-ad4d349696edd919.png
[5]: ./img/6497917-086303cd4e8cea63.png
[6]: ./img/6497917-a51202e924174b88.png
[7]: ./img/6497917-f3e2d9b7771d86dd.png
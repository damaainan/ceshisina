## IO多路复用（一）-- Select、Poll、Epoll

来源：[https://segmentfault.com/a/1190000016400053](https://segmentfault.com/a/1190000016400053)

在上一篇博文中提到了五种IO模型，关于这五种IO模型可以参考博文[IO模型浅析-阻塞、非阻塞、IO复用、信号驱动、异步IO、同步IO][0]，本篇主要介绍IO多路复用的使用和编程。
## IO多路复用的概念

多路复用是一种机制，可以用来监听多种描述符，如果其中任意一个描述符处于就绪的状态，就会返回消息给对应的进程通知其采取下一步的操作。
## IO多路复用的优势

当进程需要等待多个描述符的时候，通常情况下进程会开启多个线程，每个线程等待一个描述符就绪，但是多路复用可以同时监听多个描述符，进程中无需开启线程，减少系统开销，在这种情况下多路复用的性能要比使用多线程的性能要好很多。
## 相关API介绍

在linux中，关于多路复用的使用，有三种不同的API，select、poll和epoll
### Select介绍

select的使用需要引入sys/select.h头文件，API函数比较简单，函数原型如下：

```c
int select (int __nfds, fd_set *__restrict __readfds,
           fd_set *__restrict __writefds,
           fd_set *__restrict __exceptfds,
           struct timeval *__restrict __timeout);
```
#### fd_set

其中有一个很重要的结构体fd_set，该结构体可以看作是一个描述符的集合，可以将fa_set看作是一个位图，类似于操作系统中的位图，其中每个整数的每一bit代表一个描述符，。

举个简单的例子，fd_set中元素的个数为2，初始化都为0，则fd_set中含有两个整数0，假设一个整数的长度8位（为了好举例子），则展开fd_set的结构就是 00000000 0000000，如果这个时候添加一个描述符为3，则对应fd_set编程 00000000 00001000，可以看到在这种情况下，第一个整数标记描述符0~7，第二个整数标记8~15，依次类推。fd_set有四个关联的api

```c
void FD_ZERO(fd_set *fdset) //清空fdset，将所有bit置为0
void FD_SET(int fd, fd_set *fdset) //将fd对应的bit置为1
void FD_CLR(int fd, fd_set *fdset) //将fd对应的bit置为0
void FD_ISSET(int fd, fd_set *fdset) //判断fd对应的bit是否为1,也就是fd是否就绪
```

select函数中存在三个fd_set集合，分别代表三种事件，__readfds表示读描述符集合，__writefds表示读描述符集合，__exceptfds表示读描述符集合，当对应的fd_set = NULL时，表示不监听该类描述符。
#### __nfds

__nfds是fd_set中最大的描述符+1，当调用select的时候，内核态会判断fd_set中描述符是否就绪，__nfds告诉内核最多判断到哪一个描述符。
#### timeval

```c
struct timeval {
    long tv_sec;    //秒
    long tv_usec;    //微秒
}
```

参数__timeout指定select的工作方式：

* __timeout= NULL，表示select永远等待下去，直到其中至少存在一个描述符就绪
* __timeout结构体中秒或者微妙是一个大于0的整数，表示select等待一段固定的事件，若该短时间内未有描述符就绪则返回
* __timeout= 0，表示不等待，直接返回


#### 函数返回

select函数返回产生事件的描述符的数量，如果为-1表示产生错误

值得注意的是，比如用户态要监听描述符1和3的读事件，则将readset对应bit置为1，当调用select函数之后，若只有1描述符就绪，则readset对应bit为1，但是描述符3对应的位置为0，这就需要注意，每次调用select的时候，都需要重新初始化并赋值readset结构体，将需要监听的描述符对应的bit置为1，而不能直接使用readset，因为这个时候readset已经被内核改变了。### Poll介绍

select中，每个fd_set结构体最多只能标识1024个描述符，在poll中去掉了这种限制，使用poll需要引入头文件sys/poll.h，poll调用的API如下：

```c
int poll (struct pollfd *__fds, nfds_t __nfds, int __timeout);
```
#### pollfd

```c
struct pollfd {
    int fd;                    // poll的文件描述符
    short int events;        // poll关心的事件类型
    short int revents;        // 发生的事件类型
  };
```

Poll使用结构体pollfd来指定一个需要监听的描述符，结构体中fd为需要监听的文件描述符，events为需要监听的事件类型，而revents为经过poll调用之后返回的事件类型，在调用poll的时候，一般会传入一个pollfd的结构体数组，数组的元素个数表示监控的描述符个数，所以pollfd相对于select，没有最大1024个描述符的限制。

事件类型有多种，在bits/poll.h中定义了多种事件类型，主要如下：

```c
#define POLLIN        0x001        // 有数据可读
#define POLLPRI        0x002        // 有紧迫数据可读
#define POLLOUT        0x004        // 现在写数据不会导致阻塞

# define POLLRDNORM    0x040        // 有普通数据可读
# define POLLRDBAND    0x080        // 有优先数据可读
# define POLLWRNORM    0x100        // 写普通数据不会导致阻塞
# define POLLWRBAND    0x200        // 写优先数据不会导致阻塞

#define POLLERR        0x008        // 发生错误
#define POLLHUP        0x010        // 挂起
#define POLLNVAL    0x020        // 无效文件描述符
```

当一个文件描述符要同时监听读写事件时，可以写成 events = POLLIN | POLLOUT

可以看到，poll中使用结构体保存一个文件描述符关心的事件，而在select中，统一使用fd_set，一个fd_set中可以是所有需要监听读事件的文件描述符，也可以是所有需要写事件的文件描述符。相比来说，poll比select更加的灵活，在调用poll之后，无需像select一样需要重新对文件描述符初始化，因为poll返回的事件写在了pollfd->revents成员中。

#### __fds

`__fds`的作用同select中的`__nfds`，表示pollfd数组中最大的下标索引
#### `__timeout`

* __timeout = -1：poll阻塞直到有事件产生
* __timeout = -0：poll立刻返回
* `__timeout != -1 && __timeout != 0`：poll阻塞`__timeout`对应的时候，如果超过该时间没有事件产生则返回


#### 函数返回

poll函数返回产生事件的描述符的数量，如果返回0表示超时，如果为-1表示产生错误
### Epoll介绍

epoll中，使用一个描述符来管理多个文件描述符，使用epoll需要引入头文件sys/epoll.h，epoll相关的api函数如下：

```c
int epoll_create (int __size);
int epoll_ctl (int __epfd, int __op, int __fd, struct epoll_event *__event);
int epoll_wait (int __epfd, struct epoll_event *__events, int __maxevents, int __timeout);
```
#### epoll_event

```c
typedef union epoll_data {
  void *ptr;     // 可以用改指针指向自定义的参数
  int fd;         // 可以用改成员指向epoll所监控的文件描述符
  uint32_t u32;
  uint64_t u64;
} epoll_data_t;

struct epoll_event {
  uint32_t events;        // epoll事件
  epoll_data_t data;    // 用户数据
} __EPOLL_PACKED;
```

epoll_event结构体中，首先是一个events的整型变量，类似于pollfd->events，表示要监控的事件，events支持的事件类型在sys/epoll.h的头文件中，跟pollfd中的事件类型基本移植，如下，这里只写出一部分：

```c
enum EPOLL_EVENTS {
    EPOLLIN = 0x001,
#define EPOLLIN EPOLLIN     // 有数据可读
    EPOLLPRI = 0x002,
#define EPOLLPRI EPOLLPRI     // 有紧迫数据可读
    EPOLLOUT = 0x004,
#define EPOLLOUT EPOLLOUT     // 现在写数据不会导致阻塞
    EPOLLRDNORM = 0x040,
#define EPOLLRDNORM EPOLLRDNORM        // 有普通数据可读
    EPOLLRDBAND = 0x080,
#define EPOLLRDBAND EPOLLRDBAND        // 有优先数据可读
    EPOLLWRNORM = 0x100,
#define EPOLLWRNORM EPOLLWRNORM        // 写普通数据不会导致阻塞
    EPOLLWRBAND = 0x200,
#define EPOLLWRBAND EPOLLWRBAND        // 写优先数据不会导致阻塞
    ...
    EPOLLERR = 0x008,
#define EPOLLERR EPOLLERR    // 发生错误
    EPOLLHUP = 0x010,
#define EPOLLHUP EPOLLHUP    // 挂起
    EPOLLRDHUP = 0x2000,
       ...
  };
```

epoll_event中的data指向一个共用体结构，可以用该共用体保存自定义的参数，或者指向被监控的文件描述符。
#### epoll_create

```c
int epoll_create (int __size);
```

epoll_create函数创建一个epoll实例并返回，该实例可以用于监控__size个文件描述符
#### epoll_ctl

```c
int epoll_ctl (int __epfd, int __op, int __fd, struct epoll_event *__event);
```

该函数用来向epoll中注册事件函数，其中__epfd为epoll_create返回的epoll实例，__op表示要进行的操作，__fd为要进行监控的文件描述符，__event要监控的事件。

__op可用的类型定义在sys/epoll.h头文件中，如下：

```c
#define EPOLL_CTL_ADD 1        // 添加文件描述符
#define EPOLL_CTL_DEL 2        // 删除文件描述符
#define EPOLL_CTL_MOD 3        //    修改文件描述符（指的是epoll_ctl中传入的__event）
```

该函数如果调用成功返回0，否则返回-1。
#### epoll_wait

```c
int epoll_wait (int __epfd, struct epoll_event *__events, int __maxevents, int __timeout);
```

epoll_wait类似与select中的select函数、poll中的poll函数，等待内核返回监听描述符的事件产生，其中__epfd是epoll_create创建的epoll实例，__events数组为epoll_wait要返回的已经产生的事件集合，其中第i个元素成员的__events[i]->data->fd表示产生该事件的描述符，__maxevents为希望返回的最大的事件数量（通常为__events的大小），__timeout和poll中的__timeout相同。该函数返回已经就绪的事件的数量，如果为-1表示出错。
## select、poll、epoll比较

select和poll的机制基本相同，只不过poll没有select最大文件描述符的限制，在具体使用的时候，有如下缺点：

* 每次调用select或者poll，都需要将监听的fd_set或者pollfd发送给内核态，如果需要监听大量的文件描述符，这样的效率是很低下的
* 在内核态中，每次需要对传入的文件描述符进行轮询，查询是否有对应的事件产生。


epoll的高效在于将这些分开，首先epoll不是在每次调用epoll_wait的时候，将描述符传送给内核，而是在epoll_ctl的时候传送描述符给内核，当调用epoll_wait的收，不用每次都接收

不像select和poll使用一个单独的API函数，在epoll中，使用epoll_create创建一个epoll实例，然后当调用epoll_ctl新增监听描述符的时候，这个时候才将用户态的描述符发送到内核态，因为epoll_wait调用的频率肯定要比epoll_create的频率要高，所以当epoll_wait的时候无需传送任何描述符到用户态；

关于第二点，在内核态中，使用一个描述符就绪的链表，当描述符就绪的时候，在内核态中会使用回调函数，该函数会将对应的描述符添加入就绪链表中，那么当epoll_wait调用的时候，就不需要遍历所有的描述符查看是否有就绪的事件，而是直接查看链表是否为空。
## 总结

可以使用一个生活中的场景来对三者的区别做个总结，仍然接着笔者的上一篇博文[IO模型浅析-阻塞、非阻塞、IO复用、信号驱动、异步IO、同步IO][0]中吃饭的例子：

在这个例子中，服务员和餐厅代表内核，客户“你”就是用户态进程，可能觉得这个例子写的不好，在这里写下加深记忆。

select和poll：你去餐厅请客吃饭，你是个豪爽的人，点了很多菜，你告诉服务员对应种类的菜有多少上多少，服务员将菜名一一写在纸上。然后你开始问服务员饭菜有好了么，服务员看着你的菜单一大串，头皮发麻，于是按着菜单的顺序去厨房查看饭菜有没有好，如果菜没有好就划掉菜单中对应的菜，终于找出了所有已经烧好的饭菜，服务员把饭菜端给了你。可是这个时候菜单上只能看到已经准备好的菜了，没准备好的菜看不清了，你觉得这个服务员做事很傻逼，没办法将就点，谁让你性格好呢，于是你重新写了一份菜单（可能这个过程中你又想点一些新的菜或者删除一些菜）。接下来你又去问饭菜好没好，服务员又开始按照菜单的顺序去厨房查看饭菜有没有好。。。（select和poll的主要区别就在于，select中的菜单是有限的，而poll中的菜单是无限的，你可以点任意种类的菜）

epoll：你去餐厅请客吃饭，你是个豪爽的人，点了很多菜，你告诉服务员对应种类的菜有多少上多少，服务员将菜名一一录入到餐厅后台的菜单管理软件中，厨房的师傅烧好一道菜在管理软件中标记完成一下，然后在烧好的菜上挂上对应的桌号放在取菜区，这个时候你来问服务员饭菜有准备好的么，服务员于是查一下管理软件，有标记欸，于是从取菜区取出对应桌号的饭菜送给你，清空标记。过了段时间，你又想点一道新的菜，于是叫来服务员，服务员在菜单软件中添加一栏。接下来你又去问饭菜好没好，服务员又开始看菜单软件中是否有标记完成的信息。。。

另外关于epoll的高效还有很多细节，例如使用mmap将用户空间和内核空间的地址映射到同一块物理内存地址，使用红黑树存储要监听的事件等等，具体的细节可以参考博文[select、poll、epoll之间的区别总结整理][2]、[高并发网络编程之epoll详解][3]、[Linux下的I/O复用与epoll详解][4]、[彻底学会使用epoll(一)——ET模式实现分析][5]等几篇文章。

接下来使用[select、poll、epoll实现一个TCP反射程序][6]
## 参考资料

UNIX网络变成卷1：套接字联网API

[select、poll、epoll之间的区别总结整理][2]

[高并发网络编程之epoll详解][3]

[Linux下的I/O复用与epoll详解][4]

作者：[yearsj][10]转载请注明出处：[https://segmentfault.com/a/11...][11]

[0]: https://segmentfault.com/a/1190000016359495
[1]: https://segmentfault.com/a/1190000016359495
[2]: https://www.cnblogs.com/Anker/p/3265058.html
[3]: https://blog.csdn.net/shenya1314/article/details/73691088
[4]: https://www.cnblogs.com/lojunren/p/3856290.html
[5]: http://blog.chinaunix.net/uid-28541347-id-4273856.html
[6]: https://segmentfault.com/a/1190000016400430
[7]: https://www.cnblogs.com/Anker/p/3265058.html
[8]: https://blog.csdn.net/shenya1314/article/details/73691088
[9]: https://www.cnblogs.com/lojunren/p/3856290.html
[10]: https://segmentfault.com/u/yearsj
[11]: https://segmentfault.com/a/1190000016400053
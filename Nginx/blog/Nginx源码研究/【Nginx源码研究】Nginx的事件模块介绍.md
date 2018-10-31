## 【Nginx源码研究】Nginx的事件模块介绍

来源：[https://segmentfault.com/a/1190000016856346](https://segmentfault.com/a/1190000016856346)

运营研发团队  谭淼
## 一、nginx模块介绍

高并发是nginx最大的优势之一，而高并发的原因就是nginx强大的事件模块。本文将重点介绍nginx是如果利用Linux系统的epoll来完成高并发的。

首先介绍nginx的模块，nginx1.15.5源码中，自带的模块主要分为core模块、conf模块、event模块、http模块和mail模块五大类。其中mail模块比较特殊，本文暂不讨论。

![][0]

查看nginx模块属于哪一类也很简单，对于每一个模块，都有一个ngx_module_t类型的结构体，该结构体的type字段就是标明该模块是属于哪一类模块的。以ngx_http_module为例：

```c
ngx_module_t  ngx_http_module = {
    NGX_MODULE_V1,
    &ngx_http_module_ctx,                  /* module context */
    ngx_http_commands,                     /* module directives */
    NGX_CORE_MODULE,                       /* module type */
    NULL,                                  /* init master */
    NULL,                                  /* init module */
    NULL,                                  /* init process */
    NULL,                                  /* init thread */
    NULL,                                  /* exit thread */
    NULL,                                  /* exit process */
    NULL,                                  /* exit master */
    NGX_MODULE_V1_PADDING
};
```

可以ngx_core_module是属于NGX_CORE_MODULE类型的模块。

由于本文主要介绍使用epoll来完成nginx的事件驱动，故主要介绍core模块的ngx_events_module与event模块的ngx_event_core_module、ngx_epoll_module。
## 二、epoll介绍
## 2.1、epoll原理

关于epoll的实现原理，本文不会具体介绍，这里只是介绍epoll的工作流程。具体的实现参考：[https://titenwang.github.io/2...][15]

epoll的使用是三个函数：

```c
int epoll_create(int size);
int epoll_ctl(int epfd, int op, int fd, struct epoll_event *event);
int epoll_wait(int epfd, struct epoll_event *events, int maxevents, int timeout);
```

首先epoll_create函数会在内核中创建一块独立的内存存储一个eventpoll结构体，该结构体包括一颗红黑树和一个链表，如下图所示:

![][1]

然后通过epoll_ctl函数，可以完成两件事。

* （1）将事件添加到红黑树中，这样可以防止重复添加事件；
* （2）将事件与网卡建立回调关系，当事件发生时，网卡驱动会回调ep_poll_callback函数，将事件添加到epoll_create创建的链表中。


最后，通过epoll_wait函数，检查并返回链表中是否有事件。该函数是阻塞函数，阻塞时间为timeout，当双向链表有事件或者超时的时候就会返回链表长度(发生事件的数量)。
## 2.2、epoll相关函数的参数

* （1）epoll_create函数的参数size表示该红黑树的大致数量，实际上很多操作系统没有使用这个参数。
* （2）epoll_ctl函数的参数为epfd，op，fd和event。


![][2]
* （3）epoll_wait函数的参数为epfd，events，maxevents和timeout

![][3]
## 三、事件模块的初始化

众所周知，nginx是master/worker框架，在nginx启动时是一个进程，在启动的过程中master会fork出了多个子进程作为worker。master主要是管理worker，本身并不处理请求。而worker负责处理请求。因此，事件模块的初始化也是分成两部分。一部分发生在fork出worker前，主要是配置文件解析等操作，另外一部分发生在fork之后，主要是向epoll中添加监听事件。
## 3.1 启动进程对事件模块的初始化

启动进程对事件模块的初始化分为配置文件解析、开始监听端口和ngx_event_core_module模块的初始化。这三个步骤均在ngx_init_cycle函数进行。

调用关系：main() ---> ngx_init_cycle()

下图是ngx_init_cycle函数的流程，红框是本节将要介绍的三部分内容。

![][4]
## 3.1.1 配置文件解析

启动进程的一个主要工作是解析配置文件。在nginx中，用户主要通过nginx配置文件nginx.conf的event块来控制和调节事件模块的参数。下面是一个event块配置的示例：

```c
user  nobody;
worker_processes  1;
error_log  logs/error.log;
pid        logs/nginx.pid;
......
 
events {
    use epoll;
    worker_connections  1024;
    accept_mutex on；
}
 
http {
    ......
}
```

首先我们先看看nginx是如何解析event块，并将event块存储在什么地方。

在nginx中，解析配置文件的工作是调用ngx_init_cycle函数完成的。下图是该函数在解析配置文件部分的一个流程：

![][5]

* （1）ngx_init_cycle函数首先会进行一些初始化工作，包括更新时间，创建内存池和创建并更新ngx_cycle_t结构体cycle；
* （2）调用各个core模块的create_conf方法，可以创建cycle的conf_ctx数组，该阶段完成后cycle->conf_ctx如下图所示：


![][6]

* （3）初始化ngx_conf_t类型的结构体conf，将cycle->conf_ctx结构体赋值给conf的ctx字段
* （4）解析配置文件


解析配置文件会调用ngx_conf_parse函数，该函数会解析一行命令，当遇到块时会递归调用自身。解析的方法也很简单，就是读取一个命令，然后在所有模块的cmd数组中寻找该命令，若找到则调用该命令的cmd->set()，完成参数的解析。下面介绍event块的解析。

event命令是在event/ngx_event.c文件中定义的，代码如下。

```c
static ngx_command_t  ngx_events_commands[] = {
 
    { ngx_string("events"),
      NGX_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
      ngx_events_block,
      0,
      0,
      NULL },
 
      ngx_null_command
};
```

在从配置文件中读取到event后，会调用ngx_events_block函数。下面是ngx_events_block函数的主要工作：

![][7]

解析完配置文件中的event块后，cycle->conf_ctx如下图所示：

![][8]
* （5）解析完整个配置文件后，调用各个core类型模块的init_conf方法。ngx_event_module的ctx的init_conf方法为ngx_event_init_conf。该方法并没有实际的用途，暂不详述。

## 3.1.2 监听socket

虽然监听socket和事件模块并没有太多的关系，但是为了使得整个流程完整，此处会简单介绍一下启动进程是如何监听端口的。

![][9]

该过程首先检查old_cycle，如果old_cycle中有和cycle中相同的socket，就直接把old_cycle中的fd赋值给cycle。之后会调用ngx_open_listening_socket函数，监听端口。

下面是ngx_open_listening_sockets函数，该函数的作用是遍历所有需要监听的端口，然后调用socket()，bind()和listen()函数，该函数会重试5次。

```c
ngx_int_t
ngx_open_listening_sockets(ngx_cycle_t *cycle)
{
    ......
 
    /* 重试5次 */
    for (tries = 5; tries; tries--) {
        failed = 0;
 
        /* 遍历需要监听的端口 */
        ls = cycle->listening.elts;
        for (i = 0; i < cycle->listening.nelts; i++) {
            ......
 
            /* ngx_socket函数就是socket函数 */
            s = ngx_socket(ls[i].sockaddr->sa_family, ls[i].type, 0);
 
            ......
 
            /* 设置socket属性 */
            if (setsockopt(s, SOL_SOCKET, SO_REUSEADDR,
                           (const void *) &reuseaddr, sizeof(int))
                == -1)
            {
                ......
            }
 
            ......
 
            /* IOCP事件操作 */
            if (!(ngx_event_flags & NGX_USE_IOCP_EVENT)) {
                if (ngx_nonblocking(s) == -1) {
                    ......
                }
            }
 
            ......
 
            /* 绑定socket和地址 */
            if (bind(s, ls[i].sockaddr, ls[i].socklen) == -1) {
               ......
            }
 
            ......
 
            /* 开始监听 */
            if (listen(s, ls[i].backlog) == -1) {
                ......
            }
 
            ls[i].listen = 1;
 
            ls[i].fd = s;
        }
 
        ......
 
        /* 两次重试间隔500ms */
        ngx_msleep(500);
    }
 
    ......
 
    return NGX_OK;
}
```
## 3.1.3 ngx_event_core_module模块的初始化

在ngx_init_cycle函数监听完端口，并提交新的cycle后，便会调用ngx_init_modules函数，该方法会遍历所有模块并调用其init_module方法。对于该阶段，和事件驱动模块有关系的只有ngx_event_core_module的ngx_event_module_init方法。该方法主要做了下面三个工作：

* （1）获取core模块配置结构体中的时间精度timer_resolution，用在epoll里更新缓存时间
* （2）调用getrlimit方法，检查连接数是否超过系统的资源限制
* （3）利用 mmap 分配一块共享内存，存储负载均衡锁（ngx_accept_mutex）、连接计数器（ngx_connection_counter）


## 3.2 worker进程对事件模块的初始化

启动进程在完成一系列操作后，会fork出master进程，并自我关闭，让master进程继续完成初始化工作。master进程会在ngx_spawn_process函数中fork出worker进程，并让worker进程调用ngx_worker_process_cycle函数。ngx_worker_process_cycle函数是worker进程的主循环函数，该函数首先会调用ngx_worker_process_init函数完成worker的初始化，然后就会进入到一个循环中，持续监听处理请求。

![][10]

事件模块的初始化就发生在ngx_worker_process_init函数中。

其调用关系：main() ---> ngx_master_process_cycle() ---> ngx_start_worker_processes() ---> ngx_spawn_process() ---> ngx_worker_process_cycle() ---> ngx_worker_process_init()。

对于ngx_worker_process_init函数，会调用各个模块的init_process方法：

```c
static void
ngx_worker_process_init(ngx_cycle_t *cycle, ngx_int_t worker)
{
     
    ......
 
    for (i = 0; cycle->modules[i]; i++) {
        if (cycle->modules[i]->init_process) {
            if (cycle->modules[i]->init_process(cycle) == NGX_ERROR) {
                /* fatal */
                exit(2);
            }
        }
    }
 
    ......
}
```

在此处，会调用ngx_event_core_module的ngx_event_process_init函数。该函数较为关键，将会重点解析。在介绍ngx_event_process_init函数前，先介绍两个终于的结构体，由于这两个结构体较为复杂，故只介绍部分字段：
* （1）ngx_event_s结构体。nginx中，事件会使用ngx_event_s结构体来表示。

```c
ngx_event_s
struct ngx_event_s {
    /* 通常指向ngx_connection_t结构体 */
    void            *data;
 
    /* 事件可写 */   
    unsigned         write:1;
 
    /* 事件可建立新连接 */
    unsigned         accept:1;
 
    /* 检测事件是否过期 */
    unsigned         instance:1;
 
    /* 通常将事件加入到epoll中会将该字段置为1 */
    unsigned         active:1;
 
    ......
 
    /* 事件超时 */
    unsigned         timedout:1;
 
    /* 事件是否在定时器中 */
    unsigned         timer_set:1;
 
    ......
 
    /* 事件是否在延迟处理队列中 */
    unsigned         posted:1;
 
    ......
 
    /* 事件的处理函数 */
    ngx_event_handler_pt  handler;
 
    ......
 
    /* 定时器红黑树节点 */
    ngx_rbtree_node_t   timer;
 
    /* 延迟处理队列节点 */
    ngx_queue_t      queue;
 
    ......
};
```

（2）ngx_connection_s结构体代表一个nginx连接

```c
struct ngx_connection_s {
    /* 若该结构体未使用，则指向下一个为使用的ngx_connection_s，若已使用，则指向ngx_http_request_t */
    void               *data;
 
    /* 指向一个读事件结构体，这个读事件结构体表示该连接的读事件 */
    ngx_event_t        *read;
 
    /* 指向一个写事件结构体，这个写事件结构体表示该连接的写事件 */
    ngx_event_t        *write;
 
    /* 连接的套接字 */
    ngx_socket_t        fd;
 
    ......
 
    /* 该连接对应的监听端口，表示是由该端口建立的连接 */
    ngx_listening_t    *listening;
 
    ......
};
```

下面介绍ngx_event_process_init函数的实现，代码如下：

```c
/* 此方法在worker进程初始化时调用 */
static ngx_int_t
ngx_event_process_init(ngx_cycle_t *cycle)
{
    ......
 
    /* 打开accept_mutex负载均衡锁，用于防止惊群 */
    if (ccf->master && ccf->worker_processes > 1 && ecf->accept_mutex) {
        ngx_use_accept_mutex = 1;
        ngx_accept_mutex_held = 0;
        ngx_accept_mutex_delay = ecf->accept_mutex_delay;
 
    } else {
        ngx_use_accept_mutex = 0;
    }
 
    /* 初始化两个队列，一个用于存放不能及时处理的建立连接事件，一个用于存储不能及时处理的读写事件 */
    ngx_queue_init(&ngx_posted_accept_events);
    ngx_queue_init(&ngx_posted_events);
 
    /* 初始化定时器 */
    if (ngx_event_timer_init(cycle->log) == NGX_ERROR) {
        return NGX_ERROR;
    }
 
    /**
      * 调用使用的ngx_epoll_module的ctx的actions的init方法，即ngx_epoll_init函数
      * 该函数主要的作用是调用epoll_create()和创建用于epoll_wait()返回事件链表的event_list
      **/
    for (m = 0; cycle->modules[m]; m++) {
        ......
 
        if (module->actions.init(cycle, ngx_timer_resolution) != NGX_OK) {
            exit(2);
        }
 
        break;
    }
 
    /* 如果在配置中设置了timer_resolution，则要设置控制时间精度。通过setitimer方法会设置一个定时器，每隔timer_resolution的时间会发送一个SIGALRM信号 */
    if (ngx_timer_resolution && !(ngx_event_flags & NGX_USE_TIMER_EVENT)) {
        ......
        sa.sa_handler = ngx_timer_signal_handler;
        sigemptyset(&sa.sa_mask);
 
        if (sigaction(SIGALRM, &sa, NULL) == -1) {
            ......
        }
 
        itv.it_interval.tv_sec = ngx_timer_resolution / 1000;
        ......
 
        if (setitimer(ITIMER_REAL, &itv, NULL) == -1) {
            ......
        }
    }
 
    ......
 
    /* 分配连接池空间 */
    cycle->connections =
        ngx_alloc(sizeof(ngx_connection_t) * cycle->connection_n, cycle->log);
    ......
 
    c = cycle->connections;
 
    /* 分配读事件结构体数组空间，并初始化读事件的closed和instance */
    cycle->read_events = ngx_alloc(sizeof(ngx_event_t) * cycle->connection_n,
                                   cycle->log);
    ......
 
    rev = cycle->read_events;
    for (i = 0; i < cycle->connection_n; i++) {
        rev[i].closed = 1;
        rev[i].instance = 1;
    }
 
    /* 分配写事件结构体数组空间，并初始化写事件的closed */
    cycle->write_events = ngx_alloc(sizeof(ngx_event_t) * cycle->connection_n,
                                    cycle->log);
    ......
 
    wev = cycle->write_events;
    for (i = 0; i < cycle->connection_n; i++) {
        wev[i].closed = 1;
    }
 
    /* 将序号为i的读事件结构体和写事件结构体赋值给序号为i的connections结构体的元素 */
    i = cycle->connection_n;
    next = NULL;
 
    do {
        i--;
         
        /* 将connection的data字段设置为下一个connection */
        c[i].data = next;
        c[i].read = &cycle->read_events[i];
        c[i].write = &cycle->write_events[i];
        c[i].fd = (ngx_socket_t) -1;
 
        next = &c[i];
    } while (i);
 
    /* 初始化cycle->free_connections */
    cycle->free_connections = next;
    cycle->free_connection_n = cycle->connection_n;
     
    /* 为每个监听端口分配连接 */
    ls = cycle->listening.elts;
    for (i = 0; i < cycle->listening.nelts; i++) {
 
        ......
 
        c = ngx_get_connection(ls[i].fd, cycle->log);
 
        ......
 
        rev = c->read;
 
        ......
 
        /* 为监听的端口的connection结构体的read事件设置回调函数 */
        rev->handler = (c->type == SOCK_STREAM) ? ngx_event_accept
                                                : ngx_event_recvmsg;
                                  
        /* 将监听的connection的read事件添加到事件驱动模块（epoll） */
        ......
        if (ngx_add_event(rev, NGX_READ_EVENT, 0) == NGX_ERROR) {
            return NGX_ERROR;
        }
 
    }
 
    return NGX_OK;
}
```

该方法主要做了下面几件事：

* (1）打开accept_mutex负载均衡锁，用于防止惊群。惊群是指当多个worker都处于等待事件状态，如果突然来了一个请求，就会同时唤醒多个worker，但是只有一个worker会处理该请求，这就造成系统资源浪费。为了解决这个问题，nginx使用了accept_mutex负载均衡锁。各个worker首先会抢锁，抢到锁的worker才会监听各个端口。
* （2）初始化两个队列，一个用于存放不能及时处理的建立连接事件，一个用于存储不能及时处理的读写事件。
* （3）初始化定时器，该定时器就是一颗红黑树，根据时间对事件进行排序。
* （4）调用使用的ngx_epoll_module的ctx的actions的init方法，即ngx_epoll_init函数。该函数较为简单，主要的作用是调用epoll_create()和创建用于存储epoll_wait()返回事件的链表event_list。
* （5）如果再配置中设置了timer_resolution，则要设置控制时间精度，用于控制nginx时间。这部分在第五部分重点讲解。
* （6）分配连接池空间、读事件结构体数组、写事件结构体数组。


上文介绍了ngx_connection_s和ngx_event_s结构体，我们了解到每一个ngx_connection_s结构体都有两个ngx_event_s结构体，一个读事件，一个写事件。在这个阶段，会向内存池中申请三个数组：cycle->connections、cycle->read_events和cycle->write_events，并将序号为i的读事件结构体和写事件结构体赋值给序号为i的connections结构体的元素。并将cycle->free_connections指向第一个未使用的ngx_connections结构体。

![][11]
* （7）为每个监听端口分配连接

在此阶段，会获取cycle->listening数组中的ngx_listening_s结构体元素。在3.1.2小节中，我们已经讲了nginx启动进程会监听端口，并将socket连接的fd存储在cycle->listening数组中。在这里，会获取到3.1.2小节中监听的端口，并为每个监听分配连接结构体。
* （8）为每个监听端口的连接的读事件设置handler

在为cycle->listening的元素分配完ngx_connection_s类型的连接后，会为连接的读事件设置回调方法handler。这里handler为ngx_event_accept函数，对于该函数，将在后文讲解。
* （9）将每个监听端口的连接的读事件添加到epoll中

在此处，会调用ngx_epoll_module的ngx_epoll_add_event函数，将监听端口的连接的读事件(ls[i].connection->read)添加到epoll中。ngx_epoll_add_event函数的流程如下：

![][12]

在向epoll中添加事件前，需要判断之前是否添加过该连接的事件。

至此，ngx_event_process_init的工作完成，事件模块的初始化也完成了。后面worker开始进入循环监听阶段。
## 四、事件处理
## 4.1 worker的主循环函数ngx_worker_process_cycle

worker在初始化完成之后，开始循环监听端口，并处理请求。下面开始我们开始讲解worker是如何处理事件的。worker的循环代码如下：

```c
static void
ngx_worker_process_cycle(ngx_cycle_t *cycle, void *data)
{
    ngx_int_t worker = (intptr_t) data;
 
    ngx_process = NGX_PROCESS_WORKER;
    ngx_worker = worker;
 
    /* 初始化worker */
    ngx_worker_process_init(cycle, worker);
 
    ngx_setproctitle("worker process");
 
    for ( ;; ) {
 
        if (ngx_exiting) {
            ......
        }
 
        ngx_log_debug0(NGX_LOG_DEBUG_EVENT, cycle->log, 0, "worker cycle");
 
        /* 处理IO事件和时间事件 */
        ngx_process_events_and_timers(cycle);
 
        if (ngx_terminate) {
            ......
        }
 
        if (ngx_quit) {
            ......
        }
 
        if (ngx_reopen) {
            ......
        }
    }
}
```

可以看到，在worker初始化后进入一个for循环，所有的IO事件和时间事件都是在函数ngx_process_events_and_timers中处理的。
## 4.2 worker的事件处理函数ngx_process_events_and_timers

在worker的主循环中，所有的事件都是通过函数ngx_process_events_and_timers处理的，该函数的代码如下：

```c
/* 事件处理函数和定时器处理函数 */
void
ngx_process_events_and_timers(ngx_cycle_t *cycle)
{
    ngx_uint_t  flags;
    ngx_msec_t  timer, delta;
 
    /* timer_resolution模式，设置epoll_wait函数阻塞ngx_timer_resolution的时间 */
    if (ngx_timer_resolution) {
        /* timer_resolution模式 */
        timer = NGX_TIMER_INFINITE;
        flags = 0;
 
    } else {
        /* 非timer_resolution模式，epoll_wait函数等待至下一个定时器事件到来时返回 */
        timer = ngx_event_find_timer();
        flags = NGX_UPDATE_TIME;
    }
 
    /* 是否使用accept_mutex */
    if (ngx_use_accept_mutex) {
        /**
         * 该worker是否负载过高，若负载过高则不抢锁
         * 判断负载过高是判断该worker建立的连接数是否大于该worker可以建立的最大连接数的7/8
         **/
        if (ngx_accept_disabled > 0) {
            ngx_accept_disabled--;
 
        } else {
            /* 抢锁 */
            if (ngx_trylock_accept_mutex(cycle) == NGX_ERROR) {
                return;
            }
 
            if (ngx_accept_mutex_held) {
                /* 抢到锁，则收到事件后暂不处理，先扔到事件队列中 */
                flags |= NGX_POST_EVENTS;
 
            } else {
                /* 未抢到锁，要修改worker在epoll_wait函数等待的时间，使其不要过大 */
                if (timer == NGX_TIMER_INFINITE
                    || timer > ngx_accept_mutex_delay)
                {
                    timer = ngx_accept_mutex_delay;
                }
            }
        }
    }
 
    /* delta用于计算ngx_process_events的耗时 */
    delta = ngx_current_msec;
 
    /* 事件处理函数，epoll使用的是ngx_epoll_process_events函数 */
    (void) ngx_process_events(cycle, timer, flags);
 
    delta = ngx_current_msec - delta;
 
    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                   "timer delta: %M", delta);
 
    /* 处理ngx_posted_accept_events队列的连接事件 */
    ngx_event_process_posted(cycle, &ngx_posted_accept_events);
 
    /* 若持有accept_mutex，则释放锁 */
    if (ngx_accept_mutex_held) {
        ngx_shmtx_unlock(&ngx_accept_mutex);
    }
 
    /* 若事件处理函数的执行时间不为0，则要处理定时器事件 */
    if (delta) {
        ngx_event_expire_timers();
    }
 
    /* 处理ngx_posted_events队列的读写事件 */
    ngx_event_process_posted(cycle, &ngx_posted_events);
}
```

ngx_process_events_and_timers函数是nginx处理事件的核心函数，主要的工作可以分为下面几部分：
* （1）设置nginx更新时间的方式。

nginx会将时间存储在内存中，每隔一段时间调用ngx_time_update函数更新时间。那么多久更新一次呢？nginx提供两种方式：

* 方式一：timer_resolution模式。在nginx配置文件中，可以使用timer_resolution之类来选择此方式。如果使用此方式，会将epoll_wait的阻塞时间设置为无穷大，即一直阻塞。那么如果nginx一直都没有收到事件，会一直阻塞吗？答案是不会的。在本文3.2节中讲解的ngx_event_process_init函数(第5步)将会设置一个时间定时器和一个信号处理函数，其中时间定时器会每隔timer_resolution的时间发送一个SIGALRM信号，而当worker收到时间定时器发送的信号，会将epoll_wait函数终端，同时调用SIGALRM信号的中断处理函数，将全局变量ngx_event_timer_alarm置为1。后面会检查该变量，调用ngx_time_update函数来更新nginx的时间。
* 方式二：如果不在配置文件中设置timer_resolution，nginx默认会使用方式二来更新nginx的时间。首先会调用ngx_event_find_timer函数来设置epoll_wait的阻塞时间，ngx_event_find_timer函数返回的是下一个时间事件发生的时间与当前时间的差值，即让epoll_wait阻塞到下一个时间事件发生为止。当使用这种模式，每当epoll_wait返回，都会调用ngx_time_update函数更新时间。
* （2）使用负载均衡锁ngx_use_accept_mutex。


上文曾经提过一个问题，当多个worker都处于等待事件状态，如果突然来了一个请求，就会同时唤醒多个worker，但是只有一个worker会处理该请求，这就造成系统资源浪费。nginx如果解决这个问题呢？答案就是使用一个锁来解决。在监听事件前，各个worker会进行一次抢锁行为，只有抢到锁的worker才会监听端口，而其他worker值处理已经建立连接的事件。

首先函数会通过ngx_accept_disabled是否大于0来判断是否过载，过载的worker是不允许抢锁的。ngx_accept_disabled的计算方式如下。

```c
/**
 * ngx_cycle->connection_n是每个进程最大连接数，也是连接池的总连接数，ngx_cycle->free_connection_n是连接池中未使用的连接数量。
 * 当未使用的数量小于总数量的1/8时，会使ngx_accept_disabled大于0。这时认为该worker过载。
 **/
ngx_accept_disabled = ngx_cycle->connection_n / 8 - ngx_cycle->free_connection_n;
```

若ngx_accept_disabled小于0，worker可以抢锁。这时会通过ngx_trylock_accept_mutex函数抢锁。该函数的流程如下图所示：

![][13]

在抢锁结束后，若worker抢到锁，设置该worker的flag为NGX_POST_EVENTS，表示抢到锁的这个worker在收到事件后并不会立即调用事件的处理函数，而是会把事件放到一个队列里，后期处理。
* （3）调用事件处理函数ngx_process_events，epoll使用的是ngx_epoll_process_events函数。此代码较为重要，下面是代码：

```c
static ngx_int_t
ngx_epoll_process_events(ngx_cycle_t *cycle, ngx_msec_t timer, ngx_uint_t flags)
{
    int                events;
     
    uint32_t           revents;
    ngx_int_t          instance, i;
    ngx_uint_t         level;
    ngx_err_t          err;
    ngx_event_t       *rev, *wev;
    ngx_queue_t       *queue;
    ngx_connection_t  *c;
 
    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                   "epoll timer: %M", timer);
 
    /* 调用epoll_wait，从epoll中获取发生的事件 */
    events = epoll_wait(ep, event_list, (int) nevents, timer);
 
    err = (events == -1) ? ngx_errno : 0;
 
    /* 两种方式更新nginx时间，timer_resolution模式ngx_event_timer_alarm为1，非timer_resolution模式flags & NGX_UPDATE_TIME不为0，均会进入if条件 */
    if (flags & NGX_UPDATE_TIME || ngx_event_timer_alarm) {
        ngx_time_update();
    }
 
    /* 处理epoll_wait返回为-1的情况 */
    if (err) {
 
        /**
         * 对于timer_resolution模式，如果worker接收到SIGALRM信号，会调用该信号的处理函数，将ngx_event_timer_alarm置为1，从而更新时间。
         * 同时如果在epoll_wait阻塞的过程中接收到SIGALRM信号，会中断epoll_wait，使其返回NGX_EINTR。由于上一步已经更新了时间，这里要把ngx_event_timer_alarm置为0。
         **/
        if (err == NGX_EINTR) {
 
            if (ngx_event_timer_alarm) {
                ngx_event_timer_alarm = 0;
                return NGX_OK;
            }
 
            level = NGX_LOG_INFO;
 
        } else {
            level = NGX_LOG_ALERT;
        }
 
        ngx_log_error(level, cycle->log, err, "epoll_wait() failed");
        return NGX_ERROR;
    }
 
    /* 若events返回为0，判断是因为epoll_wait超时还是其他原因 */
    if (events == 0) {
        if (timer != NGX_TIMER_INFINITE) {
            return NGX_OK;
        }
 
        ngx_log_error(NGX_LOG_ALERT, cycle->log, 0,
                      "epoll_wait() returned no events without timeout");
        return NGX_ERROR;
    }
 
    /* 对epoll_wait返回的链表进行遍历 */
    for (i = 0; i < events; i++) {
        c = event_list[i].data.ptr;
 
        /* 从data中获取connection & instance的值，并解析出instance和connection */
        instance = (uintptr_t) c & 1;
        c = (ngx_connection_t *) ((uintptr_t) c & (uintptr_t) ~1);
 
        /* 取出connection的read事件 */
        rev = c->read;
 
        /* 判断读事件是否过期 */
        if (c->fd == -1 || rev->instance != instance) {
            ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                           "epoll: stale event %p", c);
            continue;
        }
 
        /* 取出事件的类型 */
        revents = event_list[i].events;
 
        ngx_log_debug3(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                       "epoll: fd:%d ev:%04XD d:%p",
                       c->fd, revents, event_list[i].data.ptr);
 
        /* 若连接发生错误，则将EPOLLIN、EPOLLOUT添加到revents中，在调用读写事件时能够处理连接的错误 */
        if (revents & (EPOLLERR|EPOLLHUP)) {
            ngx_log_debug2(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                           "epoll_wait() error on fd:%d ev:%04XD",
                           c->fd, revents);
 
            revents |= EPOLLIN|EPOLLOUT;
        }
 
        /* 事件为读事件且读事件在epoll中 */
        if ((revents & EPOLLIN) && rev->active) {
 
#if (NGX_HAVE_EPOLLRDHUP)
            if (revents & EPOLLRDHUP) {
                rev->pending_eof = 1;
            }
 
            rev->available = 1;
#endif
 
            rev->ready = 1;
 
            /* 事件是否需要延迟处理？对于抢到锁监听端口的worker，会将事件延迟处理 */
            if (flags & NGX_POST_EVENTS) {
                /* 根据事件的是否是accept事件，加到不同的队列中 */
                queue = rev->accept ? &ngx_posted_accept_events
                                    : &ngx_posted_events;
 
                ngx_post_event(rev, queue);
 
            } else {
                /* 若不需要延迟处理，直接调用read事件的handler */
                rev->handler(rev);
            }
        }
 
        /* 取出connection的write事件 */
        wev = c->write;
 
        /* 事件为写事件且写事件在epoll中 */
        if ((revents & EPOLLOUT) && wev->active) {
 
            /* 判断写事件是否过期 */
            if (c->fd == -1 || wev->instance != instance) {
                ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                               "epoll: stale event %p", c);
                continue;
            }
 
            wev->ready = 1;
#if (NGX_THREADS)
            wev->complete = 1;
#endif
 
            /* 事件是否需要延迟处理？对于抢到锁监听端口的worker，会将事件延迟处理 */
            if (flags & NGX_POST_EVENTS) {
                ngx_post_event(wev, &ngx_posted_events);
 
            } else {
                /* 若不需要延迟处理，直接调用write事件的handler */
                wev->handler(wev);
            }
        }
    }
 
    return NGX_OK;
}
```

该函数的流程图如下：

![][14]

* （4）计算ngx_process_events函数的调用时间。
* （5）处理ngx_posted_accept_events队列的连接事件。这里就是遍历ngx_posted_accept_events队列，调用事件的handler方法，这里accept事件的handler为ngx_event_accept。
* （6）释放负载均衡锁。
* （7）处理定时器事件，具体操作是在定时器红黑树中查找过期的事件，调用其handler方法。
* （8）处理ngx_posted_events队列的读写事件，即遍历ngx_posted_events队列，调用事件的handler方法。


## 结束

至此，我们介绍完了nginx事件模块的事件处理函数ngx_process_events_and_timers。nginx事件模块的相关知识也初步介绍完了。

[15]: https://titenwang.github.io/2017/10/05/implementation-of-epoll/
[0]: ./img/bVbiTep.png
[1]: ./img/bVbiTet.png
[2]: ./img/bVbiTeO.png
[3]: ./img/bVbiTeR.png
[4]: ./img/bVbiTe1.png
[5]: ./img/bVbiTe7.png
[6]: ./img/bVbiTe8.png
[7]: ./img/bVbiTff.png
[8]: ./img/bVbiTfm.png
[9]: ./img/bVbiTfs.png
[10]: ./img/bVbiTfC.png
[11]: ./img/bVbiTfU.png
[12]: ./img/bVbiTfY.png
[13]: ./img/bVbiTgw.png
[14]: ./img/bVbiTgz.png
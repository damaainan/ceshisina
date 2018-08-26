### 概述

       Nginx 是以事件的触发来驱动的，事件驱动模型主要包括事件收集、事件发送、事件处理（即事件管理）三部分。在Nginx 的工作进程中主要关注的事件是IO 网络事件 和 定时器事件。在生成的 objs 目录文件中，其中ngx_modules.c 文件的内容是Nginx 各种模块的执行顺序，我们可以从该文件的内容中看到事件模块的执行顺序为以下所示：注意：由于是在Linux 系统下，所以支持具体的 epoll 事件模块，接下来的文章结构按照以下顺序来写。

```c
extern ngx_module_t  ngx_events_module;
extern ngx_module_t  ngx_event_core_module;
extern ngx_module_t  ngx_epoll_module;

```

### 事件模块接口

### ngx_event_module_t 结构体

       在 Nginx 中，结构体 ngx_module_t 是 Nginx 模块最基本的接口。对于每一种不同类型的模块，都有一个具体的结构体来描述这一类模块的通用接口，该接口由ngx_module_t 中的成员ctx 管理。在 Nginx 中定义了事件模块的通用接口ngx_event_module_t 结构体，该结构体定义在文件[src/event/ngx_event.h](http://lxr.nginx.org/source/src/event/ngx_event.h) 中：

```c
/* 事件驱动模型通用接口ngx_event_module_t结构体 */
typedef struct {
    /* 事件模块名称 */
    ngx_str_t              *name;

    /* 解析配置项前调用，创建存储配置项参数的结构体 */
    void                 *(*create_conf)(ngx_cycle_t *cycle);
    /* 完成配置项解析后调用，处理当前事件模块感兴趣的全部配置 */
    char                 *(*init_conf)(ngx_cycle_t *cycle, void *conf);

    /* 每个事件模块具体实现的方法，有10个方法，即IO多路复用模型的统一接口 */
    ngx_event_actions_t     actions;
} ngx_event_module_t;

```

       在 ngx_event_module_t 结构体中actions 的类型是ngx_event_actions_t 结构体，该成员结构实现了事件驱动模块的具体方法。该结构体定义在文件[src/event/ngx_event.h](http://lxr.nginx.org/source/src/event/ngx_event.h) 中：

```c
/* IO多路复用模型的统一接口 */
typedef struct {
    /* 添加事件，将某个描述符的某个事件添加到事件驱动机制监控描述符集中 */
    ngx_int_t  (*add)(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags);
    /* 删除事件，将某个描述符的某个事件从事件驱动机制监控描述符集中删除 */
    ngx_int_t  (*del)(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags);

    /* 启动对某个指定事件的监控 */
    ngx_int_t  (*enable)(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags);
    /* 禁用对某个指定事件的监控 */
    ngx_int_t  (*disable)(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags);

    /* 将指定连接所关联的描述符添加到事件驱动机制监控中 */
    ngx_int_t  (*add_conn)(ngx_connection_t *c);
    /* 将指定连接所关联的描述符从事件驱动机制监控中删除 */
    ngx_int_t  (*del_conn)(ngx_connection_t *c, ngx_uint_t flags);

    /* 监控事件是否发生变化，仅用在多线程环境中 */
    ngx_int_t  (*process_changes)(ngx_cycle_t *cycle, ngx_uint_t nowait);
    /* 等待事件的发生，并对事件进行处理 */
    ngx_int_t  (*process_events)(ngx_cycle_t *cycle, ngx_msec_t timer,
                   ngx_uint_t flags);

    /* 初始化事件驱动模块 */
    ngx_int_t  (*init)(ngx_cycle_t *cycle, ngx_msec_t timer);
    /* 在退出事件驱动模块前调用该函数回收资源 */
    void       (*done)(ngx_cycle_t *cycle);
} ngx_event_actions_t;

```

### ngx_event_t 结构体

       在 Nginx 中，每一个具体事件的定义由结构体ngx_event_t 来表示，该结构体ngx_event_t 用来保存具体事件。该结构体定义在文件 [src/event/ngx_event.h](http://lxr.nginx.org/source/src/event/ngx_event.h) 中： 

```c
/* 描述每一个事件的ngx_event_t结构体 */
struct ngx_event_s {
    /* 事件相关对象的数据，通常指向ngx_connect_t连接对象 */
    void            *data;

    /* 标志位，为1表示事件可写，即当前对应的TCP连接状态可写 */
    unsigned         write:1;

    /* 标志位，为1表示事件可以建立新连接 */
    unsigned         accept:1;

    /* used to detect the stale events in kqueue, rtsig, and epoll */
    unsigned         instance:1;

    /*
     * the event was passed or would be passed to a kernel;
     * in aio mode - operation was posted.
     */
    /* 标志位，为1表示事件处于活跃状态 */
    unsigned         active:1;

    /* 标志位，为1表示禁用事件 */
    unsigned         disabled:1;

    /* the ready event; in aio mode 0 means that no operation can be posted */
    /* 标志位，为1表示当前事件已经准备就绪 */
    unsigned         ready:1;

    /* 该标志位只用于kqueue,eventport模块，对Linux上的驱动模块没有任何意义 */
    unsigned         oneshot:1;

    /* aio operation is complete */
    /* 该标志位用于异步AIO事件处理 */
    unsigned         complete:1;

    /* 标志位，为1表示当前处理的字符流已经结束 */
    unsigned         eof:1;
    /* 标志位，为1表示当前事件处理过程中出错 */
    unsigned         error:1;

    /* 标志位，为1表示当前事件已超时 */
    unsigned         timedout:1;
    /* 标志位，为1表示当前事件存在于定时器中 */
    unsigned         timer_set:1;

    /* 标志位，为1表示当前事件需要延迟处理 */
    unsigned         delayed:1;

    /*
     * 标志位，为1表示TCP建立需要延迟，即完成建立TCP连接的三次握手后，
     * 不会立即建立TCP连接，直到接收到数据包才建立TCP连接；
     */
    unsigned         deferred_accept:1;

    /* the pending eof reported by kqueue, epoll or in aio chain operation */
    /* 标志位，为1表示等待字符流结束 */
    unsigned         pending_eof:1;

    /* 标志位，为1表示处理post事件 */
    unsigned         posted:1;

#if (NGX_WIN32)
    /* setsockopt(SO_UPDATE_ACCEPT_CONTEXT) was successful */
    unsigned         accept_context_updated:1;
#endif

#if (NGX_HAVE_KQUEUE)
    unsigned         kq_vnode:1;

    /* the pending errno reported by kqueue */
    int              kq_errno;
#endif

    /*
     * kqueue only:
     *   accept:     number of sockets that wait to be accepted
     *   read:       bytes to read when event is ready
     *               or lowat when event is set with NGX_LOWAT_EVENT flag
     *   write:      available space in buffer when event is ready
     *               or lowat when event is set with NGX_LOWAT_EVENT flag
     *
     * iocp: TODO
     *
     * otherwise:
     *   accept:     1 if accept many, 0 otherwise
     */

#if (NGX_HAVE_KQUEUE) || (NGX_HAVE_IOCP)
    int              available;
#else
    /* 标志位，在epoll事件机制中表示一次尽可能多地建立TCP连接 */
    unsigned         available:1;
#endif

    /* 当前事件发生时的处理方法 */
    ngx_event_handler_pt  handler;

#if (NGX_HAVE_AIO)

#if (NGX_HAVE_IOCP)
    ngx_event_ovlp_t ovlp;
#else
    /* Linux系统aio机制中定义的结构体 */
    struct aiocb     aiocb;
#endif

#endif

    /* epoll机制不使用该变量 */
    ngx_uint_t       index;

    /* 日志记录 */
    ngx_log_t       *log;

    /* 定时器 */
    ngx_rbtree_node_t   timer;

    /* the posted queue */
    ngx_queue_t      queue;

    /* 标志位，为1表示当前事件已经关闭 */
    unsigned         closed:1;

    /* to test on worker exit */
    unsigned         channel:1;
    unsigned         resolver:1;

    unsigned         cancelable:1;

#if 0

    /* the threads support */

    /*
     * the event thread context, we store it here
     * if $(CC) does not understand __thread declaration
     * and pthread_getspecific() is too costly
     */

    void            *thr_ctx;

#if (NGX_EVENT_T_PADDING)

    /* event should not cross cache line in SMP */

    uint32_t         padding[NGX_EVENT_T_PADDING];
#endif
#endif
};

```

       在每个事件结构体 ngx_event_t 最重要的成员是handler 回调函数，该回调函数定义了当事件发生时的处理方法。该回调方法原型在文件[src/core/ngx_core.h](http://lxr.nginx.org/source/src/core/ngx_core.h) 中：

```c
typedef void (*ngx_event_handler_pt)(ngx_event_t *ev);

```

### ngx_connection_t 结构体

       当客户端向 Nginx 服务器发起连接请求时，此时若Nginx 服务器被动接收该连接，则相对Nginx 服务器来说称为被动连接，被动连接的表示由基本数据结构体ngx_connection_t 完成。该结构体定义在文件 [src/core/ngx_connection.h](http://lxr.nginx.org/source/src/core/ngx_connection.h) 中：

```c
/* TCP连接结构体 */
struct ngx_connection_s {
    /*
     * 当Nginx服务器产生新的socket时，
     * 都会创建一个ngx_connection_s 结构体，
     * 该结构体用于保存socket的属性和数据；
     */

    /*
     * 当连接未被使用时，data充当连接池中空闲连接表中的next指针；
     * 当连接被使用时，data的意义由具体Nginx模块决定；
     */
    void               *data;
    /* 设置该链接的读事件 */
    ngx_event_t        *read;
    /* 设置该连接的写事件 */
    ngx_event_t        *write;

    /* 用于设置socket的套接字描述符 */
    ngx_socket_t        fd;

    /* 接收网络字符流的方法，是一个函数指针，指向接收函数 */
    ngx_recv_pt         recv;
    /* 发送网络字符流的方法，是一个函数指针，指向发送函数 */
    ngx_send_pt         send;
    /* 以ngx_chain_t链表方式接收网络字符流的方法 */
    ngx_recv_chain_pt   recv_chain;
    /* 以ngx_chain_t链表方式发送网络字符流的方法 */
    ngx_send_chain_pt   send_chain;

    /*
     * 当前连接对应的ngx_listening_t监听对象，
     * 当前连接由ngx_listening_t成员的listening监听端口的事件建立；
     * 成员connection指向当前连接；
     */
    ngx_listening_t    *listening;

    /* 当前连接已发生的字节数 */
    off_t               sent;

    /* 记录日志 */
    ngx_log_t          *log;

    /* 内存池 */
    ngx_pool_t         *pool;

    /* 对端的socket地址sockaddr属性*/
    struct sockaddr    *sockaddr;
    socklen_t           socklen;
    /* 字符串形式的IP地址 */
    ngx_str_t           addr_text;

    ngx_str_t           proxy_protocol_addr;

#if (NGX_SSL)
    ngx_ssl_connection_t  *ssl;
#endif

    /* 本端的监听端口对应的socket的地址sockaddr属性 */
    struct sockaddr    *local_sockaddr;
    socklen_t           local_socklen;

    /* 用于接收、缓存对端发来的字符流 */
    ngx_buf_t          *buffer;

    /*
     * 表示将当前连接作为双向连接中节点元素，
     * 添加到ngx_cycle_t结构体的成员
     * reuseable_connections_queue的双向链表中；
     */
    ngx_queue_t         queue;

    /* 连接使用次数 */
    ngx_atomic_uint_t   number;

    /* 处理请求的次数 */
    ngx_uint_t          requests;

    unsigned            buffered:8;

    unsigned            log_error:3;     /* ngx_connection_log_error_e */

    /* 标志位，为1表示不期待字符流结束 */
    unsigned            unexpected_eof:1;
    /* 标志位，为1表示当前连接已经超时 */
    unsigned            timedout:1;
    /* 标志位，为1表示处理连接过程出错 */
    unsigned            error:1;
    /* 标志位，为1表示当前TCP连接已经销毁 */
    unsigned            destroyed:1;

    /* 标志位，为1表示当前连接处于空闲状态 */
    unsigned            idle:1;
    /* 标志位，为1表示当前连接可重用 */
    unsigned            reusable:1;
    /* 标志为，为1表示当前连接已经关闭 */
    unsigned            close:1;

    /* 标志位，为1表示正在将文件的数据发往对端 */
    unsigned            sendfile:1;
    /*
     * 标志位，若为1，则表示只有连接对应的发送缓冲区满足最低设置的阈值时，
     * 事件驱动模块才会分发事件；
     */
    unsigned            sndlowat:1;
    unsigned            tcp_nodelay:2;   /* ngx_connection_tcp_nodelay_e */
    unsigned            tcp_nopush:2;    /* ngx_connection_tcp_nopush_e */

    unsigned            need_last_buf:1;

#if (NGX_HAVE_IOCP)
    unsigned            accept_context_updated:1;
#endif

#if (NGX_HAVE_AIO_SENDFILE)
    /* 标志位，为1表示使用异步IO方式将磁盘文件发送给网络连接的对端 */
    unsigned            aio_sendfile:1;
    unsigned            busy_count:2;
    /* 使用异步IO发送文件时，用于待发送的文件信息 */
    ngx_buf_t          *busy_sendfile;
#endif

#if (NGX_THREADS)
    ngx_atomic_t        lock;
#endif
};

```

       在处理请求的过程中，若 Nginx 服务器主动向上游服务器建立连接，完成连接建立并与之进行通信，这种相对Nginx 服务器来说是一种主动连接，主动连接由结构体ngx_peer_connection_t 表示，但是该结构体 ngx_peer_connection_t 也是 ngx_connection_t 结构体的封装。该结构体定义在文件[src/event/ngx_event_connect.h](http://lxr.nginx.org/source/src/event/ngx_event_connect.h) 中：

```c
/* 主动连接的结构体 */
struct ngx_peer_connection_s {
    /* 这里是对ngx_connection_t连接结构体的引用 */
    ngx_connection_t                *connection;

    /* 远端服务器的socket的地址sockaddr信息 */
    struct sockaddr                 *sockaddr;
    socklen_t                        socklen;
    /* 远端服务器的名称 */
    ngx_str_t                       *name;

    /* 连接重试的次数 */
    ngx_uint_t                       tries;

    /* 获取连接的方法 */
    ngx_event_get_peer_pt            get;
    /* 释放连接的方法 */
    ngx_event_free_peer_pt           free;
    /* 配合get、free使用 */
    void                            *data;

#if (NGX_SSL)
    ngx_event_set_peer_session_pt    set_session;
    ngx_event_save_peer_session_pt   save_session;
#endif

#if (NGX_THREADS)
    ngx_atomic_t                    *lock;
#endif

    /* 本地地址信息 */
    ngx_addr_t                      *local;

    /* 接收缓冲区 */
    int                              rcvbuf;

    /* 记录日志 */
    ngx_log_t                       *log;

    /* 标志位，为1表示connection连接已经缓存 */
    unsigned                         cached:1;

                                     /* ngx_connection_log_error_e */
    unsigned                         log_error:2;
};

```

### ngx_events_module 核心模块

### ngx_events_module 核心模块的定义

       ngx_events_module 模块是事件的核心模块，该模块的功能是：定义新的事件类型，并为每个事件模块定义通用接口ngx_event_module_t 结构体，管理事件模块生成的配置项结构体，并解析事件类配置项。首先，看下该模块在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中的定义：

```c
/* 定义事件核心模块 */
ngx_module_t  ngx_events_module = {
    NGX_MODULE_V1,
    &amp;ngx_events_module_ctx,                /* module context */
    ngx_events_commands,                   /* module directives */
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

       其中，模块的配置项指令结构 ngx_events_commands 决定了该模块的功能。配置项指令结构ngx_events_commands 在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义如下：

```c
/* 配置项结构体数组 */
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

       从配置项结构体中可以知道，该模块只对 events{...} 配置块感兴趣，并定义了管理事件模块的方法ngx_events_block；ngx_events_block 方法在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义：

```c
/* 管理事件模块 */
static char *
ngx_events_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    char                 *rv;
    void               ***ctx;
    ngx_uint_t            i;
    ngx_conf_t            pcf;
    ngx_event_module_t   *m;

    if (*(void **) conf) {
        return "is duplicate";
    }

    /* count the number of the event modules and set up their indices */

    /* 计算模块类中模块的总数，并初始化模块在模块类中的序号 */
    ngx_event_max_module = 0;
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_EVENT_MODULE) {
            continue;
        }

        ngx_modules[i]->ctx_index = ngx_event_max_module++;
    }

    ctx = ngx_pcalloc(cf->pool, sizeof(void *));
    if (ctx == NULL) {
        return NGX_CONF_ERROR;
    }

    /* 分配指针数组，用于存储所有事件模块生成的配置项结构体指针 */
    *ctx = ngx_pcalloc(cf->pool, ngx_event_max_module * sizeof(void *));
    if (*ctx == NULL) {
        return NGX_CONF_ERROR;
    }

    *(void **) conf = ctx;

    /* 若是事件模块，并且定义了create_conf方法，则调用该方法创建存储配置项参数的结构体 */
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_EVENT_MODULE) {
            continue;
        }

        m = ngx_modules[i]->ctx;

        if (m->create_conf) {
            (*ctx)[ngx_modules[i]->ctx_index] = m->create_conf(cf->cycle);
            if ((*ctx)[ngx_modules[i]->ctx_index] == NULL) {
                return NGX_CONF_ERROR;
            }
        }
    }

    /* 初始化配置项结构体cf */
    pcf = *cf;
    cf->ctx = ctx;/* 描述事件模块的配置项结构 */
    cf->module_type = NGX_EVENT_MODULE;/* 当前解析指令的模块类型 */
    cf->cmd_type = NGX_EVENT_CONF;/* 当前解析指令的指令类型 */

    /* 为所有事件模块解析配置文件nginx.conf中的event{}块中的指令 */
    rv = ngx_conf_parse(cf, NULL);

    *cf = pcf;

    if (rv != NGX_CONF_OK)
        return rv;

    /* 遍历所有事件模块，若定义了init_conf方法，则调用该方法用于处理事件模块感兴趣的配置项 */
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_EVENT_MODULE) {
            continue;
        }

        m = ngx_modules[i]->ctx;

        if (m->init_conf) {
            rv = m->init_conf(cf->cycle, (*ctx)[ngx_modules[i]->ctx_index]);
            if (rv != NGX_CONF_OK) {
                return rv;
            }
        }
    }

    return NGX_CONF_OK;
}

```

       另外，在 ngx_events_module 模块的定义中有一个成员ctx 指向了核心模块的通用接口结构。核心模块的通用接口结构体定义在文件[src/core/ngx_conf_file.h](http://lxr.nginx.org/source/src/core/ngx_conf_file.h) 中：

```c
/* 核心模块的通用接口结构体 */
typedef struct {
    /* 模块名称 */
    ngx_str_t             name;
    /* 解析配置项前，调用该方法 */
    void               *(*create_conf)(ngx_cycle_t *cycle);
    /* 完成配置项解析后，调用该函数 */
    char               *(*init_conf)(ngx_cycle_t *cycle, void *conf);
} ngx_core_module_t;

```

       因此，ngx_events_module 作为核心模块，必须定义核心模块的通用接口结构。ngx_events_module 模块的核心模块通用接口在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义：

```c
/* 实现核心模块通用接口 */
static ngx_core_module_t  ngx_events_module_ctx = {
    ngx_string("events"),
    NULL,
    /*
     * 以前的版本这里是NULL，现在实现了一个获取events配置项的函数，*
     * 但是没有什么作用，因为每个事件模块都会去获取events配置项，
     * 并进行解析与处理；
     */
    ngx_event_init_conf
};

```

### 所有事件模块的配置项管理

       Nginx 服务器在结构体 ngx_cycle_t 中定义了一个四级指针成员 conf_ctx，整个Nginx 模块都是使用该四级指针成员管理模块的配置项结构，以下events 模块为例对该四级指针成员进行简单的分析，如下图所示：

![](./img/2016-09-01_57c7edd0daba6.jpg)
  

       每个事件模块可以通过宏定义 ngx_event_get_conf 获取它在create_conf 中分配的结构体的指针；该宏中定义如下：

```c
#define ngx_event_get_conf(conf_ctx, module) \
(*(ngx_get_conf(conf_ctx, ngx_events_module))) [module.ctx_index];

/* 其中 ngx_get_conf 定义如下 */
#define ngx_get_conf(conf_ctx, module) conf_ctx[module.index]

```

       从上面的宏定义可以知道，每个事件模块获取自己在 create_conf 中分配的结构体的指针，只需在ngx_event_get_conf 传入参数ngx_cycle_t 中的 conf_ctx 成员，并且传入自己模块的名称即可获取自己分配的结构体指针。

### ngx_event_core_module 事件模块

       ngx_event_core_module 模块是一个事件类型的模块，它在所有事件模块中的顺序是第一，是其它事件类模块的基础。它主要完成以下任务：

1. 创建连接池；
1. 决定使用哪些事件驱动机制；
1. 初始化将要使用的事件模块；

### ngx_event_conf_t 结构体

       ngx_event_conf_t 结构体是用来保存ngx_event_core_module 事件模块配置项参数的。该结构体在文件[src/event/ngx_event.h](http://lxr.nginx.org/source/src/event/ngx_event.h) 中定义：

```c
/* 存储ngx_event_core_module事件模块配置项参数的结构体 ngx_event_conf_t */
typedef struct {
    /* 连接池中最大连接数 */
    ngx_uint_t    connections;
    /* 被选用模块在所有事件模块中的序号 */
    ngx_uint_t    use;

    /* 标志位，为1表示可批量建立连接 */
    ngx_flag_t    multi_accept;
    /* 标志位，为1表示打开负载均衡锁 */
    ngx_flag_t    accept_mutex;

    /* 延迟建立连接 */
    ngx_msec_t    accept_mutex_delay;

    /* 被使用事件模块的名称 */
    u_char       *name;

#if (NGX_DEBUG)
    /* 用于保存与输出调试级别日志连接对应客户端的地址信息 */
    ngx_array_t   debug_connection;
#endif
} ngx_event_conf_t;

```

### ngx_event_core_module 事件模块的定义

       该模块在文件 [src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义：

```c
/* 事件模块的定义 */
ngx_module_t  ngx_event_core_module = {
    NGX_MODULE_V1,
    &amp;ngx_event_core_module_ctx,            /* module context */
    ngx_event_core_commands,               /* module directives */
    NGX_EVENT_MODULE,                      /* module type */
    NULL,                                  /* init master */
    ngx_event_module_init,                 /* init module */
    ngx_event_process_init,                /* init process */
    NULL,                                  /* init thread */
    NULL,                                  /* exit thread */
    NULL,                                  /* exit process */
    NULL,                                  /* exit master */
    NGX_MODULE_V1_PADDING
};

```

       其中，模块的配置项指令结构 ngx_event_core_commands 决定了该模块的功能。配置项指令结构 ngx_event_core_commands 在文件 [src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义如下：

```c
static ngx_str_t  event_core_name = ngx_string("event_core");

/* 定义ngx_event_core_module 模块感兴趣的配置项 */
static ngx_command_t  ngx_event_core_commands[] = {

    /* 每个worker进程中TCP最大连接数 */
    { ngx_string("worker_connections"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_event_connections,
      0,
      0,
      NULL },

    /* 与上面的worker_connections配置项相同 */
    { ngx_string("connections"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_event_connections,
      0,
      0,
      NULL },

    /* 选择事件模块作为事件驱动机制 */
    { ngx_string("use"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_event_use,
      0,
      0,
      NULL },

    /* 批量接收连接 */
    { ngx_string("multi_accept"),
      NGX_EVENT_CONF|NGX_CONF_FLAG,
      ngx_conf_set_flag_slot,
      0,
      offsetof(ngx_event_conf_t, multi_accept),
      NULL },

    /* 是否打开accept_mutex负载均衡锁 */
    { ngx_string("accept_mutex"),
      NGX_EVENT_CONF|NGX_CONF_FLAG,
      ngx_conf_set_flag_slot,
      0,
      offsetof(ngx_event_conf_t, accept_mutex),
      NULL },

    /* 打开accept_mutex负载均衡锁后，延迟处理新连接事件 */
    { ngx_string("accept_mutex_delay"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_conf_set_msec_slot,
      0,
      offsetof(ngx_event_conf_t, accept_mutex_delay),
      NULL },

    /* 对指定IP的TCP连接打印debug级别的调试日志 */
    { ngx_string("debug_connection"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_event_debug_connection,
      0,
      0,
      NULL },

      ngx_null_command
};

```

       其中，每个事件模块都需要实现事件模块的通用接口结构 ngx_event_module_t，ngx_event_core_module 模块的上下文结构 ngx_event_core_module_ctx 并不真正的负责网络事件的驱动，所有不会实现ngx_event_module_t 结构体中的成员 actions 中的方法。上下文结构 ngx_event_core_module_ctx 在文件 [src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义如下：

```c
/* 根据事件模块通用接口，实现ngx_event_core_module事件模块的上下文结构 */
ngx_event_module_t  ngx_event_core_module_ctx = {
    &amp;event_core_name,
    ngx_event_core_create_conf,            /* create configuration */
    ngx_event_core_init_conf,              /* init configuration */
    { NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL }
};

```

       在模块定义中，实现了两种方法分别为 ngx_event_module_init 和ngx_event_process_init 方法。在Nginx 启动过程中没有使用 fork 出 worker 子进程之前，先调用 ngx_event_core_module 模块中的 ngx_event_module_init 方法，当fork 出 worker 子进程后，每一个 worker 子进程则会调用 ngx_event_process_init 方法。

       ngx_event_module_init 方法在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义：

```c
/* 初始化事件模块 */
static ngx_int_t
ngx_event_module_init(ngx_cycle_t *cycle)
{
    void              ***cf;
    u_char              *shared;
    size_t               size, cl;
    ngx_shm_t            shm;
    ngx_time_t          *tp;
    ngx_core_conf_t     *ccf;
    ngx_event_conf_t    *ecf;

    /* 获取存储所有事件模块配置结构的指针数据的首地址 */
    cf = ngx_get_conf(cycle->conf_ctx, ngx_events_module);
    /* 获取事件模块ngx_event_core_module的配置结构 */
    ecf = (*cf)[ngx_event_core_module.ctx_index];

    /* 在错误日志中输出被使用的事件模块名称 */
    if (!ngx_test_config &amp;&amp; ngx_process <= NGX_PROCESS_MASTER) {
        ngx_log_error(NGX_LOG_NOTICE, cycle->log, 0,
                      "using the \"%s\" event method", ecf->name);
    }

    /* 获取模块ngx_core_module的配置结构 */
    ccf = (ngx_core_conf_t *) ngx_get_conf(cycle->conf_ctx, ngx_core_module);

    ngx_timer_resolution = ccf->timer_resolution;

#if !(NGX_WIN32)
    {
    ngx_int_t      limit;
    struct rlimit  rlmt;

    /* 获取当前进程所打开的最大文件描述符个数 */
    if (getrlimit(RLIMIT_NOFILE, &amp;rlmt) == -1) {
        ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                      "getrlimit(RLIMIT_NOFILE) failed, ignored");

    } else {
        /*
         * 当前事件模块的连接数大于最大文件描述符个数，
         * 或者大于由配置文件nginx.conf指定的worker_rlinit_nofile设置的最大文件描述符个数时，
         * 出错返回；
         */
        if (ecf->connections > (ngx_uint_t) rlmt.rlim_cur
            &amp;&amp; (ccf->rlimit_nofile == NGX_CONF_UNSET
                || ecf->connections > (ngx_uint_t) ccf->rlimit_nofile))
        {
            limit = (ccf->rlimit_nofile == NGX_CONF_UNSET) ?
                         (ngx_int_t) rlmt.rlim_cur : ccf->rlimit_nofile;

            ngx_log_error(NGX_LOG_WARN, cycle->log, 0,
                          "%ui worker_connections exceed "
                          "open file resource limit: %i",
                          ecf->connections, limit);
        }
    }
    }
#endif /* !(NGX_WIN32) */

    /*
     * 模块ngx_core_module的master进程为0，表示不创建worker进程，
     * 则初始化到此结束，并成功返回；
     */
    if (ccf->master == 0) {
        return NGX_OK;
    }

    /*
     * 若master不为0，且存在负载均衡锁，则表示初始化完毕，并成功返回；
     */
    if (ngx_accept_mutex_ptr) {
        return NGX_OK;
    }

    /* 不满足以上两个条件，则初始化下列变量 */
    /* cl should be equal to or greater than cache line size */

    /* 缓存行的大小 */
    cl = 128;

    /*
     * 统计需要创建的共享内存大小；
     * ngx_accept_mutex用于多个worker进程之间的负载均衡锁；
     * ngx_connection_counter表示nginx处理的连接总数；
     * ngx_temp_number表示在连接中创建的临时文件个数；
     */
    size = cl            /* ngx_accept_mutex */
           + cl          /* ngx_connection_counter */
           + cl;         /* ngx_temp_number */

#if (NGX_STAT_STUB)

    /*
     * 下面表示某种情况的连接数；
     * ngx_stat_accepted    表示已成功建立的连接数；
     * ngx_stat_handled     表示已获取ngx_connection_t结构并已初始化读写事件的连接数；
     * ngx_stat_requests    表示已被http模块处理过的连接数；
     * ngx_stat_active      表示已获取ngx_connection_t结构体的连接数；
     * ngx_stat_reading     表示正在接收TCP字符流的连接数；
     * ngx_stat_writing     表示正在发送TCP字符流的连接数；
     * ngx_stat_waiting     表示正在等待事件发生的连接数；
     */
    size += cl           /* ngx_stat_accepted */
           + cl          /* ngx_stat_handled */
           + cl          /* ngx_stat_requests */
           + cl          /* ngx_stat_active */
           + cl          /* ngx_stat_reading */
           + cl          /* ngx_stat_writing */
           + cl;         /* ngx_stat_waiting */

#endif

    /* 初始化共享内存信息 */
    shm.size = size;
    shm.name.len = sizeof("nginx_shared_zone");
    shm.name.data = (u_char *) "nginx_shared_zone";
    shm.log = cycle->log;

    /* 创建共享内存 */
    if (ngx_shm_alloc(&amp;shm) != NGX_OK) {
        return NGX_ERROR;
    }

    /* 获取共享内存的首地址 */
    shared = shm.addr;

    ngx_accept_mutex_ptr = (ngx_atomic_t *) shared;
    /* -1表示以非阻塞模式获取共享内存锁 */
    ngx_accept_mutex.spin = (ngx_uint_t) -1;

    if (ngx_shmtx_create(&amp;ngx_accept_mutex, (ngx_shmtx_sh_t *) shared,
                         cycle->lock_file.data)
        != NGX_OK)
    {
        return NGX_ERROR;
    }

    /* 初始化变量 */
    ngx_connection_counter = (ngx_atomic_t *) (shared + 1 * cl);

    (void) ngx_atomic_cmp_set(ngx_connection_counter, 0, 1);

    ngx_log_debug2(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                   "counter: %p, %d",
                   ngx_connection_counter, *ngx_connection_counter);

    ngx_temp_number = (ngx_atomic_t *) (shared + 2 * cl);

    tp = ngx_timeofday();

    ngx_random_number = (tp->msec << 16) + ngx_pid;

#if (NGX_STAT_STUB)

    ngx_stat_accepted = (ngx_atomic_t *) (shared + 3 * cl);
    ngx_stat_handled = (ngx_atomic_t *) (shared + 4 * cl);
    ngx_stat_requests = (ngx_atomic_t *) (shared + 5 * cl);
    ngx_stat_active = (ngx_atomic_t *) (shared + 6 * cl);
    ngx_stat_reading = (ngx_atomic_t *) (shared + 7 * cl);
    ngx_stat_writing = (ngx_atomic_t *) (shared + 8 * cl);
    ngx_stat_waiting = (ngx_atomic_t *) (shared + 9 * cl);

#endif

    return NGX_OK;
}

```

       ngx_event_process_init 方法在文件[src/event/ngx_event.c](http://lxr.nginx.org/source/src/event/ngx_event.c) 中定义：

```c
static ngx_int_t
ngx_event_process_init(ngx_cycle_t *cycle)
{
    ngx_uint_t           m, i;
    ngx_event_t         *rev, *wev;
    ngx_listening_t     *ls;
    ngx_connection_t    *c, *next, *old;
    ngx_core_conf_t     *ccf;
    ngx_event_conf_t    *ecf;
    ngx_event_module_t  *module;

    /* 获取ngx_core_module核心模块的配置结构 */
    ccf = (ngx_core_conf_t *) ngx_get_conf(cycle->conf_ctx, ngx_core_module);
    /* 获取ngx_event_core_module事件核心模块的配置结构 */
    ecf = ngx_event_get_conf(cycle->conf_ctx, ngx_event_core_module);

    /*
     * 在事件核心模块启用accept_mutex锁的情况下，
     * 只有在master-worker工作模式并且worker进程数量大于1，
     * 此时，才确定进程启用负载均衡锁；
     */
    if (ccf->master &amp;&amp; ccf->worker_processes > 1 &amp;&amp; ecf->accept_mutex) {
        ngx_use_accept_mutex = 1;
        ngx_accept_mutex_held = 0;
        ngx_accept_mutex_delay = ecf->accept_mutex_delay;

    } else {/* 否则关闭负载均衡锁 */
        ngx_use_accept_mutex = 0;
    }

#if (NGX_WIN32)

    /*
     * disable accept mutex on win32 as it may cause deadlock if
     * grabbed by a process which can't accept connections
     */

    ngx_use_accept_mutex = 0;

#endif

    ngx_queue_init(&amp;ngx_posted_accept_events);
    ngx_queue_init(&amp;ngx_posted_events);

    /* 初始化由红黑树实现的定时器 */
    if (ngx_event_timer_init(cycle->log) == NGX_ERROR) {
        return NGX_ERROR;
    }

    /* 根据use配置项所指定的事件模块，调用ngx_actions_t中的init方法初始化事件模块 */
    for (m = 0; ngx_modules[m]; m++) {
        if (ngx_modules[m]->type != NGX_EVENT_MODULE) {
            continue;
        }

        if (ngx_modules[m]->ctx_index != ecf->use) {
            continue;
        }

        module = ngx_modules[m]->ctx;

        if (module->actions.init(cycle, ngx_timer_resolution) != NGX_OK) {
            /* fatal */
            exit(2);
        }

        break;
    }

#if !(NGX_WIN32)

    /*
     * NGX_USE_TIMER_EVENT只有在eventport和kqueue事件模型中使用，
     * 若配置文件nginx.conf设置了timer_resolution配置项，
     * 并且事件模型不为eventport和kqueue时，调用settimer方法，
     */
    if (ngx_timer_resolution &amp;&amp; !(ngx_event_flags &amp; NGX_USE_TIMER_EVENT)) {
        struct sigaction  sa;
        struct itimerval  itv;

        ngx_memzero(&amp;sa, sizeof(struct sigaction));
        /*
         * ngx_timer_signal_handler的实现如下：
         * void ngx_timer_signal_handler(int signo)
         * {
         *      ngx_event_timer_alarm = 1;
         * }
         * ngx_event_timer_alarm 为1时表示需要更新系统时间，即调用ngx_time_update方法；
         * 更新完系统时间之后，该变量设为0；
         */
        /* 指定信号处理函数 */
        sa.sa_handler = ngx_timer_signal_handler;
        /* 初始化信号集 */
        sigemptyset(&amp;sa.sa_mask);

        /* 捕获信号SIGALRM */
        if (sigaction(SIGALRM, &amp;sa, NULL) == -1) {
            ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                          "sigaction(SIGALRM) failed");
            return NGX_ERROR;
        }

        /* 设置时间精度 */
        itv.it_interval.tv_sec = ngx_timer_resolution / 1000;
        itv.it_interval.tv_usec = (ngx_timer_resolution % 1000) * 1000;
        itv.it_value.tv_sec = ngx_timer_resolution / 1000;
        itv.it_value.tv_usec = (ngx_timer_resolution % 1000 ) * 1000;

        /* 使用settimer函数发送信号 SIGALRM */
        if (setitimer(ITIMER_REAL, &amp;itv, NULL) == -1) {
            ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                          "setitimer() failed");
        }
    }

    /* 对poll、/dev/poll、rtsig事件模块的特殊处理 */
    if (ngx_event_flags &amp; NGX_USE_FD_EVENT) {
        struct rlimit  rlmt;

        if (getrlimit(RLIMIT_NOFILE, &amp;rlmt) == -1) {
            ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                          "getrlimit(RLIMIT_NOFILE) failed");
            return NGX_ERROR;
        }

        cycle->files_n = (ngx_uint_t) rlmt.rlim_cur;

        cycle->files = ngx_calloc(sizeof(ngx_connection_t *) * cycle->files_n,
                                  cycle->log);
        if (cycle->files == NULL) {
            return NGX_ERROR;
        }
    }

#endif

    /* 预分配连接池 */
    cycle->connections =
        ngx_alloc(sizeof(ngx_connection_t) * cycle->connection_n, cycle->log);
    if (cycle->connections == NULL) {
        return NGX_ERROR;
    }

    c = cycle->connections;

    /* 预分配读事件结构，读事件个数与连接数相同 */
    cycle->read_events = ngx_alloc(sizeof(ngx_event_t) * cycle->connection_n,
                                   cycle->log);
    if (cycle->read_events == NULL) {
        return NGX_ERROR;
    }

    rev = cycle->read_events;
    for (i = 0; i < cycle->connection_n; i++) {
        rev[i].closed = 1;
        rev[i].instance = 1;
    }

    /* 预分配写事件结构，写事件个数与连接数相同 */
    cycle->write_events = ngx_alloc(sizeof(ngx_event_t) * cycle->connection_n,
                                    cycle->log);
    if (cycle->write_events == NULL) {
        return NGX_ERROR;
    }

    wev = cycle->write_events;
    for (i = 0; i < cycle->connection_n; i++) {
        wev[i].closed = 1;
    }

    i = cycle->connection_n;
    next = NULL;

    /* 按照序号，将读、写事件与连接对象对应，即设置到每个ngx_connection_t 对象中 */
    do {
        i--;

        c[i].data = next;
        c[i].read = &amp;cycle->read_events[i];
        c[i].write = &amp;cycle->write_events[i];
        c[i].fd = (ngx_socket_t) -1;

        next = &amp;c[i];

#if (NGX_THREADS)
        c[i].lock = 0;
#endif
    } while (i);

    /* 设置空闲连接链表 */
    cycle->free_connections = next;
    cycle->free_connection_n = cycle->connection_n;

    /* for each listening socket */

    /* 为所有ngx_listening_t监听对象中的connections成员分配连接，并设置读事件的处理方法 */
    ls = cycle->listening.elts;
    for (i = 0; i < cycle->listening.nelts; i++) {

        /* 为监听套接字分配连接，并设置读事件 */
        c = ngx_get_connection(ls[i].fd, cycle->log);

        if (c == NULL) {
            return NGX_ERROR;
        }

        c->log = &amp;ls[i].log;

        c->listening = &amp;ls[i];
        ls[i].connection = c;

        rev = c->read;

        rev->log = c->log;
        rev->accept = 1;

#if (NGX_HAVE_DEFERRED_ACCEPT)
        rev->deferred_accept = ls[i].deferred_accept;
#endif

        if (!(ngx_event_flags &amp; NGX_USE_IOCP_EVENT)) {
            if (ls[i].previous) {

                /*
                 * delete the old accept events that were bound to
                 * the old cycle read events array
                 */

                old = ls[i].previous->connection;

                if (ngx_del_event(old->read, NGX_READ_EVENT, NGX_CLOSE_EVENT)
                    == NGX_ERROR)
                {
                    return NGX_ERROR;
                }

                old->fd = (ngx_socket_t) -1;
            }
        }

#if (NGX_WIN32)

        if (ngx_event_flags &amp; NGX_USE_IOCP_EVENT) {
            ngx_iocp_conf_t  *iocpcf;

            rev->handler = ngx_event_acceptex;

            if (ngx_use_accept_mutex) {
                continue;
            }

            if (ngx_add_event(rev, 0, NGX_IOCP_ACCEPT) == NGX_ERROR) {
                return NGX_ERROR;
            }

            ls[i].log.handler = ngx_acceptex_log_error;

            iocpcf = ngx_event_get_conf(cycle->conf_ctx, ngx_iocp_module);
            if (ngx_event_post_acceptex(&amp;ls[i], iocpcf->post_acceptex)
                == NGX_ERROR)
            {
                return NGX_ERROR;
            }

        } else {
            rev->handler = ngx_event_accept;

            if (ngx_use_accept_mutex) {
                continue;
            }

            if (ngx_add_event(rev, NGX_READ_EVENT, 0) == NGX_ERROR) {
                return NGX_ERROR;
            }
        }

#else

        /* 为监听端口的读事件设置处理方法ngx_event_accept */
        rev->handler = ngx_event_accept;

        if (ngx_use_accept_mutex) {
            continue;
        }

        if (ngx_event_flags &amp; NGX_USE_RTSIG_EVENT) {
            if (ngx_add_conn(c) == NGX_ERROR) {
                return NGX_ERROR;
            }

        } else {
        /* 将监听对象连接的读事件添加到事件驱动模块中 */
            if (ngx_add_event(rev, NGX_READ_EVENT, 0) == NGX_ERROR) {
                return NGX_ERROR;
            }
        }

#endif

    }

    return NGX_OK;
}

```

  

参考资料：

《深入理解 Nginx 》

《[nginx事件模块分析(二)](http://blog.csdn.net/freeinfor/article/details/16343223)》
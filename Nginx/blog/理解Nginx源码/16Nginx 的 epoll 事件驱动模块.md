### 概述

       在前面的文章中《[Nginx 事件模块](http://blog.csdn.net/chenhanzhun/article/details/42805757)》介绍了Nginx 的事件驱动框架以及不同类型事件驱动模块的管理。本节基于前面的知识，简单介绍下在Linux 系统下的 epoll 事件驱动模块。关于 epoll 的使用与原理可以参照文章 《[epoll 解析](http://blog.csdn.net/chenhanzhun/article/details/42747127)》。在这里直接介绍Nginx 服务器基于事件驱动框架实现的epoll 事件驱动模块。

### ngx_epoll_module 事件驱动模块

### ngx_epoll_conf_t 结构体

      ngx_epoll_conf_t 结构体是保存ngx_epoll_module 事件驱动模块的配置项结构。该结构体在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 存储epoll模块配置项结构体 */
typedef struct {
    ngx_uint_t  events;         /* 表示epoll_wait函数返回的最大事件数 */
    ngx_uint_t  aio_requests;   /* 并发处理异步IO事件个数 */
} ngx_epoll_conf_t;

```

### ngx_epoll_module 事件驱动模块的定义

       所有模块的定义都是基于模块通用接口 ngx_module_t 结构，ngx_epoll_module 模块在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义如下：

```c
/* epoll模块定义 */
ngx_module_t  ngx_epoll_module = {
    NGX_MODULE_V1,
    &amp;ngx_epoll_module_ctx,               /* module context */
    ngx_epoll_commands,                  /* module directives */
    NGX_EVENT_MODULE,                    /* module type */
    NULL,                                /* init master */
    NULL,                                /* init module */
    NULL,                                /* init process */
    NULL,                                /* init thread */
    NULL,                                /* exit thread */
    NULL,                                /* exit process */
    NULL,                                /* exit master */
    NGX_MODULE_V1_PADDING
};

```

       在 ngx_epoll_module 模块的定义中，其中定义了该模块感兴趣的配置项ngx_epoll_commands 数组，该配置项数组在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 定义epoll模块感兴趣的配置项结构数组 */
static ngx_command_t  ngx_epoll_commands[] = {

    /*
     * epoll_events配置项表示epoll_wait函数每次返回的最多事件数(即第3个参数)，
     * 在ngx_epoll_init函数中会预分配epoll_events配置项指定的epoll_event结构体；
     */
    { ngx_string("epoll_events"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_conf_set_num_slot,
      0,
      offsetof(ngx_epoll_conf_t, events),
      NULL },

    /*
     * 该配置项表示创建的异步IO上下文能并发处理异步IO事件的个数，
     * 即io_setup函数的第一个参数；
     */
    { ngx_string("worker_aio_requests"),
      NGX_EVENT_CONF|NGX_CONF_TAKE1,
      ngx_conf_set_num_slot,
      0,
      offsetof(ngx_epoll_conf_t, aio_requests),
      NULL },

      ngx_null_command
};

```

       在 ngx_epoll_module 模块的定义中，定义了该模块的上下文结构ngx_epoll_module_ctx，该上下文结构是基于事件模块的通用接口ngx_event_module_t 结构来定义的。该上下文结构在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 由事件模块通用接口ngx_event_module_t定义的epoll模块上下文结构 */
ngx_event_module_t  ngx_epoll_module_ctx = {
    &amp;epoll_name,
    ngx_epoll_create_conf,               /* create configuration */
    ngx_epoll_init_conf,                 /* init configuration */

    {
        ngx_epoll_add_event,             /* add an event */
        ngx_epoll_del_event,             /* delete an event */
        ngx_epoll_add_event,             /* enable an event */
        ngx_epoll_del_event,             /* disable an event */
        ngx_epoll_add_connection,        /* add an connection */
        ngx_epoll_del_connection,        /* delete an connection */
        NULL,                            /* process the changes */
        ngx_epoll_process_events,        /* process the events */
        ngx_epoll_init,                  /* init the events */
        ngx_epoll_done,                  /* done the events */
    }
};

```

       在 ngx_epoll_module 模块的上下文事件接口结构中，重点定义了ngx_event_actions_t 结构中的接口回调方法。

### ngx_epoll_module 事件驱动模块的操作

       ngx_epoll_module 模块的操作由ngx_epoll_module 模块的上下文事件接口结构中成员actions 实现。该成员实现的方法如下所示：

```c
        ngx_epoll_add_event,             /* add an event */
        ngx_epoll_del_event,             /* delete an event */
        ngx_epoll_add_event,             /* enable an event */
        ngx_epoll_del_event,             /* disable an event */
        ngx_epoll_add_connection,        /* add an connection */
        ngx_epoll_del_connection,        /* delete an connection */
        NULL,                            /* process the changes */
        ngx_epoll_process_events,        /* process the events */
        ngx_epoll_init,                  /* init the events */
        ngx_epoll_done,                  /* done the events */

```

#### ngx_epoll_module 模块的初始化

       ngx_epoll_module 模块的初始化由函数ngx_epoll_init 实现。该函数主要做了两件事：创建epoll 对象 和 创建 event_list 数组（调用epoll_wait 函数时用于存储从内核复制的已就绪的事件）；该函数在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
static int                  ep = -1;    /* epoll对象描述符 */
static struct epoll_event  *event_list; /* 作为epoll_wait函数的第二个参数，保存从内存复制的事件 */
static ngx_uint_t           nevents;    /* epoll_wait函数返回的最多事件数 */

/* epoll模块初始化函数 */
static ngx_int_t
ngx_epoll_init(ngx_cycle_t *cycle, ngx_msec_t timer)
{
    ngx_epoll_conf_t  *epcf;

    /* 获取ngx_epoll_module模块的配置项结构 */
    epcf = ngx_event_get_conf(cycle->conf_ctx, ngx_epoll_module);

    if (ep == -1) {
        /* 调用epoll_create函数创建epoll对象描述符 */
        ep = epoll_create(cycle->connection_n / 2);

        /* 若创建失败，则出错返回 */
        if (ep == -1) {
            ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_errno,
                          "epoll_create() failed");
            return NGX_ERROR;
        }

#if (NGX_HAVE_FILE_AIO)

        /* 若系统支持异步IO，则初始化异步IO */
        ngx_epoll_aio_init(cycle, epcf);

#endif
    }

    /*
     * 预分配events个epoll_event结构event_list，event_list是存储产生事件的数组；
     * events由epoll_events配置项指定；
     */
    if (nevents < epcf->events) {
        /*
         * 若现有event_list个数小于配置项所指定的值epcf->events，
         * 则先释放，再从新分配；
         */
        if (event_list) {
            ngx_free(event_list);
        }

        /* 预分配epcf->events个epoll_event结构，并使event_list指向该地址 */
        event_list = ngx_alloc(sizeof(struct epoll_event) * epcf->events,
                               cycle->log);
        if (event_list == NULL) {
            return NGX_ERROR;
        }
    }

    /* 设置正确的epoll_event结构个数 */
    nevents = epcf->events;

    /* 指定IO的读写方法 */
    /*
     * 初始化全局变量ngx_io, ngx_os_io定义为:
        ngx_os_io_t ngx_os_io = {
            ngx_unix_recv,
            ngx_readv_chain,
            ngx_udp_unix_recv,
            ngx_unix_send,
            ngx_writev_chain,
            0
        };（位于src/os/unix/ngx_posix_init.c）
    */
    ngx_io = ngx_os_io;

    /* 设置ngx_event_actions 接口 */
    ngx_event_actions = ngx_epoll_module_ctx.actions;

#if (NGX_HAVE_CLEAR_EVENT)
    /* ET模式 */
    ngx_event_flags = NGX_USE_CLEAR_EVENT
#else
    /* LT模式 */
    ngx_event_flags = NGX_USE_LEVEL_EVENT
#endif
                      |NGX_USE_GREEDY_EVENT
                      |NGX_USE_EPOLL_EVENT;

    return NGX_OK;
}

```

#### ngx_epoll_module 模块的事件处理

       ngx_epoll_module 模块的事件处理由函数ngx_epoll_process_events 实现。ngx_epoll_process_events 函数是实现事件收集、事件发送的接口。该函数在文件[src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 处理已准备就绪的事件 */
static ngx_int_t
ngx_epoll_process_events(ngx_cycle_t *cycle, ngx_msec_t timer, ngx_uint_t flags)
{
    int                events;
    uint32_t           revents;
    ngx_int_t          instance, i;
    ngx_uint_t         level;
    ngx_err_t          err;
    ngx_event_t       *rev, *wev, **queue;
    ngx_connection_t  *c;

    /* NGX_TIMER_INFINITE == INFTIM */

    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                   "epoll timer: %M", timer);

    /* 调用epoll_wait在规定的timer时间内等待监控的事件准备就绪 */
    events = epoll_wait(ep, event_list, (int) nevents, timer);

    /* 若出错，设置错误编码 */
    err = (events == -1) ? ngx_errno : 0;

    /*
     * 若没有设置timer_resolution配置项时，
     * NGX_UPDATE_TIME 标志表示每次调用epoll_wait函数返回后需要更新时间；
     * 若设置timer_resolution配置项，
     * 则每隔timer_resolution配置项参数会设置ngx_event_timer_alarm为1，表示需要更新时间；
     */
    if (flags &amp; NGX_UPDATE_TIME || ngx_event_timer_alarm) {
        /* 更新时间，将时间缓存到一组全局变量中，方便程序高效获取事件 */
        ngx_time_update();
    }

    /* 处理epoll_wait的错误 */
    if (err) {
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

    /*
     * 若epoll_wait返回的事件数events为0，则有两种可能：
     * 1、超时返回，即时间超过timer；
     * 2、在限定的timer时间内返回，此时表示出错error返回；
     */
    if (events == 0) {
        if (timer != NGX_TIMER_INFINITE) {
            return NGX_OK;
        }

        ngx_log_error(NGX_LOG_ALERT, cycle->log, 0,
                      "epoll_wait() returned no events without timeout");
        return NGX_ERROR;
    }

    /* 仅在多线程环境下有效 */
    ngx_mutex_lock(ngx_posted_events_mutex);

    /* 遍历由epoll_wait返回的所有已准备就绪的事件，并处理这些事件 */
    for (i = 0; i < events; i++) {
        /*
         * 获取与事件关联的连接对象；
         * 连接对象地址的最低位保存的是添加事件时设置的事件过期标志位；
         */
        c = event_list[i].data.ptr;

        /* 获取事件过期标志位，即连接对象地址的最低位 */
        instance = (uintptr_t) c &amp; 1;
        /* 屏蔽连接对象的最低位，即获取连接对象的真正地址 */
        c = (ngx_connection_t *) ((uintptr_t) c &amp; (uintptr_t) ~1);

        /* 获取读事件 */
        rev = c->read;

        /*
         * 同一连接的读写事件的instance标志位是相同的；
         * 若fd描述符为-1，或连接对象读事件的instance标志位不相同，则判为过期事件；
         */
        if (c->fd == -1 || rev->instance != instance) {

            /*
             * the stale event from a file descriptor
             * that was just closed in this iteration
             */

            ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                           "epoll: stale event %p", c);
            continue;
        }

        /* 获取连接对象中已准备就绪的事件类型 */
        revents = event_list[i].events;

        ngx_log_debug3(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                       "epoll: fd:%d ev:%04XD d:%p",
                       c->fd, revents, event_list[i].data.ptr);
        /* 记录epoll_wait的错误返回状态 */
        /*
         * EPOLLERR表示连接出错；EPOLLHUP表示收到RST报文；
         * 检测到上面这两种错误时，TCP连接中可能存在未读取的数据；
         */
        if (revents &amp; (EPOLLERR|EPOLLHUP)) {
            ngx_log_debug2(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                           "epoll_wait() error on fd:%d ev:%04XD",
                           c->fd, revents);
        }

#if 0
        if (revents &amp; ~(EPOLLIN|EPOLLOUT|EPOLLERR|EPOLLHUP)) {
            ngx_log_error(NGX_LOG_ALERT, cycle->log, 0,
                          "strange epoll_wait() events fd:%d ev:%04XD",
                          c->fd, revents);
        }
#endif

        /*
         * 若连接发生错误且未设置EPOLLIN、EPOLLOUT，
         * 则将EPOLLIN、EPOLLOUT添加到revents中；
         * 即在调用读写事件时能够处理连接的错误；
         */
        if ((revents &amp; (EPOLLERR|EPOLLHUP))
             &amp;&amp; (revents &amp; (EPOLLIN|EPOLLOUT)) == 0)
        {
            /*
             * if the error events were returned without EPOLLIN or EPOLLOUT,
             * then add these flags to handle the events at least in one
             * active handler
             */

            revents |= EPOLLIN|EPOLLOUT;
        }

        /* 连接有可读事件，且该读事件是active活跃的 */
        if ((revents &amp; EPOLLIN) &amp;&amp; rev->active) {

#if (NGX_HAVE_EPOLLRDHUP)
            /* EPOLLRDHUP表示连接对端关闭了读取端 */
            if (revents &amp; EPOLLRDHUP) {
                rev->pending_eof = 1;
            }
#endif

            if ((flags &amp; NGX_POST_THREAD_EVENTS) &amp;&amp; !rev->accept) {
                rev->posted_ready = 1;

            } else {
                /* 读事件已准备就绪 */
                /*
                 * 这里要区分active与ready：
                 * active是指事件被添加到epoll对象的监控中，
                 * 而ready表示被监控的事件已经准备就绪，即可以对其进程IO处理；
                 */
                rev->ready = 1;
            }

            /*
             * NGX_POST_EVENTS表示已准备就绪的事件需要延迟处理，
             * 根据accept标志位将事件加入到相应的队列中；
             */
            if (flags &amp; NGX_POST_EVENTS) {
                queue = (ngx_event_t **) (rev->accept ?
                               &amp;ngx_posted_accept_events : &amp;ngx_posted_events);

                ngx_locked_post_event(rev, queue);

            } else {
                /* 若不延迟处理，则直接调用事件的处理函数 */
                rev->handler(rev);
            }
        }

        /* 获取连接的写事件，写事件的处理逻辑过程与读事件类似 */
        wev = c->write;

        /* 连接有可写事件，且该写事件是active活跃的 */
        if ((revents &amp; EPOLLOUT) &amp;&amp; wev->active) {

            /* 检查写事件是否过期 */
            if (c->fd == -1 || wev->instance != instance) {

                /*
                 * the stale event from a file descriptor
                 * that was just closed in this iteration
                 */

                ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                               "epoll: stale event %p", c);
                continue;
            }

            if (flags &amp; NGX_POST_THREAD_EVENTS) {
                wev->posted_ready = 1;

            } else {

                /* 写事件已准备就绪 */
                wev->ready = 1;
            }

            /*
             * NGX_POST_EVENTS表示已准备就绪的事件需要延迟处理，
             * 根据accept标志位将事件加入到相应的队列中；
             */
            if (flags &amp; NGX_POST_EVENTS) {
                ngx_locked_post_event(wev, &amp;ngx_posted_events);

            } else {
                /* 若不延迟处理，则直接调用事件的处理函数 */
                wev->handler(wev);
            }
        }
    }

    ngx_mutex_unlock(ngx_posted_events_mutex);

    return NGX_OK;
}

```

#### ngx_epoll_module 模块的事件添加与删除

       ngx_epoll_module 模块的事件添加与删除分别由函数ngx_epoll_add_event 与ngx_epoll_del_event 实现。这两个函数的实现都是通过调用epoll_ctl 函数。具体实现在文件 [src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 将某个描述符的某个事件添加到epoll对象的监控机制中 */
static ngx_int_t
ngx_epoll_add_event(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags)
{
    int                  op;
    uint32_t             events, prev;
    ngx_event_t         *e;
    ngx_connection_t    *c;
    struct epoll_event   ee;

    /* 每个事件的data成员都存放着其对应的ngx_connection_t连接 */
    /* 获取事件关联的连接 */
    c = ev->data;

    /* events参数是方便下面确定当前事件是可读还是可写 */
    events = (uint32_t) event;

    /*
     * 这里在判断事件类型是可读还是可写，必须根据事件的active标志位来判断事件是否活跃；
     * 因为epoll_ctl函数有添加add和修改mod模式，
     * 若一个事件所关联的连接已经在epoll对象的监控中，则只需修改事件的类型即可；
     * 若一个事件所关联的连接没有在epoll对象的监控中，则需要将其相应的事件类型注册到epoll对象中；
     * 这样做的情况是避免与事件相关联的连接两次注册到epoll对象中；
     */

    if (event == NGX_READ_EVENT) {
        /*
         * 若待添加的事件类型event是可读；
         * 则首先判断该事件所关联的连接是否将写事件添加到epoll对象中，
         * 即先判断关联的连接的写事件是否为活跃事件；
         */
        e = c->write;
        prev = EPOLLOUT;
#if (NGX_READ_EVENT != EPOLLIN|EPOLLRDHUP)
        events = EPOLLIN|EPOLLRDHUP;
#endif

    } else {
        e = c->read;
        prev = EPOLLIN|EPOLLRDHUP;
#if (NGX_WRITE_EVENT != EPOLLOUT)
        events = EPOLLOUT;
#endif
    }

    /* 根据active标志位确定事件是否为活跃事件，以决定到达是修改还是添加事件 */
    if (e->active) {
        /* 若当前事件是活跃事件，则只需修改其事件类型即可 */
        op = EPOLL_CTL_MOD;
        events |= prev;

    } else {
        /* 若当前事件不是活跃事件，则将该事件添加到epoll对象中 */
        op = EPOLL_CTL_ADD;
    }

    /* 将flags参数加入到events标志位中 */
    ee.events = events | (uint32_t) flags;
    /* prt存储事件关联的连接对象ngx_connection_t以及过期事件instance标志位 */
    ee.data.ptr = (void *) ((uintptr_t) c | ev->instance);

    ngx_log_debug3(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                   "epoll add event: fd:%d op:%d ev:%08XD",
                   c->fd, op, ee.events);

    /* 调用epoll_ctl方法向epoll对象添加事件或在epoll对象中修改事件 */
    if (epoll_ctl(ep, op, c->fd, &amp;ee) == -1) {
        ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_errno,
                      "epoll_ctl(%d, %d) failed", op, c->fd);
        return NGX_ERROR;
    }

    /* 将该事件的active标志位设置为1，表示当前事件是活跃事件 */
    ev->active = 1;
#if 0
    ev->oneshot = (flags &amp; NGX_ONESHOT_EVENT) ? 1 : 0;
#endif

    return NGX_OK;
}

/* 将某个连接的某个事件从epoll对象监控中删除 */
static ngx_int_t
ngx_epoll_del_event(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags)
{
    int                  op;
    uint32_t             prev;
    ngx_event_t         *e;
    ngx_connection_t    *c;
    struct epoll_event   ee;

    /*
     * when the file descriptor is closed, the epoll automatically deletes
     * it from its queue, so we do not need to delete explicitly the event
     * before the closing the file descriptor
     */

    /* 当事件关联的文件描述符关闭后，epoll对象自动将其事件删除 */
    if (flags &amp; NGX_CLOSE_EVENT) {
        ev->active = 0;
        return NGX_OK;
    }

    /* 获取事件关联的连接对象 */
    c = ev->data;

    /* 根据event参数判断当前删除的是读事件还是写事件 */
    if (event == NGX_READ_EVENT) {
        /* 若要删除读事件，则首先判断写事件的active标志位 */
        e = c->write;
        prev = EPOLLOUT;

    } else {
        /* 若要删除写事件，则判断读事件的active标志位 */
        e = c->read;
        prev = EPOLLIN|EPOLLRDHUP;
    }

    /*
     * 若要删除读事件，且写事件是活跃事件，则修改事件类型即可；
     * 若要删除写事件，且读事件是活跃事件，则修改事件类型即可；
     */
    if (e->active) {
        op = EPOLL_CTL_MOD;
        ee.events = prev | (uint32_t) flags;
        ee.data.ptr = (void *) ((uintptr_t) c | ev->instance);

    } else {
        /* 若读写事件都不是活跃事件，此时表示事件未准备就绪，则将其删除 */
        op = EPOLL_CTL_DEL;
        ee.events = 0;
        ee.data.ptr = NULL;
    }

    ngx_log_debug3(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                   "epoll del event: fd:%d op:%d ev:%08XD",
                   c->fd, op, ee.events);

    /* 删除或修改事件 */
    if (epoll_ctl(ep, op, c->fd, &amp;ee) == -1) {
        ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_errno,
                      "epoll_ctl(%d, %d) failed", op, c->fd);
        return NGX_ERROR;
    }

    /* 设置当前事件的active标志位 */
    ev->active = 0;

    return NGX_OK;
}

```

#### ngx_epoll_module 模块的连接添加与删除

       ngx_epoll_module 模块的连接添加与删除分别由函数ngx_epoll_add_connection 与ngx_epoll_del_connection 实现。这两个函数的实现都是通过调用epoll_ctl 函数。具体实现在文件 [src/event/modules/ngx_epoll_module.c](http://lxr.nginx.org/source/src/event/modules/ngx_epoll_module.c) 中定义：

```c
/* 将指定连接所关联的描述符添加到epoll对象中 */
static ngx_int_t
ngx_epoll_add_connection(ngx_connection_t *c)
{
    struct epoll_event  ee;

    /* 设置事件的类型：可读、可写、ET模式 */
    ee.events = EPOLLIN|EPOLLOUT|EPOLLET|EPOLLRDHUP;
    ee.data.ptr = (void *) ((uintptr_t) c | c->read->instance);

    ngx_log_debug2(NGX_LOG_DEBUG_EVENT, c->log, 0,
                   "epoll add connection: fd:%d ev:%08XD", c->fd, ee.events);

    /* 调用epoll_ctl方法将连接所关联的描述符添加到epoll对象中 */
    if (epoll_ctl(ep, EPOLL_CTL_ADD, c->fd, &amp;ee) == -1) {
        ngx_log_error(NGX_LOG_ALERT, c->log, ngx_errno,
                      "epoll_ctl(EPOLL_CTL_ADD, %d) failed", c->fd);
        return NGX_ERROR;
    }

    /* 设置读写事件的active标志位 */
    c->read->active = 1;
    c->write->active = 1;

    return NGX_OK;
}
Nginx

/* 将连接所关联的描述符从epoll对象中删除 */
static ngx_int_t
ngx_epoll_del_connection(ngx_connection_t *c, ngx_uint_t flags)
{
    int                 op;
    struct epoll_event  ee;

    /*
     * when the file descriptor is closed the epoll automatically deletes
     * it from its queue so we do not need to delete explicitly the event
     * before the closing the file descriptor
     */

    if (flags &amp; NGX_CLOSE_EVENT) {
        c->read->active = 0;
        c->write->active = 0;
        return NGX_OK;
    }

    ngx_log_debug1(NGX_LOG_DEBUG_EVENTNginx, c->log, 0,
                   "epoll del connection: fd:%d", c->fd);

    op = EPOLL_CTL_DEL;
    ee.events = 0;
    ee.data.ptr = NULL;

    /* 调用epoll_ctl方法将描述符从epoll对象中删除 */
    if (epoll_ctl(ep, op, c->fd, &amp;ee) == -1) {
        ngx_log_error(NGX_LOG_ALERT, c->log, ngx_errno,
                      "epoll_ctl(%d, %d) failed", op, c->fd);
        return NGX_ERROR;
    }

    /* 设置描述符读写事件的active标志位 */
    c->read->active = 0;
    c->write->active = 0;

    return NGX_OK;
}

```

### ngx_epoll_module 模块的异步 I/O

       在 Nginx 中，文件异步 I/O 事件完成后的通知是集成到 epoll 对象中。该模块的文件异步I/O 实现如下：

```c
#if (NGX_HAVE_FILE_AIO)

int                         ngx_eventfd = -1;   /* 用于通知异步IO的事件描述符 */
aio_context_t               ngx_aio_ctx = 0;    /* 异步IO的上下文结构，由io_setup 函数初始化 */

static ngx_event_t          ngx_eventfd_event;  /* 异步IO事件 */
static ngx_connection_t     ngx_eventfd_conn;   /* 异步IO事件所对应的连接ngx_connection_t */

#endif

#if (NGX_HAVE_FILE_AIO)

/*
 * We call io_setup(), io_destroy() io_submit(), and io_getevents() directly
 * as syscalls instead of libaio usage, because the library header file
 * supports eventfd() since 0.3.107 version only.
 *
 * Also we do not use eventfd() in glibc, because glibc supports it
 * since 2.8 version and glibc maps two syscalls eventfd() and eventfd2()
 * into single eventfd() function with different number of parameters.
 */

/* 初始化文件异步IO的上下文结构 */
static int
io_setup(u_int nr_reqs, aio_context_t *ctx)
{
    return syscall(SYS_io_setup, nr_reqs, ctx);
}

/* 销毁文件异步IO的上下文结构 */
static int
io_destroy(aio_context_t ctx)
{
    return syscall(SYS_io_destroy, ctx);
}

/* 从文件异步IO操作队列中读取操作 */
static int
io_getevents(aio_context_t ctx, long min_nr, long nr, struct io_event *events,
    struct timespec *tmo)
{
    return syscall(SYS_io_getevents, ctx, min_nr, nr, events, tmo);
}

/* 异步IO的初始化 */
static void
ngx_epoll_aio_init(ngx_cycle_t *cycle, ngx_epoll_conf_t *epcf)
{
    int                 n;
    struct epoll_event  ee;

    /* 使用Linux系统调用获取一个描述符句柄 */
    ngx_eventfd = syscall(SYS_eventfd, 0);

    if (ngx_eventfd == -1) {
        ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_errno,
                      "eventfd() failed");
        ngx_file_aio = 0;
        return;
    }

    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                   "eventfd: %d", ngx_eventfd);

    n = 1;

    /* 设置ngx_eventfd描述符句柄为非阻塞IO模式 */
    if (ioctl(ngx_eventfd, FIONBIO, &amp;n) == -1) {
        ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_errno,
                      "ioctl(eventfd, FIONBIO) failed");
        goto failed;
    }

    /* 初始化文件异步IO的上下文结构 */
    if (io_setup(epcf->aio_requests, &amp;ngx_aio_ctx) == -1) {
        ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_errno,
                      "io_setup() failed");
        goto failed;
    }

    /* 设置异步IO事件ngx_eventfd_event，该事件是ngx_eventfd对应的ngx_event事件 */

    /* 用于异步IO完成通知的ngx_eventfd_event事件，它与ngx_eventfd_conn连接对应 */
    ngx_eventfd_event.data = &amp;ngx_eventfd_conn;
    /* 在异步IO事件完成后，调用ngx_epoll_eventfd_handler处理方法 */
    ngx_eventfd_event.handler = ngx_epoll_eventfd_handler;
    /* 设置事件相应的日志 */
    ngx_eventfd_event.log = cycle->log;
    /* 设置active标志位 */
    ngx_eventfd_event.active = 1;
    /* 初始化ngx_eventfd_conn 连接 */
    ngx_eventfd_conn.fd = ngx_eventfd;
    /* ngx_eventfd_conn连接的读事件就是ngx_eventfd_event事件 */
    ngx_eventfd_conn.read = &amp;ngx_eventfd_event;
    /* 设置连接的相应日志 */
    ngx_eventfd_conn.log = cycle->log;

    ee.events = EPOLLIN|EPOLLET;
    ee.data.ptr = &amp;ngx_eventfd_conn;

    /* 向epoll对象添加异步IO通知描述符ngx_eventfd */
    if (epoll_ctl(ep, EPOLL_CTL_ADD, ngx_eventfd, &amp;ee) != -1) {
        return;
    }

    /* 若添加出错，则销毁文件异步IO上下文结构，并返回 */
    ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_errno,
                  "epoll_ctl(EPOLL_CTL_ADD, eventfd) failed");

    if (io_destroy(ngx_aio_ctx) == -1) {
        ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                      "io_destroy() failed");
    }

failed:

    if (close(ngx_eventfd) == -1) {
        ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                      "eventfd close() failed");
    }

    ngx_eventfd = -1;
    ngx_aio_ctx = 0;
    ngx_file_aio = 0;
}

#endif

#if (NGX_HAVE_FILE_AIO)

/* 处理已完成的异步IO事件 */
static void
ngx_epoll_eventfd_handler(ngx_event_t *ev)
{
    int               n, events;
    long              i;
    uint64_t          ready;
    ngx_err_t         err;
    ngx_event_t      *e;
    ngx_event_aio_t  *aio;
    struct io_event   event[64];    /* 一次最多处理64个事件 */
    struct timespec   ts;

    ngx_log_debug0(NGX_LOG_DEBUG_EVENT, ev->log, 0, "eventfd handler");

    /* 获取已完成的事件数，并将其设置到ready */
    n = read(ngx_eventfd, &amp;ready, 8);

    err = ngx_errno;

    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, ev->log, 0, "eventfd: %d", n);

    if (n != 8) {
        if (n == -1) {
            if (err == NGX_EAGAIN) {
                return;
            }

            ngx_log_error(NGX_LOG_ALERT, ev->log, err, "read(eventfd) failed");
            return;
        }

        ngx_log_error(NGX_LOG_ALERT, ev->log, 0,
                      "read(eventfd) returned only %d bytes", n);
        return;
    }

    ts.tv_sec = 0;
    ts.tv_nsec = 0;

    /* 遍历ready，处理异步IO事件 */
    while (ready) {

        /* 获取已完成的异步IO事件 */
        events = io_getevents(ngx_aio_ctx, 1, 64, event, &amp;ts);

        ngx_log_debug1(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                       "io_getevents: %l", events);

        if (events > 0) {
            ready -= events;/* ready减去已经取出的事件数 */

            /* 处理已被取出的事件 */
            for (i = 0; i < events; i++) {

                ngx_log_debug4(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                               "io_event: %uXL %uXL %L %L",
                                event[i].data, event[i].obj,
                                event[i].res, event[i].res2);

                /* 获取异步IO事件对应的实际事件 */
                e = (ngx_event_t *) (uintptr_t) event[i].data;

                e->complete = 1;
                e->active = 0;
                e->ready = 1;

                aio = e->data;
                aio->res = event[i].res;

                /* 将实际事件加入到ngx_posted_event队列中等待处理 */
                ngx_post_event(e, &amp;ngx_posted_events);
            }

            continue;
        }

        if (events == 0) {
            return;
        }

        /* events == -1 */
        ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_errno,
                      "io_getevents() failed");
        return;
    }
}

#endif

```

参考资料：

《深入理解Nginx》

《[模块ngx_epoll_module详解](http://blog.csdn.net/xiajun07061225/article/details/9250341)》

《 [nginx epoll详解](http://blog.csdn.net/freeinfor/article/details/17008131)》

《[Nginx源码分析-Epoll模块](http://www.alidata.org/archives/1296)》

《[关于ngx_epoll_add_event的一些解释](http://blog.csdn.net/brainkick/article/details/9080789)》
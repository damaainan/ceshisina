## PHP FPM源代码反刍品味之六：IO事件机制的封装.

2016.08.05 18:30*

来源：[https://www.jianshu.com/p/dac223d7d9ad](https://www.jianshu.com/p/dac223d7d9ad)


服务器程序绕不过对操作系统IO事件机制(多路复用)的使用,

不同的操作系统，支持不同的IO事件机制，linux 支持epoll,

windows支持select, freebsd 支持kqueue. 详细如下:

```c
select     (任何 POSIX os, 含windows)
poll       (任何 POSIX os)
epoll      (linux >= 2.5.44)
kqueue     (FreeBSD >= 4.1, OpenBSD >= 2.9, NetBSD >= 2.0)
/dev/poll  (Solaris >= 7)
port       (Solaris >= 10)

```

程序要支持跨平台,需要支持不同的机制,但不同的机制有不同的使用接口(api),

为了省事,需要有个统一的接口来处理. 这个需求早就有聪明人为我们考虑到了.

现成的库libevent, libev, libuv就是对不同操作系统IO事件机制的封装,

要开发服务程序,选一个就可用.

PHP FPM 没有使用上述封装好的库, 而是自己实现了一个简单的封装.

我们看看FPM怎么封装的? 同时也了解下C语言对函数的封装实现.
### FPM IO事件机制封装的特点.

* IO事件的文件句柄fd 和 事件处理函数(回调函数) 封装到一个对象中(c结构体)
* 设计一个新的对象实现不同函数的封装.
* 编译时选择事件机制, 编译时通过对操作系统的检测,选择支持的机制.


### 源代码注释说明

事件对象结构

```c
//fpm_event.h
struct fpm_event_s {
    int fd;                   /* IO 文件句柄*/
    struct timeval timeout;   
    struct timeval frequency;
    void (*callback)(struct fpm_event_s *, short, void *); /* 回调函数 */
    void *arg; /* 回调函数的参数 */
    int flags;
    int index;         
    short which;       
};
//队列,多个事件对象的容器
typedef struct fpm_event_queue_s {
    struct fpm_event_queue_s *prev;
    struct fpm_event_queue_s *next;
    struct fpm_event_s *ev;
} fpm_event_queue;

```

事件模块封装结构

```c
struct fpm_event_module_s {
    const char *name;
    int support_edge_trigger;
    int (*init)(int max_fd);
    int (*clean)(void);
    //等待多个事件
    int (*wait)(struct fpm_event_queue_s *queue, unsigned long int timeout);
    int (*add)(struct fpm_event_s *ev);
    int (*remove)(struct fpm_event_s *ev);
};

```

这个结构很清晰:

就是对事件对象的 添加,移除,等待操作,

再加上使用前后的初始化及清理操作.

对事件的添加,移除,等待也是不同实现机制的同一的一个接口.

由于事件回调函数及其参数也放到了事件对象里, 等到事件发生时,触发回调函数即可.

了解的外观接口后,我们看看不同机制模块的实现, 以epoll,select为例.
### epoll

```c
//events/epoll.c
//环境变量,在编译时确定是否纳入.
#if HAVE_EPOLL

#include <sys/epoll.h>
#include <errno.h>

static int fpm_event_epoll_init(int max);
static int fpm_event_epoll_clean();
static int fpm_event_epoll_wait(struct fpm_event_queue_s *queue, unsigned long int timeout);
static int fpm_event_epoll_add(struct fpm_event_s *ev);
static int fpm_event_epoll_remove(struct fpm_event_s *ev);

static struct fpm_event_module_s epoll_module = {
    .name = "epoll",
    .support_edge_trigger = 1,
    .init = fpm_event_epoll_init,
    .clean = fpm_event_epoll_clean,
    .wait = fpm_event_epoll_wait,
    .add = fpm_event_epoll_add,
    .remove = fpm_event_epoll_remove, 
};

//全局变量
static struct epoll_event *epollfds = NULL;
static int nepollfds = 0;
static int epollfd = -1;

#endif /* HAVE_EPOLL */

//这是使用时,获取模块对象的函数
//系统不支持返回NULL,这个函数总是编入二进制文件里.
struct fpm_event_module_s *fpm_event_epoll_module() /* {{{ */
{
#if HAVE_EPOLL
    return &epoll_module;
#else
    return NULL;
#endif /* HAVE_EPOLL */
}
/* }}} */

#if HAVE_EPOLL

/*
 * Init the module
 */
static int fpm_event_epoll_init(int max) /* {{{ */
{
    if (max < 1) {
        return 0;
    }

    /* init epoll */
    epollfd = epoll_create(max + 1);
    if (epollfd < 0) {
        zlog(ZLOG_ERROR, "epoll: unable to initialize");
        return -1;
    }

    /* allocate fds */
    epollfds = malloc(sizeof(struct epoll_event) * max);
    if (!epollfds) {
        zlog(ZLOG_ERROR, "epoll: unable to allocate %d events", max);
        return -1;
    }
    memset(epollfds, 0, sizeof(struct epoll_event) * max);

    /* save max */
    nepollfds = max;

    return 0;
}
/* }}} */

/*
 * Clean the module
 */
static int fpm_event_epoll_clean() /* {{{ */
{
    /* free epollfds */
    if (epollfds) {
        free(epollfds);
        epollfds = NULL;
    }
    if (epollfd != -1) {
        close(epollfd);
        epollfd = -1;
    }

    nepollfds = 0;

    return 0;
}
/* }}} */

/*
 * wait for events or timeout
 */
static int fpm_event_epoll_wait(struct fpm_event_queue_s *queue, unsigned long int timeout) /* {{{ */
{
    int ret, i;

    /* ensure we have a clean epoolfds before calling epoll_wait() */
    memset(epollfds, 0, sizeof(struct epoll_event) * nepollfds);

    /* wait for inconming event or timeout */
    ret = epoll_wait(epollfd, epollfds, nepollfds, timeout);
    if (ret == -1) {

        /* trigger error unless signal interrupt */
        if (errno != EINTR) {
            zlog(ZLOG_WARNING, "epoll_wait() returns %d", errno);
            return -1;
        }
    }

    /* events have been triggered, let's fire them */
    for (i = 0; i < ret; i++) {

        /* do we have a valid ev ptr ? */
        if (!epollfds[i].data.ptr) {
            continue;
        }

        /* fire the event */
        fpm_event_fire((struct fpm_event_s *)epollfds[i].data.ptr);

        /* sanity check */
        if (fpm_globals.parent_pid != getpid()) {
            return -2;
        }
    }

    return ret;
}
/* }}} */

/*
 * Add a FD to the fd set
 */
static int fpm_event_epoll_add(struct fpm_event_s *ev) /* {{{ */
{
    struct epoll_event e;

    /* fill epoll struct */
    e.events = EPOLLIN;
    e.data.fd = ev->fd;
    
    //data.ptr 设为自定义对象, 事件触发时,以此获取自定义对象
    e.data.ptr = (void *)ev;

    if (ev->flags & FPM_EV_EDGE) {
        e.events = e.events | EPOLLET;
    }

    /* add the event to epoll internal queue */
    if (epoll_ctl(epollfd, EPOLL_CTL_ADD, ev->fd, &e) == -1) {
        zlog(ZLOG_ERROR, "epoll: unable to add fd %d", ev->fd);
        return -1;
    }

    /* mark the event as registered */
    ev->index = ev->fd;
    return 0;
}
/* }}} */

/*
 * Remove a FD from the fd set
 */
static int fpm_event_epoll_remove(struct fpm_event_s *ev) /* {{{ */
{
    struct epoll_event e;

    /* fill epoll struct the same way we did in fpm_event_epoll_add() */
    e.events = EPOLLIN;
    e.data.fd = ev->fd;
    e.data.ptr = (void *)ev;

    if (ev->flags & FPM_EV_EDGE) {
        e.events = e.events | EPOLLET;
    }

    /* remove the event from epoll internal queue */
    if (epoll_ctl(epollfd, EPOLL_CTL_DEL, ev->fd, &e) == -1) {
        zlog(ZLOG_ERROR, "epoll: unable to remove fd %d", ev->fd);
        return -1;
    }

    /* mark the event as not registered */
    ev->index = -1;
    return 0;
}
/* }}} */

#endif /* HAVE_EPOLL */


```
### select

```c
////events/select.c
//环境变量,在编译时确定是否纳入.
#if HAVE_SELECT

/* According to POSIX.1-2001 */
#include <sys/select.h>

/* According to earlier standards */
#include <sys/time.h>
#include <sys/types.h>
#include <unistd.h>

#include <errno.h>

static int fpm_event_select_init(int max);
static int fpm_event_select_wait(struct fpm_event_queue_s *queue, unsigned long int timeout);
static int fpm_event_select_add(struct fpm_event_s *ev);
static int fpm_event_select_remove(struct fpm_event_s *ev);

static struct fpm_event_module_s select_module = {
    .name = "select",
    .support_edge_trigger = 0,
    .init = fpm_event_select_init,
    .clean = NULL,
    .wait = fpm_event_select_wait,
    .add = fpm_event_select_add,
    .remove = fpm_event_select_remove,
};

//全局变量
static fd_set fds;

#endif /* HAVE_SELECT */

//这是使用时,获取模块对象的函数
//系统不支持返回NULL,这个函数总是编入二进制文件里.
struct fpm_event_module_s *fpm_event_select_module() /* {{{ */
{
#if HAVE_SELECT
    return &select_module;
#else
    return NULL;
#endif /* HAVE_SELECT */
}
/* }}} */

#if HAVE_SELECT

/*
 * Init the module
 */
static int fpm_event_select_init(int max) /* {{{ */
{
    FD_ZERO(&fds);
    return 0;
}
/* }}} */


/*
 * wait for events or timeout
 */
static int fpm_event_select_wait(struct fpm_event_queue_s *queue, unsigned long int timeout) /* {{{ */
{
    int ret;
    struct fpm_event_queue_s *q;
    fd_set current_fds;
    struct timeval t;

    /* copy fds because select() alters it */
    current_fds = fds;

    /* fill struct timeval with timeout */
    t.tv_sec = timeout / 1000;
    t.tv_usec = (timeout % 1000) * 1000;

    /* wait for inconming event or timeout */
    ret = select(FD_SETSIZE, &current_fds, NULL, NULL, &t);
    if (ret == -1) {

        /* trigger error unless signal interrupt */
        if (errno != EINTR) {
            zlog(ZLOG_WARNING, "poll() returns %d", errno);
            return -1;
        }
    }

    /* events have been triggered */
    if (ret > 0) {

        /* trigger POLLIN events */
        q = queue;
        while (q) {
            if (q->ev) { /* sanity check */

                /* check if the event has been triggered */
                if (FD_ISSET(q->ev->fd, &current_fds)) {

                    /* fire the event */
                    fpm_event_fire(q->ev);

                    /* sanity check */
                    if (fpm_globals.parent_pid != getpid()) {
                        return -2;
                    }
                }
            }
            q = q->next; /* iterate */
        }
    }
    return ret;

}
/* }}} */

/*
 * Add a FD to the fd set
 */
static int fpm_event_select_add(struct fpm_event_s *ev) /* {{{ */
{
    /* check size limitation */
    if (ev->fd >= FD_SETSIZE) {
        zlog(ZLOG_ERROR, "select: not enough space in the select fd list (max = %d). Please consider using another event mechanism.", FD_SETSIZE);
        return -1;
    }

    /* add the FD if not already in */
    if (!FD_ISSET(ev->fd, &fds)) {
        FD_SET(ev->fd, &fds);
        ev->index = ev->fd;
    }

    return 0;
}
/* }}} */

/*
 * Remove a FD from the fd set
 */
static int fpm_event_select_remove(struct fpm_event_s *ev) /* {{{ */
{
    /* remove the fd if it's in */
    if (FD_ISSET(ev->fd, &fds)) {
        FD_CLR(ev->fd, &fds);
        ev->index = -1;
    }

    return 0;
}
/* }}} */

#endif /* HAVE_SELECT */


```


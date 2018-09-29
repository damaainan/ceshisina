## PHP FPM源代码反刍品味之四：事件处理

2016.08.03 17:16*

来源：[https://www.jianshu.com/p/e567ba80f3b2](https://www.jianshu.com/p/e567ba80f3b2)


FPM master 进程启动后，会进入函数fpm_event_loop,无限循环.

处理事件.
### 事件概要

master 进程所做的的事，总的来说就是两类：
#### 一 定时器事件

简称timer事件,需按时运行，主要有３个:

* fpm_pctl_heartbeat, 任务:检查超时进程
* fpm_pctl_perform_idle_server_maintenance_heartbeat ,

任务:worker进程动态管理,更新记分板的统计数据
* fpm_systemd_heartbeat，任务: 发送fpm状态信息给systemd

(这一项需FPM编译时，启用 systemd 集成　--with-fpm-systemd 默认为 no)


#### 二 文件可读事件

简称fd事件,需从文件句柄(file descriptor)读取到指令后，依指令运行．

重复一下，unix 下一切IO, 皆文件，socket ,socketpair,pipe 都返回文件句柄(fd) 用于通信．

主要的fd有:

* 信号fd,  master 进程中，操作系统信号，SIGTERM，SIGINT等会被自定义函数写到一个socketpair管道里.监听这个信号fd，处理操作系统信号．关于信号处理，另文详述．
* 网络监听socket fd(listening_socket)

有请求时，创建worker进程，处理连接．(需配置为按需模式ondemand)

事件添加代码：


```c
//fpm_children.c
int fpm_children_create_initial(struct fpm_worker_pool_s *wp) 
{
        ...
        memset(wp->ondemand_event, 0, sizeof(struct fpm_event_s));
        fpm_event_set(wp->ondemand_event, wp->listening_socket, FPM_EV_READ | FPM_EV_EDGE, fpm_pctl_on_socket_accept, wp);
        wp->socket_event_set = 1;
        fpm_event_add(wp->ondemand_event, 0);
        ...
}

```

* worker进程标准输出stdout. (需配置catch_workers_output = yes)
* worker进程标准错误输出stderr　(需配置catch_workers_output = yes)

默认情况下worker进程标准输出stdout和标准错误输出stderr,为/dev/null,不记录．

开启catch_workers_output，会通过pipe管道导到master 进程，写到日志里．

开启catch_workers_output，有助于排查错误

事件添加代码：


```c
//fpm_stdio.c
int fpm_stdio_parent_use_pipes(struct fpm_child_s *child) 
{
  ...
  child->fd_stdout = fd_stdout[0];
  child->fd_stderr = fd_stderr[0];

  fpm_event_set(&child->ev_stdout, child->fd_stdout, FPM_EV_READ, fpm_stdio_child_said, child);
  fpm_event_add(&child->ev_stdout, 0);

  fpm_event_set(&child->ev_stderr, child->fd_stderr, FPM_EV_READ, fpm_stdio_child_said, child);
  fpm_event_add(&child->ev_stderr, 0);
  return 0;
}

```
#### 两类事件的不同点是：

对于timer事件，多个事件在事件轴上是依次排列的，只需反复检查，到时运行．

对于fd事件，需监听多个fd，需用到我们第二篇讲的IO多路复用技术．
#### 两类事件的共同点是：

如果满足事件条件，则处理事件内容．

FPM设计上，两类事件使用同一个结构，并且事件触发条件和事件处理逻辑放到同一个事件对象里(C语言对象就是结构体)．

举个例子， **`打铃下课，打铃是触发条件，下课是事件内容，两个同时放到一个事件对象`** ，这是一个很好的设计．
### 事件对象结构

```c
//fpm_event.h
struct fpm_event_s {
    int fd;                   /* 没设置,表示定时事件*/
    struct timeval timeout;   /* timer事件触发时间点*/
    struct timeval frequency;　/* timer事件触发事件间隔*/
    void (*callback)(struct fpm_event_s *, short, void *); /* 回调函数 */
    void *arg; /* 回调函数的参数 */
    int flags;
    int index;                
    short which;             /* 事件类型 */
};

```
#### timer事件

fd值: -1

flags值:　FPM_EV_PERSIST

which值:  FPM_EV_TIMEOUT
#### fd事件.

fd值: 获取触发指令的文件fd

flags值: FPM_EV_EDGE(fd事件底层的边缘触发标志,需系统支持)

which值: FPM_EV_READ

两类事件分别放在两个事件队列

static struct fpm_event_queue_s *fpm_event_queue_timer = NULL;　

static struct fpm_event_queue_s *fpm_event_queue_fd = NULL;

事件队列的结构很常见，双向队列：

typedef struct fpm_event_queue_s {

struct fpm_event_queue_s *prev;

struct fpm_event_queue_s *next;

struct fpm_event_s *ev;

} fpm_event_queue;
### 事件相关的重要函数：

* 创建fd事件对象函数fpm_event_set：

第一个参数会获得新建对象的指针，后续参数为事件对象参数


```c
//fpm_events.c
int fpm_event_set(struct fpm_event_s *ev, int fd, int flags, void (*callback)(struct fpm_event_s *, short, void *), void *arg) 
{
    if (!ev || !callback || fd < -1) {
        return -1;
    }
    memset(ev, 0, sizeof(struct fpm_event_s));
    ev->fd = fd;
    ev->callback = callback;
    ev->arg = arg;
    ev->flags = flags;
    return 0;
}

```

* 创建timer事件对象函数fpm_event_set_timer,

fd 值为-1,其他和fpm_event_set一致．


```c
#define fpm_event_set_timer(ev, flags, cb, arg) fpm_event_set((ev), -1, (flags), (cb), (arg))

```

* 添加事件．(fpm_event_add -> fpm_event_queue_add)

static int fpm_event_queue_add(struct fpm_event_queue_s **queue, struct fpm_event_s *ev)

简单的入列操作：

对于fd事件,需加到底层事件轮询机制里（如：epoll）．


```c
    if (*queue == fpm_event_queue_fd && module->add) {
        module->add(ev);
    }

```

４移除事件 (fpm_event_del -> fpm_event_queue_del)

简单的出列操作：

static int fpm_event_queue_del(struct fpm_event_queue_s **queue, struct fpm_event_s *ev)

对于fd事件,需在底层事件轮询机制里移除（如：epoll）

```c
    if (*queue == fpm_event_queue_fd && module->remove) {
        module->remove(ev);
    }

```

５,运行事件回调函数：

```c
void fpm_event_fire(struct fpm_event_s *ev) 
{
    if (!ev || !ev->callback) {
        return;
    }

    (*ev->callback)( (struct fpm_event_s *) ev, ev->which, ev->arg);    
}

```

６, 底层事件轮询模块结构

```c
struct fpm_event_module_s {
    const char *name;
    int support_edge_trigger;
    int (*init)(int max_fd);　／＊初始外化函数＊／
    int (*clean)(void);
    int (*wait)(struct fpm_event_queue_s *queue, unsigned long int timeout);
    int (*add)(struct fpm_event_s *ev);
    int (*remove)(struct fpm_event_s *ev);
};

```

不同的操作系统，支持不同的IO事件机制，linux 支持epoll,

windows支持select, freebsd 支持kqueue,这个结构统一操作接口

在函数fpm_event_init_main里 调用module->init初始化

fpm 里对应的配置

```c
events.mechanism = epoll

```
### 监控事件的无限循环

master进程在fpm_event_loop函数里无限循环，处理定时任务和fd事件.

期间会在module->wait阻塞片刻，对于epoll机制，就是epoll_wait．

```c
void fpm_event_loop(int err) /* {{{ */
{
    static struct fpm_event_s signal_fd_event;
    ...
    //添加信号处理fd事件
    fpm_event_set(&signal_fd_event, fpm_signals_get_fd(), FPM_EV_READ, &fpm_got_signal, NULL);
    fpm_event_add(&signal_fd_event, 0);

    //添加检查超时进程timer事件
    if (fpm_globals.heartbeat > 0) {
        fpm_pctl_heartbeat(NULL, 0, NULL);
    }

    if (!err) {
       //添加闲时服务维护timer事件
        fpm_pctl_perform_idle_server_maintenance_heartbeat(NULL, 0, NULL);
        ...
#ifdef HAVE_SYSTEMD
        //添加报告systemd timer事件
        fpm_systemd_heartbeat(NULL, 0, NULL);
#endif
    }

    while (1) {
        struct fpm_event_queue_s *q, *q2;
        struct timeval ms;
        struct timeval tmp;
        struct timeval now;
        unsigned long int timeout;　／＊这个timeout是等待事件，事件对象的timeout是标准时间点，同名不同义＊／
        int ret;
        ...

        fpm_clock_get(&now);
        timerclear(&ms);

        /＊timer时队列里查找应该运行的最近标准时间＊/
        q = fpm_event_queue_timer;
        while (q) {
            if (!timerisset(&ms)) {
                ms = q->ev->timeout;
            } else {
                if (timercmp(&q->ev->timeout, &ms, <)) {
                    ms = q->ev->timeout;
                }
            }
            q = q->next;
        }

        /* 没设置，默认１秒*/
        if (!timerisset(&ms) || timercmp(&ms, &now, <) || timercmp(&ms, &now, ==)) {
            timeout = 1000;
        } else {
        　/* 事件timeout值与当前时间相减，计算等待时间*/
            timersub(&ms, &now, &tmp);
            timeout = (tmp.tv_sec * 1000) + (tmp.tv_usec / 1000) + 1;
        }

        /* 程序阻塞在这里，设置阻塞timeout，是为了及时处理timer事件*/
        ret = module->wait(fpm_event_queue_fd, timeout);
        ．．．

        /* trigger timers */
        q = fpm_event_queue_timer;
        while (q) {
            fpm_clock_get(&now);
            if (q->ev) {
            　／＊　如果事件过期或到期，运行事件回调＊／
                if (timercmp(&now, &q->ev->timeout, >) || timercmp(&now, &q->ev->timeout, ==)) {
                    fpm_event_fire(q->ev);
                    ．．．
                    ／＊如果是连续运行timer事件
                        重设事件ev->timeout= ev->frequency＋now
                    ＊／
                    if (q->ev->flags & FPM_EV_PERSIST) {
                        fpm_event_set_timeout(q->ev, now);
                    } else { 
                        ／＊如果是运行一次的timer事件，移除队列＊／
                        q2 = q;
                        if (q->prev) {
                            q->prev->next = q->next;
                        }
                        if (q->next) {
                            q->next->prev = q->prev;
                        }
                        if (q == fpm_event_queue_timer) {
                            fpm_event_queue_timer = q->next;
                            if (fpm_event_queue_timer) {
                                fpm_event_queue_timer->prev = NULL;
                            }
                        }
                        q = q->next;
                        free(q2);
                        continue;
                    }
                }
            }
            q = q->next;
        }
    }
}

```


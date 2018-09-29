## PHP FPM源代码反刍品味之三: 多进程模型

2016.08.02 19:17*

来源：[https://www.jianshu.com/p/542935a3bfa8](https://www.jianshu.com/p/542935a3bfa8)

 本文开始会涉及写源代码, FPM源代码目录位于PHP源代码目录下的sapi/fpm 
### FPM多进程轮廓：

FPM大致的多进程模型就是：一个master进程,多个worker进程.

master进程负责管理调度，worker进程负责处理客户端(nginx)的请求.

master负责创建并监听(listen)网络连接，worker负责接受(accept)网络连接.

对于一个工作池，只有一个监听socket, 多个worker共用一个监听socket.

master进程与worker进程之间，通过信号(signals)和管道(pipe)通信．

FPM支持多个工作池(worker pool), FPM的工作池可以简单的理解为监听多个网络的多个FPM实例，只不过多个池都由一个master进程管理．

这里只考虑一个工作池的情况，理解了一个工作池，多个工作池也容易.
### fork()函数

Unix类操作系统通过fork调用新建子进程．

```c
int pid = fork();

```

fork函数，可以简单的理解为克隆一份进程，包含全局变量的复制．

父子进程几乎一模一样，是两个独立的进程，两个进程使用同一份代码．在fork之前运行的代码也一样．

两个进程之所以拥有不同的功能．主要就是在fork之后，父进程返回的子进程pid(大于零)，子进程返回的pid等于０.

重复一下，fork之后的代码也是相同的，由于返回的pid 不一样，依据条件判断，父子进程在fork之后所运行的代码块不一样．
 注：现在操作系统对fork进程复制做了性能优化，比如写时复制（copy-on-write ）,这是实现细节．说成进程克隆，是了便于理解 
### 守护进程(daemonize)

FPM 默认是以守护进程方式运行．

```ini
daemonize = yes

```
 配置为daemonize = no, 前台运行，有助于调试 

FPM启动后，有些创建守护进程常见的代码．如果只想专注了解fpm,守护进程这块代码可跳过．

由于FPM 默认是以守护进程方式运行，这里做个简单的介绍：

为了和控制台tty分离，fpm启动进程，会创建子进程（这个子进程就是后来的master进程）

启动进程创建一个管道pipe 用于和子进程通信，子进程完成初始化后，会通过这个管道给启动进程发消息，

启动进程收到消息后，简单处理后退出，由这个子进程负责后续工作．

平时，我们看到的 fpm master进程,其实是第一个子进程．

fpm 前台运行时(daemonize = no) ，没有这个fork的过程，启动进程就是master进程

文件fpm_main.c 里的main函数，是fpm服务启动入口，依次调用函数：

main -> fpm_init -> fpm_unix_init_main ,代码如下：

```c
//fpm_unix.c
if (fpm_global_config.daemonize) {
    ．．．
        if (pipe(fpm_globals.send_config_pipe) == -1) {
            zlog(ZLOG_SYSERROR, "failed to create pipe");
            return -1;
        }
        /* then fork */
        pid_t pid = fork();
　   ．．．
｝

```
### worker进程的创建

worker进程创建函数为fpm_children_make:

```c
//fpm_children.c
int fpm_children_make(struct fpm_worker_pool_s *wp, int in_event_loop, int nb_to_spawn, int is_debug) 
    pid_t pid;
    struct fpm_child_s *child;
    int max;
    static int warned = 0;
    //calculate max value
    ...
    while (fpm_pctl_can_spawn_children() && wp->running_children < max && (fpm_global_config.process_max < 1 || fpm_globals.running_children < fpm_global_config.process_max)) {
        warned = 0;
        child = fpm_resources_prepare(wp);
        if (!child) {
            return 2;
        }

        pid = fork();

        switch (pid) {
            case 0 :
                fpm_child_resources_use(child);
                fpm_globals.is_child = 1;
                fpm_child_init(wp);
                return 0;
            case -1 :
                fpm_resources_discard(child);
                return 2;

            default :
                child->pid = pid;
                fpm_clock_get(&child->started);
                fpm_parent_resources_use(child);
        }

    }
    ...
    return 1; 
}

```

依据fpm配置

```ini
pm = static 或 ondemand 或 dynamic

```

有三种创建worker进程的情况：

* static: 启动时创建：

main -> fpm_run -> fpm_children_create_initial ->  fpm_children_make
* ondemand: 按需创建，有请求才创建．

启动时，注册创建事件．事件的细节是：监听socket(listening_socket) 可读时：调用创建函数 fpm_pctl_on_socket_accept

main -> fpm_run -> fpm_children_create_initial


```c
//fpm_children.c
    if (wp->config->pm == PM_STYLE_ONDEMAND) {
        wp->ondemand_event = (struct fpm_event_s *)malloc(sizeof(struct fpm_event_s));
        ...
        memset(wp->ondemand_event, 0, sizeof(struct fpm_event_s));
        fpm_event_set(wp->ondemand_event, wp->listening_socket, FPM_EV_READ | FPM_EV_EDGE, fpm_pctl_on_socket_accept, wp);
        wp->socket_event_set = 1;
        fpm_event_add(wp->ondemand_event, 0);
        return 1;
    }

```

３,dynamic: 依据配置动态创建．

fpm_pctl_perform_idle_server_maintenance -> fpm_children_make

fpm_pctl_perform_idle_server_maintenance 会定时重复运行，依据配置创建worker进程

启动时这个逻辑会加到timer队列．

后面两个ondemand和dynamic是把创建逻辑加队列里，一个是IO事件，一个是timer队列．
 **`有条件触发`** ，有连接或是运行时间到．

两者都是在fpm_event_loop函数内部触发运行．

以上三种子进程创建方式的共同点是：都位于函数fpm_run内．
 **`fpm_run是fpm 多进程模型的关键节点`** ，

master进程会调用里面的fpm_event_loop,无限循环，不会返回fpm_run

worker进程会在fpm_run返回后，在后续的while语句无限循环．

```c
//fpm.c
int fpm_run(int *max_requests)
{
    struct fpm_worker_pool_s *wp;
    for (wp = fpm_worker_all_pools; wp; wp = wp->next) {
        int is_parent;

        is_parent = fpm_children_create_initial(wp);

        if (!is_parent) {
            goto run_child;
        }

        /* handle error */
        if (is_parent == 2) {
            fpm_pctl(FPM_PCTL_STATE_TERMINATING, FPM_PCTL_ACTION_SET);
            fpm_event_loop(1);
        }
    }

    /* run event loop forever */
    fpm_event_loop(0);

run_child: 
    fpm_cleanups_run(FPM_CLEANUP_CHILD);
    *max_requests = fpm_globals.max_requests;
    return fpm_globals.listening_socket;
}

```
### master进程无限循环

master进程无限循环fpm_event_loop，主要处理定时任务和IO事件．

这里内容较多，另文介绍．
### worker进程无限循环

worker进程无限循环，接受fast-cgi请求,交给PHP 解释引擎处理

```c
//fpm_main.c
//fcgi_accept_request 函数返回值小于0 时，循环退出。
while (fcgi_accept_request(&request) >= 0) {
    ...
　  //php解释引擎处理文件
    php_execute_script(&file_handle TSRMLS_CC);
    ...
}

```

```c
//fastcgi.c
int fcgi_accept_request(fcgi_request *req)
{
    while (1) {
    　  //fd>0 长链接，多个请求一个连接
        //fd<0 短链接，一个请求一个连接
        if (req->fd < 0) {
            while (1) {
                //in_shutdown 全局变量，优雅退出的一个开关．
                if (in_shutdown) {
                    return -1;
                }
                int listen_socket = req->listen_socket;
                FCGI_LOCK(req->listen_socket);
                req->fd = accept(listen_socket, (struct sockaddr *)&sa, &len);
                FCGI_UNLOCK(req->listen_socket);

            }
        }else if (in_shutdown) {
            return -1;
        }

        if (fcgi_read_request(req)) {
            return req->fd;
        } 
    }
}

```

空闲时：

对于长连接（少用），worker 进程会阻塞在fcgi_read_request里的read函数，等待请求．

对于短连接（常用），worker 进程会阻塞在accept函数，等待连接．
### 网络通信
#### master进程监听套接字(listen socket)的创建

以监听端口方式为例，函数调用过程

main

fpm_sockets_init_main

fpm_socket_af_inet_listening_socket

fpm_sockets_get_listening_socket

fpm_sockets_new_listening_socket

```c
//fpm_sockets.c
static int fpm_sockets_new_listening_socket(struct fpm_worker_pool_s *wp, struct sockaddr *sa, int socklen)
{
    int flags = 1;
    int sock;
    mode_t saved_umask = 0;

    sock = socket(sa->sa_family, SOCK_STREAM, 0);

    if (0 > setsockopt(sock, SOL_SOCKET, SO_REUSEADDR, &flags, sizeof(flags))) {
        zlog(ZLOG_WARNING, "failed to change socket attribute");
    }

    if (wp->listen_address_domain == FPM_AF_UNIX) {
        if (fpm_socket_unix_test_connect((struct sockaddr_un *)sa, socklen) == 0) {
            zlog(ZLOG_ERROR, "An another FPM instance seems to already listen on %s", ((struct sockaddr_un *) sa)->sun_path);
            close(sock);
            return -1;
        }
        unlink( ((struct sockaddr_un *) sa)->sun_path);
        saved_umask = umask(0777 ^ wp->socket_mode);
    }

    if (0 > bind(sock, sa, socklen)) {
        zlog(ZLOG_SYSERROR, "unable to bind listening socket for address '%s'", wp->config->listen_address);
        if (wp->listen_address_domain == FPM_AF_UNIX) {
            umask(saved_umask);
        }
        close(sock);
        return -1;
    }

    if (wp->listen_address_domain == FPM_AF_UNIX) {
        char *path = ((struct sockaddr_un *) sa)->sun_path;

        umask(saved_umask);

        if (0 > fpm_unix_set_socket_premissions(wp, path)) {
            close(sock);
            return -1;
        }
    }

    if (0 > listen(sock, wp->config->listen_backlog)) {
        zlog(ZLOG_SYSERROR, "failed to listen to address '%s'", wp->config->listen_address);
        close(sock);
        return -1;
    }

    return sock;
}

```
#### worker进程accept连接

对于worker进程，fpm_run返回监听套接字(listen socket)

```c
//fpm.c
int fpm_run(int *max_requests){
        ...
        return fpm_globals.listening_socket; //恒为０
}

```

这个返回的监听套接字，最后将传递给accept函数，等待连接．

当是，这个函数总是返回０，0号文件通常是标准输入，哪里不对？

原来0号文件被绑到了监听套接字上(dup2)．

```c
//fpm_stdio.c
int fpm_stdio_init_child(struct fpm_worker_pool_s *wp) 
{
　...
    if (wp->listening_socket != STDIN_FILENO) {
        if (0 > dup2(wp->listening_socket, STDIN_FILENO)) {
            zlog(ZLOG_SYSERROR, "failed to init child stdio: dup2()");
            return -1;
        }
    }
    return 0;
}

```

由于多个worker 共用一个监听套接字，这里accept前后加了加锁和解锁，避免惊群效应．

```c
//fastcgi.c
int fcgi_accept_request(fcgi_request *req)
{
        ...
        FCGI_LOCK(req->listen_socket);
        req->fd = accept(listen_socket, (struct sockaddr *)&sa, &len);
        FCGI_UNLOCK(req->listen_socket);
        ...
}

```

事实上，现在多数的操作unix类系统，这个加锁和解锁是不必要的，

操作系统内核已处理好了这个问题．

```c
//fastcgi.c
# ifdef USE_LOCKING
#  define FCGI_LOCK(fd)                             \
    do {                                            \
        struct flock lock;                          \
        lock.l_type = F_WRLCK;                      \
        lock.l_start = 0;                           \
        lock.l_whence = SEEK_SET;                   \
        lock.l_len = 0;                             \
        if (fcntl(fd, F_SETLKW, &lock) != -1) {     \
            break;                                  \
        } else if (errno != EINTR || in_shutdown) { \
            return -1;                              \
        }                                           \
    } while (1)
# else
#  define FCGI_LOCK(fd)
# endif

```

我们看到，如果没定义USE_LOCKING，FCGI_LOCK是空的，FCGI_UNLOCK类似．

而fpm 默认的编译配置就是没定义USE_LOCKING，所以accept 之前默认没加锁．

我们看到fpm的worker进程是阻塞的．FPM配置

```ini
events.mechanism = epoll

```

这个IO多路复用配置worker进程没用到．(master进程管理用到)．

Nginx和Tomcat一个worker可同时处理多个连接

FPM一个worker可同时只能处理一个个连接

这是PHP FPM 和 Nginx和Tomcat 的重大区别．


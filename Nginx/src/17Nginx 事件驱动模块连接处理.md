### 概述

由于 Nginx 工作在 master-worker 多进程模式，若所有 worker 进程在同一时间监听同一个端口，当该端口有新的连接事件出现时，每个worker 进程都会调用函数ngx_event_accept 试图与新的连接建立通信，即所有worker 进程都会被唤醒，这就是所谓的“惊群”问题，这样会导致系统性能下降。幸好在Nginx 采用了ngx_accept_mutex 同步锁机制，即只有获得该锁的worker 进程才能去处理新的连接事件，也就在同一时间只能有一个worker 进程监听某个端口。虽然这样做解决了“惊群”问题，但是随之会出现另一个问题，若每次出现的新连接事件都被同一个worker 进程获得锁的权利并处理该连接事件，这样会导致进程之间不均衡的状态，即在所有worker 进程中，某些进程处理的连接事件数量很庞大，而某些进程基本上不用处理连接事件，一直处于空闲状态。因此，这样会导致worker 进程之间的负载不均衡，会影响Nginx 的整体性能。为了解决负载失衡的问题，Nginx 在已经实现同步锁的基础上定义了负载阈值ngx_accept_disabled，当某个worker 进程的负载阈值大于 0 时，表示该进程处于负载超重的状态，则Nginx 会控制该进程，使其没机会试图与新的连接事件进行通信，这样就会为其他没有负载超重的进程创造了处理新连接事件的机会，以此达到进程间的负载均衡。

### 连接事件处理

新连接事件由函数 ngx_event_accept 处理。

    /* 处理新连接事件 */
    void
    ngx_event_accept(ngx_event_t *ev)
    {
        socklen_t          socklen;
        ngx_err_t          err;
        ngx_log_t         *log;
        ngx_uint_t         level;
        ngx_socket_t       s;
        ngx_event_t       *rev, *wev;
        ngx_listening_t   *ls;
        ngx_connection_t  *c, *lc;
        ngx_event_conf_t  *ecf;
        u_char             sa[NGX_SOCKADDRLEN];
    #if (NGX_HAVE_ACCEPT4)
        static ngx_uint_t  use_accept4 = 1;
    #endif
    
        if (ev->timedout) {
            if (ngx_enable_accept_events((ngx_cycle_t *) ngx_cycle) != NGX_OK) {
                return;
            }
    
            ev->timedout = 0;
        }
    
        /* 获取ngx_event_core_module模块的配置项参数结构 */
        ecf = ngx_event_get_conf(ngx_cycle->conf_ctx, ngx_event_core_module);
    
        if (ngx_event_flags & NGX_USE_RTSIG_EVENT) {
            ev->available = 1;
    
        } else if (!(ngx_event_flags & NGX_USE_KQUEUE_EVENT)) {
            ev->available = ecf->multi_accept;
        }
    
        lc = ev->data;/* 获取事件所对应的连接对象 */
        ls = lc->listening;/* 获取连接对象的监听端口数组 */
        ev->ready = 0;/* 设置事件的状态为未准备就绪 */
    
        ngx_log_debug2(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                       "accept on %V, ready: %d", &ls->addr_text, ev->available);
    
        do {
            socklen = NGX_SOCKADDRLEN;
    
            /* accept 建立一个新的连接 */
    #if (NGX_HAVE_ACCEPT4)
            if (use_accept4) {
                s = accept4(lc->fd, (struct sockaddr *) sa, &socklen,
                            SOCK_NONBLOCK);
            } else {
                s = accept(lc->fd, (struct sockaddr *) sa, &socklen);
            }
    #else
            s = accept(lc->fd, (struct sockaddr *) sa, &socklen);
    #endif
    
            /* 连接建立错误时的相应处理 */
            if (s == (ngx_socket_t) -1) {
                err = ngx_socket_errno;
    
                if (err == NGX_EAGAIN) {
                    ngx_log_debug0(NGX_LOG_DEBUG_EVENT, ev->log, err,
                                   "accept() not ready");
                    return;
                }
    
                level = NGX_LOG_ALERT;
    
                if (err == NGX_ECONNABORTED) {
                    level = NGX_LOG_ERR;
    
                } else if (err == NGX_EMFILE || err == NGX_ENFILE) {
                    level = NGX_LOG_CRIT;
                }
    
    #if (NGX_HAVE_ACCEPT4)
                ngx_log_error(level, ev->log, err,
                              use_accept4 ? "accept4() failed" : "accept() failed");
    
                if (use_accept4 && err == NGX_ENOSYS) {
                    use_accept4 = 0;
                    ngx_inherited_nonblocking = 0;
                    continue;
                }
    #else
                ngx_log_error(level, ev->log, err, "accept() failed");
    #endif
    
                if (err == NGX_ECONNABORTED) {
                    if (ngx_event_flags & NGX_USE_KQUEUE_EVENT) {
                        ev->available--;
                    }
    
                    if (ev->available) {
                        continue;
                    }
                }
    
                if (err == NGX_EMFILE || err == NGX_ENFILE) {
                    if (ngx_disable_accept_events((ngx_cycle_t *) ngx_cycle)
                        != NGX_OK)
                    {
                        return;
                    }
    
                    if (ngx_use_accept_mutex) {
                        if (ngx_accept_mutex_held) {
                            ngx_shmtx_unlock(&ngx_accept_mutex);
                            ngx_accept_mutex_held = 0;
                        }
    
                        ngx_accept_disabled = 1;
    
                    } else {
                        ngx_add_timer(ev, ecf->accept_mutex_delay);
                    }
                }
    
                return;
            }
    
    #if (NGX_STAT_STUB)
            (void) ngx_atomic_fetch_add(ngx_stat_accepted, 1);
    #endif
    
            /*
             * ngx_accept_disabled 变量是负载均衡阈值，表示进程是否超载；
             * 设置负载均衡阈值为每个进程最大连接数的八分之一减去空闲连接数；
             * 即当每个进程accept到的活动连接数超过最大连接数的7/8时，
             * ngx_accept_disabled 大于0，表示该进程处于负载过重；
             */
            ngx_accept_disabled = ngx_cycle->connection_n / 8
                                  - ngx_cycle->free_connection_n;
    
            /* 从connections数组中获取一个connection连接来维护新的连接 */
            c = ngx_get_connection(s, ev->log);
    
            if (c == NULL) {
                if (ngx_close_socket(s) == -1) {
                    ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_socket_errno,
                                  ngx_close_socket_n " failed");
                }
    
                return;
            }
    
    #if (NGX_STAT_STUB)
            (void) ngx_atomic_fetch_add(ngx_stat_active, 1);
    #endif
    
            /* 为新的连接创建一个连接池pool，直到关闭该连接时才释放该连接池pool */
            c->pool = ngx_create_pool(ls->pool_size, ev->log);
            if (c->pool == NULL) {
                ngx_close_accepted_connection(c);
                return;
            }
    
            c->sockaddr = ngx_palloc(c->pool, socklen);
            if (c->sockaddr == NULL) {
                ngx_close_accepted_connection(c);
                return;
            }
    
            ngx_memcpy(c->sockaddr, sa, socklen);
    
            log = ngx_palloc(c->pool, sizeof(ngx_log_t));
            if (log == NULL) {
                ngx_close_accepted_connection(c);
                return;
            }
    
            /* set a blocking mode for aio and non-blocking mode for others */
    
            /* 设置套接字的属性 */
            if (ngx_inherited_nonblocking) {
                if (ngx_event_flags & NGX_USE_AIO_EVENT) {
                    if (ngx_blocking(s) == -1) {
                        ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_socket_errno,
                                      ngx_blocking_n " failed");
                        ngx_close_accepted_connection(c);
                        return;
                    }
                }
    
            } else {
                /* 使用epoll模型时，套接字的属性为非阻塞模式 */
                if (!(ngx_event_flags & (NGX_USE_AIO_EVENT|NGX_USE_RTSIG_EVENT))) {
                    if (ngx_nonblocking(s) == -1) {
                        ngx_log_error(NGX_LOG_ALERT, ev->log, ngx_socket_errno,
                                      ngx_nonblocking_n " failed");
                        ngx_close_accepted_connection(c);
                        return;
                    }
                }
            }
    
            *log = ls->log;
    
            /* 初始化新连接 */
            c->recv = ngx_recv;
            c->send = ngx_send;
            c->recv_chain = ngx_recv_chain;
            c->send_chain = ngx_send_chain;
    
            c->log = log;
            c->pool->log = log;
    
            c->socklen = socklen;
            c->listening = ls;
            c->local_sockaddr = ls->sockaddr;
            c->local_socklen = ls->socklen;
    
            c->unexpected_eof = 1;
    
    #if (NGX_HAVE_UNIX_DOMAIN)
            if (c->sockaddr->sa_family == AF_UNIX) {
                c->tcp_nopush = NGX_TCP_NOPUSH_DISABLED;
                c->tcp_nodelay = NGX_TCP_NODELAY_DISABLED;
    #if (NGX_SOLARIS)
                /* Solaris's sendfilev() supports AF_NCA, AF_INET, and AF_INET6 */
                c->sendfile = 0;
    #endif
            }
    #endif
    
            /* 获取新连接的读事件、写事件 */
            rev = c->read;
            wev = c->write;
    
            /* 写事件准备就绪 */
            wev->ready = 1;
    
            if (ngx_event_flags & (NGX_USE_AIO_EVENT|NGX_USE_RTSIG_EVENT)) {
                /* rtsig, aio, iocp */
                rev->ready = 1;
            }
    
            if (ev->deferred_accept) {
                rev->ready = 1;
    #if (NGX_HAVE_KQUEUE)
                rev->available = 1;
    #endif
            }
    
            rev->log = log;
            wev->log = log;
    
            /*
             * TODO: MT: - ngx_atomic_fetch_add()
             *             or protection by critical section or light mutex
             *
             * TODO: MP: - allocated in a shared memory
             *           - ngx_atomic_fetch_add()
             *             or protection by critical section or light mutex
             */
    
            c->number = ngx_atomic_fetch_add(ngx_connection_counter, 1);
    
    #if (NGX_STAT_STUB)
            (void) ngx_atomic_fetch_add(ngx_stat_handled, 1);
    #endif
    
    #if (NGX_THREADS)
            rev->lock = &c->lock;
            wev->lock = &c->lock;
            rev->own_lock = &c->lock;
            wev->own_lock = &c->lock;
    #endif
    
            if (ls->addr_ntop) {
                c->addr_text.data = ngx_pnalloc(c->pool, ls->addr_text_max_len);
                if (c->addr_text.data == NULL) {
                    ngx_close_accepted_connection(c);
                    return;
                }
    
                c->addr_text.len = ngx_sock_ntop(c->sockaddr, c->socklen,
                                                 c->addr_text.data,
                                                 ls->addr_text_max_len, 0);
                if (c->addr_text.len == 0) {
                    ngx_close_accepted_connection(c);
                    return;
                }
            }
    
    #if (NGX_DEBUG)
            {
    
            struct sockaddr_in   *sin;
            ngx_cidr_t           *cidr;
            ngx_uint_t            i;
    #if (NGX_HAVE_INET6)
            struct sockaddr_in6  *sin6;
            ngx_uint_t            n;
    #endif
    
            cidr = ecf->debug_connection.elts;
            for (i = 0; i < ecf->debug_connection.nelts; i++) {
                if (cidr[i].family != (ngx_uint_t) c->sockaddr->sa_family) {
                    goto next;
                }
    
                switch (cidr[i].family) {
    
    #if (NGX_HAVE_INET6)
                case AF_INET6:
                    sin6 = (struct sockaddr_in6 *) c->sockaddr;
                    for (n = 0; n < 16; n++) {
                        if ((sin6->sin6_addr.s6_addr[n]
                            & cidr[i].u.in6.mask.s6_addr[n])
                            != cidr[i].u.in6.addr.s6_addr[n])
                        {
                            goto next;
                        }
                    }
                    break;
    #endif
    
    #if (NGX_HAVE_UNIX_DOMAIN)
                case AF_UNIX:
                    break;
    #endif
    
                default: /* AF_INET */
                    sin = (struct sockaddr_in *) c->sockaddr;
                    if ((sin->sin_addr.s_addr & cidr[i].u.in.mask)
                        != cidr[i].u.in.addr)
                    {
                        goto next;
                    }
                    break;
                }
    
                log->log_level = NGX_LOG_DEBUG_CONNECTION|NGX_LOG_DEBUG_ALL;
                break;
    
            next:
                continue;
            }
    
            }
    #endif
    
            ngx_log_debug3(NGX_LOG_DEBUG_EVENT, log, 0,
                           "*%uA accept: %V fd:%d", c->number, &c->addr_text, s);
    
            /* 将新连接对应的读事件注册到事件监控机制中；
             * 注意：若是epoll事件机制，这里是不会执行，
             * 因为epoll事件机制会在调用新连接处理函数ls->handler(c)
             *（实际调用ngx_http_init_connection）时，才会把新连接对应的读事件注册到epoll事件机制中；
             */
            if (ngx_add_conn && (ngx_event_flags & NGX_USE_EPOLL_EVENT) == 0) {
                if (ngx_add_conn(c) == NGX_ERROR) {
                    ngx_close_accepted_connection(c);
                    return;
                }
            }
    
            log->data = NULL;
            log->handler = NULL;
    
            /*
             * 设置回调函数，完成新连接的最后初始化工作，
             * 由函数ngx_http_init_connection完成
             */
            ls->handler(c);
    
            /* 调整事件available标志位，该标志位为1表示Nginx一次尽可能多建立新连接 */
            if (ngx_event_flags & NGX_USE_KQUEUE_EVENT) {
                ev->available--;
            }
    
        } while (ev->available);
    }
    
    /* 将监听socket连接的读事件加入到监听事件中 */
    static ngx_int_t
    ngx_enable_accept_events(ngx_cycle_t *cycle)
    {
        ngx_uint_t         i;
        ngx_listening_t   *ls;
        ngx_connection_t  *c;
    
        /* 获取监听数组的首地址 */
        ls = cycle->listening.elts;
        /* 遍历整个监听数组 */
        for (i = 0; i < cycle->listening.nelts; i++) {
    
            /* 获取当前监听socket所对应的连接 */
            c = ls[i].connection;
    
            /* 当前连接的读事件是否处于active活跃状态 */
            if (c->read->active) {
                /* 若是处于active状态，表示该连接的读事件已经在事件监控对象中 */
                continue;
            }
    
            /* 若当前连接没有加入到事件监控对象中，则将该链接注册到事件监控中 */
            if (ngx_event_flags & NGX_USE_RTSIG_EVENT) {
    
                if (ngx_add_conn(c) == NGX_ERROR) {
                    return NGX_ERROR;
                }
    
            } else {
                /* 若当前连接的读事件不在事件监控对象中，则将其加入 */
                if (ngx_add_event(c->read, NGX_READ_EVENT, 0) == NGX_ERROR) {
                    return NGX_ERROR;
                }
            }
        }
    
        return NGX_OK;
    }
    
    /* 将监听连接的读事件从事件驱动模块中删除 */
    static ngx_int_t
    ngx_disable_accept_events(ngx_cycle_t *cycle)
    {
        ngx_uint_t         i;
        ngx_listening_t   *ls;
        ngx_connection_t  *c;
    
        /* 获取监听接口 */
        ls = cycle->listening.elts;
        for (i = 0; i < cycle->listening.nelts; i++) {
    
            /* 获取监听接口对应的连接 */
            c = ls[i].connection;
    
            if (!c->read->active) {
                continue;
            }
    
            /* 从事件驱动模块中移除连接 */
            if (ngx_event_flags & NGX_USE_RTSIG_EVENT) {
                if (ngx_del_conn(c, NGX_DISABLE_EVENT) == NGX_ERROR) {
                    return NGX_ERROR;
                }
    
            } else {
                /* 从事件驱动模块移除连接的读事件 */
                if (ngx_del_event(c->read, NGX_READ_EVENT, NGX_DISABLE_EVENT)
                    == NGX_ERROR)
                {
                    return NGX_ERROR;
                }
            }
        }
    
        return NGX_OK;
    }
    
    

当出现新连接事件时，只有获得同步锁的进程才可以处理该连接事件，避免了“惊群”问题，进程试图处理新连接事件由函数 ngx_trylock_accept_mutex 实现。

    /* 试图处理监听端口的新连接事件 */
    ngx_int_t
    ngx_trylock_accept_mutex(ngx_cycle_t *cycle)
    {
        /* 获取ngx_accept_mutex锁，成功返回1，失败返回0 */
        if (ngx_shmtx_trylock(&ngx_accept_mutex)) {
    
            ngx_log_debug0(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                           "accept mutex locked");
    
            /*
             * 标志位ngx_accept_mutex_held为1表示当前进程已经获取了ngx_accept_mutex锁；
             * 满足下面条件时，表示当前进程在之前已经获得ngx_accept_mutex锁；
             * 则直接返回；
             */
            if (ngx_accept_mutex_held
                && ngx_accept_events == 0
                && !(ngx_event_flags & NGX_USE_RTSIG_EVENT))
            {
                return NGX_OK;
            }
    
            /* 将所有监听连接的读事件添加到当前的epoll事件驱动模块中 */
            if (ngx_enable_accept_events(cycle) == NGX_ERROR) {
                /* 若添加失败，则释放该锁 */
                ngx_shmtx_unlock(&ngx_accept_mutex);
                return NGX_ERROR;
            }
    
            /* 设置当前进程获取锁的情况 */
            ngx_accept_events = 0;
            ngx_accept_mutex_held = 1;/* 表示当前进程已经得到ngx_accept_mutex锁 */
    
            return NGX_OK;
        }
    
        ngx_log_debug1(NGX_LOG_DEBUG_EVENT, cycle->log, 0,
                       "accept mutex lock failed: %ui", ngx_accept_mutex_held);
    
        /*
         * 若当前进程获取ngx_accept_mutex锁失败，并且ngx_accept_mutex_held为1，
         * 此时是错误情况
         */
        if (ngx_accept_mutex_held) {
            /* 将所有监听连接的读事件从事件驱动模块中移除 */
            if (ngx_disable_accept_events(cycle) == NGX_ERROR) {
                return NGX_ERROR;
            }
    
            ngx_accept_mutex_held = 0;
        }
    
        return NGX_OK;
    }
    
    

Nginx 通过负载阈值 ngx_accept_disabled 控制进程是否处理新连接事件，避免进程间负载均衡问题。

    if(ngx_accept_disabled > 0){
       ngx_accept_disabled --;
    }else{
      if(ngx_trylock_accept_mutex(cycle) == NGX_ERROR){
            return;
       }
    ...
    }
    
    

参考资料：

《深入理解Nginx》

《深入剖析Nginx》

《[Nginx源码分析-事件循环][0]》

[0]: http://www.alidata.org/archives/1267
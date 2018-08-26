### 概述

       在 Nginx 中定时器事件的实现与内核无关。在事件模块中，当等待的事件不能在指定的时间内到达，则会触发Nginx 的超时机制，超时机制会对发生超时的事件进行管理，并对这些超时事件作出处理。对于定时事件的管理包括两方面：定时事件对象的组织形式 和 定时事件对象的超时检测。

### 定时事件的组织

       Nginx 的定时器由红黑树实现的。在保存事件的结构体ngx_event_t 中有三个关于时间管理的成员，如下所示：

```c
struct ngx_event_s{
    ...
    /* 标志位，为1表示当前事件已超时 */  
    unsigned         timedout:1;  
    /* 标志位，为1表示当前事件存在于由红黑树维护的定时器中 */  
    unsigned         timer_set:1;  
    /* 由红黑树维护的定时器 */  
    ngx_rbtree_node_t   timer; 
    ...
};

```

       Nginx 设置两个关于定时器的全局变量。在文件[src/event/ngx_event_timer.c](http://lxr.nginx.org/source/src/event/ngx_event_timer.c)中定义：

```c
/* 所有定时器事件组成的红黑树 */
ngx_thread_volatile ngx_rbtree_t  ngx_event_timer_rbtree;
/* 红黑树的哨兵节点 */
static ngx_rbtree_node_t          ngx_event_timer_sentinel;

```

       这棵红黑树的每一个节点代表一个事件 ngx_event_t 结构体中的成员timer，ngx_rbtree_node_t 节点代表事件的超时时间，以这个超时时间的大小组成的红黑树ngx_event_timer_rbtree，则该红黑树中最左边的节点代表最可能超时的事件。

       定时器事件初始化实际上调用红黑树的初始化，其在文件 [src/event/ngx_event_timer.c](http://lxr.nginx.org/source/src/event/ngx_event_timer.c)中定义：

```c
/* 定时器事件初始化 */
ngx_int_t
ngx_event_timer_init(ngx_log_t *log)
{
    /* 初始化红黑树 */
    ngx_rbtree_init(&amp;ngx_event_timer_rbtree, &amp;ngx_event_timer_sentinel,
                    ngx_rbtree_insert_timer_value);

    /* 下面是针对多线程环境 */
#if (NGX_THREADS)

    if (ngx_event_timer_mutex) {
        ngx_event_timer_mutex->log = log;
        return NGX_OK;
    }

    ngx_event_timer_mutex = ngx_mutex_init(log, 0);
    if (ngx_event_timer_mutex == NULL) {
        return NGX_ERROR;
    }

#endif

    return NGX_OK;
}

```

### 定时事件的超时检测

       当需要对某个事件进行超时检测时，只需要将该事件添加到定时器红黑树中即可，由函数 ngx_event_add_timer，将一个事件从定时器红黑树中删除由函数 ngx_event_del_timer 实现。以下的函数都在文件 [src/event/ngx_event_timer.h](http://lxr.nginx.org/source/src/event/ngx_event_timer.h)中定义：

```c
/* 从定时器中移除事件 */
static ngx_inline void
ngx_event_del_timer(ngx_event_t *ev)
{
    ngx_log_debug2(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                   "event timer del: %d: %M",
                    ngx_event_ident(ev->data), ev->timer.key);

    ngx_mutex_lock(ngx_event_timer_mutex);

    /* 从红黑树中移除指定事件的节点对象 */
    ngx_rbtree_delete(&amp;ngx_event_timer_rbtree, &amp;ev->timer);

    ngx_mutex_unlock(ngx_event_timer_mutex);

#if (NGX_DEBUG)
    ev->timer.left = NULL;
    ev->timer.right = NULL;
    ev->timer.parent = NULL;
#endif

    /* 设置相应的标志位 */
    ev->timer_set = 0;
}

/* 将事件添加到定时器中 */
static ngx_inline void
ngx_event_add_timer(ngx_event_t *ev, ngx_msec_t timer)
{
    ngx_msec_t      key;
    ngx_msec_int_t  diff;

    /* 设置事件对象节点的键值 */
    key = ngx_current_msec + timer;

    /* 判断事件的相应标志位 */
    if (ev->timer_set) {

        /*
         * Use a previous timer value if difference between it and a new
         * value is less than NGX_TIMER_LAZY_DELAY milliseconds: this allows
         * to minimize the rbtree operations for fast connections.
         */

        diff = (ngx_msec_int_t) (key - ev->timer.key);

        if (ngx_abs(diff) < NGX_TIMER_LAZY_DELAY) {
            ngx_log_debug3(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                           "event timer: %d, old: %M, new: %M",
                            ngx_event_ident(ev->data), ev->timer.key, key);
            return;
        }

        ngx_del_timer(ev);
    }

    ev->timer.key = key;

    ngx_log_debug3(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                   "event timer add: %d: %M:%M",
                    ngx_event_ident(ev->data), timer, ev->timer.key);

    ngx_mutex_lock(ngx_event_timer_mutex);

    /* 将事件对象节点插入到红黑树中 */
    ngx_rbtree_insert(&amp;ngx_event_timer_rbtree, &amp;ev->timer);

    ngx_mutex_unlock(ngx_event_timer_mutex);

    /* 设置标志位 */
    ev->timer_set = 1;
}

```

       判断一个函数是否超时由函数 ngx_event_find_timer 实现，检查定时器所有事件由函数ngx_event_expire_timer 实现。以下的函数都在文件[src/event/ngx_event_timer.c](http://lxr.nginx.org/source/src/event/ngx_event_timer.c)中定义：

```c
/* 找出定时器红黑树最左边的节点 */
ngx_msec_t
ngx_event_find_timer(void)
{
    ngx_msec_int_t      timer;
    ngx_rbtree_node_t  *node, *root, *sentinel;

    /* 若红黑树为空 */
    if (ngx_event_timer_rbtree.root == &amp;ngx_event_timer_sentinel) {
        return NGX_TIMER_INFINITE;
    }

    ngx_mutex_lock(ngx_event_timer_mutex);

    root = ngx_event_timer_rbtree.root;
    sentinel = ngx_event_timer_rbtree.sentinel;

    /* 找出红黑树最小的节点，即最左边的节点 */
    node = ngx_rbtree_min(root, sentinel);

    ngx_mutex_unlock(ngx_event_timer_mutex);

    /* 计算最左节点键值与当前时间的差值timer，当timer大于0表示不超时，不大于0表示超时 */
    timer = (ngx_msec_int_t) (node->key - ngx_current_msec);

    /*
     * 若timer大于0，则事件不超时，返回该值；
     * 若timer不大于0，则事件超时，返回0，标志触发超时事件；
     */
    return (ngx_msec_t) (timer > 0 ? timer : 0);
}

/* 检查定时器中所有事件 */
void
ngx_event_expire_timers(void)
{
    ngx_event_t        *ev;
    ngx_rbtree_node_t  *node, *root, *sentinel;

    sentinel = ngx_event_timer_rbtree.sentinel;

    /* 循环检查 */
    for ( ;; ) {

        ngx_mutex_lock(ngx_event_timer_mutex);

        root = ngx_event_timer_rbtree.root;

        /* 若定时器红黑树为空，则直接返回，不做任何处理 */
        if (root == sentinel) {
            return;
        }

        /* 找出定时器红黑树最左边的节点，即最小的节点，同时也是最有可能超时的事件对象 */
        node = ngx_rbtree_min(root, sentinel);

        /* node->key <= ngx_current_time */

        /* 若检查到的当前事件已超时 */
        if ((ngx_msec_int_t) (node->key - ngx_current_msec) <= 0) {
            /* 获取超时的具体事件 */
            ev = (ngx_event_t *) ((char *) node - offsetof(ngx_event_t, timer));

            /* 下面是针对多线程 */
#if (NGX_THREADS)

            if (ngx_threaded &amp;&amp; ngx_trylock(ev->lock) == 0) {

                /*
                 * We cannot change the timer of the event that is being
                 * handled by another thread.  And we cannot easy walk
                 * the rbtree to find next expired timer so we exit the loop.
                 * However, it should be a rare case when the event that is
                 * being handled has an expired timer.
                 */

                ngx_log_debug1(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                               "event %p is busy in expire timers", ev);
                break;
            }
#endif

            ngx_log_debug2(NGX_LOG_DEBUG_EVENT, ev->log, 0,
                           "event timer del: %d: %M",
                           ngx_event_ident(ev->data), ev->timer.key);

            /* 将已超时事件对象从现有定时器红黑树中移除 */
            ngx_rbtree_delete(&amp;ngx_event_timer_rbtree, &amp;ev->timer);

            ngx_mutex_unlock(ngx_event_timer_mutex);

#if (NGX_DEBUG)
            ev->timer.left = NULL;
            ev->timer.right = NULL;
            ev->timer.parent = NULL;
#endif

            /* 设置事件的在定时器红黑树中的监控标志位 */
            ev->timer_set = 0;/* 0表示不受监控 */

            /* 多线程环境 */
#if (NGX_THREADS)
            if (ngx_threaded) {
                ev->posted_timedout = 1;

                ngx_post_event(ev, &amp;ngx_posted_events);

                ngx_unlock(ev->lock);

                continue;
            }
#endif

            /* 设置事件的超时标志位 */
            ev->timedout = 1;/* 1表示已经超时 */

            /* 调用已超时事件的处理函数对该事件进行处理 */
            ev->handler(ev);

            continue;
        }

        break;
    }

    ngx_mutex_unlock(ngx_event_timer_mutex);
}

```

参考资料

《深入剖析Nginx》

《深入理解Nginx》
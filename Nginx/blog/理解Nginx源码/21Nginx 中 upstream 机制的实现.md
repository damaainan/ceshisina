### 概述

       upstream 机制使得 Nginx 成为一个反向代理服务器，Nginx 接收来自下游客户端的 http 请求，并处理该请求，同时根据该请求向上游服务器发送 tcp 请求报文，上游服务器会根据该请求返回相应地响应报文，Nginx 根据上游服务器的响应报文，决定是否向下游客户端转发响应报文。另外 upstream 机制提供了负载均衡的功能，可以将请求负载均衡到集群服务器的某个服务器上面。

### 启动 upstream

       在 Nginx 中调用 ngx_http_upstream_init 方法启动 upstream 机制，但是在使用 upstream 机制之前必须调用 ngx_http_upstream_create 方法创建 ngx_http_upstream_t 结构体，因为默认情况下 ngx_http_request_t 结构体中的 upstream 成员是指向 NULL，该结构体的具体初始化工作还需由 HTTP 模块完成。有关 ngx_http_upstream_t 结构体 和ngx_http_upstream_conf_t 结构体的相关说明可参考文章《[Nginx 中 upstream 机制](http://blog.csdn.net/chenhanzhun/article/details/42680343)》。

      下面是函数 ngx_http_upstream_create 的实现：

```c
/* 创建 ngx_http_upstream_t 结构体 */
ngx_int_t
ngx_http_upstream_create(ngx_http_request_t *r)
{
    ngx_http_upstream_t  *u;

    u = r->upstream;

    /*
     * 若已经创建过ngx_http_upstream_t 且定义了cleanup成员，
     * 则调用cleanup清理方法将原始结构体清除；
     */
    if (u &amp;&amp; u->cleanup) {
        r->main->count++;
        ngx_http_upstream_cleanup(r);
    }

    /* 从内存池分配ngx_http_upstream_t 结构体空间 */
    u = ngx_pcalloc(r->pool, sizeof(ngx_http_upstream_t));
    if (u == NULL) {
        return NGX_ERROR;
    }

    /* 给ngx_http_request_t 结构体成员upstream赋值 */
    r->upstream = u;

    u->peer.log = r->connection->log;
    u->peer.log_error = NGX_ERROR_ERR;
#if (NGX_THREADS)
    u->peer.lock = &amp;r->connection->lock;
#endif

#if (NGX_HTTP_CACHE)
    r->cache = NULL;
#endif

    u->headers_in.content_length_n = -1;

    return NGX_OK;
}
```

关于 upstream 机制的启动方法 ngx_http_upstream_init 的执行流程如下：

- 检查 Nginx 与下游服务器之间连接上的读事件是否在定时器中，即检查 timer_set 标志位是否为 1，若该标志位为 1，则把读事件从定时器中移除；
- 调用 ngx_http_upstream_init_request 方法启动 upstream 机制；

ngx_http_upstream_init_request 方法执行流程如下所示：

- 检查 ngx_http_upstream_t 结构体中的 store 标志位是否为 0；检查 ngx_http_request_t 结构体中的 post_action 标志位是否为0；检查 ngx_http_upstream_conf_t 结构体中的ignore_client_abort 是否为 0；若上面的标志位都为 0，则设置ngx_http_request_t 请求的读事件的回调方法为ngx_http_upstream_rd_check_broken_connection；设置写事件的回调方法为 ngx_http_upstream_wr_check_broken_connection；这两个方法都会调用 ngx_http_upstream_check_broken_connection方法检查 Nginx 与下游之间的连接是否正常，若出现错误，则终止连接；
- 若不满足上面的标志位，即至少有一个不为 0 ，调用请求中ngx_http_upstream_t 结构体中某个 HTTP 模块实现的create_request 方法，构造发往上游服务器的请求；
- 调用 ngx_http_cleanup_add 方法向原始请求的 cleanup 链表尾端添加一个回调 handler 方法，该回调方法设置为ngx_http_upstream_cleanup，若当前请求结束时会调用该方法做一些清理工作；
- 调用 ngx_http_upstream_connect 方法向上游服务器发起连接请求；

```c
/* 初始化启动upstream机制 */
void
ngx_http_upstream_init(ngx_http_request_t *r)
{
    ngx_connection_t     *c;

    /* 获取当前请求所对应的连接 */
    c = r->connection;

    ngx_log_debug1(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http init upstream, client timer: %d", c->read->timer_set);

#if (NGX_HTTP_SPDY)
    if (r->spdy_stream) {
        ngx_http_upstream_init_request(r);
        return;
    }
#endif

    /*
     * 检查当前连接上读事件的timer_set标志位是否为1，若该标志位为1，
     * 表示读事件在定时器机制中，则需要把它从定时器机制中移除；
     * 因为在启动upstream机制后，就不需要对客户端的读操作进行超时管理；
     */
    if (c->read->timer_set) {
        ngx_del_timer(c->read);
    }

    if (ngx_event_flags &amp; NGX_USE_CLEAR_EVENT) {

        if (!c->write->active) {
            if (ngx_add_event(c->write, NGX_WRITE_EVENT, NGX_CLEAR_EVENT)
                == NGX_ERROR)
            {
                ngx_http_finalize_request(r, NGX_HTTP_INTERNAL_SERVER_ERROR);
                return;
            }
        }
    }

    ngx_http_upstream_init_request(r);
}
```

```c
static void
ngx_http_upstream_init_request(ngx_http_request_t *r)
{
    ngx_str_t                      *host;
    ngx_uint_t                      i;
    ngx_resolver_ctx_t             *ctx, temp;
    ngx_http_cleanup_t             *cln;
    ngx_http_upstream_t            *u;
    ngx_http_core_loc_conf_t       *clcf;
    ngx_http_upstream_srv_conf_t   *uscf, **uscfp;
    ngx_http_upstream_main_conf_t  *umcf;

    if (r->aio) {
        return;
    }

    u = r->upstream;

#if (NGX_HTTP_CACHE)
    ...
    ...
#endif

    /* 文件缓存标志位 */
    u->store = (u->conf->store || u->conf->store_lengths);

    /*
     * 检查ngx_http_upstream_t 结构中标志位 store；
     * 检查ngx_http_request_t 结构中标志位 post_action；
     * 检查ngx_http_upstream_conf_t 结构中标志位 ignore_client_abort；
     * 若上面这些标志位为1，则表示需要检查Nginx与下游(即客户端)之间的TCP连接是否断开；
     */
    if (!u->store &amp;&amp; !r->post_action &amp;&amp; !u->conf->ignore_client_abort) {
        r->read_event_handler = ngx_http_upstream_rd_check_broken_connection;
        r->write_event_handler = ngx_http_upstream_wr_check_broken_connection;
    }

    /* 把当前请求包体结构保存在ngx_http_upstream_t 结构的request_bufs链表缓冲区中 */
    if (r->request_body) {
        u->request_bufs = r->request_body->bufs;
    }

    /* 调用create_request方法构造发往上游服务器的请求 */
    if (u->create_request(r) != NGX_OK) {
        ngx_http_finalize_request(r, NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    /* 获取ngx_http_upstream_t结构中主动连接结构peer的local本地地址信息 */
    u->peer.local = ngx_http_upstream_get_local(r, u->conf->local);

    /* 获取ngx_http_core_module模块的loc级别的配置项结构 */
    clcf = ngx_http_get_module_loc_conf(r, ngx_http_core_module);

    /* 初始化ngx_http_upstream_t结构中成员output向下游发送响应的方式 */
    u->output.alignment = clcf->directio_alignment;
    u->output.pool = r->pool;
    u->output.bufs.num = 1;
    u->output.bufs.size = clcf->client_body_buffer_size;
    u->output.output_filter = ngx_chain_writer;
    u->output.filter_ctx = &amp;u->writer;

    u->writer.pool = r->pool;

    /* 添加用于表示上游响应的状态，例如：错误编码、包体长度等 */
    if (r->upstream_states == NULL) {

        r->upstream_states = ngx_array_create(r->pool, 1,
                                            sizeof(ngx_http_upstream_state_t));
        if (r->upstream_states == NULL) {
            ngx_http_finalize_request(r, NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

    } else {

        u->state = ngx_array_push(r->upstream_states);
        if (u->state == NULL) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        ngx_memzero(u->state, sizeof(ngx_http_upstream_state_t));
    }

    /*
     * 调用ngx_http_cleanup_add方法原始请求的cleanup链表尾端添加一个回调handler方法，
     * 该handler回调方法设置为ngx_http_upstream_cleanup，若当前请求结束时会调用该方法做一些清理工作；
     */
    cln = ngx_http_cleanup_add(r, 0);
    if (cln == NULL) {
        ngx_http_finalize_request(r, NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    cln->handler = ngx_http_upstream_cleanup;
    cln->data = r;
    u->cleanup = &amp;cln->handler;

    if (u->resolved == NULL) {

        /* 若没有实现u->resolved标志位，则定义上游服务器的配置 */
        uscf = u->conf->upstream;

    } else {

        /*
         * 若实现了u->resolved标志位，则解析主机域名，指定上游服务器的地址；
         */

        /*
         * 若已经指定了上游服务器地址，则不需要解析，
         * 直接调用ngx_http_upstream_connection方法向上游服务器发起连接；
         * 并return从当前函数返回；
         */
        if (u->resolved->sockaddr) {

            if (ngx_http_upstream_create_round_robin_peer(r, u->resolved)
                != NGX_OK)
            {
                ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
                return;
            }

            ngx_http_upstream_connect(r, u);

            return;
        }

        /*
         * 若还没指定上游服务器的地址，则需解析主机域名；
         * 若成功解析出上游服务器的地址和端口号，
         * 则调用ngx_http_upstream_connection方法向上游服务器发起连接；
         */
        host = &amp;u->resolved->host;

        umcf = ngx_http_get_module_main_conf(r, ngx_http_upstream_module);

        uscfp = umcf->upstreams.elts;

        for (i = 0; i < umcf->upstreams.nelts; i++) {

            uscf = uscfp[i];

            if (uscf->host.len == host->len
                &amp;&amp; ((uscf->port == 0 &amp;&amp; u->resolved->no_port)
                     || uscf->port == u->resolved->port)
                &amp;&amp; ngx_strncasecmp(uscf->host.data, host->data, host->len) == 0)
            {
                goto found;
            }
        }

        if (u->resolved->port == 0) {
            ngx_log_error(NGX_LOG_ERR, r->connection->log, 0,
                          "no port in upstream \"%V\"", host);
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        temp.name = *host;

        ctx = ngx_resolve_start(clcf->resolver, &amp;temp);
        if (ctx == NULL) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        if (ctx == NGX_NO_RESOLVER) {
            ngx_log_error(NGX_LOG_ERR, r->connection->log, 0,
                          "no resolver defined to resolve %V", host);

            ngx_http_upstream_finalize_request(r, u, NGX_HTTP_BAD_GATEWAY);
            return;
        }

        ctx->name = *host;
        ctx->handler = ngx_http_upstream_resolve_handler;
        ctx->data = r;
        ctx->timeout = clcf->resolver_timeout;

        u->resolved->ctx = ctx;

        if (ngx_resolve_name(ctx) != NGX_OK) {
            u->resolved->ctx = NULL;
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        return;
    }

found:

    if (uscf == NULL) {
        ngx_log_error(NGX_LOG_ALERT, r->connection->log, 0,
                      "no upstream configuration");
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    if (uscf->peer.init(r, uscf) != NGX_OK) {
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    ngx_http_upstream_connect(r, u);
}

static void
ngx_http_upstream_rd_check_broken_connection(ngx_http_request_t *r)
{
    ngx_http_upstream_check_broken_connection(r, r->connection->read);
}

static void
ngx_http_upstream_wr_check_broken_connection(ngx_http_request_t *r)
{
    ngx_http_upstream_check_broken_connection(r, r->connection->write);
}
```

### 建立连接

       upstream 机制与上游服务器建立 TCP 连接时，采用的是非阻塞模式的套接字，即发起连接请求之后立即返回，不管连接是否建立成功，若没有立即建立成功，则需在 epoll 事件机制中监听该套接字。向上游服务器发起连接请求由函数ngx_http_upstream_connect 实现。在分析 ngx_http_upstream_connect 方法之前，首先分析下 ngx_event_connect_peer 方法，因为该方法会被ngx_http_upstream_connect 方法调用。

ngx_event_connect_peer 方法的执行流程如下所示：

- 调用 ngx_socket 方法创建一个 TCP 套接字；
- 调用 ngx_nonblocking 方法设置该 TCP 套接字为非阻塞模式；
- 设置套接字连接接收和发送网络字符流的方法；
- 设置套接字连接上读、写事件方法；
- 将 TCP 套接字以期待 EPOLLIN | EPOLLOUT 事件的方式添加到epoll 事件机制中；
- 调用 connect 方法向服务器发起 TCP 连接请求；

ngx_http_upstream_connect 方法表示向上游服务器发起连接请求，其执行流程如下所示：

- 调用 ngx_event_connect_peer 方法主动向上游服务器发起连接请求，需要注意的是该方法已经将相应的套接字注册到epoll事件机制来监听读、写事件，该方法返回值为 rc；

- 若 rc = NGX_ERROR，表示发起连接失败，则调用ngx_http_upstream_finalize_request 方法关闭连接请求，并 return 从当前函数返回；
- 若 rc = NGX_BUSY，表示当前上游服务器处于不活跃状态，则调用 ngx_http_upstream_next 方法根据传入的参数尝试重新发起连接请求，并 return 从当前函数返回；
- 若 rc = NGX_DECLINED，表示当前上游服务器负载过重，则调用 ngx_http_upstream_next 方法尝试与其他上游服务器建立连接，并 return 从当前函数返回；
- 设置上游连接 ngx_connection_t 结构体的读事件、写事件的回调方法 handler 都为 ngx_http_upstream_handler，设置 ngx_http_upstream_t 结构体的写事件 write_event_handler 的回调为 ngx_http_upstream_send_request_handler，读事件 read_event_handler 的回调方法为 ngx_http_upstream_process_header；
- 若 rc = NGX_AGAIN，表示当前已经发起连接，但是没有收到上游服务器的确认应答报文，即上游连接的写事件不可写，则需调用 ngx_add_timer 方法将上游连接的写事件添加到定时器中，管理超时确认应答；
- 若 rc = NGX_OK，表示成功建立连接，则调用 ngx_http_upsream_send_request 方法向上游服务器发送请求；

```c
/* 向上游服务器建立连接 */
static void
ngx_http_upstream_connect(ngx_http_request_t *r, ngx_http_upstream_t *u)
{
    ngx_int_t          rc;
    ngx_time_t        *tp;
    ngx_connection_t  *c;

    r->connection->log->action = "connecting to upstream";

    if (u->state &amp;&amp; u->state->response_sec) {
        tp = ngx_timeofday();
        u->state->response_sec = tp->sec - u->state->response_sec;
        u->state->response_msec = tp->msec - u->state->response_msec;
    }

    u->state = ngx_array_push(r->upstream_states);
    if (u->state == NULL) {
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    ngx_memzero(u->state, sizeof(ngx_http_upstream_state_t));

    tp = ngx_timeofday();
    u->state->response_sec = tp->sec;
    u->state->response_msec = tp->msec;

    /* 向上游服务器发起连接 */
    rc = ngx_event_connect_peer(&amp;u->peer);

    ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                   "http upstream connect: %i", rc);

    /* 下面根据rc不同返回值进行分析 */

    /* 若建立连接失败，则关闭当前请求，并return从当前函数返回 */
    if (rc == NGX_ERROR) {
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    u->state->peer = u->peer.name;

    /*
     * 若返回rc = NGX_BUSY，表示当前上游服务器不活跃，
     * 则调用ngx_http_upstream_next向上游服务器重新发起连接，
     * 实际上，该方法最终还是调用ngx_http_upstream_connect方法；
     * 并return从当前函数返回；
     */
    if (rc == NGX_BUSY) {
        ngx_log_error(NGX_LOG_ERR, r->connection->log, 0, "no live upstreams");
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_NOLIVE);
        return;
    }

    /*
     * 若返回rc = NGX_DECLINED，表示当前上游服务器负载过重，
     * 则调用ngx_http_upstream_next向上游服务器重新发起连接，
     * 实际上，该方法最终还是调用ngx_http_upstream_connect方法；
     * 并return从当前函数返回；
     */
    if (rc == NGX_DECLINED) {
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_ERROR);
        return;
    }

    /* rc == NGX_OK || rc == NGX_AGAIN || rc == NGX_DONE */

    c = u->peer.connection;

    c->data = r;

    /* 设置当前连接ngx_connection_t 上读、写事件的回调方法 */
    c->write->handler = ngx_http_upstream_handler;
    c->read->handler = ngx_http_upstream_handler;

    /* 设置upstream机制的读、写事件的回调方法 */
    u->write_event_handler = ngx_http_upstream_send_request_handler;
    u->read_event_handler = ngx_http_upstream_process_header;

    c->sendfile &amp;= r->connection->sendfile;
    u->output.sendfile = c->sendfile;

    if (c->pool == NULL) {

        /* we need separate pool here to be able to cache SSL connections */

        c->pool = ngx_create_pool(128, r->connection->log);
        if (c->pool == NULL) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }
    }

    c->log = r->connection->log;
    c->pool->log = c->log;
    c->read->log = c->log;
    c->write->log = c->log;

    /* init or reinit the ngx_output_chain() and ngx_chain_writer() contexts */

    u->writer.out = NULL;
    u->writer.last = &amp;u->writer.out;
    u->writer.connection = c;
    u->writer.limit = 0;

    /*
     * 检查当前ngx_http_upstream_t 结构的request_sent标志位，
     * 若该标志位为1，则表示已经向上游服务器发送请求，即本次发起连接失败；
     * 则调用ngx_http_upstream_reinit方法重新向上游服务器发起连接；
     */
    if (u->request_sent) {
        if (ngx_http_upstream_reinit(r, u) != NGX_OK) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }
    }

    if (r->request_body
        &amp;&amp; r->request_body->buf
        &amp;&amp; r->request_body->temp_file
        &amp;&amp; r == r->main)
    {
        /*
         * the r->request_body->buf can be reused for one request only,
         * the subrequests should allocate their own temporary bufs
         */

        u->output.free = ngx_alloc_chain_link(r->pool);
        if (u->output.free == NULL) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        u->output.free->buf = r->request_body->buf;
        u->output.free->next = NULL;
        u->output.allocated = 1;

        r->request_body->buf->pos = r->request_body->buf->start;
        r->request_body->buf->last = r->request_body->buf->start;
        r->request_body->buf->tag = u->output.tag;
    }

    u->request_sent = 0;

    /*
     * 若返回rc = NGX_AGAIN，表示没有收到上游服务器允许建立连接的应答；
     * 由于写事件已经添加到epoll事件机制中等待可写事件发生，
     * 所有在这里只需将当前连接的写事件添加到定时器机制中进行超时管理；
     * 并return从当前函数返回；
     */
    if (rc == NGX_AGAIN) {
        ngx_add_timer(c->write, u->conf->connect_timeout);
        return;
    }

#if (NGX_HTTP_SSL)

    if (u->ssl &amp;&amp; c->ssl == NULL) {
        ngx_http_upstream_ssl_init_connection(r, u, c);
        return;
    }

#endif

    /*
     * 若返回值rc = NGX_OK，表示连接成功建立，
     * 调用此方法向上游服务器发送请求 */
    ngx_http_upstream_send_request(r, u);
}
```

### 发送请求

       当 Nginx 与上游服务器成功建立连接之后，会调用 ngx_http_upstream_send_request 方法发送请求，若是该方法不能一次性把请求内容发送完成时，则需等待 epoll 事件机制的写事件发生，若写事件发生，则会调用写事件 write_event_handler 的回调方法 ngx_http_upstream_send_request_handler 继续发送请求，并且有可能会多次调用该写事件的回调方法， 直到把请求发送完成。

下面是 ngx_http_upstream_send_request 方法的执行流程：

- 检查 ngx_http_upstream_t 结构体中的标志位 request_sent 是否为 0，若为 0 表示未向上游发送请求。 且此时调用 ngx_http_upstream_test_connect 方法测试是否与上游建立连接，若返回非 NGX_OK， 则需调用 ngx_http_upstream_next 方法试图与上游建立连接，并return 从当前函数返回；
- 调用 ngx_output_chain 方法向上游发送保存在 request_bufs 链表中的请求数据，该方法返回值为 rc，并设置 request_sent 标志位为 1，检查连接上写事件 timer_set 标志位是否为1，若为 1 调用ngx_del_timer 方法将写事件从定时器中移除；
- 若 rc = NGX_ERROR，表示当前连接上出错，则调用 ngx_http_upstream_next 方法尝试再次与上游建立连接，并 return 从当前函数返回；
- 若 rc = NGX_AGAIN，并是当前请求数据未完全发送，则需将剩余的请求数据保存在 ngx_http_upstream_t 结构体的 output 成员中，并且调用 ngx_add_timer 方法将当前连接上的写事件添加到定时器中，调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中，等待可写事件发生，并return 从当前函数返回；
- 若 rc = NGX_OK，表示已经发送全部请求数据，则准备接收来自上游服务器的响应报文；
- 先调用 ngx_add_timer 方法将当前连接的读事件添加到定时器机制中，检测接收响应是否超时，检查当前连接上的读事件是否准备就绪，即标志位 ready 是否为1，若该标志位为 1，则调用 ngx_http_upstream_process_header 方法开始处理响应头部，并 return 从当前函数返回；
- 若当前连接上读事件的标志位 ready 为0，表示暂时无可读数据，则需等待读事件再次被触发，由于原始读事件的回调方法为 ngx_http_upstream_process_header，所有无需重新设置。由于请求已经全部发送，防止写事件的回调方法 ngx_http_upstream_send_request_handler 再次被触发，因此需要重新设置写事件的回调方法为 ngx_http_upstream_dummy_handler，该方法实际上不执行任何操作，同时调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中；

```c
/* 向上游服务器发送请求 */
static void
ngx_http_upstream_send_request(ngx_http_request_t *r, ngx_http_upstream_t *u)
{
    ngx_int_t          rc;
    ngx_connection_t  *c;

    /* 获取当前连接 */
    c = u->peer.connection;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream send request");

    /*
     * 若标志位request_sent为0，表示还未发送请求；
     * 且ngx_http_upstream_test_connect方法返回非NGX_OK，标志当前还未与上游服务器成功建立连接；
     * 则需要调用ngx_http_upstream_next方法尝试与下一个上游服务器建立连接；
     * 并return从当前函数返回；
     */
    if (!u->request_sent &amp;&amp; ngx_http_upstream_test_connect(c) != NGX_OK) {
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_ERROR);
        return;
    }

    c->log->action = "sending request to upstream";

    /*
     * 调用ngx_output_chain方法向上游发送保存在request_bufs链表中的请求数据；
     * 值得注意的是该方法的第二个参数可以是NULL也可以是request_bufs，那怎么来区分呢？
     * 若是第一次调用该方法发送request_bufs链表中的请求数据时，request_sent标志位为0，
     * 此时，第二个参数自然就是request_bufs了，那么为什么会有NULL作为参数的情况呢？
     * 当在第一次调用该方法时，并不能一次性把所有request_bufs中的数据发送完毕时，
     * 此时，会把剩余的数据保存在output结构里面，并把标志位request_sent设置为1，
     * 因此，再次发送请求数据时，不用指定request_bufs参数，因为此时剩余数据已经保存在output中；
     */
    rc = ngx_output_chain(&amp;u->output, u->request_sent ? NULL : u->request_bufs);

    /* 向上游服务器发送请求之后，把request_sent标志位设置为1 */
    u->request_sent = 1;

    /* 下面根据不同rc的返回值进行判断 */

    /*
     * 若返回值rc=NGX_ERROR，表示当前连接上出错，
     * 将错误信息传递给ngx_http_upstream_next方法，
     * 该方法根据错误信息决定是否重新向上游服务器发起连接；
     * 并return从当前函数返回；
     */
    if (rc == NGX_ERROR) {
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_ERROR);
        return;
    }

    /*
     * 检查当前连接上写事件的标志位timer_set是否为1，
     * 若该标志位为1，则需把写事件从定时器机制中移除；
     */
    if (c->write->timer_set) {
        ngx_del_timer(c->write);
    }

    /*
     * 若返回值rc = NGX_AGAIN，表示请求数据并未完全发送，
     * 即有剩余的请求数据保存在output中，但此时，写事件已经不可写，
     * 则调用ngx_add_timer方法把当前连接上的写事件添加到定时器机制，
     * 并调用ngx_handle_write_event方法将写事件注册到epoll事件机制中；
     * 并return从当前函数返回；
     */
    if (rc == NGX_AGAIN) {
        ngx_add_timer(c->write, u->conf->send_timeout);

        if (ngx_handle_write_event(c->write, u->conf->send_lowat) != NGX_OK) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        return;
    }

    /* rc == NGX_OK */

    /*
     * 若返回值 rc = NGX_OK，表示已经发送完全部请求数据，
     * 准备接收来自上游服务器的响应报文，则执行以下程序；
     */
    if (c->tcp_nopush == NGX_TCP_NOPUSH_SET) {
        if (ngx_tcp_push(c->fd) == NGX_ERROR) {
            ngx_log_error(NGX_LOG_CRIT, c->log, ngx_socket_errno,
                          ngx_tcp_push_n " failed");
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        c->tcp_nopush = NGX_TCP_NOPUSH_UNSET;
    }

    /* 将当前连接上读事件添加到定时器机制中 */
    ngx_add_timer(c->read, u->conf->read_timeout);

    /*
     * 若此时，读事件已经准备就绪，
     * 则调用ngx_http_upstream_process_header方法开始接收并处理响应头部；
     * 并return从当前函数返回；
     */
    if (c->read->ready) {
        ngx_http_upstream_process_header(r, u);
        return;
    }

    /*
     * 若当前读事件未准备就绪；
     * 则把写事件的回调方法设置为ngx_http_upstream_dumy_handler方法(不进行任何实际操作)；
     * 并把写事件注册到epoll事件机制中；
     */
    u->write_event_handler = ngx_http_upstream_dummy_handler;

    if (ngx_handle_write_event(c->write, 0) != NGX_OK) {
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }
}
```

当无法一次性将请求内容全部发送完毕，则需等待 epoll 事件机制的写事件发生，一旦发生就会调用回调方法 ngx_http_upstream_send_request_handler。

ngx_http_upstream_send_request_handler 方法的执行流程如下所示：

- 检查连接上写事件是否超时，即timedout 标志位是否为 1，若为 1 表示已经超时，则调用 ngx_http_upstream_next 方法重新向上游发起连接请求，并 return 从当前函数返回；
- 若标志位 timedout 为0，即不超时，检查 header_sent 标志位是否为 1，表示已经接收到来自上游服务器的响应头部，则不需要再向上游发送请求，将写事件的回调方法设置为 ngx_http_upstream_dummy_handler，同时将写事件注册到 epoll 事件机制中，并return 从当前函数返回；
- 若标志位 header_sent 为 0，则调用 ngx_http_upstream_send_request 方法向上游发送请求数据；

```c
static void
ngx_http_upstream_send_request_handler(ngx_http_request_t *r,
    ngx_http_upstream_t *u)
{
    ngx_connection_t  *c;

    c = u->peer.connection;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                   "http upstream send request handler");

    /* 检查当前连接上写事件的超时标志位 */
    if (c->write->timedout) {
        /* 执行超时重连机制 */
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_TIMEOUT);
        return;
    }

#if (NGX_HTTP_SSL)

    if (u->ssl &amp;&amp; c->ssl == NULL) {
        ngx_http_upstream_ssl_init_connection(r, u, c);
        return;
    }

#endif

    /* 已经接收到上游服务器的响应头部，则不需要再向上游服务器发送请求数据 */
    if (u->header_sent) {
        /* 将写事件的回调方法设置为不进行任何实际操作的方法ngx_http_upstream_dumy_handler */
        u->write_event_handler = ngx_http_upstream_dummy_handler;

        /* 将写事件注册到epoll事件机制中，并return从当前函数返回 */
        (void) ngx_handle_write_event(c->write, 0);

        return;
    }

    /* 若没有接收来自上游服务器的响应头部，则需向上游服务器发送请求数据 */
    ngx_http_upstream_send_request(r, u);
}
```

### 接收响应

### 接收响应头部

当 Nginx 已经向上游发送请求，准备开始接收来自上游的响应头部，由方法 ngx_http_upstream_process_header 实现，该方法接收并解析响应头部。

ngx_http_upstream_process_header 方法的执行流程如下：

- 检查上游连接上的读事件是否超时，若标志位 timedout 为 1，则表示超时，此时调用 ngx_http_upstream_next 方法重新与上游建立连接，并 return 从当前函数返回；
- 若标志位 timedout 为 0，接着检查 ngx_http_upstream_t 结构体中的标志位 request_sent，若该标志位为 0，表示未向上游发送请求，同时调用 ngx_http_upstream_test_connect 方法测试连接状态，若该方法返回值为非 NGX_OK，表示与上游已经断开连接，则调用 ngx_http_upstream_next 方法重新与上游建立连接，并 return 从当前函数返回；
- 检查 ngx_http_upstream_t 结构体中接收响应头部的 buffer 缓冲区是否有内存空间以便接收响应头部，若 buffer.start 为 NULL，表示该缓冲区为空，则需调用 ngx_palloc 方法分配内存，该内存大小 buffer_size 由 ngx_http_upstream_conf_t 配置结构体的 buffer_size 成员指定；
- 调用 recv 方法开始接收来自上游服务器的响应头部，并根据该方法的返回值 n 进行判断：

- 若 n = NGX_AGAIN，表示读事件未准备就绪，需要等待下次读事件被触发时继续接收响应头部，此时，调用 ngx_add_timer 方法将读事件添加到定时器中，同时调用 ngx_handle_read_event 方法将读事件注册到epoll 事件机制中，并 return 从当前函数返回；
- 若 n = NGX_ERROR 或 n = 0，表示上游连接发生错误 或 上游服务器主动关闭连接，则调用 ngx_http_upstream_next 方法重新发起连接请求，并 return 从当前函数返回；
- 若 n 大于 0，表示已经接收到响应头部，此时，调用 ngx_http_upstream_t 结构体中由 HTTP 模块实现的 process_header 方法解析响应头部，且返回 rc 值；

- 若 rc = NGX_AGAIN，表示接收到的响应头部不完整，检查接收缓冲区 buffer 是否还有剩余的内存空间，若缓冲区没有剩余的内存空间，表示接收到的响应头部过大，此时调用 ngx_http_upstream_next 方法重新建立连接，并 return 从当前函数返回；若缓冲区还有剩余的内存空间，则continue 继续接收响应头部；
- 若 rc = NGX_HTTP_UPSTREAM_INVALID_HEADER，表示接收到的响应头部是非法的，则调用 ngx_http_upstream_next 方法重新建立连接，并 return 从当前函数返回；
- 若 rc = NGX_ERROR，表示连接出错，此时调用 ngx_http_upstream_finalize_request 方法结束请求，并 return 从当前函数返回；
- 若 rc = NGX_OK，表示已接收到完整的响应头部，则调用 ngx_http_upstream_process_headers 方法处理已解析的响应头部，该方法会将已解析出来的响应头部保存在 ngx_http_request_t 结构体中的 headers_out 成员；
- 检查 ngx_http_request_t 结构体的 subrequest_in_memory 成员决定是否需要转发响应给下游服务器；

- 若 subrequest_in_memory 为 0，表示需要转发响应给下游服务器，则调用 ngx_http_upstream_send_response 方法开始转发响应给下游服务器，并 return 从当前函数返回；
- 若 subrequest_in_memory 为 1，表示不需要将响应转发给下游，此时检查 HTTP 模块是否定义了 ngx_http_upstream_t 结构体中的 input_filter 方法处理响应包体；

- 若没有定义 input_filter 方法，则使用 upstream 机制默认方法 ngx_http_upstream_non_buffered_filter 代替 input_filter 方法；
- 若定义了自己的 input_filter 方法，则首先调用 input_filter_init 方法为处理响应包体做初始化工作；
- 检查接收缓冲区 buffer 在解析完响应头部之后剩余的字符流，若有剩余的字符流，则表示已经预接收了响应包体，此时调用 input_filter 方法处理响应包体；
- 设置 upstream 机制读事件 read_event_handler 的回调方法为 ngx_http_upstream_process_body_in_memory，并调用该方法开始接收并解析响应包体；

```c
/* 接收并解析响应头部 */
static void
ngx_http_upstream_process_header(ngx_http_request_t *r, ngx_http_upstream_t *u)
{
    ssize_t            n;
    ngx_int_t          rc;
    ngx_connection_t  *c;

    c = u->peer.connection;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process header");

    c->log->action = "reading response header from upstream";

    /* 检查当前连接上的读事件是否超时 */
    if (c->read->timedout) {
        /*
         * 若标志位timedout为1，表示读事件超时；
         * 则把超时错误传递给ngx_http_upstream_next方法，
         * 该方法根据允许的错误进行重连接策略；
         * 并return从当前函数返回；
         */
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_TIMEOUT);
        return;
    }

    /*
     * 若标志位request_sent为0，表示还未发送请求；
     * 且ngx_http_upstream_test_connect方法返回非NGX_OK，标志当前还未与上游服务器成功建立连接；
     * 则需要调用ngx_http_upstream_next方法尝试与下一个上游服务器建立连接；
     * 并return从当前函数返回；
     */
    if (!u->request_sent &amp;&amp; ngx_http_upstream_test_connect(c) != NGX_OK) {
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_ERROR);
        return;
    }

    /*
     * 检查ngx_http_upstream_t结构体中接收响应头部的buffer缓冲区；
     * 若接收缓冲区buffer未分配内存，则调用ngx_palloce方法分配内存，
     * 该内存的大小buffer_size由ngx_http_upstream_conf_t配置结构的buffer_size指定；
     */
    if (u->buffer.start == NULL) {
        u->buffer.start = ngx_palloc(r->pool, u->conf->buffer_size);
        if (u->buffer.start == NULL) {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        /* 调整接收缓冲区buffer，准备接收响应头部 */
        u->buffer.pos = u->buffer.start;
        u->buffer.last = u->buffer.start;
        u->buffer.end = u->buffer.start + u->conf->buffer_size;
        /* 表示该缓冲区内存可被复用、数据可被改变 */
        u->buffer.temporary = 1;

        u->buffer.tag = u->output.tag;

        /* 初始化headers_in的成员headers链表 */
        if (ngx_list_init(&amp;u->headers_in.headers, r->pool, 8,
                          sizeof(ngx_table_elt_t))
            != NGX_OK)
        {
            ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

#if (NGX_HTTP_CACHE)

        if (r->cache) {
            u->buffer.pos += r->cache->header_start;
            u->buffer.last = u->buffer.pos;
        }
#endif
    }

    for ( ;; ) {

        /* 调用recv方法从当前连接上读取响应头部数据 */
        n = c->recv(c, u->buffer.last, u->buffer.end - u->buffer.last);

        /* 下面根据 recv 方法不同返回值 n 进行判断 */

        /*
         * 若返回值 n = NGX_AGAIN，表示读事件未准备就绪，
         * 需等待下次读事件被触发时继续接收响应头部，
         * 即将读事件注册到epoll事件机制中，等待可读事件发生；
         * 并return从当前函数返回；
         */
        if (n == NGX_AGAIN) {
#if 0
            ngx_add_timer(rev, u->read_timeout);
#endif

            if (ngx_handle_read_event(c->read, 0) != NGX_OK) {
                ngx_http_upstream_finalize_request(r, u,
                                               NGX_HTTP_INTERNAL_SERVER_ERROR);
                return;
            }

            return;
        }

        if (n == 0) {
            ngx_log_error(NGX_LOG_ERR, c->log, 0,
                          "upstream prematurely closed connection");
        }

        /*
         * 若返回值 n = NGX_ERROR 或 n = 0，则表示上游服务器已经主动关闭连接；
         * 此时，调用ngx_http_upstream_next方法决定是否重新发起连接；
         * 并return从当前函数返回；
         */
        if (n == NGX_ERROR || n == 0) {
            ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_ERROR);
            return;
        }

        /* 若返回值 n 大于 0，表示已经接收到响应头部 */
        u->buffer.last += n;

#if 0
        u->valid_header_in = 0;

        u->peer.cached = 0;
#endif

        /*
         * 调用ngx_http_upstream_t结构体中process_header方法开始解析响应头部；
         * 并根据该方法返回值进行不同的判断；
         */
        rc = u->process_header(r);

        /*
         * 若返回值 rc = NGX_AGAIN，表示接收到的响应头部不完整，
         * 需等待下次读事件被触发时继续接收响应头部；
         * continue继续接收响应；
         */
        if (rc == NGX_AGAIN) {

            if (u->buffer.last == u->buffer.end) {
                ngx_log_error(NGX_LOG_ERR, c->log, 0,
                              "upstream sent too big header");

                ngx_http_upstream_next(r, u,
                                       NGX_HTTP_UPSTREAM_FT_INVALID_HEADER);
                return;
            }

            continue;
        }

        break;
    }

    /*
     * 若返回值 rc = NGX_HTTP_UPSTREAM_INVALID_HEADER，
     * 则表示接收到的响应头部是非法的，
     * 调用ngx_http_upstream_next方法决定是否重新发起连接；
     * 并return从当前函数返回；
     */
    if (rc == NGX_HTTP_UPSTREAM_INVALID_HEADER) {
        ngx_http_upstream_next(r, u, NGX_HTTP_UPSTREAM_FT_INVALID_HEADER);
        return;
    }

    /*
     * 若返回值 rc = NGX_ERROR，表示出错，
     * 则调用ngx_http_upstream_finalize_request方法结束该请求；
     * 并return从当前函数返回；
     */
    if (rc == NGX_ERROR) {
        ngx_http_upstream_finalize_request(r, u,
                                           NGX_HTTP_INTERNAL_SERVER_ERROR);
        return;
    }

    /* rc == NGX_OK */

    /*
     * 若返回值 rc = NGX_OK，表示成功解析到完整的响应头部；*/
    if (u->headers_in.status_n >= NGX_HTTP_SPECIAL_RESPONSE) {

        if (ngx_http_upstream_test_next(r, u) == NGX_OK) {
            return;
        }

        if (ngx_http_upstream_intercept_errors(r, u) == NGX_OK) {
            return;
        }
    }

    /* 调用ngx_http_upstream_process_headers方法处理已解析处理的响应头部 */
    if (ngx_http_upstream_process_headers(r, u) != NGX_OK) {
        return;
    }

    /*
     * 检查ngx_http_request_t 结构体的subrequest_in_memory成员决定是否转发响应给下游服务器；
     * 若该标志位为0，则需调用ngx_http_upstream_send_response方法转发响应给下游服务器；
     * 并return从当前函数返回；
     */
    if (!r->subrequest_in_memory) {
        ngx_http_upstream_send_response(r, u);
        return;
    }

    /* 若不需要转发响应，则调用ngx_http_upstream_t中的input_filter方法处理响应包体 */
    /* subrequest content in memory */

    /*
     * 若HTTP模块没有定义ngx_http_upstream_t中的input_filter处理方法；
     * 则使用upstream机制默认方法ngx_http_upstream_non_buffered_filter；
     *
     * 若HTTP模块实现了input_filter方法，则不使用upstream默认的方法；
     */
    if (u->input_filter == NULL) {
        u->input_filter_init = ngx_http_upstream_non_buffered_filter_init;
        u->input_filter = ngx_http_upstream_non_buffered_filter;
        u->input_filter_ctx = r;
    }

    /*
     * 调用input_filter_init方法为处理包体做初始化工作；
     */
    if (u->input_filter_init(u->input_filter_ctx) == NGX_ERROR) {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    /*
     * 检查接收缓冲区是否有剩余的响应数据；
     * 因为响应头部已经解析完毕，若接收缓冲区还有未被解析的剩余数据，
     * 则该数据就是响应包体；
     */
    n = u->buffer.last - u->buffer.pos;

    /*
     * 若接收缓冲区有剩余的响应包体，调用input_filter方法开始处理已接收到响应包体；
     */
    if (n) {
        u->buffer.last = u->buffer.pos;

        u->state->response_length += n;

        /* 调用input_filter方法处理响应包体 */
        if (u->input_filter(u->input_filter_ctx, n) == NGX_ERROR) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }
    }

    if (u->length == 0) {
        ngx_http_upstream_finalize_request(r, u, 0);
        return;
    }

    /* 设置upstream机制的读事件回调方法read_event_handler为ngx_http_upstream_process_body_in_memory */
    u->read_event_handler = ngx_http_upstream_process_body_in_memory;

    /* 调用ngx_http_upstream_process_body_in_memory方法开始处理响应包体 */
    ngx_http_upstream_process_body_in_memory(r, u);
}
```

### 接收响应包体

接收并解析响应包体由 ngx_http_upstream_process_body_in_memory 方法实现；

ngx_http_upstream_process_body_in_memory 方法的执行流程如下所示：

- 检查上游连接上读事件是否超时，若标志位 timedout 为 1，则表示已经超时，此时调用 ngx_http_upstream_finalize_request 方法结束请求，并 return 从当前函数返回；
- 检查接收缓冲区 buffer 是否还有剩余的内存空间，若没有剩余的内存空间，则调用 ngx_http_upstream_finalize_request 方法结束请求，并 return 从当前函数返回；若有剩余的内存空间则调用 recv 方法开始接收响应包体；

- 若返回值 n = NGX_AGAIN，表示等待下一次触发读事件再接收响应包体，调用 ngx_handle_read_event 方法将读事件注册到 epoll 事件机制中，同时将读事件添加到定时器机制中；
- 若返回值 n = 0 或 n = NGX_ERROR，则调用 ngx_http_upstream_finalize_request 方法结束请求，并 return 从当前函数返回；
- 若返回值 n 大于 0，则表示成功接收到响应包体，调用 input_filter 方法开始处理响应包体，检查读事件的 ready 标志位；

- 若标志位 ready 为 1，表示仍有可读的响应包体数据，因此回到步骤 2 继续调用 recv 方法读取响应包体，直到读取完毕；
- 若标志位 ready 为 0，则调用 ngx_handle_read_event 方法将读事件注册到epoll事件机制中，同时调用 ngx_add_timer 方法将读事件添加到定时器机制中；

```c
/* 接收并解析响应包体 */
static void
ngx_http_upstream_process_body_in_memory(ngx_http_request_t *r,
    ngx_http_upstream_t *u)
{
    size_t             size;
    ssize_t            n;
    ngx_buf_t         *b;
    ngx_event_t       *rev;
    ngx_connection_t  *c;

    c = u->peer.connection;
    rev = c->read;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process body on memory");

    /*
     * 检查读事件标志位timedout是否超时，若该标志位为1，表示响应已经超时；
     * 则调用ngx_http_upstream_finalize_request方法结束请求；
     * 并return从当前函数返回；
     */
    if (rev->timedout) {
        ngx_connection_error(c, NGX_ETIMEDOUT, "upstream timed out");
        ngx_http_upstream_finalize_request(r, u, NGX_HTTP_GATEWAY_TIME_OUT);
        return;
    }

    b = &amp;u->buffer;

    for ( ;; ) {

        /* 检查当前接收缓冲区是否剩余的内存空间 */
        size = b->end - b->last;

        /*
         * 若接收缓冲区不存在空闲的内存空间，
         * 则调用ngx_http_upstream_finalize_request方法结束请求；
         * 并return从当前函数返回；
         */
        if (size == 0) {
            ngx_log_error(NGX_LOG_ALERT, c->log, 0,
                          "upstream buffer is too small to read response");
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }

        /*
         * 若接收缓冲区有可用的内存空间，
         * 则调用recv方法开始接收响应包体；
         */
        n = c->recv(c, b->last, size);

        /*
         * 若返回值 n = NGX_AGAIN，表示等待下一次触发读事件再接收响应包体；
         */
        if (n == NGX_AGAIN) {
            break;
        }

        /*
         * 若返回值n = 0(表示上游服务器主动关闭连接)，或n = NGX_ERROR(表示出错)；
         * 则调用ngx_http_upstream_finalize_request方法结束请求；
         * 并return从当前函数返回；
         */
        if (n == 0 || n == NGX_ERROR) {
            ngx_http_upstream_finalize_request(r, u, n);
            return;
        }

        /* 若返回值 n 大于0，表示成功读取到响应包体 */
        u->state->response_length += n;

        /* 调用input_filter方法处理本次接收到的响应包体 */
        if (u->input_filter(u->input_filter_ctx, n) == NGX_ERROR) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }

        /* 检查读事件的ready标志位，若为1，继续读取响应包体 */
        if (!rev->ready) {
            break;
        }
    }

    if (u->length == 0) {
        ngx_http_upstream_finalize_request(r, u, 0);
        return;
    }

    /*
     * 若读事件的ready标志位为0，表示读事件未准备就绪，
     * 则将读事件注册到epoll事件机制中，添加到定时器机制中；
     * 读事件的回调方法不改变，即依旧为ngx_http_upstream_process_body_in_memory；
     */
    if (ngx_handle_read_event(rev, 0) != NGX_OK) {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    if (rev->active) {
        ngx_add_timer(rev, u->conf->read_timeout);

    } else if (rev->timer_set) {
        ngx_del_timer(rev);
    }
}
```

### 转发响应

下面看下 upstream 处理上游响应包体的三种方式：

1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 1 时，upstream 不转发响应包体到下游，并由HTTP 模块实现的input_filter() 方法处理包体；
1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 0 时，且ngx_http_upstream_conf_t 配置结构体中的成员buffering 标志位为 1 时，upstream 将开启更多的内存和磁盘文件用于缓存上游的响应包体（此时，上游网速更快），并转发响应包体；
1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 0 时，且ngx_http_upstream_conf_t 配置结构体中的成员buffering 标志位为 0 时，upstream 将使用固定大小的缓冲区来转发响应包体；

转发响应由函数 ngx_http_upstream_send_response 实现，该函数的执行流程如下：

- 调用 ngx_http_send_header 方法转发响应头部，并将 ngx_http_upstream_t 结构体中的 header_sent 标志位设置为 1，表示已经转发响应头部；
- 若临时文件还保存着请求包体，则需调用 ngx_pool_run_cleanup_filter 方法清理临时文件；
- 检查标志位 buffering，若该标志位为 1，表示需要开启文件缓存，若该标志位为 0，则不需要开启文件缓存，只需要以固定的内存块大小转发响应包体即可；
- 若标志位 buffering 为0；

- 则检查 HTTP 模块是否实现了自己的 input_filter 方法，若没有则使用 upstream 机制默认的方法 ngx_http_upstream_non_buffered_filter；
- 设置 ngx_http_upstream_t 结构体中读事件 read_event_handler 的回调方法为 ngx_http_upstream_process_non_buffered_upstream，当接收上游响应时，会通过 ngx_http_upstream_handler 方法最终调用 ngx_http_upstream_process_non_buffered_uptream 来接收响应；
- 设置 ngx_http_upstream_t 结构体中写事件 write_event_handler 的回调方法为 ngx_http_upstream_process_non_buffered_downstream，当向下游发送数据时，会通过 ngx_http_handler 方法最终调用 ngx_http_upstream_process_non_buffered_downstream 方法来发送响应包体；
- 调用 input_filter_init 方法为 input_filter 方法处理响应包体做初始化工作；
- 检查接收缓冲区 buffer 在解析完响应头部之后，是否还有剩余的响应数据，若有表示预接收了响应包体：

- 若在解析响应头部区间，预接收了响应包体，则调用 input_filter 方法处理该部分预接收的响应包体，并调用 ngx_http_upstream_process_non_buffered_downstream 方法转发本次接收到的响应包体给下游服务器；
- 若在解析响应头部区间，没有接收响应包体，则首先清空接收缓冲区 buffer 以便复用来接收响应包体，检查上游连接上读事件是否准备就绪，若标志位 ready 为1，表示准备就绪，则调用 ngx_http_upstream_process_non_buffered_upstream 方法接收上游响应包体；若标志位 ready 为 0，则 return 从当前函数返回；

- 若标志位 buffering 为1；

- 初始化 ngx_http_upstream_t 结构体中的 ngx_event_pipe_t pipe 成员；
- 调用 input_filter_init 方法为 input_filter 方法处理响应包体做初始化工作；
- 设置上游连接上的读事件 read_event_handler 的回调方法为 ngx_http_upstream_process_upstream；
- 设置上游连接上的写事件 write_event_handler 的回调方法为 ngx_http_upstream_process_downstream；
- 调用 ngx_http_upstream_proess_upstream 方法处理由上游服务器发来的响应包体；

```c
/* 转发响应包体 */
static void
ngx_http_upstream_send_response(ngx_http_request_t *r, ngx_http_upstream_t *u)
{
    int                        tcp_nodelay;
    ssize_t                    n;
    ngx_int_t                  rc;
    ngx_event_pipe_t          *p;
    ngx_connection_t          *c;
    ngx_http_core_loc_conf_t  *clcf;

    /* 调用ngx_http_send_hander方法向下游发送响应头部 */
    rc = ngx_http_send_header(r);

    if (rc == NGX_ERROR || rc > NGX_OK || r->post_action) {
        ngx_http_upstream_finalize_request(r, u, rc);
        return;
    }

    /* 将标志位header_sent设置为1 */
    u->header_sent = 1;

    if (u->upgrade) {
        ngx_http_upstream_upgrade(r, u);
        return;
    }

    /* 获取Nginx与下游之间的TCP连接 */
    c = r->connection;

    if (r->header_only) {

        if (u->cacheable || u->store) {

            if (ngx_shutdown_socket(c->fd, NGX_WRITE_SHUTDOWN) == -1) {
                ngx_connection_error(c, ngx_socket_errno,
                                     ngx_shutdown_socket_n " failed");
            }

            r->read_event_handler = ngx_http_request_empty_handler;
            r->write_event_handler = ngx_http_request_empty_handler;
            c->error = 1;

        } else {
            ngx_http_upstream_finalize_request(r, u, rc);
            return;
        }
    }

    /* 若临时文件保存着请求包体，则调用ngx_pool_run_cleanup_file方法清理临时文件的请求包体 */
    if (r->request_body &amp;&amp; r->request_body->temp_file) {
        ngx_pool_run_cleanup_file(r->pool, r->request_body->temp_file->file.fd);
        r->request_body->temp_file->file.fd = NGX_INVALID_FILE;
    }

    clcf = ngx_http_get_module_loc_conf(r, ngx_http_core_module);

    /*
     * 若标志位buffering为0，转发响应时以下游服务器网速优先；
     * 即只需分配固定的内存块大小来接收来自上游服务器的响应并转发，
     * 当该内存块已满，则暂停接收来自上游服务器的响应数据，
     * 等待把内存块的响应数据转发给下游服务器后有剩余内存空间再继续接收响应；
     */
    if (!u->buffering) {

        /*
         * 若HTTP模块没有实现input_filter方法，
         * 则采用upstream机制默认的方法ngx_http_upstream_non_buffered_filter；
         */
        if (u->input_filter == NULL) {
            u->input_filter_init = ngx_http_upstream_non_buffered_filter_init;
            u->input_filter = ngx_http_upstream_non_buffered_filter;
            u->input_filter_ctx = r;
        }

        /*
         * 设置ngx_http_upstream_t结构体中读事件的回调方法为ngx_http_upstream_non_buffered_upstream，(即读取上游响应的方法)；
         * 设置当前请求ngx_http_request_t结构体中写事件的回调方法为ngx_http_upstream_process_non_buffered_downstream，(即转发响应到下游的方法)；
         */
        u->read_event_handler = ngx_http_upstream_process_non_buffered_upstream;
        r->write_event_handler =
                             ngx_http_upstream_process_non_buffered_downstream;

        r->limit_rate = 0;

        /* 调用input_filter_init为input_filter方法处理响应包体做初始化工作 */
        if (u->input_filter_init(u->input_filter_ctx) == NGX_ERROR) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }

        if (clcf->tcp_nodelay &amp;&amp; c->tcp_nodelay == NGX_TCP_NODELAY_UNSET) {
            ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0, "tcp_nodelay");

            tcp_nodelay = 1;

            if (setsockopt(c->fd, IPPROTO_TCP, TCP_NODELAY,
                               (const void *) &amp;tcp_nodelay, sizeof(int)) == -1)
            {
                ngx_connection_error(c, ngx_socket_errno,
                                     "setsockopt(TCP_NODELAY) failed");
                ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                return;
            }

            c->tcp_nodelay = NGX_TCP_NODELAY_SET;
        }

        /* 检查解析完响应头部后接收缓冲区buffer是否已接收了响应包体 */
        n = u->buffer.last - u->buffer.pos;

        /* 若接收缓冲区已经接收了响应包体 */
        if (n) {
            u->buffer.last = u->buffer.pos;

            u->state->response_length += n;

            /* 调用input_filter方法开始处理响应包体 */
            if (u->input_filter(u->input_filter_ctx, n) == NGX_ERROR) {
                ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                return;
            }

            /* 调用该方法把本次接收到的响应包体转发给下游服务器 */
            ngx_http_upstream_process_non_buffered_downstream(r);

        } else {
            /* 若接收缓冲区中没有响应包体，则将其清空，即复用这个缓冲区 */
            u->buffer.pos = u->buffer.start;
            u->buffer.last = u->buffer.start;

            if (ngx_http_send_special(r, NGX_HTTP_FLUSH) == NGX_ERROR) {
                ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                return;
            }

            /*
             * 若当前连接上读事件已准备就绪，
             * 则调用ngx_http_upstream_process_non_buffered_upstream方法接收响应包体并处理；
             */
            if (u->peer.connection->read->ready || u->length == 0) {
                ngx_http_upstream_process_non_buffered_upstream(r, u);
            }
        }

        return;
    }

    /*
     * 若ngx_http_upstream_t结构体的buffering标志位为1，则转发响应包体时以上游网速优先；
     * 即分配更多的内存和缓存，即一直接收来自上游服务器的响应，把来自上游服务器的响应保存的内存或缓存中；
     */
    /* TODO: preallocate event_pipe bufs, look "Content-Length" */

#if (NGX_HTTP_CACHE)
    ...
    ...
#endif

    /* 初始化ngx_event_pipe_t结构体 p */
    p = u->pipe;

    p->output_filter = (ngx_event_pipe_output_filter_pt) ngx_http_output_filter;
    p->output_ctx = r;
    p->tag = u->output.tag;
    p->bufs = u->conf->bufs;
    p->busy_size = u->conf->busy_buffers_size;
    p->upstream = u->peer.connection;
    p->downstream = c;
    p->pool = r->pool;
    p->log = c->log;

    p->cacheable = u->cacheable || u->store;

    p->temp_file = ngx_pcalloc(r->pool, sizeof(ngx_temp_file_t));
    if (p->temp_file == NULL) {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    p->temp_file->file.fd = NGX_INVALID_FILE;
    p->temp_file->file.log = c->log;
    p->temp_file->path = u->conf->temp_path;
    p->temp_file->pool = r->pool;

    if (p->cacheable) {
        p->temp_file->persistent = 1;

    } else {
        p->temp_file->log_level = NGX_LOG_WARN;
        p->temp_file->warn = "an upstream response is buffered "
                             "to a temporary file";
    }

    p->max_temp_file_size = u->conf->max_temp_file_size;
    p->temp_file_write_size = u->conf->temp_file_write_size;

    /* 初始化预读链表缓冲区preread_bufs */
    p->preread_bufs = ngx_alloc_chain_link(r->pool);
    if (p->preread_bufs == NULL) {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    p->preread_bufs->buf = &amp;u->buffer;
    p->preread_bufs->next = NULL;
    u->buffer.recycled = 1;

    p->preread_size = u->buffer.last - u->buffer.pos;

    if (u->cacheable) {

        p->buf_to_file = ngx_calloc_buf(r->pool);
        if (p->buf_to_file == NULL) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }

        p->buf_to_file->start = u->buffer.start;
        p->buf_to_file->pos = u->buffer.start;
        p->buf_to_file->last = u->buffer.pos;
        p->buf_to_file->temporary = 1;
    }

    if (ngx_event_flags &amp; NGX_USE_AIO_EVENT) {
        /* the posted aio operation may corrupt a shadow buffer */
        p->single_buf = 1;
    }

    /* TODO: p->free_bufs = 0 if use ngx_create_chain_of_bufs() */
    p->free_bufs = 1;

    /*
     * event_pipe would do u->buffer.last += p->preread_size
     * as though these bytes were read
     */
    u->buffer.last = u->buffer.pos;

    if (u->conf->cyclic_temp_file) {

        /*
         * we need to disable the use of sendfile() if we use cyclic temp file
         * because the writing a new data may interfere with sendfile()
         * that uses the same kernel file pages (at least on FreeBSD)
         */

        p->cyclic_temp_file = 1;
        c->sendfile = 0;

    } else {
        p->cyclic_temp_file = 0;
    }

    p->read_timeout = u->conf->read_timeout;
    p->send_timeout = clcf->send_timeout;
    p->send_lowat = clcf->send_lowat;

    p->length = -1;

    /* 调用input_filter_init方法进行初始化工作 */
    if (u->input_filter_init
        &amp;&amp; u->input_filter_init(p->input_ctx) != NGX_OK)
    {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    /* 设置上游读事件的方法 */
    u->read_event_handler = ngx_http_upstream_process_upstream;
    /* 设置下游写事件的方法 */
    r->write_event_handler = ngx_http_upstream_process_downstream;

    /* 处理上游响应包体 */
    ngx_http_upstream_process_upstream(r, u);
}
```

当以下游网速优先转发响应包体给下游时，由函数 ngx_http_upstream_process_non_buffered_downstrean 实现，该函数的执行流程如下所示：

- 检查下游连接上写事件是否超时，若标志位 timedout 为1，则表示超时，此时调用 ngx_http_upstream_finalize_request 方法接收请求，并 return 从当前函数返回；
- 调用 ngx_http_upstream_process_non_bufferd_request 方法向下游服务器发送响应包体，此时第二个参数为 1；

```c
/* buffering 标志位为0时，转发响应包体给下游服务器 */
static void
ngx_http_upstream_process_non_buffered_downstream(ngx_http_request_t *r)
{
    ngx_event_t          *wev;
    ngx_connection_t     *c;
    ngx_http_upstream_t  *u;

    /* 获取Nginx与下游服务器之间的TCP连接 */
    c = r->connection;
    /* 获取ngx_http_upstream_t结构体 */
    u = r->upstream;
    /* 获取当前连接的写事件 */
    wev = c->write;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process non buffered downstream");

    c->log->action = "sending to client";

    /* 检查写事件是否超时，若超时则结束请求 */
    if (wev->timedout) {
        c->timedout = 1;
        ngx_connection_error(c, NGX_ETIMEDOUT, "client timed out");
        ngx_http_upstream_finalize_request(r, u, NGX_HTTP_REQUEST_TIME_OUT);
        return;
    }

    /* 若不超时，以固定内存块方式转发响应包体给下游服务器 */
    ngx_http_upstream_process_non_buffered_request(r, 1);
}
```

       由于 buffering 标志位为0时，没有开启文件缓存，只有固定大小的内存块作为接收响应缓冲区，当上游的响应包体比较大时，此时，接收缓冲区内存并不能够满足一次性接收完所有响应包体， 因此，在接收缓冲区已满时，会阻塞接收响应包体，并先把已经收到的响应包体转发给下游服务器。所有在转发响应包体时，有可能会接收上游响应包体。此过程由 ngx_http_upstream_process_non_buffered_upstream 方法实现；

ngx_http_upstream_process_non_buffered_upstream 方法执行流程如下：

- 检查上游连接上的读事件是否超时，若标志位 timedout 为 1，表示已经超时，此时调用 ngx_http_upstream_finalize_request 方法结束请求，并 return 从当前函数返回；
- 调用 ngx_http_upstream_process_non_buffered_request 方法接收上游响应包体，此时第二个参数为 0；

```c
/* 接收上游响应包体(buffering为0的情况) */
static void
ngx_http_upstream_process_non_buffered_upstream(ngx_http_request_t *r,
    ngx_http_upstream_t *u)
{
    ngx_connection_t  *c;

    /* 获取Nginx与上游服务器之间的TCP连接 */
    c = u->peer.connection;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process non buffered upstream");

    c->log->action = "reading upstream";

    /* 判断读事件是否超时，若超时则结束当前请求 */
    if (c->read->timedout) {
        ngx_connection_error(c, NGX_ETIMEDOUT, "upstream timed out");
        ngx_http_upstream_finalize_request(r, u, NGX_HTTP_GATEWAY_TIME_OUT);
        return;
    }

    /*
     * 若不超时，则以固定内存块方式转发响应包体给下游服务器，
     * 注意：转发的过程中，会接收来自上游服务器的响应包体；
     */
    ngx_http_upstream_process_non_buffered_request(r, 0);
}
```

       在上面函数中向下游服务器转发响应包体过程中，最终会调用 ngx_http_upstream_process_non_buffered_request 方法来实现，而且转发响应包体给下游服务器时，同时会接收来自上游的响应包体，接收上游响应包体最终也会调用该函数，只是调用的时候第二个参数指定不同的值；

ngx_http_upstream_process_non_buffered_request 方法执行流程如下所示：

- *步骤1*：若 do_write 参数的值为 0，表示需要接收来自上游服务器的响应包体，则直接跳到*步骤3*开始执行；
- *步骤2*：若 do_write 参数的值为 1，则开始向下游转发响应包体；

- 检查 ngx_http_upstream_t 结构体中的 out_bufs 链表 或 busy_bufs 链表是否有数据：

- 若 out_bufs 或 busy_bufs 链表缓冲区中有响应包体，则调用 ngx_http_output_filter 方法向下游发送响应包体，并调用 ngx_chain_update_chains 方法更新 ngx_http_upstream_t 结构体中的 free_bufs、busy_bufs、out_bufs 链表缓冲区；
- 若 out_bufs 和 busy_bufs 链表缓冲区中都没有数据，则清空接收缓冲区 buffer 以便再次接收来自上游服务器的响应包体；

- *步骤3*：计算接收缓冲区 buffer 剩余的内存空间 size，若有剩余的内存空间，且此时上游连接上有可读的响应包体(即读事件的 ready 标志位为 1)，则调用 recv 方法读取上游响应包体，并返回 n；

- 若返回值 n 大于 0，表示已经接收到上游响应包体，则调用 input_filter 方法处理响应包体，并设置 do_write 标志位为 1，表示已经接收到响应包体，此时可转发给下游服务器，又回到*步骤2*继续执行；
- 若返回值 n = NGX_AGAIN，表示需要等待下一次读事件的发生以便继续接收上游响应包体，则直接跳至*步骤5*开始执行；

- *步骤4*：若接收缓冲区 buffer 没有剩余内存空间 或 上游连接上读事件未准备就绪，则从*步骤5*开始执行；
- *步骤5*：调用 ngx_add_timer 方法将下游连接上写事件添加到定时器机制中，调用 ngx_handle_write_event 方法将下游连接上写事件注册到 epoll 事件机制中；
- *步骤6*：调用 ngx_handle_read_event 方法将上游连接上读事件注册到 epoll 事件机制中，调用 ngx_add_timer 方法将上游连接上读事件添加到定时器机制中；

```c
/* 以固定内存块方式转发响应包体给下游服务器 */

/*
 * 第二个参数表示本次是否需要向下游发送响应；若为0时，需要接收来自上游服务器的响应，也需要转发响应给下游；
 * 若为1，只负责转发响应给下游服务器；
 */
static void
ngx_http_upstream_process_non_buffered_request(ngx_http_request_t *r,
    ngx_uint_t do_write)
{
    size_t                     size;
    ssize_t                    n;
    ngx_buf_t                 *b;
    ngx_int_t                  rc;
    ngx_connection_t          *downstream, *upstream;
    ngx_http_upstream_t       *u;
    ngx_http_core_loc_conf_t  *clcf;

    /* 获取ngx_http_upstream_t结构体 */
    u = r->upstream;
    /* 获取Nginx与下游服务器之间的TCP连接 */
    downstream = r->connection;
    /* 获取Nginx与上游服务器之间的TCP连接 */
    upstream = u->peer.connection;

    /* 获取ngx_hhtp_upstream_t结构体的接收缓冲区buffer */
    b = &amp;u->buffer;

    /*
     * 获取do_write的值，该值决定是否还要接收来自上游服务器的响应；
     * 其中length表示还需要接收的上游响应包体长度；
     */
    do_write = do_write || u->length == 0;

    for ( ;; ) {

        if (do_write) {/* 若do_write为1，则开始向下游服务器转发响应包体 */

            /*
             * 检查是否有响应包体需要转发给下游服务器；
             * 其中out_bufs表示本次需要转发给下游服务器的响应包体；
             * busy_bufs表示上一次向下游服务器转发响应包体时没有转发完的响应包体内存；
             * 即若一次性转发不完所有的响应包体，则会保存在busy_bufs链表缓冲区中，
             * 这里的保存只是将busy_bufs指向未发送完毕的响应数据；
             */
            if (u->out_bufs || u->busy_bufs) {
                /* 调用ngx_http_output_filter方法将响应包体发送给下游服务器 */
                rc = ngx_http_output_filter(r, u->out_bufs);

                /* 若返回值 rc = NGX_ERROR，则结束请求 */
                if (rc == NGX_ERROR) {
                    ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                    return;
                }

                /*
                 * 调用ngx_chain_update_chains方法更新free_bufs、busy_bufs、out_bufs链表；
                 * 即清空out_bufs链表，把out_bufs链表中已发送完的ngx_buf_t缓冲区清空，并将其添加到free_bufs链表中；
                 * 把out_bufs链表中未发送完的ngx_buf_t缓冲区添加到busy_bufs链表中；
                 */
                ngx_chain_update_chains(r->pool, &amp;u->free_bufs, &amp;u->busy_bufs,
                                        &amp;u->out_bufs, u->output.tag);
            }

            /*
             * busy_bufs为空，表示所有响应包体已经转发到下游服务器，
             * 此时清空接收缓冲区buffer以便再次接收来自上游服务器的响应包体；
             */
            if (u->busy_bufs == NULL) {

                if (u->length == 0
                    || (upstream->read->eof &amp;&amp; u->length == -1))
                {
                    ngx_http_upstream_finalize_request(r, u, 0);
                    return;
                }

                if (upstream->read->eof) {
                    ngx_log_error(NGX_LOG_ERR, upstream->log, 0,
                                  "upstream prematurely closed connection");

                    ngx_http_upstream_finalize_request(r, u,
                                                       NGX_HTTP_BAD_GATEWAY);
                    return;
                }

                if (upstream->read->error) {
                    ngx_http_upstream_finalize_request(r, u,
                                                       NGX_HTTP_BAD_GATEWAY);
                    return;
                }

                b->pos = b->start;
                b->last = b->start;
            }
        }

        /* 计算接收缓冲区buffer剩余可用的内存空间 */
        size = b->end - b->last;

        /*
         * 若接收缓冲区buffer有剩余的可用空间，
         * 且此时读事件可读，即可读取来自上游服务器的响应包体；
         * 则调用recv方法开始接收来自上游服务器的响应包体，并保存在接收缓冲区buffer中；
         */
        if (size &amp;&amp; upstream->read->ready) {

            n = upstream->recv(upstream, b->last, size);

            /* 若返回值 n = NGX_AGAIN，则等待下一次可读事件发生继续接收响应 */
            if (n == NGX_AGAIN) {
                break;
            }

            /*
             * 若返回值 n 大于0，表示接收到响应包体，
             * 则调用input_filter方法处理响应包体；
             * 并把do_write设置为1；
             */
            if (n > 0) {
                u->state->response_length += n;

                if (u->input_filter(u->input_filter_ctx, n) == NGX_ERROR) {
                    ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                    return;
                }
            }

            do_write = 1;

            continue;
        }

        break;
    }

    clcf = ngx_http_get_module_loc_conf(r, ngx_http_core_module);

    /* 调用ngx_handle_write_event方法将Nginx与下游之间的连接上的写事件注册的epoll事件机制中 */
    if (downstream->data == r) {
        if (ngx_handle_write_event(downstream->write, clcf->send_lowat)
            != NGX_OK)
        {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }
    }

    /* 调用ngx_add_timer方法将Nginx与下游之间的连接上的写事件添加到定时器事件机制中 */
    if (downstream->write->active &amp;&amp; !downstream->write->ready) {
        ngx_add_timer(downstream->write, clcf->send_timeout);

    } else if (downstream->write->timer_set) {
        ngx_del_timer(downstream->write);
    }

    /* 调用ngx_handle_read_event方法将Nginx与上游之间的连接上的读事件注册的epoll事件机制中 */
    if (ngx_handle_read_event(upstream->read, 0) != NGX_OK) {
        ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        return;
    }

    /* 调用ngx_add_timer方法将Nginx与上游之间的连接上的读事件添加到定时器事件机制中 */
    if (upstream->read->active &amp;&amp; !upstream->read->ready) {
        ngx_add_timer(upstream->read, u->conf->read_timeout);

    } else if (upstream->read->timer_set) {
        ngx_del_timer(upstream->read);
    }
}
```

```c
/* upstream机制默认的input_filter方法 */
static ngx_int_t
ngx_http_upstream_non_buffered_filter(void *data, ssize_t bytes)
{
    /*
     * data参数是ngx_http_upstream_t结构体中的input_filter_ctx，
     * 当HTTP模块没有实现input_filter方法时，
     * input_filter_ctx指向ngx_http_request_t结构体；
     */
    ngx_http_request_t  *r = data;

    ngx_buf_t            *b;
    ngx_chain_t          *cl, **ll;
    ngx_http_upstream_t  *u;

    u = r->upstream;

    /* 找到out_bufs链表的最后一个缓冲区，并由ll指向该缓冲区 */
    for (cl = u->out_bufs, ll = &amp;u->out_bufs; cl; cl = cl->next) {
        ll = &amp;cl->next;
    }

    /* 从free_bufs空闲链表缓冲区中获取一个ngx_buf_t结构体给cl */
    cl = ngx_chain_get_free_buf(r->pool, &amp;u->free_bufs);
    if (cl == NULL) {
        return NGX_ERROR;
    }

    /* 将新分配的ngx_buf_t缓冲区添加到out_bufs链表的尾端 */
    *ll = cl;

    cl->buf->flush = 1;
    cl->buf->memory = 1;

    /* buffer是保存来自上游服务器的响应包体 */
    b = &amp;u->buffer;

    /* 将响应包体数据保存在cl缓冲区中 */
    cl->buf->pos = b->last;
    b->last += bytes;
    cl->buf->last = b->last;
    cl->buf->tag = u->output.tag;

    if (u->length == -1) {
        return NGX_OK;
    }

    /* 更新length长度，表示需要接收的包体长度减少bytes字节 */
    u->length -= bytes;

    return NGX_OK;
}
```

当 buffering 标志位为 1 转发响应包体给下游时，由函数 ngx_http_upstream_process_downstream 实现。

ngx_http_upstream_process_downstream 方法的执行流程如下所示：

- 若下游连接上写事件的 timedout 标志位为 1，表示写事件已经超时；

- 若下游连接上写事件的 delayed 标志位为 1；

- 若下游连接上写事件 ready 标志位为 1，表示写事件已经准备就绪，则调用 ngx_event_pipe 方法转发响应包体；
- 若下游连接上写事件 ready 标志位为 0，表示写事件未准备就绪，则调用 ngx_add_timer 方法将写事件添加到定时器机制中，调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中，并 return 从当前函数返回；

- 若下游连接上写事件的 delayed 标志位为 0，则该连接已经出错，设置 ngx_event_pipe_t 结构体中 downstream_error标志位为 1，设置 ngx_connection_t 结构体中 timedout 标志位为 1，并调用 ngx_connection_error 方法；

- 若下游连接上写事件的 timedout 标志位为 0，表示写事件不超时；

- 若下游连接上写事件的 delayed 标志位为 1，则调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中，并 return 从当前函数返回；
- 若下游连接上写事件的 delayed 标志位为 0，则调用 ngx_event_pipe 方法转发响应包体；

- 最终调用 ngx_http_upstream_process_request 方法；

```c
static void
ngx_http_upstream_process_downstream(ngx_http_request_t *r)
{
    ngx_event_t          *wev;
    ngx_connection_t     *c;
    ngx_event_pipe_t     *p;
    ngx_http_upstream_t  *u;

    /* 获取 Nginx 与下游服务器之间的连接 */
    c = r->connection;
    /* 获取 Nginx 与上游服务器之间的连接 */
    u = r->upstream;
    /* 获取 ngx_event_pipe_t 结构体 */
    p = u->pipe;
    /* 获取下游连接上的写事件 */
    wev = c->write;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process downstream");

    c->log->action = "sending to client";

    /* 检查下游连接上写事件是否超时，若标志位 timedout 为 1，表示超时 */
    if (wev->timedout) {

        /* 若下游连接上写事件的delayed 标志位为 1 */
        if (wev->delayed) {

            wev->timedout = 0;
            wev->delayed = 0;

            /*
             * 检查写事件是否准备就绪，若 ready 标志位为 0，
             * 表示未准备就绪，则调用 ngx_add_timer 方法将写事件添加到定时器机制中；
             * 调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中；
             * 并 return 从当前函数返回；
             */
            if (!wev->ready) {
                ngx_add_timer(wev, p->send_timeout);

                if (ngx_handle_write_event(wev, p->send_lowat) != NGX_OK) {
                    ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                }

                return;
            }

            /*
             * 若写事件已经准备就绪，即ready 标志位为 1；
             * 则调用 ngx_event_pipe 方法将响应包体转发给下游服务器；
             * 并 return 从当前函数返回；
             */
            if (ngx_event_pipe(p, wev->write) == NGX_ABORT) {
                ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
                return;
            }

        } else {
            /* 若写事件的delayed标志位为 0，则设置downstream_error标志位为 1,，表示连接出错 */
            p->downstream_error = 1;
            c->timedout = 1;
            ngx_connection_error(c, NGX_ETIMEDOUT, "client timed out");
        }

    } else {/* 若下游连接上写事件不超时，即timedout 标志位为 0 */

        /*
         * 检查写事件 delayed 标志位，若该标志位为 1；
         * 则调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中；
         * 并 return 从当前函数返回；
         */
        if (wev->delayed) {

            ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                           "http downstream delayed");

            if (ngx_handle_write_event(wev, p->send_lowat) != NGX_OK) {
                ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            }

            return;
        }

        /* 若写事件的delayed 标志位为 0，则调用 ngx_event_pipe 方法转发响应 */
        if (ngx_event_pipe(p, 1) == NGX_ABORT) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }
    }
    /* 最终调用该函数 */
    ngx_http_upstream_process_request(r);
}
```

ngx_http_upstream_process_upstream 方法执行流程如下所示：

- 若上游连接上读事件的 timedout 标志位为 1，表示读事件已经超时，则设置 upstream_error 为 1，调用 ngx_connection_error 方法接收当前函数；
- 若上游连接上读事件的 timedout 标志位为 0，则调用 ngx_event_pipe 方法接收上游响应包体；
- 最终调用 ngx_http_upstream_process_request 方法；

```c
static void
ngx_http_upstream_process_upstream(ngx_http_request_t *r,
    ngx_http_upstream_t *u)
{
    ngx_connection_t  *c;

    c = u->peer.connection;

    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, c->log, 0,
                   "http upstream process upstream");

    c->log->action = "reading upstream";

    if (c->read->timedout) {
        u->pipe->upstream_error = 1;
        ngx_connection_error(c, NGX_ETIMEDOUT, "upstream timed out");

    } else {
        if (ngx_event_pipe(u->pipe, 0) == NGX_ABORT) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
            return;
        }
    }

    ngx_http_upstream_process_request(r);
}
```

ngx_event_pipe 方法的执行流程如下所示：

- *步骤1*：若参数 do_write 为 1，表示向下游转发响应；

- 调用 ngx_event_pipe_write_to_downstream 方法向下游转发响应，并返回值为 rc；

- 若返回值 rc = NGX_ABORT，则 return NGX_ABORT 从当前函数返回；
- 若返回值 rc = NGX_BUSY，表示不需要往下执行，则 return NGX_OK 从当前函数返回；
- 若返回值 rc = NGX_OK，则直接跳至*步骤3*执行；

- *步骤2*：若参数 do_write 为 0，表示需要接收上游响应，直接跳至*步骤3*执行；
- *步骤3*：设置 ngx_event_pipe_t 结构体中的 read 标志位为 0，upstream_blocked 标志位为 0；
- *步骤4*：调用 ngx_event_pipe_read_upstream 方法读取上游响应；
- *步骤5*：检查 read 和 upstream_blocked 标志位，若 read 和 upstream_blocked 标志位都为 0，则跳至*步骤7*执行；
- *步骤6*：若 read 或 upstream_blocked 标志位为 1，表示需要向下游发送刚刚读取到的响应，则设置 do_write 标志为 1，跳至*步骤1*继续执行；
- *步骤7*：调用 ngx_add_timer 方法将上游连接上的读事件添加到定时器机制中，调用 ngx_handle_read_event 方法将读事件注册到 epoll 事件机制中；
- *步骤8*：调用 ngx_add_timer 方法将下游连接上的写事件添加到定时器机制中，调用 ngx_handle_write_event 方法将写事件注册到 epoll 事件机制中；
- *步骤9*：return NGX_OK 从当前函数返回；

```c
/* 转发响应的ngx_event_pipe_t结构体 */
ngx_int_t
ngx_event_pipe(ngx_event_pipe_t *p, ngx_int_t do_write)
{
    u_int         flags;
    ngx_int_t     rc;
    ngx_event_t  *rev, *wev;

    for ( ;; ) {
        if (do_write) {/* 若 do_write标志位为1，表示向下游转发响应 */
            p->log->action = "sending to client";

            /* 调用ngx_event_pipe_write_to_downstream方法向下游转发响应 */
            rc = ngx_event_pipe_write_to_downstream(p);

            if (rc == NGX_ABORT) {
                return NGX_ABORT;
            }

            if (rc == NGX_BUSY) {
                return NGX_OK;
            }
        }

        /* 若do_write标志位为0，则接收上游响应 */
        p->read = 0;
        p->upstream_blocked = 0;

        p->log->action = "reading upstream";

        /* 调用ngx_event_pipe_read_upstream方法读取上游响应 */
        if (ngx_event_pipe_read_upstream(p) == NGX_ABORT) {
            return NGX_ABORT;
        }

        /*
         * 若标志位read和upstream_blocked为0，
         * 则没有可读的响应数据，break退出for循环；
         */
        if (!p->read &amp;&amp; !p->upstream_blocked) {
            break;
        }

        /* 否则，设置do_write标志位为1继续进行for循环操作 */
        do_write = 1;
    }

    /*
     * 将上游读事件添加到定时器机制中，注册到epoll事件机制中；
     */
    if (p->upstream->fd != (ngx_socket_t) -1) {
        rev = p->upstream->read;

        flags = (rev->eof || rev->error) ? NGX_CLOSE_EVENT : 0;

        if (ngx_handle_read_event(rev, flags) != NGX_OK) {
            return NGX_ABORT;
        }

        if (rev->active &amp;&amp; !rev->ready) {
            ngx_add_timer(rev, p->read_timeout);

        } else if (rev->timer_set) {
            ngx_del_timer(rev);
        }
    }

    /*
     * 将下游写事件添加到定时器机制中，注册到epoll事件机制中；
     */
    if (p->downstream->fd != (ngx_socket_t) -1
        &amp;&amp; p->downstream->data == p->output_ctx)
    {
        wev = p->downstream->write;
        if (ngx_handle_write_event(wev, p->send_lowat) != NGX_OK) {
            return NGX_ABORT;
        }

        if (!wev->delayed) {
            if (wev->active &amp;&amp; !wev->ready) {
                ngx_add_timer(wev, p->send_timeout);

            } else if (wev->timer_set) {
                ngx_del_timer(wev);
            }
        }
    }

    return NGX_OK;
}
```

ngx_event_pipe_read_upstream 方法的执行流程如下所示：

- *步骤1*：检查 ngx_event_pipe_t 结构体中的 upstream_eof(若为 1 表示上游连接通信已经结束)、upstream_error(若为 1，表示上游连接出错)、upstream_done(若为 1，表示上游连接已经关闭) 标志位，若其中一个标志位为 1，表示上游连接关闭，则 return NGX_OK 从当前函数返回；
- *步骤2*：进入 for 循环，再次检查以上三个标志位，若其中有一个为 1，则 break 退出for 循环，跳至*步骤8*执行；
- *步骤3*：若 preread_bufs 链表缓冲区为空，表示接收响应头部区间，没有预接收响应包体，且此时上游连接上读事件未准备就绪，即ready标志位为0，则 break 退出for 循环，跳至*步骤8*执行；
- *步骤4*：若 preread_bufs 链表缓冲区不为空，表示预接收了响应包体，则将 preread_bufs 链表缓冲区挂载到 chain 链表中，并计算预接收到响应包体的长度 n，若 n 大于 0，则设置 read 标志位为 1；
- *步骤5*：若 preread_bufs 链表缓冲区为空：

- 若 free_raw_bufs 不为空，则将该链表缓冲区挂载到chain链表中；
- 若 free_raw_bufs 为空：

- 若 allocated 小于 bufs.num，则调用 ngx_create_temp_buf 方法分配一个新的缓冲区 b，并将新分配的缓冲区挂载到 chain 链表中；
- 若 allocated 大于 bufs.num：

- 若 cacheable 标志位为 0，且下游连接上写事件已准备就绪，即写事件的 ready 标志位为 1，表示可以向下游发送响应包体，此时，设置upstream_blocked 标志位为 1，表示阻塞读取上游响应包体，因为没有缓冲区来接收上游响应包体，并break 退出for循环，跳至*步骤8*执行；
- 若 cacheable 标志位为 1，即开启了文件缓存，且此时临时文件长度未达到最大长度，则调用 ngx_event_pipe_write_chain_to_temp_file 方法将上游响应写入到临时文件中，以便使 free_raw_bufs 有空余缓冲区来继续接收上游响应，并将此 free_raw_bufs 链表缓冲区挂载到 chain 链表中；
- 若以上条件都不满足，则break 退出 for 循环，跳至 *步骤8*执行；

- *步骤6*：调用 recv_chain 方法接收上游响应包体，返回值为 n，并把接收到的上游响应包体缓冲区添加到 free_raw_bufs 链表的尾端；

- 若返回值 n = NGX_ERROR，表示上游连接出错，return NGX_ERROR 从当前函数返回；
- 若返回值 n = NGX_AGAIN，表示没有读取到上游响应包体，则 break 退出 for 循环，跳至*步骤8*执行；
- 若返回值 n = 0，表示上游服务器主动关闭连接，则 break 退出 for 循环，跳至*步骤8*执行；
- 若返回值 n 大于 0，则设置 read 标志位为 1，表示已经接收到上游响应包体；

- *步骤7*：开始处理已接收到的上游响应包体，遍历待处理缓冲区链表 chain 中的每一个 ngx_buf_t 缓冲区：

- 调用 ngx_event_pipe_remove_shadow_links 方法释放当前缓冲区 ngx_buf_t 中的 shadow 域；
- 计算当前缓冲区剩余的内存空间大小为 size：

- 若本次接收到的上游响应包体 n 不小于 size，表示当前缓冲区已满，调用 input_filter 方法处理当前缓冲区的响应包体，把其挂载到 in 链表中；
- 若本次接收到的上游响应包体 n 小于 size 值，则表示当前缓冲区还有剩余空间继续接收上游响应包体，先把本次接收到的响应包体缓冲区添加到 free_raw_bufs 链表尾端；

- *步骤8*：由于上面步骤接收到的上游响应包体最终会方法 free_raw_bufs 链表缓冲区中，再次检查 free_raw_bufs 链表缓冲区，若该缓冲区不为空，则调用 input_filter 方法处理该缓冲区的响应包体，同时调用 ngx_free_chain 方法是否 chain 缓冲区数据；
- *步骤9*：若 upstream_eof 或 upstream_error 标志位为 1，且 free_raw_bufs 不为空，再次调用 input_filter 方法处理 free_raw_bufs 缓冲区数据，若 free_bufs 标志位为 1，则调用 ngx_free 释放 shadow 域为空的缓冲区；

```c
/* 读取上游响应 */
static ngx_int_t
ngx_event_pipe_read_upstream(ngx_event_pipe_t *p)
{
    ssize_t       n, size;
    ngx_int_t     rc;
    ngx_buf_t    *b;
    ngx_chain_t  *chain, *cl, *ln;

    /*
     * 若Nginx与上游之间的通信已经结束、
     * 或Nginx与上游之间的连接出错、
     * 或Nginx与上游之间的连接已经关闭；
     * 则直接return NGX_OK 从当前函数返回；
     */
    if (p->upstream_eof || p->upstream_error || p->upstream_done) {
        return NGX_OK;
    }

    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                   "pipe read upstream: %d", p->upstream->read->ready);

    /* 开始接收上游响应包体，并调用input_filter方法进行处理 */
    for ( ;; ) {

        /*
         * 若Nginx与上游之间的通信已经结束、
         * 或Nginx与上游之间的连接出错、
         * 或Nginx与上游之间的连接已经关闭；
         * 则直接break 从当前for循环退出；
         */
        if (p->upstream_eof || p->upstream_error || p->upstream_done) {
            break;
        }

        /*
         * 若preread_bufs链表缓冲区为空(表示在接收响应头部区间，未预接收响应包体)，
         * 且上游读事件未准备就绪，即没有可读的响应包体；
         * break从for循环退出；
         */
        if (p->preread_bufs == NULL &amp;&amp; !p->upstream->read->ready) {
            break;
        }

        /*
         * 若preread_bufs链表缓冲区有未处理的数据，需要把它挂载到chain链表中
         * 即该数据是接收响应头部区间，预接收的响应包体；
         */
        if (p->preread_bufs) {/* the preread_bufs is not empty */

            /* use the pre-read bufs if they exist */

            chain = p->preread_bufs;/* 将预接收响应包体缓冲区添加到chain链表尾端 */
            p->preread_bufs = NULL;/* 使该缓冲区指向NULL，表示没有响应包体 */
            n = p->preread_size;/* 计算预接收响应包体的长度 n */

            ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                           "pipe preread: %z", n);

            /*
             * 若preread_bufs链表缓冲区不为空，
             * 则设置read标志位为1，表示当前已经接收了响应包体；
             */
            if (n) {
                p->read = 1;
            }

        } else {/* the preread_bufs is NULL */
        /*
         * 若preread_bufs链表缓冲区没有未处理的响应包体，
         * 则需要有缓冲区来接收上游响应包体；
         */

#if (NGX_HAVE_KQUEUE)
    ...
    ...
#endif

            /*
             * 若 free_raw_bufs不为空，则使用该链表缓冲区接收上游响应包体；
             * free_raw_bufs链表缓冲区用来保存调用一次ngx_event_pipe_read_upstream方法所接收到的上游响应包体；
             */
            if (p->free_raw_bufs) {

                /* use the free bufs if they exist */

                chain = p->free_raw_bufs;/* 将接收响应包体缓冲区添加到chain链表中 */
                if (p->single_buf) {/* 表示每一次只能接收一个ngx_buf_t缓冲区的响应包体 */
                    p->free_raw_bufs = p->free_raw_bufs->next;
                    chain->next = NULL;
                } else {
                    p->free_raw_bufs = NULL;
                }

            } else if (p->allocated < p->bufs.num) {
                /*
                 * 若 free_raw_bufs为空，且已分配的缓冲区数目allocated小于缓冲区数目bufs.num；
                 * 则需要分配一个新的缓冲区来接收上游响应包体；
                 */

                /* allocate a new buf if it's still allowed */

                /* 分配新的缓冲区来接收上游响应 */
                b = ngx_create_temp_buf(p->pool, p->bufs.size);
                if (b == NULL) {
                    return NGX_ABORT;
                }

                p->allocated++;

                /* 分配一个ngx_chain_t 链表缓冲区 */
                chain = ngx_alloc_chain_link(p->pool);
                if (chain == NULL) {
                    return NGX_ABORT;
                }

                /* 把新分配接收响应包体的缓冲区添加到chain链表中 */
                chain->buf = b;
                chain->next = NULL;

            } else if (!p->cacheable
                       &amp;&amp; p->downstream->data == p->output_ctx
                       &amp;&amp; p->downstream->write->ready
                       &amp;&amp; !p->downstream->write->delayed)
            {
                /* 若free_raw_bufs为空，且allocated大于bufs.num，若cacheable标志位为0，即不启用文件缓存，
                 * 检查Nginx与下游之间连接，并检查该连接上写事件是否准备就绪，
                 * 若已准备就绪，即表示可以向下游发送响应包体；
                 */
                /*
                 * if the bufs are not needed to be saved in a cache and
                 * a downstream is ready then write the bufs to a downstream
                 */

                /*
                 * 设置upstream_blocked标志位为1，表示阻塞读取上游响应，
                 * 因为没有缓冲区或文件缓存来接收响应包体，则应该阻塞读取上游响应包体；
                 * 并break退出for循环，此时会向下游转发响应，释放缓冲区，以便再次接收上游响应包体；
                 */
                p->upstream_blocked = 1;

                ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe downstream ready");

                break;/* 退出for循环 */

            } else if (p->cacheable
                       || p->temp_file->offset < p->max_temp_file_size)
            {/* 若cacheable标志位为1，即开启了文件缓存，则检查临时文件是否达到最大长度，若未达到最大长度 */

                /*
                 * if it is allowed, then save some bufs from p->in
                 * to a temporary file, and add them to a p->out chain
                 */

                /* 将上游响应写入到临时文件中，此时free_raw_bufs有缓冲区空间来接收上游 响应包体 */
                rc = ngx_event_pipe_write_chain_to_temp_file(p);

                ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe temp offset: %O", p->temp_file->offset);

                if (rc == NGX_BUSY) {
                    break;
                }

                if (rc == NGX_AGAIN) {
                    if (ngx_event_flags &amp; NGX_USE_LEVEL_EVENT
                        &amp;&amp; p->upstream->read->active
                        &amp;&amp; p->upstream->read->ready)
                    {
                        if (ngx_del_event(p->upstream->read, NGX_READ_EVENT, 0)
                            == NGX_ERROR)
                        {
                            return NGX_ABORT;
                        }
                    }
                }

                if (rc != NGX_OK) {
                    return rc;
                }

                chain = p->free_raw_bufs;
                if (p->single_buf) {
                    p->free_raw_bufs = p->free_raw_bufs->next;
                    chain->next = NULL;
                } else {
                    p->free_raw_bufs = NULL;
                }

            } else {
                /* 若没有缓冲区或文件缓存接收上游响应包体，则暂时不收受上游响应包体，break退出循环 */

                /* there are no bufs to read in */

                ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "no pipe bufs to read in");

                break;
            }
            /* end of check the free_raw_bufs */

            /*
             * 若有缓冲区接收上游响应包体，则调用recv_chain方法接收上游响应包体；
             * 把接收到的上游响应包体缓冲区添加到free_raw_bufs链表的尾端；
             */
            n = p->upstream->recv_chain(p->upstream, chain);

            ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                           "pipe recv chain: %z", n);

            /* 将保存接收到上游响应包体的缓冲区添加到free_raw_bufs链表尾端 */
            if (p->free_raw_bufs) {
                chain->next = p->free_raw_bufs;
            }
            p->free_raw_bufs = chain;

            /* 下面根据所接收上游响应包体的返回值n来进行判断 */

            /*
             * n = NGX_ERROR，表示发生错误，
             * 则设置upstream_error标志位为1，
             * 并return NGX_ERROR从当前函数返回；
             */
            if (n == NGX_ERROR) {
                p->upstream_error = 1;
                return NGX_ERROR;
            }

            /*
             * n = NGX_AGAIN，表示没有读取到上游响应包体，
             * 则break跳出for循环；
             */
            if (n == NGX_AGAIN) {
                if (p->single_buf) {
                    ngx_event_pipe_remove_shadow_links(chain->buf);
                }

                break;
            }

            /*
             * n 大于0，表示已经接收到上游响应包体，
             * 则设置read标志位为1；
             */
            p->read = 1;

            /*
             * n = 0，表示上游服务器主动关闭连接，
             * 则设置upstream_eof标志位为1，表示已关闭连接；
             * 并break退出for循环；
             */
            if (n == 0) {
                p->upstream_eof = 1;
                break;
            }
        }

        /* checking the preread_bufs is end */

        /* 下面开始处理已接收到的上游响应包体数据 */
        p->read_length += n;
        cl = chain;
        p->free_raw_bufs = NULL;

        /* 遍历待处理缓冲区链表chain中的ngx_buf_t缓冲区 */
        while (cl &amp;&amp; n > 0) {

            /* 调用该方法将当前ngx_buf_t缓冲区中的shadow域释放 */
            ngx_event_pipe_remove_shadow_links(cl->buf);

            /* 计算当前缓冲区剩余的空间大小 */
            size = cl->buf->end - cl->buf->last;

            /* 若本次接收到上游响应包体的长度大于缓冲区剩余的空间，表示当前缓冲区已满 */
            if (n >= size) {
                cl->buf->last = cl->buf->end;

                /* STUB */ cl->buf->num = p->num++;

                /* 调用input_filter方法处理当前缓冲区响应包体，把其挂载到in链表中 */
                if (p->input_filter(p, cl->buf) == NGX_ERROR) {
                    return NGX_ABORT;
                }

                n -= size;
                ln = cl;
                cl = cl->next;
                ngx_free_chain(p->pool, ln);

            } else {
            /*
             * 若本次接收到上游响应包体的长度小于缓冲区剩余的空间，
             * 表示当前缓冲区还有剩余空间接收上游响应包体；
             * 则先把本次接收到的响应包体缓冲区添加到free_raw_bufs链表尾端；
             */
                cl->buf->last += n;
                n = 0;
            }
        }

        if (cl) {
            for (ln = cl; ln->next; ln = ln->next) { /* void */ }

            ln->next = p->free_raw_bufs;
            p->free_raw_bufs = cl;
        }
    }

    /* end of the For cycle */

#if (NGX_DEBUG)
    ...
    ...
#endif

    /* 若free_raw_bufs不为空 */
    if (p->free_raw_bufs &amp;&amp; p->length != -1) {
        cl = p->free_raw_bufs;

        if (cl->buf->last - cl->buf->pos >= p->length) {

            p->free_raw_bufs = cl->next;

            /* STUB */ cl->buf->num = p->num++;

            /* 调用input_filter方法处理free_raw_bufs缓冲区 */
            if (p->input_filter(p, cl->buf) == NGX_ERROR) {
                 return NGX_ABORT;
            }

            /* 释放已被处理的chain缓冲区数据 */
            ngx_free_chain(p->pool, cl);
        }
    }

    if (p->length == 0) {
        p->upstream_done = 1;
        p->read = 1;
    }

    /*
     * 检查upstream_eof或upstream_error标志位是否为1，若其中一个为1，表示连接已经关闭，
     * 若连接已经关闭，且free_raw_bufs缓冲区不为空；
     */
    if ((p->upstream_eof || p->upstream_error) &amp;&amp; p->free_raw_bufs) {

        /* STUB */ p->free_raw_bufs->buf->num = p->num++;

        /* 再次调用input_filter方法处理free_raw_bufs缓冲区的响应包体数据 */
        if (p->input_filter(p, p->free_raw_bufs->buf) == NGX_ERROR) {
            return NGX_ABORT;
        }

        p->free_raw_bufs = p->free_raw_bufs->next;

        /* 检查free_bufs标志位，若为1，则释放shadow域为空的缓冲区 */
        if (p->free_bufs &amp;&amp; p->buf_to_file == NULL) {
            for (cl = p->free_raw_bufs; cl; cl = cl->next) {
                if (cl->buf->shadow == NULL) {
                    ngx_pfree(p->pool, cl->buf->start);
                }
            }
        }
    }

    if (p->cacheable &amp;&amp; p->in) {
        if (ngx_event_pipe_write_chain_to_temp_file(p) == NGX_ABORT) {
            return NGX_ABORT;
        }
    }

    /* 返回NGX_OK，结束当前函数 */
    return NGX_OK;
}
```

       ngx_event_pipe_write_to_downstream 方法将 in 链表和 out 链表中管理的缓冲区发送到下游服务器，由于 out 链表中缓冲区的内容在响应中的位置比 in 链表靠前，因此优先发送 out 链表内容给下游服务器。

ngx_event_pipe_write_to_downstream 方法的执行流程如下：

- *步骤1*：检查上游连接是否结束，即标志位 upstream_eof、upstream_error、upstream_done 有一个为 1，则表示不需要再接收上游响应包体，跳至*步骤2*执行，否则跳至*步骤5*执行；
- *步骤2*：调用 output_filter 方法将 out 链表缓冲区中的响应包体发送给下游服务器；
- *步骤3*：调用 output_filter 方法将 in 链表缓冲区中的响应包体发送给下游服务器；
- *步骤4*：设置 downstream_done 标志位为 1，结束当前函数；
- *步骤5*：计算 busy 链表缓冲区中待发送的响应包体长度 bsize，若 bsize 大于配置项规定值 busy_size，则跳至*步骤7*执行，否则继续向下游准备发送 out 或 in 链表缓冲区中的响应包体；
- *步骤6*：检查 out 链表是否为空：

- 若 out 链表不为空，取出 out 链表首个缓冲区 ngx_buf_t 作为发送响应包体，跳至*步骤7*执行；
- 若 out 链表为空，检查 in 链表是否为空：

- 若 in 链表为空，则说明本次没有需要发送的响应包体，则返回 NGX_OK，结束当前函数；
- 若 in 链表不为空，取出 in 链表首部的第一个缓冲区作为待发送响应包体缓冲区，跳至*步骤7*执行；

- *步骤7*：检查以前待发送响应包体长度加上本次本次需要发送的响应包体长度是否大于 busy_size，若大于 busy_size，跳至*步骤8*执行；否则跳至*步骤5*执行；
- *步骤8*：调用 output_filter 方法向下游服务器发送存储响应包体的 out 缓冲区链表；
- *步骤9*：调用 ngx_chain_update_chain 方法更新 free、busy、out 缓冲区；
- *步骤10*：遍历 free 链表，释放缓冲区中的 shadow 域；

```c
/* 向下游服务器转发响应包体 */
static ngx_int_t
ngx_event_pipe_write_to_downstream(ngx_event_pipe_t *p)
{
    u_char            *prev;
    size_t             bsize;
    ngx_int_t          rc;
    ngx_uint_t         flush, flushed, prev_last_shadow;
    ngx_chain_t       *out, **ll, *cl;
    ngx_connection_t  *downstream;

    /* 获取Nginx与下游服务器之间的连接 */
    downstream = p->downstream;

    ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                   "pipe write downstream: %d", downstream->write->ready);

    flushed = 0;

    for ( ;; ) {
        /* downstream_error标志位为1，表示与下游之间的连接出现错误 */
        if (p->downstream_error) {
            return ngx_event_pipe_drain_chains(p);
        }

        /* 检查与上游之间的连接是否关闭，若已关闭 */
        if (p->upstream_eof || p->upstream_error || p->upstream_done) {

            /* pass the p->out and p->in chains to the output filter */

            for (cl = p->busy; cl; cl = cl->next) {
                cl->buf->recycled = 0;
            }

            /* 调用output_filter方法将out链表中的缓冲区响应包体转发给下游 */
            if (p->out) {
                ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe write downstream flush out");

                for (cl = p->out; cl; cl = cl->next) {
                    cl->buf->recycled = 0;
                }

                rc = p->output_filter(p->output_ctx, p->out);

                if (rc == NGX_ERROR) {
                    p->downstream_error = 1;
                    return ngx_event_pipe_drain_chains(p);
                }

                p->out = NULL;
            }

            /* 调用output_filter方法将in链表中的缓冲区响应包体转发给下游 */
            if (p->in) {
                ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe write downstream flush in");

                for (cl = p->in; cl; cl = cl->next) {
                    cl->buf->recycled = 0;
                }

                rc = p->output_filter(p->output_ctx, p->in);

                if (rc == NGX_ERROR) {
                    p->downstream_error = 1;
                    return ngx_event_pipe_drain_chains(p);
                }

                p->in = NULL;
            }

            if (p->cacheable &amp;&amp; p->buf_to_file) {
                ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe write chain");

                if (ngx_event_pipe_write_chain_to_temp_file(p) == NGX_ABORT) {
                    return NGX_ABORT;
                }
            }

            ngx_log_debug0(NGX_LOG_DEBUG_EVENT, p->log, 0,
                           "pipe write downstream done");

            /* TODO: free unused bufs */

            p->downstream_done = 1;
            break;
        }

        /*
         * 若上游连接没有关闭，则检查下游连接上的写事件是否准备就绪；
         * 若准备就绪，则表示可以向下游转发响应包体；
         * 若未准备就绪，则break退出for循环，return NGX_OK从当前函数返回；
         */
        if (downstream->data != p->output_ctx
            || !downstream->write->ready
            || downstream->write->delayed)
        {
            break;
        }

        /* bsize is the size of the busy recycled bufs */

        prev = NULL;
        bsize = 0;

        /* 计算busy链表缓冲区中待发送响应包体的长度bsize */
        for (cl = p->busy; cl; cl = cl->next) {

            if (cl->buf->recycled) {
                if (prev == cl->buf->start) {
                    continue;
                }

                bsize += cl->buf->end - cl->buf->start;
                prev = cl->buf->start;
            }
        }

        ngx_log_debug1(NGX_LOG_DEBUG_EVENT, p->log, 0,
                       "pipe write busy: %uz", bsize);

        out = NULL;

        /* 检查bsize是否大于busy_size配置项 */
        if (bsize >= (size_t) p->busy_size) {
            flush = 1;
            goto flush;
        }

        /* 若bsize小于busy_size配置项 */
        flush = 0;
        ll = NULL;
        prev_last_shadow = 1;

        /*
         * 检查in、out链表缓冲区是否为空，若不为空；
         * 将out、in链表首个缓冲区作为发送内容；
         */
        for ( ;; ) {
            if (p->out) {/* out链表不为空，则取出首个缓冲区作为发送响应内容 */
                cl = p->out;

                if (cl->buf->recycled) {
                    ngx_log_error(NGX_LOG_ALERT, p->log, 0,
                                  "recycled buffer in pipe out chain");
                }

                p->out = p->out->next;

            } else if (!p->cacheable &amp;&amp; p->in) {
                /* 若out为空，检查in是否为空，若in不为空，则首个缓冲区作为发送内容 */
                cl = p->in;

                ngx_log_debug3(NGX_LOG_DEBUG_EVENT, p->log, 0,
                               "pipe write buf ls:%d %p %z",
                               cl->buf->last_shadow,
                               cl->buf->pos,
                               cl->buf->last - cl->buf->pos);

                if (cl->buf->recycled &amp;&amp; prev_last_shadow) {
                    /* 判断待发送响应包体长度加上本次缓冲区的长度是否大于busy_size */
                    if (bsize + cl->buf->end - cl->buf->start > p->busy_size) {
                        flush = 1;
                        break;
                    }

                    bsize += cl->buf->end - cl->buf->start;
                }

                prev_last_shadow = cl->buf->last_shadow;

                p->in = p->in->next;

            } else {
                break;
            }

            cl->next = NULL;

            if (out) {
                *ll = cl;
            } else {
                out = cl;
            }
            ll = &amp;cl->next;
        }

    flush:

        ngx_log_debug2(NGX_LOG_DEBUG_EVENT, p->log, 0,
                       "pipe write: out:%p, f:%d", out, flush);

        if (out == NULL) {

            if (!flush) {
                break;
            }

            /* a workaround for AIO */
            if (flushed++ > 10) {
                return NGX_BUSY;
            }
        }

        /* 调用output_filter方法发送out链表缓冲区 */
        rc = p->output_filter(p->output_ctx, out);

        /* 更新free、busy、out链表缓冲区 */
        ngx_chain_update_chains(p->pool, &amp;p->free, &amp;p->busy, &amp;out, p->tag);

        if (rc == NGX_ERROR) {
            p->downstream_error = 1;
            return ngx_event_pipe_drain_chains(p);
        }

        /* 遍历free链表，释放缓冲区中的shadow域 */
        for (cl = p->free; cl; cl = cl->next) {

            if (cl->buf->temp_file) {
                if (p->cacheable || !p->cyclic_temp_file) {
                    continue;
                }

                /* reset p->temp_offset if all bufs had been sent */

                if (cl->buf->file_last == p->temp_file->offset) {
                    p->temp_file->offset = 0;
                }
            }

            /* TODO: free buf if p->free_bufs &amp;&amp; upstream done */

            /* add the free shadow raw buf to p->free_raw_bufs */

            if (cl->buf->last_shadow) {
                if (ngx_event_pipe_add_free_buf(p, cl->buf->shadow) != NGX_OK) {
                    return NGX_ABORT;
                }

                cl->buf->last_shadow = 0;
            }

            cl->buf->shadow = NULL;
        }
    }

    return NGX_OK;
}
```

```c
static void
ngx_http_upstream_process_request(ngx_http_request_t *r)
{
    ngx_temp_file_t      *tf;
    ngx_event_pipe_t     *p;
    ngx_http_upstream_t  *u;

    u = r->upstream;
    p = u->pipe;

    if (u->peer.connection) {

        if (u->store) {

            if (p->upstream_eof || p->upstream_done) {

                tf = p->temp_file;

                if (u->headers_in.status_n == NGX_HTTP_OK
                    &amp;&amp; (p->upstream_done || p->length == -1)
                    &amp;&amp; (u->headers_in.content_length_n == -1
                        || u->headers_in.content_length_n == tf->offset))
                {
                    ngx_http_upstream_store(r, u);
                    u->store = 0;
                }
            }
        }

#if (NGX_HTTP_CACHE)
    ...
    ...
#endif

        if (p->upstream_done || p->upstream_eof || p->upstream_error) {
            ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                           "http upstream exit: %p", p->out);

            if (p->upstream_done
                || (p->upstream_eof &amp;&amp; p->length == -1))
            {
                ngx_http_upstream_finalize_request(r, u, 0);
                return;
            }

            if (p->upstream_eof) {
                ngx_log_error(NGX_LOG_ERR, r->connection->log, 0,
                              "upstream prematurely closed connection");
            }

            ngx_http_upstream_finalize_request(r, u, NGX_HTTP_BAD_GATEWAY);
            return;
        }
    }

    if (p->downstream_error) {
        ngx_log_debug0(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                       "http upstream downstream error");

        if (!u->cacheable &amp;&amp; !u->store &amp;&amp; u->peer.connection) {
            ngx_http_upstream_finalize_request(r, u, NGX_ERROR);
        }
    }
}
```

### 结束 upstream 请求

结束 upstream 请求由函数 ngx_http_upstream_finalize_request 实现，该函数最终会调用 HTTP 框架的 ngx_http_finalize_request 方法来结束请求。

```c
static void
ngx_http_upstream_finalize_request(ngx_http_request_t *r,
    ngx_http_upstream_t *u, ngx_int_t rc)
{
    ngx_uint_t   flush;
    ngx_time_t  *tp;

    ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                   "finalize http upstream request: %i", rc);

    /* 将 cleanup 指向的清理资源回调方法设置为 NULL */
    if (u->cleanup) {
        *u->cleanup = NULL;
        u->cleanup = NULL;
    }
    /* 释放解析主机域名时分配的资源 */
    if (u->resolved &amp;&amp; u->resolved->ctx) {
        ngx_resolve_name_done(u->resolved->ctx);
        u->resolved->ctx = NULL;
    }

    /* 设置当前时间为 HTTP 响应结束的时间 */
    if (u->state &amp;&amp; u->state->response_sec) {
        tp = ngx_timeofday();
        u->state->response_sec = tp->sec - u->state->response_sec;
        u->state->response_msec = tp->msec - u->state->response_msec;

        if (u->pipe &amp;&amp; u->pipe->read_length) {
            u->state->response_length = u->pipe->read_length;
        }
    }

    /* 调用该方法执行一些操作 */
    u->finalize_request(r, rc);

    /* 调用 free 方法释放连接资源 */
    if (u->peer.free &amp;&amp; u->peer.sockaddr) {
        u->peer.free(&amp;u->peer, u->peer.data, 0);
        u->peer.sockaddr = NULL;
    }

    /* 若上游连接还未关闭，则调用 ngx_close_connection 方法关闭该连接 */
    if (u->peer.connection) {

#if (NGX_HTTP_SSL)
    ...
    ...
#endif

        ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                       "close http upstream connection: %d",
                       u->peer.connection->fd);

        if (u->peer.connection->pool) {
            ngx_destroy_pool(u->peer.connection->pool);
        }

        ngx_close_connection(u->peer.connection);
    }

    u->peer.connection = NULL;

    if (u->pipe &amp;&amp; u->pipe->temp_file) {
        ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                       "http upstream temp fd: %d",
                       u->pipe->temp_file->file.fd);
    }

    /* 若使用了文件缓存，则调用 ngx_delete_file 方法删除用于缓存响应的临时文件 */
    if (u->store &amp;&amp; u->pipe &amp;&amp; u->pipe->temp_file
        &amp;&amp; u->pipe->temp_file->file.fd != NGX_INVALID_FILE)
    {
        if (ngx_delete_file(u->pipe->temp_file->file.name.data)
            == NGX_FILE_ERROR)
        {
            ngx_log_error(NGX_LOG_CRIT, r->connection->log, ngx_errno,
                          ngx_delete_file_n " \"%s\" failed",
                          u->pipe->temp_file->file.name.data);
        }
    }

#if (NGX_HTTP_CACHE)
    ...
    ...
#endif

    if (r->subrequest_in_memory
        &amp;&amp; u->headers_in.status_n >= NGX_HTTP_SPECIAL_RESPONSE)
    {
        u->buffer.last = u->buffer.pos;
    }

    if (rc == NGX_DECLINED) {
        return;
    }

    r->connection->log->action = "sending to client";

    if (!u->header_sent
        || rc == NGX_HTTP_REQUEST_TIME_OUT
        || rc == NGX_HTTP_CLIENT_CLOSED_REQUEST)
    {
        ngx_http_finalize_request(r, rc);
        return;
    }

    flush = 0;

    if (rc >= NGX_HTTP_SPECIAL_RESPONSE) {
        rc = NGX_ERROR;
        flush = 1;
    }

    if (r->header_only) {
        ngx_http_finalize_request(r, rc);
        return;
    }

    if (rc == 0) {
        rc = ngx_http_send_special(r, NGX_HTTP_LAST);

    } else if (flush) {
        r->keepalive = 0;
        rc = ngx_http_send_special(r, NGX_HTTP_FLUSH);
    }

    /* 调用 HTTP 框架实现的 ngx_http_finalize_request 方法关闭请求 */
    ngx_http_finalize_request(r, rc);
}
```
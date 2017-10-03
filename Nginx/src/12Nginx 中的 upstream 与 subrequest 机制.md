### 概述

Nginx 提供了两种全异步方式与第三方服务进行通信：**upstream**和**subrequest**。upstream 在与第三方服务器交互时（包括建立TCP 连接、发送请求、接收响应、关闭TCP 连接），不会阻塞Nginx 进程处理其他请求。subrequest 只是分解复杂请求的一种设计模式，它可以把原始请求分解为多个子请求，使得诸多请求协同完成一个用户请求，并且每个请求只关注一个功能。subrequest 访问第三方服务最终也是基于upstream 实现的。

upstream 被定义为访问上游服务器，它把Nginx 定义为反代理服务器，首要功能是透传，其次才是以TCP 获取第三方服务器的内容。Nginx 的HTTP 反向代理模块是基于 upstream 方式实现的。subrequest 是子请求，也就是说subrequest 将会为用户创建子请求，即将一个复杂的请求分解为多个子请求，每个子请求负责一种功能项，而最初的原始请求负责构成并发送响应给用户。当subrequest 访问第三服务时，首先派生出子请求访问上游服务器，父请求在完全取得上游服务器的响应后再决定如何处理来自客户端的请求。

因此，若希望把是第三方服务的内容原封不动地返回给用户时，则使用 upstream 方式。若访问第三方服务是为了获取某些信息，再根据这些信息来构造响应并发给用户，则应使用 subrequest 方式。

### upstream 使用方式

upstream 模块不产生自己的内容，而是通过请求后端服务器得到内容。Nginx 内部封装了请求并取得响应内容的整个过程，所以upstream 模块只需要开发若干回调函数，完成构造请求和解析响应等具体的工作。

### ngx_http_request_t 结构体

首先了解 upstream 是如何嵌入到一个请求中，这里必须从请求结构体 ngx_http_request_t 入手，在该结构体中具有一个ngx_http_upstream_t 结构体类型的成员upstream。请求结构体 ngx_http_request_t 定义在文件 [src/http/ngx_http_request.h][0] 中如下:

    struct ngx_http_request_s {
        uint32_t                          signature;         /* "HTTP" */
    
        /* 当前请求所对应的客户端连接 */
        ngx_connection_t                 *connection;
    
        /*
         * 以下四个成员是保存模块对应的上下文结构指针；
         * ctx 对应的是自定义的上下文结构指针数组，若是HTTP框架，则存储所有HTTP模块上下文结构；
         * main_conf 对应的是main级别配置结构体的指针数组；
         * srv_conf 对应的是srv级别配置结构体的指针数组；
         * loc_conf 对应的是loc级别配置结构体的指针数组；
         */
        void                            **ctx;
        void                            **main_conf;
        void                            **srv_conf;
        void                            **loc_conf;
    
        /*
         * 以下两个是处理http请求；
         * 当http头部接收完毕，第一次在业务上处理http请求时，http框架提供的处理方法是ngx_http_process_request;
         * 若该方法无法一次性处理完该请求的全部业务时，当控制权归还给epoll事件模块后，若该请求再次被回调时，
         * 此时，将通过ngx_http_request_handler方法进行处理，而这个方法中对于可读或可写事件的处理就是由函数
         * read_event_handler或write_event_handler 来处理请求；
         */
        ngx_http_event_handler_pt         read_event_handler;
        ngx_http_event_handler_pt         write_event_handler;
    
    #if (NGX_HTTP_CACHE)
        ngx_http_cache_t                 *cache;
    #endif
    
        /* 若使用upstream机制，则需要以下的结构体 */
        ngx_http_upstream_t              *upstream;
        ngx_array_t                      *upstream_states;
                                             /* of ngx_http_upstream_state_t */
    
        /* 当前请求的内存池 */
        ngx_pool_t                       *pool;
        /* 主要用于接收http请求头部内容的缓冲区 */
        ngx_buf_t                        *header_in;
    
        /*
         * 调用函数ngx_http_request_headers 接收并解析http请求头部完毕后，
         * 则把解析完成的每一个http头部加入到结构体headers_in的成员headers链表中，
         * 同时初始化该结构体的其他成员；
         */
        ngx_http_headers_in_t             headers_in;
        /*
         * http模块将待发送的http响应的信息存放在headers_out中，
         * 并期望http框架将headers_out中的成员序列化为http响应包体发送个客户端；
         */
        ngx_http_headers_out_t            headers_out;
    
        /* 接收请求包体的数据结构 */
        ngx_http_request_body_t          *request_body;
    
        /* 延迟关闭连接的时间 */
        time_t                            lingering_time;
        /* 当前请求初始化的时间 */
        time_t                            start_sec;
        /* 相对于start_sec的毫秒偏移量 */
        ngx_msec_t                        start_msec;
    
        /*
         * 以下的 9 个成员是函数ngx_http_process_request_line在接收、解析http请求行时解析出的信息 */
        ngx_uint_t                        method;       /* 方法名称 */
        ngx_uint_t                        http_version; /* 协议版本 */
    
        ngx_str_t                         request_line; /* 请求行 */
        ngx_str_t                         uri;          /* 客户请求中的uri */
        ngx_str_t                         args;         /* uri 中的参数 */
        ngx_str_t                         exten;        /* 客户请求的文件扩展名 */
        ngx_str_t                         unparsed_uri; /* 没经过URI 解码的原始请求 */
    
        ngx_str_t                         method_name;  /* 方法名称字符串 */
        ngx_str_t                         http_protocol;/* 其data成员指向请求中http的起始地址 */
    
        /*
         * 存储待发送给客户的http响应；
         * out保存着由headers_out序列化后的表示http头部的TCP流；
         * 调用ngx_http_output_filter方法后，out还保存这待发送的http包体；
         */
        ngx_chain_t                      *out;
        /*
         * 当前请求可能是用户请求，或是派生的子请求；
         * main标识一序列相关的派生子请求的原始请求；
         * 即通过main与当前请求的地址对比来判断是用户请求还是派生子请求；
         */
        ngx_http_request_t               *main;
        /*
         * 当前请求的父亲请求，但不一定是原始请求 */
        ngx_http_request_t               *parent;
        /* 以下两个是与subrequest子请求相关的功能 */
        ngx_http_postponed_request_t     *postponed;
        ngx_http_post_subrequest_t       *post_subrequest;
        /* 连接子请求的链表 */
        ngx_http_posted_request_t        *posted_requests;
    
        /*
         * 全局结构体ngx_http_phase_engine_t定义了一个ngx_http_phase_handler_t回调方法的数组；
         * 而这里的phase_handler作为该数组的序列号表示指定数组中的回调方法，相当于数组的下标；
         */
        ngx_int_t                         phase_handler;
        /*
         * 表示NGX_HTTP_CONTENT_PHASE阶段提供给http模块请求的一种方式，它指向http模块实现的请求处理方法 */
        ngx_http_handler_pt               content_handler;
        /*
         * 在NGX——HTTP_CONTENT_PHASE阶段需要判断请求是否具有访问权限时，
         * 可通过access_code来传递http模块的handler回调方法的返回值来判断，
         * 若为0表示具备权限，否则不具备；
         */
        ngx_uint_t                        access_code;
    
        ngx_http_variable_value_t        *variables;
    
    #if (NGX_PCRE)
        ngx_uint_t                        ncaptures;
        int                              *captures;
        u_char                           *captures_data;
    #endif
    
        /* 限制当前请求的发送的速率 */
        size_t                            limit_rate;
        size_t                            limit_rate_after;
    
        /* http响应的长度，不包括http响应头部 */
        /* used to learn the Apache compatible response length without a header */
        size_t                            header_size;
    
        /* http请求的长度，包括http请求头部、http请求包体 */
        off_t                             request_length;
    
        /* 表示错误状态标志 */
        ngx_uint_t                        err_status;
    
        /* http 连接 */
        ngx_http_connection_t            *http_connection;
    #if (NGX_HTTP_SPDY)
        ngx_http_spdy_stream_t           *spdy_stream;
    #endif
    
        /* http日志处理函数 */
        ngx_http_log_handler_pt           log_handler;
    
        /* 释放资源 */
        ngx_http_cleanup_t               *cleanup;
    
        /* 以下都是一些标志位 */
        /* 派生子请求 */
        unsigned                          subrequests:8;
        /* 作为原始请求的引用计数，每派生一个子请求，原始请求的成员count会增加1 */
        unsigned                          count:8;
        /* 阻塞标志位，仅用于aio */
        unsigned                          blocked:8;
    
        /* 标志位：为1表示当前请求是异步IO方式 */
        unsigned                          aio:1;
    
        unsigned                          http_state:4;
    
        /* URI with "/." and on Win32 with "//" */
        unsigned                          complex_uri:1;
    
        /* URI with "%" */
        unsigned                          quoted_uri:1;
    
        /* URI with "+" */
        unsigned                          plus_in_uri:1;
    
        /* URI with " " */
        unsigned                          space_in_uri:1;
    
        unsigned                          invalid_header:1;
    
        unsigned                          add_uri_to_alias:1;
        unsigned                          valid_location:1;
        unsigned                          valid_unparsed_uri:1;
        /* 标志位：为1表示URI已经被重写 */
        unsigned                          uri_changed:1;
        /* 表示URI被重写的次数 */
        unsigned                          uri_changes:4;
    
        unsigned                          request_body_in_single_buf:1;
        unsigned                          request_body_in_file_only:1;
        unsigned                          request_body_in_persistent_file:1;
        unsigned                          request_body_in_clean_file:1;
        unsigned                          request_body_file_group_access:1;
        unsigned                          request_body_file_log_level:3;
    
        /* 决定是否转发响应，若该标志位为1，表示不转发响应，否则转发响应 */
        unsigned                          subrequest_in_memory:1;
        unsigned                          waited:1;
    
    #if (NGX_HTTP_CACHE)
        unsigned                          cached:1;
    #endif
    
    #if (NGX_HTTP_GZIP)
        unsigned                          gzip_tested:1;
        unsigned                          gzip_ok:1;
        unsigned                          gzip_vary:1;
    #endif
    
        unsigned                          proxy:1;
        unsigned                          bypass_cache:1;
        unsigned                          no_cache:1;
    
        /*
         * instead of using the request context data in
         * ngx_http_limit_conn_module and ngx_http_limit_req_module
         * we use the single bits in the request structure
         */
        unsigned                          limit_conn_set:1;
        unsigned                          limit_req_set:1;
    
    #if 0
        unsigned                          cacheable:1;
    #endif
    
        unsigned                          pipeline:1;
        unsigned                          chunked:1;
        unsigned                          header_only:1;
        /* 标志位，为1表示当前请求是keepalive模式请求 */
        unsigned                          keepalive:1;
        /* 延迟关闭标志位，为1表示需要延迟关闭 */
        unsigned                          lingering_close:1;
        /* 标志位，为1表示正在丢弃HTTP请求中的包体 */
        unsigned                          discard_body:1;
        /* 标志位，为1表示请求的当前状态是在做内部跳转 */
        unsigned                          internal:1;
        unsigned                          error_page:1;
        unsigned                          ignore_content_encoding:1;
        unsigned                          filter_finalize:1;
        unsigned                          post_action:1;
        unsigned                          request_complete:1;
        unsigned                          request_output:1;
        /* 标志位，为1表示待发送的HTTP响应头部已发送给客户端 */
        unsigned                          header_sent:1;
        unsigned                          expect_tested:1;
        unsigned                          root_tested:1;
        unsigned                          done:1;
        unsigned                          logged:1;
    
        /* 标志位，表示缓冲区是否存在待发送内容 */
        unsigned                          buffered:4;
    
        unsigned                          main_filter_need_in_memory:1;
        unsigned                          filter_need_in_memory:1;
        unsigned                          filter_need_temporary:1;
        unsigned                          allow_ranges:1;
        unsigned                          single_range:1;
    
    #if (NGX_STAT_STUB)
        unsigned                          stat_reading:1;
        unsigned                          stat_writing:1;
    #endif
    
        /* used to parse HTTP headers */
    
        /* 当前的解析状态 */
        ngx_uint_t                        state;
    
        ngx_uint_t                        header_hash;
        ngx_uint_t                        lowcase_index;
        u_char                            lowcase_header[NGX_HTTP_LC_HEADER_LEN];
    
        u_char                           *header_name_start;
        u_char                           *header_name_end;
        u_char                           *header_start;
        u_char                           *header_end;
    
        /*
         * a memory that can be reused after parsing a request line
         * via ngx_http_ephemeral_t
         */
    
        u_char                           *uri_start;
        u_char                           *uri_end;
        u_char                           *uri_ext;
        u_char                           *args_start;
        u_char                           *request_start;
        u_char                           *request_end;
        u_char                           *method_end;
        u_char                           *schema_start;
        u_char                           *schema_end;
        u_char                           *host_start;
        u_char                           *host_end;
        u_char                           *port_start;
        u_char                           *port_end;
    
        unsigned                          http_minor:16;
        unsigned                          http_major:16;
    };
    
    

若没有实现 upstream 机制，则请求结构体 ngx_http_request_t 中的upstream成员设置为NULL，否则必须设置该成员。首先看下 HTTP 模块启动upstream 机制的过程：

1. 调用函数 ngx_http_upstream_create 为请求创建upstream；
1. 设置上游服务器的地址；可通过配置文件 nginx.conf 配置好上游服务器地址；也可以通过ngx_http_request_t 中的成员resolved 设置上游服务器地址；
1. 设置 upstream 的回调方法；
1. 调用函数 ngx_http_upstream_init 启动upstream；
upstream 启动过程如下图所示：

![][1]

### ngx_http_upstream_t 结构体

upstream 结构体是 ngx_http_upstream_t，该结构体只在 upstream 模块内部使用，其定义在文件：[src/http/ngx_http_upstream.h][2]

    /* ngx_http_upstream_t 结构体 */
    struct ngx_http_upstream_s {
        /* 处理读事件的回调方法，每一个阶段都有不同的 read_event_handler */
        ngx_http_upstream_handler_pt     read_event_handler;
        /* 处理写事件的回调方法，每一个阶段都有不同的 write_event_handler */
        ngx_http_upstream_handler_pt     write_event_handler;
    
        /* 表示主动向上游服务器发起的连接 */
        ngx_peer_connection_t            peer;
    
        /*
         * 当向 下游客户端转发响应时（此时，ngx_http_request_t 结构体中的subrequest_in_memory标志位为0），
         * 若已打开缓存且认为上游网速更快，此时会使用pipe成员来转发响应；
         * 使用这种方式转发响应时，在HTTP模块使用upstream机制前必须构造pipe结构体；
         */
        ngx_event_pipe_t                *pipe;
    
        /* 发送给上游服务器的请求，在实现create_request方法时需设置它 */
        ngx_chain_t                     *request_bufs;
    
        /* 定义了向下游发送响应的方式 */
        ngx_output_chain_ctx_t           output;
        ngx_chain_writer_ctx_t           writer;
    
        /* 指定upstream机制的运行方式 */
        ngx_http_upstream_conf_t        *conf;
    
        /*
         * HTTP模块实现process_header方法时，若希望upstream直接转发响应，
         * 则需把解析出来的响应头部适配为HTTP的响应头部，同时需要把包头中的
         * 信息设置到headers_in结构体中
         */
        ngx_http_upstream_headers_in_t   headers_in;
    
        /* 解析主机域名，用于直接指定的上游服务器地址 */
        ngx_http_upstream_resolved_t    *resolved;
    
        /* 接收客户信息的缓冲区 */
        ngx_buf_t                        from_client;
    
        /*
         * 接收上游服务器响应包头的缓冲区，当不直接把响应转发给客户端，
         * 或buffering标志位为0的情况转发包体时，接收包体的缓冲区仍然使用buffer
         */
        ngx_buf_t                        buffer;
        off_t                            length;
    
        /*
         * out_bufs有两种不同意义：
         * 1、当不需要转发包体，且默认使用input_filter方法处理包体时，
         *    out_bufs将会指向响应包体，out_bufs链表中产生多个ngx_but_t缓冲区，
         *    每个缓冲区都指向buffer缓存中的一部分，而这里只是调用recv方法接收到的一段TCP流；
         * 2、当需要向下游转发包体时，这个链表指向上一次向下游转发响应到现在这段时间内接收自上游的缓存响应；
         */
        ngx_chain_t                     *out_bufs;
        /*
         * 当需要向下游转发响应包体时，它表示上一次向下游转发响应时没有发送完的内容；
         */
        ngx_chain_t                     *busy_bufs;
        /*
         * 这个链表用于回收out_bufs中已经发送给下游的ngx_buf_t结构体；
         */
        ngx_chain_t                     *free_bufs;
    
        /*
         * 处理包体前的初始化方法；
         * 其中data参数用于传递用户数据结构，就是下面成员input_filter_ctx
         */
        ngx_int_t                      (*input_filter_init)(void *data);
        /*
         * 处理包体的方法；
         * 其中data参数用于传递用户数据结构，就是下面成员input_filter_ctx，
         * bytes表示本次接收到包体的长度；
         */
        ngx_int_t                      (*input_filter)(void *data, ssize_t bytes);
        /* 用于传递HTTP自定义的数据结构 */
        void                            *input_filter_ctx;
    
    #if (NGX_HTTP_CACHE)
        ngx_int_t                      (*create_key)(ngx_http_request_t *r);
    #endif
        /* HTTP模块实现的create_request方法用于构造发往上游服务器的请求 */
        ngx_int_t                      (*create_request)(ngx_http_request_t *r);
        /* 与上游服务器的通信失败后，若想再次向上游服务器发起连接，则调用该函数 */
        ngx_int_t                      (*reinit_request)(ngx_http_request_t *r);
        /*
         * 解析上游服务器返回的响应包头，该函数返回四个值中的一个：
         * NGX_AGAIN                            表示包头没有接收完整；
         * NGX_HTTP_UPSTREAM_INVALID_HEADER     表示包头不合法；
         * NGX_ERROR                            表示出现错误；
         * NGX_OK                               表示解析到完整的包头；
         */
        ngx_int_t                      (*process_header)(ngx_http_request_t *r);
        /* 当客户端放弃请求时被调用，由于系统会自动关闭连接，因此，该函数不会进行任何具体操作 */
        void                           (*abort_request)(ngx_http_request_t *r);
        /* 结束upstream请求时会调用该函数 */
        void                           (*finalize_request)(ngx_http_request_t *r,
                                             ngx_int_t rc);
        /*
         * 在上游返回的响应出现location或者refresh头部表示重定向时，
         * 会通过ngx_http_upstream_process_headers方法调用到可由HTTP模块
         * 实现的rewrite_redirect方法；
         */
        ngx_int_t                      (*rewrite_redirect)(ngx_http_request_t *r,
                                             ngx_table_elt_t *h, size_t prefix);
        ngx_int_t                      (*rewrite_cookie)(ngx_http_request_t *r,
                                             ngx_table_elt_t *h);
    
        ngx_msec_t                       timeout;
    
        /* 用于表示上游响应的状态：错误编码、包体长度等信息 */
        ngx_http_upstream_state_t       *state;
    
        ngx_str_t                        method;
        /* 用于记录日志 */
        ngx_str_t                        schema;
        ngx_str_t                        uri;
    
        /* 清理资源 */
        ngx_http_cleanup_pt             *cleanup;
    
        /* 以下是一些标志位 */
    
        /* 指定文件缓存路径 */
        unsigned                         store:1;
        /* 启用文件缓存 */
        unsigned                         cacheable:1;
        unsigned                         accel:1;
        /* 基于ssl协议访问上游服务器 */
        unsigned                         ssl:1;
    #if (NGX_HTTP_CACHE)
        unsigned                         cache_status:3;
    #endif
    
        /* 开启更大的内存及临时磁盘文件用于缓存来不及发送到下游的响应包体 */
        unsigned                         buffering:1;
        /* keepalive机制 */
        unsigned                         keepalive:1;
        unsigned                         upgrade:1;
    
        /* 表示是否已向上游服务器发送请求 */
        unsigned                         request_sent:1;
        /* 表示是否已经转发响应报头 */
        unsigned                         header_sent:1;
    };
    
    

下面看下 upstream 处理上游响应包体的三种方式：

1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 1 时，upstream 不转发响应包体到下游，并由HTTP 模块实现的 input_filter() 方法处理包体；
1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 0 时，且ngx_http_upstream_conf_t 配置结构体中的成员buffering 标志位为 1 时，upstream 将开启更多的内存和磁盘文件用于缓存上游的响应包体（此时，上游网速更快），并转发响应包体；
1. 当请求结构体 ngx_http_request_t 中的成员subrequest_in_memory 标志位为 0 时，且ngx_http_upstream_conf_t 配置结构体中的成员buffering 标志位为 0 时，upstream 将使用固定大小的缓冲区来转发响应包体；
### ngx_http_upstream_conf_t 结构体

在结构体 ngx_http_upstream_t 的成员conf 中，conf 是一个结构体ngx_http_upstream_conf_t 变量，该变量设置了upstream 的限制性参数。ngx_http_upstream_conf_t 结构体定义如下：[src/http/ngx_http_upstream.h][2]

    /* ngx_http_upstream_conf_t 结构体 */
    typedef struct {
        /*
         * 若在ngx_http_upstream_t结构体中没有实现resolved成员时，
         * upstream这个结构体才会生效，定义上游服务器的配置；
         */
        ngx_http_upstream_srv_conf_t    *upstream;
    
        /* 建立TCP连接的超时时间 */
        ngx_msec_t                       connect_timeout;
        /* 发送请求的超时时间 */
        ngx_msec_t                       send_timeout;
        /* 接收响应的超时时间 */
        ngx_msec_t                       read_timeout;
        ngx_msec_t                       timeout;
    
        /* TCP的SO_SNOLOWAT选项，表示发送缓冲区的下限 */
        size_t                           send_lowat;
        /* ngx_http_upstream_t中的buffer大小 */
        size_t                           buffer_size;
    
        size_t                           busy_buffers_size;
        /* 临时文件的最大长度 */
        size_t                           max_temp_file_size;
        /* 表示缓冲区中的响应写入到临时文件时一次写入字符流的最大长度 */
        size_t                           temp_file_write_size;
    
        size_t                           busy_buffers_size_conf;
        size_t                           max_temp_file_size_conf;
        size_t                           temp_file_write_size_conf;
    
        /* 以缓存响应的方式转发上游服务器的包体时所使用的内存大小 */
        ngx_bufs_t                       bufs;
    
        /* ignore_headers使得upstream在转发包头时跳过对某些头部的处理 */
        ngx_uint_t                       ignore_headers;
        /*
         * 以二进制位来处理错误码，若处理上游响应时发现这些错误码，
         * 那么在没有将响应转发给下游客户端时，将会选择一个上游服务器来重发请求；
         */
        ngx_uint_t                       next_upstream;
        /* 表示所创建的目录与文件的权限 */
        ngx_uint_t                       store_access;
        /*
         * 转发响应方式的标志位，为1表示启用更多内存和磁盘文件缓存来自上游响应(即上游网速优先)；
         * 若为0，则启用固定内存大小缓存上游响应(即下游网速优先)；
         */
        ngx_flag_t                       buffering;
        ngx_flag_t                       pass_request_headers;
        ngx_flag_t                       pass_request_body;
    
        /* 不检查Nginx与下游之间的连接是否断开 */
        ngx_flag_t                       ignore_client_abort;
        ngx_flag_t                       intercept_errors;
        /* 复用临时文件中已使用过的空间 */
        ngx_flag_t                       cyclic_temp_file;
    
        /* 存放临时文件的目录 */
        ngx_path_t                      *temp_path;
    
        /* 不转发的头部 */
        ngx_hash_t                       hide_headers_hash;
        /*
         * 当转发上游响应头部到下游客户端时，
         * 若不希望将某些头部转发，则设置在这个数组中
         */
        ngx_array_t                     *hide_headers;
        /*
         * 当转发上游响应头部到下游客户端时，
         * 若希望将某些头部转发，则设置在这个数组中
         */
        ngx_array_t                     *pass_headers;
    
        /* 连接上游服务器的本机地址 */
        ngx_http_upstream_local_t       *local;
    
    #if (NGX_HTTP_CACHE)
        ngx_shm_zone_t                  *cache;
    
        ngx_uint_t                       cache_min_uses;
        ngx_uint_t                       cache_use_stale;
        ngx_uint_t                       cache_methods;
    
        ngx_flag_t                       cache_lock;
        ngx_msec_t                       cache_lock_timeout;
    
        ngx_flag_t                       cache_revalidate;
    
        ngx_array_t                     *cache_valid;
        ngx_array_t                     *cache_bypass;
        ngx_array_t                     *no_cache;
    #endif
    
        /*
         * 当ngx_http_upstream_t 中的store标志位为1时，
         * 如果需要将上游的响应存放在文件中，
         * store_lengths表示存放路径的长度；
         * store_values表示存放路径；
         */
        ngx_array_t                     *store_lengths;
        ngx_array_t                     *store_values;
    
        /* 文件缓存的路径 */
        signed                           store:2;
        /* 直接将上游返回的404错误码转发给下游 */
        unsigned                         intercept_404:1;
        /* 根据返回的响应头部，动态决定是以上游网速还是下游网速优先 */
        unsigned                         change_buffering:1;
    
    #if (NGX_HTTP_SSL)
        ngx_ssl_t                       *ssl;
        ngx_flag_t                       ssl_session_reuse;
    #endif
    
        /* 使用upstream的模块名称，仅用于记录日志 */
        ngx_str_t                        module;
    } ngx_http_upstream_conf_t;
    
    

在 HTTP 反向代理模块在配置文件 nginx.conf 提供的配置项大都是用来设置结构体 ngx_http_upstream_conf_t 的成员。3 个超时时间成员是必须要设置的，因为他们默认是 0，即若不设置这 3 个成员，则无法与上游服务器建立TCP 连接。每一个请求都有独立的ngx_http_upstream_conf_t 结构体，因此，每个请求都可以拥有不同的网络超时时间等配置。

例如，将 nginx.conf 文件中的 upstream_conn_timeout 配置项解析到 ngx_http_hello_conf_t 结构体中的成员upstream.conn_timeout 中。可定义如下的连接超时时间，并把ngx_http_hello_conf_t 配置项的 upstream 成员赋给 ngx_http_upstream_t 中的conf 即可；

    typedef struct  
    {  
            ... 
            ngx_http_upstream_conf_t    upstream;
    }ngx_http_hello_conf_t;  
      
      
    static ngx_command_t ngx_http_hello_commands[] = {  
       {  
                    ngx_string("upstream_conn_timeout"),  
                    NGX_HTTP_LOC_CONF|NGX_CONF_TAKE1,  
                    ngx_conf_set_msec_slot,  
                    NGX_HTTP_LOC_CONF_OFFSET,  
                    offsetof(ngx_http_hello_conf_t, upstream.conn_timeout),  
                    NULL },  
      
      
            ngx_null_command  
    };  
    /* 在 ngx_http_hello_handler 方法中如下定义 */
      
    static ngx_int_t  
    ngx_http_hello_handler(ngx_http_request_t *r)  
    {  
         ...
         ngx_http_hello_conf_t *mycf = (ngx_http_hello_conf_t *)ngx_http_get_module_loc_conf(r,ngx_http_hello_module);
         r->upstream->conf = &mycf->upstream;
         ...
    }
    
    

### 设置第三方服务器地址

在 ngx_http_upstream_t 结构体中的resolved 成员可直接设置上游服务器的地址，也可以由nginx.conf 文件中配置upstream 模块，并指定上游服务器的地址。resolved 类型定义如下：

    typedef struct {
        /* 主机名称 */
        ngx_str_t                        host;
        /* 端口号 */
        in_port_t                        port;
        ngx_uint_t                       no_port; /* unsigned no_port:1 */
    
        /* 地址个数 */
        ngx_uint_t                       naddrs;
        /* 地址 */
        ngx_addr_t                      *addrs;
    
        /* 上游服务器地址 */
        struct sockaddr                 *sockaddr;
        /* 上游服务器地址长度 */
        socklen_t                        socklen;
    
        ngx_resolver_ctx_t              *ctx;
    } ngx_http_upstream_resolved_t;
    
    

### 设置回调方法

在结构体 ngx_http_upstream_t 中定义了 8 个回调方法：

        /*
         * 处理包体前的初始化方法；
         * 其中data参数用于传递用户数据结构，就是下面成员input_filter_ctx
         */
        ngx_int_t                      (*input_filter_init)(void *data);
        /*
         * 处理包体的方法；
         * 其中data参数用于传递用户数据结构，就是下面成员input_filter_ctx，
         * bytes表示本次接收到包体的长度；
         */
        ngx_int_t                      (*input_filter)(void *data, ssize_t bytes);
        /* 用于传递HTTP自定义的数据结构 */
        void                            *input_filter_ctx;
    
        /* HTTP模块实现的create_request方法用于构造发往上游服务器的请求 */
        ngx_int_t                      (*create_request)(ngx_http_request_t *r);
        /* 与上游服务器的通信失败后，若想再次向上游服务器发起连接，则调用该函数 */
        ngx_int_t                      (*reinit_request)(ngx_http_request_t *r);
        /*
         * 解析上游服务器返回的响应包头，该函数返回四个值中的一个：
         * NGX_AGAIN                            表示包头没有接收完整；
         * NGX_HTTP_UPSTREAM_INVALID_HEADER     表示包头不合法；
         * NGX_ERROR                            表示出现错误；
         * NGX_OK                               表示解析到完整的包头；
         */
        ngx_int_t                      (*process_header)(ngx_http_request_t *r);
        /* 当客户端放弃请求时被调用，由于系统会自动关闭连接，因此，该函数不会进行任何具体操作 */
        void                           (*abort_request)(ngx_http_request_t *r);
        /* 结束upstream请求时会调用该函数 */
        void                           (*finalize_request)(ngx_http_request_t *r,
                                             ngx_int_t rc);
        /*
         * 在上游返回的响应出现location或者refresh头部表示重定向时，
         * 会通过ngx_http_upstream_process_headers方法调用到可由HTTP模块
         * 实现的rewrite_redirect方法；
         */
        ngx_int_t                      (*rewrite_redirect)(ngx_http_request_t *r,
                                             ngx_table_elt_t *h, size_t prefix);
    
    

在这些回调方法中，其中有 3 个非常重要，在模块中是必须要实现的，这 3 个回调函数为：

    /* HTTP模块实现的create_request方法用于构造发往上游服务器的请求 */
        ngx_int_t                      (*create_request)(ngx_http_request_t *r);
    /*
         * 解析上游服务器返回的响应包头，该函数返回四个值中的一个：
         * NGX_AGAIN                            表示包头没有接收完整；
         * NGX_HTTP_UPSTREAM_INVALID_HEADER     表示包头不合法；
         * NGX_ERROR                            表示出现错误；
         * NGX_OK                               表示解析到完整的包头；
         */
        ngx_int_t                      (*process_header)(ngx_http_request_t *r);
    /* 结束upstream请求时会调用该函数 */
        void                           (*finalize_request)(ngx_http_request_t *r,
                                             ngx_int_t rc);
    
    

create_request 在初始化 upstream 时被调用，生成发送到后端服务器的请求缓冲（缓冲链）。reinit_request 在某台后端服务器出错的情况，Nginx 会尝试连接到另一台后端服务器。Nginx 选定新的服务器以后，会先调用此函数，以重新初始化upstream 模块的工作状态，然后再次进行 upstream 连接。process_header 是用于解析上游服务器返回的基于TCP 的响应头部。finalize_request 在正常完成与后端服务器的请求后 或 失败 导致销毁请求时，该方法被调用。input_filter_init 和input_filter 都用于处理上游的响应包体，因为在处理包体前HTTP 模块可能需要做一些初始化工作。初始化工作由input_filter_init 完成，实际处理包体由 input_filter 方法完成。

### 启动 upstream 机制

调用 ngx_http_upstream_init 方法便可启动upstream 机制，此时，必须通过返回NGX_DONE 通知HTTP 框架暂停执行请求的下一个阶段，并且需要执行r->main->count++ 告知HTTP 框架将当前请求的引用计数增加 1，即告知ngx_http_hello_handler 方法暂时不要销毁请求，因为HTTP 框架只有在引用计数为 0 时才真正销毁请求。例如：

    static ngx_int_t ngx_http_hello_handler(ngx_http_request_t *r)
    {
        ...
        r->main->count++;
        ngx_http_upstream_init(r);
        return NGX_DONE;
    }
    
    

### subrequest 使用方式

subrequest 只是分解复杂请求的一种设计模式，它可以把原始请求分解为多个子请求，使得诸多请求协同完成一个用户请求，并且每个请求只关注一个功能。首先，若不是完全将上游服务器的响应包体转发到下游客户端，基本都会使用subrequest 创建子请求，并由子请求使用upstream 机制访问上游服务器，然后由父请求根据上游响应重新构造返回给下游客户端的响应。

subrequest 的使用步骤如下：

1. 在 nginx.conf 配置文件中配置好子请求的处理方式；
1. 启动 subrequest 子请求；
1. 实现子请求执行结束时的回调函数；
1. 实现父请求被激活时的回调函数；
### 配置子请求的处理方式

子请求并不是由 HTTP 框架解析所接收到客户端网络包而得到的，而是由父请求派生的。它的配置和普通请求的配置相同，都是在nginx.conf 文件中配置相应的处理模块。例如：可以在配置文件nginx.conf 中配置以下的子请求访问 [https://github.com][3]

    location /subrq { 
            rewrite ^/subrq(.*)$ $1 break;
            proxy_pass https://github.com;
        }
    
    

### 启动 subrequest 子请求

subrequest 是在父请求的基础上派生的子请求，subrequest 返回的内容会被附加到父请求上面，他的实现方法是调用ngx_http_subrequest 函数，该函数定义在文件：[src/http/ngx_http_core_module.h][4]

    ngx_int_t ngx_http_subrequest(ngx_http_request_t *r,
         ngx_str_t *uri, ngx_str_t *args, ngx_http_request_t **psr,
         ngx_http_post_subrequest_t *ps, ngx_uint_t flags);
    
    

该函数的参数如下：引用自文件《[Emiller's Advanced Topics In Nginx Module Development][5]》

* *r is the original request（当前的请求，即父请求）；
* _uri and_argsrefer to the sub-request（*uri 是子请求的URI，*args是子请求URI 的参数）；
* **psr is a reference to a NULL pointer that will point to the new (sub-)request structure（**psr 是指向返回子请求，相当于值-结果传递，作为参数传递进去是指向 NULL 指针，输出结果是指向新创建的子请求）；
* *ps is a callback for when the subrequest is finished. （*ps 是指出子请求结束时必须回调的处理方法）；
* flags can be a bitwise-OR'ed combination of:
* NGX_HTTP_ZERO_IN_URI: the URI contains a character with ASCII code 0 (also known as '\0'), or contains "%00"
* NGX_HTTP_SUBREQUEST_IN_MEMORY: store the result of the subrequest in a contiguous chunk of memory (usually not necessary) （将子请求的subrequest_in_memory 标志位为 1，表示发起的子请求，访问的网络资源返回的响应将全部在内存中处理）；
* NGX_HTTP_SUBREQUEST_WAITED: store the result of the subrequest in a contiguous chunk of memory (usually not necessary) （将子请求的waited 标志位为 1，表示子请求完成后会设置自身的r->done 标志位，可以通过判断该标志位得知子请求是否完成）；
该函数 ngx_http_subrequest 的返回值如下：

* NGX_OK:the subrequest finished without touching the network（成功建立子请求）；
* NGX_DONE:the client reset the network connection（客户端重置网络连接）；
* NGX_ERROR:there was a server error of some sort（建立子请求失败）；
* NGX_AGAIN:the subrequest requires network activity（子请求需要激活网络）；
该子请求返回的结果附加在你期望的位置。若要修改子请求的结果，可以使用 another filter（或同一个）。并告知该 filter 对父请求或子请求进行操作：具体实例可参照模块["addition" module][6]

    if (r == r->main) { 
        /* primary request */
    } else {
        /* subrequest */
    }
    
    

以下是子请求函数 ngx_http_subrequest 的源码剖析，其源码定义在文件：[src/http/ngx_http_core_module.c][7]

    /* ngx_http_subrequest 函数 */
    ngx_int_t
    ngx_http_subrequest(ngx_http_request_t *r,
        ngx_str_t *uri, ngx_str_t *args, ngx_http_request_t **psr,
        ngx_http_post_subrequest_t *ps, ngx_uint_t flags)
    {
        ngx_time_t                    *tp;
        ngx_connection_t              *c;
        ngx_http_request_t            *sr;
        ngx_http_core_srv_conf_t      *cscf;
        ngx_http_postponed_request_t  *pr, *p;
    
        /* 原始请求的子请求减少一个 */
        r->main->subrequests--;
    
        /* 若没有子请求则出错返回 */
        if (r->main->subrequests == 0) {
            ngx_log_error(NGX_LOG_ERR, r->connection->log, 0,
                          "subrequests cycle while processing \"%V\"", uri);
            r->main->subrequests = 1;
            return NGX_ERROR;
        }
    
        /* 分配内存sr */
        sr = ngx_pcalloc(r->pool, sizeof(ngx_http_request_t));
        if (sr == NULL) {
            return NGX_ERROR;
        }
    
        /* 设置为 HTTP 模块 */
        sr->signature = NGX_HTTP_MODULE;
    
        /* 设置sr的客户端连接 */
        c = r->connection;
        sr->connection = c;
    
        /* 为自定义上下文结构分配内存 */
        sr->ctx = ngx_pcalloc(r->pool, sizeof(void *) * ngx_http_max_module);
        if (sr->ctx == NULL) {
            return NGX_ERROR;
        }
    
        /* 初始化headers链表，该链表存储待发送的http响应包体 */
        if (ngx_list_init(&sr->headers_out.headers, r->pool, 20,
                          sizeof(ngx_table_elt_t))
            != NGX_OK)
        {
            return NGX_ERROR;
        }
    
        /* 设置main、server、location级别的配置结构体指针 */
        cscf = ngx_http_get_module_srv_conf(r, ngx_http_core_module);
        sr->main_conf = cscf->ctx->main_conf;
        sr->srv_conf = cscf->ctx->srv_conf;
        sr->loc_conf = cscf->ctx->loc_conf;
    
        /* 设置内存池 */
        sr->pool = r->pool;
    
        /* 设置headers_in成员，该成员保存解析完成的http头部 */
        sr->headers_in = r->headers_in;
    
        ngx_http_clear_content_length(sr);
        ngx_http_clear_accept_ranges(sr);
        ngx_http_clear_last_modified(sr);
    
        /* 设置接收请求包体的数据结构 */
        sr->request_body = r->request_body;
    
    #if (NGX_HTTP_SPDY)
        sr->spdy_stream = r->spdy_stream;
    #endif
    
        /* 请求的方法名称 */
        sr->method = NGX_HTTP_GET;
        /* 请求协议的版本 */
        sr->http_version = r->http_version;
    
        /* 请求行 */
        sr->request_line = r->request_line;
        /* 请求中的uri */
        sr->uri = *uri;
    
        /* uri中的参数 */
        if (args) {
            sr->args = *args;
        }
    
        ngx_log_debug2(NGX_LOG_DEBUG_HTTP, c->log, 0,
                       "http subrequest \"%V?%V\"", uri, &sr->args);
    
        /* 标志位 */
        sr->subrequest_in_memory = (flags & NGX_HTTP_SUBREQUEST_IN_MEMORY) != 0;
        sr->waited = (flags & NGX_HTTP_SUBREQUEST_WAITED) != 0;
    
        sr->unparsed_uri = r->unparsed_uri;
        sr->method_name = ngx_http_core_get_method;
        sr->http_protocol = r->http_protocol;
    
        ngx_http_set_exten(sr);
    
        /* 原始请求 */
        sr->main = r->main;
        sr->parent = r;/* 当前请求，即新创建子请求的父请求 */
        sr->post_subrequest = ps;/* 子请求执行结束时，执行的回调方法 */
        /* http请求的可读或可写事件的处理方法 */
        sr->read_event_handler = ngx_http_request_empty_handler;
        sr->write_event_handler = ngx_http_handler;
    
        /* 保存当前可以向out chain输出数组的请求 */
        if (c->data == r && r->postponed == NULL) {
            c->data = sr;
        }
    
        /* 默认共享父请求的变量，也可以根据需求创建完子请求后，再创建子请求独立的变量集 */
        sr->variables = r->variables;
    
        /* 日志处理方法 */
        sr->log_handler = r->log_handler;
    
        pr = ngx_palloc(r->pool, sizeof(ngx_http_postponed_request_t));
        if (pr == NULL) {
            return NGX_ERROR;
        }
    
        pr->request = sr;
        pr->out = NULL;
        pr->next = NULL;
    
        /* 把该子请求挂载到其父请求的postponed链表队尾 */
        if (r->postponed) {
            for (p = r->postponed; p->next; p = p->next) { /* void */ }
            p->next = pr;
    
        } else {
            r->postponed = pr;
        }
    
        /* 子请求为内部请求 */
        sr->internal = 1;
    
        /* 继承父请求的部分状态 */
        sr->discard_body = r->discard_body;
        sr->expect_tested = 1;
        sr->main_filter_need_in_memory = r->main_filter_need_in_memory;
    
        sr->uri_changes = NGX_HTTP_MAX_URI_CHANGES + 1;
    
        tp = ngx_timeofday();
        sr->start_sec = tp->sec;
        sr->start_msec = tp->msec;
    
        /* 增加原始请求的引用计数 */
        r->main->count++;
    
        *psr = sr;/* 指向新创建的子请求 */
    
        /* 将该子请求挂载到原始请求的posted_requests链表队尾 */
        return ngx_http_post_request(sr, NULL);
    }
    /* 其中 ngx_http_post_request 定义在文件 src/http/ngx_http_request.c */
    ngx_int_t
    ngx_http_post_request(ngx_http_request_t *r, ngx_http_posted_request_t *pr)
    {
        ngx_http_posted_request_t  **p;
    
        if (pr == NULL) {
            pr = ngx_palloc(r->pool, sizeof(ngx_http_posted_request_t));
            if (pr == NULL) {
                return NGX_ERROR;
            }
        }
    
        pr->request = r;
        pr->next = NULL;
    
        for (p = &r->main->posted_requests; *p; p = &(*p)->next) { /* void */ }
    
        *p = pr;
    
        return NGX_OK;
    }
    
    

### 子请求结束时的回调函数

在子请求结束时（正常或异常结束）Nginx 会调用ngx_http_post_subrequest_pt 回调处理方法。下面是回调方法的定义：

    typedef struct {
        ngx_http_post_subrequest_pt       handler;
        void                             *data;
    } ngx_http_post_subrequest_t;
    
    typedef ngx_int_t (*ngx_http_post_subrequest_pt)(ngx_http_request_t *r,
        void *data, ngx_int_t rc);
    
    

在结构体 ngx_http_post_subrequest_t 中，生成该结构体的变量时，可把用户的任意数据赋给指针data ，ngx_http_post_subrequest_pt 回调方法的参数data 就是用户把数据赋给结构体 ngx_http_post_subrequest_t 中的成员指针data 所指的数据。ngx_http_post_subrequest_pt 回调方法中的参数rc 是子请求结束时的状态，它的取值由函数ngx_http_finalize_request 销毁请求时传递给参数rc。 函数ngx_http_finalize_request 的部分源码，具体可查阅文件：[src/http/ngx_http_request.c][8]

    void
    ngx_http_finalize_request(ngx_http_request_t *r, ngx_int_t rc) 
    {
      ...
        /* 如果当前请求是某个原始请求的一个子请求，检查它是否有回调handler处理函数，若存在则执行 */
        if (r != r->main && r->post_subrequest) {
            rc = r->post_subrequest->handler(r, r->post_subrequest->data, rc);
        }
    
      ...
        
        /* 若 r 是子请求 */
        if (r != r->main) {  
            /* 该子请求还有未处理完的数据或者子请求 */
            if (r->buffered || r->postponed) {
                /* 添加一个该子请求的写事件，并设置合适的write event hander，
                   以便下次写事件来的时候继续处理，这里实际上下次执行时会调用ngx_http_output_filter函数，
                   最终还是会进入ngx_http_postpone_filter进行处理 */
                if (ngx_http_set_write_handler(r) != NGX_OK) {
                    ngx_http_terminate_request(r, 0);
                }
    
                return;
            }
            ...
            pr = r->parent;
            
    
            /* 该子请求已经处理完毕，如果它拥有发送数据的权利，则将权利移交给父请求， */
            if (r == c->data) { 
    
                r->main->count--;
    
                if (!r->logged) {
    
                    clcf = ngx_http_get_module_loc_conf(r, ngx_http_core_module);
    
                    if (clcf->log_subrequest) {
                        ngx_http_log_request(r);
                    }
    
                    r->logged = 1;
    
                } else {
                    ngx_log_error(NGX_LOG_ALERT, c->log, 0,
                                  "subrequest: \"%V?%V\" logged again",
                                  &r->uri, &r->args);
                }
    
                r->done = 1;
                /* 如果该子请求不是提前完成，则从父请求的postponed链表中删除 */
                if (pr->postponed && pr->postponed->request == r) {
                    pr->postponed = pr->postponed->next;
                }
                /* 将发送权利移交给父请求，父请求下次执行的时候会发送它的postponed链表中可以
                 * 发送的数据节点，或者将发送权利移交给它的下一个子请求 */
                c->data = pr;   
    
            } else {
                /* 该子请求提前执行完成，而且它没有产生任何数据，则它下次再次获得
                 * 执行机会时，将会执行ngx_http_request_finalzier函数，它实际上是执行
                 * ngx_http_finalzie_request（r,0），不做具体操作，直到它发送数据时，
                 * ngx_http_finalzie_request函数会将它从父请求的postponed链表中删除
                 */
                r->write_event_handler = ngx_http_request_finalizer;
    
                if (r->waited) {
                    r->done = 1;
                }
            }
            /* 将父请求加入posted_request队尾，获得一次运行机会 */
            if (ngx_http_post_request(pr, NULL) != NGX_OK) {
                r->main->count++;
                ngx_http_terminate_request(r, 0);
                return;
            }
    
            return;
        }
        /* 这里是处理主请求结束的逻辑，如果主请求有未发送的数据或者未处理的子请求，
         * 则给主请求添加写事件，并设置合适的write event hander，
         * 以便下次写事件来的时候继续处理 */
        if (r->buffered || c->buffered || r->postponed || r->blocked) {
    
            if (ngx_http_set_write_handler(r) != NGX_OK) {
                ngx_http_terminate_request(r, 0);
            }
    
            return;
        }
    
     ...
    } 
    
    

### 父请求被激活后的回调方法

父请求被激活后的回调方法由指针 ngx_http_event_pt 实现。该方法负责把响应包发送给用户。如下所示：

    typedef void(*ngx_http_event_handler_pt)(ngx_http_request_t *r);
    
    struct ngx_http_request_s{
          ...
          ngx_http_event_handler_pt      write_event_handler;
          ...
    };
    
    

一个请求中，只能调用一次 subrequest，即不能一次创建多个子请求，但是可以在新创建的子请求中再创建新的子请求。

参考资料：

《深入理解Nginx 》

《[Emiller's Advanced Topics In Nginx Module Development][9]》

《[nginx subrequest的实现解析][10]》

《[ngx_http_request_t结构体][11]》

[0]: http://lxr.nginx.org/source/src/http/ngx_http_request.h
[1]: ./img/2016-09-01_57c7edd0953f8.jpg
[2]: http://lxr.nginx.org/source/src/http/ngx_http_upstream.h
[3]: https://github.com
[4]: http://lxr.nginx.org/source/src/http/ngx_http_core_module.h
[5]: http://www.evanmiller.org/nginx-modules-guide-advanced.html#subrequests
[6]: http://lxr.nginx.org/source/src/http/modules/ngx_http_addition_filter_module.c
[7]: http://lxr.nginx.org/source/src/http/ngx_http_core_module.c
[8]: http://lxr.nginx.org/source/src/http/ngx_http_request.c
[9]: http://www.evanmiller.org/nginx-modules-guide-advanced.html#subrequests-single
[10]: http://blog.csdn.net/fengmo_q/article/details/6685840
[11]: http://blog.csdn.net/xiajun07061225/article/details/9189505
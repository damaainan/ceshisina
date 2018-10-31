## 【Nginx源码研究】初探nginx HTTP处理流程

来源：[https://segmentfault.com/a/1190000016698217](https://segmentfault.com/a/1190000016698217)

运营研发团队  李乐
## 1.初始化服务器

server指令用于配置virtual server，我们通常会在一台机器配置多个virtual server，监听不同端口号，映射到不同文件目录；nginx解析用户配置，在所有端口创建socket并启动监听。

nginx解析配置文件是由各个模块分担处理的，每个模块注册并处理自己关心的配置，通过模块结构体ngx_module_t的字段ngx_command_t *commands实现；

例如ngx_http_module是一个核心模块，其commands字段定义如下：

```c
struct ngx_command_s {
    ngx_str_t             name;
    ngx_uint_t            type;
    char               *(*set)(ngx_conf_t *cf, ngx_command_t *cmd, void *conf);
};
 
static ngx_command_t  ngx_http_commands[] = {
 
    { ngx_string("http"),
      NGX_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
      ngx_http_block,
     },
};
```


* name指令名称，解析配置文件时按照名称能匹配查找；
* type指令类型，NGX_CONF_NOARGS该配置无参数，NGX_CONF_BLOCK该配置是一个配置块，NGX_MAIN_CONF表示配置可以出现在哪些位（NGX_MAIN_CONF、NGX_HTTP_SRV_CONF、NGX_HTTP_LOC_CONF）；
* set指令处理函数指针；


可以看到解析http指令的处理函数为ngx_http_block，实现如下：

```c
static char * ngx_http_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    //解析main配置
    //解析server配置
    //解析location配置
 
    //初始化HTTP处理流程所需的handler
 
    //初始化listening
    if (ngx_http_optimize_servers(cf, cmcf, cmcf->ports) != NGX_OK) {
        return NGX_CONF_ERROR;
    }
}
```

ngx_http_optimize_servers方法循环所有配置端口，创建ngx_listening_t对象，并将其添加到conf->cycle->listening（后续操作会遍历此数组，创建socket并监听）。方法主要操作如下图：

![][0]

注意到这里设置了ngx_listening_t的handler为ngx_http_init_connection，当接收到socket连接请求时，会调用此handler处理。

那么什么时候启动监听呢？全局搜索关键字cycle->listening可以找到。main方法会调用ngx_init_cycle，其完成了服务器初始化的大部分工作，其中就包括启动监听（ngx_open_listening_sockets）。

假设nginx使用epoll处理所有socket事件，什么时候将监听事件添加到epoll呢？全局搜索关键字cycle->listening可以找到。ngx_event_core_module模块是事件处理核心模块，初始化此模块时会执行ngx_event_process_init函数，其中将监听事件添加到epoll。

```c
static ngx_int_t ngx_event_process_init(ngx_cycle_t *cycle)
{
    ls = cycle->listening.elts;
    for (i = 0; i < cycle->listening.nelts; i++) {
        //设置读事件处理handler
        rev->handler = ngx_event_accept;
         
        ngx_add_event(rev, NGX_READ_EVENT, 0)；
    }
}
```

注意到接收到客户端socket连接请求事件的处理函数是ngx_event_accept。
## 2.HTTP请求解析
## 2.1 基础结构体

结构体ngx_connection_t存储socket连接相关信息；nginx预先创建若干个ngx_connection_t对象，存储在全局变量ngx_cycle->free_connections，称之为连接池；当新生成socket时，会尝试从连接池中获取空闲connection连接，如果获取失败，则会直接关闭此socket。

指令worker_connections用于配置连接池最大连接数目，配置在events指令块中，由ngx_event_core_module解析。

```c
vents {
   use epoll;
   worker_connections  60000;
}
```

当nginx作为HTTP服务器时，最大客户端数目maxClient=worker_processes worker_connections/2；当nginx作为反向代理服务器时，最大客户端数目maxClient=worker_processes worker_connections/4。其worker_processes为用户配置的worker进程数目。

结构体ngx_connection_t定义如下：

```c
struct ngx_connection_s {
    //空闲连接池中，data指向下一个连接，形成链表；取出来使用时，data指向请求结构体ngx_http_request_s
    void               *data;
    //读写事件结构体，两个关键字段：handler处理函数、timer定时器
    ngx_event_t        *read;
    ngx_event_t        *write;
 
    ngx_socket_t        fd;   //socket fd
 
    ngx_recv_pt         recv; //socket接收数据函数指针
    ngx_send_pt         send; //socket发送数据函数指针
 
    ngx_buf_t          *buffer; //输入缓冲区
 
    struct sockaddr    *sockaddr; //客户端地址
    socklen_t           socklen;
 
    ngx_listening_t    *listening; //监听的ngx_listening_t对象
 
    struct sockaddr    *local_sockaddr; //本地地址
    socklen_t           local_socklen;
 
    …………
}
```

结构体ngx_http_request_t存储整个HTTP请求处理流程所需的所有信息，字段非常多，这里只进行简要说明：

```c
struct ngx_http_request_s {
 
    ngx_connection_t                 *connection;
 
    //读写事件处理handler
    ngx_http_event_handler_pt         read_event_handler;
    ngx_http_event_handler_pt         write_event_handler;
 
    //请求头缓冲区
    ngx_buf_t                        *header_in;
 
    //解析后的请求头
    ngx_http_headers_in_t             headers_in;
     
    //请求体结构体
    ngx_http_request_body_t          *request_body;
 
    //请求行
    ngx_str_t                         request_line;
    //解析后请求行若干字段
    ngx_uint_t                        method;
    ngx_uint_t                        http_version;
    ngx_str_t                         uri;
    ngx_str_t                         args;
 
    …………
}
```

请求行与请求体解析相对比较简单，这里重点讲述请求头的解析，解析后的请求头信息都存储在ngx_http_headers_in_t结构体中。

ngx_http_request.c文件中定义了所有的HTTP头部，存储在ngx_http_headers_in数组，数组的每个元素是一个ngx_http_header_t结构体，主要包含三个字段，头部名称、头部解析后字段存储在ngx_http_headers_in_t的偏移量，解析头部的处理函数。

```c
ngx_http_header_t  ngx_http_headers_in[] = {
    { ngx_string("Host"), offsetof(ngx_http_headers_in_t, host),
                 ngx_http_process_host },
 
    { ngx_string("Connection"), offsetof(ngx_http_headers_in_t, connection),
                 ngx_http_process_connection },
    …………
}
 
typedef struct {
    ngx_str_t                         name;
    ngx_uint_t                        offset;
    ngx_http_header_handler_pt        handler;
} ngx_http_header_t;
```

解析请求头时，从ngx_http_headers_in数组中查找请求头ngx_http_header_t对象，调用处理函数handler，存储到r->headers_in对应字段。以解析Connection头部为例，ngx_http_process_connection实现如下：

```c
static ngx_int_t ngx_http_process_connection(ngx_http_request_t *r, ngx_table_elt_t *h, ngx_uint_t offset)
{
    if (ngx_strcasestrn(h->value.data, "close", 5 - 1)) {
        r->headers_in.connection_type = NGX_HTTP_CONNECTION_CLOSE;
 
    } else if (ngx_strcasestrn(h->value.data, "keep-alive", 10 - 1)) {
        r->headers_in.connection_type = NGX_HTTP_CONNECTION_KEEP_ALIVE;
    }
 
    return NGX_OK;
}
```

输入参数offset在此处并没有什么作用。注意到第二个输入参数ngx_table_elt_t，存储了当前请求头的键值对信息：

```c
typedef struct {
    ngx_uint_t        hash;  //请求头key的hash值
    ngx_str_t         key;
    ngx_str_t         value;
    u_char           *lowcase_key;  //请求头key转为小写字符串（可以看到HTTP请求头解析时key不区分大小写）
} ngx_table_elt_t;
```

再思考一个问题，从ngx_http_headers_in数组中查找请求头对应ngx_http_header_t对象时，需要遍历，每个元素都需要进行字符串比较，效率低下。因此nginx将ngx_http_headers_in数组转换为哈希表，哈希表的键即为请求头的key，方法ngx_http_init_headers_in_hash实现了数组到哈希表的转换，转换后的哈希表存储在cmcf->headers_in_hash字段。

![][1]
## 2.2 解析HTTP请求

第1节提到，在创建socket启动监听时，会添加可读事件到epoll，事件处理函数为ngx_event_accept，用于接收socket连接，分配connection连接，并调用ngx_listening_t对象的处理函数（ngx_http_init_connection）。

```c
void ngx_event_accept(ngx_event_t *ev)
{
    s = accept4(lc->fd, (struct sockaddr *) sa, &socklen, SOCK_NONBLOCK);
 
    //客户端socket连接成功时，都需要分配connection连接，如果分配失败则会直接关闭此socket。
    //而每个worker进程连接池的最大连接数目是固定的，当不存在空闲连接时，此worker进程accept的所有socket都会被拒绝；
    //多个worker进程通过竞争执行epoll_wait；而当ngx_accept_disabled大于0时，会直接放弃此次竞争，同时ngx_accept_disabled减1。
    //以此实现，当worker进程的空闲连接过少时，减少其竞争epoll_wait次数
    ngx_accept_disabled = ngx_cycle->connection_n / 8 - ngx_cycle->free_connection_n;
 
    c = ngx_get_connection(s, ev->log);
 
    ls->handler(c);
}
```

socket连接成功后，nginx会等待客户端发送HTTP请求，默认会有60秒的超时时间，即60秒内没有接收到客户端请求时，断开此连接，打印错误日志。函数ngx_http_init_connection用于设置读事件处理函数，以及超时定时器。

```c
void ngx_http_init_connection(ngx_connection_t *c)
{
    c->read = ngx_http_wait_request_handler;
    c->write->handler = ngx_http_empty_handler;
 
    ngx_add_timer(rev, c->listening->post_accept_timeout);
}
```

全局搜索post_accept_timeout字段，可以查找到设置此超时时间的配置指令，client_header_timeout，其可以在http、server指令块中配置。

函数ngx_http_wait_request_handler为解析HTTP请求的入口函数，实现如下：

```c
static void ngx_http_wait_request_handler(ngx_event_t *rev)
{
    //读事件已经超时
    if (rev->timedout) {
        ngx_log_error(NGX_LOG_INFO, c->log, NGX_ETIMEDOUT, "client timed out");
        ngx_http_close_connection(c);
        return;
    }
 
    size = cscf->client_header_buffer_size;   //client_header_buffer_size指令用于配置接收请求头缓冲区大小
    b = c->buffer;
 
    n = c->recv(c, b->last, size);
 
    //创建请求对象ngx_http_request_t，HTTP请求整个处理过程都有用；
    c->data = ngx_http_create_request(c);
 
    rev->handler = ngx_http_process_request_line; //设置读事件处理函数（此次请求行可能没有读取完）
    ngx_http_process_request_line(rev);
}
```

函数ngx_http_create_request创建并初始化ngx_http_request_t对象，注意这赋值语句r->header_in =c->buffer。

解析请求行与请求头的代码较为繁琐，终点在于读取socket数据，解析字符串，这里不做详述。HTTP请求解析过程主要函数调用如下图所示：

![][2]

注意，解析完成请求行与请求头，nginx就开始处理HTTP请求，并没有等到解析完请求体再处理。处理请求入口为ngx_http_process_request。
## 3.处理HTTP请求
## 3.1 HTTP请求处理的11个阶段

nginx将HTTP请求处理流程分为11个阶段，绝大多数HTTP模块都会将自己的handler添加到某个阶段（将handler添加到全局唯一的数组phases中），注意其中有4个阶段不能添加自定义handler，nginx处理HTTP请求时会挨个调用每个阶段的handler；

```c
typedef enum {
    NGX_HTTP_POST_READ_PHASE = 0, //第一个阶段，目前只有realip模块会注册handler，但是该模块默认不会运行（nginx作为代理服务器时有用，后端以此获取客户端原始ip）
  
    NGX_HTTP_SERVER_REWRITE_PHASE,  //server块中配置了rewrite指令，重写url
  
    NGX_HTTP_FIND_CONFIG_PHASE,   //查找匹配的location配置；不能自定义handler；
    NGX_HTTP_REWRITE_PHASE,       //location块中配置了rewrite指令，重写url
    NGX_HTTP_POST_REWRITE_PHASE,  //检查是否发生了url重写，如果有，重新回到FIND_CONFIG阶段；不能自定义handler；
  
    NGX_HTTP_PREACCESS_PHASE,     //访问控制，比如限流模块会注册handler到此阶段
  
    NGX_HTTP_ACCESS_PHASE,        //访问权限控制，比如基于ip黑白名单的权限控制，基于用户名密码的权限控制等
    NGX_HTTP_POST_ACCESS_PHASE,   //根据访问权限控制阶段做相应处理；不能自定义handler；
  
    NGX_HTTP_TRY_FILES_PHASE,     //只有配置了try_files指令，才会有此阶段；不能自定义handler；
    NGX_HTTP_CONTENT_PHASE,       //内容产生阶段，返回响应给客户端
  
    NGX_HTTP_LOG_PHASE            //日志记录
} ngx_http_phases;
```

nginx使用结构体ngx_module_s表示一个模块，其中字段ctx，是一个指向模块上下文结构体的指针（上下文结构体的字段都是一些函数指针）；nginx的HTTP模块上下文结构体大多都有字段postconfiguration，负责注册本模块的handler到某个处理阶段。11个阶段在解析完成http配置块指令后初始化。

```c
static char * ngx_http_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    //解析http配置块
 
    //初始化11个阶段的phases数组，注意多个模块可能注册到同一个阶段，因此phases是一个二维数组
    if (ngx_http_init_phases(cf, cmcf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }
 
    //遍历索引HTTP模块，注册handler
    for (m = 0; ngx_modules[m]; m++) {
        if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
            continue;
        }
 
        module = ngx_modules[m]->ctx;
 
        if (module->postconfiguration) {
            if (module->postconfiguration(cf) != NGX_OK) {
                return NGX_CONF_ERROR;
            }
        }
    }
 
    //将二维数组转换为一维数组，从而遍历执行数组所有handler
    if (ngx_http_init_phase_handlers(cf, cmcf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }
}
```

以限流模块ngx_http_limit_req_module模块为例，postconfiguration方法简单实现如下：

```c
static ngx_int_t ngx_http_limit_req_init(ngx_conf_t *cf)
{
    h = ngx_array_push(&cmcf->phases[NGX_HTTP_PREACCESS_PHASE].handlers);
     
    *h = ngx_http_limit_req_handler;  //ngx_http_limit_req_module模块的限流方法；nginx处理HTTP请求时，都会调用此方法判断应该继续执行还是拒绝请求
  
    return NGX_OK;
}
```

GDB调试，断点到ngx_http_block方法执行所有HTTP模块注册handler之后，打印phases数组

```c
p cmcf->phases[*].handlers
p *(ngx_http_handler_pt*)cmcf->phases[*].handlers.elts
```

11个阶段注册的handler如下图所示：

![][3]
## 3.2 11个阶段初始化

上面提到HTTP的11个处理阶段handler存储在phases数组，但由于多个模块可能注册handler到同一个阶段，使得phases是一个二维数组，因此需要转换为一维数组，转换后存储在cmcf->phase_engine字段，phase_engine的类型为ngx_http_phase_engine_t，定义如下：

```c
typedef struct {
    ngx_http_phase_handler_t  *handlers;   //一维数组，存储所有handler
    ngx_uint_t                 server_rewrite_index;  //记录NGX_HTTP_SERVER_REWRITE_PHASE阶段handler的索引值
    ngx_uint_t                 location_rewrite_index; //记录NGX_HTTP_REWRITE_PHASE阶段handler的索引值
} ngx_http_phase_engine_t;
 
struct ngx_http_phase_handler_t {
    ngx_http_phase_handler_pt  checker;  //执行handler之前的校验函数
    ngx_http_handler_pt        handler;
    ngx_uint_t                 next;   //下一个待执行handler的索引（通过next实现handler跳转执行）
};
 
//cheker函数指针类型定义
typedef ngx_int_t (*ngx_http_phase_handler_pt)(ngx_http_request_t *r, ngx_http_phase_handler_t *ph);
//handler函数指针类型定义
typedef ngx_int_t (*ngx_http_handler_pt)(ngx_http_request_t *r);
```

数组转换函数ngx_http_init_phase_handlers实现如下：

```c
static ngx_int_t ngx_http_init_phase_handlers(ngx_conf_t *cf, ngx_http_core_main_conf_t *cmcf)
{
    use_rewrite = cmcf->phases[NGX_HTTP_REWRITE_PHASE].handlers.nelts ? 1 : 0;
    use_access = cmcf->phases[NGX_HTTP_ACCESS_PHASE].handlers.nelts ? 1 : 0;
     
    n = use_rewrite + use_access + cmcf->try_files + 1 /* find config phase */; //至少有4个阶段，这4个阶段是上面说的不能注册handler的4个阶段
     
    //计算handler数目，分配空间
    for (i = 0; i < NGX_HTTP_LOG_PHASE; i++) {
        n += cmcf->phases[i].handlers.nelts;
    }
    ph = ngx_pcalloc(cf->pool, n * sizeof(ngx_http_phase_handler_t) + sizeof(void *));
 
    //遍历二维数组
    for (i = 0; i < NGX_HTTP_LOG_PHASE; i++) {
        h = cmcf->phases[i].handlers.elts;
 
        switch (i) {
 
        case NGX_HTTP_SERVER_REWRITE_PHASE:
            if (cmcf->phase_engine.server_rewrite_index == (ngx_uint_t) -1) {
                cmcf->phase_engine.server_rewrite_index = n;   //记录NGX_HTTP_SERVER_REWRITE_PHASE阶段handler的索引值
            }
            checker = ngx_http_core_rewrite_phase;
            break;
 
        case NGX_HTTP_FIND_CONFIG_PHASE:
            find_config_index = n;   //记录NGX_HTTP_FIND_CONFIG_PHASE阶段的索引，NGX_HTTP_POST_REWRITE_PHASE阶段可能会跳转回此阶段
            ph->checker = ngx_http_core_find_config_phase;
            n++;
            ph++;
            continue;   //进入下一个阶段NGX_HTTP_REWRITE_PHASE
  
        case NGX_HTTP_REWRITE_PHASE:
            if (cmcf->phase_engine.location_rewrite_index == (ngx_uint_t) -1) {
                cmcf->phase_engine.location_rewrite_index = n;   //记录NGX_HTTP_REWRITE_PHASE阶段handler的索引值
            }
            checker = ngx_http_core_rewrite_phase; 
            break;
 
        case NGX_HTTP_POST_REWRITE_PHASE:
            if (use_rewrite) {
                ph->checker = ngx_http_core_post_rewrite_phase;
                ph->next = find_config_index;
                n++;
                ph++;
            }
            continue;  //进入下一个阶段NGX_HTTP_ACCESS_PHASE
 
        case NGX_HTTP_ACCESS_PHASE:
            checker = ngx_http_core_access_phase;
            n++;
            break;
 
        case NGX_HTTP_POST_ACCESS_PHASE:
            if (use_access) {
                ph->checker = ngx_http_core_post_access_phase;
                ph->next = n;
                ph++;
            }
            continue;  //进入下一个阶段
 
        case NGX_HTTP_TRY_FILES_PHASE:
            if (cmcf->try_files) {
                ph->checker = ngx_http_core_try_files_phase;
                n++;
                ph++;
            }
            continue;
 
        case NGX_HTTP_CONTENT_PHASE:
            checker = ngx_http_core_content_phase;
            break;
 
        default:
            checker = ngx_http_core_generic_phase;
        }
 
        //n为下一个阶段第一个handler的索引
        n += cmcf->phases[i].handlers.nelts;
 
        //遍历当前阶段的所有handler
        for (j = cmcf->phases[i].handlers.nelts - 1; j >=0; j--) {
            ph->checker = checker;
            ph->handler = h[j];
            ph->next = n;
            ph++;
        }
    }
}
```

GDB打印出转换后的数组如下图所示，第一列是cheker字段，第二列是handler字段，箭头表示next跳转；图中有个返回的箭头，即NGX_HTTP_POST_REWRITE_PHASE阶段可能返回到NGX_HTTP_FIND_CONFIG_PHASE；原因在于只要NGX_HTTP_REWRITE_PHASE阶段产生了url重写，就需要重新查找匹配location。

![][4]
## 3.3 处理HTTP请求

2.2节提到HTTP请求的处理入口函数是ngx_http_process_request，其主要调用ngx_http_core_run_phases实现11个阶段的执行流程；

ngx_http_core_run_phases遍历预先设置好的cmcf->phase_engine.handlers数组，调用其checker函数，逻辑如下：

```c
void ngx_http_core_run_phases(ngx_http_request_t *r)
{
    ph = cmcf->phase_engine.handlers;
 
    //phase_handler初始为0，表示待处理handler的索引；cheker内部会根据ph->next字段修改phase_handler
    while (ph[r->phase_handler].checker) {
 
        rc = ph[r->phase_handler].checker(r, &ph[r->phase_handler]);
 
        if (rc == NGX_OK) {
            return;
        }
    }
}
```

checker内部就是调用handler，并设置下一步要执行handler的索引；比如说ngx_http_core_generic_phase实现如下：

```c
ngx_int_t ngx_http_core_generic_phase(ngx_http_request_t *r, ngx_http_phase_handler_t *ph)
{
    ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0, "rewrite phase: %ui", r->phase_handler);
    rc = ph->handler(r);
    if (rc == NGX_OK) {
        r->phase_handler = ph->next;
        return NGX_AGAIN;
    }
}
```
## 3.4 内容产生阶段

内容产生阶段NGX_HTTP_CONTENT_PHASE是HTTP请求处理的第10个阶段，一般情况有3个模块注册handler到此阶段：ngx_http_static_module、ngx_http_autoindex_module和ngx_http_index_module。

但是当我们配置了proxy_pass和fastcgi_pass时，情况会有所不同；

使用proxy_pass配置上游时，ngx_http_proxy_module模块会设置其处理函数到配置类conf；使用fastcgi_pass配置时，ngx_http_fastcgi_module会设置其处理函数到配置类conf。例如：

```c
static char * ngx_http_fastcgi_pass(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    ngx_http_core_loc_conf_t   *clcf;
    clcf = ngx_http_conf_get_module_loc_conf(cf, ngx_http_core_module);
 
    clcf->handler = ngx_http_fastcgi_handler;
}
```

阶段NGX_HTTP_FIND_CONFIG_PHASE查找匹配的location，并获取此ngx_http_core_loc_conf_t对象，将其handler赋值给ngx_http_request_t对象的content_handler字段（内容产生处理函数）。

而在执行内容产生阶段的checker函数时，会执行content_handler指向的函数；查看ngx_http_core_content_phase函数实现（内容产生阶段的checker函数）：

```c
ngx_int_t ngx_http_core_content_phase(ngx_http_request_t *r,
    ngx_http_phase_handler_t *ph)
{
    if (r->content_handler) {  //如果请求对象的content_handler字段不为空，则调用
        r->write_event_handler = ngx_http_request_empty_handler;
        ngx_http_finalize_request(r, r->content_handler(r));
        return NGX_OK;
    }
 
    ngx_log_debug1(NGX_LOG_DEBUG_HTTP, r->connection->log, 0, "content phase: %ui", r->phase_handler);
 
    rc = ph->handler(r);  //否则执行内容产生阶段handler
}
```
## 总结

nginx处理HTTP请求的流程较为复杂，因此本文只是简单提供了一条线索：分析了nginx服务器启动监听的过程，HTTP请求的解析过程，11个阶段的初始化与调用过程。至于HTTP解析处理的详细流程，还需要读者去探索。

[0]: ./img/bVbid5n.png
[1]: ./img/bVbid5Y.png
[2]: ./img/bVbid7b.png
[3]: ./img/bVbid7u.png
[4]: ./img/bVbid7X.png
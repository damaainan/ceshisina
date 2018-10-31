## 【Nginx源码研究】nginx限流模块详解

来源：[https://segmentfault.com/a/1190000016509710](https://segmentfault.com/a/1190000016509710)

运营研发团队  李乐
## 高并发系统有三把利器：缓存、降级和限流；

限流的目的是通过对并发访问/请求进行限速来保护系统，一旦达到限制速率则可以拒绝服务（定向到错误页）、排队等待（秒杀）、降级（返回兜底数据或默认数据）；

高并发系统常见的限流有：限制总并发数（数据库连接池）、限制瞬时并发数（如nginx的limit_conn模块，用来限制瞬时并发连接数）、限制时间窗口内的平均速率（nginx的limit_req模块，用来限制每秒的平均速率）；

另外还可以根据网络连接数、网络流量、CPU或内存负载等来限流。
## 1.限流算法

最简单粗暴的限流算法就是计数器法了，而比较常用的有漏桶算法和令牌桶算法；
## 1.1计数器

计数器法是限流算法里最简单也是最容易实现的一种算法。比如我们规定，对于A接口来说，我们1分钟的访问次数不能超过100个。

那么我们我们可以设置一个计数器counter，其有效时间为1分钟（即每分钟计数器会被重置为0），每当一个请求过来的时候，counter就加1，如果counter的值大于100，就说明请求数过多；

这个算法虽然简单，但是有一个十分致命的问题，那就是临界问题。

如下图所示，在1:00前一刻到达100个请求，1:00计数器被重置，1:00后一刻又到达100个请求，显然计数器不会超过100，所有请求都不会被拦截；

然而这一时间段内请求数已经达到200，远超100。

![][0]
## 1.2 漏桶算法

如下图所示，有一个固定容量的漏桶，按照常量固定速率流出水滴；如果桶是空的，则不会流出水滴；流入到漏桶的水流速度是随意的；如果流入的水超出了桶的容量，则流入的水会溢出（被丢弃）；

可以看到漏桶算法天生就限制了请求的速度，可以用于流量整形和限流控制；

![][1]
## 1.3 令牌桶算法

令牌桶是一个存放固定容量令牌的桶，按照固定速率r往桶里添加令牌；桶中最多存放b个令牌，当桶满时，新添加的令牌被丢弃；

当一个请求达到时，会尝试从桶中获取令牌；如果有，则继续处理请求；如果没有则排队等待或者直接丢弃；

可以发现，漏桶算法的流出速率恒定或者为0，而令牌桶算法的流出速率却有可能大于r；

![][2]
## 2.nginx基础知识

Nginx主要有两种限流方式：按连接数限流(ngx_http_limit_conn_module)、按请求速率限流(ngx_http_limit_req_module)；

学习限流模块之前还需要了解nginx对HTTP请求的处理过程，nginx事件处理流程等；
## 2.1HTTP请求处理过程

nginx将HTTP请求处理流程分为11个阶段，绝大多数HTTP模块都会将自己的handler添加到某个阶段（其中有4个阶段不能添加自定义handler），nginx处理HTTP请求时会挨个调用所有的handler；

```c
typedef enum {
    NGX_HTTP_POST_READ_PHASE = 0, //目前只有realip模块会注册handler（nginx作为代理服务器时有用，后端以此获取客户端原始ip）
 
    NGX_HTTP_SERVER_REWRITE_PHASE,  //server块中配置了rewrite指令，重写url
 
    NGX_HTTP_FIND_CONFIG_PHASE,   //查找匹配location；不能自定义handler；
    NGX_HTTP_REWRITE_PHASE,       //location块中配置了rewrite指令，重写url
    NGX_HTTP_POST_REWRITE_PHASE,  //检查是否发生了url重写，如果有，重新回到FIND_CONFIG阶段；不能自定义handler；
 
    NGX_HTTP_PREACCESS_PHASE,     //访问控制，限流模块会注册handler到此阶段
 
    NGX_HTTP_ACCESS_PHASE,        //访问权限控制
    NGX_HTTP_POST_ACCESS_PHASE,   //根据访问权限控制阶段做相应处理；不能自定义handler；
 
    NGX_HTTP_TRY_FILES_PHASE,     //只有配置了try_files指令，才会有此阶段；不能自定义handler；
    NGX_HTTP_CONTENT_PHASE,       //内容产生阶段，返回响应给客户端
 
    NGX_HTTP_LOG_PHASE            //日志记录
} ngx_http_phases;
```

nginx使用结构体ngx_module_s表示一个模块，其中字段ctx，是一个指向模块上下文结构体的指针；nginx的HTTP模块上下文结构体如下所示（上下文结构体的字段都是一些函数指针）：

```c
typedef struct {
    ngx_int_t   (*preconfiguration)(ngx_conf_t *cf);
    ngx_int_t   (*postconfiguration)(ngx_conf_t *cf);  //此方法注册handler到相应阶段
 
    void       *(*create_main_conf)(ngx_conf_t *cf);   //http块中的主配置
    char       *(*init_main_conf)(ngx_conf_t *cf, void *conf);
 
    void       *(*create_srv_conf)(ngx_conf_t *cf);    //server配置
    char       *(*merge_srv_conf)(ngx_conf_t *cf, void *prev, void *conf);
 
    void       *(*create_loc_conf)(ngx_conf_t *cf);    //location配置
    char       *(*merge_loc_conf)(ngx_conf_t *cf, void *prev, void *conf);
} ngx_http_module_t;
```

以ngx_http_limit_req_module模块为例，postconfiguration方法简单实现如下：

```c
static ngx_int_t ngx_http_limit_req_init(ngx_conf_t *cf)
{
    h = ngx_array_push(&cmcf->phases[NGX_HTTP_PREACCESS_PHASE].handlers);
    
    *h = ngx_http_limit_req_handler;  //ngx_http_limit_req_module模块的限流方法；nginx处理HTTP请求时，都会调用此方法判断应该继续执行还是拒绝请求
 
    return NGX_OK;
}
```
## 2.2 nginx事件处理简单介绍

假设nginx使用的是epoll。

nginx需要将所有关心的fd注册到epoll，添加方法生命如下：

```c
static ngx_int_t ngx_epoll_add_event(ngx_event_t *ev, ngx_int_t event, ngx_uint_t flags);
```

方法第一个参数是ngx_event_t结构体指针，代表关心的一个读或者写事件；nginx为事件可能会设置一个超时定时器，从而能够处理事件超时情况；定义如下：

```c
struct ngx_event_s {
    
    ngx_event_handler_pt  handler; //函数指针：事件的处理函数
 
    ngx_rbtree_node_t   timer;     //超时定时器，存储在红黑树中（节点的key即为事件的超时时间）
 
    unsigned         timedout:1;   //记录事件是否超时
 
};
```

一般都会循环调用epoll_wait监听所有fd，处理发生的读写事件；epoll_wait是阻塞调用，最后一个参数timeout是超时时间，即最多阻塞timeout时间如果还是没有事件发生，方法会返回；

nginx在设置超时时间timeout时，会从上面说的记录超时定时器的红黑树中查找最近要到时的节点，以此作为epoll_wait的超时时间，如下面代码所示；

```c
ngx_msec_t ngx_event_find_timer(void)
{
    node = ngx_rbtree_min(root, sentinel);
    timer = (ngx_msec_int_t) (node->key - ngx_current_msec);
 
    return (ngx_msec_t) (timer > 0 ? timer : 0);
}
```

同时nginx在每次循环的最后，会从红黑树中查看是否有事件已经过期，如果过期，标记timeout=1，并调用事件的handler；

```c
void ngx_event_expire_timers(void)
{
    for ( ;; ) {
        node = ngx_rbtree_min(root, sentinel);
 
        if ((ngx_msec_int_t) (node->key - ngx_current_msec) <= 0) {  //当前事件已经超时
            ev = (ngx_event_t *) ((char *) node - offsetof(ngx_event_t, timer));
 
            ev->timedout = 1;
 
            ev->handler(ev);
 
            continue;
        }
 
        break;
    }
}
```

nginx就是通过上面的方法实现了socket事件的处理，定时事件的处理；

* ngx_http_limit_req_module模块解析

=====

ngx_http_limit_req_module模块是对请求进行限流，即限制某一时间段内用户的请求速率；且使用的是令牌桶算法；
## 3.1配置指令

ngx_http_limit_req_module模块提供一下配置指令，供用户配置限流策略

```c
//每个配置指令主要包含两个字段：名称，解析配置的处理方法
static ngx_command_t  ngx_http_limit_req_commands[] = {
 
    //一般用法：limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;
    //$binary_remote_addr表示远程客户端IP；
    //zone配置一个存储空间（需要分配空间记录每个客户端的访问速率，超时空间限制使用lru算法淘汰；注意此空间是在共享内存分配的，所有worker进程都能访问）
    //rate表示限制速率，此例为1qps
    { ngx_string("limit_req_zone"),
      ngx_http_limit_req_zone,
     },
 
    //用法：limit_req zone=one burst=5 nodelay;
    //zone指定使用哪一个共享空间
    //超出此速率的请求是直接丢弃吗？burst配置用于处理突发流量，表示最大排队请求数目，当客户端请求速率超过限流速率时，请求会排队等待；而超出burst的才会被直接拒绝；
    //nodelay必须与burst一起使用；此时排队等待的请求会被优先处理；否则假如这些请求依然按照限流速度处理，可能等到服务器处理完成后，客户端早已超时
    { ngx_string("limit_req"),
      ngx_http_limit_req,
     },
 
    //当请求被限流时，日志记录级别；用法：limit_req_log_level info | notice | warn | error;
    { ngx_string("limit_req_log_level"),
      ngx_conf_set_enum_slot,
     },
 
    //当请求被限流时，给客户端返回的状态码；用法：limit_req_status 503
    { ngx_string("limit_req_status"),
      ngx_conf_set_num_slot,
    },
};
```

注意：$binary_remote_addr是nginx提供的变量，用户在配置文件中可以直接使用；nginx还提供了许多变量，在ngx_http_variable.c文件中查找ngx_http_core_variables数组即可：

```c
static ngx_http_variable_t  ngx_http_core_variables[] = {
 
    { ngx_string("http_host"), NULL, ngx_http_variable_header,
      offsetof(ngx_http_request_t, headers_in.host), 0, 0 },
 
    { ngx_string("http_user_agent"), NULL, ngx_http_variable_header,
      offsetof(ngx_http_request_t, headers_in.user_agent), 0, 0 },
    …………
}
```
## 3.2源码解析

ngx_http_limit_req_module在postconfiguration过程会注册ngx_http_limit_req_handler方法到HTTP处理的NGX_HTTP_PREACCESS_PHASE阶段；

ngx_http_limit_req_handler会执行漏桶算法，判断是否超出配置的限流速率，从而进行丢弃或者排队或者通过；

当用户第一次请求时，会新增一条记录（主要记录访问计数、访问时间），以客户端IP地址（配置$binary_remote_addr）的hash值作为key存储在红黑树中（快速查找），同时存储在LRU队列中（存储空间不够时，淘汰记录，每次都是从尾部删除）；当用户再次请求时，会从红黑树中查找这条记录并更新，同时移动记录到LRU队列首部；
### 3.2.1数据结构

-----

limit_req_zone配置限流算法所需的存储空间（名称及大小），限流速度，限流变量（客户端IP等），结构如下：

```c
typedef struct {
    ngx_http_limit_req_shctx_t  *sh;
    ngx_slab_pool_t             *shpool;//内存池
    ngx_uint_t                   rate; //限流速度（qps乘以1000存储）
    ngx_int_t                    index; //变量索引（nginx提供了一系列变量，用户配置的限流变量索引）
    ngx_str_t                    var;   //限流变量名称
    ngx_http_limit_req_node_t   *node;
} ngx_http_limit_req_ctx_t;
 
//同时会初始化共享存储空间
struct ngx_shm_zone_s {
    void                     *data;  //data指向ngx_http_limit_req_ctx_t结构
    ngx_shm_t                 shm;   //共享空间
    ngx_shm_zone_init_pt      init;  //初始化方法函数指针
    void                     *tag;   //指向ngx_http_limit_req_module结构体
};
```

limit_req配置限流使用的存储空间，排队队列大小，是否紧急处理，结构如下：

```c
typedef struct {
    ngx_shm_zone_t              *shm_zone;  //共享存储空间
     
    ngx_uint_t                   burst;     //队列大小
    ngx_uint_t                   nodelay;   //有请求排队时是否紧急处理，与burst配合使用（如果配置，则会紧急处理排队请求，否则依然按照限流速度处理）
} ngx_http_limit_req_limit_t;
```

![][3]

前面说过用户访问记录会同时存储在红黑树与LRU队列中，结构如下：

```c
//记录结构体
typedef struct {
    u_char                       color;
    u_char                       dummy;
    u_short                      len;    //数据长度
    ngx_queue_t                  queue; 
    ngx_msec_t                   last;   //上次访问时间
     
    ngx_uint_t                   excess; //当前剩余待处理的请求数（nginx用此实现令牌桶限流算法）
    ngx_uint_t                   count;  //此类记录请求的总数
    u_char                       data[1];//数据内容（先按照key（hash值）查找，再比较数据内容是否相等）
} ngx_http_limit_req_node_t;
 
//红黑树节点，key为用户配置限流变量的hash值；
struct ngx_rbtree_node_s {
    ngx_rbtree_key_t       key;
    ngx_rbtree_node_t     *left;
    ngx_rbtree_node_t     *right;
    ngx_rbtree_node_t     *parent;
    u_char                 color;
    u_char                 data;
};
 
 
typedef struct {
    ngx_rbtree_t                  rbtree; //红黑树
    ngx_rbtree_node_t             sentinel; //NIL节点
    ngx_queue_t                   queue; //LRU队列
} ngx_http_limit_req_shctx_t;
 
//队列只有prev和next指针
struct ngx_queue_s {
    ngx_queue_t  *prev;
    ngx_queue_t  *next;
};
```

思考1：ngx_http_limit_req_node_t记录通过prev和next指针形成双向链表，实现LRU队列；最新访问的节点总会被插入链表头部，淘汰时从尾部删除节点；

![][4]

```c
ngx_http_limit_req_ctx_t *ctx;
ngx_queue_t                *q;
 
q = ngx_queue_last(&ctx->sh->queue);
 
lr = ngx_queue_data(q, ngx_http_limit_req_node_t, queue);//此方法由ngx_queue_t获取ngx_http_limit_req_node_t结构首地址，实现如下：
 
#define ngx_queue_data(q, type, link)    (type *) ((u_char *) q - offsetof(type, link)) //queue字段地址减去其在结构体中偏移，为结构体首地址
```

思考2：限流算法首先使用key查找红黑树节点，从而找到对应的记录，红黑树节点如何与记录ngx_http_limit_req_node_t结构关联起来呢？在ngx_http_limit_req_module模块可以找到以代码：

```c
size = offsetof(ngx_rbtree_node_t, color)    //新建记录分配内存，计算所需空间大小
       + offsetof(ngx_http_limit_req_node_t, data)
       + len;
 
node = ngx_slab_alloc_locked(ctx->shpool, size);
 
node->key = hash;
 
lr = (ngx_http_limit_req_node_t *) &node->color; //color为u_char类型，为什么能强制转换为ngx_http_limit_req_node_t指针类型呢？
 
lr->len = (u_char) len;
lr->excess = 0;
 
ngx_memcpy(lr->data, data, len);
 
ngx_rbtree_insert(&ctx->sh->rbtree, node);
 
ngx_queue_insert_head(&ctx->sh->queue, &lr->queue);
```

通过分析上面代码，ngx_rbtree_node_s结构体的color与data字段其实是无意义的，结构体的生命形式与最终存储形式是不同的，nginx最终使用以下存储形式存储每条记录；

![][5]
### 3.2.2限流算法

-----

上面提到在postconfiguration过程会注册ngx_http_limit_req_handler方法到HTTP处理的NGX_HTTP_PREACCESS_PHASE阶段；

因此在处理HTTP请求时，会执行ngx_http_limit_req_handler方法判断是否需要限流；
#### 3.2.2.1漏桶算法实现

-----

用户可能同时配置若干限流，因此对于HTTP请求，nginx需要遍历所有限流策略，判断是否需要限流；

ngx_http_limit_req_lookup方法实现了漏桶算法，方法返回3种结果：


* NGX_BUSY：请求速率超出限流配置，拒绝请求；
* NGX_AGAIN：请求通过了当前限流策略校验，继续校验下一个限流策略；
* NGX_OK：请求已经通过了所有限流策略的校验，可以执行下一阶段；
* NGX_ERROR：出错


```c
//limit，限流策略；hash，记录key的hash值；data，记录key的数据内容；len，记录key的数据长度；ep，待处理请求数目；account，是否是最后一条限流策略
static ngx_int_t ngx_http_limit_req_lookup(ngx_http_limit_req_limit_t *limit, ngx_uint_t hash, u_char *data, size_t len, ngx_uint_t *ep, ngx_uint_t account)
{
    //红黑树查找指定界定
    while (node != sentinel) {
 
        if (hash < node->key) {
            node = node->left;
            continue;
        }
 
        if (hash > node->key) {
            node = node->right;
            continue;
        }
 
        //hash值相等，比较数据是否相等
        lr = (ngx_http_limit_req_node_t *) &node->color;
 
        rc = ngx_memn2cmp(data, lr->data, len, (size_t) lr->len);
        //查找到
        if (rc == 0) {
            ngx_queue_remove(&lr->queue);
            ngx_queue_insert_head(&ctx->sh->queue, &lr->queue); //将记录移动到LRU队列头部
     
            ms = (ngx_msec_int_t) (now - lr->last); //当前时间减去上次访问时间
 
            excess = lr->excess - ctx->rate * ngx_abs(ms) / 1000 + 1000; //待处理请求书-限流速率*时间段+1个请求（速率，请求数等都乘以1000了）
 
            if (excess < 0) {
                excess = 0;
            }
 
            *ep = excess;
 
            //待处理数目超过burst（等待队列大小），返回NGX_BUSY拒绝请求（没有配置burst时，值为0）
            if ((ngx_uint_t) excess > limit->burst) {
                return NGX_BUSY;
            }
 
            if (account) {  //如果是最后一条限流策略，则更新上次访问时间，待处理请求数目，返回NGX_OK
                lr->excess = excess;
                lr->last = now;
                return NGX_OK;
            }
            //访问次数递增
            lr->count++;
 
            ctx->node = lr;
 
            return NGX_AGAIN; //非最后一条限流策略，返回NGX_AGAIN，继续校验下一条限流策略
        }
 
        node = (rc < 0) ? node->left : node->right;
    }
 
    //假如没有查找到节点，需要新建一条记录
    *ep = 0;
    //存储空间大小计算方法参照3.2.1节数据结构
    size = offsetof(ngx_rbtree_node_t, color)
            + offsetof(ngx_http_limit_req_node_t, data)
            + len;
    //尝试淘汰记录（LRU）
    ngx_http_limit_req_expire(ctx, 1);
 
     
    node = ngx_slab_alloc_locked(ctx->shpool, size);//分配空间
    if (node == NULL) {  //空间不足，分配失败
        ngx_http_limit_req_expire(ctx, 0); //强制淘汰记录
 
        node = ngx_slab_alloc_locked(ctx->shpool, size); //分配空间
        if (node == NULL) { //分配失败，返回NGX_ERROR
            return NGX_ERROR;
        }
    }
 
    node->key = hash;  //赋值
    lr = (ngx_http_limit_req_node_t *) &node->color;
    lr->len = (u_char) len;
    lr->excess = 0;
    ngx_memcpy(lr->data, data, len);
 
    ngx_rbtree_insert(&ctx->sh->rbtree, node);  //插入记录到红黑树与LRU队列
    ngx_queue_insert_head(&ctx->sh->queue, &lr->queue);
 
    if (account) { //如果是最后一条限流策略，则更新上次访问时间，待处理请求数目，返回NGX_OK
        lr->last = now;
        lr->count = 0;
        return NGX_OK;
    }
 
    lr->last = 0;
    lr->count = 1;
 
    ctx->node = lr;
 
    return NGX_AGAIN;  //非最后一条限流策略，返回NGX_AGAIN，继续校验下一条限流策略
     
}
```

举个例子，假如burst配置为0，待处理请求数初始为excess；令牌产生周期为T；如下图所示

![][6]
#### 3.2.2.2LRU淘汰策略

-----

上一节叩痛算法中，会执行ngx_http_limit_req_expire淘汰一条记录，每次都是从LRU队列末尾删除；

第二个参数n，当n==0时，强制删除末尾一条记录，之后再尝试删除一条或两条记录；n==1时，会尝试删除一条或两条记录；代码实现如下：

```c
static void ngx_http_limit_req_expire(ngx_http_limit_req_ctx_t *ctx, ngx_uint_t n)
{
    //最多删除3条记录
    while (n < 3) {
        //尾部节点
        q = ngx_queue_last(&ctx->sh->queue);
        //获取记录
        lr = ngx_queue_data(q, ngx_http_limit_req_node_t, queue);
         
        //注意：当为0时，无法进入if代码块，因此一定会删除尾部节点；当n不为0时，进入if代码块，校验是否可以删除
        if (n++ != 0) {
 
            ms = (ngx_msec_int_t) (now - lr->last);
            ms = ngx_abs(ms);
            //短时间内被访问，不能删除，直接返回
            if (ms < 60000) {
                return;
            }
             
            //有待处理请求，不能删除，直接返回
            excess = lr->excess - ctx->rate * ms / 1000;
            if (excess > 0) {
                return;
            }
        }
 
        //删除
        ngx_queue_remove(q);
 
        node = (ngx_rbtree_node_t *)
                   ((u_char *) lr - offsetof(ngx_rbtree_node_t, color));
 
        ngx_rbtree_delete(&ctx->sh->rbtree, node);
 
        ngx_slab_free_locked(ctx->shpool, node);
    }
}
```
#### 3.2.2.3 burst实现

-----

burst是为了应对突发流量的，偶然间的突发流量到达时，应该允许服务端多处理一些请求才行；

当burst为0时，请求只要超出限流速率就会被拒绝；当burst大于0时，超出限流速率的请求会被排队等待 处理，而不是直接拒绝；

排队过程如何实现？而且nginx还需要定时去处理排队中的请求；

2.2小节提到事件都有一个定时器，nginx是通过事件与定时器配合实现请求的排队与定时处理；

ngx_http_limit_req_handler方法有下面的代码：

```c
//计算当前请求还需要排队多久才能处理
delay = ngx_http_limit_req_account(limits, n, &excess, &limit);

//添加可读事件
if (ngx_handle_read_event(r->connection->read, 0) != NGX_OK) {
   return NGX_HTTP_INTERNAL_SERVER_ERROR;
}

r->read_event_handler = ngx_http_test_reading;
r->write_event_handler = ngx_http_limit_req_delay; //可写事件处理函数
ngx_add_timer(r->connection->write, delay);    //可写事件添加定时器（超时之前是不能往客户端返回的）
```

计算delay的方法很简单，就是遍历所有的限流策略，计算处理完所有待处理请求需要的时间，返回最大值；

```c
if (limits[n].nodelay) { //配置了nodelay时，请求不会被延时处理，delay为0
    continue;
}
 
delay = excess * 1000 / ctx->rate;
 
if (delay > max_delay) {
    max_delay = delay;
    *ep = excess;
    *limit = &limits[n];
}
```

简单看看可写事件处理函数ngx_http_limit_req_delay的实现

```c
static void ngx_http_limit_req_delay(ngx_http_request_t *r)
{
    
    wev = r->connection->write;
 
    if (!wev->timedout) {  //没有超时不会处理
 
        if (ngx_handle_write_event(wev, 0) != NGX_OK) {
            ngx_http_finalize_request(r, NGX_HTTP_INTERNAL_SERVER_ERROR);
        }
 
        return;
    }
 
    wev->timedout = 0;
 
    r->read_event_handler = ngx_http_block_reading;
    r->write_event_handler = ngx_http_core_run_phases;
 
    ngx_http_core_run_phases(r);  //超时了，继续处理HTTP请求
}
```
## 4.实战
## 4.1测试普通限流

* 1）配置nginx限流速率为1qps，针对客户端IP地址限流（返回状态码默认为503），如下：

```nginx
http{
    limit_req_zone $binary_remote_addr zone=test:10m rate=1r/s;
 
    server {
        listen       80;
        server_name  localhost;
        location / {
            limit_req zone=test;
            root   html;
            index  index.html index.htm;
        }
}
```


* 2）连续并发发起若干请求；
* 3）查看服务端access日志，可以看到22秒连续到达3个请求，只处理1个请求；23秒到达两个请求，第一个请求处理，第二个请求被拒绝


```
xx.xx.xx.xxx - - [22/Sep/2018:23:33:22 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:33:22 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:33:22 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:33:23 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:33:23 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
```
## 4.2测试burst

* 1）限速1qps时，超过请求会被直接拒绝，为了应对突发流量，应该允许请求被排队处理；因此配置burst=5，即最多允许5个请求排队等待处理；

```nginx
http{
    limit_req_zone $binary_remote_addr zone=test:10m rate=1r/s;
 
    server {
        listen       80;
        server_name  localhost;
        location / {
            limit_req zone=test burst=5;
            root   html;
            index  index.html index.htm;
        }
}
```


* 2）使用ab并发发起10个请求，ab -n 10 -c 10 [http://xxxxx][7]；
* 3）查看服务端access日志；根据日志显示第一个请求被处理，2到5四个请求拒绝，6到10五个请求被处理；为什么会是这样的结果呢？


查看ngx_http_log_module，注册handler到NGX_HTTP_LOG_PHASE阶段（HTTP请求处理最后一个阶段）；

因此实际情况应该是这样的：10个请求同时到达，第一个请求到达直接被处理，第2到6个请求到达，排队延迟处理（每秒处理一个）；第7到10个请求被直接拒绝，因此先打印access日志；

第2到6个请求米诶秒处理一个，处理完成打印access日志，即49到53秒每秒处理一个；

```
xx.xx.xx.xxx - - [22/Sep/2018:23:41:48 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:48 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:48 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:48 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:48 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:49 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:50 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:51 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:52 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [22/Sep/2018:23:41:53 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
```

* 4）ab统计的响应时间见下面，最小响应时间87ms，最大响应时间5128ms，平均响应时间为1609ms：

```
             min  mean[+/-sd] median   max
Connect:       41   44   1.7     44      46
Processing:    46 1566 1916.6   1093    5084
Waiting:       46 1565 1916.7   1092    5084
Total:         87 1609 1916.2   1135    5128
```
## 4.3测试nodelay

* 1）4.2显示，配置burst后，虽然突发请求会被排队处理，但是响应时间过长，客户端可能早已超时；因此添加配置nodelay，使得nginx紧急处理等待请求，以减小响应时间：

```nginx
http{
    limit_req_zone $binary_remote_addr zone=test:10m rate=1r/s;
 
    server {
        listen       80;
        server_name  localhost;
        location / {
            limit_req zone=test burst=5 nodelay;
            root   html;
            index  index.html index.htm;
        }
}
```


* 2）使用ab并发发起10个请求，ab -n 10 -c 10 [http://xxxx/][8]；
* 3）查看服务端access日志；第一个请求直接处理，第2到6个五个请求排队处理（配置nodelay，nginx紧急处理），第7到10四个请求被拒绝


```
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 200 612 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
xx.xx.xx.xxx - - [23/Sep/2018:00:04:47 +0800] "GET / HTTP/1.0" 503 537 "-" "ApacheBench/2.3"
```

* 4）ab统计的响应时间见下面，最小响应时间85ms，最大响应时间92ms，平均响应时间为88ms：

```
              min  mean[+/-sd] median   max
Connect:       42   43   0.5     43      43
Processing:    43   46   2.4     47      49
Waiting:       42   45   2.5     46      49
Total:         85   88   2.8     90      92
```
## 总结

本文首先分析常用限流算法（漏桶算法与令牌桶算法），并简单介绍nginx处理HTTP请求的过程，nginx定时事件实现；然后详细分析ngx_http_limit_req_module模块的基本数据结构，及其限流过程；并以实例帮助读者体会nginx限流的配置及结果。至于另一个模块ngx_http_limit_conn_module是针对链接数的限流，比较容易理解，在此就不做详细介绍。

[7]: http://xxxxx
[8]: http://xxxx/
[0]: ./img/bVbhq3h.png
[1]: ./img/bVbhq3k.png
[2]: ./img/bVbhq3q.png
[3]: ./img/bVbhq4c.png
[4]: ./img/bVbhq4u.png
[5]: ./img/bVbhq4D.png
[6]: ./img/bVbhq4Q.png
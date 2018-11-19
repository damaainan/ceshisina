## 【Nginx源码分析】Nginx配置文件解析（二）

来源：[https://segmentfault.com/a/1190000016922188](https://segmentfault.com/a/1190000016922188)

运营研发团队  李乐

本文作为nginx配置文件解析的第二篇，开始讲解nginx配置文件解析的源码，在阅读本文之前，希望你已经阅读过第一篇。[《nginx配置文件解析（一）》][7]
## 1.1配置解析流程

解析配置的入口函数是ngx_conf_parse(ngx_conf_t cf, ngx_str_t filename)，其输入参数filename表示配置文件路径，如果为NULL表明此时解析的是指令块。

那么cf是什么呢？先看看其结构体声明：

```LANG
struct ngx_conf_s {
    char                 *name; //当前读取到的指令名称
    ngx_array_t          *args; //当前读取到的指令参数
 
    ngx_cycle_t          *cycle; //指向全局cycle
    ngx_pool_t           *pool;  //内存池
    ngx_conf_file_t      *conf_file; //配置文件
 
    void                 *ctx;   //上下文
    ngx_uint_t            module_type; //模块类型
    ngx_uint_t            cmd_type;   //指令类型
 
    ngx_conf_handler_pt   handler; //一般都是NULL，暂时不管
};
```

重点需要关注这些字段：

* 1）name和args存储当前读取到的指令信息；
* 2）ctx上下文，就是我们上面所说的指令上下文，想象下如果没有ctx我们获取该指令最终存储的位置；
* 3）module_type和cmd_type分别表示模块类型与指令类型；读取到某条指令时，需要遍历所有模块的指令数组，通过这两个字段可以过滤某些不应该解析该配置的模块与指令。


函数ngx_conf_parse逻辑比较简单，就是读取完整指令，并调用函数ngx_conf_handler处理指令。

函数ngx_conf_handler主要逻辑是，遍历类型为cf->module_type的模块，查找该模块指令数组中类型为cf->cmd_type的指令；如果没找到打印错误日志并返回错误；如果找到还需要校验指令参数等是否合法；最后才是调用set函数设置。

这些流程都比较简单，难点是如何根据ctx获取到该配置最终存储的位置。下面的代码需要结合上图来分析。配置肯定是存储在某个结构体的，所以需要通过ctx找到对应结构体。

```LANG
if (cmd->type & NGX_DIRECT_CONF) {
    //此类型的cf->ctx只会是conf_ctx，直接获取第index个元素，说明该数组元素已经指向了某个结构体
    conf = ((void **) cf->ctx)[ngx_modules[i]->index]; 
 
} else if (cmd->type & NGX_MAIN_CONF) {
    //此类型的cf->ctx只会是conf_ctx，获取的是第index个元素的地址，原因就在于此时数组元素指向NULL
    conf = &(((void **) cf->ctx)[ngx_modules[i]->index]);
 
} else if (cf->ctx) {  //此时cf->ctx可能是events_ctx，http_ctx，srv_ctx或者loc_ctx
 
    //假设cf->ctx为http_ctx，此时cmd->conf是字段main_conf,srv_conf或者loc_conf在结构体ngx_http_conf_ctx_t中的偏移量
    confp = *(void **) ((char *) cf->ctx + cmd->conf);
 
    if (confp) {
        conf = confp[ngx_modules[i]->ctx_index]; //一样是获取数组的第ctx_index个元素，此时一定是指向某个结构体
    }
}
 
rv = cmd->set(cf, cmd, conf); //调用set函数设置，注意这里入参conf
```
## 1.2 配置文件的解析

函数ngx_init_cycle会调用ngx_conf_parse开始配置文件的解析。

解析配置文件首先需要创建配置文件上下文，并初始化结构体ngx_conf_t；

```LANG
//创建配置文件上下文，并初始化上下文数组元素
 
cycle->conf_ctx = ngx_pcalloc(pool, ngx_max_module * sizeof(void *));//ngx_max_module为模块总数目
 
//需要遍历所有核心模块，并调用其create_conf创建配置结构体，存储到上下文数组
for (i = 0; ngx_modules[i]; i++) {
    if (ngx_modules[i]->type != NGX_CORE_MODULE) {
        continue;
    }
 
    module = ngx_modules[i]->ctx;
 
    if (module->create_conf) {
        rv = module->create_conf(cycle);
        if (rv == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
        cycle->conf_ctx[ngx_modules[i]->index] = rv;
    }
}
 
//初始化结构体ngx_conf_t
conf.ctx = cycle->conf_ctx;
conf.module_type = NGX_CORE_MODULE;
conf.cmd_type = NGX_MAIN_CONF;
```

读者可以查找下代码，看看哪些核心模块有create_conf方法。执行此步骤之后，可以画出下图：

![][0]

结合2.2节所示的代码逻辑，可以很容易知道，核心模块ngx_core_module的配置指令都是带有NGX_DIRECT_CONF标识的，conf_ctx数组第0个元素就指向其配置结构体ngx_core_conf_t。

```LANG
if (cmd->type & NGX_DIRECT_CONF) {
    conf = ((void **) cf->ctx)[ngx_modules[i]->index];
}
 
rv = cmd->set(cf, cmd, conf);
```

以配置worker_processes（设置worker进程数目）为例，其指令结构定义如下：

```LANG
{ ngx_string("worker_processes"),
  NGX_MAIN_CONF|NGX_DIRECT_CONF|NGX_CONF_TAKE1,
  ngx_set_worker_processes,
  0,
  0,
  NULL }
```

注意此时函数ngx_set_worker_processes入参的第三个参数已经指向了结构体ngx_core_conf_t，所以可以强制类型转换

```LANG
static char * ngx_set_worker_processes(ngx_conf_t *cf, ngx_command_t *cmd, void *conf){
    ngx_core_conf_t  *ccf;
    ccf = (ngx_core_conf_t *) conf;
}
```
## 1.3 events指令块的解析

ngx_events_module模块（核心模块）中定义了events指令结构，如下：

```LANG
{ ngx_string("events"),
  NGX_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
  ngx_events_block,
  0,
  0,
  NULL }
```

events配置指令处理函数为ngx_events_block；根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cmd->type & NGX_MAIN_CONF) {
    conf = &(((void **) cf->ctx)[ngx_modules[i]->index]);  //此时cf->ctx仍然是conf_ctx
}
 
rv = cmd->set(cf, cmd, conf);
```

即此时函数ngx_events_block的第三个输入参数是conf_ctx数组第index个元素的地址，且该元素指向NULL。

函数ngx_events_block主要需要处理3件事：1）创建events_ctx上下文；2）调用所有事件模块的create_conf方法创建配置结构；3）修改cf->ctx （注意解析events块时配置上下文会发生改变），cf->module_type 和cf->cmd_type 并调用ngx_conf_parse函数解析events块中的配置

```LANG
static char *
ngx_events_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    //创建配置上下文events_ctx，只是一个void*结构
    ctx = ngx_pcalloc(cf->pool, sizeof(void *));
    //数组，指向所有时间模块创建的配置结构；ngx_event_max_module为事件模块数目
    *ctx = ngx_pcalloc(cf->pool, ngx_event_max_module * sizeof(void *));
     
    //conf是conf_ctx数组某个元素的地址；即让该元素指向配置上下文events_ctx
    *(void **) conf = ctx;
 
    //遍历所有事件模块，创建配置结构
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_EVENT_MODULE) {
            continue;
        }
 
        m = ngx_modules[i]->ctx;
 
        if (m->create_conf) {
            (*ctx)[ngx_modules[i]->ctx_index] = m->create_conf(cf->cycle);
        }
    }
 
    //修改cf的配置上下文，模块类型，指令类型；原始cf暂存在pcf变量
    pcf = *cf;
    cf->ctx = ctx;
    cf->module_type = NGX_EVENT_MODULE;
    cf->cmd_type = NGX_EVENT_CONF;
 
    //解析events块中的配置
    rv = ngx_conf_parse(cf, NULL);
 
    //还原cf
    *cf = pcf;
}
```

在linux机器上采用默认选项编译nginx代码，事件模块通常只有ngx_event_core_module和ngx_event_core_module，且两个模块都有create_conf方法，执行上述代码之后，可以画出以下配置存储结构图：

![][1]

以ngx_event_core_module模块中的配置connections为例（设置连接池连接数目），其结构定义如下：

```LANG
{ ngx_string("connections"),
  NGX_EVENT_CONF|NGX_CONF_TAKE1,
  ngx_event_connections,
  0,
  0,
  NULL }
```

connections配置指令处理函数为ngx_event_connections；根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cf->ctx) {  //此时cf->ctx是events_ctx
  
    //confp为数组首地址
    confp = *(void **) ((char *) cf->ctx + cmd->conf);
 
    if (confp) {
        conf = confp[ngx_modules[i]->ctx_index];  //获取数组元素
    }
}
 
rv = cmd->set(cf, cmd, conf); //ngx_event_core_module的ctx_index为0，此时conf指向结构体ngx_event_conf_t
```

函数ngx_event_connections实现较为简单，只需要给结构体ngx_event_conf_t相应字段赋值即可；注意输入参数conf指向结构体ngx_event_conf_t，可以直接强制类型转换。

```LANG
static char *
ngx_event_connections(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    ngx_event_conf_t  *ecf = conf;
}
```
## 1.4 http指令块的解析

上面学习了events指令块的解析，http指令块、server指令块和location指令块的解析都是非常类似的。

```LANG
ngx_http_module模块（核心模块）中定义了http指令结构，如下：

{ ngx_string("http"),
  NGX_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
  ngx_http_block,
  0,
  0,
  NULL }
```

http配置指令的处理函数为ngx_http_block，根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cmd->type & NGX_MAIN_CONF) {
    conf = &(((void **) cf->ctx)[ngx_modules[i]->index]);    //此时cf->ctx仍然是conf_ctx
}
  
rv = cmd->set(cf, cmd, conf);
```

即此时函数ngx_http_block的第三个输入参数是conf_ctx数组第index个元素的地址，且该元素指向NULL。

函数ngx_http_block主要需要处理3件事：1）创建http_ctx上下文；2）调用所有http模块的create_main_conf、create_srv_conf和create_loc_conf方法创建配置结构；3）修改cf->ctx （注意解析http块时配置上下文会发生改变），cf->module_type 和cf->cmd_type 并调用ngx_conf_parse函数解析http块中的配置

```LANG
static char * ngx_http_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf){
    //创建http_ctx配置长下文
    ctx = ngx_pcalloc(cf->pool, sizeof(ngx_http_conf_ctx_t));
 
    //conf是conf_ctx数组某个元素的地址，即该元素指向http_ctx配置上下文
    *(ngx_http_conf_ctx_t **) conf = ctx;
 
    //初始化main_conf数组、srv_conf数组和loc_conf数组；ngx_http_max_module为http模块数目
    ctx->main_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
    ctx->srv_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
    ctx->loc_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
 
    //调用所有http模块的create_main_conf方法、create_srv_conf方法和create_loc_conf创建相应配置结构
    for (m = 0; ngx_modules[m]; m++) {
        if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
            continue;
        }
 
        module = ngx_modules[m]->ctx;
        mi = ngx_modules[m]->ctx_index;
 
        if (module->create_main_conf) {
            ctx->main_conf[mi] = module->create_main_conf(cf);
        }
 
        if (module->create_srv_conf) {
            ctx->srv_conf[mi] = module->create_srv_conf(cf);   
        }
 
        if (module->create_loc_conf) {
            ctx->loc_conf[mi] = module->create_loc_conf(cf);
        }
    }
 
    //修改cf的配置上下文，模块类型，指令类型；原始cf暂存在pcf变量
    pcf = *cf;
    cf->ctx = ctx;
    cf->module_type = NGX_HTTP_MODULE;
    cf->cmd_type = NGX_HTTP_MAIN_CONF;
 
    //解析http块中的配置
    rv = ngx_conf_parse(cf, NULL);
 
    //还原cf
    *cf = pcf;
}
```

执行上述代码之后，可以画出以下配置存储结构图：

![][2]

http_ctx配置上下文类型为结构体ngx_http_conf_ctx_t，其只有三个字段main_conf、srv_conf和loc_conf，分别指向一个数组，数组的每个元素指向的是对应的配置结构。

比如说ngx_http_core_module是第一个http模块，其create_main_conf方法创建的配置结构为ngx_http_core_main_conf_t。

以ngx_http_core_module模块的配置keepalive_timeout（该配置可以出现在location块、server块和http块，假设在http块中添加该配置）为例，指令结构定义如下：

```LANG
{ ngx_string("keepalive_timeout"),
  NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF|NGX_CONF_TAKE12,
  ngx_http_core_keepalive,
  NGX_HTTP_LOC_CONF_OFFSET,
  0,
  NULL }
 
#define NGX_HTTP_LOC_CONF_OFFSET   offsetof(ngx_http_conf_ctx_t, loc_conf)
```

可以看到该指令结构的第四个参数不为0了，为loc_conf字段在结构体ngx_http_conf_ctx_t中的偏移量。

keepalive_timeout配置指令处理函数为ngx_http_core_keepalive；根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cf->ctx) {  //此时cf->ctx是http_ctx
  
    //cmd->conf为loc_conf字段在结构体ngx_http_conf_ctx_t中的偏移量；confp为loc_conf数组首地址
    confp = *(void **) ((char *) cf->ctx + cmd->conf);
 
    if (confp) {
        conf = confp[ngx_modules[i]->ctx_index];  //获取数组元素
    }
}
  
rv = cmd->set(cf, cmd, conf); //ngx_http_core_module的ctx_index为0，此时conf指向结构体ngx_http_core_loc_conf_t
```

函数ngx_http_core_keepalive实现较为简单，这里不做详述。
## 1.5 server指令块的解析

ngx_http_core_module模块中定义了server指令结构，如下：

```LANG
{ ngx_string("server"),
  NGX_HTTP_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
  ngx_http_core_server,
  0,
  0,
  NULL }
```

server配置指令的处理函数为ngx_http_core_server，根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cf->ctx) {  //此时cf->ctx是http_ctx
  
    //cmd->conf为0；confp为main_conf数组首地址
    confp = *(void **) ((char *) cf->ctx + cmd->conf);
 
    if (confp) {
        conf = confp[ngx_modules[i]->ctx_index];  //获取数组元素
    }
}
  
rv = cmd->set(cf, cmd, conf); //ngx_http_core_module的ctx_index为0，此时conf指向结构体ngx_http_core_main_conf_t
```

函数ngx_http_core_server主要需要处理4件事：1）创建srv_ctx上下文；2）调用所有http模块的create_srv_conf和create_loc_conf方法创建配置结构；3）将srv_ctx上下文添加到http_ctx配置上下文；4）修改cf->ctx （注意解析http块时配置上下文会发生改变），cf->module_type 和cf->cmd_type 并调用ngx_conf_parse函数解析server块中的配置

```LANG
static char * ngx_http_core_server(ngx_conf_t *cf, ngx_command_t *cmd, void *dummy){
    //创建srv_ctx配置上下文
    ctx = ngx_pcalloc(cf->pool, sizeof(ngx_http_conf_ctx_t));
 
    //cf->ctx为http_ctx配置上下文
    http_ctx = cf->ctx;
 
    //main_conf共用同一个（server块中不会有NGX_HTTP_MAIN_CONF类型的配置，所以其实是不需要main_conf的）
    ctx->main_conf = http_ctx->main_conf;
    ctx->srv_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
    ctx->loc_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
     
    //遍历所有http模块，调用其create_srv_conf方法和create_loc_conf创建相应配置结构
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_HTTP_MODULE) {
            continue;
        }
 
        module = ngx_modules[i]->ctx;
 
        if (module->create_srv_conf) {
            mconf = module->create_srv_conf(cf);
            ctx->srv_conf[ngx_modules[i]->ctx_index] = mconf;
        }
 
        if (module->create_loc_conf) {
            mconf = module->create_loc_conf(cf);
            ctx->loc_conf[ngx_modules[i]->ctx_index] = mconf;
        }
    }
 
    //注意这里实现将srv_ctx上下文添加到http_ctx配置上下文；代码不好理解，可参考下面的示意图。
 
    //ngx_http_core_module模块是第一个http模块。获取其创建的srv_conf类型的配置结构ngx_http_core_srv_conf_t；将其ctx字段指向srv_ctx配置上下文
    cscf = ctx->srv_conf[ngx_http_core_module.ctx_index];
    cscf->ctx = ctx;
 
    //main_conf是http_ctx上下文的数组；获取其创建的main_conf类型的配置结构ngx_http_core_main_conf_t；
    //并且将，srv_ctx配置上下文的配置结构ngx_http_core_srv_conf_t添加到http_ctx配置上下文的ngx_http_core_main_conf_t配置结构的servers数组
    cmcf = ctx->main_conf[ngx_http_core_module.ctx_index];
    cscfp = ngx_array_push(&cmcf->servers);
    *cscfp = cscf;
 
    //修改cf的配置上下文，模块类型，指令类型；原始cf暂存在pcf变量
    pcf = *cf;
    cf->ctx = ctx;
    cf->cmd_type = NGX_HTTP_SRV_CONF;
 
    //解析server块中的配置；注意此时配置上下文为srv_ctx
    rv = ngx_conf_parse(cf, NULL);
 
    //还原cf
    *cf = pcf;
}
```

执行上述代码之后，可以画出以下配置存储结构图，这里只画出http_ctx与srv_ctx配置上下文的示意图：

![][3]

注意上图红色的箭头，按照红色箭头的引用，可以从http_ctx配置上下文找到srv_ctx配置上下文；

看到这里可能会觉得存储结构好复杂，别着急，等解析location指令块时，图还会更复杂。

但是不用担心，这只是解析时候的存储结构，最终还会做一些优化，查找时并不是按照这种结构查找的。

至于server指令块内部的配置，比较简单，这里不再举例详述。
## 1.6 location指令块的解析

ngx_http_core_module模块中定义了location指令结构，如下：

```LANG
{ ngx_string("location"),
  NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF|NGX_CONF_BLOCK|NGX_CONF_TAKE12,
  ngx_http_core_location,
  NGX_HTTP_SRV_CONF_OFFSET,
  0,
  NULL }
 
#define NGX_HTTP_SRV_CONF_OFFSET   offsetof(ngx_http_conf_ctx_t, srv_conf)
```

可以看到，location指令可以出现在server指令块和location指令块（即location本身可以嵌套）；location配置可以由一个或者两个参数；注意指令结构第四个参数不为0了，为srv_conf字段在结构体ngx_http_conf_ctx_t中的偏移量；指令处理函数为ngx_http_core_location。根据其类型可以知道在ngx_conf_handler调用该函数时走的是以下分支：

```LANG
else if (cf->ctx) {  //此时cf->ctx是srv_ctx
  
    //cmd->conf为srv_conf字段在结构体ngx_http_conf_ctx_t中的偏移量；confp为srv_conf数组首地址
    confp = *(void **) ((char *) cf->ctx + cmd->conf);
 
    if (confp) {
        conf = confp[ngx_modules[i]->ctx_index];  //获取数组元素
    }
}
  
rv = cmd->set(cf, cmd, conf); //ngx_http_core_module的ctx_index为0，此时conf指向结构体ngx_http_core_srv_conf_t
```

函数ngx_http_core_server主要需要处理3件事：1）创建loc_ctx上下文；2）调用所有http模块的create_loc_conf方法创建配置结构；3）将loc_ctx上下文添加到srv_ctx配置上下文；4）修改cf->ctx （注意解析http块时配置上下文会发生改变），cf->module_type 和cf->cmd_type 并调用ngx_conf_parse函数解析location块中的配置

```LANG
static char * ngx_http_core_location(ngx_conf_t *cf, ngx_command_t *cmd, void *dummy){
    //创建loc_conf上下文
    ctx = ngx_pcalloc(cf->pool, sizeof(ngx_http_conf_ctx_t));
 
    //cf->ctx指向srv_conf上下文
    pctx = cf->ctx;
 
    //main_conf与srv_conf与srv_ctx上下文公用；
    //（location块中不会有NGX_HTTP_MAIN_CONF和NGX_HTTP_SRV_CONF类型的配置，所以其实是不需要main_conf和srv_conf的）
    ctx->main_conf = pctx->main_conf;
    ctx->srv_conf = pctx->srv_conf;
    ctx->loc_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);
 
    //遍历所有http模块，调用其create_loc_conf方法创建相应配置结构
    for (i = 0; ngx_modules[i]; i++) {
        if (ngx_modules[i]->type != NGX_HTTP_MODULE) {
            continue;
        }
 
        module = ngx_modules[i]->ctx;
 
        if (module->create_loc_conf) {
            ctx->loc_conf[ngx_modules[i]->ctx_index] = module->create_loc_conf(cf);
        }
    }
 
    //ngx_http_core_module是第一个http模块；获取loc_ctx配置上下文的loc_conf数组的第一个元素，即ngx_http_core_loc_conf_t结构
    //将该结构的loc_conf字段指向loc_ctx配置上下文的loc_conf数组首地址
    clcf = ctx->loc_conf[ngx_http_core_module.ctx_index];
    clcf->loc_conf = ctx->loc_conf;
 
    获取srv_ctx配置上下文的loc_conf数组的第一个元素，即ngx_http_core_loc_conf_t结构
    pclcf = pctx->loc_conf[ngx_http_core_module.ctx_index];
 
    //将loc_ctx配置上下文的ngx_http_core_loc_conf_t结构添加到srv_ctx配置上下文的ngx_http_core_loc_conf_t的locations字段
    //locations是一个双向链表，链表结构也挺有意思的，有兴趣的读者可以研究下
    if (ngx_http_add_location(cf, &pclcf->locations, clcf) != NGX_OK) {
    
    }
}
```

执行上述代码之后，可以画出以下配置存储结构图，这里只画出srv_ctx和loc_ctx配置上下文的示意图：

![][4]

注意上图红色的箭头，按照红色箭头的引用，可以从srv_ctx配置上下文找到loc_ctx配置上下文；其实这句话是不严谨的，准确的说，从srv_ctx配置上下文只能找到loc_ctx配置上下文的loc_conf数组。

原因就在于，所有的配置其实都是存储在main_conf数组、srv_conf数组和loc_conf数组。而loc_conf配置上下文的main_conf数组和srv_conf数组其实是没有存配置的。

所以只需要loc_conf配置上下文的loc_conf数组即可。

这里还遗留一个问题，location参数的解析，这也是我们应该关注的重点，将在2.9节讲述。至于location指令块内部的配置，比较简单，这里不再举例详述。
## 1.7 配置合并

到这一步其实配置文件已经算是解析完成了，但是http相关存储结构过于复杂。

而且还有一个问题：http_ctx配置上下文和srv_ctx配置上下文都有srv_conf，同时存储NGX_HTTP_SRV_CONF类型的配置；而http_ctx、srv_ctx和loc_ctx配置上下文都有loc_conf数组，

同时存储NGX_HTTP_LOC_CONF类型的配置。那么当配置同时出现在多个配置上下文中该如何处理，以哪个为准呢？

观察1.1节nginx模块的介绍，大多http模块都有这两个方法merge_srv_conf和merge_loc_conf，用于合并不同配置上下文的相同配置。

这里的配置合并其实就是两个srv_conf数组或者loc_conf数组的合并。

ngx_http_block函数中解析完成http块内部所有配置之后，执行合并操作。

```LANG
//此处的ctx是http_ctx配置上下文。不理解的话可以参照上面的示意图。
cmcf = ctx->main_conf[ngx_http_core_module.ctx_index];
cscfp = cmcf->servers.elts;
 
//遍历所有http模块（其实就是遍历合并srv_conf和loc_conf数组的每个元素）
for (m = 0; ngx_modules[m]; m++) {
    if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
        continue;
    }
 
 
    module = ngx_modules[m]->ctx;
    mi = ngx_modules[m]->ctx_index;
 
    //init_main_conf是初始化配置默认值的，有些配置没有赋值时需要初始化默认值
    if (module->init_main_conf) {
        rv = module->init_main_conf(cf, ctx->main_conf[mi]);
    }
 
    //合并
    rv = ngx_http_merge_servers(cf, cmcf, module, mi);
     
}
```

合并操作由函数ngx_http_merge_servers实现：

```LANG
static char * ngx_http_merge_servers(ngx_conf_t *cf, ngx_http_core_main_conf_t *cmcf,
    ngx_http_module_t *module, ngx_uint_t ctx_index) {
 
    //ngx_http_core_srv_conf_t数组
    cscfp = cmcf->servers.elts;
 
    //cf->ctx指向http_ctx配置上下文
    ctx = (ngx_http_conf_ctx_t *) cf->ctx;
    saved = *ctx;
 
    //遍历多个ngx_http_core_srv_conf_t（多个server配置）
    for (s = 0; s < cmcf->servers.nelts; s++) {
 
        //通过ngx_http_core_srv_conf_t可以找到每个srv_ctx配置上下文的srv_conf数组
        ctx->srv_conf = cscfp[s]->ctx->srv_conf;
 
        //合并http_ctx配置上下文的srv_conf数组中配置到srv_ctx配置上下文的srv_conf数组
        if (module->merge_srv_conf) {
            rv = module->merge_srv_conf(cf, saved.srv_conf[ctx_index],cscfp[s]->ctx->srv_conf[ctx_index]);
        }
 
        if (module->merge_loc_conf) {
            //通过ngx_http_core_srv_conf_t可以找到每个srv_ctx配置上下文的loc_conf数组
            ctx->loc_conf = cscfp[s]->ctx->loc_conf;
 
            //合并http_ctx配置上下文的loc_conf数组中配置到srv_ctx配置上下文的loc_conf数组
            rv = module->merge_loc_conf(cf, saved.loc_conf[ctx_index],
                                        cscfp[s]->ctx->loc_conf[ctx_index]);
             
            //合并srv_ctx配置上下文的loc_conf数组中配置到loc_ctx配置上下文的loc_conf数组
            clcf = cscfp[s]->ctx->loc_conf[ngx_http_core_module.ctx_index];
            rv = ngx_http_merge_locations(cf, clcf->locations, cscfp[s]->ctx->loc_conf, module, ctx_index);
        }
    }
}
```

函数ngx_http_merge_locations的实现与函数ngx_http_merge_servers基本类似，这里不再详述。合并示意图如下：

![][5]

最终http相关配置存储在：一个http_ctx配置上下文的main_conf数组，多个srv_ctx配置上下文的srv_conf数组，多个loc_ctx配置上下文的loc_conf数组；为图中阴影部分。

http_ctx、srv_ctx和loc_ctx之间的引用关系参考红色箭头。

问题就在于如何查找到多个srv_ctx配置上下文的srv_conf数组，多个loc_ctx配置上下文的loc_conf数组，将在第3节介绍。
## 1.8 location配置优化

location配置的语法规则是：location [=|~|~*|^~] /uri/ { … }，可以简单讲location配置分为三种类型：精确匹配，最大前缀匹配和正则匹配。

分类规则如下：1）以“=”开始的为精确匹配；2）以“~”和“~*”开始的分别为为区分大小写的正则匹配和不区分大小写的正则匹配；3）以“^~”开始的是最大前缀匹配；4）参数只有/uri的是最大前缀匹配。

可以看到类型3和类型4都是最大类型匹配，那么这两者有什么区别呢？在查找匹配location时可以看到。

那么当我们配置了多个locaiton，且请求uri可以满足多个location的匹配规则时，最终选择哪个配置呢？不同location类型有不同的匹配优先级。

我们先看下location配置的分类，显然可以根据第一个字符来分类，location配置的参数以及类型等信息都存储在ngx_http_core_loc_conf_t以下几个字段：

```LANG
struct ngx_http_core_loc_conf_s {
    ngx_str_t     name;   //名称，即location配置的uri参数
    ngx_http_regex_t  *regex;  //编译后的正则表达式，可标识类型2
  
    unsigned      exact_match:1;  //标识以=开头的location配置，类型1
    unsigned      noregex:1;   //查找匹配的location配置时有用。标识匹配到该location之后，不再尝试匹配正则类型的locaiton；类型3带有此标识
 
    ngx_http_location_tree_node_t   *static_locations; //通过命名可以看到这是一棵树（存储的是类型为1，3和4的locaiton配置）
    ngx_http_core_loc_conf_t       **regex_locations;   //存储所有的正则匹配
}
```

2.7节解析location指令块时提到，srv_ctx上下文的loc_conf数组，第一个元素指向类型为ngx_http_core_loc_conf_t的结构体，结构体的locations字段时一个双向链表，存储的是当前server指令块内部配置的所有location。

双向链表节点定义如下：

```LANG
typedef struct {
    ngx_queue_t                      queue; //双向链表统一头部；该结构维护了prev和next指针；
    ngx_http_core_loc_conf_t        *exact; //类型为1和2的location配置存储在链表节点的此字段
    ngx_http_core_loc_conf_t        *inclusive; //类型为3和4的location配置存储在链表节点的此字段
} ngx_http_location_queue_t;
```

location已经按照类型做好了标记，且存储在双向链表，为了实现location的高效优先级查找，需要给location配置排序，同时将多个location配置形成一棵树。

这些操作都是由函数ngx_http_block 执行的，且在解析http块内的所有配置之后。

```LANG
static char * ngx_http_block(ngx_conf_t *cf, ngx_command_t *cmd, void *conf){
     
    //指向比较乱。需要参考上面示意图的红色箭头。
 
    //ctx指向http_ctx配置上下文
    cmcf = ctx->main_conf[ngx_http_core_module.ctx_index];
    cscfp = cmcf->servers.elts;
 
    //遍历所有srv_ctx上下文
    for (s = 0; s < cmcf->servers.nelts; s++) {
 
        clcf = cscfp[s]->ctx->loc_conf[ngx_http_core_module.ctx_index];
 
        //该方法实现了location配置排序，以及将双向链表中正则类型的location配置裁剪出来
        if (ngx_http_init_locations(cf, cscfp[s], clcf) != NGX_OK) {
            return NGX_CONF_ERROR;
        }
 
        //双向链表中只剩下类型1、3和4的location配置了
        if (ngx_http_init_static_location_trees(cf, clcf) != NGX_OK) {
            return NGX_CONF_ERROR;
        }
    }
}
```

下面分别分析location配置排序，正则类型location配置的裁剪，以及形成location树：
* 1）location配置排序由函数ngx_queue_sort(ngx_queue_t queue, ngx_int_t ( cmp)(const ngx_queue_t , const ngx_queue_t ))实现，输入参数queue为双向链表，cmp为链表节点的比较函数。ngx_queue_sort函数按照从小到大排序，且采用稳定的排序算法（两个元素相等时，排序后顺序与排序之前的原始顺序相同）

locations双向链表节点的比较函数为ngx_http_cmp_locations，通过该函数就可以知道location配置的排序规则，实现如下：

```LANG
//与一般比较函数一样，返回1表示one大于two；0表示两者相等；-1表示one小于two
static ngx_int_t ngx_http_cmp_locations(const ngx_queue_t *one, const ngx_queue_t *two) {
    //正则类型的配置大于其余类型的配置
    if (first->regex && !second->regex) {
        return 1;
    }
    if (!first->regex && second->regex) {
        return -1;
    }
    if (first->regex || second->regex) {
        return 0;
    }
 
    rc = ngx_filename_cmp(first->name.data, second->name.data,
                        ngx_min(first->name.len, second->name.len) + 1);
 
    //按照location名称，即uri排序；且当两个uri前缀相同时，保证精确匹配类型的location排在前面
    if (rc == 0 && !first->exact_match && second->exact_match) {
        return 1;
    }
 
    return rc;
}
```

按照上述比较函数的规则排序后，正则类型的location配置一定是排列在双向链表尾部；精确匹配和最大前缀匹配首先按照uri字母序排列，且当两个uri前缀相同时，精确匹配类型排列在最大前缀匹配的前面。

* 2）经历了第一步location已经排好序了，且正则类型的排列在双向链表尾部，这样就很容易裁剪出所有正则类型的location配置了。只需要从头到尾遍历双向链表，直至查找到正则类型的location配置，并从该位置出将双向链表拆分开来即可。
* 3）双向链表中只剩下精确匹配类型和最大前缀匹配类型的location配置了，且都是按照uri字母序排序的，这些配置会被组织成一棵树，方便查找。


形成的这棵树是一棵三叉树，每个节点node都有三个子节点，left、tree和right。left一定小于node；right一定大于node；tree与node前缀相同，且tree节点uri长度一定大于node节点uri长度。

注意只有最大前缀匹配的配置才有tree节点。

思考下为什么会有tree节点，且最大前缀匹配才有tree节点呢？node匹配成功后，tree节点还有可能匹配成功。

形成树过程这里不做详述，有兴趣的读者可以研究下函数ngx_http_init_static_location_trees的实现。
## 总结

至此配置文件解析完成，http、server和location相关配置最终存储在main_conf、多个srv_conf和多个loc_conf数组中，但是当服务器接收到客户端请求时，如何查找对应的srv_conf数组和loc_conf数组呢？

将在第三篇《nginx配置文件解析（三）》讲解。

希望交流，一起学习Nginx PHP Redis 等源码的朋友请入微信群：

![][6]

[7]: https://segmentfault.com/a/1190000016913713
[0]: ./img/bVbjakA.png
[1]: ./img/bVbjalX.png
[2]: ./img/bVbjamP.png
[3]: ./img/bVbjann.png
[4]: ./img/bVbjanE.png
[5]: ./img/bVbjan0.png
[6]: ./img/bVbjaqD.png
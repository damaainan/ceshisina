### 概述

       在上一篇文章《 [Nginx 启动初始化过程](http://blog.csdn.net/chenhanzhun/article/details/42611315)》简单介绍了 Nginx 启动的过程，并分析了其启动过程的源码。在启动过程中有一个步骤非常重要，就是调用函数 ngx_init_cycle()，该函数的调用为配置解析提供了接口。配置解析接口大概可分为两个阶段：**准备数据阶段**和**配置解析阶段**；

       准备数据阶段包括：

- 准备内存；
- 准备错误日志；
- 准备所需数据结构；

       配置解析阶段是调用函数：

```c
    /* 配置文件解析 */  
    if (ngx_conf_param(&amp;conf) != NGX_CONF_OK) {/* 带有命令行参数'-g' 加入的配置 */  
        environ = senv;  
        ngx_destroy_cycle_pools(&amp;conf);  
        return NULL;  
    }  
  
    if (ngx_conf_parse(&amp;conf, &amp;cycle->conf_file) != NGX_CONF_OK) {/* 解析配置文件*/  
        environ = senv;  
        ngx_destroy_cycle_pools(&amp;conf);  
        return NULL;  
    }  

```

### 配置解析

### ngx_conf_t 结构体

       该结构体用于 Nginx 在解析配置文件时描述每个指令的属性，也是Nginx 程序中非常重要的一个数据结构，其定义于文件：[src/core/ngx_conf_file.h](http://lxr.nginx.org/source/src/core/ngx_conf_file.h)  

```c
/* 解析配置时所使用的结构体 */
struct ngx_conf_s {
    char                 *name;     /* 当前解析到的指令 */
    ngx_array_t          *args;     /* 当前指令所包含的所有参数 */

    ngx_cycle_t          *cycle;    /* 待解析的全局变量ngx_cycle_t */
    ngx_pool_t           *pool;     /* 内存池 */
    ngx_pool_t           *temp_pool;/* 临时内存池，分配一些临时数组或变量 */
    ngx_conf_file_t      *conf_file;/* 待解析的配置文件 */
    ngx_log_t            *log;      /* 日志信息 */

    void                 *ctx;      /* 描述指令的上下文 */
    ngx_uint_t            module_type;/* 当前解析的指令的模块类型 */
    ngx_uint_t            cmd_type; /* 当前解析的指令的指令类型 */

    ngx_conf_handler_pt   handler;  /* 模块自定义的handler，即指令自定义的处理函数 */
    char                 *handler_conf;/* 自定义处理函数需要的相关配置 */
};

```

#### 配置文件信息 conf_file

       conf_file 是存放 Nginx 配置文件的相关信息。ngx_conf_file_t 结构体的定义如下：

```c
typedef struct {
    ngx_file_t            file;     /* 文件的属性 */
    ngx_buf_t            *buffer;   /* 文件的内容 */
    ngx_uint_t            line;     /* 文件的行数 */
} ngx_conf_file_t;
```

#### 配置上下文 ctx

       Nginx 的配置文件是分块配置的，常见的有http 块、server 块、location 块以及upsteam 块和 mail 块等。每一个这样的配置块代表一个作用域。高一级配置块的作用域包含了多个低一级配置块的作用域，也就是有作用域嵌套的现象。这样，配置文件中的许多指令都会同时包含在多个作用域内。比如，http 块中的指令都可能同时处于http 块、server 块和location 块等三层作用域内。

       在 Nginx 程序解析配置文件时，每一条指令都应该记录自己所属的作用域范围，而配置文件上下文ctx 变量就是用来存放当前指令所属的作用域的。在Nginx 配置文件的各种配置块中，http 块可以包含子配置块，这在存储结构上比较复杂。

#### 指令类型 type

       Nginx 程序中的不同的指令类型以宏的形式定义在不同的源码头文件中，指令类型是core 模块类型的定义在文件：[src/core/ngx_conf_file.h](http://lxr.nginx.org/source/src/core/ngx_conf_file.h)

```c
#define NGX_DIRECT_CONF            0x00010000  
#define NGX_MAIN_CONF              0x01000000  
#define NGX_ANY_CONF               0x0F000000 
```
       这些是 core 类型模块支持的指令类型。其中的 NGX_DIRECT_CONF类指令在 Nginx 程序进入配置解析函数之前已经初始化完成，所以在进入配置解析函数之后可以将它们直接解析并存储到实际的数据结构中，从配置文件的结构上来看，它们一般指的就是那些游离于配置块之外、处于配置文件全局块部分的指令。NGX_MAIN_CONF 类指令包括event、http、mail、upstream 等可以形成配置块的指令，它们没有自己的初始化函数。Nginx 程序在解析配置文件时如果遇到 NGX_MAIN_CONF 类指令，将转入对下一级指令的解析。
       以下是 event 类型模块支持的指令类型。

```c
#define NGX_EVENT_CONF            0x02000000 
```

       以下是 http 类型模块支持的指令类型，其定义在文件：[src/http/ngx_http_config.h](http://lxr.nginx.org/source/src/http/ngx_http_config.h)

```c
#define NGX_HTTP_MAIN_CONF          0x02000000  
#define NGX_HTTP_SRV_CONF           0x04000000  
#define NGX_HTTP_LOC_CONF           0x08000000  
#define NGX_HTTP_UPS_CONF           0x10000000  
#define NGX_HTTP_SIF_CONF           0x20000000  
#define NGX_HTTP_LIF_CONF           0x40000000  
#define NGX_HTTP_LMT_CONF           0x80000000  
```

### 通用模块配置解析

       配置解析模块在 [src/core/ngx_conf_file.c](http://lxr.nginx.org/source/src/core/ngx_conf_file.c) 中实现。模块提供的接口函数主要是ngx_conf_parse。另外，模块提供另一个单独的接口ngx_conf_param，用来解析命令行传递的配置，这个接口也是对ngx_conf_parse 的包装。首先看下配置解析函数 ngx_conf_parse，其定义如下：

```c
/*
 * 函数功能：配置文件解析；
 * 支持三种不同的解析类型：
 * 1、解析配置文件；
 * 2、解析block块设置；
 * 3、解析命令行配置；
 */
char *
ngx_conf_parse(ngx_conf_t *cf, ngx_str_t *filename)
{
    char             *rv;
    ngx_fd_t          fd;
    ngx_int_t         rc;
    ngx_buf_t         buf;
    ngx_conf_file_t  *prev, conf_file;
    enum {
        parse_file = 0,
        parse_block,
        parse_param
    } type;

#if (NGX_SUPPRESS_WARN)
    fd = NGX_INVALID_FILE;
    prev = NULL;
#endif

    if (filename) {/* 若解析的是配置文件 */

        /* open configuration file */

        /* 打开配置文件 */
        fd = ngx_open_file(filename->data, NGX_FILE_RDONLY, NGX_FILE_OPEN, 0);
        if (fd == NGX_INVALID_FILE) {
            ngx_conf_log_error(NGX_LOG_EMERG, cf, ngx_errno,
                               ngx_open_file_n " \"%s\" failed",
                               filename->data);
            return NGX_CONF_ERROR;
        }

        prev = cf->conf_file;

        cf->conf_file = &amp;conf_file;

        if (ngx_fd_info(fd, &amp;cf->conf_file->file.info) == NGX_FILE_ERROR) {
            ngx_log_error(NGX_LOG_EMERG, cf->log, ngx_errno,
                          ngx_fd_info_n " \"%s\" failed", filename->data);
        }

        cf->conf_file->buffer = &amp;buf;

        buf.start = ngx_alloc(NGX_CONF_BUFFER, cf->log);
        if (buf.start == NULL) {
            goto failed;
        }

        buf.pos = buf.start;
        buf.last = buf.start;
        buf.end = buf.last + NGX_CONF_BUFFER;
        buf.temporary = 1;

        /* 复制文件属性及文件内容 */
        cf->conf_file->file.fd = fd;
        cf->conf_file->file.name.len = filename->len;
        cf->conf_file->file.name.data = filename->data;
        cf->conf_file->file.offset = 0;
        cf->conf_file->file.log = cf->log;
        cf->conf_file->line = 1;

        type = parse_file;  /* 解析的类型是配置文件 */

    } else if (cf->conf_file->file.fd != NGX_INVALID_FILE) {

        type = parse_block; /* 解析的类型是block块 */

    } else {
        type = parse_param; /* 解析的类型是命令行配置 */
    }

    for ( ;; ) {
        /* 语法分析函数 */
        rc = ngx_conf_read_token(cf);

        /*
         * ngx_conf_read_token() may return
         *
         *    NGX_ERROR             there is error
         *    NGX_OK                the token terminated by ";" was found
         *    NGX_CONF_BLOCK_START  the token terminated by "{" was found
         *    NGX_CONF_BLOCK_DONE   the "}" was found
         *    NGX_CONF_FILE_DONE    the configuration file is done
         */

        if (rc == NGX_ERROR) {
            goto done;
        }

        /* 解析block块设置 */
        if (rc == NGX_CONF_BLOCK_DONE) {

            if (type != parse_block) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "unexpected \"}\"");
                goto failed;
            }

            goto done;
        }

        /* 解析配置文件 */
        if (rc == NGX_CONF_FILE_DONE) {

            if (type == parse_block) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                                   "unexpected end of file, expecting \"}\"");
                goto failed;
            }

            goto done;
        }

        if (rc == NGX_CONF_BLOCK_START) {

            if (type == parse_param) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                                   "block directives are not supported "
                                   "in -g option");
                goto failed;
            }
        }

        /* rc == NGX_OK || rc == NGX_CONF_BLOCK_START */

        /* 自定义指令处理函数 */
        if (cf->handler) {

            /*
             * the custom handler, i.e., that is used in the http's
             * "types { ... }" directive
             */

            if (rc == NGX_CONF_BLOCK_START) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "unexpected \"{\"");
                goto failed;
            }

            /* 命令行配置处理函数 */
            rv = (*cf->handler)(cf, NULL, cf->handler_conf);
            if (rv == NGX_CONF_OK) {
                continue;
            }

            if (rv == NGX_CONF_ERROR) {
                goto failed;
            }

            ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, rv);

            goto failed;
        }

        /* 若自定义指令处理函数handler为NULL，则调用Nginx内建的指令解析机制 */
        rc = ngx_conf_handler(cf, rc);

        if (rc == NGX_ERROR) {
            goto failed;
        }
    }

failed:

    rc = NGX_ERROR;

done:

    if (filename) {/* 若是配置文件 */
        if (cf->conf_file->buffer->start) {
            ngx_free(cf->conf_file->buffer->start);
        }

        if (ngx_close_file(fd) == NGX_FILE_ERROR) {
            ngx_log_error(NGX_LOG_ALERT, cf->log, ngx_errno,
                          ngx_close_file_n " %s failed",
                          filename->data);
            return NGX_CONF_ERROR;
        }

        cf->conf_file = prev;
    }

    if (rc == NGX_ERROR) {
        return NGX_CONF_ERROR;
    }

    return NGX_CONF_OK;
}

```
       从配置解析函数的源码可以看出，该函数分为两个阶段：**语法分析**和 **指令解析**。语法分析由 ngx_conf_read_token()函数完成。指令解析有两种方式：一种是Nginx 内建的指令解析机制；另一种是自定义的指令解析机制。自定义指令解析源码如下所示：
```c
        /* 自定义指令处理函数 */
        if (cf->handler) {

            /*
             * the custom handler, i.e., that is used in the http's
             * "types { ... }" directive
             */

            if (rc == NGX_CONF_BLOCK_START) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "unexpected \"{\"");
                goto failed;
            }

            /* 命令行配置处理函数 */
            rv = (*cf->handler)(cf, NULL, cf->handler_conf);
            if (rv == NGX_CONF_OK) {
                continue;
            }

            if (rv == NGX_CONF_ERROR) {
                goto failed;
            }

            ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, rv);

            goto failed;
        }

```
       而Nginx 内置解析机制有函数ngx_conf_handler() 实现。其定义如下：
```c
/* Nginx内建的指令解析机制 */
static ngx_int_t
ngx_conf_handler(ngx_conf_t *cf, ngx_int_t last)
{
    char           *rv;
    void           *conf, **confp;
    ngx_uint_t      i, found;
    ngx_str_t      *name;
    ngx_command_t  *cmd;

    name = cf->args->elts;

    found = 0;

    for (i = 0; ngx_modules[i]; i++) {

        cmd = ngx_modules[i]->commands;
        if (cmd == NULL) {
            continue;
        }

        for ( /* void */ ; cmd->name.len; cmd++) {

            if (name->len != cmd->name.len) {
                continue;
            }

            if (ngx_strcmp(name->data, cmd->name.data) != 0) {
                continue;
            }

            found = 1;

            /*
             * 只处理模块类型为NGX_CONF_MODULE 或是当前正在处理的模块类型；
             */
            if (ngx_modules[i]->type != NGX_CONF_MODULE
                &amp;&amp; ngx_modules[i]->type != cf->module_type)
            {
                continue;
            }

            /* is the directive's location right ? */

            if (!(cmd->type &amp; cf->cmd_type)) {
                continue;
            }

            /* 非block块指令必须以";"分号结尾，否则出错返回 */
            if (!(cmd->type &amp; NGX_CONF_BLOCK) &amp;&amp; last != NGX_OK) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                                  "directive \"%s\" is not terminated by \";\"",
                                  name->data);
                return NGX_ERROR;
            }

            /* block块指令必须后接"{"大括号，否则出粗返回 */
            if ((cmd->type &amp; NGX_CONF_BLOCK) &amp;&amp; last != NGX_CONF_BLOCK_START) {
                ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                                   "directive \"%s\" has no opening \"{\"",
                                   name->data);
                return NGX_ERROR;
            }

            /* is the directive's argument count right ? */

            /* 验证指令参数个数是否正确 */
            if (!(cmd->type &amp; NGX_CONF_ANY)) {

                /* 指令携带的参数只能是 1 个，且其参数值只能是 on 或 off */
                if (cmd->type &amp; NGX_CONF_FLAG) {

                    if (cf->args->nelts != 2) {
                        goto invalid;
                    }

                } else if (cmd->type &amp; NGX_CONF_1MORE) {/* 指令携带的参数必须超过 1 个 */

                    if (cf->args->nelts < 2) {
                        goto invalid;
                    }

                } else if (cmd->type &amp; NGX_CONF_2MORE) {/* 指令携带的参数必须超过 2 个 */

                    if (cf->args->nelts < 3) {
                        goto invalid;
                    }

                } else if (cf->args->nelts > NGX_CONF_MAX_ARGS) {

                    goto invalid;

                } else if (!(cmd->type &amp; argument_number[cf->args->nelts - 1]))
                {
                    goto invalid;
                }
            }

            /* set up the directive's configuration context */

            conf = NULL;

            if (cmd->type &amp; NGX_DIRECT_CONF) {/* 在core模块使用 */
                conf = ((void **) cf->ctx)[ngx_modules[i]->index];

            } else if (cmd->type &amp; NGX_MAIN_CONF) {/* 指令配置项出现在全局配置中，不属于任何{}配置块 */
                conf = &amp;(((void **) cf->ctx)[ngx_modules[i]->index]);

            } else if (cf->ctx) {/* 除了core模块，其他模块都是用该项 */
                confp = *(void **) ((char *) cf->ctx + cmd->conf);

                if (confp) {
                    conf = confp[ngx_modules[i]->ctx_index];
                }
            }

            /* 执行指令解析回调函数 */
            rv = cmd->set(cf, cmd, conf);

            if (rv == NGX_CONF_OK) {
                return NGX_OK;
            }

            if (rv == NGX_CONF_ERROR) {
                return NGX_ERROR;
            }

            ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                               "\"%s\" directive %s", name->data, rv);

            return NGX_ERROR;
        }
    }

    if (found) {
        ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                           "\"%s\" directive is not allowed here", name->data);

        return NGX_ERROR;
    }

    ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                       "unknown directive \"%s\"", name->data);

    return NGX_ERROR;

invalid:

    ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                       "invalid number of arguments in \"%s\" directive",
                       name->data);

    return NGX_ERROR;
}

```

### HTTP 模块配置解析

　　这里主要是结构体 *ngx_command_t* ，我们在文章 《[Nginx 模块开发](http://blog.csdn.net/chenhanzhun/article/details/42528951)》 对该结构体作了介绍，其定义如下：

```c
struct ngx_command_s {  
    /* 配置项名称 */  
    ngx_str_t             name;  
    /* 配置项类型，type将指定配置项可以出现的位置以及携带参数的个数 */  
    ngx_uint_t            type;  
    /* 处理配置项的参数 */  
    char               *(*set)(ngx_conf_t *cf, ngx_command_t *cmd, void *conf);  
    /* 在配置文件中的偏移量，conf与offset配合使用 */  
    ngx_uint_t            conf;  
    ngx_uint_t            offset;  
    /* 配置项读取后的处理方法，必须指向ngx_conf_post_t 结构 */  
    void                 *post;  
}; 

```

       若在上面的通用配置解析中，定义了如下的 http 配置项结构，则回调用http 配置项，并对该http 配置项进行解析。此时，解析的是http block 块设置。

```c
static ngx_command_t  ngx_http_commands[] = {

    { ngx_string("http"),
      NGX_MAIN_CONF|NGX_CONF_BLOCK|NGX_CONF_NOARGS,
      ngx_http_block,
      0,
      0,
      NULL },

      ngx_null_command
};

```

       http 是作为一个 core 模块被 nginx 通用解析过程解析的，其核心就是http{} 块指令回调，它完成了http 解析的整个功能，从初始化到计算配置结果。http{} 块指令的流程是：

- 创建并初始化上下文结构；
- 调用通用模块配置解析流程解析；
- 根据解析结果进行配置项合并处理；

### 创建并初始化上下文结构

　　当 Nginx 检查到 http{…} 配置项时，HTTP 配置模型就会启动，则会建立一个*ngx_http_conf_ctx_t* 结构，该结构定义在文件中：[src/http/ngx_http_config.h](http://lxr.nginx.org/source/src/core/ngx_conf_file.h)

```c
typedef struct{
　　/*  指针数组，数组中的每个元素指向所有 HTTP 模块 create_main_conf 方法产生的结构体 */
   void **main_conf;
   /*  指针数组，数组中的每个元素指向所有 HTTP 模块 create_srv_conf 方法产生的结构体 */
   void **srv_conf;
   /*  指针数组，数组中的每个元素指向所有 HTTP 模块 create_loc_conf 方法产生的结构体 */
   void **loc_conf;
}ngx_http_conf_ctx_t;

```

　　此时，HTTP 框架为所有 HTTP 模块建立 3 个数组，分别存放所有 HTTP 模块的*create_main_conf*、*create_srv_conf* 、*create_loc_conf* 方法返回的地址指针。*ngx_http_conf_ctx_t* 结构的三个成员分别指向这 3 个数组。例如下面的例子是设置 *create_main_conf*、*create_srv_conf* 、*create_loc_conf*  返回的地址。

```c
ngx_http_conf_ctx *ctx;
/* HTTP 框架生成 1 个 ngx_http_conf_ctx_t 结构变量 */
ctx = ngx_pcalloc(cf->pool, sizeof(ngx_http_conf_ctx_t));

*(ngx_http_conf_ctx_t **) conf = ctx;

...
/* 分别生成 3 个数组存储所有的 HTTP 模块的 create_main_conf、create_srv_conf、create_loc_conf 方法返回的地址 */
ctx->main_conf = ngx_pcalloc(cf->pool,
                             sizeof(void *) * ngx_http_max_module);

ctx->srv_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);

ctx->loc_conf = ngx_pcalloc(cf->pool, sizeof(void *) * ngx_http_max_module);

/* 遍历所有 HTTP 模块 */
for (m = 0; ngx_modules[m]; m++) {
    if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
        continue;
    }

    module = ngx_modules[m]->ctx;
    mi = ngx_modules[m]->ctx_index;

    /* 若实现了create_main_conf 方法，则调用该方法，并把返回的地址存储到 main_conf 中 */
    if (module->create_main_conf) {
        ctx->main_conf[mi] = module->create_main_conf(cf);
    }
    /* 若实现了create_srv_conf 方法，则调用该方法，并把返回的地址存储到 srv_conf 中 */
    if (module->create_srv_conf) {
        ctx->srv_conf[mi] = module->create_srv_conf(cf);
    }
    /* 若实现了create_loc_conf 方法，则调用该方法，并把返回的地址存储到 loc_conf 中 */
    if (module->create_loc_conf) {
        ctx->loc_conf[mi] = module->create_loc_conf(cf);
    }
}

pcf = *cf;
cf->ctx = ctx;

for (m = 0; ngx_modules[m]; m++) {
    if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
        continue;
    }

    module = ngx_modules[m]->ctx;

    if (module->preconfiguration) {
        if (module->preconfiguration(cf) != NGX_OK) {
            return NGX_CONF_ERROR;
        }
    }
}

```

### 调用通用模块配置解析流程解析

       从源码 [src/http/ngx_http.c](http://lxr.nginx.org/source/src/http/ngx_http.c) 中可以看到，http 块的配置解析是调用通用模块的配置解析函数，其实现如下：

```c
    /* 调用通用模块配置解析 */
    /* parse inside the http{} block */

    cf->module_type = NGX_HTTP_MODULE;
    cf->cmd_type = NGX_HTTP_MAIN_CONF;
    rv = ngx_conf_parse(cf, NULL);

    if (rv != NGX_CONF_OK) {
        goto failed;
    }

```

### 根据解析结果进行配置项合并处理

```c
    /* 根据解析结构进行合并处理 */
    /*
     * init http{} main_conf's, merge the server{}s' srv_conf's
     * and its location{}s' loc_conf's
     */

    cmcf = ctx->main_conf[ngx_http_core_module.ctx_index];
    cscfp = cmcf->servers.elts;

    for (m = 0; ngx_modules[m]; m++) {
        if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
            continue;
        }

        module = ngx_modules[m]->ctx;
        mi = ngx_modules[m]->ctx_index;

        /* init http{} main_conf's */

        if (module->init_main_conf) {
            rv = module->init_main_conf(cf, ctx->main_conf[mi]);
            if (rv != NGX_CONF_OK) {
                goto failed;
            }
        }

        rv = ngx_http_merge_servers(cf, cmcf, module, mi);
        if (rv != NGX_CONF_OK) {
            goto failed;
        }
    }

    /* create location trees */

    for (s = 0; s < cmcf->servers.nelts; s++) {

        clcf = cscfp[s]->ctx->loc_conf[ngx_http_core_module.ctx_index];

        if (ngx_http_init_locations(cf, cscfp[s], clcf) != NGX_OK) {
            return NGX_CONF_ERROR;
        }

        if (ngx_http_init_static_location_trees(cf, clcf) != NGX_OK) {
            return NGX_CONF_ERROR;
        }
    }

    if (ngx_http_init_phases(cf, cmcf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }

    if (ngx_http_init_headers_in_hash(cf, cmcf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }

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

    if (ngx_http_variables_init_vars(cf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }

    /*
     * http{}'s cf->ctx was needed while the configuration merging
     * and in postconfiguration process
     */

    *cf = pcf;

    if (ngx_http_init_phase_handlers(cf, cmcf) != NGX_OK) {
        return NGX_CONF_ERROR;
    }

    /* optimize the lists of ports, addresses and server names */

    if (ngx_http_optimize_servers(cf, cmcf, cmcf->ports) != NGX_OK) {
        return NGX_CONF_ERROR;
    }

    return NGX_CONF_OK;

failed:

    *cf = pcf;

    return rv;

```

### HTTP 配置解析流程

       从上面的分析中可以总结出 HTTP 配置解析的流程如下：

- Nginx 进程进入主循环，在主循环中调用配置解析器解析配置文件*nginx.conf*;
- 在配置文件中遇到 http{} 块配置，则 HTTP 框架开始初始化并启动，其由函数 ngx_http_block() 实现；
- HTTP 框架初始化所有 HTTP 模块的序列号，并创建 3 个类型为 *ngx_http_conf_ctx_t *结构的数组用于存储所有HTTP 模块的*create_main_conf*、*create_srv_conf*、*create_loc_conf*方法返回的指针地址；
- 调用每个 HTTP 模块的 preconfiguration 方法；
- HTTP 框架调用函数 ngx_conf_parse() 开始循环解析配置文件 *nginx.conf *中的http{}块里面的所有配置项；
- HTTP 框架处理完毕 http{} 配置项，根据解析配置项的结果，必要时进行配置项合并处理；
- 继续处理其他 http{} 块之外的配置项，直到配置文件解析器处理完所有配置项后通知Nginx 主循环配置项解析完毕。此时，Nginx 才会启动Web 服务器；

### 合并配置项

       HTTP 框架解析完毕 http{} 块配置项时，会根据解析的结果进行合并配置项操作，即合并 http{}、server{}、location{} 不同块下各HTTP 模块生成的存放配置项的结构体。其合并过程如下所示：

- 若 HTTP 模块实现了 *merge_srv_conf* 方法，则将 http{} 块下*create_srv_conf* 生成的结构体与遍历每一个 server{}配置块下的结构体进行*merge_srv_conf* 操作；
- 若 HTTP 模块实现了 *merge_loc_conf* 方法，则将 http{} 块下*create_loc_conf* 生成的结构体与嵌套每一个server{} 配置块下的结构体进行*merge_loc_conf* 操作；
- 若 HTTP 模块实现了 *merge_loc_conf* 方法，则将server{} 块下*create_loc_conf* 生成的结构体与嵌套每一个location{}配置块下的结构体进行*merge_loc_conf* 操作；
- 若 HTTP 模块实现了 *merge_loc_conf* 方法，则将location{} 块下*create_loc_conf* 生成的结构体与嵌套每一个location{}配置块下的结构体进行*merge_loc_conf* 操作；

       以下是合并配置项操作的源码实现：

```c
/* 合并配置项操作 */
static char *
ngx_http_merge_servers(ngx_conf_t *cf, ngx_http_core_main_conf_t *cmcf,
    ngx_http_module_t *module, ngx_uint_t ctx_index)
{
    char                        *rv;
    ngx_uint_t                   s;
    ngx_http_conf_ctx_t         *ctx, saved;
    ngx_http_core_loc_conf_t    *clcf;
    ngx_http_core_srv_conf_t   **cscfp;

    cscfp = cmcf->servers.elts;
    ctx = (ngx_http_conf_ctx_t *) cf->ctx;
    saved = *ctx;
    rv = NGX_CONF_OK;

    /* 遍历每一个server{}块 */
    for (s = 0; s < cmcf->servers.nelts; s++) {

        /* merge the server{}s' srv_conf's */

        ctx->srv_conf = cscfp[s]->ctx->srv_conf;

        /*
         * 若定义了merge_srv_conf 方法；
         * 则进行http{}块下create_srv_conf 生成的结构体与遍历server{}块配置项生成的结构体进行merge_srv_conf操作；
         */
        if (module->merge_srv_conf) {
            rv = module->merge_srv_conf(cf, saved.srv_conf[ctx_index],
                                        cscfp[s]->ctx->srv_conf[ctx_index]);
            if (rv != NGX_CONF_OK) {
                goto failed;
            }
        }

        /*
         * 若定义了merge_loc_conf 方法；
         * 则进行http{}块下create_loc_conf 生成的结构体与嵌套server{}块配置项生成的结构体进行merge_loc_conf操作；
         */
        if (module->merge_loc_conf) {

            /* merge the server{}'s loc_conf */

            ctx->loc_conf = cscfp[s]->ctx->loc_conf;

            rv = module->merge_loc_conf(cf, saved.loc_conf[ctx_index],
                                        cscfp[s]->ctx->loc_conf[ctx_index]);
            if (rv != NGX_CONF_OK) {
                goto failed;
            }

            /* merge the locations{}' loc_conf's */

            /*
             * 若定义了merge_loc_conf 方法；
             * 则进行server{}块下create_loc_conf 生成的结构体与嵌套location{}块配置项生成的结构体进行merge_loc_conf操作；
             */
            clcf = cscfp[s]->ctx->loc_conf[ngx_http_core_module.ctx_index];

            rv = ngx_http_merge_locations(cf, clcf->locations,
                                          cscfp[s]->ctx->loc_conf,
                                          module, ctx_index);
            if (rv != NGX_CONF_OK) {
                goto failed;
            }
        }
    }

failed:

    *ctx = saved;

    return rv;
}

static char *
ngx_http_merge_locations(ngx_conf_t *cf, ngx_queue_t *locations,
    void **loc_conf, ngx_http_module_t *module, ngx_uint_t ctx_index)
{
    char                       *rv;
    ngx_queue_t                *q;
    ngx_http_conf_ctx_t        *ctx, saved;
    ngx_http_core_loc_conf_t   *clcf;
    ngx_http_location_queue_t  *lq;

    if (locations == NULL) {
        return NGX_CONF_OK;
    }

    ctx = (ngx_http_conf_ctx_t *) cf->ctx;
    saved = *ctx;

    /*
     * 若定义了merge_loc_conf 方法；
     * 则进行location{}块下create_loc_conf 生成的结构体与嵌套location{}块配置项生成的结构体进行merge_loc_conf操作；
     */
    for (q = ngx_queue_head(locations);
         q != ngx_queue_sentinel(locations);
         q = ngx_queue_next(q))
    {
        lq = (ngx_http_location_queue_t *) q;

        clcf = lq->exact ? lq->exact : lq->inclusive;
        ctx->loc_conf = clcf->loc_conf;

        rv = module->merge_loc_conf(cf, loc_conf[ctx_index],
                                    clcf->loc_conf[ctx_index]);
        if (rv != NGX_CONF_OK) {
            return rv;
        }

        /*
         * 递归调用该函数；
         * 因为location{}继续内嵌location{}
         */
        rv = ngx_http_merge_locations(cf, clcf->locations, clcf->loc_conf,
                                      module, ctx_index);
        if (rv != NGX_CONF_OK) {
            return rv;
        }
    }

    *ctx = saved;

    return NGX_CONF_OK;
}

```

### 处理自定义的配置

       在文章中 《[Nginx 模块开发](http://blog.csdn.net/chenhanzhun/article/details/42528951)》，我们给出了“Hello World” 的开发例子，在这个开发例子中，我们定义了自己的配置项，配置项名称的结构体定义如下：

```c
typedef struct  
{  
        ngx_str_t hello_string;  
        ngx_int_t hello_counter;  
}ngx_http_hello_loc_conf_t;  

```

       为了处理我们定义的配置项结构，因此，我们把 *ngx_command_t* 结构体定义如下：

```c
static ngx_command_t ngx_http_hello_commands[] = {  
   {  
                ngx_string("hello_string"),  
                NGX_HTTP_LOC_CONF|NGX_CONF_NOARGS|NGX_CONF_TAKE1,  
                ngx_http_hello_string,  
                NGX_HTTP_LOC_CONF_OFFSET,  
                offsetof(ngx_http_hello_loc_conf_t, hello_string),  
                NULL },  
  
        {  
                ngx_string("hello_counter"),  
                NGX_HTTP_LOC_CONF|NGX_CONF_FLAG,  
                ngx_http_hello_counter,  
                NGX_HTTP_LOC_CONF_OFFSET,  
                offsetof(ngx_http_hello_loc_conf_t, hello_counter),  
                NULL },  
  
        ngx_null_command  
};  

```

       处理方法 *ngx_http_hello_string* 和*ngx_http_hello_counter* 定义如下：

```c
static char *  
ngx_http_hello_string(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)  
{  
  
        ngx_http_hello_loc_conf_t* local_conf;  
  
  
        local_conf = conf;  
        char* rv = ngx_conf_set_str_slot(cf, cmd, conf);  
  
        ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "hello_string:%s", local_conf->hello_string.data);  
  
        return rv;  
}  
  
  
static char *ngx_http_hello_counter(ngx_conf_t *cf, ngx_command_t *cmd,  
        void *conf)  
{  
        ngx_http_hello_loc_conf_t* local_conf;  
  
        local_conf = conf;  
  
        char* rv = NULL;  
  
        rv = ngx_conf_set_flag_slot(cf, cmd, conf);  
  
  
        ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "hello_counter:%d", local_conf->hello_counter);  
        return rv;  
}  

```

### error 日志

       Nginx 日志模块为其他模块提供了基本的日志记录功能，日志模块定义如下：[src/​core/​ngx_log.c](http://lxr.nginx.org/source/src/core/ngx_log.c)

```c
static ngx_command_t  ngx_errlog_commands[] = {

    {ngx_string("error_log"),
     NGX_MAIN_CONF|NGX_CONF_1MORE,
     ngx_error_log,
     0,
     0,
     NULL},

    ngx_null_command
};

static ngx_core_module_t  ngx_errlog_module_ctx = {
    ngx_string("errlog"),
    NULL,
    NULL
};

ngx_module_t  ngx_errlog_module = {
    NGX_MODULE_V1,
    &amp;ngx_errlog_module_ctx,                /* module context */
    ngx_errlog_commands,                   /* module directives */
    NGX_CORE_MODULE,                       /* module type */
    NULL,                                  /* init master */
    NULL,                                  /* init module */
    NULL,                                  /* init process */
    NULL,                                  /* init thread */
    NULL,                                  /* exit thread */
    NULL,                                  /* exit process */
    NULL,                                  /* exit master */
    NGX_MODULE_V1_PADDING
};

```

       Nginx 日志模块对于支持可变参数提供了三个接口，这三个接口定义在文件：[src/​core/​ngx_log.h](http://lxr.nginx.org/source/src/core/ngx_log.h)

```c
#define ngx_log_error(level, log, ...)                                        \
    if ((log)->log_level >= level) ngx_log_error_core(level, log, __VA_ARGS__)

void ngx_log_error_core(ngx_uint_t level, ngx_log_t *log, ngx_err_t err,
    const char *fmt, ...);

#define ngx_log_debug(level, log, ...)                                        \
    if ((log)->log_level &amp; level)                                             \
        ngx_log_error_core(NGX_LOG_DEBUG, log, __VA_ARGS__)

```
       Nginx 日志模块记录日志的核心功能是由ngx_log_error_core 方法实现，ngx_log_error 和ngx_log_debug 宏定义只是对其进行简单的封装，一般情况下日志调用只需要这两个宏定义。
       ngx_log_error 和 ngx_log_debug 宏定义都包括参数 level、log、err、fmt，下面分别对这些参数进行简单的介绍：

**level 参数**：对于 ngx_log_error 宏来说，level 表示当前日志的级别，其取值如下所示：

```c
/* ngx_log_error中level参数的取值；下面 9 个日志的级别依次从高到低 */
#define NGX_LOG_STDERR            0     /* 最高级别日志，将日志输出到标准错误设备 */
#define NGX_LOG_EMERG             1
#define NGX_LOG_ALERT             2
#define NGX_LOG_CRIT              3
#define NGX_LOG_ERR               4
#define NGX_LOG_WARN              5
#define NGX_LOG_NOTICE            6
#define NGX_LOG_INFO              7
#define NGX_LOG_DEBUG             8     /* 最低级别日志，属于调试级别 */

```
       使用 ngx_log_error 宏记录日志时，若传入的level 级别小于或等于log 参数中的日志级别，就会输出日志内容，否则忽略该日志。
       在使用 ngx_log_debug 宏时，参数level 不同于ngx_log_error 宏的level 参数，它表达的不是日志级别，而是日志类型。ngx_log_debug 宏记录日志时必须是NGX_LOG_DEBUG 调试级别，这里的level 取值如下：

```c
/* ngx_log_debug中level参数的取值 */
#define NGX_LOG_DEBUG_CORE        0x010 /* nginx核心模块的调试日志 */
#define NGX_LOG_DEBUG_ALLOC       0x020 /* nginx在分配内存时使用的调试日志 */
#define NGX_LOG_DEBUG_MUTEX       0x040 /* nginx在使用进程锁时使用的调试日志 */
#define NGX_LOG_DEBUG_EVENT       0x080 /* nginx event模块的调试日志 */
#define NGX_LOG_DEBUG_HTTP        0x100 /* nginx http模块的调试日志 */
#define NGX_LOG_DEBUG_MAIL        0x200 /* nginx mail模块的调试日志 */
#define NGX_LOG_DEBUG_MYSQL       0x400 /* 与MySQL相关的nginx模块所使用的调试日志 */

```
       当 HTTP 模块调用ngx_log_debug 宏记录日志时，传入的level 参数是NGX_LOG_DEBUG_HTTP，此时，若log 参数不属于HTTP 模块，若使用event 事件模块的log，则不会输出任何日志。

**log 参数**：log 参数的结构定义如下：[src/core/ngx_log.h](http://lxr.nginx.org/source/src/core/ngx_log.h)；从其结构中可以知道，若只想把相应的信息记录到日志文件中，则不需要关系参数 log 的构造。

```c
/* ngx_log_t 结构的定义 */
struct ngx_log_s {
    /* 日志级别或日志类型 */
    ngx_uint_t           log_level;
    /* 日志文件 */
    ngx_open_file_t     *file;

    /* 连接数，不为0时会输出到日志文件中 */
    ngx_atomic_uint_t    connection;

    /* 记录日志时的回调方法，不是DEBUG调试级别才会被调用 */
    ngx_log_handler_pt   handler;
    /* 模块的data */
    void                *data;

    /*
     * we declare "action" as "char *" because the actions are usually
     * the static strings and in the "u_char *" case we have to override
     * their types all the time
     */

    char                *action;

    /* 指向日志链表的下一个日志 */
    ngx_log_t           *next;
};

```

**err 参数**：err 参数是错误编码，一般是执行系统调用失败后取得的errno 参数。当err 不为 0 时，Nginx 日志模块将会在正常日志输出这个错误编码以及其对应的字符串形成的错误信息。

**fmt 参数**：fmt 参数类似于C 语言中的printf 函数的输出格式。

  

参考资料：

《深入理解 Nginx 》

《[nginx 启动阶段](http://tengine.taobao.org/book/chapter_11.html)》

《Nginx高性能Web服务器详解》
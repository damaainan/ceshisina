### Nginx 启动过程

Nginx 的启动初始化由 main 函数完成，该函数是整个 Nginx 的入口，该函数完成 Nginx 启动初始化任务，也是所有功能模块的入口。Nginx 的初始化工作主要是一个类型为 ngx_cycle_t 类型的全局变量。main 函数定义在文件：[src/​core/​nginx.c][0]

Nginx 启动过程如下。

* 调用 ngx_get_options() 解析命令参数；
* 显示版本号与帮助信息；
* 调用 ngx_time_init() 初始化并更新时间；
* 调用 ngx_log_init() 初始化日志；
* 创建全局变量 init_cycle 的内存池 pool；
* 调用 ngx_save_argv() 保存命令行参数至全局变量ngx_os_argv、ngx_argc、ngx_argv 中；
* 调用 ngx_process_options() 初始化 init_cycle 的 prefix, conf_prefix, conf_file, conf_param 等字段；
* 调用 ngx_os_init() 初始化系统相关变量；
* 调用 ngx_crc32_table_init() 初始化CRC表；
* 调用 ngx_add_inherited_sockets() 继承 sockets；
* 通过环境变量 NGINX 完成 socket 的继承，将其保存在全局变量 init_cycle 的 listening 数组中；
* 初始化每个模块 module 的index，并计算 ngx_max_module；
* 调用 ngx_init_cycle() 进行初始化全局变量 init_cycle，这个步骤非常重要；
* 调用 ngx_signal_process() 处理进程信号；
* 调用 ngx_init_signals() 注册相关信号；
* 若无继承 sockets，则调用 ngx_daemon() 创建守护进程，并设置其标志；
* 调用 ngx_create_pidfile() 创建进程 ID 记录文件；
* 进入进程处理：
* 单进程工作模式；
* 多进程工作模式，即 master-worker 多进程工作模式；

---

    int ngx_cdecl
    main(int argc, char *const *argv)
    {
        ngx_int_t         i;
        ngx_log_t        *log;
        ngx_cycle_t      *cycle, init_cycle;
        ngx_core_conf_t  *ccf;
    
        ngx_debug_init();
    
        if (ngx_strerror_init() != NGX_OK) {
            return 1;
        }
    
        /* 解析命令行参数 */
        if (ngx_get_options(argc, argv) != NGX_OK) {
            return 1;
        }
    
        /* 显示版本号与帮助信息 */
        if (ngx_show_version) {
            ngx_write_stderr("nginx version: " NGINX_VER NGX_LINEFEED);
    
            if (ngx_show_help) {
                ngx_write_stderr(
                    "Usage: nginx [-?hvVtq] [-s signal] [-c filename] "
                                 "[-p prefix] [-g directives]" NGX_LINEFEED
                                 NGX_LINEFEED
                    "Options:" NGX_LINEFEED
                    "  -?,-h         : this help" NGX_LINEFEED
                    "  -v            : show version and exit" NGX_LINEFEED
                    "  -V            : show version and configure options then exit"
                                       NGX_LINEFEED
                    "  -t            : test configuration and exit" NGX_LINEFEED
                    "  -q            : suppress non-error messages "
                                       "during configuration testing" NGX_LINEFEED
                    "  -s signal     : send signal to a master process: "
                                       "stop, quit, reopen, reload" NGX_LINEFEED
    #ifdef NGX_PREFIX
                    "  -p prefix     : set prefix path (default: "
                                       NGX_PREFIX ")" NGX_LINEFEED
    #else
                    "  -p prefix     : set prefix path (default: NONE)" NGX_LINEFEED
    #endif
                    "  -c filename   : set configuration file (default: "
                                       NGX_CONF_PATH ")" NGX_LINEFEED
                    "  -g directives : set global directives out of configuration "
                                       "file" NGX_LINEFEED NGX_LINEFEED
                    );
            }
    
            if (ngx_show_configure) {
                ngx_write_stderr(
    #ifdef NGX_COMPILER
                    "built by " NGX_COMPILER NGX_LINEFEED
    #endif
    #if (NGX_SSL)
    #ifdef SSL_CTRL_SET_TLSEXT_HOSTNAME
                    "TLS SNI support enabled" NGX_LINEFEED
    #else
                    "TLS SNI support disabled" NGX_LINEFEED
    #endif
    #endif
                    "configure arguments:" NGX_CONFIGURE NGX_LINEFEED);
            }
    
            if (!ngx_test_config) {
                return 0;
            }
        }
    
        /* TODO */ ngx_max_sockets = -1;
    
        /* 初始化并更新时间 */
        ngx_time_init();
    
    #if (NGX_PCRE)
        ngx_regex_init();
    #endif
    
        ngx_pid = ngx_getpid();
    
        /* 初始化日志信息 */
        log = ngx_log_init(ngx_prefix);
        if (log == NULL) {
            return 1;
        }
    
        /* STUB */
    #if (NGX_OPENSSL)
        ngx_ssl_init(log);
    #endif
    
        /*
         * init_cycle->log is required for signal handlers and
         * ngx_process_options()
         */
    
        /* 全局变量init_cycle清零，并创建改变量的内存池pool */
        ngx_memzero(&init_cycle, sizeof(ngx_cycle_t));
        init_cycle.log = log;
        ngx_cycle = &init_cycle;
    
        init_cycle.pool = ngx_create_pool(1024, log);
        if (init_cycle.pool == NULL) {
            return 1;
        }
    
        /* 保存命令行参数至全局变量ngx_os_argv、ngx_argc、ngx_argv */
        if (ngx_save_argv(&init_cycle, argc, argv) != NGX_OK) {
            return 1;
        }
    
        /* 初始化全局变量init_cycle中的成员：prefix、conf_prefix、conf_file、conf_param 等字段 */
        if (ngx_process_options(&init_cycle) != NGX_OK) {
            return 1;
        }
    
        /* 初始化系统相关变量，如：内存页面大小ngx_pagesize、最大连接数ngx_max_sockets等 */
        if (ngx_os_init(log) != NGX_OK) {
            return 1;
        }
    
        /*
         * ngx_crc32_table_init() requires ngx_cacheline_size set in ngx_os_init()
         */
    
        /* 初始化 CRC 表（循环冗余校验表） */
        if (ngx_crc32_table_init() != NGX_OK) {
            return 1;
        }
    
        /* 通过环境变量NGINX完成socket的继承，将其保存在全局变量init_cycle的listening数组中 */
        if (ngx_add_inherited_sockets(&init_cycle) != NGX_OK) {
            return 1;
        }
    
        /* 初始化每个模块module的index，并计算ngx_max_module */
        ngx_max_module = 0;
        for (i = 0; ngx_modules[i]; i++) {
            ngx_modules[i]->index = ngx_max_module++;
        }
    
        /* 初始化全局变量init_cycle ，这里很重要 */
        cycle = ngx_init_cycle(&init_cycle);
        if (cycle == NULL) {
            if (ngx_test_config) {
                ngx_log_stderr(0, "configuration file %s test failed",
                               init_cycle.conf_file.data);
            }
    
            return 1;
        }
    
        if (ngx_test_config) {
            if (!ngx_quiet_mode) {
                ngx_log_stderr(0, "configuration file %s test is successful",
                               cycle->conf_file.data);
            }
    
            return 0;
        }
    
        /* 信号处理 */
        if (ngx_signal) {
            return ngx_signal_process(cycle, ngx_signal);
        }
    
        ngx_os_status(cycle->log);
    
        ngx_cycle = cycle;
    
        ccf = (ngx_core_conf_t *) ngx_get_conf(cycle->conf_ctx, ngx_core_module);
    
        if (ccf->master && ngx_process == NGX_PROCESS_SINGLE) {
            ngx_process = NGX_PROCESS_MASTER;
        }
    
    #if !(NGX_WIN32)
    
        /* 初始化信号，注册相关信号 */
        if (ngx_init_signals(cycle->log) != NGX_OK) {
            return 1;
        }
    
        /* 若无socket继承，则创建守护进程，并设置守护进程标志 */
        if (!ngx_inherited && ccf->daemon) {
            if (ngx_daemon(cycle->log) != NGX_OK) {
                return 1;
            }
    
            ngx_daemonized = 1;
        }
    
        if (ngx_inherited) {
            ngx_daemonized = 1;
        }
    
    #endif
    
        /* 记录进程ID */
        if (ngx_create_pidfile(&ccf->pid, cycle->log) != NGX_OK) {
            return 1;
        }
    
        if (ngx_log_redirect_stderr(cycle) != NGX_OK) {
            return 1;
        }
    
        if (log->file->fd != ngx_stderr) {
            if (ngx_close_file(log->file->fd) == NGX_FILE_ERROR) {
                ngx_log_error(NGX_LOG_ALERT, cycle->log, ngx_errno,
                              ngx_close_file_n " built-in log failed");
            }
        }
    
        ngx_use_stderr = 0;
    
        /* 进入进程处理 */
        if (ngx_process == NGX_PROCESS_SINGLE) {
            /* 单进程工作模式 */
            ngx_single_process_cycle(cycle);
    
        } else {
            /* master-worker 多进程模式工作 */
            ngx_master_process_cycle(cycle);
        }
    
        return 0;
    }
    
    

### ngx_cycle_t 变量初始化

### ngx_cycle_t 结构体初始化

在初始化过程中，ngx_cycle_t 类型的全局变量最为重要，该类型结构定义如下：[src/​core/​ngx_cycle.h][1]

    /* ngx_cycle_t 全局变量数据结构 */
    struct ngx_cycle_s {
        /*
         * 保存所有模块配置项的结构体指针，该数组每个成员又是一个指针，
         * 这个指针又指向存储指针的数组
         */
        void                  **conf_ctx; /* 所有模块配置上下文的数组 */
        ngx_pool_t               *pool;     /* 内存池 */
    
        ngx_log_t                *log;      /* 日志 */
        ngx_log_t                 new_log;
    
        ngx_uint_t                log_use_stderr;  /* unsigned  log_use_stderr:1; */
    
        ngx_connection_t        **files;    /* 连接文件 */
        ngx_connection_t         *free_connections; /* 空闲连接 */
        ngx_uint_t                free_connection_n;/* 空闲连接的个数 */
    
        /* 可再利用的连接队列 */
        ngx_queue_t               reusable_connections_queue;
    
        ngx_array_t               listening;    /* 监听数组 */
        ngx_array_t               paths;        /* 路径数组 */
        ngx_list_t                open_files;   /* 已打开文件的链表 */
        ngx_list_t                shared_memory;/* 共享内存链表 */
    
        ngx_uint_t                connection_n; /* 已连接个数 */
        ngx_uint_t                files_n;      /* 已打开文件的个数 */
    
        ngx_connection_t         *connections;  /* 连接 */
        ngx_event_t              *read_events;  /* 读事件 */
        ngx_event_t              *write_events; /* 写事件 */
    
        /* old 的 ngx_cycle_t 对象，用于引用前一个 ngx_cycle_t 对象的成员 */
        ngx_cycle_t              *old_cycle;
    
        ngx_str_t                 conf_file;    /* nginx 配置文件 */
        ngx_str_t                 conf_param;   /* nginx 处理配置文件时需要特殊处理的，在命令行携带的参数 */
        ngx_str_t                 conf_prefix;  /* nginx 配置文件的路径 */
        ngx_str_t                 prefix;       /* nginx 安装路径 */
        ngx_str_t                 lock_file;    /* 加锁文件 */
        ngx_str_t                 hostname;     /* 主机名 */
    };
    
    

### ngx_init_cycle() 初始化函数

该结构的初始化是通过函数 ngx_init_cycle() 完成的，该函数定义如下：[src/​core/​ngx_cycle.c][2]

ngx_cycle_t 结构全局变量初始化过程如下：

* 更新时区与时间；
* 创建内存池；
* 分配 ngx_cycle_t 结构体内存，创建该结构的变量 cycle 并初始化；
* 遍历所有 core模块，并调用该模块的 create_conf() 函数；
* 配置文件解析；
* 遍历所有core模块，并调用core模块的init_conf()函数；
* 遍历 open_files 链表中的每一个文件并打开；
* 创建新的共享内存并初始化；
* 遍历 listening 数组并打开所有侦听；
* 提交新的 cycle 配置，并调用所有模块的init_module；
* 关闭或删除不被使用的在 old_cycle 中的资源：
* 释放多余的共享内存；
* 关闭多余的侦听 sockets；
* 关闭多余的打开文件；

```c
    ngx_cycle_t *
    ngx_init_cycle(ngx_cycle_t *old_cycle)
    {
        void                *rv;
        char               **senv, **env;
        ngx_uint_t           i, n;
        ngx_log_t           *log;
        ngx_time_t          *tp;
        ngx_conf_t           conf;
        ngx_pool_t          *pool;
        ngx_cycle_t         *cycle, **old;
        ngx_shm_zone_t      *shm_zone, *oshm_zone;
        ngx_list_part_t     *part, *opart;
        ngx_open_file_t     *file;
        ngx_listening_t     *ls, *nls;
        ngx_core_conf_t     *ccf, *old_ccf;
        ngx_core_module_t   *module;
        char                 hostname[NGX_MAXHOSTNAMELEN];
    
        /* 更新时区 */
        ngx_timezone_update();
    
        /* force localtime update with a new timezone */
    
        tp = ngx_timeofday();
        tp->sec = 0;
    
        /* 更新时间 */
        ngx_time_update();
    
        /* 创建内存池pool，并初始化 */
        log = old_cycle->log;
    
        pool = ngx_create_pool(NGX_CYCLE_POOL_SIZE, log);
        if (pool == NULL) {
            return NULL;
        }
        pool->log = log;
    
        /* 创建ngx_cycle_t 结构体变量 */
        cycle = ngx_pcalloc(pool, sizeof(ngx_cycle_t));
        if (cycle == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        /* 初始化ngx_cycle_t 结构体变量 cycle */
        cycle->pool = pool;
        cycle->log = log;
        cycle->old_cycle = old_cycle;
    
        cycle->conf_prefix.len = old_cycle->conf_prefix.len;
        cycle->conf_prefix.data = ngx_pstrdup(pool, &old_cycle->conf_prefix);
        if (cycle->conf_prefix.data == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        cycle->prefix.len = old_cycle->prefix.len;
        cycle->prefix.data = ngx_pstrdup(pool, &old_cycle->prefix);
        if (cycle->prefix.data == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        cycle->conf_file.len = old_cycle->conf_file.len;
        cycle->conf_file.data = ngx_pnalloc(pool, old_cycle->conf_file.len + 1);
        if (cycle->conf_file.data == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
        ngx_cpystrn(cycle->conf_file.data, old_cycle->conf_file.data,
                    old_cycle->conf_file.len + 1);
    
        cycle->conf_param.len = old_cycle->conf_param.len;
        cycle->conf_param.data = ngx_pstrdup(pool, &old_cycle->conf_param);
        if (cycle->conf_param.data == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        n = old_cycle->paths.nelts ? old_cycle->paths.nelts : 10;
    
        cycle->paths.elts = ngx_pcalloc(pool, n * sizeof(ngx_path_t *));
        if (cycle->paths.elts == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        cycle->paths.nelts = 0;
        cycle->paths.size = sizeof(ngx_path_t *);
        cycle->paths.nalloc = n;
        cycle->paths.pool = pool;
    
        if (old_cycle->open_files.part.nelts) {
            n = old_cycle->open_files.part.nelts;
            for (part = old_cycle->open_files.part.next; part; part = part->next) {
                n += part->nelts;
            }
    
        } else {
            n = 20;
        }
    
        if (ngx_list_init(&cycle->open_files, pool, n, sizeof(ngx_open_file_t))
            != NGX_OK)
        {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        if (old_cycle->shared_memory.part.nelts) {
            n = old_cycle->shared_memory.part.nelts;
            for (part = old_cycle->shared_memory.part.next; part; part = part->next)
            {
                n += part->nelts;
            }
    
        } else {
            n = 1;
        }
    
        if (ngx_list_init(&cycle->shared_memory, pool, n, sizeof(ngx_shm_zone_t))
            != NGX_OK)
        {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        n = old_cycle->listening.nelts ? old_cycle->listening.nelts : 10;
    
        cycle->listening.elts = ngx_pcalloc(pool, n * sizeof(ngx_listening_t));
        if (cycle->listening.elts == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        cycle->listening.nelts = 0;
        cycle->listening.size = sizeof(ngx_listening_t);
        cycle->listening.nalloc = n;
        cycle->listening.pool = pool;
    
        ngx_queue_init(&cycle->reusable_connections_queue);
    
        cycle->conf_ctx = ngx_pcalloc(pool, ngx_max_module * sizeof(void *));
        if (cycle->conf_ctx == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        if (gethostname(hostname, NGX_MAXHOSTNAMELEN) == -1) {
            ngx_log_error(NGX_LOG_EMERG, log, ngx_errno, "gethostname() failed");
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        /* on Linux gethostname() silently truncates name that does not fit */
    
        hostname[NGX_MAXHOSTNAMELEN - 1] = '\0';
        cycle->hostname.len = ngx_strlen(hostname);
    
        cycle->hostname.data = ngx_pnalloc(pool, cycle->hostname.len);
        if (cycle->hostname.data == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        ngx_strlow(cycle->hostname.data, (u_char *) hostname, cycle->hostname.len);
    
        /* 遍历所有core模块 */
        for (i = 0; ngx_modules[i]; i++) {
            if (ngx_modules[i]->type != NGX_CORE_MODULE) {
                continue;
            }
    
            module = ngx_modules[i]->ctx;
    
            /* 若有core模块实现了create_conf()，则就调用它，并返回地址 */
            if (module->create_conf) {
                rv = module->create_conf(cycle);
                if (rv == NULL) {
                    ngx_destroy_pool(pool);
                    return NULL;
                }
                cycle->conf_ctx[ngx_modules[i]->index] = rv;
            }
        }
    
        senv = environ;
    
        ngx_memzero(&conf, sizeof(ngx_conf_t));
        /* STUB: init array ? */
        conf.args = ngx_array_create(pool, 10, sizeof(ngx_str_t));
        if (conf.args == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        conf.temp_pool = ngx_create_pool(NGX_CYCLE_POOL_SIZE, log);
        if (conf.temp_pool == NULL) {
            ngx_destroy_pool(pool);
            return NULL;
        }
    
        conf.ctx = cycle->conf_ctx;
        conf.cycle = cycle;
        conf.pool = pool;
        conf.log = log;
        conf.module_type = NGX_CORE_MODULE;
        conf.cmd_type = NGX_MAIN_CONF;
    
    #if 0
        log->log_level = NGX_LOG_DEBUG_ALL;
    #endif
    
        /* 配置文件解析 */
        if (ngx_conf_param(&conf) != NGX_CONF_OK) {
            environ = senv;
            ngx_destroy_cycle_pools(&conf);
            return NULL;
        }
    
        if (ngx_conf_parse(&conf, &cycle->conf_file) != NGX_CONF_OK) {
            environ = senv;
            ngx_destroy_cycle_pools(&conf);
            return NULL;
        }
    
        if (ngx_test_config && !ngx_quiet_mode) {
            ngx_log_stderr(0, "the configuration file %s syntax is ok",
                           cycle->conf_file.data);
        }
    
        /* 遍历所有core模块，并调用core模块的init_conf()函数 */
        for (i = 0; ngx_modules[i]; i++) {
            if (ngx_modules[i]->type != NGX_CORE_MODULE) {
                continue;
            }
    
            module = ngx_modules[i]->ctx;
    
            if (module->init_conf) {
                if (module->init_conf(cycle, cycle->conf_ctx[ngx_modules[i]->index])
                    == NGX_CONF_ERROR)
                {
                    environ = senv;
                    ngx_destroy_cycle_pools(&conf);
                    return NULL;
                }
            }
        }
    
        if (ngx_process == NGX_PROCESS_SIGNALLER) {
            return cycle;
        }
    
        ccf = (ngx_core_conf_t *) ngx_get_conf(cycle->conf_ctx, ngx_core_module);
    
        if (ngx_test_config) {
    
            if (ngx_create_pidfile(&ccf->pid, log) != NGX_OK) {
                goto failed;
            }
    
        } else if (!ngx_is_init_cycle(old_cycle)) {
    
            /*
             * we do not create the pid file in the first ngx_init_cycle() call
             * because we need to write the demonized process pid
             */
    
            old_ccf = (ngx_core_conf_t *) ngx_get_conf(old_cycle->conf_ctx,
                                                       ngx_core_module);
            if (ccf->pid.len != old_ccf->pid.len
                || ngx_strcmp(ccf->pid.data, old_ccf->pid.data) != 0)
            {
                /* new pid file name */
    
                if (ngx_create_pidfile(&ccf->pid, log) != NGX_OK) {
                    goto failed;
                }
    
                ngx_delete_pidfile(old_cycle);
            }
        }
    
        if (ngx_test_lockfile(cycle->lock_file.data, log) != NGX_OK) {
            goto failed;
        }
    
        /* 初始化paths 数组 */
        if (ngx_create_paths(cycle, ccf->user) != NGX_OK) {
            goto failed;
        }
    
        if (ngx_log_open_default(cycle) != NGX_OK) {
            goto failed;
        }
    
        /* open the new files */
    
        part = &cycle->open_files.part;
        file = part->elts;
    
        /* 遍历open_file 链表中每一个文件，并打开该文件 */
        for (i = 0; /* void */ ; i++) {
    
            if (i >= part->nelts) {
                if (part->next == NULL) {
                    break;
                }
                part = part->next;
                file = part->elts;
                i = 0;
            }
    
            if (file[i].name.len == 0) {
                continue;
            }
    
            file[i].fd = ngx_open_file(file[i].name.data,
                                       NGX_FILE_APPEND,
                                       NGX_FILE_CREATE_OR_OPEN,
                                       NGX_FILE_DEFAULT_ACCESS);
    
            ngx_log_debug3(NGX_LOG_DEBUG_CORE, log, 0,
                           "log: %p %d \"%s\"",
                           &file[i], file[i].fd, file[i].name.data);
    
            if (file[i].fd == NGX_INVALID_FILE) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_errno,
                              ngx_open_file_n " \"%s\" failed",
                              file[i].name.data);
                goto failed;
            }
    
    #if !(NGX_WIN32)
            if (fcntl(file[i].fd, F_SETFD, FD_CLOEXEC) == -1) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_errno,
                              "fcntl(FD_CLOEXEC) \"%s\" failed",
                              file[i].name.data);
                goto failed;
            }
    #endif
        }
    
        cycle->log = &cycle->new_log;
        pool->log = &cycle->new_log;
    
        /* 创建共享内存并初始化 */
        /* create shared memory */
    
        part = &cycle->shared_memory.part;
        shm_zone = part->elts;
    
        for (i = 0; /* void */ ; i++) {
    
            if (i >= part->nelts) {
                if (part->next == NULL) {
                    break;
                }
                part = part->next;
                shm_zone = part->elts;
                i = 0;
            }
    
            if (shm_zone[i].shm.size == 0) {
                ngx_log_error(NGX_LOG_EMERG, log, 0,
                              "zero size shared memory zone \"%V\"",
                              &shm_zone[i].shm.name);
                goto failed;
            }
    
            shm_zone[i].shm.log = cycle->log;
    
            opart = &old_cycle->shared_memory.part;
            oshm_zone = opart->elts;
    
            /*
             * 新的共享内存与旧的共享内存链表进行比较，
             * 相同则保留，不同创建新的，旧的被释放
             */
            for (n = 0; /* void */ ; n++) {
    
                if (n >= opart->nelts) {
                    if (opart->next == NULL) {
                        break;
                    }
                    opart = opart->next;
                    oshm_zone = opart->elts;
                    n = 0;
                }
    
                if (shm_zone[i].shm.name.len != oshm_zone[n].shm.name.len) {
                    continue;
                }
    
                if (ngx_strncmp(shm_zone[i].shm.name.data,
                                oshm_zone[n].shm.name.data,
                                shm_zone[i].shm.name.len)
                    != 0)
                {
                    continue;
                }
    
                if (shm_zone[i].tag == oshm_zone[n].tag
                    && shm_zone[i].shm.size == oshm_zone[n].shm.size)
                {
                    shm_zone[i].shm.addr = oshm_zone[n].shm.addr;
    
                    if (shm_zone[i].init(&shm_zone[i], oshm_zone[n].data)
                        != NGX_OK)
                    {
                        goto failed;
                    }
    
                    goto shm_zone_found;
                }
    
                ngx_shm_free(&oshm_zone[n].shm);
    
                break;
            }
    
            if (ngx_shm_alloc(&shm_zone[i].shm) != NGX_OK) {
                goto failed;
            }
    
            if (ngx_init_zone_pool(cycle, &shm_zone[i]) != NGX_OK) {
                goto failed;
            }
    
            if (shm_zone[i].init(&shm_zone[i], NULL) != NGX_OK) {
                goto failed;
            }
    
        shm_zone_found:
    
            continue;
        }
    
        /* 遍历listening 数组，并打开所有监听 */
        /* handle the listening sockets */
    
        if (old_cycle->listening.nelts) {
            ls = old_cycle->listening.elts;
            for (i = 0; i < old_cycle->listening.nelts; i++) {
                ls[i].remain = 0;
            }
    
            nls = cycle->listening.elts;
            for (n = 0; n < cycle->listening.nelts; n++) {
    
                for (i = 0; i < old_cycle->listening.nelts; i++) {
                    if (ls[i].ignore) {
                        continue;
                    }
    
                    if (ngx_cmp_sockaddr(nls[n].sockaddr, nls[n].socklen,
                                         ls[i].sockaddr, ls[i].socklen, 1)
                        == NGX_OK)
                    {
                        nls[n].fd = ls[i].fd;
                        nls[n].previous = &ls[i];
                        ls[i].remain = 1;
    
                        if (ls[i].backlog != nls[n].backlog) {
                            nls[n].listen = 1;
                        }
    
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined SO_ACCEPTFILTER)
    
                        /*
                         * FreeBSD, except the most recent versions,
                         * could not remove accept filter
                         */
                        nls[n].deferred_accept = ls[i].deferred_accept;
    
                        if (ls[i].accept_filter && nls[n].accept_filter) {
                            if (ngx_strcmp(ls[i].accept_filter,
                                           nls[n].accept_filter)
                                != 0)
                            {
                                nls[n].delete_deferred = 1;
                                nls[n].add_deferred = 1;
                            }
    
                        } else if (ls[i].accept_filter) {
                            nls[n].delete_deferred = 1;
    
                        } else if (nls[n].accept_filter) {
                            nls[n].add_deferred = 1;
                        }
    #endif
    
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined TCP_DEFER_ACCEPT)
    
                        if (ls[i].deferred_accept && !nls[n].deferred_accept) {
                            nls[n].delete_deferred = 1;
    
                        } else if (ls[i].deferred_accept != nls[n].deferred_accept)
                        {
                            nls[n].add_deferred = 1;
                        }
    #endif
                        break;
                    }
                }
    
                if (nls[n].fd == (ngx_socket_t) -1) {
                    nls[n].open = 1;
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined SO_ACCEPTFILTER)
                    if (nls[n].accept_filter) {
                        nls[n].add_deferred = 1;
                    }
    #endif
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined TCP_DEFER_ACCEPT)
                    if (nls[n].deferred_accept) {
                        nls[n].add_deferred = 1;
                    }
    #endif
                }
            }
    
        } else {
            ls = cycle->listening.elts;
            for (i = 0; i < cycle->listening.nelts; i++) {
                ls[i].open = 1;
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined SO_ACCEPTFILTER)
                if (ls[i].accept_filter) {
                    ls[i].add_deferred = 1;
                }
    #endif
    #if (NGX_HAVE_DEFERRED_ACCEPT && defined TCP_DEFER_ACCEPT)
                if (ls[i].deferred_accept) {
                    ls[i].add_deferred = 1;
                }
    #endif
            }
        }
    
        /* 打开监听 */
        if (ngx_open_listening_sockets(cycle) != NGX_OK) {
            goto failed;
        }
    
        if (!ngx_test_config) {
            ngx_configure_listening_sockets(cycle);
        }
    
        /* 提交新的cycle配置，并调用所有模块的 init_module */
        /* commit the new cycle configuration */
    
        if (!ngx_use_stderr) {
            (void) ngx_log_redirect_stderr(cycle);
        }
    
        pool->log = cycle->log;
    
        for (i = 0; ngx_modules[i]; i++) {
            if (ngx_modules[i]->init_module) {
                if (ngx_modules[i]->init_module(cycle) != NGX_OK) {
                    /* fatal */
                    exit(1);
                }
            }
        }
    
        /* 关闭或删除不被使用的old_cycle 资源 */
        /* close and delete stuff that lefts from an old cycle */
    
        /* free the unnecessary shared memory */
    
        opart = &old_cycle->shared_memory.part;
        oshm_zone = opart->elts;
    
        for (i = 0; /* void */ ; i++) {
    
            if (i >= opart->nelts) {
                if (opart->next == NULL) {
                    goto old_shm_zone_done;
                }
                opart = opart->next;
                oshm_zone = opart->elts;
                i = 0;
            }
    
            part = &cycle->shared_memory.part;
            shm_zone = part->elts;
    
            for (n = 0; /* void */ ; n++) {
    
                if (n >= part->nelts) {
                    if (part->next == NULL) {
                        break;
                    }
                    part = part->next;
                    shm_zone = part->elts;
                    n = 0;
                }
    
                if (oshm_zone[i].shm.name.len == shm_zone[n].shm.name.len
                    && ngx_strncmp(oshm_zone[i].shm.name.data,
                                   shm_zone[n].shm.name.data,
                                   oshm_zone[i].shm.name.len)
                    == 0)
                {
                    goto live_shm_zone;
                }
            }
    
            ngx_shm_free(&oshm_zone[i].shm);
    
        live_shm_zone:
    
            continue;
        }
    
    old_shm_zone_done:
    
        /* close the unnecessary listening sockets */
    
        ls = old_cycle->listening.elts;
        for (i = 0; i < old_cycle->listening.nelts; i++) {
    
            if (ls[i].remain || ls[i].fd == (ngx_socket_t) -1) {
                continue;
            }
    
            if (ngx_close_socket(ls[i].fd) == -1) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_socket_errno,
                              ngx_close_socket_n " listening socket on %V failed",
                              &ls[i].addr_text);
            }
    
    #if (NGX_HAVE_UNIX_DOMAIN)
    
            if (ls[i].sockaddr->sa_family == AF_UNIX) {
                u_char  *name;
    
                name = ls[i].addr_text.data + sizeof("unix:") - 1;
    
                ngx_log_error(NGX_LOG_WARN, cycle->log, 0,
                              "deleting socket %s", name);
    
                if (ngx_delete_file(name) == NGX_FILE_ERROR) {
                    ngx_log_error(NGX_LOG_EMERG, cycle->log, ngx_socket_errno,
                                  ngx_delete_file_n " %s failed", name);
                }
            }
    
    #endif
        }
    
        /* close the unnecessary open files */
    
        part = &old_cycle->open_files.part;
        file = part->elts;
    
        for (i = 0; /* void */ ; i++) {
    
            if (i >= part->nelts) {
                if (part->next == NULL) {
                    break;
                }
                part = part->next;
                file = part->elts;
                i = 0;
            }
    
            if (file[i].fd == NGX_INVALID_FILE || file[i].fd == ngx_stderr) {
                continue;
            }
    
            if (ngx_close_file(file[i].fd) == NGX_FILE_ERROR) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_errno,
                              ngx_close_file_n " \"%s\" failed",
                              file[i].name.data);
            }
        }
    
        ngx_destroy_pool(conf.temp_pool);
    
        if (ngx_process == NGX_PROCESS_MASTER || ngx_is_init_cycle(old_cycle)) {
    
            /*
             * perl_destruct() frees environ, if it is not the same as it was at
             * perl_construct() time, therefore we save the previous cycle
             * environment before ngx_conf_parse() where it will be changed.
             */
    
            env = environ;
            environ = senv;
    
            ngx_destroy_pool(old_cycle->pool);
            cycle->old_cycle = NULL;
    
            environ = env;
    
            return cycle;
        }
    
        if (ngx_temp_pool == NULL) {
            ngx_temp_pool = ngx_create_pool(128, cycle->log);
            if (ngx_temp_pool == NULL) {
                ngx_log_error(NGX_LOG_EMERG, cycle->log, 0,
                              "could not create ngx_temp_pool");
                exit(1);
            }
    
            n = 10;
            ngx_old_cycles.elts = ngx_pcalloc(ngx_temp_pool,
                                              n * sizeof(ngx_cycle_t *));
            if (ngx_old_cycles.elts == NULL) {
                exit(1);
            }
            ngx_old_cycles.nelts = 0;
            ngx_old_cycles.size = sizeof(ngx_cycle_t *);
            ngx_old_cycles.nalloc = n;
            ngx_old_cycles.pool = ngx_temp_pool;
    
            ngx_cleaner_event.handler = ngx_clean_old_cycles;
            ngx_cleaner_event.log = cycle->log;
            ngx_cleaner_event.data = &dumb;
            dumb.fd = (ngx_socket_t) -1;
        }
    
        ngx_temp_pool->log = cycle->log;
    
        old = ngx_array_push(&ngx_old_cycles);
        if (old == NULL) {
            exit(1);
        }
        *old = old_cycle;
    
        if (!ngx_cleaner_event.timer_set) {
            ngx_add_timer(&ngx_cleaner_event, 30000);
            ngx_cleaner_event.timer_set = 1;
        }
    
        return cycle;
    
    failed:
    
        if (!ngx_is_init_cycle(old_cycle)) {
            old_ccf = (ngx_core_conf_t *) ngx_get_conf(old_cycle->conf_ctx,
                                                       ngx_core_module);
            if (old_ccf->environment) {
                environ = old_ccf->environment;
            }
        }
    
        /* rollback the new cycle configuration */
    
        part = &cycle->open_files.part;
        file = part->elts;
    
        for (i = 0; /* void */ ; i++) {
    
            if (i >= part->nelts) {
                if (part->next == NULL) {
                    break;
                }
                part = part->next;
                file = part->elts;
                i = 0;
            }
    
            if (file[i].fd == NGX_INVALID_FILE || file[i].fd == ngx_stderr) {
                continue;
            }
    
            if (ngx_close_file(file[i].fd) == NGX_FILE_ERROR) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_errno,
                              ngx_close_file_n " \"%s\" failed",
                              file[i].name.data);
            }
        }
    
        if (ngx_test_config) {
            ngx_destroy_cycle_pools(&conf);
            return NULL;
        }
    
        ls = cycle->listening.elts;
        for (i = 0; i < cycle->listening.nelts; i++) {
            if (ls[i].fd == (ngx_socket_t) -1 || !ls[i].open) {
                continue;
            }
    
            if (ngx_close_socket(ls[i].fd) == -1) {
                ngx_log_error(NGX_LOG_EMERG, log, ngx_socket_errno,
                              ngx_close_socket_n " %V failed",
                              &ls[i].addr_text);
            }
        }
    
        ngx_destroy_cycle_pools(&conf);
    
        return NULL;
    }
```
    

参考资料：  
《 [nginx源码分析—启动流程][3]》

《[Nginx启动初始化过程][4]》

[0]: http://lxr.nginx.org/source/src/core/nginx.c
[1]: http://lxr.nginx.org/source/src/core/ngx_cycle.h
[2]: http://lxr.nginx.org/source/src/core/ngx_cycle.c
[3]: http://blog.csdn.net/livelylittlefish/article/details/7243718
[4]: http://www.alidata.org/archives/1148
# Nginx 配置文件

### 概述

Nginx 是使用一个 master 进程来管理多个 worker 进程提供服务。master 负责管理 worker 进程，而 worker 进程则提供真正的客户服务，worker 进程的数量一般跟服务器上 CPU 的核心数相同，worker 之间通过一些进程间通信机制实现负载均衡等功能。Nginx 进程之间的关系可由下图表示：

![][0]

Nginx 服务启动时会读入配置文件，后续的行为则按照配置文件中的指令进行。Nginx 的配置文件是纯文本文件，默认安装 Nginx 后，其配置文件均在 /usr/local/nginx/conf/ 目录下。其中，nginx.conf 为主配置文件。配置文件中以 # 开始的行，或者是前面有若干空格或者 TAB 键，然后再跟 # 的行，都被认为是注释。这里只是了解主配置文件的结构。

Nginx 配置文件是以 block（块）形式组织，每个 block 都是以一个块名字和一对大括号 “{}” 表示组成，block 分为几个层级，整个配置文件为 main 层级，即最大的层级；在 main 层级下可以有 event、http 、mail 等层级，而 http 中又会有 server block，server block中可以包含 location block。即块之间是可以嵌套的，内层块继承外层块。最基本的配置项语法格式是“配置项名 配置项值1 配置项值2 配置项值3 ... ”；

每个层级可以有自己的指令（Directive），例如 worker_processes 是一个main层级指令，它指定 Nginx 服务的 Worker 进程数量。有的指令只能在一个层级中配置，如worker_processes 只能存在于 main 中，而有的指令可以存在于多个层级，在这种情况下，子 block 会继承 父 block 的配置，同时如果子block配置了与父block不同的指令，则会覆盖掉父 block 的配置。指令的格式是“指令名 参数1 参数2 … 参数N;”，注意参数间可用任意数量空格分隔，最后要加分号。

下图是 Nginx 配置文件通常结构图示。

![][1]

### Nginx 服务的基本配置项

Nginx 服务运行时，需要加载几个核心模块和一个事件模块，这些模块运行时所支持的配置项称为基本配置；基本配置项大概可分为以下四类：

* 用于调试、定位的配置项；
* 正常运行的必备配置项；
* 优化性能的配置项；
* 事件类配置项；

各个配置项的具体实现如下：
```
    /* Nginx 服务基本配置项 */
    
    /* 用于调试、定位的配置项 */
    
    #以守护进程 Nginx 运行方式
    #语法：daemon off | on;
    #默认：daemon on;
    
    #master / worker 工作方式
    #语法：master_process on | off;
    #默认：master_process on;
    
    #error 日志设置
    #                   路径        错误级别
    #语法：error_log    /path/file  level;
    #默认：error_log    logs/error.log  error;
    #其中/path/file是一个具体文件；level是日志的输出级别，其取值如下：
    #   debug info notice warn error crit alert emerg
    #从左至右级别增大；若设定一个级别后，则在输出的日志文件中只输出级别大于或等于已设定的级别；
    
    #处理特殊调试点
    #语法：debug_points [stop | abort]
    #这个设置是来跟踪调试 Nginx 的；
    
    #仅对指定的客户端输出 debug 级别的日志
    #语法：debug_connection [IP | DIR]
    
    #限制 coredump 核心转储文件的大小
    #语法：worker_rlimit_core   size;
    
    #指定 coredump 文件的生成目录
    #语法：working_directory    path;
    
    /* 正常运行的配置项 */
    
    #定义环境变量
    #语法：env  VAR | VAR=VALUE;
    #VAR 是变量名，VALUE 是目录；
    
    #嵌入其他配置文件
    #语法：include  /path/file;
    #include 配置项可以将其他配置文件嵌入到 Nginx 的 nginx.conf 文件中；
    
    #pid 的文件路径
    #语法：pid  path/file;
    #默认：pid  logs/nginx.pid;
    #保存 master 进程 ID 的 pid 文件存放路径；
    
    #Nginx worker 运行的用户及用户组
    #语法：user username    [groupname];
    #默认：user nobody nobody;
    
    #指定 Nginx worker进程可打开最大句柄个数
    #语法：worker_rlimit_nofile limit;
    
    #限制信号队列
    #语法：worker_rlimit_sigpending limit;
    #设置每个用户发给 Nginx 的信号队列大小，超出则丢弃；
    
    /* 优化性能配置项 */
    
    #Nginx worker 进程的个数
    #语法：worker_process   number;
    #默认：worker_process   1;
    
    #绑定 Nginx worker 进程到指定的 CPU 内核
    #语法：worker_cpu_affinity  cpumask [cpumask...]
    
    #SSL 硬件加速
    #语法：ssl_engine   device;
    
    #系统调用 gettimeofday 的执行频率
    #语法：timer_resolution t;
    
    #Nginx worker 进程优先级设置
    #语法：worker_priority  nice;
    #默认：worker_priority  0;
    
    /* 事件类配置项  */
    #一般有以下几种配置：
    #1、是否打开accept锁
    #   语法格式：accept_mutex [on | off];
    
    #2、lock文件的路径
    #   语法格式：lock_file  path/file;
    
    #3、使用accept锁后到真正建立连接之间的延迟时间
    #   语法格式：accept_mutex_delay Nms;
    
    #4、批量建立新连接
    #   语法格式：multi_accept [on | off];
    #
    #5、选择事件模型
    #   语法格式：use [kqueue | rtisg | epoll | /dev/poll | select | poll | eventport];
    
    #6、每个worker进行的最大连接数
    #   语法格式：worker_connections number;
```
    

HTTP 核心模块的配置

具体可以参看《[Nginx 中 HTTP 核心模块配置][2]》
```
    /* HTTP 核心模块配置的功能 */
    
    /* 虚拟主机与请求分发 */
    
    #监听端口
    #语法：listen   address:port[default | default_server | [backlong=num | rcvbuf=size | sndbuf=size | 
    # accept_filter | deferred | bind | ipv6only=[on | off] | ssl]];
    # 默认：listen:80;
    # 说明：
    #   default或default_server：将所在的server块作为web服务的默认server块；当请求无法匹配配置文件中的所有主机名时，就会选择默认的虚拟主机；
    #   backlog=num：表示 TCP 中backlog队列存放TCP新连接请求的大小，默认是-1，表示不予设置；
    #   rcvbuf=size：设置监听句柄SO_RCVBUF的参数；
    #   sndbuf=size：设置监听句柄SO_SNDBUF的参数；
    #   accept_filter：设置accept过滤器，只对FreeBSD操作系统有用；
    #   deferred：设置该参数后，若用户发起TCP连接请求，并且完成TCP三次握手，但是若用户没有发送数据，则不会唤醒worker进程，直到发送数据；
    #   bind：绑定当前端口 / 地址对，只有同时对一个端口监听多个地址时才会生效；
    #   ssl：在当前端口建立的连接必须基于ssl协议；
    #配置块范围：server
    
    #主机名称
    #语法：server_name  name[...];
    #默认：server_name  "";
    #配置块范围：server
    
    #server name 是使用散列表存储的
    #每个散列桶占用内存大小
    #语法：server_names_hash_bucket_size    size;
    #默认：server_names_hash_bucker_size    32|64|128;
    #
    #散列表最大bucket数量
    #语法：server_names_hash_max_size   size;
    #默认：server_names_hash_max_size   512;
    #默认：server_name_in_redirect  on;
    #配置块范围：server、http、location
    
    #处理重定向主机名
    #语法：server_name_in_redirect  on | off;
    #默认：server_name_in_redirect  on;
    #配置块范围：server、http、location
    
    #location语法：location[= | ~ | ~* | ^~ | @] /uri/ {}  
    #配置块范围：server
            #location尝试根据用户请求中的URI来匹配 /uri表达式，若匹配成功，则执行{}里面的配置来处理用户请求  
    #以下是location的一般配置项  
    #1、以root方式设置资源路径  
    #   语法格式：root path;  
    #2、以alias方式设置资源路径  
    #   语法格式：alias path;  
    #3、访问首页  
    #   语法格式：index file...;  
    #4、根据HTTP返回码重定向页面  
    #   语法格式：error_page code [code...] [= | =answer-code] uri | @named_location;  
    #5、是否允许递归使用error_page  
    #   语法格式：recursive_error_pages [on | off];  
    #6、try_files  
    #   语法格式：try_files path1 [path2] uri;  
    
    /* 文件路径的定义 */
    
    #root方式设置资源路径
    #语法：root path;
    #默认：root html;
    #配置块范围：server、http、location、if
    
    #以alias方式设置资源路径
    #语法：alias path;
    #配置块范围：location
    
    #访问主页
    #语法：index    file...;
    #默认：index    index.html;
    #配置块范围：http、server、location
    
    #根据HTTP返回码重定向页面  
    #   语法：error_page code [code...] [= | =answer-code] uri | @named_location;  
    #配置块范围：server、http、location、if
    
    #是否允许递归使用error_page  
    #   语法：recursive_error_pages [on | off];  
    #配置块范围：http、server、location
    
    #try_files  
    #   语法：try_files path1 [path2] uri;  
    #配置块范围：server、location
    
    /* 内存及磁盘资源分配 */
    
    # HTTP 包体只存储在磁盘文件中
    # 语法：client_body_in_file_only    on | clean | off;
    # 默认：client_body_in_file_only  off;
    # 配置块范围：http、server、location
    
    # HTTP 包体尽量写入到一个内存buffer中
    # 语法：client_body_single_buffer   on | off;
    # 默认：client_body_single_buffer   off;
    # 配置块范围：http、server、location
    
    # 存储 HTTP 头部的内存buffer大小
    # 语法：client_header_buffer_size   size;
    # 默认：client_header_buffer_size   1k;
    # 配置块范围：http、server
    
    # 存储超大 HTTP 头部的内存buffer大小
    # 语法：large_client_header_buffer_size   number    size;
    # 默认：large_client_header_buffer_size   4   8k;
    # 配置块范围：http、server
    
    # 存储 HTTP 包体的内存buffer大小
    # 语法：client_body_buffer_size   size;
    # 默认：client_body_buffer_size   8k/16k;
    # 配置块范围：http、server、location
    
    # HTTP 包体的临时存放目录
    # 语法：client_body_temp_path   dir-path    [level1 [level2 [level3]]];
    # 默认：client_body_temp_path   client_body_temp;
    # 配置块范围：http、server、location
    
    # 存储 TCP 成功建立连接的内存池大小
    # 语法：connection_pool_size    size;
    # 默认：connection_pool_size    256;
    # 配置块范围：http、server
    
    # 存储 TCP 请求连接的内存池大小
    # 语法：request_pool_size    size;
    # 默认：request_pool_size    4k;
    # 配置块范围：http、server
    
    /* 网络连接设置 */
    
    # 读取 HTTP 头部的超时时间
    # 语法：client_header_timeout   time;
    # 默认：client_header_timeout   60;
    # 配置块范围：http、server、location
    
    # 读取 HTTP 包体的超时时间
    # 语法：client_body_timeout   time;
    # 默认：client_body_timeout   60;
    # 配置块范围：http、server、location
    
    # 发送响应的超时时间
    # 语法：send_timeout   time;
    # 默认：send_timeout   60;
    # 配置块范围：http、server、location
    
    # TCP 连接的超时重置
    # 语法：reset_timeout_connection   on | off;
    # 默认：reset_timeout_connection   off;
    # 配置块范围：http、server、location
    
    # 控制关闭 TCP 连接的方式
    # 语法：lingering_close off | on | always;
    # 默认：lingering_close on;
    # 配置块范围：http、server、location
    # always 表示关闭连接之前无条件处理连接上所有用户数据；
    # off 表示不处理；on 一般会处理；
    
    # lingering_time
    # 语法：lingering_time   time;
    # 默认：lingering_time   30s;
    # 配置块范围：http、server、location
    
    # lingering_timeout
    # 语法：lingering_timeout   time;
    # 默认：lingering_time   5s;
    # 配置块范围：http、server、location
    
    # 对某些浏览器禁止keepalive功能
    # 语法：keepalive_disable   [mise6 | safari | none]...
    # 默认：keepalive_disable   mise6  safari;
    # 配置块范围：http、server、location
    
    # keepalive超时时间
    # 语法：keepalive_timeout   time;
    # 默认：keepalive_timeout   75;
    # 配置块范围：http、server、location
    
    # keepalive长连接上允许最大请求数
    # 语法：keepalive_requests  n;
    # 默认：keepalive_requests  100;
    # 配置块范围：http、server、location
    
    # tcp_nodelay
    # 语法：tcp_nodelay on | off;
    # 默认：tcp_nodelay on;
    # 配置块范围：http、server、location
    
    # tcp_nopush
    # 语法：tcp_nopush on | off;
    # 默认：tcp_nopush off;
    # 配置块范围：http、server、location
    
    /* MIME 类型设置 */
    
    # MIME type 与文件扩展的映射
    # 语法：type{...}
    # 配置块范围：http、server、location
    # 多个扩展名可映射到同一个 MIME type
    
    # 默认 MIME type
    # 语法：default_type    MIME-type;
    # 默认：default_type    text/plain;
    # 配置块范围：http、server、location
    
    # type_hash_bucket_size
    # 语法：type_hash_bucket_size   size;
    # 默认：type_hash_bucket_size   32 | 64 | 128;
    # 配置块范围：http、server、location
    
    # type_hash_max_size
    # 语法：type_hash_max_size   size;
    # 默认：type_hash_max_size   1024;
    # 配置块范围：http、server、location
    
    /* 限制客户端请求 */
    
    # 按 HTTP 方法名限制用户请求
    # 语法：limit_except    method...{...}
    # 配置块：location
    # method 的取值如下：
    # GET、HEAD、POST、PUT、DELETE、MKCOL、COPY、MOVE、OPTIONS、
    # PROPFIND、PROPPATCH、LOCK、UNLOCK、PATCH
    
    # HTTP 请求包体的最大值
    # 语法：client_max_body_size    size;
    # 默认：client_max_body_size    1m;
    # 配置块范围：http、server、location
    
    # 对请求限制速度
    # 语法：limit_rate  speed;
    # 默认：limit_rate  0;
    # 配置块范围：http、server、location、if
    # 0 表示不限速
    
    # limit_rate_after规定时间后限速
    # 语法：limit_rate_after  time;
    # 默认：limit_rate_after    1m;
    # 配置块范围：http、server、location、if
    
    /* 文件操作的优化 */
    
    # sendfile系统调用
    # 语法：sendfile    on | off;
    # 默认：sendfile    off;
    # 配置块：http、server、location
    
    # AIO 系统调用
    # 语法：aio on | off;
    # 默认：aio off;
    # 配置块：http、server、location
    
    # directio
    # 语法：directio    size | off;
    # 默认：directio    off;
    # 配置块：http、server、location
    
    # directio_alignment
    # 语法：directio_alignment    size;
    # 默认：directio_alignment    512;
    # 配置块：http、server、location
    
    # 打开文件缓存
    # 语法：open_file_cache max=N [inactive=time] | off;
    # 默认：open_file_cache off;
    # 配置块：http、server、location
    
    # 是否缓存打开文件的错误信息
    # 语法：open_file_cache_errors  on | off;
    # 默认：open_file_cache_errors  off;
    # 配置块：http、server、location
    
    # 不被淘汰的最小访问次数
    # 语法：open_file_cache_min_user  number;
    # 默认：open_file_cache_min_user  1;
    # 配置块：http、server、location
    
    # 检验缓存中元素有效性的频率
    # 语法：open_file_cache_valid  time;
    # 默认：open_file_cache_valid  60s;
    # 配置块：http、server、location
    
    /* 客户请求的特殊处理 */
    
    # 忽略不合法的 HTTP 头部
    # 语法：ignore_invalid_headers  on | off;
    # 默认：ignore_invalid_headers  on;
    # 配置块：http、server
    
    # HTTP 头部是否允许下划线
    # 语法：underscores_in_headers  on | off;
    # 默认：underscores_in_headers  off;
    # 配置块：http、server
    
    # If_Modified_Since 头部的处理策略
    # 语法：if_modified_since   [off | exact | before]
    # 默认：if_modified_since   exact;
    # 配置块：http、server、location
    
    # 文件未找到时是否记录到error日志
    # 语法：log_not_found   on | off;
    # 默认：log_not_found   on;
    # 配置块：http、server、location
    
    # 是否合并相邻的“/”
    # 语法：merge_slashes   on | off;
    # 默认：merge_slashes   on;
    # 配置块：http、server、location
    
    # DNS解析地址
    # 语法：resolver    address...;
    # 配置块：http、server、location
    
    # DNS解析的超时时间
    # 语法：resolver_timeout    time;
    # 默认：resolver_timeout    30s;
    # 配置块：http、server、location
    
    # 返回错误页面是否在server中注明Nginx版本
    # 语法：server_tokens   on | off;
    # 默认：server_tokens   on;
    # 配置块：http、server、location
```
    

以下是在 Ubuntu 12.04 系统成功安装 Nginx 之后的主配置文件：
```nginx
    #Nginx服务器正常启动时会读取该配置文件，以下的值都是默认的，若需要可自行修改；
    #以下是配置选项
    
    #Nginx worker进程运行的用户以及用户组
    #语法格式：user  username[groupname]
    #user  nobody;
    
    #Nginx worker 进程个数
    worker_processes  1;
    
    #error 日志设置
    #语法格式：error /path/file level
    #其中/path/file是一个具体文件；level是日志的输出级别，其取值如下：
    #debug info notice warn error crit alert emerg,从左至右级别增大；
    #若设定一个级别后，则在输出的日志文件中只输出级别大于或等于已设定的级别；
    #error_log  logs/error.log;
    #error_log  logs/error.log  notice;
    #error_log  logs/error.log  info;
    
    #保存master进程ID的pid文件存放路径
    #语法格式：pid path/file
    #pid        logs/nginx.pid;
    
    #事件类配置项
    #一般有以下几种配置：
    #1、是否打开accept锁
    #   语法格式：accept_mutex [on | off];
    #2、lock文件的路径
    #   语法格式：lock_file  path/file;
    #3、使用accept锁后到真正建立连接之间的延迟时间
    #   语法格式：accept_mutex_delay Nms;
    #4、批量建立新连接
    #   语法格式：multi_accept [on | off];
    #5、选择事件模型
    #   语法格式：use [kqueue | rtisg | epoll | /dev/poll | select | poll | eventport];
    #6、每个worker进行的最大连接数
    #   语法格式：worker_connections number;
    events {
        worker_connections  1024;
    }
    
    #以下是http模块
    http {
        include       mime.types;
        default_type  application/octet-stream;
    
        #log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
        #                  '$status $body_bytes_sent "$http_referer" '
        #                  '"$http_user_agent" "$http_x_forwarded_for"';
    
        #access_log  logs/access.log  main;
    
        sendfile        on;
        #tcp_nopush     on;
    
        #keepalive_timeout  0;
        keepalive_timeout  65;
    
        #gzip  on;
    
    #server块
    #   每个server块就是一个虚拟主机，按照server_name来区分
        server {
    #监听端口
            listen       80;
    #主机名称
            server_name  localhost;
    
            #charset koi8-r;
    
            #access_log  logs/host.access.log  main;
    #location语法：location[= | ~ | ~* | ^~ | @] /uri/ {}
            #location尝试根据用户请求中的URI来匹配 /uri表达式，若匹配成功，则执行{}里面的配置来处理用户请求
    #以下是location的一般配置项
    #1、以root方式设置资源路径
    #   语法格式：root path;
    #2、以alias方式设置资源路径
    #   语法格式：alias path;
    #3、访问首页
    #   语法格式：index file...;
    #4、根据HTTP返回码重定向页面
    #   语法格式：error_page code [code...] [= | =answer-code] uri | @named_location;
    #5、是否允许递归使用error_page
    #   语法格式：recursive_error_pages [on | off];
    #6、try_files
    #   语法格式：try_files path1 [path2] uri;
            location / {
                root   html;
                index  index.html index.htm;
            }
    
            #error_page  404              /404.html;
    
            # redirect server error pages to the static page /50x.html
            #
            error_page   500 502 503 504  /50x.html;
            location = /50x.html {
                root   html;
            }
    
            # proxy the PHP scripts to Apache listening on 127.0.0.1:80
            #
            #location ~ \.php$ {
            #    proxy_pass   http://127.0.0.1;
            #}
    
            # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
            #
            #location ~ \.php$ {
            #    root           html;
            #    fastcgi_pass   127.0.0.1:9000;
            #    fastcgi_index  index.php;
            #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
            #    include        fastcgi_params;
            #}
    
            # deny access to .htaccess files, if Apache's document root
            # concurs with nginx's one
            #
            #location ~ /\.ht {
            #    deny  all;
            #}
        }
    
        # another virtual host using mix of IP-, name-, and port-based configuration
        #
        #server {
        #    listen       8000;
        #    listen       somename:8080;
        #    server_name  somename  alias  another.alias;
    
        #    location / {
        #        root   html;
        #        index  index.html index.htm;
        #    }
        #}
    
        # HTTPS server
        #
        #server {
        #    listen       443 ssl;
        #    server_name  localhost;
    
        #    ssl_certificate      cert.pem;
        #    ssl_certificate_key  cert.key;
    
        #    ssl_session_cache    shared:SSL:1m;
        #    ssl_session_timeout  5m;
    
        #    ssl_ciphers  HIGH:!aNULL:!MD5;
        #    ssl_prefer_server_ciphers  on;
    
        #    location / {
        #        root   html;
        #        index  index.html index.htm;
        #    }
        #}
    
    }
```
    

参考资料：

《深入理解Nginx》

《[Nginx模块开发入门][3]》

《[Nginx开发从入门到精通][4]》

[0]: ./img/2016-09-01_57c7edce687dc.jpg
[1]: ./img/2016-09-01_57c7edce82018.jpg
[2]: http://nginx.org/en/docs/http/ngx_http_core_module.html
[3]: http://kb.cnblogs.com/page/98352/#section1-2
[4]: http://tengine.taobao.org/book/index.html
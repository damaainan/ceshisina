## Nginx 请求处理流程你了解吗？

来源：[https://mp.weixin.qq.com/s/otQIhuLABU3omOLtRfJnZQ](https://mp.weixin.qq.com/s/otQIhuLABU3omOLtRfJnZQ)

时间 2018-11-23 08:11:10

 
![][0]
 
  
![][1]
 
本文主要介绍了nginx的11个处理阶段和lua的8个处理阶段，并说明了  nginx和lua运行阶段的对应关系。
 
上篇文章回顾：[Linux网络编程之IO模型][6]

 
  
一
 
nginx 11 个处理阶段
 
   
nginx实际把http请求处理流程划分为了11个阶段，这样划分的原因是将请求的执行逻辑细分，以模块为单位进行处理，各个阶段可以包含任意多个HTTP模块并以流水线的方式处理请求。这样做的好处是使处理过程更加灵活、降低耦合度。这11个HTTP阶段如下所示：
 
1）NGX_HTTP_POST_READ_PHASE：
 
接收到完整的HTTP头部后处理的阶段，它位于uri重写之前，实际上很少有模块会注册在该阶段，默认的情况下，该阶段被跳过。
 
2）NGX_HTTP_SERVER_REWRITE_PHASE：
 
URI与location匹配前，修改URI的阶段，用于重定向，也就是该阶段执行处于server块内，location块外的重写指令，在读取请求头的过程中nginx会根据host及端口找到对应的虚拟主机配置。
 
3）NGX_HTTP_FIND_CONFIG_PHASE：
 
根据URI寻找匹配的location块配置项阶段，该阶段使用重写之后的uri来查找对应的location，值得注意的是该阶段可能会被执行多次，因为也可能有location级别的重写指令。
 
4）NGX_HTTP_REWRITE_PHASE：
 
上一阶段找到location块后再修改URI，location级别的uri重写阶段，该阶段执行location基本的重写指令，也可能会被执行多次。
 
5）NGX_HTTP_POST_REWRITE_PHASE：
 
防止重写URL后导致的死循环，location级别重写的后一阶段，用来检查上阶段是否有uri重写，并根据结果跳转到合适的阶段。
 
6）NGX_HTTP_PREACCESS_PHASE：
 
下一阶段之前的准备，访问权限控制的前一阶段，该阶段在权限控制阶段之前，一般也用于访问控制，比如限制访问频率，链接数等。
 
7）NGX_HTTP_ACCESS_PHASE：
 
让HTTP模块判断是否允许这个请求进入Nginx服务器，访问权限控制阶段，比如基于ip黑白名单的权限控制，基于用户名密码的权限控制等。
 
8）NGX_HTTP_POST_ACCESS_PHASE：
 
访问权限控制的后一阶段，该阶段根据权限控制阶段的执行结果进行相应处理，向用户发送拒绝服务的错误码，用来响应上一阶段的拒绝。
 
9）NGX_HTTP_TRY_FILES_PHASE：
 
为访问静态文件资源而设置，try_files指令的处理阶段，如果没有配置try_files指令，则该阶段被跳过。
 
10）NGX_HTTP_CONTENT_PHASE：
 
处理HTTP请求内容的阶段，大部分HTTP模块介入这个阶段，内容生成阶段，该阶段产生响应，并发送到客户端。
 
11）NGX_HTTP_LOG_PHASE：
 
处理完请求后的日志记录阶段，该阶段记录访问日志。
 
以上11个阶段中，HTTP无法介入的阶段有4个：
 
3）NGX_HTTP_FIND_CONFIG_PHASE
 
5）NGX_HTTP_POST_REWRITE_PHASE
 
8）NGX_HTTP_POST_ACCESS_PHASE
 
9）NGX_HTTP_TRY_FILES_PHASE
 
剩余的7个阶段，HTTP模块均能介入，每个阶段可介入模块的个数也是没有限制的，多个HTTP模块可同时介入同一阶段并作用于同一请求。
 
![][2]
 
HTTP阶段的定义，包括checker检查方法和handler处理方法，如下所示
 
![][2]

```c
typedef structngx_http_phase_handler_s ngx_http_phase_handler_t;/*一个HTTP处理阶段中的checker检查方法，仅可以由HTTP框架实现，以此控制HTTP请求的处理流程*/typedef ngx_int_t(*ngx_http_phase_handler_pt)(ngx_http_request_t *r, ngx_http_phase_handler_t*ph);/*由HTTP模块实现的handler处理方法*/typedef ngx_int_t(*ngx_http_handler_pt)(ngx_http_request_t *r);

struct ngx_http_phase_handler_s {    /*在处理到某一个HTTP阶段时，HTTP框架将会在checker方法已实现的前提下首先调用checker方法来处理请求，
    而不会直接调用任何阶段中的hanlder方法，只有在checker方法中才会去调用handler方法，因此，事实上所有
    的checker方法都是由框架中的ngx_http_core_module模块实现的，且普通模块无法重定义checker方法*/
    ngx_http_phase_handler_pt  checker;    /*除ngx_http_core_module模块以外的HTTP模块，只能通过定义handler方法才能介入某一个HTTP处理阶段以处理请求*/
    ngx_http_handler_pt        handler;    /*将要处理的下一个HTTP处理阶段的序号
    next的设计使得处理阶段不必按顺序依次执行，既可以向后跳跃数个阶段继续执行，也可以跳跃到之前的某个阶段重新
    执行，通常，next表示下一个处理阶段中的第1个ngx_http_phase_handler_t处理方法*/
    ngx_uint_t                 next;
};
```
 
一个http{}块解析完毕后，将会根据nginx.conf中的配置产生由ngx_http_phase_handler_t组成的数组，在处理HTTP请求时，一般情况下这些阶段是顺序向后执行的，但ngx_http_phase_handler_t中的next成员使得它们也可以非顺序地执行，ngx_http_phase_engine_t结构体就是所有ngx_http_phase_handler_t组成的数组，如下所示：

```c
typedef struct {    /*handlers是由ngx_http_phase_handler_t构成的数组首地址，它表示一个请求可能经历的所有ngx_http_handler_pt处理方法*/
    ngx_http_phase_handler_t  *handlers;    /*表示NGX_HTTP_SERVER_REWRITE_PHASE阶段第1个ngx_http_phase_handler_t处理方法在handlers数组中的序号，用于在执行
    HTTP请求的任何阶段中快速跳转到HTTP_SERVER_REWRITE_PHASE阶段处理请求*/
    ngx_uint_t                 server_rewrite_index;    /*表示NGX_HTTP_PREACCESS_PHASE阶段第1个ngx_http_phase_handler_t处理方法在handlers数组中的序号，用于在执行
    HTTP请求的任何阶段中快速跳转到NGX_HTTP_PREACCESS_PHASE阶段处理请求*/
    ngx_uint_t                 location_rewrite_index;
} ngx_http_phase_engine_t;
```
 
可以看到，ngx_http_phase_engine_t中保存了在当前nginx.conf配置下，一个用户请求可能经历的所有ngx_http_handler_pt处理方法，这是所有HTTP模块可以合作处理用户请求的关键，这个ngx_http_phase_engine_t结构体保存在全局的ngx_http_core_main_conf_t结构体中，如下：

```c
typedef struct {
    ngx_array_t                servers;         /* ngx_http_core_srv_conf_t */
    /*由下面各阶段处理方法构成的phases数组构建的阶段引擎才是流水式处理HTTP请求的实际数据结构*/
    ngx_http_phase_engine_t    phase_engine;
    ngx_hash_t                 headers_in_hash;
    ngx_hash_t                 variables_hash;
    ngx_array_t                variables;       /* ngx_http_variable_t */
    ngx_uint_t                 ncaptures;
    ngx_uint_t                 server_names_hash_max_size;
    ngx_uint_t                 server_names_hash_bucket_size;
    ngx_uint_t                 variables_hash_max_size;
    ngx_uint_t                 variables_hash_bucket_size;
    ngx_hash_keys_arrays_t    *variables_keys;
    ngx_array_t               *ports;
    ngx_uint_t                 try_files;       /* unsigned  try_files:1 */
    /*用于在HTTP框架初始化时帮助各个HTTP模块在任意阶段中添加HTTP处理方法，它是一个有11个成员的ngx_http_phase_t数组，
    其中每一个ngx_http_phase_t结构体对应一个HTTP阶段，在HTTP框架初始化完毕后，运行过程中的phases数组是无用的*/
    ngx_http_phase_t           phases[NGX_HTTP_LOG_PHASE + 1];
} ngx_http_core_main_conf_t;
```
 
在ngx_http_phase_t中关于HTTP阶段有两个成员：phase_engine和phases，其中phase_engine控制运行过程中的一个HTTP请求所要经过的HTTP处理阶段，它将配合ngx_http_request_t结构体中的phase_handler成员使用(phase_handler制定了当前请求应当执行哪一个HTTP阶段)；而phases数组更像一个临时变量，它实际上仅会在Nginx启动过程中用到，它的唯一使命是按照11个阶段的概率初始化phase_engine中的handlers数组。

```c
typedef struct {    /*handlers动态数组保存着每一个HTTP模块初始化时添加到当前阶段的处理方法*/
    ngx_array_t                handlers;
} ngx_http_phase_t;
```
 
在HTTP框架的初始化过程中，任何HTTP模块都可以在ngx_http_module_t接口的postconfiguration方法中将自定义的方法添加到handler动态数组中，这样，这个方法就会最终添加到phase_engine动态数组中。 
 
二
 
nginx lua 8个阶段
 
init_by_lua                         http 
 set_by_lua                         server, server if, location, location if 
 rewrite_by_lua                   http, server, location, location if 
 access_by_lua                    http, server, location, location if 
 content_by_lua                  location, location if 
 header_filter_by_lua          http, server, location, location if 
 body_filter_by_lua             http, server, location, location if 
 log_by_lua                         http, server, location, location if
 
1）init_by_lua：
 
在nginx重新加载配置文件时，运行里面lua脚本，常用于全局变量的申请。（例如：lua_shared_dict共享内存的申请，只有当nginx重起后，共享内存数据才清空，这常用于统计。）
 
2）set_by_lua：
 
流程分支处理判断变量初始化（设置一个变量，常用与计算一个逻辑，然后返回结果，该阶段不能运行Output API、Control API、Subrequest API、Cosocket API）
 
3）rewrite_by_lua：
 
转发、重定向、缓存等功能 (例如特定请求代理到外网，在access阶段前运行，主要用于rewrite)
 
4）access_by_lua：
 
IP准入、接口权限等情况集中处理(例如配合iptable完成简单防火墙，主要用于访问控制，能收集到大部分变量，类似status需要在log阶段才有。这条指令运行于nginx access阶段的末尾，因此总是在 allow 和 deny 这样的指令之后运行，虽然它们同属 access 阶段。）
 
5）content_by_lua：
 
内容生成，阶段是所有请求处理阶段中最为重要的一个，运行在这个阶段的配置指令一般都肩负着生成内容（content）并输出HTTP响应。
 
6）header_filter_by_lua：
 
应答HTTP过滤处理，一般只用于设置Cookie和Headers等，该阶段不能运行Output API、Control API、Subrequest API、Cosocket API(例如添加头部信息)。
 
7）body_filter_by_lua：
 
应答BODY过滤处理(例如完成应答内容统一成大写)（一般会在一次请求中被调用多次, 因为这是实现基于 HTTP 1.1 chunked 编码的所谓“流式输出”的，该阶段不能运行Output API、Control API、Subrequest API、Cosocket API）
 
8）log_by_lua：
 
会话完成后本地异步完成日志记录(日志可以记录在本地，还可以同步到其他机器)（该阶段总是运行在请求结束的时候，用于请求的后续操作，如在共享内存中进行统计数据,如果要高精确的数据统计，应该使用body_filter_by_lua，该阶段不能运行Output API、Control API、Subrequest API、Cosocket API）
 
三
 
nginx和lua运行阶段的对应关系
 
1）init_by_lua，运行在initialization Phase；
 
2）set_by_lua，运行在rewrite 阶段；
 
set 指令来自 ngx_rewrite 模块，运行于 rewrite 阶段；
 
3）rewrite_by_lua 指令来自 ngx_lua 模块，运行于 rewrite 阶段的末尾
 
4）access_by_lua 指令同样来自 ngx_lua 模块，运行于 access 阶段的末尾；
 
deny 指令来自 ngx_access 模块，运行于 access 阶段；
 
5）content_by_lua 指令来自 ngx_lua 模块，运行于 content 阶段；不要将它和其它的内容处理指令在同一个location内使用如proxy_pass；
 
echo 指令则来自 ngx_echo 模块，运行在 content 阶段；
 
6）header_filter_by_lua 运行于 content 阶段，output-header-filter 一般用来设置cookie和headers；
 
7）body_filter_by_lua，运行于 content 阶段；
 
8）log_by_lua，运行在Log Phase 阶段；
 
如图：
 
![][4]

 
参考资料：
 
1. https://blog.csdn.net/fb408487792/article/details/53610140
 
2. https://blog.csdn.net/lijinqi1987/article/details/53010000?locationNum=15&fps=1
 
3.https://blog.csdn.net/yangguanghaozi/article/details/54139258

 
![][5]


[6]: http://mp.weixin.qq.com/s?__biz=MzUxMDQxMDMyNg==&mid=2247484772&idx=1&sn=4dac1e0efe3d89fbc3091575d71d6c67&chksm=f9022e5dce75a74bc0ea87ae131b7e8d3c5b8bda532949f97bb135636219361a9b21fd49814f&scene=21#wechat_redirect
[1]: ../img/vMVNNv2.png
[2]: ../img/AFRfAfv.png
[3]: ../img/AFRfAfv.png
[4]: ../img/Aju2qqA.jpg
[5]: ../img/BRfAnui.jpg
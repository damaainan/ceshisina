# [Nginx 源代码笔记 - 知识点][0]

## Nginx 源代码笔记 - Prerequisite

本文对 Nginx 源代码中 HTTP 协议处理部分的零碎知识点进行汇总，以便日后查阅。

路很长，高人很多，要坚持，要抱着持续学习的态度。

本文目录

* [调试工具][1]
* [只言片语][2]
* [请求阶段][3]
    * [`POST_READ`][4]
    * [`SERVER_REWRITE`][5]
    * [`FIND_CONFIG`][6]
    * [`REWRITE`][7]
    * [`POST_REWRITE`][8]
    * [`PREACCESS`][9]
    * [`ACCESS`][10]
    * [`POST_ACCESS`][11]
    * [`TRY_FILES (PRECONTENT)`][12]
    * [`CONTENT`][13]
    * [`LOG`][14]
* [模块顺序][15]
* [数据字段][16]
    * [`ngx_connection_t::data`][17]
    * [`ngx_connection_t::buffered`][18]
    * [`ngx_http_request_t::postponed`][19]
    * [`ngx_event_t::active`][20]
    * [`ngx_event_t::ready`][21]
    * [`ngx_http_request_t::valid_location`][22]
    * [`ngx_http_request_t::uri_changed`][23]
    * [`ngx_http_request_t::count`][24]
* [函数调用][25]
    * [`ngx_http_close_connection`][26]
    * [`ngx_http_finalize_connection`][27]
    * [`ngx_http_termniate_request`][28]
    * [`ngx_http_free_request`][29]
    * [`ngx_http_close_request`][30]
    * [`ngx_http_finalize_request`][31]
    * [`ngx_event_t::handler`][32]
    * [`ngx_request_t::read_event_handler` 和 `write_event_handler`][33]
* [请求变量][34]
    * [`$request_body`][35]

### [调试工具][36]

* 测试用例 - Test::Nginx (perl)
* 检查内存泄漏 - valgrind (`worker_processes 1; daemon off; master_process off;`), [no-pool-patch][37]
```
    valgrind --log-file=valgrid.log --leak-check=full --leak-resolution=high \
             --track-origins=yes --show-reachable=yes
    
    root@laptop:/usr/local/src/nginx-1.4.3# patch -p1 <../no-pool-nginx/nginx-1.4.3-no_pool.patch
```
* valgrind stress file - 自动化 valgrind 检测。
* 单步调试 - gdb, cgdb
* 调用栈 - pstack, strace, lstrace, systemtap, -finstrument-functions, valgrind --tool=callgrind
* 压力测试 - ab, httperf, wrk
* 真实流量压测 - tcpcopy, goreplay, ngx-http-mirror-module

### [只言片语][38]

* 指令解析回调函数，可以返回字符串描述错误信息（"is duplicate")。此错误信息会由 Nginx 拼接成完整信息 后，在 终端打印出来：
```
    nginx: [emerg] "spent_prefix" directive is duplicate in /usr/local/nginx/conf/nginx.conf:101
```
* 配置结构创建函数，返回 `NULL` 或者实际结构体。返回 `NULL` 时，Nginx 不会打印任何出错信息。所以 ，最好 使用 `ngx_conf_log_error` 函数手动打印出错信息。
* Nginx 提供的类型转换符 `%V` 对应的类型是 `ngx_str_t *`，而不是 `ngx_str_t` 。
* `ngx_cpystrn(u_char *dst, u_char *src, size_t n)` 函数的第三个参数 `n` 指示 `dst` 对应内存块 的最大长度 (including the terminating null byte，参考函数 `snprintf` 函数定义)。
* `ngx_str_t` 类型只有两个成员，其中 `ngx_str_t::data` 指针指向字符串起始地址， `ngx_str_t::len` 表示字符串的有效长度。 `ngx_str_t::data` 指向的可能并不是普通的字符串，未必会 以 `\0` 结尾，所以使用 `ngx_str_t` 时必须根据长度 `ngx_str_t::len` 的值确定字符串长度。
* `ngx_buf_t` 和 `ngx_chain_t`
    * `ngx_buf_t` 可以表示的对象有：内存块、磁盘文件和特殊标志；
    * `ngx_buf_t` 通常和 `ngx_chain_t` 配对使用：`ngx_chain_t` 将 `ngx_buf_t` 包装成单链表结 点 然后 Nginx 使用由 `ngx_chain_t` 组成单链表来表示逻辑上连续的数据；
* 分清以下四个回调函数的功能，它们是理解请求处理流程的关键：
```
    c->read->handler = ngx_http_request_handler;
    c->write->handler = ngx_http_request_handler;
    r->read_event_handler = ngx_http_block_reading;
    r->write_event_handler = ngx_http_block_reading;
```
### [请求阶段][39]

Nginx 将 HTTP 请求处理流程分为几个阶段（PHASE）进行，每个阶段对应的 **phase checker** 按注册顺序逐个 调用 各模块提供的回调函数，也就是 **phase handler** 。针对所有请求的 **phase checker** 及 **phase handler** 执行顺序在 Nginx 进程启动时，在 `ngx_http_init_phase_handlers` 函数中定义。

下面列出 Nginx 定义的阶段，以及该阶段中 **phase checker** 可以处理的 **phase handler** 返回值。

#### [`POST_READ`][40]

该阶段的 **phase checker** 函数是 `ngx_http_core_generic_phase` 。各模块注册到该阶段的 **phase handler** 可以使用如下返回值控制请求处理流程：

错误码 | 处理方式 
-|-
NGX_OK | 将请求转入下一处理阶段（跳过本阶段还未调用的 phase handler） 
NGX_DECLINED | 为请求调用下一个 phase handler（可能是本阶段的，也可能是下一个阶段的） 
NGX_AGAIN/NGX_DONE | 需对该请求再次调用当前 phase handler 
其它 | 错误码是 `NGX_ERROR` 或者` NGX_HTTP_*` 等等时，提前结束当前请求 

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* **ngx_http_realip_module** - Change the client address to the one sent in the specificed header field.

#### [`SERVER_REWRITE`][41]

该阶段的 **phase checker** 函数为 `ngx_http_core_rewrite_phase` 。各模块注册到该阶段的 **phase handler** 可以使用如下返回值控制请求处理流程：

错误码 | 处理方式 
-|-
NGX_DECLINED | 为请求调用下一个 phase handler（可能是本阶段的，也可能是下一个阶段的） 
NGX_DONE | 需对该请求再次调用当前 phase handler 
其它 | 错误码是 `NGX_OK`, `NGX_AGAIN`, `NGX_ERROR` 或者 `NGX_HTTP_xx` 等等 时，提前结束当前请求 

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* **ngx_http_rewrite_module** - 执行定义于 `server {}` 的 `rewrite`, `set`, `if` 等指令。

#### [`FIND_CONFIG`][42]

该阶段的 **phase checker** 函数为 `ngx_http_core_find_config_phase` 。该阶段属于 Nginx 内部流程， 不允 许模块注册 **phase handler** 。

`ngx_http_core_find_config_phase` 函数根据请求 `uri` 匹配合适的 `location {}` 配置块。

#### [`REWRITE`][43]

该阶段的 **phase checker** 函数为 ngx_http_core_rewrite_phase 。各模块注册到该阶段的 **phase handler** 可以使用的返回值和 Nginx 对这些返回值的处理方式和 SERVER_REWRITE 阶段一致。

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* **ngx_http_rewrite_module** - 执行定义于 `location {}` 的 `rewrite`, `set`, `if` 等指令。

#### [`POST_REWRITE`][44]

该阶段的 **phase checker** 函数为 `ngx_http_core_post_rewrite_phase` 。该阶段属于 Nginx 内部流程， 不 允许模块注册 **phase handler** 。

该阶段检查 `REWRITE` 阶段的执行结果并执行不同逻辑：如果请求 `uri` 被上个阶段修改过的话 （ `r->uri_changed = 1`），将此请求转到 `FIND_CONFIG` 阶段，重新进行 `location {}` 查找和匹配；如 果请求 `uri` 未被上个阶段修改的话，继续为请求调用 `PREACCESS` 阶段的 **phase handler** 。

#### [`PREACCESS`][45]

该阶段的 **phase checker** 函数为 `ngx_http_core_generic_phase` 。各模块注册到该阶段的 **phase handler** 可以使用的返回值和 Nginx 对这些返回值的处理方式和 `POST_READ` 阶段一致。

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* `ngx_http_limit_conn_module` - limits the number of connections per the defined key, in particular, the number of of connections from a single IP address.
* `ngx_http_limit_req_module` - limits the request processing rate per the defined key, in particular, the processing rate of requests coming from a single IP address.
* `ngx_http_degradation_module` - returns 204 or 444 code for some locations on low memory condition.
* `ngx_http_realip_module` - Change the client address to the one sent in the specificed header field.

#### [`ACCESS`][46]

该阶段的 **phase checker** 函数为 `ngx_http_core_access_phase` 。各模块注册到该阶段的 **phase handler** 可以使用如下返回值控制请求处理流程：

错误码 | 处理方式 
-|-
`r != r->main` | 当前请求是子请求，直接将其转入下一处理阶段 
NGX_DECLINED | 为请求调用下一个 phase handler（可能是本阶段的，也可能是下一个阶段的） 
NGX_AGAIN/NGX_DONE | 需对该请求再次调用当前 phase handler 
NGX_OK | * SATISFY_ALL: 为请求调用下一个 phase handler（可能是本阶段的，也可能是 下一个阶段的）<br/>* SATISFY_ANY: `r->access_code` 赋 `0` 值，并将请求转到下一处理阶段
NGX_HTTP_FORBIDDEN | * SATISFY_ALL: 提前结束当前请求<br/>* SATISFY_ANY: 将该错误码赋值给 `r->access_code` 变量，并为请求调用下一个 phase handler。
NGX_HTTP_UNAUTHORIZED | 处理逻辑和 `NGX_HTTP_FORBIDDEN` 相同 
其它 | 错误码是 `NGX_ERROR` 或者 `NGX_HTTP_*` 等等时，提前结束当前请求 

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* `ngx_http_auth_basic_module` - limits access to resources by validating the user name and password using the "HTTP Basic Authentication" protocol.
* `ngx_http_access_module` - limits access to certain client addresses.

#### [`POST_ACCESS`][47]

该阶段的 **phase checker** 函数为 `ngx_http_core_post_access_phase` 。该阶段属于 Nginx 内部流程， 不允 许模块注册 **phase handler** 。

该阶段检查 ACCESS 阶段的处理结果并执行不同逻辑：如果 `r->access_code == NGX_HTTP_FORBIDDEN` 则 提 前结束该请求处理（使用 `NGX_HTTP_FORBIDDEN` 作为 HTTP 响应码）；如果 `r->access_code` 为其它 非 0 值，则提前结束该请求处理；如果 `r->access_code == 0` 值，为请求调用下一下 **phase handler** 。

#### [`TRY_FILES (PRECONTENT)`][48]

NOTES：从 Nginx 1.13.4 开始，此阶段更名为 `PRECONTENT` ，并使用 `ngx_http_core_generic_phase` 作 为 **phase checker** `。try_files` 指令功能由模块 `ngx_http_try_files_module` 提供。

在 Nginx 1.13.4 之前的版本，该阶段的 **phase checker** 函数为 `ngx_http_core_try_files_phase` 。该 阶 段属于 Nginx 内部流程，不允许模块注册 **phase handler** 。

如果请求使用的 `location {}` 块未配置 `try_files` 指令，将该请求转入下一个 **phase handler** 。

如果请求使用的 `location {}` 中使用了 `try_files` 指令，那么继续检查该指令的参数：如果参数（最后 一个 参数除外）对应的磁盘静态文件存在，将静态文件内容返回给客户端；如果参数对应的磁盘静态文件都不存 在，使用函数 `ngx_http_internal_redirect` 将该请求重定向到 `try_files` 指令最后一个参数指定的 `location` 后， 重新处理该请求。

#### [`CONTENT`][49]

该阶段的 **phase checker** 函数为 `ngx_http_core_content_phase` 。各模块注册到该阶段的 **phase handler** 可以使用如下返回值控制请求处理流程：

错误码 | 处理方式 
-|-
`r->content_handler` | 如果 `location {}` 配置有 **content handler** ，使用它处理请求，并忽略其它 **phase handler** 
`NGX_DECLINED` | 为请求调用下一个 phase handler。如果所有 phase handler 都已经被调用过后，则结束该 请求处理过程（使用响应码：`NGX_HTTP_FORBIDDEN` (请求 uri 以 '/' 结束) 或者 `NGX_HTTP_NOT_FOUND` （请求 uri 不以 '/' 结束)） 
其它 | 结束该请求处理流程（错误码作为 `ngx_http_finalize_request` 函数参数使用） 

从上面的分析可以看到，请求的响应数据可以由 **content handler** 函数或者 **phase handler** 函数提供。 **content handler** 优先级比 **phase handler** 高，并且具有排他性。**phase handler** 可以看作是 **CONTENT** 阶段为请求提供的通用处理逻辑，而 **content handler** 是某个 `location {}` 块为请求提供 的 特殊处理逻辑。

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* `ngx_http_random_index_module` - processes requests ending with the slash character ('/') and picks a random file in a directory to serve as an index file.
* `ngx_http_index_module` - processes requests ending with the slash character ('/').
* `ngx_http_autoindex_module` - processes requests ending with the slash character ('/') and produces a directory listings.
* `ngx_http_dav_module` - intended for file management automation via the WebDAV protocol. The module processes HTTP and WebDAV methods PUT, DELETE, MKCOL, COPY, and MOVE.
* `ngx_http_gzip_static_module` - allows sending precompressed files with the ".gz" filename extension instead of regular files.
* `ngx_http_static_module` - 静态文件响应模块

Nginx 自带模块中提供了 **content handler** 的有：

* `ngx_http_fastcgi_module`
* `ngx_http_scgi_module`
* `ngx_http_memcached_module`
* `ngx_http_proxy_module`
* `ngx_http_stub_status_module`
* `ngx_http_flv_module`
* `ngx_http_mp4_module`
* `ngx_http_empty_gif_module`
* `ngx_http_perl_module`
* `ngx_http_uwsgi_module`

#### [`LOG`][50]

该阶段比较特殊，它并没有对应 **phase checker** ，该阶段的 **phase handler** 在请求处理结束时，由 `ngx_http_log_request` 函数直接调用。

Nginx 自带模块中在此阶段注册 **phase handler** 的模块有：

* `ngx_http_log_module`

### [模块顺序][51]

标准配置下，_filter_ 模块的调用顺序（和模块初始化顺序相反）如下：

调用顺序 | 模块名 | 提供的 filter 
-|-|-
1 | ngx_http_not_modified_filter_module | header 
2 | ngx_http_range_body_filter_module | body 
3 | ngx_http_copy_filter_module | body 
4 | ngx_http_headers_filter_module | header 
5 | **third party filter goes here** | -
6 | ngx_http_userid_filter_module | header 
7 | ngx_http_ssi_filter_module | header and body 
8 | ngx_http_charset_filter_module | header and body 
9 | ngx_http_postpone_filter_module | body 
10 | ngx_http_gzip_filter_module | header and body 
11 | ngx_http_range_header_filter_module | header 
12 | ngx_http_chunked_filter_module | header and body 
13 | ngx_http_header_filter_module | header 
14 | ngx_http_write_filter_module | body 

### [数据字段][52]

#### [`ngx_connection_t::data`][53]

这个字段有多种用途，它得值随着 `ngx_connection_t` 的状态变化而代表不同含义：

* 当该连接被回收放入 Nginx 的空闲连接池（单链表）时，该指针字段充当用于单链表节点的 _next_ 指针：
* 客户端和 Nginx 建立 TCP 连接之后，Nginx 开始读取并处理该 TCP 连接上的 HTTP 请求数据之前，该指针字 段指向 ngx_http_connection_t 类型的变量。该变量中保存该 HTTP 请求对应的虚拟主机、虚拟主机的配 置结构体、 SSL 主机名称等 「HTTP 连接」相关的信息。
* 在随后的 HTTP 请求处理过程（请求数据接收，响应数据生成等）中，该字段始终指向该 TCP 连接上的当前活 跃请求 （由于HTTP Pipeline 技术、Nginx 子请求机制等等，当前 TCP 连接上可能会对应多个请求实例）。该 请求的响应数据可以立即发送给客户端。

#### [`ngx_connection_t::buffered`][54]

> More explanation needed.

#### [`ngx_http_request_t::postponed`][55]

当该指针字段值不是 `NULL` 时，指向一个节点类型是 `ngx_http_postponed_request_t` 的单链表。这个单 链表 的节点中包含该请求创建的子请求（ `ngx_http_postponed_request_t::request` ），或者该请求产生的 还 未发出的响 应数据（ `ngx_http_postponed_request_t::out` ）。

当该指针字段值是 NULL 时，该请求无待处理子请求。

#### [`ngx_event_t::active`][56]

Nginx 使用的事件模块是否正在管理该事件结构体。

#### [`ngx_event_t::ready`][57]

该事件结构体是否就绪（有未处理事件）。

#### [`ngx_http_request_t::valid_location`][58]

> `More explanation needed`.当 **rewrite** 指令使用 break 修改了 `r->uri` 后，此标志位变量被置为 0；如果此请求被 `ngx_http_internal_redirect` 函数在 Nginx 内部重定向的话，这个标志位被重置为 1。

#### [`ngx_http_request_t::uri_changed`][59]

> More explanation needed.当 **rewrite** 指令使用了非 break 修改了 `r->uri` 后，此标志位变量被置为 1；

#### [`ngx_http_request_t::count`][60]

请求引用计数。关于它的作用，在《深入理解 Nginx：模块开发与架构解析》第 1 版的 11.8 节有过介绍：

    > 在 HTTP 模块中每进行一类新的（异步）操作，包括为一个请求添加新的事件，或者把一些已经
    > 由定时器、epoll 中移除事件重新加放其中，都需要把这个请求的引用计数加 1.这是因为需要让
    > HTTP 框架知道，HTTP 模块对于该请求有独立的异步处理机制，将由该 HTTP 模块决定这个操作
    > 什么时候结束，防止在这个操作还未结束时 HTTP 框架却把这个请求销毁了（如其它 HTTP 模块通
    > 过调用 ngx_http_finalize_request 方法要求 HTTP 框架结束请求），异致请求出现不可知的
    > 严重错误。这就要求每个操作在 “认为” 自身的动作结束时，都得最终调用
    > ngx_http_close_request 方法，该方法会自动检查引用计数，当引用计数为 0 时才真正地销销
    > 毁请求。
    

### [函数调用][61]

#### [`ngx_http_close_connection`][62]

该函数用于关闭连接。

- 函数签名：

```c
    void
    ngx_http_close_connection(ngx_connect_t *c);
```
- 主要功能：
函数 `ngx_close_connection` 从事件模块中将该连接注册的事件（网络事件、超时事件）全部清理掉后， 关闭 底层 socket 描述符。同时，调用 `ngx_free_connection` 函数将连接的结构体存入单链表 `ngx_cycle->free_connections` 以便下次使用。

该函数对连接对应的 SSL 相关结构进行清理，关闭连接对应的内存池，然后调用 `ngx_close_connection` 函数 完成其它清理工作。

#### [`ngx_http_finalize_connection`][63]

该函数在请求正常处理完成后，调用 `ngx_http_close_request` 关闭请求（请求引用计数减一），并判断需要 关闭连 接或是使其保持连通（keepalive 连接）。

- 函数签名：

```c
    static void
    ngx_http_finalize_connection(ngx_http_request_t *r);
```
#### [`ngx_http_termniate_request`][64]

强制清理并销毁请求。

- 函数签名：

```c
    static void
    ngx_http_terminate_request(ngx_http_request_t *r, ngx_int_t rc);
```
- 主要功能：
该函数调用为在请求上注册的清理函数（cleanup handler），并强制将引用计数置 1，然后使用 `nxx_http_close_request` 函数销毁该请求。

#### [`ngx_http_free_request`][65]

该函数对请求进行清理和销毁。

- 函数签名：

```c
    void
    ngx_http_free_request(ngx_http_request_t *r, ngx_int_t rc);
```
- 主要功能：
该函数调用为在请求上注册的清理函数（cleanup handler）、为该请求调用 LOG 阶段的 **phase handler** ， 然后销毁请求内存池。

#### [`ngx_http_close_request`][66]

该函数关闭对请求的一次引用。

- 函数签名：

```c
    static void
    ngx_http_close_request(ngx_http_request_t *r, ngx_int_t rc);
```
- 主要功能：
该函数将请求 `r` 所属的主请求（ `r->main` ）的引用计数（ `r->main->count` ）减 1。如果主请 求引用计数等于 0，调用 `ngx_http_free_request` 函数清理并销毁主请求，调用 `ngx_http_close_connection` 函数清理并销毁请求所用的连接。

#### [`ngx_http_finalize_request`][67]

该函数根据请求处理结果，决定请求接下来的处理流程。

- 函数签名：

```c
    void
    ngx_http_finalize_request(ngx_http_request_t *r, ngx_int_t rc);
```
- 主要功能：
该函数功能比较复杂，逻辑分支较多。接下来，我们根据参数 rc 值和请求当前的状态，总结一下该函 数的主要分支流程和处理方式（使用源代码版本号：1.13.6）：

顺序 | 分支条件 | 处理方式 
-|-|-
1 | `rc == NGX_DONE` |  请求当次动作已经处理完成，但是该请求上还有其它进行中的异步动作。调用 `ngx_http_finalize_connection` 函数将请求引用计数减一。 **return**
2 | `rc == DECLINED` | content handler 无法处理该请求，将其交由 CONTENT 阶段的 phase handler 处理。 **return** 
3 | rc 值是 `NGX_ERROR` ， `NGX_HTTP_REQUEST_TIMEOUT` ， `NGX_HTTP_CLIENT_CLOSE_REQUEST` 错误码之一，或者 `c->error == 1` | 该请求所在的连接上发生了错误，调用 `ngx_http_terminate_request` 强制清理 销毁该请求。 **return** 
4 | `rc >= NGX_HTTP_SPECIAL_RESPONSE` 或者 `rc` 值是 `NGX_HTTP_CREATED` ， `NGX_HTTP_NO_CONTENT` 中的一个 | 如果 rc 值是 `NGX_HTTP_CLOSE` (大于 `NGX_HTTP_SPECIAL_RESPONSE` ），立即调 用 `ngx_http_terminate_request` 销毁请求；其它情况调用函数 `ngx_http_special_response_handler` 重新生成此请求的响应数据。这时需要再次 调用函数 `ngx_http_finalize_request` 处理 `ngx_http_special_response_handler` 的返回值。**return** 请求响应发送完毕、请求响应数据未发送完毕、请求是子请求 等等正常业务流程继续向下进行分支处理 
5 | `r != r->main` | 当前请求是「子请求」时，函数继续以下处理流程：<br/><br/>1. 如果该子请求是「后台子请求」，Nginx 认为此时该子请求已经处理完成（Why？）。 置其 `r->done` 值为 1，随后调用函数 `ngx_http_finalize_connection` 将 其占用的引用计数清除。**return**<br/><br/> 2. 如果该子请求非「后台子请求」，并且响应数据未完全发送（ `r->buffered == 1` ） 或者该子请求创建的子请求未全部完成（ `r->postponed != NULL` ），使用函数 `ngx_http_set_write_handler` 为其注册写事件处理函数 `ngx_http_writer` 待有事件发生时继续处理该请求。**return** <br/><br/> 3. 对于己处理完成的普通子请求，如果它是「连接活跃请求」（ `c->data == r` ）， 那么将其标记为己完成（ `r->done = 1` ），并将其父请求（ `r->parent` ）设为 「连接活跃请求」。随后调用函数 `ngx_http_post_request` 将父请求添加到主 请求的 `posted_request` 链表中，以便由函数 `ngx_http_run_posted_requests` 调用其写回调函数 `write_event_handler` ，启动其处理流程。**return**
6 | `r == r->main` | 当前请求是「主请求」，函数继续以下处理流程：<br/><br/>1. 如果请求数据未发送完毕（ `r->buffered || c->buffered` ）或者还有子请求未 处理完（ `r->postponed` ）使用函数 `ngx_http_set_write_handler` 为其注 册写事件处理函数 `ngx_http_writer` 待有事件发生时继续处理该请求。**return**<br/><br/>2. 哪果请求不是「连接活跃请求」（ `r != c->data` ），暂停该请求处理流程。<br/><br/>3. 请求处理完毕，调用函数 `ngx_http_finalize_connection` 关闭该请求和底层 连接。**return**

**函数结束**

- 遗留问题：
    * 后台子请求如果还未正常执行完毕时，会不会进入 5.1 步？
    * 在第 5.2 步时，如果当前请求不是「连接活跃请求」，它的响应数据被 `ngx_http_postpone_filter` 追加到了它的 `postponed` 字段中，那么在该步骤这个请求不会继续往下处理，谁来打破这个循环？

#### [`ngx_event_t::handler`][68]

事件驱动框架直接调用的事件回调函数。

- 函数签名：

```c
    typedef void (*ngx_event_handler_pt)(ngx_event_t *ev);
```

针对 Nginx HTTP 模块来说，Nginx 接入客户端连接后，为此连接分配 `ngx_connection_t` 结构体。该结构体 含有 对应「可读事件」和「可写事件」的两个 `ngx_event_t` 类型成员 `read` 和 `write` ，这两个成 员的回调函数 会在事件发生时被调用。下面我们简要列出随着请求状态改变，这两个回调函数的值变化过程：

1. Nginx 调用函数 `ngx_event_accept` 接入连接，并为其分配 `ngx_connection_t` 结构：
```c
    void
    ngx_event_accept(ngx_event_t *ev)
    {
        ...
        c = ngx_get_connection(s, ev->log);
        ...
        ls->handler(c);
        ...
    }
```
1. 随后，Nginx 调用函数 `ngx_http_init_connection` 设置「读事件」回调函数，为接收请求数据做准备：
```c
    void
    ngx_http_init_connection(ngx_connection_t *c)
    {
        ...
        rev = c->read;
        rev->handler = ngx_http_wait_request_handler;
        c->write->handler = ngx_http_empty_handler;
        ...
        if (rev->ready) {
            ...
            rev->handler(rev);
            return;
        }
        ...
        if (ngx_handle_read_event(rev, 0) != NGX_OK) {
            ...
        }
    }
```
1. 函数 `ngx_http_wait_request_handler` 从连接读取部分数据，然后创建请求结构体 `ngx_request_t` 。然后 调整「可读事件」回调函数为 `ngx_http_process_request_line` ，用于接收和分析请求 `status line`。这期间 如果已接收数据不足构成完整 `status line` 的话，Nginx 会为该连接注册「可读事件」，等新 数据到达后，继续向下 执行。
```c
    void
    ngx_http_wait_request_handler(ngx_event_t *rev)
    {
        ...
        c->data = ngx_http_create_request(c);
        rev->handler = ngx_http_process_request_line;
        ngx_http_process_request_line(rev);
    }
    
    void
    ngx_http_process_request_line(ngx_event_t *rev)
    {
        ...
        for (;;) {
            if (rc == NGX_AGAIN) {
                n = ngx_http_read_request_header(r);
                if (n == NGX_AGAIN || n == NGX_ERROR) {
                    return;
                }
                ...
            }
            ...
            rev->handler = ngx_http_process_request_headers;
            ngx_http_process_request_headers(rev);
        }
    
    }
```
1. 请求 status line 处理完毕后，Nginx 调整「读事件」函数 `ngx_http_process_request_headers` ，用于 接收 和分析请求 headers。这期间如果已接收数据不足构成完整 `header` 的话，Nginx 会为该连接注册「可读 事件」，等新数据到达后，继续向下执行。
```c
    void
    ngx_http_process_request_headers(ngx_event_t *ev)
    {
        ...
        n = ngx_http_read_request_header(r);
        if (n == NGX_AGAIN || n == NGX_ERROR) {
            return;
        }
        ...
        if (rc == NGX_HTTP_PARSE_HEADER_DONE) {
            ...
            ngx_http_process_request(r);
            return;
        }
        ...
    }
```
1. 请求包头数据接收并解析完成后，调用函数 `ngx_http_process_request` 开启处理请求，生成响应数据的 流程。 此时，Nginx 才为连接设定有效的「可写事件」回调函数。
    * 接下来，「可读事件」和「可写事件」均由回调函数 `ngx_http_request_handler` 处理，它会根据事件 类型 调用请求结构体 `ngx_request_t` 中 `read_event_handler` 和 `write_event_handler` 成员 指向的函数。
    * 由于 Nginx 已经获取了所需要的请求数据，所以再有「可读事件」发生时，它使用函数 `ngx_http_blocking_reading` 暂时屏蔽该事件。
    * 而「可写事件」驱动请求进行 PHASE 处理流程（`ngx_http_core_run_phases`）。
    * 同时，每次事件发生时，Nginx 还会调用函数 `ngx_http_run_posted_requests` 触发「就绪」 （_posted_ ， 有 “张贴”、“发布” 的意思，也就是说这类请求已经处于就绪状态，等待被调度处理）请求 的处理流程。

```c
    void
    ngx_http_process_request(ngx_event_t *ev)
    {
        ...
        c->read->handler = ngx_http_request_handler
        c->write->handler = ngx_http_request_handler;
        r->read_event_handler = ngx_http_block_reading;
    
        ngx_http_handler(r);
        ngx_http_run_posted_requests(c);
    }
    
    void
    ngx_http_handler(ngx_http_request_t *r)
    {
        ...
        r->write_event_handler = ngx_http_core_run_phases;
        ngx_http_core_run_phases(r);
    }
    
    void
    ngx_http_request_handler(ngx_event_t *ev)
    {
        ...
        if (ev->write) {
            r->write_event_handler(r);
        } else {
            r->read_event_handler(r);
        }
    
        ngx_http_run_posted_requests(c);
    }
```
1. 从此以后，该请求的「可读事件」和「可写事件」回调函数基本就不再变化了。请求在不同阶段的处理流程由 `ngx_request_t::read_event_handler` 和 `write_event_handler` 这两个函数决定。

#### [`ngx_request_t::read_event_handler` 和`write_event_handler`][69]

请求数据接收完毕后，请求处理进入响应生成和响应发送阶段。这两个函数用于在不同处理阶段驱动请求的处理流 程。
```
    write_event_handler:
        ngx_http_core_run_phases
        ngx_http_writer
        ngx_http_request_finalizer
        ngx_http_terminate_handler
```
### [请求变量][70]

#### [`$request_body`][71]

It contains the body of the request. 这个变量当且仅当 Nginx 读取了请求包体，并且请求包体没有被写入临 时文件时，才能从它得到包体数据。

[0]: http://ialloc.org/posts/2017/11/03/ngx-notes-prerequisite/
[1]: #id2
[2]: #id3
[3]: #id4
[4]: #post-read
[5]: #server-rewrite
[6]: #find-config
[7]: #rewrite
[8]: #post-rewrite
[9]: #preaccess
[10]: #access
[11]: #post-access
[12]: #try-files-precontent
[13]: #content
[14]: #log
[15]: #id5
[16]: #id6
[17]: #ngx-connection-t-data
[18]: #ngx-connection-t-buffered
[19]: #ngx-http-request-t-postponed
[20]: #ngx-event-t-active
[21]: #ngx-event-t-ready
[22]: #ngx-http-request-t-valid-location
[23]: #ngx-http-request-t-uri-changed
[24]: #ngx-http-request-t-count
[25]: #id7
[26]: #ngx-http-close-connection
[27]: #ngx-http-finalize-connection
[28]: #ngx-http-termniate-request
[29]: #ngx-http-free-request
[30]: #ngx-http-close-request
[31]: #ngx-http-finalize-request
[32]: #ngx-event-t-handler
[33]: #ngx-request-t-read-event-handler-write-event-handler
[34]: #id8
[35]: #request-body
[36]: #id9
[37]: https://github.com/shrimp/no-pool-nginx
[38]: #id10
[39]: #id11
[40]: #id12
[41]: #id13
[42]: #id14
[43]: #id15
[44]: #id16
[45]: #id17
[46]: #id18
[47]: #id19
[48]: #id20
[49]: #id21
[50]: #id22
[51]: #id23
[52]: #id24
[53]: #id25
[54]: #id26
[55]: #id27
[56]: #id28
[57]: #id29
[58]: #id30
[59]: #id31
[60]: #id32
[61]: #id33
[62]: #id34
[63]: #id35
[64]: #id36
[65]: #id37
[66]: #id38
[67]: #id39
[68]: #id40
[69]: #id41
[70]: #id42
[71]: #id43
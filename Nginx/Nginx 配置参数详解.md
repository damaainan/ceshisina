## Nginx 配置参数详解

来源：[http://www.leyafo.com/post/2018-07-16-nginx-configuration/](http://www.leyafo.com/post/2018-07-16-nginx-configuration/)

时间 2018-07-16 10:17:28


Nginx 的配置很多，最常见的配置就是做一个反向代理服务器。一般作为新手配置 Nginx 最常见的做法是去网上搜索关于 反向代理的配置，实际上里面配置的项并不是特别清楚。本文的作用就是详细介绍这些配置项的作用，可以让你在配置的时候不再依靠搜索引擎去找具体配置项的含义，最好能做看一眼 nginx 配置文件就能知道问题在那里。


## Nginx 进程关系

Nginx 使用 **`Master-Worker` 多进程模式**来进行并发处理网络连接。其中 **worker 进程处理具体的事物**，**master 进程负责监督 worker 进程的工作状态**。worker 进程之间通过共享内存，原子操作等一些进程间通信机制来实现负载均衡。

这中多进程的方式有如下好处：
1. Master 进程可以监控 Worker 进程，并在其 crash 的时候重启。
2. Master 可以在运行时重载配置文件，可以做到平滑升级。
3. Worker 数量对应 CPU 数可以减少内核切换，没有同步锁的限制。


### 进程相关的块配置

Nginx 块配置由一对大括号组成，每个块都有它对应支持的变量，以下是进程相关的块配置选项及说明。

| 配置项 | 值(value) | 说明 | 类别 |
|-|-|-|-|
| daemon | on/off | 是否以 daemon 模式运行 | 调试 |
| master_process | on/off | 是否以 master／worker 多进程的方式运行 | 调试 |
| error_log | pathfile level((从小到大依次是 debug、info、notice、warn、error、crit、alert、emerg)) | log 的路径和级别 | 调试 |
| debug_connection((使用前，需确保在编译执行configure时已经加入了–with-debug参数，否则不会生效。)) | IP/CIDR((10.224.57.0/24)) | 仅对指定的客户端输出debug级别的日志 | 调试 |
| worker_rlimit_core | size | 限制 core dump 的大小 | 调试 |
| working_directory | path | 设置 worker 进程的工作目录(它唯一的作用就是设置  dump 文件路径) | 调试 |
| env VAR | VAR=VALUE | 设置操作系统环境变量 | 运行 |
| include | pathfile | 嵌入其他配置文件 | 运行 |
| pid | pathfile | 设置pid文件目录 | 运行 |
| user | username[groupname]; (默认 user nobody nobody) | 设置work进程运行的用户 | 运行 |
| worker_rlimit_nofile | limit | 指定work进程可打开的最大文件句柄数 | 运行 |
| worker_rlimit_sigpending | limit | 限制信号队列的大小 | 运行 |
| worker_processes | number | 设置 worker进程数量(建议按 CPU 内核来) | 优化 |
| worker_cpu_affinity | cpumask[cpumask…] | 绑定 worker 进程到指定的 CPU 内核（确保每个 worker 独占一个 CPU） | 优化 |
| ssl_engine | device | 绑定SSL硬件加速设备 | 优化 |
| timer_resolution | t | 系统调用 gettimeofday 的执行频率（虽然是系统调用，但代价并不高，不要太在意。） | 优化 |
| worker_priority | nice(–20(最高)~+19(最低)) | 配置 worker 进程的优先级（越高进程获得的时间片就越大） | 优化 |
| accept_mutex | on/off | 配置 accept 事件锁（各 worker 之间的负载均衡就靠它来调度，不建议关闭） | 事件 |
| lock文件路径 | pathfile | accept锁需要这个locker文件（操作系统如果有原子锁，这个配置就没意义。） | 事件 |
| accept_mutex_delay | Nms | 使用accept锁后到真正建立连接之间的延迟时间（如果有一个 worker 进程试图取 accept 锁而没有取到，它至少要等 accept_mutex_delay 定义的时间间隔后才能再次试图取锁。） | 事件 |
| multi_accept | on/off | 批量建立新连接（当事件模型通知有新连接时，尽可能地对本次调度中客户端发起的所有TCP请求都建立连接。） | 事件 |
| use | kqueue/rtsig/epoll/ /dev/poll /select/poll/eventport | 设置事件模型（nginx 会自动选择合适的事件模型） | 事件 |
| worker_connections | number | 定义每个事件的最大连接数 | 事件 |
  


## HTTP 配置

所有的 HTTP 配置项都必须直属于 `http` 块、`server` 块、`location` 块、`upstream` 块或 `if` 块等。由于 IP 地址数量的问题，HTTP 指向的主机就被叫成被虚拟主机，一个 nginx 可以通过 server_name 指向不同的主机 。


### 端口监听

listenaddress:port [default(deprecated in 0.8.21)|default_server| backlog=num|rcvbuf=size|sndbuf=size|accept_filter=filter|deferred|bind|ipv6only=[on|off]|ssl]];如果不加以上是 listen 配置项所带的选项及配置，下面是各选项详解。 （listen 端口如果不指定，默认监听 80。）


default和 default_server 将所在的块设为默认的 web server 处理的块，当一个请求无法匹配到对应的主机处理时，就会用默认的块。

backlog=num 表示 TCP 中 backlog 队列的大小，默认为 -1 表示不设置。如果队列已满，新进来的 TCP 连接将不会处理。

rcvbuf = size 设置监听句柄的 SO_RCVBUF 参数。

sndbuf = size 设置监听句柄的 SO_SNDBUF 参数。

accept_filter 设置 accept 过滤器，只对 FreeBSD 操作系统有用。

deferred：在设置该参数后，若用户发起建立连接请求，并且完成了 TCP 的三次握手，内核也不会为了这次的连接调度 worker 进程来处理，只有用户真的发送请求数据时内核才会唤醒worker进程处理这个连接。

server_name=name在开始处理一个 HTTP 请求时，Nginx 会取出 header 头中的 Host，与每个 server 中的 server_name 进行匹配，以此决定到底由哪一个 server 块来处理这个请求。


server_name与 Host 的匹配优先级如下：

- 首先选择所有字符串完全匹配的 server_name，如 www.testweb.com 。
    - 其次选择通配符在前面的  server_name，如 .testweb.com。
    - 再次选择通配符在后面的 server_name，如 www.testweb. 。
    - 最后选择使用正则表达式才匹配的 server_name，如 ~^.testweb.com$。

  
`server_names_hash_bucket_sizesize;` 为了快速找到 server name, nginx 使用散列桶来快速匹配，这里设置每个桶的大小。

`server_names_hash_max_sizeszie` 会影响散列表的冲突率。server_names_hash_max_size 越大，消耗的内存就越多，但散列 key 的冲突率则会降低，检索速度也更快。

 **`server_name_in_redirect`** on|off; 配合 server_name 使用。在使用 on 打开时，表示在重定向请求时会使用 server_name 里配置的第一个主机名代替原先请求中的 Host 头部，而使用 off 关闭时，表示在重定向请求时使用请求本身的 Host 头部。

  
### location 配置


* `=` 表示把 URI 作为字符串，以便与参数中的 uri 做完全匹配。
* `~` 表示匹配 URI 时是字母大小写敏感的。
* `~*` 表示匹配 URI 时忽略字母大小写问题。
* `^~` 表示匹配 URI 时只需要其前半部分与 uri 参数匹配即可。 例如 `^~ images`。
* `@` 表示仅用于 Nginx 服务内部请求之间的重定向，带有 `@` 的 location 不直接处理用户请求。
* 还可以使用正则表达式，例如： `.(gif|jpg|jpeg)$`
  

在以上各种匹配方式中，都只能表达为 “if then”，没有 unless then。如果要使用 unless then 可以定义 location /，表示如果以上都不匹配就到这里面来。


### 文件路径定义

**`root path`** 位于 `http` `sever` `location` `if` 块中。  以 `root` 方式定义资源 HTTP 请求的根目录。

**`http path`** 的相对路径就是以 `root` 为准的。 

**`alias path`** 位于配置块 `location`中，如果有一个文件的请求 URI 是 `/conf/nginx.conf`  实际访问的文件在 `/usr/local/nginx/conf/nginx.conf`  则可以采用如下方式：

```nginx
location conf{
   alias usr/local/nginx/conf/;
} 
```
**`index file`** URI 是 / ，返回网站的首页。如：index index.html html/index.php /index.php；接收到请求后会依次从左到右进行匹配。

**`error_page`**根据具体的错误码匹配到具体的页面。如：error_page 404 404.html。也可以像下面一样转发到上游的服务。
```nginx
  location / (
    error_page 404 @fallback;
  )
  location @fallback (
    proxy_pass    http://backend;
  )
```
另外还有一个选项就是是否允许递归使用 `error_page recursive_error_pages on\off`。

**`try_files`**后面要跟若干路径，最后必须要有 uri 参数。它的意义是尝试按照顺序访问每一个 path，如果可以有效读取就直接向用户返回这个 path 对应的文件结束请求，否则继续向下访问。示例：

```nginx
try_files system/maintenance.html \$uri/index.html \$uri.html @other;
location @other {
    proxy_pass   http://backend
;
  }
```
上面表示如果前面的路径都找不到就会反向代理到 http://backend 上面。


### 内存及磁盘资源的分配

`client_body_in_file_onlyon/clean/off;`
on 表示 HTTP body 存储到磁盘上，off 表示不存，clean 表示请求结束后删除磁盘文件。（这个选项一般用于调试）。

`client_body_in_single_bufferon/off`（http，server，location块）;

`client_header_buffer_sizesize;` (http，server块)
用户请求的包一律存到内存 buffer 中，如果超过 size 就存到磁盘里。

`client_body_temp_pathdir-path[level1[level2[level3]]]`
设置包体临时存放目录，为防止文件数量太多导致性能下降，额外加了 3 层目录结构。 **`connection_pool_size`** 对于每个连接会预先分配一个内存池，如果大了会非常占内存，小了会引发更多的内存分配次数。 **`request_pool_size`** Nginx 开始处理 HTTP 请求时，将会为每个请求都分配一个内存池，size 配置项将指定这个内存池的初始大小。

  
### 网络连接的设置


**以下配置所在的块都是 http、server、location。 ** 

**`client_header_timeout`** time(默认单位：秒)。
客户端与服务端建立连接后将开始接受 HTTP 头，如果超过这个时间就返回 408。

`client_body_timeouttime`（默认单位：秒）
读取 HTTP body 的超时时间。

`send_timeouttime`（默认单位：秒）
发送响应的超时时间。

`reset_timeout_connectionon/off`
连接超时后通过向客户端发送 RST 包来直接重置连接，这个选项打开后 nginx 会在某个连接超时后，不是使用正常情况下的四次分手关闭连接，而是直接向用户发送 RST 重置包。相比正常的关闭方式，它使得服务器避免产生许多处于FIN_WAIT_1、FIN_WAIT_2、TIME_WAIT状态的TCP连接。 

**`lingering_close`** off/on/always
控制 Nginx 关闭用户连接的方式。always 表示关闭用户连接前必须无条件地处理连接上所有用户发送的数据。off 表示关闭连接时完全不管连接上是否已经有准备就绪的来自用户的数据。on 是中间值，一般情况下在关闭连接前都会处理连接上的用户发送的数据，除了有些情况下在业务上认定这之后的数据是不必要的。 

**`lingering_time`** time（默认单位：秒）
Content-Length 大于 max_client_body_size 配置时，Nginx 服务会立刻向用户发送 413（Requestentity too large）响应。经过了 lingering_time 时间后，nginx 不管用户是否仍在上传都会关闭连接。 

**`lingering_timeout`** time（默认单位：秒）
在关闭连接前，会检测是否有用户发送的数据到达服务器，如果超过 lingering_timeout 时间后还没有数据可读，就直接关闭连接。否则，必须读完缓冲区上的数据病丢弃掉才关闭。 

**`keepalive_disable`** [msie6|safari|none]
对特定浏览器禁用 keepalive。 

**`keepalive_timeout`** time（默认单位：秒）
一个连接在闲置超过一定的时间后，服务器和浏览器都会关闭这个连接。 

**`keepalive_requests`** number;
keepalive 长连接允许承载的请求最大数。 

**`tcp_nodelay`** on/off;
确定对 keepalive 连接是否使用 TCP_NODELAY 选项。

**`tcp_nopush`** on/off;
确定是否开启 FreeBSD 系统上的 TCP_NOPUSH 或 Linux 系统上的 TCP_CORK 功能。

  
#### MIME 类型设置


type{…};

定义文件到扩展名的映射。如：
```
types {
    text/html html;
    text/html conf;
    image/gif gif;
    image/jpeg jpg;
  }
```
  
`default_typeMIME-type;`
当找不到对应的 MIME 使用这个配置里的 MIME-type;

`types_hash_bucket_size`

**`types_hash_max_size`** 为快速匹配 MIME nginx 使用散列表来存储 MIME type 与文件扩展名。max_size 为最大大小设置。


#### 对客户端请求的限制

`limit_exceptmethod`
限制客户端指定的 http method。

`client_max_body_sizesize;`
client body 允许的最大值。

`limit_ratespeed;`
对客户端请求进行限速。

`limit_rate_aftersize`
当请求超过一定的长度，limit_rate 才生效。

  
#### 文件操作


`sendfileon/off;`
启用 linux 上的 sendfile 来发送文件，减少内核与用户态直接的交互。

`aioon/off`
启用内核级别的异步文件 I/O 功能，与 sendfile 互斥。
  
`directiosize/off`
使用 O_DIRECT 选项去读取文件，通常对大文件有优化作用，与 sendfile 功能互斥。
  
`directio_alignment`
与 directio 配合使用，指定以 directio 方式读取文件时的对齐方式.一般情况下，512B 已经足够了。
  
`open_file_cachemax=N[inactive=time]|off;`
打开文件缓存，减少对磁盘操作。max：表示在内存中存储元素的最大个数。inactive：表示在指定的时间段内没有被访问过的元素将会被淘汰。off 表示关闭缓存功能。

`open_file_cache_errorson/off;`
是否缓存打开文件错误的信息。

`open_file_cache_min_usesnumber`
不被淘汰的最小访问次数。与 open_file_cache 中的 inactive 参数配合使用。

`open_file_cache_validtime`
检验缓存中元素的有效性，默认为 60s。

#### 对客户端请求的特殊处理

`ignore_invalid_headerson/off;`
忽略不合法的 HTTP 头。

`underscores_in_headerson/off;`
HTTP 头是否允许有下划线。

`if_modified_sinceoff/exact/before]`
对 if_modified_since 进行处理。参数详解如下： **`off`** ：表示忽略用户请求中的If-Modified-Since头部。

exact：将If-Modified-Since头部包含的时间与将要返回的文件上次修改的时间做精确比较，如果没有匹配上，则返回200和文件的实际内容，如果匹配上，则表示浏览器缓存的文件内容已经是最新的了，没有必要再返回文件从而浪费时间与带宽了，这时会返回304 Not Modified，浏览器收到后会直接读取自己的本地缓存。

before：是比exact更宽松的比较。只要文件的上次修改时间等于或者早于用户请求中的If-Modified-Since头部的时间，就会向客户端返回304 Not Modified。

  
`log_not_foundon／off`
文件未找到是否记录到 error 日志。

  
`merge_slasheson／off`
是否合并相邻的斜杠。

  
resolveraddress； 

**`resolver_timeout`** time;
设置 DNS 解析服务器的地址以及 DNS 解析超时时间。

`server_tokenson/off;`
返回错误页是否注明 **`nginx`** 版本。


### Upstream（反向代理）


upstream 定义了一个上游服务器集群，便于反向代理中的 `proxy_pass` 使用。

servername[parameters]

server 配置项指定了一台上游服务器的名字，这个名字可以是域名，IP 地址端口，UNIX 句柄等，在其后还可以跟下列参数：
 `weight = number ;`  设置这台上游服务器的转发权重，默认为1。  

 `max_fails=number;` 
与 `fails_timout` 配合使用，在 `fail_timeout` 时间段内，如果向上游服务器转发失败的次数超过 number，则认为台上游服务器不可用。默认为 1，为 0 表示不检查失败次数。

`fail_timeout=time; `
表示该段时间内转发失败多少次后就认为上游服务器不可用，默认为 10 秒。  

`down;` 
表示所在的上游服务器永久下线，只在使用 `ip_hash` 配置项时才有用。  

`backup`
表示所在的上游服务器只是备份服务器，只有在所有非备份服务器都失效后，才会向所在的上游服务器转发请求。

 `ip_hash` 把客户端 IP 地址计算出一个 key，将 key 按照 upstream 集群里的上游服务器数量进行取模，然后以取模的结果把请求转发到相应的上游服务器。主要解决一个客户端 IP 地址始终往一个上游服务器上发。它与 weight 配置不能同时使用。


#### upstream 日志支持的变量：

`$upstream_addr`  处理请求的上游服务器地址。

`$upstream_cache_status`  表示是否命中缓存，取值范围：MISS, EXPIRED, UPDATING, STATE, HIT

`$upstream_status`  上游服务器返回的相应码。

`$upstream_response_time`  上游服务器的相应时间，精确到毫秒。

`$upstream_http_HEADER`   HTTP 的头部。

  
默认情况下反向代理是不会转发请求中的 Host 头部，如果需要必须加上：

proxy_set_headerHost $host;

`proxy_methodmethod`
表示转发时的 HTTP method。假如设置为 POST，那么客户端所有过来的请求都会变为 POST。
  
`proxy_hide_headerthe_header`
Nginx会将上游服务器的响应转发给客户端，但默认不会转发以下HTTP头部字段：Date、Server、X-Pad 和 X-Accel-*。使用 proxy_hide_header 后可以任意地指定哪些 HTTP 头部字段不能被转发。

`proxy_pass_headerheader`
与 proxy_hide_header 功能相反，proxy_pass_header 会将原来禁止转发的头设置为允许转发。

`proxy_pass_request_bodyon/off;`
确定是否向上游服务器发送 HTTP body 部分。

`proxy_pass_request_headerson/of`
确定是否转发HTTP头部。
  
`proxy_redirect[default|off|redirect replacement];`
当上游服务器返回的响应是重定向或刷新请求时，`proxy_redirect` 可以重设 HTTP 头部的 `location` 或 `refresh` 字段。其中 default 表示按照 `proxy_pass` 配置项和所属的 `location` 配置项重组发往客户端 `location` 头部。off 表示不变。

`proxy_next_upstream[error|timeout|invalid_header|http_500|http_502|http_503|http_504|http_404|off];`
向一台上游服务器转发请求出现错误时，继续换一台上游服务器处理这个请求。这个配置来说明在哪些情况下会继续选择下一台上游服务器转发请求。


`error`：当向上游服务器发起连接、发送请求、读取响应时出错。

`timeout`：发送请求或读取响应时发生超时。

`invalid_header`：上游服务器发送的响应是不合法的。

`http_5xx`： 上游服务器返回的 HTTP 响应码是 5xx。

`off`：关闭 proxy_next_upstream 功能—出错就选择另一台上游服务器再次转发。


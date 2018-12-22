## 【Nginx源码分析】Nginx中http2浅析

来源：[https://segmentfault.com/a/1190000017342023](https://segmentfault.com/a/1190000017342023)

运营研发  张仕华

本文通过一个小例子串一遍nginx处理http2的流程。主要涉及到http2的协议以及nginx的处理流程。
## http2简介

http2比较http1.1主要有如下五个方面的不同：
## 二进制协议

http1.1请求行和请求头部都是纯文本编码,即可以直接按ascii字符解释，而http2是有自己的编码格式。并且nginx中http2必须建立在ssl协议之上。
## 头部压缩

举个例子,HTTP1.1传一个header  `<method: GET>`,需要11个字符.http2中有一个静态索引表，客户端传索引键，例如1，nginx通过查表能知道1代表method: GET.nginx中除了该静态表，还会有一个动态表，保存例如host这种变化的头部
## 多路复用

http1.1一个连接上只能传输一个请求，当一个请求结束之后才能传输下一个请求。所以对http1.1协议的服务发起请求时，一般浏览器会建立6条连接，并行的去请求不同的资源。而http2的二进制协议中有一个frame的概念，每个frame有自己的id,所以一个连接上可以同时多路复用传输多个不同id的frame
## 主动push

http1.1是请求-响应模型，而http2可以主动给客户端推送资源
## 优先级

既然多路复用，所有数据跑在了一条通道上，必然会有优先级的需求

本文的例子主要通过解析报文说明头三个特性
## 配置环境

NGINX配置如下：

```nginx
    server {
        listen 8443 ssl http2;
        access_log  logs/host_server2.access.log  main;
        ssl_certificate /home/xiaoju/nginx-2/nginx-selfsigned.crt;
        ssl_certificate_key /home/xiaoju/nginx-2/nginx-selfsigned.key;
        ssl_ciphers EECDH+CHACHA20:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;

        location / {
            root   html;
            index  index.html index.htm /abc.html;
            access_log  logs/host_location3.access.log  main;
            http2_push /favicon.ico;
            http2_push /nginx.png;
        }
    }
```

客户端按如下方式发起请求:

```
curl  -k  -I   -L https://IP:8443
HTTP/2 200  //可以看到，返回是http/2
server: nginx/1.14.0
date: Tue, 11 Dec 2018 09:20:33 GMT
content-type: text/html
content-length: 664
last-modified: Tue, 11 Dec 2018 04:19:32 GMT
etag: "5c0f3ad4-298"
accept-ranges: bytes
```
## 请求解析
## 客户端请求问题

先思考一个问题，上文配置中使用curl发送请求时,为何直接返回的是http/2,而不是http/1.1(虽然服务端配置了使用http2,但万一客户端未支持http2协议，直接返回http2客户端会解析不了)

因为nginx中http2必须在ssl之上，所以我们首先通过在nginx代码中的ssl握手部分打断点gdb跟一下.

```
(gdb) b ngx_ssl_handshake_handler  //ssl握手函数
Breakpoint 1 at 0x47ddb5: file src/event/ngx_event_openssl.c, line 1373.
(gdb) c
Continuing.
Breakpoint 1, ngx_ssl_handshake_handler (ev=0x16141f0) at src/event/ngx_event_openssl.c:1373
1373    {

1390        c->ssl->handler(c); //实际处理逻辑位于ngx_http_ssl_handshake_handler
(gdb) s
ngx_http_ssl_handshake_handler (c=0x15da400) at src/http/ngx_http_request.c:782
782    {

(gdb) n
805            if (hc->addr_conf->http2) { //配置http2后hc->addr_conf->http2标志位为1

(gdb) n
808                SSL_get0_alpn_selected(c->ssl->connection, &data, &len);//从ssl协议中取出alpn


(gdb) n
820                if (len == 2 && data[0] == 'h' && data[1] == '2') { //如果为h2,说明客户端支持升级到http2协议

(gdb) n
821                    ngx_http_v2_init(c->read);//开始进入http2的初始化阶段
```

简单说就是通过ssl协议握手阶段获取一个alpn相关的配置，如果是h2，就进入http2的处理流程。我们通过wireshark抓包可以更直观的看出这个流程

![][0]

如上图，在ssl握手中的Client Hello 阶段有一个协议扩展alpn
## http2报文格式

http2 以一个preface开头，接着是一个个的frame,其中每个frame都有一个header,如下：

![][1]

其中length代表frame内容的长度,type表明frame的类型,flag给frame做一些特殊的标记,sid代表的就是frame的id.

其中 frame有如下10种类型

```c
#define NGX_HTTP_V2_DATA_FRAME           0x0 //body数据
#define NGX_HTTP_V2_HEADERS_FRAME        0x1 //header数据
#define NGX_HTTP_V2_PRIORITY_FRAME       0x2 //优先级设置
#define NGX_HTTP_V2_RST_STREAM_FRAME     0x3 //重置一个stream
#define NGX_HTTP_V2_SETTINGS_FRAME       0x4 //其他设置项，例如是否开启push,同时能够处理的stream数量等
#define NGX_HTTP_V2_PUSH_PROMISE_FRAME   0x5 //push
#define NGX_HTTP_V2_PING_FRAME           0x6 //ping
#define NGX_HTTP_V2_GOAWAY_FRAME         0x7 //goaway.发送此frame后会重新建立连接
#define NGX_HTTP_V2_WINDOW_UPDATE_FRAME  0x8 //窗口更新 流控使用
#define NGX_HTTP_V2_CONTINUATION_FRAME   0x9 //当一个frame发送不完数据时，可以按continuation格式继续发送
```

frame ID在客户端按奇数递增，例如1，3，5，偶数型id留给服务端推送push时使用，设置连接属性相关的frame id都为0

flags有如下定义：

```c
#define NGX_HTTP_V2_NO_FLAG              0x00 //未设置
#define NGX_HTTP_V2_ACK_FLAG             0x01 //ack flag
#define NGX_HTTP_V2_END_STREAM_FLAG      0x01 //结束stream
#define NGX_HTTP_V2_END_HEADERS_FLAG     0x04 //结束headers
#define NGX_HTTP_V2_PADDED_FLAG          0x08 //填充flag
#define NGX_HTTP_V2_PRIORITY_FLAG        0x20 //优先级设置flag
```

如下是一个http头类型frame具体的内容格式：

![][2]

padded和priority由上文头部的flag决定是否有这两字段。接下来占8bit的flag决定header是否需要索引，如果需要，索引号是多少。

huff(1)表明该字段是否使用了huffman编码。header_value_len(7)和header_value是具体头字段的value值

如下是一个设置相关的frame

![][3]

如下是一个窗口更新的frame

![][4]

下边我们看一个具体的例子，来更直观的了解下。
## http2报文解析

新版本的curl有一个–http2参数，可以直接指明使用http2进行通讯。我们将客户端命令修改如下：

```
curl --http2 -k  -I   -L https://10.96.79.14:8443
```

通过上边的gdb跟踪，我们看到http2初始化入口函数为ngx_http_v2_init，直接在此处打断点，继续跟踪代码.跟踪过程不再详细描述，当把报文读取进缓存之后，我们直接在gdb中bt查看调用路径，如下：

```
#0  ngx_http_v2_state_preface (h2c=0x15a9310, pos=0x164b0b0 "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n", end=0x164b11e "")
    at src/http/v2/ngx_http_v2.c:713
#1  0x00000000004bca20 in ngx_http_v2_read_handler (rev=0x16141f0) at src/http/v2/ngx_http_v2.c:415
#2  0x00000000004bcf8a in ngx_http_v2_init (rev=0x16141f0) at src/http/v2/ngx_http_v2.c:328
#3  0x0000000000490a13 in ngx_http_ssl_handshake_handler (c=0x15da400) at src/http/ngx_http_request.c:821
#4  0x000000000047de24 in ngx_ssl_handshake_handler (ev=0x16141f0) at src/event/ngx_event_openssl.c:1390
#5  0x0000000000479637 in ngx_epoll_process_events (cycle=0x1597e30, timer=<optimized out>, flags=<optimized out>)
    at src/event/modules/ngx_epoll_module.c:902
#6  0x000000000046f9db in ngx_process_events_and_timers (cycle=0x1597e30) at src/event/ngx_event.c:242
#7  0x000000000047761c in ngx_worker_process_cycle (cycle=0x1597e30, data=<optimized out>) at src/os/unix/ngx_process_cycle.c:750
#8  0x0000000000475c50 in ngx_spawn_process (cycle=0x1597e30, proc=0x477589 <ngx_worker_process_cycle>, data=0x0,
    name=0x684922 "worker process", respawn=-3) at src/os/unix/ngx_process.c:199
#9  0x00000000004769aa in ngx_start_worker_processes (cycle=0x1597e30, n=1, type=-3) at src/os/unix/ngx_process_cycle.c:359
#10 0x0000000000477cb0 in ngx_master_process_cycle (cycle=0x1597e30) at src/os/unix/ngx_process_cycle.c:131
#11 0x0000000000450ea4 in main (argc=<optimized out>, argv=<optimized out>) at src/core/nginx.c:382
```

调用到ngx_http_v2_state_preface这个函数之后，开始处理http2请求，我们将请求内容打印出来看一下：

```
(gdb) p end-pos
$1 = 110
(gdb) p *pos@110
$2 = "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n\000\000\022\004\000\000\000\000\000\000\003\000\000\000d\000\004@\000\000\000\000\002\000\000\000\000\000\000\004\b\000\000\000\000\000?\377\000\001\000\000%\001\005\000\000\000\001B\004HEAD\204\207A\214\b\027}\305\335}p\265q\346\232gz\210%\266Pë\266\322\340S\003*/*"
```

nginx接下来开始处理http2请求，处理方法可以按上述方法继续跟踪，我们直接按http2协议将上述报文解析一下，如下所示：

注意gdb打印出来的是八进制格式

![][5]

![][6]
## http push抓包

注意上文nginx配置中配置了两条http2_push指令，即服务端会在请求index.html时主动将favicon.ico和nginx.png两个图片push下去。

wireshark中抓包如下：

![][7]

服务端首先发送一个push_promise报文，报文中会包括push的文件路径和frame id.第二个和第三个红框即开始push具体的信息,frame id分别为2和4

我们从浏览器端看一下push的请求：

![][8]

不主动push请求如下：

![][9]

浏览器必须首先将index.html加载之后才会知道接着去请求哪些资源，于是favicon.ico和nginx.png就会延迟加载。
## 问题

HTTP2如果在服务端动态索引header，会使http变成有状态的服务，集群之间如何解决header头缓存的问题？
静态资源文件首次请求后会在浏览器端缓存，push如何保证只推送一次(即只有首次请求时才push)?
参考资料
1.[https://www.nginx.com/blog/ht...][10]

2.[https://httpwg.org/specs/rfc7540][11]

[10]: https://www.nginx.com/blog/http2-theory-and-practice-in-nginx-stable-13/
[11]: https://httpwg.org/specs/rfc7540
[0]: ./img/bVbkVA7.png
[1]: ./img/bVbkVBn.png
[2]: ./img/bVbkVBw.png
[3]: ./img/bVbkVBy.png
[4]: ./img/bVbkVBB.png
[5]: ./img/bVbkVBN.png
[6]: ./img/bVbkVBO.png
[7]: ./img/bVbkVBQ.png
[8]: ./img/bVbkVBR.png
[9]: ./img/bVbkVBS.png
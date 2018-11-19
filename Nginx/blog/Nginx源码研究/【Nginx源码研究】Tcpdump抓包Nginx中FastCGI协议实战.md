## 【Nginx源码研究】Tcpdump抓包Nginx中FastCGI协议实战

来源：[https://segmentfault.com/a/1190000016901718](https://segmentfault.com/a/1190000016901718)

运营研发团队  方波 施洪宝
## 一. FastCGI协议简介
## 1.1 简介

FastCGI(Fast Common Gateway Interface, 快速通用网关接口)是一种通信协议。可以通过Unix Domain Socket, Named Pipe, TCP等方式按照FastCGI协议通信。

![][0]

图 1.1 FastCGI简介
## 1.2 数据包格式

FastCGI数据包两部分, 头部(header), 包体(body), 每个数据包都必须包含header, body可以没有。header为8个字节, body必须为8的整数倍, 不是的话需要填充。
### 1.2.1 头部

```LANG
typedef struct {
    unsigned char version;            // 版本号
    unsigned char type;               // 数据包类型
    unsigned char requestIdB1;        // 记录id高8位
    unsigned char requestIdB0;        // 记录id低8位
    unsigned char contentLengthB1;    // 记录内容长度高8位(body长度高8位)
    unsigned char contentLengthB0;    // 记录内容长度低8位(body长度低8位)
    unsigned char paddingLength;      // 补齐位长度(body补齐长度)
    unsigned char reserved;           // 补齐位
}Header;
```

![][1]

图 1.2 FastCGI协议数据包头部字段说明

type的取值

```LANG
#define FCGI_BEGIN_REQUEST       1                     //(web->fastcgi)请求开始数据包
#define FCGI_ABORT_REQUEST       2                     //(web->fastcgi)终止请求
#define FCGI_END_REQUEST         3                     //(fastcgi->web)请求结束
#define FCGI_PARAMS              4                     //(web->fastcgi)传递参数
#define FCGI_STDIN               5                     //(web->fastcgi)数据流传输数据
#define FCGI_STDOUT              6                     //(fastcgi->web)数据流传输数据
#define FCGI_STDERR              7                     //(fastcgi->web)数据流传输
#define FCGI_DATA                8                     //(web->fastcgi)数据流传输
#define FCGI_GET_VALUES          9                     //(web->fastcgi)查询fastcgi服务器性能参数
#define FCGI_GET_VALUES_RESULT  10                     //(fastcgi->web)fastcgi性能参数查询返回
#define FCGI_UNKNOWN_TYPE       11
#define FCGI_MAXTYPE (FCGI_UNKNOWN_TYPE)
```
### 1.2.2 params类型数据包

![][2]

图 1.3 Params数据包

说明:

* 1.params数据包以key, value格式发送, 具体格式为(keyLen, valLen, key, val)
* 2.key或者val长度大于127时，会用4个字节存储长度，否则用一个字节


### 1.2.3 数据流类型数据包(stdin, stdout, stderr, data)

![][3]

图 1.4 数据流类型数据包
## 1.3 通信流程示例

![][4]

图 1.5 FastCGI简单通信流程

说明:

* begin request 代表请求开始, end request 代表请求结束。
* 除begin request, end request类型数据包外, 其他类型数据包在发送完成后，需要发送一个只有头部，包体长度为0, 也就是没有包体的数据包，代表这种类型的数据包发送结束。


## 1.4 参考

[https://segmentfault.com/a/11...][12]
[https://blog.csdn.net/hepangd...][13]
## 二. Nginx FastCGI

nginx发送的缓冲区数据格式如下:

![][5]

图 2.1 FastCGI数据包总体结构图

说明:

* 本部分主要依据ngx_http_fastcgi_create_request函数, 该函数会构造缓存区，并向其中写入上图所示内容，上述没有考虑HTTP请求含有body的情况。
* ngx_http_fastcgi_create_request 函数所需的变量, 在进入该函数之前认为已经初始化完成。


## 2.1 基础
### 2.1.1 le.ip结构图

fastcgi le.ip

![][6]

图 2.2 le.ip 结构图
### 2.1.2 e.ip结构图

![][7]

图2.3 e.ip结构图
## 2.2 ngx_http_fastcgi_create_request

* 该函数主要依据图2.1， 将所需数据写入到ngx_http_request_t对应的ngx_http_upstream_t的缓冲区中。
* 写入key, val时, 通过调用相应函数实现, 该函数是与对应的key, val放置在一起的, 如图2.3所示。


```LANG
//params数据包写入核心代码
while (*(uintptr_t *) le.ip) {
    lcode = *(ngx_http_script_len_code_pt *) le.ip;
    key_len = (u_char) lcode(&le); //获取key的长度
    lcode = *(ngx_http_script_len_code_pt *) le.ip;
    skip_empty = lcode(&le);      //查看空时是否跳过
    for (val_len = 0; *(uintptr_t *) le.ip; val_len += lcode(&le)) {
        lcode = *(ngx_http_script_len_code_pt *) le.ip;
    }
    le.ip += sizeof(uintptr_t);        //当前key:value结束, le.ip后移1位
    if (skip_empty && val_len == 0) { //value为空, 并且设置空时跳过该key
        e.skip = 1;
        while (*(uintptr_t *) e.ip) {
            code = *(ngx_http_script_code_pt *) e.ip;
            code((ngx_http_script_engine_t *) &e);
        }
        e.ip += sizeof(uintptr_t);
        e.skip = 0;
        continue;
    }
    *e.pos++ = (u_char) key_len; //写入key len 
    if (val_len > 127) {         //写入value len
        *e.pos++ = (u_char) (((val_len >> 24) & 0x7f) | 0x80);
        *e.pos++ = (u_char) ((val_len >> 16) & 0xff);
        *e.pos++ = (u_char) ((val_len >> 8) & 0xff);
        *e.pos++ = (u_char) (val_len & 0xff);
    } else {
        *e.pos++ = (u_char) val_len;
    }
    while (*(uintptr_t *) e.ip) {
        code = *(ngx_http_script_code_pt *) e.ip;
        code((ngx_http_script_engine_t *) &e);  //调用code存储的处理函数, 负责将key, value内容写入缓存, 并将e.ip后移
    }
    e.ip += sizeof(uintptr_t);                  //当前Key:Value结束, 跳过NULL, e.ip后移一位
    ngx_log_debug4(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                   "fastcgi param: \"%*s: %*s\"",
                   key_len, e.pos - (key_len + val_len),
                   val_len, e.pos - val_len);
}
```
## 三. 抓包分析
## 3.1 TCP三次握手

```LANG
14:50:02.836252 IP bogon.46288 > localhost.cslistener: Flags [S], seq 304127093, win 29200, options [mss 1460,sackOK,TS val 105743206 ecr 0,nop,wscale 7], length 0
14:50:02.874743 IP localhost.cslistener > bogon.46288: Flags [S.], seq 15154, ack 304127094, win 32768, options [mss 1460], length 0
14:50:02.874804 IP bogon.46288 > localhost.cslistener: Flags [.], ack 15155, win 29200, length 0
```

代码3.1 tcpdump三次握手

![][8]

图3.1 三次握手链接过程

Client发送请求包seq 304127093，Server返回确认数据包ack=seq(client)+1，同时返回Server自己的seq 15154，Client收到后发送确认包ack=seq(server)+1，建立链接。
Client和Server都有自己的seq，且互不干涉，后续发送的序列号以此为基准。
## 3.2 发送数据

![][9]

3.2 请求数据包图2.1 Client向Server发送请求数据包

* Mac帧头部(14字节)
* IP头部(20字节)
* TCP头部(20字节)
* FastCGI数据包(begin request(8+ 8 = 16) + params(8 + 507 + 5 = 520) + end params(8+ 0 = 8) + stdin(8 + 0 = 8) = 552)


Params 数据包参数整理:
| key len | val len | key | val | |
| - | - | - | - | - |
| 10 | 4 | PRODUCTION | true |
| 15 | 38 | SCRIPT_FILENAME | /home/xiaoju/webroot/default/index.php | |
| 12 | 0 | QUERY_STRING |  | |
| 14 | 3 | REQUEST_METHOD | GET | |
| 12 | 0 | CONTENT_TYPE |  | |
| 14 | 0 | CONTENT_LENGTH |  | |
| 11 | 10 | SCRIPT_NAME | /index.php | |
| 11 | 1 | REQUEST_URI | / | |
| 12 | 10 | DOCUMENT_URI | /index.php | |
| 13 | 28 | DOCUMENT_ROOT | /home/xiaoju/webroot/default | |
| 15 | 8 | SERVER_PROTOCOL | HTTP/1.1 | |
| 17 | 7 | GATEWAY_INTERFACE | CGI/1.1 | |
| 15 | 11 | SERVER_SOFTWARE | nginx/1.6.2 | |
| 11 | 9 | REMOTE_ADDR | 127.0.0.1 | |
| 11 | 5 | REMOTE_PORT | 42282 | |
| 11 | 9 | SERVER_ADDR | 127.0.0.1 | |
| 11 | 4 | SERVER_PORT | 8100 | |
| 11 | 0 | SERVER_NAME |  | |
| 15 | 3 | REDIRECT_STATUS | 200 | |
| 15 | 11 | HTTP_USER_AGENT | curl/7.29.0 | |
| 9 | 14 | HTTP_HOST | localhost:8100 | |
| 11 | 3 | HTTP_ACCEPT | */* | |


说明

* stdin类型数据包长度大于32k时, nginx fastcgi会进行分包, 此时会发送多个stdin类型数据包, 发送完成后再发送stdin结束包(只有包头，没有包体的数据包)。
* wireshark或者tcpdump抓包时, 可能会出现某个数据包长度大于MTU, 主要是由于主机开启TSO功能导致, 具体可以参考[http://wsfdl.com/%E8%B8%A9%E5...][14]

* TSO进行的是TCP分段, 不是IP分片
* 如果抓到的包, IP头部, identified field is 0, 表明tcp不需分段, don't fragment 置位1, 具体参考[http://www.linuxsa.org.au/pip...][15]

* TCP头部可选项含义参考[https://www.jianshu.com/p/39b...][16]



## 3.3 响应包

```LANG
14:50:02.913289 IP localhost.cslistener > bogon.46288: Flags [P.], seq 15155:15395, ack 304127646, win 32216, length 240
    0x0000:  0800 2701 5190 5254 0012 3500 0800 4500  ..'.Q.RT..5...E.
    0x0010:  0118 02bf 0000 ff06 2fb6 0a60 7207 0a00  ......../..`r...
    0x0020:  0204 2328 b4d0 0000 3b33 1220 9e9e 5018  ..#(....;3....P.
    0x0030:  7dd8 df40 0000 0106 0001 00d8 0000 5365  }..@..........Se
    0x0040:  742d 436f 6f6b 6965 3a20 5048 5053 4553  t-Cookie:.PHPSES
    0x0050:  5349 443d 6268 3174 3772 6e61 3233 716c  SID=bh1t7rna23ql
    0x0060:  6d63 6235 6d6a 686d 3967 756f 7631 3b20  mcb5mjhm9guov1;.
    0x0070:  7061 7468 3d2f 0d0a 4578 7069 7265 733a  path=/..Expires:
    0x0080:  2054 6875 2c20 3139 204e 6f76 2031 3938  .Thu,.19.Nov.198
    0x0090:  3120 3038 3a35 323a 3030 2047 4d54 0d0a  1.08:52:00.GMT..
    0x00a0:  4361 6368 652d 436f 6e74 726f 6c3a 206e  Cache-Control:.n
    0x00b0:  6f2d 7374 6f72 652c 206e 6f2d 6361 6368  o-store,.no-cach
    0x00c0:  652c 206d 7573 742d 7265 7661 6c69 6461  e,.must-revalida
    0x00d0:  7465 0d0a 5072 6167 6d61 3a20 6e6f 2d63  te..Pragma:.no-c
    0x00e0:  6163 6865 0d0a 436f 6e74 656e 742d 7479  ache..Content-ty
    0x00f0:  7065 3a20 7465 7874 2f68 746d 6c3b 2063  pe:.text/html;.c
    0x0100:  6861 7273 6574 3d55 5446 2d38 0d0a 0d0a  harset=UTF-8....
    0x0110:  646f 636b 6572 0103 0001 0008 0000 0000  docker..........
    0x0120:  0000 0064 223a                           ...d":
```

代码3.2 Server向Client发送响应数据包

* Mac帧头部(14字节)
* IP头部(20字节)
* TCP头部(20字节)
* FastCGI数据包(begin stdout(8+ 216 = 224) + end request(8+ 8 = 16) = 240)


## 3.4 Client发送接收Server数据的确认包

```LANG
14:50:02.913365 IP bogon.46288 > localhost.cslistener: Flags [.], ack 15395, win 30016, length 0
```

![][10]

图3.3 数据发送
## 3.5 断开链接

```LANG
14:50:02.913629 IP bogon.46288 > localhost.cslistener: Flags [F.], seq 304127646, ack 15395, win 30016, length 0
14:50:02.913767 IP localhost.cslistener > bogon.46288: Flags [.], ack 304127647, win 32215, length 0
14:50:02.951270 IP localhost.cslistener > bogon.46288: Flags [F.], seq 15395, ack 304127647, win 32215, length 0
14:50:02.951452 IP bogon.46288 > localhost.cslistener: Flags [.], ack 15396, win 30016, length 0
```

代码3.3 tcp断开链接

![][11]

图3.4 tcpdump抓包对应的tcp流程图
## 3.6 参考

[https://my.oschina.net/manmao...][17]
[https://segmentfault.com/a/11...][12]

[12]: https://segmentfault.com/a/1190000013112052
[13]: https://blog.csdn.net/hepangda/article/details/81560515
[14]: http://wsfdl.com/%E8%B8%A9%E5%9D%91%E6%9D%82%E8%AE%B0/2016/07/12/tcp_package_large_then_MTU.html
[15]: http://www.linuxsa.org.au/pipermail/linuxsa/2003-May/055084.html
[16]: https://www.jianshu.com/p/39b23068bb0f
[17]: https://my.oschina.net/manmao/blog/654034
[18]: https://segmentfault.com/a/1190000013112052
[0]: ./img/bVbi42U.png
[1]: ./img/bVbhyzy.png
[2]: ./img/bVbhyzC.png
[3]: ./img/bVbhyzK.png
[4]: ./img/bVbhyzO.png
[5]: ./img/bVbi43n.png
[6]: ./img/bVbi43p.png
[7]: ./img/bVbi43u.png
[8]: ./img/bVbhFlm.png
[9]: ./img/bVbi45f.png
[10]: ./img/bVbhFme.png
[11]: ./img/bVbhFmp.png
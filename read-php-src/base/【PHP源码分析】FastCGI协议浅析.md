## 【PHP源码分析】FastCGI协议浅析

来源：[https://segmentfault.com/a/1190000015681368](https://segmentfault.com/a/1190000015681368)

 **`顺风车运营研发团队  陈雷`** 

FastCGI 是一种协议，它是建立在CGI/1.1基础之上的，把CGI/1.1里面的要传递的数据通过FastCGI协议定义的顺序和格式进行传递。为了更好理解PHP-FPM的工作，下面具体阐述一下FastCGI协议的内容。
## **`1. 消息类型`** 

FastCGI协议分为了10种类型，具体定义如下：

```c
typedef enum _fcgi_request_type {

      FCGI_BEGIN_REQUEST  =  1, /* [in] */

      FCGI_ABORT_REQUEST  =  2, /* [in]  (not supported) */

      FCGI_END_REQUEST     =  3, /* [out] */

      FCGI_PARAMS          =  4, /* [in]  environment variables  */

      FCGI_STDIN           =  5, /* [in]  post data   */

      FCGI_STDOUT          =  6, /* [out] response   */

      FCGI_STDERR          =  7, /* [out] errors     */

      FCGI_DATA    =  8, /* [in]  filter data (not supported) */

      FCGI_GET_VALUES      =  9, /* [in]  */

      FCGI_GET_VALUES_RESULT = 10  /* [out] */

} fcgi_request_type;
```

整个FastCGI是二进制连续传递的，定义了一个统一结构的消息头，用来读取每个消息的消息体，方便消息包的切割。一般情况下，最先发送的是FCGI_BEGIN_REQUEST类型的消息，然后是FCGI_PARAMS和FCGI_STDIN类型的消息，当FastCGI响应处理完后，将发送FCGI_STDOUT和FCGI_STDERR类型的消息，最后以FCGI_END_REQUEST表示请求的结束。FCGI_BEGIN_REQUEST和FCGI_END_REQUEST分别表示请求的开始和结束，与整个协议相关。
## **`2. 消息头`** 

对于10种类型的消息，都是以一个消息头开始的，其结构体定义如下：

```c
typedef struct _fcgi_header {

      unsigned char version;

      unsigned char type;

      unsigned char requestIdB1;

      unsigned char requestIdB0;

      unsigned char contentLengthB1;

      unsigned char contentLengthB0;

      unsigned char paddingLength;

      unsigned char reserved;

} fcgi_header;
```

其中，


* version标识FastCGI协议版本
* type 标识FastCGI记录类型
* requestId标识消息所属的FastCGI请求


requestId计算方式如下：

```c
(requestIdB1 << 8) + requestIdB0
```

所以requestId的范围为0~2的16次方-1，也就是0~65535；

contentLength标识消息的contentData组件的字节数，计算方式跟requestId类似，范围同样是0~65535：

```c
(contentLengthB1 << 8) | contentLengthB0
```

paddingLength标识消息的paddingData组件的字节数，范围是0~255；协议通过paddingData提供给发送者填充发送的记录的功能，并且方便接受者通过paddingLength快速的跳过paddingData。填充的目的是允许发送者为更有效地处理保持对齐的数据。如果内容的长度超过65535怎么办呢？答案是可以分成多个消息发送。
## **`3. FCGI_BEGIN_REQUEST`** 

FCGI_BEGIN_REQUEST 的结构体定义如下：

```c
typedef struct _fcgi_begin_request {

      unsigned char roleB1;

      unsigned char roleB0;

      unsigned char flags;

      unsigned char reserved[5];

} fcgi_begin_request;
```

其中role代表的是Web服务器期望应用扮演的角色，计算方式是：

```c
(roleB1 << 8) + roleB0
```

对于PHP7中，处理了三种角色，分别是FCGI_RESPONDER，FCGI_AUTHORIZER       和FCGI_FILTER。

flags & FCGI_KEEP_CONN：如果为0，则在对本次请求响应后关闭链接。如果非0，在对本次请求响应后不会关闭链接。
## **`4. 名-值对`** 

对于，type为FCGI_PARAMS类型，FastCGI协议中提供了名-值对来很好的满足读写可变长度的name和value，格式如下：

```c
nameLength+valueLength+name+value
```

为了节省空间，对于0~127长度的值，Length使用了一个char来表示，第一位为0，对于大于127的长度的值，Length使用了4个char来表示，第一位为1；如图所示：

![][0]

长度计算代码如下：

```c
if (UNEXPECTED(name_len >= 128)) {

      if (UNEXPECTED(p + 3 >= end)) return 0;

      name_len = ((name_len & 0x7f) << 24);

      name_len |= (*p++ << 16);

      name_len |= (*p++ << 8);

      name_len |= *p++;

}
```

这样最长可以表达0~2的31次方的长度。
## **`5. 请求协议`** 

FastCGI协议的定义结构体如下：

```c
    typedef struct _fcgi_begin_request_rec {

      fcgi_header hdr;

      fcgi_begin_request body;

} fcgi_begin_request_rec;
```

分析完FastCGI的协议，我们整体掌握了请求的FastCGI消息的内容，我们通过访问对应的接口，采用gdb抓取其中的内容：

首先我们修改php-fpm.conf的参数，保证只启动一个worker：

```c
pm.max_children = 1
```

然后重新启动php-fpm：

```c
./sbin/php-fpm -y etc/php-fpm.conf
```

然后对worker进行gdb：

```
ps aux | grep php-fpm

root     30014  0.0  0.0 142308  4724 ?        Ss   Nov26   0:03 php-fpm: master process (etc/php-fpm.conf)

chenlei   30015  0.0  0.0 142508  5500 ?        S    Nov26   0:00 php-fpm: pool www

gdb –p 30015

(gdb) b fcgi_read_request
```

然后通过浏览器访问nginx，nginx转发到php-fpm的worker上，根据gdb可以打印出FastCGI消息的内容：

```c
(gdb) b fcgi_read_request
```

对于第一个消息，内容如图：

![][1]

其中type对应的是FCGI_BEGIN_REQUEST，requestid为1，长度为8， 恰好是fcgi_begin_request结构体的大小，内容如图：

![][2]

role对应的是FCGI_RESPONDER。继续往下读，得到的消息内容如图：

![][3]

其中type对应的是FCGI_PARAMS，requestid为1，长度为：

```c
(contentLengthB1 << 8) | contentLengthB0  == 987
```

paddingLength=5，而987+5=992，恰好是8的倍数。根据contentLength+ paddingLength向后读取992长度的字节流，我们打印一下：

```
(gdb) p *p@987

$1 = "\017TSCRIPT_FILENAME/home/xiaoju/webroot/beatles/application/mis/mis/src/index.php/admin/operation/index\f\016QUERY_STRINGactivity_id=89\016\003REQUEST_METHODGET\f\000CONTENT_TYPE\016\000CONTENT_LENGTH\v SCRIPT_NAME/index.php/admin/operation/index\v%REQUEST_URI/admin/operation/index?activity_id=89\f DOCUMENT_URI/index.php/admin/operation/index\r4DOCUMENT_ROOT/home/xiaoju/webroot/beatles/application/mis/mis/src\017\bSERVER_PROTOCOLHTTP/1.1\021\aGATEWAY_INTERFACECGI/1.1\017\vSERVER_SOFTWAREnginx/1.2.5\v\rREMOTE_ADDR172.22.32.131\v\005REMOTE_PORT50973\v\fSERVER_ADDR10.94.98.116\v\004SERVER_PORT8085\v\000SERVER_NAME\017\003REDIRECT_STATUS200\t\021HTTP_HOST10.94.98.116:8085\017\nHTTP_CONNECTIONkeep-alive\017xHTTP_USER_AGENTMozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36\036\001HTTP_UPGRADE_INSECURE_REQUESTS1\vUHTTP_ACCEPTtext/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8\024\rHTTP_ACCEPT_ENCODINGgzip, deflate\024\027HTTP_ACCEPT_cUAGEzh-CN,zh;q=0.9,en;q=0.8"
```

根据上一节我们讲到的名-值对的长度规则，我们可以看出，Fastcgi协议中封装了类似于http协议里面的键值对。读取完毕后，继续跟踪消息，打印可以得出，得到的消息如图所示。

![][4]

其中type对应的是FCGI_PARAMS，requestid为1，长度为0，此时完成了FastCGI协议消息的读取过程。下面说一下处理完请求后返回给nginx的FastCGI协议的消息。
## **`6. 响应协议`** 

在fcgi_finish_request中调用fcgi_flush，fcgi_flush中封装一个FCGI_END_REQUEST消息体，再通过safe_write写入 socket 连接的客户端描述符。

```c
int fcgi_flush(fcgi_request *req, int close)

{

      int len;

 

      close_packet(req);

      len = (int)(req->out_pos - req->out_buf);

 

      if (close) {

               fcgi_end_request_rec *rec = (fcgi_end_request_rec*)(req->out_pos);

                 //创建FCGI_END_REQUEST的头

               fcgi_make_header(&rec->hdr, FCGI_END_REQUEST, req->id, sizeof(fcgi_end_request));

                 //写入appStatus

               rec->body.appStatusB3 = 0;

               rec->body.appStatusB2 = 0;

               rec->body.appStatusB1 = 0;

               rec->body.appStatusB0 = 0;

                 //修改protocolStatus为FCGI_REQUEST_COMPLETE;

               rec->body.protocolStatus = FCGI_REQUEST_COMPLETE;

               len += sizeof(fcgi_end_request_rec);

      }

 

      if (safe_write(req, req->out_buf, len) != len) {

               req->keep = 0;

               req->out_pos = req->out_buf;

               return 0;

      }

 

      req->out_pos = req->out_buf;

      return 1;

}
```

到此我们就完全掌握了FastCGI的协议。

[0]: ./img/bVbdXAi.png
[1]: ./img/bVbdXAr.png
[2]: ./img/bVbdXAt.png
[3]: ./img/bVbdXAw.png
[4]: ./img/bVbdXAT.png
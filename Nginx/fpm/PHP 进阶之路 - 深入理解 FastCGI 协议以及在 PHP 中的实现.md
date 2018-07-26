## PHP 进阶之路 - 深入理解 FastCGI 协议以及在 PHP 中的实现

来源：[https://segmentfault.com/a/1190000009863108](https://segmentfault.com/a/1190000009863108)

在讨论 FastCGI 之前，不得不说传统的 CGI 的工作原理，同时应该大概了解 [CGI 1.1][4] 协议

## 传统 CGI 工作原理分析

客户端访问某个 URL 地址之后，通过 GET/POST/PUT 等方式提交数据，并通过 HTTP 协议向 Web 服务器发出请求，服务器端的 HTTP Daemon（守护进程）将 HTTP 请求里描述的信息通过标准输入 stdin 和环境变量(environment variable)传递给主页指定的 CGI 程序，并启动此应用程序进行处理（包括对数据库的处理），处理结果通过标准输出 stdout 返回给 HTTP Daemon 守护进程，再由 HTTP Daemon 进程通过 HTTP 协议返回给客户端。

上面的这段话理解可能还是比较抽象，下面我们就通过一次GET请求为例进行详细说明。

![][0] 
下面用代码来实现图中表述的功能。Web 服务器启动一个 socket 监听服务，然后在本地执行 CGI 程序。后面有比较详细的代码解读。
## Web 服务器代码

```c
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <string.h>
    
#define SERV_PORT 9003
 
char* str_join(char *str1, char *str2);
char* html_response(char *res, char *buf);
   
int main(void)
{
    int lfd, cfd;
    struct sockaddr_in serv_addr,clin_addr;
    socklen_t clin_len;
    char buf[1024],web_result[1024];
    int len;
    FILE *cin;
  
    if((lfd = socket(AF_INET,SOCK_STREAM,0)) == -1){
        perror("create socket failed");
        exit(1);
    }
      
    memset(&serv_addr, 0, sizeof(serv_addr));
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_addr.s_addr = htonl(INADDR_ANY);
    serv_addr.sin_port = htons(SERV_PORT);
  
    if(bind(lfd, (struct sockaddr *)&serv_addr, sizeof(serv_addr)) == -1)
    {
        perror("bind error");
        exit(1);
    }
  
    if(listen(lfd, 128) == -1)
    {
        perror("listen error");
        exit(1);
    }
     
    signal(SIGCLD,SIG_IGN);
   
    while(1)
    {
        clin_len = sizeof(clin_addr);
        if ((cfd = accept(lfd, (struct sockaddr *)&clin_addr, &clin_len)) == -1)
        {
            perror("接收错误\n");
            continue;
        }
 
        cin = fdopen(cfd, "r");
        setbuf(cin, (char *)0);
        fgets(buf,1024,cin); //读取第一行
        printf("\n%s", buf);
 
        //============================ cgi 环境变量设置演示 ============================
         
        // 例如 "GET /user.cgi?id=1 HTTP/1.1";
 
        char *delim = " ";
        char *p;
        char *method, *filename, *query_string;
        char *query_string_pre = "QUERY_STRING=";
 
        method = strtok(buf,delim);         // GET
        p = strtok(NULL,delim);             // /user.cgi?id=1 
        filename = strtok(p,"?");           // /user.cgi
         
        if (strcmp(filename,"/favicon.ico") == 0)
        {
            continue;
        }
 
        query_string = strtok(NULL,"?");    // id=1
        putenv(str_join(query_string_pre,query_string));
 
        //============================ cgi 环境变量设置演示 ============================
 
        int pid = fork();
  
        if (pid > 0)
        {
            close(cfd);
        }
        else if (pid == 0)
        {
            close(lfd);
            FILE *stream = popen(str_join(".",filename),"r");
            fread(buf,sizeof(char),sizeof(buf),stream);
            html_response(web_result,buf);
            write(cfd,web_result,sizeof(web_result));
            pclose(stream);
            close(cfd);
            exit(0);
        }
        else
        {
            perror("fork error");
            exit(1);
        }
    }
   
    close(lfd);
       
    return 0;
}
 
char* str_join(char *str1, char *str2)
{
    char *result = malloc(strlen(str1)+strlen(str2)+1);
    if (result == NULL) exit (1);
    strcpy(result, str1);
    strcat(result, str2);
   
    return result;
}
 
char* html_response(char *res, char *buf)
{
    char *html_response_template = "HTTP/1.1 200 OK\r\nContent-Type:text/html\r\nContent-Length: %d\r\nServer: mengkang\r\n\r\n%s";
 
    sprintf(res,html_response_template,strlen(buf),buf);
     
    return res;
}
```
#### 如上代码中的重点：


* 66~81行找到CGI程序的相对路径（我们为了简单，直接将其根目录定义为Web程序的当前目录），这样就可以在子进程中执行 CGI 程序了；同时设置环境变量，方便CGI程序运行时读取；

* 94~95行将 CGI 程序的标准输出结果写入 Web 服务器守护进程的缓存中；

* 97行则将包装后的 html 结果写入客户端 socket 描述符，返回给连接Web服务器的客户端。



## CGI 程序(user.c)

```c
#include <stdio.h>
#include <stdlib.h>
// 通过获取的 id 查询用户的信息
int main(void){
 
    //============================ 模拟数据库 ============================
    typedef struct 
    {
        int  id;
        char *username;
        int  age;
    } user;
 
    user users[] = {
        {},
        {
            1,
            "mengkang.zhou",
            18
        }
    };
    //============================ 模拟数据库 ============================
 
 
    char *query_string;
    int id;
 
    query_string = getenv("QUERY_STRING");
     
    if (query_string == NULL)
    {
        printf("没有输入数据");
    } else if (sscanf(query_string,"id=%d",&id) != 1)
    {
        printf("没有输入id");
    } else
    {
        printf("用户信息查询
学号: %d
姓名: %s
年龄: %d",id,users[id].username,users[id].age);
    }
     
    return 0;
}
```

将上面的 CGI 程序编译成`gcc user.c -o user.cgi`，放在上面web程序的同级目录。
代码中的第28行，从环境变量中读取前面在Web服务器守护进程中设置的环境变量，是我们演示的重点。
## FastCGI 工作原理分析

相对于 CGI/1.1 规范在 Web 服务器在本地 fork 一个子进程执行 CGI 程序，填充 CGI 预定义的环境变量，放入系统环境变量，把 HTTP body 体的 content 通过标准输入传入子进程，处理完毕之后通过标准输出返回给 Web 服务器。FastCGI 的核心则是取缔传统的 fork-and-execute 方式，减少每次启动的巨大开销（后面以 PHP 为例说明），以常驻的方式来处理请求。

FastCGI 工作流程如下：


* FastCGI 进程管理器自身初始化，启动多个 CGI 解释器进程，并等待来自 Web Server 的连接。
* Web 服务器与 FastCGI 进程管理器进行 Socket 通信，通过 FastCGI 协议发送 CGI 环境变量和标准输入数据给 CGI 解释器进程。
* CGI 解释器进程完成处理后将标准输出和错误信息从同一连接返回 Web Server。
* CGI 解释器进程接着等待并处理来自 Web Server 的下一个连接。



![][1]

FastCGI 与传统 CGI 模式的区别之一则是 Web 服务器不是直接执行 CGI 程序了，而是通过 socket 与 FastCGI 响应器（FastCGI 进程管理器）进行交互，Web 服务器需要将 CGI 接口数据封装在遵循 FastCGI 协议包中发送给 FastCGI 响应器程序。正是由于 FastCGI 进程管理器是基于 socket 通信的，所以也是分布式的，Web服务器和CGI响应器服务器分开部署。

再啰嗦一句，FastCGI 是一种协议，它是建立在CGI/1.1基础之上的，把CGI/1.1里面的要传递的数据通过FastCGI协议定义的顺序、格式进行传递。
## 准备工作

可能上面的内容理解起来还是很抽象，这是由于第一对FastCGI协议还没有一个大概的认识，第二没有实际代码的学习。所以需要预先学习下 FastCGI 协议的内容，不一定需要完全看懂，可大致了解之后，看完本篇再结合着学习理解消化。

[http://www.fastcgi.com/devkit...][5] （英文原版）
[http://andylin02.iteye.com/bl...][6] （中文版）

## FastCGI 协议分析

下面结合 PHP 的 FastCGI 的代码进行分析，不作特殊说明以下代码均来自于 PHP 源码。
#### FastCGI 消息类型

FastCGI 将传输的消息做了很多类型的划分，其结构体定义如下：

```c
typedef enum _fcgi_request_type {
    FCGI_BEGIN_REQUEST      =  1, /* [in]                              */
    FCGI_ABORT_REQUEST      =  2, /* [in]  (not supported)             */
    FCGI_END_REQUEST        =  3, /* [out]                             */
    FCGI_PARAMS             =  4, /* [in]  environment variables       */
    FCGI_STDIN              =  5, /* [in]  post data                   */
    FCGI_STDOUT             =  6, /* [out] response                    */
    FCGI_STDERR             =  7, /* [out] errors                      */
    FCGI_DATA               =  8, /* [in]  filter data (not supported) */
    FCGI_GET_VALUES         =  9, /* [in]                              */
    FCGI_GET_VALUES_RESULT  = 10  /* [out]                             */
} fcgi_request_type;
```
#### 消息的发送顺序

下图是一个简单的消息传递流程

![][2]

最先发送的是`FCGI_BEGIN_REQUEST`，然后是`FCGI_PARAMS`和`FCGI_STDIN`，由于每个消息头（下面将详细说明）里面能够承载的最大长度是65535，所以这两种类型的消息不一定只发送一次，有可能连续发送多次。

FastCGI 响应体处理完毕之后，将发送`FCGI_STDOUT`、`FCGI_STDERR`，同理也可能多次连续发送。最后以`FCGI_END_REQUEST`表示请求的结束。

需要注意的一点，`FCGI_BEGIN_REQUEST`和`FCGI_END_REQUEST`分别标识着请求的开始和结束，与整个协议息息相关，所以他们的消息体的内容也是协议的一部分，因此也会有相应的结构体与之对应（后面会详细说明）。而环境变量、标准输入、标准输出、错误输出，这些都是业务相关，与协议无关，所以他们的消息体的内容则无结构体对应。

由于整个消息是二进制连续传递的，所以必须定义一个统一的结构的消息头，这样以便读取每个消息的消息体，方便消息的切割。这在网络通讯中是非常常见的一种手段。
#### FastCGI 消息头

如上，FastCGI 消息分10种消息类型，有的是输入有的是输出。而所有的消息都以一个消息头开始。其结构体定义如下：

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

字段解释下：
`version`标识FastCGI协议版本。
`type`标识FastCGI记录类型，也就是记录执行的一般职能。
`requestId`标识记录所属的FastCGI请求。
`contentLength`记录的contentData组件的字节数。
关于上面的`xxB1`和`xxB0`的协议说明：当两个相邻的结构组件除了后缀“B1”和“B0”之外命名相同时，它表示这两个组件可视为估值为B1<<8 + B0的单个数字。该单个数字的名字是这些组件减去后缀的名字。这个约定归纳了一个由超过两个字节表示的数字的处理方式。

比如协议头中`requestId`和`contentLength`表示的最大值就是`65535`

```c
#include <stdio.h>
#include <stdlib.h>
#include <limits.h>

int main()
{
   unsigned char requestIdB1 = UCHAR_MAX;
   unsigned char requestIdB0 = UCHAR_MAX;
   printf("%d\n", (requestIdB1 << 8) + requestIdB0); // 65535
}
```

你可能会想到如果一个消息体长度超过65535怎么办，则分割为多个相同类型的消息发送即可。
#### FCGI_BEGIN_REQUEST 的定义

```c
typedef struct _fcgi_begin_request {
    unsigned char roleB1;
    unsigned char roleB0;
    unsigned char flags;
    unsigned char reserved[5];
} fcgi_begin_request;
```

字段解释
`role`表示Web服务器期望应用扮演的角色。分为三个角色（而我们这里讨论的情况一般都是响应器角色）

```c
typedef enum _fcgi_role {
    FCGI_RESPONDER    = 1,
    FCGI_AUTHORIZER    = 2,
    FCGI_FILTER        = 3
} fcgi_role;
```

而`FCGI_BEGIN_REQUEST`中的`flags`组件包含一个控制线路关闭的位：`flags & FCGI_KEEP_CONN`：如果为0，则应用在对本次请求响应后关闭线路。如果非0，应用在对本次请求响应后不会关闭线路；Web服务器为线路保持响应性。
#### FCGI_END_REQUEST 的定义

```c
typedef struct _fcgi_end_request {
    unsigned char appStatusB3;
    unsigned char appStatusB2;
    unsigned char appStatusB1;
    unsigned char appStatusB0;
    unsigned char protocolStatus;
    unsigned char reserved[3];
} fcgi_end_request;
```

字段解释
`appStatus`组件是应用级别的状态码。
`protocolStatus`组件是协议级别的状态码；`protocolStatus`的值可能是：

FCGI_REQUEST_COMPLETE：请求的正常结束。
FCGI_CANT_MPX_CONN：拒绝新请求。这发生在Web服务器通过一条线路向应用发送并发的请求时，后者被设计为每条线路每次处理一个请求。
FCGI_OVERLOADED：拒绝新请求。这发生在应用用完某些资源时，例如数据库连接。
FCGI_UNKNOWN_ROLE：拒绝新请求。这发生在Web服务器指定了一个应用不能识别的角色时。
`protocolStatus`在 PHP 中的定义如下

```c
typedef enum _fcgi_protocol_status {
    FCGI_REQUEST_COMPLETE    = 0,
    FCGI_CANT_MPX_CONN        = 1,
    FCGI_OVERLOADED            = 2,
    FCGI_UNKNOWN_ROLE        = 3
} dcgi_protocol_status;
```

需要注意`dcgi_protocol_status`和`fcgi_role`各个元素的值都是 FastCGI 协议里定义好的，而非 PHP 自定义的。
#### 消息通讯样例

为了简单的表示，消息头只显示消息的类型和消息的 id，其他字段都不予以显示。下面的例子来自于官网

```c
{FCGI_BEGIN_REQUEST,   1, {FCGI_RESPONDER, 0}}
{FCGI_PARAMS,          1, "\013\002SERVER_PORT80\013\016SERVER_ADDR199.170.183.42 ... "}
{FCGI_STDIN,           1, "quantity=100&item=3047936"}
{FCGI_STDOUT,          1, "Content-type: text/html\r\n\r\n<html>\n<head> ... "}
{FCGI_END_REQUEST,     1, {0, FCGI_REQUEST_COMPLETE}}
```

配合上面各个结构体，则可以大致想到 FastCGI 响应器的解析和响应流程：

首先读取消息头，得到其类型为`FCGI_BEGIN_REQUEST`，然后解析其消息体，得知其需要的角色就是`FCGI_RESPONDER`，`flag`为0，表示请求结束后关闭线路。然后解析第二段消息，得知其消息类型为`FCGI_PARAMS`，然后直接将消息体里的内容以回车符切割后存入环境变量。与之类似，处理完毕之后，则返回了`FCGI_STDOUT`消息体和`FCGI_END_REQUEST`消息体供 Web 服务器解析。
## PHP 中的 FastCGI 的实现

下面对代码的解读笔记只是我个人知识的一个梳理提炼，如有勘误，请大家指出。对不熟悉该代码的同学来说可能是一个引导，初步认识，如果觉得很模糊不清晰，那么还是需要自己逐行去阅读。

以`php-src/sapi/cgi/cgi_main.c`为例进行分析说明，假设开发环境为 unix 环境。main 函数中一些变量的定义，以及 sapi 的初始化，我们就不讨论在这里讨论了，只说明关于 FastCGI 相关的内容。
### 1.开启一个 socket 监听服务

```c
fcgi_fd = fcgi_listen(bindpath, 128);
```

从这里开始监听，而`fcgi_listen`函数里面则完成 socket 服务前三步`socket`,`bind`,`listen`。
### 2.初始化请求对象

为`fcgi_request`对象分配内存，绑定监听的 socket 套接字。

```c
fcgi_init_request(&request, fcgi_fd);
```

整个请求从输入到返回，都围绕着`fcgi_request`结构体对象在进行。

```c
typedef struct _fcgi_request {
    int            listen_socket;
    int            fd;
    int            id;
    int            keep;
    int            closed;

    int            in_len;
    int            in_pad;

    fcgi_header   *out_hdr;
    unsigned char *out_pos;
    unsigned char  out_buf[1024*8];
    unsigned char  reserved[sizeof(fcgi_end_request_rec)];

    HashTable     *env;
} fcgi_request;
```
### 3.创建多个 CGI 解析器子进程

这里子进程的个数默认是0，从配置文件中读取设置到环境变量，然后在程序中读取，然后创建指定数目的子进程来等待处理 Web 服务器的请求。

```c
if (getenv("PHP_FCGI_CHILDREN")) {
    char * children_str = getenv("PHP_FCGI_CHILDREN");
    children = atoi(children_str);
    ...
}

do {
    pid = fork();
    switch (pid) {
    case 0:
        parent = 0; // 将子进程中的父进程标识改为0，防止循环 fork

        /* don't catch our signals */
        sigaction(SIGTERM, &old_term, 0);
        sigaction(SIGQUIT, &old_quit, 0);
        sigaction(SIGINT,  &old_int,  0);
        break;
    case -1:
        perror("php (pre-forking)");
        exit(1);
        break;
    default:
        /* Fine */
        running++;
        break;
    }
} while (parent && (running < children));
```
### 4.在子进程中接收请求

到这里一切都还是 socket 的服务的套路。接受请求，然后调用了`fcgi_read_request`。

```c
fcgi_accept_request(&request)
```

```c
int fcgi_accept_request(fcgi_request *req)
{
    int listen_socket = req->listen_socket;
    sa_t sa;
    socklen_t len = sizeof(sa);
    req->fd = accept(listen_socket, (struct sockaddr *)&sa, &len);

    ...

    if (req->fd >= 0) {
        // 采用多路复用的机制
        struct pollfd fds;
        int ret;

        fds.fd = req->fd;
        fds.events = POLLIN;
        fds.revents = 0;
        do {
            errno = 0;
            ret = poll(&fds, 1, 5000);
        } while (ret < 0 && errno == EINTR);
        if (ret > 0 && (fds.revents & POLLIN)) {
            break;
        }
        // 仅仅是关闭 socket 连接，不清空 req->env
        fcgi_close(req, 1, 0);
    }

    ...

    if (fcgi_read_request(req)) {
        return req->fd;
    }
}
```

并且把`request`放入全局变量`sapi_globals.server_context`，这点很重要，方便了在其他地方对请求的调用。

```c
SG(server_context) = (void *) &request;
```
### 5.读取数据

下面的代码删除一些异常情况的处理，只显示了正常情况下执行顺序。
在`fcgi_read_request`中则完成我们在消息通讯样例中的消息读取，而其中很多的`len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;`操作，已经在前面的FastCGI 消息头中解释过了。
这里是解析 FastCGI 协议的关键。

```c
static inline ssize_t safe_read(fcgi_request *req, const void *buf, size_t count)
{
    int    ret;
    size_t n = 0;

    do {
        errno = 0;
        ret = read(req->fd, ((char*)buf)+n, count-n);
        n += ret;
    } while (n != count);
    return n;
}
```

```c
static int fcgi_read_request(fcgi_request *req)
{
    ...

    if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) || hdr.version < FCGI_VERSION_1) {
        return 0;
    }

    len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
    padding = hdr.paddingLength;

    req->id = (hdr.requestIdB1 << 8) + hdr.requestIdB0;

    if (hdr.type == FCGI_BEGIN_REQUEST && len == sizeof(fcgi_begin_request)) {
        char *val;

        if (safe_read(req, buf, len+padding) != len+padding) {
            return 0;
        }

        req->keep = (((fcgi_begin_request*)buf)->flags & FCGI_KEEP_CONN);
        
        switch ((((fcgi_begin_request*)buf)->roleB1 << 8) + ((fcgi_begin_request*)buf)->roleB0) {
            case FCGI_RESPONDER:
                val = estrdup("RESPONDER");
                zend_hash_update(req->env, "FCGI_ROLE", sizeof("FCGI_ROLE"), &val, sizeof(char*), NULL);
                break;
            ...
            default:
                return 0;
        }

        if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) || hdr.version < FCGI_VERSION_1) {
            return 0;
        }

        len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
        padding = hdr.paddingLength;

        while (hdr.type == FCGI_PARAMS && len > 0) {
            if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) || hdr.version < FCGI_VERSION_1) {
                req->keep = 0;
                return 0;
            }
            len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
            padding = hdr.paddingLength;
        }
        
        ...
    }
}
```
### 6.执行脚本

假设此次请求为`PHP_MODE_STANDARD`则会调用`php_execute_script`执行PHP文件。这里就不展开了。
### 7.结束请求

```c
fcgi_finish_request(&request, 1);
```

```c
int fcgi_finish_request(fcgi_request *req, int force_close)
{
    int ret = 1;

    if (req->fd >= 0) {
        if (!req->closed) {
            ret = fcgi_flush(req, 1);
            req->closed = 1;
        }
        fcgi_close(req, force_close, 1);
    }
    return ret;
}
```

在`fcgi_finish_request`中调用`fcgi_flush`，`fcgi_flush`中封装一个`FCGI_END_REQUEST`消息体，再通过`safe_write`写入 socket 连接的客户端描述符。
### 8.标准输入标准输出的处理

标准输入和标准输出在上面没有一起讨论，实际在`cgi_sapi_module`结构体中有定义，但是`cgi_sapi_module`这个`sapi_module_struct`结构体与其他代码耦合太多，我自己也没深入的理解，这里简单做下比较，希望其他网友予以指点、补充。
`cgi_sapi_module`中定义了`sapi_cgi_read_post`来处理POST数据的读取.

```c
while (read_bytes < count_bytes) {
    fcgi_request *request = (fcgi_request*) SG(server_context);
    tmp_read_bytes = fcgi_read(request, buffer + read_bytes, count_bytes - read_bytes);
    read_bytes += tmp_read_bytes;
}
```

在`fcgi_read`中则对`FCGI_STDIN`的数据进行读取。
同时`cgi_sapi_module`中定义了`sapi_cgibin_ub_write`来接管输出处理，而其中又调用了`sapi_cgibin_single_write`，最后实现了`FCGI_STDOUT`FastCGI 数据包的封装.

```c
fcgi_write(request, FCGI_STDOUT, str, str_length);
```
## 写在最后

把 FastCGI 的知识学习理解的过程做了这样一篇笔记，把自己理解的内容（自我认为）有条理地写出来，能够让别人比较容易看明白也是一件不挺不容易的事。同时也让自己对这个知识点的理解又深入了一层。对 PHP 代码学习理解中还有很多困惑的地方还需要我自己后期慢慢消化和理解。

本文都是自己的一些理解，水平有限，如有勘误，希望大家予以指正。

本文已合并到 [http://www.php-internals.com/...][7]
我的微博 [http://weibo.com/zmkang][8] 对本文有问题可以和我沟通

坚持看完本的都是老司机，说实话，后面有些太枯燥了！如果能把每个知识点真正理解消化，绝对获益良多。

[4]: https://datatracker.ietf.org/doc/rfc3875/
[5]: http://www.fastcgi.com/devkit/doc/fcgi-spec.html
[6]: http://andylin02.iteye.com/blog/648412
[7]: http://www.php-internals.com/book/?p=chapt02/02-02-03-fastcgi
[8]: http://weibo.com/zmkang
[0]: ../img/bVPxXx.png
[1]: ../img/bVPxX5.png
[2]: ../img/bVPxYr.png
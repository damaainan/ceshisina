## PHP基础之fastcgi协议

来源：[https://segmentfault.com/a/1190000013112052](https://segmentfault.com/a/1190000013112052)


## 前言

闲来无事，决定整理一下最近看的一些东西，于是先写写`fastcgi`协议，此协议是`cgi`协议的升级版，其实就是当年`cgi`太弱，导致动态页面太耗性能，所以开发了例如`fastcgi`协议等升级版，下面我们就来聊聊这个协议的相关内容。
##`CGI`协议以及`Fastcgi`协议的介绍
###`CGI`协议的介绍
`CGI`协议的诞生是为了解决`HTTP`协议与编程语言之间的连接问题，从而减低动态页面的开发难度。这个协议避免所有的编程语言开发动态页面时还需要开发一套`HTTP`的解析库。
那么，关于`HTTP`协议本身，其实就是2个部分： **`请求头部`** 和 **`请求体`** 。请求头部基本上是作为键值对传输，例如`Date: Sat, 03 Feb 2018 00:14:03 GMT`。请求体则是纯数据流，用于传输文件等数据。所以，`CGI`本身也相应提供了2中数据格式的输入： **`键值对输入`** 和 **`数据流输入`** 。
那最初的CGI程序的输入方式及其简单，键值对数据的输入直接利用环境变量进行传输，而数据流输入则是利用标准输入流(`stdin`)进行传输。
`CGI`程序的返回也包含了2种： **`正常数据输出`** 和 **`错误数据输出`** ，正常数据输出是用于输出处理后的数据信息，主要是`HTTP`的响应报文，而错误数据输出则是用于在程序解析错误时返回给`web`服务器的错误信息，以便于`web`服务器做响应的处理和日志记录功能。正常数据输出和错误数据输出在当时的`CGI`程序中也理所应当的使用了标准输出流`stdout`和标准错误流`stderr`。可谓是十分简约。
下面是一个简单的`CGI`程序的小栗子：

```sh
#!/bin/sh

echo "Content-Type:text/html\n\n"
echo ""
echo ""
echo "hello! This is the PATH var:"
echo $PATH
```

这个`CGI`程序主要功能就是输出了`PATH`环境变量，其中会包含请求头部的相关信息（之后补充结果）。
###`Fastcgi`协议的介绍

但是`CGI`程序的弊端十分显而易见：需要新的进程进行数据处理，数据传输方式无法分布式部署，使用进程导致容易影响系统运行，每次请求都重新加载数据耗费性能。于是乎，`Fastcgi`程序就是为了解决相关问题而出现。  

`Fastcgi`程序将`CGI`程序的规范都进行了保留，并将其升级，主要是将输入和输出的方式从标准流迁移到了`socket`传输，同时，`fastcgi`协议也支持将`cgi`程序进行守护进程化，这样可以提高请求的处理速度，同时提高了稳定性。  

**`那么，`Fastcgi`协议、`php-fpm`、`Nginx`三者本身是什么关系？`** 其实就是，`Nginx`是`web`服务器，只提供`HTTP`协议的输入和输出。`php-fpm`是`Fastcgi`服务器，只支持`Fastcgi`协议的输入和输出。他们2者直接由`Nginx`将`HTTP`协议转换为`Fastcgi`协议传输给`php-fpm`进行处理。  

##`Fastcgi`协议的详解
### 协议的组成
`Fastcgi`协议是由一段一段的数据段组成，可以想象成一个车队，每辆车装了不同的数据，但是车队的顺序是固定的。 

**`输入时顺序`** 为：请求开始描述、请求键值对、请求输入数据流。 

**`输出时顺序`** 为：错误输出数据流、正常输出数据流、请求结束描述。

其中键值对、输入流、输出流，错误流的数据和`CGI`程序是一样的，只不过是换了种传输方式而已。
再回到车队的描述，每辆车的结构也是统一的，在前面都有一个引擎，引擎决定了你的车是什么样的。所以，每个数据块都包含一个 **`头部信息`** ，结构如下：

```c
typedef struct {
    unsigned char version;  // 版本号
    unsigned char type;     // 记录类型
    unsigned char requestIdB1;  // 记录id高8位
    unsigned char requestIdB0;  // 记录id低8位
    unsigned char contentLengthB1;  // 记录内容长度高8位
    unsigned char contentLengthB0;  // 记录内容长度低8位
    unsigned char paddingLength;    // 补齐位长度
    unsigned char reserved; // 真·记录头部补齐位
} FCGI_Header;
```

![][0]

注释都描述的很清楚：


* `version`为版本号，当前只有第一版本。
* `type`作为关键的描述，用于描述数据的类型，例如是键值对类型还是数据流类型，或者是请求开始和请求结束，都是通过`type`进行描述。
* `requestId`是记录ID，（`B1代表高位，B0代表低位，下文同理`），记录ID主要避免同一个`socket`通道时候传输的数据的正确性，同时也提高了传输的效率。
* `ContentLength`为数据内容的长度。
* `paddingLength`是用于数据能进行`8`字节对齐，这样对解析以及底层的`IO`操作性能有提示，所以`paddingLength`只是数据对`8`取余，固然不会超过`7`。
* 而`reserved`作为保留位，主要也是为了协议头部能与`8`字节对齐。


关于`type`的取值范围：

```c
#define FCGI_BEGIN_REQUEST       1
#define FCGI_ABORT_REQUEST       2
#define FCGI_END_REQUEST         3
#define FCGI_PARAMS              4
#define FCGI_STDIN               5
#define FCGI_STDOUT              6
#define FCGI_STDERR              7
#define FCGI_DATA                8
#define FCGI_GET_VALUES          9
#define FCGI_GET_VALUES_RESULT  10
#define FCGI_UNKNOWN_TYPE       11
#define FCGI_MAXTYPE (FCGI_UNKNOWN_TYPE)
```

我们大致按照其中的顺序进行介绍。

##### `FCGI_BEGIN_REQUEST`请求输入的时候，会带有该类型的数据，这样是为了描述当前需要`Fastcgi`服务器 **`充当的角色以及相关的设定`** 。其中的数据结构为：

```c
typedef struct {
    unsigned char roleB1;   // 角色类型高8位
    unsigned char roleB0;   // 角色类型低8位
    unsigned char flags;    // 小红旗
    unsigned char reserved[5];  // 补齐位
} FCGI_BeginRequestBody;
```

官方在升级`CGI`的时候，同时加入了多种角色给`Fastcgi`协议，其中定义为：

```c
#define FCGI_RESPONDER 1 
#define FCGI_AUTHORIZER 2 
#define FCGI_FILTER 3
```

其中`FCGI_RESPONDER`是我们最常见的动态语言脚本处理角色，叫做响应器。  
`FCGI_AUTHORIZER`是用于判断请求是否拥有访问权限，类似于`HTTP`请求中的认证功能，叫做授权器。  
`FCGI_FILTER`是用于对一些特殊的数据进行处理并返回，包括添加数据头部与尾部等功能，叫做过滤器（官方对其没有过多的介绍，所以无法详细描述）。

大多数请求我们都是使用`FCGI_RESPONDER`角色进行请求传输，因为动态语言可以完全的替代其他2中角色的功能，所以授权器和过滤器的功能被大家给遗忘了。不过这不代表角色的设定是错误的，角色的设定很大一部分程度上给`Fastcgi`协议提供了快捷扩展的功能，保证了协议的可扩展性。
`flags`则是用于设置使用传输时复用通道，避免每次传输都需要新开一个`socket`通道来浪费时间和性能。
##### `FCGI_ABORT_REQUEST`该类型主要是给`web`服务器提供 **`主动结束通道的功能`** ，场景为当`web`服务器需要尽快结束并关闭通道，则会发送该请求给`Fastcgi`服务器，这样`Fastcgi`服务会尽快的将数据处理完并返回关闭通道。
##### `FCGI_END_REQUEST`该类型是当响应数据输出完毕后，用于 **`描述该请求的响应结果`** ，类似于HTTP的响应报文的状态码，数据结构如下：

```c
typedef struct {
    unsigned char appStatusB3;
    unsigned char appStatusB2;
    unsigned char appStatusB1;
    unsigned char appStatusB0;
    unsigned char protocolStatus;
    unsigned char reserved[3];
} FCGI_EndRequestBody;
```

其中`appStatus`类似于`HTTP`请求的状态码，主要用于描述数据处理的情况，而`protocolStatus`主要用于对于此次请求通道的描述，是请求正常完成还是拒绝完成等，其中的赋值范围如下：

```c
#define FCGI_REQUEST_COMPLETE 0
#define FCGI_CANT_MPX_CONN    1
#define FCGI_OVERLOADED       2
#define FCGI_UNKNOWN_ROLE     3
```

区别如下：


* FCGI_REQUEST_COMPLETE：请求的正常结束。
* FCGI_CANT_MPX_CONN：拒绝新请求。这发生在Web服务器通过一条线路向应用发送并发的请求时，后者被设计为每条线路每次处理一个请求。
* FCGI_OVERLOADED：拒绝新请求。这发生在应用用完某些资源时，例如数据库连接。
* FCGI_UNKNOWN_ROLE：拒绝新请求。这发生在Web服务器指定了一个应用不能识别的角色时。


但是，一般情况下，大家都只返回`appStatus`为0以及`protocolStatus`为0的数据。这其实也是由于官方对相关的描述并不充分的原因。
##### `FCGI_PARAMS`该结果主要用于传输键值对类型数据，毕竟英文翻译叫参数。其中该类型为了节约空间提供了`4`类结构体：

```c
typedef struct {
    unsigned char nameLengthB0;  /* nameLengthB0  >> 7 == 0 */
    unsigned char valueLengthB0; /* valueLengthB0 >> 7 == 0 */
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair11;
 
typedef struct {
    unsigned char nameLengthB0;  /* nameLengthB0  >> 7 == 0 */
    unsigned char valueLengthB3; /* valueLengthB3 >> 7 == 1 */
    unsigned char valueLengthB2;
    unsigned char valueLengthB1;
    unsigned char valueLengthB0;
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair14;

typedef struct {
    unsigned char nameLengthB3;  /* nameLengthB3  >> 7 == 1 */
    unsigned char nameLengthB2;
    unsigned char nameLengthB1;
    unsigned char nameLengthB0;
    unsigned char valueLengthB0; /* valueLengthB0 >> 7 == 0 */
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair41;
 
typedef struct {
    unsigned char nameLengthB3;  /* nameLengthB3  >> 7 == 1 */
    unsigned char nameLengthB2;
    unsigned char nameLengthB1;
    unsigned char nameLengthB0;
    unsigned char valueLengthB3; /* valueLengthB3 >> 7 == 1 */
    unsigned char valueLengthB2;
    unsigned char valueLengthB1;
    unsigned char valueLengthB0;
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair44;
```

相对晦涩的话，我们再来看看图片描述：

![][1]

如图所示，该类似分为4个部分：`nameLength`、`valueLength`、`nameData`和`valueData`,其中`nameLength`和`valueLength`用于描述长度，有`1`字节和`4`字节的`2`种方案，也就构成了上面的`4`种不同的结构体。其中只需要判断第一个字节的最高位是否为`1`，若为`1`则是用`4`字节描述的长度，若为`0`则用`1`字节。  
其中，`1`字节是`char`型的大小，`4`字节是`int`型大小，所以十分方便解析。
### 数据流类型

类型中的`FCGI_STDIN`、`FCGI_STDOUT`、`FCGI_STDERR`和`FCGI_DATA`都是 **`数据流传输`** ，不存在什么结构体，内容中只有数据信息。十分暴力，如图所示：

![][2]
##### `FCGI_GET_VALUES`该类型主要用于查询`fastcgi`服务器的相关性能参数，结构体复用了`FCGI_PARAMS`类型的结构体，其中`name`设置为相应的值，而`value`为空即可。之后由`fastcgi`服务返回`FCGI_GET_VALUES_RESULT`类型的数据并填充`value`即可。其中`name`取值类型包括：


* FCGI_MAX_CONNS：此应用程序将接受的最大并发传输连接数, e.g. "1" or "10".
* FCGI_MAX_REQS：此应用程序将接受的最大并发请求数, e.g. "1" or "50".
* FCGI_MPXS_CONNS：此应用程序将接受的最大复用传输连接数.


## `Fastcgi`协议实例

```
0x0000:  0000 0000 0000 0000 0000 0000 0800 4500  ..............E.
0x0010:  03dc 535f 4000 4006 e5ba 7f00 0001 7f00  ..S_@.@.........
0x0020:  0001 ee2e 2328 3093 101a 95fd 1652 8018  ....#(0......R..
0x0030:  0156 01d1 0000 0101 080a 0004 4344 0004  .V..........CD..
0x0040:  4344 0101 0001 0008 0000 0001 0000 0000  CD..............
0x0050:  0000 0104 0001 037f 0100 0f1f 5343 5249  ............SCRI
0x0060:  5054 5f46 494c 454e 414d 452f 686f 6d65  PT_FILENAME/home
0x0070:  2f6d 6f62 792f 6e67 696e 782f 6874 6d6c  /moby/nginx/html
0x0080:  2f69 6e64 6578 2e70 6870 0c00 5155 4552  /index.php..QUER

... ...

0x03c0:  4745 7a68 2d43 4e2c 7a68 3b71 3d30 2e39  GEzh-CN,zh;q=0.9
0x03d0:  2c65 6e3b 713d 302e 3800 0104 0001 0000  ,en;q=0.8.......
0x03e0:  0000 0105 0001 0000 0000                 ..........
```

首先，`0800`之前是mac报文头部的数据，`ee2e`是ip报文头部的数据，`4344`之前是tcp报文的头部，所以，`0101`以后，便是我们的`fastcgi`的数据包信息。  
`0101 0001 0008 0000`，我们可以一一对应:`version:1,type:1,requestId:1,contentLength:8,padding:0`，之后就是`FCGI_BEGIN_REQUEST`的数据包：`role:1,flags:0`，说明使用的是响应器功能。   
之后再解析一下请求头：`version:1,type:4,requestId:1,contentLength:037f,padding:1`,所以下面的结构体为`FCGI_PARAMS`，继续解析：`nameLength:15,valueLength:31,nameData:SCRIPT_FILENAME,valueData:/home/moby/nginx/html/index.php`，以此类推。
#### 报文实例
`POST`请求报文

```
0x0000:  0000 0000 0000 0000 0000 0000 0800 4500  ..............E.
0x0010:  0374 0e6d 4000 4006 2b15 7f00 0001 7f00  .t.m@.@.+.......
0x0020:  0001 d5a4 2328 3da1 e47f 4aa2 48a3 8018  ....#(=...J.H...
0x0030:  0156 0169 0000 0101 080a ffff ea15 ffff  .V.i............
0x0040:  ea15 0101 0001 0008 0000 0001 0000 0000  ................
0x0050:  0000 0104 0001 02fd 0300 0f1f 5343 5249  ............SCRI
0x0060:  5054 5f46 494c 454e 414d 452f 686f 6d65  PT_FILENAME/home
0x0070:  2f6d 6f62 792f 6e67 696e 782f 6874 6d6c  /moby/nginx/html

... ...

0x0340:  5450 5f43 4f4e 4e45 4354 494f 4e6b 6565  TP_CONNECTIONkee
0x0350:  702d 616c 6976 6500 0000 0104 0001 0000  p-alive.........
0x0360:  0000 0105 0001 000c 0400 6469 6469 3d63  ..........didi=c
0x0370:  6875 7869 6e67 0000 0000 0105 0001 0000  huxing..........
0x0380:  0000                                     ..
```

响应报文:

```
0x0000:  0000 0000 0000 0000 0000 0000 0800 4500  ..............E.
0x0010:  05b4 30a7 4000 4006 069b 7f00 0001 7f00  ..0.@.@.........
0x0020:  0001 2328 ee28 d52b 8b2b 96bd f7d9 8018  ..#(.(.+.+......
0x0030:  0164 03a9 0000 0101 080a 0000 bb8d 0000  .d..............
0x0040:  bb8d 0106 0001 0564 0400 436f 6e74 656e  .......d..Conten
0x0050:  742d 7479 7065 3a20 7465 7874 2f68 746d  t-type:.text/htm
0x0060:  6c3b 2063 6861 7273 6574 3d55 5446 2d38  l;.charset=UTF-8
0x0070:  0d0a 0d0a 4172 7261 790a 280a 2020 2020  ....Array.(.....
0x0080:  5b55 5345 525d 203d 3e20 7777 772d 6461  [USER].=>.www-da
0x0090:  7461 0a20 2020 205b 484f 4d45 5d20 3d3e  ta.....[HOME].=>

... ...

0x0580:  3734 3430 322e 3335 3135 0a20 2020 205b  74402.3515.....[
0x0590:  5245 5155 4553 545f 5449 4d45 5d20 3d3e  REQUEST_TIME].=>
0x05a0:  2031 3531 3638 3734 3430 320a 290a 0000  .1516874402.)...
0x05b0:  0000 0103 0001 0008 0000 0000 0000 0000  ................
0x05c0:  0000                                     ..
```

等，实例可以自行利用`tcpdump`工具抓取。
## 总结
`Fastcgi`协议本身完成了对`CGI`协议的升级，同时自身拥有一个很好的可扩展性，但本身功能的限制导致了市面上很好有协议功能的所有实现实例。但对其进行了解有利于对网络数据的传输的熟悉以及加深印象。
## 小广告

若对`php/c++`等方向感兴趣且对滴滴出行有意向的小伙伴，可以将简历投送一波`739609084@qq.com`，福利多多。

[0]: ./img/bV3a29.png
[1]: ./img/bV3a7f.png
[2]: ./img/bV3a9O.png
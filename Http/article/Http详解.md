## Http详解

来源：[http://tinycoder.cc/2018/01/16/Http详解/](http://tinycoder.cc/2018/01/16/Http详解/)

时间 2018-01-21 15:06:01

 
HTTP（HyperText Transfer Protocol），超文本传输协议，是一个基于TCP实现的应用层协议。
 
### 报文格式 
 
HTTP1.0的报文有两种类型：请求和相应。其报文格式分别为：
 
#### 请求报文格式 

```
请求方法 URL HTTP/版本号
请求首部字段(可选)
空行
body(只对Post请求有效)
```
 
例如：

```
GET http://m.baidu.com/ HTTP/1.1
Host m.baidu.com
Connection Keep-Alive
...// 其他header

key=Android
```
 
#### 响应报文格式 

```
HTTP/版本号 返回码 返回码描述
应答首部字段(可选)
空行
body
```
 
例如：

```
HTTP/1.1 200 OK
Content-Type text/html;charset=UTF-8
...// 其他header

<html>...
```
 
### URL的结构 
 
使用HTTP协议访问资源是通过URL（Uniform Resource Identifiers）统一资源定位符来实现的。URL的格式如下：

```
scheme://host:port/path?query

scheme: 表示协议，如Http, Https, Ftp等；
host: 表示所访问资源所在的主机名：如：www.baidu.com;
port: 表示端口号，默认为80；
path: 表示所访问的资源在目标主机上的储存路径；
query: 表示查询条件；

例如： http://www.baidu.com/search?words=Baidu
```
 
### HTTP的请求方法 
 
 
 
* GET: 获取URL指定的资源； 
* POST：传输实体信息 
* PUT：上传文件 
* DELETE：删除文件 
* HEAD：获取报文首部，与GET相比，不返回报文主体部分 
* OPTIONS：询问支持的方法 
* TRACE：追踪请求的路径； 
* CONNECT：要求在与代理服务器通信时建立隧道，使用隧道进行TCP通信。主要使用SSL和TLS将数据加密后通过网络隧道进行传输。 
  
### 报文字段 
 
HTTP首部字段由字段名和字段值组成，中间以”:”分隔，如Content-Type: text/html.其中，同一个字段名可对应多个字段值。
 
HTTP的报文字段分为5种：
 
 
 
* 请求报文字段 
* 应答报文字段 
* 实体首部字段 
* 通用报文字段 
* 其他报文字段 
  
#### 请求报文字段 
 
Http请求中支持的报文字段。

```
Accept：客户端能够处理的媒体类型。如text/html, 表示客户端让服务器返回html类型的数据，如果没有，返回text
类型的也可以。媒体类型的格式一般为：type/subType, 表示优先请求subType类型的数据，如果没有，返回type类型
数据也可以。

常见的媒体类型：
文本文件：text/html, text/plain, text/css, application/xml
图片文件：iamge/jpeg, image/gif, image/png;
视频文件：video/mpeg
应用程序使用的二进制文件：application/octet-stream, application/zip

Accept字段可设置多个字段值，这样服务器依次进行匹配，并返回最先匹配到的媒体类型，当然，也可通过q参数来设置
媒体类型的权重，权重越高，优先级越高。q的取值为[0, 1], 可取小数点后3位，默认为1.0。例如：
Accept: text/html, application/xml; q=0.9, */*

Accept-Charset: 表示客户端支持的字符集。例如：Accept-Charset: GB2312, ISO-8859-1

Accept-Encoding： 表示客户端支持的内容编码格式。如：Accept-Encoding：gzip

常用的内容编码：
gzip: 由文件压缩程序gzip生成的编码格式；
compress: 由Unix文件压缩程序compress生成的编码格式；
deflate: 组合使用zlib和deflate压缩算法生成的编码格式；
identity：默认的编码格式，不执行压缩。

Accept-Language：表示客户端支持的语言。如：Accept-Language: zh-cn, en

Authorization：表示客户端的认证信息。客户端在访问需要认证的也是时，服务器会返回401，随后客户端将认证信息
加在Authorization字段中发送到服务器后，如果认证成功，则返回200. 如Linux公社下的Ftp服务器就是这种流程：
ftp://ftp1.linuxidc.com。

Host: 表示访问资源所在的主机名，即URL中的域名部分。如：m.baidu.com

If-Match: If-Match的值与所请求资源的ETag值（实体标记，与资源相关联。资源变化，实体标记跟着变化）一致时，
服务器才处理此请求。

If-Modified-Since: 用于确认客户端拥有的本地资源的时效性。 如果客户端请求的资源在If-Modified-Since指定
的时间后发生了改变，则服务器处理该请求。如：If-Modified-Since:Thu 09 Jul 2018 00:00:00, 表示如果客户
端请求的资源在2018年1月9号0点之后发生了变化，则服务器处理改请求。通过该字段我们可解决以下问题：有一个包含大
量数据的接口，且实时性较高，我们在刷新时就可使用改字段，从而避免多余的流量消耗。

If-None-Match: If-Match的值与所请求资源的ETag值不一致时服务器才处理此请求。

If-Range： If-Range的值（ETag值或时间）与所访问资源的ETag值或时间相一致时，服务器处理此请求，并返回
Range字段中设置的指定范围的数据。如果不一致，则返回所有内容。If-Range其实算是If-Match的升级版，因为它
的值不匹配时，依然能够返回数据，而If-Match不匹配时，请求不会被处理，需要数据时需再次进行请求。


If-Unmodified-Since：与If-Modified-Since相反，表示请求的资源在指定的时间之后未发生变化时，才处理请求，
否则返回412。

Max-Forwards：表示请求可经过的服务器的最大数目，请求每被转发一次，Max-Forwards减1，当Max-Forwards为0
时，所在的服务器将不再转发，而是直接做出应答。通过此字段可定位通信问题，比如之前支付宝光纤被挖断，就可通过设
置Max-Forwards来定位大概的位置。

Proxy-Authorization：当客户端接收到来自代理服务器的认证质询时，客户端会将认证信息添加到
Proxy-Authorization来完成认证。与Authorization类似，只不过Authorization是发生在客户端与服务端之间。

Range：获取部分资源，例如：Range: bytes=500-1000表示获取指定资源的第500到1000字节之间的内容，如果服务器
能够正确处理，则返回206作为应答，表示返回了部分数据，如果不能处理这种范围请求，则以200作为应答，返回完整的
数据，

Referer：告知服务器请求是从哪个页面发起的。例如在百度首页中搜索某个关键字，结果页面的请求头部就会有这个字段，
其值为https://www.baidu.com/。通过这个字段可统计广告的点击情况。

User-Agent：将发起请求的浏览器和代理名称等信息发送给服务端，例如：
User-Agent: Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36
(KHTML, like Gecko) Chrome/63.0.3239.84 Mobile Safari/537.36
```
 
#### 应答报文字段 
 
Http应答中支持的报文字段。

```
Accept-Ranges: 服务端告知客户端自己能够处理范围请求，其值有两种:bytes，none.其中bytes表示可处理，none
表示不能处理。

Age：服务端告知客户端，源服务器（而不是缓存服务器）在多久之前创建了响应。
单位为秒。

ETag： 实体资源的标识，可用来请求指定的资源。

Location：请求的资源所在的新位置。

Proxy-Authenticate：将代理服务器需要的认证信息发送给客户端。

Retry-After：服务端告知客户端多久之后再重试，一般与503和3xx重定向类型的应答一起使用。

Server：告知服务端当前使用的HTTP服务器应用程序的相关信息。

WWW-Authenticate：告知客户端适用于所访问资源的认证方案，如Basic或Digest。401的响应中肯定带有
WWW-Authenticate字段。
```
 
#### 实体首部字段 

```
Allow：通知客户端，服务器所支持的请求方法。但服务器收到不支持的请求方法时，会以405（Method Not Allowed）
作为响应。

Content-Encoding：告知客户端，服务器对资源的内容编码。

Content-Language：告知客户端，资源所使用的自然语言。

Content-Length：告知客户端资源的长度

Content-Location：告知客户端资源所在的位置。

Content-Type：告知客户端资源的媒体类型，取值同请求首部字段中的Accept。

Expires：告知客户端资源的失效日期。可用于对缓存的处理。

Last-Modified：告知客户端资源最后一次修改的时间。
```
 
#### 通用报文字段 
 
即可在HTTP请求中使用，也可在HTTP应答中使用的报文字段。

```
Cache-Control：控制缓存行为；

Connection：管理持久连接，设置其值为Keep-Alive可实现长连接。

Date：创建HTTP报文的日期和时间。

Pragma：Http/1.1之前的历史遗留字段，仅作为HTTP/1.0向后兼容而定义，虽然是通用字段，当通常被使用在客户单的
请求中，如Pragma: no-cache, 表示客户端在请求过程中不循序服务端返回缓存的数据；

Transfer-Encoding：规定了传输报文主题时使用的传输编码，如Transfer-Encoding: chunked

Upgrade: 用于检查HTTP协议或其他协议是否有可使用的更高版本。

Via：追踪客户端和服务端之间的报文的传输路径，还可避免会环的发生，所以在经过代理时必须添加此字段。

Warning：Http/1.1的报文字段，从Http/1.0的AfterRetry演变而来，用来告知用户一些与缓存相关的警告信息。
```
 
#### 其他报文字段 
 
这些字段不是HTTP协议中定义的，但被广泛应用于HTTP请求中。
 
 
 
* Cookie：属于请求型报文字段，在请求时添加Cookie, 以实现HTTP的状态记录。
  
* Set-Cookie：属于应答型报文字段。服务器给客户端传递Cookie信息时，就是通过此字段实现的。
  
  
Set-Cookie的字段属性：

```
NAME=VALUE：赋予Cookie的名称和值；
expires=DATE: Cookie的有效期；
path=PATH: 将服务器上的目录作为Cookie的适用对象，若不指定，则默认为文档所在的文件目录；
domin=域名：作为Cookies适用对象的域名，若不指定，则默认为创建Cookie的服务器域名；
Secure: 仅在HTTPS安全通信是才会发送Cookie；
HttpOnly: 使Cookie不能被JS脚本访问；

如：Set-Cookie:BDSVRBFE=Go; max-age=10; domain=m.baidu.com; path=/
```
 
### HTTP应答状态码
 
| 状态码 | 类别 | 描述 | 
| 1xx | Informational(信息性状态码) | 请求正在被处理 | 
| 2xx | Success(成功状态码) | 请求处理成功 | 
| 3xx | Redirection(重定向状态码) | 需要进行重定向 | 
| 4xx | Client Error(客户端状态码) | 服务器无法处理请求 | 
| 5xx | Server Error(服务端状态码) | 服务器处理请求时出错 | 
 
 
#### 常见应答状态码： 
 


![][0] 
 
了解应答状态码的含义，有助于我们在开发过程中定位问题，比如出现4xx, 我们首先需要检查的是请求是否有问题，而出现5xx时，则应让服务端做相应的检查工作。
 
### HTTP的实现 
 
HTTP是基于TCP协议的一种应用层协议，那我们就可通过系统提供的Socket来实现，实现步骤如下：
 
 
 
* 建立一条TCP连接； 
* 按照HTTP请求报文的格式构造数据，构造完成之后发送到服务器端； 
* 接收服务器端的数据，根据HTTP的应答报文的格式，解析数据。 
* 断开TCP连接； 
  
这里的代码依然使用上一篇的理论知识中引用的代码：

```c
#include <sys/socket.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <netdb.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <arpa/inet.h> 

int main(int argc, char *argv[])
{
    int sockfd = 0, n = 0;
    char recvBuff[1024];
    struct sockaddr_in serv_addr; 

    if(argc != 2)
    {
        printf("\n Usage: %s <ip of server> \n",argv[0]);
        return 1;
    } 

    memset(recvBuff, '0',sizeof(recvBuff));
    // 创建socket
    if((sockfd = socket(AF_INET, SOCK_STREAM, 0)) < 0)
    {
        printf("\n Error : Could not create socket \n");
        return 1;
    } 

    // 设置IP和端口
    memset(&serv_addr, '0', sizeof(serv_addr)); 
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(80); 

    if(inet_pton(AF_INET, argv[1], &serv_addr.sin_addr)<=0)
    {
        printf("\n inet_pton error occured\n");
        return 1;
    } 
    // 连接到指定的IP和端口 -> 连接成功后即三次握手完成
    if( connect(sockfd, (struct sockaddr *)&serv_addr, sizeof(serv_addr)) < 0)
    {
       printf("\n Error : Connect Failed \n");
       return 1;
    } 

    // 构造HTTP头部
    char *str = "HEAD http://www.baidu.com/ HTTP/1.1\r\n"
            "Host: www.baidu.com\r\n"
            "\r\n";

    // 将HTTP请求发送到服务端
    int len = write(sockfd, str, strlen(str) + 1);
    if (len > 0) {
        printf("request send successful!\n\n");
    }

    // 读取服务端返回的数据
    while ( (n = read(sockfd, recvBuff, sizeof(recvBuff)-1)) > 0)
    {
        recvBuff[n] = 0;
        if(fputs(recvBuff, stdout) == EOF)
        {
            printf("\n Error : Fputs error\n");
        }
    } 

    if(n < 0)
    {
        printf("\n Read error \n");
    } 
    close(sockfd);

    return 0;
}
```
 
通过gcc编译之后运行可得以下结果：

```
$ ./client 58.217.200.13
request send successful!

HTTP/1.1 200 OK
Date: Mon, 15 Jan 2018 12:21:47 GMT
Server: Apache
P3P: CP=" OTI DSP COR IVA OUR IND COM "
P3P: CP=" OTI DSP COR IVA OUR IND COM "
Set-Cookie: BAIDUID=37229A83CAD417143F243CF4BF632CD4:FG=1; expires=Tue, 15-Jan-19 12:21:47 GMT; max-age=31536000; path=/; domain=.baidu.com; version=1
Set-Cookie: BAIDUID=37229A83CAD41714BC95B90FC2266CF0:FG=1; expires=Tue, 15-Jan-19 12:21:47 GMT; max-age=31536000; path=/; domain=.baidu.com; version=1
Last-Modified: Wed, 08 Feb 2017 07:55:35 GMT
ETag: "1cd6-5480030886bc0"
Accept-Ranges: bytes
Content-Length: 7382
Cache-Control: max-age=1
Expires: Mon, 15 Jan 2018 12:21:48 GMT
Vary: Accept-Encoding,User-Agent
Connection: Keep-Alive
Content-Type: text/html
```
 
从结果可以看出，我们已实现了HTTP的请求过程。其应答过程的实现也是同样的道理。根据上一篇文章 [网络编程之理论篇][1] 中介绍的Socket服务端的编程步骤，结合HTTP应答报文的格式，即可快速实现HTTP的应答过程。当然，要实现完整的HTTP协议还是有很多细节需要处理的。这里只是演示了一下HTTP的实现过程，便于对HTTP协议的实现由一个简单的了解。下一篇文章通过JDK提供的HttpUrlConnection来深入理解HTTP的实现过程。 
 


[1]: https://juejin.im/post/5a535f8b518825733060c7bd
[0]: https://img0.tuicool.com/aiYrmme.png
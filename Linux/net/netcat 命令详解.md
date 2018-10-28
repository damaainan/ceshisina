## netcat 命令详解

来源：[https://www.imydl.tech/linux/689.html](https://www.imydl.tech/linux/689.html)

时间 2018-10-15 16:54:00

 
netcat 是一款调试 TCP/UDP 网络连接的利器，常被称作网络调试的瑞士军刀，可见其功能强大。
 
netcat 在 Linux, Windows 等各大操作系统上都有对应等发行版，以下以 Linux(Ubuntu 16.04) 为例介绍其几个强大的用法。
 
![][0]
 
netcat 在 Linux 中一般通过命令 nc 调用。我们先来看下它的帮助文档

```
# nc -h
OpenBSD netcat (Debian patchlevel 1.105-7ubuntu1)
This is nc from the netcat-openbsd package. An alternative nc is available
in the netcat-traditional package.
usage: nc [-46bCDdhjklnrStUuvZz] [-I length] [-i interval] [-O length]
      [-P proxy_username] [-p source_port] [-q seconds] [-s source]
      [-T toskeyword] [-V rtable] [-w timeout] [-X proxy_protocol]
      [-x proxy_address[:port]] [destination] [port]
    Command Summary:
        -4        Use IPv4
        -6        Use IPv6
        -b        Allow broadcast
        -C        Send CRLF as line-ending
        -D        Enable the debug socket option
        -d        Detach from stdin
        -h        This help text
        -I length    TCP receive buffer length
        -i secs        Delay interval for lines sent, ports scanned
        -j        Use jumbo frame
        -k        Keep inbound sockets open for multiple connects
        -l        Listen mode, for inbound connects
        -n        Suppress name/port resolutions
        -O length    TCP send buffer length
        -P proxyuser    Username for proxy authentication
        -p port        Specify local port for remote connects
            -q secs        quit after EOF on stdin and delay of secs
        -r        Randomize remote ports
        -S        Enable the TCP MD5 signature option
        -s addr        Local source address
        -T toskeyword    Set IP Type of Service
        -t        Answer TELNET negotiation
        -U        Use UNIX domain socket
        -u        UDP mode
        -V rtable    Specify alternate routing table
        -v        Verbose
        -w secs        Timeout for connects and final net reads
        -X proto    Proxy protocol: "4", "5" (SOCKS) or "connect"
        -x addr[:port]    Specify proxy address and port
        -Z        DCCP mode
        -z        Zero-I/O mode [used for scanning]
    Port numbers can be individual or ranges: lo-hi [inclusive]
```
 
可以看到我们使用的是`netcat-openbsd`这个发行版的 netcat, 除此之外，还有`netcat-traditiona`l 等发行版。不同发行版的基本功能相同，只是有细微的差别。
 
![][1]
 
简单来说， nc 有以下功能：

 
* 模拟 TCP 服务端 
* 模拟 TCP 客户端 
* 模拟 UDP 服务端 
* 模拟 UDP 客户端 
* 模拟 UNIX socket 服务端 
* 模拟 UNIX socket 客户端 
* 端口扫描 
* 传输文件 
* 将服务器 bash 暴露给远程客户端 
* 内网穿透，反向获取防火墙后的机器的 bash 
 
 
以下分别举例说明。
 
## 实例环境设定
 
假设

 
* 服务器 A 有外网 IP 202.118.69.40 
* 服务器 B 没有外网 IP 
* 客户端 C 有外网 IP 202.119.70.41 
 
 
三台主机上均为 Ubuntu 16.04 操作系统。
 
## 1、模拟 TCP 服务端
 `nc -lk 9090`在服务器 A 执行以上命令，将会把 nc 绑定到 9090 端口，并开始监听请求。

```
-l
-k


```
 
这时就可以请求该接口了， nc 会把请求报文输出到标准输出。
 
例如在客户端 C 执行`curl 202.118.69.40`
 nc 将会将 HTTP 请求的报文输出到标准输出

```
GET / HTTP/1.1
Host: 192.168.0.71:9090
User-Agent: curl/7.54.0
Accept: */*
```
 
## 2、模拟 TCP 客户端
 `printf "GET / HTTP/1.1\r\nHost: example.com\r\n\r\n" | nc example.com 80`在客户端 C 执行上述代码,
 
C 的输出如下

```
Connection to example.com port 80 [tcp/http] succeeded!
HTTP/1.1 200 OK
Accept-Ranges: bytes
Cache-Control: max-age=604800
Content-Type: text/html; charset=UTF-8
Date: Tue, 09 Oct 2018 07:08:38 GMT
Etag: "1541025663+gzip"
Expires: Tue, 16 Oct 2018 07:08:38 GMT
Last-Modified: Fri, 09 Aug 2013 23:54:35 GMT
Server: ECS (sjc/4E52)
Vary: Accept-Encoding
X-Cache: HIT
Content-Length: 1270

<!doctype html>
<html>
<head>
    <title>Example Domain</title>

    <meta charset="utf-8" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
   ...
</head>

<body>

  
## Example Domain

This domain is established to be used for illustrative examples in documents. You may use this
    domain in examples without prior coordination or asking for permission.

<a href="http://www.iana.org/domains/example">More information...</a>


</body>
</html>
```
 
证明客户端模拟成功，给 example.com 发送了 HTTP Method 为 GET 的 HTTP 请求。
 
## 3、模拟 UDP 服务端
 
在 A 执行
 `nc -lk -u 9090`## 4、模拟 UDP 客户端
 
在 C 执行
 `nc -u 202.118.69.40 9090`此时在客户端终端中输入任意字符，将在 A 的终端中输出同样的字符，证明 UDP 服务端和客户端模拟成功。
 
## 5、模拟 UNIX socket 服务端
 
在 A 执行
 `nc -Ul /tmp/mync.sock`## 6、模拟 UNIX socket 客户端
 
在 A 执行(UNIX 默认不能跨服务器)
 `nc -U /tmp/mync.sock`此时在该终端中输入任意字符，将在第5步的终端中输出同样的字符，证明 Unix socket 服务端和客户端模拟成功。
 
## 7、端口扫描
 `nc -vz 202.118.69.40 1-81 2>&1|grep succeed`
 
* `-z`指 Zero-I/O mode，即连接的过程中禁用输入输出，仅用与端口扫描。  
* `2>&1|grep succeed`
 默认情况下扫描过程中，不论成功与失败，扫描结果都被输出到了“标准错误输出”，该命令用来过滤，仅显示出打开到端口。  
 
 
上述指令输出结果如下：

```
Connection to 202.118.69.40 22 port [tcp/ssh] succeeded!
Connection to 202.118.69.40 53 port [tcp/domain] succeeded!
Connection to 202.118.69.40 80 port [tcp/http] succeeded!
```
 
## 8、传输文件
 
### 8.1 向服务器上传图片
 
服务器 A 监听 9090 端口
 `nc -l 9090 | base64 -d > WechatIMG88.jpeg`客户端上传图片
 `base64 WechatIMG88.jpeg | nc 202.118.69.40 9090`注：因为需要传输图片，所以先 base64 编码，然后下载完再解码避免终端错乱。
 
### 8.2 从服务器下载图片
 
服务器 A 监听 9090 端口，并将要下载的图片输出到 nc
 `base64 WechatIMG88.jpeg | nc -l 9090`客户端下载
 `nc -t 202.118.69.40 9090|base64 -D > w.jpeg`## 9、将服务器 bash 暴露给远程客户端
 
与 7 类似，只不过服务端将接收到到内容管道给 /bin/bash
 
然后在客户端输入要敲的命令
 `nc -l 9090 | /bin/bash`## 10、内网穿透，反向获取防火墙后的机器的 bash
 
与 8 类似，只不过服务器 B 将内容管道给 /bin/bash
 
在客户端 A 打开监听
 `nc -l 9090`在服务器 C 上执行以下代码反向接受命令
 `nc -t 202.119.70.41 9090 | /bin/bash`然后在客户端 A 输入要执行的命令即可。
 
需要注意的是，使用上述命令远程执行命令时在客户端无法看到命令的返回结果。
 
通过创建命名管道的方式，可将 bash 执行的结果回传给 netcat,
 
具体命令如下（在服务器 C 执行代码）：

```
mkfifo ncpipe
nc -t 202.119.70.41 9090 0<ncpipe| /bin/bash 1>ncpipe
```


[0]: ./img/Z3yINjM.png
[1]: ./img/2AFriir.gif
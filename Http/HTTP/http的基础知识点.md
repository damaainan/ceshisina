# http的基础知识点

## 前言

三月份就快要结束了，这个月定的目标是对http的基础知识点做一个落地。直入主题我们顺着下面的思路去对http基础知识做一个总结：

* 概念
* 五层网络协议
* HTTP Method
* HTTP Status
* HTTP Header
* Cookie/Session
* HTTPs
* Websocket
* HTTP2

## 概念

什么是http？Hypertext Transfer Protocol, 超文本传输(转移)协议，是客户端和服务端传输文本制定的协议。说起http协议，还得说说WWW，http是构建WWW的三项技术之一，具体的三项技术如下：

WWW: world wide web, 万维网

* HTML: Hypertext Markup Language, 超文本标记语言
* HTTP: Hypertext Transfer Protocol, 超文本传输(转移)协议
* URL: Uniform Resource Locator, 统一资源定位符号


> URI: Uniform Resource Identitier, 统一资源标示符号，URL是URI的子集

## 五层网络协议

        应用层(http/https/websocket/ftp...) => 定义：文本传输协议
          |
        传输层(tcp/udp) => 定义：端口
          |
        网络层(ip)　=> 定义：IP
          |
        链路层(mac&数据包) => 定义：数据包，MAC地址
          |
        实体层(光缆/电缆/交换机/路由/终端...) => 定义：物理

TCP/IP:

说起网络协议自然不能不提TCP/IP协议了，它有两种解释如下，

* 解释一：分别代表tcp协议和ip协议
* 解释二：如果按照网络五层架构，TCP/IP代表除了应用层其他层所有协议簇的统称

TCP/IP connect: TCP/IP的三次握手:

              标有syn的数据包
              ------------->
              标有syn/ack的数据包
      client  <-------------  server
              标有ack的数据包
              -------------->

TCP/IP finish: TCP/IP的四次握手:

                              fin
                        <-------------
                              ack
    client(或server)    -------------> server(或client)
                              fin
                        ------------->
                              ack
                        <-------------

Keep-Alive: 

HTTP协议初期每次连接结束后都会断开TCP连接，之后HEADER的connection字段定义Keep-Alive（HTTP 1.1 默认 持久连接），代表如果连接双方如果没有一方主动断开都不会断开TCP连接，减少了每次建立HTTP连接时进行TCP连接的消耗。

## HTTP Method

* get: 获取资源，url传参，大小2KB
* post: 传输资源，http body, 大小默认8M，1000个input variable
* put: 传输资源，http body，资源更新
* delete: 删除资源
* patch: 传输资源，http body，存在的资源局部更新
* head: 获取http header
* options: 获取支持的method
* trace: 追踪，返回请求回环信息
* connect: 建立隧道通信

## HTTP Status

* 200: ok
* 301: 永久重定向
* 302: 临时重定向
* 303: 临时重定向，要求用get请求资源
* 304: not modified, 返回缓存，和重定向无关
* 307: 临时重定向,严格不从post到get
* 400: 参数错误
* 401: 未通过http认证
* 403: forbidden，未授权
* 404: not found，不存在资源
* 500: internet server error，代码错误
* 502: bad gateway，fastcgi返回的内容web server不明白
* 503: service unavailable，服务不可用
* 504: gateway timeout，fastcgi响应超时


> 接口选取http status作为响应code是个不错的选择

## HTTP Header Fields

常见通用头部

* Cache-Control:
    * no-cache: 不缓存过期的缓存
    * no-store: 不缓存* Pragma: no-cache, 不使用缓存，http1.1前的历史字段

* Connection:
    * 控制不在转发给代理首部不字段
    * Keep-Alive/Close: 持久连接

* Date: 创建http报文的日期

常见请求头

* Accept: 可以处理的媒体类型和优先级
* Host: 目标主机域名
* Referer: 请求从哪发起的原始资源URI
* User-Agent: 创建请求的用户代理名称
* Cookie: cookie信息

常见响应头

* Location: 重定向地址
* Server: 被请求的服务web server的信息
* Set-Cookie: 要设置的cookie信息
    * NAME: 要设置的键值对
    * expires: cookie过期时间
    * path: 指定发送cookie的目录
    * domain: 指定发送cookie的域名
    * Secure: 指定之后只有https下才发送cookie
    * HostOnly: 指定之后javascript无法读取cookie

## Cookie/Session

* Cookie: 工作机制是用户识别和状态管理，服务端为了管理用户的状态会通过客户端，把一些临时的数据写入到设备中Set-Cookie，当用户访问服务的时候，服务可以通过通信的方式取回之前存放的cookie。
* Session: 由于http是无状态的，请求之间无法维系上下文，所以就出现了session作为会话控制，服务端存放用户的会话信息。

## HTTPs

概念:在http协议上增加了ssl(secure socket layer)层。

        SSL层
          |
        应用层
          |
        传输层
          |
        网络层
          |
        链路层
          |
        实体层

HTTPS 认证流程

    
                                  发起请求
                         --------------------------->　　server 
                                  下发证书
                          <---------------------------   server 
                          证书数字签名(用证书机构公钥加密)
                         --------------------------->　　证书机构 
                              证书数字签名验证通过
    client(内置证书机构证书) <---------------------------   证书机构
                          公钥加密随机密码串(未来的共享秘钥)
                         --------------------------->　　server私钥解密(非对称加密)
                            SSL协议结束　HTTP协议开始
                          <---------------------------   server(对称加密)
                                共享秘钥加密 HTTP
                         --------------------------->　　server(对称加密)

* 核对证书证书： 证书机构的公开秘钥验证证书的数字签名
* 公开密钥加密建立连接：非对称加密
* 共享密钥加密

## Websocket

* 基于http协议建立连接，header的upgrade字段转化协议为websocket
* 全双工通信，客户端建立连接

## HTTP2

* 多路复用：多个请求共享一个tcp连接
* 全双工通信
* 必须https://
* 头部压缩
* 二进制传输

## 附录

详细五层协议

* 概括：从上到下，越上越接近用户，越下越接近硬件
* 应用层:
    * 规定应用程序的数据格式
    * [HEAD(以太网标头) [HEAD(IP标头) [HEAD(TCP标头) DATA(应用层数据包)]]]

* 传输层(端口到端口的通信):
    * 端口：
        * 0到65535(2^16)的整数
        * 进程使用网卡的编号
        * 通过IP+mac确定主机，只要确定主机+端口(套接字socket)，就能进行程序间的通信

    * UDP协议：
        * 数据包中加入端口依赖的新协议
        * 数据包[HEAD(发送、接收mac) [HEAD(发送、接收ip) [HEAD(发送、接收端口) DATA]]]
        * 简单，可靠性差，不知道对方是否接受包

    * TCP协议：
        * 带有确认机制的UDP协议
        * 过程复杂，实现困难，消耗资源

* 网络层(主机到主机的通信):
    * IP协议
        * ipv4:
            * 32个二进制位表示，由网络部分和主机部分构成，
            * 子网掩码: 网络部分都为1，主机部分都为0，目的判断ip的网络部分，如255.255.255.0(11111111.11111111.11111111.00000000)
            * IP数据包：标头Head+数据Data,放进以太网数据包的Data部分[HEAD [HEAD DATA]]
            * IP数据包的传递：
                * 非同一网络：无法获得mac地址,发送数据到网关，网关处理
                    * ARP(Address Resolation Protocol): 解析地址协议，通过ip解析mac地址
      
                * 同一网络：mac地址填写FF:FF:FF:FF:FF:FF:FF，广播数据，对比ip，不符合丢包

* 链接层：
    * 定义数据包(帧Frame)
        * 标头(Head):数据包的一些说明项, 如发送者、接收者、数据类型
        * 数据(Data):数据包的具体内容
        * 数据包:[HEAD DATA]

    * 定义网卡和网卡唯一的mac地址
        * 以太网规定接入网络的所有终端都应该具有网卡接口，数据包必须是从一个网卡的mac地址到另一网卡接口的mac地址
        * mac全球唯一，16位16位进制组成，前6厂商编号，后6网卡流水号

    * 广播发送数据
        * 向本网络内的所有设备发送数据包，对比接收者mac地址，不是丢包，是接受

* 实体层：
    * 终端(pc，phone，pad...)的物理连接(光缆，电缆，路由...)，负责传递0和1信号

# HTTPS 性能优化学习 

 13 May 2017

最近在学习https性能优化，虽然网上已经有许多的关于https性能优化的文章了，但还是想写下这篇文章，作为学习总结=^_^=，文中对于一些概念性或实现细节上的东西并不会展开，但会给出相应的引用，有些图片也来自网上资源。

章节规划：

* 认识SSL/TLS
* 算法选择
* 会话恢复
* OCSP stapling
* TLS 缓冲区优化
* TLS false start
* 其他优化

#### 认识SSL/TLS

- - -

SSL和TLS都是用于保障端到端之间连接的安全性。SSL最初是由Netscape开发的，后来为了使得该安全协议更加开放和自由，更名为TLS，并被标准化到RFC中，现在主流的是TLS 1.2版本。

![][0]

从上图，可以看出SSL/TLS是介于应用层和传输层之间，并且分为握手层（Handshake Layer）和记录层（Record Layer）。

* 握手层：端与端之间协商密码套件、连接状态。
* 记录层：对数据的封装，数据交给传输层之前，会经过**分片-压缩-认证-加密**。

从TLS 1.2 RFC可以了解更多：[https://www.ietf.org/rfc/rfc5246.txt][1]

#### 算法选择

- - -

TLS中可被配置的算法分类：

1. [数字签名][2]：RSA、DSA
1. [流加密][3]：RC4
1. [分组加密][4]：DES、AES
1. [认证加密][5]：GCM
1. [公钥加密][6]：RSA
1. [消息认证码][7]：SHA
1. [密钥交换][8]：Diffie–Hellman

密码套件决定了会使用到的算法，例如执行openssl ciphers -v 'ALL' | grep ECDHE-RSA-AES128-GCM-SHA256：

    ECDHE-RSA-AES128-GCM-SHA256 TLSv1.2 Kx=ECDH     Au=RSA  Enc=AESGCM(128) Mac=AEAD
    

表明该算法是在TLS 1.2中支持的，密钥交换采用ECDH（EC是指采用椭圆曲线的DH）,数字签名采用RSA，加密采用128位密钥长度的AESGCM，消息认证码采用AEAD（AEAD是一种新的加密形式，把加密和消息认证码结合到一起，而不是某个算法，例如使用AES并采用GCM模式加密，就能够为数据提供保密性、完整性的保障）。

> 如何理解完整性？

> > A 将明文M加密后为MC，发给B，B解密，得到明文。 如果此时有中间人C，将MC替换为CMC（虽然C不知道A怎么加密的，但这没关系），B将CMC解密，得到明文（那么B拿到的其实是错误的明文）。 所以需要引入消息认证码，B才能够判断收到的密文是否被篡改过。 这里你可能会问：那如果C同时伪造消息认证码呢？ 这个就得看MAC和加密是如何配合的了，详情可以查看[> > 认证加密][5]> > 中的Approaches to Authenticated Encryption章节。

在TLS握手和数据传输的不同阶段会采用相应的算法：

* 服务端身份验证：数字签名（RSA、ECDSA）
* 密钥交换：RSA/密钥交换算法（ECDH）
* 加密/解密：流加密（RC4）和分组加密（3DES/AES/AESGCM）
* 生成消息认证码：SHA/AEAD

> 不知是否有人发现并没有提到压缩算法，如果google下TLS压缩优化相关的内容，会发现没有，因为目前在TLS 1.2 RFC中，关于压缩方法的结构定义为> enum { null(0), (255) } CompressionMethod;> ，即只有null方法（不进行压缩）。目前存在对TLS压缩的攻击：[> http://www.freebuf.com/articles/web/5636.html][9]> ，可能是基于此原因，TLS压缩目前只是个概念性的东西，没有被真正应用起来。

##### 如何选择算法——安全性

通常加密算法的安全性依赖于密钥的长度，且不同加密算法，即使密钥长度相同，但提供的安全性也可能是不同的，相关资料：[key size][10]。所以并没有一个标准的归一化方法去衡量所有的加密算法，但是有来自世界上各个组织/机构对不同类型算法安全性的评估，可以看下这个网站：[https://www.keylength.com/][11]

执行openssl ciphers -v 'ALL' | wc -l会发现有100+个密码套件（不同openssl版本提供的密码套件有点差异），然而，实际只会使用到其中一部分，因为openssl提供的不少算法是不安全的，需要排除掉。

执行openssl ciphers -v 'HIGH MEDIUM !aNULL !eNULL !LOW !MD5 !EXP !DSS !PSK !SRP !CAMELLIA !IDEA !SEED !RC4' | wc -l，发现只剩下50+个密码套件。

筛选后剩下的密码套件还是挺多的，一个个做性能测试的话，会GG的= =。其实可以根据需要支持的客户端，再筛选出主流的密码套件。网址：[https://www.ssllabs.com/ssltest/clients.html][12]，提供了绝大部分客户端对TLS的支持情况，点击相应的User agent可以查看到其支持的密码套件，并且各套件的安全性也被标注出来了。

网址：[https://www.ssllabs.com/ssltest/][13]，可以用于测试服务器的SSL配置情况，并会给出得分，如下图google的得分为A：

![][14]

#### 如何选择算法——性能

> 以下性能测试都是选取主流的算法进行。

**数字签名：ECDSA vs RSA**

需要先分别生成采用ECDSA和RSA的签名证书。

生成ECDSA自签名的证书：

    openssl ecparam -name prime256v1 -genkey -out ec_key.pem
    openssl req -new -x509 -key ec_key.pem -out cert.pem -days 365
    

> -param_enc参数使用默认的named_curve就可以了，如果使用explicit，会发现生成的证书nginx能配置成功，但客户端连接时会出现handshake error。

生成RSA签名的证书：

    openssl req -newkey rsa:2048 -nodes -keyout rsa_key.pem -x509 -days 365 -out cert.pem
    

执行openssl speed rsa2048 ecdsap256测试下：

                      sign    verify    sign/s verify/s
    rsa 2048 bits 0.000834s 0.000024s   1198.9  41031.9
                                  sign    verify   sign/s  verify/s
    256 bit ecdsa (nistp256)   0.0000s   0.0001s  21302.5   9728.5
    

可以看到签名性能ECDSA > RSA，而验证性能RSA > ECDSA。

测试环境：

* 服务端：1台虚拟机CentOS 4核 openresty 2个worker
* 客户端：4台虚拟机CentOS 4/2/2/2核（手头只有这些虚拟机= =）， 用shell脚本模拟并发的ab -c 800 -n 800（并发的ab实例数=2*CPU_NUM），使用time命令获取消耗的时间
* 测试页面562字节，目标是测试数字签名的性能，所以页面小点，避免加密/解密、数据传输占用太多时间

> 多台客户端如何同时启动？ctrl+tab，命令+回车……

> 为什么不用jmeter？我用了1Master3Slave的jmeter分布式压测发现，jmeter对于在该场景（CPU bound）下的性能测试不行，服务端压力上不去

> 在相同的请求量下，RSA签名会使服务端CPU占用更高，所以这次测试需要在两种签名的压测下，服务端CPU都保持在90%以上（不然的话，对ECDSA就不公平了）。

> 为何openresty是2个worker？因为开4个的话，ECDSA的压测没法使openresty4个worker的CPU消耗达到90%

ECDHE-ECDSA-AES128-GCM-SHA256，服务端CPU占比90%，结果：

客户端（CPU核数标识） 4 2 2 2 第一次 11.988 17.334 9.161 7.748 第二次 12.524 13.750 12.129 7.582 第三次 11.836 17.991 9.195 10.023 第四次 11.617 7.081 9.168 8.919 

ECDHE-RSA-AES128-GCM-SHA256，服务端CPU占比100%，结果：

客户端（CPU核数标识） 4 2 2 2 第一次 12.704 21.088 18.232 6.134 第二次 13.355 21.071 26.990 6.102 第三次 14.638 16.009 11.669 6.071 第四次 13.913 21.061 21.271 5.108 

从表格中的数据可以看出ECDSA的性能要比RSA好点，这里ECDSA的测试尚未压榨完服务端呢。从openssl speed的结果也可以看出ECDSA的签名性能是要远超过RSA的，而且签名是在服务端做的，所以面对海量的客户端，服务端应该选择使用ECDSA。

**密钥交换：RSA vs ECDHE**

测试环境同上，但只使用了4/2核两台客户端机器发请求。证书使用的是生成的RSA证书，ECDSA证书能用到的密钥交换算法只能是ECDHE。

AES256-GCM-SHA384，服务端CPU占比100%，结果：

客户端（CPU核数标识） 4 2 第一次 12.144 15.737 第二次 12.133 15.452 第三次 11.902 16.145 第四次 11.614 16.133 

ECDHE-RSA-AES256-GCM-SHA384，服务端CPU占比100%，结果：

客户端（CPU核数标识） 4 2 第一次 11.950 16.213 第二次 12.488 16.666 第三次 12.167 16.378 第四次 13.784 16.484 

从表格中的数据可以看出ECDHE与RSA的性能差不多。ECDHE比RSA要多了一次端到端的传输，还会用到RSA对DH参数进行签名和验证；而RSA密钥交换则会使用到RSA的加密/解密，具体可看如下CloudFlare的两张图，图片来自[Keyless SSL: The Nitty Gritty Technical Details][15]：

> ECDHE支持[> 前向保密（Forward Secrecy）][16]> ，简单理解：中间人可以保存下来客户端和服务端之间的所有通信数据，如果使用RSA握手，那么未来某一天，中间人如果获取到了服务端的私钥，就可以解密所有之前采集的通信数据了；如果采用ECDHE握手的话，就可以避免这个问题。而且使用ECDHE握手的话，还有可能开启TLS false start的特性（下文中会提到）。

RSA握手：

![][17]

ECDHE握手：

![][18]

所以密钥交换算法ECDHE会更好些。

**对称加密：AES256-GCM vs AES256 vs AES128-GCM vs 3DES**

测试环境同上，但只使用了4核一台客户端机器发请求，ab参数为ab -n 2000 -c 10，ab实例4个，测试页面153K。因为是要压测对应用层数据的加密解密性能，所以连接数少，但每个连接的请求数多。

ECDHE-RSA-AES256-GCM-SHA384，服务端CPU占比94%，结果：

客户端（CPU核数标识） 4 第一次 17.972 第二次 18.863 第三次 18.761 第四次 19.345 

ECDHE-RSA-AES256-SHA384，服务端CPU占比98%，结果：

客户端（CPU核数标识） 4 第一次 20.490 第二次 19.575 第三次 19.725 第四次 20.262 

ECDHE-RSA-AES128-GCM-SHA256，服务端CPU占比92%，结果：

客户端（CPU核数标识） 4 第一次 17.886 第二次 18.449 第三次 17.897 第四次 18.371 

DES-CBC3-SHA，服务端CPU占比100%，结果（太慢了，就测了两个=。=）：

客户端（CPU核数标识） 4 第一次 52.262 第二次 51.476 

从表格中的数据可以看出AES128GCM > AES256GCM > AES256 > 3DES。

**消息认证码：SHA256 vs SHA1 vs AEAD**

测试环境同上。

AES256-SHA256，服务端CPU占比100%，结果：

客户端（CPU核数标识） 4 第一次 18.544 第二次 18.309 第三次 18.594 第四次 18.670 

AES256-SHA，服务端CPU占比98%，结果：

客户端（CPU核数标识） 4 第一次 15.418 第二次 15.071 第三次 16.614 第四次 16.146 

AES256-GCM-SHA384，服务端CPU占比95%，结果：

客户端（CPU核数标识） 4 第一次 14.443 第二次 15.669 第三次 15.880 第四次 15.960 

从结果中可以看出AES256-GCM-SHA384 > AES256-SHA > AES256-SHA256。

#### 会话恢复

- - -

##### Session Cache

客户端希望恢复先前的session，或者复制一个存在的session，可以在ClientHello中带上Session ID，如果服务端能够在它的Session Cache中找到相应的Session ID的session-state（存储协商好的密码套件等信息），并且愿意使用该Session ID重建连接，那么服务端会发送一个带有相同Session ID的ServerHello。

![][19]

目前Nginx 只支持单机Session Cache，Openresty 支持分布式Session Cache，但处于实验阶段。

##### Session Ticket

Session Cache需要服务端缓存Session相关的信息，对服务端存在存取压力，而且还有分布式Session Cache问题。 对于支持Session Ticket的客户端，服务端可以通过某种机制将session-state加密后作为ticket发给客户端。客户端凭借该ticket就可以恢复先前的会话了。

> 类似于HTTP中用[> Json Web TOken][20]> 作为cookie-session的另一种选择。

![][21]

#### OCSP（在线证书状态协议） stapling

- - -

当客户端在握手环节接受到服务端的证书时，除了对证书进行签名验证，还需要知道证书是否被吊销了，那么需要向证书中指定的OCSP url发送OCSP查询请求。

![][22]

对于同一份服务端证书，如果每个客户端都自己去查询一次证书状态就浪费了。所以，OCSP stapling就是为了解决这一问题，由服务端查询到证书状态（通常会缓存一段时间），并返回给客户端（客户端会在本地校验这个证书状态是否真实）。

![][23]

在nginx的配置中，可以选择性的配置是否对OCSP response做校验，防止将非法的证书状态发送给客户端。如果设置了校验，ssl_trusted_certificate参数需要为包含所有中间证书+根证书的文件。

如下图是对nginx请求OCSP Server的抓包，可以看到发了个http的ocsp请求：

![][24]

下图是对nginx在发送证书给客户端时，带上的证书状态的抓包：

![][25]

#### TLS缓冲区调优

- - -

nginx默认的ssl_buffer_size是16K（TLS Record Layer最大的分片），即一个TLS Record的大小，如果HTTP的数据是160K，那么就会被拆分为10个TLS Record（每个TLS Record会被TCP层拆分为多个TCP包传输）发送给客户端。

![][26]

如果TLS Record Size过大的话，拆分的TCP包也会较多，传输时，如果出现TCP丢包，整个TLS Record到达客户端的时间就会加长，客户端必须等待完整的TLS Record收到才能进行解密。

![][27]

如果TLS Record Size小一些的话，TCP丢包影响的TLS Record占比就会小很多，到达客户端的TLS Record就会多些，客户端干等着的时间就相对少了。但是，TLS Record Head的负载就增加了，可能还会降低连接的吞吐量。

假设ssl_buffer_size设置为1460byte：

![][28]

可以看下这篇文章关于：[Nginx TLS 首字节的优化][29]

通常，在TCP慢启动的过程中，TLS Record Size小点好，因为这个时候TCP连接的拥塞窗口cwnd较小，TCP连接吞吐量也小。而在TCP连接结束慢启动之后，TLS Record Size就可以增大一些了，因为这个时候吞吐量上来了。所以更希望能够动态的调整nginx中ssl_buffer_size的大小，目前官方nginx还不支持，不过cloudflare为nginx打了个patch，以支持动态的调整TLS Record Size：[Optimizing TLS over TCP to reduce latency][30]

#### TLS False Start

- - -

某一端在发送 Change Cipher Spec、Finished 之后，可以立即发送应用数据，无需等待另一端的 Change Cipher Spec、Finished 。这样，应用数据的发送实际上并未等到握手全部完成，从而节省出一个RTT时间。

完整握手时，Client Side False Start：

![][31]

简短握手时，Server Side False Start：

![][32]

可以看下这篇文章：[TLS False Start究竟是如何加速网站的][33] 和 [Transport Layer Security (TLS) False Start][34]

> RFC7918中并没有对Server Side False Start进行定义（其之前的草案中就有提到，draft-bmoeller-tls-falsestart-00/01），文中的说明：However, if the server sends application data first, the abbreviated handshake adds two round-trip times, and this could be reduced to just one added round-trip time by doing a server-side False Start. There is little need for this in practice, so this document does not consider server-side False Starts further.

> 可能是在之前的HTTP 1场景下，对Server Side False Start的需求并不强烈，或者说实践不多（当然其他应用层协议可能会有，例如websocket）。

Client Side False Start需要的条件：

* 客户端和服务端都需要支持NPN/ALPN（浏览器要求）
* 需要采用支持前向保密的密码套件，即使用ECDHE进行密钥交换（RFC7918中有规定）

#### 其他优化

- - -

* TCP优化，毕竟SSL数据也是基于TCP进行传输的
* 证书优化，采用ECDSA证书、服务器发送给客户端的证书链包含所有中间证书
* 硬件配置优化，例如使用SSL加速器

#### 总结

- - -

本文是个人近段时间学习到的关于HTTPS性能优化的总结，推荐阅读[HTTPS权威指南][35]和[High Performance Browser Networking][36]以了解更多内容。

推荐的密码套件列表：

    openssl ciphers -v 'ECDHE+ECDSA ECDHE AESGCM AES HIGH MEDIUM !kDH !kECDH !aNULL !eNULL !LOW !MD5 !EXP !DSS !PSK !SRP !CAMELLIA !IDEA !SEED !RC4 !3DES'
    

其他额外的密码套件，比如需要支持IE6，可以放在密码套件列表末尾。

自己写了个go程序用于检测密码套件列表支持/不支持的客户端：[sslciphersuitescheck][37]

![][38]

[0]: ./img/201705130101.png
[1]: https://www.ietf.org/rfc/rfc5246.txt
[2]: https://en.wikipedia.org/wiki/Digital_signature
[3]: https://en.wikipedia.org/wiki/Stream_cipher
[4]: https://en.wikipedia.org/wiki/Block_cipher
[5]: https://en.wikipedia.org/wiki/Authenticated_encryption
[6]: https://en.wikipedia.org/wiki/Public-key_cryptography
[7]: https://en.wikipedia.org/wiki/Message_authentication_code
[8]: https://en.wikipedia.org/wiki/Key_exchange
[9]: http://www.freebuf.com/articles/web/5636.html
[10]: https://en.wikipedia.org/wiki/Key_size
[11]: https://www.keylength.com/
[12]: https://www.ssllabs.com/ssltest/clients.html
[13]: https://www.ssllabs.com/ssltest/
[14]: ./img/201705130102.png
[15]: https://blog.cloudflare.com/keyless-ssl-the-nitty-gritty-technical-details/
[16]: https://en.wikipedia.org/wiki/Forward_secrecy
[17]: ./img/201705130105.png
[18]: ./img/201705130106.png
[19]: ./img/201705130103.png
[20]: https://jwt.io/
[21]: ./img/201705130104.png
[22]: ./img/201705130107.png
[23]: ./img/201705130108.png
[24]: ./img/201705130109.png
[25]: ./img/201705130110.png
[26]: ./img/201705130111.png
[27]: ./img/201705130112.png
[28]: ./img/201705130113.png
[29]: https://www.igvita.com/2013/12/16/optimizing-nginx-tls-time-to-first-byte/
[30]: https://blog.cloudflare.com/optimizing-tls-over-tcp-to-reduce-latency/
[31]: ./img/201705130116.png
[32]: ./img/201705130119.png
[33]: https://cnodejs.org/topic/564af9751ba2ef107f854d3e
[34]: https://tools.ietf.org/html/rfc7918
[35]: http://www.ituring.com.cn/book/1734
[36]: http://chimera.labs.oreilly.com/books/1230000000545/index.html
[37]: https://github.com/yangxikun/sslciphersuitescheck
[38]: ./img/201705130120.png
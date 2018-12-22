## 基于 Nginx 的 HTTPS 性能优化实践

来源：[https://segmentfault.com/a/1190000017270510](https://segmentfault.com/a/1190000017270510)

 **`摘要：`**  随着相关浏览器对HTTP协议的“不安全”、红色页面警告等严格措施的出台，以及向 iOS 应用的 ATS 要求和微信、支付宝小程序强制 HTTPS 需求，以及在合规方面如等级保护对传输安全性的要求都在推动 HTTPS 的发展。## 前言

分享一个[卓见云][10]的较多客户遇到HTTPS优化案例。

随着相关浏览器对HTTP协议的“不安全”、红色页面警告等严格措施的出台，以及向 iOS 应用的 ATS 要求和微信、支付宝小程序强制 HTTPS 需求，以及在合规方面如等级保护对传输安全性的要求都在推动 HTTPS 的发展。

虽然 HTTPS 优化了网站访问体验（防劫持）以及让传输更加安全，但是很多网站主赶鸭子上架式的使用了 HTTPS 后往往都会遇到诸如：页面加载速度变慢、服务器负载过高以及证书过期不及时更新等问题。

所以本文就来探讨一下 HTTPS 的优化实践。
## 选型

其实像 Apache Httpd、LigHttpd、Canddy 等 Web 服务软件都可以设置 HTTPS，但是在相应的扩展生态和更新率上都不如 Nginx。 Nginx 作为大型互联网网站的 Web 入口软件有着广泛的支持率，例如阿里系的 Tengine、CloudFlare 的 cloudflare-nginx、又拍云用的 OpenResty 都是基于 Nginx 而来的，Nginx 是接受过大规模访问验证的。同时大家也将自己开发的组件回馈给 Nginx 社区，让 Nginx 有着非常良好的扩展生态。

![][0]

​

所以说 Nginx 是一款很好的 Web 服务软件，选择 Nginx 在提升性能的同时能极大的降低我们的扩展成本。
## 新功能

围绕 Web 服务已经有非常多的新功能需要我们关注并应用了，这里先罗列相关新功能。
### HTTP/2

相比廉颇老矣的 HTTP/1.x，HTTP/2 在底层传输做了很大的改动和优化包括有：


* 每个服务器只用一个连接，节省多次建立连接的时间，在TLS上效果尤为明显
* 加速 TLS 交付，HTTP/2 只耗时一次 TLS 握手，通过一个连接上的多路利用实现最佳性能
* 更安全，通过减少 TLS 的性能损失，让更多应用使用 TLS，从而让用户信息更安全


![][1]

在 Akamai 的 HTTP/2 DEMO中，加载300张图片，HTTP/2 的优越性极大的显现了出来，在 HTTP/1.X 需要 14.8s 的操作中，HTTP/2 仅需不到1s。

HTTP/2 现在已经获得了绝大多数的现代浏览器的支持。只要我们保证 Nginx 版本大于 1.9.5 即可。当然建议保持最新的 Nginx 稳定版本以便更新相关补丁。同时 HTTP/2 在现代浏览器的支持上还需要 OpenSSL 版本大于 1.0.2。
### TLS 1.3

和 HTTP/1.x 一样，目前受到主流支持的 TLS 协议版本是 1.1 和 1.2，分别发布于 2006年和2008年，也都已经落后于时代的需求了。在2018年8月份，IETF终于宣布TLS 1.3规范正式发布了，标准规范（Standards Track）定义在 [rfc8446][11]。

![][2]

![][3]

TLS 1.3 相较之前版本的优化内容有：


* **`握手时间：`** 同等情况下，TLSv1.3 比 TLSv1.2 少一个 RTT
* **`应用数据：`** 在会话复用场景下，支持 0-RTT 发送应用数据
* **`握手消息：`** 从 ServerHello 之后都是密文。
* **`会话复用机制：`** 弃用了 Session ID 方式的会话复用，采用 PSK 机制的会话复用。
* **`密钥算法：`** TLSv1.3 只支持 PFS （即完全前向安全）的密钥交换算法，禁用 RSA 这种密钥交换算法。对称密钥算法只采用 AEAD 类型的加密算法，禁用CBC 模式的 AES、RC4 算法。
* **`密钥导出算法：`** TLSv1.3 使用新设计的叫做 HKDF 的算法，而 TLSv1.2 是使用PRF算法，稍后我们再来看看这两种算法的差别。


总结一下就是在更安全的基础上还做到了更快，目前 TLS 1.3 的重要实现是 OpenSSL 1.1.1 开始支持了，并且 1.1.1 还是一个 LTS 版本，未来的 RHEL8、Debian10 都将其作为主要支持版本。在 Nginx 上的实现需要 Nginx 1.13+。
### Brotli

Brotli 是由 Google 于 2015 年 9 月推出的无损压缩算法，它通过用变种的 LZ77 算法，Huffman 编码和二阶文本建模进行数据压缩，是一种压缩比很高的压缩方法。
 **`根据Google 发布的研究报告，Brotli 具有如下特点：`** 


* 针对常见的 Web 资源内容，Brotli 的性能要比 Gzip 好 17-25%；
* Brotli 压缩级别为 1 时，压缩速度是最快的，而且此时压缩率比 gzip 压缩等级为 9（最高）时还要高；
* 在处理不同 HTML 文档时，brotli 依然提供了非常高的压缩率；

 **`在兼容 GZIP 的同时，相较 GZIP：`** 


* JavaScript 上缩小 14%
* HTML上缩小 21%
* CSS上缩小 17%


Brotli 的支持必须依赖 HTTPS，不过换句话说就是只有在 HTTPS 下才能实现 Brotli。
### ECC 证书

椭圆曲线密码学（Elliptic curve cryptography，缩写为ECC），一种建立公开金钥加密的算法，基于椭圆曲线数学。椭圆曲线在密码学中的使用是在1985年由Neal Koblitz和Victor Miller分别独立提出的。

内置 ECDSA 公钥的证书一般被称之为 ECC 证书，内置 RSA 公钥的证书就是 RSA 证书。由于 256 位 ECC Key 在安全性上等同于 3072 位 RSA Key，加上 ECC 运算速度更快，ECDHE 密钥交换 + ECDSA 数字签名无疑是最好的选择。 **`由于同等安全条件下，ECC 算法所需的 Key 更短，所以 ECC 证书文件体积比 RSA 证书要小一些。`** 

ECC 证书不仅仅可以用于 HTTPS 场景当中，理论上可以代替所有 RSA 证书的应用场景，如 SSH 密钥登陆、SMTP 的 TLS 发件等。
 **`不过使用 ECC 证书有两个点需要注意：`** 

一、 并不是每一个证书类型都支持的，一般商业证书中带 **`增强型`** 字眼的才支持ECC证书的签发。

![][4]

二、 ECC证书在一些场景中可能还不被支持，因为一些产品或者软件可能还不支持 ECC。 这时候就要虚线解决问题了，例如针对部分旧操作系统和浏览器不支持ECC，可以通过ECC+RSA双证书模式来解决问题。
## 安装
## 下载源码
 **`综合上述我们要用到的新特性，我们整合一下需求：`** 

HTTP/2 要求 Nginx 1.9.5+，，OpenSSL 1.0.2+

TLS 1.3 要求 Nginx 1.13+，OpenSSL 1.1.1+

Brotli 要求 HTTPS，并在 Nginx 中添加扩展支持

ECC 双证书 要求 Nginx 1.11+

这里 Nginx，我个人推荐 1.15+，因为 1.14 虽然已经能支持TLS1.3了，但是一些 TLS1.3 的进阶特性还只在 1.15+ 中提供。
 **`然后我们定义一下版本号：`** 

```
# Version
OpenSSLVersion='openssl-1.1.1a';
nginxVersion='nginx-1.14.1';
```
 **`建议去官网随时关注最新版：`** 

[http://nginx.org/en/download.html][12]

[https://www.openssl.org/source/][13]

[https://github.com/eustas/ngx_brotli/releases][14]
### Nginx

```
cd /opt
wget http://nginx.org/download/$nginxVersion.tar.gz
tar xzf $nginxVersion.tar.gz
```
### OpenSSL

```
cd /opt
wget https://www.openssl.org/source/$OpenSSLVersion.tar.gz
tar xzf $OpenSSLVersion.tar.gz
```
### Brotli

```
cd /opt
git clone https://github.com/eustas/ngx_brotli.git
cd ngx_brotli
git submodule update --init --recursive
```
## 编译

```
cd /opt/$nginxVersion/
./configure \
--prefix=/usr/local/nginx \  ## 编译后安装的目录位置
--with-openssl=/opt/$OpenSSLVersion  \ ## 指定单独编译入 OpenSSL 的源码位置
--with-openssl-opt=enable-tls1_3 \ ## 开启 TLS 1.3 支持
--with-http_v2_module \ ## 开启 HTTP/2 
--with-http_ssl_module \ ## 开启 HTTPS 支持
--with-http_gzip_static_module \ ## 开启 GZip 压缩
--add-module=/opt/ngx_brotli ## 编译入 ngx_BroTli 扩展

make && make install ## 编译并安装
```

后续还有相关变量设置和设置服务、开启启动等步骤，篇幅限制就省略了，这篇文章有介绍在 Ubuntu 下的 Nginx 编译：[https://www.mf8.biz/ubuntu-nginx/][15] 。
## 配置

接下来我们需要修改配置文件。
### HTTP2

```nginx
listen 443 ssl http2;
```

只要在 `server{} ` 下的`lisen 443 ssl` 后添加 `http2` 即可。而且从 1.15 开始，只要写了这一句话就不需要再写 `ssl on` 了，很多小伙伴可能用了 1.15+ 以后衍用原配置文件会报错，就是因为这一点。
### TLS 1.3

```nginx
ssl_protocols    TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;
```

如果不打算继续支持 IE8，或者一些合规的要求，可以去掉`TLSv1`。

然后我们再修改对应的加密算法，加入TLS1.3引入的新算法：

```
ssl_ciphers        TLS13-AES-256-GCM-SHA384:TLS13-CHACHA20-POLY1305-SHA256:TLS13-AES-128-GCM-SHA256:TLS13-AES-128-CCM-8-SHA256:TLS13-AES-128-CCM-SHA256:EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+ECDSA+AES128:EECDH+aRSA+AES128:RSA+AES128:EECDH+ECDSA+AES256:EECDH+aRSA+AES256:RSA+AES256:EECDH+ECDSA+3DES:EECDH+aRSA+3DES:RSA+3DES:!MD5;
```

如果不打算继续支持 IE8，可以去掉包含 `3DES` 的 Cipher Suite。

默认情况下 Nginx 因为安全原因，没有开启 TLS 1.3 0-RTT，可以通过添加 `ssl_early_data on;` 指令开启 0-RTT的支持。

————
 **`实验性尝试`** 

众所周知，TLS1.3 由于更新了很久，很多浏览器的旧版本依旧只支持 Draft 版本，如 23 26 28 分别在 Chrome、FirFox 上有支持，反而正式版由于草案出来很久，导致TLS1.3在浏览器上兼容性不少太好。

可以使用 [https://github.com/hakasenyang/openssl-patch/][16] 提供的 OpenSSL Patch 让 OpenSSL 1.1.1 同时支持草案23,26,28和正式版输出。 不过由于不是官方脚本，稳定性和安全性有待考量。
### ECC双证书

双证书配置的很简单了，保证域名的证书有RSA和ECC各一份即可。

```nginx
  ##证书部分
  ssl_certificate /usr/local/nginx/conf/ssl/www.mf8.biz-ecc.crt; #ECC证书
  ssl_certificate_key /usr/local/nginx/conf/ssl/www.mf8.biz-ecc.key; #ECC密钥
  ssl_certificate /usr/local/nginx/conf/ssl/www.mf8.biz.crt; #RSA证书
  ssl_certificate_key /usr/local/nginx/conf/ssl/www.mf8.biz.key; #RSA密钥
```
### Brotli

需要在对应配置文件中，添加下面代码即可：

```nginx
    brotli                     on;
    brotli_comp_level          6;
    brotli_min_length          1k;
    brotli_types               text/plain text/css text/xml text/javascript text/x-component application/json application/javascript application/x-javascript application/xml application/xhtml+xml application/rss+xml application/atom+xml application/x-font-ttf application/vnd.ms-fontobject image/svg+xml image/x-icon font/opentype;
```

为了防止大家看糊涂了，放一个完整的 `server{}`供大家参考：

```nginx
    server {
        listen       443 ssl http2; # 开启 http/2
        server_name  mf8.biz www.mf8.biz;

        #证书部分
        ssl_certificate     /usr/local/nginx/conf/ssl/www.mf8.biz-ecc.crt; #ECC证书
        ssl_certificate_key /usr/local/nginx/conf/ssl/www.mf8.biz-ecc.key; #ECC密钥
        ssl_certificate     /usr/local/nginx/conf/ssl/www.mf8.biz.crt; #RSA证书
        sl_certificate_key  /usr/local/nginx/conf/ssl/www.mf8.biz.key; #RSA密钥

        #TLS 握手优化
        ssl_session_cache    shared:SSL:1m;
        ssl_session_timeout  5m;
        keepalive_timeout    75s;
        keepalive_requests   100;

        #TLS 版本控制
        ssl_protocols    TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;
        ssl_ciphers        'TLS13-AES-256-GCM-SHA384:TLS13-CHACHA20-POLY1305-SHA256:TLS13-AES-128-GCM-SHA256:TLS13-AES-128-CCM-8-SHA256:TLS13-AES-128-CCM-SHA256:EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+ECDSA+AES128:EECDH+aRSA+AES128:RSA+AES128:EECDH+ECDSA+AES256:EECDH+aRSA+AES256:RSA+AES256:EECDH+ECDSA+3DES:EECDH+aRSA+3DES:RSA+3DES:!MD5';

        # 开启 1.3 o-RTT
        ssl_early_data     on;

        # GZip 和 Brotli
        gzip            on;
        gzip_comp_level    6;
        gzip_min_length    1k;
        gzip_types        text/plain text/css text/xml text/javascript text/x-component application/json application/javascript application/x-javascript application/xml application/xhtml+xml application/rss+xml application/atom+xml application/x-font-ttf application/vnd.ms-fontobject image/svg+xml image/x-icon font/opentype;
        brotli            on;
        brotli_comp_level    6;
        brotli_min_length    1k;
        brotli_types    text/plain text/css text/xml text/javascript text/x-component application/json application/javascript application/x-javascript application/xml application/xhtml+xml application/rss+xml application/atom+xml application/x-font-ttf application/vnd.ms-fontobject image/svg+xml image/x-icon font/opentype;

        location / {
            root   html;
            index  index.html index.htm;
        }
    }
```

先验证一下配置文件是否有误：

```
nginx -t
```

如果反馈的是：

```
nginx: the configuration file /usr/local/nginx/conf/nginx.conf syntax is ok
nginx: configuration file /usr/local/nginx/conf/nginx.conf test is successful
```

就可以重启 Nginx ，然后到对应网站中去查看效果了。
## 验证
### HTTP/2

通过浏览器的 **`开发者工具`** ，我们可以在  **`Network`**  栏目中看到  **`Protocol`**  中显示 `h2` 有无来判断。

![][5]
### TLS 1.3

老地方，我们可以通过浏览器的 **`开发者工具`**  中的  **`Security`**  栏目看到  **`Connection`**  栏目下是否有显示 TLS 1.3

![][6]
### ECC 双证书

ECC 双证书配置了以后无非就是在旧浏览器设别上的验证了。这里用足够老的上古XP虚拟机来给大家证明一波。

XP系统上：

![][7]

现代操作系统上的：

![][8]
### Brotli

通过浏览器的 **`开发者工具`** ，我们可以在  **`Network`**  栏目中，打开具体页面的头信息，看到  **`accept-encoding`**  中有 br 字眼就行。

![][9]
## 总结

通过上述手段应该可以让 HTTPS 访问的体验优化不少，而且会比没做 HTTPS 的网站访问可能更快。

这样的模式比较适合云服务器单机或者简单集群上搭建，如果有应用 SLB 七层代理、WAF、CDN 这样的产品可能会让我们的这些操作都白费。 我们的这几项操作都是自建的 Web 七层服务，如果有设置 SLB 七层代理、WAF、CDN 这样设置在云服务器之前就会被覆盖掉。

由于 SLB 七层和CDN这样的产品会更加追求广泛的兼容性和稳定性并不会第一时间就用上上述的这些新特性（HTTP/2 是普遍有的），但是他们都配备了阿里云的 Tengine 的外部专用算法加速硬件如 Intel® QuickAssist Technology(QAT) 加速器可以显著提高SSL/TLS握手阶段性能。 所有 HTTPS 的加密解密都在 SLB 或 CDN 上完成，而不会落到ECS上，可以显著降低 ECS 的负载压力，并且提升访问体验。

目前云上的网络产品中能支持四层的都是可以继续兼容我们这套设计的，例如：SLB 的四层转发（TCP UDP）、DDOS高防的四层转发。

-----

本文作者：妙正灰[阅读原文][17]

本文为云栖社区原创内容，未经允许不得转载。

[10]: https://www.juncdt.com/
[11]: http://link.zhihu.com/?target=https%3A//tools.ietf.org/html/rfc8446
[12]: http://nginx.org/en/download.html
[13]: https://www.openssl.org/source/
[14]: https://github.com/eustas/ngx_brotli/releases
[15]: https://www.mf8.biz/ubuntu-nginx/
[16]: https://github.com/hakasenyang/openssl-patch/
[17]: http://click.aliyun.com/m/1000028438/
[0]: ../img/1460000017270513.png
[1]: ../img/1460000017270514.png
[2]: ../img/1460000017270515.png
[3]: ../img/1460000017270516.png
[4]: ../img/1460000017270517.png
[5]: ../img/1460000017270518.png
[6]: ../img/1460000017270519.png
[7]: ../img/1460000017270520.png
[8]: ../img/1460000017270521.png
[9]: ../img/1460000017270522.png
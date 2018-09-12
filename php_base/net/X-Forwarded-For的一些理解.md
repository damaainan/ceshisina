# X-Forwarded-For的一些理解

2017.02.03 14:49  字数 1909 

X-Forwarded-For 是一个 HTTP 扩展头部，主要是为了让 Web 服务器获取访问用户的真实 IP 地址（其实这个真实未必是真实的，后面会说到）。

那为什么 Web 服务器只有通过 X-Forwarded-For 头才能获取真实的 IP？  
这里用 PHP 语言来说明，不明白原理的开发者为了获取客户 IP，会使用 `$_SERVER['REMOTE_ADDR']` 变量，这个服务器变量表示和 Web 服务器握手的 IP 是什么（这个不能伪造）。  
但是很多用户都通过代理来访问服务器的，那么假如使用该全局变量，PHP获取到的 IP 就是代理服务器的 IP（不是用户的）。

可能很多人看的晕乎乎的，那么看看一个请求可能经过的路径：

    客户端=>（正向代理=>透明代理=>服务器反向代理=>）Web服务器

其中正向代理、透明代理、服务器反向代理这三个环节并不一定存在。

* 什么是正向代理呢，很多企业会在自己的出口网关上设置代理（主要是为了加速和节省流量）。
* 透明代理可能是用户自己设置的代理（比如为了翻墙，这样也绕开了公司的正向代理）。
* 服务器反向代理是部署在 Web 服务器前面的，主要原因是为了负载均衡和安全考虑。

现在假设几种情况：

* 假如客户端直接连接 Web 服务器（假设 Web 服务器有公网地址），则 `$_SERVER['REMOTE_ADDR']` 获取到的是客户端的真实 IP 。
* 假设 Web 服务器前部署了反向代理（比如 Nginx），则 `$_SERVER['REMOTE_ADDR']` 获取到的是反向代理设备的 IP（Nginx）。
* 假设客户端通过正向代理直接连接 Web 服务器（假设 Web 服务器有公网地址），则 `$_SERVER['REMOTE_ADDR']` 获取到的正向代理设备的 IP 。

其实这里的知识点很多，记住一点就行了，`$_SERVER['REMOTE_ADDR']` 获取到的 IP 是 Web 服务器 TCP 连接的 IP（这个不能伪造，一般 Web 服务器也不会修改这个头）。

#### X-Forwarded-For

从上面大家也看出来了，因为有了各种代理，才会导致 REMOTE_ADDR 这个全局变量产生了一定的歧义，为了让 Web 服务器获取到真实的客户端 IP，X-Forwarded-For 出现了，这个协议头也是由 Squid 起草的（Squid 应该是最早的代理软件之一）。

这个协议头的格式：

    X-Forwarded-For: client, proxy1, proxy2

client 表示用户的真实 IP，每经过一次代理服务器，代理服务器会在这个头增加用户的 IP（有点拗口）。  
注意最后一个代理服务器请求 Web 服务器的时候是不会将自己的 IP 附加到 X-Forwarded-For 头上的，最后一个代理服务器的 IP 地址应该通过`$_SERVER['REMOTE_ADDR']`获取。

举个例子：  
用户的 IP 为（A）,分别经过两个代理服务器（B，C），最后到达 Web 服务器，那么Web 服务器接收到的 X-Forwarded-For 就是 A,B。

那么 PHP 如何获取真实客户端 IP 呢？
```php
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
    if (!$ip) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';
    }
    $a = explode('|', str_replace(',', '|', $ip));
    $ip = trim($a[0]);
```
这里预先说明下，假设这两个代理服务器都是好的代理服务器，没有伪造 HTTP_X_FORWARDED_FOR。

#### 配置反向代理

上面一直在说代理，大家可能觉得这到底有啥用？不同类型的代理有不同的目的，对于正向代理来说主要是为了加速并且让局域网的用户有一个真实的 IP 地址，而透明代理则主要是为了一些其他的目的（比如就是不想让别人知道我的 IP），而反向代理主要是企业内部安全和负载均衡考虑，这里主要说下如何配置反向代理。

现在只要是具备一定规模的网站（Web 服务器大于 1 台），为了安全和负载均衡考虑都会在 Web 服务器前面部署反向代理，反向代理有 HAproxy，Nginx，Apache 等等。

这里通过 Nginx 来部署反向代理：
```nginx
    proxy_set_header Host $http_host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
```
简单的解释下：

* X-Forwarded-For 表示 Nginx 接收到的头，原样的转发过来（假如不转发，Web 服务器就不能获取这个头）。
* X-Real-IP，这是一个内部协议头（就是反向代理服务器和 Web 服务器约定的），这个头表示连接反向代理服务器的 IP 地址（这个地址不能伪造），其实个人觉得为了让 PHP 代码保持无二义性，不应该这样设置，可以修改为 proxy_set_header REMOTE_ADDR $remote_addr;

#### Apache WEB 服务器的 Access 日志如何获取 X-Forwarded-For 头

其实写这篇文章主要是因为自己在 Apache Web 服务器上获取不到 X-Forwarded-For（上层的负载均衡设备确定传递了），搜索了下（在 Apache 官方文档并没有找到解决方案），解决如下：

    LogFormat "%{X-Forwarded-For}i %a %h %A %l %u %t \"%r\" %>s %b \"%{Referer}i\"
     \"%{User-Agent}i\"" combined

#### X-Forwarded-For 安全性

那么很多同学会说，通过 X-Forwarded-For 就能获取到用户的真实 IP，是不是万事大吉了，对于 Web 服务器来说，安全有两个纬度，第一个纬度是 REMOTE_ADDR 这个头，这个头不能伪造。第二个纬度就是 X-Forwarded-For，但是这个头是可以伪造的。

那么谁在伪造呢？，我们分别看下：

正向代理一般是公司加速使用的，假如没有特殊的目的，不应该传递 X-Forwarded-For 头，因为它的上层连接是内部 IP，不应该暴露出去，当然它也可以透明的传递这个头的值（而这个值用户可以伪造）。

透明代理，这个可能是用户自己搭建的（比如翻墙），而且在一个用户的请求中，可能有多个透明代理，这时候透明代理就抓瞎了，为了让自己尽量的正确，也会透明的传递这个头的值（而这个值用户可以伪造），当然一些不法企业或者人员，为了一些目的，会改下这个头的值（比如来自世界各地的 IP 地址）。

反向代理，Web 服务器前的反向代理服务器是不会伪造的（同一个公司的），一般会原样传递这个头的值。

那么对应用程序来说，既然这个值不能完全相信，该怎么办呢？这取决于应用的性质：

假如提供的服务可能就是一些非机密服务，也不需要知道用户的真实 IP，那么建议应用程序或者 Web 服务器对 REMOTE_ADDR 做一些限制，比如进行限速等等，也可以放行一些白名单的代理 IP，但是这些白名单 IP 就太难衡量了。

假设你的服务很重要，比如抽奖（一个 IP 只能一次抽奖），这时候你可能想通过 X-Forwarded-For 来获取用户的真实 IP（假如使用 REMOTE_ADDR 则会误杀一片），但是由于 X-Forwarded-For 可能会伪造，所以其实并没有什么好的办法，只能在应用层进行处理了。


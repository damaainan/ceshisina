## Apache 与 Nginx 性能对比：Web 服务器优化技术

来源：[https://segmentfault.com/a/1190000016071075](https://segmentfault.com/a/1190000016071075)

译文首发于 [Apache 与 Nginx 性能对比：Web 服务器优化技术][9]，转载请注明出处。
多年前 [Apache 基金会 Web 服务器][10] 简称「Apache」,由于使用者众多几乎等同于「Web 服务器」。httpd（含义是简单的 http 进程）是它在 Linux 系统上的守护进程 - 同时它被预装到主流的 Linux 发行版中。

Apache 初版于 1995 年发布，它在 [维基百科][11] 描述如下，「它在万维网（WWW）发展初期发挥了至关重要的作用」。从 [W3techs][12] 统计结果来看，它依然是最常用的 Web 服务器软件。不过，依据 [过去十年的发展趋势][13] 和 [与其它服务器解决方案比较][14] 的报告的结果来分析，不难发现它的市场份额正在逐年下降。尽管，[Netcraft][15] 和 [Builtwith][16] 这两家提供的报告略有不同，但不得不承认 Apache 市场份额的缩减与 Nginx 服务器份额在增长这一事实。

 **`Nginx`**  读作「engine x」- 由 [Igor Sysoev][17] 在 2004 年发布，最初的愿景就是取代 Apache 在 Web 服务器市场上的领导地位。在 Nginx 的网站上有一篇值得一读的 [文章][18]，对两款服务器进行了比较。一开始 Nginx 只是作为 Apache 某些功能的补充，主要提供静态文件服务支持。得益于它积极的扩展在 Web 服务器领域相关功能的全方位支持，这使得它能够稳步增长。

Nginx 通常被用作 [反向代理][19]、[负载均衡][20] 和 [HTTP 缓存][21] 服务器。CDN 和 视频提供商使用它来构将性能强劲的内容分发系统（CDN: content delivery system）。

Apache 在其不短的发展历程中，提供了许多 [有用的模块][22]。众所周知管理 Apache 服务器对开发者极其友好。[动态模块加载][23] 能够在无需重新编译主服务器文件的基础上，将模块编译并添加到 Apache 扩展中。通常，这些模块位于 Linux 发行版仓库中，在使用系统包管理器安装后，便可以通过诸如 [a2enmod][24] 这样的命令，将其添加到扩展中。Nginx 服务器到目前为止，依然无法灵活的实现动态添加模块的功能。当我们阅读 [如何在 Nginx 服务器设置 HTTP/2 指南][25] 时，你就会发现模块需要在构建 Nginx 时，通过设置参数选项，才能将其添加进 Nginx 服务器。

另一个让 Apache 保持住市场份额的功臣就是 [.htaccess 重写文件][26]。它就像 Apache 服务器的万金油一样，使其成为共享托管技术的首选方案，因为 .htaccess 重写支持在目录级别上控制服务器配置。在 Apache 服务器上的每个目录都能够配置自己的 **`.htaccess`**  文件。

在这点上 Nginx 不仅没有相应的解决方案，而且由于重写性能低、命中率不高而 [不被推荐][27]。

![][0]
 1995–2005 Web 服务器市场份额。 [数据由 Netcraft 提供][28] 

[LiteSpeed][29] 即 LSWS 是 Web 服务器市场的另一个竞争者，它兼具 Apache 的灵活性与 Nginx 的性能。支持 Apache 风格的 **`.htaccess`** 、 **`mode_security`**  和 **`mode_rewrite`**  模块，另外它还支持共享设置。它的设计初衷是替代 Apache 服务器，并且能够和 cPanel 和 Plesk 组合使用。从 2015 年开始提供 HTTP/2 支持。

[LiteSpeed 有三个版本][30]，OpenLiteSpeed、LSWS 保准版和 LSWS 企业版。标准版和企业版还提供了可选的 [缓存解决方案][31]，它可以和 Varnish 与 LSCache 一较长短。[LSCache][32] 是服务器内置的缓存解决方案，通过 **`.htaccess`**  重写规则配置进行控制。并且，它还提供了内置预防 [DDoS 攻击的解决方案][33]。这个功能同它的事件驱动架构设计一起成为这款服务器的竞争力保障，不仅能够满足以 [性能为导向的服务提供商需求][34]，还能兼顾小型服务器或网站架设市场。
## 硬件考量（Hardware Considerations）

当我们优化系统时，我们无法忽视硬件配置。无论选择哪种解决方案，我们都需要拥有足够的 RAM，这点至关重要。当 Web 服务器进程或类似 PHP 解释器程序无可用的 RAM 时，它们就会进行交换（swapping）即需要使用硬盘来补充 RAM 内存的不足。这会导致每当访问这块内存区域时都会带来访问延迟。于是便引出了第二个优化点 - **`硬盘`** 。使用 SSD 固态硬盘来构建网站是提升性能的又一关键。此外，我们还应考虑 CPU 可用性和服务器数据中心同目标用户的距离。

想要深入研究硬件优化方法，可以查看 [Dropbox 的好文][35]。
## 监控（Monitoring）

[htop][36] 是一个监控当前服务器性能及每个进程详细信息的实用工具，它能够在 Linux、Unix 和 macOS 系统上运行，并为我们以不同颜色区分出不同的进程状态。

![][1]

其它的监控工具如 [New Relic][37]，提供全套的监控解决方案；[Netdata][38] 一款开源的监控解决方案，兼具扩展性、细粒度指标和可定制的 Web 仪表盘，适用于小型的 VPS 系统和网络服务器的监控。它可以通过邮件、Slack、pushbullet、Telegram 和 Twilio 等方式给任何应用或系统进程发送警告消息。

![][2]

[Monit][39] 是另一款开源的系统监控工具，可以通过配置在重启进程、重启系统或任何我们关心事件时给我们的发送提示信息。
## 系统测试（Testing the System）

[AB][40] - Apache Benchmark - 是一款有 Apache 基金会提供的简单的压测工具，其它压测工具还有 [Siege][41]。[这篇文章][42] 详细讲解了如何同时安装这两款工具，可以阅读 [这篇文章][43] 学习 AB 工具的高级使用技巧，如果需要研究 Siege 可以阅读 [此文][44]。

如果你钟爱 Web 应用，可以使用 [Locust][45] 这款基于 Python 的测试工具，一样可以很方便的对网站进行性能测试。

![][3]

在安装完成 Locust 后，我们需要在项目的根目录下创建一个 **`[locusfile][46]`** :

```python
from locust import HttpLocust, TaskSet, task

class UserBehavior(TaskSet):
    @task(1)
    def index(self):
        self.client.get("/")

    @task(2)
    def shop(self):
        self.client.get("/?page_id=5")

    @task(3)
    def page(self):
        self.client.get("/?page_id=2")

class WebsiteUser(HttpLocust):
    task_set = UserBehavior
    min_wait = 300
    max_wait = 3000
```

然后使用如下命令启动服务：

```LANG
locust --host=https://my-website.com
```

使用压测工具时需要注意：这些工具可能造成 DDoS 攻击，所以需要在测试网站时进行限制。
## Apache 优化技术（Tuning Apache）
### Apache 的 mpm 模块

Apache 可以追溯到 1995 年和互联网的早期阶段，当时的服务器将接收的 HTTP 请求传入到 TCP 连接上并重新生成一个新进程并响应这个请求。当众多的请求被接收，也就意味着需要创建处理它们的 worker 进程。由于创建新 worker 进程的系统开销巨大，所以 Apache 服务器的技术人员设计了 prefork 模式，并预先生成多个 worker 进程解决重新创建的问题。不过将每个进程嵌入到动态语言的解释器（如 mod_php）中依然造成大量的资源消耗，这使得 Apache 服务器经常会出现 [服务器崩溃][47] 的问题。这是因为单个 worker 进程只能同时处理一个连接。

这个模块在 Apache 的 [MPM][48] 系统中称为 [mpm_prefork_module][49]。从 Apache 官网可以了解到，这个模块仅需极少的 [配置][50] 即可完成工作，因为它能够自动调整，其中最关键的是将 **`MaxRequestWorkers`**  指令值配置的足够大，这样可以处理更多的请求，但是还需要保证有每个 worker 进程有足够的物理 RAM 可用。

![][4]

上面的 Locust 压测显示 Apache 创建了大量的进程来处理请求。

不得不说，这个模块是 Apache 声名狼藉的罪魁祸首，它可能导致资源利用率低下的问题。

在 Apache 第二版中引入了两个新的 MPM 模块，试图解决 prefork 模式所带来的问题。即 [worker 模块][51] 或曰 mpm_worker_module 以及 [event 模块][52]。

worker 模块不再基于进程模型，而是一种混合了进程-线程（process-thread）处理模式。下面引用自 [Apache 官网][53]:

单个进程（父进程）负责启动子进程（worder 进程）。子进程负责创建由 **`ThreadsPerChild`**  指令设置的服务器线程，同时还负责监听接收到的请求，并将请求分发给处理线程。
这种模式能提升资源利用率。

在 2.4 版本 Apache 引入了 - [event 模块][52]，这个模块基于 worker 模块创建的，并加入了独立的监听线程来管理 HTTP 请求处理完成后的休眠的 keepalive 连接。它是一种异步非阻塞模型，内存占用小。可以从 [这里][55] 了解这个版本的信息。

我们在虚拟机上安装 WooCommerce 并基于 Apache 2.4 默认的 prefork 和 mod_php 配置发送 1200 请求进行负载测试。

首先，我们在 [https://tools.pingdom.com/][56] 网站对 [libapache2-mod-php7][57] 和 mpm_prefork_module 进行测试：

![][5]

然后，我们对 MPM 的 evnet 模块仅需测试。

这需要将 **`multiverse`**  加入到 **`/etc/apt/sources.list`** ：

```
deb http://archive.ubuntu.com/ubuntu xenial main restricted universe multiverse
deb http://archive.ubuntu.com/ubuntu xenial-updates main restricted universe multiverse
deb http://security.ubuntu.com/ubuntu xenial-security main restricted universe multiverse
deb http://archive.canonical.com/ubuntu xenial partner
```

随后，执行 **`sudo apt-get update`**  来安装 **`libapache2-mod-fastcgi`**  和 php-fpm。

```
sudo apt-get install libapache2-mod-fastcgi php7.0-fpm
```

由于 php-fpm 独立于 Apache 服务器，所以需要重启服务：

```
sudo service start php7.0-fpm
```

然后，关闭 prefork 模块，启用 event 模式和 proxy_fcgi:

```
sudo a2dismod php7.0 mpm_prefork
sudo a2enmod mpm_event proxy_fcgi
```

将下面的代码加入到 Apache 虚拟机：

```apache
<filesmatch "\.php$">
    SetHandler "proxy:fcgi://127.0.0.1:9000/"
</filesmatch>
```

端口号需要与 php-fpm 配置保持一致 **`/etc/php/7.0/fpm/pool.d/www.conf`** 。可以从 [这里][58] 了解 PHP-FPM 配置。

现在，我们调整 mpm_evnet 配置选项 **`/etc/apache2/mods-available/mpm_event.conf`** ，记住我们的 mini-VPS 资源在测试上受限 - 所以需要减少一些默认值。有关指令的详细信息可以查看 [指令文档][59]，关于 event 模块的可以阅读 [这个章节][60]。记住，重启服务会消耗大量的内存资源。 **`MaxRequestWorkers`**  指令设置最大请求数限制：将 **`MaxConnectionsPerChild`**  设置为非零非常重要，它可以防止内存泄露。

```apache
<ifmodule mpm_event_module>
    StartServers              1
    MinSpareThreads          30
    MaxSpareThreads          75
    ThreadLimit              64
    ThreadsPerChild          30
    MaxRequestWorkers        80
    MaxConnectionsPerChild   80
</ifmodule>
```

使用 **`sudo service apache2 restart`**  重新启动服务器，如果我们修改了如 **`ThreadLimit`**  这类指令，我们还需要显示的停止和启动服务 **`sudo service apache2 stop; sudo service apache2 start`** 。

在 [Pingdom][61] 上的测试结果显示页面加载时间缩短了一半以上。
### Apache 配置其它技巧
 **`禁用 .htaccess`** ： **`.htaccess`**  允许在无需重启服务时对根目录下的每个目录单独进行配置。所以，服务器接收请求后会遍历所有目录，查找 .htaccess 文件，这会导致性能下降。

以下引用自 Apache 官方文档：

通常，仅当你的主服务器配置文件没有进行相应的访问控制时才需要使用 **`.htaccess`**  文件。... 一般，需要尽可能避免使用 **`.htaccess`**  文件。当需要使用 **`.htaccess`**  文件时，都可以在主服务器配置的 **`directory`**  配置节点去执行配置
解决方案是到 **`/etc/apache2/apache2.conf`**  禁用重写功能：

```apache
AllowOverride None
```

如果需要在特定目录启用重写功能，可以到虚拟主机配置文件中指定节点启用：

```apache
AllowOverride All
```

更多使用技巧：


* [使用 mod_expire 控制浏览器缓存][62] - 通过设值 expires 响应头。
* 关闭 **`HostNameLookups`**  功能 - **`HostNameLookups`**  自 Apache 1.3 器默认关闭 **`off`** ，由于它会导致性能下降，所以直接关闭就好。
* **`Apache2buddy`**  是一个简单的脚本，我们可以运行并获得调整系统的提示：curl -sL [https://raw.githubusercontent...][63] | perl


![][6]
## Nginx

Nginx 是一款 [事件驱动（event-driven）][64] 非阻塞模式的 Web 服务器。下面摘自 [Hacker News][65]：

与事件循环相比 fork 子进程消耗更多系统资源。基于事件的 HTTP 服务器完胜。
这个言论引发了对 Hacker News 的吐槽，从我的经验来看，从 Apache 的 mpm_prefork 切换到 Nginx 可以保证网站不宕机。简单的将 Web 服务器切换到 Nginx 就可做到这点。

![][7]

可以从 [这里][66] 获取 Nginx 架构的全面分析。
### 配置 Nginx

Nginx 推荐将 worker 进程数量设置为 PC 的 核心数（类似 Apache 的 mpm_event 配置），将 **`/etc/nginx/nginx.conf`**  配置文件中 **`worker_processes`**  指令设置为 **`auto`**  （默认为 1）。
 **`worker_connections`**  设置单个 worker 进程能够处理的连接数。默认为 512，不过通常可以增加处理连接数量。
 **`[keepalive 连接数][67]`**  一样会影响服务器性能，在基准测试中一般看不到这个 [请求头][68]。

![][8]

[从 Nginx 网站了解到][69]：

HTTP keepalive 连接数是能够有效减少延迟提升 web 页面加载速度的优化性能手段。
创建新的 TCP 连接会 [消耗资源][70] - 尤其是启用安全的 HTTPS 加密协议。[HTTP/2][71] 协议通过 [复用特性][72] 可以减少资源消耗。复用已经创建好的连接能够降低请求时间。

Apache 的 mpm_prefork 和 mpm_worker 对比 keepalive 事件循环在并发处理能力上存在不足。所以在 Apache 2.4 中引入 mpm_event 模块对此进行了修复，然而对于 Nginx 事件驱动是唯一默认处理模式。Nginx 的 worker 进程可以同时处理数千个连接，如果使用它作为反向代理或负载均衡器的话，Nginx 还可以使用本地 keepalive 连接池，而无需使用 TCP 连接所带来的开销。
 **`[keepalive_requests][73]`**  指令用于设置单个客户端能够在一个 keepalive 连接上处理的请求数量。
 **`[keepalive_timeout][74]`**  设置空闲 keepalive 连接保持打开的时间。
 **`[keepalive][75]`**  是关于 upstream（上游） 服务器和 Nginx 连接有关的配置 - 当 Nginx 充当代理或负载均衡服务器角色时。表示在空闲状态 upstream 服务器在单个 worker 进程中支持的 keepalive 连接数。

当使用 upstream keepalive 连接处理请求时，需要将如下指令添加到 nginx 主配置文件中：

```nginx
proxy_http_version 1.1;
proxy_set_header Connection "";
```

nginx upstream 连接由 **`[ngx_http_upstream_module][76]`**  模块管理。

如果我们的客户端应用需要不断轮询服务端应用进行数据更新，可以通过 **`keepalive_requests`**  和 **`keepalive_timeout`**  增加连接数。同时 **`keepalive`**  指令值不应太大，这样就能够保证其他的 upstream 服务器也能够处理其它请求。

这些配置需要基于不同的应用的测试结果来进行单独配置。这或许就是 **`keepalive`**  没有默认值的原因。
### 使用 UNIX 套接字

默认情况下，nginx 使用单独的 PHP 进程将 HTTP 请求转发到 PHP 文件。这种场景就是代理（类似 Apache 需要设置 php7.0-fpm）。

我们所用的 Nginx 虚拟主机配置如下：

```nginx
location ~ \.php$ {
    fastcgi_param REQUEST_METHOD $request_method;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
}
```

由于 FastCGI 与 HTTP 是不同的协议，前两行配置是将一些参数和请求头转发到 php-fpm 进程管理器，最后一行设置了请求的代理方式 - 通过本地网络套接字完成。

这对于多服务器它很实用，因为 nginx 可以对远程服务器进行代理转发。

但是，如果我们将网站托管在一台服务器上时，我们就应该使用 UNIX 套接字来监听 php 进程：

```nginx
fastcgi_pass unix:/var/run/php7.0-fpm.sock;
```

UNIX 套接字相比 TCP 连接有更好的 [性能][77]，从安全角度来讲这个设置也是更优的选择。你可以从 Rackspace 站点的 [这篇文章][78] 掌握更多配置细节。

这个技巧同样适用于 Apache 服务器。可以到 [这里][58] 进行学习。
 **`gzip_static`** ：在 web 服务器优对静态文件进行压缩处理是公认的行之有效的技术。这表示我们对大文件做出让步，会对哪些超过指定大小的文件进行压缩处理，因为这些文件在请求时消耗更多的资源。Nginx 提供一个 **`gzip_static`**  指令，允许我们使用服务器的 gzip 压缩工具对文件进行压缩 - 压缩后的文件扩展名为 .gz 而非不同文件：

```nginx
location /assets {
    gzip_static on;
}
```

这样 Nginx 服务器会长时间 **`style.css`**  压缩成 **`style.css.gz`**  文件（此时我们需要自己处理解压）。

通过这种方式，在 CPU 周期内无需在每个请求时动态的对文件进行压缩处理。
### 启用 Nginx 服务器缓存

如果不涉及讲解如何进行缓存配置，那么对 Nginx 讲解就是不是完整的。由于 Nginx 缓存非常高效，以至于诸多系统管理员认为使用单独的 [HTTP 缓存][80] 都是多余的（如 [Varnish][81]）。Nginx 缓存配置也十分简单。

```nginx
proxy_cache_path /path/to/cache levels=1:2 keys_zone=my_cache:10m max_size=10g
  inactive=60m;
```

这些指令配置在 **`server`**  块级指令中。 **`proxy_cache_path`**  参数可以是任何缓存保存的路径。 **`levels`**  设置 Nginx 可以缓存什么层级目录。出于性能考量，两层目录通常就可以了。因为目录递归处理非常消耗资源。 **`keys_zone`**  参数用于识别共享内存的缓存键名， **`10m`**  表示该键名能够使用的内存大小（10 MB 通常就够了；这不是实际缓存内容的空间大小）。可选的 **`max_size`**  指令设置缓存的内容上限 - 这里是 10GB。如果未设置该值，则会占用所有可用的存储空间。 **`inactive`**  指令设置数据未被命中时可被缓存的有效期。

设置完成后，将缓存键名添加到 **`server`**  或 **`location`**  指令块就好了：

```nginx
proxy_cache my_cache;
```

Nginx 容错层能够通知源服务器或 upstream 服务器在服务器出错或关闭时从缓存中获取命中的数据：

```nginx
proxy_cache_use_stale error timeout http_500 http_502 http_503 http_504;
```

有关 **`server`**  和 **`location`**  指令对于缓存的配置细节可以阅读 [这里][82]。
 **`proxy_cache_`** * 用于静态资源缓存，不过通常我们希望能够缓存动态内容 - 如 CMS 或其他应用。此时，我们可以使用 **`fastcgi_cache_*`**   指令来代替 **`proxy_cache_*`** ：

```nginx
fastcgi_cache_path /var/run/nginx-cache levels=1:2 keys_zone=my_cache:10m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
fastcgi_cache_use_stale error timeout invalid_header http_500;
fastcgi_ignore_headers Cache-Control Expires Set-Cookie;
add_header NGINX_FASTCGI_CACHE $upstream_cache_status;
```

上面的最后一行会设置响应头，来告知我们内容是否从缓存中获取。

然后，在我们的 **`server`**  或 **`location`**  块中，我们可以为缓存设置一些无需缓存的场景 - 例如，当请求 URL 中存在查询字符串时：

```nginx
if ($query_string != "") {
    set $skip_cache 1;
}
```

另外，在 **`server`**  指令下的 **`\.php`**  块指令里，我们会添加如下内容：

```nginx
try_files $uri =404;
include fastcgi_params;

fastcgi_read_timeout 360s;
fastcgi_buffer_size 128k;
fastcgi_buffers 4 256k;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

fastcgi_pass unix:/run/php/php7.0-fpm.sock;

fastcgi_index index.php;
fastcgi_cache_bypass $skip_cache;
fastcgi_no_cache $skip_cache;
fastcgi_cache my_cache;
fastcgi_cache_valid  60m;
```

以上， **`fastcgi_cache*`**  和 **`fastcgi_no_cache`**  就配置完可缓存和不可缓存的所有规则。

你可以从 Nginx 官网 [文档][83] 中获取这些指令的指引。

要了解更多信息，Nginx 提供了相关主题的 [会议][84]，还有好多免费的 [电子书][85]。
## 总结

我们试图介绍一些有助于我们改进 Web 服务器性能的技术，以及这些技术背后的理论。但是这个主题才涉及皮毛：我们还没有涵盖 Apache 和 Nginx 或多服务器有关如何设置反向代理的讲解。使用这两种服务器实现最佳方式是依据测试和分析特定的案例来进行选择。这是一个永无止境的话题。

[Apache vs Nginx Performance: Optimization Techniques][86]

[9]: http://blog.phpzendo.com/?p=465
[10]: https://httpd.apache.org/
[11]: https://en.wikipedia.org/wiki/Apache_HTTP_Server
[12]: https://w3techs.com/blog/entry/fact_20170828
[13]: https://w3techs.com/technologies/history_overview/web_server/ms/y
[14]: https://w3techs.com/technologies/comparison/ws-apache,ws-microsoftiis,ws-nginx
[15]: https://news.netcraft.com/archives/2017/09/11/september-2017-web-server-survey.html
[16]: https://trends.builtwith.com/web-server
[17]: https://www.wikiwand.com/en/Igor_Sysoev
[18]: https://www.nginx.com/blog/nginx-vs-apache-our-view/
[19]: https://docs.nginx.com/nginx/admin-guide/web-server/reverse-proxy/
[20]: http://nginx.org/en/docs/http/load_balancing.html
[21]: https://www.nginx.com/blog/nginx-caching-guide/
[22]: https://www.wikiwand.com/en/List_of_Apache_modules
[23]: http://howtolamp.com/lamp/httpd/2.4/dso/
[24]: http://manpages.ubuntu.com/cgi-bin/search.py?cx=003883529982892832976%3A5zl6o8w6f0s&cof=FORID%3A9&ie=UTF-8&titles=404&lr=lang_en&q=a2enmod.8
[25]: http://nginx.org/en/docs/http/ngx_http_v2_module.html
[26]: http://www.htaccess-guide.com/
[27]: https://www.nginx.com/resources/wiki/start/topics/examples/likeapache-htaccess/
[28]: https://www.netcraft.com/
[29]: https://www.litespeedtech.com/products/litespeed-web-server
[30]: https://www.hivelocity.net/kb/what-is-litespeed/
[31]: https://www.interserver.net/tips/kb/litespeed-cache-lscache-details-advantages/
[32]: https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache
[33]: https://www.litespeedtech.com/products/litespeed-web-server/features/anti-ddos-advances
[34]: https://www.a2hosting.com/litespeed-hosting
[35]: https://blogs.dropbox.com/tech/2017/09/optimizing-web-servers-for-high-throughput-and-low-latency/
[36]: http://hisham.hm/htop/
[37]: https://newrelic.com/
[38]: https://my-netdata.io/
[39]: https://www.cyberciti.biz/faq/how-to-install-and-use-monit-on-ubuntudebian-linux-server/
[40]: https://httpd.apache.org/docs/2.4/programs/ab.html
[41]: https://www.joedog.org/siege-home/
[42]: https://kalamuna.atlassian.net/wiki/spaces/KALA/pages/16023587/Testing+With+Apache+Benchmark+and+Siege
[43]: https://blog.getpolymorph.com/7-tips-for-heavy-load-testing-with-apache-bench-b1127916b7b6
[44]: https://www.sitepoint.com/web-app-performance-testing-siege-plan-test-learn/
[45]: https://locust.io/
[46]: https://docs.locust.io/en/latest/writing-a-locustfile.html
[47]: https://serverfault.com/questions/823121/why-is-apache-spawning-so-many-processes/823162
[48]: https://httpd.apache.org/docs/trunk/mpm.html#dynamic
[49]: http://httpd.apache.org/docs/2.4/mod/prefork.html
[50]: http://httpd.apache.org/docs/2.4/mod/prefork.html
[51]: http://httpd.apache.org/docs/2.4/mod/worker.html
[52]: http://httpd.apache.org/docs/2.4/mod/event.html
[53]: http://httpd.apache.org/docs/2.4/mod/worker.html
[54]: http://httpd.apache.org/docs/2.4/mod/event.html
[55]: https://www.slideshare.net/jimjag/apachecon-2017-whats-new-in-httpd-24
[56]: https://tools.pingdom.com/
[57]: https://packages.debian.org/sid/amd64/libapache2-mod-php7.0
[58]: https://wiki.apache.org/httpd/PHP-FPM
[59]: http://httpd.apache.org/docs/current/mod/mpm_common.html
[60]: http://httpd.apache.org/docs/2.4/mod/event.html
[61]: https://tools.pingdom.com/
[62]: http://httpd.apache.org/docs/current/mod/mod_expires.html
[63]: https://raw.githubusercontent.com/richardforth/apache2buddy/master/apache2buddy.pl
[64]: https://www.nginx.com/blog/inside-nginx-how-we-designed-for-performance-scale/
[65]: https://news.ycombinator.com/item?id=8343350
[66]: https://www.nginx.com/resources/library/infographic-inside-nginx/
[67]: https://en.wikipedia.org/wiki/Keepalive
[68]: https://www.nginx.com/blog/http-keepalives-and-web-performance/
[69]: https://www.nginx.com/blog/http-keepalives-and-web-performance/
[70]: https://en.wikipedia.org/wiki/Handshaking
[71]: https://http2.github.io/
[72]: https://en.wikipedia.org/wiki/Multiplexing
[73]: http://nginx.org/en/docs/http/ngx_http_core_module.html?&_ga=2.26969269.942121935.1510206018-994710012.1508256997#keepalive_requests
[74]: http://nginx.org/en/docs/http/ngx_http_core_module.html?&_ga=2.191644834.942121935.1510206018-994710012.1508256997#keepalive_timeout
[75]: http://nginx.org/en/docs/http/ngx_http_upstream_module.html?&_ga=2.203640216.942121935.1510206018-994710012.1508256997#keepalive
[76]: http://nginx.org/en/docs/http/ngx_http_upstream_module.html
[77]: https://stackoverflow.com/questions/257433/postgresql-unix-domain-sockets-vs-tcp-sockets/257479#257479
[78]: https://support.rackspace.com/how-to/install-nginx-and-php-fpm-running-on-unix-file-sockets/
[79]: https://wiki.apache.org/httpd/PHP-FPM
[80]: https://en.wikipedia.org/wiki/Web_cache
[81]: https://varnish-cache.org/
[82]: https://www.nginx.com/blog/nginx-caching-guide/
[83]: http://nginx.org/en/docs/dirindex.html
[84]: https://www.nginx.com/resources/webinars/installing-tuning-nginx/
[85]: https://www.nginx.com/resources/library/
[86]: https://www.sitepoint.com/apache-vs-nginx-performance-optimization-techniques/
[0]: ./img/1460000016071078.png
[1]: ./img/1460000016071079.png
[2]: ./img/1460000016071080.png
[3]: ./img/1460000016071081.png
[4]: ./img/1460000016071082.png
[5]: ./img/1460000016071083.png
[6]: ./img/1460000016071084.png
[7]: ./img/1460000016071085.png
[8]: ./img/1460000016071086.png
**CDN类的网站曾经一度雄踞 Alexa 域名排行的前 100。以前一些小网站不需要使用 CDN 或者根本负担不起其价格，不过这一现象近几年发生了很大的变化，CDN 市场上出现了很多按次付费，非公司性的提供商，这使得 CDN 变成人人都能负担的起的一种服务了。本文讲述的就是如何使用这种简单易用的缓存服务。**

上篇文章[掌握 HTTP 缓存--从请求到响应过程的一切（上） - 知乎专栏][0]中，我们讨论了关于利用 HTTP 头来解决缓存问题，这篇文章我们将介绍缓存和 Cookie之间的关系。

## **Cookies**

你已经知道了缓存头是如何起作用的，现在我们来看下在缓存里面 cookie 起了什么作用。首先， Cookie 的设定也在 HTTP 响应头中，名字是 Set-Cookie。设置一个 cookie 的目的是标识这个用户，就是说你需要为每个用户设置一个 cookie。

想象一下缓存的场景，你是否会缓存一个包含了Set-Cookie的 HTTP 响应，在缓存时间内，每个人都会得到相同的 cookie 和同样的用户 session？你肯定不想这样。

另外，用户 session 状态的改变可能会影响到响应内容的变化。一个简单的场景：电商购物车。你给用户要么提供一个空购物车，要么是用户自己选了很多物品的购物车。同样的道理，你不希望这个也被缓存，毕竟每个用户都应该有自己的购物车。

一个解决方法是在运行时通过 JavaScript 设置 Cookie，比如 Google Analytics。GA 通过 JS 设置 cookie，但这个 cookie 既不影响渲染，也不设置 Set-Cookie 头。GA 会在目标网站上添加类似于 "you are tracked via Google Analytics" 的图标，_但是只要这些改变都是在运行时添加进去的，就都没有问题_。

## **正确处理 cookie 和缓存**

首先你需要知道你网站的 cookie 的工作原理。cookie 是不是只在特定时间使用（如在用户登录过程中使用）？原则上，cookie 是不是会被注入到所有响应？

正如上一节所说的，不论何时服务器返回了一个带有Set-Cookie 的响应，你都希望能够保证它不会被缓存。那么问题就转化成为，当你返回一个带有“用户特性”内容的响应时（如购物车），CDN /代理服务器，会作何操作？

* 如果没设置 Set-Cookie，是不是允许缓存呢？
* 如果设置了 Set-Cookie，是不是自动丢弃所有Cache-Control 头呢？

其实，如果从应用层面来讲，你尽管可以去实现你所喜欢的 web 应用就可以了，至于 cookie 和 CDN 都是自动设置的。还是用 Apache 的 .htaccess 来作为例子来解释：

    # 1) 如果 cookie 没设置，允许缓存
    Header set Cache-Control "public max-age=3600" "expr=-z resp('Set-Cookie')
    
    # 2) 如果 cookie 被设置，不允许缓存
    Header always remove Cache-Control "expr=-n resp('Set-Cookie')
    
    # 2a) 第二条的另一种形式，如果设置了 cookie，缓存时间设置成0
    Header set Cache-Control "no-cache max-age=0 must-revalidate" "expr=-n resp('Set-Cookie')
    

* 规则1：如果没设置 Set-Cookie，则给Cache-Control 设置一个默认值；
* 规则2：如果设置了 Set-Cookie，则忽略Cache-Control；
* 规则2a：是规则2的另一种表示形式，设置最大缓存时间是 0。

### **无 cookie 的访问路径**

一些 CMS / 框架还在使用一种暴力的方式种 cookie。而实际上，决定是否种 cookie 取决于不同的因素，比如会话时间因素。如果你有一个很高安全性的 web 应用，设置会话时间是 5 分钟，那么为每个响应设置一个新 cookie 都不过分。而假设你的应用连“用户特性”都没有，也就是说所有的东西对所有用户都是公用的，那么设置任何形式的 cookie 都是没有道理的。

所以下面这个例子是否适合你自己，很大程度上依赖于你的应用到底是什么类型的。我们来一起看一下，我先给一下这个例子的上下文关系：假设你有个新网站，你的所有文章都在 [http://www. foobar.tld/news/item/][1] 这个路径下面。现在你希望能够保证，所有访问 /news/item/<ID>的路径都不包含 Set-Cookie，因为你确定不需要 cookie。

    # 通用 PHP 重定向做法，将"?path=$1"写到重定向规则里
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?path=$1 [NC,L,QSA]
    RewriteRule ^$ index.php [NC,L,QSA]
    
    # 利用 query 中的 path= 来判断
    

通过这样的设置，你就可以保证所有访问/news/item/<ID>的路径都不包含 Set-Cookie。而到底是否应该设置 cookie，需要你根据你自己的应用特点来判断。

### **设计出来的缓存能力**

有很多设计方案可以使你的 web 应用具有高缓存性。鉴于本文仅仅是一篇文章而不是一本书，我不可能每个点都深入的来讲，但是我可以着重提一下通用的方法。

我还用电商作为例子。假设电商网站首页的 top 位置上展示了正在出售的物品，生成这些物品需要进行若干次的数据库操作，代价比较大，因此希望把它们缓存起来。但是，问题在于购物车，它是为那些登陆用户准备的，所以希望得到的结果是： top 物品是一样的，而针对登陆用户展示购物车。

那么优化策略首先要为每个用户提供一个和登陆状态无关的“通用”页。然后通过 JavaScript 为已经生成的网页提供购物车。站在用户的视角，最终展示形式是一样的。那么现在你有了两个请求（整个网页请求 + 购物车请求），而不是一个请求（整个网页请求，包含购物车）。ok，现在你可以把代价很大的部分，即 top 物品分离出来，把它们缓存起来了。

这种方法或者其延伸方法，不适合已经开发好的项目。因为它可能会改变很多接口和视图层（MVC 架构）的内容。最好你在一开始就设计好。

## **缓存失效：busting 和 purging**

使用 max-age 和 s-maxage 你已经可以很好地控制一个指定的响应被缓存多长时间。但是这不足以适用于所有的情况。这些设置都是在返回响应时预设的，而现实情况往往是并不知道一个响应应该设置多久期满。回想一下刚才电商首页的例子：假设它包含了展示在 top 位置的 10 个实体。你设置了 max-age=900给这个首页以保证每15分钟刷新一次。现在，其中 1 个实体由于发布了太久了要被撤销，那么你就需要把之前的缓存响应删掉，这时候其实还没到 15 分钟，那么该怎么办？

不要担心，这是一个常见的问题，有很多方法解决。首先我们先来解释一下术语：

* **缓存 busting**，是用来解决浏览器长期缓存问题，它通过版本标识来告诉浏览器该文件有一个新的版本。这时浏览器将不会从本地缓存取内容，而从源服务器请求新版本的文件。缓存 busting的详细介绍在这里：[What is Cache Busting?][0]。
* **缓存 purging**，表示直接从缓存中删除内容（即响应），以使得缓存可以立马得到更新。

### **用于版本管理的缓存 busting**

这种方法经常使用在 CSS 文件、JS 文件上。通常一个确切的版本号、一串哈希或者时间戳都可以用作标识，如下面的例子：

* 数字版本号：style-v1.css，style.css?v=1
* 哈希串版本：style.css?d3b07384d113edec49eaa6238ad5ff00
* 时间戳版本：styles.css?t=1486398121

这时候在发布程序的时候，你只要注意文件的版本就可以了。举个例子，一个 HTML 网页通过 这种形式包含了一个 CSS 文件。CSS 文件将会被缓存起来，这时如果你想让你的新 CSS 文件起作用，那么用最新的版本号命名它就可以。如果不做任何变化的话，即便你更新了文件，这个 HTML 还会使用缓存中的旧 CSS 文件。

### **缓存 purging**

不同 CDN 供应商清除缓存的方式不一样。很多供应商都是基于开源软件 [Varnish][1] 来构建自己的 CDN 服务，所以一个通用的做法是在 HTPP 请求中使用 PURGE 结构，如：

    PURGE /news/item/i-am-obsolete HTTP/1.1
    Host: www.foobar.tld
    

使用这个请求通常需要权限认证，或者是源确认（即 IP 白名单），不过不同供应商的要求也不一样。

清除一个或几个缓存项比较容易，但是在某些场景下，却不是这么简单。举个例子，一个博客的场景，博客里面都有关于作者的部分，现在你要改变关于作者的一些内容，那么你需要手动清理所有包含了作者信息的页面。你确实可以一个一个手动清理，但是假设你有成千上万个网页被影响了，那问题就变得麻烦了。

下面介绍一个解决方案。

### **代理标签**

“代理标签” 这个名字来源于 CDN 供应商 [Fastly][2]，不同供应商给它起的名字不一样，比如还有叫它“缓存标签”的，Varnish 叫它 [Hashtwo/Xkey][3]，这里我就不详细介绍其他供应商的情况了。

不论它叫什么，它们的目的都是一样的：给响应打标签。这样你就可以轻松地从缓存中删除相关的标签就可以，甚至都不用知道缓存的到底是什么东西。

还是拿<客户端-代理-源端>来举例子，源端返回一个含有代理标签的响应：

    HTTP/1.1 200 OK
    Content-Type: text/html
    Content-Length: 123
    Surrogate-Key: top-10 company-acme category-foodstuff
    

这个例子中的标签为：top-10， company-acme，和category-foodstuff。这里给一个电商的实际场景来理解其含义：这个响应包含了电商首页的前 10 个物品，这些物品由 ACME 公司提供，并且其目录类别都设定为食品类。

设置了标签以后，当物品发生了变化以后，你只需要删除包含有 company-acme 和 top-10 的标签就可以了。是不是很简单？

同样，具体如何清除缓存的操作方法，不同 CDN 供应商是不一样的。

## **写在最后**

上面讨论的更多的是理论上的做法，还有很多文章专门介绍不同的 CDN 的使用。如果你想深入了解的话，下面的资料每篇可能都是你需要的。

* [谷歌开发者：HTTP 缓存][4]
* [Push CDN 和 Pull CDN][5]
* [CDN 类型（管理员视角）][6]
* [缓存头概览][7]
* [缓存详解（Mozilla）][8]
* [ETag头详解（Mozilla）][9]
* [Cace-Control 头详解（Mozilla）][10]
* [If-None-Match 头详解（Mozilla）][11]
* [Fastly：代理标签][12]
* [KeyCDN：缓存标签][13]

欢迎大家关注我的[前端大哈 - 知乎专栏][14]，定期发布高质量前端文章。

[0]: https://link.zhihu.com/?target=https%3A//www.keycdn.com/support/what-is-cache-busting/
[1]: https://link.zhihu.com/?target=https%3A//varnish-cache.org/
[2]: https://link.zhihu.com/?target=https%3A//www.fastly.com/
[3]: https://link.zhihu.com/?target=http%3A//book.varnish-software.com/4.0/chapters/Cache_Invalidation.html%23hashtwo-xkey-varnish-software-implementation-of-surrogate-keys
[4]: https://link.zhihu.com/?target=https%3A//developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching%3Fhl%3Den%23cache-control
[5]: https://link.zhihu.com/?target=http%3A//www.whoishostingthis.com/blog/2010/06/30/cdns-push-vs-pull/
[6]: https://link.zhihu.com/?target=http%3A//www.the-toffee-project.org/index.php%3Fpage%3D32-cdn-content-delivery-networks-types
[7]: https://link.zhihu.com/?target=https%3A//www.keycdn.com/support/http-caching-headers/
[8]: https://link.zhihu.com/?target=https%3A//developer.mozilla.org/en-US/docs/Web/HTTP/Caching
[9]: https://link.zhihu.com/?target=https%3A//developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
[10]: https://link.zhihu.com/?target=https%3A//developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
[11]: https://link.zhihu.com/?target=https%3A//developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match
[12]: https://link.zhihu.com/?target=https%3A//docs.fastly.com/guides/purging/getting-started-with-surrogate-keys
[13]: https://link.zhihu.com/?target=https%3A//www.keycdn.com/support/purge-cdn-cache/
[14]: https://zhuanlan.zhihu.com/qianduandaha
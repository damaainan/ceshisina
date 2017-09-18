# HTTP Cache 为什么让人很困惑

2017.04.05 11:56  字数 1897  

网上有很多关于 HTPP Cache 的知识，但个人感觉大部分讲的并不好，而这个主题对于 Web 开发者来说很重要，其实假如想全面了解相关知识，参考 [MDN][1] 和 [Google 开发者网站][2] 这两篇文章就可以了。千万不要去看 RFC 文档，个人在看的时候非常费劲，最后放弃了。

本文算一个笔记，阐述其中的关键点，也会说明为什么这个主题不好讲（大部分写的不好的原因在于整体把控做的不好，或者说理解的比较片面）。

#### 要区分理解 Private browser caches 和 Shared proxy caches

HTTP Cache 包括浏览器缓存和代理服务器缓存（比如 CDN），很多文章在描述的时候没有有效的区分这两者，所以会让人比较困惑。

浏览器缓存的服务架构可能是这样的：浏览器（Cache）=>服务器。  
代理服务器缓存架构可能是这样的：浏览器=>CDN（Cache）=>源服务器。

不同的 HTTP Cache 解决的问题和使用的场景是不一样的。个人理解浏览器缓存主要是为了避免不必要的请求和大量的网络传输，而代理服务器缓存主要是为了让服务离用户更近更有效率（当然也解决了请求和网络传输）。

对于 Web 开发者来说，可能经常遇到的还是浏览器缓存，这篇文章主要说的也是**此类缓存**。

而对于 HTTP Cache Header 指令（主要是 Cache-Control）来说，对于这两种类型的缓存，具体在使用上有不少的区别，需要仔细分辨。

#### 浏览器行为是不可控的

HTTP Cache 是通过 HTTP Cache Header 指令来控制的，指令分为请求和响应指令，响应指令告诉浏览器应该做什么（当然浏览器可以不遵守），而响应指令也一定程度上控制未来可能的请求指令。

不过不同浏览器针对请求指令处理机制可能是不一样的，比如浏览器“回退动作”、“F5 动作”、“Ctrl +F5” 等动作会发出不一样的请求指令。

具体查看下面的图，通过这张图，就明白为什么“回退动作”，会从浏览器缓存获取数据了。

![%u6D4F%u89C8%u5668%u884C%u4E3A%u63A7%u5236%u6307%u4EE4][3]



浏览器行为控制指令

#### 使用 HTTP/1.1 标准的指令

HTTP 协议是一直演变的，不考虑浏览器版本和服务器的问题，尽量使用最新标准协议的头，因为假如混着理解，会让人很困惑，比如 Expires 和 Pargma 等指令都可以被 Cache-Control 指令替代了。

#### 正确理解 Cache-Control 指令

这个指令是一个通用首部字段，就是说这个指令能够作为请求和响应指令，同时这个指令的参数也有多个，比如说其参数 max-age = 0 在请求和响应指令中分别代表什么？在理解的时候一定要分辨清楚。

#### 进一步理解 Cache-Control 指令

理解了这个指令基本上就理解了 HTTP Cache，个人觉得这句话（Cache-Control directives control who can cache the response, under which conditions, and for how long）精确描述了这个指令。

它有三个含义：

（1）能否缓存（针对响应来说）

* private：表示它只应该存在与浏览器缓存。
* public：表示它可以缓存在浏览器或者 CDN 上。
* no-cache：这个词很迷惑，不是代表不能使用缓存，而是代表在使用前必须到服务器上确认。
* no-store：表示不允许被缓存。

（2）缓存多久（针对响应来说）

* max-age= 秒，告知浏览器这个缓存的有效时间多少。

（3）revalidation（针对响应来说，就是条件检查）

* must-revalidate：表示浏览器必须检查服务器，确认本地缓存是否有效，这个参数和请求参数 max-age = 0 有些类似。

这个指令形象的告诉浏览器，你是不是可以缓存这个对象，这个对象缓存时间是多少，是否在每次使用缓存的时候先确认下。

#### 如何使用你的 Cache-Control 策略

对于一个开发者来说，如何设定Cache-Control 策略是门艺术，首先要明白资源是什么性质的，在此基础上定义 HTTP Cache 策略，Google 开发者网站的这张图形象的描述了策略。

![Cache-Control%20%u7B56%u7565][4]



Cache-Control 策略

* 这个资源是否允许缓存？
* 客户端每次使用缓存的时候需要去服务器校验吗？
* 这个缓存是 Public 的还是 Private？
* 缓存时间多少？
* 资源标识符是什么（Etag）？

#### 浏览器如何校验缓存

通过上面的描述，开发者明白了如何设置 HTTP Cache ，那么浏览器如何选择是否使用缓存呢？理解了这个会巩固理解 Cache-Control 策略。

这里面会增加两个指令，ETag 指令和 Last-modified，分别代表什么含义呢？ETag 表示资源的唯一性，假如这个值变化了代表资源更新了；Last-modified 表示资源最后的更新时间；

通过上面的图也可以发现，开发者可以在响应的时候输出这两个头信息。那这个指令代表什么意思呢？

当浏览器发现本地有缓存，且服务器指示没有必要每次使用前去确认，那么浏览器可以直接使用本地缓存。

当浏览器发现缓存已经过期了，那么这个时候可以选择重新去获取资源，但是有这么一种情况，服务器资源其实没有变化，那么为了减少带宽使用，服务器输出一个 304 HTTP 协议头，告诉浏览器，你继续使用你存储的缓存把。  
问题来了，服务器怎么知道这个资源没有变化呢（从浏览器缓存生效的那时算），假如在第一次响应的时候输出了 Last-modified 头（表示资源的最后更新时间），那么客户端发现缓存失效的时候，在请求的时候会带上 if-Modified-Since（其实就是 Last-modified 的值）信息，服务器一看服务器上的资源最后更新时间小于或等于 if-Modified-Since 时间，就表示这个资源其实是新的，然后就发送一个 304 头。

#### 如何通过 Nginx 来配置

大部分情况下，Nginx 会进行如下配置，但是需要明白含义，

    location ~* \.(ico|css|js|gif|jpe?g|png)(\?[0-9]+)?$ {
       expires 10d;
    }

expires 这个指定会输出如下的头:

    Cache-Control:max-age=864000
    Date:Tue, 28 Mar 2017 10:00:38 GMT
    ETag:"5864a0ab-1e75"
    Expires:Fri, 07 Apr 2017 10:00:38 GMT
    Last-Modified:Thu, 29 Dec 2016 05:35:39 GMT

假如缓存没有过期就会一直使用，每次也不会去服务器校验，假如想每次请求资源的时候都确认下，可以使用以下指令：

    location ~* \.(ico|css|js|gif|jpe?g|png)(\?[0-9]+)?$ {
        expires 10d;
        add_header Cache-Control "no-cache,must-revalidate,max-age=0";
    }

#### 动态程序如何控制

动态程序要负责所有的 HTTP Cache 头输出，还要自己计算 Etag，直接上代码看把：

    <?php
    $now = gmdate("D, d M Y H:i:s", time() ) . " GMT";
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
    
    if ($if_modified_since && $if_modified_since  >$now){
        header('HTTP/1.1 304 Not Modified');
        exit();
    } else {
    
        $seconds_to_cache = 3600*24;
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
    
        header("Last-Modified: $ts");
        header("Cache-Control: no-cache, must-revalidate");
    ｝

[1]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Caching
[2]: https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching
[3]: ../img/httpcache_1.png
[4]: ../img/httpcache_2.png
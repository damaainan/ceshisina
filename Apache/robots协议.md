## 玩转robots协议

浏览:1464次 [出处信息][0]

# 玩转robots协议

2013年2月8日北京市第一中级人民法院正式受理了百度诉奇虎360违反“Robots协议”抓取、复制其网站内容的不正当竞争行为一案，索赔金额高达一亿元，这可以看做2012年下半年“3B大战”的继续。在此次索赔案件中，百度称自己的Robots文本中已设定不允许360爬虫进入，而360的爬虫依然对“百度知道”、“百度百科”等百度网站内容进行抓取。

其实早在2012年11月初，针对双方摩擦加剧的情况，在中国互联网协会的牵头下，包括百度、新浪、奇虎360在内的12家互联网公司已共同签署了《互联网搜索引擎服务自律公约》，在公约第七条承诺“遵循国际通行的行业惯例与商业规则，遵守机器人协议(robots协议)。

今天就找个机会聊聊一夜成名的robots协议。

## 初识robots协议

### 什么是robots

要了解robots协议首先要了解robots，本文说的robots不是《I，robot》里的威尔·史密斯，不是《机器人总动员》里的瓦力和伊娃，不是《终结者》系列中的施瓦辛格。什么？这些经典电影你都不知道？好吧，算我想多了。本文的robots特指搜索引擎领域的web robots，这个名字可能很多人不熟悉，但是提到Web Wanderers，Crawlers和Spiders很多人可能就恍然大悟了，在中文里我们统称为爬虫或者网络爬虫，也就是搜索引擎抓取互联网网页的程序。

同学们都知道网页是通过超级链接互相关联起来的，从而形成了网页的网状结构。爬虫的工作方式就像蜘蛛在网上沿着链接爬来爬去，最基本的流程可以简化如下：

1.喂给爬虫一堆url，我们称之为种子(seeds)

2.爬虫抓取seeds，解析html网页，抽取其中的超级链接

3.爬虫接着抓取这些新发现的链接指向的网页

2，3循环往复

### 什么是robots协议

了解了上面的流程就能看到对爬虫来说网站非常被动，只有老老实实被抓取的份。存在这样的需求：

1.某些路径下是个人隐私或者网站管理使用，不想被搜索引擎抓取，比如说日本爱情动作片

2.不喜欢某个搜索引擎，不愿意被他抓取，最有名的就是之前淘宝不希望被百度抓取

3.小网站使用的是公用的虚拟主机，流量有限或者需要付费，希望搜索引擎抓的温柔点

4.某些网页是动态生成的，没有直接的链接指向，但是希望内容被搜索引擎抓取和索引

网站内容的所有者是网站管理员，搜索引擎应该尊重所有者的意愿，为了满足以上等等，就需要提供一种网站和爬虫进行沟通的途径，给网站管理员表达自己意愿的机会。有需求就有供应，robots协议就此诞生。Robots协议，学名叫：The Robots Exclusion Protocol，就搜索引擎抓取网站内容的范围作了约定，包括网站是否希望被搜索引擎抓取，哪些内容不允许被抓取，把这些内容放到一个纯文本文件robots.txt里，然后放到站点的根目录下。爬虫抓取网站内容前会先抓取robots.txt，据此“自觉地”抓取或者不抓取该网页内容，其目的是保护网站数据和敏感信息、确保用户个人信息和隐私不被侵犯。

需要注意的是robots协议并非是规范，只是行业内一个约定俗成的协议。什么意思呢？Robots协议不是什么技术壁垒，而只是一种互相尊重的协议，好比私家花园的门口挂着“闲人免进”，尊重者绕道而行，不尊重者依然可以推门而入，比如说360。

说了这么多，看几个有名的例子感觉一下先：

例子1：[淘宝][1]

    User-agent: Baiduspider
    Disallow: /
    
    User-agent: baiduspider
    Disallow: /
    

程序猿，你懂的。这不就是淘宝不想让百度抓取嘛

例子2：[京东][2]

    User-agent: *
    Disallow: /?*
    Disallow: /pop/*.html
    User-agent: EtaoSpider
    Disallow: /
    

这个也不复杂，京东有2个目录不希望所有搜索引擎来抓。同时，对etao完全屏蔽。

## 基本玩法

### robots.txt的位置

说简单也简单，robots.txt放到一个站点的根目录下即可。说复杂也有点小复杂，一个robots.txt只能控制相同协议，相同端口，相同站点的网页抓取策略。什么意思呢？看个例子最清楚：

[百度网页搜索][3]

[百度知道][4]

这两个robots.txt的内容是不同的，也就是说百度网页搜索和百度知道的抓取策略可以由自己独立的robots.txt来控制，井水不犯河水。

### robots.txt的内容

最简单的robots.txt只有两条规则：

1.User-agent：指定对哪些爬虫生效

2.Disallow：指定要屏蔽的网址

整个文件分为x节，一节由y个User-agent行和z个Disallow行组成。一节就表示对User-agent行指定的y个爬虫屏蔽z个网址。这里x>=0，y>0，z>0。x=0时即表示空文件，空文件等同于没有robots.txt。

下面详细介绍这两条规则：

### User-agent

爬虫抓取时会声明自己的身份，这就是User-agent，没错，就是http协议里的User-agent。robots.txt利用User-agent来区分各个引擎的爬虫。

举例说明：google网页搜索爬虫的User-agent为Googlebot，下面这行就指定google的爬虫。

    User-agent：Googlebot
    

如果想指定所有的爬虫怎么办？不可能穷举啊，可以用下面这一行：

    User-agent: *
    

可能有的同学要问了，我怎么知道爬虫的User-agent是什么？这里提供了一个简单的列表：[爬虫列表][5]

当然，你还可以查相关搜索引擎的资料得到官方的数据，比如说[google爬虫列表][6]，[百度爬虫列表][7]

### Disallow

Disallow 行列出的是要拦截的网页，以正斜线 (/) 开头，可以列出特定的网址或模式。

要屏蔽整个网站，使用正斜线即可：

    Disallow: /
    

要屏蔽某一目录以及其中的所有内容，在目录名后添加正斜线：

Disallow: /无用目录名/

要屏蔽某个具体的网页，就指出这个网页。

Disallow: /网页.html

Disallow还可以使用前缀和通配符。

要屏蔽目录a1-a100，可以使用上面的方式写100行，或者

Disallow：/a

但是需要注意，这样会把任何以a开头的目录和文件也屏蔽，慎用。如果需要屏蔽a1-a100，但是不屏蔽a50，怎么办？同学们可以思考一下，这个问题我们留到下一节。

要阻止特定类型的文件(如 .gif)，请使用以下内容：

    Disallow: /*.gif$
    *匹配任意个字符，$匹配url结束，具体就不解释了吧，不了解的同学去自学一下通配符。
    

提示一下，Disallow的内容区分大小写。例如，Disallow: /junkfile.asp 会屏蔽 junkfile.asp，却会允许Junk_file.asp。

最最后，通配符不是所有搜索引擎都支持，使用要小心。没办法，谁让robots.txt没有一个大家都承认的标准呢。

### 实例 ###

[百度网页搜索][3]

    User-agent: Baiduspider
    Disallow: /baidu
    Disallow: /s?
    
    User-agent: Googlebot
    Disallow: /baidu
    Disallow: /s?
    Disallow: /shifen/
    Disallow: /homepage/
    Disallow: /cpro
    
    User-agent: MSNBot
    Disallow: /baidu
    Disallow: /s?
    Disallow: /shifen/
    Disallow: /homepage/
    Disallow: /cpro
    ...
    

现在读懂这个应该毫无压力了吧，顺便说一句百度的robots.txt比较嗦，有兴趣的同学可以简化一下。

## 高阶玩法

首先声明：高级玩法不是所有引擎的爬虫都支持，一般来说，作为搜索引擎技术领导者的谷歌支持的最好。

例子：[google robots.txt][8]

### allow

还记得上面的问题吗？如果需要屏蔽a1-a100，但是不屏蔽a50，怎么办？

方案1：

    Disallow：/a1/
    Disallow：/a2/
    ...
    Disallow：/a49/
    Disallow：/a51/
    ...
    Disallow：/a100/
    

方案2：

    Disallow：/a
    Allow:/a50/
    

ok，allow大家会用了吧。

顺便说一句，如果想屏蔽a50下面的文件private.html，咋整？

Disallow：/a

Allow:/a50/

Disallow：/a50/private.html

聪明的你一定能发现其中的规律，对吧？谁管的越细就听谁的。

### sitemap

前面说过爬虫会通过网页内部的链接发现新的网页。但是如果没有连接指向的网页怎么办？或者用户输入条件生成的动态网页怎么办？能否让网站管理员通知搜索引擎他们网站上有哪些可供抓取的网页？这就是sitemap，最简单的 Sitepmap 形式就是 XML 文件，在其中列出网站中的网址以及关于每个网址的其他数据(上次更新的时间、更改的频率以及相对于网站上其他网址的重要程度等等)，利用这些信息搜索引擎可以更加智能地抓取网站内容。

sitemap是另一个话题，足够开一篇新的文章聊的，这里就不展开了，有兴趣的同学可以参考[sitemap][9]

新的问题来了，爬虫怎么知道这个网站有没有提供sitemap文件，或者说网站管理员生成了sitemap，(可能是多个文件)，爬虫怎么知道放在哪里呢？

由于robots.txt的位置是固定的，于是大家就想到了把sitemap的位置信息放在robots.txt里。这就成为robots.txt里的新成员了。

节选一段[google robots.txt][8]：

    Sitemap: http://www.gstatic.com/culturalinstitute/sitemaps/www_google_com_culturalinstitute/sitemap-index.xml
    Sitemap: http://www.google.com/hostednews/sitemap_index.xml
    

插一句，考虑到一个网站的网页众多，sitemap人工维护不太靠谱，google提供了工具可以自动生成sitemap。

### meta tag

其实严格来说这部分内容不属于robots.txt，不过也算非常相关，我也不知道放哪里合适，暂且放到这里吧。

robots.txt的初衷是为了让网站管理员管理可以出现在搜索引擎里的网站内容。但是，即使使用 robots.txt 文件让爬虫无法抓取这些内容，搜索引擎也可以通过其他方式找到这些网页并将它添加到索引中。例如，其他网站仍可能链接到该网站。因此，网页网址及其他公开的信息(如指向相关网站的链接中的定位文字或开放式目录管理系统中的标题)有可能会出现在引擎的搜索结果中。如果想彻底对搜索引擎隐身那咋整呢？答案是：元标记，即meta tag。

比如要完全阻止一个网页的内容列在搜索引擎索引中(即使有其他网站链接到此网页)，可使用 noindex 元标记。只要搜索引擎查看该网页，便会看到 noindex 元标记并阻止该网页显示在索引中，这里注意noindex元标记提供的是一种逐页控制对网站的访问的方式。

举例：

要防止所有搜索引擎将网站中的网页编入索引，在网页的 

部分添加：

    <meta name="robots" content="noindex">
    

这里的name取值可以设置为某个搜索引擎的User-agent从而指定屏蔽某一个搜索引擎。

除了noindex外，还有其他元标记，比如说nofollow，禁止爬虫从此页面中跟踪链接。详细信息可以参考[Google支持的元标记][10]，这里提一句：noindex和nofollow在[HTML 4.01][11]规范里有描述，但是其他tag的在不同引擎支持到什么程度各不相同，还请读者自行查阅各个引擎的说明文档。

### Crawl-delay

除了控制哪些可以抓哪些不能抓之外，robots.txt还可以用来控制爬虫抓取的速率。如何做到的呢？通过设置爬虫在两次抓取之间等待的秒数。

    Crawl-delay:5
    

表示本次抓取后下一次抓取前需要等待5秒。

注意：google已经不支持这种方式了，在webmaster tools里提供了一个功能可以更直观的控制抓取速率。

这里插一句题外话，几年前我记得曾经有一段时间robots.txt还支持复杂的参数:Visit-time，只有在visit-time指定的时间段里，爬虫才可以访问；Request-rate: 用来限制URL的读取频率，用于控制不同的时间段采用不同的抓取速率。后来估计支持的人太少，就渐渐的废掉了，有兴趣的同学可以自行google。我了解到的是目前google和baidu都已经不支持这个规则了，其他小的引擎公司貌似从来都没有支持过。如果确有支持那是我孤陋寡闻了，欢迎留言告知。

## 真的有用？

好吧，到此为止robots.txt相关的东东介绍的也七七八八了，能坚持看到这里的同学估计都跃跃欲试了，可惜，我要泼盆冷水，能完全指望robots.txt保护我们网站的内容吗？不一定。否则百度和360就不用打官司了。

### 协议一致性

第一个问题是robots.txt没有一个正式的标准，各个搜索引擎都在不断的扩充robots.txt功能，这就导致每个引擎对robots.txt的支持程度各有不同，更不用说在某个功能上的具体实现的不同了。

### 缓存

第二个问题是robots.txt本身也是需要抓取的，出于效率考虑，一般爬虫不会每次抓取网站网页前都抓一下robots.txt，加上robots.txt更新不频繁，内容需要解析。通常爬虫的做法是先抓取一次，解析后缓存下来，而且是相当长的时间。假设网站管理员更新了robots.txt，修改了某些规则，但是对爬虫来说并不会立刻生效，只有当爬虫下次抓取robots.txt之后才能看到最新的内容。尴尬的是，爬虫下次抓取robots.txt的时间并不是由网站管理员控制的。当然，有些搜索引擎提供了web 工具可以让网站管理员通知搜索引擎那个url发生了变化，建议重新抓取。注意，此处是建议，即使你通知了搜索引擎，搜索引擎何时抓取仍然是不确定的，只是比完全不通知要好点。至于好多少，那就看搜索引擎的良心和技术能力了。

### ignore

第三个问题，不知是无意还是有意，反正有些爬虫不太遵守或者完全忽略robots.txt，不排除开发人员能力的问题，比如说根本不知道robots.txt。另外，本身robots.txt不是一种强制措施，如果网站有数据需要保密，必需采取技术措施，比如说：用户验证，加密，ip拦截，访问频率控制等。

### 偷偷的抓

第四个问题，即使采用了种种限制，仍然存在某些恶意的抓取行为能突破这些限制，比如一些利用肉鸡进行的抓取。悲观的说，只要普通用户可以访问，就不能完全杜绝这种恶意抓取的行为。但是，可以通过种种手段使抓取的代价增大到让对方无法接受。比如说：[Captcha][12]， Ajax用户行为驱动的异步加载等等。这个就不属于本文讨论的范畴了。

### 泄密

最后，robots.txt本身还存在泄密的风险。举例，如果某一个网站的robots.txt里突然新增了一条：Disallow /map/，你想到了什么？是不是要推出地图服务了？于是有好奇心的同学就会开始尝试各种文件名去访问该路径下的文件，希望能看到惊喜。貌似当初google的地图就是这么被提前爆出来的，关于这点我不太确定，大家就当八卦听听好了。有兴趣的同学可以参考[用robots.txt探索Google Baidu隐藏的秘密][13]

## 工具

* [google webmaster tools][14]
* [robots.txt生成工具][15]
* [Perl robots.txt解析器][16]
* [Python robots.txt解析器][17]

## 参考资料

* [robotstxt.org][18]
* [google robots.txt规范][19]
* [robots.txt wikipedia][20]
* [Internet robot wikipedia][21]
* [Web Crawler wikipedia][22]
* [sitemap][23]

[0]: #original
[1]: http://www.taobao.com/robots.txt
[2]: http://www.jd.com/robots.txt
[3]: http://www.baidu.com/robots.txt
[4]: http://zhidao.baidu.com/robots.txt
[5]: http://www.robotstxt.org/db.html
[6]: http://support.google.com/webmasters/bin/answer.py?hl=zh&answer=1061943
[7]: http://www.baidu.com/search/spider.html
[8]: http://www.google.com/robots.txt
[9]: http://www.sitemaps.org/
[10]: http://support.google.com/webmasters/bin/answer.py?hl=zh-Hans&answer=79812
[11]: http://www.w3.org/TR/html401/appendix/notes.html#h-B.4.1.2
[12]: http://en.wikipedia.org/wiki/Captcha
[13]: http://www.pconline.com.cn/pcedu/soft/wl/brower/0610/881177_1.html
[14]: https://www.google.com/webmasters/tools/home?hl=en&authuser=0
[15]: http://www.google.com.hk/search?q=create+robots.txt+tool
[16]: http://search.cpan.org/~gaas/WWW-RobotRules-6.02/lib/WWW/RobotRules.pm
[17]: http://docs.python.org/3.0/library/urllib.robotparser.html
[18]: http://www.robotstxt.org/
[19]: https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt
[20]: https://en.wikipedia.org/wiki/Robots_exclusion_standard
[21]: https://en.wikipedia.org/wiki/Internet_robot
[22]: https://en.wikipedia.org/wiki/Web_crawler
[23]: http://www.sitemaps.org/#informing
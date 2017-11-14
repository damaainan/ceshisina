# 写爬虫时PHP解析HTML最高效的方法那就是用DomCrawler!

 时间 2017-07-09 23:22:33  

原文[https://www.iamle.com/archives/2202.html][1]



## 需求来源,需要用PHP解析HTML提取我想要的数据

用PHP写网站爬虫的时候,需要把爬取的网页进行解析,提取里面想要的数据,这个过程叫做网页HTML中数据结构化。

很多人应该知道用phpQuery像JQuery一样的语法进行网页处理,抽取想要的数据。

但是在复杂一些的场景phpQuery并不能很好的完成工作,说简单点就是复杂场景不好用。

有没有更好的方式呢,我们看看商业爬虫软件是怎么做的。

## 他山之石,商业爬虫都用什么方式解析页面提取字段数据

看看市面上商业爬虫怎么做HTML解析结构化字段抽取

通过观察市面上商业爬虫工具GooSeeker、神箭手、八爪鱼,可以知道他们都用一个叫做XPath表达式的方式提取需要的字段数据。

那么可以判断使用XPath对HTML解析进行数据定位提取就是通行最佳的方式之一。

### PHP中的XPath支持

XPath表达式可以查找HTML节点或元素,是一种路径表达语言。

那么需要先学习下XPath的基础,花个1-2小时入门,XPath就是页面数据提取能力的最佳内功之一，这个时间值得花。

既然用XPath提取页面数据是通行的方式,那么PHP中支持XPath的扩展包是什么呢?

为了帮大家节约时间,Symfony DomCrawler 就是PHP中最佳XPath包之一,直接用他吧，Symfony出品质量可是有目共睹，PHP热门框架laravel都用Symfony的包。

## 撸起袖子干,用DomCrawler做XPath HTML页面解析结构化字段抽取

### 基本思路

在Chrome浏览器中安装”XPath Helper”插件(XPath Helper怎么使用见参考资料)

打开需要解析的网站页面编写和测试XPath表达式

在PHP代码用DomCrawler使用上XPath表达式抽取想要的字段数据

### 实例

解析《神偷奶爸3》页面的的电影介绍信息和短评列表

在Chrome中用编写测试XPath表达式

![][5]

xpath helper使用

在项目下用composer安装guzzlehttp/guzzle(http client)、symfony/dom-crawler(Symfony DomCrawler) 
```
    composer require  guzzlehttp/guzzle
    composer require  symfony/dom-crawler
```
    

下面直接上代码,在代码中用注释做说明

php Douban.php

```php
    <?php
    /**
     * Created by PhpStorm.
     * User: wwek
     * Date: 2017/7/9
     * Time: 21:41
     */
     
    require __DIR__ . '/vendor/autoload.php';
     
    use GuzzleHttp\Client;
    use Symfony\Component\DomCrawler\Crawler;
     
    print_r(json_encode(Spider(), JSON_UNESCAPED_UNICODE));
    //print_r(Spider());
     
    function Spider()
    {
        //需要爬取的页面
        $url = 'https://movie.douban.com/subject/25812712/?from=showing';
     
        //下载网页内容
        $client   = new Client([
            'timeout' => 10,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; Baiduspider-render/2.0; +http://www.baidu.com/search/spider.html)',
            ],
        ]);
        $response = $client->request('GET', $url)->getBody()->getContents();
     
        //进行XPath页面数据抽取
        $data    = []; //结构化数据存本数组
        $crawler = new Crawler();
        $crawler->addHtmlContent($response);
     
        try {
            //电影名称
            //网页结构中用css选择器用id的比较容易写xpath表达式
            $data['name'] = $crawler->filterXPath('//*[@id="content"]/h1/span[1]')->text();
            //电影海报
            $data['cover'] = $crawler->filterXPath('//*[@id="mainpic"]/a/img/@src')->text();
            //导演
            $data['director'] = $crawler->filterXPath('//*[@id="info"]/span[1]/span[2]')->text();
            //多个导演处理成数组
            $data['director'] = explode('/', $data['director']);
            //过滤前后空格
            $data['director'] = array_map('trim', $data['director']);
     
            //编剧
            $data['cover'] = $crawler->filterXPath('//*[@id="info"]/span[2]/span[2]/a')->text();
            //主演
            $data['mactor'] = $crawler->filterXPath('//*[@id="info"]/span[contains(@class,"actor")]/span[contains(@class,"attrs")]')->text();
            //多个主演处理成数组
            $data['mactor'] = explode('/', $data['mactor']);
            //过滤前后空格
            $data['mactor'] = array_map('trim', $data['mactor']);
     
            //上映日期
            $data['rdate'] = $crawler->filterXPath('//*[@id="info"]')->text();
            //使用正则进行抽取
            preg_match_all("/(\d{4})-(\d{2})-(\d{2})\(.*?\)/", $data['rdate'], $rdate); //2017-07-07(中国大陆) / 2017-06-14(安锡动画电影节) / 2017-06-30(美国)
            $data['rdate'] = $rdate[0];
            //简介
            //演示使用class选择器的方式
            $data['introduction'] = trim($crawler->filterXPath('//div[contains(@class,"indent")]/span')->text());
     
            //演员
            //本xpath表达式会得到多个对象结果,用each方法进行遍历
            //each是传入的参数是一个闭包,在闭包中使用外部的变量使用use方法,并使用变量指针
            $crawler->filterXPath('//ul[contains(@class,"celebrities-list from-subject")]/li')->each(function (Crawler $node, $i) use (&$data) {
                $actor['name']   = $node->filterXPath('//div[contains(@class,"info")]/span[contains(@class,"name")]/a')->text(); //名字
                $actor['role']   = $node->filterXPath('//div[contains(@class,"info")]/span[contains(@class,"role")]')->text(); //角色
                $actor['avatar'] = $node->filterXPath('//a/div[contains(@class,"avatar")]/@style')->text(); //头像
                //background-image: url(https://img3.doubanio.com/img/celebrity/medium/5253.jpg) 正则抽取头像图片
                preg_match_all("/((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+\.(jpg|jpeg|gif|png)/", $actor['avatar'], $avatar);
                $actor['avatar'] = $avatar[0];
                //print_r($actor);
                $data['actor'][] = $actor;
            });
     
        } catch (\Exception $e) {
     
        }
     
        return $data;
     
    }
     
```
    

执行结果

```
    {
        "name": "神偷奶爸3 Despicable Me 3",
        "cover": "肯·道里欧",
        "director": [
            "凯尔·巴尔达",
            "皮艾尔·柯芬"
        ],
        "mactor": [
            "史蒂夫·卡瑞尔",
            "克里斯汀·韦格",
            "崔·帕克",
            "米兰达·卡斯格拉夫",
            "拉塞尔·布兰德",
            "迈克尔·贝亚蒂",
            "达纳·盖尔",
            "皮艾尔·柯芬",
            "安迪·尼曼"
        ],
        "rdate": [
            "2017-07-07(中国大陆)",
            "2017-06-14(安锡动画电影节)",
            "2017-06-30(美国)"
        ],
        "introduction": " 《神偷奶爸3》将延续前两部的温馨、搞笑风格，聚焦格鲁和露西的婚后生活，继续讲述格鲁和三个女儿的爆笑故事。“恶棍”奶爸格鲁将会如何对付大反派巴萨扎·布莱德，调皮可爱的小黄人们又会如何耍贱卖萌，无疑让全球观众万分期待。该片配音也最大程度沿用前作阵容，史蒂夫·卡瑞尔继续为男主角格鲁配音，皮埃尔·柯芬也将继续为经典角色小黄人配音，而新角色巴萨扎·布莱德则由《南方公园》主创元老崔·帕克为其配音。",
        "actor": [
            {
                "name": "皮艾尔·柯芬 ",
                "role": "导演",
                "avatar": [
                    "https://img3.doubanio.com/img/celebrity/medium/1389806916.36.jpg"
                ]
            },
            {
                "name": "凯尔·巴尔达 ",
                "role": "导演",
                "avatar": [
                    "https://img3.doubanio.com/img/celebrity/medium/51602.jpg"
                ]
            },
            {
                "name": "史蒂夫·卡瑞尔 ",
                "role": "饰 Gru / Dru",
                "avatar": [
                    "https://img3.doubanio.com/img/celebrity/medium/15731.jpg"
                ]
            },
            {
                "name": "克里斯汀·韦格 ",
                "role": "饰 Lucy Wilde",
                "avatar": [
                    "https://img3.doubanio.com/img/celebrity/medium/24543.jpg"
                ]
            },
            {
                "name": "崔·帕克 ",
                "role": "饰 Balthazar Bratt",
                "avatar": [
                    "https://img3.doubanio.com/img/celebrity/medium/5253.jpg"
                ]
            },
            {
                "name": "米兰达·卡斯格拉夫 ",
                "role": "饰 Margo",
                "avatar": [
                    "https://img1.doubanio.com/img/celebrity/medium/1410165824.37.jpg"
                ]
            }
        ]
    }
```
    

## 经验

网页上数据是动态变化的,没获取到的字段需要用try catch进行异常处理,这样程序就不会崩溃

XPath表达式写得多了基本能应付绝大多数的需求

某些数据如果用XPath表达式也不好取,或者取出来的数据还需要加工的,用正则表达式处理,用preg_match_all进行抽取,用preg_replace进行替换

用strip_tags()函数去除HTML、XML以及PHP的标签,加参数可以保留标签去除,如处理文章内容strip_tags($str, ”

“)   
一些常用的正则表达式 

```php
    $str=preg_replace("/\s+/", " ", $str); //过滤多余回车
    $str=preg_replace("/<[ ]+/si","<",$str); //过滤<__("<"号后面带空格)
     
    $str=preg_replace("/<\!--.*?-->/si","",$str); //注释
    $str=preg_replace("/<(\!.*?)>/si","",$str); //过滤DOCTYPE
    $str=preg_replace("/<(\/?html.*?)>/si","",$str); //过滤html标签
    $str=preg_replace("/<(\/?head.*?)>/si","",$str); //过滤head标签
    $str=preg_replace("/<(\/?meta.*?)>/si","",$str); //过滤meta标签
    $str=preg_replace("/<(\/?body.*?)>/si","",$str); //过滤body标签
    $str=preg_replace("/<(\/?link.*?)>/si","",$str); //过滤link标签
    $str=preg_replace("/<(\/?form.*?)>/si","",$str); //过滤form标签
    $str=preg_replace("/cookie/si","COOKIE",$str); //过滤COOKIE标签
    $str=preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si","",$str); //过滤applet标签
    $str=preg_replace("/<(\/?applet.*?)>/si","",$str); //过滤applet标签
    $str=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si","",$str); //过滤style标签
    $str=preg_replace("/<(\/?style.*?)>/si","",$str); //过滤style标签
    $str=preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si","",$str); //过滤title标签
    $str=preg_replace("/<(\/?title.*?)>/si","",$str); //过滤title标签
    $str=preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si","",$str); //过滤object标签
    $str=preg_replace("/<(\/?objec.*?)>/si","",$str); //过滤object标签
    $str=preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si","",$str); //过滤noframes标签
    $str=preg_replace("/<(\/?noframes.*?)>/si","",$str); //过滤noframes标签
    $str=preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si","",$str); //过滤frame标签
    $str=preg_replace("/<(\/?i?frame.*?)>/si","",$str); //过滤frame标签
    $str=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si","",$str); //过滤script标签
    $str=preg_replace("/<(\/?script.*?)>/si","",$str); //过滤script标签
    $str=preg_replace("/javascript/si","Javascript",$str); //过滤script标签
    $str=preg_replace("/vbscript/si","Vbscript",$str); //过滤script标签
    $str=preg_replace("/on([a-z]+)\s*=/si","On\\1=",$str); //过滤script标签
    $str=preg_replace("/&#/si","&＃",$str); //过滤script标签，如javAsCript:alert(
```
    

## 参考资料

[八爪鱼的了解XPath常用术语和表达式解析 十分钟轻松入门][6]

[gooseeker的xpath基础知识培训][7]

[神箭手的常用的辅助开发工具][8]

[XPath Helper：chrome爬虫网页解析工具 Chrome插件图文教程][9]

[在PHP中，您如何解析和处理HTML/XML？][10]

## 扩展阅读

[关于反爬虫，看这一篇就够了][11]

[最好的语言PHP + 最好的前端测试框架Selenium = 最好的爬虫（上）][12]


[1]: https://www.iamle.com/archives/2202.html

[5]: https://img1.tuicool.com/ZZrauiq.png
[6]: http://www.bazhuayu.com/blog/20140917.aspx
[7]: http://www.gooseeker.com/doc/article-248-1.html
[8]: http://docs.shenjianshou.cn/develop/tools/tools.html
[9]: http://blog.csdn.net/love666666shen/article/details/72613143
[10]: https://gxnotes.com/article/64945.html
[11]: https://mp.weixin.qq.com/s?__biz=MjM5MDI3MjA5MQ==&mid=2697265241&idx=2&sn=f2965d124d07fe5efcdc85094eb1c2df&scene=21
[12]: https://mp.weixin.qq.com/s?__biz=MzIyODY2OTQ3Mg==&mid=2247483721&idx=1&sn=132a10bb1076c3a627542a17f334bb09&chksm=e84f2006df38a910afb7ed776866b94220f321a23803752735ca2306a922ba686f239afb75af&mpshare=1&scene=1&srcid=12109WDtK6ceBsNWNT1yKbFR#rd
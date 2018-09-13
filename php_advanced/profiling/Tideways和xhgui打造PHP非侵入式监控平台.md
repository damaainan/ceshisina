## Tideways和xhgui打造PHP非侵入式监控平台

<font face=微软雅黑>

2017年06月13日 03:45:24 投稿人：[PHP探秘][0]

当我们发现生产环境的某个接口执行时间特别长时应该怎么做？是不是直接登录线上机器单步调试？或者打大量的log然后分析？ 一般我们可以把分析流程分为如下几步操作：

1. 分析开发环境下执行是否会慢；

如果是代码问题，在开发环境下就能检测出来；
1. 分析预发环境执行是否会慢；

如果是数据库或者第三方扩展问题，在预发环境就能检查出来。
1. 从生产环境摘一台机器，分析代码执行慢的原因；

如果是机器的问题，在生产环境就能检查出来。

1，2，3步骤都需要去分析代码，看哪部分执行时间长。如果人工一句一句代码去排查，很容易导致用户流失。大多时候我们会使用第三方的分析工具tideways或者xhprof来快速发现问题。选择哪一个工具比较好呢？xhprof虽然来自facebook但已经很久不更新，官方源已经显示This package is abandoned and no longer maintained（此包已废弃，不再维护）。tideways恰好相反，一直有商业公司在维护，并且积极的支持了PHP7。两个扩展都是开源的，综上所述我建议大家选择tideways来分析代码。

tideways扩展能把每条请求生成详细的执行日志，我们通过对日志做简单的分析就能看到程序哪部分耗时最长，这里可以使用xhprof的UI程序（xhprof生成的日志和tideways生成的日志格式通用），交互虽然不大友好但是够用了。如果想有更好的视觉效果，建议下载xhgui，一款基于Bootstrap的xhprof UI程序。

在开始搭建PHP非侵入式监控平台之前，我需要解释几个问题。

### 一. Tideways这家公司如何盈利？

Tideways这家公司与Sentry的营销模式一样，都是卖存储服务+数据分析服务。

tideways.so扩展是开源的，可以免费使用。但是tideways.so扩展只能生成日志文件，我们获得日志文件后还需要花很长时间去整理和分析。如果你购买了Tideways的服务，就能无缝的将日志存储到他们的服务器，登录他们提供的后台就能看到项目代码和服务器的运行状况。加上各种可视化的图表展示，体验非常的好，有很多大公司愿意付费。

### 二. 安装扩展后代码改动会不会很大？

tideways.so扩展提供的监控方式是非侵入式的监控，不会对当前项目有任何的影响。我们只需要在Nginx配置文件中加上一行配置即可：

> fastcgi_param PHP_VALUE "auto_prepend_file=/home/admin/xhgui-branch/external/header.php";

> 代码的含义：在执行主程序前都运行我们指定的PHP脚本

具体如何安装这个服务，我在文章的下半部分会详细说明。现在我们需要知道『非侵入式的监控』就是不用改动一行项目代码。

### 三. 每个请求都生成日志会不会影响服务本身？

用户的每次请求都生成执行日志对服务会有轻微的影响。虽然tideways.so扩展提供的监控方式是非侵入式的不会影响线上项目，但对CPU和内存的消耗是不可忽略的。为了减少对内存和CPU的消耗，我们可以控制生成日志的频率，还能编写生成日志的规则。默认频率为1%(每100个请求生成1条日志，这里的概率非绝对)。

如果有多台服务器，只需要对一台进行监控，机器比较多的话可以每个机房一台。

## 搭建非侵入式监控环境

1. 安装PHP mongodb扩展；

> sudo pecl install mongodb

1. 安装PHP tideaways扩展；

tideaways的文档写的非常详细，[安装tideaways扩展（官方文档）][1] 这里我用Centos举例。

```shell
  > echo "[tideways] name = Tideways baseurl = https://s3-eu-west-1.amazonaws.com/qafoo-profiler/rpm" > /etc/yum.repos.d/tideways.repo
  > rpm --import https://s3-eu-west-1.amazonaws.com/qafoo-profiler/packages/EEB5E8F4.gpg
  > yum makecache --disablerepo=* --enablerepo=tideways
  > yum install tideways-php tideways-cli tideways-daemon
```

PS: MarkDown的语法转换可能有部分问题，容易把中划线转没了，这里建议安装时从官网COPY命令，[安装tideaways扩展][2]

1. 修改php.ini文件；

我们需要在php.ini文件中引入扩展

```ini
[mongodb]
extension=mongodb.so
[tideways]
extension=tideways.so
;不需要自动加载，在程序中控制就行
tideways.auto_prepend_library=0
;频率设置为100，在程序调用时能改
tideways.sample_rate=100
```

1. 安装mongodb-server（可选择安装mongodb客户端）;

我们需要在系统中安装mongodb-server，用来存储tideways扩展生成的日志。多台服务器也只需要安装一个mongodb-server，用来做日志归拢。如果有单独的mongodb机器，可以跳过这一步。

Centos下安装MongoDB服务：

> sudo yum install mongodb-server

启动服务：

> sudo service mongod start

Centos下安装MongoDB客户端：

> sudo yum install mongodb
1. 安装xhgui；

```bash
git clone https://github.com/laynefyc/xhgui-branch.git
cd xhgui
php install.php
```

PS: xhgui官方版本已经很久不更新，很多符号和单位都不适合中国用户。为了方便自己，我单独维护了一个版本，不断的在更新中。安装这个版本，将有更好的体验。需要安装原版的请执行下面的命令

```bash
    git clone https://github.com/perftools/xhgui
    cd xhgui
    php install.php
```

如果你的MongoDB安装在当前机器，可以不用修改xhgui的配置文件，如果不是你需要在配置文件中修改MongoDB的连接ip和域名，xhgui-branch/config/config.default.php。当然你也可以选择直接存为文件。

```php
// Can be either mongodb or file.
/*
save.handler => file,
save.handler.filename => dirname(__DIR__) . /cache/ . xhgui.data. . microtime(true) . _ . substr(md5($url), 0, 6),
*/
save.handler => mongodb,

// Needed for file save handler. Beware of file locking. You can adujst this file path
// to reduce locking problems (eg uniqid, time ...)
//save.handler.filename => __DIR__./../data/xhgui_.date(Ymd)..dat,
db.host => mongodb://127.0.0.1:27017,
db.db => xhprof,
```

1. 测试MongoDB连接情况并优化索引；

你在当前机器安装过mongo客户端才能调用mongo命令。

```
    $ mongo
    > use xhprof
    > db.results.ensureIndex( { meta.SERVER.REQUEST_TIME : -1 } )
    > db.results.ensureIndex( { profile.main().wt : -1 } )
    > db.results.ensureIndex( { profile.main().mu : -1 } )
    > db.results.ensureIndex( { profile.main().cpu : -1 } )
    > db.results.ensureIndex( { meta.url : 1 } )
```

1. 配置Nginx；

Nginx需要加入两处配置，一个是PHP_VALUE：

```nginx
server {
  listen 80;
  server_name site.localhost;
  root /Users/markstory/Sites/awesome-thing/app/webroot/;
  fastcgi_param PHP_VALUE "auto_prepend_file=/Users/markstory/Sites/xhgui/external/header.php";
}
```

另一个是需要配置一个路径指向5中安装的xhgui的webroot目录，如下配置为单独申请了一个域名：

```nginx
server {
    listen       80;
    server_name  blog110.it2048.cn;
    root  /home/admin/xhgui-branch/webroot;

    location / {
        index  index.php;
        if (!-e $request_filename) {
            rewrite . /index.php last;
        }
    }

    location ~ .php$ {
        fastcgi_pass   127.0.0.1:9001;
        fastcgi_index  index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

- - -

如果安装很顺利，此时访问 http://blog110.it2048.cn 能看到效果。详细的效果图可以看这里 [https://github.com/laynefyc/xhgui][3]

首页截图 

![首页截图][4]

瀑布图 

![瀑布图][5]

函数监控图 

![函数监控图][6]

最后我们来说说频率如何配置，还是在xhgui的config/config.default.php文件中

```php
profiler.enable => function() {
    // 如果域名为我们新建的域名则不捕获
    if($_SERVER[SERVER_NAME] == blog110.it2048.cn){
        return False;
    }else{
        // 100%采样，默认为1%
        return True;//rand(1, 100) === 42;
    }
}
```


数据存储到MongoDB之后，UI如何展示需要我们自己探究。比如将英文换成中文，添加曲线图和饼图等等。至此已经能实时监控我们项目的CPU、内存的消耗情况。哪些接口执行慢也能一目了然。[https://github.com/laynefyc/xhgui-branch][7] 这是我维护的一个xhpui汉化版本，欢迎使用和 [提建议][8]

<font face=楷体>

申明: 文章由 [极客博客][0]整理发表,转载需带网页链接   
作者: [极客导航][9](http://it2048.cn/)[极客博客][0](http://blog.it2048.cn/)   
文章地址: [http://blog.it2048.cn/article_tideways-xhgui.html][10]

</font>
</font>

[0]: http://blog.it2048.cn/
[1]: https://tideways.io/profiler/docs/setup/installation
[2]: https://tideways.io/profiler/docs/setup/installation#redhatfedoracentos
[3]: https://github.com/laynefyc/xhgui
[4]: ../img/homepage.png
[5]: ../img/waterfall.png
[6]: ../img/view-function.png
[7]: https://github.com/laynefyc/xhgui-branch
[8]: https://github.com/laynefyc/xhgui-branch/issues
[9]: http://it2048.cn/
[10]: http://blog.it2048.cn/article_tideways-xhgui.html
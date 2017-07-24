## RewriteCond 与 RewriteRule 指令格式配置详解

上面花了大量的时间讲述VirtualHost 里面的一些配置参数的写法和作用，接下来就是rewrite的重点了，3个核心的东西：**RewriteEngine，RewriteCond，RewriteRule**

**RewriteEngine**  
这个是rewrite的总开关，用来开启是否启动url rewrite，要想打开，像这样就可以了：

    RewriteEngine on

**RewriteCond 和 RewriteRule**  
表示指令定义和匹配一个规则条件，让RewriteRule来重写。说的简单点，RewriteCond就像我们程序中的if语句一样，表示如果符合某个或某几个条件则执行RewriteCond下面紧邻的RewriteRule语句，这就是RewriteCond最原始、基础的功能。

先看个例子：

    RewriteEngine on
    RewriteCond  %{HTTP_USER_AGENT}  ^Mozilla//5/.0.*
    RewriteRule  index.php            index.m.php
    

上面的匹配规则就是：如果匹配到http请求中HTTP_USER_AGENT 是 Mozilla//5/.0.* 开头的，也就是用FireFox浏览器访问index.php这个文件的时候，会自动让你访问到index.m.php这个文件。

**RewriteCond 和 RewriteRule 是上下对应的关系。可以有1个或者好几个RewriteCond来匹配一个RewriteRule**

RewriteCond一般是这样使用的

    RewriteCond %{XXXXXXX} + 正则匹配条件
    

那么RewriteCond可以匹配什么样的数据请求呢？   
它的使用方式是：RewriteCond %{NAME_OF_VARIABLE} REGX FLAG

    RewriteCond %{HTTP_REFERER} (www.test.cn)
    RewriteCond %{HTTP_USER_AGENT}  ^Mozilla//5/.0.*
    RewriteCond %{REQUEST_FILENAME} !-f
    

上面是常见的3种最常见使用最多的HTTP头连接与请求匹配。

**HTTP_REFERER**  
这个匹配访问者的地址，php中$_REQUREST中也有这个，当我们需要判断或者限制访问的来源的时候，就可以用它。

比如：

    RewriteCond %{HTTP_REFERER} (www.test.cn)
    RewriteRule (.*)$ test.php
    

上面语句的作用是如果你访问的上一个页面的主机地址是www.test.cn，则无论你当前访问的是哪个页面，都会跳转到对test.php的访问。

再比如，也可以利用 HTTP_REFERER 防倒链，就是限制别人网站使用我网站的图片。

    RewriteCond %{HTTP_REFERER} !^$ [NC]
    RewriteCond %{HTTP_REFERER} !ww.iyangyi.com [NC]
    RewriteRule \.(jpg|gif) http://image.baidu.com/ [R,NC,L]
    

NC nocase的意思，忽略大小写。第一句呢，是必须要有域名，第一句就是看域名如果不是 www.iyangyi.com 的，当访问.jpg或者.gif文件时候，就都会自动跳转到 [http://image.baidu.com/](http://image.baidu.com/) 上，很好的达到了防盗链的要求。

**REQUEST_FILENAME**  
这个基本是用的最多的，以为url重写是用的最多的，它是匹配当前访问的域名文件，那哪一块属于REQUEST_FILENAME 呢？是url 除了host域名外的。

    http://www.rainleaves.com/html/1569.html?replytocom=265
    

这个url，那么 REQUEST_FILENAME 就是 html/1569.html?replytocom=265看个例子：

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^room/video/(\d+)\.html web/index\.php?c=room&a=video&r=$1 [QSA,NC,L]
    

**-d 是否是一个目录. 判断TestString是否不是一个目录可以这样: !-d  
-f 是否是一个文件. 判断TestString是否不是一个文件可以这样: !-f这两句语句RewriteCond的意思是请求的文件或路径是不存在的，如果文件或路径存在将返回已经存在的文件或路径。一般是这样结合在一起用的。**


上面RewriteRule正则的意思是以 room开头的 room/video/123.html 这样子，变成 web/index.php?c=room&a=video&r=123

$1 表示匹配到的第一个参数。

## RewriteRule 写法和规则

RewriteRule是配合RewriteCond一起使用，可以说，RewriteRule是RewriteCond成功匹配后的执行结果，所以，它是很重要的。

来看一下 RewriteRule的写法：

    RewriteRule Pattern Substitution [flags]
    

Pattern是一个正则匹配。Substitution是匹配的替换 [flags]是一些参数限制；

我们看几个例子：

    RewriteRule ^room/video/(\d+)\.html web/index\.php?c=room&a=video&r=$1 [QSA,NC,L]
    

意思是 以 room开头的 room/video/123.html 这样子，变成 web/index.php?c=room&a=video&r=123

    RewriteRule \.(jpg|gif) http://image.baidu.com/ [R,NC,L]
    

意思是以为是访问.jpg或者gif的文件，都会调整到 [http://image.baidu.com](http://image.baidu.com)

所以，掌握正则级是关键所在了。以后，我会专门搞一个正则的篇章来学习下。

我们再看看[flags]是什么意思？

因为它太多了。我就挑几个最常用的来说说吧。

[QSA] qsappend(追加查询字符串)的意思，次标记强制重写引擎在已有的替换字符串中追加一个查询字符串，而不是简单的替换。如果需要通过重写规则在请求串中增加信息，就可以使用这个标记。上面那个room的例子，就必须用它。

NC nocase(忽略大小写)的意思，它使Pattern忽略大小写，也就是在Pattern与当前URL匹配时，"A-Z"和"a-z"没有区别。这个一般也会加上，因为我们的url本身就不区分大小写的。

R redirect(强制重定向)的意思，适合匹配Patter后，Substitution是一个http地址url的情况，就调整出去了。上面那个调整到image.baidu.com的例子，就必须也用它。

L last(结尾规则)的意思，就是已经匹配到了，就立即停止，不再匹配下面的Rule了，类似于编程语言中的break语法，跳出去了。

其他的一些具体的语法，可以参考以下资料：

[http://www.skygq.com/2011/02/21/apache%E4%B8%ADrewritecond%E8%A7%84%E5%88%99%E5%8F%82%E6%95%B0%E4%BB%8B%E7%BB%8D%E8%BD%AC/][3]

[http://www.2cto.com/os/201201/116040.html][4]

[http://www.cnblogs.com/adforce/archive/2012/11/23/2784664.html][5]

## .htaccess文件的使用

.htaccess文件是啥呢？我们前面说了这么多的配置和修改，都是针对于apache的配置文件来修改的。.htaccess文件就是它的一个替代品。为啥呢？因为你每次修改apache的配置文件，都必须重启apache服务器，很麻烦不说，有些共享apache的服务器，你还没权限修改和重启apache。所以，.htaccess文件就应运而生了。

.htaccess分布式配置文件。它文件名字比较奇怪，没有文件名，只有一个文件后缀就是.htaccess。所以一般在windows下还没法新建这个文件，因为windows不允许文件名是空的，比较蛋疼。但是我相信你总归会有办法新建这个文件的。

.htaccess同时是一个针对目录的配置，你可以把它放到项目的根目录下，那么它就多整个项目其效果，如果你把它放到一个单独的子目录下，那么它就对这个子目录其效果了。

**.htaccess**文件如何生效呢。上面讲配置的时候，我讲过了AllowOverride All这个配置，它就是启动.htaccess文件是否可以使用的。AllowOverrideAll表示可以。AllowOverride None表示禁止使用。还是蛮简单的。

**那.htaccess文件里的语法是怎么写额呢？**

其实和上面说的一模一样的写法。可以完全的搬过来用。没问题。

```apache
     <Directory "D:/wamp/www/testphp/">
        Options Indexes FollowSymLinks
        AllowOverride All
        Order Allow,Deny
        Allow from all
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
    </Directory>
```

上面的apache的<Directory>里的这一块就可以完全的搬到.htaccess文件中来，且效果一模一样。

[3]: http://www.skygq.com/2011/02/21/apache%E4%B8%ADrewritecond%E8%A7%84%E5%88%99%E5%8F%82%E6%95%B0%E4%BB%8B%E7%BB%8D%E8%BD%AC/
[4]: http://www.2cto.com/os/201201/116040.html
[5]: http://www.cnblogs.com/adforce/archive/2012/11/23/2784664.html
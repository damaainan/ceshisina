## 配置虚拟域名

啥是虚拟主机呢？就是说把你自己的本地的开发的机子变成一个虚拟域名，比如：你在开发pptv下面的一个项目 127.0.0.1/pptv_trunk，你想把自己的机器域名变成 www.pptv.com。那么你自己的机器访问方式就成为了一个虚拟域名。

虚拟域名的好处：

虚拟域名的好处就是可以模拟线上的域名，无缝切换测试环境和线上环境，使他们保持统一，更有利于开发和测试。

如何配置呢。一步步来：

**1 .** 打开apache的配置文件 htppd.cnf。分别打开重写扩展和虚拟主机扩展：

> LoadModule rewrite_module modules/mod_rewrite.so 这句前面的 注释 # 去掉  
> Include conf/extra/httpd-vhosts.conf 这句前面的 注释 # 去掉

**2 .** 配置下这个 httpd-vhosts.conf 文件，这个文件就在当前httpd.conf配置文件所在的目录的子目录/extra/下，window和linux 差不多稍有不同

文件里面有几个例子，我们先搞一个，其实很简单。

    
```apache
    NameVirtualHost *:80
    <VirtualHost *:80>
        DocumentRoot "D:/wamp/www/pptv_trunk"
        ServerName www.pptv.com
        <Directory "D:/wamp/www/pptv_trunk>
            Options Indexes FollowSymLinks
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>
```

好，这样一个虚拟域名在Apache上就配置好了。

**3 .** 打开windows/Linux的hosts配置文件，这个文件是系统的dns路由文件：

> windows 在 C:\Windows\System32\drivers\etc\ 目录下    
> linux 在 /etc/hosts\ 目录下。

我们编辑一下，绑定刚才的pptv项目,加上这条：

    127.0.0.1 pptv.com www.pptv.com

**4 .** 打开浏览器访问 www.pptv.com 那么就会定位到刚才的 D:/wamp/www/pptv_trunk项目了。和本地开发一模一样。   
**5 .** 如果想切换到其他的环境，只需要修改hosts文件就可以了：

> 内测环境 192.168.100.104 www.pptv.com pptv.com  
> 预发布环境 112.123.132.33 www.pptv.com pptv.com   
> 生产线上就用#注释掉，不需要绑定，就切换到线上环境了。

## 虚拟域名VirtualHost配置详解

什么是rewrite? 就是重写，重写访问的url连接，有时候为了访问的url简洁点，比如：

> www.pptv.com/i/?user_id=123&time=123&from=web

我觉得这个地址太长了不够简洁，就可以依靠apache的虚拟域名规则把它写的简单一点： 

> www.pptv.com/i/123/123/web

看这样是不是舒服多了也更加简洁。

那么如何配置呢？还是得回到咱们刚才的 httpd-vhosts.conf 这个文件，所有的配置都在这个文件当中来完成。

在学习rewrite之前，我们先详细看一下一个Virtuhost的详细配置解读。

我们还是打开刚的一个虚拟域名，我们对着这个讲述，如何配置rewirite

```apache
    NameVirtualHost *:80
    <VirtualHost *:80>
        DocumentRoot "D:/wamp/www/testphp/"
        ServerName php.iyangyi.com
        ServerAlias www.pptv.cn #可省略
        ServerAdmin stefan321@qq.com #可省略
        ErrorLog logs/dev-error.log #可省略
        CustomLog logs/dev-access.log common #可省略
        ErrorDocument 404 logs/404.html #可省略
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
    </VirtualHost>
```
我们一个一个的说下如何配置。

**1 .** 首先需要申明虚拟域名包块，采用xml风格，开始和结束符号对应。*.80表示接受任何ip的80端口，一般是这样写，不改。

    <VirtualHost *:80>
     ***
    </VirtualHost>
**2 .** 进去之后，就是申明DocumentRoot 这是表示：项目代码的路径。填入我们这个项目的代码所在的根目录D:/wamp/www/testphp/就可以了。

**3 .** ServerName 这个是我们的虚拟域名，也是这次修改的关键。

**4 .** ServerAlias 这个是我们的虚拟域名的别名，可以不要，他的出现场景就是我们希望另外一个域名也往这个目录下调整。比如 www.pptv.cn 我们也希望跳到这里来，就可以这样做，但是前提是 www.pptv.cn 也要绑定host 127.0.0.1

**5 .** ServerAdmin 这里填 服务器管理员的邮箱，也可以不要，当服务器出现故障后，如果提前有配置邮箱的话，会往这个邮箱发邮件，或者是显示在网页的错误信息当中。一般我们可以不填。

**6 .** ErrorLog 这里填 错误日志显示路径，当访问出现错误的时候，就会记录到这里，**注意：logs/dev-error.log 这个文件路径是apache的安装目录下的logs 目录** 。可以不要。

比如我们访问 [http://php.iyangyi.com/f.html](http://php.iyangyi.com/f.html) 时候， f.html是不存在的一个文件，那么这次就会被记录下来了。

在apache/logs/dev-error.log 中记录下来了：

    [Wed Mar 11 11:14:23 2015] [error] [client 127.0.0.1] File does not exist: D:/wamp/www/testphp/f.html
    

**7 .** CustomLog 这里填 访问日志，用来记录每一次的请求访问，可以不要。**注意：logs/dev-access.log 这个文件路径是apache的安装目录下的logs 目录** 。记住：路径后面加common。

比如我们访问 [http://php.iyangyi.com/f.html](http://php.iyangyi.com/f.html) 时候

在apache/logs/dev-access.log 中记录下来了这次的访问：

    127.0.0.1 - - [11/Mar/2015:11:14:23 +0800] "GET /f.html HTTP/1.1" 404 177
    

**8 .** ErrorDocument 这里填 403,404等错误信息调整页面，用来访问出现404页面等情况时的错误页面展示，比较有用，也可以不要。**注意：/404.html 这个文件路径是项目的根目录，不是apache的目录** 。

我这里放到了D:/wamp/www/testphp/404.html 这里，所以，我们访问一个不存在的文件时，就会自动跳转到这个404.html页面了。

比如我们访问 [http://php.iyangyi.com/f.html][0] 时候，就会显示404.html的内容：

    404.html 的内容：
    
    404啊这是没找到页面啊傻逼
    

所以，我们可以根据业务需要把常用的状态码都给用上：

    ErrorDocument 403 /403.html
    ErrorDocument 404 /404.html
    ErrorDocument 405 /405.html
    ErrorDocument 500 /500.html
    ErrorDocument 503 /503.html
    

**9 .** <Directory "D:/wamp/www/testphp/"> ***** </Directory> 这个是最重要的一步了，这里也是填本项目的路径，然后所有的rewrite规则都是在里面完成。所以这个是很重要的。

**10 .** 我们进入到<Directory> 层次中，这里面很多都是很关键的。我们主要看一些常用的，也是很关键的。
**Options Indexes FollowSymLinks** 这是来设置是否来显示文件根目录的目录列表的。

设置成：Options Indexes FollowSymLinks 就表示：我访问php.iyangyi.com，如果文件根目录里有 index.html(index.php)，浏览器就会显示 index.html的内容，如果没有 index.html，浏览器就会显示这文件根目录的目录列表，目录列表包括文件根目录下的文件和子目录。

到底是优先显示index.php还是index.html 有apache的配置决定的：

    <IfModule dir_module>
        DirectoryIndex index.html index.htm index.php index.php3  
    </IfModule>
    

哪个在前面，目录下如果有这个文件，就优先显示哪个。

**如果我不想让别人访问我的目录结构咋搞？** ，可以将这个Indexs去掉，或者这样:-Index 就可以啦。

变成：Options FollowSymLinks 或者 Options -Indexes FollowSymLinks再次访问 php.iyangyi.com ，我们把index.php和index.html删掉了，刷新浏览器，就会显示：

    Forbidden
    
    You don't have permission to access / on this server.
    

**11 .** AllowOverride All 这个是干嘛的呢？其他教材说的很复杂，我们说简单点，就是允许根目录下的.htaccess起rewrite作用，下面会说到的，我们在根目录下放一个.htaccess文件，也是可以起到url rewrite作用的。

如果想禁止掉这个根目录下的.htaccess文件，就可以这样： AllowOverride None 就可以了。

**12 .** Order Deny,Allow Allow from all这2个一般是组合在一起用。用来设置访问权限 ，设置哪些ip可以访问这个域名, 哪些ip禁止访问。

所以order是设置这2个的组合排序, 不区分大小写，中间用,分开，中间不能有空格。   
Order Deny,Allow ：表示设定“先检查禁止设定，没有设定禁止的全部允许” 

Order Allow,Deny : 表示设定“先检查允许设定，没有设定允许的全部禁止”

**而且最后的访问结果有第二参数决定！**

Deny from All  Deny from 127.0.0.1 禁止访问的ip， all 表示全部   
Allow from All   Allow from 127.0.0.1 允许访问的ip， all 表示全部

我们看几个他们2个组合的例子。

这个例子：

    Order Deny,Allow
    Deny from All
    

表示先检查允许的, 没有允许的全部禁止。但是下却没有Allow，那么就表示是无条件禁止了所有的访问了。

    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
    

上面表示 只允许127.0.0.1访问

    Order Allow,Deny
    Allow from all
    Deny from 127.0.0.1 192.168.1.51
    

上面表示禁止127.0.0.1和192.168.1.51访问，其他都可以！

所以这个的组合就可以达到很多的过滤访问效果。
## [Laravel学习笔记之基于PHPStorm编辑器的Laravel开发](https://segmentfault.com/a/1190000004505815)

### 引言

本文主要讲述在PHPStorm编辑器中如何使用PHPStorm的Laravel插件和Laravel IDE Helper来开发Laravel程序，结合个人积累的一点经验来说明使用PHPStorm编辑器来开发程序还是很顺手的，内容主要基于PHPStorm官方文档Laravel Development using PhpStorm。

### 学习主题

本文主要涉及以下几个技巧：

1. Composer的初始化
1. Laravel IDE Helper的安装
1. Laravel Plugin的安装
1. PHPStorm对Laravel框架的支持
1. PHPStorm对Blade模板引擎的支持
1. 使用PHPStorm调试Laravel程序
1. 使用PHPStorm的单元测试功能
1. 使用PHPStorm的数据库功能

### 1、一些准备工作

(1)、初始化composer  
PHPStorm提供了composer配置功能，可以在PHPStorm新建一个空项目，然后在空项目根目录右键选择Composer|Init Composer...，然后点击从getcomposer.org网上下载，PHPStorm会自动下载composer.phar文件，并在项目根目录下生成composer.json文件，速度也很快：

![][0] 

![][1]

对于composer.json文件中数组key字段值可以在[Composer官网][2]上查找相关解释，包括重要的require和require-dev字段解释。  
(2)、安装Laravel IDE Helper  
安装也很简单，还是在项目根目录右键找到Composer选择Add Dependancy...，搜索laravel-ide-helper选择安装就行，如果composer.json文件中"minimum-stability":"stable"那就必须要安装个稳定版的，我这里选择v2.1.2稳定版。安装后就会在根目录下自动生成一个vendor文件夹，该文件夹存放各种依赖包。当然也可直接在composer.json里添加上require字段值及对应的laravel-ide-helper值，再php composer.phar composer.json update就行，具体可以去packagist.org里去搜laravel-ide-helper找对应的安装方法。

![][3]   
其实，Laravel IDE Helper就是一个第三方安装包，安装后会有些代码提示等一些好处，也可选择不安装，当然安装也比较简单。安装后需要在config/app.php里注册下Service Provider就行：

    
        return array(
            // ...
            'providers' => array(
                // ...
                'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider', // Laravel IDE helper
            ),
            // ...
        );
    

Laracasts官网上有一个有关Laravel Ide Helper的视频，可以看下，[PHPStorm's Laravel Facades Issue][4]

(3)、安装Laravel Plugin  
选择Preference或者Command + ,，选择下方的Browse repositories...浏览插件仓库，并选择安装Laravel Plugin，并重启PHPStorm就行，最后在Preference|Other Settings|Laravel Plugin里选择enable plugin for this project再重启下PHPStorm就OK了:

![][5]   
So，安装Laravel Plugin有啥好处没：主要就是代码补全。针对Routes/Controllers/Views/Configuration/Services/Translations的代码补全，比较方便而已，懒得装也可以不装。举个视图代码补全例子：

![][6]   
效率会高很多，而且安装也很简单，装一个也无妨嘛。

### 2、PHPStorm对Blade模板支持

PHPStorm提供了对Blade模板语法高亮，而且还包括一些指令的补全和浏览，如@include/@section/@extends等等，写代码时很方便：

![][7] 

![][8]

总的来说，PHPStorm对Blade模板的代码提示和补全还是支持的比较好的，使用很顺手。

这里，还推荐一个Laravel Live Templates for PhpStorm，安装地址：[https://github.com/koomai/php...][9]，这个小依赖包也比较好用，建议在PHPStorm中安装下，安装方法和好处可以进去看看，安装很简单。

### 3、使用Xdebug来调试Laravel程序

Xdebug是调试PHP程序的神器，尤其在调试Laravel代码时会非常有用。在PHPStorm中配置Xdebug也很简单，首先看下PHP版本中是否安装了Xdebug扩展：

    php -m
    
    [PHP Modules]
    apcu
    bcmath
    bz2
    calendar
    Core
    ctype
    curl
    date
    dom
    exif
    fileinfo
    filter
    ftp
    gd
    gettext
    gmp
    hash
    http
    iconv
    imap
    intl
    json
    ldap
    libxml
    mbstring
    mcrypt
    mysqli
    mysqlnd
    openssl
    pcntl
    pcre
    PDO
    pdo_mysql
    pdo_pgsql
    pdo_sqlite
    pgsql
    Phar
    posix
    propro
    raphf
    readline
    Reflection
    session
    shmop
    SimpleXML
    soap
    sockets
    SPL
    sqlite3
    standard
    sysvmsg
    sysvsem
    sysvshm
    tidy
    tokenizer
    wddx
    xdebug
    xml
    xmlreader
    xmlrpc
    xmlwriter
    xsl
    Zend OPcache
    zip
    zlib
    
    [Zend Modules]
    Xdebug
    Zend OPcache
    

如果没有装Xdebug扩展的话需要装一下，装完后修改下php.ini把xdebug.so前的路径';'去掉，并重启下PHP就行。如果你是本地开发，是MAC系统的话，可以装集成环境MAMP，该PHP中都有xdebug.so，不过需要使能下php.ini中xdebug扩展。

安装好后xdebug后，需要配置下PHP：

![][10]

使能下PHPStorm中Debug Listening:点击Run->Start listening for PHP Debug Connections，然后点击右上角的下三角设置下：

![][11]

可以选择新建一个PHP Web Application或者PHP Script，选择PHP Web Application的话需要配置下Server，默认本地开发并且路由为localhost，则配置如下：

![][12]

这里以PHP Script举例，如下：

![][13]

然后点击右上角的爬虫图标执行调试，并且各个变量值在调试控制台中显示：

![][14]

大概聊了下在PHPStorm中配置Xdebug，不管咋样，一定要配置好Xdebug，这在平时读代码尤其Laravel源码时会非常有用。

### 4、使用PHPUnit单元测试Laravel程序

首先需要在本地安装下PHPUnit：

     wget https://phar.phpunit.de/phpunit.phar
     chmod +x phpunit.phar
     sudo mv phpunit.phar /usr/local/bin/phpunit
     phpunit --version

然后在PHPStorm中配置下PHPUnit：

![][15]

Configuration file指向本地的phpunit.xml.dist文件，该文件是用来配置phpunit的测试套件的，可以看官网中文版的：[用 XML 配置来编排测试套件  
][16]，比如本人这里的套件配置：

    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit backupGlobals="false"
             backupStaticAttributes="false"
             bootstrap="vendor/autoload.php"
             colors="true"
             convertErrorsToExceptions="true"
             convertNoticesToExceptions="true"
             convertWarningsToExceptions="true"
             processIsolation="false"
             stopOnFailure="false"
             syntaxCheck="true"
             verbose="true"
    >
        <testsuites>
            <testsuite name="flysystem/tests">
                <directory suffix=".php">./tests/</directory>
            </testsuite>
        </testsuites>
        <filter>
            <whitelist>
                <directory suffix=".php">./src/</directory>
            </whitelist>
        </filter>
        <listeners>
            <listener class="Mockery\Adapter\Phpunit\TestListener" file="./vendor/mockery/mockery/library/Mockery/Adapter/Phpunit/TestListener.php"></listener>
        </listeners>
        <logging>
            <!--<log type="coverage-text" target="php://stdout" showUncoveredFiles="true"></log>-->
            <!--<log type="coverage-html" target="coverage" showUncoveredFiles="true"></log>-->
            <!--<log type="coverage-clover" target="coverage.xml" showUncoveredFiles="true"></log>-->
        </logging>
    </phpunit>

在写好PHPUnit测试后，可以在终端执行phpunit命令就行，或者单独执行某个测试类，可以在测试类里右键选择Run xxxTest.php就行，如：

![][16]

写测试还是很有必要的，而且是必须的。PHPUnit写测试，以后还会详聊。### 5、使用PHPStorm的Database链接Laravel程序数据库

PHPStorm中提供了database插件，功能也比较强大，我想用过PHPStorm的应该都知道如何使用，这里聊下一个很好用的一个功能。经常遇到这样的一个情景：数据库装在数据库服务器db.test.com中，但本地是登陆不上去的，但  
在开发服务器host.company.com是可以链接数据库服务器的，那如何使用database来连接数据库服务器呢？

可以通过SSH tunnel来连接，如：

![][17]

这里Auth type可以选择Password或者Key Pair，如果你是通过密码登录开发服务器的，选择Password；如果是通过密钥登陆的，选择Key Pair。然后在general tab中配置下数据库服务器就OK了。

PHPStorm还提供了Remote Host插件来链接远程服务器，点击Tools->Deployment->Browse Remote Host就可看到，这个插件和Database一样同样的方便看服务器的东西，并可以在本地修改病Upload到服务器上，而不用在Terminal中登录到远程服务器，在Terminal上查看修改东西。

欢迎关注[Laravel-China][18]。

[RightCapital][19]招聘[Laravel DevOps][20]

[0]: ./img/bVs5g7.png
[1]: ./img/bVs5g8.png
[2]: https://getcomposer.org/
[3]: ./img/bVs5m6.png
[4]: https://laracasts.com/series/how-to-be-awesome-in-phpstorm/episodes/15
[5]: ./img/bVs5nT.png
[6]: ./img/bVs5pg.png
[7]: ./img/bVs5qq.png
[8]: ./img/bVs5qC.png
[9]: https://github.com/koomai/phpstorm-laravel-live-templates#requests--input
[10]: ./img/bVCZZh.png
[11]: ./img/bVCZZx.png
[12]: ./img/bVCZZU.png
[13]: ./img/bVCZ0s.png
[14]: ./img/bVCZ0x.png
[15]: ./img/bVCZ2n.png
[16]: ./img/bVCZ25.png
[17]: ./img/bVCZ1r.png
[18]: https://laravel-china.org/
[19]: https://www.rightcapital.com
[20]: https://join.rightcapital.com
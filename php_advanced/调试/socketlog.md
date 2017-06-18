#说明

SocketLog适合Ajax调试和API调试， 举一个常见的场景，用SocketLog来做微信调试， 我们在做微信API开发的时候，如果API有bug，微信只提示“改公众账号暂时无法提供服务，请稍候再试” ，我们根本不知道API出来什么问题。  有了SocketLog就不一样了， 我们可以知道微信给API传递了哪些参数， 程序有错误我们也能看见错误信息(下方有张图片，可能加载慢，请耐心等待一下)

![微信调试](https://raw.githubusercontent.com/luofei614/SocketLog/master/screenshots/weixin.png)

# [检测 PHP 应用的代码复杂度][0]

* [php][1]
* [code][2]
* [代码复杂度][3]

[**JellyBool**][4] 6月4日发布 


> 原文来自：[> https://www.laravist.com/blog...][13]

如果说你想知道一个 PHP 项目的代码复杂度是什么样子的，我推荐你可以使用 [phploc][14] 和 [PhpMetrics][15] 来检测一下。

## 1.使用 phploc

这是一个标准的 composer package，不过我推荐大家可以直接使用 composer 全局安装：

    composer global require 'phploc/phploc=*'

然后安装完毕，你就可以使用 phploc 命令来检测你的代码复杂度了：

    phploc ./app

比如上面这行代码就是检测你的项目中 app/ 目录的代码复杂度；如果是一个 Laravel 的项目的话，大概会是这个样子的结果输出：

    phploc 3.0.1 by Sebastian Bergmann.
    
    Directories                                         14
    Files                                               72
    
    Size
      Lines of Code (LOC)                             3748
      Comment Lines of Code (CLOC)                     790 (21.08%)
      Non-Comment Lines of Code (NCLOC)               2958 (78.92%)
      Logical Lines of Code (LLOC)                     950 (25.35%)
        Classes                                        656 (69.05%)
          Average Class Length                           9
            Minimum Class Length                         0
            Maximum Class Length                        84
          Average Method Length                          2
            Minimum Method Length                        0
            Maximum Method Length                       21
        Functions                                        0 (0.00%)
          Average Function Length                        0
        Not in classes or functions                    294 (30.95%)
    
    Cyclomatic Complexity
      Average Complexity per LLOC                     0.10
      Average Complexity per Class                    2.33
        Minimum Class Complexity                      1.00
        Maximum Class Complexity                     15.00
      Average Complexity per Method                   1.41
        Minimum Method Complexity                     1.00
        Maximum Method Complexity                     6.00
    
    Dependencies
      Global Accesses                                    0
        Global Constants                                 0 (0.00%)
        Global Variables                                 0 (0.00%)
        Super-Global Variables                           0 (0.00%)
      Attribute Accesses                               436
        Non-Static                                     436 (100.00%)
        Static                                           0 (0.00%)
      Method Calls                                     570
        Non-Static                                     412 (72.28%)
        Static                                         158 (27.72%)
    
    Structure
      Namespaces                                        15
      Interfaces                                         0
      Traits                                             0
      Classes                                           72
        Abstract Classes                                 0 (0.00%)
        Concrete Classes                                72 (100.00%)
      Methods                                          233
        Scope
          Non-Static Methods                           226 (97.00%)
          Static Methods                                 7 (3.00%)
        Visibility
          Public Methods                               194 (83.26%)
          Non-Public Methods                            39 (16.74%)
      Functions                                         24
        Named Functions                                  0 (0.00%)
        Anonymous Functions                             24 (100.00%)
      Constants                                          0
        Global Constants                                 0 (0.00%)
        Class Constants                                  0 (0.00%)
        

不过你可能也感觉到，这个 phploc 的一大不便之处就是，目前来说，他还不能把相关的测试结果可视化或者说自定义检测的最高复杂度。所以，PhpMetrics 就应运而生了。

## 使用 PhpMetrics

首先需要说明的是，[PhpMetrics][15] 可以更深入到你的代码中，并且会生成一个 html 文件作为分析的结果，这样我们查看检测结果就会非常的直观。

安装 [PhpMetrics][15] 也是可以直接 composer 全局安装：

    composer global require 'phpmetrics/phpmetrics'

安装完毕之后，可以这样来运行命令分析代码复杂度：

    phpmetrics --report-html=report.html ./app

等待 phpmetrics 运行结束，用 Chrome 打开 report.html 就可以查看相对应的结果，大概是这个样子：

![][16]

[0]: https://segmentfault.com/a/1190000009654074
[1]: https://segmentfault.com/t/php/blogs
[2]: https://segmentfault.com/t/code/blogs
[3]: https://segmentfault.com/t/%E4%BB%A3%E7%A0%81%E5%A4%8D%E6%9D%82%E5%BA%A6/blogs
[4]: https://segmentfault.com/u/jellybool
[13]: https://www.laravist.com/blog/post/code-complexity-tools-for-php-apps
[14]: https://github.com/sebastianbergmann/phploc
[15]: http://www.phpmetrics.org/
[16]: https://segmentfault.com/img/bVOFB1?w=694&h=724
 *   正在运行的API有bug，不能var_dump进行调试，因为会影响client的调用。 将日志写到文件，查看也不方便，特别是带调用栈或大数据结构的文件日志，查看日志十分困难。 这时候用SocketLog最好，SocketLog通过websocket将调试日志打印到浏览器的console中。你还可以用它来分析开源程序，分析SQL性能，结合taint分析程序漏洞。
 * Chrome插件安装： https://chrome.google.com/webstore/detail/socketlog/apkmbfpihjhongonfcgdagliaglghcod （如果不能正常访问这个页面，你可以用下面手动安装的方法进行安装）
 * 目录结构：
 * chrome 目录是 chrome插件的源代码
 * chrome.crx 文件是chrome插件的安装包， 如果你无法从chrome应用商店安装，可进行手动安装， 浏览器地址栏输入并打开： chrome://extensions/  ，然后将chrome.crx拖入即可安装。
 * php 目录下的SocketLog.class.php是发送日志的类库,我们在发送日志的时候，需要载入这个类库然后调用函数slog即可。
 * 效果展示： 我们在浏览网站的时候在浏览器console中就知道程序做了什么，这对于二次开发产品十分有用。 下面效果图在console中打印出浏览discuz程序时，执行了哪些sql语句， 以及执行sql语句的调用栈。程序的warning，notice等错误信息也可以打到console中。
![enter image description here][1]

#使用方法
 * 首先，请在chrome浏览器上安装好插件。
 * 安装服务端`npm install -g socketlog-server` , 运行命令 `socketlog-server` 即可启动服务。 将会在本地起一个websocket服务 ，监听端口是1229 。 如果想服务后台运行： `socketlog-server > /dev/null &` 我们提供公用的服务端，需要去申请client_id : http://slog.thinkphp.cn/
 * 如果你的服务器有防火墙，请开启1229和1116两个端口，这两个端口是socketlog要使用的。
 * 在自己的程序中发送日志：


        <?php
        include './php/slog.function.php';
        slog('hello world');
        ?>


 * 用slog函数发送日志， 支持多种日志类型：


        slog('msg','log');  //一般日志
        slog('msg','error'); //错误日志
        slog('msg','info'); //信息日志
        slog('msg','warn'); //警告日志
        slog('msg','trace');// 输入日志同时会打出调用栈
        slog('msg','alert');//将日志以alert方式弹出
        slog('msg','log','color:red;font-size:20px;');//自定义日志的样式，第三个参数为css样式

 * 通过上面例子可以看出， slog函数支持三个参数：
 * 第一个参数是日志内容，日志内容不光能支持字符串哟，大家如果传递数组,对象等一样可以打印到console中。
 * 第二个参数是日志类型，可选，如果没有指定日志类型默认类型为log， 第三个参数是自定样式，在这里写上你自定义css样式即可。

##配置
* 在载入slog.function.php文件后，还可以对SocketLog进行一些配置。
* 例如：我们如果想将程序的报错信息页输出到console，可以配置


        <?php
        include './php/slog.function.php';
        slog(array(
        'error_handler'=>true
        ),'config');
        echo notice;//制造一个notice报错
        slog('这里是输出的一般日志');
        ?>
* 配置SocketLog也是用slog函数， 第一个参数传递配置项的数组，第二个参数设置为config
* 还支持其他配置项
```php
    <?php
    include './php/slog.function.php';
    slog(array(
    'enable'=>true,//是否打印日志的开关
    'host'=>'localhost',//websocket服务器地址，默认localhost
    'optimize'=>false,//是否显示利于优化的参数，如果运行时间，消耗内存等，默认为false
    'show_included_files'=>false,//是否显示本次程序运行加载了哪些文件，默认为false
    'error_handler'=>false,//是否接管程序错误，将程序错误显示在console中，默认为false
    'force_client_id'=>'',//日志强制记录到配置的client_id,默认为空
    'allow_client_ids'=>array()////限制允许读取日志的client_id，默认为空,表示所有人都可以获得日志。
    )
    ,'config');
    ?>
```
* optimize 参数如果设置为true， 可以在日志中看见利于优化参数，如：`[运行时间：0.081346035003662s][吞吐率：12.29req/s][内存消耗：346,910.45kb]` 
* show_included_files 设置为true，能显示出程序运行时加载了哪些文件，比如我们在分析开源程序时，如果不知道模板文件在那里， 往往看一下加载文件列表就知道模板文件在哪里了。
* error_handler 设置为true，能接管报错，将错误信息显示到浏览器console， 在开发程序时notice报错能让我们快速发现bug，但是有些notice报错是不可避免的，如果让他们显示在页面中会影响网页的正常布局，那么就设置error_handler,让它显示在浏览器console中吧。  另外此功能结合php taint也是极佳的。 taint能自动检测出xss，sql注入， 如果只用php taint， 它warning报错只告诉了变量输出的地方，并不知道变量在那里赋值、怎么传递。通过SocketLog， 能看到调用栈，轻松对有问题变量进行跟踪。 更多taint的信息：http://www.laruence.com/2012/02/14/2544.html 
* 设置client_id:  在chrome浏览器中，可以设置插件的Client_ID ，Client_ID是你任意指定的字符串。
![enter image description here][2]
* 设置client_id后能实现以下功能：

* 1，配置allow_client_ids 配置项，让指定的浏览器才能获得日志，这样就可以把调试代码带上线。  普通用户访问不会触发调试，不会发送日志。  开发人员访问就能看的调试日志， 这样利于找线上bug。 Client_ID 建议设置为姓名拼命加上随机字符串，这样如果有员工离职可以将其对应的client_id从配置项allow_client_ids中移除。 client_id除了姓名拼音，加上随机字符串的目的，以防别人根据你公司员工姓名猜测出client_id,获取线上的调试日志。
* 设置allow_client_ids示例代码：


        slog(array(
        'allow_client_ids'=>array('luofei_zfH5NbLn','easy_DJq0z80H')
        ),'set_config')

* 2, 设置force_client_id配置项，让后台脚本也能输出日志到chrome。 网站有可能用了队列，一些业务逻辑通过后台脚本处理， 如果后台脚本需要调试，你也可以将日志打印到浏览器的console中， 当然后台脚本不和浏览器接触，不知道当前触发程序的是哪个浏览器，所以我们需要强制将日志打印到指定client_id的浏览器上面。 我们在后台脚本中使用SocketLog时设置force_client_id 配置项指定要强制输出浏览器的client_id 即可。
* 示例代码:


        <?php
        include './php/slog.function.php';
        slog(array(
        'force_client_id'=>'luofei_zfH5NbLn'
        ),'config');
        slog('test'); `

##支持composer 

 * 使用composer安装命令 `composer require luofei614/socketlog`

 * 直接调用静态方法


        <?php
        require './vendor/autoload.php';
        use think\org\Slog
        //配置socketlog
        Slog::config(array(
            'enable'=>true,//是否打印日志的开关
            'host'=>'localhost',//websocket服务器地址，默认localhost
            'optimize'=>false,//是否显示利于优化的参数，如果运行时间，消耗内存等，默认为false
            'show_included_files'=>false,//是否显示本次程序运行加载了哪些文件，默认为false
            'error_handler'=>false,//是否接管程序错误，将程序错误显示在console中，默认为false
            'force_client_id'=>'',//日志强制记录到配置的client_id,默认为空
            'allow_client_ids'=>array()////限制允许读取日志的client_id，默认为空,表示所有人都可以获得日志。
        ));
        Slog::log('log');  //一般日志
        Slog::error('msg'); //错误日志
        Slog::info('msg'); //信息日志
        Slog::warn('msg'); //警告日志
        Slog::trace('msg');// 输入日志同时会打出调用栈
        Slog::alert('msg');//将日志以alert方式弹出
        Slog::log('msg','color:red;font-size:20px;');//自定义日志的样式，第三个参数为css样式

##支持ThinkPHP
    ThinkPHP5后， 在框架层集成了SocketLog ，只需要设置配置即可用

##对数据库进行调试
  * SocketLog还能对sql语句进行调试，自动对sql语句进行explain分析，显示出有性能问题的sql语句。 如下图所示。 
  ![enter image description here][3]
  * 图中显示出了三条sql语句 ， 第一条sql语句字体较大，是因为它又性能问题， 在sql语句的后台已经标注Using filesort。 我们还可以点击某个sql语句看到sql执行的调用栈，清楚的知道sql语句是如何被执行的，方便我们分析程序、方便做开源程序的二次开发。图中第三条sql语句为被点开的状态。
  * 用slog函数打印sql语句是，第二个参数传递为mysql或mysqli的对象即可。 示例代码：
  


        $link=mysql_connect( 'localhost:3306' , 'root' , '123456' , true ) ;
        mysql_select_db('kuaijianli',$link);
        $sql="SELECT * FROM `user`";
        slog($sql,$link);
后面会以OneThink为实例再对数据库调试进行演示。

通过上面的方法，socketlog还能自动为你检测没有where语句的sql操作，然后自动提示你。

  * 注意，有时候在数据比较少的情况下，mysql查询不会使用索引，explain也会提示Using filesort等性能问题， 其实这时候并不是真正有性能问题， 你需要自行进行判断，或者增加更多的数据再测试。

##对API进行调试
  网站调用了API ，如何将API程序的调试信息也打印到浏览器的console中？ 前面我们讲了一个配置 force_client_id， 能将日志强制记录到指定的浏览器。 用这种方式也可以将API的调试信息打印到console中，但是force_client_id 只能指定一个client_id， 如果我们的开发环境是多人共用，这种方式就不方便了。
  其实只要将浏览器传递给网站的User-Agent 再传递给API， API程序中不用配置force_client_id， 也能识别当前访问程序的浏览器， 将日志打印到当前访问程序的浏览器， 我们需要将SDK代码稍微做一下修改。 调用API的SDK，一般是用curl写的，增加下面代码可以将浏览器的User-Agent传递到API 。 


        $headers=array();
        if(isset($_SERVER['HTTP_USER_AGENT']))
        {
            $headers[]='User-Agent: '.$_SERVER['HTTP_USER_AGENT'];
        }
        if(isset($_SERVER['HTTP_SOCKETLOG']))
        {
            $headers[]='Socketlog: '.$_SERVER['HTTP_SOCKETLOG'];
        }
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers); 

##区分正式和开发环境

  进入chrome浏览器的“工具”-->“扩展程序”  ，  点击SocketLog的“选项”进行设置。


##分析开源程序

   有了SocketLog，我们能很方便的分析开源程序，下面以OneThink为例， 大家可以在 http://www.topthink.com/topic/2228.html 下载最新的OneThink程序。 安装好OneThink后，按下面步骤增加SocketLog程序。 

 * 将SocketLog.class.php复制到OneThink的程序目录中，你如果没有想好将文件放到哪个子文件夹，暂且放到根目录吧。 
 * 编辑入口文件index.php, 再代码的最前面加载slog.function.php ,并设置SocketLog


   

        <?php
            include './slog.function.php';
            slog(array(
             'error_handler'=>true,
             'optimize'=>true,
             'show_included_files'=>true
            ),'config');


 - 编辑ThinkPHP/Library/Think/Db/Driver.class.php 文件，在这个类中的execute 方法为一个执行sql语句的方法，增加代码：
 

        slog($this->queryStr,$this->_linkID);

 -  类中的query方法也是一个执行sql语句的地方， 同样需要增加上面的代码
 
 -  然后浏览网站看看效果： 
 
 ![enter image description here][4]
 
通过console的日志，访问每一页我们都知道程序干了什么，是一件很爽的事情。

-  提示：另一种更简单的方法，因为OneThink每次执行完sql语句都会调用$this->debug， 所以我们可以把slog($this->queryStr,$this->_linkID); 直接写在 Db.class.php文件的debug方法中。 这样不管是mysqli还是mysql驱动都有效。

## 视频教程
 [http://edu.yuantuan.com/course/198](http://edu.yuantuan.com/course/198)

感谢猿团的张盛翔（诺墨）提供教程。

##About Me
* Author: @luofei614 新浪微博：http://weibo.com/luofei614
* 优伯立信创始人，ThinkPHP核心开发者之一，待过新浪云计算


  [1]: https://github.com/luofei614/SocketLog/raw/master/screenshots/discuz.png
  [2]: https://github.com/luofei614/SocketLog/raw/master/screenshots/socketlog.png
  [3]: https://github.com/luofei614/SocketLog/raw/master/screenshots/sql.png
  [4]: https://github.com/luofei614/SocketLog/raw/master/screenshots/onethink.png
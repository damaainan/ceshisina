## 成为高级 PHP 程序员的第一步——调试（xdebug 配置篇） 

5个月前 ⋅ 3676 ⋅ 46 ⋅ 16 

> 世界上最崩溃的事就是，在你快文章收尾的时候，浏览器因为意外关闭导致前功尽弃

社区的缓存功能好像对我这次没起作用，哎，再来一次吧，真是瞬间泄气的感觉！！！


上一篇 [成为高级 PHP 程序员的第一步——调试（xdebug 原理篇）][1]，介绍了一下 xdebug 工作原理，明白了工作原理，对我们接下来的配置应该就是信手拈来，就算第一次没成功，也会很快定位到问题。（好像任何事情好像都是这个道理 



#### xdebug 配置[#][2]

先简单说一下在我们的 Homestead 中，xdebug 的简单配置。（安装就不在此展开了）  
打开配置文件，（我的环境是7.1，下面都是以此版本配置，其他版本相同） `/etc/php/7.1/mods-available/xdebug.ini`

    # 必填项
    zend_extension=xdebug.so
    xdebug.remote_enable = 1
    xdebug.idekey = PHPSTORM （这个值是作为 XDEBUG_SESSION_START 的值，是通知 PHP 开启调试的标识）
    
    # 可选项
    xdebug.remote_connect_back = 1 （如原理篇介绍的，如果开启，将会忽视 remote_host 的配置，以请求来源的 IP 作为 xdebug 响应的 IP）
    xdebug.remote_log="/tmp/xdebug_php71.log" （记录日志）
    #xdebug.remote_autostart = 1 （如果开启，则无论什么请求都会进行调试响应）
    
    # 默认的 remote_host 和 remote_port 如果不做更改可以省略

简单的配置完成后，我们需要给 PHP 开启一下这个模块。

> TIPS : 我们知道，PHP 有两种运行模式 `FPM` 和 `CLI`，想要开启模块有一个实用的命令 `sudo phpenmod -s fpm [ -v 7.1 ] xdebug`。这样就会开启 `PHP-FPM` 的 xdebug 模块，而不会影响 `CLI` 。这个命令还有一些其他参数，比如 `-v` 可以指定 PHP 的版本

开启模块后，重启一下服务：

    sudo service php-fpm restart

至此，Homestead 的所有配置就全部完成啦，是不是很简单！

#### PHPSTORM 配置[#][3]

其实针对 xdebug 有两种调试模式，针对单个 PHP 文件和针对整个PHP项目，这里我们分开来说，也容易理解我们做的配置具体针对那部分生效

#### 针对单个 PHP 文件调试配置[#][4]

想要调试 PHP 我们首先需要 开启 xdebug 对 PHP 程序，这里我们使用 Homestead 中的 PHP。

如图所示，我们增加一个 CLI Interpreter

![file][5]

这里我使用的方式是通过 SSH，注意红框处，表示 PHP-CLI 模式也已经开启了 xdebug 模块，如果没有开启我们需要开启一下，还是上面说到的那个命令 sudo phpenmod -s cli xdebug

![file][6]

配置完成后，我们配置使用一下这个 CLI Interpreter，注意红框，必须配置目录映射，至于原因下文说

![file][7]

到这里，针对单文件调试的配置就算做完啦！我们调试一个文件试试！

![file][8]

#### 针对整个项目的配置[#][9]

下面说的配置和上面配置可以说没有任何关系啦，大家可以忘掉刚才配置的事儿，继续下一个。和调试单个文件不同的是，针对整个项目调试，需要我们先配置一个 server。同样的，一定要配置目录映射

![file][10]

保存后，我们的配置可以说就完啦！（不过为了调试的方便，我们增加一个其他的配置，这个稍后讲。）

> 打断一下，不知道大家发现没有，不管针对`单个文件`还是针对`整个项目`的配置，都有一个`For current project` 的字样。没错，这些配置只会对当前项目生效，如果再打开一个新项目，这些配置是需要重新配置的！（不过已经配置的项目是会保留的）

我们请求项目测试之前，先打上几个断点，要不是看不到效果的：

![file][11]

如昨天原理说的，访问之前我们还需要开启一下 9000 端口 的监听，打开菜单栏

![file][12]

开启后，现在我们打开浏览器，访问项目当前打过断点的路由。记得加我们的调试标识

![file][13]

这时我们的 phpstorm 应该会蹦出一个调试栏，和许多调试信息

![file][14]

> 如果没有弹出调试栏，但是浏览器一直在转圈，没有输出结果，百分之百是因为调试栏当中有其他调试的 > tab> ，这就需要我们手动打开调试栏全部关掉!

到这里，其实我们的配置已经完成了，已经可以进行远端调试了。但是刚才说的未完全的配置还有什么？  
还是我们的 菜单栏 > Run > Edit Configurations

![file][15]

这里我们增加一个 PHP Web Application 类型的项目，配置如图。这个配置有什么用呢？配置完这个后，当我们在 IDE 点击 菜单栏 > Run > Debug 的时候，会弹出下面的框

![file][16]

  
我们选择刚才配置的xdebug-myblog，这时就会在浏览器开启一个带 debug 标示的项目链接，不用我们手动输入链接了

#### TIPS[#][17]

1. 每次需要调试都需要手动写那个参数太麻烦了怎么办？  
已经有人帮你想好啦，chrome 可以安装一个插件xdebug helper

![file][18]

  
开启这个插件后，我们的链接不用加那个参数也可以调试了！

![file][19]

他的工作原理是，设置一个cookie，如下图所示。（XDEBUG_SESSION_START 支持 get、post和cookie 三种方式告诉 PHP 开启调试）

![file][20]

2 . 为什么一定要设置目录映射？  
我的理解是，无论那种调试模式，实际上运行的都是 Homestead 中的项目和文件，所以开启映射就相当于给 Homestead 中的项目打断点，和 PHP 执行的文件就统一了！这样才能成功。这里有一个回答，我觉得和我理解的是一个意思 [Remote debugging path mapping][21]

> 其实 xdebug 不仅能调试，也可以作为 Profile 和 Trace 工具，当然，@MrJing 说的追踪项目源码也是一个利器！最后祝大家能早日成为 "高级程序员" ，笔芯

[1]: https://laravel-china.org/articles/4090
[2]: #xdebug-配置
[3]: #PHPSTORM-配置
[4]: #针对单个-PHP-文件调试配置
[5]: ./img/qJAsVyKoO0.png
[6]: ./img/80bjj1wKfu.png
[7]: ./img/DW2HF3D1f8.png
[8]: ./img/A1DdQ1GApY.png
[9]: #针对整个项目的配置
[10]: ./img/ZwZvXIQP5m.png
[11]: ./img/pbRDLM3r0B.png
[12]: ./img/uXyBA4tJC4.png
[13]: ./img/Fl9wOaO0ho.png
[14]: ./img/Qs1jDpak9I.png
[15]: ./img/lvlMD4t06M.png
[16]: ./img/apU1LatH3r.png
[17]: #TIPS
[18]: ./img/FyIilLAmSZ.png
[19]: ./img/cEWOygrhW9.png
[20]: ./img/Y7DqG71qme.png
[21]: http://stackoverflow.com/a/37869910/5081938
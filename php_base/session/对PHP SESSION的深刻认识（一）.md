#  [对 PHP SESSION 的深刻认识（一）][0]

 标签： [session][1][php][2][服务器][3]

 2016-12-04 15:38  302人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][9]
1. [为什么要使用 SESSION][10]
1. [引入][11]
1. [理解 PHP SESSION 机制][12]
1. [SESSION 是怎么存储数据的][13]
1. [session_start函数的作用是什么][14]
1. [SESSION 的清理][15]
1. [后话][16]

## 前言：

在不久之前，本人去参加了某公司的实习面试，其中 HR 问我关于 SESSION 实现的原理，当时我就懵逼了，因为在之前的开发中，我只知道 session 与 cookie 的区别在于：session 是保存在服务器端，cookie 保存在客户端。那 session 在服务端是怎么样保存的？session_id 又是什么？等等。我当时答不上来。回来后决定把这些搞懂。

## 为什么要使用 SESSION？

是因为目前网络中所使用的http协议造成的，http协议是无状态协议，通俗点说就是当你发送一次请求道服务器端，然后再次发送请求到服务器端，服务器是不知道你的这一次请求和上一次请求是来源于同一个人发送的。而 session 就能很好解决这个问题。

在我们的访问期间，各个页面间共享的数据放在session中，就比如说我们的登陆信息，如果没有 session 的话，当你在这个页面登陆之后，在点击下一个页面的时候你需要再次登陆。

## 引入：

现在我们来看看平时我们是怎么使用 session 的，大家看下面的例子：

    <?php
    #index.php 文件
    
    session_start();     //启动会话
    
    $user = isset($_GET['user'])?$_GET['user']:"default"
    
    if(!isset($_SESSION['user'])){
      $_SESSION['user'] = $user;    //设置会话
    }
    
    var_dump($_SESSION);
    
    unset($_SESSION['user']);     //清除会话

现在我们在浏览器 A 打开 [http://localhost/index.php?user=lsgogroup][17]；

    返回：array(1){["user"]=>string(9)"lsgogroup"}

在浏览器 B 打开 [http://localhost/index.php][18]

    返回：array(1){["user"]=>string(7)"default"}

问题：

1. session_start() 的作用是什么？
1. 为什么在浏览器 B 中返回的不是： array(1){[“user”]=>string(9)”lsgogroup”} ？
1. $_SESSION 数组是怎么保存这些数据的？

## 理解 PHP SESSION 机制：

session 机制是一种服务器端的机制，服务器使用一种类似于散列表的结构来保存信息。

当程序需要为某个客户端的请求创建一个 session 的时候，服务器首先检查这个客户端的请求（Http Request）里是否已包含了一个 session 标识-称为 sessionid，如果已包含一个 sessionid 则说明以前已经为此客户端创建过 session，服务器就按照 sessionid 把这个 session 检索出来使用，如果客户端请求不包含 sessionid，则为此客户端创建一个 session 并且生成一个与此 session 相关联的 sessionid，sessionid的值应该是一个既不会重复，又不容易被找到规律以仿造的字符串，这个 sessionid 将被在本次响应中返回给客户端保存。而这个 sessionid 就是作为客户端的唯一标识而存在的（即使在同一台电脑上，浏览器 A 和浏览器 B 对于服务器来说都是不同的客户端）。

上面一段话你可能暂时不会理解，不过不要紧，我会在下面作出解释：

现在我们来看看浏览器 A 和 浏览器 B 的 cookie：

浏览器 A （这里对应是谷歌浏览器）:

![这里写图片描述][19]

浏览器 B （这里对应是火狐浏览器） :

![这里写图片描述][20]

对比可以看到，两个浏览器对于 localhost 都有一条名为 PHPSESSID 的 cookie 记录，而这个 PHPSESSID 就是上面所说的 sessionid，它告诉服务器请求是来自浏览器 A 还是浏览器 B 。

现在我们可以回答上面的问题 2 了：

由于浏览器 A 的 PHPSESSID 和浏览器 B 的 PHPSESSID 是不一样的，因此服务器根据 sessionid 检索 session 的数据也是不一样的，也就是说浏览器 A 请求的 $ _SESSION 数组和 浏览器 B 请求的 $ _SESSION 数组也是不一样的。

（当然，PHPSESSID 这个 id 名不是固定的，我们可以在 [PHP][21].ini 文件中的 session.name 项进行修改。）

上面的例子是使用 COOKIE 保存 PHPSESSID，但是，由于 cookie 可以被人为的禁止，必须有其他机制以便在 cookie 被禁止时仍然能够把 sessionid 传递回服务器。有两种技术可以解决这个问题：

1. URL重写，就是把 sessionid 直接附加在URL路径的后面：[http://localhost/index.php?user=lsgogroup&PHPSESSID=ByOK3vjFD75aPnrF7C2HmdnV6QZcEbzWoWiBYEnLerjQ99zWpBng][22]
1. 隐藏表单传递。

由于这不是重点，这里不展开讲。

## SESSION 是怎么存储数据的？

答：session 是以文件的形式保存的。

[php][21].ini 中的配置项 session.save_handler = files；   
默认为 file，定义 session 在服务端的保存方式，file 意为把 session 保存到一个临时文件里。

php.ini 中的配置项 session.save_path= “”;   
这个里面填写的路径，将会使session文件保存在该路径下。

session 文件的命名格式是：”sess_[PHPSESSID的值]”。每一个文件，里面保存了一个会话的数据。

我们查看服务器端 session.save_path 目录会发现很多类似 sess_vv9lpgf0nmkurgvkba1vbvj915 这样的文件，这个其实就是 sessionid（也就是 PHPSESSID） “vv9lpgf0nmkurgvkba1vbvj915″ 对应的数据。真相就在这里，客户端将 sessionid 传递到服务器，服务器根据 sessionid 找到对应的文件，读取的时候对文件内容进行反序列化就得到 session 的值（$_SESSION数组中的数据），保存的时候先序列化再写入。

由于我做实验的时候使用的是 ubuntu 系统，因此我的 session.save_path 默认实在 /var/lib/php/sessions 下，我们来看看前面浏览器 A 生成的 session 文件是怎样的（浏览器 A 的 PHPSESSID = ‘nqqleletmsb0nuf7d4ulvotk45’）：

    cd /var/lib/php/sessions
    #由于session数据是很重要的数据，因此必须只能 root 用户才能打开
    sudo vim sess_nqqleletmsb0nuf7d4ulvotk45
    #看看文件格式是不是 "sess_[PHPSESSID的值]"

文件内容：

    user|s:9:"lsgogroup";

从文件内容可以看到，数据是经过序列化的，数据的读取规则是这样的：

1. 每一个session的值是以分号”;”分开的。比如”user|s:9:”lsgogroup”;“就是一个完整的session值结束，如果再添加 $_SESSION[‘name’]=”LSGOZJ”，则变成这样 ”user|s:9:”lsgogroup”;name|s:7:”LSGOZJ”;“
1. 里面的读取规则：符号“|”前面表示 session 名称。符号后面是该 session 的具体信息。包括：数据类型，字符长度，内容。比如说 ”user|s:9:”lsgogroup”;“，$_SESSION[‘user’] 的值是 “lsgogroup”，是一个长度为 9 的字符串。
1. 等等。。。

到了这里，我们就解决了上面的问题 3 了。

其实还有很多种存储session的方式，如果我们想自定义别的方式保存（比如用[数据库][23]），则需要把该项设置为 user；我们还可以使用 memcache、[Redis][24] 等优秀的缓存系统（前提是你的服务器安装了此类软件）。

![这里写图片描述][25]

## session_start()函数的作用是什么？

了解的原理之后，所谓的 session 其实就是客户端一个 sessionid 对应服务器端一个 session file，新建session 之前执行 session_start() 是告诉服务器要种一个 cookie 以及准备好 session 文件，要不然你的session 内容怎么存；读取 session 之前执行 session_start() 是告诉服务器，赶紧根据 sessionid 把对应的 session 文件反序列化。

说白了，当我们使用 php 的内置函数 session_start( ) 的时候，就是到服务器的指定的磁盘目录把 session 数据载入，实际上就是拿类似 sess_74dd7807n2mfml49a1i12hkc45 的文件。

只有一个 session 函数可以在 session_start() 之前执行，session_name()：读取或指定 session 名称（比如默认的就是”PHPSESSID”），这个当然要在session_start之前执行。

根据 http 的请求机制，当浏览器请求的时候，头部信息会把浏览器中的 cookie 一起发给服务器。PHPSESSID 这个 cookie 也是在其中发给了服务器，php 引擎通过读取 PHPSESSID 的值来确定要载入哪个 session 文件。

比如值为 74dd7807n2mfml49a1i12hkc45，载入的就是”sess_74dd7807n2mfml49a1i12hkc45”。

注：当你调用 php 的函数 session_start(),才表明你需要使用 session 文件了。不然平白无故就去载入文件，浪费性能。

## SESSION 的清理：

在平时我们谈论 SESSION 的机制的时候，常常听到这样一种误解“只要关闭浏览器，session就消失了”（本人也是一度认为这样），其实可以想象一下会员卡的例子，除非顾客主动对店家提出销卡，否则店家绝对不会轻易删除顾客的资料。

对 session 来说也是一样的，除非程序通知服务器删除一个 session，否则服务器会一直保留，程序一般都是在用户做 logoff （注销操作，类似于 session_destroy()操作）的时候发个指令去删除 session。然而浏览器从来不会主动在关闭之前通知服务器它将要关闭，因此服务器根本不会有机会知道浏览器已经关闭，之所以会有这种错觉，是大部分 session 机制都使用会话 cookie 来保存 sessionid ，而关闭浏览器后这个sessionid就消失了，再次连接服务器时也就无法找到原来的session，但是服务器上对应的 session file 依然存在。

为什么关闭浏览器后 sessionid 就会消失呢？这跟 cookie 在客户端的存储有关，如果在设置 cookie 的时候没有指定生命周期，那么 cookie 的数据是存储在内存中的，当浏览器被关闭，内存被回收了，那么cookie 也就没有了（这就是为什么cookie在没有指定生命周期的时候，其生命周期与浏览器生命周期一样）。

![这里写图片描述][26]

如果服务器设置的 cookie 被保存到硬盘上（设置了生命周期），或者使用某种手段改写浏览器发出的HTTP请求头，把原来的 sessionid 发送给服务器，则再次打开浏览器仍然能够找到原来的session。

恰恰是由于关闭浏览器不会导致 session 被删除，迫使服务器为 seesion 设置了一个失效时间，当距离客户端下一次使用 session 的时间超过这个失效时间时，服务器就可以认为客户端已经停止了活动，才会把session 删除以节省存储空间。

我们来看看服务器是怎样删除 session 数据的：

session.gc_probability = 1

session.gc_divisor = 100

session.gc_maxlifetime = 1440

这三个配置项组合构建服务端 session 的垃圾回收机制。

session.gc_probability 与 session.gc_divisor 构成执行 session 清理的概率，理论上的解释为服务端定期有一定的概率调用 gc（garbage collection 垃圾回收） 进程来对 session 进行清理，清理的概率为：gc_probability/gc_divisor 比如：1/100 表示每一个新会话初始化时，有 1% 的概率会启动垃圾回收程序，清理的标准为 session.gc_maxlifetime 定义的时间（清理过期的数据）。

我所用的系统是ubuntu，php.ini 中指定的 session.gc_probability = 0，也就是概率为零，原因是该系统是使用 cron 脚本来执行垃圾清理的。

## 后话：

session 还有很多需要整理和学习的地方，如：

1. session多服务器共享的问题，假如有多台php服务器进行负载均衡的时候，用户登录时访问的是第一台服务器，没准下一个页面访问的是第二台服务器，但是 session 数据是存储在第一台服务器上的，因此在访问下一个页面的时候由于没有 session 数据（第二台服务器上）导致用户必须重新登陆。
1. 从上面的分析我们也知道，php 中 session 默认通过文件的方式实现，但是如果访问量大，可能产生的 SESSION 文件会比较多，从众多的文件中选择其中一个文件不是一件轻松的事情，而且每次都以打开文件、读取文件的方式，也会产生大量的 I/O 操作，严重影响服务器的性能。

[0]: http://www.csdn.net/baidu_30000217/article/details/53453202
[1]: http://www.csdn.net/tag/session
[2]: http://www.csdn.net/tag/php
[3]: http://www.csdn.net/tag/%e6%9c%8d%e5%8a%a1%e5%99%a8
[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: #t3
[13]: #t4
[14]: #t5
[15]: #t6
[16]: #t7
[17]: http://localhost/index.php?user=lsgogroup
[18]: http://localhost/index.php
[19]: ../img/20161204130527836.png
[20]: ../img/20161204130550227.png
[21]: http://lib.csdn.net/base/php
[22]: http://localhost/index.php?user=lsgogroup&PHPSESSID=ByOK3vjFD75aPnrF7C2HmdnV6QZcEbzWoWiBYEnLerjQ99zWpBng
[23]: http://lib.csdn.net/base/mysql
[24]: http://lib.csdn.net/base/redis
[25]: ../img/20161204144021823.png
[26]: ../img/20161204150257432.png
## 你确定你真的懂Nginx与PHP的交互

Nginx是俄国人最早开发的Webserver，现在已经风靡全球，相信大家并不陌生。PHP也通过二十多年的发展来到了7系列版本，更加关注性能。这对搭档在最近这些年，叱咤风云，基本上LNMP成了当下的标配。可是，你用了这么多年的Nginx+PHP的搭配，你真正知道他们之间是怎么交互怎么通信的么？作为一道常常用来面试的考题，从过往经验看，情况并不乐观。更多的同学是知道PHP-FPM、知道FastCGI，但不晓得Nginx、PHP这对老搭档具体的交互细节。那么，今天我们就来一起学习一下，做一回认真的PHP工程师。

**前菜**为了讲解的有理有据，我们先来准备一个纯净精简的Nginx+PHP环境，这里我们使用Docker拉取Centos最新版本环境，来快速通过编译安装方式搭建一个Nginx+PHP环境。（图1，通过docker启动一台CentOS机器并进入）

![图1，通过docker启动一台CentOS机器并进入][0]

有了Linux环境，我们来源码编译安装Nginx、PHP，这个过程网络里有很多的教程，我们就不细说了。当然你也可以安装lnmp一键安装包来快速搭建。通过安装nginx、php，我们的Linux环境里就有了今天的这两位主角了。我们稍加配置，让Nginx可以接收请求并转发给PHP-FPM，我们目标是输出一个phpinfo()的信息。（图2，phpinfo()的输出内容）  
![图2][1]

我们通过对Nginx新增Server配置实现了nginx与PHP的一次通信，配置文件非常简单，如下图：（图3，一份nginx server配置）  
![图3，一份nginx][2]

有了上面的一个sample示例，我们开始深入Nginx与FastCGI协议。

**主食**  
从上图的Nginx配置中可以注意到 `fastcgi_*` 开头的一些配置，以及引入的 `fastcgi.conf` 文件。其实在`fastcgi.conf`中，也是一堆`fastcgi_*`的配置项，只是这些配置项相对不常变，通常单独文件保管可以在多处引用。（图4，fastcgi.conf文件中的内容）  
![图4，fastcgi.conf文件中的内容][3]

可以看到在`fastcgi.conf`中，有很多的`fastcgi_param`配置，结合`nginx server`配置中的`fastcgi_pass`、`fastcgi_index`，通常我们的同学已经能够想到Nginx与PHP之间打交道就是用的FastCGI，但再深问FastCGI是什么？它起到衔接Nginx、PHP的什么作用？等等深入的问题的时候，很多同学就卡壳了。那么，我们就来一探究竟。

`CGI`是通用网关协议，`FastCGI`则是一种常住进程的CGI模式程序。我们所熟知的PHP-FPM的全称是`PHP FastCGI Process Manager`，即PHP-FPM会通过用户配置来管理一批FastCGI进程，例如在PHP-FPM管理下的某个FastCGI进程挂了，PHP-FPM会根据用户配置来看是否要重启补全，PHP-FPM更像是管理器，而真正衔接Nginx与PHP的则是FastCGI进程。（图5，FastCGI在请求流中的位置）  
![图5，FastCGI在请求流中的位置][4]

如上图所示，`FastCGI`的下游，是`CGI-APP`，在我们的LNMP架构里，这个CGI-APP就是PHP程序。而FastCGI的上游是Nginx，他们之间有一个通信载体，即图中的socket。在我们上文图3的配置文件中，fastcgi_pass所配置的内容，便是告诉Nginx你接收到用户请求以后，你该往哪里转发，在我们图3中是转发到本机的一个socket文件，这里fastcgi_pass也常配置为一个http接口地址（这个可以在php-fpm.conf中配置）。而上图5中的Pre-fork，则对应着我们PHP-FPM的启动，也就是在我们启动PHP-FPM时便会根据用户配置启动诸多FastCGI触发器（FastCGI Wrapper）。

对FastCGI在Nginx+PHP的模式中的定位有了一定了解后，我们再来了解下Nginx中为何能写很多`fastcgi_*`的配置项。这是因为Nginx的一个默认内置module实现了FastCGI的Client。关于Module **ngx_http_fastcgi_module**的详细文档可以查看这里: [http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html][5] 。我们关心一下我们图4中的这些fastcgi_param都是些什么吧，详细描述见下图。（图6，nginx模块中fastcgi_param的介绍）  
![图6，nginx模块中fastcgi_param的介绍][6]

从图6中可以看到，fastcgi_param所声明的内容，将会被传递给“FastCGI server”，那这里指的就是`fastcgi_pass`所指向的server，也就是我们Nginx+PHP模式下的PHP-FPM所管理的FastCGI进程，或者说是那个socket文件载体。这时，有的同学会问：“为什么PHP-FPM管理的那些FastCGI进程要关心这些参数呢？”，好问题，我们一起想想我们做PHP应用开发时候有没有用到 `$_SERVER` 这个全局变量，它里面包含了很多服务器的信息，比如包含了用户的IP地址。同学们不想想我们的PHP身处socket文件之后，为什么能得到远端用户的IP呢？聪明的同学应该注意到图4中的一个`fastcgi_param`配置 `REMOTE_ADDR` ，这不正是我们在PHP中用 `$_SERVER[‘REMOTE_ADDR’]` 取到的用户IP么。的确，Nginx这个模块里`fastcgi_param`参数，就是考虑后端程序有时需要获取Webserver外部的变量以及服务器情况，那么`ngx_http_fastcgi_module`就帮我们做了这件事。真的是太感谢它啦！

那么我们已经说清了FastCGI是个什么东东，并且它在Nginx+PHP中的定位。我们回到前面提出的问题，“它起到衔接Nginx、PHP的什么作用？”。

对PHP有一定了解的同学，应该会知道**PHP提供`SAPI`面向Webserver来提供扩展编程**。但是这样的方式意味着你要是自主研发一套Webserver，你就需要学习SAPI，并且在你的Webserver程序中实现它。这意味着你的Webserver与PHP产生了耦合。在互联网的大趋势下，一般大家都不喜欢看到耦合。譬如Nginx在最初研发时候也不是为了和PHP组成黄金搭档而研发的，相信早些年的Nginx后端程序可能是其他语言开发。**那么解决耦合的办法，比较好的方式是有一套通用的规范，上下游都兼容它**。那么CGI协议便成了Nginx、PHP都愿意接受的一种方式，而FastCGI常住进程的模式又让上下游程序有了高并发的可能。那么，FastCGI的作用是Nginx、PHP的接口载体，就像插座与插销，让流行的WebServer与“世界上最好的语言”有了合作的可能。

**有了这些基础背景知识与他们的缘由，我们就可以举一反三的做更多有意思的事情**。譬如我在前年曾实现了Java程序中按照FastCGI Client的方式（替代Nginx）与PHP-FPM通信，实现Java项目+PHP的一种组合搭配，解决的问题是Java程序一般来说在代码调整后需要编译过程，而PHP可以随时调整代码随时生效，那么让Java作为项目外壳，一些易变的代码由PHP实现，在需要的时候Java程序通过FastCGI与PHP打交道就好。这套想法也是基于对Nginx+PHP交互模式的理解之上想到的。

网络中也有一些借助FastCGI的尝试与实践，譬如《[Writing Hello World in FCGI with C++][7]》这篇文章，用C++实现一个FastCGI的程序，外部依然是某款Webserver来处理HTTP请求，但具体功能则有C++来实现，他们的中间交互同样适用的FastCGI。同学们有兴趣了也可以做些Geek尝试。（图7，C++实现一个FastCGI程序）  
![图7，C++实现一个FastCGI程序][8]

**甜品**  
通过本文的讲解，我们希望让大家看到，**Nginx+PHP的工程模式下，两位主角分工明确，Nginx负责承载HTTP请求的响应与返回，以及超时控制记录日志等HTTP相关的功能，而PHP则负责处理具体请求要做的业务逻辑，它们俩的这种合作模式也是常见的分层架构设计中的一种，在它们各有专注面的同时，FastCGI又很好的将两块衔接，保障上下游通信交互**，这种通过某种协议或规范来衔接好上下游的模式，在我们日常的PHP应用开发中也有这样的思想落地，譬如我们所开发的**高性能API**，具体的Client到底是PC、APP还是某个其他程序，我们不关心，而这些PC、APP、第三方程序也不关心我们的PHP代码实现，他们按照API的规范来请求做处理即可。同学们是不是发现**技术思想是可以在各个环节融会贯通的**，是不是很兴奋？很刺激？哈，同学们开心就好，祝大家在工作学习过程中，能挖掘到更多的好知识，提升自己的同时造福身边小伙伴！

[0]: ./img/596e558e0001c89b04520054.png
[1]: ./img/596e55bd00019daf18940871.png
[2]: ./img/596e56010001222706100396.png
[3]: ./img/596e56510001032007890706.png
[4]: ./img/596e56910001ae5d05590315.png
[5]: http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html
[6]: ./img/596e56eb0001f80908140678.png
[7]: http://chriswu.me/blog/writing-hello-world-in-fcgi-with-c-plus-plus/
[8]: ./img/596e57670001f53a13250474.png
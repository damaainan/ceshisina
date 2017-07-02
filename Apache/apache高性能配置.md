# [apache高性能配置][0]



### 1.对于访问量稍大的站点，Apache的这些默认配置是无法满足需求的，我们仍需调整Apache的一些参数，使Apache能够在大访问量环境下发挥出更好的性能。以下我们对Apache配置文件==httpd.conf==中对性能影响较大的参数进行一些说明。

(1) Timeout 该参数指定Apache在接收请求或发送所请求内容之前的最长等待时间（秒），若超过该时间Apache则放弃处理该请求，并释放连接。该参数默认值为120，推荐设置为60，对于访问量较大的网站可以设置为30。

(2) KeepAlive 该参数控制Apache是否允许在一个连接中有多个请求，默认打开。但对于大多数论坛类型站点来说，通常设置为off以关闭该支持。

(3) MPM – prefork.c 在**默认情况下Apache使用Prefork（进程）工作模式**，可以说这部分的参数设置是对Apache性能影响的核心和关键。用户可以在配置文档中找到以下配置段：

    <IfModule prefork.c>   
    StartServers 5   
    MinSpareServers 5   
    MaxSpareServers 10   
    MaxClients 15   
    MaxRequestsPerChild 0   
    </IfModule>

这就是控制Apache进程工作的配置段，为了更好的理解上述配置中的各项参数，下面让我们先了解一下Apache是如何控制进程工作的。我们知道，在 Unix系统中，很多服务(Service)的守护进程(Daemon)在启动时会创建一个进程以准备应答可能的连接请求，服务即进入了端口监听状态，当一个来自客户端(Client)的请求被发送至服务所监听的端口时，该服务进程即会处理该请求，在处理过程中，该进程处于独占状态，也就是说如果此时有其他请求到达，这些请求只能“排队”等待当前请求处理完成且服务进程释放。这样就会导致越来越多的请求处于队列等待状态，实际表现就是该服务处理能力非常低下。Apache使用Prefork模式很好的解决了这一问题。下面我们来看看Apache实际上是如何高效率工作的。

当Apache启动时，Apache会启动StartSpareServers个空闲进程同时准备接收处理请求，当多个请求到来时，StarSpareServers进行会越来越少，当空闲进程减少到MinSpareServers个时，Apache为了能够继续有充裕的进程处理请求，它会再启动StartsServers个进程备用，这样就大大减少了请求队列等待的可能，使得服务效率提高，这也是为什么叫做Pre-fork的原因；让我们继续跟踪Apache的工作，我们假设Apache已经启动了200个进程来处理请求，理论上来说，此时Apache一共有205个进程，而过了一段时间，假设有100个请求都得到了Apache的响应和处理，那么此时这100个进程就被释放成为空闲进程，那么此时Apache有105个空闲进程。而对于服务而言，启动太多的空闲进程时没有任何意义的，反而会降低服务器的整体性能，那么Apache真的会有105个空闲进程么？当然不会！实际上 Apache随时在检查自己，当发现有超过MaxSpareServers个空闲进程时，则会自动停止关闭一些进程，以保证空闲进程不过过多。说到这里，用户应该对Apache的工作方式有了一定的了解，如果想获得更多更详细的说明请参阅Apache手册文档。

我们还有两个参数没有介绍：MaxClients和MaxRequestPerchild；MaxClients指定Apache在同一时间内最多允许有多少客户端能够与其连接，如果超过MaxClients个连接，客户端将会得到一个“服务器繁忙”的错误页面。我们看到默认情况下MaxClients设置为15，这对一些中型站点和大型站点显然是远远不够的！也许您需要同时允许512个客户端连接才能满足应用需求，好吧，那么就让我们把 MaxClients修改为512，保存httpd.conf并退出，重启Apache，很遗憾，在重启过程当中您看到了一些错误提示，Apache重启失败。错误提示中告诉您MaxClients最大只能设定为256，相信您一定很失望。不过不要沮丧，Apache作为世界一流的Web Server一定不会如此单薄的！在默认情况下，MaxClients的确只能设定为不超过256的整数，但是，如果您有需要完全可以随意定制，此时就需要使用ServerLimit参数来配合使用，简单的说ServerLimit就像是水桶，而MaxClients就像是水，您可以通过更换更大的水桶（将ServerLimit设定为一个较大值）来容纳更多的水(MaxClients)，但要注意，MaxClients的设定数值是不能大于 ServerLimit的设定数值的！

下面让我们了解一下MaxRequestPerChild参数，该参数指定一个连接进程中可以有多少个线程同时工作。也许这样解释过于专业，那么您只要想想“网络蚂蚁”、“网际快车FlashGet”中的“多点同时下载”即可，该参数实际上就是限制最多可以用几个“点”，当这些“点”用完以后就会结束进程，并重新开启一个进程。默认设置为0，即为：不限制。但需要注意，如果将该值设置的过小会引起访问问题，如果没有特殊需要或者访问量压力并非很大可以保持默认值，如果访问量很大则推荐设置为2048，一般现在高负载服务器设置10000也可。

好了，解释了这么多，让我们看看经过修改后Perfork.c配置段的推荐配置：

    <IfModule prefork.c>   
    StartServers 5   
    MinSpareServers 5   
    MaxSpareServers 10   
    ServerLimit 1024   
    MaxClients 768   
    MaxRequestsPerChild 0   
    </IfModule>

完成了上述对Apache的调整，Apache已经获得了较大的性能改善。记住，在修改任何参数后都需要重启Apache才能生效的。有关Apache的优化远远不止这些，有兴趣的用户可以阅读Apache手册文档或者寻找一些文献资料学习。

###  2. PHP优化对于PHP的优化主要是对==php.ini==中的相关主要参数进行合理调整和设置，以下我们就来看看php.ini中的一些对性能影响较大的参数应该如何设置。


    # vi /etc/php.ini

(1) PHP函数禁用找到：

    disable_functions =

该选项可以设置哪些PHP函数是禁止使用的，PHP中有一些函数的风险性还是相当大的，可以直接执行一些系统级脚本命令，如果允许这些函数执行，当PHP程序出现漏洞时，损失是非常严重的！以下我们给出推荐的禁用函数设置：

    disable_functions = phpinfo,passthru,exec,system,popen,chroot,escapeshellcmd,escapeshellarg,shell_exec,proc_open,proc_get_status

需注意：如果您的服务器中含有一些系统状态检测的PHP程序，则不要禁用shell_exec,proc_open,proc_get_status等函数。

(2) PHP脚本执行时间找到：

    max_execution_time = 30

该选项设定PHP程序的最大执行时间，如果一个PHP脚本被请求，且该PHP脚本在max_execution_time时间内没能执行完毕，则PHP不再继续执行，直接给客户端返回超时错误。没有特殊需要该选项可保持默认设置30秒，如果您的PHP脚本确实需要长执行时间则可以适当增大该时间设置。

(3) PHP脚本处理内存占用找到：

    memory_limit = 8M

该选项指定PHP脚本处理所能占用的最大内存，默认为8MB，如果您的服务器内存为1GB以上，则该选项可以设置为12MB以获得更快的PHP脚本处理效率。

(4) PHP全局函数声明找到：

    register_globals = Off

网络上很多关于PHP设置的文章都推荐将该选项设置为On，其实这是一种及其危险的设置方法，很可能引起严重的安全性问题。如果没有特殊的需要，强烈推荐保留默认设置！

(5) PHP上传文件大小限制找到：

    upload_max_filesize = 2M

该选项设定PHP所能允许最大上传文件大小，默认为2MB。根据实际应用需求，可以适当增大该设置。

(6) Session存储介质找到：

    session.save_path

如果你的PHP程序使用Session对话，则可以将Session存储位置设置为/dev/shm，/dev/shm是Linux系统独有的TMPFS 文件系统，是以内存为主要存储方式的文件系统，比RAMDISK更优秀，因为可以使用DISKSWAP作为补充，而且是系统自带的功能模块，不需要另行配置。想想看，从磁盘IO操作到内存操作，速度会快多少？只是需要注意，存储在/dev/shm的数据，在服务器重启后会全部丢失。不过这对于 Session来说是无足轻重的。

[0]: http://www.cnblogs.com/jishume/articles/2264578.html
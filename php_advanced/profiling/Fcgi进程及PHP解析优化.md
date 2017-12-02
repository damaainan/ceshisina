# [PHP] – 性能优化 – Fcgi进程及PHP解析优化

   **首先在此感谢下我的老师****-****老男孩专家拥有****16****年一线实战经验，为我们运维班28期所有成员的耐心讲解,未经本人同意禁止转载**

**博客地址：[oldboy][0]**

- - -

### 1、PHP引擎缓存加速

常见四种软件：

1.eAccelerator

2.Zendcache

3.xcache

4.apc

5.zendopcache php5.5自带

### 2、使用 tmpfs 作为缓存加速缓存的文件目录

    [root@web02 ~]# mount -t tmpfs tmpfs /dev/shm -o size=256m  
    [root@web02 ~]# mount -t tmpfs /dev/shm/ /tmp/eaccelerator/  
    [root@web02 ~]# df -h  
    Filesystem      Size  Used Avail Use% Mounted on  
    /dev/sda3       6.6G  3.9G  2.5G  62% /  
    /dev/sda1       190M   36M  145M  20% /boot  
    tmpfs           256M     0  256M   0% /dev/shm  
    /dev/shm        238M     0  238M   0% /tmp/eaccelerator

**提示：**

1. 上传图片缩略临时处理的目录/tmp

2. 其他加速器临时目录/tmp/eacclerator

- - -

 tmpfs是一种基于内存的文件系统，它和虚拟磁盘ramdisk比较类似像，但不完全相同，和ramdisk一样，tmpfs可以使用RAM 但它也可以使用swap分区来存储。而且传统的ramdisk是个块设备，要用mkfs来格式化它，才能真正地使用它；而tmpfs是一个文件系统，并不是块设备，只是安装它，就可以使用了。tmpfs是最好的基于RAM的文件系统

[更多解释][1]

- - -

### 3、php.ini参数调优

无论是apache还是nginx，`php.ini`都是适合的。而`php-fpm.conf`适合`nginx+fastcgi`配置。首选产品环境的`php.ini`（**php.ini-production**）

    [root@web02 ~]# ls /home/oldboy/tools/php-5.5.32|grep php.ini  
    php.ini-development  
    php.ini-production

 两者的区别：生产场景php.ini的日志都是关闭或者输出到文件中的。所以，我们再生产场景把非程序(php引擎)上输出都关闭或隐藏

- - -

**3.1 打开php的安全模式**

php的安全模式是个非常重要的php内嵌的安全机制，能够控制一些php中的函数执行，比如`system()`,同时把很多文件操作的函数进行了权限控制。

     safe_mode = On  
    ；是否启用安全模式。  
    ；打开时，PHP将检查当前脚本的拥有者是否被操作的文件的拥有者相同

- - -

**3.2 用户组安全**

当s`afe_mode`打开时，`safe_mode_gid`被关闭，那么php脚本能够对文件进行访问，并且相同组的用户也能够对文件进行访问 建议设置为：

     safe_mode_gid = Off

如果不进行设置，可能我们无法对我们服务器网站目录下的文件进行操作了，比如我们需要对文件进行操作的时候。**php5.3.27**默认为  **safe_mode_gid = Off**。

提示：5.5 此参数已经没有了

- - -

**3.3 关闭危险函数**

如果打开了安全模式，那么函数禁止是不需要的，但是我们为了安全考虑还是设置。比如，我们觉得不希望执行包括system()等能执行命令的php函数，或者能够查看php信息的phpinfo()等函数，那么我们就禁止它们。

     disable_functions = system,passthru,exec,shell_exec,popen,phpinfo

如果你要禁止任何文件和目录的操作，那么可以关闭很多文件操作

     disable_functions = chdir,chroot,dir,getcwd,opendir,readdir,scandir,fopen,unlink,delete,copy,mkdir,rmdir,rename,file,file_get_contents,fpus,fwrite,chgrp,chmod,chown

以上只是列了部分不叫常用的文件处理函数，你也可以把上面执行命令函数和这个函数结合，就能够抵制大部分的phpshell了

- - -

企业面试题：

下列PHP函数中，会对系统产生安全隐患降低安全的函数有？

A.md5() B.phpinfo() C.shell_exec() D.exec()

答案：B、C、D

- - -

**3.4 关闭PHP版本信息在http头中的泄漏**

我们为了防止黑客获取服务器中php版本信息，可以关闭该信息在http头中

     expose_php = Off  
    ；是否暴露PHP被安装在服务器上的事实（在http中加上其前面）  
    ；它不会有安全上的直接危险，但它使得客户端知道服务器上安装了PHP

例子：

![][2]

优化后

![][3]

- - -

**3.5 关闭注册全局变量**

在PHP中提交的变量，包括使用POST或者GET提交的变量，都会自动注册为全局变量，能够直接访问，这是对服务器非常不安全的，所以我们不能让它注册为全局变量，就把注册全局改变选项关闭

    register_globals = Off  
    ;是否将E,G,P,C,S 变量注册为全局变量  
    ;打开该指令可能会导致严重的安全问题，除非你的脚本经过非常仔细的检查。  
    ;推荐使用预定义的超全局变量：$_ENV.$_GET,$_POST,$_COOKIE,$_SERVER  
    ;该指令受variables_order指令的影响。

**提示：此参数5.5没有**

- - -

**3.6 打开magic_quotes_gpc 来防止SQL注入**

SQL注入是非常危险的问题，轻则网站后台被入侵，重则整个服务器沦陷，所以一定要小心。

     magic_quotes_gpc = Off

这个默认是关闭的，如果它打开将自动把用户提交对sql的查询进行转换，比如把‘转为\'等，这对防止sql注入有重大作用。

     magic_quotes_gpc = On

**提示：5.5已经没有**

**SQL注入防范：**

Nginx 可以使用WA，防止SQL注入（nginx 基于lua模块开发WAF）

apache使用`mod_security`和`mod_evasive` 来防止SQL注入

- - -

**3.7 错误信息控制**

一般php在没有连接到数据库或者其他情况会有提示错误，一般错误信息中会包含php脚本当前的路径信息或者查询的SQL语句等信息，这类信息提供给黑客后，是不安全的，所以一般服务器建议禁止错误提示。

     display_errors = Off  
    ;是否将错误信息作为输出的一部分显示给终端用户，应用调试时，可以打开，方便查看错误  
    ;最终发布的web站点上，强烈建议你关掉这个特性，并使用错误日志代替

设置为

    display_errors = Off (php5.3.27默认即为display_errors = Off)

如果确实是要显示错误信息，一定设置显示错误的级别，计入只显示警告以上的信息。

信息：

    error_reporting = E_WARNING & E_ERROR

当然，最好是关闭错误提示。

- - -

**3.8 错误日志**

建议在关闭`display_errors` 后能够把错误信息记录下来，便于查找服务器运行的原因：

    log_errors = On (php5.3.27 默认即为log_errors = On)

同时也要设置错误日志存放路径的目录，建议根apache的日志存在一起

    error_log = /app/logs/php_error.log

注意：给文件必须允许apache用户的组具有写的权限

日志切割可以使用cp在清空

- - -

### **4、部分资源限制参数优化**

**1.设置每个脚本运行的最长时间**

当无法上传较大的文件或者后台备份数据经常超时，此时需要调整如下：

    max_execution_time = 30  
    ;Maximum amout of memory a script may consume (128MB)  
    ;每个脚本最大允许执行时间(秒)，0表示没有限制  
    ;这个参数有助于阻止劣质脚本无休止的占用服务器资源。  
    ;该指令仅影响脚本本身的运行时间，任何其他花费在脚本运行之外的事件  
    ;如果system()/sleep()函数的使用、数据库查询、文件上传等，都不包括在内  
    ;在安全模式下，你不能用int_set()在运行时改变这个设置

**2.每个脚本使用的最大内存**

    memory_limit = 128M  
    ;一个脚本所能狗申请到的最大内存字节数（可以使用K/M作为单位）  
    ;这有助于防止劣质脚本消耗完服务器上的所有内存。  
    ;要能够使用该指令必须在编译时使用“--enable-memory-limit”配置选项  
    ;如果要取消内存限制，则必须将其设为-1  
    ;设置了该指令后，memory_get_usage()函数将变为可用

**3.每个脚本等待输入数据最长时间**

    max_input_time = -1  
    ;每个脚本解析输入数据(POST,GET,upload)的最大允许时间(秒)  
    ;-1 表示不限制。

设置为

    max_input_time = 60

**4.上载文件的最大许可大小**

当上传较大文件时，需要调整如下参数：

    upload_max_filesize = 2M;  
    ;上载文件最大许可大小，自己改吧，一些图片论坛需要这个更大的值。

## 5、部分安全优化参数

1.禁止打开远程地址，例如：php include的那个漏洞。就是在一个php程序中include了变量，那么入侵者就可以利用这个控制服务器在本地执行远程的一个php程序中include了变量，那么入侵者就可以利用这个控制服务器在本地执行远程的一个php程序，例如phpshell，所以我们关闭这个

    allow_url_fopen = Off

2.设置：`cgi.fix_pathinfo=0` 防止Nginx文件类似错误解析漏洞

- - -

**注：2010年5月23日14:00前阅读本文的朋友，请按目前v1.1版本的最新配置进行设置。** 昨日，80Sec 爆出Nginx具有严重的0day漏洞，详见《[Nginx文件类型错误解析漏洞][4]》。只要用户拥有上传图片权限的Nginx+PHP服务器，就有被入侵的可能。

其实此漏洞并不是Nginx的漏洞，而是PHP PATH_INFO的漏洞，详见：[http://bugs.php.net/bug.php?id=50852&edit=1][5]

例如用户上传了一张照片，访问地址为[http://www.domain.com/images/test.jpg][6]，而test.jpg文件内的内容实际上是PHP代码时，通过[http://www.domain.com/images/test.jpg/abc.php][7]就能够执行该文件内的PHP代码。

- - -

**网上提供的临时解决方法有：** 
方法①、修改`php.ini`，设置`cgi.fix_pathinfo = 0`;然后重启php-cgi。此修改会影响到使用PATH_INFO伪静态的应用，例如我以前博文的URL：[http://blog.zyan.cc/read.php/348.htm][8] 就不能访问了。

方法②、在nginx的配置文件添加如下内容后重启：

    if ( $fastcgi_script_name ~ \..*\/.*php ) {return 403;}
该匹配会影响类似 [http://www.domain.com/software/5.0/test.php][9]（5.0为目录），[http://www.domain.com/goto.php/phpwind][10] 的URL访问。

  
方法③、对于存储图片的`location{...}`，或虚拟主机`server{...}`，只允许纯静态访问，不配置PHP访问。例如在金山逍遥网论坛、  
SNS上传的图片、附件，会传送到专门的图片、附件存储服务器集群上（pic.xoyo.com），这组服务器提供纯静态服务，无任何动态PHP配置。各大网站几乎全部进行了图片服务器分离，因此Nginx的此次漏洞对大型网站影响不大。

[本文转载][11]

- - -

### 6、调整php sesson 信息存放类型和位置

    session.save_handler = files  
    ;存储和检索与会话关联的数据的处理器名字，默认为文件("files")  
    ;如果想要使用自定义的处理器(如基于数据库的处理器)，可用“user”  
    ;设为“memcached”则可以使用memcached作为会话处理器(需要指定“--enable-memcache-seesion”编译选项)  
    ;session.save_path = "/tmp"  
    ;传递给存储处理器的参数，对于files处理器，此值是创建会话数据文件的路径。

可以直接memcached来作php的`session.save_handler`.

 **修改成如下配置：**

    session.save_handler = memcache  
    session.save_path = "tcp://10.0.0.18:11211"

**提示：**  
1）10.0.0.18:11211 为memcached数据库缓存的IP及端口。  
2）上述适合LNMP,LAMP环境。  
3）memcached服务器也可以是多台通过hash调度。

- - -

**php5.3 配置ini *****为重点** 

配置php.ini

    338 行 设置为 safe_mode = On #开启安全模式  
    435 行 设置为 expose_php = Off #关闭版本信息  
    538 行 设置为 display_errors = Off #错误信息控制，测试的时候开启   
    #报错的级别在521行 默认为 error_reporting = E_ALL & ~E_DEPRECATED  
    559 行 设置为 log_errors = On #打开log 日志  
    643 行 设置为 error_log = /app/logs/php_errors.log #log日志得路径（需log_errors 为 On 才能生效）  
    703 行 设置为 register_globals = Off #关闭全局变量（默认即为关闭，万万不能开启）  
    756 行 设置为 magic_quotes_gpc = On #防止SQL注入  
    902 行 设置为 allow_url_fopen = Off #打开远程打开（禁止）  
    854 行 设置为 cgi.fix_pathinfo=0 #防止Nginx文件类型错误解析漏洞  
    #可修改参数  
    444 max_execution_time = 30 #单个脚本最大运行时间，单位是秒（****开发插入程序时可能会需要把数值调大）   
    454 max_input_time = 60 #单个脚本等待输入的最长时间  
    465 memory_limit = 128M #单个脚本最大使用内存，单位为K或M（128M稍大可以适当调小）  
    891 upload_max_filesize = 2M #上传文件最大许可  
    894 max_file_uploads = 20 #可以通过单个请求上载的最大文件数

## php-fpm.conf优化配置

![][12]

**PHP 优化前后对比图**

修改后

    pid = /app/log/php-fpm.pid  
    error_log = /app/logs/php-fpm.log  
    log_level = error  
    events.mechanism = epoll  
    listen.owner = www  
    listen.group = www  
    pm.max_children = 1024  
    pm.start_servers = 14  
    pm.min_spare_servers = 5  
    pm.max_spare_servers = 20  
    pm.process_idle_timeout = 15s;  
    pm.max_requests = 2048  
    slow = /app/logs/$pool.log.slow  
    rlimit_files = 32768  
    request_slowlog_timeout = 10 
 修改前 
 
    ;pid = run/php-fpm.pid

    ;error_log = log/php-fpm.log

    ;log_level = notice

    ;events.mechanism = epoll

    ;listen.owner = www

    ;listen.group = www

    pm.max_children = 5

    pm.start_servers = 2

    pm.min_spare_servers = 1

    pm.max_spare_servers = 3

    ;pm.process_idle_timeout = 10s;

    ;pm.max_requests = 500

    ;slowlog = log/$pool.log.slow

    ;rlimit_files = 1024

    ;request_slowlog_timeout = 0

**优化参数介绍**

- - -

    error_log = /app/logs/php-fpm.log #指定pid路径

    log_level = error      #开启日志，log级别为error

    events.mechanism = epoll  #使用epoll模式

    listen.owner = www     #使用php的用户

    listen.group = www

    pm.max_children = 1024   #php子进程数量

    pm.start_servers = 14    #php初始启动子进程数量

    pm.min_spare_servers = 5  #php最小空闲进程数量

    pm.max_spare_servers = 20  #php最大空闲进程数量

    pm.process_idle_timeout = 15s; #进程超时时间

    pm.max_requests = 2048   #每个子进程退出之前可以进行的请求数

    slowlog = /app/logs/$pool.log.slow #开启慢查询日志(执行程序时间长了可以查看到)

    rlimit_files = 32768      #开启文件描述符数量

    request_slowlog_timeout = 10  #慢查询的超时时间，超时10秒记录

- - -

**温馨提示：**

**所有的优化都需要看业务进行操作，否则会出现问题！**

**修改配置文件前操作前必须备份！****操作前必须备份！****操作前必须备份！**

- - -

 **参考资料：**

[**Apache主配置文件httpd.conf 详解**][13]

- - -

[**php-fpm 启动参数及重要配置详解**][14]

- - -

[**LAMP 系统性能调优，第 1 部分: 理解 LAMP 架构**][15]

- - -

[**LAMP 系统性能调优，第 2 部分: 优化 Apache 和 PHP**][16]

- - -

**[LAMP 系统性能调优，第 3 部分: MySQL 服务器调优][17]**

****

****

**所谓天才，只不过是把别人喝咖啡的功夫都用在工作上了. ——鲁迅**

[0]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://oldboy.blog.51cto.com/
[1]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://baike.baidu.com/view/1511292.htm
[2]: http://www.abcdocker.com/wp-content/uploads/2016/08/a2b7f6cb12790b389b57217e0386ee99_b57fa839-80fa-4aab-9453-3788b2e8f4ed.png
[3]: http://www.abcdocker.com/wp-content/uploads/2016/08/a2b7f6cb12790b389b57217e0386ee99_04818873-478f-4669-8851-600cd5ffc64e.png
[4]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://www.80sec.com/nginx-securit.html
[5]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://bugs.php.net/bug.php?id=50852&edit=1
[6]: http://www.domain.com/images/test.jpg
[7]: http://www.domain.com/images/test.jpg/abc.php
[8]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://blog.zyan.cc/read.php/348.htm
[9]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://www.domain.com/software/5.0/test.php
[10]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://www.domain.com/goto.php/phpwind
[11]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://zyan.cc/nginx_0day
[12]: http://www.abcdocker.com/wp-content/uploads/2016/08/a2b7f6cb12790b389b57217e0386ee99_00adb309-27f1-433f-968d-b4cade696749.png
[13]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://www.linuxidc.com/Linux/2015-02/113921.htm
[14]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=http://www.cnblogs.com/argb/p/3604340.html
[15]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=https://www.ibm.com/developerworks/cn/linux/l-tune-lamp-1/
[16]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=https://www.ibm.com/developerworks/cn/linux/l-tune-lamp-2.html/
[17]: https://www.abcdocker.com/wp-content/themes/begin2.0-1/inc/go.php?url=https://www.ibm.com/developerworks/cn/linux/l-tune-lamp-3.html/
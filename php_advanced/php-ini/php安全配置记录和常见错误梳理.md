# [php安全配置记录和常见错误梳理][0]

通常部署完php环境后会进行一些安全设置，除了熟悉各种php漏洞外，还可以通过配置php.ini来加固PHP的运行环境，PHP官方也曾经多次修改php.ini的默认设置。  
下面对php.ini中一些安全相关参数的配置进行说明

```
register_globals当register_globals = ON时，PHP不知道变量从何而来，也容易出现一些变量覆盖的问题。因此从最佳实践的角度，强烈建议设置 register_globals = OFF，这也是PHP新版本中的默认设置。

open_basediropen_basedir可以限制PHP只能操作指定目录下的文件。这在对抗文件包含、目录遍历等攻击时非常有用，应该为此选项设置一个值。需要注意的是，如果设置的值是一个指定的目录，则需要在目录最后加上一个“/”，否则会被认为是目录的前缀。open_basedir =/home/web/html/

allow_url_include = Off为了对抗远程文件包含，请关闭此选项，一般应用也用不到此选项。同时推荐关闭的还有allow_url_fopen。

display_errors = Off错误回显，一般常用于开发模式，但是很多应用在正式环境中也忘记了关闭此选项。错误回显可以暴露出非常多的敏感信息，为攻击者下一步攻击提供便利。推荐关闭此选项。

log_errors = On在正式环境下用这个就行了，把错误信息记录在日志里。正好可以关闭错误回显。

magic_quotes_gpc = Off推荐关闭，它并不值得依赖（请参考“注入攻击”一章），已知已经有若干种方法可以绕过它，甚至由于它的存在反而衍生出一些新的安全问题。XSS、SQL注入等漏洞，都应该由应用在正确的地方解决。同时关闭它还能提高性能。

cgi.fix_pathinfo = 0若PHP以CGI的方式安装，则需要关闭此项，以避免出现文件解析问题（请参考“文件上传漏洞”一章）。

session.cookie_httponly = 1 开启HttpOnly

session.cookie_secure = 1若是全站HTTPS则请开启此项。

sql.safe_mode = OffPHP的安全模式是否应该开启的争议一直比较大。一方面，它会影响很多函数；另一方面，它又不停地被黑客们绕过，因此很难取舍。如果是共享环境（比如App Engine），则建议开启safe_mode，可以和disable_functions配合使用；如果是单独的应用环境，则可以考虑关闭它，更多地依赖于disable_functions控制运行环境安全。

disable_functions =能够在PHP中禁用函数（如上默认=号后面什么都不配置）。这是把双刃剑，禁用函数可能会为开发带来不便，但禁用的函数太少又可能增加开发写出不安全代码的几率，同时为黑客获取webshell提供便利。一般来说，如果是独立的应用环境，则推荐禁用以下函数：disable_functions = escapeshellarg, escapeshellcmd,exec,passthru, proc_close, proc_get_status, proc_open, proc_nice,proc_terminate, shell_exec, system, ini_restore, popen, dl,disk_free_space, diskfreespace, set_time_limit, tmpfile, fopen,readfile, fpassthru, fsockopen, mail, ini_alter, highlight_file,openlog, show_source,symlink, apache_child_terminate,apache_get_modules, apache_get_version, apache_getenv,apache_note, apache_setenv, parse_ini_file
```

### php 上传大文件主要涉及配置`upload_max_filesize`和`post_max_size`两个选项


曾经遇到的问题：
在网站后台上传图片的时候出现一个非常怪的问题，有时候表单提交可以获取到值，有时候就获取不到了，连普通的字段都获取不到了，苦思冥想还没解决，最后问了师傅，
师傅看了说挺奇怪的，然后问我 `upload_max_filesize`的值改了吗，我说改了啊，师傅也解决不了了。过了一会师傅问 `post_max_size`改了吗，我说那个和上传没关系吧，
师傅没理我，我还是照着自己的想法继续测试，弄了半天还是不行，最后试了师傅提的意见，成功了，原来上传是和 post_max_size有关系的。
  
问题总结 :
php.ini配置文件中的默认文件上传大小为 2M，默认`upload_max_filesize = 2M` ，即文件上传的大小为 2M，如果你想上传超过8M的文件，比如 20M，
  
必须设定 `upload_max_filesize = 20M`。但是光设置`upload_max_filesize = 20M`还是无法实现大文件的上传功能，你必须修改 php.ini配置文件中的`post_max_size`选项，
其代表允许 POST的数据最大字节长度，默认为 8M。如果POST 数据超出限制，那么 `$_POST`和`$_FILES` 将会为空。要上传大文件，
你必须设定该选项值大于 `upload_max_filesize`指令的值，我一般设定`upload_max_filesize`和 `post_max_size`值相等。
另外如果启用了内存限制，那么该值应当小于 `memory_limit`选项的值。
  
文件上传的其他注意事项 :
在上传大文件时，你会有上传速度慢的感觉，当超过一定的时间，会报脚本执行超过 30秒的错误，这是因为在php.ini配置文件中 `max_execution_time` 配置选项在作怪，
其表示每个脚本最大允许执行时间 (秒) ，0 表示没有限制。你可以适当调整 `max_execution_time`的值，不推荐设定为0。


解释：
具体可查看 PHP手册 中的 〔php.ini 核心配置选项说明〕
`upload_max_filesize` 所上传的文件的最大大小。
`post_max_size`       设定 POST 数据所允许的最大大小。
`memory_limit`        设定了一个脚本所能够申请到的最大内存字节数。
 
一般来说：`memory_limit` > `post_max_size` > `upload_max_filesize`
  
`upload_max_filesize`是限制本次上传的最大值
`post_max_size`是post数据的最大值， 通过POST提交数据的最大值
一般我们在php中用的是POST方式上传


---

### php.ini中记录PHP错误日志的参数：display_errors与log_errors的区别


#### 1）display_errors
错误回显，一般常用语开发模式，但是很多应用在正式环境中也忘记了关闭此选项。错误回显可以暴露出非常多的敏感信息，为攻击者下一步攻击提供便利。推荐关闭此选项。
 
display_errors = On
开启状态下，若出现错误，则报错，出现错误提示。即显示所有错误信息。
 
dispaly_errors = Off
关闭状态下，若出现错误，则提示：服务器错误，但是不会出现错误提示。即关闭所有错误信息
 
#### 2）log_errors
在正式环境下用这个就行了，把错误信息记录在日志里。正好可以关闭错误回显。
 
    log_errors = On    //注意，log_errors设置为On后，那么dispaly_errors就要设置为Off，这两个不能同时打开。
     
    error_log = /Data/logs/php/error.log   //注意，log_errors设置为On时，必须要设置error_log的日志文件路径，并且这个日志文件要能有权限正常写入。
 
也就是说log_errors = On时，必须指定error_log文件，如果没指定或者指定的文件没有权限写入，那么照样会输出到正常的输出渠道，那么也就使得display_errors 这个指定的Off失效，错误信息还是打印了出来。
 
对于PHP开发人员来说，一旦项目上线后，第一件事就是应该将display_errors选项关闭，以免因为这些错误所透露的路径、数据库连接、数据表等信息而遭到黑客攻击。
 
---------------------------------------------------

一般说来：
 
#### 测试环境下的php.ini中的错误日志设置：             

    error_reporting = E_ALL 
    display_errors = On 
    html_errors = On 
    log_errors = Off 
 
#### 正式环境下的php.ini中的错误日志设置：

    error_reporting = E_ALL &~ E_NOTICE &~ E_WARNING       //注意这个设置，记得有一次因为这个设置有误，导致了线上一个业务访问出现了nginx 500报错！这个导致了php框架报错！ 
    display_errors = Off 
    log_errors = On 
    html_errors = Off 
    error_log = /Data/logs/php/error.log
    ignore_repeated_errors = On 
    ignore_repeated_source = On 
 
#### 简单讲解下各个配置的意义：

    error_reporting ：设置报告哪些错误 
    display_errors ：设置错误是否作为输出的一部分显示 
    html_errors ：设置错误信息是否采用html格式 
    log_errors ：设置是否记录错误信息 
    error_log ：设置错误信息记录的文件 
    ignore_repeated_errors ：是否在同一行中重复显示一样的错误信息 
    ignore_repeated_source ： 是否重复显示来自同个文件同行代码的错误


----

### 顺便记录下php的页面老是报时区错误的处理过程：

```
Warning: phpinfo(): It is not safe to rely on the system's timezone settings. You are
*required* to use the date.timezone setting or the date_default_timezone_set()
function. In case you used any of those methods and you are still getting this
warning, you most likely misspelled the timezone identifier. We selected the
timezone 'UTC' for now, but please set date.timezone to select your timezone. in
/usr/local/www/zabbix2/phpinfo.php on line 2
date/time support enabled
"Olson" Timezone Database Version 2013.8
Timezone Database internal
Default timezone UTC
```

修改php.ini 文件

```
# vim /usr/local/php/etc/php.ini
........
[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = Asia/Shanghai
```

注意必须把要 php.ini 复制一份到/usr/local/php/lib/下，否则 php 服务默认会到这个 lib 目录下读取 php.ini 文件，没有的话，就是默认时区UTC，这个时区和北京时间相差8小时。

```
[root@i-gxcmjlge lib]# pwd
/usr/local/php/lib
[root@i-gxcmjlge lib]# ll
total 72
drwxr-xr-x 14 root root 4096 Nov 18 01:11 php
-rw-r--r-- 1 root root 65681 Nov 18 15:01 php.ini
```
 
然后重启php服务和nginx/apache服务


### 除了php.ini文件，还要注意php-fpm.conf配置，如下：

```
[root@i-v5lmgh7y etc]# cat php-fpm.conf|grep -v "^;"|grep -v "^$"
[global]
pid = run/php-fpm.pid   //pid 设置，默认在安装目录中的 var/run/php-fpm.pid，建议开启
error_log = log/php-fpm.log   //错误日志，默认在安装目录中的 var/log/php-fpm.log
log_level = notice      //错误级别. 可用级别为: alert（必须立即处理）, error（错误情况）, warning（警告情况）, notice（一般重要信息）, debug（调试信息）. 默认: notice.
emergency_restart_threshold = 60
emergency_restart_interval = 60s //表示在emergency_restart_interval所设值内出现SIGSEGV或者SIGBUS错误的php-cgi进程数如果超过 emergency_restart_threshold个，php-fpm就会优雅重启。这两个选项一般保持默认值。
process_control_timeout = 0  //设置子进程接受主进程复用信号的超时时间. 可用单位: s(秒), m(分), h(小时), 或者 d(天) 默认单位: s(秒). 默认值: 0.
daemonize = yes   //后台执行fpm,默认值为yes，如果为了调试可以改为no。在FPM中，可以使用不同的设置来运行多个进程池。 这些设置可以针对每个进程池单独设置。
 
[www]
user = nobody     //启动进程的帐户
group = nobody    //启动进程的组
listen = 127.0.0.1:9000    //fpm监听端口，即nginx中php处理的地址，一般默认值即可。可用格式为: 'ip:port', 'port', '/path/to/unix/socket'. 每个进程池都需要设置.
listen.backlog = 1024   //backlog数，，由操作系统决定，-1表示无限制。也可以注释掉此行。
listen.allowed_clients = 127.0.0.1  //（可以不设置此行）允许访问FastCGI进程的IP，如果没有设置或者为空，则允许任何服务器请求连接。设置any为不限制IP，如果要设置其他主机的nginx也能访问这台FPM进程，listen处要设置成本地可被访问的IP。默认值是any。每个地址是用逗号分隔.
 
pm = static   //对于专用服务器，pm可以设置为static，如何控制子进程，选项有static和dynamic。如果选择static，则由pm.max_children指定固定的子进程数。如果选择dynamic，则由下开参数决定：
pm.max_children = 512   //子进程最大数
pm.start_servers = 387  //启动时的进程数
pm.min_spare_servers = 32  //保证空闲进程数最小值，如果空闲进程小于此值，则创建新的子进程
pm.max_spare_servers = 387  //保证空闲进程数最大值，如果空闲进程大于此值，此进行清理
pm.max_requests = 1024  //设置每个子进程重生之前服务的请求数. 对于可能存在内存泄漏的第三方模块来说是非常有用的. 如果设置为 '0' 则一直接受请求. 等同于 PHP_FCGI_MAX_REQUESTS 环境变量. 默认值: 0
pm.status_path = /status   //fpm状态页面的网址. 如果没有设置, 则无法访问状态页面. 默认值: none. munin监控会使用到
 
ping.path = /ping   //fpm监控页面的ping网址. 如果没有设置, 则无法访问ping页面. 该页面用于外部检测FPM是否存活并且可以响应请求. 请注意必须以斜线开头 (/)。可以不设置此行。
ping.response = pong  //用于定义ping请求的返回相应. 返回为HTTP 200的text/plain 格式文本. 默认值: pong。可以不设置此行。
  
slowlog = var/log/slow.log   //慢请求的记录日志,配合request_slowlog_timeout使用
request_slowlog_timeout = 0  //设置单个请求的超时中止时间. 该选项可能会对php.ini设置中的'max_execution_time'因为某些特殊原因没有中止运行的脚本有用. 设置为 '0' 表示 'Off'.当经常出现502错误时可以尝试更改此选项。
request_terminate_timeout = 10s  //当一个请求该设置的超时时间后，就会将对应的PHP调用堆栈信息完整写入到慢日志中. 设置为 '0' 表示 'Off'。可以不设置此行。
rlimit_files = 65535    //设置文件打开描述符的rlimit限制. 默认值: 系统定义值默认可打开句柄是1024，可使用 ulimit -n查看，ulimit -n 2048修改。
rlimit_core = 0   //设置核心rlimit最大限制值. 可用值: 'unlimited' 、0或者正整数. 默认值: 系统定义值.
catch_workers_output = yes  //重定向运行过程中的stdout和stderr到主要的错误日志文件中. 如果没有设置, stdout 和 stderr 将会根据FastCGI的规则被重定向到 /dev/null . 默认值: 空.
```

### Nginx+Php中限制站点目录防止跨站的配置方案记录（使用open_basedir）--

**方法1）在Nginx配置文件中加入：**

    fastcgi_param PHP_VALUE"open_basedir=$document_root:/tmp/:/proc/";

通常nginx的站点配置文件里用了include fastcgi.conf;，这样的，把这行加在fastcgi.conf里就OK了。  
如果某个站点需要单独设置额外的目录，把上面的代码写在include fastcgi.conf;这行下面就OK了，会把fastcgi.conf中的设置覆盖掉。  
这种方式的设置需要重启nginx后生效。

**方法2）在php.ini中加入**
```
[HOST=www.wangshibo.com]
open_basedir=/home/www/www.wangshibo.com:/tmp/:/proc/
[PATH=/home/www/www.wangshibo.com]
open_basedir=/home/www/www.wangshibo.com:/tmp/:/proc/

```
这种方式的设置需要重启php-fpm后生效。

**方法3）在网站根目录下创建.user.ini文件，并在该文件中写入下面信息：**

    open_basedir=/home/www/www.wangshibo.com:/tmp/:/proc/
    
这种方式不需要重启nginx或php-fpm服务。安全起见应当取消掉.user.ini文件的写权限。

php.ini中建议禁止的函数如下：


    disable_functions = pcntl_alarm, pcntl_fork, pcntl_waitpid, pcntl_wait, pcntl_wifexited, pcntl_wifstopped, pcntl_wifsignaled, pcntl_wexitstatus, pcntl_wtermsig, pcntl_wstopsig, pcntl_signal, pcntl_signal_dispatch, pcntl_get_last_error, pcntl_strerror, pcntl_sigprocmask, pcntl_sigwaitinfo, pcntl_sigtimedwait, pcntl_exec, pcntl_getpriority, pcntl_setpriority,eval, popen, passthru,exec, system, shell_exec, proc_open, proc_get_status, chroot,chgrp,chown, ini_alter, ini_restore, dl, pfsockopen, openlog, syslog, readlink,symlink, popepassthru, stream_socket_server, fsocket, chdir 
    

----

### php启动后，9000端口没有起来？-

问题描述：  
php服务安装后，启动php-fpm，启动的时候没有报错。然后`ps -ef|grep php`没有发现进程起来，`lsof -i:9000`发现端口也没有起来。  
查看日志，发现系统所允许打开的文件数超过了预定设置。

```
[root@i-v5lmgh7y etc]# /usr/local/php/sbin/php-fpm
[root@i-v5lmgh7y etc]# ps -ef|grep php
[root@i-v5lmgh7y etc]#lsof -i:9000
[root@i-v5lmgh7y etc]#
```

查看错误日志发现问题：

```
[root@i-v5lmgh7y log]# tail -f php-fpm.log
[15-Nov-2015 23:53:15] NOTICE: fpm is running, pid 18277
[15-Nov-2015 23:53:15] ERROR: failed to prepare the stderr pipe: Too many open files (24)
[15-Nov-2015 23:53:16] NOTICE: exiting, bye-bye!
[15-Nov-2015 23:53:59] NOTICE: fpm is running, pid 18855
```

发现是系统允许打开的文件数超了预定的设置。需要调大这个值：

```
[root@i-v5lmgh7y etc]# ulimit -n
1024
[root@i-v5lmgh7y etc]# ulimit -n 65535    //临时解决办法
[root@i-v5lmgh7y etc]# ulimit -n
65535
```
 
永久解决办法：
在`/etc/security/limits.conf`文件底部添加下面四行内容：

```
[root@i-v5lmgh7y etc]# cat /etc/security/limits.conf
.........
# End of file
* soft nproc unlimited
* hard nproc unlimited
* soft nofile 65535
* hard nofile 65535
```

然后再次启动php-fpm程序，9000端口就能正常启动了

```
[root@i-v5lmgh7y etc]# /usr/local/php/sbin/php-fpm
[root@i-v5lmgh7y etc]# ps -ef|grep php
root 21055 1 0 00:12 ? 00:00:00 php-fpm: master process
(/usr/local/php/etc/php-fpm.conf)
nobody 21056 21055 0 00:12 ? 00:00:00 php-fpm: pool www
nobody 21057 21055 0 00:12 ? 00:00:00 php-fpm: pool www

```


### 下面梳理几个常见的php不恰当配置引发的问题



#### 1）`request_terminate_timeout`的值如果设置为0或者过长的时间，可能会引起`file_get_contents`的资源问题。
如果访问请求的远程资源反应过慢，php-cgi进程就会一直卡在那里不会超时。虽然php.ini文件里面`max_execution_time`可以设置PHP脚本的最大执行时间，但是，在php-cgi(php-fpm) 中该参数不会起效。真正能够控制PHP脚本最大执行时间的是php-fpm.conf配置文件中的`request_terminate_timeout`参数。
 
`request_terminate_timeout`默认值为0秒，也就是说，PHP脚本会一直执行下去。这样当所有的php-cgi进程都卡住时，这台Nginx+PHP的WebServer已经无法再处理新的PHP请求了，Nginx 将给用户返回“502 Bad Gateway”。
修改该参数，设置一个PHP脚本最大执行时间是必要的，但是治标不治本。例如改成30s，如果发生访问获取网页内容较慢的情况，这就意味着150个php-cgi进程，每秒钟只能处理5个请求，WebServer同样很难避免”502 Bad Gateway”。
 
解决办法是`request_terminate_timeout`设置为10s或者一个合理的值。
 
#### 2）`max_requests`参数配置不当，可能会引起间歇性502错误
设置每个子进程重生之前服务的请求数. 对于可能存在内存泄漏的第三方模块来说是非常有用的.
如果设置为0，则一直接受请求，等同于`php_fcgi_max_requests`环境变量。默认值为 0.
比如：`pm.max_requests = 1000`  这个配置的意思是，当一个 php-cgi 进程处理的请求数累积到500个后，自动重启该进程。
 
但是为什么要重启进程呢？
一般在项目中，多多少少都会用到一些PHP的第三方库，这些第三方库经常存在内存泄漏问题，如果不定期重启php-cgi进程，势必造成内存使用量不断增长。因此php-fpm作为php-cgi的管理器，提供了这么一项监控功能，对请求达到指定次数的php-cgi进程进行重启，保证内存使用量不增长。正是因为这个机制，在高并发的站点中，经常导致502错误，
目前解决方法是，把这个值尽量设置大些，尽可能减少php-cgi重新SPAWN的次数，同时也能提高总体性能。在实际的生产环境中发现，内存泄漏如果不明显，可以将这个值设置得非常大（比如204800）。要根据自己的实际情况设置这个值（比如我们线上设置1024），不能盲目地加大。
话说回来，这套机制目的只为保证php-cgi不过分地占用内存，为何不通过检测内存的方式来处理呢？通过设置进程的峰值内在占用量来重启php-cgi进程，会是更好的一个解决方案。
 
#### 3）php-fpm的慢日志，debug及异常排查神器
`request_slowlog_timeout`设置一个超时的参数，`slowlog`设置慢日志的存放位置



**当你发现自己的才华撑不起野心时，就请安静下来学习吧**

[0]: http://www.cnblogs.com/kevingrace/p/5685471.html
[1]: #
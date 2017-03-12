# nginx的日志

 时间 2017-02-14 01:20:41  [Jackie的家][0]

_原文_[http://www.jackieathome.net/archives/431.html][1]

 主题 [Nginx][2][Linux命令][3]

## nginx的日志

nginx的日志包括错误日志和访问日志，分别使用不同的指令来定义其输出位置和相应的级别。

下面介绍其各自的用途。

## 错误日志

nginx提供了 error_log 指令来指定错误日志的输出文件和级别。 

指令定义如下：

    Syntax: error_log file [level];
    Default:    
    error_log logs/error.log error;
    Context:    main, http, mail, stream, server, location
    

error_log的第一个参数，定义了输出的文件名。

另外还可以使用一些特殊的文件，可选值有：

* stderr，向nginx进程的标准错误输出流输出日志。
* syslog，向 [syslog][4] 输出日志。配置样例如下： 

    error_log syslog: server=192.168.1.1 debug;
* memory，向环形内存缓冲区写出日志，一般情况下仅在调试时才会用到。配置样例如下：

    error_log memory:32m debug;

使用gdb的指令来查看内存中日志的方法，如下

    set $log = ngx_cycle->log
    
    while $log->writer != ngx_log_memory_writer
        set $log = $log->next
    end
    
    set $buf = (ngx_log_memory_buf_t *) $log->wdata
    dump binary memory debug_log.txt $buf->start $buf->end
* /dev/null，忽略错误日志，相当于是关闭了错误日志。

error_log指令的第二个参数，定义了输出日志的级别，默认值为 error 。官方文档对级别的定义比较简单，如下说明来自于文章 [Configuring the Nginx Error Log and Access Log][5]

* debug – Useful debugging information to help determine where the problem lies.
* info – Informational messages that aren’t necessary to read but may be good to know.
* notice – Something normal happened that is worth noting.
* warn – Something unexpected happened, however is not a cause for concern.
* error – Something was unsuccessful.
* crit – There are problems that need to be critically addressed.
* alert – Prompt action is required.
* emerg – The system is in an unusable state and requires immediate attention.

从上到下，严重程度逐渐变高。比如使用如下指令指定级别为 error 时， 

    error_log logs/error.log error;
    

级别为 error 、 crit 、 alert 、 emerg 的日志将输出到文件 logs/error.log 中。 

需要注意的是，在构建nginx时需要指定选项 --with-debug ，否则无法使用 error_log 来使 debug 生效，参见 [A debugging log][6] 。 

## 访问日志

nginx提供了 access_log 指令来实现访问日志的输出文件和级别。 

官方文档中给出的配置样例

    log_format compression '$remote_addr - $remote_user [$time_local] '
                           '"$request" $status $bytes_sent '
                           '"$http_referer" "$http_user_agent" "$gzip_ratio"';
    
    access_log /spool/logs/nginx-access.log compression buffer=32k;
    

关键指令有 log_format 和 access_log 。 

### log_format

指令定义如下：

    Syntax: log_format name [escape=default|json] string ...;
    Default:    
    log_format combined "...";
    Context:    http
    

使用 log_format 可以在配置文件中定义多个输出格式，满足不同场景下的 access_log 指令输出日志的需求。 

如下是 log_format 指令提供的日志字段和说明，来自 [nginx日志配置][7]

* $remote_addr和$http_x_forwarded_for，记录客户端IP地址。
* $remote_user，记录客户端用户名称。
* $request，记录请求的URL和HTTP协议。
* $status，记录响应的状态码
* $body_bytes_sent，发送给客户端的字节数，不包括响应头的大小；该变量与Apache模块mod_log_config里的“%B”参数兼容。
* $bytes_sent，发送给客户端的总字节数。
* $connection，连接的序列号。
* $connection_requests，当前通过一个连接获得的请求数量。
* $msec，日志写入时间。单位为秒，精度是毫秒。
* $pipe，如果请求是通过HTTP流水线(pipelined)发送，pipe值为“p”，否则为“.”。
* $http_referer，记录从哪个页面链接访问过来的。
* $http_user_agent，记录客户端浏览器相关信息。
* $request_length，请求的长度（包括请求行，请求头和请求正文）。
* $request_time，请求处理时间，单位为秒，精度毫秒。从读入客户端的第一个字节开始，直到把最后一个字符发送给客户端后进行日志写入为止。
* $time_iso8601，ISO8601标准格式下的本地时间。
* $time_local，通用日志格式下的本地时间。

定位性能问题时比较有用的几个字段，如下

* $upstream_connect_time，与upstream服务器建立链接时所花费的时间。
* $upstream_header_time，与upstream服务器，从建立链接开始到收到响应消息的http头部的第一个字节时所花费的时间。
* $upstream_response_time，与upstream服务器，从建立链接到接收到响应的最后一个字节时所花费的时间。
* $request_time，从接收到客户请求的第一个字节到返回给客户响应的最后一个字节发出时，所花费的时间。

### access_log

指令定义如下

    Syntax: access_log path [format [buffer=size] [gzip[=level]] [flush=time] [if=condition]];
    access_log off;
    Default:    
    access_log logs/access.log combined;
    Context:    http, server, location, if in location, limit_except
    

指令的参数说明：

* path，日志文件的全路径名。与 error_log 相同， access_log 指令也允许将日志输出到 [syslog][4] 输出日志，配置方法与 error_log 相同。
* format，日志的格式，使用 log_format 指令指定。
* buffer，内存中缓存日志的大小，适当的取值可以改善日志输出操作的效率。
* gzip，日志数据的压缩级别。
* flush，日志保存在缓存区中的最长时间，配合使用 buffer ，可以改善日志输出操作的效率。

配置样例

    # 关闭日志。
    access_log off;
    # 日志记录至logs/access.log中，格式为combined。
    access_log logs/access.log combined;
    
    # 日志记录至logs/access.log，内存中缓存至多64k日志，每隔10s将日志刷新到文件中。
    access_log logs/access.log buffer=64k flush=10s;
    

## 其它指令

### open_log_file_cache

指令定义如下

    Syntax: open_log_file_cache max=N [inactive=time] [min_uses=N] [valid=time];
    open_log_file_cache off;
    Default:    
    open_log_file_cache off;
    Context:    http, server, location
    

指令的参数说明：

* max，设置缓存中的最大文件描述符数量，当缓存中的文件句柄的数量超出本值，则采用LRU算法选择需要清理的文件句柄。
* inactive，设置存活时间，当文件句柄在指定时间内没有日志写出，则关闭该文件句柄。默认是10s。
* min_uses，在 inactive 指定的时间段内，文件句柄至少使用的次数，否则会被关闭。默认是1次。
* valid，检查文件句柄关联的日志文件是否存在的时间间隔。默认60s。
* off，关闭缓存。

配置样例

    open_log_file_cache max=1000 inactive=20s valid=1m min_uses=2;
    

### log_not_found

指令定义如下

    Syntax: log_not_found on | off;
    Default:    
    log_not_found on;
    Context:    http, server, location
    

是否在error_log中记录被访问URL不存在的错误。默认值为 on ，表示记录相关信息。 

### log_subrequest

指令定义如下

    Syntax: log_subrequest on | off;
    Default:    
    log_subrequest off;
    Context:    http, server, location
    

是否在access_log中记录子请求的访问日志。默认值为 off ，表示不记录相关信息。 

### rewrite_log

指令定义如下

    Syntax: rewrite_log on | off;
    Default:    
    rewrite_log off;
    Context:    http, server, location, if
    

用来控制nginx执行URL重写操作的处理日志是否写出到文件中；在调试重写规则时，建议开启日志。当启用后，将以 notice 级别来记录URL重写操作相关的日志。 

## 如何管理nginx输出的日志

为避免应用输出的日志过多、过大，导致硬盘满而影响应用、硬件的稳定性，需要对日志文件进行管理。而通常情况下采取的策略有：

* 文件绕接，控制文件的数量，避免文件无限制生成。
* 按天切换，控制文件的生成规则，避免单个文件中记录的数量过多，同时便于管理。
* 按大小切换，控制文件的大小，避免单个文件中记录的数量过多，同时便于管理。
* 。。。

从官方文档以及众网友的分享看，nginx并没有对日志文件的管理提供原生的支持。但仍然可以找到一些方法来解决nginx日志管理的问题。

### 原生方法

nginx的开发者提供了一种简单、粗暴的方式来实现日志文件的切换。来自官网的一篇文章 [Log Rotation][8] 介绍了这种方法，核心脚本如下： 

    mv access.log access.log.0
    kill -USR1 `cat master.nginx.pid`
    sleep 1
    # do something with access.log.0
    gzip access.log.0
    

nginx开发人员对上述方法的解释：

* [How it works][9]  
The rotator should send the -USR1 signal to the master process. The master process reopens files, does chown() and chmod() to enable the worker processes to write to files, and send a notification to the worker procesess. They reopen files instantly. If the rotator sends the -HUP signal, then them master does a reconfiguration and starts a new worker processes those write to the new log files, but the old shuting down worker processes still uses the old log files.
* [Another explanation][10]  
When master process receives -USR1 it repopens all logs, does chown() and chmod() (to allow unpriviliged worker processes to reopen them), and notifies workers about reopening. Then the workers reopens its logs too. So the old logs are available to gzip right away – you will not lose any line.

### 使用logrotate

在网上简单搜索，发现使用 [logrotate][11] 也是一个不错的选择，网上可以找到相当数量的介绍文章。此处不再赘述使用方法和原理。 

## 如何利用nginx的日志

如下是截取自 [纯手工玩转 Nginx 日志][12] 的一些例子。 

给定 access_log 日志的格式如下 

    log_format myformat '$remote_addr^A$http_x_forwarded_for^A$host^A$time_local^A$status^A'
    '$request_time^A$request_length^A$bytes_sent^A$http_referer^A$request^A$http_user_agent';
    

利用 [awk][13] 命令，可以快速得到如下的数据。 

* 查找访问频率最高的URL，以及相应的访问次数： 

    cat access.log | awk -F '^A' '{print $10}' | sort | uniq -c
* 查找当前日志文件中500错误的访问： 

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}'
* 查找当前日志文件 500 错误的数量： 

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}' | wc -l
* 查找某一分钟内 500 错误访问的数量: 

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}' | grep '09:00' | wc-l
* 查找耗时超过 1s 的慢请求： 

    tail -f access.log | awk -F '^A' '{if($6>1) print $0}'
* 假如只想查看某些字段的值： 

    tail -f access.log | awk -F '^A' '{if($6>1) print $3"|"$4}'
* 查找 502 错误最多的 URL： 

    cat access.log | awk -F '^A' '{if($5==502) print $11}' | sort | uniq -c
* 查找 200 空白页 

    cat access.log | awk -F '^A' '{if($5==200 && $8 < 100) print $3"|"$4"|"$11"|"$6}'
* 查看实时日志数据流

    tail -f access.log | cat -e

或者

    tail -f access.log | tr '^A' '|'

AWK的深入使用方法，请参考 [The GNU Awk User’s Guide][14] 。 

## 参考资料

* [Core functionality][15]
* [Module ngx_http_log_module][16]
* [Module ngx_http_rewrite_module][17]
* [A debugging log][6]
* [Logging to syslog][18]
* [CONFIGURING LOGGING][19]
* [Log Rotation][8]
* [How To Configure Logging and Log Rotation in Nginx on an Ubuntu VPS][20]
* [Configuring the Nginx Error Log and Access Log][5]
* [纯手工玩转 Nginx 日志][12]
* [nginx access_log日志][21]
* [NGINX:日志配置][22]
* [nginx日志配置][7]
* [nginx日志格式说明][23]
* [nginx日志格式及自定义日志配置][24]
* [nginx（四）初识nginx日志文件][25]
* [nginx error_log 错误日志配置说明][26]
* [nginx error_log 错误日志配置说明][27]
* [logrotate轮询nginx日志][28]
* [使用logrotate高效切割轮询管理nginx日志文件][29]
* [切割nginx日志并删除指定天数前的日志记录][30]
* [使用logrotate轮询nginx和apache日志][31]
* [logrotate无法自动轮询日志的原因][32]
* [烂泥：切割nginx日志][33]
* [被遗忘的Logrotate][34]
* [使用logrotate管理nginx日志文件][35]

[0]: /sites/7ji2uem
[1]: http://www.jackieathome.net/archives/431.html?utm_source=tuicool&utm_medium=referral
[2]: /topics/11090014
[3]: /topics/11200019
[4]: https://zh.wikipedia.org/wiki/Syslog
[5]: https://www.keycdn.com/support/nginx-error-log/
[6]: http://nginx.org/en/docs/debugging_log.html
[7]: http://www.ttlsa.com/linux/the-nginx-log-configuration/
[8]: https://www.nginx.com/resources/wiki/start/topics/examples/logrotation/
[9]: http://article.gmane.org/gmane.comp.web.nginx.english/583
[10]: http://article.gmane.org/gmane.comp.web.nginx.english/181
[11]: http://www.linuxcommand.org/man_pages/logrotate8.html
[12]: https://blog.eood.cn/nginx_logs
[13]: https://zh.wikipedia.org/zh-cn/Awk
[14]: https://www.gnu.org/software/gawk/manual/gawk.html
[15]: http://nginx.org/en/docs/ngx_core_module.html
[16]: http://nginx.org/en/docs/http/ngx_http_log_module.html
[17]: http://nginx.org/en/docs/http/ngx_http_rewrite_module.html
[18]: http://nginx.org/en/docs/syslog.html
[19]: https://www.nginx.com/resources/admin-guide/logging-and-monitoring/
[20]: https://www.digitalocean.com/community/tutorials/how-to-configure-logging-and-log-rotation-in-nginx-on-an-ubuntu-vps
[21]: https://lanjingling.github.io/2016/03/14/nginx-access-log/
[22]: http://who0168.blog.51cto.com/253401/569615
[23]: http://linux008.blog.51cto.com/2837805/595749
[24]: http://blog.chinaunix.net/uid-29179844-id-4433640.html
[25]: http://summervast.blog.51cto.com/690507/386455
[26]: http://www.cnblogs.com/wicub/p/6203261.html
[27]: https://my.oschina.net/u/205403/blog/142631
[28]: https://blog.linuxeye.com/313.html
[29]: https://www.cnhzz.com/logrotate-nginx/
[30]: https://www.cnhzz.com/nginx-logs/
[31]: https://www.centos.bz/2011/12/logrotate-nginx-log/
[32]: https://www.centos.bz/2011/12/logrotate-can-not-auto-cut-logfile/
[33]: http://www.ilanni.com/?p=11150
[34]: http://huoding.com/2013/04/21/246
[35]: http://linux008.blog.51cto.com/2837805/555829/
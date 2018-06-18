## Linux 下配置滚动日志之 logrotate

来源：[https://unmi.cc/linux-config-log-ratation-logrotate/](https://unmi.cc/linux-config-log-ratation-logrotate/)

时间 2018-06-01 15:07:41


日志是个好东西，便于定位历史问题，但记录太多，不滚动，不除旧总暴盘的时候。如果是用日志框架输出的日志，像 Log4j 或 Logback 通过选择具有滚动特性的 Appender 就能实现日志的滚动，并删除旧的归档日志文件。但也有在程序当中难以控制的日志输出文件，这用的话必须采取事后补救措施，程序尽管往一个日志文件里写，由另一个程序来对该日志文件进行归档，清理操作。

与此相关的工具，我们可以找到以下几个



* [logrotate][0]
, 如今的多数 Linux 发布版都自带了，感觉有一种主场优势。github 上      [logrotate/logrotate][1]
仍活跃着    
* [newsyslog][2]
,  FreeBSD 和  Mac 系统自带，应该不常用。Mac OS 下可以看下配置文件`/etc/newsyslog.conf`
* [cronolog][3]
, 原本的官网 www.cronolog.org 全是日文了，找到它的快照      [fleible web log rotation][4]
, github 上      [fordmason/cronolog][5]
 最近更新是五年前    
* [rotatelogs][6]
, 出自于 Apache HTTP 项目, Apache HTTP server 用它滚动访问和错误日志    
  

本人最为推崇使用第一个工具`logrotate`, 因为多数 Linux 系统自带，不像 cronolog 和 rotatelogs 需要额外安装。它也有着更完备的功能，下面慢慢领略


### logrotate 的工作机制

Linux 下默认有一个每日执行的 Cron Job，配置在`/etc/cron.daily/logrotate`，文件内容为(以 centos7 为例)

```
#!/bin/sh
 
/usr/sbin/logrotate -s /var/lib/logrotate/logrotate.status /etc/logrotate.conf
EXITVALUE=$?
if [ $EXITVALUE != 0 ]; then
    /usr/bin/logger -t logrotate "ALERT exited abnormally with [$EXITVALUE]"
fi
exit 0


```

上面的意思是说，Linux 每日使用配置文件`/etc/logrotate.conf`执行命令`logrotate`, 并且执行的状态写在`/var/lib/logrotate/logrotate.status`文件中。我们可以查看该状态记录文件以确认 logrotate 的实际行为。
`logrotate`本身默认执行间隔都是每日一次，所以即使在自己配置中用了`hourly`也是没用的，除非我们把上面的`logrotate`从`/etc/cron.daily`移入到`/etc/cron.hourly`目录中去。

在 Ubuntu 中的`/etc/cron.daily/logrotate`也类似，总之都是要应用`/etc/logrotate.conf`配置文件。该配置文件主要内容参考如下

``` 
#以下五行是日志滚动的全局默认配置
weekly #默认每周一个日志归档
rotate 4 #最多保存 4 个归档
create #日志滚动后创建一个新的日志文件
dateext #归档文件名加上日期后缀
#compress #归档文件是否启用压缩
 
# 包含 /etc/logrotate.d/ 目录中的所有配置文件
include /etc/logrotate.d
 
# 这是一个指定日志文件归档配置样例
/var/log/wtmp {
    monthly
    create 0664 root utmp
    minsize 1M
    rotate 1
}
 
....


```

所以我们希望对某个日志文件进行自动归档，配置可以直接写在`/etc/logrotate.conf`文件中，像`/var/log/wtmp {...`那段一样，也可以在目录`/etc/logrotate.conf`中创建一个单独的配置文件，强烈建议采用后者。


### 创建自己的 logrotate 配置

配置文件可以参考`/etc/logrotate.d`目录中的几个现实例子，在`Centos7`下可以看到 bootlog, chrony, syslog, wpa_supplicant, yum 这样的配置文件。完整配置说明请参照    [logrotate(8) - Linux man page][7]
。

假如，我们需要对 httpd 的访问日志进行滚动，可以在`/etc/logrotate.d/`目录中创建文件`httpd_access_log`, 内容放上

``` 
/var/log/httpd/access.log {
    rotate 5
    size 20M
    compress
    copytruncate
    dateext
    sharedscripts
    postrotate
        /usr/bin/killall -HUP httpd
    endscript
}


```

上面的配置可以在每一天，如果日志文件`/var/log/httpd/access.log`达到 20 M 的话，就会进行归档生成

``` 
/var/log/httpd/access-20180601.gz


```

并且把原日志文件`/var/log/httpd/access.log`清空，还给 httpd 进程发送一个 HUP 信号。

最多保留有 5 个归档日志文件，如果上面没有`dateext`配置项的话，生成的归档文件将会是`access.1.gz`,`access.2.gz`这样的文件名。

配置文件中更多配置选项还是请参考    [logrotate(8) - Linux man page][7]
，这里不具体解释它的选项，只简单举例说明一下借助配置项可以实现什么



* 可以在归档的时候发送邮件
* 可以设定归档文件后缀中的日期格式
* 归档日志可存储到别的目录中，默认存在同一目录中
* 归档前后都可以执行自定义的脚本
* 匹配多个日志文件是可指定脚本是针对每个日志文件触发，还是只触发一次
  

文件`/etc/logrotate.d/httpd_access_log`创建好了静静的躺在哪儿即可， **`不需要作任何的服务重启操作，第二天就可以看到结果`** ，实在是等不及的话就用`date`命令修改系统时间，需谨慎，别造成运行中程序日期错乱。


### 调试配置文件

要用到`logrotate`命令了，可以`logrotate /etc/logrotate.conf`, 这会触发到所有所有的日志滚动操作，应该不是我们想要的，所以应该简单的

``` 
logrotate -d -f /etc/logrotate.d/httpd_access_log


```

加了一个 -d (--debug), 相当于干运行，能看到下面的模拟操作输出

``` 
$ logrotate -d -f /etc/logrotate.d/httpd_access_log
  reading config file /etc/logrotate.d/httpd_access_log  Allocating hash table for state file, size 15360 B
Handling 1 logs
rotating pattern: /var/log/httpd/access.log forced from command line (5 rotations)  empty log files are rotated, old logs are removed  considering log /var/log/httpd/access.log      log needs rotating  rotating log /var/log/httpd/access.log, log->rotateCount is 5  dateext suffix '-20180601'  glob pattern '-[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'  glob finding old rotated logs failed  copying /var/log/httpd/access.log to /var/log/httpd/access.log-20180601  truncating /var/log/httpd/access.log  compressing log with: /bin/gzip

```

如果想看到它的实际效果的话就把其中的`-d`去掉

``` 
$ logrotate -f /etc/logrotate.d/httpd_access_log


```

注意，前面加上了一个`-f`(--force), 就是把不管日志文件大小是否符合要求，强制执行日志滚动操作

激进一点的话，还可以修改系统时间来触发 Cron Job 中`logrotate`的执行，比如今天是 2018-06-02, 改成 2 号

``` 
$ sudo date -s 2018-06-02


```

过一会就可以去查看日志文件是否滚动了，或是 logrotate 本身的状态日志`/var/lib/logrotate/logrotate.status`。


### 多日志文件与通配符

一个配置条目`日志文件 { 配置项 }`不仅仅支持一个日志文件，可以配置多个文件或使用通配符，如

``` 
/var/log/httpd/access.log /var/log/httpd/error.log {
....
}
 
# 或
/var/log/httpd/access.log
/var/log/httpd/error.log {
...
}


```

通配符的形式

``` 
/var/log/news/* {
...
}
 
/var/log/news/*.log {
...
}
 
/var/log/*/stdout.log {
...
}
 
/var/log/*/*.log {
...
}


```

不仅文件名处可以用通配符，目录处也能用通配符。例如最后那个配置`/var/log/*/*.log {...}`将会对`/var/log/`下所有目录中的`*.log`文件产生效果。比如下面那样的文件

``` 
/var/log/aa/x.log  /var/log/bb/y.log  /var/log/cc/z.log


```

针对多个日志文件时，归档文件也会生成在相应日志所在目录中。有了多日志文件与通配符的支持，能够通过一个配置对系统中众多日志文件采取一致的行动。



[0]: https://linux.die.net/man/8/logrotate
[1]: https://github.com/logrotate/logrotate
[2]: https://www.newsyslog.org/manual.html
[3]: http://oldzipsarchive.dreamhosters.com/apache/log_rotation/cronolog_faq.htm
[4]: http://oldzipsarchive.dreamhosters.com/apache/log_rotation/cronolog_faq.htm
[5]: https://github.com/fordmason/cronolog
[6]: https://httpd.apache.org/docs/2.4/programs/rotatelogs.html
[7]: https://linux.die.net/man/8/logrotate
[8]: https://linux.die.net/man/8/logrotate
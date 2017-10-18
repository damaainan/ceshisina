# logrotate命令

**logrotate命令**用于对系统日志进行轮转、压缩和删除，也可以将日志发送到指定邮箱。使用logrotate指令，可让你轻松管理系统所产生的记录文件。每个记录文件都可被设置成每日，每周或每月处理，也能在文件太大时立即处理。您必须自行编辑，指定配置文件，预设的配置文件存放在`/etc/logrotate.conf`文件中。 

### 语法  
    logrotate(选项)(参数)

### 选项  
    -?或--help：在线帮助；
    -d或--debug：详细显示指令执行过程，便于排错或了解程序执行的情况；
    -f或--force ：强行启动记录文件维护操作，纵使logrotate指令认为没有需要亦然；
    -s<状态文件>或--state=<状态文件>：使用指定的状态文件；
    -v或--version：显示指令执行过程；
    -usage：显示指令基本用法。

### 参数  
配置文件：指定lograote指令的配置文件。

----

# 使用logrotate来切割日志文件

 时间 2017-04-22 15:02:38  Wuyuan's Blog

原文[https://wuyuans.com/2017/04/logrotate-usage][1]

程序在运行的时候为了了解运行状态，会输出日志文件，时间久了日志文件会变得非常大，甚至达到GB级别。我在golang应用里使用logrus包来打日志，配置和使用都很方便，就是没有日志分割的功能，应用在线上运行一个月后日志文件都已经达到上百兆。后来发现了logrotate，这是centos自带的日志分割工具，都不用安装额外组件就能实现定时分割日志。

## 1.运行原理 

logrotate由系统的cron运行，位置在`/etc/cron.daily/logrotate`


```bash
    #!/bin/sh
    
    /usr/sbin/logrotate -s /var/lib/logrotate/logrotate.status /etc/logrotate.conf
    EXITVALUE=$?
    if [ $EXITVALUE != 0 ]; then
        /usr/bin/logger -t logrotate "ALERT exited abnormally with [$EXITVALUE]"
    fi
    exit 0
```

可以看到入口配置文件是/etc/logrotate.conf，依次运行/etc/logrotate.conf.d里的配置文件 _如果发现配置的logrotate没有执行，可以看下系统的crond服务有没有开启_

如果有安装nginx，可以参考nginx里的配置例子

    /var/log/nginx/*log {
        create 0644 nginx nginx
        daily
        rotate 10
        missingok
        notifempty
        compress
        sharedscripts
        postrotate
            /bin/kill -USR1 `cat /run/nginx.pid 2>/dev/null` 2>/dev/null || true
        endscript
    }

第一行定义的是日志文件的路径，可以用*通配，一般可以定义成`*.log`来匹配所有日志文件。也可以指定多个文件，用空格隔开，比如

    /var/log/nginx/access.log /var/log/nginx/error.log {
     
    }

花括号里面是日志切割相关的参数，下面是常用的切割参数

* compress 是否开启压缩，压缩格式gzip
* 不开启压缩
* compresscmd 自定义压缩命令
* compressexty 压缩文件名后缀
* compressoptions 压缩选项
* copy 复制一份文件
* create 后面跟mode owner group，设置新日志文件的权限
* daily 按天分割
* weekly 按周分割
* monthly 按月分割
* rotate 后面跟数字，表示需要保留的文件历史记录，超过数量就会删除，或者通过邮件发送
* size 后面跟文件大小，比如100k、100M，超过这个大小后分割
* missingok 忽略不存在的文件，不报错
* notifempty 不分割空文件
* sharedscripts 配合postrotate、prerotate，让他们只执行一次
* postrotate/endscript 文件分割完后，执行postrotate、endscript之间的命令
* prerotate/endscript 文件分割完前，执行prerotate、endscript之间的命令

下面看几个例子

    /var/log/httpd/error.log {
        rotate 5
        mail i@wuyuans.com
        size=100k
        sharedscripts
        postrotate
            /sbin/killall -HUP httpd
        endscript
    }

切割/var/log/httpd/error.log日志文件，超过100k后切割，保留最新的5个历史记录，超过5个的邮件发送到i@wuyuans.com，postrotate里的的命令是为了让httpd重新打开日志文件。

    /var/lib/mysql/mysqld.log {
        # create 600 mysql mysql
        notifempty
     daily
        rotate 3
        missingok
        compress
        postrotate
     # just if mysqld is really running
     if test -x /usr/bin/mysqladmin && \
        /usr/bin/mysqladmin ping &>/dev/null
     then
        /usr/bin/mysqladmin --local flush-error-log \
                  flush-engine-log flush-general-log flush-slow-log
     fi
        endscript
    }

这是对mysql日志的切割，每天一份，忽略空文件，保留最新3份，使用gzip压缩

    /home/wuyuan/log/*.log {
        su wuyuan wuyuan
        create 0777 wuyuan wuyuan
        daily
        rotate 10
        olddir /home/wuyuan/log/old
        missingok
        postrotate
        endscript
        nocompress
    }

这是我在用的配置项，对log目录所有.log文件切割，每天一份，保留10份，新文件设定权限777，历史文件保留在old目录里，这样可以方便查看。因为应用程序用的logrus使用append的方式写日志，所以不需要重新打开日志文件，这点logrus做得很不错。

写完配置文件后可以手动执行下，来验证是否可用。

    logrotate -f /etc/logrotate.d/wuyuan

其中-f 表示强制执行，其他命令可以用help来查看

    logrotate --help
    用法: logrotate [OPTION...] <configfile>
      -d, --debug Don't do anything, just test (implies -v)
      -f, --force Force file rotation
      -m, --mail=command Command to send mail (instead of `/bin/mail')
      -s, --state=statefile Path of state file
      -v, --verbose Display messages during rotation
      -l, --log=STRING Log file
      --version Display version information
    
    Help options:
      -?, --help Show this help message
      --usage Display brief usage message

没问题的话日志就会被移到old目录下，并带上日期，之前的log文件会被清空

[1]: https://wuyuans.com/2017/04/logrotate-usage


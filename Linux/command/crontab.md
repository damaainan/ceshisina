# Crontab 指南 


Crontab 是 Unix/Linux 中用于设置周期执行指令的命令。如果我们需要定期执行某些任务，除了让任务常驻外，更方便的方法是让 crontab 来帮助我们调度执行。

- - -

更新记录

* 2016.07.26: 初稿

## 简介

cron 是 Unix/Linux 中提供定期执行 shell 命令的服务，包括 crond 和 crontab 两部分：

* crond: cron 服务的守护进程，常驻内存负责定期调度
* crontab: cron 的管理工具，负责编辑调度计划

下面的演示在 Ubuntu 16.04 下进行。基本的使用方法可以用命令 man crontab 查看

    

    NAME
           crontab - maintain crontab files for individual users (Vixie Cron)
    
    SYNOPSIS
           crontab [ -u user ] file
           crontab [ -u user ] [ -i ] { -e | -l | -r }

简单解释一下

* -e 编辑，类似 vim，保存退出时会检查语法
* -l 列举所有任务
* -r 删除所有任务

如果 crontab 运行出错，可以查看日志文件/var/log/syslog

## 基本语法

cron 的语法非常简单，一共分六大块，其中前五块用于指定时间周期，最后一块是具体执行的命令，看起来大概是这么个格式：

**min hour day month week command**其中

* min 表示分钟，范围 0-59
* hour 表示小时，范围 0-23
* day 表示天，范围 1-31
  * 可以填写 L，表示当月最后一天
  * 可以填写 W，1W 表示离 1 号最近的工作日
* month 表示月，范围 1-12
  * 每个月的最后一天 crontab 本身是不支持的，需要通过脚本判断
* week 表示周，范围 0-7
  * 这里 0 和 7 都表示周日
  * 周与日月不能并存，可能会冲突
  * 可以填写 #，4#3 表示当月第三个星期四
  * 可以填写 L，5L 表示当月最后一个星期五
* command 表示具体要执行的命令（最好是绝对路径）
  * 如果有多条命令，则需要用&连接，或者将多条命令写在shell脚本中，然后crontab定期执行这个shell脚本即可

另外，类似正则表达式，还有一些特殊符号帮助我们实现灵活调度

* * 星号，表示每个可能的值都接受
  * 例如 * * * * * command 表示每分钟都执行 command 一次
* , 逗号，并列时间
  * 例如 * 6,12,18 * * * command 表示在 6 点、12 点和 18 点执行 command 一次
* - 减号，连续区间
  * 例如 * 9-17 * * * command 表示从 9 点到 17 点，每分钟都执行 command 一次
* / 斜线，间隔单位
  * 例如 */5 * * * * command 表示每隔 5 分钟执行 command 一次

## 系统级 Crontab

如果我们需要执行一些权限较高的指令，就需要利用 root 权限来执行，这时的机制和前面介绍的基本语法也是有区别的，我们需要编辑的文件是 /etc/crontab。先来看看其内容 

    

    dawang@dawang-Parallels-Virtual-Platform:~$ cat /etc/crontab
    # /etc/crontab: system-wide crontab
    # Unlike any other crontab you don't have to run the `crontab'
    # command to install the new version when you edit this file
    # and files in /etc/cron.d. These files also have username fields,
    # that none of the other crontabs do.
    
    SHELL=/bin/sh
    PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
    
    # m h dom mon dow user  command
    17 *    * * *   root    cd / && run-parts --report /etc/cron.hourly
    25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
    47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
    52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
    #

我们需要在命令和时间间隔之间添加命令执行者，并且也可以添加环境变量在调度中使用。我们看到配置文件中有几个 cron.* 文件，先来看看还有什么类似的文件

    

    dawang@dawang-Parallels-Virtual-Platform:~$ ll /etc | grep cron
    -rw-r--r--   1 root root     401 12月 29  2014 anacrontab
    drwxr-xr-x   2 root root    4096 4月  21 06:14 cron.d/
    drwxr-xr-x   2 root root    4096 4月  21 06:14 cron.daily/
    drwxr-xr-x   2 root root    4096 4月  21 06:08 cron.hourly/
    drwxr-xr-x   2 root root    4096 4月  21 06:14 cron.monthly/
    -rw-r--r--   1 root root     722 4月   6 05:59 crontab
    drwxr-xr-x   2 root root    4096 4月  21 06:14 cron.weekly/

其中

* cron.d 目录：该目录下及子目录中所有符合调度语法的文件都会被执行
* cron.deny：记录拒绝执行的用户
* cron.allow：记录允许执行的用户，这个文件的优先级较高，一般来说只需要配置一个文件即可（看是需要白名单还是黑名单机制）
* cron.daily/hourly/monthly/weekly 目录：里面都是脚本，分别在指定的时间里执行

更多详细介绍，可以输入 man 5 crontab 或 man 8 cron 查阅

## 原理

为什么我们用 crontab -e 编辑一下就可以添加一个定时任务呢？每次我们添加一行，这个工作就会被记录到 /var/spool/cron/crontab 中，如果我的用户名是 dawang，那么对应的文件就是 /var/spool/cron/crontab/dawang（需要 root 权限才能查看）。不过不建议直接修改，因为直接修改是不会进行语法检查的。

在某些系统中，不一定会每次都读取源配置文件（而是利用载入到内存的版本），这个时候我们就需要重启 crond 服务，命令为 /sbin/service crond restart## Crond 服务管理

默认情况系统并没有为我们启动 crond 服务，如果想开机启动，需要在 /etc/rc.d/rc.local 中添加 service crond start 这一行，其他的管理命令为 

    

# 启动服务

/sbin/service crond start 

# 关闭服务

/sbin/service crond stop 

# 重启服务

/sbin/service crond restart 

# 重新载入配置

/sbin/service crond reload

## 实例测试

接着我们来实战一下，第一次使用 crontab -e 需要我们选择编辑器，默认是 nano，但是我选择了 vim

    

    dawang@dawang-Parallels-Virtual-Platform:~$ crontab -e
    no crontab for dawang - using an empty one
    
    Select an editor.  To change later, run 'select-editor'.
      1. /bin/ed
      2. /bin/nano        <---- easiest
      3. /usr/bin/vim.tiny
    
    Choose 1-3 [2]:

为了验证真的在执行，我们建立两个每分钟都执行的操作，具体如下（主要关注最后两行）：

    

    # Edit this file to introduce tasks to be run by cron.
    #
    # Each task to run has to be defined through a single line
    # indicating with different fields when the task will be run
    # and what command to run for the task
    #
    # To define the time you can provide concrete values for
    # minute (m), hour (h), day of month (dom), month (mon),
    # and day of week (dow) or use '*' in these fields (for 'any').#
    # Notice that tasks will be started based on the cron's system
    # daemon's notion of time and timezones.
    #
    # Output of the crontab jobs (including errors) is sent through
    # email to the user the crontab file belongs to (unless redirected).
    #
    # For example, you can run a backup of all your user accounts
    # at 5 a.m every week with:
    # 0 5 * * 1 tar -zcf /var/backups/home.tgz /home/
    #
    # For more information see the manual pages of crontab(5) and cron(8)
    #
    # m h  dom mon dow   command
    * * * * * date >> /home/dawang/date.txt
    * * * * * echo "time to go!" >> /home/dawang/time.txt

这里做了两件事，一个是每分钟报时，另一个就是每分钟输出一段话，这里使用 >> 表示追加输出，更多输入输出方式在下一节有介绍。如果刚才没有启动服务，现在用 service crond start 启动，然后等待一段时间，就可以看到输出啦，具体参考下面的命令，这里就不赘述了：

    

    dawang@dawang-Parallels-Virtual-Platform:~$ ll | grep txt
    -rw-rw-r--   1 dawang dawang   1849 7月  26 16:08 date.txt
    -rw-rw-r--   1 dawang dawang    516 7月  26 16:08 time.txt
    dawang@dawang-Parallels-Virtual-Platform:~$ tail -n 10 date.txt
    2016年 07月 26日 星期二 16:01:01 CST
    2016年 07月 26日 星期二 16:02:01 CST
    2016年 07月 26日 星期二 16:03:01 CST
    2016年 07月 26日 星期二 16:04:01 CST
    2016年 07月 26日 星期二 16:05:01 CST
    2016年 07月 26日 星期二 16:06:01 CST
    2016年 07月 26日 星期二 16:07:01 CST
    2016年 07月 26日 星期二 16:08:01 CST
    2016年 07月 26日 星期二 16:09:01 CST
    2016年 07月 26日 星期二 16:10:01 CST
    dawang@dawang-Parallels-Virtual-Platform:~$ tail -n 10 time.txt 
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!
    time to go!

## 重定向命令

这里直接给出例子

    

command > file 把标准输出重定向到文件

command >> file 把标准输出追加到文件

command 1 > file 把标准输出重定向到文件

command 2 > file 把标准错误重定向到文件

command 2 >> file 把标准输出追加到文件

command 2>&1 把command命令标准错误重定向到标准输出

command > file 2>&1 把标准输出和标准错误一起重定向到文件

command >> file 2>&1 把标准输出和标准错误一起追加到文件

command < file 把command命令以file文件作为标准输入

command < file >file2 把command命令以file文件作为标准输入，以file2文件作为标准输出

command <&- 关闭标准输入

## 参考链接

* [crontab使用入门][2]
* [判断一个月最后一天][3]
* [crontab实用手册][4]
* [Crontab 使用入门][5]


[2]: http://www.cnblogs.com/bourneli/archive/2012/04/14/2446944.html
[3]: http://backreference.org/2010/04/05/last-day-of-month-cron-job/
[4]: http://www.cnblogs.com/ggjucheng/archive/2012/08/19/2646763.html
[5]: http://liluo.org/blog/2012/07/how-to-use-crontab/
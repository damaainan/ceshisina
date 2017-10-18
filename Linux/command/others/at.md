# Linux at command

 [首页][0]  [分类][1]  [标签][2]  [留言][3]  [关于][4]  [订阅][5]  2016-09-29 | 分类 [linux][6] | 标签 [linux][7] 

### 前言

at 命令是个非常有用的命令，如果你指定某程序在将来的某个时间点执行，at是个不错的工具。本文介绍at的用法

### 用法

典型的用法是：

    echo command ｜at  sometime
    

其中command 是你要执行的命令，sometime是将来的某个时间。

比如：

    echo "ls > /tmp/future" |at 10am October 2                                                                 1 ↵
    job 1 at Sun Oct  2 10:00:00 2016
    

另外支持这种格式：

    echo "ls /tmp >> /tmp/future" |at 10:25 10/03/2016
    job 2 at Mon Oct  3 10:25:00 2016
    
    
    echo "ls /root >> /tmp/future" |at 10:40 03.10.2016
    job 3 at Mon Oct  3 10:40:00 2016
    

也就是说年月日可以用如下方式：

(MM 表示月份， DD表示天，YYYY表示年)

* MM/DD/YYYY
* MMDDYYYY
* DD.MM.YYYY

一天中的时刻支持如下方式：

* HHMM
* HH:MM

其中还有一种方式比较有意思，now ＋ count ［time units］，

    echo command ｜ at now + 40 minutes 
    

表示40分钟后运行.

注意，严格来讲，40分钟后是不精确的，因为at 命令精确到分钟，在分钟的00秒开始执行。如下所示：

    LiBeandeiMac ezgateway/etc ‹virtualstor-6.1*› » date ; echo "ls /root > /tmp/future" |at now + 40 minutes
    2016年 9月29日 星期四 21时20分57秒 CST
    job 4 at Thu Sep 29 22:00:00 2016
    

我们可以看到当前时间是21点20分57秒，40分钟后应该是22:00:57秒，但是，任务是22:00:00秒执行。

### 展示任务内容

我们可以通过at -l命令列出当前的任务，

    at -l
    5   Mon Sep 26 18:13:00 2016 a root
    

对于我们关心的任务，比如上面的5号任务，我们可以列出任务的详细内容,命令是

    at -c job_num
    

如下所示：

    root@node-157:/var/log/ezcloudstor# at -c 5
    #!/bin/sh
    # atrun uid=0 gid=0
    # mail root 0
    umask 0
    UPSTART_INSTANCE=; export UPSTART_INSTANCE
    runlevel=2; export runlevel
    UPSTART_JOB=rc; export UPSTART_JOB
    PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/bin:/usr/local/sbin; export PATH
    RUNLEVEL=2; export RUNLEVEL
    PREVLEVEL=N; export PREVLEVEL
    HA_logfacility=none; export HA_logfacility
    UPSTART_EVENTS=runlevel; export UPSTART_EVENTS
    HA_debug=0; export HA_debug
    PWD=/; export PWD
    previous=N; export previous
    LC_ALL=en_US.UTF-8; export LC_ALL
    VERBOSE=no; export VERBOSE
    cd / || {
         echo 'Execution directory inaccessible' >&2
         exit 1
    }
    python /usr/local/bin/delay_delte_volume.py -G Default -T iqn.2015-01.com:1 -V delete_test_5 
    

### 删除任务

at -l 会给出job id，通过atrm命令可以删除某条任务,如下所示：

    at -l
    1   Sun Oct  2 10:00:00 2016
    2   Mon Oct  3 10:25:00 2016
    3   Mon Oct  3 10:40:00 2016
    4   Thu Sep 29 22:00:00 2016
    
    atrm 3
    
    at -l
    1   Sun Oct  2 10:00:00 2016
    2   Mon Oct  3 10:25:00 2016
    4   Thu Sep 29 22:00:00 2016
    

### 关于重启

如果at command设定的任务，在任务指定时间到来之前，发生了重启，那么重启后，at command 指定的时间到来的时候，命令会不会执行？

答案是会执行。

at 把将来要执行的命令持久化了，写入了硬盘，如果中间发生了重启，at依然可以找到尚未执行的命令，在约定的时间到来之后，继续执行。

这个属性是非常关键的，如果没有这条性质，我们就无法放心的使用at command，因为约定的命令可以无法执行。

### 关于持久化：

at创建的任务存放到哪里了呢？

我们通过试验来查看：

    root@BEAN-0:/var/spool/cron/atjobs# echo "echo haha >> future" |at  now + 2 minute
    warning: commands will be executed using /bin/sh
    job 10 at Thu Sep 29 18:15:00 2016
    root@BEAN-0:/var/spool/cron/atjobs# at -l
    10  Thu Sep 29 18:15:00 2016 a root
    root@BEAN-0:/var/spool/cron/atjobs# ll
    total 20
    drwxrwx--T 2 daemon daemon 4096 Sep 29 18:13 ./
    drwxr-xr-x 5 root   root   4096 Jun 14 16:09 ../
    -rwx------ 1 root   daemon 2333 Sep 29 18:13 a0000a01772607*
    -rw-r--r-- 1 root   root      5 Sep 29 18:11 future
    -rw------- 1 daemon daemon    6 Sep 29 18:13 .SEQ
    

对于Ubuntu而言， /var/spool/cron/atjobs 是存放任务的文件夹。下面有一个文件a0000a01772607， 这个文件是何意？

* 0 char， 表示queue id
* 1～5 16进制，表示job id
* 6～13 16 进制，等于 expect_time/60

我们取出来最后8位：

    root@BEAN-0:/var/log# printf %d 0x1772607
    24585735root@BEAN-0:/var/log# 
    root@BEAN-0:/var/log# echo 24585735*60 |bc
    1475144100
    root@BEAN-0:/var/log# date +%s
    1475144154
    root@BEAN-0:/var/log# date -d @1475144100
    Thu Sep 29 18:15:00 CST 2016
    

注意，将最后8位16进制转换成10进制，值为24585735。将24585735乘以60，即为任务应该执行的时间，timestamp为1475144154，转换成可读的时间，为：

    Thu Sep 29 18:15:00 CST 2016
    

### 其他

如果要执行的任务，写在了脚本里，可以采用如下方式：

    at 12:32  -f /usr/local/bin/backup-script
    

即 12:32分，执行 /usr/local/bin/backup-script脚本。

[0]: http://bean-li.github.io/
[1]: http://bean-li.github.io/categories/
[2]: http://bean-li.github.io/tags/
[3]: http://bean-li.github.io/guestbook/
[4]: http://bean-li.github.io/about/
[5]: http://bean-li.github.io/feed/
[6]: http://bean-li.github.io/categories/#linux
[7]: http://bean-li.github.io/tags/#linux
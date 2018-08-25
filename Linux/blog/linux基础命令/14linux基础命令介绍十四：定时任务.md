## linux基础命令介绍十四：定时任务

来源：[https://segmentfault.com/a/1190000007916299](https://segmentfault.com/a/1190000007916299)

在计算机的使用过程中，经常会有一些计划中的任务需要在将来的某个时间执行，linux中提供了一些方法来设定`定时任务`。

### 1、`at`命令`at`从文件或标准输入中读取命令并在将来的一个时间执行，只执行一次。`at`的正常执行需要有守护进程`atd`(关于systemctl请看[这一篇][0])：

```sh
#安装at
yum install -y at 或 apt-get install at -y
#启动守护进程
service atd start 或 systemctl start atd
#查看是否开机启动
chkconfig --list|grep atd 或 systemctl list-unit-files|grep atd
#设置开机启动
chkconfig --level 235 atd on 或 systemctl enable atd
```

如果不使用管道`|`或指定选项`-f`的话，`at`的执行将会是交互式的，需要在at的提示符下输入命令：

```
[root@centos7 temp]# at now +2 minutes #执行at并指定执行时刻为现在时间的后两分钟
at> echo hello world > /root/temp/file #手动输入命令并回车
at> <EOT>                              #ctrl+d 结束输入
job 9 at Thu Dec 22 14:05:00 2016      #显示任务号及执行时间
[root@centos7 temp]# 
```

选项`-l`或命令`atq`查询任务

```
[root@centos7 temp]# atq
9       Thu Dec 22 14:05:00 2016 a root
```

到达时间后任务被执行，生成一个新文件file并保存echo的输出内容

```
[root@centos7 temp]# ls -l file 
-rw-r--r-- 1 root root 12 12月 22 14:05 file
[root@centos7 temp]# cat file 
hello world
[root@centos7 temp]# 
```
`at`指定时间的方法很丰富，可以是   
1）`hh:mm`小时:分钟(当天，如果时间已过，则在第二天执行)  
2）`midnight`(深夜),`noon`(中午),`teatime`(下午茶时间，下午4点),`today`,`tomorrow`等  
3）12小时计时制，时间后加`am`(上午)或`pm`(下午)  
4）指定具体执行日期`mm/dd/yy`(月/日/年)或`dd.mm.yy`(日.月.年)  
5）相对计时法`now + n units`，now是现在时刻，n为数字，units是单位(minutes、hours、days、weeks)  
如明天下午2点20分执行创建一个目录  

```
[root@centos7 temp]# at 02:20pm tomorrow
at> mkdir /root/temp/X
at> <EOT>
job 11 at Fri Dec 23 14:20:00 2016
```

选项`-d`或命令`atrm`表示删除任务

```
[root@centos7 temp]# at -d 11 #删除11号任务(上例)
[root@centos7 temp]# atq
[root@centos7 temp]# 
```

可以使用管道`|`或选项`-f`让`at`从标准输入或文件中获得任务

```
[root@centos7 temp]# cat test.txt 
echo hello world > /root/temp/file
[root@centos7 temp]# at -f test.txt 5pm +2 days
job 12 at Sat Dec 24 17:00:00 2016
[root@centos7 temp]# cat test.txt|at 16:20 12/23/16
job 13 at Fri Dec 23 16:20:00 2016
```

`atd`通过两个文件`/etc/at.allow`和`/etc/at.deny`来决定系统中哪些用户可以使用`at`设置定时任务，它首先检查`/etc/at.allow`，如果文件存在，则只有文件中列出的用户(每行一个用户名)，才能使用at；如果不存在，则检查文件`/etc/at.deny`，不在此文件中的所有用户都可以使用at。如果`/etc/at.deny`是空文件，则表示系统中所有用户都可以使用at；如果`/etc/at.deny`文件也不存在，则只有超级用户(root)才能使用at。

### 2、`crontab`命令`crontab`用来设置、移除、列出服务`crond`表格，`crond`服务的作用类似`atd`，区别的地方在于`crond`可以设置任务多次执行。相对来说比`atd`更常用。

同样需要启动服务`crond`

```
[root@centos7 temp]# ps -ef|grep [c]rond
root       733     1  0 12月20 ?      00:00:00 /usr/sbin/crond -n
```

系统中每个用户都可以拥有自己的`cron table`，同`atd`类似，`crond`也有两个文件`/etc/cron.allow`和`/etc/cron.deny`用来限制用户使用cron，规则也和`atd`的两个文件相同。

选项`-l`表示列出当前用户的cron表项  
选项`-u`表示指定用户  

```
[root@centos7 ~]# crontab -l -u learner
no crontab for learner
[root@centos7 ~]#
```

选项`-e`表示编辑用户的cron table。编辑时系统会选定默认编辑器，在笔者的环境中是`vi`通过直接编辑文件`/etc/crontab`可以设置系统级别的cron table。  
使用`crontab -e`的方式编辑时，会在/tmp下面生成一个临时文件，保存后`crond`会将内容写入到/var/spool/cron下面一个和用户名同名的文件中，`crond`会在保存时做语法检查。这也是推荐的设置定时任务的用法。

语法：

```sh
*  *  *  *  *  command
```

每一行表示一个任务，以符号`#`开头的行表示注释，不生效。每个生效行都形如上面所示，一行被分为6部分，其中：

```sh
第一部分表示分钟(0-59)，* 表示每分钟
第二部分表示小时(0-23)，* 表示每小时
第三部分表示日(1-31)，  * 表示每天
第四部分表示月(1-12)，  * 表示每月
第五部分表示周几(0-6,0表示周日)，* 表示一周中每天
第六部分表示要执行的任务
```

关于时间设置的前五部分中，除了`*`表示当前部分的任意时间外，还支持另外三个符号`/`、`,`、`-`分别表示`每隔`、`时间点A和时间点B`、`时间点A到时间点B`。   
如每隔3分钟测试10.0.1.252的连通性，并将结果追加输出到/root/252.log中

```
[root@centos7 ~]# crontab -e
*/3 * * * * /usr/bin/ping -c1 10.0.1.252 &>> /root/252.log
```

保存后会有`crontab: installing new crontab`字样出现。注意六个部分都不能为空，命令最好写绝对路径，编辑普通用户的定时任务时，要注意命令的执行权限。

如一月份到五月份，每周2和周5凌晨2:30执行备份任务

```sh
30 2 * 1-5 2,5 /bin/bash /root/temp/backup.sh
```

这里将备份任务写入到脚本`/root/temp/backup.sh`中执行

如3-6月和9-12月，每周一到周五12点到14点，每2分钟执行一次刷新任务

```sh
*/2 12-14 * 3-6,9-12 1-5 /bin/bash /root/temp/refresh.sh
```

混合使用日期时间及特殊符号，可以组合出大多数想要的时间。

查看定时任务

```
[root@centos7 ~]# crontab -l
*/3 * * * * /usr/bin/ping -c1 10.0.1.252 &>> /root/252.log
30 2 * 1-5 2,5 /bin/bash /root/temp/backup.sh
*/2 12-14 * 3-6,9-12 1-5 /bin/bash /root/temp/refresh.sh
```

选项`-r`表示删除定时任务

```
[root@centos7 ~]# crontab -r
[root@centos7 ~]# crontab -l
no crontab for root
```

使用`crontab`时经常会遇到的一个问题是，在命令行下能够正常执行的命令或脚本，设置了定时任务时却不能正常执行。造成这种情况的原因一般是因为`crond`为命令或脚本设置了与登录shell不同的`环境变量`


```
[root@centos7 ~]# head -3 /etc/crontab 
SHELL=/bin/bash
PATH=/sbin:/bin:/usr/sbin:/usr/bin
MAILTO=root
[root@centos7 ~]#
[root@centos7 ~]# echo $PATH
/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin
[root@centos7 ~]#
```

这里crond的`PATH`和shell中的值不同，`PATH`环境变量定义了shell执行命令时搜索命令的路径。关于环境变量更多的内容，将在shell编程的文章里详细说明。

对于系统级别的定时任务，这些任务更加重要，大部分linux系统在/etc中包含了一系列与 cron有关的子目录：`/etc/cron.{hourly,daily,weekly,monthly}`，目录中的文件定义了每小时、每天、每周、每月需要运行的脚本，运行这些任务的精确时间在文件`/etc/crontab`中指定。如：

```sh
SHELL=/bin/bash
PATH=/sbin:/bin:/usr/sbin:/usr/bin
MAILTO=root
HOME=/

# run-parts
01 * * * * root run-parts /etc/cron.hourly
02 4 * * * root run-parts /etc/cron.daily
22 4 * * 0 root run-parts /etc/cron.weekly
42 4 1 * * root run-parts /etc/cron.monthly
```

对于24小时开机的服务器来说，这些任务的定期运行，保证了服务器的稳定性。但注意到这些任务的执行一般都在凌晨，对于经常需要关机的linux计算机(如笔记本)来说，很可能在需要运行cron的时候处于关机状态，cron得不到运行，时间长了会导致系统变慢。对于这样的系统，linux引入了另一个工具`anacron`来负责执行系统定时任务。   
`anacron`的目的并不是完全替代`cron`，是作为`cron`的一个补充。`anacron`的任务定义在文件`/etc/anacrontab`中：

```sh
# /etc/anacrontab: configuration file for anacron

# See anacron(8) and anacrontab(5) for details.

SHELL=/bin/sh
PATH=/sbin:/bin:/usr/sbin:/usr/bin
MAILTO=root
# the maximal random delay added to the base delay of the jobs
RANDOM_DELAY=45
# the jobs will be started during the following hours only
START_HOURS_RANGE=3-22

#period in days   delay in minutes   job-identifier   command
1       5       cron.daily              nice run-parts /etc/cron.daily
7       25      cron.weekly             nice run-parts /etc/cron.weekly
@monthly 45     cron.monthly            nice run-parts /etc/cron.monthly
```

与`cron`是作为守护进程运行的不同，`anacron`是作为普通进程运行并终止的。对于定义的每个任务，`anacron`在系统启动后将会检查应当运行的任务，判断上一次运行到现在的时间是否超过了预定天数(/etc/anacrontab中任务行第一列)，如果大于预定天数，则会延迟一个时间(/etc/anacrontab中任务行第二列)之后运行该任务。这样就保证了任务的执行。关于`anacron`的更多内容，请查阅相关文档。

### 3、`systemd.timer` `crond`和`atd`服务基于分钟的，意思是说它们每分钟醒来一次检查是否有任务需要执行。如果有任务的执行需要精确到秒，`crond`和`atd`是无能为力的。在基于`systemd`的系统上，可以通过计时器`systemd.timer`来实现精确到秒的计划任务。  
上一篇文章中我们提到了systemd中服务单元的概念，在这里我们需要用到其中的两种：`.service`和`.timer`。其中`.service`负责配置需要运行的任务，`.timer`负责配置执行时间。

我们先看一个例子：

创建任务脚本

```
[root@centos7 temp]# cat /root/temp/ping252.sh 
#!/bin/bash
ping -c1 10.0.1.252 &>> /root/temp/252.log
```

配置服务.service

```
[root@centos7 temp]# cd /usr/lib/systemd/system
[root@centos7 system]# cat ping252.service 
[Unit]
Description=ping 252

[Service]
Type=simple
ExecStart=/root/temp/ping252.
[root@centos7 system]# 
```

配置计时器.timer

```
[root@centos7 temp]# cd /usr/lib/systemd/system
[root@centos7 system]# cat ping252.timer 
[Unit]
Description=ping 252 every 30s

[Timer]
# Time to wait after enable this unit
OnActiveSec=60
# Time between running each consecutive time
OnUnitActiveSec=30
Unit=ping252.service

[Install]
WantedBy=multi-user.target
[root@centos7 system]# 
```

启用计时器

```
[root@centos7 system]# systemctl enable ping252.timer
Created symlink from /etc/systemd/system/multi-user.target.wants/ping252.timer to /usr/lib/systemd/system/ping252.timer.
[root@centos7 system]# systemctl start ping252.timer
```

查看

```
#计时器
[root@centos7 system]# systemctl status ping252.timer
● ping252.timer - ping 252 every 30s
   Loaded: loaded (/usr/lib/systemd/system/ping252.timer; enabled; vendor preset: disabled)
   Active: active (waiting) since 五 2016-12-23 14:27:26 CST; 3min 42s ago

12月 23 14:27:26 centos7 systemd[1]: Started ping 252 every 30s.
12月 23 14:27:26 centos7 systemd[1]: Starting ping 252 every 30s.
#服务
[root@centos7 system]# systemctl status ping252
● ping252.service - ping 252
   Loaded: loaded (/usr/lib/systemd/system/ping252.service; static; vendor preset: disabled)
   Active: active (running) since 五 2016-12-23 14:35:38 CST; 2ms ago
 Main PID: 11494 (ping252.sh)
   CGroup: /system.slice/ping252.service
           └─11494 /bin/bash /root/temp/ping252.sh

12月 23 14:35:38 centos7 systemd[1]: Started ping 252.
12月 23 14:35:38 centos7 systemd[1]: Starting ping 252...
```

停用

```
[root@centos7 system]# systemctl disable ping252.timer 
Removed symlink /etc/systemd/system/multi-user.target.wants/ping252.timer.
[root@centos7 system]# systemctl stop ping252.timer
[root@centos7 system]# 
```

计时器启用1分钟之后看到/root/temp/252.log文件的生成，之后每隔30秒都有内容写入。`systemd`的服务单元配置文件中被不同的`标签`分隔成不同的配置区块，其中：   
`[Unit]`标签下指定了不依赖于特定类型的通用配置信息，比如例子中两个文件都指定了一个选项`Description=`表示描述信息。   
`[Install]`标签下保存了本单元的安装信息，其中`WantedBy=`表示当使用`systemctl enable`命令启用该单元时，会在指定的目标的`.wants/`或`.requires/`下创建对应的符号链接(如上例)。这么做的结果是：当指定的目标启动时本单元也会被启动。  

除了这两个所有配置文件都可以设置的标签外(其余选项可以通过命令`man 5 systemd.unit`查看)，每个服务单元还有一个特定单元类型的标签，比如我们例子中`.service`文件中的`[Service]`和`.timer`文件中的`[Timer]`。  
`[Service]`标签下`Type=`后的值指明了执行方式，设置为`simple`并配合`ExecStart=`表明指定的程序(我们例子中的脚本)将不会fork()而启动；如果设置为`oneshot`表明只执行一次(类似`at`)，如果需要让systemd在服务进程退出之后仍然认为该服务处于激活状态，则还需要设置`RemainAfterExit=yes`。其余选项请用命令`man 5 systemd.service`查看    
`[Timer]`标签中可以指定多种单调定时器，所谓"单调时间"的意思是从开机那一刻(零点)起， 只要系统正在运行，该时间就不断的单调均匀递增(但在系统休眠时此时间保持不变)，永远不会往后退，并且与时区也没有关系。 即使在系统运行的过程中，用户向前/向后修改系统时间，也不会对"单调时间"产生任何影响。包括：

```sh
OnActiveSec=       表示相对于本单元被启用的时间点
OnBootSec=         表示相对于机器被启动的时间点
OnStartupSec=      表示相对于systemd被首次启动的时间点
OnUnitActiveSec=   表示相对于匹配单元(本标签下Unit=指定的单元)最后一次被启动的时间点
OnUnitInactiveSec= 表示相对于匹配单元(本标签下Unit=指定的单元)最后一次被停止的时间点
```

我们的例子中使用了其中的两个`OnActiveSec=60`和`OnUnitActiveSec=30`指定本单元在启用之后60秒调用`Unit=`后的单元，并在此单元被启用后每隔30秒再次启用它，达到了定时周期性的执行的目的。

这些定时器后指定的时间单位可以是：us(微秒), ms(毫秒), s(秒), m(分), h(时), d(天), w(周), month(月), y(年)。如果省略了单位，则表示使用默认单位‘秒’。可以写成`5h 30min`表示之后的5小时30分钟。   
`[Timer]`标签下还可以设置基于挂钟时间(wall clock)的日历定时器`OnCalendar=`，所谓"挂钟时间"是指真实世界中墙上挂钟的时间， 在操作系统中实际上就是系统时间，这个时间是操作系统在启动时从主板的时钟芯片中读取的。由于这个时间是可以手动修改的，所以，这个时间既不一定是单调递增的、也不一定是均匀递增的。其时间格式可以是：

```sh
Thu,Fri 2012-*-1,5 11:12:13  #表示2012年任意月份的1日和5日，如果是星期四或星期五，则在时间11:12:13执行
*-*-* *:*:00                 #表示每分钟
*-*-* 00:00:00               #表示每天
*-01,07-01 00:00:00          #表示每半年
*:0/15                       #表示每15分钟
12,14,13:20,10,30            #表示12/13/14点的10分、20分、30分
Mon,Fri *-01/2-01,03 *:30:45 #表示任意年份奇数月份的1日和3日，如果是周一或周五，则在每小时的30分45秒执行
```

单调定时器和日历定时器的其他内容可以通过命令`man 7 systemd.time`查询   
`Unit=`后指明了与此计时器相关联的服务单元(我们例子中的ping252.service)。  
服务单元中的大部分设置选项允许指定多次，不相冲突的情况下将均生效，如`.timer`中可以设置多个Unit表示这些服务单元共用一个计时器。

另外`[Timer]`标签下还可以设置选项`Persistent=`，它只对`OnCalendar=`指令定义的日历定时器有意义。如果设为`yes`(默认值为`no`)，则表示将匹配单元的上次触发时间永久保存在磁盘上。 这样，当定时器单元再次被启动时， 如果匹配单元本应该在定时器单元停止期间至少被启动一次， 那么将立即启动匹配单元。 这样就不会因为关机而错过必须执行的任务。(类似于`anacron`的功能)   
关于定时器的更多选项可以通过`man systemd.timer`查看

使用`systemd.timer`设置定时任务可以代替`atd`和`crond`的所有功能，另外`systemd`还接管了许多其他服务，这些内容超出了本篇的范围，在以后的文章中如果涉及到相关的内容，会有相应的介绍。

[0]: https://segmentfault.com/a/1190000007878452#articleHeader12
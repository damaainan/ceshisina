 **at命令** **-->用于设置定时任务，指定一个时间执行一个任务，只能执行一次**

 ****

 **【at 命令作用 】**

 Windows提供了计划任务这一功能，在控制面板 -> 性能与维护 -> 任务计划， 它的功能就是安排自动运行的任务。 Linux中通过什么来实现类似功能，这里就必须介绍crontab, at  
在一个指定的时间执行一个指定任务，只能执行一次，且需要开启atd进程   
ps -ef | grep atd # 查看  
/etc/init.d/atd start # 启动  
chkconfig --level 2345 atd on; # 开机即启动   
/etc/init.d/atd status # 查看at服务是否开启  

![][0]

【 **常用时间格式** 】   
格式 例子

-------------------------------------------------------------------   
HH:MM at 21:00   
HH:MM YYYY-MM-DD at 21:00 2015-11-30   
HH:MM[am|pm] [Month] [Date] at 09pm May 1   
HH:MM[am|pm]+数字[minutes|hours|days|weeks] at now + 5 minutes

【 **指定 时间 方式** 】  
绝对时间：HH:MM, DD.MM.YY, MM/DD/YY, YYYY-MM-DD  
相对时间：now + #单位即可 $ now + 5 days  
单位时间：minutes，hours，days，weeks  
模糊时间：noon(12:00PM)， midnight(12:00AM)，teatime(4:00PM)

【 **at的配置文件** 】/etc/at.deny和/etc/at.allow  
如果deny单独存在，则是deny以外的所有用户都可以使用at命令  
如果allow单独存在，则是只允许allow内的用户可以使用at命令  
如果同时存在，则只允许allow内的用户使用at命令  
如果同时不存在，则只允许root账号执行at操作

【 **&和nohup** 】  
1.当在前台运行某个作业时,终端被该作业占据,而在后台运行作业时,它不会占据终端.  
可以使用&命令把作业放到后台执行.格式为: 命令 &  
2.如果正在运行一个进程,而且觉得在退出帐户时该进程还不会结束,那么可以使用nohup命令,该命令可以在你退出帐户之后继续运行相应的进程.nohup就是不挂起的意思.缺省情况下该作业的所有输出都被重定向到一个名为  
nohup.out的文件中,除非另外指定了输出文件.  
nohup command > myout.file 2>&1 # 输出被重定向到myout.file文件中.

[0]: ./img/20170205100926846.png
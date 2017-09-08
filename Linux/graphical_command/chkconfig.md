 **chkconfig命令** **-->用来检查和设置系统的各种服务**

 ****

 【 **chkconfig命令作用** 】

 每个被chkconfig管理的服务需要在对应的init.d下的脚本加上两行或者更多行的注释。

 第一行告诉chkconfig缺省启动的运行级以及启动和停止的优先级。

 第二行对服务进行描述，可以用\跨行注释。  
例如，random.init包含三行：  

    # chkconfig: 2345 20 80  
    # description: Saves and restores system entropy pool for \  
    # higher quality random number generation.

 **备注:**

 如果某服务缺省不在任何运行级启动，那么使用-代替运行级。

 【 **级别代表的含义** 】

等级0表示：表示关机  
等级1表示：单用户模式  
等级2表示：无网络连接的多用户命令行模式  
等级3表示：有网络连接的多用户命令行模式  
等级4表示：不可用  
等级5表示：带图形界面的多用户模式  
等级6表示：重新启动

 

![][0]

 【 **增加删除服务** 】

 **--add** 增加所指定的系统服务，让chkconfig指令得以管理它，并同时在系统启动的叙述文件内增加相关数据

 **--del** 删除所指定的系统服务，不再由chkconfig指令管理，并同时在系统启动的叙述文件内删除相关数据

 【 **如何增加一个服务** 】  
1) 增加服务 # 服务脚本必须存放在/etc/ini.d/目录下  
2) chkconfig --add servicename # 增加此服务，此时服务会被在/etc/rc.d/rcN.d中赋予K/S入口了；  
3) chkconfig --level 35 mysqld on # 修改服务的默认启动等级

   
 【 **备注** 】

 1) 系统服务：一直在内存中，而且一直在运行，并提供服务的被称为服务；  
2) 而服务也是一个运行的程序，则这个运行的程序则被称为daemons；  
3) 这些服务的启动脚本一般放置在： /etc/init.d  
4) 在CentOS中服务启动脚本放置在：/etc/rc.d/init.d而/etc/init.d这个目录为公认的目录，在centos中/etc/init.d就是一个链接档案  
5) /etc/sysconfig 服务初始化环境变量配置都在这个文件中。  
6) /var/lib 各个服务产生的数据库都在这个目录下，最简单的在这里找到mysql 使用 vim 打开就可以看到，你建立的数据库以及系统默认产生的数据库名称都在这里面！  
7) 启动/停止/重启服务： /etc/init.d/serverName [start|stop|restart|status]  
service serverName [start|stop|restart] 

【 **--del** 】

[0]: ./img/20170212211421915.png
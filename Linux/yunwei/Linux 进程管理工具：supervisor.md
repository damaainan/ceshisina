# Linux 进程管理工具：supervisor 

原创  2017-03-14 00:12:56  

**Linux 进程管理工具：supervisor**

**supervisor简介**在Linux服务器中，有时候我们需要一个进程需要可靠的在后台运行，并且能够监控进程状态，在意外结束时能够自动重启等。此时就可以使用supervisor。

supervisor 是使用Python开发的一套通用的进程管理程序，能够将一个普通的命令行进程变成后台的守护进程，并且监控进程的状态，异常退出时能够自动重启。

**安装supervisor**在Ubuntu中可以使用apt-get来安装：

    sudo apt-get install supervisor

如果安装缓慢，可以更换中科大的源：

[> https://mirrors.ustc.edu.cn/help/ubuntu.html][1]

**配置**安装完成以后，我们需要编写一个配置文件，让supervisor来管理它。每个进程的配置文件都可以单独拆分，放在/etc/supervisor/conf.d/目录下，以.conf作为扩展名。

    # 首先进入/etc/supervisor/conf.d目录
    /etc/supervisor/conf.d
    # 使用supervisor自带的命令生成模板
    echo_supervisord_conf > foo.conf

编辑模板，在开头添加以下内容：

    [program:foo]
    command=/bin/cat

> [program:app] ： 定义进程app

> command ： 命令

> directory ： 进程的当前目录

> user ： 进程运行的用户身份

详细配置说明：

```conf
    ;*为必须填写项
    ;*[program:应用名称]
    [program:cat]
    
    ;*命令路径,如果使用python启动的程序应该为 python /home/test.py, 
    ;不建议放入/home/user/, 对于非user用户一般情况下是不能访问
    command=/bin/cat
    
    ;当numprocs为1时,process_name=%(program_name)s
    ;当numprocs>=2时,%(program_name)s_%(process_num)02d
    process_name=%(program_name)s
    
    ;进程数量
    numprocs=1
    
    ;执行目录,若有/home/supervisor_test/test1.py
    ;将directory设置成/home/supervisor_test
    ;则command只需设置成python test1.py
    ;否则command必须设置成绝对执行目录
    directory=/tmp
    
    ;掩码:--- -w- -w-, 转换后rwx r-x w-x
    umask=022
    
    ;优先级,值越高,最后启动,最先被关闭,默认值999
    priority=999
    
    ;如果是true,当supervisor启动时,程序将会自动启动
    autostart=true
    
    ;*自动重启
    autorestart=true
    
    ;启动延时执行,默认1秒
    startsecs=10
    
    ;启动尝试次数,默认3次
    startretries=3
    
    ;当退出码是0,2时,执行重启,默认值0,2
    exitcodes=0,2
    
    ;停止信号,默认TERM
    ;中断:INT(类似于Ctrl+C)(kill -INT pid),退出后会将写文件或日志(推荐)
    ;终止:TERM(kill -TERM pid)
    ;挂起:HUP(kill -HUP pid),注意与Ctrl+Z/kill -stop pid不同
    ;从容停止:QUIT(kill -QUIT pid)
    ;KILL, USR1, USR2其他见命令(kill -l),说明1
    stopsignal=TERM
    
    stopwaitsecs=10
    
    ;*以root用户执行
    user=root
    
    ;重定向
    redirect_stderr=false
    
    stdout_logfile=/a/path
    stdout_logfile_maxbytes=1MB
    stdout_logfile_backups=10
    stdout_capture_maxbytes=1MB
    stderr_logfile=/a/path
    stderr_logfile_maxbytes=1MB
    stderr_logfile_backups=10
    stderr_capture_maxbytes=1MB
    
    ;环境变量设置
    environment=A="1",B="2"
    
    serverurl=AUTO
```


**启动**如果编辑默认的supervisor.conf，则需要重启supervisor使配置文件生效：

    supervisorctl reload

然后运行下面的命令启动进程：

    supervisorctl start foo

如果运行出现如下错误：

    unix:///var/run/supervisor.sock no such file

可以运行下面的命令，然后再次启动：

> sudo touch /var/run/supervisor.sock  
> sudo chmod 777 /var/run/supervisor.sock  
> sudo service supervisor restart

看到如下信息，说明运行成功：

> foo: started

也可以输入supervisorctl进入supervisor的控制台界面，同样能够看到：

> foo RUNNING pid 6665, uptime 0:08:08

在supervisor的控制台输入help可以获取帮助信息，输入status可以获取当前运行的进程信息，输入exit可以退出supervisor的控制台界面。

**关闭**使用下面的命令就可以关闭supervisor启动的进程：

    supervisorctl stop foo

可以看到输出信息：

> foo: stopped

**常用命令**> 更新新的配置到supervisord

    > supervisorctl update

> 重新启动配置中的所有程序

    > supervisorctl reload

> 启动某个进程(program_name=你配置中写的程序名称)

    > supervisorctl start program_name

> 查看正在守候的进程(同时进入控制台)

    > supervisorctl

> 停止某一进程 (program_name=你配置中写的程序名称)

    > pervisorctl stop program_name

> 重启某一进程 (program_name=你配置中写的程序名称)

    > supervisorctl restart program_name

> 停止全部进程

    > supervisorctl stop all

更多内容可以参考：[supervisor官方文档][2]


[1]: https://mirrors.ustc.edu.cn/help/ubuntu.html
[2]: http://supervisord.org/
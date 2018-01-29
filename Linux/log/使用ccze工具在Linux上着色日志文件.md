# 使用ccze工具在Linux上着色日志文件

 时间 2018-01-29 10:23:43 

原文[http://www.jianshu.com/p/4333e9148153][1]


不知道大家有没有这种感受，在一片白茫茫的日志输出中，查看和阅读起来非常痛苦，你有没有想过，如果日志有颜色是否就会方便阅读一点，正巧我也是这样想的，聪明的人都是这样想的( 允许我自恋一下 )。 

_ccze_ 一个用C语言编写的快速日志着色器，使日志查找更加方便快捷,它使用模块化方法来支持流行应用程序（如 Apache ， Postfix ， Exim 等）或自定义颜色格式。 

#### 在CentOS和Fedora linux上安装ccze

    # yum install ccze -y

#### 在Debian / Ubuntu linux上安装ccze

    〜$ sudo apt-get install ccze -y

#### 使用ccze工具

* 控制台查看

ccze colourises 发送到标准的日志，例如，我们可以使用 tailf 来跟踪一个日志文件，然后通过管道输出来美化输出到 ccze ，例如： 
```
    〜$ tail /var/log/syslog | ccze -A
```
![][3]

* 使用 ccze 工具将日志文件导出到 html 文件：
```
    〜$ cat /var/log/syslog | ccze -h > /home/syslog.html
```
![][4]

* 列出 ccze 模块:
```
    $ ccze -l

    ~$ ccze -l
    
    Available plugins:
    
    Name      | Type    | Description
    ------------------------------------------------------------
    apm       | Partial | Coloriser for APM sub-logs.                         #部分| 用于APM子日志的着色器。           
    distcc    | Full    | Coloriser for distcc(1) logs.                       #完整| 用于distcc（1）日志的着色器。           
    dpkg      | Full    | Coloriser for dpkg logs.                            #完整| dpkg日志的着色器。       
    exim      | Full    | Coloriser for exim logs.                            #完整| 进出口日志的着色剂。       
    fetchmail | Partial | Coloriser for fetchmail(1) sub-logs.                #部分| 用于fetchmail的着色器（1）子日志。                   
    ftpstats  | Full    | Coloriser for ftpstats (pure-ftpd) logs.            #完整| ftpstats（pure-ftpd）日志的着色器。                       
    httpd     | Full    | Coloriser for generic HTTPD access and error logs.  #完整| 用于通用HTTPD访问和错误日​​志的着色器。                                   
    icecast   | Full    | Coloriser for Icecast(8) logs.                      #完整| 用于Icecast（8）日志的着色剂。               
    oops      | Full    | Coloriser for oops proxy logs.                      #完整| oops代理日志的着色器。               
    php       | Full    | Coloriser for PHP logs.                             #完整| PHP日志的着色器。       
    postfix   | Partial | Coloriser for postfix(1) sub-logs.                  #部分| 用于后缀（1）子日志的着色器。                   
    procmail  | Full    | Coloriser for procmail(1) logs.                     #完整| procmail（1）日志的着色器。               
    proftpd   | Full    | Coloriser for proftpd access and auth logs.         #完整| 用于proftpd访问和auth日志的着色器。                           
    squid     | Full    | Coloriser for squid access, store and cache logs.   #完整| 用于鱿鱼访问，存储和缓存日志的着色器。                               
    sulog     | Full    | Coloriser for su(1) logs.                           #完整| su（1）原木的着色剂。       
    super     | Full    | Coloriser for super(1) logs.                        #完整| 超级（1）原木的着色剂。           
    syslog    | Full    | Generic syslog(8) log coloriser.                    #完整| 通用系统日志（8）日志着色器。               
    ulogd     | Partial | Coloriser for ulogd sub-logs.                       #部分| ulogd子日志的着色器。           
    vsftpd    | Full    | Coloriser for vsftpd(8) logs.                       #完整| vsftpd（8）日志的着色器。           
    xferlog   | Full    | Generic xferlog coloriser.                          #完整| 通用xferlog着色器。
```
* 这个工具有很多选项，我们可以在这些文件中进行更多的自定义
```
    ~$ vim /etc/cczerc
```
博客原文地址: [使用ccze工具在Linux上着色日志文件][5]

[1]: http://www.jianshu.com/p/4333e9148153?utm_source=tuicool&utm_medium=referral
[3]: https://img1.tuicool.com/3IzeAzq.png
[4]: https://img2.tuicool.com/ZV7rIvq.png
[5]: https://link.jianshu.com?t=http%3A%2F%2Fwww.leshalv.net%2Fposts%2F8a49e8dd%2F
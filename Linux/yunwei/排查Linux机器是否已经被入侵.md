## [排查Linux机器是否已经被入侵][0]

随着开源产品的越来越盛行，作为一个Linux运维工程师，能够清晰地鉴别异常机器是否已经被入侵了显得至关重要，个人结合自己的工作经历，整理了几种常见的机器被黑情况供参考

背景信息：以下情况是在CentOS 6.9的系统中查看的，其它Linux发行版类似

### 1.入侵者可能会删除机器的日志信息，可以查看日志信息是否还存在或者是否被清空，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# ll -h /var/log/*
    
    -rw-------. 1 root root 2.6K Jul  7 18:31 /var/log/anaconda.ifcfg.log
    
    -rw-------. 1 root root  23K Jul  7 18:31 /var/log/anaconda.log
    
    -rw-------. 1 root root  26K Jul  7 18:31 /var/log/anaconda.program.log
    
    -rw-------. 1 root root  63K Jul  7 18:31 /var/log/anaconda.storage.log
    
     
    
    [root@hlmcen69n3 ~]# du -sh /var/log/*
    
    8.0K /var/log/anaconda
    
    4.0K /var/log/anaconda.ifcfg.log
    
    24K  /var/log/anaconda.log
    
    28K  /var/log/anaconda.program.log
    
    64K  /var/log/anaconda.storage.log
    
     
```

### 2.入侵者可能创建一个新的存放用户名及密码文件，可以查看/etc/passwd及/etc/shadow文件，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# ll /etc/pass*
    
    -rw-r--r--. 1 root root 1373 Sep 15 11:36 /etc/passwd
    
    -rw-r--r--. 1 root root 1373 Sep 15 11:36 /etc/passwd-
    
     
    
    [root@hlmcen69n3 ~]# ll /etc/sha*
    
    ----------. 1 root root 816 Sep 15 11:36 /etc/shadow
    
    ----------. 1 root root 718 Sep 15 11:36 /etc/shadow-
```

### 3.入侵者可能修改用户名及密码文件，可以查看/etc/passwd及/etc/shadow文件内容进行鉴别，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# more /etc/passwd
    
    root:x:0:0:root:/root:/bin/bash
    
    bin:x:1:1:bin:/bin:/sbin/nologin
    
    daemon:x:2:2:daemon:/sbin:/sbin/nologin
    
     
    
    [root@hlmcen69n3 ~]# more /etc/shadow
    
    root:*LOCK*:14600::::::
    
    bin:*:17246:0:99999:7:::
    
    daemon:*:17246:0:99999:7:::
```

### 4.查看机器最近成功登陆的事件和最后一次不成功的登陆事件，对应日志“/var/log/lastlog”，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# lastlog
    
    Username         Port     From             Latest
    
    root                                       **Never logged in**
    
    bin                                        **Never logged in**
    
    daemon                                     **Never logged in**
```

### 5.查看机器当前登录的全部用户，对应日志文件“/var/run/utmp”，相关命令示例：

    [root@hlmcen69n3 ~]# who
    
    stone    pts/0        2017-09-20 16:17 (X.X.X.X)
    
    test01   pts/2        2017-09-20 16:47 (X.X.X.X)

### 6.查看机器创建以来登陆过的用户，对应日志文件“/var/log/wtmp”，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# last
    
    test01   pts/1        X.X.X.X   Wed Sep 20 16:50   still logged in  
    
    test01   pts/2        X.X.X.X   Wed Sep 20 16:47 - 16:49  (00:02)   
    
    stone    pts/1        X.X.X.X   Wed Sep 20 16:46 - 16:47  (00:01)   
    
    stone    pts/0        X.X.X.X   Wed Sep 20 16:17   still logged in
```

### 7.查看机器所有用户的连接时间（小时），对应日志文件“/var/log/wtmp”，相关命令示例：

 
```shell
    [root@hlmcen69n3 ~]# ac -dp
    
             stone                               11.98
    
    Sep 15      total       11.98
    
             stone                               67.06
    
    Sep 18      total       67.06
    
             stone                                1.27
    
             test01                               0.24
    
    Today        total        1.50
```

### 8.如果发现机器产生了异常流量，可以使用命令“tcpdump”抓取网络包查看流量情况或者使用工具”iperf”查看流量情况

### 9.可以查看/var/log/secure日志文件，尝试发现入侵者的信息，相关命令示例：

 
```
    [root@hlmcen69n3 ~]# cat /var/log/secure | grep -i "accepted password"
    
    Sep 20 12:47:20 hlmcen69n3 sshd[37193]: Accepted password for stone from X.X.X.X port 15898 ssh2
    
    Sep 20 16:17:47 hlmcen69n3 sshd[38206]: Accepted password for stone from X.X.X.X port 9140 ssh2
    
    Sep 20 16:46:00 hlmcen69n3 sshd[38511]: Accepted password for stone from X.X.X.X port 2540 ssh2
    
    Sep 20 16:47:16 hlmcen69n3 sshd[38605]: Accepted password for test01 from X.X.X.X port 10790 ssh2
    
    Sep 20 16:50:04 hlmcen69n3 sshd[38652]: Accepted password for test01 from X.X.X.X port 28956 ssh2
```

### 10.查询异常进程所对应的执行脚本文件

a.top命令查看异常进程对应的PID

![][1]

b.在虚拟文件系统目录查找该进程的可执行文件

 
```shell
    [root@hlmcen69n3 ~]# ll /proc/1850/ | grep -i exe
    
    lrwxrwxrwx. 1 root root 0 Sep 15 12:31 exe -> /usr/bin/python
    
     
    
    [root@hlmcen69n3 ~]# ll /usr/bin/python
    
    -rwxr-xr-x. 2 root root 9032 Aug 18  2016 /usr/bin/python
```

### 11.如果确认机器已经被入侵，重要文件已经被删除，可以尝试找回被删除的文件

Note：

参考Link：http://www.cnblogs.com/ggjucheng/archive/2012/01/08/2316599.html

1>当进程打开了某个文件时，只要该进程保持打开该文件，即使将其删除，它依然存在于磁盘中。这意味着，进程并不知道文件已经被删除，它仍然可以向打开该文件时提供给它的文件描述符进行读取和写入。除了该进程之外，这个文件是不可见的，因为已经删除了其相应的目录索引节点。

2>在/proc 目录下，其中包含了反映内核和进程树的各种文件。/proc目录挂载的是在内存中所映射的一块区域，所以这些文件和目录并不存在于磁盘中，因此当我们对这些文件进行读取和写入时，实际上是在从内存中获取相关信息。大多数与 lsof 相关的信息都存储于以进程的 PID 命名的目录中，即 /proc/1234 中包含的是 PID 为 1234 的进程的信息。每个进程目录中存在着各种文件，它们可以使得应用程序简单地了解进程的内存空间、文件描述符列表、指向磁盘上的文件的符号链接和其他系统信息。lsof 程序使用该信息和其他关于内核内部状态的信息来产生其输出。所以lsof 可以显示进程的文件描述符和相关的文件名等信息。也就是我们通过访问进程的文件描述符可以找到该文件的相关信息。

3>当系统中的某个文件被意外地删除了，只要这个时候系统中还有进程正在访问该文件，那么我们就可以通过lsof从/proc目录下恢复该文件的内容。

假设入侵者将/var/log/secure文件删除掉了，尝试将/var/log/secure文件恢复的方法可以参考如下：

a.查看/var/log/secure文件，发现已经没有该文件

    [root@hlmcen69n3 ~]# ll /var/log/secure
    
    ls: cannot access /var/log/secure: No such file or directory

b.使用lsof命令查看当前是否有进程打开/var/log/secure，

    [root@hlmcen69n3 ~]# lsof | grep /var/log/secure
    
    rsyslogd   1264      root    4w      REG                8,1  3173904     263917 /var/log/secure (deleted)

c.从上面的信息可以看到 PID 1264（rsyslogd）打开文件的文件描述符为4。同时还可以看到/var/log/ secure已经标记为被删除了。因此我们可以在/proc/1264/fd/4（fd下的每个以数字命名的文件表示进程对应的文件描述符）中查看相应的信息，如下：

 
```
    [root@hlmcen69n3 ~]# tail /proc/1264/fd/4
    
    Sep 20 16:47:21 hlmcen69n3 sshd[38511]: pam_unix(sshd:session): session closed for user stone
    
    Sep 20 16:47:21 hlmcen69n3 su: pam_unix(su-l:session): session closed for user root
    
    Sep 20 16:49:30 hlmcen69n3 sshd[38605]: pam_unix(sshd:session): session closed for user test01
    
    Sep 20 16:50:04 hlmcen69n3 sshd[38652]: reverse mapping checking getaddrinfo for 190.78.120.106.static.bjtelecom.net [106.120.78.190] failed - POSSIBLE BREAK-IN ATTEMPT!
    
    Sep 20 16:50:04 hlmcen69n3 sshd[38652]: Accepted password for test01 from 106.120.78.190 port 28956 ssh2
    
    Sep 20 16:50:05 hlmcen69n3 sshd[38652]: pam_unix(sshd:session): session opened for user test01 by (uid=0)
    
    Sep 20 17:18:51 hlmcen69n3 unix_chkpwd[38793]: password check failed for user (root)
    
    Sep 20 17:18:51 hlmcen69n3 sshd[38789]: pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=51.15.81.90  user=root
    
    Sep 20 17:18:52 hlmcen69n3 sshd[38789]: Failed password for root from 51.15.81.90 port 47014 ssh2
    
    Sep 20 17:18:52 hlmcen69n3 sshd[38790]: Connection closed by 51.15.81.90
```

d.从上面的信息可以看出，查看/proc/1264/fd/4就可以得到所要恢复的数据。如果可以通过文件描述符查看相应的数据，那么就可以使用I/O重定向将其重定向到文件中，如:

    [root@hlmcen69n3 ~]# cat /proc/1264/fd/4 > /var/log/secure

e.再次查看/var/log/secure，发现该文件已经存在。对于许多应用程序，尤其是日志文件和数据库，这种恢复删除文件的方法非常有用。

 
```
    [root@hlmcen69n3 ~]# ll /var/log/secure
    
    -rw-r--r--. 1 root root 3173904 Sep 20 17:24 /var/log/secure
    
     
    
    [root@hlmcen69n3 ~]# head /var/log/secure
    
    Sep 17 03:28:15 hlmcen69n3 sshd[13288]: reverse mapping checking getaddrinfo for 137-64-15-51.rev.cloud.scaleway.com [51.15.64.137] failed - POSSIBLE BREAK-IN ATTEMPT!
    
    Sep 17 03:28:15 hlmcen69n3 unix_chkpwd[13290]: password check failed for user (root)
    
    Sep 17 03:28:15 hlmcen69n3 sshd[13288]: pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=51.15.64.137  user=root
    
    Sep 17 03:28:17 hlmcen69n3 sshd[13288]: Failed password for root from 51.15.64.137 port 59498 ssh2
    
    Sep 17 03:28:18 hlmcen69n3 sshd[13289]: Received disconnect from 51.15.64.137: 11: Bye Bye
    
    Sep 17 03:28:22 hlmcen69n3 sshd[13291]: reverse mapping checking getaddrinfo for 137-64-15-51.rev.cloud.scaleway.com [51.15.64.137] failed - POSSIBLE BREAK-IN ATTEMPT!
    
    Sep 17 03:28:22 hlmcen69n3 unix_chkpwd[13293]: password check failed for user (root)
    
    Sep 17 03:28:22 hlmcen69n3 sshd[13291]: pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=51.15.64.137  user=root
    
    Sep 17 03:28:24 hlmcen69n3 sshd[13291]: Failed password for root from 51.15.64.137 port 37722 ssh2
    
    Sep 17 03:28:25 hlmcen69n3 sshd[13292]: Received disconnect from 51.15.64.137: 11: Bye Bye
```

[0]: http://www.cnblogs.com/stonehe/p/7562374.html
[1]: ../IMG/1520094017.png
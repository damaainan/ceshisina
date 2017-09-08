 **talk**命令--> 加强版的write命令，talk命令可让你与其他登录的用户交谈

![][0]

 **备注:**

 1) 如果talk命令没安装的话,可以通过apt 或yum 安装所需的包.  
    # yum install talk  
    # apt-get install talk

 2) talk服务是一台机器上得用户互相聊天用得，如果想让不同机器上得用户聊天，得安装和启动ntalk服务   
3) 在username后加入主机名称或域名，建立网络会话连接

4) 如果出现不能用Linux中talk命令参数,出错:Error no read from talk deamon:connection refused  
redhat里是修改的/etc/xinetd.d底下的 ktalk的disable=yes 改成no, 然后重启：  
    
    # /sbin/service xinetd restart

[0]: ./img/20160813100404239.png
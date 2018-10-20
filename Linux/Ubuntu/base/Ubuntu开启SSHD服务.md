## Ubuntu开启SSHD服务

来源：[https://www.cnblogs.com/happyliuyi/p/5833887.html](https://www.cnblogs.com/happyliuyi/p/5833887.html)

2016-09-02 16:06

 **`ubuntu安装ssh服务`** 

 **`一、`** 

 **`SSH分客户端openssh-client和openssh-server`**  
如果你只是想登陆别的机器的SSH只需要安装openssh-client（ubuntu有默认安装，如果没有则sudo apt-get install openssh-client），如果要使本机开放SSH服务就需要安装openssh-server
    
    sudo apt-get install openssh-server
然后确认sshserver是否启动了：

    ps -e |grep ssh
如果看到sshd那说明ssh-server已经启动了。
如果没有则可以这样启动：sudo /etc/init.d/ssh start
ssh-server配置文件位于/ etc/ssh/sshd_config，在这里可以定义SSH的服务端口，默认端口是22，你可以自己定义成其他端口号，如222。
然后重启SSH服务：
    
    sudo /etc/init.d/ssh stop
    sudo /etc/init.d/ssh start
然后使用以下方式登陆SSH：

    ssh tuns@192.168.0.100 tuns为192.168.0.100机器上的用户，需要输入密码。
断开连接：exit

 **`二、`** 

ubuntu默认并没有安装ssh服务，如果通过ssh链接ubuntu，需要自己手动安装`ssh-server`。判断是否安装ssh服务，可以通过如下命令进行：

    ssh localhost

    ssh: connect to host localhost port 22: Connection refused

如上所示，表示没有还没有安装，可以通过apt安装，命令如下：

    sudo apt-get install openssh-server

（若找不到安装包，先运行apt-get update，运行命令若出现E: 无法获得锁 /var/lib/apt/lists/lock - open (11: 资源暂时不可用)E: 无法对目录 /var/lib/apt/lists/ 加锁的问题，执行命令sudo rm /var/lib/apt/lists/lock即可。这是一种极端的情况，也就是在上次更新没有正常关闭的情况下使用。在大部分情况下，出现问题的原因在于其它的程序如系统的自动更新等正在使用apt-get进程，所以解决方法也就是将这一进程关闭。）

系统将自动进行安装，安装完成以后，先启动服务：
```
 service ssh start

ssh start/running, process 3582

sudo /etc/init.d/ssh start
```
启动后，可以通过如下命令查看服务是否正确启动
```
 ps -e | grep ssh

 2152 ?        00:00:00 ssh-agent

 3582 ?        00:00:00 sshd
```
如上表示启动ok。注意，ssh默认的端口是22，可以更改端口，更改后先stop，

然后start就可以了。改配置在`/etc/ssh/sshd_config`下

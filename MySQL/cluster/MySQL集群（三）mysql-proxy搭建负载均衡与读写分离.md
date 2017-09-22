# [MySQL集群（三）mysql-proxy搭建负载均衡与读写分离][0]

**阅读目录(Content)**

* [一、mysq-proxy简介与安装][1]
    * [1.1、mysql-proxy简介][2]
    * [1.2、实例描述作用][3]
    * [1.3、mysql-proxy的安装][4]

* [二、使用mysql-proxy实现负载均衡][5]
* [三、使用mysql-proxy实现读写分离][6]
    * [3.1、概述][7]
    * [3.2、配置读写分离][8]

* [四、Mysql-proxy 中间件的使用][9]
    * [4.1、在mysql 客户端通过中间件连接mysql集群][10]
    * [4.2、在mysql 客户端通过中间件连接mysql集群][11]

 **前言**

前面学习了主从复制和主主复制，接下来给大家分享一下怎么去使用`mysql-proxy`这个插件去配置MySQL集群中的负载均衡以及读写分离。

注意：这里比较坑的就是`mysql-proxy`一直没有更新导致版本太落后了，我在 MySQL5.7.19中搭建玩负载均衡后有bug，所以这里我在windows中搭建了两个MySQL（好像是5.6或5.6以下的都可以搭建成功）服务器 。

还有就是可以使用`mysql-proxy`的替代品`mysql-Router`功能更强大！废话不多说，我们直接干起来。


## 一、mysq-proxy简介与安装

### 1.1、mysql-proxy简介

mysql-proxy是官方提供的mysql中间件产品可以 实现负载平衡，读写分离，failover等

MySQL Proxy就是这么一个 中间层代理 ，简单的说，MySQL Proxy就是一个连接池，负责将 前台应用的连接请求转发给后台的数据库 ，并且通过使用lua脚本，可以 实现复杂的连接控制和过滤，

从而实现读写分离和负载平衡 。对于 应用来说，MySQL Proxy是完全透明的，应用则只需要连接到MySQL Proxy的监听端口即可 。

当然，这样proxy机器可能成为单点失效，但完全可以使用多个proxy机器做为冗余，在 应用服务器的连接池配置中配置到多 个proxy的连接参数即可 。

### 1.2、实例描述作用

1）我们在进行web开发的时候，往往一台MySQL服务器是不够用的，可能需要多台，web到底连接哪个数据库？

这个要程序员自己写的代码来决定的，现在是二台mysql服务器，如果有多台或者是N台呢，靠用php代码来管理连接数据库，就很尴尬了。

![][13]

2）mysql proxy就很好解决了这个问题，对于程序端而言，web端的请求，只要到 mysql proxy的连接池 就OK了，剩下的工作就交给mysql proxy了。对于程序代码管理来说就简单多了。个人觉得这一点最值得借用的了。

![][14]

### 1.3、mysql-proxy的安装

其实这个也有windows的版本，但是我推荐在linux中去使用，因为在开发中大部分服务器都是安装在linux中的。我的就是安装在ubuntu17.04版本的server版中。

1）首先你需要有这个安装包：mysql-proxy-0.8.5-linux-debian6.0-x86-64bit.tar.gz（需要的话私聊我）

2）一般我们把这个第三方的软件安装在/opt目录下

解压mysql-proxy-0.8.5-linux-debian6.0-x86-64bit.tar.gz到/opt/目录并创建软链接。 

    tar zxvf mysql-proxy-0.8.5-linux-debian6.0-x86-64bit.tar.gz –C /opt/  
    ln –snf /opt/mysql-proxy-0.8.5-linux-debian6.0-x86-64bit /opt/mysql-proxy（创建软链接不懂的看前面的博文）

![][15]

3）配置环境变量

这里的话， 一般配置在`.bashrc`下，因为每个用户有每个用户不同的功能 ，你也可以配置在全局环境变量当中（/etc/profile）

我在`.baserc`下的配置：

    sudo vi .bashrc 

在最后面添加：

![][16]

完成之后注意要更新:`source .bashrc`如果配置在全局变量中也要更新

4）查看是否安装成功

其实当你输入mysql-p的时候，按tab键能够联想出来，说明你已经配置成功了。我们可以通过 mysql-proxy -V 查看是否配置成功。 

![][17]


## 二、使用mysql-proxy实现负载均衡

那我们该怎么去配置负载均衡呢？我们可以通过mysql-proxy --help来查看参数的意思

搭建步骤：

我的mysql-proxy安装在ubuntu中，两个mysql服务器安装在windows当中。

1）在你的已经安装了mysql-proxy的主机上创建一个脚本： `mysql-proxy-replication.sh`

    #!/bin/bash
    /opt/mysql-proxy/bin/mysql-proxy \
    --proxy-address=1.0.0.3:4040 \   #这个是安装mysql-proxy的主机上的ip，这个4040端口是mysql-proxy的默认端口
    --proxy-backend-addresses=192.168.2.45:3125 \ #这个是mysql服务器安装主机的ip和mysql的端口
    --proxy-backend-addresses=192.168.2.45:3126 \
    --log-level=info \
    --log-file=/opt/mysql-proxy/logs/mysql-proxy-12.log \ #存放日志文件的位置
    --daemon

2）然后我们执行这个脚本，我把脚本放在了家目录上面

创建好了，可以修改一下文件的权限

    sudo chmod u+x mysql-proxy-replication.sh

还要创建一下存放日志文件的目录与文件

    sudo mkdir -r /opt/mysql-proxy/logs与touch mysql-proxy-12.log

执行

    sudo ./mysql-proxy-replication.sh

注意：这里我为了给大家演示我把mysql-proxy先关闭：使用ps -ef查看进程号，在使用sudo kill -9 进程号或者sudo killall mysql-proxy

3）我们查看一下日志，看是否脚本运行成功

     sudo vi /opt/mysql-proxy/logs/mysql-proxy-12.log

![][18]

从日志文件可以清楚的看到，插件proxy正在监听着4040端口，它还添加了两个MySQL服务器（主主复制）。

4）获取mysql-proxy中管理的两个服务器的连接

![][19]

获取连接：mysql -uroot -p123456 -h1.0.0.3 -P4040

分析：我们的用户名和密码是使用的是MySQL服务器的用户名和密码，因为是要从他们两个当中获取连接，ip和端口都是使用代理的ip和端口。 

5）结果

![][20]

我们在这里执行创建一个数据库:create database db_test_1

使用mysql -uroot -p123456 -h1.0.0.3 -P4040获取的连接

![][21]

然后在查看mysql两台服务器有没有创建成功

使用mysql -uroot -p -h192.168.2.45 -P3125和mysql -uroot -p -h192.168.2.45 -P3126登录

![][22]

两个都是一样的，说明主主复制的两个集群，使用mysql-proxy管理成功！

总结：在这个负载均衡当中，其实就是 使用mysql-proxy均衡两个MySQL服务器的连接数。 这里不管这个连接里面的连接处理的数据量有多大，处理时间有多长。

比如主机A有5个连接，处理时间只需要10分钟。二主机B有2个连接，处理时间需要1个小时。当有第8个连接时，更有可能获取的是主机B的连接。 

mysql_proxy会把 连接mysql服务器的tcp/IP连接缓存进连接池,以提高性能. 在缓存池里, 缓存的连接大致是平均分配在每台mysql服务器上. 但具体的每一个连接,始终连某台服务器.


## 三、使用mysql-proxy实现读写分离

### 3.1、概述

Mysql作为目前世界上使用最广泛的免费数据库，相信所有从事系统运维的工程师都一定接触过。但在实际的生产环境中，由单台Mysql作为独立的数据库是完全不能满足实际需求的，无论是在安全性，高可用性以及高并发等各个方面。

因此，一般来说都是通过 主从复制（Master-Slave）的方式来同步数据，再通过读写分离（MySQL-Proxy）来提升数据库的并发负载能力 这样的方案来进行部署与实施的。

![][23]

### 3.2、配置读写分离

在这里我只是配置的是主主复制。

![][24]

1）怎么配置在两台MySQL服务器中的主主复制我就不介绍了，前面一篇博客已经介绍了。

2）在你的已经安装了mysql-proxy的主机上创建一个脚本：mysql-proxy-rw-splitting.sh，并运行 

首先我们在前面当中已经运行了mysql-proxy我们需要先杀死这个进程:sudo killall mysql-proxy

 

    #!bash/bin  
    /opt/mysql-proxy/bin/mysql-proxy \
        --proxy-address=1.0.0.3:4040 \
        --proxy-backend-addresses=17.16.15.112:3125 \  #在3125端口的服务器中配置可读可写
        --proxy-read-only-backend-addresses=172.16.15.112:3126 \ #在3126端口的服务器中配置只读
        --proxy-lua-script=/opt/mysql-proxy/share/doc/mysql-proxy/rw-splitting.lua \  #用这个lua脚本来实现读写分离
        --log-level=info \
        --log-file=/opt/mysql-proxy/logs/mysql-proxy-12.log \ #这是它的日志
        --daemon

3）我们查看一下日志，看是否脚本运行成功

    sudo vi /opt/mysql-proxy/logs/mysql-proxy-12.log 

    2017-09-20 03:03:58: (critical) plugin proxy 0.8.5 started
    2017-09-20 03:03:58: (message) proxy listening on port 1.0.0.3:4040
    2017-09-20 03:03:58: (message) added read/write backend: 17.16.15.112:3125
    2017-09-20 03:03:58: (message) added read-only backend: 172.16.15.112:3126

4）获取连接：mysql -uroot -p123456 -h1.0.0.3 -P4040

分析：我们的用户名和密码是使用的是MySQL服务器的用户名和密码，因为是要从他们两个当中获取连接，ip和端口都是使用代理的ip和端口。 


## 四、Mysql-proxy 中间件的使用

### 4.1、在mysql 客户端通过中间件连接mysql集群 

mysql –uroot –p –h 192.168.41.201 –P 4040（注意修改my.conf中绑定ip后才能远程登录mysql,且有远程登录账号 GRANT ALL PRIVILEGES ON *.* TO ‘root’@‘%’ IDENTIFIED BY ‘’ WITH GRANT OPTION;）

如果远程连接很卡，或者很慢，可以关闭mysql节点 地址反向解析功能 在my.cnf 中添加 skip-name-resolve。

### 4.2、在mysql 客户端通过中间件连接mysql集群

可以通过 JDBC 访问mysql-proxy进而访问mysql集群 Class.forName("com.mysql.jdbc.Driver"); String url="jdbc:mysql://192.168.41.201:4040/test?user=briup&password=briup";

[0]: http://www.cnblogs.com/zhangyinhua/p/7565373.html
[1]: #_label0
[2]: #_lab2_0_0
[3]: #_lab2_0_1
[4]: #_lab2_0_2
[5]: #_label1
[6]: #_label2
[7]: #_lab2_2_0
[8]: #_lab2_2_1
[9]: #_label3
[10]: #_lab2_3_0
[11]: #_lab2_3_1
[12]: #_labelTop
[13]: ./img/1729119101.png
[14]: ./img/790542351.png
[15]: ./img/1817416858.png
[16]: ./img/936039925.png
[17]: ./img/1892505953.png
[18]: ./img/758942868.png
[19]: ./img/732500184.png
[20]: ./img/1869010194.png
[21]: ./img/2096195310.png
[22]: ./img/1830135763.png
[23]: ./img/1969503977.png
[24]: ./img/2030642863.png
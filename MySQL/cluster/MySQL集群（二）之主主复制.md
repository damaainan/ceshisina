# [MySQL集群（二）之主主复制][0]

**阅读目录(Content)**

* [一、主从复制中的问题][1]
    * [1.1、从节点占用了主节点的自增id][2]
    * [1.2、主从关系建立前的前提][3]
    * [1.3、在搭建MySQL集群主从复制的时候遇到的问题][4]

* [二、主主复制][5]
    * [2.1、主主复制理解][6]
    * [2.2、主主复制过程][7]

* [三、MySQL集群的主主复制的深入探讨][8]
    * [3.1、解决主键冲突问题][9]
    * [3.2、Mysql 集群的被动主主复制][10]
    * [3.3、节点的部署方式][11]

前面介绍了主从复制，这一篇我将介绍的是主主复制，其实听名字就可以知道，主主复制其实就是两台服务器互为主节点与从节点。接下来我将详细的给大家介绍，怎么去配置主主复制！


## 一、主从复制中的问题

### 1.1、从节点占用了主节点的自增id

环境：

主节点：zyhserver1=1.0.0.3

从节点：udzyh1=1.0.0.5

第一步：我们在主节点中创建一个数据库db_love_1，在创建一个表tb_love（里面有id自增和name属性）。

    　　create database db_love_1;
    　　use db_love_1;
    　　create table tb_love( id int primary key auto_increment, name varchar(30));

第二步：在主节点中添加一条数据，我们可以 在主从节点中都可以看到这条数据都有了 。

    　　insert into tb_love(name)values('zhangsan');

第三步：如果我们在从节点中加入一条数据

    　　insert into tb_love(name)values('lisi');

在从节点中：

![][13]

在主节点中：

![][14]

这是自然的因为我们是主从复制，只有主节点写的数据才能同步到从节点中，从节点中的数据是不能同步同主节点中的。因为从节点并没有二进制日志文件，而主节点也没有中继日志文件，去完成相应的功能。

第四步：如果我们在主节点中在插入一条数据

     　　insert into tb_love(name)values('wangwu');

在主节点中：

![][15]

在从节点中：

![][16]

分析：这时候我们会发现从节点并没有更新主节点的wangwu这条数据，因为从节点中的id为2的位置已经被占了，然后我们在来看一下从节点的状态：

![][17]

在这里我们可以看到从节点的IO线程是开启的，而SQL线程是关闭的。这样就导致了主从关系断裂了，那我们要怎么去恢复它呢？

我们 先在从节点中`stop slave`,然后进行`reset slave`,然后重新来进行在从节点中`change master to`。

### 1.2、主从关系建立前的前提

其实在建立主从关系之前，我们需要保证两点：

1）一是数据库和表的结构是一样的，也就是说主节点中有哪些数据库和表从节点也应该有哪些数据库和表。

（如果说主节点中有个数据库是从节点中没有的，那当我们删除这个数据库时，从节点没有就会出错了）

2）二是保证主从节点的：数据库主键自增的步长一致，但是自增起始位置位置不一致。

（一个从1开始自增，则生成的主键为：1,3,5,7,9。另一个从2开始自增，生成的主键为：2,4,6,810）

如果是双主的话其实没必要设置的，但是如果是主从模式并且主节点和从节点都能插入数据的话，这样从节点插入的数据不能同步到主节点。

如果主节点再插入ID相同的数据之后在同步到从节点的时候就出错了。

那要怎么去设置呢？

临时设置：

    　　主节点的MySQL终端执行：
      　　  set auto_increment_increment=2
      　　  set auto_increment_offset=1
    　　从节点的MySQL终端执行：
        　　set auto_increment_increment=2
        　　set auto_increment_offset=2

永久设置，如果是重启了MySQL服务还是要重新设置：

    　　主节点的MySQL终端执行：
     　　   set global auto_increment_increment=2
      　　  set global auto_increment_offset=1
    　　从节点的MySQL终端执行：
      　　  set global auto_increment_increment=2
      　　  set global auto_increment_offset=2

### 1.3、在搭建MySQL集群主从复制的时候遇到的问题

1）查看slave的状态出现的是

![][18]

查看你change的时候host（这里最好使用ip）、port、user、password、fileN、pos是否正确。

有没有真的创建了用户zyh。如果还不行在查看一下两台服务器能不能ping通。

2）主节点主机能ping通从节点，反过来不行

因为我们在VMware中安装的两台虚拟机，一个用的是桥接模式，一个用的是NAT模式，所以

我把桥接模式改成了NAT模式就有用了。

### 1.4、理解binary-log文件的内容获取

Slave 的 IO 线程接收到信息后，将 接收到的日志内容 依次写入到 Slave 端的`RelayLog` 文件(MySQL-relay-bin.xxxxxx)的最末端，并将读取到的Master 端的`bin-log` 的文件名和位置记录到`master-info` 文件中

Slave 的 SQL 线程检测到 `Relay Log` 中新增加了内容后，会 马上解析该 Log 文件中的内容成为在 Master 端真实执行时候的那些可执行的 Query 语句 ，并在自身执行这些 Query。

分析：slave的IO线程读到的SQL语句，是怎么来的？其实它并不能直接获取到主节点中写入的SQL语句。而是通过查询（分析）主节点中数据变化结果（如插入、删除、修改操作）

，来自己生成SQL语句存入到二进制日志文件中，所以为什么我们在主节点中指定查询语句，从节点不会去做查询操作了。


## 二、主主复制

其实我们学会了主从复制，那主主复制理解起来就是相当的简单了。不就是在主节点中配置从节点，从节点加上主节点的配置吗！

### 2.1、主主复制理解 

1）在slave节点授权账号  
2） 在master节点进行slave配置，将原来的slave当做master进行连接

![][19]

### 2.2、主主复制过程

环境：

ubuntu的server版：1.0.0.3==server1（主节点）

ubuntu的desktop版(两台):1.0.0.5=udzyh1（从节点）

1）其实我们一开始的配置（`mysqld.cnf`文件中）是server1——>udzyh1：

在主节点中：

    　　server-id=11
    　　log-bin=mysql-bin-11
    　　binlog-format=rpw

在从节点中： 

    server-id=12
    relay-log=mysql-relay-12

2）我们需要把从节点的配置加到主节点中，主节点的配置加到从节点中

在主节点加上：

    　　relay-log=mysql-relay-11

在从节点上加上： 

    　　lob-bin=mysql-bin-12
    　　binlog-format=row

 当我们重启服务的时候就可以在`/var/lib/mysql`下主节点会生成中继日志文件，而从节点就会生成二进制日志文件了 。我们还是在配置文件

中加上`skip-name-resolve`把反向域名解析关闭，可以加快运行（只是关闭MySQL的）

3）连接

在udzyh1中运行：

    grant replication slave,replication client on *.* to 'zyh'@'%' identified by '123456';(在主节点创建一个用户)

然后在server1中的MySQL终端运行：

![][20]

4）然后在server1中开启主从复制

    start slave

注意：有两个常用的操作

    　　show binary logs;作用和show master status \G一样
    　　show binlog events in 'mysql-bin-11.0000001' \G


## 三、MySQL集群的主主复制的深入探讨

### 3.1、解决主键冲突问题

1）如果为简单的两台节点，可以让第一台节点id自增步长为2 起点为1，让第二台节点id自增步长为2 起点为2   

    set session/ set global auto_increment_increment=2  
    set session / set global auto_increment_offset=1 

2） 利用主键生成程序或者主键服务器 

### 3.2、Mysql 集群的被动主主复制

两台服务器都互为master 但是其中一台为只读服务器，不能插入修改数据。  
在只读服务器的my.conf配置文件中 添加 `read-only=1`(对于拥有super权限的用户，可以ignore这个选项) ,目的主要是为了备份master服务器

![][21]

注意：但是我们一般不会这样做，我们会通过`mysql-proxy`来完成（后面讲解）

### 3.3、节点的部署方式

![][22]

![][23]

不能让一台slave节点，复制多台master节点

![][24]

[0]: http://www.cnblogs.com/zhangyinhua/p/7554574.html
[1]: #_label0
[2]: #_lab2_0_0
[3]: #_lab2_0_1
[4]: #_lab2_0_2
[5]: #_label1
[6]: #_lab2_1_0
[7]: #_lab2_1_1
[8]: #_label2
[9]: #_lab2_2_0
[10]: #_lab2_2_1
[11]: #_lab2_2_2
[12]: #_labelTop
[13]: ./img/301710901.png
[14]: ./img/232034743.png
[15]: ./img/1296590949.png
[16]: ./img/823324333.png
[17]: ./img/2060906254.png
[18]: ./img/1646300885.png
[19]: ./img/1699203140.png
[20]: ./img/1606183235.png
[21]: ./img/2119420422.png
[22]: ./img/1305775919.png
[23]: ./img/653686373.png
[24]: ./img/228136137.png
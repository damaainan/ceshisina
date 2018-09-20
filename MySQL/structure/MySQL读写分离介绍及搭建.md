## MySQL读写分离介绍及搭建

来源：[https://segmentfault.com/a/1190000003716617](https://segmentfault.com/a/1190000003716617)


## MySQL读写分离介绍

MySQL读写分离基本原理是让master数据库处理写操作，slave数据库处理读操作。master将写操作的变更同步到各个slave节点。

![][0]

MySQL读写分离能提高系统性能的原因在于：


* 物理服务器增加，机器处理能力提升。拿硬件换性能。

* 主从只负责各自的读和写，极大程度缓解X锁和S锁争用。

* slave可以配置myiasm引擎，提升查询性能以及节约系统开销。

* master直接写是并发的，slave通过主库发送来的binlog恢复数据是异步。

* slave可以单独设置一些参数来提升其读的性能。

* 增加冗余，提高可用性。



## MySQLProxy介绍

下面使用MySQL官方提供的数据库代理层产品[MySQLProxy][2]搭建读写分离。
[MySQLProxy][2]实际上是在客户端请求与MySQLServer之间建立了一个连接池。所有客户端请求都是发向MySQLProxy，然后经由MySQLProxy进行相应的分析，判断出是读操作还是写操作，分发至对应的MySQLServer上。对于多节点Slave集群，也可以起做到 **`负载均衡`** 的效果。

![][1]
## MySQL读写分离配置

MySQL环境准备

master  192.168.1.5

slave   192.168.1.6

proxy   192.168.1.2

MySQL：5.5.37

MySQL-proxy：mysql-proxy-0.8.4-linux-rhel5-x86-64bit.tar.gz
#### 创建用户并分配权限

``` 
    mysql> create user libai identified by 'libai';
    mysql> grant all on *.* to libai@'192.168.1.%' identified by 'libai';
```

在配置了MySQL复制，以上操作在master执行会同步到slave节点。
#### 启用MySQL复制

MySQL复制配置请参考[这里][4]

先关闭并清除之前的复制。

``` 
    mysql> stop slave;
    mysql> reset slave all;
```

启用新的复制同步。启用之前需要清除日志

``` 
mysql> change master to master_host='192.168.1.5',master_user='libai',master_password='libai',master_port=3306,master_log_file='mysql-bin.000001',master_log_pos=0;

```

主库

``` 
    # mysql -h localhost -ulibai -plibai
    mysql> create database d;
    mysql> use d;
    mysql> create table t(i int);
    mysql> insert into t values(1);
```

从库

``` 
    mysql> select * from t;
    +------+
    | i    |
    +------+
    |    1 |

```
#### 启用MySQLProxy代理服务器

代理服务器上创建mysql用户

``` 
    # groupadd mysql
    # useradd -g mysql mysql

```

解压启动mysql-proxy

``` 
    # ./mysql-proxy --daemon --log-level=debug --user=mysql --keepalive --log-file=/var/log/mysql-proxy.log --plugins="proxy" --proxy-backend-addresses="192.168.1.5:3306" --proxy-read-only-backend-addresses="192.168.1.6:3306" --proxy-lua-script="/root/soft/mysql-proxy/rw-splitting.lua" --plugins=admin --admin-username="admin" --admin-password="admin" --admin-lua-script="/root/soft/mysql-proxy/lib/mysql-proxy/lua/admin.lua"
```

其中proxy-backend-addresses是master服务器，proxy-read-only-backend-addresses是slave服务器。可以通过./mysql-proxy --help 查看详细说明。

查看启动后进程

``` 
    # ps -ef | grep mysql
    root     25721     1  0 11:33 ?        00:00:00 /root/soft/mysql-proxy/libexec/mysql-proxy --daemon --log-level=debug --user=mysql --keepalive --log-file=/var/log/mysql-proxy.log --plugins=proxy --proxy-backend-addresses=192.168.1.5:3306 --proxy-read-only-backend-addresses=192.168.1.6:3306 --proxy-lua-script=/root/soft/mysql-proxy/rw-splitting.lua --plugins=admin --admin-username=admin --admin-password=admin --admin-lua-script=/root/soft/mysql-proxy/lib/mysql-proxy/lua/admin.lua
    mysql    25722 25721  0 11:33 ?        00:00:00 /root/soft/mysql-proxy/libexec/mysql-proxy --daemon --log-level=debug --user=mysql --keepalive --log-file=/var/log/mysql-proxy.log --plugins=proxy --proxy-backend-addresses=192.168.1.5:3306 --proxy-read-only-backend-addresses=192.168.1.6:3306 --proxy-lua-script=/root/soft/mysql-proxy/rw-splitting.lua --plugins=admin --admin-username=admin --admin-password=admin --admin-lua-script=/root/soft/mysql-proxy/lib/mysql-proxy/lua/admin.lua

```

4040是proxy端口，4041是admin管理端口

``` 
    # lsof -i:4040
    COMMAND     PID  USER   FD   TYPE DEVICE SIZE/OFF NODE NAME
    mysql-pro 25722 mysql   10u  IPv4 762429      0t0  TCP *:yo-main (LISTEN)
    # lsof -i:4041
    COMMAND     PID  USER   FD   TYPE DEVICE SIZE/OFF NODE NAME
    mysql-pro 25722 mysql   11u  IPv4 762432      0t0  TCP *:houston (LISTEN)

```
#### 测试

保证mysqlproxy节点上可执行mysql 。通过复制同步帐号连接proxy

``` 
    # mysql -h 192.168.1.2 -ulibai -p --port=4040
    mysql> show databases;
    +--------------------+
    | Database           |
    +--------------------+
    | information_schema |
    | d                  |
    | mysql              |
    | performance_schema |
    | test               |
    +--------------------+

```

登录admin查看状态

``` 
    # mysql -h 192.168.1.2 -u admin -p --port=4041
    mysql> select * from backends;
    +-------------+------------------+-------+------+------+-------------------+
    | backend_ndx | address          | state | type | uuid | connected_clients |
    +-------------+------------------+-------+------+------+-------------------+
    |           1 | 192.168.1.5:3306 | up    | rw   | NULL |                 0 |
    |           2 | 192.168.1.6:3306 | up    | ro   | NULL |                 0 |
    +-------------+------------------+-------+------+------+-------------------+
    2 rows in set (0.00 sec)
```

可以从以上查询中看到master和slave状态均为up。

1）登录proxy节点，创建数据库dufu，并创建一张表t

``` 
    mysql> create database dufu;
    mysql> show databases;
    mysql> use dufu;
    mysql> create table t(id int(10),name varchar(20));
    mysql> show tables;
```

创建完数据库及表后，主从节点上应该都可以看到

2）关闭同步，分别在master和slave上插入数据

``` 
    mysql> slave stop;
```

master

``` 
    mysql> insert into t values(1,'this_is_master');
```

slave

``` 
    mysql> insert into t values(2,'this_is_slave');
```

3）proxy上查看结果

``` 
    mysql> use dufu;
    mysql> select * from t;
    +------+---------------+
    | id   | name          |
    +------+---------------+
    |    2 | this_is_slave |
    +------+---------------+
    1 row in set (0.00 sec)
```

从结果可以看到数据是从slave上读取的，并没考虑master节点上的数据。

直接从proxy上插入数据

``` 
    mysql> insert into t values(3,'this_is_proxy');
```

再次查询

``` 
    mysql> select * from t;
    +------+---------------+
    | id   | name          |
    +------+---------------+
    |    2 | this_is_slave |
    +------+---------------+
```

结果显示查询数据没有变化，因为proxy上执行insert相当于写入到了master上，而查询的数据是从slave上读取的。

master上查询

``` 
    mysql> select * from t;
    +------+----------------+
    | id   | name           |
    +------+----------------+
    |    1 | this_is_master |
    |    3 | this_is_proxy  |
    +------+----------------+

```

启用复制，proxy查询

``` 
    mysql> select * from t;
    +------+----------------+
    | id   | name           |
    +------+----------------+
    |    2 | this_is_slave  |
    |    1 | this_is_master |
    |    3 | this_is_proxy  |
    +------+----------------+
```

说明此时master上的数据同步到了slave，并且在proxy查询到数据是slave数据库的数据。此时，可以看到MySQLProxy实现了分离。

[2]: #
[3]: #

[4]: http://bestvivi.com/2015/09/06/MySQL%E5%A4%8D%E5%88%B6%E4%BB%8B%E7%BB%8D%E5%8F%8A%E6%90%AD%E5%BB%BA/
[0]: ./img/1460000008417784.png
[1]: ./img/1460000005764337.png
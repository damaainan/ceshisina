## MySQL-5.7.18 搭建主从复制

来源：[http://www.dboracle.com/archivers/mysql-5-7-18-搭建主从复制.html](http://www.dboracle.com/archivers/mysql-5-7-18-搭建主从复制.html)

时间 2018-07-05 22:35:18


最近需要做一套MySQL主从复制的升级，因为之前没搭过MySQL主从复制，这次正好现在虚拟机上搭一下。先介绍一下我的环境。

两套Linux主机，都是CentOS 6.9操作系统。Master的IP是192.168.56.161，Slave的IP是192.168.56.2。Master和Salve都安装了MySQL 5.7.18二进制版本，也就是解压能用的免安装版本。我们把主从复制分为两个部分，一部分是Master上的操作，另外一部分是Salve上的操作。


### 一、在Master主机上配置MySQL

因为使用的是免安装版本，所以我们要做的就是先配置好my.cnf文件。在Mysqld中增加下列条目。这里需要注意binlog-do-db是要在Slave上复制的数据库名称。

```
server-id=1
binlog-do-db=test1
relay-log = /usr/local/mysql/data/mysql-relay-bin
relay-log-index=/usr/local/mysql/data/mysql-relay-bin.index
master-info-file=/usr/local/mysql/data/mysql-master.info
relay-log-info-file =/usr/local/mysql/data/mysql-relay-log.info
log-bin=/usr/local/mysql/data/mysql-bin
```

修改完配置文件后重启MySQL服务。

```
[root@Mysql-Master ~]# /etc/init.d/mysql.server restart
Shutting down MySQL.. [ OK ]
Starting MySQL. [ OK ]
```

使用Root用户登录到MySQL数据库中，创建Slave user并赋予REPLICATION复制权限。通过查看Master状态，这里要记住文件(mysql-bin.000002)和位置(595 )。稍后在Slave上需要看到这些信息。来判断同步的情况。

```
mysql> GRANT REPLICATION SLAVE ON *.* TO 'slave_user'@'%' IDENTIFIED BY 'mysql';
Query OK, 0 rows affected, 1 warning (0.00 sec)

mysql> FLUSH PRIVILEGES;
Query OK, 0 rows affected (0.01 sec)

mysql> SHOW MASTER STATUS;
+------------------+----------+--------------+------------------+-------------------+
| File             | Position | Binlog_Do_DB | Binlog_Ignore_DB | Executed_Gtid_Set |
+------------------+----------+--------------+------------------+-------------------+
| mysql-bin.000002 |      595 | test1        |                  |                   |
+------------------+----------+--------------+------------------+-------------------+
1 row in set (0.00 sec)
```

接下来将READ LOCK应用于数据库，然后使用mysqldump进行转储所有数据库和主数据库信息。一旦导完数据，就可以UNLOCK TABLES。

```
mysql> FLUSH TABLES WITH READ LOCK;
Query OK, 0 rows affected (0.00 sec)

[root@Mysql-Master ~]# mysqldump -u root -p --all-databases --master-data > /tmp/dbdump.db
Enter password: 
[root@Mysql-Master tmp]# ls -l dbdump.db 
-rw-r--r--. 1 root root 776461 Jul  5 15:41 dbdump.db

mysql> UNLOCK TABLES;
Query OK, 0 rows affected (0.00 sec)
mysql> quit;
Bye
```

使用scp命令把转储后的文件传输到Slave主机上。


### 二、在Salve主机上配置MySQL

因为Slave我们也是二进制免安装版本的，数据库也已经是创建好的，那么我们先要停止数据库，然后修改/etc/my.cnf。然后在mysqld中配置下列参数。

```
[mysqld]
server-id=2
replicate-do-db=test1
relay-log = /usr/local/mysql/data/mysql-relay-bin
relay-log-index = /usr/local/mysql/data/mysql-relay-bin.index
log-error =/usr/local/mysql/data/mysql.err
master-info-file = /usr/local/mysql/data/mysql-master.info
relay-log-info-file = /usr/local/mysql/data/mysql-relay-log.info
log-bin = /usr/local/mysql/data/mysql-bin
```

但是我这里有一个问题，配置这些参数重启，直接失败。原因是没有配置master-host、master-user、master-password等参数。但是我查了下文档，在5.7.18上配置文件中已经废弃了这些参数，改成在数据库内配置，那么我们这里一个做法就是保留server-id=2这个参数，其他的都不配置，然后启动数据库，进入到数据库中执行change master命令。

```
mysql> stop slave;
Query OK, 0 rows affected (0.02 sec)
mysql> change master to master_host='192.168.56.161', master_user='slave_user', master_password='mysql',MASTER_LOG_FILE='mysql-bin.000002', MASTER_LOG_POS=595;
Query OK, 0 rows affected, 2 warnings (0.03 sec)
mysql> start slave;
Query OK, 0 rows affected, 1 warning (0.00 sec)

mysql> commit;
Query OK, 0 rows affected (0.00 sec)
```

执行完上述命令后，然后关闭数据库，再次配置/etc/my.cnf，将刚刚的配置参数设上，重新启动后就正常了。查看Salve的状态。发现不正常。

```
mysql> show slave status\G
*************************** 1. row ***************************
               Slave_IO_State: Connecting to master
                  Master_Host: 192.168.56.161
                  Master_User: slave_user
                  Master_Port: 3306
                Connect_Retry: 60
              Master_Log_File: 
          Read_Master_Log_Pos: 4
               Relay_Log_File: mysql-relay-bin.000002
                Relay_Log_Pos: 4
        Relay_Master_Log_File: 
             Slave_IO_Running: Connecting
            Slave_SQL_Running: Yes
              Replicate_Do_DB: test1
          Replicate_Ignore_DB: 
           Replicate_Do_Table: 
       Replicate_Ignore_Table: 
      Replicate_Wild_Do_Table: 
  Replicate_Wild_Ignore_Table: 
                   Last_Errno: 0
                   Last_Error: 
                 Skip_Counter: 0
          Exec_Master_Log_Pos: 0
              Relay_Log_Space: 154
              Until_Condition: None
               Until_Log_File: 
                Until_Log_Pos: 0
           Master_SSL_Allowed: No
           Master_SSL_CA_File: 
           Master_SSL_CA_Path: 
              Master_SSL_Cert: 
            Master_SSL_Cipher: 
               Master_SSL_Key: 
        Seconds_Behind_Master: 0
Master_SSL_Verify_Server_Cert: No
                Last_IO_Errno: 2003
                Last_IO_Error: error connecting to master 'slave_user@192.168.56.161:3306' - retry-time: 60  retries: 1
               Last_SQL_Errno: 0
               Last_SQL_Error: 
  Replicate_Ignore_Server_Ids: 
             Master_Server_Id: 0
                  Master_UUID: 
             Master_Info_File: /usr/local/mysql-5.7.18/data/mysql-master.info
                    SQL_Delay: 0
          SQL_Remaining_Delay: NULL
      Slave_SQL_Running_State: Slave has read all relay log; waiting for more updates
           Master_Retry_Count: 86400
                  Master_Bind: 
      Last_IO_Error_Timestamp: 180705 17:28:47
     Last_SQL_Error_Timestamp: 
               Master_SSL_Crl: 
           Master_SSL_Crlpath: 
           Retrieved_Gtid_Set: 
            Executed_Gtid_Set: 
                Auto_Position: 0
         Replicate_Rewrite_DB: 
                 Channel_Name: 
           Master_TLS_Version: 
1 row in set (0.00 sec)
```

这里有一个明显的报错：Last_IO_Error: error connecting to master ‘slave_user@192.168.56.161:3306’ – retry-time: 60  retries: 1，通过搜索相关问题，发现可能是防火墙没有关闭导致的。关闭主库和备库的防火墙之后，恢复正常。

```
[root@Mysql-Slave ~]# /etc/init.d/iptables stop
iptables: Setting chains to policy ACCEPT: filter          [  OK  ]
iptables: Flushing firewall rules:                         [  OK  ]
iptables: Unloading modules:                               [  OK  ]
[root@Mysql-Slave ~]# chkconfig --level 2345 iptables off 

mysql> show slave status\G
*************************** 1. row ***************************
               Slave_IO_State: Waiting for master to send event
                  Master_Host: 192.168.56.161
                  Master_User: slave_user
                  Master_Port: 3306
                Connect_Retry: 60
              Master_Log_File: mysql-bin.000004
          Read_Master_Log_Pos: 154
               Relay_Log_File: mysql-relay-bin.000009
                Relay_Log_Pos: 367
        Relay_Master_Log_File: mysql-bin.000004
             Slave_IO_Running: Yes
            Slave_SQL_Running: Yes
              Replicate_Do_DB: test1
          Replicate_Ignore_DB: 
           Replicate_Do_Table: 
       Replicate_Ignore_Table: 
      Replicate_Wild_Do_Table: 
  Replicate_Wild_Ignore_Table: 
                   Last_Errno: 0
                   Last_Error: 
                 Skip_Counter: 0
          Exec_Master_Log_Pos: 154
              Relay_Log_Space: 740
              Until_Condition: None
               Until_Log_File: 
                Until_Log_Pos: 0
           Master_SSL_Allowed: No
           Master_SSL_CA_File: 
           Master_SSL_CA_Path: 
              Master_SSL_Cert: 
            Master_SSL_Cipher: 
               Master_SSL_Key: 
        Seconds_Behind_Master: 0
Master_SSL_Verify_Server_Cert: No
                Last_IO_Errno: 0
                Last_IO_Error: 
               Last_SQL_Errno: 0
               Last_SQL_Error: 
  Replicate_Ignore_Server_Ids: 
             Master_Server_Id: 1
                  Master_UUID: 942b8e95-8019-11e8-a4d6-0800272f786e
             Master_Info_File: /usr/local/mysql-5.7.18/data/mysql-master.info
                    SQL_Delay: 0
          SQL_Remaining_Delay: NULL
      Slave_SQL_Running_State: Slave has read all relay log; waiting for more updates
           Master_Retry_Count: 86400
                  Master_Bind: 
      Last_IO_Error_Timestamp: 
     Last_SQL_Error_Timestamp: 
               Master_SSL_Crl: 
           Master_SSL_Crlpath: 
           Retrieved_Gtid_Set: 
            Executed_Gtid_Set: 
                Auto_Position: 0
         Replicate_Rewrite_DB: 
                 Channel_Name: 
           Master_TLS_Version: 
1 row in set (0.00 sec)
```


### 三、验证主从同步

在主库上执行:

```
mysql> create database test1; 
mysql> use test1
mysql> create table a1 (id int);
mysql> insert into a1 values(1);
mysql> select * from a1;
+------+
| id   |
+------+
|    1 |
+------+
1 row in set (0.00 sec)
```

在从库上执行:

```
mysql> use test1;
Database changed
mysql> select * from a1;
+------+
| id   |
+------+
|    1 |
+------+
1 row in set (0.00 sec)
```

至此主从同步配置完成！



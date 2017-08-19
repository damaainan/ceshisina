# MySQL timeout相关参数解析和测试

 时间 2017-08-18 08:31:42  

原文[http://www.linuxidc.com/Linux/2017-08/146317.htm][1]


MySQL中有两个关于连接超时的配置项: **`wait_timeout`** 和 **`interactive_timeout`** 。他们之间在某些条件下会互相继承，那究竟这两个参数会在什么情况下起作用呢？ 本文将会通过一些测试实例来证明总结两者的相互关系。 

### 参数介绍

#### interactive_timeout

The number of seconds the server waits for activity on an interactive connection before closing it. An interactive client is defined as a client that uses the CLIENT_INTERACTIVE option to mysql_real_connect() . See alsowait_timeout. 

#### wait_timeout

The number of seconds the server waits for activity on a noninteractive connection before closing it. Before MySQL 5.1.41, this timeout applies only to TCP/IP connections, not to connections made through Unix socket files, named pipes, or shared memory. On thread startup, the session wait_timeout value is initialized from the global wait_timeout value or from the global interactive_timeout value, depending on the type of client (as defined by the CLIENT_INTERACTIVE connect option to mysql_real_connect() ). See also interactive_timeout. 

#### CLIENT_INTERACTIVE

Permit interactive_timeout seconds (instead of wait_timeout seconds) of inactivity before closing the connection. The client’s session wait_timeout variable is set to the value of the session interactive_timeout variable. 

简单的说 interactive就是交互式的终端，例如在shell里面直接执行mysql，出现形如 mysql> 的提示符后就是交互式的连接。而mysql -e ‘select 1’ 这样的直接返回结果的方式就是非交互式的连接。 

### 测试及验证

#### 继承关系

**Q：通过socket连接 timeout会从哪个global timeout继承__A：由下例可见，通过socket登录，timeout 继承于global.interactive_timeout;**

```sql
    mysql> set global interactive_timeout =  11111;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> set global wait_timeout = 22222;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> show global variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | connect_timeout            | 10       |
    | delayed_insert_timeout     | 300      |
    | innodb_lock_wait_timeout   | 50       |
    | innodb_rollback_on_timeout | OFF      |
    | interactive_timeout        | 11111    |
    | lock_wait_timeout          | 31536000 |
    | net_read_timeout           | 30       |
    | net_write_timeout          | 60       |
    | slave_net_timeout          | 3600     |
    | wait_timeout               | 22222    |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
    
    mysql -uroot -ppassword -S /usr/local/mysql3310/mysql.sock
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 4
    Server version: 5.5.16-log MySQL Community Server (GPL)
    
    Copyright (c) 2000, 2011, [Oracle][3] and/or its affiliates. All rights reserved.
    
    Oracle is a registered trademark of Oracle Corporation and/or its
    affiliates. Other names may be trademarks of their respective
    owners.
    
    Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.
    
    mysql> show session variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | connect_timeout            | 10       |
    | delayed_insert_timeout     | 300      |
    | innodb_lock_wait_timeout   | 50       |
    | innodb_rollback_on_timeout | OFF      |
    | interactive_timeout        | 11111    |
    | lock_wait_timeout          | 31536000 |
    | net_read_timeout           | 30       |
    | net_write_timeout          | 60       |
    | slave_net_timeout          | 3600     |
    | wait_timeout               | 11111    |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
```

**Q：通过TCP/IP client 连接， timeout会从哪个global timeout继承__A：由下例可见，通过TCP/IP client 连接后的 wait_timeout 仍然继承于 global.interactive_timeout**

```sql
    mysql -uroot -ppassword -h 127.0.0.1 --port 3310
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 6
    Server version: 5.5.16-log MySQL Community Server (GPL)
    
    Copyright (c) 2000, 2011, Oracle and/or its affiliates. All rights reserved.
    
    Oracle is a registered trademark of Oracle Corporation and/or its
    affiliates. Other names may be trademarks of their respective
    owners.
    
    Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.
    
    mysql> show session variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | connect_timeout            | 10       |
    | delayed_insert_timeout     | 300      |
    | innodb_lock_wait_timeout   | 50       |
    | innodb_rollback_on_timeout | OFF      |
    | interactive_timeout        | 11111    |
    | lock_wait_timeout          | 31536000 |
    | net_read_timeout           | 30       |
    | net_write_timeout          | 60       |
    | slave_net_timeout          | 3600     |
    | wait_timeout               | 11111    |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
```

### 起效关系

**Q：timeout值，对于处于运行状态SQL语句是否起效（即是否等价于执行超时）？__A：由下例可见SQL正在执行状态的等待时间不计入timeout时间。即SQL运行再久也不会因为timeout的配置而中断**

```sql
    mysql> set session wait_timeout=10;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> set session interactive_timeout=10;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select 1,sleep(20) from dual;
    +---+-----------+
    | 1 | sleep(20) |
    +---+-----------+
    | 1 |         0 |
    +---+-----------+
    1 row in set (20.00 sec)
    
    mysql> 
    mysql> show session variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | connect_timeout            | 10       |
    | delayed_insert_timeout     | 300      |
    | innodb_lock_wait_timeout   | 50       |
    | innodb_rollback_on_timeout | OFF      |
    | interactive_timeout        | 10       |
    | lock_wait_timeout          | 31536000 |
    | net_read_timeout           | 30       |
    | net_write_timeout          | 60       |
    | slave_net_timeout          | 3600     |
    | wait_timeout               | 10       |
    +----------------------------+----------+
```

**Q：同一个session中， wait_timeout 和 interacitve_timeout 是否都会生效。__A：只有 wait_timeout 会真正起到超时限制的作用**

```sql
    mysql> set session interactive_timeout=10;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> set session wait_timeout=20;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> show full processlist;
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host            | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  1 | system user |                 | NULL | Connect | 103749 | Slave has read all relay log; waiting for the slave I/O thread to update it | NULL                  |         0 |             0 |         1 |
    |  2 | system user |                 | NULL | Connect | 103750 | Connecting to master                                                        | NULL                  |         0 |             0 |         1 |
    |  3 | root        | localhost       | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    | 10 | root        | localhost:58946 | NULL | Sleep   |     20 |                                                                             | NULL                  |         0 |             0 |        11 |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    4 rows in set (0.00 sec)
    
    mysql> show full processlist;
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host      | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  1 | system user |           | NULL | Connect | 103749 | Slave has read all relay log; waiting for the slave I/O thread to update it | NULL                  |         0 |             0 |         1 |
    |  2 | system user |           | NULL | Connect | 103750 | Connecting to master                                                        | NULL                  |         0 |             0 |         1 |
    |  3 | root        | localhost | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    3 rows in set (0.00 sec)
```

**Q：global timeout和session timeout是否都会作为超时判断依据？__A：只有session级别 timeout 会起作用。即一个session开始后，无论如何��改global级别的timeout都不会影响该session**

* 测试1：

```sql
    mysql> set session interactive_timeout = 10;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> set session wait_timeout = 10;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> show session variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | interactive_timeout        | 10       |
    | wait_timeout               | 10       |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
    
    mysql> show global variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | interactive_timeout        | 20       |
    | wait_timeout               | 20       |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
    
    
    mysql> show full processlist;
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host            | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  3 | root        | localhost       | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    | 17 | root        | localhost:60585 | NULL | Sleep   |     10 |                                                                             | NULL                  |        10 |            10 |        11 |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    2 rows in set (0.00 sec)
    
    mysql> show full processlist;
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host      | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  3 | root        | localhost | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    1 rows in set (0.00 sec)
```

* 测试2：

```sql
    mysql> show session variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | interactive_timeout        | 20       |
    | wait_timeout               | 20       |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
    
    mysql> show global variables like '%timeout%';
    +----------------------------+----------+
    | Variable_name              | Value    |
    +----------------------------+----------+
    | interactive_timeout        | 10       |
    | wait_timeout               | 10       |
    +----------------------------+----------+
    10 rows in set (0.00 sec)
    
    
    
    mysql> show full processlist;
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host            | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  3 | root        | localhost       | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    | 19 | root        | localhost:50276 | NULL | Sleep   |     19 |                                                                             | NULL                  |        10 |            10 |        11 |
    +----+-------------+-----------------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    2 rows in set (0.00 sec)
    
    mysql> show full processlist;
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    | Id | User        | Host      | db   | Command | Time   | State                                                                       | Info                  | Rows_sent | Rows_examined | Rows_read |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    |  3 | root        | localhost | NULL | Query   |      0 | NULL                                                                        | show full processlist |         0 |             0 |        11 |
    +----+-------------+-----------+------+---------+--------+-----------------------------------------------------------------------------+-----------------------+-----------+---------------+-----------+
    1 rows in set (0.00 sec)
```

### 总结

由以上的阶段测试可以获得以下结论。

1. 超时时间只对非活动状态的connection进行计算。
1. 超时时间只以session级别的 wait_timeout 为超时依据，global级别只决定session初始化时的超时默认值。
1. 交互式连接的 wait_timeout 继承于global的 interactive_timeout 。非交互式连接的 wait_timeout 继承于global的 wait_timeout
1. 继承关系和超时对 TCP/IP 和 Socket 连接均有效果


[1]: http://www.linuxidc.com/Linux/2017-08/146317.htm

[3]: http://www.linuxidc.com/topicnews.aspx?tid=12
# MySQL运维实战（一）之 系统变量潜规则

 时间 2017-08-07 19:05:36  

原文[http://keithlan.github.io/2017/08/07/mysql_system_variable/][2]


## Agenda

1. 踩坑经历
1. 测试用例
1. 结论
1. 实战用途

## 一、踩坑经历

1. 设置了slow log 的时间，但是抓不到正确的sql
1. 设置了read_only ，为啥还有写入进来
1. 设置了sql_safe_update , 为啥还能全表删除
1. 测试方法的不对，导致设置了read_only后，有的时候可以insert，有的时候不可以insert

太多这样的问题, 所以打算一窥究竟

## 二、测试用例

测试设置参数后，是否会生效

### 2.1 官方文档说明

[https://dev.mysql.com/doc/refman/5.7/en/set-variable.html][5]
```
    * 重点说明
    
    If you change a session system variable, the value remains in effect within your session until you change the variable to a different value or the session ends. The change has no effect on othersessions.
    
    If you change a global system variable, the value is remembered and used for new sessions until you change the variable to a different value or the server exits. The change is visible to any client that accesses the global variable. However, the change affects the corresponding session variable only for clients that connect after the change. The global variable change does not affect the session variable for any current client sessions (not even the session within which the SET GLOBAL statement occurred).
    
    官方重点说明，设置global变量的时候，只对后面连接进来的session生效，对当前session和之前的session不生效  
    接下来，我们好好测试下
```

### 2.2 系统变量的Scope
```
    1. Global : 全局级别
        set global variables= xx;  --正确
        set variables= xx; --报错 （因为是scope=Global，所以不能设置session变量 ）
    
    2. Session : 会话级别
        set variables= xx; --正确
        set global variables= xx;  --报错 （因为是Scope=session，所以不能设置Global变量）
    
    3. Both : 两者皆可  
        3.1 Global : set global variables= xx; --正确（因为是scope=both，他既可以设置全局变量，也可以设置session变量）
        3.2 Session : set variables= xx;  --正确（因为是scope=both，他既可以设置全局变量，也可以设置session变量）
```

### 2.3 Session 级别测试
```
    1. session 级别的变量代表：sql_log_bin  
    2. 该类型的变量，设置后，只会影响当前session，其他session不受影响
```

## 2.4 Global 级别测试

* 变量代表
```
    1. Global 级别的变量代表：read_only , log_queries_not_using_indexes
```

* 测试一
```
    * processlist_id = 100:
        
    lc_rx:lc> select @@global.log_queries_not_using_indexes;
    +----------------------------------------+
    | @@global.log_queries_not_using_indexes |
    +----------------------------------------+
    | 0 |
    +----------------------------------------+
    1 row in set (0.00 sec) 
    
    
    lc_rx:lc> select * from lc_1;
    +------+
    | id |
    +------+
    |    1 |
    |    2 |
    |    3 |
    |    4 |
    | 5 |
    +------+
    5 rows in set (0.00 sec)
    
    
    此时查看slow log，并未发现任何slow
    
    
    * processlist_id = 120:
        
    dba:(none)> set global log_queries_not_using_indexes=on;
    Query OK, 0 rows affected (0.00 sec)
    
    * processlist_id = 100:
    
    lc_rx:lc> select @@global.log_queries_not_using_indexes;
    +----------------------------------------+
    | @@global.log_queries_not_using_indexes |
    +----------------------------------------+
    | 1 |
    +----------------------------------------+
    1 row in set (0.00 sec)
    
    
    lc_rx:lc> select * from lc_1;
    +------+
    | id |
    +------+
    |    1 |
    |    2 |
    |    3 |
    |    4 |
    | 5 |
    +------+
    5 rows in set (0.00 sec)
    
    此时，去发现slow log
    
    # Time: 2017-08-04T16:05:04.303005+08:00
    # User@Host: lc_rx[lc_rx] @ localhost []  Id:   296
    # Query_time: 0.000149 Lock_time: 0.000081 Rows_sent: 5 Rows_examined: 5
    SET timestamp=1501833904;
    select * from lc_1;
    
    
    * 结论
     说明全局参数变量不管是在session前，还是session后设置，都是立马让所有session生效
```

* 测试二
```
    dba:(none)> show processlist;
    +-----+-------+----------------------+------+------------------+---------+---------------------------------------------------------------+------------------+
    | Id | User | Host | db | Command | Time | State | Info |
    +-----+-------+----------------------+------+------------------+---------+---------------------------------------------------------------+------------------+
    | 303 | lc_rx | localhost | lc | Sleep | 83 | | NULL |
    | 304 | dba   | localhost            | NULL | Query            |       0 | starting                                                      | show processlist |
    +-----+-------+----------------------+------+------------------+---------+---------------------------------------------------------------+------------------+
    3 rows in set (0.00 sec)
    
    * PROCESSLIST_ID=303
    
    lc_rx:lc> select @@global.read_only;
    +--------------------+
    | @@global.read_only |
    +--------------------+
    | 0 |
    +--------------------+
    1 row in set (0.00 sec)
    
    
    lc_rx:lc> insert into lc_1 select 2;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    
    * PROCESSLIST_ID=304
    
    dba:(none)> set global read_only=on;
    Query OK, 0 rows affected (0.00 sec)
    
    
    * PROCESSLIST_ID=303
    
    lc_rx:lc> select @@global.read_only;
    +--------------------+
    | @@global.read_only |
    +--------------------+
    | 1 |
    +--------------------+
    1 row in set (0.00 sec)
    
    lc_rx:lc> insert into lc_1 select 3;
    ERROR 1290 (HY000): The MySQL server is running with the --read-only option so it cannot execute this statement
    
    * 结论：
     PROCESSLIST_ID=304 设置的参数，导致PROCESSLIST_ID=303 也生效了
```

## 2.5 如何查看当下所有session中的系统变量值呢？

5.7 可以看到

遗憾的是：只能看到Both和session的变量，scope=global没法看(因为会立即生效)
```
    dba:(none)> select * from performance_schema.variables_by_thread as a,\
        ->     (select THREAD_ID,PROCESSLIST_ID,PROCESSLIST_USER,PROCESSLIST_HOST,PROCESSLIST_COMMAND,PROCESSLIST_STATE from performance_schema.threads where PROCESSLIST_USER<>'NULL') as b\
        ->         where a.THREAD_ID = b.THREAD_ID and a.VARIABLE_NAME = 'sql_safe_updates';
    +-----------+------------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    | THREAD_ID | VARIABLE_NAME | VARIABLE_VALUE | THREAD_ID | PROCESSLIST_ID | PROCESSLIST_USER | PROCESSLIST_HOST | PROCESSLIST_COMMAND | PROCESSLIST_STATE |
    +-----------+------------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    |       313 | sql_safe_updates | OFF            |       313 |            232 | repl             | xx.xxx.xxx.xxx   | Binlog Dump GTID    | Master has sent all binlog to slave; waiting for more updates |
    | 381 | sql_safe_updates | ON | 381 | 300 | dba | localhost | Query | Sending data |
    +-----------+------------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    2 rows in set (0.00 sec)
```

## 2.6 Both 级别测试

用我们刚刚学到的知识，来验证更加快速和靠谱

* 变量代表
```
    1. Both 级别的变量代表：sql_safe_updates , long_query_time
```

* 测试
```
    * 第一次查看long_query_time参数，PROCESSLIST_ID=307，308，309 都是一样的，都是300s
    
    dba:(none)> select * from performance_schema.variables_by_thread as a, (select THREAD_ID,PROCESSLIST_ID,PROCESSLIST_USER,PROCESSLIST_HOST,PROCESSLIST_COMMAND,PROCESSLIST_STATE from performance_schema.threads where PROCESSLIST_USER<>'NULL') as b where a.THREAD_ID = b.THREAD_ID and a.VARIABLE_NAME = 'long_query_time';
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    | THREAD_ID | VARIABLE_NAME | VARIABLE_VALUE | THREAD_ID | PROCESSLIST_ID | PROCESSLIST_USER | PROCESSLIST_HOST | PROCESSLIST_COMMAND | PROCESSLIST_STATE |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    |       388 | long_query_time | 300.000000     |       388 |            307 | dba              | localhost        | Sleep               | NULL                                                          |
    |       389 | long_query_time | 300.000000     |       389 |            308 | dba              | localhost        | Query               | Sending data                                                  |
    | 390 | long_query_time | 300.000000 | 390 | 309 | dba | localhost | Sleep | NULL |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    4 rows in set (0.00 sec)
    
    
    * 我们再PROCESSLIST_ID=308的session上进行设置long_query_time=100，我们能看到这个时候所有的session都还是300，没有生效
    
    dba:(none)> set global long_query_time=100;
    Query OK, 0 rows affected (0.00 sec)
    
    dba:(none)> select * from performance_schema.variables_by_thread as a, (select THREAD_ID,PROCESSLIST_ID,PROCESSLIST_USER,PROCESSLIST_HOST,PROCESSLIST_COMMAND,PROCESSLIST_STATE from performance_schema.threads where PROCESSLIST_USER<>'NULL') as b where a.THREAD_ID = b.THREAD_ID and a.VARIABLE_NAME = 'long_query_time';
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    | THREAD_ID | VARIABLE_NAME | VARIABLE_VALUE | THREAD_ID | PROCESSLIST_ID | PROCESSLIST_USER | PROCESSLIST_HOST | PROCESSLIST_COMMAND | PROCESSLIST_STATE |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    |       388 | long_query_time | 300.000000     |       388 |            307 | dba              | localhost        | Sleep               | NULL                                                          |
    |       389 | long_query_time | 300.000000     |       389 |            308 | dba              | localhost        | Query               | Sending data                                                  |
    | 390 | long_query_time | 300.000000 | 390 | 309 | dba | localhost | Sleep | NULL |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    4 rows in set (0.00 sec)
    
    * 接下来，我们再断开309，重连时，processlist id 应该是310，这时候的结果就是100s了。这一点说明，在执行set global参数后进来的session才会生效，对当前session和之前的session不生效  
    
    dba:(none)> select * from performance_schema.variables_by_thread as a, (select THREAD_ID,PROCESSLIST_ID,PROCESSLIST_USER,PROCESSLIST_HOST,PROCESSLIST_COMMAND,PROCESSLIST_STATE from performance_schema.threads where PROCESSLIST_USER<>'NULL') as b where a.THREAD_ID = b.THREAD_ID and a.VARIABLE_NAME = 'long_query_time';
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    | THREAD_ID | VARIABLE_NAME | VARIABLE_VALUE | THREAD_ID | PROCESSLIST_ID | PROCESSLIST_USER | PROCESSLIST_HOST | PROCESSLIST_COMMAND | PROCESSLIST_STATE |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    |       388 | long_query_time | 300.000000     |       388 |            307 | dba              | localhost        | Sleep               | NULL                                                          |
    |       389 | long_query_time | 300.000000     |       389 |            308 | dba              | localhost        | Query               | Sending data                                                  |
    | 391 | long_query_time | 100.000000 | 391 | 310 | dba | localhost | Sleep | NULL |
    +-----------+-----------------+----------------+-----------+----------------+------------------+------------------+---------------------+---------------------------------------------------------------+
    4 rows in set (0.00 sec)
```

## 三、结论

官方文档也不是很靠谱，也有很多差强人意的地方

自己动手，测试验证的时候做好测试方案和计划，以免遗漏导致测试失败，得出错误的结论

![][6]

![][7]

## 四、实战意义

### 4.1 项目背景
```
    a. 修改sql_safe_update=on, 这里面有很多难点，其中的一个难点就是如何让所有session生效
```

### 4.2 解决方案

* MySQL5.7+
```
    结合今天的知识，通过performance_schema.variables_by_thread，performance_schema.threads表，可以知道哪些变量已经生效，哪些变量还没生效
```

* MySQL5.7-
```
    1. 如果对今天的Both变量知识理解了，不难发现，还有一个变通的办法
    
    2. 执行这条命令即可
        2.1 set global$both_scope_variables =on|off
        2.2 select max(ID) from information_schema.PROCESSLIST;
    
    3. kill掉所有小于processlist<max(ID) 的session即可
        3.1 当然，系统用户进程你不能kill，read_only的用户你没必要kill
        3.2 其他的自行脑补
```

[2]: http://keithlan.github.io/2017/08/07/mysql_system_variable/

[5]: https://dev.mysql.com/doc/refman/5.7/en/set-variable.html
[6]: https://img1.tuicool.com/IRjAbaM.png
[7]: https://img2.tuicool.com/MJNrYbR.png
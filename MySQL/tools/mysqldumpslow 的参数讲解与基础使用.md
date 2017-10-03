## mysqldumpslow 的参数讲解与基础使用

## 目录

* [认识 mysqldumpslow][0]
* [mysqldumpslow 的命令参数][1]
* [mysqldumpslow 的结果参数][2]

## 认识 mysqldumpslow

mysqldumpslow 是一个针对于 MySQL 慢查询的命令行程序。在配置 MySQL 相关参数后，可以通过 mysqldumpslow 查找出查询较慢的 SQL 语句。

进入 MySQL 命令行，执行命令 `mysql> show variables like "%query%" ;`


    mysql> mysql> show variables like "%quer%" ;
    +----------------------------------------+-----------------------------------------------------+
    | Variable_name                          | Value                                               |
    +----------------------------------------+-----------------------------------------------------+
    | binlog_rows_query_log_events           | OFF                                                 |
    | ft_query_expansion_limit               | 20                                                  |
    | have_query_cache                       | YES                                                 |
    | log_queries_not_using_indexes          | OFF                                                 |
    | log_throttle_queries_not_using_indexes | 0                                                   |
    | long_query_time                        | 10.000000                                           |
    | query_alloc_block_size                 | 8192                                                |
    | query_cache_limit                      | 1048576                                             |
    | query_cache_min_res_unit               | 4096                                                |
    | query_cache_size                       | 1048576                                             |
    | query_cache_type                       | OFF                                                 |
    | query_cache_wlock_invalidate           | OFF                                                 |
    | query_prealloc_size                    | 8192                                                |
    | slow_query_log                         | OFF                                                 |
    | slow_query_log_file                    | /usr/local/var/mysql/luyiyuandeMacBook-Pro-slow.log |
    +----------------------------------------+-----------------------------------------------------+
    15 rows in set (0.01 sec)
    

与 mysqldumpslow 相关的配置变量

* slow_query_log：是否开启慢查询日志
* long_query_time：是否设置慢查询的 SQL 执行规定时间
* slow_query_log_file：设置慢查询日志记录位置
* log_queries_not_using_indexes：是否设置了把没有索引的记录到慢查询日志

配置变量设置格式如下：

    # 开启慢查询日志
    set global slow_query_log=on;
    
    # 设置没有索引的记录到慢查询日志
    set global log_queries_not_using_indexes=on;
    
    # 设置到慢查询日志的 SQL 执行时间（1 代表 1 秒）
    set global long_query_time=1;
    
    # 设置慢查询日志的存放位置
    set global slow_query_log_file="/Users/LuisEdware/Code/output/mysql-slow.log";
    

设置完毕后，不要重启 MySQL 服务，否则设置会失效，如果想要配置持久生效，需要在 my.ini 配置文件编辑上述变量。

## mysqldumpslow 的命令参数

执行命令 `mysqldumpslow --help`，显示命令参数如下：

    Usage: mysqldumpslow [ OPTS... ] [ LOGS... ]
    
    Parse and summarize the MySQL slow query log. Options are
    
      --verbose    verbose
      --debug      debug
      --help       write this text to standard output
    
      -v           verbose
      -d           debug
      -s ORDER     what to sort by (al, at, ar, c, l, r, t), 'at' is default
                    al: average lock time
                    ar: average rows sent
                    at: average query time
                     c: count
                     l: lock time
                     r: rows sent
                     t: query time
      -r           reverse the sort order (largest last instead of first)
      -t NUM       just show the top n queries
      -a           don't abstract all numbers to N and strings to 'S'
      -n NUM       abstract numbers with at least n digits within names
      -g PATTERN   grep: only consider stmts that include this string
      -h HOSTNAME  hostname of db server for *-slow.log filename (can be wildcard),
                   default is '*', i.e. match all
      -i NAME      name of server instance (if using mysql.server startup script)
      -l           don't subtract lock time from total time
    

命令参数意义如下：

* -v、--verbose

> 在详细模式下运行，打印有关该程序的更多信息。

* -d、--debug

> 在调试模式下运行。

* --help

> 显示帮助信息并退出程序

* -s [sort_type]

> sort_type 是信息排序的依据  
> - al：按平均锁定时间排序  
> - ar：按平均返回行数排序  
> - at：按平均查询时间排序  
> - c：按计数排序  
> - l：按锁定时间排序  
> - r：按返回函数排序  
> - t：按查询时间排序

* -r 「reverse the sort order (largest last instead of first)」

> 倒序信息排序

* -t NUM「just show the top n queries」

> 只显示前 n 个查询

* -a 「Do not abstract all numbers to N and strings to 'S'.」

> TODO

* -n NUM 「abstract numbers with at least n digits within names」

> TODO

* -g PATTERN 「grep: only consider stmts that include this string」

> 根据字符串筛选慢查询日志

* -h HOSTNAME 「hostname of db server for _-slow.log filename (can be wildcard), default is '_', i.e. match all」

> 根据服务器名称选择慢查询日志

* -i NAME 「name of server instance (if using mysql.server startup script)」

> 根据服务器 MySQL 实例名称选择慢查询日志

* -l 「don't subtract lock time from total time」

> 不要将总时间减去锁定时间

## mysqldumpslow 的结果参数

使用 Vim 打开慢查询日志 mysql-slow.log，内容如下:

    /usr/local/opt/mysql/bin/mysqld, Version: 5.7.18 (Homebrew). started with:
    Tcp port: 3306  Unix socket: /tmp/mysql.sock
    Time                 Id Command    Argument
    /usr/local/opt/mysql/bin/mysqld, Version: 5.7.18 (Homebrew). started with:
    Tcp port: 3306  Unix socket: /tmp/mysql.sock
    Time                 Id Command    Argument
    # Time: 2017-06-03T06:47:46.502825Z
    # User@Host: root[root] @ localhost []  Id:     3
    # Query_time: 0.195360  Lock_time: 0.000131 Rows_sent: 10000  Rows_examined: 10000
    use bingoshuiguo;
    SET timestamp=1496472466;
    select * from z_order limit 10000;
    # Time: 2017-06-03T06:48:27.030315Z
    # User@Host: root[root] @ localhost []  Id:     3
    # Query_time: 1.896889  Lock_time: 0.000823 Rows_sent: 100000  Rows_examined: 100000
    SET timestamp=1496472507;
    select * from z_order limit 100000;
    # Time: 2017-06-03T06:53:37.786379Z
    # User@Host: root[root] @ localhost []  Id:     3
    # Query_time: 3.456264  Lock_time: 0.008454 Rows_sent: 100000  Rows_examined: 200000
    SET timestamp=1496472817;
    select * from z_order left join z_league on z_order.league_id = z_league.id limit 100000;
    # Time: 2017-06-03T07:03:25.615137Z
    # User@Host: root[root] @ localhost []  Id:     3
    # Query_time: 3.837932  Lock_time: 0.000648 Rows_sent: 100000  Rows_examined: 200000
    SET timestamp=1496473405;
    select * from z_order left join z_league on z_order.league_id = z_league.id limit 100000;

其中参数如下：

* SQL 的执行时间：**# Time: 2017-06-03T06:53:37.786379Z**
* SQL 的执行主机：**# User@Host: root[root] @ localhost [] Id: 3**
* SQL 的执行信息：**# Query_time: 3.456264 Lock_time: 0.008454 Rows_sent: 100000 Rows_examined: 200000**
* SQL 的执行时间：**SET timestamp=1496472817;**
* SQL 的执行内容：**select * from z_order left join z_league on z_order.league_id = z_league.id limit 100000;**

执行 mysqldumpslow 的命令 `mysqldumpslow mysql-slow.log`，查看内容如下：

    Reading mysql slow query log from mysql-slow.log
    Count: 2  Time=3.64s (7s)  Lock=0.00s (0s)  Rows=100000.0 (200000), root[root]@localhost
      select * from z_order left join z_league on z_order.league_id = z_league.id limit N
    
    Count: 2  Time=1.05s (2s)  Lock=0.00s (0s)  Rows=55000.0 (110000), root[root]@localhost
      select * from z_order limit N
    

* Count：出现次数,
* Time：执行最长时间和累计总耗费时间
* Lock：等待锁的时间
* Rows：返回客户端行总数和扫描行总数

[0]: #1
[1]: #2
[2]: #3
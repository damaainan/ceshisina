## MySQL中的日志文件解析

2018.08.23 10:59

来源：[https://www.jianshu.com/p/20ffed814148](https://www.jianshu.com/p/20ffed814148)


            
-----

### 前言

日志文件记录了影响MySQL数据库的各种类型活动，MySQL数据库中常见的日志文件有错误日志，二进制日志，慢查询日志和查询日志。下面分别对他们进行介绍。

-----

### 错误日志

错误日志文件对MySQL的启动，运行，关闭过程进行了记录。

```sql
mysql> show variables like 'log_error';
+---------------+---------------------+
| Variable_name | Value               |
+---------------+---------------------+
| log_error     | /var/log/mysqld.log |
+---------------+---------------------+
1 row in set (0.03 sec)

```

可以看到错误日志的路径和文件名，默认情况下错误文件的文件名为服务器的主机名，即：hostname.err。只不过我这里设置的是/var/log/mysqld.log,修改错误日志地址可以在/etc/my.cnf中添加

```sql
# Recommended in standard MySQL setup
sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES

[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

```

当出现MySQL数据库不能正常启动时，第一个必须查找的文件就是错误日志文件，该文件记录了出错信息，能够帮助我们找到问题。

-----

### 慢查询日志

慢查询日志用来记录响应时间超过阈值的SQL语句，所以我们可以设置一个阈值，将运行时间超过该值的所有SQL语句都记录到慢查询日志文件中。该阈值可以通过参数 long_query_time 来设置，默认为10秒。
 **`启动慢查询日志`** 

默认情况下，MySQL数据库并不启动慢查询日志，需要手动将这个参数设为ON，然后启动

```sql
mysql> show variables like "%slow%";
+---------------------------+-------------------------------------------------+
| Variable_name             | Value                                           |
+---------------------------+-------------------------------------------------+
| log_slow_admin_statements | OFF                                             |
| log_slow_slave_statements | OFF                                             |
| slow_launch_time          | 2                                               |
| slow_query_log            | OFF                                             |
| slow_query_log_file       | /var/lib/mysql/iz2zeaf3cg1099kiidi06mz-slow.log |
+---------------------------+-------------------------------------------------+
5 rows in set (0.00 sec)

mysql> set global slow_query_log='ON';
Query OK, 0 rows affected (0.00 sec)


mysql> show variables like "slow_query_log";
+---------------------------+-------------------------------------------------+
| Variable_name             | Value                                           |
+---------------------------+-------------------------------------------------+                                        |
| slow_query_log            | ON                                              |
| slow_query_log_file       | /var/lib/mysql/iz2zeaf3cg1099kiidi06mz-slow.log |
+---------------------------+-------------------------------------------------+
2   rows in set (0.00 sec)

```

但是使用 set global slow_query_log='ON' 开启慢查询日志，只是对当前数据库有效，如果MySQL数据库重启后就会失效。所以如果要永久生效，就要修改配置文件 my.cnf (其他系统变量也是如此)，如下：

```
[mysqld]
slow_query_log=1

```

然后重启MySQL就可以让慢查询日志记录开启了,至于日志文件的路径就是上面slow_query_log_file对应的路径。

-----

### 

设置阈值

```sql
mysql> show variables like 'long_query_time';
+-----------------+-----------+
| Variable_name   | Value     |
+-----------------+-----------+
| long_query_time | 10.000000 |
+-----------------+-----------+
1 row in set (0.00 sec)

```

阈值默认为10秒，我们可以修改阈值大小，比如(当然这还是对当前数据库有效)：

```sql
mysql> set global long_query_time=0.05;
Query OK, 0 rows affected (0.00 sec)

```

设置long_query_time这个阈值之后，MySQL数据库会记录运行时间超过该值的所有SQL语句，但对于运行时间正好等于 long_query_time 的情况，并不会被记录下。而设置 long_query_time为0来捕获所有的查询
 **`参数log_queries_not_using_indexes`** 


* 另一个和慢查询日志有关的参数是 log_queries_not_using_indexes,

如果运行的SQL语句没有使用索引，则MySQL数据库同样会将这条SQL语句记录到慢查询日志文件。首先确认打开了log_queries_not_using_indexes;


```sql
mysql> show variables like 'log_queries_not_using_indexes';
+-------------------------------+-------+
| Variable_name                 | Value |
+-------------------------------+-------+
| log_queries_not_using_indexes | ON    |
+-------------------------------+-------+
1 row in set (0.12 sec)

```

例子，没有用到索引进行查询：

```sql
mysql> explain select * from vote_record_memory where vote_id = 323;
+----+-------------+--------------------+------+---------------+------+---------+------+--------+-------------+
| id | select_type | table              | type | possible_keys | key  | key_len | ref  | rows   | Extra       |
+----+-------------+--------------------+------+---------------+------+---------+------+--------+-------------+
|  1 | SIMPLE      | vote_record_memory | ALL  | NULL          | NULL | NULL    | NULL | 149272 | Using where |
+----+-------------+--------------------+------+---------------+------+---------+------+--------+-------------+
1 row in set (1.56 sec)

```

可以看到是进行了全表扫描； 然后去log日志文件中查看这条SQL已经被标记为慢SQL，因为它没有使用索引。

```sql
# Time: 180817 11:42:59
# User@Host: root[root] @  [117.136.86.151]  Id:  2625
# Query_time: 0.016542  Lock_time: 0.000112 Rows_sent: 142  Rows_examined: 149272
SET timestamp=1534477379;
select * from vote_record_memory where vote_id = 323;

```
### 将日志记录放入表中

MySQL5.1开始可以将慢查询的日志记录放入一张表中，在mysql数据库下，名为slow_log

```sql
| slow_log | CREATE TABLE `slow_log` (
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_host` mediumtext NOT NULL,
  `query_time` time NOT NULL,
  `lock_time` time NOT NULL,
  `rows_sent` int(11) NOT NULL,
  `rows_examined` int(11) NOT NULL,
  `db` varchar(512) NOT NULL,
  `last_insert_id` int(11) NOT NULL,
  `insert_id` int(11) NOT NULL,
  `server_id` int(10) unsigned NOT NULL,
  `sql_text` mediumtext NOT NULL,
  `thread_id` bigint(21) unsigned NOT NULL
) ENGINE=CSV DEFAULT CHARSET=utf8 COMMENT='Slow log' |

```

参数log_output指定了慢查询输出的格式，默认为file,可以将它设置成table，将变成了上面的slow_log中

```sql
mysql> show variables like "log_output";
+---------------+-------+
| Variable_name | Value |
+---------------+-------+
| log_output    | FILE  |
+---------------+-------+
1 row in set (0.19 sec)

```

但是多数情况下这样做没什么必要，这不但对性能有较大影响，而且 MySQL 5.1 在将慢查询记录到文件中时已经支持微秒级别的信息，然而将慢查询记录到表中会导致时间粒度退化为只能到秒级，而秒级别的慢查询日志没有太大的意义

慢查询日志分析工具

mysqldumpslow命令

当越来越多的SQL查询被记录到慢查询日志文件中，这时候直接看日志文件就不容易了，MySQL提供了mysqldumpslow 命令解决:

```
[root@iz2zeaf3cg1099kiidi06mz mysql]# mysqldumpslow iz2zeaf3cg1099kiidi06mz-slow.log

Reading mysql slow query log from iz2zeaf3cg1099kiidi06mz-slow.log
Count: 1  Time=60.02s (60s)  Lock=0.00s (0s)  Rows=149272.0 (149272), root[root]@[117.136.86.151]
  select * from vote_record_memory

Count: 1  Time=14.85s (14s)  Lock=0.00s (0s)  Rows=0.0 (0), root[root]@[117.136.86.151]
  CALL add_vote_memory(N)

Count: 1  Time=1.72s (1s)  Lock=0.00s (0s)  Rows=0.0 (0), root[root]@[117.136.86.151]
  INSERT into vote_record SELECT * from  vote_record_memory

Count: 1  Time=0.02s (0s)  Lock=0.00s (0s)  Rows=142.0 (142), root[root]@[117.136.86.151]
  select * from vote_record_memory where vote_id = N

```

**pt-query-digest 工具

pt-query-digest 是分析MySQL查询日志最有力的工具，该工具功能强大，它可以分析binlog，Generallog，slowlog，也可以通过show processlist或者通过 tcpdump 抓取的MySQL协议数据来进行分析，比 mysqldumpslow 更具体，更完善。以下是使用pt-query-digest的示例:

```
//直接分析慢查询文件
pt-query-digest  slow.log > slow_report.log

```

该工具可以将查询的剖析报告打印出来，可以分析结果输出到文件中，分析过程是先对查询语句的条件进行参数化，然后对参数化以后的查询进行分组统计，统计出各查询的执行时间，次数，占比等，可以借助分析结果找出问题进行优化。

-----

### 查询日志

查看日志记录了所有对 MySQL 数据库请求的信息，不论这些请求是否得到了正确的执行。默认为 主机名.log

```sql
mysql> show variables like "general_log%";
+------------------+--------------------------------------------+
| Variable_name    | Value                                      |
+------------------+--------------------------------------------+
| general_log      | OFF                                        |
| general_log_file | /var/lib/mysql/iz2zeaf3cg1099kiidi06mz.log |
+------------------+--------------------------------------------+
2 rows in set (0.24 sec)   

```

默认情况下不启动查询日志，必须要先开启。

```sql
mysql> set global general_log='ON';
Query OK, 0 rows affected (0.05 sec)

mysql> show variables like "general_log%";
+------------------+--------------------------------------------+
| Variable_name    | Value                                      |
+------------------+--------------------------------------------+
| general_log      | ON                                         |
| general_log_file | /var/lib/mysql/iz2zeaf3cg1099kiidi06mz.log |
+------------------+--------------------------------------------+
2 rows in set (0.11 sec)

```

-----

### 二进制日志

二进制日志记录了对数据库执行更改的所有操作，但是不包括select和show这类操作，因为这类操作对数据本身并没有修改，如果你还想记录select和show操作，那只能使用查询日志了，而不是二进制日志。

此外，二进制还包括了执行数据库更改操作的时间和执行时间等信息。二进制日志主要有以下几种作用:


* **`恢复(recovery):`**  某些数据的恢复需要二进制日志，如当一个数据库全备文件恢复后，我们可以通过二进制的日志进行 point-in-time的恢复
* **`复制(replication):`** 通过复制和执行二进制日志使得一台远程的 MySQL 数据库(一般是slave 或者 standby) 与一台MySQL数据库(一般为master或者primary) 进行实时同步
* **`审计(audit):`** 用户可以通过二进制日志中的信息来进行审计，判断是否有对数据库进行注入攻击

 **`开启二进制日志`** 

通过配置参数 log-bin[=name] 可以启动二进制日志。如果不指定name,则默认二进制日志文件名为主机名，后缀名为二进制日志的序列号

```
[mysqld]
log-bin

```

```sql
mysql> show variables like 'datadir';
+---------------+-----------------+
| Variable_name | Value           |
+---------------+-----------------+
| datadir       | /var/lib/mysql/ |
+---------------+-----------------+
1 row in set (0.00 sec)

```

mysqld-bin.000001即为二进制日志文件，而mysqld-bin.index为二进制的索引文件，为了管理所有的binlog文件，MySQL额外创建了一个index文件，它按顺序记录了MySQL使用的所有binlog文件。如果你想自定义index文件的名称，可以设置 log_bin_index=file参数。

```
-rw-rw---- 1 mysql mysql      120 Aug 21 16:42 mysqld-bin.000001
-rw-rw---- 1 mysql mysql       20 Aug 21 16:42 mysqld-bin.index

```
 **`查看二进制日志文件`** 

对于二进制日志文件来说，不像错误日志文件，慢查询日志文件那样用cat，head, tail等命令可以查看，它需要通过 MySQL 提供的工具 mysqlbinlog。如:

```
[root@iz2zeaf3cg1099kiidi06mz mysql]# mysqlbinlog mysqld-bin.000001
/*!50530 SET @@SESSION.PSEUDO_SLAVE_MODE=1*/;
/*!40019 SET @@session.max_insert_delayed_threads=0*/;
/*!50003 SET @OLD_COMPLETION_TYPE=@@COMPLETION_TYPE,COMPLETION_TYPE=0*/;
DELIMITER /*!*/;
# at 4
#180821 16:42:53 server id 1  end_log_pos 120 CRC32 0x3e55be40  Start: binlog v 4, server v 5.6.39-log created 180821 16:42:53 at startup
# Warning: this binlog is either in use or was not closed properly.
ROLLBACK/*!*/;
BINLOG '
jdB7Ww8BAAAAdAAAAHgAAAABAAQANS42LjM5LWxvZwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAAAAAAAAAAAAAACN0HtbEzgNAAgAEgAEBAQEEgAAXAAEGggAAAAICAgCAAAACgoKGRkAAUC+
VT4=
'/*!*/;
DELIMITER ;
# End of log file
ROLLBACK /* added by mysqlbinlog */;
/*!50003 SET COMPLETION_TYPE=@OLD_COMPLETION_TYPE*/;
/*!50530 SET @@SESSION.PSEUDO_SLAVE_MODE=0*/;

```

-----

### 二进制日志文件配置参数

下面比较简要介绍下二进制日志文件几个重要的配置参数
 **`max_binlog_size`** 

可以通过max_binlog_size参数来限定单个binlog文件的大小(默认1G)
 **`binlog_cache_size`** 

当使用事务的表存储引擎(如InnoDB存储引擎)时，所有未提交(uncommitted)的二进制日志会被记录到一个缓冲中去，等该事务提交(committed)时，直接将缓存中的二进制日志写入二进制日志文件中，而该缓冲的大小由binlog_cache_size决定，默认大小为32K。此外，binlog_cache_size 是基于会话(session)的，当每一个线程开启一个事务时，MySQL会自动分配一个大小为 binlog_cache_size 的缓存

```sql
mysql> show variables like 'binlog_cache_size';
+-------------------+-------+
| Variable_name     | Value |
+-------------------+-------+
| binlog_cache_size | 32768 |
+-------------------+-------+
1 row in set (0.00 sec)

```
 **`sync_binlog`** 

在默认情况下，二进制日志并不是在每次写的时候同步到磁盘。参数 sync_binlog = [N] 表示每写缓冲多少次就同步到磁盘。如果将N设置为1，即 sync_binlog = 1表示采用同步写磁盘的方式来写二进制日志，这时写操作就不用向上面所说的使用操作系统的缓冲来写二进制日志
 **`binlog_format`** 

binlog_format 参数十分重要，它影响了记录二进制日志的格式,分为三种格式:


* statement : 记录的是日志的逻辑SQL语句
* row: 记录表的行更改情况
* mixed: 在此格式下，mysql默认采用statement格式进行二进制日志文件的记录，但是有些情况下使用ROW格式，有以下几种情况:


```
1)表的存储引擎为NDB，这时对表的DML操作都会以ROW格式记录。
2)使用了UUID()、USER()、CURRENT_USER()、FOUND_ROW()、ROW_COUNT()等不确定函数。
3)使用了INSERT DELAY语句。
4)使用了用户定义函数（UDF）。
5)使用了临时表（temporary table）。

```


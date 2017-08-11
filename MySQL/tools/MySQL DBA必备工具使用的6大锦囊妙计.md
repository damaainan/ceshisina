## MySQL DBA必备工具使用的6大锦囊妙计

老张我呢不仅是个金庸迷，还是个三国迷。就是喜欢看后期蜀国诸葛亮与魏国司马懿之间的斗智斗勇。各种锦囊妙计的使用，堪称经典。针对管理MySQL数据库这块，张老师也有很多妙计，今后一一给大家介绍。说回三国，我个人更倾向于蜀国可以统一，但事与愿违，很可惜，最终还是魏国司马炎统一了天下。有人把蜀国失败的原因归结于一个扶不起的刘婵，也有人把原因归结于天命，更有甚者说是"卧龙凤雏得其一"才可得天下，而刘备两人兼得了。现在听听很可笑，其实任何人的命运还都是掌握在自己手中的。

我们要学会尽人事知天命，努力去做好每一件事儿，不放过一个小小的细节。尤其是从事数据库这个领域，更要细致细心。曾经我的一位老师跟我说过，你要学会把你从事的工作，融入到自己的血液当中去。只有真正地爱上它，才能去用心去研究它！

每次老张写博之前，都喜欢说一些心灵鸡汤，不爱听的老铁们，也希望你们见谅！其实就是希望大家能够用心去做每一件事儿，不管在哪个行业，你早晚会成功。


 **今儿给大家分享一篇，关于MySQL DBA必备工具的使用。可以方便帮助我们管理我们的数据库，让我们的工作更高效。**

这款工具是 MySQL 一个重要分支 percona 的，名称叫做 percona-toolkit（一把锋利的瑞士军刀），它呢是一组命令的集合。今儿给大家介绍几个我们在生产环境中最长用到的。

工具包的下载地址：[**https://www.percona.com/downloads/percona-toolkit/LATEST/**][1]

安装过程很简单，先解压：

    tar-zxvf percona-toolkit-3.0.3_x86_64.tar.gz

由于是二进制的包，解压完可以直接进到percona-toolkit-3.0.3/bin目录下使用。

 **锦囊妙计一：**

**pt-online-schema-change**

功能可以在线整理表结构，收集碎片，给大表添加字段和索引。避免出现锁表导致阻塞读写的操作。针对 MySQL 5.7 版本，就可以不需要使用这个命令，直接在线 online DDL 就可以了。

**展现过程如下：**

由于是测试环境，就不创建一张数据量特大的表，主要让大家理解这个过程。

这是表里面数据的情况和表结构

```
mysql> select count(*) from su;
+----------+
| count(*) |
+----------+
|   100000 | 
+----------+
1 row in set (0.03 sec)
mysql> desc su;
+-------+------------------+------+-----+-------------------+-----------------------------+
| Field | Type             | Null | Key | Default           | Extra                       |
+-------+------------------+------+-----+-------------------+-----------------------------+
| id    | int(10) unsigned | NO   | PRI | NULL              | auto_increment              | 
| c1    | int(11)          | NO   |     | 0                 |                             | 
| c2    | int(11)          | NO   |     | 0                 |                             | 
| c3    | int(11)          | NO   |     | 0                 |                             | 
| c4    | int(11)          | NO   |     | 0                 |                             | 
| c5    | timestamp        | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP | 
| c6    | varchar(200)     | NO   |     |                   |                             |
```

在线增加字段的过程：

```
[root@node3 bin]# ./pt-online-schema-change --user=root --password=root123 
--host=localhost  --alter="ADD COLUMN city_id INT" D=test,t=su --execute
 
No slaves found.  See --recursion-method if host node3 has slaves.
Not checking slave lag because no slaves were found and --check-slave-lag was not specified.
Operation, tries, wait:
  analyze_table, 10, 1
  copy_rows, 10, 0.25
  create_triggers, 10, 1
  drop_triggers, 10, 1
  swap_tables, 10, 1
  update_foreign_keys, 10, 1
Altering `test`.`su`...
Creating new table...
Created new table test._su_new OK.
Altering new table...
Altered `test`.`_su_new` OK.
2017-08-10T14:53:59 Creating triggers...
2017-08-10T14:53:59 Created triggers OK.
2017-08-10T14:53:59 Copying approximately 100163 rows...
2017-08-10T14:54:00 Copied rows OK.
2017-08-10T14:54:00 Analyzing new table...
2017-08-10T14:54:00 Swapping tables...
2017-08-10T14:54:00 Swapped original and new tables OK.
2017-08-10T14:54:00 Dropping old table...
2017-08-10T14:54:00 Dropped old table `test`.`_su_old` OK.
2017-08-10T14:54:00 Dropping triggers...
2017-08-10T14:54:00 Dropped triggers OK.
Successfully altered `test`.`su`.
```

查看结果新增了一个 city_id 的字段：

```
mysql> desc su;
+---------+------------------+------+-----+-------------------+-----------------------------+
| Field   | Type             | Null | Key | Default           | Extra                       |
+---------+------------------+------+-----+-------------------+-----------------------------+
| id      | int(10) unsigned | NO   | PRI | NULL              | auto_increment              | 
| c1      | int(11)          | NO   |     | 0                 |                             | 
| c2      | int(11)          | NO   |     | 0                 |                             | 
| c3      | int(11)          | NO   |     | 0                 |                             | 
| c4      | int(11)          | NO   |     | 0                 |                             | 
| c5      | timestamp        | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP | 
| c6      | varchar(200)     | NO   |     |                   |                             | 
| city_id | int(11)          | YES  |     | NULL              |                             | 
+---------+------------------+------+-----+-------------------+-----------------------------+

```

 **锦囊妙计二：**

**pt-query-digest**

功能：现在捕获线上TOP 10 慢 sql 语句。

大家都知道数据库大多数的性能问题是 sql 语句造成的，所以我们要抓住它们这些犯罪分子。及时做相关的优化处理。

展现过程如下：

可以根据时间间隔，来采样慢 sql 语句。since 是可以调整的 sql 语句

    [root@node3 bin]# ./pt-query-digest --since=24h /data/mysql/slow.log > 1.log

查看 sql 报告，总结慢语句有哪些，并可以看针对时间的消耗。

如下只是部分报告过程

```
cat 1.log
# Profile
# Rank Query ID           Response time Calls R/Call  V/M   Item
# ==== ================== ============= ===== ======= ===== ==============
#    1 0x040ADBE3A1EED0A2 16.8901 87.2%     1 16.8901  0.00 CALL insert_su
#    2 0x8E44F4ED46297D4C  1.3013  6.7%     3  0.4338  0.18 INSERT SELECT test._su_new test.su
#    3 0x12E7CAFEA3145EEF  0.7431  3.8%     1  0.7431  0.00 DELETE su
# MISC 0xMISC              0.4434  2.3%     3  0.1478   0.0  <3ITEMS>
 
# Query 1: 0 QPS, 0x concurrency, ID 0x040ADBE3A1EED0A2 at byte 19060 ____
# Scores: V/M = 0.00
# Time range: all events occurred at 2017-08-02 12:12:07
# Attribute    pct   total     min     max     avg     95%  stddev  median
# ============ === ======= ======= ======= ======= ======= ======= =======
# Count          2       1
# Exec time     47     18s     18s     18s     18s     18s       0     18s
# Lock time      0   103us   103us   103us   103us   103us       0   103us
# Rows sent      0       0       0       0       0       0       0       0
# Rows examine   0       0       0       0       0       0       0       0
# Query size     0      21      21      21      21      21       0      21
# String:
# Databases    test
# Hosts        localhost
# Users        root
# Query_time distribution
#   1us
#  10us
# 100us
#   1ms
#  10ms
# 100ms
#    1s
#  10s+  ################################################################
call insert_su(50000)\G

```


可以看到报告中，列举出了一些sql语句响应时间占比情况，和sql语句的执行时间情况。方便我们可以很直观的观察哪些语句有问题。（这里只列举了一条sql）

 **锦囊妙计三：**

**pt-heartbeat**

功能监控主从延迟。监控从库落后主库大概多少时间。

环境介绍：192.168.56.132主库，192.168.56.133从库

**操作如下：**

在主库上执行：

    [root@node3 bin]# ./pt-heartbeat --database test --update 
    --create-table --daemonize -uroot -proot123

test为我监控同步的库，在该库下创建一张监控表heartbeat，后台进程会时时更新这张表。

在从库上执行监控主从同步延迟时间的语句：

master-server-id是主库的server-id, -h（主库ip）

```
[root@node4 bin]# ./pt-heartbeat --master-server-id=1323306
--monitor --database test  -uzs -p123456 -h 192.168.56.132
0.00s [  0.00s,  0.00s,  0.00s ]
0.00s [  0.00s,  0.00s,  0.00s ]
0.00s [  0.00s,  0.00s,  0.00s ]
0.00s [  0.00s,  0.00s,  0.00s ]
0.00s [  0.00s,  0.00s,  0.00s ]
0.00s [  0.00s,  0.00s,  0.00s ]
```

时间是0s，目前没有延迟的出现。

 **锦囊妙计四：**

**pt-table-checksum**

功能检查主从复制一致性

原理：在主上执行检查语句去检查 mysql主从复制的一致性，生成 replace 语句，然后通过复制传递到从库，再通过update 更新 master_src 的值。最后通过检测从上 this_src 和 master_src 的  
值从而判断复制是否一致。

比较test库的差异情况，在主库上面执行：

```
[root@node3 bin]# ./pt-table-checksum --no-check-binlog-format --nocheck-replication-filters
 --databases=test --replicate=test.checksums --host=192.168.56.132 -uzs -p123456
            TS ERRORS  DIFFS     ROWS  CHUNKS SKIPPED    TIME TABLE
08-10T16:01:02      0      0        1       1       0   0.013 test.heartbeat
08-10T16:01:02      0      0        0       1       0   0.015 test.su
08-10T16:01:02      0      0        0       1       0   0.011 test.t
```

可见diff都为0，证明主从的test库没有差异情况。

比较test库哪些表有差异(需要添加replicate-check-only)，在主库上面执行：

```
[root@node3 bin]# ./pt-table-checksum --no-check-binlog-format 
--nocheck-replication-filters --databases=test --replicate=test.checksums  
--replicate-check-only  --host=192.168.56.132 -uzs -p123456
Differences on node4
TABLE CHUNK CNT_DIFF CRC_DIFF CHUNK_INDEX LOWER_BOUNDARY UPPER_BOUNDARY
test.t 1 1 1
```

可见test库下面t这张表主从数据不一致。

 **锦囊妙计五：**

 **pt-slave-restart**

功能：监控主从错误，并尝试重启MySQL主从

注意事项：跳过错误这个命令，解决从库多数据的现象（错误代码1062）。如果从库少数据，还跳过错误，就不能从根儿上解决主从同步的问题了（错误代码1032），就需要先找到缺少的数据是什么了，如果缺少的特别多，建议重新搭建主从环境。

从库出现1062的错误：

```
Slave_IO_Running: Yes
Slave_SQL_Running: No
Last_Errno: 1062
Last_Error: Could not execute Write_rows event on table test.t; 
Duplicate entry '1' for key 'PRIMARY', 
Error_code: 1062; handler error HA_ERR_FOUND_DUPP_KEY; 
the event's master log mysql-bin.000006, end_log_pos 757482

```


需要在从库上面执行：

    [root@node4 bin]# ./pt-slave-restart -uroot -proot123 --error-numbers=1062
    2017-08-10T16:28:12 p=...,u=root node4-relay-bin.000002      751437 1062

跳过错误之后，检查主从结果：

    Slave_IO_Running: Yes
    Slave_SQL_Running: Yes

同步状态又恢复一致了。

 **锦囊妙计六：**

**pt-ioprofile**

功能：方便定位IO问题，可通过IO吞吐量来定位。

```
[root@node3 bin]# ./pt-ioprofile 
Thu Aug 10 16:33:47 CST 2017
Tracing process ID 3907
     total       read     pwrite      write      fsync filename
 13.949355   0.839006   0.000000   0.286556  12.823793 /data/mysql/mysql-bin.000006
  7.454844   0.000000   2.913702   0.000000   4.541142 /data/mysql/ib_logfile0
  0.000193   0.000000   0.000000   0.000193   0.000000 /data/mysql/slow.log
   
read：从文件中读出数据。要读取的文件用文件描述符标识，数据读入一个事先定义好的缓冲区。
 
write：把缓冲区的数据写入文件中。
 
pread：由于lseek和read调用之间，内核可能会临时挂起进程，所以对同步问题造成了问题，
调用pread相当于顺序调用了lseek和read，这两个操作相当于一个捆绑的原子操作。
 
pwrite：由于lseek和write调用之间，内核可能会临时挂起进程，所以对同步问题造成了问题，
调用pwrite相当于顺序调用了lseek 和write，这两个操作相当于一个捆绑的原子操作。
 
fsync：确保文件所有已修改的内容已经正确同步到硬盘上，该调用会阻塞等待直到设备报告IO完成。
 
filename：与磁盘交互的文件名称

```

通过这个报告我们可以看到，哪个文件占用IO的时间比较多，跟磁盘交互最为繁忙，便于锁定IO问题。

因为这个工具集命令很多，今儿先给大家介绍这些比较常用的，其他的一些大家感兴趣可以私下去研究下。

官方地址：[**https://www.percona.com/doc/percona-toolkit/LATEST/index.html**][2]

[0]: http://edu.51cto.com/course/10681.html
[1]: https://www.percona.com/downloads/percona-toolkit/LATEST/
[2]: https://www.percona.com/doc/percona-toolkit/LATEST/index.html
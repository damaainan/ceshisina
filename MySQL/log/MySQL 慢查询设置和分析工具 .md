# MySQL 慢查询设置和分析工具 

  发表于 2016-12-23   | 
<font face=微软雅黑>

测试使用 MySQL 版本为 5.7.13

### MySQL 配置

首先可以通过命令查看是否开启慢查询日志

```sql
mysql> SHOW VARIABLES LIKE 'slow_query%';
+---------------------+-----------------------------------+
| Variable_name       | Value                             |
|---------------------+-----------------------------------|
| slow_query_log      | ON                                |
| slow_query_log_file | /var/log/mysql/slow.log           |
+---------------------+-----------------------------------+
2 rows in set
Time: 0.005s
mysql> SHOW VARIABLES LIKE 'long_query_time%';
+-----------------+---------+
| Variable_name   |   Value |
|-----------------+---------|
| long_query_time |       1 |
+-----------------+---------+
1 row in set
Time: 0.004s
```
`slow_query_log` 表示是否开启慢查询日志，ON 为开启，OFF 为关闭，如果为关闭可以开启。  
`slow_query_log_file` 表示慢查询日志记录文件  
`long_query_time` 表示超过多长时间为慢查询，是一个边界值，单位为秒

### 设置

#### 临时设置

在 MySQL 执行 SQL 语句设置，但是如果重启 MySQL 的话将失效

```
set global slow_query_log = ON;

set long_query_time = 1;
```

#### 永久设置

修改配置文件，重启 MySQL, 这种永久生效

```
my.cnf

[mysqld]

slow_query_log = ON

slow_query_log_file = /var/log/mysql/slow.log

long_query_time = 1
```
### 慢日志格式

```
# Time: 2016-12-22T09:41:17.777439Z

# User@Host: homestead[homestead] @  [192.168.10.1]  Id:  1543

# Query_time: 2.001423  Lock_time: 0.000000 Rows_sent: 1  Rows_examined: 0

use homestead;

SET timestamp=1482399677;

select sleep(2);
```
### 慢查询日志分析工具

#### MySQL 自带工具 mysqldumpslow

使用很简单，可以跟 `-h` 来查看具体的用法。

    mysqldumpslow /var/log/mysql/slow.log

主要功能是, 统计不同慢 sql 下面这些属性：

* 出现次数(Count),
* 执行最长时间(Time),
* 累计总耗费时间(Time),
* 等待锁的时间(Lock),
* 发送给客户端的行总数(Rows),
* 扫描的行总数(Rows),
* 用户以及sql语句本身(抽象了一下格式, 比如 limit 1, 20 用 limit N,N 表示).

讲一下有用的参数：  
`-s` 排序选项：c 查询次数 r 返回记录行数 t 查询时间  
`-t num` 只显示 `top n` 条查询  
其他参数可以使用 `-h` 命令进行查看

## 使用 mysqlsla 工具 [项目地址][0]

`mysqlsla` 工具，功能非常强大。数据报表，非常有利于分析慢查询的原因，包括执行频率，数据量，查询消耗等。  
此工具已停止维护，项目 github 介绍页面推荐使用 `percona-toolkit`，下面有介绍。

    mysqlsla -lt /var/log/mysql/slow.log

#### 使用 percona-toolkit

`percona-toolkit` 是一组高级命令行工具的集合，用来执行各种通过手工执行非常复杂和麻烦的 mysql 和系统任务。这些任务包括：

* 检查master和slave数据的一致性
* 有效地对记录进行归档
* 查找重复的索引
* 对服务器信息进行汇总
* 分析来自日志和tcpdump的查询
* 当系统出问题的时候收集重要的系统信息

安装：

```
1. 下载，可以在官网找最新版本
wget https://www.percona.com/downloads/percona-toolkit/2.2.20/deb/percona-toolkit_2.2.20-1.tar.gz
2. 解压
tar zxvf percona-toolkit_2.2.20-1.tar.gz
3. 安装
perl Makefile.PL
make && make install
```

`percona-toolkit` 工具集下 `pt-query-digest` 命令可以对慢日志进行分析

```
./pt-query-digest  slow.log
# 170ms user time, 20ms system time, 26.18M rss, 76.54M vsz
# Current date: Fri Dec 23 02:20:25 2016
# Hostname: dev
# Files: /var/log/mysql/slow.log
# Overall: 3 total, 1 unique, 0.00 QPS, 0.00x concurrency ________________
# Time range: 2016-12-22T09:40:03 to 2016-12-23T02:19:35
# Attribute          total     min     max     avg     96%  stddev  median
# ============     ======= ======= ======= ======= ======= ======= =======
# Exec time             6s      2s      2s      2s      2s       0      2s
# Lock time              0       0       0       0       0       0       0
# Rows sent              3       1       1       1       1       0       1
# Rows examine           0       0       0       0       0       0       0
# Query size            45      15      15      15      15       0      15
这个部分是总体概要统计信息，对当前 MySQL 的查询性能做一个初步的评估，比如各个指标的最大值(max)，
平均值(min)，95%分布值，中位数(median)，标准偏差(stddev)。这些指标有查询的执行时间（Exec time），
锁占用的时间（Lock time），MySQL 执行器需要检查的行数（Rows examine），
最后返回给客户端的行数（Rows sent），查询的大小 （Query size）。
后面就会把慢查询的 SQL 展示出来，这样就可以去对 SQL 进行分析优化

```

这个部分是总体概要统计信息，对当前 MySQL 的查询性能做一个初步的评估，比如各个指标的最大值(max)，

平均值(min)，95%分布值，中位数(median)，标准偏差(stddev)。这些指标有查询的执行时间（Exec time），

锁占用的时间（Lock time），MySQL 执行器需要检查的行数（Rows examine），

最后返回给客户端的行数（Rows sent），查询的大小 （Query size）。

后面就会把慢查询的 SQL 展示出来，这样就可以去对 SQL 进行分析优化

详情信息查看 [官网][1]，也可以了解其他 percona-toolkit 下的工具。

可以对日志进行分析，在实际开发中是一个十分有效的功能，可以快速的分析定位到系统 SQL 的瓶颈，便于做出更好的优化。

</font>

[0]: https://github.com/daniel-nichter/hackmysql.com
[1]: https://www.percona.com/software/database-tools/percona-toolkit
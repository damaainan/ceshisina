## 慢查询日志分析（mysql）

来源：[http://www.cnblogs.com/peter-yan/p/8670005.html](http://www.cnblogs.com/peter-yan/p/8670005.html)

时间 2018-03-29 19:03:00

 
开启慢查询日志之后，慢查询sql会被存到数据库系统表mysql.slow_log或是文件中，可参考。有两个工具可以帮助我们分析输出报告，分别是mysqldumpslow和pt-query-digest.
 
**`mysqldumpslow`**
 
`mysqldumpslow`是mysql自身提供的日志分析工具，一般在mysql的bin目录下
 
![][0]
 
帮助信息
 
```

 $ mysqldumpslow.pl --help
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

```
 
```

 -s, 是表示按照何种方式排序
     c: 访问计数
  
     l: 锁定时间
  
     r: 返回记录
  
     t: 查询时间
  
     al:平均锁定时间
  
     ar:平均返回记录数
  
     at:平均查询时间
  
 -t, 是top n的意思，即为返回前面多少条的数据；
 -g, 后边可以写一个正则匹配模式，大小写不敏感的；
  
 比如:
 得到返回记录集最多的10个SQL。
 mysqldumpslow -s r -t 10 /database/mysql/mysql06_slow.log
  
 得到访问次数最多的10个SQL
 mysqldumpslow -s c -t 10 /database/mysql/mysql06_slow.log
  
 得到按照时间排序的前10条里面含有左连接的查询语句。
 mysqldumpslow -s t -t 10 -g “left join” /database/mysql/mysql06_slow.log
  
 另外建议在使用这些命令时结合 | 和more 使用 ，否则有可能出现刷屏的情况。
 mysqldumpslow -s r -t 20 /mysqldata/mysql/mysql06-slow.log | more

```
 
如果不能执行，可以先安装perl，然后通过perl mysqldumpslow xxx.log
 
**`pt-query-digest`**参考
 
个人觉的pt-query-digest分析报告比mysqldumpslow好用。`pt-query-digest`可以不仅可以分析slowlog，还可以分析binlog，generallog等。
 
下载安装 <https://www.percona.com/downloads/percona-toolkit/LATEST/>
 
在windows下，下载tar.gz包，解压之后，使用perl命令运行
 
![][1]
 
![][2]
 
帮助信息
 
```


$ perl pt-query-digest --help
pt-query-digest analyzes MySQL queries from slow, general, and binary log files.
It can also analyze queries from C<SHOW PROCESSLIST> and MySQL protocol data
from tcpdump.  By default, queries are grouped by fingerprint and reported in
descending order of query time (i.e. the slowest queries first).  If no C<FILES>
are given, the tool reads C<STDIN>.  The optional C<DSN> is used for certain
options like L<"--since"> and L<"--until">.  For more details, please use the
--help option, or try 'perldoc pt-query-digest' for complete documentation.

Usage: pt-query-digest [OPTIONS] [FILES] [DSN]

Options:

  --ask-pass                   Prompt for a password when connecting to MySQL
  --attribute-aliases=a        List of attribute|alias,etc (default db|Schema)
  --attribute-value-limit=i    A sanity limit for attribute values (default 0)
  --charset=s              -A  Default character set
  --config=A                   Read this comma-separated list of config files;
                               if specified, this must be the first option on
                               the command line
  --[no]continue-on-error      Continue parsing even if there is an error (
                               default yes)
  --[no]create-history-table   Create the --history table if it does not exist (
                               default yes)
  --[no]create-review-table    Create the --review table if it does not exist (
                               default yes)
  --daemonize                  Fork to the background and detach from the shell
  --database=s             -D  Connect to this database
  --defaults-file=s        -F  Only read mysql options from the given file
  --embedded-attributes=a      Two Perl regex patterns to capture pseudo-
                               attributes embedded in queries
  --expected-range=a           Explain items when there are more or fewer than
                               expected (default 5,10)
  --explain=d                  Run EXPLAIN for the sample query with this DSN
                               and print results
  --filter=s                   Discard events for which this Perl code doesn't
                               return true
  --group-by=A                 Which attribute of the events to group by (
                               default fingerprint)
  --help                       Show help and exit
  --history=d                  Save metrics for each query class in the given
                               table. pt-query-digest saves query metrics (
                               query time, lock time, etc.) to this table so
                               you can see how query classes change over time
  --host=s                 -h  Connect to host
  --ignore-attributes=a        Do not aggregate these attributes (default arg,
                               cmd, insert_id, ip, port, Thread_id, timestamp,
                               exptime, flags, key, res, val, server_id,
                               offset, end_log_pos, Xid)
  --inherit-attributes=a       If missing, inherit these attributes from the
                               last event that had them (default db,ts)
  --interval=f                 How frequently to poll the processlist, in
                               seconds (default .1)
  --iterations=i               How many times to iterate through the collect-
                               and-report cycle (default 1)
  --limit=A                    Limit output to the given percentage or count (
                               default 95%:20)
  --log=s                      Print all output to this file when daemonized
  --order-by=A                 Sort events by this attribute and aggregate
                               function (default Query_time:sum)
  --outliers=a                 Report outliers by attribute:percentile:count (
                               default Query_time:1:10)
  --output=s                   How to format and print the query analysis
                               results (default report)
  --password=s             -p  Password to use when connecting
  --pid=s                      Create the given PID file
  --port=i                 -P  Port number to use for connection
  --preserve-embedded-numbers  Preserve numbers in database/table names when
                               fingerprinting queries
  --processlist=d              Poll this DSN's processlist for queries, with --
                               interval sleep between
  --progress=a                 Print progress reports to STDERR (default time,
                               30)
  --read-timeout=m             Wait this long for an event from the input; 0 to
                               wait forever (default 0).  Optional suffix s=
                               seconds, m=minutes, h=hours, d=days; if no
                               suffix, s is used.
  --[no]report                 Print query analysis reports for each --group-by
                               attribute (default yes)
  --report-all                 Report all queries, even ones that have been
                               reviewed
  --report-format=A            Print these sections of the query analysis
                               report (default rusage,date,hostname,files,
                               header,profile,query_report,prepared)
  --report-histogram=s         Chart the distribution of this attribute's
                               values (default Query_time)
  --resume=s                   If specified, the tool writes the last file
                               offset, if there is one, to the given filename
  --review=d                   Save query classes for later review, and don't
                               report already reviewed classes
  --run-time=m                 How long to run for each --iterations.  Optional
                               suffix s=seconds, m=minutes, h=hours, d=days; if
                               no suffix, s is used.
  --run-time-mode=s            Set what the value of --run-time operates on (
                               default clock)
  --sample=i                   Filter out all but the first N occurrences of
                               each query
  --set-vars=A                 Set the MySQL variables in this comma-separated
                               list of variable=value pairs
  --show-all=H                 Show all values for these attributes
  --since=s                    Parse only queries newer than this value (parse
                               queries since this date)
  --slave-password=s           Sets the password to be used to connect to the
                               slaves
  --slave-user=s               Sets the user to be used to connect to the slaves
  --socket=s               -S  Socket file to use for connection
  --timeline                   Show a timeline of events
  --type=A                     The type of input to parse (default slowlog)
  --until=s                    Parse only queries older than this value (parse
                               queries until this date)
  --user=s                 -u  User for login if not current user
  --variations=A               Report the number of variations in these
                               attributes' values
  --version                    Show version and exit
  --[no]version-check          Check for the latest version of Percona Toolkit,
                               MySQL, and other programs (default yes)
  --[no]vertical-format        Output a trailing "\G" in the reported SQL
                               queries (default yes)
  --watch-server=s             This option tells pt-query-digest which server
                               IP address and port (like "10.0.0.1:3306") to
                               watch when parsing tcpdump (for --type tcpdump);
                               all other servers are ignored

Option types: s=string, i=integer, f=float, h/H/a/A=comma-separated list, d=DSN, z=size, m=time

Rules:

  This tool accepts additional command-line arguments. Refer to the SYNOPSIS and usage information for details.

DSN syntax is key=value[,key=value...]  Allowable DSN keys:

  KEY  COPY  MEANING
  ===  ====  =============================================
  A    yes   Default character set
  D    yes   Default database to use when connecting to MySQL
  F    yes   Only read default options from the given file
  P    yes   Port number to use for connection
  S    yes   Socket file to use for connection
  h    yes   Connect to host
  p    yes   Password to use when connecting
  t    no    The --review or --history table
  u    yes   User for login if not current user

  If the DSN is a bareword, the word is treated as the 'h' key.

Options and values after processing arguments:

  --ask-pass                   FALSE
  --attribute-aliases          db|Schema
  --attribute-value-limit      0
  --charset                    (No value)
  --config                     /etc/percona-toolkit/percona-toolkit.conf,/etc/percona-toolkit/pt-query-digest.conf,/c/Users/Admin/.percona-toolkit.conf,/c/Users/Admin/.pt-query-digest.conf
  --continue-on-error          TRUE
  --create-history-table       TRUE
  --create-review-table        TRUE
  --daemonize                  FALSE
  --database                   (No value)
  --defaults-file              (No value)
  --embedded-attributes        (No value)
  --expected-range             5,10
  --explain                    (No value)
  --filter                     (No value)
  --group-by                   fingerprint
  --help                       TRUE
  --history                    (No value)
  --host                       (No value)
  --ignore-attributes          arg,cmd,insert_id,ip,port,Thread_id,timestamp,exptime,flags,key,res,val,server_id,offset,end_log_pos,Xid
  --inherit-attributes         db,ts
  --interval                   .1
  --iterations                 1
  --limit                      95%:20
  --log                        (No value)
  --order-by                   Query_time:sum
  --outliers                   Query_time:1:10
  --output                     report
  --password                   (No value)
  --pid                        (No value)
  --port                       (No value)
  --preserve-embedded-numbers  FALSE
  --processlist                (No value)
  --progress                   time,30
  --read-timeout               0
  --report                     TRUE
  --report-all                 FALSE
  --report-format              rusage,date,hostname,files,header,profile,query_report,prepared
  --report-histogram           Query_time
  --resume                     (No value)
  --review                     (No value)
  --run-time                   (No value)
  --run-time-mode              clock
  --sample                     (No value)
  --set-vars
  --show-all
  --since                      (No value)
  --slave-password             (No value)
  --slave-user                 (No value)
  --socket                     (No value)
  --timeline                   FALSE
  --type                       slowlog
  --until                      (No value)
  --user                       (No value)
  --variations
  --version                    FALSE
  --version-check              TRUE
  --vertical-format            TRUE
  --watch-server               (No value)

```
 
```

pt-query-digest [OPTIONS] [FILES] [DSN]
--create-review-table  当使用--review参数把分析结果输出到表中时，如果没有表就自动创建。
--create-history-table  当使用--history参数把分析结果输出到表中时，如果没有表就自动创建。
--filter  对输入的慢查询按指定的字符串进行匹配过滤后再进行分析
--limit    限制输出结果百分比或数量，默认值是20,即将最慢的20条语句输出，如果是50%则按总响应时间占比从大到小排序，输出到总和达到50%位置截止。
--host  mysql服务器地址
--user  mysql用户名
--password  mysql用户密码
--history 将分析结果保存到表中，分析结果比较详细，下次再使用--history时，如果存在相同的语句，且查询所在的时间区间和历史表中的不同，则会记录到数据表中，可以通过查询同一CHECKSUM来比较某类型查询的历史变化。
--review 将分析结果保存到表中，这个分析只是对查询条件进行参数化，一个类型的查询一条记录，比较简单。当下次使用--review时，如果存在相同的语句分析，就不会记录到数据表中。
--output 分析结果输出类型，值可以是report(标准分析报告)、slowlog(Mysql slow log)、json、json-anon，一般使用report，以便于阅读。
--since 从什么时间开始分析，值为字符串，可以是指定的某个”yyyy-mm-dd [hh:mm:ss]”格式的时间点，也可以是简单的一个时间值：s(秒)、h(小时)、m(分钟)、d(天)，如12h就表示从12小时前开始统计。
--until 截止时间，配合—since可以分析一段时间内的慢查询。

```
 
输出结果分析
 
分为三部分
 
第一部分 总体统计结果
 
overall：总共统计结果
 
time range：查询执行的时间范围
 
unique：唯一查询数量，即对查询条件进行参数化以后，总共有多少个不同的查询
 
total：总计
 
min：最小
 
max：最大
 
avg：平均
 
95%：把所有值从小到大排列，位置位于95%的那个数，这个数一般最具参考价值
 
median：中位数，把所有值从小到大排列，位置位于中间那个数
 
```

# 该工具执行日志分析的用户时间，系统时间，物理内存占用大小，虚拟内存占用大小
# 343ms user time, 78ms system time, 0 rss, 0 vsz
# 工具执行时间
# Current date: Thu Mar 29 15:51:38 2018
# 运行分析工具的主机名
# Hostname: NB2015041602
# 被分析的文件名
# Files: /d/xampp/mysql/data/NB2015041602-slow.log
# 语句总数量，唯一的语句数量，QPS，并发数
# Overall: 5 total, 3 unique, 0.00 QPS, 0.05x concurrency ________________
# 日志记录的时间范围
# Time range: 2018-03-28 14:02:06 to 14:22:10
# 属性               总计      最小    最大    平均    95%  标准    中等
# Attribute          total     min     max     avg     95%  stddev  median
# ============     ======= ======= ======= ======= ======= ======= =======
# 语句执行时间
# Exec time            60s     10s     17s     12s     17s      3s     11s
# 锁占用时间
# Lock time            1ms       0   500us   200us   490us   240us       0
# 发送到客户端的行数
# Rows sent             50      10      10      10      10       0      10
# select语句扫描行数
# Rows examine     629.99k  45.43k 146.14k 126.00k 143.37k  39.57k 143.37k
# 查询的字符数
# Query size         2.81k     235   1.36k  575.40   1.33k  445.36  234.30

```
 
第二部分 查询分组统计结果
 
rank：所有语句的排序，默认按照查询时间降序排序，通过--order-by指定
 
query id：语句的id，（去掉多余空格和文本字符，计算hash值）
 
response：总的响应时间
 
time：该查询在本次分析中总的时间占比
 
calls：执行次数，即本次分析总共有多少条这种类型的查询语句
 
r/call：平均每次执行的响应时间
 
v/m：响应时间variance-to-mean的比率
 
item：查询对象
 
```

# Profile
# Rank Query ID           Response time Calls R/Call  V/M   Item
# ==== ================== ============= ===== ======= ===== ==============
#    1 0x96112A601F7BCCC0 32.9042 55.0%     3 10.9681  0.01 SELECT affiliatemerchant_list user_list
#    2 0x70885F9703A0E38D 17.2162 28.8%     1 17.2162  0.00 SELECT normalmerchant merchant_mapping normalmerchant_addinfo merchant_search_filter affiliatemerchant_list user_list
#    3 0x43D8527285567FC4  9.7367 16.3%     1  9.7367  0.00 SELECT affiliatemerchant_list user_list affiliatemerchant_list user_list

```
 
第三部分 每一种查询的详细统计结果
 
id：查询的id号，和上面的query id对应
 
databases：数据库名
 
users：各个用户执行的次数（占比）
 
query_time_distribution：查询时间分布，长短体现区间占比
 
tables：查询中设计到的表
 
explain：sql语句
 
```

# Query 1: 0.00 QPS, 0.03x concurrency, ID 0x96112A601F7BCCC0 at byte 2647
# This item is included in the report because it matches --limit.
# Scores: V/M = 0.01
# Time range: 2018-03-28 14:03:31 to 14:19:54
# Attribute    pct   total     min     max     avg     95%  stddev  median
# ============ === ======= ======= ======= ======= ======= ======= =======
# Count         60       3
# Exec time     54     33s     11s     11s     11s     11s   243ms     11s
# Lock time     50   500us       0   500us   166us   490us   231us       0
# Rows sent     60      30      10      10      10      10       0      10
# Rows examine  69 438.42k 146.14k 146.14k 146.14k 146.14k       0 146.14k
# Query size    24     707     235     236  235.67  234.30       0  234.30
# String:
# Databases    database_base
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
# Tables
#    SHOW TABLE STATUS FROM `database_base` LIKE 'table_list1'\G
#    SHOW CREATE TABLE `database_base`.`table_list1`\G
#    SHOW TABLE STATUS FROM `database_base` LIKE 'user_list'\G
#    SHOW CREATE TABLE `database_base`.`user_list`\G
# EXPLAIN /*!50100 PARTITIONS*/
select SQL_CALC_FOUND_ROWS al.*, ul.Alias as userName
        FROM table_list1 al
        LEFT JOIN user_list ul ON ul.ID = al.UserId
         WHERE TRUE  AND (al.SupportCountrys LIKE '%%')
        
         limit 80, 10\G

```
 
pt-query-digest用法示例（未测试）
 
```

直接分析慢查询文件
pt-query-digest  slow.log > slow_report.log

分析最近12小时内的查询
pt-query-digest  --since=12h  slow.log > slow_report2.log

分析指定时间范围内的查询
pt-query-digest slow.log --since '2017-01-07 09:30:00' --until '2017-01-07 10:00:00'> > slow_report3.log

分析含有select语句的慢查询
pt-query-digest --filter '$event->{fingerprint} =~ m/^select/i' slow.log> slow_report4.log

针对某个用户的慢查询
pt-query-digest --filter '($event->{user} || "") =~ m/^root/i' slow.log> slow_report5.log

查询所有全表扫描或full join的慢查询
pt-query-digest --filter '(($event->{Full_scan} || "") eq "yes") ||(($event->{Full_join} || "") eq "yes")' slow.log> slow_report6.log

把查询保存到query_review表
pt-query-digest --user=root –password=abc123 --review  h=localhost,D=test,t=query_review--create-review-table  slow.log

把查询保存到query_history表
pt-query-digest  --user=root –password=abc123 --review  h=localhost,D=test,t=query_history--create-review-table  slow.log_0001
pt-query-digest  --user=root –password=abc123 --review  h=localhost,D=test,t=query_history--create-review-table  slow.log_0002

通过tcpdump抓取的tcp协议数据，然后分析
tcpdump -s 65535 -x -nn -q -tttt -i any -c 1000 port 3306 > mysql.tcp.txt
pt-query-digest --type tcpdump mysql.tcp.txt> slow_report9.log

分析biglog
mysqlbinlog mysql-bin.000093 > mysql-bin000093.sql
pt-query-digest  --type=binlog  mysql-bin000093.sql > slow_report10.log

分析general log
pt-query-digest  --type=genlog  localhost.log > slow_report11.log

```
 


[0]: https://img2.tuicool.com/NF3QF3q.png 
[1]: https://img2.tuicool.com/mMJvMzA.png 
[2]: https://img2.tuicool.com/eEr2yiJ.png 
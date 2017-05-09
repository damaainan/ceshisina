# [mysqlsla 分析mysql慢查询日志][0]

发现有一个工具mysqlsla，分析查询日志比 mysqldumpslow分析的会更清晰明了！  
  
安装mysqlsla：  
  
下载mysqlsla-2.03.tar.gz  
  
[root@yoon export]# tar -xvf mysqlsla-2.03.tar.gz  
  
[root@yoon export]# yum install *DBI*  
  
[root@yoon mysqlsla-2.03]# perl Makefile.PL  
Can't locate Time/HiRes.pm in @INC (@INC contains: /usr/local/lib64/perl5 /usr/local/share/perl5 /usr/lib64/perl5/vendor_perl /usr/share/perl5/vendor_perl /usr/lib64/perl5 /usr/share/perl5 .) at /usr/local/bin/mysqlsla line 2095.  
BEGIN failed--compilation aborted at /usr/local/bin/mysqlsla line 2095.  
  
提示报错要安装：  
[root@yoon mysqlsla-2.03]# yum -y install perl-Time-HiRes  
  
1、总的查询次数（queries） 去重后的SQL数量（unique）  
2、输出报表的内容排序：Sorted by 't_sum' 最重大的慢sql统计信息, 包括 平均执行时间, 等待锁时间, 结果行的总数, 扫描的行总数  
3、Count: sql的执行次数及占总的slow log数量的百分比  
4、Time: 执行时间, 包括总时间, 平均时间, 最小, 最大时间, 时间占到总慢sql时间的百分比  
5、95% of Time: 去除最快和最慢的sql, 覆盖率占95%的sql的执行时间  
6、Lock Time: 等待锁的时间  
7、95% of Lock: 95%的慢sql等待锁时间.   
8、Rows sent: 结果行统计数量, 包括平均, 最小, 最大数量  
9、Rows examined: 扫描的行数量   
10、Database: 属于哪个数据库  
11、Users: 哪个用户,IP, 占到所有用户执行的sql百分比  
12、Query abstract: 抽象后的sql语句  
13、Query sample: sql语句  
  
  
参数说明  
-sort  
使用什么参数来对分析结果进行排序，默认是t_sum来进行排序  
t_sum：按总时间排序  
c_sum：按总次数排序  
c_sum_p：SQL语句执行次数占总执行次数的百分比  
  
  
-top  
显示SQL的数量，默认是10，表示按规则取排序的前10条  
  
-db-database  
对应的数据库  
  
-statement-filter：  
过滤SQL语句类型，比如select、update、drop.  
  
  
慢查询日志中，执行时间最长的10条SQL  
mysqlsla -lt slow -sf "+select" -top 10 slow.log > yoon.log  
  
慢查询日志中slow.log的数据库为sakila的所有select和update的慢查询sql，并查询次数最多的100条sql  
mysqlsla -lt slow -sf "+select,update" -top 100 -sort c_sum -db sakila slow.log > yoon.log  
  
取数据库sakila库中的select语句、按照c_sum_p排序的前2条  
mysqlsla -lt slow -sort c_sum_p -sf "+select" -db sakila -top2 /export/servers/mysql/log/slow.log   
  
慢查询日志中，取出执行时间最长的3条SQL语句  
mysqlsla -lt slow --top 3 slow.log  
  
按照总的执行次数  
mysqlsla -lt slow --top 3 --sort c_sum slow.log  
  
  
取出create语句的慢查询  
mysqlsla -lt slow -sf "+create"--top 3 --sort c_sum slow.log > yoon.log

[0]: http://www.cnblogs.com/zengkefu/p/5638902.html
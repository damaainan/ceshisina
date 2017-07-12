# MySQL日志查询分析工具pt-query-digest的安装和使用方法

 时间 2016-11-16 11:30:32  [刘宁个人博客][0]

_原文_[http://www.36nu.com/post/228.html][1]

 主题 [MySQL][2]

pt-query-digest是用于分析mysql慢查询的一个工具，它可以分析binlog、General log、slowlog，也可以通过SHOWPROCESSLIST或者通过tcpdump抓取的MySQL协议数据来进行分析。可以把分析结果输出到文件中，分析过程是先对查询语句的条件进行参数化，然后对参数化以后的查询进行分组统计，统计出各查询的执行时间、次数、占比等，可以借助分析结果找出问题进行优化。pt-query-digest是一个perl脚本，只需下载并赋权即可执行。pt-query-digest包含在percona-toolkit里面，如果已经安装过percona-toolkit则可以直接使用（percona-toolkit安装方法请参考 [Linux系统中percona-toolkit的安装方法][3] ），下面是centos系统中pt-query-digest的单独安装方法 

    # yum install perl-DBI
    # yum install perl-DBD-MySQL
    # yum install perl-Time-HiRes
    # yum install perl-IO-Socket-SSL
    # wget percona.com/get/pt-query-digest 
    # chmod u+x pt-query-digest

pt-query-digest使用方法介绍

#### 直接分析慢查询文件

    # pt-query-digest  slow.log > slow_report.log

#### 分析最近12小时内的查询

    # pt-query-digest  --since=12h  slow.log > slow_report2.log

#### 分析指定时间范围内的查询

    # pt-query-digest slow.log --since '2016-10-17 09:30:00' --until '2016-10-17 10:00:00' > > slow_report3.log

#### 分析指含有select语句的慢查询

    # pt-query-digest--filter '$event->{fingerprint} =~ m/^select/i' slow.log> slow_report4.log

#### 针对某个用户的慢查询

    # pt-query-digest--filter '($event->{user} || "") =~ m/^root/i' slow.log> slow_report5.log

#### 查询所有所有的全表扫描或full join的慢查询

    # pt-query-digest--filter '(($event->{Full_scan} || "") eq "yes") ||(($event->{Full_join} || "") eq "yes")' slow.log> slow_report6.log

#### 把查询保存到query_review表

    # pt-query-digest  --user=root –password=abc123 --review  h=localhost,D=test,t=query_review--create-review-table  slow.log

#### 把查询保存到query_history表

    # pt-query-digest  --user=root –password=abc123 --review  h=localhost,D=test,t=query_ history--create-review-table  slow.log_20161101
    # pt-query-digest  --user=root –password=abc123--review  h=localhost,D=test,t=query_history--create-review-table  slow.log_20161102

#### 通过tcpdump抓取mysql的tcp协议数据，然后再分析

    # tcpdump -s 65535 -x -nn -q -tttt -i any -c 1000 port 3306 > mysql.tcp.txt
    # pt-query-digest --type tcpdump mysql.tcp.txt> slow_report9.log

#### 分析binlog

    # mysqlbinlog mysql-bin.000093 > mysql-bin000093.sql
    # pt-query-digest  --type=binlog  mysql-bin000093.sql > slow_report10.log

#### 分析general log

    # pt-query-digest  --type=genlog  localhost.log > slow_report11.log

更多使用方法请参考 [官方文档][4]

[0]: /sites/eQZbMzY
[1]: http://www.36nu.com/post/228.html
[2]: /topics/11030000
[3]: http://www.36nu.com/post/227.html
[4]: http://www.percona.com/doc/percona-toolkit/2.2/pt-query-digest.html
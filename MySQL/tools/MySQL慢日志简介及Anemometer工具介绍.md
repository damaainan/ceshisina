# MySQL慢日志简介及Anemometer工具介绍

 时间 2017-08-14 11:14:35 

原文[http://fordba.com/box-anemometer-visual-mysql-slow.html][1]


## MySQL 慢日志简介：

MySQL慢日志想必大家或多或少都有听说，主要是用来记录MySQL中长时间执行（超过`long_query_time` 单位秒），同时`examine`的行数超过`min_examined_row_limit` ,影响MySQL性能的SQL语句，以便DBA进行优化。

在MySQL中，如果一个SQL需要长时间等待获取一把锁，那么这段获取锁的时间并不算执行时间，当SQL执行完成，释放相应的锁，才会记录到慢日志中，所以MySQL的慢日志中记录的顺序和实际的执行顺序可能不大一样。

在默认情况下，MySQL的慢日志记录是关闭的,我们可以通过将设置`slow_query_log=1`来打开MySQL的慢查询日志，通过`slow_query_log_file=file_name`来设置慢查询的文件名，如果文件名没有设置，他的默认名字为 `host_name-slow.log`。同时，我们也可以设置 `log-output={FILE|TABLE}`来指定慢日志是写到文件还是数据库里面（如果设置`log-output=NONE`，将不进行慢日志记录，即使`slow_query_log=1`）。 

MySQL的管理维护命令的慢SQL并不会被记录到MySQL慢日志中。常见的管理维护命令包括`ALTER TABLE`,`ANALYZE TABLE`, `CHECK TABLE`, `CREATE INDEX`, `DROP INDEX`, `OPTIMIZE TABLE`, 和`REPAIR TABLE`。如果希望MySQL的慢日志记录这类长时间执行的命令，可以设置`log_slow_admin_statements` 为1。

通过设置`log_queries_not_using_indexes=1`，MySQL的慢日志也能记录那些不使用索引的SQL（并不需要超过`long_query_time`，两者条件满足一个即可）。但打开该选项的时候，如果你的数据库中存在大量没有使用索引的SQL，那么MySQL慢日志的记录量将非常大，所以通常还需要设置参数`log_throttle_queries_not_using_indexes` 。默认情况下，该参数为`0`，表示不限制，当设置改参数为大于0的值的时候，表示MySQL在一分钟内记录的不使用索引的SQL的数量，来避免慢日志记录过多的该类SQL.

在MySQL 5.7.2 之后，如果设置了慢日志是写到文件里，需要设置`log_timestamps` 来控制写入到慢日志文件里面的时区（该参数同时影响`general`日志和`err`日志）。如果设置慢日志是写入到数据库中，该参数将不产生作用。

所以，总结下哪些SQL能被MySQL慢日志记录：

* 不会记录MySQL中的管理维护命令，除非明确设置`log_slow_admin_statements=1`;
* SQL执行时间必须超过`long_query_time`，（不包括锁等待时间）
* 参数`log_queries_not_using_indexes`设置为`1`，且SQL没有用到索引，同时没有超过`log_throttle_queries_not_using_indexes` 参数的设定。
* 查询`examine`的行数必须超过`min_examined_row_limit`

注：如果表没有记录或者只有1条记录，优化器觉得走索引并不能提升效率，即使设置了`log_queries_not_using_indexes=1`，那么也不会记录到慢日志中。

注：如果SQL使用了QC，那也不会记录到慢日志中。

注：修改密码之类的维护操作，密码部分将会被星号代替，避免明文显示。

## Anemometer 简介：

项目地址： [https://github.com/box/Anemometer][3]

演示地址： [http://lab.fordba.com/anemometer/][4]

Anemometer 是一个图形化显示从MySQL慢日志的工具。结合`pt-query-digest`，Anemometer可以很轻松的帮你去分析慢查询日志，让你很容易就能找到哪些SQL需要优化。

如果你想要使用Anemometer这个工具，那么你需要准备以下环境：

1. 一个用来存储分析数据的MySQL数据库
1. [pt-query-digest][5] . (doc: [Percona Toolkit][6] )
1. MySQL数据库的慢查询日志 (doc: [The Slow Query Log][7] )
1. PHP版本为 5.5+ apache或者nginx等web服务器均可。

## 安装：

* 下载Anemometer
```
    git clone git://github.com/box/Anemometer.git anemometer
```
## 载入数据：

首先创建表结构，将`global_query_review` 以及`global_query_review_history` 创建出来。由于表定义中存在0000-00-00 00:00:00 的日期默认值，需要修改`sql_mode`，将其`zero_date`的`sql_mode` 关闭，同时关闭`only_full_group_by`

    cd /www/lab/anemometer
    mysql < ./install.sql

现在需要使用`pt-query-digest` 抓取MySQL的慢查询日志，然后将数据插入到`slow_query_log` 数据库的相应表中

使用如下方式载入数据，h表示主机名或者ip地址，D表示database，t表示表名，再最后面跟上慢日志路径。

如果 `pt-query-digest version > 2.2`：

    $ pt-query-digest --user=anemometer --password=superSecurePass \
                      --review h=127.0.0.1,D=slow_query_log,t=global_query_review \
                      --review-history h=127.0.0.1,D=slow_query_log,t=global_query_review_history \
                      --no-report --limit=0% \ 
                      --filter=" \$event->{Bytes} = length(\$event->{arg}) and \$event->{hostname}=\"$HOSTNAME\"" \ 
                      /data/mysql/slow-query.log

如果 `pt-query-digest version <= 2.2`

    $  pt-query-digest --user=root --password=root  --review h=127.0.0.1,D=slow_query_log,t=global_query_review --history h=127.0.0.1,D=slow_query_log,t=global_query_review_history  --no-report --limit=0%  --filter=" \$event->{Bytes} = length(\$event->{arg}) and \$event->{hostname}=\"$HOSTNAME\""  /data/mysql/slow-query.log
    
    
    Pipeline process 11 (aggregate fingerprint) caused an error: Argument "57A" isn't numeric in numeric gt (>) at (eval 40) line 6, <> line 27.
    Pipeline process 11 (aggregate fingerprint) caused an error: Argument "57B" isn't numeric in numeric gt (>) at (eval 40) line 6, <> line 28.
    Pipeline process 11 (aggregate fingerprint) caused an error: Argument "57C" isn't numeric in numeric gt (>) at (eval 40) line 6, <> line 29.

如果你看到一些报错如上面例子所示，脚本并没有出现问题，他只是输出当前的操作。

## 配置Anemometer

修改Anemometer配置文件

    $ cd anemometer/conf
    $ cp sample.config.inc.php config.inc.php

示例的配置文件中，你需要进行部分修改，用来连接数据库获取慢查询的分析数据。

修改 `datasource_localhost.inc.php` 文件中的配置，主要为主机

    $conf['datasources']['localhost'] = array(
        'host'  => '127.0.0.1',
        'port'  => 3306,
        'db'    => 'slow_query_log',
        'user'  => 'root',
        'password' => 'root',
        'tables' => array(
            'global_query_review' => 'fact',
            'global_query_review_history' => 'dimension'
        ),
            'source_type' => 'slow_query_log'
    );

然后访问127.0.0.1/anemometer 的时候出现

    Expression #2 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'slow_query_log.dimension.sample' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by (1055)

需要将`sql_mode` 中`only_full_group_by` 关闭。

如果你想利用Anemometer 的explain功能来获取执行计划，修改配置文件的以下部分。

    $conf['plugins'] = array(
         'visual_explain' => '/usr/local/bin/pt-visual-explain',  --这里需要修改为正确的路径
            ...
        'explain'       =>      function ($sample) {
            $conn['user'] = 'anemometer';
            $conn['password'] = 'superSecurePass';
    
            return $conn;
        },
    );

## 结果展示：

在5.7中，默认`SQL_MODE`是启用`ONLY_FULL_GROUP_BY`的，需要将其关闭，否则Anemometer将报错。

选择相应的列，然后点击search，就可以显示结果

![][8]

## sql执行计划查看以及历史

当我们选择一个sql的hash值的时候，能看到他的一个具体的执行计划，同时也能看到匹配该sql的历史sql，消耗，表的统计信息，建表语句等。

![][9]

同时也能针对sql进行评论，为sql优化提交建议等。

## 创建自动收集慢日志脚本

在anemometer下面的script文件中有个收集脚本，可以通过crontab进行定时收集慢日志，语法如下：

    Usage: ./scripts/anemometer_collect.sh --interval 
    
    Options:
        --socket -S              The mysql socket to use
        --defaults-file          The defaults file to use for the client
        --interval -i            The collection duration
        --rate                   Set log_slow_rate_limit (For Percona MySQL Only)
    
        --history-db-host        Hostname of anemometer database server
        --history-db-port        Port of anemometer database server
        --history-db-name        Database name of anemometer database server (Default slow_query_log)
        --history-defaults-file  Defaults file to pass to pt-query-digest for connecting to the remote anemometer database

示例脚本：

```
    cd anemometer 
    mkdir etc
    cd etc
    vi anemometer.local.cnf   --这里创建配置文件，添加用户名密码
    [client]
    user=anemometer_local
    password=superSecurePass
    ./scripts/anemometer_collect.sh --interval 30 --history-db-host=127.0.0.1
```

[1]: http://fordba.com/box-anemometer-visual-mysql-slow.html
[3]: https://github.com/box/Anemometer
[4]: http://lab.fordba.com/anemometer/
[5]: http://www.percona.com/doc/percona-toolkit/pt-query-digest.html
[6]: http://www.percona.com/doc/percona-toolkit
[7]: http://dev.mysql.com/doc/refman/5.5/en/slow-query-log.html
[8]: ./img/2M7NJ3I.png
[9]: ./img/AvINjaM.png
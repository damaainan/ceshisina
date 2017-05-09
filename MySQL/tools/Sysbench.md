# 使用sysbench对MySQL压力测试

 时间 2017-01-09 16:05:15  [Linux公社][0]

_原文_[http://www.linuxidc.com/Linux/2017-01/139393.htm][1]

 主题 [sysbench][2][MySQL][3]

sysbench是一个模块化的、跨平台、多线程基准测试工具，主要用于评估测试各种不同系统参数下的数据库负载情况。关于这个项目的详细介绍请看： [https://github.com/akopytov/sysbench][4] 。 

它主要包括以下几种方式的测试：

1. cpu性能
1. 磁盘io性能
1. 调度程序性能
1. 内存分配及传输速度
1. POSIX线程性能
1. 数据库性能(OLTP基准测试)

sysbench的数据库OLTP测试支持MySQL、PostgreSQL、Oracle，目前主要用于Linux操作系统，开源社区已经将sysbench移植到了Windows，并支持SQL Server的基准测试。 

废话不多说，开始。

### 1. sysbench安装

* mysql版本: mysql-community-server-5.6.29
* OS:CentOS 6.7 X86_64
* sysbench 0.5相比0.4版本有一些变化，包括oltp测试结合了lua脚本，还多了一些隐藏选项，本文会涉及得到一部分。

目前许多仓库里已编译好的二进制sysbench还是0.4.x版本，不过现在主流也还是github上的0.5，可以从 [这里][5] 下载0.5版本的rpm包直接安装，不过我选择自己编译，因为只有这个办法是通用的。 

    // 先安装编译依赖环境
    $ sudo yum install gcc gcc-c++ automake make libtool mysql-community-devel
     
    $ cd /tmp && git clone https://github.com/akopytov/sysbench.git
     
    $ cd /tmp/sysbench && ./autogen.sh
    $ ./configure --prefix=/usr/local/sysbench-0.5
    $ ./make && sudo make install
     
    // 0.5版本需要oltp.lua测试脚本
    // 如果是rpm包方式安装的，在 /usr/share/doc/sysbench/tests/db/ 下可找到
    $ cd /usr/local/sysbench && sudo mkdir -p share/tests/db
    $ cp /tmp/sysbench/sysbench/tests/db/*.lua share/tests/db/
    $ ./bin/sysbench --version
    sysbench 0.5
    

如果需要测试PostgreSQL、Oracle，则在configure时需要加上 –with-oracle 或者 –with-pgsql 参数

### 2. 使用sysbench对mysql压测

#### 2.1 只读示例

    ./bin/sysbench --test=./share/tests/db/oltp.lua \
    --mysql-host=10.0.201.36 --mysql-port=8066 --mysql-user=ecuser --mysql-password=ecuser \
    --mysql-db=dbtest1a --oltp-tables-count=10 --oltp-table-size=500000 \
    --report-interval=10 --oltp-dist-type=uniform --rand-init=on --max-requests=0 \
    --oltp-test-mode=nontrx --oltp-nontrx-mode=select \
    --oltp-read-only=on --oltp-skip-trx=on \
    --max-time=120 --num-threads=12 \
    [prepare|run|cleanup]
    

注意最后一行，一项测试开始前需要用 prepare 来准备好表和数据， run 执行真正的压测， cleanup 用来清除数据和表。实际prepare的表结构： 

    mysql> desc dbtest1a.sbtest1;
    +-------+------------------+------+-----+---------+----------------+
    | Field | Type | Null | Key | Default | Extra |
    +-------+------------------+------+-----+---------+----------------+
    | id | int(10) unsigned | NO | PRI | NULL | auto_increment |
    | k | int(10) unsigned | NO | MUL | 0 ||
    | c | char(120) | NO ||||
    | pad | char(60) | NO ||||
    +-------+------------------+------+-----+---------+----------------+
    4 rows in set (0.00 sec)
    

上面的测试命令代表的是：对mysql进行oltp基准测试，表数量10，每表行数约50w（几乎delete多少就会insert的多少），并且是非事务的只读测试，持续60s，并发线程数12。

#### 需要说明的选项：

* mysql-db=dbtest1a ：测试使用的目标数据库，这个库名要事先创建
* --oltp-tables-count=10 ：产生表的数量
* --oltp-table-size=500000 ：每个表产生的记录行数
* --oltp-dist-type=uniform ：指定随机取样类型，可选值有 uniform(均匀分布), Gaussian(高斯分布), special(空间分布)。默认是special
* --oltp-read-only=off ：表示不止产生只读SQL，也就是使用oltp.lua时会采用读写混合模式。默认 off，如果设置为on，则不会产生update,delete,insert的sql。
* --oltp-test-mode=nontrx ：执行模式，这里是非事务式的。可选值有simple,complex,nontrx。默认是complex 
  * simple：简单查询，SELECT c FROM sbtest WHERE id=N
  * complex (advanced transactional)：事务模式在开始和结束事务之前加上begin��commit， 一个事务里可以有多个语句，如点查询、范围查询、排序查询、更新、删除、插入等，并且为了不破坏测试表的数据，该模式下一条记录删除后会在同一个事务里添加一条相同的记录。
  * nontrx (non-transactional)：与simple相似，但是可以进行update/insert等操作，所以如果做连续的对比压测，你可能需要重新cleanup,prepare。
* --oltp-skip-trx=[on|off] ：省略begin/commit语句。默认是off
* --rand-init=on ：是否随机初始化数据，如果不随机化那么初始好的数据每行内容除了主键不同外其他完全相同
* --num-threads=12 ： 并发线程数，可以理解为模拟的客户端并发连接数
* --report-interval=10 ：表示每10s输出一次测试进度报告
* --max-requests=0 ：压力测试产生请求的总数，如果以下面的 max-time 来记，这个值设为0
* --max-time=120 ：压力测试的持续时间，这里是2分钟。

注意，针对不同的选项取值就会有不同的子选项。比如 oltp-dist-type=special ，就有比如 oltp-dist-pct=1 、 oltp-dist-res=50 两个子选项，代表有50%的查询落在1%的行（即热点数据）上，另外50%均匀的(sample uniformly)落在另外99%的记录行上。 

再比如 oltp-test-mode=nontrx 时, 就可以有 oltp-nontrx-mode ，可选值有select（默认）, update_key, update_nokey, insert, delete，代表非事务式模式下使用的测试sql类型。 

以上代表的是一个只读的例子，可以把 num-threads 依次递增（16,36,72,128,256,512），或者调整my.cnf参数，比较效果。另外需要注意的是，大部分mysql中间件对事务的处理，默认都是把sql发到主库执行，所以只读测试需要加上 oltp-skip-trx=on 来跳过测试中的显式事务。 

ps1: 只读测试也可以使用 share/tests/db/select.lua 进行，但只是简单的point select。 

ps2: 我在用sysbench压的时候，在mysql后端会话里有时看到大量的query cache lock，如果使用的是uniform取样，最好把查询缓存关掉。当然如果是做两组性能对比压测，因为都受这个因素影响，关心也不大。

#### 2.2 混合读写

读写测试还是用oltp.lua，只需把 --oltp-read-only 等于 off 。 

    ./bin/sysbench --test=./share/tests/db/oltp.lua --mysql-host=10.0.201.36 --mysql-port=8066 --mysql-user=ecuser --mysql-password=ecuser --mysql-db=dbtest1a --oltp-tables-count=10 --oltp-table-size=500000 --report-interval=10 --rand-init=on --max-requests=0 --oltp-test-mode=nontrx --oltp-nontrx-mode=select --oltp-read-only=off --max-time=120 --num-threads=128 prepare
     
    ./bin/sysbench --test=./share/tests/db/oltp.lua --mysql-host=10.0.201.36 --mysql-port=8066 --mysql-user=ecuser --mysql-password=ecuser --mysql-db=dbtest1a --oltp-tables-count=10 --oltp-table-size=500000 --report-interval=10 --rand-init=on --max-requests=0 --oltp-test-mode=nontrx --oltp-nontrx-mode=select --oltp-read-only=off --max-time=120 --num-threads=128 run
     
    ./bin/sysbench --test=./share/tests/db/oltp.lua --mysql-host=10.0.201.36 --mysql-port=8066 --mysql-user=ecuser --mysql-password=ecuser --mysql-db=dbtest1a --oltp-tables-count=10 --oltp-table-size=500000 --report-interval=10 --rand-init=on --max-requests=0 --oltp-test-mode=nontrx --oltp-nontrx-mode=select --oltp-read-only=off --max-time=120 --num-threads=128 cleanup
    

然而 oltp-test-mode=nontrx 一直没有跟着我预期的去走，在mysql general log里面看到的sql记录与 complex 模式相同。所以上面示例中的 --oltp-test-mode=nontrx --oltp-nontrx-mode=select 可以删掉。 

update: 

sysbench作者 akopytov 对我这个疑问有了回复： [https://github.com/akopytov/sysbench/issues/34][6] ，原来sysbench 0.5版本去掉了这个选项，因为作者正在准备1.0版本，所以也就没有更新0.5版本的doc。网上的博客漫天飞，就没有一个提出来的，也是没谁了。 

分析一下oltp.lua脚本内容，可以清楚单个事务各操作的默认比例：select:update_key:update_non_key:delete:insert = 14:1:1:1:1，可通过 oltp-point-selects 、 oltp-simple-ranges 、 oltp-sum-ranges 、 oltp-order-ranges 、 oltp-distinct-ranges ， oltp-index-updates 、 oltp-non-index-updates 这些选项去调整读写权重。 

同只读测试一样，在atlas,mycat这类中间件测试中如果不加 oltp-skip-trx=on ，那么所有查询都会发往主库，但如果在有写入的情况下使用 --oltp-skip-trx=on 跳过BEGIN和COMMIT，会出现问题： 
```
ALERT: failed to execute MySQL query: INSERT INTO sbtest4 (id, k, c, pad) VALUES (48228, 47329, '82773802508-44916890724-85859319254-67627358653-96425730419-64102446666-75789993135-91202056934-68463872307-28147315305', '13146850449-23153169696-47584324044-14749610547-34267941374') : 

ALERT: Error 1062 Duplicate entry ‘48228’ for key ‘PRIMARY’

FATAL: failed to execute function `event’: (null)
```
原因也很容易理解，每个线程将选择一个随机的表，不加事务的情况下高并发更新（插入）出现重复key的概率很大，但我们压测不在乎这些数据，所以需要跳过这个错误 --mysql-ignore-errors=1062 ，这个问题老外有出过打补丁的方案允许 --mysql-ignore-duplicates=on ，但作者新加入的忽略错误码这个功能已经取代了它。 mysql-ignore-errors 选项是0.5版本加入的，但目前没有文档标明，也是我在github上提的 [issue][7] 作者回复的。 

这里不得不佩服老外的办事效率和责任心，提个疑惑能立马得到回复，反观国内，比如在atlas,mycat项目里提到问题到现在都没人搭理。。。

#### 2.3 只更新

如果基准测试的时候，你只想比较两个项目的update（或insert）效率，那可以不使用oltp脚本，而直接改用 update_index.lua ： 

    ./bin/sysbench --test=./share/tests/db/update_index.lua \
    --mysql-host=10.0.201.36 --mysql-port=8066 --mysql-user=ecuser --mysql-password=ecuser \
    --mysql-db=dbtest1a --oltp-tables-count=10 --oltp-table-size=500000 \
    --report-interval=10 --rand-init=on --max-requests=0 \
    --oltp-read-only=off --max-time=120 --num-threads=128 \
    [ prepare | run | cleanup ]
    

此时像 oltp-read-only=off 许多参数都失效了。需要说明的是这里 (非)索引更新，不是where条件根据索引去查找更新，而是更新索引列上的值。 

### 3. 结果解读

    sysbench 0.5: multi-threaded system evaluation benchmark
     
    Running the test with following options:
    Number of threads: 128
    Report intermediate results every 20 second(s)
    Initializing random number generator from timer.
     
    Random number generator seed is 0 and will be ignored
     
     
    Initializing worker threads...
     
    Threads started!
     
    [ 20s] threads: 128, tps: 2354.54, reads: 33035.89, writes: 9423.39, response time: 66.80ms (95%), errors: 0.00, reconnects: 0.00
    [ 40s] threads: 128, tps: 2377.75, reads: 33274.26, writes: 9507.55, response time: 66.88ms (95%), errors: 0.00, reconnects: 0.00
    [ 60s] threads: 128, tps: 2401.35, reads: 33615.30, writes: 9607.40, response time: 66.40ms (95%), errors: 0.00, reconnects: 0.00
    [ 80s] threads: 128, tps: 2381.20, reads: 33331.50, writes: 9522.55, response time: 67.30ms (95%), errors: 0.00, reconnects: 0.00
    [ 100s] threads: 128, tps: 2388.85, reads: 33446.10, writes: 9556.35, response time: 67.00ms (95%), errors: 0.00, reconnects: 0.00
    [ 120s] threads: 128, tps: 2386.40, reads: 33421.35, writes: 9545.35, response time: 66.94ms (95%), errors: 0.00, reconnects: 0.00
    OLTP test statistics:
    queries performed:
    read: 4003048 //总select数量
    write: 1143728 //总update、insert、delete语句数量
    other: 571864 //commit、unlock tables以及其他mutex的数量
    total: 5718640
    transactions: 285932 (2382.10 per sec.) //通常需要关注的数字(TPS)
    read/write requests: 5146776 (42877.85 per sec.)
    other operations: 571864 (4764.21 per sec.)
    ignored errors: 0 (0.00 per sec.) //忽略的错误数
    reconnects: 0 (0.00 per sec.)
     
    General statistics:
    total time: 120.0334s //即max-time指定的压测实际
    total number of events: 285932 //总的事件数，一般与transactions相同
    total time taken by event execution: 15362.6623s
    response time:
    min: 17.60ms
    avg: 53.73ms //95%的语句的平均响应时间
    max: 252.90ms
    approx. 95 percentile: 66.88ms
     
    Threads fairness:
    events (avg/stddev): 2233.8438/9.04
    execution time (avg/stddev): 120.0208/0.01
    

我们一般关注的用于绘图的指标主要有：

* response time avg: 平均响应时间。（后面的95%的大小可以通过 --percentile=98 的方式去更改）
* transactions: 精确的说是这一项后面的TPS 。但如果使用了 -oltp-skip-trx=on ，这项事务数恒为0，需要用 total number of events 去除以总时间，得到tps（其实还可以分为读tps和写tps）
* read/write requests: 用它除以总时间，得到吞吐量QPS
* 当然还有一些系统层面的cpu,io,mem相关指标

sysbench还可以对文件系统IO测试，CPU性能测试，以及内存分配与传输速度测试，这里就不介绍了。

总结起来sysbench的缺点就是，模拟的表结构太简单，不像tpcc-mysql那样完整的事务系统。但对于性能压测对比还是很有用的，因为sysbench使用的环境参数限制是一样的。

[0]: /sites/umUrqm
[1]: http://www.linuxidc.com/Linux/2017-01/139393.htm?utm_source=tuicool&utm_medium=referral
[2]: /topics/11350039
[3]: /topics/11030000
[4]: https://github.com/akopytov/sysbench
[5]: http://www.lefred.be/node/154
[6]: https://github.com/akopytov/sysbench/issues/34
[7]: https://github.com/akopytov/sysbench/issues/23
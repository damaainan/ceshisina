<font face=微软雅黑>

* 慢查询日志  
* explain分析查询  
* profiling分析查询  

#### 慢查询日志

慢查询日志可以帮助我们知道哪些SQL语句执行效率低下。

先确保开启了慢查询日志：

    # 检查是否开启
    show variables like '%slow%';
    # 如果没有开启，也可以在运行时开启这个参数。说明是动态参数
    set global slow_query_log=ON;
    # 设置慢查询记录查询耗时多长的SQL,这里演示用100毫秒
    set long_query_time = 0.1;
    # 用SQL试一下。这里休眠500毫秒
    select sleep(0.5)

慢查询日志查看：

1. 直接查看 `more` `cat`

1. 使用工具`mysqldumpslow`

####  explain分析查询

EXPLAIN 关键字可以模拟优化器执行SQL查询语句，从而知道MySQL是如何处理你的SQL语句的。  
explain命令可以获取的信息：

1. 表的读取顺序
1. 数据读取操作的操作类型
1. 哪些索引可以使用
1. 哪些索引被实际使用
1. 表之间的引用
1. 每张表有多少行被优化器查询

各列的含义如下:

* id: SELECT 查询的标识符. 每个 SELECT 都会自动分配一个唯一的标识符.
* select_type: SELECT 查询的类型.
* table: 查询的是哪个表
* partitions: 匹配的分区
* type: join 类型
* possible_keys: 此次查询中可能选用的索引
* key: 此次查询中确切使用到的索引.
* ref: 哪个字段或常数与 key 一起被使用
* rows: 显示此查询一共扫描了多少行. 这个是一个估计值.
* filtered: 表示此查询条件所过滤的数据的百分比
* extra: 额外的信息

#### profiling分析查询

如果觉得explain的信息不够详细，可以同通过`profiling`命令得到更准确的SQL执行消耗系统资源的信息。profiling默认是关闭的。可以通过以下语句查看：

    # 查看是否开启profiling
    select @@profiling;
    # 开profiling。注意测试完关闭该特性，否则耗费资源
    set profiling=1;
    # 查看所有记录profile的SQL
    show profiles;
    # 查看指定ID的SQL的详情
    show profile for query 1;
    # 测试完，关闭该特性
    set profiling=0;




</font>
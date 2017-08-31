<font face=微软雅黑>

<font face=楷体 color=green style="font-weight: bold">

## 参数 

#### 1 连接请求的变量：
1) `max_connections`: MySQL的最大连接数  
`show variables like 'max_connections'` 最大连接数  
`show status like 'max_used_connections'`响应的连接数

2) `back_log`  
MySQL能暂存的连接数量。

3) `interactive_timeout`  
一个交互连接在被服务器在关闭前等待行动的秒数。


#### 2 缓冲区变量

全局缓冲：

4) `key_buffer_size`  
`key_buffer_size`指定索引缓冲区的大小，它决定索引处理的速度，尤其是索引读的速度。

5) `query_cache_size`  
使用查询缓冲，MySQL将查询结果存放在缓冲区中，今后对于同样的SELECT语句（区分大小写），将直接从缓冲区中读取结果。  
通过检查状态值Qcache_*，可以知道`query_cache_size`设置是否合理（上述状态值可以使用`SHOW STATUS LIKE 'Qcache%'`获得）。

**查询缓存碎片率**  
**查询缓存利用率**  
**查询缓存命中率**  

6) `record_buffer_size`  
每个进行一个顺序扫描的线程为其扫描的每张表分配这个大小的一个缓冲区。

7) `read_rnd_buffer_size`  
随机读缓冲区大小。

8) `sort_buffer_size`  
每个需要进行排序的线程分配该大小的一个缓冲区。

9)`join_buffer_size`  
联合查询操作所能使用的缓冲区大小  

10) `table_cache`  
表高速缓存的大小。

11) `max_heap_table_size`  
用户可以创建的内存表(memory table)的大小。

12) `tmp_table_size`  
通过设置tmp_table_size选项来增加一张临时表的大小，例如做高级GROUP BY操作生成的临时表。



</font>

--------

###  参数配置

安装MySQL后，配置文件`my.cnf`在 /MySQL安装目录`/share/mysql`目录中，该目录中还包含多个配置文件可供参考，有`my-large.cnf` ，`my-huge.cnf`， `my-medium.cnf`，`my-small.cnf`，分别对应大中小型数据库应用的配置。win环境下即存在于MySQL安装目录中的.ini文件。

下面列出了对性能优化影响较大的主要变量，主要分为连接请求的变量和缓冲区变量。

#### 1 连接请求的变量：

1) `max_connections`: MySQL的最大连接数，增加该值增加mysqld 要求的文件描述符的数量。如果服务器的并发连接请求量比较大，建议调高此值，以增加并行连接数量，当然这建立在机器能支撑的情况下，因为如果连接数越多，介于MySQL会为每个连接提供连接缓冲区，就会开销越多的内存，所以要适当调整该值，不能盲目提高设值。  
数值过小会经常出现ERROR 1040: Too many connections错误，可以过'conn%'通配符查看当前状态的连接数量，以定夺该值的大小。  
`show variables like 'max_connections'` 最大连接数  
`show status like 'max_used_connections'`响应的连接数

如下：

```sql
    mysql> show variables like 'max_connections';
    +------------–+——-+
    | Variable_name　| Value |
    +------------–+——-+
    | max_connections | 256　　|
    +------------–+——-+
    mysql> show status like 'max%connections';
    +------------–+——-+
    | Variable_name　      | Value |
    +-------------+——-+
    | max_used_connections | 256|
    +-------------+——-+
```

`max_used_connections / max_connections * 100%` （理想值≈ 85%）   
如果`max_used_connections`跟`max_connections`相同 那么就是`max_connections`设置过低或者超过服务器负载上限了，低于10%则设置过大。

2) `back_log`  
MySQL能暂存的连接数量。当主要MySQL线程在一个很短时间内得到非常多的连接请求，这就起作用。如果MySQL的连接数据达到max_connections时，新来的请求将会被存在堆栈中，以等待某一连接释放资源，该堆栈的数量即back_log，如果等待连接的数量超过back_log，将不被授予连接资源。  
`back_log`值指出在MySQL暂时停止回答新请求之前的短时间内有多少个请求可以被存在堆栈中。只有如果期望在一个短时间内有很多连接，你需要增加它，换句话说，这值对到来的TCP/IP连接的侦听队列的大小。  
当观察你主机进程列表（mysql> show full processlist），发现大量264084 | unauthenticated user | xxx.xxx.xxx.xxx | NULL | Connect | NULL | login | NULL 的待连接进程时，就要加大back_log 的值了。  
默认数值是50，可调优为128，对于Linux系统设置范围为小于512的整数。 

3) `interactive_timeout`  
一个交互连接在被服务器在关闭前等待行动的秒数。一个交互的客户被定义为对mysql_real_connect()使用CLIENT_INTERACTIVE 选项的客户。   
默认数值是28800，可调优为7200。 

#### 2 缓冲区变量

全局缓冲：

4) `key_buffer_size`  
`key_buffer_size`指定索引缓冲区的大小，它决定索引处理的速度，尤其是索引读的速度。通过检查状态值Key_read_requests和Key_reads，可以知道key_buffer_size设置是否合理。比例key_reads / key_read_requests应该尽可能的低，至少是1:100，1:1000更好（上述状态值可以使用SHOW STATUS LIKE 'key_read%'获得）。  
key_buffer_size只对MyISAM表起作用。即使你不使用MyISAM表，但是内部的临时磁盘表是MyISAM表，也要使用该值。可以使用检查状态值created_tmp_disk_tables得知详情。  
举例如下：

```sql
    mysql> show variables like 'key_buffer_size';
    +-----------+-------+
    | Variable_name | Value      |
    +----------+-------+
    | key_buffer_size | 536870912 |
    +-------+------+
```

key_buffer_size为512MB，我们再看一下key_buffer_size的使用情况：

```sql
    mysql> show global status like 'key_read%';
    +----------+--------+
    | Variable_name　  | Value    |
    +----------+--------+
    | Key_read_requests| 27813678764 |
    | Key_reads　　　|  6798830      |
    +----------+--------+
```

一共有27813678764个索引读取请求，有6798830个请求在内存中没有找到直接从硬盘读取索引，计算索引未命中缓存的概率：  
key_cache_miss_rate ＝Key_reads / Key_read_requests * 100%，设置在1/1000左右较好  
默认配置数值是8388600(8M)，主机有4GB内存，可以调优值为268435456(256MB)。

5) `query_cache_size`  
使用查询缓冲，MySQL将查询结果存放在缓冲区中，今后对于同样的SELECT语句（区分大小写），将直接从缓冲区中读取结果。  
通过检查状态值Qcache_*，可以知道`query_cache_size`设置是否合理（上述状态值可以使用SHOW STATUS LIKE 'Qcache%'获得）。如果Qcache_lowmem_prunes的值非常大，则表明经常出现缓冲不够的情况，如果Qcache_hits的值也非常大，则表明查询缓冲使用非常频繁，此时需要增加缓冲大小；如果Qcache_hits的值不大，则表明你的查询重复率很低，这种情况下使用查询缓冲反而会影响效率，那么可以考虑不用查询缓冲。此外，在SELECT语句中加入SQL_NO_CACHE可以明确表示不使用查询缓冲。  
与查询缓冲有关的参数还有query_cache_type、query_cache_limit、query_cache_min_res_unit。  
query_cache_type指定是否使用查询缓冲，可以设置为0、1、2，该变量是SESSION级的变量。  
query_cache_limit指定单个查询能够使用的缓冲区大小，缺省为1M。  
query_cache_min_res_unit是在4.1版本以后引入的，它指定分配缓冲区空间的最小单位，缺省为4K。检查状态值Qcache_free_blocks，如果该值非常大，则表明缓冲区中碎片很多，这就表明查询结果都比较小，此时需要减小query_cache_min_res_unit。

举例如下：

```sql
    mysql> show global status like 'qcache%';
    +-------------+—————–+
    | Variable_name                  | Value　       |
    +-------------+—————–+
    | Qcache_free_blocks　       | 22756　      |
    | Qcache_free_memory　    | 76764704    |
    | Qcache_hits　　　　　      | 213028692 |
    | Qcache_inserts　　　　     | 208894227   |
    | Qcache_lowmem_prunes   | 4010916      |
    | Qcache_not_cached　| 13385031    |
    | Qcache_queries_in_cache | 43560　|
    | Qcache_total_blocks          | 111212　     |
    +-------------+—————–+
    mysql> show variables like 'query_cache%';
    +------------–+————–+
    | Variable_name　　　　　       | Value　     |
    +------------–+———–+
    | query_cache_limit　　　　　    | 2097152     |
    | query_cache_min_res_unit　     | 4096　　  |
    | query_cache_size　　　　　    | 203423744 |
    | query_cache_type　　　　　   | ON　          |
    | query_cache_wlock_invalidate | OFF　  |
    +------------–+—————+
```

**查询缓存碎片率**= `Qcache_free_blocks / Qcache_total_blocks * 100%`  
如果查询缓存碎片率超过20%，可以用`FLUSH QUERY CACHE`整理缓存碎片，或者试试减小query_cache_min_res_unit，如果你的查询都是小数据量的话。  

**查询缓存利用率**= (query_cache_size – Qcache_free_memory) / query_cache_size * 100%  
查询缓存利用率在25%以下的话说明query_cache_size设置的过大，可适当减小；查询缓存利用率在80％以上而且Qcache_lowmem_prunes > 50的话说明query_cache_size可能有点小，要不就是碎片太多。  

**查询缓存命中率**= (Qcache_hits – Qcache_inserts) / Qcache_hits * 100%  
示例服务器查询缓存碎片率＝20.46％，查询缓存利用率＝62.26％，查询缓存命中率＝1.94％，命中率很差，可能写操作比较频繁吧，而且可能有些碎片。  
每个连接的缓冲

6) `record_buffer_size`  
每个进行一个顺序扫描的线程为其扫描的每张表分配这个大小的一个缓冲区。如果你做很多顺序扫描，你可能想要增加该值。  
默认数值是131072(128K)，可改为16773120 (16M)

7) `read_rnd_buffer_size`  
随机读缓冲区大小。当按任意顺序读取行时(例如，按照排序顺序)，将分配一个随机读缓存区。进行排序查询时，MySQL会首先扫描一遍该缓冲，以避免磁盘搜索，提高查询速度，如果需要排序大量数据，可适当调高该值。但MySQL会为每个客户连接发放该缓冲空间，所以应尽量适当设置该值，以避免内存开销过大。  
一般可设置为16M 

8) `sort_buffer_size`  
每个需要进行排序的线程分配该大小的一个缓冲区。增加这值加速ORDER BY或GROUP BY操作。  
默认数值是2097144(2M)，可改为16777208 (16M)。

9)`join_buffer_size`  
联合查询操作所能使用的缓冲区大小  
record_buffer_size，read_rnd_buffer_size，sort_buffer_size，join_buffer_size为每个线程独占，也就是说，如果有100个线程连接，则占用为16M*100

10) `table_cache`  
表高速缓存的大小。每当MySQL访问一个表时，如果在表缓冲区中还有空间，该表就被打开并放入其中，这样可以更快地访问表内容。通过检查峰值时间的状态值Open_tables和Opened_tables，可以决定是否需要增加table_cache的值。如果你发现open_tables等于table_cache，并且opened_tables在不断增长，那么你就需要增加table_cache的值了（上述状态值可以使用SHOW STATUS LIKE 'Open%tables'获得）。注意，不能盲目地把table_cache设置成很大的值。如果设置得太高，可能会造成文件描述符不足，从而造成性能不稳定或者连接失败。  
1G内存机器，推荐值是128－256。内存在4GB左右的服务器该参数可设置为256M或384M。

11) `max_heap_table_size`  
用户可以创建的内存表(memory table)的大小。这个值用来计算内存表的最大行数值。这个变量支持动态改变，即set @max_heap_table_size=#  
这个变量和tmp_table_size一起限制了内部内存表的大小。如果某个内部heap（堆积）表大小超过tmp_table_size，MySQL可以根据需要自动将内存中的heap表改为基于硬盘的MyISAM表。

12) `tmp_table_size`  
通过设置tmp_table_size选项来增加一张临时表的大小，例如做高级GROUP BY操作生成的临时表。如果调高该值，MySQL同时将增加heap表的大小，可达到提高联接查询速度的效果，建议尽量优化查询，要确保查询过程中生成的临时表在内存中，避免临时表过大导致生成基于硬盘的MyISAM表。

```sql
    mysql> show global status like 'created_tmp%';
    +-----------------–+------+
    | Variable_name　　           | Value　|
    +------------------+------+
    | Created_tmp_disk_tables | 21197  |
    | Created_tmp_files　　　| 58　　|
    | Created_tmp_tables　　| 1771587 |
    +-----------+---------+
```

每次创建临时表，Created_tmp_tables增加，如果临时表大小超过tmp_table_size，则是在磁盘上创建临时表，Created_tmp_disk_tables也增加,Created_tmp_files表示MySQL服务创建的临时文件文件数，比较理想的配置是：  
Created_tmp_disk_tables / Created_tmp_tables * 100% <= 25%比如上面的服务器Created_tmp_disk_tables / Created_tmp_tables * 100% ＝1.20%，应该相当好了  
默认为16M，可调到64-256最佳，线程独占，太大可能内存不够I/O堵塞

13) thread_cache_size  
可以复用的保存在中的线程的数量。如果有，新的线程从缓存中取得，当断开连接的时候如果有空间，客户的线置在缓存中。如果有很多新的线程，为了提高性能可以这个变量值。  
通过比较 Connections和Threads_created状态的变量，可以看到这个变量的作用。  
默认值为110，可调优为80。 

14) thread_concurrency  
推荐设置为服务器 CPU核数的2倍，例如双核的CPU, 那么thread_concurrency的应该为4；2个双核的cpu, thread_concurrency的值应为8。默认为8

15) wait_timeout  
指定一个请求的最大连接时间，对于4GB左右内存的服务器可以设置为5-10。

#### 3 配置InnoDB的几个变量

16)innodb_buffer_pool_size  
对于InnoDB表来说，innodb_buffer_pool_size的作用就相当于key_buffer_size对于MyISAM表的作用一样。InnoDB使用该参数指定大小的内存来缓冲数据和索引。对于单独的MySQL数据库服务器，最大可以把该值设置成物理内存的80%。  
根据MySQL手册，对于2G内存的机器，推荐值是1G（50%）。

17)innodb_flush_log_at_trx_commit  
主要控制了innodb将log buffer中的数据写入日志文件并flush磁盘的时间点，取值分别为0、1、2三个。0，表示当事务提交时，不做日志写入操作，而是每秒钟将log buffer中的数据写入日志文件并flush磁盘一次；1，则在每秒钟或是每次事物的提交都会引起日志文件写入、flush磁盘的操作，确保了事务的ACID；设置为2，每次事务提交引起写入日志文件的动作，但每秒钟完成一次flush磁盘操作。  
实际测试发现，该值对插入数据的速度影响非常大，设置为2时插入10000条记录只需要2秒，设置为0时只需要1秒，而设置为1时则需要229秒。因此，MySQL手册也建议尽量将插入操作合并成一个事务，这样可以大幅提高速度。  
根据MySQL手册，在允许丢失最近部分事务的危险的前提下，可以把该值设为0或2。

18) innodb_log_buffer_size  
log缓存大小，一般为1-8M，默认为1M，对于较大的事务，可以增大缓存大小。  
可设置为4M或8M。

19)innodb_additional_mem_pool_size  
该参数指定InnoDB用来存储数据字典和其他内部数据结构的内存池大小。缺省值是1M。通常不用太大，只要够用就行，应该与表结构的复杂度有关系。如果不够用，MySQL会在错误日志中写入一条警告信息。  
根据MySQL手册，对于2G内存的机器，推荐值是20M，可适当增加。

20) innodb_thread_concurrency=8  
推荐设置为 2*(NumCPUs+NumDisks)，默认一般为8



</font>
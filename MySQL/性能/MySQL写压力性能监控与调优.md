# MySQL写压力性能监控与调优

 时间 2017-08-18 20:24:00 

原文[http://www.cnblogs.com/geaozhang/p/7392056.html][1]



### 一、关于DB的写

##### 1、数据库是一个写频繁的系统

##### 2、后台写、写缓存

##### 3、commit需要写入

##### 4、写缓存失效或者写满-->写压力陡增-->写占读的带宽
1、BBU失效  
2、写入突然增加、cache满

##### 5、日志写入、脏缓冲区写入

### 二、写压力性能监控

全面剖析写压力：多维度的对写性能进行监控。

####  1、OS层面的监控：  `iostat -x`

```shell
    [root@localhost mydata]# iostat -x
    Linux 2.6.32-642.el6.x86_64 (localhost.chinaitsoft.com)     07/05/2017     _x86_64_    (8 CPU)
     
    avg-cpu:  %user   %nice %system %iowait  %steal   %idle
               0.00    0.00    0.03    0.00    0.00   99.97
     
    Device:         rrqm/s   wrqm/s     r/s     w/s   rsec/s   wsec/s avgrq-sz avgqu-sz   await r_await w_await  svctm  %util
    scd0              0.00     0.00    0.00    0.00     0.01     0.00     7.72     0.00    1.25    1.25    0.00   1.25   0.00
    sdc               0.02     0.00    0.01    0.00     0.07     0.00     7.93     0.00    0.89    0.89    0.00   0.72   0.00
    sda               0.18     0.13    0.13    0.05     5.38     1.43    37.95     0.00    6.63    3.99   13.77   2.23   0.04
    sdb               0.03     0.00    0.01    0.00     0.12     0.00     8.72     0.00    1.14    0.80   35.89   0.71   0.00
```

1、写入的吞吐量：`wsec/s sec=512字节=0.5K`、  写入的响应时间：`await`

2、我们需要确认我们的系统是写入还是读取的系统，如果是写入为主的系统，写压力自然就大，相关状态值也就大些。

3、监控系统的io状况，主要查看`%util`、`r/s`、`w/s`，一般繁忙度在`70%`，每秒写也在理想值了；但如果系统目前繁忙度低，每秒写很低，可以增加写入。

####  2、DB层面监控 ，有没有写异常：监控 各种pending（挂起）

```sql
    mysql> show global status like '%pend%';
    +------------------------------+-------+
    | Variable_name                | Value |
    +------------------------------+-------+
    | Innodb_data_pending_fsyncs   | 0     |     #被挂起的fsync
    | Innodb_data_pending_reads    | 0     |     #被挂起的物理读
    | Innodb_data_pending_writes   | 0     |     #被挂起的写
    | Innodb_os_log_pending_fsyncs | 0     |     #被挂起的日志fsync
    | Innodb_os_log_pending_writes | 0     |     #被挂起的日志写
    +------------------------------+-------+
    5 rows in set (0.01 sec)
```

写挂起次数值大于0，甭管是什么写挂起，出现挂起的话就说明出现写压力，所以值最好的是保持为0。监控“挂起”状态值，出现大于0且持续增加，报警处理。

####  3、写入速度监控： 日志写、脏页写

 1、 日志写入速度监控

```sql
    mysql> show global status like '%log%written';
    +-----------------------+-------+
    | Variable_name         | Value |
    +-----------------------+-------+
    | Innodb_os_log_written | 5120  |
    +-----------------------+-------+
    1 row in set (0.01 sec)
```
    
 2、 脏页写入速度监控

```sql
    mysql> show global status like '%a%written';
    +----------------------------+---------+
    | Variable_name              | Value   |
    +----------------------------+---------+
    | Innodb_data_written        | 1073152 |     #目前为止写的总的数据量，单位字节
    | Innodb_dblwr_pages_written | 7       |
    | Innodb_pages_written       | 58      |     #写数据页的数量
    +----------------------------+---------+
    3 rows in set (0.01 sec)
```

 3、 关注比值 ： `Innodb_dblwr_pages_written / Innodb_dblwr_writes` ，表示一次写了多少页

```sql
    mysql> show global status like '%dblwr%';
    +----------------------------+-------+
    | Variable_name              | Value |
    +----------------------------+-------+
    | Innodb_dblwr_pages_written | 7     |     #已经写入到doublewrite buffer的页的数量
    | Innodb_dblwr_writes        | 3     |     #doublewrite写的次数
    +----------------------------+-------+
    2 rows in set (0.00 sec)
```

1、如果该比值是`64：1`，说明`doublewrite`每次都是满写，写的压力很大。

2、如果系统的`double_write`比较高的话，iostat看到的`wrqm/s`(每秒合并写的值)就高，因为`double_write`高意味着每次写基本上都是写`2M`，这时候就发生更多的合并，但`wrqm/s`高并不害怕，因为发生合并是好事，看wrqm/s和繁忙度能不能接受。

#### 4、脏页的量监控

```sql
    mysql> show global status like '%dirty%';
    +--------------------------------+-------+
    | Variable_name                  | Value |
    +--------------------------------+-------+
    | Innodb_buffer_pool_pages_dirty | 0     |     #当前buffer pool中脏页的数量
    | Innodb_buffer_pool_bytes_dirty | 0     |     #当前buffer pool中脏页的总字节数
    +--------------------------------+-------+
    2 rows in set (0.01 sec)
     
    mysql> show global status like 'i%total%';
    +--------------------------------+-------+
    | Variable_name                  | Value |
    +--------------------------------+-------+
    | Innodb_buffer_pool_pages_total | 8192  |     #buffer pool中数据页总量
    +--------------------------------+-------+
    1 row in set (0.01 sec)
```

关注比值 ： `Innodb_buffer_pool_pages_dirty / Innodb_buffer_pool_pages_total` ，脏页占比

通过比值看脏页是否多，比如脏页10%的话，可以判断系统可能不是写为主的系统。

#### 5、写性能瓶颈

```sql
    mysql> show global status like '%t_free';
    +------------------------------+-------+
    | Variable_name                | Value |
    +------------------------------+-------+
    | Innodb_buffer_pool_wait_free | 0     |
    +------------------------------+-------+
    1 row in set (0.01 sec)
     
    mysql> show global status like '%g_waits';
    +------------------+-------+
    | Variable_name    | Value |
    +------------------+-------+
    | Innodb_log_waits | 0     |
    +------------------+-------+
    1 row in set (0.00 sec)
```

1、`Innodb_buffer_pool_wait_free`，如果该值大于`0`，说明`buffer pool`中已经没有可用页，等待后台往回刷脏页，腾出可用数据页，这样就很影响业务了，hang住。

2、`Innodb_log_waits`，如果该值大于0，说明写压力很大，出现了日志等待。

####  6、系统真实负载： rows增删改查 、事务提交、事务回滚

```sql
    mysql> show global status like 'i%rows%';
    +----------------------+-------+
    | Variable_name        | Value |
    +----------------------+-------+
    | Innodb_rows_deleted  | 0     |
    | Innodb_rows_inserted | 145   |
    | Innodb_rows_read     | 233   |
    | Innodb_rows_updated  | 5     |
    +----------------------+-------+
    4 rows in set (0.01 sec)
     
    mysql> show global status like '%commit%';
    +----------------+-------+
    | Variable_name  | Value |
    +----------------+-------+
    | Com_commit     | 0     |
    | Com_xa_commit  | 0     |
    | Handler_commit | 16    |
    +----------------+-------+
    3 rows in set (0.01 sec)
     
    mysql> show global status like '%rollback%';
    +----------------------------+-------+
    | Variable_name              | Value |
    +----------------------------+-------+
    | Com_rollback               | 0     |
    | Com_rollback_to_savepoint  | 0     |
    | Com_xa_rollback            | 0     |
    | Handler_rollback           | 0     |
    | Handler_savepoint_rollback | 0     |
    +----------------------------+-------+
    5 rows in set (0.01 sec)
```

 通过监控系统真实负载，如果业务正常，负载上升，写压力是那自然是无可厚非的。此时，就要根据业务具体情况，进行相应的调优。

### 三、写压力调优参数

降低写压力、加大写入的力度。

通过调整参数降低写压力时，一定要 实时关注`iostat`系统的各项指标。

#### 1、脏页刷新的频率

```sql
    mysql> show variables like 'i%depth%';
    +-----------------------+-------+
    | Variable_name         | Value |
    +-----------------------+-------+
    | innodb_lru_scan_depth | 1024  |
    +-----------------------+-------+
    1 row in set (0.01 sec)
```

默认1024，遍历`lru list`刷新脏页，值越大，说明刷脏页频率越高。

####  2、磁盘刷新脏页的量 ： 磁盘io能力

```sql
    mysql> show variables like '%io_c%';
    +------------------------+-------+
    | Variable_name          | Value |
    +------------------------+-------+
    | innodb_io_capacity     | 200   |
    | innodb_io_capacity_max | 2000  |
    +------------------------+-------+
    2 rows in set (0.00 sec)
```

 根据磁盘io能力进行调整，值越大，每次刷脏页的量越大。

#### 3、`redolog`调优

```sql
    mysql> show variables like 'innodb_log%';
    +-----------------------------+----------+
    | Variable_name               | Value    |
    +-----------------------------+----------+
    | innodb_log_buffer_size      | 16777216 |
    | innodb_log_checksums        | ON       |     #解决数据在io环节的出错问题，checksum值检查
    | innodb_log_compressed_pages | ON       |
    | innodb_log_file_size        | 50331648 |
    | innodb_log_files_in_group   | 2        |
    | innodb_log_group_home_dir   | ./       |
    | innodb_log_write_ahead_size | 8192     |
    +-----------------------------+----------+
    7 rows in set (0.01 sec)
```

 `logfile`大小和组数可能会导致写抖动：日志切换频率需要监控（文件系统层面技巧）。

#### 4、`redolog`的刷新机制

```sql
    mysql> show variables like '%flush%commit';
    +--------------------------------+-------+
    | Variable_name                  | Value |
    +--------------------------------+-------+
    | innodb_flush_log_at_trx_commit | 1     |
    +--------------------------------+-------+
    1 row in set (0.00 sec)
```

默认MySQL的刷盘策略是1，最安全的，但是安全的同时，自然也就会带来一定的性能压力。在写压力巨大的情况下，根据具体的业务场景，牺牲安全性的将其调为0或2。

关于`redolog`的刷盘策略：

也就是用户在commit，事务提交时，处理`redolog`的方式`（0、1、2）`：

  ![][3]

0：当提交事务时，并不将事务的`redo log`写入`logfile`中，而是等待`master thread`每秒的刷新`redo log`。(数据库崩溃丢失数据，丢一秒钟的事务)

1：执行`commit`时将`redo log`同步写到磁盘`logfile`中，即伴有`fsync`的调用(默认是1，保证不丢失事务)

2：在每个提交，日志缓冲被写到文件系统缓存，但不是写到磁盘的刷新(数据库宕机而操作系统及服务器并没有宕机，当恢复时能保证数据不丢失；但是文件系统(OS)崩溃会丢失数据)

#### 5、定义每次日志刷新的时间

```sql
    mysql> show variables like 'innodb_flush_log_at_timeout';
    +-----------------------------+-------+
    | Variable_name               | Value |
    +-----------------------------+-------+
    | innodb_flush_log_at_timeout | 1     |
    +-----------------------------+-------+
    1 row in set (0.01 sec)
```

默认是1，也就是每秒log刷盘，配合`innodb_flush_log_at_trx_commit`来设置，为了充分保证数据的一致性，一般`innodb_flush_log_at_trx_commit=1`，这样的话，`innodb_flush_log_at_timeout`的设置也就没有意义了。因此，该参数的设置只针对`innodb_flush_log_at_trx_commit`为`0/2`起作用。

#### 6、内存脏页占比控制

```sql
    mysql> show variables like '%dirty%pct%';
    +--------------------------------+-----------+
    | Variable_name                  | Value     |
    +--------------------------------+-----------+
    | innodb_max_dirty_pages_pct     | 75.000000 |     #脏页在buffer pool中的最大占比
    | innodb_max_dirty_pages_pct_lwm | 0.000000  |
    +--------------------------------+-----------+
    2 rows in set (0.01 sec)
```

在内存`buffer pool`空间允许的范围下，可以调大脏页允许在内存空间的占比，可解燃眉之急，降低写压力。

#### 7、关闭`doublewrite`降低写压力

```sql
    mysql> show variables like '%doub%';
    +--------------------+-------+
    | Variable_name      | Value |
    +--------------------+-------+
    | innodb_doublewrite | ON    |
    +--------------------+-------+
    1 row in set (0.01 sec)
```

 两次写特性，默认开启，静态参数。 关闭`doublewrite`适合的场景：

 1、海量DML

 2、不惧怕数据损坏和丢失

 3、系统写负载成为主要负载，关闭`doublewrite`，降低写压力

 注意：

 关于参数调整的生效范围，如何调整（静态参数、动态参数），都是要依据官方文档，依照文档进行调参。


[1]: http://www.cnblogs.com/geaozhang/p/7392056.html

[3]: ./img/raUruab.png
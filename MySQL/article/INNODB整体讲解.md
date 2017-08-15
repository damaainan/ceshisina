## [INNODB整体讲解][0]

<font face=微软雅黑>
 2016-09-04 17:13  298人阅读  

 =========================  
## 1. 内存结构

组成部分：  

##### 缓冲池 buffer pool, 
由`innodb_buffer_pool_size`配置  
##### 重做日志缓冲池 redo log buffer, 
由`innodb_log_buffer_size`配置  
##### 额外内存池 additional memory pool, 
由`innodb_additional_mem_pool_size`配置

#### 1.1 buffer pool

 是占最大块内存的部分，用来存放各种数据的缓存；  
innodb将[数据库][6]文件按页(16K)读取到缓冲池，然后按最少使用(LRU)[算法][7]来保留缓存数据；数据文件修改时，先修改缓存池中的页（即脏页），然后按一定频率将脏页刷新到文件；

 缓冲池中的数据页类型有：  
**索引页、数据页、undo页、插入缓冲(insert buffer)、自适应哈希索引(adaptive hash index)、锁信息(lock info)、数据字典信息(data dictionary)**

查看buffer pool的使用情况  

    show engine innodb status\G

结果示例:  

```
=====================================  
120610 18:31:49 INNODB MONITOR OUTPUT  
=====================================  
Per second averages calculated from the last 44 seconds  
...  
----------------------  
BUFFER POOL AND MEMORY  
----------------------  
Total memory allocated 53657600; in additional pool allocated 0  
Dictionary memory allocated 39802  
Buffer pool size 3200  
Free buffers 2790  
Database pages 409  
Old database pages 0  
Modified db pages 0  
Pending reads 0  
Pending writes: LRU 0, flush list 0, single page 0  
Pages made young 0, not young 0  
0.00 youngs/s, 0.00 non-youngs/s  
Pages read 409, created 0, written 4  
0.09 reads/s, 0.00 creates/s, 0.09 writes/s  
Buffer pool hit rate 998 / 1000, young-making rate 0 / 1000 not 0 / 1000  
Pages read ahead 0.00/s, evicted without access 0.00/s  
LRU len: 409, unzip_LRU len: 0  
I/O sum[0]:cur[0], unzip sum[0]:cur[0]  
...
```

 分析  
(1)Per second averages calculated from the last 44 seconds  
show engine innodb status 显示的过去某个时间段内的使用情况  

(2)Total memory allocated 53657600; in additional pool allocated 0  
当前分配的`memory`大小和`additional pool`大小，单位`byte`  

(3)接下来的`pool`中各项占的大小  
Dictionary memory allocated 39802 数据字典内存区大小，单位byte  
Buffer pool size 3200 总页数, `3200*16/1024=50M `  
Free buffers 2790 空闲的页数, `2790*16/1024=43M`  
Database pages 409 已使用的缓冲页数, `409*16/1024=6.3M`  
Old database pages 0   
Modified db pages 0 表示脏页数

#### 1.2 log buffer  

作用  
将重做日志先放入这个区，然后按一定频率将其刷新至重做日志文件，一般情况下每1秒就会刷新一次；

 配置  
一般不需配置很大；

#### 1.3 额外内存池  
作用：  
innodb申请缓冲池(`buffer pool`)，但每个缓冲池中的页缓冲有对应的缓冲控制对象(`buffer control block`)，这些对象记录LRU、锁、等待等信息，这些对象的内存需要多额外内存池中申请；因此当buffer pool较大时，也需相应增大该值

 ====================================  
## 2. innodb的后台线程

默认情况下，innodb有以下几类线程：  

##### io thread，分为read thread和write thread；  
##### master thread，1个  
##### lock monit thread，1个  
##### error monit thread，1个

##  3. io thread  
包括以下几种：  
###### read thread  
###### write thread  
###### insert buffer thread  
###### log thread

 配置设置:  
`read thread`和`write thread`分别由`innodb_read_io_threads`和`innodb_write_io_threads`来配置；  

`log thread`和`insert buffer thread`一般是1个；

 查看  

    show variables like '%threads%';  
    show engine innodb status\G

##  4. master thread  

完成的工作  
主循环 loop  
后台循环 background loop  
刷新循环 flush loop  
暂停循环 suspend loop

#### 4.1 主循环 loop  
该循环中完成的有两种操作，每秒一次的操作和每10秒一次的操作

每秒一次的操作:  
a)日志缓冲刷新到磁盘，即使这个事务未提交（总是）；  
b)合并插入缓冲（可能），会根据前一秒内的io次数判断，如果小于5次，可以执行合并插入缓冲；  
c)至多刷新100个脏页至磁盘（可能），通过判断脏页比例是否超过了`innodb_max_dirty_pages_pct`这个设置值来进行，未超过则不执行；  
d)无用户活动，切换到`background loop`（可能）；

每10秒一次的操作:  
a)**刷新100个脏页到磁盘**（可能），如果过去10秒磁盘io操作小于200次，则执行本操作；  
b)合并至多5个插入缓冲（总是）；  
c)日志缓冲刷新到磁盘（总是）；  
d)删除无用的undo页（总是）；  
e)**刷新100个或10个脏页到磁盘**（总是），判断缓冲池脏页比例，超过70%则刷新100个脏页，比例小于10%则刷新10个脏页；  
f)产生一个检查点`checkpoint`（总是），注意此时并不是把所有脏页都刷新到了磁盘，只是将最老日志序列号的页写入磁盘；

#### 4.2 后台循环 background loop  
当没有用户活动或数据库关闭时，会切换到这个循环；

完成的操作  
a)删除无用的undo页（总是）；  
b)合并20个插入缓冲（总是）；  
c)跳回到主循环（总是）；  
d)不断刷新100个页，直到符合条件（可能，跳转到flush loop中完成）；

#### 4.3 flush loop  
由`background loop`跳转到此loop中完成刷新脏页的工作；  
当`flush loop`中无事可做时会切换到`suspend loop`；

#### 4.4 suspend loop  
该loop将`master thread`挂起，等待事件发生；在启用了innodb引擎，但未使用innodb表时，`master thread`总是处于挂起状态；

#### 4.5 查看示例  


```sql
show engine innodb status\G
 -----------------  
BACKGROUND THREAD  
-----------------  
srv_master_thread loops: 4 1_second, 4 sleeps, 0 10_second, 6 background, 6 flush  
srv_master_thread log flush and writes: 4
```

说明  
(1)主循环，每秒一次的操作有4次，每10秒一次的操作有0次；一般这两个比例在1:10时较合理；  
(2)`background loop`，执行了6次，`flush loop`执行了6次  
(3)其中的`sleeps`指循环中的每秒sleep的操作，一般压力较小情况下此值和每秒一次的操作数相同，压力大时会小于每秒一次的操作数；

#### 4.6 新版本优化了上边的判断配置值  
`innodb_io_capacity=200`  
合并插入缓冲时，合并插入缓冲的数量为`innodb_io_capacity`数值的`5%`;  
缓冲区刷新到磁盘时，刷新脏页数量为`innodb_io_capacity`；

 使用情况  
在使用了ssd磁盘，或做了raid后，可将此值设置较大，直到符合磁盘io的吞吐量；

#### 4.7 其它配置值注意情况  
`innodb_max_dirty_pages_pct=90`  
每秒的主loop和flush loop中，会判断此值，如果大于才刷新100个脏页，在数据库压力很大时，这时刷新速度反而会降低；google的[测试][8]表明75是个合理值；

 `innodb_adaptive_flushing=on`  
该值影响每秒刷新脏页的操作，开启此配置后，刷新脏页会通过判断产生重做日志的速度来判断最合适的刷新脏页的数量；

 ===============================  
## 5. innodb的插入缓冲  

是innodb引擎的关键特性之一；  
插入缓冲，`insert buffer`，是从缓冲池中分配的；用来对插入的性能进行优化和提升；  
具体讲，即是对有非唯一的非聚集索引的索引页的插入进行了缓冲，之后合并再插入；

 工作原理  
当表只有一个聚集索引时，插入顺序是按照该主键的顺序进行插入的，不需要磁盘的随机读取；  
当表有一个甚或多个非聚集索引时，且该索引不是表的唯一索引时，插入时数据而按主键顺序存放，但叶子节点需要离散地访问非聚集索引页，插入性能会降低；此时，插入缓冲生效，先判断非聚集索引页是否在缓冲池中，如在则直接插入；如不在，则先放入一个插入缓冲区，然后再以一定的频率执行插入缓冲和非聚集索引页子节点的合并操作；

 插入缓冲生效的条件  
存在非聚集索引，且索引不是表的唯一索引；

 插入缓冲的分析  


```sql
show engine innodb status\G
 -------------------------------------  
INSERT BUFFER AND ADAPTIVE HASH INDEX  
-------------------------------------  
Ibuf: size 1, free list len 2366, seg size 2368, 0 merges  
merged operations:  
insert 0, delete mark 0, delete 0  
discarded operations:  
insert 0, delete mark 0, delete 0  
Hash table size 103867, node heap has 1 buffer(s)  
0.00 hash searches/s, 0.20 non-hash searches/s
```

 说明  
(1)`seg size`表示当前插入缓冲的大小 `2368*16K，free list len`表示空闲列表的长度，`size`表示已经合并记录页的数量，`merges`表示合并的数量；  
(2)`merged operations`是合并的情况，此处是我们最关注的部分；`inserts`表示插入的记录数；

 ====================================  
## 6. innodb的doublewrite  

是innodb引擎的关键特性之一；  
两次写保证的是innodb的可靠性；插入缓冲保证的是性能；

 数据文件的逻辑结构：  
**页 page 16K, 区 extent 1M, 段 seg 2M**

 主要作用：  
针对写磁盘文件失败（由于各种原因）时，通过页的副本还原该页，再进行redo恢复，保证数据的完整性；

 工作原理：  
由两部分组成，一部分是内存中的`doublewrite buffer`，大小为`2M`，另一部分是磁盘上共享表空间中连续的两个区`extend`, 2M；  
刷新脏页时，先后做三步工作：  

一先将脏页拷贝到内存的`doublewrite buffer`，  
二是通过该`buffer`两次写入（每次写入1M）到共享表空间，然后马上调用`fsync函数`同步磁盘；这个过程是顺序写的，开销不大；  
三是再将该`buffer`中的页写入各个表空间文件中，此过程中的写入是离散的；

 查看doublewrite的运行情况  

```sql
show global status like 'innodb_dblwr%'\G  
+----------------------------+-------+  
| Variable_name | Value |  
+----------------------------+-------+  
| Innodb_dblwr_pages_written | 4 |  
| Innodb_dblwr_writes | 2 |  
+----------------------------+-------+
```

 结果分析  
`pages_written`为写的页数，`writes`为写的次数，一般两者的比例小于`64：1`；  
如果比例较高，说明写入压力较大；

 写入磁盘失效时的恢复原理  
从共享表空间中的`doublewrite`中找到该页的一个副本，拷贝到表空间文件，再应用重做日志进行恢复；

 配置建议  

    innodb_doublewrite=ON  
    #skip_innodb_doublewrite 或 innodb_doublewrite=OFF  
需要提供较快的性能时，可禁用双写；但在主服务器上，任何时间都应确保开启双写功能；  
有些文件系统本身提供了部分写失效的机制，如zfs文件系统，此时可以关闭双写；

 =================================  
## 7. 自适应哈希索引  

工作机制  
innodb会自动监控表上索引的查找，如果发现建立哈希索引可以带来速度的提升，则建立哈希索引；  
建立的依据是根据访问的频率和模式自动进行的；据官方文档说明，对读取和写入可以提高2倍，对辅助索引的连接操作性能可提高5倍；

 适用条件  
只能用来搜索等值的查询，如`select * from table where index_col='xxx'；`   
对其他类型是不会被使用的；

 查看运行情况  

```sql
show engine innodb status\G  
-------------------------------------  
INSERT BUFFER AND ADAPTIVE HASH INDEX  
-------------------------------------  
Ibuf: size 1, free list len 2366, seg size 2368, 0 merges  
merged operations:  
insert 0, delete mark 0, delete 0  
discarded operations:  
insert 0, delete mark 0, delete 0  
Hash table size 103867, node heap has 1 buffer(s)  
0.00 hash searches/s, 0.00 non-hash searches/s
```

 结果说明  
`hash searches`显示了hash查找的次数  
`non-hash searches`显示了不能利用hash查找的次数；

 配置：  

    innodb_adaptive_hash_index=ON

 =================================  
## 8. innodb启动关闭和恢复相关

`innodb_fast_shutdown=1`  
可取值0, 1, 2，默认为1  
0 关闭[MySQL][6]时，完成所有的`full purge`和`merge insert` buffer操作，这个可能会花费很长时间，不次启动可不用做重做日志恢复；  
在做plugin升级时通常需要调整这个值为0  
1 关闭[mysql][6]时，不完成上述的`full purge`和`merge insert buffer`操作，只是刷新缓冲池中的一些数据脏页；  
2 关闭mysql时，既不完成上述的full purge和merge insert buffer操作，也不刷新缓冲池中的一些数据脏页；只是将日志写入日志文件，下次启动时进行恢复；

`innodb_force_recovery=0..6 ` 
0 默认值，表示当需要恢复时执行所有的恢复操作；  
1-6 用于恢复不能启动的或崩溃的数据文件，此时只能进行`select/create/drop`等操作，不以进行`update/delete/insert`操作；  
1 忽略检查到的`corrupt`页  
2 阻止主线程的运行，如主线程需要执行`full purge`会导致`crash`  
3 不执行事务回滚  
4 不执行插入缓冲的合并操作  
5 不查看撤销日志`undo log`，视未提交的事务为已提交；  
6 不执行前滚操作  
数据库出现问题时，可利用此配置尝试进行备份数据，结合`error log`观察操作过程；

 ==============================  
## 9. redo日志，重做日志  

作用：  
用于保证事务已提交时发生故障，数据库的数据如果如果和日志不一致，则将日志中的数据重写到数据库中；  
redo存放在重做日志文件中；

 过程：  
一个事务开始时，redo会记录该事务的一个lsn(log sequence number,日志序列号)，事务执行时会往日志缓冲里插入事务日志，事务提交时，将日志缓冲写入磁盘；

 观察日志情况：  

```sql
show engine innodb status\G  
...  
---  
LOG  
---  
Log sequence number 1995757275  
Log flushed up to 1995757275  
Last checkpoint at 1995757275  
0 pending log writes, 0 pending chkp writes  
1220353 log i/o's done, 0.27 log i/o's/second
```
 说明：  
`log sequence number` 为当前的lsn(`log sequence number`,日志序列号);  
`log flushed up to` 为刷新到redo的lsn;  
`Last checkpoint at` 为刷新到磁盘的lsn;

 ===========================  
## 10. undo日志，撤消日志  

作用：  
用于保证事务未提交时发生故障后，数据库的数据如果和日志不一致，则将日志中的数据写回到数据库中；  
undo存放在数据库内部的一个特殊段`segment`中，称为undo段，位于共享表空间内；

 过程  
undo并不是物理地恢复，而是逻辑地恢复；这个不难理解，因为同一个页，可能有多个事务在进行操作，不可能进行物理地恢复；  
回滚时，实际是做一个与先前相反的工作；对于`insert`，`undo`会完成一个`delete`；对于`delete`，会执行一个`delete`；对于`update`, 会执行一个相反的`update`；

</font>

[0]: /caomiao2006/article/details/52433400
[6]: http://lib.csdn.net/base/mysql
[7]: http://lib.csdn.net/base/datastructure
[8]: http://lib.csdn.net/base/softwaretest
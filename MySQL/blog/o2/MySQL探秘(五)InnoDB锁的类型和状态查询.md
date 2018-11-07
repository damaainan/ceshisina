## MySQL探秘(五):InnoDB锁的类型和状态查询

时间：2018年11月04日

来源：<https://juejin.im/post/5bded0b76fb9a049b829e58e>

 锁是数据库系统区分于文件系统的一个关键特性。数据库使用锁来支持对共享资源进行并发访问，提供数据的完整性和一致性。此外，数据库事务的隔离性也是通过锁实现的。InnoDB在此方面一直优于其他数据库引擎。InnoDB会在行级别上对表数据上锁，而MyISAM只能在表级别上锁，二者性能差异可想而知。
#### InnoDB存储引擎中的锁

 InnoDB存储引擎实现了如下两种标准的行级锁：


* 共享锁(S Lock)，允许事务读取一行
* 排他锁(X Lock)，允许事务删除或更新一行数据


  如果一个事务T1已经获取了行r的共享锁，那么另外一个事务T2可以立刻获得行r的共享锁，因为读取并不会改变数据，可以进行并发的读取操作；但若其他的事务T3想要获取行r的排他锁，则必须等待事务T1和T2释放行r上的共享锁之后才能继续，因为获取排他锁一般是为了改变数据，所以不能同时进行读取或则其他写入操作。

| | X | S |
| - | - | - |
| X | 不兼容 | 不兼容 |
| S | 不兼容 | 兼容 |


 InnoDB存储引擎支持多粒度锁定，这种锁定允许事务在行级上的锁和表级上的锁同时存在。为了支持在不同粒度上进行加锁操作，InnoDB存储引擎支持一种称为意向锁的锁方式。意向锁是将锁定的对象分为多个层次，意向锁意味着事务希望在更细粒度上进行加锁。

 InnoDB存储引擎的意向锁即为表级别的锁。设计目的主要是为了在一个事务中揭示下一行将被请求的锁类型。其支持两种意向锁：


* 意向共享锁(IS Lock)，事务想要获得一张表中某几行的共享锁
* 意向排他锁(IX Lock)，事务想要获得一张表中某几行的排他锁


 需要注意的是意向锁是表级别的锁，它不会和行级的X，S锁发生冲突。只会和表级的X，S发生冲突。故表级别的意向锁和表级别的锁的兼容性如下表所示。

| | IS | IX | S | X |
| - | - | - | - | - |
| IS | 兼容 | 兼容 | 兼容 | 不兼容 |
| IX | 兼容 | 兼容 | 不兼容 | 不兼容 |
| S | 兼容 | 不兼容 | 兼容 | 不兼容 |
| X | 不兼容 | 不兼容 | 不兼容 | 不兼容 |


 向一个表添加表级X锁的时候(执行ALTER TABLE, DROP TABLE, LOCK TABLES等操作)，如果没有意向锁的话，则需要遍历所有整个表判断是否有行锁的存在，以免发生冲突。如果有了意向锁，只需要判断该意向锁与即将添加的表级锁是否兼容即可。因为意向锁的存在代表了，有行级锁的存在或者即将有行级锁的存在，因而无需遍历整个表，即可获取结果。


![][0]


 如果将上锁的对象看成一棵树，那么对最下层的对象上锁，也就是对最细粒度的对象进行上锁，那么首先需要对粗粒度的对象上锁。如上图所示，如果需要对表1的记录m行上X锁，那么需要先对表1加意向IX锁，然后对记录m上X锁。如果其中任何一个部分导致等待，那么该操作需要等待粗粒度锁的完成。
#### InnoDB锁相关状态查询

 用户可以使用INFOMATION_SCHEMA库下的INNODB_TRX、INNODB_LOCKS和INNODB_LOCK_WAITS表来监控当前事务并分析可能出现的锁问题。INNODB_TRX的定义如下表所示，其由8个字段组成。

| 字段名 | 说明 |
| - | - |
| trx_id | InnoDB存储引擎内部唯一的事务ID |
| trx_state | 当前事务的状态 |
| trx_started | 事务的开始时间 |
| trx_request_lock_id | 等待事务的锁ID。如果trx_state的状态为LOCK WAIT,那么该字段代表当前事务等待之前事务占用的锁资源ID |
| trx_wait_started | 事务等待的时间 |
| trx_weight | 事务的权重，反映了一个事务修改和锁住的行数，当发生死锁需要回滚时，会选择该数值最小的进行回滚 |
| trx_mysql_thread_id | 线程ID，SHOW PROCESSLIST 显示的结果 |
| trx_query | 事务运行的SQL语句 |


```sql
mysql> SELECT * FROM information_schema.INNODB_TRX\G;
************************************* 1.row *********************************************
trx_id:  7311F4
trx_state: LOCK WAIT
trx_started: 2010-01-04 10:49:33
trx_requested_lock_id: 7311F4:96:3:2
trx_wait_started: 2010-01-04 10:49:33
trx_weight: 2
trx_mysql_thread_id: 471719
trx_query: select * from parent lock in share mode
```

 INNODB_TRX表只能显示当前运行的InnoDB事务，并不能直接判断锁的一些情况。如果需要查看锁，则还需要访问表INNODB_LOCKS，该表的字段组成如下表所示。

| 字段名 | 说明 |
| - | - |
| lock_id | 锁的ID |
| lock_trx_id | 事务的ID |
| lock_mode | 锁的模式 |
| lock_type | 锁的类型，表锁还是行锁 |
| lock_table | 要加锁的表 |
| lock_index | 锁住的索引 |
| lock_space | 锁住的space id |
| lock_page | 事务锁定页的数量，若是表锁，则该值为NULL |
| lock_rec | 事务锁定行的数量，如果是表锁，则该值为NULL |
| lock_data | 事务锁住记录的主键值，如果是表锁，则该值为NULL |


```sql
mysql> SELECT * FROM information_schema.INNODB_LOCKS\G;
*************************************** 1.row *************************************
lock_id: 7311F4:96:3:2
lock_trx_id: 7311F4
lock_mode: S
lock_type: RECORD
lock_table: 'mytest'.'parent'
lock_index: 'PRIMARY'
lock_space: 96
lock_page: 3
lock_rec: 2
lock_data: 1
```

 通过表INNODB_LOCKS查看每张表上锁的情况后，用户就可以来判断由此引发的等待情况。当时当事务量非常大，其中锁和等待也时常发生，这个时候就不那么容易判断。但是通过表INNODB_LOCK_WAITS，可以很直观的反应当前事务的等待。表INNODB_LOCK_WAITS由四个字段组成，如下表所示。

| 字段名 | 说明 |
| - | - |
| requesting_trx_id | 申请锁资源的事务ID |
| requesting_lock_id | 申请的锁的ID |
| blocking_trx_id | 阻塞的事务ID |
| blocking_lock_id | 阻塞的锁的ID |


```sql
mysql> SELECT * FROM information_schema.INNODB_LOCK_WAITS\G;
*******************************************1.row************************************
requesting_trx_id: 7311F4
requesting_lock_id: 7311F4:96:3:2
blocking_trx_id: 730FEE
blocking_lock_id: 730FEE:96:3:2
```

 通过上述的SQL语句，用户可以清楚直观地看到哪个事务阻塞了另一个事务，然后使用上述的事务ID和锁ID，去INNODB_TRX和INNDOB_LOCKS表中查看更加详细的信息。
#### 后记

 我们后续还会学习InnoDB的一致性非锁定读相关的知识，请大家持续关注。

[0]: ./img/166de5f6fe7e3851.png

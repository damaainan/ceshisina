## RR和RC隔离级别下的索引和锁的测试脚本

来源：[https://elsef.com/2018/11/29/RR和RC隔离级别下的索引和锁的测试脚本/](https://elsef.com/2018/11/29/RR和RC隔离级别下的索引和锁的测试脚本/)

时间 2018-11-29 08:00:00



## 基本概念


## 当前读与快照读

在MVCC中，读操作可以分成两类：快照读 (snapshot read)与当前读 (current read)。
快照读，读取的是记录的可见版本 (有可能是历史版本)，不用加锁。当前读，读取的是记录的最新版本，并且对返回的记录，都会加上锁，保证在事务结束前，这条数据都是最新版本。


* 快照读：简单的select操作，属于快照读，不加锁(Serializable除外)。
```sql
select * from table where ?;
```

    
* 当前读：特殊的读操作，插入/更新/删除操作，属于当前读，需要加锁。
```sql
select * from table where ? lock in share mode;
select * from table where ? for update;
insert into table values ();
update table set ? where ?;
delete from table where ?;
```



## 隔离级别与加锁机制


* Read Uncommitted
会发生脏读，不考虑。

    
* Read Committed (RC)
针对当前读，RC隔离级别保证对读取到的记录加锁 (Gap Locking)，存在幻读现象。

    
* Repeatable Read (RR)
针对当前读，RR隔离级别保证对读取到的记录加锁 (Record Locking)，同时保证对读取的范围加锁，新的满足查询条件的记录不能够插入 (Gap Locking)，不存在幻读现象。

    
* Serializable
所有的读操作均为退化为当前读，读写冲突，因此并发度急剧下降，不考虑。



## 测试脚本

```sql
-- 基本操作 --
-- 查询事务隔离级别，默认是RR
show variables like '%isolation%';

-- 设置事务隔离级别为RC
set session transaction isolation level read committed;


-- 数据初始化 --
begin;
drop table if exists user;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL,
  `age` int(11) NOT NULL,
  `address` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `idx_age` (`age`)
);

insert into user (email, age, address) values ("test1@elsef.com", 18, "address1");
insert into user (email, age, address) values ("test2@elsef.com", 20, "address2");
insert into user (email, age, address) values ("test3@elsef.com", 20, "address3");

commit;
select * from user;



-- 一、trx_id示例
begin;
SELECT TRX_ID FROM INFORMATION_SCHEMA.INNODB_TRX  WHERE TRX_MYSQL_THREAD_ID = CONNECTION_ID();
select * from user;
SELECT TRX_ID FROM INFORMATION_SCHEMA.INNODB_TRX  WHERE TRX_MYSQL_THREAD_ID = CONNECTION_ID();
SHOW ENGINE INNODB STATUS;
update user set age = 22 where id = 3;
-- 查询事务id
SELECT TRX_ID FROM INFORMATION_SCHEMA.INNODB_TRX  WHERE TRX_MYSQL_THREAD_ID = CONNECTION_ID();
-- INNODB 引擎状态
SHOW ENGINE INNODB STATUS;
commit;

-- 二、可重复读、不可重复读示例

-- session1
set session transaction isolation level read committed;
begin;
-- session2
set session transaction isolation level repeatable read;
begin;
-- session1
select * from user;
-- session2
select * from user;
-- session3
begin;
insert into user (email, age, address) values ("test4@elsef.com", 30, "address4");
commit;
-- session1 这里因为是RC，所以可以读到trx3提交的新数据，这里如果是证明不可重复读的话应该使用update而不是insert
select * from user;
commit;
-- session2 这里因为是RR，所以不会读到trx3提交的新数据
select * from user;
commit;

-- 三、快照读幻读示例
-- session1
set session transaction isolation level repeatable read;
begin;
-- 这里使用快照读
select * from user;
-- session2
begin;
insert into user (email, age, address) values ("test4@elsef.com", 30, "address4");
commit;
select * from user;
-- session1
select * from user; -- 这里读不到test4@的数据，因为是RR
-- 这里发生了幻读
insert into user (email, age, address) values ("test4@elsef.com", 30, "address4"); -- 插入失败因为email唯一索引冲突
commit;

-- 四、当前读幻读示例
-- RC
-- session1
set session transaction isolation level read committed;
begin;
-- 这里会对所有满足条件的age=20的记录加锁，因为是RC，所以没有GAP锁
delete from user where age = 20;
select * from user;
-- session2
set session transaction isolation level read committed;
begin;
-- 因为trx1没有加GAP锁，所以之类可以插入age=20的记录
insert into user (email, age, address) values ("test4@elsef.com", 20, "address4");
select * from user; -- 可以查到4条数据，可以读到trx1的删除数据，因为是RC，trx1未提交所以没影响trx2
commit;
-- session1
select * from user; -- 可以读到trx2新插入的数据，虽然trx1是当前读，但是并未添加相应的next-key锁，没有阻止trx2的新数据插入
commit;

--RR
-- session1
set session transaction isolation level repeatable read;
begin;
delete from user where age = 20;
select * from user;
-- session2
begin;
-- 这里会阻塞，因为trx1在age=20周围加了GAP锁
-- 非唯一索引，首先，通过索引定位到第一条满足查询条件的记录，加记录上的X锁，加GAP上的GAP锁，然后加主键聚簇索引上的记录X锁；
-- 然后读取下一条，重复进行。直至进行到第一条不满足条件的记录，此时，不需要加记录X锁，但是仍旧需要加GAP锁，最后返回结束。
insert into user (email, age, address) values ("test4@elsef.com", 20, "address4");
-- 直到超时，ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
-- 此时如果查询可以看到3条记录
commit;
-- session1
-- 此时只能看到1条记录，另外两条被删除了
select * from user;
commit;

-- 唯一索引+RC
-- session1
set session transaction isolation level read committed;
begin;
delete from user where email = "test3@elsef.com";
-- session2
begin;
-- 可以读到，因为trx1是RC
select * from user where email = "test3@elsef.com";
-- 尝试更新这个记录的age，会阻塞直到超时，因为email是唯一索引已经被trx1锁住了，同时也会在对应的主键索引上加锁
-- 注意这里操作的id=3就是trx1中操作的email的同一行记录
update user set age = 40 where id = 3;
-- session1
commit;
-- session2
commit;

-- 无索引+RC
-- session1
set session transaction isolation level read committed;
begin;
-- 由于address字段无索引，所以Innodb会对所有行进行加锁，由MySQL server进行判断并释放锁
delete from user where address = "address3";
-- session2
set session transaction isolation level read committed;
begin;
-- 这一行会成功，因为这一行没有加锁（先加了后释放了）
update user set age = 10 where address = "address2";
-- 这一行同样会被阻塞，原因是它已经被trx1的语句加了锁了，全部符合条件的都加锁了
update user set age = 10 where address = "address3";
-- session1
commit;
-- session2
commit;

-- 非唯一索引+RR
-- session1
set session transaction isolation level repeatable read;
begin;
delete from user where age = 20;
-- session2
set session transaction isolation level repeatable read;
begin;
-- 这里会阻塞，因为trx1中已经锁住了age=20的记录以及加上了GAP锁，所以这里18已经落入锁区间
insert into user (email, age, address) values ("test4@elsef.com", 18, "address4");
-- session1
commit;
-- session2
commit;

-- 无索引RR
-- session1
set session transaction isolation level repeatable read;
begin;
-- 没有索引，那么会锁上表中的所有记录，同时会锁上主键索引上的所有GAP，杜绝所有的并发更新操作
delete from user where address = "address3";
-- session2
set session transaction isolation level repeatable read;
begin;
-- 这里会阻塞，原因是主键已经被加上了GAP锁，所以新的插入不能执行成功
insert into user (email, age, address) values ("test4@elsef.com", 18, "address4");
-- session1
commit;
-- session2
commit;

-- 死锁 简单示例
-- session1
begin;
delete from user where id = 1;
-- session2
begin;
delete from user where id = 3;
-- session1
delete from user where id = 3;
-- seession2
-- 这里MySQL判断发生了死锁，中断了一个trx
-- ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction
delete from user where id = 1;
-- session1
rollback;
-- session2;
rollback;

-- 五、死锁 insert示例
drop table if exists t1;
begin;
create table t1 (
  `id` bigint not null auto_increment,
  primary key (`id`)
);
insert into t1 values(1);
insert into t1 values(5);
commit;
select * from t1;
-- session1
begin;
insert into t1 values (2);
-- sessioin2
begin;
-- 这里会阻塞
insert into t1 values (2);
-- session3
begin;
-- 这里会阻塞
insert into t1 values (2);
-- session1;
-- 此时回滚，trx2和trx3收到通知，MySQL自动中断一个trx，因为发生了死锁
-- ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction
rollback;
--session2;
rollback;
--session3;
rollback;
```

本文首次发布于ElseF’s Blog, 作者[@stuartlau][0],转载请保留原文链接.


* [Previous很少有人说清楚的MySQL如何用REPEATABLE-READ解决幻读问题][1]    


[0]: http://github.com/stuartlau
[1]: https://elsef.com/2018/11/28/%E7%BB%8F%E5%B8%B8%E8%A2%AB%E8%AF%AF%E8%A7%A3%E7%9A%84MySQL%E4%B8%AD%E5%AF%B9REPEATABLE-READ/
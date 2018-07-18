# MySQL锁系列（五）之 隔离级别

 时间 2017-06-14 17:43:32  Focus on MySQL

原文[http://keithlan.github.io/2017/06/14/innodb_locks_mvcc_isolation/][1]


## 一、隔离级别

事务的隔离级别有4种: SQL-1992 ，但是我只想介绍其中两种，因为其他的两个根本就用不上

### 1.1 什么叫一致性锁定读 和 一致性非锁定读

* 一致性锁定读
    1. 读数据的时候，会去加S-lock、x-lock
    2. eg：select ... for update , select ... lock in share mode
    3. dml语句
    
* 一致性非锁定读
    1. 读数据的时候，不加任何的锁，快照读（snapshot read）    
    2. eg: select ... 最普通的查询语句
    
### 1.2 什么是幻读(不可重复读)

* 概念

    一个事务内的同一条【一致性锁定读】SQL多次执行，**读到的结果不一致，我们称之为幻读**。

* 实战

```
    * set global tx_isolation='READ-COMMITTED'
```

```sql
    > 事务一: 
    
    
    root:test> begin;select * from lc for update;
    +------+
    | id |
    +------+
    |    1 |
    | 2 |
    +------+
    
    
    > 事务二：
    
    root:test>begin; insert into lc values(3);
    Query OK, 1 row affected (0.00 sec)
    
    root:test> commit ;
    Query OK, 0 rows affected (0.00 sec)
    
    > 事务一：  
    
    
    root:test> select * from lc for update; 
    +------+
    | id |
    +------+
    |    1 |
    |    2 |
    | 3 |
    +------+
    3 rows in set (0.00 sec)
```
    
* 同一个事务一中，同一条`select * from lc for update` (一致性锁定读) 执行两次，得到的结果不一致，说明产生了幻读
* 同一个事务一中，同一条`select * from lc`  (一致性非锁定读) 执行两次，得到的结果不一致，说明产生了幻读
* 我们姑且认为，幻读和不可重复读为一个概念，实际上也差不多一个概念。
    
### 1.3 什么是脏读

  这个大家都很多好理解，就是**事务一还没有提交的事务，却被事务二读到了**，这就是**`脏读`**
    
### 1.4 repeatable-read（RR）

* 什么是RR
    * 学名： **`可重复读`**    
    * 顾名思义：一个事务内的同一条【一致性锁定读】SQL多次执行，读到的结果一致，我们称之为可重复读。
    * **解决了幻读的问题**
    
### 1.5 read-committed （RC）

* 学名：**`可提交读`**  
* 顾名思义: 只要其他事务提交了，我就能读到   
* **解决了脏读**的问题，**没有解决幻读**的问题
    

## 二、隔离级别是如何实现的

就拿上面那个简单的例子来佐证好了

## 环境

```sql
    dba:lc_4> show create table lc;
    +-------+--------------------------------------------------------------------------------------------------------+
    | Table | Create Table |
    +-------+--------------------------------------------------------------------------------------------------------+
    | lc    | CREATE TABLE `lc` (
     `id` int(11) NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +-------+--------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    
    dba:lc_4> select * from lc;
    +----+
    | id |
    +----+
    |  1 |
    |  2 |
    | 3 |
    +----+
    3 rows in set (0.00 sec)
```

## 2.1 RR

RR 如何解决幻读问题？

RR 的锁算法：next-key lock

* 解决幻读的案例

```sql
    dba:lc_4> set tx_isolation='repeatable-read';
    Query OK, 0 rows affected (0.00 sec)
    
    dba:lc_4> select * from lc for update ;
    +----+
    | id |
    +----+
    |  1 |
    |  2 |
    | 3 |
    +----+
    3 rows in set (0.00 sec)
```

    这时候，查看下锁的情况：

```
    
    ------------
    TRANSACTIONS
    ------------
    Trx id counter 133588361
    Purge done for trx's n:o < 133588356 undo n:o < 0 state: running but idle
    History list length 892
    LIST OF TRANSACTIONS FOR EACH SESSION:
    ---TRANSACTION 421565826150000, not started
    0 lock struct(s), heap size 1136, 0 row lock(s)
    ---TRANSACTION 421565826149088, not started
    0 lock struct(s), heap size 1136, 0 row lock(s)
    ---TRANSACTION 133588360, ACTIVE 4 sec
    2 lock struct(s), heap size 1136, 4 row lock(s)
    MySQL thread id 135, OS thread handle 140001104295680, query id 1176 localhost dba cleaning up
    TABLE LOCK table `lc_4`.`lc` trx id 133588360 lock mode IX
    RECORD LOCKS space id 289 page no 3 n bits 72 index PRIMARY of table `lc_4`.`lc` trx id 133588360 lock_mode X --next key lock ， 锁记录和范围
    Record lock, heap no 1 PHYSICAL RECORD: n_fields 1; compact format; info bits 0
     0: len 8; hex 73757072656d756d; asc supremum;; --next-key lock, 锁住正无穷大
    
    Record lock, heap no 2 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000001; asc     ;;  --next-key lock, 锁住1和1之前的区间，包括记录 (negtive,1]
     1: len 6; hex 000007f6657e; asc     e~;;
     2: len 7; hex e5000040220110; asc    @"  ;;
    
    Record lock, heap no 3 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000002; asc     ;;  --next-key lock, 锁住2和1之前的区间，包括记录 (1,2]
     1: len 6; hex 000007f6657f; asc     e ;;
     2: len 7; hex e6000040330110; asc    @3  ;;
    
    Record lock, heap no 4 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000003; asc     ;;  --next-key lock, 锁住3和2之间的区间，包括记录 (2,3]
     1: len 6; hex 000007f66584; asc     e ;;
     2: len 7; hex e9000040240110; asc    @$  ;;
```
    
* 总结下来就是：
    1. (negtive bounds,1] ， (1,2] ， (2,3]，(3,positive bounds) --锁住的记录和范围，相当于表锁  
    2. 这时候，session 2 插入任何一条记录，会被锁住，所以幻读可以避免，尤其彻底解决了幻读的问题
    

## 2.2 RC

RC 的锁算法：record locks

幻读对线上影响大吗？ oracle默认就是RC隔离级别

* 不解决幻读的案例

```sql
    dba:lc_4> set tx_isolation='read-committed';
    Query OK, 0 rows affected (0.00 sec)
    
    dba:lc_4> select * from lc for update ;
    +----+
    | id |
    +----+
    |  1 |
    |  2 |
    | 3 |
    +----+
    3 rows in set (0.00 sec)
```

* 查看锁的信息如下  

```
    
    ------------
    TRANSACTIONS
    ------------
    Trx id counter 133588362
    Purge done for trx's n:o < 133588356 undo n:o < 0 state: running but idle
    History list length 892
    LIST OF TRANSACTIONS FOR EACH SESSION:
    ---TRANSACTION 421565826150000, not started
    0 lock struct(s), heap size 1136, 0 row lock(s)
    ---TRANSACTION 421565826149088, not started
    0 lock struct(s), heap size 1136, 0 row lock(s)
    ---TRANSACTION 133588361, ACTIVE 3 sec
    2 lock struct(s), heap size 1136, 3 row lock(s)
    MySQL thread id 138, OS thread handle 140001238955776, query id 1192 localhost dba cleaning up
    TABLE LOCK table `lc_4`.`lc` trx id 133588361 lock mode IX
    RECORD LOCKS space id 289 page no 3 n bits 72 index PRIMARY of table `lc_4`.`lc` trx id 133588361 lock_mode X locks rec but not gap --记录锁，只锁记录
    Record lock, heap no 2 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000001; asc ;; -- 记录锁，锁住1
     1: len 6; hex 000007f6657e; asc e~;;
     2: len 7; hex e5000040220110; asc @" ;;
    
    Record lock, heap no 3 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000002; asc     ;;  -- 记录锁，锁住2
     1: len 6; hex 000007f6657f; asc     e ;;
     2: len 7; hex e6000040330110; asc    @3  ;;
    
    Record lock, heap no 4 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000003; asc     ;; -- 记录锁，锁住3
     1: len 6; hex 000007f66584; asc     e ;;
     2: len 7; hex e9000040240110; asc    @$  ;;
```
    
* 总结下来   
    1. 锁住的是哪些？  [1,2,3] 这些记录被锁住  
    2. 那么session 2 除了1，2，3 不能插入之外，其他的记录都能，比如； `insert into lc select 4` , 那么再次`select * from lc for udpate` 的时候，就是4条记录了，由此产生幻读
    

## 2.3 RC vs RR 安全性

* RC 和 binlog
    * RC 模式，**`binlog 必须使用Row 模式`**
    
* 为什么RC的binlog必须使用Row

```
    * session 1:
    
    begin;
    delete fromtb_1whereid >0;
    
    * session 2:
    
    begin;
    insert intotb_1select 100;
    commit;
    
    * session 1:
    
    commit;
    
    * 如果RC模式下的binlog是statement模式，结果会是怎么样呢？
    
    master :  结果是 100
    slave  :  结果是 空
    这样就导致master和slave结果不一致了: 因为在slave上，先执行insert intotb_1select 100; 再执行delete fromtb_1whereid >0; 当然等于空咯    
    
    * 如果RC模式下的binlog是ROW模式，结果会是怎么样呢？
    master :  结果是 100
    slave :  结果是 100
    主从结果一致，因为binlog是row模式，slave并不是逻辑的执行上述sql，而记录的都是行的变化
```

## 2.4 总结

* RC 的优点
    * 由于降低了隔离级别，那么实现起来简单，对锁的开销小，基本上不会有Gap lock，那么导致死锁和锁等待的可能就小
    * 当然RC也不是完全没有Gap lock，当purge 和 唯一性索引存在的时候会产生`特殊的Gap lock`，这个后面会具体讲
    
* RC 的缺点
    * 会有幻读发生  
    * 事务内的每条select，都会产生新的read-view，造成资源浪费
    
* RR 的优点
    * 一个事务，只有再开始的时候才会产生read-view，有且只有一个，所以这块消耗比较小  
    * 解决了幻读的问题, 实现了真正意义上的隔离级别
    
* RR 的缺点
    * 由于RR的实现，是通过`Gap-lock`实现，经常会锁定一个范围，那么导致死锁和所等待的概率非常大
    
* 我们的选择

    一般我们生产环境的标配，都是**`RC+Row`** 模式，谁用谁知道哦


[1]: http://keithlan.github.io/2017/06/14/innodb_locks_mvcc_isolation/

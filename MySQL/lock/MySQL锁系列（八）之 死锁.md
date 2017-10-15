# MySQL锁系列（八）之 死锁

 时间 2017-08-17 08:39:33  Focus on MySQL

原文[http://keithlan.github.io/2017/08/17/innodb_locks_deadlock/][1]

## 能学到什么

1. 什么是死锁
1. 死锁有什么危害
1. 典型的死锁案例剖析
1. 如何避免死锁

## 一、什么是死锁

* 1.必须满足的条件  
    1. 必须有两个或者两个以上的事务  
    2. 不同事务之间都持有对方需要的锁资源。 A事务需要B的资源，B事务需要A的资源，这就是典型的AB-BA死锁
    
* 2.死锁相关的参数

    * `innodb_print_all_deadlocks`
    
        1. 如果这个参数打开，那么死锁相关的信息都会打印输出到error log
    
    * `innodb_lock_wait_timeout`
    
        1. 当MySQL获取row lock的时候，如果wait了`innodb_lock_wait_timeout`=N的时间，会报以下错误
    
        ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    * `innodb_deadlock_detect`
    
        1. `innodb_deadlock_detect` = off  可以关闭掉死锁检测，那么就发生死锁的时候，用锁超时来处理。
        2. `innodb_deadlock_detect` = on  （默认选项）开启死锁检测，数据库自动回滚
    
    * `innodb_status_lock_output = on`
    
        1. 可以看到更加详细的锁信息
    

## 二、死锁有什么危害

1. 死锁，即表明有多个事务之间需要互相争夺资源而互相等待。
1. 如果没有死锁检测，那么就会互相卡死，一直hang死
1. 如果有死锁检测机制，那么数据库会自动根据代价来评估出哪些事务可以被回滚掉，用来打破这个僵局
1. 所以说：死锁并没有啥坏处，反而可以保护数据库和应用
1. 那么出现死锁，而且非常频繁，我们应该调整业务逻辑，让其避免产生死锁方为上策

## 三、典型的死锁案例剖析

### 3.1 死锁案例一

典型的 AB-BA 死锁

    session 1:
        select * from tb_b where id_2 = 1 for update (A)
    
    session 2:
        select * from tb_a where id = 2 for update (B)
    
    session 1:
        select * from tb_a where id = 2 for update (B)
    
    session 2:
        select * from tb_b where id_2 = 1 for update (A)
        ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction
    
    1213的死锁错误，mysql会自动回滚
    哪个回滚代价最小，回滚哪个（根据undo判断）
    
    
    
    ------------------------
    LATEST DETECTED DEADLOCK
    ------------------------
    2017-06-22 16:39:50 0x7f547dd02700
    *** (1) TRANSACTION:
    TRANSACTION 133601982, ACTIVE 48 sec starting index read
    mysql tables in use 1, locked 1
    LOCK WAIT 4 lock struct(s), heap size 1136, 2 row lock(s)
    MySQL thread id 11900, OS thread handle 140000866637568, query id 25108 localhost dba statistics
    select * from tb_a where id = 2 for update    -----session1 持有tb_a中记录为2的锁
    *** (1) WAITING FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 303 page no 3 n bits 72 index PRIMARY of table `lc_5`.`tb_a` trx id 133601982 lock_mode X locks rec but not gap waiting
    Record lock, heap no 3 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000002; asc     ;;   --session 1 需要tb_a中记录为2的锁( session1 -> session2 )
     1: len 6; hex 000007f69ab2; asc       ;;
     2: len 7; hex dc000027100110; asc    '   ;;
    
    *** (2) TRANSACTION:
    TRANSACTION 133601983, ACTIVE 28 sec starting index read, thread declared inside InnoDB 5000
    mysql tables in use 1, locked 1
    4 lock struct(s), heap size 1136, 2 row lock(s)
    MySQL thread id 11901, OS thread handle 140000864773888, query id 25109 localhost dba statistics
    select * from tb_b where id_2 = 1 for update
    *** (2) HOLDS THE LOCK(S):
    RECORD LOCKS space id 303 page no 3 n bits 72 index PRIMARY of table `lc_5`.`tb_a` trx id 133601983 lock_mode X locks rec but not gap
    Record lock, heap no 3 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000002; asc     ;;              --session 2 持有tb_a中记录等于2的锁
     1: len 6; hex 000007f69ab2; asc       ;;
     2: len 7; hex dc000027100110; asc    '   ;;
    
    *** (2) WAITING FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 304 page no 3 n bits 72 index PRIMARY of table `lc_5`.`tb_b` trx id 133601983 lock_mode X locks rec but not gap waiting
    Record lock, heap no 2 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000001; asc     ;;             --session 2 需要tb_b中记录为1的锁 ( session2 -> session1 )
     1: len 6; hex 000007f69ab8; asc       ;;
     2: len 7; hex e0000027120110; asc    '   ;;
    
    最终的结果：
        死锁路径：[session1 -> session2 , session2 -> session1]
        ABBA死锁产生
    

### 3.2 死锁案例二

同一个事务中，S-lock 升级为 X-lock 不能直接继承

    * session 1:
    
    mysql> CREATE TABLE t (i INT) ENGINE = InnoDB;
    Query OK, 0 rows affected (1.07 sec)
    
    mysql> INSERT INTO t (i) VALUES(1);
    Query OK, 1 row affected (0.09 sec)
    
    mysql> START TRANSACTION;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE i = 1 LOCK IN SHARE MODE; --获取S-lock
    +------+
    | i |
    +------+
    | 1 |
    +------+
    
    * session 2:
    
    mysql> START TRANSACTION;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> DELETE FROM t WHERE i = 1;   --想要获取X-lock，但是被session1的S-lock 卡住，目前处于waiting lock阶段
    
    
    
    * session 1:
    
    mysql> DELETE FROM t WHERE i = 1;   --想要获取X-lock，session1本身拥有S-lock，但是由于session 2 获取X-lock再前，所以session1不能够从S-lock 提升到 X-lock，需要等待session2 释放才可以获取，所以造成死锁
    ERROR 1213 (40001): Deadlock found when trying to get lock;
    try restarting transaction
    
    
    死锁路径：
     session2 -> session1 , session1 -> session2
    

### 3.3 死锁案例三

唯一键死锁 （`delete + insert`）

关键点在于：S-lock

    dba:lc_3> show create table uk;
    +-------+--------------------------------------------------------------------------------------------------------------+
    | Table | Create Table |
    +-------+--------------------------------------------------------------------------------------------------------------+
    | uk    | CREATE TABLE `uk` (
     `a` int(11) NOT NULL,
     UNIQUE KEY `uniq_a` (`a`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +-------+--------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    
    
    dba:lc_3> select * from uk;
    +---+
    | a |
    +---+
    | 1 |
    +---+
    1 row in set (0.00 sec)
    
    
    session 1:
    
    dba:lc_3> begin;
    Query OK, 0 rows affected (0.00 sec)
    
    dba:lc_3> delete from uk where a=1;
    Query OK, 1 row affected (0.00 sec)
    
    session 2:
    
    dba:(none)> use lc_3;
    Database changed
    dba:lc_3> insert into uk values(1);  --wait lock(想要加S-lock，却被sesson1的X-lock卡住)
    
    
    sesson 3:
    
    dba:(none)> use lc_3;
    Database changed
    dba:lc_3> insert into uk values(1); --wait lock（想要加S-lock，却被sesson1的X-lock卡住）
    
    
    session 1:
    
    commit;    --session2和session3 都获得了S-lock，然后都想要去给记录1 加上X-lock，却互相被对方的S-lock卡住，死锁产生
    
    
    再来看session 2 和 session 3 的结果：
    
    session2：
    Query OK, 1 row affected (7.36 sec)
    
    session3：
    ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction
    
    
    总结： 试想想，如果session 1 不是commit，而是rollback会是怎么样呢？ 大家去测测就会发现，结果肯定是唯一键冲突啊
    

### 3.4 死锁案例四

主键和二级索引的死锁

    * primary key
    
    1   2   3   4   --primary key col1
    
    10  30  20  40  --idx_key2 col2
    
    100 200 300 400  --idx_key3 col3
    
    
    * idx_key2      select*fromtwherecol2 >10: 锁二级索引顺序为：20=》30， 对应锁主键的顺序为：3=》2
    
    10 20 30 40
    
    1  3  2  4
    
    
    * idx_key3    select * from t where col3 > 100：锁二级索引顺序为：200 =》300 ， 对应锁主键的顺序为：2 =》3
    
    100 200 300 400
    
    1   2   3   4
    
    
    死锁路径：
        由于二级索引引起的主键加锁顺序： 3 =》2
        由于二级索引引起的主键加锁顺序： 2 =》3
    
    这个要求并发，且刚好
    
    session 1 加锁3的时候 session 2 要加锁2.
    session 1 加锁2的时候 session 3 要加锁3.
    
    这样就产生了 AB-BA 死锁
    

## 3.5 死锁案例五

`purge + unique key` 引发的死锁

    A表的记录： id =  1    10   40   100    200   500  800  900
    
    session 1 :
        delete fromawhereid =10;   ???
    
    session 2 :
        delete fromawhereid =800;  ???
    
    session 1 :
        insert intoaselect 800; ???
    
    session 2 :
        insert intoaselect 10; ???
    
    * 如果大家去跑这两钟SQL语句的并发测试，是可以导致死锁的。
    
    * 如何验证是由于purge导致的问题呢？这个本想用mysqld-debug模式去关闭purge线程，但是很遗憾我没能模拟出来。。。
    

## 3.6 死锁案例六

`REPLACE INTO`问题

    * 这个问题模拟起来非常简单，原理非常复杂，这里不过多解释
        * 详情请看姜老师的文章,据说看懂了年薪都100w了：  http://www.innomysql.com/26186-2/
    
    * 解决方案：
        * 用insert into ... on duplicate key update 代替 replace into
        * 此方案亲测有效
    

## 四、如何避免死锁

* 产生死锁的原因
    * 事务之间互相占用资源
    
* 方法和总结
    * 降低隔离级别，修改 `RR -> RC` , 如果这个调整了，可以避免掉60%的死锁场景和奇怪的锁等待   
    * 调整业务逻辑和SQL，让其都按照顺序执行操作    
    * 减少unique索引，大部分死锁的场景都是由于unique索引导致    
    * 尽量不用`replace into`，用`insert into ... on duplicate key update` 代替


[1]: http://keithlan.github.io/2017/08/17/innodb_locks_deadlock/

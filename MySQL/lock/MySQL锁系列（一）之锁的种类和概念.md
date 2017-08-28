# MySQL锁系列（一）之锁的种类和概念

 时间 2017-06-05 20:25:05  [ocus on MySQL

原文[http://keithlan.github.io/2017/06/05/innodb_locks_1/][1]



## 背景

锁是MySQL里面最难理解的知识，但是又无处不在。

一开始接触锁的时候，感觉被各种锁类型和名词弄得晕头转向，就别说其他了。

本文是通过DBA的视角（非InnoDB内核开发）来分析和窥探锁的奥秘，并解决实际工作当中遇到的问题

## 锁的种类&概念

想要啃掉这块最难的大骨头，必须先画一个框架，先了解其全貌，才能逐个击破

* Shared and Exclusive Locks


    * Shared lock: 共享锁,官方描述：permits thetransactionthat holds thelock to readarow
    
    eg：select * from xx where a=1 lock in share mode
    
    * Exclusive Locks：排他锁： permits the transaction that holds the lock to update or delete a row
    
    eg: select * from xx where a=1 for update
    

* Intention Locks


    1. 这个锁是加在table上的，表示要对下一个层级（记录）进行加锁  
    2. Intention shared (IS）：Transaction T intends to setS locksonindividualrows in tablet
    3. Intention exclusive (IX):  Transaction T intends to set X locks on those rows  
    4. 在数据库层看到的结果是这样的：
        TABLE LOCK table `lc_3`.`a` trx id 133588125 lock mode IX
    

* Record Locks


    1. 在数据库层看到的结果是这样的：
        RECORD LOCKS space id 281 page no 3 n bits 72 index PRIMARY of table `lc_3`.`a`trx id 133588125 lock_mode X locks rec but not gap
    
    2. 该锁是加在索引上的（从上面的index PRIMARY of table `lc_3`.`a`就能看出来）
    
    3. 记录锁可以有两种类型：lock_mode X locks rec but not gap  && lock_mode S locks rec but not gap
    

* Gap Locks


    1. 在数据库层看到的结果是这样的：
        RECORD LOCKS space id 281 page no 5 n bits 72 index idx_c of table `lc_3`.`a` trx id 133588125 lock_mode X locks gap before rec  
    
    2. Gap锁是用来防止insert的  
    
    3. Gap锁，中文名间隙锁，锁住的不是记录，而是范围,比如：(negative infinity, 10），(10, 11）区间，这里都是开区间哦
    

* Next-Key Locks


    1. 在数据库层看到的结果是这样的：
        RECORD LOCKS space id 281 page no 5 n bits 72 index idx_c of table `lc_3`.`a` trx id 133588125 lock_mode X
    
    2. Next-Key Locks = Gap Locks + Record Locks 的结合, 不仅仅锁住记录，还会锁住间隙，比如： (negative infinity, 10】，(10, 11】区间，这些右边都是闭区间哦
    

* Insert Intention Locks


    1. 在数据库层看到的结果是这样的：
        RECORD LOCKS space id 279 page no 3 n bits 72 index PRIMARY of table `lc_3`.`t1` trx id 133587907 lock_mode X insert intention waiting
    
    2. Insert Intention Locks 可以理解为特殊的Gap锁的一种，用以提升并发写入的性能
    

* AUTO-INC Locks


    1. 在数据库层看到的结果是这样的：
        TABLE LOCK tablexx trx id7498948lock modeAUTO-INC waiting
    
    2. 属于表级别的锁  
    
    3. 自增锁的详细情况可以之前的一篇文章:
        http://keithlan.github.io/2017/03/03/auto_increment_lock/
    

* 显示锁 vs 隐示锁


    * 显示锁(explicit lock)
        显示的加锁，在show engine innoDB status 中能够看到  ，会在内存中产生对象，占用内存  
        eg: select ... for update , select ... lock in share mode   
    
    * 隐示锁(implicit lock)
        implicit lock 是在索引中对记录逻辑的加锁，但是实际上不产生锁对象，不占用内存空间  
        
    * 哪些语句会产生implicit lock 呢？
       eg: insert into xx values(xx) 
       eg: update xx set t=t+1 where id = 1 ; 会对辅助索引加implicit lock  
    
    * implicit lock 在什么情况下会转换成 explicit lock
      eg： 只有implicit lock 产生冲突的时候，会自动转换成explicit lock,这样做的好处就是降低锁的开销    
      eg: 比如：我插入了一条记录10，本身这个记录加上implicit lock，如果这时候有人再去更新这条10的记录，那么就会自动转换成explicit lock
    
    * 数据库怎么知道implicit lock的存在呢？如何实现锁的转化呢？
      1. 对于聚集索引上面的记录，有db_trx_id,如果该事务id在活跃事务列表中，那么说明还没有提交，那么implicit则存在  
      2. 对于非聚集索引：由于上面没有事务id，那么可以通过上面的主键id，再通过主键id上面的事务id来判断，不过算法要非常复杂，这里不做介绍
    

* metadata lock


    1. 这是Server 层实现的锁，跟引擎层无关  
    2. 当你执行select的时候，如果这时候有ddl语句，那么ddl会被阻塞，因为select语句拥有metadata lock，防止元数据被改掉
    

* 锁迁移


    1. 锁迁移，又名锁继承  
    2. 什么是锁迁移呢？
     a) 满足的场景条件：
     b）我锁住的记录是一条已经被标记为删除的记录，但是还没有被puge 
     c) 然后这条被标记为删除的记录，被purge掉了 
     d) 那么上面的锁自然而然就继承给了下一条记录，我们称之为锁迁移
    

* 锁升级


    锁升级指的是：一条全表更新的语句，那么数据库就会对所有记录进行加锁，那么可能造成锁开销非常大，可能升级为页锁，或者表锁。
    MySQL 没有锁升级
    

* 锁分裂


    1. InnoDB的实现加锁，其实是在页上面做的，没有办法直接对记录加锁  
    2. 一个页被读取到内存，然后会产生锁对象，锁对象里面会有位图信息来表示哪些heapno被锁住，heapno表示的就是堆的序列号，可以认为就是定位到某一条记录  
    3. 大家又知道，由于B+tree的存在，当insert的时候，会产生页的分裂动作  
    4. 如果页分裂了，那么原来对页上面的加锁位图信息也就变了，为了保持这种变化和锁信息，锁对象也会分裂，由于继续维护分裂后页的锁信息
    

* 锁合并


    锁的合并，和锁的分裂，其实原理是一样的，参考上面即可。  
    
    至于锁合并和锁分裂的算法，比较复杂，这里就不介绍了
    

* latch vs lock


    * latch
      mutex
      rw-lock
      临界资源用完释放
      不支持死锁检测
      以上是应用程序中的锁，不是数据库的锁
    
    * lock
      当事务结束后，释放
      支持死锁检测
      数据库中的锁
    

## 锁的兼容矩阵

* X vs S

兼容性 | X | S
-|-|
 X | N | N 
 S | N | Y 

* IS,IX,S,X

兼容性 | IS | IX | S | X 
-|-|-|-|-
IS | Y | Y | Y | N
 IX | Y | Y | N | N 
 S | Y | N | Y | N 
 X | N | N | N | N 

* AI,IS,IX,S,X

兼容性 | AI | IS | IX | S | X 
-|-|-|-|-|-
AI | N | Y | Y  | N  | N 
IS | Y | Y | Y  | Y  | N 
IX | Y | Y | Y  | N  | N 
S | N | Y | N  | Y  | N 
X | N | N | N  | N  | N 

## 参考资料

1. https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html
2. MySQL技术内幕：InnoDB 存储引擎
3. MySQL内核：InnoDB 存储引擎


[1]: http://keithlan.github.io/2017/06/05/innodb_locks_1/

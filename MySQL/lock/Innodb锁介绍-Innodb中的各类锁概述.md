## Innodb锁介绍-Innodb中的各类锁概述

来源：[http://sadwxqezc.github.io/HuangHuanBlog/mysql/2017/05/07/Innodb锁.html](http://sadwxqezc.github.io/HuangHuanBlog/mysql/2017/05/07/Innodb锁.html)

时间 2017-05-07 19:25:18

 
## Innodb锁概述
 
#### 参考文档:
 
 
* [美团Innodb锁介绍][2]  
* [MySQL insert锁机制][3]  
* [MySQL锁官方文档][4]  
 
 
## 概述
 
Innodb中行级锁作用于索引之上，如果没有索引，则只能够锁表。
 
### 一次封锁法
 
为了预防死锁，一般应用中推荐一次封锁法。也就是在方法的开始阶段，已经预先知道会用到哪些数据，然后全部锁住，在方法运行完成之后，再进行解锁。
 
一次封锁法能够预防死锁，但从该方法的定义中可以看到，每次操作都锁住全部数据，如果这样数据的执行只能是串行化的，性能不高。
 
### 两阶段锁协议
 
数据库遵循的是两段锁协议，将事物分解成加锁和解锁两个阶段
 
#### 加锁阶段
 
该阶段可以进行加锁操作，在对任何数据进行读操作之前要申请并获得S锁(Shared Lock，其它事务可以继续加S锁，但不能加Exclusive Lock，即排他锁)；而在进行写操作之前，需要申请X锁(Exclusive Lock，其它事务不能再获得任何锁)。加锁不成功则进入等待状态，而不能再加其它锁。
 
从这个定义可以看出，加锁阶段定义了事务之间的协调规则，能够有效提高多个事务之间的执行性能，但同时也带来了死锁的风险，之后会举例介绍死锁的成因。
 
#### 解锁阶段
 
事务进入解锁阶段将释放其持有的锁，该阶段只能进行解锁操作，而不能再加其它锁。
 
## Innodb中的各种锁
 
### Shared Lock And Exclusive Locks
 
这是两个行级锁，包括 **`Shared Lock(S 共享锁)`**  和 **`Exclusive Lock(X 排他锁):`** 
 
 
* **`共享锁`**  允许持有锁的事务去读取一行数据，可以有多个事务同时持有共享锁，但当数据被加上共享锁时，不能再被加排他锁。  
* **`排他锁`**  允许持有锁的事务去更新或则删除一行数据，同时只能有一个事务持有排他锁，当数据被加上排他锁时，不能再加共享锁。  
 
 
### Record Locks
 
记录锁是作用在索引上，比如这么一条语句：
 
  
```sql
SELECT c1 FROM t WHERE c1=10 FOR UPDATE
```
 
这条语句将会在`c1`值为10这条记录的索引加锁，阻止其它事务的插入，更新和删除操作。 即使`c1`不存在索引，Innodb也会创建一个隐藏的`clustered index`，并用其作为锁的依据。
 
### Next-key Locks
 
Next-key锁是记录锁和Gap锁的结合，锁住了记录和记录之前的一段Gap区间。 比如索引包含了10，11，13和20，那么Next-key分出的区间如下：
 
  
```
(negative infinity, 10]
(10, 11]
(11, 13]
(13, 20]
(20, positive infinity)
```
 
### Intention Locks
 
Intention Locks(意向锁)是MySQL为了支持不同粒度的锁而设计的一种 **`表级别锁(但不是通常认为的表锁)`**  ，它表示了表之后将被加上哪种行级锁。意向锁的分类如下：
 
 
* **`Intention Shared Lock，意向共享锁(IS)`**  ，表示事务将要在表上加共享锁，规则是在表中申请某些行的共享锁之前，必须先申请`IS`锁。  
* **`Intention Exclusive Lock，意向排他锁(IX)`**  ，表示事务将要在标上加排他锁，规则是在表中申请某些行的排他锁之前，必须先申请`IX`锁。  
 
  
```sql
SELECT ... LOCK IN SHARE MODE
```
 
该语句将会在表上加`IS`锁，同时在对应的记录上加上`S`锁。
  
```sql
SELECT ... FOR UPDATE
```
该语句将会在标上加上`IX`锁，同时在对应的记录上加上`X`锁。
 
#### 表级锁的兼容性矩阵：
 
![][0]
 
事实上意向锁不会和行级的`S`和`X`锁产生冲突，只会和表级的`S`和`X`锁产生冲突。
 
### GAP Locks
 
Gap锁是一种范围锁，Gap锁作用范围是Record锁之间，或者Record锁之前与Record锁之后的范围。
 
![][1]
 
如图所示，首先当前该记录存在索引，id为5和30的记录将整个分为了`<=5`，`>5&<=30`和`>30`三个区间，如果要更新30的数据，那么`>5`的所有区间都会被锁住。
 
### Insert Intention Locks
 
Insert Intention Locks也就是插入意向锁，但它其实是一种GAP锁，在行数据被插入之前，设定的一种锁，如果两个事务要插入同一个GAP中的不同行记录，它们都会获取这个GAP的插入意向锁，但相互之间不会冲突。
 
### AUTO-INC Locks
 
AUTO-INC锁是一种特殊的表级别锁，主要处理表中带有自增列的情况。实际上是为了保证自增的正确性，所以有了这种锁。


[2]: http://tech.meituan.com/innodb-lock.html
[3]: http://yeshaoting.cn/article/database/mysql%20insert%E9%94%81%E6%9C%BA%E5%88%B6/
[4]: https://dev.mysql.com/doc/refman/5.6/en/innodb-locking.html
[0]: ./img/FF3yeqj.png
[1]: ./img/VRZnqiE.png
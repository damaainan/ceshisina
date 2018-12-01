## 很少有人说清楚的MySQL如何用REPEATABLE-READ解决幻读问题

来源：[https://elsef.com/2018/11/28/经常被误解的MySQL中对REPEATABLE-READ/](https://elsef.com/2018/11/28/经常被误解的MySQL中对REPEATABLE-READ/)

时间 2018-11-28 23:04:15



## MySQL解决幻读


## 啥是幻读

```
The so-called phantom problem occurs within a transaction when the same query produces different sets of rows at different times. For example, if a SELECT is executed twice, but returns a row the second time that was not returned the first time, the row is a “phantom” row.
```


## MySQL的隔离级别

MySQL的InnoDb存储引擎默认的隔离级别是REPEATABLE-READ
，即可重复读。那什么是可重复读呢，简单来说就是一个事务里的两个相同条件的查询查到的结果应该是一致的，即结果是「可以重复读到的」，所以就解决了「幻读」。
如果做不到可重复读，如READ-COMMITTED
隔离级别，由于一个事务执行过程中可能读到其他事务已经提交的数据，那么按照上面的描述「一个事务里的两个相同条件的查询查到的结果应该是一致的」这个就无法达到了，因为结果集合可以新增，跟之前读的结果不一样多，就幻觉读了。


## 如何解决

OK，听起来很简单，一个隔离级别就可以搞定了，但是内部的机制和原理并不简单，并且有些概念的作用可能大家并不知道具体解决了什么问题。

首先还是了解一下InnoDb的锁机制，InnoDB有三种行锁的算法：


* Record Lock：单个行记录上的锁
* Gap Lock：间隙锁，锁定一个范围，但不包括记录本身。GAP锁的目的，是为了防止同一事务的两次当前读，出现幻读的情况
* Next-Key Lock：前两个锁的加和，锁定一个范围，并且锁定记录本身。对于行的查询，都是采用该方法，主要目的是解决幻读的问题
  

关于「幻读」，有一个点需要注意，它只跟读有关系：


* MVCC(Multi-Version Concurrency Control多版本并发控制)
如果是简单的SELECT * FROM table1 WHERE 
这种语句为什么读不到隔壁事务的提交数据的原因是，InnoDb使用了MVCC机制，为了提高并发，提供了这个非锁定读，即不需要等待访问行上的锁释放，读取行的一个快照即可。
但是，它也不会阻止隔壁事务去插入新的数据，因为它并未有加锁操作，但当前事务读不到而已（其实想读也可以读到，请看后部分）。

    
* Next-Key Lock
如果是带排他锁操作（除了INSERT/UPDATE/DELETE这种，还包括SELECT FOR UPDATE等），它们默认都在操作的记录上加了Next-Key 
Lock。只有使用了这里的操作后才会在相应的记录周围和记录本上加锁，即Record Lock+ Gap Lock，所以会导致冲突的事务阻塞或超时失败。
PS.想说，隔离级别越高并发度越差，性能越差，虽然默认的是RR，但是如果业务不需要严格的没有「幻读」现象，是可以降低为RC的或修改innodb_locks_unsafe_for_binlog为1。
注意有的时候会进行优化，并退化为只加Record Lock，不加Gap Lock，如相关字段为主键的时候。



## REPEATABLE-READ的误解


## 误解一

REPEATABLE-READ肯定不会读到隔壁事务已经提交的数据，即使某个数据已经由隔壁事务提交，当前事务插入不会报错，否则就是发生了幻读。

简单来说前半句话是对的，后半句有什么问题呢？可REPEATABLE-READ
其实跟「写操作」无关，当前事务读不到的数据并不一定是不存在的，如果存在，那么当前事务尝试插入的时候是可能会失败的。
而插入失败的原因可能是因为主键冲突导致数据库报异常，跟隔离级别无直接关系。任何隔离级别下插入已经存在的数据都会报错。
看不到并不代表没有，并不代表可以自以为然的插入无忧。


## 误解二

REPEATABLE-READ的事务里查不到的数据一定是不存在的，所以我可以放心插入，100%成功。

这个观点也是错的，查不到只能说明当前事务里读不到，并不代表此时其他事务没有插入这样的数据。
如何保证判断某个数据不存在以后其他事务也不会插入成功？答案是上锁。不上锁是无法阻止其他事务插入的。

```sql
SELECT * FROM table1 WHERE id >100
```

上面这个语句在事务里判断后如果不存在数据是无法保证其他事务插入符合条件的数据的，需要加锁

```sql
SELECT * FROM table1 WHERE id >100
 FOR UPDATE;

```

此时如果有隔壁事务尝试插入大于100的id的数据则会等待当前事务释放锁，直到超时后中断当前事务。

```
(waiting for lock … then timeout) ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

但是如果当前事务使用的加锁的条件仅仅是某一个行锁的话最多会在前后加next-key locking，影响范围较小，但仍然可能阻塞其他事务的插入，如恰好新数据的位置被gap 
locking锁住了，那只能等待当前事务释放锁了。

说了这么多，有一点要注意，就是这个next-key locking一定是在REPEATABLE-READ下才有，READ-COMMITTED是不存在的。

```
To prevent phantoms, InnoDB uses an algorithm called next-key locking that combines index-row locking with gap locking. You can use next-key locking to implement a uniqueness check in your application: If you read your data in share mode and do not see a duplicate for a row you are going to insert, then you can safely insert your row and know that the next-key lock set on the successor of your row during the read prevents anyone meanwhile inserting a duplicate for your row. Thus, the next-key locking enables you to “lock” the nonexistence of something in your table.
```

即InnoDb提供next-key locking机制，但是需要业务自己去加锁，如果不加锁，只是简单的select查询，是无法限制并行的插入的。


## 误解三

凡是REPEATABLE-READ中的读都无法读取最新的数据。

这个观点也是错误的，虽然我们读取的记录都是可重复读取的，但是如果你想读取最新的记录可以用加锁的方式读。

```
If you want to see the “freshest” state of the database, you should use either the READ COMMITTED isolation level or a locking read:
```

以下任意一种均：


* SELECT * FROM table1 LOCK IN SHARE MODE;
* SELECT * FROM table1 FOR UPDATE;
  

但这里要说明的是这样做跟SERIALIZABLE没有什么区别，即读也加了锁，性能大打折扣。


## 参考


* https://dev.mysql.com/doc/refman/8.0/en/innodb-next-key-locking.html
* https://dev.mysql.com/doc/refman/8.0/en/innodb-consistent-read.html
  

本文首次发布于ElseF’s Blog, 作者[@stuartlau][0],转载请保留原文链接.


* [Previous新TLD的消亡][1]    
  


[0]: http://github.com/stuartlau
[1]: https://elsef.com/2018/10/14/%E6%96%B0TLD%E7%9A%84%E6%B6%88%E4%BA%A1/
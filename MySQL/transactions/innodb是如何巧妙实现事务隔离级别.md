## innodb是如何巧妙实现事务隔离级别

来源：[https://www.cnblogs.com/volcano-liu/p/9893317.html](https://www.cnblogs.com/volcano-liu/p/9893317.html)

2018-11-11 22:06

　　之前的文章[mysql锁机制详解][100]中我们详细讲解了innodb的锁机制，锁机制是用来保证在并发情况下数据的准确性，而要保证数据准确通常需要事务的支持，而mysql存储引擎innodb是通过锁机制来巧妙地实现事务的隔离特性中的4种隔离级别。

　　事务ACID特性，其中I代表隔离性(Isolation)。隔离性是指，多个用户的并发事务访问同一个数据库时，一个用户的事务不应该被其他用户的事务干扰，多个并发事务之间要相互隔离。
## 1. 事务之间如何互相干扰

　　一个事务是如何干扰其他事务呢？举个例子，有如下表：

```sql
create table lock_example(id smallint(10),name varchar(20),primary key id)engine=innodb;
```


　　表中有如下数据：

```sql
1, zhangsan
2, lisi
3, wangwu
```

### demo1:

　　事务A，先执行，处于未提交的状态：

```sql
insert into t values(4, 'zhaoliu');
```


　　事务B，后执行，也未提交：

```sql
select * from t;
```


　　如果事务B能够读取到(4, zhaoliu)这条记录，说明事务A就对事务B产生了影响，这种影响叫做“读脏 ”，即读到了未提交事务操作的记录。
### demo2:

　　事务A，先执行：

```sql
select * from t where id=1;结果集为1,zhangsan
```


　　事务B，后执行，并且提交：

```sql
update t set name=xxx where id=1;
commit;
```


　　事务A，再次执行相同的查询：

```sql
select * from t where id=1;

结果集为：

1, xxx
```


　　这次是已提交事务B对事务A产生的影响，这种影响叫做“不可重复读 ”，即一个事务内相同的查询，却得到了不同的结果。
### demo3:

　　事务A，先执行：

```sql
select * from t where id>3;
```

　结果集为：

　NULL


　　事务B，后执行，并且提交：

```sql
insert into t values(4, zhaoliu);
commit;
```


　　事务A，首次查询了id>3的结果为NULL，于是想插入一条为4的记录：

```sql
insert into t values(4, xxoo);

结果集为：

Error : duplicate key!
```


　　你可能会想。。。你TM在逗我？查了id>3为空集，insert id=4时又告诉我PK冲突？→_→

　　这次是已提交事务B对事务A产生的影响，这种影响叫做“幻读 ”。

　　如上，并发的事务可能导致其他事务出现读脏 、不可重复读 、幻读 。为了避免如上情况出现，innodb又做了哪些努力呢？
## 2. InnoDB实现了哪几种事务的隔离级别?

　　InnoDB实现了四种不同事务的隔离级别：


* 读未提交(Read Uncommitted)
* 读提交(Read Committed, RC)
* 可重复读(Repeated Read, RR)
* 串行化(Serializable)


　　不同事务的隔离级别，实际上是一致性与并发性的一个权衡与折衷。

## 3. 四种事务的隔离级别，innodb如何实现?

　　InnoDB使用不同的锁策略(Locking Strategy)来实现不同的隔离级别。

### a. 读未提交(Read Uncommitted)

　　这种事务隔离级别下，select语句不加锁，也不是快照读。

SELECT statements are performed in a nonlocking fashion.

　　此时，可能读取到不一致的数据，即“读脏”。这是并发最高，一致性最差的隔离级别。

### b. 读提交(Read Committed, RC)


* 普通select是快照读；
* 加锁的select, update, delete等语句，除了在外键约束检查(foreign-key constraint checking)以及重复键检查(duplicate-key checking)时会封锁区间，其他时刻都只使用 **`记录锁`** ；
* 间隙锁(gap lock)、临建锁(next-key lock)在该级别下失效；


  此时，其他事务的插入依然可以执行，就可能导致，读取到幻影记录。该级别是最常使用的。而且如果是不上锁的select，可能产生不可重复读。

　　该级别下是通过快照读来防止读脏的。因为在该级别下的快照读总是能读到最新的行数据快照，当然，必须是已提交事务写入的，所以可能产生不可重复读。

### c. 可重复读(Repeated Read, RR)

　　这是InnoDB默认的隔离级别，在RR下：


* 普通的select使用快照读(snapshot read)，这是一种不加锁的一致性读(Consistent Nonlocking Read)，底层使用MVCC来实现；
* 加锁的select(select ... in share mode / select ... for update), update, delete等语句，它们的锁，依赖于它们是否在唯一索引(unique index)上使用了唯一的查询条件(unique search condition，此时使用记录锁)，或者范围查询条件(range-type search condition，此时使用间隙锁或临键锁)；
* 在唯一索引上使用唯一的查询条件，会使用记录锁(record lock)，而不会封锁记录之间的间隔，即不会使用间隙锁(gap lock)与临键锁(next-key lock)；
* 范围查询条件或者是非唯一索引，会使用间隙锁与临键锁，锁住索引记录之间的范围，避免范围间插入记录，以避免产生幻影行记录，以及避免不可重复读；


　　在该级别下

* 通过快照读以及锁定区间来实现避免产生幻读和不可重复读； 
* 某个事务首次read记录的时间为T，未来不会读取到T时间之后已提交事务写入的记录，以保证连续相同的read读到相同的结果集，这可以防止不可重复读； 
* RR下是通过间隙锁，临键锁来解决幻影读问题； 

### d. 串行化(Serializable)

　　这种事务的隔离级别下，所有select语句都会被隐式的转化为select ... in share mode，也就是默认上共享读锁(S锁)。

　　所以，如果事务A先执行如下sql之后，会尝试获取所查询行的IS锁(和别的IS、IX锁是兼容的)，这时别的事务也能获取这些行的IS锁甚至是S锁，但是如果接下来，事务A如果update或delete其中的某些行，这时就获取了X锁，别的事务即便是执行普通的select语句也会阻塞，因为它们尝试获取IS锁，但是IS锁和X锁是互斥的，这样就避免了读脏、不可重复读以及幻读，所有事务就只能串行了。

```sql
select ... ;
```

　　这是一致性最好的，但并发性最差的隔离级别。高并发量的场景下，几乎不会使用上述a和d这两种隔离级别。
## 4. 总结

  并发事务之间相互干扰，就可能导致事务出现读脏，不可重复读，幻读等问题。

  InnoDB实现了SQL92标准中的四种隔离级别：

* 读未提交：select不加锁，可能出现读脏；

* 读提交(RC)：普通select快照读，锁select /update /delete 会使用记录锁，可能出现不可重复读；

* 可重复读(RR)：普通select快照读，锁select /update /delete 根据查询条件等情况，会选择记录锁，或者间隙锁/临键锁，以防止读取到幻影记录；

* 串行化：select隐式转化为select ... in share mode，会被update与delete互斥；

  InnoDB默认的隔离级别是RR，用得最多的隔离级别是RC


[100]: https://www.cnblogs.com/volcano-liu/p/9890832.html
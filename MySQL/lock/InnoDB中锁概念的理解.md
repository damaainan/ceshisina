## MySQL/InnoDB中，乐观锁、悲观锁、共享锁、排它锁、行锁、表锁、死锁概念的理解

来源：[https://www.souyunku.com/2018/07/30/mysql/](https://www.souyunku.com/2018/07/30/mysql/)

时间 2018-07-30 10:06:59


MySQL/InnoDB的加锁，一直是一个面试中常问的话题。例如，数据库如果有高并发请求，如何保证数据完整性？产生死锁问题如何排查并解决？我在工作过程中，也会经常用到，乐观锁，排它锁，等。于是今天就对这几个概念进行学习，屡屡思路，记录一下。

注：MySQL是一个支持插件式存储引擎的数据库系统。本文下面的所有介绍，都是基于InnoDB存储引擎，其他引擎的表现，会有较大的区别。


#### 存储引擎查看

MySQL给开发者提供了查询存储引擎的功能，我这里使用的是MySQL5.6.4，可以使用：
`SHOW ENGINES`

## 乐观锁

用数据版本（Version）记录机制实现，这是乐观锁最常用的一种实现方式。何谓数据版本？即为数据增加一个版本标识，一般是通过为数据库表增加一个数字类型的 “version” 字段来实现。当读取数据时，将version字段的值一同读出，数据每更新一次，对此version值加1。当我们提交更新的时候，判断数据库表对应记录的当前版本信息与第一次取出来的version值进行比对，如果数据库表当前版本号与第一次取出来的version值相等，则予以更新，否则认为是过期数据。


#### 举例

1、数据库表设计

三个字段，分别是`id,value、version`

```sql
select id,value,version from TABLE where id=#{id}
```

2、每次更新表中的value字段时，为了防止发生冲突，需要这样操作

```sql
update TABLE
set value=2,version=version+1
where id=#{id} and version=#{version};
```


## 悲观锁

与乐观锁相对应的就是悲观锁了。悲观锁就是在操作数据时，认为此操作会出现数据冲突，所以在进行每次操作时都要通过获取锁才能进行对相同数据的操作，这点跟java中的synchronized很相似，所以悲观锁需要耗费较多的时间。另外与乐观锁相对应的，悲观锁是由数据库自己实现了的，要用的时候，我们直接调用数据库的相关语句就可以了。

说到这里，由悲观锁涉及到的另外两个锁概念就出来了，它们就是共享锁与排它锁。 **`共享锁和排它锁是悲观锁的不同的实现`** ，它俩都属于悲观锁的范畴。


#### 使用，排它锁 举例

要使用悲观锁，我们必须关闭mysql数据库的自动提交属性，因为MySQL默认使用autocommit模式，也就是说，当你执行一个更新操作后，MySQL会立刻将结果进行提交。

我们可以使用命令设置MySQL为非autocommit模式：

```sql
set autocommit=0;

# 设置完autocommit后，我们就可以执行我们的正常业务了。具体如下：

# 1. 开始事务

begin;/begin work;/start transaction; (三者选一就可以)

# 2. 查询表信息

select status from TABLE where id=1 for update;

# 3. 插入一条数据

insert into TABLE (id,value) values (2,2);

# 4. 修改数据为

update TABLE set value=2 where id=1;

# 5. 提交事务

commit;/commit work;
```


## 共享锁

共享锁又称 **`读锁 read lock`** ，是读取操作创建的锁。其他用户可以并发读取数据，但任何事务都不能对数据进行修改（获取数据上的排他锁），直到已释放所有共享锁。

如果事务T对数据A加上共享锁后，则其他事务只能对A再加共享锁，不能加排他锁。获得共享锁的事务只能读数据，不能修改数据

打开第一个查询窗口

```sql
begin;/begin work;/start transaction;  (三者选一就可以)

SELECT * from TABLE where id = 1  lock in share mode;
```

然后在另一个查询窗口中，对id为1的数据进行更新

```sql
update  TABLE set name="www.souyunku.com" where id =1;
```

此时，操作界面进入了卡顿状态，过了超时间，提示错误信息

如果在超时前，执行`commit`，此更新语句就会成功。

```
[SQL]update  test_one set name="www.souyunku.com" where id =1;
[Err] 1205 - Lock wait timeout exceeded; try restarting transaction
```

加上共享锁后，也提示错误信息

```sql
update  test_one set name="www.souyunku.com" where id =1 lock in share mode;
```

```
[SQL]update  test_one set name="www.souyunku.com" where id =1 lock in share mode;
[Err] 1064 - You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'lock in share mode' at line 1
```

在查询语句后面增加 **`
 `LOCK IN SHARE MODE``** ，Mysql会对查询结果中的每行都加共享锁，当没有其他线程对查询结果集中的任何一行使用排他锁时，可以成功申请共享锁，否则会被阻塞。其他线程也可以读取使用了共享锁的表，而且这些线程读取的是同一个版本的数据。

加上共享锁后，对于`update,insert,delete`语句会自动加排它锁。


## 排它锁

排他锁 exclusive lock（也叫writer lock）又称 **`写锁`** 。


#### 排它锁是悲观锁的一种实现，在上面悲观锁也介绍过。

若事务 1 对数据对象A加上X锁，事务 1 可以读A也可以修改A，其他事务不能再对A加任何锁，直到事物 1 释放A上的锁。这保证了其他事务在事物 1 释放A上的锁之前不能再读取和修改A。排它锁会阻塞所有的排它锁和共享锁

读取为什么要加读锁呢：防止数据在被读取的时候被别的线程加上写锁，

使用方式：在需要执行的语句后面加上`for update`就可以了


## 行锁

行锁又分 **`共享锁`** 和 **`排他锁`** ,由字面意思理解，就是给某一行加上锁，也就是一条记录加上锁。

注意：行级锁都是基于索引的，如果一条SQL语句用不到索引是不会使用行级锁的，会使用表级锁。


#### 共享锁：

名词解释：共享锁又叫做读锁，所有的事务只能对其进行读操作不能写操作，加上共享锁后在事务结束之前其他事务只能再加共享锁，除此之外其他任何类型的锁都不能再加了。

```sql
SELECT * from TABLE where id = "1"  lock in share mode;  结果集的数据都会加共享锁
```


#### 排他锁：

名词解释：若某个事物对某一行加上了排他锁，只能这个事务对其进行读写，在此事务结束之前，其他事务不能对其进行加任何锁，其他进程可以读取,不能进行写操作，需等待其释放。

```sql
select status from TABLE where id=1 for update;
```

可以参考之前演示的共享锁,排它锁语句

由于对于表中,id字段为主键，就也相当于索引。执行加锁时，会将id这个索引为1的记录加上锁，那么这个锁就是行锁。


## 表锁

如何加表锁

innodb 的行锁是在有索引的情况下,没有索引的表是锁定全表的.


#### Innodb中的行锁与表锁

前面提到过，在Innodb引擎中既支持行锁也支持表锁，那么什么时候会锁住整张表，什么时候或只锁住一行呢？
只有通过索引条件检索数据，InnoDB才使用行级锁，否则，InnoDB将使用表锁！

在实际应用中，要特别注意InnoDB行锁的这一特性，不然的话，可能导致大量的锁冲突，从而影响并发性能。

行级锁都是基于索引的，如果一条SQL语句用不到索引是不会使用行级锁的，会使用表级锁。行级锁的缺点是：由于需要请求大量的锁资源，所以速度慢，内存消耗大。


## 死锁

死锁（Deadlock） 
所谓死锁：是指两个或两个以上的进程在执行过程中，因争夺资源而造成的一种互相等待的现象，若无外力作用，它们都将无法推进下去。此时称系统处于死锁状态或系统产生了死锁，这些永远在互相等待的进程称为死锁进程。由于资源占用是互斥的，当某个进程提出申请资源后，使得有关进程在无外力协助下，永远分配不到必需的资源而无法继续运行，这就产生了一种特殊现象死锁。

解除正在死锁的状态有两种方法：


#### 第一种：

1.查询是否锁表

```sql
show OPEN TABLES where In_use > 0;
```

2.查询进程（如果您有SUPER权限，您可以看到所有线程。否则，您只能看到您自己的线程）

```sql
show processlist
```

3.杀死进程id（就是上面命令的id列）

```
kill id
```


#### 第二种：

1：查看当前的事务

```sql
SELECT * FROM INFORMATION_SCHEMA.INNODB_TRX;
```

2：查看当前锁定的事务

```sql
SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
```

3：查看当前等锁的事务

```sql
SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
```


#### 杀死进程

```
kill 线程ID
```

如果系统资源充足，进程的资源请求都能够得到满足，死锁出现的可能性就很低，否则就会因争夺有限的资源而陷入死锁。其次，进程运行推进顺序与速度不同，也可能产生死锁。
产生死锁的四个必要条件：

（1） 互斥条件：一个资源每次只能被一个进程使用。
（2） 请求与保持条件：一个进程因请求资源而阻塞时，对已获得的资源保持不放。
（3） 不剥夺条件:进程已获得的资源，在末使用完之前，不能强行剥夺。
（4） 循环等待条件:若干进程之间形成一种头尾相接的循环等待资源关系。

虽然不能完全避免死锁，但可以使死锁的数量减至最少。将死锁减至最少可以增加事务的吞吐量并减少系统开销，因为只有很少的事务回滚，而回滚会取消事务执行的所有工作。由于死锁时回滚而由应用程序重新提交。


#### 下列方法有助于最大限度地降低死锁：

（1）按同一顺序访问对象。
（2）避免事务中的用户交互。
（3）保持事务简短并在一个批处理中。
（4）使用低隔离级别。
（5）使用绑定连接。

参考 :

[https://blog.csdn.net/puhaiyang/article/details/72284702][0]

[https://www.jb51.net/article/78088.htm][1]



[0]: https://blog.csdn.net/puhaiyang/article/details/72284702
[1]: https://www.jb51.net/article/78088.htm
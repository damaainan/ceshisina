## 全面了解mysql锁机制（InnoDB）与问题排查

来源：[https://juejin.im/post/5b82e0196fb9a019f47d1823](https://juejin.im/post/5b82e0196fb9a019f47d1823)

时间 2018-09-03 13:47:29

 
 ![][0]
 
MySQL/InnoDB的加锁，一直是一个常见的话题。例如，数据库如果有高并发请求，如何保证数据完整性？产生死锁问题如何排查并解决？下面是不同锁等级的区别

 
* 表级锁：开销小，加锁快；不会出现死锁；锁定粒度大，发生锁冲突的概率最高 ，并发度最低。 
* 页面锁：开销和加锁时间界于表锁和行锁之间；会出现死锁；锁定粒度界于表锁和行锁之间，并发度一般。 
* 行级锁：开销大，加锁慢；会出现死锁；锁定粒度最小，发生锁冲突的概率最低，并发度也最高。 
 
 
查看数据库拥有的存储引擎类型 `SHOW ENGINES`

## 乐观锁
 
用数据版本（Version）记录机制实现，这是乐观锁最常用的一种实现方式。何谓数据版本？即为数据增加一个版本标识，一般是通过为数据库表增加一个数字类型的 “version” 字段来实现。当读取数据时，将version字段的值一同读出，数据每更新一次，对此version值加1。当我们提交更新的时候，判断数据库表对应记录的当前版本信息与第一次取出来的version值进行比对，如果数据库表当前版本号与第一次取出来的version值相等，则予以更新，否则认为是过期数据。
 
#### 举例：
 
  
1、数据库表三个字段，分别是id、value、version
 `select id,value,version from TABLE where id = #{id}`
 
2、每次更新表中的value字段时，为了防止发生冲突，需要这样操作

```sql
update TABLE
set value=2,version=version+1
where id=#{id} and version=#{version}
```
 
## 悲观锁
 
与乐观锁相对应的就是悲观锁了。悲观锁就是在操作数据时，认为此操作会出现数据冲突，所以在进行每次操作时都要通过获取锁才能进行对相同数据的操作  ，这点跟java中的synchronized很相似，所以悲观锁需要耗费较多的时间。另外与乐观锁相对应的，悲观锁是由数据库自己实现了的，要用的时候，我们直接调用数据库的相关语句就可以了。
 
说到这里，由悲观锁涉及到的另外两个锁概念就出来了，它们就是共享锁与排它锁。共享锁和排它锁是悲观锁的不同的实现，它俩都属于悲观锁的范畴。
 
### 共享锁
 
共享锁又称读锁 (read lock)，是读取操作创建的锁。其他用户可以并发读取数据，但任何事务都不能对数据进行修改（获取数据上的排他锁），直到已释放所有共享锁。当如果事务对读锁进行修改操作，很可能会造成死锁。如下图所示。
 
 ![][1]
 
如果事务T对数据A加上共享锁后，则其他事务只能对A再加共享锁，不能加排他锁。获得共享锁的事务只能读数据，不能修改数据
 
打开第一个查询窗口

```sql
begin;/begin work;/start transaction;  (三者选一就可以)
#(lock in share mode 共享锁)
SELECT * from TABLE where id = 1  lock in share mode;
```
 
  
然后在另一个查询窗口中，对id为1的数据进行更新
 `update TABLE set name="www.souyunku.com" where id =1;`
 
此时，操作界面进入了卡顿状态，过了很久超时，提示错误信息
 
如果在超时前，第一个窗口执行`commit`，此更新语句就会成功。

```sql
[SQL]update test_one set name="www.souyunku.com" where id =1;
[Err] 1205 - Lock wait timeout exceeded; try restarting transaction
```
 
加上共享锁后，也提示错误信息

```sql
update test_one set name="www.souyunku.com" where id =1 lock in share mode;
[SQL]update  test_one set name="www.souyunku.com" where id =1 lock in share mode;
[Err] 1064 - You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'lock in share mode' at line 1
```
 
在查询语句后面增加LOCK IN SHARE MODE ，Mysql会对查询结果中的每行都加共享锁，当没有其他线程对查询结果集中的任何一行使用排他锁时，可以成功申请共享锁，否则会被阻塞。  其他线程也可以读取使用了共享锁的表，而且这些线程读取的是同一个版本的数据。
 
加上共享锁后，对于update，insert，delete语句会自动加排它锁。
 
### 排它锁
 
排他锁 exclusive lock（也叫writer lock）又称写锁。
 
名词解释：若某个事物对某一行加上了排他锁，只能这个事务对其进行读写，在此事务结束之前，其他事务不能对其进行加任何锁，其他进程可以读取,不能进行写操作，需等待其释放。 **`排它锁是悲观锁的一种实现`**  ，在上面悲观锁也介绍过。
 
若事务 1 对数据对象A加上X锁，事务 1 可以读A也可以修改A，其他事务不能再对A加任何锁，直到事物 1 释放A上的锁。这保证了其他事务在事物 1 释放A上的锁之前不能再读取和修改A。 **`排它锁会阻塞所有的排它锁和共享锁`** 
 
读取为什么要加读锁呢？防止数据在被读取的时候被别的线程加上写锁。 排他锁使用方式：在需要执行的语句后面加上 **`for update`**  就可以了`select status from TABLE where id=1 for update;`排他锁，也称写锁，独占锁，当前写操作没有完成前，它会阻断其他写锁和读锁。
 
 ![][2]
 
#### 排它锁-举例：
 
要使用排他锁，我们必须关闭mysql数据库的自动提交属性，因为MySQL默认使用autocommit模式，也就是说，当你执行一个更新操作后，MySQL会立刻将结果进行提交。
 
我们可以使用命令设置MySQL为非autocommit模式：

```sql
set autocommit=0;
# 设置完autocommit后，我们就可以执行我们的正常业务了。具体如下：
# 1. 开始事务
begin;/begin work;/start transaction; (三者选一就可以)
# 2. 查询表信息（for update加锁）
select status from TABLE where id=1 for update;
# 3. 插入一条数据
insert into TABLE (id,value) values (2,2);
# 4. 修改数据为
update TABLE set value=2 where id=1;
# 5. 提交事务
commit;/commit work
```
 
## 行锁
 
总结：多个事务操作同一行数据时，后来的事务处于阻塞等待状态。这样可以避免了脏读等数据一致性的问题。后来的事务可以操作其他行数据，解决了表锁高并发性能低的问题。

```sql
# Transaction-A
mysql> set autocommit = 0;
mysql> update innodb_lock set v='1001' where id=1;
mysql> commit;

# Transaction-B
mysql> update innodb_lock set v='2001' where id=2;
Query OK, 1 row affected (0.37 sec)
mysql> update innodb_lock set v='1002' where id=1;
Query OK, 1 row affected (37.51 sec)
```
 
现实：当执行批量修改数据脚本的时候，行锁升级为表锁。其他对订单的操作都处于等待中，，， 原因：nnoDB只有在通过索引条件检索数据时使用行级锁，否则使用表锁！  而模拟操作正是通过id去作为检索条件，而id又是MySQL自动创建的唯一索引，所以才忽略了行锁变表锁的情况
 
#### 总结：InnoDB的行锁是针对索引加的锁，不是针对记录加的锁。并且该索引不能失效，否则都会从行锁升级为表锁。

 
* 行锁的劣势：开销大；加锁慢；会出现死锁 
* 行锁的优势：锁的粒度小，发生锁冲突的概率低；处理并发的能力强 
* 加锁的方式：自动加锁。对于UPDATE、DELETE和INSERT语句，InnoDB会自动给涉及数据集加排他锁；对于普通SELECT语句，InnoDB不会加任何锁；当然我们也可以显示的加锁： 
 
 
从上面的案例看出，行锁变表锁似乎是一个坑，可MySQL没有这么无聊给你挖坑。这是因为MySQL有自己的执行计划。 当你需要更新一张较大表的大部分甚至全表的数据时。而你又傻乎乎地用索引作为检索条件。一不小心开启了行锁(没毛病啊！保证数据的一致性！)。可MySQL却认为大量对一张表使用行锁，会导致事务执行效率低，从而可能造成其他事务长时间锁等待和更多的锁冲突问题，性能严重下降。所以MySQL会将行锁升级为表锁，即实际上并没有使用索引。 我们仔细想想也能理解，既然整张表的大部分数据都要更新数据，在一行一行地加锁效率则更低。其实我们可以通过explain命令查看MySQL的执行计划，你会发现key为null。表明MySQL实际上并没有使用索引，行锁升级为表锁也和上面的结论一致。
 
#### 注意：行级锁都是基于索引的，如果一条SQL语句用不到索引是不会使用行级锁的，会使用表级锁。
 
### 间隙锁
 
当我们用范围条件检索数据，并请求共享锁或排他锁时，InnoDB会给符合条件的数据记录的索引项加锁； 对于键值在条件范围内但并不存在的记录，叫做"间隙(GAP)"。InnoDB也会对这个"间隙"加锁，这种锁机制就是所谓的间隙锁(Next-Key锁)。

```sql
Transaction-A
mysql> update innodb_lock set k=66 where id >=6;
Query OK, 1 row affected (0.63 sec)
mysql> commit;

Transaction-B
mysql> insert into innodb_lock (id,k,v) values(7,'7','7000');
Query OK, 1 row affected (18.99 sec)
```
 
#### 危害(坑)：若执行的条件是范围过大，则InnoDB会将整个范围内所有的索引键值全部锁定，很容易对性能造成影响。
 
## 表锁
 
如何加表锁？ innodb 的行锁是在有索引的情况下，没有索引的表是锁定全表的。
 
### Innodb中的行锁与表锁
 
前面提到过，在Innodb引擎中既支持行锁也支持表锁，那么什么时候会锁住整张表，什么时候只锁住一行呢？只有通过索引条件检索数据，InnoDB才使用行级锁，否则，InnoDB将使用表锁！
 
在实际应用中，要特别注意InnoDB行锁的这一特性，不然的话，可能导致大量的锁冲突，从而影响并发性能。
 
行级锁都是基于索引的，如果一条SQL语句用不到索引是不会使用行级锁的，会使用表级锁。行级锁的缺点是：由于需要请求大量的锁资源，所以速度慢，内存消耗大。
 
## 死锁
 
死锁（Deadlock） 所谓死锁：是指两个或两个以上的进程在执行过程中，因争夺资源而造成的一种互相等待的现象，若无外力作用，它们都将无法推进下去。此时称系统处于死锁状态或系统产生了死锁，这些永远在互相等待的进程称为死锁进程。由于资源占用是互斥的，当某个进程提出申请资源后，使得有关进程在无外力协助下，永远分配不到必需的资源而无法继续运行，这就产生了一种特殊现象死锁。
 
解除正在死锁的状态有两种方法：
 
#### 第一种：

 
* 查询是否锁表`show OPEN TABLES where In_use > 0;` 
* 查询进程（如果您有SUPER权限，您可以看到所有线程。否则，您只能看到您自己的线程） 
`show processlist` 
* 杀死进程id（就是上面命令的id列） 
`kill id` 
 
 
#### 第二种：

 
* 查看当前的事务 
`SELECT * FROM INFORMATION_SCHEMA.INNODB_TRX;` 
* 查看当前锁定的事务 
`SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;` 
* 查看当前等锁的事务 
`SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;`
* 杀死进程 
`kill 进程ID` 
 
 
如果系统资源充足，进程的资源请求都能够得到满足，死锁出现的可能性就很低，否则就会因争夺有限的资源而陷入死锁。其次，进程运行推进顺序与速度不同，也可能产生死锁。 产生死锁的四个必要条件：

 
* 互斥条件：一个资源每次只能被一个进程使用。 
* 请求与保持条件：一个进程因请求资源而阻塞时，对已获得的资源保持不放。 
* 不剥夺条件:进程已获得的资源，在末使用完之前，不能强行剥夺。 
* 循环等待条件:若干进程之间形成一种头尾相接的循环等待资源关系。 
 
 
虽然不能完全避免死锁，但可以使死锁的数量减至最少。将死锁减至最少可以增加事务的吞吐量并减少系统开销，因为只有很少的事务回滚，而回滚会取消事务执行的所有工作。由于死锁时回滚的操作由应用程序重新提交。
 
#### 下列方法有助于最大限度地降低死锁：

 
* 按同一顺序访问对象。 
* 避免事务中的用户交互。 
* 保持事务简短并在一个批处理中。 
* 使用低隔离级别。 
* 使用绑定连接。 
 
 
## MyISAM存储引擎
 
InnoDB和MyISAM的最大不同点有两个：

 
* InnoDB支持事务(transaction)；MyISAM不支持事务 
* 默认采用行级锁。加锁可以保证事务的一致性，可谓是有人(锁)的地方，就有江湖(事务) 
* MyISAM不适合高并发 
 
 
### 共享读锁
 
对MyISAM表的读操作（加读锁），不会阻塞其他进程对同一表的读操作，但会阻塞对同一表的写操作。只有当读锁释放后，才能执行其他进程的写操作。在锁释放前不能读其他表。
 
 ![][3]
 
### 独占写锁
 
对MyISAM表的写操作（加写锁），会阻塞其他进程对同一表的读和写操作，只有当写锁释放后，才会执行其他进程的读写操作。在锁释放前不能写其他表。
 
 ![][4]
 
总结：

 
* 表锁，读锁会阻塞写，不会阻塞读。而写锁则会把读写都阻塞。 
* 表锁的加锁/解锁方式：MyISAM 在执行查询语句(SELECT)前,会自动给涉及的所有表加读锁,在执行更新操作 (UPDATE、DELETE、INSERT 等)前，会自动给涉及的表加写锁，这个过程并不需要用户干预，因此，用户一般不需要直接用LOCK TABLE命令给MyISAM表显式加锁。 
 
 
如果用户想要显示的加锁可以使用以下命令：
 
#### 锁定表：

```sql
LOCK TABLES tbl_name {READ | WRITE},[ tbl_name {READ | WRITE},…] 
```
 
#### 解锁表：

```sql
UNLOCK TABLES 
```
 
在用`LOCK TABLES`给表显式加表锁时,必须同时取得所有涉及到表的锁。 在执行`LOCK TABLES`后，只能访问显式加锁的这些表，不能访问未加锁的表;
 
如果加的是读锁，那么只能执行查询操作，而不能执行更新操作。
 
在自动加锁的情况下也基本如此，MyISAM 总是一次获得 SQL 语句所需要的全部锁。这也正是 MyISAM 表不会出现死锁(Deadlock Free)的原因。
 
对表test_table增加读锁：

```sql
LOCK TABLES test_table READ UNLOCK test_table
```
 
对表test_table增加写锁

```sql
LOCK TABLES test_table WRITE UNLOCK test_table
```
 
当使用 LOCK TABLES 时，不仅需要一次锁定用到的所有表,而且,同一个表在 SQL 语句中出现多少次，就要通过与 SQL 语句中相同的别名锁定多少次，否则也会出错！
 
比如如下SQL语句：

```sql
select a.first_name,b.first_name, from actor a,actor b where a.first_name = b.first_name;
```
 
该Sql语句中，actor表以别名的方式出现了两次，分别是a,b，这时如果要在该Sql执行之前加锁就要使用以下Sql:

```sql
lock table actor as a read,actor as b read;
```
 
### 并发插入
 
上文说到过 MyISAM 表的读和写是串行的,但这是就总体而言的。在一定条件下,MyISAM表也支持查询和插入操作的并发进行。 MyISAM存储引擎有一个系统变量concurrent_insert,专门用以控制其并发插入的行为,其值分别可以为0、1或2。

 
* 当concurrent_insert设置为0时,不允许并发插入。 
* 当concurrent_insert设置为1时,如果MyISAM表中没有空洞(即表的中间没有被删除的 行),MyISAM允许在一个进程读表的同时,另一个进程从表尾插入记录。这也是MySQL 的默认设置。 
* 当concurrent_insert设置为2时,无论MyISAM表中有没有空洞,都允许在表尾并发插入记录。 
 
 
可以利用MyISAM存储引擎的并发插入特性,来解决应用中对同一表查询和插入的锁争用。
 
### MyISAM的锁调度
 
前面讲过，MyISAM 存储引擎的读锁和写锁是互斥的，读写操作是串行的。那么，一个进程请求某个 MyISAM 表的读锁，同时另一个进程也请求同一表的写锁，MySQL 如何处理呢?
 
#### 答案是写进程先获得锁。
 
不仅如此，即使读请求先到锁等待队列，写请求后到，写锁也会插到读锁请求之前！这是因为 MySQL 认为写请求一般比读请求要重要。这也正是 MyISAM 表不太适合于有大量更新操作和查询操作应用的原因，因为大量的更新操作会造成查询操作很难获得读锁，从而可能永远阻塞。这种情况有时可能会变得非常糟糕！
 
幸好我们可以通过一些设置来调节 MyISAM 的调度行为。
 
通过指定 **`启动参数low-priority-updates`**  ，使MyISAM引擎默认给予读请求以优先的权利。

 
* 通过执行命令`SET LOWPRIORITYUPDATES=1,`使该连接发出的更新请求优先级降低。  
* 通过指定INSERT、UPDATE、DELETE语句的 **`LOW_PRIORITY`**  属性,降低该语句的优先级。  
* 另外,MySQL也 供了一种折中的办法来调节读写冲突,即给 **`系统参数max_write_lock_count`**  设置一个合适的值,当一个表的读锁达到这个值后,MySQL就暂时将写请求的优先级降低, 给读进程一定获得锁的机会。  
 
 
### 总结

 
* 数据库中的锁从锁定的粒度上分可以分为行级锁、页级锁和表级锁。 
* MySQL的MyISAM引擎支持表级锁。 
* 表级锁分为两种：共享读锁、互斥写锁。这两种锁都是阻塞锁。 
* 可以在读锁上增加读锁，不能在读锁上增加写锁。在写锁上不能增加写锁。 
* 默认情况下，MySql在执行查询语句之前会加读锁，在执行更新语句之前会执行写锁。 
* 如果想要显示的加锁/解锁的花可以使用LOCK TABLES和UNLOCK来进行。 
* 在使用LOCK TABLES之后，在解锁之前，不能操作未加锁的表。 
* 在加锁时，如果显示的指明是要增加读锁，那么在解锁之前，只能进行读操作，不能执行写操作。 
* 如果一次Sql语句要操作的表以别名的方式多次出现，那么就要在加锁时都指明要加锁的表的别名。 
* MyISAM存储引擎有一个系统变量concurrent_insert,专门用以控制其并发插入的行为,其值分别可以为0、1或2。 
* 由于读锁和写锁互斥，那么在调度过程中，默认情况下，MySql会本着写锁优先的原则。可以通过low-priority-updates来设置。 
 
 
## 实践解决
 
### 分析行锁定
 
通过检查 **`InnoDB_row_lock`**  状态变量分析系统上中行锁的争夺情况

```sql
mysql> show status like 'innodb_row_lock%';
+-------------------------------+-------+
| Variable_name                 | Value |
+-------------------------------+-------+
| Innodb_row_lock_current_waits | 0     |
| Innodb_row_lock_time          | 0     |
| Innodb_row_lock_time_avg      | 0     |
| Innodb_row_lock_time_max      | 0     |
| Innodb_row_lock_waits         | 0     |
+-------------------------------+-------+
```

 
* **`innodb_row_lock_current_waits:`**  当前正在等待锁定的数量  
* **`innodb_row_lock_time:`**  从系统启动到现在锁定总时间长度；非常重要的参数，  
* **`innodb_row_lock_time_avg:`**  每次等待所花平均时间；非常重要的参数，  
* **`innodb_row_lock_time_max:`**  从系统启动到现在等待最常的一次所花的时间；  
* **`innodb_row_lock_waits:`**  系统启动后到现在总共等待的次数；非常重要的参数。直接决定优化的方向和策略。  
 
 
#### 行锁优化

 
* 尽可能让所有数据检索都通过索引来完成，避免无索引行或索引失效导致行锁升级为表锁。 
* 尽可能避免间隙锁带来的性能下降，减少或使用合理的检索范围。 
* 尽可能减少事务的粒度，比如控制事务大小，而从减少锁定资源量和时间长度，从而减少锁的竞争等，提供性能。 
* 尽可能低级别事务隔离，隔离级别越高，并发的处理能力越低。 
 
 
### 表锁优化
 
查看加锁情况how open tables; 1表示加锁，0表示未加锁。

```sql
mysql> show open tables where in_use > 0;
+----------+-------------+--------+-------------+
| Database | Table       | In_use | Name_locked |
+----------+-------------+--------+-------------+
| lock     | myisam_lock |      1 |           0 |
+----------+-------------+--------+-------------+
```
 
#### 分析表锁定
 
可以通过检查table_locks_waited 和 table_locks_immediate 状态变量分析系统上的表锁定：`show status like 'table_locks%'`

```sql
mysql> show status like 'table_locks%';
+----------------------------+-------+
| Variable_name              | Value |
+----------------------------+-------+
| Table_locks_immediate      | 104   |
| Table_locks_waited         | 0     |
+----------------------------+-------+
```

 
* **`table_locks_immediate:`**  表示立即释放表锁数。  
* **`table_locks_waited:`**  表示需要等待的表锁数。此值越高则说明存在着越严重的表级锁争用情况。  
 
 
此外，MyISAM的读写锁调度是写优先，这也是MyISAM不适合做写为主表的存储引擎。因为写锁后，其他线程不能做任何操作，大量的更新会使查询很难得到锁，从而造成永久阻塞。
 
### 什么场景下用表锁
 
第一种情况：全表更新。事务需要更新大部分或全部数据，且表又比较大。若使用行锁，会导致事务执行效率低，从而可能造成其他事务长时间锁等待和更多的锁冲突。
 
第二种情况：多表查询。事务涉及多个表，比较复杂的关联查询，很可能引起死锁，造成大量事务回滚。这种情况若能一次性锁定事务涉及的表，从而可以避免死锁、减少数据库因事务回滚带来的开销。

 
* InnoDB 支持表锁和行锁，使用索引作为检索条件修改数据时采用行锁，否则采用表锁。 
* InnoDB 自动给修改操作加锁，给查询操作不自动加锁 
* 行锁可能因为未使用索引而升级为表锁，所以除了检查索引是否创建的同时，也需要通过explain执行计划查询索引是否被实际使用。 
* 行锁相对于表锁来说，优势在于高并发场景下表现更突出，毕竟锁的粒度小。 
* 当表的大部分数据需要被修改，或者是多表复杂关联查询时，建议使用表锁优于行锁。 
* 为了保证数据的一致完整性，任何一个数据库都存在锁定机制。锁定机制的优劣直接影响到一个数据库的并发处理能力和性能。 
 
 
mysql 5.6 在 update 和 delete 的时候，where 条件如果不存在索引字段，那么这个事务是否会导致表锁？ 有人回答： 只有主键和唯一索引才是行锁，普通索引是表锁。
 
结果发现普通索引并不一定会引发表锁，在普通索引中，是否引发表锁取决于普通索引的高效程度。
 
上文提及的“高效”是相对主键和唯一索引而言，也许“高效”并不是一个很好的解释，只要明白在一般情况下，“普通索引”效率低于其他两者即可。 属性值重复率高
 
### 属性值重复率
 
当“值重复率”低时，甚至接近主键或者唯一索引的效果，“普通索引”依然是行锁；当“值重复率”高时，MySQL 不会把这个“普通索引”当做索引，即造成了一个没有索引的 SQL，此时引发表锁。
 
同 JVM 自动优化 java 代码一样，MySQL 也具有自动优化 SQL 的功能。低效的索引将被忽略，这也就倒逼开发者使用正确且高效的索引。
 
#### 举个栗子：

 
* 用户A在银行卡有100元钱，某一刻用户B向A转账50元（称为B操作），同时有用户C向A转账50元（称为C操作); 
* B操作从数据库中读取他此时的余额100，计算新的余额为100+50=150 
* C操作也从数据库中读取他此时的余额100，计算新的余额为100+50=150 
* B操作将balance=150写入数据库,之后C操作也将balance=150写入数据库 
* 最终A的余额变为150 
 
 
上面的例子，A同时收到两笔50元转账，最后的余额应该是200元，但却因为并发的问题变为了150元，原因是B和C向A发起转账请求时，同时打开了两个数据库会话，进行了两个事务，后一个事务拿到了前一个事务的中间状态数据，导致更新丢失。
 
常用的解决思路有两种：

 
* 加锁同步执行 
* update前检查数据一致性 
 
 
要注意悲观锁和乐观锁都是业务逻辑层次的定义，不同的设计可能会有不同的实现。在mysql层常用的悲观锁实现方式是加一个排他锁。
 
然而实际上并不是这样，实际上加了排他锁的数据，在释放锁（事务结束）之前其他事务不能再对该数据加锁 排他锁之所以能阻止update,delete等操作是因为update，delete操作会自动加排他锁， 也就是说即使加了排他锁也无法阻止select操作。而select XX for update 语法可以对select操作加上排他锁。  所以为了防止更新丢失可以在select时加上for update加锁 这样就可以阻止其余事务的select for update **`(但注意无法阻止select)`** 
 
乐观锁example：

```sql
begin;
select balance from account where id=1;
-- 得到balance=100;然后计算balance=100+50=150
update account set balance = 150 where id=1 and balance = 100;
commit;
```
 
如上，如果sql在执行的过程中发现update的affected为0 说明balance不等于100即该条数据有被其余事务更改过，此时业务上就可以返回失败或者重新select再计算
 
### 回滚的话，为什么只有部分 update 语句失败，而不是整个事务里的所有 update 都失败？
 
这是因为咱们的 innodb 默认是自动提交的：
 
需要注意的是，通常还有另外一种情况也可能导致部分语句回滚，需要格外留意。在 innodb 里有个参数叫：innodb_rollback_on_timeout

```sql
show VARIABLES LIKE 'innodb_rollback_on_timeout'
+----------------------------+---------+
| Variable_name              | Value   |
|----------------------------+---------|
| innodb_rollback_on_timeout | OFF     |
+----------------------------+---------+
```
 
官方手册里这样描述：
 
In MySQL 5.1, InnoDB rolls back only the last statement on a transaction timeout by default. If –innodb_rollback_on_timeout is specified, a transaction timeout causes InnoDB to abort and roll back the entire transaction (the same behavior as in MySQL 4.1). This variable was added in MySQL 5.1.15.
 
解释：这个参数关闭或不存在的话遇到超时只回滚事务最后一个Query，打开的话事务遇到超时就回滚整个事务。
 
#### 注意：

 
* MySQL insert、update、replace into 死锁回滚默认情况下不会记录该条 DML 语句到 binlog，也不会有回滚日志、error ，如果不对 jdbc 返回码做处理 Mapreduce、hive 等大数据计算任务会显示 success 造成插入、更新部分成功部分失败，但是可以从 SHOW ENGINE INNODB STATUS\G 看到数据库的死锁回滚日志。这种情况下建议根据 jdbc 错误码或者 SQLException 增加重试机制或者 throw exception/error。 
* 在一个事务系统中，死锁是确切存在并且是不能完全避免的。 InnoDB会自动检测事务死锁，立即回滚其中某个事务，并且返回一个错误。它根据某种机制来选择那个最简单（代价最小）的事务来进行回滚。偶然发生的死锁不必担心，但死锁频繁出现的时候就要引起注意了。InnoDB存储引擎有一个后台的锁监控线程，该线程负责查看可能的死锁问题，并自动告知用户。 
 
 
### 怎样降低 innodb 死锁几率？
 
死锁在行锁及事务场景下很难完全消除，但可以通过表设计和SQL调整等措施减少锁冲突和死锁，包括：

 
* 尽量使用较低的隔离级别，比如如果发生了间隙锁，你可以把会话或者事务的事务隔离级别更改为 RC(read committed)级别来避免，但此时需要把 binlog_format 设置成 row 或者 mixed 格式 
* 精心设计索引，并尽量使用索引访问数据，使加锁更精确，从而减少锁冲突的机会； 
* 选择合理的事务大小，小事务发生锁冲突的几率也更小； 
* 给记录集显示加锁时，最好一次性请求足够级别的锁。比如要修改数据的话，最好直接申请排他锁，而不是先申请共享锁，修改时再请求排他锁，这样容易产生死锁； 
* 不同的程序访问一组表时，应尽量约定以相同的顺序访问各表，对一个表而言，尽可能以固定的顺序存取表中的行。这样可以大大减少死锁的机会； 
 
 
#### 例子：

```sql
DELETE FROM onlineusers WHERE datetime <= now() - INTERVAL 900 SECOND
至
DELETE FROM onlineusers WHERE id IN (SELECT id FROM onlineusers
    WHERE datetime <= now() - INTERVAL 900 SECOND order by id) u;
```
 
尽量用相等条件访问数据，这样可以避免间隙锁对并发插入的影响； 不要申请超过实际需要的锁级别；除非必须，查询时不要显示加锁； 对于一些特定的事务，可以使用表锁来提高处理速度或减少死锁的可能。
 
### 诡异的 Lock wait timeout

```sql
# 默认 lock 超时时间 50s，这个时间真心不短了
show variables like 'innodb_lock_wait_timeout';
+--------------------------+---------+
| Variable_name            |   Value |
|--------------------------+---------|
| innodb_lock_wait_timeout |      50 |
+--------------------------+---------+
```
 
而且这次`SHOW ENGINE INNODB STATUS\G`也没出现任何死锁信息，然后又将目光转向 MySQL-server 日志，希望能从日志里看一看那个时刻前后数据究竟在做什么操作。这里先简单的介绍下MySQL日志文件系统的组成：

 
* error 日志：记录启动、运行或停止 mysqld 时出现的问题，默认开启。 
* general 日志：通用查询日志，记录所有语句和指令，开启数据库会有 5% 左右性能损失。 
* binlog 日志：二进制格式，记录所有更改数据的语句，主要用于 slave 复制和数据恢复。 
* slow 日志：记录所有执行时间超过 long_query_time 秒的查询或不使用索引的查询，默认关闭。 
* Innodb日志：innodb redo log、undo log，用于恢复数据和撤销操作。 
 
 
从上面的介绍可以看到，目前这个问题的日志可能在 2 和 4 中，看了下 4 中没有，那就只能开启 2 了，但 2 对数据库的性能有一定损耗，由于是全量日志，量非常巨大，所以开启一定要谨慎：

```sql
-- general_log 日志默认关闭，开启会影响数据库 5% 左右性能：
show variables like 'general%';
+------------------+---------------------------------+
| Variable_name    | Value                           |
|------------------+---------------------------------|
| general_log      | OFF                             |
| general_log_file | /opt/data/mysql/tjtx-103-26.log |
+------------------+---------------------------------+

-- 全局 session 级别开启：
set global general_log=1

-- 如果需要对当前 session 生效需要：
set general_log=1

-- set 指令设置的动态参数在 MySQL 重启后失效，如果需要永久生效需要在 /etc/my.cnf 中配置静态变量/参数。
-- 如果不知道 my.cnf 位置，可以根据 mysql -? | grep ".cnf" 查询
                      order of preference, my.cnf, $MYSQL_TCP_PORT,
/etc/my.cnf /etc/mysql/my.cnf /usr/etc/my.cnf ~/.my.cnf
```
 
set 指令设置的动态参数在 MySQL 重启后失效，如果需要永久生效需要在 /etc/my.cnf 中配置静态变量/参数。
 
  
更多内容请参考
 
[mysql死锁问题分析][5]
 
[mysql中插入，更新，删除锁][6]
 
[MySQL InnoDB 锁——官方文档][7] 
 


[5]: https://link.juejin.im?target=https%3A%2F%2Fyq.aliyun.com%2Farticles%2F5533%3F%26amp%3Butm_source%3Dqq
[6]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fzhuangjiesen%2Freading-learning-coding%2Fblob%2Fmaster%2Fmysql%2Fmysql%25E4%25B8%25AD%2520insert%25E3%2580%2581update%25E3%2580%2581delete%25E9%2594%2581.md
[7]: https://link.juejin.im?target=https%3A%2F%2Fsegmentfault.com%2Fa%2F1190000014071758
[0]: ./img/MJn6fa7.png
[1]: ./img/FfmeEnr.png
[2]: ./img/VRzuEnb.png
[3]: ./img/FFNjQ3z.png
[4]: ./img/6rYva2z.png
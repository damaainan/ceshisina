## MySQL InnoDB 可重复读下的排他锁探索

来源：[http://www.letiantian.me/mysql-innodb-rr-x-lock-example/](http://www.letiantian.me/mysql-innodb-rr-x-lock-example/)

时间 2018-09-24 14:02:06


排他锁是一种独占锁，用于独占资源。以多人轮流使用吹风机吹头发为例子。独占什么资源？独占吹风机，在独占的期间内，只有这个人能使用吹风机，独占结束后，别人尝试独占吹风机。当然之前用过吹风机的人也可以继续尝试独占。如何尝试独占？众人可以掷骰子，可以排队等等。锁在哪里？在这里，锁是隐式的，看不到的，存在于「吹风机同一时刻只能服务一个人」这句话里。

本文主要通过一些示例，观察MySQL InnoDB中，可重复读隔离级别下，行锁中排他锁的表现。

注意，对于`排他锁`有三个限定修饰：


* InnoDB 存储引擎
* 事务隔离级别是可重复读。这也是MySQL默认的事务隔离级别。
* 行锁
  

再加一个限定：MySQL 5.6版本，默认配置 —— 本文中除非特别说明，都是这个配置。


## 基础知识  

先准备一些基础知识。这些整理自网络和《MySQL技术内幕-InnoDB存储引擎》。


### 事务的4个特性：ACID

| 特性 | 英文 | 说明 |
| - | - | - |
| 原子性 | Atomicity | 一个事物内所有操作共同组成一个原子包，要么全部成功，要么全部失败 |
| 一致性 | Consistency | 事务将数据库从一种状态转变为下一种一致性状态。在事务开始之前和结束之后，事务的完整性约束没有被破坏。 |
| 隔离性 | Isolation | 事务之间不干扰。 |
| 持久性 | Durability | 事务一旦提交，其结果是永久性的。即使发生当即等故障，数据库也能叫数据恢复。 |
  

对于一致性的一种解释是，事务开始前和结束后，完整性约束没有被破坏，比如某一列要求每个数据具有唯一性，那么事务前后，无论有无新增数据，这一列的每列数据仍然具有唯一性。

另一种关于一致性的解释中更关注中间状态。一致性要求中间状态不被其他事务感知。但出于性能等方面考虑，不同的`隔离性`程度通过对`一致性`不同程度的破坏来提升性能和并发能力。

隔离性的程度，就是隔离级别。


### 隔离级别

| 隔离级别 | 是否会脏读 | 是否会不可重复读 | 是否会幻读 |
| - | - | - | - |
| 读未提交 | 是 | 是 | 是 |
| 读已提交 | 否 | 是 | 是 |
| 可重复读 | 否 | 否 | 是 |
| 串行化 | 否 | 否 | 否 |
  

上表中，隔离级别依次增高。隔离级别越高，越不会出现奇怪的`*读`问题。下表示关于`脏读`、`不可重复读`、`幻读`的解释：

| 概念 | 解释 |
| - | - |
| 脏读 | 当一个事务正在访问数据，并且对数据进行了修改，而这种修改还没有提交到数据库中，这时，另外一个事务也访问这个数据，然后使用了这个数据 |
| 不可重复读 | 在一个事务内，多次读同一数据。在这个事务还没有结束时，另外一个事务也访问该同一数据。那么，在第一个事务中的两次读数据之间，由于第二个事务的修改，那么第一个事务两次读到的的数据可能是不一样的。这样在一个事务内两次读到的数据是不一样的，因此称为是不可重复读。 |
| 幻读 | 例如第一个事务对一个表中的数据进行了修改，这种修改涉及到表中的全部数据行。同时，第二个事务也修改这个表中的数据，这种修改是向表中插入一行新数据。那么，以后就会发生操作第一个事务的用户发现表中还有没有修改的数据行，就好象发生了幻觉一样。 |
  

一般的说法是隔离级别越高，性能越差。首先串行化的性能的确很差。事务中一个纯粹的一个select操作就会把数据锁住。但是我看到网上一些对比，可重复读的性能并不比读已提交差。另外，MySQL的可重复读，从某个角度而言，没有幻读问题。


### 索引的底层实现  

索引用来做什么？用于快速检索数据。

InnoDB的索引基于B+树。行锁是基于索引的。B+树是一种平衡查找树，在基于磁头的硬盘中查找性能很高。

在B+树中，非叶子节点充当的是索引的作用。所有的叶子结点中包含了全部关键字的信息，及指向含这些关键字记录的指针，且叶子结点本身依关键字的大小自小而大顺序链接。

在 MySQL 中，索引有聚集索引和辅助索引之分：

| 名词 | 解释 |
| - | - |
| 聚集索引 | 按照一张表的主键构造一颗B+树，叶子节点存放的是整张表的行记录数据。 |
| 辅助索引 | 也叫非聚集索引。   叶节点除了包含键值以外，每个叶级别中的索引行中还包含了一个书签（bookmark），该书签就是相应行数据的聚集索引键。 |
  

如果一张表在创建时没设置主键怎么办？没关系，MySQL会帮忙创建一个我们看不到的主键。

另外，索引也可以划分为「唯一索引」和「非唯一索引」。


### 锁之间的兼容性  

InnoDB中有很多种锁，比如针对表这个粒度有共享锁(S)、排他锁(X)、意向共享锁(IS)、意向排他锁(IX)。表级兼容性如下：

| | X | IX | S | IS |
| - | - | - | - | - |
| X | Conflict | Conflict | Conflict | Conflict |
| IX | Conflict | Compatible | Conflict | Compatible |
| S | Conflict | Conflict | Compatible | Compatible |
| IS | Conflict | Compatible | Compatible | Compatible |
  

什么时候用到意向锁？在锁行之前，会对表加上对应的意向锁。

注意，意向锁之间是互相兼容的，但与共享锁、排他锁不全部兼容。那么要意向锁干嘛用？

想象下，当前表中有10条数据被加上了行锁，现在要对表加排他锁（X），肯定要判断是不是有数据已经被锁了，如果有，那对表加排他锁的操作就要等一下，等到表中没有任何锁为止。

那么如何判断是不是有数据已经被锁？两种方案：


* 一条条数据扫描
* 根据意向锁
  

可以看出，意向锁是最合适的方案。

行锁分为共享锁和排他锁。

再看下行锁之间的兼容性：

| | X | S |
| - | - | - |
| X | Conflict | Conflict |
| S | Conflict | Compatible |
  

有哪些会导致行锁呢？

```sql
select * lock in share mode
select * for update
udpate
delete
insert
```

如果要使用行锁锁住的数据太多，会升级为表锁。什么叫太多？一个测试结果是超过20%的数据。注意，20%只是一个参考值，并非确定值。

行锁的另一种划分，是分成记录锁、间隙锁、next-key锁等。含义不表，均在下面的示例中提现。


#### 什么时候释放锁呢？


* 如果是单条SQL，执行完后释放锁。
* 如果在事务中锁数据，等事务结束后，锁被释放。
  


#### 提问：

MySQL的锁表指令是什么？


## 探索（一）  


### 数据准备  

创建数据库和table：

```sql
CREATE DATABASE `test01`;
CREATE DATABASE `test02`;

CREATE TABLE `test01`.`ttt` (
    id BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    num BIGINT(20) DEFAULT 0 NOT NULL
)ENGINE =InnoDB DEFAULT CHARSET =utf8mb4;

CREATE TABLE `test02`.`ttt` (
    id BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    num BIGINT(20) DEFAULT 0 NOT NULL
)ENGINE =InnoDB DEFAULT CHARSET =utf8mb4;
```

插入数据：

```sql
INSERT INTO test01.ttt(id, num) VALUES(1, 1001);
INSERT INTO test01.ttt(id, num) VALUES(2, 1002);

INSERT INTO test02.ttt(id, num) VALUES(1, 2001);
INSERT INTO test02.ttt(id, num) VALUES(2, 2002);
```

查看数据：

```sql
mysql> select * from test01.ttt;
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+

mysql> select * from test02.ttt;
+----+------+
| id | num  |
+----+------+
|  1 | 2001 |
|  2 | 2002 |
+----+------+
```


### 示例1  

下面的`会话`是指，打开终端，输入`mysql -uroot -p`，然后输密码，进入与MySQL服务器的交互。若有多个会话，则是打开了多个终端。。

| 步骤 | 会话 |
| - | - |
| 1 | start transaction; |
| 2 | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 3 | INSERT INTO test02.ttt(id, num) VALUES(3, 2003); |
| 4 | rollback; |
| 5 | select * from test01.ttt; |
| 6 | select * from test02.ttt; |
  

因为第4会回滚了，第5、6步查到的数据都还是原先的两条。这里验证了，一个事务中可以操作一个MySQL实例中多个数据库中的数据。


### 示例2  

验证锁的可重入。

| 步骤 | 会话 |
| - | - |
| 1 | start transaction; |
| 2 | select * from test01.ttt where id=1 for update; |
| 3 | select * from test01.ttt where id=1 or id=2 for update; |
| 4 | select * from test01.ttt where id=1 for update; |
  

步骤3：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```


### 示例3

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | select * from test01.ttt where id = 1 for update; | |
| 3 | select * from test02.ttt where id = 1 for update; | |
| 4 |  | select * from test01.ttt where id = 1 for update; |
| 5 |  | select * from test02.ttt where id = 1 for update; |
| 6 |  | select * from test01.ttt; |
| 7 |  | update  test01.ttt set num=123 where id=1; |
  


#### 会话1：

第2、3步骤，直接输出数据。会对`test01.ttt`、`test02.ttt`中id为1的数据加排他锁。


#### 会话2：

第4步会发生什么？

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

事务还在。

第5步将发生什么？

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

第6步将发生什么？

直接取出数据。

第7步将发生什么？

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

出现了锁等待超时错误。这个等待时间，默认是50秒。可以通过`select @@innodb_lock_wait_timeout;`查看。

注意，出现这种错误时，会话2的事务并没有结束。怎么验证？可以通过执行下面的SQL查看当前有哪些事务：

```sql
SELECT * FROM information_schema.INNODB_TRX \G
```


### 示例4

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id = 1 for update; | |
| 3 |  | select * from test01.ttt where id = 1 for update; |
| 4 |  | select * from test01.ttt where id = 2 for update; |
  


#### 会话1：

步骤2将直接取出数据：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```


#### 会话2：

步骤3将：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤4将：直接输出结果

```sql
+----+------+
| id | num  |
+----+------+
|  2 | 1002 |
+----+------+
```

会话2虽然没有看起事务，但步骤3依然出现锁超时。可以认为一个单条的SQL可以看做一个单独的事务。


### 示例5

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); | |
| 3 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
  


#### 会话1：

步骤2：

```sql
Query OK, 1 row affected (0.00 sec)
```


#### 会话2：

步骤3：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

是的，id为3，被会话1锁了。


### 示例6

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | update  test01.ttt set num=999 where id=1; | |
| 3 | select * from test01.ttt where id = 1; | |
| 4 |  | select * from test01.ttt where id = 1 for update; |
| 5 |  | select * from test01.ttt where id = 1; |
| 6 | rollback; | |
| 7 | select * from test01.ttt where id = 1; |
  


#### 会话1：

步骤2会锁数据。

步骤3：

```sql
+----+-----+
| id | num |
+----+-----+
|  1 | 999 |
+----+-----+
```

步骤7：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```


#### 会话2：

步骤4：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```


### 示例7

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | select * from test01.ttt where id = 3 for update; | |
| 3 |  | select * from test01.ttt where id = 3 for update; |
| 4 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 5 |  | INSERT INTO test01.ttt(id, num) VALUES(4, 1004); |
  


#### 会话1：

步骤2：

```sql
Empty set (0.00 sec)
```

没有数据，但这里其实把(2，∞)这个范围的id都锁住了。这就是间隙锁。


#### 会话2：

步骤3：

```sql
Empty set (0.00 sec)
```

虽然id=3被会话1锁了，但因为没数据，这一步直接给了结果。

步骤4、5：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例8

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id >0 and id<10 for update; | |
| 3 |  | select * from test01.ttt where id = 3 for update; |
| 4 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 5 |  | select * from test01.ttt where id = 2 for update; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

得到两条数据，不过`(0, 10)`范围的id都被锁了。


#### 会话2：

步骤3：

```sql
Empty set (0.00 sec)
```

步骤4：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤5：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例9

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id >2 for update; | |
| 3 |  | select * from test01.ttt where id = 3 for update; |
| 4 |  | select * from test01.ttt where id = 2 for update; |
| 5 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
  


#### 会话1：

步骤2：

```sql
Empty set (0.00 sec)
```
`(2, ∞)`范围的id都被锁了。


#### 会话2：

步骤3：

```sql
Empty set (0.00 sec)
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  2 | 1002 |
+----+------+
```

步骤5：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例10

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id = 3 for update; | |
| 3 |  | select * from test01.ttt where id = 3 for update; |
| 4 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 5 |  | select * from test01.ttt where id = 2 for update; |
  


#### 会话1：

步骤2：

```sql
Empty set (0.00 sec)
```


#### 会话2：

步骤3：

```sql
Empty set (0.00 sec)
```

步骤4：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  2 | 1002 |
+----+------+
```


### 示例11

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | update  test01.ttt set num=999 where id=1; | |
| 3 |  | update  test01.ttt set num=9999 where id=1; |
  


#### 会话1：

步骤2：

```sql
Query OK, 1 row affected (0.03 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```


#### 会话2：

步骤3：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例12

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where num=1001 for update; | |
| 3 |  | select * from test01.ttt where id = 2 for update; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```

因为where中没用索引进行查询，所以，锁表了。


#### 会话2：

步骤3：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例13  

验证可重复读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt; | |
| 3 |  | update  test01.ttt set num=9999 where id=1; |
| 4 | select * from test01.ttt; | |
| 5 |  | select * from test01.ttt; |
| 6 | select * from test01.ttt where id=1 for update; | |
| 7 | select * from test01.ttt; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4、7：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
+----+------+
```


#### select是快照读，读的数据是select在该事务中执行那一刻之前，数据库中已经提交的数据。除非本事务对数据有修改，否则，多次同样select的结果是一样的。对应的，select for update叫做当前读。


#### 会话2：

步骤3：

```sql
Query OK, 1 row affected (0.01 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
|  2 | 1002 |
+----+------+
```


### 示例14  

验证可重复读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | select * from test01.ttt; | |
| 2 | start transaction; | |
| 3 |  | update  test01.ttt set num=9999 where id=1; |
| 4 | select * from test01.ttt; | |
| 5 |  | select * from test01.ttt; |
  


#### 会话1：

步骤1：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
|  2 | 1002 |
+----+------+
```


#### 会话2：

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
|  2 | 1002 |
+----+------+
```


### 示例15

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 |  | select * from test01.ttt; |
| 3 | update  test01.ttt set num=9999 where id=1; | |
| 4 | select * from test01.ttt; | select * from test01.ttt; |
| 5 | commit; | |
| 6 |  | select * from test01.ttt; |
  


#### 会话1：

步骤3：

```sql
Query OK, 1 row affected (0.00 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
|  2 | 1002 |
+----+------+
```


#### 会话2：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤6：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 9999 |
|  2 | 1002 |
+----+------+
```


### 示例16  

验证快照读不会出现幻读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt; | |
| 3 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 4 | select * from test01.ttt; | |
| 5 |  | select * from test01.ttt; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```


#### 会话2：

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
|  3 | 1003 |
+----+------+
```


### 示例17  

验证快照读不会出现幻读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id=3; | |
| 3 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 4 | select * from test01.ttt where id=3; | |
| 5 |  | select * from test01.ttt; |
  


#### 会话1：

步骤2、4，无数据：

```sql
Empty set (0.00 sec)
```


#### 会话2：

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
|  3 | 1003 |
+----+------+
```


### 示例18  

验证快照读不会出现幻读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt; | |
| 3 |  | delete from test01.ttt where id=2; |
| 4 | select * from test01.ttt; | |
| 5 |  | select * from test01.ttt; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```


#### 会话2：

步骤3：

```sql
Query OK, 1 row affected (0.00 sec)
```

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```


### 示例19  

出现幻读。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt where id=3; | |
| 3 |  | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); |
| 4 | INSERT INTO test01.ttt(id, num) VALUES(3, 1003); | |
| 5 |  | select * from test01.ttt; |
| 6 |  | select * from test01.ttt where id=3 for update; |
  


#### 会话1：

步骤2，无数据：

```sql
Empty set (0.00 sec)
```

步骤4：

```sql
ERROR 1062 (23000): Duplicate entry '3' for key 'PRIMARY'
```

步骤4会比较困惑，「之前不是没数据吗，怎么又有数据了:cry:」。解决办法很简单，将步骤2改成select for update。


#### 会话2：

步骤5：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
|  3 | 1003 |
+----+------+
```

步骤6：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例20

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt; | |
| 3 |  | UPDATE test01.ttt set num=num+1 where id=2; |
| 4 | select * from test01.ttt; | select * from test01.ttt; |
| 5 | UPDATE test01.ttt set num=num+1000 where id=2; | |
| 6 | commit; | |
| 7 |  | select * from test01.ttt; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1002 |
+----+------+
```

步骤5：

```sql
Query OK, 1 row affected (0.01 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```


#### 会话2：

步骤3：

```sql
Query OK, 1 row affected (0.01 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```

步骤4：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1003 |
+----+------+
```

步骤6：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 1003 |
+----+------+
```

步骤8：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
|  2 | 2003 |
+----+------+
```


### 示例21

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.ttt; | |
| 3 |  | UPDATE test01.ttt set num=num+1 where id=2; |
| 4 | select * from test01.ttt; | select * from test01.ttt; |
| 5 | UPDATE test01.ttt set num=num+1000 where id=2; | |
| 6 |  | UPDATE test01.ttt set num=num+2 where id=2; |
| 7 | commit; | |
| 8 |  | select * from test01.ttt; |
  

会话2步骤6，锁超时。


### 示例22  

死锁示例。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | start transaction; |
| 2 | select * from test01.ttt where id=1 for update; | |
| 3 |  | select * from test01.ttt where id=2 for update; |
| 4 | select * from test01.ttt where id=2 for update; | |
| 5 |  | select * from test01.ttt where id=1 for update; |
  


#### 会话1：

步骤2：

```sql
+----+------+
| id | num  |
+----+------+
|  1 | 1001 |
+----+------+
```

步骤4：

先是等待。然后会话2步骤5执行后，输出：

```sql
+----+------+
| id | num  |
+----+------+
|  2 | 1002 |
+----+------+
```


#### 会话2：

步骤3：

```sql
+----+------+
| id | num  |
+----+------+
|  2 | 1002 |
+----+------+
```

步骤5：

```sql
ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction
```


#### 死锁检测出后，会话2事务会结束。


## 探索（二）  


### 数据准备  

```sql
CREATE TABLE `test01`.`sss` (
    `id` BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `num` BIGINT(20) DEFAULT 0 NOT NULL,
    `age` BIGINT(20) DEFAULT 0 NOT NULL,
    KEY `idx_num` (`num`)
)ENGINE =InnoDB DEFAULT CHARSET =utf8mb4;

INSERT INTO test01.sss(id, num, age) VALUES(1, 3001, 20);
INSERT INTO test01.sss(id, num, age) VALUES(2, 3002, 21);
INSERT INTO test01.sss(id, num, age) VALUES(3, 3002, 22);
INSERT INTO test01.sss(id, num, age) VALUES(10, 4000, 23);

mysql> select * from test01.sss;
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
+----+------+-----+
```

表`sss`中id是自增主键，num列增加了非唯一索引。


### 示例1

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.sss where id=3  for update; | |
| 3 |  | select * from test01.sss where num=3002 and age=22 for update; |
| 4 |  | select * from test01.sss where num=3003 for update; |
| 5 |  | INSERT INTO test01.sss(id, num, age) VALUES(4, 3003, 22); |
| 6 |  | INSERT INTO test01.sss(id, num, age) VALUES(5, 3002, 22); |
  


#### 会话1：

步骤2：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  3 | 3002 |  22 |
+----+------+-----+
```


#### 会话2：

步骤3：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

基于num查找数据，先从num对应的辅助索引查找id是3，然后去聚集索引找id为3的数据内容。但这条数据被锁上了。所以，所等待超时。

步骤4：

```sql
Empty set (0.00 sec)
```

步骤5：

```sql
Query OK, 1 row affected (0.01 sec)
```

步骤6：

```sql
Query OK, 1 row affected (0.01 sec)
```


### 示例2  

验证锁行是基于索引的。

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.sss where num=3002 and age=21 for update; | |
| 3 |  | select * from test01.sss where num=3002 and age=22 for update; |
| 4 |  | select * from test01.sss where num=3003 for update; |
| 5 |  | INSERT INTO test01.sss(id, num, age) VALUES(4, 3003, 22); |
| 6 |  | select * from test01.sss where num=4000 for update; |
  


#### 会话1：

步骤2：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  2 | 3002 |  21 |
+----+------+-----+
```

有两条数据的num都是3002，其中一条的age是21。num上有`非唯一索引`，这里锁数据会锁住`3002`、`(3001, 3002)`、`(3002, 4000)`这三个位置/范围。防止其他事务插入数据，导致新插入num为3002的数据，从而防止幻读。

为什么这么锁？想想B+树，想想非唯一索引。这种锁能解决幻读问题。

可不可以只锁3002？肯定有技术方案可以做到，但是MySQL没选那个方案。


#### 会话2：

步骤3：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

因为被会话1锁了，所以这里锁超时。

步骤4：

```sql
Empty set (0.00 sec)
```

步骤5：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤6：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
| 10 | 4000 |  23 |
+----+------+-----+
```


### 示例3

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.sss; | |
| 3 | select * from test01.sss where id =10  for update; | |
| 4 |  | update  test01.sss set age=30 where num=4000; |
  


#### 会话1：

步骤2：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
+----+------+-----+
```

步骤3：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
| 10 | 4000 |  23 |
+----+------+-----+
```


#### 会话2：

步骤4：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例4

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | INSERT INTO test01.sss(id, num, age) VALUES(16, 4800, 23); | |
| 2 | start transaction; | |
| 3 | select * from test01.sss; | |
| 4 | select * from test01.sss where num = 4300 for update; | |
| 5 |  | INSERT INTO test01.sss(id, num, age) VALUES(22, 4300, 22); |
| 6 |  | INSERT INTO test01.sss(id, num, age) VALUES(20, 4200, 22); |
| 7 |  | INSERT INTO test01.sss(id, num, age) VALUES(21, 4500, 22); |
| 8 |  | INSERT INTO test01.sss(id, num, age) VALUES(22, 5000, 22); |
  


#### 会话1：

步骤3：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
| 16 | 4800 |  23 |
+----+------+-----+
```


#### 步骤4：

```sql
Empty set (0.00 sec)
```


#### 会话2：

步骤5、6、7：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤6、7：

```sql
Query OK, 1 row affected (0.01 sec)
```


### 示例5

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | INSERT INTO test01.sss(id, num, age) VALUES(16, 4800, 23); | |
| 2 | start transaction; | |
| 3 | select * from test01.sss; | |
| 4 | select * from test01.sss where num = 4800 for update; | |
| 5 |  | INSERT INTO test01.sss(id, num, age) VALUES(20, 4200, 22); |
| 6 |  | INSERT INTO test01.sss(id, num, age) VALUES(21, 5000, 22); |
| 7 |  | INSERT INTO test01.sss(id, num, age) VALUES(22, 4800, 22); |
  


#### 会话1：

步骤3：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
| 16 | 4800 |  23 |
+----+------+-----+
```

步骤4：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
| 16 | 4800 |  23 |
+----+------+-----+
```


#### 会话2：

步骤5、6、7：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```


### 示例6

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.sss; | |
| 3 | INSERT INTO test01.sss(id, num, age) VALUES(16, 4800, 23); | |
| 4 | select * from test01.sss | |
| 5 |  | INSERT INTO test01.sss(id, num, age) VALUES(20, 4200, 22); |
| 6 |  | INSERT INTO test01.sss(id, num, age) VALUES(21, 5000, 22); |
| 7 |  | INSERT INTO test01.sss(id, num, age) VALUES(22, 4800, 22); |
  


#### 会话1：

步骤2：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
+----+------+-----+
```

步骤4：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3002 |  22 |
| 10 | 4000 |  23 |
| 16 | 4800 |  23 |
+----+------+-----+
```


#### 会话2：

步骤5、6、7：

```sql
Query OK, 1 row affected (0.01 sec)
```


#### 看起来insert只对主键上锁。或者是对唯一索引上锁。


## 探索（三）  


### 数据准备  

```sql
CREATE TABLE `test01`.`kkk` (
    `id` BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `num` BIGINT(20) DEFAULT 0 NOT NULL,
    `age` BIGINT(20) DEFAULT 0 NOT NULL,
    UNIQUE INDEX `uk_num` (`num`),
    KEY `idx_age` (`age`)
)ENGINE =InnoDB DEFAULT CHARSET =utf8mb4;

INSERT INTO test01.kkk(id, num, age) VALUES(1, 3001, 20);
INSERT INTO test01.kkk(id, num, age) VALUES(2, 3002, 21);
INSERT INTO test01.kkk(id, num, age) VALUES(3, 3003, 22);
INSERT INTO test01.kkk(id, num, age) VALUES(10, 4000, 23);
```

num具有了唯一索引。age具有非唯一索引。


### 示例1

| 步骤 | 会话1 | 会话2 |
| - | - | - |
| 1 | start transaction; | |
| 2 | select * from test01.kkk; | |
| 3 | INSERT INTO test01.kkk(id, num, age) VALUES(16, 4800, 23); | |
| 4 |  | INSERT INTO test01.kkk(id, num, age) VALUES(16, 4801, 24); |
| 5 |  | INSERT INTO test01.kkk(id, num, age) VALUES(17, 4800, 25); |
| 6 |  | INSERT INTO test01.kkk(id, num, age) VALUES(18,  4803, 23); |
| 7 |  | INSERT INTO test01.sss(id, num, age) VALUES(19, 4804, 27); |
  


#### 会话1

步骤2：

```sql
+----+------+-----+
| id | num  | age |
+----+------+-----+
|  1 | 3001 |  20 |
|  2 | 3002 |  21 |
|  3 | 3003 |  22 |
| 10 | 4000 |  23 |
+----+------+-----+
```

步骤3：

```sql
Query OK, 1 row affected (0.00 sec)
```


#### 会话2：

步骤4：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤5：

```sql
ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

步骤6：

```sql
Query OK, 1 row affected (0.01 sec)
```

步骤7：

```sql
Query OK, 1 row affected (0.01 sec)
```

id和num都是唯一索引，所以id和num是一一对应的关系。插入数据时，对id、num加了锁，所以会话2步骤4、5锁超时 。


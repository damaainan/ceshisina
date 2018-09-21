## MySQL数据库InnoDB引擎行级锁锁定范围详解

来源：[https://segmentfault.com/a/1190000013307132](https://segmentfault.com/a/1190000013307132)


## 前言

每个数据库几乎都会实现自己的锁机制，锁机制是数据库区别于文件系统的主要标志之一，用于管理对共享资源的并发访问。

Mysql数据库InnoDB引擎支持行级锁，也就是说我们可以对表中某些行数据执行锁定操作，锁定操作的影响是：如果一个事物对表中某行执行了锁定操作，而另一个事务也需要对同样的行执行锁定操作，这样第二个事务的锁定操作有可能被阻塞，一旦被阻塞第二个事务只能等到第一个事务执行完毕（提交或回滚）或超时。

本文主要介绍InnoDB中的行锁相关概念，重点介绍行锁的锁定范围：


* 什么样的SQL语句会加锁？
* 加什么样的锁？
* **`加锁语句会锁定哪些行`** ？


## 背景知识

上面我们简单的介绍了InnoDB的行级锁，为了理解后面的验证部分，需要补充一下背景知识。如果对相应知识非常了解，可以直接跳转到验证部分内容。
## 1. InnoDB锁的类型

InnoDB引擎使用了七种类型的锁，他们分别是：


* **`共享排他锁（Shared and Exclusive Locks）`** 
* 意向锁（Intention Locks）
* **`记录锁（Record Locks）`** 
* **`间隙锁（Gap Locks）`** 
* **`Next-Key Locks`** 
* 插入意图锁（Insert Intention Locks）
* 自增锁（AUTO-INC Locks）


本文主要涉及Shared and Exclusive Locks，Record Locks，Gap Locks，Next-Key Locks这几种锁，其他类型锁如果大家感兴趣可以自己深入了解，在此不在详述。
### 1.1 Shared and Exclusive Locks

共享锁（S锁）和排他锁（X锁）的概念在许多编程语言中都出现过。先来描述一下这两种锁在MySQL中的影响结果：


* 如果一个事务对某一行数据加了S锁，另一个事务还可以对相应的行加S锁，但是不能对相应的行加X锁。
* 如果一个事务对某一行数据加了X锁，另一个事务既不能对相应的行加S锁也不能加X锁。


用一张经典的矩阵表格继续说明共享锁和排他锁的互斥关系：

| -- | S | X |
| - | - | - |
| **`S`** | 0 | 1 |
| **`X`** | 1 | 1 |


图中S表示共享锁X表示独占锁，0表示锁兼容1表示锁冲突，兼容不被阻塞，冲突被阻塞。由表可知一旦一个事务加了排他锁，其他个事务加任何锁都需要等待。多个共享锁不会相互阻塞。
### 1.2 Record Locks、Gap Locks、Next-Key Locks

这三种类型的锁都描述了锁定的范围，故放在一起说明。

以下定义摘自MySQL官方文档


* 记录锁（Record Locks）:记录锁锁定索引中一条记录。
* 间隙锁（Gap Locks）:间隙锁要么锁住索引记录中间的值，要么锁住第一个索引记录前面的值或者最后一个索引记录后面的值。
* Next-Key Locks:Next-Key锁是索引记录上的记录锁和在索引记录之前的间隙锁的组合。

定义中都提到了索引记录（index record）。为什么？行锁和索引有什么关系呢？其实，InnoDB是通过搜索或者扫描表中索引来完成加锁操作，InnoDB会为他遇到的每一个索引数据加上共享锁或排他锁。所以我们可以称行级锁（row-level locks）为索引记录锁（index-record locks），因为行级锁是添加到行对应的索引上的。

三种类型锁的锁定范围不同，且逐渐扩大。我们来举一个例子来简要说明各种锁的锁定范围，假设表t中索引列有3、5、8、9四个数字值，根据官方文档的确定三种锁的锁定范围如下：


* 记录锁的锁定范围是单独的索引记录，就是3、5、8、9这四行数据。
* 间隙锁的锁定为行中间隙，用集合表示为(-∞,3)、(3,5)、(5,8)、(8,9)、(9,+∞)。
* Next-Key锁是有索引记录锁加上索引记录锁之前的间隙锁组合而成，用集合的方式表示为(-∞,3]、(3,5]、(5,8]、(8,9]、(9,+∞)。


最后对于间隙锁还需要补充三点：


* 间隙锁阻止其他事务对间隙数据的并发插入，这样可有有效的解决幻读问题(Phantom Problem)。正因为如此， **`并不是所有事务隔离级别都使用间隙锁`** ，MySQL InnoDB引擎只有在Repeatable Read（默认）隔离级别才使用间隙锁。
* 间隙锁的作用只是用来阻止其他事务在间隙中插入数据，他不会阻止其他事务拥有同样的的间隙锁。这就意味着， **`除了insert语句，允许其他SQL语句可以对同样的行加间隙锁而不会被阻塞`** 。
* **`对于唯一索引的加锁行为，间隙锁就会失效，此时只有记录锁起作用`** 。


## 2. 加锁语句

前面我们已经介绍了InnoDB的是在SQL语句的执行过程中通过扫描索引记录的方式来实现加锁行为的。那哪些些语句会加锁？加什么样的锁？接下来我们逐一描述：


* `select ... from语句`：InnoDB引擎采用多版本并发控制（MVCC）的方式实现了非阻塞读，所以对于普通的select读语句，InnoDB并不会加锁【注1】。
* `select ... from lock in share mode语句`：这条语句和普通select语句的区别就是后面加了lock in share mode，通过字面意思我们可以猜到这是一条加锁的读语句，并且锁类型为共享锁（读锁）。InnoDB会对搜索的所有索引记录加next-key锁，但是如果扫描的唯一索引的唯一行，next-key降级为索引记录锁。
* `select ... from for update语句`：和上面的语句一样，这条语句加的是排他锁（写锁）。InnoDB会对搜索的所有索引记录加next-key锁，但是如果扫描唯一索引的唯一行，next-key降级为索引记录锁。
* `update ... where ...语句`：。InnoDB会对搜索的所有索引记录加next-key锁，但是如果扫描唯一索引的唯一行，next-key降级为索引记录锁。【注2】
* `delete ... where ...语句`：。InnoDB会对搜索的所有索引记录加next-key锁，但是如果扫描唯一索引的唯一行，next-key降级为索引记录锁。
* `insert语句`：InnoDB只会在将要插入的那一行上设置一个排他的索引记录锁。


最后补充两点：


* 如果一个查询使用了辅助索引并且在索引记录加上了排他锁，InnoDB会在相对应的聚合索引记录上加锁。
* 如果你的SQL语句无法使用索引，这样MySQL必须扫描整个表以处理该语句，导致的结果就是表的每一行都会被锁定，并且阻止其他用户对该表的所有插入。


## SQL语句验证

闲言少叙，接下来我们进入本文重点SQL语句验证部分。
## 1.测试环境

数据库：MySQL 5.6.35  
事务隔离级别：Repeatable read  
数据库访问终端：mysql client
## 2.验证场景
### 2.1 场景一

建表：

```sql
CREATE TABLE `user` (
 `id` int(11) NOT NULL,
 `name` varchar(8) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

插入数据：

```sql
INSERT INTO `user` (`id`, `name`) VALUES ('1', 'a');
INSERT INTO `user` (`id`, `name`) VALUES ('3', 'c');
INSERT INTO `user` (`id`, `name`) VALUES ('5', 'e');
INSERT INTO `user` (`id`, `name`) VALUES ('7', 'g');
INSERT INTO `user` (`id`, `name`) VALUES ('9', 'i');
```

首先我们执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name='e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`) VALUES ( **`10`** , #{name}); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| a | 不阻塞 |
| b | 不阻塞 |
| **`d`** | **`阻塞`** |
| **`e`** | **`阻塞`** |
| **`f`** | **`阻塞`** |
| h | 不阻塞 |
| i | 不阻塞 |


观察结果，我们发现SQL语句
`SELECT * FROM user where name='e' for update`
 一共锁住索引name中三行记录，(c,e]区间应该是next-key锁而(e,h)区间是索引记录e后面的间隙。

接下来我们确定next-key锁中哪部分是索引记录锁哪部分是间隙锁。

执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name='e' for update; | -- |
| 3 | -- | SELECT * FROM user where name=#{name} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| d | 不阻塞 |
| **`e`** | **`阻塞`** |
| f | 不阻塞 |


因为间隙锁只会阻止insert语句，所以同样的索引数据，`insert`语句阻塞而`select for update`语句不阻塞的就是间隙锁，如果两条语句都阻塞就是索引记录锁。

观察执行结果可知，d和f为间隙锁，e为索引记录锁。

结论：通过两条SQL，我们确定了对于辅助索引name在查询条件为` where name='e'  `时的加锁范围为（c,e],(e,g),其中：


* 对SQL语句扫描的索引记录e加索引记录锁[e]。
* 锁定了e前面的间隙，c到e之间的数据(c,e)加了间隙锁
* 前两个构成了next-key锁(c,e]。
* 值得注意的是还锁定了e后面的间隙(e,g)。


说的这里细心的读者可能已经发现我们的测试数据中没有间隙的边界数据c和g。接下来我们就对间隙边界值进行测试。

执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name='e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`) VALUES (#{id}, #{name}); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id，name的值，观察结果：

| id的值 | name=c | 执行结果 | id的值 | name=g | 执行结果 |
| - | - | - | - | - | - |
| -- | -- | -- | **`-3`** | g | **`组塞`** |
| -- | -- | -- | **`-2`** | g | **`阻塞`** |
| -1 | c | 不阻塞 | **`-1`** | g | **`阻塞`** |
| 1 | c | 不阻塞 | 1 | g | 不阻塞 |
| 2 | c | 不阻塞 | **`2`** | g | **`阻塞`** |
| 3 | c | 不阻塞 | 3 | g | 不阻塞 |
| **`4`** | c | **`阻塞`** | **`4`** | g | **`阻塞`** |
| **`5`** | c | **`阻塞`** | **`5`** | g | **`阻塞`** |
| **`6`** | c | **`阻塞`** | **`6`** | g | **`阻塞`** |
| 7 | c | 不阻塞 | 7 | g | 不阻塞 |
| **`8`** | c | **`阻塞`** | 8 | g | 不阻塞 |
| 9 | c | 不阻塞 | 9 | g | 不阻塞 |
| **`10`** | c | **`阻塞`** | 10 | g | 不阻塞 |
| **`11`** | c | **`阻塞`** | - | - | - |
| **`12`** | c | **`阻塞`** | - | - | - |


通过观察以上执行结果，我们发现，name等于c和e时`insert`语句的结果随着id值得不同一会儿锁定，一会儿不锁定。那一定是id列加了锁才会造成这样的结果。

如果先不看`id=5`这一行数据的结果，我们发现一个规律：


* 当`name=c`时，`name=c`对应的`id=3`的id聚合索引数据记录之后的间隙(3,5)，(5,7)，(7,9)，(9,∞)都被加上了锁。
* 当`name=e`时，`name=e`对应的`id=7`的id聚合索引数据记录之前的间隙(5,7)，(3,5)，(1,3)，(-∞,1)都被加上了锁。
* 我们可用`select * from user where id = x for update;`语句判断出以上间隙上加的锁都为间隙锁。


接下来我们解释一下`id=5`的锁定情况

执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name='e' for update; | -- |
| 3 | -- | SELECT * FROM user where id=#{id} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id的值，观察结果：

| id的值 | 执行结果 |
| - | - |
| 3 | 不阻塞 |
| 4 | 不阻塞 |
| **`5`** | **`阻塞`** |
| 6 | 不阻塞 |
| 7 | 不阻塞 |


通过观察执行结果可知，`id=5`的聚合索引记录上添加了索引记录锁。根据MySQL官方文档描述，InnoDB引擎在对辅助索引加锁的时候，也会对辅助索引所在行所对应的聚合索引（主键）加锁。而主键是唯一索引，在对唯一索引加锁时，间隙锁失效，只使用索引记录锁。所以`SELECT * FROM user where name='e' for update;`不仅对辅助索引`name=e`列加上了next-key锁，还对对应的聚合索引`id=5`列加上了索引记录锁。
 **`最终结论：`**   
 对于`SELECT * FROM user where name='e' for update;`一共有三种锁定行为:


* **`对SQL语句扫描过的辅助索引记录行加上next-key锁（注意也锁住记录行之后的间隙）。`** 
* **`对辅助索引对应的聚合索引加上索引记录锁。`** 
* **`当辅助索引为间隙锁“最小”和“最大”值时，对聚合索引相应的行加间隙锁。“最小”锁定对应聚合索引之后的行间隙。“最大”值锁定对应聚合索引之前的行间隙。`** 


上面我们将对辅助索引加锁的情况介绍完了，接下来我们测试一下对聚合索引和唯一索引加锁。
### 2.2 场景二

建表：

```sql
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

注意与场景一表user不同的是name列为唯一索引。

插入数据：

```sql
INSERT INTO `user` (`id`, `name`) VALUES ('1', 'a');
INSERT INTO `user` (`id`, `name`) VALUES ('3', 'c');
INSERT INTO `user` (`id`, `name`) VALUES ('5', 'e');
INSERT INTO `user` (`id`, `name`) VALUES ('7', 'g');
INSERT INTO `user` (`id`, `name`) VALUES ('9', 'i');
```

首先我们执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name='e' for update; |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`) VALUES ( **`10`** , #{name}); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| a | 不阻塞 |
| b | 不阻塞 |
| c | 不阻塞 |
| d | 不阻塞 |
| **`e`** | **`阻塞`** |
| f | 不阻塞 |
| g | 不阻塞 |
| h | 不阻塞 |
| i | 不阻塞 |


由测试结果可知，只有`name='e'`这行数据被锁定。

通过SQL语句我们验证了，对于唯一索引列加锁，间隙锁失效，
### 2.3 场景三

场景一和场景二都是在查询条件等于的情况下做出的范围判断，现在我们尝试一下其他查询条件，看看结论是否一致。

借用场景一的表和数据。

建表：

```sql
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

插入数据：

```sql
INSERT INTO `user` (`id`, `name`) VALUES ('1', 'a');
INSERT INTO `user` (`id`, `name`) VALUES ('3', 'c');
INSERT INTO `user` (`id`, `name`) VALUES ('5', 'e');
INSERT INTO `user` (`id`, `name`) VALUES ('7', 'g');
INSERT INTO `user` (`id`, `name`) VALUES ('9', 'i');
```

执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`) VALUES ('10', #{name}); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| **`a`** | **`阻塞`** |
| **`b`** | **`阻塞`** |
| **`c`** | **`阻塞`** |
| **`d`** | **`阻塞`** |
| **`e`** | **`阻塞`** |
| **`f`** | **`阻塞`** |
| **`g`** | **`阻塞`** |
| **`h`** | **`阻塞`** |
| **`i`** | **`阻塞`** |


这个结果是不是和你想象的不太一样，这个结果表明`where name>'e'`这个查询条件并不是锁住`'e'`列之后的数据，而锁住了所有`name`列中所有数据和间隙。这是为什么呢？

我们执行以下的SQL语句执行计划：

```sql
 explain select * from user where name>'e' for update;
```

执行结果：

```sql
+----+-------------+-------+-------+---------------+------------+---------+------+------+--------------------------+
| id | select_type | table | type  | possible_keys | key        | key_len | ref  | rows | Extra                    |
+----+-------------+-------+-------+---------------+------------+---------+------+------+--------------------------+
|  1 | SIMPLE      | user  | index | index_name    | index_name | 26      | NULL |    5 | Using where; Using index |
+----+-------------+-------+-------+---------------+------------+---------+------+------+--------------------------+
1 row in set (0.00 sec)
```
 如果你的结果与上面不同先执行一下`OPTIMIZE TABLE user;`再执行以上语句。 

通过观察SQL语句的执行计划我们发现，语句使用了`name`列索引，且`rows`参数等于5，user表中一共也只有5行数据。SQL语句的执行过程中一共扫描了`name`索引记录5行数据且对这5行数据都加上了next-key锁，符合我们上面的执行结果。

接下来我们再制造一组数据。  
建表：

```sql
CREATE TABLE `user` (
 `id` int(11) NOT NULL,
 `name` varchar(8) NOT NULL,
 `age` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `index_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

插入数据：

```sql
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('1', 'a','15');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('3', 'c','20');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('5', 'e','16');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('7', 'g','19');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('9', 'i','34');
```
 这张表和前表的区别是多了一列非索引列`age`。 

我们再执行一下同样的SQL语句执行计划：

```sql
 explain select * from user where name>'e' for update;
```

执行结果：

```sql
+----+-------------+-------+-------+---------------+------------+---------+------+------+-----------------------+
| id | select_type | table | type  | possible_keys | key        | key_len | ref  | rows | Extra                 |
+----+-------------+-------+-------+---------------+------------+---------+------+------+-----------------------+
|  1 | SIMPLE      | user  | range | index_name    | index_name | 26      | NULL |    2 | Using index condition |
+----+-------------+-------+-------+---------------+------------+---------+------+------+-----------------------+
1 row in set (0.00 sec)
```

是不是和第一次执行结果不同了，`rows`参数等于2，说明扫描了两行记录，结合SQL语句`select * from user where name>'e' for update;`执行后返回结果我们判断这两行记录应该为g和i。

因为`select * from user where name>'e' for update;`语句扫描了两行索引记录分别是g和i，所以我们将g和i的锁定范围叠就可以得到`where name>'e'`的锁定范围：


* 索引记录g在`name`列锁定范围为(e,g],(g,i)。索引记录i的在`name`列锁定范围为(g,i],(i,+∞)。两者叠加后锁定范围为(e,g],(g,i],(i,+∞)。其中g,i为索引记录锁。
* g和i对应`id`列中的7和9加索引记录锁。
* 当`name`列的值为锁定范围上边界e时，还会在e所对应的`id`列值为5之后的所有值之间加上间隙锁，范围为(5,7),(7,9),(9,+∞)。下边界为+∞无需考虑。


接下来我们逐一测试：

首先测试验证了next-key锁范围，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`, `age`) VALUES ('10', #{name},'18'); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| a | 不阻塞 |
| b | 不阻塞 |
| c | 不阻塞 |
| d | 不阻塞 |
| **`f`** | **`阻塞`** |
| **`g`** | **`阻塞`** |
| **`h`** | **`阻塞`** |
| **`i`** | **`阻塞`** |
| **`j`** | **`阻塞`** |
| **`k`** | **`阻塞`** |


下面验证next-key锁中哪部分是间隙锁，哪部分是索引记录锁，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | SELECT * FROM user where name=#{name} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| e | 不阻塞 |
| f | 不阻塞 |
| **`g`** | **`阻塞`** |
| h | 不阻塞 |
| **`i`** | **`阻塞`** |
| j | 不阻塞 |


接下来验证对`id`列加索引记录锁，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | SELECT * FROM user where id=#{id} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id的值，观察结果：

| id的值 | 执行结果 |
| - | - |
| 5 | 不阻塞 |
| 6 | 不阻塞 |
| **`7`** | **`阻塞`** |
| 8 | 不阻塞 |
| **`9`** | **`阻塞`** |
| 10 | 不阻塞 |


最后我们验证`name`列的值为边界数据e时，`id`列间隙锁的范围，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`,`age`) VALUES (#{id}, 'e','18'); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id的值，观察结果：

| id的值 | 执行结果 |
| - | - |
| -1 | 不阻塞 |
| 1 | 不阻塞 |
| 2 | 不阻塞 |
| 3 | 不阻塞 |
| 4 | 不阻塞 |
| 5 | 不阻塞 |
| **`6`** | **`阻塞`** |
| **`7`** | **`阻塞`** |
| **`8`** | **`阻塞`** |
| **`9`** | **`阻塞`** |
| **`10`** | **`阻塞`** |
| **`11`** | **`阻塞`** |
| **`12`** | **`阻塞`** |

 注意7和9是索引记录锁记录锁 。

观察上面的所有SQL语句执行结果，可以验证`select * from user where name>'e' for update`的锁定范围为此语句扫描`name`列索引记录g和i的锁定范围的叠加组合。
### 2.4 场景四

我们通过场景三验证了普通索引的范围查询语句加锁范围，现在我们来验证一下唯一索引的范围查询情况下的加锁范围。有了场景三的铺垫我们直接跳过扫描全部索引的情况，创建可以扫描范围记录的表结构并插入相应数据测试。

建表：

```sql
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(8) NOT NULL,
  `age` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

```

插入数据：

```sql
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('1', 'a','15');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('3', 'c','20');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('5', 'e','16');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('7', 'g','19');
INSERT INTO `user` (`id`, `name`,`age`) VALUES ('9', 'i','34');
```
 和场景三表唯一不同是`name`列为唯一索引。 

SQL语句`select * from user where name>'e'`扫描`name`列两条索引记录g和i。如果需要只对g和i这两条记录加上记录锁无法避免幻读的发生， **`索引锁定范围应该还是两条数据next-key锁锁的组合：(e,g],(g,i],(i,+∞)。其中g,i为索引记录锁`** 。

我们通过SQL验证我们的结论，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`, `age`) VALUES ('10', #{name},'18'); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| a | 不阻塞 |
| b | 不阻塞 |
| c | 不阻塞 |
| d | 不阻塞 |
| **`f`** | **`阻塞`** |
| **`g`** | **`阻塞`** |
| **`h`** | **`阻塞`** |
| **`i`** | **`阻塞`** |
| **`j`** | **`阻塞`** |
| **`k`** | **`阻塞`** |


下面验证next-key锁中哪部分是间隙锁，哪部分是索引记录锁，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | SELECT * FROM user where name=#{name} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中name的值，观察结果：

| name的值 | 执行结果 |
| - | - |
| e | 不阻塞 |
| f | 不阻塞 |
| **`g`** | **`阻塞`** |
| h | 不阻塞 |
| **`i`** | **`阻塞`** |
| j | 不阻塞 |


通过上面两条SQL语句的验证结果，我们证明了我们的g和i的锁定范围趋势为两者next-key叠加组合。

接下来我们验证一下对辅助索引加锁后对聚合索引的锁转移，执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | SELECT * FROM user where id=#{id} for update; |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id的值，观察结果：

| id的值 | 执行结果 |
| - | - |
| 5 | 不阻塞 |
| 6 | 不阻塞 |
| **`7`** | **`阻塞`** |
| 8 | 不阻塞 |
| **`9`** | **`阻塞`** |
| 10 | 不阻塞 |


由结果可知对辅助索引`name`中的g和i列对应的聚合索引`id`列中的7和9加上了索引记录锁。

到目前为止所有实验结果和场景三完全一样，这也很好理解，毕竟场景四和场景三只是辅助索引`name`的索引类型不同，一个是唯一索引，一个是普通索引。

最后验证意向，next-key锁边界数据e，看看结论时候和场景三相同。

执行SQL语句的模板：

| 步骤 | client 1 | client 2 |
| - | - | - |
| 1 | begin; | -- |
| 2 | SELECT * FROM user where name>'e' for update; | -- |
| 3 | -- | begin; |
| 4 | -- | INSERT INTO `user` (`id`, `name`,`age`) VALUES (#{id}, 'e','18'); |
| 5 | rollback; | -- |
| 6 | -- | rollback; |


替换步骤5中id的值，观察结果：

| id的值 | 执行结果 |
| - | - |
| -1 | 不阻塞 |
| 1 | 不阻塞 |
| 2 | 不阻塞 |
| 3 | 不阻塞 |
| 4 | 不阻塞 |
| 5 | 不阻塞 |
| 6 | 不阻塞 |
| **`7`** | **`阻塞`** |
| 8 | 不阻塞 |
| **`9`** | **`阻塞`** |
| 10 | 不阻塞 |
| 11 | 不阻塞 |
| 12 | 不阻塞 |

 注意7和9是索引记录锁记录锁 。

通过结果可知，当`name`列为索引记录上边界e时，并没有对id有加锁行为，这点与场景三不同。

对于唯一索引的范围查询和普通索引的范围查询类似，唯一不同的是当辅助索引等于上下范围的边界值是不会对主键加上间隙锁。
 **`唯一索引范围查询加锁范围：`** 


* **`对于扫描的辅助索引记录的锁定范围就是多个索引记录next-key范围的叠加组合。`** 
* **`对于聚合索引（主键）的锁定范围，会对多个辅助索引对应的聚合索引列加索引记录锁。`** 


## 结论

InnoDB引擎会对他扫描过的索引记录加上相应的锁，通过“场景一”我们已经明确了扫描一条普通索引记录的锁定范围，通过“场景三”我们可以推断任意多个扫描普通索引索引记录的锁定范围。通过“场景二”我们确定了扫描一条唯一索引记录（或主键）的锁定范围。通过“场景四”我们可以推断任意多个扫描索唯一引记录（或主键）的锁定范围。在实际的应用可以灵活使用，判断两条SQL语句是否相互锁定。这里还需要注意的是对于索引的查询条件，不能想当然的理解，他往往不是我们理解的样子，需要结合执行计划判断索引最终扫描的记录数，否则会对加锁范围理解产生偏差。

-----

## 备注

注1：在事务隔离级别为SERIALIZABLE时，普通的select语句也会对语句执行过程中扫描过的索引加上next-key锁。如果语句扫描的是唯一索引，那就将next-key锁降级为索引记录锁了。  
注2：当更新语句修改聚合索引（主键）记录时，会对受影响的辅助索引执行隐性的加锁操作。当插入新的辅助索引记录之前执行重复检查扫描时和当插入新的辅助索引记录时，更新操作还对受影响的辅助索引记录添加共享锁。

-----

-----

##### 参考：

[https://dev.mysql.com/doc/ref...][0]  
[https://dev.mysql.com/doc/ref...][1]

[0]: https://dev.mysql.com/doc/refman/5.6/en/innodb-locking.html
[1]: https://dev.mysql.com/doc/refman/5.6/en/innodb-locks-set.html
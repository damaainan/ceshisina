## mysql(InnoDB)事务隔离级别(READ UNCOMMITTED) 与 锁

来源：[https://segmentfault.com/a/1190000012654564](https://segmentfault.com/a/1190000012654564)


## 前言

先针对自己以前错误的思维做个记录, 大家可以直接跳过


* 由于以前看到很多资料在谈到并发控制的时候, 都会提到用`锁`来控制并发, MySQL也不例外, 也有很多和锁相关的概念(留到后面会单独整理一篇笔记出来), 所以一提到高并发产生的问题, 我会不自觉地提出一个疑问:`现在并发出问题了, 那怎么用锁的相关知识来解决?`;
* 而且近期一段时间也一直在看很多有关MySQL锁相关的资料,书籍, 于是乎`死锁`,`锁冲突`,`行锁`,`表锁`,`读锁`,`写锁`,`乐观锁`,`悲观锁`......等等 N多锁相关的名词(后面的笔记会把所有自己遇到的, 全部整理并进行分析), 大量的篇幅, 高深晦涩的描述, 直接导致我意识里认为`嗯, 锁真tm高大上, 真tm高端, 肯定tm就是它了`;
* 于是就进入了思想误区, 认为在解决`脏读`,`不可重复读`,`幻读`的资料中, 应该大篇幅的描述如何用锁相关的知识来解决这些问题, 然而略失落了, 资料倒是提了点儿锁的知识, 但更多的是用事务的哪个隔离级别来解决这些问题,`锁`哪儿去了?
* 尤其是在分析`脏读`,`不可重复读`,`幻读`这几个问题的时候, 一上去就全乱了, 比如`脏读`, 如果总是以MySQL锁的相关知识作为前提来分析, **`就会陷入误区`**  '事务A读取数据的时候肯定会加S锁的, 事务B自然是无法对未完成的事务A中的数据进行修改的, 我Ca, **`这种脏读的场景根本就不成立嘛!`** ', 那为什么不提锁, 而是用隔离级别来解决。
......
......
* 晕了几天之后,终于稍微醒了点......

[参考美团技术博客][12]

![][0] 
* 显然, **`事务隔离级别的核心就是锁, 各隔离级别使用了不同的加锁策略`** ，在分析之前的几个高并发事务问题的时候,`隔离级别(锁)`自然是不能作为前置知识点的, 而是最终问题的解决方案!


## "READ UNCOMMITTED与锁"的困惑

(未提交读)


* 在READ UNCOMMITTED级别, 事务中的修改, 即使还没有提交, 对其他事务也都是可见的; 也就是说事务可以读取未提交的数据, 这也就造成了`脏读(Dirty Read)`的出现。
* 这个级别会导致很多问题, 而且从性能上来说, READ COMMITTED 并不会比其他的级别好太多, 却缺乏其他级别的很多好处, 在实际应用中一般很少使用。
* 虽然很少使用, 但还是有必要了解一下, **`它这个隔离级别究竟是怎么隔离的, 竟然还能容许很多问题的存在？`**  (老兄亏你还算个隔离级别, 怎么办事儿的...) 网上相关资料五花八门, 下面列几个出来(希望你看完不要激动):


* [美团技术博客][13]: 

![][1] 
* [segmentfault一篇文章][14] 

![][2] 
* [CSDN一篇文章][15]

![][3] 
* [CSDN一篇文章][16]

![][4] 



* 说实话, 资料查到这份儿上, 我已经快崩溃了, 就`READ UNCOMMITTED`这个隔离级别:


* 有说读写都不加锁的
* 有说'修改完数据立即加S锁的, 修改时撤掉S锁'
* 有说'写加S锁,事务结束释放'的
* 有说'写加X锁,事务结束释放'的



* **`行啦, 不查了, 再查就崩溃了, 自己去测一下吧!!!`** 


* 本次测试是使用MAMP PRO中mysql5.6版本
* 先准备一张测试表`test_transaction`:

```sql
DROP TABLE IF EXISTS `test_transaction`;
CREATE TABLE `test_transaction` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_name` char(20) NOT NULL COMMENT '姓名',
  `age` tinyint(3) NOT NULL COMMENT '年龄',
  `gender` tinyint(1) NOT NULL COMMENT '1:男, 2:女',
  `desctiption` text NOT NULL COMMENT '简介',
  PRIMARY KEY (`id`),
  KEY `name_age_gender_index` (`user_name`,`age`,`gender`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `test_transaction` VALUES (1, '金刚狼', 127, 1, '我有一双铁爪');
INSERT INTO `test_transaction` VALUES (2, '钢铁侠', 120, 1, '我有一身铁甲');
INSERT INTO `test_transaction` VALUES (3, '绿巨人', 0, 2, '我有一身肉');
```


* 如下:

```sql
mysql> select * from test_transaction;
+----+-----------+-----+--------+--------------------+
| id | user_name | age | gender | desctiption        |
+----+-----------+-----+--------+--------------------+
|  1 | 金刚狼 | 127 |      2 | 我有一双铁爪 |
|  2 | 钢铁侠 | 120 |      1 | 我有一身铁甲 |
|  3 | 绿巨人 |   0 |      2 | 我有一身肉    |
+----+-----------+-----+--------+--------------------+
3 rows in set (0.00 sec)
```


## READ UNCOMMITTED与锁 测试
### 演该隔离级别脏读效果


* 先查看当前会话(当前客户端)事务的隔离级别:`SELECT @@SESSION.tx_isolation;`
可以看到:`REPEATABLE READ`是InnoDB存储引擎的默认事务隔离级别

```sql
mysql> SELECT @@SESSION.tx_isolation;
+------------------------+
| @@SESSION.tx_isolation |
+------------------------+
| REPEATABLE-READ        |
+------------------------+
1 row in set (0.00 sec)
 
mysql> 
```


* 重新设置当前客户端事务隔离级别为read uncommitted:`SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;`
注意, 此时只是当前会话端的隔离级别被改, 其余客户端连接自然还是默认的REPEATABLE READ隔离级别

![][5] 
* 接下来将客户端2的事务隔离级别也设置为read uncommitted;

![][6] 
* 客户端1开启事务,并执行一个查询'读取数据':

```sql
mysql> SELECT @@SESSION.tx_isolation;
+------------------------+
| @@SESSION.tx_isolation |
+------------------------+
| READ-UNCOMMITTED       |
+------------------------+
1 row in set (0.00 sec)
 
mysql> begin;
Query OK, 0 rows affected (0.00 sec)
 
mysql> select * from test_transaction where id=2;
+----+-----------+-----+--------+--------------------+
| id | user_name | age | gender | desctiption        |
+----+-----------+-----+--------+--------------------+
|  2 | 钢铁侠 | 120 |      1 | 我有一身铁甲 |
+----+-----------+-----+--------+--------------------+
1 row in set (0.00 sec)
 
mysql> 
```
 **`注意, 客户端1此时的事务并未提交`** 


* 客户端2开启事务, 并修改客户端1查询的数据

```sql
mysql> SELECT @@SESSION.tx_isolation;
+------------------------+
| @@SESSION.tx_isolation |
+------------------------+
| READ-UNCOMMITTED       |
+------------------------+
1 row in set (0.00 sec)
 
mysql> begin;
Query OK, 0 rows affected (0.00 sec)
 
mysql> update test_transaction set user_name='钢铁侠-托尼' where id=2;
Query OK, 1 row affected (0.00 sec)
Rows matched: 1  Changed: 1  Warnings: 0
mysql> 
```


* 此时发现, 客户端2可以对客户端1正在读取的记录进行修改, 而根据锁相关知识,`如果说客户端1在读取记录的时候加了S锁, 那么客户端2是不能加X锁对该记录进行更改的`, 所以可以得出结论: 要么是客户端1读取记录的时候没有加S锁, 要么是客户端2更改记录的时候不加任何锁(这样即使客户端1加了S锁,对它这个不加锁的事务也无可奈何), 那么究竟是哪中情况导致的? 下面继续进行分析...
* **`注意, 客户端2此时的事务也并未提交`** 



* 切换到客户端1, 再次查询数据, 发现数据已经变成了'钢铁侠-托尼'; 然后客户端2`rollback`事务, 再到客户端1中查询,发现user_name又变成了'钢铁侠', 那之前独到'钢铁侠-托尼'就是脏数据了, 这就是一次`脏读`

![][7] 


### 测试,分析该隔离级别如何加锁


* 重新构造测试条件

![][8] 
* 客户端1开启事务, 然后对数据做修改

```sql
mysql> begin;
Query OK, 0 rows affected (0.00 sec)
 
mysql> update test_transaction set user_name='钢铁侠-rymuscle' where id=2;
Query OK, 1 row affected (0.00 sec)
Rows matched: 1  Changed: 1  Warnings: 0
mysql> 
```
 **`注意, 客户端1此时的事务并未提交`** 


* 客户端2开启事务, 对相同的数据行做修改

```sql
mysql> begin;
Query OK, 0 rows affected (0.00 sec)
 
mysql> update test_transaction set user_name='钢铁侠-rym' where id=2;
....阻塞等待了
```

最终会如下:

![][9]


* **`注意:`**  在上面的过程, 在客户端2阻塞阶段, 你可以通过一个新的客户端来分析, 客户端2在锁等待的情况下的`加锁情况`和`事务状态`:


* 查看表的加锁情况:`select * from information_schema.INNODB_LOCKS;`

![][10] 
* 事务状态`select * from information_schema.INNODB_TRX;`

![][11] 



* 所以, **`READ UNCOMMITTED 隔离级别下, 写操作是会加锁的, 而且是X排他锁, 直到客户端1事务完成, 锁才释放, 客户端2才能进行写操作`** 
* 接下来你肯定会纳闷 "既然该隔离级别下事务在修改数据的时候加的是x锁, 并且是事务完成后才释放, 那之前的测试客户端2在事务中修改完数据之后, 为什么事务还没完成, 也就是x锁还在, 结果客户端1却能读取到客户端2修改的数据"？ **`这完全不符合排他锁的特性啊(要知道,排他锁会阻塞除当前事务之外的其他事务的读,写操作)`** 

* 其实网上已经有人在sqlserver的官网上找到了相关资料:

```sql
ansactions running at the READ UNCOMMITTED level do not issue shared locks to prevent other transactions from modifying data read by the current transaction. 
READ UNCOMMITTED transactions are also not blocked by exclusive locks that would prevent the current transaction from reading rows that have been modified but not committed by other transactions. 
When this option is set, it is possible to read uncommitted modifications, which are called dirty reads. Values in the data can be changed and rows can appear or disappear in the data set before the end of the transaction. 
This option has the same effect as setting NOLOCK on all tables in all SELECT statements in a transaction. 
This is the least restrictive of the isolation levels.
```

* 翻译翻译, 在思考思考, 其实说的是

```sql
在 READ UNCOMMITTED 级别运行的事务不会发出共享锁来防止其他事务修改当前事务读取的数据, 既然不加共享锁了, 那么当前事务所读取的数据自然就可以被其他事务来修改。
而且当前事务要读取其他事务未提交的修改, 也不会被排他锁阻止, 因为排他锁会阻止其他事务再对其锁定的数据加读写锁, **但是可笑的是, 事务在该隔离级别下去读数据的话根本什么锁都不加, 这就让排他锁无法排它了, 因为它连锁都没有**。
这就导致了事务可以读取未提交的修改, 称为脏读。
         
```
 **`所以可以得出`** :`READ UNCOMMITTED`隔离级别下, 读不会加任何锁。而写会加排他锁，并到事务结束之后释放。

参考资料:
-《高性能MySQL》


* [MySQL官方文档][17]
* [慕课mark_rock同学手记][18]
* [美团技术博客][13]


[12]: https://tech.meituan.com/innodb-lock.html
[13]: https://tech.meituan.com/innodb-lock.html
[14]: https://segmentfault.com/a/1190000004469395#articleHeader10
[15]: http://blog.csdn.net/flyingfalcon/article/details/53045672
[16]: http://blog.csdn.net/ozwarld/article/details/8259796
[17]: https://dev.mysql.com/doc/refman/5.6/en/innodb-consistent-read.html
[18]: https://www.imooc.com/article/17291
[19]: https://tech.meituan.com/innodb-lock.html
[0]: ./img/bV1ga5.png
[1]: ./img/bV1gbc.png
[2]: ./img/bV1ga9.png
[3]: ./img/bV1gbd.png
[4]: ./img/bV1gbi.png
[5]: ./img/bV1gck.png
[6]: ./img/bV1gci.png
[7]: ./img/bV1gcZ.png
[8]: ./img/bV1gcJ.png
[9]: ./img/bV1gcV.png
[10]: ./img/bV1gcY.png
[11]: ./img/bV1gcZ.png
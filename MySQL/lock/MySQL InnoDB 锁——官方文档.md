## MySQL InnoDB 锁——官方文档

来源：[https://segmentfault.com/a/1190000014071758](https://segmentfault.com/a/1190000014071758)

个人认为学习MySQL最好的书面材料莫过于[官方文档][0]了，它不仅详细介绍了方方面面的使用方法，还讲解了原理，让你知其然并且知其所以然。这里就把官网的[InnoDB Locking][1]这一小节翻译过来，抛砖引玉。

InnoDB锁类型包括


* [共享锁与独占锁][2]
* [意向锁][3]
* [记录锁][4]
* [间隙锁][5]
* [Next-Key Locks][6]（暂无对应翻译）
* [插入意向锁][7]
* [自增锁][8]
* [空间索引断言锁][9]


## 共享锁与独占锁

InnoDB 实现了标准的行级锁，包括两种：共享锁（简称 s 锁）、排它锁（简称 x 锁）


* [共享锁][10]允许持锁事务读取一行
* [排它锁][11]允许持锁事务更新或者删除一行


如果事务 T1 持有行 r 的 s 锁，那么另一个事务 T2 请求 r 的锁时，会做如下处理：


* T2 请求 s 锁立即被允许，结果 T1 T2 都持有 r 行的 s 锁
* T2 请求 x 锁不能被立即允许


如果 T1 持有 r 的 x 锁，那么 T2 请求 r 的 x、s 锁都不能被立即允许，T2 必须等待T1释放 x 锁才行。
## 意向锁

InnoDB 支持多粒度的锁，允许表级锁和行级锁共存。一个类似于 [LOCK TABLES ... WRITE][12] 的语句会获得这个表的 x 锁。为了实现多粒度锁，InnoDB 使用了[意向锁][13]（简称 I 锁）。I 锁是表明一个事务稍后要获得针对一行记录的某种锁（s or x）的对应表的表级锁，有两种：


* 意向排它锁（简称 IX 锁）表明一个事务意图在某个表中设置某些行的 x 锁
* 意向共享锁（简称 IS 锁）表明一个事务意图在某个表中设置某些行的 s 锁


例如， [SELECT ... LOCK IN SHARE MODE][14] 设置一个 IS 锁, [SELECT ... FOR UPDATE][15] 设置一个 IX 锁。
意向锁的原则如下：


* 一个事务必须先持有该表上的 IS 或者更强的锁才能持有该表中某行的 S 锁
* 一个事务必须先持有该表上的 IX 锁才能持有该表中某行的 X 锁


各个锁的兼容性如下:


| | X | IX | S | IS |
| - | - | - | - | - |
| X | N | N | N | N |
| IX | N | Y | N | Y |
| S | N | N | Y | Y |
| IS | N | Y | Y | Y |


新请求的锁只有兼容已有锁才能被允许，否则必须等待不兼容的已有锁被释放。一个不兼容的锁请求不被允许是因为它会引起[死锁][16]，错误会发生。
意向锁只会阻塞全表请求（比如 [LOCK TABLES ... WRITE][12] ）。意向锁的主要目的是展示某人正在锁定表中一行，或者将要锁定一行。

意向锁的事务数据类似于下面的 [SHOW ENGINE INNODB STATUS][18]或者 [InnoDB monitor][19] 的输出：

```sql
TABLE LOCK table `test`.`t` trx id 10080 lock mode IX
```
## 记录锁

记录锁针对索引记录。举个例子，`SELECT c1 FROM t WHERE c1 = 10 FOR UPDATE;`阻止其他所有事务插入、修改或者删除 t.c1 是 10 的行。
记录锁总是锁定索引记录，即使表没有索引（这种情况下，InnoDB会创建隐式的索引，并使用这个索引实施记录锁），见[此处][20]。

记录锁的事务数据类似于下面：

```
RECORD LOCKS space id 58 page no 3 n bits 72 index `PRIMARY` of table `test`.`t` 
trx id 10078 lock_mode X locks rec but not gap
Record lock, heap no 2 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
 0: len 4; hex 8000000a; asc     ;;
 1: len 6; hex 00000000274f; asc     'O;;
 2: len 7; hex b60000019d0110; asc        ;;
```
## 间隙锁

间隙锁（gap）是索引记录之间上的锁，或者说第一个索引记录之前或最后一个索引记录之后的间隔上的锁。例如，`SELECT c1 FROM t WHERE c1 BETWEEN 10 and 20 FOR UPDATE;`阻止其他事务插入 t.c1 = 15 的记录，不管是否已经有这种值在本列中，因为这个范围内的所有值都被上锁了。

一个间隙可能是一个索引值、多个索引值，甚至是空的。

间隙锁是性能与并发的部分折中，并只适用于一些事务隔离级别。

使用唯一索引的时候用不上间隙锁。例如，id 列有唯一索引，下面的语句只是用索引记录锁（针对id=100的行）不管其他会话是否在前面的间隙中插入行。

```sql
SELECT * FROM child WHERE id = 100;

```

如果id列没有索引或者是非唯一索引，那么这条语句的确会锁住前面的间隙。

同样值得注意的是，不同的事务可能会在一个间隙中持有冲突的锁，例如，事务A可以持有一个间隙上共享的间隙锁（gap s lock）同时事务B持有
该间隙的排他的间隙锁（gap x lock），冲突的间隙锁被允许的原因是如果一条记录从索引中被清除了，那么这条记录上的间隙锁必须被合并。

间隙锁在Innodb中是被“十足的抑制”的，也就是说，他们只阻止其他事务插入到间隙中，他们不阻止其他事物在同一个间隙上获得间隙锁，所以 gap x lock 和 gap s lock 有相同的作用。

间隙锁可以被显式的关闭，比如你可以：1、设置事务隔离级别为[读已提交][21]或者2、把 innodb_locks_unsafe_for_binlog 系统变量设置为 true （现在已经废弃这个变量了）。这两种情况下间隙锁就被关闭了，索引扫描只用于外键检查和重复键检查。

上面两个操作还要其他效果。mysql检索了where条件后，不匹配的行的记录锁释放了，对于[UPDATE][22]语句，Innodb有一个semi-consistent 读操作，亦即返回最新提交的版本给mysql，这样mysql就能判断哪些行符合条件。
## Next-Key Locks

Next-Key Locks （简称 NK 锁）是记录锁和间隙锁的组合。
Innodb是这样执行行级别锁的，它搜索或者扫描一个表的索引，在他遇上的索引记录上设置共享或者排他锁，这样行级锁实际就是索引记录锁，一个NK 锁同样影响索引记录之前的间隙。所以，NK 锁是一个索引记录锁和索引记录之前的间隙上的间隙锁。如果一个会话在一行 R 上有一个共享、排它锁，其他会话不能立即在R之前的间隙中插入新的索引记录。

假设一个索引包含值 10,11,13和20，索引上可能的NK 锁包括如下几个区间（注意开闭区间）

```
(negative infinity, 10]
(10, 11]
(11, 13]
(13, 20]
(20, positive infinity)

```

对于最后一个区间，NK 锁锁住了索引中最大值和比索引值中任何值都大的上确界伪值之上的间隙。上确界不是一个真正的索引记录，所以事实上NK锁只锁住了最大索引值上的间隙。

默认情况下，Innodb 是[可重读][23]隔离级别，这样的话，Innodb使用NK 锁来进行索引搜索和扫描，阻止了[幻读][24]。

事务数据类似于下面：

```
RECORD LOCKS space id 58 page no 3 n bits 72 index `PRIMARY` of table `test`.`t` 
trx id 10080 lock_mode X
Record lock, heap no 1 PHYSICAL RECORD: n_fields 1; compact format; info bits 0
 0: len 8; hex 73757072656d756d; asc supremum;;

Record lock, heap no 2 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
 0: len 4; hex 8000000a; asc     ;;
 1: len 6; hex 00000000274f; asc     'O;;
 2: len 7; hex b60000019d0110; asc        ;;

```
## 插入意向锁

插入意向锁是在插入一行记录操作之前设置的一种间隙锁，这个锁释放了一种插入方式的信号，亦即多个事务在相同的索引间隙插入时如果不是插入间隙中相同的位置就不需要互相等待。假设有索引值4、7，几个不同的事务准备插入5、6，每个锁都在获得插入行的独占锁之前用插入意向锁各自锁住了4、7之间的间隙，但是不阻塞对方因为插入行不冲突。

下面的例子展示了事务在获得独占锁之前获得插入意向锁的过程，例子包括客户端A、B。A 创建了表包含两个索引记录（90和102），然后开启了事务会放置一个独占锁在id大于100的索引记录中，这个独占锁锁住了102之前的间隙

```
mysql> CREATE TABLE child (id int(11) NOT NULL, PRIMARY KEY(id)) ENGINE=InnoDB;
mysql> INSERT INTO child (id) values (90),(102);

mysql> START TRANSACTION;
mysql> SELECT * FROM child WHERE id > 100 FOR UPDATE;
+-----+
| id  |
+-----+
| 102 |
+-----+

```

B开启事务插入记录到间隙中，这个事务在等待获得独占锁的时候获得一个插入意向锁。

```sql
mysql> START TRANSACTION;
mysql> INSERT INTO child (id) VALUES (101);

```

事务数据类似于下面：

```
RECORD LOCKS space id 31 page no 3 n bits 72 index `PRIMARY` of table `test`.`child`
trx id 8731 lock_mode X locks gap before rec insert intention waiting
Record lock, heap no 3 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
 0: len 4; hex 80000066; asc    f;;
 1: len 6; hex 000000002215; asc     " ;;
 2: len 7; hex 9000000172011c; asc     r  ;;...

```
## 自增锁

自增锁是一个特殊的表级锁，事务插入自增列的时候需要获取，最简单情况下如果一个事务插入一个值到表中，任何其他事务都要等待，这样第一个事物才能获得连续的主键值。

[innodb_autoinc_lock_mode][25]配置选项控制了自增锁的算法，它让你选择在可预测的连续自增值和并发度之间的平衡。

见 [Section 14.8.1.5, “AUTO_INCREMENT Handling in InnoDB”.][26]
## 空间索引断言锁

InnoDB 支持针对含空间数据的列的列空间索引，要处理空间索引的锁，next-key处理的不好，不能支持[可重复读][27]和[序列化][28]的事务隔离级别。因为多维数据中没有绝对的顺序概念，所以不能明确什么是next key(下一个键)。

为了支持含空间索引的表的事务隔离级别，InnoDB 使用了断言锁，一个空间索引包含了最小外接矩形（MBR）值,所以InnoDB 通过为查询使用的MBR设置断言锁保证索引了一致读。其他事务不能插入符合当前事务查询条件的行。

查看翻译原文：[MageekChiu][29]。

[0]: https://dev.mysql.com/doc/
[1]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html
[2]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-shared-exclusive-locks
[3]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-intention-locks
[4]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-record-locks
[5]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-gap-locks
[6]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-next-key-locks
[7]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-insert-intention-locks
[8]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-auto-inc-locks
[9]: https://dev.mysql.com/doc/refman/5.7/en/innodb-locking.html#innodb-predicate-locks
[10]: https://dev.mysql.com/doc/refman/5.7/en/glossary.html#glos_shared_lock
[11]: https://dev.mysql.com/doc/refman/5.7/en/glossary.html#glos_exclusive_lock
[12]: https://dev.mysql.com/doc/refman/5.7/en/lock-tables.html
[13]: https://dev.mysql.com/doc/refman/5.7/en/glossary.html#glos_intention_lock
[14]: https://dev.mysql.com/doc/refman/5.7/en/select.html
[15]: https://dev.mysql.com/doc/refman/5.7/en/select.html
[16]: https://dev.mysql.com/doc/refman/5.7/en/glossary.html#glos_deadlock
[17]: https://dev.mysql.com/doc/refman/5.7/en/lock-tables.html
[18]: https://dev.mysql.com/doc/refman/5.7/en/show-engine.html
[19]: https://dev.mysql.com/doc/refman/5.7/en/innodb-standard-monitor.html
[20]: https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
[21]: https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html#isolevel_read-committed
[22]: https://dev.mysql.com/doc/refman/5.7/en/update.html
[23]: https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html#isolevel_repeatable-read
[24]: https://dev.mysql.com/doc/refman/5.7/en/innodb-next-key-locking.html
[25]: https://dev.mysql.com/doc/refman/5.7/en/innodb-parameters.html#sysvar_innodb_autoinc_lock_mode
[26]: https://dev.mysql.com/doc/refman/5.7/en/innodb-auto-increment-handling.html
[27]: https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html#isolevel_repeatable-read
[28]: https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html#isolevel_serializable
[29]: http://mageek.cn/archives/83/
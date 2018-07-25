## MySQL自增列（AUTO_INCREMENT）相关知识点总结

来源：[http://www.cnblogs.com/kerrycode/p/9294767.html](http://www.cnblogs.com/kerrycode/p/9294767.html)

时间 2018-07-11 15:32:00

 
 ** MySQL自增（AUTO_INCREMENT）相关知识点归纳   ** 
 
MySQL的自增列（AUTO_INCREMENT）和其它数据库的自增列对比，有很多特性和不同点（甚至不同存储引擎、不同版本也有一些不同的特性），让人感觉有点稍微复杂。下面我们从一些测试开始，来认识、了解一下这方面的特殊知识点：
 
 ** 自增列持久化问题   ** 
 
 如果一个表拥有自增列，当前最大自增列值为9， 删除了自增列6、7、8、9的记录，重启MySQL服务后，再往表里面插入数据，自增列的值为6还是10呢？   如果表的存储引擎为MyISAM呢，又会是什么情况？ 下面实验环境为MySQL 5.7.21 
 
```sql
mysql> drop table if exists test;
Query OK, 0 rows affected (0.08 sec)
 
mysql> create table test(id int auto_increment primary key, name varchar(32)) ENGINE=InnoDB;
Query OK, 0 rows affected (0.02 sec)
 
 
mysql> insert into test(name)
    -> select 'kkk1' from dual union all
    -> select 'kkk2' from dual union all
    -> select 'kkk3' from dual union all
    -> select 'kkk4' from dual union all
    -> select 'kkk5' from dual union all
    -> select 'kkk6' from dual union all
    -> select 'kkk7' from dual union all
    -> select 'kkk8' from dual union all
    -> select 'kkk9' from dual;
Query OK, 9 rows affected (0.01 sec)
Records: 9  Duplicates: 0  Warnings: 0
 
 
mysql> select * from test;
+----+------+
| id | name |
+----+------+
|  1 | kkk1 |
|  2 | kkk2 |
|  3 | kkk3 |
|  4 | kkk4 |
|  5 | kkk5 |
|  6 | kkk6 |
|  7 | kkk7 |
|  8 | kkk8 |
|  9 | kkk9 |
+----+------+
9 rows in set (0.00 sec)
 
mysql> delete from test where id>=6;
Query OK, 4 rows affected (0.00 sec)


```
 
重启MySQL服务后，然后我们插入一条记录，字段ID会从什么值开始呢？ 如下所示，如果表的存储引擎为InnoDB，那么插入的数据的自增字段值为6.
 
![][0]
 
接下来，我们创建一个MyISAM类型的测试表。如下所示：
 
```sql
mysql> drop table if exists test;
Query OK, 0 rows affected (0.01 sec)
 
mysql> create table test(id int auto_increment  primary key, name varchar(32)) engine=MyISAM;
Query OK, 0 rows affected (0.02 sec)
 
mysql> 
 
insert into test(name)
select 'kkk1' from dual union all
select 'kkk2' from dual union all
select 'kkk3' from dual union all
select 'kkk4' from dual union all
select 'kkk5' from dual union all
select 'kkk6' from dual union all
select 'kkk7' from dual union all
select 'kkk8' from dual union all
select 'kkk9' from dual;
 
 
mysql> delete from test where id>=6;
Query OK, 4 rows affected (0.00 sec)


```
 
删除了id>=6的记录后，重启MySQL服务，如下所示，测试结果为id =10, 那么为什么出现不同的两个结果呢？这个是因为InnoDB存储引擎中，自增主键没有持久化，而是放在内存中，关于自增主键的分配，是由InnoDB数据字典内部一个计数器来决定的，而该计数器只在内存中维护，并不会持久化到磁盘中。当数据库重启时，该计数器会通过SELECT MAX(ID) FROM TEST FOR UPDATE这样的SQL语句来初始化（不同表对应不同的SQL语句）, 其实这是一个bug来着, 对应的链接地址为：https://bugs.mysql.com/bug.php?id=199，直到MySQL 8.0 ，才将自增主键的计数器持久化到redo log中。每次计数器发生改变，都会将其写入到redo log中。如果数据库发生重启，InnoDB会根据redo log中的计数器信息来初始化其内存值。 而对应与MySIAM存储引擎，自增主键的最大值存放在数据文件当中，每次重启MySQL服务都不会影响其值变化。
 
![][1]
 
 ** 自增列细节特性   ** 
 
1：SQL模式的NO_AUTO_VALUE_ON_ZERO值影响AUTO_INCREMENT列的行为。
 
```sql
mysql> drop table if exists test;
Query OK, 0 rows affected (0.01 sec)
 
mysql> create table test(id int auto_increment primary key, name varchar(32));
Query OK, 0 rows affected (0.02 sec)
 
mysql> select @@sql_mode;
+-------------------------------------------------------------------------------------------------------------------------------------------+
| @@sql_mode                                                                                                                                |
+-------------------------------------------------------------------------------------------------------------------------------------------+
| ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION |
+-------------------------------------------------------------------------------------------------------------------------------------------+
1 row in set (0.00 sec)
 
mysql> insert into test(id, name) value(0, 'kerry');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
+----+-------+
1 row in set (0.00 sec)
 
mysql> 
 

```
 
如上所示，如果在SQL模式里面没有设置NO_AUTO_VALUE_ON_ZERO的话，那么在默认设置下，自增列默认一般从1开始自增，插入0或者null代表生成下一个自增长值。如果用户希望插入的值为0，而该列又是自增长的，那么这个选项就必须设置
 
```sql
mysql> SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";
Query OK, 0 rows affected (0.00 sec)
 
mysql> insert into test(id, name) value(0, 'kerry');
Query OK, 1 row affected (0.01 sec)
 
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  0 | kerry |
|  1 | kerry |
+----+-------+
2 rows in set (0.00 sec)
 
mysql> 
 

```
 
2：如果把一个NULL值插入到一个AUTO_INCREMENT数据列里去，MySQL将自动生成下一个序列编号。如下所示，这个语法对于熟悉SQL Server中自增字段的人来来看，简直就是不可思议的事情。
 
```sql
mysql> drop table if exists test;
Query OK, 0 rows affected (0.03 sec)
 
mysql> create table test(id int auto_increment primary key, name varchar(32));
Query OK, 0 rows affected (0.05 sec)
 
mysql> insert into test(id , name) value(null, 'kerry');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
+----+-------+
1 row in set (0.00 sec)


```
 
3：获取当前自增列的值
 
 <font face="宋体">        获取当前自增列的值，可以使用          <font face="宋体"> LAST_INSERT_ID函数，注意，这个是一个系统函数，   可   获得自增列自动生成的最后一个值。但该函数只与服务器的本次会话过程中生成的值有关。如果在与服务器的本次会话中尚未生成AUTO_INCREMENT值，则该函数返回0   
 
```sql
mysql> select last_insert_id();
+------------------+
| last_insert_id() |
+------------------+
|                1 |
+------------------+
1 row in set (0.00 sec)
 
mysql> insert into test(name) value('jimmy');
Query OK, 1 row affected (0.00 sec)
 
mysql> select last_insert_id();
+------------------+
| last_insert_id() |
+------------------+
|                2 |
+------------------+
1 row in set (0.00 sec)
 
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
|  2 | jimmy |
+----+-------+
2 rows in set (0.00 sec)


```
 
如果要获取自增列的下一个值，那么可以使用show create table tablename查看。如下截图所示
 
![][2]
 
4：自增列跳号
 
MySQL中，自增字段可以跳号：可以插入一条指定自增列值的记录（即使插入的值大于自增列的最大值），如下所示，当前自增列最大值为1，我插入一个200的值，然后就会以200为基础继续自增，而且我还可以继续插入ID=100的记录，无需任何额外设置。
 
```sql
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
+----+-------+
1 row in set (0.00 sec)
 
mysql> insert into test value(200, 'test');
Query OK, 1 row affected (0.01 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 200 | test  |
+-----+-------+
2 rows in set (0.00 sec)
 
mysql> insert into test(name) value('test2');
Query OK, 1 row affected (0.01 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 200 | test  |
| 201 | test2 |
+-----+-------+
3 rows in set (0.00 sec)
 
mysql> 
mysql> insert into test(id, name) value(100, 'ken');
Query OK, 1 row affected (0.01 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 100 | ken   |
| 200 | test  |
| 201 | test2 |
+-----+-------+
4 rows in set (0.00 sec)


```
 
另外一个是关于自增列逻辑跳号问题，在一个事务里面，使用遇到事务回滚，自增列就会跳号，如下所示，id从201 跳到 203了。
 
```sql
mysql> begin;
Query OK, 0 rows affected (0.00 sec)
 
mysql> insert into test(name) value('kkk');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 100 | ken   |
| 200 | test  |
| 201 | test2 |
| 202 | kkk   |
+-----+-------+
5 rows in set (0.00 sec)
 
mysql> rollback;
Query OK, 0 rows affected (0.00 sec)
 
mysql> insert into test(name) value('kkk');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 100 | ken   |
| 200 | test  |
| 201 | test2 |
| 203 | kkk   |
+-----+-------+
5 rows in set (0.00 sec)


```
 
当然，无论MySQL还是其他关系型数据库，都会遇到这种逻辑跳号的情况，例如ORACLE的序列也会存在这种逻辑跳号问题。为提高自增列的生成效率，都将生成自增值的操作设计为非事务性操作，表现为当事务回滚时，事务中生成的自增值不会被回滚。
 
5：truncate table操作会引起自增列从头开始计数
 
```sql
mysql> truncate table test;
Query OK, 0 rows affected (0.01 sec)
 
mysql> insert into test(name) value('kerry');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
+----+-------+
1 row in set (0.00 sec)
 
mysql> 
 

```
 
6：修改AUTO_INCREMENT的值来修改自增起始值。
 
```sql
mysql> select * from test;
+----+-------+
| id | name  |
+----+-------+
|  1 | kerry |
+----+-------+
1 row in set (0.00 sec)
 
mysql> alter table test auto_increment=100;
Query OK, 0 rows affected (0.00 sec)
Records: 0  Duplicates: 0  Warnings: 0
 
mysql> insert into test(name) value('k3');
Query OK, 1 row affected (0.00 sec)
 
mysql> select * from test;
+-----+-------+
| id  | name  |
+-----+-------+
|   1 | kerry |
| 100 | k3    |
+-----+-------+
2 rows in set (0.00 sec)


```
 
当然MySQL还有一些相关知识点，这里没有做总结，主要是没有遇到过相关场景。以后遇到了再做总结，另外一方面，写技术文章，很难面面俱到，这样太耗时也太累人了！
 
 ** 参考资料：   ** 
 
http://www.cnblogs.com/TeyGao/p/9279390.html
 
https://dev.mysql.com/doc/refman/5.7/en/example-auto-increment.html
 
 [https://dev.mysql.com/doc/refman/5.7/en/innodb-auto-increment-handling.html][3] 
 
http://www.cnblogs.com/yangzumin/p/3756583.html
 


[3]: https://dev.mysql.com/doc/refman/5.7/en/innodb-auto-increment-handling.html
[0]: ./img/zyaInyf.png 
[1]: ./img/BFFzayM.png 
[2]: ./img/EfaMZzA.png 
## MYSQL中的乐观锁实现(MVCC)简析

来源：[https://segmentfault.com/a/1190000009374567](https://segmentfault.com/a/1190000009374567)


## 什么是MVCC

MVCC即Multi-Version Concurrency Control，中文翻译过来叫多版本并发控制。
## MVCC是解决了什么问题

众所周知，在MYSQL中，MyISAM使用的是表锁，InnoDB使用的是行锁。而InnoDB的事务分为四个隔离级别，其中默认的隔离级别REPEATABLE READ需要两个不同的事务相互之间不能影响，而且还能支持并发，这点悲观锁是达不到的，所以REPEATABLE READ采用的就是乐观锁，而乐观锁的实现采用的就是MVCC。正是因为有了MVCC，才造就了InnoDB强大的事务处理能力。
## MVCC具体实现分析

InnoDB的MVCC,是通过在每行记录后面保存两个隐藏的列来实现的,这两个列，分别保存了这个行的创建时间，一个保存的是行的删除时间。这里存储的并不是实际的时间值,而是系统版本号(可以理解为事务的ID)，每开始一个新的事务，系统版本号就会自动递增，事务开始时刻的系统版本号会作为事务的ID.下面看一下在REPEATABLE READ隔离级别下,MVCC具体是如何操作的。

-----

首先创建一张表:

```sql
create table yang( 
    id int primary key auto_increment, 
    name varchar(20)
);
```

假设系统的版本号从1开始.
### INSERT

InnoDB为新插入的每一行保存当前系统版本号作为版本号。第一个事务ID为1:

```sql
start transaction; 
insert into yang values(NULL,'yang');
insert into yang values(NULL,'long');
insert into yang values(NULL,'fei');
commit;
```

对应在数据中的表如下(后面两列是隐藏列,我们通过查询语句并看不到)

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | undefined |
| 2 | long | 1 | undefined |
| 3 | fei | 1 | undefined |


### SELECT

InnoDB会根据以下两个条件检查每行记录:


* InnoDB只会查找版本早于当前事务版本的数据行(也就是,行的系统版本号小于或等于事务的系统版本号)，这样可以确保事务读取的行，要么是在事务开始前已经存在的，要么是事务自身插入或者修改过的.

* 行的删除版本要么未定义,要么大于当前事务版本号(这可以确保事务读取到的行，在事务开始之前未被删除)， 
只有条件1、2同时满足的记录，才能返回作为查询结果.


### DELETE

InnoDB会为删除的每一行保存当前系统的版本号(事务的ID)作为删除标识.

看下面的具体例子分析: 第二个事务,ID为2:

```sql
start transaction; 
select * from yang; 
select * from yang; 
commit;
```
 **` 假设1: `** 
假设在执行这个事务ID为2的过程中,刚执行到(1),这时,有另一个事务ID为3往这个表里插入了一条数据; 第三个事务ID为3;

```sql
start transaction;
insert into yang values(NULL,'tian');
commit;
```

这时表中的数据如下:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | undefined |
| 2 | long | 1 | undefined |
| 3 | fei | 1 | undefined |
| 4 | tian | 3 | undefined |


然后接着执行事务2中的(2),由于id=4的数据的创建时间(事务ID为3),执行当前事务的ID为2,而InnoDB只会查找事务ID小于等于当前事务ID的数据行,所以id=4的数据行并不会在执行事务2中的(2)被检索出来,在事务2中的两条select 语句检索出来的数据如下:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | undefined |
| 2 | long | 1 | undefined |
| 3 | fei | 1 | undefined |

 **` 假设2 `** 
假设在执行这个事务ID为2的过程中,刚执行到(1),假设事务执行完事务3后，接着又执行了事务4; 
第四个事务:

```sql
start transaction; 
delete from yang where id=1; 
commit;
```

此时数据库中的表如下:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | 4 |
| 2 | long | 1 | undefined |
| 3 | fei | 1 | undefined |
| 4 | tian | 3 | undefined |


接着执行事务ID为2的事务(2),根据SELECT 检索条件可以知道,它会检索创建时间(创建事务的ID)小于当前事务ID的行和删除时间(删除事务的ID)大于当前事务的行,而id=4的行上面已经说过,而id=1的行由于删除时间(删除事务的ID)大于当前事务的ID,所以事务2的(2)select * from yang也会把id=1的数据检索出来.所以,事务2中的两条select 语句检索出来的数据都如下:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | 4 |
| 2 | long | 1 | undefined |
| 3 | fei | 1 | undefined |


### UPDATE

InnoDB执行UPDATE，实际上是新插入了一行记录，并保存其创建时间为当前事务的ID，同时保存当前事务ID到要UPDATE的行的删除时间。 
 **` 假设3： `** 
假设在执行完事务2的(1)后又执行,其它用户执行了事务3,4,这时，又有一个用户对这张表执行了UPDATE操作: 
第5个事务:

```sql
start transaction; 
update yang set name='Long' where id=2;
commit;
```

根据update的更新原则:会生成新的一行,并在原来要修改的列的删除时间列上添加本事务ID,得到表如下:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | 4 |
| 2 | long | 1 | 5 |
| 3 | fei | 1 | undefined |
| 4 | tian | 3 | undefined |
| 2 | Long | 5 | undefined |


继续执行事务2的(2),根据select 语句的检索条件,得到下表:

| id | name | 创建时间(事务ID) | 删除时间(事务ID) |
| - | - | - | - |
| 1 | yang | 1 | 4 |
| 2 | long | 1 | 5 |
| 3 | fei | 1 | undefined |


还是和事务2中(1)select 得到相同的结果.

## Mysql事务处理详细讲解及完整实例下载

<font face=微软雅黑>

 时间 2017-08-15 00:16:00  博客园精华区

原文[http://www.cnblogs.com/sztx/p/7361954.html][2]


### 一、Mysql事务概念

MySQL 事务主要用于处理操作量大，复杂度高的数据。由一步或几步数据库操作序列组成逻辑执行单元，这系列操作要么全部执行，要么全部放弃执行。在 MySQL 中只有使用了 Innodb 数据库引擎的数据库或表才支持事务。事务用来管理 insert,update,delete 语句。

### 二、事务特性：
Atomicity(原子性)、Consistency（稳定性,一致性）、隔离性（Isolation）和Durability(持续性,可靠性)。这四个特性也简称ACID性。

##### 1.原子性：
事务是应用中最小的执行单位，就如原子是自然界最小颗粒，具有不可再分的特征一样。事务是应用中不可再分的最小逻辑执行体,一组事务，要么成功；要么撤回。

##### 2.稳定性,一致性：
事务执行的结果，必须使数据库从一个一致性状态，变到另一个一致性状态。当数据库中只包含事务成功提交的结果时，数据库处于一致性状态。一致性是通过原子性来保证的。有非法数据（外键约束之类），事务撤回。

##### 3.隔离性：
各个事务的执行互不干扰，任意一个事务的内部操作对其他并发的事务，都是隔离的。也就是说：并发执行的事务之间不能看到对方的中间状态，并发执行的事务之间不能相互影响。事务独立运行。一个事务处理后的结果，影响了其他事务，那么其他事务会撤回。事务的100%隔离，需要牺牲速度。

##### 4.持续性,可靠性：
持续性也称为持久性，指事务一旦提交，对数据所做的任何改变，都要记录到永久存储器中，通常是保存进物理数据库。软、硬件崩溃后，InnoDB数据表驱动会利用日志文件重构修改。可靠性和高速度不可兼得， innodb_flush_log_at_trx_commit 选项 决定什么时候吧事务保存到日志里。

> 注意事项 ：存储引擎MyISAM不支持事物，存储引擎InnoDB支持事物。事务只针对对数据数据产生影响的语句有效。show engines 查看mysql锁支持的数据引擎。 

### 三、读取数据概念

##### 1.脏读（Dirty Reads）：
所谓脏读就是对脏数据的读取，而脏数据所指的就是未提交的数据。一个事务正在对一条记录做修改，在这个事务完成并提交之前，这条数据是处于待定状态的（可能提交也可能回滚），这时，第二个事务来读取这条没有提交的数据，并据此做进一步的处理，就会产生未提交的数据依赖关系。这种现象被称为脏读。

##### 2.不可重复读（Non-Repeatable Reads）：
一个事务先后读取同一条记录，但两次读取的数据不同，我们称之为不可重复读。也就是说，这个事务在两次读取之间该数据被其它事务所修改。

##### 3.幻读（Phantom Reads）：
一个事务按相同的查询条件重新读取以前检索过的数据，却发现其他事务插入了满足其查询条件的新数据，这种现象就称为幻读。

### 四、事务隔离级别

修改事务隔离级别语法：

    SET [SESSION | GLOBAL] TRANSACTION ISOLATION LEVEL {READ UNCOMMITTED | READ COMMITTED | REPEATABLE READ | SERIALIZABLE}

##### 1、Read Uncommitted(未授权读取、读未提交)：
这是最低的隔离等级，允许其他事务看到没有提交的数据。这种等级会导致脏读。如果一个事务已经开始写数据，则另外一个事务则不允许同时进行写操作，但允许其他事务读此行数据。该隔离级别可以通过“排他写锁”实现。避免了更新丢失，却可能出现脏读。也就是说事务B读取到了事务A未提交的数据。SELECT语句以非锁定方式被执行，所以有可能读到脏数据，隔离级别最低。

```sql
    SET session transaction isolation level  read uncommitted ;
    SET global transaction isolation level read uncommitted;/*全局建议不用*/
    SELECT @@global.tx_isolation;
    SELECT @@session.tx_isolation;
    SELECT @@tx_isolation;
```

新建一个简单的student表，设置id和name,num字段，开启事务1对表新增通过存储过程，事务不提交，查看当前数据库事务状态,可以看到一条数据事务，事务级别为READ UNCOMMITTED：

```sql
    drop table if exists student;
    create table student(
    id int primary key auto_increment comment 'id',
    name varchar(100) comment '名称',
    num int
    );
    drop procedure if exists proc_on_sw;
    delimiter ;;
    create procedure proc_on_sw()
    begin
    start transaction;
    insert into student(name,num) value('aaa',1);
    select * from information_schema.INNODB_TRX;
    end
    ;;
    delimiter ;;
    call proc_on_sw();
```

新建事务2，查询student表，我们在READ UNCOMMITTED级别下，可以看到其他事务未提交的数据：再去查看数据库事务状态，我们会看到状态正常。

```sql
    start transaction ;
    select * from student;
    commit;
```

##### 2.Read Committed(授权读取、读提交):
读取数据的事务允许其他事务继续访问该行数据，但是未提交的写事务将会禁止其他事务访问该行。该隔离级别避免了脏读，但是却可能出现不可重复读。事务A事先读取了数据，事务B紧接了更新了数据，并提交了事务，而事务A再次读取该数据时，数据已经发生了改变。

```sql
    SET session transaction isolation level  read committed ;
    SET global transaction isolation level read committed; /*全局建议不用*/
    
    drop procedure if exists proc_on_up;
    delimiter ;;
    create procedure proc_on_up()
    begin
    set autocommit=0;
    update student set name='cc' where id=1;
    commit;
    set autocommit=1;
    end
    ;;
    delimiter ;;
    call proc_on_up();
    select * from student;
```

##### 3.repeatable read(可重复读取):
就是在开始读取数据（事务开启）时，不再允许修改操作,事务开启，不允许其他事务的UPDATE修改操作，不可重复读对应的是修改，即UPDATE操作。但是可能还会有幻读问题。因为幻读问题对应的是插入INSERT操作，而不是UPDATE操作。避免了不可重复读取和脏读，但是有时可能出现幻读。这可以通过“共享读锁”和“排他写锁”实现。


```sql
    set session transaction isolation level repeatable read;
```

##### 4.串行化、序列化：
提供严格的事务隔离。它要求事务序列化执行，事务只能一个接着一个地执行，但不能并发执行。如果仅仅通过“行级锁”是无法实现事务序列化的，必须通过其他机制保证新插入的数据不会被刚执行查询操作的事务访问到。序列化是最高的事务隔离级别，同时代价也花费最高，性能很低，一般很少使用，在该级别下，事务顺序执行，不仅可以避免脏读、不可重复读，还避免了幻像读。

```sql
    set session transaction isolation level serializable;
```

隔离等级 脏读 不可重复读 幻读

读未提交 YES YES YES

读已提交 NO YES YES

可重复读 NO NO YES

串行化 NO NO NO

五、完整例子包括提交和回滚完整例子

```sql
    drop procedure if exists pro_new;
    delimiter;;
    create procedure pro_new(out rtn int)
    begin
    declare err INT default 0;
    -- 如果出现异常，会自动处理并rollback
    declare exit handler for  sqlexception ROLLBACK ; 
    -- 启动事务
    set autocommit=0;
    start transaction;
    insert into student(name,num) values(NULL,2.3);
    -- set err = @@IDENTITY; -- =    获取上一次插入的自增ID;
    set err =last_insert_id(); -- 获取上一次插入的自增ID
    insert into student(name,num) VALUEs('ccc',err);
    -- 运行没有异常，提交事务
    commit;
    -- 设置返回值为1
    set rtn=1;
    set autocommit=1;
    end
    ;;
    delimiter ;;
    set @n=1;
    call pro_new(@n);
    select @n;
```

```sql
-- 未提交事务隔离级别
SET session transaction isolation level  read uncommitted ;
SET global transaction isolation level read uncommitted;
SELECT @@global.tx_isolation;
SELECT @@session.tx_isolation;
SELECT @@tx_isolation;
-- 未提交事务隔离级别是的实例
drop table if exists student;
create table student(
id int primary key auto_increment comment 'id',
name varchar(100) comment '名称' not NULL,
num int
);
drop procedure if exists proc_on_sw;
delimiter ;;
create procedure proc_on_sw()
begin
 
start transaction;
insert into student(name,num) value('aaa',1);
select * from information_schema.INNODB_TRX;
end
;;
delimiter ;;
call proc_on_sw();
-- 新建事务2，查询student表，我们在READ UNCOMMITTED级别下，可以看到其他事务未提交的数据：
start transaction ;
select * from student;
commit;
select * from information_schema.INNODB_TRX;
 
-- Read Committed(授权读取、读提交)实例
SET session transaction isolation level  read committed ;
/* SET global transaction isolation level read committed; */
 
drop procedure if exists proc_on_up;
delimiter ;;
create procedure proc_on_up()
begin
set autocommit=0;
update student set name='cc' where id=1;
commit;
set autocommit=1;
end
;;
delimiter ;;
call proc_on_up();
select * from student;
-- 可重复读取实例
set session transaction isolation level repeatable read;
SET global transaction isolation level repeatable read; /*全局建议不用*/
start transaction;
update student set name='atttc' where id=1;
commit;
select * from student;
 
-- 串行化
set session transaction isolation level serializable;
 
-- 完整实例
SET session transaction isolation level  read committed ;
 SET global transaction isolation level read committed; 
drop procedure if exists pro_new;
delimiter;;
create procedure pro_new(out rtn int)
begin
declare err INT default 0;
-- 如果出现异常，会自动处理并rollback
declare exit handler for  sqlexception ROLLBACK ; 
-- 启动事务
set autocommit=0;
start transaction;
insert into student(name,num) values(NULL,2.3);
-- set err = @@IDENTITY; -- =   获取上一次插入的自增ID;
set err =last_insert_id(); -- 获取上一次插入的自增ID
insert into student(name,num) VALUEs('ccc',err);
-- 运行没有异常，提交事务
commit;
-- 设置返回值为1
set rtn=1;
set autocommit=1;
end
;;
delimiter ;;
set @n=1;
call pro_new(@n);
select @n;

```

</font>

[2]: http://www.cnblogs.com/sztx/p/7361954.html

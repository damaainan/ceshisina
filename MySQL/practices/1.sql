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
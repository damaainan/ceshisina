/*简单创建存储函数名proc_on_insert;
 *判断proc_on_insert 函数名如果存在就用drop删除
 */
drop procedure if exists proc_on_insert; 
delimiter ;;
create procedure proc_on_insert()
begin
declare a int ;
end
;;
delimiter ;;
-- in参数函数
drop procedure if exists prc_on_in;
delimiter ;;
create procedure prc_on_in(in num int)
begin
declare number int ;
set number=num;
select number;
end
;;
delimiter ;;
set @num=1;
call prc_on_in(@num);
 
-- out参数函数
drop procedure if exists prc_on_out;
delimiter ;;
create procedure prc_on_out(out out_num int)
begin
select out_num;
set out_num=78;
select out_num;
end
;;
delimiter ;;
set @number=6;
call prc_on_out(@number);
-- inout 参数函数
drop procedure if exists prc_on_inout;
delimiter ;;
create procedure prc_on_inout(inout p_inout int)
begin
select p_inout;
set p_inout=100;
select p_inout;
end
;;
delimiter ;;
set @p_out=90;
call prc_on_inout(@p_out);
-- 存储过程中的IF语句（if then elseif then else end if）
drop procedure if exists p_else;
create procedure p_else(in id int)
begin
    if (id > 0) then
        select '> 0' as id;
    elseif (id = 0) then
        select '= 0' as id;
    else
        select '< 0' as id;
    end if;
end;
set @p=-10;
call p_else(@p);
-- case when then
drop procedure if exists p_case;
delimiter ;;
create procedure p_case(  
    id int 
)  
begin 
    case id  
    when 1 then    
    select 'one' as trans;  
    when 2 then 
    select 'two' as trans;  
    when 3 then  
    select 'three' as trans;  
    else 
    select 'no trans' as trans;  
    end case;  
end;  
;;
delimiter ;;
set @id=1;
call p_case(@id);
-- while do … end while语句
 
drop procedure if exists p_while_do;  
create procedure p_while_do()  
begin 
    declare i int;  
        set i = 1;  
        while i <= 10 do  
            select concat('index : ', i) ;  
            set i = i + 1;  
        end while;  
end;  
call p_while_do();
-- repeat … until end repeat
drop procedure if exists p_repeat;
delimiter ;;
create procedure p_repeat(in parameter int)
BEGIN
     declare var int;  
     set var = parameter; 
     REPEAT
     set var = var - 1; 
     set parameter = parameter -2; 
     UNTIL var<0
     end REPEAT;
     select parameter;
END
;;
delimiter ;; 
set @parameter=1;
call p_repeat(@parameter);
-- loop ··· end loop语句
drop procedure if exists p_loop;
delimiter;;
create procedure p_loop(in parameter int)
BEGIN
     declare var int;  
     set var = parameter; 
     LOOP_LABLE:loop
     set var = var - 1; 
     set parameter = parameter -2; 
     if var<0 THEN
   LEAVE LOOP_LABLE;
     END IF;
     end LOOP;
     select parameter;
END
;;
delimiter;;
set @parameter=4;
call p_loop(@parameter);
-- 游标
drop table if exists  person;
CREATE TABLE `person` (
  `id` int(11) NOT NULL DEFAULT '0',
  `age` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into person(age) value(1);
drop procedure if exists prc_test1;
delimiter ;;
create definer = root@localhost procedure prc_test1()
BEGIN
    declare var int;   
     /**跳出循环标识**/
   declare done INT DEFAULT FALSE;
     /**声明游标**/
   declare cur cursor for select age from person;
   /**循环结束设置跳出标识**/
   declare continue handler for not FOUND set done = true;
   /**打开游标**/
   open cur;
     LOOP_LABLE:loop
        FETCH cur INTO var;
        select var;
     if done THEN
   LEAVE LOOP_LABLE;
     END IF;
     end LOOP;
     /**关闭游标**/
   CLOSE cur;
END;
;;
delimiter ;;
call prc_test1();
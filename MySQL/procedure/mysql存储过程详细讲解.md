## mysql存储过程详细讲解及完整实例下载

 时间 2017-08-13 19:23:00  博客园精华区

原文[http://www.cnblogs.com/sztx/p/7354421.html][2]

### 一、存储过程概念

**1. ** 存储过程（Stored Procedure）是一组为了完成特定功能的SQL语句集。经编译后存储在数据库 中。

**2. ** 存储过程是数据库中的一个重要对象，用户通过指定存储过程的名字并给出参数（如果该存储过 程带有参数）来执行它。

**3. ** 存储过程是由流控制和SQL语句书写的过程，这个过程经编译和优化后存储在数据库服务器中。

**4. ** 存储过程可由应用程序通过一个调用来执行，而且允许用户声明变量。

**5. ** 同时，存储过程可以接收和输出参数、返回执行存储过程的状态值，也可以嵌套调用。

### 二、存储过程优点

** 1. ** 增强了SQL语句的功能和灵活性

** 2. ** 不需要反复建立一系列处理步骤，保证了数据的完整性

** 3. ** 降低了网络的通信量，客户端调用存储过程只需要传存储过程名和相关参数即可，与传输SQL语 句相比自然数据量少了很多

** 4. ** 增强了使用的安全性，通过存储过程可以使没有权限的用户在控制之下间接地存取数据库，从而 保证数据的安全。

** 5. ** 可以实现集中控制，当规则发生改变时，只需要修改存储过程就可以。。、

### 三、存储过程缺点

** 1. ** 调试不是很方便。

** 2. ** 可能没有创建存储过程的权利。

** 3. ** 重新编译问题。

** 4. ** 移植性问题。

### 四、变量

##### 1.用户变量：
以”@”开始，形式为”@变量名。” 用户变量跟MySQL客户端是绑定的，设置的变量，只对当前用户使用的客户端生效.

##### 2.全局变量：
定义时，以如下两种形式出现，set GLOBAL 变量名 或者 set @@global.变量名。show global variables; 对所有客户端生效。只有super权限才可以设置全局变量。

##### 3.会话变量：
只对连接的客户端有效。一旦客户端失去连接，变量失效。show session variables;

##### 4.局部变量：
作用范围在begin到end语句块之间。

###### 4.1 在该语句块里设置的变量declare语句专门用于定义局部变量。de
clare numeric number(8,2)【MySQL的数据类型，如:int,float, date, varchar(length)】 default 9.95;

###### 4.2 变量赋值:
SET 变量名 = 表达式值 [,variable_name= expression ...]，set numeric=1.2或者SELECT 2.3 into @x；

### 五、mysql 存储程序

##### 1.基本语法：
create procedure 过程名 ([过程参数[,...]])[特性 ...] 过程体;先看基本例子

第一种：

```sql
    delimiter ;;
    create procedure proc_on_insert()
    begin
    end
    ;;
    delimiter
```

第二种：

```sql
    delimiter //
    create procedure proc_on_insert()
    begin
    end
    //
    delimiter ;;
```

#### 注意：

1).这里需要注意的是delimiter // 和delimiter ;;两句，delimiter是分割符的意思，因为MySQL默认以";"为分隔符，如果我们没有声明分割符，那么编译器会把存储过程当成SQL语句进行处理，则存储过程的编译过程会报错，所以要事先用delimiter关键字申明当前段分隔符，这样MySQL才会将";"当做存储过程中的代码。

2).存储过程根据需要可能会有输入、输出、输入输出参数，这里有一个输出参数s，类型是int型，如果有多个参数用","分割开。

3).过程体的开始与结束使用begin与emd进行标识。

##### 2..调用存储过程基本语法：
call sp_name()

##### 3.参数:
MySQL存储过程的参数用在存储过程的定义，共有三种参数类型,IN,OUT,INOUT,形式如：

    create procedure([[in |out |inout ] 参数名 数据类形...])

in输入参数:表示该参数的值必须在调用存储过程时指定，在存储过程中修改该参数的值不能被返回，为默认值

out 输出参数:该值可在存储过程内部被改变，并可返回

inout 输入输出参数:调用时指定，并且可被改变和返回

**3.1** in参数例子：

```sql
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
```

**3.2** out参数创建例子

```sql
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
```

**3.3** inout参数创建例子

```sql
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
```

**3.4** 存储过程中的IF语句（if then elseif then else end if）

```sql
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
```

**3.5** 存储过程中的case when then

```sql
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
```

**3.6** 存储过程中的while do … end while语句

```sql
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
```

**3.7** 存储过程中的repeat … until end repeat语句

```sql
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
```

这个REPEAT循环的功能和前面WHILE循环一样，区别在于它的执行后检查是否满足循环条件（until i>=5），而WHILE则是执行前检查（while i<5 do）。

不过要注意until i>=5后面不要加分号，如果加分号，就是提示语法错误。

**3.8** 存储过程中的loop ··· end loop语句

```sql
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
```

使用LOOP编写同样的循环控制语句要比使用while和repeat编写的要复杂一些：在循环内部加入了IF……END IF语句，在IF语句中又加入了LEAVE语句，LEAVE语句的意思是离开循环，LEAVE的格式是：LEAVE 循环标号。

##### 4.游标的使用 :定义游标 ，打开游标 ,使用游标 ,关闭游标例子

```sql
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
```

##### 5.MySQL存储过程的查询

**5.1.** 查看某个数据库下面的存储过程
```
select name from mysql.proc where db=’数据库名’;
```
或者
```
select routine_name frominformation_schema.routines where routine_schema='数据库名';
```
或者
```
show procedure status where db='数据库名';
```
**5.2.**  查看存储过程的详细
```
show create procedure 数据库.存储过程名;
```
##### 6、MySQL存储过程的修改
```
ALTER PROCEDURE:更改用CREATE PROCEDURE 建立的预先指定的存储过程，其不会影响相关存储过程或存储功能。
```
##### 7.删除存储过程
```
drop procedure sp_name //注释函数名
```

完整代码：

```sql
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
```

[2]: http://www.cnblogs.com/sztx/p/7354421.html

## 【mysql的编程专题①】流程控制与其他语法

来源：[https://segmentfault.com/a/1190000006063323](https://segmentfault.com/a/1190000006063323)

流程控制与内置函数,一般用在select的field字段上,或者用在函数,存储过程,触发器中;
如果用在select上就会随着query出来的row来隐式迭代;

## 注释与语句结束符
### 语句结束符

默认有两个：`;` 和`\g（只能在命令行中使用）`
可以使用delimiter 命令来修改语句结束符，例如： `delimiter $$`（注意，一般手工修改结束符后再手工改回原来默认值 ;）
### 注释

行注释：`#` 和`--[空格]`
块注释：`/* */`
## 变量的定义与输出
### 定义变量

MySQL中可以使用DECLARE关键字来定义变量。定义变量的基本语法如下：

```sql
DECLARE  var_name[,...]  type  [DEFAULT value] 
```

* 其中， DECLARE关键字是用来声明变量的；var_name参数是变量的名称，这里可以同时定义多个变量；type参数用来指定变量的类型；DEFAULT value子句将变量默认值设置为value，没有使用DEFAULT子句时，默认值为NULL。 **`只能用在存储过程或者函数内部`** 


在过程中定义的变量并不是真正的定义，你只是在BEGIN/END（即复合语句）块内定义了而已。注意这些变量和会话变量不一样，不能使用修饰符@你必须清楚的在BEGIN/END块中声明变量和它们的类型。变量一旦声明，你就能在任何能使用会话变量、文字、列名的地方使用。还需要注意的一点是，在一个块内，我们需要把所有要使用的变量先声明，才能在后面使用，并且不能在声明变量的语句间夹杂其他使用变量的语句，否会报语法错误。

```sql
CREATE PROCEDURE P5()
BEGIN
 DECLARE a INT;
 DECLARE b INT;
 SET a = 5;
 SET b = 5;
 INSERT INTO t VALUES(a);
 SELECT s1 FROM t WHERE s1>= b;
END;
-------------------------------------------------
mysql> CALL p5();
+----+
| s1 |
+----+
|  5 |
|  5 |
+----+
2 rows in set
 
Query OK, 0 rows affected
```

MySQL中可以使用SET关键字来为变量赋值。SET语句的基本语法如下:

```sql
SET  var_name = expr [, var_name = expr] ... 
```

MySQL中还可以使用SELECT…INTO语句为变量赋值。其基本语法如下：

```sql
SELECT  col_name[,…]  INTO  var_name[,…]  
FROM  table_name  WEHRE  condition 
```

其中，col_name参数表示查询的字段名称；var_name参数是变量的名称；table_name参数指表的名称；condition参数指查询条件。

```sql
-- 查看系统变量 show variables [like pattern]
show variables like "innodb%";

-- set 变量名=变量值; 注意：为了区分用户自定义变量和系统变量，需要在用户自定义变量名称前加@符号。例如 set @name=’John’;
-- 如果在存储过程或者函数中用DECLARE来预先定义了某个变量,后面的set可以不用加@,详见后文例子;
set @userTotel = (select count(*) from users); -- Set赋值用法的变量值也可是标量查询的结果

-- select 字段 from 表名 into @变量名
select nickname from users ORDER BY user_money desc limit 1 into @richName;
SELECT id,data INTO x,y FROM test.t1 LIMIT 1; -- 这个SELECT语法把选定的列直接存储到变量。因此，只有单一的行可以被取回

-- select @变量名:=变量值 与 select @变量名=变量值
set @who = 'zhouzhou';
select @who='小李'; -- 注意:此处不是赋值操作,而是变量的判断,如果@who变量是已经存在了,那就判断@who中的值是否等于'小李',返回0或1;如果@who的变量不存在就返回NULL;
```

**`注意`** 


* 变量的有效期为会话结束后，变量就失效（即断开连接后，变量失效）!

* 变量的作用域: 用户定义的变量是全局的。但在函数内定义的变量则是局部的。

* 变量的数据类型与字段的数据类型一致！



### 输出变量

```sql
select @who;
```
## 分支语句
### **`IF`** 

**`语法`** 

```sql
IF search_condition THEN statement_list
[ELSEIF search_condition THEN statement_list] ...
[ELSE statement_list]
END IF;
```

statement_list: 多条语句由`;`号隔开

**`实例`** 

```sql
delimiter $
CREATE PROCEDURE `hd`(IN `arg` TINYINT)
BEGIN
DECLARE `age` TINYINT DEFAULT 0;
SET `age` = `arg`;
IF `age`<20 THEN
SELECT "年轻人";
ELSEIF `age`<40 THEN
SELECT "青年人";
ELSELF
SELECT "OLD MAN";
END IF;
END$
```

```sql
create procedure proc_getGrade
(stu_no varchar(20),cour_no varchar(10))
begin 
declare stu_grade float;
select grade into stu_grade from grade where student_no=stu_no and course_no=cour_no;
if stu_grade>=90 then 
select stu_grade,'a';
elseif stu_grade<90 and stu_grade>=80 then 
select stu_grade,'b';
elseif stu_grade<80 and stu_grade>=70 then 
select stu_grade,'c';
elseif stu_grade<70 and stu_grade>=60 then 
select stu_grade,'d';
else 
select stu_grade,'e';
end if;
end 
```
### 三元表达式

```sql
SELECT IF(@a=1,'真','失败');
```
### IFNULL(字段,值)

```sql
select age,ifnull(age,"空") from c; -- 如果age的值为null就返回空;
```
### NULLIF(expr1,expr2)

```sql
-- 如果表达式1=表达式2，则返回null,否则返回第1个表达式 
SELECT NULLIF(5,5);  -- null

SELECT NULLIF(10,4); -- 10
```
### case

```sql
CASE case_value
WHEN when_value THEN statement_list
[WHEN when_value THEN statement_list] ...
[ELSE statement_list]
END CASE 
或者
CASE 
WHEN search_condition THEN statement_list
[WHEN search_condition THEN statement_list] ...
[ELSE statement_list]
END CASE 
```

**`Example1`** 

```sql
delimiter $
CREATE PROCEDURE `pro2`(INOUT `arg` INT)
BEGIN
DECLARE `i` INT DEFAULT 0;
SET `i` = `arg`;
CASE `i` 
WHEN 1 THEN
SELECT "sina";
WHEN 2 THEN
SELECT "baidu";
ELSE 
SELECT "163";
END CASE;
END;
$
delimiter ;
```

**`Example2`** 

```sql
delimiter $
CREATE PROCEDURE `pro3`(INOUT `arg` INT)
BEGIN
DECLARE `i` INT DEFAULT 0;
SET `i` = `arg`;
CASE
WHEN i = 1 THEN
SELECT "sina";
WHEN i = 2 THEN
SELECT "baidu";
ELSE 
SELECT "163";
END CASE;
END;
$
delimiter ;
```
## 循环
### leave

退出循环

```sql
LEAVE label -- 退出循环,注意如果要使用leave的话,循环就必须得带上leave;
```
### while

```sql
[begin_label:] WHILE search_condition DO
statement_list
END WHILE [end_label]
```

**`Example1`** 

```sql
delimiter $
CREATE PROCEDURE `createstu`(IN `num` INT)
BEGIN
DECLARE `i` INT DEFAULT 0;
DECLARE `yeard` DATE;
WHILE `num`>0 DO
SET `yeard` = DATE_SUB("2000-1-1",INTERVAL `i` DAY);
INSERT INTO `test` (`sname`,`birthday`) VALUES(MD5(`i`),`yeard`);
SET `i`=`i`+1;
SET `num`=`num`-1;
END WHILE;
END$
```
### loop

```sql
[begin_label:] LOOP
statement_list
END LOOP [end_label]
```

**`Example1`** 

```sql
delimiter $
create procedure t_loop()
begin 
declare i int;
set i = 0;
loop_label:loop 
insert into test(sname,birthday) values(md5(i),2005);
set i = i + 1;
if i > 100 then 
leave loop_label; -- 注意这里的label是必须的哦;
end if;
end loop;
end$
delimiter ;
```

loop是在执行后检查结果,while是在执行前检查结果

### repeat

```sql
[begin_label:] REPEAT
statement_list
UNTIL search_condition  -- REPEAT语句内的语句或语句群被重复，直至search_condition 为真。
END REPEAT [end_label]
```

**`Example1`** 

```sql
delimiter $
create procedure t_repeat()
begin 
declare i int;
set i = 100;
repeat 
insert into test(sname,birthday) values(md5(i),1988);
set i = i + 5;
until i > 10000 -- 注意until此处没有分号,是为和下面的end链接一起的;
end repeat;
end;$
```
## 其他
### INSERT INTO SELECT

**`语法`** 

```sql
Insert into Table2(field1,field2,...) select value1,value2,... from Table1
```

要求目标表Table2 **`必须存在`** ，如果目标table2已经存在了,并且和table1的结构一样的话,可以直接`Insert into Table2 select * from Table1`,如果结构不一样,就要Table2的字段对应Table1的字段

### SELECT INTO FROM

**`语法`** 

```sql
SELECT vale1, value2 into Table2 from Table1 
```


* 要求目标表Table2 **`不存在`** ，因为在插入时会自动创建表Table2，并将Table1中指定字段数据复制到Table2中。

* **`注意：`**  MySQL不支持Sybase SQL扩展：`SELECT ... INTO TABLE ....`。只支持`select 字段 from 表名 into @变量名`。



### replace into

```sql
replace into table (id,name) values('1','aa'),('2','bb') -- 此语句的作用是向表table中插入两条记录。如果主键id为1或2不存在就相当于insert into table (id,name) values('1','aa'),('2','bb') ,如果存在相同的值则不会插入数据  
```
### create...select

```sql
-- 创建表并插入它表的数据进来;
CREATE TABLE tdb_goods_brands (
  brand_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  brand_name VARCHAR (40) NOT NULL
) ENGINE = INNODB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '用户信息表' SELECT
  brand_name
FROM
  tdb_goods
GROUP BY
  brand_name;
```

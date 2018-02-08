## MySQL 视图和存储程序

来源：[http://www.jianshu.com/p/12fe4d39e821](http://www.jianshu.com/p/12fe4d39e821)

时间 2018-02-06 10:30:48


  
## MySQL 视图和存储程序    

存储程序：存储函数、存储过程、触发器和事件的总称。

存储例程：存储函数+存储过程。

触发器：与表关联，当这个表使用 INSERT、DELETE 和 UPDATE 语句进行修改时，它会自动执行。    

事件：根据计划在预定时刻自动执行。    

  
## 使用视图    

 
```sql
-- 根据表的部分列创建视图
CREATE VIEW vpress(ln,fn) AS
SELECT last_name,first_name FROM president;
-- 有些视图中，有的列是由聚合函数得到的，这样的视图不可更新。
```

  
## 使用存储程序    

  
### 复合语句 & 语句分隔符    

由一个 **`BEGIN`** 和 **`END`** 块构成，其间可以包含任意数量的语句。    

以下存储过程 **`返回两个结果集`**     

 
```sql
-- 临时将分隔符更改为 $
-- 原则：如果存储程序体包含了某些内容分号，那么应该重新定义分隔符。
DELIMITER $
CREATE PROCEDURE show_times()
BEGIN
  -- 块内的语句必须使用分号';'分隔
  SELECT current_timestamp AS 'Local Time';
  SELECT utc_timestamp AS 'UTC Time';
END $
-- 恢复成默认分隔符
DELIMITER ;
-- 执行
CALL show_times();
```

  
### 存储函数 & 存储过程    

存储函数：常用在 **`表达式中`** ，执行计算 **`返回一个值`**     

 
```sql
-- 确定有多少位总统出生于给定年份，并返回统计值
DELIMITER $
CREATE FUNCTION count_born_in_year(p_year INT)
RETURNS INT -- 表明其返回值数据类型
READS SQL DATA
-- 函数体
BEGIN
  RETURN (SELECT count(*) FROM president WHERE year(brith) = p_year);
END $
DELIMITER ;
-- 执行存储函数
SELECT count_born_in_year(1992);
```

存储过程：使用 **`CALL 语句`** 来调用的独立操作，不能用在表达式中。    

存储过程使用的 **`两种场景`** ：    

  

* 只需通过运算来实现 **`某种效果或动作`** ，不需要返回值      
* 运算结果需要 **`返回多个结果集`**       
    

```sql
-- 定义存储过程
DELIMITER $
CREATE PROCEDURE show_born_in_year(p_year INT)
BEGIN
  SELECT first_name,last_name,brith,death
  FROM president
  WHERE year(brith) = p_year;
END $
DELIMITER ;
-- 调用存储过程
CALL show_born_in_year(1995);
```

参数类型    

存储过程的参数分为 3 种类型：

  

*  **`IN 参数`** ：调用者会把一个值传递到过程里（ **`默认类型`** ）      
*  **`OUT 参数`** ：过程会返回一个值给调用者访问      
*  **`INOUT 参数`** ：允许调用者向过程传递一个值，然后再取回一个值      
    

```sql
-- 定义存储过程
CREATE PROCEDURE count_students_by_sex (OUT p_male INT,OUT p_fmale INT)
BEGIN
  SET p_male = (SELECT count(*) FROM student WHERE sex = 'M');
  SET p_fmale = (SELECT count(*) FROM student WHERE sex = 'F');
END;
-- 执行存储过程
CALL count_students_by_sex(@male_count,@fmale_count);
-- 查询变量值
SELECT @male_count,@fmale_count;
```

  
### 触发器    

触发器的作用：

  

* 可以检查或修改数据值，在被插入或者用来更新行之前（还可以用来 **`对输入数据进行过滤`** ）      
* 可以基于某个表达式来为列（包括那些只能使用常量默认值进行定义的列） **`提供默认值`** ，      
* 可以在删除或更新之前，先 **`检查行的当前内容`**       
    

语法如下：

 
```sql
CREATE TRIGGER trigger_name # 触发器的名字
{BEFORE | AFTER}            # 触发器激活的时机
{INSERT | UPDATE | DELETE}  # 激活触发器的语句
ON tbl_name                 # 关联表
FOR EACH ROW trigger_stmt;  # 触发器内容
```

  
#### 触发器属于表（删除某个表，那么 MySQL 会删除所有与之关联的触发器）

 
```sql
CREATE TABLE t(percent INT,dt DATETIME);
-- 创建触发器
-- 如果插入的值超出了 0~100 的范围，那么这个触发器将把该值转换成最近端点的那个值
DELIMITER $
CREATE TRIGGER bi_t BEFORE INSERT ON t
FOR EACH ROW BEGIN
  IF NEW.percent < 0 THEN SET NEW.percent = 0;
  ELSEIF NEW.percent > 100 THEN SET NEW.percent = 100;
  END IF;
  SET NEW.dt = current_timestamp(); -- 自动设置为当前的时间点
END $
DELIMITER ;
```

  
### 事件    

MySQL 有一个事件调度器，它可以 **`定时激活多个数据库操作`** 。    

事件：就是一个 **`与计划相关联的存储程序`** 。 **`计划`** 会定义事件执行的 **`时间或次数`** ，并且还可以定义事件 **`何时强行退出`** 。    

事件非常适合于执行那些 **`无人值守的系统管理任务`** 。    

默认情况下，时间调度器并不会运行，因此必须先启用它才能使用事件。

 
```sql
-- 查看事件调度器的状态
SHOW VARIABLES LIKE 'event_scheduler';

SET GLOBAL EVENT_SCHEDULER = 0; -- 关闭
SET GLOBAL EVENT_SCHEDULER = 1; -- 开启
```

示例：定时任务

 
```sql
-- 每 4 小时执行一次，将超过一天的行清除掉
-- 默认情况下，every 事件在被创建后会立刻开始第一次执行，并且会定时持续执行下去，永不停止
CREATE EVENT expire_web_session
-- 可选子句 starts datetime (第一次执行时间)和 ends datetime（最后一次执行时间）
ON SCHEDULE EVERY 4 HOUR
DO          -- 负责定义事件的语句体部分
  DELETE FROM web_session
  WHERE last_visit < current_timestamp - INTERVAL 1 DAY;
```

如果想创建一个只执行一次的时间，则应该使用 AT 调度类型替换 EVERY 类型。

 
```sql
-- 1小时之后，执行一次
CREATE EVENT ont_shot
ON SCHEDULE AT current_timestamp + INTERVAL 1 HOUR
DO ...
```

禁用某个事件

 
```sql
ALTER EVENT enent_name DISABLE; -- 禁用
ALTER EVENT enent_name ENABLE;  -- 启用
```

  
## 视图和存储程序的安全性    

默认情况下，服务器会使用 **`定义该对象的那个用户的权限`** 来检查访问权限。    

在定义存储程序或视图时，可以显式指定定义者的方法

 
```sql
-- definer = 'user_name'@'host_name' 或者 CURRENT_USER 
CREATE DEFINER = 'user_name'@'localhost' PROCEDURE count_student();
SELECT count(*) FROM student;
```

  

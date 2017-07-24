# [MySQL6：触发器][0]

**什么是触发器**

MySQL的触发器（trigger）和存储过程一样，都是嵌入到MySQL中的一段程序。触发器是由事件来触发某个操作，这些事件包括INSERT、UPDATE和DELETE语句。如果定义了触发程序，当数据库执行这些语句的时候就会激发触发器执行相应的操作，触发程序是与表有关的命名数据库对象，当表上出现特定事件时，将激活该对象。

**创建触发器**

触发器是个特殊的存储过程，不同的是， **执行存储过程要使用CALL语句来调用，而触发器的执行不需要使用CALL语句调用，也不需要手工启动，只要当一个预定义的事件发生的时候，就会被MySQL自动调用** 。比如对student表进行操作（INSERT、DELETE或UPDATE ）时就会激活它执行。

触发器可以查询其他表，而且可以包含复杂的SQL语句。它们主要用于满足复杂的业务规则或要求。可以创建只有一条语句的触发器，不过一般都是有多个执行语句的触发器用得比较多，即使单条语句的触发器，也可以使用多条语句的触发器的写法来写，看下有多个执行语句的触发器的基本写法：
```sql
    CREATE TRIGGER trigger_name trigger_time trigger_event
    ON tbl_name FOR EACH ROW trigger_stmt
```

解释一下：

1、trigger_name标识触发器名称，用户自行指定

2、trigger_time标识触发时机，可以指定为before或after

3、trigger_event标识触发事件，包括INSERT、UPDATE和DELETE

4、tbl_name标识建立触发器的表名，即在哪张表上建立触发器

5、trigger_stmt是触发器程序体，触发器程序可以使用begin和end作为开始和结束，中间包含多条语句

触发器程序可以使用begin和end作为开始和结束，中间包含多条语句。举个例子，还是以前的学生表：

```sql
    create table student
    (
        studentId            int                 primary key    auto_increment    not null,
        studentName        varchar(10)                                                            not null,
        studentAge        int,
        studentPhone    varchar(15)
    )
```
给学生表的studentName、studentAge、studentPhone三个字段都创建一个触发器表：
 
```sql
    create table triggerstudentname
    (
        t_studentName VARCHAR(10)
    );
    
    create table triggerstudentAge
    (
        t_studentAge int
    );
    
    create table triggerstudentPhone
    (
        t_studentPhone VARCHAR(15)
    );
```
创建一个触发器，每次插入一条数据之后分别往三张表插字段：
 
```sql
    CREATE TRIGGER trigger_student AFTER INSERT ON student
    FOR EACH ROW 
    BEGIN
        INSERT INTO triggerstudentname values(NEW.studentName);
        INSERT INTO triggerstudentAge values(NEW.studentAge);
        INSERT INTO triggerstudentPhone values(NEW.studentPhone);
    END
```
插入三条数据：
```sql
    insert into student values(null,'Jack', '11', '55555555');
    insert into student values(null,'Dicky', '14', '66666666');
    insert into student values(null,'Coco', '19', '77777777');
    commit;
```
看一下三张表的情况：

![][1]

![][2]

![][3]

没什么问题，执行结果显示，在向student表插入数据的同时，triggerstudentname、triggerstudentAge和triggerstudentPhone三张表里面的数据都发生了裱花，INSERT动作触发了触发器。

**查看触发器**

查看触发器是指查看数据库中已存在的触发器的定义、状态和语法信息等。可以通过命令来查看已经创建的触发器，有两种方式可以查看触发器，一一讲解。

**1、SHOW TRIGGERS语句查看触发器**

通过SHOW TRIGGERS查看触发器的语句如下：

    SHOW TRIGGERS;

用这个命令来查看一下触发器：

![][4]

有一部分没截取完整，解释一下主要部分的含义：

（1）Trigger表示触发器的名称，这里有两个触发器分别是tri_student和trigger_student

（2）Event表示激活触发器的事件，这里的两个触发事件为插入操作INSERT

（3）Table表示激活触发器的操作对象表，这里都为student表

（4）Statement表示激活触发器之后执行的语句

（5）Timing表示触发器触发的时间，分别为插入操作之前（BEFORE）和插入操作之后（AFTER）

**2、在triggers表中查看触发器信息**

SHOW TRIGGERS语句查看当前创建的所有触发器信息，这在触发器较少的情况下，使用该语句会很方便，如果要查看特定的触发器信息，可以直接从infomation_schema数据库中的triggers表中查找，通过SELECT命令查看，基本语法为：

    SELECT * FROM INFORMATION_SCHEMA.TRIGGERS WHERE condition;

比如：

    SELECT * FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_NAME = 'trigger_student';

可以自己查看一下命令运行的效果

**删除触发器**

使用DROP TRIGGER语句可以删除MySQL中已经定义的触发器，删除触发器的基本语法为：

    DROP TRIGGER [schema_name.]trigger_name;

schema_name表示数据库名称，是可选的，如果省略了schema_name，将从当前数据库中删除触发器，trigger_name是要删除的触发器的名称，比如：

    DROP TRIGGER school.tri_student

触发器tri_student删除成功

**使用触发器的注意点**

在使用触发器的时候需要注意： **对于相同的表，相同的事件只能创建一个触发器** 。

比如对表student创建了一个BEFORE INSERT触发器，那么如果对表student再次创建一个BEFORE INSERT触发器，MySQL将会报错，此时，只可以在表student上创建AFTER INSERT或者BEFORE UPDATE类型的触发器。灵活地运用触发器将为操作省去很多麻烦。

[0]: http://www.cnblogs.com/xrq730/p/4940579.html
[1]: ./img/801753-20151106194024180-1726234051.png
[2]: ./img/801753-20151106194031055-1212506838.png
[3]: ./img/801753-20151106194040211-1909966571.png
[4]: ./img/801753-20151106194600008-479514646.png
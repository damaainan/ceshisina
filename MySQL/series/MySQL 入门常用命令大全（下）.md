### MySQL 入门常用命令大全（下）

<font face=微软雅黑>
吕吕  2017-05-26  148  

接上篇[《MySQL入门常用命令大全（上）》][3]

## 3.4DQL 篇（数据查询篇）

### 3.4.1 查询记录

    #命令格式
    mysql> SELECT [ 列名称] FROM [ 表名称] where [ 条件]
    

**说明：**一个完整的 SELECT 语句包含可选的几个子句。SELECT 语句的定义如下：

    <SELECT clause> [<FROM clause>] [<WHERE clause>] [<GROUP BY clause>] [<HAVING clause>] [<ORDER BY clause>] [<LIMIT clause>]
    

（1） SELECT 子句是必选的，其它子句如 WHERE 子句、GROUP BY 子句等是可选的。

（2）一个 SELECT 语句中，子句的顺序是固定的。例如 GROUP BY 子句不会位于 WHERE 子句的前面。

（3） SELECT 语句执行顺序 ：

    开始->FROM 子句->WHERE 子句->GROUP BY 子句->HAVING 子句->ORDER BY 子句->SELECT 子句->LIMIT 子句->最终结果
    

每个子句执行后都会产生一个中间数据结果，即所谓的临时视图，供接下来的子句使用，如果不存在某个子句，就跳过。MySQL 和 SQL 执行顺序基本是一样的。

### 3.4.2 查看 SQL 执行时的警告

    mysql> show warnings;
    

## 3.5DML 篇（数据操作篇）

### 3.5.1 插入记录

    #命令格式
    mysql> insert into [tablename](column1,column2,...) values(value1,value2,...);
    
    #示例
    mysql> insert into student(name,school,grade,major,gender) values('lvlv0','software','first year','software engineering',0);
    

**注意：** 如果插入值刚好与数据表的所有列一一对应，那么可以省略书写插入的指定列，即：

    mysql> insert into student values(10000,'lvlv0','software','first year','software engineering',0);   
    

### 3.5.2 删除记录

    #命令格式
    mysql> delete from [tablename] where [condition];
    
    #示例，删除学号为 10000 的学生记录
    mysql> delete from student where from studentNo=1000;
    

### 3.5.3 修改记录

    #命令格式
    mysql> UPDATE [ 表名称] SET [ 列名称]=[ 新值] WHERE [ 条件];
    
    #示例，将学号为 10000 的学生性别改为女性
    mysql> UPDATE student SET gender=1 WHERE studentNo=1000;
    

这里只列出简单的增删改的 DML 操作，关于全面的基础的 DML 教程可参考 [W3CSchool SQL 教程][4]。

### 3.5.4 备份还原数据

（1）导出数据库的所有数据表

    #命令格式
    mysqldump -u 用户名 -p 数据库名 > 导出的文件名
    
    #示例
    mysqldump -u user_name -p123456 database_name > outfile_name.sql
    

（2）还原整个数据库  
在 mysql 客户端环境下，选择一个数据库之后，直接执行 sql 文件即可。 

    mysql> source file.sql;
    

（3）导出一个表到 sql 文件 

    #命令格式
    mysqldump -u 用户名 -p 数据库名 表名>导出的文件名
    
    #示例
    mysqldump -u user_name -p 123456 database_name table_name > outfile_name.sql   
    

（4）导入 sql 文件  
方法同还原整个数据库。

（5）将数据表导出到 csv 文件

    #命令格式
    mysql> SELECT * FROM [TABLE] INTO OUTFILE '[FILE]';
    #或者  
    mysql> SELECT * FROM [TABLE] INTO OUTFILE '[FILE]' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY 'n';  
    
    #示例
    mysql> select * from student into outfile "student.csv";
    

**说明：**  
1）如果不指明输出文件的输出目录的话，默认输出至数据库文件的存储目录。可使用命令 find / -name student.csv 来查看具体位置。  
2）如果使用指定 csv 文件输出目录的话，报如下错误：ERROR 1 (HY000): Can't create/write to file (Errcode: 13)，那么错误的原因是所在目录没有写权限。给所在的目录增加写权限即可。

（6）导入 csv 文件 

    #命令格式
    mysql> LOAD DATA INFILE '[FILE]' INTO TABLE [TABLE];  
    #或者  
    mysql> LOAD DATA INFILE '[FILE]' INTO TABLE [TABLE] FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY 'n';
    
    #示例
    mysql> load data infile '/root/dablelv/student.csv' into table student;    
    

**注意：**  
1）指定 csv 文件时使用绝对路径，否则 MySQL 默认从数据库存储的目录寻找；  
2）在导入时，如果出现如下错误：ERROR 13 (HY000) at line 1: Can't get stat of '/fullpath/file.csv' (Errcode: 13)，检查之后并非文件没有可读权限，请使用 load data local infile。加不加 local 的区别是：使用 LOCAL 关键词，表示从 mysql 客户端所在的客户主机读文件。不使用 LOCAL，从服务器读取文件。

（7）导入 excel 文件  
同导入 csv 文件的方法一致。注意导入文件时，都需要提前建立好与文件内各个段对应好的数据表。并且文件的路径需要使用引号括起来，双引号和单引号都可以。

## 3.6TCL 篇（事务控制篇）

说到事务控制，先说一下数据库的事务是什么以及 MySQL 中我们必知的知识点。

数据库事务(Database Transaction) ，是指对数据库的一系列操作组成的逻辑工作单元（unit）。

并非任意的对数据库的操作序列都是数据库事务。数据库事务拥有以下四个特性，习惯上被称之为 ACID 特性。 

**原子性（Atomicity）**：事务作为一个整体被执行，包含在其中的对数据库的操作要么全部被执行，要么都不执行。 

**一致性（Consistency）**：事务应确保数据库的状态从一个一致状态转变为另一个一致状态。一致状态的含义是数据库中的数据应满足完整性约束。 

**隔离性（Isolation）**：多个事务并发执行时，一个事务的执行不应影响其他事务的执行。

**持久性（Durability）**：已被提交的事务对数据库的修改应该永久保存在数据库中。

MySQL 中并非所有的数据库存储引擎都支持事务操作，比如 ISAM 和 MyISAM 就不支持。所以，使用事务处理的时候一定要确定所操作的表示是否支持事务处理，可以通过查看建表语句来查看有没有指定事务类型的存储引擎。当然，事务处理是为了保障表数据原子性、一致性、隔离性、持久性。这些都是要消耗系统资源的，要谨慎选择。

下面以数据库引擎 InnoDB 为例来演示命令行模式下事务的基本操作。

### 3.6.1 查看是否自动提交事务

MySQL 默认操作模式就是 autocommit 自动提交模式。自动提交事务由会话变量 autocommit 来控制，该变量只对当前会话有效。 

    mysql> select @@global.autocommit;
    mysql> show variables like '%autocommit%';
    

**说明：** 环境变量 autocommit 是用来控制一条 SQL 语句提交后是否自动执行，默认值是 1，表示在 mysql 命令行模式下每条增删改语句在键入回车后，都会立即生效，而不需要手动 commit。我们可以把它关闭，关闭之后就需要 commit 之后，SQL 语句才会真正的生效。

### 3.6.2 关闭和开启自动提交事务

（1）关闭自动提交事务  
MySQL 默认是自动提交事务的，关闭自动提交事务主要有两种方法。一种是临时关闭，只对当前会话有效。第二种是永久关闭，对所有会话有效。

**第一种：临时关闭。**

    #关闭当前会话的自动提交事务
    mysql> set autocommit = 0;
    

这样之后，所有增删改语句，都必须使用 commit 之后，才能生效；

**第二种：永久关闭。**  
通过修改配置文件 my.cnf 文件，通过 vim 编辑 my.cnf 文件，在 [mysqld]（服务器选项下）添加： 

    autocommit=0
    

保存，然后重启 mysql 服务即可生效。

（2）开启自动提交事务  
如果需要，可以开启自动提交模式。

    mysql> set autocommit=1;
    

或者将上面配置文件中的新增的 autocommit=0 删除即可。

### 3.6.3 事务执行的基本流程

首先创建一个测试数据表，建表语句如下：

mysql> create table transactionTest(a int primary key)engine=InnoDB;（1）开启一个事务

    mysql> start  transaction；      
    
    #或者
    mysql> begin;
    

（2）执行一系列增删改语句

    mysql> insert into transactionTest values(1);
    

（3）手动提交或者回滚  
**事务回滚：**

    mysql> rollback;
    

会滚后我们查看数据表中的数据时为：

    mysql> select * from transactionTest;
    Empty set (0.00 sec)
    

表中没有数据，回滚成功。

**手动提交事务：**

    mysql> commit;
    

提交后，再 rollback 的话已经不能回滚了，数据已经插入到数据表了。这里需要注意的是，在当前会话中，我们还没有手动 commit 提交事务的时候，表中的数据已经被插入了，但对于其它会话，如果事务隔离级别是 read commited，那么在 commit 之前，是查询不到新插入的记录的。

### 3.6.4 设置事务的保存点

    #设置折返点
    mysql> savepoit [pointname];
    
    #回滚至折返点
    mysql> rollback to savepoint [pointname];
    

发生在保存点之前的事务被提交，之后的被忽略。

### 3.6.5 设置事务的隔离级别

在数据库操作中，为了有效保证并发读取数据的正确性，提出了事务隔离级别。

数据库是要被广大客户所共享访问的，那么在数据库操作过程中很可能出现以下几种不确定情况。  
**（1）更新丢失（Update Lost）**  
两个事务都同时更新一行数据，一个事务对数据的更新把另一个事务对数据的更新覆盖了。这是因为系统没有执行任何的锁操作，因此并发事务并没有被隔离开来。

**（2）脏读（Dirty Read）**  
一个事务读取到了另一个事务未提交的数据操作结果。这是相当危险的，因为很可能所有的操作都被回滚。

**（3）不可重复读（Non-repeatable Read）**  
指的是同一事务中的多个 select 语句在读取数据时，前一个 select 和后一个 select 得到的结果不同。原因是第一次读取数据后，另外的事务对其做了**修改**，当再次读该数据时得到与前一次不同的值。

**（4）幻读（Phantom Read）：**  
幻读是不可重复读的特殊情况，事务中第二次读取的结果相对第一次读取的数据产生了新增，这是因为在两次查询过程中有另外一个事务进行**插入**造成的。

（对不可重复读和幻读的个人理解不同于《高性能 MySQL》，主要觉得《高性能 MySQL》解释的有很多疑点。个人理解，如有误，后续纠正）

为了解决上面的问题，于是就提出事务隔离。事务隔离的级别从低到高有四个级别分别是：Read uncommitted、Read committed、Repeatable read、Serializable。

**Read Uncommitted：读取未提交内容**  
所有事务都可以读取未提交事务的执行结果，也就是允许脏读。但不允许**更新丢失**。如果一个事务已经开始写数据，则另外一个事务则不允许同时进行写操作，但允许其他事务读该事务增删改的数据。该隔离级别可以通过"排他写锁"实现。

**Read Committed：读取提交内容**  
允许不可重复读取，但不允许脏读取。这可以通过"瞬间共享读锁"和"排他写锁"实现。读取数据的事务允许其他事务继续访问该行数据，但是未提交的写事务将会禁止其他事务访问该行。

**Repeatable Read：可重复读取**  
禁止不可重复读取和脏读取。这可以通过"共享读锁"和"排他写锁"实现。读取数据的事务将会禁止写事务（但允许读事务），写事务则禁止任何其他事务。按照这种说法，是不会出现幻读的，MySQL 的 InnoDB 的可重复读隔离级别和其他数据库的可重复读是有区别的，不会造成幻象读（phantom read）。

**Serializable：序列化**  
提供严格的事务隔离。它要求事务序列化执行，事务只能一个接着一个地执行，不能并发执行。仅仅通过"行级锁"是无法实现事务序列化的，必须通过其他机制保证新插入的数据不会被刚执行查询操作的事务访问到。

隔离级别越高，越能保证数据的完整性和一致性，但是对并发性能的影响也越大。对于多数应用程序，可以优先考虑把数据库系统的隔离级别设为 Read Committed。它能够避免脏读取，而且具有较好的并发性能。尽管它会导致不可重复读、幻读和第二类丢失更新这些并发问题，在可能出现这类问题的个别场合，可以由应用程序采用悲观锁或乐观锁来控制。

（1）查看全局和当前会话的事务隔离级别。

    #查看全局
    mysql> SELECT @@global.tx_isolation; 
    
    #查看当前会话
    mysql> SELECT @@session.tx_isolation; 
    mysql> SELECT @@tx_isolation;
    mysql> show variables like 'tx_isolation';
    

（2）更改事务的隔离级别

    SET [SESSION | GLOBAL] TRANSACTION ISOLATION LEVEL {READ UNCOMMITTED | READ COMMITTED | REPEATABLE READ | SERIALIZABLE}
    
    #默认更改当前会话事务隔离级别
    mysql> set tx_isolation='read-committed';
    

**注意：**不显示指明 session 和 global，默认的行为是带 session，即设置当前会话的事务隔离级别。如果使用 GLOBAL 关键字，为之后的所有新连接设置事务隔离级别。需要 SUPER 权限来做这个。MySQL 的 InnoDB 默认的事务隔离等级是 Repeatable Read。

## 3.7CCL（游标控制语言）

游标（cursor）是系统为用户开设的一个数据缓冲区，存放 SQL 语句的执行结果。每个游标区都有一个名字，用户可以用 SQL 语句逐一从游标中获取记录，并赋给主变量，交由主语言进一步处理。

游标的操作主要用于存储过程中用来书写过程化的 SQL，类似于 Oracle 的 PL/SQL。使用 SQL 的一般遵循的步骤如下。  
(1) 声明游标，把游标与 T-SQL 语句的结果集联系起来。  
(2) 打开游标。  
(3) 提取数据。  
(4) 关闭游标。

### 3.7.1 定义游标

    DECLARE cursor_name CURSOR FOR select_statement
    

这个语句声明一个游标。也可以在子程序中定义多个游标，一个块中的每一个游标必须命名唯一。

### 3.7.2 打开游标

    OPEN cursor_name
    

这个语句打开先前声明的游标。

### 3.7.3 根据游标提取数据

    FETCH cursor_name INTO var_name1,var_name2...
    

这个语句用指定的打开游标读取下一行（如果有下一行的话），并且推进游标指针至该行。

### 3.7.4 关闭游标

        CLOSE cursor_name
    

这个语句关闭先前打开的游标，注意，用完后必须关闭

上面简单的介绍了游标的基本用法，下面给出一个实例，下面是一个存储过程，里面用到游标，逐条更新数据（批量更新数据）。

```sql
    DELIMITER $  
    CREATE PROCEDURE updateBatchRecord()
    BEGIN
        DECLARE  no_more_record INT DEFAULT 0;
        DECLARE  pID BIGINT(20);
        DECLARE  pValue DECIMAL(15,5);
        DECLARE  cur_record CURSOR FOR   SELECT colA, colB from tableABC;  /*首先这里对游标进行定义*/
        DECLARE  CONTINUE HANDLER FOR NOT FOUND  SET  no_more_record = 1; /*这个是个条件处理,针对 NOT FOUND 的条件,当没有记录时赋值为 1*/
    
        OPEN  cur_record; /*接着使用 OPEN 打开游标*/
        FETCH  cur_record INTO pID, pValue; /*把第一行数据写入变量中,游标也随之指向了记录的第一行*/
    
        WHILE no_more_record != 1 DO
            INSERT  INTO testTable(ID, Value) VALUES (pID, pValue);
            FETCH  cur_record INTO pID, pValue;
        END WHILE;
        CLOSE  cur_record;  /*用完后记得用 CLOSE 把资源释放掉*/
    END$
    DELIMITER ;
```

关于 MySQL 存储过程的简单介绍，见博文 [MySQL 存储过程][5]。

## 3.8MySQL 常用功能

（1）显示当前时间 

    mysql> select now();
    

（2）显示年月日

    #显示年月日
    mysql> select current_date;
    
    #显示年
    mysql> select year(current_date);
    
    #显示月
    mysql> select month(current_date);
    
    #显示日
    mysql> select day(current_date);
    

（3）当计算器使用

    mysql> select ((4*4)/2) 25;
    

（4）连接字符串

    mysql> select CONCAT(f_name, " ", l_name) AS Name from employee where level>3;
    

结果：

     ---------------  
    | Name          | 
     ---------------  
    | Monica Sehgal | 
    | Hal Simlai    |
    

注意：这里用到 CONCAT() 函数，用来把字符串串接起来。另外，我们还用到以前学到的 AS 给结果列 CONCAT(f_name, " ", l_name) 起了个别名。

（5） IP 地址与无符号整型互相转换；利用 MySQL 内置函数完成转换。  
inet_aton:将 ip 地址转换成数字型；inet_ntoa:将数字型转换成 ip 地址。

    #示例
    mysql> select inet_ntoa(3232236292);
    
    mysql> select inet_aton('192.168.3.4');
    

# 4.小结

因工作用到 MySQL，作为一个 MySQL 的初学者，在短短的几个月中接触了一下，记录了一下工作中用到的 SQL 语句以及未来可能会用到的 MySQL 知识点，作为日后的参考手册。因内容繁杂，参考资料质量参差不齐，个人水平有限，错误在所难免，也请大家勿吝惜金言，给予批评指正。

本文持续更新中…

- - -

## 附录

### 附录 1：MySQL 权限类型

MySQL 的权限可以分为三种类型：数据库、数据表和数据列的权限。从 mysql.user 表中可查看用户权限信息，查看命令：

```sql
    mysql>select * from mysql.user where user='username' G;
```

列出权限有：

    Select_priv: 查看数据表；
    Insert_priv: 插入数据表；
    Update_priv: 更新数据表；
    Delete_priv: 删除数据表记录；
    Create_priv: 创建数据库和数据表；
    Drop_priv: 删除数据库和数据表；
    Reload_priv: 允许使用 FLUSH； 
    Shutdown_priv: 允许使用 mysqladmin shutdown；
    Process_priv: 允许使用 SHOW FULL PROCESSLIST 查看其他用户的进程；
    File_priv: 允许使用 SELECT… INTO OUTFILE and LOAD DATA INFILE；
    Grant_priv: 允许使用 grant 为用户授权；
    References_priv: 未来功能的占位符；现在没有作用；
    Index_priv: 确定用户是否可以创建和删除表索引；
    Alter_priv: 确定用户是否可以重命名和修改表结构；
    Show_db_priv: 确定用户是否可以查看服务器上所有数据库的名字，包括用户拥有足够访问权限的数据库。可以考虑对所有用户禁用这个权限，除非有特别不可抗拒的原因；
    Super_priv: 确定用户是否可以执行某些强大的管理功能，例如通过 KILL 命令删除用户进程，Allows use of CHANGE MASTER, KILL, PURGE MASTER LOGS, and SET GLOBAL SQL statements. Allows mysqladmin debug command. Allows one extra connection to be made if maximum connections are reached；
    Create_tmp_table_priv: 创建临时表；
    Lock_tables_priv: 可以使用 LOCK TABLES 命令阻止对表的访问修改；
    Execute_priv: 执行存储过程。此权限只在 MySQL5.0 及更高版本中有意义。
    Repl_slave_priv: 读取用于维护复制数据库环境的二进制日志文件。此用户位于主系统中，有利于主机和客户机之间的通信；
    Repl_client_priv: 确定用户是否可以确定复制从服务器和主服务器的位置；
    Create_view_priv: 创建视图。此权限只在 MySQL5.0 及更高版本中有意义；
    Show_view_priv: 查看视图或了解视图如何执行。此权限只在 MySQL5.0 及更高版本中有意义。关于视图的更多信息；
    Create_routine_priv: 更改或放弃存储过程和函数。此权限是在 MySQL5.0 中引入；
    Alter_routine_priv: 修改或删除存储函数及函数。此权限是在 MySQL5.0 中引入的；
    Create_user_priv: 执行 CREATE USER 命令，这个命令用于创建新的 MySQL 账户；
    Event_priv: 确定用户能否创建、修改和删除事件。这个权限是 MySQL 5.1.6 新增；
    Trigger_priv: 创建和删除触发器，这个权限是 MySQL 5.1.6 新增的；
    
    MySQL 特别的权限： 
    ALL: 允许做任何事(和 root 一样)； 
    USAGE: 只允许登录，其它什么也不允许做。
    

**参考文献**

[[1]SQL 四种语言：DDL,DML,DCL,TCL][6]  
[[2]SQL 语言的四种类型][7]  
[\[3\]结构化查询语言.百度百科][8]  
[\[4\]Mysql 命令行添加用户][9]  
[\[5\]MySQL 的权限有哪些][10]  
[\[6\]MYSQL——为现有字段添加自增属性][11]  
[\[7\]mysql 设置自动增加字段的初始值][12]  
[\[8\]MySQL 命令大全][13]  
[\[9\]MySQL 中的存储引擎讲解][14]  
[\[10\]mysql 的内存表和临时表][15]  
[\[11\]数据库事务.维基百科][16]  
[\[12\]mysql 事务处理用法与实例详解][17]  
[\[13\] 事务隔离级别.百度百科][18]  
[\[14\]MySQL 数据库事务隔离级别介绍(Transaction Isolation Level)][19]  
\[15\]高性能MySQL[M].北京:电子工业出版社,2010  
[\[16\]mysql 事务隔离级别脏读，不可重复读，幻象读][20]  
[\[17\]mysql 游标示例 mysql 游标简易教程][21]  
[\[18\]Mysql 高级特性：游标与流程控制][22]  
[\[19\]游标.百度百科][23]

</font>

[3]: https://cloud.tencent.com/community/article/263395
[4]: http://www.w3school.com.cn/sql/
[5]: http://blog.csdn.net/k346k346/article/details/51801977
[6]: http://www.cnblogs.com/henryhappier/archive/2010/07/05/1771295.html
[7]: http://blog.csdn.net/yingyujianmo/article/details/51152844
[8]: http://baike.baidu.com/link?url=OHxlHjGt6ICpm67B1ViQ2xYf1ZvKQM1FMtzwTRqt6kv77HQZTwNhyHgTmMsQE1KiAPoJ_eouiurtGcb7zJ3j93KYigBfLiet9uWPWTT2DI39TpRndQpDWiEGlQ81y6FF
[9]: http://my.oschina.net/u/1179414/blog/202377
[10]: http://zhidao.baidu.com/link?url=MFsIIpOC0zsMo6N0eNZHPEPbQQkFokPc6M3Ju8Br4F55vo_jRA4E7bIQYbeAlWHOm95q1fCGI2wuUs1UHXNQbL4xlyqULDMLjuvIuEAsAmG
[11]: http://blog.chinaunix.net/uid-20344928-id-3430090.html
[12]: http://blog.csdn.net/lanjianhun/article/details/8155690
[13]: http://www.cnblogs.com/zhangzhu/archive/2013/07/04/3172486.html
[14]: http://blog.csdn.net/qh_java/article/details/14045827
[15]: http://www.cnblogs.com/sunss/archive/2013/07/15/3191137.html
[16]: https://zh.wikipedia.org/wiki/数据库事务
[17]: http://www.111cn.net/database/mysql/53025.htm
[18]: http://baike.baidu.com/link?url=FZUbjp8jT0lXHm8QAjm-puyhXe4slJ9RnSpU70R1vwdR5OtKcXAUzVxXCK2cAJ_RDTC2XHaGBLT1LG_p6cKUU_
[19]: http://www.jb51.net/article/49596.htm
[20]: http://my.oschina.net/rotiwen/blog/177786?p=1
[21]: http://www.2cto.com/database/201412/359989.html
[22]: http://www.open-open.com/home/space-135360-do-blog-id-11781.html
[23]: http://baike.baidu.com/view/176618.htm
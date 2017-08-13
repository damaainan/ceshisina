### MySQL 入门常用命令大全（上）

<font face=微软雅黑>

吕吕  2017-05-26  416  

## 1.mysql 命令简介

mysql 命令是 MySQL 数据库的客户端应用程序，用于解释执行 SQL 语句。

## 2.SQL 的六种子语言

SQL（Structured Query Language）是结构化查询语言，也是一种高级的非过程化编程语言。SQL 语句可用于增删查改数据以及管理关系型数据库，并不局限于数据查询。

关于 SQL 的组成部分，网上的资料也是众说纷纭，有些将 SQL 分为四个子语言，DQL 纳入 DML 的一部分，也有些没有 TCL，因为没有参考到较权威的资料，目前按照百度百科的说法，SQL 主要由六个子语言组成，分别是 DDL、DQL、DML、DCL、TCL（TPL）和 CCL，下面将一一讲解。 

**（1） DDL（Data Definition Language，数据定义语言）**

DDL 用于定义数据库的三级结构，包括外模式、概念模式、内模式及其相互之间的映像，定义数据的完整性约束、安全控制等。使我们有能力创建、修改和删除表格。也可以定义索引和键，规定表之间的链接，以及施加表之间的约束。DDL 不需要 commit，主要操作有： 

<font color=red>
CREATE - 创建  
ALTER - 修改  
DROP - 删除  
TRUNCATE - 截断  
COMMENT - 注释  
RENAME - 重命名</font>

**（2） DQL（Data Query Language，数据查询语言）**

其语句，也称为"数据检索语句"，用以从表中获得数据，确定数据怎样在应用程序给出。保留字 SELECT 是 DQL（也是所有 SQL）用得最多的动词。常用的关键字有： 

<font color=red>
SELECT-从数据库表中获取数据  
FROM - 指定从哪个数据表或者子查询中查询  
WHERE - 指定查询条件  
GROUP BY - 结合合计函数，根据一个或多个列对结果集进行分组  
HAVING - 对分组后的结果集进行筛选  
ORDER BY - 对结果集进行排序  
LIMIT - 对结果集进行 top 限制输出  
UNION - 结果集纵向联合  
JOIN - 结果集横向拼接</font>

**（3） DML（Data Manipulation Language，数据操作语言）**

供用户对数据库中数据的操作，包括数据的增加、删除、更新，载入等操作。

<font color=red>
UPDATE - 更新数据库表中的数据  
DELETE - 从数据库表中删除数据  
INSERT INTO - 向数据库表中插入数据  
LOAD - 载入数据</font>

**（4） DCL（Data Control Language，数据控制语言）**

用于对数据库，数据表的访问角色和权限的控制等。 

<font color=red>
GRANT - 授权  
REVOKE - 撤销授权  
DENY - 拒绝授权</font>

**（5） TCL（Transaction Control Language，事务控制语言）**

又名 TPL（Transaction Process Language）事务处理语言，它能确保被 DML 语句影响的表的所有行及时得以更新。TPL 语句包括： 

<font color=red>
START TRANSACTION 或 BEGIN - 开始事务  
SAVEPOINT - 在事务中设置保存点，可以回滚到此处  
ROLLBACK - 回滚  
COMMIT - 提交  
SET TRANSACTION – 改变事务选项</font>

**（6） CCL（Cursor Control Language，游标控制语言）**

游标（cursor）是 DBMS 为用户开设的一个数据缓冲区，存放 SQL 语句的执行结果。游标控制语言对游标的操作主要有： 

<font color=red>
DECLARE CURSOR - 申明游标  
OPEN CURSOR - 打开游标  
FETCH INTO - 取值  
UPDATE WHERE CURRENT - 更新游标所在的值  
CLOSE CURSOR - 关闭游标</font>

下面将从上面的六个子语言来陈述 MySQL 的常用 SQL 语句和 MySQL 的相关命令。

## 3.MySQL 常用命令

本人使用 MySQL 版本是 5.1.61，下面所有的命令均在本版本 MySQL 测试通过，如遇到问题，请留言探讨！

### 3.1MySQL 准备篇

### 3.1.1 连接到本机上的 MySQL

首先打开 shell 命令终端或者命令行程序，键入命令 mysql -u root -p，回车后提示你输密码。注意用户名前可以有空格也可以没有空格，但是密码前必须没有空格，否则让你重新输入密码。 

或者直接给出密码：

    mysql -u[username] -p[password] #中括号中的变量需要替换指定值
    

如果刚安装好 MySQL，超级用户 root 是没有密码的，故直接回车即可进入到 MySQL 中了，MYSQL 的提示符是： mysql>。

### 3.1.2 连接到远程主机上的 MySQL

假设远程主机的 IP 为：110.110.110.110，用户名为 root,密码为 abc123。则键入以下命令：

    mysql -h 110.110.110.110 -u root -p 123;
    

注：u 与 root 之间可以不用加空格，其它也一样。

### 3.1.3 退出 MySQL

```sql
    mysql> exit;
    #或者
    mysql> quit;
```

### 3.1.4 查看 MySQL 版本

```sql
    mysql> select version();
    #或者
    mysql> status;
```

## 3.2DCL 篇（数据控制篇）

### 3.2.1 新建用户

```sql
    #命令格式
    mysql> create user [username]@[host] identified by [password];
    
    #示例
    mysql> create user lvlv@localhost identified by 'lvlv';
    mysql> create user lvlv@192.168.1.1 identified by 'lvlv';
    mysql> create user lvlv@"%" identified by 'lvlv';
    mysql> CREATE USER lvlv@"%";
```

说明：username – 你将创建的用户名, host – 指定该用户在哪个主机上可以登陆,如果是本地用户可用 localhost, 如 果想让该用户可以从任意远程主机登陆,可以使用通配符%. password – 该用户的登陆密码,密码可以为空,如果为空则该用户可以不需要密码登陆 MySQL 服务器。

### 3.2.2 删除用户

```sql
    #命令格式
    mysql> DROP USER [username]@[host];
    
    #示例
    mysql> DROP USER lvlv@localhost;
```

说明：删除用户时，主机名要与创建用户时使用的主机名称相同。

### 3.2.3 给用户授权

```sql
    #命令格式
    mysql> GRANT [privileges] ON [databasename].[tablename] TO [username]@[host];
    
    #示例
    mysql> GRANT select ON *.* TO lvlv@'%';
    mysql> GRANT ALL ON *.* TO lvlv@'%';
    
    #最后不要忘了刷新权限
    mysql> flush privileges;
```

说明：

（1） privileges —是一个用逗号分隔的赋予 MySQL 用户的权限列表，如 SELECT , INSERT , UPDATE 等（详细列表见该文末附录 1）。如果要授予所有的权限则使用 ALL；databasename – 数据库名，tablename-表名，如果要授予该用户对所有数据库和表的相应操作权限则可用*表示，如*.*。

（2）使用 GRANT 为用户授权时，如果指定的用户不存在，则会新建该用户并授权。设置允许用户远程访问 MySQL 服务器时，一般使用该命令，并指定密码。 

```sql
    #示例
    mysql> GRANT select ON *.* TO lvlv@'%' identified by '123456';
```

### 3.2.4 撤销用户权限

```sql
    #命令格式
    mysql> REVOKE [privileges] ON [databasename].[tablename] FROM [username]@[host];
    
    #示例
    mysql> REVOKE SELECT ON *.* FROM lvlv@'%';
    mysql> REVOKE ALL ON *.* FROM 'lvlv'@'%';
```

说明: 

（1） privilege, databasename, tablename – 同授权部分。

（2）假如你在给用户 'pig'@'%' 授权的时候是这样的(或类似 的):GRANT SELECT ON test.user TO 'pig'@'%', 则在使用 REVOKE SELECT ON *.* FROM 'pig'@'%'; 命令并不能撤销该用户对 test 数据库中 user 表的 SELECT 操作。相反,如果授权使用的是 GRANT SELECT ON *.* TO 'pig'@'%'; 则 REVOKE SELECT ON test.user FROM 'pig'@'%'; 命令也不能撤销该用户对 test 数据库中 user 表的 select 权限。

具体信息可以用命令 SHOW GRANTS FOR 'pig'@'%'; 查看。

### 3.2.5 查看用户权限

方法一：可以从 mysql.user 表中查看所有用户的信息，包括用户的权限。 

```sql
    mysql>select * from mysql.user where user='username' G
```

方法二：查看给用户的授权信息。

```sql
    #命令格式
    mysql> show grants for [username]@[host];
    
    #示例
    mysql> show grants for lvlv@localhost;
    mysql> show grants for lvlv;
```

说明：不指定主机名称，默认为任意主机"%"。

### 3.2.6 修改用户密码

方法一：使用 SQL 语句。 

```sql
    #命令格式：
    mysql> SET PASSWORD FOR [username]@[host]= PASSWORD([newpassword]);
    
    #示例
    mysql> set password for lvlv@localhost=password('123456');
```

如果是当前登录用户： 

```sql
    mysql> SET PASSWORD = PASSWORD("newpassword");
```
方法二：使用服务端工具 mysqladmin 来修改用户密码。

```sql
    #命令格式
    mysqladmin -u[username] -p[oldpassword] password [newpassword]
    
    #示例
    mysqladmin -ulvlv -p123456 password "123321"
```

## 3.3DDL 篇（数据定义篇）

### 3.3.1 创建数据库

```sql
    #命令格式
    mysql> create database [databasename];
    
    #示例
    mysql> create database Student;
```

### 3.3.2 删除数据库

```sql
    #命令格式
    mysql> drop database [databasename];
    
    #示例
    mysql> drop database Student;
```

### 3.3.3 查看所有数据库

```sql
    mysql> show databases;
```

### 3.3.4 查看当前数据库

```sql
    mysql> select database();
    
    #或者
    mysql> status;
```

### 3.3.5 连接数据库

```sql
    #命令格式
    mysql> use [databasename]
    
    #示例
    mysql> use Student;
```

### 3.3.6 创建数据表

命令格式： 

```sql
    mysql> create table [ 表名] ( [ 字段名 1] [ 类型 1] [is null] [key] [default value] [extra] [comment],
    ...
    )[engine] [charset];
```

**说明：**上面的建表语句命令格式，除了表名，字段名和字段类型，其它都是可选参数，可有可无，根据实际情况来定。is null 表示该字段是否允许为空，不指明，默认允许为 NULL；key 表示该字段是否是主键，外键，唯一键还是索引；default value 表示该字段在未显示赋值时的默认值；extra 表示其它的一些修饰，比如自增 auto_increment；comment 表示对该字段的说明注释；engine 表示数据库存储引擎，MySQL 支持的常用引擎有 ISAM、MyISAM、Memory、InnoDB 和BDB(BerkeleyDB)，不显示指明默认使用 MyISAM；charset 表示数据表数据存储编码格式，默认为 latin1。

**存储引擎是什么？**其实就是如何实现存储数据，如何为存储的数据建立索引以及如何更新，查询数据等技术实现的方法。

以学生表为例，演示数据表的创建。

**学生表设计：**

字段(Field)  | 类型(Type)  |  可空(Null)  |  键(Key) | 默认值(Default)  |  其他(Extra)
-|-|-|-|-|-
学号（studentNo）|    INT UNSIGNED  |   N |   PRI NULL  |  auto_increment
姓名（name）    | VARCHAR(12) | N  | N  | NULL  |  
学院（school）  | VARCHAR(12) | N  | N  | NULL  |  
年级（grade）   | VARCHAR(12) | N  | N  | NULL  |  
专业（major）   | VARCHAR(12) | N  | N  | NULL  |  
性别（gender）  | Boolean | N  |  N |  NULL |

建表语句是：

```sql
    mysql> create table if not exists student(
        studentNo int unsigned not null comment '学号' auto_increment,
        name varchar(12) not null comment '姓名',
        school varchar(12) not null comment '学院',
        grade varchar(12) not null comment '年级',
        major varchar(12) not null comment '专业',
        gender boolean not null comment '性别',
        primary key(studentNo)
    )engine=MyISAM default charset=utf8 auto_increment=20160001;
```

**说明：** 上面的建表语句需要注意三点。第一，可以使用 if not exists 来判断数据表是否存在，存在则创建，不存在则不创建。第二，设置主键时可以将 primary key 放在字段的后面来修饰，也可以另起一行单独来指定主键。第三，设置自增时，可以指定自增的起始值，MySQL 默认是从 1 开始自增，比如 QQ 号是从 10000 开始的。

关于 MySQL 支持的数据类型，可参考 [MySQL 数据类型][3]

### 3.3.7 查看 MySQL 支持的存储引擎和默认的存储引擎

```sql
    #查看所支持的存储引擎
    mysql> show engines;
    
    #查看默认的存储引擎
    mysql> show  variables  like '%storage_engine';
```

### 3.3.8 删除数据表

    mysql> drop table [tablename];
    

### 3.3.9 查看数据表结构

    mysql> desc [tablename];
    
    #或者
    mysql> describe [tablename];
    

查看上面创建的 student 数据表的结构如下：

![][4]

### 3.3.10 查看建表语句

```sql
    mysql> show create table [tablename]
```

### 3.3.11 重命名数据表

```sql
    mysql> rename table [tablename] to [newtablename];
```

### 3.3.12 增加、删除和修改字段自增长

（1）删除字段自增长

```sql
    #命令格式
    mysql>alter table [tablename] change [columnname] [columnname] [type];
    
    #示例，取消 studentNo 的自增长
    mysql>alter table student change studentNo studentNo int(10) unsigned;
```

说明：注意列名称要重复一次，即需要将列的名称写两次。

（2）增加字段自增长

```sql
    #命令格式
    mysql>alter table [tablename] modify [columnname] [type] auto_increment;
    
    #或者与上面删除字段自增长相反
    mysql>alter table [tablename] change [columnname] [columnname] [type] auto_increment;
    
    #示例，添加 studentNo 自增长
    mysql>alter table student modify studentNo int(10) unsigned auto_increment;
```

说明：添加自增长的列必须为 NOT NULL 及 PRIMARY KEY（UNIQUE）属性。如果不是，需添加相应定义。

（3）修改自增长起始值

```sql
    #命令格式
    mysql> alter table [tablename] auto_increment=[value];
    
    #示例，设置 studentNo 从 10000 开始自增
    mysql> alter table [tablename] auto_increment=10000;
```

**注意：**设定的起始值 value 只能大于已有的 auto_increment 的整数值，小于的值无效。  
show table status like 'table_name' 或者 show create table [tablename] 可以看到 auto_increment 这一列现有的起始值。

### 3.3.13 增加、删除和修改数据表的列

（1）增加列

```sql
    #命令格式
    mysql>alter table [tablename] add column [columnname] [columdefinition];
    
    #示例，为数据表 student 增加家乡 hometown
    mysql>alter table student add column hometown varchar(32) comment '家乡';
```

（2）删除列

```sql
    #命令格式
    mysql>alter table [tablename] drop column [columnname];
```

（3）重命名列

```sql
    #命令格式
    mysql>alter table [tablename] change [columnname] [newcolumnname] [type];
```

（4）修改列属性

```sql
    #命令格式
    mysql> alter table [tablename] modify [columnname] [newdefinition];
    
    #示例，修改 home 类型为 varchar(64) 且不允许 NULL
    mysql> alter table student modify home varchar(64) not null;
```

### 3.3.13 添加、删除和查看索引

（1）添加索引

```sql
    #命令格式
    mysql> alter table [tablename] add index [indexname](字段名 1,字段名 2…);
    
    #示例，为数据表 student 数据列 studentNo 添加索引
    mysql> alter table student add index index_studentNo(studentNo);
    #或者
    mysql> alter table student add index(studentNo);
```

**说明：** 上面示例的第二种方法，如果不显示指明索引名称的话，默认以列名称作为索引的名称。添加索引是为了提高查询的速度。

（2）查看索引

```sql
    mysql> show index from [tablename];
```

（3）删除索引

```sql
    #命令格式
    mysql> alter table [tablename] drop index [indexname];
    
    #示例
    mysql> alter table student drop index index_studentNo;
```

### 3.3.14 创建临时表

```sql
    #命令格式
    mysql> create temporary table [ 表名] ( [ 字段名 1] [ 类型 1] [is null] [key] [default value] [extra] [comment],...);
    
    #示例
    mysql> create temporary table pig(i int);
```

**说明：**  
（1）创建临时表与创建普通表的语句基本是一致的，只是多了一个 temporary 关键； 

（2）临时表的特点是：表结构和表数据都是存储到内存中的，生命周期是当前 MySQL 会话，会话结束后，临时表自动被 drop；

（3）注意临时表与 Memory 表（内存表）的区别是： 

    * （a） Memory 表的表结构存储在磁盘，临时表的表结构存储在内存；  
    * （b） show tables 看不到临时表，看得到内存表；  
    * （c）内存表的生命周期是服务端 MySQL 进程生命周期，MySQL 重启或者关闭后内存表里的数据会丢失，但是表结构仍然存在，而临时表的生命周期是 MySQL 客户端会话。
    * （d）内存表支持唯一索引，临时表不支持唯一索引；  
    * （e）在不同会话可以创建同名临时表，不能创建同名内存表。
    

### 3.3.15 创建内存表

与创建表的命令格式相同，只是显示的在后面指明存储引擎为 MEMORY。

```sql
    #命令格式
    mysql> create temporary table [ 表名] ( [ 字段名 1] [ 类型 1] [is null] [key] [default value] [extra] [comment],...)engine=memory;
    
    #示例
    mysql> create table pig(i int)engine=memory;
```

### 3.3.17 修改数据表的存储引擎

```sql
    mysql> alter table [tablename] type|engine=[enginename];
    
    #示例，将数据表 test 存储引擎设置为 InnoDB
    mysql> alter table test type=InnoDB;
    #或者
    mysql> alter table test engine=InnoDB;
```

</font>

[3]: http://www.runoob.com/mysql/mysql-data-types.html
[4]: ./img/1495702900586_5494_1495702902266.jpg
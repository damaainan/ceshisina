# [MySQL笔记汇总][0]

[目录]

    MySQL笔记汇总
    一、mysql简介
       数据简介
       结构化查询语言
    二、mysql命令行操作
    三、数据库(表)更改
       表相关
       字段相关
       索引相关
       表引擎操作
    四、数据库类型
       数字型
       字符串型
       日期型
       NULL值
    五、数据字段属性
    六、数据库索引
       主键索引（PRIMARY KEY）
       唯一索引（UNIQUE）
       常规索引（INDEX）
       全文索引（FULLTEXT）
    七、数据表的类型
    八、字符集设置
    九、mysql查询
       数据操作(DML)语言
       数据查询(DQL)语言
       查询语法言
       查询语法
    十、PHP中使用MYSQL
    

## 一、mysql简介

### 数据简介

数据库是计算机应用系统中的一种专门管理数据资源的系统。

数据库就是一组经过计算机整理后的数据，存储在一个或者多个文件中，而管理这个数据库的软件就称为数据库管理系统。

主流的软件开发中应用数据库有IBM的DB2，Oracle，Informix，Sybase，SQL Server，PostgreSQL，MySQL，Access，FoxPro和Teradata等。

PHP脚本程序作为MySQL服务器的客户机程序，是通过PHP中的MySQL扩展函数，对MySQL服务器中存储的数据进行获取，插入，更新及删除等操作。

### 结构化查询语言

SQL（Structured Query Language）是一种专门用于查询和修改数据库里的数据，以及对数据库进行管理和维护的标准化语言。SQL语言结构简洁，功能强大，简单易学。

无论是Oracle，Sybase，Informix，SQL Server这些大型的数据库管理系统，  
还是像Visual Foxpro，PowerBuilder这些PC上常用的数据库开发系统，都支持SQL语言作为查询语言。

**SQL语言包含四个部分**：

1. 数据定义语言（DDL）：用于定义和管理数据对象，包括数据库，数据表等。例如：CREATE，DROP，ALTER等。
1. 数据操作语言（DML）：用于操作数据库对象中所包含的数据。例如：INSERT，UPDATE，DELETE语句。
1. 数据查询语言（DQL）：用于查询数据库对象中所包含的数据，能够进行单表查询，连接查询，嵌套查询，以及集合查询等各种复杂程度不同的数据库查询，并将数据返回客户机中显示。例如：SELETE。
1. 数据控制语言（DCL）：是用来管理数据库的语言，包括管理权限及数据更改。例如：GRANT，REVOKE，COMMIT，ROLLBACK等。

## 二、mysql命令行操作

准备：  
1、 windows下添加mysql环境变量，方便使用。示例：

    PATH = C:\program files\mysql\bin;

如果配置出错，Windows会提示

    'mysql' 不是内部或外部命令，也不是可运行的程序或批处理文件。

配置好后不用重启，重新打开CMD即可。

2、cmd里连接mysql

    mysql -h localhost -u root -p
    
    ##之后输入密码即可 
    ##本机-h localhost可以省略
    
    mysql -u root -p

进入mysql交互界面后：

> 注意：  
> 1.每个SQL命令都需要使用分号来完成  
> 2.可以将一行命令拆成多行  
> 3.可以通过\c来取消本行命令  
> 4.可以通过\q、exit、ctrl+c或者quit来退出当前客户端  
> 5.使用'help;' 或者 '\h' 查看帮助.

    help select;
    help grant;

3、 显示所有数据库

    show databases;

4、使用某个数据库  
命令：use 数据库名;

    use mydb;

5、显示选中数据库所有表

    show tables;

6、查询显示某个表内数据库内容  
命令：select * from 数据库表名;  
如果想纵向显示，可以加参数\G

    select * from  user \G;

7、显示某个数据表结构

    命令：describe 数据库表名，或者desc 数据库表名;
    
    ##示例
    describe user;
    desc user;

8、数据库表内容管理

    插入数据：INSERT INTO 表名称[(字段1,字段2,...)] VALUE(值1,值2,...)；
    查询数据：SELECT 字段1,字段2,… FROM 表名称
    更改数据：UPDATE 数据表 set 字段名称=新修改的值 [WHERE 条件]
    删除数据：DELETE FROM 表名称 [WHERE 条件] 

9、数据库及表操作

    创建数据库 CREATE DATABASE [IF NOT EXISTS] 数据库名称；
    删除数据库 DROP DATABASE [IF EXISTS] 数据库名称；
    
    创建数据表：CREATE TABLE [IF NOT EXISTS] 表名称(字段1信息，字段2信息…字段N信息)[ENGINE=MyISAM DEFAULT CHARSET=UTF8];
    删除数据表：DROP TABLE [IF EXISTS] 数据表名称；
    修改表结构：ALTER TABLE 数据表名称 相关操作；

示例：

    create table 表名(
       字段名 类型 [字段约束],
       字段名 类型 [字段约束],
       字段名 类型 [字段约束],
       ...
      );
    
    mysql> create table stu(
        -> id int unsigned not null auto_increment primary key,
        -> name varchar(8) not null unique,
        -> age tinyint unsigned,
        -> sex enum('m','w') not null default 'm',
        -> classid char(6)
        -> );
    Query OK, 0 rows affected (0.05 sec)

10、创建新用户并授权

    格式：grant 允许操作 on 数据库名.表名 to 账号@来源 identified by '密码';

实例：创建zhangsan账号，密码123，授权lamp61库下所有表的增/删/改/查数据,来源地不限

    grant select,insert,update,delete on lamp61.* to zhangsan@'%' identified by '123';

例如：

    GRANT INSERT ON *.* TO yjc@"%" IDENTIFIED BY "123";//只具有插入数据库权限
    
    grant select,insert,update,delete on lamp61.* to zhangsan@'%' identified by '123';//具有增删查改权限

11、其它

    显示创建数据表sql语句：show create table 表名;

12、备份与恢复

* 备份数据库

    命令：MYSQLDUMP –u用户名(根用户) –p密码 db_name >  存放路径级/文件名.sql
    （不是在mysql控制台执行，而要退出控制台在DOS下执行）

例：

    mysqldump -uroot -p12345 mydata1>mydata1.sql
    mysqldump –u root –p12345 mydb > D:/mydb.sql;
* 恢复数据库  
前提：要创建一个空数据库

步骤：  
1、进入mysql控制台,创建一个空数据库,然后使用use选中那个空数据库  
2、使用命令：SOURCE 存放路径/文件名.sql (在Mysql控制台执行)

    mysql> create database bbs87;
    Query OK, 1 row affected (0
    
    mysql> use bbs87;
    Database changed
    mysql> source mydata1.sql;

注意：在DOS里若不指定备份恢复路径，均相对于所打开的DOS窗口路径

## 三、数据库(表)更改

使用帮助：DOS下使用HELP ALERT;

    HELP ALERT TABLE;

### 表相关

1、修改表名

    alter table 旧表名 rename as 新表名;
    
    ## 示例
    alter table user rename as bbs_user;

2、设置字段自增的初始值

    alter table 表名 auto_increment=初始值;
    
    ## 示例
    alter table user  auto_increment=20;

3、复制表（仅结构）  
原理是使用like创建一个与目标表一模一样的数据表。可以完整复制结构，包括主键。

    alter table user_geo_line rename as user_geo_line_20150729;
    create table user_geo_line like user_geo_line_20150729;

4、修改表注释

    ALTER table user COMMENT '用户表';

### 字段相关

1、添加字段

    alter table 表名 add 字段名 字段类型(长度） 属性 [索引];
    
    ##示例
    alter table user add qq int(10);
    
    alter table user add qq int(10) after id;
    
    ALTER TABLE `user`
    ADD `age` int NOT NULL DEFAULT '1' COMMENT '年龄' AFTER `name`,
    COMMENT='用户名';

2、删除字段

    alter table 表名 drop 字段名；
    
    ##示例
    alter table user drop qq;

3、修改字段  
1) 只能修改属性

    alter table 表名 modify 字段名 属性 [索引]；
    
    ##示例
    alter table user modify email varchar(30) not null;

2) 既能修改字段名,又能修改属性

    alter table 表名 change 原字段名 新字段名  属性 [索引]；
    
    ##示例
    alter table user change email youxiang varchar(30) not null;

3)更改字段排序

    ALTER TABLE 表名 MODIFY 字段名1 数据类型 FIRST ｜ AFTER 字段名2;

其中：

> 字段名1：表示需要修改位置的字段的名称。  
> 数据类型：表示“字段名1”的数据类型。  
> FIRST：指定位置为表的第一个位置。  
> AFTER 字段名2：指定“字段名1”插入在“字段名2”之后。

示例：

    alter table user MODIFY  id int(10) FIRST; #更改排序为首列
    
    alter table user MODIFY  age int(10) AFTER id; 

### 索引相关

1、查看当前表索引

    show indexes from 表名
    
    ##示例
    show indexes from user;

2、添加普通索引

    alter talble 表名 ADD INDEX/UNIQUE/PRIMARY KEY (字段);
    
    ##示例
    alter table user add index(username);

3、删除索引

    alter table 表名 drop index 索引名称;
    
    ##示例
    alter table user drop index username;

4、添加主键索引  
方法一：

    alter table user add id int(10) unsigned not null auto_increment, add primary key (id);

方法二：

    alter table user add id int(10) unsigned not null auto_increment,
    alter table user add primary key (id);

5、删除主键索引

1)先去除auto_increment

    alter table user modify id int not null;

2)再删除主键索引

    alter table  user drop primary key;

### 表引擎操作

1、 查看系统支持的存储引擎

    show engines;

2、查看表使用的存储引擎  
两种方法：

    show table status from db_name where name='table_name';
    show create table table_name;

3、 修改表引擎方法

    alter table table_name engine=innodb;

4、 关闭Innodb引擎方法  
1) 关闭mysql服务： net stop mysql  
2) 找到mysql安装目录下的my.ini文件：  
找到default-storage-engine=INNODB 改为default-storage-engine=MYISAM  
找到#skip-innodb 改为skip-innodb  
3) 启动mysql服务：net start mysql## 四、数据库类型

### 数字型

    TINYINT 1字节 非常小的整数 带符号值：-128~127 无符号值：0~255
    
    SMALLINT 2字节 较小的整数 带符号值：-32768~32767 无符号值：0~65535
    
    MEDIUMINT    3字节 中等大小的整数    带符号值：-8388608~8388607 无符号值：0~16777215
    
    INT 4字节 标准整数 带符号值：-2147483648~2147483647 无符号值：0~4294967295（10位数）
    
    BIGINT 8字节 大整数 带符号值：-2^63~2^63-1 无符号值：0~2^64-1
    
    FLOAT 4或8字节 单精度浮点数 最小非零值：+- 1.175494351E-38 最大非零值：+- 3.402823466E+38
    
    DOUBLE 8字节 双精度浮点数 最小非零值：+- 2.225073E-308 最大非零值：+- 1.797693E+308
    
    DECIMAL 自定义 以字符串形式表示的浮点数 取决于存储单元字节数

常用：

1. TINYINT(2) 用来表示flag,type等状态值；
1. INT 用来存储id,uid,时间等；
1. DECIMAL(6,2) 用来存储价钱等有小数的值；
1. BIGINT(14) 用来存储大于11位长度的int值。

注意：

1. 整型INT后面的数字可以省略。
1. INT(3)、SMALLINT(3)等整型后面的数字不会影响数值的存储范围，只会影响显示

### 字符串型

    CHAR[(M)] M字节 定长字符串 M字节
    
    VARCHAR[(M)] L+1字节 可变字符串 M字节
    
    TINYBLOB,TINYTEXT   L+1字节 非常小的BLOB(二进制大对象)和文本串 2^8-1字节
    
    BLOB,TEXT L+2字节 小BLOB和文本串 2^16-1字节
    
    MEDIUMBLOB,MEDIUMTEXT L+3字节 中等的BLOB和文本串 2^24-1字节
    
    LONGBLOB,LONGTEXT L+4字节 大BLOB和文本串 2^32-1字节
    
    ENUM('value1','value2'…) 1或2字节 枚举：可赋予某个枚举成员 65535个成员
    
    SET('value1','value2'…) 1,2,3,4或8字节 集合：可赋予多个集合成员    64个成员

常用：

1. VARCHAR(25) 用来存储标题、名称、网址、号码等常用值;
1. CHAR(32) 用来存储固定长度的值,如MD5加密后的密码等;
1. TEXT 用来存储文章段落。

注意：

1. CHAR,VARCHAR,TEXT用得比较多;md5加密后是32位，常用CHAR(32)。
1. 在使用CHAR和VARCHAR类型时，当我们传入的实际的值的长度大于指定的长度，字符串会被截取至指定长度。
1. 在使用CHAR类型时，如果我们传入的值的长度小于指定长度，实际长度会使用空格补至指定长度。
1. 在使用VARCHAR类型时，如果我们传入的值的长度小于指定长度，实际长度即为传入字符串的长度，不会使用空格填补。
1. BLOB区分大小写，TEXT不区分大小写。

### 日期型

    DATE 3 字节 "YYYY-MM-DD" 格式表示的日期值 1000-01-01~9999-12-31
    
    TIME    3 字节 "hh:mm:ss" 格式表示的时间值 -838:59:59-838:59:59
    
    DATETIME 8 字节 "YYYY-MM-DD hh:mm:ss" 格式 1000-01-01 00：00：00~9999-12-31
    
    TIMESTAMP 4 字节 "YYYYMMDDhhmmss" 格式表示的时间戳 19700101000000-2037年的某个时刻
    
    YEAR 1 字节 "YYYY”格式的年份值  1901~2155

常用：

1. DATETIME 用来存储0000-00-00 00:00:00格式的时间;
1. DATE 用来存储0000-00-00格式的时间;
1. 通常用int来存储11位时间戳。

注意：

1. 存储日期时，我们可以使用整型INT来进行存储时间戳，这样做便于我们进行日期的计算。

### NULL值

    1. NULL意味着“没有值”或“未知值”
    2. 可以测试某个值是否为NULL
    3. 不能对NULL值进行算术计算
    4. 对NULL值进行算术运算，其结果还是NULL
    5. 0或NULL都意味着假，其余值都意味着真

注意：

1. 给字段设置默认值是良好的习惯。通常数字类型设置0或者其它值;状态类设置1；字符串类设置'';日期设置为0000-00-00 00:00:00。

## 五、数据字段属性

UNSIGNED 无符号型，即非负数。  
ZEROFILL 只能用于设置数值类型，在数值之前会自动用0补齐不足的位数。  
AUTO_INCREMENT 用于设置字段的自动增长属性，每增加一条记录，该字段的值会自动加1。  
NULL和NOT NULL  
默认为NULL，即插入值时没有在此字段插入值，默认为NULL值，如果指定了NOT NULL，则必须在插入值时在此字段填入值。  
DEFAULT 可以通过此属性来指定一个默认值，如果没有在此列添加值，那么默认添加此值。

注意：

1. 字段默认not null，且最好设置默认值；
1. 该字段确认不会出现负值,设置UNSIGNED；
1. 主键必需设置AUTO_INCREMENT。

## 六、数据库索引

### 唯一索引（UNIQUE）

唯一索引规定字段不能重复。如果重复，操作失败。

### 主键索引（PRIMARY KEY）

1. 最好为每张表指定一个主键，但不是必须指定。
1. 一个表只能指定一个主键，而且主键的值不能为空
1. 主键可以有多个候选索引（例如NOT NULL，AUTO_INCREMENT）

常用id作为主键索引，并且not null,auto_increment。

主键索引是特殊的唯一索引，除了规定字段不能重复，查询速度最快，因为直接根据主键去查询数据的。主键索引还能设置自增。

### 常规索引（INDEX）

常规索引技术是关系数据查询中最重要的技术，如果要提升数据库的性能，  
索引优化是首先应该考虑的，因为它能使我们的数据库得到最大性能方面的提升。

可用于username等

### 全文索引（FULLTEXT）

## 七、数据表的类型

MySQL支持MyISAM、InnoDB、HEAP、BOB、ARCHIVE、CSV等多种数据表类型，  
在创建一个新MySQL数据表时，可以为它设置一个类型。

MyISAM和InnoDB两种表类型最为重要：

1. MyISAM数据表类型的特点是成熟、稳定和易于管理。
1. MyISAM表类型会产生碎片空间，要经常使用OPTIMIZE TABLE命令去清理表空间
1. MyISAM不支持事务处理，InnoDB支持
1. MyISAM不支持外键，InnoDB支持
1. MyISAM表类型的数据表效率更高
1. MyISAM表类型的数据表会产生三个文件，InnoDB表类型表默认只会产生一个文件。

## 八、字符集设置

命令行下：

    SET NAMES 'utf8'; 
    SET NAMES 'GBK'; 

支持的字符集查看：

    SHOW CHARSET;

默认字符集设置：  
1) my.ini设置

    [client]
    port=3306
    
    [mysql]
    default-character-set=utf8
    character-set-server=utf8
    default-storage-engine=MyISAM

命令行方式：  
2) 还有一种修改mysql默认字符集的方法，就是使用mysql的命令

    mysql> SET character_set_client = utf8 ;  
    mysql> SET character_set_connection = utf8 ;   
    mysql> SET character_set_database = utf8 ;   
    mysql> SET character_set_results = utf8 ;    
    mysql> SET character_set_server = utf8 ;   
     
    mysql> SET collation_connection = utf8 ;  
    mysql> SET collation_database = utf8 ;   
    mysql> SET collation_server = utf8 ; 

一般就算设置了表的mysql默认字符集为utf8并且通过UTF-8编码发送查询，你会发现存入数据库的仍然是乱码。问题就出在这个connection连接层上。解决方法是在发送查询前执行一下下面这句：

    SET NAMES 'utf8'; 

它相当于下面的三句指令：

    SET character_set_client = utf8;  
    SET character_set_results = utf8;   
    SET character_set_connection = utf8; 

## 九、mysql查询

### 数据操作(DML)语言

1、INSERT INTO（增）

    格式：INSERT INTO 表名(字段1,字段2,字段3....) values('值1', '值2','值3'....); 

> 说明：表字段不用加引号，值建议都加引号，字符必须加引号。

例1：

    INSERT INTO user(id,username,age,sex,detial) VALUES('0','vilin','27','男','this is a boy');

说明：当给出字段时，可以不按顺序指定，但值顺序必须和给出的顺序一致。

例2：

    INSERT INTO user VALUES('0','vilin','27','男','this is a boy');

说明：如果表名不给出字段名，VALUES部分要按顺序全部给出值。

例3：

    INSERT INTO user(id,name,age,sex,detial) VALUES
    ('null','lili','29','女','my name is lili'),
    ('0','xueer','23','女','my name  is xueer');

说明：可以使用VALUES 一次插入多条语句，语句之间用逗号隔开。

例4：

    INSERT INTO users1 select * from users;

说明：将表users的值全部插入到users1,但是要求表结构要一致。

例5：

    INSERT INTO users1(name,sex) select name,sex from users;

说明：将搜索的值插入到users1,但是要求字段个数要对应。

2、UPDATE（改）

    格式：UPDATE 表名 SET 字段名1='值1',字段名2='值2'... [where 条件]；

说明：如果不写条件默认更新SET后面字段中所有的值（需谨慎）。可以更新多个值，需逗号隔开。例：

    UPDATE bbs_user SET name='xiaoli' where id=9;
    UPDATE bbs_user SET name='zhenzhen' where id>20 AND id<30;

3、DELETE（删）

    格式：DELETE FROM 表名 [where 条件]

说明：一定要给出条件，不然会删除所有表中数据。

例1：

    DELETE FROM bbs_user;

说明：删除表中所有数据，清空表。  
注意：如果想清空表且将自增也设置为1，请使用truncate 表名。示例：

    truncate bbs_user;

例2：

    DELETE FROM bbs_user WHERE id=2;

说明：删除id=2的内容。

### 数据查询(DQL)语言

SELECT（查）

    格式：SELECT [ALL|DISTINCT] {*|table.*|[table.]field1[AS alias1][,[table.]field2[AS alias2][,…]]} FROM 表名

说明：  
[ALL|DISTINCT] 默认为ALL，DISTINCT关键字取消重复的数据；  
*|table.*|[table.]field1如果查询所有数据，用*即可；如果指定某字段，使用数据表.字段；  
AS alias1可以给指定表、指定字段起别名。as可省略。

    #例1：
    SELECT * FROM bbs_user; 查询bbs_user所有数据
    
    #例2：
    SELECT id,name,sex FROM bbs_user; 查询bbs_user指定字段数据
    
    #例3：
    SELECT id,name,sex FROM bbs_user WHERE  name='vilin'； #查询name='vilin'的行数据。
    
    #例4：
    SELECT id,name,sex FROM bbs_user WHERE  name='vilin' && id=20;
    #查询条件为name='vilin'并且id=20 的数据
    
    #例5：
    SELECT id,name,sex FROM bbs_user WHERE id >5 limit 2,2;
    limit 2,2 偏移量,显示条数   #显示结果为id= 8,id=9的数据。limit 2 显示条数。

实例：分页limit 偏移量公式

    $page:当前页码，
    $pagesize：每页显示数据条数
    $offset=($page-1)*$pagesize 查询的偏移量
    
    实际使用：
    limit $offset,$pagesize;

实例：分页显示指定条数  
查询id>0的数据，数据中包含username,userpwd 总共要查1条

    select username,userpwd from bbs_users where id>0 limit 1;

查询id>0的数据，从第二条开始取一条，只包含username，userpwd

    select username,userpwd from bbs_users where id>0 limit 1,1;

例6：DISTINCT 过滤username 字段中重名的行。

    SELECT DISTINCT username FROM bbs_user;

例7： AS 别名

    SELECT name as 姓名, age as年龄 FROM bbs_user;

说明：AS 别名 AS可以省略，中间用空格。  
省略写法：

    SELECT name  姓名, age 年龄 FROM bbs_user; 

例8： 多表联合查询：

    SELECT * FROM user,bbs WHERE  bbs.username=user.username

说明：查询user和bbs两个表中username字段相同的数据

    SELECT u.username  uname,o.username,o.bianhao FROM bbs_order  o,bbs_users  u WHERE o.username = u.username;

AS可省略。

例9：ORDER BY 排序：

    SELECT * FROM 表名 [WHERE 条件]  ORDER BY  字段1 ASC|DESC,字段2 ASC|DESC...; 

默认ASC省略正序小到大，DESC从大到小。示例：

    SELECT  id,name,age FROM bbs_user WHERE id>10 && id<20 ORDER BY id DESC;

例10：自联合查询：

    SELECT a.id aid,a.name aname,b.id bid, b.name bname FROM cats a, cats b WHERE b.pid=a.id;

例11：嵌套查询：（子查询）

    SELECT * FROM products where cid in(select id from cats where name like 'j%');

### 查询语法

1、LIMIT

> limit 5表示从第0条开始，查找5条记录  
> limit 1,5表示从第1条开始，查找5条记录

2、ORDER BY  
ORDER BY后面可以接一列或多列用于排序的字段，表示按什么字段排序。例：

    select * from user order by id; #按id升序形式显示user中所有内容

使用DESC或ASC关键字设计字段排序的方式。默认升序（ASC），降序使用DESC。

3、逻辑操作符  
AND或&&，OR或||，XOR，NOT或!

4、比较操作符

    比较符：
    >,<,=,>=,<=,!=或<>,<=>
    
    是否为空：
    IS NULL,IS NOT NULL
    
    区间查询：
    BETWEEN，NOT BETWEEN
    
    说明：a BETWEEN b AND c 若a在b和c之间，为真
    例子：select * from user where id between 1 and 5;
    
    模糊搜索：
    LIKE，NOT LIKE 用于模糊查找
    % 表示0个或任意多个字符     
    _表示一个字符 
    
    例1：select * from bbs where  title  like '%php%';//查询含有php的title
    例2：select * from bbs where  title  like 'php%';//查询php开头的title
    
    
    IN查询
    示例：a in(b1,b2….)    若a等于b1,b2,b3,…中的某一个，则为真
    例子：select * from user where age in(22,23,26);

5、统计函数  
COUNT()返回满足SELECT语句中指定条件的 记录数  
例子：

    select count(*) from user;
    select count(id) from user;

SUM()返回一列的总和。例子：

    select sum(jifen) from user;

AVG()返回一列的平均值  
MAX()返回一列中最大的值  
MIN()返回一列中最小的值

6、GROUP BY 对查询结果分组  
例子：

    select sum(jifen) from user group by username;

7、HAVING 对分组查询的结果列表进一步过滤  
例子：

    select sum(jifen) as total from user group by username having total>2;

说明：having后面的字段只能是新生成的表里的字段，否则会提示无此栏目。

8、查询优化  
EXPLAIN语句是检测索引和查询能否良好匹配的简便方法。

    EXPLAIN SELECT * FROM table WHERE a>’0’ AND b<’1’ ORDER BY c; 

## 十、PHP中使用MYSQL

> 总体步骤

1. 连接MySQL数据库,判断是否连接成功  
2.选择数据库  
3.设置字符集  
4.准备SQL语句  
5.向MySQL服务发送SQL语句  
6.判断执行结果  
7.处理结果集  
8.释放结果集，关闭数据库连接

1、数据库连接  
mysql_connect()连接数据库，并返回一个连接资源

    #格式： 
    mysql_connect(主机名，用户，密码); 
    
    #例：
    $db = mysql_connect("localhost","root","12345");

2、连接错误处理

    mysql_error()获取刚刚（最后）执行数据库操作的错误信息
    mysql_errno()获取刚刚（最后）执行数据库操作的错误号;错误号为0表示没有错误

3、选择数据库  
mysql_select_db()选择一个数据库，等同于use 库名语句。例：

    mysql_select_db("mydb");

4、设置字符集

    mysql_set_charset("utf8");

等同于：

    mysql_query("set names utf8");

5、数据库查询  
mysql_query()发送一条sql语句  
sql语句若是查询，则返回结果集，其他则返回boolean值表示执行是否成功。例：

    mysql_query("select * from user");

6-1、解析结果集

    mysql_fetch_assoc();以关联式数组解析结果集
    mysql_fetch_row();以索引式数组解析结果集
    mysql_fetch_array();以关联和索引两种方式数组解析结果集，也可以指定第二参数来定义返回格式：MYSQL_BOTH(关联和索引)/MYSQL_NUM(索引)/MYSQL_ASSOC(关联)
    mysql_fetch_object();以对象方式解析结果集

6-2、解析结果集数目

    mysql_num_rows(结果集); 获取结果集中的数据条数
    mysql_num_fields(结果集);获取结果集中的列数(字段数量)
    mysql_result(); 
    mysql_result($result,0,3); //获取第1条数据的第4列中的值
    mysql_result($result,1,2); //获取第2条数据的第3列中的值
    mysql_result($result,5,4); //获取第6条数据的第5列中的值

7、释放结果集

    mysql_free_result(结果集名);

8、关闭数据库连接

    mysql_close();

9、其它

    mysql_insert_id取得上一步 INSERT 操作产生的 ID 
    mysql_affected_rows取得前一次 MySQL 操作所影响的记录行数
    关联的 INSERT，UPDATE 或 DELETE 查询所影响的记录行数。

实例：

    <?php
    //1、连接数据库
    $conn=mysql_connect("localhost","root","123456");
    
    //2、选择数据库
    mysql_select_db("bbs87");
    
    //3、设置数据库编码
    mysql_set_charset("utf8");
    
    //4、准备SQL语句
    $sql = "select * from artical ";
    
    //5、执行SQL语句并获取结果集
    $result = mysql_query($sql);
    
    //6、判断查询是否成功
    if($result && mysql_num_rows($result)>0){
    
    //7、处理结果
    while($row=mysql_fetch_assoc($result)){
        print_r($row);
    }
    
    //8、是否结果集并关闭数据库
    mysql_free_result($result);
    }
    mysql_close();
    

tips:

    PHP Mysql中判断操作是否成功：
    
    1插入操作
    mysql_insert_id()>0
    
    2删除操作
    mysql_affected_rows()>0
    
    3修改操作
    mysql_affected_rows()>0
    
    4select 查询
    mysql_num_rows($result)>0

**作者：飞鸿影~**

**出处：**http://52fhy.cnblogs.com/

[0]: http://www.cnblogs.com/52fhy/p/4930719.html
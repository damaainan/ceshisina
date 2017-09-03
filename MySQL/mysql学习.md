
## 一

    show databases;
    

### 创建数据库 

create DATABASE 数据库名称 

    create DATABASE databasetest;
    

### 选择数据库 

use 数据库名称 

    use databasetest;
    ------------
    Database changed;切换成功
    

### 查看当前数据库名称 

    SELECT DATABASE();
    

### 删除数据库 

drop DATABASE 数据库名称 

    drop DATABASE databasetest;
    

### 数据库状态 

    status;
    
    --------------
    mysql  Ver 14.14 Distrib 5.7.17, for Win64 (x86_64)
    
    Connection id:          5
    Current database:
    Current user:           root@localhost
    SSL:                    Not in use
    Using delimiter:        ;
    Server version:         5.7.17-log MySQL Community Server (GPL)
    Protocol version:       10
    Connection:             127.0.0.1 via TCP/IP
    Server characterset:    utf8
    Db     characterset:    utf8
    Client characterset:    gbk
    Conn.  characterset:    gbk
    TCP port:               3306
    Uptime:                 2 hours 25 min 34 sec
    
    Threads: 2  Questions: 57  Slow queries: 0  Opens: 114  Flush tables: 1  Open tables: 107  Queries per second avg: 0.006
    --------------


## 二数据引擎


    show engines
    
    +--------------------+---------+----------------------------------------------------------------+--------------+------+------------+
    | Engine             | Support | Comment                                                        | Transactions | XA   | Savepoints |
    +--------------------+---------+----------------------------------------------------------------+--------------+------+------------+
    | InnoDB             | DEFAULT | Supports transactions, row-level locking, and foreign keys     | YES          | YES  | YES        |
    | MRG_MYISAM         | YES     | Collection of identical MyISAM tables                          | NO           | NO   | NO         |
    | MEMORY             | YES     | Hash based, stored in memory, useful for temporary tables      | NO           | NO   | NO         |
    | BLACKHOLE          | YES     | /dev/null storage engine (anything you write to it disappears) | NO           | NO   | NO         |
    | MyISAM             | YES     | MyISAM storage engine                                          | NO           | NO   | NO         |
    | CSV                | YES     | CSV storage engine                                             | NO           | NO   | NO         |
    | ARCHIVE            | YES     | Archive storage engine                                         | NO           | NO   | NO         |
    | PERFORMANCE_SCHEMA | YES     | Performance Schema                                             | NO           | NO   | NO         |
    | FEDERATED          | NO      | Federated MySQL storage engine                                 | NULL         | NULL | NULL       |
    +--------------------+---------+----------------------------------------------------------------+--------------+------+------------+
    9 rows in set (0.00 sec)
    

或者 

    show engines \G
    
    mysql> show engines \G
    *************************** 1. row ***************************
          Engine: InnoDB
         Support: DEFAULT
         Comment: Supports transactions, row-level locking, and foreign keys
    Transactions: YES
              XA: YES
      Savepoints: YES
    *************************** 2. row ***************************
          Engine: MRG_MYISAM
         Support: YES
         Comment: Collection of identical MyISAM tables
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 3. row ***************************
          Engine: MEMORY
         Support: YES
         Comment: Hash based, stored in memory, useful for temporary tables
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 4. row ***************************
          Engine: BLACKHOLE
         Support: YES
         Comment: /dev/null storage engine (anything you write to it disappears)
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 5. row ***************************
          Engine: MyISAM
         Support: YES
         Comment: MyISAM storage engine
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 6. row ***************************
          Engine: CSV
         Support: YES
         Comment: CSV storage engine
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 7. row ***************************
          Engine: ARCHIVE
         Support: YES
         Comment: Archive storage engine
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 8. row ***************************
          Engine: PERFORMANCE_SCHEMA
         Support: YES
         Comment: Performance Schema
    Transactions: NO
              XA: NO
      Savepoints: NO
    *************************** 9. row ***************************
          Engine: FEDERATED
         Support: NO
         Comment: Federated MySQL storage engine
    Transactions: NULL
              XA: NULL
      Savepoints: NULL
    9 rows in set (0.00 sec)
    

* Engine 引擎的名称
* Support 是否支付YES表示支持，NO表示不支持
* Comment 评价或者备注 Defalut表示，默认支持的引擎
* Transactions 是否支持事务，YES表示支持，NO表示不支持
* XA 所有支持的分布式是否符合XA规范，YES表示支持，NO表示不支持
* Savepoints 是否支持事务处理中的保存点，YES表示支持，NO表示不支持

或者

show variables like ‘have%’

    mysql> show variables like 'have%';
    +------------------------+----------+
    | Variable_name          | Value    |
    +------------------------+----------+
    | have_compress          | YES      |
    | have_crypt             | NO       |
    | have_dynamic_loading   | YES      |
    | have_geometry          | YES      |
    | have_openssl           | DISABLED |
    | have_profiling         | YES      |
    | have_query_cache       | YES      |
    | have_rtree_keys        | YES      |
    | have_ssl               | DISABLED |
    | have_statement_timeout | YES      |
    | have_symlink           | YES      |
    +------------------------+----------+
    11 rows in set, 1 warning (0.00 sec)
    

* Variable_name 引擎名称
* value 是否支持YES支持，NO不支持,DISABLED表示支持但未启用

### 查看默认引擎 

show variables like ‘%storage_engine%’ 

    mysql> show variables like '%storage_engine%';
    +----------------------------------+--------+
    | Variable_name                    | Value  |
    +----------------------------------+--------+
    | default_storage_engine           | InnoDB |
    | default_tmp_storage_engine       | InnoDB |
    | disabled_storage_engines         |        |
    | internal_tmp_disk_storage_engine | InnoDB |
    +----------------------------------+--------+
    4 rows in set, 1 warning (0.00 sec)
    

InnoDB 为默认引擎

### 修改默认引擎 

my.ini文件

    [mysqld]
    
    # The next three options are mutually exclusive to SERVER_PORT below.
    # skip-networking
    
    # enable-named-pipe
    
    # shared-memory
    
    # shared-memory-base-name=MYSQL
    
    # The Pipe the MySQL Server will use
    # socket=MYSQL
    
    # The TCP/IP Port the MySQL Server will listen on 默认端口号
    port=3306
    
    # Path to installation directory. All paths are usually resolved relative to this.  服务器的默认安装目录
    # basedir="C:/Program Files/MySQL/MySQL Server 5.7/"
    
    # Path to the database root   数据库数据文件的目录
    datadir=C:/ProgramData/MySQL/MySQL Server 5.7\Data
    
    # The default character set that will be used when a new schema or table is
    # created and no character set is defined  修改服务器默认字符
    character-set-server=utf8
    
    # The default storage engine that will be used when create new tables when
    # 这里修改默认引擎
    default-storage-engine=INNODB
    

修改后重启Mysql服务

---
## 三帮忙文档

help contents 

    mysql> help contents;
    You asked for help about help category: "Contents"
    For more information, type 'help <item>', where <item> is one of the following
    categories:
       Account Management
       Administration
       Compound Statements
       Data Definition
       Data Manipulation
       Data Types
       Functions
       Functions and Modifiers for Use with GROUP BY
       Geographic Features
       Help Metadata
       Language Structure
       Plugins
       Procedures
       Storage Engines
       Table Maintenance
       Transactions
       User-Defined Functions
       Utility
    

帮助文档的目录列表

### 查看数据类型 

help data types 

    mysql> help data types;
    You asked for help about help category: "Data Types"
    For more information, type 'help <item>', where <item> is one of the following
    topics:
       AUTO_INCREMENT
       BIGINT
       BINARY
       BIT
       BLOB
       BLOB DATA TYPE
       BOOLEAN
       CHAR
       CHAR BYTE
       DATE
       DATETIME
       DEC
       DECIMAL
       DOUBLE
       DOUBLE PRECISION
       ENUM
       FLOAT
       INT
       INTEGER
       LONGBLOB
       LONGTEXT
       MEDIUMBLOB
       MEDIUMINT
       MEDIUMTEXT
       SET DATA TYPE
       SMALLINT
       TEXT
       TIME
       TIMESTAMP
       TINYBLOB
       TINYINT
       TINYTEXT
       VARBINARY
       VARCHAR
       YEAR DATA TYPE
    

### 查看整数 

    mysql> help int;
    Name: 'INT'
    Description:
    INT[(M)] [UNSIGNED] [ZEROFILL]
    
    A normal-size integer. The signed range is -2147483648 to 2147483647.
    The unsigned range is 0 to 4294967295.
    
    URL: http://dev.mysql.com/doc/refman/5.7/en/numeric-type-overview.html
    
    
    mysql> help tinyint;
    Name: 'TINYINT'
    Description:
    TINYINT[(M)] [UNSIGNED] [ZEROFILL]
    
    A very small integer. The signed range is -128 to 127. The unsigned
    range is 0 to 255.
    
    URL: http://dev.mysql.com/doc/refman/5.7/en/numeric-type-overview.html

---

## 四表的基本操作

create table 表名

create table if not exists 表名

    mysql> create database company;
    Query OK, 1 row affected (0.00 sec)
    
    mysql> use company;
    Database changed
    mysql> create table if not exists t_dept(
        -> deptno int,
        -> dname varchar(20),
        -> loc varchar(40));
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> show tables;
    +-------------------+
    | Tables_in_company |
    +-------------------+
    | t_dept            |
    +-------------------+
    1 row in set (0.00 sec)
    
    mysql>
    

## 显示当前库下的所有表 

show tables; 

    mysql> show tables;
    +-------------------+
    | Tables_in_company |
    +-------------------+
    | t_dept            |
    +-------------------+
    1 row in set (0.00 sec)
    

## 查看表的结构 

describe 表名

简写

desc 表名

    mysql> describe t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    

## 查看表的详细 

show create table 表名 

    mysql> show create table t_dept;
    +--------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Table  | Create Table                                                                                                                                                       |
    +--------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | t_dept | CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +--------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    

show create table t_dept \G 

    mysql> show create table t_dept \G
    *************************** 1. row ***************************
           Table: t_dept
    Create Table: CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    1 row in set (0.00 sec)
    

## 删除表 

drop table 表名

drop table if exists 表名

    mysql> drop table if exists t_dept;
    Query OK, 0 rows affected (0.12 sec)
    
    mysql> show  tables;
    Empty set (0.00 sec)
    

### 修改表名 

ALTER TABLE old_table_name RENAME [TO] new_table_name

* old_table_name 原表名
* new_table_name 新表名   
将t_dept修改为tab_dept 
```
    mysql > alter table t_dept rename tab_dept;
    Query OK, 0 rows affected (0.09 sec)

    mysql> show tables;
    +-------------------+
    | Tables_in_company |
    +-------------------+
    | tab_dept          |
    +-------------------+
    1 row in set (0.00 sec)
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
```
### 为表增加一个字段默认在最后 

ALTER TABLE table_name ADD 属性名 属性类型

为tab_dept增加一个字段descri varchar(20)

    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> alter table tab_dept add descri varchar(20);
    Query OK, 0 rows affected (0.33 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | varchar(20) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    4 rows in set (0.00 sec)
    

### 在表的第一个位置增加一个字段 

ALTER TABLE table_name ADD 属性名 属性类型 first 

    mysql> alter table tab_dept add id int first;
    Query OK, 0 rows affected (0.38 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | varchar(20) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

### 在表的指定字段之后增加字段 

ALTER TABLE table_name ADD 属性名 属性类型 AFTER 属性名

    mysql> alter table tab_dept add comm varchar(20) after dname;
    Query OK, 0 rows affected (0.31 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | comm   | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | varchar(20) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    6 rows in set (0.00 sec)
    

### 删除字段 

ALTER TABLE table_name DROP 属性名 

    mysql> alter table tab_dept drop comm;
    Query OK, 0 rows affected (0.32 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | varchar(20) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

### 字段修改-修改字段数据类型 

ALTER TABLE table_name MODIFY 属性名 数据类型 

    mysql> alter table tab_dept modify descri int;
    Query OK, 0 rows affected (0.45 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

### 字段修改-修改字段名称 

ALTER TABLE table_name CHANGE 旧属性名 新属性名 旧数据类型 

    mysql> alter table tab_dept change id deptid int;
    Query OK, 0 rows affected (0.07 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptid | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

### 字段修改-同时修改字段名称与数据类型 

ALTER TABLE table_name CHANGE 旧属性名 新属性名 新数据类型

    mysql> alter table tab_dept change deptid id varchar(32);
    Query OK, 0 rows affected (0.49 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | varchar(32) | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

### 修改顺序 

ALTER TABLE table_name MODIFY 属性名1 数据类型 FIRST|AFTER 属性名2

2个属性必须存在

#### 将deptno调到第一个位置 

    mysql> alter table tab_dept modify deptno int first;
    Query OK, 0 rows affected (0.33 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | id     | varchar(32) | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    

#### 将ID放在最后 

    mysql> alter table tab_dept modify deptno int after descri;
    Query OK, 0 rows affected (0.29 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | id     | varchar(32) | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    | deptno | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)
    
    mysql> alter table tab_dept modify deptno int first;
    Query OK, 0 rows affected (0.34 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> alter table tab_dept modify id int after descri;
    Query OK, 0 rows affected (0.47 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc tab_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    | descri | int(11)     | YES  |     | NULL    |       |
    | id     | int(11)     | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    5 rows in set (0.00 sec)

 --- 

 ## 五表的约束


 --- 

 ## 六索引基本操作

 索引就像书的目录一样。主要是为了提高从表中检索数据的速度。

### 以下情况适合创建索引 

* 经常被查询的字段，即在WHERE子句中出现的字段
* 在分组的字段，即在GROUG BY子句中出现的字段
* 存在依赖关系的子表与父表之间的联合查询，即主键或者外键字段
* 设置唯一完整性约束字段

### 以下情况不适合创建索引 

* 在查询中很少被使用的字段
* 拥有许多重复值的字段

### 创建和查看普通索引 

    CREATE TABLE 表名(
        属性名 数据类型，
        属性名 数据类型，
        属性名 数据类型，
        
        INDEX | KEY 【索引名】（属性名1，【长度】【ASC|DESC】）
    );
    

INDEX或者KEY参数用来指定字段为索引，

索引名参数用来指定所创建的索引名，

属性名1，用来指定索引所关联的字段名称，

长度用来指定索引长度，

ASC参数用来指定索引为升序，DESC用来指定索引为降序

    mysql> drop table t_employee;
    Query OK, 0 rows affected (0.21 sec)
    
    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.10 sec)
    
    mysql> drop database company;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> show databases;
    +--------------------+
    | Database           |
    +--------------------+
    | information_schema |
    | databasetest       |
    | mysql              |
    | performance_schema |
    | sakila             |
    | sys                |
    | world              |
    +--------------------+
    7 rows in set (0.00 sec)
    
    mysql> create database company;
    Query OK, 1 row affected (0.00 sec)
    
    mysql> use company;
    Database changed
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT ,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40) ,
        -> INDEX index_deptno (deptno)
        -> );
    Query OK, 0 rows affected (0.30 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | MUL | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept;
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Table  | Create Table                                                                                                                                                                                        |
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | t_dept | CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    
    mysql> show create table t_dept \G
    *************************** 1. row ***************************
           Table: t_dept
    Create Table: CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    1 row in set (0.00 sec)
    

show create table t_dept \G 查看是否创建索引

### 检验索引是否被使用 

EXPLAIN select * from t_dept where deptno=1; 

    mysql> EXPLAIN select * from t_dept where deptno=1;
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type | possible_keys | key          | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | ref  | index_deptno  | index_deptno | 5       | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    

possible_keys | key

字段处的值为报建的索引名，说这个索引已经存在，而且开始使用

### 在已经存在的表上创建普通索引 

CREATE INDEX 索引名 on 表名（属性名【(长度)】【ASC|DESC】）

删除表，创建一个没有索引的空表

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.17 sec)
    
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT ,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40)
        -> );
    Query OK, 0 rows affected (0.19 sec)
    
    mysql> show tables;
    +-------------------+
    | Tables_in_company |
    +-------------------+
    | t_dept            |
    +-------------------+
    1 row in set (0.00 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> explain select * from t_dept where deptno=1;
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | NULL          | NULL | NULL    | NULL |    1 |   100.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    

下面是创建索引

    mysql> create index index_deptno on t_dept(deptno);
    Query OK, 0 rows affected (0.24 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> explain select * from t_dept where deptno=1;
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type | possible_keys | key          | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | ref  | index_deptno  | index_deptno | 5       | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+------+---------------+--------------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    
    ysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | MUL | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept;
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Table  | Create Table                                                                                                                                                                                        |
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | t_dept | CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +--------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    

Key已经有值，说明创建成功

### 在已经存在的表上添加普通索引 

ALTER table 表名 ADD INDEX 索引名 （属性名【(长度)】【ASC|DESC】）

准备数据 

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.18 sec)
    
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT ,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40)
        -> );
    Query OK, 0 rows affected (0.21 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept \G
    *************************** 1. row ***************************
           Table: t_dept
    Create Table: CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    1 row in set (0.00 sec)
    

添加索引

    mysql> alter table t_dept add index index_deptno (deptno);
    Query OK, 0 rows affected (0.20 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | MUL | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept \G
    *************************** 1. row ***************************
           Table: t_dept
    Create Table: CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    1 row in set (0.00 sec)
    

KEY已经有值

### 创建来查看唯一索引 

    CREATE TABLE 表名(
        属性名 数据类型，
        属性名 数据类型，
        属性名 数据类型，
        
        UNIQUE INDEX | KEY 【索引名】（属性名1，【长度】【ASC|DESC】）
    );
    

创建表时直接创建唯一索引 

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.45 sec)
    
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT ,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40),
        -> UNIQUE INDEX index_deptno (deptno)
        -> );
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | UNI | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept \G
    *************************** 1. row ***************************
           Table: t_dept
    Create Table: CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      UNIQUE KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    1 row in set (0.00 sec)
    

KEY的值为UNI,表示创建成功

### 在已经存在的表上创建唯一索引 

CREATE UNIQUE INDEX 索引名 on 表名（属性名【(长度)】【ASC|DESC】）

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.16 sec)
    
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT UNIQUE,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40)
        ->
        -> );
    Query OK, 0 rows affected (0.24 sec)
    
    mysql> create unique index index_deptno on t_dept (deptno);
    Query OK, 0 rows affected, 1 warning (1.30 sec)
    Records: 0  Duplicates: 0  Warnings: 1
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | UNI | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> show create table t_dept;
    +--------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Table  | Create Table                                                                                                                                                                                                                                 |
    +--------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | t_dept | CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      UNIQUE KEY `deptno` (`deptno`),
      UNIQUE KEY `index_deptno` (`deptno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +--------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    

KEY的值为UNI,表示创建成功

### 在已经存在的表上添加唯一索引 

ALTER table 表名 ADD UNIQUE INDEX 索引名 （属性名【(长度)】【ASC|DESC】）

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.21 sec)
    
    mysql> CREATE TABLE IF NOT EXISTS t_dept(
        -> deptno INT UNIQUE,
        -> dname VARCHAR(20)  ,
        -> loc   VARCHAR(40)
        ->
        -> );
    Query OK, 0 rows affected (0.21 sec)
    
    mysql> alter table t_dept add unique index index_deptno (deptno);
    Query OK, 0 rows affected, 1 warning (0.20 sec)
    Records: 0  Duplicates: 0  Warnings: 1
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  | UNI | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> explain  select * from t_dept where deptno=1;
    +----+-------------+--------+------------+-------+---------------------+--------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type  | possible_keys       | key    | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+-------+---------------------+--------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | const | deptno,index_deptno | deptno | 5       | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+-------+---------------------+--------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain  select * from t_dept where deptno=1\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: t_dept
       partitions: NULL
             type: const
    possible_keys: deptno,index_deptno
              key: deptno
          key_len: 5
              ref: const
             rows: 1
         filtered: 100.00
            Extra: NULL
    1 row in set, 1 warning (0.00 sec)
    

### 创建和查看全文索引 

全文索引主要关联在数据类型为 CHAR ,VARCHAR和TEXT的字段上,

Mysql从3.23.23版本开始支持全文索引，只能在存储引擎为MyISAM的数据表表创建全文索引，默认情况下不区分大小写

    CREATE TABLE 表名(
        属性名 数据类型，
        属性名 数据类型，
        属性名 数据类型，
        
        FULLTEXT INDEX | KEY 【索引名】（属性名1，【长度】【ASC|DESC】）
    );
    

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) DEFAULT NULL,
        ->   `dname` VARCHAR(20) DEFAULT NULL,
        ->   `loc` VARCHAR(40) DEFAULT NULL
        ->
        -> ) ENGINE=MYISAM DEFAULT CHARSET=utf8;
    Query OK, 0 rows affected (0.04 sec)
    
    mysql> create fulltext index index_loc on t_dept(loc);
    Query OK, 2 rows affected (0.05 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    
    mysql> explain select * from t_dept where loc='bj';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | index_loc     | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain select * from t_dept where loc='sz';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | index_loc     | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql>
    

### 在已经存在的表上创建全文索引 

CREATE FULLTEXT INDEX 索引名 on 表名（属性名【(长度)】【ASC|DESC】）

创建全文索引

    DROP TABLE IF EXISTS `t_dept`;
    
    CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL,
      FULLTEXT KEY `index_loc` (`loc`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    mysql> explain select * from t_dept where loc='cj';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | index_loc     | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    

### 在已经存在的表上添加全文索引 

ALTER table 表名 ADD FULLTEXT INDEX 索引名 （属性名【(长度)】【ASC|DESC】）

    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) ,
        ->   `dname` VARCHAR(20) ,
        ->   `loc` VARCHAR(40)
        ->
        -> ) ENGINE=MYISAM DEFAULT CHARSET=utf8;
    Query OK, 0 rows affected (0.04 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> alter table t_dept add fulltext index index_loc(loc);
    Query OK, 0 rows affected (0.02 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  | MUL | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> explain select * from t_dept where loc='sz';
    +----+-------------+-------+------------+------+---------------+------+---------+------+------+----------+--------------------------------+
    | id | select_type | table | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra                          |
    +----+-------------+-------+------------+------+---------------+------+---------+------+------+----------+--------------------------------+
    |  1 | SIMPLE      | NULL  | NULL       | NULL | NULL          | NULL | NULL    | NULL | NULL |     NULL | no matching row in const table |
    +----+-------------+-------+------------+------+---------------+------+---------+------+------+----------+--------------------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain select * from t_dept where loc='sz';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | index_loc     | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  | MUL | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    

### 创建和查看多列索引 

    CREATE TABLE 表名(
        属性名 数据类型，
        属性名 数据类型，
        属性名 数据类型，
        
         INDEX | KEY 【索引名】（属性名1，【长度】【ASC|DESC】,
                             （属性名2，【长度】【ASC|DESC】
                                .....)
        ）
    );
    

多列索引，是指在创建索引时，所关联的字段不是一个字段，而是多字段，虽然可以通过所关联的字段进行查询，但是只有查询条件中使用了所关联字段中的第一个字段，多列索引才会被使用

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) ,
        ->   `dname` VARCHAR(20) ,
        ->   `loc` VARCHAR(40),
        ->   INDEX index_dname_loc(dname,loc)
        ->
        -> );
    Query OK, 0 rows affected (0.18 sec)
    
    mysql> insert into t_dept values(1,'sz','szn'),(2,'sh','shg');
    Query OK, 2 rows affected (0.05 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  | MUL | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> explain select * from t_dept where loc='szn';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | NULL          | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain select * from t_dept where dname='sz';
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type | possible_keys   | key             | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | ref  | index_dname_loc | index_dname_loc | 63      | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    

### 在已经存在的表上创建多列索引 

CREATE INDEX 索引名 on 表名（属性名1，【长度】【ASC|DESC】,

（属性名2，【长度】【ASC|DESC】

…..)

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.15 sec)
    
    mysql>
    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) ,
        ->   `dname` VARCHAR(20) ,
        ->   `loc` VARCHAR(40)
        ->
        ->
        -> );
    Query OK, 0 rows affected (0.31 sec)
    
    mysql> INSERT INTO t_dept VALUES(1,'sz','szn'),(2,'sh','shg');
    Query OK, 2 rows affected (0.05 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> create index index_dname_loc on t_dept (dname,loc);
    Query OK, 0 rows affected (0.25 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  | MUL | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.01 sec)
    
    mysql> explain select * from t_dept where loc='shg';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | NULL          | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain select * from t_dept where dname='sh';
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type | possible_keys   | key             | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | ref  | index_dname_loc | index_dname_loc | 63      | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    

### 在已经存在的表上添加多列索引 

ALTER table 表名

ADD INDEX 索引名

（属性名1，【长度】【ASC|DESC】,

（属性名2，【长度】【ASC|DESC】

…..)

    mysql> drop table t_dept;
    Query OK, 0 rows affected (0.15 sec)
    
    mysql>
    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) ,
        ->   `dname` VARCHAR(20) ,
        ->   `loc` VARCHAR(40)
        ->
        ->
        -> );
    Query OK, 0 rows affected (0.31 sec)
    
    mysql> INSERT INTO t_dept VALUES(1,'sz','szn'),(2,'sh','shg');
    Query OK, 2 rows affected (0.05 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    mysql> alter table t_dept add index index_loc_dname (loc,dname);
    Query OK, 0 rows affected (0.23 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  | MUL | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> explain select * from t_dept where loc='szn';
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    | id | select_type | table  | partitions | type | possible_keys   | key             | key_len | ref   | rows | filtered | Extra |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    |  1 | SIMPLE      | t_dept | NULL       | ref  | index_loc_dname | index_loc_dname | 123     | const |    1 |   100.00 | NULL  |
    +----+-------------+--------+------------+------+-----------------+-----------------+---------+-------+------+----------+-------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> explain select * from t_dept where dname='sz';
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    | id | select_type | table  | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra       |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    |  1 | SIMPLE      | t_dept | NULL       | ALL  | NULL          | NULL | NULL    | NULL |    2 |    50.00 | Using where |
    +----+-------------+--------+------------+------+---------------+------+---------+------+------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    

### 删除索引 

DROP INDEX 索引名 on 表名

    mysql> drop index  index_dname_loc on t_dept;
    Query OK, 0 rows affected (0.14 sec)
    Records: 0  Duplicates: 0  Warnings: 0

---

## 七视图的基本应用

* 视图的列可以来自于不同的表，是表的抽象和在逻辑意义上建立的新关系
* 视图是由基本表（实表）产生的表（虚表）
* 视图的建立和删除不影响基本表
* 对视图内容的更新（添加、修改、删除）直接影响基本表
* 当视图来自多个表时，不允许添加和删除数据

### 创建视图和查看 

    create view view_name
        AS 查询语句
    
    -----------
    CREATE OR REPLACE VIEW  视图名
    AS
    语句
    

####查看 

    SHOW CREATE VIEW viewname
    DESCRIBE | DESC viewname
     show table status from view;
    

####准备数据 

    DROP TABLE IF EXISTS `t_product`;
    
    CREATE TABLE `t_product` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(50) NOT NULL,
      `price` decimal(5,2) unsigned DEFAULT '0.00',
      PRIMARY KEY (`id`)
    ) 
    
    /*Data for the table `t_product` */
    
    insert  into `t_product`(`id`,`name`,`price`) values (1,'apple','6.50'),(2,'banana','4.50'),(3,'orange','1.50'),(4,'pear','2.50');
    

####建立视图

    CREATE VIEW  view_product
    AS
    SELECT id,NAME,price
    FROM t_product;
    

####使用视图

    mysql> select * from view_product;
    +----+--------+-------+
    | id | name   | price |
    +----+--------+-------+
    |  1 | apple  |  6.50 |
    |  2 | banana |  4.50 |
    |  3 | orange |  1.50 |
    |  4 | pear   |  2.50 |
    +----+--------+-------+
    4 rows in set (0.00 sec)
    

#### 向视图插入数据 

    INSERT INTO view_product (NAME,price) VALUES('花生',3.33);
    
    mysql> select * from view_product;
    +----+--------+-------+
    | id | name   | price |
    +----+--------+-------+
    |  1 | apple  |  6.50 |
    |  2 | banana |  4.50 |
    |  3 | orange |  1.50 |
    |  4 | pear   |  2.50 |
    |  5 | 花生   |  3.33 |
    +----+--------+-------+
    5 rows in set (0.05 sec)
    

#### 修改 

    mysql> UPDATE view_product SET price=5.55 WHERE NAME='花生';
    Query OK, 1 row affected (0.04 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> select * from view_product;
    +----+--------+-------+
    | id | name   | price |
    +----+--------+-------+
    |  1 | apple  |  6.50 |
    |  2 | banana |  4.50 |
    |  3 | orange |  1.50 |
    |  4 | pear   |  2.50 |
    |  5 | 花生   |  5.55 |
    +----+--------+-------+
    5 rows in set (0.00 sec)
    

#### 删除 

    mysql> delete from view_product where name='花生';
    Query OK, 1 row affected (0.04 sec)
    
    mysql> select * from view_product;
    +----+--------+-------+
    | id | name   | price |
    +----+--------+-------+
    |  1 | apple  |  6.50 |
    |  2 | banana |  4.50 |
    |  3 | orange |  1.50 |
    |  4 | pear   |  2.50 |
    +----+--------+-------+
    4 rows in set (0.00 sec)
    

#### 修改视图 

CREATE OR REPLACE VIEW 视图名

AS

语句

    mysql> create or replace view_product
        -> as
        -> select name from t_product;
    ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'view_product
    as
    select name from t_product' at line 1
    mysql> create or replace view view_product
        -> as
        -> select name from t_product;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> select * from view_product;
    +--------+
    | name   |
    +--------+
    | apple  |
    | banana |
    | orange |
    | pear   |
    +--------+
    4 rows in set (0.00 sec)
    

#### Alter 修改视图 

ALTER VIWE 视图名

AS

语句

    mysql> alter view view_product
        -> as
        -> select name,price from t_product;
    Query OK, 0 rows affected (0.05 sec)
    
    mysql> select * from view_product;
    +--------+-------+
    | name   | price |
    +--------+-------+
    | apple  |  6.50 |
    | banana |  4.50 |
    | orange |  1.50 |
    | pear   |  2.50 |
    +--------+-------+
    4 rows in set (0.00 sec)
    

#### 通过系统表查看视图 

数据库information_schema，下有一个包含视图信息的表views;

    mysql> desc views;
    +----------------------+--------------+------+-----+---------+-------+
    | Field                | Type         | Null | Key | Default | Extra |
    +----------------------+--------------+------+-----+---------+-------+
    | TABLE_CATALOG        | varchar(512) | NO   |     |         |       |
    | TABLE_SCHEMA         | varchar(64)  | NO   |     |         |       |
    | TABLE_NAME           | varchar(64)  | NO   |     |         |       |
    | VIEW_DEFINITION      | longtext     | NO   |     | NULL    |       |
    | CHECK_OPTION         | varchar(8)   | NO   |     |         |       |
    | IS_UPDATABLE         | varchar(3)   | NO   |     |         |       |
    | DEFINER              | varchar(93)  | NO   |     |         |       |
    | SECURITY_TYPE        | varchar(7)   | NO   |     |         |       |
    | CHARACTER_SET_CLIENT | varchar(32)  | NO   |     |         |       |
    | COLLATION_CONNECTION | varchar(32)  | NO   |     |         |       |
    +----------------------+--------------+------+-----+---------+-------+
    10 rows in set (0.00 sec)
    
    mysql> select * from views where table_name='view_product';
    +---------------+--------------+--------------+------------------------------------------------------------------------------------------------------------------------------------------+--------------+--------------+----------------+---------------+----------------------+----------------------+
    | TABLE_CATALOG | TABLE_SCHEMA | TABLE_NAME   | VIEW_DEFINITION                                                                                                                          | CHECK_OPTION | IS_UPDATABLE | DEFINER        | SECURITY_TYPE | CHARACTER_SET_CLIENT | COLLATION_CONNECTION |
    +---------------+--------------+--------------+------------------------------------------------------------------------------------------------------------------------------------------+--------------+--------------+----------------+---------------+----------------------+----------------------+
    | def           | view         | view_product | select `view`.`t_product`.`id` AS `id`,`view`.`t_product`.`name` AS `name`,`view`.`t_product`.`price` AS `price` from `view`.`t_product` | NONE         | YES          | root@localhost | DEFINER       | utf8                 | utf8_general_ci      |
    +---------------+--------------+--------------+------------------------------------------------------------------------------------------------------------------------------------------+--------------+--------------+----------------+---------------+----------------------+----------------------+
    1 row in set (0.00 sec)
    
    mysql> select * from views where table_name='view_product'\G
    *************************** 1. row ***************************
           TABLE_CATALOG: def
            TABLE_SCHEMA: view
              TABLE_NAME: view_product
         VIEW_DEFINITION: select `view`.`t_product`.`id` AS `id`,`view`.`t_product`.`name` AS `name`,`view`.`t_product`.`price` AS `price` from `view`.`t_product`
            CHECK_OPTION: NONE
            IS_UPDATABLE: YES
                 DEFINER: root@localhost
           SECURITY_TYPE: DEFINER
    CHARACTER_SET_CLIENT: utf8
    COLLATION_CONNECTION: utf8_general_ci
    1 row in set (0.00 sec)
    

#### 删除视图 

DROP VIEW 视图名1，视图名2 。。。

    mysql> drop view if exists view_product;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> show tables;
    +----------------+
    | Tables_in_view |
    +----------------+
    | t_product      |
    +----------------+
    1 row in set (0.00 sec)
    

删除成功

----

## 八触发器基本操作 

    CREATE OR REPLACE TRIGGER 触发器名称
    
    BEFORE | AFTER 触发器事件trigger_event
    
    ON 表名TABLE_NAME FOR EACH ROW trigger_stmt
    

* 触发器名称 trigger_xxx
* BEFORE | AFTER 参数指定触发器执行的时间
* 触发器事件trigger_event 包含DELETE,INSERT,UPDATE
* 表名TABLE_NAME 触发事件的操作的表名
* FOR EACH ROW 参数表示任何一条记录上的操作满足触发条件都会触发这个触发器
* trigger_stmt 激活触发器后被执行的语句

    mysql> use company;
    Database changed
    mysql> show tables;
    Empty set (0.00 sec)
    
    mysql> CREATE TABLE `t_dept` (
        ->   `deptno` INT(11) ,
        ->   `dname` VARCHAR(20) ,
        ->   `loc` VARCHAR(40)
        ->
        ->
        -> );
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> CREATE TABLE `t_diary` (
        ->   diaryno INT PRIMARY KEY AUTO_INCREMENT,
        ->   tablename VARCHAR(20),
        ->   diarytime DATETIME
        ->
        ->
        -> );
    Query OK, 0 rows affected (0.19 sec)
    
    mysql> show tables;
    +-------------------+
    | Tables_in_company |
    +-------------------+
    | t_dept            |
    | t_diary           |
    +-------------------+
    2 rows in set (0.00 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | YES  |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> desc t_diary;
    +-----------+------------+------+-----+---------+----------------+
    | Field     | Type       | Null | Key | Default | Extra          |
    +-----------+------------+------+-----+---------+----------------+
    | diaryno   | int(11)    | NO   | PRI | NULL    | auto_increment |
    | tablename | varchar(2) | YES  |     | NULL    |                |
    | diarytime | datetime   | YES  |     | NULL    |                |
    +-----------+------------+------+-----+---------+----------------+
    3 rows in set (0.00 sec)
    

创建 

    mysql> CREATE  TRIGGER tri_diarytime
        -> BEFORE INSERT
        -> ON t_dept FOR EACH ROW
        -> INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    Query OK, 0 rows affected (0.07 sec)
    

插入一条记录到t_dept

    mysql> INSERT INTO t_dept VALUE(1,'sz','szn');
    Query OK, 1 row affected (0.08 sec)
    
    mysql> select * from t_dept;
    +--------+-------+------+
    | deptno | dname | loc  |
    +--------+-------+------+
    |      1 | sz    | szn  |
    +--------+-------+------+
    1 row in set (0.00 sec)
    
    mysql> select * from t_diary;
    +---------+-----------+---------------------+
    | diaryno | tablename | diarytime           |
    +---------+-----------+---------------------+
    |       1 | t_dept    | 2017-02-13 16:29:41 |
    +---------+-----------+---------------------+
    1 row in set (0.00 sec)
    
    mysql> INSERT INTO t_dept VALUE(2,'sz','szn'),(3,'test','sh');
    Query OK, 2 rows affected (0.10 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+-------+------+
    | deptno | dname | loc  |
    +--------+-------+------+
    |      1 | sz    | szn  |
    |      2 | sz    | szn  |
    |      3 | test  | sh   |
    +--------+-------+------+
    3 rows in set (0.00 sec)
    
    mysql> select * from t_diary;
    +---------+-----------+---------------------+
    | diaryno | tablename | diarytime           |
    +---------+-----------+---------------------+
    |       1 | t_dept    | 2017-02-13 16:29:41 |
    |       2 | t_dept    | 2017-02-13 16:30:24 |
    |       3 | t_dept    | 2017-02-13 16:30:24 |
    +---------+-----------+---------------------+
    3 rows in set (0.00 sec)
    

第二种创建 

    DELIMITER $$
    
    CREATE  TRIGGER tri_diarytime
    BEFORE INSERT
    ON t_dept FOR EACH ROW
    BEGIN
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    END
    $$;
    DELIMITER;
    

#### 查看 

show triggers \G

show triggers;

    mysql> show triggers \G
    *************************** 1. row ***************************
                 Trigger: tri_diarytime
                   Event: INSERT
                   Table: t_dept
               Statement: begin
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    end
                  Timing: BEFORE
                 Created: 2017-02-13 16:35:31.52
                sql_mode: STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
                 Definer: root@localhost
    character_set_client: utf8
    collation_connection: utf8_general_ci
      Database Collation: utf8_general_ci
    1 row in set (0.00 sec)
    

#### 通过information_schma查看triggers 

    mysql> show databases;
    +--------------------+
    | Database           |
    +--------------------+
    | information_schema |
    | company            |
    | databasetest       |
    | mysql              |
    | performance_schema |
    | sakila             |
    | sys                |
    | view               |
    | world              |
    +--------------------+
    9 rows in set (0.00 sec)
    
    mysql> use information_schema;
    Database changed
    mysql> desc triggers;
    +----------------------------+---------------+------+-----+---------+-------+
    | Field                      | Type          | Null | Key | Default | Extra |
    +----------------------------+---------------+------+-----+---------+-------+
    | TRIGGER_CATALOG            | varchar(512)  | NO   |     |         |       |
    | TRIGGER_SCHEMA             | varchar(64)   | NO   |     |         |       |
    | TRIGGER_NAME               | varchar(64)   | NO   |     |         |       |
    | EVENT_MANIPULATION         | varchar(6)    | NO   |     |         |       |
    | EVENT_OBJECT_CATALOG       | varchar(512)  | NO   |     |         |       |
    | EVENT_OBJECT_SCHEMA        | varchar(64)   | NO   |     |         |       |
    | EVENT_OBJECT_TABLE         | varchar(64)   | NO   |     |         |       |
    | ACTION_ORDER               | bigint(4)     | NO   |     | 0       |       |
    | ACTION_CONDITION           | longtext      | YES  |     | NULL    |       |
    | ACTION_STATEMENT           | longtext      | NO   |     | NULL    |       |
    | ACTION_ORIENTATION         | varchar(9)    | NO   |     |         |       |
    | ACTION_TIMING              | varchar(6)    | NO   |     |         |       |
    | ACTION_REFERENCE_OLD_TABLE | varchar(64)   | YES  |     | NULL    |       |
    | ACTION_REFERENCE_NEW_TABLE | varchar(64)   | YES  |     | NULL    |       |
    | ACTION_REFERENCE_OLD_ROW   | varchar(3)    | NO   |     |         |       |
    | ACTION_REFERENCE_NEW_ROW   | varchar(3)    | NO   |     |         |       |
    | CREATED                    | datetime(2)   | YES  |     | NULL    |       |
    | SQL_MODE                   | varchar(8192) | NO   |     |         |       |
    | DEFINER                    | varchar(93)   | NO   |     |         |       |
    | CHARACTER_SET_CLIENT       | varchar(32)   | NO   |     |         |       |
    | COLLATION_CONNECTION       | varchar(32)   | NO   |     |         |       |
    | DATABASE_COLLATION         | varchar(32)   | NO   |     |         |       |
    +----------------------------+---------------+------+-----+---------+-------+
    22 rows in set (0.00 sec)
    
    
    mysql> select * from triggers where trigger_name='tri_diarytime' \G
    *************************** 1. row ***************************
               TRIGGER_CATALOG: def
                TRIGGER_SCHEMA: company
                  TRIGGER_NAME: tri_diarytime
            EVENT_MANIPULATION: INSERT
          EVENT_OBJECT_CATALOG: def
           EVENT_OBJECT_SCHEMA: company
            EVENT_OBJECT_TABLE: t_dept
                  ACTION_ORDER: 1
              ACTION_CONDITION: NULL
              ACTION_STATEMENT: begin
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    INSERT INTO t_diary VALUES(NULL,'t_dept',NOW());
    end
            ACTION_ORIENTATION: ROW
                 ACTION_TIMING: BEFORE
    ACTION_REFERENCE_OLD_TABLE: NULL
    ACTION_REFERENCE_NEW_TABLE: NULL
      ACTION_REFERENCE_OLD_ROW: OLD
      ACTION_REFERENCE_NEW_ROW: NEW
                       CREATED: 2017-02-13 16:35:31.52
                      SQL_MODE: STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
                       DEFINER: root@localhost
          CHARACTER_SET_CLIENT: utf8
          COLLATION_CONNECTION: utf8_general_ci
            DATABASE_COLLATION: utf8_general_ci
    1 row in set (0.01 sec)
    

#### 删除 

DROP TRIGGER IF EXISTS 触发器名称

    drop trigger if exists tri_diarytime;
    mysql> show triggers;
    Empty set (0.00 sec)
    

删除成功

---
## 九数据的基本操作

实际上就是对表的数据进行操作

CRUD

* C–CREATE 插入
* R–READ 查询
* U–UPDATE 更新
* D–DELETE 删除

#### 插入完整数据记录 

    INSERT INTO table_name(field1,field2,field3,……fieldn)
        VALUES(value11,value21,value31……valuen1),
                (value12,value22,value32……valuen2),
                (value13,value23,value33……valuen3),
                ……
                (value1m,value2m,value3m……valuenm)
    

创建表 

    CREATE TABLE `t_dept` (
      `deptno` int(11) DEFAULT NULL,
      `dname` varchar(20) DEFAULT NULL,
      `loc` varchar(40) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    

插入记录 

    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+-------+
    | Field  | Type        | Null | Key | Default | Extra |
    +--------+-------------+------+-----+---------+-------+
    | deptno | int(11)     | NO   |     | NULL    |       |
    | dname  | varchar(20) | YES  |     | NULL    |       |
    | loc    | varchar(40) | YES  |     | NULL    |       |
    +--------+-------------+------+-----+---------+-------+
    3 rows in set (0.00 sec)
    
    mysql> insert into t_dept(deptno,dname,loc) values(1,'开发','深圳');
    Query OK, 1 row affected (0.04 sec)
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 深圳   |
    +--------+--------+--------+
    1 row in set (0.00 sec)
    

#### 插入了一部分数据记录 

    INSERT INTO 表名(field1,field2,...) values(value1,valu2,...);
    

    mysql> insert into t_dept(dname,loc) values('测试','上海');
    Query OK, 1 row affected (0.04 sec)
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 深圳   |
    |   NULL | 测试   | 上海   |
    +--------+--------+--------+
    2 rows in set (0.00 sec)
    

#### 插入了多条数据记录 

    INSERT INTO 表名(field1,field2,...) values(value1,valu2,...),(value1,valu2,...),....;
    

插入记录 

    mysql> INSERT INTO t_dept VALUES(2,'sz','szn'),(3,'test','sh'),(4,'销售','北京'),(5,'人事','重庆');
    Query OK, 4 rows affected (0.04 sec)
    Records: 4  Duplicates: 0  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 深圳   |
    |   NULL | 测试   | 上海   |
    |      2 | sz     | szn    |
    |      3 | test   | sh     |
    |      4 | 销售   | 北京   |
    |      5 | 人事   | 重庆   |
    +--------+--------+--------+
    6 rows in set (0.00 sec)
    

#### 插入了查询结果 

    INSERT INTO table_name1(field11,field12,field13,……field1n)
        SELECT (field21,field22,field23,……field2n)
            FROM table_name2
                WHERE ……
    

插入查询结果 

    mysql> INSERT INTO t_dept select * from t_dept where deptno is not null;
    Query OK, 5 rows affected (0.05 sec)
    Records: 5  Duplicates: 0  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 深圳   |
    |   NULL | 测试   | 上海   |
    |      2 | sz     | szn    |
    |      3 | test   | sh     |
    |      4 | 销售   | 北京   |
    |      5 | 人事   | 重庆   |
    |      1 | 开发   | 深圳   |
    |      2 | sz     | szn    |
    |      3 | test   | sh     |
    |      4 | 销售   | 北京   |
    |      5 | 人事   | 重庆   |
    +--------+--------+--------+
    11 rows in set (0.00 sec)
    

#### 更新记录 

    UPDATE table_name
        SET field1=value1,
            field2=value2,
            field3=value3,
        WHERE CONDITION
    

更新所有deptno不是NULL的loc为广州 

    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 深圳   |
    |   NULL | 测试   | 上海   |
    |      2 | sz     | szn    |
    |      3 | test   | sh     |
    |      4 | 销售   | 北京   |
    |      5 | 人事   | 重庆   |
    |      1 | 开发   | 深圳   |
    |      2 | sz     | szn    |
    |      3 | test   | sh     |
    |      4 | 销售   | 北京   |
    |      5 | 人事   | 重庆   |
    +--------+--------+--------+
    11 rows in set (0.00 sec)
    
    
    mysql> update t_dept set loc='广州' where deptno is not null;
    Query OK, 10 rows affected (0.05 sec)
    Rows matched: 10  Changed: 10  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 广州   |
    |   NULL | 测试   | 上海   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    |      1 | 开发   | 广州   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    +--------+--------+--------+
    11 rows in set (0.00 sec)
    

#### 删除记录 

    DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name
        [WHERE where_condition]
        [ORDER BY ...]
        [LIMIT row_count]
    

删除deptno 为null的记录

    mysql> delete from t_dept where deptno is null;
    Query OK, 1 row affected (0.05 sec)
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 广州   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    |      1 | 开发   | 广州   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    +--------+--------+--------+
    10 rows in set (0.00 sec)
    

#### 删除所有记录 

    DELETE FROM 表名
    

    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | 开发   | 广州   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    |      1 | 开发   | 广州   |
    |      2 | sz     | 广州   |
    |      3 | test   | 广州   |
    |      4 | 销售   | 广州   |
    |      5 | 人事   | 广州   |
    +--------+--------+--------+
    10 rows in set (0.00 sec)
    
    mysql> delete from t_dept;
    Query OK, 10 rows affected (0.05 sec)
    
    mysql> select * from t_dept;
    Empty set (0.00 sec)
    

#### 清空表 

    truncate  表名
    

数据表 

    mysql> CREATE TABLE t_dept (
        ->   deptno INT(11)  PRIMARY KEY AUTO_INCREMENT,
        ->   dname VARCHAR(20) ,
        ->   loc VARCHAR(40)
        -> );
    Query OK, 0 rows affected (0.19 sec)
    
    mysql> desc t_dept;
    +--------+-------------+------+-----+---------+----------------+
    | Field  | Type        | Null | Key | Default | Extra          |
    +--------+-------------+------+-----+---------+----------------+
    | deptno | int(11)     | NO   | PRI | NULL    | auto_increment |
    | dname  | varchar(20) | YES  |     | NULL    |                |
    | loc    | varchar(40) | YES  |     | NULL    |                |
    +--------+-------------+------+-----+---------+----------------+
    3 rows in set (0.00 sec)
    
    mysql> INSERT INTO t_dept VALUES('sz','szn'),('test','sh'),('销售','北京'),('人事','重庆');
    ERROR 1136 (21S01): Column count doesn't match value count at row 1
    mysql> INSERT INTO t_dept (dname,loc) VALUES('sz','szn'),('test','sh'),('销售','北京'),('人事','重庆');
    Query OK, 4 rows affected (0.05 sec)
    Records: 4  Duplicates: 0  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | sz     | szn    |
    |      2 | test   | sh     |
    |      3 | 销售   | 北京   |
    |      4 | 人事   | 重庆   |
    +--------+--------+--------+
    4 rows in set (0.00 sec)
    

清空 

    mysql> TRUNCATE t_dept;
    Query OK, 0 rows affected (0.22 sec)
    
    mysql> select * from t_dept;
    Empty set (0.00 sec)
    
    mysql> INSERT INTO t_dept (dname,loc) VALUES('sz','szn'),('test','sh'),('销售','北京'),('人事','重庆');
    Query OK, 4 rows affected (0.05 sec)
    Records: 4  Duplicates: 0  Warnings: 0
    
    mysql> select * from t_dept;
    +--------+--------+--------+
    | deptno | dname  | loc    |
    +--------+--------+--------+
    |      1 | sz     | szn    |
    |      2 | test   | sh     |
    |      3 | 销售   | 北京   |
    |      4 | 人事   | 重庆   |
    +--------+--------+--------+
    4 rows in set (0.00 sec)
    

TRUNCATE 会把表中的数据和主键同时都复位DELETE 不会

#### 查询记录语法 

    SELECT
        [ALL | DISTINCT | DISTINCTROW ]
          [HIGH_PRIORITY]
          [STRAIGHT_JOIN]
          [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
          [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
        select_expr, ...
        [FROM table_references
        [WHERE where_condition]
        [GROUP BY {col_name | expr | position}
          [ASC | DESC], ... [WITH ROLLUP]]
        [HAVING where_condition]
        [ORDER BY {col_name | expr | position}
          [ASC | DESC], ...]
        [LIMIT {[offset,] row_count | row_count OFFSET offset}]
        [PROCEDURE procedure_name(argument_list)]
        [INTO OUTFILE 'file_name' export_options
          | INTO DUMPFILE 'file_name']
        [FOR UPDATE | LOCK IN SHARE MODE]]

---

## 十单表记录查询

语法

    SELECT
        [ALL | DISTINCT | DISTINCTROW ]
          [HIGH_PRIORITY]
          [STRAIGHT_JOIN]
          [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
          [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
        select_expr, ...
        [FROM table_references
        [WHERE where_condition]
        [GROUP BY {col_name | expr | position}
          [ASC | DESC], ... [WITH ROLLUP]]
        [HAVING where_condition]
        [ORDER BY {col_name | expr | position}
          [ASC | DESC], ...]
        [LIMIT {[offset,] row_count | row_count OFFSET offset}]
        [PROCEDURE procedure_name(argument_list)]
        [INTO OUTFILE 'file_name' export_options
          | INTO DUMPFILE 'file_name']
        [FOR UPDATE | LOCK IN SHARE MODE]]

---

## 十一多表数据查询

UNION用的比较多union all是直接连接，取到得是所有值，记录可能有重复 union 是取唯一值，记录没有重复

1、UNION 的语法如下：

[SQL 语句 1]

UNION

[SQL 语句 2]

2、UNION ALL 的语法如下：

[SQL 语句 1]

UNION ALL

[SQL 语句 2]

效率：

UNION和UNION ALL关键字都是将两个结果集合并为一个，但这两者从使用和效率上来说都有所不同。

1、对重复结果的处理：UNION在进行表链接后会筛选掉重复的记录，Union All不会去除重复记录。

2、对排序的处理：Union将会按照字段的顺序进行排序；UNION ALL只是简单的将两个结果合并后就返回。

从效率上说，UNION ALL 要比UNION快很多，所以，如果可以确认合并的两个结果集中不包含重复数据且不需要排序时的话，那么就使用UNION ALL。

#### 笛卡尔积 

查询所有员工的信息和部门信息

    mysql> select * from emp ,dept;
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno | deptno | dname      | loc      |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |     10 | ACCOUNTING | NEW YORK |
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |     30 | SALES      | CHICAGO  |
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |     40 | OPERATIONS | BOSTON   |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |     20 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |     40 | OPERATIONS | BOSTON   |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |     20 | RESEARCH   | DALLAS   |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |     40 | OPERATIONS | BOSTON   |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |     10 | ACCOUNTING | NEW YORK |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |     30 | SALES      | CHICAGO  |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |     40 | OPERATIONS | BOSTON   |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |     20 | RESEARCH   | DALLAS   |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |     40 | OPERATIONS | BOSTON   |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |     20 | RESEARCH   | DALLAS   |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |     30 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |     40 | OPERATIONS | BOSTON   |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |     20 | RESEARCH   | DALLAS   |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |     30 | SALES      | CHICAGO  |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |     40 | OPERATIONS | BOSTON   |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |     10 | ACCOUNTING | NEW YORK |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |     30 | SALES      | CHICAGO  |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |     40 | OPERATIONS | BOSTON   |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |     20 | RESEARCH   | DALLAS   |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |     30 | SALES      | CHICAGO  |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |     40 | OPERATIONS | BOSTON   |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |     20 | RESEARCH   | DALLAS   |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |     40 | OPERATIONS | BOSTON   |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |     10 | ACCOUNTING | NEW YORK |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |     30 | SALES      | CHICAGO  |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |     40 | OPERATIONS | BOSTON   |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |     10 | ACCOUNTING | NEW YORK |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |     20 | RESEARCH   | DALLAS   |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |     30 | SALES      | CHICAGO  |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |     40 | OPERATIONS | BOSTON   |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |     10 | ACCOUNTING | NEW YORK |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |     30 | SALES      | CHICAGO  |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |     40 | OPERATIONS | BOSTON   |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |     20 | RESEARCH   | DALLAS   |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |     30 | SALES      | CHICAGO  |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |     40 | OPERATIONS | BOSTON   |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    56 rows in set (0.01 sec)
    

原来只有14条记录的，2张表一起查就变56条，

消除笛卡尔积

    mysql> select * from emp e,dept d where e.deptno=d.deptno;
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno | deptno | dname      | loc      |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |     30 | SALES      | CHICAGO  |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |     30 | SALES      | CHICAGO  |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |     30 | SALES      | CHICAGO  |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |     20 | RESEARCH   | DALLAS   |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |     10 | ACCOUNTING | NEW YORK |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+--------+------------+----------+
    14 rows in set (0.00 sec)
    

#### 内连接 INNER JION 

连接分为内连接 INNER JION，外连接 OUTER JION，交叉连接 CROSS JION

内连接： 在表关系的笛卡尔积数据记录中，保留表关系中所有匹配的数据记录，舍弃不匹配的数据记录

分为自然连接、等值连接，不等连接

#### 查询每个员工的编号、姓名、职位、基本工资、部门名称、部门位置 

    mysql> SELECT e.empno,e.ename,e.job,e.sal,d.dname,d.loc
        -> FROM emp e ,dept d
        -> WHERE e.deptno = d.deptno;
    +-------+--------+-----------+---------+------------+----------+
    | empno | ename  | job       | sal     | dname      | loc      |
    +-------+--------+-----------+---------+------------+----------+
    |  7369 | SMITH  | CLERK     |  800.00 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 1600.00 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7566 | JONES  | MANAGER   | 2975.00 | RESEARCH   | DALLAS   |
    |  7654 | MARTIN | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 2850.00 | SALES      | CHICAGO  |
    |  7782 | CLARK  | MANAGER   | 2450.00 | ACCOUNTING | NEW YORK |
    |  7788 | SCOTT  | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7839 | KING   | PRESIDENT | 5000.00 | ACCOUNTING | NEW YORK |
    |  7844 | TURNER | SALESMAN  | 1500.00 | SALES      | CHICAGO  |
    |  7876 | ADAMS  | CLERK     | 1100.00 | RESEARCH   | DALLAS   |
    |  7900 | JAMES  | CLERK     |  950.00 | SALES      | CHICAGO  |
    |  7902 | FORD   | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7934 | MILLER | CLERK     | 1300.00 | ACCOUNTING | NEW YORK |
    +-------+--------+-----------+---------+------------+----------+
    14 rows in set (0.00 sec)
    

第二种连接查询 

    mysql> SELECT e.empno,e.ename,e.job,e.sal,d.dname,d.loc
        -> FROM emp e INNER JOIN dept d ON e.deptno = d.deptno;
    +-------+--------+-----------+---------+------------+----------+
    | empno | ename  | job       | sal     | dname      | loc      |
    +-------+--------+-----------+---------+------------+----------+
    |  7369 | SMITH  | CLERK     |  800.00 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 1600.00 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7566 | JONES  | MANAGER   | 2975.00 | RESEARCH   | DALLAS   |
    |  7654 | MARTIN | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 2850.00 | SALES      | CHICAGO  |
    |  7782 | CLARK  | MANAGER   | 2450.00 | ACCOUNTING | NEW YORK |
    |  7788 | SCOTT  | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7839 | KING   | PRESIDENT | 5000.00 | ACCOUNTING | NEW YORK |
    |  7844 | TURNER | SALESMAN  | 1500.00 | SALES      | CHICAGO  |
    |  7876 | ADAMS  | CLERK     | 1100.00 | RESEARCH   | DALLAS   |
    |  7900 | JAMES  | CLERK     |  950.00 | SALES      | CHICAGO  |
    |  7902 | FORD   | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7934 | MILLER | CLERK     | 1300.00 | ACCOUNTING | NEW YORK |
    +-------+--------+-----------+---------+------------+----------+
    14 rows in set (0.00 sec)
    

结果相同

左连接

    mysql> SELECT e.empno,e.ename,e.job,e.sal,d.dname,d.loc
        -> FROM emp e LEFT JOIN dept d ON e.deptno = d.deptno;
    +-------+--------+-----------+---------+------------+----------+
    | empno | ename  | job       | sal     | dname      | loc      |
    +-------+--------+-----------+---------+------------+----------+
    |  7782 | CLARK  | MANAGER   | 2450.00 | ACCOUNTING | NEW YORK |
    |  7839 | KING   | PRESIDENT | 5000.00 | ACCOUNTING | NEW YORK |
    |  7934 | MILLER | CLERK     | 1300.00 | ACCOUNTING | NEW YORK |
    |  7369 | SMITH  | CLERK     |  800.00 | RESEARCH   | DALLAS   |
    |  7566 | JONES  | MANAGER   | 2975.00 | RESEARCH   | DALLAS   |
    |  7788 | SCOTT  | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7876 | ADAMS  | CLERK     | 1100.00 | RESEARCH   | DALLAS   |
    |  7902 | FORD   | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 1600.00 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7654 | MARTIN | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 2850.00 | SALES      | CHICAGO  |
    |  7844 | TURNER | SALESMAN  | 1500.00 | SALES      | CHICAGO  |
    |  7900 | JAMES  | CLERK     |  950.00 | SALES      | CHICAGO  |
    +-------+--------+-----------+---------+------------+----------+
    14 rows in set (0.00 sec)
    

右连接 

    mysql> SELECT e.empno,e.ename,e.job,e.sal,d.dname,d.loc
        -> FROM emp e RIGHT JOIN dept d ON e.deptno = d.deptno;
    +-------+--------+-----------+---------+------------+----------+
    | empno | ename  | job       | sal     | dname      | loc      |
    +-------+--------+-----------+---------+------------+----------+
    |  7369 | SMITH  | CLERK     |  800.00 | RESEARCH   | DALLAS   |
    |  7499 | ALLEN  | SALESMAN  | 1600.00 | SALES      | CHICAGO  |
    |  7521 | WARD   | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7566 | JONES  | MANAGER   | 2975.00 | RESEARCH   | DALLAS   |
    |  7654 | MARTIN | SALESMAN  | 1250.00 | SALES      | CHICAGO  |
    |  7698 | BLAKE  | MANAGER   | 2850.00 | SALES      | CHICAGO  |
    |  7782 | CLARK  | MANAGER   | 2450.00 | ACCOUNTING | NEW YORK |
    |  7788 | SCOTT  | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7839 | KING   | PRESIDENT | 5000.00 | ACCOUNTING | NEW YORK |
    |  7844 | TURNER | SALESMAN  | 1500.00 | SALES      | CHICAGO  |
    |  7876 | ADAMS  | CLERK     | 1100.00 | RESEARCH   | DALLAS   |
    |  7900 | JAMES  | CLERK     |  950.00 | SALES      | CHICAGO  |
    |  7902 | FORD   | ANALYST   | 3000.00 | RESEARCH   | DALLAS   |
    |  7934 | MILLER | CLERK     | 1300.00 | ACCOUNTING | NEW YORK |
    |  NULL | NULL   | NULL      |    NULL | OPERATIONS | BOSTON   |
    +-------+--------+-----------+---------+------------+----------+
    15 rows in set (0.00 sec)
    

#### 集合运算是一种二目运算符 

一共4种运算符,并，差，交，笛卡尔积

集合运算语法:

查询语句

[UNION | UNION ALL | INTERSECT|MINUS |]

查询语句

…

UNION(并集):返回查询结果的全部内容，但是重复内容不显示

UNION ALL(并集):返回查询结果的全部内容，但是重复内容显示

INTERSECT(交集):返回查询结果中的相同部分

MINUS(差集):返回查询结果中的不同部分

#### (UNION)并集 

    mysql> SELECT * from dept
        -> union
        -> select * from dept where deptno=10;
    +--------+------------+----------+
    | deptno | dname      | loc      |
    +--------+------------+----------+
    |     10 | ACCOUNTING | NEW YORK |
    |     20 | RESEARCH   | DALLAS   |
    |     30 | SALES      | CHICAGO  |
    |     40 | OPERATIONS | BOSTON   |
    +--------+------------+----------+
    4 rows in set (0.00 sec)
    

结果返回4条记录,重复内容未显示

#### (UNION ALL)并集 

    mysql> SELECT * FROM dept
        -> UNION ALL
        -> SELECT * FROM dept WHERE deptno=10;
    +--------+------------+----------+
    | deptno | dname      | loc      |
    +--------+------------+----------+
    |     10 | ACCOUNTING | NEW YORK |
    |     20 | RESEARCH   | DALLAS   |
    |     30 | SALES      | CHICAGO  |
    |     40 | OPERATIONS | BOSTON   |
    |     10 | ACCOUNTING | NEW YORK |
    +--------+------------+----------+
    5 rows in set (0.00 sec)
    

结果返回5条记录，重复内容会显示

#### 子查询一单行单列 

查询所有比SMITH工资高的人 

    mysql>  SELECT * FROM emp
        ->  WHERE sal>(
        -> SELECT e.sal
        -> FROM emp e
        -> WHERE e.ename='smith');
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    13 rows in set (0.00 sec)
    

#### 单行多列 

查询所有工资和职位和SMITH相同的人 

    mysql> SELECT * FROM emp
        ->  WHERE (sal,job)=(
        -> SELECT sal,job
        -> FROM emp
        -> WHERE ename='smith');
    +-------+-------+-------+------+---------------------+--------+------+--------+
    | empno | ename | job   | mgr  | hiredate            | sal    | comm | deptno |
    +-------+-------+-------+------+---------------------+--------+------+--------+
    |  7369 | SMITH | CLERK | 7902 | 1980-12-17 00:00:00 | 800.00 | NULL |     20 |
    +-------+-------+-------+------+---------------------+--------+------+--------+
    1 row in set (0.00 sec)
    

#### 多行单列 IN ANY,ALL EXISTS 

    mysql> SELECT * FROM emp
        -> WHERE deptno IN(
        -> SELECT deptno
        -> FROM dept);
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    14 rows in set (0.00 sec)
    

#### ANY 

* =ANY 功能与IN一样
* ANY(>=ANY) 比子查询中最小的记录还要大于（大于等于）
* <=any) 比子查询中最大的记录还要大于（小于等于）="" 
```
    mysql> SELECT sal
     -> FROM emp
     -> WHERE job='MANAGER';
    +---------+
    | sal |
    +---------+
    | 2975.00 |
    | 2850.00 |
    | 2450.00 |
    +---------+
    3 rows in set (0.00 sec)
```
使用ANY 
```
    mysql> SELECT ename,sal FROM emp
        -> WHERE sal >=ANY(
        -> SELECT sal
        -> FROM emp
        -> WHERE job='MANAGER');
    +-------+---------+
    | ename | sal     |
    +-------+---------+
    | JONES | 2975.00 |
    | BLAKE | 2850.00 |
    | CLARK | 2450.00 |
    | SCOTT | 3000.00 |
    | KING  | 5000.00 |
    | FORD  | 3000.00 |
    +-------+---------+
    6 rows in set (0.00 sec)
    

    mysql> SELECT ename,sal FROM emp
        -> WHERE sal >ANY(
        -> SELECT sal
        -> FROM emp
        -> WHERE job='MANAGER');
    +-------+---------+
    | ename | sal     |
    +-------+---------+
    | JONES | 2975.00 |
    | BLAKE | 2850.00 |
    | SCOTT | 3000.00 |
    | KING  | 5000.00 |
    | FORD  | 3000.00 |
    +-------+---------+
    5 rows in set (0.00 sec)
```

#### ALL 

* =ALL 功能与IN一样
* ALL(>=ALL) 比子查询中最大的记录还要大于（大于等于）
* <ALL(<=ALL) 比子查询中最小的记录还要小于（小于等于）

大于ALL 

    mysql> SELECT ename,sal,job FROM emp
        -> WHERE sal >ALL(
        -> SELECT sal
        -> FROM emp
        -> WHERE job='MANAGER');
    +-------+---------+-----------+
    | ename | sal     | job       |
    +-------+---------+-----------+
    | SCOTT | 3000.00 | ANALYST   |
    | KING  | 5000.00 | PRESIDENT |
    | FORD  | 3000.00 | ANALYST   |
    +-------+---------+-----------+
    3 rows in set (0.00 sec)
    

小于all 

    mysql> SELECT ename,sal,job FROM emp
        -> WHERE sal <ALL(
        -> SELECT sal
        -> FROM emp
        -> WHERE job='MANAGER');
    +--------+---------+----------+
    | ename  | sal     | job      |
    +--------+---------+----------+
    | SMITH  |  800.00 | CLERK    |
    | ALLEN  | 1600.00 | SALESMAN |
    | WARD   | 1250.00 | SALESMAN |
    | MARTIN | 1250.00 | SALESMAN |
    | TURNER | 1500.00 | SALESMAN |
    | ADAMS  | 1100.00 | CLERK    |
    | JAMES  |  950.00 | CLERK    |
    | MILLER | 1300.00 | CLERK    |
    +--------+---------+----------+
    8 rows in set (0.00 sec)
    

#### EXISTS的子查询 

关键字EXISTS是一个布尔类型，当返回的结果集时为TRUE，不能返回结果集时为FALSE.

查询部门表中有员工的部门或者没有员工部门 多行单列

    mysql> SELECT *
        -> FROM dept d
        -> WHERE NOT EXISTS(
        -> SELECT * FROM emp
        -> WHERE deptno = d.deptno
        -> );
    +--------+------------+--------+
    | deptno | dname      | loc    |
    +--------+------------+--------+
    |     40 | OPERATIONS | BOSTON |
    +--------+------------+--------+
    1 row in set (0.00 sec)
    

    mysql> SELECT *
        -> FROM dept d
        -> WHERE  EXISTS(
        -> SELECT * FROM emp
        -> WHERE deptno = d.deptno
        -> );
    +--------+------------+----------+
    | deptno | dname      | loc      |
    +--------+------------+----------+
    |     10 | ACCOUNTING | NEW YORK |
    |     20 | RESEARCH   | DALLAS   |
    |     30 | SALES      | CHICAGO  |
    +--------+------------+----------+
    3 rows in set (0.00 sec)
    

多行多列

查询员工表中各个部门的编辑，名称，地址，员工人数，平均工资

    mysql> SELECT d.deptno,d.dname,d.loc,COUNT(e.empno) counts,ROUND(AVG(e.sal),2) avgsal
        -> FROM emp e INNER JOIN dept d
        -> ON e.deptno=d.deptno
        -> GROUP BY deptno,dname,loc;
    +--------+------------+----------+--------+---------+
    | deptno | dname      | loc      | counts | avgsal  |
    +--------+------------+----------+--------+---------+
    |     10 | ACCOUNTING | NEW YORK |      3 | 2916.67 |
    |     20 | RESEARCH   | DALLAS   |      5 | 2175.00 |
    |     30 | SALES      | CHICAGO  |      6 | 1566.67 |
    +--------+------------+----------+--------+---------+
    3 rows in set (0.00 sec)
    
    mysql> SELECT d.deptno,d.dname,d.loc,COUNT(e.empno) counts,ROUND(AVG(e.sal),2) avgsal
        -> FROM emp e INNER JOIN dept d
        -> ON e.deptno=d.deptno
        -> GROUP BY deptno DESC,dname,loc;
    +--------+------------+----------+--------+---------+
    | deptno | dname      | loc      | counts | avgsal  |
    +--------+------------+----------+--------+---------+
    |     30 | SALES      | CHICAGO  |      6 | 1566.67 |
    |     20 | RESEARCH   | DALLAS   |      5 | 2175.00 |
    |     10 | ACCOUNTING | NEW YORK |      3 | 2916.67 |
    +--------+------------+----------+--------+---------+
    3 rows in set (0.00 sec)
    

查询每个部门的

    mysql> SELECT
        ->   e.deptno,
        ->   de.dname,
        ->   de.loc,
        ->   de.counts,
        ->   de.avgsal
        -> FROM
        ->   dept e
        ->   INNER JOIN
        ->     (SELECT
        ->       d.deptno,
        ->       d.dname,
        ->       d.loc,
        ->       COUNT(e.empno) counts,
        ->       ROUND(AVG(e.sal), 2) avgsal
        ->     FROM
        ->       emp e
        ->       INNER JOIN dept d
        ->         ON e.deptno = d.deptno
        ->     GROUP BY deptno DESC,
        ->       dname,
        ->       loc) de
        ->     ON e.deptno = de.deptno ;
    +--------+------------+----------+--------+---------+
    | deptno | dname      | loc      | counts | avgsal  |
    +--------+------------+----------+--------+---------+
    |     10 | ACCOUNTING | NEW YORK |      3 | 2916.67 |
    |     20 | RESEARCH   | DALLAS   |      5 | 2175.00 |
    |     30 | SALES      | CHICAGO  |      6 | 1566.67 |
    +--------+------------+----------+--------+---------+
    3 rows in set (0.00 sec)

--- 

## 十二子查询一(WHERE中的子查询)

子查询就是指的在一个完整的查询语句之中，嵌套若干个不同功能的小查询，从而一起完成复杂查询的一种编写形式，为了让读者更加清楚子查询的概念。

子查询返回结果

子查询可以返回的数据类型一共分为四种：

单行单列：返回的是一个具体列的内容，可以理解为一个单值数据；

单行多列：返回一行数据中多个列的内容；

多行单列：返回多行记录之中同一列的内容，相当于给出了一个操作范围；

多行多列：查询返回的结果是一张临时表；

在WHERE子句中使用子查询

在WHERE子句之中处理单行单列子查询、多行单列子查询、单行多列子查询。

### 单行单列子查询 

#### 查询公司之中工资最低的雇员的完整信息 

首先查询出最低工资 

    mysql> SELECT MIN(sal) FROM emp;
    +----------+
    | MIN(sal) |
    +----------+
    |   800.00 |
    +----------+
    1 row in set (0.00 sec)
    

再查询员工信息 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal =(
        -> SELECT MIN(sal)
        -> FROM emp
        -> );
    +-------+-------+-------+------+---------------------+--------+------+--------+
    | empno | ename | job   | mgr  | hiredate            | sal    | comm | deptno |
    +-------+-------+-------+------+---------------------+--------+------+--------+
    |  7369 | SMITH | CLERK | 7902 | 1980-12-17 00:00:00 | 800.00 | NULL |     20 |
    +-------+-------+-------+------+---------------------+--------+------+--------+
    1 row in set (0.00 sec)
    

#### 查询出基本工资比ALLEN低的全部雇员信息 

查出ALLEN的基本工资 

    mysql> SELECT sal
        -> FROM emp
        -> WHERE ename='allen';
    +---------+
    | sal     |
    +---------+
    | 1600.00 |
    +---------+
    1 row in set (0.00 sec)
    

再查出比这个工资还低的员工信息 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal<(
        -> SELECT sal
        -> FROM emp
        -> WHERE ename='allen'
        -> );
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job      | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK    | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7521 | WARD   | SALESMAN | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7654 | MARTIN | SALESMAN | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7844 | TURNER | SALESMAN | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK    | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK    | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7934 | MILLER | CLERK    | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    7 rows in set (0.00 sec)
    

#### 查询基本工资高于公司平均薪金的全部雇员信息 

平均工资 

    mysql> SELECT AVG(sal)
        -> FROM emp;
    +-------------+
    | AVG(sal)    |
    +-------------+
    | 2073.214286 |
    +-------------+
    1 row in set (0.00 sec)
    

高于平均工资的员工信息 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal >(
        -> SELECT AVG(sal)
        -> FROM emp
        -> );
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    | empno | ename | job       | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    |  7566 | JONES | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 | NULL |     20 |
    |  7698 | BLAKE | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 | NULL |     30 |
    |  7782 | CLARK | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 | NULL |     10 |
    |  7788 | SCOTT | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 | NULL |     20 |
    |  7839 | KING  | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 | NULL |     10 |
    |  7902 | FORD  | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 | NULL |     20 |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    6 rows in set (0.00 sec)
    

### 单行多列子查询 

#### 查找出与ALLEN从事同一工作，并且基本工资高于雇员编号为7521的全部雇员信息, 

先查出ALLEN的工作和编辑为7521的工资,再分别添加条件 

    SELECT * 
    FROM emp
    WHERE job=(
    SELECT job
    FROM emp
    WHERE ename='allen'
    ) 
    AND sal >(
    SELECT sal 
    FROM emp
    WHERE empno=7521
    ) AND  NOT ename='allen';
    

#### 查询与SCOTT从事同一工作且工资相同的雇员信息 

查询出SCOTT的工作 

    mysql> SELECT job
        -> FROM emp
        -> WHERE ename='scott';
    +---------+
    | job     |
    +---------+
    | ANALYST |
    +---------+
    1 row in set (0.00 sec)
    

再查询出相同工作的员工 

    mysql> SELECT *
        -> FROM emp
        -> WHERE job=(
        -> SELECT job
        -> FROM emp
        -> WHERE ename='scott'
        -> ) AND NOT ename='scott';
    +-------+-------+---------+------+---------------------+---------+------+--------+
    | empno | ename | job     | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    |  7902 | FORD  | ANALYST | 7566 | 1981-12-03 00:00:00 | 3000.00 | NULL |     20 |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    1 row in set (0.00 sec)
    

#### 查询与雇员7566从事同一工作且领导相同的全部雇员信息 

查询出7566的工作与领导 

    mysql> select job,mgr
        -> from emp
        -> where empno=7566;
    +---------+------+
    | job     | mgr  |
    +---------+------+
    | MANAGER | 7839 |
    +---------+------+
    1 row in set (0.00 sec)
    

再查询出相同的员工 

    mysql> SELECT
        ->   *
        -> FROM
        ->   emp
        -> WHERE (job, mgr) =
        ->   (SELECT
        ->     job,
        ->     mgr
        ->   FROM
        ->     emp
        ->   WHERE empno = 7566) ;
    +-------+-------+---------+------+---------------------+---------+------+--------+
    | empno | ename | job     | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    |  7566 | JONES | MANAGER | 7839 | 1981-04-02 00:00:00 | 2975.00 | NULL |     20 |
    |  7698 | BLAKE | MANAGER | 7839 | 1981-05-01 00:00:00 | 2850.00 | NULL |     30 |
    |  7782 | CLARK | MANAGER | 7839 | 1981-06-09 00:00:00 | 2450.00 | NULL |     10 |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    3 rows in set (0.00 sec)
    

### 多行单列子查询 

主要使用三种操作符：IN、ANY、ALL

### IN操作 

#### 查询出与每个部门中最低工资相同的全部雇员信息 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal IN(
        -> SELECT MIN(sal)
        -> FROM emp
        -> GROUP BY deptno
        -> );
    +-------+--------+-------+------+---------------------+---------+------+--------+
    | empno | ename  | job   | mgr  | hiredate            | sal     | comm | deptno |
    +-------+--------+-------+------+---------------------+---------+------+--------+
    |  7369 | SMITH  | CLERK | 7902 | 1980-12-17 00:00:00 |  800.00 | NULL |     20 |
    |  7900 | JAMES  | CLERK | 7698 | 1981-12-03 00:00:00 |  950.00 | NULL |     30 |
    |  7934 | MILLER | CLERK | 7782 | 1982-01-23 00:00:00 | 1300.00 | NULL |     10 |
    +-------+--------+-------+------+---------------------+---------+------+--------+
    3 rows in set (0.00 sec)
    

#### 查询出不与每个部门中最低工资相同的全部雇员信息 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal NOT IN(
        -> SELECT MIN(sal)
        -> FROM emp
        -> GROUP BY deptno
        -> );
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    11 rows in set (0.00 sec)
    

### ANY在使用中有如下三种使用形式： 

=ANY：表示与子查询中的每个元素进行比较，功能与IN类似（然而<>ANY不等价于NOT IN）

ANY：比子查询中返回结果的最小的要大（还包含了>=ANY）

<ANY：比子查询中返回结果的最大的要小（还包含了<=ANY）

#### 查询出每个部门经理的工资 

    mysql> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno;
    +----------+
    | MIN(sal) |
    +----------+
    |  2450.00 |
    |  2975.00 |
    |  2850.00 |
    +----------+
    3 rows in set (0.00 sec)
    

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal = ANY(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+-------+---------+------+---------------------+---------+------+--------+
    | empno | ename | job     | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    |  7566 | JONES | MANAGER | 7839 | 1981-04-02 00:00:00 | 2975.00 | NULL |     20 |
    |  7698 | BLAKE | MANAGER | 7839 | 1981-05-01 00:00:00 | 2850.00 | NULL |     30 |
    |  7782 | CLARK | MANAGER | 7839 | 1981-06-09 00:00:00 | 2450.00 | NULL |     10 |
    +-------+-------+---------+------+---------------------+---------+------+--------+
    3 rows in set (0.00 sec)
    

#### 查询出每个部门大于经理的工资 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal > ANY(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    | empno | ename | job       | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    |  7566 | JONES | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 | NULL |     20 |
    |  7698 | BLAKE | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 | NULL |     30 |
    |  7788 | SCOTT | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 | NULL |     20 |
    |  7839 | KING  | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 | NULL |     10 |
    |  7902 | FORD  | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 | NULL |     20 |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    5 rows in set (0.00 sec)
    

#### 查询出每个部门小于经理的工资 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal < ANY(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job      | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK    | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7499 | ALLEN  | SALESMAN | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7654 | MARTIN | SALESMAN | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7698 | BLAKE  | MANAGER  | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |
    |  7782 | CLARK  | MANAGER  | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK    | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK    | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7934 | MILLER | CLERK    | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    10 rows in set (0.00 sec)
    

### ALL操作符有以下三种用法： 

<>ALL：等价于NOT IN（但是=ALL并不等价于IN）

ALL：比子查询中最大的值还要大（还包含了>=ALL）

<ALL：比子查询中最小的值还要小（还包含了<=ALL）

#### 查询出每个部门不等于经理的工资 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal<>ALL(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    11 rows in set (0.00 sec)
    

#### 查询出每个部门大于经理的工资 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal>ALL(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    | empno | ename | job       | mgr  | hiredate            | sal     | comm | deptno |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    |  7788 | SCOTT | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 | NULL |     20 |
    |  7839 | KING  | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 | NULL |     10 |
    |  7902 | FORD  | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 | NULL |     20 |
    +-------+-------+-----------+------+---------------------+---------+------+--------+
    3 rows in set (0.00 sec)
    

#### 查询出每个部门小于经理的工资 

    mysql> SELECT *
        -> FROM emp
        -> WHERE sal<ALL(
        -> SELECT MIN(sal)
        -> FROM emp
        -> WHERE job='manager'
        -> GROUP BY deptno
        -> );
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job      | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK    | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7499 | ALLEN  | SALESMAN | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7654 | MARTIN | SALESMAN | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7844 | TURNER | SALESMAN | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK    | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK    | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7934 | MILLER | CLERK    | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+----------+------+---------------------+---------+---------+--------+
    8 rows in set (0.00 sec)
    

### 空数据判断 

在SQL之中提供了一个exists结构用于判断子查询是否有数据返回。如果子查询中有数据返回，则exists结构返回true，反之返回false。

#### 验证exists结构 

    --验证exists结构
    SELECT * FROM emp
        WHERE EXISTS(   --返回空值，没有内容输出
          SELECT * FROM emp WHERE empno=9999); --没有这个编号的员工
    mysql> select *
        -> from emp
        -> where exists(
        -> select * from emp where empno=9999);
    Empty set (0.00 sec)
    

### 有数据返回 

    SELECT * FROM emp
     WHERE EXISTS(SELECT * FROM emp);--有内容将返回数据
    

    mysql> select * from emp
        -> where exists(
        -> select * from emp);
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    | empno | ename  | job       | mgr  | hiredate            | sal     | comm    | deptno |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    |  7369 | SMITH  | CLERK     | 7902 | 1980-12-17 00:00:00 |  800.00 |    NULL |     20 |
    |  7499 | ALLEN  | SALESMAN  | 7698 | 1981-02-20 00:00:00 | 1600.00 |  300.00 |     30 |
    |  7521 | WARD   | SALESMAN  | 7698 | 1981-02-22 00:00:00 | 1250.00 |  500.00 |     30 |
    |  7566 | JONES  | MANAGER   | 7839 | 1981-04-02 00:00:00 | 2975.00 |    NULL |     20 |
    |  7654 | MARTIN | SALESMAN  | 7698 | 1981-09-28 00:00:00 | 1250.00 | 1400.00 |     30 |
    |  7698 | BLAKE  | MANAGER   | 7839 | 1981-05-01 00:00:00 | 2850.00 |    NULL |     30 |
    |  7782 | CLARK  | MANAGER   | 7839 | 1981-06-09 00:00:00 | 2450.00 |    NULL |     10 |
    |  7788 | SCOTT  | ANALYST   | 7566 | 1982-12-09 00:00:00 | 3000.00 |    NULL |     20 |
    |  7839 | KING   | PRESIDENT | NULL | 1981-11-17 00:00:00 | 5000.00 |    NULL |     10 |
    |  7844 | TURNER | SALESMAN  | 7698 | 1981-09-08 00:00:00 | 1500.00 |    0.00 |     30 |
    |  7876 | ADAMS  | CLERK     | 7788 | 1983-01-12 00:00:00 | 1100.00 |    NULL |     20 |
    |  7900 | JAMES  | CLERK     | 7698 | 1981-12-03 00:00:00 |  950.00 |    NULL |     30 |
    |  7902 | FORD   | ANALYST   | 7566 | 1981-12-03 00:00:00 | 3000.00 |    NULL |     20 |
    |  7934 | MILLER | CLERK     | 7782 | 1982-01-23 00:00:00 | 1300.00 |    NULL |     10 |
    +-------+--------+-----------+------+---------------------+---------+---------+--------+
    14 rows in set (0.00 sec)
    

#### 测试取反 

    mysql>  SELECT * FROM emp
        ->  WHERE NOT EXISTS(SELECT * FROM emp);--有数据，但取返，没有内容输出
    Empty set (0.00 sec)



---

## 十三子查询二(在HAVING子句中使用子查询)


HAVING子句的主要功能是对分组后的数据进行过滤，如果子查询在HAVING中表示要进行分组过滤，一般返回单行单列的数据

#### 查询部门编号，人数，平均工资，并且要求这些部门的平均工资高于公司的平均工资 

    mysql> SELECT deptno,COUNT(empno),ROUND(AVG(sal),2)
        -> FROM emp
        -> GROUP BY deptno
        -> HAVING AVG(sal)>(
        -> SELECT AVG(sal)
        -> FROM emp);
    +--------+--------------+-------------------+
    | deptno | COUNT(empno) | ROUND(AVG(sal),2) |
    +--------+--------------+-------------------+
    |     10 |            3 |           2916.67 |
    |     20 |            5 |           2175.00 |
    +--------+--------------+-------------------+
    2 rows in set (0.00 sec)

---
##  十四子查询三(在FROM子句中使用子查询)

    mysql> SELECT d.deptno,d.dname,d.loc,dp.counts,dp.avgsal
        -> FROM dept d,(
        -> SELECT deptno,COUNT(empno) counts,AVG(sal) avgsal
        -> FROM emp
        -> GROUP BY deptno
        -> )dp
        -> WHERE d.deptno = dp.deptno;
    +--------+------------+----------+--------+-------------+
    | deptno | dname      | loc      | counts | avgsal      |
    +--------+------------+----------+--------+-------------+
    |     10 | ACCOUNTING | NEW YORK |      3 | 2916.666667 |
    |     20 | RESEARCH   | DALLAS   |      5 | 2175.000000 |
    |     30 | SALES      | CHICAGO  |      6 | 1566.666667 |
    +--------+------------+----------+--------+-------------+
    3 rows in set (0.00 sec)
    

查询出所有在部门SALES(销售部)工作的员工编号，姓名，基本工资，奖金，职位，入职日期，部门最高和最低工资 

    mysql> SELECT e.empno,e.ename,e.sal,e.comm,e.job,e.hiredate,e.deptno,temp.maxsal,temp.minsal
        -> FROM emp e,(
        ->             SELECT deptno dno,MAX(sal) maxsal,MIN(sal) minsal
        ->             FROM emp
        ->             GROUP BY deptno
        ->             ) temp
        -> WHERE e.deptno=(SELECT deptno
        ->                  FROM dept
        ->                  WHERE dname='SALES')
        ->                  AND e.deptno=temp.dno;
    +-------+--------+---------+---------+----------+---------------------+--------+---------+--------+
    | empno | ename  | sal     | comm    | job      | hiredate            | deptno | maxsal  | minsal |
    +-------+--------+---------+---------+----------+---------------------+--------+---------+--------+
    |  7499 | ALLEN  | 1600.00 |  300.00 | SALESMAN | 1981-02-20 00:00:00 |     30 | 2850.00 | 950.00 |
    |  7521 | WARD   | 1250.00 |  500.00 | SALESMAN | 1981-02-22 00:00:00 |     30 | 2850.00 | 950.00 |
    |  7654 | MARTIN | 1250.00 | 1400.00 | SALESMAN | 1981-09-28 00:00:00 |     30 | 2850.00 | 950.00 |
    |  7698 | BLAKE  | 2850.00 |    NULL | MANAGER  | 1981-05-01 00:00:00 |     30 | 2850.00 | 950.00 |
    |  7844 | TURNER | 1500.00 |    0.00 | SALESMAN | 1981-09-08 00:00:00 |     30 | 2850.00 | 950.00 |
    |  7900 | JAMES  |  950.00 |    NULL | CLERK    | 1981-12-03 00:00:00 |     30 | 2850.00 | 950.00 |
    +-------+--------+---------+---------+----------+---------------------+--------+---------+--------+
    6 rows in set (0.00 sec)
    

#### 列出工资比ALLEN或者CLARK多的所有员工的编号，姓名，基本工资，部门名称，领导姓名，部门人数 

    mysql> SELECT e.empno,e.ename,e.sal,d.dname,m.ename,temp.con
        -> FROM emp e,dept d,emp m,(
        ->                           SELECT deptno dno,COUNT(empno) con
        ->                           FROM emp
        ->                           GROUP BY deptno
        ->                           )temp
        -> WHERE e.sal>ANY(SELECT sal
        ->                  FROM emp
        ->                  WHERE ename IN('ALLEN','CLARK')
        ->                  )
        ->               AND e.ename NOT IN ('ALLEN','CLARK')
        ->               AND e.deptno=d.deptno
        ->               AND e.mgr=m.empno
        ->               AND e.deptno=temp.dno;
    +-------+-------+---------+----------+-------+-----+
    | empno | ename | sal     | dname    | ename | con |
    +-------+-------+---------+----------+-------+-----+
    |  7788 | SCOTT | 3000.00 | RESEARCH | JONES |   5 |
    |  7902 | FORD  | 3000.00 | RESEARCH | JONES |   5 |
    |  7566 | JONES | 2975.00 | RESEARCH | KING  |   5 |
    |  7698 | BLAKE | 2850.00 | SALES    | KING  |   6 |
    +-------+-------+---------+----------+-------+-----+
    4 rows in set (0.00 sec)
    

#### 列出公司各个部门的经理（一个部门只有一个）的姓名,工资，部门名称，部门人数，部门平均工资 

    mysql> SELECT e.ename,e.sal,d.deptno,d.dname,tmp.counts,tmp.avgsal,e.job
        -> FROM emp e,dept d,(
        -> SELECT deptno,COUNT(empno) counts,AVG(sal) avgsal
        -> FROM emp
        -> GROUP BY deptno
        -> )tmp
        -> WHERE e.deptno=d.deptno
        -> AND d.deptno=tmp.deptno
        -> AND e.job='manager';
    +-------+---------+--------+------------+--------+-------------+---------+
    | ename | sal     | deptno | dname      | counts | avgsal      | job     |
    +-------+---------+--------+------------+--------+-------------+---------+
    | CLARK | 2450.00 |     10 | ACCOUNTING |      3 | 2916.666667 | MANAGER |
    | JONES | 2975.00 |     20 | RESEARCH   |      5 | 2175.000000 | MANAGER |
    | BLAKE | 2850.00 |     30 | SALES      |      6 | 1566.666667 | MANAGER |
    +-------+---------+--------+------------+--------+-------------+---------+
    3 rows in set (0.00 sec)

---

## 十五子查询四(在select子句中使用子查询)

使用from 实现 

    mysql> SELECT d.deptno,d.dname,d.loc,tmp.counts,tmp.avgsal
        -> FROM dept d,(
        -> SELECT deptno,COUNT(empno) counts,AVG(sal) avgsal
        -> FROM emp
        -> GROUP BY deptno
        -> ) tmp
        -> WHERE d.deptno=tmp.deptno;
    +--------+------------+----------+--------+-------------+
    | deptno | dname      | loc      | counts | avgsal      |
    +--------+------------+----------+--------+-------------+
    |     10 | ACCOUNTING | NEW YORK |      3 | 2916.666667 |
    |     20 | RESEARCH   | DALLAS   |      5 | 2175.000000 |
    |     30 | SALES      | CHICAGO  |      6 | 1566.666667 |
    +--------+------------+----------+--------+-------------+
    3 rows in set (0.00 sec)
    

select

    mysql> SELECT d.deptno,d.dname,d.loc,
        -> (SELECT COUNT(empno) FROM emp WHERE emp.deptno=d.deptno GROUP BY emp.deptno) con,
        -> (SELECT AVG(sal) FROM emp WHERE emp.`deptno`=d.deptno GROUP BY emp.`deptno`) avgsal
        -> FROM dept d;
    +--------+------------+----------+------+-------------+
    | deptno | dname      | loc      | con  | avgsal      |
    +--------+------------+----------+------+-------------+
    |     10 | ACCOUNTING | NEW YORK |    3 | 2916.666667 |
    |     20 | RESEARCH   | DALLAS   |    5 | 2175.000000 |
    |     30 | SALES      | CHICAGO  |    6 | 1566.666667 |
    |     40 | OPERATIONS | BOSTON   | NULL |        NULL |
    +--------+------------+----------+------+-------------+
    4 rows in set (0.00 sec)

---

## 十六常用字符函数

    CONCAT(S1,S2,S3...SN)
    

将传入的函数连接起来返回所合并字符串数据，如果其中一个为NULL，则返加值为NULL 

    mysql> select concat('M','y','S','q','l');
    +-----------------------------+
    | concat('M','y','S','q','l') |
    +-----------------------------+
    | MySql                       |
    +-----------------------------+
    1 row in set (0.00 sec)
    

有NULL 

    mysql> select concat('M','y','S','q','l',null);
    +----------------------------------+
    | concat('M','y','S','q','l',null) |
    +----------------------------------+
    | NULL                             |
    +----------------------------------+
    1 row in set (0.00 sec)
    

实现当前时间CURDATE和数值合并 

    mysql> select concat(curdate(),13.27);
    +-------------------------+
    | concat(curdate(),13.27) |
    +-------------------------+
    | 2017-02-1713.27         |
    +-------------------------+
    1 row in set (0.03 sec)
    

#### 全并字符串函数CONCAT_WS() 

    CONCAT_WS(S1,S2,S3...SN)
    

与CONCAT相比多了一个SEP分割符参数，即不仅将传的其他参数连接起来，而且还会通过分割符将各个字符串侵害开，分隔答可以是一个字符串，也可以是其他参数。

如果分隔符为NULL，则返回结果为NULL，函数会忽略任何分隔符参数后的NULL。

    mysql>  SELECT CONCAT_WS('-','0755','518000');
    +--------------------------------+
    | CONCAT_WS('-','0755','518000') |
    +--------------------------------+
    | 0755-518000                    |
    +--------------------------------+
    1 row in set (0.00 sec)
    

NULL 

    mysql>  SELECT CONCAT_WS(null,'0755','518000');
    +---------------------------------+
    | CONCAT_WS(null,'0755','518000') |
    +---------------------------------+
    | NULL                            |
    +---------------------------------+
    1 row in set (0.00 sec)
    

当侵害符后，参数存在NULL时 

    mysql>  SELECT CONCAT_WS('-','0755',null,'518000');
    +-------------------------------------+
    | CONCAT_WS('-','0755',null,'518000') |
    +-------------------------------------+
    | 0755-518000                         |
    +-------------------------------------+
    1 row in set (0.00 sec)
    
    mysql>  SELECT CONCAT_WS('-',null,'0755','518000');
    +-------------------------------------+
    | CONCAT_WS('-',null,'0755','518000') |
    +-------------------------------------+
    | 0755-518000                         |
    +-------------------------------------+
    1 row in set (0.00 sec)
    

#### 比较字符大小函数STRCMP() 

STRCMP(S1,S2)

比较字符串1，与字符串2，如果参数1大于参数2，返回1，

如果参数1小参数2，返回-1

如果相等则返回0

    mysql> SELECT STRCMP('ABC','abc'),
        -> STRCMP('abc','abd'),
        -> STRCMP('abc','abb');
    +---------------------+---------------------+---------------------+
    | STRCMP('ABC','abc') | STRCMP('abc','abd') | STRCMP('abc','abb') |
    +---------------------+---------------------+---------------------+
    |                   0 |                  -1 |                   1 |
    +---------------------+---------------------+---------------------+
    1 row in set (0.00 sec)
    

#### 获取字符串长度函数LENGTH() 

LENGTH(STR) 

    mysql> SELECT 'Mysql' 英文字符,
        -> LENGTH('Mysql') 长度,
        -> '常用数' 中文字符,
        -> LENGTH('常用数') 长度;
    +--------------+--------+--------------+--------+
    | 英文字符     | 长度   | 中文字符     | 长度   |
    +--------------+--------+--------------+--------+
    | Mysql        |      5 | 常用数       |      9 |
    +--------------+--------+--------------+--------+
    1 row in set (0.00 sec)
    

中文字符一个占3位

#### 获取字符串长度函数CHAR_LENGTH() 

CHAR_LENGTH(str) 

    mysql> SELECT 'Mysql' 英文字符,
        -> CHAR_LENGTH('Mysql') 长度,
        -> '常用数' 中文字符,
        -> CHAR_LENGTH('常用数') 长度;
    +--------------+--------+--------------+--------+
    | 英文字符     | 长度   | 中文字符     | 长度   |
    +--------------+--------+--------------+--------+
    | Mysql        |      5 | 常用数       |      3 |
    +--------------+--------+--------------+--------+
    1 row in set (0.00 sec)
    

中文还是占一个字符

#### 字母大小写转换函数UPPPER()和函数LOWER() 

UPPER(STR)将字符所有字符转换为大写

ucase(str)将字符所有字符转换为大写

LOWER(STR)将字符所有字符转换为小写

LCASE(STR)将字符所有字符转换为小写

    mysql> select 'mysql' 字符串,upper('mysql') 转换后,ucase('mysql') 转换后,lower('MYsQL') 转换后, lcase('MYSQL') 转换后;
    +-----------+-----------+-----------+-----------+-----------+
    | 字符串    | 转换后    | 转换后    | 转换后    | 转换后    |
    +-----------+-----------+-----------+-----------+-----------+
    | mysql     | MYSQL     | MYSQL     | mysql     | mysql     |
    +-----------+-----------+-----------+-----------+-----------+
    1 row in set (0.00 sec)
    

#### 返加字符串位置的FIND_IN_SET() 

FIND_IN_SET(s1,,s2)

会返回在字符串s2中与s1相匹配的字符口中的益，参数S2字符串中将包含若干个用逗号隔开的字符串

    mysql> SELECT FIND_IN_SET('MySQL','oracle,sql,server,MySql');
    +------------------------------------------------+
    | FIND_IN_SET('MySQL','oracle,sql,server,MySql') |
    +------------------------------------------------+
    |                                              4 |
    +------------------------------------------------+
    1 row in set (0.00 sec)
    

#### 返回指定字符串的位置FIELD() 

FIELD(str,s1,,s2,…)返回第一个与字符串str，匹配的字符串的位置 

    mysql> SELECT FIELD('MySQL','oracle','MySql','sql','server');
    +------------------------------------------------+
    | FIELD('MySQL','oracle','MySql','sql','server') |
    +------------------------------------------------+
    |                                              2 |
    +------------------------------------------------+
    1 row in set (0.00 sec)
    

#### 返回子字符串相匹配的开始位置 

LOCATE(str1,str)

返回参数str中字符串str1的开始位置

POSITION(str1 in str)

instr(str,str1)

    mysql> SELECT LOCATE('sql','mysql'),
        ->        POSITION('sql' IN 'MySQL'),
        ->        INSTR('MySql','my');
    +-----------------------+----------------------------+---------------------+
    | LOCATE('sql','mysql') | POSITION('sql' IN 'MySQL') | INSTR('MySql','my') |
    +-----------------------+----------------------------+---------------------+
    |                     3 |                          3 |                   1 |
    +-----------------------+----------------------------+---------------------+
    1 row in set (0.00 sec)
    

#### 返回指定位置的字符串ELT() 

ELT(N，s1,s2,s3…);

    mysql> SELECT ELT(3,'MySQL','oracle','MySql','sql','server');
    +------------------------------------------------+
    | ELT(3,'MySQL','oracle','MySql','sql','server') |
    +------------------------------------------------+
    | MySql                                          |
    +------------------------------------------------+
    1 row in set (0.00 sec)
    
    mysql> SELECT ELT(0,'MySQL','oracle','MySql','sql','server');
    +------------------------------------------------+
    | ELT(0,'MySQL','oracle','MySql','sql','server') |
    +------------------------------------------------+
    | NULL                                           |
    +------------------------------------------------+
    1 row in set (0.00 sec)
    
    mysql> SELECT ELT(-1,'MySQL','oracle','MySql','sql','server');
    +-------------------------------------------------+
    | ELT(-1,'MySQL','oracle','MySql','sql','server') |
    +-------------------------------------------------+
    | NULL                                            |
    +-------------------------------------------------+
    1 row in set (0.01 sec)
    
    mysql> SELECT ELT(8,'MySQL','oracle','MySql','sql','server');
    +------------------------------------------------+
    | ELT(8,'MySQL','oracle','MySql','sql','server') |
    +------------------------------------------------+
    | NULL                                           |
    +------------------------------------------------+
    1 row in set (0.00 sec)
    

#### 选择字符串的MAKE_SET（） 

MAKE_SET（num,s1,s2,s3,…）

    SELECT BIN(5) 二进制数,MAKE_SET(5,'MySQL','Oracle','SQL Server','PostgreSQL') 选取后的字符串;
    +--------------+-----------------------+
    | 二进制数     | 选取后的字符串        |
    +--------------+-----------------------+
    | 101          | MySQL,SQL Server      |
    +--------------+-----------------------+
    1 row in set (0.00 sec)
    

由于5的二进制为101,所以选择MySQL,SQL Server ，第一个和第三个 

    mysql> select BIN(7) 二进制数,MAKE_SET(7,'MySQL','Oracle','SQL Server','PostgreSQL') 选取后的字符串;
    +--------------+-------------------------+
    | 二进制数     | 选取后的字符串          |
    +--------------+-------------------------+
    | 111          | MySQL,Oracle,SQL Server |
    +--------------+-------------------------+
    1 row in set (0.00 sec)
    

7为111,选择为前3个

#### 从现有字符串中截取子字符串LEFT()和RIGHT 

LEFT(str,num) RIGHT(str,num)

从左边或右边截取子字符串

返回字符串str中包含前NUM个字符，从左或者从右边数的字符串

    mysql> SELECT 'MySQL' 字符串,LEFT('MySQL',2) 前2个字符串 ,RIGHT('MySQL',3) 后3个字符串;
    +-----------+------------------+------------------+
    | 字符串    | 前2个字符串      | 后3个字符串      |
    +-----------+------------------+------------------+
    | MySQL     | My               | SQL              |
    +-----------+------------------+------------------+
    1 row in set (0.00 sec)
    

#### 截取指定位置和长度子字符串SUBSTRING和MID() 

SUBSTRING(str,num,len)

返回字符串str第num个位置开始长度为len的子字符串

MID(str,num,len)

返回字符串str第num个位置开始长度为len的子字符串

    mysql> SELECT 'oraclemysql' 字符串,SUBSTR('oraclemysql',1,5),SUBSTR('oraclemysql',7),SUBSTRING('oraclemysql',7,5),SUBSTRING('oraclemysql',7),MID('oraclemysql',7,5),MID('oraclemysql',7);
    +-------------+---------------------------+-------------------------+------------------------------+----------------------------+------------------------+----------------------+
    | 字符串      | SUBSTR('oraclemysql',1,5) | SUBSTR('oraclemysql',7) | SUBSTRING('oraclemysql',7,5) | SUBSTRING('oraclemysql',7) | MID('oraclemysql',7,5) | MID('oraclemysql',7) |
    +-------------+---------------------------+-------------------------+------------------------------+----------------------------+------------------------+----------------------+
    | oraclemysql | oracl                     | mysql                   | mysql                        | mysql                      | mysql                  | mysql                |
    +-------------+---------------------------+-------------------------+------------------------------+----------------------------+------------------------+----------------------+
    1 row in set (0.00 sec)
    

#### 去队字符串的首空格LTRIM() 

LTRIM(STR) 

    mysql> SELECT  CONCAT('-',' MySQL ','-') 原来字符串,
        -> CHAR_LENGTH(CONCAT('-',' MySQL ','-'))  原来字符串长度,
        -> CONCAT('-',LTRIM(' MySQL '),'-') 处理后字符串,
        -> CHAR_LENGTH(CONCAT('-',LTRIM(' MySQL '),'-'))  处理后字符串长度;
    +-----------------+-----------------------+--------------------+--------------------------+
    | 原来字符串      | 原来字符串长度        | 处理后字符串       | 处理后字符串长度         |
    +-----------------+-----------------------+--------------------+--------------------------+
    | - MySQL -       |                     9 | -MySQL -           |                        8 |
    +-----------------+-----------------------+--------------------+--------------------------+
    1 row in set (0.00 sec)
    

#### 去队字符串的尾空格RTRIM() 

RTRIM(STR) 

    mysql> SELECT  CONCAT('-',' MySQL ','-') 原来字符串,
        -> CHAR_LENGTH(CONCAT('-',' MySQL ','-'))  原来字符串长度,
        -> CONCAT('-',RTRIM(' MySQL '),'-') 处理后字符串,
        -> CHAR_LENGTH(CONCAT('-',RTRIM(' MySQL '),'-'))  处理后字符串长度;
    +-----------------+-----------------------+--------------------+--------------------------+
    | 原来字符串      | 原来字符串长度        | 处理后字符串       | 处理后字符串长度         |
    +-----------------+-----------------------+--------------------+--------------------------+
    | - MySQL -       |                     9 | - MySQL-           |                        8 |
    +-----------------+-----------------------+--------------------+--------------------------+
    1 row in set (0.00 sec)
    

#### 去队字符串的首尾空格TRIM() 

TRIM(STR) 

    mysql> SELECT  CONCAT('-',' MySQL ','-') 原来字符串,
        -> CHAR_LENGTH(CONCAT('-',' MySQL ','-'))  原来字符串长度,
        -> CONCAT('-',TRIM(' MySQL '),'-') 处理后字符串,
        -> CHAR_LENGTH(CONCAT('-',TRIM(' MySQL '),'-'))  处理后字符串长度;
    +-----------------+-----------------------+--------------------+--------------------------+
    | 原来字符串      | 原来字符串长度        | 处理后字符串       | 处理后字符串长度         |
    +-----------------+-----------------------+--------------------+--------------------------+
    | - MySQL -       |                     9 | -MySQL-            |                        7 |
    +-----------------+-----------------------+--------------------+--------------------------+
    1 row in set (0.00 sec)
    

#### 替换字符串insert 

INSERT(str,pos,len,newstr)

将字符串str中pos位置开始长度为len的字符串使用newstr来替换

    mysql> SELECT '这是MySQL数据库管理系统' 原字符串,
        -> INSERT('这是MySQL数据库管理系统',3,5,'Oracle') 替换后字符串;
    +----------------------------------+-----------------------------------+
    | 原字符串                         | 替换后字符串                      |
    +----------------------------------+-----------------------------------+
    | 这是MySQL数据库管理系统          | 这是Oracle数据库管理系统          |
    +----------------------------------+-----------------------------------+
    1 row in set (0.00 sec)
    

当长度大于原来的字符串 

    mysql> SELECT '这是MySQL数据库管理系统' 原字符串,
        -> CHAR_LENGTH('这是MySQL数据库管理系统') 字符串长度,
        -> INSERT('这是MySQL数据库管理系统',20,5,'Oracle') 替换后字符串;
    +----------------------------------+-----------------+----------------------------------+
    | 原字符串                         | 字符串长度      | 替换后字符串                     |
    +----------------------------------+-----------------+----------------------------------+
    | 这是MySQL数据库管理系统          |              14 | 这是MySQL数据库管理系统          |
    +----------------------------------+-----------------+----------------------------------+
    1 row in set (0.00 sec)
    
    mysql> SELECT '这是MySQL数据库管理系统' 原字符串,
        -> CHAR_LENGTH('这是MySQL数据库管理系统') 字符串长度,
        -> INSERT('这是MySQL数据库管理系统',13,5,'Oracle') 替换后字符串;
    +----------------------------------+-----------------+----------------------------------+
    | 原字符串                         | 字符串长度      | 替换后字符串                     |
    +----------------------------------+-----------------+----------------------------------+
    | 这是MySQL数据库管理系统          |              14 | 这是MySQL数据库管理Oracle        |
    +----------------------------------+-----------------+----------------------------------+
    1 row in set (0.00 sec)
    

当所有替换的长度大于原来字符串中所剩下的长度 

    mysql> SELECT '这是MySQL数据库管理系统' 原字符串,
        -> CHAR_LENGTH('这是MySQL数据库管理系统') 字符串长度,
        -> INSERT('这是MySQL数据库管理系统',6,20,'Oracle') 替换后字符串;
    +----------------------------------+-----------------+--------------------+
    | 原字符串                         | 字符串长度      | 替换后字符串       |
    +----------------------------------+-----------------+--------------------+
    | 这是MySQL数据库管理系统          |              14 | 这是MySOracle      |
    +----------------------------------+-----------------+--------------------+
    1 row in set (0.00 sec)
    

#### 替换字符串REPLACE 

replcae(str,substr,newstr)

将字符串str中的子字符串substr用字符串newstr替换

    mysql> SELECT '这是MySQL数据库管理系统' 原字符串,
        -> REPLACE('这是MySQL数据库管理系统','mysql','Oracle') 替换后字符串,
        -> REPLACE('这是MySQL数据库管理系统','MySQL','Oracle') 替换后字符串;
    +----------------------------------+----------------------------------+-----------------------------------+
    | 原字符串                         | 替换后字符串                     | 替换后字符串                      |
    +----------------------------------+----------------------------------+-----------------------------------+
    | 这是MySQL数据库管理系统          | 这是MySQL数据库管理系统          | 这是Oracle数据库管理系统          |
    +----------------------------------+----------------------------------+-----------------------------------+
    1 row in set (0.00 sec)
    

大小写必须一样才会替换，不然不会替换

#### 字符串反转 Reverse(str)字符串反转.. 

    mysql> select reverse('abc');
    +----------------+
    | reverse('abc') |
    +----------------+
    | cba            |
    +----------------+
    1 row in set (0.00 sec)

---

## 十七数值函数

* ABS(X) 返回数据X的绝对值
* CEIL(X) 返回大于X的最大整数值
* FLOOR(X) 返回小于X的最大整数
* MODE(X,Y) 返回X模Y的值
* RAND(） 返回0-1之间的随机数
* ROUND(X,Y) 返回数值X的四舍5入后有Y位小数的数值
* TRUNCATE(X,Y)，返回X断为Y位小数的数值

#### 获取随机数RAND() 

    mysql> SELECT RAND(),RAND(),RAND(3),RAND(3);
    +---------------------+---------------------+--------------------+--------------------+
    | RAND()              | RAND()              | RAND(3)            | RAND(3)            |
    +---------------------+---------------------+--------------------+--------------------+
    | 0.14539613215755312 | 0.17698627540561024 | 0.9057697559760601 | 0.9057697559760601 |
    +---------------------+---------------------+--------------------+--------------------+
    1 row in set (0.00 sec)
    

每次运行RAND（）函数返回的都不一样，如果要获取相同的随机数，使用带有相同参数的RNAD()来实现

#### 获取整数 

* CEIL(X) 返回大于X的最大整数值
* FLOOR(X) 返回小于X的最大整数 
```
    mysql> SELECT CEIL(3.5),CEIL(-3.5),FLOOR(3.5),FLOOR(-3.5);
    +-----------+------------+------------+-------------+
    | CEIL(3.5) | CEIL(-3.5) | FLOOR(3.5) | FLOOR(-3.5) |
    +-----------+------------+------------+-------------+
    |         4 |         -3 |          3 |          -4 |
    +-----------+------------+------------+-------------+
    1 row in set (0.00 sec)
```
#### 截取数值函数 

* TRUNCATE(X,Y)，返回X断为Y位小数的数值
```
    mysql> SELECT TRUNCATE(903.53564,2),TRUNCATE(903.53567,-1);
    +-----------------------+------------------------+
    | TRUNCATE(903.53564,2) | TRUNCATE(903.53567,-1) |
    +-----------------------+------------------------+
    |                903.53 |                    900 |
    +-----------------------+------------------------+
    1 row in set (0.00 sec)
```

#### 四舍五入函数 

* ROUND(X,Y) 返回数值X的四舍5入后有Y位小数的数值
```
    mysql> SELECT ROUND(903.53564),ROUND(903.53564,2),ROUND(-903.53564),ROUND(903.53564,-1);
    +------------------+--------------------+-------------------+---------------------+
    | ROUND(903.53564) | ROUND(903.53564,2) | ROUND(-903.53564) | ROUND(903.53564,-1) |
    +------------------+--------------------+-------------------+---------------------+
    |              904 |             903.54 |              -904 |                 900 |
    +------------------+--------------------+-------------------+---------------------+
    1 row in set (0.00 sec)
```

#### BIN（N）,OCT（N），HEX（N_or_S）函数..其实就是进制转换 

    mysql> SELECT BIN(12);
            -> '1100'
    mysql> SELECT OCT(12);
            -> '14'
    mysql> SELECT HEX(255);
            -> 'FF'
    mysql> SELECT HEX("abc");
            -> 616263
    

#### CONV(N,from_base,to_base)函数 

将一个数字N，从from_base转换为to_base，返回值为字符串..

    mysql> SELECT CONV("a",16,2);
            -> '1010'
    mysql> SELECT CONV("6E",18,8);
            -> '172'

---
## 十八日期函数


* CURDATE() 获取当前日期
* CURTIME() 获取当前时间
* NOW() 获取前的日期和时间
* UNIX_TIMESTAMP(DATE) 获取日期DATE的UNIX时间戳
* FROM_UNIXTIME()获取UNIX时间戳的日期值
* WEEK（date） 返日期DATE为一年中的第几周
* YEAR(DATE)返回日期DATE的年份
* HOUR(TIME) 返回时间 为TIME的小时值
* MINUTE(TIME)返回时间为TIME的分钟值
* MONTHNAME(DATE)返回时间TIME的月份值

#### 获取当前的时间和时间 

    mysql> SELECT NOW() now方式,CURRENT_TIMESTAMP() timestamp方式,LOCALTIME() localtime方式,SYSDATE() sysdate方式;
    +---------------------+---------------------+---------------------+---------------------+
    | now方式             | timestamp方式       | localtime方式       | sysdate方式         |
    +---------------------+---------------------+---------------------+---------------------+
    | 2017-02-17 15:11:36 | 2017-02-17 15:11:36 | 2017-02-17 15:11:36 | 2017-02-17 15:11:36 |
    +---------------------+---------------------+---------------------+---------------------+
    1 row in set (0.00 sec)
    

可以获取当前时间，同一种格式显示日期和时间

#### 获取当前日期 

    mysql> select curdate() curdate方式,current_date() current_date方式;
    +---------------+--------------------+
    | curdate方式   | current_date方式   |
    +---------------+--------------------+
    | 2017-02-17    | 2017-02-17         |
    +---------------+--------------------+
    1 row in set (0.00 sec)
    

#### 获取当前时间 

```sql
    mysql> select curtime() curtime方式,current_time current_time方式;
    +---------------+--------------------+
    | curtime方式   | current_time方式   |
    +---------------+--------------------+
    | 15:14:48      | 15:14:48           |
    +---------------+--------------------+
    1 row in set (0.00 sec)
```

#### 通过UNIX方式显示日期和时间 

```sql
    mysql> SELECT NOW() 当前时间,UNIX_TIMESTAMP(NOW()) unix方式,FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())) 普通方式;
    +---------------------+------------+---------------------+
    | 当前时间            | unix方式   | 普通方式            |
    +---------------------+------------+---------------------+
    | 2017-02-17 15:17:53 | 1487315873 | 2017-02-17 15:17:53 |
    +---------------------+------------+---------------------+
    1 row in set (0.00 sec)
```

UNIX_TIMESTAMP(),UNIX_TIMESTAMP(NOW()) 

```sql
    mysql> SELECT NOW(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(NOW());
    +---------------------+------------------+-----------------------+
    | NOW()               | UNIX_TIMESTAMP() | UNIX_TIMESTAMP(NOW()) |
    +---------------------+------------------+-----------------------+
    | 2017-02-17 15:19:17 |       1487315957 |            1487315957 |
    +---------------------+------------------+-----------------------+
    1 row in set (0.00 sec)
```

UNIX_TIMESTAMP(),UNIX_TIMESTAMP(NOW())返回相同的时间戳

#### 通过UTC的方式显示日期和时间 

```sql
    mysql> SELECT NOW(),UTC_DATE(),UTC_TIME();
    +---------------------+------------+------------+
    | NOW()               | UTC_DATE() | UTC_TIME() |
    +---------------------+------------+------------+
    | 2017-02-17 15:20:42 | 2017-02-17 | 07:20:42   |
    +---------------------+------------+------------+
    1 row in set (0.00 sec)
```

#### 获取日期和时间各部分的值 

```sql
    mysql> SELECT NOW() 当前时间,YEAR(NOW()) 年,MONTH(NOW()) 月,DAY(NOW())日,QUARTER(NOW()) 季度,WEEK(NOW()) 星期,DAYOFMONTH(NOW())月中天,HOUR(NOW())时,MINUTE(NOW())分,SECOND(NOW())秒;
    +---------------------+------+------+------+--------+--------+-----------+------+------+------+
    | 当前时间            | 年   | 月   | 日   | 季度   | 星期   | 月中天    | 时   | 分   | 秒   |
    +---------------------+------+------+------+--------+--------+-----------+------+------+------+
    | 2017-02-17 15:24:32 | 2017 |    2 |   17 |      1 |      7 |        17 |   15 |   24 |   32 |
    +---------------------+------+------+------+--------+--------+-----------+------+------+------+
```

#### 月的函数 

```sql
    mysql> select now(), month(now()),monthname(now());
    +---------------------+--------------+------------------+
    | now()               | month(now()) | monthname(now()) |
    +---------------------+--------------+------------------+
    | 2017-02-17 15:26:04 |            2 | February         |
    +---------------------+--------------+------------------+
    1 row in set (0.01 sec)
```

#### 星期 

* WEEK()和WEEKOFYEAR（）返回日期和时间中星期都是当前年的第几个星期1-53
* DAYNAME()返回日期和时间中星期的英文名
* DAYOFWEEK()返回星期几1-7
* WEEKDAY()返回星期几0-6

```sql
    mysql> SELECT NOW()当前时间,WEEK(NOW()),WEEKOFYEAR(NOW()),DAYNAME(NOW()),DAYOFWEEK(NOW()),WEEKDAY(NOW());
    +---------------------+-------------+-------------------+----------------+------------------+----------------+
    | 当前时间            | WEEK(NOW()) | WEEKOFYEAR(NOW()) | DAYNAME(NOW()) | DAYOFWEEK(NOW()) | WEEKDAY(NOW()) |
    +---------------------+-------------+-------------------+----------------+------------------+----------------+
    | 2017-02-17 15:31:06 |           7 |                 7 | Friday         |                6 |              4 |
    +---------------------+-------------+-------------------+----------------+------------------+----------------+
    1 row in set (0.00 sec)
```

#### 天 

* DAYOFMONTH()日期属于当前月的第几天
* DAY()日期属于当前月的第几天
* DAYOFYEAR（） 本年的第几天

```sql
    mysql> SELECT NOW()当前时间,DAY(NOW()),DAYOFMONTH(NOW()),DAYOFYEAR(NOW());
    +---------------------+------------+-------------------+------------------+
    | 当前时间            | DAY(NOW()) | DAYOFMONTH(NOW()) | DAYOFYEAR(NOW()) |
    +---------------------+------------+-------------------+------------------+
    | 2017-02-17 15:32:56 |         17 |                17 |               48 |
    +---------------------+------------+-------------------+------------------+
    1 row in set (0.00 sec)
```

#### 获取指定EXTRACT()函数 

统一获取日期和时间的各部分值

extract(type from date); 

    mysql> SELECT NOW()当前时间,EXTRACT(YEAR FROM NOW()) 年,EXTRACT(MONTH FROM NOW())月,EXTRACT(DAY FROM NOW()) 日,EXTRACT(QUARTER FROM NOW())季,EXTRACT(WEEK FROM NOW())星期,EXTRACT(HOUR FROM NOW())时,EXTRACT(MINUTE FROM NOW())分,EXTRACT(SECOND FROM NOW());
    +---------------------+------+------+------+------+--------+------+------+----------------------------+
    | 当前时间            | 年   | 月   | 日   | 季   | 星期   | 时   | 分   | EXTRACT(SECOND FROM NOW()) |
    +---------------------+------+------+------+------+--------+------+------+----------------------------+
    | 2017-02-17 15:40:12 | 2017 |    2 |   17 |    1 |      7 |   15 |   40 |                         12 |
    +---------------------+------+------+------+------+--------+------+------+----------------------------+
    1 row in set (0.00 sec)
    

#### TO_DAYS(DATE)与FROM_DAYS(NUMBER) 

* TO_DAYS(DATE) 计算日期参数与默认日期0000年1月1日）之间的天数
* FROM_DAYS(NUMBER) 计算从默认昌和时间0000年1月1日）开始经历NUMBER天数后的日期和时间

```sql
    mysql> SELECT NOW() ,TO_DAYS(NOW()),FROM_DAYS(TO_DAYS(NOW()));
    +---------------------+----------------+---------------------------+
    | NOW()               | TO_DAYS(NOW()) | FROM_DAYS(TO_DAYS(NOW())) |
    +---------------------+----------------+---------------------------+
    | 2017-02-17 16:06:39 |         736742 | 2017-02-17                |
    +---------------------+----------------+---------------------------+
    1 row in set (0.00 sec)
```

#### DATEDIFF(date1,date2) 

返回date1与date2之间的天数

```sql
    mysql> SELECT NOW(),DATEDIFF(NOW(),'2015-02-24');
    +---------------------+------------------------------+
    | NOW()               | DATEDIFF(NOW(),'2015-02-24') |
    +---------------------+------------------------------+
    | 2017-02-17 16:07:57 |                          724 |
    +---------------------+------------------------------+
    1 row in set (0.00 sec)
```

#### 指定日期和时间操作 

* ADDDATE(DATE,N),计算日期加上N天后的日期
* SUBDATE(DATE,N),计算日期减去N天后的日期

```sql
    mysql> SELECT NOW(),ADDDATE(CURDATE(),5),SUBDATE(CURDATE(),5);
    +---------------------+----------------------+----------------------+
    | NOW()               | ADDDATE(CURDATE(),5) | SUBDATE(CURDATE(),5) |
    +---------------------+----------------------+----------------------+
    | 2017-02-17 16:15:35 | 2017-02-22           | 2017-02-12           |
    +---------------------+----------------------+----------------------+
    1 row in set (0.00 sec)
```

* ADDDATE(DATE,interval expr type),计算日期加上N天后的日期
* SUBDATE(DATE,interval expr type),计算日期减去N天后的日期

#### TYPE类型
* YEAR 年 YY
* MONTH 月 MM
* DAY 日 DD
* HOUR 时 hh
* MINUTE 分钟 mm
* SECOND 秒 ss
* YEAR_MONTH 年和月 YY与MM之间用任意符号隔开
* DAY_HOUR 日和小时 DD和hh之间用任意符号隔开
* DAY_MINUTE 日和分钟 DD和mm之间用任意符号隔开
* DAY_SECOND 日和秒 DD和ss之间用任意符号隔开
* HOUR_MINUTE 小时和分钟 hh和mm之间用任意符号隔开
* HOUR_SECOND 小时和秒 hh和ss之间用任意符号隔开
* MINUTE_SECOND 分钟和秒 mm和ss之间用任意符号隔开

```sql
    mysql> select adddate(curdate(),interval '1,2' year_month),subdate(curdate(),interval 1 year);
    +----------------------------------------------+------------------------------------+
    | adddate(curdate(),interval '1,2' year_month) | subdate(curdate(),interval 1 year) |
    +----------------------------------------------+------------------------------------+
    | 2018-04-17                                   | 2016-02-17                         |
    +----------------------------------------------+------------------------------------+
    1 row in set (0.00 sec)
    mysql> select adddate(curdate(),interval '1,2' year_month),subdate(curdate(),interval -1 year);
    +----------------------------------------------+-------------------------------------+
    | adddate(curdate(),interval '1,2' year_month) | subdate(curdate(),interval -1 year) |
    +----------------------------------------------+-------------------------------------+
    | 2018-04-17                                   | 2018-02-17                          |
    +----------------------------------------------+-------------------------------------+
    1 row in set (0.00 sec)
```

#### ADDTIME(TIME,N),SUBTIME(TIME,N) 

时间加上N秒，或者时间减去N秒后的时间 

    mysql> SELECT CURTIME(),ADDTIME(CURTIME(),5),SUBTIME(CURTIME(),5);
    +-----------+----------------------+----------------------+
    | CURTIME() | ADDTIME(CURTIME(),5) | SUBTIME(CURTIME(),5) |
    +-----------+----------------------+----------------------+
    | 16:27:29  | 16:27:34             | 16:27:24             |
    +-----------+----------------------+----------------------+
    1 row in set (0.00 sec)

---
## 十九系统信息函数

* version() 返回数据库版本号
* DATABASE() 返回当前数据库名
* USER() 返回当前用户
* LAST_INSERT_ID() 返回最近生成的AUTO_INCREMENT值

```sql
    mysql> select version(),database(),user();
    +------------+------------+----------------+
    | version()  | database() | user()         |
    +------------+------------+----------------+
    | 5.7.17-log | company    | root@localhost |
    +------------+------------+----------------+
    1 row in set (0.00 sec)
```

#### 获取AUTO_INCREMENT约束的最后ID值 

```sql
    CREATE TABLE IF NOT EXISTS t_autoincrement(
    id INT(11) PRIMARY KEY AUTO_INCREMENT 
    );
    
    INSERT INTO t_autoincrement VALUES(NULL);
    INSERT INTO t_autoincrement VALUES(NULL);
    INSERT INTO t_autoincrement VALUES(NULL);
    INSERT INTO t_autoincrement VALUES(NULL);
    INSERT INTO t_autoincrement VALUES(NULL);
    INSERT INTO t_autoincrement VALUES(NULL);
```

```sql
    SELECT LAST_INSERT_ID();
    mysql> select last_insert_id();
    +------------------+
    | last_insert_id() |
    +------------------+
    |               6 |
    +------------------+
    1 row in set (0.00 sec)\
```


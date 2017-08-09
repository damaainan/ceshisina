# [MySQL 分区表以及操作][0]

 标签： [mysql][1][分区表][2][数据][3][分库分表][4]

 2017-07-24 15:23  84人阅读  


版权声明：本文为orangleliu (http://blog.csdn.net/orangleliu/)原创文章，自由传播，文章转载请声明, 多谢。

 目录

1. [分区表][10]
1. [RANGE分区][11]
    1. [List分区][12]
    1. [Hash分区][13]
    1. [Key分区][14]

1. [在线改动和测试][15]

> 对现有表进行分表, 对过期数据进行归档等操作。

分表的一般 **参考**（只是参考)： 表体积大于`2g`，简单查询表数据超过`1000w行`，复杂查询表超过`200w行`。   
两种分表的思路

* 横行分表 比较常见的是按时间切分
* 纵向分表 对于字段的冷热程度区分很明显的情况

注意的点

* 分表之前要搞清楚数据库引擎，数据现有的量，多大磁盘空间，多少行
* 分表之前 需要备份数据
* 分区之后，索引和数据都会分区，无法进行单独的设置
* 适合有历史归档的情况， 热点数据都集中在最后的行中
* 分区表无法使用外键约束
* 每个存储引擎会有一些具体的实现，可能不同

请谨慎使用

## 分区表

分区表是[数据库][16]层面的实现，应用层基本不用关心。一些限制

* 5.6.7版本之前 最多有`1024`个分区，之后的版本可以使用 `8192`个分区，最新版本的mysql会有很多优化
* 不能使用外键约束
* 主表的所有唯一索引（包括主键）都必须包含分区字段。[文档解释][17]
* 插入频繁的数据，使用范围为分区条件的不要设置太多分区（100以内)，查询分区也有消耗

查看是否支持分区表

```sql
    SHOW VARIABLES LIKE "%partition%";
```

还有一个，如果您的表之前使用自增id，直接alter成分区表，可能需要把分区字段加到主键中

```sql
    ALTER TABLE  `auth_user` DROP PRIMARY KEY , ADD PRIMARY KEY ( `id`, `date_joined`);
```

下面摘抄网络， 以 5.6 版本为基准吧，5.7分区表还没看，看到说有变化。

## RANGE分区

根据范围分区，范围应该连续但是不重叠，使用PARTITION BY RANGE, VALUES LESS THAN关键字。不使用COLUMNS关键字时RANGE括号内必须为整数字段名或返回确定整数的函数。

根据数值范围：

```sql
    CREATE TABLE employees (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT NOT NULL,
        store_id INT NOT NULL
    )
    PARTITION BY RANGE (store_id) (
        PARTITION p0 VALUES LESS THAN (6),
        PARTITION p1 VALUES LESS THAN (11),
        PARTITION p2 VALUES LESS THAN (16),
        PARTITION p3 VALUES LESS THAN MAXVALUE
    );
```

根据TIMESTAMP范围：

```sql
    CREATE TABLE quarterly_report_status (
        report_id INT NOT NULL,
        report_status VARCHAR(20) NOT NULL,
        report_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
    PARTITION BY RANGE ( UNIX_TIMESTAMP(report_updated) ) (
        PARTITION p0 VALUES LESS THAN ( UNIX_TIMESTAMP('2008-01-01 00:00:00') ),
        PARTITION p1 VALUES LESS THAN ( UNIX_TIMESTAMP('2008-04-01 00:00:00') ),
        PARTITION p2 VALUES LESS THAN ( UNIX_TIMESTAMP('2008-07-01 00:00:00') ),
        PARTITION p3 VALUES LESS THAN ( UNIX_TIMESTAMP('2008-10-01 00:00:00') ),
        PARTITION p4 VALUES LESS THAN ( UNIX_TIMESTAMP('2009-01-01 00:00:00') ),
        PARTITION p5 VALUES LESS THAN ( UNIX_TIMESTAMP('2009-04-01 00:00:00') ),
        PARTITION p6 VALUES LESS THAN ( UNIX_TIMESTAMP('2009-07-01 00:00:00') ),
        PARTITION p7 VALUES LESS THAN ( UNIX_TIMESTAMP('2009-10-01 00:00:00') ),
        PARTITION p8 VALUES LESS THAN ( UNIX_TIMESTAMP('2010-01-01 00:00:00') ),
        PARTITION p9 VALUES LESS THAN (MAXVALUE)
    );
```

**这里还需要有 分区的删除，增加等操作**

添加COLUMNS关键字可定义非integer范围及多列范围，不过需要注意COLUMNS括号内只能是列名，不支持函数；多列范围时，多列范围必须呈递增趋势：

根据DATE、DATETIME范围：

```sql
    CREATE TABLE members (
        firstname VARCHAR(25) NOT NULL,
        lastname VARCHAR(25) NOT NULL,
        username VARCHAR(16) NOT NULL,
        email VARCHAR(35),
        joined DATE NOT NULL
    )
    PARTITION BY RANGE COLUMNS(joined) (
        PARTITION p0 VALUES LESS THAN ('1960-01-01'),
        PARTITION p1 VALUES LESS THAN ('1970-01-01'),
        PARTITION p2 VALUES LESS THAN ('1980-01-01'),
        PARTITION p3 VALUES LESS THAN ('1990-01-01'),
        PARTITION p4 VALUES LESS THAN MAXVALUE
    );
```

根据多列范围：

```sql
    CREATE TABLE rc3 (
        a INT,
        b INT
    )
    PARTITION BY RANGE COLUMNS(a,b) (
        PARTITION p0 VALUES LESS THAN (0,10),
        PARTITION p1 VALUES LESS THAN (10,20),
        PARTITION p2 VALUES LESS THAN (10,30),
        PARTITION p3 VALUES LESS THAN (10,35),
        PARTITION p4 VALUES LESS THAN (20,40),
        PARTITION p5 VALUES LESS THAN (MAXVALUE,MAXVALUE)
     );
```

### List分区

根据具体数值分区，每个分区数值不重叠，使用PARTITION BY LIST、VALUES IN关键字。跟Range分区类似，不使用COLUMNS关键字时List括号内必须为整数字段名或返回确定整数的函数。

```sql
    CREATE TABLE employees (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT,
        store_id INT
    )
    PARTITION BY LIST(store_id) (
        PARTITION pNorth VALUES IN (3,5,6,9,17),
        PARTITION pEast VALUES IN (1,2,10,11,19,20),
        PARTITION pWest VALUES IN (4,12,13,14,18),
        PARTITION pCentral VALUES IN (7,8,15,16)
    );
```

数值必须被所有分区覆盖，否则插入一个不属于任何一个分区的数值会报错。

```sql
    mysql> CREATE TABLE h2 (
        ->   c1 INT,
        ->   c2 INT
        -> )
        -> PARTITION BY LIST(c1) (
        ->   PARTITION p0 VALUES IN (1, 4, 7),
        ->   PARTITION p1 VALUES IN (2, 5, 8)
        -> );
    Query OK, 0 rows affected (0.11 sec)
    
    mysql> INSERT INTO h2 VALUES (3, 5);
    ERROR 1525 (HY000): Table has no partition for value 3
```

当插入多条数据出错时，如果表的引擎支持事务（Innodb），则不会插入任何数据；如果不支持事务，则出错前的数据会插入，后面的不会执行。   
可以使用IGNORE关键字忽略出错的数据，这样其他符合条件的数据会全部插入不受影响。

```sql
    mysql> TRUNCATE h2;
    Query OK, 1 row affected (0.00 sec)
    
    mysql> SELECT * FROM h2;
    Empty set (0.00 sec)
    
    mysql> INSERT IGNORE INTO h2 VALUES (2, 5), (6, 10), (7, 5), (3, 1), (1, 9);
    Query OK, 3 rows affected (0.00 sec)
    Records: 5  Duplicates: 2  Warnings: 0
    
    mysql> SELECT * FROM h2;
    +------+------+
    | c1   | c2   |
    +------+------+
    |    7 |    5 |
    |    1 |    9 |
    |    2 |    5 |
    +------+------+
    3 rows in set (0.00 sec)
```

与Range分区相同，添加COLUMNS关键字可支持非整数和多列。

### Hash分区

Hash分区主要用来确保数据在预先确定数目的分区中平均分布，Hash括号内只能是整数列或返回确定整数的函数，实际上就是使用返回的整数对分区数取模。

```sql
    CREATE TABLE employees (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT,
        store_id INT
    )
    PARTITION BY HASH(store_id)
    PARTITIONS 4;
    CREATE TABLE employees (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT,
        store_id INT
    )
    PARTITION BY HASH( YEAR(hired) )
    PARTITIONS 4;
```

Hash分区也存在与传统Hash分表一样的问题，可扩展性差。[MySQL][16]也提供了一个类似于一致Hash的分区方法－线性Hash分区，只需要在定义分区时添加LINEAR关键字，如果对实现原理感兴趣，可以查看官方文档。

```sql
    CREATE TABLE employees (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT,
        store_id INT
    )
    PARTITION BY LINEAR HASH( YEAR(hired) )
    PARTITIONS 4;
```

### Key分区

按照KEY进行分区类似于按照HASH分区，除了HASH分区使用的用户定义的表达式，而KEY分区的 哈希函数是由[mysql][16] 服务器提供。   
MySQL 簇（Cluster）使用函数MD5()来实现KEY分区；对于使用其他存储引擎的表，服务器使用其自己内部的 哈希函数，这些函数是基于与PASSWORD()一样的运[算法][18]则。   
Key分区与Hash分区很相似，只是Hash函数不同，定义时把Hash关键字替换成Key即可，同样Key分区也有对应与线性Hash的线性Key分区方法。

```sql
    CREATE TABLE tk (
        col1 INT NOT NULL,
        col2 CHAR(5),
        col3 DATE
    )
    PARTITION BY LINEAR KEY (col1)
    PARTITIONS 3;
```

另外，当表存在主键或唯一索引时可省略Key括号内的列名，Mysql将按照主键－唯一索引的顺序选择，当找不到唯一索引时报错

[分区表使用][19]

## 在线改动和测试

如果有downtime，采用临时表的方式，新建一个 和原表一样的分区表，然后进行插入工作，最后进行修改名称。 如果有主从，感觉还是影响很大呀。

加一个小例子把，对已经存在表添加分区

```sql
    mysql> create table orders (id int, st int, whatever varchar(10), primary key (id));
    Query OK, 0 rows affected (0.06 sec)
    
    mysql> ALTER TABLE orders DROP PRIMARY KEY, ADD PRIMARY KEY(id, st);
    Query OK, 0 rows affected (0.06 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    mysql> ALTER TABLE orders PARTITION BY LIST(st) (
        ->           PARTITION p0 VALUES IN (20,10),
        ->           PARTITION p1 VALUES IN (0,-10)
        -> );
    Query OK, 0 rows affected (0.06 sec)
    Records: 0  Duplicates: 0  Warnings: 0
```

> 文章地址 [http://blog.csdn.net/orangleliu/article/details/57088338][20]  
[MySQL对数据表已有表进行分区表][20]

[0]: http://blog.csdn.net/orangleliu/article/details/76021074
[1]: http://www.csdn.net/tag/mysql
[2]: http://www.csdn.net/tag/%e5%88%86%e5%8c%ba%e8%a1%a8
[3]: http://www.csdn.net/tag/%e6%95%b0%e6%8d%ae
[4]: http://www.csdn.net/tag/%e5%88%86%e5%ba%93%e5%88%86%e8%a1%a8
[9]: #
[10]: #t0
[11]: #t1
[12]: #t2
[13]: #t3
[14]: #t4
[15]: #t5
[16]: http://lib.csdn.net/base/mysql
[17]: http://dev.mysql.com/doc/refman/5.7/en/partitioning-limitations-partitioning-keys-unique-keys.html
[18]: http://lib.csdn.net/base/datastructure
[19]: http://haitian299.github.io/2016/05/26/mysql-partitioning/
[20]: http://blog.csdn.net/orangleliu/article/details/57088338
# [MySQL RANGE分区][0]

### 介绍 

RANGE分区基于一个给定的连续区间范围，早期版本RANGE主要是基于整数的分区。在5.7版本中DATE、DATETIME列也可以使用RANGE分区，同时在5.5以上的版本提供了基于非整形的RANGE COLUMN分区。RANGE分区必须的连续的且不能重叠。使用

“VALUES LESS THAN ()” 来定义分区区间,非整形的范围值需要使用 单引号 ，并且可以使用MAXVALUE作为分区的最高值。

**一、RANGE分区**

**1.创建分区**

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
        PARTITION p3 VALUES LESS THAN (21)
    );  
    alter table employees add index ix_store_id(store_id) ;  
    alter table employees add index ix_job_code(job_code) ;
```


p0:指分区p0,这个分区名的取值可以随便取值只要同一个表里面的每个分区名不重复即可，也不需要非得从0开始，你也可以使用a、b、c、d。

THEN():分区的范围值，这个值只能的连续不重叠的从小到大的值。

**2.性能分析**

插入测试数据

    insert into employees(id,job_code,store_id) values(1,1001,1),(2,1002,2),(3,1003,3),(4,1004,4);

![][1]

从执行计划可以看到两个查询都用到了分区的效果；如果细心估计会发现第二个查询没有走索引，并不是使用小于就不会走索引而且执行计划分析评估任务不走索引的效果会更好，事实却是如果当前查询整个分区的数据时使用索引的话还需要去查询其它的字段还不如直接扫描整个分区来的快。

**3.增加分区**

由于当前分区值的范围是小于21，当向分区表中插入一个超过分区范围的值时会报错。这个时候可以增加一个分区，当你不确定需要给一个多大的上限值时可以使用MAXVALUE

![][2]

    alter table employees add PARTITION  (PARTITION p4 VALUES LESS THAN MAXVALUE);

注意：增加分区只能在最大端增加

**4.删除分区**

    alter table employees drop  PARTITION p4;

注意：通过这种删除分区的方式会将分区中的数据也删除，慎用！！！！。但是通过删除分区的方式删除数据会比delete快很多，因为它相当于删除一个数据库一样因为每个分区都是一个独立的数据文件。用来删除历史分区数据是非常好的办法。

**5.拆分合并分区**

拆分合并分区统称为重新定义分区，拆分分为不会造成数据的丢失，只将会将数据从一个分区移动到另一个分区。

例1：将P0拆分成s1,s2两个分区

```sql
    ALTER TABLE employees REORGANIZE PARTITION p0 INTO (
        PARTITION s0 VALUES LESS THAN (3),
        PARTITION s1 VALUES LESS THAN (6)
    );
```

注意：原来分区p0的范围是[负无穷-6)，所以新拆分的分区也必须是这范围，所以新的分区范围值最大不能超过6。

![][3]

分区由原来的p0[-6)变成了so[-3),s1[3-6)，整个分区的范围还是不变。

例2：将s1,p1,p2合并为a,b两个分区

```sql
    ALTER TABLE employees REORGANIZE PARTITION s1,p1,p2 INTO (
        PARTITION a VALUES LESS THAN (5),
        PARTITION b VALUES LESS THAN (16)
    );
```

原本的s1,p1,p2分区范围是：[3-16)所以新的分区也必须和原本的分区相同，所以新的分区的值不能低于3不能高于16即可。

![][4]

分区由原来的s1[3-6),p1[6-11),p2[11-16)变成了现在的a[3-5),b[5-16),总的范围没有发生变化

注意：无论是拆分还是合并分区都不能改变分区原本的覆盖范围，并且合并分区只能合并连续的分区不能跳过分区合并；并且不能改变分区的类型，例如不能把range分区改成key分区等。

**二、日期字段分区方法**

注意：RANG分区针对日期字段进行分区可以使用时间类型的函数进行转换成整形，但是如果你的查询语句需要利用分区那么查询语句也需要使用相同的时间函数进行查询。

1.使用YEAR()函数进行分区

```sql
    CREATE TABLE employees1 (
        id INT NOT NULL,
        fname VARCHAR(30),
        lname VARCHAR(30),
        hired DATE NOT NULL DEFAULT '1970-01-01',
        separated DATE NOT NULL DEFAULT '9999-12-31',
        job_code INT,
        store_id INT
    )
    PARTITION BY RANGE ( YEAR(separated) ) (
        PARTITION p0 VALUES LESS THAN (1991),
        PARTITION p1 VALUES LESS THAN (1996),
        PARTITION p2 VALUES LESS THAN (2001),
        PARTITION p3 VALUES LESS THAN MAXVALUE
    ); 
```

插入测试数据

    insert into employees1(id,separated,job_code,store_id) values(1,'1990-03-04',1001,1),(2,'1995-03-04',1002,2),(3,'1998-03-04',1003,3),(4,'2016-03-04',1004,4);

![][5]

对于日期字段分区，查询条件使用> 、< 、betnwen、=都会利用分区查询,如果条件使用函数转换则不会走分区，比如使用YEAR()。

**2.TIMESTAMP类型的列的分区方法**

针对TIMESTAMP的日期类型的字段需要使用专门的UNIX_TIMESTAMP（）函数进行转换

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

**三、null值处理**

当往分区列中插入null值RANG 分区会将其当作最小值来处理即插入最小的分区中

```sql
    CREATE TABLE test (
        id INT NOT NULL,
        store_id INT 
    )
    PARTITION BY RANGE (store_id) (
        PARTITION p0 VALUES LESS THAN (6),
        PARTITION p1 VALUES LESS THAN (11),
        PARTITION p2 VALUES LESS THAN (16),
        PARTITION p3 VALUES LESS THAN (21)
    );
    insert into test(id,store_id) values(1,null);
```

```sql
    SELECT PARTITION_NAME,PARTITION_METHOD,PARTITION_EXPRESSION,PARTITION_DESCRIPTION,TABLE_ROWS,SUBPARTITION_NAME,SUBPARTITION_METHOD,SUBPARTITION_EXPRESSION 
    FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA=SCHEMA() AND TABLE_NAME='test';
```

![][6]

备注：文章中的示例摘自mysql官方参考手册

**四、移除表的分区**

    ALTER TABLE tablename
    REMOVE PARTITIONING ;

注意：使用remove移除分区是仅仅移除分区的定义，并不会删除数据和drop PARTITION不一样，后者会连同数据一起删除


### **总结** 

有两点非常重要需要注意，第一删除分区时要慎重因为会连同分区里的数据一并删除，拆分合并分区新的分区一定要和原来的分区的范围一致。RANGE COLUMN分区单独用章节进行讲解，。

[0]: http://www.cnblogs.com/chenmh/p/5627912.html
[1]: ./img/135426-20160629181117093-1533866780.png
[2]: ./img/135426-20160629182119343-897262041.png
[3]: ./img/135426-20160630162741952-428451023.png
[4]: ./img/135426-20160630164749827-590391064.png
[5]: ./img/135426-20160630154843546-962750964.png
[6]: ./img/135426-20160701141114437-688852616.png

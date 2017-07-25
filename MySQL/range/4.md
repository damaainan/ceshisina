# [MySQL LIST分区][0]

### 介绍 

LIST分区和RANGE分区非常的相似，主要区别在于LIST是枚举值列表的集合，RANGE是连续的区间值的集合。二者在语法方面非常的相似。同样建议LIST分区列是非null列，否则插入null值如果枚举列表里面不存在null值会插入失败，这点和其它的分区不一样，RANGE分区会将其作为最小分区值存储，HASH\KEY分为会将其转换成0存储，主要LIST分区只支持整形，非整形字段需要通过函数转换成整形；5.5版本之后可以不需要函数转换使用LIST COLUMN分区支持非整形字段，在COLUMN分区中有详细的讲解。

**一、创建分区**

List各个分区枚举的值只需要不相同即可，没有固定的顺序。

```sql
    CREATE TABLE tblist (
        id INT NOT NULL,
        store_id INT
    )
    PARTITION BY LIST(store_id) (
        PARTITION a VALUES IN (1,5,6),
        PARTITION b VALUES IN (2,7,8),
        PARTITION c VALUES IN (3,9,10),
        PARTITION d VALUES IN (4,11,12)
    );
```

```sql
    SELECT PARTITION_NAME,PARTITION_METHOD,PARTITION_EXPRESSION,PARTITION_DESCRIPTION,TABLE_ROWS,SUBPARTITION_NAME,SUBPARTITION_METHOD,SUBPARTITION_EXPRESSION 
    FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA=SCHEMA() AND TABLE_NAME='tblist';
```

![][1]

**1.插入数据**

    insert into tblist(id,store_id) values(1,1),(7,7); 

往a、b两个分区中各插入一条记录

![][2]

**2.插入不在列表中的值**

![][3]

当往分区中插入不在枚举列表中的值是会插入失败，插入null值如果null值不在枚举列表中也同样失败

**二、分区管理**

**1.增加分区**

    ALTER TABLE tblist ADD PARTITION (PARTITION e VALUES IN (20));

注意：不能增加包含现有任意值的分区。

**2.合并分区**

    ALTER TABLE tblist REORGANIZE PARTITION  a,b INTO (PARTITION m VALUES IN (1,5,6,2,7,8));

将分区a,b合并为分区m

注意：同RANGE分区一样，只能合并相邻的几个分区，不能跨分区合并。例如不能合并a,c两个分区，只能通过合并a,b,c

![][4]

**3.拆分分区**

```sql
    ALTER TABLE tblist REORGANIZE PARTITION  a,b,c INTO 
    (PARTITION n VALUES IN (1,5,6,3,9,10),
    PARTITION m VALUES IN (2,7,8));
    
    ALTER TABLE tblist REORGANIZE PARTITION  n INTO 
        ( PARTITION a VALUES IN (1,5,6),
        PARTITION b VALUES IN (3,9,10));
```

![][5]

经过两轮的拆分，枚举列表（3,9,10）排到了（2,7,8）的前面去了；其实是这样的，一开始合并abc成nm两个分区由于n中的枚举值小于m所以n在m的前面，后面再拆分n分区由于n分区在m分区的前面所以拆分出来的分区也是排在m分区的前面，由于a分区的值小于b分区的值所以a排在b的前面。

注意：1.在5.7.12版本中测试发现，合并和拆分分区重新定义的枚举值可以不是原来的值，如果原来的枚举值包含了数据而新合并或拆分的分区枚举值又不不包含原来的枚举值会造成数据丢失。虽然不知道为什么mysql不会禁止该行为，但是人为的要求无论是合并还是拆分分区枚举值保持不变，或者只能增加不能减少，这样能保证数据不丢失。

2.合并和拆分后的分区由于是相邻的分区进行合并和拆分会根据原本的分区的值新的分区也会在原本的分区的顺序位置。

**4.删除分区**

    ALTER TABLE tblist DROP PARTITION e;

注意：删除分区同时会将分区中的数据删除，同时枚举的list值也被删除，后面无法往表中插入该值的数据。

**三、其它分区**

**1.对时间字段进行分区** 

```sql
    CREATE TABLE listdate (
        id INT NOT NULL,
        hired DATETIME NOT NULL
    )
    PARTITION BY LIST( YEAR(hired) ) 
    (
        PARTITION a VALUES IN (1990),
        PARTITION b VALUES IN (1991),
        PARTITION c VALUES IN (1992),
        PARTITION d VALUES IN (1993)
    );
    
    ALTER TABLE listdate ADD INDEX ix_hired(hired);
    
    INSERT INTO listdate() VALUES(1,'1990-01-01 10:00:00'),(1,'1991-01-01 10:00:00'),(1,'1992-01-01 10:00:00');
```

    EXPLAIN SELECT * FROM listdate WHERE hired='1990-01-01 10:00:00';

![][6]

LIST分区也支持对非整形的时间类型字段的转换分区。

**四、移除表的分区**

    ALTER TABLE tablename
    REMOVE PARTITIONING ;

注意：使用remove移除分区是仅仅移除分区的定义，并不会删除数据和drop PARTITION不一样，后者会连同数据一起删除

**参考：**


### **总结** 

重新定义LIST分区时只能重新定义相邻的分区，不能跳过分区定义，重新定义的分区列表枚举必须包含原分区的列表枚举，如果丢失某个包含记录的枚举值那么数据也将被删除；重新定义分区不能改变分区的类型。

[0]: http://www.cnblogs.com/chenmh/p/5643174.html
[1]: ./img/135426-20160705115219514-479514484.png
[2]: ./img/135426-20160705115719608-152749057.png
[3]: ./img/135426-20160705115853686-711472333.png
[4]: ./img/135426-20160705143328967-1566927676.png
[5]: ./img/135426-20160705145246483-1938635423.png
[6]: ./img/135426-20160705161917358-831505307.png

# 如何理解并正确使用MySQL索引

 时间 2017-10-21 10:25:27  21CTO

原文[http://www.21cto.com/article/1564][1]


## 1. 概述

索引是存储引擎用于快速查找记录的一种数据结构，通过合理的使用数据库索引可以大大提高系统的访问性能，接下来主要介绍在 MySQL 数据库中索引类型，以及如何创建出更加合理且高效的索引技巧。

注：这里主要针对的是 InnoDB 存储引擎的 B+Tree 索引数据结构

## 2. 索引的优点

大大减轻了服务器需要扫描的数据量，从而提高了数据的检索速度

帮助服务器避免排序和临时表

可以将随机 I/O 变为顺序 I/O

## 3. 索引的创建  

### 3.1 主键索引    
`ALTER TABLE 'table_name' ADD PRIMARY KEY 'index_name' ('column');`

### 3.2 唯一索引

    ALTER TABLE 'table_name' ADD UNIQUE 'index_name' ('column');

###  3.3 普通索引    
`ALTER TABLE 'table_name' ADD INDEX 'index_name' ('column');`

###  3.4 全文索引    
`ALTER TABLE 'table_name' ADD FULLTEXT 'index_name' ('column');`

###  3.5 组合索引    
`ALTER TABLE 'table_name' ADD INDEX 'index_name' ('column1', 'column2', ...);`

## 4. B+Tree的索引规则

创建一个测试的用户表

    DROP TABLE IFEXIST Suser_test;
    
    CREATE TABLE user_test(id int AUTO_INCREMENT PRIMARY KEY, user_namev archar(30) NOTNULL,sex  bit(1) NOTNULL DEFAULTb'1',city varchar(50) NOTNULL, ageintNOTNULL) ENGINE=InnoDBDEFAULTCHARSET=utf8;

创建一个组合索引： 

    ALTER TABLE user_test ADD INDEX idx_user(user_name , city , age);
    

### 4.1 索引有效的查询

4.1.1 `全值匹配`

全值匹配指的是和索引中的所有列进行匹配，如：以上面创建的索引为例，在where条件后可同时查询（user_name，city，age）为条件的数据。

注：与where后查询条件的顺序无关，这里是很多同学容易误解的一个地方

    SELECT * FROM user_test WHERE user_name = 'feinik' AND age = 26 AND city ='广州';
    

4.1.2 `匹配最左前缀`

匹配最左前缀是指优先匹配最左索引列，如：上面创建的索引可用于查询条件为：（user_name ）、（user_name, city）、（user_name , city , age）

注：满足最左前缀查询条件的顺序与索引列的顺序无关，如：（city, user_name）、（age, city, user_name）

4.1.3 `匹配列前缀`

指匹配列值的开头部分，如：查询用户名以feinik开头的所有用户

    SELECT * FROM user_test WHERE user_name LIKE 'feinik%';
    

4.1.4 `匹配范围值`

如：查询用户名以feinik开头的所有用户，这里使用了索引的第一列

    SELECT * FROM user_test WHERE user_name LIKE 'feinik%';
    

### 4.2 索引的限制

1、where查询条件中不包含索引列中的最左索引列，则无法使用到索引查询，如：

    SELECT * FROM user_test WHERE city = '广州';
    

或

    SELECT* FROM user_test WHERE age= 26;
    

或

    SELECT * FROM user_test WHERE city ='广州' AND age = '26';
    

2、即使where的查询条件是最左索引列，也无法使用索引查询用户名以feinik结尾的用户

    SELECT * FROM user_test WHERE user_name like '%feinik';

3、如果where查询条件中有某个列的范围查询，则其右边的所有列都无法使用索引优化查询，如：

    SELECT * FROM user_test WHERE user_name ='feinik' AND city LIKE '广州%' AND age = 26;

## 5. 高效的索引策略

### 5.1 索引

列不能是表达式的一部分，也不能作为函数的参数，否则无法使用索引查询。

    SELECT * FROM user_test WHERE user_name = concat(user_name, 'fei');

### 5.2 前缀索引

有时候需要索引很长的字符列，这会增加索引的存储空间以及降低索引的效率，一种策略是可以使用哈希索引，还有一种就是可以使用前缀索引，前缀索引是选择字符列的前n个字符作为索引，这样可以大大节约索引空间，从而提高索引效率。

5.2.1 前缀索引的选择性

前缀索引要选择足够长的前缀以保证高的选择性，同时又不能太长，我们可以通过以下方式来计算出合适的前缀索引的选择长度值：

（1）

    SELECT COUNT(DISTINCT index_column)/COUNT(*)  FROM table_name;

--index_column代表要添加前缀索引的列

注：通过以上方式来计算出前缀索引的选择性比值，比值越高说明索引的效率也就越高效。

（2）

    SELECT COUNT(DISTINCTLEFT(index_column,1))/COUNT(*),COUNT(DISTINCTLEFT(index_column,2))/COUNT(*),COUNT(DISTINCTLEFT(index_column,3))/COUNT(*)... FROM table_name;

注：通过以上语句逐步找到最接近于（1）中的前缀索引的选择性比值，那么就可以使用对应的字符截取长度来做前缀索引了

5.2.2 前缀索引的创建

    ALTER TABLE table_name ADD INDEX index_name (index_column(length));
    

5.2.3 使用前缀索引的注意点

前缀索引是一种能使索引更小，更快的有效办法，但是MySql无法使用前缀索引做ORDER BY 和 GROUP BY以及使用前缀索引做覆盖扫描。

### 5.3 选择合适的索引列顺序

在组合索引的创建中索引列的顺序非常重要，正确的索引顺序依赖于使用该索引的查询方式.

对于组合索引的索引顺序可以通过经验法则来帮助我们完成：将选择性最高的列放到索引最前列，该法则与前缀索引的选择性方法一致，但并不是说所有的组合索引的顺序都使用该法则就能确定，还需要根据具体的查询场景来确定具体的索引顺序。

### 5.4 聚集索引与非聚集索引

1. **`聚集索引`**

聚集索引决定数据在物理磁盘上的物理排序，一个表只能有一个聚集索引，如果定义了主键，那么 InnoDB 会通过主键来聚集数据，如果没有定义主键，InnoDB 会选择一个唯一的非空索引代替，如果没有唯一的非空索引，InnoDB 会隐式定义一个主键来作为聚集索引。

聚集索引可以很大程度的提高访问速度，因为聚集索引将索引和行数据保存在了同一个 B-Tree 中，所以找到了索引也就相应的找到了对应的行数据，但在使用聚集索引的时候需注意避免随机的聚集索引（一般指主键值不连续，且分布范围不均匀）。

如使用 UUID 来作为聚集索引性能会很差，因为 UUID 值的不连续会导致增加很多的索引碎片和随机I/O，最终导致查询的性能急剧下降。

2. **`非聚集索引`**

与聚集索引不同的是非聚集索引并不决定数据在磁盘上的物理排序，且在 B-Tree 中包含索引但不包含行数据，行数据只是通过保存在 B-Tree 中的索引对应的指针来指向行数据，如：上面在（user_name，city, age）上建立的索引就是非聚集索引。

### 5.5 覆盖索引

如果一个索引（如：组合索引）中包含所有要查询的字段的值，那么就称之为覆盖索引，如：

    SELECT user_name, city, age FROM user_test WHERE user_name ='feinik' AND age >25;

因为要查询的字段（user_name, city, age）都包含在组合索引的索引列中，所以就使用了覆盖索引查询，查看是否使用了覆盖索引可以通过执行计划中的Extra中的值为Using index则证明使用了覆盖索引，覆盖索引可以极大的提高访问性能。

### 5.6 如何使用索引来排序

在排序操作中如果能使用到索引来排序，那么可以极大的提高排序的速度，要使用索引来排序需要满足以下两点即可。

ORDER BY 子句后的列顺序要与组合索引的列顺序一致，且所有排序列的排序方向（正序/倒序）需一致；

1.所查询的字段值需要包含在索引列中，及满足覆盖索引。

2.通过例子来具体分析

在 user_test 表上创建一个组合索引

    ALTER TABLE user_test ADD INDEX index_user(user_name , city , age);

可以使用到索引排序的案例

    1、SELECT user_name, city, age FROM user_test ORDER BY user_name;

    2、SELECT user_name, city, age FROM user_test ORDER BY user_name, city;

    3、SELECT user_name, city, age FROM user_test ORDER BY user_name DESC, city DESC;

    4、SELECT user_name, city, age FROM user_test WHERE user_name = 'feinik'ORDER BY city;

注：第4点比较特殊一点，如果where查询条件为索引列的第一列，且为常量条件，那么也可以使用到索引

###  无法使用索引排序的案例

1. sex不在索引列中

```
    SELECT user_name, city, age FROM user_test ORDER BY user_name, sex;
```

2. 排序列的方向不一致
```
SELECT user_name, city, age FROM user_test ORDER BY user_name ASC, city DESC;
```
3. 所要查询的字段列sex没有包含在索引列中
```
SELECT user_name, city, age, sex FROM user_test ORDER BY user_name;
```
4. where查询条件后的user_name为范围查询，所以无法使用到索引的其他列
```
    SELECT user_name, city, age FROM user_test WHERE user_name LIKE 'feinik%' ORDER BY city;
```
5. 多表连接查询时，只有当ORDER BY后的排序字段都是第一个表中的索引列（需要满足以上索引排序的两个规则）时，方可使用索引排序。

如：再创建一个用户的扩展表user_test_ext，并建立uid的索引。

    DROP TABLE IFEXISTS user_test_ext;

    CREATE TABLE user_test_ext( id int AUTO_INCREMENT PRIMARY KEY, uid int NOTNULL, u_password VARCHAR(64) NOTNULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE user_test_ext ADD INDEX index_user_ext(uid);

走索引排序

    SELECT user_name, city, age FROM user_test u LEFT JOIN user_test_ext ue ON u.id= ue.uid ORDER BY u.user_name;

不走索引排序

    SELECT user_name, city, age FROM user_test u LEFT JOIN user_test_ext ue ON u.id= ue.uid ORDER BY ue.uid;
    

## 6. 总结

本文主要讲了B+Tree树结构的索引规则，不同索引的创建，以及如何正确的创建出高效的索引技巧来尽可能的提高查询速度，当然了关于索引的使用技巧不单单只有这些，关于索引的更多技巧还需平时不断的积累相关经验。

作者：FEINIK


[1]: http://www.21cto.com/article/1564

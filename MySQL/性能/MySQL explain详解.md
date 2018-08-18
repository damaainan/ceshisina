## MySQL explain详解

来源：[http://www.cnblogs.com/fswhq/p/explain.html](http://www.cnblogs.com/fswhq/p/explain.html)

时间 2018-08-17 22:48:00



## Explain简介

本文主要讲述如何通过 explain 命令获取 select 语句的执行计划，通过 explain 我们可以知道以下信息：表的读取顺序，数据读取操作的类型，哪些索引可以使用，哪些索引实际使用了，表之间的引用，每张表有多少行被优化器查询等信息。

下面是使用 explain 的例子：

在 select 语句之前增加 explain 关键字，MySQL 会在查询上设置一个标记，执行查询时，会返回执行计划的信息，而不是执行这条SQL（如果 from 中包含子查询，仍会执行该子查询，将结果放入临时表中）。

```sql


mysql> explain select * from actor;
+----+-------------+-------+------+---------------+------+---------+------+------+-------+
| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra |
+----+-------------+-------+------+---------------+------+---------+------+------+-------+
|  1 | SIMPLE      | actor | ALL  | NULL          | NULL | NULL    | NULL |    2 | NULL  |
+----+-------------+-------+------+---------------+------+---------+------+------+-------+


```

在查询中的每个表会输出一行，如果有两个表通过 join 连接查询，那么会输出两行。表的意义相当广泛：可以是子查询、一个 union 结果等。

explain 有两个变种：

1） **`explain extended`** ：会在 explain  的基础上额外提供一些查询优化的信息。紧随其后通过 show warnings 命令可以 得到优化后的查询语句，从而看出优化器优化了什么。额外还有 filtered 列，是一个半分比的值，rows * filtered/100 可以估算出将要和 explain 中前一个表进行连接的行数（前一个表指 explain 中的id值比当前表id值小的表）。

```sql


mysql> explain extended select * from film where id = 1;
+----+-------------+-------+-------+---------------+---------+---------+-------+------+----------+-------+
| id | select_type | table | type  | possible_keys | key     | key_len | ref   | rows | filtered | Extra |
+----+-------------+-------+-------+---------------+---------+---------+-------+------+----------+-------+
|  1 | SIMPLE      | film  | const | PRIMARY       | PRIMARY | 4       | const |    1 |   100.00 | NULL  |
+----+-------------+-------+-------+---------------+---------+---------+-------+------+----------+-------+

mysql> show warnings;
+-------+------+--------------------------------------------------------------------------------+
| Level | Code | Message                                                                        |
+-------+------+--------------------------------------------------------------------------------+
| Note  | 1003 | /* select#1 */ select '1' AS `id`,'film1' AS `name` from `test`.`film` where 1 |
+-------+------+--------------------------------------------------------------------------------+


```

2） **`explain partitions`** ：相比 explain 多了个 partitions 字段，如果查询是基于分区表的话，会显示查询将访问的分区。


## explain 中的列

接下来我们将展示 explain 中每个列的信息。


### 1. id列

id列的编号是 select 的序列号，有几个 select 就有几个id，id是一组数字，表示查询中执行select子句或操作表的顺序，如果id相同，则执行顺序从上至下，如果是子查询，id的序号会递增，id越大则优先级越高，越先会被执行，id列为null的就表是这是一个结果集，不需要使用它来进行查询。MySQL将 select 查询分为简单查询和复杂查询。复杂查询分为三类：简单子查询、派生表（from语句中的子查询）、union 查询。

1）简单子查询

```sql


mysql> explain select (select 1 from actor limit 1) from film;
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key      | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
|  1 | PRIMARY     | film  | index | NULL          | idx_name | 32      | NULL |    1 | Using index |
|  2 | SUBQUERY    | actor | index | NULL          | PRIMARY  | 4       | NULL |    2 | Using index |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+ 


```

2）from子句中的子查询

```sql


mysql> explain select id from (select id from film) as der;
+----+-------------+------------+-------+---------------+----------+---------+------+------+-------------+
| id | select_type | table      | type  | possible_keys | key      | key_len | ref  | rows | Extra       |
+----+-------------+------------+-------+---------------+----------+---------+------+------+-------------+
|  1 | PRIMARY     | <derived2> | ALL   | NULL          | NULL     | NULL    | NULL |    2 | NULL        |
|  2 | DERIVED     | film       | index | NULL          | idx_name | 32      | NULL |    1 | Using index |
+----+-------------+------------+-------+---------------+----------+---------+------+------+-------------+


```

这个查询执行时有个临时表别名为der，外部 select 查询引用了这个临时表

3）union查询

```sql


mysql> explain select 1 union all select 1;
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+
| id | select_type  | table      | type | possible_keys | key  | key_len | ref  | rows | Extra           |
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+
|  1 | PRIMARY      | NULL       | NULL | NULL          | NULL | NULL    | NULL | NULL | No tables used  |
|  2 | UNION        | NULL       | NULL | NULL          | NULL | NULL    | NULL | NULL | No tables used  |
| NULL | UNION RESULT | <union1,2> | ALL  | NULL          | NULL | NULL    | NULL | NULL | Using temporary |
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+


```

union结果总是放在一个匿名临时表中，临时表不在SQL中出现，因此它的id是NULL。


### 2. select_type列

select_type 表示对应行是是简单还是复杂的查询，如果是复杂的查询，又是上述三种复杂查询中的哪一种。

1） **`simple`** ：表示不需要union操作或者不包含子查询的简单select查询。有连接查询时，外层的查询为simple，且只有一个。

```sql


mysql> explain select * from film where id = 2;
+----+-------------+-------+-------+---------------+---------+---------+-------+------+-------+
| id | select_type | table | type  | possible_keys | key     | key_len | ref   | rows | Extra |
+----+-------------+-------+-------+---------------+---------+---------+-------+------+-------+
|  1 | SIMPLE      | film  | const | PRIMARY       | PRIMARY | 4       | const |    1 | NULL  |
+----+-------------+-------+-------+---------------+---------+---------+-------+------+-------+


```

2） **`primary`** ：一个需要union操作或者含有子查询的select，位于最外层的单位查询的select_type即为primary。且只有一个。

3） **`subquery`** ：包含在 select 中的子查询（不在 from 子句中）

4） **`derived`** ：包含在 from 子句中的子查询。MySQL会将结果存放在一个临时表中，也称为派生表（derived的英文含义）

用这个例子来了解 primary、subquery 和 derived 类型

```sql


mysql> explain select (select 1 from actor where id = 1) from (select * from film where id = 1) der;
+----+-------------+------------+--------+---------------+---------+---------+-------+------+-------------+
| id | select_type | table      | type   | possible_keys | key     | key_len | ref   | rows | Extra       |
+----+-------------+------------+--------+---------------+---------+---------+-------+------+-------------+
|  1 | PRIMARY     | <derived3> | system | NULL          | NULL    | NULL    | NULL  |    1 | NULL        |
|  3 | DERIVED     | film       | const  | PRIMARY       | PRIMARY | 4       | const |    1 | NULL        |
|  2 | SUBQUERY    | actor      | const  | PRIMARY       | PRIMARY | 4       | const |    1 | Using index |
+----+-------------+------------+--------+---------------+---------+---------+-------+------+-------------+ 


```

5） **`union`** ：在 union 中的第二个和随后的 select

6） **`union result`** ：从 union 临时表检索结果的 select 在union和union all语句中,因为它不需要参与查询，所以id字段为null

用这个例子来了解 union 和 union result 类型：

```sql


mysql> explain select 1 union all select 1;
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+
| id | select_type  | table      | type | possible_keys | key  | key_len | ref  | rows | Extra           |
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+
|  1 | PRIMARY      | NULL       | NULL | NULL          | NULL | NULL    | NULL | NULL | No tables used  |
|  2 | UNION        | NULL       | NULL | NULL          | NULL | NULL    | NULL | NULL | No tables used  |
| NULL | UNION RESULT | <union1,2> | ALL  | NULL          | NULL | NULL    | NULL | NULL | Using temporary |
+----+--------------+------------+------+---------------+------+---------+------+------+-----------------+


```

7) **`dependent union`** ：与union一样，出现在union 或union all语句中，但是这个查询要受到外部查询的影响

8) **`dependent subquery`** ：与dependent union类似，表示这个subquery的查询要受到外部表查询的影响。


### 3. table列

这一列表示 explain 的一行正在访问哪个表。

当 from 子句中有子查询时，table列是 <derivenN> 格式，表示当前查询依赖 id=N 的查询，于是先执行 id=N 的查询。当有 union 时，UNION RESULT 的 table 列的值为 <union1,2>，1和2表示参与 union 的 select 行id。


### 4. type列

这一列表示关联类型或访问类型，即MySQL决定如何查找表中的行。

依次从最优到最差分别为：system > const > eq_ref > ref > fulltext > ref_or_null > index_merge > unique_subquery > index_subquery > range > index > ALL

除了all之外，其他的type都可以使用到索引，除了index_merge之外，其他的type只可以用到一个索引

NULL：mysql能够在优化阶段分解查询语句，在执行阶段用不着再访问表或索引。例如：在索引列中选取最小值，可以单独查找索引来完成，不需要在执行时访问表

```sql


mysql> explain select min(id) from film;
+----+-------------+-------+------+---------------+------+---------+------+------+------------------------------+
| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra                        |
+----+-------------+-------+------+---------------+------+---------+------+------+------------------------------+
|  1 | SIMPLE      | NULL  | NULL | NULL          | NULL | NULL    | NULL | NULL | Select tables optimized away |
+----+-------------+-------+------+---------------+------+---------+------+------+------------------------------+


```
`const, system`：mysql能对查询的某部分进行优化并将其转化成一个常量（可以看show warnings 的结果）。用于 primary key 或 unique key 的所有列与常数比较时，所以表最多有一个匹配行，读取1次，速度比较快。system：表中只有一行数据或者是空表，const：使用唯一索引或者主键，返回记录一定是1行记录的等值where条件时，通常type是const。其他数据库也叫做唯一索引扫描。

```sql


mysql> explain extended select * from (select * from film where id = 1) tmp;
+----+-------------+------------+--------+---------------+---------+---------+-------+------+----------+-------+
| id | select_type | table      | type   | possible_keys | key     | key_len | ref   | rows | filtered | Extra |
+----+-------------+------------+--------+---------------+---------+---------+-------+------+----------+-------+
|  1 | PRIMARY     | <derived2> | system | NULL          | NULL    | NULL    | NULL  |    1 |   100.00 | NULL  |
|  2 | DERIVED     | film       | const  | PRIMARY       | PRIMARY | 4       | const |    1 |   100.00 | NULL  |
+----+-------------+------------+--------+---------------+---------+---------+-------+------+----------+-------+

mysql> show warnings;
+-------+------+---------------------------------------------------------------+
| Level | Code | Message                                                       |
+-------+------+---------------------------------------------------------------+
| Note  | 1003 | /* select#1 */ select '1' AS `id`,'film1' AS `name` from dual |
+-------+------+---------------------------------------------------------------+


```

eq_ref：primary key 或 unique key 索引的所有部分被连接使用 ，最多只会返回一条符合条件的记录。这可能是在 const 之外最好的联接类型了，简单的 select 查询不会出现这种 type。

  
```sql


mysql> explain select * from film_actor left join film on film_actor.film_id = film.id;
+----+-------------+------------+--------+---------------+-------------------+---------+-------------------------+------+-------------+
| id | select_type | table      | type   | possible_keys | key               | key_len | ref                     | rows | Extra       |
+----+-------------+------------+--------+---------------+-------------------+---------+-------------------------+------+-------------+
|  1 | SIMPLE      | film_actor | index  | NULL          | idx_film_actor_id | 8       | NULL                    |    3 | Using index |
|  1 | SIMPLE      | film       | eq_ref | PRIMARY       | PRIMARY           | 4       | test.film_actor.film_id |    1 | NULL        |
+----+-------------+------------+--------+---------------+-------------------+---------+-------------------------+------+-------------+


```
`ref`：相比 `eq_ref`，不使用唯一索引，而是使用普通索引或者唯一性索引的部分前缀，索引要和某个值相比较，可能会找到多个符合条件的行。

    
```sql


1. 简单 select 查询，name是普通索引（非唯一索引）
mysql> explain select * from film where name = "film1";
+----+-------------+-------+------+---------------+----------+---------+-------+------+--------------------------+
| id | select_type | table | type | possible_keys | key      | key_len | ref   | rows | Extra                    |
+----+-------------+-------+------+---------------+----------+---------+-------+------+--------------------------+
|  1 | SIMPLE      | film  | ref  | idx_name      | idx_name | 33      | const |    1 | Using where; Using index |
+----+-------------+-------+------+---------------+----------+---------+-------+------+--------------------------+

2.关联表查询，idx_film_actor_id是film_id和actor_id的联合索引，这里使用到了film_actor的左边前缀film_id部分。
mysql> explain select * from film left join film_actor on film.id = film_actor.film_id;
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+-------------+
| id | select_type | table      | type  | possible_keys     | key               | key_len | ref          | rows | Extra       |
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+-------------+
|  1 | SIMPLE      | film       | index | NULL              | idx_name          | 33      | NULL         |    3 | Using index |
|  1 | SIMPLE      | film_actor | ref   | idx_film_actor_id | idx_film_actor_id | 4       | test.film.id |    1 | Using index |
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+-------------+


```
`ref_or_null`：类似`ref`，但是可以搜索值为NULL的行。

```sql


mysql> explain select * from film where name = "film1" or name is null;
+----+-------------+-------+-------------+---------------+----------+---------+-------+------+--------------------------+
| id | select_type | table | type        | possible_keys | key      | key_len | ref   | rows | Extra                    |
+----+-------------+-------+-------------+---------------+----------+---------+-------+------+--------------------------+
|  1 | SIMPLE      | film  | ref_or_null | idx_name      | idx_name | 33      | const |    2 | Using where; Using index |
+----+-------------+-------+-------------+---------------+----------+---------+-------+------+--------------------------+


```
`index_merge`：表示使用了索引合并的优化方法。 例如下表：id是主键，tenant_id是普通索引。or 的时候没有用 primary key，而是使用了 primary key(id) 和 tenant_id 索引

```sql


mysql> explain select * from role where id = 11011 or tenant_id = 8888;
+----+-------------+-------+-------------+-----------------------+-----------------------+---------+------+------+-------------------------------------------------+
| id | select_type | table | type        | possible_keys         | key                   | key_len | ref  | rows | Extra                                           |
+----+-------------+-------+-------------+-----------------------+-----------------------+---------+------+------+-------------------------------------------------+
|  1 | SIMPLE      | role  | index_merge | PRIMARY,idx_tenant_id | PRIMARY,idx_tenant_id | 4,4     | NULL |  134 | Using union(PRIMARY,idx_tenant_id); Using where |
+----+-------------+-------+-------------+-----------------------+-----------------------+---------+------+------+-------------------------------------------------+


```
`range`：范围扫描通常出现在 in(), between ,> ,<, >= 等操作中。使用一个索引来检索给定范围的行。

```sql


mysql> explain select * from actor where id > 1;
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key     | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+
|  1 | SIMPLE      | actor | range | PRIMARY       | PRIMARY | 4       | NULL |    2 | Using where |
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+


```
`index`：和ALL一样，不同就是mysql只需扫描索引树，这通常比ALL快一些。

      
```sql


mysql> explain select count(*) from film;
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key      | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
|  1 | SIMPLE      | film  | index | NULL          | idx_name | 33      | NULL |    3 | Using index |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+


```
` **`ALL`** ：`即全表扫描，意味着mysql需要从头到尾去查找所需要的行。通常情况下这需要增加索引来进行优化了

        
```sql


mysql> explain select * from actor;
+----+-------------+-------+------+---------------+------+---------+------+------+-------+
| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra |
+----+-------------+-------+------+---------------+------+---------+------+------+-------+
|  1 | SIMPLE      | actor | ALL  | NULL          | NULL | NULL    | NULL |    2 | NULL  |
+----+-------------+-------+------+---------------+------+---------+------+------+-------+


```

        
### 5. possible_keys列

这一列显示查询可能使用哪些索引来查找。 

explain 时可能出现 possible_keys 有列，而 key 显示 NULL 的情况，这种情况是因为表中数据不多，mysql认为索引对此查询帮助不大，选择了全表查询。 

如果该列是NULL，则没有相关的索引。在这种情况下，可以通过检查 where 子句看是否可以创造一个适当的索引来提高查询性能，然后用 explain 查看效果。

        
### 6. key列           

        

      

      
这一列显示mysql实际采用哪个索引来优化对该表的访问。

如果没有使用索引，则该列是 NULL。如果想强制mysql使用或忽视possible_keys列中的索引，在查询中使用 force index、ignore index。

      
### 7. key_len列

这一列显示了mysql在索引里使用的字节数，通过这个值可以算出具体使用了索引中的哪些列。

举例来说，film_actor的联合索引 idx_film_actor_id 由 film_id 和 actor_id 两个int列组成，并且每个int是4字节。通过结果中的key_len=4可推断出查询使用了第一个列：film_id列来执行索引查找。

```sql


mysql> explain select * from film_actor where film_id = 2;
+----+-------------+------------+------+-------------------+-------------------+---------+-------+------+-------------+
| id | select_type | table      | type | possible_keys     | key               | key_len | ref   | rows | Extra       |
+----+-------------+------------+------+-------------------+-------------------+---------+-------+------+-------------+
|  1 | SIMPLE      | film_actor | ref  | idx_film_actor_id | idx_film_actor_id | 4       | const |    1 | Using index |
+----+-------------+------------+------+-------------------+-------------------+---------+-------+------+-------------+


```

key_len计算规则如下：

      

* 字符串          

* char(n)：n字节长度
* varchar(n)：2字节存储字符串长度，如果是utf-8，则长度 3n + 2 字节长度和具体的字符编码有关.如utf-8一个字符就是三个字符.key_len的长度是以字节为单位的.
            

          
* 数值类型          

* tinyint：1字节
* smallint：2字节
* int：4字节
* bigint：8字节
            

          
* 时间类型          

* date：3字节
* timestamp：4字节
* datetime：8字节
            

          
* 如果字段允许为 NULL，需要1字节记录是否为 NULL
        

        
索引最大长度是768字节，当字符串过长时，mysql会做一个类似左前缀索引的处理，将前半部分的字符提取出来做索引。

        
### 8. ref列

        

      

这一列显示了在key列记录的索引中，表查找值所用到的列或常量，常见的有：const（常量），func，NULL，字段名（例：film.id）如果是使用的常数等值查询，这里会显示const，如果是连接查询，被驱动表的执行计划这里会显示驱动表的关联字段，如果是条件使用了表达式或者函数，或者条件列发生了内部隐式转换，这里可能显示为func。

    
### 9. rows列       

      
这一列是mysql估计要读取并检测的行数，注意这个不是结果集里的行数。

      
### 10. Extra列

这一列展示的是额外信息。常见的重要值如下：
`distinct`: 一旦mysql找到了与行相联合匹配的行，就不再搜索了

```sql


mysql> explain select distinct name from film left join film_actor on film.id = film_actor.film_id;
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+------------------------------+
| id | select_type | table      | type  | possible_keys     | key               | key_len | ref          | rows | Extra                        |
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+------------------------------+
|  1 | SIMPLE      | film       | index | idx_name          | idx_name          | 33      | NULL         |    3 | Using index; Using temporary |
|  1 | SIMPLE      | film_actor | ref   | idx_film_actor_id | idx_film_actor_id | 4       | test.film.id |    1 | Using index; Distinct        |
+----+-------------+------------+-------+-------------------+-------------------+---------+--------------+------+------------------------------+


```
`Using index`：这发生在对表的请求列都是同一索引的部分的时候，返回的列数据只使用了索引中的信息，而没有再去访问表中的行记录。是性能高的表现。

        
```sql


mysql> explain select id from film order by id;
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key     | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+
|  1 | SIMPLE      | film  | index | NULL          | PRIMARY | 4       | NULL |    3 | Using index |
+----+-------------+-------+-------+---------------+---------+---------+------+------+-------------+ 


```
`Using where`：mysql服务器将在存储引擎检索行后再进行过滤。就是先读取整行数据，再按 where 条件进行检查，符合就留下，不符合就丢弃。

          
  
```sql


mysql> explain select * from film where id > 1;
+----+-------------+-------+-------+---------------+----------+---------+------+------+--------------------------+
| id | select_type | table | type  | possible_keys | key      | key_len | ref  | rows | Extra                    |
+----+-------------+-------+-------+---------------+----------+---------+------+------+--------------------------+
|  1 | SIMPLE      | film  | index | PRIMARY       | idx_name | 33      | NULL |    3 | Using where; Using index |
+----+-------------+-------+-------+---------------+----------+---------+------+------+--------------------------+


```
`Using temporary`：mysql需要创建一张临时表来处理查询。出现这种情况一般是要进行优化的，首先是想到用索引来优化。表示使用了临时表存储中间结果。临时表可以是内存临时表和磁盘临时表  

            
    
```sql


1. actor.name没有索引，此时创建了张临时表来distinct
mysql> explain select distinct name from actor;
+----+-------------+-------+------+---------------+------+---------+------+------+-----------------+
| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra           |
+----+-------------+-------+------+---------------+------+---------+------+------+-----------------+
|  1 | SIMPLE      | actor | ALL  | NULL          | NULL | NULL    | NULL |    2 | Using temporary |
+----+-------------+-------+------+---------------+------+---------+------+------+-----------------+

2. film.name建立了idx_name索引，此时查询时extra是using index,没有用临时表
mysql> explain select distinct name from film;
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key      | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
|  1 | SIMPLE      | film  | index | idx_name      | idx_name | 33      | NULL |    3 | Using index |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+


```
`Using filesort`：mysql 会对结果使用一个外部索引排序，而不是按索引次序从表里读取行。此时mysql会根据联接类型浏览所有符合条件的记录，并保存排序关键字和行指针，然后排序关键字并按顺序检索行信息。这种情况下一般也是要考虑使用索引来优化的。    

    
```sql


1. actor.name未创建索引，会浏览actor整个表，保存排序关键字name和对应的id，然后排序name并检索行记录
mysql> explain select * from actor order by name;
+----+-------------+-------+------+---------------+------+---------+------+------+----------------+
| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra          |
+----+-------------+-------+------+---------------+------+---------+------+------+----------------+
|  1 | SIMPLE      | actor | ALL  | NULL          | NULL | NULL    | NULL |    2 | Using filesort |
+----+-------------+-------+------+---------------+------+---------+------+------+----------------+

2. film.name建立了idx_name索引,此时查询时extra是using index
mysql> explain select * from film order by name;
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
| id | select_type | table | type  | possible_keys | key      | key_len | ref  | rows | Extra       |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+
|  1 | SIMPLE      | film  | index | NULL          | idx_name | 33      | NULL |    3 | Using index |
+----+-------------+-------+-------+---------------+----------+---------+------+------+-------------+


```


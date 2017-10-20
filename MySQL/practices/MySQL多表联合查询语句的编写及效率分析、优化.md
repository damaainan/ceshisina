# MySQL多表联合查询语句的编写及效率分析、优化

 时间 2017-10-13 14:04:24 

原文[http://www.linuxidc.com/Linux/2017-10/147568.htm][1]


#### 一、多表连接类型

1. 笛卡尔积(交叉连接) 

在MySQL中可以为CROSS JOIN或者省略CROSS即JOIN，或者使用',' 如：

```sql
    SELECT * FROM table1 CROSS JOIN table2   
    SELECT * FROM table1 JOIN table2   
    SELECT * FROM table1,table2

    SELECT * FROM users CROSS JOIN articles;
    SELECT * FROM users JOIN articles;
    SELECT * FROM users, articles;
```
由于其返回的结果为被连接的两个数据表的乘积，因此当有WHERE, ON或USING条件的时候一般不建议使用，因为当数据表项目太多的时候，会非常慢。一般使用LEFT [OUTER] JOIN或者RIGHT [OUTER] JOIN

2. 内连接INNER JOIN 

在MySQL中把INNER JOIN叫做等值连接，即需要指定等值连接条件在MySQL中CROSS和INNER JOIN被划分在一起。

```sql
    SELECT * FROM users as u INNER JOIN articles as a where u.id = a.user_id
```
3. MySQL中的外连接 

分为左外连接和右连接，即除了返回符合连接条件的结果之外，还要返回左表(左连接)或者右表(右连接)中不符合连接条件的结果，相对应的使用NULL对应。

例子： 

users表：

```
    +----+----------+----------+--------------------+
    | id | username | password | email              |
    +----+----------+----------+--------------------+
    |  1 | junxi    | 123      | xinlei3166@126.com |
    |  2 | tangtang | 456      | xinlei3166@126.com |
    |  3 | ceshi3   | 456      | ceshi3@11.com      |
    |  4 | ceshi4   | 456      | ceshi4@qq.com      |
    |  5 | ceshi3   | 456      | ceshi3@11.com      |
    |  6 | ceshi4   | 456      | ceshi4@qq.com      |
    |  7 | ceshi3   | 456      | ceshi3@11.com      |
    |  8 | ceshi4   | 456      | ceshi4@qq.com      |
    |  9 | ceshi3   | 333      | ceshi3@11.com      |
    | 10 | ceshi4   | 444      | ceshi4@qq.com      |
    | 11 | ceshi3   | 333      | ceshi3@11.com      |
    | 12 | ceshi4   | 444      | ceshi4@qq.com      |
    +----+----------+----------+--------------------+
```
userinfos表：

```
    +----+-------+--------+-------------+----------------+---------+
    | id | name  | qq     | phone       | link           | user_id |
    +----+-------+--------+-------------+----------------+---------+
    |  1 | 君惜  | 666666 | 16616555188 | www.junxi.site |       1 |
    |  2 | 糖糖  | 777777 | 17717555177 | www.weizhi.com |       2 |
    |  3 | 测试3 | 333333 | 13313333177 | www.ceshi3.com |       3 |
    +----+-------+--------+-------------+----------------+---------+
```
SQL语句：

```sql
    SELECT * FROM users as u LEFT JOIN userinfos as i on u.id = i.user_id;
```
执行结果：

    +----+----------+----------+--------------------+------+-------+--------+-------------+----------------+---------+
    | id | username | password | email              | id   | name  | qq     | phone       | link           | user_id |
    +----+----------+----------+--------------------+------+-------+--------+-------------+----------------+---------+
    |  1 | junxi    | 123      | xinlei3166@126.com |    1 | 君惜  | 666666 | 16616555188 | www.junxi.site |       1 |
    |  2 | tangtang | 456      | xinlei3166@126.com |    2 | 糖糖  | 777777 | 17717555177 | www.weizhi.com |       2 |
    |  3 | ceshi3   | 456      | ceshi3@11.com      |    3 | 测试3 | 333333 | 13313333177 | www.ceshi3.com |       3 |
    |  4 | ceshi4   | 456      | ceshi4@qq.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    |  5 | ceshi3   | 456      | ceshi3@11.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    |  6 | ceshi4   | 456      | ceshi4@qq.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    |  7 | ceshi3   | 456      | ceshi3@11.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    |  8 | ceshi4   | 456      | ceshi4@qq.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    |  9 | ceshi3   | 333      | ceshi3@11.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    | 10 | ceshi4   | 444      | ceshi4@qq.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    | 11 | ceshi3   | 333      | ceshi3@11.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    | 12 | ceshi4   | 444      | ceshi4@qq.com      | NULL | NULL  | NULL   | NULL        | NULL           |    NULL |
    +----+----------+----------+--------------------+------+-------+--------+-------------+----------------+---------+

分析：

而users表中的id大于3的用户在userinfos中没有相应的纪录，但是却出现在了结果集中

因为现在是left join，所有的工作以left为准.

结果1，2，3都是既在左表又在右表的纪录4, 5, 6, 7, 8, 9, 10, 11, 12是只在左表，不在右表的纪录

工作原理：

从左表读出一条，选出所有与on匹配的右表纪录(n条)进行连接，形成n条纪录(包括重复的行)，如果右边没有与on条件匹配的表，那连接的字段都是null.然后继续读下一条。

引申：

我们可以用右表没有on匹配则显示null的规律, 来找出所有在左表，不在右表的纪录， 注意用来判断的那列必须声明为not null的。

如：

SQL:

(注意:

1.列值为null应该用is null 而不能用=NULL

2.这里i.user_id 列必须声明为 NOT NULL 的.

）

```sql
    SELECT * FROM users as u LEFT JOIN userinfos as i on u.id = i.user_id WHERE i.user_id is NULL;
```
执行结果：

    +----+----------+----------+---------------+------+------+------+-------+------+---------+
    | id | username | password | email         | id   | name | qq   | phone | link | user_id |
    +----+----------+----------+---------------+------+------+------+-------+------+---------+
    |  4 | ceshi4   | 456      | ceshi4@qq.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    |  5 | ceshi3   | 456      | ceshi3@11.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    |  6 | ceshi4   | 456      | ceshi4@qq.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    |  7 | ceshi3   | 456      | ceshi3@11.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    |  8 | ceshi4   | 456      | ceshi4@qq.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    |  9 | ceshi3   | 333      | ceshi3@11.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    | 10 | ceshi4   | 444      | ceshi4@qq.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    | 11 | ceshi3   | 333      | ceshi3@11.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    | 12 | ceshi4   | 444      | ceshi4@qq.com | NULL | NULL | NULL | NULL  | NULL |    NULL |
    +----+----------+----------+---------------+------+------+------+-------+------+---------+

一般用法：

a. LEFT [OUTER] JOIN：

除了返回符合连接条件的结果之外，还需要显示左表中不符合连接条件的数据列，相对应使用NULL对应

```sql
    SELECT column_name FROM table1 LEFT [OUTER] JOIN table2 ON table1.column=table2.column
```
b. RIGHT [OUTER] JOIN：

RIGHT与LEFT JOIN相似不同的仅仅是除了显示符合连接条件的结果之外，还需要显示右表中不符合连接条件的数据列，相应使用NULL对应

```sql
    SELECT column_name FROM table1 RIGHT [OUTER] JOIN table2 ON table1.column=table2.column
```
Tips:

1. on a.c1 = b.c1 等同于 using(c1)
1. INNER JOIN 和 , (逗号) 在语义上是等同的
1. 当 MySQL 在从一个表中检索信息时，你可以提示它选择了哪一个索引。   

如果 EXPLAIN 显示 MySQL 使用了可能的索引列表中错误的索引，这个特性将是很有用的。   
通过指定 USE INDEX (key_list)，你可以告诉 MySQL 使用可能的索引中最合适的一个索引在表中查找记录行。   
可选的二选一句法 IGNORE INDEX (key_list) 可被用于告诉 MySQL 不使用特定的索引。如：

```sql
    mysql> SELECT * FROM table1 USE INDEX (key1,key2) WHERE key1=1 AND key2=2 AND key3=3;  
    mysql> SELECT * FROM table1 IGNORE INDEX (key3) WHERE key1=1 AND key2=2 AND key3=3;
```
#### 二、表连接的约束条件

添加显示条件WHERE, ON, USING

#### 1. WHERE子句

```sql
    SELECT * FROM table1,table2 WHERE table1.id=table2.id;

    SELECT * FROM users, userinfos WHERE users.id=userinfos.user_id;
```
#### 2. ON

```sql
    SELECT * FROM table1 LEFT JOIN table2 ON table1.id=table2.id;  
    SELECT * FROM table1 LEFT JOIN table2 ON table1.id=table2.id LEFT JOIN table3 ON table2.id=table3.id;

    SELECT * FROM users LEFT JOIN articles ON users.id = articles.user_id;
    SELECT * FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id;
```
#### 3. USING子句，如果连接的两个表连接条件的两个列具有相同的名字的话可以使用USING

例如：

    SELECT FROM LEFT JOIN USING ()

连接多于两个表的情况举例：

```sql
    SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id;
```
执行结果：

```sql
    mysql> SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id;
    +----------+-------+------------+
    | username | name  | title      |
    +----------+-------+------------+
    | junxi    | 君惜  | 中国有嘻哈 |
    | tangtang | 糖糖  | 星光大道   |
    | ceshi3   | 测试3 | 平凡的真谛 |
    | junxi    | 君惜  | python进阶 |
    | ceshi3   | NULL  | NULL       |
    | ceshi3   | NULL  | NULL       |
```
或者

```sql
    SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id WHERE (articles.user_id IS NOT NULL AND userinfos.user_id IS NOT NULL);
```
执行结果：

```sql
    mysql> SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id WHERE (articles.user_id IS NOT NULL AND userinfos.user_
    id IS NOT NULL);
    +----------+-------+------------+
    | username | name  | title      |
    +----------+-------+------------+
    | junxi    | 君惜  | 中国有嘻哈 |
    | tangtang | 糖糖  | 星光大道   |
    | ceshi3   | 测试3 | 平凡的真谛 |
    | junxi    | 君惜  | python进阶 |
    +----------+-------+------------+
```
或者

```sql
    SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id WHERE (userinfos.name = '君惜');
```
执行结果：

```sql
    mysql> SELECT users.username, userinfos.name, articles.title FROM users LEFT JOIN userinfos ON users.id = userinfos.user_id LEFT JOIN articles ON users.id = articles.user_id WHERE (userinfos.name = '君惜');
    +----------+------+------------+
    | username | name | title      |
    +----------+------+------------+
    | junxi    | 君惜 | 中国有嘻哈 |
    | junxi    | 君惜 | python进阶 |
    +----------+------+------------+
```
另外需要注意的地方在MySQL中涉及到多表查询的时候，需要根据查询的情况，想好使用哪种连接方式效率更高。 

1. 交叉连接(笛卡尔积)或者内连接 [INNER | CROSS] JOIN
1. 左外连接LEFT [OUTER] JOIN或者右外连接RIGHT [OUTER] JOIN 注意指定连接条件WHERE, ON，USING.

#### 三、MySQL如何优化LEFT JOIN和RIGHT JOIN

在MySQL中，A LEFT JOIN B join_condition执行过程如下：

1)· 根据表A和A依赖的所有表设置表B。

2)· 根据LEFT JOIN条件中使用的所有表(除了B)设置表A。

3)· LEFT JOIN条件用于确定如何从表B搜索行。(换句话说，不使用WHERE子句中的任何条件）。

4)· 可以对所有标准连接进行优化，只是只有从它所依赖的所有表读取的表例外。如果出现循环依赖关系，MySQL提示出现一个错误。

5)· 进行所有标准WHERE优化。

6)· 如果A中有一行匹配WHERE子句，但B中没有一行匹配ON条件，则生成另一个B行，其中所有列设置为NULL。

7)· 如果使用LEFT JOIN找出在某些表中不存在的行，并且进行了下面的测试：WHERE部分的col_name IS NULL，其中col_name是一个声明为 NOT NULL的列，MySQL找到匹配LEFT JOIN条件的一个行后停止(为具体的关键字组合)搜索其它行。

RIGHT JOIN的执行类似LEFT JOIN，只是表的角色反过来。

连接优化器计算表应连接的顺序。LEFT JOIN和STRAIGHT_JOIN强制的表读顺序可以帮助连接优化器更快地工作，因为检查的表交换更少。请注意这说明如果执行下面类型的查询，MySQL进行全扫描b，因为LEFT JOIN强制它在d之前读取：

```sql
    SELECT * FROM a,b LEFT JOIN c ON (c.key=a.key) LEFT JOIN d ON (d.key=a.key) WHERE b.key=d.key;
```
在这种情况下修复时用a的相反顺序，b列于FROM子句中：

```sql
    SELECT * FROM b,a LEFT JOIN c ON (c.key=a.key) LEFT JOIN d ON (d.key=a.key) WHERE b.key=d.key;
```
MySQL可以进行下面的LEFT JOIN优化：如果对于产生的NULL行，WHERE条件总为假，LEFT JOIN变为普通联接。

例如，在下面的查询中如果t2.column1为NULL，WHERE 子句将为false：

```sql
    SELECT * FROM t1 LEFT JOIN t2 ON (column1) WHERE t2.column2=5;
```
因此，可以安全地将查询转换为普通联接：

```sql
    SELECT * FROM t1, t2 WHERE t2.column2=5 AND t1.column1=t2.column1;
```
这样可以更快，因为如果可以使查询更佳，MySQL可以在表t1之前使用表t2。为了强制使用表顺序，使用STRAIGHT_JOIN。


[1]: http://www.linuxidc.com/Linux/2017-10/147568.htm
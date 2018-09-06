## [Mysql Join语法以及性能优化][0]

**阅读目录(Content)**

* [引言][1]
* [一．Join语法概述][2]
    * [JOIN 功能分类][3]
* [二.Inner join][4]
* [三.Left join][5]
* [四.Right join][6]
* [五.Cross join][7]
* [六.Full join][8]
* [七.性能优化][9]
    * [1.显示(explicit) inner join VS 隐式(implicit) inner join][10]
    * [2.left join/right join VS inner join][11]
        * [2.1 on与 where的执行顺序][12]
        * [2.2 注意ON 子句和 WHERE 子句的不同][13]
        * [2.3 尽量避免子查询，而用join][14]
* [八.测试题(多表连接join查询)][15]
    * [1. 题目][16]
        * [1.1 班级表][17]
        * [1.2 比赛表][18]
    * [2. 详解][19]
        * [2.1 分析][20]
        * [2.2 结果][21]

## 引言 

**内外联结的区别**是**内联结**将**去除所有不符合条件**的记录，而**外联结**则**保留其中部分**。**外左联结**与**外右联结**的区别在于如果用**A左联结B**则**A中所有记录都会保留**在结果中，此时B中只有符合联结条件的记录，而右联结相反，这样也就不会混淆了。

![][23]

## 一．Join语法概述 

join 用于多表中字段之间的联系，语法如下：

代码如下:

    FROM table1 INNER|LEFT|RIGHT JOIN table2 ON conditiona

table1:左表；table2:右表。

### JOIN 功能分类

**INNER JOIN（内连接,或等值连接）** ：取得两个表中存在连接匹配关系的记录。

**LEFT JOIN（左连接）**：取得左表（table1）完全记录，即是右表（table2）并无对应匹配记录。

**RIGHT JOIN（右连接）**：与 LEFT JOIN 相反，取得右表（table2）完全记录，即是左表（table1）并无匹配对应记录。

注意：mysql不支持Full join,不过可以通过UNION 关键字来合并 LEFT JOIN 与 RIGHT JOIN来模拟FULL join.

接下来给出一个列子用于解释下面几种分类。如下两个表(A,B)

代码如下:

```sql
    mysql> select A.id,A.name,B.name from A,B where A.id=B.id;
    +----+-----------+-------------+
    | id | name | name |
    +----+-----------+-------------+
    | 1 | Pirate | Rutabaga |
    | 2 | Monkey | Pirate |
    | 3 | Ninja | Darth Vader |
    | 4 | Spaghetti | Ninja |
    +----+-----------+-------------+
    4 rows in set (0.00 sec)
```


## 二.Inner join 

**内连接**，也叫**等值连接**，inner join产生**同时符合A和B**的一组数据。

代码如下:

```sql
    mysql> select * from A inner join B on A.name = B.name;
    +----+--------+----+--------+
    | id | name | id | name |
    +----+--------+----+--------+
    | 1 | Pirate | 2 | Pirate |
    | 3 | Ninja | 4 | Ninja |
    +----+--------+----+--------+
```


![][24]

## **三.Left join** 

代码如下:

```sql
    mysql> select * from A left join B on A.name = B.name;
    #或者：select * from A left outer join B on A.name = B.name;
    +----+-----------+------+--------+
    | id | name | id | name |
    +----+-----------+------+--------+
    | 1 | Pirate | 2 | Pirate |
    | 2 | Monkey | NULL | NULL |
    | 3 | Ninja | 4 | Ninja |
    | 4 | Spaghetti | NULL | NULL |
    +----+-----------+------+--------+
    4 rows in set (0.00 sec)
```


**left join,**（或left outer join:在Mysql中两者等价，推荐使用left join.）**左连接从左表(A)产生一套完整的记录,与匹配的记录(右表(B))** .如果没有匹配,右侧将包含null。

![][25]

如果想只从左表(A)中产生一套记录，但不包含右表(B)的记录，可以通过设置where语句来执行，如下：

代码如下:

```sql
    mysql> select * from A left join B on A.name=B.name where A.id is null or B.id is null;
    +----+-----------+------+------+
    | id | name | id | name |
    +----+-----------+------+------+
    | 2 | Monkey | NULL | NULL |
    | 4 | Spaghetti | NULL | NULL |
    +----+-----------+------+------+
    2 rows in set (0.00 sec)
```


![][26]

同理，还可以模拟inner join. 如下：

代码如下:

```sql
    mysql> select * from A left join B on A.name=B.name where A.id is not null and B.id is not null;
    +----+--------+------+--------+
    | id | name | id | name |
    +----+--------+------+--------+
    | 1 | Pirate | 2 | Pirate |
    | 3 | Ninja | 4 | Ninja |
    +----+--------+------+--------+
    2 rows in set (0.00 sec)
```


求差集：

根据上面的例子可以求差集，如下：

代码如下:

```sql
    SELECT * FROM A LEFT JOIN B ON A.name = B.name
    WHERE B.id IS NULL
    union
    SELECT * FROM A right JOIN B ON A.name = B.name
    WHERE A.id IS NULL;
    # 结果
    +------+-----------+------+-------------+
    | id | name | id | name |
    +------+-----------+------+-------------+
    | 2 | Monkey | NULL | NULL |
    | 4 | Spaghetti | NULL | NULL |
    | NULL | NULL | 1 | Rutabaga |
    | NULL | NULL | 3 | Darth Vader |
    +------+-----------+------+-------------+
```


![][27]

## 四.Right join 

代码如下:

```sql
    mysql> select * from A right join B on A.name = B.name;
    +------+--------+----+-------------+
    | id | name | id | name |
    +------+--------+----+-------------+
    | NULL | NULL | 1 | Rutabaga |
    | 1 | Pirate | 2 | Pirate |
    | NULL | NULL | 3 | Darth Vader |
    | 3 | Ninja | 4 | Ninja |
    +------+--------+----+-------------+
    4 rows in set (0.00 sec)
```


同left join。

## 五.Cross join 

**cross join**：交叉连接，得到的结果是**两个表的乘积**，即**笛卡尔积**

笛卡尔（Descartes）乘积又叫直积。假设集合A={a,b}，集合B={0,1,2}，则两个集合的笛卡尔积为{(a,0),(a,1),(a,2),(b,0),(b,1), (b,2)}。可以扩展到多个集合的情况。类似的例子有，如果A表示某学校学生的集合，B表示该学校所有课程的集合，则A与B的**笛卡 尔积**表示**所有可能的选课情况**。

代码如下:

```sql
    mysql> select * from A cross join B;
    +----+-----------+----+-------------+
    | id | name | id | name |
    +----+-----------+----+-------------+
    | 1 | Pirate | 1 | Rutabaga |
    | 2 | Monkey | 1 | Rutabaga |
    | 3 | Ninja | 1 | Rutabaga |
    | 4 | Spaghetti | 1 | Rutabaga |
    | 1 | Pirate | 2 | Pirate |
    | 2 | Monkey | 2 | Pirate |
    | 3 | Ninja | 2 | Pirate |
    | 4 | Spaghetti | 2 | Pirate |
    | 1 | Pirate | 3 | Darth Vader |
    | 2 | Monkey | 3 | Darth Vader |
    | 3 | Ninja | 3 | Darth Vader |
    | 4 | Spaghetti | 3 | Darth Vader |
    | 1 | Pirate | 4 | Ninja |
    | 2 | Monkey | 4 | Ninja |
    | 3 | Ninja | 4 | Ninja |
    | 4 | Spaghetti | 4 | Ninja |
    +----+-----------+----+-------------+
    16 rows in set (0.00 sec)
```


    #再执行：mysql> select * from A inner join B; 试一试
    
    #在执行mysql> select * from A cross join B on A.name = B.name; 试一试

实际上，在 MySQL 中（**仅限于 MySQL**） **CROSS JOIN** 与**INNER JOIN** 的表现是**一样的**，在不指定 ON 条件得到的结果都是笛卡尔积，反之取得两个表完全匹配的结果。 INNER JOIN 与 CROSS JOIN 可以省略 INNER 或 CROSS 关键字，因此下面的 SQL 效果是一样的：

代码如下:

    ... FROM table1 INNER JOIN table2
    ... FROM table1 CROSS JOIN table2
    ... FROM table1 JOIN table2

## 六.Full join 

代码如下:

```sql
    mysql> select * from A left join B on B.name = A.name 
    -> union 
    -> select * from A right join B on B.name = A.name;
    +------+-----------+------+-------------+
    | id | name | id | name |
    +------+-----------+------+-------------+
    | 1 | Pirate | 2 | Pirate |
    | 2 | Monkey | NULL | NULL |
    | 3 | Ninja | 4 | Ninja |
    | 4 | Spaghetti | NULL | NULL |
    | NULL | NULL | 1 | Rutabaga |
    | NULL | NULL | 3 | Darth Vader |
    +------+-----------+------+-------------+
    6 rows in set (0.00 sec)
```


**全连接**产生的**所有记录（双方匹配记录）**在表A和表B。如果**没有匹配**,则对面**将包含null**。

![][28]

## 七.性能优化

### 1.显示(explicit) inner join VS 隐式(implicit) inner join

如：

代码如下:

```sql
    select * from
    table a inner join table b
    on a.id = b.id;
```

VS

代码如下:

```sql
    select a.*, b.*
    from table a, table b
    where a.id = b.id;
```

我在数据库中比较(10w数据)得之，它们用时几乎相同，第一个是显示的inner join，后一个是隐式的inner join。

### 2.left join/right join VS inner join

**尽量用inner join.避免 LEFT JOIN 和 NULL.**

在使用left join（或right join）时，应该清楚的知道以下几点：

#### 2.1 on与 where的执行顺序

ON 条件（“A LEFT JOIN B ON 条件表达式”中的ON）用来决定如何从 B 表中检索数据行。如果 B 表中没有任何一行数据匹配 ON 的条件,将会额外生成一行所有列为 NULL 的数据,在匹配阶段 WHERE 子句的条件都不会被使用。仅在**匹配阶段完成以后**，**WHERE 子句条件**才会被使用。**ON**将从**匹配阶段产生的数据**中**检索过滤**。

所以我们要注意：在使用Left (right) join的时候，一定要在先给出**尽可能多的匹配满足条件，减少Where的执行**。如：

PASS

代码如下:

```sql
    select * from A
    inner join B on B.name = A.name
    left join C on C.name = B.name
    left join D on D.id = C.id
    where C.status>1 and D.status=1;
```

Great

代码如下:

```sql
    select * from A
    inner join B on B.name = A.name
    left join C on C.name = B.name and C.status>1
    left join D on D.id = C.id and D.status=1
```

从上面例子可以看出，**尽可能满足ON的条件**，而**少用Where的条件**。从执行性能来看第二个显然更加省时。

#### 2.2 注意ON 子句和 WHERE 子句的不同

如作者举了一个列子：

代码如下:

```sql
    mysql> SELECT * FROM product LEFT JOIN product_details
    ON (product.id = product_details.id)
    AND product_details.id=2;
    +----+--------+------+--------+-------+
    | id | amount | id | weight | exist |
    +----+--------+------+--------+-------+
    | 1 | 100 | NULL | NULL | NULL |
    | 2 | 200 | 2 | 22 | 0 |
    | 3 | 300 | NULL | NULL | NULL |
    | 4 | 400 | NULL | NULL | NULL |
    +----+--------+------+--------+-------+
    4 rows in set (0.00 sec)
     
    
    mysql> SELECT * FROM product LEFT JOIN product_details
    ON (product.id = product_details.id)
    WHERE product_details.id=2;
    +----+--------+----+--------+-------+
    | id | amount | id | weight | exist |
    +----+--------+----+--------+-------+
    | 2 | 200 | 2 | 22 | 0 |
    +----+--------+----+--------+-------+
    1 row in set (0.01 sec)
```


从上可知，第一条查询使用 ON 条件决定了从 LEFT JOIN的 product_details表中检索符合的所有数据行。第二条查询做了简单的LEFT JOIN，然后使用 WHERE 子句从 LEFT JOIN的数据中过滤掉不符合条件的数据行。

#### 2.3 尽量避免子查询，而用join

往往性能这玩意儿，更多时候体现在数据量比较大的时候，此时，我们应该避免复杂的子查询。如下：

PASS

代码如下:

```sql
    insert into t1(a1) select b1 from t2 where not exists(select 1 from t1 where t1.id = t2.r_id);
```

Great

代码如下:

```sql
    insert into t1(a1) 
    select b1 from t2 
    left join (select distinct t1.id from t1 ) t1 on t1.id = t2.r_id 
    where t1.id is null;  
```

## 八.测试题(多表连接join查询)

### 1. 题目

  现有如下2个保存学校班级比赛的表,一个表保存班级id和班级名,另外一个表保存主场班级id,客场班级id和比赛场数,现在需要连接查询生成主场班级名,客场班级名,比赛场数的表,求具体实现的sql语句. 

#### 1.1 班级表

```sql
    mysql> select * from class;
    +------+--------+
    | c_id | c_name |
    +------+--------+
    |    1 | 1班    |
    |    2 | 2班    |
    |    3 | 3班    |
    |    4 | 4班    |
    +------+--------+
    4 rows in set (0.00 sec)
```


#### 1.2 比赛表

```sql
    mysql> select * from team;
    +------+------+------+
    | h_id | g_id | num  |
    +------+------+------+
    |    1 |    2 |   34 |
    |    2 |    4 |   37 |
    +------+------+------+
    2 rows in set (0.00 sec)
```


### 2. 详解

#### 2.1 分析 

发现要生成2个班级名的表,而一次join连接可以生成一个,则需要2次join连接,由于class表和team表连接内容必须要匹配,所以采用内连接.

#### 2.2 结果

```sql
    mysql> select ct.h_name,class.c_name as g_name,ct.num from (select class.c_name as h_name,team.* from class join team on class.c_id=team.h_id) as ct join class on ct.g_id=class.c_id;
    +--------+--------+------+
    | h_name | g_name | num  |
    +--------+--------+------+
    | 1班    | 2班    |   34 |
    | 2班    | 4班    |   37 |
    +--------+--------+------+
    2 rows in set (0.00 sec)
```

[0]: http://www.cnblogs.com/blueoverflow/p/4714470.html
[1]: #_label0
[2]: #_label1
[3]: #_lab2_1_0
[4]: #_label2
[5]: #_label3
[6]: #_label4
[7]: #_label5
[8]: #_label6
[9]: #_label7
[10]: #_lab2_7_0
[11]: #_lab2_7_1
[12]: #_label3_7_1_0
[13]: #_label3_7_1_1
[14]: #_label3_7_1_2
[15]: #_label8
[16]: #_lab2_8_0
[17]: #_label3_8_0_0
[18]: #_label3_8_0_1
[19]: #_lab2_8_1
[20]: #_label3_8_1_0
[21]: #_label3_8_1_1
[22]: #_labelTop
[23]: ./img/091828521433656.jpg
[24]: ./img/201452791529955.png
[25]: ./img/201452791556355.png
[26]: ./img/201452791622237.png
[27]: ./img/201452791648140.png
[28]: ./img/201452791935716.png
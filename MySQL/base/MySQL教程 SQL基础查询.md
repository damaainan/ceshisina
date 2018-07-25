## MySQL教程 SQL基础查询

来源：[http://www.cnblogs.com/huangminwen/p/9248908.html](http://www.cnblogs.com/huangminwen/p/9248908.html)

时间 2018-07-11 21:32:00


其实在数据库最经常用的当属查询操作


### 基本语法

```sql
SELECT
    [ALL | DISTINCT | DISTINCTROW ]
    字段列表 AS 字段别名
    [FROM 表名
    WHERE 条件表示式
    GROUP BY 字段名|表达式
      [ASC | DESC], ... [WITH ROLLUP]]
    [HAVING 条件表达式]
    [ORDER BY 字段名|表达式
      [ASC | DESC] , ...]
    [LIMIT {[offset,] row_count | row_count OFFSET offset}]
```

所有被使用的子句必须按语法说明中显示的顺序严格地排序。例如，一个HAVING子句必须位于GROUP BY子句之后，并位于ORDER BY子句之前。

ALL, DISTINCT和DISTINCTROW选项指定是否重复行应被返回，如果没有指定则默认值为ALL（返回所有匹配的行），DISTINCT和DISTINCTROW表示去重（如果是要删除重复的行，那么所有的字段都需要相同）

数据准备

```sql
CREATE TABLE IF NOT EXISTS score (
    id INT, -- 学生id
    name VARCHAR(10), -- 课程名称
    score NUMERIC(4, 1)); -- 分数

INSERT INTO score VALUES(1, '语文', 90);
INSERT INTO score VALUES(1, '数学', 95);
INSERT INTO score VALUES(1, '英语', 98);
INSERT INTO score VALUES(2, '语文', 92);
INSERT INTO score VALUES(2, '数学', 88);
INSERT INTO score VALUES(2, '英语', 90);
INSERT INTO score VALUES(3, '语文', 96);
INSERT INTO score VALUES(3, '数学', 100);
INSERT INTO score VALUES(3, '英语', 98);
```

字段别名：当数据进行查询出来的时候，有时候数据表的字段并不能符合我们的需求（多表查询的时候，可能会有同名的字段），这时候就需要对字段进行重命名


#### 注意：在一个WHERE子句中使用列别名是不允许的，因为当执行WHERE子句时，列值可能还没有被确定。

```sql
mysql> SELECT name, score FROM score; -- 没有使用别名
+------+-------+
| name | score |
+------+-------+
| 语文 | 90    |
| 数学 | 95    |
| 英语 | 98    |
| 语文 | 92    |
| 数学 | 88    |
| 英语 | 90    |
| 语文 | 96    |
| 数学 | 100   |
| 英语 | 98    |
+------+-------+
9 rows in set

mysql> SELECT name AS '课程名称', score '分数' FROM score; -- 使用别名，score字段使用了AS关键字
+----------+------+
| 课程名称 | 分数 |
+----------+------+
| 语文     | 90   |
| 数学     | 95   |
| 英语     | 98   |
| 语文     | 92   |
| 数学     | 88   |
| 英语     | 90   |
| 语文     | 96   |
| 数学     | 100  |
| 英语     | 98   |
+----------+------+
9 rows in set
```

使用AS明确地指定列的别名，把它作为习惯，是一个良好的操作规范。


### 条件过滤WHERE  

在SELECT语句中，数据根据WHERE子句中指定的搜索条件来进行过滤，在搜索条件中用来判断条件的有比较运算符与逻辑运算符，其中

比较运算符有：>,<,>=,<=,!=,<>,like,between and,in/not in

逻辑运算符有：&&(and),||(or),!(not)

当SQL执行到WHERE子句时，会先从磁盘中根据搜索条件进行逐条判断，如果成立则保存到内存中，否则跳过。


#### 注意：WHERE子句返回的结果只有0或者1（要么成立，要么不成立），其中0代表false，1代表true。

```sql
mysql> SELECT * FROM score WHERE id = 1; -- 查找id为1的学生信息
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 语文 | 90    |
|  1 | 数学 | 95    |
|  1 | 英语 | 98    |
+----+------+-------+
3 rows in set

mysql> SELECT * FROM score WHERE id = 1 OR id = 2; -- 查找id为1或者id为2的学生信息
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 语文 | 90    |
|  1 | 数学 | 95    |
|  1 | 英语 | 98    |
|  2 | 语文 | 92    |
|  2 | 数学 | 88    |
|  2 | 英语 | 90    |
+----+------+-------+
6 rows in set

mysql> SELECT * FROM score WHERE score BETWEEN 95 AND 98; -- 查找课程分数在95到98之间的学生信息
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 数学 | 95    |
|  1 | 英语 | 98    |
|  3 | 语文 | 96    |
|  3 | 英语 | 98    |
+----+------+-------+
4 rows in set
```


### 分组函数GROUP BY  

GROUP BY从语义上面来看意思是根据BY后面的字段名或者表达式进行分组，所谓的分组就是将SELECT出来的数据分成若干个组，相同的放一组），通常分组是为了做数据统计分析，所以常常配合聚合（统计）函数进行使用

常用的聚合（统计）函数有：

COUNT()：返回SELECT语句检索到的行中非NULL值的数目，若找不到匹配的行，则COUNT() 返回 0，COUNT(*)则包含非NULL值

SUM()： 统计每组数据的总数，表中列值为NULL的行不参与计算，若找不到匹配的行，则返回NULL

AVG()：统计每组数据的平均值，表中列值为NULL的行不参与计算，若找不到匹配的行，则返回 NULL

MAX()：统计每组中的最大值，如果统计的列中只有NULL值，那么返回NULL

MIN()：统计每组中的最小值，如果统计的列中只有NULL值，那么返回NULL


#### 聚合函数的特点：只有一个返回值

```sql
mysql> SELECT name, AVG(score), SUM(score) FROM score GROUP BY name; -- 统计各科的平均成绩与总成绩
+------+------------+------------+
| name | AVG(score) | SUM(score) |
+------+------------+------------+
| 数学 | 94.33333   | 283.0      |
| 英语 | 95.33333   | 286.0      |
| 语文 | 92.66667   | 278.0      |
+------+------------+------------+
3 rows in set
```

分组会根据分组的字段进行默认排序，这里的排序指的是对每个组的结果集这个整体进行排序，而不是分组中每一条记录，实际上分组后每组也就一条记录了。

现在有个需求，想要对上面的结果再进行一次汇总，那么可能会考虑到用联合查询，不过MySQL中提供了WITH ROOLUP关键字就能轻松完成这件事情

```sql
mysql> SELECT name, AVG(score), SUM(score) FROM score GROUP BY name WITH ROLLUP;
+------+------------+------------+
| name | AVG(score) | SUM(score) |
+------+------------+------------+
| 数学 | 94.33333   | 283.0      |
| 英语 | 95.33333   | 286.0      |
| 语文 | 92.66667   | 278.0      |
| NULL | 94.11111   | 847.0      |
+------+------------+------------+
4 rows in set
```

与GROUP BY相比，在查询的最后一行多了对平均成绩与总成绩的汇总。对单个维度的汇总并不能体现出ROLLUP的优势，下面对id与name进行汇总统计

```sql
mysql> SELECT id, name, AVG(score), SUM(score) FROM score GROUP BY id, name WITH ROLLUP;
+------+------+------------+------------+
| id   | name | AVG(score) | SUM(score) |
+------+------+------------+------------+
|    1 | 数学 | 95         | 95.0       |
|    1 | 英语 | 98         | 98.0       |
|    1 | 语文 | 90         | 90.0       |
|    1 | NULL | 94.33333   | 283.0      |
|    2 | 数学 | 88         | 88.0       |
|    2 | 英语 | 90         | 90.0       |
|    2 | 语文 | 92         | 92.0       |
|    2 | NULL | 90         | 270.0      |
|    3 | 数学 | 100        | 100.0      |
|    3 | 英语 | 98         | 98.0       |
|    3 | 语文 | 96         | 96.0       |
|    3 | NULL | 98         | 294.0      |
| NULL | NULL | 94.11111   | 847.0      |
+------+------+------------+------------+
13 rows in set
```

其中(NULL, NULL)与GROUP BY  name WITH ROLLUP类似，表示对最后数据的汇总

(id, NULL)表示对学生进行分组后的聚合结果，这里表示对每个学生的成绩进行汇总

(id, name)表示对学生与科目进行分组后的聚合结果，这里表示对每个学生的各科成绩进行汇总

MySQL 扩展了 GROUP BY的用途，因此你可以使用SELECT 列表中不出现在GROUP BY语句中的列或运算。例如

```sql
mysql> SELECT id, name, AVG(score), SUM(score) FROM score GROUP BY id;
+----+------+------------+------------+
| id | name | AVG(score) | SUM(score) |
+----+------+------------+------------+
|  1 | 语文 | 94.33333   | 283.0      |
|  2 | 语文 | 90         | 270.0      |
|  3 | 语文 | 98         | 294.0      |
+----+------+------------+------------+
3 rows in set
```

从上面的结果可以看出分组函数的特点 **`：返回值为该组中的第一条记录`** 

在标准SQL中，你必须将 name添加到 GROUP BY子句中。假如你从GROUP BY部分省略的列在该组中不是唯一的，那么不要使用这个功能！你会得到非预测性结果。例如根据学生查询最高成绩时所对应课程名称为 **`每组中第一条记录值`** ，这并不是我们想要的

```sql
mysql> SELECT id, name, AVG(score), MAX(score) FROM score GROUP BY id;
+----+------+------------+------------+
| id | name | AVG(score) | MAX(score) |
+----+------+------------+------------+
|  1 | 语文 | 94.33333   | 98         |
|  2 | 语文 | 90         | 92         |
|  3 | 语文 | 98         | 100        |
+----+------+------------+------------+
3 rows in set
```

如果需要在一行中显示每个学生的各科成绩，可以用GROUP_CONCAT函数，该函数通常配合GROUP BY使用，如果没有GROUP BY，将返回列中的所有值

```sql
mysql> SELECT id, GROUP_CONCAT(score) FROM score GROUP BY id;
+----+---------------------+
| id | GROUP_CONCAT(score) |
+----+---------------------+
|  1 | 90.0,95.0,98.0      |
|  2 | 92.0,88.0,90.0      |
|  3 | 96.0,100.0,98.0     |
+----+---------------------+
3 rows in set

mysql> SELECT id, GROUP_CONCAT(score) FROM score;
+----+-----------------------------------------------+
| id | GROUP_CONCAT(score)                           |
+----+-----------------------------------------------+
|  1 | 90.0,95.0,98.0,92.0,88.0,90.0,96.0,100.0,98.0 |
+----+-----------------------------------------------+
1 row in set
```


### 过滤分组HAVING  

HAVING是用来对分组后的数据进行数据筛选的，例如要查询平均成绩小于95的学生信息，使用having时，此时数据已经在内存中了。

```sql
mysql> SELECT id, AVG(score) FROM score GROUP BY id HAVING AVG(score) < 95;
+----+------------+
| id | AVG(score) |
+----+------------+
|  1 | 94.33333   |
|  2 | 90         |
+----+------------+
2 rows in set
```


### 排序ORDER BY  

根据某个字段进行升序（默认）或者降序排序，依赖校对集

```sql
mysql> SELECT * FROM score WHERE id = 1 ORDER BY score DESC; -- 查询学生1的成绩，并按照成绩由高到低进行排序
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 英语 | 98    |
|  1 | 数学 | 95    |
|  1 | 语文 | 90    |
+----+------+-------+
3 rows in set
```


### 数量限定LIMIT

两种使用方式

1、LIMIT row_count：row_count表示数量，如

```sql
mysql> SELECT * FROM score LIMIT 2; -- 查找列表前两条数据
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 语文 | 90    |
|  1 | 数学 | 95    |
+----+------+-------+
2 rows in set
```

2、LIMIT begin,offset：begin表示起始位置，offset表示数量

```sql
mysql> SELECT * FROM score LIMIT 2,3; -- 从第二条开始，取出三条数据，通常用于分页
+----+------+-------+
| id | name | score |
+----+------+-------+
|  1 | 英语 | 98    |
|  2 | 语文 | 92    |
|  2 | 数学 | 88    |
+----+------+-------+
3 rows in set
```



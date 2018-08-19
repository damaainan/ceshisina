## Mysql - JOIN详解

来源：[https://segmentfault.com/a/1190000015572505](https://segmentfault.com/a/1190000015572505)


## 0 索引


* **`JOIN`** 语句的执行顺序
* **`INNER/LEFT/RIGHT/FULL JOIN`** 的区别
* **`ON`** 和 **`WHERE`** 的区别


## 1 概述

一个完整的SQL语句中会被拆分成多个子句，子句的执行过程中会产生虚拟表(vt)，但是结果只返回最后一张虚拟表。从这个思路出发，我们试着理解一下JOIN查询的执行过程并解答一些常见的问题。
如果之前对不同JOIN的执行结果没有概念，可以结合[这篇文章][0]往下看
## 2 JOIN的执行顺序

以下是JOIN查询的通用结构

```
SELECT <row_list> 
  FROM <left_table> 
    <inner|left|right> JOIN <right_table> 
      ON <join condition> 
        WHERE <where_condition>
```

它的执行顺序如下 **`(SQL语句里第一个被执行的总是FROM子句)`** ：


* **`FROM`** :对左右两张表执行笛卡尔积，产生第一张表vt1。行数为n*m（n为左表的行数，m为右表的行数
* **`ON`** :根据ON的条件逐行筛选vt1，将结果插入vt2中
* **`JOIN`** :添加外部行，如果指定了 **` LEFT JOIN `** ( **` LEFT OUTER JOIN `** )，则先遍历一遍 **`左表`** 的每一行，其中不在vt2的行会被插入到vt2，该行的剩余字段将被填充为 **` NULL `** ，形成vt3；如果指定了 **` RIGHT JOIN `** 也是同理。但如果指定的是 **` INNER JOIN `** ，则不会添加外部行，上述插入过程被忽略，vt2=vt3（所以 **` INNER JOIN `** 的过滤条件放在 **` ON `** 或 **` WHERE `** 里 执行结果是没有区别的，下文会细说）
* **`WHERE`** :对vt3进行条件过滤，满足条件的行被输出到vt4
* **`SELECT`** :取出vt4的指定字段到vt5


下面用一个例子介绍一下上述联表的过程（这个例子不是个好的实践，只是为了说明join语法）
## 3 举例

创建一个用户信息表：

```sql
CREATE TABLE `user_info` (
  `userid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  UNIQUE `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```

再创建一个用户余额表：

```sql
CREATE TABLE `user_account` (
  `userid` int(11) NOT NULL,
  `money` bigint(20) NOT NULL,
 UNIQUE `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```

随便导入一些数据：

```sql
select * from user_info;
+--------+------+
| userid | name |
+--------+------+
|   1001 | x    |
|   1002 | y    |
|   1003 | z    |
|   1004 | a    |
|   1005 | b    |
|   1006 | c    |
|   1007 | d    |
|   1008 | e    |
+--------+------+
8 rows in set (0.00 sec)

select * from user_account;
+--------+-------+
| userid | money |
+--------+-------+
|   1001 |    22 |
|   1002 |    30 |
|   1003 |     8 |
|   1009 |    11 |
+--------+-------+
4 rows in set (0.00 sec)
```

一共8个用户有用户名，4个用户的账户有余额。
 **`取出userid为1003的用户姓名和余额，SQL如下`** ：

```sql
SELECT i.name, a.money 
  FROM user_info as i 
    LEFT JOIN user_account as a 
      ON i.userid = a.userid 
        WHERE a.userid = 1003;
```
#### 第一步：执行FROM子句对两张表进行笛卡尔积操作

笛卡尔积操作后会返回两张表中所有行的组合，左表user_info有8行，右表user_account有4行，生成的虚拟表vt1就是8*4=32行：

```sql
SELECT * FROM user_info as i LEFT JOIN user_account as a ON 1;
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1001 |    22 |
|   1003 | z    |   1001 |    22 |
|   1004 | a    |   1001 |    22 |
|   1005 | b    |   1001 |    22 |
|   1006 | c    |   1001 |    22 |
|   1007 | d    |   1001 |    22 |
|   1008 | e    |   1001 |    22 |
|   1001 | x    |   1002 |    30 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1002 |    30 |
|   1004 | a    |   1002 |    30 |
|   1005 | b    |   1002 |    30 |
|   1006 | c    |   1002 |    30 |
|   1007 | d    |   1002 |    30 |
|   1008 | e    |   1002 |    30 |
|   1001 | x    |   1003 |     8 |
|   1002 | y    |   1003 |     8 |
|   1003 | z    |   1003 |     8 |
|   1004 | a    |   1003 |     8 |
|   1005 | b    |   1003 |     8 |
|   1006 | c    |   1003 |     8 |
|   1007 | d    |   1003 |     8 |
|   1008 | e    |   1003 |     8 |
|   1001 | x    |   1009 |    11 |
|   1002 | y    |   1009 |    11 |
|   1003 | z    |   1009 |    11 |
|   1004 | a    |   1009 |    11 |
|   1005 | b    |   1009 |    11 |
|   1006 | c    |   1009 |    11 |
|   1007 | d    |   1009 |    11 |
|   1008 | e    |   1009 |    11 |
+--------+------+--------+-------+
32 rows in set (0.00 sec)
```
#### 第二步：执行ON子句过滤掉不满足条件的行

ON i.userid = a.userid  过滤之后vt2如下：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
```
#### 第三步：JOIN 添加外部行
 **`LEFT JOIN`** 会将左表未出现在vt2的行插入进vt2，每一行的剩余字段将被填充为NULL， **`RIGHT JOIN`** 同理
本例中用的是 **`LEFT JOIN`** ，所以会将左表 **`user_info`** 剩下的行都添上 生成表vt3：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
|   1004 | a    |   NULL |  NULL |
|   1005 | b    |   NULL |  NULL |
|   1006 | c    |   NULL |  NULL |
|   1007 | d    |   NULL |  NULL |
|   1008 | e    |   NULL |  NULL |
+--------+------+--------+-------+
```
#### 第四步：WHERE条件过滤

WHERE a.userid = 1003  生成表vt4：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
```
#### 第五步：SELECT

SELECT i.name, a.money  生成vt5：

```sql
+------+-------+
| name | money |
+------+-------+
| z    |     8 |
+------+-------+
```

虚拟表vt5作为最终结果返回给客户端

介绍完联表的过程之后，我们看看常用 **`JOIN`** 的区别
## 4 INNER/LEFT/RIGHT/FULL JOIN的区别


* **`INNER JOIN...ON...`** : 返回 左右表互相匹配的所有行（因为只执行上文的第二步ON过滤，不执行第三步 添加外部行）
* **`LEFT JOIN...ON...`** : 返回左表的所有行，若某些行在右表里没有相对应的匹配行，则将右表的列在新表中置为NULL
* **`RIGHT JOIN...ON...`** : 返回右表的所有行，若某些行在左表里没有相对应的匹配行，则将左表的列在新表中置为NULL


#### INNER JOIN

拿上文的第三步 **`添加外部行`** 来举例，若 **`LEFT JOIN`** 替换成 **`INNER JOIN`** ，则会跳过这一步，生成的表vt3与vt2一模一样：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
```
#### RIGHT JOIN

若 **`LEFT JOIN`** 替换成 **`RIGHT JOIN`** ，则生成的表vt3如下：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
|   NULL | NULL |   1009 |    11 |
+--------+------+--------+-------+
```
 **`因为user_account（右表）里存在userid=1009这一行，而user_info（左表）里却找不到这一行的记录，所以会在第三步插入以下一行：`** 

```sql
|   NULL | NULL |   1009 |    11 |
```
#### FULL JOIN

[上文引用的文章][1]中提到了标准SQL定义的 **`FULL JOIN`** ，这在mysql里是不支持的，不过我们可以通过 **`LEFT JOIN + UNION + RIGHT JOIN`**  来实现 **`FULL JOIN`** ：

```sql
SELECT * 
  FROM user_info as i 
    RIGHT JOIN user_account as a 
      ON a.userid=i.userid
union 
SELECT * 
  FROM user_info as i 
    LEFT JOIN user_account as a 
      ON a.userid=i.userid;
```

他会返回如下结果：

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
|   NULL | NULL |   1009 |    11 |
|   1004 | a    |   NULL |  NULL |
|   1005 | b    |   NULL |  NULL |
|   1006 | c    |   NULL |  NULL |
|   1007 | d    |   NULL |  NULL |
|   1008 | e    |   NULL |  NULL |
+--------+------+--------+-------+
```

ps：其实我们从语义上就能看出 **`LEFT JOIN`** 和 **`RIGHT JOIN`** 没什么差别，两者的结果差异取决于左右表的放置顺序，以下内容摘自mysql官方文档：

RIGHT JOIN works analogously to LEFT JOIN. To keep code portable across databases, it is recommended that you use LEFT JOIN instead of RIGHT JOIN.
所以当你纠结使用LEFT JOIN还是RIGHT JOIN时，尽可能只使用LEFT JOIN吧
## 5 ON和WHERE的区别

上文把JOIN的执行顺序了解清楚之后，ON和WHERE的区别也就很好理解了。
举例说明:

```sql
SELECT * 
  FROM user_info as i
    LEFT JOIN user_account as a
      ON i.userid = a.userid and i.userid = 1003;
```

```sql
SELECT * 
  FROM user_info as i
    LEFT JOIN user_account as a
      ON i.userid = a.userid where i.userid = 1003;
```

第一种情况 **`LEFT JOIN`** 在执行完第二步ON子句后，筛选出满足 **`i.userid = a.userid and i.userid = 1003`** 的行，生成表vt2，然后执行第三步JOIN子句，将外部行添加进虚拟表生成vt3即最终结果：

```sql
vt2:
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
vt3:
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   NULL |  NULL |
|   1002 | y    |   NULL |  NULL |
|   1003 | z    |   1003 |     8 |
|   1004 | a    |   NULL |  NULL |
|   1005 | b    |   NULL |  NULL |
|   1006 | c    |   NULL |  NULL |
|   1007 | d    |   NULL |  NULL |
|   1008 | e    |   NULL |  NULL |
+--------+------+--------+-------+
```

而第二种情况 **`LEFT JOIN`** 在执行完第二步ON子句后，筛选出满足 **`i.userid = a.userid`** 的行，生成表vt2；再执行第三步JOIN子句添加外部行生成表vt3；然后执行第四步WHERE子句，再对vt3表进行过滤生成vt4，得的最终结果：

```sql
vt2:
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
vt3:
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1001 | x    |   1001 |    22 |
|   1002 | y    |   1002 |    30 |
|   1003 | z    |   1003 |     8 |
|   1004 | a    |   NULL |  NULL |
|   1005 | b    |   NULL |  NULL |
|   1006 | c    |   NULL |  NULL |
|   1007 | d    |   NULL |  NULL |
|   1008 | e    |   NULL |  NULL |
+--------+------+--------+-------+
vt4:
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
```

如果将上例的 **`LEFT JOIN`** 替换成 **`INNER JOIN`** ，不论将条件过滤放到 **`ON`** 还是 **`WHERE`** 里，结果都是一样的，因为 **`INNER JOIN不会执行第三步添加外部行`** 

```sql
SELECT * 
  FROM user_info as i
    INNER JOIN user_account as a
      ON i.userid = a.userid and i.userid = 1003;
```

```sql
SELECT * 
  FROM user_info as i
    INNER JOIN user_account as a
      ON i.userid = a.userid where i.userid = 1003;
```

返回结果都是:

```sql
+--------+------+--------+-------+
| userid | name | userid | money |
+--------+------+--------+-------+
|   1003 | z    |   1003 |     8 |
+--------+------+--------+-------+
```
## 参考资料

《MySQL技术内幕：SQL编程》
[SQL Joins - W3Schools][2]
[sql - What is the difference between “INNER JOIN” and “OUTER JOIN”?][3]
[MySQL :: MySQL 8.0 Reference Manual :: 13.2.10.2 JOIN Syntax][4]
[Visual Representation of SQL Joins][5]
[Join (SQL) - Wikipedia][6])

[0]: https://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins
[1]: https://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins
[2]: https://www.w3schools.com/sql/sql_join.asp
[3]: https://stackoverflow.com/questions/38549/what-is-the-difference-between-inner-join-and-outer-join
[4]: https://dev.mysql.com/doc/en/join.html
[5]: https://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins
[6]: https://en.wikipedia.org/wiki/Join_(SQL
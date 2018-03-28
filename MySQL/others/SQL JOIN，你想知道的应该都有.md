## SQL JOIN，你想知道的应该都有

来源：[http://www.cnblogs.com/xufeiyang/p/5818571.html](http://www.cnblogs.com/xufeiyang/p/5818571.html)

时间 2018-03-26 16:29:00

 
## 介绍  
 
这是一篇阐述SQL JOINs的文章。
 
## 背景  
 
我是个不喜欢抽象的人，一图胜千言。我在网上查找了所有的关于SQL JOIN的解释，但是没有找到一篇能用图像形象描述的。
 
有些是有图片的但是他们没有覆盖所有JOIN的例子，有些介绍实在简单空白得不能看。所以我决定写个自己的文章来介绍SQL JOINs.
 
## 详细说明  
 
接下来我将讨论七种你可以从两个关联表中获取数据的方法， 排除了交叉JOIN和自JOIN的情况。 七个JOINs的例子如下：
 

* INNER JOIN （内连接）   
* LEFT JOIN （左连接）   
* RIGHT JOIN （右连接）   
* OUTER JOIN （外连接）   
* LEFT JOIN EXCLUDING INNER JOIN （左连接排除内连接结果）   
* RIGHT JOIN EXCLUDING INNER JOIN （右连接排除内连接结果）   
* OUTER JOIN EXCLUDING INNER JOIN （外连接排除内连接结果）   
 

为了这个文章更好的描述，我把5，6，7当作LEFT EXCLUDING INNER JOIN， RIGHT EXCLUDING INNER JOIN，OUTER EXCLUDING INNER JOIN来特别说明。
 
有些人可能有不同意见： 5，6，7不是真正的两个表的JOIN； 但是为了方便理解，我仍然把这些作为JOINs， 因为你有可能会在每个查询中使用到这些 JOIN （排除一些有WHERE条件的记录）。
 
## INNER JOIN （内连接）  
 
![][0]
这是最简单、最容易理解、最常用的JOIN方式。 内连接查询返回表A和表B中所有匹配行的结果。 SQL样例如下：
 
```sql

SELECT <select_list> 
FROM Table_A A
INNER JOIN Table_B B
ON A.Key = B.Key

```
 
## LEFT JOIN （左连接）  
 
![][1]
LFET JOIN查询返回所有表A中的记录， 不管是否有匹配记录在表B中。它会返回所有表B中的匹配记录 （没有匹配的当然会标记成null了）。 SQL样例如下：
 
```sql

SELECT <select_list>
FROM Table_A A
LEFT JOIN Table_B B
ON A.Key = B.Key

```
 
RIGHT JOIN （右连接）
 
![][2]
和LEFT JOIN相反。 RIGHT JOIN查询会返回所有表B中的记录，不管是否有匹配记录在表A中。它会返回所有表A中的匹配记录（没有匹配的当然会标记成null了）。 SQL样例如下：
 
```sql

SELECT <select_list>
FROM Table_A A
RIGHT JOIN Table_B B
ON A.Key = B.Key

```
 
OUTER JOIN （外连接）
 
![][3]
 
OUTER JOIN也可以当作是FULL OUTER JOIN 或者FULL JOIN。它会返回两个表中所有行，左表A匹配右表B，右表B也匹配左表A （没有匹配的就显示null了）。
 
OUTER JOIN一般写成下面样子：
 
```sql

SELECT <select_list>
FROM Table_A A
FULL OUTER JOIN Table_B B
ON A.Key = B.Key

```
 
## **`LEFT Excluding JOIN  `**    
 
![][4]
它会返回表A中所有不在表B中的行，一般写成：
 
```sql

SELECT <select_list> 
FROM Table_A A
LEFT JOIN Table_B B
ON A.Key = B.Key
WHERE B.Key IS NULL

```
 
#### RIGHT Excluding JOIN
 
![][5]
与上面的相反，它会返回表B中所有不在表A中的行，SQL样例如下：
 
```sql

SELECT <select_list>
FROM Table_A A
RIGHT JOIN Table_B B
ON A.Key = B.Key
WHERE A.Key IS NULL

```
 
## OUTER Excluding JOIN  
 
![][6]
 
Outer Excluding JOIN 会返回所有表A和表B中没有匹配的行。我还没有遇到要用到这种情况的，但是其他的JOIN，用的比较频繁。 SQL样例如下：

```sql
SELECT <select_list>
 
FROM Table_A A
 
FULL OUTER JOIN Table_B B
 
ON A.Key = B.Key
 
WHERE A.Key IS NULL OR B.Key IS NULL
```

## 例子  
 
假设我们有两个表：表A和表B。表中数据如下所示：
 
```sql

TABLE_A
PK Value
---- ----------
1 FOX
2 COP
3 TAXI
6 WASHINGTON
7 DELL
5 ARIZONA
4 LINCOLN
10 LUCENT

```
 
```sql

TABLE_B

PK Value
---- ----------
1 TROT
2 CAR
3 CAB
6 MONUMENT
7 PC
8 MICROSOFT
9 APPLE
11 SCOTCH

```
 
这七个JOIN的结果分别如下：
 
```sql

-- INNER JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
INNER JOIN Table_B B
ON A.PK = B.PK

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
1 FOX TROT 1
2 COP CAR 2
3 TAXI CAB 3
6 WASHINGTON MONUMENT 6
7 DELL PC 7

(5 row(s) affected)

```
 
```sql

-- LEFT JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
LEFT JOIN Table_B B
ON A.PK = B.PK

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
1 FOX TROT 1
2 COP CAR 2
3 TAXI CAB 3
4 LINCOLN NULL NULL
5 ARIZONA NULL NULL
6 WASHINGTON MONUMENT 6
7 DELL PC 7
10 LUCENT NULL NULL

(8 row(s) affected)

```
 
```sql

-- RIGHT JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
RIGHT JOIN Table_B B
ON A.PK = B.PK

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
1 FOX TROT 1
2 COP CAR 2
3 TAXI CAB 3
6 WASHINGTON MONUMENT 6
7 DELL PC 7
NULL NULL MICROSOFT 8
NULL NULL APPLE 9
NULL NULL SCOTCH 11

(8 row(s) affected)

```
 
```sql

-- OUTER JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
FULL OUTER JOIN Table_B B
ON A.PK = B.PK

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
1 FOX TROT 1
2 COP CAR 2
3 TAXI CAB 3
6 WASHINGTON MONUMENT 6
7 DELL PC 7
NULL NULL MICROSOFT 8
NULL NULL APPLE 9
NULL NULL SCOTCH 11
5 ARIZONA NULL NULL
4 LINCOLN NULL NULL
10 LUCENT NULL NULL

(11 row(s) affected)

```
 
注意：OUTER JOIN中，inner join的行会先返回，接着是右连接了的行，最后是左连接了的行。 （至少 Microsoft SQL Server是这样的； 前提是所有的join都没有使用order by排序过）
 
```sql

-- LEFT EXCLUDING JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
LEFT JOIN Table_B B
ON A.PK = B.PK
WHERE B.PK IS NULL

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
4 LINCOLN NULL NULL
5 ARIZONA NULL NULL
10 LUCENT NULL NULL
(3 row(s) affected)

```
 
```sql

-- RIGHT EXCLUDING JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
RIGHT JOIN Table_B B
ON A.PK = B.PK
WHERE A.PK IS NULL

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
NULL NULL MICROSOFT 8
NULL NULL APPLE 9
NULL NULL SCOTCH 11

(3 row(s) affected)

```
 
```sql

-- OUTER EXCLUDING JOIN
SELECT A.PK AS A_PK, A.Value AS A_Value,
B.Value AS B_Value, B.PK AS B_PK
FROM Table_A A
FULL OUTER JOIN Table_B B
ON A.PK = B.PK
WHERE A.PK IS NULL
OR B.PK IS NULL

A_PK A_Value B_Value B_PK
---- ---------- ---------- ----
NULL NULL MICROSOFT 8
NULL NULL APPLE 9
NULL NULL SCOTCH 11
5 ARIZONA NULL NULL
4 LINCOLN NULL NULL
10 LUCENT NULL NULL

(6 row(s) affected)

```
 
你可以参考Wikipedia以查阅更多信息: [http://en.wikipedia.org/wiki/Sql_join][8]
 
我创建了一个简化总结图表（如下），经常用到SQL的话，可以打印下来以作参考。 右击下载图片就行了...
 
![][7]
 
## 版权  
 
本文翻译自： [http://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins][9]
 
这篇文章和相关的代码及图片采用的授权是： [The Code Project Open License (CPOL)][10]
 


[8]: http://en.wikipedia.org/wiki/Sql_join
[9]: http://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins
[10]: https://www.codeproject.com/info/cpol10.aspx
[0]: ./img/ayAZVbf.png 
[1]: ./img/Yfqei2n.png 
[2]: ./img/y6rEre3.png 
[3]: ./img/QfuEJfY.png 
[4]: ./img/qu6r2yU.png 
[5]: ./img/EbQfiij.png 
[6]: ./img/VRnEFvN.png 
[7]: ./img/vMRjyaE.png 
# MySQL实战技巧-1：Join的使用技巧和优化

 时间 2018-01-29 15:21:42  

原文[http://www.jianshu.com/p/6864abb4d885][1]


join用于多表中字段之间的联系，在数据库的 **DML** (数据操作语言，即各种增删改查操作）中有着重要的作用。 

合理使用Join语句优化SQL有利于：

1. 增加数据库的处理效率，减少响应时间；
1. 减少数据库服务器负载，增加服务器稳定性；
1. 减少服务器通讯的网络流量；

## 1. Join的分类：

* 内连接 Inner Join
* 全外连接 FULL Outer Join
* 左外连接 Left Outer Join
* 右外连接 Right Outer Join
* 交叉连接 Cross Join

![][3]

连接的分类

每种连接的区别作为基础内容，这里就不再展开说明，请读者自己参看其他文章了解，比如 [Mysql Join语法以及性能优化][4]

需要说明的是，目前MySQL不支持全连接，需要使用 **UNION** 关键字进行联合。 

Union：对两个结果集进行并集操作，不包括重复行，同时进行默认规则的排序； 

Union All：对两个结果集进行并集操作，包括重复行，不进行排序； 

## 3. Join使用的注意事项

下面进行本文重点，Join的使用注意事项和技巧，首先给出要使用的表结构：

```sql
    -- auto-generated definition
    CREATE TABLE customer
    (
      id        INT AUTO_INCREMENT
        PRIMARY KEY,
      cust_name VARCHAR(50)  NOT NULL CHARSET utf8,
      over      VARCHAR(100) NULL CHARSET utf8,
      CONSTRAINT customer_id_uindex
      UNIQUE (id)
    )
      ENGINE = InnoDB;
      
    -- auto-generated definition
    CREATE TABLE faculty
    (
      id        INT AUTO_INCREMENT
        PRIMARY KEY,
      user_name VARCHAR(50)  NOT NULL CHARSET utf8,
      over      VARCHAR(200) NULL CHARSET utf8,
      CONSTRAINT faculty_id_uindex
      UNIQUE (id)
    )
      ENGINE = InnoDB;
```
![][5]

customer表中数据，代表客户的信息

![][6]

faculty表中的数据，代表职工的信息

### 2.1 显式连接 VS 隐式连接

所谓显式连接，即如上显示使用 **inner Join** 关键字连接两个表， 

```sql
    select * from
    table a inner join table b
    on a.id = b.id;
```
而隐式连接即不显示使用 **inner Join** 关键字，如： 

```sql
    select a.*, b.*
    from table a, table b
    where a.id = b.id;
```
二者在功能上没有差别，实现的性能上也几乎一样。只不过隐式连接是 **SQL92** 中的标准内容，而在 **SQL99** 中显式连接为标准，虽然很多人还在用隐私连接，但是它已经从标准中被移除。从使用的角度来说，还是推荐使用显示连接，这样可以更清楚的显示出多个表之间的连接关系和连接依赖的属性。 

### 2.2 On VS Where

ON 条件（“A LEFT JOIN B ON 条件表达式”中的ON）用来决定如何从 B 表中检索数据行。如果 B 表中没有任何一行数据匹配 ON 的条件,将会额外生成一行所有列为 NULL 的数据,在匹配阶段 WHERE 子句的条件都不会被使用。仅在匹配阶段完成以后，WHERE 子句条件才会被使用。ON将从匹配阶段产生的数据中检索过滤。

所以我们要注意：在使用Left (right) join的时候，一定要在先给出尽可能多的匹配满足条件，减少Where的执行。尽可能满足ON的条件，而少用Where的条件，从执行性能来看也更加高效。

## 3 Join的技巧

### 3.1 如何更新使用过虑条件中包括自身的表

假设现在要将是职工中的消费者的“over”属性设置为"优惠"，直接如下更新会报错：

![][7]

1516605305289.png

这是由于Mysql不支持这种查询后更新（这其实是标准SQL中一项要求，Oracle、SQL Server中都是可以的）。

为了解决这种更新的过虑条件中包含要更新的表的情况，可以把带过滤条件的查询结果当做一个新表，在新表上，执行更新操作。

```sql
    UPDATE (faculty f INNER JOIN customer c
        on user_name=cust_name)
    set c.over = "优惠";
```
![][8]

更新成功

### 3.2 Join优化子查询

嵌套的子查询是比较低效地，因为每一条记录都要进行匹配，如果记录长度比较大的话，那么我们的查询就有可能非常的耗时。我们应该尽量避免使用子查询，而用表连接。如下面的这个子查询就可以转化为等价的连接查询

```sql
    SELECT user_name, over ,(SELECT over FROM customer c where user_name=cust_name) as over2
    from faculty f;

    SELECT user_name, f.over , c.over as over2
    from faculty f
      LEFT JOIN customer c ON cust_name=user_name;
```
### 3.3 使用Join优化聚合查询

为了说明这个问题 ，我们在添加一个工作量的表，记录每个职工每天的工作量

```sql
    -- auto-generated definition
    CREATE TABLE tasks
    (
      id        SMALLINT(5) UNSIGNED AUTO_INCREMENT
        PRIMARY KEY,
      facult_id SMALLINT(5) UNSIGNED                NULL,
      timestr   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
      workload  SMALLINT(5) UNSIGNED                NULL
    )
      ENGINE = InnoDB
      CHARSET = utf8;
```
![][9]

tasks记录职工的工作量

比如我们想查询每个员工工作量最多是哪一天，通过子查询可以这样实现：

```sql
    select a.user_name ,b.timestr,b.workload
    from faculty a
      join tasks b
        on a.id = b.facult_id
    where b.workload = (
      select max(c.workload)
      from tasks c
      where c.facult_id = b.facult_id)
```
![][10]

查询结果

使用表连接优化之后：

```sql
    SELECT user_name, t.timestr, t.workload
    FROM faculty f
      JOIN tasks t ON f.id = t.facult_id
      JOIN tasks t2 ON t2.facult_id = t.facult_id
    GROUP BY user_name,t.timestr，t.workload
    HAVING t.workload = max(t2.workload);
```
这里额外的再连接了一个task表中内容，在这个“额外表”中通过聚合计算出工作量的最大值，然后再过虑（HAVING）出工作量最大的日期。

因为聚合函数通过作用于一组数据而只返回一个单个值，因此，在SELECT语句中出现的元素要么为一个聚合函数的输入值，要么为GROUP BY语句的参数，否则会出错。

但是mysql的 **group by** 做过 **扩展** 了，select之后的列允许其不出现在group by之后，MySQL在执行这类查询语句时，它会默认理解为，没写到GROUP BY子句的列，其列值是唯一的，如果GROUP BY省略的列值其实并不唯一，将会默认取第一个获得的值，这样就会指代不明，那么最好不要使用这项功能。 

### 3.4 如何实现分组查询

要获取每个员工完成工作量最多的两天。这个也可以通过Join来完成。

```sql
    select d.user_name,c.timestr,workload
    FROM (
           select facult_id,timestr,workload,
             (SELECT COUNT(*)
              FROM tasks b
              WHERE b.facult_id=a.facult_id AND a.workload<=b.workload) AS cnt
           FROM tasks a
           GROUP BY facult_id,timestr,workload) c
      JOIN faculty d ON c.facult_id=d.id
    WHERE cnt <= 2;
```
其中，内部的查询结果 **cnt** 表示对于tasks表中某个给定记录，相同员工的工作里记录比其大的数量有多少。 

内部查询的结果如下：

```sql
    select facult_id,timestr,workload,
             (SELECT COUNT(*)
              FROM tasks b
              WHERE b.facult_id=a.facult_id AND a.workload<=b.workload) AS cnt
           FROM tasks a
           GROUP BY facult_id,timestr,workload;
```
![][11]

内部查询的结果

即每个工作量记录信息和同一员工的工作量排名。

cnt <= 2 就代表该记录是某位员工的工作量最大两天之一。 

![][12]

每个员工完成工作量最多的两天

## 4. join的实现原理

join的实现是采用**`Nested Loop Join算法`**，就是通过驱动表的结果集作为循环基础数据，然后一条一条的通过该结果集中的数据作为过滤条件到下一个表中查询数据，然后合并结果。如果有多个join，则将前面的结果集作为循环数据，再一次作为循环条件到后一个表中查询数据。

比如我们以如下SQL语句为例：

```sql
    EXPLAIN SELECT C.id, cust_name,T.workload
    FROM customer C
      INNER JOIN faculty F
        ON C.cust_name = F.user_name
      INNER JOIN tasks T
        ON T.facult_id = F.id ;
```
![][13]

EXPLAIN 连接查询

从 **explain** 的输出看出，MySQL选择 **C** 作为驱动表， 

首先通过 **Using Where** 和 **Using join buffer** 来匹配 **F** 中的内容，然后在其结果的基础上通过主键的索引 **PRIMARY,faculty_id_uindex** 匹配到 **T** 表中的内容。 

其过程类似于三次次嵌套的循环。

需要说明的是， **C** 作为驱动表，通过 **Using Where** 和 **Using join buffer** 来匹配 **F** ，是因为 C.cust_name ，F.user_name 都没有加索引，要获取具体的内容只能通过对全表的数据进行where过滤才能获取，而 _Using join buffer_ 是指使用到了Cache(只有当join类型为 **ALL** ，`index`，`rang`或者是`index_merge`的时候才会使用`join buffer`)，记录已经查询的结果，提高效率。 

而对于 **T** 和 **F** 之间通过T的主键T.id连接，所以join类型为 `eq_ref` ，也不用使用Using join buffer。 

## 5. join语句的优化原则

1. **用小结果集驱动大结果集** ，将筛选结果小的表首先连接，再去连接结果集比较大的表，尽量减少join语句中的Nested Loop的循环总次数；
1. **优先优化Nested Loop的内层循环** （也就是最外层的Join连接），因为内层循环是循环中执行次数最多的，每次循环提升很小的性能都能在整个循环中提升很大的性能；
1. 对被驱动表的join字段上建立 **索引** ；
1. 当被驱动表的join字段上无法建立索引的时候，设置 **足够的Join Buffer Size** 。

## 参考文章

1. [MySQL数据库对GROUP BY子句的功能扩展(1)][14]
1. [SQL中GROUP BY语句与HAVING语句的使用][15]
1. [Mysql Join语法以及性能优化][4]
1. [mysql join的实现原理及优化思路][16]
1. [Explicit vs implicit SQL joins][17]
1. [Deprecation of "Old Style" JOIN Syntax: Only A Partial Thing][18]

[1]: http://www.jianshu.com/p/6864abb4d885
[3]: ./img/Jr2yaeZ.png
[4]: https://link.jianshu.com?t=https%3A%2F%2Fwww.cnblogs.com%2Fblueoverflow%2Fp%2F4714470.html
[5]: ./img/IvA3Uny.png
[6]: ./img/qIjq6ve.png
[7]: ./img/VjmAvai.png
[8]: ./img/3M7niaB.png
[9]: ./img/uQNNJzb.png
[10]: ./img/rAbU7jY.png
[11]: ./img/22qAbyR.png
[12]: ./img/NzuUnin.png
[13]: ./img/EzeUrmr.png
[14]: https://link.jianshu.com?t=http%3A%2F%2Fblog.itpub.net%2F7607759%2Fviewspace-692946%2F
[15]: https://link.jianshu.com?t=ttps%3A%2F%2Fwww.cnblogs.com%2F8335IT%2Fp%2F5850531.html
[16]: https://link.jianshu.com?t=http%3A%2F%2Fblog.csdn.net%2Ftonyxf121%2Farticle%2Fdetails%2F7796657
[17]: https://link.jianshu.com?t=https%3A%2F%2Fstackoverflow.com%2Fquestions%2F44917%2Fexplicit-vs-implicit-sql-joins
[18]: https://link.jianshu.com?t=https%3A%2F%2Fblogs.technet.microsoft.com%2Fwardpond%2F2008%2F09%2F13%2Fdeprecation-of-old-style-join-syntax-only-a-partial-thing%2F
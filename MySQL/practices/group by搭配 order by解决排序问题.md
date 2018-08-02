## group by搭配 order by解决排序问题

来源：[http://www.cnblogs.com/cposture/p/9391273.html](http://www.cnblogs.com/cposture/p/9391273.html)

时间 2018-07-30 16:30:00



## 问题

| Ftravel_id | Facct_no | Froute_code | Fmodify_time | |
|-|-|-|-|-|
| 41010020180725102219102000010452 | 1359c027b0a15266418643239300118 | 4101001701E214 | 2018-07-25 10:22:19 | |
| 41010020180725102749102000010453 | 1359c027b0a15266418643239300118 | 4101001701E214 | 2018-07-25 10:27:49 | |
| 41010020180725103059102000010455 | 1359c027b0a15266418643239300119 | 4101001701E214 | 2018-07-25 10:30:59 | |
  

这里的问题是如何得到指定时间范围内，指定 Facct_no 用户的 limit 个行程信息，返回行程序列按时间排序，且序列中每个 Froute_code 值都是唯一的，如果重复则取最新的一个。

因为 distinct 和 group by 都可以用来去重，这里总结下：



* group by & distinct 的使用和区别
* 去重时排序
  


## 去重 group by & distinct


## group by 语句

GROUP BY 语句根据一个或多个列对结果集进行分组。在分组的列上我们可以使用 COUNT, SUM, AVG,等函数。

```sql
SELECT column_name, aggregate_function(column_name)
FROM table_name
WHERE column_name operator value
GROUP BY column_name
```

在 MySQL 中，不加聚合函数的情况下，返回的结果是分组后每组结果集中的第一行；选择的字段不必在 GROUP BY 中存在。

```sql
SELECT Ftravel_id,Facct_no FROM db_ccm_cx.t_ride_record_201807
GROUP BY Froute_code
```

对于标准 SQL 而言，GROUP BY 一定要结合聚合函数使用，而且选择的字段除了聚合函数外，还必须在 GROUP BY 中出现。如以下 SQL 语句：

```sql
SELECT Froute_code,count(Facct_no) FROM db_ccm_cx.t_ride_record_201807
GROUP BY Froute_code
```

如果在SELECT语句中使用GROUP BY子句，而不使用聚合函数，则GROUP BY子句的行为与DISTINCT子句类似。

```sql
SELECT Froute_code FROM db_ccm_cx.t_ride_record_201807
GROUP BY Froute_code
```

GROUP BY X意思是将所有具有相同X字段值的记录放到一个分组里；

多列情况下，GROUP BY X, Y意思是将所有具有相同X字段值和Y字段值的记录放到一个分组里，也就是其中一个值不一样都会影响分组结果。

这里利用 group by 进行去重的原理是，不加聚合函数的情况下，返回的结果是分组后每组结果集中的第一行，这里是根据要去重的列进行分组的；比如按照 Froute_code 进行去重，则 SQL 是：

```sql
SELECT * FROM db_ccm_cx.t_ride_record_201807
GROUP BY Froute_code
```

返回的结果是分组后每组结果集中的第一行，导致重复 Froute_code 的行程信息可能会返回 Fmodify_time 较老的一条，我们是想返回重复 Froute_code 中最近的一条，Mysql 的 GROUP BY 没有排序功能。如果这样子呢：

```sql
SELECT * FROM db_ccm_cx.t_ride_record_201807
GROUP BY Froute_code ORDER BY Fmodify_time
```

增加 ORDER BY Fmodify_time，也没法实现去除的较老的，返回较新的 Froute_code 行程信息。因为 GROUP BY 会比 ORDER BY 先执行，没有办法在 GROUP BY 的各个 group 中进行针对某一列的排序。

只要在 GROUP BY 前将顺序调整好，把你希望的数据排在最前面，那么 GROUP BY 时就能顺利取到这个数据。故解决方法就是先进行你想要的排序，然后在此排序后的结果集的基础上，进行 GROUP BY 操作。比如下面 SQL：

```sql
SELECT * 
FROM
(SELECT * FROM db_ccm_cx.t_ride_record_201807 ORDER BY Fmodify_time ) temp_table
GROUP BY Froute_code
```


## distinct

关键词 DISTINCT 用于返回唯一不同的值。语法是：`SELECT DISTINCT 列名称 FROM 表名称`，比如以下 SQL：

```sql
SELECT DISTINCT Company FROM Orders
```

多列情况下，distinct 和 group by 一样，也是同时作用在了多个字段，多个字段组合一起不同的都会作为返回结果。比如以下 SQL：

```sql
SELECT DISTINCT Company,OrderPrice  FROM Orders
```

如果想返回多列，网上有一种错误的说法(见https://www.cnblogs.com/peijie-tech/p/3457777.html)：因为 DISTINCT 单独使用如果不放在前面会报错，与其他函数使用时候，没有位置限制，所以可以使用下面 SQL，这样的返回结果多了一列无用的count数据：

```sql
SELECT Company, OrderPrice , COUNT(DISTINCT Company) FROM Orders
```

在 MYSQL 5.6 上是不行的，始终只返回 1列；

因此如果想返回多列，最好使用 group by 代替。

```sql
SELECT Company, OrderPrice  FROM Orders  GROUP BY Company
```


如果列具有NULL值，并且对该列使用DISTINCT子句，MySQL将保留一个NULL值，并删除其它的NULL值，因为DISTINCT子句将所有NULL值视为相同的值。

可以使用具有聚合函数(例如SUM，AVG和COUNT)的DISTINCT子句中，在MySQL将聚合函数应用于结果集之前删除重复的行。

```sql
SELECT COUNT(DISTINCT Company) FROM Orders
```

如果要将DISTINCT子句与LIMIT子句一起使用，MySQL会在查找LIMIT子句中指定的唯一行数时立即停止搜索。

```sql
SELECT DISTINCT state FROM customers WHERE state IS NOT NULL LIMIT 3;
```


## 参考链接

  
[https://segmentfault.com/a/1190000006821331][0]

[https://www.cnblogs.com/peijie-tech/p/3457777.html][1]

[https://www.yiibai.com/mysql/distinct.html][2]

[https://blog.csdn.net/PIGer920/article/details/7006420][3]

[https://dev.mysql.com/doc/refman/8.0/en/group-by-optimization.html][4]
  



[0]: https://segmentfault.com/a/1190000006821331
[1]: https://www.cnblogs.com/peijie-tech/p/3457777.html
[2]: https://www.yiibai.com/mysql/distinct.html
[3]: https://blog.csdn.net/PIGer920/article/details/7006420
[4]: https://dev.mysql.com/doc/refman/8.0/en/group-by-optimization.html
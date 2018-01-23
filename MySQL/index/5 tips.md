# Mysql 索引你了解多少？

 时间 2018-01-22 15:53:45 

原文[https://sdk.cn/news/8028][1]


## 前言

Mysql 的索引是我们常用的，但实际了解多少呢？下面通过几个案例小问题来测验下，后面会有答案及相关解释

## 测试问题

### 问题1

下面的索引适合这个查询吗？
```sql
    CREATE INDEX tbl_idx ON tbl (date_column)
    
    SELECT COUNT(*)
      FROM tbl
     WHERE EXTRACT(YEAR FROM date_column) = 2017
```
选项：

A 很适合

B 不适合

### 问题2

下面的索引适合这个查询吗？
```sql
    CREATE INDEX tbl_idx ON tbl (a, date_column)
    
    SELECT *
      FROM tbl
     WHERE a = 12
     ORDER BY date_column DESC
     LIMIT 1
```
选项：

A 很适合

B 不适合

### 问题3

下面的索引适合这两个查询吗？
```sql
    CREATE INDEX tbl_idx ON tbl (a, b)
    
    SELECT *
      FROM tbl
     WHERE a = 38
       AND b = 1
       
    SELECT *
      FROM tbl
     WHERE b = 1  
```
选项：

A 很适合

B 不适合

### 问题4

下面的索引适合这个查询吗？
```sql
    CREATE INDEX tbl_idx ON tbl (text)
    
    SELECT *
      FROM tbl
     WHERE text LIKE 'TJ%'
```
选项：

A 很适合

B 不适合

### 问题5

先看下这个索引和查询
```sql
    CREATE INDEX tbl_idx ON tbl (a, date_column)
    
    SELECT date_column, count(*)
      FROM tbl
     WHERE a = 38
     GROUP BY date_column
```
为了实现一个新的功能需求，会添加一个新的查询条件 b = 1
```sql
    SELECT date_column, count(*)
      FROM tbl
     WHERE a = 38
       AND b = 1
     GROUP BY date_column
```
新的查询会如何影响性能？

选项：

A 两个查询的性能一致

B 无法判断，因为信息不足

C 第二个查询更慢了

D 第二个查询更快了

## 答案及解析

### 问题1
```sql
    CREATE INDEX tbl_idx ON tbl (date_column)
    
    SELECT COUNT(*)
      FROM tbl
     WHERE EXTRACT(YEAR FROM date_column) = 2017
```
#### 答案B 不适合

因为对索引列使用了函数，会使索引失效，使用下面的方式会更高效
```sql
    SELECT COUNT(*)
      FROM tbl
     WHERE date_column >= DATE'2017-01-01'
       AND date_column <  DATE'2018-01-01'
```
### 问题2
```sql
    CREATE INDEX tbl_idx ON tbl (a, date_column)
    
    SELECT *
      FROM tbl
     WHERE a = 12
     ORDER BY date_column DESC
     LIMIT 1
```
#### 答案A 很适合

这个索引很好的支持了 where 和 order by

### 问题3
```sql
    CREATE INDEX tbl_idx ON tbl (a, b)
    
    SELECT *
      FROM tbl
     WHERE a = 38
       AND b = 1
       
    SELECT *
      FROM tbl
     WHERE b = 1  
```
#### 答案B 不适合

索引只覆盖了第一个查询，第二个查询没能高效的使用索引

改变一下索引即可
```sql
    CREATE INDEX tbl_idx ON tbl (b, a)
```
### 问题4
```sql
    CREATE INDEX tbl_idx ON tbl (text)
    
    SELECT *
      FROM tbl
     WHERE text LIKE 'TJ%'
```
#### 答案A 适合

LIKE 中虽然使用了 %，但是在尾部，是可以应用索引的

### 问题5
```sql
    CREATE INDEX tbl_idx ON tbl (a, date_column)
    
    SELECT date_column, count(*)
      FROM tbl
     WHERE a = 38
     GROUP BY date_column
     
    SELECT date_column, count(*)
      FROM tbl
     WHERE a = 38
       AND b = 1
     GROUP BY date_column
```
#### 答案C 第二个查询更慢了

第一个查询只需要对索引进行扫描，因为 select, where, group by中涉及的列都是索引中的，完全不需要访问实际的表，这种情况叫做索引覆盖，性能是极好的

而第二个查询就需要访问实际的表，根据 b = 1这个条件进行过滤

## 小结

上面是5个关于索引使用的小问题，比较简单，但也常被忽略，希望能对大家有点帮助

[1]: https://sdk.cn/news/8028

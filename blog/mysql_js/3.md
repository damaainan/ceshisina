# 干货：你不知道的mysql多列索引的建立和优化

作者  [小灰灰besty][0] 关注 2017.05.04 16:44  字数 3392  

对于单列索引，没有太多的话题，但是对于多列索引的建立，一个好的多列索引能使用的场景应可以涵盖很多，减少其他不必要的索引空间，就有很多事情需要注意。

- - -

### 0.首先来了解索引的物理结构:

[http://www.jianshu.com/p/1775b4ff123a][1]

- - -

### 1.where 子句中的多列索引

如果表有多个列索引,可以使用任何左边的**前缀索引**的优化器来查找行。如何通过**前缀索引**来查找行我们通过如下例子来详细了解：

现有表formatting，数据如下图所示：

![][2]



formatting

  
在该表上建立多列索引：

> create index idx_custid_qty_empid on formatting(custid,qty,empid)## 1.1可以完全使用该索引的优化的情况

例如,如果你有一个三列的索引(col1、col2 col3),有索引搜索功能(col1),(col1,col2),(col1、col2、col3)。


1）where中条件只有col1等于常量;
说明：表中custid为A的数据共4行，查询中索引优化得到的等于结果数据。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A'\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: const
         rows: 4
     filtered: 100.00
        Extra: Using index

```
2）where中条件只有col1为范围(>,<,>=,<=)；
说明：表中custid大于A的有5行，索引起到完全优化的作用。
```sql
mysql> explain select orderid,orderdate from formatting where custid>'A'\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: range
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: NULL
         rows: 5
     filtered: 100.00
        Extra: Using where; Using index
```
3）where中条件有col1,col2，且col1等于常量，col2等于常量；
说明：表中custid为A，qty等于20的数据只有一条，索引完全优化查询。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A' and qty=20\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 36
          ref: const,const
         rows: 1
     filtered: 100.00
        Extra: Using index
```
4）where中条件有col1,col2，且col1等于常量，col2等于范围；
说明：表中custid为A，qty大于等于20的数据有2条，索引完全优化查询。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A' and qty>=20\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: range
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 36
          ref: NULL
         rows: 2
     filtered: 100.00
        Extra: Using where; Using index
```
5）where中条件有col1,col2,col3，且col1,col2,col3均等于常量；
说明：表中custid为A，qty等于10，empid等于2的数据有1条，索引完全优化查询。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A' and qty=10 and empid=2\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 40
          ref: const,const,const
         rows: 1
     filtered: 100.00
        Extra: Using index
```
6）where中条件有col1,col2,col3，且col1,col2均等于常量，col3为范围；
说明：表中custid为A，qty等于10，empid大于2的数据有1条，索引完全优化查询。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A' and qty=10 and empid>2\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: range
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 40
          ref: NULL
         rows: 1
     filtered: 100.00
        Extra: Using where; Using index
```

#### 1.2可以部分使用该索引的优化的情况

1）where中条件col1等于常量，无col2，col3的常量或范围，只能用到col1的索引；
说明：custid='A' and empid=2的数据仅有一条，但由于empid在多列索引中为第三列，故仅能使用多列索引优化custid='A'的4条数据。
```sql
mysql>  explain select orderid,orderdate from formatting where custid='A' and empid=2\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: const
         rows: 4
     filtered: 11.11
        Extra: Using where; Using index
```
2）where中条件col1等于常量，col2的范围，col3的常量或范围，只能用到col1、col2的索引；
说明：custid='A' and qty<20 and empid=2的数据仅有一条，但由于qty为范围，故仅能使用多列索引优化custid='A' and qty<20的2条数据。
```sql
mysql> explain select orderid,orderdate from formatting where custid='A' and qty<20 and empid=2\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: range
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 36
          ref: NULL
         rows: 2
     filtered: 11.11
        Extra: Using where; Using index
```
3）where中条件col1的范围，col2、col3的常量或范围，只能用到col1的索引；
说明：custid>'A' and empid=2的数据仅有一条，但由于custid为范围查询，故仅能使用多列索引优化custid>'A'的5条数据。
```sql
mysql> explain select orderid,orderdate from formatting where custid>'A' and empid=2\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: range
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: NULL
         rows: 5
     filtered: 11.11
        Extra: Using where; Using index
```
1.2不能使用该索引的优化的情况

下列情况均需要扫描所有行再进行筛选，因为多列索引的第一列没有在条件中。
1）col2 常量或范围；
2）col3 常量或范围；
3）col2、col3 常量或范围；
说明：由于没有custid的条件需要扫描9行数据。为何下面的例子中，却使用了type: index？ 因为：当索引是覆盖索引并且可以用来满足需要从表中得到的所有数据时，只有索引树被扫描。在这种情况下，（EXPLAIN命令）返回记录里的Extra字段提示Using index。此表中的orderid,orderdate为联合主键。
```sql
mysql> explain select orderid,orderdate from formatting where  qty=20\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: index
possible_keys: NULL
          key: idx_custid_qty_empid
      key_len: 40
          ref: NULL
         rows: 9
     filtered: 11.11
        Extra: Using where; Using index
```
我们添加一列新的列名叫version，并对其赋值，如下图所示。

![][3]

再来看一下上面会发生什么变化，此时要查询多列避免出现覆盖索引，我们发现没有使用任何索引了：
```sql
mysql> desc select orderid,orderdate,empid,custid,qty,version from formatting where  qty=20\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ALL
possible_keys: NULL
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 10
     filtered: 10.00
        Extra: Using where
```
2.order by 子句中的多列索引

2.1.在不是所有的order by子句中的字段完全满足索引，在一些情况下依然可以使用索引。

1） order by子句中的字段顺序与索引字段顺序一致：
```sql
create index idx_custid_qty on formatting(custid,qty);
mysql> desc select orderid from formatting order by custid,qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: index
possible_keys: NULL
          key: idx_custid_qty
      key_len: 36
          ref: NULL
         rows: 10
     filtered: 100.00
        Extra: Using index
```
注意：此时若使用 select * 则不会使用索引。
```sql
mysql> desc select * from formatting order by custid,qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ALL
possible_keys: NULL
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 9
     filtered: 100.00
        Extra: Using filesort
```
那么为何有索引却不能使用呢，我们结合最初的链接中的索引物理结构来理解：
第一种方法是直接读取表记录，然后根据order by 列去排序。
第二种方法是先扫描order by 上的索引,由于索引本身就是排好序的，因此根据索引的顺序再读出表的记录即可，省去排序的过程。
我们可以看出第一种方法直接读取表记录就可以了.第二种方法需要先读取索引，然后根据索引去读取表记录，
io会变得很随机，因此这种方法会成本比较高,所以优化器会选择第一种执行执行划。

2）按照索引顺序的where和order by子句依旧可以使用,但where条件中必须使用字段名> < =某常量。
```sql
mysql> desc select * from formatting where custid='A' order by qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty
          key: idx_custid_qty
      key_len: 32
          ref: const
         rows: 4
     filtered: 100.00
        Extra: Using index condition
```
2.2.但是有些情况是不能使用索引的

1）同一个order by 中的字段分别有多个索引。
建立custid和empid的单独索引。
```sql
create index idx_custid on formatting(custid)
create index idx_empid on formatting(empid)
mysql> desc select orderdate from formatting order by custid,empid\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ALL
possible_keys: NULL
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 10
     filtered: 100.00
        Extra: Using filesort
```
2）where 与order by 字段的顺序与索引顺序不同，排序不能使用索引。例子中没有使用联合索引，只能在where使用单个索引。
```sql
create index idx_custid_qty_empid on formatting(custid,qty,empid)
create index idx_qty on formatting(qty)
mysql> desc select orderdate from formatting where qty=10 order by custid,empid\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_qty
          key: idx_qty
      key_len: 4
          ref: const
         rows: 2
     filtered: 100.00
        Extra: Using index condition; Using filesort
```
3） 同时包含asc与desc，排序不能使用索引，只能在where条件使用索引，排序Using filesort。

删除其他索引
```sql
create index idx_custid_qty_empid on formatting(custid,qty,empid)
mysql> explain select orderid,orderdate,empid,custid,qty,version from formatting where custid='A' order by  qty desc ,empid asc\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: const
         rows: 4
     filtered: 100.00
        Extra: Using index condition; Using filesort
```
4） order by 字段中有计算，排序不能使用索引。
先来看不使用计算时，可以使用索引的情况：
```sql
mysql> explain select orderid,orderdate,empid,custid,qty,version from formatting where custid='A' order by qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: const
         rows: 4
     filtered: 100.00
        Extra: Using index condition
```
增加计算后，排序不能使用索引，需要Using filesort
```sql
mysql> explain select orderid,orderdate,empid,custid,qty,version from formatting where custid='A' order by qty+1\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: ref
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 32
          ref: const
         rows: 4
     filtered: 100.00
        Extra: Using index condition; Using filesort
```
5）ORDER BY 和 GROUP BY的表达式不同，排序不能使用索引。
先来看下ORDER BY 和 GROUP BY的表达式相同情况，是不需要Using filesort。
```sql
mysql> explain select custid,qty from formatting group by custid,qty order by custid,qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: index
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 40
          ref: NULL
         rows: 10
     filtered: 100.00
        Extra: Using index
```
但是，如果不同的情况排序是不能使用索引的，需要再一次Using filesort。
```sql
mysql> explain select custid,qty from formatting group by custid,qty order by qty\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: formatting
   partitions: NULL
         type: index
possible_keys: idx_custid_qty_empid
          key: idx_custid_qty_empid
      key_len: 40
          ref: NULL
         rows: 10
     filtered: 100.00
        Extra: Using index; Using temporary; Using filesort
```
6） join的表ORDER BY字段来自不同的表只能使用主表的索引。
如果查询需要关联多张表，则只有当ORDER BY子句引用的字段全部为第一个表时，才能使用索引做排序。此时需要注意，在inner join时，优化器将哪张表选做主表，就只能使用此表的索引。
其中一定要满足order by 在单表查询中的要求，若去掉T1.empid=2，则同2.1中1情况不能使用排序索引。
使用主表的排序：
```sql
mysql> desc select * from  formatting t1 left join emp t2 on t1.empid=t2.empid WHERE T1.empid=2 order by T1.custid \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: t1
   partitions: NULL
         type: ref
possible_keys: idx_empid_custid
          key: idx_empid_custid
      key_len: 4
          ref: const
         rows: 2
     filtered: 100.00
        Extra: Using index condition
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: t2
   partitions: NULL
         type: ref
possible_keys: idx_empid
          key: idx_empid
      key_len: 5
          ref: const
         rows: 1
     filtered: 100.00
        Extra: NULL
```
使用第二张表排序，需要Using filesort：
```sql
mysql> desc select * from  formatting t1 left join emp t2 on t1.empid=t2.empid WHERE T1.empid=2 order by T2.empname \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: t1
   partitions: NULL
         type: ref
possible_keys: idx_empid_custid
          key: idx_empid_custid
      key_len: 4
          ref: const
         rows: 2
     filtered: 100.00
        Extra: Using temporary; Using filesort
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: t2
   partitions: NULL
         type: ref
possible_keys: idx_empid
          key: idx_empid
      key_len: 5
          ref: const
         rows: 1
     filtered: 100.00
        Extra: NULL
```
对于上述去掉where条件情况可以如下优化语句：
优化前语句：

    SELECT * FROM a LEFT JOIN b ON a.id=b.a_id ORDER a.id DESC
优化后语句:

    SELECT * FROM a LEFT JOIN b ON a.id=b.a_id JOIN (SELECT id FROM a ORDER BY id DESC) a_order ON a.id = a_order.id



[0]: /u/372d9a421d7e
[1]: http://www.jianshu.com/p/1775b4ff123a
[2]: ./img/5687393-940f1a556668e79e.png
[3]: ./img/5687393-72b035ba7722cb66.png
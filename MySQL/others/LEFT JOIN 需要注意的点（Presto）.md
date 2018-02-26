## LEFT JOIN 需要注意的点（Presto）

来源：[https://segmentfault.com/a/1190000013121493](https://segmentfault.com/a/1190000013121493)

最近工在Presto中用了很多关联查询操作，遇到了一些问题再这里记录一下。
## LEFT JOIN的基本概念

LEFT JOIN是我们最常用的关联查询，对于之前很少直接接触复杂关联查询的兄弟，我们还是需要弄清楚一些基本概念。


* LEFT JOIN是逻辑操作符，对于放在左边的表来说，是以其为base，如果SELECT查询的字段全都来自左边的表，那么最终的结果条数会 >= 左表条数
* 数据库优化器是最终决定执行顺序的地方，一般的会按照你的LEFT JOIN的顺序执行，但也不保证完全是那样。


## 怎么执行呢？

例如：

```sql
SELECT table_1.a, table_1.b, table_1.c
FROM table_1 
    LEFT JOIN table_2
    ON table_1.uid = table_2.uid
    LEFT JOIN talbe_3
    ON table_1.uid = table_3.uid
```

**`执行顺序是：table_1和table_2先组合成一个虚拟表，然后这个虚拟表再和table_3关联。`** 
## 多个LEFT JOIN连接，记录的条数是不是主表的条数？

如上面的语句，在没有WHERE语句情况下，是大于等于table_1的条数。
这是因为：

```
- SELECT 后面的字段均来自table_1;
- 所有的关联条件都是为了匹配table_1;
```
## LEFT JOIN后数据量增加的问题

**`但是有一种情况，最终结果可能是大于table_1的情况的，那就是在table_2或者table_3中有重复的uid，并且正好符合关联条件的时候，结果表就会被撑大。`** 

##### 如下面的情况：

这里先考虑只有table_2的情况。
table_1中有如下数据：

| uid | a | b | c |
|-|-|-|-|
| 1 | 100 | 101 | 102 |
| 2 | 200 | 201 | 202 |
| 3 | 300 | 301 | 302 |


如果table_2是以下形式：

| uid | a | b |
|-|-|-|
| 1 | 10 | 10 |
| 1 | 10 | 10 |
| 2 | 20 | 10 |
| 2 | 20 | 10 |


如果没有去重，最终的结果就是：

| a | b | c |
|-|-|-|
| 100 | 101 | 102 |
| 100 | 101 | 102 |
| 200 | 201 | 202 |
| 200 | 201 | 202 |
| 300 | 301 | 302 |


为了看得更清楚一些，我们改写一下SQL语句：

```sql
SELECT 
    table_1.a t1_a, table_1.b t1_b, 
    table_1.c t1_c, table_1.uid t1_uid,
    table_2.uid t2_uid, table_2.a t2_a,
    table_2.b t2_b
FROM table_1
    LEFT JOIN table_2
    ON table_1.uid = table_2.uid;
```

| t1_a | t1_b | t1_c | t1_uid | t2_uid | t2_a | t2_b |
|-|-|-|-|-|-|-|
| 100 | 101 | 102 | 1 | 1 | 10 | 10 |
| 100 | 101 | 102 | 1 | 1 | 10 | 10 |
| 200 | 201 | 202 | 2 | 2 | 20 | 10 |
| 200 | 201 | 202 | 2 | 2 | 20 | 10 |
| 300 | 301 | 302 | 3 | NULL | NULL | NULL |


我们可以清楚的看到，uid=1, uid=2被分别匹配了两次。

#### 怎样解决？

我们可以考虑想将table_2去重。

我们可以先利用一个子查询去掉重复的记录，然后再与table_1进行联合。 **`这里使用了Presto的语法`** 

```sql
WITH table_2 AS (
    SELECT distinct uid, a, b 
    FROM table_2
)
SELECT 
    table_1.a t1_a, table_1.b t1_b, 
    table_1.c t1_c, table_1.uid t1_uid,
    table_2.uid t2_uid, table_2.a t2_a,
    table_2.b t2_b
FROM table_1
    LEFT JOIN table_2
    ON table_1.uid = table_2.uid;
```

结果为：

| t1_a | t1_b | t1_c | t1_uid | t2_uid | t2_a | t2_b |
|-|-|-|-|-|-|-|
| 100 | 101 | 102 | 1 | 1 | 10 | 10 |
| 200 | 201 | 202 | 2 | 2 | 20 | 10 |
| 300 | 301 | 302 | 3 | NULL | NULL | NULL |


这样就避免了被关联表中的重复记录影响最终结果。

上面的情况在处理大数据量的表时，最容易造成“Insufficient Resources”的error，导致Presto罢工。所以一定要小心。
## LEFT JOIN后得到的数据许多NULL数据

如果被关联的表中没有能匹配关联条件，这会让数据库用NULL去填充结果。
如果你的查询结果是两个表字段共同决定的，要信息处理这个问题。
例如(table_1和table_2还是用上面的数据)：

```sql
WITH table_2 AS (
    SELECT distinct uid, a, b 
    FROM table_2
)
SELECT 
    table_2.a a, table_2.b b, table_1.c c
FROM table_1
    LEFT JOIN table_2
    ON table_1.uid = table_2.uid;
```

得到的结果就是：

| a | b | c |
|-|-|-|
| 10 | 10 | 102 |
| 20 | 10 | 202 |
| NULL | NULL | 302 |


所以，要加上if语句进行判断。

```sql
WITH table_2 AS (
    SELECT distinct uid, a, b 
    FROM table_2
)
SELECT 
    if(table_2.a IS NOT NULL, table_2.a, table_1.a) a, 
    if(table_2.b IS NOT NULL, table_2.b, table_1.b) b, 
    table_1.c c
FROM table_1
    LEFT JOIN table_2
    ON table_1.uid = table_2.uid;
```

得到的结果：
| a | b | c |
|-|-|-|
| 10 | 10 | 102 |
| 20 | 10 | 202 |
| 300 | 301 | 302 |


## JOIN的左边尽量放小数据量的表

这样可以提高查询效率，优化查询速度。
## 不要JOIN（LEFT RIGHT FULL等等）起来没完

要知道，每次JOIN关联，数据库都会先将两边的数据进行全量组合（也就是笛卡尔积的形式），然后再进行条件筛选，会造成服务器资源紧张。
所以，我们需要将许多的JOIN用with语句分割成多个子查询，然后再一步一步关联。

但是也要注意，在做ETL清洗时，一般需要将一个表的全量字段经过清洗步骤，然后插入到另一个目标表中。这时，子查询很多的情况，也会使得服务器的内存紧张，必要的时候我们可以采用临时表的方案，用临时表存储一些中间结果，最后再综合中间结果完成整个操作。

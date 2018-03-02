## 【mysql的设计与优化专题(6)】mysql索引攻略

来源：[https://segmentfault.com/a/1190000006063289](https://segmentfault.com/a/1190000006063289)

所谓索引就是为特定的mysql字段进行一些特定的算法排序,比如二叉树的算法和哈希算法,哈希算法是通过建立特征值,然后根据特征值来快速查找,而用的最多,并且是mysql默认的就是二叉树算法 BTREE,通过BTREE算法建立索引的字段,比如扫描20行就能得到未使用BTREE前扫描了2^20行的结果,具体的实现方式后续本博客会出一个算法专题里面会有具体的分析讨论;

## Explain优化查询检测

EXPLAIN可以帮助开发人员分析SQL问题,explain显示了mysql如何使用索引来处理select语句以及连接表,可以帮助选择更好的索引和写出更优化的查询语句.

使用方法,在select语句前加上Explain就可以了：
`Explain select * from blog where false;`

mysql在执行一条查询之前,会对发出的每条SQL进行分析,决定是否使用索引或全表扫描如果发送一条`select * from blog where false `Mysql是不会执行查询操作的,因为经过SQL分析器的分析后MySQL已经清楚不会有任何语句符合操作;

**`Example`** 

```sql
mysql> EXPLAIN SELECT `birday` FROM `user` WHERE `birthday` < "1990/2/2";
-- 结果：
id: 1

select_type: SIMPLE -- 查询类型（简单查询,联合查询,子查询）

table: user -- 显示这一行的数据是关于哪张表的

type: range -- 区间索引（在小于1990/2/2区间的数据),这是重要的列,显示连接使用了何种类型。从最好到最差的连接类型为system > const > eq_ref > ref > fulltext > ref_or_null > index_merge > unique_subquery > index_subquery > range > index > ALL,const代表一次就命中,ALL代表扫描了全表才确定结果。一般来说,得保证查询至少达到range级别,最好能达到ref。

possible_keys: birthday  -- 指出MySQL能使用哪个索引在该表中找到行。如果是空的,没有相关的索引。这时要提高性能,可通过检验WHERE子句,看是否引用某些字段,或者检查字段不是适合索引。 

key: birthday -- 实际使用到的索引。如果为NULL,则没有使用索引。如果为primary的话,表示使用了主键。

key_len: 4 -- 最长的索引宽度。如果键是NULL,长度就是NULL。在不损失精确性的情况下,长度越短越好

ref: const -- 显示哪个字段或常数与key一起被使用。 

rows: 1 -- 这个数表示mysql要遍历多少数据才能找到,在innodb上是不准确的。

Extra: Using where; Using index -- 执行状态说明,这里可以看到的坏的例子是Using temporary和Using 
```

**`select_type`** 


* simple  简单select(不使用union或子查询)

* primary   最外面的select

* union    union中的第二个或后面的select语句

* dependent union  union中的第二个或后面的select语句,取决于外面的查询

* union result  union的结果。

* subquery 子查询中的第一个select

* dependent subquery  子查询中的第一个select,取决于外面的查询

* derived    导出表的select(from子句的子查询)



**`Extra与type详细说明`** 


* Distinct:一旦MYSQL找到了与行相联合匹配的行,就不再搜索了

* Not exists: MYSQL优化了LEFT JOIN,一旦它找到了匹配LEFT JOIN标准的行,就不再搜索了

* Range checked for each Record（index map:#）:没有找到理想的索引,因此对于从前面表中来的每一个行组合,MYSQL检查使用哪个索引,并用它来从表中返回行。这是使用索引的最慢的连接之一

* Using filesort: **`看到这个的时候,查询就需要优化了`** 。MYSQL需要进行额外的步骤来发现如何对返回的行排序。它根据连接类型以及存储排序键值和匹配条件的全部行的行指针来排序全部行

* Using index: 列数据是从仅仅使用了索引中的信息而没有读取实际的行动的表返回的,这发生在对表的全部的请求列都是同一个索引的部分的时候

* Using temporary **`看到这个的时候,查询需要优化了`** 。这里,MYSQL需要创建一个临时表来存储结果,这通常发生在对不同的列集进行ORDER BY上,而不是GROUP BY上

* Where used 使用了WHERE从句来限制哪些行将与下一张表匹配或者是返回给用户。如果不想返回表中的全部行,并且连接类型ALL或index,这就会发生,或者是查询有问题不同连接类型的解释（按照效率高低的顺序排序

* system 表只有一行：system表。这是const连接类型的特殊情况

* const:表中的一个记录的最大值能够匹配这个查询（索引可以是主键或惟一索引）。因为只有一行,这个值实际就是常数,因为MYSQL先读这个值然后把它当做常数来对待

* eq_ref:在连接中,MYSQL在查询时,从前面的表中,对每一个记录的联合都从表中读取一个记录,它在查询使用了索引为主键或惟一键的全部时使用

* ref:这个连接类型只有在查询使用了不是惟一或主键的键或者是这些类型的部分（比如,利用最左边前缀）时发生。对于之前的表的每一个行联合,全部记录都将从表中读出。这个类型严重依赖于根据索引匹配的记录多少—越少越好+

* range:这个连接类型使用索引返回一个范围中的行,比如使用>或<查找东西时发生的情况+

* index: 这个连接类型对前面的表中的每一个记录联合进行完全扫描（比ALL更好,因为索引一般小于表数据）+

* ALL:这个连接类型对于前面的每一个记录联合进行完全扫描,这一般比较糟糕,应该尽量避免



其中type:


* 如果是Only index,这意味着信息只用索引树中的信息检索出的,这比扫描整个表要快。

* 如果是where used,就是使用上了where限制。

* 如果是impossible where 表示用不着where,一般就是没查出来啥。

* 如果此信息显示Using filesort或者Using temporary的话会很吃力,WHERE和ORDER BY的索引经常无法兼顾,如果按照WHERE来确定索引,那么在ORDER BY时,就必然会引起Using filesort,这就要看是先过滤再排序划算,还是先排序再过滤划算。



## 索引
### 索引的类型

**`UNIQUE唯一索引`** 

不可以出现相同的值,可以有NULL值

**`INDEX普通索引`** 

允许出现相同的索引内容

**`PRIMARY KEY主键索引`** 

不允许出现相同的值,且不能为NULL值,一个表只能有一个primary_key索引

**`fulltext index 全文索引`** 

上述三种索引都是针对列的值发挥作用,但全文索引,可以针对值中的某个单词,比如一篇文章中的某个词, **`然而并没有什么卵用,因为只有myisam以及英文支持,并且效率让人不敢恭维,但是可以用coreseek和xunsearch等第三方应用来完成这个需求`** 
### 索引的CURD
#### 索引的创建

**`ALTER TABLE`** 
适用于表创建完毕之后再添加

```sql
ALTER TABLE 表名 ADD 索引类型 （unique,primary key,fulltext,index）[索引名]（字段名）
```

```sql
ALTER TABLE `table_name` ADD INDEX `index_name` (`column_list`) -- 索引名,可要可不要;如果不要,当前的索引名就是该字段名;
ALTER TABLE `table_name` ADD UNIQUE (`column_list`)
ALTER TABLE `table_name` ADD PRIMARY KEY (`column_list`)
ALTER TABLE `table_name` ADD FULLTEXT KEY (`column_list`)
```

**`CREATE INDEX`** 
CREATE INDEX可对表增加普通索引或UNIQUE索引

```sql
--例,只能添加这两种索引;
CREATE INDEX index_name ON table_name (column_list)
CREATE UNIQUE INDEX index_name ON table_name (column_list)
```

**`另外,还可以在建表时添加`** 

```sql
CREATE TABLE `test1` (
  `id` smallint(5) UNSIGNED AUTO_INCREMENT NOT NULL, -- 注意,下面创建了主键索引,这里就不用创建了
  `username` varchar(64) NOT NULL COMMENT '用户名',
  `nickname` varchar(50) NOT NULL COMMENT '昵称/姓名',
  `intro` text,
  PRIMARY KEY (`id`), 
  UNIQUE KEY `unique1` (`username`), -- 索引名称,可要可不要,不要就是和列名一样
  KEY `index1` (`nickname`),
  FULLTEXT KEY `intro` (`intro`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='后台用户表';
```
#### 索引的删除

```sql
DROP INDEX `index_name` ON `talbe_name` 
ALTER TABLE `table_name` DROP INDEX `index_name`
-- 这两句都是等价的,都是删除掉table_name中的索引index_name;

ALTER TABLE `table_name` DROP PRIMARY KEY -- 删除主键索引,注意主键索引只能用这种方式删除
```
### 索引的查看

```sql
show index from tablename \G;
```
### 索引的更改

更改个毛线,删掉重建一个既可
### 创建索引的技巧

1.维度高的列创建索引

数据列中 **`不重复值`** 出现的个数,这个数量越高,维度就越高
如数据表中存在8行数据a ,b ,c,d,a,b,c,d这个表的维度为4
要为维度高的列创建索引,如性别和年龄,那年龄的维度就高于性别
性别这样的列不适合创建索引,因为维度过低

2.对 **`where,on,group by,order by`**  中出现的列使用索引

3.对较小的数据列使用索引,这样会使索引文件更小,同时内存中也可以装载更多的索引键

4.为较长的字符串使用前缀索引

5.不要过多创建索引,除了增加额外的磁盘空间外,对于DML操作的速度影响很大,因为其每增删改一次就得从新建立索引

6.使用组合索引,可以减少文件索引大小,在使用时速度要优于多个单列索引
#### 组合索引与前缀索引

注意,这两种称呼是对建立索引技巧的一种称呼,并非索引的类型;

**`组合索引`** 
MySQL单列索引和组合索引究竟有何区别呢？

为了形象地对比两者,先建一个表：

```sql
CREATE TABLE `myIndex` (
  `i_testID` INT NOT NULL AUTO_INCREMENT, 
  `vc_Name` VARCHAR(50) NOT NULL, 
  `vc_City` VARCHAR(50) NOT NULL, 
  `i_Age` INT NOT NULL, 
  `i_SchoolID` INT NOT NULL, 
  PRIMARY KEY (`i_testID`) 
);
```

假设表内已有1000条数据,在这 10000 条记录里面 7 上 8 下地分布了 5 条 vc_Name="erquan" 的记录,只不过 city,age,school 的组合各不相同。
来看这条 T-SQL：

```sql
SELECT `i_testID` FROM `myIndex` WHERE `vc_Name`='erquan' AND `vc_City`='郑州' AND `i_Age`=25; -- 关联搜索;
```

首先考虑建MySQL单列索引：

在 vc_Name 列上建立了索引。执行 T-SQL 时,MYSQL 很快将目标锁定在了 vc_Name=erquan 的 5 条记录上,取出来放到一中间结果集。在这个结果集里,先排除掉 vc_City 不等于"郑州"的记录,再排除 i_Age 不等于 25 的记录,最后筛选出唯一的符合条件的记录。
虽然在 vc_Name 上建立了索引,查询时MYSQL不用扫描整张表,效率有所提高,但离我们的要求还有一定的距离。同样的,在 vc_City 和 i_Age 分别建立的MySQL单列索引的效率相似。

为了进一步榨取 MySQL 的效率,就要考虑建立组合索引。就是将 vc_Name,vc_City,i_Age 建到一个索引里：

```sql
 ALTER TABLE `myIndex` ADD INDEX `name_city_age` (vc_Name(10),vc_City,i_Age);
```

建表时,vc_Name 长度为 50,这里为什么用 10 呢？这就是下文要说到的前缀索引,因为一般情况下名字的长度不会超过 10,这样会加速索引查询速度,还会减少索引文件的大小,提高 INSERT 的更新速度。

执行 T-SQL 时,MySQL 无须扫描任何记录就到找到唯一的记录！！

如果分别在 vc_Name,vc_City,i_Age 上建立单列索引,让该表有 3 个单列索引,查询时和上述的组合索引效率一样吗？答案是大不一样,远远低于我们的组合索引。虽然此时有了三个索引, **`但 MySQL 只能用到其中的那个它认为似乎是最有效率的单列索引,另外两个是用不到的,也就是说还是一个全表扫描的过程`** 。
建立这样的组合索引,其实是相当于分别建立了

```sql
vc_Name,vc_City,i_Age
vc_Name,vc_City
vc_Name
```

这样的三个组合索引！为什么没有 vc_City,i_Age 等这样的组合索引呢？这是因为 mysql 组合索引 **`"最左前缀"`** 的结果。简单的理解就是只从最左面的开始组合。并不是只要包含这三列的查询都会用到该组合索引,下面的几个 T-SQL 会用到：

```sql
SELECT * FROM myIndex WHREE vc_Name="erquan" AND vc_City="郑州"
SELECT * FROM myIndex WHREE vc_Name="erquan"
```

而下面几个则不会用到：

```sql
SELECT * FROM myIndex WHREE i_Age=20 AND vc_City="郑州"
SELECT * FROM myIndex WHREE vc_City="郑州"
```

**`也就是,`name_city_age` (vc_Name(10),vc_City,i_Age) 从左到右进行索引,如果没有左前索引Mysql不执行索引查询`** 
#### 前缀索引

如果索引列长度过长,这种列索引时将会产生很大的索引文件,不便于操作,可以使用前缀索引方式进行索引
前缀索引应该控制在一个合适的点,控制在0.31黄金值即可(大于这个值就可以创建)

```sql
SELECT COUNT(DISTINCT(LEFT(`title`,10)))/COUNT(*) FROM Arctic; -- 这个值大于0.31就可以创建前缀索引,Distinct去重复

ALTER TABLE `user` ADD INDEX `uname`(title(10)); -- 增加前缀索引SQL,将人名的索引建立在10,这样可以减少索引文件大小,加快索引查询速度
```
### 什么样的sql不走索引

要尽量避免这些不走索引的sql

```sql
SELECT `sname` FROM `stu` WHERE `age`+10=30;-- 不会使用索引,因为所有索引列参与了计算

SELECT `sname` FROM `stu` WHERE LEFT(`date`,4) <1990; -- 不会使用索引,因为使用了函数运算,原理与上面相同

SELECT * FROM `houdunwang` WHERE `uname` LIKE'后盾%' -- 走索引

SELECT * FROM `houdunwang` WHERE `uname` LIKE "%后盾%" -- 不走索引

-- 正则表达式不使用索引,这应该很好理解,所以为什么在SQL中很难看到regexp关键字的原因

-- 字符串与数字比较不使用索引;
CREATE TABLE `a` (`a` char(10));
EXPLAIN SELECT * FROM `a` WHERE `a`="1" -- 走索引
EXPLAIN SELECT * FROM `a` WHERE `a`=1 -- 不走索引

select * from dept where dname='xxx' or loc='xx' or deptno=45 --如果条件中有or,即使其中有条件带索引也不会使用。换言之,就是要求使用的所有字段,都必须建立索引, 我们建议大家尽量避免使用or 关键字

-- 如果mysql估计使用全表扫描要比使用索引快,则不使用索引
```
### 多表关联时的索引效率


![][0]
从上图可以看出,所有表的type为all,表示全表索引;也就是6 **6** 6,共遍历查询了216次;

除第一张表示全表索引(必须的,要以此关联其他表),其余的为range(索引区间获得),也就是6+1+1+1,共遍历查询9次即可;

所以我们建议在多表join的时候尽量少join几张表,因为一不小心就是一个笛卡尔乘积的恐怖扫描,另外,我们还建议尽量使用left join,以少关联多.因为使用join 的话,第一张表是必须的全扫描的,以少关联多就可以减少这个扫描次数.
### 索引的弊端

不要盲目的创建索引,只为查询操作频繁的列创建索引,创建索引会使查询操作变得更加快速,但是会降低增加、删除、更新操作的速度,因为执行这些操作的同时会对索引文件进行重新排序或更新;

但是,在互联网应用中,查询的语句远远大于DML的语句,甚至可以占到80%~90%,所以也不要太在意,只是在大数据导入时,可以先删除索引,再批量插入数据,最后再添加索引;

[0]: https://segmentfault.com/img/remote/1460000006776839
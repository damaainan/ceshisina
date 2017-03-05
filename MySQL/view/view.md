**Mysql 视图**

**1. 视图简介**

1）视图的含义：

视图是一张虚拟的表。与包含数据的表不一样，视图只包含使用时动态检索数据的查询。

2）为什么使用视图： 

重用SQL语句。

简化复杂的SQL操作。在编写查询后，可以方便地重用它而不必知道它的基本查询细节。

使用表的组成部分而不是整个表。

保护数据。可以给用户授予表的特定部分的访问权限而不是整个表的访问权限。

更改数据格式和表示。视图可返回与底层表的表示和格式不同的数据。

注意：

重要的是知道视图仅仅是用来查看存储在别处的数据的一种设施。视图本身不包含数据，因此它们返回的数据是从其他表中检索出来的。在添加或更改这些表中的数据时，视图将返回改 变过的数据。

3）视图的规则和限制：

与表一样，视图必须唯一命名（不能给视图取与别的视图或表相同的名字）。

对于可以创建的视图数目没有限制。

为了创建视图，必须具有足够的访问权限。这些限制通常由数据库管理人员授予。

视图可以嵌套，即可以利用从其他视图中检索数据的查询来构造一个视图。

ORDER BY 可以用在视图中，但如果从该视图检索数据 SELECT 中也含有 ORDER BY ，那么该视图中的 ORDER BY 将被覆盖。

视图不能索引，也不能有关联的触发器或默认值。

视图可以和表一起使用。例如，编写一条联结表和视图的 SELECT语句。

**2. 创建视图**
```
    CREATE   ALGORITHM = UNDEFINED | MERGE | TEMPTABLE  
         VIEW   视图名   ( 属性清单 )  
         AS   SELECT   语句  
         WITH   CASCADED | LOCAL   CHECK   OPTION;
```
ALGORITHM是可选参数，表示视图选择的算法。

UNDEFINED：表示mysql将自动选择所使用的算法；

MERGE：表示将使用的视图语句与视图定义结合起来，使得视图定义的某一部分取代语句的对应部分；

TEMPTABLE：表示将视图的结果存入临时表，然后使用临时表执行语句。

属性清单是可选参数，指定视图中各个属性的名词。默认情况下与SELECT语句中查询的属性相同。

SELECT语句是一个完整的查询语句，表示从某个表查询满足条件的记录。

WITH CHECK OPTION是可选参数，表示更新视图时要保证在该视图的权限范围内。

CASCADED：表示更新视图时要满足所有相关视图和表的条件；

LOCAL：表示更新视图时要满足视图本身的定义条件即可。

注意：创建视图分为：1）从单表创建视图；2）从多表创建视图。

**3. 查看视图**
```
DESCRIBE   视图名;  
SHOW  TABLE  STATUS  LIKE   ‘视图名‘;  
SHOW  CREATE  VIEW   视图名;  
SELECT * FROM  information_schema.views;
```
前两种为查看视图的基本信息，后两种为查看视图的详细信息。

**4. 修改视图**

修改视图指修改视图中包含的表的字段。

1）CREATE OR REPLACE VIEW修改视图

    CREATE   OR  REPLACE  ALGORITHM = UNDEFINED | MERGE | TEMPTABLE  
                      VIEW   视图名   ( 属性清单 )  
                      AS   SELECT   语句  
                      WITH   CASCADED | LOCAL   CHECK   OPTION;

2）ALTER语句
```
    ALTER   ALGORITHM = UNDEFINED | MERGE | TEMPTABLE  
        VIEW   视图名   ( 属性清单 )  
        AS   SELECT   语句  
        WITH   CASCADED | LOCAL   CHECK   OPTION;
```
注意：

CREATE OR REPLACE VIEW可以修改现有的视图，也可以创建视图。而ALTER只能修改视图。

**5. 更新视图**

更新视图指通过视图来插入、更新和删除表中的数据。

因为视图是一个虚拟的表，其中没有数据。通过视图更新都是切换到基本表来更新。并且只能更新权限范围内的数据，超出范围则不能更新。

可以通过INSERT、UPDATE、DELETE更新。

注意：

以下这几种情况不能更新视图：

（1）视图中保护SUM()、COUNT()、MAX()、MIN()等函数；

（2）视图中包含UNION、UNION ALL、DISTINCT、GROUP BY、HAVING等关键字；

（3）常量视图；

（4）视图中的SELECT包含子查询；

（5）由不可更新的视图导出的视图；

（6）创建视图时，ALGORITHM为TEMPTABLE类型；

（7）视图对应的表上存在没有默认值的列，而且该列没有包含在视图中。

**6. 删除视图**

    DROP  VIEW  [IF EXISTS]   视图名列表   [RESTRICT | CASCADE];


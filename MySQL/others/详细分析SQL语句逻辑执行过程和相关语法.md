## 详细分析SQL语句逻辑执行过程和相关语法

来源：[http://www.cnblogs.com/f-ck-need-u/p/8656828.html](http://www.cnblogs.com/f-ck-need-u/p/8656828.html)

时间 2018-03-27 12:46:00

 
## 1.1 SQL语句的逻辑处理顺序
 
SQL语句的逻辑处理顺序，指的是SQL语句按照一定的规则，一整条语句应该如何执行，每一个关键字、子句部分在什么时刻执行。   
除了逻辑顺序，还有物理执行顺序。物理顺序是SQL语句真正被执行时的顺序(执行计划)，它是由各数据库系统的关系引擎中的语句分析器、优化器等等组件经过大量计算、分析决定的。   
很多时候因为优化的关系，使得语句最终的物理执行顺序和逻辑顺序不同。按照逻辑顺序，有些应该先执行的过程，可能优化器会指定它后执行。但不管是逻辑顺序还是物理顺序，设计了一条SQL语句，语句最后返回的结果不会也不能因为物理顺序改变了逻辑顺序而改变。   
其实，逻辑顺序只是为我们编写、理解SQL语句提供些帮助，除此之外，它毫无用处。而且，是不是真的存在一条严格且完整的执行顺序规则都是不确定的事情。虽然某些书上、网上给出了一些顺序(我个人所知道的比较权威的，是SQL Server的"圣书"技术内幕里介绍过)，但在任何一种数据库系统的官方手册上都没有关于这方面的介绍文档。   
SQL Server和Oracle在语句的逻辑处理顺序上是一致的，在这方面，它们严格遵守了标准SQL的要求，任何一个步骤都遵循了关系型数据库的范式要求。因为遵循了一些范式要求，导致标准SQL不允许使用某些语法。但是MySQL、MariaDB和它们小有不同，它们对标准SQL进行扩展，标准SQL中不允许使用的语法，在MySQL、MariaDB中可能可以使用，但很多时候这会违反关系模型的范式要求。   
虽然本文的最初目的是介绍MariaDB/MySQL语句的逻辑处理顺序，但在篇幅上对标准SQL介绍的更多，因为它们符合规范。理解这些规范，实际上是在理解关系模型和集合模型。本文也在多处通过这两个模型来分析为什么标准SQL不允许某些语法，以及为什么MySQL可以支持这些"不标准"的语法  。   
## 1.2 各数据库系统的语句逻辑处理顺序
 
以SELECT语句为例。   
### 1.2.1 SQL Server和Oracle的逻辑执行顺序
 
如下图：   
![][0]   
关于本图需要说明的是，虽然图中给出的顺序是DISTINCT比ORDER BY先执行，这也是网上流传的版本。但其实，在DISTINCT和ORDER BY之间的顺序没有严格的界限，甚至ORDER BY的顺序要优先于DISTINCT  。 后文会分析为什么。而且刚刚去翻了下sql server技术内幕中关于逻辑处理顺序的内容，发现它没有对DISTINCT执行位置进行排序，只是在介绍ORDER BY时提了下DISTINCT，我想也是因为DISTINCT和ORDER BY之间没有严格的顺序。   
后面关于MySQL和mariadb的两张逻辑顺序图中，将会把DISTINCT和ORDER BY的顺序调换过来。   
以下是对上述逻辑执行顺序的描述：   
(1).首先从FROM语句中获取要操作的表并计算笛卡尔积。如果有要联接的表，则还获取联接表。对它们计算笛卡尔积，笛卡尔积的结果形成一张虚拟表vt1。   
这里就体现了物理顺序和逻辑顺序的一个不同点：按照逻辑顺序，在执行SQL语句之初总是会进行笛卡尔积的计算，如果是两张或多张非常大的表，计算笛卡尔积是非常低效的行为，这是不能容忍的。所以物理顺序会进行一些优化决定，比如使用索引跳过一部分或整个笛卡尔积让计算变得很小。   
(2).对虚拟表vt1执行ON筛选语句，得到虚拟表vt2。   
(3).根据联接类型，将保留表的外部行添加到vt2中得到虚拟表vt3。   
(4).对vt3执行where条件筛选，得到虚拟表vt4。   
(5).执行分组，得到虚拟表vt5。   
注意，分组之后，整个SQL的操作上下文就变成了分组列，而不再是表中的每一列，后续的一切操作都是围绕所分的组作为操作对象进行操作的。也就是说，不在分组列表中的列不能在后续步骤中使用。例如，使用"group by a"对a列分组，那么后续的select列表中就不能使用b列，除非是对b列进行分组聚合运算。SQL Server、Oracle和MariaDB、Mysql最大的区别就在于此步，后两者可以引用分组列以外的列。   
(6).对vt5执行集合操作cube或者rollup，得到虚拟表vt6。   
(7).对分组的最终结果vt6执行having筛选，得到虚拟表vt7。   
(8).根据给定的选择列列表，将vt7中的选择列插入到虚拟表vt8中。   
注意，选择列是" **`同时性操作  " `**  ，在选择列中不能使用列别名来引用列表中的其他列。例如 select  col1+ 1 as  a,a+ 1 as  bfrom  t1   是错误的，因为"col1+1"和"a+1"之间没有执行上的先后顺序，所以它认为"a+1"中的a列是不存在的。   
(9).对vt8进行窗口分组相关的计算，得到虚拟表vt9。   
(10).对vt9按照指定的列去除重复行，得到虚拟表vt10。   
这一步是将数据复制到内存中相同的临时表结构中进行的，不过该临时表多出了一个唯一性索引列用来做重复消除。   
(11).对vt10进行排序，排序后的表为虚拟表vt11。   
(12).从vt11中根据top条件挑出其中满足的行，得到虚拟表vt12。   
如果没有应用order by，则记录是无序的集合，top挑出的行可能是随机的。也因此top一般要和order by字句一起使用。   
(13).将vt12从服务端返回给客户端作为最终查询结果。   
### 1.2.2 MariaDB的逻辑执行顺序
 
如下图：   
![][1]   
MariaDB中，使用的是LIMIT子句实现和TOP子句一样的功能：限制输出行数。且它不支持"WITH CUBE"(直接忽略该关键词)。   
但和SQL Server、Oracle最大的不同是对SELECT列表的处理。在MS SQL和Oracle中，select_list是在group by和having子句之后才进行的，这意味着group by分组后，不能在select_list中指定非分组列(除非聚合运算)，反过来看，在group by中不能使用select_list中的别名列。   
但在MariaDB和MySQL中，select_list是在group by之前进行的。在group by中能够引用select_list中的列，在select_list中也能指定非分组列。   
mariadb和mysql在这一点上实际上是"不规范"的，因为它违背了数据库的设计范式。详细内容在后文分析。   
### 1.2.3 MySQL的逻辑执行顺序
 
如下图：   
![][2]   
和MariaDB之间并没有什么区别，仅仅只是MySQL不支持开窗函数over()。   
但是注意，从mysql 5.7.5开始，已经默认设置了 sql_mode=  ONLY_FULL_GROUP_BY   ，这意味着MySQL默认也将遵循SQL规范，对于那些非分组列又没有进行聚合的列，都不允许出现在select_list中，除非select_list中使用的列是主键或者唯一索引列，之所以允许这样的行为，是因为有功能依赖性决定了它可以这样做，由此保证"规范性"。同样的，为何不规范的问题见后文。   
## 1.3 关于表表达式和虚拟表
 
派生表、CTE(公用表表达式，有的数据库系统支持)、视图和表函数都是表，我们常称之为"表表达式"，只不过它们是虚拟表(这里的虚拟表和上面逻辑执行过程中产生的虚拟表vt不是同一个概念)。它们都必须满足成为表的条件，这也是为什么定义表表达式的时候有些语法不能使用。   
从关系模型上去分析。表对应的是关系模型中的关系，表中的列对应的是关系模型中的元素。   
一方面，关系和元素都需要有唯一标识的名称，因此表和列也要有名称，即使表表达式也如此    。像派生表是嵌套在语句中的，无法在外部给它指定表明，因此必须为它指定一个表别名。同理，表表达式中的别名也一样，必须唯一且必须要有。   
另一方面，关系中的元素是无序的，因此表和表表达式中的数据也应当是无序的    。虽然有些表表达式中可以使用ORDER BY子句，但这时候的ORDER BY只是为了让TOP/LIMIT子句来挑选指定数量的行，并不是真的会对结果排序。也就是说表表达式挑选出来的行就像表一样，其内数据行仍然是无序的，以后访问它们的时候是按照物理存储顺序进行访问的，即使表表达式的定义语句中使用了ORDER BY子句。   
关于数据的无序性和随机性，见下文。   
这里还请区分表表达式(虚拟表)和逻辑执行过程中我们想象出来的虚拟表。表表达式是实实在在符合关系模型的表，即使它可能只是一条或几条语句，也不会将相关数据行进行物理的存储，但在关系引擎看来，它就是表。而逻辑执行过程中我们想象出来的虚拟表，只是为了方便理解而描述出来的，实际上不会有这样的表，它们只是按一定规则存放在内存中的一些数据行，虽然某些步骤中可能也会使用系统自建的临时表存放中途的数据，但它们不是表。   
## 1.4 关于表别名和列别名
 
在SQL语句中，我们避免不了要对表、列使用别名进行引用。关于别名，需要注意两点：   
(1).定义了表别名后，在语句中对该表的引用都必须使用别名，而不能使用原表名。   
(2).引用别名时，注意查询的逻辑处理过程。在某一阶段只能引用该阶段前面阶段定义的别名，使用该阶段后才定义的别名将报错。   
例如下面的两个查询语句，第一个错误原因是不能引用原表名，第二个错误是因为WHERE阶段不能引用SELECT阶段定义的字段别名。   
```sql

SELECT Student.Name FROM Student AS 学生表
SELECT Name,Sex AS 性别 FROM Student WHERE 性别 = '男'

```
 
下面是正确的写法。   
```sql

SELECT 学生表.Name FROM Student AS 学生表
SELECT Name,Sex AS 性别 FROM Student WHERE Sex = '男'

```
 
## 1.5 关于数据无序性和ORDER BY
 
在关系型数据库中，必须时刻都铭记在心的是"集合元素是无序"的，体现在数据库中就是"表中数据行是无序的"，除非建立了相关索引。   
出于集合模型的考虑，像我们平时看到的有行、有列的二维表数据(下图左边)，更应该看作是下图右边的结合结构，因为集合是无序的。   
![][3]   
由于数据无序，导致检索数据时都是按照存储时的物理顺序进行访问，如此检索得到的数据行都是随机而不保证任何顺序的，除非指定了 ORDER BY子句。而使用 ORDER BY查询得到的结果，它因为有序而不满足集合的概念。实际上 ORDER BY生成的是一个游标结果。了解 SQL的人，都知道能不用游标就尽量不用游标，因为它的效率相比符合集合概念的SQL语句来说，要慢很多个数量级。但也不能一棍子将其打死，因为有时候使用游标确实能比较容易达到查询目标。   
在 SQL中没有使用 ORDER BY时，有不少子句的返回结果 (虚拟表 )都是随机的，因为实在没办法去保证顺序，但却又要求返回数据。例如直接进行 SELECT * from  t;     ，再例如TOP/LIMIT子句。     
纵观整个 SQL的各个环节，不难发现很多时候获取随机行数据是不应该的，因为这种不确定性，让我们操作数据时显得更出乎意料、更危险  。   因此，除非不得不显示随机数据，标准SQL都会通过一些手段让获取随机数据的行为失败，而且在可能获取随机数据的时候，一般都会给出相关的建议和提示。   
MySQL、 mariadb之所以和 sql server、 oracle的语法相差那么大，归根结底就是对待关系型数据库的范式要求和随机数据的态度不同。 MySQL、 mariadb总是 "偷奸耍滑 "，在本无法满足关系型数据库范式的时候，它们总是挑选一个随机单行数据出来，让返回结果满足范式要求，最典型的就是 group by的处理方式  。                   不过 MySQL从5.7.5版本开始，已经逐渐走向规范化了。   
这里并非是要否认 mysql、 mariadb的设计模式，正所谓每个数据库系统都有自己对标准 SQL的扩展方式， MySQL只是走了一条和标准 SQL不同的路而已。而且关系模型的范式本就是人为定义的，为何不能违反呢？甚至可以说，表所满足的范式越强，检索表时的性能越低，nosql就没有关系模型的范式要求。     
在后文，将在多处分析标准 SQL为什么不允许某些语法，同时还会提到 MySQL和 mariadb是如何 "偷奸耍滑"的。   
## 1.6 关于TOP(或LIMIT)和ORDER BY
 
TOP和LIMIT是限制输出行数量，它们挑选数据行时是随机的(根据物理访问顺序)，所以得到的结果也是随机的。因此，建议TOP/LIMIT和ORDER BY一起使用。但即使如此，仍是不安全的。例如，ORDER BY的列中有重复值，那么TOP/LIMIT的时候如何决定获取哪些行呢？见如下LIMIT的示例(TOP也一样)：   
```sql

MariaDB [test]> select * from Student order by age;
+------+----------+------+--------+
| sid  | name     | age  | class  |
+------+----------+------+--------+
|    6 | zhaoliu  |   19 | Java   |
|    4 | lisi     |   20 | C#     |
|    8 | sunba    |   20 | C++    |
|    3 | zhangsan |   21 | Java   |
|    5 | wangwu   |   21 | Python |
|    1 | chenyi   |   22 | Java   |
|    7 | qianqi   |   22 | C      |
|    2 | huanger  |   23 | Python |
|    9 | yangjiu  |   24 | Java   |
+------+----------+------+--------+
 
MariaDB [test]> select * from Student order by age limit 9;
+------+----------+------+--------+
| sid  | name     | age  | class  |
+------+----------+------+--------+
|    6 | zhaoliu  |   19 | Java   |
|    4 | lisi     |   20 | C#     |
|    8 | sunba    |   20 | C++    |
|    3 | zhangsan |   21 | Java   |
|    5 | wangwu   |   21 | Python |
|    1 | chenyi   |   22 | Java   |
|    7 | qianqi   |   22 | C      |
|    2 | huanger  |   23 | Python |
|    9 | yangjiu  |   24 | Java   |
+------+----------+------+--------+

```
 
从两次查询结果中看到，即使都是对age进行升序排列，但age=20的两行前后顺序不一致，age=22的行顺序也不一致。   
因此一般会给另一个建议，为了确保数据一定是符合预期的，在order by中应该再加一列(最好具有唯一性)作为决胜属性，例如对age排序后再按照sid排序，这样就能保证返回结果不是随机的。   
```sql

select * from Student order by age,sid;
select * from Student order by age,sid limit 9;

```
 
## 1.7 关于DISTINCT和GROUP BY
 
DISTINCT子句用于消除select_list列的重复行，这很容易理解。大多数情况下，DISTINCT子句在功能上都可以认为等价于group by子句。有些DISTINCT不适合做的操作，可以在GROUP BY中来完成。   
例如下面两个SQL语句是等价的：   
```sql

select distinct class,age from Student; 
select class,age from Student group by class,age;

```
 
正因为等价，很多时候对DISTINCT的优化行为总是和GROUP BY的优化行为一致。以下是sql server上对上述两条语句的执行计划：   
```sql

select distinct class,age from Student;
  |--Sort(DISTINCT ORDER BY:([test].[dbo].[Student].[class] ASC, [test].[dbo].[Student].[age] ASC))
       |--Table Scan(OBJECT:([test].[dbo].[Student]))
 select class,age from Student group by class,age;
  |--Sort(DISTINCT ORDER BY:([test].[dbo].[Student].[class] ASC, [test].[dbo].[Student].[age] ASC))
       |--Table Scan(OBJECT:([test].[dbo].[Student]))

```
 
从结果中看到，执行DISTINCT去除重复行时，默认就带有了排序过程。实际上，DISTINCT几乎总是会将数据复制到内存中的一张临时表中进行，该临时表的结构和前面得到的虚拟表字段结构几乎一致，但却多了一个唯一性索引列用来做重复消除。   
但如果DISTINCT结合GROUP BY子句呢？其实不建议这么做。这里也不讨论这种问题。   
## 1.8 关于DISTINCT和ORDER BY
 
既然DISTINCT默认就带了排序行为，那此时再指定ORDER BY会如何？例如下面的语句：   
```sql

select distinct class,age from Student ORDER BY age desc; 

```
 
在SQL Server中的执行计划如下：   
```sql

select distinct class,age from Student ORDER BY age desc;
  |--Sort(DISTINCT ORDER BY:([test].[dbo].[Student].[age] DESC, [test].[dbo].[Student].[class] ASC))
       |--Table Scan(OBJECT:([test].[dbo].[Student]))

```
 
其实和前面没什么区别，无非是先对order by列进行排序而已。但是从这里能看出，DISTINCT和ORDER BY字句其实没有严格的逻辑执行先后顺序，甚至ORDER BY指定的排序列还优先于DISTINCT的排序行为。   
但是，DISTINCT和ORDER BY结合时，order by的排序列是有要求的：排序列必须是select_list中的列(distinct很多时候都可以看作group by)。例如select distinct  a,bfrom  torder by  c;   是错误的。但MySQL和mariadb又在这里进行了扩展，它们的排序列允许非select_list中的列。   
先说标准SQL为何不允许使用非select_list中的列，这归根结底还是关系型数据库的范式问题。假如DISTINCT消除了部分列的重复值，最终将只返回一条重复记录，而如果使用非select_list的列排序，将要求返回一条重复记录的同时还要返回每个重复值对应的多条记录以便排序，而在要求范式的关系表中是无法整合这样的结果。   
例如表中数据如下：   
```sql

MariaDB [test]> select sid,age,class from Student order by class;
+------+------+--------+
| sid  | age  | class  |
+------+------+--------+
|    7 |   22 | C      |
|    4 |   20 | C#     |
|    8 |   20 | C++    |
|    1 |   22 | Java   |
|    3 |   21 | Java   |
|    6 |   19 | Java   |
|    9 |   24 | Java   |
|    2 |   23 | Python |
|    5 |   21 | Python |
+------+------+--------+

```
 
现在对class列进行去重。   
```sql

MariaDB [test]> select distinct class from Student order by class;
+--------+
| class  |
+--------+
| C      |
| C#     |
| C++    |
| Java   |
| Python |
+--------+

```
 
现在假设order by的排序列能使用sid进行排序。那么期待的结果将是根据如下数据进行返回的：   
```sql

select distinct class from Student order by sid;
+------+--------+
| sid  | class  |
+------+--------+
|    7 | C      |
+---------------+
|    4 | C#     |
+---------------+
|    8 | C++    |
+---------------+
|    1 |        |
|    3 | Java   |
|    6 |        |
|    9 |        |
+---------------+
|    2 |        |
|    5 | Python |
+------+--------+ 

```
 
这样的结构已经违反了关系型数据库的范式要求。因此，sql server和oracle会直接对该语句报错。   
但是MySQL/mariadb就允许在order by中使用非select_list列进行排序。它们是如何"偷奸耍滑"的呢？还是上面违反关系模型范式的数据结构，MySQL和mariadb会从Java和Python对应的sid中挑选第一行(order by已经对其排序，因此不是随机数据)，然后和Java、Python分别组成一行，得到如下虚拟表：   
```sql

+------+--------+
| sid  | class  |
+------+--------+
|    7 | C      |
+---------------+
|    4 | C#     |
+---------------+
|    8 | C++    |
+---------------+
|    1 | Java   |
+---------------+
|    2 | Python |
+------+--------+ 

```
 
然后将此虚拟表中非select_list中的列都去掉，得到最终结果。真的是最终结果吗？   
```sql

MariaDB [test]> select distinct class from Student order by sid; 
+--------+
| class  |
+--------+
| Java   |
| Python |
| C#     |
| C      |
| C++    |
+--------+

```
 
虽然返回的结果内容上和前面分析的一致，但是顺序却不一致，影响因素就是"order by sid"。   
其实认真观察结果，很容易就发现它们是根据sid排序后再对class去重得到的结果。也就是说， **`ORDER BY子句比DISTINCT子句先执行了`**    。稍稍分析一下，这里先以sid排序，得到如下虚拟结果：   
```sql

+--------+------+
| class  | sid  |
+--------+------+
| Java   |    1 |
| Python |    2 |
| Java   |    3 |
| C#     |    4 |
| Python |    5 |
| Java   |    6 |
| C      |    7 |
| C++    |    8 |
| Java   |    9 |
+--------+------+

```
 
再对class去重，得到如下虚拟结果：   
```sql

+------+--------+
| sid  | class  |
+---------------+
|    1 |        |
|    3 | Java   |
|    6 |        |
|    9 |        |
+------+--------+
|    2 |        |
|    5 | Python |
+------+--------+
|    4 | C#     |
+---------------+
|    7 | C      |
+---------------+
|    8 | C++    |
+---------------+

```
 
最后去掉非select_list中的列sid，得到最终结果。   
## 1.9 关于标准SQL的GROUP BY
 
如果让我给SQL语句的逻辑执行顺序划分为两段式，我会将"三八线"划在GROUP BY这里。因为在GROUP BY之前甚至完全没有GROUP BY子句的语句部分，操作的对象都是表中的每行数据，也就是说操作的上下文环境是表的数据行。而在GROUP BY之后，操作的对象是组而不再是行，也就是说操作的上下文将从表中的数据行变成组。   
直白一点说，GROUP BY之前，关系引擎的目光集中在数据行的细节上，GROUP BY之后，关系引擎的目光则集中在组上。至于每个分组中的行，对关系引擎来说是透明的，它不在乎组中行这种细节性的东西是否存在，而且按照关系模型的要求，也不应该认为它们存在。注意，这里说的是标准SQL，而MySQL和mariadb又"偷奸耍滑"去了。   
举个例子就很容易理解GROUP BY前后侧重点的变化过程。   
以下是Student表的内容。   
```sql

MariaDB [test]> select * from Student;
+------+----------+------+--------+
| sid  | name     | age  | class  |
+------+----------+------+--------+
|    1 | chenyi   |   22 | Java   |
|    2 | huanger  |   23 | Python |
|    3 | zhangsan |   21 | Java   |
|    4 | lisi     |   20 | C#     |
|    5 | wangwu   |   21 | Python |
|    6 | zhaoliu  |   19 | Java   |
|    7 | qianqi   |   22 | C      |
|    8 | sunba    |   20 | C++    |
|    9 | yangjiu  |   24 | Java   |
+------+----------+------+--------+

```
 
现在按照class进行分组。下面是分组后经过我加工的表结构：   
![][4]   
其中第一列是分组得到的结果，我把它和原表的数据结合在一起了。注意，这是一个不符合关系模型范式要求的结构。   
在分组前，关系引擎会对sid、name、age和class列的每一行进行筛选。但是分组后，关系引擎只看得到第一列，也就是class列，而sid、name和age列被直接忽略，因此无法引用它们。   
关于GROUP BY，有以下两个问题：   
1.为什么分组之后涉及到对组的操作时只允许返回标量值？   
标量值即单个值，比如聚合函数返回的值就是标量值。在分组之后，组将成为表的工作中心，一个组将成为一个整体，所有涉及到分组的查询，将以组作为操作对象。组的整体是重要的，组中的个体不重要，甚至可以理解为分组后只有组的整体，即上图中左边加粗的部分，而组中的个体是透明的。   
以上图中的第一条记录举一个通俗的例子。在分组以前，知道了该学生的姓名"chenyi"之后，关注点可能要转化为它的主键列sid值"1"，因为主键列唯一标识每一行，知道了主键值就知道了该行的所有信息。而在分组之后，关注的中心只有分组列class，无论是知道姓名"chenyi"还是学号"1"都不是关注的重点， **`重点是该行记录(集合的元素)是属于"Java"班级的`**    。   
这就能解释为什么只能以组作为操作对象并返回标量值。例如，在分组之后进行SUM汇总，将以"Java"班作为一个汇总对象，以"Python"班作为另一个汇总对象，汇总的将是每个分组的总值，而不是整个表的总值，并且汇总的值是一个标量值，不会为组中的每行都返回这个汇总值。否则就违反了关系模型的范式。   
2.为什么分组之后只能使用GROUP BY列表中的列，如果不在GROUP BY列表中，就必须进行聚合？   
分组后分组列成为表的工作中心，以后的操作都必须只能为组这个整体返回一个标量值。   
如果使用了非分组列表的列，将不能保证这个标量值。例如，分组后对"Java"班返回了一个汇总值，假如同时要使用sid列和name列，因为这两列没有被聚合或分组，因此只能为这两列的每个值返回一行，也就是说在返回汇总标量值的同时还要求返回"Java"班组中的每一行，要实现这样的结果，需要整合为如上图所示的结果，但在关系表中这是违反规范的。正如前文介绍的DISTINCT一样，ORDER BY的排序列只能使用DISTINCT去重的select_list列表。   
因此，分组后只能使用分组列表中的列。如果要使用非分组列表中的列，应该让它们也返回一个标量值，只有这样才能实现分组列和非分组列结果的整合。    例如，下面的语句将会产生错误，因为select_list在GROUP BY阶段后执行，且select_list中的列没有包含在GROUP BY中，也没有使用聚合函数。   
```sql

SELECT sid,name FROM Student GROUP BY class;

```
 
事实上从严格意义上看待这条语句，它没有实现分组的意义：既然不返回分组列的分组结果，那为什么还要进行分组呢？   
其实，无论是标准SQL还是MySQL、mariadb，执行group by子句时都会表扫描并创建一个临时表(此处为了说明group by的特性，不考虑group by使用索引优化的情况)，这个临时表中只有group by的分组列，没有那些非分组列。这也是前面说group by之后，关系引擎的目光从行转为组的真正原因。    由此，已经足够说明为什么select_list中不能使用非group by的分组列。   
## 1.10 关于MySQL/MariaDB的GROUP BY
 
MySQL和mariadb的GROUP BY有几个扩展特性(都是标准SQL不支持的)：(1).能够在group by中使用列别名;(2).可以在select_list中使用非分组列;(3).可以在group by子句中指定分组列的升序和降序排序  。   下面分别说明这些特性。   
(1).group by **`中能够使用列别名。`**    
其实对于MySQL和mariadb而言，并非是有一个专门的select_list筛选过程，使得筛选完成后，后续的步骤就能使用这些筛选出来的列。而是从WHERE子句筛选了行之后，后面所有的过程都可以对select_list进行检索扫描。其中ORDER BY子句扫描select_list的时候是先检索出列表达式，再检索所引用表中的列，直到找出所有的排序列；而GROUP BY和HAVING子句则是先检索表中的列，再检索列表达式，直到找出所有的分组列。    因此，MySQL、mariadb能够使用列别名。   
下面两个查询的例子很能说明问题：   
```sql

MariaDB [test]> set @a:=0;select sid,name,class,@a:=@a+1 as class from Student order by class;
+------+----------+--------+-------+
| sid  | name     | class  | class |
+------+----------+--------+-------+
|    1 | chenyi   | Java   |     1 |
|    2 | huanger  | Python |     2 |
|    3 | zhangsan | Java   |     3 |
|    4 | lisi     | C#     |     4 |
|    5 | wangwu   | Python |     5 |
|    6 | zhaoliu  | Java   |     6 |
|    7 | qianqi   | C      |     7 |
|    8 | sunba    | C++    |     8 |
|    9 | yangjiu  | Java   |     9 |
+------+----------+--------+-------+
 
MariaDB [test]> set @a:=0;select sid,name,class,@a:=@a+1 as class from Student group by class;
+------+---------+--------+-------+
| sid  | name    | class  | class |
+------+---------+--------+-------+
|    7 | qianqi  | C      |     7 |
|    4 | lisi    | C#     |     4 |
|    8 | sunba   | C++    |     8 |
|    1 | chenyi  | Java   |     1 |
|    2 | huanger | Python |     2 |
+------+---------+--------+-------+

```
 
上面两个查询中，表达式@a  := @a + 1    的别名为class，和Student表中的class列重复。在第一个查询中，使用order by对class排序，由于order by先从select_list中的列表达式开始检索，因此这个排序列class是 @a  := @a + 1    对应的列，结果也正符合此处的分析。第二个查询中，使用group by对class进行分组，因为它先检索表的字段名，因此这个分组列class是Student中的class列，结果也同样符合此处的分析。   
但是，在标准SQL中这是不允许的行为。虽然在select_list中出现两个同名的列名称是允许的，但是在引用列别名的时候，无论是group by还是order by子句或其他子句，都认为同列名会导致二义性。标准SQL严格遵循select_list是"同时性的"，引用列的时候无法像mysql/mariadb一样分先后顺序地检索select_list。   
![][5]   
(2). **`在group by子句中可以指定分组列的升序和降序排序。`**    
无论是标准SQL还是MySQL、mariadb，group by分组的时候，都会按照分组列升序排序。只不过标准SQL中只能使用默认的升序，而MySQL、mariadb可以自行指定排序方式。   
例如：   
```sql

MariaDB [test]> select class from Student group by class;
+--------+
| class  |
+--------+
| C      |
| C#     |
| C++    |
| Java   |
| Python |
+--------+

```
 
![][6]   
很明显，结果中是按照分组列class进行升序排序的。   
在MySQL、mariadb中可以为group by子句指定排序方式。而MS SQL和Oracle不允许。   
```sql

MariaDB [test]> select class from Student group by class desc;
+--------+
| class  |
+--------+
| Python |
| Java   |
| C++    |
| C#     |
| C      |
+--------+

```
 
不过MS SQL和Oracle也能实现同样的功能，只需使用ORDER BY即可。   
![][7]   
请记住，GROUP BY子句默认会进行排序，这一点很重要。   
(3). **`在select_list中可以使用非分组列。`**    
MySQL和MariaDB在这里又"偷奸耍滑"了。   
如下查询：   
```sql

MariaDB [test]> select * from Student group by class;
+------+---------+------+--------+
| sid  | name    | age  | class  |
+------+---------+------+--------+
|    7 | qianqi  |   22 | C      |
|    4 | lisi    |   20 | C#     |
|    8 | sunba   |   20 | C++    |
|    1 | chenyi  |   22 | Java   |
|    2 | huanger |   23 | Python |
+------+---------+------+--------+

```
 
上一小节分析了标准SQL的group by的特性，select_list中本无法使用非分组列，但这里却能使用，为什么呢？仍然使用上一小节加工后的数据结构来说明：   
![][8]   
标准SQL中之所以不能使用sid、name和age列，是因为group by的每个分组都是单行(标量)结果，如果使用了这些列，会违反关系模型的范式要求(一行对多行)。而MySQL、mariadb之所以允许，是因为它们会从重复的分组列中挑出一个随机行(注意随机这个字眼)，将它和分组列的单行组成一行，这样就满足范式要求了。   
例如上图中的Java组对应了4行记录，MySQL可能会挑sid=1(按照物理存储顺序挑，因此结果是随机的)的那行和Java组构成一行，Python组对应了2行记录，MySQL可能会挑sid=2的那行和Python构成一行。于是得到结果：   
```sql

+------+---------+------+--------+
| sid  | name    | age  | class  |
+------+---------+------+--------+
|    7 | qianqi  |   22 | C      |
|    4 | lisi    |   20 | C#     |
|    8 | sunba   |   20 | C++    |
|    1 | chenyi  |   22 | Java   |
|    2 | huanger |   23 | Python |
+------+---------+------+--------+

```
 
MySQL和MariaDB用了一种不是办法的办法解决了关系模型的范式要求问题，使得select_list中能够使用非分组列。但因为挑选数据的时候具有随机性，因此不太建议如此使用。除非你知道自己在做什么，或者额外使用了ORDER BY子句保证挑选的数据是意料之中的。   
```sql

MariaDB [test]> select * from Student1 group by class order by sid desc;
+------+----------+------+--------+
| sid  | name     | age  | class  |
+------+----------+------+--------+
|    8 | sunba    |   20 | C++    |
|    7 | qianqi   |   22 | C      |
|    4 | lisi     |   20 | C#     |
|    3 | zhangsan |   21 | Java   |
|    2 | huanger  |   23 | Python |
+------+----------+------+--------+

```
 
## 1.11 关于OVER( )
 
想必写过GROUP BY子句的人都很恼火选择列中不能使用非分组列，明明很想查看分组后所有行的结果，GROUP BY却阻止了这样的行为。   
万幸，还有一个OVER()子句供我们实现目标。不过MySQL中不支持OVER()子句，ms sql、Oracle和mariaDB(MariaDB 10.2.0开始引入该功能)都支持，之所以MySQL不支持，我想是因为它的GROUP BY本就允许select_list中使用非分组列。   
over()子句常被称为窗口函数或开窗函数，其实它就是进行分组，分组后也能进行聚合运算。只不过在over()的世界里，组称为窗口。   
例如，以下是按照StudentID列进行分组。   
![][9]   
其实从上面的分组形式上看，它和GROUP BY分组的不同之处在于GROUP BY要求每个分组必须返回单行，而开窗则可以将单行数据同时分配给多个行，从而构成一个窗口。group by的侧重点是组，而开窗的侧重点在于组中的每行。   
窗口函数很强大，强大到仅仅这一个专题就可以写成一本书。本文不会对其多做描述，而是围绕本文的主题"语句的逻辑执行顺序"稍作分析。   
over()子句是对数据行按照指定列进行开窗(划分窗口)，开窗后可以围绕每一组中的行进行操作，例如排序、聚合等等。   
假如先执行DISTINCT去重再执行OVER，那么去重后再对具有唯一值的列(或多列)进行开窗就没有任何意义。例如上图中，如果先对StudentID去重，那么去重后将只有3行，这3行都是唯一值，没必要再去开窗，而且这也不符合开窗的目的。   
因此OVER()是在DISTINCT之前完成开窗的。   
另外，建议DISTINCT不要和OVER()一起使用，因为这时候的DISTINCT根本没有任何作用，但却会消耗额外的资源。   
如果真的想对某些列去重后再开窗，可以借助GROUP BY。因为DISTINCT的功能基本等价于GROUP BY，但GROUP BY却先执行。   
## 1.12 总结
 
虽然SQL语句的逻辑处理过程和真正的执行计划在有些地方会有所不同。但是理解逻辑处理过程，对学习SQL很有帮助。   
回顾全文，不难发现MySQL、MariaDB对SQL的扩展实现了不少标准SQL中不允许的语法。能够实现这样的行为，是因为MySQL/mariadb总是通过获取一个随机行的行为保证结果满足关系模型的范式要求。也正因为这样，使得看上去mysql/mariadb的语法和标准SQL的语法没什么大区别，连逻辑执行顺序都基本一致，但它们却会对其他子句产生连带反应，导致最终的执行结果不一致。   
虽然实际编写SQL语句的过程中，无需去在意这其中的为什么，但我个人觉得，理解它们很有帮助，毕竟关系型数据库的本质在于关系模型和集合模型。而且在我自己的体会中，在深入学习SQL的过程中，经常会感受到SQL和关系、集合之间的联系，这种感受可能不会立刻被自己发现，但回首一想，还真是那么回事。   
  
[ 回到Linux系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7048359.html ][10]  [ 回到网站架构系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7576137.html ][11]  [ 回到数据库系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7586194.html ][12]   [ 转载请注明出处：http://www.cnblogs.com/f-ck-need-u/p/8656828.html ][13]       
 
 
注：若您觉得这篇文章还不错请点击右下角推荐，您的支持能激发作者更大的写作热情，非常感谢！   


[10]: http://www.cnblogs.com/f-ck-need-u/p/7048359.html
[11]: http://www.cnblogs.com/f-ck-need-u/p/7576137.html
[12]: http://www.cnblogs.com/f-ck-need-u/p/7586194.html
[13]: http://www.cnblogs.com/f-ck-need-u/p/8656828.html
[0]: ./img/N3emaeu.png 
[1]: ./img/Yvamam2.png 
[2]: ./img/yiAFJjv.png 
[3]: ./img/mYjMRfv.png 
[4]: ./img/bAVbeaQ.png 
[5]: ./img/F7fqEfN.png 
[6]: ./img/ZZJJzqq.png 
[7]: ./img/Ir6j63Z.png 
[8]: ./img/bMziA3Q.png 
[9]: ./img/reeaimf.png 
# [MySQL3：索引][0]


**什么是索引**

索引是对数据库表中一列或者多列的值进行排序的一种结构，所引用于快速找出在某个列中有一特定值的行。不使用索引，MySQL必须从第一条记录开始读完整个表，直到找出相关的行。表越大，查询数据所花费的时间越多，如果表中查询的列有一个索引，MySQL能快速到达一个位置去搜索数据文件，而不必查看所有数据。

**索引的含义和特点**

索引是一个单独的、存储在磁盘上的数据库结构，它们包含着对数据表里所有记录的引用指针。使用索引用于快速找出在某个或多个列中有一特定值的行，所有MySQL列类型都可以被索引，对相关列使用索引是提高查询操作速度的最佳途径。

例如，数据库里面有20000条记录，现在要执行这么一个查询：SELECT * FROM table where num = 10000。如果没有索引，必须遍历整个表，直到num等于10000的这一行被找到为止；如果在num列上创建索引，MySQL不需要任何扫描，直接在索引中找10000，就可以得知值这一行的位置。可见，索引的建立可以提高数据库的查询速度。

索引是在存储引擎中实现的，因此，每种存储引擎的索引都不一定完全相同，并且每种存储引擎也不一定支持所有索引类型。所有存储引擎支持每个表至少16个索引，总索引长度至少为256字节。大多数存储引擎有更高的额限制，MySQL中索引的存储类型有两种：BTREE和HASH，具体和表的存储引擎相关；MyISAM和InnoDB存储引擎只支持BTREE索引，MEMORY/HEAP存储引擎可以支持HASH和BTREE缩影。

索引的优点主要有：

1、通过创建唯一索引，可以保证数据库表中每一行数据的唯一性

**2、****可以大大加快数据的查询速度，这也是创建索引最主要的原因**

3、在实现数据的参考完整性方面，可以加速表和表之间的连接

4、在使用分组和排序子句进行数据查询时，也可以显著减少查询中分组和排序的时间

增加索引也有许多不利的方面，比如：

1、创建索引和维护索引要耗费时间，并且随着数据量的增加所耗费的时间也会增加

2、索引需要占用磁盘空间，除了数据表占数据空间之外，每一个索引还要占一定的物理空间，如果有大量的索引，索引文件可能比数据文件更快达到最大文件尺寸

3、当对表中数据进行增加、删除和修改的时候，索引也要动态地维护，这样就降低了数据的维护速度

**索引的分类**

MySQL的索引可以分为以下几类：

1、普通索引和唯一索引

（1）普通索引是MySQL中的基本索引类型，允许在定义索引的列中插入重复值和空值

（2）唯一索引，索引列的值必须唯一，但允许有空值，主键索引是一种特殊的唯一索引，不允许有空值

2、单列索引和组合索引

（1）单列索引即一个索引只包含单个列，一个表可以有多个单列索引

（2）组合索引指在表的多个字段组合上创建的索引，只有在查询条件中使用了这些字段的左边字段时，索引才会被使用

3、全文索引

全文索引类型为FULLTEXT，在定义索引的列上支持值的全文查找，允许在这些索引列中插入重复值和空值。全文索引可以在CHAR、VARCHAR或者TEXT类型的列上创建，MySQL中只有MyISAM存储引擎支持全文索引

**索引的设计原则**

索引设计不合理或者缺少索引都会对数据库和应用程序的性能造成障碍，高效的索引对于获得良好的性能非常重要，设计索引时，应该考虑一下：

1、索引并非越多越好，一个表中如有大量的索引，不仅占用磁盘空间，而且会影响INSERT、DELETE、UPDATE等语句的性能，因为当表中的数据更改的同时，索引也会进行调整和更新

2、避免对经常更新的表设计过多的索引，并且索引中的列尽可能要少，而对经常用于查询的字段应该创建索引，但要避免添加不必要的字段

3、数据量小的表最好不要使用索引，由于数据较少，查询花费的时间可能比遍历索引时间还要短，索引可能不会产生优化效果

4、在条件表达式中经常用到的不同值较多的列上建立索引，在不同值较少的列上不要建立索引，比如性别字段只有男和女，就没必要建立索引。如果建立索引不但不会提高查询效率，反而会严重降低更新速度

5、当唯一性是某种数据本身的特征时，指定唯一索引。使用唯一索引需能确保定义的列的数据完整性，以提高查询速度

6、在频繁排序或分组（即group by或order by操作）的列上建立索引，如果待排序的列有多个，可以在这些列上建立组合索引

**创建表的时候创建索引**

使用CREATE TABLE创建表的时候，除了可以定义列的数据类型，还可以定义主键约束、外键约束或者唯一性约束，而不论创建哪种约束，在定义约束的同时相当于在指定列上创建了一个索引。创建表时创建索引的基本语法如下：

```sql
    CREATE TABLE table_name[col_name data_type]
    [UNIQUE|FULLTEXT|SPATIAL]
    [INDEX|KEY]
    [index_name](col_name[length])
    [ASC|DESC]
```
解释一下：

1、UNIQUE、FULLTEXT和SPATIAL为可选参数，分别表示唯一索引、全文索引和空间索引

2、INDEX和KEY为同义词，二者作用相同，用来指定创建索引

3、col_name为需要创建索引的字段列，该列必须从数据表中该定义的多个列中选择

4、index_name为指定索引的名称，为可选参数，如果不指定则MySQL默认col_name为索引值

5、length为可选参数，表示索引的长度，只有字符串类型的字段才能指定索引长度

6、ASC或DESC指定升序或者降序的索引值存储

下面创建一个普通索引，没有唯一性之类的限制，其作用只是加快对于数据的访问速度：

 
```sql
    CREATE TABLE book
    (
        bookId                        INT                        NOT NULL,
        bookName                    VARCHAR(255)    NOT NULL,
        author                        VARCHAR(255)    NOT NULL,
        info                            VARCHAR(255)    NOT NULL,
        year_publication    YEAR                    NOT NULL,
        INDEX(year_publication)
    )
```
确认一下索引是否正在使用，可以使用EXPLAIN：
```sql
    EXPLAIN select * from book where yead_publication = 1990
```
结果为：

![][1]

解释下字段的意思：

1、select_type行指定所使用的SELECT查询类型，这里值为SIMPLE，表示简单的SELECT，不使用UNION或者子查询。其他可能的取值有：PRIMARY、UNION、SUBQUERY等

2、table行指定数据库读取的数据表的名字，它们按照被读取的先后顺序排列

3、type行指定了本数据表与其他数据表之间的关联关系，可能的去只有system、const、eq_ref、ref、range、index和All

4、possible_keys行给出了MySQL在搜索数据记录时可选用的各个索引

5、key行是MySQL使用的实际索引

6、key_len行给出了索引按字节计算的长度，key_len数值越小，表示越快

7、ref行给出了关联关系中另外一个数据表里的数据列的名字

8、rows行是MySQL在执行这个查询时预计会从这个数据表里读出的数据行的个数

9、extra行提供了与关联操作有关的信息

看到，possible_keys和key的值都为year_publication，查询时使用了索引

**2、创建唯一索引**

唯一索引和普通索引类似，不过唯一索引索引列的值必须唯一，但允许有空值，如果是组合索引，则列值的组合必须唯一。看一下创建唯一索引的方式：
```sql
    CREATE TABLE uniquetable
    (
        id         INT             NOT NULL,
        name    CHAR(30)    NOT NULL,
        UNIQUE INDEX UniqIdx(id)
    )
```
这就在表的id字段上创建了一个名为UniqIdx的唯一索引

**3、创建单列索引**

单列索引是在数据表中的某一个字段上创建的索引，一个表中可以创建多个单列索引，前面两个例子中创建的索引都是单列索引，比如：
```sql
    CREATE TABLE singletable
    (
        id      INT         NOT NULL,
        name    CHAR(30)    NOT NULL,
        UNIQUE INDEX SingleIdx(name(20))
    )
```
这就在name字段上建立了一个名为SingleIdx的单列索引，索引长度为20

**4、创建组合索引**

组合索引是在多个字段上创建一个索引，比如：

```sql
    create table uniontable
    (
        id         INT                NOT NULL,
        name　　　　CHAR(30)    　　　　NOT NULL,
        age        INT                NOT NULL,
        info     　VARCHAR(255),
        INDEX UnionIdx(id, name, age)
    )
```
这就为id、name和age三个字段成功创建了一个名为UnionIdx的组合索引

**5、创建全文索引**

全文索引可以对全文进行搜索，只有MyISAM存储引擎支持全文索引，并且只为CHAR、VARCHAR和TEXT列，索引总是对整个列进行，不支持局部索引，比如：

```sql
    CREATE TABLE fulltexttable
    (
        id         INT                NOT NULL,
        name    CHAR(30)    NOT NULL,
        age        INT                NOT NULL,
        info    VARCHAR(255),
        FULLTEXT INDEX FullTxtIdx(info)
    )ENGINE=MyISAM
```
因为默认的存储引擎为InnoDB，而全文索引只支持MyISAM，所以这里创建表的时候要手动指定一下引擎。

看到这么创建，就在info字段上成功建立了一个名为FullTxtIdx的FULLTEXT全文索引，全文索引非常适合大型数据库，而对于小的数据集，它的用处可能比较小

**在已经存在的表上创建索引**

在已经存在的表上创建索引，可以使用ALTER TABLE语句或者CREATE INDEX语句，所以，分别讲解一下如何使用ALTER TABLE和CREATE INDEX语句在已知的表字段上创建索引。

**1、使用ALTER TABLE语句创建索引**

ALTER TABLE创建索引的基本语法为：
```sql
    ALTER TABLE table_name ADD [UNIQUE|FUUTEXT|SPATIAL]
    [INDEX|KEY] [index_name] (col_name[length],...) [ASC|DESC]
```
与创建表时创建索引的语法不同的是，这里用了ALTER TABLE和ADD关键字，ADD表示向表中添加索引。以book这张表为例，先看一下这张表里面有哪些索引：

    SHOW INDEX FROM book

看下结果：

![][2]

解释一下：

1、table表示创建索引的表

2、Non_unique表示索引不是一个唯一索引，1表示非唯一索引，0表示唯一索引

3、Key_name表示索引的名称

4、Seq_in_index表示该字段在索引中的位置，单列索引改值该值为1，组合索引为每个字段在索引中定义的顺序

5、Column_name表示定义索引的列字段

6、Sub_part表示索引的长度

7、Null表示该字段是否能为空值

8、Index_type表示索引类型

所以，book里面已经有一个索引了，是一个非唯一索引，现在给bookname字段加上索引，SQL语句如下：

    ALTER TABLE book ADD INDEX BoNameIdx(bookname(30));

再给bookId字段加上唯一索引，名称为UniqidIdx：

    ALTER TABLE book ADD UNIQUE INDEX UniqidIdx(bookId);

再给author字段加上单列索引：

    ALTER TABLE book ADD INDEX BkauthorIdx(author(50));

意思是查询的时候，只需要检索前面50个字符。这里专门提一下，对字符串类型的字段进行索引，如果可能应该指定一个前缀长度，例如，一个CHAR(255)的列，如果在前10个或者前30个字符内，多数值是唯一的，则不需要对整个列进行索引，短索引不仅可以提高查询速度而且可以节省磁盘空间、减少I/O操作

组合索引和全文索引和创建表时建立索引的方式差不多，就不写了，此时我们SHOW一下INDEX：

![][3]

**2、使用CREATE TABLE语句创建索引**

CREATE INDEX语句可以在已经存在的表上添加索引，MySQL中CREATE INDEX被映射到一个ALTER TABLE语句上，基本语法结构为：

    CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name ON table_name(col_name[length],...)[ASC|DESC]

看到和ALTER INDEX语句的语法基本一样，下面把book表删除了再创建，所有字段都没有索引，用CREATE INDEX语句创建一次索引：

 
```sql
    -- 为bookname字段建立名为BkNameIdx的普通索引
    CREATE INDEX BkNameIdx ON book(bookname);
    -- 为bookid字段建立名为UniqidIdx的唯一索引
    CREATE INDEX UniqidIdx ON book(bookid);
    -- 为author和info字段建立名为BkAuAndInfoIdx的组合索引
    CREATE INDEX BkAuAndInfoIdx ON book(author(20), info(50));
    -- 为year_publication字段建立名为BkyearIdx的普通索引
    CREATE INDEX BkyearIdx ON book(year_publication);
```
此时我们SHOW一下INDEX，可以看到为5个字段建立了4个索引：

![][4]

**删除索引**

最后一项工作就是删除索引了，可以使用ALTER TABLE和DROP INDEX删除索引。

**1、ALTER TABLE**

ALTER TABLE的基本语法为：

    ALTER TABLE table_name DROP INDEX index_name

比如把book的UniqidIdx给删除了：

    ALTER TABLE book DROP INDEX UniqidIdx;

这样就删除了book表中的UniqidIdx这个索引，可以SHOW INDEX from book查看一下，这里就不贴图了

**2、DROP INDEX**

DROP INDEX的基本语法为：

    DROP INDEX index_name ON table_name

比如我把BkAuAndInfoIdx这个组合索引给删了：

    DROP INDEX BkAuAndInfoIdx ON book

这样就把book表里面的BkAuAndInfoIdx这个组合索引给删除了。

注意一个细节，删除表中的列时，如果要删除的列为整个索引的组成部分，则该列也会从索引中删除；如果组成索引的所有列都被删除，则整个索引将被删除

[0]: http://www.cnblogs.com/xrq730/p/4940747.html
[1]: ./img/801753-20151105202202039-1301825051.png
[2]: ./img/801753-20151105212001821-303585575.png
[3]: ./img/801753-20151105213028414-747878549.png
[4]: ./img/801753-20151105214019961-1002371479.png
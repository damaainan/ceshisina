# [MySQL优化笔记][0]
<font face=微软雅黑>

可以从这些方面进行优化：

* 数据库(表)设计合理
* SQL语句优化
* 数据库配置优化
* 系统层、硬件层优化

查询优化、索引优化、库表结构优化

## 数据库设计

### 关系数据库三范式

1NF:字段不可分;  
2NF:有主键，非主键字段依赖主键;  
3NF:非主键字段不能相互依赖;

解释:  
1NF:原子性 字段不可再分,否则就不是关系数据库;  
2NF:唯一性 一个表只说明一个事物;  
3NF:每列都与主键有直接关系，不存在传递依赖;

    - 不符合第一范式的例子(关系数据库中create不出这样的表)： 
    【表】字段1, 字段2(字段2.1, 字段2.2), 字段3 ...... 
    【存在的问题】因为设计不出这样的表, 所以没有问题。
    
    - 不符合第二范式的例子: 
    【表】学号, 姓名, 年龄, 课程名称, 成绩, 学分; 
    这个表明显说明了两个事务:学生信息, 课程信息; 
    【存在问题】
    数据冗余，每条记录都含有相同信息； 
    删除异常：删除所有学生成绩，就把课程信息全删除了； 
    插入异常：学生未选课，无法记录进数据库； 
    更新异常：调整课程学分，所有行都调整。 
    【修正】
    学生：Student(学号, 姓名, 年龄)； 
    课程：Course(课程名称, 学分)； 
    选课关系：SelectCourse(学号, 课程名称, 成绩)。 
    
    不符合第三范式的例子: 
    【表】学号, 姓名, 年龄, 所在学院,学院联系电话，关键字为单一关键字"学号"; 
    存在依赖传递: (学号) → (所在学院) → (学院地点, 学院电话) 
    【存在问题】 
    数据冗余:有重复值； 
    更新异常：有重复的冗余信息，修改时需要同时修改多条记录，否则会出现数据不一致的情况 
    删除异常
    【修正】 
    学生：(学号, 姓名, 年龄, 所在学院)； 
    学院：(学院, 地点, 电话)。 

### 建表规约

1 . 【强制】表达是与否概念的字段，必须使用is_xxx的方式命名，数据类型是unsigned tinyint（ 1表示是，0表示否），此规则同样适用于odps建表。

> 说明：任何字段如果为非负数，必须是unsigned。

2 . 【强制】表名、字段名必须使用小写字母或数字；禁止出现数字开头，禁止两个下划线中间只出现数字。数据库字段名的修改代价很大，因为无法进行预发布，所以字段名称需要慎重考虑。

> 正例：getter_admin，task_config，level3_name  
> 反例：GetterAdmin，taskConfig，level_3_name

3 . 【强制】表名不使用复数名词。

> 说明：表名应该仅仅表示表里面的实体内容，不应该表示实体数量，对应于DO类名也是单数形式，符合表达习惯。

4 . 【强制】禁用保留字，如desc、range、match、delayed等，请参考MySQL官方保留字。  
5 . 【强制】唯一索引名为uk_字段名；普通索引名则为idx_字段名。

> 说明：uk_ 即 unique key；idx_ 即index的简称。

6 . 【强制】小数类型为decimal，禁止使用float和double。

> 说明：float和double在存储的时候，存在精度损失的问题，很可能在值的比较时，得到不正确的结果。如果存储的数据范围超过decimal的范围，建议将数据拆成整数和小数分开存储。

7 . 【强制】如果存储的字符串长度几乎相等，使用char定长字符串类型。   
8 . 【强制】varchar是可变长字符串，不预先分配存储空间，长度不要超过5000，如果存储长度大于此值，定义字段类型为text，独立出来一张表，用主键来对应，避免影响其它字段索引效率。  
9 . 【强制】表必备三字段：id, gmt_create, gmt_modified。

> 说明：其中id必为主键，类型为unsigned bigint、单表时自增、步长为1。gmt_create, gmt_modified的类型均为date_time类型。
10 . 【推荐】表的命名最好是加上“业务名称_表的作用”。

> 正例：tiger_task / tiger_reader / mpp_config

11 . 【推荐】库名与应用名称尽量一致。  
12 . 【推荐】如果修改字段含义或对字段表示的状态追加时，需要及时更新字段注释。  
13 . 【推荐】字段允许适当冗余，以提高性能，但是必须考虑数据同步的情况。冗余字段应遵循：    
    1）不是频繁修改的字段。   
    2）不是varchar超长字段，更不能是text字段。

> 正例：商品类目名称使用频率高，字段长度短，名称基本一成不变，可在相关联的表中冗余存储类目名称，避免关联查询。

14 . 【推荐】单表行数超过500万行或者单表容量超过2GB，才推荐进行分库分表。

> 说明：如果预计三年后的数据量根本达不到这个级别，请不要在创建表时就分库分表。

15 . 【参考】合适的字符存储长度，不但节约数据库表空间、节约索引存储，更重要的是提升检  
索速度。

（本小节来自《阿里巴巴Java开发手册》）

## SQL优化基础

### 数据库语句类型

* **`数据定义语言`**（DDL，Database Definiton Language）：用于定义和管理数据对象，包括数据库，数据表等。例如：CREATE，DROP，ALTER等。
* **`数据操作语言`**（DML，Database Manipulation Language）：用于操作数据库对象中所包含的数据。例如：INSERT，UPDATE，DELETE语句。
* **`数据查询语言`**（DQL，Database Query Language）：用于查询数据库对象中所包含的数据，能够进行单表查询，连接查询，嵌套查询，以及集合查询等各种复杂程度不同的数据库查询，并将数据返回客户机中显示。例如：SELETE。
* **`数据控制语言`**（DCL，Database Control Language）：是用来管理数据库的语言，包括管理权限及数据更改。例如：GRANT，REVOKE，COMMIT，ROLLBACK等。

### show status语句

show status语句用于查看数据库服务器状态信息。

查看当前会话（仅自当前客户端连接后开始计算）状态：

    show status;
    show session status; #可省略session，等价于上一条

查看全局状态（数据库服务器自启动到现在）：

    show global status;

我们关注键名带`Com_`的数据：

    show global status like 'Com_%';

`Com_xxx`表示`xxx`语句执行了多少次。

示例：

    mysql> show global status like 'Com_select';
    +---------------+-------+
    | Variable_name | Value |
    +---------------+-------+
    | Com_select    | 414   |
    +---------------+-------+
    1 row in set (0.00 sec)

查询当前客户端连接数：

    show status like 'Connections';

查询服务器启动时间：

    show status like 'Uptime'; 

示例：

    mysql> show status like 'Uptime';
    +---------------+-------+
    | Variable_name | Value |
    +---------------+-------+
    | Uptime        | 25317 |
    +---------------+-------+
    1 row in set (0.00 sec)

单位是s。

查询慢查询（默认是SQL语句执行超过10s）次数：

    show status like 'Slow_queries';

示例：

    mysql> show global status like 'Slow_queries';
    +---------------+-------+
    | Variable_name | Value |
    +---------------+-------+
    | Slow_queries  | 0     |
    +---------------+-------+
    1 row in set (0.00 sec)

检测索引是否有效:

    mysql> show status like 'handler_read%';
    +-----------------------+-------+       
    | Variable_name         | Value |       
    +-----------------------+-------+       
    | Handler_read_first    | 0     |       
    | Handler_read_key      | 1     |       
    | Handler_read_last     | 0     |       
    | Handler_read_next     | 2     |       
    | Handler_read_prev     | 0     |       
    | Handler_read_rnd      | 0     |       
    | Handler_read_rnd_next | 414   |       
    +-----------------------+-------+       
    7 rows in set (0.00 sec)                

`Handler_read_key` 越大越少，`Handler_read_rnd_next` 越小越好。

### show variables语句

`show variables`语句用于查询数据库服务器配置信息：

    show variables;
    show session variables;
    show global variables;

同样支持session和global。

查询慢查询时间变量：

    show variables like 'long_query_time';

示例：

    mysql> show variables like 'long_query_time';
    +-----------------+-----------+
    | Variable_name   | Value     |
    +-----------------+-----------+
    | long_query_time | 10.000000 |
    +-----------------+-----------+
    1 row in set (0.00 sec)

这里显示系统慢查询时间设置的是10s，即SQL语句执行时间超过10s就会记录在`status`的`Slow_queries`里。

查询是否自动事务提交：

    show variables like 'autocommit';

查询服务器当前状态的连接数量：

    show variables like '%conn%';
    
    show variables like 'max_connections'; #最大连接数
    show  status like 'max_used_connections'; #响应的连接数

### set语句

通过使用set命令默认会改变当前会话variables的变量值，不会影响其它会话。示例：

    mysql> set long_query_time = 5;
    Query OK， 0 rows affected (0.00 sec)
    
    mysql> show variables like 'long_query_time';
    +-----------------+----------+
    | Variable_name   | Value    |
    +-----------------+----------+
    | long_query_time | 5.000000 |
    +-----------------+----------+
    1 row in set (0.00 sec)

如果set命令加了globle则会对所有会话生效，相当于对整个数据库服务器做了配置。通过重启数据库服务恢复到之前的值（即配置文件里的值）。示例：

    set global long_query_time = 0.1; #设置大于0.1s的sql语句记录下来

### 查看主机进程列表

    show full processlist;

### 开启慢查询日志记录

1. 慢查询有什么用？  
它能记录下所有执行超过long_query_time时间的SQL语句， 帮我们找到执行慢的SQL, 方便对这些SQL进行优化。
1. 如何开启慢查询？  
首先我们先查看MYSQL服务器的慢查询状态是否开启。执行如下命令：
```
    show variables like '%quer%';
```
我们可以看到当前`log_slow_queries`状态为`OFF`，说明当前并没有开启慢查询。

开启慢查询非常简单, 操作如下：  
在[mysqld]中添加如下信息：

    [mysqld]
    # 日志存储目录, 此目录文件一定要有写权限
    log-slow-queries="C:/Program Files/MySQL/MySQL Server 5.5/log/mysql-slow.log"
    
    # 最长执行时间(s)，超过该时间记录到日志
    long_query_time = 4
    
    # 没有使用到索引的查询也将被记录在日志中
    log-queries-not-using-indexes

配置好以后需要重新启动MYSQL服务。

也可以使用set语句设置：

    set global log_slow_queries = ON;
    set global slow_query_log = ON;
    set global long_query_time=0.1; #设置大于0.1s的sql语句记录下来

执行命令后立马生效，数据库服务重启后会恢复到默认值。

### explain分析SQL效率

在查询语句前面加explain可以分析SQL的效率。

示例：

    mysql> explain select * from user where id ='2'\G;             
    *************************** 1. row *************************** 
               id: 1                                               
      select_type: SIMPLE                                          
            table: user                                            
             type: const                                           
    possible_keys: PRIMARY                                         
              key: PRIMARY                                         
          key_len: 4                                               
              ref: const                                           
             rows: 1                                               
            Extra:                                                 
    1 row in set (0.00 sec)                                        
                                                                   
    ERROR:                                                         
    No query specified                                             

执行完毕后，给出了查询细节。通过理解分析结果，我们可以知道SQL语句的执行效率。

* `id`：表示查询中执行 select子句或操作表的顺序，id 值越大优先级越高，越先被执行。
* `select_type`：select类型，可以有：SIMPLE、PRIMARY、UNION、DEPENDENT UNION、UNION RESULT、SUBQUERY、DEPENDENT SUBQUERY、DERIVED。
* `table`：输出行所引用的表。
* **`type`**：【重要】显示连接使用的类型，按最优到最差的类型排序：system、const、eq_ref、ref、ref_or_null、index_merge、unique_subquery、index_subquery、range、index、all。
* `possible_keys`：指出 MySQL 能在该表中使用哪些索引有助于 查询。如果为空，说明没有可用的索引。
* **`key`**：【重要】MySQL 实际从 possible_key 选择使用的索引。如果为 NULL，则没有使用索引。
* `key_len`：使用的索引的长度。在不损失精确性的情况下，长度越短越好。
* `ref`：显示索引的哪一列被使用了。
* **`rows`**：【重要】返回请求数据的行数。值越小越好。
* `Extra`：包含MySQL查询的详细信息。如果出现Using filesort或Using temporary意味着 MYSQL 根本不能使用索引，效率会受到重大影响，应尽可能对此进行优化。

**select_type说明**：

名称 | 说明 
-|-
SIMPLE | 简单的 select 查询，不使用 union 及子查询 
PRIMARY | 最外层的 select 查询 
UNION UNION | 中的第二个或随后的 select查询，不依赖于外部查询的结果集 
DEPENDENT UNION | UNION 中的第二个或随后的 select查询，依赖于外部查询的结果集 
SUBQUERY | 子查询中的第一个 select 查询，不依赖于外 部查询的结果集 
DEPENDENT SUBQUERY | 子查询中的第一个 select查询，依赖于外部查询的结果集 
DERIVED | 用于 from 子句里有子查询的情况。 MySQL 会递归执行这些子查询， 把结果放在临时表里。 
UNCACHEABLE SUBQUERY | 结果集不能被缓存的子查询，必须重新为外层查询的每一行进行评估。 
UNCACHEABLE UNION | UNION 中的第二个或随后的 select 查询，属于不可缓存的子查询 

**type说明**：

名称 | 说明 
-|-
system | 表只有一行记录（等于系统表）。这是 const 表连接类型的一个特例。 
const | 表中最多只有一行匹配的记录，它在查询一开始的时候就会被读取出来。由于只有一行记录，在余下的优化程序里该行记录的字段值可以被当作是一个恒定值。const 表查询起来非常快，因为只要读取一次。const 用于在和 PRIMARY KEY 或 UNIQUE 索引中有固定值比较的情形。查询示例：SELECT * FROM tbl_name WHERE primary_key=1; 
eq_ref | 从该表中会有一行记录被读取出来以和从前一个表中读取出来的记录做联合。与 const 类型不同的是，这是最好的连接类型。它用在索引所有部分都用于做连接并且这个索引是一个 PRIMARY KEY 或 UNIQUE 类型。eq_ref 可以用于在进行=做比较时检索字段。比较的值可以是固定值或者是表达式，表达式中可以使用表里的字段，它们在读表之前已经准备好了。查询示例：SELECT * FROM ref_table,other_table WHERE ref_table.key_column=other_table.column; 
ref | 该表中所有符合检索值的记录都会被取出来和从上一个表中取出来的记录作联合。ref 用于连接程序使用键的最左前缀或者是该键不是 PRIMARY KEY 或 UNIQUE 索引（换句话说，就是连接程序无法根据键值只取得一条记录）的情况。当根据键值只查询到少数几条匹配的记录时，这就是一个不错的连接类型。ref 还可以用于检索字段使用 = 操作符来比较的时候。 
ref_or_null | 这种连接类型类似 ref，不同的是MySQL会在检索的时候额外的搜索包含 NULL 值的记录。 
index_merge | 这种连接类型意味着使用了 Index Merge 优化方法。这种情况下，key字段包括了所有使用的索引，key_len 包括了使用的键的最长部分。 
unique_subquery | 这种类型用例如一下形式的 IN 子查询来替换 ref：value IN (SELECT primary_key FROM single_table WHERE some_expr)。unique_subquery 只是用来完全替换子查询的索引查找函数效率更高了。 
index_subquery | 这种连接类型类似 unique_subquery。它用子查询来代替 IN，不过它用于在子查询中没有唯一索引的情况下。 
range | 只有在给定范围的记录才会被取出来，利用索引来取得一条记录。key 字段表示使用了哪个索引。key_len 字段包括了使用的键的最长部分。这种类型时 ref 字段值是 NULL。range 用于将某个字段和一个定植用以下任何操作符比较时 =, <>, >, >=, <, <=, IS NULL, <=>, BETWEEN, 或 IN。 
index | 连接类型跟 ALL 一样，不同的是它只扫描索引树。它通常会比 ALL 快点，因为索引文件通常比数据文件小。MySQL在查询的字段知识单独的索引的一部分的情况下使用这种连接类型。 
all | 最坏的情况，从头到尾全表扫描。 

**extra说明**：

名称 | 说明 
-|-
Distinct | 一旦MYSQL找到了与行相联合匹配的行，就不再搜索了 
Not exists | MYSQL优化了LEFT JOIN，一旦它找到了匹配LEFT JOIN标准的行，就不再搜索了 
range checked for each record (index map: #) | 没有找到理想的索引，因此对于从前面表中来的每一个行组合，MYSQL检查使用哪个索引，并用它来从表中返回行。这是使用索引的最慢的连接之一 
Using index | 列数据是从仅仅使用了索引中的信息而没有读取实际的行动的表返回的，这发生在对表的全部的请求列都是同一个索引的部分的时候 
Using where | 使用了WHERE从句来限制哪些行将与下一张表匹配或者是返回给用户。如果不想返回表中的全部行，并且连接类型ALL或index，这就会发生，或者是查询有问题 
Using filesort | 表示 MySQL 会对结果使用一个外部索引排序，而不是从表里按索引次序读到相关内容。可能在内存或者磁盘上进行排序。MySQL中无法利用索引完成的排序操作称为“文件排序” 
Using temporary | 表示 MySQL在对查询结果排序时使用临时表。常见于排序 order by 和分组查询 group by。 

## MySQL优化

### 使用索引

索引用来快速地寻找那些具有特定值的记录，所有MySQL索引都以B-树的形式保存。如果没有索引，执行查询时MySQL必须从第一个记录开始扫描整个表的所有记录，直至找到符合要求的记录。表里面的记录数量越多，这个操作的代价就越高。如果作为搜索条件的列上已经创建了索引，MySQL无需扫描任何记录即可迅速得到目标记录所在的位置。

#### 索引类型

* 常规索引（INDEX）：最基本的索引，它没有任何限制。用于提高查询速度。
* 唯一索引（UNIQUE）：索引列的值必须唯一，但允许有空值。
* 主键索引（PRIMARY KEY）：它是一种特殊的唯一索引，不允许有空值。一般是在建表的时候同时创建主键索引。一个表只能有一个主键。
* 组合索引：为了更多的提高mysql效率可建立组合索引，遵循”最左前缀“原则。
* 全文索引（FULLTEXT）：仅可用于 MyISAM 表， 用于在一篇文章中，检索文本信息的, 针对较大的数据，生成全文索引很耗时耗空间。

常用的索引是常规索引、唯一索引、主键索引、组合索引。

#### 索引操作

1、查看当前表索引

    show indexes from `table_name`;
    
    # 或者
    show keys from `table_name`;

示例：

    mysql> show indexes from user\G;                               
    *************************** 1. row *************************** 
            Table: user                                            
       Non_unique: 0                                               
         Key_name: PRIMARY                                         
     Seq_in_index: 1                                               
      Column_name: id                                              
        Collation: A                                               
      Cardinality: 21                                              
         Sub_part: NULL                                            
           Packed: NULL                                            
             Null:                                                 
       Index_type: BTREE                                           
          Comment:                                                 
    Index_comment:                                                 
    *************************** 2. row *************************** 
            Table: user                                            
       Non_unique: 1                                               
         Key_name: index_name                                      
     Seq_in_index: 1                                               
      Column_name: name                                            
        Collation: A                                               
      Cardinality: NULL                                            
         Sub_part: NULL                                            
           Packed: NULL                                            
             Null:                                                 
       Index_type: BTREE                                           
          Comment:                                                 
    Index_comment:                                                 
    2 rows in set (0.00 sec)                                       

2、添加索引

    # 添加普通索引
    ALTER TABLE `table_name` ADD INDEX [index_name] (`column`);
    
    # 添加唯一索引
    ALTER TABLE `table_name` ADD UNIQUE [index_name]  (`column`);
    
    # 添加组合索引
    ALTER TABLE `table_name` ADD INDEX [index_name]  (`column1`, `column2`, `column3`);
    
    # 添加全文索引
    ALTER TABLE `table_name` ADD FULLTEXT [index_name]  (`column`) ;

或者：

    CREATE INDEX|UNIQUE|FULLTEXT index_name ON `table_name` (`column`) ;

其中索引名[index_name]是可以省略的，系统会自动生成，意味着同一类型索引可以添加多次。建议自定义有效名称。

3、删除索引

    ALTER TABLE `table_name` DROP INDEX index_name;
    
    # 或者
    DROP INDEX index_name ON `table_name`;
    

4、添加主键索引  
方法一：

    alter table user add id int(10) unsigned not null auto_increment, add primary key (id);

方法二：

    alter table user add id int(10) unsigned not null auto_increment,
    alter table user add primary key (id);

5、删除主键索引

1)先去除auto_increment

    alter table user modify id int not null;

2)再删除主键索引

    alter table  user drop primary key;

#### 什么情况下不建或少建索引

* 表记录太少。
* 经常插入、删除、修改。
* 内容频繁变化。
* 数据重复且分布平均的表字段。  
例如性别字段男、女每个值的分布概率大约为50%，那么对这种表A字段建索引一般不会提高数据库的查询速度。

#### 索引的不足之处

1. 虽然索引大大提高了查询速度，同时却会降低更新表的速度，如对表进行INSERT、UPDATE和DELETE。因为更新表时，MySQL不仅要保存数据，还要保存一下索引文件。  
1. 建立索引会占用磁盘空间的索引文件。一般情况这个问题不太严重，但如果你在一个大表上创建了多种组合索引，索引文件的会膨胀很快。

#### 使用索引的注意事项

下列几种情况下有可能使用到索引：

* 对于创建的组合索引，只要查询条件使用了最左边的列，索引一般就会被使用。  
例如对于user表建了索引name_index(name,address)，当where里查询name时会用到索引，查询address则不会使用索引。   
* 对于使用like的查询，查询如果是 like "%aaa" 不会使用到索引，like "aaa%" 会使用到索引。

下列的情况将不使用索引：

* 如果条件中有or，即使其中有条件带索引也不会使用。
* 对于多列索引，不是使用的第一部分，则不会使用索引。
* like查询是以%开头
* 如果列类型是字符串，那一定要在条件中将数据使用引号'引用起来。否则不使用索引。
* 如果mysql估计使用全表扫描要比使用索引快，则不使用索引。

通过查询status里的'handler_read%'可以知道一段时间内查询使用了索引的次数。

### 表引擎

MySQL支持MyISAM、InnoDB、HEAP、BOB、ARCHIVE、CSV等多种数据表类型，  
在创建一个新MySQL数据表时，可以为它设置一个类型。

MyISAM和InnoDB两种表类型最为重要：

* MyISAM 不支持外键, Innodb支持；
* MyISAM 不支持事务，不支持外键；
* 对数据信息的存储处理方式不同（如果存储引擎是MyISAM的，则创建一张表，对于三个文件`.frm`、`.MYD`、`.MYI`，如果是Innodb则只有一个文件 `*.frm`，数据存放到ibdata1）；
* 对于 MyISAM 数据库，需要定时清理：optimize table 表名

### 常见优化

* 优化group by语句：使用order by null禁用默认排序；  
比如 select * from dept group by ename order by null。
* 使用连接来代替子查询：使用join不需要创建临时表；
* 优化or查询语句：or语句连接的字段必须都有索引，否则查询将不会使用索引；
* 使用optimize定期整理表：如果表引擎是MyIsam，需要经常做删除和修改记录，要定期执行optimize table 表名；
* 在精度要求高的应用中，建议使用定点数(decimal)来存储数值，以保证结果的准确性。

### 大批量插入数据优化

对于MyISAM：

* 临时禁用索引：
```
    # 禁用索引
    alter table user disable keys;
    
    # 插入数据...
    
    # 启用索引
    alter table user enable keys;
```
原因是如果有索引，每插入一条数据会去建立索引，插入数据变慢。

对于Innodb：

* 将要导入的数据按照主键排序；
* 关闭唯一性校验：set unique_checks = 0;；
* 关闭事务自动提交：set autocommit = 0;。

### 分表

* 垂直分表：把一张宽表分成多个小表，把经常一起使用的列放到一起。
* 水平分表：把一个表分成多个表，但是表结构全一样。

#### 垂直分表

原因：  
1.根据MySQL索引实现原理及相关优化策略的内容我们知道Innodb主索引叶子节点存储着当前行的所有信息，所以减少字段可使内存加载更多行数据，有利于查询。  
2.受限于操作系统中的文件大小限制。

切分原则：  
把不常用或业务逻辑不紧密或存储内容比较多的字段分到新的表中可使表存储更多数据。另外垂直分割可以使得数据行变小，一个数据页就能存放更多的数据，在查询时就会减少I/O次数。

其缺点是需要管理冗余列，查询所有数据需要join操作。

#### 水平分表

原因：  
1.随着数据量的增大，table行数巨大，查询的效率越来越低。表很大，分割后可以降低在查询时需要读的数据和索引的页数，同时也降低了索引的层数，提高查询速度。  
2.同样受限于操作系统中的文件大小限制，数据量不能无限增加，当到达一定容量时，需要水平切分以降低单表（文件）的大小。

切分原则：  
增量区间或散列或其他业务逻辑。

使用哪种切分方法要根据实际业务逻辑判断。

### 读写分离

读写分离，基本的原理是让主数据库处理事务性增、改、删操作（INSERT、UPDATE、DELETE），而从数据库处理SELECT查询操作。数据库复制被用来把事务性操作导致的变更同步到集群中的从数据库。

## 数据库配置优化

下面列出了对性能优化影响较大的主要变量，主要分为连接请求的变量和缓冲区变量。这里仅列出关键点，详情参考 [[笔记]MySQL 配置优化][1]。

#### 连接请求的变量：

* `max_connections`：MySQL的最大连接数
```
    show variables like 'max_connections'; #最大连接数
    show status like 'max_used_connections'; #响应的连接数
    
    max_used_connections / max_connections * 100% ≈ 85%（理想值） 
```
* `back_log`：MySQL能暂存的连接数量。  
如果MySQL的连接数据达到max_connections时，新来的请求将会被存在堆栈中，以等待某一连接释放资源，该堆栈的数量即back_log，如果等待连接的数量超过back_log，将不被授予连接资源。  
默认数值是50，可调优为128，对于Linux系统设置范围为小于512的整数。
* `interactive_timeout`：服务器关闭交互式连接前等待活动的秒数。  
默认数值是28800，可调优为7200。

#### 缓冲区变量：  
#### 全局缓冲：

* `key_buffer_size`：MyISAM表索引缓冲区的大小。  
它决定索引处理的速度，尤其是索引读的速度。  
通过检查状态值`Key_read_requests`和`Key_reads`，可以知道`key_buffer_size`设置是否合理。比例`key_reads / key_read_requests`应该尽可能的低，`1:1000`左右。上述状态值可以使用`SHOW STATUS LIKE 'key_read%'`获得。  
即使你不使用MyISAM表，但是内部的临时磁盘表是MyISAM表，也要使用该值。  
默认配置数值是8388600(8M)，主机有4GB内存，可以调优值为268435456(256MB)。  
* `query_cache_size`：查询缓冲。  
MySQL将查询结果存放在缓冲区中，今后对于同样的SELECT语句（区分大小写），将直接从缓冲区中读取结果。

#### 每个连接的缓冲：

* `record_buffer_size`：每个进行一个顺序扫描的线程为其扫描的每张表分配这个大小的一个缓冲区。  
如果你做很多顺序扫描，你可能想要增加该值。  
默认数值是131072(128K)，可改为16773120 (16M)。
* `read_rnd_buffer_size`：随机读缓冲区大小。  
当按任意顺序读取行时(例如，按照排序顺序)，将分配一个随机读缓存区。进行排序查询时，MySQL会首先扫描一遍该缓冲，以避免磁盘搜索，提高查询速度，如果需要排序大量数据，可适当调高该值。但MySQL会为每个客户连接发放该缓冲空间，所以应尽量适当设置该值，以避免内存开销过大。  
一般可设置为16M。
* `sort_buffer_size`：每个需要进行排序的线程分配该大小的一个缓冲区。增加这值加速ORDER BY或GROUP BY操作。  
默认数值是2097144(2M)，可改为16777208 (16M)。
* `join_buffer_size`：联合查询操作所能使用的缓冲区大小。  
record_buffer_size，read_rnd_buffer_size，sort_buffer_size，join_buffer_size为每个线程独占，也就是说，如果有100个线程连接，则占用为16M*100。
* `table_cache`：表高速缓存的大小。  
每当MySQL访问一个表时，如果在表缓冲区中还有空间，该表就被打开并放入其中，这样可以更快地访问表内容。通过检查峰值时间的状态值Open_tables和Opened_tables，可以决定是否需要增加table_cache的值。  
1G内存机器，推荐值是128－256。内存在4GB左右的服务器该参数可设置为256M或384M。
* `max_heap_table_size`：用户可以创建的内存表(memory table)的大小。这个值用来计算内存表的最大行数值。
* `tmp_table_size`：通过设置tmp_table_size选项来增加一张临时表的大小，例如做高级GROUP BY操作生成的临时表。如果调高该值，MySQL同时将增加heap表的大小，可达到提高联接查询速度的效果，建议尽量优化查询，要确保查询过程中生成的临时表在内存中，避免临时表过大导致生成基于硬盘的MyISAM表。  
默认为16M，可调到64-256最佳，线程独占，太大可能内存不够I/O堵塞。
* `thread_cache_size`：可以复用的保存在中的线程的数量。如果有，新的线程从缓存中取得。  
通过比较 Connections和Threads_created状态的变量，可以看到这个变量的作用。  
默认值为110，可调优为80。
* `thread_concurrency`：推荐设置为服务器 CPU核数的2倍，例如双核的CPU, 那么thread_concurrency的应该为4。2个双核的cpu，thread_concurrency的值应为8。默认为8。
* `wait_timeout`：指定一个请求的最大连接时间，对于4GB左右内存的服务器可以设置为5-10。

#### 配置InnoDB的几个变量：

* `innodb_buffer_pool_size`：innodb_buffer_pool_size的作用就相当于key_buffer_size对于MyISAM表的作用一样。InnoDB使用该参数指定大小的内存来缓冲数据和索引。对于单独的MySQL数据库服务器，最大可以把该值设置成物理内存的80%。  
根据MySQL手册，对于2G内存的机器，推荐值是1G（50%）。
* `innodb_flush_log_at_trx_commit`：主要控制了innodb将log buffer中的数据写入日志文件并flush磁盘的时间点，取值分别为0、1、2三个。0，表示当事务提交时，不做日志写入操作，而是每秒钟将log buffer中的数据写入日志文件并flush磁盘一次；1，则在每秒钟或是每次事物的提交都会引起日志文件写入、flush磁盘的操作，确保了事务的ACID；设置为2，每次事务提交引起写入日志文件的动作，但每秒钟完成一次flush磁盘操作。  
实际测试发现，该值对插入数据的速度影响非常大，设置为2时插入10000条记录只需要2秒，设置为0时只需要1秒，而设置为1时则需要229秒。因此，MySQL手册也建议尽量将插入操作合并成一个事务，这样可以大幅提高速度。  
根据MySQL手册，在允许丢失最近部分事务的危险的前提下，可以把该值设为0或2。
* `innodb_log_buffer_size`：log缓存大小，一般为1-8M，默认为1M，对于较大的事务，可以增大缓存大小。  
可设置为4M或8M。
* `innodb_additional_mem_pool_size`：该参数指定InnoDB用来存储数据字典和其他内部数据结构的内存池大小。缺省值是1M。通常不用太大，只要够用就行，应该与表结构的复杂度有关系。如果不够用，MySQL会在错误日志中写入一条警告信息。  
根据MySQL手册，对于2G内存的机器，推荐值是20M，可适当增加。
* `innodb_thread_concurrency`：推荐设置为 2*(NumCPUs+NumDisks)，默认一般为8。

> 参考：  
> 1、数据库 三范式最简单最易记的解释_数据库其它_脚本之家  
[http://www.jb51.net/article/19312.htm][2]  
[http://blog.csdn.net/famousdt/article/details/6921622][3]  
> 2、EXPLAIN 语法（得到SELECT 的相关信息） - guoguo1980的专栏 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/guoguo1980/article/details/2073902][4]  
> 3、MySQL查询优化之explain的深入解析_Mysql_脚本之家  
[http://www.jb51.net/article/38357.htm][5]  
> 4、水平分表和垂直分表 - w_xuexi666的博客 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/w_xuexi666/article/details/53925604][6]  
> 5、mysql-水平分表-垂直分表 - 大猫博客 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/qq_17392301/article/details/45501597][7]  
> 6、数据库的垂直切分和水平切分 - kobejayandy的专栏 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/kobejayandy/article/details/8775138][8]  
> 7、数据库的读写分离 - kobejayandy的专栏 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/kobejayandy/article/details/8775255][9]  
> 8、利用mysql-proxy进行mysql数据库的读写分离 - 阿姜 - 博客园  
[http://www.cnblogs.com/tae44/p/4701226.html][10]  
> 9、[笔记]MySQL 配置优化 - _Boz - 博客园  
[http://www.cnblogs.com/Bozh/archive/2013/01/22/2871545.html][1]  
> 10、mysql查询、索引、配置优化 - lxpbs8851的专栏 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/lxpbs8851/article/details/7834836][11]

**作者：飞鸿影~**

</font>

**出处：**http://52fhy.cnblogs.com/

[0]: http://www.cnblogs.com/52fhy/p/6476386.html
[1]: http://www.cnblogs.com/Bozh/archive/2013/01/22/2871545.html
[2]: http://www.jb51.net/article/19312.htm
[3]: http://blog.csdn.net/famousdt/article/details/6921622
[4]: http://blog.csdn.net/guoguo1980/article/details/2073902
[5]: http://www.jb51.net/article/38357.htm
[6]: http://blog.csdn.net/w_xuexi666/article/details/53925604
[7]: http://blog.csdn.net/qq_17392301/article/details/45501597
[8]: http://blog.csdn.net/kobejayandy/article/details/8775138
[9]: http://blog.csdn.net/kobejayandy/article/details/8775255
[10]: http://www.cnblogs.com/tae44/p/4701226.html
[11]: http://blog.csdn.net/lxpbs8851/article/details/7834836
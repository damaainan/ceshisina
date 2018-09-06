##表类型（存储引擎）的选择
插件式存储引擎是MySQL最重要特性之一，5.5之前默认引擎为MyISAM，之后为InnoDB，如需修改默认存储引擎，可在参数文件中设置default_storage_engine。

查看默认引擎:`show variables like %storage%`

查看当前数据库支持的存储引擎:`show engines\G`(\G的花式用)
>需要查看某一种存储引擎的详细信息，如查看MyISAM：`? MyISAM`

创建表时指定存储引擎：

```sql
create table ai(
	i bigint(20) NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(i)
)ENGINE=myisam default charset=utf8
```

修改表的存储引擎：`alter table tablename engine=enginename`

###常用存储引擎对比
特点 | MyISAM | InnoDB | MEMORY | MERGE | NDB
--- | --- | --- | --- | --- | ---
存储限制 | 有 | 64T | 有 | 没有 | 有
事物安全 |   | 支持 |   |   |  
锁机制 | 表锁 | 行锁 | 表锁 | 表锁 | 行锁
B树索引 | 支持 | 支持 | 支持 | 支持 | 支持
哈希索引 |   |   | 支持 |   | 支持
全文索引 | 支持 |   |   |   | 
集群索引 |   | 支持 |   |   |  
数据缓存 |   | 支持 | 支持 |   | 支持
索引缓存 | 支持 | 支持 | 支持 | 支持 | 支持
数据可压缩 | 支持 |   |   |   | 
空间使用 | 低 |  高 | N/A | 低 | 低
内存使用 | 低 | 高 | 中等 | 低 | 高
批量插入的速度 | 高 | 低 | 高 | 高 | 高
支持外键 |   | 支持 |   |   |   | 

####MyISAM
5.5之前的默认引擎，不支持事务和外键，访问速度快。每个MyISAM在磁盘上存储成3个文件，文件名和表名字相同，扩展名为：
	- .frm(表定义)
	- .MYD(MYData,数据)
	- .MYI(MYIndex,索引)

数据文件和索引文件可放在不同目录，平均分布IO，获取更快速度。指定索引和数据文件路径需要在创建表时通过DATA DIRECTORY和INDEX DIRECTORY指定，文件路径为绝对路径且具有可访问权限。
	
MyISAM类型的表可用`check table`来检查表的健康，并用`repair table`语句修复一个损坏的MyISAM表。（预知详情，我现在也不知道，后面文章中会详解）
	
MyISAM表支持3中不同存储格式：
	
- 静态（固定长度）表
- 动态表
- 压缩表
	
静态表为默认存储格式，字段固定长度，优点存储快，易缓存；缺点占用空间比动态表多。*存储时按列宽度补足空格，但访问之前会去掉*。动态表包含变长，优点是占用空间相对较少，缺点是频繁更新会产生碎片，需定期`OPTIMIZE TABLE`或者`myisamchk -r`改善性能。压缩表由myisampack工具创建，占磁盘空间小。

####InnoDB
- 自动增长列
	
	InnoDB的自动增长列可以手工插入，但是插入的值如为空或者0，则实际将是自动增长的值。可以通过`alter table tablename auto_increment=n`强制设置自动增长列的初始值，默认从1开始，重启失效。可使用LAST_INSERT_ID()查询最后插入记录自增使用的值。一次插入多条是第一条记录使用的自动增长值（有点鸡肋的功能）。
	
	对InnoDB，自增列必须是索引，也必须是主键。
	
- 外键约束
	
	MySQL在创建外键时，要求父表必须有对应的索引，字表在创建外键的时候也会自动创建对应的索引。并且在父表对应的主键或索引禁止删除。暂时关闭外键约束的命令：`set foreign_key_checks=0`,等于1则恢复。查询外键信息：`show craete table`或者`show table status like 'tablename'\G`
	
- 存储方式
InnoDB存储和索引表有两种方式，一种为使用共享空间存储，一种是使用多表空间存储。要使用多表空间存储方式，需要设置参数innodb_file_per_table。

####MEMORY
MEMORY存储引擎用存在于内存中的内容来创建表。每个MEMORY表只实际对应一个.frm磁盘文件。默认使用HASH索引，服务关闭，表中数据丢失。
####MERGE
MERGE存储引擎是一组MyISAM表的组合，表结构完全相同，MERGE表本身并没有数据，对MERGE类型表的操作实际会映射为对内部MyISAM表的操作，对MERGE插入操作，通过INSERT_METHOD自定义插入的表，该值有三个FIRST或者LAST或不定义/NO（表示不能执行插入操作）。可对MERGE进行DROP操作，但是只删除MERGE定义，对内部表没有影响。MERGE表在磁盘上保留两个文件，一个.frm文件存储表定义，另一个.MRG文件包含组合表的信息。可以通过修改.MRG文件来修改MERGE表，修改后通过FLUSH TABLES刷新。以下为示例：

```sql
create table payment_2006(
	country_id smallint,
	payment_date datetime,
	amount decimal(15,2),
	key idx_fk_country_id(country_id)
)engine=myisam

create table payment_2007(
	country_id smallint,
	payment_date datetime,
	amount decimal(15,2),
	key idx_fk_country_id(country_id)
	)engine=myisam
	
create table payment_all(
	country_id smallint,
	payment_date datetime,
	amount decimal(15,2),
	INDEX(country_id)
	)engine=merge union=(payment_2006,payment_2007) INSERT_METHOD=LAST
```
>以上向payment_2006和payment_2007中插入数据后都会进入payment_all,该表是以上两表记录合并后的结果集。

###如何选择合适存储引擎
- MyISAM

	以读和插入操作为主，只有很少跟新和删除，并对事务的完整性和并发性要求不高。

- InnoDB

	用于事务处理应用程序，支持外键。对事务和并发有要求，并且除了插入和查询还有不少更新和删除。

- MEMORY
	
	将所有数据放在RAM中，因此很快，但对大小有限制，用于更新不太频繁的小表。
	
- MERGE

	将MyISAM表组合，作为一个对象引用它们，可以突破对单个MyISAM表大小的限制。
	
	
##数据类型的选择
####CHAR和VARCHAR
总的来说，CHAR是定长，且对空格的处理和VARCAHR不同，CHAR保留空格，VARCAHR不保留，读取时候都去掉空格。MyISAM存储引擎建议使用char；MEMORY存储引擎都会转为CAHR类型处理；InnoDB建议用VARCHAR。
####TEXT和BLOB
1. BLOB和TEXT值会引起一些性能问题，特别是执行大量的删除操作时。删除会在数据表中留下很大“空洞”，建议定期使用`OPTIMIZE TABLE`对这类表进行碎片整理。如下示例：

```sql
create table t(id varcahr(100),context text);
insert into t values(1,repeat('haha',100));
insert into t values(2,repeat('haha',100));
insert into t values(3,repeat('haha',100));
insert into t select * from t;
====================
此时查看数据表物理磁盘大小：
dh -sh t.*
16K   t.frm
155M  t.MYD
8.0K  t.MYI
====================
delete from t where id=1;
此时再查看发现所占用磁盘大小不变。由于大量碎片夹杂其间。执行OPTIMIZE：
OPTIMIZE table t;
====================
du -sh t.*
16K   t.frm
104M  t.MYD
8.0K  t.MYI
```

2. 可以使用合成索引来提高大文本字段的查询性能。简单说就是根据大文本字段内容建立一个散列值，并且把这个值存在单独数据列中，通过索引散列找到数据行。这只能用于精确查询。可以用MD5()或者其他加密算法生成散列值。
3. 在不必要时避免检索大型BLOB或TEXT值
4. 将BLOB或TEXT列分离到单独的表中

####浮点数和定点数
浮点一般用于表示包含小数部分的数值，当一个字段被定义为浮点类型后，插入数据的精度超过该列定义的实际精度，则插入值会被四舍五入到实际的精度值。定点数则以字符串形式存放，插入数值精度大于实际精度，则会四舍五入。

- 浮点数存在误差问题。
- 对货币精度敏感的数据，用定点存储。
- 编程中避免浮点数比较
- 浮点数中特殊值的处理
 	
####日期类型选择
- 根据实际需要选择能满足应用的最小存储的日期类型
- 记录年份比较久远选择DATETIME
- 记录的日期需要不同时区的用户使用，最好使用TIMESTAMP

##选择字符集
**总之使用UTF8就没错**

查看所有可用的字符集:`shwo character set`

查看字符集默认校对规则：`desc information_schema.character_sets`

MySQL的字符集包括字符集（CHARACTER）和校对规则（COLLATION）两个概念。字符集用来定义MySQL存储字符串的方式，校对规则用来定义比较字符串的方式。每个字符集至少对应一个校对规则，可以用`show collation like 'utf8%'`或者查看information_schema.COLLATIONS来查看。

校对规则命名约定：以其相关的字符集名开始，通常包含一个语言名，并以_ci(大小写不敏感)、_cs(大小写敏感)或_bin（比较字符编码的值）

###MySQL字符集的设置
mysql的字符集和校对规则有4个级别的默认设置：服务器、数据库、表和字段。分别在不同地方设置，作用也不同。
####服务器字符集和校对规则
- 在my.cnf中设置

```
[mysqld]
character-set-server=utf8
```
- 启动选项中指定

```
mysqld --character-set-server=utf8
```

- 在编译时指定

```
cmake . -DEFAULT_CHARSET=utf8
```
>默认使用latin1作为服务器字符集，以上没有设置校对规则，也就是使用该字符集的默认校对规则。

查询当前**服务器**字符集和校对规则`shwo variables like '%_server'`

####数据库（表）字符集和校对规则
可以在创建数据库是指定，也可以通过`alter database（table）`修改。设置数据库字符集规则：

- 都设置了，使用设置的
- 没设置校对规则，使用指定字符集的默认校对规则
- 没设置字符集，使用于改校对规则关联的字符集
- 都没有设置，使用服务器字符集和校对规则

查看当前数据库字符集和校对规则：`show variables like '%_database'`

查看数据表的字符集和校对规则：`show create table tablename\G`

####列字符集和校对规则
可以定义，但很少用，算是mysql提供的一个很灵活的设置方法。没设置使用表的字符集和校对规则

####连接字符集和校对规则
对实际的应用访问来说，还存在客户端和服务器之间交互的字符集和校对规则。mysql提供了3个不同参数：character_set_client、character_set_connection和character_set_results分别代表客户端、连接和返回结果的字符集。通常应该保证这三个字符集相同。

同时修改以上三种字符集：`set names ***`
>以上方法只能在当前连接生效，每次新的连接后都要设置

在配置文件中修改：

```
[mysqld]
default-character-set=utf8
```

可以通过`[_charset_name]'string'[collate collation_name]`命令强制字符串的字符集和校对规则：

```
select _gbk '字符串' 
select _utf8 '字符串'
```

####字符集的修改步骤
对已存储了数据的数据库，需要修改字符集不能通过直接执行`alter database charcter set **`或者`alter table tablename character set **`命令进行，这两个命令都没有更新已有记录的字符集。已有记录字符集调整需要先将数据导出，调整后再导入。以下模拟将latin1字符集的数据库修改成gbk字符集数据库的过程:

1. 导出表结果:

```
mysqldump -uroot -p --default-character-set=gbk -d databasename > createtab.sql
```

2. 手工修改create.sql中表结构定义中的字符集为新的字符集

3. 确保记录不再更新，导出所有记录


```
mysqldump -uroot -p --quick --no-create-info --extended-insert --default-character-set=latin1 databasename>data.sql
```

4. 打开data.sql，将`set names latin1`修改成`set names dbk`
5. 使用新的字符集创建新的数据库

```
create database databasebname default charset gbk
```

6. 创建表，执行createtab.sql

```
mysql -uroot -p databasename < createtab.sql
```

7. 导入数据，执行data.sql

```
mysql -uroot -p databasename < data.sql
```

>选择目标字符集时为避免丢失数据，最好选择当前字符集的超集


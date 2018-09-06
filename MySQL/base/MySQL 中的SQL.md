# MySQL 中的SQL

 时间 2017-05-19 15:43:18  Chars's Tech Blog

原文[http://chars.tech/2017/05/19/mysql-sql-study/][1]

<font face=微软雅黑>

结构化查询语言（英语：Structured Query Language，缩写：SQL），是一种特殊目的之编程语言，用于数据库中的标准数据查询语言，IBM公司最早使用在其开发的数据库系统中。

不过各种通行的数据库系统在其实践过程中都对SQL规范作了某些编改和扩充。所以，实际上不同数据库系统之间的SQL不能完全相互通用。

文章以MySQL数据库为演示环境，主要分享MySQL中的SQL使用。

## 数据库操作 

### 创建数据库 

    CREATE {DATABASE | SCHEMA} [IF NOT EXISTS] db_name [DEFAULT] CHARACTER SET [=] character_name
    

{} 表示为必选项，即必填参数。 | 表示选项，即其中选取一项即可。 [] 表示为可选项。 

character_name 参数为指定数据库的编码方式，不填则使用MySQL配置的字符集编码。 

### 查看当前服务器下的数据表列表 

    SHOW {DATABASES | SCHEMAS} [LIKE 'pattern' | WHERE expr]
    

### 删除数据库 

    DROP {DATABASE | SCHEMA} [IF NOT EXISTS] db_name
    

## AUTO_INCREMENT 

自动编号，且必须与主键组合使用。

数值型数据。

默认情况下，起始值为1，每次的增量为1。

## 约束 

1.保证数据的完整性和一致性。

2.分为表级约束（针对两个或两个以上的字段进行约束）和列级约束（针对某一个字段进行约束）。

3.类型包括：

* NOT NULL 非空约束
* PRIMARY KEY 主键约束
* UNIQUE KEY 唯一约束
* DEFAULT 默认约束
* FOREIGN KEY 外键约束

```sql
    CREATE TABLE t6(id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20) NOT NULL UNIQUE KEY, sex ENUM('1', '2', '3') DEFAULT '3');
```

![][4]

    INSERT t6 (username) VALUES ('Chars');
    

![][5]

### 空值与非空值 

NULL，字段值可以为空。

NOT NULL，字段值禁止为空。不存在表级约束。

### PRIMARY KEY 

主键约束。

每张数据表只能存在一个主键。

主键保证记录的唯一性。

主键自动为NOT NULL。

#### 注意：

AUTO_INCREMENT 必须与 PRIMARY KEY 一起使用。但是， PRIMARY KEY 不一定与 AUTO_INCREMENT 一起使用。 

### UNIQUE KEY 

唯一约束。

唯一约束可以保证记录的唯一性。

唯一约束的字段可以为空值（NULL）。

每张数据表可以存在多个唯一约束。

#### 注意：UNIQUE KEY与PRIMARY KEY区别

PRIMARY KEY每张数据表只能有一个，且不能为空。

UNIQUE KEY每张数据表可以有多个，且可以为空。

### DEFAULT 

默认值。

当插入记录时，如果没有明确为字段赋值，则自动赋予默认值。

不存在表级约束。

### FOREIGN KEY 

保持数据一致性，完整性。

实现一对一或一对多关系。

关系型数据库名称的来源。

#### 外键约束的要求 

1.父表和子表必须使用相同的存储引擎，而且禁止使用临时表。

2.数据表的存储引擎只能为InnoDB。

3.外键列和参照列必须具有相似的数据类型。其中数字的长度或是否有符号位必须相同；而字符的长度则可以不同。

4.外键列和参照列必须创建索引。如果外键列不存在索引的话，MySQL将自动创建索引。

#### 外键约束的参照操作 

1.CASCADE：从父表删除或更新且自动删除或更新子表中匹配的行。

2.SET NULL：从父表删除或更新行，并设置子表中的外键列为NULL。如果使用该选项，必须保证子表列没有指定NOT NULL。

3.RESTRICT：拒绝对父表的删除或更新操作。

4.NO ACTION：标准SQL的关键字，在MySQL中与RESTRICT相同。

注意：物理外键即使用FOREIGN KEY关键字定义表。逻辑外键即定义表的时候按照某种联系，但是不使用FOREIGN KEY关键字修饰。 

#### 编辑数据表的默认存储引擎 

MySQL配置文件

default-storage-engine=INNODB示例：

    create table provinces(id smallint unsigned primary key auto_increment, pname varchar(20) not null);
    

    create table users(id smallint unsigned primary key auto_increment, username varchar(10) not null, pid bigint, foreign key(pid) references provinces(id));
    
    # 报错
    # ERROR 1215 (HY000): Cannot add foreign key constraint
    # 因为类型不匹配
    
    # 正确命令应该是：
    create table users(id smallint unsigned primary key auto_increment, username varchar(10) not null, pid smallint unsigned, foreign key(pid) references provinces(id));
    

![][6]

![][7]

### 表级约束和列级约束 

对一个数据列建立的约束，称为列级约束。

对多个数据列建立的约束，称为表级约束。

列级约束既可以在列定义时声明，也可以在列定义后声明。

表级约束只能在列定义后声明。

## 数据表操作 

数据表（或称表）是数据库最重要的组成部分之一，是其它对象的基础。数据表即二维表，行称为记录，列称为字段。

### USE 

打开数据库

USE 数据库名称;### 创建数据表 

    CREATE TABLE [IF NOT EXISTS] table_name (column_name data_type, ...)
    

### 查看数据表列表 

    SHOW TABLES [FROM db_name] [LIKE 'pattern' | WHERE expr]
    

### 查看数据表结构 

    SHOW COLUMNS FROM tbl_name
    

### 插入表记录 

    INSERT [INTO] tbl_name [(col_name,...)] VALUES(val,...)
    

如果省略col_name就需要写全数据表所有的值。

### 记录查找 

    SELECT expr,... FROM tbl_name
    

### 添加单列（数据表字段） 

    ALTER TABLE tbl_name ADD [COLUMN] col_name column_definition [FIRST|AFTER col_name]
    

省略[FIRST|AFTER col_name]参数将位于所有列的最后面。

### 添加多列（数据表字段） 

    ALTER TABLE tbl_name ADD [COLUMN] (col_name column_definition, ...)
    

### 删除列（数据表字段） 

    ALTER TABLE tbl_name DROP [COLUMN] col_name
    

### 添加主键约束 

    ALTER TABLE tbl_name ADD [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...)
    

### 添加唯一约束 

    ALTER TABLE tbl_name ADD [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...)
    

### 添加外键约束 

    ALTER TABLE tbl_name ADD [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name, ...) reference_definition
    

### 添加／删除默认约束 

    ALTER TABLE tbl_name ALTER [COLUMN] col_name {SET DEFAULT literal | DROP DEFAULT}
    

### 删除主键约束 

    ALTER TABLE tbl_name DROP PRIMARY KEY

### 删除唯一约束 

    ALTER TABLE tbl_name DROP {INDEX | KEY} index_name
    

### 删除外键约束 

    ALTER TABLE tbl_name DROP FOREIGN KEY fk_symbol
    

### 修改列定义 

    ALTER TABLE tbl_name MODIFY [COLUMN] col_name column_definition [FIRST | AFTER col_name]
    

### 修改列名称 

    ALTER TABLE tbl_name CHANGE [COLUMN] old_col_name new_col_name column_definition [FIRST | AFTER col_name]
    

### 数据表更名 

    # 方法1
    ALTER TABLE tbl_name RENAME [TO|AS] new_tbl_name
    
    # 方法2
    RENAME TABLE tbl_name TO new_tbl_name [, tbl_name2 TO new_tbl_name2] ...
    

## 数据表数据操作 

### INSERT 

    # 插入记录（可以插入多条记录）
    INSERT [INTO] tbl_name [(col_name, ...)] {VALUES|VALUE} ({expr|DEFAULT}, ...), (...), ...

    # 插入记录（不可以插入多条记录）
    INSERT [INTO] tbl_name SET col_name = {expr|DEFAULT}, ...
    
    # 说明：与前一种方式的区别在于，此方法可以使用子查询（SubQuery）。由比较运算引发子查询（SubQuery）。

    # 插入记录
    INSERT [INTO] tbl_name [(col_name, ...)] SELECT ...
    
    # 说明：此方法可以将查询结果插入到指定数据表。

示例：

    1.创建“商品分类”表
    CREATE TABLE IF NOT EXISTS tdb_goods_cates(cate_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,cate_name VARCHAR(40));
    
    2.查询tdb_goods表的所有记录，并且按"类别"分组
    SELECT goods_cate FROM tdb_goods GROUP BY goods_cate;
    
    3.将分组结果写入到tdb_goods_cates数据表
    INSERT tdb_goods_cates (cate_name) SELECT goods_cate FROM tdb_goods GROUP BY goods_cate;
    

### UPDATE 

    # 更新记录（单表更新）
    UPDATE [LOW_PRIORITY] [IGNORE] table_reference SET col_name1 = {expr|DEFAULT} [, col_name2 = {expr|DEFAULT}] ... [WHERE where_condition]
    

### DELETE 

    # 删除记录（单表删除）
    DELETE FROM tbl_name [WHERE where_condition]
    

### SELECT 

    # 查找记录
    SELECT select_expr [, select_expr ...] 
    [
        FROM table_references
        [WHERE where_condition]
        [GROUP BY {col_name|position} [ASC|DESC], ... ]
        [HAVING where_condition]
        [ORDER BY {col_name|expr|position} [ASC|DESC], ...]
        [LIMIT {[offset,] row_count | row_count OFFSET offset}]
    ]
    

#### select_expr 查询表达式 

每一个表达式表示想要的一列，必须至少有一个。

多个列之间以英文逗号分隔。

星号（ * ）表示多有列。 tbl_name.* 可以表示命名表的所有列。 

查询表达式可以使用[AS] alias_name为其赋予别名。

别名可用于GROUP BY，ORDER BY或HAVING子句。

#### WHERE 条件表达式 

对记录进行过滤，如果没有指定WHERE子句，则显示所有记录。

在WHERE表达式中，可以使用MySQL支持的函数或运算符。

#### GROUP BY 查询结果分组 

[GROUP BY {col_name|position} [ASC|DESC], ... ]  
ASC：生序，默认值。

DESC：降序。

#### HAVING 分组条件 

[HAVING where_condition]  
where_condition 中要么使用聚合函数，要么出现的字段一定要在SELECT中出现。 

聚合函数：count() …

#### ORDER BY 对查询结果进行排序 

[ORDER BY {col_name|expr|position} [ASC|DESC], ...]#### LIMIT 限制查询返回的数量 

[LIMIT {[offset,] row_count | row_count OFFSET offset}]  
offset是从0开始的。 

### CREATE … SELECT 

创建数据表同时将查询结果写入到数据表

CREATE TABLE [IF NOT EXISTS] tbl_name [(create_definition, ...)] select_statement示例：

* 通过CREATE…SELECT来创建数据表并且同时写入记录

```sql
CREATE TABLE tdb_goods_brands (brand_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,brand_name VARCHAR(40) NOT NULL) SELECT brand_name FROM tdb_goods GROUP BY brand_name;
```

## 子查询 

子查询（Subquery）是指出现在其他SQL语句内的SELECT子句。例如：

SELECT * FROM t1 WHERE col1=(SELECT col2 FROM t2);  
其中 SELECT * FROM t1 称为Outer Query/Outer Statement。 SELECT col2 FROM t2 称为SubQuery。 

子查询指嵌套在查询内部，且必须始终出现在圆括号内。子查询可以包含多个关键字或条件，如DISTINCT、GROUP BY、ORDER BY、LIMIT函数等。子查询外层的查询可以是：SELECT、INSERT、UPDATE、SET或DO。

子查询可以返回标量、一行、一列或子查询。

### 数据准备 

1.创建表 

```sql
    create table tdb_goods (
        goods_id smallint unsigned primary key auto_increment,
        goods_name varchar(150) not null, 
        goods_cate varchar(40) not null, 
        brand_name varchar(40) not null,
        goods_price decimal(15,3) unsigned default 0 not null, 
        is_show boolean default 1 not null, 
        is_saleoff boolean default 0 not null);
```

2.添加数据 

```sql
    INSERT tdb_goods (goods_name,goods_cate,brand_name,goods_price,is_show,is_saleoff) VALUES('Mac Pro MD878CH/A 专业级台式电脑','服务器/工作站','苹果','28888',DEFAULT,DEFAULT);
     
    INSERT tdb_goods (goods_name,goods_cate,brand_name,goods_price,is_show,is_saleoff) VALUES(' HMZ-T3W 头戴显示设备','笔记本配件','索尼','6999',DEFAULT,DEFAULT);
    
    INSERT tdb_goods (goods_name,goods_cate,brand_name,goods_price,is_show,is_saleoff) VALUES('商务双肩背包','笔记本配件','索尼','99',DEFAULT,DEFAULT);
    
    INSERT tdb_goods (goods_name,goods_cate,brand_name,goods_price,is_show,is_saleoff) VALUES('X3250 M4机架式服务器 2583i14','服务器/工作站','IBM','6888',DEFAULT,DEFAULT);
```

### 分类 

#### 使用比较运算符的子查询 

`=`、`>`、`<`、`>=`、`<=`、`<>`、`!=`、`<=>` ...语法结构

operand comparison_operator subquery示例：

* 求所有电脑产品的平均价格,并且保留两位小数，AVG,MAX,MIN、COUNT、SUM为聚合函数

```sql
SELECT ROUND(AVG(goods_price),2) AS avg_price FROM tdb_goods;
```

* 查询所有价格大于平均价格的商品，并且按价格降序排序

```sql
SELECT goods_id,goods_name,goods_price FROM tdb_goods WHERE goods_price > 5845.10 ORDER BY goods_price DESC;
```

* 使用子查询来实现

```sql
SELECT goods_id,goods_name,goods_price FROM tdb_goods WHERE goods_price > (SELECT ROUND(AVG(goods_price),2) AS avg_price FROM tdb_goods) ORDER BY goods_price DESC;
```

#### 用ANY、SOME或ALL修饰的比较运算符 

operand comparison_operator ANY(subquery)  
operand comparison_operator SOME(subquery)  
operand comparison_operator ALL(subquery)ANY、SOME、ALL关键字

![][8]

示例：

* 查询价格大于或等于”超级本”价格的商品，并且按价格降序排列

```sql
SELECT goods_id,goods_name,goods_price FROM tdb_goods WHERE goods_price = ANY(SELECT goods_price FROM tdb_goods WHERE goods_cate = '超级本') ORDER BY goods_price DESC;
```

#### 使用[NOT]IN的子查询 

语法结构

operand comparison_operator [NOT]IN(subquery)  
=ANY运算符与IN等效。

`!=ALL`或`<>ALL`运算符与`NOT IN`等效。

示例：

* `= ANY` 或 `= SOME` 等价于 `IN`

```sql
SELECT goods_id,goods_name,goods_price FROM tdb_goods WHERE goods_price IN (SELECT goods_price FROM tdb_goods WHERE goods_cate = '超级本') ORDER BY goods_price DESC;
```

#### 使用`[NOT]EXISTS`的子查询 

如果子查询返回任何行，EXISTS将返回TRUE；否则为FALSE。

## 连接 

MySQL在SELECT语句、多表更新、多表删除语句中支持JOIN操作。

### 多表更新 

    UPDATE table_references SET col_name1 = {expr1|DEFAULT} [, col_name2 = {expr2|DEFAULT}] ... [WHERE where_condition]
    

table_references 的语法结构： 

{[INNER|CROSS] JOIN | {LEFT|RIGHT} [OUTER] JOIN} table_reference ON conditional_expr

#### 数据表参照 

table_references

tbl_name [[AS] alias]|table_subquery [AS] alias  
数据表可以使用 `tbl_name AS alias_name` 或 `tbl_name alias_name` 赋予别名。 

table_subquery 可以作为子查询使用在FROM子句中，这样的子查询必须为其赋予别名。 

#### 连接类型 

INNER JOIN，内连接。在MySQL中，JOIN，CROSS JOIN和INNER JOIN是等价的。

LEFT [OUTER] JOIN，左外连接。

RIGHT [OUTER] JOIN，右外连接。

示例：

* 通过tdb_goods_cates数据表来更新tdb_goods表

```sql
UPDATE tdb_goods INNER JOIN tdb_goods_cates ON goods_cate = cate_name SET goods_cate = cate_id ;
```

### 多表删除 

    DELETE tbl_name [.*] [, tbl_name [.*]] ... FROM table_references [WHERE where_condition]

### 内连接 

显示左表及右表符合连接条件的记录。即仅显示符合连接条件的内容。

![][9]

### 外连接 

A LEFT JOIN B join_condition.

数据表B的结果集依赖数据表A。

数据表A的结果集根据左连接条件依赖所有数据表（B表除外）。

左外连接条件决定如何检索数据表B（在没有指定WHERE条件的情况下）。

如果数据表A的某条记录符合WHERE条件，但是在数据表B不存在符合连接条件的记录，将生成一个所有列为空的额外的B行。

如果使用内连接查找的记录在连接数据表中不存在，并且在WHERE子句中尝试以下操作：col_name IS NULL时，如果col_name被定义为NOT NULL，MySQL将在找到符合连接条件的记录后停止搜索更多的行。

* 左外连接   
显示左表的全部记录及右表符合连接条件的记录。   
![][10]
* 右外连接   
显示右表的全部记录及左表符合连接条件的记录。   
![][11]

### 连接条件 

使用ON关键字来设定连接条件，也可以使用WHERE来代替。

通常使用ON关键字来设定连接条件，使用WHERE关键字进行结果集记录的过滤。

### 无限级分类表设计 

* 无限分类的数据表设计

```sql
CREATE TABLE tdb_goods_types( type_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, type_name VARCHAR(20) NOT NULL, parent_id SMALLINT UNSIGNED NOT NULL DEFAULT 0 );
```

* 插入数据

```sql
    INSERT tdb_goods_types(type_name,parent_id) VALUES('家用电器',DEFAULT);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('电脑、办公',DEFAULT);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('大家电',1);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('生活电器',1);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('平板电视',3);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('空调',3);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('电风扇',4);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('饮水机',4);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('电脑整机',2);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('电脑配件',2);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('笔记本',9);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('超级本',9);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('游戏本',9);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('CPU',10);
    INSERT tdb_goods_types(type_name,parent_id) VALUES('主机',10);
```


</font>

[1]: http://chars.tech/2017/05/19/mysql-sql-study/
[4]: ./img/qM7FJj7.png
[5]: ./img/uEZ3mqF.png
[6]: ./img/v2Uviu6.png
[7]: ./img/RJVBrar.png
[8]: ./img/e2YJVra.png
[9]: ./img/Bvmqa2B.png
[10]: ./img/Bja6biY.png
[11]: ./img/eUjaa2U.png
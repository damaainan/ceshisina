# MySQL中常用SQL语句的编写

 时间 2017-10-13 14:02:57  

原文[http://www.linuxidc.com/Linux/2017-10/147567.htm][1]


#### 简述

#### 之前一直使用的django的orm模型，最近想学习下原生sql语句的编写。以后工作中可能不使用django，为了更好的工作和提高自己的知识全面点，记录下常用的sql语句编写。

#### 一、创建、删除、选择数据库

#### 1. 如果数据库不存在就创建

    CREATE DATABASE IF NOT EXISTS blog CHARACTER SET utf8 COLLATE utf8_general_ci;

#### 2. 如果数据库存在就删除

    DROP DATABASE IF EXISTS blog;

#### 3. 切换到我们选择的数据库，并查看库中所有表

    USE blog;
    SHOW TABLES;

#### 4. 数据库授权用户创建

    grant all on blog.* to blog@'%' identified by '123456';

#### 5. 查看数据库结构

    SHOW DATABASES LIKE 'blog%';

#### 6. 查询授权用户

    SELECT DISTINCT CONCAT('User: ''',user,'''@''',host,''';') AS query FROM mysql.user;

#### 7. 修改数据库用户密码

    USE mysql;
    UPDATE USER SET PASSWORD = PASSWORD ("new-password") WHERE USER = "root";
    FLUSH PRIVILEGES;

#### 二、MySQL数据类型介绍

#### MySQL 数据类型

MySQL中定义数据字段的类型对你数据库的优化是非常重要的。

MySQL支持多种类型，大致可以分为三类：数值、日期/时间和字符串(字符)类型。

#### 数值类型

MySQL支持所有标准SQL数值数据类型。

这些类型包括严格数值数据类型(INTEGER、SMALLINT、DECIMAL和NUMERIC)，以及近似数值数据类型(FLOAT、REAL和DOUBLE PRECISION)。

关键字INT是INTEGER的同义词，关键字DEC是DECIMAL的同义词。

BIT数据类型保存位字段值，并且支持MyISAM、MEMORY、InnoDB和BDB表。

作为SQL标准的扩展，MySQL也支持整数类型TINYINT、MEDIUMINT和BIGINT。下面的表显示了需要的每个整数类型的存储和范围。

类型 | 大小 | 范围（有符号） | 范围（无符号） | 用途 
-|-|-|-|-
TINYINT | 1 字节 | (-128，127) | (0，255) | 小整数值 
SMALLINT | 2 字节 | (-32 768，32 767) | (0，65 535) | 大整数值 
MEDIUMINT | 3 字节 | (-8 388 608，8 388 607) | (0，16 777 215) | 大整数值 
INT或INTEGER | 4 字节 | (-2 147 483 648，2 147 483 647) | (0，4 294 967 295) | 大整数值 
BIGINT | 8 字节 | (-9 233 372 036 854 775 808，9 223 372 036 854 775 807) | (0，18 446 744 073 709 551 615) | 极大整数值 
FLOAT | 4 字节 | (-3.402 823 466 E+38，-1.175 494 351 E-38)，0，(1.175 494 351 E-38，3.402 823 466 351 E+38) | 0，(1.175 494 351 E-38，3.402 823 466 E+38) | 单精度浮点数值 
DOUBLE | 8 字节 | (-1.797 693 134 862 315 7 E+308，-2.225 073 858 507 201 4 E-308)，0，(2.225 073 858 507 201 4 E-308，1.797 693 134 862 315 7 E+308) | 0，(2.225 073 858 507 201 4 E-308，1.797 693 134 862 315 7 E+308) | 双精度浮点数值 
DECIMAL | 对DECIMAL(M,D) ，如果M>D，为M+2否则为D+2 | 依赖于M和D的值 | 依赖于M和D的值 | 小数值 

#### 日期和时间类型

表示时间值的日期和时间类型为DATETIME、DATE、TIMESTAMP、TIME和YEAR。

每个时间类型有一个有效值范围和一个"零"值，当指定不合法的MySQL不能表示的值时使用"零"值。

类型 | 大小（字节） | 范围 | 格式 | 用途
-|-|-|-|- 
DATE | 3 | 1000-01-01/9999-12-31 | YYYY-MM-DD | 日期值 
TIME | 3 | '-838:59:59'/'838:59:59' | HH:MM:SS | 时间值或持续时间 
YEAR | 1 | 1901/2155 | YYYY | 年份值 
DATETIME | 8 | 1000-01-01 00:00:00/9999-12-31 23:59:59 | YYYY-MM-DD HH:MM:SS | 混合日期和时间值 
TIMESTAMP | 4 | 1970-01-01 00:00:00/2037 年某时 | YYYYMMDD HHMMSS | 混合日期和时间值，时间戳 

#### 字符串类型

字符串类型指CHAR、VARCHAR、BINARY、VARBINARY、BLOB、TEXT、ENUM和SET。该节描述了这些类型如何工作以及如何在查询中使用这些类型。

类型 | 大小 | 用途 
-|-|-
CHAR | 0-255字节 | 定长字符串 
VARCHAR | 0-65535 字节 | 变长字符串 
TINYBLOB | 0-255字节 | 不超过 255 个字符的二进制字符串 
TINYTEXT | 0-255字节 | 短文本字符串 
BLOB | 0-65 535字节 | 二进制形式的长文本数据 
TEXT | 0-65 535字节 | 长文本数据 
MEDIUMBLOB | 0-16 777 215字节 | 二进制形式的中等长度文本数据 
MEDIUMTEXT | 0-16 777 215字节 | 中等长度文本数据 
LONGBLOB | 0-4 294 967 295字节 | 二进制形式的极大文本数据 
LONGTEXT | 0-4 294 967 295字节 | 极大文本数据 

CHAR和VARCHAR类型类似，但它们保存和检索的方式不同。它们的最大长度和是否尾部空格被保留等方面也不同。在存储或检索过程中不进行大小写转换。

BINARY和VARBINARY类类似于CHAR和VARCHAR，不同的是它们包含二进制字符串而不要非二进制字符串。也就是说，它们包含字节字符串而不是字符字符串。这说明它们没有字符集，并且排序和比较基于列值字节的数值值。

BLOB是一个二进制大对象，可以容纳可变数量的数据。有4种BLOB类型：TINYBLOB、BLOB、MEDIUMBLOB和LONGBLOB。它们只是可容纳值的最大长度不同。

有4种TEXT类型：TINYTEXT、TEXT、MEDIUMTEXT和LONGTEXT。这些对应4种BLOB类型，有相同的最大长度和存储需求。

#### 三、MySQL数据的各种骚操作

#### 1. 创建数据表

创建MySQL数据表需要以下信息：

表名

表字段名

定义每个表字段

语法 

以下为创建MySQL数据表的SQL通用语法：

    CREATE TABLE table_name (column_name column_type);

以下例子中我们将在blog数据库中创建数据表author、article、tag以及article和tag的关联表article_tag

创建author表：

```sql
    CREATE TABLE IF NOT EXISTS `author`(
       `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '作者ID',
       `name` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '作者名字',
       `qq` BIGINT(20) NULL DEFAULT NULL COMMENT '作者QQ',
       `phone` BIGINT(20) NULL DEFAULT NULL COMMENT '作者电话',
       PRIMARY KEY ( `id` ),
       INDEX `name` (`name`) USING BTREE,
       UNIQUE INDEX `phone` (`phone`) USING BTREE
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    ROW_FORMAT=DYNAMIC
    ;
```
查看author表结构：

```sql
    mysql> desc author;
    +-------+-------------+------+-----+---------+----------------+
    | Field | Type        | Null | Key | Default | Extra          |
    +-------+-------------+------+-----+---------+----------------+
    | id    | int(11)     | NO   | PRI | NULL    | auto_increment |
    | name  | varchar(40) | YES  | MUL | NULL    |                |
    | qq    | bigint(20)  | YES  |     | NULL    |                |
    | phone | bigint(20)  | YES  | UNI | NULL    |                |
    +-------+-------------+------+-----+---------+----------------+
```
创建article表：

```sql
    CREATE TABLE IF NOT EXISTS `article`(
       `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '文章ID',
       `title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文章标题',
       `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文章内容',
       `author_id` INT(11) NOT NULL COMMENT '作者ID',
       `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
       PRIMARY KEY ( `id` ),
       FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
       UNIQUE INDEX `author_id` (`author_id`) USING BTREE
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    ROW_FORMAT=DYNAMIC
    ;
```
查看article表结构：

```sql
    mysql> desc article;
    +-------------+--------------+------+-----+---------+----------------+
    | Field       | Type         | Null | Key | Default | Extra          |
    +-------------+--------------+------+-----+---------+----------------+
    | id          | int(11)      | NO   | PRI | NULL    | auto_increment |
    | title       | varchar(100) | YES  |     | NULL    |                |
    | content     | text         | YES  |     | NULL    |                |
    | author_id   | int(11)      | NO   | MUL | NULL    |                |
    | create_time | date         | YES  |     | NULL    |                |
    +-------------+--------------+------+-----+---------+----------------+
```
创建tag表：

```sql
    CREATE TABLE IF NOT EXISTS `tag`(
       `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
       `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标签名称',
       PRIMARY KEY ( `id` )
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    ROW_FORMAT=DYNAMIC
    ;
```
查看tag表结构：

```sql
    mysql> desc tag;
    +-------+--------------+------+-----+---------+----------------+
    | Field | Type         | Null | Key | Default | Extra          |
    +-------+--------------+------+-----+---------+----------------+
    | id    | int(11)      | NO   | PRI | NULL    | auto_increment |
    | name  | varchar(100) | YES  |     | NULL    |                |
    +-------+--------------+------+-----+---------+----------------+
```
创建article_tag表：

```sql
    CREATE TABLE IF NOT EXISTS `article_tag`(
       `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '文章标签关联表ID',
       `article_id` INT(11) NOT NULL COMMENT '文章ID',
       `tag_id` INT(11) NOT NULL COMMENT '标签ID',
       PRIMARY KEY ( `id` ),
       FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
       FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
       UNIQUE INDEX `article_tag_unique` (`article_id`, `tag_id`) USING BTREE ,
       INDEX `article_id` (`article_id`) USING BTREE 
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    ROW_FORMAT=DYNAMIC
    ;
```
查看article_tag表结构：

```sql
    mysql> desc article_tag;
    +------------+---------+------+-----+---------+----------------+
    | Field      | Type    | Null | Key | Default | Extra          |
    +------------+---------+------+-----+---------+----------------+
    | id         | int(11) | NO   | PRI | NULL    | auto_increment |
    | article_id | int(11) | NO   | MUL | NULL    |                |
    | tag_id     | int(11) | NO   | MUL | NULL    |                |
    +------------+---------+------+-----+---------+----------------+
```
语句解析：

如果你不想字段为 NULL 可以设置字段的属性为 NOT NULL， 在操作数据库时如果输入该字段的数据为NULL ，就会报错。

AUTO_INCREMENT定义列为自增的属性，一般用于主键，数值会自动加1。

PRIMARY KEY关键字用于定义列为主键。 您可以使用多列来定义主键，列间以逗号分隔。

ENGINE 设置存储引擎， CHARACTER SET设置编码。

INDEX设置该字段为索引，UNIQUE INDEX设置字段值唯一，并且设置该字段为索引。

COMMENT给该字段添加注释。

ROW_FORMAT=DYNAMIC，设置表为动态表（在mysql中， 若一张表里面不存在varchar、text以及其变形、blob以及其变形的字段的话，那么张这个表其实也叫静态表，即该表的row_format是fixed，就是说每条记录所占用的字节一样。其优点读取快，缺点浪费额外一部分空间。 若一张表里面存在varchar、text以及其变形、blob以及其变形的字段的话，那么张这个表其实也叫动态表，即该表的row_format是dynamic，就是说每条记录所占用的字节是动态的。其优点节省空间，缺点增加读取的时间开销。所以，做搜索查询量大的表一般都以空间来换取时间，设计成静态表）。

数据表解析：

author是作者表，有4个字段：id, name, qq, phone

article是文章表，文章和作者是多对一的关系，这里使用外键方式关联。字段author_id关联的是author的id字段。

tag是标签表，有2个字段：id, name

文章和标签是多对多的关系（ManyToMany），这里使用第三张表article_tag把它们关联起来。字段article_id外键关联的是article的id字段，字段tag_id外键关联的是tag的id字段。

#### 2. 删除数据表

    DROP TABLE IF EXISTS article_tag;

#### 3. 插入数据 

MySQL 表中使用 INSERT INTO SQL语句来插入数据。

你可以通过 mysql> 命令提示窗口中向数据表中插入数据，或者通过PHP脚本来插入数据。

语法 

以下为向MySQL数据表插入数据通用的 INSERT INTO SQL语法：

    INSERT INTO table_name ( field1, field2,...fieldN )
                           VALUES
                           ( value1, value2,...valueN );

如果数据是字符型，必须使用单引号或者双引号，如："value"。

author表插入几条数据：

```sql
    INSERT INTO author(name, qq, phone) VALUES('君惜', 123456, 18500178899), ('糖糖', 234567, 13256987582), ('琳琳', 345678, 15636589521);
```
查看author表：

```sql
    mysql> SELECT * FROM author;
    +----+------+--------+-------------+
    | id | name | qq     | phone       |
    +----+------+--------+-------------+
    |  1 | 君惜 | 123456 | 18500178899 |
    |  2 | 糖糖 | 234567 | 13256987582 |
    |  3 | 琳琳 | 345678 | 15636589521 |
    +----+------+--------+-------------+
```
article表插入几条数据：

```sql
    INSERT INTO article(title, content, author_id) VALUES('流畅的python', 'Python各种拽', 1), ('嘻哈', '中国有嘻哈', 2), ('严肃', '你这辈子就是吃了太严肃的亏', 3);
```
查看article表：

```sql
    mysql> select * from article;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:43 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:43 |
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    +----+--------------+----------------------------+-----------+---------------------+
```
tag表插入数据：

```sql
    INSERT INTO tag(name) VALUES('技术'), ('娱乐'), ('文学');
```
查看tag表：

```sql
    mysql> select * from tag;
    +----+------+
    | id | name |
    +----+------+
    |  1 | 技术 |
    |  2 | 娱乐 |
    |  3 | 文学 |
    +----+------+
```
article_tag表插入数据：

```sql
    INSERT INTO article_tag(article_id, tag_id) VALUES(1, 1), (2, 2), (3, 3);
```
查看article_tag表：

```sql
    mysql> select * from article_tag;
    +----+------------+--------+
    | id | article_id | tag_id |
    +----+------------+--------+
    |  1 |          1 |      1 |
    |  2 |          2 |      2 |
    |  3 |          3 |      3 |
    +----+------------+--------+
```
#### 4. 查询数据 

MySQL 数据库使用SQL SELECT语句来查询数据。

你可以通过 mysql> 命令提示窗口中在数据库中查询数据，或者通过PHP脚本来查询数据。

语法 

以下为在MySQL数据库中查询数据通用的 SELECT 语法：

    SELECT column_name,column_name
    FROM table_name
    [WHERE Clause]
    [OFFSET M ][LIMIT N]

* 查询语句中你可以使用一个或者多个表，表之间使用逗号(,)分割，并使用WHERE语句来设定查询条件。
* SELECT 命令可以读取一条或者多条记录。
* 你可以使用星号（*）来代替其他字段，SELECT语句会返回表的所有字段数据
* 你可以使用 WHERE 语句来包含任何条件。
* 你可以通过OFFSET指定SELECT语句开始查询的数据偏移量。默认情况下偏移量为0。
* 你可以使用 LIMIT 属性来设定返回的记录数。

实例 

以下实例将返回数据表article的所有记录

```sql
    mysql> select * from article;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:43 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:43 |
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    +----+--------------+----------------------------+-----------+---------------------+
```
查询指定字段数据

```sql
    mysql> select title, content from article;
    +--------------+----------------------------+
    | title        | content                    |
    +--------------+----------------------------+
    | 流畅的python | Python各种拽               |
    | 嘻哈         | 中国有嘻哈                 |
    | 严肃         | 你这辈子就是吃了太严肃的亏 |
    +--------------+----------------------------+
```
#### 5. WHERE 子句 

我们知道从 MySQL 表中使用 SQL SELECT 语句来读取数据。

如需有条件地从表中选取数据，可将 WHERE 子句添加到 SELECT 语句中。

语法 

以下是 SQL SELECT 语句使用 WHERE 子句从数据表中读取数据的通用语法：

    SELECT field1, field2,...fieldN FROM table_name1, table_name2...
    [WHERE condition1 [AND [OR]] condition2.....

* 查询语句中你可以使用一个或者多个表，表之间使用逗号, 分割，并使用WHERE语句来设定查询条件。
* 你可以在 WHERE 子句中指定任何条件。
* 你可以使用 AND 或者 OR 指定一个或多个条件。
* WHERE 子句也可以运用于 SQL 的 DELETE 或者 UPDATE 命令。
* WHERE 子句类似于程序语言中的 if 条件，根据 MySQL 表中的字段值来读取指定的数据。

以下为操作符列表，可用于 WHERE 子句中。

下表中实例假定 A 为 10, B 为 20

操作符 | 描述 | 实例 
-|-|-
`=` | 等号，检测两个值是否相等，如果相等返回true | (A = B) 返回false。 
`<>`, `!=` | 不等于，检测两个值是否相等，如果不相等返回true | (A != B) 返回 true。 
`>` | 大于号，检测左边的值是否大于右边的值, 如果左边的值大于右边的值返回true | (A > B) 返回false。 
`<` | 小于号，检测左边的值是否小于右边的值, 如果左边的值小于右边的值返回true | (A < B) 返回 true。 
`>=` | 大于等于号，检测左边的值是否大于或等于右边的值, 如果左边的值大于或等于右边的值返回true | (A >= B) 返回false。 
`<=` | 小于等于号，检测左边的值是否小于于或等于右边的值, 如果左边的值小于或等于右边的值返回true | (A <= B) 返回 true。 


如果我们想再 MySQL 数据表中读取指定的数据，WHERE 子句是非常有用的。

使用主键来作为 WHERE 子句的条件查询是非常快速的。

如果给定的条件在表中没有任何匹配的记录，那么查询不会返回任何数据。

实例 

以下实例将读取article表中title字段值为 嘻哈 的所有记录：

```sql
    mysql> select * from article where title="嘻哈";
    +----+-------+------------+-----------+---------------------+
    | id | title | content    | author_id | create_time         |
    +----+-------+------------+-----------+---------------------+
    |  2 | 嘻哈  | 中国有嘻哈 |         2 | 2017-09-12 16:36:43 |
    +----+-------+------------+-----------+---------------------+
```
#### 6. UPDATE 语句 

如果我们需要修改或更新 MySQL 中的数据，我们可以使用 SQL UPDATE 命令来操作。

语法 

以下是 UPDATE 命令修改 MySQL 数据表数据的通用 SQL 语法：

    UPDATE table_name SET field1=new-value1, field2=new-value2
    [WHERE Clause]

* 你可以同时更新一个或多个字段。
* 你可以在 WHERE 子句中指定任何条件。
* 你可以在一个单独表中同时更新数据。

当你需要更新数据表中指定行的数据时 WHERE 子句是非常有用的。

通过命令提示符更新数据

以下我们将在 SQL UPDATE 命令使用 WHERE 子句来更新 author 表中指定的数据：

实例 

以下实例将更新数据表中 id 为 1 的 qq 字段值：

```sql
    mysql> update author set qq='2298630081' where id=1;
    Query OK, 1 row affected (0.02 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> select * from author where id=1;
    +----+------+------------+-------------+
    | id | name | qq         | phone       |
    +----+------+------------+-------------+
    |  1 | 君惜 | 2298630081 | 18500178899 |
    +----+------+------------+-------------+
    1 row in set (0.00 sec)
```
#### 7. DELETE 语句 

你可以使用 SQL 的 DELETE FROM 命令来删除 MySQL 数据表中的记录。

你可以在 mysql> 命令提示符或 PHP 脚本中执行该命令。

语法 

以下是 SQL DELETE 语句从 MySQL 数据表中删除数据的通用语法：

    DELETE FROM table_name [WHERE Clause]

* 如果没有指定 WHERE 子句，MySQL 表中的所有记录将被删除。
* 你可以在 WHERE 子句中指定任何条件
* 您可以在单个表中一次性删除记录。

当你想删除数据表中指定的记录时 WHERE 子句是非常有用的。

实例 

插入一条数据：

    INSERT INTO author(name, qq, phone) VALUES('悦悦','456789','13343809438');

删除 author 表中 name 为 悦悦 的记录：

```sql
    mysql> delete from author where name="悦悦";
    Query OK, 1 row affected (0.01 sec)
    
    mysql> select * from author;
    +----+------+------------+-------------+
    | id | name | qq         | phone       |
    +----+------+------------+-------------+
    |  1 | 君惜 | 2298630081 | 18500178899 |
    |  2 | 糖糖 |     234567 | 13256987582 |
    |  3 | 琳琳 |     345678 | 15636589521 |
    +----+------+------------+-------------+
    3 rows in set (0.00 sec)
```
#### 8. LIKE 子句

我们知道在 MySQL 中使用 SQL SELECT 命令来读取数据， 同时我们可以在 SELECT 语句中使用 WHERE 子句来获取指定的记录。

WHERE 子句中可以使用等号 = 来设定获取数据的条件，如 "runoob_author = 'RUNOOB.COM'"。

但是有时候我们需要获取 runoob_author 字段含有 "COM" 字符的所有记录，这时我们就需要在 WHERE 子句中使用 SQL LIKE 子句。

SQL LIKE 子句中使用百分号 %字符来表示任意字符，类似于UNIX或正则表达式中的星号 *。

如果没有使用百分号 %, LIKE 子句与等号 = 的效果是一样的。

语法 

以下是 SQL SELECT 语句使用 LIKE 子句从数据表中读取数据的通用语法：

    SELECT field1, field2,...fieldN 
    FROM table_name
    WHERE field1 LIKE condition1 [AND [OR]] filed2 = 'somevalue'

* 你可以在 WHERE 子句中指定任何条件。
* 你可以在 WHERE 子句中使用LIKE子句。
* 你可以使用LIKE子句代替等号 =。
* LIKE 通常与 % 一同使用，类似于一个元字符的搜索。
* 你可以使用 AND 或者 OR 指定一个或多个条件。
* 你可以在 DELETE 或 UPDATE 命令中使用 WHERE...LIKE 子句来指定条件。

实例 

插入几条数据：

```sql
    insert into author(name, qq, phone) values('李天星', '5678911', '13345607861'), ('王星', '5678912', '13345607862'), ('张星星', '5678913', '13345607863');
```
查询 author 表 name 字段中以星为结尾的的所有记录：

```sql
    mysql> select * from author where name like '%星';
    +----+--------+---------+-------------+
    | id | name   | qq      | phone       |
    +----+--------+---------+-------------+
    |  5 | 李天星 | 5678911 | 13345607861 |
    |  6 | 王星   | 5678912 | 13345607862 |
    |  7 | 张星星 | 5678913 | 13345607863 |
    +----+--------+---------+-------------+
    3 rows in set (0.01 sec)
```
#### 9. UNION 操作符 

MySQL UNION 操作符用于连接两个以上的 SELECT 语句的结果组合到一个结果集合中。多个 SELECT 语句会删除重复的数据。

语法 

MySQL UNION 操作符语法格式：

    SELECT expression1, expression2, ... expression_n
    FROM tables
    [WHERE conditions]
    UNION [ALL | DISTINCT]
    SELECT expression1, expression2, ... expression_n
    FROM tables
    [WHERE conditions];

#### 参数

* expression1, expression2, ... expression_n: 要检索的列。
* tables: 要检索的数据表。
* WHERE conditions: 可选， 检索条件。
* DISTINCT: 可选，删除结果集中重复的数据。默认情况下 UNION 操作符已经删除了重复数据，所以 DISTINCT 修饰符对结果没啥影响。
* ALL: 可选，返回所有结果集，包含重复数据。

#### articles表

```sql
    mysql> select * from articles;
    +----+--------------+----------------------------+---------+
    | id | title        | content                    | user_id |
    +----+--------------+----------------------------+---------+
    |  1 | 中国有嘻哈   | 哈哈哈                     |       1 |
    |  2 | 星光大道     | 成名之路                   |       2 |
    |  3 | 平凡的真谛   | 开心即完美                 |       3 |
    |  4 | python进阶   | Python高级用法             |       1 |
    |  5 | 流畅的python | 就问你流畅不流畅            |       1 |
    |  6 | 严肃         | 你这辈子就是吃了太严肃的亏 |       3 |
    +----+--------------+----------------------------+---------+
    6 rows in set (0.00 sec)
```
#### article表

```sql
    mysql> select * from article;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:43 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:43 |
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    +----+--------------+----------------------------+-----------+---------------------+
    3 rows in set (0.00 sec)
```
SQL UNION 实例 

下面的 SQL 语句从 article 和 articles 表中选取所有不同的title（只有不同的值）：

```sql
    mysql> select title from article union select title from articles order by title;
    +--------------+
    | title        |
    +--------------+
    | python进阶   |
    | 严肃         |
    | 中国有嘻哈   |
    | 嘻哈         |
    | 平凡的真谛   |
    | 星光大道     |
    | 流畅的python |
    +--------------+
    7 rows in set (0.00 sec)
```
注释：UNION 不能用于列出两个表中所有的title。如果出现重复的数据，只会列出一次。UNION 只会选取不同的值。请使用 UNION ALL 来选取重复的值！

SQL UNION ALL 实例 

下面的 SQL 语句使用 UNION ALL 从 "article" 和 "articles" 表中选取所有的title（也有重复的值）：

```sql
    mysql> select title from article union all select title from articles order by title;
    +--------------+
    | title        |
    +--------------+
    | python进阶   |
    | 严肃         |
    | 严肃         |
    | 中国有嘻哈   |
    | 嘻哈         |
    | 平凡的真谛   |
    | 星光大道     |
    | 流畅的python |
    | 流畅的python |
    +--------------+
    9 rows in set (0.00 sec)
```
带有 WHERE 的 SQL UNION ALL 

下面的 SQL 语句使用 UNION ALL 从 "article" 和 "articles" 表中选取所标题(title)为流畅的python的书籍（也有重复的值）：

```sql
    mysql> select title, content from article where title='流畅的python' union all select title, content from articles  where title='流畅的python' order by title;
    +--------------+------------------+
    | title        | content          |
    +--------------+------------------+
    | 流畅的python | Python各种拽     |
    | 流畅的python | 就问你流畅不流畅 |
    +--------------+------------------+
    2 rows in set (0.00 sec)
```
#### 10. 排序 

我们知道从 MySQL 表中使用 SQL SELECT 语句来读取数据。

如果我们需要对读取的数据进行排序，我们就可以使用 MySQL 的 ORDER BY 子句来设定你想按哪个字段哪种方式来进行排序，再返回搜索结果。

语法 

以下是 SQL SELECT 语句使用 ORDER BY 子句将查询数据排序后再返回数据：

    SELECT field1, field2,...fieldN table_name1, table_name2...
    ORDER BY field1, [field2...] [ASC [DESC]]

* 你可以使用任何字段来作为排序的条件，从而返回排序后的查询结果。
* 你可以设定多个字段来排序。
* 你可以使用 ASC 或 DESC 关键字来设置查询结果是按升序或降序排列。 默认情况下，它是按升序排列。
* 你可以添加 WHERE...LIKE 子句来设置条件。

实例 

尝试以下实例，结果将按升序及降序排列。

```sql
    mysql> select * from article order by create_time asc;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:41 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:42 |
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    +----+--------------+----------------------------+-----------+---------------------+
    3 rows in set (0.00 sec)
    
    mysql> select * from article order by create_time desc;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:42 |
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:41 |
    +----+--------------+----------------------------+-----------+---------------------+
    3 rows in set (0.00 sec)
```
#### 11. GROUP BY 语句 

GROUP BY 语句根据一个或多个列对结果集进行分组。

在分组的列上我们可以使用 COUNT, SUM, AVG,等函数。

#### GROUP BY 语法

    SELECT column_name, function(column_name)
    FROM table_name
    WHERE column_name operator value
    GROUP BY column_name;

实例演示 

实例使用到了以下表结构及数据，使用前我们可以先将以下数据导入数据库中。

```sql
    SET NAMES utf8;
    SET FOREIGN_KEY_CHECKS = 0;
    
    -- ----------------------------
    --  Table structure for `user_login`
    -- ----------------------------
    DROP TABLE IF EXISTS `user_login`;
    CREATE TABLE `user_login` (
      `id` int(11) NOT NULL,
      `name` char(10) NOT NULL DEFAULT '',
      `date` datetime NOT NULL,
      `singin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '登录次数',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    -- ----------------------------
    --  Records of `user_login`
    -- ----------------------------
    BEGIN;
    INSERT INTO `user_login` VALUES ('1', '小明', '2016-04-22 15:25:33', '1'), ('2', '小王', '2016-04-20 15:25:47', '3'), ('3', '小丽', '2016-04-19 15:26:02', '2'), ('4', '小王', '2016-04-07 15:26:14', '4'), ('5', '小明', '2016-04-11 15:26:40', '4'), ('6', '小明', '2016-04-04 15:26:54', '2');
    COMMIT;
    
    SET FOREIGN_KEY_CHECKS = 1;
```
导入成功后，执行以下 SQL 语句：

```sql
    mysql> select * from user_login;
    +----+------+---------------------+--------+
    | id | name | date                | singin |
    +----+------+---------------------+--------+
    |  1 | 小明 | 2016-04-22 15:25:33 |      1 |
    |  2 | 小王 | 2016-04-20 15:25:47 |      3 |
    |  3 | 小丽 | 2016-04-19 15:26:02 |      2 |
    |  4 | 小王 | 2016-04-07 15:26:14 |      4 |
    |  5 | 小明 | 2016-04-11 15:26:40 |      4 |
    |  6 | 小明 | 2016-04-04 15:26:54 |      2 |
    +----+------+---------------------+--------+
    6 rows in set (0.00 sec)
```
接下来我们使用 GROUP BY 语句 将数据表按名字进行分组，并统计每个人有多少条记录：

```sql
    mysql> select name, count(*) from user_login group by name;
    +------+----------+
    | name | count(*) |
    +------+----------+
    | 小丽 |        1 |
    | 小明 |        3 |
    | 小王 |        2 |
    +------+----------+
    3 rows in set (0.00 sec)
```
使用 WITH ROLLUP 

WITH ROLLUP 可以实现在分组统计数据基础上再进行相同的统计（SUM,AVG,COUNT…）。

例如我们将以上的数据表按名字进行分组，再统计每个人登录的次数：

```sql
    mysql> select name, sum(singin) as singin_count from user_login group by name with rollup;
    +------+--------------+
    | name | singin_count |
    +------+--------------+
    | 小丽 |            2 |
    | 小明 |            7 |
    | 小王 |            7 |
    | NULL |           16 |
    +------+--------------+
    4 rows in set (0.00 sec)
```
其中记录 NULL 表示所有人的登录次数。

我们可以使用 coalesce 来设置一个可以取代 NUll 的名称，coalesce 语法：

    select coalesce(a,b,c);

参数说明：如果a==null,则选择b；如果b==null,则选择c；如果a!=null,则选择a；如果a b c 都为null ，则返回为null（没意义）。

以下实例中如果名字为空我们使用总数代替：

```sql
    mysql> select coalesce(name, '总数'), sum(singin) as singin_count from user_login group by name with rollup;
    +------------------------+--------------+
    | coalesce(name, '总数') | singin_count |
    +------------------------+--------------+
    | 小丽                   |            2 |
    | 小明                   |            7 |
    | 小王                   |            7 |
    | 总数                   |           16 |
    +------------------------+--------------+
    4 rows in set (0.00 sec)
```
#### 12. 多表连查 

在真正的应用中经常需要从多个数据表中读取数据。下面将向大家介绍如何使用 MySQL 的 JOIN 在两个或多个表中查询数据。

你可以在 SELECT, UPDATE 和 DELETE 语句中使用 Mysql 的 JOIN 来联合多表查询。

JOIN 按照功能大致分为如下三类：

* INNER JOIN（内连接,或等值连接）：获取两个表中字段匹配关系的记录。
* LEFT JOIN（左连接）：获取左表所有记录，即使右表没有对应匹配的记录。
* RIGHT JOIN（右连接）：与 LEFT JOIN 相反，用于获取右表所有记录，即使左表没有对应匹配的记录。

#### 我们在blog数据库中有四张表 author、article、tag、article_tag。数据表数据如下：

```sql
    mysql> select * from author;
    +----+--------+------------+-------------+
    | id | name   | qq         | phone       |
    +----+--------+------------+-------------+
    |  1 | 君惜   | 2298630081 | 18500178899 |
    |  2 | 糖糖   |     234567 | 13256987582 |
    |  3 | 琳琳   |     345678 | 15636589521 |
    |  5 | 李天星 |    5678911 | 13345607861 |
    |  6 | 王星   |    5678912 | 13345607862 |
    |  7 | 张星星 |    5678913 | 13345607863 |
    +----+--------+------------+-------------+
    6 rows in set (0.00 sec)
    
    mysql> select * from article;
    +----+--------------+----------------------------+-----------+---------------------+
    | id | title        | content                    | author_id | create_time         |
    +----+--------------+----------------------------+-----------+---------------------+
    |  1 | 流畅的python | Python各种拽               |         1 | 2017-09-12 16:36:41 |
    |  2 | 嘻哈         | 中国有嘻哈                 |         2 | 2017-09-12 16:36:42 |
    |  3 | 严肃         | 你这辈子就是吃了太严肃的亏 |         3 | 2017-09-12 16:36:43 |
    +----+--------------+----------------------------+-----------+---------------------+
    3 rows in set (0.00 sec)
    
    mysql> select * from tag;
    +----+------+
    | id | name |
    +----+------+
    |  1 | 技术 |
    |  2 | 娱乐 |
    |  3 | 文学 |
    +----+------+
    3 rows in set (0.00 sec)
    
    mysql> select * from article_tag;
    +----+------------+--------+
    | id | article_id | tag_id |
    +----+------------+--------+
    |  1 |          1 |      1 |
    |  2 |          2 |      2 |
    |  3 |          3 |      3 |
    +----+------------+--------+
    3 rows in set (0.01 sec)
```
INNER JOIN 实例 

使用 INNER JOIN查询article中author_id等于author的id的数据（这里SQL语句中INNER可以省略）：

```sql
    mysql> select name, qq, phone, title, content, create_time from author as u join article as a on u.id=a.author_id;
    +------+------------+-------------+--------------+----------------------------+---------------------+
    | name | qq         | phone       | title        | content                    | create_time         |
    +------+------------+-------------+--------------+----------------------------+---------------------+
    | 君惜 | 2298630081 | 18500178899 | 流畅的python | Python各种拽               | 2017-09-12 16:36:41 |
    | 糖糖 |     234567 | 13256987582 | 嘻哈         | 中国有嘻哈                 | 2017-09-12 16:36:42 |
    | 琳琳 |     345678 | 15636589521 | 严肃         | 你这辈子就是吃了太严肃的亏 | 2017-09-12 16:36:43 |
    +------+------------+-------------+--------------+----------------------------+---------------------+
    3 rows in set (0.00 sec)
```
以上SQL语句等价于：

where 子句

```sql
    mysql> select name, qq, phone, title, content, create_time from author as u join article as a where u.id=a.author_id;
    +------+------------+-------------+--------------+----------------------------+---------------------+
    | name | qq         | phone       | title        | content                    | create_time         |
    +------+------------+-------------+--------------+----------------------------+---------------------+
    | 君惜 | 2298630081 | 18500178899 | 流畅的python | Python各种拽               | 2017-09-12 16:36:41 |
    | 糖糖 |     234567 | 13256987582 | 嘻哈         | 中国有嘻哈                 | 2017-09-12 16:36:42 |
    | 琳琳 |     345678 | 15636589521 | 严肃         | 你这辈子就是吃了太严肃的亏 | 2017-09-12 16:36:43 |
    +------+------------+-------------+--------------+----------------------------+---------------------+
    3 rows in set (0.00 sec)
```

利用第三张表连接查询

```sql
    mysql> select title as '书名', content as '内容', name as '标签', create_time as "创建时间" from article, tag inner join article_tag as at where at.article_id=article.id and at.tag_id=tag.id;
    +--------------+----------------------------+------+---------------------+
    | 书名         | 内容                       | 标签 | 创建时间            |
    +--------------+----------------------------+------+---------------------+
    | 流畅的python | Python各种拽               | 技术 | 2017-09-12 16:36:41 |
    | 嘻哈         | 中国有嘻哈                 | 娱乐 | 2017-09-12 16:36:42 |
    | 严肃         | 你这辈子就是吃了太严肃的亏 | 文学 | 2017-09-12 16:36:43 |
    +--------------+----------------------------+------+---------------------+
    3 rows in set (0.00 sec)
```

```sql
    mysql> select au.name as '作者', ar.title as '书名', ar.content as '内容', t.name as '标签', ar.create_time as '创建时间' from author as au, article as ar, tag as t inner join article_tag as at where au.id=ar.author_id and at.art
    icle_id=ar.id and at.tag_id=t.id;
    +------+--------------+----------------------------+------+---------------------+
    | 作者 | 书名         | 内容                       | 标签 | 创建时间            |
    +------+--------------+----------------------------+------+---------------------+
    | 君惜 | 流畅的python | Python各种拽               | 技术 | 2017-09-12 16:36:41 |
    | 糖糖 | 嘻哈         | 中国有嘻哈                 | 娱乐 | 2017-09-12 16:36:42 |
    | 琳琳 | 严肃         | 你这辈子就是吃了太严肃的亏 | 文学 | 2017-09-12 16:36:43 |
    +------+--------------+----------------------------+------+---------------------+
    3 rows in set (0.00 sec)
```
LEFT JOIN 实例 

MySQL left join 与 join 有所不同。 MySQL LEFT JOIN 会读取左边数据表的全部数据，即便右边表无对应数据。

以 author 为左表，article为右表。右表无对应数据自动填充为NULL：

```sql
    mysql> select name, qq, phone, title, content, create_time from author as u left join article as a on u.id=a.author_id;
    +--------+------------+-------------+--------------+----------------------------+---------------------+
    | name   | qq         | phone       | title        | content                    | create_time         |
    +--------+------------+-------------+--------------+----------------------------+---------------------+
    | 君惜   | 2298630081 | 18500178899 | 流畅的python | Python各种拽               | 2017-09-12 16:36:41 |
    | 糖糖   |     234567 | 13256987582 | 嘻哈         | 中国有嘻哈                 | 2017-09-12 16:36:42 |
    | 琳琳   |     345678 | 15636589521 | 严肃         | 你这辈子就是吃了太严肃的亏 | 2017-09-12 16:36:43 |
    | 李天星 |    5678911 | 13345607861 | NULL         | NULL                       | NULL                |
    | 王星   |    5678912 | 13345607862 | NULL         | NULL                       | NULL                |
    | 张星星 |    5678913 | 13345607863 | NULL         | NULL                       | NULL                |
    +--------+------------+-------------+--------------+----------------------------+---------------------+
    6 rows in set (0.00 sec)
```
RIGHT JOIN 实例 

MySQL RIGHT JOIN 会读取右边数据表的全部数据，即便左边边表无对应数据。

以 article 为左表，author为右表，左表无对应数据自动填充为NULL。：

```sql
    mysql> select title, content, create_time, name, qq, phone from article as a right join author as u on u.id=a.author_id;
    +--------------+----------------------------+---------------------+--------+------------+-------------+
    | title        | content                    | create_time         | name   | qq         | phone       |
    +--------------+----------------------------+---------------------+--------+------------+-------------+
    | 流畅的python | Python各种拽               | 2017-09-12 16:36:41 | 君惜   | 2298630081 | 18500178899 |
    | 嘻哈         | 中国有嘻哈                 | 2017-09-12 16:36:42 | 糖糖   |     234567 | 13256987582 |
    | 严肃         | 你这辈子就是吃了太严肃的亏 | 2017-09-12 16:36:43 | 琳琳   |     345678 | 15636589521 |
    | NULL         | NULL                       | NULL                | 李天星 |    5678911 | 13345607861 |
    | NULL         | NULL                       | NULL                | 王星   |    5678912 | 13345607862 |
    | NULL         | NULL                       | NULL                | 张星星 |    5678913 | 13345607863 |
    +--------------+----------------------------+---------------------+--------+------------+-------------+
    6 rows in set (0.00 sec)
    
    mysql> select title, content, create_time, name, qq, phone from article as a right join author as u on u.id=a.author_id where title is not null;
    +--------------+----------------------------+---------------------+------+------------+-------------+
    | title        | content                    | create_time         | name | qq         | phone       |
    +--------------+----------------------------+---------------------+------+------------+-------------+
    | 流畅的python | Python各种拽               | 2017-09-12 16:36:41 | 君惜 | 2298630081 | 18500178899 |
    | 嘻哈         | 中国有嘻哈                 | 2017-09-12 16:36:42 | 糖糖 |     234567 | 13256987582 |
    | 严肃         | 你这辈子就是吃了太严肃的亏 | 2017-09-12 16:36:43 | 琳琳 |     345678 | 15636589521 |
    +--------------+----------------------------+---------------------+------+------------+-------------+
    3 rows in set (0.00 sec)
```
先记录到这了。


[1]: http://www.linuxidc.com/Linux/2017-10/147567.htm

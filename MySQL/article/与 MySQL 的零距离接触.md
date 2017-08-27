## 与 MySQL 的零距离接触

<font face=微软雅黑>

[MySQL][0] 是一个关系型数据库管理系统，由瑞典 MySQL AB 公司开发，目前属于 Oracle 旗下产品。MySQL 是最流行的关系型数据库管理系统之一，在 WEB 应用方面，MySQL 是最好的 RDBMS (Relational Database Management System，关系数据库管理系统) 应用软件。 

MySQL 是一个开源的关系型数据库管理系统，分为社区版和企业版。

## 1 安装 

直接前往官网 [https://www.mysql.com/][0] ，进入 download 页面下载所需对应安装版本。默认配置安装即可。 

## 2 配置 

安装成功之后，需要修改密码。详看《MySQL 安装配置》 

修改编码方式：

    [mysql]
    default-character-set=utf8
    
    [mysql]
    character-set-server=utf8
    

## 3 目录结构 

`bin` 目录，存储可执行文件。

`data` 目录，存储数据文件。

`docs`，文档。

`include` 目录，存储包含的头文件。

`lib` 目录，存储库文件。

`share`，错误消息和字符集文件。

## 4 命令参数说明 

参数  | 描述
-|-
-D,–database=name   | 打开指定数据库
–delimiter=name | 指定分隔符
-h,–host=name   | 服务器名称
-p,–password[=name] | 密码
-P,–port=#  | 端口号
–prompt=name    | 设置提示符
-u,–user=name   | 用户名
-V,–version | 输出版本信息并退出

## MySQL 提示符 

参数  | 描述
-|-
`\D`  | 完整的日期
`\d`  | 当前数据库
`\h`  | 服务器名称
`\u`  | 当前用户

## 命令使用 

### 修改 MySQL 提示符 

1. 连接客户端时通过参数指定
```
    mysql -uroot -proot --prompt 提示符
```
2. 连接上客户端后，通过 prompt 命令修改
```
    mysql>prompt 提示符
```

### MySQL 常用命令 

1. 显示当前服务器版本
```
    SELECT VERSION();
```

2. 显示当前日期时间
```
    SELECT NOW();
```

3. 显示当前用户
```
    SELECT USER();
```

### MySQL 语句规范 

1. 关键字与函数名称 **全部大写**。

2. 数据库名称、表名称、字段名称 **全部小写**。

3. SQL 语句必须以**`;`** 符号结尾。

## 5 SQL 

![][1]

## 数据库操作 

### 创建数据库 

```
    CREATE {DATABASE | SCHEMA} [IF NOT EXISTS] db_name [DEFAULT] CHARACTER SET [=] character_name
```

`{}` 表示为 **必选项**，即 **必填参数**。 `|` 表示选项，即其中选取一项即可。 `[]` 表示为可选项。 

`character_name` 参数为指定数据库的编码方式，不填则使用 MySQL 配置的字符集编码。 

### 查看当前服务器下的数据表列表 

    SHOW {DATABASES | SCHEMAS} [LIKE 'pattern' | WHERE expr]


### 删除数据库 

    DROP {DATABASE | SCHEMA} [IF NOT EXISTS] db_name

## AUTO_INCREMENT 

自动编号，且必须与主键组合使用。

数值型数据。

默认情况下，起始值为 1，每次的增量为 1。

## 约束 

1. 保证数据的完整性和一致性。

2. 分为表级约束（针对两个或两个以上的字段进行约束）和列级约束（针对某一个字段进行约束）。

3. 类型包括：

    * `NOT NULL` 非空约束
    * `PRIMARY KEY` 主键约束
    * `UNIQUE KEY` 唯一约束
    * `DEFAULT` 默认约束
    * `FOREIGN KEY` 外键约束

```sql
    CREATE TABLE t6(id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20) NOT NULL UNIQUE KEY, sex ENUM('1', '2', '3') DEFAULT '3');
```

![][2]

    INSERT t6 (username) VALUES ('Chars');


![][3]

### 空值与非空值 

`NULL`，字段值可以为空。

`NOT NULL`，字段值禁止为空。不存在表级约束。

### PRIMARY KEY 

主键约束。

每张数据表只能存在一个主键。

主键保证记录的唯一性。

主键自动为 `NOT NULL`。

#### 注意：

`AUTO_INCREMENT` 必须与 `PRIMARY KEY` 一起使用。但是， `PRIMARY KEY` 不一定与 `AUTO_INCREMENT` 一起使用。 

### UNIQUE KEY 

唯一约束。

唯一约束可以保证记录的唯一性。

唯一约束的字段可以为空值（NULL）。

每张数据表可以存在多个唯一约束。

#### 注意：UNIQUE KEY 与 PRIMARY KEY 区别

`PRIMARY KEY` 每张数据表只能有一个，且不能为空。

`UNIQUE KEY` 每张数据表可以有多个，且可以为空。

### DEFAULT 

默认值。

当插入记录时，如果没有明确为字段赋值，则自动赋予默认值。

不存在表级约束。

### FOREIGN KEY 

保持数据一致性，完整性。

实现一对一或一对多关系。

关系型数据库名称的来源。

#### 外键约束的要求 

1. 父表和子表必须使用相同的存储引擎，而且禁止使用临时表。

2. 数据表的存储引擎只能为 InnoDB。

3. 外键列和参照列必须具有相似的数据类型。其中数字的长度或是否有符号位必须相同；而字符的长度则可以不同。

4. 外键列和参照列必须创建索引。如果外键列不存在索引的话，MySQL 将自动创建索引。

#### 外键约束的参照操作 

1. `CASCADE`：从父表删除或更新且自动删除或更新子表中匹配的行。

2. `SET NULL`：从父表删除或更新行，并设置子表中的外键列为 NULL。如果使用该选项，必须保证子表列没有指定 NOT NULL。

3. `RESTRICT`：拒绝对父表的删除或更新操作。

4. `NO ACTION`：标准 SQL 的关键字，在 MySQL 中与 `RESTRICT` 相同。

注意：物理外键即使用 `FOREIGN KEY` 关键字定义表。逻辑外键即定义表的时候按照某种联系，但是不使用 `FOREIGN KEY` 关键字修饰。 

#### 编辑数据表的默认存储引擎 

MySQL 配置文件

`default-storage-engine=INNODB`示例：

```sql
    create table provinces(id smallint unsigned primary key auto_increment, pname varchar(20) not null);
```

```sql
    create table users(id smallint unsigned primary key auto_increment, username varchar(10) not null, pid bigint, foreign key(pid) references provinces(id));
    
    -- # 报错
    -- # ERROR 1215 (HY000): Cannot add foreign key constraint
    -- # 因为类型不匹配
    
    -- # 正确命令应该是：
    create table users(id smallint unsigned primary key auto_increment, username varchar(10) not null, pid smallint unsigned, foreign key(pid) references provinces(id));
```

![][4]

![][5]

### 表级约束和列级约束 

对一个数据列建立的约束，称为列级约束。

对多个数据列建立的约束，称为表级约束。

列级约束既可以在列定义时声明，也可以在列定义后声明。

表级约束只能在列定义后声明。

## 数据表操作 

数据表（或称表）是数据库最重要的组成部分之一，是其它对象的基础。数据表即二维表，行称为记录，列称为字段。

### USE 

打开数据库

`USE` 数据库名称;

### 创建数据表 

    CREATE TABLE [IF NOT EXISTS] table_name (column_name data_type, ...)


### 查看数据表列表 

    SHOW TABLES [FROM db_name] [LIKE 'pattern' | WHERE expr]


### 查看数据表结构 

    SHOW COLUMNS FROM tbl_name


### 插入表记录 

    INSERT [INTO] tbl_name [(col_name,...)] VALUES(val,...)


如果省略 `col_name` 就需要写全数据表所有的值。

### 记录查找 

    SELECT expr,... FROM tbl_name


### 添加单列（数据表字段） 

    ALTER TABLE tbl_name ADD [COLUMN] col_name column_definition [FIRST|AFTER col_name]


省略 `[FIRST|AFTER col_name]` 参数将位于所有列的最后面。

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

星号（ `*` ）表示多有列。 `tbl_name.*` 可以表示命名表的所有列。 

查询表达式可以使用 `[AS] alias_name` 为其赋予别名。

别名可用于 `GROUP BY`，`ORDER BY` 或 `HAVING` 子句。

## 6 数据类型 

数据类型是指列、存储过程参数、表达式和局部变量的数据特征，它决定了数据的存储格式，代表了不同信息的类型。

</font>

[0]: https://www.mysql.com/
[1]: ./img/uumuUbf.png
[2]: ./img/qM7FJj7.png
[3]: ./img/uEZ3mqF.png
[4]: ./img/v2Uviu6.png
[5]: ./img/RJVBrar.png
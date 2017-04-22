# SQLite3 命令
_原文_[http://www.forwhat.cn/post-444.html](http://www.forwhat.cn/post-444.html)

让我们在命令提示符下键入一个简单的 sqlite3 命令，在 SQLite 命令提示符下，您可以使用各种 SQLite 命令。

    $sqlite3
    SQLite version 3.3.6
    Enter ".help" for instructions
    sqlite>

如需获取可用的点命令的清单，可以在任何时候输入 ".help"。例如：

    sqlite>.help

上面的命令会显示各种重要的 SQLite 点命令的列表，如下所示：

-|-
-|-
.backup ?DB? FILE  | 备份 DB 数据库（默认是 "main"）到 FILE 文件。
.bail ON|OFF  | 发生错误后停止。默认为 OFF。
.databases  | 列出附加数据库的名称和文件。
.dump ?TABLE?  | 以 SQL 文本格式转储数据库。如果指定了 TABLE 表，则只转储匹配 LIKE 模式的 TABLE 表。
.echo ON|OFF  | 开启或关闭 echo 命令。
.exit  | 退出 SQLite 提示符。
.explain ON|OFF  | 开启或关闭适合于 EXPLAIN 的输出模式。如果没有带参数，则为 EXPLAIN on，及开启 EXPLAIN。
.header(s) ON|OFF  | 开启或关闭头部显示。
.help  | 显示消息。
.import FILE TABLE  | 导入来自 FILE 文件的数据到 TABLE 表中。
.indexes ?TABLE?  | 显示所有索引的名称。如果指定了 TABLE 表，则只显示匹配 LIKE 模式的 TABLE 表的索引。
.load FILE ?ENTRY?  | 加载一个扩展库。
.log FILE|off  | 开启或关闭日志。FILE 文件可以是 stderr（标准错误）/stdout（标准输出）。
.mode MODE  | 设置输出模式，MODE 可以是下列之一：* csv 逗号分隔的值* column 左对齐的列* html HTML 的 <table> 代码* insert TABLE 表的 SQL 插入（insert）语句* line 每行一个值* list 由 .separator 字符串分隔的值* tabs 由 Tab 分隔的值* tcl TCL 列表元素
.nullvalue STRING  | 在 NULL 值的地方输出 STRING 字符串。
.output FILENAME  | 发送输出到 FILENAME 文件。
.output stdout  | 发送输出到屏幕。
.print STRING...  | 逐字地输出 STRING 字符串。
.prompt MAIN CONTINUE  | 替换标准提示符。
.quit  | 退出 SQLite 提示符。
.read FILENAME  | 执行 FILENAME 文件中的 SQL。
.schema ?TABLE?  | 显示 CREATE 语句。如果指定了 TABLE 表，则只显示匹配 LIKE 模式的 TABLE 表。
.separator STRING  | 改变输出模式和 .import 所使用的分隔符。
.show  | 显示各种设置的当前值。
.stats ON|OFF  | 开启或关闭统计。
.tables ?PATTERN?  | 列出匹配 LIKE 模式的表的名称。
.timeout MS  | 尝试打开锁定的表 MS 毫秒。
.width NUM NUM  | 为 "column" 模式设置列宽度。
.timer ON|OFF  | 开启或关闭 CPU 定时器测量

使用 .show 命令，来查看 SQLite 命令提示符的默认设置。

    sqlite>.show
         echo: off
      explain: off
      headers: off
         mode: column
    nullvalue: ""
       output: stdout
    separator: "|"
        width:
    sqlite>

确保 sqlite> 提示符与点命令之间没有空格，否则将无法正常工作。

使用下列的点命令来格式化输出为本教程下面所列出的格式：

    sqlite>.header on
    sqlite>.mode column
    sqlite>.timer on
    sqlite>

上面设置将产生如下格式的输出：

    ID          NAME        AGE         ADDRESS     SALARY
    ----------  ----------  ----------  ----------  ----------
    1           Paul        32          California  20000.0
    2           Allen       25          Texas       15000.0
    3           Teddy       23          Norway      20000.0
    4           Mark        25          Rich-Mond   65000.0
    5           David       27          Texas       85000.0
    6           Kim         22          South-Hall  45000.0
    7           James       24          Houston     10000.0
    CPU Time: user 0.000000 sys 0.000000

主表中保存数据库表的关键信息，并把它命名为 sqlite_master。

    sqlite>.schema sqlite_master

这将产生如下结果：

    CREATE TABLE sqlite_master (
      type text,
      name text,
      tbl_name text,
      rootpage integer,
      sql text
    );


----


# SQLite3 数据类型

_原文_[http://www.forwhat.cn/post-446.html](http://www.forwhat.cn/post-446.html)

SQLite 数据类型是一个用来指定任何对象的数据类型的属性。SQLite 中的每一列，每个变量和表达式都有相关的数据类型。

您可以在创建表的同时使用这些数据类型。SQLite 使用一个更普遍的动态类型系统。在 SQLite 中，值的数据类型与值本身是相关的，而不是与它的容器相关。

#### SQLite 存储类

每个存储在 SQLite 数据库中的值都具有以下存储类之一：

存储类| -
-|-
NULL | 值是一个 NULL 值。
INTEGER | 值是一个带符号的整数，根据值的大小存储在 1、2、3、4、6 或 8 字节中。
REAL | 值是一个浮点值，存储为 8 字节的 IEEE 浮点数字。
TEXT | 值是一个文本字符串，使用数据库编码（UTF-8、UTF-16BE 或 UTF-16LE）存储。
BLOB | 值是一个 blob 数据，完全根据它的输入存储。
SQLite 的存储类稍微比数据类型更普遍。INTEGER 存储类，例如，包含 6 种不同的不同长度的整数数据类型。

#### SQLite 亲和(Affinity)类型

SQLite支持列的亲和类型概念。任何列仍然可以存储任何类型的数据，当数据插入时，该字段的数据将会优先采用亲缘类型作为该值的存储方式。SQLite目前的版本支持以下五种亲缘类型：

亲和类型| -
-|-
TEXT |数值型数据在被插入之前，需要先被转换为文本格式，之后再插入到目标字段中。
NUMERIC |当文本数据被插入到亲缘性为NUMERIC的字段中时，如果转换操作不会导致数据信息丢失以及完全可逆，那么SQLite就会将该文本数据转换为INTEGER或REAL类型的数据，如果转换失败，SQLite仍会以TEXT方式存储该数据。对于NULL或BLOB类型的新数据，SQLite将不做任何转换，直接以NULL或BLOB的方式存储该数据。需要额外说明的是，对于浮点格式的常量文本，如"30000.0"，如果该值可以转换为INTEGER同时又不会丢失数值信息，那么SQLite就会将其转换为INTEGER的存储方式。
INTEGER |对于亲缘类型为INTEGER的字段，其规则等同于NUMERIC，唯一差别是在执行CAST表达式时。
REAL |其规则基本等同于NUMERIC，唯一的差别是不会将"30000.0"这样的文本数据转换为INTEGER存储方式。
NONE |不做任何的转换，直接以该数据所属的数据类型进行存储。
SQLite 亲和类型(Affinity)及类型名称

下表列出了当创建 SQLite3 表时可使用的各种数据类型名称，同时也显示了相应的亲和类型：

数据类型 | 亲和类型 
-|-
* INT \t* INTEGER \t* TINYINT \t* SMALLINT \t* MEDIUMINT \t* BIGINT \t* UNSIGNED BIG INT \t* INT2 \t* INT8 | INTEGER
* CHARACTER(20) \t* VARCHAR(255) \t* VARYING CHARACTER(255) \t* NCHAR(55) \t* NATIVE CHARACTER(70) \t* NVARCHAR(100) \t* TEXT \t* CLOB | TEXT
* BLOB \t* no datatype specified | NONE
* REAL \t* DOUBLE \t* DOUBLE PRECISION \t* FLOAT | REAL
* NUMERIC \t* DECIMAL(10,5) \t* BOOLEAN \t* DATE \t* DATETIME | NUMERIC

#### Boolean 数据类型

SQLite 没有单独的 Boolean 存储类。相反，布尔值被存储为整数 0（false）和 1（true）。

#### Date 与 Time 数据类型

SQLite 没有一个单独的用于存储日期和/或时间的存储类，但 SQLite 能够把日期和时间存储为 TEXT、REAL 或 INTEGER 值。

日期格式 |-
-|-
TEXT | 格式为 "YYYY-MM-DD HH:MM:SS.SSS" 的日期。
REAL | 从公元前 4714 年 11 月 24 日格林尼治时间的正午开始算起的天数。
INTEGER | 从 1970-01-01 00:00:00 UTC 算起的秒数。
您可以以任何上述格式来存储日期和时间，并且可以使用内置的日期和时间函数来自由转换不同格式。

---

# SQLite3 语法


_原文_[http://www.forwhat.cn/post-445.html](http://www.forwhat.cn/post-445.html)


大小写敏感性

有个重要的点值得注意，SQLite 是不区分大小写的，但也有一些命令是大小写敏感的，比如 GLOB 和 glob 在 SQLite 的语句中有不同的含义。

注释

SQLite 注释是附加的注释，可以在 SQLite 代码中添加注释以增加其可读性，他们可以出现在任何空白处，包括在表达式内和其他 SQL 语句的中间，但它们不能嵌套。

SQL 注释以两个连续的 "-" 字符（ASCII 0x2d）开始，并扩展至下一个换行符（ASCII 0x0a）或直到输入结束，以先到者为准。

您也可以使用 C 风格的注释，以 "/*" 开始，并扩展至下一个 "*/" 字符对或直到输入结束，以先到者为准。SQLite的注释可以跨越多行。

    sqlite>.help -- This is a single line comment

SQLite 语句

所有的 SQLite 语句可以以任何关键字开始，如 SELECT、INSERT、UPDATE、DELETE、ALTER、DROP 等，所有的语句以分号（;）结束。

#### SQLite ANALYZE 语句：

    ANALYZE;
    or
    ANALYZE database_name;
    or
    ANALYZE database_name.table_name;

#### SQLite AND/OR 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  CONDITION-1 {AND|OR} CONDITION-2;

SQLite ALTER TABLE 语句：

    ALTER TABLE table_name ADD COLUMN column_def...;

SQLite ALTER TABLE 语句（Rename）：

    ALTER TABLE table_name RENAME TO new_table_name;

SQLite ATTACH DATABASE 语句：

    ATTACH DATABASE 'DatabaseName' As 'Alias-Name';

SQLite BEGIN TRANSACTION 语句：

    BEGIN;
    or
    BEGIN EXCLUSIVE TRANSACTION;

SQLite BETWEEN 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name BETWEEN val-1 AND val-2;

SQLite COMMIT 语句：

    COMMIT;

SQLite CREATE INDEX 语句：

    CREATE INDEX index_name
    ON table_name ( column_name COLLATE NOCASE );

SQLite CREATE UNIQUE INDEX 语句：

    CREATE UNIQUE INDEX index_name
    ON table_name ( column1, column2,...columnN);

SQLite CREATE TABLE 语句：

    CREATE TABLE table_name(
      column1 datatype,
      column2 datatype,
      column3 datatype,
      .....
      columnN datatype,
      PRIMARY KEY( one or more columns )
    );

SQLite CREATE TRIGGER 语句：

    CREATE TRIGGER database_name.trigger_name
    BEFORE INSERT ON table_name FOR EACH ROW
    BEGIN
      stmt1;
      stmt2;
      ....
    END;

SQLite CREATE VIEW 语句：

    CREATE VIEW database_name.view_name  AS
    SELECT statement....;

SQLite CREATE VIRTUAL TABLE 语句：

    CREATE VIRTUAL TABLE database_name.table_name USING weblog( access.log );
    or
    CREATE VIRTUAL TABLE database_name.table_name USING fts3( );

SQLite COMMIT TRANSACTION 语句：

    COMMIT;

SQLite COUNT 子句：

    SELECT COUNT(column_name)
    FROM   table_name
    WHERE  CONDITION;

SQLite DELETE 语句：

    DELETE FROM table_name
    WHERE  {CONDITION};

SQLite DETACH DATABASE 语句：

    DETACH DATABASE 'Alias-Name';

#### SQLite DISTINCT 子句：

    SELECT DISTINCT column1, column2....columnN
    FROM   table_name;

#### SQLite DROP INDEX 语句：

    DROP INDEX database_name.index_name;

#### SQLite DROP TABLE 语句：

    DROP TABLE database_name.table_name;

#### SQLite DROP VIEW 语句：

    DROP VIEW view_name;

#### SQLite DROP TRIGGER 语句：

    DROP TRIGGER trigger_name

#### SQLite EXISTS 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name EXISTS (SELECT * FROM   table_name );

#### SQLite EXPLAIN 语句：

    EXPLAIN INSERT statement...;
    or
    EXPLAIN QUERY PLAN SELECT statement...;

#### SQLite GLOB 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name GLOB { PATTERN };

#### SQLite GROUP BY 子句：

    SELECT SUM(column_name)
    FROM   table_name
    WHERE  CONDITION
    GROUP BY column_name;

#### SQLite HAVING 子句：

    SELECT SUM(column_name)
    FROM table_name
    WHERE  CONDITION
    GROUP BY column_name
    HAVING (arithematic function condition);

#### SQLite INSERT INTO 语句：

    INSERT INTO table_name( column1, column2....columnN)
    VALUES ( value1, value2....valueN);

#### SQLite IN 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name IN (val-1, val-2,...val-N);

#### SQLite Like 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name LIKE { PATTERN };

#### SQLite NOT IN 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  column_name NOT IN (val-1, val-2,...val-N);

#### SQLite ORDER BY 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  CONDITION
    ORDER BY column_name {ASC|DESC};

#### SQLite PRAGMA 语句：

    PRAGMA pragma_name;
    
    For example:
    
    PRAGMA page_size;
    PRAGMA cache_size = 1024;
    PRAGMA table_info(table_name);

SQLite RELEASE SAVEPOINT 语句：

    RELEASE savepoint_name;

SQLite REINDEX 语句：

    REINDEX collation_name;
    REINDEX database_name.index_name;
    REINDEX database_name.table_name;

#### SQLite ROLLBACK 语句：

    ROLLBACK;
    or
    ROLLBACK TO SAVEPOINT savepoint_name;

SQLite SAVEPOINT 语句：

    SAVEPOINT savepoint_name;

SQLite SELECT 语句：

    SELECT column1, column2....columnN
    FROM   table_name;

SQLite UPDATE 语句：

    UPDATE table_name
    SET column1 = value1, column2 = value2....columnN=valueN
    [ WHERE  CONDITION ];

SQLite VACUUM 语句：

    VACUUM;

SQLite WHERE 子句：

    SELECT column1, column2....columnN
    FROM   table_name
    WHERE  CONDITION;


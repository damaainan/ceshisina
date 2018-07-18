##索引

索引用于**快速**找出某个列中一特定值的行，如不使用索引，MySQL必须从第1条记录开始然后读完整个表知道找出相关的行。表越大，费时越多，如表中查询的列有一个索引，便可快速到达一个位置去搜寻数据文件。注意如果需要访问大部分行，则顺序读取更快，此时应该避免磁盘搜索。

MySQL列都可以被索引，对相关列使用索引很好的提高SELECT的性能。根据存储引擎可以定义每个表的最大索引数和最大索引长度，每种存储引擎对每个表至少支持16个索引，总长度至少256字节，大多存储引擎有更高限制。

MyISAM和InnoDB存储引擎的表默认创建的都是BTREE索引，MySQL支持前缀索引（对索引字段的前N个字符创建索引），其长度和存储引擎相关。

> 前缀的限制以字节为单位，而CREATE TABLE语句中的前缀长度解释为字符，因此使用多字节字符集的列指定前缀长度时一定要注意。

MySQL的MyISAM引擎支持全文索引，可用于全文搜索。但只限于CHAR、VARCHAR和TEXT列，不支持局部索引。默认情况下MEMORY存储引擎用HASH索引，但也支持BTREE索引。

索引在创建表的同时创建，也可以随时增加新的索引，创建索引：

```
CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name
[USING index_type]
On tb1_name(index_col_name,...)

index_col_name:
	col_name[(length)][ASC|DESC]
```

也可以使用`ALTER TABLE`语法增加索引，语法与`CREATE INDEX`类似。

删除索引：`DROP INDEX index_name ON tb1_name`

索引设计原则：  

- 选择最合适的索引列，通常为出现在WHERE字句中的列。
- 使用唯一索引，使用容易区分的各行而不是只有"M"和"F"之类。
- 使用短索引，尽量指定前缀长度。
- 利用最左前缀，多个索引从最左边的索引开始匹配行。
- 不要过度索引

####BTREE索引与HASH索引
HASH索引特性：  

- 只用于使用=或<=>操作符的等式比较
- 优化器不能使用HASH索引加速ORDER BY操作
- 只能使用整个关键字来索引一行
- 使用范围查性能不好

对于BTREE使用索引则不受操作符的影响。

##视图

视图是虚拟表，对使用视图的用户基本上透明，视图是在使用时候动态生成的，相对于表的优势：  

- 简单 已经是筛选好的表的结果
- 安全 只能访问被允许的结果集
- 数据独立 一旦视图的结构确定，可以屏蔽表结构改变的影响

创建视图需要`CREATE VIEW`的权限，并对查询涉及的列有`SELECT`权限，如使用`CREATE OR REPLACE`或者`DROP`修改视图，那么还需要该视图的`DROP`权限

```
CREATE [OR REPLACE] [ALGORITHM={UNDEFINED | NERGE TEMPTABLE}] VIEW view_name [(column_list)] AS select_statement [WITH | CASCADED | LOCAL | CHECK OPTION]

EG:
create or replace view stall_list_view as
select s.staff_id,s.first_name,s.last_name,a.address
from staff as s,address as a
where s.address_id=a.address_id
```

以下类型的视图不可更新：  

- 包含聚合函数(SUM、MIN、MAX、COUNT)、DISTINCT、GROUP BY、HAVING、UNION或者UNION ALL
- 常量视图
- SELECT中包含子查询
- JION
- FROM一个不能更新的视图
- WHERE字句的子查询引用了FROM字句中的表

WITH [CACSCADED |LOCAL] CHECK OPTION决定了是否允许更新数据使记录不再满足视图的条件，其中：  

- LOCAL只需满足本视图的条件就可以更新
- CASCADED则必须满足所有针对该视图的所有视图的条件才可以更新

可以一次删除一个或者多个视图

```
DROP VIEW [IF EXISTS] view_name [,view_name]...[RESTRICT|CASCADE]
```

查看视图通过以下方式都可以：

```
SHOW TABLES

SHOW TABLE STATUS

SHOW CREATE STATUS [FROM db_name] [LIKE 'pattern']

查看系统表information_schema.views
select * from views where table_name='viewname'\G
```

##存储过程和函数
存储过程是事先进过编译并存储在一个数据库中的一段SQL语句的集合，存储过程和函数的区别是函数必须有返回值，存储过程没有，存储过程的参数可用IN、OUT、INOUT类型；而函数必须使用IN类型。

操作存储过程前需要确认用户是否具有相应的权限。创建需要CREATE ROUTINE权限、修改或删除需要使用ALTER ROUTINE权限，执行需要EXECUTE权限。

创建、修改存储过程或函数语法：

```
CREATE PROCEDURE sp_name([proc_parameter[,...]])
	[characteristic...] routine_body

CREATE FUNCTION sp_name([func_parameter[,...]])
	RETURNS type
	[characteristic ...] routine_body
	
	proc_parameter:
	[IN |OUT |INOUT] param_name type
	
	func_parameter:
	param_name type
	
type:
	Any valid MySQL data type
characteristic:
	LANGUAGE SQL
	|[NOT] DELERMINISTIC
	|{CONTAINS SQL |NO SQL|READS SQL DATA|MODIFIES SQL DATA}
	|SQL SECURITY {DEFINER|INVOKER}
	|COMMENT 'string'
	
routine_body:
	Valid SQL procudure statement or statements

修改	
ALTER {PROCEDURE|FUNCTION} sp_name [characteristic ...]

haracteristic:
	{CONTAINS SQL |NO SQL|READS SQL DATA|MODIFIES SQL DATA}
	|SQL SECURITY {DEFINER|INVOKER}
	|COMMENT 'string'
	
调用
CALL sp_name([parameter[,...]])

MySQL存储过程和函数中可以包含DDL，可以执行Commit或者Rollback，但不允许LOAD DATA INFILE

eg:
DELIMITER $$

CREATE PROCEDURE film_in_sock(IN p_film_id INT,IN p_store_id INT,OUT p_file_count INT)
READ SQL DATA
BEGIN
	SELECT inventory_id
	FROM inventory
	WHERE film_id=p_film_id
	AND store_id=p_store_id
	AND inventory_in_stock(inventory_id);
	
	SELECT FOUND_ROWS() INTO p_film_count;
END $$

DELIMITER ;
```

对characteristic特征值部分说明：


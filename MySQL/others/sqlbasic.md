#SQL基础
安装：推荐下载官方rpm包安装   
配置：执行`mysql --help`可查看相关相关配置信息，服务所使用配置文件的位置可以通过`mysql --help|grep my.cnf`查看。   
服务：启动服务`mysqld_safe &`，停止服务`mysqladmin -uroot shutdown -p`
> client连接后?可以查看client端信息，其中\e结合\g(\G)很好用

##修改表(DDL)
- 修改表类型
	
```
ALTER TABLE tablename MODIFY [COLUMN] column_definition [FIRST | AFTER col_name]
```
- 增加表字段
	
```
ALTER TABLE tablename ADD [COLUMN] column_definition [FIRST | AFTER col_name]
```
- 删除表字段
	
```
ALTER TABLE tablename DROP [COLUMN] col_name
```
- 修改字段名
	
```
ALTER TABLE tablename CHANGE [COLUMN] old_col_name column_definition [FIRST | AFTER col_name]
```
> change和modify都可以修改表的定义，但change需写两次列名，优点是能改列名，modify不能。

- 修改字段排列顺序，前面介绍的字段增加和修改语法都有一个可选项 `first|after column_name`，该选项用来修改字段在表中的位置。

> CHANGE/FIRST|AFTER这些关键字都是属于mysql在标准sql上的扩展。

- 更改表名
	
```
ALTER TABLE tablename RENAME [TO] new_tablename
```
##DML语句
- 插入记录(一次插入多条--批量插入，用逗号分隔)
	
```
INSERT INTO tablename(field1,field2,...,fieldn) VALUES(value1,value2,...,valuen) [,(value1,value2,...,valuen),...,(value1,value2,...,valuen)];
```
> 在插入大量记录时，批量插入的特性节省了很多的网络开销，提高了插入效率。

- 更新记录 & 多表更新
	
```
UPDATE tablename SET field1=value1,field2=value2,...,fieldn=valuen [WHERE CONDITION]
或者
UPDATE t1,t2,...,tn set t1.filed1=expr1,...,tn.filedn=exprn [WHERE CONDITION]
```
- 删除表记录 & 多表删除
	
```
DELETE FROM tablename [WHERE CONDITION]
多表删除
DELETE t1,t2,...,tn FROM t1,t2,...,tn [WHERE CONDITIO
```
> 执行删除操作之前强烈建议先用select查确定好where条件，最后添加delete。不管是单表还是多表，没有where条件将会删除所有记录。

### 查询记录
- 聚合
```

SELECT [field1,field2,...,fieldn] fun_name
	FROM tablename 
	[WHERE where_contition] 
	[GROUP BY field1,field2,...,fieldn] 
	[WITH ROLLUP]
	[HANVING where_contition]
```
        
  - fun_name表示聚合操作：sum,count,max,min
  - GROUP BY 表示要进行分类聚合的字段
  - WITH ROLLUP 表示是否对分类聚合后的结果汇总
  - HAVING 对分类结果进行条件过滤
  > having对聚合结果进行条件过滤，where在聚合前对记录过滤，如逻辑允许，尽可能用where先过滤记录，结果集减少对聚合效率会有提高。
	
- 连接查询
	
```
SELECT [filed1,...,filedn] FROM table1name [LEFT | RIGHT] JOIN table2name ON CONDITION
```

- 子查询
 	
 	查询时需要的条件是另一个select语句的结果，用子查询。子查询关键字主要包括in、not in、=、!=、exists、not exists等。
> 在mysql4.1以前不支持子查询，需要用连接查询来实现子查询。表连接在多数情况下优于子查询。

- 联合查询
```
	SELECT * FROM t1
	UNION | UNION ALL
	SELECT * FROM t2
	...
	UNION | UNION ALL
	SELECT * FROM tn
```
	
> UNION和UNION ALL的主要区别是UNION ALL将结果集直接合并在一起，而UNION会进行一次DISTINCT。

## DCL语句

- 创建用户并且分配权限
```
	GRANT func[INSERT|SELECT] ON dbname.table[*] to 'USERNAME'@'ADDR' IDENTIFIED BY 'PASSWD'
```
- 回收权限
```
REVOKE table ON tablename.* from 'USER'@'PASSWD'
```

> 注意使用mysql的帮助文档，即为`? content`。
	
- 查询元数据信息
	
	mysql5.0以后提供了一个information_schema，用来记录mysql中的元数据信息，该数据库为一个虚拟数据库，物理上不存在目录和文件，库中`show tables`显示的‘表’实际上都是**视图**
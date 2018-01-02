## [MySQL - WHERE优化篇](https://segmentfault.com/a/1190000012647557)


> 日常开发中，编写SQL语句都避免不了使用到> WHERE> 关键字做条件过滤，细心的朋友就会发现，WHERE的不同表现形式会对数据库性能造成一定影响，本章主要针对> WHERE> 优化策略进行讨论....<!-- more -->

## 优化要素

* 想要让SELECT .... WHERE ...变快，第一就是检查一下是否可以增加索引。在WHERE子句中创建索引，可以加快求值、过滤、和最终检索结果的速度。为避免浪费磁盘空间，可以通过创建联合索引来加速多个相关查询。
* 尽量减少全表扫描的查询，尤其对于大表更要杜绝全表扫描。
* 减少函数使用（尤其是耗时的函数）。一个函数可能在结果集中每行都被调用一次或者在一个表里面每一行都被调用一次，这样做效率是非常低的。
* 掌握不同存储引擎的优化方案，合理的运用索引技术。
* 优化InnoDB事务。（对于统计型的数据，开启只读事务）
* 避免将查询转换成比较难以理解的方式，以免MySQL无法进行优化
* 熟练掌握EXPLAIN计划
* 调整MySQL用于缓存数据的内存大小
* 减少锁表的情况

## 内置优化

在做JAVA开发中，通过指令重拍会对代码做一定程度的优化，在数据库中MYSQL优化器也做了一系列相关优化工作，下面要介绍的就是数据库做的内置优化

**方案一：** 删除不必要的括号

       ((a AND b) AND c OR (((a AND b) AND (c AND d))))
    -> (a AND b AND c) OR (a AND b AND c AND d)

**方案二：** 常量折叠/常量叠算

       (a<b AND b=c) AND a=5
    -> b>5 AND b=c AND a=5

**更多：** 其他方案* 索引使用常量表达式时只计算一次，所以尽可能使用产生const的查询方式（主键查询）
* 对于MyISAM和MEMORY表来说，在一个单独的表上，如果使用COUNT(*)但是没有WHERE子句的话，那么就会直接从表的信息里面检索数据。当在一个表中用NOT NULL表达式的时候也是这么做的。
* 发现无效的常量表达式。MySQL会及时发现无效SELECT语句，然后不返回数据。
* WHERE查询中发现未使用GROUP BY或者聚合函数(比如COUNT(),MIN()等)，那么HAVING会与WHERE合并。
* 多表查询中，MYSQL会对表进行评估从而构造出更简单的查询
* 优先读取常量表
    * 空表或者一个有一行的表。
    * WHERE子句在PRIMARY KEY或者UNIQUE INDEX上的表，其中索引和常量表达式作比较，并被定义为NOT NULL。

    SELECT * FROM t WHERE primary_key = 1;
    SELECT * FROM t1,t2
      WHERE t1.primary_key= 1  AND t2.primary_key = t1.id;
* 关联查询时，MySQL会去尝试所有的可能性，从而发现最好的的组合方式。当ORDER BY和GROUP BY子句的列都位于同一个表时，该表将会第一个被链接。
* 如果ORDER BY 和 GROUP BY 字段不同，或是除**join queue**中的第一个表之外其它含有ORDER BY 或 GROUP BY的表都会为其创建临时表
* 如果使用了 SQL_SMALL_RESULT 选项，那么MySQL就会在内存中创建一个临时表。
* MySQL每次查询时都会检查是否有可用索引，除非MySQL优化器认为全表扫描性能更快。早期版本中认为索引扫描行占30%的时候就会换成全表扫描，但进过改进后，现在将根据表的大小、行的数目、I/O块大小等综合评估
* 在某些情况下，MySQL会直接跳过数据文件直接从索引中读取内容（**比如： 索引列都是数字，那么这时候会直接解析索引树**）
* 跳过不匹配HAVING条件的内容

## 示例

查询快慢除软硬件优化外，索引是必不可少，下面列举一些使用索引提供查询速度的示例。

> 高效查询

    SELECT COUNT(*) FROM tbl_name;
    
    SELECT MIN(key_part1),MAX(key_part1) FROM tbl_name;
    
    SELECT MAX(key_part2) FROM tbl_name
      WHERE key_part1 = constant;
    
    SELECT ... FROM tbl_name
      ORDER BY key_part1,key_part2,... LIMIT 10;
    
    SELECT ... FROM tbl_name
      ORDER BY key_part1 DESC, key_part2 DESC, ... LIMIT 10;

> 索引树查询（索引列是数字的情况下）

    SELECT key_part1,key_part2 FROM tbl_name WHERE key_part1 = val;
    
    SELECT COUNT(*) FROM tbl_name
      WHERE key_part1 = val1 AND key_part2 = val2;
    
    SELECT key_part2 FROM tbl_name GROUP BY key_part1;

> 索引排序（无需单独排序传递）

    SELECT ... FROM tbl_name
      ORDER BY key_part1,key_part2,... ;
    
    SELECT ... FROM tbl_name
      ORDER BY key_part1 DESC, key_part2 DESC, ... ;

## 总结

最好的优化方案，跟着新版本走**推陈出新**，新版中不仅扩展更多功能，同时会加强优化力度。虽然MySQL优化器为我们做了很多事情，但开发过程中改主意还得注意。


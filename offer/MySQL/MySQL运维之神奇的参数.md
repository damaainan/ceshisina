# MySQL运维之神奇的参数

 时间 2016-12-20 17:06:20  

原文[http://keithlan.github.io/2016/12/20/sql_safe_updates/][1]


sql_safe_updates

[http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_sql_safe_updates][5]

## 背景(why)

主要是针对大表的误操作。

如果只是更改了几条记录，那么说不定业务方可以很容易的根据日志进行恢复。即便没有，也可以通过找binlog，进行逆向操作恢复。

如果被误操作的表非常小，其实问题也不大，全备+binlog恢复 or 闪回 都可以进行很好的恢复。

But，如果你要恢复的表非常大，比如：100G，100T，对于这类型的误操作，恐怕神仙都难救。

所以，我们这里通过这个神奇的参数，可以避免掉80%的误操作场景。 PS: 不能避免100% ，下面的实战会告诉大家如何破解。

## 生产环境的误操作案例分享

    updatexxseturl_desc='防不胜防'WHERE4918=4918ANDSLEEP(5)-- xYpp' where id=7046
    
    这种表，线上有500G，一次误操作，要恢复500G的数据，会中断服务很长时间。
    
    如果设置了sql_safe_updates，此类事故就可以很华丽的避免掉了。
    

## 原理和实战

* 表结构
```
    dba:lc> show create table tb;
    +-------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Table | Create Table |
    +-------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | tb    | CREATE TABLE `tb` (
     `id` int(11) NOT NULL,
     `id_2` int(11) DEFAULT NULL COMMENT 'lc22222233333',
     `id_3` text,
     PRIMARY KEY (`id`),
     KEY `idx_2` (`id_2`),
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
    +-------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
```

* update 相关测试

UPDATE statements must have a WHERE clause that uses a key or a LIMIT clause, or both.

    * 不带where 条件
    
    dba:lc> update tb set id_2=2 ;
    ERROR 1175 (HY000): You are using safe update mode and you tried to update a table without a WHERE that uses a KEY column
    
    
    * where 条件有索引，但是没有limit 
    
    dba:lc> update tb set id_3 = 'bb' where id > 0;
    ^C^C -- query aborted
    ERROR 1317 (70100): Query execution was interrupted
    
    * where 条件无索引，也没有limit
    
    dba:lc> update tb set id_3 = 'bb' where id_3 = '0';
    ERROR 1175 (HY000): You are using safe update mode and you tried to update a table without a WHERE that uses a KEY column
    
    * where 条件有索引，有limit
    
    dba:lc> update tb set id_3 = 'bb' where id > 0 limit 1;
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    
    * where 条件无索引，有limit
    
    dba:lc> update tb set id_3 = 'bb' where id_3 > 0 limit 1;
    Query OK, 0 rows affected (0.26 sec)
    Rows matched: 0  Changed: 0  Warnings: 0
    

结论： 对于update，只有两种场景会被限制

1. 无索引，无limit的情况
1. 无where条件

* delete相关测试

DELETE statements must have both

    * 不带where 条件
    
    dba:lc> delete from tb ;
    ERROR 1175 (HY000): You are using safe update mode and you tried to update a table without a WHERE that uses a KEY column
    
    * where 条件有索引，但是没有limit 
    
    dba:lc> delete from tb where id = 0 ;
    Query OK, 0 rows affected (0.00 sec)
    
    dba:lc> delete from tb where id > 0 ;
    ^C^C -- query aborted
    ERROR 1317 (70100): Query execution was interrupted
    
    dba:lc> explain select * from tb where id_2 > 0;
    +----+-------------+-------+------------+------+---------------+------+---------+------+--------+----------+-------------+
    | id | select_type | table | partitions | type | possible_keys | key  | key_len | ref  | rows   | filtered | Extra       |
    +----+-------------+-------+------------+------+---------------+------+---------+------+--------+----------+-------------+
    |  1 | SIMPLE      | tb    | NULL       | ALL  | idx_2,idx_3   | NULL | NULL    | NULL | 245204 |    50.00 | Using where |
    +----+-------------+-------+------------+------+---------------+------+---------+------+--------+----------+-------------+
    1 row in set, 1 warning (0.00 sec)
    
    dba:lc> delete from tb where id_2 > 0 ;
    ^C^C -- query aborted
    ^C^C -- query aborted
    ERROR 1317 (70100): Query execution was interrupted
    
    
    * where 条件无索引，也没有limit
    
    dba:lc> delete from tb where id_3 = 'a' ;
    ERROR 1175 (HY000): You are using safe update mode and you tried to update a table without a WHERE that uses a KEY column
    
    * where 条件有索引，有limit
    
    dba:lc> delete from tb where id = 205 limit 1 ;
    Query OK, 1 row affected (0.00 sec)
    
    * where 条件无索引，有limit
    
    dba:lc> delete from tb where id_3 = 'aaaaa' limit 1 ;
    Query OK, 1 row affected (0.00 sec)
    

测试结果证明： 关于delete相关，官方文档描述有误。

结论： 对于delete，只有两种场景会被限制

1. 无索引，无limit的情况
1. 无where条件

综上所述：不管是update，还是delete ，被限制的前提只有两个

    1. 无索引，无limit的情况  
    2. 无where条件
    

好了，通过以上的知识，大家都应该很了解，接下来就是实施的问题了。

对于新业务，新DB，直接设置这样的参数就好了，再测试环境也设置，这样开发在测试环境就能发现问题，不会在新业务上产生这样危险的语句。

对于老业务，怎么办呢？

我们的做法：因为我们的MySQL是5.6，所以另外一个神奇的功能就是P_S（performance schema）, 通过P_S，我们可以获取哪些query语句是没有使用索引的。

这里又会引发另外一个问题，可能是Performance schema的bug，它竟然无法统计dml 是否使用索引

经过我们大量的测试后证明：events_statements_summary_by_digest 表里面的SUM_NO_INDEX_USED，SUM_NO_GOOD_INDEX_USED ，对dml无效。

既然如此，我们所幸对dml语句自己进行分析，将dml转换成对应的select语句。

比如： update tb set id = 1 where id = 2; 转换成 select * from tb where id = 2 。。。。

然后根据select语句，进行explain分析，如果没有使用索引，这样的语句就是我们认为的全表dml语句了。

## 总结

如果线上设置sql_safe_updates = 1 后，业务还有零星的dml被拒绝，业务方可以考虑如下解决方案：

1）如果你确保你的SQL语句没有任何问题，可以这样： set sql_safe_updates=0; 但是开发必须考虑到这样做的后果。

2) 可以改写SQL语句，让其使用上索引字段。

3）为什么这边没有让大家使用limit呢？因为在大多数场景下，dml + limit = 不确定的SQL 。 很可能导致主从不一致。 ( dml + limit 的方式，是线上禁止的)

各位看官，以上神器请大家慢慢享用。 关于PS和sys，如果大家有更加新奇的想法，可以一起讨论研究。


[1]: http://keithlan.github.io/2016/12/20/sql_safe_updates/

[5]: http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_sql_safe_updates
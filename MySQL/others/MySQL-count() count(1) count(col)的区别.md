## MySQL:count(*),count(1),count(col)的区别

来源：[http://yq.aliyun.com/articles/519068](http://yq.aliyun.com/articles/519068)

时间 2018-03-11 09:27:24

最近感觉大家都在讨论count的区别，那么我也写下吧：欢迎留言讨论

1、表结构：

```sql
dba_jingjing@3306>[rds_test]>CREATE TABLE `test_count` (
    ->   `c1` varchar(10) DEFAULT NULL,
    ->   `c2` varchar(10) DEFAULT NULL,
    ->   KEY `idx_c1` (`c1`)
    -> ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
Query OK, 0 rows affected (0.11 sec)
```

2、插入测试数据：

```sql
dba_jingjing@3306>[rds_test]>insert into test_count values(1,10);
Query OK, 1 row affected (0.03 sec)

dba_jingjing@3306>[rds_test]>insert into test_count values(abc,null);
ERROR 1054 (42S22): Unknown column 'abc' in 'field list'
dba_jingjing@3306>[rds_test]>insert into test_count values('abc',null);
Query OK, 1 row affected (0.04 sec)

dba_jingjing@3306>[rds_test]>insert into test_count values(null,null);
Query OK, 1 row affected (0.04 sec)

dba_jingjing@3306>[rds_test]>insert into test_count values('368rhf8fj',null);
Query OK, 1 row affected (0.03 sec)

dba_jingjing@3306>[rds_test]>select * from test_count;
+-----------+------+
| c1        | c2   |
+-----------+------+
| 1         | 10   |
| abc       | NULL |
| NULL      | NULL |
| 368rhf8fj | NULL |
+-----------+------+
4 rows in set (0.00 sec)
```

测试：

```sql
dba_jingjing@3306>[rds_test]>select count(*) from test_count;
+----------+
| count(*) |
+----------+
|        4 |
+----------+
1 row in set (0.00 sec)
            EXPLAIN: {
        "query_block": {
            "select_id": 1,
            "message": "Select tables optimized away"
        1 row in set, 1 warning (0.00 sec)
```

```sql
dba_jingjing@3306>[rds_test]>select count(1) from test_count;
+----------+
| count(1) |
+----------+
|        4 |
+----------+
1 row in set (0.00 sec)
            EXPLAIN: {
        "query_block": {
            "select_id": 1,
            "message": "Select tables optimized away"
        1 row in set, 1 warning (0.00 sec)
```

```sql
dba_jingjing@3306>[rds_test]>select count(c1) from test_count;
+-----------+
| count(c1) |
+-----------+
|         3 |
+-----------+
1 row in set (0.00 sec)
            "table": {
                "table_name": "test1",
                "access_type": "index",
                "key": "idx_c1",
                "used_key_parts": [
                    "c1"
                ],
                "key_length": "33",
```

那么这里面的"key_length": "33",为什么是33呢，什么是二级索引？见下节


**`count(*) 和count(1)`** 是没有区别的，而**`count(col)`** 是有区别的

执行计划有特点：可以看出它没有查询索引和表，有时候会出现select tables optimized away 不会查表，速度会很快

Extra有时候会显示“Select tables optimized away”，意思是没有更好的可优化的了。

```
官方解释For explains on simple count queries (i.e. explain select count(*) from people) the extra 
       section will read "Select tables optimized away." 
    This is due to the fact that MySQL can read the result directly from the table internals and therefore does not need to perform the select.
---MySQL对于“Select tables optimized away”的含义, 不是"没有更好的可优化的了", 官方解释中关键的地方在于:
 MySQL can read the result directly
所以,合理的解释是: 
    1 数据已经在内存中可以直接读取; 
    2 数据可以被认为是一个经计算后的结果,如函数或表达式的值; 
    3 一旦查询的结果被优化器"预判"可以不经执行就可以得到结果,所以才有"not need to perform the select".
```



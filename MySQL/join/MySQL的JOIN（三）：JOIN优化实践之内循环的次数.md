# [MySQL的JOIN（三）：JOIN优化实践之内循环的次数][0]

<font face=微软雅黑>

这篇博文讲述如何**优化内循环的次数**。内循环的次数受驱动表的记录数所影响，驱动表记录数越多，内循环就越多，连接效率就越低下，所以尽量用小表驱动大表。先插入测试数据。

 
```sql

        CREATE TABLE t1 (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type INT
        );
        SELECT COUNT(*) FROM t1;
        +----------+
        | COUNT(*) |
        +----------+
        |    10000 |
        +----------+
        CREATE TABLE t2 (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type INT
        );
        SELECT COUNT(*) FROM t2;
        +----------+
        | COUNT(*) |
        +----------+
        |      100 |
        +----------+
```

## 内连接谁当驱动表

实际业务场景中，左连接、右连接可以根据业务需求认定谁是驱动表，谁是被驱动表。但是内连接不同，根据嵌套循环算法的思想，t1内连接t2和t2内连接t1所得结果集是相同的。那么到底是谁连接谁呢？谨记一句话即可，**小表驱动大表可以减小内循环的次数**。下面用 STRAIGHT_JOIN强制左表连接右表。By the way，STRIGHT_JOIN比较冷门，在这里解释下，其作用相当于内连接，不过强制规定了左表驱动右边。详情看这[MySQL的JOIN（一）：用法][1]

 
```sql

        EXPLAIN SELECT * FROM t1 STRAIGHT_JOIN t2 ON t1.type=t2.type;
        +----+-------+------+------+-------+----------------------------------------------------+
        | id | table | type | key  | rows  | Extra                                              |
        +----+-------+------+------+-------+----------------------------------------------------+
        |  1 | t1    | ALL  | NULL | 10000 | NULL                                               |
        |  1 | t2    | ALL  | NULL |   100 | Using where; Using join buffer (Block Nested Loop) |
        +----+-------+------+------+-------+----------------------------------------------------+
        EXPLAIN SELECT * FROM t2 STRAIGHT_JOIN t1 ON t2.type=t1.type;
        +----+-------+------+------+-------+----------------------------------------------------+
        | id | table | type | key  | rows  | Extra                                              |
        +----+-------+------+------+-------+----------------------------------------------------+
        |  1 | t2    | ALL  | NULL |   100 | NULL                                               |
        |  1 | t1    | ALL  | NULL | 10000 | Using where; Using join buffer (Block Nested Loop) |
        +----+-------+------+------+-------+----------------------------------------------------+
```

对于第一条查询语句，t1是驱动表，其有10000条记录，内循环也就有10000次，这还得了？   
对于第二条查询语句，t2是驱动表，其有100条记录，内循环100次，感觉不错，我喜欢!   
这些SQL语句的执行时间也说明了，当内连接时，务必用小表驱动大表。

## 最佳实践：直接让MySQL去判断

但是，表的记录数是会变化的，有没有一劳永逸的写法？当然有啦，MySQL自带的Optimizer会优化内连接，优化策略就是上面讲的小表驱动大表。所以，以后写内连接不要纠结谁内连接谁了，直接让MySQL去判断吧。

 
```sql

        EXPLAIN SELECT * FROM t1 INNER JOIN t2 ON t1.type=t2.type;
        EXPLAIN SELECT * FROM t2 INNER JOIN t1 ON t1.type=t2.type;
        EXPLAIN SELECT * FROM t1 JOIN t2 ON t1.type=t2.type;
        EXPLAIN SELECT * FROM t2 JOIN t1 ON t1.type=t2.type;
        EXPLAIN SELECT * FROM t1,t2 WHERE t1.type=t2.type;
        EXPLAIN SELECT * FROM t2,t1 WHERE t1.type=t2.type;
        +----+-------+------+------+--------+----------------------------------------------------+
        | id | table | type | key  | rows   | Extra                                              |
        +----+-------+------+------+--------+----------------------------------------------------+
        |  1 | t2    | ALL  |  NULL|    100 | NULL                                               |
        |  1 | t1    | ALL  | NULL | 110428 | Using where; Using join buffer (Block Nested Loop) |
        +----+-------+------+------+--------+----------------------------------------------------+
```

上面6条内连接SQL，MySQL的Optimizer都会进行优化。

</font>

[0]: http://www.cnblogs.com/fudashi/p/7508272.html
[1]: http://www.cnblogs.com/fudashi/p/7491039.html
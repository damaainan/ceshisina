## 4. MySQL explain 详解

1. explain 语法
1. explain 输出


  1. id
  1. select_type
    1. SIMPLE
    1. PRIMARY
    1. UNION
    1. DEPENDENT UNION
    1. UNION RESULT
    1. SUBQUERY
    1. DEPENDENT SUBQUERY
    1. DERIVED
    1. MATERIALIZED
    1. UNCACHEABLE SUBQUERY
    1. UNCACHEABLE UNION
  1. table
  1. partitions
  1. type
    1. system
    1. const
    1. eq_ref
    1. ref
    1. fulltext
    1. ref_or_null
    1. index_merge
    1. unique_subquery
    1. index_subquery
    1. range
    1. index
    1. ALL
  1. possible_keys
  1. key
  1. key_len
  1. ref
  1. rows
  1. filtered
  1. extra
    1. Child of 'table' pushed join@1
    1. const row not found
    1. Deleting all rows
    1. Distinct
    1. FirstMatch
    1. Full scan on NULL key
    1. Impossible HAVING
    1. Impossible WHERE
    1. Impossible WHERE noticed after reading const tables
    1. LooseScan(m..n)
    1. No matching min/max row
    1. no matching row in const table
    1. No matching rows after partition pruning
    1. No tables used
    1. Not exists
    1. Plan isn’t ready yet
    1. Range checked for each record (index map: N)
    1. Scanned N databases
    1. Select tables optimized away
    1. Skip_open_table, Open_frm_only, Open_trigger_only, Open_full_table
      1. Skip_open_table
      1. Open_frm_only
      1. Open_trigger_only
      1. Open_full_table
    1. Start temporary, End temporary
    1. unique row not found
    1. Using filesort
    1. Using index
    1. Using index condition
    1. Using index for group-by
    1. Using join buffer (Block Nested Loop), Using join buffer (Batched Key Access)
    1. Using MRR
    1. Using sort_union(…​), Using union(…​), Using intersect(…​)
    1. Using temporary
    1. Using where
    1. Using where with pushed condition
    1. Zero limit

### 4.1. EXPLAIN 语法

DESCRIBE 和 EXPLAIN 是同义词。在实践中，DESCRIBE 多用于显示表结构，而 EXPLAIN 多用于显示 SQL 语句的执行计划。

    {EXPLAIN | DESCRIBE | DESC}
        tbl_name [col_name | wild]
    
    {EXPLAIN | DESCRIBE | DESC}
        [explain_type]
        {explainable_stmt | FOR CONNECTION connection_id}
    
    explain_type: {
        EXTENDED
      | PARTITIONS
      | FORMAT = format_name
    }
    
    format_name: {
        TRADITIONAL
      | JSON
    }
    
    explainable_stmt: {
        SELECT statement
      | DELETE statement
      | INSERT statement
      | REPLACE statement
      | UPDATE statement
    }

    EXPLAIN FORMAT = JSON SELECT DISTINCT (m.user_id)
    FROM user_extdata m
    WHERE m.city_id IN (1, 2, 3, 4) ;

#### 4.1.1. 获取表结构

DESCRIBE 是 SHOW COLUMNS 的简写形式。

### 4.2. explain 输出

#### 4.2.1. select_type- SIMPLE
简单SELECT,不使用UNION或子查询等

- PRIMARY
查询中若包含任何复杂的子部分,最外层的select被标记为PRIMARY

- UNION
UNION中的第二个或后面的SELECT语句

- DEPENDENT UNION
UNION中的第二个或后面的SELECT语句，取决于外面的查询

- UNION RESULT
UNION的结果

- SUBQUERY
子查询中的第一个SELECT

- DEPENDENT SUBQUERY
子查询中的第一个SELECT，取决于外面的查询

- DERIVED
派生表的SELECT, FROM子句的子查询

- MATERIALIZED
- UNCACHEABLE SUBQUERY
一个子查询的结果不能被缓存，必须重新评估外链接的第一行

- UNCACHEABLE UNION
==== table#### 4.2.2. partitions

#### 4.2.3. type- system
当MySQL对查询某部分进行优化，并转换为一个常量时，使用这些类型访问。如将主键置于where列表中，MySQL就能将该查询转换为一个常量,system是const类型的特例，当查询的表只有一行的情况下，使用system

- const
- eq_ref
类似ref，区别就在使用的索引是唯一索引，对于每个索引键值，表中只有一条记录匹配，简单来说，就是多表连接中使用primary key或者 unique key作为关联条件

- ref
表示上述表的连接匹配条件，即哪些列或常量被用于查找索引列上的值

- fulltext
- ref_or_null
MySQL在优化过程中分解语句，执行时甚至不用访问表或索引，例如从一个索引列里选取最小值可以通过单独索引查找完成。

- index_merge
- unique_subquery
- index_subquery
- range
只检索给定范围的行，使用一个索引来选择行

- index
Full Index Scan，index与ALL区别为index类型只遍历索引树

- ALL
Full Table Scan， MySQL将遍历全表以找到匹配的行

#### 4.2.4. possible_keys

#### 4.2.5. key

#### 4.2.6. key_len

#### 4.2.7. ref

#### 4.2.8. rows

#### 4.2.9. filtered

#### 4.2.10. extra


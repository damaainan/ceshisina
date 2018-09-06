## [优化 sql 语句的一般步骤](https://segmentfault.com/a/1190000010941790)


#### 一、通过 show status 命令了解各种 sql 的执行频率mysql 客户端连接成功后，通过 show [session|global] status 命令可以提供服务器状态信息，也可以在操作系统上使用 mysqladmin extend-status 命令获取这些消息。   
show status 命令中间可以加入选项 session（默认） 或 global：

* session （当前连接）
* global （自数据上次启动至今）

```sql
    # Com_xxx 表示每个 xxx 语句执行的次数。
    mysql> show status like 'Com_%';
```
##### 我们通常比较关心的是以下几个统计参数：

* Com_select : 执行 select 操作的次数，一次查询只累加 1。
* Com_insert : 执行 insert 操作的次数，对于批量插入的 insert 操作，只累加一次。
* Com_update : 执行 update 操作的次数。
* Com_delete : 执行 delete 操作的次数。

上面这些参数对于所有存储引擎的表操作都会进行累计。下面这几个参数只是针对 innodb 的，累加的算法也略有不同：

* Innodb_rows_read : select 查询返回的行数。
* Innodb_rows_inserted : 执行 insert 操作插入的行数。
* Innodb_rows_updated : 执行 update 操作更新的行数。
* Innodb_rows_deleted : 执行 delete 操作删除的行数。

通过以上几个参数，可以很容易地了解当前数据库的应用是以插入更新为主还是以查询操作为主，以及各种类型的 sql 大致的执行比例是多少。对于更新操作的计数，是对执行次数的计数，不论提交还是回滚都会进行累加。   
对于事务型的应用，通过 Com_commit 和 Com_rollback 可以了解事务提交和回滚的情况，对于回滚操作非常频繁的数据库，可能意味着应用编写存在问题。   
此外，以下几个参数便于用户了解数据库的基本情况：

* Connections ： 试图连接 mysql 服务器的次数。
* Uptime ： 服务器工作时间。
* Slow_queries : 慢查询次数。

#### 二、定义执行效率较低的 sql 语句

##### 1. 通过慢查询日志定位那些执行效率较低的 sql 语句，用 --log-slow-queries[=file_name] 选项启动时，mysqld 写一个包含所有执行时间超过 long_query_time 秒的 sql 语句的日志文件。

##### 2. 慢查询日志在查询结束以后才记录，所以在应用反映执行效率出现问题的时候慢查询日志并不能定位问题，可以使用 show processlist 命令查看当前 mysql 在进行的线程，包括线程的状态、是否锁表等，可以实时的查看 sql 的执行情况，同时对一些锁表操作进行优化。

#### 三、通过 explain 分析低效 sql 的执行计划

> 测试数据库地址：[> https://downloads.mysql.com/d...][0]

统计某个 email 为租赁电影拷贝所支付的总金额，需要关联客户表 customer 和 付款表 payment ， 并且对付款金额 amount 字段做求和（sum） 操作，相应的执行计划如下：

```sql
    mysql> explain select sum(amount) from customer a , payment b where a.customer_id= b.customer_id and a.email='JANE.BENNETT@sakilacustomer.org'\G  
    
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: a
       partitions: NULL
             type: ALL
    possible_keys: PRIMARY
              key: NULL
          key_len: NULL
              ref: NULL
             rows: 599
         filtered: 10.00
            Extra: Using where
    *************************** 2. row ***************************
               id: 1
      select_type: SIMPLE
            table: b
       partitions: NULL
             type: ref
    possible_keys: idx_fk_customer_id
              key: idx_fk_customer_id
          key_len: 2
              ref: sakila.a.customer_id
             rows: 26
         filtered: 100.00
            Extra: NULL
    2 rows in set, 1 warning (0.00 sec)
    
```

* select_type: 表示 select 类型，常见的取值有：
    * simple：简单表，及不使用表连接或者子查询
    * primary：主查询，即外层的查询
    * union：union 中的第二个或后面的查询语句
    * subquery： 子查询中的第一个 select
* table ： 输出结果集的表
* type ： 表示 mysql 在表中找到所需行的方式，或者叫访问类型，常见类型性能由差到最好依次是：all、index、range、ref、eq_ref、const，system、null：

1. type=ALL，全表扫描，mysql 遍历全表来找到匹配的行：

```sql
    mysql> explain select * from film where rating > 9 \G
    
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: film
      partitions: NULL
            type: ALL
    possible_keys: NULL
             key: NULL
         key_len: NULL
             ref: NULL
            rows: 1000
        filtered: 33.33
           Extra: Using where
    1 row in set, 1 warning (0.01 sec)
```
1. type=index, 索引全扫描，mysql 遍历整个索引来查询匹配的行

```sql
    mysql> explain select title form film\G
    
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: film
      partitions: NULL
            type: index
    possible_keys: NULL
             key: idx_title
         key_len: 767
             ref: NULL
            rows: 1000
        filtered: 100.00
           Extra: Using index
    1 row in set, 1 warning (0.00 sec)
```
1. type=range,索引范围扫描，常见于<、<=、>、>=、between等操作：

```sql
    mysql> explain select * from payment where customer_id >= 300 and customer_id <= 350 \G  
    
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: payment
      partitions: NULL
            type: range
    possible_keys: idx_fk_customer_id
             key: idx_fk_customer_id
         key_len: 2
             ref: NULL
            rows: 1350
        filtered: 100.00
           Extra: Using index condition
    1 row in set, 1 warning (0.07 sec)
```
1. type=ref, 使用非唯一索引扫描或唯一索引的前缀扫描，返回匹配某个单独值的记录行，例如：

```sql
    mysql> explain select * from payment where customer_id = 350 \G  
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: payment
      partitions: NULL
            type: ref
    possible_keys: idx_fk_customer_id
             key: idx_fk_customer_id
         key_len: 2
             ref: const
            rows: 23
        filtered: 100.00
           Extra: NULL
    1 row in set, 1 warning (0.01 sec)
    
```

索引 idx_fk_customer_id 是非唯一索引，查询条件为等值查询条件 customer_id = 350, 所以扫描索引的类型为 ref。ref 还经常出现在 join 操作中：

```sql
    mysql> explain select b.*, a.* from payment a,customer b where a.customer_id = b.customer_id \G 
    
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: b
      partitions: NULL
            type: ALL
    possible_keys: PRIMARY
             key: NULL
         key_len: NULL
             ref: NULL
            rows: 599
        filtered: 100.00
           Extra: NULL
    *************************** 2. row ***************************
              id: 1
     select_type: SIMPLE
           table: a
      partitions: NULL
            type: ref
    possible_keys: idx_fk_customer_id
             key: idx_fk_customer_id
         key_len: 2
             ref: sakila.b.customer_id
            rows: 26
        filtered: 100.00
           Extra: NULL
    2 rows in set, 1 warning (0.00 sec)
```
1. type=eq_ref,类似 ref，区别就在使用的索引时唯一索引，对于每个索引的键值，表中只要一条记录匹配；简单的说，就是多表连接中使用 primary key 或者 unique index 作为关联条件。

```sql
    mysql> explain select * from film a , film_text b where a.film_id = b.film_id \G
    
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: b
      partitions: NULL
            type: ALL
    possible_keys: PRIMARY
             key: NULL
         key_len: NULL
             ref: NULL
            rows: 1000
        filtered: 100.00
           Extra: NULL
    *************************** 2. row ***************************
              id: 1
     select_type: SIMPLE
           table: a
      partitions: NULL
            type: eq_ref
    possible_keys: PRIMARY
             key: PRIMARY
         key_len: 2
             ref: sakila.b.film_id
            rows: 1
        filtered: 100.00
           Extra: Using where
    2 rows in set, 1 warning (0.03 sec)
```
1. type=const/system,单表中最多有一个匹配行，查起来非常迅速，所以这个匹配行中的其他列的值可以被优化器在当前查询中当作常量来处理，例如，根据主键 primary key 或者唯一索引 unique index 进行查询。

```sql
    mysql> create table test_const (
       ->         test_id int,
       ->         test_context varchar(10),
       ->         primary key (`test_id`),
       ->     );
       
    insert into test_const values(1,'hello');
    
    explain select * from ( select * from test_const where test_id=1 ) a \G
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: test_const
      partitions: NULL
            type: const
    possible_keys: PRIMARY
             key: PRIMARY
         key_len: 4
             ref: const
            rows: 1
        filtered: 100.00
           Extra: NULL
     1 row in set, 1 warning (0.00 sec)
```
1. type=null, mysql 不用访问表或者索引，直接就能够得到结果：

```sql
    mysql> explain select 1 from dual where 1 \G
    *************************** 1. row ***************************
              id: 1
     select_type: SIMPLE
           table: NULL
      partitions: NULL
            type: NULL
    possible_keys: NULL
             key: NULL
         key_len: NULL
             ref: NULL
            rows: NULL
        filtered: NULL
           Extra: No tables used
    1 row in set, 1 warning (0.00 sec)

```
类型 type 还有其他值，如 ref_or_null (与 ref 类似，区别在于条件中包含对 null 的查询)、index_merge(索引合并优化)、unique_subquery (in 的后面是一个查询主键字段的子查询)、index_subquery(与 unique_subquery 类似，区别在于 in 的后面是查询非唯一索引字段的子查询)等。

* possible_keys : 表示查询时可能使用的索引。
* key ：表示实际使用索引
* key-len : 使用到索引字段的长度。
* rows ： 扫描行的数量
* extra：执行情况的说明和描述，包含不适合在其他列中显示但是对执行计划非常重要的额外信息。

##### show warnings 命令

执行explain 后再执行 show warnings，可以看到sql 真正被执行之前优化器做了哪些 sql 改写：

```sql
    MySQL [sakila]> explain select sum(amount) from customer a , payment b where 1=1 and a.customer_id = b.customer_id and email = 'JANE.BENNETT@sakilacustomer.org'\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: a
       partitions: NULL
             type: ALL
    possible_keys: PRIMARY
              key: NULL
          key_len: NULL
              ref: NULL
             rows: 599
         filtered: 10.00
            Extra: Using where
    *************************** 2. row ***************************
               id: 1
      select_type: SIMPLE
            table: b
       partitions: NULL
             type: ref
    possible_keys: idx_fk_customer_id
              key: idx_fk_customer_id
          key_len: 2
              ref: sakila.a.customer_id
             rows: 26
         filtered: 100.00
            Extra: NULL
    2 rows in set, 1 warning (0.00 sec)
    
    MySQL [sakila]> show warnings;
    +-------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Level | Code | Message                                                                                                                                                                                                                                                     |
    +-------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    | Note  | 1003 | /* select#1 */ select sum(`sakila`.`b`.`amount`) AS `sum(amount)` from `sakila`.`customer` `a` join `sakila`.`payment` `b` where ((`sakila`.`b`.`customer_id` = `sakila`.`a`.`customer_id`) and (`sakila`.`a`.`email` = 'JANE.BENNETT@sakilacustomer.org')) |
    +-------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.00 sec)
    
```

从 warning 的 message 字段中能够看到优化器自动去除了 1=1 恒成立的条件，也就是说优化器在改写 sql 时会自动去掉恒成立的条件。

##### explain 命令也有对分区的支持.

```sql
    MySQL [sakila]> CREATE TABLE `customer_part` (
        ->   `customer_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
        ->   `store_id` tinyint(3) unsigned NOT NULL,
        ->   `first_name` varchar(45) NOT NULL,
        ->   `last_name` varchar(45) NOT NULL,
        ->   `email` varchar(50) DEFAULT NULL,
        ->   `address_id` smallint(5) unsigned NOT NULL,
        ->   `active` tinyint(1) NOT NULL DEFAULT '1',
        ->   `create_date` datetime NOT NULL,
        ->   `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ->   PRIMARY KEY (`customer_id`)
        ->  
        -> ) partition by hash (customer_id) partitions 8;
    Query OK, 0 rows affected (0.06 sec)
    
    MySQL [sakila]> insert into customer_part select * from customer;
    Query OK, 599 rows affected (0.06 sec)
    Records: 599  Duplicates: 0  Warnings: 0
    
    MySQL [sakila]> explain select * from customer_part where customer_id=130\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: customer_part
       partitions: p2
             type: const
    possible_keys: PRIMARY
              key: PRIMARY
          key_len: 2
              ref: const
             rows: 1
         filtered: 100.00
            Extra: NULL
    1 row in set, 1 warnings (0.00 sec)
    
```

可以看到 sql 访问的分区是 p2。

#### 四、通过 performance_schema 分析 sql 性能

旧版本的 mysql 可以使用 profiles 分析 sql 性能，我用的是5.7.18的版本，已经不允许使用 profiles 了，推荐用  
performance_schema 分析sql。

#### 五、通过 trace 分析优化器如何选择执行计划。

mysql5.6 提供了对 sql 的跟踪 trace，可以进一步了解为什么优化器选择 A 执行计划而不是 B 执行计划，帮助我们更好的理解优化器的行为。 

使用方式：首先打开 trace ，设置格式为 json，设置 trace 最大能够使用的内存大小，避免解析过程中因为默认内存过小而不能够完整显示。

```sql
    MySQL [sakila]> set optimizer_trace="enabled=on",end_markers_in_json=on;
    Query OK, 0 rows affected (0.00 sec)
    
    MySQL [sakila]> set optimizer_trace_max_mem_size=1000000;
    Query OK, 0 rows affected (0.00 sec)
    
```

接下来执行想做 trace 的 sql 语句，例如像了解租赁表 rental 中库存编号 inventory_id 为 4466 的电影拷贝在出租日期 rental_date 为 2005-05-25 4:00:00 ~ 5:00:00 之间出租的记录：

```sql
    mysql> select rental_id from rental where 1=1 and rental_date >= '2005-05-25 04:00:00' and rental_date <= '2005-05-25 05:00:00' and inventory_id=4466;
    +-----------+
    | rental_id |
    +-----------+
    |        39 |
    +-----------+
    1 row in set (0.06 sec)
    
    MySQL [sakila]> select * from information_schema.optimizer_trace\G
    *************************** 1. row ***************************
                                QUERY: select * from infomation_schema.optimizer_trace
                                TRACE: {
      "steps": [
      ] /* steps */
    }
    MISSING_BYTES_BEYOND_MAX_MEM_SIZE: 0
              INSUFFICIENT_PRIVILEGES: 0
    1 row in set (0.00 sec)
    
```

#### 六、 确定问题并采取相应的优化措施

经过以上步骤，基本就可以确认问题出现的原因。此时可以根据情况采取相应的措施，进行优化以提高执行的效率。

[0]: https://downloads.mysql.com/docs/sakila-db.zip
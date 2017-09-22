# InnoDB备忘录 - Next-Key Lock

 时间 2017-05-20 02:19:38  

原文[http://zhongmingmao.me/2017/05/19/innodb-next-key-lock/][1]


本文主要介绍 InnoDB 存储引擎的 Next-Key Lock## MVCC 

1. InnoDB支持 MVCC ，与之 MVCC 相对的是 LBCC
1. MVCC中 读操作 分两类： Snapshot Read ( 不加锁 )和 Current Read （ 加锁 ）
1. MVCC的好处： **Snapshot Read不加锁** ， 并发性能好 ，适用于常规的 JavaWeb 项目（ OLTP 应用）

## 隔离级别 

InnoDB支持4种事务隔离级别（ Isolation Level ） 

隔离级别 | 描述 
-|-
`READ UNCOMMITTED` | 可以读取到其他事务中 `尚未提交` 的内容，生产环境中不会使用 
`READ COMMITTED(RC)` | 可以读取到其他事务中 `已经提交` 的内容， `Current Read会加锁` ， `存在幻读现象` ， Oracle 和 SQL Server 的默认事务隔离级别为 `RC`
`REPEATABLE READ(RR)` | 保证事务的 `隔离性` ， `Current Read会加锁` ，同时会加 Gap Lock ， 不存在幻读现象 ， `InnoDB` 的默认事务隔离级别为 `RR`
`SERIALIZABLE` | MVCC退化为 `LBCC` ，不区分 `Snapshot Read` 和 `Current Read` ， `读` 操作加 `S Lock` ， `写` 操作加 `X Lock` ，读写冲突，并发性能差 

## 行锁 

1. InnoDB实现了两种标准的 行锁 （ `Row-Level Lock` ）：共享锁（ `Shared(S) Lock` ）、排它锁（ `Exclusive(X) Lock` ）
1. `S Lock` ：允许事务持有该锁去 _读取一行数据_
1. `X Lock` ：允许事务持有该锁去 _更新或删除一行数据_

`S Lock` 与 `X Lock` 的兼容性 

- | S | X 
 -|-|-
S | Y | N 
X | N | N 

## 锁的算法 

## Record Lock 

1. `Record Lock` 即行锁，用于锁住 `Index Record` （索引记录），分为 `S Lock` 和 `X Lock`
1. 如果表中没有 `显式定义的主键` 或 唯一非NULL索引 ，InnoDB将自动创建 6Byte的ROWID 隐藏列作为主键

## Gap Lock 

1. 用于锁住 `Index Record` 之间的间隙
1. 如果是 `通过唯一索引来搜索一行记录` 的时候，不需要使用 Gap Lock ，此时 `Next-Ke`y 降级为 `Record Lock`
1. `Gap S-Lock` 与 `Gap X-Lock` 是兼容的
1. `Gap Lock` 只能阻止其他事务在 该Gap中插入记录 ，但 **无法阻止** 其他事务获取 同一个Gap 上的 Gap Lock
1. 禁用 `Gap Lock` 的两种方式 
    * 将事务隔离级别设置为 `READ COMMITTED`
    * 将变量 `innodb_locks_unsafe_for_binlog` （已弃用）设置为 1

## Next-Key Lock 

1. `Next-Key Lock = Record Lock + Gap Lock`
1. 若索引a为10、11、13、20，可锁定的区间为 (negative infinity, 10] 、 (10, 11] 、 (11, 13] 、 (13, 20] 、 (20, positive infinity)
    * 若执行 `Select...Where a=13 For Update` ，将在 a=13 上有1个 `X Lock` 和在 (11, 13) 有1个 `Gap Lock`
    * a=13 的下一个键为 a=20 ，将在 a=20 有1个 `X Lock` ，在 (13, 20) 有1个 `Gap Lock`
    * 因此，在 a=13 上有1个 `X Lock` ，在 (11, 20] 上的有1个 `Gap Lock`

1. 在InnoDB默认事务隔离级别 `REPEATABLE READ(RR)` 下，支持 `Next-Key Lock`

## 11个实例 

1. 下面11个实例仅仅考虑 `RC` 与 `RR` 的事务隔离级别
1. RR 支持` Next-Key Lock` 、 `Gap Lock` 和 `Record Lock` ， `RC` 仅支持 `Record Lock`

## RC/RR+Clustered Index+Equal Match 

1. 事务隔离级别 `READ COMMITTED(RC)` 或 `REPEATABLE READ(RR)`
1. 存在 `显式定义` 主键
1. `WHERE` 等值匹配成功

### 表初始化 

```sql
    mysql> CREATE TABLE t ( a INT NOT NULL PRIMARY KEY ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> INSERT INTO t VALUES (10),(20),(30),(40),(50),(60),(70),(80);
    Query OK, 8 rows affected (0.01 sec)
    Records: 8  Duplicates: 0  Warnings: 0
```

### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=30 FOR UPDATE;
    +----+
    | a  |
    +----+
    | 30 |
    +----+
    1 row in set (0.01 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1322763 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```

1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1322763 通过 `SELECT...FOR UPDATE` 操作获得了 聚集索引a （ Clustered Index ）上 30 的 `X Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 25;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 35;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1322764 | RUNNING   | NULL                  | READ COMMITTED      |
    | 1322763 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.01 sec)
    
    mysql> SELECT * FROM t WHERE a=30 LOCK IN SHARE MODE; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `READ COMMITTED`
1. 成功插入 a=25 和 a=35 ，说明在 (20,30) 和 (30,40) 上没有 `Gap Lock`
1. 事务 1322764 尝试通过 SELECT...LOCK IN SHARE MODE 获得 a=30 的 `S Lock` ，由于 `S lock` 与 `X Lock` 不兼容，且此时事务 1322763 持有对应的 `X Loc`k ，所以事务 1322764 被 `阻塞` （详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1322764:389:3:4 | 1322764     | S         | RECORD    | `test`.`t` | PRIMARY    |        389 |         3 |        4 | 30        |
    | 1322763:389:3:4 | 1322763     | X         | RECORD    | `test`.`t` | PRIMARY    |        389 |         3 |        4 | 30        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.02 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1322764           | 1322764:389:3:4   | 1322763         | 1322763:389:3:4  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (1.18 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1322764 | LOCK WAIT | 1322764:389:3:4       | READ COMMITTED      |
    | 1322763 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.00 sec)
    
    mysql> SHOW ENGINE INNODB STATUS\G
    LIST OF TRANSACTIONS FOR EACH SESSION:
    ---TRANSACTION 1322764, ACTIVE 74 sec starting index read
    mysql tables in use 1, locked 1
    LOCK WAIT 2 lock struct(s), heap size 1136, 1 row lock(s), undo log entries 2
    MySQL thread id 139, OS thread handle 140648641087232, query id 2146 localhost root statistics
    SELECT * FROM t WHERE a=30 LOCK IN SHARE MODE
    ------- TRX HAS BEEN WAITING 17 SEC FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 389 page no 3 n bits 80 index PRIMARY of table `test`.`t` trx id 1322764 lock mode S locks rec but not gap waiting
    Record lock, heap no 4 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 8000001e; asc     ;;
     1: len 6; hex 000000142f02; asc     / ;;
     2: len 7; hex dc000001af012a; asc       *;;
    ---TRANSACTION 1322763, ACTIVE 153 sec
    2 lock struct(s), heap size 1136, 1 row lock(s)
    MySQL thread id 138, OS thread handle 140648641488640, query id 2150 localhost root starting
```


1. `lock_index` 为 `PRIMARY` ，说明锁住的是 聚集索引a （ Clustered Index ）
1. trx id 1322764 lock mode S locks rec but not gap 表示事务 1322764 想要获得 `S Lock` ，不需要 `Gap Lock`

### 示意图 

![][3]

## RC+Clustered Index+Equal Not Match 

1. 事务隔离级别 `READ COMMITTED(RC)`
1. 存在 `显式定义` 主键
1. `WHERE` 等值匹配不成功

### 表初始化 

```sql
    mysql> CREATE TABLE t ( a INT NOT NULL PRIMARY KEY ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> INSERT INTO t VALUES (10),(20),(30),(40),(50),(60),(70),(80);
    Query OK, 8 rows affected (0.01 sec)
    Records: 8  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=35 FOR UPDATE;
    Empty set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1322801 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.01 sec)
```


1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1322801 尝试通过 SELECT...FOR UPDATE 操作获得了 聚集索引a （ Clustered Index ）上 35 的 X Lock ，但 a=35 不存在， 并不加任何锁

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 34;
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 36;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 35;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1322802 | RUNNING   | NULL                  | READ COMMITTED      |
    | 1322801 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.00 sec)
```


1. 将 Session B 的事务隔离级别设置为 `READ COMMITTED`
1. 成功插入 a=34 和 a=36 ，说明在 (30,40) 上没有 `Gap Lock`
1. 成功插入 a=35 ，说明在 a=35 上没有 X Lock

## RR+Clustered Index+Equal Not Match 

1. 事务隔离级别 `REPEATABLE READ(RR)`
1. 存在 显式定义 主键
1. WHERE 等值匹配不成功

### 表初始化 

```sql
    mysql> CREATE TABLE t ( a INT NOT NULL PRIMARY KEY ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> INSERT INTO t VALUES (10),(20),(30),(40),(50),(60),(70),(80);
    Query OK, 8 rows affected (0.01 sec)
    Records: 8  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=35 FOR UPDATE;
    Empty set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1323280 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `REPEATABLE-READ`
1. 事务 1323280 尝试通过 SELECT...FOR UPDATE 操作获得了 聚集索引a （ Clustered Index ）上 35 的 `X Lock` ，但 a=35 不存在，在 (30,40) 上加上 `Gap Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 35; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `REPEATABLE-READ`
1. Session B 的事务尝试插入 a=35 ，但由于事务 1323280 已经持有了 (30,40) 上的 `Gap Lock` ，因此被阻塞（详细信息见下节）

![][4]

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1323281:391:3:5 | 1323281     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        391 |         3 |        5 | 40        |
    | 1323280:391:3:5 | 1323280     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        391 |         3 |        5 | 40        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1323281           | 1323281:391:3:5   | 1323280         | 1323280:391:3:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1323281 | LOCK WAIT | 1323281:391:3:5       | REPEATABLE READ     |
    | 1323280 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.00 sec)
    
    mysql> SHOW ENGINE INNODB STATUS\G
    LIST OF TRANSACTIONS FOR EACH SESSION:
    ---TRANSACTION 1323281, ACTIVE 16 sec inserting
    mysql tables in use 1, locked 1
    LOCK WAIT 2 lock struct(s), heap size 1136, 1 row lock(s)
    MySQL thread id 5, OS thread handle 140546164094720, query id 119 localhost root executing
    INSERT INTO t SELECT 35
    ------- TRX HAS BEEN WAITING 16 SEC FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 391 page no 3 n bits 80 index PRIMARY of table `test`.`t` trx id 1323281 lock_mode X locks gap before rec insert intention waiting
    Record lock, heap no 5 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000028; asc    (;;
     1: len 6; hex 000000142f41; asc     /A;;
     2: len 7; hex a7000001fd0137; asc       7;;
    ---TRANSACTION 1323280, ACTIVE 99 sec
    2 lock struct(s), heap size 1136, 1 row lock(s)
    MySQL thread id 4, OS thread handle 140546164295424, query id 123 localhost root starting
    
    mysql> INSERT INTO t SELECT 35;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
```


1. 在事务隔离级别为 `REPEATABLE READ` 时，尝试给 不存在 的值上锁，会产生 `Gap Lock`
1. 在事务 1323280 插入 a=35 成功，因为其他事务（ 1323281 ）暂不持有 包含a=35 的 `Gap Lock` ，因此无法阻塞事务 1323280 的插入操作
1. 插入成功后，事务 1323280 持有 a=35 的 `X Lock`

![][5]

### Session B 

```sql
    mysql> INSERT INTO t SELECT 35; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=37 FOR UPDATE;
    Empty set (0.00 sec)
```


事务 1323280 持有 (30,40) 的 `Gap Lock` ，但无法阻止事务 1323281 获得 (35,40) 上的 `Gap Lock` （事务 1323280 已获得 a=35 的 X Lock ） 

![][6]

### Session A 

```sql
    mysql> INSERT INTO t SELECT 33;
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 36; # Blocked
```


1. 事务 1323280 持有 (30,40) 上的 Gap Lock ，另一个事务 1323281 持有 (35,40) 上的 `Gap Lock`
1. 插入 a=33 不被阻塞，插入成功后事务 1323280 持有 a=33 的 `X Lock`
1. 插入 a=36 被事务 1323281 持有 (35,40) 上的 `Gap Lock` 阻塞（详细信息见下节）

![][7]

### Session B 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1323280:391:3:5 | 1323280     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        391 |         3 |        5 | 40        |
    | 1323281:391:3:5 | 1323281     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        391 |         3 |        5 | 40        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1323280           | 1323280:391:3:5   | 1323281         | 1323281:391:3:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1323281 | RUNNING   | NULL                  | REPEATABLE READ     |
    | 1323280 | LOCK WAIT | 1323280:391:3:5       | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.00 sec)
    
    mysql> SHOW ENGINE INNODB STATUS\G;
    LIST OF TRANSACTIONS FOR EACH SESSION:
    ---TRANSACTION 1323281, ACTIVE 305 sec
    2 lock struct(s), heap size 1136, 2 row lock(s)
    MySQL thread id 5, OS thread handle 140546164094720, query id 131 localhost root starting
    SHOW ENGINE INNODB STATUS
    ---TRANSACTION 1323280, ACTIVE 388 sec inserting
    mysql tables in use 1, locked 1
    LOCK WAIT 3 lock struct(s), heap size 1136, 4 row lock(s), undo log entries 2
    MySQL thread id 4, OS thread handle 140546164295424, query id 127 localhost root executing
    INSERT INTO t SELECT 36
    ------- TRX HAS BEEN WAITING 11 SEC FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 391 page no 3 n bits 80 index PRIMARY of table `test`.`t` trx id 1323280 lock_mode X locks gap before rec insert intention waiting
    Record lock, heap no 5 PHYSICAL RECORD: n_fields 3; compact format; info bits 0
     0: len 4; hex 80000028; asc    (;;
     1: len 6; hex 000000142f41; asc     /A;;
     2: len 7; hex a7000001fd0137; asc       7;;
```


## RC+Clustered Index+Range 

1. 事务隔离级别 `READ COMMITTED(RC)`
1. 存在 显式定义 主键
1. WHERE采用 RANGE 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t ( a INT NOT NULL PRIMARY KEY ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> INSERT INTO t VALUES (10),(20),(30),(40),(50);
    Query OK, 5 rows affected (0.01 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a>15 AND a<45 FOR UPDATE;
    +----+
    | a  |
    +----+
    | 20 |
    | 30 |
    | 40 |
    +----+
    3 rows in set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                    
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1323886 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1323886 将获得 聚集索引a 上 20 、 30 、 40 上的 `X Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';      
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 25;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 35;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> SELECT * FROM t WHERE a=30 FOR UPDATE; # BLocked
```


1. 将 Session B 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1323887 成功插入 a=25 和 a=35 ，表明 (20,30) 和 (30,40) 上不存在 `Gap Lock`
1. 因为事务 1323886 已经持有 a=30 的 X Lock ，因此事务 1323887 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1323887:399:3:4 | 1323887     | X         | RECORD    | `test`.`t` | PRIMARY    |        399 |         3 |        4 | 30        |
    | 1323886:399:3:4 | 1323886     | X         | RECORD    | `test`.`t` | PRIMARY    |        399 |         3 |        4 | 30        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1323887           | 1323887:399:3:4   | 1323886         | 1323886:399:3:4  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.01 sec)
```


### 示意图 

![][8]

## RR+Clustered Index+Range 

1. 事务隔离级别 `REPEATABLE READ(RR)`
1. 存在 显式定义 主键
1. WHERE采用 `RANGE` 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t ( a INT NOT NULL PRIMARY KEY ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> INSERT INTO t VALUES (10),(20),(30),(40),(50);
    Query OK, 5 rows affected (0.01 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a>15 AND a<35 FOR UPDATE;
    +----+
    | a  |
    +----+
    | 20 |
    | 30 |
    +----+
    2 rows in set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324370 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `REPEATABLE READ`
1. 事务 1324370 将获得 聚集索引a 上 20 、 30 的 `X Lock` ，并将对应地获得 (10,20) 和 (20,30) 上的 `Gap Lock`
1. 依据 Next-Key Lock ，事务 1324370 还将获得 聚集索引a 上 40 的 `X Lock` 以及 (30,40) 上的 `Gap Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';                                                                                     Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;                                                                                                                          Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 5;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 45;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 55;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 39; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `REPEATABLE READ`
1. 成功插入 5 、 45 、 55 ，表明事务 1324370 并没有持有 (negative infinity,10) 、 (40,50) 和 (50,positive infinity) 上的 Gap Lock
1. 事务 1324370 已持有 (30,40) 上的 `Gap Lock` ，因此事务 1324371 插入 39 会被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324371           | 1324371:404:3:5   | 1324370         | 1324370:404:3:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324371:404:3:5 | 1324371     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        5 | 40        |
    | 1324370:404:3:5 | 1324370     | X         | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        5 | 40        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> INSERT INTO t SELECT 39; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t SELECT 11; # Blocked
```


事务 1324371 插入 11 会被阻塞，原因同插入39一致，不再赘述，详细信息见下节 

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324371:404:3:3 | 1324371     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        3 | 20        |
    | 1324370:404:3:3 | 1324370     | X         | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        3 | 20        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324371           | 1324371:404:3:3   | 1324370         | 1324370:404:3:3  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> INSERT INTO t SELECT 11; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=10 FOR UPDATE;
    +----+
    | a  |
    +----+
    | 10 |
    +----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=40 FOR UPDATE; # Blocked
```


1. 事务 1324370 并不持有 聚集索引a 上 10 的 `X Lock` ，事务 1324371 可以顺利获取 聚集索引a 上 10 的 `X Lock`
1. 事务 1324370 持有 聚集索引a 上 40 的 `X Lock` ，事务 1324371 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324371           | 1324371:404:3:5   | 1324370         | 1324370:404:3:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.01 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324371:404:3:5 | 1324371     | X         | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        5 | 40        |
    | 1324370:404:3:5 | 1324370     | X         | RECORD    | `test`.`t` | PRIMARY    |        404 |         3 |        5 | 40        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


### 示意图 

![][9]

## RC+Secondary Unique Index+Range 

1. 事务隔离级别 `READ COMMITTED(RC)`
1. 存在 `唯一辅助索引`
1. WHERE 通过 `RANGE` 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a),
        -> UNIQUE KEY (b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (10,20),(20,50),(30,10),(40,40),(50,30);
    Query OK, 5 rows affected (0.05 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b>25 AND b<45 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 50 | 30 |
    | 40 | 40 |
    +----+----+
    2 rows in set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324402 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.01 sec)
```


1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1324402 将获得 辅助唯一索引b 上 30 、 40 的 `X Lock` ，并获得对应的 聚集索引a 上 50 、 40 上的 `X Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=30 FOR UPDATE; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1324402 已经持有 辅助唯一索引b 上 30 的 `X Lock` ，因此会被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324403:405:4:6 | 1324403     | X         | RECORD    | `test`.`t` | b          |        405 |         4 |        6 | 30        |
    | 1324402:405:4:6 | 1324402     | X         | RECORD    | `test`.`t` | b          |        405 |         4 |        6 | 30        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.01 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324403           | 1324403:405:4:6   | 1324402         | 1324402:405:4:6  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE b=30 FOR UPDATE; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=50 FOR UPDATE; # Blocked
```


事务 1324402 已经持有 聚集索引b 上 50 的 `X Lock` ，因此会被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324403:405:3:6 | 1324403     | X         | RECORD    | `test`.`t` | PRIMARY    |        405 |         3 |        6 | 50        |
    | 1324402:405:3:6 | 1324402     | X         | RECORD    | `test`.`t` | PRIMARY    |        405 |         3 |        6 | 50        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324403           | 1324403:405:3:6   | 1324402         | 1324402:405:3:6  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### 示意图 

![][10]

## RR+Secondary Unique Index+Range 

1. 事务隔离级别 `REPEATABLE READ(RR)`
1. 存在显式定义 `唯一辅助索引`
1. WHERE 通过 `RANGE` 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a),
        -> UNIQUE KEY (b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (10,90),(20,50),(30,80),(40,60),(50,70);
    Query OK, 5 rows affected (0.05 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b>55 AND b<85 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 40 | 60 |
    | 50 | 70 |
    | 30 | 80 |
    +----+----+
    3 rows in set (0.00 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324512 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.01 sec)
```


1. 将 Session A 的事务隔离级别设置为 `REPEATABLE READ`
1. 事务 1324512 将获得 唯一辅助索引b 上 60 、 70 、 80 上的 `X Lock` 以及 (50,60) 、 (60,70) 、 (70,80) 上的 `Gap Lock` ，相应地也会获得 聚集索引a 上 40 、 50 、 30 上的 `X Lock`
1. 依据 `Next-Key Lock` ，事务 1324512 将获得 唯一辅助索引b 上 90 上的 X Lock 以及 (80,90) 上的 `Gap Lock`
1. 事务 1324512 不会在 聚集索引a 上进行 `Gap Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=50 FOR UPDATE;                                                                                          +----+----+
    | a  | b  |
    +----+----+
    | 20 | 50 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=90 FOR UPDATE; # Blocked(60/70/80 blocked too)
```


1. 将 Session B 的事务隔离级别设置为 `REPEATABLE READ`
1. 唯一辅助索引b 上 50 尚未被其他事务锁定，事务 1324513 可以顺利获得 唯一辅助索引b 上 50 的 `X Lock`
1. 事务 1324512 已持有 唯一辅助索引b 上 90 的 `X Lock` ，事务 1324513 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324513:410:4:2 | 1324513     | X         | RECORD    | `test`.`t` | b          |        410 |         4 |        2 | 90        |
    | 1324512:410:4:2 | 1324512     | X         | RECORD    | `test`.`t` | b          |        410 |         4 |        2 | 90        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324513           | 1324513:410:4:2   | 1324512         | 1324512:410:4:2  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE b=90 FOR UPDATE; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=20 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 20 | 50 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=10 FOR UPDATE; # Blocked(40/50/30 blocked too)
```


1. 聚集索引a 上 20 尚未被其他事务锁定，事务 1324513 可以顺利获得 聚集索引a 上 20 的 `X Lock`
1. 事务 1324512 已持有 聚集索引a 上 10 的 `X Lock` ，事务 1324513 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324513:410:3:2 | 1324513     | X         | RECORD    | `test`.`t` | PRIMARY    |        410 |         3 |        2 | 10        |
    | 1324512:410:3:2 | 1324512     | X         | RECORD    | `test`.`t` | PRIMARY    |        410 |         3 |        2 | 10        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324513           | 1324513:410:3:2   | 1324512         | 1324512:410:3:2  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE a=10 FOR UPDATE; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t VALUES (5,45);                                                                                                    Query OK, 1 row affected (0.00 sec)
    
    mysql> INSERT INTO t VALUES (6,55); # Blocked
```


1. 唯一聚集索引b 上 (negative infinity,50) 的尚未被其他事务锁定，因此事务 1324513 成功插入 (5,45)
1. 事务 1324512 持有 唯一聚集索引b 上 (50,60) 的 Gap Lock ，因此事务 1324513 插入 (6,55) 时会被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324513:410:4:5 | 1324513     | X,GAP     | RECORD    | `test`.`t` | b          |        410 |         4 |        5 | 60        |
    | 1324512:410:4:5 | 1324512     | X         | RECORD    | `test`.`t` | b          |        410 |         4 |        5 | 60        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324513           | 1324513:410:4:5   | 1324512         | 1324512:410:4:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### 示意图 

![][11]

## RC+Secondary Index+Range 

1. 事务隔离级别 `READ COMMITTED(RC)`
1. 存在显式定义 非唯一辅助索引
1. WHERE 通过 RANGE 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a),
        -> KEY (b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (60,50),(70,30),(80,20),(90,40),(100,30),(110,20),(120,10);
    Query OK, 7 rows affected (0.01 sec)
    Records: 7  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b>15 AND b<35 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    |  80 | 20 |
    | 110 | 20 |
    |  70 | 30 |
    | 100 | 30 |
    +-----+----+
    4 rows in set (1.97 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324589 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.01 sec)
```


1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 事务 1324589 持有 辅助索引b 上 (20,80) 、 (20,110) 、 (30,70) 、 (30,100) 的 `X Lock` ，并相应地持有 聚集索引a 上 (80,20) 、 (110,20) 、 (70,30) 、 (100,30) 的 X Lock

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=10 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    | 120 | 10 |
    +-----+----+
    1 row in set (0.02 sec)
    
    mysql> SELECT * FROM t WHERE b=40 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 90 | 40 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=30 FOR UPDATE; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `READ COMMITTED`
1. 辅助索引b 上 (10,120) 和 (40,90) 尚未被其他事务锁定，事务 1324590 能成功获取 辅助索引b 上 (10,120) 和 (40,90) 的 X Lock
1. 事务 1324589 持有辅助索引b上 (30,70) 的 `X Lock` ，因此事务 1324590 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324590           | 1324590:413:4:3   | 1324589         | 1324589:413:4:3  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.01 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324590:413:4:3 | 1324590     | X         | RECORD    | `test`.`t` | b          |        413 |         4 |        3 | 30, 70    |
    | 1324589:413:4:3 | 1324589     | X         | RECORD    | `test`.`t` | b          |        413 |         4 |        3 | 30, 70    |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.01 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE b=30 FOR UPDATE;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=120 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    | 120 | 10 |
    +-----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=90 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 90 | 40 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=100 FOR UPDATE; # Blocked
```


1. 聚集索引a 上 (120,10) 和 (90,40) 尚未被其他事务锁定，事务 1324590 能成功获取 聚集索引a 上 (120,10) 和 (90,40) 的 X Lock
1. 事务 1324589 持有 聚集索引a 上 (100,30) 的 X Lock ，因此事务 1324590 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324590           | 1324590:413:3:6   | 1324589         | 1324589:413:3:6  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324590:413:3:6 | 1324590     | X         | RECORD    | `test`.`t` | PRIMARY    |        413 |         3 |        6 | 100       |
    | 1324589:413:3:6 | 1324589     | X         | RECORD    | `test`.`t` | PRIMARY    |        413 |         3 |        6 | 100       |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


### 示意图 

![][12]

## RR+Secondary Index+Range 

1. 事务隔离级别 `REPEATABLE READ(RR)`
1. 存在显式定义 `非唯一辅助索引`
1. WHERE 通过 `RANGE` 匹配

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a),
        -> KEY (b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (60,50),(70,30),(80,20),(90,40),(100,30),(110,20),(120,10);
    Query OK, 7 rows affected (0.01 sec)
    Records: 7  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b>15 AND b<35 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    |  80 | 20 |
    | 110 | 20 |
    |  70 | 30 |
    | 100 | 30 |
    +-----+----+
    4 rows in set (1.97 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324567 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `REPEATABLE READ`
1. 事务 1324567 持有 辅助索引b 上 (20,80) 、 (20,110) 、 (30,70) 、 (30,100) 的 X Lock 和 (10,120)~(20,80) 、 (20,80)~(20,110) 、 (20,110)~(30,70) 、 (30,70)~(30,100) 、 (30,100)~(40,90) 上的 Gap Lock ，并相应地持有 聚集索引a 上 (80,20) 、 (110,20) 、 (70,30) 、 (100,30) 的 `X Lock`
1. 依据 `Next-Key Lock` ， 事务 1324567 还持有 辅助索引b 上 (40,90) 的 X Lock 和 (30,100)~(40,90) 上的 `Gap Lock` ，并相应地持有 聚集索引a 上 (90,40) 的 `X Lock`

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=10 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    | 120 | 10 |
    +-----+----+
    1 row in set (0.02 sec)
    
    mysql> SELECT * FROM t WHERE b=40 FOR UPDATE; # Blocked
```


1. 将 Session B 的事务隔离级别设置为 `REPEATABLE READ`
1. 辅助索引b 上 (10,120) 尚未被其他事务锁定，事务 1324568 能成功获取 辅助索引b 上 (10,120) 的 X Lock
1. 事务 1324567 持有 辅助索引b 上 (40,90) 的 X Lock ，因此事务 1324568 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324568:412:4:5 | 1324568     | X         | RECORD    | `test`.`t` | b          |        412 |         4 |        5 | 40, 90    |
    | 1324567:412:4:5 | 1324567     | X         | RECORD    | `test`.`t` | b          |        412 |         4 |        5 | 40, 90    |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.03 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324568           | 1324568:412:4:5   | 1324567         | 1324567:412:4:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE b=40 FOR UPDATE; # Timout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=120 FOR UPDATE;
    +-----+----+
    | a   | b  |
    +-----+----+
    | 120 | 10 |
    +-----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=90 FOR UPDATE; # Blocked
```


1. 聚集索引a 上 (120,10) 尚未被其他事务锁定，事务 1324568 能成功获取 聚集索引a 上 (120,10) 的 `X Lock`
1. 事务 1324567 持有 聚集索引a 上 (90,40) 的 X Lock ，因此事务 1324568 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324568           | 1324568:412:3:5   | 1324567         | 1324567:412:3:5  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.01 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324568:412:3:5 | 1324568     | X         | RECORD    | `test`.`t` | PRIMARY    |        412 |         3 |        5 | 90        |
    | 1324567:412:3:5 | 1324567     | X         | RECORD    | `test`.`t` | PRIMARY    |        412 |         3 |        5 | 90        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE a=90 FOR UPDATE; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t VALUES (95,40);
    Query OK, 1 row affected (0.01 sec)
    
    mysql> INSERT INTO t VALUES (75,20); # Blocked
```


1. 辅助索引b 上 (40,90)~(50,60) 不存在 Gap Lock ，事务 1324568 能成功插入 (95,40)
1. 事务 1324567 持有 辅助索引b 上 (10,120)~(20,80) 的 Gap Lock ，事务 1324568 插入 (75,20) 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324568:412:4:4 | 1324568     | X,GAP     | RECORD    | `test`.`t` | b          |        412 |         4 |        4 | 20, 80    |
    | 1324567:412:4:4 | 1324567     | X         | RECORD    | `test`.`t` | b          |        412 |         4 |        4 | 20, 80    |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324568           | 1324568:412:4:4   | 1324567         | 1324567:412:4:4  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> INSERT INTO t VALUES (75,20); # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t VALUES (115,20); # Blocked
```


事务 1324567 持有 辅助索引b 上 (20,110)~(30,70) 的 Gap Lock ，事务 1324568 插入 (115,20) 被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324568:412:4:3 | 1324568     | X,GAP     | RECORD    | `test`.`t` | b          |        412 |         4 |        3 | 30, 70    |
    | 1324567:412:4:3 | 1324567     | X         | RECORD    | `test`.`t` | b          |        412 |         4 |        3 | 30, 70    |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324568           | 1324568:412:4:3   | 1324567         | 1324567:412:4:3  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### 示意图 

![][13]

在 `RR` 隔离级别下，类似 SELECT ... FOR UPDATE 这种 `Current Read` ，使用 `Gap Lock` 能保证过滤出来的范围不被其他事务插入新的记录，防止 `幻读` 的产生 

## RC+No Index 

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (10,50),(20,60),(30,70),(40,80),(50,90);
    Query OK, 5 rows affected (0.02 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=70 OR b=90 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 30 | 70 |
    | 50 | 90 |
    +----+----+
    2 rows in set (0.01 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                        
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324624 | RUNNING   | NULL                  | READ COMMITTED      |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `READ COMMITTED`
1. 由于 列b上无索引 ，只能通过 聚集索引a 进行 全表扫描 ，事务 1324624 将持有 聚集索引a 上 30 、 50 的 X Lock

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';                                                                                      Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=10 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 50 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=20 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 20 | 60 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=40 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 40 | 80 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=30 FOR UPDATE; # Blocked
```


1. 聚集索引a 上的 10 、 20 、 40 并未被其他事务锁定，事务 1324625 能成功获取它们的 `X Lock`
1. 事务 1324624 持有 聚集索引a 上的 30 的 X lock ，事务 1324625 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324625           | 1324625:414:3:4   | 1324624         | 1324624:414:3:4  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324625:414:3:4 | 1324625     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        4 | 30        |
    | 1324624:414:3:4 | 1324624     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        4 | 30        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


### Session B 

```sql
    mysql> SELECT * FROM t WHERE a=30 FOR UPDATE; # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=50 FOR UPDATE; # Blocked
```


事务 1324624 持有 聚集索引a 上的 50 的 `X lock` ，事务 1324625 被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324625:414:3:6 | 1324625     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        6 | 50        |
    | 1324624:414:3:6 | 1324624     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        6 | 50        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324625           | 1324625:414:3:6   | 1324624         | 1324624:414:3:6  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```


### 示意图 

![][14]

## RR+No Index 

### 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> PRIMARY KEY (a)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t VALUES (10,50),(20,60),(30,70),(40,80),(50,90);
    Query OK, 5 rows affected (0.02 sec)
    Records: 5  Duplicates: 0  Warnings: 0
```


### Session A 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE b=70 FOR UPDATE;
    +----+----+
    | a  | b  |
    +----+----+
    | 30 | 70 |
    +----+----+
    1 row in set (0.01 sec)
    
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;                           +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1324610 | RUNNING   | NULL                  | REPEATABLE READ     |
    +---------+-----------+-----------------------+---------------------+
    1 row in set (0.00 sec)
```


1. 将 Session A 的事务隔离级别设置为 `REPEATABLE READ`
1. 由于 列b上无索引 ，只能通过 聚集索引a 进行 全表扫描 ，事务 1324610 将持有 聚集索引a 上 10 、 20 、 30 、 40 、 50 的 `X Lock` ，并持有 聚集索引a 上 (negative infinity,10) 、 (10,20) 、 (20,30) 、 (30,40) 、 (40,50) 、 (50,positive infinity) 上的 Gap Lock

### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t VALUES (5,100); # Blocked
```


事务 1324610 持有 聚集索引a 上 (negative infinity,10) 的 `Gap Lock` ，事务 1324611 插入 (5,100) 被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324611:414:3:2 | 1324611     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        2 | 10        |
    | 1324610:414:3:2 | 1324610     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        2 | 10        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324611           | 1324611:414:3:2   | 1324610         | 1324610:414:3:2  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```

### Session B 

```sql
    mysql> INSERT INTO t VALUES (5,100); # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t VALUES (25,100); # Blocked
```

事务 1324610 持有 聚集索引a 上 (20,30) 的 Gap Lock ，事务 1324611 插入 (25,100) 被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324611           | 1324611:414:3:4   | 1324610         | 1324610:414:3:4  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324611:414:3:4 | 1324611     | X,GAP     | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        4 | 30        |
    | 1324610:414:3:4 | 1324610     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        4 | 30        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.01 sec)
```

### Session B 

```sql
    mysql> INSERT INTO t VALUES (25,100); # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t VALUES (55,100); # Blocked
```

1. `positive infinity` 即 `supremum pseudo-record` ，相关信息请参照「InnoDB备忘录 - 数据页格式」
1. 事务 1324610 持有 聚集索引a 上 (50,positive infinity) 的 Gap Lock ，事务 1324611 插入 (55,100) 被阻塞（详细信息见下节）

### Session A 

```sql
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+------------------------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data              |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+------------------------+
    | 1324611:414:3:1 | 1324611     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        1 | supremum pseudo-record |
    | 1324610:414:3:1 | 1324610     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        1 | supremum pseudo-record |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+------------------------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324611           | 1324611:414:3:1   | 1324610         | 1324610:414:3:1  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```

### Session B 

```sql
    mysql> INSERT INTO t VALUES (55,100); # Timeout
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> SELECT * FROM t WHERE a=50 FOR UPDATE; # Blocked
```

事务 1324610 持有 聚集索引a 上 50 的 X Lock ，事务 1324611 被阻塞（详细信息见下节） 

### Session A 

```sql
    mysql> select * from information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1324611           | 1324611:414:3:6   | 1324610         | 1324610:414:3:6  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1324611:414:3:6 | 1324611     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        6 | 50        |
    | 1324610:414:3:6 | 1324610     | X         | RECORD    | `test`.`t` | PRIMARY    |        414 |         3 |        6 | 50        |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
```


[1]: http://zhongmingmao.me/2017/05/19/innodb-next-key-lock/

[3]: ./img/ZJfUZfn.png
[4]: ./img/JfYrEne.png
[5]: ./img/re22IjN.png
[6]: ./img/2mAzUnR.png
[7]: ./img/FvQrQfq.png
[8]: ./img/VVfIjq3.png
[9]: ./img/JNfamaj.png
[10]: ./img/Ub2AjuB.png
[11]: ./img/2iYNVvq.png
[12]: ./img/bqMjauY.png
[13]: ./img/IFZ3Ybv.png
[14]: ./img/EnaEjuV.png
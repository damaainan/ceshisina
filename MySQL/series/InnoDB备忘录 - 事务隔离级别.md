# InnoDB备忘录 - 事务隔离级别

 时间 2017-09-01 14:33:47  

原文[http://zhongmingmao.me/2017/05/22/innodb-isolation-level/][1]



本文主要介绍 InnoDB 的 事务隔离级别

## 脏读、不可重复读、幻读 

## 脏读 

在不同的事务下，当前事务可以读到其它事务中 `尚未提交` 的数据，即可以读到 `脏数据`

## 不可重复读 

在同一个事务中，同一个查询在T1时间读取某一行，在T2时间重新读取这一行时候，这一行的数据已经发生修改，不可重复读的重点是修改（ `Update` ） 

## 幻读 

在同一事务中，同一查询多次进行，由于包含插入或删除操作的其他事务提交，导致每次返回不同的结果集，幻读的重点在于插入（ `Insert` ）或者删除( `Delete` ) 

## 两类读操作 

## 一致性非锁定读 

1. `InnoDB` 通过行 `多版本控制` 的方式来读取当前执行时间数据中行的数据，如果读取的行正在执行 `DELETE` 或 `UPDATE` 操作，这时读操作 不会等待行上锁的释放 ，而是读取行的一个 `快照数据`
1. 非锁定读机 制极大地提高了数据库的 `并发性` ，这是 InnoDB默认的读取方式
1. `READ COMMITED` 和 `REPEATABLE READ` 支持 一致性非锁定读 ：在 `READ COMMITED` 下，总是读取被锁定行的 最新的快照数据 ，在 `REPEATABLE READ` 下，总是读取 事务开始时的快照数据

## 一致性锁定读 

对数据库读操作进行 `显式加锁` 以保证数据逻辑的 `一致性` ，  
有两种方式：` SELECT…FOR UPDATE` 对读取的行记录加一个 `X Lock` ；   
`SELECT…LOCK IN SHARE MODE` 对读取的行记录加一个 `S Lock`

## 隔离级别 

## 级别与问题 

✓ ：可能出现 ✗ ：不会出现 

隔离级别 | 脏读 | 不可重复读 | 幻读 
-|-|-|-
READ UNCOMMITTED | ✓ | ✓ | ✓ 
READ COMMITTED | ✗ | ✓ | ✓ 
REPEATABLE READ | ✗ | ✗ | ✓ 
SERIALIZABLE | ✗ | ✗ | ✗ 

## 实例 

### RUC与脏读 

`READ-UNCOMMITTED` 存在 `脏读` 问题 

#### Session A 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b INT NOT NULL
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.05 sec)
    
    mysql> INSERT INTO t SELECT 1,1;
    Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> SET SESSION TX_ISOLATION='READ-UNCOMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql>BEGIN;
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> SELECT * FROM t;
    +---+---+
    | a | b |
    +---+---+
    | 1 | 1 |
    +---+---+
    1 row in set (0.00 sec)
```

#### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-UNCOMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> UPDATE t SET b=2 WHERE a=1;
    Query OK, 1 row affected (0.01 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
```

Session B 将记录从 (1,1) 更新为 (1,2) ，因此持有了该记录的 X Lock

#### Session A 

```sql
    mysql> SELECT * FROM t;
    +---+---+
    | a | b |
    +---+---+
    | 1 | 2 |
    +---+---+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a=1 FOR UPDATE; # 一致性锁定，阻塞一段时间后超时
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

Session A 读到了 Session B 尚未提交的数据，属于 `脏读`  
Session A 尝试持有记录的 X Lock ，此时该 X Lock 的持有者为 Session B ， Session A 被阻塞 

#### Session B 

事务和锁的详细信息如下 

```sql
    mysql> SELECT trx_id,trx_state,trx_requested_lock_id,trx_isolation_level FROM INFORMATION_SCHEMA.INNODB_TRX;
    +---------+-----------+-----------------------+---------------------+
    | trx_id  | trx_state | trx_requested_lock_id | trx_isolation_level |
    +---------+-----------+-----------------------+---------------------+
    | 1328837 | LOCK WAIT | 1328837:463:3:2       | READ UNCOMMITTED    |
    | 1328836 | RUNNING   | NULL                  | READ UNCOMMITTED    |
    +---------+-----------+-----------------------+---------------------+
    2 rows in set (0.00 sec)
    
    mysql> SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | lock_id         | lock_trx_id | lock_mode | lock_type | lock_table | lock_index | lock_space | lock_page | lock_rec | lock_data |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    | 1328837:463:3:2 | 1328837     | X         | RECORD    | `test`.`t` | PRIMARY    |        463 |         3 |        2 | 1         |
    | 1328836:463:3:2 | 1328836     | X         | RECORD    | `test`.`t` | PRIMARY    |        463 |         3 |        2 | 1         |
    +-----------------+-------------+-----------+-----------+------------+------------+------------+-----------+----------+-----------+
    2 rows in set, 1 warning (0.00 sec)
    
    mysql> SELECT * FROM information_schema.INNODB_LOCK_WAITS;
    +-------------------+-------------------+-----------------+------------------+
    | requesting_trx_id | requested_lock_id | blocking_trx_id | blocking_lock_id |
    +-------------------+-------------------+-----------------+------------------+
    | 1328837           | 1328837:463:3:2   | 1328836         | 1328836:463:3:2  |
    +-------------------+-------------------+-----------------+------------------+
    1 row in set, 1 warning (0.00 sec)
```

### RC与不可重复读 

`READ-COMMITTED` 解决了 `READ-UNCOMMITTED` 存在的 `脏读` 问题，解决方法是采用 `一致性非锁定读` ，读取 最新的快照版本 ，但仍然存在 `不可重复读` 问题 

#### Session A 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b INT NOT NULL
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.05 sec)
    
    mysql> INSERT INTO t SELECT 1,1;
    Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql>BEGIN;
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> SELECT * FROM t;
    +---+---+
    | a | b |
    +---+---+
    | 1 | 1 |
    +---+---+
    1 row in set (0.00 sec)
```

#### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='READ-COMMITTED';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> UPDATE t SET b=2 WHERE a=1;
    Query OK, 1 row affected (0.01 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
```

Session B 将记录从 (1,1) 更新为 (1,2) ，因此持有了该记录的 X Lock

#### Session A 

```sql
    mysql> SELECT * FROM t; # 一致性非锁定读
    +---+---+
    | a | b |
    +---+---+
    | 1 | 1 |
    +---+---+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t FOR UPDATE; # 一致性锁定，阻塞一段时间后超时
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

Session A 采用 `一致性非锁定读` ，而 Session B 尚未提交，因此 Session A 能读取到的 最新快照版本 依然为 (1,1) ，解决了 `READ-UNCOMMITTED` 的 `脏读` 问题 

#### Session B 

```sql
    mysql> COMMIT; # 提交事务
    Query OK, 0 rows affected (0.01 sec)
```

#### Session A 

```sql
    mysql> SELECT * FROM t;
    +---+---+
    | a | b |
    +---+---+
    | 1 | 2 |
    +---+---+
    1 row in set (0.00 sec)
```

Session B 提交事务后， Session A 能读取到最新的快照版本 (1,2) ，而 Session A 尚未提交事务，初始快照版本为 (1,1) ，属于 `不可重复读`

### RR与幻读 

1. `REPEATABLE-READ` 解决了 `READ-COMMITTED` 存在的 `不可重复读` 问题，解决方法是采用 `一致性非锁定读` ，读取 事务初始时的快照版本 ，但这样仍然存在 `幻读` 问题
1. `REPEATABLE-READ` 结合 `Next-Key Locking` ，可以解决 `幻读` 问题
1. 在 `REPEATABLE-READ` 下， MVCC 可以这样理解： MV(Multi Version) 用于解决 `脏读` 和 `不可重复读` ，而 CC(Concurrency Control) 则是利用 `Next-Key Locking` 解决 `幻读` 问题
1. `REPEATABLE READ` 为 InnoDB 的 默认事务隔离级别 ， `REPEATABLE READ` 已经完全保证事务的 隔离性 要求，即达到 `SERIALIZABLE` 隔离级别
1. 隔离级别越低， 事务请求的锁 越少或 保持锁的时间 就越短，因此大多数数据库系统（ Oracle 、 SQL Server ）的默认事务隔离级别是 `READ COMMITTED`
1. InnoDB 中选择 `REPEATABLE READ` 的事务隔离级别 不会有任何性能的损失 ，同样地，即使使用 `READ COMMITTED` 的隔离级别，用户也 不会得到性能上的大幅度提升

#### Session A 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b INT NOT NULL
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.05 sec)
    
    mysql> INSERT INTO t VALUES (10,10),(20,20),(30,30);
    Query OK, 3 row affected (0.02 sec)
    Records: 3  Duplicates: 0  Warnings: 0
    
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql>BEGIN;
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> SELECT * FROM t WHERE a < 25; # 一致性非锁定读
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    | 20 | 20 |
    +----+----+
    2 rows in set (0.00 sec)
```

#### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='REPEATABLE-READ';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql>BEGIN;
    Query OK, 0 rows affected (0.20 sec)
    
    mysql> UPDATE t SET b=200 WHERE a=20;
    Query OK, 1 row affected (0.01 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> COMMIT;
    Query OK, 0 rows affected (0.01 sec)
```

#### Session A 

```sql
    mysql> SELECT * FROM t WHERE a < 25; # 可重复读
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    | 20 | 20 |
    +----+----+
    2 rows in set (0.00 sec)
```

Session A 采用的是 一致性非锁定读 ，读取 事务初始时的快照版本 ，解决了 `READ-COMMITTED` 的 `不可重复读` 问题 

#### Session B 

```sql
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 0,0;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> COMMIT;
    Query OK, 0 rows affected (0.00 sec)
```

#### Session A 

```sql
    mysql> SELECT * FROM t WHERE a < 25; # 可重复读
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    | 20 | 20 |
    +----+----+
    2 rows in set (0.00 sec)
    
    mysql> INSERT INTO t SELECT 0,0;
    ERROR 1062 (23000): Duplicate entry '0' for key 'PRIMARY'
```

SELECT 筛选出来并没有 a=0 的记录，但在 `DELETE` 时却发生主键冲突，属于 `幻读`

#### Session A 

```sql
    mysql> SELECT * FROM t WHERE a < 25 FOR UPDATE; # 一致性锁定读，采用Next-Key Locking加锁
    +----+-----+
    | a  | b   |
    +----+-----+
    |  0 |   0 |
    | 10 |  10 |
    | 20 | 200 |
    +----+-----+
    3 rows in set (0.00 sec)
```

`SELECT...FOR UPDATE` 属于 一致性锁定读 ，获取 最新的快照版本 ，然后利用 `Next-Key Locking` 进行加锁 

加锁的情况：在 a = 0,10,20,30 上加 X Lock ，在 a ∈ (-∞,0)∪(0,10)∪(10,20)∪(20,30) 加 Gap Lock  
关于 Next-Key Locking 的详细内容，请参照博文「InnoDB备忘录 - Next-Key Lock」 

#### Session B 

```sql
    mysql> SET SESSION innodb_lock_wait_timeout=1; # 默认超时时间为50秒
    Query OK, 0 rows affected (0.01 sec)
    
    # Session A持有X Lock，Session B无法删除，避免因DELETE而导致的幻读问题
    mysql> DELETE FROM t WHERE a = 0;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> DELETE FROM t WHERE a = 10;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> DELETE FROM t WHERE a = 20;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> DELETE FROM t WHERE a = 30;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    # Session A持有Gap Lock，Session B无法插入，避免因INSERT而导致的幻读问题
    mysql> INSERT INTO t SELECT -5,-5;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 5,5;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 15,15;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 22,22;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 25,25;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 28,28;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    
    mysql> INSERT INTO t SELECT 35,35;
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> COMMIT;
    Query OK, 0 rows affected (0.00 sec)
```

#### Session A 

```sql
    mysql> SELECT * FROM t WHERE a < 25 FOR UPDATE; # 解决幻读问题
    +----+-----+
    | a  | b   |
    +----+-----+
    |  0 |   0 |
    | 10 |  10 |
    | 20 | 200 |
    +----+-----+
    3 rows in set (0.01 sec)
```

### SERIALIZABLE 

1. `SERIALIZABLE` 不存在 `脏读` 、 `不可重复读` 和 `幻读`
1. 在每个 SELECT 后自动加上 `LOCK IN SHARE MODE` ，即每个 读操作 加上一个 `S Lock` ，因此不支持 `一致性非锁定读` （仅 RC 和 RR 支持）
1. 本地事务 不使用 `SERIALIZABLE` ， `SERIALIZABLE` 主要用于 InnoDB 的 分布式事务

#### Session A 

```sql
    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b INT NOT NULL
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.04 sec)
    
    mysql> INSERT INTO t VALUES (10,10),(20,20),(30,30);
    Query OK, 3 rows affected (0.01 sec)
    Records: 3  Duplicates: 0  Warnings: 0
    
    mysql> SET SESSION TX_ISOLATION='SERIALIZABLE';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a = 10; # 自动添加LOCK IN SHARE MODE，一致性锁定读
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    +----+----+
    1 row in set (0.00 sec)
```

Session A 持有 S Lock

#### Session B 

```sql
    mysql> SET SESSION TX_ISOLATION='SERIALIZABLE';
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a = 10; # S Lock与S Lock兼容
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    +----+----+
    1 row in set (0.00 sec)
    
    mysql> SELECT * FROM t WHERE a = 10 FOR UPDATE; # S Lock与X Lock兼容，阻塞
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
```

#### Session A 

```sql
    mysql> SELECT * FROM t WHERE a < 25;
    +----+----+
    | a  | b  |
    +----+----+
    | 10 | 10 |
    | 20 | 20 |
    +----+----+
    2 rows in set (0.00 sec)
```

#### Session B 

```sql
    # 与RR类似，不存在幻读问题
    mysql> DELETE FROM t WHERE a = 10;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> DELETE FROM t WHERE a = 20;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> DELETE FROM t WHERE a = 30;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 5,5;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 15,15;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 22,22;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 28,28;
    ERROR 1205 (HY000): Lock wait timeout exceeded; try restarting transaction
    mysql> INSERT INTO t SELECT 35,35;
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
```

[1]: http://zhongmingmao.me/2017/05/22/innodb-isolation-level/

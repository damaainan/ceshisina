## MySQL Replication

来源：[https://segmentfault.com/a/1190000004602717](https://segmentfault.com/a/1190000004602717)


## 总体架构
## 内部架构

![][0]
### 连接管理

MySQL服务端采用线程池维护客户端连接
### SQL解析

分析查询语句，生成解析树，并将解析结果放入缓存中
### 优化器

优化包括选择合适的索引，数据的读取方式，分析语句执行的开销以及统计信息，优化器可以和存储引擎直接交互，尽管看起来似乎不必要。
### 执行

执行查询语句，返回结果
### 缓存

缓存SQL解析器解析后的结果
### 存储引擎
#### 锁


* 粒度


* 表级锁(table lock) : 资源消耗最少，但并发度低

* 行级锁(row lock) :  资源消耗较多，但并发度高



* 死锁
死锁的简单例子：两个transaction同时开始执行，它们分别开始执行第一条update的语句时，便锁住了对方的资源，你拿着我的资源不放，我拿着你的资源不放，最后二人都僵持着，当事人事不关己高高挂起，旁观者疾呼：死锁！

```sql
# transaction 1
START TRANSACTION;
UPDATE USER SET NAME='Bob' WHERE ID=1;
# sleep for some time;
UPDATE USER SET NAME='Jack' WHERE ID=2;
COMMIT;
```

```sql
# transaction 2
START TRANSACTION;
UPDATE USER SET NAME='Bob' WHERE ID=2;
# sleep for some time
UPDATE USER SET NAME='Jack' WHERE ID=1;
COMMIT;
```


* 显示锁定和隐式锁定
隐式锁定就是系统自动加锁而不是人为的添加锁，显示锁定就是人为的添加锁，比如lock tables或者unlock tables。


#### 事务

事务的特点由存储引擎决定，是MySQL与其它数据库的不同。
不支持事务的存储引擎有：


* MYISAM

* MEMORY

* ARCHIEVE


MySQL中的表按是否支持事务，分为：事务型表和非事务型表。
非事务型表没有commit或者rollback的概念。

```sql
SET AUTOCOMMIT=OFF;
```

或者

```sql
SET AUTOCOMMIT=0;
```

能设置当前连接是否是自动提交的。不过对非事务型表没有作用。


* ACID属性


* **`原子性`** ：事务是不可分割的最小工作单元，整个事务要么全部提交要么全部回滚失败。

* **`一致性`** ：数据库总是从一个一致性状态转换到另一个一致性的状态。

* **`隔离性`** ： 一个事务所做的更改在最终提交之前其它事务是不可见的。

* **`持久性`** ：事务一旦提交所做的修改就会永久保存在数据库中，即使系统崩溃，数据也不会丢失。



* 隔离级别


* **`未提交读（READ UNCOMMITTED）`** ：未提交读隔离级别也叫读脏，就是事务可以读取其它事务未提交的数据。

* **`提交读（READ COMMITTED）`** :在其它数据库系统比如SQL Server默认的隔离级别就是提交读，已提交读隔离级别就是在事务未提交之前所做的修改其它事务是不可见的。

* **`可重复读（REPEATABLE READ）`** :保证同一个事务中的多次相同的查询的结果是一致的，比如一个事务一开始查询了一条记录然后过了几秒钟又执行了相同的查询，保证两次查询的结果是相同的，可重复读也是mysql的默认隔离级别。

* **`可串行化（SERIALIZABLE）`** :可串行化就是保证读取的范围内没有新的数据插入，比如事务第一次查询得到某个范围的数据，第二次查询也同样得到了相同范围的数据，中间没有新的数据插入到该范围中。
查询和设置隔离级别：

```sql
    # 查询系统默认隔离级别，当前会话隔离级别
    select @@global.tx_isolation,@@tx_isolation;
    # 设置系统隔离级别：
    SET global transaction isolation level read committed;
    # 设置会话隔离级别：
    SET SESSION transaction isolation LEVEL read committed;
```


## Replication概念

复制意味着一份数据可以有多个副本，一个数据库中的数据，可以复制至另一个数据库。

![][1] 
复制在我们的生活中无处不在，同一份数据，有可能在你个人的电脑上有一份，U盘上有一份，云端的网盘上也可能会有一份。甚至在个人电脑里，同样一份数据也会有多个副本。
这里，我们之所以会将数据复制出多份，目的是显而易见的： **`备份`** 。
当你抱着自己的电脑准备接上投影仪，准备向老板展示你苦战数个通宵后的PPT时，硬盘突然坏了，或者文件误删，莫名其妙的找不到了，没关系，你的U盘还有一份。什么！U盘忘了带了？没关系，你的网盘里还有一个。
 **`复制`** 与 **`备份`** 的是两件不同的事情，可以通过复制来实现备份的目的。但是，复制的却不只是能提供备份而已。
## 复制解决了什么问题

在MySQL中，复制可以解决几个问题：


* 数据分布

* 读写分离

* 备份

* 高可用和故障切换


如果你们公司的数据中心位于全国各地，通过复制，可以实现异地备份，但异地的数据同步会有较大的时间延迟。
同时，如果主数据库的数据都同步复制至从库，那么当需要更新数据时，只需要更新主库即可，新更新的数据将会通过主库同步至从库，在这个基础上，便可以实现 **`读写分离`** ，即DML语句在主库上执行，而查询类SQL语句则在从库上执行，由于主从的同步有时延，因此这里的数据一致性模型并不满足强一致性，是最终一致性模型。
因为有了主从副本，所以当主库不可用(宕机，崩溃等原因)，从库可以临危受命，升级为主库，保证数据库服务的高可用性。
## MySQL间如何复制

分为3个步骤


* 在主库上把数据更改记录到二进制(binary log)日志中。

* 从库把主库上的日志复制到自己的中继日志(relay log)中。

* 从库读取中继日志中的事件，将其重放在从库数据中。


![][2]

主库在每次事务准备提交前，按照事务的提交顺序，将更新事件记录到binary log中，并通知存储引擎提交事务。然后，从库会主动启动一个线程与主库建立连接，与此对应，主库会启动一个二进制转储(binlog dump)线程与之合作，将binary log发送给从库，从库接收并产生relay log，然后由从库的一个SQL线程负责将relay log还原为数据。

需要注意的是，从库在relay时，是单线程执行，换言之，串行执行的。
## Replication配置
## 复制的配置步骤


* 创建复制账号

* 配置主库和从库

* 通知从库开始进行复制


## 基于二进制日志的主从复制
### 配置master

通过在my.cnf中添加一个配置项 **`log-bin`** ，来起用master的二进制日志记录功能，如果没有配置log-bin，那么master的二进制记录功能并不会起用，log-bin在这里有两个作用


* 起用master的日志记录功能

* 指定日志文件名的前缀


另外，需要在my.cnf中添加一个server-id配置项，用来指定master的唯一ID，范围可以是1到2^32-1。

```cfg
[mysqld]
# Replication Configurations(by beanlam)
server-id=1
log-bin=bin-log
```

配置完成后需要重启mysql server。
### 配置slave

同样，slave也需要配置一个唯一的server-id，不允许跟master的server-id冲突，如果有多个slave，各个slave与master的server-id都应该是不同的。
slave也可以配置log-bin，配置了以后slave也可以和master一样，记录binary log。slave记录binary log有其用武之地的，比如数据备份和崩溃恢复，若当前的replication环境拓扑结构比较复杂，slave需要作为其它mysql server的master时，那么这个slave也必须启用binary log的功能。

```cfg
[mysqld]
# Replication Configurations(by beanlam)
server-id=2
```
### 创建一个账号用于复制

slave获取master的binary log时，通过用户名和密码与master建立连接，可以创建一个专用于复制的账号，并只赋予这个账号与复制有关的权限。

```sql
mysql> CREATE USER 'repl'@'%.mydomain.com' IDENTIFIED BY 'slavepass';
mysql> GRANT REPLICATION SLAVE ON *.* TO 'repl'@'%.mydomain.com';
```

除了REPLICATION SLAVE权限，还可以给用户授权REPLICATION CLIENT权限，这样用户就可以用来监控和管理复制。
### 获取master二进制文件的坐标

slave必须事先了解从master的二进制文件的哪个位置开始复制，因此需要先记录master当前二进制日志的坐标，坐标由文件名和偏移量决定。
获得master的二进制日志坐标，需要先保证没有写操作在进行。在master上执行以下语句:

```sql
mysql> FLUSH TABLES WITH READ LOCK;
```

这个语句能为表获得读锁，阻止其它写入操作。需要注意，执行这个语句的会话如果关闭，那么这个锁将会被释放，如果会话没有关闭，那么锁会一直持有。

在另一个会话里，用以下语句来查看master的二进制日志坐标

```sql
mysql> show master status;
+----------------+----------+-------------------------+------------------+-------------------+
| File           | Position | Binlog_Do_DB            | Binlog_Ignore_DB | Executed_Gtid_Set |
+----------------+----------+-------------------------+------------------+-------------------+
| bin-log.000005 |     1264 | beanlam_db1,beanlam_db2 |                  |                   |
+----------------+----------+-------------------------+------------------+-------------------+
1 row in set (0.00 sec)
```

File和Position即表明了slave应该从何处开始进行复制。

如果master之前没有启用过二进制日志的功能，那么`show master status`查询结果将为空。这时对于slave来说，File是一个空字符串，Position是4，之所以是4，与二进制日志的文件格式有关。
### slave开始复制

根据以上步骤，得到了master二进制文件的坐标后，只需要告诉slave，slave便可以埋头进入复制的状态中。

这里根据master是否有旧数据需要同步，分为两种情况：


* 如果master是一个新的数据库服务器，其上没有任何旧的数据需要复制，那么就可以使用`change master to`命令为slave配置master的信息。

```sql
CHANGE MASTER TO
MASTER_HOST='master_host_name',
MASTER_USER='replication_user_name',
MASTER_PASSWORD='replication_password',
MASTER_LOG_FILE='recorded_log_file_name',
MASTER_LOG_POS=recorded_log_position;
```

配置完后，可以通过`start slave`命令正式开始进行复制。


* 如果master在启用二进制日志功能之前，已经存在有一部分数据，这一部分数据需要在复制开始之前，先同步至slave。
可以通过mysqldump工具对当前master的数据库数据做一个快照，生成一个dump文件，在slave开始复制之前，把这个文件的数据导入slave中。


基本的使用方法：

```sql
mysqldump --all-databases --master-data > dbdump.db
```
`-all-databases`表明为所有数据库作快照，也可以用`--databases`来制定需要做快照的数据库。
`--ignore-table`可以跳过数据库中的所有表
`--master-data`会自动地在dump文件里加上`change master to`语句，启动slave复制，如果不加这个选项，则需要先开启一个新的会话，对所有的表加读。
当存储引擎是InnoDB时，推荐使用mysqldump。
另外一种方法是直接拷贝数据文件到slave。
## Replication原理
## 基于语句的复制

基于语句的复制也称为(逻辑复制)，slave把master上造成数据更改的SQL语句在自己的库上也执行一次。


* 优点
master的binlog只记录SQL语句，使得日志文件体积更小。

* 缺点


master除了传输SQL语句给slave，还需要传输一些元数据，比如当前时间戳。
还有一些语句无法被正确复制，比如包含用户自定义函数的语句，这些函数可能有不确定的行为。以下函数可能导致非正常的复制：

```sql
LOAD_FILE(), UUID(), UUID_SHORT(), USER(), FOUND_ROWS(), SYSDATE(), GET_LOCK(), IS_FREE_LOCK(), IS_USED_LOCK, MASTER_POS_WAIT, RAND(), RELEASE_LOCK(), SLEEP(), VERSION()
```

此外，`INSERT......SELECT`语句需要获取更多的行级锁，比起基于行的复制来说。
`UPDATE`语句可能导致全表扫描(where字句中没有包含索引字段)
如果使用的是InnoDB引擎，带有`auto_increment`的`insert`语句会堵塞其它非冲突的`insert`语句。
slave上的更新是串行的，因此需要更多的锁。另外，并不是所有的存储引擎都支持基于语句的复制。
## 基于行的复制

MySQL5.1开始支持基于行的复制


* 优点
更少的锁

* 缺点
对于某些语句，例如插入或者删除语句，基于行的复制方式会将整行的数据都写进binary log，导致binary log体积很大，也导致需要持有锁的时间变长。


如果包含用户自定义函数，这些函数输出值非常大的文本，那么采取行的复制，会把这么大的文本也写进日志里。
在slave端看不到执行了哪些SQL语句
当使用MyISAM引擎时，`insert`语句需要获得重量级的锁，这意味着插入操作只能是串行的。
## slave也作为master

如果slave配置了log_slave_updates选项，slave也会像master一样记录binary log，从而可以作为一个master存在。

![][3]
## 拓扑结构


* 最常见的拓扑结构

![][4]

* MySQL不支持多主库复制

![][5]

* 主动-被动模式下的主-主复制
被动服务器时 **`只读的`** (日志里记录的事件都带有一个server id，发现server id与自己的相同，则忽略这个事件)


![][6]


* 拥有备库的主-主结构

![][7]

* 环形结构
非常脆弱，其中一个节点失效会导致由这个节点发起的事件在其它节点之间链式死循环，因为只有它自己能过滤掉与自己server id相同的事件。


![][8] 
改进：

![][9]

* 主库-分发库-备库
主要用在当多个备库执行复制请求时，导致主库负载过高时，可以引进分发库来减少主库的负载。


![][10]

* 树形或金字塔形
故障处理过程更加复杂


![][11]
## Reference

[mysql5.6 ref][12]
[mysql5.7 ref][13]
《高性能MySQL》 3rd Edition

[12]: http://dev.mysql.com/doc/refman/5.6/en/
[13]: http://dev.mysql.com/doc/refman/5.7/en/
[0]:./img/bVttuN.png
[1]:./img/bVttwa.png
[2]:./img/bVttwi.png
[3]:./img/bVttwY.png
[4]:./img/bVttw2.png
[5]:./img/bVttw7.png
[6]:./img/bVttw8.png
[7]:./img/bVttxc.png
[8]:./img/bVttxf.png
[9]:./img/bVttxn.png
[10]:./img/bVttxo.png
[11]:./img/bVttxp.png
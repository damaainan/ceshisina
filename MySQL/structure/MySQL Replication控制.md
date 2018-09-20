## MySQL Replication控制

来源：[https://segmentfault.com/a/1190000004602829](https://segmentfault.com/a/1190000004602829)


## 控制Master
### 1. SHOW相关

```sql
show binary logs;/*相当于*/show master logs;
show binlog events;
show master status;
show slave hosts;
```

![][0] 

![][1] 

![][2]
### 2. binary logs清除

语法：

```sql
PURGE { BINARY | MASTER } LOGS { TO 'log_name' | BEFORE datetime_expr }
```

log_name或者datetime_expr之前的日志文件将会被删除，此处binary和master是同义词。
datetime_expr日期格式必须为'YYYY-MM-DD hh:mm:ss'。
例子:

```sql
PURGE BINARY LOGS TO 'mysql-bin.010';
PURGE BINARY LOGS BEFORE '2008-04-02 22:46:26';
```

当slave正在从master上复制时，上述的语句的执行也是安全的，如果要删除的文件正在被slave读取，那么这个文件将不会被删除。
如果删除了slave还未复制的日志文件，那么slave无法同步这部分被删除的数据。

当要清楚binary logs时，以下步骤是最佳实践：


* 在每个slave上用`show slave status`查看正在同步哪个binary log。

* 在master上执行`show binary logs`查看日志文件列表

* 在列表中，找出所有slave正在同步的binary log中那个最靠前的，准备删除这个log之前的logs。

* 为即将被删除的Logs作备份(非必须，但建议这么做)

* 最后用`purge`命令清除日志。


purge命令是根据.index文件里所列的日志文件来进行删除的( .index的前缀就是binlog的名字，这个文件是由mysqld维护的 )，如果一个日志文件在操作系统中不存在(例如被人为地通过`rm`命令删除)，而.index文件里又记录了这个日志文件，那么purge命令会报错。
### 3. 重置Master

语法：

```sql
reset master;
```

这个命令会删除.index文件里所列举的所有binary logs，清空.index文件并产生一个新的空的binary log文件。

这个命令还会重置`gtid_purged`和` gtid_executed`这两个系统变量为空字符串(会话范围内的变量值不会被重置)，并且从MySQL5.7.5开始，这个命令还会清空`mysql.gtid_executed`表。

reset命令和purge命令的区别
当有slave正在进行数据同步时，不应该使用(也不支持)reset master命令，可能带来未知的后果，不过purge命令则可以在slave运行时安全地使用。
当在测试环境中，需要经常初始化设置master和slave时，使用`reset master`的好处多多，可以按以下步骤初始master-slave：


* 分别启动master和slave，并启动replication

* 在master上执行一些语句

* 检查master的更新是否同步到slave

* 如果slave正确同步了master的数据，在slave上执行`stop slave`命令，然后执行`reset slave`，并确认数据是否删除了数据。

* 在master上执行`reset master`命令


通过以上步骤，在测试的时候，就可以清空master的binary log，并且重新开始replication的测试。
### 4. 暂停replication

命令：

```sql
set sql_log_bin=0|1;
```

0, 不同步至slave
1,  同步至slave

可以在session内进行设置，也可以在全局范围内设置(设置后新建的session会有影响，但已经存在的session不会受影响)
## 控制Slave
### 1. SHOW相关

```sql
show slave status;
show relaylog events;
```
### 2. 指向Master

语法：

```sql
CHANGE MASTER TO option [, option] ... [ channel_option ]
option:
MASTER_BIND = 'interface_name'
| MASTER_HOST = 'host_name'
| MASTER_USER = 'user_name'
| MASTER_PASSWORD = 'password'
| MASTER_PORT = port_num
| MASTER_CONNECT_RETRY = interval
| MASTER_RETRY_COUNT = count
| MASTER_DELAY = interval
| MASTER_HEARTBEAT_PERIOD = interval
| MASTER_LOG_FILE = 'master_log_name'
| MASTER_LOG_POS = master_log_pos
| MASTER_AUTO_POSITION = {0|1}
| RELAY_LOG_FILE = 'relay_log_name'
| RELAY_LOG_POS = relay_log_pos
| MASTER_SSL = {0|1}
| MASTER_SSL_CA = 'ca_file_name'
| MASTER_SSL_CAPATH = 'ca_directory_name'
| MASTER_SSL_CERT = 'cert_file_name'
| MASTER_SSL_CRL = 'crl_file_name'
| MASTER_SSL_CRLPATH = 'crl_directory_name'
| MASTER_SSL_KEY = 'key_file_name'
| MASTER_SSL_CIPHER = 'cipher_list'
| MASTER_SSL_VERIFY_SERVER_CERT = {0|1}
| MASTER_TLS_VERSION = 'protocol_list'
| IGNORE_SERVER_IDS = (server_id_list)

channel_option:
    FOR CHANNEL channel

server_id_list:
    [server_id [, server_id] ... ]
```

CHANGE MASTER TO用来重新设置slave，使其指向新的master，或者仅仅是改变一些上面提到的option。这个命令在MySQL5.7.4及其以上版本增加了许多特性，例如channel的概念等等。待补充。
### 3. replication过滤

CHANGE REPLICATION FILTER 语法

```sql
CHANGE REPLICATION FILTER filter[, filter][, ...]
filter:
REPLICATE_DO_DB = (db_list)
| REPLICATE_IGNORE_DB = (db_list)
| REPLICATE_DO_TABLE = (tbl_list)
| REPLICATE_IGNORE_TABLE = (tbl_list)
| REPLICATE_WILD_DO_TABLE = (wild_tbl_list)
| REPLICATE_WILD_IGNORE_TABLE = (wild_tbl_list)
| REPLICATE_REWRITE_DB = (db_pair_list)
db_list:
db_name[, db_name][, ...]
tbl_list:
db_name.table_name[, db_table_name][, ...]
wild_tbl_list:
'db_pattern.table_pattern'[, 'db_pattern.table_pattern'][, ...]
db_pair_list:
(db_pair)[, (db_pair)][, ...]
db_pair:
from_db, to_db
```

从MySQL5.7.3开始，`CHANGE REPLICATION FILTER`命令用来为slave设置一个或多个复制过滤规则，比如说`--
replicate-do-db`或者 `--replicate-wild-ignore-table`，这些选项不像服务器选项，重置后还需要重启mysql才生效，这些选项可以动态修改，只需要先停止slave的SQL线程，设置后，再重启SQL线程(`start|stop slave sql_thread`)。

各个选项的作用，待补充。
### 4. MASTER_POS_WAIT()

确切来说，这个一个函数，而非SQL语句 。

```sql
SELECT MASTER_POS_WAIT('master_log_file', master_log_pos [, timeout][, channel])
```

这里的file和pos对应主库show master status得到的值，代表执行位置。 函数逻辑是等待当前从库达到这个位置后返回, 返回期间执行的事务个数。
参数timeout可选，若缺省则无限等待，timeout<=0时与缺省的逻辑相同。若为正数，则等待这么多秒，超时函数返回-1.
其他返回值：若当前slave为启动或在等待期间被终止，返回NULL； 若指定的值已经在之前达到，返回0。
### 5. 重置slave

语法

```sql
RESET SLAVE [ALL] [channel_option]
channel_option:
FOR CHANNEL channel
```

这个命令清除了slave保存的关于master和relay log的信息，删除所有的relay log。使用这个命令，slave的replication线程必须先停下来。

这个命令还有影响到channel，待补充。
`reset slave`不会改变slave与master的连接信息，比如master的ip地址，端口，用户名和密码等，`reset slave all`将会重置连接的信息，因此这意味着需要重启slave的mysqld进程。
### 6. 跳过执行

```sql
SET GLOBAL sql_slave_skip_counter = N
```
### 7. start slave

语法:

```sql
START SLAVE [thread_types] [until_option] [connection_options] [channel_option]

thread_types:
[thread_type [, thread_type] ... ]
    thread_type:
    IO_THREAD | SQL_THREAD
until_option:
    UNTIL { {SQL_BEFORE_GTIDS | SQL_AFTER_GTIDS} = gtid_set
    | MASTER_LOG_FILE = 'log_name', MASTER_LOG_POS = log_pos
    | RELAY_LOG_FILE = 'log_name', RELAY_LOG_POS = log_pos
    | SQL_AFTER_MTS_GAPS }
    
connection_options:
[USER='user_name'] [PASSWORD='user_pass'] [DEFAULT_AUTH='plugin_name'] [PLUGIN_DIR='plugin_dir']

channel_option:
    FOR CHANNEL channel
gtid_set:
uuid_set [, uuid_set] ...
| ''
uuid_set:
    uuid:interval[:interval]...
uuid:
    hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
h:
    [0-9,A-F]
interval:
    n[-n]
    (n >= 1)
```

不指定thread_type时，IO线程和SQL线程都会被启动，IO线程从master读取日志，SQL线程读取relay log并执行。
### 8. stop slave

语法

```sql
STOP SLAVE [thread_types]

thread_types:
    [thread_type [, thread_type] ... ]
        
thread_type: IO_THREAD | SQL_THREAD

channel_option:
    FOR CHANNEL channel
```

当停止slave服务器时，应先执行`stop slave`停止slave功能。

执行这个命令时，还需要考虑是基于行的replication还是基于语句的replication，存储引擎，以及事务型表和非事务型表。
## 控制Group复制

```sql
start group_replication;
stop group_replication;
```
## Reference

[mysql ref5.6][3]
[mysql ref5.7][4]

[3]: http://dev.mysql.com/doc/refman/5.6/en/
[4]: http://dev.mysql.com/doc/refman/5.7/en/
[0]:./img/bVttyZ
[1]:./img/bVtty0
[2]:./img/bVtty6
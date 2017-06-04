## [MySQL备份与还原详细过程示例][0] Posted on 2017-02-28 21:43 [wajika][1] 阅读( 7 ) 评论( 0 ) [编辑][2][收藏][3]

**MySQL备份与还原详细过程示例**

**一、MySQL备份类型**

1.热备份、温备份、冷备份 （根据服务器状态）

热备份：读、写不受影响；

温备份：仅可以执行读操作；

冷备份：离线备份；读、写操作均中止；

2.物理备份与逻辑备份 （从对象来分）

物理备份：复制数据文件；

逻辑备份：将数据导出至文本文件中；

3.完全备份、增量备份、差异备份 （从数据收集来分）

完全备份：备份全部数据；

增量备份：仅备份上次完全备份或增量备份以后变化的数据；

差异备份：仅备份上次完全备份以来变化的数据；

4.逻辑备份的优点：

在备份速度上两种备份要取决于不同的存储引擎

物理备份的还原速度非常快。但是物理备份的最小力度只能做到表

逻辑备份保存的结构通常都是纯ASCII的，所以我们可以使用文本处理工具来处理

逻辑备份有非常强的兼容性，而物理备份则对版本要求非常高

逻辑备份也对保持数据的安全性有保证

5.逻辑备份的缺点：

逻辑备份要对RDBMS产生额外的压力，而裸备份无压力

逻辑备份的结果可能要比源文件更大。所以很多人都对备份的内容进行压缩

逻辑备份可能会丢失浮点数的精度信息

**注:差异备份要比增量备份占用的空间大，但恢复时比较方便！但我们一般都用增量备份！**

**二、MySQL备份都备份什么**

一般备份以下几个部分：

1.数据文件

2.日志文件（比如事务日志，二进制日志）

3.存储过程，存储函数，触发器

4.配置文件（十分重要，各个配置文件都要备份）

5.用于实现数据库备份的脚本，数据库自身清理的Croutab等……

三、MySQL常用的备份工具

1.Mysql自带的备份工具

(1)mysqldump 逻辑备份工具，支持所有引擎，MyISAM引擎是温备，InnoDB引擎是热备，备份速度中速，还原速度非常非常慢，但是在实现还原的时候，具有很大的操作余地。具有很好的弹性。

mysqlhotcopy 物理备份工具，但只支持MyISAM引擎，基本上属于冷备的范畴，物理备份，速度比较快。

2.文件系统备份工具

(1)cp冷备份，支持所有引擎，复制命令，只能实现冷备，物理备份。使用归档工具，cp命令，对其进行备份的，备份速度快，还原速度几乎最快，但是灵活度很低，可以跨系统，但是跨平台能力很差。

(2)lvm 几乎是热备份，支持所有引擎，基于快照(LVM，ZFS)的物理备份，速度非常快，几乎是热备。只影响数据几秒钟而已。但是创建快照的过程本身就影响到了数据库在线的使用，所以备份速度比较快，恢复速度比较快，没有什么弹性空间，而且LVM的限制：不能对多个逻辑卷同一时间进行备份，所以数据文件和事务日志等各种文件必须放在同一个LVM上。而ZFS则非常好的可以在多逻辑卷之间备份。

3.其它工具

(1)ibbackup 商业工具 MyISAM是温备份，InnoDB是热备份 ，备份和还原速度都很快，这个软件它的每服务器授权版本是5000美元。

(2)xtrabackup 开源工具 MyISAM是温备份，InnoDB是热备份 ，是ibbackup商业工具的替代工具。

**四、MySQL备份策略**

1.直接拷贝数据库文件（文件系统备份工具 cp）（适合小型数据库，是最可靠的）

当你使用直接备份方法时，必须保证表不在被使用。如果服务器在你正在拷贝一个表时改变它，拷贝就失去意义。保证你的拷贝完整性的最好方法是关闭服务器，拷贝文件，然后重启服务器。如果你不想关闭服务器，要在执行表检查的同时锁定服务器。如果服务器在运行，相同的制约也适用于拷贝文件，而且你应该使用相同的锁定协议让服务器“安静下来”。当你完成了备份时，需要重启服务器(如果关闭了它)或释放加在表上的锁定(如果你让服务器运行)。要用直接拷贝文件把一个数据库从一台机器拷贝到另一台机器上，只是将文件拷贝到另一台服务器主机的适当数据目录下即可。要确保文件是MyIASM格式或两台机器有相同的硬件结构，否则你的数据库在另一台主机上有奇怪的内容。你也应该保证在另一台机器上的服务器在你正在安装数据库表时不访问它们。

2.mysqldump备份数据库（完全备份+增加备份，速度相对较慢，适合中小型数据库）（MyISAM是温备份，InnoDB是热备份）

mysqldump 是采用SQL级别的备份机制，它将数据表导成 SQL 脚本文件，在不同的 MySQL 版本之间升级时相对比较合适，这也是最常用的备份方法。mysqldump 比直接拷贝要慢些。对于中等级别业务量的系统来说，备份策略可以这么定：第一次完全备份，每天一次增量备份，每周再做一次完全备份，如此一直重复。而对于重要的且繁忙的系统来说，则可能需要每天一次全量备份，每小时一次增量备份，甚至更频繁。为了不影响线上业务，实现在线备份，并且能增量备份，最好的办法就是采用主从复制机制(replication)，在 slave 机器上做备份。

3.lvs快照从物理角度实现几乎热备的完全备份，配合二进制日志备份实现增量备份，速度快适合比较烦忙的数据库

前提：

数据文件要在逻辑卷上；

此逻辑卷所在卷组必须有足够空间使用快照卷；

数据文件和事务日志要在同一个逻辑卷上；

示例步骤：

1).打开会话，施加读锁，锁定所有表；

mysql> FLUSH TABLES WITH READ LOCK;

mysql> FLUSH LOGS;

2).通过另一个终端，保存二进制日志文件及相关位置信息；

mysql -uroot -p -e 'SHOW MASTER STATUS\G' > /path/to/master.info

3).创建快照卷

lvcreate -L # -s -p r -n LV_NAME /path/to/source_lv

4).释放锁

mysql> UNLOCK TABLES;

5).挂载快照卷，备份

mount

cp

(6).删除快照卷；

或者用现成的集成命令工具mylvmbackup(可以集成上面的命令集合，自动完成备份)

mylvmbackup --user=dba --password=xxx --mycnf=/etc/my.cnf --vgname=testvg --lvname=testlv --backuptype=tar --lvsize=100M --backupdir=/var/lib/backup

4.xtrabackup 备份数据库，实现完全热备份与增量热备份（MyISAM是温备份，InnoDB是热备份），由于有的数据在设计之初，数据目录没有存放在LVM上，所以不能用LVM作备份，则用xtrabackup代替来备份数据库

**说明：Xtrabackup是一个对InnoDB做数据备份的工具，支持在线热备份(备份时不影响数据读写)，是商业备份工具InnoDB Hotbackup或ibbackup的一个很好的替代品。**

Xtrabackup有两个主要的工具：xtrabackup、innobackupex:

xtrabackup 只能备份InnoDB和XtraDB两种数据表，而不能备份MyISAM数据表。

innobackupex 是参考了InnoDB Hotbackup的innoback脚本修改而来的.innobackupex是一个perl脚本封装，封装了xtrabackup。主要是为了方便的同时备份InnoDB和MyISAM引擎的表，但在处理myisam时需要加一个读锁。并且加入了一些使用的选项。如slave-info可以记录备份恢复后作为slave需要的一些信息，根据这些信息，可以很方便的利用备份来重做slave。

特点：备份过程快速、可靠;备份过程不会打断正在执行的事务；能够基于压缩等功能节约磁盘空间和流量；自动实现备份检验；还原速度快。

5.主从复制（replication）实现数据库实时备份（集群中常用）

6.总结

单机备份是完全备份（所有数据库文件）+增量备份（备份二进制日志）相结合！

集群中备份是完全备份（所有数据库文件）+增量备份（备份二进制日志）+主从复制（replication）相结合的方法！

数据会完整备份到/root/mybackup/xtrabackup/中目录名字为当前的日期，xtrabackup会备份所有的InnoDB表，MyISAM表只是复制表结构文件、以及MyISAM、MERGE、CSV和ARCHIVE表的相关文件，同时还会备份触发器和数据库配置信息相关的文件。除了保存数据外还生成了一些xtrabackup需要的数据文件，详解如下：

xtrabackup_checkpoints 备份类型（如完全或增量）、备份状态（如是否已经为prepared状态）和LSN(日志序列号)范围信息；每个InnoDB页(通常为16k大小)都会包含一个日志序列号，即LSN。LSN是整个数据库系统的系统版本号，每个页面相关的LSN能够表明此页面最近是如何发生改变的。

xtrabackup_binlog_info mysql服务器当前正在使用的二进制日志文件及至备份这一刻为止二进制日志事件的位置。

xtrabackup_binary 备份中用到的xtrabackup的可执行文件。

backup-my.cnf 备份命令用到的配置选项信息。

xtrabackup_logfile 记录标准输出信息xtrabackup_logfile

注:如果在增量备份后数据库出现故障，我们需要通过完整备份+到现在为止的所有增量备份+最后一次增量备份到现在的二进制日志来恢复。

示例:

安装percona-xtrabackup

解决依赖关系:yum -y install perl perl-devel libaio libaio-devel perl-Time-HiRes perl-DBD-MySQL rsync perl-Digest-MD5 libev

下载percona-xtrabackup:wget https://www.percona.com/downloads/XtraBackup/Percona-XtraBackup-2.4.6/binary/redhat/7/x86_64/percona-xtrabackup-24-2.4.6-1.el7.x86_64.rpm

安装percona-xtrabackup:rpm -ivh percona-xtrabackup-24-2.4.6-1.el7.x86_64.rpm

可以创建一个最小权限的用户进行备份

1

2

3

4

mysql>CREATEUSER’bkpuser’@’localhost’ IDENTIFIEDBY’111111’;mysql>REVOKEALLPRIVILEGES,GRANTOPTIONFROM’bkpuser’;mysql>GRANTRELOAD, LOCK TABLES, REPLICATION CLIENTON*.*TO’bkpuser’@’localhost’;mysql> FLUSHPRIVILEGES; 单独备份：

1

#innobackupex --user=root --password=111111 --host=192.168.2.5 --defaults-file=/etc/my.cnf --databases=test /root/mybackup 备份并打包压缩：

1

#innobackupex --user=root --password=111111 --host=192.168.2.5 --defaults-file=/etc/my.cnf --databases=test --stream=tar /root/mybackup/ | gzip > /root/mybackup/testdb.tar.gz 带时间戳：

1

innobackupex --user=root --password=111111 --host=192.168.2.5 --defaults-file=/etc/my.cnf --databases=test--stream=tar/root/mybackup/|gzip>/root/mybackup/`date+%F`_testdb.tar.gz 备份信息输出重定向到文件：

1

innobackupex --user=root --password=111111 --host=192.168.2.5 --defaults-file=/etc/my.cnf --databases=test--stream=tar/root/mybackup/2>/root/mybackup/test.log |gzip1>/root/mybackup/test.tar.gz**说明：**

**--stream #指定流的格式，目前只支持tar**

**--database=test #单独对test数据库做备份 ，若是不添加此参数那就那就是对全库做**

**2>/root/mybackup/test.log #输出信息写入日志中**

**1>/root/mybackup/test.tar.gz #打包压缩存储到该文件中**

**解压 tar -izxvf 要加-i参数，官方解释 innobackupex : You must use -i (--ignore-zeros) option for extraction of the tar stream.**

**在备份完成后，数据尚且不能用于恢复操作，因为备份的数据中可能会包含尚未提交的事务或已经提交但尚未同步至数据文件中的事务。**

**此时数据文件仍处理不一致状态。“准备”的主要作用正是通过回滚未提交的事务及同步已经提交的事务至数据文件也使得数据文件处于一致性状态。**

五、xtrabackup工具备份与恢复实战

1.xtrabackup工具数据库完全备份与恢复

(1)查看当前测试数据库test，表tb02,准备备份目录为/root/mybackup

1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

mysql> USE test;Databasechangedmysql> SHOW TABLES;+----------------+| Tables_in_test |+----------------+| tb02 |+----------------+1 rowinset(0.00 sec)mysql>SELECT*FROMtb02;+---+---+| a | b |+---+---+| 1 | 2 |+---+---+1 rowinset(0.00 sec) (2)对test库进行全备份

1

2

3

4

5

6

7

8

9

10

11

12

13

# innobackupex --user=root --password=111111 --host=192.168.2.5 --default-file=/etc/my.cnf --include=test /root/mybackup/170227 11:11:01 innobackupex: Starting the backup operationIMPORTANT: Please check that the backup run completes successfully.At the end of a successful backup run innobackupexprints"completed OK!".......(此处省略内容)170227 11:11:06 Backup createdindirectory'/root/mybackup/2017-02-27_11-11-01/'170227 11:11:06 [00] Writing backup-my.cnf170227 11:11:06 [00] ...done170227 11:11:06 [00] Writing xtrabackup_info170227 11:11:06 [00] ...donextrabackup: Transaction log of lsn (30571253363) to (30571253372) was copied.170227 11:11:07 completed OK!#test库备份成功 (3)模拟数据被破坏(test数据库被删)

1

2

mysql>dropdatabasetest;Query OK, 1 row affected (0.01 sec)1

2

3

4

# ls /mydata/data/ #存放数据目录下已经没有test目录了auto.cnf ibdata1 ibtmp1 node03.err sysddl_log.log ib_logfile0 mysql node03.pidib_buffer_pool ib_logfile1 mysqld_safe.pid performance_schema (4)从test全备中恢复test数据库

**注:使用innobackupex的--apply-log和--export选项**

先暂停mysql服务:systemctl stop mysqld.service

1

2

3

4

5

6

7

8

9

10

11

12

13

# innobackupex --user=root --password=111111 --host=192.168.2.5 --default-file=/etc/my.cnf --apply-log --export /root/mybackup/2017-02-27_11-11-01/170227 11:14:43 innobackupex: Starting the apply-log operationIMPORTANT: Please check that the apply-log run completes successfully.At the end of a successful apply-log run innobackupexprints"completed OK!".......(此处省略内容)InnoDB: 32 non-redo rollback segment(s) are active.InnoDB: 5.7.13 started; log sequence number 30571253781xtrabackup: startingshutdownwith innodb_fast_shutdown = 0InnoDB: FTS optimize thread exiting.InnoDB: Startingshutdown...InnoDB: Shutdown completed; log sequence number 30571253800170227 11:14:48 completed OK! 拷贝备份文件拷贝回数据目录/mydata/data

1

2

3

4

5

#cp -rf /root/mybackup/2017-02-27_11-11-01/* /mydata/data/# ls /mydata/data/ #存放数据目录下test目录已经恢复auto.cnf ibdata1 ibtmp1 node03.err sysddl_log.log ib_logfile0 mysql node03.pidtestib_buffer_pool ib_logfile1 mysqld_safe.pid performance_schema 授权目录权限

1

# chown -R mysql:mysql /mydata/data/ 启动mysql服务

1

#systemctl start mysql.service 检查还原后的数据库以及表是否正常

1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

17

18

19

20

21

22

mysql> SHOW DATABASES;+--------------------+|Database|+--------------------+| information_schema || mysql || performance_schema || sys || test |+--------------------+5rowsinset(0.00 sec)mysql> USE test;ReadingtableinformationforcompletionoftableandcolumnnamesYou can turnoffthis featuretoget a quicker startupwith-ADatabasechangedmysql>select*fromtb02;+---+---+| a | b |+---+---+| 1 | 2 |+---+---+1 rowinset(0.00 sec) 2.示例1是对单个库的全备份以及还原，实际生产环境中存在所有库的备份还原。

(1)所有库备份(innobackupex后不带--include或--databases参数即表示全备所有库)

1

2

3

4

5

# innobackupex --user=root --password=111111 --host=192.168.2.5 /root/mybackup/170227 11:52:46 innobackupex: Starting the backup operationIMPORTANT: Please check that the backup run completes successfully.At the end of a successful backup run innobackupexprints"completed OK!".1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

17

Unrecognized character \x01; marked by <-- HERE after <-- HERE near column 1 at - line 1374.170227 11:52:46 Connecting to MySQL server host: 192.168.2.5, user: root, password:set, port: notset, socket: notsetUsing server version 5.7.17innobackupex version 2.4.6 based on MySQL server 5.7.13 Linux (x86_64) (revisionid: 54967d1)xtrabackup: uses posix_fadvise().......(此处省略内容)170227 11:52:51 Executing UNLOCK TABLES170227 11:52:51 All tables unlocked170227 11:52:51 [00] Copying ib_buffer_pool to/root/mybackup/2017-02-27_11-52-46/ib_buffer_pool170227 11:52:51 [00] ...done170227 11:52:51 Backup createdindirectory'/root/mybackup/2017-02-27_11-52-46/'170227 11:52:51 [00] Writing backup-my.cnf170227 11:52:51 [00] ...done170227 11:52:51 [00] Writing xtrabackup_info170227 11:52:51 [00] ...donextrabackup: Transaction log of lsn (30571256358) to (30571256367) was copied.170227 11:52:51 completed OK! (2)模拟数据被破坏(/mydata/data/目录下数据库文件被删除)

1

# rm -rf /mydata/data/* 数据全部删除 (3)从全备中恢复数据

提交未提交的数据,使用--apply-log 参数

1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

# innobackupex --user=root --password=111111 --host=192.168.2.5 --apply-log /root/mybackup/2017-02-27_11-52-46/170227 14:03:31 innobackupex: Starting the apply-log operationIMPORTANT: Please check that the apply-log run completes successfully.At the end of a successful apply-log run innobackupexprints"completed OK!".innobackupex version 2.4.6 based on MySQL server 5.7.13 Linux (x86_64) (revisionid: 54967d1)xtrabackup:cdto/root/mybackup/2017-02-27_11-52-46/xtrabackup: This target seems to be not prepared yet.InnoDB: Number of pools: 1xtrabackup: xtrabackup_logfile detected: size=8388608, start_lsn=(30571256358)......xtrabackup: startingshutdownwith innodb_fast_shutdown = 1InnoDB: FTS optimize thread exiting.InnoDB: Startingshutdown...InnoDB: Shutdown completed; log sequence number 30571256872170227 14:03:36 completed OK! 拷贝全备文件至/mydata/data/，并修改文件属主和属组，启动mysql服务

1

2

3

# cp -rf /root/mybackup/2017-02-27_11-52-46/* /mydata/data/# chown -R mysql:mysql /mydata/data/# systemctl start mysqld.service 3.实际生产环境中，常用的备份策略，如上面提到的每周一个全备份，每天一个增量备份等，下面示例模拟该场景。

(1)mysql开启二进制日志记录，在/etc/my.cnf配置新增如下参数:

server-id =1

log-bin=mysql-bin (#强烈建议生产环境中二进制日志保存位置应该在不同磁盘单独保存)

binlog-format=row

# systemctl restart mysqld.service #需要重启mysql服务

(2)进行全备份。

1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

17

18

19

20

21

# innobackupex --user=root --password=111111 --host=192.168.2.5 --default-file=/etc/my.cnf /root/mybackup/......(省略此内容)170227 15:36:02 Executing UNLOCK TABLES170227 15:36:02 All tables unlocked170227 15:36:02 [00] Copying ib_buffer_pool to/root/mybackup/2017-02-27_15-35-57/ib_buffer_pool170227 15:36:02 [00] ...done170227 15:36:02 Backup createdindirectory'/root/mybackup/2017-02-27_15-35-57/'MySQL binlog position: filename'mysql-bin.000002', position'154'170227 15:36:02 [00] Writing backup-my.cnf170227 15:36:02 [00] ...done170227 15:36:02 [00] Writing xtrabackup_info170227 15:36:02 [00] ...donextrabackup: Transaction log of lsn (30571256975) to (30571256984) was copied.170227 15:36:02 completed OK!#备份完成# cat /root/mybackup/2017-02-27_15-35-57/xtrabackup_checkpointsbackup_type = full-backupedfrom_lsn = 0to_lsn = 30571256975last_lsn = 30571256984compact = 0recover_binlog_info = 0 (3)第1次增量备份(增量存放位置/root/backupnew)

增量备份之前，在test数据库tb02表插入一行数据

1

2

mysql>insertintotb02values(3,4);Query OK, 1 row affected (0.04 sec)1

2

3

4

5

6

7

8

9

10

11

12

#innobackupex --user=root --password=111111 --host=192.168.2.5 --incremental /root/backupnew/ --incremental-basedir=/root/mybackup/2017-02-27_15-35-57/......(此处内容省略)170227 16:03:39 [00] Copying ib_buffer_pool to/root/backupnew/2017-02-27_16-03-35/ib_buffer_pool170227 16:03:39 [00] ...done170227 16:03:39 Backup createdindirectory'/root/backupnew/2017-02-27_16-03-35/'MySQL binlog position: filename'mysql-bin.000002', position'414'170227 16:03:39 [00] Writing backup-my.cnf170227 16:03:39 [00] ...done170227 16:03:39 [00] Writing xtrabackup_info170227 16:03:39 [00] ...donextrabackup: Transaction log of lsn (30571257380) to (30571257389) was copied.170227 16:03:39 completed OK! **说明：**

**--incremental #增量备份存放位置**

**--incremental-basedir #第1次增量备份指向全备份文件位置(如是第n次增量备份，该位置应该指向上一次备份文件位置)**

1

2

3

4

5

6

7

# cat /root/backupnew/2017-02-27_16-03-35/xtrabackup_checkpointsbackup_type = incrementalfrom_lsn = 30571256975#这是全备时的"to_lsn"值to_lsn = 30571257380last_lsn = 30571257389compact = 0recover_binlog_info = 0 (4)第2次增量备份

再插入一行数据

1

2

mysql>insertintotb02values(5,6);Query OK, 1 row affected (0.04 sec)1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

17

18

19

20

21

22

# innobackupex--user=root --password=111111 --host=192.168.2.5 --incremental /root/backupnew/ --incremental-basedir=/root/backupnew/2017-02-27_16-03-35/说明:--incremental-basedir指向第1次增量备份......(此处内容省略)170227 16:11:29 Executing UNLOCK TABLES170227 16:11:29Alltables unlocked170227 16:11:29 [00] Copying ib_buffer_poolto/root/backupnew/2017-02-27_16-11-24/ib_buffer_pool170227 16:11:29 [00] ...done170227 16:11:29 Backup createdindirectory'/root/backupnew/2017-02-27_16-11-24/'MySQL binlog position: filename'mysql-bin.000002', position'674'170227 16:11:29 [00] Writing backup-my.cnf170227 16:11:29 [00] ...done170227 16:11:29 [00] Writing xtrabackup_info170227 16:11:29 [00] ...donextrabackup:Transactionlogoflsn (30571257724)to(30571257733) was copied.170227 16:11:29 completed OK!# cat /root/backupnew/2017-02-27_16-11-24/xtrabackup_checkpointsbackup_type = incrementalfrom_lsn = 30571257380 #第1次增量备份的"to_lsn"值to_lsn = 30571257724last_lsn = 30571257733compact = 0recover_binlog_info = 0 备份完之后，再做一次数据插入，方便下面恢复到某个时间点测试

1

2

mysql>insertintotb02values(7,8);Query OK, 1 row affected (0.04 sec) **由于上面log-bin二进制日志跟数据保存与数据库数据文件在同一目录，如该目录被损坏就不能恢复到某个时间点了，所以这里拷贝二进制日志至/tmp/下**

1

#cp -a /mydata/data/mysql-bin.* /tmp/ (5)模拟数据损坏，如在最后一次插入数据后，存放数据库文件目录被恶意删除了

1

2

# systemctl stop mysqld.service# rm -rf /mydata/data/* #删除数据目录文件 (6)合并所有备份数据文件

1

2

3

4

5

6

7

8

9

10

11

12

13

14

15

16

17

18

19

20

21

22

23

24

25

26

27

28

29

30

31

32

33

34

35

36

37

38

39

40

41

42

43

44

45

# innobackupex --apply-log --redo-only /root/mybackup/2017-02-27_15-35-57/ #准备合并全备份170227 16:35:55 innobackupex: Starting the apply-log operationIMPORTANT: Please check that the apply-log run completes successfully.At the end of a successful apply-log run innobackupexprints"completed OK!".......(此处省略内容)xtrabackup: startingshutdownwith innodb_fast_shutdown = 1InnoDB: Startingshutdown...InnoDB: Shutdown completed; log sequence number 30571256993InnoDB: Number of pools: 1170227 16:35:56 completed OK!# innobackupex --apply-log --redo-only /root/mybackup/2017-02-27_15-35-57/ --incremental-dir=/root/backupnew/2017-02-27_16-03-35/ #准备合并第一增量备份的文件IMPORTANT: Please check that the apply-log run completes successfully.At the end of a successful apply-log run innobackupexprints"completed OK!".......(此处省略内容)170227 16:38:47 [01] ...done170227 16:38:47 [01] Copying/root/backupnew/2017-02-27_16-03-35/performance_schema/session_status.frm to ./performance_schema/session_status.frm170227 16:38:47 [01] ...done170227 16:38:47 [00] Copying/root/backupnew/2017-02-27_16-03-35//xtrabackup_binlog_infoto ./xtrabackup_binlog_info170227 16:38:47 [00] ...done170227 16:38:47 [00] Copying/root/backupnew/2017-02-27_16-03-35//xtrabackup_infoto ./xtrabackup_info170227 16:38:47 [00] ...done170227 16:38:47 completed OK!# innobackupex --apply-log --redo-only /root/mybackup/2017-02-27_15-35-57/ --incremental-dir=/root/backupnew/2017-02-27_16-11-24/ #准备合并第二增量备份的文件IMPORTANT: Please check that the apply-log run completes successfully.At the end of a successful apply-log run innobackupexprints"completed OK!".......(此处省略内容)170227 16:40:16 [01] Copying/root/backupnew/2017-02-27_16-11-24/performance_schema/global_status.frm to ./performance_schema/global_status.frm170227 16:40:16 [01] ...done170227 16:40:16 [01] Copying/root/backupnew/2017-02-27_16-11-24/performance_schema/session_status.frm to ./performance_schema/session_status.frm170227 16:40:16 [01] ...done170227 16:40:16 [00] Copying/root/backupnew/2017-02-27_16-11-24//xtrabackup_binlog_infoto ./xtrabackup_binlog_info170227 16:40:16 [00] ...done170227 16:40:16 [00] Copying/root/backupnew/2017-02-27_16-11-24//xtrabackup_infoto ./xtrabackup_info170227 16:40:16 [00] ...done170227 16:40:16 completed OK!# cat /root/mybackup/2017-02-27_15-35-57/xtrabackup_checkpointsbackup_type = log-appliedfrom_lsn = 0to_lsn = 30571257724#这时已经合并到第二次增量备份是的“to_lsn”值last_lsn = 30571257733compact = 0recover_binlog_info = 0 拷贝合并后的文件至/mydata/data/，并修改文件属主和属组，启动mysql服务

1

2

3

# cp -rf /root/mybackup/2017-02-27_15-35-57/* /mydata/data/# chown -R mysql:mysql /mydata/data/# systemctl start mysqld.service 检查数据

1

2

3

4

5

6

7

8

9

mysql>select*fromtb02;+---+---+| a | b |+---+---+| 1 | 2 || 3 | 4 || 5 | 6 |+---+---+3rowsinset(0.00 sec) **注意：这时无法恢复到第二次增量备份后的操作，也就是插入的新数据这里没有，二进制日志就起到了关键的作用了。**

查看第2次增量后的二进制日志文件及position信息：

1

2

# cat /root/backupnew/2017-02-27_16-11-24/xtrabackup_binlog_infomysql-bin.000002674 用mysqlbinlog工具导出最后一次增量备份后的sql操作

1

#/usr/local/mysql/bin/mysqlbinlog --start-position=674 /tmp/mysql-bin.000002 > /tmp/t.sql 在test数据库，导入t.sql

1

mysql> source /tmp/t.sql 重新检查数据:

1

2

3

4

5

6

7

8

9

10

mysql>select*fromtb02;+---+---+| a | b |+---+---+| 1 | 2 || 3 | 4 || 5 | 6 || 7 | 8 |+---+---+4rowsinset(0.00 sec) **总结:**

**1、xtrabackup进行数据恢复时需要把各个增量的数据备份与全备份的数据进行合并，对每次增量备份的合并只能将已提交的事务进行重放(redo)，对合备份的数据恢复也只能做redo操作，把各个增量都合并完成后再把没有提交的事务进行回滚(undo)操作，合并完增量备份后，全备份的“xtrabackup_checkpoints”文件中的“last_lsn”应该是最后一次增量备份时的值，这些合并做redo的过程就是恢复数据前的准备工作（prepare）。**

**而真正在做数据恢复，建议先把全备和增量备份的文件都copy一份为副本，避免操作失误导致备份文件的损坏。**

**2、通过apply-log同步到完全备份文件中，如果希望利用增量日志还原到固定某次增量备份的数据，则不能使用apply-log操作，如果希望利用增量日志还原到固定哪次增量备份的数据，则将最初的完全备份的数据、和期望还原到某个增量备份前的增量备份的数据，拷贝一份到别的地方，然后依次对拷贝出来的完全备份做apply-log，对每次增量备份做apply-log，然后用形成的apply-log后形成的完全备份的数据，进行恢复.**

**3、实际恢复测试中，innobackupex使用--copy-back参数时，会报错以下:**

**# innobackupex --defaults-file=/etc/my.cnf --copy-back /root/mybackup/2017-02-27_11-52-46/**

**170227 14:53:27 innobackupex: Starting the copy-back operation**

**IMPORTANT: Please check that the copy-back run completes successfully.**

**At the end of a successful copy-back run innobackupex**

**prints "completed OK!".**

**innobackupex version 2.4.6 based on MySQL server 5.7.13 Linux (x86_64) (revision id: 54967d1)**

**Original data directory /mydata/data is not empty!**

**有google、百度说是xtrabackup的BUG，建议采用--apply-log后，进行拷贝文件，再修改文件的属主和属组也是可以的。**

参考链接:http://zhaochj.blog.51cto.com/368705/1633254

本文出自 “[一万小时定律][4]” 博客，请务必保留此出处[http://daisywei.blog.51cto.com/7837970/1901733][5]

[0]: http://www.cnblogs.com/wajika/p/6481108.html
[1]: http://www.cnblogs.com/wajika/
[2]: https://i.cnblogs.com/EditPosts.aspx?postid=6481108
[3]: #
[4]: http://daisywei.blog.51cto.com
[5]: http://daisywei.blog.51cto.com/7837970/1901733
# [mysql备份实战-Xtrabackup工具备份][0]



导读 **大数据量备份与还原，始终是个难点。当MYSQL超10G，用mysqldump来导出就比较慢了。这里介绍一个强大的开源工具Xtrabackup。**

**Xtrabackup简介**

Xtrabackup是由percona提供的mysql数据库备份工具，据官方介绍，这也是世界上惟一一款开源的能够对innodb和xtradb数据库进行热备的工具，是商业备份工具InnoDB Hotbackup的一个很好的替代品。特点：

    (1)备份过程快速、可靠；
    (2)备份过程不会打断正在执行的事务；
    (3)能够基于压缩等功能节约磁盘空间和流量；
    (4)自动实现备份检验；
    (5)还原速度快；
    

**Xtrabackup安装**

其最新版的软件可从 http://www.percona.com/software/percona-xtrabackup/ 获得。本文基于CentOS6.6的系统，因此，直接下载相应版本的rpm包安装即可。

[root@localhost xtrabackup]# yum -y install perl perl-devel libaio libaio-devel perl-Time-HiRes perl-DBD-MySQL //安装依赖包

     
    [root@localhost xtrabackup]# rpm -ivh percona-xtrabackup-2.2.4-5004.el6.x86_64.rpm     
    warning: percona-xtrabackup-2.2.4-5004.el6.x86_64.rpm: Header V4 DSA/SHA1 Signature, key ID cd2efd2a: NOKEY  
    Preparing... ########################################### [100%]  
    1:percona-xtrabackup ########################################### [100%]  
    

**Xtrabackup备份的实现**

**1、完全备份**

    # innobackupex --user=DBUSER --password=DBUSERPASS  /path/to/BACKUP-DIR/
    

如果要使用一个最小权限的用户进行备份，则可基于如下命令创建此类用户：

    mysql> CREATE USER  ’feiyu'@’localhost’ IDENTIFIED BY ’s3cret’;
    mysql> REVOKE ALL PRIVILEGES, GRANT OPTION FROM ’feiyu’;
    mysql> GRANT RELOAD, LOCK TABLES, REPLICATION CLIENT ON *.* TO ’feiyu’@’localhost’;
    mysql> FLUSH PRIVILEGES;
    

使用innobakupex备份时，其会调用xtrabackup备份所有的InnoDB表，复制所有关于表结构定义的相关文件(.frm)、以及MyISAM、MERGE、CSV和ARCHIVE表的相关文件，同时还会备份触发器和数据库配置信息相关的文件。这些文件会被保存至一个以时间命令的目录中。

**在备份的同时，innobackupex还会在备份目录中创建如下文件：**

> (1)xtrabackup_checkpoints —— 备份类型（如完全或增量）、备份状态（如是否已经为prepared状态）和LSN(日志序列号)范围信息；

> 每个InnoDB页(通常为16k大小)都会包含一个日志序列号，即LSN。LSN是整个数据库系统的系统版本号，每个页面相关的LSN能够表明此页面最近是如何发生改变的。

> (2)xtrabackup_binlog_info —— mysql服务器当前正在使用的二进制日志文件及至备份这一刻为止二进制日志事件的位置。

> (3)xtrabackup_binlog_pos_innodb —— 二进制日志文件及用于InnoDB或XtraDB表的二进制日志文件的当前position。

> (4)xtrabackup_binary —— 备份中用到的xtrabackup的可执行文件；

> (5)backup-my.cnf —— 备份命令用到的配置选项信息；

在使用innobackupex进行备份时，还可以使用–no-timestamp选项来阻止命令自动创建一个以时间命名的目录；如此一来，innobackupex命令将会创建一个BACKUP-DIR目录来存储备份数据。

**2、准备(prepare)一个完全备份**

一般情况下，在备份完成后，数据尚且不能用于恢复操作，因为备份的数据中可能会包含尚未提交的事务或已经提交但尚未同步至数据文件中的事务。因此，此时数据文件仍处理不一致状态。“准备”的主要作用正是通过回滚未提交的事务及同步已经提交的事务至数据文件也使得数据文件处于一致性状态。

innobakupex命令的–apply-log选项可用于实现上述功能。如下面的命令：

    # innobackupex --apply-log  /path/to/BACKUP-DIR
    

如果执行正确，其最后输出的几行信息通常如下：

    xtrabackup: starting shutdown with innodb_fast_shutdown = 1
    120407  9:01:36  InnoDB: Starting shutdown...
    120407  9:01:40  InnoDB: Shutdown completed; log sequence number 92036620
    120407 09:01:40  innobackupex: completed OK!
    

在实现“准备”的过程中，innobackupex通常还可以使用–use-memory选项来指定其可以使用的内存的大小，默认通常为100M。如果有足够的内存可用，可以多划分一些内存给prepare的过程，以提高其完成速度。

**3、从一个完全备份中恢复数据**

innobackupex命令的–copy-back选项用于执行恢复操作，其通过复制所有数据相关的文件至mysql服务器DATADIR目录中来执行恢复过程。innobackupex通过backup-my.cnf来获取DATADIR目录的相关信息。

    # innobackupex --copy-back  /path/to/BACKUP-DIR
    

如果执行正确，其输出信息的最后几行通常如下：

    innobackupex: Starting to copy InnoDB log files
    innobackupex: in '/backup/2012-04-07_08-17-03'
    innobackupex: back to original InnoDB log directory '/mydata/data'
    innobackupex: Finished copying back files.
     
    120407 09:36:10  innobackupex: completed OK!
    

请确保如上信息的最行一行出现“innobackupex: completed OK!”。

当数据恢复至DATADIR目录以后，还需要确保所有数据文件的属主和属组均为正确的用户，如mysql，否则，在启动mysqld之前还需要事先修改数据文件的属主和属组。如：

    # chown -R  mysql:mysql  /mydata/data/
    

**4、使用innobackupex进行增量备份**

每个InnoDB的页面都会包含一个LSN信息，每当相关的数据发生改变，相关的页面的LSN就会自动增长。这正是InnoDB表可以进行增量备份的基础，即innobackupex通过备份上次完全备份之后发生改变的页面来实现。

要实现第一次增量备份，可以使用下面的命令进行：

    # innobackupex --incremental /backup --incremental-basedir=BASEDIR
    

（要对与完全备份实行增量备份，basedir指完全备份的目录，要对上一次增量备份再执行增量备份，则指定上一次  
增量备份的目录）

其中，BASEDIR指的是完全备份所在的目录，此命令执行结束后，innobackupex命令会在/backup目录中创建一个新的以时间命名的目录以存放所有的增量备份数据。另外，在执行过增量备份之后再一次进行增量备份时，其–incremental-basedir应该指向上一次的增量备份所在的目录。

需要注意的是，增量备份仅能应用于InnoDB或XtraDB表，对于MyISAM表而言，执行增量备份时其实进行的是完全备份。

“准备”(prepare)增量备份与整理完全备份有着一些不同，尤其要注意的是：

> (1)需要在每个备份(包括完全和各个增量备份)上，将已经提交的事务进行“重放”。“重放”之后，所有的备份数据将合并到完全备份上。  
> (2)基于所有的备份将未提交的事务进行“回滚”。  
> （以下为合并增量备份到完全备份，然后恢复时只指定完全备份即可）

于是，操作就变成了：

    # innobackupex --apply-log --redo-only BASE-DIR
    

接着执行：

    # innobackupex --apply-log --redo-only BASE-DIR --incremental-dir=INCREMENTAL-DIR-1
    

而后是第二个增量：

    # innobackupex --apply-log --redo-only BASE-DIR --incremental-dir=INCREMENTAL-DIR-2
    

其中BASE-DIR指的是完全备份所在的目录，而INCREMENTAL-DIR-1指的是第一次增量备份的目录，INCREMENTAL-DIR-2指的是第二次增量备份的目录，其它依次类推，即如果有多次增量备份，每一次都要执行如上操作；

**5、Xtrabackup的“流”及“备份压缩”功能**

Xtrabackup对备份的数据文件支持“流”功能，即可以将备份的数据通过STDOUT传输给tar程序进行归档，而不是默认的直接保存至某备份目录中。要使用此功能，仅需要使用–stream选项即可。如：

    # innobackupex --stream=tar  /backup | gzip > /backup/`date +%F_%H-%M-%S`.tar.gz
    

甚至也可以使用类似如下命令将数据备份至其它服务器：

    # innobackupex --stream=tar  /backup | ssh user@www.feiyu.com  "cat -  > /backups/`date +%F_%H-%M-%S`.tar" 
    

此外，在执行本地备份时，还可以使用–parallel选项对多个文件进行并行复制。此选项用于指定在复制时启动的线程数目。当然，在实际进行备份时要利用此功能的便利性，也需要启用innodb_file_per_table选项或共享的表空间通过innodb_data_file_path选项存储在多个ibdata文件中。对某一数据库的多个文件的复制无法利用到此功能。其简单使用方法如下：

    # innobackupex --parallel  /path/to/backup
    

同时，innobackupex备份的数据文件也可以存储至远程主机，这可以使用–remote-host选项来实现：

    # innobackupex --remote-host=root@www.feiyu.com  /path/IN/REMOTE/HOST/to/backup
    

**6、导入或导出单张表**

默认情况下，InnoDB表不能通过直接复制表文件的方式在mysql服务器之间进行移植，即便使用了innodb_file_per_table选项。而使用Xtrabackup工具可以实现此种功能，不过，此时需要“导出”表的mysql服务器启用了innodb_file_per_table选项（严格来说，是要“导出”的表在其创建之前，mysql服务器就启用了innodb_file_per_table选项），并且“导入”表的服务器同时启用了innodb_file_per_table和innodb_expand_import选项。

(1)“导出”表  
导出表是在备份的prepare阶段进行的，因此，一旦完全备份完成，就可以在prepare过程中通过–export选项将某表导出了：

    # innobackupex --apply-log --export /path/to/backup
    

此命令会为每个innodb表的表空间创建一个以.exp结尾的文件，这些以.exp结尾的文件则可以用于导入至其它服务器。  
(2)“导入”表  
要在mysql服务器上导入来自于其它服务器的某innodb表，需要先在当前服务器上创建一个跟原表表结构一致的表，而后才能实现将表导入：

    mysql> CREATE TABLE mytable (...)  ENGINE=InnoDB;
    

然后将此表的表空间删除：

    mysql> ALTER TABLE mydatabase.mytable  DISCARD TABLESPACE;
    

接下来，将来自于“导出”表的服务器的mytable表的mytable.ibd和mytable.exp文件复制到当前服务器的数据目录，然后使用如下命令将其“导入”：

    mysql> ALTER TABLE mydatabase.mytable  IMPORT TABLESPACE;
    

**7、使用Xtrabackup对数据库进行部分备份**

Xtrabackup也可以实现部分备份，即只备份某个或某些指定的数据库或某数据库中的某个或某些表。但要使用此功能，必须启用innodb_file_per_table选项，即每张表保存为一个独立的文件。同时，其也不支持–stream选项，即不支持将数据通过管道传输给其它程序进行处理。

此外，还原部分备份跟还原全部数据的备份也有所不同，即你不能通过简单地将prepared的部分备份使用–copy-back选项直接复制回数据目录，而是要通过导入表的方向来实现还原。当然，有些情况下，部分备份也可以直接通过–copy-back进行还原，但这种方式还原而来的数据多数会产生数据不一致的问题，因此，无论如何不推荐使用这种方式。

(1)创建部分备份

创建部分备份的方式有三种：正则表达式(–include), 枚举表文件(–tables-file)和列出要备份的数据库(–databases)。

(a)使用–include  
使用–include时，要求为其指定要备份的表的完整名称，即形如databasename.tablename，如：

    # innobackupex --include='^feiyu[.]tb1'  /path/to/backup
    

(b)使用–tables-file  
此选项的参数需要是一个文件名，此文件中每行包含一个要备份的表的完整名称；如：

    # echo -e 'feiyu.tb1\nmageedu.tb2' > /tmp/tables.txt
    # innobackupex --tables-file=/tmp/tables.txt  /path/to/backup
    

(c)使用–databases  
此选项接受的参数为数据名，如果要指定多个数据库，彼此间需要以空格隔开；同时，在指定某数据库时，也可以只指定其中的某张表。此外，此选项也可以接受一个文件为参数，文件中每一行为一个要备份的对象。如：

    # innobackupex --databases="feiyu testdb"  /path/to/backup
    

(2)整理(preparing)部分备份  
prepare部分备份的过程类似于导出表的过程，要使用–export选项进行：

    # innobackupex --apply-log --export  /pat/to/partial/backup
    

此命令执行过程中，innobackupex会调用xtrabackup命令从数据字典中移除缺失的表，因此，会显示出许多关于“表不存在”类的警告信息。同时，也会显示出为备份文件中存在的表创建.exp文件的相关信息。

(3)还原部分备份  
还原部分备份的过程跟导入表的过程相同。当然，也可以通过直接复制prepared状态的备份直接至数据目录中实现还原，不要此时要求数据目录处于一致状态。

下面实际演示其完整的备份流程：

    ←#14#root@localhost /tmp/full-backup  →innobackupex --user=root /tmp/full-backup/  #完全备份
     
    InnoDB Backup Utility v1.5.1-xtrabackup; Copyright 2003, 2009 Innobase Oy
    and Percona LLC and/or its affiliates 2009-2013.  All Rights Reserved.
    。。。。。。。。。。
     
    xtrabackup: The latest check point (for incremental): '2987626'
    xtrabackup: Stopping log copying thread.
    .>> log scanned up to (2987626)
     
    xtrabackup: Creating suspend file '/tmp/full-backup/2015-06-25_05-58-26/xtrabackup_log_copied' with pid '7858'
    xtrabackup: Transaction log of lsn (2987626) to (2987626) was copied.
    150625 05:58:30  innobackupex: All tables unlocked
     
    innobackupex: Backup created in directory '/tmp/full-backup/2015-06-25_05-58-26'
    innobackupex: MySQL binlog position: filename 'mysql-bin.000001', position 2383
    150625 05:58:30  innobackupex: Connection to database server closed
    150625 05:58:30  innobackupex: completed OK!
    

    mysql> insert into  tutors(tname) values('stu00011');#在数据库中插入数据
    Query OK, 1 row affected (0.03 sec)
     
    mysql> insert into  tutors(tname) values('stu00012');
    Query OK, 1 row affected (0.00 sec)
    

    ←#246#root@localhost /tmp  →innobackupex --incremental /tmp/full-backup/ --incremental-basedir=/tmp/full-backup/2015-06-25_05-58-26/ #做增量备份
     
    InnoDB Backup Utility v1.5.1-xtrabackup; Copyright 2003, 2009 Innobase Oy
    and Percona LLC and/or its affiliates 2009-2013.  All Rights Reserved.
    。。。。。。。。。。。
    xtrabackup: Creating suspend file '/tmp/full-backup/2015-06-25_06-00-48/xtrabackup_log_copied' with pid '8663'
    xtrabackup: Transaction log of lsn (2988209) to (2988209) was copied.
    150625 06:00:53  innobackupex: All tables unlocked
     
    innobackupex: Backup created in directory '/tmp/full-backup/2015-06-25_06-00-48'
    innobackupex: MySQL binlog position: filename 'mysql-bin.000001', position 2924
    150625 06:00:53  innobackupex: Connection to database server closed
    150625 06:00:53  innobackupex: completed OK!
    

    mysql> insert into  tutors(tname) values('stu00014');  #再次插入数据
    Query OK, 1 row affected (0.02 sec)
     
    mysql> insert into  tutors(tname) values('stu00015');
    Query OK, 1 row affected (0.00 sec)
    

    ←#247#root@localhost /tmp  →innobackupex --incremental /tmp/full-backup/ --incremental-basedir=/tmp/full-backup/2015-06-25_06-00-48  #再次做增量备份
     
    InnoDB Backup Utility v1.5.1-xtrabackup; Copyright 2003, 2009 Innobase Oy
    and Percona LLC and/or its affiliates 2009-2013.  All Rights Reserved
    。。。。。。。。。
    xtrabackup: Creating suspend file '/tmp/full-backup/2015-06-25_06-02-41/xtrabackup_log_copied' with pid '9259'
    xtrabackup: Transaction log of lsn (2988781) to (2988781) was copied.
    150625 06:02:45  innobackupex: All tables unlocked
     
    innobackupex: Backup created in directory '/tmp/full-backup/2015-06-25_06-02-41'
    innobackupex: MySQL binlog position: filename 'mysql-bin.000001', position 3465
    150625 06:02:46  innobackupex: Connection to database server closed
    150625 06:02:46  innobackupex: completed OK!
    

    ←#266#root@localhost /tmp/full-backup/2015-06-25_05-58-26  →cat  xtrabackup_checkpoints  #查看日志序列号是否一致
    backup_type = log-applied
    from_lsn = 0
    to_lsn = 2987626
    last_lsn = 2987626
    compact = 0
    ←#267#root@localhost /tmp/full-backup/2015-06-25_05-58-26  →cd ../2015-06-25_06-00-48/
    ←#268#root@localhost /tmp/full-backup/2015-06-25_06-00-48  →cat  xtrabackup_checkpoints 
    backup_type = incremental
    from_lsn = 2987626
    to_lsn = 2988209
    last_lsn = 2988209
    compact = 0
    ←#269#root@localhost /tmp/full-backup/2015-06-25_06-00-48  →cd ../2015-06-25_06-02-41/
    ←#270#root@localhost /tmp/full-backup/2015-06-25_06-02-41  →cat  xtrabackup_checkpoints 
    backup_type = incremental
    from_lsn = 2988209
    to_lsn = 2988781
    last_lsn = 2988781
    compact = 0
    

    ←#248#root@localhost /tmp  →innobackupex --apply-log --redo-only /tmp/full-backu2015-06-25_05-58-26/  #做准备
    InnoDB Backup Utility v1.5.1-xtrabackup; Copyright 2003, 2009 Innobase Oy
    and Percona LLC and/or its affiliates 2009-2013.  All Rights Reserved.
     
     
     
    [notice (again)]
      If you use binary log and don't use any hack of group commit,
      the binary log position seems to be:
    InnoDB: Last MySQL binlog file position 0 2241, file name ./mysql-bin.000001
     
    xtrabackup: starting shutdown with innodb_fast_shutdown = 1
    InnoDB: Starting shutdown...
    InnoDB: Shutdown completed; log sequence number 2987626
    150625 06:04:03  innobackupex: completed OK!
     
    ←#249#root@localhost /tmp  →innobackupex --apply-log --redo-only /tmp/full-backu2015-06-25_05-58-26/ --incremental-dir=/tmp/full-backup/2015-06-25_06-00-48/ #合并第一次增量备份文件
     
     
    。。。。。。。。。。
    innobackupex: Copying '/tmp/full-backup/2015-06-25_06-00-48/management/admin.frm' to '/tmp/full-backup/2015-06-25_05-58-26/management/admin.frm'
    150625 06:05:28  innobackupex: completed OK!
     
    ←#251#root@localhost /tmp  →innobackupex --apply-log --redo-only /tmp/full-backu2015-06-25_05-58-26/ --incremental-dir=/tmp/full-backup/2015-06-25_06-02-41/  #合并第二次增量备份文件
     
    。。。。。。。。。。。。。。。。
    innobackupex: Copying '/tmp/full-backup/2015-06-25_06-02-41/management/classinfo.frm' to '/tmp/full-backup/2015-06-25_05-58-26/management/classinfo.frm'
    innobackupex: Copying '/tmp/full-backup/2015-06-25_06-02-41/management/admin.frm' to '/tmp/full-backup/2015-06-25_05-58-26/management/admin.frm'
    150625 06:07:10  innobackupex: completed OK!
     
    ←#258#root@localhost ~  →rm -rf  /mydata/data1/*  #删除数据文件目录
    ←#259#root@localhost ~  →innobackupex --copy-back  /tmp/full-backup/2015-06-25_05-58-26/  #恢复
     
    。。。。。。。。。。。。。。。。
    innobackupex: Starting to copy InnoDB log files
    innobackupex: in '/tmp/full-backup/2015-06-25_05-58-26'
    innobackupex: back to original InnoDB log directory '/mydata/data1'
    innobackupex: Finished copying back files.
     
    150625 06:12:29  innobackupex: completed OK!
     
    ←#276#root@localhost /mydata/data1  →chown -R mysql.mysql ./*  #修改属主和属组
    

> 原文来自：[http://www.tianfeiyu.com/?p=738][1]

> 本文地址：[http://www.linuxprobe.com/mysql-xtrabackup-dump.html][0]

[0]: http://www.linuxprobe.com/mysql-xtrabackup-dump.html
[1]: http://www.tianfeiyu.com/?p=738
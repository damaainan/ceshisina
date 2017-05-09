# MySQL 5.7 mysqlpump 备份工具说明

[日期：2016-08-22] 来源：Linux社区 作者：zhoujinyi 

 **背景：**

MySQL5.7之后多了一个备份工具：mysqlpump。它是mysqldump的一个衍生，mysqldump就不多说明了，现在看看mysqlpump到底有了哪些提升，可以查看[官方文档][3]，这里针对如何使用做下说明。

mysqlpump和mysqldump一样，属于逻辑备份，备份以SQL形式的文本保存。逻辑备份相对物理备份的好处是不关心undo log的大小，直接备份数据即可。它最主要的特点是：

* **并行备份** 数据库和数据库中的对象的，加快备份过程。
* 更好的控制数据库和数据库对象（表，存储过程，用户帐户）的备份。
* 备份用户账号作为帐户管理语句（CREATE USER，GRANT），而不是直接插入到MySQL的系统数据库。
* 备份出来直接生成压缩后的备份文件。
* 备份进度指示（估计值）。
* 重新加载（还原）备份文件，先建表后插入数据最后建立索引，减少了索引维护开销，加快了还原速度。
* 备份可以排除或则指定数据库。

**参数：** 绝大部分参数和mysqldump一致，顺便复习一下。对于mysqlpump参数会用背景色  标记出来。

1：[--add-drop-database][4]：在建立库之前先执行删库操作。

    DROP DATABASE IF EXISTS `...`;

2：[--add-drop-table][5]：在建表之前先执行删表操作。

    DROP TABLE IF EXISTS `...`.`...`;

3： [--add-drop-user][6] ：在CREATE USER语句之前增加DROP USER， **注意：** 这个参数需要和 [--users][7] 一起使用，否者不生效。

    DROP USER 'backup'@'192.168.123.%';

4：[--add-locks][8]：备份表时，使用LOCK TABLES和UNLOCK TABLES。 **注意：** 这个参数不支持并行备份，需要关闭并行备份功能： [--default-parallelism][9] =0

    LOCK TABLES `...`.`...` WRITE;
    ...
    UNLOCK TABLES;

5：[--all-databases][10]：备份所有库，-A。

6：[--bind-address][11]：指定通过哪个网络接口来连接Mysql服务器（一台服务器可能有多个IP），防止同一个网卡出去影响业务。

7：[--complete-insert][12]：dump出包含所有列的完整insert语句。

8：[--compress][13]： 压缩客户端和服务器传输的所有的数据，-C。

9： [--compress-output][14] ：默认不压缩输出，目前可以使用的压缩算法有LZ4和ZLIB。

    shell> mysqlpump --compress-output=LZ4 > dump.lz4
    shell>**lz4_decompress** dump.lz4 dump.txt
    
    shell> mysqlpump --compress-output=ZLIB > dump.zlib
    shell> **zlib_decompress** dump.zlib dump.txt

10：[--databases][15]：手动指定要备份的库，支持多个数据库，用空格分隔，-B。

11：[--default-character-set][16]：指定备份的字符集。

12： [--default-parallelism][9] ：指定并行线程数，默认是2，如果设置成0，表示不使用并行备份。 **注意：** 每个线程的备份步骤是：先create table但不建立二级索引（主键会在create table时候建立），再写入数据，最后建立二级索引。

13： [--defer-table-indexes][17] ：延迟创建索引，直到所有数据都加载完之后，再创建索引，默认开启。若关闭则会和mysqldump一样：先创建一个表和所有索引，再导入数据，因为在加载还原数据的时候要维护二级索引的开销，导致效率比较低。关闭使用参数：[--skip--defer-table-indexes][17]。

14：[--events][18]：备份数据库的事件，默认开启，关闭使用--skip-events参数。

15： [--exclude-databases][19] ：备份排除该参数指定的数据库，多个用逗号分隔。类似的还有 [--exclude-events][20] 、 [--exclude-routines][21] 、 [--exclude-tables][22] 、 [--exclude-triggers][23] 、 [--exclude-users][24] 。

    **mysqlpump --exclude-databases**=mysql,sys    #备份过滤mysql和sys数据库
    
    **mysqlpump --exclude-tables**=rr,tt   #备份过滤所有数据库中rr、tt表
    
    **mysqlpump -B test --exclude-tables**=tmp_ifulltext,tt #备份过滤test库中的rr、tt表
    ...

**注意：** 要是只备份数据库的账号，需要添加参数[--users][7]，并且需要过滤掉所有的数据库，如：

    **mysqlpump --users** **--exclude-databases**=sys,mysql,db1,db2 **--exclude-users**=dba,backup  #备份除dba和backup的所有账号。

16： [--include-databases][25] ：指定备份数据库，多个用逗号分隔，类似的还有 [--include-events][26] 、 [--include-routines][27] 、 [--include-tables][28] 、 [--include-triggers][29] 、 [--include-users][30] ，大致方法使用同15。

17：[--insert-ignore][31]：备份用insert ignore语句代替insert语句。

18：[--log-error-file][32]：备份出现的warnings和erros信息输出到一个指定的文件。

19：[--max-allowed-packet][33]：备份时用于client/server直接通信的最大buffer包的大小。

20：[--net-buffer-length][34]：备份时用于client/server通信的初始buffer大小，当创建多行插入语句的时候，mysqlpump 创建行到N个字节长。

21：[--no-create-db][35]：备份不写CREATE DATABASE语句。要是备份多个库，需要使用参数-B，而使用-B的时候会出现create database语句，该参数可以屏蔽create database 语句。

22：[--no-create-info][36]：备份不写建表语句，即不备份表结构，只备份数据，-t。

23：[--hex-blob][37]： 备份binary字段的时候使用十六进制计数法，受影响的字段类型有BINARY、VARBINARY、BLOB、BIT。

24：[--host][38]：备份指定的数据库地址，-h。

25： [--parallel-schemas=[_N_:]_db_list_][39] ：指定并行备份的库，多个库用逗号分隔，如果指定了N，将使用N个线程的地队列，如果N不指定，将由 --default-parallelism才确认N的值，可以设置多个[--parallel-schemas][39]。    **mysqlpump --parallel-schemas=4:vs,aa --parallel-schemas=3****:pt**   #4个线程备份vs和aa，3个线程备份pt。通过show processlist 可以看到有7个线程。
    
    **mysqlpump --parallel-schemas=vs,abc --parallel-schemas=****pt**  #默认2个线程，即2个线程备份vs和abc，2个线程备份pt
    
    ####当然要是硬盘IO不允许的话，可以少开几个线程和数据库进行并行备份

26：[--password][40]：备份需要的密码。

27：[--port][41]：备份数据库的端口。

28：[--protocol={TCP|SOCKET|PIPE|MEMORY}][42]：指定连接服务器的协议。 29：[--replace][43]：备份出来replace into语句。

30：[--routines][44]：备份出来包含存储过程和函数，默认开启，需要对 mysql.proc表有查看权限。生成的文件中会包含CREATE PROCEDURE 和 CREATE FUNCTION语句以用于恢复，关闭则需要用--skip-routines参数。 31：[--triggers][45]：备份出来包含触发器，默认开启，使用[--skip-triggers][45]来关闭。 31：[--set-charset][46]：备份文件里写SET NAMES default_character_set 到输出，此参默认开启。 -- skip-set-charset禁用此参数，不会在备份文件里面写出set names...

32：[--single-transaction][47]：该参数在事务隔离级别设置成Repeatable Read，并在dump之前发送start transaction 语句给服务端。这在使用innodb时很有用，因为在发出start transaction时，保证了在不阻塞任何应用下的一致性状态。对myisam和memory等非事务表，还是会改变状态的，当使用此参的时候要确保没有其他连接在使用ALTER TABLE、CREATE TABLE、DROP TABLE、RENAME TABLE、TRUNCATE TABLE等语句，否则会出现不正确的内容或则失败。--add-locks和此参互斥，在mysql5.7.11之前，--default-parallelism大于1的时候和此参也互斥，必须使用--default-parallelism=0。5.7.11之后解决了--single-transaction和--default-parallelism的互斥问题。

33： [--skip-definer][48] ：忽略那些创建视图和存储过程用到的 DEFINER 和 SQL SECURITY 语句，恢复的时候，会使用默认值，否则会在还原的时候看到没有DEFINER定义时的账号而报错。

34： [--skip-dump-rows][49] ：只备份表结构，不备份数据，-d。 **注意：** mysqldump支持--no-data，mysqlpump不支持--no-data

35：[--socket][50]：对于连接到localhost，Unix使用套接字文件，在Windows上是命名管道的名称使用，-S。

36：[--ssl][51]：--ssl参数将要被去除，用[--ssl-mode][51]取代。关于ssl相关的备份，请看[官方文档][52]。

37：[--tz-utc][53]：备份时会在备份文件的最前几行添加 SET TIME_ZONE= '+00:00'。 **注意：** 如果还原的服务器不在同一个时区并且还原表中的列有timestamp字段，会导致还原出来的结果不一致。默认开启该参数，用 [--skip-tz-utc][53]来关闭参数。 38：[--user][54]：备份时候的用户名，-u。

39： [--users][7] ：备份数据库用户，备份的形式是CREATE USER...，GRANT...，只备份数据库账号可以通过如下命令：

    mysqlpump --exclude-databases=% --users    #过滤掉所有数据库

40： [--watch-progress][55] ：定期显示进度的完成，包括总数表、行和其他对象。该参数默认开启，用[--skip-watch-progress][55]来关闭。**使用说明：**

**mysqlpump的架构如下图所示**：

**![MySQL 5.7 mysqlpump 备份工具说明][56]**

mysqlpump支持基于库和表的并行导出，mysqlpump的并行导出功能的架构为：队列+线程，允许有多个队列（[--parallel-schemas][39]？），每个队列下有多个线程（N？），而一个队列可以绑定1个或者多个数据库（逗号分隔）。 **mysqlpump的备份是基于表并行的，对于每张表的导出只能是单个线程的** ，这里会有个限制是如果某个数据库有一张表非常大，可能大部分的时间都是消耗在这个表的备份上面，并行备份的效果可能就不明显。这里可以利用[**mydumper**][57]其是以chunk的方式批量导出，即 mydumper支持一张表多个线程以chunk的方式批量导出 。但是相对于mysqldump还是有了很大的提升。这里大致测试下mysqlpump和mysqldump的备份效率。

    #mysqlpump压缩备份vs数据库 三个并发线程备份，消耗时间：**222s**
    mysqlpump -uzjy -p -h192.168.123.70 --single-transaction --default-character-set=utf8 --compress-output=LZ4 --default-parallelism=3 -B vs > /home/zhoujy/vs_db.sql.lz4
    
    #mysqldump备份压缩vs数据库 单个线程备份，消耗时间：**900s**，gzip的压缩率比LZ4的高
    mysqldump -uzjy -p -h192.168.123.70 --default-character-set=utf8 -P3306 --skip-opt --add-drop-table --create-options  --quick --extended-insert --single-transaction -B vs | gzip > /home/zhoujy/vs.sql.gz
    
    #mydumper备份vs数据库 三个并发线程备份，消耗时间：**300s**，gzip的压缩率比LZ4的高
    mydumper -u zjy -p  -h 192.168.123.70 -P 3306 -t 3 -c -l 3600 -s 10000000 -B vs -o /home/zhoujy/vs/
    
    #mydumper备份vs数据库，五个并发线程备份，并且**开启对一张表多个线程以chunk的方式批量导出，****-****r**。消耗时间：**180s**
    mydumper -u zjy -p  -h 192.168.123.70 -P 3306 -t 5 -c -r 300000 -l 3600 -s 10000000 -B vs -o /home/zhoujy/vs/

从上面看出，mysqlpump的备份效率是最快的，mydumper次之，mysqldump最差。所以在IO允许的情况下，能用多线程就别用单线程备份。并且mysqlpump还支持多数据库的并行备份，而mydumper要么备份一个库，要么就备份所有库。 姜大神的[Oracle官方并行逻辑备份工具mysqlpump][58]这篇文章的测试结果也说明了mysqlpump比mysqldump的测试好。由于实际情况不同 ，测试给出的速度提升只是参考。 到底开启多少个并行备份的线程，这个看磁盘IO的承受能力，若该服务器只进行备份任务，可以最大限制的来利用磁盘。

**总结：**

mysqldump和mysqlpump的使用方法绝大部分一致，mysqlpump新的参数文章上已经标明，到底用那种工具备份数据库这个要在具体的环境下才能做出选择，有些时候可能用物理备份更好（xtrabackup），总之根据需要进行测试，最后再决定使用哪种备份工具进行备份。

**参考文档：**

[mysqlpump官方文档][3]

[Oracle][59]官方并行逻辑备份工具mysqlpump

**本文永久更新链接地址** ：[http://www.linuxidc.com/Linux/2016-08/134468.htm][60]


[3]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html
[4]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_add-drop-database
[5]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_add-drop-table
[6]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_add-drop-user
[7]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_users
[8]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_add-locks
[9]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_default-parallelism
[10]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_all-databases
[11]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_bind-address
[12]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_complete-insert
[13]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_compress
[14]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_compress-output
[15]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_databases
[16]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_default-character-set
[17]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_defer-table-indexes
[18]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_events
[19]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-databases
[20]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-events
[21]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-routines
[22]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-tables
[23]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-triggers
[24]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_exclude-users
[25]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-databases
[26]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-events
[27]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-routines
[28]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-tables
[29]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-triggers
[30]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_include-users
[31]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_insert-ignore
[32]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_log-error-file
[33]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_max-allowed-packet
[34]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_net-buffer-length
[35]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_no-create-db
[36]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_no-create-info
[37]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_hex-blob
[38]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_host
[39]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_parallel-schemas
[40]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_password
[41]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_port
[42]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_protocol
[43]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_replace
[44]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_routines
[45]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_triggers
[46]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_set-charset
[47]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_single-transaction
[48]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_skip-definer
[49]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_skip-dump-rows
[50]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_socket
[51]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_ssl
[52]: https://dev.mysql.com/doc/refman/5.7/en/secure-connection-options.html#option_general_ssl-mode
[53]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_tz-utc
[54]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_user
[55]: https://dev.mysql.com/doc/refman/5.7/en/mysqlpump.html#option_mysqlpump_watch-progress
[56]: http://www.linuxidc.com/upload/2016_08/16082209346922.png
[57]: ../../Linux/2016-08/134470.htm
[58]: ../../Linux/2016-08/134469.htm
[59]: http://www.linuxidc.com/topicnews.aspx?tid=12
[60]: ../../Linux/2016-08/134468.htm
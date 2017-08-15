# [MySQL体系结构以及各种文件类型学习汇总][0]


<font face=微软雅黑>

 2016-08-19 15:26  213人阅读  

 目录

1. [1mysql体系结构][9]
1. [2mysql文件类型][10]
1. [3参数文件mycnf][11]
1. [4日志文件][12]
1. [5错误日志][13]
1. [6慢查询日志slow log][14]
1. [7全查询日志][15]
1. [8二进制日志][16]
1. [9套接字socket文件][17]
1. [10pid文件][18]
1. [11表结构文件][19]
1. [12innodb存储文件][20]
1. [13redo文件][21]
1. [14undo日志][22]

## 1，mysql体系结构

由[数据库][23]和数据库实例组成，是单进场多线程[架构][24]。

数据库：物理[操作系统][25]文件或者其它文件的集合，在[MySQL][23]中，数据库文件可以是frm、myd、myi、ibd等结尾的文件，当使用ndb存储引擎时候，不是os文件，是存放于内存中的文件。

数据库实例：由数据库后台进程/线程以及一个共享内存区组成，共享内存可以被运行的后台进程/线程所共享。

![][26]

## 2，mysql文件类型

[mysql][23]主要文件类型有如下几种：

**参数文件** ：mysql实例启动的时候在哪里可以找到数据库文件，并且指定某些初始化参数，这些参数定义了某种内存结构的大小等设置，还介绍了参数类型以及定义作用域。

**日志文件** ：记录mysql对某种条件做出响应时候写入的文件。

**Socket文件** ：当用[Linux][27]的mysql命令行窗口登录的时候需要的文件

**Pid文件** ：mysql实例的进程文件

**Mysql表结构文件** ：存放mysql表结构定义文件

**存储引擎文件** ：记录存储引擎信息的文件。

## 3，参数文件my.cnf

? Mysql实例启动时，会先读取配置参数文件`my.cnf`

? 寻找`my.cnf`位置

（1）：默认情况： `mysql --help | grep my.cnf`

（2）：后台进程去找：`ps –eaf | grep mysql`

（3）：全局搜索：`find /-name my.cnf`

? 可以用`vi`直接维护修改里面的参数值

（1）`dynamic` ：可以通过`set`进行实时修改

（2）`static`，只能在`my.cnf`里面修改，需要`restart`生效

Mysql参数文件中的参数可以分为2种类型：动态（`dynamic`）参数和静态参数（`staitic`）

动态参数意味着可以在mysql实例运行中进行修改，`set global sort_buffer_size=32999999;`修改后，别的`connection`重新进行连接就可以生效了。

生效范围分为：`global`和`session`。

静态的说明在整个mysql实例运行期间不得进行修改，就类似一个只读的read only

## 4，日志文件

日志文件记录了影响mysql数据库的各种类型活动，常见的日志文件有 **错误日志**、 **二进制日志**、 **慢查询日志**、 **全查询日志**、 **redo日志**、 **undo日志**

## 5、错误日志

错误日志对mysql的启动、运行、关闭过程进行了记录，mysql dba在遇到问题时候，第一时间应该查看这个错误日志文件，该文件不但记录了出错信息，还记录了一些警告信息以及正确信息，这个`error日志文件`类似于[Oracle][28]的alert文件，只不过默认情况下是以error结尾。可以通过`show variables like 'log_error';`

![][29]

可以看到错误文件的文件名为服务器的主机名。当然也可以在`my.cnf`里面设置错误日志文件的路径：

    Vim my.cnf

    log-error=/usr/local/mysql/mysqld.log

我们可以在错误日志文件里面看到一些数据库启动信息，以及告警信息还有就是报错信息

## 6，慢查询日志slow log

慢查询日志就是记录运行较慢的sql语句信息，给sql语句的优化带来很好的帮助，可以设置一个`阀值`，将运行时间超过该阀值的sql语句的运行信息都记录到`slow log日志`里面去。该阀值可以通过`long_query_time`来设置，也可以设置到`毫秒微秒`：

![][30]

但是需要注意一点：对于运行时间 **等于该阀值**的，就不会记录在内了。

另外一个参数是`log_queries_not_using_indexes`，如果运行的sql没有使用索引，只要超过阀值了也会记录在慢查询日志里面的。

`long_query_time=0` （记录所有sql可以做审计） ，dba可以通过这个审计来推动业务的发展，可以知道哪些业务开展的好那些业务开展的不好，通过慢sql可以分析出哪些应用性能较差需要优化改进，dba的最大职能以及贡献就在于通过对数据库的维护来推动业务的发展和进步。从数据到业务，这是我们需要一直努力的方向。

![][31]

慢查询日志还可以记录在table里面，

`Slow_log表`，也可以将慢查询日志放入一张表里面

`show variables like 'log_output';` 查看如果是`file`就存放在`slow log`里面，如果是`table`就在`slow_log表`里面。

## 7、全查询日志

记录了对mysql数据库所有的请求信息，不论这些请求信息是否得到了正确的执行，默认文件名为`主机名.log`，你可以看到对`access denied`的请求。

数据库审计+ 问题排查跟踪（损失3%-5%性能）

## 8，二进制日志

记录了对数据库进行变更的操作，但是不包括`select操作`以及`show操作`，因为这类操作对数据库本身没有没有修改，如果你还想记录select和show的话，你就需要查看前面的全查询日志，另外`binlog`还包括了执行数据库更改操作时间和执行时间等信息。

二进制的主要作用有如下2个：

##### （1）：恢复 recovery。
某些数据的恢复需要二进制日志，在全库文件恢复后，可以在此基础上通过二进制日志进行`point-to-time`的恢复。

##### （2）：复制（replication）。
其原理和恢复类似，通过复制和执行二进制日志使得一台远程的mysql数据库（slave）于一台mysql数据库（master）进行实时同步。

通过在`my.cnf`里面设置`log-bin =/home/data/mysql/binlog/mysql-bin.log`生效，默认是在`数据目录` `datadir`下面

#### binlog日志参数：

`max_binlog_size`：指定了单个二进制文件的最大值，如果超过了该值，就会产生新的日志文件，后缀名+1，并且记录到.index文件里面。默认值是1G，不过从多年的dba生涯总结来说，`64M`是通用的大小设置。

`binlog_cache_size`：使用innodb存储引擎时候，所有未提交`uncommitted`的二进制日志会被记录到一个缓存中，等该事务提交时committed直接将缓冲中的二进制日志写入二进制日志文件里面，而该缓冲的大小就由`binlog_cache_size`来决定，这个缓冲区是基于`session`的，也就是每一个线程需要事务的时候，mysql都会分配一个`binlog_cache_size`的缓存，因此改值设置需要非常小心，不能设置过大，免得内存溢出了。

`sync_binlog`：`sync_binlog=N`，参数优化介绍过，大概就是表示每次写缓冲`N`次就同步到磁盘文件中，如果将N设置为1的话，每次都会写入binlog磁盘文件中，这是最保险最安全的，如果`N>1`，在意外发生的时候，就表示会有`N-1个dml`没有被写入`binlog`中，有可能就会发生主动数据不一致的情况。

`binlog-do-db`、`binlog-ingore-db`：表示需要写入或者忽略写入哪些库的日志，默认为空，表示可以将所有库的日志写入到二进制文件里面。

`log-slave-update`：启用从机服务器上的slave日志功能，使这台计算机可以用来构成一个镜像链(A->B->C) ，可以让从库上面产生二进制日志文件，在从库上再挂载一个从库。

`binlog-format`：日志格式有`statement`、`row`、`mixed`格式

**1.Statement**：每一条会修改数据的sql都会记录在`binlog`中。

**优点：**不需要记录每一行的变化，减少了`binlog`日志量，节约了IO，提高性能。(相比row能节约多少性能与日志量，这个取决于应用的SQL情况，正常同一条记录修改或者插入row格式所产生的日志量还小于Statement产生的日志量，但是考虑到如果带条件的update操作，以及整表删除，alter表等操作，ROW格式会产生大量日志，因此在考虑是否使用ROW格式日志时应该跟据应用的实际情况，其所产生的日志量会增加多少，以及带来的IO性能问题。)

**缺点：**由于记录的只是执行语句，为了这些语句能在slave上正确运行，因此还必须记录每条语句在执行的时候的一些相关信息，以保证所有语句能在slave得到和在master端执行时候相同的结果。另外mysql 的复制,像一些特定函数功能，slave可与master上要保持一致会有很多相关问题(如`sleep()函数`，`last_insert_id()`，以及`user-definedfunctions(udf)`会出现问题).

**2.Row:**不记录sql语句上下文相关信息，仅保存哪条记录被修改。

**优点：** `binlog`中可以不记录执行的sql语句的上下文相关的信息，仅需要记录那一条记录被修改成什么了。所以rowlevel的日志内容会非常清楚的记录下每一行数据修改的细节。而且不会出现某些特定情况下的存储过程，或`function`，以及`trigger`的调用和触发无法被正确复制的问题

**缺点:**所有的执行的语句当记录到日志中的时候，都将以每行记录的修改来记录，这样可能会产生大量的日志内容,比如一条update语句，修改多条记录，则binlog中每一条修改都会有记录，这样造成binlog日志量会很大，特别是当执行`alter table`之类的语句的时候，由于表结构修改，每条记录都发生改变，那么该表每一条记录都会记录到日志中。

**3.Mixedlevel:** 是以上两种level的混合使用，一般的语句修改使用`statment`格式保存`binlog`，如一些函数，`statement`无法完成主从复制的操作，则采用`row`格式保存`binlog`,MySQL会根据执行的每一条具体的sql语句来区分对待记录的日志形式，也就是在Statement和Row之间选择一种.新版本的MySQL中队`rowlevel`模式也被做了优化，并不是所有的修改都会以`rowlevel`来记录，像遇到表结构变更的时候就会以`statement`模式来记录。至于`update`或者d`elete`等修改数据的语句，还是会记录所有行的变更。

使用以下函数的语句也无法被复制：

* `LOAD_FILE()`

* `UUID()`

* `USER()`

* `FOUND_ROWS()`

* `SYSDATE()` (除非启动时启用了`--sysdate-is-now` 选项)

同时在`INSERT ...SELECT` 会产生比 `RBR` 更多的`行级锁`

row、mixed

## 9，套接字socket文件

[linux][27]系统下 本地连接mysql可以采用linux域套接字`socket`方式 ，需要一个套接字`socket`发文件，可以有参数socket控制，一般默认在`/tmp`目录下，也可以通过如下2种方式查看：

**1， ps -eaf|grep mysql |grep socket**

```shell
[root@data01 binlog]# ps -eaf | grep mysql | grep socket

mysql 3152 1979 0 Feb28 ? 00:00:02 /usr/local/mysql/bin/mysqld--basedir=/usr/local/mysql --datadir=/home/data/mysql/data--plugin-dir=/usr/local/mysql/lib/plugin --user=mysql--log-error=/usr/local/mysql/mysqld.log --open-files-limit=8192--pid-file=/usr/local/mysql/mysqld.pid --socket=/usr/local/mysql/mysql.sock--port=3306

[root@data01 binlog]#
```

**2**，

![][32]

**3，my.cnf**

    socket = /usr/local/mysql/mysql.sock

## 10，pid文件

当mysql实例启动的时候，会将自己的`进程id`写入一个文件中，该文件即为pid文件，由参数`pid_file`控制，默认路径位于数据库目录下，可以通过以下三种方式查看：

1)、`show variableslike 'pid_file';`

![][33]

2)、`ps -eaf | grep mysql | grep pid`

3)、**My.cnf** （`pid-file = /usr/local/mysql/mysqld.pid`）

## 11，表结构文件

*.frm

*.ibd

## 12，innodb存储文件

innodb存储引擎在存储设计上模仿了[oracle][28]，该文件就是默认的表空间文件，可以通过参数`innodb_data_file_path`来进行设置，格式如下：

`innodb_data_file_path= IBdata1:128M;IBdata2:128M:autoextend`

![][34]

可以用多个文件组成一个表空间，同时制定文件的属性，

`IBdata1`和`IBdata2`位于不同的磁盘组上，则可以对性能带来一定程度的提升。文件后面的属性表示文件大小，`autoextend`表示还可以扩展。

但是如果设置了`innodb_file_per_table`为`true`后，那么表数据文件就会在 **单独的.`ibd`**文件里面，不在这个`ibdata`文件里面了。

## 13，redo文件

所有的数据库都是日志先行，**先写日志**，**再写数据文件**，所以才会有 **redo log**的规则。

默认情况下会有2个文件名称分别为`ib_logfile0` 和`ib_logfile1` ，在mysql数据库目录下可以看到这2个文件，这个对innodb存储引擎非常重要，因为它们记录了对于innodb存储引擎的事务日志。

**重做日志文件**的主要目的是：万一实例或者介质失败 `media failure`，重做日志就可以派上用场，如果数据库由于所在主机掉电导致实例失败，innodb存储引擎会使用重做日志恢复到掉电前的时刻，以此来保证数据的完整性。

每个innodb存储引擎至少有一个重做日志组，每组至少有2个重做日志文件，如默认的 **ib_logfile0 和ib_logfile1**，为了得到更高的可靠性，你可以设置多个组，也可以将每组放在不同的磁盘上面，来提高性能

**LSN logsequence number：**

递增产生的，可以唯一的标记一条redo日志，对于我们数据库故障恢复都是非常重要的，可以唯一定位数据库运行状态，至于如何定位细节，大家可以去看下redo、undo的[源码][35]，源码：在"storage/innobase/include/log0log.h"

查看参数设置：`show variables like 'innodb%log%';`

## 14，undo日志

存在于共享表空间`ibdata1`里面，有一个回滚段地址，里面存放了头信息，配置头信息，段的头信息，里面存储了与redo相反的数据更新操作，如果`rollback`的话，就把`undo`段里面数据回写到数据文件里面。

如果用了独立表空间的话，则直接存储到表私自的空间中，而不存储到共享表空间中。在innodb存储引擎中，`undo log`用来完成事务的回滚以及MVCC的功能

`Redo`与`undo`他们并不是各自独立没有关系的，他们是有关联的，交替合作来保证数据的一致性和安全性

</font>

[0]: http://blog.csdn.net/caomiao2006/article/details/52251307


[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: #t3
[13]: #t4
[14]: #t5
[15]: #t6
[16]: #t7
[17]: #t8
[18]: #t9
[19]: #t10
[20]: #t11
[21]: #t12
[22]: #t13
[23]: http://lib.csdn.net/base/mysql
[24]: http://lib.csdn.net/base/architecture
[25]: http://lib.csdn.net/base/operatingsystem
[26]: ./img/2015030409533369.png
[27]: http://lib.csdn.net/base/linux
[28]: http://lib.csdn.net/base/oracle
[29]: ./img/2015030409533470.png
[30]: ./img/2015030409533471.png
[31]: ./img/2015030409533472.png
[32]: ./img/2015030409533473.png
[33]: ./img/2015030409533474.png
[34]: ./img/2015030409533475.png
[35]: http://www.2cto.com/ym
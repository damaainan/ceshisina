## MySQL binlog日志三种模式选择及配置

来源：[https://www.linuxidc.com/Linux/2018-08/153612.htm](https://www.linuxidc.com/Linux/2018-08/153612.htm)

时间 2018-08-20 14:50:58

 
在认识binlog日志三种模式前，先了解一下解析binlog日志的命令工mysqlbinlog。mysqlbinlog工具的作用是解析mysql的二进制binlog日志内容，把二进制日志解析成可以在MySQL数据库里执行的SQL语句。binlog日志原始数据是以二进制形式存在的，需要使用mysqlbinlog工具转换成SQL语句形式。
 
mysql的binlog日志作用是用来记录mysql内部增删改等对mysql数据库有更新内容的记录（对数据库进行改动的操作），对数据库查询的语句如show，select开头的语句，不会被binlog日志记录，主要用于数据库的主从复制与及增量恢复。
 
#### 案例：
 
在对数据库进行定时备份时，只能备份到某个时间点，假如在凌晨0点进行全备了，但是在中午12点出现故障需要恢复数据，使用0点的全备只能恢复到0点时刻的数据，难道0点到12点的数据只能丢失了吗？
 
这时就是体现binlog日志重要性的时候了，需要对binlog日志进行定时推送（一分钟一次或五分钟一次，时间频率视业务场景而定）完成增量备份。当出现故障时，可以使用定时备份和增量备份恢复到故障点时刻的数据。具体的恢复方案，这里不做简述，后面再写文章来讲解。
 
binlog日志三种模式
 
#### `ROW Level`
 
记录的方式是行，即如果批量修改数据，记录的不是批量修改的SQL语句事件，而是每条记录被更改的SQL语句，因此，ROW模式的binlog日志文件会变得很“重”。
 
![][0]
 
优点：row level的binlog日志内容会非常清楚的记录下每一行数据被修改的细节。而且不会出现某些特定情况下存储过程或function，以及trigger的调用和触发器无法被正确复制的问题。
 
缺点：row level下，所有执行的语句当记录到日志中的时候，都以每行记录的修改来记录，这样可能会产生大量的日志内容，产生的binlog日志量是惊人的。批量修改几百万条数据，那么记录几百万行……
 
#### `Statement level(默认)`
 
记录每一条修改数据的SQL语句（批量修改时，记录的不是单条SQL语句，而是批量修改的SQL语句事件）。看上面的图解可以很好的理解row level和statement level两种模式的区别。
 
优点：statement模式记录的更改的SQ语句事件，并非每条更改记录，所以大大减少了binlog日志量，节约磁盘IO，提高性能。
 
缺点：statement level下对一些特殊功能的复制效果不是很好，比如：函数、存储过程的复制。由于row level是基于每一行的变化来记录的，所以不会出现类似问题
 
#### `Mixed`
 
实际上就是前两种模式的结合。在Mixed模式下，MySQL会根据执行的每一条具体的sql语句来区分对待记录的日志形式，也就是在Statement和Row之间选择一种。
 
企业场景如何选择binlog的模式
 
1、 如果生产中使用MySQL的特殊功能相对少（存储过程、触发器、函数）。选择默认的语句模式，Statement Level。
 
2、 如果生产中使用MySQL的特殊功能较多的，可以选择Mixed模式。
 
3、 如果生产中使用MySQL的特殊功能较多，又希望数据最大化一致，此时最好Row level模式；但是要注意，该模式的binlog非常“沉重”。
 
#### 查看binlog模式

```sql
    mysql> show global variables like "%binlog_format%";
     
    +---------------+-----------+
     Variable_name | Value    |
    +---------------+-----------+
     binlog_format | STATEMENT |
    +---------------+-----------+
```

#### 配置binlog日志模式
 
`vim my.cnf`（在[mysqld]模块中配置）

```cfg
    log-bin = /data/3306/mysql-bin
    binlog_format="STATEMENT"
    #binlog_format="ROW"
    #binlog_format="MIXED"
```
不重启，使配置在msyql中生效
 
    SET global binlog_format='STATEMENT';
 
 
本文永久更新链接地址： [https://www.linuxidc.com/Linux/2018-08/153612.htm][2]


[2]: https://www.linuxidc.com/Linux/2018-08/../../Linux/2018-08/153612.htm
[0]: ./img/ZJnAvyz.jpg
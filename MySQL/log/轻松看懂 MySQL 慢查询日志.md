## 学会这 2 点，轻松看懂 MySQL 慢查询日志

来源：[http://www.jianshu.com/p/d28722e07a39](http://www.jianshu.com/p/d28722e07a39)

时间 2019-01-02 16:10:59

 
MySQL中的日志包括：错误日志、二进制日志、通用查询日志、慢查询日志等等。这里主要介绍下比较常用的两个功能：通用查询日志和慢查询日志。
 
1）通用查询日志：记录建立的客户端连接和执行的语句。
 
2）慢查询日志：记录所有执行时间超过long_query_time秒的所有查询或者不使用索引的查询
 
## （1）通用查询日志
 
在学习通用日志查询时，需要知道两个数据库中的常用命令：
 
1） show variables like '%version%';

![][0]

 
2） show variables like ‘%general%’;

![][1]

 
3） show variables like ‘%log_output%’;

![][2]

 
查看当前慢查询日志输出的格式，可以是FILE（存储在数数据库的数据文件中的hostname.log），也可以是TABLE（存储在数据库中的mysql.general_log）
 
问题：如何开启MySQL通用查询日志，以及如何设置要输出的通用日志输出格式呢？

![][3]

 
日志输出的效果图如下：
 
记录到mysql.general_log表结构如下：

![][4]

 
my.cnf文件的配置如下：

![][5]

 
## （2）慢查询日志 
 
MySQL的慢查询日志是MySQL提供的一种日志记录，用来记录在MySQL中响应时间超过阈值的语句，具体指运行时间超过long_query_time值的SQL，则会被记录到慢查询日志中（日志可以写入文件或者数据库表，如果对性能要求高的话，建议写文件）。默认情况下，MySQL数据库是不开启慢查询日志的，long_query_time的默认值为10（即10秒，通常设置为1秒），即运行10秒以上的语句是慢查询语句。
 
一般来说，慢查询发生在大表（比如：一个表的数据量有几百万），且查询条件的字段没有建立索引，此时，要匹配查询条件的字段会进行全表扫描，耗时查过long_query_time，则为慢查询语句。
 
问题：如何查看当前慢查询日志的开启情况？
 
在MySQL中输入命令：
 
show variables like '%quer%';

![][6]

 
 **问题：设置MySQL慢查询的输出日志格式为文件还是表，或者两者都有？** 
 
通过命令：show variables like ‘%log_output%’;

![][7]

 
通过log_output的值可以查看到输出的格式，上面的值为FILE,TABLE。当然，我们也可以设置输出的格式为文本，或者同时记录文本和数据库表中，设置的命令如下：

![][8]

 
 **关于慢查询日志的表中的数据个文本中的数据格式分析：** 
 
慢查询的日志记录myql.slow_log表中，格式如下：

![][9]

 
 **慢查询的日志记录到mysql_slow.log文件中，格式如下：** 

![][10]

 
 **问题：如何查询当前慢查询的语句的个数？** 
 
在MySQL中有一个变量专门记录当前慢查询语句的个数：
 
输入命令：show global status like ‘%slow%’;

![][11]

 
补充知识点：如何利用MySQL自带的慢查询日志分析工具mysqldumpslow分析日志？

![][12]

 
 **具体参数设置如下：** 
 
-s 表示按何种方式排序，c、t、l、r分别是按照记录次数、时间、查询时间、返回的记录数来排序，ac、at、al、ar，表示相应的倒叙；
 
-t 表示top的意思，后面跟着的数据表示返回前面多少条；
 
-g 后面可以写正则表达式匹配，大小写不敏感。

![][13]

 
问题：实际在学习过程中，如何得知设置的慢查询是有效的？
 
很简单，我们可以手动产生一条慢查询语句，比如，如果我们的慢查询log_query_time的值设置为1，则我们可以执行如下语句：
 
select sleep(1);
 
该条语句即是慢查询语句，之后，便可以在相应的日志输出文件或表中去查看是否有该条语句。


[0]: ./img/VFfQfeR.png 
[1]: ./img/7BJnErr.png 
[2]: ./img/JJ7vYnN.png 
[3]: ./img/f22MRfJ.png 
[4]: ./img/ErQnYzM.png 
[5]: ./img/Bjaaimr.png 
[6]: ./img/FNj6Jju.png 
[7]: ./img/MFRramB.png 
[8]: ./img/b6N3u2E.png 
[9]: ./img/bqaYBrA.png 
[10]: ./img/Unmuu2m.png 
[11]: ./img/2Yb2qmJ.png 
[12]: ./img/nyYRJ3a.png 
[13]: ./img/BbI3emz.png 
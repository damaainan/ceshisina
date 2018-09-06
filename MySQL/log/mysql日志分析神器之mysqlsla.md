# [mysql日志分析神器之mysqlsla][0]

By [兰春][1]

Jul 15 2015 Updated:Jul 15 2015

**Contents**

[1. 背景][2]  
[2. mysqlsla介绍][3]  
[3. mysqlsla安装][4]  
[4. mysqlsla的基本使用][5]  
[5. mysqlsla的核心功能之filter][6]  
[6. mysqlsla的核心功能之report][7]  
[7. mysqlsla的其他高级功能： replay，user-defined-logs][8]  
[8. 最后][9]  

[hack mysqlsla 文档][10]

## 背景

* **什么是mysqlsla？**

Mysqlsla 是daniel-nichter 用perl 写的一个脚本，专门用于处理分析Mysql的日志而存在。
* **mysqlsla 能解决什么问题？**

作为一名Mysql DBA，日常工作中处理日志是再正常不过的事情了。 通过Mysql的日志主要分为：General log，slow log，binary log三种。通 过query日志，我们可以分析业务的逻辑，业务特点。通过slow log，我们可以找到服务器的瓶颈。通过binary log，我们可以恢复数据。Mysqlsla 可以处理其中的任意日志，这也是我喜欢它的最主要原因之一。

* **为什么选择mysqlsla？**

分析mysql日志的工具当然不止mysqlsla一种，据我所知的有：


  * mysqldumpslow
  * mysqlbinlog
  * myprofi
  * mysql-explain-slow-log
  * mysql-log-filter
  * pt-query-digest
  * mysqlsla

下面做一个对比：

工具 | 一般统计 | 高级统计 | 语言 | 优势 | 针对log
-|-|-|-|-|- 
mysqldumpslow | 支持 | 不支持 | perl | mysql官方自带 | slow 
myprofi | 支持 | 不支持 | php | 简单 | slow 
mysql-log-filter | 支持 | 部分支持 | python | 简单 | slow 
mysql-explain-slow-log | 支持 | 不支持 | perl  | 无 | slow 
mysqlbinlog | 支持 | 不支持 | 二进制 | mysql官方自带 | binary log 
mysqlsla | 支持 | 支持 | perl | 总能强大，使用简单，自定义能力强 | 所有日志，包括自定义日志 
pt-query-digest | 支持 | 支持 | perl |总能强大，使用简单，自定义能力强 | 所有日志，包括自定义日志 

根据以上特点，最适合的工具非 mysqlsla 与 pt-query-digest 莫属。 mysqlsla与pt-query-digest的作者是同一个人。现在主打开发pt系列工具。由于个人已经使用过mysqlsla 三年，被其强大的功能所吸引，可以完成DBA工作的99%的需求，所以这里详细介绍mysqlsla的使用，并对现有mysqlsla的一些不足，进行二次开发。

## mysqlsla介绍

- - -

大致将mysqlsla 分解出来分为： Mysqlsla的安装，Mysqlsla的功能，Mysqlsla的用法，Mysqlsla 的filter，Mysqlsla的report，Mysqlsla的replay，Mysqlsla的user-defined-Logs。 其中最核心的当然是：filter以及report。

大致流程是： LOGS(UDL,defalut) -> parse -> filter -> sort -> reprot -> replay .

![test][11]

这里说的，Mysqlsla可以处理任意日志。默认可以处理mysql的三种常见日志。

如：General log，binary log，slow log

* Slow log： mysqlsla ­lt slow slow.log
* General log: mysqlsla ­lt general general.log
* Binary log: mysqlbinlog bin.log | mysqlsla ­lt binary ­

## mysqlsla安装

- - -

1. Download mysqlsla­2.03.tar.gz
1. tar -xvfz mysqlsla­2.03.tar.gz
1. cd mysqlsla­2.03
1. perl Makefile.PL
1. make
1. make install

## mysqlsla的基本使用

- - -

* **.mysqlsla Config File**

`~.mysqlsla` 这个文件，类似Mysql 里面的`配置文件.cnf`。 mysqlsla 启动都会读取这个全局配置文件。如：

```
atomic­-statements 
statement­-filter=+UPDATE,INSERT
```
注意点： 这里的参数，不能加 — 或者 -

* **基本命令和使用**

这里罗列一下在Mysql工作中最最最常用的命令，使用率在80%
```
* ­­--log­-type (-­lt) TYPE LOGS

    用于指定解析的是什么类型的日志，不过放心，即便你不指定，它默认也会自动去判断基本的日志，slow，general，binary，msl or udl。

* ­­--db-­inheritance

    这个参数还是通过源码发现的，之前没认真读文档。这个参数十分重要，之前对这个参数用的不多，但是如果你不指定，解析出来的日志中，很有可能对应的SQL语句，找不到database来源。

* ­­--explain (­-ex)    

    默认是disable的，并且必须report格式为standard。作用就是对每一条uniq的SQL进行explain分析，非常实用。

* --­­grep PATTERN

    正则匹配功能。您可以对你的report做任意的正则匹配。比如：我想查关于‘table_lc’,'table_cl'这两张表的所有分析结果。

    普通的方式，是没有办法查到的，但是您可以这样做：

    mysqlsla --grep '(\btable_lc\b)|(\btable_cl\b)'  db10-037.log

* --­­meta-­filter (­-mf) CONDTIONS

    这是filter中的一种，这里简单介绍，详细介绍看filter章节。

    这里的meta，指的是meta-property，meta-property后面介绍。典型的就是：c_sum，指的是SQL数量的总和。

    基本使用情况： [meta][op][value]

    特别要注意这里的op， 没有别的，就三种， >,<,=. 之前自以为事，>=，死活得不到自己想要的结果。

    这里[op]有个特殊用法：t_sum between 10 and 100 ， 可以这样写： 't_sum>10,t_sum<100'

    举例说明： 我想查看ark_db的所有结果

        mysqlsla -mf 'db=ark_db' --top 100 slow_1.log slow_2.log

­­* --statement-­filter (­-sf) CONDTIONS

    这是filter中的另一种

    别的不说，直接看使用形式：[+-][type],[type]

    [TYPE] 的值就是 :SELECT,CREATE,DROP,UPDATE,INSERT,etc.

    举例说明：如果我只想查看select，create类型的SQL。

        mysqsla -sf '+SELECT,CREATE' slow.log

    举例说明：如果我想查看除了select以外的所有结果。

        mysqlsla -sf '-SELECT' 类型的SQL。

* --top N

    显示默认降序的top N， default 为10.

* --sort META

    根据Meta的值，进行排序。默认slow log的meta 为t_sum，至于t_sum是什么，请看filter章节。

* ­­--report-­format (-­rf) FILE

    这里可以自定义报表。但是必须按照固定的格式，以便mysqlsla来解析。

    如：

    (extra command line options)

     HEADER

    (header line format) 

    (header line values)

    REPORT

    report line format

    report line values

    这个非常有用，尤其是后期可以用脚本来处理自己自定义的报表，非常方便。这也是其强大功能的一个体现。

* ­­--reports (­-R) REPORTS

    可以指定以某种格式进行输出报表。

    固定报表形式有：standard,time-­all,print-­unique,print-­all,dump

    必须注意的地方：如果你选择的是time-­all，time­-each­-query，这里会默认是safetySQL statement filter of"+SELECT,USE"。

    但是，如果你自定义了-statment-fileter，那么这些dml语句就会在生产环境中真实的执行，会污染线上的数据，非常危险，请特别注意和小心。

* --­­udl­-format (­-uf) FILE

    可以自定义log，让mysqlsla来解析。这个比较复杂，在后面的章节讲。

* ­­--replay FILE

    可以重新replay file里面的SQL。实用，但是我一般不会用，担心数据安全。

总结： 基本上，以上的命令，可以解决你50%的需求，而且是非常常用的功能，自己去试试吧。

这里可以简单举几个例子：

1）列出以用户是usr_rx，含有ac_开头的，非SELECT类型的 前100000条记录。 --默认m/P/io 大小写敏感

    mysqlsla --grep '\bac_.*\b' -mf user='usr_rx' -sf '-SELECT' --top 100000  slow.log

2) 列出含有broker_system_messages_bj 或者 broker_system_messages_oth的记录. --关键是需要打上括号。

    mysqlsla --grep '(\bbroker_system_messages_bj\b)|(\bbroker_system_messages_oth.*\b)'  slow.log

3) 列出ajk_propertys的所有查询，且按照SQL的rows_exam排序。

    mysqlsla --grep '\bajk_propertys\b' -sf '+SELECT'  --sort='re_sum'  --db-inheritance slow.log
```
## mysqlsla的核心功能之filter

- - -

filter 分为两种：

    * meta-property filter
    * statment filter
    

statment ， 上面已经详细介绍过，这里详细介绍meta-property filter。  
[meta][op][value] ， 这里详细介绍什么是meta,meta有哪些值。  
由于种类实在是太多，所以这里也只会列出工作中，最最最常用的meta参数，基本可以解决99%的需求。

log类型 | meta | 解释 | 限制 
-|-|-|-
all | c_sum | SQL次数总和 | 无 
all | db | db名称 | 只能用作meta-filter，不能用作sort 
all | exec | 真实执行时间 | 只能用做sort，不能filter 
all | exec_sum | c_sum*exec |只能作用sort，不能filter 
slow | host | 主机名 | 只能用作meta-filter，不能用作sort 
slow | ip | ip地址 | 只能用作meta-filter，不能用作sort 
slow | l_avg | 锁的平均等待时间 | 无 
slow | re_sum | rows_examined的总和 | 无 
slow | re_avg | rows_examined的平均值 | 无 
slow | rs_sum | rows_sent的总和 | 无 
slow | rs_avg | rows_sent的平均值 | 无 
slow | t_sum | SQL执行时间的总和 | 无 
slow | t_avg | SQL执行时间的平均值 | 无 
slow | user | 用户名 | 无 
general | cid | 连接id | 无 
general | host | 主机名 | 无 
general | user | 用户名 | 无 
binary | ext | 执行时间 | 无 
udl | 无 | 无 | 无 

详细过滤的过程，请参考 MySQL::Log::ParseFilter 模块。  
上列出的meta-property name，不仅仅用于filter，更加可以用于sort，所以sort我就不重复，使用规则请参考filter。

## mysqlsla的核心功能之report

- - -

基本格式为：standard，但是你可以自己覆盖掉standard格式输出。这里的report format，为了兼容所有人，这里都是用sprintf 进行输出，而不是用perl 自己的格式，所以通用性非常的强。

比如用—report-­format （-rf）FILE 可以替换。 

然后基本模板如下：
```
(extra command line options)

 HEADER

(header line format) 

(header line values)

REPORT

report line format 

report line values
```
一个自定义的slow标准模板
```
-nthp

HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

Grand Totals: Time %s s, Lock %s s, Rows sent %s, Rows Examined %s

gt_t:short gt_l:short gt_rs:short gt_re:short

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count         : %s  (%.2f%%)

c_sum:short c_sum_p

Time          : %s total, %s avg, %s to %s max  (%.2f%%)

t_sum:micro t_avg:micro t_min:micro t_max:micro t_sum_p

? %3s%% of Time : %s total, %s avg, %s to %s max

nthp:op t_sum_nthp:micro t_avg_nthp:micro t_min_nthp:micro t_max_nthp:micro

? Distribution : %s

t_dist

Lock Time (s) : %s total, %s avg, %s to %s max  (%.2f%%)

l_sum:micro l_avg:micro l_min:micro l_max:micro l_sum_p

? %3s%% of Lock : %s total, %s avg, %s to %s max

nthp:op l_sum_nthp:micro l_avg_nthp:micro l_min_nthp:micro l_max_nthp:micro

Rows sent     : %s avg, %s to %s max  (%.2f%%)

rs_avg:short rs_min:short rs_max:short rs_sum_p

Rows examined : %s avg, %s to %s max  (%.2f%%)

re_avg:short re_min:short re_max:short re_sum_p

Database      : %s

db

Users         : %s

users

?Table:#rows   : %s

tcount

?Table schemas : %s

tschema

?EXPLAIN       : %s

explain

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
一个自定义的general log标准模板
```
HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count         : %s (%.2f%%)

c_sum:short c_sum_p

Connection ID : %d

cid

Database      : %s

db

Users         : %s

users

?Table:#rows   : %s

tcount

?Table schemas : %s

tschema

?EXPLAIN       : %s

explain

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
一个自定义的binary log标准模板
```
HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count             : %s (%.2f%%)

c_sum:short c_sum_p

Connection ID     : %d

cid

Server ID         : %d

sid

Error code        : %d

err

Execution Time (s): %d total, %d avg, %d to %d max

ext_sum ext_avg ext_min ext_max

? %3s%% of Ex Time: %d total, %d avg, %d to %d max

nthp:op ext_sum_nthp ext_avg_nthp ext_min_nthp ext_max_nthp

Database          : %s

db

Users             : %s

users

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
一个自定义的msl log标准模板
```
HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

Grand Totals: Time %.3f s, Lock %.3f s, Rows sent %s, Rows Examined %s

gt_t gt_l gt_rs:short gt_re:short

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count         : %s  (%.2f%%)

c_sum:short c_sum_p

Time          : %s total, %s avg, %s to %s max  (%.2f%%)

t_sum:micro t_avg:micro t_min:micro t_max:micro t_sum_p

? %3s%% of Time : %s total, %s avg, %s to %s max

nthp:op t_sum_nthp:micro t_avg_nthp:micro t_min_nthp:micro t_max_nthp:micro

Lock Time     : %s total, %s avg, %s to %s max  (%.2f%%)

l_sum:micro l_avg:micro l_min:micro l_max:micro l_sum_p

? %3s%% of Lock : %s total, %s avg, %s to %s max

nthp:op l_sum_nthp:micro l_avg_nthp:micro l_min_nthp:micro l_max_nthp:micro

Rows sent     : %s avg, %s to %s max  (%.2f%%)

rs_avg:short rs_min:short rs_max:short  rs_sum_p

Rows examined : %s avg, %s to %s max  (%.2f%%)

re_avg:short re_min:short re_max:short  re_sum_p

Database      : %s

db

Users         : %s

users

?Table:#rows   : %s

tcount

?Table schemas : %s

tschema

?EXPLAIN       : %s

explain

?QC hit        : %d%% (%d)

qchit_t_p qchit_t

?Full scan     : %d%% (%d)

fullscan_t_p fullscan_t

?Full join     : %d%% (%d)

fulljoin_t_p fulljoin_t

?Tmp table     : %d%% (%d)

tmptable_t_p tmptable_t

?Disk tmp table: %d%% (%d)

disktmptable_t_p disktmptable_t

?Filesort      : %d%% (%d)

filesort_t_p filesort_t

?Disk filesort : %d%% (%d)

diskfilesort_t_p diskfilesort_t

?Merge passes  : %s total, %s avg, %s to %s max

merge_sum:short  merge_avg:short merge_min:short merge_max:short

?IO r ops      : %s total, %s avg, %s to %s max  (%.2f%%)

iorops_sum:short iorops_avg:short iorops_min:short iorops_max:short iorops_sum_p

?IO r bytes    : %s total, %s avg, %s to %s max  (%.2f%%)

iorbytes_sum:short iorbytes_avg:short iorbytes_min:short iorbytes_max:short iorbytes_sum_p

?IO r wait     : %s total, %s avg, %s to %s max  (%.2f%%)

iorwait_sum:micro iorwait_avg:micro iorwait_min:micro iorwait_max:micro iorwait_sum_p

?Rec lock wait : %s total, %s avg, %s to %s max  (%.2f%%)

reclwait_sum:micro reclwait_avg:micro reclwait_min:micro reclwait_max:micro reclwait_sum_p

?Queue wait    : %s total, %s avg, %s to %s max  (%.2f%%)

qwait_sum:micro qwait_avg:micro qwait_min:micro qwait_max:micro qwait_sum_p

?Pages distinct: %s total, %s avg, %s to %s max  (%.2f%%)

pages_sum:short pages_avg:short pages_min:short pages_max:short pages_sum_p

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
一个自定义的udl log标准模板
```mysql
HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count         : %s (%.2f%%)

c_sum:short c_sum_p

Database      : %s

db

?Table:#rows   : %s

tcount

?Table schemas : %s

tschema

?EXPLAIN       : %s

explain

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
这也就是平时看到的默认格式。个人觉得这个功能非常好用，当然标准格式就已经满足90%的需求，当然对于比较特殊的需求，可以做特殊的格式化输出。  
非常强大，谁用谁知道。

## mysqlsla的其他高级功能： replay，user-defined-logs

- - -

这些高级功能，我用的比较少，所以这里不多介绍。如果大家实在想用，可以参考官方文档。

## 最后

- - -

这里，基本上已经将mysqlsla的使用都介绍了一遍，如果你还没有使用，那就赶紧使用吧。  
如果还想知道更多的内如：

1）mysqlsla 是如何进行filter的？

2）mysqlsla 是如何进行abstract-in SQL的？

3）mysqlsla 是如何进行SQL解析的？

4）mysqlsla 的内部工作流程和原理？

5）mysqlsla 的bug list，以及如何修复？

敬请关注下一节[mysqlsla源码分析][12]。

[0]: http://keithlan.github.io/2015/07/15/mysqsla/
[1]: https://Keithlan.github.io
[2]: #背景
[3]: #mysqlsla介绍
[4]: #mysqlsla安装
[5]: #mysqlsla的基本使用
[6]: #mysqlsla的核心功能之filter
[7]: #mysqlsla的核心功能之report
[8]: #mysqlsla的其他高级功能：_replay，user-defined-logs
[9]: #最后
[10]: http://hackmysql.com/mysqlsla
[11]: ./img/mysqlsla_pic.png
[12]: http://keithlan.github.io/2015/07/14/mysqlsla_source_read/
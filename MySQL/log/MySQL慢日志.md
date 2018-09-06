## 关于MySQL慢日志，你想知道的都在这

 2017-07-20 11:21  阅读 1.2k  评论 0

<font face=微软雅黑>

社区广播：运维派（Yunweipai.com）是国内最早成立的IT运维社区，欢迎大家[**投稿**][0]，让运维人不再孤寂的成长！

作者介绍

**邹鹏，**现任职于腾讯云数据库团队，负责腾讯云数据库MySQL中间件研发，多年的数据库、网络安全研发经验，对云计算平台的网络、计算、存储、安全有着深入的了解，在MySQL的高可用、高可靠、中间件方面有丰富的经验。

![MySQL][1]

**目录：**

1. 什么是慢日志？
1. 什么情况下产生慢日志？
1. 慢日志相关参数
1. 慢日志输出内容
1. 慢日志分析工具
1. 慢日志的清理与备份

## **一、什么是慢日志？** 

MySQL的慢查询日志是MySQL提供的一种日志记录，它用来记录在MySQL中响应时间超过阀值的语句，具体指运行时间超过long_query_time值的SQL，则会被记录到慢查询日志中。long_query_time的默认值为10，意思是运行10s以上的语句。

默认情况下，MySQL数据库并不启动慢查询日志，需要我们手动来设置这个参数，当然，如果不是调优需要的话，一般不建议启动该参数，因为开启慢查询日志或多或少会带来一定的性能影响。慢查询日志支持将日志记录写入文件，也支持将日志记录写入数据库表。

* 5.6官方说明：https://dev.mysql.com/doc/refman/5.6/en/slow-query-log.html
* 5.7官方说明：https://dev.mysql.com/doc/refman/5.7/en/slow-query-log.html

## **二、什么情况下产生慢日志？** 

![慢日志][2]

看图说话，有很多开关影响着慢日志的生成，相关的参数后面会挨个说明。从上图可以看出慢日志输出的内容有两个，第一执行时间过长（大于设置的`long_query_time`阈值）；第二未使用索引，或者未使用最优的索引。

这两种日志默认情况下都没有打开，特别是未使用索引的日志，因为这一类的日志可能会有很多，所以还有个特别的开关`log_throttle_queries_not_using_indexes`用于限制每分钟输出未使用索引的日志数量。

关键代码如下：

![代码][3]

Slow log调用栈（MySQL 5.6.34 ）：

![Slow log][4]

## **三、慢日志相关参数** 

![参数][5]

以上应该是最完整的和慢日志相关的所有参数，大多数参数都有前置条件，所以在使用的时候可以参照上面的流程图。

5.6官方文档：

1、<https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html>

2、<https://dev.mysql.com/doc/refman/5.6/en/server-options.html>

## **四、慢日志输出内容** 

![慢日志][6]

第一行：标记日志产生的时间，准确说是SQL执行完成的时间点，改行记录每一秒只打印一条。

第二行：客户端的账户信息，两个用户名（第一个是授权账户，第二个为登录账户），客户端IP地址，还有mysqld的线程ID。

第三行：查询执行的信息，包括查询时长，锁持有时长，返回客户端的行数，扫描行数。通常我需要优化的就是最后一个内容，尽量减少SQL语句扫描的数据行数。

第四行：通过代码看，貌似和第一行的时间没有区别。

第五话：最后就是产生慢查询的SQL语句。

`–log-short-format=true`：如果mysqld启动时指定了`–log-short-format`参数，则不会输出第一、第二行。

`log-queries-not-using-indexes=on`

`log_throttle_queries_not_using_indexes > 0` :

如果启用了以上两个参数，每分钟超过`log_throttle_queries_not_using_indexes`配置的未使用索引的慢日志将会被抑制，被抑制的信息会被汇总，每分钟输出一次。

格式如下：

![][7]

## **五、慢日志分析工具** 

1. 官方自带工具： `mysqldumpslow`
1. 开源工具：mysqlsla
1. `percona-toolkit`：工具包中的`pt-query-digest`工具可以分析汇总慢查询信息，具体逻辑可以看`SlowLogParser`这个函数。

总的来说，MySQL的日志内容本身不复杂，上面3个工具都是用perl脚本实现，代码行数不超过200行，有兴趣的同学也可以自己尝试着解析下。

详情可以参阅下这篇文章：

* 《MySQL 慢查询设置和分析工具 》： 

https://flyerboy.github.io/2016/12/23/mysql_slow/

以上工具可以支撑慢日志的常用统计，但是当我们需要做到SQL级别的统计时，我们还需要取解析SQL把参数提取出来。

## **六、慢日志的清理与备份** 

删除：直接删除慢日志文件，执行`flush logs`（必须的）。

备份：先用`mv`重命名文件（不要跨分区），然后执行`flush logs`（必须的）。

另外修改系统变量`slow_query_log_file`也可以立即生效；

执行`flush logs`，系统会先`close`当前的句柄，然后重新open；mv , rm日志文件系统并不会报错，具体的原因可以Google下`linux i_count i_nlink` ；

</font>

[0]: http://www.yunweipai.com/tougao
[1]: ./img/1.webp_28.jpg
[2]: ./img/2.webp_25.jpg
[3]: ./img/3.webp_22.jpg
[4]: ./img/4.webp_24.jpg
[5]: ./img/5.webp_21.jpg
[6]: ./img/6.webp_21.jpg
[7]: ./img/7.webp_21.jpg
# MySQL运维实战（二）之 巧用P_S解决账号host访问的荣耀王者之路

 时间 2017-09-13 11:29:41  Focus on MySQL

原文[http://keithlan.github.io/2017/09/12/P_S_user_host/][1]


## 背景

* 一个MySQL实例中，如何验证一个账号上面是否还有访问？
* 一个MySQL实例中，如何验证某个业务ip是否还有访问？

## 倔强青铜级别

* 打开general log
```
    优点： 全量
    缺点： 性能差
```

## 秩序白银级别

* 打开slow log,设置long_query_time = 0
```
    优点： 全量
    缺点： 性能比较差
```

## 荣耀黄金级别

* tshark | tcpdump | tcpcopy
```
    tshark -i any dst host ${ip} and dst port 3306 -l -d tcp.port==3306,mysql -T fields -e frame.time -e 'ip.src'  -e 'mysql.query' -e 'mysql.user' -e 'mysql.schema'
    
    优点：全量*95%
    缺点：性能比较差，使用不方便
```

## 尊贵铂金级别

* 使用P_S
```
    * 使用案例
    
    
    dba:performance_schema> select USER,EVENT_NAME,COUNT_STAR,now() as time from events_statements_summary_by_user_by_event_name where EVENT_NAME in ('statement/sql/select','statement/sql/update','statement/sql/delete','statement/sql/insert','statement/sql/replace') and COUNT_STAR > 0;
    +------+----------------------+------------+---------------------+
    | USER | EVENT_NAME | COUNT_STAR | time |
    +------+----------------------+------------+---------------------+
    | dba  | statement/sql/select |        143 | 2017-09-04 18:02:33 |
    | repl | statement/sql/select | 10 | 2017-09-04 18:02:33 |
    +------+----------------------+------------+---------------------+
    2 rows in set (0.00 sec)
    
    dba:performance_schema> select HOST,EVENT_NAME,COUNT_STAR,now() as time from events_statements_summary_by_host_by_event_name where EVENT_NAME in ('statement/sql/select','statement/sql/update','statement/sql/delete','statement/sql/insert','statement/sql/replace') and COUNT_STAR > 0;
    +-----------+----------------------+------------+---------------------+
    | HOST | EVENT_NAME | COUNT_STAR | time |
    +-----------+----------------------+------------+---------------------+
    | localhost | statement/sql/select | 22 | 2017-09-04 18:02:35 |
    +-----------+----------------------+------------+---------------------+
    1 row in set (0.00 sec)
```

* 对比
```
    优点：全量，性能基本无影响
    缺点：无法抓到对应的SQL
```

## 永恒钻石级别

* 巧用P_S
```
    将每1分钟，5分钟，10分钟的P_S快照映射到对应的table，永久存下来，进行统计分析
    
    优点：全量，性能基本无影响，且时间更加细粒度化
    缺点：无法抓到对应的SQL，需要额外开发成本
```

## 最强王者

* 巧用P_S + tshark
```
    1. P_S分段，找到具体有访问的时间段 $time
    2. 在$time时间段内，去用tshark 抓取SQL相关info
```

[1]: http://keithlan.github.io/2017/09/12/P_S_user_host/

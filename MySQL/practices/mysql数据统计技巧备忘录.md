## mysql数据统计技巧备忘录

来源：[http://www.cnblogs.com/yougewe/p/9103591.html](http://www.cnblogs.com/yougewe/p/9103591.html)

时间 2018-05-30 15:46:00

 
mysql 作为常用数据库，操作贼六是必须的，对于数字操作相关的东西，那是相当方便，本节就来拎几个统计案例出来供参考！
 
order订单表,样例如下：
 
```sql


CREATE TABLE `yyd_order` (
　　`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
　　`user_id` int(11) NOT NULL,
　　`order_nid` varchar(50) NOT NULL,
　　`status` varchar(50) NOT NULL DEFAULT '0',
　　`money` decimal(20,2) NOT NULL DEFAULT '0.00',
　　`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
　　`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
　　PRIMARY KEY (`id`),
　　KEY `userid` (`user_id`),
　　KEY `createtime` (`create_time`),
　　KEY `updatetime` (`update_time`)
) ENGINE=InnoDB;


```
 
1. 按天统计进单量，date_format
 
```sql


SELECT DATE_FORMAT(t.`create_time`, '%Y-%m-%d') t_date, COUNT(1) t_count FROM t_order t WHERE t.`create_time` > '2018-05-11' GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d');


```
 
2. 按小时统计进单量
 
```sql


SELECT DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H') t_hour, COUNT(1) t_count FROM t_order t WHERE t.`create_time` > '2018-05-11' GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H');


```
 
3. 同比昨天进单量对比，order by h, date
 
```sql


SELECT DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H') t_date, COUNT(1) t_count FROM yyd_order t WHERE t.`create_time` > '2018-05-11' GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H')
ORDER BY DATE_FORMAT(t.`create_time`, '%H'),DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H');


```
 
![][0]
 
4. 环比上周同小时进单，date in ，order by
 
```sql


SELECT DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H') t_date, COUNT(1) t_count FROM yyd_order t WHERE
  DATE_FORMAT(t.`create_time`,'%Y-%m-%d') IN ('2018-05-03','2018-05-11') GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H')
ORDER BY DATE_FORMAT(t.`create_time`, '%H'),DATE_FORMAT(t.`create_time`, '%Y-%m-%d %H');


```
 
![][1]
 
5. 按照remark字段中的返回值进行统计，group by remark like ...
 
```sql


SELECT DATE_FORMAT(t.`create_time`, '%Y-%m-%d') t_date, COUNT(1) t_count, SUBSTRING_INDEX(SUBSTRING_INDEX(t.`msg`, '{', -1), '}', 1) t_rsp_msg FROM 
  cmoo_tab t WHERE t.`create_time` > '2018-05-17' AND t.`rsp_msg` LIKE '%nextProcessCode%C9000%'
  GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d'),SUBSTRING_INDEX(SUBSTRING_INDEX(t.`rsp_msg`, '{', -1), '}', 1);


```
 
![][2]
 
6. 第小时的各金额的区间数统计，sum if 1 0，各自统计
 
```sql


SELECT DATE_FORMAT(t.create_time,'%Y-%m-%d') t_date, SUM(IF(t.`amount`>0 AND t.`amount`<1000, 1, 0)) t_0_1000, SUM(IF(t.`amount`>1000 AND t.`amount`<5000, 1, 0)) t_1_5000,
　　SUM(IF(t.`amount`>5000, 1, 0)) t_5000m FROM mobp2p.`yyd_order` t WHERE t.`create_time` > '2018-05-11' GROUP BY DATE_FORMAT(t.`create_time`, '%Y-%m-%d');


```
 
![][3]
 
7. 按半小时统计进单量，floor h / 30，同理10分钟，20分钟
 
8. 成功率，失败率，临时表 join on hour
 
9. 更新日志表中最后条一条日志状态值到信息表中状态，update a join b on xx set a.status=b.status where tmp group by userid tmp2，注意索引
 
```sql


UPDATE t_order t0 LEFT JOIN (SELECT * FROM (SELECT * FROM t_order_log t WHERE t.create_time>'2018-05-11' ORDER BY id DESC) t1
  GROUP BY t1.user_id ) ON t.user_id=t2.user_id SET t0.`status`=t2.status WHERE t0.`create_time`>'2018-05-11' AND t0.`status`=10; 
 

```
 
10. 备份表，create table as select xxx where xxx
 
```sql


CREATE TABLE t_m AS SELECT * FROM t_order;


```
 
11. 纯改备注不锁表，快，类型全一致
 


[0]: ./img/VR7RZfm.png 
[1]: ./img/2AZNNnB.png 
[2]: ./img/ba636fz.png 
[3]: ./img/ZfmQryj.png 
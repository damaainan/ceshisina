


MySQL去重方法可以使用函数distinct或者group by :

```sql

(1) select distinct(customer_id) from user_table;  
  
(2) select customer_id from user_table group by customer_id;  
  
(3)拓展：查询某时间段登录的人数  
select count(distinct(customer_id)) from login_logs where logtime......  
```

  
left join使用例子：

```sql
select 20160102 AS logTime, count(regNum) as aa,platform  from   
(  
  select imei as regNum,operatingsystem as platform  
    from RegLogs_20160102  
    where operatingsystem  >=1 and operatingsystem<=3  
    group by operatingsystem,regNum  
) t1  
left join  
(  
  select imei as logNum  
    from LoginLogs_20160102  
    where operatingsystem  >=1 and operatingsystem<=3  
    group by operatingsystem,logNum  
) t2  
on t1.regNum = t2.logNum where t1.regNum is not null and t2.logNum is not null  
group by platform;  

```


# 记一次mysql千万订单汇总查询优化

 时间 2017-10-27 17:59:00 

原文[http://www.cnblogs.com/wappin/p/7725049.html][1]


公司订单系统每日订单量庞大，有很多表数据超千万。公司SQL优化这块做的很不好，可以说是没有做，所以导致查询很慢。

正题

节选某个功能中的一句 **SQL EXPLAIN** 查看执行计划 

#### EXPLAIN + SQL 查看SQL执行计划

![][3]

#### 一个索引没用到，受影响行接近2000万，难怪会慢。

#### 原来的SQL打印出来估计有好几张A4纸，我发个整理后的简版。

```sql
    SELECT
      COUNT(t.w_order_id) lineCount,
      SUM(ROUND(t.feel_total_money / 100, 2)) AS lineTotalFee,
      SUM(ROUND(t.feel_fact_money / 100, 2)) AS lineFactFee
    FROM
      w_orders_his t
    WHERE 1=1
    AND DATE_FORMAT(t.create_time, '%Y-%m-%d') >= STR_TO_DATE(#{beginTime},'%Y-%m-%d') 
    AND DATE_FORMAT(t.create_time, '%Y-%m-%d') <= STR_TO_DATE(#{endTime},'%Y-%m-%d')
    AND t.pay_state = #{payState}
    AND t.store_id LIKE '%#{storeId}%'
    limit 0,10
```

这条sql需求是在两千万的表中捞出指定时间和条件的订单进行总数总金额汇总处理。

优化sql需要根据公司的业务，技术的架构等，且针对不同业务每条 **SQL** 的优化都是有差异的。 

#### 优化点1：

```sql
    AND DATE_FORMAT(t.create_time, '%Y-%m-%d') >= STR_TO_DATE(#{beginTime},'%Y-%m-%d') 
    　　AND DATE_FORMAT(t.create_time, '%Y-%m-%d') <= STR_TO_DATE(#{endTime},'%Y-%m-%d')
```

我们知道sql中绝对要减少函数的使用，像左边 **DATE_FORMAT(t.create_time, '%Y-%m-%d')** 是绝对禁止使用的，如果数据库有一百万数据那么就会执行一百万次函数，非常非常影响效率。右边 **STR_TO_DATE(#{beginTime},'%Y-%m-%d')** 的函数会执行一次，但还是不建议使用函数。 

所以去掉函数直接使用 **>=,<=** 或 **BETWEEN AND** 速度就会快很多，但有的数据库设计时间字段只有日期没有时间，所以需要在日期后面拼接时间如： 

#### "2017-01-01" + " 00:00:00"。

更好的办法是用时间戳，数据库中存时间戳，然后拿时间戳去比较，如：**`BETWEEN '开始时间时间戳' AND '结束时间时间戳'`**

#### 优化点2：

    AND t.store_id LIKE '%#{storeId}%'
    

这句使用了 **LIKE** 并且前后匹配，前后匹配会导致索引失效，一般情况下避免使用，应该改成 **AND t.store_id LIKE '#{storeId}%'**

#### 优化点3：

一般利用好索引，根据主键、唯一索引查询某一条记录，就算上亿数据查询也是非常快的。但这条sql需要查询数据统计需要用到 **COUNT** 和 **SUM** ，所以可以建立联合索引。 

联合索引有一点需要注意：**`key index (a,b,c)`**可以支持a | a,b| a,b,c 3种组合进行查找，但不支持 b,c进行查找 ，当最左侧字段是常量引用时，索引就十分有效。

所以把必要字段排放在左边 key index(create_time,w_order_id,feel_total_money,feel_fact_money,payState,storeId)

结果

优化之前大概几分钟，现在是毫秒级。其实改的东西也不多，避免在语句上踩雷，善用 **EXPLAIN** 查询 **SQL** 效率。 

有时间我会举点别的 **SQL** 优化的例子 

#### 说几点平常可以优化的地方

* JOIN 后的的条件必须是索引，最好是唯一索引，否则数据一旦很多会直接卡死
* 一般禁止使用UNIION ON，除非UNION ON 前后的记录数很少
* **禁止使用OR**
* 查总数使用COUNT(*)就可以，不需要COUNT(ID)，MYSQL会自动优化
* 数据库字段设置 NOT NULL，字段类型 INT > VARCHAR 越小越好
* **禁止SELECT * ，需要确定到使用的字段**
* **一般情况不在SQL中进行数值计算**
* **SQL要写的简洁明了**

参考 

EXPLAIN **type（从上到下，性能从差到好）**

* **all 全表查询**
* **index 索引全扫描**
* **range 索引范围扫描**
* ref 使用非唯一或唯一索引的前缀扫描,返回相同值的记录
* **eq_ref 使用唯一索引，只返回一条记录**
* const,system 单表中最多只有一行匹配,根据唯一索引或主键进行查询
* **null 不访问表或索引就可以直接得到结果**

#### MYSQL 五大引擎

* ISAM ：读取快,不占用内存和存储资源。 不支持事物,不能容错。
* **MyISAM ：读取块，扩展多。**
* HEAP ：驻留在内存里的临时表格,比ISAM和MyISAM都快。数据是不稳定的,关机没保存,数据都会丢失。
* **InnoDB ：支持事物和外键，速度不如前面的引擎块。**
* Berkley（BDB） ：支持事物和外键，速度不如前面的引擎块。

#### 一般需要事物的设为InnoDB，其他设为MyISAM


[1]: http://www.cnblogs.com/wappin/p/7725049.html
[3]: https://img0.tuicool.com/3e67n26.png
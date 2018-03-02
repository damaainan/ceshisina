## 每日一博 | SQL 巨慢之 utf8mb4 的隐藏问题

来源：[https://my.oschina.net/qixiaobo025/blog/1627067](https://my.oschina.net/qixiaobo025/blog/1627067)

时间 2018-03-02 08:07:28

 
## 背景
 
某天 小伙伴发现一条sql巨慢无比 该sql十分长 我们来看一下

```sql
select
      '1' AS type,
      '项目' AS type_name,
      sc.name AS businessName,
      SUM(IFNULL(a.service_actual_cash,0)) AS cash,
      SUM(IFNULL(a.service_actual_card,0)) AS bank_card,
      SUM(IFNULL(a.service_actual_wechat,0)) AS wechat,
      SUM(IFNULL(a.service_actual_alipay,0)) AS alipay,
      SUM(IFNULL(a.service_actual_bank_transfer,0)) AS bank_transfer,
      SUM(IFNULL(a.service_actual_account,0)) AS account,
      SUM(IFNULL(a.service_actual_coupon,0)) AS coupon,
      SUM(IFNULL(a.service_actual_czk,0)) AS czk,
      SUM(IFNULL(a.service_actual_jck,0)) AS jck,
      SUM(IFNULL(a.service_actual_tck,0)) AS tck,
      SUM(IFNULL(a.service_actual_hyk,0)) AS hyk,
      SUM(IFNULL(a.service_actual_owe,0)) AS owe
      FROM
      (SELECT sc.name,sc.id FROM dim_service_category sc
      INNER JOIN dim_company_org o1 ON sc.id_own_org_dim = o1.id
      LEFT JOIN dim_company_org o ON o1.company_id = o.company_id
      WHERE o.org_id = CAST('10545406337939702955' AS CHAR)) sc
      LEFT JOIN
      (SELECT b.* FROM (SELECT
          s.id_maintain,
          s.id_service_category_dim,
          s.delivery_time,
          s.id_service,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.cash) AS service_actual_cash,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.card) AS service_actual_card,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.wechat) AS service_actual_wechat,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.alipay) AS service_actual_alipay,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.bank_transfer) AS service_actual_bank_transfer,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.account) AS service_actual_account,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.coupon) AS service_actual_coupon,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.czk) AS service_actual_czk,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.jck) AS service_actual_jck,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.tck) AS service_actual_tck,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.hyk) AS service_actual_hyk,
          IF(m1.total_expect = 0,0,SUM(s.service_subtotal) / m1.total_expect * m.owe) AS service_actual_owe,
          s.service_subtotal AS service_expect,
          m.id_own_org_dim
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      INNER JOIN st_maintain_service_detail s ON m1.id_maintain = s.id_maintain AND m1.delivery_time = s.delivery_time
      WHERE d.date <= '2018-02-26'
      GROUP BY s.id_maintain, s.delivery_time, s.id_service, s.id_service_category_dim
      ) b
        
      INNER JOIN(
      SELECT
      m1.id_maintain,
      MAX(m1.delivery_time) AS maxDeliveryTime
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      WHERE d.date <= '2018-02-26'
      GROUP BY m1.id_maintain
      ) m1 ON b.id_maintain = m1.id_maintain AND b.delivery_time = m1.maxDeliveryTime
    
      ) a ON sc.id = a.id_service_category_dim
      GROUP BY sc.id
      UNION ALL
      SELECT
      '2' AS type,
      '材料' AS type_name,
      sc.name AS businessName,
      SUM(IFNULL(a.service_actual_cash,0)) AS cash,
      SUM(IFNULL(a.service_actual_card,0)) AS bank_card,
      SUM(IFNULL(a.service_actual_wechat,0)) AS wechat,
      SUM(IFNULL(a.service_actual_alipay,0)) AS alipay,
      SUM(IFNULL(a.service_actual_bank_transfer,0)) AS bank_transfer,
      SUM(IFNULL(a.service_actual_account,0)) AS account,
      SUM(IFNULL(a.service_actual_coupon,0)) AS coupon,
      SUM(IFNULL(a.service_actual_czk,0)) AS czk,
      SUM(IFNULL(a.service_actual_jck,0)) AS jck,
      SUM(IFNULL(a.service_actual_tck,0)) AS tck,
      SUM(IFNULL(a.service_actual_hyk,0)) AS hyk,
      SUM(IFNULL(a.service_actual_owe,0)) AS owe
      FROM
      (SELECT sc.id,sc.name FROM dim_part_label sc
      LEFT JOIN dim_company_org o ON sc.id_company = o.company_id
      WHERE o.org_id = CAST('10545406337939702955' AS CHAR)) sc
      LEFT JOIN
      (SELECT b.* FROM (SELECT
          s.id_maintain,
          s.id_label_dim,
          s.delivery_time,
          s.id_part,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.cash) AS service_actual_cash,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.card) AS service_actual_card,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.wechat) AS service_actual_wechat,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.alipay) AS service_actual_alipay,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.bank_transfer) AS service_actual_bank_transfer,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.account) AS service_actual_account,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.coupon) AS service_actual_coupon,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.czk) AS service_actual_czk,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.jck) AS service_actual_jck,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.tck) AS service_actual_tck,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.hyk) AS service_actual_hyk,
          IF(m1.total_expect = 0,0,SUM(s.subtotal) / m1.total_expect * m.owe) AS service_actual_owe,
          s.subtotal AS service_expect,
          m.id_own_org_dim
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      INNER JOIN st_maintain_part_detail s ON m1.id_maintain = s.id_maintain AND m1.delivery_time = s.delivery_time
      WHERE d.date <= '2018-02-26'
      GROUP BY s.id_maintain, s.delivery_time, s.id_part, s.id_label_dim
      ) b
        
      INNER JOIN(
      SELECT
      m1.id_maintain,
      MAX(m1.delivery_time) AS maxDeliveryTime
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      WHERE d.date <= '2018-02-26'
      GROUP BY m1.id_maintain
      ) m1 ON b.id_maintain = m1.id_maintain AND b.delivery_time = m1.maxDeliveryTime
    
      ) a ON sc.id = a.id_label_dim
      GROUP BY sc.id
      UNION ALL
      SELECT
      '3' AS type,
      '工单其他费用' AS type_name,
      '-' AS businessName,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.cash,0))) AS cash,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.card,0))) AS
      bank_card,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.wechat,0))) AS
      wechat,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.alipay,0))) AS
      alipay,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.bank_transfer,0)))
      AS bank_transfer,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.account,0)))
      AS account,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.coupon,0)))
      AS coupon,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.czk,0))) AS czk,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.jck,0))) AS jck,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.tck,0))) AS tck,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.hyk,0))) AS hyk,
      SUM(IF(IFNULL(m1.total_expect,0) = 0,0,IFNULL(m1.other_fee,0) / m1.total_expect * IFNULL(m1.owe,0))) AS owe
      FROM
      (SELECT b.* FROM
      (SELECT
          m1.delivery_time,
          m1.total_expect,
          (m1.commission_cost + m1.diagnosis_cost + m1.check_cost + m1.process_cost + m1.management_cost) AS other_fee,
          m.*
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      WHERE d.date <= '2018-02-26'
      GROUP BY m.id_maintain, m1.delivery_time) b
        
      INNER JOIN(
      SELECT
      m1.id_maintain,
      MAX(m1.delivery_time) AS maxDeliveryTime
      FROM
        
    (SELECT
      m.id_maintain,
      m.id_own_org_dim,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      SUM(m.czk_amount_actual) AS czk,
      SUM(m.jck_amount_actual) AS jck,
      SUM(m.tck_amount_actual) AS tck,
      SUM(m.hyk_amount_actual) AS hyk,
      SUM(m.owe_amount) - SUM(m.owe_amount_return) AS owe
    FROM
      dim_date d
    STRAIGHT_JOIN st_maintain_payment_detail m ON d.id = m.id_delivery_date_dim
    WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
         AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
    GROUP BY m.id_maintain) m
    
      INNER JOIN st_maintain_payment_detail m1 ON m.id_maintain = m1.id_maintain AND m1.id_own_org_dim = m.id_own_org_dim
      INNER JOIN dim_date d ON m1.id_delivery_date_dim = d.id AND m1.payment_type IN (1,4)
      WHERE d.date <= '2018-02-26'
      GROUP BY m1.id_maintain
      ) m1 ON b.id_maintain = m1.id_maintain AND b.delivery_time = m1.maxDeliveryTime
    
      ) m1
      UNION ALL
      SELECT
      '5' AS type,
      '预收金额' AS type_name,
      '-' AS businessName,
      SUM(m.cash_amount_actual) AS cash,
      SUM(m.cash_amount_card_actual) AS bank_card,
      SUM(m.cash_amount_wechat_actual) AS wechat,
      SUM(m.cash_amount_alipay_actual) AS alipay,
      SUM(m.cash_amount_bank_transfer_actual) AS bank_transfer,
      SUM(m.cash_amount_06) AS account,
      SUM(m.cash_amount_08) AS coupon,
      0 AS czk,
      0 AS jck,
      0 AS tck,
      0 AS hyk,
      0 AS owe
      FROM dim_date d
      STRAIGHT_JOIN st_member_card_cash_detail m ON d.id = m.id_cash_date_dim
      WHERE m.id_own_org_dim IN (SELECT id FROM dim_company_org WHERE org_id = CAST('10545406337939702955' AS CHAR))
        AND d.date BETWEEN '2018-02-01' AND '2018-02-26'
       ORDER BY type,businessName DESC;
```
 
如此长的sql语句 看起来业务十分复杂 对于不清楚该业务的同学来说想优化也是蛮困难的
 
如果单纯sql慢那么就去优化sql好了 但是发生了一个十分奇怪的问题
 
小伙伴使用workbench执行该条sql语句时只需要大约2s 而在线上居然100s都跑不完！！！
 
## 分析
 
由于该sql语句时开发同学从debug 日志中获得 为了避免出现由于java等做了耗时操作 导致业务超时 因此考虑继续观察 发现确实是该条sql语句超时！
 
那么为何该同学的workbench执行该条sql语句这么快 而到了jdbc执行就会如此的慢呢？？？
 
带着一丝好奇心决定在mysql层次考虑一下 查看jdbc连接执行该条sql语句的状态
 

![][0]
 
长期处于creating sort index的状态。那么第一反应是不是sortbuffer等配置不同？是否jdbc在连接时做了一些配置？？？MySql超长自动截断 
 
网络上没有任何资料可供参考~确实是个奇葩的问题……
 
于是将该条sql语句在笔者自己的navicat上执行【现象复现了也是一直查不出来 】
 
那么考虑是否是workbench做了一些特殊的操作呢？【某些gui工具会将结果加上limit】
 
结果也是否定的~！
 
或者是否是缓存呢？【更改了多个参数之后发现依然是上述现象】===》QueryCache已经关闭
 
再次决定对比一下navicat的执行计划和workbench的执行计划
 

![][1]
 

![][2]
 
连执行计划也不完全相同？？？太神奇了吧！！！
 
感觉知识限制了我的想象！！
 
此时考虑那么是否是某客户端“自作主张“做了一些事情呢？比如设置autocommit等等？
 
那么决定比较两个GUI工具对应的connection的status

```sql
show SESSION VARIABLES ;
 
show SESSION status ;
```
 
对比后发现了不一致的地方
 
workbench中显示如下
| character_set_client | utf8 |
| character_set_connection | utf8 |
| character_set_database | utf8 |
| character_set_filesystem | binary |
| character_set_results | utf8 |
| character_set_server | utf8mb4 |
| character_set_system | utf8 |

而在navicat中显示
| character_set_client | utf8mb4 |
| character_set_connection | utf8mb4 |
| character_set_database | utf8 |
| character_set_filesystem | binary |
| character_set_results | utf8mb4 |
| character_set_server | utf8mb4 |
| character_set_system | utf8 |

此时灵光一闪 莫非是编码的问题？？？不过从未听过编码会导致索引走不到啊？【一般是类型不同走不到索引】
 
于是笔者尝试在navicat客户端执行sql语句之前执行

```sql
set names utf8;
```
 
果然此时sql执行和workbench一样了
 
将对应的结果告诉 小伙伴 小伙伴找到了如下一篇文章 [https://stackoverflow.com/questions/25276127/mysql-5-6-different-execution-plan-for-same-query-java-client-vs-terminal][7] 
 
似乎有点关系也似乎没有关系~
 
到这只能求教一些专业同学了~
 

![][3]
 
小伙伴给出了一些建议 原来姜老师也碰到过这个问题 [https://mp.weixin.qq.com/s/ns9eRxjXZfUPNSpfgGA7UA][8] 
 
于是决定效仿一下使用show warnings【该死 这个以后不能忘】 

```sql
/* select#1 */ SELECT
'1' AS `type`,
'项目' AS `type_name`,
`f6report_new`.`sc`.`name` AS `businessName`,
sum( ifnull( `b`.`service_actual_cash`, 0 ) ) AS `cash`,
sum( ifnull( `b`.`service_actual_card`, 0 ) ) AS `bank_card`,
sum( ifnull( `b`.`service_actual_wechat`, 0 ) ) AS `wechat`,
sum( ifnull( `b`.`service_actual_alipay`, 0 ) ) AS `alipay`,
sum( ifnull( `b`.`service_actual_bank_transfer`, 0 ) ) AS `bank_transfer`,
sum( ifnull( `b`.`service_actual_account`, 0 ) ) AS `account`,
sum( ifnull( `b`.`service_actual_coupon`, 0 ) ) AS `coupon`,
sum( ifnull( `b`.`service_actual_czk`, 0 ) ) AS `czk`,
sum( ifnull( `b`.`service_actual_jck`, 0 ) ) AS `jck`,
sum( ifnull( `b`.`service_actual_tck`, 0 ) ) AS `tck`,
sum( ifnull( `b`.`service_actual_hyk`, 0 ) ) AS `hyk`,
sum( ifnull( `b`.`service_actual_owe`, 0 ) ) AS `owe`
FROM
    `f6report_new`.`dim_service_category` `sc`
    JOIN `f6report_new`.`dim_company_org` `o1`
    JOIN `f6report_new`.`dim_company_org` `o`
    LEFT JOIN (
        (
/* select#4 */
        SELECT
            `f6report_new`.`s`.`id_maintain` AS `id_maintain`,
            `f6report_new`.`s`.`id_service_category_dim` AS `id_service_category_dim`,
            `f6report_new`.`s`.`delivery_time` AS `delivery_time`,
            `f6report_new`.`s`.`id_service` AS `id_service`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`cash` )
            ) AS `service_actual_cash`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`card` )
            ) AS `service_actual_card`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`wechat` )
            ) AS `service_actual_wechat`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`alipay` )
            ) AS `service_actual_alipay`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`bank_transfer` )
            ) AS `service_actual_bank_transfer`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`account` )
            ) AS `service_actual_account`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`coupon` )
            ) AS `service_actual_coupon`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`czk` )
            ) AS `service_actual_czk`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`jck` )
            ) AS `service_actual_jck`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`tck` )
            ) AS `service_actual_tck`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`hyk` )
            ) AS `service_actual_hyk`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`service_subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`owe` )
            ) AS `service_actual_owe`,
            `f6report_new`.`s`.`service_subtotal` AS `service_expect`,
            `m`.`id_own_org_dim` AS `id_own_org_dim`
        FROM
            (
/* select#5 */
            SELECT
                `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
                `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
                sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
                sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
                sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
                sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
                sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
                sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
                sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
                sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
                sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
                sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
                sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
                ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
            FROM
                `f6report_new`.`dim_company_org`
                JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
            WHERE
                (
                    ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                    AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                    AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                    AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
                )
            GROUP BY
                `f6report_new`.`m`.`id_maintain`
            ) `m`
            JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
            JOIN `f6report_new`.`dim_date` `d`
            JOIN `f6report_new`.`st_maintain_service_detail` `s`
        WHERE
            (
                ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
                AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
                AND ( `f6report_new`.`m1`.`delivery_time` = `f6report_new`.`s`.`delivery_time` )
                AND ( `f6report_new`.`m1`.`id_maintain` = `f6report_new`.`s`.`id_maintain` )
                AND ( `m`.`id_maintain` = `f6report_new`.`s`.`id_maintain` )
                AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
                AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
            )
        GROUP BY
            `f6report_new`.`s`.`id_maintain`,
            `f6report_new`.`s`.`delivery_time`,
            `f6report_new`.`s`.`id_service`,
            `f6report_new`.`s`.`id_service_category_dim`
        ) `b`
        JOIN (
/* select#7 */
        SELECT
            `f6report_new`.`m1`.`id_maintain` AS `id_maintain`,
            max( `f6report_new`.`m1`.`delivery_time` ) AS `maxDeliveryTime`
        FROM
            (
/* select#8 */
            SELECT
                `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
                `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
                sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
                sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
                sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
                sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
                sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
                sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
                sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
                sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
                sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
                sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
                sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
                ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
            FROM
                `f6report_new`.`dim_company_org`
                JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
            WHERE
                (
                    ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                    AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                    AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                    AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
                )
            GROUP BY
                `f6report_new`.`m`.`id_maintain`
            ) `m`
            JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
            JOIN `f6report_new`.`dim_date` `d`
        WHERE
            (
                ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
                AND ( `m`.`id_maintain` = `f6report_new`.`m1`.`id_maintain` )
                AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
                AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
                AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
            )
        GROUP BY
            `f6report_new`.`m1`.`id_maintain`
        ) `m1`
        ) ON (
        (
            ( `m1`.`maxDeliveryTime` = `b`.`delivery_time` )
            AND ( `m1`.`id_maintain` = `b`.`id_maintain` )
            AND ( `b`.`id_service_category_dim` = `f6report_new`.`sc`.`id` )
        )
    )
WHERE
    (
        ( `f6report_new`.`o1`.`id` = `f6report_new`.`sc`.`id_own_org_dim` )
        AND ( `f6report_new`.`o`.`company_id` = `f6report_new`.`o1`.`company_id` )
        AND ( CONVERT ( `f6report_new`.`o`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
    )
GROUP BY
    `f6report_new`.`sc`.`id` UNION ALL
/* select#10 */
SELECT
    '2' AS `type`,
    '材料' AS `type_name`,
    `f6report_new`.`sc`.`name` AS `businessName`,
    sum( ifnull( `b`.`service_actual_cash`, 0 ) ) AS `cash`,
    sum( ifnull( `b`.`service_actual_card`, 0 ) ) AS `bank_card`,
    sum( ifnull( `b`.`service_actual_wechat`, 0 ) ) AS `wechat`,
    sum( ifnull( `b`.`service_actual_alipay`, 0 ) ) AS `alipay`,
    sum( ifnull( `b`.`service_actual_bank_transfer`, 0 ) ) AS `bank_transfer`,
    sum( ifnull( `b`.`service_actual_account`, 0 ) ) AS `account`,
    sum( ifnull( `b`.`service_actual_coupon`, 0 ) ) AS `coupon`,
    sum( ifnull( `b`.`service_actual_czk`, 0 ) ) AS `czk`,
    sum( ifnull( `b`.`service_actual_jck`, 0 ) ) AS `jck`,
    sum( ifnull( `b`.`service_actual_tck`, 0 ) ) AS `tck`,
    sum( ifnull( `b`.`service_actual_hyk`, 0 ) ) AS `hyk`,
    sum( ifnull( `b`.`service_actual_owe`, 0 ) ) AS `owe`
FROM
    `f6report_new`.`dim_part_label` `sc`
    JOIN `f6report_new`.`dim_company_org` `o`
    LEFT JOIN (
        (
/* select#13 */
        SELECT
            `f6report_new`.`s`.`id_maintain` AS `id_maintain`,
            `f6report_new`.`s`.`id_label_dim` AS `id_label_dim`,
            `f6report_new`.`s`.`delivery_time` AS `delivery_time`,
            `f6report_new`.`s`.`id_part` AS `id_part`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`cash` )
            ) AS `service_actual_cash`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`card` )
            ) AS `service_actual_card`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`wechat` )
            ) AS `service_actual_wechat`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`alipay` )
            ) AS `service_actual_alipay`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`bank_transfer` )
            ) AS `service_actual_bank_transfer`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`account` )
            ) AS `service_actual_account`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`coupon` )
            ) AS `service_actual_coupon`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`czk` )
            ) AS `service_actual_czk`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`jck` )
            ) AS `service_actual_jck`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`tck` )
            ) AS `service_actual_tck`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`hyk` )
            ) AS `service_actual_hyk`,
        IF
            (
                ( `f6report_new`.`m1`.`total_expect` = 0 ),
                0,
                ( ( sum( `f6report_new`.`s`.`subtotal` ) / `f6report_new`.`m1`.`total_expect` ) * `m`.`owe` )
            ) AS `service_actual_owe`,
            `f6report_new`.`s`.`subtotal` AS `service_expect`,
            `m`.`id_own_org_dim` AS `id_own_org_dim`
        FROM
            (
/* select#14 */
            SELECT
                `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
                `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
                sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
                sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
                sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
                sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
                sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
                sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
                sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
                sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
                sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
                sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
                sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
                ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
            FROM
                `f6report_new`.`dim_company_org`
                JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
            WHERE
                (
                    ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                    AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                    AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                    AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
                )
            GROUP BY
                `f6report_new`.`m`.`id_maintain`
            ) `m`
            JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
            JOIN `f6report_new`.`dim_date` `d`
            JOIN `f6report_new`.`st_maintain_part_detail` `s`
        WHERE
            (
                ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
                AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
                AND ( `f6report_new`.`m1`.`delivery_time` = `f6report_new`.`s`.`delivery_time` )
                AND ( `f6report_new`.`m1`.`id_maintain` = `f6report_new`.`s`.`id_maintain` )
                AND ( `m`.`id_maintain` = `f6report_new`.`s`.`id_maintain` )
                AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
                AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
            )
        GROUP BY
            `f6report_new`.`s`.`id_maintain`,
            `f6report_new`.`s`.`delivery_time`,
            `f6report_new`.`s`.`id_part`,
            `f6report_new`.`s`.`id_label_dim`
        ) `b`
        JOIN (
/* select#16 */
        SELECT
            `f6report_new`.`m1`.`id_maintain` AS `id_maintain`,
            max( `f6report_new`.`m1`.`delivery_time` ) AS `maxDeliveryTime`
        FROM
            (
/* select#17 */
            SELECT
                `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
                `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
                sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
                sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
                sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
                sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
                sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
                sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
                sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
                sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
                sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
                sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
                sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
                ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
            FROM
                `f6report_new`.`dim_company_org`
                JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
            WHERE
                (
                    ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                    AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                    AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                    AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
                )
            GROUP BY
                `f6report_new`.`m`.`id_maintain`
            ) `m`
            JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
            JOIN `f6report_new`.`dim_date` `d`
        WHERE
            (
                ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
                AND ( `m`.`id_maintain` = `f6report_new`.`m1`.`id_maintain` )
                AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
                AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
                AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
            )
        GROUP BY
            `f6report_new`.`m1`.`id_maintain`
        ) `m1`
        ) ON (
        (
            ( `m1`.`maxDeliveryTime` = `b`.`delivery_time` )
            AND ( `m1`.`id_maintain` = `b`.`id_maintain` )
            AND ( `b`.`id_label_dim` = `f6report_new`.`sc`.`id` )
        )
    )
WHERE
    ( ( `f6report_new`.`o`.`company_id` = `f6report_new`.`sc`.`id_company` ) AND ( CONVERT ( `f6report_new`.`o`.`org_id` USING utf8mb4 ) = '10545406337939702955' ) )
GROUP BY
    `f6report_new`.`sc`.`id` UNION ALL
/* select#19 */
SELECT
    '3' AS `type`,
    '工单其他费用' AS `type_name`,
    '-' AS `businessName`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`cash`, 0 ) )
        )
    ) AS `cash`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`card`, 0 ) )
        )
    ) AS `bank_card`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`wechat`, 0 ) )
        )
    ) AS `wechat`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`alipay`, 0 ) )
        )
    ) AS `alipay`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`bank_transfer`, 0 ) )
        )
    ) AS `bank_transfer`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`account`, 0 ) )
        )
    ) AS `account`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`coupon`, 0 ) )
        )
    ) AS `coupon`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`czk`, 0 ) )
        )
    ) AS `czk`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`jck`, 0 ) )
        )
    ) AS `jck`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`tck`, 0 ) )
        )
    ) AS `tck`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`hyk`, 0 ) )
        )
    ) AS `hyk`,
    sum(
    IF
        (
            ( ifnull( `b`.`total_expect`, 0 ) = 0 ),
            0,
            ( ( ifnull( `b`.`other_fee`, 0 ) / `b`.`total_expect` ) * ifnull( `b`.`owe`, 0 ) )
        )
    ) AS `owe`
FROM
    (
/* select#21 */
    SELECT
        `f6report_new`.`m1`.`delivery_time` AS `delivery_time`,
        `f6report_new`.`m1`.`total_expect` AS `total_expect`,
        (
            ( ( ( `f6report_new`.`m1`.`commission_cost` + `f6report_new`.`m1`.`diagnosis_cost` ) + `f6report_new`.`m1`.`check_cost` ) + `f6report_new`.`m1`.`process_cost` ) + `f6report_new`.`m1`.`management_cost`
        ) AS `other_fee`,
        `m`.`id_maintain` AS `id_maintain`,
        `m`.`id_own_org_dim` AS `id_own_org_dim`,
        `m`.`cash` AS `cash`,
        `m`.`card` AS `card`,
        `m`.`wechat` AS `wechat`,
        `m`.`alipay` AS `alipay`,
        `m`.`bank_transfer` AS `bank_transfer`,
        `m`.`account` AS `account`,
        `m`.`coupon` AS `coupon`,
        `m`.`czk` AS `czk`,
        `m`.`jck` AS `jck`,
        `m`.`tck` AS `tck`,
        `m`.`hyk` AS `hyk`,
        `m`.`owe` AS `owe`
    FROM
        (
/* select#22 */
        SELECT
            `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
            `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
            sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
            sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
            sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
            sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
            sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
            sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
            sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
            sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
            sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
            sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
            sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
            ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
        FROM
            `f6report_new`.`dim_company_org`
            JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
        WHERE
            (
                ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
            )
        GROUP BY
            `f6report_new`.`m`.`id_maintain`
        ) `m`
        JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
        JOIN `f6report_new`.`dim_date` `d`
    WHERE
        (
            ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
            AND ( `m`.`id_maintain` = `f6report_new`.`m1`.`id_maintain` )
            AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
            AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
            AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
        )
    GROUP BY
        `m`.`id_maintain`,
        `f6report_new`.`m1`.`delivery_time`
    ) `b`
    JOIN (
/* select#24 */
    SELECT
        `f6report_new`.`m1`.`id_maintain` AS `id_maintain`,
        max( `f6report_new`.`m1`.`delivery_time` ) AS `maxDeliveryTime`
    FROM
        (
/* select#25 */
        SELECT
            `f6report_new`.`m`.`id_maintain` AS `id_maintain`,
            `f6report_new`.`m`.`id_own_org_dim` AS `id_own_org_dim`,
            sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
            sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `card`,
            sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
            sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
            sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
            sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
            sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
            sum( `f6report_new`.`m`.`czk_amount_actual` ) AS `czk`,
            sum( `f6report_new`.`m`.`jck_amount_actual` ) AS `jck`,
            sum( `f6report_new`.`m`.`tck_amount_actual` ) AS `tck`,
            sum( `f6report_new`.`m`.`hyk_amount_actual` ) AS `hyk`,
            ( sum( `f6report_new`.`m`.`owe_amount` ) - sum( `f6report_new`.`m`.`owe_amount_return` ) ) AS `owe`
        FROM
            `f6report_new`.`dim_company_org`
            JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_maintain_payment_detail` `m`
        WHERE
            (
                ( `f6report_new`.`m`.`id_delivery_date_dim` = `f6report_new`.`d`.`id` )
                AND ( `f6report_new`.`m`.`id_own_org_dim` = `f6report_new`.`dim_company_org`.`id` )
                AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
                AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
            )
        GROUP BY
            `f6report_new`.`m`.`id_maintain`
        ) `m`
        JOIN `f6report_new`.`st_maintain_payment_detail` `m1`
        JOIN `f6report_new`.`dim_date` `d`
    WHERE
        (
            ( `m`.`id_own_org_dim` = `f6report_new`.`m1`.`id_own_org_dim` )
            AND ( `m`.`id_maintain` = `f6report_new`.`m1`.`id_maintain` )
            AND ( `f6report_new`.`d`.`id` = `f6report_new`.`m1`.`id_delivery_date_dim` )
            AND ( `f6report_new`.`d`.`date` <= '2018-02-26' )
            AND ( `f6report_new`.`m1`.`payment_type` IN ( 1, 4 ) )
        )
    GROUP BY
        `f6report_new`.`m1`.`id_maintain`
    ) `m1`
WHERE
    ( ( `m1`.`maxDeliveryTime` = `b`.`delivery_time` ) AND ( `m1`.`id_maintain` = `b`.`id_maintain` ) ) UNION ALL
/* select#27 */
SELECT
    '5' AS `type`,
    '预收金额' AS `type_name`,
    '-' AS `businessName`,
    sum( `f6report_new`.`m`.`cash_amount_actual` ) AS `cash`,
    sum( `f6report_new`.`m`.`cash_amount_card_actual` ) AS `bank_card`,
    sum( `f6report_new`.`m`.`cash_amount_wechat_actual` ) AS `wechat`,
    sum( `f6report_new`.`m`.`cash_amount_alipay_actual` ) AS `alipay`,
    sum( `f6report_new`.`m`.`cash_amount_bank_transfer_actual` ) AS `bank_transfer`,
    sum( `f6report_new`.`m`.`cash_amount_06` ) AS `account`,
    sum( `f6report_new`.`m`.`cash_amount_08` ) AS `coupon`,
    0 AS `czk`,
    0 AS `jck`,
    0 AS `tck`,
    0 AS `hyk`,
    0 AS `owe`
FROM
    `f6report_new`.`dim_company_org`
    JOIN `f6report_new`.`dim_date` `d` STRAIGHT_JOIN `f6report_new`.`st_member_card_cash_detail` `m`
WHERE
    (
        ( `f6report_new`.`m`.`id_cash_date_dim` = `f6report_new`.`d`.`id` )
        AND ( `f6report_new`.`dim_company_org`.`id` = `f6report_new`.`m`.`id_own_org_dim` )
        AND ( `f6report_new`.`d`.`date` BETWEEN '2018-02-01' AND '2018-02-26' )
        AND ( CONVERT ( `f6report_new`.`dim_company_org`.`org_id` USING utf8mb4 ) = '10545406337939702955' )
    )
ORDER BY
    `type`,
    `businessName` DESC
```
 
果然看到了一坨 Using utf8mb4
 
## 复盘
 
我们表中的字段由于历史原因是通过uuid_short生成的 也就是unsigned bigint 但是java中没有对应的类型【biginteger可以】当时直接就有用了String存储该类型 然后在查询的时候为了防止类型不匹配做了cast as unsigned
 
另外的库从这边开始直接就是把该字段存进去用了char(20)的类型
 
于是在查询的时候用了CAST('10545406337939702955' AS CHAR)
 
但是对于不同的客户端编码场景下 cast到char将会出现不一样的结果
 
举个最简单的例子

```sql
explain select * from dim_company_org where org_id='10545406337939702955';
explain select * from dim_company_org where org_id=10545406337939702955;
explain select * from dim_company_org where org_id= CAST('10545406337939702955' AS CHAR);
```
 

![][4]
 

![][5]
 

![][6]
 
对应的建表语句如下

```sql
/*
 Navicat Premium Data Transfer
 Source Server         : local-test.db.f6car
 Source Server Type    : MySQL
 Source Server Version : 50713
 Source Host           : local-test.db.f6car:3306
 Source Schema         : f6report_new
 Target Server Type    : MySQL
 Target Server Version : 50713
 File Encoding         : 65001
 Date: 28/02/2018 15:17:41
*/
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
-- ----------------------------
-- Table structure for dim_company_org
-- ----------------------------
DROP TABLE IF EXISTS `dim_company_org`;
CREATE TABLE `dim_company_org` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，代理键',
  `company_id` char(20) NOT NULL COMMENT '公司ID',
  `company_name` varchar(100) DEFAULT NULL COMMENT '公司名称',
  `org_id` char(20) NOT NULL COMMENT '门店ID',
  `org_name` varchar(10) DEFAULT NULL COMMENT '门店简称',
  `effective_date` date DEFAULT '0000-00-00' COMMENT '生效日期',
  `expiry_date` date DEFAULT '9999-12-31' COMMENT '到期日期',
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_ORG_ID` (`org_id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=674 DEFAULT CHARSET=utf8 COMMENT='公司门店维度表';
SET FOREIGN_KEY_CHECKS = 1;
```
 
但是为什么表字符集不一样（实际是字段字符集不一样）就会导致t1全表扫描呢？下面来做分析。
 
（1）首先t2 left join t1决定了t2是驱动表，这一步相当于执行了select * from t2 where t2.name = ‘dddd’，取出code字段的值，这里为’8a77a32a7e0825f7c8634226105c42e5’;
 
（2）然后拿t2查到的code的值根据join条件去t1里面查找，这一步就相当于执行了select * from t1 where t1.code = ‘8a77a32a7e0825f7c8634226105c42e5’;
 
（3）但是由于第（1）步里面t2表取出的code字段是utf8mb4字符集，而t1表里面的code是utf8字符集，这里需要做字符集转换，字符集转换遵循由小到大的原则，因为utf8mb4是utf8的超集，所以这里把utf8转换成utf8mb4，即把t1.code转换成utf8mb4字符集，转换了之后，由于t1.code上面的索引仍然是utf8字符集，所以这个索引就被执行计划忽略了，然后t1表只能选择全表扫描。更糟糕的是，如果t2筛选出来的记录不止1条，那么t1就会被全表扫描多次，性能之差可想而知。
 
可以看到cast as char的杀伤力有多大！！！
 
至于utf8mb4是由于我们系统需要支持emoji [微信nickname乱码（emoji）及mysql编码格式设置（utf8mb4）解决的过程 - 永远的学习者 - SegmentFault][9] 
 
我们在mysql的配置文件有这样一句话

```sql
character-set-server = utf8mb4
init-connect = 'SET NAMES utf8mb4'
```
 
至于为啥workbench没有设置utf8mb4或者说是覆盖了使用utf8就不得而知了~
 
## 解决方案
 
使用如下sql

```sql
alter table t1 convert to charset utf8mb4;
```
 
修改完成之后果然一切正常了！！！
 
当然不使用cast也是一种方案 为何要转成char呢？
 
## 感谢
 
最后感谢期间的小伙伴提供的帮助
 


[7]: https://stackoverflow.com/questions/25276127/mysql-5-6-different-execution-plan-for-same-query-java-client-vs-terminal
[8]: https://mp.weixin.qq.com/s/ns9eRxjXZfUPNSpfgGA7UA
[9]: https://segmentfault.com/a/1190000004594385
[0]: ../img/aURveui.png
[1]: ../img/nQryuim.png
[2]: ../img/3E3INfA.png
[3]: ../img/J7rUzif.png
[4]: ../img/aYzqIrI.png
[5]: ../img/rIZNN3n.png
[6]: ../img/B3INFrI.png

## MySQL存储过程和调度简单示例

### 存储过程

> 一个简单的存储过程示例

```sql
USE test;
DROP PROCEDURE IF EXISTS sp_switch_group_trade;
-- 声明结束标记，防止遇到;就结束
DELIMITER //;
CREATE PROCEDURE sp_switch_group_trade () 
-- 作者：杨龙
-- 功能：将blog分表中的行业转换到主表中
BEGIN
  -- 变量申明
  DECLARE v_trade VARCHAR (20) ;
  DECLARE v_id BIGINT ;
  DECLARE end_flag BOOLEAN DEFAULT FALSE ;
  -- 游标查询转换分表中的行业
  DECLARE cur CURSOR FOR 
  SELECT 
    CASE
      t.trade_id 
      WHEN '100100' 
      THEN '1' 
      WHEN '100101' 
      THEN '2' 
      WHEN '100102' 
      THEN '3' 
      WHEN '100103' 
      THEN '4' 
      WHEN '100104' 
      THEN '5' 
      ELSE '0' 
    END trade,
    t.id 
  FROM
    industry t 
  WHERE  
    t.status = '1' ;
  -- 游标标记
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET end_flag = TRUE ;
  --打开游标，开始逐行读取
  OPEN cur ;
    read_loop : LOOP
      -- 获取游标数据
      FETCH cur INTO v_trade,v_id;
      -- 循环跳出判断
      IF end_flag 
        THEN LEAVE read_loop;
      END IF ;
      -- 操作执行语句块
      UPDATE 
        main_trade 
      SET
        trade = v_trade 
      WHERE group_id = v_id ;
    END LOOP ;
  CLOSE cur ;
END ;
```

### 事件调度

> 事件调度示例，完成存储过程的调用，有3个无出入参数的存储过程，将每天顺序执行一次

```sql
SET GLOBAL event_scheduler = ON;
DELIMITER $$
-- 杨龙，定时任务，转换行业    
CREATE EVENT ev_trade
ON SCHEDULE
-- 设置执行间隔时间EVERY 1 DAY每天，STARTS CURRENT_TIMESTAMP + INTERVAL 30 MINUTE当前时间后的30分钟开始第一次执行
EVERY 1 DAY STARTS CURRENT_TIMESTAMP + INTERVAL 30 MINUTE 
DO
  BEGIN
      CALL sp_switch_trade1();
      CALL sp_switch_trade2();
      CALL sp_switch_trade3();
  END$$
DELIMITER ;
```

#### 如果**SET GLOBAL event_scheduler = ON**报错，检查MySQL配置文件是否配置了

> skip-grant-tables  
> 如果配置了，将其注释掉，然后重启MySQL


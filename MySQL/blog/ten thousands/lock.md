    
    /* 锁表 */
    表锁定只用于防止其它客户端进行不正当地读取和写入
    MyISAM 支持表锁，InnoDB 支持行锁
    -- 锁定
        LOCK TABLES tbl_name [AS alias]
    -- 解锁
        UNLOCK TABLES
    
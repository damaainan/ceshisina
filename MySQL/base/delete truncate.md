## delete

    
    /* delete */ ------------------
    DELETE FROM tbl_name [WHERE where_definition] [ORDER BY ...] [LIMIT row_count]
    
    按照条件删除
    
    指定删除的最多记录数。Limit
    
    可以通过排序条件删除。order by + limit
    
    支持多表删除，使用类似连接语法。
    delete from 需要删除数据多表1，表2 using 表连接操作 条件。
    
    /* truncate */ ------------------
    TRUNCATE [TABLE] tbl_name
    清空数据
    删除重建表
    
    区别：
    **1**，truncate 是删除表再创建，delete 是逐条删除
    **2**，truncate 重置auto_increment的值。而delete不会
    **3**，truncate 不知道删除了几条，而delete知道。
    **4**，当被用于带分区的表时，truncate 会保留分区
    
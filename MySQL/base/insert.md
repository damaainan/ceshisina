##  insert

        
    /* insert */ ------------------
    select语句获得的数据可以用insert插入。
    
    可以省略对列的指定，要求 values () 括号内，提供给了按照列顺序出现的所有字段的值。
        或者使用set语法。
        insert into tbl_name set field=value,...；
    
    可以一次性使用多个值，采用(), (), ();的形式。
        insert into tbl_name values (), (), ();
    
    可以在列值指定时，使用表达式。
        insert into tbl_name values (field_value, **10**+**10**, now());
    可以使用一个特殊值 default，表示该列使用默认值。
        insert into tbl_name values (field_value, default);
    
    可以通过一个查询的结果，作为需要插入的值。
        insert into tbl_name select ...;
    
    可以指定在插入的值出现主键（或唯一索引）冲突时，更新其他非主键列的信息。
        insert into tbl_name values/set/select on duplicate key update 字段=值, …;
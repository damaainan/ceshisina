##  select


    
## select 
    
    select [all|distinct] select_expr from -> where -> group by [合计函数] -> having -> order by -> limit
    
    a. select_expr
        -- 可以用 * 表示所有字段。
            select * from tb;
        -- 可以使用表达式（计算公式、函数调用、字段也是个表达式）
            select stu,  29 + 25 , now() from tb;
        -- 可以为每个列使用别名。适用于简化列标识，避免多个列标识符重复。
            - 使用 as 关键字，也可省略 as.
            select stu+ 10  as add10 from tb;
    
    b. from 子句
        用于标识查询来源。
        -- 可以为表起别名。使用as关键字。
            select * from tb1 as tt, tb2 as bb;
        -- from子句后，可以同时出现多个表。
            -- 多个表会横向叠加到一起，而数据会形成一个笛卡尔积。
            select * from tb1, tb2;
    
    c. where 子句
        -- 从from获得的数据源中进行筛选。
        -- 整型1表示真，0表示假。
        -- 表达式由运算符和运算数组成。
            -- 运算数：变量（字段）、值、函数返回值
            -- 运算符：
                =, <=>, <>, !=, <=, <, >=, >, !, &&, ||, 
                in (not) null, (not) like, (not) in, (not) between and, is (not), and, or, not, xor
                is/is not 加上ture/false/unknown，检验某个值的真假
                <=>与<>功能相同，<=>可用于null比较
    
    d. group by 子句, 分组子句
        group by 字段/别名 [排序方式]
        分组后会进行排序。升序：ASC，降序：DESC
        
        以下[合计函数]需配合 group by 使用：
        count 返回不同的非NULL值数目    count(*)、count(字段)
        sum 求和
        max 求最大值
        min 求最小值
        avg 求平均值
        group_concat 返回带有来自一个组的连接的非NULL值的字符串结果。组内字符串连接。
    
    e. having 子句，条件子句
        与 where 功能、用法相同，执行时机不同。
        where 在开始时执行检测数据，对原数据进行过滤。
        having 对筛选出的结果再次进行过滤。
        having 字段必须是查询出来的，where 字段必须是数据表存在的。
        where 不可以使用字段的别名，having 可以。因为执行WHERE代码时，可能尚未确定列值。
        where 不可以使用合计函数。一般需用合计函数才会用 having
        SQL标准要求HAVING必须引用GROUP BY子句中的列或用于合计函数中的列。
    
    f. order by 子句，排序子句
        order by 排序字段/别名 排序方式 [,排序字段/别名 排序方式]...
        升序：ASC，降序：DESC
        支持多个字段的排序。
    
    g. limit 子句，限制结果数量子句
        仅对处理好的结果进行数量限制。将处理好的结果的看作是一个集合，按照记录出现的顺序，索引从0开始。
        limit 起始位置, 获取条数
        省略第一个参数，表示从索引0开始。limit 获取条数
    
    h. distinct, all 选项
        distinct 去除重复记录
        默认为 all, 全部记录
    
    
### UNION 

        将多个select查询的结果组合成一个结果集合。
        SELECT ... UNION [ALL|DISTINCT] SELECT ...
        默认 DISTINCT 方式，即所有返回的行都是唯一的
        建议，对每个SELECT查询加上小括号包裹。
        ORDER BY 排序时，需加上 LIMIT 进行结合。
        需要各select查询的字段数量一样。
        每个select查询的字段列表(数量、类型)应一致，因为结果中的字段名以第一条select语句为准。
    
    
### 子查询 
        - 子查询需用括号包裹。
    -- from型
        from后要求是一个表，必须给子查询结果取个别名。
        - 简化每个查询内的条件。
        - from型需将结果生成一个临时表格，可用以原表的锁定的释放。
        - 子查询返回一个表，表型子查询。
        select * from (select * from tb where id> 0 ) as subfrom where id> 1 ;
    -- where型
        - 子查询返回一个值，标量子查询。
        - 不需要给子查询取别名。
        - where子查询内的表，不能直接用以更新。
        select * from tb where money = (select max(money) from tb);
        -- 列子查询
            如果子查询结果返回的是一列。
            使用 in 或 not in 完成查询
            exists 和 not exists 条件
                如果子查询返回数据，则返回1或0。常用于判断条件。
                select column1 from t1 where exists (select * from t2);
        -- 行子查询
            查询条件是一个行。
            select * from t1 where (id, gender) in (select id, gender from t2);
            行构造符：(col1, col2, ...) 或 ROW(col1, col2, ...)
            行构造符通常用于与对能返回两个或两个以上列的子查询进行比较。
    
        -- 特殊运算符
        != all()    相当于 not in
        = some()    相当于 in。any 是 some 的别名
        != some()    不等同于 not in，不等于其中某一个。
        all, some 可以配合其他运算符一起使用。
    
    
## 连接查询(join) 
        将多个表的字段进行连接，可以指定连接条件。
    -- 内连接(inner join)
        - 默认就是内连接，可省略inner。
        - 只有数据存在时才能发送连接。即连接结果不能出现空行。
        on 表示连接条件。其条件表达式与where类似。也可以省略条件（表示条件永远为真）
        也可用where表示连接条件。
        还有 using, 但需字段名相同。 using(字段名)
    
        -- 交叉连接 cross join
            即，没有条件的内连接。
            select * from tb1 cross join tb2;
    -- 外连接(outer join)
        - 如果数据不存在，也会出现在连接结果中。
        -- 左外连接 left join
            如果数据不存在，左表记录会出现，而右表为null填充
        -- 右外连接 right join
            如果数据不存在，右表记录会出现，而左表为null填充
    -- 自然连接(natural join)
        自动判断连接条件完成连接。
        相当于省略了using，会自动查找相同字段名。
        natural join
        natural left join
        natural right join
    
    select info.id, info.name, info.stu_num, extra_info.hobby, extra_info.sex from info, extra_info where info.stu_num = extra_info.stu_id;
    
## 导入导出 
    select * into outfile 文件地址 [控制格式] from 表名;    -- 导出表数据
    load data [local] infile 文件地址 [replace|ignore] into table 表名 [控制格式];    -- 导入数据
        生成的数据默认的分隔符是制表符
        local未指定，则数据文件必须在服务器上
        replace 和 ignore 关键词控制对现有的唯一键记录的重复的处理
    -- 控制格式
    fields    控制字段格式
    默认：fields terminated by '\t' enclosed by '' escaped by '\\'
        terminated by 'string'    -- 终止
        enclosed by 'char'        -- 包裹
        escaped by 'char'        -- 转义
        -- 示例：
            SELECT a,b,a+b INTO OUTFILE '/tmp/result.text'
            FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            FROM test_table;
    lines    控制行格式
    默认：lines terminated by '\n'
        terminated by 'string'    -- 终止
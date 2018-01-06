    
    
    /* 表维护 */
    -- 分析和存储表的关键字分布
    ANALYZE [LOCAL | NO_WRITE_TO_BINLOG] TABLE 表名 ...
    -- 检查一个或多个表是否有错误
    CHECK TABLE tbl_name [, tbl_name] ... [option] ...
    option = {QUICK | FAST | MEDIUM | EXTENDED | CHANGED}
    -- 整理数据文件的碎片
    OPTIMIZE [LOCAL | NO_WRITE_TO_BINLOG] TABLE tbl_name [, tbl_name] ...
    
    

### 杂项 

1. 可用反引号（**`**）为标识符（库名、表名、字段名、索引、别名）包裹，以避免与关键字重名！中文也可以作为标识符！
2. 每个库目录存在一个保存当前数据库的选项文件db.opt。  
3. 注释：
    单行注释 # 注释内容  
    多行注释 /* 注释内容 */  
    单行注释 -- 注释内容        (标准SQL注释风格，要求双破折号后加一空格符（空格、TAB、换行等）)
4. 模式通配符：
    `_`    任意单个字符  
    `%`    任意多个字符，甚至包括零字符  
    `单引号`需要进行转义 `\'`  
5. **CMD命令行内的语句结束符**可以为 "`;`", "`\G`", "`\g`"，仅影响显示结果。其他地方还是用分号结束。delimiter 可修改当前对话的语句结束符。
6. SQL对大小写不敏感
7. 清除已有语句：`\c`
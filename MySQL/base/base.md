## MySQL信息  表基本操作


    /* 启动MySQL */
    net start mysql
    
    /* 连接与断开服务器 */
    mysql -h 地址 -P 端口 -u 用户名 -p 密码
    
    /* 跳过权限验证登录MySQL */
    mysqld --skip-grant-tables
    -- 修改root密码
    密码加密函数password()
    update mysql.user set password=password('root');
    
    SHOW PROCESSLIST -- 显示哪些线程正在运行
    SHOW VARIABLES -- 
    
    /* 数据库操作 */ ------------------
    -- 查看当前数据库
        select database();
    -- 显示当前时间、用户名、数据库版本
        select now(), user(), version();
    -- 创建库
        create database[ if not exists] 数据库名 数据库选项
        数据库选项：
            CHARACTER SET charset_name
            COLLATE collation_name
    -- 查看已有库
        show databases[ like 'pattern']
    -- 查看当前库信息
        show create database 数据库名
    -- 修改库的选项信息
        alter database 库名 选项信息
    -- 删除库
        drop database[ if exists] 数据库名
            同时删除该数据库相关的目录及其目录内容
    
    /* 表的操作 */ ------------------
    -- 创建表
        create [temporary] table[ if not exists] [库名.]表名 ( 表的结构定义 )[ 表选项]
            每个字段必须有数据类型
            最后一个字段后不能有逗号
            temporary 临时表，会话结束时表自动消失
            对于字段的定义：
                字段名 数据类型 [NOT NULL | NULL] [DEFAULT default_value] [AUTO_INCREMENT] [UNIQUE [KEY] | [PRIMARY] KEY] [COMMENT 'string']
    -- 表选项
        -- 字符集
            CHARSET = charset_name
            如果表没有设定，则使用数据库字符集
        -- 存储引擎
            ENGINE = engine_name    
            表在管理数据时采用的不同的数据结构，结构不同会导致处理方式、提供的特性操作等不同
            常见的引擎：InnoDB MyISAM Memory/Heap BDB Merge Example CSV MaxDB Archive
            不同的引擎在保存表的结构和数据时采用不同的方式
            MyISAM表文件含义：.frm表定义，.MYD表数据，.MYI表索引
            InnoDB表文件含义：.frm表定义，表空间数据和日志文件
            SHOW ENGINES -- 显示存储引擎的状态信息
            SHOW ENGINE 引擎名 {LOGS|STATUS} -- 显示存储引擎的日志或状态信息
        -- 数据文件目录
            DATA DIRECTORY = '目录'
        -- 索引文件目录
            INDEX DIRECTORY = '目录'
        -- 表注释
            COMMENT = 'string'
        -- 分区选项
            PARTITION BY ... (详细见手册)
    -- 查看所有表
        SHOW TABLES[ LIKE 'pattern']
        SHOW TABLES FROM 表名
    -- 查看表机构
        SHOW CREATE TABLE 表名    （信息更详细）
        DESC 表名 / DESCRIBE 表名 / EXPLAIN 表名 / SHOW COLUMNS FROM 表名 [LIKE 'PATTERN']
        SHOW TABLE STATUS [FROM db_name] [LIKE 'pattern']
    -- 修改表
        -- 修改表本身的选项
            ALTER TABLE 表名 表的选项
            EG:    ALTER TABLE 表名 ENGINE=MYISAM;
        -- 对表进行重命名
            RENAME TABLE 原表名 TO 新表名
            RENAME TABLE 原表名 TO 库名.表名    （可将表移动到另一个数据库）
            -- RENAME可以交换两个表名
        -- 修改表的字段机构
            ALTER TABLE 表名 操作名
            -- 操作名
                ADD[ COLUMN] 字段名        -- 增加字段
                    AFTER 字段名            -- 表示增加在该字段名后面
                    FIRST                -- 表示增加在第一个
                ADD PRIMARY KEY(字段名)    -- 创建主键
                ADD UNIQUE [索引名] (字段名)-- 创建唯一索引
                ADD INDEX [索引名] (字段名)    -- 创建普通索引
                ADD 
                DROP[ COLUMN] 字段名        -- 删除字段
                MODIFY[ COLUMN] 字段名 字段属性        -- 支持对字段属性进行修改，不能修改字段名(所有原有属性也需写上)
                CHANGE[ COLUMN] 原字段名 新字段名 字段属性        -- 支持对字段名修改
                DROP PRIMARY KEY    -- 删除主键(删除主键前需删除其AUTO_INCREMENT属性)
                DROP INDEX 索引名    -- 删除索引
                DROP FOREIGN KEY 外键    -- 删除外键
    
    -- 删除表
        DROP TABLE[ IF EXISTS] 表名 ...
    -- 清空表数据
        TRUNCATE [TABLE] 表名
    -- 复制表结构
        CREATE TABLE 表名 LIKE 要复制的表名
    -- 复制表结构和数据
        CREATE TABLE 表名 [AS] SELECT * FROM 要复制的表名
    -- 检查表是否有错误
        CHECK TABLE tbl_name [, tbl_name] ... [option] ...
    -- 优化表
        OPTIMIZE [LOCAL | NO_WRITE_TO_BINLOG] TABLE tbl_name [, tbl_name] ...
    -- 修复表
        REPAIR [LOCAL | NO_WRITE_TO_BINLOG] TABLE tbl_name [, tbl_name] ... [QUICK] [EXTENDED] [USE_FRM]
    -- 分析表
        ANALYZE [LOCAL | NO_WRITE_TO_BINLOG] TABLE tbl_name [, tbl_name] ...
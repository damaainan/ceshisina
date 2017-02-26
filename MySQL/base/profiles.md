# [show profiles 分析sql耗时瓶颈](http://www.cnblogs.com/chenqionghe/p/4298813.html)
1、首先查看是否开启profiling功能

    SHOW VARIABLES LIKE '%pro%'; 

或者

    SELECT @@profiling; 

2、开启profiling

    SET profiling=1; 

3、执行sql语句  
例如：

    SELECT table_schema FROM cqh_test GROUP BY table_schema ;  

4、查看结果


    SHOW profiles;  查看查询语句的信息(QUERY_ID,查询时间，查询语句内容)  
    SHOW profile FOR QUERY 8 查看QUERY_ID为8的sql语句信息
    SHOW profile ALL FOR QUERY 8; 查看QUERY_ID为8的sql语句所有信息
    94是查询ID号。
    SHOW profiles语法：
    SHOW PROFILE [type [, type] … ]  
        [FOR QUERY n]  
        [LIMIT row_count [OFFSET offset]]  
    type:  
        ALL  显示的所有信息
      | BLOCK IO  块IO操作的次数
      | CONTEXT SWITCHES  主动/被动上下文切换次数
      | CPU  显示用户和系统CPU使用时间
      | IPC  显示发送和接收消息数
      | MEMORY  到MySQL5.7尚未实现
      | PAGE FAULTS  示为主要和次要页面错误数
      | SOURCE  显示函数，函数对应的文件名称、行号
      | SWAPS  显示交换数


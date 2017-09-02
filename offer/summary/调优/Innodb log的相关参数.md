<font face=微软雅黑>

innodb log 的基础知识

innodb log 顾名思义：即 innodb 存储引擎产生的日志，也可以称为重做日志文件，默认在 innodb_data_home_dir 下面有两个文件ib_logfile0 和 ib_logfile1。 MySQL 官方手册中将这两个文件叫文InnoDB 存储引擎的日志文件；

    show variables like 'innodb%log%'; 

查看重做日志的相关参数

常用设置的参数有：

innodb_mirrored_log_groups 镜像组的数量，默认为 1，没有镜像；  
innodb_log_group_home_dir 日志组所在的路径，默认为 data 的home 目录；  
innodb_log_files_in_group 日志组的数量，默认为 2；  
innodb_log_file_size 日志组的大小,默认为 5M；  
innodb_log_buffer_size 日志缓冲池的大小，图上为 30M  



</font>
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


# innodb的读写参数优化   

(1)、读取参数  
global buffer pool以及 local buffer；  
  
(2)、写入参数；  
innodb_flush_log_at_trx_commit  
innodb_buffer_pool_size  
  
(3)、与IO相关的参数；  
innodb_write_io_threads = 8  
innodb_read_io_threads = 8  
innodb_thread_concurrency = 0  
  
(4)、缓存参数以及缓存的适用场景。  
query cache/query_cache_type  
并不是所有表都适合使用query cache。造成query cache失效的原因主要是相应的table发生了变更

* 第一个：读操作多的话看看比例，简单来说，如果是用户清单表，或者说是数据比例比较固定，比如说商品列表，是可以打开的，前提是这些库比较集中，数据库中的实务比较小。

* 第二个：我们“行骗”的时候，比如说我们竞标的时候压测，把query cache打开，还是能收到qps激增的效果，当然前提示前端的连接池什么的都配置一样。大部分情况下如果写入的居多，访问量并不多，那么就不要打开，例如社交网站的，10%的人产生内容，其余的90%都在消费，打开还是效果很好的，但是你如果是qq消息，或者聊天，那就很要命。

* 第三个：小网站或者没有高并发的无所谓，高并发下，会看到 很多 qcache 锁 等待，所以一般高并发下，不建议打开query cache

</font>
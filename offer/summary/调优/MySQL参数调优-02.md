
<font face=微软雅黑>
### MySQL调优必备18个参数

#### 连接数 、会话数和线程数

1.`max_connections` 

最大连接 ( 用户 ) 数

2.`max_connect_errors` 

3.`thread_concurrency`


#### 数据包和缓存

4.`max_allowed_packet` 

限制server允许通信的最大数据包大小

5.`key_buffer_size` 

关键词缓冲区大小，缓存MyISAM索引块 ，决定索引处理速度，读取索引处理。

6.`thread_cache_size` 

7.`sort_buffer_siz`

8.`join_buffer_size` 

9.`query_cache_size` 


10.`read_buffer_size` 

11.`read_rnd_buffer_size` 

12.`myisam_sort_buffer_size` 


13.`innodb_buffer_pool_size` 

#### 日志和事务

14.`innodb_log_file_size` 


15.`innodb_log_buffer_size` 


16.`innodb_flush_log_at_trx_commit` 

17.`innodb_lock_wait_timeout` 

</font>
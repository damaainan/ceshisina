##### mysql_affected_rows — 取得前一次 MySQL 操作所影响的记录行数  

mysqli_affected_rows()  
PDOStatement::rowCount()  

##### mysql_client_encoding — 返回字符集的名称  

mysqli_character_set_name()   
PDO::setAttribute() (e.g., $db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");)


##### mysql_close — 关闭 MySQL 连接  

mysqli_close()  
PDO: 为 PDO 对象设置一个 NULL 值 

##### mysql_connect — 打开一个到 MySQL 服务器的连接  

mysqli_connect()  
PDO::__construct()


##### mysql_create_db — 新建一个 MySQL 数据库  


##### mysql_data_seek — 移动内部结果的指针  


##### mysql_db_name — 取得结果数据  


##### mysql_db_query — 发送一条 MySQL 查询  


##### mysql_drop_db — 丢弃（删除）一个 MySQL 数据库  


##### mysql_errno — 返回上一个 MySQL 操作中的错误信息的数字编码  


##### mysql_error — 返回上一个 MySQL 操作产生的文本错误信息  


##### mysql_escape_string — 转义一个字符串用于 mysql_query  


##### mysql_fetch_array — 从结果集中取得一行作为关联数组，或数字数组，或二者兼有  


##### mysql_fetch_assoc — 从结果集中取得一行作为关联数组  


##### mysql_fetch_field — 从结果集中取得列信息并作为对象返回  


##### mysql_fetch_lengths — 取得结果集中每个输出的长度  


##### mysql_fetch_object — 从结果集中取得一行作为对象  


##### mysql_fetch_row — 从结果集中取得一行作为枚举数组  


##### mysql_field_flags — 从结果中取得和指定字段关联的标志  


##### mysql_field_len — 返回指定字段的长度  


##### mysql_field_name — 取得结果中指定字段的字段名  


##### mysql_field_seek — 将结果集中的指针设定为制定的字段偏移量  


##### mysql_field_table — 取得指定字段所在的表名  


##### mysql_field_type — 取得结果集中指定字段的类型  


##### mysql_free_result — 释放结果内存  


##### mysql_get_client_info — 取得 MySQL 客户端信息  


##### mysql_get_host_info — 取得 MySQL 主机信息  


##### mysql_get_proto_info — 取得 MySQL 协议信息  


##### mysql_get_server_info — 取得 MySQL 服务器信息  


##### mysql_info — 取得最近一条查询的信息  


##### mysql_insert_id — 取得上一步 INSERT 操作产生的 ID  


##### mysql_list_dbs — 列出 MySQL 服务器中所有的数据库  


##### mysql_list_fields — 列出 MySQL 结果中的字段  


##### mysql_list_processes — 列出 MySQL 进程  


##### mysql_list_tables — 列出 MySQL 数据库中的表  


##### mysql_num_fields — 取得结果集中字段的数目  


##### mysql_num_rows — 取得结果集中行的数目  

mysqli_num_rows($result);


##### mysql_pconnect — 打开一个到 MySQL 服务器的持久连接  


##### mysql_ping — Ping 一个服务器连接，如果没有连接则重新连接  


##### mysql_query — 发送一条 MySQL 查询  

mysqli_query()   
PDO::query()


##### mysql_real_escape_string — 转义 SQL 语句中使用的字符串中的特殊字符，并考虑到连接的当前字符集  

面向对象风格  
  
string mysqli::escape_string ( string $escapestr )  
  
string mysqli::real_escape_string ( string $escapestr )  
  
过程化风格  
  
string mysqli_real_escape_string ( mysqli $link , string $escapestr )  


##### mysql_result — 取得结果数据  


##### mysql_select_db — 选择 MySQL 数据库  


##### mysql_set_charset — 设置客户端的字符集  


##### mysql_stat — 取得当前系统状态  


##### mysql_tablename — 取得表名  


##### mysql_thread_id — 返回当前线程的 ID  


##### mysql_unbuffered_query — 向 MySQL 发送一条 SQL 查询，并不获取和缓存结果的行  



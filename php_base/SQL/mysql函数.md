函数 | 描述 
-|-
[mysql_affected_rows()][0] | 取得前一次 MySQL 操作所影响的记录行数。 
mysql_change_user() 不赞成。改变活动连接中登录的用户 
[mysql_client_encoding()][1] | 返回当前连接的字符集的名称 
[mysql_close()][2] | 关闭非持久的 MySQL 连接。 
[mysql_connect()][3] | 打开非持久的 MySQL 连接。 
mysql_create_db() 不赞成。新建 MySQL 数据库。使用 mysql_query() 代替。 
[mysql_data_seek()][4] | 移动记录指针。 
[mysql_db_name()][5] | 从对 mysql_list_dbs() 的调用返回数据库名称。 
mysql_db_query() 不赞成。发送一条 MySQL 查询。使用 mysql_select_db() 和 mysql_query() 
mysql_drop_db() 不赞成。丢弃（删除）一个 MySQL 数据库。使用 mysql_query() 
[mysql_errno()][6] | 返回上一个 MySQL 操作中的错误信息的数字编码。 
[mysql_error()][7] | 返回上一个 MySQL 操作产生的文本错误信息。 
mysql_escape_string() 不赞成。转义一个字符串用于 mysql_query。使用 mysql_real_escape_string() 
[mysql_fetch_array()][8] | 从结果集中取得一行作为关联数组，或数字数组，或二者兼有。 
[mysql_fetch_assoc()][9] | 从结果集中取得一行作为关联数组。 
[mysql_fetch_field()][10] | 从结果集中取得列信息并作为对象返回。 
[mysql_fetch_lengths()][11] | 取得结果集中每个字段的内容的长度。 
[mysql_fetch_object()][12] | 从结果集中取得一行作为对象。 
[mysql_fetch_row()][13] | 从结果集中取得一行作为数字数组。 
[mysql_field_flags()][14] | 从结果中取得和指定字段关联的标志。 
[mysql_field_len()][15] | 返回指定字段的长度。 
[mysql_field_name()][16] | 取得结果中指定字段的字段名。 
[mysql_field_seek()][17] | 将结果集中的指针设定为指定的字段偏移量。 
[mysql_field_table()][18] | 取得指定字段所在的表名。 
[mysql_field_type()][19] | 取得结果集中指定字段的类型。 
[mysql_free_result()][20] | 释放结果内存。 
[mysql_get_client_info()][21] | 取得 MySQL 客户端信息。 
[mysql_get_host_info()][22] | 取得 MySQL 主机信息。 
[mysql_get_proto_info()][23] | 取得 MySQL 协议信息。 
[mysql_get_server_info()][24] | 取得 MySQL 服务器信息。 
[mysql_info()][25] | 取得最近一条查询的信息。 
[mysql_insert_id()][26] | 取得上一步 INSERT 操作产生的 ID。 
[mysql_list_dbs()][27] | 列出 MySQL 服务器中所有的数据库。 
mysql_list_fields() 不赞成。列出 MySQL 结果中的字段。使用 mysql_query() 
[mysql_list_processes()][28] | 列出 MySQL 进程。 
mysql_list_tables() 不赞成。列出 MySQL 数据库中的表。使用Use mysql_query() 
[mysql_num_fields()][29] | 取得结果集中字段的数目。 
[mysql_num_rows()][30] | 取得结果集中行的数目。 
[mysql_pconnect()][31] | 打开一个到 MySQL 服务器的持久连接。 
[mysql_ping()][32] | Ping 一个服务器连接，如果没有连接则重新连接。 
[mysql_query()][33] | 发送一条 MySQL 查询。 
[mysql_real_escape_string()][34] | 转义 SQL 语句中使用的字符串中的特殊字符。 
[mysql_result()][35] | 取得结果数据。 
[mysql_select_db()][36] | 选择 MySQL 数据库。 
[mysql_stat()][37] | 取得当前系统状态。 4 mysql_tablename() 不赞成。取得表名。使用 mysql_query() 代替。 
[mysql_thread_id()][38] | 返回当前线程的 ID。 
[mysql_unbuffered_query()][39] | 向 MySQL 发送一条 SQL 查询（不获取 / 缓存结果）。 

[0]: func_mysql_affected_rows.asp
[1]: func_mysql_client_encoding.asp
[2]: func_mysql_close.asp
[3]: func_mysql_connect.asp
[4]: func_mysql_data_seek.asp
[5]: func_mysql_db_name.asp
[6]: func_mysql_errno.asp
[7]: func_mysql_error.asp
[8]: func_mysql_fetch_array.asp
[9]: func_mysql_fetch_assoc.asp
[10]: func_mysql_fetch_field.asp
[11]: func_mysql_fetch_lengths.asp
[12]: func_mysql_fetch_object.asp
[13]: func_mysql_fetch_row.asp
[14]: func_mysql_field_flags.asp
[15]: func_mysql_field_len.asp
[16]: func_mysql_field_name.asp
[17]: func_mysql_field_seek.asp
[18]: func_mysql_field_table.asp
[19]: func_mysql_field_type.asp
[20]: func_mysql_free_result.asp
[21]: func_mysql_get_client_info.asp
[22]: func_mysql_get_host_info.asp
[23]: func_mysql_get_proto_info.asp
[24]: func_mysql_get_server_info.asp
[25]: func_mysql_info.asp
[26]: func_mysql_insert_id.asp
[27]: func_mysql_list_dbs.asp
[28]: func_mysql_list_processes.asp
[29]: func_mysql_num_fields.asp
[30]: func_mysql_num_rows.asp
[31]: func_mysql_pconnect.asp
[32]: func_mysql_ping.asp
[33]: func_mysql_query.asp
[34]: func_mysql_real_escape_string.asp
[35]: func_mysql_result.asp
[36]: func_mysql_select_db.asp
[37]: func_mysql_stat.asp
[38]: func_mysql_thread_id.asp
[39]: func_mysql_unbuffered_query.asp
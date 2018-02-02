 函数 | 描述 
 -|-
 [mysqli_affected_rows()][0] | 返回前一次 MySQL 操作所影响的记录行数。 
 [mysqli_autocommit()][1] | 打开或关闭自动提交数据库修改。 
 [mysqli_change_user()][2] | 更改指定数据库连接的用户。 
 [mysqli_character_set_name()][3] | 返回数据库连接的默认字符集。 
 [mysqli_close()][4] | 关闭先前打开的数据库连接。 
 [mysqli_commit()][5] | 提交当前事务。 
 [mysqli_connect_errno()][6] | 返回上一次连接错误的错误代码。 
 [mysqli_connect_error()][7] | 返回上一次连接错误的错误描述。 
 [mysqli_connect()][8] | 打开一个到 MySQL 服务器的新的连接。 
 [mysqli_data_seek()][9] | 调整结果指针到结果集中的一个任意行。 
 [mysqli_debug()][10] | 执行调试操作。 
 [mysqli_dump_debug_info()][11] | 转储调试信息到日志中。 
 [mysqli_errno()][12] | 返回最近调用函数的最后一个错误代码。 
 [mysqli_error_list()][13] | 返回最近调用函数的错误列表。 
 [mysqli_error()][14] | 返回最近调用函数的最后一个错误描述。 
 [mysqli_fetch_all()][15] | 从结果集中取得所有行作为关联数组，或数字数组，或二者兼有。 
 [mysqli_fetch_array()][16] | 从结果集中取得一行作为关联数组，或数字数组，或二者兼有。 
 [mysqli_fetch_assoc()][17] | 从结果集中取得一行作为关联数组。 
 [mysqli_fetch_field_direct()][18] | 从结果集中取得某个单一字段的 meta-data，并作为对象返回。 
 [mysqli_fetch_field()][19] | 从结果集中取得下一字段，并作为对象返回。 
 [mysqli_fetch_fields()][20] | 返回结果中代表字段的对象的数组。 
 [mysqli_fetch_lengths()][21] | 返回结果集中当前行的每个列的长度。 
 [mysqli_fetch_object()][22] | 从结果集中取得当前行，并作为对象返回。 
 [mysqli_fetch_row()][23] | 从结果集中取得一行，并作为枚举数组返回。 
 [mysqli_field_count()][24] | 返回最近查询的列数。 
 [mysqli_field_seek()][25] | 把结果集中的指针设置为指定字段的偏移量。 
 [mysqli_field_tell()][26] | 返回结果集中的指针的位置。 
 [mysqli_free_result()][27] | 释放结果内存。 
 [mysqli_get_charset()][28] | 返回字符集对象。 
 [mysqli_get_client_info()][29] | 返回 MySQL 客户端库版本。 
 [mysqli_get_client_stats()][30] | 返回有关客户端每个进程的统计。 
 [mysqli_get_client_version()][31] | 将 MySQL 客户端库版本作为整数返回。 
 [mysqli_get_connection_stats()][32] | 返回有关客户端连接的统计。 
 [mysqli_get_host_info()][33] | 返回 MySQL 服务器主机名和连接类型。 
 [mysqli_get_proto_info()][34] | 返回 MySQL 协议版本。 
 [mysqli_get_server_info()][35] | 返回 MySQL 服务器版本。 
 [mysqli_get_server_version()][36] | 将 MySQL 服务器版本作为整数返回。 
 [mysqli_info()][37] | 返回有关最近执行查询的信息。 
 [mysqli_init()][38] | 初始化 MySQLi 并返回 mysqli_real_connect() 使用的资源。 
 [mysqli_insert_id()][39] | 返回最后一个查询中自动生成的 ID。 
 [mysql_kill()][40] | 请求服务器杀死一个 MySQL 线程。 
 [mysqli_more_results()][41] | 检查一个多查询是否有更多的结果。 
 [mysqli_multi_query()][42] | 执行一个或多个针对数据库的查询。 
 [mysqli_next_result()][43] | 为 mysqli_multi_query() 准备下一个结果集。 
 [mysqli_num_fields()][44] | 返回结果集中字段的数量。 
 [mysqli_num_rows()][45] | 返回结果集中行的数量。 
 [mysqli_options()][46] | 设置额外的连接选项，用于影响连接行为。 
 [mysqli_ping()][47] | 进行一个服务器连接，如果连接已断开则尝试重新连接。 mysqli_prepare() 准备执行一个 SQL 语句。 
 [mysqli_query()][48] | 执行某个针对数据库的查询。 
 [mysqli_real_connect()][49] | 打开一个到 MySQL 服务器的新的链接。 
 [mysqli_real_escape_string()][50] | 转义在 SQL 语句中使用的字符串中的特殊字符。 mysqli_real_query() 执行 SQL 查询 mysqli_reap_async_query() 返回异步查询的结果。 
 [mysqli_refresh()][51] | 刷新表或缓存，或者重置复制服务器信息。 
 [mysqli_rollback()][52] | 回滚数据库中的当前事务。 
 [mysqli_select_db()][53] | 更改连接的默认数据库。 
 [mysqli_set_charset()][54] | 设置默认客户端字符集。 mysqli_set_local_infile_default() 撤销用于 load local infile 命令的用户自定义句柄。 mysqli_set_local_infile_handler() 设置用于 LOAD DATA LOCAL INFILE 命令的回滚函数。 
 [mysqli_sqlstate()][55] | 返回最后一个 MySQL 操作的 SQLSTATE 错误代码。 
 [mysqli_ssl_set()][56] | 用于创建 SSL 安全连接。 
 [mysqli_stat()][57] | 返回当前系统状态。 
 [mysqli_stmt_init()][58] | 初始化声明并返回 mysqli_stmt_prepare() 使用的对象。 mysqli_store_result() 传输最后一个查询的结果集。 
 [mysqli_thread_id()][59] | 返回当前连接的线程 ID。 
 [mysqli_thread_safe()][60] | 返回是否将客户端库编译成 thread-safe。 mysqli_use_result() 从上次使用 mysqli_real_query() 执行的查询中初始化结果集的检索。 mysqli_warning_count() 返回连接中的最后一个查询的警告数量。

[0]: http://www.runoob.com/php/func-mysqli-affected-rows.html
[1]: http://www.runoob.com/php/func-mysqli-autocommit.html
[2]: http://www.runoob.com/php/func-mysqli-change-user.html
[3]: http://www.runoob.com/php/func-mysqli-character-set-name.html
[4]: http://www.runoob.com/php/func-mysqli-close.html
[5]: http://www.runoob.com/php/func-mysqli-commit.html
[6]: http://www.runoob.com/php/func-mysqli-connect-errno.html
[7]: http://www.runoob.com/php/func-mysqli-connect-error.html
[8]: http://www.runoob.com/php/func-mysqli-connect.html
[9]: http://www.runoob.com/php/func-mysqli-data-seek.html
[10]: http://www.runoob.com/php/func-mysqli-debug.html
[11]: http://www.runoob.com/php/func-mysqli-dump-debug-info.html
[12]: http://www.runoob.com/php/func-mysqli-errno.html
[13]: http://www.runoob.com/php/func-mysqli-error-list.html
[14]: http://www.runoob.com/php/func-mysqli-error.html
[15]: http://www.runoob.com/php/func-mysqli-fetch-all.html
[16]: http://www.runoob.com/php/func-mysqli-fetch-array.html
[17]: http://www.runoob.com/php/func-mysqli-fetch-assoc.html
[18]: http://www.runoob.com/php/func-mysqli-fetch-field-direct.html
[19]: http://www.runoob.com/php/func-mysqli-fetch-field.html
[20]: http://www.runoob.com/php/func-mysqli-fetch-fields.html
[21]: http://www.runoob.com/php/func-mysqli-fetch-lengths.html
[22]: http://www.runoob.com/php/func-mysqli-fetch-object.html
[23]: http://www.runoob.com/php/func-mysqli-fetch-row.html
[24]: http://www.runoob.com/php/func-mysqli-field-count.html
[25]: http://www.runoob.com/php/func-mysqli-field-seek.html
[26]: http://www.runoob.com/php/func-mysqli-field-tell.html
[27]: http://www.runoob.com/php/func-mysqli-free-result.html
[28]: http://www.runoob.com/php/func-mysqli-get-charset.html
[29]: http://www.runoob.com/php/func-mysqli-get-client-info.html
[30]: http://www.runoob.com/php/func-mysqli-get-client-stats.html
[31]: http://www.runoob.com/php/func-mysqli-get-client-version.html
[32]: http://www.runoob.com/php/func-mysqli-get-connection-stats.html
[33]: http://www.runoob.com/php/func-mysqli-get-host-info.html
[34]: http://www.runoob.com/php/func-mysqli-get-proto-info.html
[35]: http://www.runoob.com/php/func-mysqli-get-server-info.html
[36]: http://www.runoob.com/php/func-mysqli-get-server-version.html
[37]: http://www.runoob.com/php/func-mysqli-info.html
[38]: http://www.runoob.com/php/func-mysqli-init.html
[39]: http://www.runoob.com/php/func-mysqli-insert-id.html
[40]: http://www.runoob.com/php/func-mysqli-kill.html
[41]: http://www.runoob.com/php/func-mysqli-more-results.html
[42]: http://www.runoob.com/php/func-mysqli-multi-query.html
[43]: http://www.runoob.com/php/func-mysqli-next-result.html
[44]: http://www.runoob.com/php/func-mysqli-num-fields.html
[45]: http://www.runoob.com/php/func-mysqli-num-rows.html
[46]: http://www.runoob.com/php/func-mysqli-options.html
[47]: http://www.runoob.com/php/func-mysqli-ping.html
[48]: http://www.runoob.com/php/func-mysqli-query.html
[49]: http://www.runoob.com/php/func-mysqli-real-connect.html
[50]: http://www.runoob.com/php/func-mysqli-real-escape-string.html
[51]: http://www.runoob.com/php/func-mysqli-refresh.html
[52]: http://www.runoob.com/php/func-mysqli-rollback.html
[53]: http://www.runoob.com/php/func-mysqli-select-db.html
[54]: http://www.runoob.com/php/func-mysqli-set-charset.html
[55]: http://www.runoob.com/php/func-mysqli-sqlstate.html
[56]: http://www.runoob.com/php/func-mysqli-ssl-set.html
[57]: http://www.runoob.com/php/func-mysqli-stat.html
[58]: http://www.runoob.com/php/func-mysqli-stmt-init.html
[59]: http://www.runoob.com/php/func-mysqli-thread-id.html
[60]: http://www.runoob.com/php/func-mysqli-thread-safe.html
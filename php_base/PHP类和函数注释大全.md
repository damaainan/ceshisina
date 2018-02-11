## PHP类和函数注释大全

来源：[http://www.cnblogs.com/hlxs/p/8366685.html](http://www.cnblogs.com/hlxs/p/8366685.html)

时间 2018-01-27 18:20:00


| a |-|-|
|-|-|-|
| 0 |     [acos][0] | 反余弦 |
| 1 |     [acosh][1] | 反双曲余弦 |
| 2 |     [addcslashes][2] | 以 C 语言风格使用反斜线转义字符串中的字符 |
| 3 |     [addslashes][3] | 使用反斜线引用字符串 |
| 4 |     [apache_child_terminate][4] | 在本次请求结束后终止 apache 子进程 |
| 5 |     [apache_get_modules][5] | 获得已加载的Apache模块列表 |
| 6 |     [apache_get_version][6] | 获得Apache版本信息 |
| 7 |     [apache_getenv][7] | 获取 Apache subprocess_env 变量 |
| 8 |     [apache_lookup_uri][8] | 对指定的 URI 执行部分请求并返回所有有关信息 |
| 9 |     [apache_note][9] | 取得或设置 apache 请求记录 |
| 10 |     [apache_request_headers][10] | 获取全部 HTTP 请求头信息 |
| 11 |     [apache_reset_timeout][11] | 重置 Apache 写入计时器 |
| 12 |     [apache_response_headers][12] | 获得全部 HTTP 响应头信息 |
| 13 |     [apache_setenv][13] | 设置 Apache 子进程环境变量 |
| 14 |     [apc_add][14] | 缓存一个变量到数据存储 |
| 15 |     [apc_bin_dump][15] | Get a binary dump of the given files and user variables |
| 16 |     [apc_bin_dumpfile][16] | Output a binary dump of cached files and user variables to a file |
| 17 |     [apc_bin_load][17] | Load a binary dump into the APC file/user cache |
| 18 |     [apc_bin_loadfile][18] | Load a binary dump from a file into the APC file/user cache |
| 19 |     [apc_cache_info][19] | Retrieves cached information from APC's data store |
| 20 |     [apc_cas][20] | 用新值更新旧值 |
| 21 |     [apc_clear_cache][21] | 清除APC缓存 |
| 22 |     [apc_compile_file][22] | Stores a file in the bytecode cache, bypassing all filters |
| 23 |     [apc_dec][23] | Decrease a stored number |
| 24 |     [apc_define_constants][24] | Defines a set of constants for retrieval and mass-definition |
| 25 |     [apc_delete][25] | 从用户缓存中删除某个变量 |
| 26 |     [apc_delete_file][26] | Deletes files from the opcode cache |
| 27 |     [apc_exists][27] | 检查APC中是否存在某个或者某些key |
| 28 |     [apc_fetch][28] | 从缓存中取出存储的变量 |
| 29 |     [apc_inc][29] | 递增一个储存的数字 |
| 30 |     [apc_load_constants][30] | Loads a set of constants from the cache |
| 31 |     [apc_sma_info][31] | Retrieves APC's Shared Memory Allocation information |
| 32 |     [apc_store][32] | Cache a variable in the data store |
| 33 |     [apcu_add][33] | Cache a new variable in the data store |
| 34 |     [apcu_cache_info][34] | Retrieves cached information from APCu's data store |
| 35 |     [apcu_cas][35] | Updates an old value with a new value |
| 36 |     [apcu_clear_cache][36] | Clears the APCu cache |
| 37 |     [apcu_dec][37] | Decrease a stored number |
| 38 |     [apcu_delete][38] | Removes a stored variable from the cache |
| 39 |     [apcu_entry][39] | Atomically fetch or generate a cache entry |
| 40 |     [apcu_exists][40] | Checks if entry exists |
| 41 |     [apcu_fetch][41] | Fetch a stored variable from the cache |
| 42 |     [apcu_inc][42] | Increase a stored number |
| 43 |     [apcu_sma_info][43] | Retrieves APCu Shared Memory Allocation information |
| 44 |     [apcu_store][44] | Cache a variable in the data store |
| 45 |     [array_change_key_case][45] | 将数组中的所有键名修改为全大写或小写 |
| 46 |     [array_chunk][46] | 将一个数组分割成多个 |
| 47 |     [array_column][47] | 返回数组中指定的一列 |
| 48 |     [array_combine][48] | 创建一个数组，用一个数组的值作为其键名，另一个数组的值作为其值 |
| 49 |     [array_count_values][49] | 统计数组中所有的值 |
| 50 |     [array_diff][50] | 计算数组的差集 |
| 51 |     [array_diff_assoc][51] | 带索引检查计算数组的差集 |
| 52 |     [array_diff_key][52] | 使用键名比较计算数组的差集 |
| 53 |     [array_diff_uassoc][53] | 用用户提供的回调函数做索引检查来计算数组的差集 |
| 54 |     [array_diff_ukey][54] | 用回调函数对键名比较计算数组的差集 |
| 55 |     [array_fill][55] | 用给定的值填充数组 |
| 56 |     [array_fill_keys][56] | 使用指定的键和值填充数组 |
| 57 |     [array_filter][57] | 用回调函数过滤数组中的单元 |
| 58 |     [array_flip][58] | 交换数组中的键和值 |
| 59 |     [array_intersect][59] | 计算数组的交集 |
| 60 |     [array_intersect_assoc][60] | 带索引检查计算数组的交集 |
| 61 |     [array_intersect_key][61] | 使用键名比较计算数组的交集 |
| 62 |     [array_intersect_uassoc][62] | 带索引检查计算数组的交集，用回调函数比较索引 |
| 63 |     [array_intersect_ukey][63] | 用回调函数比较键名来计算数组的交集 |
| 64 |     [array_key_exists][64] | 检查数组里是否有指定的键名或索引 |
| 65 |     [array_keys][65] | 返回数组中部分的或所有的键名 |
| 66 |     [array_map][66] | 为数组的每个元素应用回调函数 |
| 67 |     [array_merge][67] | 合并一个或多个数组 |
| 68 |     [array_merge_recursive][68] | 递归地合并一个或多个数组 |
| 69 |     [array_multisort][69] | 对多个数组或多维数组进行排序 |
| 70 |     [array_pad][70] | 以指定长度将一个值填充进数组 |
| 71 |     [array_pop][71] | 弹出数组最后一个单元（出栈） |
| 72 |     [array_product][72] | 计算数组中所有值的乘积 |
| 73 |     [array_push][73] | 将一个或多个单元压入数组的末尾（入栈） |
| 74 |     [array_rand][74] | 从数组中随机取出一个或多个单元 |
| 75 |     [array_reduce][75] | 用回调函数迭代地将数组简化为单一的值 |
| 76 |     [array_replace][76] | 使用传递的数组替换第一个数组的元素 |
| 77 |     [array_replace_recursive][77] | 使用传递的数组递归替换第一个数组的元素 |
| 78 |     [array_reverse][78] | 返回单元顺序相反的数组 |
| 79 |     [array_search][79] | 在数组中搜索给定的值，如果成功则返回首个相应的键名 |
| 80 |     [array_shift][80] | 将数组开头的单元移出数组 |
| 81 |     [array_slice][81] | 从数组中取出一段 |
| 82 |     [array_splice][82] | 去掉数组中的某一部分并用其它值取代 |
| 83 |     [array_sum][83] | 对数组中所有值求和 |
| 84 |     [array_udiff][84] | 用回调函数比较数据来计算数组的差集 |
| 85 |     [array_udiff_assoc][85] | 带索引检查计算数组的差集，用回调函数比较数据 |
| 86 |     [array_udiff_uassoc][86] | 带索引检查计算数组的差集，用回调函数比较数据和索引 |
| 87 |     [array_uintersect][87] | 计算数组的交集，用回调函数比较数据 |
| 88 |     [array_uintersect_assoc][88] | 带索引检查计算数组的交集，用回调函数比较数据 |
| 89 |     [array_uintersect_uassoc][89] | 带索引检查计算数组的交集，用单独的回调函数比较数据和索引 |
| 90 |     [array_unique][90] | 移除数组中重复的值 |
| 91 |     [array_unshift][91] | 在数组开头插入一个或多个单元 |
| 92 |     [array_values][92] | 返回数组中所有的值 |
| 93 |     [array_walk][93] | 使用用户自定义函数对数组中的每个元素做回调处理 |
| 94 |     [array_walk_recursive][94] | 对数组中的每个成员递归地应用用户函数 |
| 95 |     [arsort][95] | 对数组进行逆向排序并保持索引关系 |
| 96 |     [asin][96] | 反正弦 |
| 97 |     [asinh][97] | 反双曲正弦 |
| 98 |     [asort][98] | 对数组进行排序并保持索引关系 |
| 99 |     [assert][99] | 检查一个断言是否为 **``FALSE`**  |
| 100 |     [assert_options][100] | 设置/获取断言的各种标志 |
| 101 |     [atan][101] | 反正切 |
| 102 |     [atan2][102] | 两个参数的反正切 |
| 103 |     [atanh][103] | 反双曲正切 |

| b |-|-|
|-|-|-|
| 104 |     [base64_encode][104] | 使用 MIME base64 对数据进行编码 |
| 105 |     [base_convert][105] | 在任意进制之间转换数字 |
| 106 |     [basename][106] | 返回路径中的文件名部分 |
| 107 |     [bcadd][107] | 2个任意精度数字的加法计算 |
| 108 |     [bccomp][108] | 比较两个任意精度的数字 |
| 109 |     [bcdiv][109] | 2个任意精度的数字除法计算 |
| 110 |     [bcmod][110] | 对一个任意精度数字取模 |
| 111 |     [bcmul][111] | 2个任意精度数字乘法计算 |
| 112 |     [bcpow][112] | 任意精度数字的乘方 |
| 113 |     [bcpowmod][113] | Raise an arbitrary precision number to another, reduced by a specified modulus |
| 114 |     [bcscale][114] | 设置所有bc数学函数的默认小数点保留位数 |
| 115 |     [bcsqrt][115] | 任意精度数字的二次方根 |
| 116 |     [bcsub][116] | 2个任意精度数字的减法 |
| 117 |     [bin2hex][117] | 函数把包含数据的二进制字符串转换为十六进制值 |
| 118 |     [bind_textdomain_codeset][118] | Specify the character encoding in which the messages from the DOMAIN message catalog will be returned |
| 119 |     [bindec][119] | 二进制转换为十进制 |
| 120 |     [bindtextdomain][120] | Sets the path for a domain |
| 121 |     [boolval][121] | 获取变量的布尔值 |
| 122 |     [bzclose][122] | 关闭一个 bzip2 文件 |
| 123 |     [bzcompress][123] | 把一个字符串压缩成 bzip2 编码数据 |
| 124 |     [bzdecompress][124] | 解压经 bzip2 编码过的数据 |
| 125 |     [bzerrno][125] | 返回一个 bzip2 错误码 |
| 126 |     [bzerror][126] | 返回包含 bzip2 错误号和错误字符串的一个 array |
| 127 |     [bzerrstr][127] | 返回一个 bzip2 的错误字符串 |
| 128 |     [bzflush][128] | 强制写入所有写缓冲区的数据 |
| 129 |     [bzopen][129] | 打开 bzip2 压缩文件 |
| 130 |     [bzread][130] | bzip2 文件二进制安全地读取 |
| 131 |     [bzwrite][131] | 二进制安全地写入 bzip2 文件 |

| c |-|-|
|-|-|-|
| 132 |     [cal_from_jd][132] | 转换Julian Day计数到一个支持的历法。 |
| 133 |     [cal_info][133] | 返回选定历法的信息 |
| 134 |     [cal_to_jd][134] | 从一个支持的历法转变为Julian Day计数。 |
| 135 |     [call_user_func][135] | 把第一个参数作为回调函数调用 |
| 136 |     [call_user_func_array][136] | 调用回调函数，并把一个数组参数作为回调函数的参数 |
| 137 |     [call_user_method][137] | 对特定对象调用用户方法 |
| 138 |     [call_user_method_array][138] | 以参数列表的数组，调用用户方法 |
| 139 |     [ceil][139] | 进一法取整 |
| 140 |     [chdir][140] | 改变目录 |
| 141 |     [checkdate][141] | 验证一个格里高里日期 |
| 142 |     [checkdnsrr][142] | 给指定的主机（域名）或者IP地址做DNS通信检查 |
| 143 |     [chgrp][143] | 改变文件所属的组 |
| 144 |     [chmod][144] | 改变文件模式 |
| 145 |     [chop][145] |  [rtrim()][146] 的别名 |
| 146 |     [chown][147] | 改变文件的所有者 |
| 147 |     [chr][148] | 返回指定的字符 |
| 148 |     [chroot][149] | 改变根目录 |
| 149 |     [chunk_split][150] | 将字符串分割成小块 |
| 150 |     [class_alias][151] | 为一个类创建别名 |
| 151 |     [class_exists][152] | 检查类是否已定义 |
| 152 |     [class_implements][153] | 返回指定的类实现的所有接口。 |
| 153 |     [class_parents][154] | 返回指定类的父类。 |
| 154 |     [class_uses][155] | Return the traits used by the given class |
| 155 |     [clearstatcache][156] | 清除文件状态缓存 |
| 156 |     [cli_get_process_title][157] | Returns the current process title |
| 157 |     [cli_set_process_title][158] | Sets the process title |
| 158 |     [closedir][159] | 关闭目录句柄 |
| 159 |     [closelog][160] | 关闭系统日志链接 |
| 160 |     [collator::asort][161] | Sort array maintaining index association |
| 161 |     [collator::compare][162] | Compare two Unicode strings |
| 162 |     [collator::getattribute][163] | Get collation attribute value |
| 163 |     [collator::geterrorcode][164] | Get collator's last error code |
| 164 |     [collator::geterrormessage][165] | Get text for collator's last error code |
| 165 |     [collator::getlocale][166] | Get the locale name of the collator |
| 166 |     [collator::getsortkey][167] | Get sorting key for a string |
| 167 |     [collator::getstrength][168] | Get current collation strength |
| 168 |     [collator::setattribute][169] | Set collation attribute |
| 169 |     [collator::setstrength][170] | Set collation strength |
| 170 |     [collator::sort][171] | Sort array using specified collator |
| 171 |     [collator::sortwithsortkeys][172] | Sort array using specified collator and sort keys |
| 172 |     [collator_asort][173] | Sort array maintaining index association |
| 173 |     [collator_compare][174] | Compare two Unicode strings |
| 174 |     [collator_create][175] | Create a collator |
| 175 |     [collator_sort][176] | Sort array using specified collator |
| 176 |     [compact][177] | 建立一个数组，包括变量名和它们的值 |
| 177 |     [connection_aborted][178] | 检查客户端是否已经断开 |
| 178 |     [connection_status][179] | 返回连接的状态位 |
| 179 |     [constant][180] | 返回一个常量的值 |
| 180 |     [convert_cyr_string][181] | 将字符由一种 Cyrillic 字符转换成另一种 |
| 181 |     [convert_uudecode][182] | 解码一个 uuencode 编码的字符串 |
| 182 |     [convert_uuencode][183] | 使用 uuencode 编码一个字符串 |
| 183 |     [copy][184] | 拷贝文件 |
| 184 |     [cos][185] | 余弦 |
| 185 |     [cosh][186] | 双曲余弦 |
| 186 |     [count][187] | 计算数组中的单元数目，或对象中的属性个数 |
| 187 |     [count_chars][188] | 返回字符串所用字符的信息 |
| 188 |     [crc32][189] | 计算一个字符串的 crc32 多项式 |
| 189 |     [create_function][190] | Create an anonymous (lambda-style) function |
| 190 |     [crypt][191] | 单向字符串散列 |
| 191 |     [ctype_alnum][192] | 做字母和数字字符检测 |
| 192 |     [ctype_alpha][193] | 做纯字符检测 |
| 193 |     [ctype_cntrl][194] | 做控制字符检测 |
| 194 |     [ctype_digit][195] | 做纯数字检测 |
| 195 |     [ctype_graph][196] | 做可打印字符串检测，空格除外 |
| 196 |     [ctype_lower][197] | 做小写字符检测 |
| 197 |     [ctype_print][198] | 做可打印字符检测 |
| 198 |     [ctype_punct][199] | 检测可打印的字符是不是不包含空白、数字和字母 |
| 199 |     [ctype_space][200] | 做空白字符检测 |
| 200 |     [ctype_upper][201] | 做大写字母检测 |
| 201 |     [ctype_xdigit][202] | 检测字符串是否只包含十六进制字符 |
| 202 |     [cubrid_affected_rows][203] | Return the number of rows affected by the last SQL statement |
| 203 |     [cubrid_bind][204] | Bind variables to a prepared statement as parameters |
| 204 |     [cubrid_client_encoding][205] | Return the current CUBRID connection charset |
| 205 |     [cubrid_close][206] | Close CUBRID connection |
| 206 |     [cubrid_close_prepare][207] | Close the request handle |
| 207 |     [cubrid_close_request][208] | Close the request handle |
| 208 |     [cubrid_col_get][209] | Get contents of collection type column using OID |
| 209 |     [cubrid_col_size][210] | Get the number of elements in collection type column using OID |
| 210 |     [cubrid_column_names][211] | Get the column names in result |
| 211 |     [cubrid_column_types][212] | Get column types in result |
| 212 |     [cubrid_commit][213] | Commit a transaction |
| 213 |     [cubrid_connect][214] | Open a connection to a CUBRID Server |
| 214 |     [cubrid_connect_with_url][215] | Establish the environment for connecting to CUBRID server |
| 215 |     [cubrid_current_oid][216] | Get OID of the current cursor location |
| 216 |     [cubrid_data_seek][217] | Move the internal row pointer of the CUBRID result |
| 217 |     [cubrid_db_name][218] | Get db name from results of cubrid_list_dbs |
| 218 |     [cubrid_disconnect][219] | Close a database connection |
| 219 |     [cubrid_drop][220] | Delete an instance using OID |
| 220 |     [cubrid_errno][221] | Return the numerical value of the error message from previous CUBRID operation |
| 221 |     [cubrid_error][222] | Get the error message |
| 222 |     [cubrid_error_code][223] | Get error code for the most recent function call |
| 223 |     [cubrid_error_code_facility][224] | Get the facility code of error |
| 224 |     [cubrid_error_msg][225] | Get last error message for the most recent function call |
| 225 |     [cubrid_execute][226] | Execute a prepared SQL statement |
| 226 |     [cubrid_fetch][227] | Fetch the next row from a result set |
| 227 |     [cubrid_fetch_array][228] | Fetch a result row as an associative array, a numeric array, or both |
| 228 |     [cubrid_fetch_assoc][229] | Return the associative array that corresponds to the fetched row |
| 229 |     [cubrid_fetch_field][230] | Get column information from a result and return as an object |
| 230 |     [cubrid_fetch_lengths][231] | Return an array with the lengths of the values of each field from the current row |
| 231 |     [cubrid_fetch_object][232] | Fetch the next row and return it as an object |
| 232 |     [cubrid_fetch_row][233] | Return a numerical array with the values of the current row |
| 233 |     [cubrid_field_flags][234] | Return a string with the flags of the given field offset |
| 234 |     [cubrid_field_len][235] | Get the maximum length of the specified field |
| 235 |     [cubrid_field_name][236] | Return the name of the specified field index |
| 236 |     [cubrid_field_seek][237] | Move the result set cursor to the specified field offset |
| 237 |     [cubrid_field_table][238] | Return the name of the table of the specified field |
| 238 |     [cubrid_field_type][239] | Return the type of the column corresponding to the given field offset |
| 239 |     [cubrid_free_result][240] | Free the memory occupied by the result data |
| 240 |     [cubrid_get][241] | Get a column using OID |
| 241 |     [cubrid_get_autocommit][242] | Get auto-commit mode of the connection |
| 242 |     [cubrid_get_charset][243] | Return the current CUBRID connection charset |
| 243 |     [cubrid_get_class_name][244] | Get the class name using OID |
| 244 |     [cubrid_get_client_info][245] | Return the client library version |
| 245 |     [cubrid_get_db_parameter][246] | Returns the CUBRID database parameters |
| 246 |     [cubrid_get_query_timeout][247] | Get the query timeout value of the request |
| 247 |     [cubrid_get_server_info][248] | Return the CUBRID server version |
| 248 |     [cubrid_insert_id][249] | Return the ID generated for the last updated **`AUTO_INCREMENT`** column |
| 249 |     [cubrid_is_instance][250] | Check whether the instance pointed by OID exists |
| 250 |     [cubrid_list_dbs][251] | Return an array with the list of all existing CUBRID databases |
| 251 |     [cubrid_lob2_bind][252] | Bind a lob object or a string as a lob object to a prepared statement as parameters |
| 252 |     [cubrid_lob2_close][253] | Close LOB object |
| 253 |     [cubrid_lob2_export][254] | Export the lob object to a file |
| 254 |     [cubrid_lob2_import][255] | Import BLOB/CLOB data from a file |
| 255 |     [cubrid_lob2_new][256] | Create a lob object |
| 256 |     [cubrid_lob2_read][257] | Read from BLOB/CLOB data |
| 257 |     [cubrid_lob2_seek][258] | Move the cursor of a lob object |
| 258 |     [cubrid_lob2_seek64][259] | Move the cursor of a lob object |
| 259 |     [cubrid_lob2_size][260] | Get a lob object's size |
| 260 |     [cubrid_lob2_size64][261] | Get a lob object's size |
| 261 |     [cubrid_lob2_tell][262] | Tell the cursor position of the LOB object |
| 262 |     [cubrid_lob2_tell64][263] | Tell the cursor position of the LOB object |
| 263 |     [cubrid_lob_close][264] | Close BLOB/CLOB data |
| 264 |     [cubrid_lob_export][265] | Export BLOB/CLOB data to file |
| 265 |     [cubrid_lob_get][266] | Get BLOB/CLOB data |
| 266 |     [cubrid_lob_send][267] | Read BLOB/CLOB data and send straight to browser |
| 267 |     [cubrid_lob_size][268] | Get BLOB/CLOB data size |
| 268 |     [cubrid_lock_read][269] | Set a read lock on the given OID |
| 269 |     [cubrid_lock_write][270] | Set a write lock on the given OID |
| 270 |     [cubrid_move_cursor][271] | Move the cursor in the result |
| 271 |     [cubrid_next_result][272] | Get result of next query when executing multiple SQL statements |
| 272 |     [cubrid_num_cols][273] | Return the number of columns in the result set |
| 273 |     [cubrid_num_fields][274] | Return the number of columns in the result set |
| 274 |     [cubrid_num_rows][275] | Get the number of rows in the result set |
| 275 |     [cubrid_pconnect][276] | Open a persistent connection to a CUBRID server |
| 276 |     [cubrid_pconnect_with_url][277] | Open a persistent connection to CUBRID server |
| 277 |     [cubrid_ping][278] | Ping a server connection or reconnect if there is no connection |
| 278 |     [cubrid_prepare][279] | Prepare a SQL statement for execution |
| 279 |     [cubrid_put][280] | Update a column using OID |
| 280 |     [cubrid_query][281] | Send a CUBRID query |
| 281 |     [cubrid_real_escape_string][282] | Escape special characters in a string for use in an SQL statement |
| 282 |     [cubrid_result][283] | Return the value of a specific field in a specific row |
| 283 |     [cubrid_rollback][284] | Roll back a transaction |
| 284 |     [cubrid_schema][285] | Get the requested schema information |
| 285 |     [cubrid_seq_drop][286] | Delete an element from sequence type column using OID |
| 286 |     [cubrid_seq_insert][287] | Insert an element to a sequence type column using OID |
| 287 |     [cubrid_seq_put][288] | Update the element value of sequence type column using OID |
| 288 |     [cubrid_set_add][289] | Insert a single element to set type column using OID |
| 289 |     [cubrid_set_autocommit][290] | Set autocommit mode of the connection |
| 290 |     [cubrid_set_db_parameter][291] | Sets the CUBRID database parameters |
| 291 |     [cubrid_set_drop][292] | Delete an element from set type column using OID |
| 292 |     [cubrid_set_query_timeout][293] | Set the timeout time of query execution |
| 293 |     [cubrid_unbuffered_query][294] | Perform a query without fetching the results into memory |
| 294 |     [cubrid_version][295] | Get the CUBRID PHP module's version |
| 295 |     [curl_close][296] | 关闭 cURL 会话 |
| 296 |     [curl_copy_handle][297] | 复制一个cURL句柄和它的所有选项 |
| 297 |     [curl_errno][298] | 返回最后一次的错误代码 |
| 298 |     [curl_error][299] | 返回当前会话最后一次错误的字符串 |
| 299 |     [curl_escape][300] | 使用 URL 编码给定的字符串 |
| 300 |     [curl_exec][301] | 执行 cURL 会话 |
| 301 |     [curl_file_create][302] | 创建一个 CURLFile 对象 |
| 302 |     [curl_getinfo][303] | 获取一个cURL连接资源句柄的信息 |
| 303 |     [curl_init][304] | 初始化 cURL 会话 |
| 304 |     [curl_multi_add_handle][305] | 向curl批处理会话中添加单独的curl句柄 |
| 305 |     [curl_multi_close][306] | 关闭一组cURL句柄 |
| 306 |     [curl_multi_errno][307] | 返回上一次 curl 批处理的错误码 |
| 307 |     [curl_multi_exec][308] | 运行当前 cURL 句柄的子连接 |
| 308 |     [curl_multi_getcontent][309] | 如果设置了 **`CURLOPT_RETURNTRANSFER`** ，则返回获取的输出的文本流 |
| 309 |     [curl_multi_info_read][310] | 获取当前解析的cURL的相关传输信息 |
| 310 |     [curl_multi_init][311] | 返回一个新cURL批处理句柄 |
| 311 |     [curl_multi_remove_handle][312] | 移除cURL批处理句柄资源中的某个句柄资源 |
| 312 |     [curl_multi_select][313] | 等待所有cURL批处理中的活动连接 |
| 313 |     [curl_multi_setopt][314] | 为 cURL 并行处理设置一个选项 |
| 314 |     [curl_multi_strerror][315] | 返回字符串描述的错误代码 |
| 315 |     [curl_pause][316] | 暂停和取消暂停一个连接。 |
| 316 |     [curl_reset][317] | 重置一个 libcurl 会话句柄的所有的选项 |
| 317 |     [curl_setopt][318] | 设置 cURL 传输选项 |
| 318 |     [curl_setopt_array][319] | 为 cURL 传输会话批量设置选项 |
| 319 |     [curl_share_close][320] | 关闭 cURL 共享句柄 |
| 320 |     [curl_share_errno][321] | Return the last share curl error number |
| 321 |     [curl_share_init][322] | 初始化一个 cURL 共享句柄。 |
| 322 |     [curl_share_setopt][323] | 为 cURL 共享句柄设置选项。 |
| 323 |     [curl_share_strerror][324] | Return string describing the given error code |
| 324 |     [curl_strerror][325] | 返回错误代码的字符串描述 |
| 325 |     [curl_unescape][326] | 解码给定的 URL 编码的字符串 |
| 326 |     [curl_version][327] | 获取 cURL 版本信息 |
| 327 |     [curlfile::getfilename][328] | 获取被上传文件的 文件名 |
| 328 |     [curlfile::getmimetype][329] | 获取被上传文件的 MIME 类型 |
| 329 |     [curlfile::getpostfilename][330] | 获取 POST 请求时使用的 文件名 |
| 330 |     [curlfile::setmimetype][331] | 设置被上传文件的 MIME 类型 |
| 331 |     [curlfile::setpostfilename][332] | 设置 POST 请求时使用的文件名 |
| 332 |     [current][333] | 返回数组中的当前单元 |

| d |-|-|
|-|-|-|
| 333 |     [date_add][334] | 别名     [DateTime::add()][335]|
| 334 |     [date_create][336] | 别名     [DateTime::__construct()][337]|
| 335 |     [date_create_from_format][338] | 别名     [DateTime::createFromFormat()][339]|
| 336 |     [date_create_immutable][340] | 别名     [DateTimeImmutable::__construct()][341]|
| 337 |     [date_date_set][342] | 别名     [DateTime::setDate()][343]|
| 338 |     [date_default_timezone_get][344] | 取得一个脚本中所有日期时间函数所使用的默认时区 |
| 339 |     [date_default_timezone_set][345] | 设定用于一个脚本中所有日期时间函数的默认时区 |
| 340 |     [date_diff][346] | 别名     [DateTime::diff()][347]|
| 341 |     [date_format][348] | 别名     [DateTime::format()][349]|
| 342 |     [date_get_last_errors][350] | 别名     [DateTime::getLastErrors()][351]|
| 343 |     [date_interval_create_from_date_string][352] | 别名     [DateInterval::createFromDateString()][353]|
| 344 |     [date_interval_format][354] | 别名     [DateInterval::format()][355]|
| 345 |     [date_isodate_set][356] | 别名     [DateTime::setISODate()][357]|
| 346 |     [date_modify][358] | 别名     [DateTime::modify()][359]|
| 347 |     [date_offset_get][360] | 别名     [DateTime::getOffset()][361]|
| 348 |     [date_parse][362] | Returns associative array with detailed info about given date |
| 349 |     [date_parse_from_format][363] | Get info about given date formatted according to the specified format |
| 350 |     [date_sub][364] | 别名          [DateTime::sub()][365] |
| 351 |     [date_sun_info][366] | Returns an array with information about sunset/sunrise and twilight begin/end |
| 352 |     [date_sunrise][367] | 返回给定的日期与地点的日出时间 |
| 353 |     [date_sunset][368] | 返回给定的日期与地点的日落时间 |
| 354 |     [date_time_set][369] | 别名          [DateTime::setTime()][370] |
| 355 |     [date_timestamp_get][371] | 别名          [DateTime::getTimestamp()][372] |
| 356 |     [date_timestamp_set][373] | 别名          [DateTime::setTimestamp()][374] |
| 357 |     [date_timezone_get][375] | 别名          [DateTime::getTimezone()][376] |
| 358 |     [date_timezone_set][377] | 别名          [DateTime::setTimezone()][378] |
| 359 |     [dateinterval::format][379] | Formats the interval |
| 360 |     [datetimezone::getlocation][380] | 返回与时区相关的定位信息。 |
| 361 |     [datetimezone::getname][381] | 返回时区名称。 |
| 362 |     [datetimezone::getoffset][382] | 返回相对于 GMT 的时差。 |
| 363 |     [datetimezone::gettransitions][383] | Returns all transitions for the timezone |
| 364 |     [db2_autocommit][384] | Returns or sets the AUTOCOMMIT state for a database connection |
| 365 |     [db2_bind_param][385] | Binds a PHP variable to an SQL statement parameter |
| 366 |     [db2_client_info][386] | Returns an object with properties that describe the DB2 database client |
| 367 |     [db2_close][387] | Closes a database connection |
| 368 |     [db2_column_privileges][388] | Returns a result set listing the columns and associated privileges for a table |
| 369 |     [db2_columns][389] | Returns a result set listing the columns and associated metadata for a table |
| 370 |     [db2_commit][390] | Commits a transaction |
| 371 |     [db2_conn_error][391] | Returns a string containing the SQLSTATE returned by the last connection attempt |
| 372 |     [db2_conn_errormsg][392] | Returns the last connection error message and SQLCODE value |
| 373 |     [db2_connect][393] | Returns a connection to a database |
| 374 |     [db2_cursor_type][394] | Returns the cursor type used by a statement resource |
| 375 |     [db2_escape_string][395] | Used to escape certain characters |
| 376 |     [db2_exec][396] | Executes an SQL statement directly |
| 377 |     [db2_execute][397] | Executes a prepared SQL statement |
| 378 |     [db2_fetch_array][398] | Returns an array, indexed by column position, representing a row in a result set |
| 379 |     [db2_fetch_assoc][399] | Returns an array, indexed by column name, representing a row in a result set |
| 380 |     [db2_fetch_both][400] | Returns an array, indexed by both column name and position, representing a row in a result set |
| 381 |     [db2_fetch_object][401] | Returns an object with properties representing columns in the fetched row |
| 382 |     [db2_fetch_row][402] | Sets the result set pointer to the next row or requested row |
| 383 |     [db2_field_display_size][403] | Returns the maximum number of bytes required to display a column |
| 384 |     [db2_field_name][404] | Returns the name of the column in the result set |
| 385 |     [db2_field_num][405] | Returns the position of the named column in a result set |
| 386 |     [db2_field_precision][406] | Returns the precision of the indicated column in a result set |
| 387 |     [db2_field_scale][407] | Returns the scale of the indicated column in a result set |
| 388 |     [db2_field_type][408] | Returns the data type of the indicated column in a result set |
| 389 |     [db2_field_width][409] | Returns the width of the current value of the indicated column in a result set |
| 390 |     [db2_foreign_keys][410] | Returns a result set listing the foreign keys for a table |
| 391 |     [db2_free_result][411] | Frees resources associated with a result set |
| 392 |     [db2_free_stmt][412] | Frees resources associated with the indicated statement resource |
| 393 |     [db2_get_option][413] | Retrieves an option value for a statement resource or a connection resource |
| 394 |     [db2_last_insert_id][414] | Returns the auto generated ID of the last insert query that successfully executed on this connection |
| 395 |     [db2_lob_read][415] | Gets a user defined size of LOB files with each invocation |
| 396 |     [db2_next_result][416] | Requests the next result set from a stored procedure |
| 397 |     [db2_num_fields][417] | Returns the number of fields contained in a result set |
| 398 |     [db2_num_rows][418] | Returns the number of rows affected by an SQL statement |
| 399 |     [db2_pconnect][419] | Returns a persistent connection to a database |
| 400 |     [db2_prepare][420] | Prepares an SQL statement to be executed |
| 401 |     [db2_primary_keys][421] | Returns a result set listing primary keys for a table |
| 402 |     [db2_procedure_columns][422] | Returns a result set listing stored procedure parameters |
| 403 |     [db2_procedures][423] | Returns a result set listing the stored procedures registered in a database |
| 404 |     [db2_result][424] | Returns a single column from a row in the result set |
| 405 |     [db2_rollback][425] | Rolls back a transaction |
| 406 |     [db2_server_info][426] | Returns an object with properties that describe the DB2 database server |
| 407 |     [db2_set_option][427] | Set options for connection or statement resources |
| 408 |     [db2_special_columns][428] | Returns a result set listing the unique row identifier columns for a table |
| 409 |     [db2_statistics][429] | Returns a result set listing the index and statistics for a table |
| 410 |     [db2_stmt_error][430] | Returns a string containing the SQLSTATE returned by an SQL statement |
| 411 |     [db2_stmt_errormsg][431] | Returns a string containing the last SQL statement error message |
| 412 |     [db2_table_privileges][432] | Returns a result set listing the tables and associated privileges in a database |
| 413 |     [db2_tables][433] | Returns a result set listing the tables and associated metadata in a database |
| 414 |     [dba_close][434] | Close a DBA database |
| 415 |     [dba_delete][435] | Delete DBA entry specified by key |
| 416 |     [dba_exists][436] | Check whether key exists |
| 417 |     [dba_fetch][437] | Fetch data specified by key |
| 418 |     [dba_firstkey][438] | Fetch first key |
| 419 |     [dba_handlers][439] | List all the handlers available |
| 420 |     [dba_insert][440] | Insert entry |
| 421 |     [dba_key_split][441] | Splits a key in string representation into array representation |
| 422 |     [dba_list][442] | List all open database files |
| 423 |     [dba_nextkey][443] | Fetch next key |
| 424 |     [dba_open][444] | Open database |
| 425 |     [dba_optimize][445] | Optimize database |
| 426 |     [dba_popen][446] | Open database persistently |
| 427 |     [dba_replace][447] | Replace or insert entry |
| 428 |     [dba_sync][448] | Synchronize database |
| 429 |     [dcgettext][449] | Overrides the domain for a single lookup |
| 430 |     [dcngettext][450] | Plural version of dcgettext |
| 431 |     [debug_backtrace][451] | 产生一条回溯跟踪(backtrace) |
| 432 |     [debug_print_backtrace][452] | 打印一条回溯。 |
| 433 |     [debug_zval_dump][453] | Dumps a string representation of an internal zend value to output |
| 434 |     [decbin][454] | 十进制转换为二进制 |
| 435 |     [dechex][455] | 十进制转换为十六进制 |
| 436 |     [decoct][456] | 十进制转换为八进制 |
| 437 |     [define][457] | 定义一个常量 |
| 438 |     [define_syslog_variables][458] | Initializes all syslog related variables |
| 439 |     [defined][459] | 检查某个名称的常量是否存在 |
| 440 |     [deflate_add][460] | Incrementally deflate data |
| 441 |     [deflate_init][461] | Initialize an incremental deflate context |
| 442 |     [deg2rad][462] | 将角度转换为弧度 |
| 443 |     [dgettext][463] | Override the current domain |
| 444 |     [dir][464] | 返回一个 Directory 类实例 |
| 445 |     [directory::close][465] | 释放目录句柄 |
| 446 |     [directory::read][466] | 从目录句柄中读取条目 |
| 447 |     [directory::rewind][467] | 倒回目录句柄 |
| 448 |     [dirname][468] | 返回路径中的目录部分 |
| 449 |     [disk_free_space][469] | 返回目录中的可用空间 |
| 450 |     [disk_total_space][470] | 返回一个目录的磁盘总大小 |
| 451 |     [diskfreespace][471] |  [disk_free_space()][472]的别名 |
| 452 |     [dl][473] | 运行时载入一个 PHP 扩展 |
| 453 |     [dngettext][474] | Plural version of dgettext |
| 454 |     [dns_check_record][475] | 别名          [checkdnsrr()][476] |
| 455 |     [dns_get_mx][477] | 别名          [getmxrr()][478] |
| 456 |     [dom_import_simplexml][479] | Gets a  [DOMElement][480] object from a   [SimpleXMLElement][481]object |
| 457 |     [domimplementation::createdocument][482] | Creates a DOMDocument object of the specified type with its document element |
| 458 |     [domimplementation::createdocumenttype][483] | Creates an empty DOMDocumentType object |
| 459 |     [domnode::appendchild][484] | Adds new child at the end of the children |
| 460 |     [domnode::c14n][485] | Canonicalize nodes to a string |
| 461 |     [domnode::c14nfile][486] | Canonicalize nodes to a file |
| 462 |     [domnode::clonenode][487] | Clones a node |
| 463 |     [domnode::getlineno][488] | Get line number for a node |
| 464 |     [domnode::getnodepath][489] | Get an XPath for a node |
| 465 |     [domnode::hasattributes][490] | Checks if node has attributes |
| 466 |     [domnode::haschildnodes][491] | Checks if node has children |
| 467 |     [domnode::insertbefore][492] | Adds a new child before a reference node |
| 468 |     [domnode::isdefaultnamespace][493] | Checks if the specified namespaceURI is the default namespace or not |
| 469 |     [domnode::issamenode][494] | Indicates if two nodes are the same node |
| 470 |     [domnode::issupported][495] | Checks if feature is supported for specified version |
| 471 |     [domnode::lookupnamespaceuri][496] | Gets the namespace URI of the node based on the prefix |
| 472 |     [domnode::lookupprefix][497] | Gets the namespace prefix of the node based on the namespace URI |
| 473 |     [domnode::normalize][498] | Normalizes the node |
| 474 |     [domnode::removechild][499] | Removes child from list of children |
| 475 |     [domnode::replacechild][500] | Replaces a child |
| 476 |     [domxpath::evaluate][501] | Evaluates the given XPath expression and returns a typed result if possible |
| 477 |     [domxpath::query][502] | Evaluates the given XPath expression |
| 478 |     [domxpath::registernamespace][503] | Registers the namespace with the          [DOMXPath][504]object |
| 479 |     [domxpath::registerphpfunctions][505] | Register PHP functions as XPath functions |
| 480 |     [doubleval][506] |  [floatval()][507]的别名 |

| e |-|-|
|-|-|-|
| 481 |     [easter_date][508] | 得到指定年份的复活节午夜时的Unix时间戳。 |
| 482 |     [easter_days][509] | 得到指定年份的3月21日到复活节之间的天数 |
| 483 |     [enchant_broker_describe][510] | Enumerates the Enchant providers |
| 484 |     [enchant_broker_dict_exists][511] | Whether a dictionary exists or not. Using non-empty tag |
| 485 |     [enchant_broker_free][512] | Free the broker resource and its dictionnaries |
| 486 |     [enchant_broker_free_dict][513] | Free a dictionary resource |
| 487 |     [enchant_broker_get_dict_path][514] | Get the directory path for a given backend |
| 488 |     [enchant_broker_get_error][515] | Returns the last error of the broker |
| 489 |     [enchant_broker_init][516] | Create a new broker object capable of requesting |
| 490 |     [enchant_broker_list_dicts][517] | Returns a list of available dictionaries |
| 491 |     [enchant_broker_request_dict][518] | Create a new dictionary using a tag |
| 492 |     [enchant_broker_request_pwl_dict][519] | Creates a dictionary using a PWL file |
| 493 |     [enchant_broker_set_dict_path][520] | Set the directory path for a given backend |
| 494 |     [enchant_broker_set_ordering][521] | Declares a preference of dictionaries to use for the language |
| 495 |     [enchant_dict_add_to_personal][522] | Add a word to personal word list |
| 496 |     [enchant_dict_add_to_session][523] | Add 'word' to this spell-checking session |
| 497 |     [enchant_dict_check][524] | Check whether a word is correctly spelled or not |
| 498 |     [enchant_dict_describe][525] | Describes an individual dictionary |
| 499 |     [enchant_dict_get_error][526] | Returns the last error of the current spelling-session |
| 500 |     [enchant_dict_is_in_session][527] | Whether or not 'word' exists in this spelling-session |
| 501 |     [enchant_dict_quick_check][528] | Check the word is correctly spelled and provide suggestions |
| 502 |     [enchant_dict_store_replacement][529] | Add a correction for a word |
| 503 |     [enchant_dict_suggest][530] | Will return a list of values if any of those pre-conditions are not met |
| 504 |     [end][531] | 将数组的内部指针指向最后一个单元 |
| 505 |     [ereg][532] | 正则表达式匹配 |
| 506 |     [ereg_replace][533] | 正则表达式替换 |
| 507 |     [eregi][534] | 不区分大小写的正则表达式匹配 |
| 508 |     [eregi_replace][535] | 不区分大小写的正则表达式替换 |
| 509 |     [error_clear_last][536] | 清除最近一次错误 |
| 510 |     [error_get_last][537] | 获取最后发生的错误 |
| 511 |     [error_log][538] | 发送错误信息到某个地方 |
| 512 |     [error_reporting][539] | 设置应该报告何种 PHP 错误 |
| 513 |     [escapeshellarg][540] | 把字符串转码为可以在 shell 命令里使用的参数 |
| 514 |     [escapeshellcmd][541] | shell 元字符转义 |
| 515 |     [event_add][542] | Add an event to the set of monitored events |
| 516 |     [event_base_free][543] | Destroy event base |
| 517 |     [event_base_loop][544] | Handle events |
| 518 |     [event_base_loopbreak][545] | Abort event loop |
| 519 |     [event_base_loopexit][546] | Exit loop after a time |
| 520 |     [event_base_new][547] | Create and initialize new event base |
| 521 |     [event_base_priority_init][548] | Set the number of event priority levels |
| 522 |     [event_base_set][549] | Associate event base with an event |
| 523 |     [event_buffer_base_set][550] | Associate buffered event with an event base |
| 524 |     [event_buffer_disable][551] | Disable a buffered event |
| 525 |     [event_buffer_enable][552] | Enable a buffered event |
| 526 |     [event_buffer_fd_set][553] | Change a buffered event file descriptor |
| 527 |     [event_buffer_free][554] | Destroy buffered event |
| 528 |     [event_buffer_new][555] | Create new buffered event |
| 529 |     [event_buffer_priority_set][556] | Assign a priority to a buffered event |
| 530 |     [event_buffer_read][557] | Read data from a buffered event |
| 531 |     [event_buffer_set_callback][558] | Set or reset callbacks for a buffered event |
| 532 |     [event_buffer_timeout_set][559] | Set read and write timeouts for a buffered event |
| 533 |     [event_buffer_watermark_set][560] | Set the watermarks for read and write events |
| 534 |     [event_buffer_write][561] | Write data to a buffered event |
| 535 |     [event_del][562] | Remove an event from the set of monitored events |
| 536 |     [event_free][563] | Free event resource |
| 537 |     [event_new][564] | Create new event |
| 538 |     [event_set][565] | Prepare an event |
| 539 |     [event_timer_add][566] | 别名          [event_add()][567] |
| 540 |     [event_timer_del][568] | 别名          [event_del()][569] |
| 541 |     [event_timer_new][570] | 别名          [event_new()][571] |
| 542 |     [event_timer_set][572] | Prepare a timer event |
| 543 |     [exec][573] | 执行一个外部程序 |
| 544 |     [exif_imagetype][574] | 判断一个图像的类型 |
| 545 |     [exif_read_data][575] | 从  <acronym>JPEG</acronym>或 <acronym>TIFF</acronym>文件中读取 <acronym>EXIF</acronym>头信息 |
| 546 |     [exif_tagname][576] | 获取指定索引的头名称 |
| 547 |     [exif_thumbnail][577] | 取得嵌入在 TIFF 或 JPEG 图像中的缩略图 |
| 548 |     [exp][578] | 计算 **`e`** 的指数 |
| 549 |     [explode][579] | 使用一个字符串分割另一个字符串 |
| 550 |     [expm1][580] | 返回 exp(number) - 1，甚至当 number 的值接近零也能计算出准确结果 |
| 551 |     [extension_loaded][581] | 检查一个扩展是否已经加载 |
| 552 |     [extract][582] | 从数组中将变量导入到当前的符号表 |
| 553 |     [ezmlm_hash][583] | 计算 EZMLM 所需的散列值 |

| f |-|-|
|-|-|-|
| 554 |     [fclose][584] | 关闭一个已打开的文件指针 |
| 555 |     [feof][585] | 测试文件指针是否到了文件结束的位置 |
| 556 |     [fflush][586] | 将缓冲内容输出到文件 |
| 557 |     [fgetc][587] | 从文件指针中读取字符 |
| 558 |     [fgetcsv][588] | 从文件指针中读入一行并解析 CSV 字段 |
| 559 |     [fgets][589] | 从文件指针中读取一行 |
| 560 |     [fgetss][590] | 从文件指针中读取一行并过滤掉 HTML 标记 |
| 561 |     [file][591] | 把整个文件读入一个数组中 |
| 562 |     [file_exists][592] | 检查文件或目录是否存在 |
| 563 |     [file_get_contents][593] | 将整个文件读入一个字符串 |
| 564 |     [file_put_contents][594] | 将一个字符串写入文件 |
| 565 |     [fileatime][595] | 取得文件的上次访问时间 |
| 566 |     [filectime][596] | 取得文件的 inode 修改时间 |
| 567 |     [filegroup][597] | 取得文件的组 |
| 568 |     [fileinode][598] | 取得文件的 inode |
| 569 |     [filemtime][599] | 取得文件修改时间 |
| 570 |     [fileowner][600] | 取得文件的所有者 |
| 571 |     [fileperms][601] | 取得文件的权限 |
| 572 |     [filesize][602] | 取得文件大小 |
| 573 |     [filetype][603] | 取得文件类型 |
| 574 |     [filter_has_var][604] | 检测是否存在指定类型的变量 |
| 575 |     [filter_id][605] | 返回与某个特定名称的过滤器相关联的id |
| 576 |     [filter_input][606] | 通过名称获取特定的外部变量，并且可以通过过滤器处理它 |
| 577 |     [filter_input_array][607] | 获取一系列外部变量，并且可以通过过滤器处理它们 |
| 578 |     [filter_list][608] | 返回所支持的过滤器列表 |
| 579 |     [filter_var][609] | 使用特定的过滤器过滤一个变量 |
| 580 |     [filter_var_array][610] | 获取多个变量并且过滤它们 |
| 581 |     [finfo_buffer][611] | 返回一个字符串缓冲区的信息 |
| 582 |     [finfo_close][612] | 关闭 fileinfo 资源 |
| 583 |     [finfo_file][613] | 返回一个文件的信息 |
| 584 |     [finfo_open][614] | 创建一个 fileinfo 资源 |
| 585 |     [finfo_set_flags][615] | 设置 libmagic 配置选项 |
| 586 |     [floatval][616] | 获取变量的浮点值 |
| 587 |     [flock][617] | 轻便的咨询文件锁定 |
| 588 |     [floor][618] | 舍去法取整 |
| 589 |     [flush][619] | 刷新输出缓冲 |
| 590 |     [fmod][620] | 返回除法的浮点数余数 |
| 591 |     [fnmatch][621] | 用模式匹配文件名 |
| 592 |     [fopen][622] | 打开文件或者 URL |
| 593 |     [forward_static_call][623] | Call a static method |
| 594 |     [forward_static_call_array][624] | Call a static method and pass the arguments as array |
| 595 |     [fpassthru][625] | 输出文件指针处的所有剩余数据 |
| 596 |     [fprintf][626] | 将格式化后的字符串写入到流 |
| 597 |     [fputcsv][627] | 将行格式化为 CSV 并写入文件指针 |
| 598 |     [fputs][628] |  [fwrite()][629]的别名 |
| 599 |     [fread][630] | 读取文件（可安全用于二进制文件） |
| 600 |     [frenchtojd][631] | 从一个French Republican历法的日期得到Julian Day计数。 |
| 601 |     [fscanf][632] | 从文件中格式化输入 |
| 602 |     [fseek][633] | 在文件指针中定位 |
| 603 |     [fsockopen][634] | 打开一个网络连接或者一个Unix套接字连接 |
| 604 |     [fstat][635] | 通过已打开的文件指针取得文件信息 |
| 605 |     [ftell][636] | 返回文件指针读/写的位置 |
| 606 |     [ftok][637] | Convert a pathname and a project identifier to a System V IPC key |
| 607 |     [ftp_alloc][638] | 为要上传的文件分配空间 |
| 608 |     [ftp_cdup][639] | 切换到当前目录的父目录 |
| 609 |     [ftp_chdir][640] | 在 FTP 服务器上改变当前目录 |
| 610 |     [ftp_chmod][641] | 设置 FTP 服务器上的文件权限 |
| 611 |     [ftp_close][642] | 关闭一个 FTP 连接 |
| 612 |     [ftp_connect][643] | 建立一个新的 FTP 连接 |
| 613 |     [ftp_delete][644] | 删除 FTP 服务器上的一个文件 |
| 614 |     [ftp_exec][645] | 请求运行一条 FTP 命令 |
| 615 |     [ftp_fget][646] | 从 FTP 服务器上下载一个文件并保存到本地一个已经打开的文件中 |
| 616 |     [ftp_fput][647] | 上传一个已经打开的文件到 FTP 服务器 |
| 617 |     [ftp_get][648] | 从 FTP 服务器上下载一个文件 |
| 618 |     [ftp_get_option][649] | 返回当前 FTP 连接的各种不同的选项设置 |
| 619 |     [ftp_login][650] | 登录 FTP 服务器 |
| 620 |     [ftp_mdtm][651] | 返回指定文件的最后修改时间 |
| 621 |     [ftp_mkdir][652] | 建立新目录 |
| 622 |     [ftp_nb_continue][653] | 连续获取／发送文件（non-blocking） |
| 623 |     [ftp_nb_fget][654] | 从 FTP 服务器获取文件并写入到一个打开的文件（非阻塞） |
| 624 |     [ftp_nb_fput][655] | 将文件存储到 FTP 服务器 （非阻塞） |
| 625 |     [ftp_nb_get][656] | 从 FTP 服务器上获取文件并写入本地文件（non-blocking） |
| 626 |     [ftp_nb_put][657] | 存储一个文件至 FTP 服务器（non-blocking） |
| 627 |     [ftp_nlist][658] | 返回给定目录的文件列表 |
| 628 |     [ftp_pasv][659] | 返回当前 FTP 被动模式是否打开 |
| 629 |     [ftp_put][660] | 上传文件到 FTP 服务器 |
| 630 |     [ftp_pwd][661] | 返回当前目录名 |
| 631 |     [ftp_quit][662] |   [ftp_close()][663]的 别名 |
| 632 |     [ftp_raw][664] | 向 FTP 服务器发送命令 |
| 633 |     [ftp_rawlist][665] | 返回指定目录下文件的详细列表 |
| 634 |     [ftp_rename][666] | 更改 FTP 服务器上的文件或目录名 |
| 635 |     [ftp_rmdir][667] | 删除 FTP 服务器上的一个目录 |
| 636 |     [ftp_set_option][668] | 设置各种 FTP 运行时选项 |
| 637 |     [ftp_site][669] | 向服务器发送 SITE 命令 |
| 638 |     [ftp_size][670] | 返回指定文件的大小 |
| 639 |     [ftp_ssl_connect][671] | 打开 SSL-FTP 连接 |
| 640 |     [ftp_systype][672] | 返回远程 FTP 服务器的操作系统类型 |
| 641 |     [ftruncate][673] | 将文件截断到给定的长度 |
| 642 |     [func_get_arg][674] | 返回参数列表的某一项 |
| 643 |     [func_get_args][675] | 返回一个包含函数参数列表的数组 |
| 644 |     [func_num_args][676] | Returns the number of arguments passed to the function |
| 645 |     [function_exists][677] | 如果给定的函数已经被定义就返回  ** `TRUE`** |
| 646 |     [fwrite][678] | 写入文件（可安全用于二进制文件） |

| g |-|-|
|-|-|-|
| 647 |     [gc_disable][679] | 停用循环引用收集器 |
| 648 |     [gc_enable][680] | 激活循环引用收集器 |
| 649 |     [gc_enabled][681] | 返回循环引用计数器的状态 |
| 650 |     [gc_mem_caches][682] | Reclaims memory used by the Zend Engine memory manager |
| 651 |     [gd_info][683] | 取得当前安装的 GD 库的信息 |
| 652 |     [gearman_client_clone][684] | Create a copy of a          [GearmanClient][685]object |
| 653 |     [gearman_client_context][686] | Get the application context |
| 654 |     [gearman_client_do][687] | Run a single task and return a result [deprecated] |
| 655 |     [gearman_client_echo][688] | Send data to all job servers to see if they echo it back [deprecated] |
| 656 |     [gearman_client_error][689] | Returns an error string for the last error encountered |
| 657 |     [gearman_client_timeout][690] | Get current socket I/O activity timeout value |
| 658 |     [gearman_job_handle][691] | Get the job handle |
| 659 |     [gearman_job_unique][692] | Get the unique identifier |
| 660 |     [gearman_job_workload][693] | Get workload |
| 661 |     [gearman_task_data][694] | Get data returned for a task |
| 662 |     [gearman_task_unique][695] | Get the unique identifier for a task |
| 663 |     [gearman_worker_clone][696] | Create a copy of the worker |
| 664 |     [gearman_worker_echo][697] | Test job server response |
| 665 |     [gearman_worker_error][698] | Get the last error encountered |
| 666 |     [gearman_worker_options][699] | Get worker options |
| 667 |     [gearman_worker_register][700] | Register a function with the job server |
| 668 |     [gearman_worker_timeout][701] | Get socket I/O activity timeout |
| 669 |     [gearman_worker_unregister][702] | Unregister a function name with the job servers |
| 670 |     [gearman_worker_wait][703] | Wait for activity from one of the job servers |
| 671 |     [gearman_worker_work][704] | Wait for and perform jobs |
| 672 |     [gearmanclient::addoptions][705] | Add client options |
| 673 |     [gearmanclient::addserver][706] | Add a job server to the client |
| 674 |     [gearmanclient::addservers][707] | Add a list of job servers to the client |
| 675 |     [gearmanclient::addtask][708] | Add a task to be run in parallel |
| 676 |     [gearmanclient::addtaskbackground][709] | Add a background task to be run in parallel |
| 677 |     [gearmanclient::addtaskhigh][710] | Add a high priority task to run in parallel |
| 678 |     [gearmanclient::addtaskhighbackground][711] | Add a high priority background task to be run in parallel |
| 679 |     [gearmanclient::addtasklow][712] | Add a low priority task to run in parallel |
| 680 |     [gearmanclient::addtasklowbackground][713] | Add a low priority background task to be run in parallel |
| 681 |     [gearmanclient::addtaskstatus][714] | Add a task to get status |
| 682 |     [gearmanclient::clearcallbacks][715] | Clear all task callback functions |
| 683 |     [gearmanclient::context][716] | Get the application context |
| 684 |     [gearmanclient::dobackground][717] | Run a task in the background |
| 685 |     [gearmanclient::dohigh][718] | Run a single high priority task |
| 686 |     [gearmanclient::dohighbackground][719] | Run a high priority task in the background |
| 687 |     [gearmanclient::dojobhandle][720] | Get the job handle for the running task |
| 688 |     [gearmanclient::dolow][721] | Run a single low priority task |
| 689 |     [gearmanclient::dolowbackground][722] | Run a low priority task in the background |
| 690 |     [gearmanclient::donormal][723] | Run a single task and return a result |
| 691 |     [gearmanclient::dostatus][724] | Get the status for the running task |
| 692 |     [gearmanclient::error][725] | Returns an error string for the last error encountered |
| 693 |     [gearmanclient::geterrno][726] | Get an errno value |
| 694 |     [gearmanclient::jobstatus][727] | Get the status of a background job |
| 695 |     [gearmanclient::removeoptions][728] | Remove client options |
| 696 |     [gearmanclient::returncode][729] | Get the last Gearman return code |
| 697 |     [gearmanclient::runtasks][730] | Run a list of tasks in parallel |
| 698 |     [gearmanclient::setcompletecallback][731] | Set a function to be called on task completion |
| 699 |     [gearmanclient::setcontext][732] | Set application context |
| 700 |     [gearmanclient::setcreatedcallback][733] | Set a callback for when a task is queued |
| 701 |     [gearmanclient::setdatacallback][734] | Callback function when there is a data packet for a task |
| 702 |     [gearmanclient::setexceptioncallback][735] | Set a callback for worker exceptions |
| 703 |     [gearmanclient::setfailcallback][736] | Set callback for job failure |
| 704 |     [gearmanclient::setoptions][737] | Set client options |
| 705 |     [gearmanclient::setstatuscallback][738] | Set a callback for collecting task status |
| 706 |     [gearmanclient::settimeout][739] | Set socket I/O activity timeout |
| 707 |     [gearmanclient::setwarningcallback][740] | Set a callback for worker warnings |
| 708 |     [gearmanclient::setworkloadcallback][741] | Set a callback for accepting incremental data updates |
| 709 |     [gearmanclient::timeout][742] | Get current socket I/O activity timeout value |
| 710 |     [gearmanjob::functionname][743] | Get function name |
| 711 |     [gearmanjob::handle][744] | Get the job handle |
| 712 |     [gearmanjob::returncode][745] | Get last return code |
| 713 |     [gearmanjob::sendcomplete][746] | Send the result and complete status |
| 714 |     [gearmanjob::senddata][747] | Send data for a running job |
| 715 |     [gearmanjob::sendexception][748] | Send exception for running job (exception) |
| 716 |     [gearmanjob::sendfail][749] | Send fail status |
| 717 |     [gearmanjob::sendstatus][750] | Send status |
| 718 |     [gearmanjob::sendwarning][751] | Send a warning |
| 719 |     [gearmanjob::setreturn][752] | Set a return value |
| 720 |     [gearmanjob::unique][753] | Get the unique identifier |
| 721 |     [gearmanjob::workload][754] | Get workload |
| 722 |     [gearmanjob::workloadsize][755] | Get size of work load |
| 723 |     [gearmantask::data][756] | Get data returned for a task |
| 724 |     [gearmantask::datasize][757] | Get the size of returned data |
| 725 |     [gearmantask::functionname][758] | Get associated function name |
| 726 |     [gearmantask::isknown][759] | Determine if task is known |
| 727 |     [gearmantask::isrunning][760] | Test whether the task is currently running |
| 728 |     [gearmantask::jobhandle][761] | Get the job handle |
| 729 |     [gearmantask::recvdata][762] | Read work or result data into a buffer for a task |
| 730 |     [gearmantask::returncode][763] | Get the last return code |
| 731 |     [gearmantask::sendworkload][764] | Send data for a task |
| 732 |     [gearmantask::taskdenominator][765] | Get completion percentage denominator |
| 733 |     [gearmantask::tasknumerator][766] | Get completion percentage numerator |
| 734 |     [gearmantask::unique][767] | Get the unique identifier for a task |
| 735 |     [gearmanworker::addfunction][768] | Register and add callback function |
| 736 |     [gearmanworker::addoptions][769] | Add worker options |
| 737 |     [gearmanworker::addserver][770] | Add a job server |
| 738 |     [gearmanworker::addservers][771] | Add job servers |
| 739 |     [gearmanworker::error][772] | Get the last error encountered |
| 740 |     [gearmanworker::geterrno][773] | Get errno |
| 741 |     [gearmanworker::options][774] | Get worker options |
| 742 |     [gearmanworker::register][775] | Register a function with the job server |
| 743 |     [gearmanworker::removeoptions][776] | Remove worker options |
| 744 |     [gearmanworker::returncode][777] | Get last Gearman return code |
| 745 |     [gearmanworker::setid][778] | Give the worker an identifier so it can be tracked when asking gearmand for the list of available workers |
| 746 |     [gearmanworker::setoptions][779] | Set worker options |
| 747 |     [gearmanworker::settimeout][780] | Set socket I/O activity timeout |
| 748 |     [gearmanworker::timeout][781] | Get socket I/O activity timeout |
| 749 |     [gearmanworker::unregister][782] | Unregister a function name with the job servers |
| 750 |     [gearmanworker::unregisterall][783] | Unregister all function names with the job servers |
| 751 |     [gearmanworker::wait][784] | Wait for activity from one of the job servers |
| 752 |     [gearmanworker::work][785] | Wait for and perform jobs |
| 753 |     [geoip_continent_code_by_name][786] | 获取七大洲的大写字母简称 |
| 754 |     [geoip_country_code3_by_name][787] | 获取三个字母组成的国家简称 |
| 755 |     [geoip_country_code_by_name][788] | 获取国家代码 |
| 756 |     [geoip_country_name_by_name][789] | 获取国家的全称 |
| 757 |     [geoip_database_info][790] | 获取 GeoIP 数据库的信息 |
| 758 |     [geoip_db_avail][791] | GeoIP 数据库是否可用 |
| 759 |     [geoip_db_filename][792] | 返回 GeoIP 数据库相对应的文件名 |
| 760 |     [geoip_db_get_all_info][793] | 返回所有 GeoIP 数据库类型的详细信息 |
| 761 |     [geoip_id_by_name][794] | 获取网络连接类型 |
| 762 |     [geoip_isp_by_name][795] | 获取 ISP (网络服务提供商)的名称 |
| 763 |     [geoip_org_by_name][796] | 获取机构的名称 |
| 764 |     [geoip_record_by_name][797] | 返回 GeoIP 数据库中详细的城市信息 |
| 765 |     [geoip_region_by_name][798] | 获取国家和地区代码 |
| 766 |     [geoip_region_name_by_code][799] | 返回给定的国家和地区代码组合所对应的地区名称 |
| 767 |     [geoip_time_zone_by_country_and_region][800] | 返回国家和地区的时区 |
| 768 |     [get_browser][801] | 获取浏览器具有的功能 |
| 769 |     [get_called_class][802] | 后期静态绑定（"Late Static Binding"）类的名称 |
| 770 |     [get_cfg_var][803] | 获取 PHP 配置选项的值 |
| 771 |     [get_class][804] | 返回对象的类名 |
| 772 |     [get_class_methods][805] | 返回由类的方法名组成的数组 |
| 773 |     [get_class_vars][806] | 返回由类的默认属性组成的数组 |
| 774 |     [get_current_user][807] | 获取当前 PHP 脚本所有者名称 |
| 775 |     [get_declared_classes][808] | 返回由已定义类的名字所组成的数组 |
| 776 |     [get_declared_interfaces][809] | 返回一个数组包含所有已声明的接口 |
| 777 |     [get_declared_traits][810] | 返回所有已定义的 traits 的数组 |
| 778 |     [get_defined_constants][811] | 返回所有常量的关联数组，键是常量名，值是常量值 |
| 779 |     [get_defined_functions][812] | 返回所有已定义函数的数组 |
| 780 |     [get_defined_vars][813] | 返回由所有已定义变量所组成的数组 |
| 781 |     [get_extension_funcs][814] | 返回模块函数名称的数组 |
| 782 |     [get_headers][815] | 取得服务器响应一个 HTTP 请求所发送的所有标头 |
| 783 |     [get_html_translation_table][816] | 返回使用     [htmlspecialchars()][817]和   [htmlentities()][818]后的转换表 |
| 784 |     [get_include_path][819] | 获取当前的 include_path 配置选项 |
| 785 |     [get_included_files][820] | 返回被 include 和 require 文件名的 array |
| 786 |     [get_loaded_extensions][821] | 返回所有编译并加载模块名的 array |
| 787 |     [get_magic_quotes_gpc][822] | 获取当前 magic_quotes_gpc 的配置选项设置 |
| 788 |     [get_magic_quotes_runtime][823] | 获取当前 magic_quotes_runtime 配置选项的激活状态 |
| 789 |     [get_meta_tags][824] | 从一个文件中提取所有的 meta 标签 content 属性，返回一个数组 |
| 790 |     [get_object_vars][825] | 返回由对象属性组成的关联数组 |
| 791 |     [get_parent_class][826] | 返回对象或类的父类名 |
| 792 |     [get_required_files][827] | 别名          [get_included_files()][828] |
| 793 |     [get_resource_type][829] | 返回资源（resource）类型 |
| 794 |     [get_resources][830] | Returns active resources |
| 795 |     [getallheaders][831] | 获取全部 HTTP 请求头信息 |
| 796 |     [getcwd][832] | 取得当前工作目录 |
| 797 |     [getdate][833] | 取得日期／时间信息 |
| 798 |     [getenv][834] | 获取一个环境变量的值 |
| 799 |     [gethostbyaddr][835] | 获取指定的IP地址对应的主机名 |
| 800 |     [gethostbyname][836] | 返回主机名对应的 IPv4地址。 |
| 801 |     [gethostbynamel][837] | 获取互联网主机名对应的 IPv4 地址列表 |
| 802 |     [gethostname][838] | 获取主机名 |
| 803 |     [getimagesize][839] | 取得图像大小 |
| 804 |     [getimagesizefromstring][840] | 从字符串中获取图像尺寸信息 |
| 805 |     [getlastmod][841] | 获取页面最后修改的时间 |
| 806 |     [getmxrr][842] | 获取互联网主机名对应的 MX 记录 |
| 807 |     [getmygid][843] | 获取当前 PHP 脚本拥有者的 GID |
| 808 |     [getmypid][844] | 获取 PHP 进程的 ID |
| 809 |     [getmyuid][845] | 获取 PHP 脚本所有者的 UID |
| 810 |     [getopt][846] | 从命令行参数列表中获取选项 |
| 811 |     [getprotobyname][847] | Get protocol number associated with protocol name |
| 812 |     [getprotobynumber][848] | Get protocol name associated with protocol number |
| 813 |     [getrandmax][849] | 显示随机数最大的可能值 |
| 814 |     [getrusage][850] | 获取当前资源使用状况 |
| 815 |     [getservbyname][851] | 获取互联网服务协议对应的端口 |
| 816 |     [getservbyport][852] | Get Internet service which corresponds to port and protocol |
| 817 |     [gettext][853] | Lookup a message in the current domain |
| 818 |     [gettimeofday][854] | 取得当前时间 |
| 819 |     [gettype][855] | 获取变量的类型 |
| 820 |     [glob][856] | 寻找与模式匹配的文件路径 |
| 821 |     [gmdate][857] | 格式化一个 GMT/UTC 日期／时间 |
| 822 |     [gmmktime][858] | 取得 GMT 日期的 UNIX 时间戳 |
| 823 |     [gmp_abs][859] | Absolute value |
| 824 |     [gmp_add][860] | Add numbers |
| 825 |     [gmp_and][861] | Bitwise AND |
| 826 |     [gmp_clrbit][862] | Clear bit |
| 827 |     [gmp_cmp][863] | Compare numbers |
| 828 |     [gmp_com][864] | Calculates one's complement |
| 829 |     [gmp_div][865] | 别名          [gmp_div_q()][866] |
| 830 |     [gmp_div_q][867] | Divide numbers |
| 831 |     [gmp_div_qr][868] | Divide numbers and get quotient and remainder |
| 832 |     [gmp_div_r][869] | Remainder of the division of numbers |
| 833 |     [gmp_divexact][870] | Exact division of numbers |
| 834 |     [gmp_export][871] | Export to a binary string |
| 835 |     [gmp_fact][872] | Factorial |
| 836 |     [gmp_gcd][873] | Calculate GCD |
| 837 |     [gmp_gcdext][874] | Calculate GCD and multipliers |
| 838 |     [gmp_hamdist][875] | Hamming distance |
| 839 |     [gmp_import][876] | Import from a binary string |
| 840 |     [gmp_init][877] | Create GMP number |
| 841 |     [gmp_intval][878] | Convert GMP number to integer |
| 842 |     [gmp_invert][879] | Inverse by modulo |
| 843 |     [gmp_jacobi][880] | Jacobi symbol |
| 844 |     [gmp_legendre][881] | Legendre symbol |
| 845 |     [gmp_mod][882] | Modulo operation |
| 846 |     [gmp_mul][883] | Multiply numbers |
| 847 |     [gmp_neg][884] | Negate number |
| 848 |     [gmp_nextprime][885] | Find next prime number |
| 849 |     [gmp_or][886] | Bitwise OR |
| 850 |     [gmp_perfect_square][887] | Perfect square check |
| 851 |     [gmp_popcount][888] | Population count |
| 852 |     [gmp_pow][889] | Raise number into power |
| 853 |     [gmp_powm][890] | Raise number into power with modulo |
| 854 |     [gmp_prob_prime][891] | Check if number is "probably prime" |
| 855 |     [gmp_random][892] | Random number |
| 856 |     [gmp_random_seed][893] | Sets the RNG seed |
| 857 |     [gmp_root][894] | Take the integer part of nth root |
| 858 |     [gmp_rootrem][895] | Take the integer part and remainder of nth root |
| 859 |     [gmp_scan0][896] | Scan for 0 |
| 860 |     [gmp_scan1][897] | Scan for 1 |
| 861 |     [gmp_setbit][898] | Set bit |
| 862 |     [gmp_sign][899] | Sign of number |
| 863 |     [gmp_sqrt][900] | Calculate square root |
| 864 |     [gmp_sqrtrem][901] | Square root with remainder |
| 865 |     [gmp_strval][902] | Convert GMP number to string |
| 866 |     [gmp_sub][903] | Subtract numbers |
| 867 |     [gmp_testbit][904] | Tests if a bit is set |
| 868 |     [gmp_xor][905] | Bitwise XOR |
| 869 |     [gmstrftime][906] | 根据区域设置格式化 GMT/UTC 时间／日期 |
| 870 |     [grapheme_extract][907] | Function to extract a sequence of default grapheme clusters from a text buffer, which must be encoded in UTF-8 |
| 871 |     [grapheme_stripos][908] | Find position (in grapheme units) of first occurrence of a case-insensitive string |
| 872 |     [grapheme_stristr][909] | Returns part of haystack string from the first occurrence of case-insensitive needle to the end of haystack |
| 873 |     [grapheme_strlen][910] | Get string length in grapheme units |
| 874 |     [grapheme_strpos][911] | Find position (in grapheme units) of first occurrence of a string |
| 875 |     [grapheme_strripos][912] | Find position (in grapheme units) of last occurrence of a case-insensitive string |
| 876 |     [grapheme_strrpos][913] | Find position (in grapheme units) of last occurrence of a string |
| 877 |     [grapheme_strstr][914] | Returns part of haystack string from the first occurrence of needle to the end of haystack |
| 878 |     [grapheme_substr][915] | Return part of a string |
| 879 |     [gregoriantojd][916] | 转变一个Gregorian历法日期到Julian Day计数 |
| 880 |     [gzclose][917] | Close an open gz-file pointer |
| 881 |     [gzcompress][918] | Compress a string |
| 882 |     [gzdecode][919] | Decodes a gzip compressed string |
| 883 |     [gzdeflate][920] | Deflate a string |
| 884 |     [gzencode][921] | Create a gzip compressed string |
| 885 |     [gzeof][922] | Test for          <acronym>EOF</acronym>on a gz-file pointer |
| 886 |     [gzfile][923] | Read entire gz-file into an array |
| 887 |     [gzgetc][924] | Get character from gz-file pointer |
| 888 |     [gzgets][925] | Get line from file pointer |
| 889 |     [gzgetss][926] | Get line from gz-file pointer and strip HTML tags |
| 890 |     [gzinflate][927] | Inflate a deflated string |
| 891 |     [gzopen][928] | Open gz-file |
| 892 |     [gzpassthru][929] | Output all remaining data on a gz-file pointer |
| 893 |     [gzputs][930] | 别名          [gzwrite()][931] |
| 894 |     [gzread][932] | Binary-safe gz-file read |
| 895 |     [gzrewind][933] | Rewind the position of a gz-file pointer |
| 896 |     [gzseek][934] | Seek on a gz-file pointer |
| 897 |     [gztell][935] | Tell gz-file pointer read/write position |
| 898 |     [gzuncompress][936] | Uncompress a compressed string |
| 899 |     [gzwrite][937] | Binary-safe gz-file write |

| h |-|-|
|-|-|-|
| 900 |     [hash_algos][938] | 返回已注册的哈希算法列表 |
| 901 |     [hash_copy][939] | 拷贝哈希运算上下文 |
| 902 |     [hash_equals][940] | 可防止时序攻击的字符串比较 |
| 903 |     [hash_file][941] | 使用给定文件的内容生成哈希值 |
| 904 |     [hash_final][942] | 结束增量哈希，并且返回摘要结果 |
| 905 |     [hash_hmac][943] | 使用 HMAC 方法生成带有密钥的哈希值 |
| 906 |     [hash_hmac_file][944] | 使用 HMAC 方法和给定文件的内容生成带密钥的哈希值 |
| 907 |     [hash_init][945] | 初始化增量哈希运算上下文 |
| 908 |     [hash_pbkdf2][946] | 生成所提供密码的 PBKDF2 密钥导出 |
| 909 |     [hash_update][947] | 向活跃的哈希运算上下文中填充数据 |
| 910 |     [hash_update_file][948] | 从文件向活跃的哈希运算上下文中填充数据 |
| 911 |     [hash_update_stream][949] | 从打开的流向活跃的哈希运算上下文中填充数据 |
| 912 |     [header][950] | 发送原生 HTTP 头 |
| 913 |     [header_register_callback][951] | 调用一个 header 函数 |
| 914 |     [header_remove][952] | 删除之前设置的 HTTP 头 |
| 915 |     [headers_list][953] | 返回已发送的 HTTP 响应头（或准备发送的） |
| 916 |     [headers_sent][954] | 检测 HTTP 头是否已经发送 |
| 917 |     [hebrev][955] | 将逻辑顺序希伯来文（logical-Hebrew）转换为视觉顺序希伯来文（visual-Hebrew） |
| 918 |     [hebrevc][956] | 将逻辑顺序希伯来文（logical-Hebrew）转换为视觉顺序希伯来文（visual-Hebrew），并且转换换行符 |
| 919 |     [hex2bin][957] | 转换十六进制字符串为二进制字符串 |
| 920 |     [hexdec][958] | 十六进制转换为十进制 |
| 921 |     [highlight_file][959] | 语法高亮一个文件 |
| 922 |     [highlight_string][960] | 字符串的语法高亮 |
| 923 |     [html_entity_decode][961] | Convert all HTML entities to their applicable characters |
| 924 |     [htmlentities][962] | 将字符转换为 HTML 转义字符 |
| 925 |     [htmlspecialchars][963] | 将特殊字符转换为 HTML 实体 |
| 926 |     [htmlspecialchars_decode][964] | 将特殊的 HTML 实体转换回普通字符 |
| 927 |     [http_build_query][965] | 生成 URL-encode 之后的请求字符串 |
| 928 |     [http_response_code][966] | 获取/设置响应的 HTTP 状态码 |
| 929 |     [hypot][967] | 计算一直角三角形的斜边长度 |

| i |-|-|
|-|-|-|
| 930 |     [ibase_affected_rows][968] | Return the number of rows that were affected by the previous query |
| 931 |     [ibase_backup][969] | Initiates a backup task in the service manager and returns immediately |
| 932 |     [ibase_blob_add][970] | Add data into a newly created blob |
| 933 |     [ibase_blob_cancel][971] | Cancel creating blob |
| 934 |     [ibase_blob_close][972] | Close blob |
| 935 |     [ibase_blob_create][973] | Create a new blob for adding data |
| 936 |     [ibase_blob_echo][974] | Output blob contents to browser |
| 937 |     [ibase_blob_get][975] | Get len bytes data from open blob |
| 938 |     [ibase_blob_import][976] | Create blob, copy file in it, and close it |
| 939 |     [ibase_blob_info][977] | Return blob length and other useful info |
| 940 |     [ibase_blob_open][978] | Open blob for retrieving data parts |
| 941 |     [ibase_close][979] | Close a connection to an InterBase database |
| 942 |     [ibase_commit][980] | Commit a transaction |
| 943 |     [ibase_commit_ret][981] | Commit a transaction without closing it |
| 944 |     [ibase_connect][982] | Open a connection to a database |
| 945 |     [ibase_db_info][983] | Request statistics about a database |
| 946 |     [ibase_delete_user][984] | Delete a user from a security database |
| 947 |     [ibase_drop_db][985] | Drops a database |
| 948 |     [ibase_errcode][986] | Return an error code |
| 949 |     [ibase_errmsg][987] | Return error messages |
| 950 |     [ibase_execute][988] | Execute a previously prepared query |
| 951 |     [ibase_fetch_assoc][989] | Fetch a result row from a query as an associative array |
| 952 |     [ibase_fetch_object][990] | Get an object from a InterBase database |
| 953 |     [ibase_fetch_row][991] | Fetch a row from an InterBase database |
| 954 |     [ibase_field_info][992] | Get information about a field |
| 955 |     [ibase_free_event_handler][993] | Cancels a registered event handler |
| 956 |     [ibase_free_query][994] | Free memory allocated by a prepared query |
| 957 |     [ibase_free_result][995] | Free a result set |
| 958 |     [ibase_gen_id][996] | Increments the named generator and returns its new value |
| 959 |     [ibase_maintain_db][997] | Execute a maintenance command on the database server |
| 960 |     [ibase_modify_user][998] | Modify a user to a security database |
| 961 |     [ibase_name_result][999] | Assigns a name to a result set |
| 962 |     [ibase_num_fields][1000] | Get the number of fields in a result set |
| 963 |     [ibase_num_params][1001] | Return the number of parameters in a prepared query |
| 964 |     [ibase_param_info][1002] | Return information about a parameter in a prepared query |
| 965 |     [ibase_pconnect][1003] | Open a persistent connection to an InterBase database |
| 966 |     [ibase_prepare][1004] | Prepare a query for later binding of parameter placeholders and execution |
| 967 |     [ibase_query][1005] | Execute a query on an InterBase database |
| 968 |     [ibase_restore][1006] | Initiates a restore task in the service manager and returns immediately |
| 969 |     [ibase_rollback][1007] | Roll back a transaction |
| 970 |     [ibase_rollback_ret][1008] | Roll back a transaction without closing it |
| 971 |     [ibase_server_info][1009] | Request information about a database server |
| 972 |     [ibase_service_attach][1010] | Connect to the service manager |
| 973 |     [ibase_service_detach][1011] | Disconnect from the service manager |
| 974 |     [ibase_set_event_handler][1012] | Register a callback function to be called when events are posted |
| 975 |     [ibase_trans][1013] | Begin a transaction |
| 976 |     [ibase_wait_event][1014] | Wait for an event to be posted by the database |
| 977 |     [iconv][1015] | 字符串按要求的字符编码来转换 |
| 978 |     [iconv_get_encoding][1016] | 获取 iconv 扩展的内部配置变量 |
| 979 |     [iconv_mime_decode][1017] | 解码一个           **MIME** 头字段 |
| 980 |     [iconv_mime_decode_headers][1018] | 一次性解码多个           **MIME** 头字段 |
| 981 |     [iconv_mime_encode][1019] | Composes a           **MIME** header field |
| 982 |     [iconv_set_encoding][1020] | 为字符编码转换设定当前设置 |
| 983 |     [iconv_strlen][1021] | 返回字符串的字符数统计 |
| 984 |     [iconv_strpos][1022] | Finds position of first occurrence of a needle within a haystack |
| 985 |     [iconv_strrpos][1023] | Finds the last occurrence of a needle within a haystack |
| 986 |     [iconv_substr][1024] | 截取字符串的部分 |
| 987 |     [idate][1025] | 将本地时间日期格式化为整数 |
| 988 |     [idn_to_ascii][1026] | Convert domain name to IDNA ASCII form |
| 989 |     [idn_to_utf8][1027] | Convert domain name from IDNA ASCII to Unicode |
| 990 |     [ignore_user_abort][1028] | 设置客户端断开连接时是否中断脚本的执行 |
| 991 |     [image2wbmp][1029] | 以 WBMP 格式将图像输出到浏览器或文件 |
| 992 |     [image_type_to_extension][1030] | 取得图像类型的文件后缀 |
| 993 |     [image_type_to_mime_type][1031] | 取得 getimagesize，exif_read_data，exif_thumbnail，exif_imagetype 所返回的图像类型的 MIME 类型 |
| 994 |     [imageaffine][1032] | 返回经过仿射变换后的图像，剪切区域可选 |
| 995 |     [imageaffinematrixconcat][1033] | Concatenate two affine transformation matrices |
| 996 |     [imageaffinematrixget][1034] | Get an affine transformation matrix |
| 997 |     [imagealphablending][1035] | 设定图像的混色模式 |
| 998 |     [imageantialias][1036] | 是否使用抗锯齿（antialias）功能 |
| 999 |     [imagearc][1037] | 画椭圆弧 |
| 1000 |     [imagechar][1038] | 水平地画一个字符 |
| 1001 |     [imagecharup][1039] | 垂直地画一个字符 |
| 1002 |     [imagecolorallocate][1040] | 为一幅图像分配颜色 |
| 1003 |     [imagecolorallocatealpha][1041] | 为一幅图像分配颜色 + alpha |
| 1004 |     [imagecolorat][1042] | 取得某像素的颜色索引值 |
| 1005 |     [imagecolorclosest][1043] | 取得与指定的颜色最接近的颜色的索引值 |
| 1006 |     [imagecolorclosestalpha][1044] | 取得与指定的颜色加透明度最接近的颜色 |
| 1007 |     [imagecolorclosesthwb][1045] | 取得与给定颜色最接近的色度的黑白色的索引 |
| 1008 |     [imagecolordeallocate][1046] | 取消图像颜色的分配 |
| 1009 |     [imagecolorexact][1047] | 取得指定颜色的索引值 |
| 1010 |     [imagecolorexactalpha][1048] | 取得指定的颜色加透明度的索引值 |
| 1011 |     [imagecolormatch][1049] | 使一个图像中调色板版本的颜色与真彩色版本更能匹配 |
| 1012 |     [imagecolorresolve][1050] | 取得指定颜色的索引值或有可能得到的最接近的替代值 |
| 1013 |     [imagecolorresolvealpha][1051] | 取得指定颜色 + alpha 的索引值或有可能得到的最接近的替代值 |
| 1014 |     [imagecolorset][1052] | 给指定调色板索引设定颜色 |
| 1015 |     [imagecolorsforindex][1053] | 取得某索引的颜色 |
| 1016 |     [imagecolorstotal][1054] | 取得一幅图像的调色板中颜色的数目 |
| 1017 |     [imagecolortransparent][1055] | 将某个颜色定义为透明色 |
| 1018 |     [imageconvolution][1056] | 用系数 div 和 offset 申请一个 3x3 的卷积矩阵 |
| 1019 |     [imagecopy][1057] | 拷贝图像的一部分 |
| 1020 |     [imagecopymerge][1058] | 拷贝并合并图像的一部分 |
| 1021 |     [imagecopymergegray][1059] | 用灰度拷贝并合并图像的一部分 |
| 1022 |     [imagecopyresampled][1060] | 重采样拷贝部分图像并调整大小 |
| 1023 |     [imagecopyresized][1061] | 拷贝部分图像并调整大小 |
| 1024 |     [imagecreate][1062] | 新建一个基于调色板的图像 |
| 1025 |     [imagecreatefromgd][1063] | 从 GD 文件或 URL 新建一图像 |
| 1026 |     [imagecreatefromgd2][1064] | 从 GD2 文件或 URL 新建一图像 |
| 1027 |     [imagecreatefromgd2part][1065] | 从给定的 GD2 文件或 URL 中的部分新建一图像 |
| 1028 |     [imagecreatefromgif][1066] | 由文件或 URL 创建一个新图象。 |
| 1029 |     [imagecreatefromjpeg][1067] | 由文件或 URL 创建一个新图象。 |
| 1030 |     [imagecreatefrompng][1068] | 由文件或 URL 创建一个新图象。 |
| 1031 |     [imagecreatefromstring][1069] | 从字符串中的图像流新建一图像 |
| 1032 |     [imagecreatefromwbmp][1070] | 由文件或 URL 创建一个新图象。 |
| 1033 |     [imagecreatefromxbm][1071] | 由文件或 URL 创建一个新图象。 |
| 1034 |     [imagecreatefromxpm][1072] | 由文件或 URL 创建一个新图象。 |
| 1035 |     [imagecreatetruecolor][1073] | 新建一个真彩色图像 |
| 1036 |     [imagecrop][1074] | Crop an image to the given rectangle |
| 1037 |     [imagecropauto][1075] | Crop an image automatically using one of the available modes |
| 1038 |     [imagedashedline][1076] | 画一虚线 |
| 1039 |     [imagedestroy][1077] | 销毁一图像 |
| 1040 |     [imageellipse][1078] | 画一个椭圆 |
| 1041 |     [imagefill][1079] | 区域填充 |
| 1042 |     [imagefilledarc][1080] | 画一椭圆弧且填充 |
| 1043 |     [imagefilledellipse][1081] | 画一椭圆并填充 |
| 1044 |     [imagefilledpolygon][1082] | 画一多边形并填充 |
| 1045 |     [imagefilledrectangle][1083] | 画一矩形并填充 |
| 1046 |     [imagefilltoborder][1084] | 区域填充到指定颜色的边界为止 |
| 1047 |     [imagefilter][1085] | 对图像使用过滤器 |
| 1048 |     [imageflip][1086] | Flips an image using a given mode |
| 1049 |     [imagefontheight][1087] | 取得字体高度 |
| 1050 |     [imagefontwidth][1088] | 取得字体宽度 |
| 1051 |     [imageftbbox][1089] | 给出一个使用 FreeType 2 字体的文本框 |
| 1052 |     [imagefttext][1090] | 使用 FreeType 2 字体将文本写入图像 |
| 1053 |     [imagegammacorrect][1091] | 对 GD 图像应用 gamma 修正 |
| 1054 |     [imagegd][1092] | 将 GD 图像输出到浏览器或文件 |
| 1055 |     [imagegd2][1093] | 将 GD2 图像输出到浏览器或文件 |
| 1056 |     [imagegif][1094] | 输出图象到浏览器或文件。 |
| 1057 |     [imageinterlace][1095] | 激活或禁止隔行扫描 |
| 1058 |     [imageistruecolor][1096] | 检查图像是否为真彩色图像 |
| 1059 |     [imagejpeg][1097] | 输出图象到浏览器或文件。 |
| 1060 |     [imagelayereffect][1098] | 设定 alpha 混色标志以使用绑定的 libgd 分层效果 |
| 1061 |     [imageline][1099] | 画一条线段 |
| 1062 |     [imageloadfont][1100] | 载入一新字体 |
| 1063 |     [imagepalettecopy][1101] | 将调色板从一幅图像拷贝到另一幅 |
| 1064 |     [imagepalettetotruecolor][1102] | Converts a palette based image to true color |
| 1065 |     [imagepng][1103] | 以 PNG 格式将图像输出到浏览器或文件 |
| 1066 |     [imagepolygon][1104] | 画一个多边形 |
| 1067 |     [imagepsbbox][1105] | 给出一个使用 PostScript Type1 字体的文本方框 |
| 1068 |     [imagepsencodefont][1106] | 改变字体中的字符编码矢量 |
| 1069 |     [imagepsextendfont][1107] | 扩充或精简字体 |
| 1070 |     [imagepsfreefont][1108] | 释放一个 PostScript Type 1 字体所占用的内存 |
| 1071 |     [imagepsloadfont][1109] | 从文件中加载一个 PostScript Type 1 字体 |
| 1072 |     [imagepsslantfont][1110] | 倾斜某字体 |
| 1073 |     [imagepstext][1111] | 用 PostScript Type1 字体把文本字符串画在图像上 |
| 1074 |     [imagerectangle][1112] | 画一个矩形 |
| 1075 |     [imagerotate][1113] | 用给定角度旋转图像 |
| 1076 |     [imagesavealpha][1114] | 设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息（与单一透明色相反） |
| 1077 |     [imagescale][1115] | Scale an image using the given new width and height |
| 1078 |     [imagesetbrush][1116] | 设定画线用的画笔图像 |
| 1079 |     [imagesetinterpolation][1117] | Set the interpolation method |
| 1080 |     [imagesetpixel][1118] | 画一个单一像素 |
| 1081 |     [imagesetstyle][1119] | 设定画线的风格 |
| 1082 |     [imagesetthickness][1120] | 设定画线的宽度 |
| 1083 |     [imagesettile][1121] | 设定用于填充的贴图 |
| 1084 |     [imagestring][1122] | 水平地画一行字符串 |
| 1085 |     [imagestringup][1123] | 垂直地画一行字符串 |
| 1086 |     [imagesx][1124] | 取得图像宽度 |
| 1087 |     [imagesy][1125] | 取得图像高度 |
| 1088 |     [imagetruecolortopalette][1126] | 将真彩色图像转换为调色板图像 |
| 1089 |     [imagettfbbox][1127] | 取得使用 TrueType 字体的文本的范围 |
| 1090 |     [imagettftext][1128] | 用 TrueType 字体向图像写入文本 |
| 1091 |     [imagetypes][1129] | 返回当前 PHP 版本所支持的图像类型 |
| 1092 |     [imagewbmp][1130] | 以 WBMP 格式将图像输出到浏览器或文件 |
| 1093 |     [imagexbm][1131] | 将 XBM 图像输出到浏览器或文件 |
| 1094 |     [imap_8bit][1132] | Convert an 8bit string to a quoted-printable string |
| 1095 |     [imap_alerts][1133] | Returns all IMAP alert messages that have occurred |
| 1096 |     [imap_append][1134] | Append a string message to a specified mailbox |
| 1097 |     [imap_base64][1135] | Decode BASE64 encoded text |
| 1098 |     [imap_binary][1136] | Convert an 8bit string to a base64 string |
| 1099 |     [imap_body][1137] | Read the message body |
| 1100 |     [imap_bodystruct][1138] | Read the structure of a specified body section of a specific message |
| 1101 |     [imap_check][1139] | Check current mailbox |
| 1102 |     [imap_clearflag_full][1140] | Clears flags on messages |
| 1103 |     [imap_close][1141] | Close an IMAP stream |
| 1104 |     [imap_create][1142] | 别名          [imap_createmailbox()][1143] |
| 1105 |     [imap_createmailbox][1144] | Create a new mailbox |
| 1106 |     [imap_delete][1145] | Mark a message for deletion from current mailbox |
| 1107 |     [imap_deletemailbox][1146] | Delete a mailbox |
| 1108 |     [imap_errors][1147] | Returns all of the IMAP errors that have occurred |
| 1109 |     [imap_expunge][1148] | Delete all messages marked for deletion |
| 1110 |     [imap_fetch_overview][1149] | Read an overview of the information in the headers of the given message |
| 1111 |     [imap_fetchbody][1150] | Fetch a particular section of the body of the message |
| 1112 |     [imap_fetchheader][1151] | Returns header for a message |
| 1113 |     [imap_fetchmime][1152] | Fetch MIME headers for a particular section of the message |
| 1114 |     [imap_fetchstructure][1153] | Read the structure of a particular message |
| 1115 |     [imap_fetchtext][1154] | 别名          [imap_body()][1155] |
| 1116 |     [imap_gc][1156] | Clears IMAP cache |
| 1117 |     [imap_get_quota][1157] | Retrieve the quota level settings, and usage statics per mailbox |
| 1118 |     [imap_get_quotaroot][1158] | Retrieve the quota settings per user |
| 1119 |     [imap_getacl][1159] | Gets the ACL for a given mailbox |
| 1120 |     [imap_getmailboxes][1160] | Read the list of mailboxes, returning detailed information on each one |
| 1121 |     [imap_getsubscribed][1161] | List all the subscribed mailboxes |
| 1122 |     [imap_header][1162] | 别名          [imap_headerinfo()][1163] |
| 1123 |     [imap_headerinfo][1164] | Read the header of the message |
| 1124 |     [imap_headers][1165] | Returns headers for all messages in a mailbox |
| 1125 |     [imap_last_error][1166] | Gets the last IMAP error that occurred during this page request |
| 1126 |     [imap_list][1167] | Read the list of mailboxes |
| 1127 |     [imap_listmailbox][1168] | 别名          [imap_list()][1169] |
| 1128 |     [imap_listscan][1170] | Returns the list of mailboxes that matches the given text |
| 1129 |     [imap_listsubscribed][1171] | 别名          [imap_lsub()][1172] |
| 1130 |     [imap_lsub][1173] | List all the subscribed mailboxes |
| 1131 |     [imap_mail][1174] | Send an email message |
| 1132 |     [imap_mail_compose][1175] | Create a MIME message based on given envelope and body sections |
| 1133 |     [imap_mail_copy][1176] | Copy specified messages to a mailbox |
| 1134 |     [imap_mail_move][1177] | Move specified messages to a mailbox |
| 1135 |     [imap_mailboxmsginfo][1178] | Get information about the current mailbox |
| 1136 |     [imap_mime_header_decode][1179] | Decode MIME header elements |
| 1137 |     [imap_msgno][1180] | Gets the message sequence number for the given UID |
| 1138 |     [imap_num_msg][1181] | Gets the number of messages in the current mailbox |
| 1139 |     [imap_num_recent][1182] | Gets the number of recent messages in current mailbox |
| 1140 |     [imap_open][1183] | Open an          <acronym>IMAP</acronym>stream to a mailbox |
| 1141 |     [imap_ping][1184] | Check if the IMAP stream is still active |
| 1142 |     [imap_qprint][1185] | Convert a quoted-printable string to an 8 bit string |
| 1143 |     [imap_rename][1186] | 别名          [imap_renamemailbox()][1187] |
| 1144 |     [imap_renamemailbox][1188] | Rename an old mailbox to new mailbox |
| 1145 |     [imap_reopen][1189] | Reopen          <acronym>IMAP</acronym>stream to new mailbox |
| 1146 |     [imap_rfc822_parse_adrlist][1190] | Parses an address string |
| 1147 |     [imap_rfc822_parse_headers][1191] | Parse mail headers from a string |
| 1148 |     [imap_rfc822_write_address][1192] | Returns a properly formatted email address given the mailbox, host, and personal info |
| 1149 |     [imap_savebody][1193] | Save a specific body section to a file |
| 1150 |     [imap_scan][1194] | 别名          [imap_listscan()][1195] |
| 1151 |     [imap_scanmailbox][1196] | 别名          [imap_listscan()][1195] |
| 1152 |     [imap_search][1198] | This function returns an array of messages matching the given search criteria |
| 1153 |     [imap_set_quota][1199] | Sets a quota for a given mailbox |
| 1154 |     [imap_setacl][1200] | Sets the ACL for a given mailbox |
| 1155 |     [imap_setflag_full][1201] | Sets flags on messages |
| 1156 |     [imap_sort][1202] | Gets and sort messages |
| 1157 |     [imap_status][1203] | Returns status information on a mailbox |
| 1158 |     [imap_subscribe][1204] | Subscribe to a mailbox |
| 1159 |     [imap_thread][1205] | Returns a tree of threaded message |
| 1160 |     [imap_timeout][1206] | Set or fetch imap timeout |
| 1161 |     [imap_uid][1207] | This function returns the UID for the given message sequence number |
| 1162 |     [imap_undelete][1208] | Unmark the message which is marked deleted |
| 1163 |     [imap_unsubscribe][1209] | Unsubscribe from a mailbox |
| 1164 |     [imap_utf7_decode][1210] | Decodes a modified UTF-7 encoded string |
| 1165 |     [imap_utf7_encode][1211] | Converts ISO-8859-1 string to modified UTF-7 text |
| 1166 |     [imap_utf8][1212] | Converts MIME-encoded text to UTF-8 |
| 1167 |     [implode][1213] | 将一个一维数组的值转化为字符串 |
| 1168 |     [import_request_variables][1214] | 将 GET／POST／Cookie 变量导入到全局作用域中 |
| 1169 |     [in_array][1215] | 检查数组中是否存在某个值 |
| 1170 |     [inet_ntop][1216] | Converts a packed internet address to a human readable representation |
| 1171 |     [inet_pton][1217] | Converts a human readable IP address to its packed in_addr representation |
| 1172 |     [inflate_add][1218] | Incrementally inflate encoded data |
| 1173 |     [inflate_init][1219] | Initialize an incremental inflate context |
| 1174 |     [ini_alter][1220] | 别名          [ini_set()][1221] |
| 1175 |     [ini_get][1222] | 获取一个配置选项的值 |
| 1176 |     [ini_get_all][1223] | 获取所有配置选项 |
| 1177 |     [ini_restore][1224] | 恢复配置选项的值 |
| 1178 |     [ini_set][1225] | 为一个配置选项设置值 |
| 1179 |     [inotify_add_watch][1226] | Add a watch to an initialized inotify instance |
| 1180 |     [inotify_init][1227] | Initialize an inotify instance |
| 1181 |     [inotify_queue_len][1228] | Return a number upper than zero if there are pending events |
| 1182 |     [inotify_read][1229] | Read events from an inotify instance |
| 1183 |     [inotify_rm_watch][1230] | Remove an existing watch from an inotify instance |
| 1184 |     [intdiv][1231] | 对除法结果取整 |
| 1185 |     [interface_exists][1232] | 检查接口是否已被定义 |
| 1186 |     [intl_error_name][1233] | Get symbolic name for a given error code |
| 1187 |     [intl_get_error_code][1234] | Get the last error code |
| 1188 |     [intl_get_error_message][1235] | Get description of the last error |
| 1189 |     [intl_is_failure][1236] | Check whether the given error code indicates failure |
| 1190 |     [intlcalendar::add][1237] | Add a (signed) amount of time to a field |
| 1191 |     [intlcalendar::after][1238] | Whether this objectʼs time is after that of the passed object |
| 1192 |     [intlcalendar::before][1239] | Whether this objectʼs time is before that of the passed object |
| 1193 |     [intlcalendar::clear][1240] | Clear a field or all fields |
| 1194 |     [intlcalendar::equals][1241] | Compare time of two IntlCalendar objects for equality |
| 1195 |     [intlcalendar::fielddifference][1242] | Calculate difference between given time and this objectʼs time |
| 1196 |     [intlcalendar::get][1243] | Get the value for a field |
| 1197 |     [intlcalendar::getactualmaximum][1244] | The maximum value for a field, considering the objectʼs current time |
| 1198 |     [intlcalendar::getactualminimum][1245] | The minimum value for a field, considering the objectʼs current time |
| 1199 |     [intlcalendar::getdayofweektype][1246] | Tell whether a day is a weekday, weekend or a day that has a transition between the two |
| 1200 |     [intlcalendar::geterrorcode][1247] | Get last error code on the object |
| 1201 |     [intlcalendar::geterrormessage][1248] | Get last error message on the object |
| 1202 |     [intlcalendar::getfirstdayofweek][1249] | Get the first day of the week for the calendarʼs locale |
| 1203 |     [intlcalendar::getgreatestminimum][1250] | Get the largest local minimum value for a field |
| 1204 |     [intlcalendar::getleastmaximum][1251] | Get the smallest local maximum for a field |
| 1205 |     [intlcalendar::getlocale][1252] | Get the locale associated with the object |
| 1206 |     [intlcalendar::getmaximum][1253] | Get the global maximum value for a field |
| 1207 |     [intlcalendar::getminimaldaysinfirstweek][1254] | Get minimal number of days the first week in a year or month can have |
| 1208 |     [intlcalendar::getminimum][1255] | Get the global minimum value for a field |
| 1209 |     [intlcalendar::getrepeatedwalltimeoption][1256] | Get behavior for handling repeating wall time |
| 1210 |     [intlcalendar::getskippedwalltimeoption][1257] | Get behavior for handling skipped wall time |
| 1211 |     [intlcalendar::gettime][1258] | Get time currently represented by the object |
| 1212 |     [intlcalendar::gettimezone][1259] | Get the objectʼs timezone |
| 1213 |     [intlcalendar::gettype][1260] | Get the calendar type |
| 1214 |     [intlcalendar::getweekendtransition][1261] | Get time of the day at which weekend begins or ends |
| 1215 |     [intlcalendar::indaylighttime][1262] | Whether the objectʼs time is in Daylight Savings Time |
| 1216 |     [intlcalendar::isequivalentto][1263] | Whether another calendar is equal but for a different time |
| 1217 |     [intlcalendar::islenient][1264] | Whether date/time interpretation is in lenient mode |
| 1218 |     [intlcalendar::isweekend][1265] | Whether a certain date/time is in the weekend |
| 1219 |     [intlcalendar::roll][1266] | Add value to field without carrying into more significant fields |
| 1220 |     [intlcalendar::set][1267] | Set a time field or several common fields at once |
| 1221 |     [intlcalendar::setfirstdayofweek][1268] | Set the day on which the week is deemed to start |
| 1222 |     [intlcalendar::setlenient][1269] | Set whether date/time interpretation is to be lenient |
| 1223 |     [intlcalendar::setrepeatedwalltimeoption][1270] | Set behavior for handling repeating wall times at negative timezone offset transitions |
| 1224 |     [intlcalendar::setskippedwalltimeoption][1271] | Set behavior for handling skipped wall times at positive timezone offset transitions |
| 1225 |     [intlcalendar::settime][1272] | Set the calendar time in milliseconds since the epoch |
| 1226 |     [intlcalendar::settimezone][1273] | Set the timezone used by this calendar |
| 1227 |     [intlcalendar::todatetime][1274] | Convert an IntlCalendar into a DateTime object |
| 1228 |     [intldateformatter::format][1275] | Format the date/time value as a string |
| 1229 |     [intldateformatter::formatobject][1276] | Formats an object |
| 1230 |     [intldateformatter::getcalendar][1277] | Get the calendar type used for the IntlDateFormatter |
| 1231 |     [intldateformatter::getcalendarobject][1278] | Get copy of formatterʼs calendar object |
| 1232 |     [intldateformatter::getdatetype][1279] | Get the datetype used for the IntlDateFormatter |
| 1233 |     [intldateformatter::geterrorcode][1280] | Get the error code from last operation |
| 1234 |     [intldateformatter::geterrormessage][1281] | Get the error text from the last operation |
| 1235 |     [intldateformatter::getlocale][1282] | Get the locale used by formatter |
| 1236 |     [intldateformatter::getpattern][1283] | Get the pattern used for the IntlDateFormatter |
| 1237 |     [intldateformatter::gettimetype][1284] | Get the timetype used for the IntlDateFormatter |
| 1238 |     [intldateformatter::gettimezone][1285] | Get formatterʼs timezone |
| 1239 |     [intldateformatter::gettimezoneid][1286] | Get the timezone-id used for the IntlDateFormatter |
| 1240 |     [intldateformatter::islenient][1287] | Get the lenient used for the IntlDateFormatter |
| 1241 |     [intldateformatter::localtime][1288] | Parse string to a field-based time value |
| 1242 |     [intldateformatter::parse][1289] | Parse string to a timestamp value |
| 1243 |     [intldateformatter::setcalendar][1290] | Sets the calendar type used by the formatter |
| 1244 |     [intldateformatter::setlenient][1291] | Set the leniency of the parser |
| 1245 |     [intldateformatter::setpattern][1292] | Set the pattern used for the IntlDateFormatter |
| 1246 |     [intldateformatter::settimezone][1293] | Sets formatterʼs timezone |
| 1247 |     [intldateformatter::settimezoneid][1294] | Sets the time zone to use |
| 1248 |     [intltimezone::getdisplayname][1295] | Get a name of this time zone suitable for presentation to the user |
| 1249 |     [intltimezone::getdstsavings][1296] | Get the amount of time to be added to local standard time to get local wall clock time |
| 1250 |     [intltimezone::geterrorcode][1297] | Get last error code on the object |
| 1251 |     [intltimezone::geterrormessage][1298] | Get last error message on the object |
| 1252 |     [intltimezone::getid][1299] | Get timezone ID |
| 1253 |     [intltimezone::getoffset][1300] | Get the time zone raw and GMT offset for the given moment in time |
| 1254 |     [intltimezone::getrawoffset][1301] | Get the raw GMT offset (before taking daylight savings time into account |
| 1255 |     [intltimezone::hassamerules][1302] | Check if this zone has the same rules and offset as another zone |
| 1256 |     [intltimezone::todatetimezone][1303] | Convert to      [DateTimeZone][1304]object |
| 1257 |     [intltimezone::usedaylighttime][1305] | Check if this time zone uses daylight savings time |
| 1258 |     [intval][1306] | 获取变量的整数值 |
| 1259 |     [ip2long][1307] | 将 IPV4 的字符串互联网协议转换成长整型数字 |
| 1260 |     [iptcembed][1308] | 将二进制 IPTC 数据嵌入到一幅 JPEG 图像中 |
| 1261 |     [iptcparse][1309] | 将二进制 IPTC 块解析为单个标记 |
| 1262 |     [is_a][1310] | 如果对象属于该类或该类是此对象的父类则返回   ** `TRUE``** |
| 1263 |     [is_array][1311] | 检测变量是否是数组 |
| 1264 |     [is_bool][1312] | 检测变量是否是布尔型 |
| 1265 |     [is_callable][1313] | 检测参数是否为合法的可调用结构 |
| 1266 |     [is_dir][1314] | 判断给定文件名是否是一个目录 |
| 1267 |     [is_double][1315] |   [is_float()][1316]的别名 |
| 1268 |     [is_executable][1317] | 判断给定文件名是否可执行 |
| 1269 |     [is_file][1318] | 判断给定文件名是否为一个正常的文件 |
| 1270 |     [is_finite][1319] | 判断是否为有限值 |
| 1271 |     [is_float][1320] | 检测变量是否是浮点型 |
| 1272 |     [is_infinite][1321] | 判断是否为无限值 |
| 1273 |     [is_int][1322] | 检测变量是否是整数 |
| 1274 |     [is_integer][1323] |    [is_int()][1324]的别名 |
| 1275 |     [is_iterable][1325] | Verify that the contents of a variable is an iterable value |
| 1276 |     [is_link][1326] | 判断给定文件名是否为一个符号连接 |
| 1277 |     [is_long][1327] | [is_int()][1324]的别名 |
| 1278 |     [is_nan][1329] | 判断是否为合法数值 |
| 1279 |     [is_null][1330] | 检测变量是否为       **`NULL`**  |
| 1280 |     [is_numeric][1331] | 检测变量是否为数字或数字字符串 |
| 1281 |     [is_object][1332] | 检测变量是否是一个对象 |
| 1282 |     [is_readable][1333] | 判断给定文件名是否可读 |
| 1283 |     [is_real][1334] |   [is_float()][1316]的别名 |
| 1284 |     [is_resource][1336] | 检测变量是否为资源类型 |
| 1285 |     [is_scalar][1337] | 检测变量是否是一个标量 |
| 1286 |     [is_soap_fault][1338] | Checks if a SOAP call has failed |
| 1287 |     [is_string][1339] | 检测变量是否是字符串 |
| 1288 |     [is_subclass_of][1340] | 如果此对象是该类的子类，则返回      **`TRUE`**  |
| 1289 |     [is_uploaded_file][1341] | 判断文件是否是通过 HTTP POST 上传的 |
| 1290 |     [is_writable][1342] | 判断给定的文件名是否可写 |
| 1291 |     [is_writeable][1343] |   [is_writable()][1344]的别名 |
| 1292 |     [iterator_apply][1345] | 为迭代器中每个元素调用一个用户自定义函数 |
| 1293 |     [iterator_count][1346] | 计算迭代器中元素的个数 |
| 1294 |     [iterator_to_array][1347] | 将迭代器中的元素拷贝到数组 |

| j |-|-|
|-|-|-|
| 1295 |     [jdmonthname][1348] | 返回月份的名称 |
| 1296 |     [jdtofrench][1349] | 转变一个Julian Day计数到French Republican历法的日期 |
| 1297 |     [jdtogregorian][1350] | 转变一个Julian Day计数为Gregorian历法日期 |
| 1298 |     [jdtojewish][1351] | 转换一个julian天数为Jewish历法的日期 |
| 1299 |     [jdtojulian][1352] | 转变一个Julian Day计数到Julian历法的日期 |
| 1300 |     [jdtounix][1353] | 转变Julian Day计数为一个Unix时间戳 |
| 1301 |     [jewishtojd][1354] | 转变一个Jewish历法的日期为一个Julian Day计数 |
| 1302 |     [join][1355] | 别名          [implode()][1356] |
| 1303 |     [jpeg2wbmp][1357] | 将 JPEG 图像文件转换为 WBMP 图像文件 |
| 1304 |     [json_decode][1358] | 对 JSON 格式的字符串进行解码 |
| 1305 |     [json_encode][1359] | 对变量进行 JSON 编码 |
| 1306 |     [json_last_error][1360] | 返回最后发生的错误 |
| 1307 |     [json_last_error_msg][1361] | Returns the error string of the last json_encode() or json_decode() call |
| 1308 |     [juliantojd][1362] | 转变一个Julian历法的日期为Julian Day计数 |

| k |-|-|
|-|-|-|
| 1309 |     [key_exists][1363] | 别名          [array_key_exists()][1364] |
| 1310 |     [krsort][1365] | 对数组按照键名逆向排序 |
| 1311 |     [ksort][1366] | 对数组按照键名排序 |

| l |-|-|
|-|-|-|
| 1312 |     [lcg_value][1367] | 组合线性同余发生器 |
| 1313 |     [lchgrp][1368] | 修改符号链接的所有组 |
| 1314 |     [lchown][1369] | 修改符号链接的所有者 |
| 1315 |     [ldap_add][1370] | Add entries to LDAP directory |
| 1316 |     [ldap_bind][1371] | 绑定 LDAP 目录 |
| 1317 |     [ldap_close][1372] | 别名          [ldap_unbind()][1373] |
| 1318 |     [ldap_compare][1374] | Compare value of attribute found in entry specified with DN |
| 1319 |     [ldap_connect][1375] | Connect to an LDAP server |
| 1320 |     [ldap_control_paged_result][1376] | Send LDAP pagination control |
| 1321 |     [ldap_control_paged_result_response][1377] | Retrieve the LDAP pagination cookie |
| 1322 |     [ldap_count_entries][1378] | Count the number of entries in a search |
| 1323 |     [ldap_delete][1379] | Delete an entry from a directory |
| 1324 |     [ldap_dn2ufn][1380] | Convert DN to User Friendly Naming format |
| 1325 |     [ldap_err2str][1381] | Convert LDAP error number into string error message |
| 1326 |     [ldap_errno][1382] | Return the LDAP error number of the last LDAP command |
| 1327 |     [ldap_error][1383] | Return the LDAP error message of the last LDAP command |
| 1328 |     [ldap_escape][1384] | Escape a string for use in an LDAP filter or DN |
| 1329 |     [ldap_explode_dn][1385] | Splits DN into its component parts |
| 1330 |     [ldap_first_attribute][1386] | Return first attribute |
| 1331 |     [ldap_first_entry][1387] | Return first result id |
| 1332 |     [ldap_first_reference][1388] | Return first reference |
| 1333 |     [ldap_free_result][1389] | Free result memory |
| 1334 |     [ldap_get_attributes][1390] | Get attributes from a search result entry |
| 1335 |     [ldap_get_dn][1391] | Get the DN of a result entry |
| 1336 |     [ldap_get_entries][1392] | Get all result entries |
| 1337 |     [ldap_get_option][1393] | Get the current value for given option |
| 1338 |     [ldap_get_values][1394] | Get all values from a result entry |
| 1339 |     [ldap_get_values_len][1395] | Get all binary values from a result entry |
| 1340 |     [ldap_list][1396] | Single-level search |
| 1341 |     [ldap_mod_add][1397] | Add attribute values to current attributes |
| 1342 |     [ldap_mod_del][1398] | Delete attribute values from current attributes |
| 1343 |     [ldap_mod_replace][1399] | Replace attribute values with new ones |
| 1344 |     [ldap_modify][1400] | 别名          [ldap_mod_replace()][1401] |
| 1345 |     [ldap_modify_batch][1402] | Batch and execute modifications on an LDAP entry |
| 1346 |     [ldap_next_attribute][1403] | Get the next attribute in result |
| 1347 |     [ldap_next_entry][1404] | Get next result entry |
| 1348 |     [ldap_next_reference][1405] | Get next reference |
| 1349 |     [ldap_parse_reference][1406] | Extract information from reference entry |
| 1350 |     [ldap_parse_result][1407] | Extract information from result |
| 1351 |     [ldap_read][1408] | Read an entry |
| 1352 |     [ldap_rename][1409] | Modify the name of an entry |
| 1353 |     [ldap_sasl_bind][1410] | Bind to LDAP directory using SASL |
| 1354 |     [ldap_search][1411] | Search LDAP tree |
| 1355 |     [ldap_set_option][1412] | Set the value of the given option |
| 1356 |     [ldap_set_rebind_proc][1413] | Set a callback function to do re-binds on referral chasing |
| 1357 |     [ldap_sort][1414] | Sort LDAP result entries on the client side |
| 1358 |     [ldap_start_tls][1415] | Start TLS |
| 1359 |     [ldap_unbind][1416] | Unbind from LDAP directory |
| 1360 |     [levenshtein][1417] | 计算两个字符串之间的编辑距离 |
| 1361 |     [libxml_clear_errors][1418] | Clear libxml error buffer |
| 1362 |     [libxml_disable_entity_loader][1419] | Disable the ability to load external entities |
| 1363 |     [libxml_get_errors][1420] | Retrieve array of errors |
| 1364 |     [libxml_get_last_error][1421] | Retrieve last error from libxml |
| 1365 |     [libxml_set_external_entity_loader][1422] | Changes the default external entity loader |
| 1366 |     [libxml_set_streams_context][1423] | Set the streams context for the next libxml document load or write |
| 1367 |     [libxml_use_internal_errors][1424] | Disable libxml errors and allow user to fetch error information as needed |
| 1368 |     [link][1425] | 建立一个硬连接 |
| 1369 |     [linkinfo][1426] | 获取一个连接的信息 |
| 1370 |     [locale_canonicalize][1427] | Canonicalize the locale string |
| 1371 |     [locale_lookup][1428] | Searches the language tag list for the best match to the language |
| 1372 |     [localeconv][1429] | Get numeric formatting information |
| 1373 |     [localtime][1430] | 取得本地时间 |
| 1374 |     [log][1431] | 自然对数 |
| 1375 |     [log10][1432] | 以 10 为底的对数 |
| 1376 |     [log1p][1433] | 返回 log(1 + number)，甚至当 number 的值接近零也能计算出准确结果 |
| 1377 |     [long2ip][1434] | 将长整型转化为字符串形式带点的互联网标准格式地址（IPV4） |
| 1378 |     [lstat][1435] | 给出一个文件或符号连接的信息 |
| 1379 |     [ltrim][1436] | 删除字符串开头的空白字符（或其他字符） |

| m |-|-|
|-|-|-|
| 1380 |     [mail][1437] | 发送邮件 |
| 1381 |     [max][1438] | 找出最大值 |
| 1382 |     [mb_check_encoding][1439] | 检查字符串在指定的编码里是否有效 |
| 1383 |     [mb_convert_case][1440] | 对字符串进行大小写转换 |
| 1384 |     [mb_convert_encoding][1441] | 转换字符的编码 |
| 1385 |     [mb_convert_kana][1442] | Convert "kana" one from another ("zen-kaku", "han-kaku" and more) |
| 1386 |     [mb_convert_variables][1443] | 转换一个或多个变量的字符编码 |
| 1387 |     [mb_decode_mimeheader][1444] | 解码 MIME 头字段中的字符串 |
| 1388 |     [mb_decode_numericentity][1445] | 根据 HTML 数字字符串解码成字符 |
| 1389 |     [mb_detect_encoding][1446] | 检测字符的编码 |
| 1390 |     [mb_detect_order][1447] | 设置/获取 字符编码的检测顺序 |
| 1391 |     [mb_encode_mimeheader][1448] | 为 MIME 头编码字符串 |
| 1392 |     [mb_encode_numericentity][1449] | Encode character to HTML numeric string reference |
| 1393 |     [mb_encoding_aliases][1450] | Get aliases of a known encoding type |
| 1394 |     [mb_ereg][1451] | Regular expression match with multibyte support |
| 1395 |     [mb_ereg_match][1452] | Regular expression match for multibyte string |
| 1396 |     [mb_ereg_replace][1453] | Replace regular expression with multibyte support |
| 1397 |     [mb_ereg_replace_callback][1454] | Perform a regular expresssion seach and replace with multibyte support using a callback |
| 1398 |     [mb_ereg_search][1455] | Multibyte regular expression match for predefined multibyte string |
| 1399 |     [mb_ereg_search_getpos][1456] | Returns start point for next regular expression match |
| 1400 |     [mb_ereg_search_getregs][1457] | Retrieve the result from the last multibyte regular expression match |
| 1401 |     [mb_ereg_search_init][1458] | Setup string and regular expression for a multibyte regular expression match |
| 1402 |     [mb_ereg_search_pos][1459] | Returns position and length of a matched part of the multibyte regular expression for a predefined multibyte string |
| 1403 |     [mb_ereg_search_regs][1460] | Returns the matched part of a multibyte regular expression |
| 1404 |     [mb_ereg_search_setpos][1461] | Set start point of next regular expression match |
| 1405 |     [mb_eregi][1462] | Regular expression match ignoring case with multibyte support |
| 1406 |     [mb_eregi_replace][1463] | Replace regular expression with multibyte support ignoring case |
| 1407 |     [mb_get_info][1464] | 获取 mbstring 的内部设置 |
| 1408 |     [mb_http_input][1465] | 检测 HTTP 输入字符编码 |
| 1409 |     [mb_http_output][1466] | 设置/获取 HTTP 输出字符编码 |
| 1410 |     [mb_internal_encoding][1467] | 设置/获取内部字符编码 |
| 1411 |     [mb_language][1468] | 设置/获取当前的语言 |
| 1412 |     [mb_list_encodings][1469] | 返回所有支持编码的数组 |
| 1413 |     [mb_output_handler][1470] | 在输出缓冲中转换字符编码的回调函数 |
| 1414 |     [mb_parse_str][1471] | 解析 GET/POST/COOKIE 数据并设置全局变量 |
| 1415 |     [mb_preferred_mime_name][1472] | 获取 MIME 字符串 |
| 1416 |     [mb_regex_encoding][1473] | Set/Get character encoding for multibyte regex |
| 1417 |     [mb_regex_set_options][1474] | Set/Get the default options for mbregex functions |
| 1418 |     [mb_send_mail][1475] | 发送编码过的邮件 |
| 1419 |     [mb_split][1476] | 使用正则表达式分割多字节字符串 |
| 1420 |     [mb_strcut][1477] | 获取字符的一部分 |
| 1421 |     [mb_strimwidth][1478] | 获取按指定宽度截断的字符串 |
| 1422 |     [mb_stripos][1479] | 大小写不敏感地查找字符串在另一个字符串中首次出现的位置 |
| 1423 |     [mb_stristr][1480] | 大小写不敏感地查找字符串在另一个字符串里的首次出现 |
| 1424 |     [mb_strlen][1481] | 获取字符串的长度 |
| 1425 |     [mb_strpos][1482] | 查找字符串在另一个字符串中首次出现的位置 |
| 1426 |     [mb_strrchr][1483] | 查找指定字符在另一个字符串中最后一次的出现 |
| 1427 |     [mb_strrichr][1484] | 大小写不敏感地查找指定字符在另一个字符串中最后一次的出现 |
| 1428 |     [mb_strripos][1485] | 大小写不敏感地在字符串中查找一个字符串最后出现的位置 |
| 1429 |     [mb_strrpos][1486] | 查找字符串在一个字符串中最后出现的位置 |
| 1430 |     [mb_strstr][1487] | 查找字符串在另一个字符串里的首次出现 |
| 1431 |     [mb_strtolower][1488] | 使字符串小写 |
| 1432 |     [mb_strtoupper][1489] | 使字符串大写 |
| 1433 |     [mb_strwidth][1490] | 返回字符串的宽度 |
| 1434 |     [mb_substitute_character][1491] | 设置/获取替代字符 |
| 1435 |     [mb_substr][1492] | 获取部分字符串 |
| 1436 |     [mb_substr_count][1493] | 统计字符串出现的次数 |
| 1437 |     [mcrypt_cbc][1494] | 以 CBC 模式加解密数据 |
| 1438 |     [mcrypt_cfb][1495] | 以 CFB 模式加解密数据 |
| 1439 |     [mcrypt_create_iv][1496] | 从随机源创建初始向量 |
| 1440 |     [mcrypt_decrypt][1497] | 使用给定参数解密密文 |
| 1441 |     [mcrypt_ecb][1498] | 已废弃：使用 ECB 模式加解密数据 |
| 1442 |     [mcrypt_enc_get_algorithms_name][1499] | 返回打开的算法名称 |
| 1443 |     [mcrypt_enc_get_block_size][1500] | 返回打开的算法的分组大小 |
| 1444 |     [mcrypt_enc_get_iv_size][1501] | 返回打开的算法的初始向量大小 |
| 1445 |     [mcrypt_enc_get_key_size][1502] | 返回打开的模式所能支持的最长密钥 |
| 1446 |     [mcrypt_enc_get_modes_name][1503] | 返回打开的模式的名称 |
| 1447 |     [mcrypt_enc_get_supported_key_sizes][1504] | 以数组方式返回打开的算法所支持的密钥长度 |
| 1448 |     [mcrypt_enc_is_block_algorithm][1505] | 检测打开模式的算法是否为分组算法 |
| 1449 |     [mcrypt_enc_is_block_algorithm_mode][1506] | 检测打开的模式是否支持分组加密 |
| 1450 |     [mcrypt_enc_is_block_mode][1507] | 检测打开的模式是否以分组方式输出 |
| 1451 |     [mcrypt_enc_self_test][1508] | 在打开的模块上进行自检 |
| 1452 |     [mcrypt_encrypt][1509] | 使用给定参数加密明文 |
| 1453 |     [mcrypt_generic][1510] | 加密数据 |
| 1454 |     [mcrypt_generic_deinit][1511] | 对加密模块进行清理工作 |
| 1455 |     [mcrypt_generic_end][1512] | 终止加密 |
| 1456 |     [mcrypt_generic_init][1513] | 初始化加密所需的缓冲区 |
| 1457 |     [mcrypt_get_block_size][1514] | 获得加密算法的分组大小 |
| 1458 |     [mcrypt_get_cipher_name][1515] | 获取加密算法名称 |
| 1459 |     [mcrypt_get_iv_size][1516] | 返回指定算法/模式组合的初始向量大小 |
| 1460 |     [mcrypt_get_key_size][1517] | 获取指定加密算法的密钥大小 |
| 1461 |     [mcrypt_list_algorithms][1518] | 获取支持的加密算法 |
| 1462 |     [mcrypt_list_modes][1519] | 获取所支持的模式 |
| 1463 |     [mcrypt_module_close][1520] | 关闭加密模块 |
| 1464 |     [mcrypt_module_get_algo_block_size][1521] | 返回指定算法的分组大小 |
| 1465 |     [mcrypt_module_get_algo_key_size][1522] | 获取打开模式所支持的最大密钥大小 |
| 1466 |     [mcrypt_module_get_supported_key_sizes][1523] | 以数组形式返回打开的算法所支持的密钥大小 |
| 1467 |     [mcrypt_module_is_block_algorithm][1524] | 检测指定算法是否为分组加密算法 |
| 1468 |     [mcrypt_module_is_block_algorithm_mode][1525] | 返回指定模块是否是分组加密模式 |
| 1469 |     [mcrypt_module_is_block_mode][1526] | 检测指定模式是否以分组方式输出 |
| 1470 |     [mcrypt_module_open][1527] | 打开算法和模式对应的模块 |
| 1471 |     [mcrypt_module_self_test][1528] | 在指定模块上执行自检 |
| 1472 |     [mcrypt_ofb][1529] | 使用 OFB 模式加密/解密数据 |
| 1473 |     [md5][1530] | 计算字符串的 MD5 散列值 |
| 1474 |     [md5_file][1531] | 计算指定文件的 MD5 散列值 |
| 1475 |     [mdecrypt_generic][1532] | 解密数据 |
| 1476 |     [memcache_add][1533] | 增加一个条目到缓存服务器 |
| 1477 |     [memcache_close][1534] | 关闭memcache连接 |
| 1478 |     [memcache_connect][1535] | 打开一个memcached服务端连接 |
| 1479 |     [memcache_debug][1536] | 转换调试输出的开/关 |
| 1480 |     [memcache_decrement][1537] | 减小元素的值 |
| 1481 |     [memcache_delete][1538] | 从服务端删除一个元素 |
| 1482 |     [memcache_flush][1539] | 清洗（删除）已经存储的所有的元素 |
| 1483 |     [memcache_get][1540] | 从服务端检回一个元素 |
| 1484 |     [memcache_increment][1541] | 增加一个元素的值 |
| 1485 |     [memcache_pconnect][1542] | 打开一个到服务器的持久化连接 |
| 1486 |     [memcache_replace][1543] | 替换已经存在的元素的值 |
| 1487 |     [memcache_set][1544] | Store data at the server |
| 1488 |     [memory_get_peak_usage][1545] | 返回分配给 PHP 内存的峰值 |
| 1489 |     [memory_get_usage][1546] | 返回分配给 PHP 的内存量 |
| 1490 |     [messageformatter::format][1547] | Format the message |
| 1491 |     [messageformatter::geterrorcode][1548] | Get the error code from last operation |
| 1492 |     [messageformatter::geterrormessage][1549] | Get the error text from the last operation |
| 1493 |     [messageformatter::getlocale][1550] | Get the locale for which the formatter was created |
| 1494 |     [messageformatter::getpattern][1551] | Get the pattern used by the formatter |
| 1495 |     [messageformatter::parse][1552] | Parse input string according to pattern |
| 1496 |     [messageformatter::setpattern][1553] | Set the pattern used by the formatter |
| 1497 |     [metaphone][1554] | Calculate the metaphone key of a string |
| 1498 |     [method_exists][1555] | 检查类的方法是否存在 |
| 1499 |     [mhash][1556] | Computes hash |
| 1500 |     [mhash_count][1557] | Gets the highest available hash ID |
| 1501 |     [mhash_get_block_size][1558] | Gets the block size of the specified hash |
| 1502 |     [mhash_get_hash_name][1559] | Gets the name of the specified hash |
| 1503 |     [mhash_keygen_s2k][1560] | Generates a key |
| 1504 |     [microtime][1561] | 返回当前 Unix 时间戳和微秒数 |
| 1505 |     [mime_content_type][1562] | 检测文件的 MIME 类型 |
| 1506 |     [min][1563] | 找出最小值 |
| 1507 |     [ming_keypress][1564] | Returns the action flag for keyPress(char) |
| 1508 |     [ming_setcubicthreshold][1565] | Set cubic threshold |
| 1509 |     [ming_setscale][1566] | Set the global scaling factor |
| 1510 |     [ming_setswfcompression][1567] | Sets the SWF output compression |
| 1511 |     [ming_useconstants][1568] | Use constant pool |
| 1512 |     [ming_useswfversion][1569] | Sets the SWF version |
| 1513 |     [mkdir][1570] | 新建目录 |
| 1514 |     [mktime][1571] | 取得一个日期的 Unix 时间戳 |
| 1515 |     [money_format][1572] | 将数字格式化成货币字符串 |
| 1516 |     [mongocollection::__tostring][1573] | String representation of this collection |
| 1517 |     [mongocollection::aggregate][1574] | Perform an aggregation using the aggregation framework |
| 1518 |     [mongocollection::aggregatecursor][1575] | Execute an aggregation pipeline command and retrieve results through a cursor |
| 1519 |     [mongocollection::batchinsert][1576] | Inserts multiple documents into this collection |
| 1520 |     [mongocollection::count][1577] | 返回集合中的文档数量 |
| 1521 |     [mongocollection::createdbref][1578] | 创建一个数据库引用 |
| 1522 |     [mongocollection::createindex][1579] | Creates an index on the specified field(s) if it does not already exist |
| 1523 |     [mongocollection::deleteindex][1580] | Deletes an index from this collection |
| 1524 |     [mongocollection::deleteindexes][1581] | 删除集合的所有索引 |
| 1525 |     [mongocollection::distinct][1582] | 获取集合里指定键的不同值的列表。 |
| 1526 |     [mongocollection::drop][1583] | 删除该集合 |
| 1527 |     [mongocollection::ensureindex][1584] | Creates an index on the specified field(s) if it does not already exist |
| 1528 |     [mongocollection::find][1585] | 
查询该集合，并返回结果集的          [MongoCursor][1586]
 |
| 1529 |     [mongocollection::findandmodify][1587] | Update a document and return it |
| 1530 |     [mongocollection::findone][1588] | Queries this collection, returning a single element |
| 1531 |     [mongocollection::getdbref][1589] | Fetches the document pointed to by a database reference |
| 1532 |     [mongocollection::getindexinfo][1590] | Returns information about indexes on this collection |
| 1533 |     [mongocollection::getname][1591] | 返回这个集合的名称 |
| 1534 |     [mongocollection::getreadpreference][1592] | Get the read preference for this collection |
| 1535 |     [mongocollection::getslaveokay][1593] | Get slaveOkay setting for this collection |
| 1536 |     [mongocollection::group][1594] | Performs an operation similar to SQL's GROUP BY command |
| 1537 |     [mongocollection::insert][1595] | 插入文档到集合中 |
| 1538 |     [mongocollection::remove][1596] | 从集合中删除记录 |
| 1539 |     [mongocollection::save][1597] | 保存一个文档到集合 |
| 1540 |     [mongocollection::setreadpreference][1598] | Set the read preference for this collection |
| 1541 |     [mongocollection::setslaveokay][1599] | Change slaveOkay setting for this collection |
| 1542 |     [mongocollection::update][1600] | Update records based on a given criteria |
| 1543 |     [mongocollection::validate][1601] | Validates this collection |
| 1544 |     [mongodate::todatetime][1602] | Returns a DateTime object representing this date |
| 1545 |     [mongodb::__tostring][1603] | The name of this database |
| 1546 |     [mongodb::authenticate][1604] | 登录到数据库 |
| 1547 |     [mongodb::command][1605] | 执行一条 Mongo 指令 |
| 1548 |     [mongodb::createcollection][1606] | 创建一个集合 |
| 1549 |     [mongodb::createdbref][1607] | 创建数据库引用 |
| 1550 |     [mongodb::drop][1608] | 删除数据库 |
| 1551 |     [mongodb::dropcollection][1609] | Drops a collection [deprecated] |
| 1552 |     [mongodb::execute][1610] | 在数据库服务器上运行JavaScript |
| 1553 |     [mongodb::forceerror][1611] | Creates a database error |
| 1554 |     [mongodb::getcollectionnames][1612] | Gets an array of names for all collections in this database |
| 1555 |     [mongodb::getdbref][1613] | Fetches the document pointed to by a database reference |
| 1556 |     [mongodb::getgridfs][1614] | Fetches toolkit for dealing with files stored in this database |
| 1557 |     [mongodb::getprofilinglevel][1615] | Gets this database's profiling level |
| 1558 |     [mongodb::getreadpreference][1616] | Get the read preference for this database |
| 1559 |     [mongodb::getslaveokay][1617] | Get slaveOkay setting for this database |
| 1560 |     [mongodb::getwriteconcern][1618] | Get the write concern for this database |
| 1561 |     [mongodb::lasterror][1619] | Check if there was an error on the most recent db operation performed |
| 1562 |     [mongodb::listcollections][1620] | Gets an array of MongoCollection objects for all collections in this database |
| 1563 |     [mongodb::preverror][1621] | Checks for the last error thrown during a database operation |
| 1564 |     [mongodb::repair][1622] | Repairs and compacts this database |
| 1565 |     [mongodb::reseterror][1623] | Clears any flagged errors on the database |
| 1566 |     [mongodb::selectcollection][1624] | Gets a collection |
| 1567 |     [mongodb::setprofilinglevel][1625] | Sets this database's profiling level |
| 1568 |     [mongodb::setreadpreference][1626] | Set the read preference for this database |
| 1569 |     [mongodb::setslaveokay][1627] | Change slaveOkay setting for this database |
| 1570 |     [mongodb::setwriteconcern][1628] | Set the write concern for this database |
| 1571 |     [mongogridfsfile::getbytes][1629] | Returns this file's contents as a string of bytes |
| 1572 |     [mongogridfsfile::getfilename][1630] | Returns this file's filename |
| 1573 |     [mongogridfsfile::getresource][1631] | Returns a resource that can be used to read the stored file |
| 1574 |     [mongogridfsfile::getsize][1632] | Returns this file's size |
| 1575 |     [mongogridfsfile::write][1633] | Writes this file to the filesystem |
| 1576 |     [mongoid::getinc][1634] | 返回用于创建 id 所增加的值 |
| 1577 |     [mongoid::getpid][1635] | 获取进程 ID |
| 1578 |     [mongoid::gettimestamp][1636] | 获取新纪元时间到 id 创建时的秒数。 |
| 1579 |     [move_uploaded_file][1637] | 将上传的文件移动到新位置 |
| 1580 |     [msg_get_queue][1638] | Create or attach to a message queue |
| 1581 |     [msg_queue_exists][1639] | Check whether a message queue exists |
| 1582 |     [msg_receive][1640] | Receive a message from a message queue |
| 1583 |     [msg_remove_queue][1641] | Destroy a message queue |
| 1584 |     [msg_send][1642] | Send a message to a message queue |
| 1585 |     [msg_set_queue][1643] | Set information in the message queue data structure |
| 1586 |     [msg_stat_queue][1644] | Returns information from the message queue data structure |
| 1587 |     [mssql_bind][1645] | Adds a parameter to a stored procedure or a remote stored procedure |
| 1588 |     [mssql_close][1646] | 关闭MS SQL Server链接 |
| 1589 |     [mssql_connect][1647] | 打开MS SQL server链接 |
| 1590 |     [mssql_data_seek][1648] | Moves internal row pointer |
| 1591 |     [mssql_execute][1649] | Executes a stored procedure on a MS SQL server database |
| 1592 |     [mssql_fetch_array][1650] | Fetch a result row as an associative array, a numeric array, or both |
| 1593 |     [mssql_fetch_assoc][1651] | Returns an associative array of the current row in the result |
| 1594 |     [mssql_fetch_batch][1652] | Returns the next batch of records |
| 1595 |     [mssql_fetch_field][1653] | Get field information |
| 1596 |     [mssql_fetch_object][1654] | Fetch row as object |
| 1597 |     [mssql_fetch_row][1655] | Get row as enumerated array |
| 1598 |     [mssql_field_length][1656] | Get the length of a field |
| 1599 |     [mssql_field_name][1657] | Get the name of a field |
| 1600 |     [mssql_field_seek][1658] | Seeks to the specified field offset |
| 1601 |     [mssql_field_type][1659] | Gets the type of a field |
| 1602 |     [mssql_free_result][1660] | Free result memory |
| 1603 |     [mssql_free_statement][1661] | Free statement memory |
| 1604 |     [mssql_get_last_message][1662] | Returns the last message from the server |
| 1605 |     [mssql_guid_string][1663] | Converts a 16 byte binary GUID to a string |
| 1606 |     [mssql_init][1664] | Initializes a stored procedure or a remote stored procedure |
| 1607 |     [mssql_min_error_severity][1665] | Sets the minimum error severity |
| 1608 |     [mssql_min_message_severity][1666] | Sets the minimum message severity |
| 1609 |     [mssql_next_result][1667] | Move the internal result pointer to the next result |
| 1610 |     [mssql_num_fields][1668] | Gets the number of fields in result |
| 1611 |     [mssql_num_rows][1669] | Gets the number of rows in result |
| 1612 |     [mssql_pconnect][1670] | Open persistent MS SQL connection |
| 1613 |     [mssql_query][1671] | Send MS SQL query |
| 1614 |     [mssql_result][1672] | Get result data |
| 1615 |     [mssql_rows_affected][1673] | Returns the number of records affected by the query |
| 1616 |     [mssql_select_db][1674] | Select MS SQL database |
| 1617 |     [mt_getrandmax][1675] | 显示随机数的最大可能值 |
| 1618 |     [mt_rand][1676] | 生成更好的随机数 |
| 1619 |     [mt_srand][1677] | 播下一个更好的随机数发生器种子 |
| 1620 |     [mysql_affected_rows][1678] | 取得前一次 MySQL 操作所影响的记录行数 |
| 1621 |     [mysql_client_encoding][1679] | 返回字符集的名称 |
| 1622 |     [mysql_close][1680] | 关闭 MySQL 连接 |
| 1623 |     [mysql_connect][1681] | 打开一个到 MySQL 服务器的连接 |
| 1624 |     [mysql_data_seek][1682] | 移动内部结果的指针 |
| 1625 |     [mysql_db_name][1683] | 取得结果数据 |
| 1626 |     [mysql_db_query][1684] | 发送一条 MySQL 查询 |
| 1627 |     [mysql_errno][1685] | 返回上一个 MySQL 操作中的错误信息的数字编码 |
| 1628 |     [mysql_error][1686] | 返回上一个 MySQL 操作产生的文本错误信息 |
| 1629 |     [mysql_escape_string][1687] | 转义一个字符串用于 mysql_query |
| 1630 |     [mysql_fetch_array][1688] | 从结果集中取得一行作为关联数组，或数字数组，或二者兼有 |
| 1631 |     [mysql_fetch_assoc][1689] | 从结果集中取得一行作为关联数组 |
| 1632 |     [mysql_fetch_field][1690] | 从结果集中取得列信息并作为对象返回 |
| 1633 |     [mysql_fetch_lengths][1691] | 取得结果集中每个输出的长度 |
| 1634 |     [mysql_fetch_object][1692] | 从结果集中取得一行作为对象 |
| 1635 |     [mysql_fetch_row][1693] | 从结果集中取得一行作为枚举数组 |
| 1636 |     [mysql_field_flags][1694] | 从结果中取得和指定字段关联的标志 |
| 1637 |     [mysql_field_len][1695] | 返回指定字段的长度 |
| 1638 |     [mysql_field_name][1696] | 取得结果中指定字段的字段名 |
| 1639 |     [mysql_field_seek][1697] | 将结果集中的指针设定为制定的字段偏移量 |
| 1640 |     [mysql_field_table][1698] | 取得指定字段所在的表名 |
| 1641 |     [mysql_field_type][1699] | 取得结果集中指定字段的类型 |
| 1642 |     [mysql_free_result][1700] | 释放结果内存 |
| 1643 |     [mysql_get_client_info][1701] | 取得 MySQL 客户端信息 |
| 1644 |     [mysql_get_host_info][1702] | 取得 MySQL 主机信息 |
| 1645 |     [mysql_get_proto_info][1703] | 取得 MySQL 协议信息 |
| 1646 |     [mysql_get_server_info][1704] | 取得 MySQL 服务器信息 |
| 1647 |     [mysql_info][1705] | 取得最近一条查询的信息 |
| 1648 |     [mysql_insert_id][1706] | 取得上一步 INSERT 操作产生的 ID |
| 1649 |     [mysql_list_dbs][1707] | 列出 MySQL 服务器中所有的数据库 |
| 1650 |     [mysql_list_fields][1708] | 列出 MySQL 结果中的字段 |
| 1651 |     [mysql_list_processes][1709] | 列出 MySQL 进程 |
| 1652 |     [mysql_list_tables][1710] | 列出 MySQL 数据库中的表 |
| 1653 |     [mysql_num_fields][1711] | 取得结果集中字段的数目 |
| 1654 |     [mysql_num_rows][1712] | 取得结果集中行的数目 |
| 1655 |     [mysql_pconnect][1713] | 打开一个到 MySQL 服务器的持久连接 |
| 1656 |     [mysql_ping][1714] | Ping 一个服务器连接，如果没有连接则重新连接 |
| 1657 |     [mysql_query][1715] | 发送一条 MySQL 查询 |
| 1658 |     [mysql_real_escape_string][1716] | 转义 SQL 语句中使用的字符串中的特殊字符，并考虑到连接的当前字符集 |
| 1659 |     [mysql_result][1717] | 取得结果数据 |
| 1660 |     [mysql_select_db][1718] | 选择 MySQL 数据库 |
| 1661 |     [mysql_set_charset][1719] | 设置客户端的字符集 |
| 1662 |     [mysql_stat][1720] | 取得当前系统状态 |
| 1663 |     [mysql_tablename][1721] | 取得表名 |
| 1664 |     [mysql_thread_id][1722] | 返回当前线程的 ID |
| 1665 |     [mysql_unbuffered_query][1723] | 向 MySQL 发送一条 SQL 查询，并不获取和缓存结果的行 |
| 1666 |     [mysqli::begin_transaction][1724] | Starts a transaction |
| 1667 |     [mysqli::release_savepoint][1725] | Removes the named savepoint from the set of savepoints of the current transaction |
| 1668 |     [mysqli::savepoint][1726] | Set a named transaction savepoint |
| 1669 |     [mysqli_autocommit][1727] | 打开或关闭本次数据库连接的自动命令提交事务模式 |
| 1670 |     [mysqli_bind_param][1728] |             [mysqli_stmt_bind_param()][1729]的别名 |
| 1671 |     [mysqli_bind_result][1730] |             [mysqli_stmt_bind_result()][1731]的别名 |
| 1672 |     [mysqli_client_encoding][1732] |             [mysqli_character_set_name()][1733]的别名 |
| 1673 |     [mysqli_close][1734] | 关闭先前打开的数据库连接 |
| 1674 |     [mysqli_commit][1735] | 提交一个事务 |
| 1675 |     [mysqli_connect][1736] | 别名          [mysqli::__construct()][1737] |
| 1676 |     [mysqli_debug][1738] | Performs debugging operations |
| 1677 |     [mysqli_errno][1739] | 返回最近函数调用的错误代码 |
| 1678 |     [mysqli_error][1740] | Returns a string description of the last error |
| 1679 |     [mysqli_escape_string][1741] | 别名          [mysqli_real_escape_string()][1742] |
| 1680 |     [mysqli_execute][1743] |             [mysqli_stmt_execute()][1744]的别名 |
| 1681 |     [mysqli_fetch][1745] |             [mysqli_stmt_fetch()][1746]的别名。 |
| 1682 |     [mysqli_get_metadata][1747] |             [mysqli_stmt_result_metadata()][1748]的别名 |
| 1683 |     [mysqli_info][1749] | Retrieves information about the most recently executed query |
| 1684 |     [mysqli_init][1750] | Initializes MySQLi and returns a resource for use with mysqli_real_connect() |
| 1685 |     [mysqli_kill][1751] | Asks the server to kill a MySQL thread |
| 1686 |     [mysqli_options][1752] | Set options |
| 1687 |     [mysqli_param_count][1753] |             [mysqli_stmt_param_count()][1754]的别名 |
| 1688 |     [mysqli_ping][1755] | Pings a server connection, or tries to reconnect if the connection has gone down |
| 1689 |     [mysqli_poll][1756] | Poll connections |
| 1690 |     [mysqli_prepare][1757] | Prepare an SQL statement for execution |
| 1691 |     [mysqli_query][1758] | 对数据库执行一次查询 |
| 1692 |     [mysqli_refresh][1759] | Refreshes |
| 1693 |     [mysqli_report][1760] | 开启或禁用（Mysql）内部（错误）报告函数 |
| 1694 |     [mysqli_rollback][1761] | 回退当前事务 |
| 1695 |     [mysqli_savepoint][1762] | Set a named transaction savepoint |
| 1696 |     [mysqli_send_long_data][1763] |             [mysqli_stmt_send_long_data()][1764]的别名 |
| 1697 |     [mysqli_set_opt][1765] |             [mysqli_options()][1766]的别名 |
| 1698 |     [mysqli_sqlstate][1767] | Returns the SQLSTATE error from previous MySQL operation |
| 1699 |     [mysqli_stat][1768] | Gets the current system status |
| 1700 |     [mysqli_stmt_close][1769] | Closes a prepared statement |
| 1701 |     [mysqli_stmt_errno][1770] | Returns the error code for the most recent statement call |
| 1702 |     [mysqli_stmt_error][1771] | Returns a string description for last statement error |
| 1703 |     [mysqli_stmt_execute][1772] | Executes a prepared Query |
| 1704 |     [mysqli_stmt_fetch][1773] | Fetch results from a prepared statement into the bound variables |
| 1705 |     [mysqli_stmt_prepare][1774] | Prepare an SQL statement for execution |
| 1706 |     [mysqli_stmt_reset][1775] | Resets a prepared statement |
| 1707 |     [mysqli_stmt_sqlstate][1776] | Returns SQLSTATE error from previous statement operation |

| n |-|-|
|-|-|-|
| 1708 |     [natsort][1777] | 用“自然排序”算法对数组排序 |
| 1709 |     [ncurses_addch][1778] | Add character at current position and advance cursor |
| 1710 |     [ncurses_addchnstr][1779] | Add attributed string with specified length at current position |
| 1711 |     [ncurses_addchstr][1780] | Add attributed string at current position |
| 1712 |     [ncurses_addnstr][1781] | Add string with specified length at current position |
| 1713 |     [ncurses_addstr][1782] | Output text at current position |
| 1714 |     [ncurses_assume_default_colors][1783] | Define default colors for color 0 |
| 1715 |     [ncurses_attroff][1784] | Turn off the given attributes |
| 1716 |     [ncurses_attron][1785] | Turn on the given attributes |
| 1717 |     [ncurses_attrset][1786] | Set given attributes |
| 1718 |     [ncurses_baudrate][1787] | Returns baudrate of terminal |
| 1719 |     [ncurses_beep][1788] | Let the terminal beep |
| 1720 |     [ncurses_bkgd][1789] | Set background property for terminal screen |
| 1721 |     [ncurses_bkgdset][1790] | Control screen background |
| 1722 |     [ncurses_border][1791] | Draw a border around the screen using attributed characters |
| 1723 |     [ncurses_bottom_panel][1792] | Moves a visible panel to the bottom of the stack |
| 1724 |     [ncurses_can_change_color][1793] | Checks if terminal color definitions can be changed |
| 1725 |     [ncurses_cbreak][1794] | Switch off input buffering |
| 1726 |     [ncurses_clear][1795] | Clear screen |
| 1727 |     [ncurses_clrtobot][1796] | Clear screen from current position to bottom |
| 1728 |     [ncurses_clrtoeol][1797] | Clear screen from current position to end of line |
| 1729 |     [ncurses_color_content][1798] | Retrieves RGB components of a color |
| 1730 |     [ncurses_color_set][1799] | Set active foreground and background colors |
| 1731 |     [ncurses_curs_set][1800] | Set cursor state |
| 1732 |     [ncurses_def_prog_mode][1801] | Saves terminals (program) mode |
| 1733 |     [ncurses_def_shell_mode][1802] | Saves terminals (shell) mode |
| 1734 |     [ncurses_define_key][1803] | Define a keycode |
| 1735 |     [ncurses_del_panel][1804] | Remove panel from the stack and delete it (but not the associated window) |
| 1736 |     [ncurses_delay_output][1805] | Delay output on terminal using padding characters |
| 1737 |     [ncurses_delch][1806] | Delete character at current position, move rest of line left |
| 1738 |     [ncurses_deleteln][1807] | Delete line at current position, move rest of screen up |
| 1739 |     [ncurses_delwin][1808] | Delete a ncurses window |
| 1740 |     [ncurses_doupdate][1809] | Write all prepared refreshes to terminal |
| 1741 |     [ncurses_echo][1810] | Activate keyboard input echo |
| 1742 |     [ncurses_echochar][1811] | Single character output including refresh |
| 1743 |     [ncurses_end][1812] | Stop using ncurses, clean up the screen |
| 1744 |     [ncurses_erase][1813] | Erase terminal screen |
| 1745 |     [ncurses_erasechar][1814] | Returns current erase character |
| 1746 |     [ncurses_filter][1815] | Set LINES for iniscr() and newterm() to 1 |
| 1747 |     [ncurses_flash][1816] | Flash terminal screen (visual bell) |
| 1748 |     [ncurses_flushinp][1817] | Flush keyboard input buffer |
| 1749 |     [ncurses_getch][1818] | Read a character from keyboard |
| 1750 |     [ncurses_getmaxyx][1819] | Returns the size of a window |
| 1751 |     [ncurses_getmouse][1820] | Reads mouse event |
| 1752 |     [ncurses_getyx][1821] | Returns the current cursor position for a window |
| 1753 |     [ncurses_halfdelay][1822] | Put terminal into halfdelay mode |
| 1754 |     [ncurses_has_colors][1823] | Checks if terminal has color capabilities |
| 1755 |     [ncurses_has_ic][1824] | Check for insert- and delete-capabilities |
| 1756 |     [ncurses_has_il][1825] | Check for line insert- and delete-capabilities |
| 1757 |     [ncurses_has_key][1826] | Check for presence of a function key on terminal keyboard |
| 1758 |     [ncurses_hide_panel][1827] | Remove panel from the stack, making it invisible |
| 1759 |     [ncurses_hline][1828] | Draw a horizontal line at current position using an attributed character and max. n characters long |
| 1760 |     [ncurses_inch][1829] | Get character and attribute at current position |
| 1761 |     [ncurses_init][1830] | Initialize ncurses |
| 1762 |     [ncurses_init_color][1831] | Define a terminal color |
| 1763 |     [ncurses_init_pair][1832] | Define a color pair |
| 1764 |     [ncurses_insch][1833] | Insert character moving rest of line including character at current position |
| 1765 |     [ncurses_insdelln][1834] | Insert lines before current line scrolling down (negative numbers delete and scroll up) |
| 1766 |     [ncurses_insertln][1835] | Insert a line, move rest of screen down |
| 1767 |     [ncurses_insstr][1836] | Insert string at current position, moving rest of line right |
| 1768 |     [ncurses_instr][1837] | Reads string from terminal screen |
| 1769 |     [ncurses_isendwin][1838] | Ncurses is in endwin mode, normal screen output may be performed |
| 1770 |     [ncurses_keyok][1839] | Enable or disable a keycode |
| 1771 |     [ncurses_keypad][1840] | Turns keypad on or off |
| 1772 |     [ncurses_killchar][1841] | Returns current line kill character |
| 1773 |     [ncurses_longname][1842] | Returns terminals description |
| 1774 |     [ncurses_meta][1843] | Enables/Disable 8-bit meta key information |
| 1775 |     [ncurses_mouse_trafo][1844] | Transforms coordinates |
| 1776 |     [ncurses_mouseinterval][1845] | Set timeout for mouse button clicks |
| 1777 |     [ncurses_mousemask][1846] | Sets mouse options |
| 1778 |     [ncurses_move][1847] | Move output position |
| 1779 |     [ncurses_move_panel][1848] | Moves a panel so that its upper-left corner is at [startx, starty] |
| 1780 |     [ncurses_mvaddch][1849] | Move current position and add character |
| 1781 |     [ncurses_mvaddchnstr][1850] | Move position and add attributed string with specified length |
| 1782 |     [ncurses_mvaddchstr][1851] | Move position and add attributed string |
| 1783 |     [ncurses_mvaddnstr][1852] | Move position and add string with specified length |
| 1784 |     [ncurses_mvaddstr][1853] | Move position and add string |
| 1785 |     [ncurses_mvcur][1854] | Move cursor immediately |
| 1786 |     [ncurses_mvdelch][1855] | Move position and delete character, shift rest of line left |
| 1787 |     [ncurses_mvgetch][1856] | Move position and get character at new position |
| 1788 |     [ncurses_mvhline][1857] | Set new position and draw a horizontal line using an attributed character and max. n characters long |
| 1789 |     [ncurses_mvinch][1858] | Move position and get attributed character at new position |
| 1790 |     [ncurses_mvwaddstr][1859] | Add string at new position in window |
| 1791 |     [ncurses_napms][1860] | Sleep |
| 1792 |     [ncurses_new_panel][1861] | Create a new panel and associate it with window |
| 1793 |     [ncurses_newpad][1862] | Creates a new pad (window) |
| 1794 |     [ncurses_newwin][1863] | Create a new window |
| 1795 |     [ncurses_nl][1864] | Translate newline and carriage return / line feed |
| 1796 |     [ncurses_nocbreak][1865] | Switch terminal to cooked mode |
| 1797 |     [ncurses_noecho][1866] | Switch off keyboard input echo |
| 1798 |     [ncurses_nonl][1867] | Do not translate newline and carriage return / line feed |
| 1799 |     [ncurses_noqiflush][1868] | Do not flush on signal characters |
| 1800 |     [ncurses_noraw][1869] | Switch terminal out of raw mode |
| 1801 |     [ncurses_pair_content][1870] | Retrieves foreground and background colors of a color pair |
| 1802 |     [ncurses_panel_above][1871] | Returns the panel above panel |
| 1803 |     [ncurses_panel_below][1872] | Returns the panel below panel |
| 1804 |     [ncurses_panel_window][1873] | Returns the window associated with panel |
| 1805 |     [ncurses_pnoutrefresh][1874] | Copies a region from a pad into the virtual screen |
| 1806 |     [ncurses_prefresh][1875] | Copies a region from a pad into the virtual screen |
| 1807 |     [ncurses_putp][1876] | Apply padding information to the string and output it |
| 1808 |     [ncurses_qiflush][1877] | Flush on signal characters |
| 1809 |     [ncurses_raw][1878] | Switch terminal into raw mode |
| 1810 |     [ncurses_refresh][1879] | Refresh screen |
| 1811 |     [ncurses_replace_panel][1880] | Replaces the window associated with panel |
| 1812 |     [ncurses_reset_prog_mode][1881] | Resets the prog mode saved by def_prog_mode |
| 1813 |     [ncurses_reset_shell_mode][1882] | Resets the shell mode saved by def_shell_mode |
| 1814 |     [ncurses_resetty][1883] | Restores saved terminal state |
| 1815 |     [ncurses_savetty][1884] | Saves terminal state |
| 1816 |     [ncurses_scr_dump][1885] | Dump screen content to file |
| 1817 |     [ncurses_scr_init][1886] | Initialize screen from file dump |
| 1818 |     [ncurses_scr_restore][1887] | Restore screen from file dump |
| 1819 |     [ncurses_scr_set][1888] | Inherit screen from file dump |
| 1820 |     [ncurses_scrl][1889] | Scroll window content up or down without changing current position |
| 1821 |     [ncurses_show_panel][1890] | Places an invisible panel on top of the stack, making it visible |
| 1822 |     [ncurses_slk_attr][1891] | Returns current soft label key attribute |
| 1823 |     [ncurses_slk_attroff][1892] | Turn off the given attributes for soft function-key labels |
| 1824 |     [ncurses_slk_attron][1893] | Turn on the given attributes for soft function-key labels |
| 1825 |     [ncurses_slk_attrset][1894] | Set given attributes for soft function-key labels |
| 1826 |     [ncurses_slk_clear][1895] | Clears soft labels from screen |
| 1827 |     [ncurses_slk_color][1896] | Sets color for soft label keys |
| 1828 |     [ncurses_slk_init][1897] | Initializes soft label key functions |
| 1829 |     [ncurses_slk_noutrefresh][1898] | Copies soft label keys to virtual screen |
| 1830 |     [ncurses_slk_refresh][1899] | Copies soft label keys to screen |
| 1831 |     [ncurses_slk_restore][1900] | Restores soft label keys |
| 1832 |     [ncurses_slk_set][1901] | Sets function key labels |
| 1833 |     [ncurses_slk_touch][1902] | Forces output when ncurses_slk_noutrefresh is performed |
| 1834 |     [ncurses_standend][1903] | Stop using 'standout' attribute |
| 1835 |     [ncurses_standout][1904] | Start using 'standout' attribute |
| 1836 |     [ncurses_start_color][1905] | Initializes color functionality |
| 1837 |     [ncurses_termattrs][1906] | Returns a logical OR of all attribute flags supported by terminal |
| 1838 |     [ncurses_termname][1907] | Returns terminals (short)-name |
| 1839 |     [ncurses_timeout][1908] | Set timeout for special key sequences |
| 1840 |     [ncurses_top_panel][1909] | Moves a visible panel to the top of the stack |
| 1841 |     [ncurses_typeahead][1910] | Specify different filedescriptor for typeahead checking |
| 1842 |     [ncurses_ungetch][1911] | Put a character back into the input stream |
| 1843 |     [ncurses_ungetmouse][1912] | Pushes mouse event to queue |
| 1844 |     [ncurses_update_panels][1913] | Refreshes the virtual screen to reflect the relations between panels in the stack |
| 1845 |     [ncurses_use_default_colors][1914] | Assign terminal default colors to color id -1 |
| 1846 |     [ncurses_use_env][1915] | Control use of environment information about terminal size |
| 1847 |     [ncurses_use_extended_names][1916] | Control use of extended names in terminfo descriptions |
| 1848 |     [ncurses_vidattr][1917] | Display the string on the terminal in the video attribute mode |
| 1849 |     [ncurses_vline][1918] | Draw a vertical line at current position using an attributed character and max. n characters long |
| 1850 |     [ncurses_waddch][1919] | Adds character at current position in a window and advance cursor |
| 1851 |     [ncurses_waddstr][1920] | Outputs text at current postion in window |
| 1852 |     [ncurses_wattroff][1921] | Turns off attributes for a window |
| 1853 |     [ncurses_wattron][1922] | Turns on attributes for a window |
| 1854 |     [ncurses_wattrset][1923] | Set the attributes for a window |
| 1855 |     [ncurses_wborder][1924] | Draws a border around the window using attributed characters |
| 1856 |     [ncurses_wclear][1925] | Clears window |
| 1857 |     [ncurses_wcolor_set][1926] | Sets windows color pairings |
| 1858 |     [ncurses_werase][1927] | Erase window contents |
| 1859 |     [ncurses_wgetch][1928] | Reads a character from keyboard (window) |
| 1860 |     [ncurses_whline][1929] | Draws a horizontal line in a window at current position using an attributed character and max. n characters long |
| 1861 |     [ncurses_wmouse_trafo][1930] | Transforms window/stdscr coordinates |
| 1862 |     [ncurses_wmove][1931] | Moves windows output position |
| 1863 |     [ncurses_wnoutrefresh][1932] | Copies window to virtual screen |
| 1864 |     [ncurses_wrefresh][1933] | Refresh window on terminal screen |
| 1865 |     [ncurses_wstandend][1934] | End standout mode for a window |
| 1866 |     [ncurses_wstandout][1935] | Enter standout mode for a window |
| 1867 |     [ncurses_wvline][1936] | Draws a vertical line in a window at current position using an attributed character and max. n characters long |
| 1868 |     [next][1937] | 将数组中的内部指针向前移动一位 |
| 1869 |     [ngettext][1938] | Plural version of gettext |
| 1870 |     [nl2br][1939] | 在字符串所有新行之前插入 HTML 换行标记 |
| 1871 |     [nl_langinfo][1940] | Query language and locale information |
| 1872 |     [normalizer_normalize][1941] | Normalizes the input provided and returns the normalized string |
| 1873 |     [number_format][1942] | 以千位分隔符方式格式化一个数字 |
| 1874 |     [numberformatter::format][1943] | Format a number |
| 1875 |     [numberformatter::formatcurrency][1944] | Format a currency value |
| 1876 |     [numberformatter::getattribute][1945] | Get an attribute |
| 1877 |     [numberformatter::geterrorcode][1946] | Get formatter's last error code |
| 1878 |     [numberformatter::geterrormessage][1947] | Get formatter's last error message |
| 1879 |     [numberformatter::getlocale][1948] | Get formatter locale |
| 1880 |     [numberformatter::getpattern][1949] | Get formatter pattern |
| 1881 |     [numberformatter::getsymbol][1950] | Get a symbol value |
| 1882 |     [numberformatter::gettextattribute][1951] | Get a text attribute |
| 1883 |     [numberformatter::parse][1952] | Parse a number |
| 1884 |     [numberformatter::parsecurrency][1953] | Parse a currency number |
| 1885 |     [numberformatter::setattribute][1954] | Set an attribute |
| 1886 |     [numberformatter::setpattern][1955] | Set formatter pattern |
| 1887 |     [numberformatter::setsymbol][1956] | Set a symbol value |
| 1888 |     [numberformatter::settextattribute][1957] | Set a text attribute |

| o |-|-|
|-|-|-|
| 1889 |     [oauth::disableredirects][1958] | 关闭重定向 |
| 1890 |     [oauth::disablesslchecks][1959] | 关闭 SSL 检查 |
| 1891 |     [oauth::enabledebug][1960] | 启用详细调试 |
| 1892 |     [oauth::enableredirects][1961] | 启用重定向 |
| 1893 |     [oauth::enablesslchecks][1962] | 启用 SSL 检查 |
| 1894 |     [oauth::fetch][1963] | 获取一个 OAuth 受保护的资源 |
| 1895 |     [oauth::getaccesstoken][1964] | 获取一个访问令牌 |
| 1896 |     [oauth::getcapath][1965] | 获取 CA 信息 |
| 1897 |     [oauth::getlastresponse][1966] | 获取最后一次的响应 |
| 1898 |     [oauth::getlastresponseheaders][1967] | 获取最后一次响应的头信息 |
| 1899 |     [oauth::getlastresponseinfo][1968] | 获取关于最后一次响应的 HTTP 信息 |
| 1900 |     [oauth::getrequestheader][1969] | 生成 OAuth 头信息字符串签名 |
| 1901 |     [oauth::getrequesttoken][1970] | 获取一个请求令牌 |
| 1902 |     [oauth::setauthtype][1971] | 设置授权类型 |
| 1903 |     [oauth::setcapath][1972] | 设置 CA 路径和信息 |
| 1904 |     [oauth::setnonce][1973] | 为后续请求设置现时标志 |
| 1905 |     [oauth::setrequestengine][1974] | 设置目标请求引擎 |
| 1906 |     [oauth::setrsacertificate][1975] | 设置 RSA 证书 |
| 1907 |     [oauth::settimestamp][1976] | 设置时间戳 |
| 1908 |     [oauth::settoken][1977] | 设置令牌和 secret |
| 1909 |     [oauth::setversion][1978] | 设置 OAuth 版本 |
| 1910 |     [oauth_get_sbs][1979] | 生成一个签名字符基串 |
| 1911 |     [oauth_urlencode][1980] | 将 URI 编码为 RFC 3986 规范 |
| 1912 |     [oauthprovider::callconsumerhandler][1981] | 调用 consumerNonceHandler 回调函数 |
| 1913 |     [oauthprovider::calltimestampnoncehandler][1982] | 调用 timestampNonceHandler 回调函数 |
| 1914 |     [oauthprovider::calltokenhandler][1983] | 调用 tokenNonceHandler 回调函数 |
| 1915 |     [oauthprovider::checkoauthrequest][1984] | 检查一个 oauth 请求 |
| 1916 |     [oauthprovider::consumerhandler][1985] | 设置 consumerHandler 句柄回调函数 |
| 1917 |     [oauthprovider::is2leggedendpoint][1986] | is2LeggedEndpoint |
| 1918 |     [oauthprovider::isrequesttokenendpoint][1987] | 设置 isRequestTokenEndpoint |
| 1919 |     [oauthprovider::timestampnoncehandler][1988] | 设置 timestampNonceHandler 句柄回调函数 |
| 1920 |     [oauthprovider::tokenhandler][1989] | 设置 tokenHandler 句柄回调函数 |
| 1921 |     [ob_clean][1990] | 清空（擦掉）输出缓冲区 |
| 1922 |     [ob_end_clean][1991] | 清空（擦除）缓冲区并关闭输出缓冲 |
| 1923 |     [ob_end_flush][1992] | 冲刷出（送出）输出缓冲区内容并关闭缓冲 |
| 1924 |     [ob_flush][1993] | 冲刷出（送出）输出缓冲区中的内容 |
| 1925 |     [ob_get_clean][1994] | 得到当前缓冲区的内容并删除当前输出缓。 |
| 1926 |     [ob_get_contents][1995] | 返回输出缓冲区的内容 |
| 1927 |     [ob_get_flush][1996] | 刷出（送出）缓冲区内容，以字符串形式返回内容，并关闭输出缓冲区。 |
| 1928 |     [ob_get_length][1997] | 返回输出缓冲区内容的长度 |
| 1929 |     [ob_get_level][1998] | 返回输出缓冲机制的嵌套级别 |
| 1930 |     [ob_get_status][1999] | 得到所有输出缓冲区的状态 |
| 1931 |     [ob_gzhandler][2000] | 在ob_start中使用的用来压缩输出缓冲区中内容的回调函数。ob_start callback function to gzip output buffer |
| 1932 |     [ob_iconv_handler][2001] | 以输出缓冲处理程序转换字符编码 |
| 1933 |     [ob_implicit_flush][2002] | 打开/关闭绝对刷送 |
| 1934 |     [ob_list_handlers][2003] | 列出所有使用中的输出处理程序。 |
| 1935 |     [ob_start][2004] | 打开输出控制缓冲 |
| 1936 |     [ob_tidyhandler][2005] | ob_start callback function to repair the buffer |
| 1937 |     [oci_bind_array_by_name][2006] | Binds a PHP array to an Oracle PL/SQL array parameter |
| 1938 |     [oci_bind_by_name][2007] | 绑定一个 PHP 变量到一个 Oracle 位置标志符 |
| 1939 |     [oci_cancel][2008] | 中断游标读取数据 |
| 1940 |     [oci_client_version][2009] | Returns the Oracle client library version |
| 1941 |     [oci_close][2010] | 关闭 Oracle 连接 |
| 1942 |     [oci_commit][2011] | 提交未执行的事务处理 |
| 1943 |     [oci_connect][2012] | 建立一个到 Oracle 服务器的连接 |
| 1944 |     [oci_define_by_name][2013] | 在 SELECT 中使用 PHP 变量作为定义的步骤 |
| 1945 |     [oci_error][2014] | 返回上一个错误 |
| 1946 |     [oci_execute][2015] | 执行一条语句 |
| 1947 |     [oci_fetch][2016] | Fetches the next row into result-buffer |
| 1948 |     [oci_fetch_all][2017] | 获取结果数据的所有行到一个数组 |
| 1949 |     [oci_fetch_array][2018] | Returns the next row from a query as an associative or numeric array |
| 1950 |     [oci_fetch_assoc][2019] | Returns the next row from a query as an associative array |
| 1951 |     [oci_fetch_object][2020] | Returns the next row from a query as an object |
| 1952 |     [oci_fetch_row][2021] | Returns the next row from a query as a numeric array |
| 1953 |     [oci_field_is_null][2022] | 检查字段是否为  **`NULL`**  |
| 1954 |     [oci_field_name][2023] | 返回字段名 |
| 1955 |     [oci_field_precision][2024] | 返回字段精度 |
| 1956 |     [oci_field_scale][2025] | 返回字段范围 |
| 1957 |     [oci_field_size][2026] | 返回字段大小 |
| 1958 |     [oci_field_type][2027] | 返回字段的数据类型 |
| 1959 |     [oci_field_type_raw][2028] | 返回字段的原始 Oracle 数据类型 |
| 1960 |     [oci_free_descriptor][2029] | Frees a descriptor |
| 1961 |     [oci_free_statement][2030] | 释放关联于语句或游标的所有资源 |
| 1962 |     [oci_get_implicit_resultset][2031] | Returns the next child statement resource from a parent statement resource that has Oracle Database 12c Implicit Result Sets |
| 1963 |     [oci_internal_debug][2032] | 打开或关闭内部调试输出 |
| 1964 |     [oci_lob_copy][2033] | Copies large object |
| 1965 |     [oci_lob_is_equal][2034] | Compares two LOB/FILE locators for equality |
| 1966 |     [oci_new_collection][2035] | 分配新的 collection 对象 |
| 1967 |     [oci_new_connect][2036] | 建定一个到 Oracle 服务器的新连接 |
| 1968 |     [oci_new_cursor][2037] | 分配并返回一个新的游标（语句句柄） |
| 1969 |     [oci_new_descriptor][2038] | 初始化一个新的空 LOB 或 FILE 描述符 |
| 1970 |     [oci_num_fields][2039] | 返回结果列的数目 |
| 1971 |     [oci_num_rows][2040] | 返回语句执行后受影响的行数 |
| 1972 |     [oci_parse][2041] | 配置 Oracle 语句预备执行 |
| 1973 |     [oci_password_change][2042] | 修改 Oracle 用户的密码 |
| 1974 |     [oci_pconnect][2043] | 使用一个持久连接连到 Oracle 数据库 |
| 1975 |     [oci_result][2044] | 返回所取得行中字段的值 |
| 1976 |     [oci_rollback][2045] | 回滚未提交的事务 |
| 1977 |     [oci_server_version][2046] | 返回服务器版本信息 |
| 1978 |     [oci_set_action][2047] | Sets the action name |
| 1979 |     [oci_set_client_identifier][2048] | Sets the client identifier |
| 1980 |     [oci_set_client_info][2049] | Sets the client information |
| 1981 |     [oci_set_edition][2050] | Sets the database edition |
| 1982 |     [oci_set_module_name][2051] | Sets the module name |
| 1983 |     [oci_set_prefetch][2052] | 设置预提取行数 |
| 1984 |     [oci_statement_type][2053] | 返回 OCI 语句的类型 |
| 1985 |     [ocibindbyname][2054] | 别名          [oci_bind_by_name()][2055] |
| 1986 |     [ocicancel][2056] | 别名          [oci_cancel()][2057] |
| 1987 |     [ocicloselob][2058] | 别名          [OCI-Lob::close()][2059] |
| 1988 |     [ocicollappend][2060] | 别名          [OCI-Collection::append()][2061] |
| 1989 |     [ocicollassign][2062] | 别名          [OCI-Collection::assign()][2063] |
| 1990 |     [ocicollassignelem][2064] | 别名          [OCI-Collection::assignElem()][2065] |
| 1991 |     [ocicollgetelem][2066] | 别名          [OCI-Collection::getElem()][2067] |
| 1992 |     [ocicollmax][2068] | 别名          [OCI-Collection::max()][2069] |
| 1993 |     [ocicollsize][2070] | 别名          [OCI-Collection::size()][2071] |
| 1994 |     [ocicolltrim][2072] | 别名          [OCI-Collection::trim()][2073] |
| 1995 |     [ocicolumnisnull][2074] | 别名          [oci_field_is_null()][2075] |
| 1996 |     [ocicolumnname][2076] | 别名          [oci_field_name()][2077] |
| 1997 |     [ocicolumnprecision][2078] | 别名          [oci_field_precision()][2079] |
| 1998 |     [ocicolumnscale][2080] | 别名          [oci_field_scale()][2081] |
| 1999 |     [ocicolumnsize][2082] | 别名          [oci_field_size()][2083] |
| 2000 |     [ocicolumntype][2084] | 别名          [oci_field_type()][2085] |
| 2001 |     [ocicolumntyperaw][2086] | 别名          [oci_field_type_raw()][2087] |
| 2002 |     [ocicommit][2088] | 别名          [oci_commit()][2089] |
| 2003 |     [ocidefinebyname][2090] | 别名          [oci_define_by_name()][2091] |
| 2004 |     [ocierror][2092] | 别名          [oci_error()][2093] |
| 2005 |     [ociexecute][2094] | 别名          [oci_execute()][2095] |
| 2006 |     [ocifetch][2096] | 别名          [oci_fetch()][2097] |
| 2007 |     [ocifetchinto][2098] | Obsolete variant of   [oci_fetch_array()][2099],  [oci_fetch_object()][2100],  [oci_fetch_assoc()][2101]and     [oci_fetch_row()][2102] |
| 2008 |     [ocifetchstatement][2103] | 别名          [oci_fetch_all()][2104] |
| 2009 |     [ocifreecollection][2105] | 别名          [OCI-Collection::free()][2106] |
| 2010 |     [ocifreecursor][2107] | 别名          [oci_free_statement()][2108] |
| 2011 |     [ocifreedesc][2109] | 别名          [OCI-Lob::free()][2110] |
| 2012 |     [ocifreestatement][2111] | 别名          [oci_free_statement()][2108] |
| 2013 |     [ociinternaldebug][2113] | 别名          [oci_internal_debug()][2114] |
| 2014 |     [ociloadlob][2115] | 别名          [OCI-Lob::load()][2116] |
| 2015 |     [ocilogoff][2117] | 别名          [oci_close()][2118] |
| 2016 |     [ocilogon][2119] | 别名          [oci_connect()][2120] |
| 2017 |     [ocinewcollection][2121] | 别名          [oci_new_collection()][2122] |
| 2018 |     [ocinewcursor][2123] | 别名          [oci_new_cursor()][2124] |
| 2019 |     [ocinewdescriptor][2125] | 别名          [oci_new_descriptor()][2126] |
| 2020 |     [ocinlogon][2127] | 别名          [oci_new_connect()][2128] |
| 2021 |     [ocinumcols][2129] | 别名          [oci_num_fields()][2130] |
| 2022 |     [ociparse][2131] | 别名          [oci_parse()][2132] |
| 2023 |     [ociplogon][2133] | 别名          [oci_pconnect()][2134] |
| 2024 |     [ociresult][2135] | 别名          [oci_result()][2136] |
| 2025 |     [ocirollback][2137] | 别名          [oci_rollback()][2138] |
| 2026 |     [ocirowcount][2139] | 别名          [oci_num_rows()][2140] |
| 2027 |     [ocisavelob][2141] | 别名          [OCI-Lob::save()][2142] |
| 2028 |     [ocisavelobfile][2143] | 别名          [OCI-Lob::import()][2144] |
| 2029 |     [ociserverversion][2145] | 别名          [oci_server_version()][2146] |
| 2030 |     [ocisetprefetch][2147] | 别名          [oci_set_prefetch()][2148] |
| 2031 |     [ocistatementtype][2149] | 别名          [oci_statement_type()][2150] |
| 2032 |     [ociwritelobtofile][2151] | 别名          [OCI-Lob::export()][2152] |
| 2033 |     [ociwritetemporarylob][2153] | 别名          [OCI-Lob::writeTemporary()][2154] |
| 2034 |     [octdec][2155] | 八进制转换为十进制 |
| 2035 |     [odbc_autocommit][2156] | Toggle autocommit behaviour |
| 2036 |     [odbc_binmode][2157] | Handling of binary column data |
| 2037 |     [odbc_close][2158] | Close an ODBC connection |
| 2038 |     [odbc_close_all][2159] | Close all ODBC connections |
| 2039 |     [odbc_columnprivileges][2160] | Lists columns and associated privileges for the given table |
| 2040 |     [odbc_columns][2161] | Lists the column names in specified tables |
| 2041 |     [odbc_commit][2162] | Commit an ODBC transaction |
| 2042 |     [odbc_connect][2163] | Connect to a datasource |
| 2043 |     [odbc_cursor][2164] | Get cursorname |
| 2044 |     [odbc_data_source][2165] | Returns information about a current connection |
| 2045 |     [odbc_do][2166] | 别名          [odbc_exec()][2167] |
| 2046 |     [odbc_error][2168] | Get the last error code |
| 2047 |     [odbc_errormsg][2169] | Get the last error message |
| 2048 |     [odbc_exec][2170] | Prepare and execute an SQL statement |
| 2049 |     [odbc_execute][2171] | Execute a prepared statement |
| 2050 |     [odbc_fetch_array][2172] | Fetch a result row as an associative array |
| 2051 |     [odbc_fetch_into][2173] | Fetch one result row into array |
| 2052 |     [odbc_fetch_object][2174] | Fetch a result row as an object |
| 2053 |     [odbc_fetch_row][2175] | Fetch a row |
| 2054 |     [odbc_field_len][2176] | Get the length (precision) of a field |
| 2055 |     [odbc_field_name][2177] | Get the columnname |
| 2056 |     [odbc_field_num][2178] | Return column number |
| 2057 |     [odbc_field_precision][2179] | 别名          [odbc_field_len()][2180] |
| 2058 |     [odbc_field_scale][2181] | Get the scale of a field |
| 2059 |     [odbc_field_type][2182] | Datatype of a field |
| 2060 |     [odbc_foreignkeys][2183] | Retrieves a list of foreign keys |
| 2061 |     [odbc_free_result][2184] | Free resources associated with a result |
| 2062 |     [odbc_gettypeinfo][2185] | Retrieves information about data types supported by the data source |
| 2063 |     [odbc_longreadlen][2186] | Handling of LONG columns |
| 2064 |     [odbc_next_result][2187] | Checks if multiple results are available |
| 2065 |     [odbc_num_fields][2188] | Number of columns in a result |
| 2066 |     [odbc_num_rows][2189] | Number of rows in a result |
| 2067 |     [odbc_pconnect][2190] | Open a persistent database connection |
| 2068 |     [odbc_prepare][2191] | Prepares a statement for execution |
| 2069 |     [odbc_primarykeys][2192] | Gets the primary keys for a table |
| 2070 |     [odbc_procedurecolumns][2193] | Retrieve information about parameters to procedures |
| 2071 |     [odbc_procedures][2194] | Get the list of procedures stored in a specific data source |
| 2072 |     [odbc_result][2195] | Get result data |
| 2073 |     [odbc_result_all][2196] | Print result as HTML table |
| 2074 |     [odbc_rollback][2197] | Rollback a transaction |
| 2075 |     [odbc_setoption][2198] | Adjust ODBC settings |
| 2076 |     [odbc_specialcolumns][2199] | Retrieves special columns |
| 2077 |     [odbc_statistics][2200] | Retrieve statistics about a table |
| 2078 |     [odbc_tableprivileges][2201] | Lists tables and the privileges associated with each table |
| 2079 |     [odbc_tables][2202] | Get the list of table names stored in a specific data source |
| 2080 |     [opcache_compile_file][2203] | 无需运行，即可编译并缓存 PHP 脚本 |
| 2081 |     [opcache_get_configuration][2204] | 获取缓存的配置信息 |
| 2082 |     [opcache_get_status][2205] | 获取缓存的状态信息 |
| 2083 |     [opcache_invalidate][2206] | 废除脚本缓存 |
| 2084 |     [opcache_reset][2207] | 重置字节码缓存的内容 |
| 2085 |     [opendir][2208] | 打开目录句柄 |
| 2086 |     [openlog][2209] | Open connection to system logger |
| 2087 |     [openssl_cipher_iv_length][2210] | Gets the cipher iv length |
| 2088 |     [openssl_csr_export][2211] | Exports a CSR as a string |
| 2089 |     [openssl_csr_export_to_file][2212] | Exports a CSR to a file |
| 2090 |     [openssl_csr_get_public_key][2213] | Returns the public key of a CSR |
| 2091 |     [openssl_csr_get_subject][2214] | Returns the subject of a CSR |
| 2092 |     [openssl_csr_new][2215] | Generates a CSR |
| 2093 |     [openssl_csr_sign][2216] | Sign a CSR with another certificate (or itself) and generate a certificate |
| 2094 |     [openssl_decrypt][2217] | Decrypts data |
| 2095 |     [openssl_dh_compute_key][2218] | Computes shared secret for public value of remote DH key and local DH key |
| 2096 |     [openssl_digest][2219] | Computes a digest |
| 2097 |     [openssl_encrypt][2220] | 加密数据 |
| 2098 |     [openssl_error_string][2221] | Return openSSL error message |
| 2099 |     [openssl_free_key][2222] | Free key resource |
| 2100 |     [openssl_get_cert_locations][2223] | Retrieve the available certificate locations |
| 2101 |     [openssl_get_cipher_methods][2224] | Gets available cipher methods |
| 2102 |     [openssl_get_md_methods][2225] | Gets available digest methods |
| 2103 |     [openssl_get_privatekey][2226] | 别名          [openssl_pkey_get_private()][2227] |
| 2104 |     [openssl_get_publickey][2228] | 别名          [openssl_pkey_get_public()][2229] |
| 2105 |     [openssl_open][2230] | Open sealed data |
| 2106 |     [openssl_pbkdf2][2231] | Generates a PKCS5 v2 PBKDF2 string |
| 2107 |     [openssl_pkcs12_export][2232] | Exports a PKCS#12 Compatible Certificate Store File to variable |
| 2108 |     [openssl_pkcs12_export_to_file][2233] | Exports a PKCS#12 Compatible Certificate Store File |
| 2109 |     [openssl_pkcs12_read][2234] | Parse a PKCS#12 Certificate Store into an array |
| 2110 |     [openssl_pkcs7_decrypt][2235] | Decrypts an S/MIME encrypted message |
| 2111 |     [openssl_pkcs7_encrypt][2236] | Encrypt an S/MIME message |
| 2112 |     [openssl_pkcs7_sign][2237] | Sign an S/MIME message |
| 2113 |     [openssl_pkcs7_verify][2238] | Verifies the signature of an S/MIME signed message |
| 2114 |     [openssl_pkey_export][2239] | Gets an exportable representation of a key into a string |
| 2115 |     [openssl_pkey_export_to_file][2240] | Gets an exportable representation of a key into a file |
| 2116 |     [openssl_pkey_free][2241] | Frees a private key |
| 2117 |     [openssl_pkey_get_details][2242] | Returns an array with the key details |
| 2118 |     [openssl_pkey_get_private][2243] | Get a private key |
| 2119 |     [openssl_pkey_get_public][2244] | Extract public key from certificate and prepare it for use |
| 2120 |     [openssl_pkey_new][2245] | Generates a new private key |
| 2121 |     [openssl_private_decrypt][2246] | Decrypts data with private key |
| 2122 |     [openssl_private_encrypt][2247] | Encrypts data with private key |
| 2123 |     [openssl_public_decrypt][2248] | Decrypts data with public key |
| 2124 |     [openssl_public_encrypt][2249] | Encrypts data with public key |
| 2125 |     [openssl_random_pseudo_bytes][2250] | Generate a pseudo-random string of bytes |
| 2126 |     [openssl_seal][2251] | Seal (encrypt) data |
| 2127 |     [openssl_sign][2252] | Generate signature |
| 2128 |     [openssl_spki_export][2253] | Exports a valid PEM formatted public key signed public key and challenge |
| 2129 |     [openssl_spki_export_challenge][2254] | Exports the challenge assoicated with a signed public key and challenge |
| 2130 |     [openssl_spki_new][2255] | Generate a new signed public key and challenge |
| 2131 |     [openssl_spki_verify][2256] | Verifies a signed public key and challenge |
| 2132 |     [openssl_verify][2257] | Verify signature |
| 2133 |     [openssl_x509_check_private_key][2258] | Checks if a private key corresponds to a certificate |
| 2134 |     [openssl_x509_checkpurpose][2259] | Verifies if a certificate can be used for a particular purpose |
| 2135 |     [openssl_x509_export][2260] | Exports a certificate as a string |
| 2136 |     [openssl_x509_export_to_file][2261] | Exports a certificate to file |
| 2137 |     [openssl_x509_fingerprint][2262] | Calculates the fingerprint, or digest, of a given X.509 certificate |
| 2138 |     [openssl_x509_free][2263] | Free certificate resource |
| 2139 |     [openssl_x509_parse][2264] | Parse an X509 certificate and return the information as an array |
| 2140 |     [openssl_x509_read][2265] | Parse an X.509 certificate and return a resource identifier for it |
| 2141 |     [ord][2266] | 返回字符的 ASCII 码值 |
| 2142 |     [output_add_rewrite_var][2267] | 添加URL重写器的值（Add URL rewriter values） |
| 2143 |     [output_reset_rewrite_vars][2268] | 重设URL重写器的值（Reset URL rewriter values） |

| p |-|-|
|-|-|-|
| 2144 |     [parse_ini_file][2269] | 解析一个配置文件 |
| 2145 |     [parse_ini_string][2270] | 解析配置字符串 |
| 2146 |     [parse_str][2271] | 将字符串解析成多个变量 |
| 2147 |     [parse_url][2272] | 解析 URL，返回其组成部分 |
| 2148 |     [passthru][2273] | 执行外部程序并且显示原始输出 |
| 2149 |     [password_get_info][2274] | 返回指定哈希（hash）的相关信息 |
| 2150 |     [password_hash][2275] | 创建密码的哈希（hash） |
| 2151 |     [password_needs_rehash][2276] | Checks if the given hash matches the given options |
| 2152 |     [password_verify][2277] | 验证密码是否和哈希匹配 |
| 2153 |     [pathinfo][2278] | 返回文件路径的信息 |
| 2154 |     [pclose][2279] | 关闭进程文件指针 |
| 2155 |     [pcntl_alarm][2280] | 为进程设置一个alarm闹钟信号 |
| 2156 |     [pcntl_async_signals][2281] | Enable/disable asynchronous signal handling or return the old setting |
| 2157 |     [pcntl_errno][2282] | 别名          [pcntl_get_last_error()][2283] |
| 2158 |     [pcntl_exec][2284] | 在当前进程空间执行指定程序 |
| 2159 |     [pcntl_fork][2285] | 在当前进程当前位置产生分支（子进程）。译注：fork是创建了一个子进程，父进程和子进程 都从fork的位置开始向下继续执行，不同的是父进程执行过程中，得到的fork返回值为子进程 号，而子进程得到的是0。 |
| 2160 |     [pcntl_get_last_error][2286] | Retrieve the error number set by the last pcntl function which failed |
| 2161 |     [pcntl_getpriority][2287] | 获取任意进程的优先级 |
| 2162 |     [pcntl_setpriority][2288] | 修改任意进程的优先级 |
| 2163 |     [pcntl_signal][2289] | 安装一个信号处理器 |
| 2164 |     [pcntl_signal_dispatch][2290] | 调用等待信号的处理器 |
| 2165 |     [pcntl_signal_get_handler][2291] | Get the current handler for specified signal |
| 2166 |     [pcntl_sigprocmask][2292] | 设置或检索阻塞信号 |
| 2167 |     [pcntl_sigtimedwait][2293] | 带超时机制的信号等待 |
| 2168 |     [pcntl_sigwaitinfo][2294] | 等待信号 |
| 2169 |     [pcntl_strerror][2295] | Retrieve the system error message associated with the given errno |
| 2170 |     [pcntl_wait][2296] | 等待或返回fork的子进程状态 |
| 2171 |     [pcntl_waitpid][2297] | 等待或返回fork的子进程状态 |
| 2172 |     [pcntl_wexitstatus][2298] | 返回一个中断的子进程的返回代码 |
| 2173 |     [pcntl_wifexited][2299] | 检查状态代码是否代表一个正常的退出。 |
| 2174 |     [pcntl_wifsignaled][2300] | 检查子进程状态码是否代表由于某个信号而中断 |
| 2175 |     [pcntl_wifstopped][2301] | 检查子进程当前是否已经停止 |
| 2176 |     [pcntl_wstopsig][2302] | 返回导致子进程停止的信号 |
| 2177 |     [pcntl_wtermsig][2303] | 返回导致子进程中断的信号 |
| 2178 |     [pdo::prepare][2304] | Prepares a statement for execution and returns a statement object |
| 2179 |     [pfsockopen][2305] | 打开一个持久的网络连接或者Unix套接字连接。 |
| 2180 |     [pg_affected_rows][2306] | 返回受影响的记录数目 |
| 2181 |     [pg_cancel_query][2307] | 取消异步查询 |
| 2182 |     [pg_client_encoding][2308] | 取得客户端编码方式 |
| 2183 |     [pg_close][2309] | 关闭一个 PostgreSQL 连接 |
| 2184 |     [pg_connect][2310] | 打开一个 PostgreSQL 连接 |
| 2185 |     [pg_connection_busy][2311] | 获知连接是否为忙 |
| 2186 |     [pg_connection_reset][2312] | 重置连接（再次连接） |
| 2187 |     [pg_connection_status][2313] | 获得连接状态 |
| 2188 |     [pg_convert][2314] | 将关联的数组值转换为适合 SQL 语句的格式。 |
| 2189 |     [pg_copy_from][2315] | 根据数组将记录插入表中 |
| 2190 |     [pg_copy_to][2316] | 将一个表拷贝到数组中 |
| 2191 |     [pg_dbname][2317] | 获得数据库名 |
| 2192 |     [pg_delete][2318] | 删除记录 |
| 2193 |     [pg_end_copy][2319] | 与 PostgreSQL 后端同步 |
| 2194 |     [pg_escape_bytea][2320] | 转义 bytea 类型的二进制数据 |
| 2195 |     [pg_escape_identifier][2321] | Escape a identifier for insertion into a text field |
| 2196 |     [pg_escape_literal][2322] | Escape a literal for insertion into a text field |
| 2197 |     [pg_escape_string][2323] | 转义 text/char 类型的字符串 |
| 2198 |     [pg_execute][2324] | Sends a request to execute a prepared statement with given parameters, and waits for the result |
| 2199 |     [pg_fetch_all][2325] | 从结果中提取所有行作为一个数组 |
| 2200 |     [pg_fetch_all_columns][2326] | Fetches all rows in a particular result column as an array |
| 2201 |     [pg_fetch_array][2327] | 提取一行作为数组 |
| 2202 |     [pg_fetch_assoc][2328] | 提取一行作为关联数组 |
| 2203 |     [pg_fetch_object][2329] | 提取一行作为对象 |
| 2204 |     [pg_fetch_result][2330] | 从结果资源中返回值 |
| 2205 |     [pg_fetch_row][2331] | 提取一行作为枚举数组 |
| 2206 |     [pg_field_is_null][2332] | 测试字段是否为           **`NULL`**  |
| 2207 |     [pg_field_name][2333] | 返回字段的名字 |
| 2208 |     [pg_field_num][2334] | 返回字段的编号 |
| 2209 |     [pg_field_prtlen][2335] | 返回打印出来的长度 |
| 2210 |     [pg_field_size][2336] | 返回指定字段占用内部存储空间的大小 |
| 2211 |     [pg_field_table][2337] | Returns the name or oid of the tables field |
| 2212 |     [pg_field_type][2338] | 返回相应字段的类型名称 |
| 2213 |     [pg_field_type_oid][2339] | Returns the type ID (OID) for the corresponding field number |
| 2214 |     [pg_free_result][2340] | 释放查询结果占用的内存 |
| 2215 |     [pg_get_notify][2341] | Ping 数据库连接 |
| 2216 |     [pg_get_pid][2342] | Ping 数据库连接 |
| 2217 |     [pg_get_result][2343] | 取得异步查询结果 |
| 2218 |     [pg_host][2344] | 返回和某连接关联的主机名 |
| 2219 |     [pg_insert][2345] | 将数组插入到表中 |
| 2220 |     [pg_last_error][2346] | 得到某连接的最后一条错误信息 |
| 2221 |     [pg_last_notice][2347] | 返回 PostgreSQL 服务器最新一条公告信息 |
| 2222 |     [pg_last_oid][2348] | 返回上一个对象的 oid |
| 2223 |     [pg_lo_close][2349] | 关闭一个大型对象 |
| 2224 |     [pg_lo_create][2350] | 新建一个大型对象 |
| 2225 |     [pg_lo_export][2351] | 将大型对象导出到文件 |
| 2226 |     [pg_lo_import][2352] | 将文件导入为大型对象 |
| 2227 |     [pg_lo_open][2353] | 打开一个大型对象 |
| 2228 |     [pg_lo_read][2354] | 从大型对象中读入数据 |
| 2229 |     [pg_lo_read_all][2355] | 读入整个大型对象并直接发送给浏览器 |
| 2230 |     [pg_lo_seek][2356] | 移动大型对象中的指针 |
| 2231 |     [pg_lo_tell][2357] | 返回大型对象的当前指针位置 |
| 2232 |     [pg_lo_unlink][2358] | 删除一个大型对象 |
| 2233 |     [pg_lo_write][2359] | 向大型对象写入数据 |
| 2234 |     [pg_meta_data][2360] | 获得表的元数据 |
| 2235 |     [pg_num_fields][2361] | 返回字段的数目 |
| 2236 |     [pg_num_rows][2362] | 返回行的数目 |
| 2237 |     [pg_options][2363] | 获得和连接有关的选项 |
| 2238 |     [pg_parameter_status][2364] | Looks up a current parameter setting of the server |
| 2239 |     [pg_pconnect][2365] | 打开一个持久的 PostgreSQL 连接 |
| 2240 |     [pg_ping][2366] | Ping 数据库连接 |
| 2241 |     [pg_port][2367] | 返回该连接的端口号 |
| 2242 |     [pg_prepare][2368] | Submits a request to create a prepared statement with the given parameters, and waits for completion |
| 2243 |     [pg_put_line][2369] | 向 PostgreSQL 后端发送以 NULL 结尾的字符串 |
| 2244 |     [pg_query][2370] | 执行查询 |
| 2245 |     [pg_query_params][2371] | Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text |
| 2246 |     [pg_result_error][2372] | 获得查询结果的错误信息 |
| 2247 |     [pg_result_error_field][2373] | Returns an individual field of an error report |
| 2248 |     [pg_result_seek][2374] | 在结果资源中设定内部行偏移量 |
| 2249 |     [pg_result_status][2375] | 获得查询结果的状态 |
| 2250 |     [pg_select][2376] | 选择记录 |
| 2251 |     [pg_send_execute][2377] | Sends a request to execute a prepared statement with given parameters, without waiting for the result(s) |
| 2252 |     [pg_send_prepare][2378] | Sends a request to create a prepared statement with the given parameters, without waiting for completion |
| 2253 |     [pg_send_query][2379] | 发送异步查询 |
| 2254 |     [pg_send_query_params][2380] | Submits a command and separate parameters to the server without waiting for the result(s) |
| 2255 |     [pg_set_client_encoding][2381] | 设定客户端编码 |
| 2256 |     [pg_set_error_verbosity][2382] | Determines the verbosity of messages returned by   [pg_last_error()][2383]and          [pg_result_error()][2384] |
| 2257 |     [pg_trace][2385] | 启动一个 PostgreSQL 连接的追踪功能 |
| 2258 |     [pg_transaction_status][2386] | Returns the current in-transaction status of the server |
| 2259 |     [pg_tty][2387] | 返回该连接的 tty 号 |
| 2260 |     [pg_unescape_bytea][2388] | 取消 bytea 类型中的字符串转义 |
| 2261 |     [pg_untrace][2389] | 关闭 PostgreSQL 连接的追踪功能 |
| 2262 |     [pg_update][2390] | 更新表 |
| 2263 |     [pg_version][2391] | Returns an array with client, protocol and server version (when available) |
| 2264 |     [php_ini_loaded_file][2392] | 取得已加载的 php.ini 文件的路径 |
| 2265 |     [php_ini_scanned_files][2393] | 返回从额外 ini 目录里解析的 .ini 文件列表 |
| 2266 |     [php_logo_guid][2394] | 获取 logo 的 guid |
| 2267 |     [php_sapi_name][2395] | 返回 web 服务器和 PHP 之间的接口类型 |
| 2268 |     [php_strip_whitespace][2396] | 返回删除注释和空格后的PHP源码 |
| 2269 |     [php_uname][2397] | 返回运行 PHP 的系统的有关信息 |
| 2270 |     [phpcredits][2398] | 打印 PHP 贡献者名单 |
| 2271 |     [phpinfo][2399] | 输出关于 PHP 配置的信息 |
| 2272 |     [phpversion][2400] | 获取当前的PHP版本 |
| 2273 |     [pi][2401] | 得到圆周率值 |
| 2274 |     [png2wbmp][2402] | 将 PNG 图像文件转换为 WBMP 图像文件 |
| 2275 |     [pool::collect][2403] | 回收已完成任务的引用 |
| 2276 |     [pool::resize][2404] | 改变 Pool 对象的可容纳 Worker 对象的数量 |
| 2277 |     [pool::shutdown][2405] | 停止所有的 Worker 对象 |
| 2278 |     [pool::submit][2406] | 提交对象以执行 |
| 2279 |     [popen][2407] | 打开进程文件指针 |
| 2280 |     [pos][2408] |   [current()][2409]的别名 |
| 2281 |     [posix_access][2410] | Determine accessibility of a file |
| 2282 |     [posix_ctermid][2411] | Get path name of controlling terminal |
| 2283 |     [posix_errno][2412] | 别名          [posix_get_last_error()][2413] |
| 2284 |     [posix_get_last_error][2414] | Retrieve the error number set by the last posix function that failed |
| 2285 |     [posix_getcwd][2415] | Pathname of current directory |
| 2286 |     [posix_getegid][2416] | Return the effective group ID of the current process |
| 2287 |     [posix_geteuid][2417] | Return the effective user ID of the current process |
| 2288 |     [posix_getgid][2418] | Return the real group ID of the current process |
| 2289 |     [posix_getgrgid][2419] | Return info about a group by group id |
| 2290 |     [posix_getgrnam][2420] | Return info about a group by name |
| 2291 |     [posix_getgroups][2421] | Return the group set of the current process |
| 2292 |     [posix_getlogin][2422] | Return login name |
| 2293 |     [posix_getpgid][2423] | Get process group id for job control |
| 2294 |     [posix_getpgrp][2424] | Return the current process group identifier |
| 2295 |     [posix_getpid][2425] | 返回当前进程 id |
| 2296 |     [posix_getppid][2426] | Return the parent process identifier |
| 2297 |     [posix_getpwnam][2427] | Return info about a user by username |
| 2298 |     [posix_getpwuid][2428] | Return info about a user by user id |
| 2299 |     [posix_getrlimit][2429] | Return info about system resource limits |
| 2300 |     [posix_getsid][2430] | Get the current sid of the process |
| 2301 |     [posix_getuid][2431] | Return the real user ID of the current process |
| 2302 |     [posix_initgroups][2432] | Calculate the group access list |
| 2303 |     [posix_isatty][2433] | Determine if a file descriptor is an interactive terminal |
| 2304 |     [posix_kill][2434] | Send a signal to a process |
| 2305 |     [posix_mkfifo][2435] | Create a fifo special file (a named pipe) |
| 2306 |     [posix_mknod][2436] | Create a special or ordinary file (POSIX.1) |
| 2307 |     [posix_setegid][2437] | Set the effective GID of the current process |
| 2308 |     [posix_seteuid][2438] | Set the effective UID of the current process |
| 2309 |     [posix_setgid][2439] | Set the GID of the current process |
| 2310 |     [posix_setpgid][2440] | Set process group id for job control |
| 2311 |     [posix_setrlimit][2441] | Set system resource limits |
| 2312 |     [posix_setsid][2442] | Make the current process a session leader |
| 2313 |     [posix_setuid][2443] | Set the UID of the current process |
| 2314 |     [posix_strerror][2444] | Retrieve the system error message associated with the given errno |
| 2315 |     [posix_times][2445] | Get process times |
| 2316 |     [posix_ttyname][2446] | Determine terminal device name |
| 2317 |     [posix_uname][2447] | Get system name |
| 2318 |     [pow][2448] | 指数表达式 |
| 2319 |     [preg_filter][2449] | 执行一个正则表达式搜索和替换 |
| 2320 |     [preg_grep][2450] | 返回匹配模式的数组条目 |
| 2321 |     [preg_last_error][2451] | 返回最后一个PCRE正则执行产生的错误代码 |
| 2322 |     [preg_match][2452] | 执行匹配正则表达式 |
| 2323 |     [preg_match_all][2453] | 执行一个全局正则表达式匹配 |
| 2324 |     [preg_quote][2454] | 转义正则表达式字符 |
| 2325 |     [preg_replace][2455] | 执行一个正则表达式的搜索和替换 |
| 2326 |     [preg_replace_callback][2456] | 执行一个正则表达式搜索并且使用一个回调进行替换 |
| 2327 |     [preg_replace_callback_array][2457] | Perform a regular expression search and replace using callbacks |
| 2328 |     [preg_split][2458] | 通过一个正则表达式分隔字符串 |
| 2329 |     [prev][2459] | 将数组的内部指针倒回一位 |
| 2330 |     [print_r][2460] | 打印关于变量的易于理解的信息。 |
| 2331 |     [printf][2461] | 输出格式化字符串 |
| 2332 |     [proc_close][2462] | 关闭由   [proc_open()][2463]打开的进程并且返回进程退出码 |
| 2333 |     [proc_get_status][2464] | 获取由       [proc_open()][2463]函数打开的进程的信息 |
| 2334 |     [proc_nice][2466] | 修改当前进程的优先级 |
| 2335 |     [proc_open][2467] | 执行一个命令，并且打开用来输入/输出的文件指针。 |
| 2336 |     [proc_terminate][2468] | 杀除由 proc_open 打开的进程 |
| 2337 |     [property_exists][2469] | 检查对象或类是否具有该属性 |
| 2338 |     [pspell_add_to_personal][2470] | Add the word to a personal wordlist |
| 2339 |     [pspell_add_to_session][2471] | Add the word to the wordlist in the current session |
| 2340 |     [pspell_check][2472] | Check a word |
| 2341 |     [pspell_clear_session][2473] | Clear the current session |
| 2342 |     [pspell_config_create][2474] | Create a config used to open a dictionary |
| 2343 |     [pspell_config_data_dir][2475] | Location of language data files |
| 2344 |     [pspell_config_dict_dir][2476] | Location of the main word list |
| 2345 |     [pspell_config_ignore][2477] | Ignore words less than N characters long |
| 2346 |     [pspell_config_mode][2478] | Change the mode number of suggestions returned |
| 2347 |     [pspell_config_personal][2479] | Set a file that contains personal wordlist |
| 2348 |     [pspell_config_repl][2480] | Set a file that contains replacement pairs |
| 2349 |     [pspell_config_runtogether][2481] | Consider run-together words as valid compounds |
| 2350 |     [pspell_config_save_repl][2482] | Determine whether to save a replacement pairs list along with the wordlist |
| 2351 |     [pspell_new][2483] | Load a new dictionary |
| 2352 |     [pspell_new_config][2484] | Load a new dictionary with settings based on a given config |
| 2353 |     [pspell_new_personal][2485] | Load a new dictionary with personal wordlist |
| 2354 |     [pspell_save_wordlist][2486] | Save the personal wordlist to a file |
| 2355 |     [pspell_store_replacement][2487] | Store a replacement pair for a word |
| 2356 |     [pspell_suggest][2488] | Suggest spellings of a word |
| 2357 |     [putenv][2489] | 设置环境变量的值 |

| q |-|-|
|-|-|-|
| 2358 |     [quoted_printable_encode][2490] | 将 8-bit 字符串转换成 quoted-printable 字符串 |
| 2359 |     [quotemeta][2491] | 转义元字符集 |

| r |-|-|
|-|-|-|
| 2360 |     [rand][2492] | 产生一个随机整数 |
| 2361 |     [random_bytes][2493] | Generates cryptographically secure pseudo-random bytes |
| 2362 |     [range][2494] | 根据范围创建数组，包含指定的元素 |
| 2363 |     [rawurldecode][2495] | 对已编码的 URL 字符串进行解码 |
| 2364 |     [rawurlencode][2496] | 按照 RFC 3986 对 URL 进行编码 |
| 2365 |     [read_exif_data][2497] | 别名          [exif_read_data()][2498] |
| 2366 |     [readdir][2499] | 从目录句柄中读取条目 |
| 2367 |     [readfile][2500] | 输出文件 |
| 2368 |     [readgzfile][2501] | Output a gz-file |
| 2369 |     [readline][2502] | 读取一行 |
| 2370 |     [readline_add_history][2503] | 添加一行命令行历史记录 |
| 2371 |     [readline_callback_handler_install][2504] | 初始化一个 readline 回调接口，然后终端输出提示信息并立即返回 |
| 2372 |     [readline_callback_handler_remove][2505] | 移除上一个安装的回调函数句柄并且恢复终端设置 |
| 2373 |     [readline_callback_read_char][2506] | 当一个行被接收时读取一个字符并且通知 readline 调用回调函数 |
| 2374 |     [readline_clear_history][2507] | 清除历史 |
| 2375 |     [readline_completion_function][2508] | 注册一个完成函数 |
| 2376 |     [readline_info][2509] | 获取/设置readline内部的各个变量 |
| 2377 |     [readline_list_history][2510] | 获取命令历史列表 |
| 2378 |     [readline_on_new_line][2511] | 通知readline将光标移动到新行 |
| 2379 |     [readline_read_history][2512] | 读取命令历史 |
| 2380 |     [readline_redisplay][2513] | 重绘显示区 |
| 2381 |     [readline_write_history][2514] | 写入历史记录 |
| 2382 |     [readlink][2515] | 返回符号连接指向的目标 |
| 2383 |     [realpath][2516] | 返回规范化的绝对路径名 |
| 2384 |     [realpath_cache_get][2517] | 获取真实目录缓存的详情 |
| 2385 |     [realpath_cache_size][2518] | 获取真实路径缓冲区的大小 |
| 2386 |     [recode][2519] | 别名          [recode_string()][2520] |
| 2387 |     [recode_file][2521] | Recode from file to file according to recode request |
| 2388 |     [recode_string][2522] | Recode a string according to a recode request |
| 2389 |     [register_shutdown_function][2523] | 注册一个会在php中止时执行的函数 |
| 2390 |     [register_tick_function][2524] | Register a function for execution on each tick |
| 2391 |     [rename][2525] | 重命名一个文件或目录 |
| 2392 |     [reset][2526] | 将数组的内部指针指向第一个单元 |
| 2393 |     [resourcebundle_count][2527] | Get number of elements in the bundle |
| 2394 |     [resourcebundle_create][2528] | Create a resource bundle |
| 2395 |     [resourcebundle_get][2529] | Get data from the bundle |
| 2396 |     [resourcebundle_locales][2530] | Get supported locales |
| 2397 |     [restore_error_handler][2531] | 还原之前的错误处理函数 |
| 2398 |     [restore_exception_handler][2532] | 恢复之前定义过的异常处理函数。 |
| 2399 |     [restore_include_path][2533] | 还原 include_path 配置选项的值 |
| 2400 |     [rewind][2534] | 倒回文件指针的位置 |
| 2401 |     [rewinddir][2535] | 倒回目录句柄 |
| 2402 |     [rmdir][2536] | 删除目录 |
| 2403 |     [round][2537] | 对浮点数进行四舍五入 |
| 2404 |     [rrd_create][2538] | Creates rrd database file |
| 2405 |     [rrd_error][2539] | Gets latest error message |
| 2406 |     [rrd_fetch][2540] | Fetch the data for graph as array |
| 2407 |     [rrd_first][2541] | Gets the timestamp of the first sample from rrd file |
| 2408 |     [rrd_graph][2542] | Creates image from a data |
| 2409 |     [rrd_info][2543] | Gets information about rrd file |
| 2410 |     [rrd_last][2544] | Gets unix timestamp of the last sample |
| 2411 |     [rrd_lastupdate][2545] | Gets information about last updated data |
| 2412 |     [rrd_restore][2546] | Restores the RRD file from XML dump |
| 2413 |     [rrd_tune][2547] | Tunes some RRD database file header options |
| 2414 |     [rrd_update][2548] | Updates the RRD database |
| 2415 |     [rrd_version][2549] | Gets information about underlying rrdtool library |
| 2416 |     [rrd_xport][2550] | Exports the information about RRD database |
| 2417 |     [rrdcreator::addarchive][2551] | Adds RRA - archive of data values for each data source |
| 2418 |     [rrdcreator::adddatasource][2552] | Adds data source definition for RRD database |
| 2419 |     [rrdcreator::save][2553] | Saves the RRD database to a file |
| 2420 |     [rrdgraph::save][2554] | Saves the result of query into image |
| 2421 |     [rrdgraph::saveverbose][2555] | Saves the RRD database query into image and returns the verbose information about generated graph |
| 2422 |     [rrdgraph::setoptions][2556] | Sets the options for rrd graph export |
| 2423 |     [rrdupdater::update][2557] | Update the RRD database file |
| 2424 |     [rsort][2558] | 对数组逆向排序 |
| 2425 |     [rtrim][2559] | 删除字符串末端的空白字符（或者其他字符） |

| s |-|-|
|-|-|-|
| 2426 |     [sem_acquire][2560] | Acquire a semaphore |
| 2427 |     [sem_get][2561] | Get a semaphore id |
| 2428 |     [sem_release][2562] | Release a semaphore |
| 2429 |     [sem_remove][2563] | Remove a semaphore |
| 2430 |     [serialize][2564] | 产生一个可存储的值的表示 |
| 2431 |     [session_abort][2565] | Discard session array changes and finish session |
| 2432 |     [session_cache_expire][2566] | 返回当前缓存的到期时间 |
| 2433 |     [session_cache_limiter][2567] | 读取/设置缓存限制器 |
| 2434 |     [session_commit][2568] |    [session_write_close()][2569]的别名 |
| 2435 |     [session_create_id][2570] | Create new session id |
| 2436 |     [session_decode][2571] | 解码会话数据 |
| 2437 |     [session_destroy][2572] | 销毁一个会话中的全部数据 |
| 2438 |     [session_encode][2573] | 将当前会话数据编码为一个字符串 |
| 2439 |     [session_gc][2574] | Perform session data garbage collection |
| 2440 |     [session_get_cookie_params][2575] | 获取会话 cookie 参数 |
| 2441 |     [session_id][2576] | 获取/设置当前会话 ID |
| 2442 |     [session_is_registered][2577] | 检查变量是否在会话中已经注册 |
| 2443 |     [session_module_name][2578] | 获取/设置会话模块名称 |
| 2444 |     [session_name][2579] | 读取/设置会话名称 |
| 2445 |     [session_regenerate_id][2580] | 使用新生成的会话 ID 更新现有会话 ID |
| 2446 |     [session_register][2581] | Register one or more global variables with the current session |
| 2447 |     [session_register_shutdown][2582] | 关闭会话 |
| 2448 |     [session_reset][2583] | Re-initialize session array with original values |
| 2449 |     [session_save_path][2584] | 读取/设置当前会话的保存路径 |
| 2450 |     [session_set_cookie_params][2585] | 设置会话 cookie 参数 |
| 2451 |     [session_set_save_handler][2586] | 设置用户自定义会话存储函数 |
| 2452 |     [session_start][2587] | 启动新会话或者重用现有会话 |
| 2453 |     [session_status][2588] | 返回当前会话状态 |
| 2454 |     [session_unregister][2589] | Unregister a global variable from the current session |
| 2455 |     [session_unset][2590] | 释放所有的会话变量 |
| 2456 |     [session_write_close][2591] | Write session data and end session |
| 2457 |     [set_error_handler][2592] | 设置用户自定义的错误处理函数 |
| 2458 |     [set_exception_handler][2593] | 设置用户自定义的异常处理函数 |
| 2459 |     [set_file_buffer][2594] |  [stream_set_write_buffer()][2595]的别名 |
| 2460 |     [set_include_path][2596] | 设置 include_path 配置选项 |
| 2461 |     [set_magic_quotes_runtime][2597] | 设置当前 magic_quotes_runtime 配置选项的激活状态 |
| 2462 |     [set_socket_blocking][2598] | 别名          [stream_set_blocking()][2599] |
| 2463 |     [set_time_limit][2600] | 设置脚本最大执行时间 |
| 2464 |     [setcookie][2601] | 发送 Cookie |
| 2465 |     [setlocale][2602] | 设置地区信息 |
| 2466 |     [setrawcookie][2603] | 发送未经 URL 编码的 cookie |
| 2467 |     [settype][2604] | 设置变量的类型 |
| 2468 |     [sha1][2605] | 计算字符串的 sha1 散列值 |
| 2469 |     [sha1_file][2606] | 计算文件的 sha1 散列值 |
| 2470 |     [shell_exec][2607] | 通过 shell 环境执行命令，并且将完整的输出以字符串的方式返回。 |
| 2471 |     [shm_attach][2608] | Creates or open a shared memory segment |
| 2472 |     [shm_detach][2609] | Disconnects from shared memory segment |
| 2473 |     [shm_get_var][2610] | Returns a variable from shared memory |
| 2474 |     [shm_has_var][2611] | Check whether a specific entry exists |
| 2475 |     [shm_put_var][2612] | Inserts or updates a variable in shared memory |
| 2476 |     [shm_remove][2613] | Removes shared memory from Unix systems |
| 2477 |     [shm_remove_var][2614] | Removes a variable from shared memory |
| 2478 |     [shmop_close][2615] | Close shared memory block |
| 2479 |     [shmop_delete][2616] | Delete shared memory block |
| 2480 |     [shmop_open][2617] | Create or open shared memory block |
| 2481 |     [shmop_read][2618] | Read data from shared memory block |
| 2482 |     [shmop_size][2619] | Get size of shared memory block |
| 2483 |     [shmop_write][2620] | Write data into shared memory block |
| 2484 |     [show_source][2621] | 别名          [highlight_file()][2622] |
| 2485 |     [shuffle][2623] | 打乱数组 |
| 2486 |     [similar_text][2624] | 计算两个字符串的相似度 |
| 2487 |     [simplexml_import_dom][2625] | Get a     **SimpleXMLElement** object from a DOM node |
| 2488 |     [simplexml_load_file][2626] | Interprets an XML file into an object |
| 2489 |     [simplexml_load_string][2627] | Interprets a string of XML into an object |
| 2490 |     [sin][2628] | 正弦 |
| 2491 |     [sinh][2629] | 双曲正弦 |
| 2492 |     [sizeof][2630] | [count()][2631]的别名 |
| 2493 |     [sleep][2632] | 延缓执行 |
| 2494 |     [snmp2_get][2633] | Fetch an          <acronym>SNMP</acronym>object |
| 2495 |     [snmp2_getnext][2634] | Fetch the          <acronym>SNMP</acronym>
object which follows the given object id |
| 2496 |     [snmp2_real_walk][2635] | Return all objects including their respective object ID within the specified one |
| 2497 |     [snmp2_set][2636] | Set the value of an          <acronym>SNMP</acronym>object |
| 2498 |     [snmp2_walk][2637] | Fetch all the          <acronym>SNMP</acronym>objects from an agent |
| 2499 |     [snmp3_get][2638] | Fetch an          <acronym>SNMP</acronym>object |
| 2500 |     [snmp3_getnext][2639] | Fetch the          <acronym>SNMP</acronym>object which follows the given object id |
| 2501 |     [snmp3_real_walk][2640] | Return all objects including their respective object ID within the specified one |
| 2502 |     [snmp3_set][2641] | Set the value of an SNMP object |
| 2503 |     [snmp3_walk][2642] | Fetch all the          <acronym>SNMP</acronym>objects from an agent |
| 2504 |     [snmp_get_quick_print][2643] | 返回 UCD 库中 quick_print 设置的当前值 |
| 2505 |     [snmp_get_valueretrieval][2644] | Return the method how the SNMP values will be returned |
| 2506 |     [snmp_read_mib][2645] | Reads and parses a MIB file into the active MIB tree |
| 2507 |     [snmp_set_enum_print][2646] | Return all values that are enums with their enum value instead of the raw integer |
| 2508 |     [snmp_set_oid_numeric_print][2647] | Set the OID output format |
| 2509 |     [snmp_set_oid_output_format][2648] | Set the OID output format |
| 2510 |     [snmp_set_quick_print][2649] | 设置 UCD SNMP 库中 quick_print 的值 |
| 2511 |     [snmp_set_valueretrieval][2650] | Specify the method how the SNMP values will be returned |
| 2512 |     [snmpget][2651] | 获取一个 SNMP 对象 |
| 2513 |     [snmpgetnext][2652] | Fetch the          <acronym>SNMP</acronym>object which follows the given object id |
| 2514 |     [snmprealwalk][2653] | 返回指定的所有对象，包括它们各自的对象 ID |
| 2515 |     [snmpset][2654] | 设置一个 SNMP 对象 |
| 2516 |     [snmpwalk][2655] | 从代理返回所有的 SNMP 对象 |
| 2517 |     [snmpwalkoid][2656] | 查询关于网络实体的信息树 |
| 2518 |     [socket_accept][2657] | Accepts a connection on a socket |
| 2519 |     [socket_bind][2658] | 给套接字绑定名字 |
| 2520 |     [socket_clear_error][2659] | 清除套接字或者最后的错误代码上的错误 |
| 2521 |     [socket_close][2660] | 关闭套接字资源 |
| 2522 |     [socket_cmsg_space][2661] | Calculate message buffer size |
| 2523 |     [socket_connect][2662] | 开启一个套接字连接 |
| 2524 |     [socket_create][2663] | 创建一个套接字（通讯节点） |
| 2525 |     [socket_create_listen][2664] | Opens a socket on port to accept connections |
| 2526 |     [socket_create_pair][2665] | Creates a pair of indistinguishable sockets and stores them in an array |
| 2527 |     [socket_get_option][2666] | Gets socket options for the socket |
| 2528 |     [socket_get_status][2667] | 别名          [stream_get_meta_data()][2668] |
| 2529 |     [socket_getopt][2669] | 别名          [socket_get_option()][2670] |
| 2530 |     [socket_getpeername][2671] | Queries the remote side of the given socket which may either result in host/port or in a Unix filesystem path, dependent on its type |
| 2531 |     [socket_getsockname][2672] | Queries the local side of the given socket which may either result in host/port or in a Unix filesystem path, dependent on its type |
| 2532 |     [socket_import_stream][2673] | Import a stream |
| 2533 |     [socket_last_error][2674] | Returns the last error on the socket |
| 2534 |     [socket_listen][2675] | Listens for a connection on a socket |
| 2535 |     [socket_read][2676] | Reads a maximum of length bytes from a socket |
| 2536 |     [socket_recv][2677] | 从已连接的socket接收数据 |
| 2537 |     [socket_recvfrom][2678] | Receives data from a socket whether or not it is connection-oriented |
| 2538 |     [socket_recvmsg][2679] | Read a message |
| 2539 |     [socket_select][2680] | Runs the select() system call on the given arrays of sockets with a specified timeout |
| 2540 |     [socket_send][2681] | Sends data to a connected socket |
| 2541 |     [socket_sendmsg][2682] | Send a message |
| 2542 |     [socket_sendto][2683] | Sends a message to a socket, whether it is connected or not |
| 2543 |     [socket_set_block][2684] | Sets blocking mode on a socket resource |
| 2544 |     [socket_set_blocking][2685] | 别名          [stream_set_blocking()][2599] |
| 2545 |     [socket_set_nonblock][2687] | Sets nonblocking mode for file descriptor fd |
| 2546 |     [socket_set_option][2688] | Sets socket options for the socket |
| 2547 |     [socket_set_timeout][2689] | 别名          [stream_set_timeout()][2690] |
| 2548 |     [socket_setopt][2691] | 别名          [socket_set_option()][2692] |
| 2549 |     [socket_shutdown][2693] | Shuts down a socket for receiving, sending, or both |
| 2550 |     [socket_strerror][2694] | Return a string describing a socket error |
| 2551 |     [socket_write][2695] | Write to a socket |
| 2552 |     [sort][2696] | 对数组排序 |
| 2553 |     [soundex][2697] | Calculate the soundex key of a string |
| 2554 |     [spl_autoload][2698] | __autoload()函数的默认实现 |
| 2555 |     [spl_autoload_call][2699] | 尝试调用所有已注册的__autoload()函数来装载请求类 |
| 2556 |     [spl_autoload_extensions][2700] | 注册并返回spl_autoload函数使用的默认文件扩展名。 |
| 2557 |     [spl_autoload_functions][2701] | 返回所有已注册的__autoload()函数。 |
| 2558 |     [spl_autoload_register][2702] | 注册给定的函数作为 __autoload 的实现 |
| 2559 |     [spl_autoload_unregister][2703] | 注销已注册的__autoload()函数 |
| 2560 |     [spl_classes][2704] | 返回所有可用的SPL类 |
| 2561 |     [spl_object_hash][2705] | 返回指定对象的hash id |
| 2562 |     [splfileinfo::getatime][2706] | Gets last access time of the file |
| 2563 |     [splfileinfo::getbasename][2707] | Gets the base name of the file |
| 2564 |     [splfileinfo::getctime][2708] | 获取文件 inode 修改时间 |
| 2565 |     [splfileinfo::getextension][2709] | Gets the file extension |
| 2566 |     [splfileinfo::getfileinfo][2710] | Gets an SplFileInfo object for the file |
| 2567 |     [splfileinfo::getfilename][2711] | Gets the filename |
| 2568 |     [splfileinfo::getgroup][2712] | Gets the file group |
| 2569 |     [splfileinfo::getinode][2713] | Gets the inode for the file |
| 2570 |     [splfileinfo::getlinktarget][2714] | Gets the target of a link |
| 2571 |     [splfileinfo::getmtime][2715] | Gets the last modified time |
| 2572 |     [splfileinfo::getowner][2716] | Gets the owner of the file |
| 2573 |     [splfileinfo::getpath][2717] | Gets the path without filename |
| 2574 |     [splfileinfo::getpathinfo][2718] | Gets an SplFileInfo object for the path |
| 2575 |     [splfileinfo::getpathname][2719] | Gets the path to the file |
| 2576 |     [splfileinfo::getperms][2720] | Gets file permissions |
| 2577 |     [splfileinfo::getrealpath][2721] | Gets absolute path to file |
| 2578 |     [splfileinfo::getsize][2722] | Gets file size |
| 2579 |     [splfileinfo::gettype][2723] | Gets file type |
| 2580 |     [splfileinfo::isdir][2724] | Tells if the file is a directory |
| 2581 |     [splfileinfo::isexecutable][2725] | Tells if the file is executable |
| 2582 |     [splfileinfo::isfile][2726] | Tells if the object references a regular file |
| 2583 |     [splfileinfo::islink][2727] | Tells if the file is a link |
| 2584 |     [splfileinfo::isreadable][2728] | Tells if file is readable |
| 2585 |     [splfileinfo::iswritable][2729] | Tells if the entry is writable |
| 2586 |     [splfileinfo::setfileclass][2730] | Sets the class used with          [SplFileInfo::openFile()][2731] |
| 2587 |     [splfileinfo::setinfoclass][2732] | Sets the class used with  [SplFileInfo::getFileInfo()][2733] and          [SplFileInfo::getPathInfo()][2734] |
| 2588 |     [split][2735] | 用正则表达式将字符串分割到数组中 |
| 2589 |     [spliti][2736] | 用正则表达式不区分大小写将字符串分割到数组中 |
| 2590 |     [spoofchecker::areconfusable][2737] | Checks if given strings can be confused |
| 2591 |     [spoofchecker::issuspicious][2738] | Checks if a given text contains any suspicious characters |
| 2592 |     [spoofchecker::setallowedlocales][2739] | Locales to use when running checks |
| 2593 |     [spoofchecker::setchecks][2740] | Set the checks to run |
| 2594 |     [sprintf][2741] | Return a formatted string |
| 2595 |     [sql_regcase][2742] | 产生用于不区分大小的匹配的正则表达式 |
| 2596 |     [sqlite_array_query][2743] | Execute a query against a given database and returns an array |
| 2597 |     [sqlite_busy_timeout][2744] | Set busy timeout duration, or disable busy handlers |
| 2598 |     [sqlite_changes][2745] | Returns the number of rows that were changed by the most recent SQL statement |
| 2599 |     [sqlite_close][2746] | Closes an open SQLite database |
| 2600 |     [sqlite_column][2747] | Fetches a column from the current row of a result set |
| 2601 |     [sqlite_create_aggregate][2748] | Register an aggregating UDF for use in SQL statements |
| 2602 |     [sqlite_create_function][2749] | Registers a "regular" User Defined Function for use in SQL statements |
| 2603 |     [sqlite_current][2750] | Fetches the current row from a result set as an array |
| 2604 |     [sqlite_error_string][2751] | Returns the textual description of an error code |
| 2605 |     [sqlite_escape_string][2752] | Escapes a string for use as a query parameter |
| 2606 |     [sqlite_exec][2753] | Executes a result-less query against a given database |
| 2607 |     [sqlite_factory][2754] | Opens an SQLite database and returns an SQLiteDatabase object |
| 2608 |     [sqlite_fetch_all][2755] | Fetches all rows from a result set as an array of arrays |
| 2609 |     [sqlite_fetch_array][2756] | Fetches the next row from a result set as an array |
| 2610 |     [sqlite_fetch_column_types][2757] | Return an array of column types from a particular table |
| 2611 |     [sqlite_fetch_object][2758] | Fetches the next row from a result set as an object |
| 2612 |     [sqlite_fetch_single][2759] | Fetches the first column of a result set as a string |
| 2613 |     [sqlite_fetch_string][2760] | 别名          [sqlite_fetch_single()][2761] |
| 2614 |     [sqlite_field_name][2762] | Returns the name of a particular field |
| 2615 |     [sqlite_has_more][2763] | Finds whether or not more rows are available |
| 2616 |     [sqlite_has_prev][2764] | Returns whether or not a previous row is available |
| 2617 |     [sqlite_last_error][2765] | Returns the error code of the last error for a database |
| 2618 |     [sqlite_last_insert_rowid][2766] | Returns the rowid of the most recently inserted row |
| 2619 |     [sqlite_libencoding][2767] | Returns the encoding of the linked SQLite library |
| 2620 |     [sqlite_libversion][2768] | Returns the version of the linked SQLite library |
| 2621 |     [sqlite_next][2769] | Seek to the next row number |
| 2622 |     [sqlite_num_fields][2770] | Returns the number of fields in a result set |
| 2623 |     [sqlite_num_rows][2771] | Returns the number of rows in a buffered result set |
| 2624 |     [sqlite_open][2772] | Opens an SQLite database and create the database if it does not exist |
| 2625 |     [sqlite_popen][2773] | Opens a persistent handle to an SQLite database and create the database if it does not exist |
| 2626 |     [sqlite_prev][2774] | Seek to the previous row number of a result set |
| 2627 |     [sqlite_query][2775] | Executes a query against a given database and returns a result handle |
| 2628 |     [sqlite_rewind][2776] | Seek to the first row number |
| 2629 |     [sqlite_seek][2777] | Seek to a particular row number of a buffered result set |
| 2630 |     [sqlite_single_query][2778] | Executes a query and returns either an array for one single column or the value of the first row |
| 2631 |     [sqlite_udf_decode_binary][2779] | Decode binary data passed as parameters to an          <acronym>UDF</acronym> |
| 2632 |     [sqlite_udf_encode_binary][2780] | Encode binary data before returning it from an UDF |
| 2633 |     [sqlite_unbuffered_query][2781] | Execute a query that does not prefetch and buffer all data |
| 2634 |     [sqlite_valid][2782] | Returns whether more rows are available |
| 2635 |     [sqlsrv_begin_transaction][2783] | Begins a database transaction |
| 2636 |     [sqlsrv_cancel][2784] | Cancels a statement |
| 2637 |     [sqlsrv_client_info][2785] | Returns information about the client and specified connection |
| 2638 |     [sqlsrv_close][2786] | Closes an open connection and releases resourses associated with the connection |
| 2639 |     [sqlsrv_commit][2787] | Commits a transaction that was begun with          [sqlsrv_begin_transaction()][2788] |
| 2640 |     [sqlsrv_configure][2789] | Changes the driver error handling and logging configurations |
| 2641 |     [sqlsrv_connect][2790] | Opens a connection to a Microsoft SQL Server database |
| 2642 |     [sqlsrv_errors][2791] | Returns error and warning information about the last SQLSRV operation performed |
| 2643 |     [sqlsrv_execute][2792] | Executes a statement prepared with          [sqlsrv_prepare()][2793] |
| 2644 |     [sqlsrv_fetch][2794] | Makes the next row in a result set available for reading |
| 2645 |     [sqlsrv_fetch_array][2795] | Returns a row as an array |
| 2646 |     [sqlsrv_fetch_object][2796] | Retrieves the next row of data in a result set as an object |
| 2647 |     [sqlsrv_field_metadata][2797] | Retrieves metadata for the fields of a statement prepared by [sqlsrv_prepare()][2793]or          [sqlsrv_query()][2799] |
| 2648 |     [sqlsrv_free_stmt][2800] | Frees all resources for the specified statement |
| 2649 |     [sqlsrv_get_config][2801] | Returns the value of the specified configuration setting |
| 2650 |     [sqlsrv_get_field][2802] | Gets field data from the currently selected row |
| 2651 |     [sqlsrv_has_rows][2803] | Indicates whether the specified statement has rows |
| 2652 |     [sqlsrv_next_result][2804] | Makes the next result of the specified statement active |
| 2653 |     [sqlsrv_num_fields][2805] | Retrieves the number of fields (columns) on a statement |
| 2654 |     [sqlsrv_num_rows][2806] | Retrieves the number of rows in a result set |
| 2655 |     [sqlsrv_prepare][2807] | Prepares a query for execution |
| 2656 |     [sqlsrv_query][2808] | Prepares and executes a query |
| 2657 |     [sqlsrv_rollback][2809] | Rolls back a transaction that was begun with          [sqlsrv_begin_transaction()][2788] |
| 2658 |     [sqlsrv_rows_affected][2811] | Returns the number of rows modified by the last INSERT, UPDATE, or DELETE query executed |
| 2659 |     [sqlsrv_send_stream_data][2812] | Sends data from parameter streams to the server |
| 2660 |     [sqlsrv_server_info][2813] | Returns information about the server |
| 2661 |     [sqrt][2814] | 平方根 |
| 2662 |     [srand][2815] | 播下随机数发生器种子 |
| 2663 |     [sscanf][2816] | 根据指定格式解析输入的字符 |
| 2664 |     [ssh2_auth_hostbased_file][2817] | Authenticate using a public hostkey |
| 2665 |     [ssh2_auth_none][2818] | Authenticate as "none" |
| 2666 |     [ssh2_auth_password][2819] | Authenticate over SSH using a plain password |
| 2667 |     [ssh2_auth_pubkey_file][2820] | Authenticate using a public key |
| 2668 |     [ssh2_connect][2821] | Connect to an SSH server |
| 2669 |     [ssh2_exec][2822] | Execute a command on a remote server |
| 2670 |     [ssh2_fetch_stream][2823] | Fetch an extended data stream |
| 2671 |     [ssh2_fingerprint][2824] | Retrieve fingerprint of remote server |
| 2672 |     [ssh2_methods_negotiated][2825] | Return list of negotiated methods |
| 2673 |     [ssh2_publickey_add][2826] | Add an authorized publickey |
| 2674 |     [ssh2_publickey_init][2827] | Initialize Publickey subsystem |
| 2675 |     [ssh2_publickey_list][2828] | List currently authorized publickeys |
| 2676 |     [ssh2_publickey_remove][2829] | Remove an authorized publickey |
| 2677 |     [ssh2_scp_recv][2830] | Request a file via SCP |
| 2678 |     [ssh2_scp_send][2831] | Send a file via SCP |
| 2679 |     [ssh2_sftp][2832] | Initialize SFTP subsystem |
| 2680 |     [ssh2_sftp_lstat][2833] | Stat a symbolic link |
| 2681 |     [ssh2_sftp_mkdir][2834] | Create a directory |
| 2682 |     [ssh2_sftp_readlink][2835] | Return the target of a symbolic link |
| 2683 |     [ssh2_sftp_realpath][2836] | Resolve the realpath of a provided path string |
| 2684 |     [ssh2_sftp_rename][2837] | Rename a remote file |
| 2685 |     [ssh2_sftp_rmdir][2838] | Remove a directory |
| 2686 |     [ssh2_sftp_stat][2839] | Stat a file on a remote filesystem |
| 2687 |     [ssh2_sftp_symlink][2840] | Create a symlink |
| 2688 |     [ssh2_sftp_unlink][2841] | Delete a file |
| 2689 |     [ssh2_shell][2842] | Request an interactive shell |
| 2690 |     [ssh2_tunnel][2843] | Open a tunnel through a remote server |
| 2691 |     [stat][2844] | 给出文件的信息 |
| 2692 |     [str_getcsv][2845] | 解析 CSV 字符串为一个数组 |
| 2693 |     [str_ireplace][2846] |  [str_replace()][2847]的忽略大小写版本 |
| 2694 |     [str_pad][2848] | 使用另一个字符串填充字符串为指定长度 |
| 2695 |     [str_repeat][2849] | 重复一个字符串 |
| 2696 |     [str_replace][2850] | 子字符串替换 |
| 2697 |     [str_rot13][2851] | 对字符串执行 ROT13 转换 |
| 2698 |     [str_shuffle][2852] | 随机打乱一个字符串 |
| 2699 |     [str_split][2853] | 将字符串转换为数组 |
| 2700 |     [str_word_count][2854] | 返回字符串中单词的使用情况 |
| 2701 |     [strcasecmp][2855] | 二进制安全比较字符串（不区分大小写） |
| 2702 |     [strchr][2856] | 别名          [strstr()][2857] |
| 2703 |     [strcmp][2858] | 二进制安全字符串比较 |
| 2704 |     [strcoll][2859] | 基于区域设置的字符串比较 |
| 2705 |     [strcspn][2860] | 获取不匹配遮罩的起始子字符串的长度 |
| 2706 |     [stream_bucket_append][2861] | Append bucket to brigade |
| 2707 |     [stream_bucket_make_writeable][2862] | Return a bucket object from the brigade for operating on |
| 2708 |     [stream_bucket_new][2863] | Create a new bucket for use on the current stream |
| 2709 |     [stream_bucket_prepend][2864] | Prepend bucket to brigade |
| 2710 |     [stream_context_create][2865] | 创建资源流上下文 |
| 2711 |     [stream_context_get_default][2866] | Retrieve the default stream context |
| 2712 |     [stream_context_get_options][2867] | 获取资源流/数据包/上下文的参数 |
| 2713 |     [stream_context_get_params][2868] | Retrieves parameters from a context |
| 2714 |     [stream_context_set_default][2869] | Set the default stream context |
| 2715 |     [stream_context_set_option][2870] | 对资源流、数据包或者上下文设置参数 |
| 2716 |     [stream_context_set_params][2871] | Set parameters for a stream/wrapper/context |
| 2717 |     [stream_copy_to_stream][2872] | Copies data from one stream to another |
| 2718 |     [stream_filter_append][2873] | Attach a filter to a stream |
| 2719 |     [stream_filter_prepend][2874] | Attach a filter to a stream |
| 2720 |     [stream_filter_register][2875] | Register a user defined stream filter |
| 2721 |     [stream_filter_remove][2876] | 从资源流里移除某个过滤器 |
| 2722 |     [stream_get_contents][2877] | 读取资源流到一个字符串 |
| 2723 |     [stream_get_filters][2878] | 获取已注册的数据流过滤器列表 |
| 2724 |     [stream_get_line][2879] | 从资源流里读取一行直到给定的定界符 |
| 2725 |     [stream_get_meta_data][2880] | 从封装协议文件指针中取得报头／元数据 |
| 2726 |     [stream_get_transports][2881] | 获取已注册的套接字传输协议列表 |
| 2727 |     [stream_get_wrappers][2882] | 获取已注册的流类型 |
| 2728 |     [stream_is_local][2883] | Checks if a stream is a local stream |
| 2729 |     [stream_register_wrapper][2884] | 别名          [stream_wrapper_register()][2885] |
| 2730 |     [stream_resolve_include_path][2886] | Resolve filename against the include path |
| 2731 |     [stream_select][2887] | Runs the equivalent of the select() system call on the given arrays of streams with a timeout specified by tv_sec and tv_usec |
| 2732 |     [stream_set_blocking][2888] | 为资源流设置阻塞或者阻塞模式 |
| 2733 |     [stream_set_chunk_size][2889] | 设置资源流区块大小 |
| 2734 |     [stream_set_read_buffer][2890] | Set read file buffering on the given stream |
| 2735 |     [stream_set_timeout][2891] | Set timeout period on a stream |
| 2736 |     [stream_set_write_buffer][2892] | Sets write file buffering on the given stream |
| 2737 |     [stream_socket_accept][2893] | 接受由   [stream_socket_server()][2894]创建的套接字连接 |
| 2738 |     [stream_socket_client][2895] | Open Internet or Unix domain socket connection |
| 2739 |     [stream_socket_enable_crypto][2896] | Turns encryption on/off on an already connected socket |
| 2740 |     [stream_socket_get_name][2897] | 获取本地或者远程的套接字名称 |
| 2741 |     [stream_socket_pair][2898] | 创建一对完全一样的网络套接字连接流 |
| 2742 |     [stream_socket_recvfrom][2899] | Receives data from a socket, connected or not |
| 2743 |     [stream_socket_sendto][2900] | Sends a message to a socket, whether it is connected or not |
| 2744 |     [stream_socket_server][2901] | Create an Internet or Unix domain server socket |
| 2745 |     [stream_socket_shutdown][2902] | Shutdown a full-duplex connection |
| 2746 |     [stream_supports_lock][2903] | Tells whether the stream supports locking |
| 2747 |     [stream_wrapper_register][2904] | 注册一个用 PHP 类实现的 URL 封装协议 |
| 2748 |     [stream_wrapper_restore][2905] | Restores a previously unregistered built-in wrapper |
| 2749 |     [stream_wrapper_unregister][2906] | Unregister a URL wrapper |
| 2750 |     [strftime][2907] | 根据区域设置格式化本地时间／日期 |
| 2751 |     [strip_tags][2908] | 从字符串中去除 HTML 和 PHP 标记 |
| 2752 |     [stripcslashes][2909] | 反引用一个使用           [addcslashes()][2910]转义的字符串 |
| 2753 |     [stripos][2911] | 查找字符串首次出现的位置（不区分大小写） |
| 2754 |     [stripslashes][2912] | 反引用一个引用字符串 |
| 2755 |     [stristr][2913] | [strstr()][2857]函数的忽略大小写版本 |
| 2756 |     [strlen][2915] | 获取字符串长度 |
| 2757 |     [strnatcasecmp][2916] | 使用“自然顺序”算法比较字符串（不区分大小写） |
| 2758 |     [strnatcmp][2917] | 使用自然排序算法比较字符串 |
| 2759 |     [strncasecmp][2918] | 二进制安全比较字符串开头的若干个字符（不区分大小写） |
| 2760 |     [strncmp][2919] | 二进制安全比较字符串开头的若干个字符 |
| 2761 |     [strpbrk][2920] | 在字符串中查找一组字符的任何一个字符 |
| 2762 |     [strpos][2921] | 查找字符串首次出现的位置 |
| 2763 |     [strptime][2922] | 解析由   [strftime()][2923]生成的日期／时间 |
| 2764 |     [strrchr][2924] | 查找指定字符在字符串中的最后一次出现 |
| 2765 |     [strrev][2925] | 反转字符串 |
| 2766 |     [strripos][2926] | 计算指定字符串在目标字符串中最后一次出现的位置（不区分大小写） |
| 2767 |     [strrpos][2927] | 计算指定字符串在目标字符串中最后一次出现的位置 |
| 2768 |     [strspn][2928] | 计算字符串中全部字符都存在于指定字符集合中的第一段子串的长度。 |
| 2769 |     [strstr][2929] | 查找字符串的首次出现 |
| 2770 |     [strtok][2930] | 标记分割字符串 |
| 2771 |     [strtolower][2931] | 将字符串转化为小写 |
| 2772 |     [strtotime][2932] | 将任何字符串的日期时间描述解析为 Unix 时间戳 |
| 2773 |     [strtoupper][2933] | 将字符串转化为大写 |
| 2774 |     [strtr][2934] | 转换指定字符 |
| 2775 |     [strval][2935] | 获取变量的字符串值 |
| 2776 |     [substr][2936] | 返回字符串的子串 |
| 2777 |     [substr_compare][2937] | 二进制安全比较字符串（从偏移位置比较指定长度） |
| 2778 |     [substr_count][2938] | 计算字串出现的次数 |
| 2779 |     [substr_replace][2939] | 替换字符串的子串 |
| 2780 |     [svn_add][2940] | 计划在工作目录添加项 |
| 2781 |     [svn_auth_get_parameter][2941] | Retrieves authentication parameter |
| 2782 |     [svn_auth_set_parameter][2942] | Sets an authentication parameter |
| 2783 |     [svn_blame][2943] | Get the SVN blame for a file |
| 2784 |     [svn_cat][2944] | Returns the contents of a file in a repository |
| 2785 |     [svn_checkout][2945] | Checks out a working copy from the repository |
| 2786 |     [svn_cleanup][2946] | Recursively cleanup a working copy directory, finishing incomplete operations and removing locks |
| 2787 |     [svn_client_version][2947] | Returns the version of the SVN client libraries |
| 2788 |     [svn_commit][2948] | 将修改的本地文件副本发送至版本库 |
| 2789 |     [svn_delete][2949] | Delete items from a working copy or repository |
| 2790 |     [svn_diff][2950] | Recursively diffs two paths |
| 2791 |     [svn_export][2951] | Export the contents of a SVN directory |
| 2792 |     [svn_fs_abort_txn][2952] | Abort a transaction, returns true if everything is okay, false otherwise |
| 2793 |     [svn_fs_apply_text][2953] | Creates and returns a stream that will be used to replace |
| 2794 |     [svn_fs_begin_txn2][2954] | Create a new transaction |
| 2795 |     [svn_fs_change_node_prop][2955] | Return true if everything is ok, false otherwise |
| 2796 |     [svn_fs_check_path][2956] | Determines what kind of item lives at path in a given repository fsroot |
| 2797 |     [svn_fs_contents_changed][2957] | Return true if content is different, false otherwise |
| 2798 |     [svn_fs_copy][2958] | Copies a file or a directory, returns true if all is ok, false otherwise |
| 2799 |     [svn_fs_delete][2959] | Deletes a file or a directory, return true if all is ok, false otherwise |
| 2800 |     [svn_fs_dir_entries][2960] | Enumerates the directory entries under path; returns a hash of dir names to file type |
| 2801 |     [svn_fs_file_contents][2961] | Returns a stream to access the contents of a file from a given version of the fs |
| 2802 |     [svn_fs_file_length][2962] | Returns the length of a file from a given version of the fs |
| 2803 |     [svn_fs_is_dir][2963] | Return true if the path points to a directory, false otherwise |
| 2804 |     [svn_fs_is_file][2964] | Return true if the path points to a file, false otherwise |
| 2805 |     [svn_fs_make_dir][2965] | Creates a new empty directory, returns true if all is ok, false otherwise |
| 2806 |     [svn_fs_make_file][2966] | Creates a new empty file, returns true if all is ok, false otherwise |
| 2807 |     [svn_fs_node_created_rev][2967] | Returns the revision in which path under fsroot was created |
| 2808 |     [svn_fs_node_prop][2968] | Returns the value of a property for a node |
| 2809 |     [svn_fs_props_changed][2969] | Return true if props are different, false otherwise |
| 2810 |     [svn_fs_revision_prop][2970] | Fetches the value of a named property |
| 2811 |     [svn_fs_revision_root][2971] | Get a handle on a specific version of the repository root |
| 2812 |     [svn_fs_txn_root][2972] | Creates and returns a transaction root |
| 2813 |     [svn_fs_youngest_rev][2973] | Returns the number of the youngest revision in the filesystem |
| 2814 |     [svn_import][2974] | Imports an unversioned path into a repository |
| 2815 |     [svn_log][2975] | Returns the commit log messages of a repository URL |
| 2816 |     [svn_ls][2976] | Returns list of directory contents in repository URL, optionally at revision number |
| 2817 |     [svn_mkdir][2977] | Creates a directory in a working copy or repository |
| 2818 |     [svn_repos_create][2978] | Create a new subversion repository at path |
| 2819 |     [svn_repos_fs][2979] | Gets a handle on the filesystem for a repository |
| 2820 |     [svn_repos_fs_begin_txn_for_commit][2980] | Create a new transaction |
| 2821 |     [svn_repos_fs_commit_txn][2981] | Commits a transaction and returns the new revision |
| 2822 |     [svn_repos_hotcopy][2982] | Make a hot-copy of the repos at repospath; copy it to destpath |
| 2823 |     [svn_repos_open][2983] | Open a shared lock on a repository |
| 2824 |     [svn_repos_recover][2984] | Run recovery procedures on the repository located at path |
| 2825 |     [svn_revert][2985] | Revert changes to the working copy |
| 2826 |     [svn_status][2986] | Returns the status of working copy files and directories |
| 2827 |     [svn_update][2987] | Update working copy |
| 2828 |     [sybase_affected_rows][2988] | Gets number of affected rows in last query |
| 2829 |     [sybase_close][2989] | Closes a Sybase connection |
| 2830 |     [sybase_connect][2990] | Opens a Sybase server connection |
| 2831 |     [sybase_data_seek][2991] | Moves internal row pointer |
| 2832 |     [sybase_deadlock_retry_count][2992] | Sets the deadlock retry count |
| 2833 |     [sybase_fetch_array][2993] | Fetch row as array |
| 2834 |     [sybase_fetch_assoc][2994] | Fetch a result row as an associative array |
| 2835 |     [sybase_fetch_field][2995] | Get field information from a result |
| 2836 |     [sybase_fetch_object][2996] | Fetch a row as an object |
| 2837 |     [sybase_fetch_row][2997] | Get a result row as an enumerated array |
| 2838 |     [sybase_field_seek][2998] | Sets field offset |
| 2839 |     [sybase_free_result][2999] | Frees result memory |
| 2840 |     [sybase_get_last_message][3000] | Returns the last message from the server |
| 2841 |     [sybase_min_client_severity][3001] | Sets minimum client severity |
| 2842 |     [sybase_min_server_severity][3002] | Sets minimum server severity |
| 2843 |     [sybase_num_fields][3003] | Gets the number of fields in a result set |
| 2844 |     [sybase_num_rows][3004] | Get number of rows in a result set |
| 2845 |     [sybase_pconnect][3005] | Open persistent Sybase connection |
| 2846 |     [sybase_query][3006] | Sends a Sybase query |
| 2847 |     [sybase_result][3007] | Get result data |
| 2848 |     [sybase_select_db][3008] | Selects a Sybase database |
| 2849 |     [sybase_set_message_handler][3009] | Sets the handler called when a server message is raised |
| 2850 |     [sybase_unbuffered_query][3010] | Send a Sybase query and do not block |
| 2851 |     [symlink][3011] | 建立符号连接 |
| 2852 |     [sys_get_temp_dir][3012] | 返回用于临时文件的目录 |
| 2853 |     [sys_getloadavg][3013] | 获取系统的负载（load average） |
| 2854 |     [syslog][3014] | Generate a system log message |
| 2855 |     [system][3015] | 执行外部程序，并且显示输出 |

| t |-|-|
|-|-|-|
| 2856 |     [tanh][3016] | 双曲正切 |
| 2857 |     [tempnam][3017] | 建立一个具有唯一文件名的文件 |
| 2858 |     [textdomain][3018] | Sets the default domain |
| 2859 |     [tidy_access_count][3019] | Returns the Number of Tidy accessibility warnings encountered for specified document |
| 2860 |     [tidy_config_count][3020] | Returns the Number of Tidy configuration errors encountered for specified document |
| 2861 |     [tidy_diagnose][3021] | Run configured diagnostics on parsed and repaired markup |
| 2862 |     [tidy_error_count][3022] | Returns the Number of Tidy errors encountered for specified document |
| 2863 |     [tidy_get_output][3023] | Return a string representing the parsed tidy markup |
| 2864 |     [tidy_getopt][3024] | Returns the value of the specified configuration option for the tidy document |
| 2865 |     [tidy_warning_count][3025] | Returns the Number of Tidy warnings encountered for specified document |
| 2866 |     [time][3026] | 返回当前的 Unix 时间戳 |
| 2867 |     [time_nanosleep][3027] | 延缓执行若干秒和纳秒 |
| 2868 |     [time_sleep_until][3028] | 使脚本睡眠到指定的时间为止。 |
| 2869 |     [timezone_abbreviations_list][3029] | 别名          [DateTimeZone::listAbbreviations()][3030] |
| 2870 |     [timezone_identifiers_list][3031] | 别名          [DateTimeZone::listIdentifiers()][3032] |
| 2871 |     [timezone_location_get][3033] | 别名          [DateTimeZone::getLocation()][3034] |
| 2872 |     [timezone_name_from_abbr][3035] | Returns the timezone name from abbreviation |
| 2873 |     [timezone_name_get][3036] | 别名          [DateTimeZone::getName()][3037] |
| 2874 |     [timezone_offset_get][3038] | 别名          [DateTimeZone::getOffset()][3039] |
| 2875 |     [timezone_open][3040] | 别名          [DateTimeZone::__construct()][3041] |
| 2876 |     [timezone_transitions_get][3042] | 别名          [DateTimeZone::getTransitions()][3043] |
| 2877 |     [timezone_version_get][3044] | Gets the version of the timezonedb |
| 2878 |     [tmpfile][3045] | 建立一个临时文件 |
| 2879 |     [token_get_all][3046] | 将提供的源码按 PHP 标记进行分割 |
| 2880 |     [token_name][3047] | 获取提供的 PHP 解析器代号的符号名称 |
| 2881 |     [touch][3048] | 设定文件的访问和修改时间 |
| 2882 |     [trait_exists][3049] | 检查指定的 trait 是否存在 |
| 2883 |     [transliterator::createinverse][3050] | Create an inverse transliterator |
| 2884 |     [transliterator::geterrorcode][3051] | Get last error code |
| 2885 |     [transliterator::geterrormessage][3052] | Get last error message |
| 2886 |     [transliterator::transliterate][3053] | Transliterate a string |
| 2887 |     [transliterator_create][3054] | Create a transliterator |
| 2888 |     [transliterator_transliterate][3055] | Transliterate a string |
| 2889 |     [trigger_error][3056] | 产生一个用户级别的 error/warning/notice 信息 |
| 2890 |     [trim][3057] | 去除字符串首尾处的空白字符（或者其他字符） |

| u |-|-|
|-|-|-|
| 2891 |     [ucfirst][3058] | 将字符串的首字母转换为大写 |
| 2892 |     [uconverter::convert][3059] | Convert string from one charset to another |
| 2893 |     [uconverter::fromucallback][3060] | Default "from" callback function |
| 2894 |     [uconverter::getdestinationencoding][3061] | Get the destination encoding |
| 2895 |     [uconverter::getdestinationtype][3062] | Get the destination converter type |
| 2896 |     [uconverter::geterrorcode][3063] | Get last error code on the object |
| 2897 |     [uconverter::geterrormessage][3064] | Get last error message on the object |
| 2898 |     [uconverter::getsourceencoding][3065] | Get the source encoding |
| 2899 |     [uconverter::getsourcetype][3066] | Get the source convertor type |
| 2900 |     [uconverter::getsubstchars][3067] | Get substitution chars |
| 2901 |     [uconverter::setdestinationencoding][3068] | Set the destination encoding |
| 2902 |     [uconverter::setsourceencoding][3069] | Set the source encoding |
| 2903 |     [uconverter::setsubstchars][3070] | Set the substitution chars |
| 2904 |     [uconverter::toucallback][3071] | Default "to" callback function |
| 2905 |     [ucwords][3072] | 将字符串中每个单词的首字母转换为大写 |
| 2906 |     [uksort][3073] | 使用用户自定义的比较函数对数组中的键名进行排序 |
| 2907 |     [umask][3074] | 改变当前的 umask |
| 2908 |     [uniqid][3075] | 生成一个唯一ID |
| 2909 |     [unixtojd][3076] | 转变Unix时间戳为Julian Day计数 |
| 2910 |     [unlink][3077] | 删除文件 |
| 2911 |     [unpack][3078] | Unpack data from binary string |
| 2912 |     [unregister_tick_function][3079] | De-register a function for execution on each tick |
| 2913 |     [unserialize][3080] | 从已存储的表示中创建 PHP 的值 |
| 2914 |     [urldecode][3081] | 解码已编码的 URL 字符串 |
| 2915 |     [urlencode][3082] | 编码 URL 字符串 |
| 2916 |     [use_soap_error_handler][3083] | Set whether to use the SOAP error handler |
| 2917 |     [user_error][3084] |  [trigger_error()][3085]的别名 |
| 2918 |     [usleep][3086] | 以指定的微秒数延迟执行 |
| 2919 |     [usort][3087] | 使用用户自定义的比较函数对数组中的值进行排序 |
| 2920 |     [utf8_decode][3088] | 将用 UTF-8 方式编码的 ISO-8859-1 字符串转换成单字节的 ISO-8859-1 字符串。 |
| 2921 |     [utf8_encode][3089] | 将 ISO-8859-1 编码的字符串转换为 UTF-8 编码 |

| v |-|-|
|-|-|-|
| 2922 |     [var_export][3090] | 输出或返回一个变量的字符串表示 |
| 2923 |     [version_compare][3091] | 对比两个「PHP 规范化」的版本数字字符串 |
| 2924 |     [vfprintf][3092] | 将格式化字符串写入流 |
| 2925 |     [virtual][3093] | 执行 Apache 子请求 |
| 2926 |     [vprintf][3094] | 输出格式化字符串 |
| 2927 |     [vsprintf][3095] | 返回格式化字符串 |

| w |-|-|
|-|-|-|
| 2928 |     [wddx_deserialize][3096] | Unserializes a WDDX packet |
| 2929 |     [wddx_packet_end][3097] | Ends a WDDX packet with the specified ID |
| 2930 |     [wddx_packet_start][3098] | Starts a new WDDX packet with structure inside it |
| 2931 |     [wddx_serialize_value][3099] | Serialize a single value into a WDDX packet |
| 2932 |     [wddx_serialize_vars][3100] | Serialize variables into a WDDX packet |
| 2933 |     [wincache_fcache_fileinfo][3101] | Retrieves information about files cached in the file cache |
| 2934 |     [wincache_fcache_meminfo][3102] | Retrieves information about file cache memory usage |
| 2935 |     [wincache_lock][3103] | Acquires an exclusive lock on a given key |
| 2936 |     [wincache_ocache_fileinfo][3104] | Retrieves information about files cached in the opcode cache |
| 2937 |     [wincache_ocache_meminfo][3105] | Retrieves information about opcode cache memory usage |
| 2938 |     [wincache_refresh_if_changed][3106] | Refreshes the cache entries for the cached files |
| 2939 |     [wincache_rplist_fileinfo][3107] | Retrieves information about resolve file path cache |
| 2940 |     [wincache_rplist_meminfo][3108] | Retrieves information about memory usage by the resolve file path cache |
| 2941 |     [wincache_scache_info][3109] | Retrieves information about files cached in the session cache |
| 2942 |     [wincache_scache_meminfo][3110] | Retrieves information about session cache memory usage |
| 2943 |     [wincache_ucache_add][3111] | Adds a variable in user cache only if variable does not already exist in the cache |
| 2944 |     [wincache_ucache_cas][3112] | Compares the variable with old value and assigns new value to it |
| 2945 |     [wincache_ucache_clear][3113] | Deletes entire content of the user cache |
| 2946 |     [wincache_ucache_dec][3114] | Decrements the value associated with the key |
| 2947 |     [wincache_ucache_delete][3115] | Deletes variables from the user cache |
| 2948 |     [wincache_ucache_exists][3116] | Checks if a variable exists in the user cache |
| 2949 |     [wincache_ucache_get][3117] | Gets a variable stored in the user cache |
| 2950 |     [wincache_ucache_inc][3118] | Increments the value associated with the key |
| 2951 |     [wincache_ucache_info][3119] | Retrieves information about data stored in the user cache |
| 2952 |     [wincache_ucache_meminfo][3120] | Retrieves information about user cache memory usage |
| 2953 |     [wincache_ucache_set][3121] | Adds a variable in user cache and overwrites a variable if it already exists in the cache |
| 2954 |     [wincache_unlock][3122] | Releases an exclusive lock on a given key |
| 2955 |     [wordwrap][3123] | 打断字符串为指定数量的字串 |

| x |-|-|
|-|-|-|
| 2956 |     [xhprof_enable][3124] | 启动 xhprof 性能分析器 |
| 2957 |     [xhprof_sample_disable][3125] | 停止 xhprof 性能采样分析器 |
| 2958 |     [xhprof_sample_enable][3126] | 以采样模式启动 XHProf 性能分析 |
| 2959 |     [xml_error_string][3127] | 获取 XML 解析器的错误字符串 |
| 2960 |     [xml_get_current_byte_index][3128] | 获取 XML 解析器的当前字节索引 |
| 2961 |     [xml_get_current_column_number][3129] | 获取 XML 解析器的当前列号 |
| 2962 |     [xml_get_current_line_number][3130] | 获取 XML 解析器的当前行号 |
| 2963 |     [xml_get_error_code][3131] | 获取 XML 解析器错误代码 |
| 2964 |     [xml_parse][3132] | 开始解析一个 XML 文档 |
| 2965 |     [xml_parse_into_struct][3133] | 将 XML 数据解析到数组中 |
| 2966 |     [xml_parser_create][3134] | 建立一个 XML 解析器 |
| 2967 |     [xml_parser_create_ns][3135] | 生成一个支持命名空间的 XML 解析器 |
| 2968 |     [xml_parser_free][3136] | 释放指定的 XML 解析器 |
| 2969 |     [xml_parser_get_option][3137] | 从 XML 解析器获取选项设置信息 |
| 2970 |     [xml_parser_set_option][3138] | 为指定 XML 解析进行选项设置 |
| 2971 |     [xml_set_character_data_handler][3139] | 建立字符数据处理器 |
| 2972 |     [xml_set_default_handler][3140] | 建立默认处理器 |
| 2973 |     [xml_set_element_handler][3141] | 建立起始和终止元素处理器 |
| 2974 |     [xml_set_end_namespace_decl_handler][3142] | 建立终止命名空间声明处理器 |
| 2975 |     [xml_set_external_entity_ref_handler][3143] | 建立外部实体指向处理器 |
| 2976 |     [xml_set_notation_decl_handler][3144] | 建立注释声明处理器 |
| 2977 |     [xml_set_object][3145] | 在对象中使用 XML 解析器 |
| 2978 |     [xml_set_processing_instruction_handler][3146] | 建立处理指令（PI）处理器 |
| 2979 |     [xml_set_start_namespace_decl_handler][3147] | 建立起始命名空间声明处理器 |
| 2980 |     [xml_set_unparsed_entity_decl_handler][3148] | 建立未解析实体定义声明处理器 |
| 2981 |     [xmlrpc_decode][3149] | 将 XML 译码为 PHP 本身的类型 |
| 2982 |     [xmlrpc_decode_request][3150] | 将 XML 译码为 PHP 本身的类型 |
| 2983 |     [xmlrpc_encode][3151] | 为 PHP 的值生成 XML |
| 2984 |     [xmlrpc_encode_request][3152] | 为 PHP 的值生成 XML |
| 2985 |     [xmlrpc_get_type][3153] | 为 PHP 的值获取 xmlrpc 的类型 |
| 2986 |     [xmlrpc_is_fault][3154] | Determines if an array value represents an XMLRPC fault |
| 2987 |     [xmlrpc_parse_method_descriptions][3155] | 将 XML 译码成方法描述的列表 |
| 2988 |     [xmlrpc_server_add_introspection_data][3156] | 添加自我描述的文档 |
| 2989 |     [xmlrpc_server_call_method][3157] | 解析 XML 请求同时调用方法 |
| 2990 |     [xmlrpc_server_create][3158] | 创建一个 xmlrpc 服务端 |
| 2991 |     [xmlrpc_server_destroy][3159] | 销毁服务端资源 |
| 2992 |     [xmlrpc_server_register_introspection_callback][3160] | 注册一个 PHP 函数用于生成文档 |
| 2993 |     [xmlrpc_server_register_method][3161] | 注册一个 PHP 函数用于匹配 xmlrpc 方法名 |
| 2994 |     [xmlrpc_set_type][3162] | 为一个 PHP 字符串值设置 xmlrpc 的类型、base64 或日期时间 |
| 2995 |     [xmlwriter_end_attribute][3163] | End attribute |
| 2996 |     [xmlwriter_end_cdata][3164] | End current CDATA |
| 2997 |     [xmlwriter_end_comment][3165] | Create end comment |
| 2998 |     [xmlwriter_end_document][3166] | End current document |
| 2999 |     [xmlwriter_end_dtd][3167] | End current DTD |
| 3000 |     [xmlwriter_end_dtd_attlist][3168] | End current DTD AttList |
| 3001 |     [xmlwriter_end_dtd_element][3169] | End current DTD element |
| 3002 |     [xmlwriter_end_dtd_entity][3170] | End current DTD Entity |
| 3003 |     [xmlwriter_end_element][3171] | End current element |
| 3004 |     [xmlwriter_end_pi][3172] | End current PI |
| 3005 |     [xmlwriter_flush][3173] | Flush current buffer |
| 3006 |     [xmlwriter_full_end_element][3174] | End current element |
| 3007 |     [xmlwriter_open_memory][3175] | Create new xmlwriter using memory for string output |
| 3008 |     [xmlwriter_open_uri][3176] | Create new xmlwriter using source uri for output |
| 3009 |     [xmlwriter_output_memory][3177] | Returns current buffer |
| 3010 |     [xmlwriter_set_indent][3178] | Toggle indentation on/off |
| 3011 |     [xmlwriter_set_indent_string][3179] | Set string used for indenting |
| 3012 |     [xmlwriter_start_attribute][3180] | Create start attribute |
| 3013 |     [xmlwriter_start_attribute_ns][3181] | Create start namespaced attribute |
| 3014 |     [xmlwriter_start_cdata][3182] | Create start CDATA tag |
| 3015 |     [xmlwriter_start_comment][3183] | Create start comment |
| 3016 |     [xmlwriter_start_document][3184] | Create document tag |
| 3017 |     [xmlwriter_start_dtd][3185] | Create start DTD tag |
| 3018 |     [xmlwriter_start_dtd_attlist][3186] | Create start DTD AttList |
| 3019 |     [xmlwriter_start_dtd_element][3187] | Create start DTD element |
| 3020 |     [xmlwriter_start_dtd_entity][3188] | Create start DTD Entity |
| 3021 |     [xmlwriter_start_element][3189] | Create start element tag |
| 3022 |     [xmlwriter_start_element_ns][3190] | Create start namespaced element tag |
| 3023 |     [xmlwriter_start_pi][3191] | Create start PI tag |
| 3024 |     [xmlwriter_text][3192] | Write text |
| 3025 |     [xmlwriter_write_attribute][3193] | Write full attribute |
| 3026 |     [xmlwriter_write_attribute_ns][3194] | Write full namespaced attribute |
| 3027 |     [xmlwriter_write_cdata][3195] | Write full CDATA tag |
| 3028 |     [xmlwriter_write_comment][3196] | Write full comment tag |
| 3029 |     [xmlwriter_write_dtd][3197] | Write full DTD tag |
| 3030 |     [xmlwriter_write_dtd_attlist][3198] | Write full DTD AttList tag |
| 3031 |     [xmlwriter_write_dtd_element][3199] | Write full DTD element tag |
| 3032 |     [xmlwriter_write_dtd_entity][3200] | Write full DTD Entity tag |
| 3033 |     [xmlwriter_write_element][3201] | Write full element tag |
| 3034 |     [xmlwriter_write_element_ns][3202] | Write full namespaced element tag |
| 3035 |     [xmlwriter_write_pi][3203] | Writes a PI |
| 3036 |     [xmlwriter_write_raw][3204] | Write a raw XML text |

| y |-|-|
|-|-|-|
| 3037 |     [yaml_emit_file][3205] | Send the YAML representation of a value to a file |
| 3038 |     [yaml_parse][3206] | Parse a YAML stream |
| 3039 |     [yaml_parse_file][3207] | Parse a YAML stream from a file |
| 3040 |     [yaml_parse_url][3208] | Parse a Yaml stream from a URL |

| z |-|-|
|-|-|-|
| 3041 |     [zend_version][3209] | 获取当前 Zend 引擎的版本 |
| 3042 |     [zip_close][3210] | 关闭一个ZIP档案文件 |
| 3043 |     [zip_entry_close][3211] | 关闭目录项 |
| 3044 |     [zip_entry_compressedsize][3212] | 检索目录项压缩过后的大小 |
| 3045 |     [zip_entry_compressionmethod][3213] | 检索目录实体的压缩方法 |
| 3046 |     [zip_entry_filesize][3214] | 检索目录实体的实际大小 |
| 3047 |     [zip_entry_name][3215] | 检索目录项的名称 |
| 3048 |     [zip_entry_open][3216] | 打开用于读取的目录实体 |
| 3049 |     [zip_entry_read][3217] | 读取一个打开了的压缩目录实体 |
| 3050 |     [zip_open][3218] | 打开ZIP存档文件 |
| 3051 |     [zip_read][3219] | 读取ZIP存档文件中下一项 |
| 3052 |     [ziparchive::setpassword][3220] | Set the password for the active archive |
| 3053 |     [zlib_decode][3221] | Uncompress any raw/gzip/zlib encoded data |
| 3054 |     [zlib_encode][3222] | Compress data with the specified encoding |
| 3055 |     [zlib_get_coding_type][3223] | Returns the coding type used for output compression |
  



[0]: http://php.net/manual/zh/function.acos.php
[1]: http://php.net/manual/zh/function.acosh.php
[2]: http://php.net/manual/zh/function.addcslashes.php
[3]: http://php.net/manual/zh/function.addslashes.php
[4]: http://php.net/manual/zh/function.apache-child-terminate.php
[5]: http://php.net/manual/zh/function.apache-get-modules.php
[6]: http://php.net/manual/zh/function.apache-get-version.php
[7]: http://php.net/manual/zh/function.apache-getenv.php
[8]: http://php.net/manual/zh/function.apache-lookup-uri.php
[9]: http://php.net/manual/zh/function.apache-note.php
[10]: http://php.net/manual/zh/function.apache-request-headers.php
[11]: http://php.net/manual/zh/function.apache-reset-timeout.php
[12]: http://php.net/manual/zh/function.apache-response-headers.php
[13]: http://php.net/manual/zh/function.apache-setenv.php
[14]: http://php.net/manual/zh/function.apc-add.php
[15]: http://php.net/manual/zh/function.apc-bin-dump.php
[16]: http://php.net/manual/zh/function.apc-bin-dumpfile.php
[17]: http://php.net/manual/zh/function.apc-bin-load.php
[18]: http://php.net/manual/zh/function.apc-bin-loadfile.php
[19]: http://php.net/manual/zh/function.apc-cache-info.php
[20]: http://php.net/manual/zh/function.apc-cas.php
[21]: http://php.net/manual/zh/function.apc-clear-cache.php
[22]: http://php.net/manual/zh/function.apc-compile-file.php
[23]: http://php.net/manual/zh/function.apc-dec.php
[24]: http://php.net/manual/zh/function.apc-define-constants.php
[25]: http://php.net/manual/zh/function.apc-delete.php
[26]: http://php.net/manual/zh/function.apc-delete-file.php
[27]: http://php.net/manual/zh/function.apc-exists.php
[28]: http://php.net/manual/zh/function.apc-fetch.php
[29]: http://php.net/manual/zh/function.apc-inc.php
[30]: http://php.net/manual/zh/function.apc-load-constants.php
[31]: http://php.net/manual/zh/function.apc-sma-info.php
[32]: http://php.net/manual/zh/function.apc-store.php
[33]: http://php.net/manual/zh/function.apcu-add.php
[34]: http://php.net/manual/zh/function.apcu-cache-info.php
[35]: http://php.net/manual/zh/function.apcu-cas.php
[36]: http://php.net/manual/zh/function.apcu-clear-cache.php
[37]: http://php.net/manual/zh/function.apcu-dec.php
[38]: http://php.net/manual/zh/function.apcu-delete.php
[39]: http://php.net/manual/zh/function.apcu-entry.php
[40]: http://php.net/manual/zh/function.apcu-exists.php
[41]: http://php.net/manual/zh/function.apcu-fetch.php
[42]: http://php.net/manual/zh/function.apcu-inc.php
[43]: http://php.net/manual/zh/function.apcu-sma-info.php
[44]: http://php.net/manual/zh/function.apcu-store.php
[45]: http://php.net/manual/zh/function.array-change-key-case.php
[46]: http://php.net/manual/zh/function.array-chunk.php
[47]: http://php.net/manual/zh/function.array-column.php
[48]: http://php.net/manual/zh/function.array-combine.php
[49]: http://php.net/manual/zh/function.array-count-values.php
[50]: http://php.net/manual/zh/function.array-diff.php
[51]: http://php.net/manual/zh/function.array-diff-assoc.php
[52]: http://php.net/manual/zh/function.array-diff-key.php
[53]: http://php.net/manual/zh/function.array-diff-uassoc.php
[54]: http://php.net/manual/zh/function.array-diff-ukey.php
[55]: http://php.net/manual/zh/function.array-fill.php
[56]: http://php.net/manual/zh/function.array-fill-keys.php
[57]: http://php.net/manual/zh/function.array-filter.php
[58]: http://php.net/manual/zh/function.array-flip.php
[59]: http://php.net/manual/zh/function.array-intersect.php
[60]: http://php.net/manual/zh/function.array-intersect-assoc.php
[61]: http://php.net/manual/zh/function.array-intersect-key.php
[62]: http://php.net/manual/zh/function.array-intersect-uassoc.php
[63]: http://php.net/manual/zh/function.array-intersect-ukey.php
[64]: http://php.net/manual/zh/function.array-key-exists.php
[65]: http://php.net/manual/zh/function.array-keys.php
[66]: http://php.net/manual/zh/function.array-map.php
[67]: http://php.net/manual/zh/function.array-merge.php
[68]: http://php.net/manual/zh/function.array-merge-recursive.php
[69]: http://php.net/manual/zh/function.array-multisort.php
[70]: http://php.net/manual/zh/function.array-pad.php
[71]: http://php.net/manual/zh/function.array-pop.php
[72]: http://php.net/manual/zh/function.array-product.php
[73]: http://php.net/manual/zh/function.array-push.php
[74]: http://php.net/manual/zh/function.array-rand.php
[75]: http://php.net/manual/zh/function.array-reduce.php
[76]: http://php.net/manual/zh/function.array-replace.php
[77]: http://php.net/manual/zh/function.array-replace-recursive.php
[78]: http://php.net/manual/zh/function.array-reverse.php
[79]: http://php.net/manual/zh/function.array-search.php
[80]: http://php.net/manual/zh/function.array-shift.php
[81]: http://php.net/manual/zh/function.array-slice.php
[82]: http://php.net/manual/zh/function.array-splice.php
[83]: http://php.net/manual/zh/function.array-sum.php
[84]: http://php.net/manual/zh/function.array-udiff.php
[85]: http://php.net/manual/zh/function.array-udiff-assoc.php
[86]: http://php.net/manual/zh/function.array-udiff-uassoc.php
[87]: http://php.net/manual/zh/function.array-uintersect.php
[88]: http://php.net/manual/zh/function.array-uintersect-assoc.php
[89]: http://php.net/manual/zh/function.array-uintersect-uassoc.php
[90]: http://php.net/manual/zh/function.array-unique.php
[91]: http://php.net/manual/zh/function.array-unshift.php
[92]: http://php.net/manual/zh/function.array-values.php
[93]: http://php.net/manual/zh/function.array-walk.php
[94]: http://php.net/manual/zh/function.array-walk-recursive.php
[95]: http://php.net/manual/zh/function.arsort.php
[96]: http://php.net/manual/zh/function.asin.php
[97]: http://php.net/manual/zh/function.asinh.php
[98]: http://php.net/manual/zh/function.asort.php
[99]: http://php.net/manual/zh/function.assert.php
[100]: http://php.net/manual/zh/function.assert-options.php
[101]: http://php.net/manual/zh/function.atan.php
[102]: http://php.net/manual/zh/function.atan2.php
[103]: http://php.net/manual/zh/function.atanh.php
[104]: http://php.net/manual/zh/function.base64-encode.php
[105]: http://php.net/manual/zh/function.base-convert.php
[106]: http://php.net/manual/zh/function.basename.php
[107]: http://php.net/manual/zh/function.bcadd.php
[108]: http://php.net/manual/zh/function.bccomp.php
[109]: http://php.net/manual/zh/function.bcdiv.php
[110]: http://php.net/manual/zh/function.bcmod.php
[111]: http://php.net/manual/zh/function.bcmul.php
[112]: http://php.net/manual/zh/function.bcpow.php
[113]: http://php.net/manual/zh/function.bcpowmod.php
[114]: http://php.net/manual/zh/function.bcscale.php
[115]: http://php.net/manual/zh/function.bcsqrt.php
[116]: http://php.net/manual/zh/function.bcsub.php
[117]: http://php.net/manual/zh/function.bin2hex.php
[118]: http://php.net/manual/zh/function.bind-textdomain-codeset.php
[119]: http://php.net/manual/zh/function.bindec.php
[120]: http://php.net/manual/zh/function.bindtextdomain.php
[121]: http://php.net/manual/zh/function.boolval.php
[122]: http://php.net/manual/zh/function.bzclose.php
[123]: http://php.net/manual/zh/function.bzcompress.php
[124]: http://php.net/manual/zh/function.bzdecompress.php
[125]: http://php.net/manual/zh/function.bzerrno.php
[126]: http://php.net/manual/zh/function.bzerror.php
[127]: http://php.net/manual/zh/function.bzerrstr.php
[128]: http://php.net/manual/zh/function.bzflush.php
[129]: http://php.net/manual/zh/function.bzopen.php
[130]: http://php.net/manual/zh/function.bzread.php
[131]: http://php.net/manual/zh/function.bzwrite.php
[132]: http://php.net/manual/zh/function.cal-from-jd.php
[133]: http://php.net/manual/zh/function.cal-info.php
[134]: http://php.net/manual/zh/function.cal-to-jd.php
[135]: http://php.net/manual/zh/function.call-user-func.php
[136]: http://php.net/manual/zh/function.call-user-func-array.php
[137]: http://php.net/manual/zh/function.call-user-method.php
[138]: http://php.net/manual/zh/function.call-user-method-array.php
[139]: http://php.net/manual/zh/function.ceil.php
[140]: http://php.net/manual/zh/function.chdir.php
[141]: http://php.net/manual/zh/function.checkdate.php
[142]: http://php.net/manual/zh/function.checkdnsrr.php
[143]: http://php.net/manual/zh/function.chgrp.php
[144]: http://php.net/manual/zh/function.chmod.php
[145]: http://php.net/manual/zh/function.chop.php
[146]: http://php.net/manual/zh/function.rtrim.php
[147]: http://php.net/manual/zh/function.chown.php
[148]: http://php.net/manual/zh/function.chr.php
[149]: http://php.net/manual/zh/function.chroot.php
[150]: http://php.net/manual/zh/function.chunk-split.php
[151]: http://php.net/manual/zh/function.class-alias.php
[152]: http://php.net/manual/zh/function.class-exists.php
[153]: http://php.net/manual/zh/function.class-implements.php
[154]: http://php.net/manual/zh/function.class-parents.php
[155]: http://php.net/manual/zh/function.class-uses.php
[156]: http://php.net/manual/zh/function.clearstatcache.php
[157]: http://php.net/manual/zh/function.cli-get-process-title.php
[158]: http://php.net/manual/zh/function.cli-set-process-title.php
[159]: http://php.net/manual/zh/function.closedir.php
[160]: http://php.net/manual/zh/function.closelog.php
[161]: http://php.net/manual/zh/collator.asort.php
[162]: http://php.net/manual/zh/collator.compare.php
[163]: http://php.net/manual/zh/collator.getattribute.php
[164]: http://php.net/manual/zh/collator.geterrorcode.php
[165]: http://php.net/manual/zh/collator.geterrormessage.php
[166]: http://php.net/manual/zh/collator.getlocale.php
[167]: http://php.net/manual/zh/collator.getsortkey.php
[168]: http://php.net/manual/zh/collator.getstrength.php
[169]: http://php.net/manual/zh/collator.setattribute.php
[170]: http://php.net/manual/zh/collator.setstrength.php
[171]: http://php.net/manual/zh/collator.sort.php
[172]: http://php.net/manual/zh/collator.sortwithsortkeys.php
[173]: http://php.net/manual/zh/function.collator-asort.php
[174]: http://php.net/manual/zh/function.collator-compare.php
[175]: http://php.net/manual/zh/function.collator-create.php
[176]: http://php.net/manual/zh/function.collator-sort.php
[177]: http://php.net/manual/zh/function.compact.php
[178]: http://php.net/manual/zh/function.connection-aborted.php
[179]: http://php.net/manual/zh/function.connection-status.php
[180]: http://php.net/manual/zh/function.constant.php
[181]: http://php.net/manual/zh/function.convert-cyr-string.php
[182]: http://php.net/manual/zh/function.convert-uudecode.php
[183]: http://php.net/manual/zh/function.convert-uuencode.php
[184]: http://php.net/manual/zh/function.copy.php
[185]: http://php.net/manual/zh/function.cos.php
[186]: http://php.net/manual/zh/function.cosh.php
[187]: http://php.net/manual/zh/function.count.php
[188]: http://php.net/manual/zh/function.count-chars.php
[189]: http://php.net/manual/zh/function.crc32.php
[190]: http://php.net/manual/zh/function.create-function.php
[191]: http://php.net/manual/zh/function.crypt.php
[192]: http://php.net/manual/zh/function.ctype-alnum.php
[193]: http://php.net/manual/zh/function.ctype-alpha.php
[194]: http://php.net/manual/zh/function.ctype-cntrl.php
[195]: http://php.net/manual/zh/function.ctype-digit.php
[196]: http://php.net/manual/zh/function.ctype-graph.php
[197]: http://php.net/manual/zh/function.ctype-lower.php
[198]: http://php.net/manual/zh/function.ctype-print.php
[199]: http://php.net/manual/zh/function.ctype-punct.php
[200]: http://php.net/manual/zh/function.ctype-space.php
[201]: http://php.net/manual/zh/function.ctype-upper.php
[202]: http://php.net/manual/zh/function.ctype-xdigit.php
[203]: http://php.net/manual/zh/function.cubrid-affected-rows.php
[204]: http://php.net/manual/zh/function.cubrid-bind.php
[205]: http://php.net/manual/zh/function.cubrid-client-encoding.php
[206]: http://php.net/manual/zh/function.cubrid-close.php
[207]: http://php.net/manual/zh/function.cubrid-close-prepare.php
[208]: http://php.net/manual/zh/function.cubrid-close-request.php
[209]: http://php.net/manual/zh/function.cubrid-col-get.php
[210]: http://php.net/manual/zh/function.cubrid-col-size.php
[211]: http://php.net/manual/zh/function.cubrid-column-names.php
[212]: http://php.net/manual/zh/function.cubrid-column-types.php
[213]: http://php.net/manual/zh/function.cubrid-commit.php
[214]: http://php.net/manual/zh/function.cubrid-connect.php
[215]: http://php.net/manual/zh/function.cubrid-connect-with-url.php
[216]: http://php.net/manual/zh/function.cubrid-current-oid.php
[217]: http://php.net/manual/zh/function.cubrid-data-seek.php
[218]: http://php.net/manual/zh/function.cubrid-db-name.php
[219]: http://php.net/manual/zh/function.cubrid-disconnect.php
[220]: http://php.net/manual/zh/function.cubrid-drop.php
[221]: http://php.net/manual/zh/function.cubrid-errno.php
[222]: http://php.net/manual/zh/function.cubrid-error.php
[223]: http://php.net/manual/zh/function.cubrid-error-code.php
[224]: http://php.net/manual/zh/function.cubrid-error-code-facility.php
[225]: http://php.net/manual/zh/function.cubrid-error-msg.php
[226]: http://php.net/manual/zh/function.cubrid-execute.php
[227]: http://php.net/manual/zh/function.cubrid-fetch.php
[228]: http://php.net/manual/zh/function.cubrid-fetch-array.php
[229]: http://php.net/manual/zh/function.cubrid-fetch-assoc.php
[230]: http://php.net/manual/zh/function.cubrid-fetch-field.php
[231]: http://php.net/manual/zh/function.cubrid-fetch-lengths.php
[232]: http://php.net/manual/zh/function.cubrid-fetch-object.php
[233]: http://php.net/manual/zh/function.cubrid-fetch-row.php
[234]: http://php.net/manual/zh/function.cubrid-field-flags.php
[235]: http://php.net/manual/zh/function.cubrid-field-len.php
[236]: http://php.net/manual/zh/function.cubrid-field-name.php
[237]: http://php.net/manual/zh/function.cubrid-field-seek.php
[238]: http://php.net/manual/zh/function.cubrid-field-table.php
[239]: http://php.net/manual/zh/function.cubrid-field-type.php
[240]: http://php.net/manual/zh/function.cubrid-free-result.php
[241]: http://php.net/manual/zh/function.cubrid-get.php
[242]: http://php.net/manual/zh/function.cubrid-get-autocommit.php
[243]: http://php.net/manual/zh/function.cubrid-get-charset.php
[244]: http://php.net/manual/zh/function.cubrid-get-class-name.php
[245]: http://php.net/manual/zh/function.cubrid-get-client-info.php
[246]: http://php.net/manual/zh/function.cubrid-get-db-parameter.php
[247]: http://php.net/manual/zh/function.cubrid-get-query-timeout.php
[248]: http://php.net/manual/zh/function.cubrid-get-server-info.php
[249]: http://php.net/manual/zh/function.cubrid-insert-id.php
[250]: http://php.net/manual/zh/function.cubrid-is-instance.php
[251]: http://php.net/manual/zh/function.cubrid-list-dbs.php
[252]: http://php.net/manual/zh/function.cubrid-lob2-bind.php
[253]: http://php.net/manual/zh/function.cubrid-lob2-close.php
[254]: http://php.net/manual/zh/function.cubrid-lob2-export.php
[255]: http://php.net/manual/zh/function.cubrid-lob2-import.php
[256]: http://php.net/manual/zh/function.cubrid-lob2-new.php
[257]: http://php.net/manual/zh/function.cubrid-lob2-read.php
[258]: http://php.net/manual/zh/function.cubrid-lob2-seek.php
[259]: http://php.net/manual/zh/function.cubrid-lob2-seek64.php
[260]: http://php.net/manual/zh/function.cubrid-lob2-size.php
[261]: http://php.net/manual/zh/function.cubrid-lob2-size64.php
[262]: http://php.net/manual/zh/function.cubrid-lob2-tell.php
[263]: http://php.net/manual/zh/function.cubrid-lob2-tell64.php
[264]: http://php.net/manual/zh/function.cubrid-lob-close.php
[265]: http://php.net/manual/zh/function.cubrid-lob-export.php
[266]: http://php.net/manual/zh/function.cubrid-lob-get.php
[267]: http://php.net/manual/zh/function.cubrid-lob-send.php
[268]: http://php.net/manual/zh/function.cubrid-lob-size.php
[269]: http://php.net/manual/zh/function.cubrid-lock-read.php
[270]: http://php.net/manual/zh/function.cubrid-lock-write.php
[271]: http://php.net/manual/zh/function.cubrid-move-cursor.php
[272]: http://php.net/manual/zh/function.cubrid-next-result.php
[273]: http://php.net/manual/zh/function.cubrid-num-cols.php
[274]: http://php.net/manual/zh/function.cubrid-num-fields.php
[275]: http://php.net/manual/zh/function.cubrid-num-rows.php
[276]: http://php.net/manual/zh/function.cubrid-pconnect.php
[277]: http://php.net/manual/zh/function.cubrid-pconnect-with-url.php
[278]: http://php.net/manual/zh/function.cubrid-ping.php
[279]: http://php.net/manual/zh/function.cubrid-prepare.php
[280]: http://php.net/manual/zh/function.cubrid-put.php
[281]: http://php.net/manual/zh/function.cubrid-query.php
[282]: http://php.net/manual/zh/function.cubrid-real-escape-string.php
[283]: http://php.net/manual/zh/function.cubrid-result.php
[284]: http://php.net/manual/zh/function.cubrid-rollback.php
[285]: http://php.net/manual/zh/function.cubrid-schema.php
[286]: http://php.net/manual/zh/function.cubrid-seq-drop.php
[287]: http://php.net/manual/zh/function.cubrid-seq-insert.php
[288]: http://php.net/manual/zh/function.cubrid-seq-put.php
[289]: http://php.net/manual/zh/function.cubrid-set-add.php
[290]: http://php.net/manual/zh/function.cubrid-set-autocommit.php
[291]: http://php.net/manual/zh/function.cubrid-set-db-parameter.php
[292]: http://php.net/manual/zh/function.cubrid-set-drop.php
[293]: http://php.net/manual/zh/function.cubrid-set-query-timeout.php
[294]: http://php.net/manual/zh/function.cubrid-unbuffered-query.php
[295]: http://php.net/manual/zh/function.cubrid-version.php
[296]: http://php.net/manual/zh/function.curl-close.php
[297]: http://php.net/manual/zh/function.curl-copy-handle.php
[298]: http://php.net/manual/zh/function.curl-errno.php
[299]: http://php.net/manual/zh/function.curl-error.php
[300]: http://php.net/manual/zh/function.curl-escape.php
[301]: http://php.net/manual/zh/function.curl-exec.php
[302]: http://php.net/manual/zh/function.curl-file-create.php
[303]: http://php.net/manual/zh/function.curl-getinfo.php
[304]: http://php.net/manual/zh/function.curl-init.php
[305]: http://php.net/manual/zh/function.curl-multi-add-handle.php
[306]: http://php.net/manual/zh/function.curl-multi-close.php
[307]: http://php.net/manual/zh/function.curl-multi-errno.php
[308]: http://php.net/manual/zh/function.curl-multi-exec.php
[309]: http://php.net/manual/zh/function.curl-multi-getcontent.php
[310]: http://php.net/manual/zh/function.curl-multi-info-read.php
[311]: http://php.net/manual/zh/function.curl-multi-init.php
[312]: http://php.net/manual/zh/function.curl-multi-remove-handle.php
[313]: http://php.net/manual/zh/function.curl-multi-select.php
[314]: http://php.net/manual/zh/function.curl-multi-setopt.php
[315]: http://php.net/manual/zh/function.curl-multi-strerror.php
[316]: http://php.net/manual/zh/function.curl-pause.php
[317]: http://php.net/manual/zh/function.curl-reset.php
[318]: http://php.net/manual/zh/function.curl-setopt.php
[319]: http://php.net/manual/zh/function.curl-setopt-array.php
[320]: http://php.net/manual/zh/function.curl-share-close.php
[321]: http://php.net/manual/zh/function.curl-share-errno.php
[322]: http://php.net/manual/zh/function.curl-share-init.php
[323]: http://php.net/manual/zh/function.curl-share-setopt.php
[324]: http://php.net/manual/zh/function.curl-share-strerror.php
[325]: http://php.net/manual/zh/function.curl-strerror.php
[326]: http://php.net/manual/zh/function.curl-unescape.php
[327]: http://php.net/manual/zh/function.curl-version.php
[328]: http://php.net/manual/zh/curlfile.getfilename.php
[329]: http://php.net/manual/zh/curlfile.getmimetype.php
[330]: http://php.net/manual/zh/curlfile.getpostfilename.php
[331]: http://php.net/manual/zh/curlfile.setmimetype.php
[332]: http://php.net/manual/zh/curlfile.setpostfilename.php
[333]: http://php.net/manual/zh/function.current.php
[334]: http://php.net/manual/zh/function.date-add.php
[335]: http://php.net/manual/zh/datetime.add.php
[336]: http://php.net/manual/zh/function.date-create.php
[337]: http://php.net/manual/zh/datetime.construct.php
[338]: http://php.net/manual/zh/function.date-create-from-format.php
[339]: http://php.net/manual/zh/datetime.createfromformat.php
[340]: http://php.net/manual/zh/function.date-create-immutable.php
[341]: http://php.net/manual/zh/datetimeimmutable.construct.php
[342]: http://php.net/manual/zh/function.date-date-set.php
[343]: http://php.net/manual/zh/datetime.setdate.php
[344]: http://php.net/manual/zh/function.date-default-timezone-get.php
[345]: http://php.net/manual/zh/function.date-default-timezone-set.php
[346]: http://php.net/manual/zh/function.date-diff.php
[347]: http://php.net/manual/zh/datetime.diff.php
[348]: http://php.net/manual/zh/function.date-format.php
[349]: http://php.net/manual/zh/datetime.format.php
[350]: http://php.net/manual/zh/function.date-get-last-errors.php
[351]: http://php.net/manual/zh/datetime.getlasterrors.php
[352]: http://php.net/manual/zh/function.date-interval-create-from-date-string.php
[353]: http://php.net/manual/zh/dateinterval.createfromdatestring.php
[354]: http://php.net/manual/zh/function.date-interval-format.php
[355]: http://php.net/manual/zh/dateinterval.format.php
[356]: http://php.net/manual/zh/function.date-isodate-set.php
[357]: http://php.net/manual/zh/datetime.setisodate.php
[358]: http://php.net/manual/zh/function.date-modify.php
[359]: http://php.net/manual/zh/datetime.modify.php
[360]: http://php.net/manual/zh/function.date-offset-get.php
[361]: http://php.net/manual/zh/datetime.getoffset.php
[362]: http://php.net/manual/zh/function.date-parse.php
[363]: http://php.net/manual/zh/function.date-parse-from-format.php
[364]: http://php.net/manual/zh/function.date-sub.php
[365]: http://php.net/manual/zh/datetime.sub.php
[366]: http://php.net/manual/zh/function.date-sun-info.php
[367]: http://php.net/manual/zh/function.date-sunrise.php
[368]: http://php.net/manual/zh/function.date-sunset.php
[369]: http://php.net/manual/zh/function.date-time-set.php
[370]: http://php.net/manual/zh/datetime.settime.php
[371]: http://php.net/manual/zh/function.date-timestamp-get.php
[372]: http://php.net/manual/zh/datetime.gettimestamp.php
[373]: http://php.net/manual/zh/function.date-timestamp-set.php
[374]: http://php.net/manual/zh/datetime.settimestamp.php
[375]: http://php.net/manual/zh/function.date-timezone-get.php
[376]: http://php.net/manual/zh/datetime.gettimezone.php
[377]: http://php.net/manual/zh/function.date-timezone-set.php
[378]: http://php.net/manual/zh/datetime.settimezone.php
[379]: http://php.net/manual/zh/dateinterval.format.php
[380]: http://php.net/manual/zh/datetimezone.getlocation.php
[381]: http://php.net/manual/zh/datetimezone.getname.php
[382]: http://php.net/manual/zh/datetimezone.getoffset.php
[383]: http://php.net/manual/zh/datetimezone.gettransitions.php
[384]: http://php.net/manual/zh/function.db2-autocommit.php
[385]: http://php.net/manual/zh/function.db2-bind-param.php
[386]: http://php.net/manual/zh/function.db2-client-info.php
[387]: http://php.net/manual/zh/function.db2-close.php
[388]: http://php.net/manual/zh/function.db2-column-privileges.php
[389]: http://php.net/manual/zh/function.db2-columns.php
[390]: http://php.net/manual/zh/function.db2-commit.php
[391]: http://php.net/manual/zh/function.db2-conn-error.php
[392]: http://php.net/manual/zh/function.db2-conn-errormsg.php
[393]: http://php.net/manual/zh/function.db2-connect.php
[394]: http://php.net/manual/zh/function.db2-cursor-type.php
[395]: http://php.net/manual/zh/function.db2-escape-string.php
[396]: http://php.net/manual/zh/function.db2-exec.php
[397]: http://php.net/manual/zh/function.db2-execute.php
[398]: http://php.net/manual/zh/function.db2-fetch-array.php
[399]: http://php.net/manual/zh/function.db2-fetch-assoc.php
[400]: http://php.net/manual/zh/function.db2-fetch-both.php
[401]: http://php.net/manual/zh/function.db2-fetch-object.php
[402]: http://php.net/manual/zh/function.db2-fetch-row.php
[403]: http://php.net/manual/zh/function.db2-field-display-size.php
[404]: http://php.net/manual/zh/function.db2-field-name.php
[405]: http://php.net/manual/zh/function.db2-field-num.php
[406]: http://php.net/manual/zh/function.db2-field-precision.php
[407]: http://php.net/manual/zh/function.db2-field-scale.php
[408]: http://php.net/manual/zh/function.db2-field-type.php
[409]: http://php.net/manual/zh/function.db2-field-width.php
[410]: http://php.net/manual/zh/function.db2-foreign-keys.php
[411]: http://php.net/manual/zh/function.db2-free-result.php
[412]: http://php.net/manual/zh/function.db2-free-stmt.php
[413]: http://php.net/manual/zh/function.db2-get-option.php
[414]: http://php.net/manual/zh/function.db2-last-insert-id.php
[415]: http://php.net/manual/zh/function.db2-lob-read.php
[416]: http://php.net/manual/zh/function.db2-next-result.php
[417]: http://php.net/manual/zh/function.db2-num-fields.php
[418]: http://php.net/manual/zh/function.db2-num-rows.php
[419]: http://php.net/manual/zh/function.db2-pconnect.php
[420]: http://php.net/manual/zh/function.db2-prepare.php
[421]: http://php.net/manual/zh/function.db2-primary-keys.php
[422]: http://php.net/manual/zh/function.db2-procedure-columns.php
[423]: http://php.net/manual/zh/function.db2-procedures.php
[424]: http://php.net/manual/zh/function.db2-result.php
[425]: http://php.net/manual/zh/function.db2-rollback.php
[426]: http://php.net/manual/zh/function.db2-server-info.php
[427]: http://php.net/manual/zh/function.db2-set-option.php
[428]: http://php.net/manual/zh/function.db2-special-columns.php
[429]: http://php.net/manual/zh/function.db2-statistics.php
[430]: http://php.net/manual/zh/function.db2-stmt-error.php
[431]: http://php.net/manual/zh/function.db2-stmt-errormsg.php
[432]: http://php.net/manual/zh/function.db2-table-privileges.php
[433]: http://php.net/manual/zh/function.db2-tables.php
[434]: http://php.net/manual/zh/function.dba-close.php
[435]: http://php.net/manual/zh/function.dba-delete.php
[436]: http://php.net/manual/zh/function.dba-exists.php
[437]: http://php.net/manual/zh/function.dba-fetch.php
[438]: http://php.net/manual/zh/function.dba-firstkey.php
[439]: http://php.net/manual/zh/function.dba-handlers.php
[440]: http://php.net/manual/zh/function.dba-insert.php
[441]: http://php.net/manual/zh/function.dba-key-split.php
[442]: http://php.net/manual/zh/function.dba-list.php
[443]: http://php.net/manual/zh/function.dba-nextkey.php
[444]: http://php.net/manual/zh/function.dba-open.php
[445]: http://php.net/manual/zh/function.dba-optimize.php
[446]: http://php.net/manual/zh/function.dba-popen.php
[447]: http://php.net/manual/zh/function.dba-replace.php
[448]: http://php.net/manual/zh/function.dba-sync.php
[449]: http://php.net/manual/zh/function.dcgettext.php
[450]: http://php.net/manual/zh/function.dcngettext.php
[451]: http://php.net/manual/zh/function.debug-backtrace.php
[452]: http://php.net/manual/zh/function.debug-print-backtrace.php
[453]: http://php.net/manual/zh/function.debug-zval-dump.php
[454]: http://php.net/manual/zh/function.decbin.php
[455]: http://php.net/manual/zh/function.dechex.php
[456]: http://php.net/manual/zh/function.decoct.php
[457]: http://php.net/manual/zh/function.define.php
[458]: http://php.net/manual/zh/function.define-syslog-variables.php
[459]: http://php.net/manual/zh/function.defined.php
[460]: http://php.net/manual/zh/function.deflate-add.php
[461]: http://php.net/manual/zh/function.deflate-init.php
[462]: http://php.net/manual/zh/function.deg2rad.php
[463]: http://php.net/manual/zh/function.dgettext.php
[464]: http://php.net/manual/zh/function.dir.php
[465]: http://php.net/manual/zh/directory.close.php
[466]: http://php.net/manual/zh/directory.read.php
[467]: http://php.net/manual/zh/directory.rewind.php
[468]: http://php.net/manual/zh/function.dirname.php
[469]: http://php.net/manual/zh/function.disk-free-space.php
[470]: http://php.net/manual/zh/function.disk-total-space.php
[471]: http://php.net/manual/zh/function.diskfreespace.php
[472]: http://php.net/manual/zh/function.disk-free-space.php
[473]: http://php.net/manual/zh/function.dl.php
[474]: http://php.net/manual/zh/function.dngettext.php
[475]: http://php.net/manual/zh/function.dns-check-record.php
[476]: http://php.net/manual/zh/function.checkdnsrr.php
[477]: http://php.net/manual/zh/function.dns-get-mx.php
[478]: http://php.net/manual/zh/function.getmxrr.php
[479]: http://php.net/manual/zh/function.dom-import-simplexml.php
[480]: http://php.net/manual/zh/class.domelement.php
[481]: http://php.net/manual/zh/class.simplexmlelement.php
[482]: http://php.net/manual/zh/domimplementation.createdocument.php
[483]: http://php.net/manual/zh/domimplementation.createdocumenttype.php
[484]: http://php.net/manual/zh/domnode.appendchild.php
[485]: http://php.net/manual/zh/domnode.c14n.php
[486]: http://php.net/manual/zh/domnode.c14nfile.php
[487]: http://php.net/manual/zh/domnode.clonenode.php
[488]: http://php.net/manual/zh/domnode.getlineno.php
[489]: http://php.net/manual/zh/domnode.getnodepath.php
[490]: http://php.net/manual/zh/domnode.hasattributes.php
[491]: http://php.net/manual/zh/domnode.haschildnodes.php
[492]: http://php.net/manual/zh/domnode.insertbefore.php
[493]: http://php.net/manual/zh/domnode.isdefaultnamespace.php
[494]: http://php.net/manual/zh/domnode.issamenode.php
[495]: http://php.net/manual/zh/domnode.issupported.php
[496]: http://php.net/manual/zh/domnode.lookupnamespaceuri.php
[497]: http://php.net/manual/zh/domnode.lookupprefix.php
[498]: http://php.net/manual/zh/domnode.normalize.php
[499]: http://php.net/manual/zh/domnode.removechild.php
[500]: http://php.net/manual/zh/domnode.replacechild.php
[501]: http://php.net/manual/zh/domxpath.evaluate.php
[502]: http://php.net/manual/zh/domxpath.query.php
[503]: http://php.net/manual/zh/domxpath.registernamespace.php
[504]: http://php.net/manual/zh/class.domxpath.php
[505]: http://php.net/manual/zh/domxpath.registerphpfunctions.php
[506]: http://php.net/manual/zh/function.doubleval.php
[507]: http://php.net/manual/zh/function.floatval.php
[508]: http://php.net/manual/zh/function.easter-date.php
[509]: http://php.net/manual/zh/function.easter-days.php
[510]: http://php.net/manual/zh/function.enchant-broker-describe.php
[511]: http://php.net/manual/zh/function.enchant-broker-dict-exists.php
[512]: http://php.net/manual/zh/function.enchant-broker-free.php
[513]: http://php.net/manual/zh/function.enchant-broker-free-dict.php
[514]: http://php.net/manual/zh/function.enchant-broker-get-dict-path.php
[515]: http://php.net/manual/zh/function.enchant-broker-get-error.php
[516]: http://php.net/manual/zh/function.enchant-broker-init.php
[517]: http://php.net/manual/zh/function.enchant-broker-list-dicts.php
[518]: http://php.net/manual/zh/function.enchant-broker-request-dict.php
[519]: http://php.net/manual/zh/function.enchant-broker-request-pwl-dict.php
[520]: http://php.net/manual/zh/function.enchant-broker-set-dict-path.php
[521]: http://php.net/manual/zh/function.enchant-broker-set-ordering.php
[522]: http://php.net/manual/zh/function.enchant-dict-add-to-personal.php
[523]: http://php.net/manual/zh/function.enchant-dict-add-to-session.php
[524]: http://php.net/manual/zh/function.enchant-dict-check.php
[525]: http://php.net/manual/zh/function.enchant-dict-describe.php
[526]: http://php.net/manual/zh/function.enchant-dict-get-error.php
[527]: http://php.net/manual/zh/function.enchant-dict-is-in-session.php
[528]: http://php.net/manual/zh/function.enchant-dict-quick-check.php
[529]: http://php.net/manual/zh/function.enchant-dict-store-replacement.php
[530]: http://php.net/manual/zh/function.enchant-dict-suggest.php
[531]: http://php.net/manual/zh/function.end.php
[532]: http://php.net/manual/zh/function.ereg.php
[533]: http://php.net/manual/zh/function.ereg-replace.php
[534]: http://php.net/manual/zh/function.eregi.php
[535]: http://php.net/manual/zh/function.eregi-replace.php
[536]: http://php.net/manual/zh/function.error-clear-last.php
[537]: http://php.net/manual/zh/function.error-get-last.php
[538]: http://php.net/manual/zh/function.error-log.php
[539]: http://php.net/manual/zh/function.error-reporting.php
[540]: http://php.net/manual/zh/function.escapeshellarg.php
[541]: http://php.net/manual/zh/function.escapeshellcmd.php
[542]: http://php.net/manual/zh/function.event-add.php
[543]: http://php.net/manual/zh/function.event-base-free.php
[544]: http://php.net/manual/zh/function.event-base-loop.php
[545]: http://php.net/manual/zh/function.event-base-loopbreak.php
[546]: http://php.net/manual/zh/function.event-base-loopexit.php
[547]: http://php.net/manual/zh/function.event-base-new.php
[548]: http://php.net/manual/zh/function.event-base-priority-init.php
[549]: http://php.net/manual/zh/function.event-base-set.php
[550]: http://php.net/manual/zh/function.event-buffer-base-set.php
[551]: http://php.net/manual/zh/function.event-buffer-disable.php
[552]: http://php.net/manual/zh/function.event-buffer-enable.php
[553]: http://php.net/manual/zh/function.event-buffer-fd-set.php
[554]: http://php.net/manual/zh/function.event-buffer-free.php
[555]: http://php.net/manual/zh/function.event-buffer-new.php
[556]: http://php.net/manual/zh/function.event-buffer-priority-set.php
[557]: http://php.net/manual/zh/function.event-buffer-read.php
[558]: http://php.net/manual/zh/function.event-buffer-set-callback.php
[559]: http://php.net/manual/zh/function.event-buffer-timeout-set.php
[560]: http://php.net/manual/zh/function.event-buffer-watermark-set.php
[561]: http://php.net/manual/zh/function.event-buffer-write.php
[562]: http://php.net/manual/zh/function.event-del.php
[563]: http://php.net/manual/zh/function.event-free.php
[564]: http://php.net/manual/zh/function.event-new.php
[565]: http://php.net/manual/zh/function.event-set.php
[566]: http://php.net/manual/zh/function.event-timer-add.php
[567]: http://php.net/manual/zh/event.add.php
[568]: http://php.net/manual/zh/function.event-timer-del.php
[569]: http://php.net/manual/zh/event.del.php
[570]: http://php.net/manual/zh/function.event-timer-new.php
[571]: http://php.net/manual/zh/function.event-new.php
[572]: http://php.net/manual/zh/function.event-timer-set.php
[573]: http://php.net/manual/zh/function.exec.php
[574]: http://php.net/manual/zh/function.exif-imagetype.php
[575]: http://php.net/manual/zh/function.exif-read-data.php
[576]: http://php.net/manual/zh/function.exif-tagname.php
[577]: http://php.net/manual/zh/function.exif-thumbnail.php
[578]: http://php.net/manual/zh/function.exp.php
[579]: http://php.net/manual/zh/function.explode.php
[580]: http://php.net/manual/zh/function.expm1.php
[581]: http://php.net/manual/zh/function.extension-loaded.php
[582]: http://php.net/manual/zh/function.extract.php
[583]: http://php.net/manual/zh/function.ezmlm-hash.php
[584]: http://php.net/manual/zh/function.fclose.php
[585]: http://php.net/manual/zh/function.feof.php
[586]: http://php.net/manual/zh/function.fflush.php
[587]: http://php.net/manual/zh/function.fgetc.php
[588]: http://php.net/manual/zh/function.fgetcsv.php
[589]: http://php.net/manual/zh/function.fgets.php
[590]: http://php.net/manual/zh/function.fgetss.php
[591]: http://php.net/manual/zh/function.file.php
[592]: http://php.net/manual/zh/function.file-exists.php
[593]: http://php.net/manual/zh/function.file-get-contents.php
[594]: http://php.net/manual/zh/function.file-put-contents.php
[595]: http://php.net/manual/zh/function.fileatime.php
[596]: http://php.net/manual/zh/function.filectime.php
[597]: http://php.net/manual/zh/function.filegroup.php
[598]: http://php.net/manual/zh/function.fileinode.php
[599]: http://php.net/manual/zh/function.filemtime.php
[600]: http://php.net/manual/zh/function.fileowner.php
[601]: http://php.net/manual/zh/function.fileperms.php
[602]: http://php.net/manual/zh/function.filesize.php
[603]: http://php.net/manual/zh/function.filetype.php
[604]: http://php.net/manual/zh/function.filter-has-var.php
[605]: http://php.net/manual/zh/function.filter-id.php
[606]: http://php.net/manual/zh/function.filter-input.php
[607]: http://php.net/manual/zh/function.filter-input-array.php
[608]: http://php.net/manual/zh/function.filter-list.php
[609]: http://php.net/manual/zh/function.filter-var.php
[610]: http://php.net/manual/zh/function.filter-var-array.php
[611]: http://php.net/manual/zh/function.finfo-buffer.php
[612]: http://php.net/manual/zh/function.finfo-close.php
[613]: http://php.net/manual/zh/function.finfo-file.php
[614]: http://php.net/manual/zh/function.finfo-open.php
[615]: http://php.net/manual/zh/function.finfo-set-flags.php
[616]: http://php.net/manual/zh/function.floatval.php
[617]: http://php.net/manual/zh/function.flock.php
[618]: http://php.net/manual/zh/function.floor.php
[619]: http://php.net/manual/zh/function.flush.php
[620]: http://php.net/manual/zh/function.fmod.php
[621]: http://php.net/manual/zh/function.fnmatch.php
[622]: http://php.net/manual/zh/function.fopen.php
[623]: http://php.net/manual/zh/function.forward-static-call.php
[624]: http://php.net/manual/zh/function.forward-static-call-array.php
[625]: http://php.net/manual/zh/function.fpassthru.php
[626]: http://php.net/manual/zh/function.fprintf.php
[627]: http://php.net/manual/zh/function.fputcsv.php
[628]: http://php.net/manual/zh/function.fputs.php
[629]: http://php.net/manual/zh/function.fwrite.php
[630]: http://php.net/manual/zh/function.fread.php
[631]: http://php.net/manual/zh/function.frenchtojd.php
[632]: http://php.net/manual/zh/function.fscanf.php
[633]: http://php.net/manual/zh/function.fseek.php
[634]: http://php.net/manual/zh/function.fsockopen.php
[635]: http://php.net/manual/zh/function.fstat.php
[636]: http://php.net/manual/zh/function.ftell.php
[637]: http://php.net/manual/zh/function.ftok.php
[638]: http://php.net/manual/zh/function.ftp-alloc.php
[639]: http://php.net/manual/zh/function.ftp-cdup.php
[640]: http://php.net/manual/zh/function.ftp-chdir.php
[641]: http://php.net/manual/zh/function.ftp-chmod.php
[642]: http://php.net/manual/zh/function.ftp-close.php
[643]: http://php.net/manual/zh/function.ftp-connect.php
[644]: http://php.net/manual/zh/function.ftp-delete.php
[645]: http://php.net/manual/zh/function.ftp-exec.php
[646]: http://php.net/manual/zh/function.ftp-fget.php
[647]: http://php.net/manual/zh/function.ftp-fput.php
[648]: http://php.net/manual/zh/function.ftp-get.php
[649]: http://php.net/manual/zh/function.ftp-get-option.php
[650]: http://php.net/manual/zh/function.ftp-login.php
[651]: http://php.net/manual/zh/function.ftp-mdtm.php
[652]: http://php.net/manual/zh/function.ftp-mkdir.php
[653]: http://php.net/manual/zh/function.ftp-nb-continue.php
[654]: http://php.net/manual/zh/function.ftp-nb-fget.php
[655]: http://php.net/manual/zh/function.ftp-nb-fput.php
[656]: http://php.net/manual/zh/function.ftp-nb-get.php
[657]: http://php.net/manual/zh/function.ftp-nb-put.php
[658]: http://php.net/manual/zh/function.ftp-nlist.php
[659]: http://php.net/manual/zh/function.ftp-pasv.php
[660]: http://php.net/manual/zh/function.ftp-put.php
[661]: http://php.net/manual/zh/function.ftp-pwd.php
[662]: http://php.net/manual/zh/function.ftp-quit.php
[663]: http://php.net/manual/zh/function.ftp-close.php
[664]: http://php.net/manual/zh/function.ftp-raw.php
[665]: http://php.net/manual/zh/function.ftp-rawlist.php
[666]: http://php.net/manual/zh/function.ftp-rename.php
[667]: http://php.net/manual/zh/function.ftp-rmdir.php
[668]: http://php.net/manual/zh/function.ftp-set-option.php
[669]: http://php.net/manual/zh/function.ftp-site.php
[670]: http://php.net/manual/zh/function.ftp-size.php
[671]: http://php.net/manual/zh/function.ftp-ssl-connect.php
[672]: http://php.net/manual/zh/function.ftp-systype.php
[673]: http://php.net/manual/zh/function.ftruncate.php
[674]: http://php.net/manual/zh/function.func-get-arg.php
[675]: http://php.net/manual/zh/function.func-get-args.php
[676]: http://php.net/manual/zh/function.func-num-args.php
[677]: http://php.net/manual/zh/function.function-exists.php
[678]: http://php.net/manual/zh/function.fwrite.php
[679]: http://php.net/manual/zh/function.gc-disable.php
[680]: http://php.net/manual/zh/function.gc-enable.php
[681]: http://php.net/manual/zh/function.gc-enabled.php
[682]: http://php.net/manual/zh/function.gc-mem-caches.php
[683]: http://php.net/manual/zh/function.gd-info.php
[684]: http://php.net/manual/zh/function.gearman-client-clone.php
[685]: http://php.net/manual/zh/class.gearmanclient.php
[686]: http://php.net/manual/zh/function.gearman-client-context.php
[687]: http://php.net/manual/zh/function.gearman-client-do.php
[688]: http://php.net/manual/zh/function.gearman-client-echo.php
[689]: http://php.net/manual/zh/function.gearman-client-error.php
[690]: http://php.net/manual/zh/function.gearman-client-timeout.php
[691]: http://php.net/manual/zh/function.gearman-job-handle.php
[692]: http://php.net/manual/zh/function.gearman-job-unique.php
[693]: http://php.net/manual/zh/function.gearman-job-workload.php
[694]: http://php.net/manual/zh/function.gearman-task-data.php
[695]: http://php.net/manual/zh/function.gearman-task-unique.php
[696]: http://php.net/manual/zh/function.gearman-worker-clone.php
[697]: http://php.net/manual/zh/function.gearman-worker-echo.php
[698]: http://php.net/manual/zh/function.gearman-worker-error.php
[699]: http://php.net/manual/zh/function.gearman-worker-options.php
[700]: http://php.net/manual/zh/function.gearman-worker-register.php
[701]: http://php.net/manual/zh/function.gearman-worker-timeout.php
[702]: http://php.net/manual/zh/function.gearman-worker-unregister.php
[703]: http://php.net/manual/zh/function.gearman-worker-wait.php
[704]: http://php.net/manual/zh/function.gearman-worker-work.php
[705]: http://php.net/manual/zh/gearmanclient.addoptions.php
[706]: http://php.net/manual/zh/gearmanclient.addserver.php
[707]: http://php.net/manual/zh/gearmanclient.addservers.php
[708]: http://php.net/manual/zh/gearmanclient.addtask.php
[709]: http://php.net/manual/zh/gearmanclient.addtaskbackground.php
[710]: http://php.net/manual/zh/gearmanclient.addtaskhigh.php
[711]: http://php.net/manual/zh/gearmanclient.addtaskhighbackground.php
[712]: http://php.net/manual/zh/gearmanclient.addtasklow.php
[713]: http://php.net/manual/zh/gearmanclient.addtasklowbackground.php
[714]: http://php.net/manual/zh/gearmanclient.addtaskstatus.php
[715]: http://php.net/manual/zh/gearmanclient.clearcallbacks.php
[716]: http://php.net/manual/zh/gearmanclient.context.php
[717]: http://php.net/manual/zh/gearmanclient.dobackground.php
[718]: http://php.net/manual/zh/gearmanclient.dohigh.php
[719]: http://php.net/manual/zh/gearmanclient.dohighbackground.php
[720]: http://php.net/manual/zh/gearmanclient.dojobhandle.php
[721]: http://php.net/manual/zh/gearmanclient.dolow.php
[722]: http://php.net/manual/zh/gearmanclient.dolowbackground.php
[723]: http://php.net/manual/zh/gearmanclient.donormal.php
[724]: http://php.net/manual/zh/gearmanclient.dostatus.php
[725]: http://php.net/manual/zh/gearmanclient.error.php
[726]: http://php.net/manual/zh/gearmanclient.geterrno.php
[727]: http://php.net/manual/zh/gearmanclient.jobstatus.php
[728]: http://php.net/manual/zh/gearmanclient.removeoptions.php
[729]: http://php.net/manual/zh/gearmanclient.returncode.php
[730]: http://php.net/manual/zh/gearmanclient.runtasks.php
[731]: http://php.net/manual/zh/gearmanclient.setcompletecallback.php
[732]: http://php.net/manual/zh/gearmanclient.setcontext.php
[733]: http://php.net/manual/zh/gearmanclient.setcreatedcallback.php
[734]: http://php.net/manual/zh/gearmanclient.setdatacallback.php
[735]: http://php.net/manual/zh/gearmanclient.setexceptioncallback.php
[736]: http://php.net/manual/zh/gearmanclient.setfailcallback.php
[737]: http://php.net/manual/zh/gearmanclient.setoptions.php
[738]: http://php.net/manual/zh/gearmanclient.setstatuscallback.php
[739]: http://php.net/manual/zh/gearmanclient.settimeout.php
[740]: http://php.net/manual/zh/gearmanclient.setwarningcallback.php
[741]: http://php.net/manual/zh/gearmanclient.setworkloadcallback.php
[742]: http://php.net/manual/zh/gearmanclient.timeout.php
[743]: http://php.net/manual/zh/gearmanjob.functionname.php
[744]: http://php.net/manual/zh/gearmanjob.handle.php
[745]: http://php.net/manual/zh/gearmanjob.returncode.php
[746]: http://php.net/manual/zh/gearmanjob.sendcomplete.php
[747]: http://php.net/manual/zh/gearmanjob.senddata.php
[748]: http://php.net/manual/zh/gearmanjob.sendexception.php
[749]: http://php.net/manual/zh/gearmanjob.sendfail.php
[750]: http://php.net/manual/zh/gearmanjob.sendstatus.php
[751]: http://php.net/manual/zh/gearmanjob.sendwarning.php
[752]: http://php.net/manual/zh/gearmanjob.setreturn.php
[753]: http://php.net/manual/zh/gearmanjob.unique.php
[754]: http://php.net/manual/zh/gearmanjob.workload.php
[755]: http://php.net/manual/zh/gearmanjob.workloadsize.php
[756]: http://php.net/manual/zh/gearmantask.data.php
[757]: http://php.net/manual/zh/gearmantask.datasize.php
[758]: http://php.net/manual/zh/gearmantask.functionname.php
[759]: http://php.net/manual/zh/gearmantask.isknown.php
[760]: http://php.net/manual/zh/gearmantask.isrunning.php
[761]: http://php.net/manual/zh/gearmantask.jobhandle.php
[762]: http://php.net/manual/zh/gearmantask.recvdata.php
[763]: http://php.net/manual/zh/gearmantask.returncode.php
[764]: http://php.net/manual/zh/gearmantask.sendworkload.php
[765]: http://php.net/manual/zh/gearmantask.taskdenominator.php
[766]: http://php.net/manual/zh/gearmantask.tasknumerator.php
[767]: http://php.net/manual/zh/gearmantask.unique.php
[768]: http://php.net/manual/zh/gearmanworker.addfunction.php
[769]: http://php.net/manual/zh/gearmanworker.addoptions.php
[770]: http://php.net/manual/zh/gearmanworker.addserver.php
[771]: http://php.net/manual/zh/gearmanworker.addservers.php
[772]: http://php.net/manual/zh/gearmanworker.error.php
[773]: http://php.net/manual/zh/gearmanworker.geterrno.php
[774]: http://php.net/manual/zh/gearmanworker.options.php
[775]: http://php.net/manual/zh/gearmanworker.register.php
[776]: http://php.net/manual/zh/gearmanworker.removeoptions.php
[777]: http://php.net/manual/zh/gearmanworker.returncode.php
[778]: http://php.net/manual/zh/gearmanworker.setid.php
[779]: http://php.net/manual/zh/gearmanworker.setoptions.php
[780]: http://php.net/manual/zh/gearmanworker.settimeout.php
[781]: http://php.net/manual/zh/gearmanworker.timeout.php
[782]: http://php.net/manual/zh/gearmanworker.unregister.php
[783]: http://php.net/manual/zh/gearmanworker.unregisterall.php
[784]: http://php.net/manual/zh/gearmanworker.wait.php
[785]: http://php.net/manual/zh/gearmanworker.work.php
[786]: http://php.net/manual/zh/function.geoip-continent-code-by-name.php
[787]: http://php.net/manual/zh/function.geoip-country-code3-by-name.php
[788]: http://php.net/manual/zh/function.geoip-country-code-by-name.php
[789]: http://php.net/manual/zh/function.geoip-country-name-by-name.php
[790]: http://php.net/manual/zh/function.geoip-database-info.php
[791]: http://php.net/manual/zh/function.geoip-db-avail.php
[792]: http://php.net/manual/zh/function.geoip-db-filename.php
[793]: http://php.net/manual/zh/function.geoip-db-get-all-info.php
[794]: http://php.net/manual/zh/function.geoip-id-by-name.php
[795]: http://php.net/manual/zh/function.geoip-isp-by-name.php
[796]: http://php.net/manual/zh/function.geoip-org-by-name.php
[797]: http://php.net/manual/zh/function.geoip-record-by-name.php
[798]: http://php.net/manual/zh/function.geoip-region-by-name.php
[799]: http://php.net/manual/zh/function.geoip-region-name-by-code.php
[800]: http://php.net/manual/zh/function.geoip-time-zone-by-country-and-region.php
[801]: http://php.net/manual/zh/function.get-browser.php
[802]: http://php.net/manual/zh/function.get-called-class.php
[803]: http://php.net/manual/zh/function.get-cfg-var.php
[804]: http://php.net/manual/zh/function.get-class.php
[805]: http://php.net/manual/zh/function.get-class-methods.php
[806]: http://php.net/manual/zh/function.get-class-vars.php
[807]: http://php.net/manual/zh/function.get-current-user.php
[808]: http://php.net/manual/zh/function.get-declared-classes.php
[809]: http://php.net/manual/zh/function.get-declared-interfaces.php
[810]: http://php.net/manual/zh/function.get-declared-traits.php
[811]: http://php.net/manual/zh/function.get-defined-constants.php
[812]: http://php.net/manual/zh/function.get-defined-functions.php
[813]: http://php.net/manual/zh/function.get-defined-vars.php
[814]: http://php.net/manual/zh/function.get-extension-funcs.php
[815]: http://php.net/manual/zh/function.get-headers.php
[816]: http://php.net/manual/zh/function.get-html-translation-table.php
[817]: http://php.net/manual/zh/function.htmlspecialchars.php
[818]: http://php.net/manual/zh/function.htmlentities.php
[819]: http://php.net/manual/zh/function.get-include-path.php
[820]: http://php.net/manual/zh/function.get-included-files.php
[821]: http://php.net/manual/zh/function.get-loaded-extensions.php
[822]: http://php.net/manual/zh/function.get-magic-quotes-gpc.php
[823]: http://php.net/manual/zh/function.get-magic-quotes-runtime.php
[824]: http://php.net/manual/zh/function.get-meta-tags.php
[825]: http://php.net/manual/zh/function.get-object-vars.php
[826]: http://php.net/manual/zh/function.get-parent-class.php
[827]: http://php.net/manual/zh/function.get-required-files.php
[828]: http://php.net/manual/zh/function.get-included-files.php
[829]: http://php.net/manual/zh/function.get-resource-type.php
[830]: http://php.net/manual/zh/function.get-resources.php
[831]: http://php.net/manual/zh/function.getallheaders.php
[832]: http://php.net/manual/zh/function.getcwd.php
[833]: http://php.net/manual/zh/function.getdate.php
[834]: http://php.net/manual/zh/function.getenv.php
[835]: http://php.net/manual/zh/function.gethostbyaddr.php
[836]: http://php.net/manual/zh/function.gethostbyname.php
[837]: http://php.net/manual/zh/function.gethostbynamel.php
[838]: http://php.net/manual/zh/function.gethostname.php
[839]: http://php.net/manual/zh/function.getimagesize.php
[840]: http://php.net/manual/zh/function.getimagesizefromstring.php
[841]: http://php.net/manual/zh/function.getlastmod.php
[842]: http://php.net/manual/zh/function.getmxrr.php
[843]: http://php.net/manual/zh/function.getmygid.php
[844]: http://php.net/manual/zh/function.getmypid.php
[845]: http://php.net/manual/zh/function.getmyuid.php
[846]: http://php.net/manual/zh/function.getopt.php
[847]: http://php.net/manual/zh/function.getprotobyname.php
[848]: http://php.net/manual/zh/function.getprotobynumber.php
[849]: http://php.net/manual/zh/function.getrandmax.php
[850]: http://php.net/manual/zh/function.getrusage.php
[851]: http://php.net/manual/zh/function.getservbyname.php
[852]: http://php.net/manual/zh/function.getservbyport.php
[853]: http://php.net/manual/zh/function.gettext.php
[854]: http://php.net/manual/zh/function.gettimeofday.php
[855]: http://php.net/manual/zh/function.gettype.php
[856]: http://php.net/manual/zh/function.glob.php
[857]: http://php.net/manual/zh/function.gmdate.php
[858]: http://php.net/manual/zh/function.gmmktime.php
[859]: http://php.net/manual/zh/function.gmp-abs.php
[860]: http://php.net/manual/zh/function.gmp-add.php
[861]: http://php.net/manual/zh/function.gmp-and.php
[862]: http://php.net/manual/zh/function.gmp-clrbit.php
[863]: http://php.net/manual/zh/function.gmp-cmp.php
[864]: http://php.net/manual/zh/function.gmp-com.php
[865]: http://php.net/manual/zh/function.gmp-div.php
[866]: http://php.net/manual/zh/function.gmp-div-q.php
[867]: http://php.net/manual/zh/function.gmp-div-q.php
[868]: http://php.net/manual/zh/function.gmp-div-qr.php
[869]: http://php.net/manual/zh/function.gmp-div-r.php
[870]: http://php.net/manual/zh/function.gmp-divexact.php
[871]: http://php.net/manual/zh/function.gmp-export.php
[872]: http://php.net/manual/zh/function.gmp-fact.php
[873]: http://php.net/manual/zh/function.gmp-gcd.php
[874]: http://php.net/manual/zh/function.gmp-gcdext.php
[875]: http://php.net/manual/zh/function.gmp-hamdist.php
[876]: http://php.net/manual/zh/function.gmp-import.php
[877]: http://php.net/manual/zh/function.gmp-init.php
[878]: http://php.net/manual/zh/function.gmp-intval.php
[879]: http://php.net/manual/zh/function.gmp-invert.php
[880]: http://php.net/manual/zh/function.gmp-jacobi.php
[881]: http://php.net/manual/zh/function.gmp-legendre.php
[882]: http://php.net/manual/zh/function.gmp-mod.php
[883]: http://php.net/manual/zh/function.gmp-mul.php
[884]: http://php.net/manual/zh/function.gmp-neg.php
[885]: http://php.net/manual/zh/function.gmp-nextprime.php
[886]: http://php.net/manual/zh/function.gmp-or.php
[887]: http://php.net/manual/zh/function.gmp-perfect-square.php
[888]: http://php.net/manual/zh/function.gmp-popcount.php
[889]: http://php.net/manual/zh/function.gmp-pow.php
[890]: http://php.net/manual/zh/function.gmp-powm.php
[891]: http://php.net/manual/zh/function.gmp-prob-prime.php
[892]: http://php.net/manual/zh/function.gmp-random.php
[893]: http://php.net/manual/zh/function.gmp-random-seed.php
[894]: http://php.net/manual/zh/function.gmp-root.php
[895]: http://php.net/manual/zh/function.gmp-rootrem.php
[896]: http://php.net/manual/zh/function.gmp-scan0.php
[897]: http://php.net/manual/zh/function.gmp-scan1.php
[898]: http://php.net/manual/zh/function.gmp-setbit.php
[899]: http://php.net/manual/zh/function.gmp-sign.php
[900]: http://php.net/manual/zh/function.gmp-sqrt.php
[901]: http://php.net/manual/zh/function.gmp-sqrtrem.php
[902]: http://php.net/manual/zh/function.gmp-strval.php
[903]: http://php.net/manual/zh/function.gmp-sub.php
[904]: http://php.net/manual/zh/function.gmp-testbit.php
[905]: http://php.net/manual/zh/function.gmp-xor.php
[906]: http://php.net/manual/zh/function.gmstrftime.php
[907]: http://php.net/manual/zh/function.grapheme-extract.php
[908]: http://php.net/manual/zh/function.grapheme-stripos.php
[909]: http://php.net/manual/zh/function.grapheme-stristr.php
[910]: http://php.net/manual/zh/function.grapheme-strlen.php
[911]: http://php.net/manual/zh/function.grapheme-strpos.php
[912]: http://php.net/manual/zh/function.grapheme-strripos.php
[913]: http://php.net/manual/zh/function.grapheme-strrpos.php
[914]: http://php.net/manual/zh/function.grapheme-strstr.php
[915]: http://php.net/manual/zh/function.grapheme-substr.php
[916]: http://php.net/manual/zh/function.gregoriantojd.php
[917]: http://php.net/manual/zh/function.gzclose.php
[918]: http://php.net/manual/zh/function.gzcompress.php
[919]: http://php.net/manual/zh/function.gzdecode.php
[920]: http://php.net/manual/zh/function.gzdeflate.php
[921]: http://php.net/manual/zh/function.gzencode.php
[922]: http://php.net/manual/zh/function.gzeof.php
[923]: http://php.net/manual/zh/function.gzfile.php
[924]: http://php.net/manual/zh/function.gzgetc.php
[925]: http://php.net/manual/zh/function.gzgets.php
[926]: http://php.net/manual/zh/function.gzgetss.php
[927]: http://php.net/manual/zh/function.gzinflate.php
[928]: http://php.net/manual/zh/function.gzopen.php
[929]: http://php.net/manual/zh/function.gzpassthru.php
[930]: http://php.net/manual/zh/function.gzputs.php
[931]: http://php.net/manual/zh/function.gzwrite.php
[932]: http://php.net/manual/zh/function.gzread.php
[933]: http://php.net/manual/zh/function.gzrewind.php
[934]: http://php.net/manual/zh/function.gzseek.php
[935]: http://php.net/manual/zh/function.gztell.php
[936]: http://php.net/manual/zh/function.gzuncompress.php
[937]: http://php.net/manual/zh/function.gzwrite.php
[938]: http://php.net/manual/zh/function.hash-algos.php
[939]: http://php.net/manual/zh/function.hash-copy.php
[940]: http://php.net/manual/zh/function.hash-equals.php
[941]: http://php.net/manual/zh/function.hash-file.php
[942]: http://php.net/manual/zh/function.hash-final.php
[943]: http://php.net/manual/zh/function.hash-hmac.php
[944]: http://php.net/manual/zh/function.hash-hmac-file.php
[945]: http://php.net/manual/zh/function.hash-init.php
[946]: http://php.net/manual/zh/function.hash-pbkdf2.php
[947]: http://php.net/manual/zh/function.hash-update.php
[948]: http://php.net/manual/zh/function.hash-update-file.php
[949]: http://php.net/manual/zh/function.hash-update-stream.php
[950]: http://php.net/manual/zh/function.header.php
[951]: http://php.net/manual/zh/function.header-register-callback.php
[952]: http://php.net/manual/zh/function.header-remove.php
[953]: http://php.net/manual/zh/function.headers-list.php
[954]: http://php.net/manual/zh/function.headers-sent.php
[955]: http://php.net/manual/zh/function.hebrev.php
[956]: http://php.net/manual/zh/function.hebrevc.php
[957]: http://php.net/manual/zh/function.hex2bin.php
[958]: http://php.net/manual/zh/function.hexdec.php
[959]: http://php.net/manual/zh/function.highlight-file.php
[960]: http://php.net/manual/zh/function.highlight-string.php
[961]: http://php.net/manual/zh/function.html-entity-decode.php
[962]: http://php.net/manual/zh/function.htmlentities.php
[963]: http://php.net/manual/zh/function.htmlspecialchars.php
[964]: http://php.net/manual/zh/function.htmlspecialchars-decode.php
[965]: http://php.net/manual/zh/function.http-build-query.php
[966]: http://php.net/manual/zh/function.http-response-code.php
[967]: http://php.net/manual/zh/function.hypot.php
[968]: http://php.net/manual/zh/function.ibase-affected-rows.php
[969]: http://php.net/manual/zh/function.ibase-backup.php
[970]: http://php.net/manual/zh/function.ibase-blob-add.php
[971]: http://php.net/manual/zh/function.ibase-blob-cancel.php
[972]: http://php.net/manual/zh/function.ibase-blob-close.php
[973]: http://php.net/manual/zh/function.ibase-blob-create.php
[974]: http://php.net/manual/zh/function.ibase-blob-echo.php
[975]: http://php.net/manual/zh/function.ibase-blob-get.php
[976]: http://php.net/manual/zh/function.ibase-blob-import.php
[977]: http://php.net/manual/zh/function.ibase-blob-info.php
[978]: http://php.net/manual/zh/function.ibase-blob-open.php
[979]: http://php.net/manual/zh/function.ibase-close.php
[980]: http://php.net/manual/zh/function.ibase-commit.php
[981]: http://php.net/manual/zh/function.ibase-commit-ret.php
[982]: http://php.net/manual/zh/function.ibase-connect.php
[983]: http://php.net/manual/zh/function.ibase-db-info.php
[984]: http://php.net/manual/zh/function.ibase-delete-user.php
[985]: http://php.net/manual/zh/function.ibase-drop-db.php
[986]: http://php.net/manual/zh/function.ibase-errcode.php
[987]: http://php.net/manual/zh/function.ibase-errmsg.php
[988]: http://php.net/manual/zh/function.ibase-execute.php
[989]: http://php.net/manual/zh/function.ibase-fetch-assoc.php
[990]: http://php.net/manual/zh/function.ibase-fetch-object.php
[991]: http://php.net/manual/zh/function.ibase-fetch-row.php
[992]: http://php.net/manual/zh/function.ibase-field-info.php
[993]: http://php.net/manual/zh/function.ibase-free-event-handler.php
[994]: http://php.net/manual/zh/function.ibase-free-query.php
[995]: http://php.net/manual/zh/function.ibase-free-result.php
[996]: http://php.net/manual/zh/function.ibase-gen-id.php
[997]: http://php.net/manual/zh/function.ibase-maintain-db.php
[998]: http://php.net/manual/zh/function.ibase-modify-user.php
[999]: http://php.net/manual/zh/function.ibase-name-result.php
[1000]: http://php.net/manual/zh/function.ibase-num-fields.php
[1001]: http://php.net/manual/zh/function.ibase-num-params.php
[1002]: http://php.net/manual/zh/function.ibase-param-info.php
[1003]: http://php.net/manual/zh/function.ibase-pconnect.php
[1004]: http://php.net/manual/zh/function.ibase-prepare.php
[1005]: http://php.net/manual/zh/function.ibase-query.php
[1006]: http://php.net/manual/zh/function.ibase-restore.php
[1007]: http://php.net/manual/zh/function.ibase-rollback.php
[1008]: http://php.net/manual/zh/function.ibase-rollback-ret.php
[1009]: http://php.net/manual/zh/function.ibase-server-info.php
[1010]: http://php.net/manual/zh/function.ibase-service-attach.php
[1011]: http://php.net/manual/zh/function.ibase-service-detach.php
[1012]: http://php.net/manual/zh/function.ibase-set-event-handler.php
[1013]: http://php.net/manual/zh/function.ibase-trans.php
[1014]: http://php.net/manual/zh/function.ibase-wait-event.php
[1015]: http://php.net/manual/zh/function.iconv.php
[1016]: http://php.net/manual/zh/function.iconv-get-encoding.php
[1017]: http://php.net/manual/zh/function.iconv-mime-decode.php
[1018]: http://php.net/manual/zh/function.iconv-mime-decode-headers.php
[1019]: http://php.net/manual/zh/function.iconv-mime-encode.php
[1020]: http://php.net/manual/zh/function.iconv-set-encoding.php
[1021]: http://php.net/manual/zh/function.iconv-strlen.php
[1022]: http://php.net/manual/zh/function.iconv-strpos.php
[1023]: http://php.net/manual/zh/function.iconv-strrpos.php
[1024]: http://php.net/manual/zh/function.iconv-substr.php
[1025]: http://php.net/manual/zh/function.idate.php
[1026]: http://php.net/manual/zh/function.idn-to-ascii.php
[1027]: http://php.net/manual/zh/function.idn-to-utf8.php
[1028]: http://php.net/manual/zh/function.ignore-user-abort.php
[1029]: http://php.net/manual/zh/function.image2wbmp.php
[1030]: http://php.net/manual/zh/function.image-type-to-extension.php
[1031]: http://php.net/manual/zh/function.image-type-to-mime-type.php
[1032]: http://php.net/manual/zh/function.imageaffine.php
[1033]: http://php.net/manual/zh/function.imageaffinematrixconcat.php
[1034]: http://php.net/manual/zh/function.imageaffinematrixget.php
[1035]: http://php.net/manual/zh/function.imagealphablending.php
[1036]: http://php.net/manual/zh/function.imageantialias.php
[1037]: http://php.net/manual/zh/function.imagearc.php
[1038]: http://php.net/manual/zh/function.imagechar.php
[1039]: http://php.net/manual/zh/function.imagecharup.php
[1040]: http://php.net/manual/zh/function.imagecolorallocate.php
[1041]: http://php.net/manual/zh/function.imagecolorallocatealpha.php
[1042]: http://php.net/manual/zh/function.imagecolorat.php
[1043]: http://php.net/manual/zh/function.imagecolorclosest.php
[1044]: http://php.net/manual/zh/function.imagecolorclosestalpha.php
[1045]: http://php.net/manual/zh/function.imagecolorclosesthwb.php
[1046]: http://php.net/manual/zh/function.imagecolordeallocate.php
[1047]: http://php.net/manual/zh/function.imagecolorexact.php
[1048]: http://php.net/manual/zh/function.imagecolorexactalpha.php
[1049]: http://php.net/manual/zh/function.imagecolormatch.php
[1050]: http://php.net/manual/zh/function.imagecolorresolve.php
[1051]: http://php.net/manual/zh/function.imagecolorresolvealpha.php
[1052]: http://php.net/manual/zh/function.imagecolorset.php
[1053]: http://php.net/manual/zh/function.imagecolorsforindex.php
[1054]: http://php.net/manual/zh/function.imagecolorstotal.php
[1055]: http://php.net/manual/zh/function.imagecolortransparent.php
[1056]: http://php.net/manual/zh/function.imageconvolution.php
[1057]: http://php.net/manual/zh/function.imagecopy.php
[1058]: http://php.net/manual/zh/function.imagecopymerge.php
[1059]: http://php.net/manual/zh/function.imagecopymergegray.php
[1060]: http://php.net/manual/zh/function.imagecopyresampled.php
[1061]: http://php.net/manual/zh/function.imagecopyresized.php
[1062]: http://php.net/manual/zh/function.imagecreate.php
[1063]: http://php.net/manual/zh/function.imagecreatefromgd.php
[1064]: http://php.net/manual/zh/function.imagecreatefromgd2.php
[1065]: http://php.net/manual/zh/function.imagecreatefromgd2part.php
[1066]: http://php.net/manual/zh/function.imagecreatefromgif.php
[1067]: http://php.net/manual/zh/function.imagecreatefromjpeg.php
[1068]: http://php.net/manual/zh/function.imagecreatefrompng.php
[1069]: http://php.net/manual/zh/function.imagecreatefromstring.php
[1070]: http://php.net/manual/zh/function.imagecreatefromwbmp.php
[1071]: http://php.net/manual/zh/function.imagecreatefromxbm.php
[1072]: http://php.net/manual/zh/function.imagecreatefromxpm.php
[1073]: http://php.net/manual/zh/function.imagecreatetruecolor.php
[1074]: http://php.net/manual/zh/function.imagecrop.php
[1075]: http://php.net/manual/zh/function.imagecropauto.php
[1076]: http://php.net/manual/zh/function.imagedashedline.php
[1077]: http://php.net/manual/zh/function.imagedestroy.php
[1078]: http://php.net/manual/zh/function.imageellipse.php
[1079]: http://php.net/manual/zh/function.imagefill.php
[1080]: http://php.net/manual/zh/function.imagefilledarc.php
[1081]: http://php.net/manual/zh/function.imagefilledellipse.php
[1082]: http://php.net/manual/zh/function.imagefilledpolygon.php
[1083]: http://php.net/manual/zh/function.imagefilledrectangle.php
[1084]: http://php.net/manual/zh/function.imagefilltoborder.php
[1085]: http://php.net/manual/zh/function.imagefilter.php
[1086]: http://php.net/manual/zh/function.imageflip.php
[1087]: http://php.net/manual/zh/function.imagefontheight.php
[1088]: http://php.net/manual/zh/function.imagefontwidth.php
[1089]: http://php.net/manual/zh/function.imageftbbox.php
[1090]: http://php.net/manual/zh/function.imagefttext.php
[1091]: http://php.net/manual/zh/function.imagegammacorrect.php
[1092]: http://php.net/manual/zh/function.imagegd.php
[1093]: http://php.net/manual/zh/function.imagegd2.php
[1094]: http://php.net/manual/zh/function.imagegif.php
[1095]: http://php.net/manual/zh/function.imageinterlace.php
[1096]: http://php.net/manual/zh/function.imageistruecolor.php
[1097]: http://php.net/manual/zh/function.imagejpeg.php
[1098]: http://php.net/manual/zh/function.imagelayereffect.php
[1099]: http://php.net/manual/zh/function.imageline.php
[1100]: http://php.net/manual/zh/function.imageloadfont.php
[1101]: http://php.net/manual/zh/function.imagepalettecopy.php
[1102]: http://php.net/manual/zh/function.imagepalettetotruecolor.php
[1103]: http://php.net/manual/zh/function.imagepng.php
[1104]: http://php.net/manual/zh/function.imagepolygon.php
[1105]: http://php.net/manual/zh/function.imagepsbbox.php
[1106]: http://php.net/manual/zh/function.imagepsencodefont.php
[1107]: http://php.net/manual/zh/function.imagepsextendfont.php
[1108]: http://php.net/manual/zh/function.imagepsfreefont.php
[1109]: http://php.net/manual/zh/function.imagepsloadfont.php
[1110]: http://php.net/manual/zh/function.imagepsslantfont.php
[1111]: http://php.net/manual/zh/function.imagepstext.php
[1112]: http://php.net/manual/zh/function.imagerectangle.php
[1113]: http://php.net/manual/zh/function.imagerotate.php
[1114]: http://php.net/manual/zh/function.imagesavealpha.php
[1115]: http://php.net/manual/zh/function.imagescale.php
[1116]: http://php.net/manual/zh/function.imagesetbrush.php
[1117]: http://php.net/manual/zh/function.imagesetinterpolation.php
[1118]: http://php.net/manual/zh/function.imagesetpixel.php
[1119]: http://php.net/manual/zh/function.imagesetstyle.php
[1120]: http://php.net/manual/zh/function.imagesetthickness.php
[1121]: http://php.net/manual/zh/function.imagesettile.php
[1122]: http://php.net/manual/zh/function.imagestring.php
[1123]: http://php.net/manual/zh/function.imagestringup.php
[1124]: http://php.net/manual/zh/function.imagesx.php
[1125]: http://php.net/manual/zh/function.imagesy.php
[1126]: http://php.net/manual/zh/function.imagetruecolortopalette.php
[1127]: http://php.net/manual/zh/function.imagettfbbox.php
[1128]: http://php.net/manual/zh/function.imagettftext.php
[1129]: http://php.net/manual/zh/function.imagetypes.php
[1130]: http://php.net/manual/zh/function.imagewbmp.php
[1131]: http://php.net/manual/zh/function.imagexbm.php
[1132]: http://php.net/manual/zh/function.imap-8bit.php
[1133]: http://php.net/manual/zh/function.imap-alerts.php
[1134]: http://php.net/manual/zh/function.imap-append.php
[1135]: http://php.net/manual/zh/function.imap-base64.php
[1136]: http://php.net/manual/zh/function.imap-binary.php
[1137]: http://php.net/manual/zh/function.imap-body.php
[1138]: http://php.net/manual/zh/function.imap-bodystruct.php
[1139]: http://php.net/manual/zh/function.imap-check.php
[1140]: http://php.net/manual/zh/function.imap-clearflag-full.php
[1141]: http://php.net/manual/zh/function.imap-close.php
[1142]: http://php.net/manual/zh/function.imap-create.php
[1143]: http://php.net/manual/zh/function.imap-createmailbox.php
[1144]: http://php.net/manual/zh/function.imap-createmailbox.php
[1145]: http://php.net/manual/zh/function.imap-delete.php
[1146]: http://php.net/manual/zh/function.imap-deletemailbox.php
[1147]: http://php.net/manual/zh/function.imap-errors.php
[1148]: http://php.net/manual/zh/function.imap-expunge.php
[1149]: http://php.net/manual/zh/function.imap-fetch-overview.php
[1150]: http://php.net/manual/zh/function.imap-fetchbody.php
[1151]: http://php.net/manual/zh/function.imap-fetchheader.php
[1152]: http://php.net/manual/zh/function.imap-fetchmime.php
[1153]: http://php.net/manual/zh/function.imap-fetchstructure.php
[1154]: http://php.net/manual/zh/function.imap-fetchtext.php
[1155]: http://php.net/manual/zh/function.imap-body.php
[1156]: http://php.net/manual/zh/function.imap-gc.php
[1157]: http://php.net/manual/zh/function.imap-get-quota.php
[1158]: http://php.net/manual/zh/function.imap-get-quotaroot.php
[1159]: http://php.net/manual/zh/function.imap-getacl.php
[1160]: http://php.net/manual/zh/function.imap-getmailboxes.php
[1161]: http://php.net/manual/zh/function.imap-getsubscribed.php
[1162]: http://php.net/manual/zh/function.imap-header.php
[1163]: http://php.net/manual/zh/function.imap-headerinfo.php
[1164]: http://php.net/manual/zh/function.imap-headerinfo.php
[1165]: http://php.net/manual/zh/function.imap-headers.php
[1166]: http://php.net/manual/zh/function.imap-last-error.php
[1167]: http://php.net/manual/zh/function.imap-list.php
[1168]: http://php.net/manual/zh/function.imap-listmailbox.php
[1169]: http://php.net/manual/zh/function.imap-list.php
[1170]: http://php.net/manual/zh/function.imap-listscan.php
[1171]: http://php.net/manual/zh/function.imap-listsubscribed.php
[1172]: http://php.net/manual/zh/function.imap-lsub.php
[1173]: http://php.net/manual/zh/function.imap-lsub.php
[1174]: http://php.net/manual/zh/function.imap-mail.php
[1175]: http://php.net/manual/zh/function.imap-mail-compose.php
[1176]: http://php.net/manual/zh/function.imap-mail-copy.php
[1177]: http://php.net/manual/zh/function.imap-mail-move.php
[1178]: http://php.net/manual/zh/function.imap-mailboxmsginfo.php
[1179]: http://php.net/manual/zh/function.imap-mime-header-decode.php
[1180]: http://php.net/manual/zh/function.imap-msgno.php
[1181]: http://php.net/manual/zh/function.imap-num-msg.php
[1182]: http://php.net/manual/zh/function.imap-num-recent.php
[1183]: http://php.net/manual/zh/function.imap-open.php
[1184]: http://php.net/manual/zh/function.imap-ping.php
[1185]: http://php.net/manual/zh/function.imap-qprint.php
[1186]: http://php.net/manual/zh/function.imap-rename.php
[1187]: http://php.net/manual/zh/function.imap-renamemailbox.php
[1188]: http://php.net/manual/zh/function.imap-renamemailbox.php
[1189]: http://php.net/manual/zh/function.imap-reopen.php
[1190]: http://php.net/manual/zh/function.imap-rfc822-parse-adrlist.php
[1191]: http://php.net/manual/zh/function.imap-rfc822-parse-headers.php
[1192]: http://php.net/manual/zh/function.imap-rfc822-write-address.php
[1193]: http://php.net/manual/zh/function.imap-savebody.php
[1194]: http://php.net/manual/zh/function.imap-scan.php
[1195]: http://php.net/manual/zh/function.imap-listscan.php
[1196]: http://php.net/manual/zh/function.imap-scanmailbox.php
[1197]: http://php.net/manual/zh/function.imap-listscan.php
[1198]: http://php.net/manual/zh/function.imap-search.php
[1199]: http://php.net/manual/zh/function.imap-set-quota.php
[1200]: http://php.net/manual/zh/function.imap-setacl.php
[1201]: http://php.net/manual/zh/function.imap-setflag-full.php
[1202]: http://php.net/manual/zh/function.imap-sort.php
[1203]: http://php.net/manual/zh/function.imap-status.php
[1204]: http://php.net/manual/zh/function.imap-subscribe.php
[1205]: http://php.net/manual/zh/function.imap-thread.php
[1206]: http://php.net/manual/zh/function.imap-timeout.php
[1207]: http://php.net/manual/zh/function.imap-uid.php
[1208]: http://php.net/manual/zh/function.imap-undelete.php
[1209]: http://php.net/manual/zh/function.imap-unsubscribe.php
[1210]: http://php.net/manual/zh/function.imap-utf7-decode.php
[1211]: http://php.net/manual/zh/function.imap-utf7-encode.php
[1212]: http://php.net/manual/zh/function.imap-utf8.php
[1213]: http://php.net/manual/zh/function.implode.php
[1214]: http://php.net/manual/zh/function.import-request-variables.php
[1215]: http://php.net/manual/zh/function.in-array.php
[1216]: http://php.net/manual/zh/function.inet-ntop.php
[1217]: http://php.net/manual/zh/function.inet-pton.php
[1218]: http://php.net/manual/zh/function.inflate-add.php
[1219]: http://php.net/manual/zh/function.inflate-init.php
[1220]: http://php.net/manual/zh/function.ini-alter.php
[1221]: http://php.net/manual/zh/function.ini-set.php
[1222]: http://php.net/manual/zh/function.ini-get.php
[1223]: http://php.net/manual/zh/function.ini-get-all.php
[1224]: http://php.net/manual/zh/function.ini-restore.php
[1225]: http://php.net/manual/zh/function.ini-set.php
[1226]: http://php.net/manual/zh/function.inotify-add-watch.php
[1227]: http://php.net/manual/zh/function.inotify-init.php
[1228]: http://php.net/manual/zh/function.inotify-queue-len.php
[1229]: http://php.net/manual/zh/function.inotify-read.php
[1230]: http://php.net/manual/zh/function.inotify-rm-watch.php
[1231]: http://php.net/manual/zh/function.intdiv.php
[1232]: http://php.net/manual/zh/function.interface-exists.php
[1233]: http://php.net/manual/zh/function.intl-error-name.php
[1234]: http://php.net/manual/zh/function.intl-get-error-code.php
[1235]: http://php.net/manual/zh/function.intl-get-error-message.php
[1236]: http://php.net/manual/zh/function.intl-is-failure.php
[1237]: http://php.net/manual/zh/intlcalendar.add.php
[1238]: http://php.net/manual/zh/intlcalendar.after.php
[1239]: http://php.net/manual/zh/intlcalendar.before.php
[1240]: http://php.net/manual/zh/intlcalendar.clear.php
[1241]: http://php.net/manual/zh/intlcalendar.equals.php
[1242]: http://php.net/manual/zh/intlcalendar.fielddifference.php
[1243]: http://php.net/manual/zh/intlcalendar.get.php
[1244]: http://php.net/manual/zh/intlcalendar.getactualmaximum.php
[1245]: http://php.net/manual/zh/intlcalendar.getactualminimum.php
[1246]: http://php.net/manual/zh/intlcalendar.getdayofweektype.php
[1247]: http://php.net/manual/zh/intlcalendar.geterrorcode.php
[1248]: http://php.net/manual/zh/intlcalendar.geterrormessage.php
[1249]: http://php.net/manual/zh/intlcalendar.getfirstdayofweek.php
[1250]: http://php.net/manual/zh/intlcalendar.getgreatestminimum.php
[1251]: http://php.net/manual/zh/intlcalendar.getleastmaximum.php
[1252]: http://php.net/manual/zh/intlcalendar.getlocale.php
[1253]: http://php.net/manual/zh/intlcalendar.getmaximum.php
[1254]: http://php.net/manual/zh/intlcalendar.getminimaldaysinfirstweek.php
[1255]: http://php.net/manual/zh/intlcalendar.getminimum.php
[1256]: http://php.net/manual/zh/intlcalendar.getrepeatedwalltimeoption.php
[1257]: http://php.net/manual/zh/intlcalendar.getskippedwalltimeoption.php
[1258]: http://php.net/manual/zh/intlcalendar.gettime.php
[1259]: http://php.net/manual/zh/intlcalendar.gettimezone.php
[1260]: http://php.net/manual/zh/intlcalendar.gettype.php
[1261]: http://php.net/manual/zh/intlcalendar.getweekendtransition.php
[1262]: http://php.net/manual/zh/intlcalendar.indaylighttime.php
[1263]: http://php.net/manual/zh/intlcalendar.isequivalentto.php
[1264]: http://php.net/manual/zh/intlcalendar.islenient.php
[1265]: http://php.net/manual/zh/intlcalendar.isweekend.php
[1266]: http://php.net/manual/zh/intlcalendar.roll.php
[1267]: http://php.net/manual/zh/intlcalendar.set.php
[1268]: http://php.net/manual/zh/intlcalendar.setfirstdayofweek.php
[1269]: http://php.net/manual/zh/intlcalendar.setlenient.php
[1270]: http://php.net/manual/zh/intlcalendar.setrepeatedwalltimeoption.php
[1271]: http://php.net/manual/zh/intlcalendar.setskippedwalltimeoption.php
[1272]: http://php.net/manual/zh/intlcalendar.settime.php
[1273]: http://php.net/manual/zh/intlcalendar.settimezone.php
[1274]: http://php.net/manual/zh/intlcalendar.todatetime.php
[1275]: http://php.net/manual/zh/intldateformatter.format.php
[1276]: http://php.net/manual/zh/intldateformatter.formatobject.php
[1277]: http://php.net/manual/zh/intldateformatter.getcalendar.php
[1278]: http://php.net/manual/zh/intldateformatter.getcalendarobject.php
[1279]: http://php.net/manual/zh/intldateformatter.getdatetype.php
[1280]: http://php.net/manual/zh/intldateformatter.geterrorcode.php
[1281]: http://php.net/manual/zh/intldateformatter.geterrormessage.php
[1282]: http://php.net/manual/zh/intldateformatter.getlocale.php
[1283]: http://php.net/manual/zh/intldateformatter.getpattern.php
[1284]: http://php.net/manual/zh/intldateformatter.gettimetype.php
[1285]: http://php.net/manual/zh/intldateformatter.gettimezone.php
[1286]: http://php.net/manual/zh/intldateformatter.gettimezoneid.php
[1287]: http://php.net/manual/zh/intldateformatter.islenient.php
[1288]: http://php.net/manual/zh/intldateformatter.localtime.php
[1289]: http://php.net/manual/zh/intldateformatter.parse.php
[1290]: http://php.net/manual/zh/intldateformatter.setcalendar.php
[1291]: http://php.net/manual/zh/intldateformatter.setlenient.php
[1292]: http://php.net/manual/zh/intldateformatter.setpattern.php
[1293]: http://php.net/manual/zh/intldateformatter.settimezone.php
[1294]: http://php.net/manual/zh/intldateformatter.settimezoneid.php
[1295]: http://php.net/manual/zh/intltimezone.getdisplayname.php
[1296]: http://php.net/manual/zh/intltimezone.getdstsavings.php
[1297]: http://php.net/manual/zh/intltimezone.geterrorcode.php
[1298]: http://php.net/manual/zh/intltimezone.geterrormessage.php
[1299]: http://php.net/manual/zh/intltimezone.getid.php
[1300]: http://php.net/manual/zh/intltimezone.getoffset.php
[1301]: http://php.net/manual/zh/intltimezone.getrawoffset.php
[1302]: http://php.net/manual/zh/intltimezone.hassamerules.php
[1303]: http://php.net/manual/zh/intltimezone.todatetimezone.php
[1304]: http://php.net/manual/zh/class.datetimezone.php
[1305]: http://php.net/manual/zh/intltimezone.usedaylighttime.php
[1306]: http://php.net/manual/zh/function.intval.php
[1307]: http://php.net/manual/zh/function.ip2long.php
[1308]: http://php.net/manual/zh/function.iptcembed.php
[1309]: http://php.net/manual/zh/function.iptcparse.php
[1310]: http://php.net/manual/zh/function.is-a.php
[1311]: http://php.net/manual/zh/function.is-array.php
[1312]: http://php.net/manual/zh/function.is-bool.php
[1313]: http://php.net/manual/zh/function.is-callable.php
[1314]: http://php.net/manual/zh/function.is-dir.php
[1315]: http://php.net/manual/zh/function.is-double.php
[1316]: http://php.net/manual/zh/function.is-float.php
[1317]: http://php.net/manual/zh/function.is-executable.php
[1318]: http://php.net/manual/zh/function.is-file.php
[1319]: http://php.net/manual/zh/function.is-finite.php
[1320]: http://php.net/manual/zh/function.is-float.php
[1321]: http://php.net/manual/zh/function.is-infinite.php
[1322]: http://php.net/manual/zh/function.is-int.php
[1323]: http://php.net/manual/zh/function.is-integer.php
[1324]: http://php.net/manual/zh/function.is-int.php
[1325]: http://php.net/manual/zh/function.is-iterable.php
[1326]: http://php.net/manual/zh/function.is-link.php
[1327]: http://php.net/manual/zh/function.is-long.php
[1328]: http://php.net/manual/zh/function.is-int.php
[1329]: http://php.net/manual/zh/function.is-nan.php
[1330]: http://php.net/manual/zh/function.is-null.php
[1331]: http://php.net/manual/zh/function.is-numeric.php
[1332]: http://php.net/manual/zh/function.is-object.php
[1333]: http://php.net/manual/zh/function.is-readable.php
[1334]: http://php.net/manual/zh/function.is-real.php
[1335]: http://php.net/manual/zh/function.is-float.php
[1336]: http://php.net/manual/zh/function.is-resource.php
[1337]: http://php.net/manual/zh/function.is-scalar.php
[1338]: http://php.net/manual/zh/function.is-soap-fault.php
[1339]: http://php.net/manual/zh/function.is-string.php
[1340]: http://php.net/manual/zh/function.is-subclass-of.php
[1341]: http://php.net/manual/zh/function.is-uploaded-file.php
[1342]: http://php.net/manual/zh/function.is-writable.php
[1343]: http://php.net/manual/zh/function.is-writeable.php
[1344]: http://php.net/manual/zh/function.is-writable.php
[1345]: http://php.net/manual/zh/function.iterator-apply.php
[1346]: http://php.net/manual/zh/function.iterator-count.php
[1347]: http://php.net/manual/zh/function.iterator-to-array.php
[1348]: http://php.net/manual/zh/function.jdmonthname.php
[1349]: http://php.net/manual/zh/function.jdtofrench.php
[1350]: http://php.net/manual/zh/function.jdtogregorian.php
[1351]: http://php.net/manual/zh/function.jdtojewish.php
[1352]: http://php.net/manual/zh/function.jdtojulian.php
[1353]: http://php.net/manual/zh/function.jdtounix.php
[1354]: http://php.net/manual/zh/function.jewishtojd.php
[1355]: http://php.net/manual/zh/function.join.php
[1356]: http://php.net/manual/zh/function.implode.php
[1357]: http://php.net/manual/zh/function.jpeg2wbmp.php
[1358]: http://php.net/manual/zh/function.json-decode.php
[1359]: http://php.net/manual/zh/function.json-encode.php
[1360]: http://php.net/manual/zh/function.json-last-error.php
[1361]: http://php.net/manual/zh/function.json-last-error-msg.php
[1362]: http://php.net/manual/zh/function.juliantojd.php
[1363]: http://php.net/manual/zh/function.key-exists.php
[1364]: http://php.net/manual/zh/function.array-key-exists.php
[1365]: http://php.net/manual/zh/function.krsort.php
[1366]: http://php.net/manual/zh/function.ksort.php
[1367]: http://php.net/manual/zh/function.lcg-value.php
[1368]: http://php.net/manual/zh/function.lchgrp.php
[1369]: http://php.net/manual/zh/function.lchown.php
[1370]: http://php.net/manual/zh/function.ldap-add.php
[1371]: http://php.net/manual/zh/function.ldap-bind.php
[1372]: http://php.net/manual/zh/function.ldap-close.php
[1373]: http://php.net/manual/zh/function.ldap-unbind.php
[1374]: http://php.net/manual/zh/function.ldap-compare.php
[1375]: http://php.net/manual/zh/function.ldap-connect.php
[1376]: http://php.net/manual/zh/function.ldap-control-paged-result.php
[1377]: http://php.net/manual/zh/function.ldap-control-paged-result-response.php
[1378]: http://php.net/manual/zh/function.ldap-count-entries.php
[1379]: http://php.net/manual/zh/function.ldap-delete.php
[1380]: http://php.net/manual/zh/function.ldap-dn2ufn.php
[1381]: http://php.net/manual/zh/function.ldap-err2str.php
[1382]: http://php.net/manual/zh/function.ldap-errno.php
[1383]: http://php.net/manual/zh/function.ldap-error.php
[1384]: http://php.net/manual/zh/function.ldap-escape.php
[1385]: http://php.net/manual/zh/function.ldap-explode-dn.php
[1386]: http://php.net/manual/zh/function.ldap-first-attribute.php
[1387]: http://php.net/manual/zh/function.ldap-first-entry.php
[1388]: http://php.net/manual/zh/function.ldap-first-reference.php
[1389]: http://php.net/manual/zh/function.ldap-free-result.php
[1390]: http://php.net/manual/zh/function.ldap-get-attributes.php
[1391]: http://php.net/manual/zh/function.ldap-get-dn.php
[1392]: http://php.net/manual/zh/function.ldap-get-entries.php
[1393]: http://php.net/manual/zh/function.ldap-get-option.php
[1394]: http://php.net/manual/zh/function.ldap-get-values.php
[1395]: http://php.net/manual/zh/function.ldap-get-values-len.php
[1396]: http://php.net/manual/zh/function.ldap-list.php
[1397]: http://php.net/manual/zh/function.ldap-mod-add.php
[1398]: http://php.net/manual/zh/function.ldap-mod-del.php
[1399]: http://php.net/manual/zh/function.ldap-mod-replace.php
[1400]: http://php.net/manual/zh/function.ldap-modify.php
[1401]: http://php.net/manual/zh/function.ldap-mod-replace.php
[1402]: http://php.net/manual/zh/function.ldap-modify-batch.php
[1403]: http://php.net/manual/zh/function.ldap-next-attribute.php
[1404]: http://php.net/manual/zh/function.ldap-next-entry.php
[1405]: http://php.net/manual/zh/function.ldap-next-reference.php
[1406]: http://php.net/manual/zh/function.ldap-parse-reference.php
[1407]: http://php.net/manual/zh/function.ldap-parse-result.php
[1408]: http://php.net/manual/zh/function.ldap-read.php
[1409]: http://php.net/manual/zh/function.ldap-rename.php
[1410]: http://php.net/manual/zh/function.ldap-sasl-bind.php
[1411]: http://php.net/manual/zh/function.ldap-search.php
[1412]: http://php.net/manual/zh/function.ldap-set-option.php
[1413]: http://php.net/manual/zh/function.ldap-set-rebind-proc.php
[1414]: http://php.net/manual/zh/function.ldap-sort.php
[1415]: http://php.net/manual/zh/function.ldap-start-tls.php
[1416]: http://php.net/manual/zh/function.ldap-unbind.php
[1417]: http://php.net/manual/zh/function.levenshtein.php
[1418]: http://php.net/manual/zh/function.libxml-clear-errors.php
[1419]: http://php.net/manual/zh/function.libxml-disable-entity-loader.php
[1420]: http://php.net/manual/zh/function.libxml-get-errors.php
[1421]: http://php.net/manual/zh/function.libxml-get-last-error.php
[1422]: http://php.net/manual/zh/function.libxml-set-external-entity-loader.php
[1423]: http://php.net/manual/zh/function.libxml-set-streams-context.php
[1424]: http://php.net/manual/zh/function.libxml-use-internal-errors.php
[1425]: http://php.net/manual/zh/function.link.php
[1426]: http://php.net/manual/zh/function.linkinfo.php
[1427]: http://php.net/manual/zh/function.locale-canonicalize.php
[1428]: http://php.net/manual/zh/function.locale-lookup.php
[1429]: http://php.net/manual/zh/function.localeconv.php
[1430]: http://php.net/manual/zh/function.localtime.php
[1431]: http://php.net/manual/zh/function.log.php
[1432]: http://php.net/manual/zh/function.log10.php
[1433]: http://php.net/manual/zh/function.log1p.php
[1434]: http://php.net/manual/zh/function.long2ip.php
[1435]: http://php.net/manual/zh/function.lstat.php
[1436]: http://php.net/manual/zh/function.ltrim.php
[1437]: http://php.net/manual/zh/function.mail.php
[1438]: http://php.net/manual/zh/function.max.php
[1439]: http://php.net/manual/zh/function.mb-check-encoding.php
[1440]: http://php.net/manual/zh/function.mb-convert-case.php
[1441]: http://php.net/manual/zh/function.mb-convert-encoding.php
[1442]: http://php.net/manual/zh/function.mb-convert-kana.php
[1443]: http://php.net/manual/zh/function.mb-convert-variables.php
[1444]: http://php.net/manual/zh/function.mb-decode-mimeheader.php
[1445]: http://php.net/manual/zh/function.mb-decode-numericentity.php
[1446]: http://php.net/manual/zh/function.mb-detect-encoding.php
[1447]: http://php.net/manual/zh/function.mb-detect-order.php
[1448]: http://php.net/manual/zh/function.mb-encode-mimeheader.php
[1449]: http://php.net/manual/zh/function.mb-encode-numericentity.php
[1450]: http://php.net/manual/zh/function.mb-encoding-aliases.php
[1451]: http://php.net/manual/zh/function.mb-ereg.php
[1452]: http://php.net/manual/zh/function.mb-ereg-match.php
[1453]: http://php.net/manual/zh/function.mb-ereg-replace.php
[1454]: http://php.net/manual/zh/function.mb-ereg-replace-callback.php
[1455]: http://php.net/manual/zh/function.mb-ereg-search.php
[1456]: http://php.net/manual/zh/function.mb-ereg-search-getpos.php
[1457]: http://php.net/manual/zh/function.mb-ereg-search-getregs.php
[1458]: http://php.net/manual/zh/function.mb-ereg-search-init.php
[1459]: http://php.net/manual/zh/function.mb-ereg-search-pos.php
[1460]: http://php.net/manual/zh/function.mb-ereg-search-regs.php
[1461]: http://php.net/manual/zh/function.mb-ereg-search-setpos.php
[1462]: http://php.net/manual/zh/function.mb-eregi.php
[1463]: http://php.net/manual/zh/function.mb-eregi-replace.php
[1464]: http://php.net/manual/zh/function.mb-get-info.php
[1465]: http://php.net/manual/zh/function.mb-http-input.php
[1466]: http://php.net/manual/zh/function.mb-http-output.php
[1467]: http://php.net/manual/zh/function.mb-internal-encoding.php
[1468]: http://php.net/manual/zh/function.mb-language.php
[1469]: http://php.net/manual/zh/function.mb-list-encodings.php
[1470]: http://php.net/manual/zh/function.mb-output-handler.php
[1471]: http://php.net/manual/zh/function.mb-parse-str.php
[1472]: http://php.net/manual/zh/function.mb-preferred-mime-name.php
[1473]: http://php.net/manual/zh/function.mb-regex-encoding.php
[1474]: http://php.net/manual/zh/function.mb-regex-set-options.php
[1475]: http://php.net/manual/zh/function.mb-send-mail.php
[1476]: http://php.net/manual/zh/function.mb-split.php
[1477]: http://php.net/manual/zh/function.mb-strcut.php
[1478]: http://php.net/manual/zh/function.mb-strimwidth.php
[1479]: http://php.net/manual/zh/function.mb-stripos.php
[1480]: http://php.net/manual/zh/function.mb-stristr.php
[1481]: http://php.net/manual/zh/function.mb-strlen.php
[1482]: http://php.net/manual/zh/function.mb-strpos.php
[1483]: http://php.net/manual/zh/function.mb-strrchr.php
[1484]: http://php.net/manual/zh/function.mb-strrichr.php
[1485]: http://php.net/manual/zh/function.mb-strripos.php
[1486]: http://php.net/manual/zh/function.mb-strrpos.php
[1487]: http://php.net/manual/zh/function.mb-strstr.php
[1488]: http://php.net/manual/zh/function.mb-strtolower.php
[1489]: http://php.net/manual/zh/function.mb-strtoupper.php
[1490]: http://php.net/manual/zh/function.mb-strwidth.php
[1491]: http://php.net/manual/zh/function.mb-substitute-character.php
[1492]: http://php.net/manual/zh/function.mb-substr.php
[1493]: http://php.net/manual/zh/function.mb-substr-count.php
[1494]: http://php.net/manual/zh/function.mcrypt-cbc.php
[1495]: http://php.net/manual/zh/function.mcrypt-cfb.php
[1496]: http://php.net/manual/zh/function.mcrypt-create-iv.php
[1497]: http://php.net/manual/zh/function.mcrypt-decrypt.php
[1498]: http://php.net/manual/zh/function.mcrypt-ecb.php
[1499]: http://php.net/manual/zh/function.mcrypt-enc-get-algorithms-name.php
[1500]: http://php.net/manual/zh/function.mcrypt-enc-get-block-size.php
[1501]: http://php.net/manual/zh/function.mcrypt-enc-get-iv-size.php
[1502]: http://php.net/manual/zh/function.mcrypt-enc-get-key-size.php
[1503]: http://php.net/manual/zh/function.mcrypt-enc-get-modes-name.php
[1504]: http://php.net/manual/zh/function.mcrypt-enc-get-supported-key-sizes.php
[1505]: http://php.net/manual/zh/function.mcrypt-enc-is-block-algorithm.php
[1506]: http://php.net/manual/zh/function.mcrypt-enc-is-block-algorithm-mode.php
[1507]: http://php.net/manual/zh/function.mcrypt-enc-is-block-mode.php
[1508]: http://php.net/manual/zh/function.mcrypt-enc-self-test.php
[1509]: http://php.net/manual/zh/function.mcrypt-encrypt.php
[1510]: http://php.net/manual/zh/function.mcrypt-generic.php
[1511]: http://php.net/manual/zh/function.mcrypt-generic-deinit.php
[1512]: http://php.net/manual/zh/function.mcrypt-generic-end.php
[1513]: http://php.net/manual/zh/function.mcrypt-generic-init.php
[1514]: http://php.net/manual/zh/function.mcrypt-get-block-size.php
[1515]: http://php.net/manual/zh/function.mcrypt-get-cipher-name.php
[1516]: http://php.net/manual/zh/function.mcrypt-get-iv-size.php
[1517]: http://php.net/manual/zh/function.mcrypt-get-key-size.php
[1518]: http://php.net/manual/zh/function.mcrypt-list-algorithms.php
[1519]: http://php.net/manual/zh/function.mcrypt-list-modes.php
[1520]: http://php.net/manual/zh/function.mcrypt-module-close.php
[1521]: http://php.net/manual/zh/function.mcrypt-module-get-algo-block-size.php
[1522]: http://php.net/manual/zh/function.mcrypt-module-get-algo-key-size.php
[1523]: http://php.net/manual/zh/function.mcrypt-module-get-supported-key-sizes.php
[1524]: http://php.net/manual/zh/function.mcrypt-module-is-block-algorithm.php
[1525]: http://php.net/manual/zh/function.mcrypt-module-is-block-algorithm-mode.php
[1526]: http://php.net/manual/zh/function.mcrypt-module-is-block-mode.php
[1527]: http://php.net/manual/zh/function.mcrypt-module-open.php
[1528]: http://php.net/manual/zh/function.mcrypt-module-self-test.php
[1529]: http://php.net/manual/zh/function.mcrypt-ofb.php
[1530]: http://php.net/manual/zh/function.md5.php
[1531]: http://php.net/manual/zh/function.md5-file.php
[1532]: http://php.net/manual/zh/function.mdecrypt-generic.php
[1533]: http://php.net/manual/zh/function.memcache-add.php
[1534]: http://php.net/manual/zh/function.memcache-close.php
[1535]: http://php.net/manual/zh/function.memcache-connect.php
[1536]: http://php.net/manual/zh/function.memcache-debug.php
[1537]: http://php.net/manual/zh/function.memcache-decrement.php
[1538]: http://php.net/manual/zh/function.memcache-delete.php
[1539]: http://php.net/manual/zh/function.memcache-flush.php
[1540]: http://php.net/manual/zh/function.memcache-get.php
[1541]: http://php.net/manual/zh/function.memcache-increment.php
[1542]: http://php.net/manual/zh/function.memcache-pconnect.php
[1543]: http://php.net/manual/zh/function.memcache-replace.php
[1544]: http://php.net/manual/zh/function.memcache-set.php
[1545]: http://php.net/manual/zh/function.memory-get-peak-usage.php
[1546]: http://php.net/manual/zh/function.memory-get-usage.php
[1547]: http://php.net/manual/zh/messageformatter.format.php
[1548]: http://php.net/manual/zh/messageformatter.geterrorcode.php
[1549]: http://php.net/manual/zh/messageformatter.geterrormessage.php
[1550]: http://php.net/manual/zh/messageformatter.getlocale.php
[1551]: http://php.net/manual/zh/messageformatter.getpattern.php
[1552]: http://php.net/manual/zh/messageformatter.parse.php
[1553]: http://php.net/manual/zh/messageformatter.setpattern.php
[1554]: http://php.net/manual/zh/function.metaphone.php
[1555]: http://php.net/manual/zh/function.method-exists.php
[1556]: http://php.net/manual/zh/function.mhash.php
[1557]: http://php.net/manual/zh/function.mhash-count.php
[1558]: http://php.net/manual/zh/function.mhash-get-block-size.php
[1559]: http://php.net/manual/zh/function.mhash-get-hash-name.php
[1560]: http://php.net/manual/zh/function.mhash-keygen-s2k.php
[1561]: http://php.net/manual/zh/function.microtime.php
[1562]: http://php.net/manual/zh/function.mime-content-type.php
[1563]: http://php.net/manual/zh/function.min.php
[1564]: http://php.net/manual/zh/function.ming-keypress.php
[1565]: http://php.net/manual/zh/function.ming-setcubicthreshold.php
[1566]: http://php.net/manual/zh/function.ming-setscale.php
[1567]: http://php.net/manual/zh/function.ming-setswfcompression.php
[1568]: http://php.net/manual/zh/function.ming-useconstants.php
[1569]: http://php.net/manual/zh/function.ming-useswfversion.php
[1570]: http://php.net/manual/zh/function.mkdir.php
[1571]: http://php.net/manual/zh/function.mktime.php
[1572]: http://php.net/manual/zh/function.money-format.php
[1573]: http://php.net/manual/zh/mongocollection.--tostring.php
[1574]: http://php.net/manual/zh/mongocollection.aggregate.php
[1575]: http://php.net/manual/zh/mongocollection.aggregatecursor.php
[1576]: http://php.net/manual/zh/mongocollection.batchinsert.php
[1577]: http://php.net/manual/zh/mongocollection.count.php
[1578]: http://php.net/manual/zh/mongocollection.createdbref.php
[1579]: http://php.net/manual/zh/mongocollection.createindex.php
[1580]: http://php.net/manual/zh/mongocollection.deleteindex.php
[1581]: http://php.net/manual/zh/mongocollection.deleteindexes.php
[1582]: http://php.net/manual/zh/mongocollection.distinct.php
[1583]: http://php.net/manual/zh/mongocollection.drop.php
[1584]: http://php.net/manual/zh/mongocollection.ensureindex.php
[1585]: http://php.net/manual/zh/mongocollection.find.php
[1586]: http://php.net/manual/zh/class.mongocursor.php
[1587]: http://php.net/manual/zh/mongocollection.findandmodify.php
[1588]: http://php.net/manual/zh/mongocollection.findone.php
[1589]: http://php.net/manual/zh/mongocollection.getdbref.php
[1590]: http://php.net/manual/zh/mongocollection.getindexinfo.php
[1591]: http://php.net/manual/zh/mongocollection.getname.php
[1592]: http://php.net/manual/zh/mongocollection.getreadpreference.php
[1593]: http://php.net/manual/zh/mongocollection.getslaveokay.php
[1594]: http://php.net/manual/zh/mongocollection.group.php
[1595]: http://php.net/manual/zh/mongocollection.insert.php
[1596]: http://php.net/manual/zh/mongocollection.remove.php
[1597]: http://php.net/manual/zh/mongocollection.save.php
[1598]: http://php.net/manual/zh/mongocollection.setreadpreference.php
[1599]: http://php.net/manual/zh/mongocollection.setslaveokay.php
[1600]: http://php.net/manual/zh/mongocollection.update.php
[1601]: http://php.net/manual/zh/mongocollection.validate.php
[1602]: http://php.net/manual/zh/mongodate.todatetime.php
[1603]: http://php.net/manual/zh/mongodb.--tostring.php
[1604]: http://php.net/manual/zh/mongodb.authenticate.php
[1605]: http://php.net/manual/zh/mongodb.command.php
[1606]: http://php.net/manual/zh/mongodb.createcollection.php
[1607]: http://php.net/manual/zh/mongodb.createdbref.php
[1608]: http://php.net/manual/zh/mongodb.drop.php
[1609]: http://php.net/manual/zh/mongodb.dropcollection.php
[1610]: http://php.net/manual/zh/mongodb.execute.php
[1611]: http://php.net/manual/zh/mongodb.forceerror.php
[1612]: http://php.net/manual/zh/mongodb.getcollectionnames.php
[1613]: http://php.net/manual/zh/mongodb.getdbref.php
[1614]: http://php.net/manual/zh/mongodb.getgridfs.php
[1615]: http://php.net/manual/zh/mongodb.getprofilinglevel.php
[1616]: http://php.net/manual/zh/mongodb.getreadpreference.php
[1617]: http://php.net/manual/zh/mongodb.getslaveokay.php
[1618]: http://php.net/manual/zh/mongodb.getwriteconcern.php
[1619]: http://php.net/manual/zh/mongodb.lasterror.php
[1620]: http://php.net/manual/zh/mongodb.listcollections.php
[1621]: http://php.net/manual/zh/mongodb.preverror.php
[1622]: http://php.net/manual/zh/mongodb.repair.php
[1623]: http://php.net/manual/zh/mongodb.reseterror.php
[1624]: http://php.net/manual/zh/mongodb.selectcollection.php
[1625]: http://php.net/manual/zh/mongodb.setprofilinglevel.php
[1626]: http://php.net/manual/zh/mongodb.setreadpreference.php
[1627]: http://php.net/manual/zh/mongodb.setslaveokay.php
[1628]: http://php.net/manual/zh/mongodb.setwriteconcern.php
[1629]: http://php.net/manual/zh/mongogridfsfile.getbytes.php
[1630]: http://php.net/manual/zh/mongogridfsfile.getfilename.php
[1631]: http://php.net/manual/zh/mongogridfsfile.getresource.php
[1632]: http://php.net/manual/zh/mongogridfsfile.getsize.php
[1633]: http://php.net/manual/zh/mongogridfsfile.write.php
[1634]: http://php.net/manual/zh/mongoid.getinc.php
[1635]: http://php.net/manual/zh/mongoid.getpid.php
[1636]: http://php.net/manual/zh/mongoid.gettimestamp.php
[1637]: http://php.net/manual/zh/function.move-uploaded-file.php
[1638]: http://php.net/manual/zh/function.msg-get-queue.php
[1639]: http://php.net/manual/zh/function.msg-queue-exists.php
[1640]: http://php.net/manual/zh/function.msg-receive.php
[1641]: http://php.net/manual/zh/function.msg-remove-queue.php
[1642]: http://php.net/manual/zh/function.msg-send.php
[1643]: http://php.net/manual/zh/function.msg-set-queue.php
[1644]: http://php.net/manual/zh/function.msg-stat-queue.php
[1645]: http://php.net/manual/zh/function.mssql-bind.php
[1646]: http://php.net/manual/zh/function.mssql-close.php
[1647]: http://php.net/manual/zh/function.mssql-connect.php
[1648]: http://php.net/manual/zh/function.mssql-data-seek.php
[1649]: http://php.net/manual/zh/function.mssql-execute.php
[1650]: http://php.net/manual/zh/function.mssql-fetch-array.php
[1651]: http://php.net/manual/zh/function.mssql-fetch-assoc.php
[1652]: http://php.net/manual/zh/function.mssql-fetch-batch.php
[1653]: http://php.net/manual/zh/function.mssql-fetch-field.php
[1654]: http://php.net/manual/zh/function.mssql-fetch-object.php
[1655]: http://php.net/manual/zh/function.mssql-fetch-row.php
[1656]: http://php.net/manual/zh/function.mssql-field-length.php
[1657]: http://php.net/manual/zh/function.mssql-field-name.php
[1658]: http://php.net/manual/zh/function.mssql-field-seek.php
[1659]: http://php.net/manual/zh/function.mssql-field-type.php
[1660]: http://php.net/manual/zh/function.mssql-free-result.php
[1661]: http://php.net/manual/zh/function.mssql-free-statement.php
[1662]: http://php.net/manual/zh/function.mssql-get-last-message.php
[1663]: http://php.net/manual/zh/function.mssql-guid-string.php
[1664]: http://php.net/manual/zh/function.mssql-init.php
[1665]: http://php.net/manual/zh/function.mssql-min-error-severity.php
[1666]: http://php.net/manual/zh/function.mssql-min-message-severity.php
[1667]: http://php.net/manual/zh/function.mssql-next-result.php
[1668]: http://php.net/manual/zh/function.mssql-num-fields.php
[1669]: http://php.net/manual/zh/function.mssql-num-rows.php
[1670]: http://php.net/manual/zh/function.mssql-pconnect.php
[1671]: http://php.net/manual/zh/function.mssql-query.php
[1672]: http://php.net/manual/zh/function.mssql-result.php
[1673]: http://php.net/manual/zh/function.mssql-rows-affected.php
[1674]: http://php.net/manual/zh/function.mssql-select-db.php
[1675]: http://php.net/manual/zh/function.mt-getrandmax.php
[1676]: http://php.net/manual/zh/function.mt-rand.php
[1677]: http://php.net/manual/zh/function.mt-srand.php
[1678]: http://php.net/manual/zh/function.mysql-affected-rows.php
[1679]: http://php.net/manual/zh/function.mysql-client-encoding.php
[1680]: http://php.net/manual/zh/function.mysql-close.php
[1681]: http://php.net/manual/zh/function.mysql-connect.php
[1682]: http://php.net/manual/zh/function.mysql-data-seek.php
[1683]: http://php.net/manual/zh/function.mysql-db-name.php
[1684]: http://php.net/manual/zh/function.mysql-db-query.php
[1685]: http://php.net/manual/zh/function.mysql-errno.php
[1686]: http://php.net/manual/zh/function.mysql-error.php
[1687]: http://php.net/manual/zh/function.mysql-escape-string.php
[1688]: http://php.net/manual/zh/function.mysql-fetch-array.php
[1689]: http://php.net/manual/zh/function.mysql-fetch-assoc.php
[1690]: http://php.net/manual/zh/function.mysql-fetch-field.php
[1691]: http://php.net/manual/zh/function.mysql-fetch-lengths.php
[1692]: http://php.net/manual/zh/function.mysql-fetch-object.php
[1693]: http://php.net/manual/zh/function.mysql-fetch-row.php
[1694]: http://php.net/manual/zh/function.mysql-field-flags.php
[1695]: http://php.net/manual/zh/function.mysql-field-len.php
[1696]: http://php.net/manual/zh/function.mysql-field-name.php
[1697]: http://php.net/manual/zh/function.mysql-field-seek.php
[1698]: http://php.net/manual/zh/function.mysql-field-table.php
[1699]: http://php.net/manual/zh/function.mysql-field-type.php
[1700]: http://php.net/manual/zh/function.mysql-free-result.php
[1701]: http://php.net/manual/zh/function.mysql-get-client-info.php
[1702]: http://php.net/manual/zh/function.mysql-get-host-info.php
[1703]: http://php.net/manual/zh/function.mysql-get-proto-info.php
[1704]: http://php.net/manual/zh/function.mysql-get-server-info.php
[1705]: http://php.net/manual/zh/function.mysql-info.php
[1706]: http://php.net/manual/zh/function.mysql-insert-id.php
[1707]: http://php.net/manual/zh/function.mysql-list-dbs.php
[1708]: http://php.net/manual/zh/function.mysql-list-fields.php
[1709]: http://php.net/manual/zh/function.mysql-list-processes.php
[1710]: http://php.net/manual/zh/function.mysql-list-tables.php
[1711]: http://php.net/manual/zh/function.mysql-num-fields.php
[1712]: http://php.net/manual/zh/function.mysql-num-rows.php
[1713]: http://php.net/manual/zh/function.mysql-pconnect.php
[1714]: http://php.net/manual/zh/function.mysql-ping.php
[1715]: http://php.net/manual/zh/function.mysql-query.php
[1716]: http://php.net/manual/zh/function.mysql-real-escape-string.php
[1717]: http://php.net/manual/zh/function.mysql-result.php
[1718]: http://php.net/manual/zh/function.mysql-select-db.php
[1719]: http://php.net/manual/zh/function.mysql-set-charset.php
[1720]: http://php.net/manual/zh/function.mysql-stat.php
[1721]: http://php.net/manual/zh/function.mysql-tablename.php
[1722]: http://php.net/manual/zh/function.mysql-thread-id.php
[1723]: http://php.net/manual/zh/function.mysql-unbuffered-query.php
[1724]: http://php.net/manual/zh/mysqli.begin-transaction.php
[1725]: http://php.net/manual/zh/mysqli.release-savepoint.php
[1726]: http://php.net/manual/zh/mysqli.savepoint.php
[1727]: http://php.net/manual/zh/function.mysqli-autocommit.php
[1728]: http://php.net/manual/zh/function.mysqli-bind-param.php
[1729]: http://php.net/manual/zh/mysqli-stmt.bind-param.php
[1730]: http://php.net/manual/zh/function.mysqli-bind-result.php
[1731]: http://php.net/manual/zh/mysqli-stmt.bind-result.php
[1732]: http://php.net/manual/zh/function.mysqli-client-encoding.php
[1733]: http://php.net/manual/zh/mysqli.character-set-name.php
[1734]: http://php.net/manual/zh/function.mysqli-close.php
[1735]: http://php.net/manual/zh/function.mysqli-commit.php
[1736]: http://php.net/manual/zh/function.mysqli-connect.php
[1737]: http://php.net/manual/zh/mysqli.construct.php
[1738]: http://php.net/manual/zh/function.mysqli-debug.php
[1739]: http://php.net/manual/zh/function.mysqli-errno.php
[1740]: http://php.net/manual/zh/function.mysqli-error.php
[1741]: http://php.net/manual/zh/function.mysqli-escape-string.php
[1742]: http://php.net/manual/zh/mysqli.real-escape-string.php
[1743]: http://php.net/manual/zh/function.mysqli-execute.php
[1744]: http://php.net/manual/zh/mysqli-stmt.execute.php
[1745]: http://php.net/manual/zh/function.mysqli-fetch.php
[1746]: http://php.net/manual/zh/mysqli-stmt.fetch.php
[1747]: http://php.net/manual/zh/function.mysqli-get-metadata.php
[1748]: http://php.net/manual/zh/mysqli-stmt.result-metadata.php
[1749]: http://php.net/manual/zh/function.mysqli-info.php
[1750]: http://php.net/manual/zh/function.mysqli-init.php
[1751]: http://php.net/manual/zh/function.mysqli-kill.php
[1752]: http://php.net/manual/zh/function.mysqli-options.php
[1753]: http://php.net/manual/zh/function.mysqli-param-count.php
[1754]: http://php.net/manual/zh/mysqli-stmt.param-count.php
[1755]: http://php.net/manual/zh/function.mysqli-ping.php
[1756]: http://php.net/manual/zh/function.mysqli-poll.php
[1757]: http://php.net/manual/zh/function.mysqli-prepare.php
[1758]: http://php.net/manual/zh/function.mysqli-query.php
[1759]: http://php.net/manual/zh/function.mysqli-refresh.php
[1760]: http://php.net/manual/zh/function.mysqli-report.php
[1761]: http://php.net/manual/zh/function.mysqli-rollback.php
[1762]: http://php.net/manual/zh/function.mysqli-savepoint.php
[1763]: http://php.net/manual/zh/function.mysqli-send-long-data.php
[1764]: http://php.net/manual/zh/mysqli-stmt.send-long-data.php
[1765]: http://php.net/manual/zh/function.mysqli-set-opt.php
[1766]: http://php.net/manual/zh/mysqli.options.php
[1767]: http://php.net/manual/zh/function.mysqli-sqlstate.php
[1768]: http://php.net/manual/zh/function.mysqli-stat.php
[1769]: http://php.net/manual/zh/function.mysqli-stmt-close.php
[1770]: http://php.net/manual/zh/function.mysqli-stmt-errno.php
[1771]: http://php.net/manual/zh/function.mysqli-stmt-error.php
[1772]: http://php.net/manual/zh/function.mysqli-stmt-execute.php
[1773]: http://php.net/manual/zh/function.mysqli-stmt-fetch.php
[1774]: http://php.net/manual/zh/function.mysqli-stmt-prepare.php
[1775]: http://php.net/manual/zh/function.mysqli-stmt-reset.php
[1776]: http://php.net/manual/zh/function.mysqli-stmt-sqlstate.php
[1777]: http://php.net/manual/zh/function.natsort.php
[1778]: http://php.net/manual/zh/function.ncurses-addch.php
[1779]: http://php.net/manual/zh/function.ncurses-addchnstr.php
[1780]: http://php.net/manual/zh/function.ncurses-addchstr.php
[1781]: http://php.net/manual/zh/function.ncurses-addnstr.php
[1782]: http://php.net/manual/zh/function.ncurses-addstr.php
[1783]: http://php.net/manual/zh/function.ncurses-assume-default-colors.php
[1784]: http://php.net/manual/zh/function.ncurses-attroff.php
[1785]: http://php.net/manual/zh/function.ncurses-attron.php
[1786]: http://php.net/manual/zh/function.ncurses-attrset.php
[1787]: http://php.net/manual/zh/function.ncurses-baudrate.php
[1788]: http://php.net/manual/zh/function.ncurses-beep.php
[1789]: http://php.net/manual/zh/function.ncurses-bkgd.php
[1790]: http://php.net/manual/zh/function.ncurses-bkgdset.php
[1791]: http://php.net/manual/zh/function.ncurses-border.php
[1792]: http://php.net/manual/zh/function.ncurses-bottom-panel.php
[1793]: http://php.net/manual/zh/function.ncurses-can-change-color.php
[1794]: http://php.net/manual/zh/function.ncurses-cbreak.php
[1795]: http://php.net/manual/zh/function.ncurses-clear.php
[1796]: http://php.net/manual/zh/function.ncurses-clrtobot.php
[1797]: http://php.net/manual/zh/function.ncurses-clrtoeol.php
[1798]: http://php.net/manual/zh/function.ncurses-color-content.php
[1799]: http://php.net/manual/zh/function.ncurses-color-set.php
[1800]: http://php.net/manual/zh/function.ncurses-curs-set.php
[1801]: http://php.net/manual/zh/function.ncurses-def-prog-mode.php
[1802]: http://php.net/manual/zh/function.ncurses-def-shell-mode.php
[1803]: http://php.net/manual/zh/function.ncurses-define-key.php
[1804]: http://php.net/manual/zh/function.ncurses-del-panel.php
[1805]: http://php.net/manual/zh/function.ncurses-delay-output.php
[1806]: http://php.net/manual/zh/function.ncurses-delch.php
[1807]: http://php.net/manual/zh/function.ncurses-deleteln.php
[1808]: http://php.net/manual/zh/function.ncurses-delwin.php
[1809]: http://php.net/manual/zh/function.ncurses-doupdate.php
[1810]: http://php.net/manual/zh/function.ncurses-echo.php
[1811]: http://php.net/manual/zh/function.ncurses-echochar.php
[1812]: http://php.net/manual/zh/function.ncurses-end.php
[1813]: http://php.net/manual/zh/function.ncurses-erase.php
[1814]: http://php.net/manual/zh/function.ncurses-erasechar.php
[1815]: http://php.net/manual/zh/function.ncurses-filter.php
[1816]: http://php.net/manual/zh/function.ncurses-flash.php
[1817]: http://php.net/manual/zh/function.ncurses-flushinp.php
[1818]: http://php.net/manual/zh/function.ncurses-getch.php
[1819]: http://php.net/manual/zh/function.ncurses-getmaxyx.php
[1820]: http://php.net/manual/zh/function.ncurses-getmouse.php
[1821]: http://php.net/manual/zh/function.ncurses-getyx.php
[1822]: http://php.net/manual/zh/function.ncurses-halfdelay.php
[1823]: http://php.net/manual/zh/function.ncurses-has-colors.php
[1824]: http://php.net/manual/zh/function.ncurses-has-ic.php
[1825]: http://php.net/manual/zh/function.ncurses-has-il.php
[1826]: http://php.net/manual/zh/function.ncurses-has-key.php
[1827]: http://php.net/manual/zh/function.ncurses-hide-panel.php
[1828]: http://php.net/manual/zh/function.ncurses-hline.php
[1829]: http://php.net/manual/zh/function.ncurses-inch.php
[1830]: http://php.net/manual/zh/function.ncurses-init.php
[1831]: http://php.net/manual/zh/function.ncurses-init-color.php
[1832]: http://php.net/manual/zh/function.ncurses-init-pair.php
[1833]: http://php.net/manual/zh/function.ncurses-insch.php
[1834]: http://php.net/manual/zh/function.ncurses-insdelln.php
[1835]: http://php.net/manual/zh/function.ncurses-insertln.php
[1836]: http://php.net/manual/zh/function.ncurses-insstr.php
[1837]: http://php.net/manual/zh/function.ncurses-instr.php
[1838]: http://php.net/manual/zh/function.ncurses-isendwin.php
[1839]: http://php.net/manual/zh/function.ncurses-keyok.php
[1840]: http://php.net/manual/zh/function.ncurses-keypad.php
[1841]: http://php.net/manual/zh/function.ncurses-killchar.php
[1842]: http://php.net/manual/zh/function.ncurses-longname.php
[1843]: http://php.net/manual/zh/function.ncurses-meta.php
[1844]: http://php.net/manual/zh/function.ncurses-mouse-trafo.php
[1845]: http://php.net/manual/zh/function.ncurses-mouseinterval.php
[1846]: http://php.net/manual/zh/function.ncurses-mousemask.php
[1847]: http://php.net/manual/zh/function.ncurses-move.php
[1848]: http://php.net/manual/zh/function.ncurses-move-panel.php
[1849]: http://php.net/manual/zh/function.ncurses-mvaddch.php
[1850]: http://php.net/manual/zh/function.ncurses-mvaddchnstr.php
[1851]: http://php.net/manual/zh/function.ncurses-mvaddchstr.php
[1852]: http://php.net/manual/zh/function.ncurses-mvaddnstr.php
[1853]: http://php.net/manual/zh/function.ncurses-mvaddstr.php
[1854]: http://php.net/manual/zh/function.ncurses-mvcur.php
[1855]: http://php.net/manual/zh/function.ncurses-mvdelch.php
[1856]: http://php.net/manual/zh/function.ncurses-mvgetch.php
[1857]: http://php.net/manual/zh/function.ncurses-mvhline.php
[1858]: http://php.net/manual/zh/function.ncurses-mvinch.php
[1859]: http://php.net/manual/zh/function.ncurses-mvwaddstr.php
[1860]: http://php.net/manual/zh/function.ncurses-napms.php
[1861]: http://php.net/manual/zh/function.ncurses-new-panel.php
[1862]: http://php.net/manual/zh/function.ncurses-newpad.php
[1863]: http://php.net/manual/zh/function.ncurses-newwin.php
[1864]: http://php.net/manual/zh/function.ncurses-nl.php
[1865]: http://php.net/manual/zh/function.ncurses-nocbreak.php
[1866]: http://php.net/manual/zh/function.ncurses-noecho.php
[1867]: http://php.net/manual/zh/function.ncurses-nonl.php
[1868]: http://php.net/manual/zh/function.ncurses-noqiflush.php
[1869]: http://php.net/manual/zh/function.ncurses-noraw.php
[1870]: http://php.net/manual/zh/function.ncurses-pair-content.php
[1871]: http://php.net/manual/zh/function.ncurses-panel-above.php
[1872]: http://php.net/manual/zh/function.ncurses-panel-below.php
[1873]: http://php.net/manual/zh/function.ncurses-panel-window.php
[1874]: http://php.net/manual/zh/function.ncurses-pnoutrefresh.php
[1875]: http://php.net/manual/zh/function.ncurses-prefresh.php
[1876]: http://php.net/manual/zh/function.ncurses-putp.php
[1877]: http://php.net/manual/zh/function.ncurses-qiflush.php
[1878]: http://php.net/manual/zh/function.ncurses-raw.php
[1879]: http://php.net/manual/zh/function.ncurses-refresh.php
[1880]: http://php.net/manual/zh/function.ncurses-replace-panel.php
[1881]: http://php.net/manual/zh/function.ncurses-reset-prog-mode.php
[1882]: http://php.net/manual/zh/function.ncurses-reset-shell-mode.php
[1883]: http://php.net/manual/zh/function.ncurses-resetty.php
[1884]: http://php.net/manual/zh/function.ncurses-savetty.php
[1885]: http://php.net/manual/zh/function.ncurses-scr-dump.php
[1886]: http://php.net/manual/zh/function.ncurses-scr-init.php
[1887]: http://php.net/manual/zh/function.ncurses-scr-restore.php
[1888]: http://php.net/manual/zh/function.ncurses-scr-set.php
[1889]: http://php.net/manual/zh/function.ncurses-scrl.php
[1890]: http://php.net/manual/zh/function.ncurses-show-panel.php
[1891]: http://php.net/manual/zh/function.ncurses-slk-attr.php
[1892]: http://php.net/manual/zh/function.ncurses-slk-attroff.php
[1893]: http://php.net/manual/zh/function.ncurses-slk-attron.php
[1894]: http://php.net/manual/zh/function.ncurses-slk-attrset.php
[1895]: http://php.net/manual/zh/function.ncurses-slk-clear.php
[1896]: http://php.net/manual/zh/function.ncurses-slk-color.php
[1897]: http://php.net/manual/zh/function.ncurses-slk-init.php
[1898]: http://php.net/manual/zh/function.ncurses-slk-noutrefresh.php
[1899]: http://php.net/manual/zh/function.ncurses-slk-refresh.php
[1900]: http://php.net/manual/zh/function.ncurses-slk-restore.php
[1901]: http://php.net/manual/zh/function.ncurses-slk-set.php
[1902]: http://php.net/manual/zh/function.ncurses-slk-touch.php
[1903]: http://php.net/manual/zh/function.ncurses-standend.php
[1904]: http://php.net/manual/zh/function.ncurses-standout.php
[1905]: http://php.net/manual/zh/function.ncurses-start-color.php
[1906]: http://php.net/manual/zh/function.ncurses-termattrs.php
[1907]: http://php.net/manual/zh/function.ncurses-termname.php
[1908]: http://php.net/manual/zh/function.ncurses-timeout.php
[1909]: http://php.net/manual/zh/function.ncurses-top-panel.php
[1910]: http://php.net/manual/zh/function.ncurses-typeahead.php
[1911]: http://php.net/manual/zh/function.ncurses-ungetch.php
[1912]: http://php.net/manual/zh/function.ncurses-ungetmouse.php
[1913]: http://php.net/manual/zh/function.ncurses-update-panels.php
[1914]: http://php.net/manual/zh/function.ncurses-use-default-colors.php
[1915]: http://php.net/manual/zh/function.ncurses-use-env.php
[1916]: http://php.net/manual/zh/function.ncurses-use-extended-names.php
[1917]: http://php.net/manual/zh/function.ncurses-vidattr.php
[1918]: http://php.net/manual/zh/function.ncurses-vline.php
[1919]: http://php.net/manual/zh/function.ncurses-waddch.php
[1920]: http://php.net/manual/zh/function.ncurses-waddstr.php
[1921]: http://php.net/manual/zh/function.ncurses-wattroff.php
[1922]: http://php.net/manual/zh/function.ncurses-wattron.php
[1923]: http://php.net/manual/zh/function.ncurses-wattrset.php
[1924]: http://php.net/manual/zh/function.ncurses-wborder.php
[1925]: http://php.net/manual/zh/function.ncurses-wclear.php
[1926]: http://php.net/manual/zh/function.ncurses-wcolor-set.php
[1927]: http://php.net/manual/zh/function.ncurses-werase.php
[1928]: http://php.net/manual/zh/function.ncurses-wgetch.php
[1929]: http://php.net/manual/zh/function.ncurses-whline.php
[1930]: http://php.net/manual/zh/function.ncurses-wmouse-trafo.php
[1931]: http://php.net/manual/zh/function.ncurses-wmove.php
[1932]: http://php.net/manual/zh/function.ncurses-wnoutrefresh.php
[1933]: http://php.net/manual/zh/function.ncurses-wrefresh.php
[1934]: http://php.net/manual/zh/function.ncurses-wstandend.php
[1935]: http://php.net/manual/zh/function.ncurses-wstandout.php
[1936]: http://php.net/manual/zh/function.ncurses-wvline.php
[1937]: http://php.net/manual/zh/function.next.php
[1938]: http://php.net/manual/zh/function.ngettext.php
[1939]: http://php.net/manual/zh/function.nl2br.php
[1940]: http://php.net/manual/zh/function.nl-langinfo.php
[1941]: http://php.net/manual/zh/function.normalizer-normalize.php
[1942]: http://php.net/manual/zh/function.number-format.php
[1943]: http://php.net/manual/zh/numberformatter.format.php
[1944]: http://php.net/manual/zh/numberformatter.formatcurrency.php
[1945]: http://php.net/manual/zh/numberformatter.getattribute.php
[1946]: http://php.net/manual/zh/numberformatter.geterrorcode.php
[1947]: http://php.net/manual/zh/numberformatter.geterrormessage.php
[1948]: http://php.net/manual/zh/numberformatter.getlocale.php
[1949]: http://php.net/manual/zh/numberformatter.getpattern.php
[1950]: http://php.net/manual/zh/numberformatter.getsymbol.php
[1951]: http://php.net/manual/zh/numberformatter.gettextattribute.php
[1952]: http://php.net/manual/zh/numberformatter.parse.php
[1953]: http://php.net/manual/zh/numberformatter.parsecurrency.php
[1954]: http://php.net/manual/zh/numberformatter.setattribute.php
[1955]: http://php.net/manual/zh/numberformatter.setpattern.php
[1956]: http://php.net/manual/zh/numberformatter.setsymbol.php
[1957]: http://php.net/manual/zh/numberformatter.settextattribute.php
[1958]: http://php.net/manual/zh/oauth.disableredirects.php
[1959]: http://php.net/manual/zh/oauth.disablesslchecks.php
[1960]: http://php.net/manual/zh/oauth.enabledebug.php
[1961]: http://php.net/manual/zh/oauth.enableredirects.php
[1962]: http://php.net/manual/zh/oauth.enablesslchecks.php
[1963]: http://php.net/manual/zh/oauth.fetch.php
[1964]: http://php.net/manual/zh/oauth.getaccesstoken.php
[1965]: http://php.net/manual/zh/oauth.getcapath.php
[1966]: http://php.net/manual/zh/oauth.getlastresponse.php
[1967]: http://php.net/manual/zh/oauth.getlastresponseheaders.php
[1968]: http://php.net/manual/zh/oauth.getlastresponseinfo.php
[1969]: http://php.net/manual/zh/oauth.getrequestheader.php
[1970]: http://php.net/manual/zh/oauth.getrequesttoken.php
[1971]: http://php.net/manual/zh/oauth.setauthtype.php
[1972]: http://php.net/manual/zh/oauth.setcapath.php
[1973]: http://php.net/manual/zh/oauth.setnonce.php
[1974]: http://php.net/manual/zh/oauth.setrequestengine.php
[1975]: http://php.net/manual/zh/oauth.setrsacertificate.php
[1976]: http://php.net/manual/zh/oauth.settimestamp.php
[1977]: http://php.net/manual/zh/oauth.settoken.php
[1978]: http://php.net/manual/zh/oauth.setversion.php
[1979]: http://php.net/manual/zh/function.oauth-get-sbs.php
[1980]: http://php.net/manual/zh/function.oauth-urlencode.php
[1981]: http://php.net/manual/zh/oauthprovider.callconsumerhandler.php
[1982]: http://php.net/manual/zh/oauthprovider.calltimestampnoncehandler.php
[1983]: http://php.net/manual/zh/oauthprovider.calltokenhandler.php
[1984]: http://php.net/manual/zh/oauthprovider.checkoauthrequest.php
[1985]: http://php.net/manual/zh/oauthprovider.consumerhandler.php
[1986]: http://php.net/manual/zh/oauthprovider.is2leggedendpoint.php
[1987]: http://php.net/manual/zh/oauthprovider.isrequesttokenendpoint.php
[1988]: http://php.net/manual/zh/oauthprovider.timestampnoncehandler.php
[1989]: http://php.net/manual/zh/oauthprovider.tokenhandler.php
[1990]: http://php.net/manual/zh/function.ob-clean.php
[1991]: http://php.net/manual/zh/function.ob-end-clean.php
[1992]: http://php.net/manual/zh/function.ob-end-flush.php
[1993]: http://php.net/manual/zh/function.ob-flush.php
[1994]: http://php.net/manual/zh/function.ob-get-clean.php
[1995]: http://php.net/manual/zh/function.ob-get-contents.php
[1996]: http://php.net/manual/zh/function.ob-get-flush.php
[1997]: http://php.net/manual/zh/function.ob-get-length.php
[1998]: http://php.net/manual/zh/function.ob-get-level.php
[1999]: http://php.net/manual/zh/function.ob-get-status.php
[2000]: http://php.net/manual/zh/function.ob-gzhandler.php
[2001]: http://php.net/manual/zh/function.ob-iconv-handler.php
[2002]: http://php.net/manual/zh/function.ob-implicit-flush.php
[2003]: http://php.net/manual/zh/function.ob-list-handlers.php
[2004]: http://php.net/manual/zh/function.ob-start.php
[2005]: http://php.net/manual/zh/function.ob-tidyhandler.php
[2006]: http://php.net/manual/zh/function.oci-bind-array-by-name.php
[2007]: http://php.net/manual/zh/function.oci-bind-by-name.php
[2008]: http://php.net/manual/zh/function.oci-cancel.php
[2009]: http://php.net/manual/zh/function.oci-client-version.php
[2010]: http://php.net/manual/zh/function.oci-close.php
[2011]: http://php.net/manual/zh/function.oci-commit.php
[2012]: http://php.net/manual/zh/function.oci-connect.php
[2013]: http://php.net/manual/zh/function.oci-define-by-name.php
[2014]: http://php.net/manual/zh/function.oci-error.php
[2015]: http://php.net/manual/zh/function.oci-execute.php
[2016]: http://php.net/manual/zh/function.oci-fetch.php
[2017]: http://php.net/manual/zh/function.oci-fetch-all.php
[2018]: http://php.net/manual/zh/function.oci-fetch-array.php
[2019]: http://php.net/manual/zh/function.oci-fetch-assoc.php
[2020]: http://php.net/manual/zh/function.oci-fetch-object.php
[2021]: http://php.net/manual/zh/function.oci-fetch-row.php
[2022]: http://php.net/manual/zh/function.oci-field-is-null.php
[2023]: http://php.net/manual/zh/function.oci-field-name.php
[2024]: http://php.net/manual/zh/function.oci-field-precision.php
[2025]: http://php.net/manual/zh/function.oci-field-scale.php
[2026]: http://php.net/manual/zh/function.oci-field-size.php
[2027]: http://php.net/manual/zh/function.oci-field-type.php
[2028]: http://php.net/manual/zh/function.oci-field-type-raw.php
[2029]: http://php.net/manual/zh/function.oci-free-descriptor.php
[2030]: http://php.net/manual/zh/function.oci-free-statement.php
[2031]: http://php.net/manual/zh/function.oci-get-implicit-resultset.php
[2032]: http://php.net/manual/zh/function.oci-internal-debug.php
[2033]: http://php.net/manual/zh/function.oci-lob-copy.php
[2034]: http://php.net/manual/zh/function.oci-lob-is-equal.php
[2035]: http://php.net/manual/zh/function.oci-new-collection.php
[2036]: http://php.net/manual/zh/function.oci-new-connect.php
[2037]: http://php.net/manual/zh/function.oci-new-cursor.php
[2038]: http://php.net/manual/zh/function.oci-new-descriptor.php
[2039]: http://php.net/manual/zh/function.oci-num-fields.php
[2040]: http://php.net/manual/zh/function.oci-num-rows.php
[2041]: http://php.net/manual/zh/function.oci-parse.php
[2042]: http://php.net/manual/zh/function.oci-password-change.php
[2043]: http://php.net/manual/zh/function.oci-pconnect.php
[2044]: http://php.net/manual/zh/function.oci-result.php
[2045]: http://php.net/manual/zh/function.oci-rollback.php
[2046]: http://php.net/manual/zh/function.oci-server-version.php
[2047]: http://php.net/manual/zh/function.oci-set-action.php
[2048]: http://php.net/manual/zh/function.oci-set-client-identifier.php
[2049]: http://php.net/manual/zh/function.oci-set-client-info.php
[2050]: http://php.net/manual/zh/function.oci-set-edition.php
[2051]: http://php.net/manual/zh/function.oci-set-module-name.php
[2052]: http://php.net/manual/zh/function.oci-set-prefetch.php
[2053]: http://php.net/manual/zh/function.oci-statement-type.php
[2054]: http://php.net/manual/zh/function.ocibindbyname.php
[2055]: http://php.net/manual/zh/function.oci-bind-by-name.php
[2056]: http://php.net/manual/zh/function.ocicancel.php
[2057]: http://php.net/manual/zh/function.oci-cancel.php
[2058]: http://php.net/manual/zh/function.ocicloselob.php
[2059]: http://php.net/manual/zh/oci-lob.close.php
[2060]: http://php.net/manual/zh/function.ocicollappend.php
[2061]: http://php.net/manual/zh/oci-collection.append.php
[2062]: http://php.net/manual/zh/function.ocicollassign.php
[2063]: http://php.net/manual/zh/oci-collection.assign.php
[2064]: http://php.net/manual/zh/function.ocicollassignelem.php
[2065]: http://php.net/manual/zh/oci-collection.assignelem.php
[2066]: http://php.net/manual/zh/function.ocicollgetelem.php
[2067]: http://php.net/manual/zh/oci-collection.getelem.php
[2068]: http://php.net/manual/zh/function.ocicollmax.php
[2069]: http://php.net/manual/zh/oci-collection.max.php
[2070]: http://php.net/manual/zh/function.ocicollsize.php
[2071]: http://php.net/manual/zh/oci-collection.size.php
[2072]: http://php.net/manual/zh/function.ocicolltrim.php
[2073]: http://php.net/manual/zh/oci-collection.trim.php
[2074]: http://php.net/manual/zh/function.ocicolumnisnull.php
[2075]: http://php.net/manual/zh/function.oci-field-is-null.php
[2076]: http://php.net/manual/zh/function.ocicolumnname.php
[2077]: http://php.net/manual/zh/function.oci-field-name.php
[2078]: http://php.net/manual/zh/function.ocicolumnprecision.php
[2079]: http://php.net/manual/zh/function.oci-field-precision.php
[2080]: http://php.net/manual/zh/function.ocicolumnscale.php
[2081]: http://php.net/manual/zh/function.oci-field-scale.php
[2082]: http://php.net/manual/zh/function.ocicolumnsize.php
[2083]: http://php.net/manual/zh/function.oci-field-size.php
[2084]: http://php.net/manual/zh/function.ocicolumntype.php
[2085]: http://php.net/manual/zh/function.oci-field-type.php
[2086]: http://php.net/manual/zh/function.ocicolumntyperaw.php
[2087]: http://php.net/manual/zh/function.oci-field-type-raw.php
[2088]: http://php.net/manual/zh/function.ocicommit.php
[2089]: http://php.net/manual/zh/function.oci-commit.php
[2090]: http://php.net/manual/zh/function.ocidefinebyname.php
[2091]: http://php.net/manual/zh/function.oci-define-by-name.php
[2092]: http://php.net/manual/zh/function.ocierror.php
[2093]: http://php.net/manual/zh/function.oci-error.php
[2094]: http://php.net/manual/zh/function.ociexecute.php
[2095]: http://php.net/manual/zh/function.oci-execute.php
[2096]: http://php.net/manual/zh/function.ocifetch.php
[2097]: http://php.net/manual/zh/function.oci-fetch.php
[2098]: http://php.net/manual/zh/function.ocifetchinto.php
[2099]: http://php.net/manual/zh/function.oci-fetch-array.php
[2100]: http://php.net/manual/zh/function.oci-fetch-object.php
[2101]: http://php.net/manual/zh/function.oci-fetch-assoc.php
[2102]: http://php.net/manual/zh/function.oci-fetch-row.php
[2103]: http://php.net/manual/zh/function.ocifetchstatement.php
[2104]: http://php.net/manual/zh/function.oci-fetch-all.php
[2105]: http://php.net/manual/zh/function.ocifreecollection.php
[2106]: http://php.net/manual/zh/oci-collection.free.php
[2107]: http://php.net/manual/zh/function.ocifreecursor.php
[2108]: http://php.net/manual/zh/function.oci-free-statement.php
[2109]: http://php.net/manual/zh/function.ocifreedesc.php
[2110]: http://php.net/manual/zh/oci-lob.free.php
[2111]: http://php.net/manual/zh/function.ocifreestatement.php
[2112]: http://php.net/manual/zh/function.oci-free-statement.php
[2113]: http://php.net/manual/zh/function.ociinternaldebug.php
[2114]: http://php.net/manual/zh/function.oci-internal-debug.php
[2115]: http://php.net/manual/zh/function.ociloadlob.php
[2116]: http://php.net/manual/zh/oci-lob.load.php
[2117]: http://php.net/manual/zh/function.ocilogoff.php
[2118]: http://php.net/manual/zh/function.oci-close.php
[2119]: http://php.net/manual/zh/function.ocilogon.php
[2120]: http://php.net/manual/zh/function.oci-connect.php
[2121]: http://php.net/manual/zh/function.ocinewcollection.php
[2122]: http://php.net/manual/zh/function.oci-new-collection.php
[2123]: http://php.net/manual/zh/function.ocinewcursor.php
[2124]: http://php.net/manual/zh/function.oci-new-cursor.php
[2125]: http://php.net/manual/zh/function.ocinewdescriptor.php
[2126]: http://php.net/manual/zh/function.oci-new-descriptor.php
[2127]: http://php.net/manual/zh/function.ocinlogon.php
[2128]: http://php.net/manual/zh/function.oci-new-connect.php
[2129]: http://php.net/manual/zh/function.ocinumcols.php
[2130]: http://php.net/manual/zh/function.oci-num-fields.php
[2131]: http://php.net/manual/zh/function.ociparse.php
[2132]: http://php.net/manual/zh/function.oci-parse.php
[2133]: http://php.net/manual/zh/function.ociplogon.php
[2134]: http://php.net/manual/zh/function.oci-pconnect.php
[2135]: http://php.net/manual/zh/function.ociresult.php
[2136]: http://php.net/manual/zh/function.oci-result.php
[2137]: http://php.net/manual/zh/function.ocirollback.php
[2138]: http://php.net/manual/zh/function.oci-rollback.php
[2139]: http://php.net/manual/zh/function.ocirowcount.php
[2140]: http://php.net/manual/zh/function.oci-num-rows.php
[2141]: http://php.net/manual/zh/function.ocisavelob.php
[2142]: http://php.net/manual/zh/oci-lob.save.php
[2143]: http://php.net/manual/zh/function.ocisavelobfile.php
[2144]: http://php.net/manual/zh/oci-lob.import.php
[2145]: http://php.net/manual/zh/function.ociserverversion.php
[2146]: http://php.net/manual/zh/function.oci-server-version.php
[2147]: http://php.net/manual/zh/function.ocisetprefetch.php
[2148]: http://php.net/manual/zh/function.oci-set-prefetch.php
[2149]: http://php.net/manual/zh/function.ocistatementtype.php
[2150]: http://php.net/manual/zh/function.oci-statement-type.php
[2151]: http://php.net/manual/zh/function.ociwritelobtofile.php
[2152]: http://php.net/manual/zh/oci-lob.export.php
[2153]: http://php.net/manual/zh/function.ociwritetemporarylob.php
[2154]: http://php.net/manual/zh/oci-lob.writetemporary.php
[2155]: http://php.net/manual/zh/function.octdec.php
[2156]: http://php.net/manual/zh/function.odbc-autocommit.php
[2157]: http://php.net/manual/zh/function.odbc-binmode.php
[2158]: http://php.net/manual/zh/function.odbc-close.php
[2159]: http://php.net/manual/zh/function.odbc-close-all.php
[2160]: http://php.net/manual/zh/function.odbc-columnprivileges.php
[2161]: http://php.net/manual/zh/function.odbc-columns.php
[2162]: http://php.net/manual/zh/function.odbc-commit.php
[2163]: http://php.net/manual/zh/function.odbc-connect.php
[2164]: http://php.net/manual/zh/function.odbc-cursor.php
[2165]: http://php.net/manual/zh/function.odbc-data-source.php
[2166]: http://php.net/manual/zh/function.odbc-do.php
[2167]: http://php.net/manual/zh/function.odbc-exec.php
[2168]: http://php.net/manual/zh/function.odbc-error.php
[2169]: http://php.net/manual/zh/function.odbc-errormsg.php
[2170]: http://php.net/manual/zh/function.odbc-exec.php
[2171]: http://php.net/manual/zh/function.odbc-execute.php
[2172]: http://php.net/manual/zh/function.odbc-fetch-array.php
[2173]: http://php.net/manual/zh/function.odbc-fetch-into.php
[2174]: http://php.net/manual/zh/function.odbc-fetch-object.php
[2175]: http://php.net/manual/zh/function.odbc-fetch-row.php
[2176]: http://php.net/manual/zh/function.odbc-field-len.php
[2177]: http://php.net/manual/zh/function.odbc-field-name.php
[2178]: http://php.net/manual/zh/function.odbc-field-num.php
[2179]: http://php.net/manual/zh/function.odbc-field-precision.php
[2180]: http://php.net/manual/zh/function.odbc-field-len.php
[2181]: http://php.net/manual/zh/function.odbc-field-scale.php
[2182]: http://php.net/manual/zh/function.odbc-field-type.php
[2183]: http://php.net/manual/zh/function.odbc-foreignkeys.php
[2184]: http://php.net/manual/zh/function.odbc-free-result.php
[2185]: http://php.net/manual/zh/function.odbc-gettypeinfo.php
[2186]: http://php.net/manual/zh/function.odbc-longreadlen.php
[2187]: http://php.net/manual/zh/function.odbc-next-result.php
[2188]: http://php.net/manual/zh/function.odbc-num-fields.php
[2189]: http://php.net/manual/zh/function.odbc-num-rows.php
[2190]: http://php.net/manual/zh/function.odbc-pconnect.php
[2191]: http://php.net/manual/zh/function.odbc-prepare.php
[2192]: http://php.net/manual/zh/function.odbc-primarykeys.php
[2193]: http://php.net/manual/zh/function.odbc-procedurecolumns.php
[2194]: http://php.net/manual/zh/function.odbc-procedures.php
[2195]: http://php.net/manual/zh/function.odbc-result.php
[2196]: http://php.net/manual/zh/function.odbc-result-all.php
[2197]: http://php.net/manual/zh/function.odbc-rollback.php
[2198]: http://php.net/manual/zh/function.odbc-setoption.php
[2199]: http://php.net/manual/zh/function.odbc-specialcolumns.php
[2200]: http://php.net/manual/zh/function.odbc-statistics.php
[2201]: http://php.net/manual/zh/function.odbc-tableprivileges.php
[2202]: http://php.net/manual/zh/function.odbc-tables.php
[2203]: http://php.net/manual/zh/function.opcache-compile-file.php
[2204]: http://php.net/manual/zh/function.opcache-get-configuration.php
[2205]: http://php.net/manual/zh/function.opcache-get-status.php
[2206]: http://php.net/manual/zh/function.opcache-invalidate.php
[2207]: http://php.net/manual/zh/function.opcache-reset.php
[2208]: http://php.net/manual/zh/function.opendir.php
[2209]: http://php.net/manual/zh/function.openlog.php
[2210]: http://php.net/manual/zh/function.openssl-cipher-iv-length.php
[2211]: http://php.net/manual/zh/function.openssl-csr-export.php
[2212]: http://php.net/manual/zh/function.openssl-csr-export-to-file.php
[2213]: http://php.net/manual/zh/function.openssl-csr-get-public-key.php
[2214]: http://php.net/manual/zh/function.openssl-csr-get-subject.php
[2215]: http://php.net/manual/zh/function.openssl-csr-new.php
[2216]: http://php.net/manual/zh/function.openssl-csr-sign.php
[2217]: http://php.net/manual/zh/function.openssl-decrypt.php
[2218]: http://php.net/manual/zh/function.openssl-dh-compute-key.php
[2219]: http://php.net/manual/zh/function.openssl-digest.php
[2220]: http://php.net/manual/zh/function.openssl-encrypt.php
[2221]: http://php.net/manual/zh/function.openssl-error-string.php
[2222]: http://php.net/manual/zh/function.openssl-free-key.php
[2223]: http://php.net/manual/zh/function.openssl-get-cert-locations.php
[2224]: http://php.net/manual/zh/function.openssl-get-cipher-methods.php
[2225]: http://php.net/manual/zh/function.openssl-get-md-methods.php
[2226]: http://php.net/manual/zh/function.openssl-get-privatekey.php
[2227]: http://php.net/manual/zh/function.openssl-pkey-get-private.php
[2228]: http://php.net/manual/zh/function.openssl-get-publickey.php
[2229]: http://php.net/manual/zh/function.openssl-pkey-get-public.php
[2230]: http://php.net/manual/zh/function.openssl-open.php
[2231]: http://php.net/manual/zh/function.openssl-pbkdf2.php
[2232]: http://php.net/manual/zh/function.openssl-pkcs12-export.php
[2233]: http://php.net/manual/zh/function.openssl-pkcs12-export-to-file.php
[2234]: http://php.net/manual/zh/function.openssl-pkcs12-read.php
[2235]: http://php.net/manual/zh/function.openssl-pkcs7-decrypt.php
[2236]: http://php.net/manual/zh/function.openssl-pkcs7-encrypt.php
[2237]: http://php.net/manual/zh/function.openssl-pkcs7-sign.php
[2238]: http://php.net/manual/zh/function.openssl-pkcs7-verify.php
[2239]: http://php.net/manual/zh/function.openssl-pkey-export.php
[2240]: http://php.net/manual/zh/function.openssl-pkey-export-to-file.php
[2241]: http://php.net/manual/zh/function.openssl-pkey-free.php
[2242]: http://php.net/manual/zh/function.openssl-pkey-get-details.php
[2243]: http://php.net/manual/zh/function.openssl-pkey-get-private.php
[2244]: http://php.net/manual/zh/function.openssl-pkey-get-public.php
[2245]: http://php.net/manual/zh/function.openssl-pkey-new.php
[2246]: http://php.net/manual/zh/function.openssl-private-decrypt.php
[2247]: http://php.net/manual/zh/function.openssl-private-encrypt.php
[2248]: http://php.net/manual/zh/function.openssl-public-decrypt.php
[2249]: http://php.net/manual/zh/function.openssl-public-encrypt.php
[2250]: http://php.net/manual/zh/function.openssl-random-pseudo-bytes.php
[2251]: http://php.net/manual/zh/function.openssl-seal.php
[2252]: http://php.net/manual/zh/function.openssl-sign.php
[2253]: http://php.net/manual/zh/function.openssl-spki-export.php
[2254]: http://php.net/manual/zh/function.openssl-spki-export-challenge.php
[2255]: http://php.net/manual/zh/function.openssl-spki-new.php
[2256]: http://php.net/manual/zh/function.openssl-spki-verify.php
[2257]: http://php.net/manual/zh/function.openssl-verify.php
[2258]: http://php.net/manual/zh/function.openssl-x509-check-private-key.php
[2259]: http://php.net/manual/zh/function.openssl-x509-checkpurpose.php
[2260]: http://php.net/manual/zh/function.openssl-x509-export.php
[2261]: http://php.net/manual/zh/function.openssl-x509-export-to-file.php
[2262]: http://php.net/manual/zh/function.openssl-x509-fingerprint.php
[2263]: http://php.net/manual/zh/function.openssl-x509-free.php
[2264]: http://php.net/manual/zh/function.openssl-x509-parse.php
[2265]: http://php.net/manual/zh/function.openssl-x509-read.php
[2266]: http://php.net/manual/zh/function.ord.php
[2267]: http://php.net/manual/zh/function.output-add-rewrite-var.php
[2268]: http://php.net/manual/zh/function.output-reset-rewrite-vars.php
[2269]: http://php.net/manual/zh/function.parse-ini-file.php
[2270]: http://php.net/manual/zh/function.parse-ini-string.php
[2271]: http://php.net/manual/zh/function.parse-str.php
[2272]: http://php.net/manual/zh/function.parse-url.php
[2273]: http://php.net/manual/zh/function.passthru.php
[2274]: http://php.net/manual/zh/function.password-get-info.php
[2275]: http://php.net/manual/zh/function.password-hash.php
[2276]: http://php.net/manual/zh/function.password-needs-rehash.php
[2277]: http://php.net/manual/zh/function.password-verify.php
[2278]: http://php.net/manual/zh/function.pathinfo.php
[2279]: http://php.net/manual/zh/function.pclose.php
[2280]: http://php.net/manual/zh/function.pcntl-alarm.php
[2281]: http://php.net/manual/zh/function.pcntl-async-signals.php
[2282]: http://php.net/manual/zh/function.pcntl-errno.php
[2283]: http://php.net/manual/zh/function.pcntl-get-last-error.php
[2284]: http://php.net/manual/zh/function.pcntl-exec.php
[2285]: http://php.net/manual/zh/function.pcntl-fork.php
[2286]: http://php.net/manual/zh/function.pcntl-get-last-error.php
[2287]: http://php.net/manual/zh/function.pcntl-getpriority.php
[2288]: http://php.net/manual/zh/function.pcntl-setpriority.php
[2289]: http://php.net/manual/zh/function.pcntl-signal.php
[2290]: http://php.net/manual/zh/function.pcntl-signal-dispatch.php
[2291]: http://php.net/manual/zh/function.pcntl-signal-get-handler.php
[2292]: http://php.net/manual/zh/function.pcntl-sigprocmask.php
[2293]: http://php.net/manual/zh/function.pcntl-sigtimedwait.php
[2294]: http://php.net/manual/zh/function.pcntl-sigwaitinfo.php
[2295]: http://php.net/manual/zh/function.pcntl-strerror.php
[2296]: http://php.net/manual/zh/function.pcntl-wait.php
[2297]: http://php.net/manual/zh/function.pcntl-waitpid.php
[2298]: http://php.net/manual/zh/function.pcntl-wexitstatus.php
[2299]: http://php.net/manual/zh/function.pcntl-wifexited.php
[2300]: http://php.net/manual/zh/function.pcntl-wifsignaled.php
[2301]: http://php.net/manual/zh/function.pcntl-wifstopped.php
[2302]: http://php.net/manual/zh/function.pcntl-wstopsig.php
[2303]: http://php.net/manual/zh/function.pcntl-wtermsig.php
[2304]: http://php.net/manual/zh/pdo.prepare.php
[2305]: http://php.net/manual/zh/function.pfsockopen.php
[2306]: http://php.net/manual/zh/function.pg-affected-rows.php
[2307]: http://php.net/manual/zh/function.pg-cancel-query.php
[2308]: http://php.net/manual/zh/function.pg-client-encoding.php
[2309]: http://php.net/manual/zh/function.pg-close.php
[2310]: http://php.net/manual/zh/function.pg-connect.php
[2311]: http://php.net/manual/zh/function.pg-connection-busy.php
[2312]: http://php.net/manual/zh/function.pg-connection-reset.php
[2313]: http://php.net/manual/zh/function.pg-connection-status.php
[2314]: http://php.net/manual/zh/function.pg-convert.php
[2315]: http://php.net/manual/zh/function.pg-copy-from.php
[2316]: http://php.net/manual/zh/function.pg-copy-to.php
[2317]: http://php.net/manual/zh/function.pg-dbname.php
[2318]: http://php.net/manual/zh/function.pg-delete.php
[2319]: http://php.net/manual/zh/function.pg-end-copy.php
[2320]: http://php.net/manual/zh/function.pg-escape-bytea.php
[2321]: http://php.net/manual/zh/function.pg-escape-identifier.php
[2322]: http://php.net/manual/zh/function.pg-escape-literal.php
[2323]: http://php.net/manual/zh/function.pg-escape-string.php
[2324]: http://php.net/manual/zh/function.pg-execute.php
[2325]: http://php.net/manual/zh/function.pg-fetch-all.php
[2326]: http://php.net/manual/zh/function.pg-fetch-all-columns.php
[2327]: http://php.net/manual/zh/function.pg-fetch-array.php
[2328]: http://php.net/manual/zh/function.pg-fetch-assoc.php
[2329]: http://php.net/manual/zh/function.pg-fetch-object.php
[2330]: http://php.net/manual/zh/function.pg-fetch-result.php
[2331]: http://php.net/manual/zh/function.pg-fetch-row.php
[2332]: http://php.net/manual/zh/function.pg-field-is-null.php
[2333]: http://php.net/manual/zh/function.pg-field-name.php
[2334]: http://php.net/manual/zh/function.pg-field-num.php
[2335]: http://php.net/manual/zh/function.pg-field-prtlen.php
[2336]: http://php.net/manual/zh/function.pg-field-size.php
[2337]: http://php.net/manual/zh/function.pg-field-table.php
[2338]: http://php.net/manual/zh/function.pg-field-type.php
[2339]: http://php.net/manual/zh/function.pg-field-type-oid.php
[2340]: http://php.net/manual/zh/function.pg-free-result.php
[2341]: http://php.net/manual/zh/function.pg-get-notify.php
[2342]: http://php.net/manual/zh/function.pg-get-pid.php
[2343]: http://php.net/manual/zh/function.pg-get-result.php
[2344]: http://php.net/manual/zh/function.pg-host.php
[2345]: http://php.net/manual/zh/function.pg-insert.php
[2346]: http://php.net/manual/zh/function.pg-last-error.php
[2347]: http://php.net/manual/zh/function.pg-last-notice.php
[2348]: http://php.net/manual/zh/function.pg-last-oid.php
[2349]: http://php.net/manual/zh/function.pg-lo-close.php
[2350]: http://php.net/manual/zh/function.pg-lo-create.php
[2351]: http://php.net/manual/zh/function.pg-lo-export.php
[2352]: http://php.net/manual/zh/function.pg-lo-import.php
[2353]: http://php.net/manual/zh/function.pg-lo-open.php
[2354]: http://php.net/manual/zh/function.pg-lo-read.php
[2355]: http://php.net/manual/zh/function.pg-lo-read-all.php
[2356]: http://php.net/manual/zh/function.pg-lo-seek.php
[2357]: http://php.net/manual/zh/function.pg-lo-tell.php
[2358]: http://php.net/manual/zh/function.pg-lo-unlink.php
[2359]: http://php.net/manual/zh/function.pg-lo-write.php
[2360]: http://php.net/manual/zh/function.pg-meta-data.php
[2361]: http://php.net/manual/zh/function.pg-num-fields.php
[2362]: http://php.net/manual/zh/function.pg-num-rows.php
[2363]: http://php.net/manual/zh/function.pg-options.php
[2364]: http://php.net/manual/zh/function.pg-parameter-status.php
[2365]: http://php.net/manual/zh/function.pg-pconnect.php
[2366]: http://php.net/manual/zh/function.pg-ping.php
[2367]: http://php.net/manual/zh/function.pg-port.php
[2368]: http://php.net/manual/zh/function.pg-prepare.php
[2369]: http://php.net/manual/zh/function.pg-put-line.php
[2370]: http://php.net/manual/zh/function.pg-query.php
[2371]: http://php.net/manual/zh/function.pg-query-params.php
[2372]: http://php.net/manual/zh/function.pg-result-error.php
[2373]: http://php.net/manual/zh/function.pg-result-error-field.php
[2374]: http://php.net/manual/zh/function.pg-result-seek.php
[2375]: http://php.net/manual/zh/function.pg-result-status.php
[2376]: http://php.net/manual/zh/function.pg-select.php
[2377]: http://php.net/manual/zh/function.pg-send-execute.php
[2378]: http://php.net/manual/zh/function.pg-send-prepare.php
[2379]: http://php.net/manual/zh/function.pg-send-query.php
[2380]: http://php.net/manual/zh/function.pg-send-query-params.php
[2381]: http://php.net/manual/zh/function.pg-set-client-encoding.php
[2382]: http://php.net/manual/zh/function.pg-set-error-verbosity.php
[2383]: http://php.net/manual/zh/function.pg-last-error.php
[2384]: http://php.net/manual/zh/function.pg-result-error.php
[2385]: http://php.net/manual/zh/function.pg-trace.php
[2386]: http://php.net/manual/zh/function.pg-transaction-status.php
[2387]: http://php.net/manual/zh/function.pg-tty.php
[2388]: http://php.net/manual/zh/function.pg-unescape-bytea.php
[2389]: http://php.net/manual/zh/function.pg-untrace.php
[2390]: http://php.net/manual/zh/function.pg-update.php
[2391]: http://php.net/manual/zh/function.pg-version.php
[2392]: http://php.net/manual/zh/function.php-ini-loaded-file.php
[2393]: http://php.net/manual/zh/function.php-ini-scanned-files.php
[2394]: http://php.net/manual/zh/function.php-logo-guid.php
[2395]: http://php.net/manual/zh/function.php-sapi-name.php
[2396]: http://php.net/manual/zh/function.php-strip-whitespace.php
[2397]: http://php.net/manual/zh/function.php-uname.php
[2398]: http://php.net/manual/zh/function.phpcredits.php
[2399]: http://php.net/manual/zh/function.phpinfo.php
[2400]: http://php.net/manual/zh/function.phpversion.php
[2401]: http://php.net/manual/zh/function.pi.php
[2402]: http://php.net/manual/zh/function.png2wbmp.php
[2403]: http://php.net/manual/zh/pool.collect.php
[2404]: http://php.net/manual/zh/pool.resize.php
[2405]: http://php.net/manual/zh/pool.shutdown.php
[2406]: http://php.net/manual/zh/pool.submit.php
[2407]: http://php.net/manual/zh/function.popen.php
[2408]: http://php.net/manual/zh/function.pos.php
[2409]: http://php.net/manual/zh/function.current.php
[2410]: http://php.net/manual/zh/function.posix-access.php
[2411]: http://php.net/manual/zh/function.posix-ctermid.php
[2412]: http://php.net/manual/zh/function.posix-errno.php
[2413]: http://php.net/manual/zh/function.posix-get-last-error.php
[2414]: http://php.net/manual/zh/function.posix-get-last-error.php
[2415]: http://php.net/manual/zh/function.posix-getcwd.php
[2416]: http://php.net/manual/zh/function.posix-getegid.php
[2417]: http://php.net/manual/zh/function.posix-geteuid.php
[2418]: http://php.net/manual/zh/function.posix-getgid.php
[2419]: http://php.net/manual/zh/function.posix-getgrgid.php
[2420]: http://php.net/manual/zh/function.posix-getgrnam.php
[2421]: http://php.net/manual/zh/function.posix-getgroups.php
[2422]: http://php.net/manual/zh/function.posix-getlogin.php
[2423]: http://php.net/manual/zh/function.posix-getpgid.php
[2424]: http://php.net/manual/zh/function.posix-getpgrp.php
[2425]: http://php.net/manual/zh/function.posix-getpid.php
[2426]: http://php.net/manual/zh/function.posix-getppid.php
[2427]: http://php.net/manual/zh/function.posix-getpwnam.php
[2428]: http://php.net/manual/zh/function.posix-getpwuid.php
[2429]: http://php.net/manual/zh/function.posix-getrlimit.php
[2430]: http://php.net/manual/zh/function.posix-getsid.php
[2431]: http://php.net/manual/zh/function.posix-getuid.php
[2432]: http://php.net/manual/zh/function.posix-initgroups.php
[2433]: http://php.net/manual/zh/function.posix-isatty.php
[2434]: http://php.net/manual/zh/function.posix-kill.php
[2435]: http://php.net/manual/zh/function.posix-mkfifo.php
[2436]: http://php.net/manual/zh/function.posix-mknod.php
[2437]: http://php.net/manual/zh/function.posix-setegid.php
[2438]: http://php.net/manual/zh/function.posix-seteuid.php
[2439]: http://php.net/manual/zh/function.posix-setgid.php
[2440]: http://php.net/manual/zh/function.posix-setpgid.php
[2441]: http://php.net/manual/zh/function.posix-setrlimit.php
[2442]: http://php.net/manual/zh/function.posix-setsid.php
[2443]: http://php.net/manual/zh/function.posix-setuid.php
[2444]: http://php.net/manual/zh/function.posix-strerror.php
[2445]: http://php.net/manual/zh/function.posix-times.php
[2446]: http://php.net/manual/zh/function.posix-ttyname.php
[2447]: http://php.net/manual/zh/function.posix-uname.php
[2448]: http://php.net/manual/zh/function.pow.php
[2449]: http://php.net/manual/zh/function.preg-filter.php
[2450]: http://php.net/manual/zh/function.preg-grep.php
[2451]: http://php.net/manual/zh/function.preg-last-error.php
[2452]: http://php.net/manual/zh/function.preg-match.php
[2453]: http://php.net/manual/zh/function.preg-match-all.php
[2454]: http://php.net/manual/zh/function.preg-quote.php
[2455]: http://php.net/manual/zh/function.preg-replace.php
[2456]: http://php.net/manual/zh/function.preg-replace-callback.php
[2457]: http://php.net/manual/zh/function.preg-replace-callback-array.php
[2458]: http://php.net/manual/zh/function.preg-split.php
[2459]: http://php.net/manual/zh/function.prev.php
[2460]: http://php.net/manual/zh/function.print-r.php
[2461]: http://php.net/manual/zh/function.printf.php
[2462]: http://php.net/manual/zh/function.proc-close.php
[2463]: http://php.net/manual/zh/function.proc-open.php
[2464]: http://php.net/manual/zh/function.proc-get-status.php
[2465]: http://php.net/manual/zh/function.proc-open.php
[2466]: http://php.net/manual/zh/function.proc-nice.php
[2467]: http://php.net/manual/zh/function.proc-open.php
[2468]: http://php.net/manual/zh/function.proc-terminate.php
[2469]: http://php.net/manual/zh/function.property-exists.php
[2470]: http://php.net/manual/zh/function.pspell-add-to-personal.php
[2471]: http://php.net/manual/zh/function.pspell-add-to-session.php
[2472]: http://php.net/manual/zh/function.pspell-check.php
[2473]: http://php.net/manual/zh/function.pspell-clear-session.php
[2474]: http://php.net/manual/zh/function.pspell-config-create.php
[2475]: http://php.net/manual/zh/function.pspell-config-data-dir.php
[2476]: http://php.net/manual/zh/function.pspell-config-dict-dir.php
[2477]: http://php.net/manual/zh/function.pspell-config-ignore.php
[2478]: http://php.net/manual/zh/function.pspell-config-mode.php
[2479]: http://php.net/manual/zh/function.pspell-config-personal.php
[2480]: http://php.net/manual/zh/function.pspell-config-repl.php
[2481]: http://php.net/manual/zh/function.pspell-config-runtogether.php
[2482]: http://php.net/manual/zh/function.pspell-config-save-repl.php
[2483]: http://php.net/manual/zh/function.pspell-new.php
[2484]: http://php.net/manual/zh/function.pspell-new-config.php
[2485]: http://php.net/manual/zh/function.pspell-new-personal.php
[2486]: http://php.net/manual/zh/function.pspell-save-wordlist.php
[2487]: http://php.net/manual/zh/function.pspell-store-replacement.php
[2488]: http://php.net/manual/zh/function.pspell-suggest.php
[2489]: http://php.net/manual/zh/function.putenv.php
[2490]: http://php.net/manual/zh/function.quoted-printable-encode.php
[2491]: http://php.net/manual/zh/function.quotemeta.php
[2492]: http://php.net/manual/zh/function.rand.php
[2493]: http://php.net/manual/zh/function.random-bytes.php
[2494]: http://php.net/manual/zh/function.range.php
[2495]: http://php.net/manual/zh/function.rawurldecode.php
[2496]: http://php.net/manual/zh/function.rawurlencode.php
[2497]: http://php.net/manual/zh/function.read-exif-data.php
[2498]: http://php.net/manual/zh/function.exif-read-data.php
[2499]: http://php.net/manual/zh/function.readdir.php
[2500]: http://php.net/manual/zh/function.readfile.php
[2501]: http://php.net/manual/zh/function.readgzfile.php
[2502]: http://php.net/manual/zh/function.readline.php
[2503]: http://php.net/manual/zh/function.readline-add-history.php
[2504]: http://php.net/manual/zh/function.readline-callback-handler-install.php
[2505]: http://php.net/manual/zh/function.readline-callback-handler-remove.php
[2506]: http://php.net/manual/zh/function.readline-callback-read-char.php
[2507]: http://php.net/manual/zh/function.readline-clear-history.php
[2508]: http://php.net/manual/zh/function.readline-completion-function.php
[2509]: http://php.net/manual/zh/function.readline-info.php
[2510]: http://php.net/manual/zh/function.readline-list-history.php
[2511]: http://php.net/manual/zh/function.readline-on-new-line.php
[2512]: http://php.net/manual/zh/function.readline-read-history.php
[2513]: http://php.net/manual/zh/function.readline-redisplay.php
[2514]: http://php.net/manual/zh/function.readline-write-history.php
[2515]: http://php.net/manual/zh/function.readlink.php
[2516]: http://php.net/manual/zh/function.realpath.php
[2517]: http://php.net/manual/zh/function.realpath-cache-get.php
[2518]: http://php.net/manual/zh/function.realpath-cache-size.php
[2519]: http://php.net/manual/zh/function.recode.php
[2520]: http://php.net/manual/zh/function.recode-string.php
[2521]: http://php.net/manual/zh/function.recode-file.php
[2522]: http://php.net/manual/zh/function.recode-string.php
[2523]: http://php.net/manual/zh/function.register-shutdown-function.php
[2524]: http://php.net/manual/zh/function.register-tick-function.php
[2525]: http://php.net/manual/zh/function.rename.php
[2526]: http://php.net/manual/zh/function.reset.php
[2527]: http://php.net/manual/zh/function.resourcebundle-count.php
[2528]: http://php.net/manual/zh/function.resourcebundle-create.php
[2529]: http://php.net/manual/zh/function.resourcebundle-get.php
[2530]: http://php.net/manual/zh/function.resourcebundle-locales.php
[2531]: http://php.net/manual/zh/function.restore-error-handler.php
[2532]: http://php.net/manual/zh/function.restore-exception-handler.php
[2533]: http://php.net/manual/zh/function.restore-include-path.php
[2534]: http://php.net/manual/zh/function.rewind.php
[2535]: http://php.net/manual/zh/function.rewinddir.php
[2536]: http://php.net/manual/zh/function.rmdir.php
[2537]: http://php.net/manual/zh/function.round.php
[2538]: http://php.net/manual/zh/function.rrd-create.php
[2539]: http://php.net/manual/zh/function.rrd-error.php
[2540]: http://php.net/manual/zh/function.rrd-fetch.php
[2541]: http://php.net/manual/zh/function.rrd-first.php
[2542]: http://php.net/manual/zh/function.rrd-graph.php
[2543]: http://php.net/manual/zh/function.rrd-info.php
[2544]: http://php.net/manual/zh/function.rrd-last.php
[2545]: http://php.net/manual/zh/function.rrd-lastupdate.php
[2546]: http://php.net/manual/zh/function.rrd-restore.php
[2547]: http://php.net/manual/zh/function.rrd-tune.php
[2548]: http://php.net/manual/zh/function.rrd-update.php
[2549]: http://php.net/manual/zh/function.rrd-version.php
[2550]: http://php.net/manual/zh/function.rrd-xport.php
[2551]: http://php.net/manual/zh/rrdcreator.addarchive.php
[2552]: http://php.net/manual/zh/rrdcreator.adddatasource.php
[2553]: http://php.net/manual/zh/rrdcreator.save.php
[2554]: http://php.net/manual/zh/rrdgraph.save.php
[2555]: http://php.net/manual/zh/rrdgraph.saveverbose.php
[2556]: http://php.net/manual/zh/rrdgraph.setoptions.php
[2557]: http://php.net/manual/zh/rrdupdater.update.php
[2558]: http://php.net/manual/zh/function.rsort.php
[2559]: http://php.net/manual/zh/function.rtrim.php
[2560]: http://php.net/manual/zh/function.sem-acquire.php
[2561]: http://php.net/manual/zh/function.sem-get.php
[2562]: http://php.net/manual/zh/function.sem-release.php
[2563]: http://php.net/manual/zh/function.sem-remove.php
[2564]: http://php.net/manual/zh/function.serialize.php
[2565]: http://php.net/manual/zh/function.session-abort.php
[2566]: http://php.net/manual/zh/function.session-cache-expire.php
[2567]: http://php.net/manual/zh/function.session-cache-limiter.php
[2568]: http://php.net/manual/zh/function.session-commit.php
[2569]: http://php.net/manual/zh/function.session-write-close.php
[2570]: http://php.net/manual/zh/function.session-create-id.php
[2571]: http://php.net/manual/zh/function.session-decode.php
[2572]: http://php.net/manual/zh/function.session-destroy.php
[2573]: http://php.net/manual/zh/function.session-encode.php
[2574]: http://php.net/manual/zh/function.session-gc.php
[2575]: http://php.net/manual/zh/function.session-get-cookie-params.php
[2576]: http://php.net/manual/zh/function.session-id.php
[2577]: http://php.net/manual/zh/function.session-is-registered.php
[2578]: http://php.net/manual/zh/function.session-module-name.php
[2579]: http://php.net/manual/zh/function.session-name.php
[2580]: http://php.net/manual/zh/function.session-regenerate-id.php
[2581]: http://php.net/manual/zh/function.session-register.php
[2582]: http://php.net/manual/zh/function.session-register-shutdown.php
[2583]: http://php.net/manual/zh/function.session-reset.php
[2584]: http://php.net/manual/zh/function.session-save-path.php
[2585]: http://php.net/manual/zh/function.session-set-cookie-params.php
[2586]: http://php.net/manual/zh/function.session-set-save-handler.php
[2587]: http://php.net/manual/zh/function.session-start.php
[2588]: http://php.net/manual/zh/function.session-status.php
[2589]: http://php.net/manual/zh/function.session-unregister.php
[2590]: http://php.net/manual/zh/function.session-unset.php
[2591]: http://php.net/manual/zh/function.session-write-close.php
[2592]: http://php.net/manual/zh/function.set-error-handler.php
[2593]: http://php.net/manual/zh/function.set-exception-handler.php
[2594]: http://php.net/manual/zh/function.set-file-buffer.php
[2595]: http://php.net/manual/zh/function.stream-set-write-buffer.php
[2596]: http://php.net/manual/zh/function.set-include-path.php
[2597]: http://php.net/manual/zh/function.set-magic-quotes-runtime.php
[2598]: http://php.net/manual/zh/function.set-socket-blocking.php
[2599]: http://php.net/manual/zh/function.stream-set-blocking.php
[2600]: http://php.net/manual/zh/function.set-time-limit.php
[2601]: http://php.net/manual/zh/function.setcookie.php
[2602]: http://php.net/manual/zh/function.setlocale.php
[2603]: http://php.net/manual/zh/function.setrawcookie.php
[2604]: http://php.net/manual/zh/function.settype.php
[2605]: http://php.net/manual/zh/function.sha1.php
[2606]: http://php.net/manual/zh/function.sha1-file.php
[2607]: http://php.net/manual/zh/function.shell-exec.php
[2608]: http://php.net/manual/zh/function.shm-attach.php
[2609]: http://php.net/manual/zh/function.shm-detach.php
[2610]: http://php.net/manual/zh/function.shm-get-var.php
[2611]: http://php.net/manual/zh/function.shm-has-var.php
[2612]: http://php.net/manual/zh/function.shm-put-var.php
[2613]: http://php.net/manual/zh/function.shm-remove.php
[2614]: http://php.net/manual/zh/function.shm-remove-var.php
[2615]: http://php.net/manual/zh/function.shmop-close.php
[2616]: http://php.net/manual/zh/function.shmop-delete.php
[2617]: http://php.net/manual/zh/function.shmop-open.php
[2618]: http://php.net/manual/zh/function.shmop-read.php
[2619]: http://php.net/manual/zh/function.shmop-size.php
[2620]: http://php.net/manual/zh/function.shmop-write.php
[2621]: http://php.net/manual/zh/function.show-source.php
[2622]: http://php.net/manual/zh/function.highlight-file.php
[2623]: http://php.net/manual/zh/function.shuffle.php
[2624]: http://php.net/manual/zh/function.similar-text.php
[2625]: http://php.net/manual/zh/function.simplexml-import-dom.php
[2626]: http://php.net/manual/zh/function.simplexml-load-file.php
[2627]: http://php.net/manual/zh/function.simplexml-load-string.php
[2628]: http://php.net/manual/zh/function.sin.php
[2629]: http://php.net/manual/zh/function.sinh.php
[2630]: http://php.net/manual/zh/function.sizeof.php
[2631]: http://php.net/manual/zh/function.count.php
[2632]: http://php.net/manual/zh/function.sleep.php
[2633]: http://php.net/manual/zh/function.snmp2-get.php
[2634]: http://php.net/manual/zh/function.snmp2-getnext.php
[2635]: http://php.net/manual/zh/function.snmp2-real-walk.php
[2636]: http://php.net/manual/zh/function.snmp2-set.php
[2637]: http://php.net/manual/zh/function.snmp2-walk.php
[2638]: http://php.net/manual/zh/function.snmp3-get.php
[2639]: http://php.net/manual/zh/function.snmp3-getnext.php
[2640]: http://php.net/manual/zh/function.snmp3-real-walk.php
[2641]: http://php.net/manual/zh/function.snmp3-set.php
[2642]: http://php.net/manual/zh/function.snmp3-walk.php
[2643]: http://php.net/manual/zh/function.snmp-get-quick-print.php
[2644]: http://php.net/manual/zh/function.snmp-get-valueretrieval.php
[2645]: http://php.net/manual/zh/function.snmp-read-mib.php
[2646]: http://php.net/manual/zh/function.snmp-set-enum-print.php
[2647]: http://php.net/manual/zh/function.snmp-set-oid-numeric-print.php
[2648]: http://php.net/manual/zh/function.snmp-set-oid-output-format.php
[2649]: http://php.net/manual/zh/function.snmp-set-quick-print.php
[2650]: http://php.net/manual/zh/function.snmp-set-valueretrieval.php
[2651]: http://php.net/manual/zh/function.snmpget.php
[2652]: http://php.net/manual/zh/function.snmpgetnext.php
[2653]: http://php.net/manual/zh/function.snmprealwalk.php
[2654]: http://php.net/manual/zh/function.snmpset.php
[2655]: http://php.net/manual/zh/function.snmpwalk.php
[2656]: http://php.net/manual/zh/function.snmpwalkoid.php
[2657]: http://php.net/manual/zh/function.socket-accept.php
[2658]: http://php.net/manual/zh/function.socket-bind.php
[2659]: http://php.net/manual/zh/function.socket-clear-error.php
[2660]: http://php.net/manual/zh/function.socket-close.php
[2661]: http://php.net/manual/zh/function.socket-cmsg-space.php
[2662]: http://php.net/manual/zh/function.socket-connect.php
[2663]: http://php.net/manual/zh/function.socket-create.php
[2664]: http://php.net/manual/zh/function.socket-create-listen.php
[2665]: http://php.net/manual/zh/function.socket-create-pair.php
[2666]: http://php.net/manual/zh/function.socket-get-option.php
[2667]: http://php.net/manual/zh/function.socket-get-status.php
[2668]: http://php.net/manual/zh/function.stream-get-meta-data.php
[2669]: http://php.net/manual/zh/function.socket-getopt.php
[2670]: http://php.net/manual/zh/function.socket-get-option.php
[2671]: http://php.net/manual/zh/function.socket-getpeername.php
[2672]: http://php.net/manual/zh/function.socket-getsockname.php
[2673]: http://php.net/manual/zh/function.socket-import-stream.php
[2674]: http://php.net/manual/zh/function.socket-last-error.php
[2675]: http://php.net/manual/zh/function.socket-listen.php
[2676]: http://php.net/manual/zh/function.socket-read.php
[2677]: http://php.net/manual/zh/function.socket-recv.php
[2678]: http://php.net/manual/zh/function.socket-recvfrom.php
[2679]: http://php.net/manual/zh/function.socket-recvmsg.php
[2680]: http://php.net/manual/zh/function.socket-select.php
[2681]: http://php.net/manual/zh/function.socket-send.php
[2682]: http://php.net/manual/zh/function.socket-sendmsg.php
[2683]: http://php.net/manual/zh/function.socket-sendto.php
[2684]: http://php.net/manual/zh/function.socket-set-block.php
[2685]: http://php.net/manual/zh/function.socket-set-blocking.php
[2686]: http://php.net/manual/zh/function.stream-set-blocking.php
[2687]: http://php.net/manual/zh/function.socket-set-nonblock.php
[2688]: http://php.net/manual/zh/function.socket-set-option.php
[2689]: http://php.net/manual/zh/function.socket-set-timeout.php
[2690]: http://php.net/manual/zh/function.stream-set-timeout.php
[2691]: http://php.net/manual/zh/function.socket-setopt.php
[2692]: http://php.net/manual/zh/function.socket-set-option.php
[2693]: http://php.net/manual/zh/function.socket-shutdown.php
[2694]: http://php.net/manual/zh/function.socket-strerror.php
[2695]: http://php.net/manual/zh/function.socket-write.php
[2696]: http://php.net/manual/zh/function.sort.php
[2697]: http://php.net/manual/zh/function.soundex.php
[2698]: http://php.net/manual/zh/function.spl-autoload.php
[2699]: http://php.net/manual/zh/function.spl-autoload-call.php
[2700]: http://php.net/manual/zh/function.spl-autoload-extensions.php
[2701]: http://php.net/manual/zh/function.spl-autoload-functions.php
[2702]: http://php.net/manual/zh/function.spl-autoload-register.php
[2703]: http://php.net/manual/zh/function.spl-autoload-unregister.php
[2704]: http://php.net/manual/zh/function.spl-classes.php
[2705]: http://php.net/manual/zh/function.spl-object-hash.php
[2706]: http://php.net/manual/zh/splfileinfo.getatime.php
[2707]: http://php.net/manual/zh/splfileinfo.getbasename.php
[2708]: http://php.net/manual/zh/splfileinfo.getctime.php
[2709]: http://php.net/manual/zh/splfileinfo.getextension.php
[2710]: http://php.net/manual/zh/splfileinfo.getfileinfo.php
[2711]: http://php.net/manual/zh/splfileinfo.getfilename.php
[2712]: http://php.net/manual/zh/splfileinfo.getgroup.php
[2713]: http://php.net/manual/zh/splfileinfo.getinode.php
[2714]: http://php.net/manual/zh/splfileinfo.getlinktarget.php
[2715]: http://php.net/manual/zh/splfileinfo.getmtime.php
[2716]: http://php.net/manual/zh/splfileinfo.getowner.php
[2717]: http://php.net/manual/zh/splfileinfo.getpath.php
[2718]: http://php.net/manual/zh/splfileinfo.getpathinfo.php
[2719]: http://php.net/manual/zh/splfileinfo.getpathname.php
[2720]: http://php.net/manual/zh/splfileinfo.getperms.php
[2721]: http://php.net/manual/zh/splfileinfo.getrealpath.php
[2722]: http://php.net/manual/zh/splfileinfo.getsize.php
[2723]: http://php.net/manual/zh/splfileinfo.gettype.php
[2724]: http://php.net/manual/zh/splfileinfo.isdir.php
[2725]: http://php.net/manual/zh/splfileinfo.isexecutable.php
[2726]: http://php.net/manual/zh/splfileinfo.isfile.php
[2727]: http://php.net/manual/zh/splfileinfo.islink.php
[2728]: http://php.net/manual/zh/splfileinfo.isreadable.php
[2729]: http://php.net/manual/zh/splfileinfo.iswritable.php
[2730]: http://php.net/manual/zh/splfileinfo.setfileclass.php
[2731]: http://php.net/manual/zh/splfileinfo.openfile.php
[2732]: http://php.net/manual/zh/splfileinfo.setinfoclass.php
[2733]: http://php.net/manual/zh/splfileinfo.getfileinfo.php
[2734]: http://php.net/manual/zh/splfileinfo.getpathinfo.php
[2735]: http://php.net/manual/zh/function.split.php
[2736]: http://php.net/manual/zh/function.spliti.php
[2737]: http://php.net/manual/zh/spoofchecker.areconfusable.php
[2738]: http://php.net/manual/zh/spoofchecker.issuspicious.php
[2739]: http://php.net/manual/zh/spoofchecker.setallowedlocales.php
[2740]: http://php.net/manual/zh/spoofchecker.setchecks.php
[2741]: http://php.net/manual/zh/function.sprintf.php
[2742]: http://php.net/manual/zh/function.sql-regcase.php
[2743]: http://php.net/manual/zh/function.sqlite-array-query.php
[2744]: http://php.net/manual/zh/function.sqlite-busy-timeout.php
[2745]: http://php.net/manual/zh/function.sqlite-changes.php
[2746]: http://php.net/manual/zh/function.sqlite-close.php
[2747]: http://php.net/manual/zh/function.sqlite-column.php
[2748]: http://php.net/manual/zh/function.sqlite-create-aggregate.php
[2749]: http://php.net/manual/zh/function.sqlite-create-function.php
[2750]: http://php.net/manual/zh/function.sqlite-current.php
[2751]: http://php.net/manual/zh/function.sqlite-error-string.php
[2752]: http://php.net/manual/zh/function.sqlite-escape-string.php
[2753]: http://php.net/manual/zh/function.sqlite-exec.php
[2754]: http://php.net/manual/zh/function.sqlite-factory.php
[2755]: http://php.net/manual/zh/function.sqlite-fetch-all.php
[2756]: http://php.net/manual/zh/function.sqlite-fetch-array.php
[2757]: http://php.net/manual/zh/function.sqlite-fetch-column-types.php
[2758]: http://php.net/manual/zh/function.sqlite-fetch-object.php
[2759]: http://php.net/manual/zh/function.sqlite-fetch-single.php
[2760]: http://php.net/manual/zh/function.sqlite-fetch-string.php
[2761]: http://php.net/manual/zh/function.sqlite-fetch-single.php
[2762]: http://php.net/manual/zh/function.sqlite-field-name.php
[2763]: http://php.net/manual/zh/function.sqlite-has-more.php
[2764]: http://php.net/manual/zh/function.sqlite-has-prev.php
[2765]: http://php.net/manual/zh/function.sqlite-last-error.php
[2766]: http://php.net/manual/zh/function.sqlite-last-insert-rowid.php
[2767]: http://php.net/manual/zh/function.sqlite-libencoding.php
[2768]: http://php.net/manual/zh/function.sqlite-libversion.php
[2769]: http://php.net/manual/zh/function.sqlite-next.php
[2770]: http://php.net/manual/zh/function.sqlite-num-fields.php
[2771]: http://php.net/manual/zh/function.sqlite-num-rows.php
[2772]: http://php.net/manual/zh/function.sqlite-open.php
[2773]: http://php.net/manual/zh/function.sqlite-popen.php
[2774]: http://php.net/manual/zh/function.sqlite-prev.php
[2775]: http://php.net/manual/zh/function.sqlite-query.php
[2776]: http://php.net/manual/zh/function.sqlite-rewind.php
[2777]: http://php.net/manual/zh/function.sqlite-seek.php
[2778]: http://php.net/manual/zh/function.sqlite-single-query.php
[2779]: http://php.net/manual/zh/function.sqlite-udf-decode-binary.php
[2780]: http://php.net/manual/zh/function.sqlite-udf-encode-binary.php
[2781]: http://php.net/manual/zh/function.sqlite-unbuffered-query.php
[2782]: http://php.net/manual/zh/function.sqlite-valid.php
[2783]: http://php.net/manual/zh/function.sqlsrv-begin-transaction.php
[2784]: http://php.net/manual/zh/function.sqlsrv-cancel.php
[2785]: http://php.net/manual/zh/function.sqlsrv-client-info.php
[2786]: http://php.net/manual/zh/function.sqlsrv-close.php
[2787]: http://php.net/manual/zh/function.sqlsrv-commit.php
[2788]: http://php.net/manual/zh/function.sqlsrv-begin-transaction.php
[2789]: http://php.net/manual/zh/function.sqlsrv-configure.php
[2790]: http://php.net/manual/zh/function.sqlsrv-connect.php
[2791]: http://php.net/manual/zh/function.sqlsrv-errors.php
[2792]: http://php.net/manual/zh/function.sqlsrv-execute.php
[2793]: http://php.net/manual/zh/function.sqlsrv-prepare.php
[2794]: http://php.net/manual/zh/function.sqlsrv-fetch.php
[2795]: http://php.net/manual/zh/function.sqlsrv-fetch-array.php
[2796]: http://php.net/manual/zh/function.sqlsrv-fetch-object.php
[2797]: http://php.net/manual/zh/function.sqlsrv-field-metadata.php
[2798]: http://php.net/manual/zh/function.sqlsrv-prepare.php
[2799]: http://php.net/manual/zh/function.sqlsrv-query.php
[2800]: http://php.net/manual/zh/function.sqlsrv-free-stmt.php
[2801]: http://php.net/manual/zh/function.sqlsrv-get-config.php
[2802]: http://php.net/manual/zh/function.sqlsrv-get-field.php
[2803]: http://php.net/manual/zh/function.sqlsrv-has-rows.php
[2804]: http://php.net/manual/zh/function.sqlsrv-next-result.php
[2805]: http://php.net/manual/zh/function.sqlsrv-num-fields.php
[2806]: http://php.net/manual/zh/function.sqlsrv-num-rows.php
[2807]: http://php.net/manual/zh/function.sqlsrv-prepare.php
[2808]: http://php.net/manual/zh/function.sqlsrv-query.php
[2809]: http://php.net/manual/zh/function.sqlsrv-rollback.php
[2810]: http://php.net/manual/zh/function.sqlsrv-begin-transaction.php
[2811]: http://php.net/manual/zh/function.sqlsrv-rows-affected.php
[2812]: http://php.net/manual/zh/function.sqlsrv-send-stream-data.php
[2813]: http://php.net/manual/zh/function.sqlsrv-server-info.php
[2814]: http://php.net/manual/zh/function.sqrt.php
[2815]: http://php.net/manual/zh/function.srand.php
[2816]: http://php.net/manual/zh/function.sscanf.php
[2817]: http://php.net/manual/zh/function.ssh2-auth-hostbased-file.php
[2818]: http://php.net/manual/zh/function.ssh2-auth-none.php
[2819]: http://php.net/manual/zh/function.ssh2-auth-password.php
[2820]: http://php.net/manual/zh/function.ssh2-auth-pubkey-file.php
[2821]: http://php.net/manual/zh/function.ssh2-connect.php
[2822]: http://php.net/manual/zh/function.ssh2-exec.php
[2823]: http://php.net/manual/zh/function.ssh2-fetch-stream.php
[2824]: http://php.net/manual/zh/function.ssh2-fingerprint.php
[2825]: http://php.net/manual/zh/function.ssh2-methods-negotiated.php
[2826]: http://php.net/manual/zh/function.ssh2-publickey-add.php
[2827]: http://php.net/manual/zh/function.ssh2-publickey-init.php
[2828]: http://php.net/manual/zh/function.ssh2-publickey-list.php
[2829]: http://php.net/manual/zh/function.ssh2-publickey-remove.php
[2830]: http://php.net/manual/zh/function.ssh2-scp-recv.php
[2831]: http://php.net/manual/zh/function.ssh2-scp-send.php
[2832]: http://php.net/manual/zh/function.ssh2-sftp.php
[2833]: http://php.net/manual/zh/function.ssh2-sftp-lstat.php
[2834]: http://php.net/manual/zh/function.ssh2-sftp-mkdir.php
[2835]: http://php.net/manual/zh/function.ssh2-sftp-readlink.php
[2836]: http://php.net/manual/zh/function.ssh2-sftp-realpath.php
[2837]: http://php.net/manual/zh/function.ssh2-sftp-rename.php
[2838]: http://php.net/manual/zh/function.ssh2-sftp-rmdir.php
[2839]: http://php.net/manual/zh/function.ssh2-sftp-stat.php
[2840]: http://php.net/manual/zh/function.ssh2-sftp-symlink.php
[2841]: http://php.net/manual/zh/function.ssh2-sftp-unlink.php
[2842]: http://php.net/manual/zh/function.ssh2-shell.php
[2843]: http://php.net/manual/zh/function.ssh2-tunnel.php
[2844]: http://php.net/manual/zh/function.stat.php
[2845]: http://php.net/manual/zh/function.str-getcsv.php
[2846]: http://php.net/manual/zh/function.str-ireplace.php
[2847]: http://php.net/manual/zh/function.str-replace.php
[2848]: http://php.net/manual/zh/function.str-pad.php
[2849]: http://php.net/manual/zh/function.str-repeat.php
[2850]: http://php.net/manual/zh/function.str-replace.php
[2851]: http://php.net/manual/zh/function.str-rot13.php
[2852]: http://php.net/manual/zh/function.str-shuffle.php
[2853]: http://php.net/manual/zh/function.str-split.php
[2854]: http://php.net/manual/zh/function.str-word-count.php
[2855]: http://php.net/manual/zh/function.strcasecmp.php
[2856]: http://php.net/manual/zh/function.strchr.php
[2857]: http://php.net/manual/zh/function.strstr.php
[2858]: http://php.net/manual/zh/function.strcmp.php
[2859]: http://php.net/manual/zh/function.strcoll.php
[2860]: http://php.net/manual/zh/function.strcspn.php
[2861]: http://php.net/manual/zh/function.stream-bucket-append.php
[2862]: http://php.net/manual/zh/function.stream-bucket-make-writeable.php
[2863]: http://php.net/manual/zh/function.stream-bucket-new.php
[2864]: http://php.net/manual/zh/function.stream-bucket-prepend.php
[2865]: http://php.net/manual/zh/function.stream-context-create.php
[2866]: http://php.net/manual/zh/function.stream-context-get-default.php
[2867]: http://php.net/manual/zh/function.stream-context-get-options.php
[2868]: http://php.net/manual/zh/function.stream-context-get-params.php
[2869]: http://php.net/manual/zh/function.stream-context-set-default.php
[2870]: http://php.net/manual/zh/function.stream-context-set-option.php
[2871]: http://php.net/manual/zh/function.stream-context-set-params.php
[2872]: http://php.net/manual/zh/function.stream-copy-to-stream.php
[2873]: http://php.net/manual/zh/function.stream-filter-append.php
[2874]: http://php.net/manual/zh/function.stream-filter-prepend.php
[2875]: http://php.net/manual/zh/function.stream-filter-register.php
[2876]: http://php.net/manual/zh/function.stream-filter-remove.php
[2877]: http://php.net/manual/zh/function.stream-get-contents.php
[2878]: http://php.net/manual/zh/function.stream-get-filters.php
[2879]: http://php.net/manual/zh/function.stream-get-line.php
[2880]: http://php.net/manual/zh/function.stream-get-meta-data.php
[2881]: http://php.net/manual/zh/function.stream-get-transports.php
[2882]: http://php.net/manual/zh/function.stream-get-wrappers.php
[2883]: http://php.net/manual/zh/function.stream-is-local.php
[2884]: http://php.net/manual/zh/function.stream-register-wrapper.php
[2885]: http://php.net/manual/zh/function.stream-wrapper-register.php
[2886]: http://php.net/manual/zh/function.stream-resolve-include-path.php
[2887]: http://php.net/manual/zh/function.stream-select.php
[2888]: http://php.net/manual/zh/function.stream-set-blocking.php
[2889]: http://php.net/manual/zh/function.stream-set-chunk-size.php
[2890]: http://php.net/manual/zh/function.stream-set-read-buffer.php
[2891]: http://php.net/manual/zh/function.stream-set-timeout.php
[2892]: http://php.net/manual/zh/function.stream-set-write-buffer.php
[2893]: http://php.net/manual/zh/function.stream-socket-accept.php
[2894]: http://php.net/manual/zh/function.stream-socket-server.php
[2895]: http://php.net/manual/zh/function.stream-socket-client.php
[2896]: http://php.net/manual/zh/function.stream-socket-enable-crypto.php
[2897]: http://php.net/manual/zh/function.stream-socket-get-name.php
[2898]: http://php.net/manual/zh/function.stream-socket-pair.php
[2899]: http://php.net/manual/zh/function.stream-socket-recvfrom.php
[2900]: http://php.net/manual/zh/function.stream-socket-sendto.php
[2901]: http://php.net/manual/zh/function.stream-socket-server.php
[2902]: http://php.net/manual/zh/function.stream-socket-shutdown.php
[2903]: http://php.net/manual/zh/function.stream-supports-lock.php
[2904]: http://php.net/manual/zh/function.stream-wrapper-register.php
[2905]: http://php.net/manual/zh/function.stream-wrapper-restore.php
[2906]: http://php.net/manual/zh/function.stream-wrapper-unregister.php
[2907]: http://php.net/manual/zh/function.strftime.php
[2908]: http://php.net/manual/zh/function.strip-tags.php
[2909]: http://php.net/manual/zh/function.stripcslashes.php
[2910]: http://php.net/manual/zh/function.addcslashes.php
[2911]: http://php.net/manual/zh/function.stripos.php
[2912]: http://php.net/manual/zh/function.stripslashes.php
[2913]: http://php.net/manual/zh/function.stristr.php
[2914]: http://php.net/manual/zh/function.strstr.php
[2915]: http://php.net/manual/zh/function.strlen.php
[2916]: http://php.net/manual/zh/function.strnatcasecmp.php
[2917]: http://php.net/manual/zh/function.strnatcmp.php
[2918]: http://php.net/manual/zh/function.strncasecmp.php
[2919]: http://php.net/manual/zh/function.strncmp.php
[2920]: http://php.net/manual/zh/function.strpbrk.php
[2921]: http://php.net/manual/zh/function.strpos.php
[2922]: http://php.net/manual/zh/function.strptime.php
[2923]: http://php.net/manual/zh/function.strftime.php
[2924]: http://php.net/manual/zh/function.strrchr.php
[2925]: http://php.net/manual/zh/function.strrev.php
[2926]: http://php.net/manual/zh/function.strripos.php
[2927]: http://php.net/manual/zh/function.strrpos.php
[2928]: http://php.net/manual/zh/function.strspn.php
[2929]: http://php.net/manual/zh/function.strstr.php
[2930]: http://php.net/manual/zh/function.strtok.php
[2931]: http://php.net/manual/zh/function.strtolower.php
[2932]: http://php.net/manual/zh/function.strtotime.php
[2933]: http://php.net/manual/zh/function.strtoupper.php
[2934]: http://php.net/manual/zh/function.strtr.php
[2935]: http://php.net/manual/zh/function.strval.php
[2936]: http://php.net/manual/zh/function.substr.php
[2937]: http://php.net/manual/zh/function.substr-compare.php
[2938]: http://php.net/manual/zh/function.substr-count.php
[2939]: http://php.net/manual/zh/function.substr-replace.php
[2940]: http://php.net/manual/zh/function.svn-add.php
[2941]: http://php.net/manual/zh/function.svn-auth-get-parameter.php
[2942]: http://php.net/manual/zh/function.svn-auth-set-parameter.php
[2943]: http://php.net/manual/zh/function.svn-blame.php
[2944]: http://php.net/manual/zh/function.svn-cat.php
[2945]: http://php.net/manual/zh/function.svn-checkout.php
[2946]: http://php.net/manual/zh/function.svn-cleanup.php
[2947]: http://php.net/manual/zh/function.svn-client-version.php
[2948]: http://php.net/manual/zh/function.svn-commit.php
[2949]: http://php.net/manual/zh/function.svn-delete.php
[2950]: http://php.net/manual/zh/function.svn-diff.php
[2951]: http://php.net/manual/zh/function.svn-export.php
[2952]: http://php.net/manual/zh/function.svn-fs-abort-txn.php
[2953]: http://php.net/manual/zh/function.svn-fs-apply-text.php
[2954]: http://php.net/manual/zh/function.svn-fs-begin-txn2.php
[2955]: http://php.net/manual/zh/function.svn-fs-change-node-prop.php
[2956]: http://php.net/manual/zh/function.svn-fs-check-path.php
[2957]: http://php.net/manual/zh/function.svn-fs-contents-changed.php
[2958]: http://php.net/manual/zh/function.svn-fs-copy.php
[2959]: http://php.net/manual/zh/function.svn-fs-delete.php
[2960]: http://php.net/manual/zh/function.svn-fs-dir-entries.php
[2961]: http://php.net/manual/zh/function.svn-fs-file-contents.php
[2962]: http://php.net/manual/zh/function.svn-fs-file-length.php
[2963]: http://php.net/manual/zh/function.svn-fs-is-dir.php
[2964]: http://php.net/manual/zh/function.svn-fs-is-file.php
[2965]: http://php.net/manual/zh/function.svn-fs-make-dir.php
[2966]: http://php.net/manual/zh/function.svn-fs-make-file.php
[2967]: http://php.net/manual/zh/function.svn-fs-node-created-rev.php
[2968]: http://php.net/manual/zh/function.svn-fs-node-prop.php
[2969]: http://php.net/manual/zh/function.svn-fs-props-changed.php
[2970]: http://php.net/manual/zh/function.svn-fs-revision-prop.php
[2971]: http://php.net/manual/zh/function.svn-fs-revision-root.php
[2972]: http://php.net/manual/zh/function.svn-fs-txn-root.php
[2973]: http://php.net/manual/zh/function.svn-fs-youngest-rev.php
[2974]: http://php.net/manual/zh/function.svn-import.php
[2975]: http://php.net/manual/zh/function.svn-log.php
[2976]: http://php.net/manual/zh/function.svn-ls.php
[2977]: http://php.net/manual/zh/function.svn-mkdir.php
[2978]: http://php.net/manual/zh/function.svn-repos-create.php
[2979]: http://php.net/manual/zh/function.svn-repos-fs.php
[2980]: http://php.net/manual/zh/function.svn-repos-fs-begin-txn-for-commit.php
[2981]: http://php.net/manual/zh/function.svn-repos-fs-commit-txn.php
[2982]: http://php.net/manual/zh/function.svn-repos-hotcopy.php
[2983]: http://php.net/manual/zh/function.svn-repos-open.php
[2984]: http://php.net/manual/zh/function.svn-repos-recover.php
[2985]: http://php.net/manual/zh/function.svn-revert.php
[2986]: http://php.net/manual/zh/function.svn-status.php
[2987]: http://php.net/manual/zh/function.svn-update.php
[2988]: http://php.net/manual/zh/function.sybase-affected-rows.php
[2989]: http://php.net/manual/zh/function.sybase-close.php
[2990]: http://php.net/manual/zh/function.sybase-connect.php
[2991]: http://php.net/manual/zh/function.sybase-data-seek.php
[2992]: http://php.net/manual/zh/function.sybase-deadlock-retry-count.php
[2993]: http://php.net/manual/zh/function.sybase-fetch-array.php
[2994]: http://php.net/manual/zh/function.sybase-fetch-assoc.php
[2995]: http://php.net/manual/zh/function.sybase-fetch-field.php
[2996]: http://php.net/manual/zh/function.sybase-fetch-object.php
[2997]: http://php.net/manual/zh/function.sybase-fetch-row.php
[2998]: http://php.net/manual/zh/function.sybase-field-seek.php
[2999]: http://php.net/manual/zh/function.sybase-free-result.php
[3000]: http://php.net/manual/zh/function.sybase-get-last-message.php
[3001]: http://php.net/manual/zh/function.sybase-min-client-severity.php
[3002]: http://php.net/manual/zh/function.sybase-min-server-severity.php
[3003]: http://php.net/manual/zh/function.sybase-num-fields.php
[3004]: http://php.net/manual/zh/function.sybase-num-rows.php
[3005]: http://php.net/manual/zh/function.sybase-pconnect.php
[3006]: http://php.net/manual/zh/function.sybase-query.php
[3007]: http://php.net/manual/zh/function.sybase-result.php
[3008]: http://php.net/manual/zh/function.sybase-select-db.php
[3009]: http://php.net/manual/zh/function.sybase-set-message-handler.php
[3010]: http://php.net/manual/zh/function.sybase-unbuffered-query.php
[3011]: http://php.net/manual/zh/function.symlink.php
[3012]: http://php.net/manual/zh/function.sys-get-temp-dir.php
[3013]: http://php.net/manual/zh/function.sys-getloadavg.php
[3014]: http://php.net/manual/zh/function.syslog.php
[3015]: http://php.net/manual/zh/function.system.php
[3016]: http://php.net/manual/zh/function.tanh.php
[3017]: http://php.net/manual/zh/function.tempnam.php
[3018]: http://php.net/manual/zh/function.textdomain.php
[3019]: http://php.net/manual/zh/function.tidy-access-count.php
[3020]: http://php.net/manual/zh/function.tidy-config-count.php
[3021]: http://php.net/manual/zh/function.tidy-diagnose.php
[3022]: http://php.net/manual/zh/function.tidy-error-count.php
[3023]: http://php.net/manual/zh/function.tidy-get-output.php
[3024]: http://php.net/manual/zh/function.tidy-getopt.php
[3025]: http://php.net/manual/zh/function.tidy-warning-count.php
[3026]: http://php.net/manual/zh/function.time.php
[3027]: http://php.net/manual/zh/function.time-nanosleep.php
[3028]: http://php.net/manual/zh/function.time-sleep-until.php
[3029]: http://php.net/manual/zh/function.timezone-abbreviations-list.php
[3030]: http://php.net/manual/zh/datetimezone.listabbreviations.php
[3031]: http://php.net/manual/zh/function.timezone-identifiers-list.php
[3032]: http://php.net/manual/zh/datetimezone.listidentifiers.php
[3033]: http://php.net/manual/zh/function.timezone-location-get.php
[3034]: http://php.net/manual/zh/datetimezone.getlocation.php
[3035]: http://php.net/manual/zh/function.timezone-name-from-abbr.php
[3036]: http://php.net/manual/zh/function.timezone-name-get.php
[3037]: http://php.net/manual/zh/datetimezone.getname.php
[3038]: http://php.net/manual/zh/function.timezone-offset-get.php
[3039]: http://php.net/manual/zh/datetimezone.getoffset.php
[3040]: http://php.net/manual/zh/function.timezone-open.php
[3041]: http://php.net/manual/zh/datetimezone.construct.php
[3042]: http://php.net/manual/zh/function.timezone-transitions-get.php
[3043]: http://php.net/manual/zh/datetimezone.gettransitions.php
[3044]: http://php.net/manual/zh/function.timezone-version-get.php
[3045]: http://php.net/manual/zh/function.tmpfile.php
[3046]: http://php.net/manual/zh/function.token-get-all.php
[3047]: http://php.net/manual/zh/function.token-name.php
[3048]: http://php.net/manual/zh/function.touch.php
[3049]: http://php.net/manual/zh/function.trait-exists.php
[3050]: http://php.net/manual/zh/transliterator.createinverse.php
[3051]: http://php.net/manual/zh/transliterator.geterrorcode.php
[3052]: http://php.net/manual/zh/transliterator.geterrormessage.php
[3053]: http://php.net/manual/zh/transliterator.transliterate.php
[3054]: http://php.net/manual/zh/function.transliterator-create.php
[3055]: http://php.net/manual/zh/function.transliterator-transliterate.php
[3056]: http://php.net/manual/zh/function.trigger-error.php
[3057]: http://php.net/manual/zh/function.trim.php
[3058]: http://php.net/manual/zh/function.ucfirst.php
[3059]: http://php.net/manual/zh/uconverter.convert.php
[3060]: http://php.net/manual/zh/uconverter.fromucallback.php
[3061]: http://php.net/manual/zh/uconverter.getdestinationencoding.php
[3062]: http://php.net/manual/zh/uconverter.getdestinationtype.php
[3063]: http://php.net/manual/zh/uconverter.geterrorcode.php
[3064]: http://php.net/manual/zh/uconverter.geterrormessage.php
[3065]: http://php.net/manual/zh/uconverter.getsourceencoding.php
[3066]: http://php.net/manual/zh/uconverter.getsourcetype.php
[3067]: http://php.net/manual/zh/uconverter.getsubstchars.php
[3068]: http://php.net/manual/zh/uconverter.setdestinationencoding.php
[3069]: http://php.net/manual/zh/uconverter.setsourceencoding.php
[3070]: http://php.net/manual/zh/uconverter.setsubstchars.php
[3071]: http://php.net/manual/zh/uconverter.toucallback.php
[3072]: http://php.net/manual/zh/function.ucwords.php
[3073]: http://php.net/manual/zh/function.uksort.php
[3074]: http://php.net/manual/zh/function.umask.php
[3075]: http://php.net/manual/zh/function.uniqid.php
[3076]: http://php.net/manual/zh/function.unixtojd.php
[3077]: http://php.net/manual/zh/function.unlink.php
[3078]: http://php.net/manual/zh/function.unpack.php
[3079]: http://php.net/manual/zh/function.unregister-tick-function.php
[3080]: http://php.net/manual/zh/function.unserialize.php
[3081]: http://php.net/manual/zh/function.urldecode.php
[3082]: http://php.net/manual/zh/function.urlencode.php
[3083]: http://php.net/manual/zh/function.use-soap-error-handler.php
[3084]: http://php.net/manual/zh/function.user-error.php
[3085]: http://php.net/manual/zh/function.trigger-error.php
[3086]: http://php.net/manual/zh/function.usleep.php
[3087]: http://php.net/manual/zh/function.usort.php
[3088]: http://php.net/manual/zh/function.utf8-decode.php
[3089]: http://php.net/manual/zh/function.utf8-encode.php
[3090]: http://php.net/manual/zh/function.var-export.php
[3091]: http://php.net/manual/zh/function.version-compare.php
[3092]: http://php.net/manual/zh/function.vfprintf.php
[3093]: http://php.net/manual/zh/function.virtual.php
[3094]: http://php.net/manual/zh/function.vprintf.php
[3095]: http://php.net/manual/zh/function.vsprintf.php
[3096]: http://php.net/manual/zh/function.wddx-deserialize.php
[3097]: http://php.net/manual/zh/function.wddx-packet-end.php
[3098]: http://php.net/manual/zh/function.wddx-packet-start.php
[3099]: http://php.net/manual/zh/function.wddx-serialize-value.php
[3100]: http://php.net/manual/zh/function.wddx-serialize-vars.php
[3101]: http://php.net/manual/zh/function.wincache-fcache-fileinfo.php
[3102]: http://php.net/manual/zh/function.wincache-fcache-meminfo.php
[3103]: http://php.net/manual/zh/function.wincache-lock.php
[3104]: http://php.net/manual/zh/function.wincache-ocache-fileinfo.php
[3105]: http://php.net/manual/zh/function.wincache-ocache-meminfo.php
[3106]: http://php.net/manual/zh/function.wincache-refresh-if-changed.php
[3107]: http://php.net/manual/zh/function.wincache-rplist-fileinfo.php
[3108]: http://php.net/manual/zh/function.wincache-rplist-meminfo.php
[3109]: http://php.net/manual/zh/function.wincache-scache-info.php
[3110]: http://php.net/manual/zh/function.wincache-scache-meminfo.php
[3111]: http://php.net/manual/zh/function.wincache-ucache-add.php
[3112]: http://php.net/manual/zh/function.wincache-ucache-cas.php
[3113]: http://php.net/manual/zh/function.wincache-ucache-clear.php
[3114]: http://php.net/manual/zh/function.wincache-ucache-dec.php
[3115]: http://php.net/manual/zh/function.wincache-ucache-delete.php
[3116]: http://php.net/manual/zh/function.wincache-ucache-exists.php
[3117]: http://php.net/manual/zh/function.wincache-ucache-get.php
[3118]: http://php.net/manual/zh/function.wincache-ucache-inc.php
[3119]: http://php.net/manual/zh/function.wincache-ucache-info.php
[3120]: http://php.net/manual/zh/function.wincache-ucache-meminfo.php
[3121]: http://php.net/manual/zh/function.wincache-ucache-set.php
[3122]: http://php.net/manual/zh/function.wincache-unlock.php
[3123]: http://php.net/manual/zh/function.wordwrap.php
[3124]: http://php.net/manual/zh/function.xhprof-enable.php
[3125]: http://php.net/manual/zh/function.xhprof-sample-disable.php
[3126]: http://php.net/manual/zh/function.xhprof-sample-enable.php
[3127]: http://php.net/manual/zh/function.xml-error-string.php
[3128]: http://php.net/manual/zh/function.xml-get-current-byte-index.php
[3129]: http://php.net/manual/zh/function.xml-get-current-column-number.php
[3130]: http://php.net/manual/zh/function.xml-get-current-line-number.php
[3131]: http://php.net/manual/zh/function.xml-get-error-code.php
[3132]: http://php.net/manual/zh/function.xml-parse.php
[3133]: http://php.net/manual/zh/function.xml-parse-into-struct.php
[3134]: http://php.net/manual/zh/function.xml-parser-create.php
[3135]: http://php.net/manual/zh/function.xml-parser-create-ns.php
[3136]: http://php.net/manual/zh/function.xml-parser-free.php
[3137]: http://php.net/manual/zh/function.xml-parser-get-option.php
[3138]: http://php.net/manual/zh/function.xml-parser-set-option.php
[3139]: http://php.net/manual/zh/function.xml-set-character-data-handler.php
[3140]: http://php.net/manual/zh/function.xml-set-default-handler.php
[3141]: http://php.net/manual/zh/function.xml-set-element-handler.php
[3142]: http://php.net/manual/zh/function.xml-set-end-namespace-decl-handler.php
[3143]: http://php.net/manual/zh/function.xml-set-external-entity-ref-handler.php
[3144]: http://php.net/manual/zh/function.xml-set-notation-decl-handler.php
[3145]: http://php.net/manual/zh/function.xml-set-object.php
[3146]: http://php.net/manual/zh/function.xml-set-processing-instruction-handler.php
[3147]: http://php.net/manual/zh/function.xml-set-start-namespace-decl-handler.php
[3148]: http://php.net/manual/zh/function.xml-set-unparsed-entity-decl-handler.php
[3149]: http://php.net/manual/zh/function.xmlrpc-decode.php
[3150]: http://php.net/manual/zh/function.xmlrpc-decode-request.php
[3151]: http://php.net/manual/zh/function.xmlrpc-encode.php
[3152]: http://php.net/manual/zh/function.xmlrpc-encode-request.php
[3153]: http://php.net/manual/zh/function.xmlrpc-get-type.php
[3154]: http://php.net/manual/zh/function.xmlrpc-is-fault.php
[3155]: http://php.net/manual/zh/function.xmlrpc-parse-method-descriptions.php
[3156]: http://php.net/manual/zh/function.xmlrpc-server-add-introspection-data.php
[3157]: http://php.net/manual/zh/function.xmlrpc-server-call-method.php
[3158]: http://php.net/manual/zh/function.xmlrpc-server-create.php
[3159]: http://php.net/manual/zh/function.xmlrpc-server-destroy.php
[3160]: http://php.net/manual/zh/function.xmlrpc-server-register-introspection-callback.php
[3161]: http://php.net/manual/zh/function.xmlrpc-server-register-method.php
[3162]: http://php.net/manual/zh/function.xmlrpc-set-type.php
[3163]: http://php.net/manual/zh/function.xmlwriter-end-attribute.php
[3164]: http://php.net/manual/zh/function.xmlwriter-end-cdata.php
[3165]: http://php.net/manual/zh/function.xmlwriter-end-comment.php
[3166]: http://php.net/manual/zh/function.xmlwriter-end-document.php
[3167]: http://php.net/manual/zh/function.xmlwriter-end-dtd.php
[3168]: http://php.net/manual/zh/function.xmlwriter-end-dtd-attlist.php
[3169]: http://php.net/manual/zh/function.xmlwriter-end-dtd-element.php
[3170]: http://php.net/manual/zh/function.xmlwriter-end-dtd-entity.php
[3171]: http://php.net/manual/zh/function.xmlwriter-end-element.php
[3172]: http://php.net/manual/zh/function.xmlwriter-end-pi.php
[3173]: http://php.net/manual/zh/function.xmlwriter-flush.php
[3174]: http://php.net/manual/zh/function.xmlwriter-full-end-element.php
[3175]: http://php.net/manual/zh/function.xmlwriter-open-memory.php
[3176]: http://php.net/manual/zh/function.xmlwriter-open-uri.php
[3177]: http://php.net/manual/zh/function.xmlwriter-output-memory.php
[3178]: http://php.net/manual/zh/function.xmlwriter-set-indent.php
[3179]: http://php.net/manual/zh/function.xmlwriter-set-indent-string.php
[3180]: http://php.net/manual/zh/function.xmlwriter-start-attribute.php
[3181]: http://php.net/manual/zh/function.xmlwriter-start-attribute-ns.php
[3182]: http://php.net/manual/zh/function.xmlwriter-start-cdata.php
[3183]: http://php.net/manual/zh/function.xmlwriter-start-comment.php
[3184]: http://php.net/manual/zh/function.xmlwriter-start-document.php
[3185]: http://php.net/manual/zh/function.xmlwriter-start-dtd.php
[3186]: http://php.net/manual/zh/function.xmlwriter-start-dtd-attlist.php
[3187]: http://php.net/manual/zh/function.xmlwriter-start-dtd-element.php
[3188]: http://php.net/manual/zh/function.xmlwriter-start-dtd-entity.php
[3189]: http://php.net/manual/zh/function.xmlwriter-start-element.php
[3190]: http://php.net/manual/zh/function.xmlwriter-start-element-ns.php
[3191]: http://php.net/manual/zh/function.xmlwriter-start-pi.php
[3192]: http://php.net/manual/zh/function.xmlwriter-text.php
[3193]: http://php.net/manual/zh/function.xmlwriter-write-attribute.php
[3194]: http://php.net/manual/zh/function.xmlwriter-write-attribute-ns.php
[3195]: http://php.net/manual/zh/function.xmlwriter-write-cdata.php
[3196]: http://php.net/manual/zh/function.xmlwriter-write-comment.php
[3197]: http://php.net/manual/zh/function.xmlwriter-write-dtd.php
[3198]: http://php.net/manual/zh/function.xmlwriter-write-dtd-attlist.php
[3199]: http://php.net/manual/zh/function.xmlwriter-write-dtd-element.php
[3200]: http://php.net/manual/zh/function.xmlwriter-write-dtd-entity.php
[3201]: http://php.net/manual/zh/function.xmlwriter-write-element.php
[3202]: http://php.net/manual/zh/function.xmlwriter-write-element-ns.php
[3203]: http://php.net/manual/zh/function.xmlwriter-write-pi.php
[3204]: http://php.net/manual/zh/function.xmlwriter-write-raw.php
[3205]: http://php.net/manual/zh/function.yaml-emit-file.php
[3206]: http://php.net/manual/zh/function.yaml-parse.php
[3207]: http://php.net/manual/zh/function.yaml-parse-file.php
[3208]: http://php.net/manual/zh/function.yaml-parse-url.php
[3209]: http://php.net/manual/zh/function.zend-version.php
[3210]: http://php.net/manual/zh/function.zip-close.php
[3211]: http://php.net/manual/zh/function.zip-entry-close.php
[3212]: http://php.net/manual/zh/function.zip-entry-compressedsize.php
[3213]: http://php.net/manual/zh/function.zip-entry-compressionmethod.php
[3214]: http://php.net/manual/zh/function.zip-entry-filesize.php
[3215]: http://php.net/manual/zh/function.zip-entry-name.php
[3216]: http://php.net/manual/zh/function.zip-entry-open.php
[3217]: http://php.net/manual/zh/function.zip-entry-read.php
[3218]: http://php.net/manual/zh/function.zip-open.php
[3219]: http://php.net/manual/zh/function.zip-read.php
[3220]: http://php.net/manual/zh/ziparchive.setpassword.php
[3221]: http://php.net/manual/zh/function.zlib-decode.php
[3222]: http://php.net/manual/zh/function.zlib-encode.php
[3223]: http://php.net/manual/zh/function.zlib-get-coding-type.php
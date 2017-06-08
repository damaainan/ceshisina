# [MySQL1：MySQL函数汇总][0]

**前言**

MySQL提供了众多功能强大、方便易用的函数，使用这些函数，可以极大地提高用户对于数据库的管理效率，从而更加灵活地满足不同用户的需求。本文将MySQL的函数分类并汇总，以便以后用到的时候可以随时查看。

**数学函数**

**（1）ABS(x)**

返回x的绝对值

**（2）PI()**

返回圆周率π，默认显示6位小数

**（3）SQRT(x)**

返回非负数的x的二次方根

**（4）MOD(x,y)**

返回x被y除后的余数

**（5）CEIL(x)、CEILING(x)**

返回不小于x的最小整数

**（6）FLOOR(x)**

返回不大于x的最大整数

**（7）ROUND(x)、ROUND(x,y)**

前者返回最接近于x的整数，即对x进行四舍五入；后者返回最接近x的数，其值保留到小数点后面y位，若y为负值，则将保留到x到小数点左边y位

**（8）SIGN(x)**

返回参数x的符号，-1表示负数，0表示0，1表示正数

**（9）POW(x,y)和、POWER(x,y)**

返回x的y次乘方的值

**（10）EXP(x)**

返回e的x乘方后的值

**（11）LOG(x)**

返回x的自然对数，x相对于基数e的对数

**（12）LOG10(x)**

返回x的基数为10的对数

**（13）RADIANS(x)**

返回x由角度转化为弧度的值

**（14）DEGREES(x)**

返回x由弧度转化为角度的值

**（15）SIN(x)、ASIN(x)**

前者返回x的正弦，其中x为给定的弧度值；后者返回x的反正弦值，x为正弦

**（16）COS(x)、ACOS(x)**

前者返回x的余弦，其中x为给定的弧度值；后者返回x的反余弦值，x为余弦

**（17）TAN(x)、ATAN(x)**

前者返回x的正切，其中x为给定的弧度值；后者返回x的反正切值，x为正切

**（18）COT(x)**

返回给定弧度值x的余切

**字符串函数**

**（1）CHAR_LENGTH(str)**

计算字符串字符个数

**（2）CONCAT(s1,s2，...)**

返回连接参数产生的字符串，一个或多个待拼接的内容，任意一个为NULL则返回值为NULL

**（3）CONCAT_WS(x,s1,s2,...)**

返回多个字符串拼接之后的字符串，每个字符串之间有一个x

**（4）INSERT(s1,x,len,s2)**

返回字符串s1，其子字符串起始于位置x，被字符串s2取代len个字符

**（5）LOWER(str)和LCASE(str)、UPPER(str)和UCASE(str)**

前两者将str中的字母全部转换成小写，后两者将字符串中的字母全部转换成大写

**（6）LEFT(s,n)、RIGHT(s,n)**

前者返回字符串s从最左边开始的n个字符，后者返回字符串s从最右边开始的n个字符

**（7）LPAD(s1,len,s2)、RPAD(s1,len,s2)**

前者返回s1，其左边由字符串s2填补到len字符长度，假如s1的长度大于len，则返回值被缩短至len字符；前者返回s1，其右边由字符串s2填补到len字符长度，假如s1的长度大于len，则返回值被缩短至len字符

**（8）LTRIM(s)、RTRIM(s)**

前者返回字符串s，其左边所有空格被删除；后者返回字符串s，其右边所有空格被删除

**（9）TRIM(s)**

返回字符串s删除了两边空格之后的字符串

**（10）TRIM(s1 FROM s)**

删除字符串s两端所有子字符串s1，未指定s1的情况下则默认删除空格

**（11）REPEAT(s,n)**

返回一个由重复字符串s组成的字符串，字符串s的数目等于n

**（12）SPACE(n)**

返回一个由n个空格组成的字符串

**（13）REPLACE(s,s1,s2)**

返回一个字符串，用字符串s2替代字符串s中所有的字符串s1

**（14）STRCMP(s1,s2)**

若s1和s2中所有的字符串都相同，则返回0；根据当前分类次序，第一个参数小于第二个则返回-1，其他情况返回1

**（15）SUBSTRING(s,n,len)、MID(s,n,len)**

两个函数作用相同，从字符串s中返回一个第n个字符开始、长度为len的字符串

**（16）LOCATE(str1,str)、POSITION(str1 IN str)、INSTR(str,str1)**

三个函数作用相同，返回子字符串str1在字符串str中的开始位置（从第几个字符开始）

**（17）REVERSE(s)**

将字符串s反转

**（18）ELT(N,str1,str2,str3,str4,...)**

返回第N个字符串

**日期和时间函数**

**（1）CURDATE()、CURRENT_DATE()**

将当前日期按照"YYYY-MM-DD"或者"YYYYMMDD"格式的值返回，具体格式根据函数用在字符串或是数字语境中而定

**（2）CURRENT_TIMESTAMP()、LOCALTIME()、NOW()、SYSDATE()**

这四个函数作用相同，返回当前日期和时间值，格式为"YYYY_MM-DD HH:MM:SS"或"YYYYMMDDHHMMSS"，具体格式根据函数用在字符串或数字语境中而定

**（3）UNIX_TIMESTAMP()、UNIX_TIMESTAMP(date)**

前者返回一个格林尼治标准时间1970-01-01 00:00:00到现在的秒数，后者返回一个格林尼治标准时间1970-01-01 00:00:00到指定时间的秒数

**（4）FROM_UNIXTIME(date)**

和UNIX_TIMESTAMP互为反函数，把UNIX时间戳转换为普通格式的时间

**（5）UTC_DATE()和UTC_TIME()**

前者返回当前UTC（世界标准时间）日期值，其格式为"YYYY-MM-DD"或"YYYYMMDD"，后者返回当前UTC时间值，其格式为"YYYY-MM-DD"或"YYYYMMDD"。具体使用哪种取决于函数用在字符串还是数字语境中

**（6）MONTH(date)和MONTHNAME(date)**

前者返回指定日期中的月份，后者返回指定日期中的月份的名称

**（7）DAYNAME(d)、DAYOFWEEK(d)、WEEKDAY(d)**

DAYNAME(d)返回d对应的工作日的英文名称，如Sunday、Monday等；DAYOFWEEK(d)返回的对应一周中的索引，1表示周日、2表示周一；WEEKDAY(d)表示d对应的工作日索引，0表示周一，1表示周二

**（8）WEEK(d)、WEEKOFYEAD(d)**

前者计算日期d是一年中的第几周，后者计算某一天位于一年中的第几周

**（9）DAYOFYEAR(d)、DAYOFMONTH(d)**

前者返回d是一年中的第几天，后者返回d是一月中的第几天

**（10）YEAR(date)、QUARTER(date)、MINUTE(time)、SECOND(time)**

YEAR(date)返回指定日期对应的年份，范围是1970~2069；QUARTER(date)返回date对应一年中的季度，范围是1~4；MINUTE(time)返回time对应的分钟数，范围是0~59；SECOND(time)返回制定时间的秒值

**（11）EXTRACE(type FROM date)**

从日期中提取一部分，type可以是YEAR、YEAR_MONTH、DAY_HOUR、DAY_MICROSECOND、DAY_MINUTE、DAY_SECOND

**（12）TIME_TO_SEC(time)**

返回以转换为秒的time参数，转换公式为"3600*小时 + 60*分钟 + 秒"

**（13）SEC_TO_TIME()**

和TIME_TO_SEC(time)互为反函数，将秒值转换为时间格式

**（14）DATE_ADD(date,INTERVAL expr type)、ADD_DATE(date,INTERVAL expr type)**

返回将起始时间加上expr type之后的时间，比如DATE_ADD('2010-12-31 23:59:59', INTERVAL 1 SECOND)表示的就是把第一个时间加1秒

**（15）DATE_SUB(date,INTERVAL expr type)、SUBDATE(date,INTERVAL expr type)**

返回将起始时间减去expr type之后的时间

**（16）ADDTIME(date,expr)、SUBTIME(date,expr)**

前者进行date的时间加操作，后者进行date的时间减操作

**条件判断函数**

**（1）IF(expr,v1,v2)**

如果expr是TRUE则返回v1，否则返回v2

**（2）IFNULL(v1,v2)**

如果v1不为NULL，则返回v1，否则返回v2

**（3）CASE expr WHEN v1 THEN r1 [WHEN v2 THEN v2] [ELSE rn] END**

如果expr等于某个vn，则返回对应位置THEN后面的结果，如果与所有值都不想等，则返回ELSE后面的rn

**系统信息函数**

**（1）VERSION()**

查看MySQL版本号

**（2）CONNECTION_ID()**

查看当前用户的连接数

**（3）USER()、CURRENT_USER()、SYSTEM_USER()、SESSION_USER()**

查看当前被MySQL服务器验证的用户名和主机的组合，一般这几个函数的返回值是相同的

**（4）CHARSET(str)**

查看字符串str使用的字符集

**（5）COLLATION()**

查看字符串排列方式

**加密函数**

**（1）PASSWORD(str)**

从原明文密码str计算并返回加密后的字符串密码，注意这个函数的加密是单向的（不可逆），因此不应将它应用在个人的应用程序中而应该只在MySQL服务器的鉴定系统中使用

**（2）MD5(str)**

为字符串算出一个MD5 128比特校验和，改值以32位十六进制数字的二进制字符串形式返回

**（3）ENCODE(str, pswd_str)**

使用pswd_str作为密码，加密str

**（4）DECODE(crypt_str,pswd_str)**

使用pswd_str作为密码，解密加密字符串crypt_str，crypt_str是由ENCODE函数返回的字符串

**其他函数**

**（1）FORMAT(x,n)**

将数字x格式化，并以四舍五入的方式保留小数点后n位，结果以字符串形式返回

**（2）CONV(N,from_base,to_base)**

不同进制数之间的转换，返回值为数值N的字符串表示，由from_base进制转换为to_base进制

**（3）INET_ATON(expr)**

给出一个作为字符串的网络地址的点地址表示，返回一个代表该地址数值的整数，地址可以使4或8比特

**（4）INET_NTOA(expr)**

给定一个数字网络地址（4或8比特），返回作为字符串的该地址的点地址表示

**（5）BENCHMARK(count,expr)**

重复执行count次表达式expr，它可以用于计算MySQL处理表达式的速度，结果值通常是0（0只是表示很快，并不是没有速度）。另一个作用是用它在MySQL客户端内部报告语句执行的时间

**（6）CONVERT(str USING charset)**

使用字符集charset表示字符串str

[0]: http://www.cnblogs.com/xrq730/p/4943660.html